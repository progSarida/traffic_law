<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/function_import.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 3000);

$P = CheckValue('P', 's');
$compress = CheckValue('compress', 'n');
$CityId = $_SESSION['cityid'];

$rs_Customer = $rs->Select('Customer', "CityId='" . $CityId . "'");
$n_Validation = mysqli_fetch_array($rs_Customer)['Validation'];

$DepartmentId = 0;
$VehicleMass = 0;
$StatusTypeId = ($n_Validation == 1) ? 0 : 1;

$UserId = "'" . $_SESSION['username'] . "'";

$path = PUBLIC_FOLDER . "/_VIOLATION_/" . $_SESSION['cityid'] . "/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;
$VehicleTypeId = 1;


$file = fopen($path . $ImportFile, "r");
$delimiter = detectDelimiter($path . $ImportFile);

$TimeTLightFirst = 0;
$TimeTLightSecond = 0;
$b_TrafficLight = false;

$str_TLImageFirst = "";
$str_TLImageSecond = "";

$n_ContFine = 0;
$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)) {
    $a_Street_Type[$streettype['Title']] = $streettype['Id'];
}


$controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
$a_controller = array();
while ($controller = mysqli_fetch_array($controllers)) {
    $a_controller[$controller['Code']] = $controller['Id'];
}

$a_chk_VehicleType = array(
    "1"=>1,
    "14"=>2,
    "10"=>4,
    "2"=>3,
    "5"=>4,
    "12"=>5
);




