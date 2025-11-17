<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 3000);


$P = CheckValue('P','s');

$n_ContFine = 0;
$DataSourceId = 21;
$CityId = $_SESSION['cityid'];
$VehicleColor = "";
$message=new CLS_MESSAGE();
if($CityId==null){
    $message->addError("Sessione vuota, impossibile procedere conl' importazione");
    echo $message->getMessagesString();
}

$UserId = "'".$_SESSION['username']."'";

$path = PUBLIC_FOLDER."/_VIOLATION_/".$CityId."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;



$file = fopen($path.$ImportFile,  "r");
$delimiter = detectDelimiter($path . $ImportFile);



$countries = $rs->Select('Country', "MegaspCode!=''");
$a_chk_country = array();
while ($country = mysqli_fetch_array($countries)) {
    $a_chk_country_code[$country['MegaspCountry']] = $country['Id'];
}


$countries = $rs->Select('Country', "MegaspCountry!=''");
$a_chk_country = array();
while ($country = mysqli_fetch_array($countries)) {
    $a_chk_country[$country['MegaspCountry']] = $country['Title'];
}


$a_VehicleTypeId = array(
    "A" => "1",
);




$Controllers = $rs->Select('Controller', "CityId='" . $CityId . "'");
while ($Controller = mysqli_fetch_array($Controllers)) {
    $a_controllerid[$Controller['Code']] = $Controller['Id'];

}

