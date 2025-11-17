<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$strTrespasser = "";
$strFine = "";


$VehiclePlate = strtoupper($_POST['VehiclePlate']);
$FineDate = DateInDB($_POST['FineDate']);
$FineTime = $_POST['FineTime'];
$isPageUpdate = CheckValue('IsPageUpdate', 's');

$H = 0;


$fines = $rs->Select('Fine',"VehiclePlate='".$VehiclePlate."' AND FineDate='".$FineDate."' AND FineTime='".$FineTime."'","Id DESC");
$Number = mysqli_num_rows($fines);

if($Number>0) {
	$strFine = '
		<div class="BoxRowLabel col-sm-12">Altri verbali con stessa ora e targa</div>
		<div class="BoxRowLabel col-sm-3">Riferimento</div>
		<div class="BoxRowLabel col-sm-9">Località</div>

';
	while($fine = mysqli_fetch_array($fines)){
		$strFine .= '
			<div class="BoxRowCaption col-sm-3">'.$fine['Code'].'</div>
			<div class="BoxRowCaption col-sm-9">'.$fine['Address'].'</div>	
		';
	}
	$H += 150;
}else{
	$strFine='';
}

if ($isPageUpdate != ""){
    $strTrespasser .='
	<div class="BoxRowLabel col-sm-12">Altri trasgressori con questa targa</div>
	<div class="BoxRowLabel col-sm-5">Nominativo</div>
	<div class="BoxRowLabel col-sm-5">Città</div>
	<div class="BoxRowLabel col-sm-2">Stato</div>
';
} else {
    $strTrespasser .='
	<div class="BoxRowLabel col-sm-12">Altri trasgressori con questa targa</div>
	<div class="BoxRowLabel col-sm-1">Assegna</div>
	<div class="BoxRowLabel col-sm-4">Nominativo</div>
	<div class="BoxRowLabel col-sm-5">Città</div>
	<div class="BoxRowLabel col-sm-2">Stato</div>
';
}




$trespassers = $rs->SelectQuery("SELECT DISTINCT TrespasserId, CompanyName, Surname, Name, City, CountryTitle FROM V_FineTrespasser WHERE (TrespasserTypeId=1 OR TrespasserTypeId=11) AND VehiclePlate='".$VehiclePlate."';");
$Number = mysqli_num_rows($trespassers);



if($Number==0) {
	$strTrespasser = '';
}else{
	while($trespasser = mysqli_fetch_array($trespassers)){
	    if ($isPageUpdate != ""){
	        $strTrespasser .= '
			<div class="BoxRowCaption col-sm-5">'.$trespasser['CompanyName']." ".$trespasser['Surname']." ".$trespasser['Name'].'</div>
			<div class="BoxRowCaption col-sm-5">'.$trespasser['City'].'</div>
			<div class="BoxRowCaption col-sm-2">'.$trespasser['CountryTitle'].'</div>
		';
	    } else {
	        $strTrespasser .= '
			<div class="BoxRowCaption col-sm-1"><input type="checkbox" name="checkbox[]" id="checkbox"  value="' . $trespasser['TrespasserId'] . '" /></div>
			<div class="BoxRowCaption col-sm-4">'.$trespasser['CompanyName']." ".$trespasser['Surname']." ".$trespasser['Name'].'</div>
			<div class="BoxRowCaption col-sm-5">'.$trespasser['City'].'</div>
			<div class="BoxRowCaption col-sm-2">'.$trespasser['CountryTitle'].'</div>
		';
	    }
	}
	$H += 150;
}

echo json_encode(
	array(
		"F" => $strFine,
		"T" => $strTrespasser,
		"H" => $H
	)
);