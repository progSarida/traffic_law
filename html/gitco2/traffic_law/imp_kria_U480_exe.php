<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
ini_set('max_execution_time', 5000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$n_ContFine = 0;

$CityId = $_SESSION['cityid'];


$UserId = "'".$_SESSION['username']."'";

$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;


$file = fopen($path.$ImportFile,  "r");
$delimiter = ";";


$a_VehicleTypeId = array(
	"autoveicolo"=>1,
	"motoveicolo"=>2,
	"motociclo"=>9,
	"non_definito"=>6,
	"autobus"=>8,
	"autocarro"=>4,
	"autoarticolato"=>12,
);


$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
	$a_Street_Type[$streettype['Title']] = $streettype['Id'];
}




$controllers = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_ControllerName = $a_ControllerCode = array();
while ($controller = mysqli_fetch_array($controllers)){
	$a_ControllerName[$controller['Name']] = $controller['Id'] ;
    $a_ControllerCode[$controller['Code']] = $controller['Id'] ;
}


$countries = $rs->Select('Country', "KriaCode!=''");
$a_country_id = array();
$a_country_title = array();
while ($country = mysqli_fetch_array($countries)){
	$a_country_id[$country['KriaCode']]=$country['Id'];
	$a_country_title[$country['KriaCode']]=$country['Title'];
}




if(is_resource($file)) {
    $fileEncoding=null;
    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);
        if($fileEncoding==null)
            $fileEncoding=mb_detect_encoding($row[0]);
        if (isset($row[0]) && $row[0] != 'Targa') {
            $rs->Start_Transaction();
            $DetectorCode = $row[11];
            $rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode."'");
            $n_Record = mysqli_num_rows($rs_Detector);

            $r_Detector = mysqli_fetch_array($rs_Detector);
            $strDetector = $r_Detector['Kind'];
            $VehiclePlate=mb_convert_encoding($row[0],"UTF-8",$fileEncoding);
            $VehiclePlate = str_replace("<?>", "*", strtoupper($VehiclePlate));
            $FineTime = $row[2];
            $FineDate = $row[1];
            $VehicleTypeId = $a_VehicleTypeId[strtolower($row[7])];

            $aFineDate = explode("/", $FineDate);
            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
            $ProtocolYear = $aFineDate[2];
            $SpeedControl = $row[5];
            $str_Address = $row[3];
            $DocumentationTypeId = 1;
            $StatusTypeId = 1;
            $VehicleMass = CheckValue('VehicleMass', 'n');
            $DepartmentId = CheckValue('DepartmentId', 'n');
            $DetectorId = $r_Detector['Id'];
            $Documentation = $row[9];

            if ($r_Detector['DetectorTypeId'] == 1) {
                $ReasonId = 1;
                $str_Locality = $_SESSION['cityid'];
                $TimeTLightFirst = 0;


                $a_Address = explode(" ", $str_Address);
                $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


                $SpeedLimit = $row[6];
                $SpeedControl = $row[5];

                if ($DetectorCode == 2085) {
                    $SpeedLimit = ($VehicleTypeId == 2 || $VehicleTypeId == 9) ? 60 : 80;
                }


                $aCode = explode("_", $Documentation);
                $Code = intval(substr($aCode[0], 6)) . "/" . $ProtocolYear;


                $chkTolerance = $r_Detector['Tolerance'];

                $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

                $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


                $chkTolerance = $r_Detector['Tolerance'];
                $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

                $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


                $Speed = $SpeedControl - $Tolerance;
                $SpeedExcess = $Speed - $SpeedLimit;


                $ControllerId = $a_ControllerName[ltrim($row[13], '0')];


                //$rs_Exception = $rs->Select('Detector', "CityId='".$_SESSION['cityid']."' AND Code=".$DetectorCode);


                $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


                if ($SpeedExcess <= 10) {
                    $Where .= " AND Article=142 AND Paragraph=7";
                } elseif ($SpeedExcess <= 40) {
                    $Where .= " AND Article=142 AND Paragraph=8";
                } elseif ($SpeedExcess <= 60) {
                    $Where .= " AND Article=142 AND Paragraph=9 AND Letter!='bis'";
                } else {
                    $Where .= " AND Article=142 AND Paragraph=9 AND Letter='bis'";
                }


            } else {
                $Code = $row[14];
                $ControllerId = $a_ControllerCode[ltrim($row[13], '0')];

                $chk_Tolerance = 0.00;
                $SpeedLimit = 0.00;
                $Speed = 0.00;

                $TimeTLightFirst = 1;

                $Where = "Article=146 AND Paragraph=3 AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


                $a_Locality = explode("(", $str_Address);
                $str_Locality = $a_Locality[0];

                $str_Address = trim(substr($str_Address, strpos($str_Address, ')') + 1));

                $a_Address = explode(" ", $str_Address);
                $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


                $rs_Locality = $rs->Select(MAIN_DB . '.City', "Title='" . $str_Locality . "'");


                $r_Locality = mysqli_fetch_array($rs_Locality);
                $str_Locality = $r_Locality['Id'];


            }

            $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='" . $_SESSION['cityid'] . "' AND VehiclePlate='" . $VehiclePlate . "'");
            if (mysqli_num_rows($rs_VehicleWhiteList) > 0) {
                $StatusTypeId = 90;
                $Note = 'White list ente';
            }


            $finds = $rs->Select('V_Article', $Where);


            $find = mysqli_fetch_array($finds);

            $ArticleId = $find['Id'];


            $ViolationTypeId = $find['ViolationTypeId'];
            $AdditionalNight = $find['AdditionalNight'];


            $rs_Reasons = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=" . $ViolationTypeId . " AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "'");
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


            $Country = $row[8];
            if ($Country == "I") $Country = "IT";


            $VehicleCountry = $a_country_title[$Country];
            $CountryId = $a_country_id[$Country];


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

                if ($_SESSION['cityid'] != 'U480') {
                    $a_Fine[] = array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int');
                }

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
                    array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                    array('field' => 'TimeTLightFirst', 'selector' => 'value', 'type' => 'int', 'value' => $TimeTLightFirst, 'settype' => 'int'),


                );

                $rs->Insert('FineArticle', $a_FineArticle);


                if ($r_Detector['DetectorTypeId'] == 1) {

                } else {

                }


                if (file_exists($path . $Documentation)) {

                    $DocumentName = $Documentation;


                    $str_Folder = ($a_country_id[$Country] == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


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
                        $img = new Imagick($path . $Documentation);
                        $width = intval($img->getimagewidth() / 3);
                        $height = intval($img->getimageheight() / 3);
                        $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                        $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                        $img->setImageCompressionQuality(40);
                        $img->stripImage();
                        $img->writeImage($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);
                        $img->destroy();

                    } else {
                        copy($path . $Documentation, $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);
                    }


                    if (file_exists($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName)) {
                        unlink($path . $Documentation);
                    } else {
                        echo "Poblemi con la creazione del documento: " . $DocumentName;
                        die;
                    }

                }


                if ($r_Detector['DetectorTypeId'] == 2) {
                    $Documentation = $row[18];

                    $DocumentName = $Documentation;


                    $str_Folder = ($a_country_id[$Country] == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;

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