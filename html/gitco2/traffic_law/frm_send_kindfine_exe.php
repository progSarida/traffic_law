<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

set_time_limit(-1);

$s_TypePlate= CheckValue('TypePlate','s');
$Filters = CheckValue('Filters', 's');


if($s_TypePlate=="F"){
	require(COD . "/send_kindfine_foreign.php");
} else {
	require(COD . "/send_kindfine_national.php");
}

header("location: frm_send_kindfine.php".$Filters);





