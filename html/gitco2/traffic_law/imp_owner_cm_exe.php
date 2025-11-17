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


$a_VehiclePlate = array();


$file = fopen($path.$ImportFile,  "r");
$delimiter = detectDelimiter($path . $ImportFile);




$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
	$a_Street_Type[$streettype['Title']] = $streettype['Id'];
}


$rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_chk_controller = array();
while ($r_Controller = mysqli_fetch_array($rs_Controller)){
    $a_chk_controller[$r_Controller['Code']] = $r_Controller['Id'];
}


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






$aDocViolation = glob($path . '*.jpg');



if(is_resource($file)) {
    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);

        if (isset($row[0])) {

            $chk_VehiclePlate = true;

            $DetectorId = 0;
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
            if ($str_VehicleTypeId == "C") $str_VehicleTypeId = "MOTOVEICOLO";
            if ($str_VehicleTypeId == "M") $str_VehicleTypeId = "MOTOVEICOLO";
            if ($str_VehicleTypeId == "ARTICOLATO") $str_VehicleTypeId = "AUTOARTICOLATO";
            if ($str_VehicleTypeId == "CISTERNA") $str_VehicleTypeId = "AUTOARTICOLATO";
            if ($str_VehicleTypeId == "MOTOCICLO") $str_VehicleTypeId = "MOTOVEICOLO";


            $VehicleTypeId = $a_chk_VehicleType[$str_VehicleTypeId];
            $VehicleBrand = $row[6];
            $VehicleColor = $row[7];


            $ControllerCode = $row[11];
            $AdditionalControllerCode = 0;


            $Locality = strtoupper($row[13]);
            $Address = trim($row[14]);
            if (trim($row[15]) != "") $Address .= " " . trim($row[15]);

            if (trim($row[16]) != "") $Address .= " Direzione " . trim($row[16]);


            $CountryId = "ZZZZ";
            $VehicleCountry = "Da assegnare";


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

            $Note = "Caricamento da file";


            $ControllerId = $a_chk_controller[$ControllerCode];


            $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;
            $b_AdditionalArticle = false;
            $str_Date = "";
            if (strlen(trim($SpeedLimit)) > 0) {
                $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code=" . $DetectorCode);
                $detector = mysqli_fetch_array($detectors);

                $DetectorId = $detector['Id'];
                $chkTolerance = $detector['Tolerance'];
                $str_FixedWhere = 'Fixed=' . $detector['Fixed'];

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

                $ArticleId = $find['Id'];
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
                    $ReasonId = $detector['ReasonId'];
                    $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
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
                if (isset($a_Article[2])) $Where .= "AND Id3='" . trim($a_Article[2]) . "'";


                $rs_Article = $rs->Select('V_Article', $Where);
                $r_Article = mysqli_fetch_array($rs_Article);


                $ArticleId = $r_Article['Id'];
                $Fee = $r_Article['Fee'];
                $MaxFee = $r_Article['MaxFee'];


                $ViolationTypeId = $r_Article['ViolationTypeId'];
            }


            //////////////////////////
            //
            //
            //              ReasonId
            //
            //
            //////////////////////////
            $str_Where = $str_FixedWhere . " AND ReasonTypeId=1 AND CityId='" . $_SESSION['cityid'] . "'";
            switch ($ViolationTypeId) {
                case 4:
                case 6:
                    $str_Where .= ($DetectorCode == 0) ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" . $ViolationTypeId;
                    break;

                default:
                    $str_Where .= " AND ViolationTypeId=" . $ViolationTypeId;

            }

            $rs_Reason = $rs->Select('Reason', $str_Where);
            $r_Reason = mysqli_fetch_array($rs_Reason);

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


                    $rs->Insert('FineArticle', $a_FineArticle);


                    $str_Document = trim($row[37]);
                    $matches = preg_grep('/' . $str_Document . '/', $aDocViolation);


                    $keys = array_keys($matches);


                    foreach ($matches as $key => $value) {
                        if (strpos($value, $str_Document) !== false) {

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

