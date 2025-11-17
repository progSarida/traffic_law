<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");
require(TCPDF . "/fpdi.php");

$rs= new CLS_DB();

$s_TypePlate        = CheckValue('TypePlate','s');

$Filters = CheckValue('Filters', 's');

$ReturnParams = array();

//La password per la firma
$SignaturePwd = CheckValue('SignaturePwd', 's');
if (!empty($SignaturePwd)) $ReturnParams['SignaturePwd'] = $SignaturePwd;

if($s_TypePlate=="F"){
    require(COD."/createdynamic_pecnotification_foreign.php");
} else {
    require(COD."/createdynamic_pecnotification_national.php");
}

header("location: ".impostaParametriUrl($ReturnParams, 'frm_createdynamic_pecnotification.php'.$Filters));







