<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$rs->Start_Transaction();

$Id = CheckValue('Id','s');
$CityId = $_SESSION['cityid'];

$a_QualificationType = array(
    array('field'=>'Description','selector'=>'field','type'=>'str'),
);

$rs->Update('QualificationType', $a_QualificationType, "Id=$Id AND CityId='$CityId'");

$rs->End_Transaction();

header("location: mgmt_qualification.php".$str_GET_Parameter."&answer=Modificato con successo.");
