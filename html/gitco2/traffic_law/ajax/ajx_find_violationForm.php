<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$resultNational = "";
$resultForeign = "";

$RuleTypeId = $_POST['RuleTypeId'];
$CityId = $_SESSION['cityid'];

$rs_FormNational = $rs->SelectQuery('
    SELECT distinct f.FormTypeId, f.Title AS TypeTitle
    FROM FormDynamic AS f
    WHERE RuleTypeId='.$RuleTypeId.' AND CityId="'.$CityId.'" AND NationalityId=1 AND Deleted=0');

while ($r_FormNational = mysqli_fetch_array($rs_FormNational)){
    $resultNational .= '<option value='.$r_FormNational['FormTypeId'].'>'.StringOutDB($r_FormNational['TypeTitle']).'</option>';
}

$rs_FormForeign = $rs->SelectQuery('
    SELECT distinct f.FormTypeId, f.Title AS TypeTitle
    FROM FormDynamic AS f
    WHERE RuleTypeId='.$RuleTypeId.' AND CityId="'.$CityId.'" AND NationalityId<>1 AND Deleted=0');

while ($r_FormForeign = mysqli_fetch_array($rs_FormForeign)){
    $resultForeign .= '<option value='.$r_FormForeign['FormTypeId'].'>'.StringOutDB($r_FormForeign['TypeTitle']).'</option>';
}

echo json_encode(
    array(
        "Foreign" => $resultForeign,
        "National" => $resultNational,
    )
);