if(is_resource($file)) {
    while (!feof($file)) {
        $StatusTypeId = 1;


        $a_Row = fgetcsv($file, 10000, $delimiter);


        if (isset($a_Row[0]) && trim($a_Row[0]) != 'NOME FILE IMMAGINE VERBALE') {

            $a_Documentation = array();
            if (trim($a_Row[0]) != "") $a_Documentation[] = trim($a_Row[0]);
            if (trim($a_Row[43]) != "") $a_Documentation[] = trim($a_Row[43]);
            if (trim($a_Row[63]) != "") $a_Documentation[] = trim($a_Row[63]);


            $DetectorId = 0;
            $DocumentationTypeId = 1;
            $str_FixedWhere = "Fixed IS NULL";


            $Code = trim($a_Row[1]);
            $VehiclePlate = trim($a_Row[8]);
            $VehiclePlate = str_replace("-", "", $VehiclePlate);
            $VehiclePlate = str_replace(",", "", $VehiclePlate);
            $VehiclePlate = str_replace("*", "", $VehiclePlate);


            $VehicleBrand = trim($a_Row[10]);

            $ZIPCity = "";
            $strCountryCode = $NameSurname = $Address = $Country = "";

            if (trim($a_Row[9]) == "") {
                $StatusTypeId = 2;


                $CountryId = "Z00Z";
                $VehicleCountry = "Italia Noleggi";


            } else {

                $CountryCode = trim($a_Row[9]);

                $VehicleCountry = $a_chk_country[$CountryCode];
                $CountryId = $a_chk_country_code[$CountryCode];
            }


            $VehicleTypeId = $a_VehicleTypeId[$a_Row[12]];

            $FineDate = trim($a_Row[3]);
            $FineTime = $a_Row[4];


            $ProtocolYear = substr($FineDate, 0, 4);


            $ArticleId1 = trim($a_Row[19]);
            $ArticleId3 = "";

            if (strpos($a_Row[20], "e") === false) {
                $ArticleId2 = trim($a_Row[20]);
            } else {
                $a_Article = explode("e", trim($a_Row[20]));
                $ArticleId2 = trim($a_Article[0]);
                $ArticleId3 = trim($a_Article[1]);
            }


            $Locality = $CityId;
            $Address = trim($a_Row[6]);

            if (trim($a_Row[7]) != "") $Address .= " Direzione " . trim($a_Row[7]);


            $aFineTime = explode(":", $FineTime);
            if (strlen($aFineTime[0]) < 2) $aFineTime[0] = "0" . $aFineTime[0];
            if (strlen($aFineTime[1]) < 2) $aFineTime[1] = "0" . $aFineTime[1];
            $FineTime = $aFineTime[0] . ":" . $aFineTime[1];

            $Note = "Caricamento da file";


            $ControllerId = $a_controllerid[$a_Row[37]];


            $Where = "Disabled=0 AND CityId='" . $CityId . "' AND Year=" . $ProtocolYear;


            $SpeedLimit = trim($a_Row[44]);
            if ($SpeedLimit == "") $SpeedLimit = 0;


            if ($SpeedLimit > 0) {


                $Speed = trim($a_Row[45]);

                $DetectorCode = trim($a_Row[41]);


                $detectors = $rs->Select('Detector', "CityId='" . $CityId . "' AND Number='" . $DetectorCode . "'");
                $detector = mysqli_fetch_array($detectors);

                $DetectorId = $detector['Id'];
                $chkTolerance = $detector['Tolerance'];
                $str_FixedWhere = 'Fixed=' . $detector['Fixed'];

                $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;


                //$TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
                //$Tolerance = ($TolerancePerc<$chkTolerance) ? $chkTolerance : $TolerancePerc;


                //$Speed = $SpeedControl - $Tolerance;
                $SpeedControl = $Speed + $chkTolerance;

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


                $rs_Article = $rs->Select('V_Article', $Where);
                $r_Article = mysqli_fetch_array($rs_Article);

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

                $DetectorCode = 0;


                $Where .= " AND Id1=" . $ArticleId1 . " AND Id2='" . $ArticleId2 . "'";

                if ($ArticleId3 != "") $Where .= " AND Id3='" . $ArticleId3 . "'";


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
            $str_Where = $str_FixedWhere . " AND ReasonTypeId=1 AND CityId='" . $CityId . "'";
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


            $rs_fine = $rs->Select('Fine', "CityId='" . $CityId . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
            $FindNumber = mysqli_num_rows($rs_fine);


            if ($FindNumber == 0) {
                $rs->Start_Transaction();

                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
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


                if ($CountryId == "Z00Z") {
                    $Name = trim($a_Row[56]);
                    $Surname = trim($a_Row[57]);
                    $Address = trim($a_Row[58]);
                    $Country = trim($a_Row[62]);
                    $Genre = "M";
                    $ZoneId = 1;
                    $LanguageId = 1;

                    $str_Where = "Surname='" . addslashes($Surname) . "' AND Name='" . addslashes($Name) . "' AND Address='" . addslashes($Address) . "'";


                    $rs_Trespasser = $rs->Select('Trespasser', $str_Where);
                    if (mysqli_num_rows($rs_Trespasser) == 0) {

                        $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='" . $CityId . "'");
                        $Code = mysqli_fetch_array($rs_Code)['Code'];

                        $a_Trespasser = array(
                            array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                            array('field' => 'Code', 'selector' => 'value', 'type' => 'int', 'value' => $Code, 'settype' => 'int'),
                            array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                            array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => trim($Surname)),
                            array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => trim($Name)),
                            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                            array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $Country),
                            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => 'ZZZZ'),
                            array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                            array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                            array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                            array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        );

                        $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);

                    } else {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                        $TrespasserId = $r_Trespasser['Id'];
                    }


                    $ReceiveDate = $a_Row[48];
                    $AdditionalCost = $a_Row[36];
                    $TrespasserTypeId = 11;

                    $a_FineTrespasser = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                        array('field' => 'ReceiveDate', 'selector' => 'value', 'type' => 'date', 'value' => $ReceiveDate),
                        array('field' => 'CustomerAdditionalFee', 'selector' => 'value', 'type' => 'flt', 'value' => $AdditionalCost, 'settype' => 'flt'),

                    );

                    $rs->Insert('FineTrespasser', $a_FineTrespasser);


                }


                for ($i = 0; $i < count($a_Documentation); $i++) {
                    $str_Document = trim($a_Documentation[$i]);

                    if (file_exists($path . $str_Document)) {
                        $DocumentName = $str_Document;

                        $str_Folder = ($CountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                        if (!is_dir($str_Folder . "/" . $CityId)) {
                            mkdir($str_Folder . "/" . $CityId, 0777);
                        }
                        if (!is_dir($str_Folder . "/" . $CityId . "/" . $FineId)) {
                            mkdir($str_Folder . "/" . $CityId . "/" . $FineId, 0777);
                        }


                        $a_FineDocumentation = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                        );
                        $rs->Insert('FineDocumentation', $a_FineDocumentation);

                        copy($path . $DocumentName, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);

                        if (file_exists($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
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

