<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$rs->Start_Transaction();


$DateHearing = CheckValue('DateHearing','s');
$TypeHearing = CheckValue('TypeHearing','s');

if($DateHearing=="") $TypeHearing="";




$a_Dispute = array(
	array('field'=>'GradeTypeId', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
	array('field'=>'OwnerPresentation', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
	array('field'=>'ProtocolNumber', 'selector'=>'field', 'type'=>'str'),
	array('field'=>'DateProtocol', 'selector'=>'field', 'type'=>'date'),
    array('field'=>'DateReceive', 'selector'=>'field', 'type'=>'date'),
    array('field'=>'DateSend', 'selector'=>'field', 'type'=>'date'),
    array('field'=>'DateFile', 'selector'=>'field', 'type'=>'date'),
    array('field'=>'OfficeId','selector'=>'field', 'type'=>'int', 'settype'=>'int'),
    array('field'=>'OfficeCity','selector'=>'field', 'type'=>'str'),
    array('field'=>'OfficeAdditionalData','selector'=>'field', 'type'=>'str'),
    array('field'=>'DateMeasure','selector'=>'field', 'type'=>'date'),
    array('field'=>'MeasureNumber','selector'=>'field', 'type'=>'str'),
    array('field'=>'FineSuspension', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
    array('field'=>'DateProtocolEntity','selector'=>'field', 'type'=>'date'),
    array('field'=>'EntityProtocolNumber','selector'=>'field', 'type'=>'str'),
    array('field'=>'Number', 'selector'=>'field', 'type'=>'str'),
    array('field'=>'Division', 'selector'=>'field', 'type'=>'str'),
    array('field'=>'RegDate', 'selector'=>'value','type'=>'date', 'value'=>date("Y-m-d")),
	array('field'=>'RegTime', 'selector'=>'value','type'=>'str', 'value'=>date("H:i")),
	array('field'=>'UserId', 'selector'=>'value', 'type'=>'str', 'value'=>$_SESSION['username']),
);
$DisputeId = $rs->Insert('Dispute',$a_Dispute);

//$a_DisputeDate = array(
//    array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>$DisputeId, 'settype'=>'int'),
//    array('field'=>'GradeTypeId', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
//    array('field'=>'DateHearing', 'selector'=>'field', 'type'=>'date'),
//    array('field'=>'TimeHearing', 'selector'=>'field', 'type'=>'time','settype'=>'time'),
//    array('field'=>'TypeHearing', 'selector'=>'value', 'type'=>'str','value'=>$TypeHearing),
//);
//
//$rs->Insert('DisputeDate',$a_DisputeDate);

$a_fineId = $_REQUEST['FineId'];
foreach($a_fineId as $fineId){
    $a_FineDispute = array(
        array('field'=>'FineId', 'selector'=>'value', 'type'=>'int', 'value'=>$fineId, 'settype'=>'int'),
        array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>$DisputeId, 'settype'=>'int'),

    );
    $rs->InsertOrUpdateIfExist('FineDispute',$a_FineDispute);
}


$rs->End_Transaction();

header("location: mgmt_dispute.php");