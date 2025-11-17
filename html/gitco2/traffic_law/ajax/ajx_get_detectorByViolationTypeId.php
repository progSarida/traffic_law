<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$ViolationTypeId = CheckValue('ViolationTypeId', 's');
$Options = "<option></option>";
$n_Detector = 0;

if ($ViolationTypeId != ""){
    $rs_Detector = $rs->SelectQuery("SELECT D.TitleIta, D.Id FROM Detector D JOIN DetectorType DT on D.DetectorTypeId = DT.Id WHERE DT.ViolationTypeId =".$ViolationTypeId." AND D.CityId='".$_SESSION['cityid']."'");
    $n_Detector = mysqli_num_rows($rs_Detector);
    
    while ($r_Detector = mysqli_fetch_array($rs_Detector)){
        $Options .= '<option value='.$r_Detector['Id'].'>'.StringOutDB($r_Detector['TitleIta']).'</option>';
    }
}

echo json_encode(
    array(
        "Options" => $Options,
        "Results" => $n_Detector
    )
);