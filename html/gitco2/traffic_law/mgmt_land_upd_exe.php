<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}

$rs->Start_Transaction();

$LandId = CheckValue('LandId','n');
$Title = strtoupper(CheckValue('Title','s'));
$CountryId = CheckValue('CountryId','s');

$rs_Land = $rs->Select('sarida.Land', "Id=$LandId");
$r_Land = mysqli_fetch_array($rs_Land);

$rs_CheckLand = $rs->Select('sarida.Land', 'Title="'.$Title.'" AND CountryId="'.$CountryId.'" And Title<>"'.StringOutDB($r_Land['Title']).'"');
if(mysqli_num_rows($rs_CheckLand)>0){
    header("location: mgmt_land.php$Parameters&error=Land già presente per la nazione selezionata.");
    DIE;
}

$a_Land = array(
    array('field'=>'Title','selector'=>'value','value'=>$Title,'type'=>'str'),
);

$rs->Update('sarida.Land', $a_Land, "Id=$LandId");

$rs->End_Transaction();

header("location: mgmt_land.php$Parameters&answer=Modificato con successo.");
