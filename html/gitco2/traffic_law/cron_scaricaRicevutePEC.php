<?php
require_once ("_path.php");
require_once (INC."/parameter.php");
require_once (CLS."/cls_db.php");
require_once (CLS."/cls_view.php");
require_once (CLS.'/cls_mail.php');
require_once (INC."/cli_function.php");

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

const CONNECTION_ATTEMPTS = 3;

const PEC_X_RICEVUTA_ACCETTAZIONE = 'accettazione';
const PEC_X_RICEVUTA_CONSEGNA = 'avvenuta-consegna';
const PEC_X_RICEVUTA_MANCATA_CONSEGNA = 'errore-consegna';
const PEC_X_RICEVUTA_PREAVVISO_ERRORE_CONSEGNA = 'preavviso-errore-consegna';

const PEC_X_RICEVUTE = array(
    PEC_X_RICEVUTA_ACCETTAZIONE,
    PEC_X_RICEVUTA_CONSEGNA,
    PEC_X_RICEVUTA_MANCATA_CONSEGNA,
    PEC_X_RICEVUTA_PREAVVISO_ERRORE_CONSEGNA
);

$rs = new CLS_DB();
$rs->SetCharset('utf8');

function writeLog(String $tipo, String $messaggio, string $cityId = '', bool $registraEvento = false){
    global $rs;
    $serverity = null;
    
    switch($tipo){
        case 'N': trigger_error("<SCARICARICEVUTEPEC> DEBUG -> $messaggio", E_USER_NOTICE); $serverity = 'INFO'; break;
        case 'W': trigger_error("<SCARICARICEVUTEPEC> ATTENZIONE -> $messaggio", E_USER_WARNING); $serverity = 'WARNING'; break;
        case 'D': trigger_error("<SCARICARICEVUTEPEC> ERRORE -> $messaggio", E_USER_WARNING); $serverity = 'ERROR'; break;
        case 'S': trigger_error("<SCARICARICEVUTEPEC> SUCCESS -> $messaggio", E_USER_WARNING); $serverity = 'SUCCESS'; break;
        default : trigger_error("<SCARICARICEVUTEPEC> DEBUG -> $messaggio", E_USER_NOTICE); $serverity = 'INFO'; break;
    }
    
    if($registraEvento){
        $a_Insert = array(
            array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => 'Scarico automatico ricevute PEC'),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $cityId),
            array('field' => 'Message', 'selector' => 'value', 'type' => 'str', 'value' => substr($messaggio, 0, 1000)),
            array('field' => 'Severity', 'selector' => 'value', 'type' => 'str', 'value' => $serverity),
            array('field' => 'EventDate', 'selector' => 'value', 'type' => 'str', 'value' => date('Y-m-d H:i:s')),
        );
        
        $rs->Insert('Events', $a_Insert);
    }
}

//Crea il gestore delle mail e controlla l' autenticazione
function checkMailConnection($cityId, $attempts){
    global $rs;
    
    $connection = false;
    $r_CustomerMail = $rs->getArrayLine($rs->Select('CustomerMailAuthentication', "ConfigType=1 AND CityId='$cityId'"));
    
    try{
        $mail = new MAIL_HANDLER($r_CustomerMail);
        
        for($i=0; $i<$attempts; $i++){
            writeLog('N', "Tentativo di connessione n.".($i+1)." al server di posta {$r_CustomerMail['IncomingMailServer']}...");
            if(($testmail = $mail->mailboxOpening()) !== true){
                writeLog('D', "ERRORE SERVER DI POSTA IN ENTRATA: $testmail");
                sleep(3);
            } else {
                writeLog('N', "Connessione riuscita.");
                $connection = true;
                break;
            }
        }
    } catch (Exception $e) {
        writeLog('D', 'ERRORE NEL PROCESSO AUTOMATICO DI SCARICO RICEVUTE PEC PEC', $cityId, true);
        writeLog('D', $e->getMessage());
    }
    
    if($connection){
        if(($testfolder = $mail->mailboxSelectFolder($r_CustomerMail['IncomingMailbox'])) === true){
            writeLog('N', "Cartella in lettura: {$r_CustomerMail['IncomingMailbox']}");
            return $mail;
        } else writeLog('D', "ERRORE NELL'APERTURA DELLA CARTELLA {$r_CustomerMail['IncomingMailbox']}: $testfolder", $cityId, true);
    } else writeLog('D', "CONNESSIONE AL SERVER DI POSTA IN ENTRATA {$r_CustomerMail['IncomingMailServer']} FALLITA", $cityId, true);
    
    return null;
}

