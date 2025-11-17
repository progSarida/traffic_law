<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs = new CLS_DB();
$rs->SetCharset("utf8");

$CityId = CheckValue("CityId", "s");

$a_Result = $rs->getResults($rs->Select("RuleType", "CityId='$CityId'"), "object");

echo json_encode(
    array(
        "Result" => $a_Result
    )
);