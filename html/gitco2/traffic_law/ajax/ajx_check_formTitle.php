<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$result = "";

$RuleTypeId = $_POST['RuleTypeId'];
$CityId = $_POST['CityId'];
$NationalityId = $_POST['NationalityId'];
$Title = $_POST['Title'];

$LanguageId = ($NationalityId == 1) ? 1 : $_POST['LanguageId'];

$rs_check = $rs->Select("FormDynamic", "CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND Title='$Title' AND LanguageId=$LanguageId AND Deleted=0");

if(mysqli_num_rows($rs_check)>0)
    $result = "NO";
else
    $result = "OK";

echo json_encode(
    array(
        "Result" => $result
    )
);
