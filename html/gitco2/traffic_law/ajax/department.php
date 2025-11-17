<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$strDepartment= "<option></option>";
//$strDepartment= "";
$id = $_POST['id'];

$departments = $rs->Select("Department","CountryId='".$id."'","Code");


while($department = mysqli_fetch_array($departments)){
	$strDepartment .= '<option value="'.$department['Id'].'">'.$department['Code'].'</option>';
}


echo $strDepartment;