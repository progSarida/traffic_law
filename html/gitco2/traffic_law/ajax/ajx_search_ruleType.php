<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$result = "";

$CityId = $_POST['CityId'];
$FirstOption = "";
$Options = "";
$str_RuleTypes = "";
$a_Types= array();

$rs_RuleType = $rs->Select('RuleType', "CityId='". $CityId . "'");

if(mysqli_num_rows($rs_RuleType)>0){
    while($r_RuleType = mysqli_fetch_array($rs_RuleType)){
        $Options .= "<option value=".$r_RuleType["Id"].">".$r_RuleType["Title"]."</option>";
        $a_Types[] = $r_RuleType["Id"];
    }
    $FirstOption .= '<option value="'.implode(",", $a_Types).'"></option>';
    $str_RuleTypes = $FirstOption.$Options;
    $result = "OK";
} else $result = "NO";
        
echo json_encode(
    array(
        "Result" => $result,
        "RuleTypes" => $str_RuleTypes
    )
);
        