//Calcola i flag del verbale///////////////////////////////////////////////////////
function getFineNotificationFlags($r_Fine){
    global $rs;
    $V_FineTariff = new CLS_VIEW(V_FINETARIFF);
    $rs_FineTariff = $rs->SelectQuery($V_FineTariff->generateSelect("Id={$r_Fine['Id']}"));
    $r_FineTariff = $rs->getArrayLine($rs_FineTariff);
    
    $a_Flags = array(
        'PaymentProcedure' => 0,
        'ReminderAdditionalFeeProcedure' => 0,
        '126BisProcedure' => 0,
        'PresentationDocumentProcedure' => 0,
        'LicensePointProcedure' => 0,
        'InjunctionProcedure' => 0,
        'HabitualProcedure' => 0,
        'SuspensionLicenseProcedure' => 0,
        'LossLicenseProcedure' => 0,
    );
    
    //Oltre a fare la verifica su tmp, verifica anche se il verbale risulta pagato (stato 30)
    $rs_PaymentProcedure = $rs->Select("TMP_PaymentProcedure", "FineId={$r_Fine['Id']}");
    if(mysqli_num_rows($rs_PaymentProcedure)==0){
        $a_Flags['PaymentProcedure'] = $r_Fine['StatusTypeId'] == 30 ? 0 : 1;
    } else $a_Flags['PaymentProcedure'] = 0;
    
    //Verifica se almeno un trasgressore ha la notifica su strada, in tal caso lascia il valore a 0
    $rs_126BisProcedure = $rs->Select("TMP_126BisProcedure", "FineId={$r_Fine['Id']}");
    if(mysqli_num_rows($rs_126BisProcedure)==0){
        //atti notificati su strada
        $rs_FineTrespasser = $rs->SelectQuery("SELECT FineNotificationType FROM FineTrespasser WHERE FineId={$r_Fine['Id']} AND FineNotificationType = 1");
        $a_Flags['126BisProcedure'] = mysqli_num_rows($rs_FineTrespasser) > 0 ? 0 : $r_FineTariff['126Bis'];
    } else $a_Flags['126BisProcedure'] = 0;
    
    //In questo caso il valore è opposto, se c'è il record su TMP è SI, altrimenti NO
    $rs_ReminderAdditionalFeeProcedure = $rs->Select("TMP_ReminderAdditionalFeeProcedure", "FineId={$r_Fine['Id']}");
    $a_Flags['ReminderAdditionalFeeProcedure'] = (mysqli_num_rows($rs_ReminderAdditionalFeeProcedure)==0) ? 0 : 1;
    
    //Se non c'è TMP deve tener conto del flag su ArticleTariff o della gestione avviso bonario
    $rs_PresentationDocumentProcedure = $rs->Select("TMP_PresentationDocumentProcedure", "FineId={$r_Fine['Id']}");
    $PresentationDocumentProcedureAvvisoBonario = 0;
    if(mysqli_num_rows($rs_PresentationDocumentProcedure)==0){
        $rs_Article = $rs->Select('V_FineArticle', "Id={$r_Fine['Id']}");
        $r_Article = mysqli_fetch_array($rs_Article);
        if (isset($r_Fine['KindCreateDate']) && isset($r_Fine['KindSendDate'])
            && (($r_Article['Article']=193 AND $r_Article['Paragraph']='2') || ($r_Article['Article']=80 AND $r_Article['Paragraph']='14'))) {
                $PresentationDocumentProcedureAvvisoBonario = 1;
            }
    }
    $a_Flags['PresentationDocumentProcedure'] = (mysqli_num_rows($rs_PresentationDocumentProcedure)==0) ?
    ($r_FineTariff['PresentationDocument'] || $PresentationDocumentProcedureAvvisoBonario ? 1 : 0) : 0; //$r_FineTariff['PresentationDocument'] || $PresentationDocumentProcedureAvvisoBonario : 0;
    
    $rs_LicensePointProcedure = $rs->Select("TMP_LicensePointProcedure", "FineId={$r_Fine['Id']}");
    $a_Flags['LicensePointProcedure'] = (mysqli_num_rows($rs_LicensePointProcedure)==0) ? $r_FineTariff['LicensePoint'] : 0;
    
    $rs_InjunctionProcedure = $rs->Select("TMP_InjunctionProcedure", "FineId={$r_Fine['Id']}");
    $a_Flags['InjunctionProcedure'] = (mysqli_num_rows($rs_InjunctionProcedure)==0) ? 1 : 0;
    
    $a_Flags['HabitualProcedure'] = $r_FineTariff['Habitual'];
    $a_Flags['SuspensionLicenseProcedure'] = $r_FineTariff['SuspensionLicense'];
    $a_Flags['LossLicenseProcedure'] = $r_FineTariff['LossLicense'];
    
    return $a_Flags;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(PRODUCTION){
    $a_Customers = array_column($rs->getResults($rs->Select('V_Customer', 'ManagePEC > 0')), null, 'CityId');
    
    foreach($a_Customers as $cityid => $a_Customer){
        //BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
        $a_LockTables = array("LockedPage WRITE");
        $rs->LockTables($a_LockTables);
        
        $rs_Locked = $rs->Select('LockedPage', "Title='scaricaRicevutePEC_$cityid'");
        $r_Locked = mysqli_fetch_assoc($rs_Locked);
        $b_Locked = false;
        
        if($r_Locked){
            if ($r_Locked['Locked'] == 1) {
                $b_Locked = true;
            } else {
                $UpdateLockedPage = array(
                    array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                    array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => 'scaricaRicevutePEC'),
                );
                $rs->Update('LockedPage', $UpdateLockedPage, "Title='scaricaRicevutePEC_$cityid'");
            }
        } else {
            $InsertLockedPage = array(
                array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => "scaricaRicevutePEC_$cityid"),
                array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => 'scaricaRicevutePEC'),
                );
            $rs->Insert('LockedPage', $InsertLockedPage);
        }
        
        $rs->UnlockTables();
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        if($b_Locked){
            writeLog('D', "Procedura bloccata dall'utente '{$r_Locked['UserName']}'. L'operazione non verrà eseguita.", $cityid, true);
        } else {
            if (!is_dir(PECRECEIPT_FOLDER . "/" . $cityid)) {
                mkdir(PECRECEIPT_FOLDER . "/" . $cityid, 0777, true);
            }
            
            writeLog('N', "Mi preparo al collegamento alla casella di posta delle ricevute PEC di $cityid");
            
            //Gestore mail: inizializzazione///////////////////////////////////////////////////////////
            $mail = checkMailConnection($cityid, CONNECTION_ATTEMPTS);
            ////////////////////////////////////////////////////////////////////////
            
            if($mail){
                writeLog('N', "Sono presenti {$mail->mailboxGetNumMails()} messaggi.");
                
                $a_Mails = array();
                $a_UIDS = $mail->mailboxGetMails();
                $a_UIDSToSave = array();
                
                foreach ($a_UIDS as $UID){
                    $headers = $mail->mailboxGetMailHeader($UID);
                    
                    if(isset($headers['X-Ricevuta']) && in_array($headers['X-Ricevuta'], PEC_X_RICEVUTE)){
                        $a_Mails[$UID]['Headers'] = $headers;
                        $a_Mails[$UID]['ExtraHeaders'] = (array) $mail->mailboxGetMailHeaderInfo($UID);
                        $a_Mails[$UID]['Message'] = $mail->mailboxGetMailBody($UID);
                    }
                }
                
                foreach ($a_Mails as $UID => $mailData){
                    if(!empty($mailData['Headers']['X-Riferimento-Message-ID'])){
                        $a_UpdateFPM = array();
                        $a_UpdateFH = array();
                        
                        $messageId = $mailData['Headers']['X-Riferimento-Message-ID'];
                        
                        $r_FlowPecMails = $rs->getArrayLine($rs->Select('FlowPecMails', "MessageId='$messageId' AND (Accepted != 'S' OR (Delivered != 'S' AND Anomaly != 'S'))"));
                        
                        if($r_FlowPecMails){
                            $FineId = $r_FlowPecMails['FineId'];
                            $FlowId = $r_FlowPecMails['FlowId'];
                            $AcceptedPresent = $r_FlowPecMails['Accepted'] == 'S' ? true : false;
                            $DeliveredPresent = $r_FlowPecMails['Delivered'] == 'S' ? true : false;
                            $AnomalyPresent = $r_FlowPecMails['Anomaly'] == 'S' ? true : false;
                            $AnomalyForeWarning = $r_FlowPecMails['Anomaly'] == 'T' ? true : false;
                            
                            $Fine = $rs->getArrayLine($rs->Select('Fine', "Id=$FineId"));
                            
                            $filepath = NATIONAL_FINE."/".$cityid."/".$FineId;
                            $filename = 'Ricevuta_InvioPEC_Id_'.$r_FlowPecMails['Id'].'_'.$mailData['Headers']['X-Ricevuta'];
                            
                            $a_FineNotification = array(
                                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>'scaricaRicevutePEC'),
                            );
                            
                            $a_Documentation10 = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 10),
                                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                            );
                            
                            switch($mailData['Headers']['X-Ricevuta']){
                                
                                case PEC_X_RICEVUTA_ACCETTAZIONE:
                                    if(!$AcceptedPresent){
                                        writelog('N', "Scarico ricevuta {$mailData['Headers']['X-Ricevuta']}: {$mailData['Headers']['Subject']}");
                                        if(file_put_contents("$filepath/$filename.html", $mailData['Message'])){
                                            $a_UpdateFPM[] = array('field' => 'Accepted', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                                            $a_UpdateFH = array(
                                                array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d', $mailData['ExtraHeaders']['udate'])),
                                            );
                                            $AcceptedPresent = true;
                                            $a_UIDSToSave[$FlowId][] = $UID;
                                        }
                                    } break;
                                    
                                case PEC_X_RICEVUTA_CONSEGNA:
                                    if(!$DeliveredPresent){
                                        writelog('N', "Scarico ricevuta {$mailData['Headers']['X-Ricevuta']}: {$mailData['Headers']['Subject']}");
                                        if(file_put_contents("$filepath/$filename.html", $mailData['Message'])){
                                            $a_Documentation10['Documentation'] = array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => "$filename.html");
                                            $a_UpdateFPM[] = array('field' => 'Delivered', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                                            $a_UpdateFPM[] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'N');
                                            $a_UpdateFH = array(
                                                array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d', $mailData['ExtraHeaders']['udate'])),
                                                array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 22, 'settype'=>'int'), //ResultId 22 (Notifica via PEC)
                                            );
                                            $DeliveredPresent = true;
                                            $a_UIDSToSave[$FlowId][] = $UID;
                                        }
                                    } break;
                                    
                                case PEC_X_RICEVUTA_MANCATA_CONSEGNA:
                                    if(!$AnomalyPresent){
                                        writelog('N', "Scarico ricevuta {$mailData['Headers']['X-Ricevuta']}: {$mailData['Headers']['Subject']}");
                                        if(file_put_contents("$filepath/$filename.html", $mailData['Message'])){
                                            $a_Documentation10['Documentation'] = array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => "$filename.html");
                                            $a_UpdateFPM[] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                                            $a_UpdateFH = array(
                                                array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 23, 'settype'=>'int'), //ResultId 23 (per tutti i casi in cui fallisce invio o consegna tramite pec)
                                            );
                                            $AnomalyPresent = true;
                                            $a_UIDSToSave[$FlowId][] = $UID;
                                        }
                                    } break;
                                    
                                case PEC_X_RICEVUTA_PREAVVISO_ERRORE_CONSEGNA:
                                    writelog('N', "Scarico ricevuta {$mailData['Headers']['X-Ricevuta']}: {$mailData['Headers']['Subject']}");
                                    if(file_put_contents("$filepath/$filename.html", $mailData['Message'])){
                                        if(!$DeliveredPresent){
                                            $a_Documentation10['Documentation'] = array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => "$filename.html");
                                            
                                            if($AnomalyForeWarning){
                                                $a_UpdateFPM[] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                                                $a_UpdateFH = array(
                                                    array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 23, 'settype'=>'int'), //ResultId 23 (per tutti i casi in cui fallisce invio o consegna tramite pec)
                                                );
                                                $AnomalyPresent = true;
                                            } else {
                                                $a_UpdateFPM[] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'T');
                                            }
                                        }
                                        
                                        $a_UIDSToSave[$FlowId][] = $UID;
                                    } break;
                            }
                            
                            if(!empty($a_UpdateFH)){
                                $rs->Update('FineHistory', $a_UpdateFH, 'FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']);
                            }
                            if(!empty($a_UpdateFPM)){
                                $rs->Update('FlowPecMails', $a_UpdateFPM, 'Id='.$r_FlowPecMails['Id']);
                            }
                            if(!empty(($a_Documentation10['Documentation']))){
                                $r_FineDocumentation10 = $rs->getArrayLine($rs->Select('FineDocumentation', 'FineId='.$FineId.' AND DocumentationTypeId=10'));
                                
                                if($r_FineDocumentation10){
                                    $rs->Update('FineDocumentation', $a_Documentation10, 'FineId='.$FineId.' AND DocumentationTypeId=10');
                                } else {
                                    $rs->Insert('FineDocumentation', $a_Documentation10);
                                }
                            }
                            
                            if(($DeliveredPresent || $AnomalyPresent) && $AcceptedPresent){
                                $FineNotification = $rs->getArrayLine($rs->Select('FineNotification', 'FineId='.$FineId));
                                $FineHistoryFlow = $rs->getArrayLine($rs->Select('FineHistory', 'FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']));
                                
                                $a_FineNotification[] = array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'));
                                $a_FineNotification[] = array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$FineHistoryFlow['SendDate']);
                                
                                if($DeliveredPresent){
                                    $a_FineNotification[] = array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$FineHistoryFlow['SendDate']);
                                    $a_FineNotification[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>22,'settype'=>'int');
                                    $a_Fine = array(
                                        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>25,'settype'=>'int')
                                    );
                                } else {
                                    $a_FineNotification[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>23,'settype'=>'int');
                                    $a_Fine = array(
                                        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>23,'settype'=>'int')
                                    );
                                }
                                
                                //Non aggiorna lo stato del verbale se è già in uno stato
                                if(!in_array($Fine['StatusTypeId'], STATUSTYPEID_VERBALI_STATI_FINALI)){
                                    $rs->Update('Fine', $a_Fine, 'Id='.$FineId);
                                }
                                
                                if(!$FineNotification){
                                    $a_Flags = getFineNotificationFlags($Fine);
                                    
                                    $a_FineNotification[] = array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['PaymentProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['126BisProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['PresentationDocumentProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['LicensePointProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['HabitualProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['SuspensionLicenseProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['LossLicenseProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['InjunctionProcedure'],'settype'=>'int');
                                    $a_FineNotification[] = array('field'=>'ReminderAdditionalFeeProcedure','selector'=>'value','type'=>'int','value'=>$a_Flags['ReminderAdditionalFeeProcedure'],'settype'=>'int');
                                    
                                    $rs->Insert('FineNotification', $a_FineNotification);
                                    $rs->Delete('TMP_126BisProcedure', "FineId=".$FineId);
                                    $rs->Delete('TMP_InjunctionProcedure', "FineId=".$FineId);
                                    $rs->Delete('TMP_LicensePointProcedure', "FineId=".$FineId);
                                    $rs->Delete('TMP_PaymentProcedure', "FineId=".$FineId);
                                    $rs->Delete('TMP_PresentationDocumentProcedure', "FineId=".$FineId);
                                    $rs->Delete('TMP_ReminderAdditionalFeeProcedure', "FineId=".$FineId);
                                } else {
                                    $rs->Update('FineNotification', $a_FineNotification, 'FineId='.$FineId);
                                }
                            }
                        }
                    }
                }
                
                if(!empty($a_UIDSToSave)){
                    foreach($a_UIDSToSave as $UIDSFlowId => $a_UDISFlow){
                        $Flow = $rs->getArrayLine($rs->Select('Flow', "Id=$UIDSFlowId"));
                        
                        writeLog('S', "Sono state scaricate ".count($a_UDISFlow)." ricevute per il flusso ($cityid) {$Flow['Number']}/{$Flow['Year']}", $cityid, true);
                        writeLog('N', "Archivio ".count($a_UDISFlow)." ricevute per il flusso ($cityid) {$Flow['Number']}/{$Flow['Year']}");
                        
                        $zipName = $cityid.'_'.$Flow['Number'].'_'.$Flow['Year'].'_'.date('Y-m-d_H-i-s').'_'.count($a_UDISFlow).'.zip';
                        $mail->mailboxExportMails($a_UDISFlow, PECRECEIPT_FOLDER.'/'.$cityid.'/'.$zipName, true);
                    }
                }
                
                writeLog('N', "Cerco flussi PEC senza data di lavorazione o consegna per $cityid");
                
                $a_Flows = $rs->getResults($rs->Select('Flow', "CityId = '$cityid' AND (SendDate IS NULL OR ProcessingDate IS NULL) AND PrinterId=3"));
                foreach ($a_Flows as $flow){
                    
                    $CountFlowPecMails = $rs->getArrayLine($rs->SelectQuery("
                        SELECT
                        COUNT(CASE WHEN Delivered='N' AND Anomaly='N' AND COALESCE(SendError,'') = '' THEN 1 END) NotDelivered,
                        COUNT(CASE WHEN Accepted ='N' AND COALESCE(SendError,'') = '' THEN 1 END) NotAccepted
                        FROM FlowPecMails WHERE FlowId = {$flow['Id']}"));
                    
                    writeLog('N', "Ricevute assenti per flusso ($cityid) {$flow['Number']}/{$flow['Year']} -> Accettazione: {$CountFlowPecMails['NotAccepted']}, Consegna/Mancata consegna: {$CountFlowPecMails['NotDelivered']}");
                    
                    if(!$flow['ProcessingDate']){
                        if($CountFlowPecMails['NotAccepted'] <= 0){
                            writeLog('N', "Tutte le ricevute di accettazione acquisite, imposto flusso a LAVORATO");
                            $a_UpdateFlow = array(
                                array('field' => 'ProcessingDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                            );
                            $rs->Update('Flow', $a_UpdateFlow, 'Id='.$flow['Id']);
                        }
                    }
                    if(!$flow['SendDate']){
                        if($CountFlowPecMails['NotDelivered'] <= 0){
                            writeLog('N', "Tutte le ricevute di consegna/mancata consegna acquisite, imposto flusso a CONSEGNATO");
                            $a_UpdateFlow = array(
                                array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                            );
                            $rs->Update('Flow', $a_UpdateFlow, 'Id='.$flow['Id']);
                        }
                    }
                }
                
                $mail->mailboxClosing(true);
            }
            
            $UpdateLockedPage = array(
                array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
            );
            $rs->Update('LockedPage', $UpdateLockedPage, "Title='scaricaRicevutePEC_$cityid'");
        }
    }
} else {
    writeLog('W', "Script non abilitato all'esecuzione su ambienti di sviluppo.");
}



