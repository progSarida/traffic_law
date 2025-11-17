<?php
include(INC."/function_postalCharge.php");

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_send_fine.php'.$Filters));
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$str_Success = $str_Warning = '';

$ProtocolNumber = 0;


$CityId = "'".$_SESSION['cityid']."'";
$Locality = "'".$_SESSION['cityid']."'";


$StatusTypeId = 20;
$NotificationTypeId = 6;



$NotificationDate = date("Y-m-d");

$PrintTypeId = ($RegularPostalFine) ? 4 : 1;
$DocumentTypeId = 5;

$ultimate = CheckValue('ultimate','s');

$a_Lan = unserialize(LANGUAGE);
$a_Rent = unserialize(RENT);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
$a_AdditionalMass = unserialize(ADDITIONAL_MASS);

$a_PrinterConfigs = unserialize(PRINTER_FTP_CONFIG);
$a_PrinterConf = $a_PrinterConfigs[$PrinterId] ?? null;

//Se viene selezionato uno stampatore per cui è previsto l'invio del flusso tramite FTP, tenta la connessione
if($a_PrinterConf && $ultimate && PRODUCTION){
    $phpFTP = PhpFTPFactory::create(
        $a_PrinterConf['Type'],
        $a_PrinterConf['Host'],
        $a_PrinterConf['Username'],
        $a_PrinterConf['Password'],
        $a_PrinterConf['Port']);
    if(!$phpFTP->connect()){
        $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
        header("location: ".$P);
        DIE;
    } else {
        $phpFTP->disconnect();
    }
}

//Parametri stampatore////////////////////////////
$str_Mod23LSubject          = $r_Customer['ForeignMod23LSubject'];
$str_Mod23LCustomerName     = $r_Customer['ForeignMod23LCustomerName'];

$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrinterId AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_SmaName                = $r_PrintParameter['ForeignSmaName'] ?? '';
$str_SmaAuthorization       = $r_PrintParameter['ForeignSmaAuthorization'] ?? '';
$str_SmaPayment             = $r_PrintParameter['ForeignSmaPayment'] ?? '';

$str_Mod23LCustomerSubject  = $r_PrintParameter['ForeignMod23LCustomerSubject'] ?? '';
$str_Mod23LCustomerAddress  = $r_PrintParameter['ForeignMod23LCustomerAddress'] ?? '';
$str_Mod23LCustomerCity     = $r_PrintParameter['ForeignMod23LCustomerCity'] ?? '';
//$str_PostalAuthorization = trim($r_PrintParameter['ForeignPostalAuthorization']) ?: $r_Customer['ForeignPostalAuthorization'];
/////////////////////////////////////////////////

