<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters','s');

$RefundStatus = CheckValue('RefundStatus','n');


if($RefundStatus==1){
    $PaymentId = CheckValue('PaymentId','n');


    $rs->Start_Transaction();


    $a_Payment = array(
        array('field'=>'RefundStatus','selector'=>'value','type'=>'int','value'=>$RefundStatus,'settype'=>'int'),
    );

    $rs->Update('FinePayment',$a_Payment, "Id=".$PaymentId);




    $a_Refund = array(
        array('field'=>'FineId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'PaymentId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
        array('field'=>'Amount','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'RefundDate','selector'=>'field','type'=>'date'),
        array('field'=>'Note','selector'=>'field','type'=>'str'),
        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),

    );

    $rs->Insert('FineRefund',$a_Refund);
    $rs->End_Transaction();

    $_SESSION['Message'] = "Azione eseguita con successo.";
    header("location: mgmt_payment.php".$Filters);


} else {
    $Id= CheckValue('Id','n');
    $PaymentTypeId= CheckValue('PaymentTypeId','n');






    $rs_payment = $rs->SelectQuery("SELECT MAX(ReclaimOrder)+1 AS MaxNumber FROM FinePayment");
    $r_payment = mysqli_fetch_array($rs_payment);

    $a_Payment = array(
        array('field'=>'ReclaimOrder','selector'=>'value','type'=>'int','value'=>$r_payment['MaxNumber'],'settype'=>'int'),
    );

    $rs->Update('FinePayment',$a_Payment, "Id=".$Id);

    header("location: ".impostaParametriUrl(array('PaymentTypeId' => $PaymentTypeId), 'frm_reclaim_payment.php'.$str_GET_Parameter));
}



