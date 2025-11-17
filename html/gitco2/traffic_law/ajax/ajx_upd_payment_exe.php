<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


if($_POST) {

    $PaymentId = CheckValue('PaymentId','n');
    
    $PaymentTypeIdType1 = CheckValue('PaymentTypeIdType1','n');
    
    
    if(!$PaymentTypeIdType1){
        $a_FinePayment = array(
            array('field'=>'Name','selector'=>'field','type'=>'str'),
            array('field'=>'DocumentType','selector'=>'field','type'=>'int','settype'=>'int'),
            array('field'=>'PaymentDate','selector'=>'field','type'=>'date'),
            array('field'=>'CreditDate','selector'=>'field','type'=>'date'),
            array('field'=>'FifthField','selector'=>'field','type'=>'str'),
            array('field'=>'Amount','selector'=>'field','type'=>'flt','settype'=>'flt'),
            array('field'=>'Code','selector'=>'field','type'=>'str'),
            array('field'=>'ProtocolId','selector'=>'field','type'=>'int','settype'=>'int'),
            array('field'=>'VehiclePlate','selector'=>'field','type'=>'str'),
            array('field'=>'FineDate','selector'=>'field','type'=>'date'),
            );
    }
    else {
        $InstallmentId = empty($_REQUEST['InstallmentId']) ? null : $_REQUEST['InstallmentId'];
        $a_FinePayment = array(
            array('field'=>'Name','selector'=>'field','type'=>'str'),
            array('field'=>'PaymentFee','selector'=>'field','type'=>'int','settype'=>'int'),
            array('field'=>'InstallmentId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$InstallmentId,'nullable'=>true),
        );
    }
    
    $rs->Update('FinePayment',$a_FinePayment,"Id=".$PaymentId);
    echo json_encode(
        array(
            "Response" => "OK",
            "PaymentId" => $PaymentId,
            "InstallmentId" => $InstallmentId
        )
    );
}






