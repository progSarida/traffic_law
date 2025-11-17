<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$RuleTypeId = CheckValue('RuleTypeId', 's');
$a_ViolaionTypes = array();

if ($RuleTypeId != ""){
    $rs_ViolationType = $rs->Select("ViolationType", "RuleTypeId=$RuleTypeId");
    while ($r_ViolationType = mysqli_fetch_array($rs_ViolationType)){
        $data = new stdClass();
        $data->Id = $r_ViolationType['Id'];
        $data->Title = StringOutDB($r_ViolationType['Title']);
        array_push($a_ViolaionTypes,$data);
    }
}

echo json_encode(
    array(
        "Result" => $a_ViolaionTypes
    )
    );