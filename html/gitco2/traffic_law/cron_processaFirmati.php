<?php
use function Complex\subtract;

require_once ("_path.php");
require_once (INC . "/parameter.php");
require_once (CLS . "/cls_db.php");
require_once (CLS . '/cls_literal_number.php');
require_once (CLS . '/cls_mail.php');
require_once (INC . "/cli_function.php");
require_once (INC."/function_postalCharge.php");

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

const SIGN_SUFFIXES = array(
    '-signed',
    '_signed');
const SIGN_NOTIFICATION_SUFFIXES = array(
    '_N-signed.'.PDFA,
    '_N_signed.'.PDFA);
const SIGN_REPLACEMENTS = array('','');
const CONNECTION_ATTEMPTS = 3;
const TEST_PEC = 'ovunque.svil@pec.it';
const PEC_SEND_LIMIT = 200;
const MAILBOX_MINIMUM_FREESPACE_MB = 200;

$rs = new CLS_DB();

function writeLog(String $tipo, String $messaggio, string $cityId = '', bool $registraEvento = false){
    global $rs;
    $serverity = null;
    
    switch($tipo){
        case 'N': trigger_error("<PROCESSAFIRMATI> DEBUG -> $messaggio", E_USER_NOTICE); $serverity = 'INFO'; break;
        case 'W': trigger_error("<PROCESSAFIRMATI> ATTENZIONE -> $messaggio", E_USER_WARNING); $serverity = 'WARNING'; break;
        case 'D': trigger_error("<PROCESSAFIRMATI> ERRORE -> $messaggio", E_USER_WARNING); $serverity = 'ERROR'; break;
        case 'S': trigger_error("<PROCESSAFIRMATI> SUCCESS -> $messaggio", E_USER_WARNING); $serverity = 'SUCCESS'; break;
        default : trigger_error("<PROCESSAFIRMATI> DEBUG -> $messaggio", E_USER_NOTICE); $serverity = 'INFO'; break;
    }
    
    if($registraEvento){
        $a_Insert = array(
            array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => 'Invio automatico atti PEC'),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $cityId),
            array('field' => 'Message', 'selector' => 'value', 'type' => 'str', 'value' => substr($messaggio, 0, 1000)),
            array('field' => 'Severity', 'selector' => 'value', 'type' => 'str', 'value' => $serverity),
            array('field' => 'EventDate', 'selector' => 'value', 'type' => 'str', 'value' => date('Y-m-d H:i:s')),
        );
        
        $rs->Insert('Events', $a_Insert);
    }
}

//Crea il gestore delle mail e controlla l' autenticazione
function checkMailConnection($customer, $attempts){
    $mail=null;
    global $rs;
    $rs_CustomerMail = $rs->Select('CustomerMailAuthentication', "ConfigType=1 AND CityId='" . $customer['CityId'] . "'");
    $r_CustomerMail = mysqli_num_rows($rs_CustomerMail) > 0 ? mysqli_fetch_assoc($rs_CustomerMail) : array();
  
    try{
        $mail = new MAIL_HANDLER($r_CustomerMail);
    
        for($i=0; $i<$attempts; $i++){
            writeLog('N', "Tentativo di connessione n.".($i+1)." al server di posta {$r_CustomerMail['OutgoingMailServer']}...");
            if(($testConnection = $mail->testOutgoingConnection()) !== true){
                writeLog('D', "ERRORE SERVER DI POSTA IN USCITA: $testConnection");
                sleep(3);
            } else {
                writeLog('N', "Connessione riuscita. Verifica dello spazio disponibile.");
                break;
            }
        }
        
        if($testConnection === true){
            if(($inboxError = $mail->mailboxOpening()) === true){
                if($space = $mail->mailboxGetSpaceUsageAndLimit()){
                    if($space['usage'] < ($space['limit'] - (MAILBOX_MINIMUM_FREESPACE_MB * 1024))){
                        writeLog('N', "Spazio sufficente. Richiesto: ".MAILBOX_MINIMUM_FREESPACE_MB." MB, in uso: ".(round($space['usage']/1024, 2))." MB, limite: ".(round($space['limit']/1024, 2))." MB.");
                        
                        if($r_CustomerMail['ReplyToManagerPEC'] == 1 && !empty($customer['ManagerPEC'])){
                            $mail->addReplyTo($customer['ManagerPEC'], $customer['ManagerName']);
                            writeLog('N', 'Imposta "Rispondi a" all\'indirizzo PEC dell\'ente è attivo. Indirizzo: '.$customer['ManagerPEC']. ' Nome visualizzato: '.$customer['ManagerName']);
                        }
                        return $mail;
                    } else writeLog('D', "SPAZIO INSUFFICENTE PER POTER EFFETTUARE NUOVI INVII, SONO RICHIESTI ALMENO ".MAILBOX_MINIMUM_FREESPACE_MB." MB LIBERI. LIMITE: ".(round($space['limit']/1024, 2))." MB, IN USO: ".(round($space['usage']/1024, 2))." MB.", $customer['CityId'], true);
                } else writeLog('D', "IMPOSSIBILE DETERMINARE LO SPAZIO DISPONIBILE", $customer['CityId'], true);
            } else writeLog('D', "IMPOSSIBILE CONNETTERSI AL SERVER DI POSTA IN ENTRATA PER VERIFICARE LO SPAZIO: ".$inboxError, $customer['CityId'], true);
        } else writeLog('D', "CONNESSIONE AL SERVER DI POSTA IN USCITA {$r_CustomerMail['OutgoingMailServer']} FALLITA", $customer['CityId'], true);
            
        return null;
    } catch (Exception $e){
        writeLog('D', 'ERRORE NEL PROCESSO AUTOMATICO DI INVIO ATTI TRAMITE PEC', $customer['CityId'], true);
        writeLog('D', $e->getMessage());
        return null;
    }
}

