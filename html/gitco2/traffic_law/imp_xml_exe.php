<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(CLS . "/cls_message.php");
include(INC . "/function.php");
require(INC . "/initialization.php");

ini_set('max_execution_time', 5000);


$P = CheckValue('P', 's');
$n_Compress = CheckValue('compress', 'n');
$DataSourceId = 20;
$n_ContFine = 0;

$CityId = $_SESSION['cityid'];
$message=new CLS_MESSAGE();
if($CityId==null){
    $message->addError("Sessione vuota, impossibile procedere conl' importazione");
    echo $message->getMessagesString();
}

$Locality = $CityId;


$path = PUBLIC_FOLDER . "/_VIOLATION_/" . $CityId . "/";
$ImportFile = CheckValue('ImportFile', 's');
$a_Address = array("001" => "Loc. Vigalfo SP 235 intersezione Via Gioiello dir. Pavia");

$aVehicleTypeId = array(
    "A"=>1,
    "M"=>2,
    "C"=>2,
    "J"=>1,
    "E"=>1,
    "B"=>1,
    "R"=>1,
    "AUTOVEICOLO"=>1,
    "VEICOLO"=>1,
    "MOTOCICLO"=>2,
    "RIMORCHIO"=>7,
    "F"=>1,
    "MOTOVEICOLO"=>2,
);


$rs_streettype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($r_streettype = mysqli_fetch_array($rs_streettype)) {
    $a_Street_Type[$r_streettype['Title']] = $r_streettype['Id'];
}


$rs_country = $rs->Select('Country', "BPCode!=''");
$a_Country = array();
while ($r_country = mysqli_fetch_array($rs_country)) {
    $a_Country[$r_country['BPCode']] = $r_country['Id'];
}

$rs_controller = $rs->Select('Controller', "CityId='" . $CityId . "'");
$a_chk_controller = array();
$a_Controller = array();
while ($r_controller = mysqli_fetch_array($rs_controller)) {
    $a_Controller[$r_controller['Code']] = $r_controller['Id'];
}

$rs_detector = $rs->Select('Detector', "CityId='" . $CityId . "'");
$a_chk_detector = array();
$a_Tolerance = array();
$a_Detector = array();
$a_Fixed = array();
while ($r_detector = mysqli_fetch_array($rs_detector)) {
    $a_chk_detector[$r_detector['Code']] = $r_detector['Kind'];
    $a_Tolerance[$r_detector['Code']] = $r_detector['Tolerance'];
    $a_Detector[$r_detector['Code']] = $r_detector['Id'];
    $a_Fixed[$r_detector['Code']] = $r_detector['Fixed'];
}


$xml = simplexml_load_file($path . $ImportFile) or die("Error: Cannot create object");

