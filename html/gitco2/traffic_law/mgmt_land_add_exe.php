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

$Title = strtoupper(CheckValue('Title','s'));
$CountryId = CheckValue('CountryId','s');

$rs_Land = $rs->Select("sarida.Land", 'Title="'.$Title.'" AND CountryId="'.$CountryId.'"');

if(mysqli_num_rows($rs_Land)>0){
    header("location: mgmt_land.php".$Parameters."&error=Land già presente per la nazione selezionata.");
    DIE;
}

$a_Land = array(
    array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryId),
    array('field'=>'Title','selector'=>'value','type'=>'str','value'=>$Title),
);

$rs->Insert('sarida.Land', $a_Land);


$rs->End_Transaction();


header("location: mgmt_land.php".$Parameters."&answer=Inserito con successo.");
