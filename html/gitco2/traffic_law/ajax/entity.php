<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$strEntity= "<option></option>";
$id = $_POST['id'];

$entities = $rs->Select("Entity","CountryId='".$id."'","Region");


while($entity = mysqli_fetch_array($entities)){
	$strEntity .= '<option value="'.$entity['Region'].'">'.$entity['Region'].'</option>';
}


echo $strEntity;