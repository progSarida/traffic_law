<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(CLS."/cls_mail.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");

ini_set('max_execution_time', 3000);
//dato che al momento l'operazione è laboriosa, aumentiamo la memoria limite a 2 GB
ini_set('memory_limit', '2048M');

global $rs;

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
/////////////////////////////////////////////////////////////////////////////////////////

//Crea l'immagine della notifica/////////////////////////////////////////////////////////
function saveDocument(string $title, string $text, string $path, string $author){
    try {
        $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($author);
        $pdf->SetSubject('');
        $pdf->SetTitle($title);
        $pdf->SetKeywords('');
        $pdf->SetMargins(10, 8, 10);
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 8));
        $pdf->SetFont('helvetica', '', 8, '', true);
        $page_format = array('Rotate' => 45);
        $pdf->AddPage('P', $page_format);
        //Rimuove gli stili HTML dalla stringa perchè potrebbero compromettere il testo
        $strippedHTML = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $text);
        $pdf->writeHTML($strippedHTML, true, false, true, false, '');
        $pdf->Output($path.'.pdf', "F");
        
        if (file_exists($path.'.pdf')){
            if (class_exists('Imagick')){
                $img = new Imagick();
                $img->setResolution(300,300);
                $img->readImage($path.'.pdf');
                $img->setImageFormat('jpeg');
                $img->writeImage($path.'.jpg');
                $img->clear();
                $img->destroy();
            } else {
                trigger_error("Errore nella creazione dell'immagine della ricevuta $path.jpg : classe Imagick inesistente", E_USER_WARNING);
                return false;
            }
            //Elimina il pdf
            unlink($path.'.pdf');
        } else {
            trigger_error("Errore nella creazione dell'immagine della ricevuta $path.jpg : file pdf da convertire non trovato", E_USER_WARNING);
            return false;
        }
        
    } catch (Exception $e){
        trigger_error("Errore nella creazione dell'immagine della ricevuta $path : $e", E_USER_WARNING);
        return false;
    }
    return true;
}
/////////////////////////////////////////////////////////

if (!is_dir(PECRECEIPT_FOLDER)) {
    mkdir(PECRECEIPT_FOLDER, 0777);
}

if (!is_dir(PECRECEIPT_FOLDER . "/" . $_SESSION['cityid'])) {
    mkdir(PECRECEIPT_FOLDER . "/" . $_SESSION['cityid'], 0777);
}

$a_ReceiptPrefixes = array(
    'accettazione' =>               'ACCETTAZIONE',
    'avvenuta-consegna' =>          'CONSEGNA',
    'errore-consegna' =>            'AVVISO_DI_MANCATA_CONSEGNA',
    'preavviso-errore-consegna' =>  'AVVISO_DI_MANCATA_CONSEGNA_PER_SUP_TEMPO_MASSIMO'
);

$Filters = CheckValue('Filters', 's');
$FlowId = CheckValue('FlowId', 'n');

$str_Warning = '';

$rs_Flow = $rs->Select('Flow', "Id=$FlowId");
$r_Flow = $rs->getArrayLine($rs_Flow);
$CityId = $r_Flow['CityId'];
$a_uidsToSave = array();

//Gestore mail: inizializzazione///////////////////////////////////////////////////////////
$rs_CustomerMail = $rs->Select('CustomerMailAuthentication', "ConfigType=1 AND CityId='".$_SESSION['cityid']."'");
$r_CustomerMail = mysqli_num_rows($rs_CustomerMail) > 0 ? mysqli_fetch_assoc($rs_CustomerMail) : array();
try{
    $mail = new MAIL_HANDLER($r_CustomerMail);
} catch (Exception $e) {
    $_SESSION['Message']['Error'] = 'MAIL_HANDLER: '.$e->getMessage();
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_flow.php', 'FlowId' => $FlowId), 'mgmt_flow_pecdetail_upd.php'.$Filters));
    DIE;
}

//Tenta di aprire il server in entrata
$testmail = $mail->mailboxOpening();

if($testmail !== true){
    $_SESSION['Message']['Error'] = 'Errore server di posta in entrata: '.$testmail.'';
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_flow.php', 'FlowId' => $FlowId), 'mgmt_flow_pecdetail_upd.php'.$Filters));
    DIE;
}
////////////////////////////////////////////////////////////////////////