foreach ($xml->children() as $xml_Import) {
    $StatusTypeId = 1;


    $DocumentationTypeId = 1;
    $CountryId = (trim($xml_Import->nazione_veicolo)=="I") ? "Z000" : "ZZZZ";
    $VehicleCountry = (trim($xml_Import->nazione_veicolo)=="I") ? "Italia" : "Da assegnare";

    $Code = trim($xml_Import->ref_verbale);
    $DetectorCode = trim(str_replace("MATR. ", "", strtoupper(trim($xml_Import->matricola_rilevatore))));
    $DetectorCode = trim(str_replace("T-XROAD ","",strtoupper(trim($DetectorCode))));

    $SpeedLimit = trim($xml_Import->limite);
    $SpeedControl = trim($xml_Import->velocita_rilevata);
    $FineTime = trim($xml_Import->ora_violazione);
    $FineDate = trim($xml_Import->data_violazione);
    $Address = ($CityId=='I684') ? trim($xml_Import->luogo_violazione) : trim($xml_Import->indirizzo_violazione);
    if ($CityId=='A175') $Address = "Loc. Vigalfo SP 235 intersezione Via Gioiello dir. Pavia";

    //if(isset($a_Address[$Address]))  $Address = $a_Address[$Address];

    $VehiclePlate = trim(strtoupper($xml_Import->targa_veicolo));
    $VehicleTypeId = $aVehicleTypeId[trim(strtoupper($xml_Import->tipo_veicolo))];
    $ControllerCode = trim($xml_Import->matricola_accertatore);
    $chk_Mass = trim($xml_Import->comma);

    $a_Address = explode(" ", $Address);
    $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;

    $Note = "Caricamento da file xml";


    $ControllerId = $a_Controller[$ControllerCode];


    $ProtocolYear = substr($FineDate, 0, 4);

    $Where = "Disabled=0 AND CityId='" . $CityId . "' AND Year=" . $ProtocolYear;

    if(trim($xml_Import->articolo==7) && $CityId=='I684' && $DetectorCode=="" &&  trim($xml_Import->comma!="1-14")) {
        $DetectorCode = '010101';
    }

    $aTime = explode(":", $FineTime);

    if ($DetectorCode != "") {
        $DetectorId = $a_Detector[$DetectorCode];

        $str_FixedWhere = "Fixed=" . $a_Fixed[$DetectorCode];

        if(trim($xml_Import->articolo==142)) {
            $TimeTLightFirst = 0;
            $chk_Tolerance = $a_Tolerance[$DetectorCode];
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
            $AdditionalNight = $find['AdditionalNight'];





            if ($chk_Mass == "7-11" || $chk_Mass == "8-11" || $chk_Mass == "9-11") {
                $Fee = $Fee * FINE_MASS;
                $MaxFee = $MaxFee * FINE_MASS;
                $VehicleMass = 4;
            } else {
                $VehicleMass = 0;
            }

        } else {
            $SpeedLimit = 0.00;
            $SpeedControl = 0.00;
            $Speed = 0.00;
            $TimeTLightFirst =1;
            $chk_Article = trim($xml_Import->articolo);

            $a_Paragraph = explode("-", trim($xml_Import->comma));

            $chk_Paragraph = $a_Paragraph[0];

            $Where .= " AND Id1=" . $chk_Article . " AND Id2='" . $chk_Paragraph . "'";
            $chk_Letter = "";
            if (isset($a_Paragraph[1])) {
                $chk_Letter = $a_Paragraph[1];
                $Where .= " AND Id3='" . $chk_Letter . "'";
            } else {
                $chk_Letter = trim($xml_Import->lettera);
                if ($chk_Letter != "") {
                    $Where .= " AND Id3='" . $chk_Letter . "'";
                }
            }

            $finds = $rs->Select('V_Article', $Where);

            $find = mysqli_fetch_array($finds);

            $ArticleId = $find['Id'];
            $Fee = $find['Fee'];
            $MaxFee = $find['MaxFee'];
            $ViolationTypeId = $find['ViolationTypeId'];
            $AdditionalNight = $find['AdditionalNight'];

            $VehicleMass = 0;
        }


    } else {
        $DetectorId = 0;


        $SpeedLimit = 0.00;
        $SpeedControl = 0.00;
        $Speed = 0.00;

        $chk_Article = trim($xml_Import->articolo);
        $str_FixedWhere = "Fixed IS NULL";

        $a_Paragraph = explode("-", trim($xml_Import->comma));

        $chk_Paragraph = $a_Paragraph[0];

        $Where .= " AND Id1=" . $chk_Article . " AND Id2='" . $chk_Paragraph . "'";
        $chk_Letter = "";
        if (isset($a_Paragraph[1])) {
            $chk_Letter = $a_Paragraph[1];
            $Where .= " AND Id3='" . $chk_Letter . "'";
        } else {
            $chk_Letter = trim($xml_Import->lettera);
            if ($chk_Letter != "") {
                $Where .= " AND Id3='" . $chk_Letter . "'";
            }
        }

        $finds = $rs->Select('V_Article', $Where);

        $find = mysqli_fetch_array($finds);

        $ArticleId = $find['Id'];
        $Fee = $find['Fee'];
        $MaxFee = $find['MaxFee'];
        $ViolationTypeId = $find['ViolationTypeId'];
        $AdditionalNight = $find['AdditionalNight'];

        $VehicleMass = 0;

    }
    if($AdditionalNight == 1){
        if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
            $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
            $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

        }
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
            $str_Where .= ($DetectorCode=="") ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" . $ViolationTypeId;
            break;

        default:
            $str_Where .= " AND ViolationTypeId=" . $ViolationTypeId;

    }

    $rs_Reason = $rs->Select('Reason', $str_Where);
    $r_Reason = mysqli_fetch_array($rs_Reason);

    $ReasonId = $r_Reason['Id'];


    $fines = $rs->Select('Fine', "CityId='" . $CityId . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
    $FindNumber = mysqli_num_rows($fines);

    $FineId = null;
    
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
            DIE;
        }

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
            array('field' => 'TimeTLightFirst', 'selector' => 'value', 'type' => 'int', 'value' => $TimeTLightFirst, 'settype' => 'int'),

        );

        $rs->Insert('FineArticle', $a_FineArticle);




        $b_Trespasser = ($CountryId=="Z000") ? false : true;
        $b_TrespasserRent = false;
        $strCountry="";

        foreach ($xml_Import->trasgressori->children() as $xml_Trespasser) {
            $TrespasserTypeId = trim($xml_Trespasser->tipo_trasgressore);

            if($TrespasserTypeId==1){
                $b_Trespasser = false;
                $CompanyName = trim($xml_Trespasser->ragione_sociale_trasgressore. " ".$xml_Trespasser->cognome_trasgressore);
                $CompanyAddress = trim($xml_Trespasser->indirizzo_trasgressore);
                $CompanyCity = trim($xml_Trespasser->citta_trasgressore);
                $CompanyProvince = trim($xml_Trespasser->provincia_trasgressore);
                $CompanyZIP = trim($xml_Trespasser->cap_trasgressore);


                $CompanyCountryId = ($b_TrespasserRent) ? "Z000" : "AIRE";
                $TrespasserTypeId = ($b_TrespasserRent) ? 10 : 1;
                $Genre = "D";


                $str_Where = "CompanyName='" . addslashes($CompanyName) . "' AND CountryId='" . $CompanyCountryId . "' AND Address='" . addslashes($CompanyAddress) . "'";

                if(strlen($CompanyProvince)>2){
                    $CompanyProvince = "";
                    $CompanyCity .= " ".$CompanyProvince;
                }

                $rs_Trespasser = $rs->Select('Trespasser', $str_Where);
                if (mysqli_num_rows($rs_Trespasser) == 0) {
                    $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$CityId."'");
                    $Code = mysqli_fetch_array($rs_Code)['Code'];
                    
                    $a_Trespasser = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),   
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



            } else if($TrespasserTypeId==10){
                $b_TrespasserRent = true;
                $b_Trespasser = false;
                $TrespasserName = trim($xml_Trespasser->ragione_sociale_trasgressore ." ". $xml_Trespasser->cognome_trasgressore);
                $TrespasserAddress = trim($xml_Trespasser->indirizzo_trasgressore);
                $TrespasserCity = trim($xml_Trespasser->cap_trasgressore ." ". $xml_Trespasser->citta_trasgressore ." ". $xml_Trespasser->nazione_trasgressore);
                $TrespasserProvince = trim($xml_Trespasser->provincia_trasgressore);

                $BornPlace = trim($xml_Trespasser->luogo_nascita_trasgressore);
                $BornDate = trim($xml_Trespasser->data_nascita_trasgressore);


/*
<numero_patente_trasgressore>12.267.271</numero_patente_trasgressore>
<data_rilascio_trasgressore>01/04/2015</data_rilascio_trasgressore>
<ufficio_rilascio_trasgressore/>
<categoria_patente_trasgressore>B</categoria_patente_trasgressore>
*/




                if(strlen($TrespasserProvince)>2){
                    $TrespasserCity .= " ".$TrespasserProvince;
                    $TrespasserProvince = "";
                }


                $TrespasserCountry = strtoupper(trim($xml_Trespasser->nazione_trasgressore));
                if(! isset($a_Country[$TrespasserCountry])){
                    $TrespasserCountry = strtoupper(trim($xml_Trespasser->provincia_trasgressore));
                }
                $TrespasserZIP = trim($xml_Trespasser->cap_trasgressore);
                $AdditionalCost = number_format(trim($xml_Import->costi_aggiuntivi), 2, '.', '');
                $ReceiveDate = trim($xml_Trespasser->data_acquisizione_dati_trasgressore);

                if($ReceiveDate == "0000-00-00") $ReceiveDate = "";

                $TrespasserCountryId = (isset($a_Country[$TrespasserCountry])) ? $a_Country[$TrespasserCountry] : $a_Country['EE'];;
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
                    $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$CityId."'");
                    $Code = mysqli_fetch_array($rs_Code)['Code'];
                    
                    $a_Trespasser = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),                     
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserAddress),
                        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserZIP),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCity),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCountryId),
                        array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserProvince),
                        array('field' => 'BornPlace', 'selector' => 'value', 'type' => 'str', 'value' => $BornPlace),
                        array('field' => 'BornDate', 'selector' => 'value', 'type' => 'date', 'value' => $BornDate),
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
                    array('field' => 'CustomerAdditionalFee', 'selector' => 'value', 'type' => 'flt', 'value' => $AdditionalCost, 'settype' => 'flt'),

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

            }
        }


        if($b_Trespasser){
            $rs_FineTrespasser = $rs->Select('V_FineTrespasser', "REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'","FineDate DESC");
            if(mysqli_num_rows($rs_FineTrespasser)>0){

                $r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser);
                $a_FineTrespasser = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineTrespasser['TrespasserId'], 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                );
                $rs->Insert('FineTrespasser', $a_FineTrespasser);

                $StatusTypeId = 10;
                $a_Fine = array(
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $r_FineTrespasser['VehicleCountryId']),
                    array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $r_FineTrespasser['VehicleCountry']),

                );


                $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);


            }
        }