//Invia le pec per i fineid passati  e crea il flusso
function sendMailAndCreateFlow(array $fines,string $CityId,string $FixedRecipient,array $r_Customer,MAIL_HANDLER $mail, $attempts){
    global $rs;
    $str_Warning = '';
    $number=count($fines);
    $FormTypeId = 42; //Testo per corpo mail della notifica dei verbali PEC
    $a_Documentation_N_Signed = array();
    $a_Documentation = array();
    $a_FineHistory = array();
    $a_FlowPecMails = array();
    $a_I_FineDocumentation = array();
    $a_U_Fine = array();
    $a_I_FineHistoryN = array();
    $a_I_FineHistoryFlow = array();
    
    $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$CityId."' AND Year=".date('Y'));
    $flow = mysqli_fetch_array($flows);
    $int_FlowNumber = $flow['Number']+1;
    $FileName = "Flusso_".$int_FlowNumber."_PEC_Ita_".$CityId."_".date("Y-m-d")."_".date("H-i-s")."_".$number;
    $FileNameZip = $FileName.".zip";
    $FlowPath = NATIONAL_FLOW."/".$CityId."/";

    $str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" .$CityId . "'" : "Id='" . $CityId . "'";
    $rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
    $a_ProtocolLetterLocality = array();
    
    while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
    }
    foreach ($fines as $FineId){
        $a_Documentation_N_Signed[$FineId] = array();
        $a_Documentation[$FineId] = array();
        $rs_Fine = $rs->Select('V_ViolationAll', "Id=" . $FineId);

        while ($r_Fine = mysqli_fetch_array($rs_Fine)){
            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);
        
            $rs_FineHistoryPEC = $rs->Select("FineHistory", "FineId=$FineId AND NotificationTypeId=15 AND TrespasserId=".$r_Fine['TrespasserId']);
            $r_FineHistoryPEC = mysqli_fetch_array($rs_FineHistoryPEC);
            
            $rs_Documentation = $rs->Select("FineDocumentation", "FineId=$FineId AND DocumentationTypeId=14 AND Documentation LIKE '%".substr($r_FineHistoryPEC['Documentation'], 31,10)."%'");
            $r_Documentation = mysqli_fetch_array($rs_Documentation);
        
            $n_LanguageId = 1;
            $n_NationalityId = 1;
        
            //  Nome del file//////////////////////////////
            $Documentation = $r_FineHistoryPEC['Documentation'];
            $a_Documentation[$FineId][] = $Documentation;
            $Documentation_N_Signed = $r_Documentation['Documentation'];
            $a_Documentation_N_Signed[$FineId][] = $Documentation_N_Signed;
            //MAIL////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $a_Recipient = array();
            $a_Attachements = array();
            $RecipientName = StringOutDB((isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '').$trespasser['Name'] . ' ' . $trespasser['Surname']);
        
            if (PRODUCTION){
                $a_Recipient[$r_Fine['PEC']] = $RecipientName;
            } else {
                $a_Recipient[$FixedRecipient] = $RecipientName;
            }
        
            $a_Attachements[$Documentation] = NATIONAL_FINE . "/" . $CityId . "/" . $FineId . "/" . $Documentation;
            
            //Aggiunge la notifica firmata come allegato se è abilitata la gestione//////////////////////////////////////
            if ($r_Customer['EnableINIPECNotification'] == 1){
                $a_Attachements[$Documentation_N_Signed] = NATIONAL_FINE . "/" . $CityId . '/' .$FineId . "/" .$Documentation_N_Signed;
            }
        
            $subject = 'Atto amministrativo relativo ad una sanzione amministrativa prevista dal codice della strada: '.$r_Fine['ProtocolId'].'-'.$r_Fine['ProtocolYear'].' del '.str_replace('/', '.', DateOutDB($r_Fine['FineDate']));
        
            $InsertFlowPecMails = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
            );
        
            $FlowPecMailId = $rs->Insert('FlowPecMails', $InsertFlowPecMails);
            $a_FlowPecMails[] = $FlowPecMailId;
        
            $subject .= ' [Id:'.$FlowPecMailId.']';
        
            //TODO eventualmente, se le pec verranno inviate agli esteri, questa query va filtrata anche per la lingua del trasgressore, così da prendere i testi nella lingua giusta
            $r_FormDynamic = mysqli_fetch_assoc($rs->Select('FormDynamic', "CityId='$CityId' AND FormTypeId=$FormTypeId AND NationalityId=$n_NationalityId AND LanguageId=$n_LanguageId AND RuleTypeId=1"));
        
            $body = $r_FormDynamic['Content'];
        
            //Variabili/////////////////////////////////////////////////////////////////////////////
            $body = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $body);
            $body = str_replace("{ProtocolId}", $r_Fine['ProtocolId'], $body);
            $body = str_replace("{ProtocolYear}", $r_Fine['ProtocolYear'], $body);
            $body = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $body);
            $body = str_replace("{ManagerSector}", StringOutDB($r_Customer['ManagerSector']), $body);
            $body = str_replace("{ManagerZIP}", $r_Customer['ManagerZIP'], $body);
            $body = str_replace("{ManagerCity}", StringOutDB($r_Customer['ManagerCity']), $body);
            $body = str_replace("{ManagerProvince}", StringOutDB($r_Customer['ManagerProvince']), $body);
        
            writeLog('N', "Invio PEC a: {$r_Fine['PEC']} - Oggetto: $subject");
            
            $UpdateFlowPecMails = array(
                array('field' => 'MailSubject', 'selector' => 'value', 'type' => 'str', 'value' => substr($subject, 0, 200))
            );
        
            //Effettua un determinato numero di tentativi di invio
            for($i=0; $i<$attempts; $i++){
                writeLog('N', "Tentativo di invio n.".($i+1)."...");
                $SentMail = $mail->sendMail($a_Recipient, $subject, $body, true, $a_Attachements);
                if($SentMail !== true){
                    writeLog('D', "INVIO FALLITO: $SentMail. Nuovo tentativo...");
                    sleep(3);
                } else {
                    $messageId = $mail->getLastMessageID();
                    $UpdateFlowPecMails[] = array('field' => 'MessageId', 'selector' => 'value', 'type' => 'str', 'value' => $messageId);
                    writeLog('N', "PEC Inviata, MessageId: $messageId");
                    break;
                }
            }
            
            //Se fallisce l'invio salva l'errore
            if ($SentMail !== true){
                $SentMail = empty($SentMail) ? 'Anomalia non gestita' : $SentMail; //Fallback nel caso SentMail non venga valorizzato
                $UpdateFlowPecMails[] = array('field' => 'SendError', 'selector' => 'value', 'type' => 'str', 'value' => substr($SentMail, 0, 200));
            }
            
            $rs->Update('FlowPecMails', $UpdateFlowPecMails, "Id=$FlowPecMailId");
        
            //Fine
            $a_UpdateF = array(
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 20, 'settype' => 'int'),
            );
        
            $a_U_Fine[] = array('Data' => $a_UpdateF, 'FineId' => $r_Fine['Id']);
        
            //FineHistory Notifica
            $a_InsertFHN = array(
                array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 16, 'settype' => 'int'),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistoryPEC['CustomerFee'], 'settype' => 'flt'),
                array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistoryPEC['NotificationFee'], 'settype' => 'flt'),
                array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistoryPEC['ResearchFee'], 'settype' => 'flt'),
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['ControllerId'], 'settype' => 'int'),
                array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation_N_Signed),
            );
            $a_I_FineHistoryN[]=array('Data' => $a_InsertFHN, 'FineId' => $FineId);
        
            //FineHistory Flusso
            $a_InsertFHF = array(
                array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int'),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistoryPEC['CustomerFee'], 'settype' => 'flt'),
                array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistoryPEC['NotificationFee'], 'settype' => 'flt'),
                array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistoryPEC['ResearchFee'], 'settype' => 'flt'),
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['ControllerId'], 'settype' => 'int'),
                array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileNameZip),
            );
            //Se l'invio è fallito salva il ResultId
            if ($SentMail !== true){
                $a_InsertFHF[] = array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 23, 'settype' => 'int');
            }
        
            $a_I_FineHistoryFlow[]= array('Data' => $a_InsertFHF, 'FineId' => $FineId);
        }
    }



    //Se "Abilita la creazione e la firma digitale della relata di notifica dei verbali con contestuale invio degli atti tramite PEC" è attivo
    if ($r_Customer['EnableINIPECNotification'] == 1){
        //Inserisce i record in FineDocumentation
        foreach ($a_I_FineDocumentation as $Insert){
            $rs->Insert('FineDocumentation', $Insert['Data']);
        }
        foreach ($a_I_FineHistoryN as $Insert){
            $rs->Insert('FineHistory', $Insert['Data']);
        }
    }

    //Aggiorna lo stato dei verbali
    foreach ($a_U_Fine as $Insert){
        $rs->Update('Fine', $Insert['Data'], 'Id='.$Insert['FineId']);
    }
    //Iserisce i record in FineHistory per il flusso
    foreach ($a_I_FineHistoryFlow as $Insert){
        $a_FineHistory[] = $rs->Insert('FineHistory', $Insert['Data']);
    }

    //ZIP FLUSSO E GESTIONE DOCUMENTI/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $zip = new ZipArchive();
    
    if($zip->open($FlowPath . $FileNameZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)===true){
        //Se "Abilita notifica dei verbali dinamici tramite PEC" è attivo
        if ($r_Customer['EnableINIPECNotification'] == 1){
            //Aggiunge le notifiche firmate allo zip
            foreach ($a_Documentation_N_Signed as $DocFineId => $a_Doc){
                foreach ($a_Doc as $Doc){
                    $zip->addFile(NATIONAL_FINE . "/" . $CityId . "/". $DocFineId . "/". $Doc, $Doc);
                }
            }
        }

        //Aggiunge i verbali allo zip
        foreach ($a_Documentation as $DocFineId => $a_Doc){
            foreach ($a_Doc as $Doc){
              $zip->addFile(NATIONAL_FINE . "/" . $CityId . "/" . $DocFineId . "/" . $Doc, $Doc);
            }
        }

        $zip->close();
    } else $str_Warning .= 'Non è stato possibile creare l\'archivio del flusso, controllare che gli invii PEC siano andati a buon fine.<br>';
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //Inserisce il flusso in Flow e aggiorna i valori sulle altre tabelle////////////////////////////////////////////////////////
    $Zone0Number=$number;
    $aInsertFlow = array(
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
        array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
        array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
        array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>7,'settype'=>'int'),
        array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>17,'settype'=>'int'),
        array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>$number),
        array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
        array('field'=>'UploadDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
        array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$FileNameZip),
        array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>3,'settype'=>'int'),
        array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
    );
    $FlowId = $rs->Insert('Flow',$aInsertFlow);
    //////////////////////////////////////////////////////////////////////////////////////
    
    //Aggiorna FlowId sui record generati su FineHistory/////////////////////////////////
    foreach ($a_FineHistory as $FineHistoryId){
        $aUpdateHistory = array(
            array('field' => 'FlowId', 'selector' => 'value', 'type' => 'int', 'value' => $FlowId, 'settype' => 'int'),
        );
        $rs->Update('FineHistory', $aUpdateHistory, 'Id='.$FineHistoryId);
    }
    
    //Aggiorna FlowId sui record generati su FlowPecMails/////////////////////////////////
    foreach ($a_FlowPecMails as $FlowPecMailsId){
        $aUpdateFlowPecMails = array(
            array('field' => 'FlowId', 'selector' => 'value', 'type' => 'int', 'value' => $FlowId, 'settype' => 'int'),
        );
        $rs->Update('FlowPecMails', $aUpdateFlowPecMails, 'Id='.$FlowPecMailsId);
    }
  
    if ($str_Warning != ''){
        writeLog('W', $str_Warning);
    } else {
        writeLog('S', "Flusso PEC inviato: ($CityId) $int_FlowNumber/".date('Y').", N. atti: $number" , $CityId, true);
    }
}

