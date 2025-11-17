<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");

require(TCPDF . "/fpdi.php");


ini_set('max_execution_time', 3000);
$rs= new CLS_DB();

$P = "frm_create_reminder.php";

$s_TypePlate        = CheckValue('TypePlate','s');
$CreationDate       = CheckValue('CreationDate','s');

$ChiefControllerId  = CheckValue('ChiefControllerId','n');
$n_ControllerId     = CheckValue('ControllerId','n');


if($s_TypePlate=="F"){
	require(COD."/create_reminder_foreign.php");
} else {
	require(COD."/create_reminder_national.php");
}


if ($ultimate){
	if($table_row['DigitalSignature']==1){

		echo "<script>window.location='".$P."?DisplayMsg=1'</script>";
	}
}

header("location: ".$P.$str_BackPage.'&ControllerId='.$n_ControllerId);







