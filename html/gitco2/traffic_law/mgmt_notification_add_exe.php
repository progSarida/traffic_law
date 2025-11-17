<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

$a_ResultIds_For_ValidatedAddress = unserialize(RESULTIDS_FOR_VALIDATEDADDRESS);

$Filters            = CheckValue('Filters','s');
$FineId             = CheckValue('FineId','n');
$NotificationDate   = CheckValue('NotificationDate','s');
$LetterNumber       = CheckValue('LetterNumber','s');
$ReceiptNumber      = CheckValue('ReceiptNumber','s');
$ResultId           = CheckValue('ResultId','n');
$ValidatedAddress   = !in_array($ResultId, $a_ResultIds_For_ValidatedAddress) ? 0 : CheckValue('ValidatedAddress','n');

$V_FineTariff = new CLS_VIEW(V_FINETARIFF);
$rs_FineTariff = $rs->SelectQuery($V_FineTariff->generateSelect("Id=$FineId"));
$r_FineTariff = $rs->getArrayLine($rs_FineTariff);

$PaymentProcedure = 
$ReminderAdditionalFeeProcedure = 
$BisProcedure = 
$PresentationDocumentProcedure =
$LicensePointProcedure =
$InjunctionProcedure = 
$HabitualProcedure = 
$SuspensionLicenseProcedure = 
$LossLicenseProcedure = 0;

//Oltre a fare la verifica su tmp, verifica anche se il verbale risulta pagato (stato 30)
$rs_PaymentProcedure = $rs->Select("TMP_PaymentProcedure", "FineId=". $FineId);
if(mysqli_num_rows($rs_PaymentProcedure)==0){
    $rs_Fine = $rs->SelectQuery("SELECT StatusTypeId FROM Fine WHERE Id=$FineId");
    $PaymentProcedure = $rs->getArrayLine($rs_Fine)['StatusTypeId'] == 30 ? 0 : 1;
} else $PaymentProcedure = 0;

//Verifica se almeno un trasgressore ha la notifica su strada, in tal caso lascia il valore a 0
$rs_126BisProcedure = $rs->Select("TMP_126BisProcedure", "FineId=". $FineId);
if(mysqli_num_rows($rs_126BisProcedure)==0){
    $rs_FineTrespasser = $rs->SelectQuery("SELECT FineNotificationType FROM FineTrespasser WHERE FineId=$FineId AND FineNotificationType = 1");
    $BisProcedure = mysqli_num_rows($rs_FineTrespasser) > 0 ? 0 : $r_FineTariff['126Bis'];
} else $BisProcedure = 0;

//In questo caso il valore è opposto, se c'è il record su TMP è SI, altrimenti NO
$rs_ReminderAdditionalFeeProcedure = $rs->Select("TMP_ReminderAdditionalFeeProcedure", "FineId=". $FineId);
$ReminderAdditionalFeeProcedure = (mysqli_num_rows($rs_ReminderAdditionalFeeProcedure)==0) ? 0 : 1;

//Se non c'è TMP deve tener conto del flag su ArticleTariff o della gestione avviso bonario
$rs_PresentationDocumentProcedure = $rs->Select("TMP_PresentationDocumentProcedure", "FineId=". $FineId);
$PresentationDocumentProcedureAvvisoBonario = 0;
if(mysqli_num_rows($rs_PresentationDocumentProcedure)==0){
    $rs_Fine = $rs->SelectQuery("SELECT KindCreateDate,KindSendDate FROM Fine WHERE Id=$FineId");
    $r_Fine = mysqli_fetch_array($rs_Fine);
    $rs_Article = $rs->Select('V_FineArticle', "Id=" . $FineId);
    $r_Article = mysqli_fetch_array($rs_Article);
    if (isset($r_Fine['KindCreateDate']) && isset($r_Fine['KindSendDate'])
        && (($r_Article['Article']=193 AND $r_Article['Paragraph']='2') || ($r_Article['Article']=80 AND $r_Article['Paragraph']='14'))) {
            $PresentationDocumentProcedureAvvisoBonario = 1;
        }
}
$PresentationDocumentProcedure = (mysqli_num_rows($rs_PresentationDocumentProcedure)==0) ? 
($r_FineTariff['PresentationDocument'] || $PresentationDocumentProcedureAvvisoBonario ? 1 : 0) : 0;
        
        
$rs_LicensePointProcedure = $rs->Select("TMP_LicensePointProcedure", "FineId=". $FineId);
$LicensePointProcedure = (mysqli_num_rows($rs_LicensePointProcedure)==0) ? $r_FineTariff['LicensePoint'] : 0;

