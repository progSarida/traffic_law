<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");




$rs= new CLS_DB();



$n_ArticleNumber = 1;

$VehiclePlate       = strtoupper(CheckValue('VehiclePlate','s'));
$Address            = strtoupper(CheckValue('Address','s'));
$ArticleId          = CheckValue('ArticleId','n');
$CountryId          = CheckValue('CountryId','s');
$Locality           = CheckValue('Locality','s');
$FineId          = CheckValue('FineId','n');

$rs_Country = $rs->Select('Country', "Id='".$CountryId."'");
$VehicleCountry = mysqli_fetch_array($rs_Country)['Title'];
$VehicleBrand        = strtoupper(CheckValue('VehicleBrand','s'));

if($ArticleId>0){
    $rs_ArticleApp = $rs->Select('ArticleApp', "Id=".$ArticleId);
    $r_ArticleApp = mysqli_fetch_array($rs_ArticleApp);

    $ArticleId          = $r_ArticleApp['ArticleId'];
    $str_ArticleText    = $r_ArticleApp['Description'];


}






if($Locality=="") $Locality = $_SESSION['usercity'];

$StatusTypeId = 13;



$a_Fine = array(
    array('field'=>'FineDate','selector'=>'field','type'=>'date'),
    array('field'=>'FineTime','selector'=>'field','type'=>'str'),
    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'VehicleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
    array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$VehicleCountry),
    array('field'=>'CountryId','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleBrand','selector'=>'value','type'=>'str','value'=>$VehicleBrand),
    array('field'=>'VehicleModel','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleColor','selector'=>'field','type'=>'str'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),

);



$rs->Update('Fine', $a_Fine, "Id=".$FineId);


if($ArticleId>0){
    $Where = "Disabled=0 AND CityId='" . $_SESSION['usercity'] . "' AND Year=" . Date("Y") . " AND Id=". $ArticleId;
    $finds = $rs->Select('V_Article', $Where);

    $find = mysqli_fetch_array($finds);

    $ArticleId          = $find['Id'];
    $Fee                = $find['Fee'];
    $MaxFee             = $find['MaxFee'];
    $ViolationTypeId    = $find['ViolationTypeId'];
    $LicensePoint       = $find['LicensePoint'];


    $str_Where = " ReasonTypeId=1 AND CityId='" . $_SESSION['usercity'] . "' AND ViolationTypeId=" . $ViolationTypeId;;


    $rs_Reason = $rs->Select('Reason', $str_Where);
    $r_Reason = mysqli_fetch_array($rs_Reason);

    $ReasonId = $r_Reason['Id'];


    $a_FineArticle = array(
        array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['usercity']),
        array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId, 'settype'=>'int'),
        array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
        array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
        array('field'=>'LicensePoint','selector'=>'field','type'=>'int','value'=>$LicensePoint,'settype'=>'int'),
    );

    $rs->Update('FineArticle',$a_FineArticle, "FineId=". $FineId);




    $a_FineOwner = array( array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText) );
    $rs->Update('FineOwner', $a_FineOwner, "FineId=". $FineId);

}






$_SESSION['Message']['Success'] = "Inserito con successo.";
header("location: panel.php");
