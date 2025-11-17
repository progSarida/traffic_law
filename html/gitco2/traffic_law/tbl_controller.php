<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_table.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$str_CurrentPage = curPageName();


$datareg = "'".date("Y-m-d")."'";
$timereg = "'".date("H:i")."'";
$userreg = "'".$_SESSION['username']."'";


 	 	 	//





$aField = array(
    array('field'=>'Id','type'=>'int','hidden'=>true,'label'=>'Id','name'=>'Id','getvaluefield'=>'Id'),
    array('field'=>'Name','type'=>'str','hidden'=>false,'label'=>'Nominativo','name'=>'Name','getvaluefield'=>'Name','width'=>'5'),
    array('field'=>'Sign','type'=>'str','hidden'=>false,'label'=>'Firma','name'=>'Sign','getvaluefield'=>'Sign','width'=>'4'),
    array('field'=>'Disabled','type'=>'str','hidden'=>false,'label'=>'Cessato','name'=>'Disabled','getvaluefield'=>'Disabled','width'=>'2'),

);

$page = CheckValue('page','n');
$clsTable = new CLS_TABLE('Controller');

$clsTable->dButton = false;

$clsTable->idField = 'Id';
$clsTable->filter = "CityId='".$_SESSION['cityid']."'";

$clsTable->page = $page;
$clsTable->LinkPage = $str_CurrentPage;
$clsTable->aField = $aField;
$clsTable->Create_Page();

echo $clsTable->str_out;

include(INC."/footer.php");