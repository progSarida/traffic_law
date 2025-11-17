<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");




$rs= new CLS_DB();


$FineId          = CheckValue('FineId','n');


$rs->Start_Transaction();

$rs->Delete('Fine', "Id=".$FineId);
$rs->Delete('FineArticle', "FineId=". $FineId);
$rs->Delete('FineOwner', "FineId=". $FineId);
$rs->Delete('FineDocumentation', "FineId=". $FineId);

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Preavviso cancellato con successo.";
header("location: panel.php");