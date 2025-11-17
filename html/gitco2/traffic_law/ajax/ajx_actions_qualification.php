<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$ActionType = $_POST['ActionType'];

if ($ActionType == "fetch"){
    $QualificationId = $_POST['QualificationId'];
    
    $rs_Qualification = $rs->Select("QualificationType", "Id=$QualificationId");
    $Description = mysqli_fetch_array($rs_Qualification)['Description'];
    echo json_encode(
        array(
            "Description" => StringOutDB($Description),
        )
    );
}

if ($ActionType == "check"){
    $Description = $_POST['Description'];
    $OldDescription = $_POST['OldDescription'];
    $CityId = $_SESSION['cityid'];
    
    if ($OldDescription != "")
        $rs_Qualification = $rs->Select("QualificationType", "Description='$Description' AND Description<>'$OldDescription'  AND CityId='$CityId'");
    else
        $rs_Qualification = $rs->Select("QualificationType", "Description='$Description' AND CityId='$CityId'");
    
    echo json_encode(
        array(
            "Result" => mysqli_num_rows($rs_Qualification) > 0 ? "NO" : "OK",
            "Description" => $Description,
            "OldDescription" => $OldDescription,
        )
    );
    
}