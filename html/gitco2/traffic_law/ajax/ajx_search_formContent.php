<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$FormTypeId = $_POST['FormTypeId'];
$CityId = $_POST['CityId'];
$RuleTypeId = $_POST['RuleTypeId'];
$NationalityId = $_POST['NationalityId'];
$LanguageId = $_POST['LanguageId'];
$Deleted = $_POST['Deleted'];

$rs_Form = $rs->Select("FormDynamic", "FormTypeId=$FormTypeId AND CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Deleted=$Deleted");
$Content = mysqli_fetch_array($rs_Form)['Content'];

echo json_encode(
    array(
        "Result" => utf8_encode($Content),
    )
);
