<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/function_import.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 5000);
CONST VEHICLE_TYPE_ID = array( "C" => 1, "T" => 2,);

$P = CheckValue('P', 's');
$n_Compress = CheckValue('compress', 'n');
$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$a_XmlFile = array();
$CityId = $_SESSION['cityid'];
$VehicleCountry = "Italia";
$CountryId = "Z000";
$path = PUBLIC_FOLDER . "/_VIOLATION_/" . $_SESSION['cityid'] . "/";
$chk_Tolerance = 0;

if ($directory_handle = opendir($path)) {

    $rs_streettype = $rs->Select('StreetType', "Disabled=0");
    $a_Street_Type = array();
    while ($r_streettype = mysqli_fetch_array($rs_streettype)) {
        $a_Street_Type[$r_streettype['Title']] = $r_streettype['Id'];
    }

    $rs_controller = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
    $a_Controller = controllersByCodeArray($rs_controller);
    mysqli_data_seek($rs_controller,0);

    $a_ControllerByName = controllersByFieldArray($rs_controller,'Name');

    $rs_detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "'");
    $a_chk_detector = array();
    $a_Tolerance = array();
    $a_Detector = array();
    $a_Fixed = array();

    $cont = 0;


    while ($r_detector = mysqli_fetch_array($rs_detector)) {
        $a_chk_detector[$r_detector['Code']] = $r_detector['TitleIta'];
        $a_Tolerance[$r_detector['Code']] = $r_detector['Tolerance'];
        $a_Detector[$r_detector['Code']] = $r_detector['Id'];
        $a_Fixed[$r_detector['Code']] = $r_detector['Fixed'];
        $a_ReasonId[$r_detector['Code']] = $r_detector['ReasonId'];
        $a_StatusType[$r_detector['Code']]=getStatusTypeId($r_detector['Id']);
    }


    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "xml") {
            $cont++;

            $obj_Dom = new DOMDocument();
            $obj_Dom->load($path . $file);

            $a_XmlFile[] = $file;
            $chk_Speed = true;
            $a_Fine = array();

            showDOMNode($obj_Dom);

            $RndCode = "";
            for ($i = 0; $i < 5; $i++) {
                $n = rand(1, 24);
                $RndCode .= substr($strCode, $n, 1);
            }

            $Code = date("m")."_".$RndCode."/".date("Y");

            $DocumentationTypeId = 1;
            $ControllerCode = $a_Fine['Operator1'];
            $RoadSideDistance = $a_Fine['RoadSideDistance'];
            $DetectorCode = $a_Fine['InstrumentId'];
            $SpeedLimit = $a_Fine['RoadSpeedLimit'];
            $SpeedControl = $a_Fine['Speed'];
            $FineTime = $a_Fine['ShotTimeVB'];
            $FineDate = $a_Fine['ShotDate'];
            $Locality = $a_Fine['Location'];


            $ControllerId = getControllerByCode($a_Controller, $FineDate, $ControllerCode);
            if($ControllerId==null)
                $ControllerId = getControllerByField($a_ControllerByName, $FineDate, $ControllerCode);
            $Address = $a_Fine['Rsv2'];
            $a_TmpAddress = explode("DirectionDescription:", $Address);
            $a_Address = explode("|", $a_TmpAddress[1]);
            $Address = $a_Address[0];
            $StreetTypeId = (array_key_exists(strtoupper($Address), $a_Street_Type)) ? $a_Street_Type[strtoupper($Address)] : 0;
            $VehiclePlate = "";
            $VehicleTypeId = VEHICLE_TYPE_ID[$a_Fine['VehicleType']];
            $Note = "Caricamento da file xml";
            $ProtocolYear = substr($FineDate, 0, 4);
            $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


            if ($DetectorCode != "") {
                $DetectorId = $a_Detector[$DetectorCode];

                $str_FixedWhere = "Fixed=" . $a_Fixed[$DetectorCode];
                $ReasonId=$a_ReasonId[$DetectorCode];
                if ($SpeedLimit > 0) {
                    $chk_Tolerance = $a_Tolerance[$DetectorCode];
                    $SpeedExcess=getSpeedExcess($SpeedControl,$SpeedLimit,$chk_Tolerance);
                    if($SpeedExcess<=0) $chk_Speed = false;
                    $find =getVArticle($DetectorId,$_SESSION['cityid'],$SpeedExcess,$ProtocolYear);
                    $ArticleId = $find['Id'];
                    $Fee = $find['Fee'];
                    $MaxFee = $find['MaxFee'];
                    $ViolationTypeId = $find['ViolationTypeId'];
                    $aTime = explode(":", $FineTime);
                    if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                        $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                        $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
                    }
                }
                $Documentation = $aFile[0] . ".jpg";
                $fines = $rs->ExecuteQuery("select f.* from Fine f join FineDocumentation fd on fd.FineId=f.Id where fd.Documentation='$Documentation' and f.CityId='" . $_SESSION['cityid'] . "' AND f.FineDate='$FineDate' ");
                $FindNumber = mysqli_num_rows($fines);
                if($FindNumber>0)
                    continue;

                if ($chk_Speed) {
                    $rs->Start_Transaction();
                    $a_Fine = array(
                        array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'settype' => 'int', 'value' => $a_StatusType[$DetectorCode]),
                        array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                        array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                        array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                        array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'StreetTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StreetTypeId, 'settype' => 'int'),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Locality),
                        array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                        array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                        array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                    );
                    $FineId = $rs->     Insert('Fine', $a_Fine);
                    $n_ContFine++;

                    if ($FineId == 0) {
                        echo "Poblemi con l'inserimento del verbale con targa: " . $VehiclePlate;
                        DIE;
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
                        array('field' => 'RoadSideDistance', 'selector' => 'value', 'type' => 'int', 'value' => $RoadSideDistance, 'settype' => 'int'),


                    );


                    $rs->Insert('FineArticle', $a_FineArticle);





                    if ($Documentation != "" && file_exists($path . $Documentation)) {

                        $DocumentName = $Documentation;

                        $str_Folder = NATIONAL_VIOLATION;

                        if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
                            mkdir($str_Folder . "/" . $_SESSION['cityid']   , 0777);
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


                        if ($n_Compress && $str_DocumentType != "PDF") {
                            $img = new Imagick($path . $Documentation);
                            $width = intval($img->getimagewidth() / 2);
                            $height = intval($img->getimageheight() / 2);
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
                            DIE;
                        }

                    }


                    $rs->End_Transaction();
                } else {
                    if (file_exists($path . $Documentation)) {
                        unlink($path . $Documentation);
                    };
                }

            }
        }
    }

    closedir($directory_handle);

}
for($i=0; $i<count($a_XmlFile); $i++){
    unlink($path . $a_XmlFile[$i]);
}



    if ($n_ContFine > 0) {


        $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM " . MAIN_DB . ".V_UserCity WHERE UserLevel>=3 AND CityId='" . $CityId . "'");
        while ($r_UserMail = mysqli_fetch_array($rs_UserMail)) {

            $str_Content = $r_UserMail['CityTitle'] . ": sono state elaborate n. " . $n_ContFine . " violazioni.";

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


    header("location: " . $P);