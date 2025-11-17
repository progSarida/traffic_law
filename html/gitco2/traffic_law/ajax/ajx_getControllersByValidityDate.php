<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

//Restituisce gli accertatori la cui validità rientra nella data passata
//FineDigitalSign : specifica se filtrare per gli accertatori con firma verbale digitale abilitato o no

$Date = DateInDB(CheckValue('Date', 's'));
$FineDigitalSign = $_POST['FineDigitalSign'] ?? false;

$str_WHhereControllers = "CityId='{$_SESSION['cityid']}' AND ('$Date' >= FromDate OR FromDate IS NULL) AND ('$Date' <= ToDate OR ToDate IS NULL) AND Disabled=0 AND ChiefController=1";
if($FineDigitalSign) $str_WHhereControllers .= " AND FineDigitalSign=1";

$a_Results = array();

if($Date){
    $rs_Controllers = $rs->Select('Controller', $str_WHhereControllers, "CAST(Code AS UNSIGNED)");
    
    if(mysqli_num_rows($rs_Controllers) > 0){
        while($r_Controller = $rs->getArrayLine($rs_Controllers)){
            $o_Result = new stdClass();
            
            $o_Result->id = $r_Controller['Id'];
            $o_Result->code = $r_Controller['Code'];
            $o_Result->name = $r_Controller['Name'];
            $o_Result->qualification = $r_Controller['Qualification'];
            
            array_push($a_Results, $o_Result);
        }
    }
}

echo json_encode(
    array(
        "Result" => $a_Results,
    )
);