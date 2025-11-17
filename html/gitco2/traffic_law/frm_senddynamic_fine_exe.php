<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_literal_number.php");
require_once(CLS."/ftp/PhpFTPFactory.php");
require_once(INC."/function.php");
require_once(INC."/function_printerFtp.php");
require_once(INC."/pagopa.php");
require_once(INC."/initialization.php");

require_once(TCPDF."/tcpdf.php");
require_once(TCPDF."/fpdi.php");

set_time_limit(-1);
$rs= new CLS_DB();
$P = "frm_senddynamic_fine.php";

$Filters = CheckValue('Filters', 's');

$s_TypePlate= CheckValue('TypePlate','s');
$RegularPostalFine  = CheckValue('RegularPostalFine','n');
$PrinterId = CheckValue('PrinterId','n');

$AdditionalFilters = array();
$AdditionalFilters['RegularPostalFine'] = $RegularPostalFine;
$AdditionalFilters['PrinterId'] = $PrinterId;

if($s_TypePlate=="F"){
	require_once(COD."/senddynamic_fine_foreign.php");
} else {
	require_once(COD."/senddynamic_fine_national.php");
}

header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_senddynamic_fine.php'.$Filters));