<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");
ini_set("display_errors",0);require(INC."/initialization.php");

ini_set('max_execution_time', 5000);

$P = CheckValue('P','s');
$n_ContFine = 0;
$CityId = $_SESSION['cityid'];
$UserId = "'".$_SESSION['username']."'";
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;

$file = fopen($path.$ImportFile,  "r");
$delimiter = "|";

$a_VehicleTypeId = array(
    "Autoveicolo"=>1,
    "Motoveicolo"=>2,
    "Motociclo"=>9,
    "non_definito"=>6,
    "Autobus"=>8,
    "Autocarro"=>4,
    "Autoarticolato"=>12,
);

$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
    $a_Street_Type[$streettype['Title']] = $streettype['Id'];
}

$a_AddressCustomer = array(
    "A"=> "S.S. ex S.P. 432 Km 9+405 dir. Romito Magra",
    "B"=> "S.S. ex S.P. 432 Km 9+405 dir. Senato di Lerici"
);

$rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_Controllers = controllersByFieldArray($rs_Controller,'IspeedCode');

if(is_resource($file)) {
    while (!feof($file)) {


        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0])) {

            $rs->Start_Transaction();


            $a_RowData = explode("_", $row[0]);


            $DetectorCode = $row[11]; //$a_RowData[1]; // forse prima leggeva l'import code dal nome file

            $rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code=" . $DetectorCode);
            $n_Record = mysqli_num_rows($rs_Detector);


            $r_Detector = mysqli_fetch_array($rs_Detector);

            $strDetector = $r_Detector['Kind'];


            $VehiclePlate = strtoupper($row[7]);

            $FineDate = $row[1];
            $FineTime = $row[2];

            $VehicleTypeId = $a_VehicleTypeId[$row[6]];


            $aFineDate = explode("/", $FineDate);


            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
            $ProtocolYear = $aFineDate[2];


            $str_Address = $a_AddressCustomer[$a_RowData[2]];


            $DocumentationTypeId = 1;
            $StatusTypeId = 1;

            $FineTime = $row[2];
            $FineDate = $row[1];

            $Note = '';


            $FineDate = $row[1];
            $aFineDate = explode("/", $FineDate);


            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
            $ProtocolYear = $aFineDate[2];
            $VehicleMass = 0;
            $DepartmentId = 0;

            $DetectorId = $r_Detector['Id'];
            $Documentation = $row[0];
            $str_Locality = $_SESSION['cityid'];


            $a_Address = explode(" ", $str_Address);
            $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


            $SpeedLimit = str_replace(",", ".", $row[3]);
            $SpeedControl = str_replace(",", ".", $row[4]);
            $Code = $a_RowData[0] . "/" . $ProtocolYear;

            $chkTolerance = $r_Detector['Tolerance'];
            $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chkTolerance);
            $ControllerId = getControllerByCode($a_Controllers,$FineDate,trim($row[13]))['Id'];
            $ControllerDate = trim($row[15]);
            $ControllerTime = trim($row[16]);

        $find = getVArticle($DetectorId, $_SESSION['cityid'], $SpeedExcess, $ProtocolYear);
		$ArticleId = $find['Id'];
		$ViolationTypeId = $find['ViolationTypeId'];
        $AdditionalNight = $find['AdditionalNight'];

        $rs_Reasons=getReasonRs($r_Detector['ReasonId'],$CityId,$ViolationTypeId,$DetectorCode);
        $rs_Reason = mysqli_fetch_array($rs_Reasons);
        $ReasonId = $rs_Reason['Id'];

		$Fee = $find['Fee'];
		$MaxFee = $find['MaxFee'];

		if ($AdditionalNight) {
            $aTime = explode(":", $FineTime);

            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                //FINE_MINUTE_START_DAY
                //FINE_MINUTE_END_DAY
                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

            }

        }

		$VehicleCountry = "Italia";
		$CountryId = "Z000";

		$fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . $VehiclePlate . "'");
		$FindNumber = mysqli_num_rows($fines);

		if ($FindNumber == 0) {
            $a_Fine = array(
                array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $str_Locality),
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => $ControllerDate),
                array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'time', 'value' => $ControllerTime),
                array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                array('field' => 'StreetTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StreetTypeId, 'settype' => 'int'),
                array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $str_Address),
                array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                array('field' => 'DepartmentId', 'selector' => 'value', 'type' => 'int', 'value' => $DepartmentId),
                array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleMass, 'settype' => 'int'),
                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
            );

            $FineId = $rs->Insert('Fine', $a_Fine);

            $n_ContFine++;

            if ($FineId == 0) {
                echo "Poblemi con l'inserimento del verbale con targa: " . $VehiclePlate;
                die;
            }

            $a_FineArticle = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
                array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
                array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit+$SpeedExcess, 'settype' => 'flt'),
            );

            $rs->Insert('FineArticle', $a_FineArticle);


            if (file_exists($path . $Documentation)) {
                $DocumentName = $Documentation;
                $str_Folder = ($CountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;

                if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
                    mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
                }
                if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                    mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                }

                $a_FineDocumentation = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                );
                $rs->Insert('FineDocumentation', $a_FineDocumentation);

                copy($path . $Documentation, $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);

                if (file_exists($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName)) {
                    unlink($path . $Documentation);
                } else {
                    echo "Poblemi con la creazione del documento: " . $DocumentName;
                    die;
                }
            }
        }
        $rs->End_Transaction();
    }
    }
    fclose($file);
}

unlink($path.$ImportFile);

if($n_ContFine>0){
    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){
        $str_Content = $r_UserMail['CityTitle'].": sono state elaborate n. ".$n_ContFine." violazioni.";
        $a_Mail = array(
            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione"),
            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail',$a_Mail);
        $rs->End_Transaction();
    }
}
header("location: ".$P);