<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


if($_POST) {

    $PaymentId  =  CheckValue('PaymentId','n');

    $a_FinePayment = array(
        array('field'=>'FineId','selector'=>'field','type'=>'int','settype'=>'int'),
        );

    $rs->Update('FinePayment',$a_FinePayment,"Id=".$PaymentId);
}






