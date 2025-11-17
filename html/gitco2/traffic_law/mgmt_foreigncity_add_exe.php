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

$CountryId = CheckValue('CountryId','s');
$Title = strtoupper(CheckValue('Title','s'));
$Zip = CheckValue('Zip','s');
$LandId = CheckValue('LandId','n');


$rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$CountryId.'" AND Title="'.$Title.'"');

if(mysqli_num_rows($rs_ForeignCity)>0){
    header("location: mgmt_foreigncity_add.php".$Parameters."&error=Già presente!");
    DIE;
}

$a_ForeignCity = array(
    array('field'=>'CountryId','selector'=>'value','value'=>$CountryId,'type'=>'str'),
    array('field'=>'Zip','selector'=>'value','value'=>$Zip,'type'=>'str'),
    array('field'=>'Title','selector'=>'value','value'=>$Title,'type'=>'str'),
);

if ($LandId != "")
    $a_ForeignCity[] = array('field'=>'LandId','selector'=>'value','value'=>$LandId,'type'=>'int','settype'=>'int');

$rs->Insert('ForeignCity', $a_ForeignCity);


$rs->End_Transaction();


header("location: mgmt_foreigncity.php".$Parameters."&answer=Inserito con successo.");
