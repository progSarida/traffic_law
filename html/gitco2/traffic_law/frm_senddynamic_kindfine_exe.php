<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/pagopa.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");
require(TCPDF . "/fpdi.php");

set_time_limit(-1);
$rs= new CLS_DB();
$Filters = CheckValue('Filters', 's');
$CreationDate = CheckValue('CreationDate', 's');

$s_TypePlate= CheckValue('TypePlate','s');

if($s_TypePlate=="F"){
	require(COD . "/senddynamic_kindfine_foreign.php");
} else {
	require(COD . "/senddynamic_kindfine_national.php");
}

header("location: frm_senddynamic_kindfine.php".$Filters);







