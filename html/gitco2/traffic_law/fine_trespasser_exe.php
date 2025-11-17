<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs = new CLS_DB();


$TrespasserId = $rs->ValueSetType($_REQUEST['TrespasserId'],'int');
$FineId = $rs->ValueSetType($_REQUEST['Id'],'int');
$StatusTypeId = 10;

$trespassers = $rs->Select('Trespasser',"Id=".$TrespasserId);
$trespasser = mysqli_fetch_array($trespassers);

$LanguageId = 0;
$VehiclePlate = $rs->ValueSetType($_REQUEST['VehiclePlate'],'str');
if($trespasser['CountryId']=='Z133'){
	$ZoneId = substr($VehiclePlate,0,2);
	$zones = $rs->Select('CountryZone',"Id='".$ZoneId."' AND CountryId='Z133'");
	$zone = mysqli_fetch_array($zones);
	$LanguageId = $zone['LanguageId'];
}

$rs->Start_Transaction();
if($LanguageId>0){
	$a_Trespasser = array(
		array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
	);
	$rs->Update('Trespasser',$a_Trespasser, 'Id='.$TrespasserId);
}

$a_FineTrespasser = array(
	array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
	array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
	array('field'=>'TrespasserTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'Note','selector'=>'field','type'=>'str'),
);

$rs->Insert('FineTrespasser',$a_FineTrespasser);


$a_Fine = array(
	array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
);
$rs->Update('Fine',$a_Fine, 'Id='.$FineId);

if(isset($_POST['checkbox'])) {
	foreach($_POST['checkbox'] as $FineId) {

		$a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
            array('field'=>'Note','selector'=>'field','type'=>'str'),
		);
		$rs->Insert('FineTrespasser',$a_FineTrespasser);


        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
		);
		$rs->Update('Fine',$a_Fine, 'Id='.$FineId);

	}
}
$rs->End_Transaction();


header("location: mgmt_violation.php");