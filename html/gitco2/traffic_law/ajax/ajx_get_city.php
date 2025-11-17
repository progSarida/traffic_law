<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs_City = $rs->Select(MAIN_DB.".City", 'Id="'.CheckValue('CityId','s').'"');
$resp=array();
if ($r_City = mysqli_fetch_array($rs_City)){
	$resp["Title"]=$r_City["Title"];
	$resp["Zip"]=$r_City["ZIP"];
		$resp["Country"]=$r_City["Country"];

	$rs_Province = $rs->Select(MAIN_DB.".Province", 'Id="'.$r_City["ProvinceId"].'"');
	if ($r_Province = mysqli_fetch_array($rs_Province))
		$resp["Province"]=$r_Province["ShortTitle"];
}
echo json_encode($resp);