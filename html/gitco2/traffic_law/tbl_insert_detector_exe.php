<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');



$rs->Start_Transaction();


$CityId = $_SESSION['cityid'];




$detector = $rs->Select('Detector', "CityId='".$CityId."'");
$RowNumber = mysqli_num_rows($detector);



$a_Detector = array(
	array('field'=>'Kind','selector'=>'field','type'=>'str'),
	array('field'=>'Brand','selector'=>'field','type'=>'str'),
	array('field'=>'Model','selector'=>'field','type'=>'str'),
	array('field'=>'Ratification','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'TitleIta','selector'=>'field','type'=>'str'),
	array('field'=>'TitleGer','selector'=>'field','type'=>'str'),
	array('field'=>'TitleSpa','selector'=>'field','type'=>'str'),
	array('field'=>'TitleEng','selector'=>'field','type'=>'str'),
	array('field'=>'TitleFre','selector'=>'field','type'=>'str'),
	
	
);

if ($RowNumber == 0) {


	$DetectorId = $rs->Insert('Detector',$a_Detector);
} else {
	$detector_s = mysqli_fetch_array($detector);
	$DetectorId = $detector_s['Id'];

	$rs->Update('Detector',$a_Detector,"Id=".$DetectorId);
}





$rs->End_Transaction();

header("location: tbl_detector.php");