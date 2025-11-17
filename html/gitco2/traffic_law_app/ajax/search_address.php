<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");




$LAT = CheckValue('LAT','s');
$LNG = CheckValue('LNG','s');


$str_Address = getaddress($LAT, $LNG) ;


echo json_encode( array( "Address" => $str_Address ));