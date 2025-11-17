<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset('utf8');

$Type = $_REQUEST['Type'];
if($Type=="add"){
    $DisputeId = $_REQUEST['DisputeId'];
    $DisputeDateId = $_REQUEST['DisputeDateId'];
    $GradeTypeId = $_REQUEST['GradeTypeId'];

    $a_DisputeAmount = array(
        array('field'=>'GradeTypeId', 'selector'=>'value', 'type'=>'int', "value"=>(int)$GradeTypeId),
        array('field'=>'DisputeId','selector'=>'value', 'type'=>'int', "value"=>(int)$DisputeId),
        array('field'=>'DisputeDateId','selector'=>'value', 'type'=>'int', "value"=>(int)$DisputeDateId),
    );

    $rs->Insert('DisputeAmount',$a_DisputeAmount);
}
else if($Type=="remove"){
    $AmountId = $_REQUEST['AmountId'];
    $rs->Delete('DisputeAmount',"Id=".$AmountId);
    $rs->Delete('FineDisputeAmount',"DisputeAmountId=".$AmountId);
}

echo json_encode(
	array("msg" => "ok")
);