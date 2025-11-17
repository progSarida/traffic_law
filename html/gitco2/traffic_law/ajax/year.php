<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$strYear= "<option></option>";
$id = $_POST['id'];

for($i=0;$i<count($_SESSION['YearArray'][MENU_ID][$id]);$i++){
	$strYear .= '<option value="'.$_SESSION['YearArray'][MENU_ID][$id][$i].'" style="color:#294A9C">'.$_SESSION['YearArray'][MENU_ID][$id][$i].'</option>';
}

echo $strYear;