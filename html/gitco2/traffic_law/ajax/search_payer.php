<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$str_Payment = "";

$Search_Payer = $_POST['Search_Payer'];

$str_City = $_SESSION['cityid'];

$str_Where = " AND FineId=0 AND CityId='".$str_City."'";

$str_Payment .='

	<div class="table_label_H col-sm-7">Nominativo</div>
	<div class="table_label_H col-sm-2">Data</div>
	<div class="table_label_H col-sm-2">Importo</div>
	<div class="table_label_H col-sm-1"></div>
';




$rs_fine = $rs->Select('FinePayment', "Name LIKE '%".$Search_Payer."%'".$str_Where);
$n_Number = mysqli_num_rows($rs_fine);



$str_Payment .= '<div class="row-fluid">';

if($n_Number==0) {
	$str_Payment .= '<div class="table_caption_H col-sm-12">Nessun nominativo trovato</div>  ';
}else{
	while($r_fine = mysqli_fetch_array($rs_fine)){

		$str_Payment .= '
		    <div class="table_caption_H col-sm-7">'.$r_fine['Name'].'</div>
			<div class="table_caption_H col-sm-2">'.DateOutDB($r_fine['PaymentDate']).'</div>
			<div class="table_caption_H col-sm-2">'.$r_fine['Amount'].'</div>
			<div class="table_caption_H col-sm-1"><a href="frm_reclaim_payment.php?Id=' . $r_fine['Id'] . '"><span class="fa fa-plus-circle"></span></a></div>
		';
	}
}
$str_Payment .= '</div>';



echo json_encode(
	array(
		"Payer" => $str_Payment,

		)
);