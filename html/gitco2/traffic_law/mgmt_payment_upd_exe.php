<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters','s');

$PaymentId = CheckValue('Id','n');
$PaymentProcedure = CheckValue('PaymentProcedure','n');
$FineId = CheckValue('FineId','n');

$Name = strtoupper(CheckValue('Name','s'));

$FifthField = CheckValue('FifthField','s');
if($FifthField>16)$FifthField=substr($FifthField,0,16);

$PaymentFee = CheckValue('PaymentFee','n');
$InstallmentId = CheckValue('InstallmentList','n') != 0 ? CheckValue('InstallmentList','n') : null;

$rs->Start_Transaction();

$a_Payment = array(
    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$Name),
    array('field'=>'BankMgmt','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'PaymentTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'PaymentDocumentId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'PaymentDate','selector'=>'field','type'=>'date'),
    array('field'=>'CreditDate','selector'=>'field','type'=>'date'),
    array('field'=>'PaymentFee','selector'=>'value','type'=>'int','value'=>$PaymentFee, 'settype'=>'int'),
    array('field'=>'Amount','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Fee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CanFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CadFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CustomerFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'PercentualFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'OfficeNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'DocumentType','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'FifthField','selector'=>'value','type'=>'str','value'=>$FifthField),
    array('field'=>'Note','selector'=>'field','type'=>'str'),
    array('field'=>'InstallmentId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$InstallmentId,'nullable'=>true),
);

$rs->Update('FinePayment',$a_Payment, "Id=".$PaymentId);


if($PaymentProcedure==0 AND $FineId>0){
    $rs_PaymentProcedure = $rs->Select('FineNotification', "FineId=" .$FineId);
    if (mysqli_num_rows($rs_PaymentProcedure) > 0) {
        $a_FineNotification = array(
            array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
        );

        $rs->Update('FineNotification',$a_FineNotification, "FineId=".$FineId);
    } else{
        $rs_TMP_PaymentProcedure = $rs->Select('TMP_PaymentProcedure', "FineId=" .$FineId);
        if (mysqli_num_rows($rs_TMP_PaymentProcedure) == 0) {
            $a_TMP_PaymentProcedure = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'PaymentProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentProcedure, 'settype' => 'int'),
            );
            $rs->Insert('TMP_PaymentProcedure', $a_TMP_PaymentProcedure);
        }
    }
} else if($PaymentProcedure==1 AND $FineId>0){
    $a_FineNotification = array(
        array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
    );

    $rs->Update('FineNotification',$a_FineNotification, "FineId=".$FineId);
    $rs->Delete('TMP_PaymentProcedure', "FineId=".$FineId);

}

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";

header("location: mgmt_payment.php".$Filters);