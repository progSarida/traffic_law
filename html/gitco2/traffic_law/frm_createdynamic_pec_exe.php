<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/pagopa.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");
require_once(TCPDF . "/fpdi.php");

ini_set('max_execution_time', 3000);
$rs= new CLS_DB();

$s_TypePlate        = CheckValue('TypePlate','s');
$CreationDate       = CheckValue('CreationDate','s');
$SelectChiefControllerId  = CheckValue('ChiefControllerId','n');
$Search_EscludiRinotifichePEC  = CheckValue('Search_EscludiRinotifichePEC','n');

$Filters = CheckValue('Filters', 's');

//La password per la firma
$SignaturePwd = CheckValue('SignaturePwd', 's');


$AdditionalFilters = array();
if ($CreationDate != '') $AdditionalFilters['CreationDate'] = $CreationDate;
if ($SelectChiefControllerId != '') $AdditionalFilters['ChiefControllerId'] = $SelectChiefControllerId;
if ($SignaturePwd != '') $AdditionalFilters['SignaturePwd'] = $SignaturePwd;
if ($Search_EscludiRinotifichePEC != '') $AdditionalFilters['Search_EscludiRinotifichePEC'] = $Search_EscludiRinotifichePEC;


if($s_TypePlate=="F"){
	//require(COD."/createdynamic_pec_foreign.php");
} else {
	require(COD."/createdynamic_pec_national.php");
}

header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_createdynamic_pec.php'.$Filters));