/*
        foreach($xml_Import->documenti->children()  as $xml_Document) {

            $Documentation = trim($xml_Document->nome_documento);

            if (strpos($Documentation, 'http') === false){
                $str_DocumentType = strtoupper(substr($Documentation, -3));

                if ($Documentation != "" && file_exists($path . $Documentation)) {

                    $DocumentName = $Documentation;


                    $str_Folder = ($CountryId=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


                    if (!is_dir($str_Folder . "/" . $CityId . "/" . $FineId)) {
                        mkdir($str_Folder . "/" . $CityId . "/" . $FineId, 0777);
                    }


                    //if( $CityId=="A175" && $str_DocumentType == "PDF"){
                    //    $DocumentName = substr($DocumentName, 0, strlen($DocumentName)-3)."jpg";
                    //}



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
                        $img->writeImage($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                        $img->destroy();

                    } else {
                        copy($path . $Documentation, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                    }


                    if (file_exists($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
                        unlink($path . $Documentation);
                    } else {
                        echo "Poblemi con la creazione del documento: " . $DocumentName;
                        DIE;
                    }

                }
            }


        } */
        $rs->End_Transaction();
    }
    else //se il verbale c'è già ma non aveva immagine le importo
    {
        $r_fine = mysqli_fetch_array($fines);
        $FineId = $r_fine['Id'];
    }
    
    if ($FineId != null) 
    {
        $rs->Start_Transaction();
        
        foreach($xml_Import->documenti->children()  as $xml_Document) {
            
            $Documentation = trim($xml_Document->nome_documento);
            
            $rs_FineDocumentation = $rs->Select('FineDocumentation', "Documentation='$Documentation' AND FineId=$FineId");
            $FindDocument = mysqli_num_rows($rs_FineDocumentation);
            
            if ($FindDocument == 0) {
                if (strpos($Documentation, 'http') === false){
                    $str_DocumentType = strtoupper(substr($Documentation, -3));
                    
                    if ($Documentation != "" && file_exists($path . $Documentation)) {
                        
                        $DocumentName = $Documentation;
                        
                        
                        $str_Folder = ($CountryId=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                        
                        
                        if (!is_dir($str_Folder . "/" . $CityId . "/" . $FineId)) {
                            mkdir($str_Folder . "/" . $CityId . "/" . $FineId, 0777);
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
                            $img->writeImage($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                            $img->destroy();
                            
                        } else {
                            copy($path . $Documentation, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                        }
                        
                        
                        if (file_exists($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
                            unlink($path . $Documentation);
                        } else {
                            echo "Poblemi con la creazione del documento: " . $DocumentName;
                            DIE;
                        }
                        
                    }
                }
            }
            
        }
        $rs->End_Transaction();
    }

}

fclose($file);


unlink($path . $ImportFile);


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