<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$strSpeed = "";

$rs= new CLS_DB();

$DetectorId= $_POST['DetectorId'];
$CityId = $_SESSION['cityid'];


$Detectors = $rs->Select('Detector',"CityId='".$CityId."' AND Id=".$DetectorId);
$Detector = mysqli_fetch_array($Detectors);

$DetectorTypeId = $Detector['DetectorTypeId'];
$UpladNumber = $Detector['UploadImageNumber'];
//$Ratification = $Detector['Ratification'];
// Mod Daniela per evitare caratteri speciali in data 3 Luglio 2020
$Ratification = StringOutDB($Detector['Ratification']) ?? '';

echo json_encode(
    array(
        "DetectorTypeId" => $DetectorTypeId,
        "UploadNumber" => $UpladNumber,
        "Ratification" => $Ratification,
    )
);

//echo  $strSpeed;