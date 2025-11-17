<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");


ini_set('max_execution_time', 7000);

$FileList = "";
$n_ContFine = 0;

$CityId = $_SESSION['cityid'];
$message=new CLS_MESSAGE();
if($CityId==null){
    $message->addError("Sessione vuota, impossibile procedere conl' importazione");
    echo $message->getMessagesString();
}

$path = PUBLIC_FOLDER . "/" . $CityId . "/";
$ImportFile = CheckValue('ImportFile', 's');

$n_AIRE = CheckValue('AIRE', 'n');


$P = CheckValue('P','s');
$chkTolerance = 0;

$ErrorMessage ="";


$Note = "Caricamento da file MEGASP";
$DocumentationTypeId = 1;


$countries = $rs->Select('Country', "MegaspCode!=''");
$a_chk_country = array();
while ($country = mysqli_fetch_array($countries)) {
    $a_trespasser_country[$country['MegaspCode']] = $country['Id'];

}


$countries = $rs->Select('Country', "MegaspCountry!=''");
$a_chk_country = array();
$a_country = array();
$a_vehiclecountry = array();
while ($country = mysqli_fetch_array($countries)) {
    $a_country[$country['MegaspCountry']] = $country['Id'];
    $a_vehiclecountry[$country['MegaspCountry']] = $country['Title'];


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


$controllers = $rs->Select('Controller', "CityId='" . $CityId . "'");
$a_chk_controller = array();
while ($controller = mysqli_fetch_array($controllers)) {
    $a_chk_controller[] = $controller['MegaspCode'];
    $a_controllername[$controller['MegaspCode']] = $controller['Name'];
    $a_controllerid[$controller['MegaspCode']] = $controller['Id'];

}


$CityAddresses = $rs->Select('CityAddress', "CityId='" . $CityId . "'");
$a_chk_address = array();
while ($CityAddress = mysqli_fetch_array($CityAddresses)) {
    $a_chk_address[] = $CityAddress['MegaspCode'];
    $a_addressid[$CityAddress['MegaspCode']] = $CityAddress['StreetType'] . " " . $CityAddress['StreetTitle'];

}


$file = fopen($path . $ImportFile, "r");

$a_File = explode("_", $ImportFile);
$f_AddFile = fopen($path . $a_File[0], "r");

$delimiter = ";";



$row = fgets($file);
$row = fgets($f_AddFile);


if(is_resource($file)) {
    while (!feof($file)) {

        $rs->Start_Transaction();

        $row = fgets($file);
        $row_add = fgets($f_AddFile);

        $a_Row = explode($delimiter, $row);
        if (isset($a_Row[1])) {
            $DetectorId = 0;
            $str_FixedWhere = "Fixed IS NULL";

            $chk_Folder = trim($a_Row[0]);
            $Code = trim($a_Row[5]);
            $VehiclePlate = trim($a_Row[3]);
            $FineDate = trim($a_Row[6]);
            $FineTime = $a_Row[7];
            $Id1Megasp = trim($a_Row[8]);
            $Id2Megasp = trim($a_Row[9]);
            $AddressId = trim($a_Row[10]);
            $ReceiveDate = trim($a_Row[17]);

            $DetectorCode = trim($a_Row[20]);
            if ($DetectorCode != "") {
                $detectors = $rs->Select('Detector', "CityId='" . $CityId . "' AND IdMegasp=" . $DetectorCode);
                $detector = mysqli_fetch_array($detectors);
                $DetectorId = $detector['Id'];
                $str_FixedWhere = 'Fixed=' . $detector['Fixed'];

            } else {

                $DetectorCode = 0;

            }


            if (strlen($ReceiveDate) != 10) $ReceiveDate = date("d/m/Y");

            if ($Id1Megasp == 7 and $Id2Megasp == 383) {
                $Id1Megasp = 7;
                $Id2Megasp = 100;
            }
            if ($Id1Megasp == 7 and $Id2Megasp == 276) {
                $Id1Megasp = 7;
                $Id2Megasp = 160;
            }
            if ($Id1Megasp == 7 and $Id2Megasp == 273) {
                $Id1Megasp = 7;
                $Id2Megasp = 160;
            }
            if ($Id1Megasp == 7 and $Id2Megasp == 80) {
                $Id1Megasp = 7;
                $Id2Megasp = 33;
            }
            if ($Id1Megasp == 7 and $Id2Megasp == 360) {
                $Id1Megasp = 7;
                $Id2Megasp = 78;
            }


            if ($Id1Megasp == 146 and $Id2Megasp == 60) {
                $Id1Megasp = 146;
                $Id2Megasp = 66;
            }


            if ($Id1Megasp == 157 and $Id2Megasp == 303) {
                $Id1Megasp = 157;
                $Id2Megasp = 306;
            }
            if ($Id1Megasp == 157 and $Id2Megasp == 146) {
                $Id1Megasp = 157;
                $Id2Megasp = 306;
            }


            if ($Id1Megasp == 158 and $Id2Megasp == 200) {
                $Id1Megasp = 158;
                $Id2Megasp = 8;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 5) {
                $Id1Megasp = 158;
                $Id2Megasp = 4;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 6) {
                $Id1Megasp = 158;
                $Id2Megasp = 174;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 135) {
                $Id1Megasp = 158;
                $Id2Megasp = 406;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 281) {
                $Id1Megasp = 158;
                $Id2Megasp = 465;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 336) {
                $Id1Megasp = 158;
                $Id2Megasp = 133;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 314) {
                $Id1Megasp = 158;
                $Id2Megasp = 292;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 145) {
                $Id1Megasp = 158;
                $Id2Megasp = 174;
            }
            if ($Id1Megasp == 158 and $Id2Megasp == 34) {
                $Id1Megasp = 158;
                $Id2Megasp = 4;
            }

            if ($Id1Megasp == 158 and $Id2Megasp == 270) {
                $Id1Megasp = 158;
                $Id2Megasp = 3;
            }

            $VehiclePlate = str_replace("-", "", $VehiclePlate);
            $VehiclePlate = str_replace(",", "", $VehiclePlate);


            $NameSurname = trim(substr($row_add, 83, 44));
            $Address = trim(substr($row_add, 128, 45));
            $ZIPCity = str_replace("00000", "", trim(substr($row_add, 173, 9)));
            $Country = trim(substr($row_add, 183, 40));
            $CountryCode = trim(substr($row_add, 3317, 3));


            $Fee = trim(substr($row_add, 229, 7));
            $PartialFee = trim(substr($row_add, 3424, 7));
            $VehicleModel = trim(substr($row_add, 3217, 40));


            if ($VehicleModel = "00") $VehicleModel = "";


            $articles = $rs->Select('Article', "Id1Megasp=" . $Id1Megasp . " AND Id2Megasp=" . $Id2Megasp);
            $article = mysqli_fetch_array($articles);
            $ArticleId = $article['Id'];
            $ViolationTypeId = $article['ViolationTypeId'];

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


            $SpeedLimit = 0.00;
            $SpeedControl = 0.00;
            $Speed = 0.00;


            $ControllerId = $a_controllerid[$a_Row[11]];

            $CityAddress = $a_addressid[$AddressId];

            $VehicleTypeId = $a_VehicleTypeId[$a_Row[1]];


            $aFineDate = explode("/", $FineDate);


            $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
            $ProtocolYear = $aFineDate[2];


            $tariffes = $rs->Select('ArticleTariff', "ArticleId=" . $ArticleId . " AND Year=" . $ProtocolYear);
            $tariff = mysqli_fetch_array($tariffes);
            $Fee = $tariff['Fee'];
            $MaxFee = $tariff['MaxFee'];
            $AdditionalNight = $tariff['AdditionalNight'];


            $aTime = explode(":", $FineTime);
            if ($AdditionalNight) {
                if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                    //FINE_MINUTE_START_DAY
                    //FINE_MINUTE_END_DAY
                    $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                    $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                }
            }


            $StatusTypeId = 1;
            $CountryId = $a_country[$a_Row[4]];
            $VehicleCountry = $a_vehiclecountry[$a_Row[4]];


            if ($CountryId == "Z000") {

                if ($n_AIRE) {
                    $CountryId = "AIRE";
                    $VehicleCountry = "A.I.R.E.";
                    $StatusTypeId = 1;
                } else {
                    $CountryId = "Z00Z";
                    $VehicleCountry = "Italia Noleggi";
                    $StatusTypeId = 2;
                }
            }


            $fines = $rs->Select('Fine', "CityId='" . $CityId . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . mb_convert_encoding($VehiclePlate, 'UTF-8', "pass") . "'");
            $FindNumber = mysqli_num_rows($fines);

            if ($FindNumber == 0) {
                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
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

                if ($CountryCode != "") {
                    $DataSourceId = 10;


                    $TrespasserCountryId = $a_trespasser_country[$CountryCode];
                    $countries = $rs->Select('Country', "Id='" . $TrespasserCountryId . "'");
                    $country = mysqli_fetch_array($countries);


                    $ZoneId = $country['ZoneId'];
                    $LanguageId = $country['LanguageId'];
                    if ($TrespasserCountryId == "Z133" || $LanguageId == 0 || $LanguageId == "") $LanguageId = 1;
                    $a_Name = explode(" ", $NameSurname);

                    if ($n_AIRE) {
                        $TrespasserTypeId = 1;
                    } else {

                        //$files = array_diff(scandir($path . $chk_Folder), array('.', '..'));


                        $TrespasserTypeId = 11;
                    }

                    $trespassers = $rs->Select('Trespasser', "Surname='" . addslashes($a_Name[0]) . "' AND Name='" . addslashes($a_Name[1]) . "' AND CountryId='" . $TrespasserCountryId . "' AND Address='" . addslashes($Address) . "'");
                    if (mysqli_num_rows($trespassers) == 0) {
                        $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='" . $CityId . "'");
                        $Code = mysqli_fetch_array($rs_Code)['Code'];

                        $a_Trespasser = array(
                            array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                            array('field' => 'Code', 'selector' => 'value', 'type' => 'int', 'value' => $Code, 'settype' => 'int'),

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
                        array('field' => 'ReceiveDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($ReceiveDate)),

                    );
                    $rs->Insert('FineTrespasser', $a_FineTrespasser);


                    $StatusTypeId = ($n_AIRE) ? 10 : 2;


                    $a_Fine = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                    );


                    $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);


                } else {
                    $rs_Trespasser = $rs->SelectQuery("SELECT * FROM V_FineTrespasser WHERE  (REPLACE(VehiclePlate,' ','') = '" . mb_convert_encoding($VehiclePlate, 'UTF-8', 'pass') . "') AND (TrespasserTypeId=1 OR TrespasserTypeId=10) ORDER BY FineId DESC");
                    if (mysqli_num_rows($rs_Trespasser) > 0) {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);

                        $TrespasserTypeId = $r_Trespasser['TrespasserTypeId'];

                        $a_FineTrespasser = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Trespasser['TrespasserId'], 'settype' => 'int'),
                            array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                            array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),

                        );
                        $rs->Insert('FineTrespasser', $a_FineTrespasser);

                        $StatusTypeId = ($TrespasserTypeId == 1) ? 10 : 2;

                        $a_Fine = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                        );


                        $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);

                    }
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

fclose($f_AddFile);

unlink($path.$ImportFile);
unlink($path . $a_File[0]);


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