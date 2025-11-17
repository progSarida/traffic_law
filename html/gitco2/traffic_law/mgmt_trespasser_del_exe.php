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

$TrespasserId = CheckValue('TrespasserId','n');

$rs->Start_Transaction();

$rs->Delete('Trespasser', "Id=$TrespasserId");

$rs->Delete('TrespasserHistory', "TrespasserId=$TrespasserId");

$rs->Delete('TrespasserContact', "TrespasserId=$TrespasserId");

$rs->Delete('TrespasserContactHistory', "TrespasserId=$TrespasserId");

$rs->End_Transaction();

header("location: mgmt_trespasser.php$Parameters&answer=Eliminato con successo!");