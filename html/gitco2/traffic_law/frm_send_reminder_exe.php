<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require(CLS."/cls_literal_number.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";

set_time_limit(-1);
$rs= new CLS_DB();
$P = "frm_send_reminder.php";

$s_TypePlate = CheckValue('TypePlate','s');
$n_PrinterId = CheckValue('PrinterId','n');

$ultimate = CheckValue('ultimate','s');

if($s_TypePlate=="F"){
	require(COD . "/send_reminder_foreign.php");
} else {
	require(COD . "/send_reminder_national.php");
}


header("location: ".$P."?TypePlate=".$s_TypePlate);





