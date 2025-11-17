<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$resultForm = "";
$RuleTypeId = $_POST['RuleTypeId'];
$NationalityId = $_POST['NationalityId'];
$LanguageId = $_POST['LanguageId'];
$CityId = $_SESSION['cityid'];
$rs_Form = $rs->SelectQuery('
    SELECT distinct f.FormTypeId, f.Title AS TypeTitle
    FROM FormDynamic AS f
    WHERE RuleTypeId='.$RuleTypeId.' AND CityId="'.$CityId.'" AND NationalityId='.$NationalityId.' AND LanguageId='.$LanguageId.' AND Deleted=0');
while ($r_Form = mysqli_fetch_array($rs_Form)){
    $resultForm .= '<option value='.$r_Form['FormTypeId'].'>'.StringOutDB($r_Form['TypeTitle']).'</option>';
}


echo json_encode(
    array(
        "Form" => $resultForm,
    )
);