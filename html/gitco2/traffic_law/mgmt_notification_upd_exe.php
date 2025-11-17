<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
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

$rs->Start_Transaction();

$a_FineHistory = array(
    array('field'=>'NotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CanFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CadFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NotifierFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'OtherFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
);

//NOTA 01/07/2022 è controintuitivo il fatto che la notifica venga modificata so se c'è la data di notifica
// io toglierei la condizione per lasciare più spazio di manovra agli utenti
if($NotificationDate!=""){
    $a_FineHistory[] = array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);

    $a_FineNotification = array(
        array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
        array('field'=>'SendDate','selector'=>'field','type'=>'date'),
        array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'ReceiptNumber','selector'=>'value','type'=>'str','value'=>$ReceiptNumber),
        array('field'=>'LetterNumber','selector'=>'value','type'=>'str','value'=>$LetterNumber),
        array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
        array('field'=>'ValidatedAddress','selector'=>'value','type'=>'int','value'=>$ValidatedAddress,'settype'=>'int'),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    );
    
    $rs->Update('FineNotification',$a_FineNotification, "FineId=".$FineId);
}

$a_FineHistory[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int');
$a_FineHistory[] = array('field'=>'SendDate','selector'=>'field','type'=>'date');

//se FineHistory esiste lo aggiorno, altrimenti lo inersisco
$rs_FineHistory = $rs->Select('FineHistory', "FineId=".$FineId." AND NotificationTypeId=6");
if (mysqli_num_rows($rs_FineHistory)>0){
    $rs->Update('FineHistory',$a_FineHistory, "FineId=".$FineId." AND NotificationTypeId=6");
}
else {
    //se notifico su strada, via messo o d'ufficio e non esiste il record di tipo 6 su FineHistory lo devo inserire
    // ma anche se registro una notifica negativa altrimenti non la si vede
    if($ResultId>0) {
        //se notifico su strada, via messo o d'ufficio e non esiste il record di tipo 6 su FineHistory lo devo inserire
        $rs_FineTrespasser = $rs->Select('FineTrespasser',"FineId=$FineId and FineSendDate is null");
        while($r_FineTrespasser=mysqli_fetch_array($rs_FineTrespasser)){
            
            $a_FineHistoryIter = $a_FineHistory;
            $a_FineHistoryIter[] =    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int');
            $a_FineHistoryIter[] =    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']);
            $a_FineHistoryIter[] =    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int');
            $a_FineHistoryIter[] =    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineTrespasser['TrespasserId'], 'settype' => 'int');
            $a_FineHistoryIter[] =    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineTrespasser['TrespasserTypeId'], 'settype' => 'int');
            $a_FineHistoryIter[] =    array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
            $a_FineHistoryIter[] =    array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
            $a_FineHistoryIter[] =    array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
            $a_FineHistoryIter[] =    array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int');

            $rs->Insert('FineHistory', $a_FineHistoryIter);
        }
    }
}

//se cambio il resultId lo stato su Fine deve cambiare coerentemente
$StatusTypeId = 25;
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
$rs->Update('Fine',$a_Fine, "Id=$FineId AND StatusTypeId NOT IN(".implode(',', STATUSTYPEID_VERBALI_STATI_FINALI).")");


$rs->End_Transaction();

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
header("location: mgmt_fine.php".$Filters);