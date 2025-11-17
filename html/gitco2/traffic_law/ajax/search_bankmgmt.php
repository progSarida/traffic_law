<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$rs_customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_customer = mysqli_fetch_array($rs_customer);


$NationalBankMgmt = $r_customer['NationalBankMgmt'];
$ForeignBankMgmt = $r_customer['ForeignBankMgmt'];




$FineId = $_POST['id'];

$rs_fine = $rs->Select('Fine', "Id=".$FineId);
$r_fine = mysqli_fetch_array($rs_fine);


$str_BankMgmt = ($r_fine['CountryId'] == 'Z000') ? $NationalBankMgmt : $ForeignBankMgmt;



echo json_encode(
	array(
		"BankMgmt" => $str_BankMgmt,

		)
);