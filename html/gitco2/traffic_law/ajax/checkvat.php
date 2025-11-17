<?php
require_once("../_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
$rs = new CLS_DB();
$TaxCode = $_REQUEST['VatCode'];

if(isset($_POST['Id'])){
	$response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' AND VatCode = '".$TaxCode."' AND Id!='".$_POST['Id']."'");
	$row = mysqli_num_rows($response);
	if ($row > 0) {
		echo json_encode('Exists');
	} else {
		echo json_encode('Not Exists');
	}
} else {
	$response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' AND  VatCode = '".$TaxCode."'");
	$row = mysqli_num_rows($response);
	if ($row > 0) {
		echo json_encode('Exists');
	} else {
		echo json_encode('Not Exists');
	}
}
