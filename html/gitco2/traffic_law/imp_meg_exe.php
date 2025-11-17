<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");


ini_set('max_execution_time', 7000);

$FileList = "";
$n_ContFine = 0;

$CityId         = $_SESSION['cityid'];
$message=new CLS_MESSAGE();
if($CityId==null){
    $message->addError("Sessione vuota, impossibile procedere conl' importazione");
    echo $message->getMessagesString();
}

$path           = PUBLIC_FOLDER . "/" . $CityId . "/";
$ImportFile     = CheckValue('ImportFile', 's');


$P              = CheckValue('P','s');

$ErrorMessage   ="";


$Note = "Caricamento da file MEGASP";
$DocumentationTypeId    = 1;
$VehicleTypeId          = 1;



$countries = $rs->Select('Country', "MegaspCountry!=''");
$a_Country = array();
$a_VehicleCountry = array();
while ($country = mysqli_fetch_array($countries)) {
    $a_Country[$country['MegaspCountry']] = $country['Id'];
    $a_VehicleCountry[$country['MegaspCountry']] = $country['Title'];


}
$rs_Controller = $rs->Select('Controller', "CityId='".$CityId."'");
$a_chk_controller = array();
while ($r_Controller = mysqli_fetch_array($rs_Controller)){
    $a_chk_controller[$r_Controller['Code']] = $r_Controller['Id'];
}

$a_VehicleTypeId = array(
    "AUTOVEICOLO" => 1,
    "AUTOVETTURA" => 1,
    "MOTOVEICOLO" => 2,
    "AUTOVETTURA PUBBLICA" => 1,
    "AUTOCARRO" => 4,
    "TRATTORE STRADALE" => 10,
    "CAMPER" => 5,
    "AUTOCARAVAN" => 5,
    "RIMORCHIO" => 7,
    "AUTOBUS" => 8,
    "AUTOBUS EXTRAURBANA"=>8,
    "AUTOTRENO CON RIMORCHIO"=>12,
    "MOTOCICLO" => 2,
    "CICLOMOTORE"=>9,
    "AUTOSNODATO O AUTOARTICOL" => 12,
);






$file = fopen($path . $ImportFile, "r");
$delimiter = detectDelimiter($path . $ImportFile);




