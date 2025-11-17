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

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");

require_once(TCPDF . "/fpdi.php");

set_time_limit(-1);
ini_set('memory_limit', '2048M');
$rs= new CLS_DB();

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}

$P = "frm_senddynamic_reminder.php";
$Operation          = CheckValue('Operation','s');

$s_TypePlate        = CheckValue('TypePlate','s');
$n_PrinterId        = CheckValue('PrinterId','s');
$PrintDestinationFold = CheckValue('PrintDestinationFold','s');
$CreationType       = CheckValue('CreationType','n');
$CreationDate       = CheckValue('CreationDate','s');
$ProcessingDate     = CheckValue('ProcessingDate','s');
$PrintType          = CheckValue('PrintType','n');
$n_ControllerId     = CheckValue('ControllerId','n');

$ultimate = CheckValue('ultimate','s');

$AdditionalFilters = array();

if ($Operation == "flow"){
    $AdditionalFilters['PrinterId'] = $n_PrinterId;
    
    if($s_TypePlate=="F"){
        require(COD . "/senddynamic_reminder_foreign.php");
    } else {
        require(COD . "/senddynamic_reminder_national.php");
    }
} else if ($Operation == "create" || $Operation == "update") {
    $AdditionalFilters['PrintType'] = $PrintType;
    $AdditionalFilters['ProcessingDate'] = $ProcessingDate;
    $AdditionalFilters['CreationDate'] = $CreationDate;
    $AdditionalFilters['PrintDestinationFold'] = $PrintDestinationFold;
    $AdditionalFilters['ControllerId'] = $n_ControllerId;

    if($s_TypePlate=="F"){
        require(COD . "/createdynamic_reminder_foreign.php");
    } else {
        require(COD . "/createdynamic_reminder_national.php");
    }
} else if ($Operation == "delete"){
    if($s_TypePlate=="F"){
        require(COD . "/deletedynamic_reminder_national.php"); //visto che ad oggi non fa logiche per nazionalità usiamo lo stesso file (Nota del 18/09/2020)
    } else {
        require(COD . "/deletedynamic_reminder_national.php");
    }
}

header("location: ".impostaParametriUrl($AdditionalFilters, $P.$Parameters));