$rs_InjunctionProcedure = $rs->Select("TMP_InjunctionProcedure", "FineId=". $FineId);
$InjunctionProcedure = (mysqli_num_rows($rs_InjunctionProcedure)==0) ? 1 : 0;

$HabitualProcedure = $r_FineTariff['Habitual'];
$SuspensionLicenseProcedure = $r_FineTariff['SuspensionLicense'];
$LossLicenseProcedure = $r_FineTariff['LossLicense'];

$a_FineNotification = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
    array('field'=>'SendDate','selector'=>'field','type'=>'date'),
    array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'ReceiptNumber','selector'=>'value','type'=>'str','value'=>$ReceiptNumber),
    array('field'=>'LetterNumber','selector'=>'value','type'=>'str','value'=>$LetterNumber),
    array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
    array('field'=>'ValidatedAddress','selector'=>'value','type'=>'int','value'=>$ValidatedAddress,'settype'=>'int'),
    array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
    array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
    array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>$PresentationDocumentProcedure,'settype'=>'int'),
    array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>$LicensePointProcedure,'settype'=>'int'),
    array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>$HabitualProcedure,'settype'=>'int'),
    array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>$SuspensionLicenseProcedure,'settype'=>'int'),
    array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>$LossLicenseProcedure,'settype'=>'int'),
    array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>$InjunctionProcedure,'settype'=>'int'),
    array('field'=>'ReminderAdditionalFeeProcedure','selector'=>'value','type'=>'int','value'=>$ReminderAdditionalFeeProcedure,'settype'=>'int'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
);

$a_FineHistory = array(
    array('field'=>'SendDate','selector'=>'field','type'=>'date'),
    array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
);

//se notifico su strada, via messo o d'ufficio e non esiste il record di tipo 6 su FineHistory lo devo inserire
if($ResultId<10  || $ResultId == 22 || $ResultId == 24){
    $StatusTypeId = 25;
    array_push($a_FineNotification, array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>DateInDB($NotificationDate)));
    if(isset($NotificationDate))
        array_push($a_FineHistory,array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>DateInDB($NotificationDate)));

}else{
    $StatusTypeId = 23;
}


$a_Fine = array(
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
);

$rs->Start_Transaction();

$rs->Update('Fine',$a_Fine, "Id=$FineId AND StatusTypeId NOT IN(".implode(',', STATUSTYPEID_VERBALI_STATI_FINALI).")");

$rs->Insert('FineNotification',$a_FineNotification);
$rs->Delete('TMP_126BisProcedure', "FineId=".$FineId);
$rs->Delete('TMP_InjunctionProcedure', "FineId=".$FineId);
$rs->Delete('TMP_LicensePointProcedure', "FineId=".$FineId);
$rs->Delete('TMP_PaymentProcedure', "FineId=".$FineId);
$rs->Delete('TMP_PresentationDocumentProcedure', "FineId=".$FineId);
$rs->Delete('TMP_ReminderAdditionalFeeProcedure', "FineId=".$FineId);

//se FineHistory esiste lo aggiorno, altrimenti lo inersisco
$rs_FineHistory = $rs->Select('FineHistory', "FineId=".$FineId." AND NotificationTypeId=6");
if (mysqli_num_rows($rs_FineHistory)>0){
    $rs->Update('FineHistory',$a_FineHistory, "FineId=".$FineId." AND NotificationTypeId=6 AND ResultId is null");
}
else {
    //se notifico su strada, via messo o d'ufficio e non esiste il record di tipo 6 su FineHistory lo devo inserire
    // ma anche se registro una notifica negativa altrimenti non la si vede
    if($ResultId>0) {
        //devo cercare i trasgressori
        $rs_FineTrespasser = $rs->Select('FineTrespasser',"FineId=$FineId and FineSendDate is null");
        while($r_FineTrespasser=mysqli_fetch_array($rs_FineTrespasser)){
            $a_FineHistoryIter = $a_FineHistory;
            $a_FineHistoryIter[] = array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int');
            $a_FineHistoryIter[] = array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']);
            $a_FineHistoryIter[] = array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int');
            $a_FineHistoryIter[] = array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineTrespasser['TrespasserId'], 'settype' => 'int');
            $a_FineHistoryIter[] = array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineTrespasser['TrespasserTypeId'], 'settype' => 'int');
            $a_FineHistoryIter[] = array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
            $a_FineHistoryIter[] = array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
            $a_FineHistoryIter[] = array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
            $a_FineHistoryIter[] = array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int');
            $rs->Insert('FineHistory', $a_FineHistoryIter);
        }
    }
}
$rs->End_Transaction();

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
header("location: mgmt_fine.php".$Filters);
