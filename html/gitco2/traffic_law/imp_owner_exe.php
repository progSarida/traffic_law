<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
include(INC . "/function_import.php");
ini_set('max_execution_time', 3000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$n_ContFine = 0;

$CityId = $_SESSION['cityid'];

$StatusTypeId = 1;

$UserId = "'".$_SESSION['username']."'";

$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;

$chk_GlobalImage = false;
$a_VehiclePlate = array();


$file = fopen($path.$ImportFile,  "r");
$delimiter = detectDelimiter($path . $ImportFile);
$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";



$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
	$a_Street_Type[$streettype['Title']] = $streettype['Id'];
}


$rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_Controllers = controllersByCodeArray($rs_Controller);


$rs_VehicleType = $rs->Select('VehicleType', "Disabled=0");
$a_chk_VehicleType = array();
while ($r_VehicleType = mysqli_fetch_array($rs_VehicleType)){
    $a_chk_VehicleType[strtoupper($r_VehicleType['TitleIta'])] = $r_VehicleType['Id'];
}



$a_Country = array(
    "BG" => "Z104",
    "A" => "Z102",
    "I" => "Z000",
    "CH" => "Z133",
    "LT" => "Z146",
    "H" => "Z134",
    "SK" => "Z155",
    "D" => "Z112",
    "RO" => "Z129",
    "F" => "Z110",
    "EE" => "ZZZZ",
    "GB" => "Z114",
    "PL" => "Z127",
    "NL" => "Z126",

);

$a_CountryText = array(
    "BG" => "Bulgaria",
    "A" => "Austria",
    "I" => "Italia",
    "CH"=> "Svizzera",
    "LT"=> "Lituania",
    "H"=> "Ungheria",
    "SK" => "Slovacchia",
    "D" => "Germania",
    "RO" => "Romania",
    "F" => "Francia",
    "EE" => "Da assegnare",
    "GB" => "Regno Unito",
    "PL" => "Polonia",
    "NL" => "Olanda",
);





