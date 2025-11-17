<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$result = "";

$RuleTypeId = $_POST['RuleTypeId'];
$CityId = $_POST['CityId'];

$rs_Rule = $rs->Select('RuleType', "Id=" . $RuleTypeId. " AND CityId='". $CityId . "'");

if(mysqli_num_rows($rs_Rule)>0)
    $result = "NO";
else
    $result = "OK";

echo json_encode(
    array(
        "Result" => $result
    )
    );