if(is_resource($file)) {
    while (!feof($file)) {

        $rs->Start_Transaction();

        $row = fgetcsv($file, 1000, $delimiter);

        if (isset($row[0]) && trim($row[0]) != "Ida") {


            $DetectorId = 0;
            $str_FixedWhere = "Fixed IS NULL";

            $chk_Folder = trim($row[0]);
            $Code = trim($row[3]) . "/" . trim($row[2]) . "/" . trim($row[1]);
            $ProtocolId = trim($row[3]);
            $VehiclePlate = trim($row[11]);
            $VehiclePlate = str_replace("-", "", $VehiclePlate);
            $VehiclePlate = str_replace(",", "", $VehiclePlate);


            $FineDate = trim($row[4]);

            $FineTime = trim($row[5]);

            $ControllerCode = trim($row[20]);
            $a_ControllerCode = explode("-", $ControllerCode);
            $ControllerCode = $a_ControllerCode[0];

            $DetectorCode = trim($row[14]);

            $aFineDate = explode("/", $FineDate);
            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];

            $ProtocolYear = $aFineDate[2];

            $Where = "Disabled=0 AND CityId='" . $CityId . "' AND Year=" . $ProtocolYear;


            if ($DetectorCode != "") {

                $SpeedLimit = trim($row[8]);
                $SpeedControl = trim($row[9]);
                $Speed = trim($row[10]);


                $detectors = $rs->Select('Detector', "CityId='" . $CityId . "' AND Code='" . $DetectorCode . "'");
                $detector = mysqli_fetch_array($detectors);

                $DetectorId = $detector['Id'];
                $str_FixedWhere = 'Fixed=' . $detector['Fixed'];

                $chk_Tolerance = $detector['Tolerance'];
                $chk_Tolerance = ($chk_Tolerance > FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;

                $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                $Tolerance = ($TolerancePerc < $chk_Tolerance) ? $chk_Tolerance : $TolerancePerc;


                $Speed = $SpeedControl - $Tolerance;
                $SpeedExcess = $Speed - $SpeedLimit;
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

                $DetectorCode = 0;
                $SpeedLimit = 0.00;
                $SpeedControl = 0.00;
                $Speed = 0.00;

                $Where .= " AND Article=126 AND Paragraph='0' AND Letter='bis'";


            }


            $NameSurname = trim($row[29]) . " " . trim($row[30]);
            $Address = trim($row[35]);
            $ZIPCity = trim($row[34]);
            $Country = trim($row[33]);
            $CountryCode = trim($row[12]);

            $VehicleModel = trim($row[13]);


            $articles = $rs->Select('V_Article', $Where);
            $article = mysqli_fetch_array($articles);
            $ArticleId = $article['Id'];
            $ViolationTypeId = $article['ViolationTypeId'];

            $Fee = $article['Fee'];
            $MaxFee = $article['MaxFee'];

            $aTime = explode(":", $FineTime);

            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

            }


            $str_WhereReason = $str_FixedWhere . " AND ReasonTypeId=1 AND CityId='" . $CityId . "'";
            switch ($ViolationTypeId) {
                case 4:
                case 6:
                    $str_WhereReason .= ($DetectorCode == 0) ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" . $ViolationTypeId;
                    break;

                default:
                    $str_WhereReason .= " AND ViolationTypeId=" . $ViolationTypeId;
            }

            $rs_Reason = $rs->Select('Reason', $str_WhereReason);
            $r_Reason = mysqli_fetch_array($rs_Reason);

            $ReasonId = $r_Reason['Id'];


            $ControllerCode = trim($row[20]);
            $a_ControllerCode = explode("-", $ControllerCode);
            $ControllerCode = $a_ControllerCode[0];
            $ControllerId = $a_chk_controller[$ControllerCode];

            $CityAddress = trim($row[7]);


            if (isset($row[57]) && trim($row[57]) != '0') {
                $PaymentAmount = number_format(trim($row[57]), 2, '.', '');
                $PaymentDate = trim($row[58]);

                $StatusTypeId = 28;
            } else {
                $PaymentAmount = '';
                $PaymentDate = '';

                $StatusTypeId = 27;
            }


            $CountryId = $a_Country[$CountryCode];
            $VehicleCountry = $a_VehicleCountry[$CountryCode];

            $TrespasserTypeId = 1;
            if ($CountryId == "Z000") {
                $CountryId = "Z00Z";
                $VehicleCountry = "Italia Noleggi";
                $TrespasserTypeId = 11;
            }


            $fines = $rs->Select('Fine', "CityId='" . $CityId . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . mb_convert_encoding($VehiclePlate, 'UTF-8', "pass") . "'");
            $FindNumber = mysqli_num_rows($fines);

            if ($FindNumber == 0) {
                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                    array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolId, 'settype' => 'int'),
                    array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                    array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                    array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                    array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $CityAddress),
                    array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                    array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => mb_convert_encoding($VehiclePlate, 'UTF-8', "pass")),
                    array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel),
                    array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
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
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
                    array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                    array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                    array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                    array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                    array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                );
                $rs->Insert('FineArticle', $a_FineArticle);

                $DataSourceId = 10;


                $TrespasserCountryId = $a_Country[$CountryCode];
                $countries = $rs->Select('Country', "Id='" . $TrespasserCountryId . "'");
                $country = mysqli_fetch_array($countries);


                $ZoneId = $country['ZoneId'];
                $LanguageId = $country['LanguageId'];
                if ($TrespasserCountryId == "Z133" || $LanguageId == 0 || $LanguageId == "") $LanguageId = 1;
                $a_Name = explode(" ", $NameSurname);


                $trespassers = $rs->Select('Trespasser', "Surname='" . addslashes($a_Name[0]) . "' AND Name='" . addslashes($a_Name[1]) . "' AND CountryId='" . $TrespasserCountryId . "' AND Address='" . addslashes($Address) . "'");
                if (mysqli_num_rows($trespassers) == 0) {
                    $a_Trespasser = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),

                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => "M"),
                        array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => $a_Name[0]),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $a_Name[1]),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIPCity),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $Country),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),

                    );
                    $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);
                } else {
                    $trespasser = mysqli_fetch_array($trespassers);
                    $TrespasserId = $trespasser['Id'];

                }


                $a_FineTrespasser = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                    array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),

                );
                $rs->Insert('FineTrespasser', $a_FineTrespasser);


                $SendDate = trim($row[26]);
                $NotificationDate = DateInDB(trim($row[27]));

                if ($SendDate == "") {
                    $SendDate = date('Y-m-d', strtotime('-15 days', strtotime($NotificationDate)));
                }


                $CustomerFee = 0.00;
                $ResearchFee = number_format(trim($row[55]), 2, '.', '');
                $NotificationFee = number_format(trim($row[56]), 2, '.', '');


                $NotificationTypeId = 2;


                $aInsert = array(
                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                    array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerFee, 'settype' => 'flt'),
                    array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                    array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                    array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                );
                $rs->Insert('FineHistory', $aInsert);


                $NotificationTypeId = 6;
                $ResultId = 1;
                $aInsert = array(
                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                    array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerFee, 'settype' => 'flt'),
                    array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                    array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                    array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                    array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int'),

                );
                $rs->Insert('FineHistory', $aInsert);


                $a_FineNotification = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int'),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                );

                $rs->Insert('FineNotification', $a_FineNotification);


                if ($PaymentAmount > 0) {


                    $a_Payment = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $NameSurname),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                        array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                        array('field' => 'PaymentFee', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentAmount, 'settype' => 'int'),
                        array('field' => 'Amount', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentAmount, 'settype' => 'int'),
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                    );


                    $n_FinePayment = $rs->Insert('FinePayment', $a_Payment);


                }


                if (file_exists($path . $chk_Folder)) {
                    $str_Folder = ($CountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


                    $files = array_diff(scandir($path . $chk_Folder), array('.', '..'));


                    foreach ($files as $key => $value) {

                        $DocumentName = $value;

                        if (!is_dir($str_Folder . "/" . $CityId . "/" . $FineId)) {
                            mkdir($str_Folder . "/" . $CityId . "/" . $FineId, 0777);
                        }

                        $a_FineDocumentation = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                        );
                        $rs->Insert('FineDocumentation', $a_FineDocumentation);


                        copy($path . $chk_Folder . '/' . $DocumentName, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);


                        if (file_exists($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
                            unlink($path . $chk_Folder . '/' . $DocumentName);
                        } else {
                            $ErrorMessage .= $chk_Folder . ":Poblemi con la creazione del documento: " . $DocumentName . " FineId:" . $FineId . "<br />";
                        }

                    }


                    rmdir($path . $chk_Folder);


                }

            }


        }
        $rs->End_Transaction();
        if ($ErrorMessage != "") {
            echo $ErrorMessage;
            die;

        }

    }
    fclose($file);
}



unlink($path.$ImportFile);



if($n_ContFine>0){


    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

        $str_Content = $r_UserMail['CityTitle'].": sono state importate n. ".$n_ContFine." violazioni.";

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





header("location: " . $P);