$row = fgets($file);
if(is_resource($file)) {
    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0]) && $row[0] != 'Numero / Riferimento') {

            $chk_VehiclePlate = true;

            $DetectorId = 0;
            $ReasonId = null;
            $DocumentationTypeId = 1;
            $str_FixedWhere = "Fixed IS NULL";


            $DetectorCode = $row[10];
            $DetectorCode = str_replace("Matricola: ", "", $DetectorCode);
            $SpeedLimit = $row[20];
            $SpeedControl = $row[19];
            $SpeedDifference = $row[21];

            $Code = $row[0];

            $FineDate = $row[1];
            $FineTime = $row[2];


            $VehiclePlate = strtoupper($row[4]);
            $str_VehicleTypeId = strtoupper(trim($row[5]));

            if ($str_VehicleTypeId == "AUTOVETTURA") $str_VehicleTypeId = "AUTOVEICOLO";
            if ($str_VehicleTypeId == "CICLOMOTORE") $str_VehicleTypeId = "CICLOMOTORE";
            if ($str_VehicleTypeId == "A") $str_VehicleTypeId = "AUTOVEICOLO";
            if ($str_VehicleTypeId == "M") $str_VehicleTypeId = "MOTOVEICOLO";
            if ($str_VehicleTypeId == "ARTICOLATO") $str_VehicleTypeId = "AUTOARTICOLATO";
            if ($str_VehicleTypeId == "CISTERNA") $str_VehicleTypeId = "AUTOARTICOLATO";
            if ($str_VehicleTypeId == "MOTOCICLO") $str_VehicleTypeId = "MOTOVEICOLO";


            $VehicleTypeId = $a_chk_VehicleType[$str_VehicleTypeId];
            $VehicleBrand = $row[6];
            $VehicleColor = $row[7];


            $Locality = strtoupper($row[13]);
            $Address = trim($row[14]);
            if (trim($row[15]) != "") $Address .= " " . trim($row[15]);

            if (trim($row[16]) != "") $Address .= " Direzione " . trim($row[16]);


            if (isset($a_Country[trim($row[3])])) {
                $VehicleCountry = $a_CountryText[strtoupper(trim($row[3]))];
                $CountryId = $a_Country[strtoupper(trim($row[3]))];
            } else {
                $CountryId = "ZZZZ";
                $VehicleCountry = "Da assegnare";
            }


            if (strpos($FineDate, 'T') !== false) {


                $str_DateTime = str_replace("T", "_", $FineDate);
                $str_DateTime = str_replace(":", "-", $str_DateTime);
                $str_DateTime = str_replace(".", "-", $str_DateTime);


                $a_DateTime = explode("T", $FineDate);

                $FineDate = $a_DateTime[0];
                $FineTime = $a_DateTime[1];

            } else if ($FineTime == "") {
                $str_DateTime = str_replace(" ", "_", $FineDate);
                $str_DateTime = str_replace(":", "-", $str_DateTime);
                $str_DateTime = str_replace(".", "-", $str_DateTime);


                $a_DateTime = explode(" ", $FineDate);

                $FineDate = $a_DateTime[0];
                $FineTime = $a_DateTime[1];
            }


            if (strpos($FineDate, '/') !== false) {
                $aFineDate = explode("/", $FineDate);

                $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                $ProtocolYear = $aFineDate[2];

            } else {
                $ProtocolYear = substr($FineDate, 0, 4);
            }

            $aFineTime = explode(":", $FineTime);
            if (strlen($aFineTime[0]) < 2) $aFineTime[0] = "0" . $aFineTime[0];
            if (strlen($aFineTime[1]) < 2) $aFineTime[1] = "0" . $aFineTime[1];
            $FineTime = $aFineTime[0] . ":" . $aFineTime[1];

            if (strpos($row[11], "+") === false) {
                $ControllerCode = $row[11];
                $AdditionalControllerCode = 0;
            } else {
                $a_ControllerCode = explode("+", trim($row[11]));
                $ControllerCode = $a_ControllerCode[0];
                $AdditionalControllerCode = getControllerByCode($a_Controllers, $FineDate, $a_ControllerCode[1]);
            }

            $Note = "Caricamento da file";


            $ControllerId = getControllerByCode($a_Controllers, $FineDate, $ControllerCode);


            $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;
            $b_AdditionalArticle = false;
            $str_Date = "";
            if (strlen(trim($SpeedLimit)) > 0) {
                $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='$DetectorCode'");
                $detector = mysqli_fetch_array($detectors);

                $DetectorId = $detector['Id'];
                $chkTolerance = $detector['Tolerance'];
                $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
                $ReasonId = $detector['ReasonId'];
                $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

                $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


                if ($SpeedDifference > 0) {
                    $Speed = $SpeedControl;
                    $SpeedControl += $chkTolerance;


                } else {
                    $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

                    $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                    $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


                    $Speed = $SpeedControl - $Tolerance;

                }

                $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chkTolerance);
                $r_Article = getVArticle($detector['Id'], $_SESSION['cityid'], $SpeedExcess, $ProtocolYear);
                $ArticleId = $r_Article['Id'];
                $Fee = $r_Article['Fee'];
                $MaxFee = $r_Article['MaxFee'];
                $ViolationTypeId = $r_Article['ViolationTypeId'];
                $aTime = explode(":", $FineTime);
                if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                    $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                    $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                }
            } else {


                $SpeedLimit = 0.00;
                $SpeedControl = 0.00;
                $Speed = 0.00;

                if ($DetectorCode != "") {
                    $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code=" . $DetectorCode);
                    $FindNumber = mysqli_num_rows($detectors);
                    $detector = mysqli_fetch_array($detectors);
                    $DetectorId = $detector['Id'];
                    $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
                    $ReasonId = $detector['ReasonId'];
                }


                if (strpos($row[8], "+") === false) {
                    if (strpos($row[8], "/") === false) {
                        $a_Article = explode("-", trim($row[8]));
                    } else {
                        $a_Article = explode("/", trim($row[8]));
                    }
                } else {
                    $b_AdditionalArticle = true;
                    $a_AdditionalArticle = explode("+", trim($row[8]));
                    $a_Article = explode("-", trim($a_AdditionalArticle[0]));

                }
                $Where .= " AND Id1=" . trim($a_Article[0]) . " AND Id2='" . trim($a_Article[1]) . "'";
                if (isset($a_Article[2])) $Where .= " AND Id3='" . trim($a_Article[2]) . "'";


                $finds = $rs->Select('V_Article', $Where);
                $r_Article=mysqli_fetch_array($finds);
                if (($r_Article['Article'] == 80 and $r_Article['Paragraph'] == '14') || ($r_Article['Article'] == 193 and $r_Article['Paragraph'] == '2')) {

                    if (in_array($VehiclePlate, $a_VehiclePlate)) {
                        $chk_VehiclePlate = false;
                    } else {
                        $a_VehiclePlate[] = $VehiclePlate;
                    }

                    $RndCode = "";
                    for ($i = 0; $i < 3; $i++) {
                        $n = rand(1, 24);
                        $RndCode .= substr($strCode, $n, 1);
                    }

                    $Code = date("m") . "_" . $RndCode . "/" . date("Y");

                    $a_ArticleDate = explode(":", trim($row[9]));

                    if (!$chk_GlobalImage) {
                        $chk_GlobalImage = true;
                        $aDocViolation = glob($path . '*.jpg');

                    }

                    if ($b_AdditionalArticle) {
                        $a_ArticleAdditionalDate = explode(" ", trim($a_ArticleDate[1]));
                        $str_Date = $a_ArticleAdditionalDate[0];
                    } else
                        $str_Date = substr (trim($a_ArticleDate[1]),0,10);
                }

                $ArticleId = $r_Article['Id'];
                $Fee = $r_Article['Fee'];
                $MaxFee = $r_Article['MaxFee'];


                if ($r_Article['Article'] == 80 and $r_Article['Paragraph'] == '14') {

                    $d1 = new DateTime($FineDate);
                    $d2 = new DateTime($str_Date);

                    $diff = $d2->diff($d1);

                    if ($diff->y >= 4) {
                        $Fee = $Fee * 2;
                        $MaxFee = $MaxFee * 2;
                    };


                }

                $ViolationTypeId = $r_Article['ViolationTypeId'];
            }
            $rs_reason = getReasonRs($ReasonId, $_SESSION['cityid'], $r_Article['ViolationTypeId'], $DetectorCode);
            $r_Reason = mysqli_fetch_array($rs_reason);
            $ReasonId = $r_Reason['Id'];

            $rs_Locality = $rs->Select(MAIN_DB . '.City', "UPPER(Title)='" . trim($Locality) . "'");
            $r_Locality = mysqli_fetch_array($rs_Locality);
            $Locality = $r_Locality['Id'];


            if ($chk_VehiclePlate) {
                $rs_fine = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
                $FindNumber = mysqli_num_rows($rs_fine);


                if ($FindNumber == 0) {
                    $rs->Start_Transaction();

                    $a_Fine = array(
                        array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                        array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                        array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                        array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                        array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $Locality),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                        array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                        array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                        array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand),
                        array('field' => 'VehicleColor', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleColor),
                        array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                    );


                    $FineId = $rs->Insert('Fine', $a_Fine);
                    $n_ContFine++;


                    if ($AdditionalControllerCode > 0) {
                        $a_FineAdditionalController = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $AdditionalControllerCode, 'settype' => 'int'),
                        );

                        $rs->Insert('FineAdditionalController', $a_FineAdditionalController);
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
                        array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                    );

                    if ($str_Date != "") {
                        $a_FineArticle[] = array('field' => 'ExpirationDate', 'selector' => 'value', 'type' => 'date', 'value' => $str_Date);
                    }
                    if ($b_AdditionalArticle) {
                        $a_FineArticle[] = array('field' => 'ArticleNumber', 'selector' => 'value', 'type' => 'int', 'value' => 2, 'settype' => 'int');

                        $a_Article = explode("-", trim($a_AdditionalArticle[1]));
                        $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chkTolerance);
                        $find = getVArticle($detector['Id'], $_SESSION['cityid'], $SpeedExcess, $ProtocolYear);
                        if (($r_Article['Article'] == 80 and $r_Article['Paragraph'] == '14') || ($r_Article['Article'] == 193 and $r_Article['Paragraph'] == '2')) {
                            $str_Date = trim($a_ArticleDate[2]);

                        }

                        $ArticleId = $r_Article['Id'];
                        $Fee = $r_Article['Fee'];
                        $MaxFee = $r_Article['MaxFee'];


                        if ($r_Article['Article'] == 80 and $r_Article['Paragraph'] == '14') {

                            $d1 = new DateTime($FineDate);
                            $d2 = new DateTime($str_Date);

                            $diff = $d2->diff($d1);

                            if ($diff->y >= 4) {
                                $Fee = $Fee * 2;
                                $MaxFee = $MaxFee * 2;
                            };


                        }
                        $a_FineAdditionalArticle = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
                            array('field' => 'ExpirationDate', 'selector' => 'value', 'type' => 'date', 'value' => $str_Date),
                            array('field' => 'ArticleOrder', 'selector' => 'value', 'type' => 'int', 'value' => 2, 'settype' => 'int'),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                        );
                        $rs->Insert('FineAdditionalArticle', $a_FineAdditionalArticle);


                    }


                    $rs->Insert('FineArticle', $a_FineArticle);

                    if ($chk_GlobalImage) {


                        $matches = preg_grep('/' . strtoupper($VehiclePlate) . '/', $aDocViolation);


                        $keys = array_keys($matches);


                        foreach ($matches as $key => $value) {
                            if (strpos($value, $str_DateTime) !== false) {

                                if (file_exists($value)) {
                                    $a_Documentation = explode("/", $value);
                                    $DocumentName = $a_Documentation[count($a_Documentation) - 1];

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


                                    copy($value, $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);


                                    if (file_exists($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName)) {
                                        unlink($value);
                                    } else {
                                        echo "Poblemi con la creazione del documento: " . $DocumentName;
                                        die;
                                    }
                                }

                            }
                        }


                    } else if (isset($row[37]) and strlen(trim($row[37])) > 0) {
                        $str_Document = trim($row[37]);

                        if (file_exists($path . $str_Document)) {

                            $DocumentName = $str_Document;


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

                            if ($compress) {
                                $img = new Imagick($path . $DocumentName);
                                $width = intval($img->getimagewidth() / 2);
                                $height = intval($img->getimageheight() / 2);
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
                    $rs->End_Transaction();
                }
            }

        }
    }
    fclose($file);
}



if($n_ContFine>0){


    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

        $str_Content = $r_UserMail['CityTitle'].": sono stati elaborati n. ".$n_ContFine." preinserimenti";

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


unlink($path.$ImportFile);
header("location: ".$P);

