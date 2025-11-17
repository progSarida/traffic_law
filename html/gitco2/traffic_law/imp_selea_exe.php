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
$ControllerId = CheckValue('ControllerId1','n');
$AdditionalControllerId = CheckValue('ControllerId2','n');
ini_set('display_errors',"1");
$n_ContFine = 0;

$CityId = $_SESSION['cityid'];

$rs_Customer = $rs->Select('Customer', "CityId='" . $CityId . "'");
$n_Validation = mysqli_fetch_array($rs_Customer)['Validation'];

$StatusTypeId = ($n_Validation == 1) ? 0 : 1;

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





$VehicleBrand  	= $VehicleColor = "";
$VehicleCountry = "Italia";
$CountryId		= "Z000";
$VehicleTypeId 	= 1;



$row = fgets($file);
if(is_resource($file)) {
    $rs_DetectorSelea = $rs->Select('DetectorSelea', "CityId='". $_SESSION['cityid'] ."'");

    $a_DetectorAddress = $a_DetectorLocality = array();

    while($r_DetectorSelea = mysqli_fetch_array($rs_DetectorSelea)){
        $a_DetectorAddress[$r_DetectorSelea['Code']] = $r_DetectorSelea['Address'];
        $a_DetectorLocality[$r_DetectorSelea['Code']] = $r_DetectorSelea['Locality'];
    }


    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0]) && $row[0] != "CARPLATE") {

            $chk_VehiclePlate = true;


            $str_FixedWhere = "Fixed IS NULL";
            $chkFine = '';



            $strDetector = "";
            $ReasonId = null;


            $FineDate = $row[1];


            $VehiclePlate = strtoupper($row[0]);
            if (in_array($VehiclePlate, $a_VehiclePlate)) {
                $chk_VehiclePlate = false;
                $cont--;

            } else {
                $a_VehiclePlate[] = $VehiclePlate;
            }


            if (!$chk_GlobalImage) {
                $chk_GlobalImage = true;
                $aDocViolation = glob($path . '*.jpg');

            }
            if (strpos($FineDate, 'T') !== false) {


                $str_DateTime = str_replace("T", "_", $FineDate);
                $str_DateTime = str_replace(":", "-", $str_DateTime);
                $str_DateTime = str_replace(".", "-", $str_DateTime);

                $a_DateTime = explode("T", $FineDate);

                $FineDate = $a_DateTime[0];
                $FineTime = $a_DateTime[1];


            }
            $aFineDate = explode("-", $FineDate);

            $FineDate = $aFineDate[0] . "-" . $aFineDate[1] . "-" . $aFineDate[2];
            $ProtocolYear = $aFineDate[0];


            $aFineTime = explode(":", $FineTime);
            if (strlen($aFineTime[0]) < 2) $aFineTime[0] = "0" . $aFineTime[0];
            if (strlen($aFineTime[1]) < 2) $aFineTime[1] = "0" . $aFineTime[1];
            $FineTime = $aFineTime[0] . ":" . $aFineTime[1];



            $Note = "Caricamento da file SELEA";


            $str_Address = trim($row[4]);


            $Address = $a_DetectorAddress[$str_Address];
            $Locality = $a_DetectorLocality[$str_Address];


            $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;
            $b_AdditionalArticle = false;
            $str_Date = "";



            $SpeedLimit = 0.00;
            $SpeedControl = 0.00;
            $Speed = 0.00;


            $SpeedLimit 	= 0.00;
            $SpeedControl 	= 0.00;
            $Speed 			= 0.00;
            $DetectorId 	= 0;
            $DetectorCode 	= 0;


            $RndCode = "";
            for ($i = 0; $i < 3; $i++) {
                $n = rand(1, 24);
                $RndCode .= substr($strCode, $n, 1);
            }

            $Code = date("m") . "_" . $RndCode . "/" . date("Y");

            if (!$chk_GlobalImage) {
                $chk_GlobalImage = true;
                $aDocViolation = glob($path . '*.jpg');

            }








            if ($chk_VehiclePlate) {
                $rs_fine = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
                $FindNumber = mysqli_num_rows($rs_fine);


                if ($FindNumber == 0) {


                    $rs_FineAll = $rs->Select('V_FineAll', "VehiclePlate='" . $VehiclePlate . "' AND  ((Article=80 AND Paragraph=14 AND (Letter is null or Letter = '')) OR (Article=193 AND Paragraph =2 AND (Letter is null or Letter = '')))");
                    $FindNumber = mysqli_num_rows($rs_FineAll);

                    $chk_Imp = CheckValue('IMP_'. strtoupper($VehiclePlate), 'n');

                    if ($FindNumber==0 || $chk_Imp==1) {
                        $rs->Start_Transaction();

                        $a_Fine = array(
                            array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                            array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                            array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                            array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                            array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $Locality),
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




                        if($AdditionalControllerId>0){
                            $a_FineAdditionalController = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $AdditionalControllerId, 'settype' => 'int'),
                            );

                            $rs->Insert('FineAdditionalController', $a_FineAdditionalController);

                        }



                        $b_AdditionalArticle = false;

                        $str_Art80 	= "REVISIONE SCADUTA IL:";
                        $str_Art193 = "POLIZZA ASSICURATIVA SCADUTA IL:";

                        $str_ArticleContent = strtoupper(trim($row[5]));

                        $b_Art193 = $b_Art80 = false;



                        // 80-14    193-2


                        $a_FineArticle = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),


                            array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                            array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                            array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                            array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                        );

                        if (strpos($str_ArticleContent, $str_Art80) === false) {

                            $b_Art193 = true;

                        } else {

                            $b_Art80 = true;


                            $rs_Article = $rs->Select('V_Article', $Where . " AND Id1=80 AND Id2='14' AND (Id3 is null or Id3 = '')");
                            $FindNumber = mysqli_num_rows($rs_Article);


                            $r_Article = mysqli_fetch_array($rs_Article);

                            $ArticleId 		= $r_Article['Id'];
                            $Fee 			= $r_Article['Fee'];
                            $MaxFee 		= $r_Article['MaxFee'];

                            $ViolationTypeId = $r_Article['ViolationTypeId'];

                            $a_TMP_Date = explode($str_Art80, $str_ArticleContent);
                            $str_Date = substr(trim($a_TMP_Date[1]),0,10);

                            $d1 = new DateTime($FineDate);
                            $d2 = new DateTime($str_Date);

                            $diff = $d2->diff($d1);

                            if ($diff->y >= 4) {
                                $Fee = $Fee * 2;
                                $MaxFee = $MaxFee * 2;
                            };



                            $rs_reason = getReasonRs($ReasonId, $_SESSION['cityid'], $ViolationTypeId, $DetectorCode);
                            $r_Reason = mysqli_fetch_array($rs_reason);
                            $ReasonId = $r_Reason['Id'];

                            $a_FineArticle[] = array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int');
                            $a_FineArticle[] = array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int');
                            $a_FineArticle[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt');
                            $a_FineArticle[] = array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt');
                            $a_FineArticle[] = array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int');
                            if ($str_Date != "") {
                                $a_FineArticle[] = array('field' => 'ExpirationDate', 'selector' => 'value', 'type' => 'date', 'value' => $str_Date);
                            }

                            if (! strpos($str_ArticleContent, $str_Art193) === false){
                                $b_AdditionalArticle = true;
                                $a_FineArticle[] = array('field' => 'ArticleNumber', 'selector' => 'value', 'type' => 'int', 'value' => 2, 'settype' => 'int');
                            }

                        }

                        if($b_Art80){
                            $rs->Insert('FineArticle', $a_FineArticle);
                        }

                        if($b_Art193 || $b_AdditionalArticle){


                            $rs_Article = $rs->Select('V_Article', $Where . " AND Id1=193 AND Id2='2' AND (Id3 is null or Id3 = '')");
                            $r_Article = mysqli_fetch_array($rs_Article);

                            $ArticleId = $r_Article['Id'];
                            $Fee = $r_Article['Fee'];
                            $MaxFee = $r_Article['MaxFee'];

                            $ViolationTypeId = $r_Article['ViolationTypeId'];


                            $a_TMP_Date = explode($str_Art193, $str_ArticleContent);
                            $str_Date = substr(trim($a_TMP_Date[1]),0,10);
                            if($b_AdditionalArticle){
                                $a_FineAdditionalArticle = array(
                                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                    array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
                                    array('field' => 'ArticleOrder', 'selector' => 'value', 'type' => 'int', 'value' => 2, 'settype' => 'int'),
                                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                                );
                                if (! strpos($str_ArticleContent, $str_Art193) === false){
                                    $b_AdditionalArticle = true;
                                    $a_FineArticle[] = array('field' => 'ArticleNumber', 'selector' => 'value', 'type' => 'int', 'value' => 2, 'settype' => 'int');
                                }


                                $rs->Insert('FineAdditionalArticle', $a_FineAdditionalArticle);

                            } else {
                                $rs_reason = getReasonRs($ReasonId, $_SESSION['cityid'], $ViolationTypeId, $DetectorCode);
                                $r_Reason = mysqli_fetch_array($rs_reason);
                                $ReasonId = $r_Reason['Id'];

                                $a_FineArticle[] = array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int');
                                $a_FineArticle[] = array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int');
                                $a_FineArticle[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt');
                                $a_FineArticle[] = array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt');
                                $a_FineArticle[] = array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int');
                                if ($str_Date != "") {
                                    $a_FineArticle[] = array('field' => 'ExpirationDate', 'selector' => 'value', 'type' => 'date', 'value' => $str_Date);
                                }

                                $rs->Insert('FineArticle', $a_FineArticle);

                            }


                        }



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
                                        $DocumentationTypeId = 1;
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


                        }

                        $rs->End_Transaction();
                    }
                }
            }
        }
    }
    fclose($file);
}



if($n_ContFine>0){

    //aggiunto per salvare i dati del file importato come fa Passano su Titan
    $a_Import = array(
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>2),
        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$ImportFile),
        array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_ContFine),
    );
    $rs->Start_Transaction();
    $importedFilesId = $rs->Insert('ImportedFiles',$a_Import);
    $rs->End_Transaction();

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

