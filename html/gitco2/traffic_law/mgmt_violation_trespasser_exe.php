<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");




$TrespasserId = CheckValue('TrespasserId','n');
$FineId = CheckValue('FineId','n');
$TrespasserTypeId = CheckValue('TrespasserTypeId','n');

$VehiclePlate= strtoupper(CheckValue('VehiclePlate','s'));


$trespassers = $rs->Select('Trespasser',"Id=".$TrespasserId);
$trespasser = mysqli_fetch_array($trespassers);

$LanguageId = 0;


$rs->Start_Transaction();


if($trespasser['CountryId']=='Z133'){
	$ZoneId = substr($VehiclePlate,0,2);
	$zones = $rs->Select('CountryZone',"Id='".$ZoneId."' AND CountryId='Z133'");
	$zone = mysqli_fetch_array($zones);
	$LanguageId = $zone['LanguageId'];
    if($LanguageId>0){
        $a_Trespasser = array(
            array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
        );
        $rs->Update('Trespasser',$a_Trespasser, 'Id='.$TrespasserId);
    }
}



$rs_FineTrespasser = $rs->Select('FineTrespasser',"FineId=".$FineId. " AND TrespasserTypeId=".$TrespasserTypeId);
if(mysqli_num_rows($rs_FineTrespasser)>0){

    if($TrespasserId>0){

        $a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
            array('field'=>'Note','selector'=>'field','type'=>'str'),
        );

        $rs->Update('FineTrespasser',$a_FineTrespasser, "FineId=".$FineId. " AND TrespasserTypeId=".$TrespasserTypeId);

    } else {
        $StatusTypeId = 1;

        $rs->Delete('FineTrespasser', "FineId=".$FineId. " AND TrespasserTypeId=".$TrespasserTypeId);


        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$FineId);

    }

} else {
    $StatusTypeId = 10;

    $a_FineTrespasser = array(
        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
        array('field'=>'TrespasserTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'Note','selector'=>'field','type'=>'str'),
        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
    );


    if($TrespasserTypeId==11){
        array_push($a_FineTrespasser, array('field'=>'ReceiveDate','selector'=>'field','type'=>'date'));
        array_push($a_FineTrespasser, array('field'=>'OwnerAdditionalFee','selector'=>'field','type'=>'flt', 'settype'=>'flt'));
    }




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
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            );




            $rs->Insert('FineTrespasser',$a_FineTrespasser);


            $a_Fine = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
            );
            $rs->Update('Fine',$a_Fine, 'Id='.$FineId);

        }
    }

}









$rs->End_Transaction();


header("location: ".$str_BackPage);