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

$Id = (CheckValue('ForeignCityId','n'));

$rs->Delete('ForeignCity', "Id=$Id");

$rs->End_Transaction();


header("location: mgmt_foreigncity.php".$Parameters."&answer=Eliminato con successo.");