$Note = "Caricamento da file";
if(is_resource($file)) {
    while (!feof($file)) {
        $rs->Start_Transaction();
        $row = fgetcsv($file, 1000, $delimiter);

        if (isset($row[0]) && $row[0] != 'Ticket') {
            $ViolationTypeId = "";
            $n_DetectorTypeId = 0;


            $chk_Speed = true;


            $country = trim($row[10]);
            if ($country == 1) {
                $VehicleCountry = "Italia";
                $CountryId = "Z000";
            } else {
                $VehicleCountry = "Da assegnare";
                $CountryId = "ZZZZ";
            }


            $DocumentationTypeId = 1;

            $DetectorCode = trim($row[4]);


            $rs_detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode . "'");


            $r_detector = mysqli_fetch_array($rs_detector);
            $strDetector = $r_detector['Kind'];
            $chkTolerance = $r_detector['Tolerance'];
            $n_DetectorTypeId = $r_detector['DetectorTypeId'];
            $DetectorId = $r_detector['Id'];


            $FineDate = trim($row[6]);
            $FineTime = trim($row[7]);

            $Address = trim($row[5]);
            $StreetTypeId = (array_key_exists(strtoupper($Address), $a_Street_Type)) ? $a_Street_Type[strtoupper($Address)] : 0;


            $Locality = trim($row[3]);

            $ControllerName = trim($row[23]);
            $VehiclePlate = trim($row[8]);
            $ControllerId = $a_controller[trim($row[23])];
            $ControllerDate = trim($row[21]);
            $ControllerTime = trim($row[22]);
            $aFineDate = explode("/", $FineDate);
            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
            $ProtocolYear = $aFineDate[2];
            $VehicleTypeId = trim($row[11]);
            $VehicleTypeId = $a_chk_VehicleType[$VehicleTypeId];


            if ($n_DetectorTypeId == 2) {
                $b_TrafficLight = true;

                $TimeTLightFirst = trim($row[33]);
                $TimeTLightSecond = trim($row[33]);

                $Where = "Article=146 AND Paragraph=3 AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;
                $finds = $rs->Select('V_Article', $Where);
                $find = mysqli_fetch_array($finds);
                $ArticleId = $find['Id'];

                $ViolationTypeId = $find['ViolationTypeId'];
                $Fee = $find['Fee'];
                $MaxFee = $find['MaxFee'];
                $Speed = 0.00;
                $SpeedLimit = 0.00;
                $SpeedControl = 0.00;
            } else {
                $SpeedLimit = trim($row[30]);
                $SpeedControl = trim($row[31]);
                $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;
                $detector=getDetector($_SESSION['cityid'],$DetectorCode,$cont);
                $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chkTolerance);
                $find = getVArticle($detector['Id'], $_SESSION['cityid'], $SpeedExcess, $ProtocolYear);
                $ArticleId = $find['Id'];

                $ViolationTypeId = $find['ViolationTypeId'];
                $Fee = $find['Fee'];
                $MaxFee = $find['MaxFee'];
                $TimeTLightFirst = 0;
                $TimeTLightSecond = 0;

                $aTime = explode(":", $FineTime);

                if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                    //FINE_MINUTE_START_DAY
                    //FINE_MINUTE_END_DAY
                    $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                    $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                }
            }

            $Address = utf8_encode($Address);

            $rs_Localities = $rs->Select(MAIN_DB . '.City', "Title='" . trim($Locality) . "'");
            $rs_Locality = mysqli_fetch_array($rs_Localities);
            $Locality = $rs_Locality['Id'];


            $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . strtoupper($VehiclePlate) . "'");
            $FindNumber = mysqli_num_rows($fines);

            if ($FindNumber == 0 && $chk_Speed) {
                $Code = trim($row[0]) . "/" . $ProtocolYear;
                $rs_Reason = getReasonRs($DetectorCode,$_SESSION['cityid'],$ViolationTypeId);
                $ReasonId = $rs_Reason['Id'];

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
                    array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $Locality),
                    array('field' => 'StreetTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StreetTypeId, 'settype' => 'int'),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
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
                    array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                    array('field' => 'TimeTLightFirst', 'selector' => 'value', 'type' => 'int', 'value' => $TimeTLightFirst, 'settype' => 'int'),
                    array('field' => 'TimeTLightSecond', 'selector' => 'value', 'type' => 'int', 'value' => $TimeTLightSecond, 'settype' => 'int'),
                );

                $rs->Insert('FineArticle', $a_FineArticle);

                $a_FileLetter = array("_A.jpg", "_B.jpg", "_F.jpg");

                $str_Folder = ($CountryId == "Z000") ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;

                if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                    mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                }

                for ($i = 0; $i < count($a_FileLetter); $i++) {
                    if (file_exists($path . trim($row[1]) . $a_FileLetter[$i])) {

                        $a_FineDocumentation = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => trim($row[1]) . $a_FileLetter[$i]),
                            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                        );
                        $rs->Insert('FineDocumentation', $a_FineDocumentation);

                        if ($compress) {
                            $img = new Imagick($path . trim($row[1]) . $a_FileLetter[$i]);
                            $width = intval($img->getimagewidth() / 3);
                            $height = intval($img->getimageheight() / 3);
                            $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                            $img->setImageCompressionQuality(40);
                            $img->stripImage();
                            $img->writeImage($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . trim($row[1]) . $a_FileLetter[$i]);
                            $img->destroy();

                        } else {
                            copy($path . trim($row[1]) . $a_FileLetter[$i], $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . trim($row[1]) . $a_FileLetter[$i]);
                        }


                        if (file_exists($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . trim($row[1]) . $a_FileLetter[$i])) {
                            unlink($path . trim($row[1]) . $a_FileLetter[$i]);
                        } else {
                            echo "Poblemi con la creazione del documento: " . trim($row[1]) . $a_FileLetter[$i];
                            die;
                        }
                    }
                }
            } else {
                if (file_exists($path . $Documentation)) {
                    //unlink($path . $Documentation);
                };
                die;
            }
        }
        $rs->End_Transaction();
    }
    fclose($file);
}
if ($n_ContFine > 0) {
    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM " . MAIN_DB . ".V_UserCity WHERE UserLevel>=3 AND CityId='" . $CityId . "'");
    while ($r_UserMail = mysqli_fetch_array($rs_UserMail)) {
        $str_Content = $r_UserMail['CityTitle'] . ": sono stati elaborati n. " . $n_ContFine . " preinserimenti";
        $a_Mail = array(
            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
            array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
            array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Nuova importazione"),
            array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_UserMail['UserId'], 'settype' => 'int'),
            array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail', $a_Mail);
        $rs->End_Transaction();
    }
}

unlink($path . $ImportFile);
header("location: imp_project.php");