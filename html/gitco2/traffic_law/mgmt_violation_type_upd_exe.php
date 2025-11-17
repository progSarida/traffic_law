<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');


$rs->Start_Transaction();

$Id = CheckValue('ViolationTypeId','n');

$rs_ViolationType = $rs->Select('ViolationType', "Id=$Id");

$a_ViolationType = array(
    array('field'=>'Title','selector'=>'field','type'=>'str'),
    array('field'=>'Description','selector'=>'field','type'=>'str'),
    array('field'=>'Disabled','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'NationalFormId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ForeignFormId','selector'=>'field','type'=>'int','settype'=>'int'),
);

$rs->Update('ViolationType', $a_ViolationType, "Id=$Id");


$rs->End_Transaction();


header("location: mgmt_violation_type.php".$str_GET_Parameter."&answer=Modificato con successo.");
