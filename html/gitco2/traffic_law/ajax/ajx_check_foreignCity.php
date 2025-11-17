<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$result = "";

$CountryId = $_POST['CountryId'];
$Title = $_POST['Title'];
$str_WhereId = (isset($_REQUEST['ForeignCityId'])) ? 'AND Id<>'.$_REQUEST['ForeignCityId'] : '';

$rs_check = $rs->Select("ForeignCity", 'CountryId="'.$CountryId.'" AND Title="'.$Title.'"'.$str_WhereId);

if(mysqli_num_rows($rs_check)>0)
    $result = "NO";
else
    $result = "OK";

echo json_encode(
    array(
        "Result" => $result
    )
);
