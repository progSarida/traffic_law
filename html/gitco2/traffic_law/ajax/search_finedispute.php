<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset('utf8');

$str_Fine = "";


$str_City = $_SESSION['cityid'];
$Search_Protocol = $_POST['Search_Protocol'];
$Search_Plate = $_POST['Search_Plate'];
$Search_Trespasser = addslashes($_POST['Search_Trespasser']);

$str_Where = "F.CityId='".$str_City."'";
if($Search_Protocol!=""){
    $str_Where .= " AND F.ProtocolId = ".$Search_Protocol;
}
if($Search_Plate!=""){
    $str_Where .= " AND F.VehiclePlate LIKE '".$Search_Plate."%'";
}
if($Search_Trespasser!=""){
    $str_Where .= " AND 
		(T.CompanyName LIKE '".$Search_Trespasser."%' OR 
		T.Surname LIKE '".$Search_Trespasser."%')";
}


$fineView = new CLS_VIEW(SEARCH_DISPUTE_FINE_TRESPASSER);
$rs_fine= $rs->getResults($rs->ExecuteQuery($fineView->generateSelect($str_Where,null, "CompanyName, Surname, Name, FineId, ProtocolId")));
//echo $fineView->generateSelect($str_Where,null, "CompanyName, Surname, Name, FineId, ProtocolId");
//$rs_fine = $rs->Select('V_FineTrespasser', $str_Where, "CompanyName, Surname, Name, FineId, ProtocolId");
$n_Number = count($rs_fine);

$str_Fine .='
    <div class="clean_row HSpace4"></div>
	<div class="table_label_H col-sm-2">Cron</div>
	<div class="table_label_H col-sm-2">Data</div>
	<div class="table_label_H col-sm-2">Targa</div>
	<div class="table_label_H col-sm-5">Nominativo</div>
	<div class="table_label_H col-sm-1">&nbsp;</div>
';

$str_Fine .= '<div class="row-fluid">';

if($n_Number==0) {
	$str_Fine .= '<div class="table_caption_H col-sm-12">Nessun verbale trovato</div>  ';
}else{
	foreach($rs_fine as $r_fine){
		$NameOut = substr($r_fine['CompanyName']." ".$r_fine['Surname']." ".$r_fine['Name'],0,25);

		$str_Fine .= '
			<div class="table_caption_H col-sm-2">'.$r_fine['ProtocolId'].'/'.$r_fine['ProtocolYear'].'</div>			
			<div class="table_caption_H col-sm-2">'.DateOutDB($r_fine['FineDate']).'</div>
			<div class="table_caption_H col-sm-2">'.$r_fine['VehiclePlate'].'</div>
			<div class="table_caption_H col-sm-5">'.$NameOut.'</div>   
			
			<div class="table_caption_H col-sm-1"><a href="#"><span class="fa fa-plus-circle" id="' . $r_fine['FineId'] . '" 
			data-protocol="'.$r_fine['ProtocolId'].' / '.$r_fine['ProtocolYear'].'"
			data-plate="'.$r_fine['VehiclePlate'].'"
			data-trespasser="'. $r_fine['CompanyName'] ." ".$r_fine['Surname']." ".$r_fine['Name'].'"></span></a></div>
		';
	}
}
$str_Fine .= '</div><div class="clean_row HSpace4"></div><div class="clean_row HSpace4"></div>
';
$str_Fine .= '
<script>
var countFineId = 0;
function removeDiv(key){
    $("#DivFineId_"+key).remove();
}
$(".fa-plus-circle").click(function(){
	var id = $(this).attr("id");
	var protocolData = $(this).attr("data-protocol");
	var trespasserData = $(this).attr("data-trespasser");
	var plateData = $(this).attr("data-plate");
	
	var protocolRow= "<div id=\'DivFineId_"+countFineId+"\'><input type=\'hidden\' value=\'"+id+"\' id=\'FineId_"+countFineId+"\' name=\'FineId["+countFineId+"]\' />";
	protocolRow += "<div class=\'table_caption_H col-sm-3\'>Cron: "+protocolData+"</div>";	
	protocolRow+= "<div class=\'table_caption_H col-sm-3\'>Targa: "+plateData+"</div>";
	protocolRow += "<div class=\'table_caption_H col-sm-5\'>"+trespasserData+"</div>";	
	protocolRow+= "<div class=\'table_caption_H col-sm-1\' style=\'padding-top: 5px; text-align: center;\'>";
	protocolRow+= "<i class=\'fas fa-minus-circle fa-lg\' style=\'color:red; cursor: pointer;\' onclick=\'removeDiv("+countFineId+");\'></i></div>";
	protocolRow+= "<div class=\'clean_row HSpace4\'></div><div>";

	$("#fine_container").append(protocolRow);
	countFineId++;
	return false;
});
</script>
';





echo json_encode(
	array(
		"Trespasser" => $str_Fine

		)
);