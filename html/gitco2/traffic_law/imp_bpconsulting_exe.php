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
$Locality = $_SESSION['cityid'];


$UserId = "'".$_SESSION['username']."'";

$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;


$file = fopen($path.$ImportFile,  "r");
$delimiter = ",";


$aVehicleTypeId = array(
	"A"=>1,
	"M"=>2,
);


$rs_streettype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($r_streettype = mysqli_fetch_array($rs_streettype)){
	$a_Street_Type[$r_streettype['Title']] = $r_streettype['Id'];
}


$rs_country = $rs->Select('Country', "BPCode!=''");
$a_Country = array();
while ($r_country = mysqli_fetch_array($rs_country)){
    $a_Country[$r_country['BPCode']]=$r_country['Id'];
}

$rs_controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_chk_controller = array();
$a_Controller = array();
while ($r_controller = mysqli_fetch_array($rs_controller)){
    $a_Controller[$r_controller['Code']]=$r_controller['Id'];
}

$rs_detector = $rs->Select('Detector', "CityId='".$_SESSION['cityid']."'");
$a_chk_detector = array();
$a_Tolerance = array();
$a_Detector = array();
$a_Fixed = array();
while ($r_detector = mysqli_fetch_array($rs_detector)){
    $a_chk_detector[$r_detector['Code']] = $r_detector['Kind'];
    $a_Tolerance[$r_detector['Code']] = $r_detector['Tolerance'];
    $a_Detector[$r_detector['Code']] = $r_detector['Id'];
    $a_Fixed[$r_detector['Code']] = $r_detector['Fixed'];
}





