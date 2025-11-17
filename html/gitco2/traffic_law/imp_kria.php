<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/function_import.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chk_Tolerance = 0;
$error = false;
$msgProblem = "";
/*

2085
moto 60
auto 80

*/

$a_VehicleTypeId = array(
    "autoveicolo"=>1,
    "motoveicolo"=>2,
    "motociclo"=>9,
    "non_definito"=>6,
    "autobus"=>8,
    "autocarro"=>4,
    "autoarticolato"=>12,
    "rimorchio"=>7,
);

if($_SESSION['cityid']=='U480')
    require_once("imp_kria_U480.php");
else
    require_once("imp_kria_generic.php");

include(INC."/footer.php");