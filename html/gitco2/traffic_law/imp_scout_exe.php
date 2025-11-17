<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");
require(INC."/initialization.php");
ini_set('max_execution_time', 5000);
ini_set("display_errors",1);

$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$n_ContFine = 0;
$CityId = $_SESSION['cityid'];
$UserId = "'".$_SESSION['username']."'";
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;
$file = fopen($path.$ImportFile,  "r");
$delimiter = detectDelimiter($path . $ImportFile);

$a_VehicleTypeId = array(
    "1"=>1,
    "12"=>1,
    "Motoveicolo"=>2,
    "Motociclo"=>9,
    "non_definito"=>6,
    "3"=>8,
    "5"=>4,
    "Autoarticolato"=>12,
    "6"=>12,
    "A"=>1,
    "U"=>4,
);

$a_country_id = $a_country_title = array();
$a_country_id["I"]='Z000';
$a_country_id["RO"]='Z129';
$a_country_id["F"]='Z110';
$a_country_id["D"]='Z112';
$a_country_id["SLO"]='Z150';
$a_country_id["CH"]='Z133';
$a_country_id["MC"]='Z123';
$a_country_id["MC"]='Z127';
$a_country_title["I"]='Italia';
$a_country_title["RO"]='Romania';
$a_country_title["F"]='Francia';
$a_country_title["D"]='Germania';
$a_country_title["SLO"]='Slovenia';
$a_country_title["CH"]='Svizzera';
$a_country_title["MC"]='Principato di Monaco';
$a_country_title["MC"]='Polonia';

global $rs;
$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
	$a_Street_Type[$streettype['Title']] = $streettype['Id'];
}

$controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
$a_chk_controller = mysqli_fetch_all($controllers,MYSQLI_ASSOC);

if(is_resource($file)) {
    while (!feof($file)) {


        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0]) && $row[0] != 'TARGA') {

            $rs->Start_Transaction();

            $DetectorCode = $row[9];
            $SpeedLimit = $row[32];
            $SpeedControl = $row[30];
            $detector=getDetector($_SESSION['cityid'],$DetectorCode);
            $DocumentationTypeId = 1;
            $StatusTypeId = 1;
            $FineTime = (strlen($row[26]) == 1) ? "0" . $row[26] : $row[26];
            $FineTime .= ":";
            $FineTime .= (strlen($row[27]) == 1) ? "0" . $row[27] : $row[27];
            $FineDate = (strlen($row[25]) == 1) ? "0" . $row[25] : $row[25];
            $FineDate .= "/";
            $FineDate .= (strlen($row[24]) == 1) ? "0" . $row[24] : $row[24];
            $FineDate .= "/" . $row[23];
            $VehicleTypeId = $a_VehicleTypeId[$row[1]];
            $VehiclePlate = strtoupper($row[0]);
            $a_Gps = explode("  ", trim($row[34]));
            $GpsLat = $a_Gps[0];
            $GpsLong = $a_Gps[1];
            $Address = $row[5] . " " . $row[6];
            $a_Address = explode(" ", $Address);
            $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;
            $Note = '';

            $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='" . $_SESSION['cityid'] . "' AND VehiclePlate='" . $VehiclePlate . "'");
            if (mysqli_num_rows($rs_VehicleWhiteList) > 0) {
                $StatusTypeId = 90;
                $Note = 'White list ente';
            }

            $VehicleMass = CheckValue('VehicleMass', 'n');
            $aFineDate = explode("/", $FineDate);
            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
            $ProtocolYear = $aFineDate[2];
            $Code = trim($row[13]) . "/" . $ProtocolYear;
            $controller=getControllerFromArrayByField($a_chk_controller,array('Code'=>trim($row[20])),$FineDate);
            $ControllerId = $controller['Id'];
            $ControllerDate = date("Y-m-d", filemtime($path . $ImportFile));
            $ControllerTime = date("H:i", filemtime($path . $ImportFile));
            $DepartmentId = CheckValue('DepartmentId', 'n');
            $detector = getDetector($_SESSION['cityid'], $DetectorCode);
            $DetectorId = $detector['Id'];
            $chkTolerance = $detector['Tolerance'];
            $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;
            $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chkTolerance);
            $find = getVArticle($DetectorId, $_SESSION['cityid'], $SpeedExcess, $ProtocolYear);
            $ArticleId = $find['Id'];
            $ViolationTypeId = $find['ViolationTypeId'];
            $ViolationTitle = $find['ViolationTitle'];
            $Fee = $find['Fee'];
            $MaxFee = $find['MaxFee'];
            $reasonRs =getReasonRs($detector['ReasonId'],$_SESSION['cityid'],$ViolationTypeId,$DetectorCode);
            $ReasonId=mysqli_fetch_array($reasonRs)["Id"];
            if($ReasonId==null)
                $ReasonId=252;
            $aTime = explode(":", $FineTime);

            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                //FINE_MINUTE_START_DAY
                //FINE_MINUTE_END_DAY
                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
            }

            $VehicleCountry = $a_country_title[$row[4]];
            $CountryId = $a_country_id[$row[4]];

            $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . $VehiclePlate . "'");
            $FindNumber = mysqli_num_rows($fines);

            if ($FindNumber == 0) {
                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                    array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                    array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                    array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                    array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => $ControllerDate),
                    array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'str', 'value' => $ControllerTime),
                    array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'StreetTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StreetTypeId, 'settype' => 'int'),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                    array('field' => 'GpsLat', 'selector' => 'value', 'type' => 'str', 'value' => $GpsLat),
                    array('field' => 'GpsLong', 'selector' => 'value', 'type' => 'str', 'value' => $GpsLong),
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

                if (isset($a_controller[trim($row[21])])) {
                    if ($ControllerId != $a_controller[trim($row[21])]) {
                        $ControllerId = $a_controller[trim($row[21])];
                        $a_AdditionalController = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                        );
                        $rs->Insert('FineAdditionalController', $a_AdditionalController);
                    }
                }
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

                if (file_exists($path . $row[14] . ".jpg") && file_exists($path . $row[15] . ".jpg")) {
                    $Documentation = array($row[14] . ".jpg", $row[15] . ".jpg");
                    for ($i = 0; $i <= 1; $i++) {
                        $DocumentName = $Documentation[$i];
                        $str_Folder = ($a_country_id[$row[4]] == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                        if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
                        if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);

                        $a_FineDocumentation = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                        );
                        $rs->Insert('FineDocumentation', $a_FineDocumentation);

                        if ($compress) {
                            $img = new Imagick($path . $DocumentName);
                            $width = intval($img->getimagewidth() / 3);
                            $height = intval($img->getimageheight() / 3);
                            $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                            $img->setImageCompressionQuality(40);
                            $img->stripImage();
                            $img->writeImage($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);
                            $img->destroy();

                        } else {
                            copy($path . $DocumentName, $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);
                        }


                        if (file_exists($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName)) {
                            unlink($path . $DocumentName);
                        } else {
                            echo "Poblemi con la creazione del documento: " . $DocumentName;
                            die;
                        }

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