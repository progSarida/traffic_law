<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$CountryId = CheckValue('CountryId','s');
$Title = strtoupper(CheckValue('Title','s'));
$Zip = CheckValue('Zip','s');
$LandId = CheckValue('LandId','n');

$rs->Start_Transaction();

$rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$CountryId.'" AND Title="'.$Title.'"');

if(mysqli_num_rows($rs_ForeignCity)>0){
    echo json_encode(
        array(
            "Message" => "Città già presente per questa nazione.",
            "Answer" => "NO",
        )
    );
} else {
    $a_ForeignCity = array(
        array('field'=>'CountryId','selector'=>'value','value'=>$CountryId,'type'=>'str'),
        array('field'=>'Zip','selector'=>'value','value'=>$Zip,'type'=>'str'),
        array('field'=>'Title','selector'=>'value','value'=>$Title,'type'=>'str'),
    );
    
    if ($LandId != "")
        $a_ForeignCity[] = array('field'=>'LandId','selector'=>'value','value'=>$LandId,'type'=>'int','settype'=>'int');
        
    $CityInsertId = $rs->Insert('ForeignCity', $a_ForeignCity);
    
    $rs->End_Transaction();
        
    echo json_encode(
        array(
            "CityId" => $CityInsertId,
            "CityTitle" => $Title,
            "CountryId" => $CountryId,
            "Answer" => "OK",
        )
    );
}