$selectFolder = $mail->mailboxSelectFolder($r_CustomerMail['IncomingMailbox']);

if ($selectFolder === true) {
    
    $rs->Start_Transaction();
    
    foreach($_POST['FlowPecMailsId'] as $Id){
        
        $a_UpdateFPM = array();
        
        $rs_FlowPecMails = $rs->Select('FlowPecMails', 'Id='.$Id);
        $r_FlowPecMails = mysqli_fetch_assoc($rs_FlowPecMails);
        $FineId = $r_FlowPecMails['FineId'];
        
        $rs_Fine = $rs->Select("Fine", "Id=$FineId");
        $Fine = $rs->getArrayLine($rs_Fine);
        
        $AcceptedPresent = $r_FlowPecMails['Accepted'] == 'S' ? true : false;
        $DeliveredPresent = $r_FlowPecMails['Delivered'] == 'S' ? true : false;
        $AnomalyPresent = $r_FlowPecMails['Anomaly'] == 'S' ? true : false;
        $AnomalyForeWarning = false;
        
        $filepath = NATIONAL_FINE."/".$_SESSION['cityid']."/".$FineId."/";
        
        $a_FineNotification = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
        );
        
        $a_Documentation10 = array(
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 10),
            array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
        );
        
        if(!$AcceptedPresent || (!$AnomalyPresent && !$DeliveredPresent)){
            $a_ReceiptMailIds = $mail->mailboxGetMailBySubject($r_FlowPecMails['MailSubject']);
            
            foreach($a_ReceiptMailIds as $mailId){
                $Header = $mail->mailboxGetMailHeader($mailId);
                $HeaderInfo = $mail->mailboxGetMailHeaderInfo($mailId);
                $MailBody = $mail->mailboxGetMailBody($mailId);
                
                if(isset($Header['X-Ricevuta'])){
                    $ReceiptPrefix = $a_ReceiptPrefixes[$Header['X-Ricevuta']];
                    
                    switch($Header['X-Ricevuta']){
                        //ACCETTAZIONE
                        case 'accettazione' :
                            //Ha trovato l'accettazione: salva la notifica nella cartella del verbale e aggiorna la colonna sul record
                            $a_uidsToSave[] = $mailId;
                            $filename = 'Ricevuta_InvioPEC_Id_'.$r_FlowPecMails['Id'].'_'.$ReceiptPrefix;
                            //FILE JPG
                            if (!saveDocument($ReceiptPrefix, $MailBody, $filepath.$filename, $CityId)){
                                $str_Warning .= $r_FlowPecMails['Id'].': Errore nella creazione del file della notifica '.$r_CustomerMail['PrefixAccepted'].'.<br>';
                            }
                            
                            $a_UpdateFPM['Accepted'] = array('field' => 'Accepted', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                            
                            $a_UpdateFH = array(
                                array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d', $HeaderInfo->udate)),
                            );
                            $rs->Update('FineHistory', $a_UpdateFH, 'FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']);
                            
                            $AcceptedPresent = true;
                            break;
                        
                        //CONSEGNA
                        case 'avvenuta-consegna' :
                            //Ha trovato la consegna: salva la notifica nella cartella del verbale e aggiorna la colonna sul record
                            $a_uidsToSave[] = $mailId;
                            $filename = 'Ricevuta_InvioPEC_Id_'.$r_FlowPecMails['Id'].'_'.$ReceiptPrefix;
                            //FILE JPG
                            if (!saveDocument($ReceiptPrefix, $MailBody, $filepath.$filename, $CityId)){
                                $str_Warning .= $r_FlowPecMails['Id'].': Errore nella creazione del file della notifica '.$ReceiptPrefix.'.<br>';
                            }
                            
                            $rs_FineDocumentation10 = $rs->Select('FineDocumentation', 'FineId='.$FineId.' AND DocumentationTypeId=10');
                            $a_Documentation10[] = array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $filename.'.jpg');
                            if(mysqli_num_rows($rs_FineDocumentation10) > 0){
                                $rs->Update('FineDocumentation', $a_Documentation10, 'FineId='.$FineId.' AND DocumentationTypeId=10');
                            } else {
                                $rs->Insert('FineDocumentation', $a_Documentation10);
                            }
                            $a_UpdateFPM['Delivered'] = array('field' => 'Delivered', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                            
                            $a_UpdateFH = array(
                                array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d', $HeaderInfo->udate)),
                                //ResultId 22 (Notifica via PEC)
                                array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 22, 'settype'=>'int'),
                            );
                            $rs->Update('FineHistory', $a_UpdateFH, 'FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']);
                            
                            $DeliveredPresent = true;
                            break;
                            
                        //MANCATA CONSEGNA
                        case 'errore-consegna':
                            //Ha trovato la mancata consegna: salva la notifica nella cartella del verbale e aggiorna la colonna sul record
                            $a_uidsToSave[] = $mailId;
                            $filename = 'Ricevuta_InvioPEC_Id_'.$r_FlowPecMails['Id'].'_'.$ReceiptPrefix;
                            //FILE JPG
                            if (!saveDocument($ReceiptPrefix, $MailBody, $filepath.$filename, $CityId)){
                                $str_Warning .= $r_FlowPecMails['Id'].': Errore nella creazione del file della notifica '.$ReceiptPrefix.'.<br>';
                            }
                            
                            $rs_FineDocumentation10 = $rs->Select('FineDocumentation', 'FineId='.$FineId.' AND DocumentationTypeId=10');
                            $a_Documentation10[] = array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $filename.'.jpg');
                            if(mysqli_num_rows($rs_FineDocumentation10) > 0){
                                $rs->Update('FineDocumentation', $a_Documentation10, 'FineId='.$FineId.' AND DocumentationTypeId=10');
                            } else {
                                $rs->Insert('FineDocumentation', $a_Documentation10);
                            }
                            $a_UpdateFPM['Anomaly'] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                            
                            $a_UpdateFH = array(
                                //ResultId 23 (per tutti i casi in cui fallisce invio o consegna tramite pec)
                                //TODO se necessario va creato un ResultId per anomalia mancata consegna, dato che non è per forza detto che l'indirizzo sia inesatto
                                array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 23, 'settype'=>'int'),
                            );
                            $rs->Update('FineHistory', $a_UpdateFH, 'FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']);
                            
                            $AnomalyPresent = true;
                            break;
                            
                        //MANCATA CONSEGNA PER SUP. TEMPO MASSIMO
                        case 'preavviso-errore-consegna' :
                            if(!$AnomalyForeWarning){
                                //Ha trovato il primo preavviso di mancata consegna dopo 12 ore: aggiorna la colonna sul record
                                $a_uidsToSave[] = $mailId;
                                $a_UpdateFPM['Anomaly'] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'T');
                                $AnomalyForeWarning = true;
                            } elseif(!$DeliveredPresent) {
                                //Ha trovato il secondo preavviso di mancata consegna dopo 24 ore: salva la notifica nella cartella del verbale e aggiorna la colonna sul record
                                $a_uidsToSave[] = $mailId;
                                $filename = 'Ricevuta_InvioPEC_Id_'.$r_FlowPecMails['Id'].'_'.$ReceiptPrefix;
                                //FILE JPG
                                if (!saveDocument($ReceiptPrefix, $MailBody, $filepath.$filename, $CityId)){
                                    $str_Warning .= $r_FlowPecMails['Id'].': Errore nella creazione del file della notifica '.$ReceiptPrefix.'.<br>';
                                }
                                
                                $rs_FineDocumentation10 = $rs->Select('FineDocumentation', 'FineId='.$FineId.' AND DocumentationTypeId=10');
                                $a_Documentation10[] = array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $filename.'.jpg');
                                if(mysqli_num_rows($rs_FineDocumentation10) > 0){
                                    $rs->Update('FineDocumentation', $a_Documentation10, 'FineId='.$FineId.' AND DocumentationTypeId=10');
                                } else {
                                    $rs->Insert('FineDocumentation', $a_Documentation10);
                                }
                                $a_UpdateFPM['Anomaly'] = array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'S');
                                
                                $a_UpdateFH = array(
                                    //ResultId 23 (tutte le volte che fallisce l'invio o la consegna tramite pec)
                                    //TODO se necessario va creato un ResultId per anomalia mancata consegna, dato che non è per forza detto che l'indirizzo sia inesatto
                                    array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => 23, 'settype'=>'int'),
                                );
                                $rs->Update('FineHistory', $a_UpdateFH, 'FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']);
                                
                                $AnomalyPresent = true;
                            }
                            break;
                    }
                }
            }
            
            //Se ha trovato la consegna annulla la valorizzazione della mancata consegna,
            //questa situazione si può verificare nel caso esista un preavviso di mancata consegna ma poi si riceve l'avvenuta consegna
            if($DeliveredPresent){
                unset($a_UpdateFPM['Anomaly']);
            }
        }
        
        if(($DeliveredPresent || $AnomalyPresent) && $AcceptedPresent){
            $rs_FineNotification = $rs->Select('FineNotification', 'FineId='.$FineId);
            $FineNotification = mysqli_num_rows($rs_FineNotification) > 0 ? true : false;
            
            $rs_FineHistorySD = $rs->SelectQuery('SELECT SendDate From FineHistory WHERE FlowId='.$r_FlowPecMails['FlowId'].' AND FineId='.$FineId.' AND TrespasserId='.$r_FlowPecMails['TrespasserId']);
            $SendDate = mysqli_fetch_assoc($rs_FineHistorySD)['SendDate'];
            
            $a_FineNotification[] = array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'));
            $a_FineNotification[] = array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$SendDate);
            //Nella NotificationDate ci andrebbe la data in cui è arrivata sul server del destinatario o la data della mancata consegna
            //condizionarla sul caso positivo di consegna arrivata
            if($DeliveredPresent){
                $a_FineNotification[] = array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$SendDate);
                //Se c'è la consegna, mette ResultId 22 (Notifica via PEC)
                $a_FineNotification[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>22,'settype'=>'int');
                //Metto il verbale in stato notificato
                $a_Fine = array(
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>25,'settype'=>'int')
                );
            } else {
                //Altrimenti presuppone sia mancata consegna e mette ResultId 23 (quanto fallisce invio o consegna via PEC)
                $a_FineNotification[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>23,'settype'=>'int');
                //Metto il verbale in stato non notificato
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

        if(!empty($a_UpdateFPM)){
            $rs->Update('FlowPecMails', $a_UpdateFPM, 'Id='.$Id);
        }
    }
    
    //Esporta le ricevute trovate in formato .eml in uno zip e le elimina dalla casella di posta
    if(!empty($a_uidsToSave)){
        $zipName = $_SESSION['cityid'].'_'.$r_Flow['Number'].'_'.$r_Flow['Year'].'_'.date('Y-m-d_H-i-s').'_'.count($a_uidsToSave).'.zip';
        $mail->mailboxExportMails($a_uidsToSave, PECRECEIPT_FOLDER.'/'.$_SESSION['cityid'].'/'.$zipName, true);
    }
    
    //Se le ricevute di accettazione sono tutte presenti, mette il flusso in stato LAVORATO
    $rs_NotAccepted = $rs->Select('FlowPecMails', "FlowId=".$FlowId." AND Accepted='N'");
    if (mysqli_num_rows($rs_NotAccepted) <= 0){
        $a_UpdateFlow = array(
            array('field' => 'ProcessingDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
        );
        if(empty($r_Flow['ProcessingDate'])){
            $rs->Update('Flow', $a_UpdateFlow, 'Id='.$FlowId);
        }
    }
    
    //Se le ricevute di consegna sono tutte presenti, mette il flusso in stato COMPLETATO
    $rs_NotDelivered = $rs->Select('FlowPecMails', "FlowId=".$FlowId." AND Delivered='N' AND Anomaly='N' AND COALESCE(SendError,'') = ''");
    if (mysqli_num_rows($rs_NotDelivered) <= 0){
        $a_UpdateFlow = array(
            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
        );
        if(empty($r_Flow['SendDate'])){
            $rs->Update('Flow', $a_UpdateFlow, 'Id='.$FlowId);
        }
    }
    
    $rs->End_Transaction();
    
} else {
    $_SESSION['Message']['Error'] = 'Errore nell\'apertura della casella di posta: '.$selectFolder.'';
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_flow.php', 'FlowId' => $FlowId), 'mgmt_flow_pecdetail_upd.php'.$Filters));
    DIE;
}

$mail->mailboxClosing(true);

if($str_Warning === ''){
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
} else $_SESSION['Message']['Warning'] = $str_Warning;

header("location: ".impostaParametriUrl(array('P' => 'mgmt_flow.php', 'FlowId' => $FlowId), 'mgmt_flow_pecdetail_upd.php'.$Filters));
