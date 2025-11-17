<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$CityId = CheckValue('CityId', 's');
$Province = "";

$rs_Province = $rs->SelectQuery("
        SELECT P.ShortTitle ProvinceTitle
        FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
        WHERE C.Id='".$CityId."'
        ");
$r_Province = mysqli_fetch_array($rs_Province);
$Province = $r_Province['ProvinceTitle'];

echo json_encode(
    array(
        "Province" => $Province,
    )
);
