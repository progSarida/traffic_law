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

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}

$P = CheckValue('P','s');
$Id= CheckValue('Id','n');

$s_TypePlate        = CheckValue('TypePlate','s');
$CreationDate       = CheckValue('CreationDate','s');
$CreationType       = CheckValue('CreationType','n');
$InsuranceDate      = CheckValue('InsuranceDate','s');
$PrintDestinationFold = CheckValue('PrintDestinationFold','n');
$ProtocolIdAssigned = CheckValue('ProtocolIdAssigned','n');


$n_Duplicate        = CheckValue('Duplicate','n');
$RegularPostalFine  = CheckValue('RegularPostalFine','n');

$SelectChiefControllerId  = CheckValue('ChiefControllerId','n');
$n_ControllerId     = CheckValue('ControllerId','n');

//Dato che posso arrivare qui anche da altre pagine, mi assicuro di entrare solo se la pagina chiamante è Moduli > Crea verbali posta
if($P == 'frm_create_fine.php'){
    $_SESSION['Checkboxes'][$P] = $_POST['checkbox'];
}

if($s_TypePlate=="F"){
	require(COD."/create_fine_foreign.php");
} else {
	require(COD."/create_fine_national.php");
}


if ($ultimate){
	if($table_row['DigitalSignature']==1){

		echo "<script>window.location='".$P."?DisplayMsg=1'</script>";
	}
}

header("location: ".$P.$Parameters.'&Id='.$Id.'&CreationDate='.$CreationDate.'&InsuranceDate='.$InsuranceDate.'&ChiefControllerId='.$ChiefControllerId.'&PrintDestinationFold='.$PrintDestinationFold);