if(isset($_POST['checkbox'])) {
    
    $rs->Start_Transaction();
    
    $str_WhereCity = ($r_Customer['CityUnion']>=1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
    $rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
    $a_ProtocolLetterLocality = array();
    while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
    }


    $rs_CountryBank = $rs->Select("CountryBank", "CityId='" . $_SESSION['cityid'] . "'");
    $a_CountryBank = array();
    while($r_CountryBank = mysqli_fetch_array($rs_CountryBank)){
        $a_CountryBank[$r_CountryBank['CountryId']] = array(
            "Currency"=> $r_CountryBank['Currency'],
            "BankOwner"=> $r_CountryBank['BankOwner'],
            "BankName"=> $r_CountryBank['BankName'],
            "BankAccount"=> $r_CountryBank['BankAccount'],
            "BankIban"=> $r_CountryBank['BankIban'],
            "BankSwift"=> $r_CountryBank['BankSwift'],
        );
    }



    $controllers = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Sign !='' AND Disabled=0");
    $controller = mysqli_fetch_array($controllers);

    if($ultimate){
        $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
        $flow = mysqli_fetch_array($flows);

        $int_FlowNumber = $flow['Number']+1;

        $FileNameDoc = "Flusso_".$int_FlowNumber."_Verb_Estero_".$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);

        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";

        $aInsert = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
            array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
            array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$PrinterId,'settype'=>'int'),
            array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>$PrintTypeId,'settype'=>'int'),
            array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeId,'settype'=>'int'),
            array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
            array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
            array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
            array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
        );

        $FlowId = $rs->Insert('Flow',$aInsert);


    }
    else{
        $FileNameDoc = "Flusso_Verb_Estero_".$_SESSION['cityid']."_PROVVISORIO";
        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";
        
    }


    $path = FOREIGN_FLOW."/".$_SESSION['cityid']."/";
    $myfile = fopen($path.$Documentation, "w") or die("Unable to open file!");


    $zoneSum = array(0,0,0,0);

    $checkFlowHeader = 0;
    foreach($_POST['checkbox'] as $FineId) {
        $chk_180                    = false;
        $chk_126Bis                 = false;
        $chk_ReducedPayment         = false;

        $n_LicensePoint             = 0;
        $n_TotPartialFee            = 0;
        $n_TotFee                   = 0;
        $n_TotMaxFee                = 0;


        $NotificationFee = 0;
        $ChargeTotalFee = 0;
        $ResearchFee = 0;


        if ($r_Customer['ForeignTotalFee'] > 0) $ChargeTotalFee = $r_Customer['ForeignTotalFee'];
        else {
            if ($r_Customer['ForeignNotificationFee'] > 0) {
                $NotificationFee = $r_Customer['ForeignNotificationFee'];
            }
            $ResearchFee = $r_Customer['ForeignResearchFee'];
        }


        $rs_Fine = $rs->Select('V_FineArticle', "Id=" . $FineId. " AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
            $ViolationTypeId = $r_Fine['ViolationTypeId'];
            $ProtocolYear = $r_Fine['ProtocolYear'];
            
            $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType1'];
            $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType2'];

            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);

            $RuleTypeId = $r_RuleType['Id'];

            $Fee = $r_Fine['Fee'];

            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);
            $zoneSum[$trespasser['ZoneId']]++;
            $ZoneId = $trespasser['ZoneId'];

            $n_LanguageId = $trespasser['LanguageId'];

            $ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            $FormTypeId = $r_RuleType['ForeignFormId'];

            $a_PrintObject = explode("*", $r_RuleType['PrintObject' . $a_Lan[$n_LanguageId]]);


            $str_AdditionalNight = "";
            if ($r_ArticleTariff['AdditionalNight'] == 1) {

                $a_Time = explode(":", $r_Fine['FineTime']);

                if ($a_Time[0] < FINE_HOUR_START_DAY || ($a_Time[0] > FINE_HOUR_END_DAY) || ($a_Time[0] == FINE_HOUR_END_DAY && $a_Time[1] != "00")) {
                    $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId];
                }
            }

            $str_AdditionalMass = "";
            if ($r_ArticleTariff['AdditionalMass'] == 1) {
                if ($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
            }
            if ($r_ArticleTariff['126Bis'] == 1) {
                $chk_126Bis = true;
            }
            if (($r_Fine['Article'] == 126 AND $r_Fine['Paragraph'] == '0' AND $r_Fine['Letter'] == 'bis') || ($r_Fine['Article'] == 180 AND $r_Fine['Paragraph'] == '8' AND $r_Fine['Letter'] == '')) {
                $PreviousId = $r_Fine['PreviousId'];

                $rs_PreviousFine = $rs->Select('Fine', "Id=" . $PreviousId);
                $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);

                $PreviousProtocolId = $r_PreviousFine['ProtocolId'];
                $PreviousProtocolYear = $r_PreviousFine['ProtocolYear'];
                $PreviousFineDate = DateOutDB($r_PreviousFine['FineDate']);


            } else {

                $PreviousProtocolId = "";
                $PreviousProtocolYear = "";
                $PreviousFineDate = "";


            }

            $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);
            
            if ($ChargeTotalFee > 0) {
                $ResearchFee = $ChargeTotalFee - $postalcharge['Zone' . $ZoneId];
                $NotificationFee = $postalcharge['Zone' . $ZoneId];
            } else {
                if ($NotificationFee == 0) {
                    $NotificationFee = $postalcharge['Zone' . $ZoneId];
                }
            }


            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;

            $TotalFee = $Fee + $ResearchFee + $NotificationFee;


            $SpeedLengthAverage = 0;
            $DetectorTitle = "";

            $FineTime = $r_Fine['FineTime'];
            if ($r_Fine['DetectorId'] != 0) {
                $detectors = $rs->Select('Detector', "Id=" . $r_Fine['DetectorId']);
                $detector = mysqli_fetch_array($detectors);

                $DetectorTitle = $detector['Title' . $a_Lan[$n_LanguageId]];
                $SpeedLengthAverage = $detector['SpeedLengthAverage'];

            }
            $str_ProtocolLetter = ($RuleTypeId == 1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
            $Content = getStaticContent($FormTypeId,$_SESSION['cityid'],2, $n_LanguageId);


            $Content = str_replace("{PrintObjectRow1}", $a_PrintObject[0], $Content);
            $Content = str_replace("{PrintObjectRow2}", $a_PrintObject[1], $Content);
            $Content = str_replace("{PrintObjectRow3}", $a_PrintObject[2], $Content);

            $Content = str_replace("<h3>", "", $Content);
            $Content = str_replace("</h3>", "", $Content);
            $Content = str_replace("<h4>", "", $Content);
            $Content = str_replace("</h4>", "", $Content);
            $Content = str_replace("<h5>", "", $Content);
            $Content = str_replace("</h5>", "", $Content);
            $Content = str_replace("<b>", "", $Content);
            $Content = str_replace("</b>", "", $Content);
            $Content = str_replace(array("\n", "\r"), "", $Content);

            $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
            $Content = str_replace("{FineTime}", TimeOutDB($FineTime), $Content);
            $Content = str_replace("{VehicleTypeId}", $r_Fine['VehicleTitle' . $a_Lan[$n_LanguageId]], $Content);
            $Content = str_replace("{VehiclePlate}", $r_Fine['VehiclePlate'], $Content);

            $Content = str_replace("{VehicleBrand}", $r_Fine['VehicleBrand'], $Content);
            $Content = str_replace("{VehicleModel}", $r_Fine['VehicleModel'], $Content);
            $Content = str_replace("{VehicleColor}", $r_Fine['VehicleColor'], $Content);

            $Content = str_replace("{IuvCode}", $r_Fine['IuvCode'], $Content);

            $Paragraph = ($r_Fine['Paragraph'] == "0" || $r_Fine['Paragraph'] == "") ? "" : " / " . $r_Fine['Paragraph'];
            $Letter = ($r_Fine['Letter'] == "0") ? "" : $r_Fine['Letter'];

            $Content = str_replace("{ArticleId}", $r_Fine['Article'] . $Paragraph . " " . $Letter, $Content);

            /////////////////////////////////////////////
            //Article Owner
            /////////////////////////////////////////////
            $rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
            $r_FineOwner = mysqli_fetch_array($rs_FineOwner);

            $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
            $Content = str_replace("{ArticleDescription}", $str_ArticleDescription, $Content);

            $str_ControllerName = trim($r_Fine['ControllerQualification']." ".$r_Fine['ControllerName']);
            $str_ControllerCode = trim($r_Fine['ControllerCode']);
            /////////////////////////////////////////////
            //Additional controller
            /////////////////////////////////////////////

            $rs_FineAdditionalController = $rs->Select('V_AdditionalController', "FineId=" . $FineId);

            while ($r_FineAdditionalController = mysqli_fetch_array($rs_FineAdditionalController)){
                $str_ControllerCode="";
                $str_ControllerName .= ", ".trim($r_FineAdditionalController['ControllerQualification']." ".$r_FineAdditionalController['ControllerName']);
            }



            $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ReasonTitle' . $a_Lan[$n_LanguageId]];
            $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);

            if ($SpeedLengthAverage > 0) {
                $SpeedTimeAverage = $SpeedLengthAverage * 3.6 / $r_Fine['SpeedControl'];

                $Content = str_replace("{SpeedTimeAverage}", NumberDisplay($SpeedTimeAverage), $Content);
                $Content = str_replace("{SpeedLengthAverage}", $SpeedLengthAverage, $Content);
            }
            /////////////////////////////////////////////
            //126 BIS
            /////////////////////////////////////////////
            $Content = str_replace("{PreviousProtocolId}", $PreviousProtocolId, $Content);
            $Content = str_replace("{PreviousProtocolYear}", $PreviousProtocolYear, $Content);
            $Content = str_replace("{PreviousFineDate}", $PreviousFineDate, $Content);

            $Content = str_replace("{Speed}", $r_Fine['Speed'], $Content);
            $Content = str_replace("{SpeedLimit}", NumberDisplay($r_Fine['SpeedLimit']), $Content);
            $Content = str_replace("{SpeedControl}", NumberDisplay($r_Fine['SpeedControl']), $Content);

            $Content = str_replace("{Locality}", $r_Fine['CityTitle'], $Content);
            $Content = str_replace("{Address}", $r_Fine['Address'], $Content);

            $Content = str_replace("{DetectorId}", $DetectorTitle, $Content);

            $Content = str_replace("{BankOwner}", $r_Customer['ForeignBankOwner'], $Content);
            $Content = str_replace("{BankName}", $r_Customer['ForeignBankName'], $Content);
            $Content = str_replace("{BankAccount}", $r_Customer['ForeignBankAccount'], $Content);
            $Content = str_replace("{BankSwift}", $r_Customer['ForeignBankSwift'], $Content);
            $Content = str_replace("{BankIban}", $r_Customer['ForeignBankIban'], $Content);

            $Content = str_replace("{Fee}", NumberDisplay($Fee), $Content);
            $Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);


            $PartialFee = number_format($Fee * FINE_PARTIAL, 2);

            $TotalDiscountFee = $PartialFee + $ResearchFee + $NotificationFee;

            $Content = str_replace("{TotalDiscountFee}", NumberDisplay($TotalDiscountFee), $Content);

            $Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
            $Content = str_replace("{ResearchFee}", NumberDisplay($ResearchFee), $Content);
            $Content = str_replace("{NotificatioFee}", NumberDisplay($NotificationFee), $Content);
            $Content = str_replace("{ChargeTotalFee}", NumberDisplay($ChargeTotalFee), $Content);

            $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
            $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);

            $Content = str_replace("{ChiefControllerName}", $controller['Name'], $Content);
            $Content = str_replace("{AdditionalSanction}", $str_AdditionalNight . $str_AdditionalMass, $Content);

            $Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);


        if($r_Fine['TrespasserTypeId']==11){
                $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=".$FineId." AND TrespasserTypeId=10");
                $r_trespasser = mysqli_fetch_array($rs_trespasser);

                $Content = str_replace("{TrespasserName}",$r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name'],$Content);

                $Content = str_replace("{TrespasserAddress}",$trespasser['Address']." ".$trespasser['ZIP'].' '.$trespasser['City'],$Content);
                $Content = str_replace("{TrespasserCountry}",$trespasser['CountryTitle'],$Content);

        } else {

                if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {
                    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=2");
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);
                    $Content = str_replace("{TrespasserName}",$r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name'],$Content);

                    if (strlen($r_trespasser['BornDate'])==10){
                        $Content = str_replace("{TrespasserBornDate}",DateOutDB($r_trespasser['BornDate']),$Content);
                    }else{
                        $Content = str_replace("{TrespasserBornDate}","",$Content);
                    }
                    $Content = str_replace("{TrespasserAddress}",$r_trespasser['Address']." ".$r_trespasser['ZIP'].' '.$r_trespasser['City'],$Content);
                    $Content = str_replace("{TrespasserCountry}",$r_trespasser['CountryTitle'],$Content);

                } else {

                    $Content = str_replace("{TrespasserName}",$trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name'],$Content);

                    if (strlen($trespasser['BornDate'])==10){
                        $Content = str_replace("{TrespasserBornDate}",DateOutDB($trespasser['BornDate']),$Content);
                    }else{
                        $Content = str_replace("{TrespasserBornDate}","",$Content);
                    }
                    $Content = str_replace("{TrespasserAddress}",$trespasser['Address']." ".$trespasser['ZIP'].' '.$trespasser['City'],$Content);
                    $Content = str_replace("{TrespasserCountry}",$trespasser['CountryTitle'],$Content);

                }

            }

            $offices = $rs->Select('V_JudicialOffice', "CityId='" . $_SESSION['cityid'] . "'");
            while ($office = mysqli_fetch_array($offices)) {
                if ($office['OfficeId'] == 1) {
                    $Content = str_replace("{Judge}", $office['OfficeTitle' . $a_Lan[$n_LanguageId]], $Content);
                    $Content = str_replace("{JudgeCity}", $office['City'], $Content);
                    $Content = str_replace("{JudgeProvince}", $office['Province'], $Content);
                }
                if ($office['OfficeId'] == 2) {
                    $Content = str_replace("{Court}", $office['OfficeTitle' . $a_Lan[$n_LanguageId]], $Content);
                    $Content = str_replace("{CourtCity}", $office['City'], $Content);
                    $Content = str_replace("{CourtProvince}", $office['Province'], $Content);

                }

            }

            $Content = str_replace("{Code}", $r_Fine['Code'], $Content);
            $Content = str_replace("{ProtocolId}", $r_Fine['ProtocolId'], $Content);

            $aMainPart = explode("<main_part>", $Content);
            $aRow = explode("<row>", $aMainPart[1]);



            $a_Flow = array(
                "TIPOLOGIA_STAMPA"          => "Raccomandata AR",
                "TIPOLOGIA_ATTO"            => "VERBALI_EST",
                "TIPOLOGIA_FLUSSO"          => $FormTypeId,
                "CodiceComune"              => $_SESSION['cityid'],

                "HeaderRow1"                => $r_Customer['ManagerName'],
                "HeaderRow1_RichiestaDati"  => $r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName'],
                "HeaderRow2"                => $ManagerSubject,
                "HeaderRow3"                => $r_Customer['ManagerAddress'],
                "HeaderRow4"                => $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")",
                "HeaderRow5"                => $r_Customer['ManagerCountry'],
                "HeaderRow6"                => $r_Customer['ManagerMail'],

                "Spese_Anticipate"          => $str_SmaPayment,
                "Intestatario_SMA"          => $str_SmaName,
                "Numero_SMA"                => $str_SmaAuthorization,

                "Mod23_Soggetto_Mittente"   => $str_Mod23LSubject,
                "Mod23_Ente_Gestito"        => $str_Mod23LCustomerName,
                "Mod23_Recapito_Soggetto"   => $str_Mod23LCustomerSubject,
                "Mod23_Indirizzo_Soggetto"  => $str_Mod23LCustomerAddress,
                "Mod23_Citta_Soggetto"      => $str_Mod23LCustomerCity,
            );


            $a_Flow["Richiesta_Dati"] = ($chk_126Bis) ? "SI" : "NO";



            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
            );

            //Arial 9 minuscolo
            $a_Flow["Page1_Row1"] = trim($aRow[1]);
            $a_Flow["Page1_Row2"] = ($r_Customer['PDFRefPrint'] == 1) ? trim($aRow[2]) : '';

            $a_Flow["Recipient_Row1"] = $trespasser['CompanyName'] . " " . $trespasser['Surname'] . " " . $trespasser['Name'];
            $a_Flow["Recipient_Row2"] = $str_TrespasserAddress;//L
            $a_Flow["Recipient_Row3"] = $trespasser['ZIP'] . ' ' . $trespasser['City'];
            $a_Flow["Recipient_Row4"] = strtoupper($trespasser['CountryTitle']);

            $a_Flow["Page1_Row3"] = trim($aRow[3]);//C
            $a_Flow["Page1_Row4"] = trim($aRow[4]);//C
            $a_Flow["Page1_Row5"] = trim($aRow[5]);//C

            $a_Flow["Page1_Row6"] = trim($aRow[6]);//C
            $a_Flow["Page1_Row7"] = trim($aRow[7]);//C

            for ($i = 8; $i < 21; $i++) {
                $aCol = explode("<col>", trim($aRow[$i]));

                if ($i == 18) {
                    if($r_Fine['TrespasserTypeId']==11){

                        $aCol[0] = $a_Rent[$n_LanguageId];
                        $aCol[1] = $trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name'];

                    }else if($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16){
                        $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=3");
                        $r_trespasser = mysqli_fetch_array($rs_trespasser);

                        $aCol[0] = $a_Rent[$n_LanguageId];
                        $aCol[1] = $r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name'];
                    }
                }


                if (isset($aCol[1]) && !(trim($aCol[1]) == "0" || trim($aCol[1]) == "" || trim($aCol[1]) == "//")) {

                    $a_Flow["Page1_Row" . $i . "_Col1"] = $aCol[0];
                    $a_Flow["Page1_Row" . $i . "_Col2"] = $aCol[1];
                    if ($i == 13) {
                        if ($r_Fine['Speed'] > 0)
                            $a_Flow["Page1_Row" . $i . "_Col3"] = $aCol[2];
                        else
                            $a_Flow["Page1_Row" . $i . "_Col3"] = "";
                    }
                } else {
                    $a_Flow["Page1_Row" . $i . "_Col1"] = "";
                    $a_Flow["Page1_Row" . $i . "_Col2"] = "";
                }
            }

            $a_Flow["Page1_Row21"] = trim($aRow[21]);//C
            $a_Flow["Page1_Row22"] = trim($aRow[22]);//C
            $a_Flow["Page1_Row23"] = trim($aRow[23]);//C
            if ($r_ArticleTariff['ReducedPayment']) {
                $a_Flow["Page1_Row24"] = trim($aRow[24]);//C
            } else $a_Flow["Page1_Row24"] = "";

            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            ////
            ////
            ////    Fine page 2
            ////
            ////
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            $aRow[25] = str_replace("</u>", "", trim($aRow[25]));
            $aCol = explode("<u>", $aRow[25]);
            $a_Flow["Page2_Row1_Col1"] = trim($aCol[0]);
            $a_Flow["Page2_Row1_Col2"] = trim($aCol[1]);

            $a_Flow["Page2_Row2"] = $aRow[26];

            if ($chk_126Bis) {
                $query = "Select ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId ";
                $query .= "WHERE A.CityId='" . $_SESSION['cityid'] . "' AND A.Article=126 AND A.Letter='bis' AND ART.Year = " . $_SESSION['year'];

                $articles126bis = $rs->SelectQuery($query);
                $article126bis = mysqli_fetch_array($articles126bis);

                $aRow[27] = str_replace("{DecurtationPoints}", $r_ArticleTariff['LicensePoint'], $aRow[27]);
                $aRow[27] = str_replace("{Fee126bis}", NumberDisplay($article126bis['Fee']), $aRow[27]);
                $aRow[27] = str_replace("{MaxFee126bis}", NumberDisplay($article126bis['MaxFee']), $aRow[27]);
                $a_Flow["Page2_Row3"] = $aRow[27];
            } else
                $a_Flow["Page2_Row3"] = "";

//        echo "<br>".$a_Flow["Page2_Row3"]."<br>";

            $aRow[28] = str_replace("<i>", "", trim($aRow[28]));
            $aCol = explode("</i>", $aRow[28]);
            $a_Flow["Page2_Row4_Col1"] = trim($aCol[0]);
            $a_Flow["Page2_Row4_Col2"] = (isset($aCol[1])) ? trim($aCol[1]) : "";



            $a_Flow["Page2_Row6"] = $r_Customer['ManagerName'] . ", " . date("d/m/Y");


            if($_SESSION['cityid']=='I138'){
                $a_Flow["Page2_Row10"] = trim($aRow[30]);
                $a_Flow["Page2_Row7"] = trim($aRow[29]);
                $a_Flow["Page2_Row8"] = "";
                $a_Flow["Page2_Row9"] = "";
            }else{
                if ($r_ArticleTariff['ReducedPayment']) {
                    $aRow[29] = str_replace("<i>", "", trim($aRow[29]));
                    $aCol = explode("</i>", $aRow[29]);
                    $a_Flow["Page2_Row5_Col1"] = trim($aCol[0]);
                    $a_Flow["Page2_Row5_Col2"] = (isset($aCol[1])) ? trim($aCol[1]) : "";
                } else {
                    $a_Flow["Page2_Row5_Col1"] = "";
                    $a_Flow["Page2_Row5_Col2"] = "";
                }
                //gestione firma con due righe
                if (!empty($aRow[34])){
                    $a_Flow["Page2_Row7"] = trim($aRow[30]);
                    $a_Flow["Page2_Row8"] = trim($aRow[31]);
                    $a_Flow["Page2_Row9"] = trim($aRow[32]);
                    $a_Flow["Page2_Row10"] = trim($aRow[33]).":".trim($aRow[34]);
                } else {
                    if (strlen($controller['Name']) > 0) {
                        $a_Flow["Page2_Row7"] = trim($aRow[30]);
                        $a_Flow["Page2_Row8"] = trim($aRow[31]);
                        $a_Flow["Page2_Row9"] = trim($aRow[32]);
                        
                    } else {
                        $a_Flow["Page2_Row7"] = "";
                        $a_Flow["Page2_Row8"] = "";
                        $a_Flow["Page2_Row9"] = "";
                        $a_Flow["Page2_Row10"] = "";
                    }
                }
               
            }


            if ($r_ArticleTariff['LicensePoint'] > 0) {
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                ////
                ////
                ////    LicensePoint page 1
                ////
                ////
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                $aRow = explode("<row>", $aMainPart[2]);

                //Arial 10 minuscolo
                $aRow[1] = $ManagerSubject;
                $a_Flow["Page3_Row1"] = $aRow[1];//C
                $a_Flow["Page3_Row2"] = strtoupper($aRow[2]);//C
                $a_Flow["Page3_Row3"] = $aRow[3];//C

                //Arial 9 minuscolo
                $aCol = explode("<col>", $aRow[4]);
                $a_Flow["Page3_Row4_Col1"] = strtoupper($aCol[0]);//L
                $a_Flow["Page3_Row4_Col2"] = $aCol[1];//J
                $a_Flow["Page3_Row4_Col3"] = strtoupper($aCol[2]);//J

                $a_Flow["Page3_Row5"] = $aRow[5];//L
                $a_Flow["Page3_Row6"] = $aRow[6];//L
                $a_Flow["Page3_Row7"] = $aRow[7];//L
                $a_Flow["Page3_Row8"] = $aRow[8];//L
                $a_Flow["Page3_Row9"] = $aRow[9];//L
                $a_Flow["Page3_Row10"] = $aRow[10];//L
                $a_Flow["Page3_Row11"] = $aRow[11];//L
                $a_Flow["Page3_Row12"] = $aRow[12];//L
                $a_Flow["Page3_Row13"] = $aRow[13];//L
                $a_Flow["Page3_Row14"] = $aRow[14];//L
//            $a_Flow["Page3_Row15"] = $aRow[15];//J
                $a_Flow["Page3_Row15"] = $aRow[15];//J
                $a_Flow["Page3_Row16"] = $aRow[16];//J
                $a_Flow["Page3_Row17"] = $aRow[17];//J

                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                ////
                ////
                ////    LicensePoint page 2
                ////
                ////
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                $aRow = explode("<row>", $aMainPart[3]);

                //Arial 9 minuscolo
                $a_Flow["Page4_Row1"] = strtoupper($aRow[1]);//J
                $a_Flow["Page4_Row2"] = $aRow[2];//J
                $a_Flow["Page4_Row3"] = $aRow[3];//J
                $a_Flow["Page4_Row4"] = strtoupper($aRow[4]);//J
                $a_Flow["Page4_Row5"] = $aRow[5];//J
                $a_Flow["Page4_Row6"] = $aRow[6];//J
                $a_Flow["Page4_Row7"] = $aRow[7];//J
                $a_Flow["Page4_Row8"] = $aRow[8];//J
                $a_Flow["Page4_Row9"] = $aRow[9];//J
                $a_Flow["Page4_Row10"] = $aRow[10];//J
            } else {
                $a_Flow["Page3_Row1"] = "";//C
                $a_Flow["Page3_Row2"] = "";//C
                $a_Flow["Page3_Row3"] = "";//C

                $a_Flow["Page3_Row4_Col1"] = "";//L
                $a_Flow["Page3_Row4_Col2"] = "";//J
                $a_Flow["Page3_Row4_Col3"] = "";//J
                $a_Flow["Page3_Row5"] = "";//L
                $a_Flow["Page3_Row6"] = "";//L
                $a_Flow["Page3_Row7"] = "";//L
                $a_Flow["Page3_Row8"] = "";//L
                $a_Flow["Page3_Row9"] = "";//L
                $a_Flow["Page3_Row10"] = "";//L
                $a_Flow["Page3_Row11"] = "";//L
                $a_Flow["Page3_Row12"] = "";//L
                $a_Flow["Page3_Row13"] = "";//L
                $a_Flow["Page3_Row14"] = "";//L
                $a_Flow["Page3_Row15"] = "";//L
                $a_Flow["Page3_Row16"] = "";//L
                $a_Flow["Page3_Row17"] = "";//J

                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                ////
                ////
                ////    LicensePoint page 2
                ////
                ////
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                $a_Flow["Page4_Row1"] = "";//J
                $a_Flow["Page4_Row2"] = "";//J
                $a_Flow["Page4_Row3"] = "";//J
                $a_Flow["Page4_Row4"] = "";//J
                $a_Flow["Page4_Row5"] = "";//J
                $a_Flow["Page4_Row6"] = "";//J
                $a_Flow["Page4_Row7"] = "";//J
                $a_Flow["Page4_Row8"] = "";//J
                $a_Flow["Page4_Row9"] = "";//J
                $a_Flow["Page4_Row10"] = "";//J
            }
            if (isset($a_CountryBank[$trespasser['CountryId']])) {


                $a_BankField = $a_CountryBank[$trespasser['CountryId']];


                $a_Flow["BankOwner"] = $a_BankField['BankOwner'];
                $a_Flow["BankAccount"] = $a_BankField['BankAccount'];


                //01000000000000164382	110	66	0100000110665>101118010000000000001643826+ 010013534>
                //FifthField		FullCode
                $ForeignSum = number_format(($TotalFee * 1.18), 2);
                $a_IntFloat = explode(".", $ForeignSum);
                $BankAmount = $a_IntFloat[0];
                $BankDecimal = $a_IntFloat[1];


                $a_Flow["BankAmount"] = $BankAmount;
                $a_Flow["BankDecimal"] = $BankDecimal;

                $str_FirsPart = "01";

                $tmp_ControllerCode = $BankAmount . $BankDecimal;

                $int_Count = 10 - strlen($tmp_ControllerCode);
                for ($i = 0; $i < $int_Count; $i++) {
                    $tmp_ControllerCode = "0" . $tmp_ControllerCode;
                }
                $str_FirsPart .= $tmp_ControllerCode;
                $str_FirsPart .= GET_ControllerBankCode($str_FirsPart);

                $str_FirsPart .= ">";

                $str_SecondPart = "101118";
                $str_SecondPart .= "01";

                $tmp_ControllerCode = $FineId;
                $int_Count = 18 - strlen($tmp_ControllerCode);

                for ($i = 0; $i < $int_Count; $i++) {
                    $tmp_ControllerCode = "0" . $tmp_ControllerCode;
                }
                $str_SecondPart .= $tmp_ControllerCode;
                $str_SecondPart .= GET_ControllerBankCode($str_SecondPart);

                $a_Flow["FifthField"] = $str_SecondPart;

                $str_FullCode = $str_FirsPart . $str_SecondPart . "+ 010013534>";
                $a_Flow["FullCode"] = $str_FullCode;




            }
            $a_Flow["NOME_FLUSSO"] = $DocumentationZip;

            if ($checkFlowHeader == 0) {
                foreach ($a_Flow as $key => $value) {
                    fwrite($myfile, $key . Chr(9));  //  TAB
                }
                fwrite($myfile, Chr(13) . Chr(10));  //  fine riga
                $checkFlowHeader = 1;
            }

            foreach ($a_Flow as $value) {
                fwrite($myfile, trim($value) . Chr(9));  //  TAB
            }
            fwrite($myfile, Chr(13) . Chr(10));  //  fine riga
            $a_Flow = null;

            if ($ultimate) {
                $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $r_Fine['Id']." AND NotificationTypeId=".$NotificationTypeId);

                if(mysqli_num_rows($rs_FineHistory)==0){
                    $aInsert = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['Id'], 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                        array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $controller['Id'], 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentationZip),
                        array('field' => 'FlowId', 'selector' => 'value', 'type' => 'int', 'value' => $FlowId, 'settype' => 'int'),
                    );
                    $rs->Insert('FineHistory', $aInsert);

                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int')
                    );
                    $rs->Update('Fine', $aUpdate, 'Id=' . $r_Fine['Id']);
                }

            }

        }
    }
    fclose($myfile);

    $zip = new ZipArchive();
    if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFile($path.$Documentation,$Documentation);
        $zip->addFile($_SESSION['blazon'],'blazon.png');
        if($controller['Sign'] != "" && file_exists(ROOT."/img/sign/".$_SESSION['cityid']."/".$controller['Sign'])){

            $zip->addFile(ROOT . "/img/sign/" . $_SESSION['cityid'] . "/" . $controller['Sign'], $controller['Sign']);

        }

        $zip->close();
        $_SESSION['Documentation'] = FOREIGN_FLOW_HTML.'/'.$_SESSION['cityid'].'/'.$DocumentationZip;
    } else {
        $str_Warning .= "Errore nella creazione dell\'archivio del flusso.<br>";
    }

    if($ultimate){

        $a_Flow = array(
            array('field'=>'Zone1Number','selector'=>'value','type'=>'int','value'=>$zoneSum[1],'settype'=>'int'),
            array('field'=>'Zone2Number','selector'=>'value','type'=>'int','value'=>$zoneSum[2],'settype'=>'int'),
            array('field'=>'Zone3Number','selector'=>'value','type'=>'int','value'=>$zoneSum[3],'settype'=>'int')
        );

        $rs->Update('Flow', $a_Flow, 'Id=' . $FlowId);
        
        //Se il verbale è da inviare ad uno stampatore, chiama una funzione specifica definita nel parametri di configurazione
        //passandogli i riferimenti al file del flusso come parametri
        if($a_PrinterConf && PRODUCTION){
            if(!$phpFTP->connect()){
                $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
                header("location: ".$P);
                DIE;
            } else {
                //Riferimenti zip flusso
                $a_Flow = array(
                    'LocalFile' => $path.$DocumentationZip,
                    'RemoteFile' => isset($a_PrinterConf['Path']['VERBALI'])
                    ? $a_PrinterConf['Path']['VERBALI'].'/'.$DocumentationZip
                    : $DocumentationZip
                );
                
                if(call_user_func_array($a_PrinterConf['Function'], array($phpFTP, $a_Flow))){
                    $a_UpdateFlow = array(
                        array('field'=>'UploadDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'))
                    );
                    
                    $rs->Update('Flow', $a_UpdateFlow, "Id=$FlowId");
                    
                    $str_Success .= "Flusso caricato con successo.<br>";
                } else {
                    $str_Warning .= 'Errore nell\'invio del flusso allo stampatore:<br>'.implode('<br>', $phpFTP->errors());
                }
                
                $phpFTP->disconnect();
            }
        }
    }
    
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = $str_Warning;
    } else if ($ultimate) {
        $str_Success .= 'Azione eseguita con successo.';
        $_SESSION['Message']['Success'] = $str_Success;
    }
    
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $rs->End_Transaction();
}