// Aggiorna lo stato del verbale
function updateFineStatus($fineId, $documentationTypeId, $nonFirmato, $firmato){
    global $rs;
    $insertFineDocumentation = array(
        array('field' => 'FineId','selector' => 'value','type' => 'int','value' => $fineId,'settype' => 'int'),
        array('field' => 'Documentation','selector' => 'value','type' => 'str','value' => $firmato),
        array('field' => 'DocumentationTypeId','selector' => 'value','type' => 'int','value' => $documentationTypeId,'settype' => 'int'),
        array('field' => 'Attachment','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")));
    $rs->insert("FineDocumentation", $insertFineDocumentation);

    $updateFineHistory = array(array('field' => 'Documentation','selector' => 'value','type' => 'str','value' => $firmato));
    $rs->update("FineHistory", $updateFineHistory, "FineId=$fineId and Documentation='$nonFirmato'");
    writeLog('N', "Aggiornato verbale $fineId con documentationtype $documentationTypeId e cambiato file $nonFirmato in $firmato");
}

//INIZIO/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (! file_exists(SIGNED_FOLDER)){
    mkdir(SIGNED_FOLDER, 0775, true);
    chmod(SIGNED_FOLDER, 0775, true);
}
if (! file_exists(TOSIGN_FOLDER)){
    mkdir(TOSIGN_FOLDER, 0775, true);
    chmod(TOSIGN_FOLDER, 0775, true);
}

foreach (scandir(SIGNED_FOLDER) as $ente){
    if ($ente != '.' && $ente != '..'){
        writeLog('N', "Cerco file firmati per ente $ente");
        $contenuto = array_diff(scandir(SIGNED_FOLDER . '/' . $ente), array('.','..'));
        $customer = mysqli_fetch_array($rs->Select("Customer", "CityId='$ente'"));
        $finesToSend=array();
        $n_wrongSuffixDocs=0;
        $b_Locked = false;
        
        //Controlla se l'ente specificato dal nome della cartella è stato trovato sulla banca dati
        if($customer){
            //BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
            $a_LockTables = array("LockedPage WRITE");
            $rs->LockTables($a_LockTables);
            
            $rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$customer['CityId']}'");
            
            if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
                if ($r_Locked['Locked'] == 1) {
                    $b_Locked = true;
                } else {
                    $UpdateLockedPage = array(
                        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => 'processaFirmati'),
                    );
                    $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$customer['CityId']}'");
                }
            } else {
                $InsertLockedPage = array(
                    array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => FLOW_LOCKED_PAGE."_{$customer['CityId']}"),
                    array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                    array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => 'processaFirmati'),
                );
                $rs->Insert('LockedPage', $InsertLockedPage);
            }
            
            $rs->UnlockTables();
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            if($b_Locked){
                writeLog('D', "Procedura bloccata dall'utente '{$r_Locked['UserName']}'. L'operazione non verrà eseguita.", $customer['CityId'], true);
            } else {
                if(!empty($contenuto)){
                    $mail = checkMailConnection($customer, CONNECTION_ATTEMPTS);
                    
                    if($mail){
                        
                        foreach ($contenuto as $firmatoSenzaCartella){
                            writeLog('N', "Trovato file $firmatoSenzaCartella");
                            
                            $firmato = SIGNED_FOLDER . "/$ente/" . $firmatoSenzaCartella;
                            $firmatoSenzaSuffisso = str_replace(SIGN_SUFFIXES, SIGN_REPLACEMENTS, $firmatoSenzaCartella);
                                
                                if($firmatoSenzaSuffisso == $firmatoSenzaCartella){
                                    writeLog('W', "Il file $firmatoSenzaCartella è in un formato non supportato.");
                                    $n_wrongSuffixDocs++;
                                    continue;
                                }
                                
                            $firmaNotifica = strpos_arr($firmatoSenzaCartella, SIGN_NOTIFICATION_SUFFIXES, null, null, true);
                            
                            writeLog('N', "Notifica: $firmaNotifica");
                            
                            $daFirmare = TOSIGN_FOLDER . "/$ente/" . $firmatoSenzaSuffisso;
                            writeLog('N', "Il file da firmare: ".date("Y-m-d",filemtime($daFirmare)).", il file firmato: ". date("Y-m-d",filemtime($firmato)));
                            
                            if (!file_exists($daFirmare))
                            {
                                writeLog('W', "Il file da firmare: $daFirmare non esiste, elimino il file firmato $firmato");
                                unlink($firmato);
                            } else if($firmaNotifica && (date("Y-m-d",filemtime($daFirmare)) != date("Y-m-d",filemtime($firmato)))){
                                    writeLog('W', "La relata di notifica firmata non ha la stessa data di quella da firmare. ");
                                unlink($firmato);
                                unlink($daFirmare);
                            } else {
                                //10/03/2022 la query è stata semplificata per aumentare le prestazioni. La query originale attingeva da V_ViolationArticle che aveva dei filtri sui trasgressori con FineCreationDate
                                //quindi avrebbe ignorato quelli notificati su strada, però qui non serve perchè già la procedura di creazione verbale PEC filtra i verbali con allo stesso modo con V_ViolationAll
                                //e abbiamo il verbale del trasgressore non notificato su strada
                                $fines=$rs->SelectQuery("SELECT DISTINCT FineId FROM FineDocumentation WHERE Documentation='$firmatoSenzaSuffisso'");
                                if ($fine = mysqli_fetch_array($fines)){
                                    $posizioneFinaleFirmato = NATIONAL_FINE . "/$ente/{$fine['FineId']}/" . $firmatoSenzaCartella;
                                    unlink($daFirmare);
                                    writeLog('N', "Sposto $firmato in $posizioneFinaleFirmato");
                                    rename($firmato, $posizioneFinaleFirmato);
                                    if ($firmaNotifica){
                                        updateFineStatus($fine['FineId'], 14, $firmatoSenzaSuffisso, $firmatoSenzaCartella);
                                        //I verbali possono essere più di uno in caso di più trasgressori da notificare
                                        $numeroDocumentiNonFirmati = mysqli_num_rows($rs->Select("FineDocumentation", "DocumentationTypeId=13 and FineId={$fine['FineId']}"));
                                        $numeroDocumentiFirmati = mysqli_num_rows($rs->Select("FineDocumentation", "DocumentationTypeId=14  and FineId={$fine['FineId']}"));
                                        writeLog('N', "N.Documenti NON firmati: $numeroDocumentiNonFirmati /// N.Documenti firmati: $numeroDocumentiFirmati");
                                        if ($numeroDocumentiNonFirmati <= $numeroDocumentiFirmati){
                                            writeLog('N', "Tutto firmato, predisposizione invio della notifica per verbale {$fine['FineId']}");
                                            $finesToSend[]=$fine['FineId'];
                                            writeLog('N', "INVII TOTALI PREDISPOSTI: ".count($finesToSend));
                                        }
                                    } else {
                                        //Firma verbale
                                        updateFineStatus($fine['FineId'], 3, $firmatoSenzaSuffisso, $firmatoSenzaCartella);
                                    }
                                }
                            }
                            
                            if(count($finesToSend) >= PEC_SEND_LIMIT){
                                writeLog('W', "LIMITE DI ".PEC_SEND_LIMIT." INVII SUPERATO.");
                                break;
                            }
                        }
                    }
                    if($n_wrongSuffixDocs > 0){
                        writeLog('D', "Non è stato possibile elaborare $n_wrongSuffixDocs file per l'ente {$customer['CityId']}. Assicurarsi che i file caricati nella cartella siano firmati nel formato PAdES con suffissi: ".implode(', ', SIGN_SUFFIXES), $customer['CityId'], true);
                    }
                    if(count($finesToSend)>0){
                        sendMailAndCreateFlow($finesToSend,$customer['CityId'], TEST_PEC, $customer,$mail, CONNECTION_ATTEMPTS);
                    }
                } else {
                    writeLog('N', "Nessun file firmato trovato per {$customer['CityId']}.");
                }
                
                $UpdateLockedPage = array(
                    array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                    array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
                );
                $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$customer['CityId']}'");
            }
        } else {
            writeLog('W', "Ente $ente non registrato a sistema. L'operazione non verrà eseguita.");
        }
    }
}
