<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");

ini_set('max_execution_time', 5000);


$P = CheckValue('P', 's');
$n_Compress = CheckValue('compress', 'n');
$DataSourceId = 20;
$n_ContFine = 0;

$CityId = $_SESSION['cityid'];
$Locality = $_SESSION['cityid'];


$path = PUBLIC_FOLDER . "/" . $_SESSION['cityid'] . "/";
$ImportFile = CheckValue('ImportFile', 's');

$a_VehicleTypeId = array(
    "AUTOVETTURA"=>1,
    "AUTOVEICOLO"=>1,
    "MOTOVEICOLO"=>2,
    "MOTOCICLO"=>2,
    "AUTOCARAVAN"=>1,
    "AUTOCARRO"=>4,
    "CICLOMOTORE"=>9,
    "MOTOCARRO"=>4,
    "AUTOBUS"=>1,
);




$rs_streettype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($r_streettype = mysqli_fetch_array($rs_streettype)) {
    $a_Street_Type[$r_streettype['Title']] = $r_streettype['Id'];
}


$rs_country = $rs->Select('Country', "MegaspCode!=''");
$a_countryId = array();
$a_CountryTitle = array();

while ($r_country = mysqli_fetch_array($rs_country)){

    $a_countryId[$r_country['MegaspCode']] = $r_country['Id'];
    $a_CountryTitle[$r_country['MegaspCode']] = $r_country['Title'];

}
$rs_controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_chk_controller = array();
$a_Controller = array();
while ($r_controller = mysqli_fetch_array($rs_controller)){
    $a_chk_controller[$r_controller['Code']] = $r_controller['Name'];
    $a_Controller[$r_controller['Code']]=$r_controller['Id'];
}



$xml = simplexml_load_file($path . $ImportFile) or die("Error: Cannot create object");

foreach ($xml->children() as $xml_Import) {
    $StatusTypeId = 1;

    $Code = strval($xml_Import->Numero->Numero."/".$xml_Import->Numero->Tipoa."/".$xml_Import->Numero->Anno);
    $IuvCode = $xml_Import->Numero->IDUnivoco;

    $FineDate = strval($xml_Import->DataRilevazione);
    $ProtocolYear = substr($FineDate,0,4);




    $FineTime = strval($xml_Import->Ore);


    $ControllerCode = (int)$xml_Import->Agenti->Matricola;



    $Address = strval($xml_Import->Luogo->Descrizione);

    if(isset($xml_Import->Luogo->Civico)){
        $Address .= " " . strval($xml_Import->Luogo->Civico);
    }


    $a_Address = explode(" ", $Address);
    $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;

    $VehiclePlate = strval($xml_Import->Targa);
    $VehicleType = strval($xml_Import->Veicolo->Descrizione);
    $VehicleBrand = strval($xml_Import->Marca);
    $VehicleModel = strval($xml_Import->Modello);


    $VehicleTypeId = $a_VehicleTypeId[$VehicleType];

    $VehicleCountryId = strval($xml_Import->Stato->Codice);

    $CountryId = $a_countryId[$VehicleCountryId];
    $CountryTitle = $a_CountryTitle[$VehicleCountryId];

    $Note = "Caricamento da file xml";


    $ControllerId = $a_Controller[$ControllerCode];



    $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


    $DetectorId = 0;
    $DetectorCode = "";

    $SpeedLimit = 0.00;
    $SpeedControl = 0.00;
    $Speed = 0.00;


    $str_FixedWhere = "Fixed IS NULL";

    $str_Article = trim($xml_Import->CDSInfrazioni->Articolo);

    $a_Article = explode("-",$str_Article);

    $Article = trim($a_Article[0]);

    if(isset($a_Article[1])){
        $a_Paragraph = explode("/", trim($a_Article[1]));

        $Paragraph = $a_Paragraph[0];

        $Letter = (isset($a_Paragraph[1])) ?$a_Paragraph[1] : "";

    }else{
        $Paragraph = "0";
        $Letter = "";
    }
    $Where .= " AND Id1=".$Article." AND Id2='".$Paragraph."'";

    if($Letter!=""){
        $Where .= " AND Id3='".$Letter."'";
    }


    $finds = $rs->Select('V_Article', $Where);

    $find = mysqli_fetch_array($finds);

    $ArticleId = $find['Id'];
    $Fee = $find['Fee'];
    $MaxFee = $find['MaxFee'];
    $ViolationTypeId = $find['ViolationTypeId'];


    $VehicleMass = 0;



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
            $str_Where .= ($DetectorCode=="") ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" . $ViolationTypeId;
            break;

        default:
            $str_Where .= " AND ViolationTypeId=" . $ViolationTypeId;

    }

    $rs_Reason = $rs->Select('Reason', $str_Where);
    $r_Reason = mysqli_fetch_array($rs_Reason);

    $ReasonId = $r_Reason['Id'];


    $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
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
            array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $CountryTitle),
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