if(is_resource($file)) {

    while (!feof($file)) {
        $StatusTypeId = 1;
        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0])) {


            $DocumentationTypeId = 1;
            $CountryId = "ZZZZ";
            $VehicleCountry = "Da assegnare";

            $Code = trim($row[1]);
            $DetectorCode = trim(str_replace("MATR. ", "", strtoupper(trim($row[41]))));
            $SpeedLimit = $row[44];
            $SpeedControl = $row[45];
            $FineTime = $row[4];
            $FineDate = $row[3];
            $Address = $row[6];
            $VehiclePlate = trim(strtoupper($row[8]));
            $VehicleTypeId = $aVehicleTypeId[trim(strtoupper($row[12]))];
            $ControllerCode = trim($row[37]);
            $Documentation = trim($row[43]);

            $chk_Mass = trim($row[20]);


            $TrespasserName = trim($row[50]);


            $a_Address = explode(" ", $Address);
            $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


            $Note = "Caricamento da file";


            $ControllerId = $a_Controller[$ControllerCode];


            $ProtocolYear = substr($FineDate, 0, 4);

            $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


            if ($DetectorCode != "") {
                $DetectorId = $a_Detector[$DetectorCode];
                $chk_Tolerance = $a_Tolerance[$DetectorCode];
                $str_FixedWhere = "Fixed=" . $a_Fixed[$DetectorCode];

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


                $finds = $rs->Select('V_Article', $Where);

                $find = mysqli_fetch_array($finds);

                $ArticleId = $find['Id'];
                $Fee = $find['Fee'];
                $MaxFee = $find['MaxFee'];
                $ViolationTypeId = $find['ViolationTypeId'];


                $aTime = explode(":", $FineTime);

                if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                    $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                    $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                }

                if ($chk_Mass == "8-11") {
                    $Fee = $Fee * FINE_MASS;
                    $MaxFee = $MaxFee * FINE_MASS;
                    $VehicleMass = 4;
                } else {
                    $VehicleMass = 0;
                }

            } else {
                $chk_Article = $row[19];
                $str_FixedWhere = "Fixed IS NULL";
                $a_Paragraph = explode("-", $row[20]);

                $chk_Paragraph = $a_Paragraph[0];

                $Where .= " AND Id1=" . $chk_Article . " AND Id2='" . $chk_Paragraph . "'";
                $chk_Letter = "";
                if (isset($a_Paragraph[1])) {
                    $chk_Letter = $a_Paragraph[1];
                    $Where .= " AND Id3='" . $chk_Letter . "'";
                }

                $finds = $rs->Select('V_Article', $Where);

                $find = mysqli_fetch_array($finds);

                $ArticleId = $find['Id'];
                $Fee = $find['Fee'];
                $MaxFee = $find['MaxFee'];
                $ViolationTypeId = $find['ViolationTypeId'];


                $VehicleMass = 0;

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


            $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . $VehiclePlate . "'");
            $FindNumber = mysqli_num_rows($fines);

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
                    array('field' => 'StreetTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StreetTypeId, 'settype' => 'int'),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                    array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                    array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                    array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                    array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleMass, 'settype' => 'int'),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                    array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                );


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
                );

                $rs->Insert('FineArticle', $a_FineArticle);


                if (strlen($TrespasserName) > 0) {


                    $CompanyName = trim($row[66]);
                    $CompanyAddress = trim($row[67]);
                    $CompanyCity = trim($row[68]);
                    $CompanyProvince = trim($row[69]);
                    $CompanyZIP = trim($row[70]);

                    $CompanyCountryId = "Z000";

                    $ReceiveDate = trim($row[72]);
                    $AdditionalFee = number_format(trim($row[36]), 2, '.', '');


                    $DataSourceId = 20;
                    $TrespasserTypeId = 10;
                    $Genre = "D";


                    $str_Where = "CompanyName='" . addslashes($CompanyName) . "' AND CountryId='" . $CompanyCountryId . "' AND Address='" . addslashes($CompanyAddress) . "'";


                    $rs_Trespasser = $rs->Select('Trespasser', $str_Where);


                    if (mysqli_num_rows($rs_Trespasser) == 0) {

                        $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='" . $_SESSION['cityid'] . "'");
                        $Code = mysqli_fetch_array($rs_Code)['Code'];

                        $a_Trespasser = array(
                            array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'Code', 'selector' => 'value', 'type' => 'int', 'value' => $Code, 'settype' => 'int'),
                            array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                            array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName),
                            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyAddress),
                            array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyZIP),
                            array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyCity),
                            array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyProvince),
                            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyCountryId),
                            array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                            array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                            array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                            array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        );


                        $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);
                    } else {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                        $TrespasserId = $r_Trespasser['Id'];

                    }


                    $a_FineTrespasser = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),

                    );
                    $rs->Insert('FineTrespasser', $a_FineTrespasser);


                    $TrespasserAddress = trim($row[51]);
                    $TrespasserCity = trim($row[52]);
                    $TrespasserCountry = strtoupper(trim($row[53]));
                    $TrespasserZIP = trim($row[54]);

                    $TrespasserCountryId = $a_Country[$TrespasserCountry];
                    $TrespasserTypeId = 11;
                    $Surname = "";
                    $Name = "";
                    $CompanyName = "";
                    $a_Name = explode(" ", $TrespasserName);
                    if (count($a_Name) == 1) {
                        $Genre = "D";

                        $CompanyName = $a_Name[0];

                        $str_Where = "CompanyName='" . addslashes($CompanyName) . "' AND CountryId='" . $TrespasserCountryId . "' AND Address='" . addslashes($TrespasserAddress) . "'";

                    } else {
                        if (count($a_Name) == 2) {
                            $Surname = trim($a_Name[0]);
                            $Name = trim($a_Name[1]);

                        } else {
                            for ($i = 0; $i < count($a_Name); $i++) {
                                if ($i <= 1) {
                                    $Surname .= $a_Name[$i] . " ";
                                } else {
                                    $Name .= $a_Name[$i] . " ";
                                }

                            }

                        }
                        $Surname = trim($Surname);
                        $Name = trim($Name);
                        $Genre = "M";

                        $str_Where = "Surname='" . addslashes($Surname) . "' AND Name='" . addslashes($Name) . "' AND CountryId='" . $TrespasserCountryId . "' AND Address='" . addslashes($TrespasserAddress) . "'";

                    }


                    $rs_Country = $rs->Select('Country', "Id='" . $TrespasserCountryId . "'");
                    $r_Country = mysqli_fetch_array($rs_Country);


                    $ZoneId = $r_Country['ZoneId'];
                    $LanguageId = $r_Country['LanguageId'];


                    $rs_Trespasser = $rs->Select('Trespasser', $str_Where);
                    if (mysqli_num_rows($rs_Trespasser) == 0) {

                        $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='" . $_SESSION['cityid'] . "'");
                        $Code = mysqli_fetch_array($rs_Code)['Code'];

                        $a_Trespasser = array(
                            array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'Code', 'selector' => 'value', 'type' => 'int', 'value' => $Code, 'settype' => 'int'),
                            array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserAddress),
                            array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserZIP),
                            array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCity),
                            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCountryId),
                            array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                            array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                            array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                            array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        );

                        if ($Genre == "D") {

                            $a_Trespasser[] = array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName);

                        } else {

                            $a_Trespasser[] = array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => trim($Surname));
                            $a_Trespasser[] = array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => trim($Name));

                        }

                        $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);
                    } else {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                        $TrespasserId = $r_Trespasser['Id'];

                    }


                    $a_FineTrespasser = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                        array('field' => 'ReceiveDate', 'selector' => 'value', 'type' => 'date', 'value' => $ReceiveDate),
                        array('field' => 'AdditionalFee', 'selector' => 'value', 'type' => 'flt', 'value' => $AdditionalFee, 'settype' => 'flt'),

                    );
                    $rs->Insert('FineTrespasser', $a_FineTrespasser);


                    $CountryId = "Z00Z";
                    $VehicleCountry = "Italia Noleggi";

                    $StatusTypeId = 10;
                    $a_Fine = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),

                    );


                    $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);

                } else if (trim($row[66]) != "") {

                    $TrespasserName = trim($row[66]);
                    $TrespasserAddress = trim($row[67]);
                    $TrespasserCity = trim($row[68] . " " . $row[69]);
                    $TrespasserCountry = "A.I.R.E.";
                    $TrespasserZIP = trim($row[70]);

                    $TrespasserCountryId = "AIRE";
                    $TrespasserTypeId = 1;
                    $Surname = "";
                    $Name = "";
                    $CompanyName = "";
                    $a_Name = explode(" ", $TrespasserName);
                    if (count($a_Name) == 1) {
                        $Genre = "D";

                        $CompanyName = $a_Name[0];

                        $str_Where = "CompanyName='" . addslashes($CompanyName) . "' AND CountryId='" . $TrespasserCountryId . "' AND Address='" . addslashes($TrespasserAddress) . "'";

                    } else {
                        if (count($a_Name) == 2) {
                            $Surname = trim($a_Name[0]);
                            $Name = trim($a_Name[1]);

                        } else {
                            for ($i = 0; $i < count($a_Name); $i++) {
                                if ($i <= 1) {
                                    $Surname .= $a_Name[$i] . " ";
                                } else {
                                    $Name .= $a_Name[$i] . " ";
                                }

                            }

                        }
                        $Surname = trim($Surname);
                        $Name = trim($Name);
                        $Genre = "M";

                        $str_Where = "Surname='" . addslashes($Surname) . "' AND Name='" . addslashes($Name) . "' AND CountryId='" . $TrespasserCountryId . "' AND Address='" . addslashes($TrespasserAddress) . "'";

                    }


                    $ZoneId = 1;
                    $LanguageId = 1;


                    $rs_Trespasser = $rs->Select('Trespasser', $str_Where);
                    if (mysqli_num_rows($rs_Trespasser) == 0) {
                        $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='" . $_SESSION['cityid'] . "'");
                        $Code = mysqli_fetch_array($rs_Code)['Code'];

                        $a_Trespasser = array(
                            array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'Code', 'selector' => 'value', 'type' => 'int', 'value' => $Code, 'settype' => 'int'),
                            array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserAddress),
                            array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserZIP),
                            array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCity),
                            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCountryId),
                            array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                            array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                            array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                            array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        );

                        if ($Genre == "D") {

                            $a_Trespasser[] = array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName);

                        } else {

                            $a_Trespasser[] = array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => trim($Surname));
                            $a_Trespasser[] = array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => trim($Name));

                        }

                        $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);
                    } else {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                        $TrespasserId = $r_Trespasser['Id'];

                    }


                    $a_FineTrespasser = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                        array('field' => 'ReceiveDate', 'selector' => 'value', 'type' => 'date', 'value' => $ReceiveDate),
                        array('field' => 'AdditionalFee', 'selector' => 'value', 'type' => 'flt', 'value' => $AdditionalFee, 'settype' => 'flt'),

                    );

                    $rs->Insert('FineTrespasser', $a_FineTrespasser);


                    $CountryId = "AIRE";
                    $VehicleCountry = "A.I.R.E.";

                    $StatusTypeId = 10;
                    $a_Fine = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),

                    );


                    $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);
                }


                if (file_exists($path . $Documentation)) {

                    $DocumentName = $Documentation;


                    //$str_Folder = ($a_country_id[$row[8]]=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                    $str_Folder = FOREIGN_VIOLATION;


                    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                        mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                    }


                    $a_FineDocumentation = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                    );
                    $rs->Insert('FineDocumentation', $a_FineDocumentation);

                    $str_DocumentType = strtoupper(substr($DocumentName, -3));


                    if ($compress && $str_DocumentType != "PDF") {
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
                        die;
                    }

                }


                $rs->End_Transaction();
            }
        }
    }

    fclose($file);
}


unlink($path.$ImportFile);


if($n_ContFine>0){


    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

        $str_Content = $r_UserMail['CityTitle'].": sono statie elaborate n. ".$n_ContFine." violazioni.";

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