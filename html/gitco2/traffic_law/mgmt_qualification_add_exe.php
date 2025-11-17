<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$rs->Start_Transaction();

$Description = CheckValue('Description','s');
$CityId = $_SESSION['cityid'];

$rs_Qualification = $rs->Select("QualificationType", "Description='$Description' AND CityId='$CityId'");

if(mysqli_num_rows($rs_Qualification)>0){
    header("location: mgmt_qualification.php".$str_GET_Parameter."&P=mgmt_keyword.php&error=Qualifica già presente per questo comune.");
    DIE;
}

$a_QualificationType = array(
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
    array('field'=>'Description','selector'=>'field','type'=>'str'),
);

$rs->Insert('QualificationType', $a_QualificationType);


$rs->End_Transaction();


header("location: mgmt_qualification.php".$str_GET_Parameter."&answer=Inserito con successo.");
