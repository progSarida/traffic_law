<?php
include(INC."/function_postalCharge.php");


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$n_LanguageId = 1;

$rs_FineHistory = $rs->Select('V_FineHistory', "Id=" . $FineId . " AND NotificationTypeId=2");
$r_FineHistory = mysqli_fetch_array($rs_FineHistory);



$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}


$ProtocolYear = $r_FineHistory['ProtocolYear'];
$ProtocolNumber = $r_FineHistory['ProtocolId'];

$StatusTypeId = 15;
$NotificationTypeId = 2;


$NotificationDate = $r_FineHistory['NotificationDate'];
$ResearchFee = $r_FineHistory['CustomerResearchFee'] + $r_FineHistory['ResearchFee'];


$a_Lan = unserialize(LANGUAGE);


$str_Speed = "";


$a_DocumentationFineZip = array();
$a_FineId = array();

$a_Detector = array();
$a_SpeedLengthAverage = array();
$a_GenreLetter = array("D" => "Spett.le", "M" => "Sig.", "F" => "Sig.ra");


$str_Detector = "
    Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. 
    La velocita' rilevata dall'apparecchiatura elettronica e' stata di {SpeedControl} Km/h e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), 
    ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h. 
    L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e 
    precisamente {Kind} matricola {Code} - {Ratification}, sul quale sono state effettuate 
    le previste verifiche preventive indicate dal manuale d'uso e dal decreto di omologazione.
";

$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0");
while ($r_Detector = mysqli_fetch_array($rs_Detector)) {
    $str = str_replace("{Kind}", $r_Detector['Kind'], $str_Detector);
    $str = str_replace("{Code}", $r_Detector['Code'], $str);
    $str = str_replace("{Tolerance}", intval($r_Detector['Tolerance']), $str);
    $str = str_replace("{Ratification}", $r_Detector['Ratification'], $str);

    $a_Detector[$r_Detector['Id']] = $str;
    $a_SpeedLengthAverage[$r_Detector['Id']] = $r_Detector['SpeedLengthAverage'];

}


$controllers = $rs->Select('Controller', "CityId='" . $r_FineHistory['CityId'] . "' AND Id=" . $r_FineHistory['FineControllerId']);
$controller = mysqli_fetch_array($controllers);


$ChiefControllerName = $controller['Name'];


$pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);

$pdf->TemporaryPrint = 1;
$pdf->NationalFine = 1;
$pdf->CustomerFooter = 0;


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('Violation');
$pdf->SetSubject('');
$pdf->SetKeywords('');
$pdf->setHeaderFont(array('helvetica', '', 8));
$pdf->setFooterFont(array('helvetica', '', 8));

$pdf->SetMargins(10, 10, 10);



$NotificationFee = 0;


$NotificationFee = $r_FineHistory['NotificationFee'];




$ManagerName = $r_Customer['ManagerName'];
$pdf->customer = $ManagerName;



$rs_Fine = $rs->Select('V_FineArticle', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND Id=" . $FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);
$ViolationTypeId = $r_Fine['ViolationTypeId'];

$NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType1'];
$NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType2'];

$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
$r_RuleType = mysqli_fetch_array($rs_RuleType);


$RuleTypeId = $r_RuleType['Id'];

$ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
$FormTypeId = $r_RuleType['NationalFormId'];

$a_PrintObject = explode("*",$r_RuleType['PrintObject'.$a_Lan[$n_LanguageId]]);



$pdf->Temporary();
$pdf->RightHeader = true;
$pdf->PrintObject1 = $a_PrintObject[0];
$pdf->PrintObject2 = $a_PrintObject[1];
$pdf->PrintObject3 = $a_PrintObject[2];
$pdf->AddPage();

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

$pdf->SetFont('helvetica', '', 10, '', true);


if (strlen($r_Customer['ManagerName']) > 22) {
    $pdf->writeHTMLCell(60, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
    $pdf->LN(10);

} else {

    $pdf->writeHTMLCell(60, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
    $pdf->LN(5);
}


$pdf->writeHTMLCell(60, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
//        $pdf->Line(7, 34, 200, 34);
$pdf->LN(10);





$Fee = $r_FineHistory['Fee'];
$MaxFee = $r_FineHistory['MaxFee'];

$rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);


$str_AdditionalNight = "";
if($r_ArticleTariff['AdditionalNight']==1){

    $a_Time = explode(":",$r_Fine['FineTime']);

    if($a_Time[0]<FINE_HOUR_START_DAY ||  ($a_Time[0]>FINE_HOUR_END_DAY) || ($a_Time[0]==FINE_HOUR_END_DAY && $a_Time[1]!="00")){
        $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId];
    }
}
$str_AdditionalMass = "";
if($r_ArticleTariff['AdditionalMass']==1){
    if($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
}

$trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
$trespasser = mysqli_fetch_array($trespassers);



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


$ZoneId = 0;

$postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);

if ($NotificationFee == 0) {
    $NotificationFee = $postalcharge['Zone' . $ZoneId];
}

$AdditionalFee = $NotificationFee + $ResearchFee;


$AdditionalFeeCAN = $AdditionalFee + $postalcharge['CanFee'];
$AdditionalFeeCAD = $AdditionalFee + $postalcharge['CadFee'];


$r_FineTime = $r_FineHistory['FineTime'];
$SpeedLengthAverage = 0;
if ($r_FineHistory['Speed'] > 0) {

    $str_Speed = $a_Detector[$r_Fine['DetectorId']];
    $str_Speed = str_replace("{Speed}", intval($r_Fine['Speed']), $str_Speed);
    $str_Speed = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_Speed);
    $str_Speed = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_Speed);

    $str_Speed = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_Speed);
    $SpeedLengthAverage = $a_SpeedLengthAverage[$r_Fine['DetectorId']];

}


$forms = $rs->Select('Form', "FormTypeId=" . $FormTypeId . " AND CityId='" . $r_FineHistory['CityId'] . "' AND LanguageId=" . $n_LanguageId);
$form = mysqli_fetch_array($forms);
$str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
$Content = $form['Content'];


if ($r_ArticleTariff['AdditionalSanctionId'] > 0) {
    $rs_AdditionalSanction = $rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']);
    $r_AdditionalSanction = mysqli_fetch_array($rs_AdditionalSanction);

    $Content = str_replace("{AdditionalSanctionId}", "SANZIONE ACCESSORIA: " . $r_AdditionalSanction['Title' . $a_Lan[$n_LanguageId]], $Content);

} else {
    $Content = str_replace("{AdditionalSanctionId}", "", $Content);
}


$Content = str_replace("{FineDate}", DateOutDB($r_FineHistory['FineDate']), $Content);
$Content = str_replace("{FineTime}", TimeOutDB($r_FineTime), $Content);
$Content = str_replace("{VehicleTypeId}", $r_FineHistory['VehicleTitle' . $a_Lan[$n_LanguageId]], $Content);
$Content = str_replace("{VehiclePlate}", $r_FineHistory['VehiclePlate'], $Content);

$Content = str_replace("{VehicleBrand}", $r_FineHistory['VehicleBrand'], $Content);
$Content = str_replace("{VehicleModel}", $r_FineHistory['VehicleModel'], $Content);
$Content = str_replace("{VehicleColor}", $r_FineHistory['VehicleColor'], $Content);

$Paragraph = ($r_Fine['Paragraph'] == "0") ? "" : $r_FineHistory['Paragraph'];
$Letter = ($r_Fine['Letter'] == "0") ? "" : $r_FineHistory['Letter'];

$Content = str_replace("{ArticleId}", $r_FineHistory['Article'] . "/" . $Paragraph . " " . $Letter, $Content);


/////////////////////////////////////////////
//Article Owner
/////////////////////////////////////////////
$rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);

$str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
$str_ArticleDescription .= $str_Speed.$str_AdditionalNight.$str_AdditionalMass;
$Content = str_replace("{ArticleDescription}", $str_ArticleDescription, $Content);

$str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ReasonDescription' . $a_Lan[$n_LanguageId]];
$Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);

$Content = str_replace("{ArticleAdditionalText}", $r_Fine['ArticleAdditionalText' . $a_Lan[$n_LanguageId]], $Content);

if($SpeedLengthAverage>0) {
    $SpeedTimeAverage = $SpeedLengthAverage*3.6/$r_Fine['SpeedControl'];

    $Content = str_replace("{SpeedTimeAverage}", NumberDisplay($SpeedTimeAverage), $Content);
    $Content = str_replace("{SpeedLengthAverage}", $SpeedLengthAverage, $Content);
}
/////////////////////////////////////////////
//126 BIS
/////////////////////////////////////////////
$Content = str_replace("{PreviousProtocolId}", $PreviousProtocolId, $Content);
$Content = str_replace("{PreviousProtocolYear}", $PreviousProtocolYear, $Content);
$Content = str_replace("{PreviousFineDate}", $PreviousFineDate, $Content);


$Content = str_replace("{Locality}", $r_FineHistory['CityTitle'], $Content);
$Content = str_replace("{Address}", $r_FineHistory['Address'], $Content);

$Content = str_replace("{BankOwner}", $r_Customer['NationalBankOwner'], $Content);
$Content = str_replace("{BankName}", $r_Customer['NationalBankName'], $Content);
$Content = str_replace("{BankAccount}", $r_Customer['NationalBankAccount'], $Content);
$Content = str_replace("{BankSwift}", $r_Customer['NationalBankSwift'], $Content);
$Content = str_replace("{BankIban}", $r_Customer['NationalBankIban'], $Content);


$Content = str_replace("{CurrentDate}", DateOutDB($NotificationDate), $Content);


if (is_null($r_Customer['ManagerSignName'])) {
    if ($r_Customer['CityUnion'] > 1) {
        $Content = str_replace("{Date}", $r_FineHistory['CityTitle'] . ", " . DateOutDB($NotificationDate), $Content);
    } else {
        $Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . DateOutDB($NotificationDate), $Content);
    }
} else {
    $Content = str_replace("{Date}", $r_Customer['ManagerSignName'] . ", " . DateOutDB($NotificationDate), $Content);
}

$Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . date("d/m/Y"), $Content);

$Content = str_replace("{ManagerDataEntryName}", $r_Customer['ManagerDataEntryName'], $Content);
$Content = str_replace("{ManagerProcessName}", $r_Customer['ManagerProcessName'], $Content);

$Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
$Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);

$PartialFee = $Fee * FINE_PARTIAL;
$MaxFee = $MaxFee * FINE_MAX;

$TotalPartialFee = $PartialFee + $AdditionalFee;
$TotalPartialFeeCAN = $PartialFee + $AdditionalFeeCAN;
$TotalPartialFeeCAD = $PartialFee + $AdditionalFeeCAD;


$TotalFee = $Fee + $AdditionalFee;
$TotalFeeCAN = $Fee + $AdditionalFeeCAN;
$TotalFeeCAD = $Fee + $AdditionalFeeCAD;

$TotalMaxFee = $MaxFee + $AdditionalFee;
$TotalMaxFeeCAN = $MaxFee + $AdditionalFeeCAN;
$TotalMaxFeeCAD = $MaxFee + $AdditionalFeeCAD;


$Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
$Content = str_replace("{TotalPartialFee}", NumberDisplay($TotalPartialFee), $Content);
$Content = str_replace("{TotalPartialFeeCAN}", NumberDisplay($TotalPartialFeeCAN), $Content);
$Content = str_replace("{TotalPartialFeeCAD}", NumberDisplay($TotalPartialFeeCAD), $Content);

$Content = str_replace("{Fee}", NumberDisplay($Fee), $Content);
$Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);
$Content = str_replace("{TotalFeeCAN}", NumberDisplay($TotalFeeCAN), $Content);
$Content = str_replace("{TotalFeeCAD}", NumberDisplay($TotalFeeCAD), $Content);

$Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
$Content = str_replace("{TotalMaxFee}", NumberDisplay($TotalMaxFee), $Content);
$Content = str_replace("{TotalMaxFeeCAN}", NumberDisplay($TotalMaxFeeCAD), $Content);
$Content = str_replace("{TotalMaxFeeCAN}", NumberDisplay($TotalMaxFeeCAD), $Content);

$Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content);
$Content = str_replace("{AdditionalFeeCAN}", NumberDisplay($AdditionalFeeCAN), $Content);
$Content = str_replace("{AdditionalFeeCAD}", NumberDisplay($AdditionalFeeCAD), $Content);

$Content = str_replace("{ControllerName}", $r_Fine['ControllerName'], $Content);
$Content = str_replace("{ControllerCode}", $r_Fine['ControllerCode'], $Content);


$Content = str_replace("{ChiefControllerName}", $ChiefControllerName, $Content);

$Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
$Content = str_replace("{TrespasserCity}", $trespasser['City'], $Content);
$Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);

if (!((strlen($trespasser['BornDate']) != 10) || (is_null($trespasser['BornDate'])))) {
    $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
} else {
    $Content = str_replace("il {TrespasserBornDate}", "", $Content);
}

if (strlen(trim($trespasser['BornPlace'])) > 0) {
    $Content = str_replace("{TrespasserBornCity}", $trespasser['BornPlace'], $Content);

} else {
    $Content = str_replace("Nato/a a {TrespasserBornCity}", "", $Content);
}

$Content = str_replace("{TrespasserAddress}", $trespasser['Address'] . " " . $trespasser['ZIP'], $Content);
$Content = str_replace("{TrespasserCountry}", $trespasser['CountryTitle'], $Content);

if ($trespasser['Genre'] == "D") {
    $Content = str_replace("{TrespasserC}", $trespasser['CompanyName'], $Content);
    $Content = str_replace("{TrespasserCC}", $trespasser['City'] . "(" . $trespasser['Province'] . ")", $Content);
    $Content = str_replace("{TrespasserCA}", $trespasser['Address'], $Content);

    $Content = str_replace("{TrespasserN}", "___________________", $Content);
    $Content = str_replace("{TrespasserS}", "___________________", $Content);
    $Content = str_replace("{TrespasserBC}", "_____________________", $Content);
    $Content = str_replace("{TrespasserBD}", "_________________", $Content);
    $Content = str_replace("{TrespasserR}", "____________________________________________", $Content);
    $Content = str_replace("{TrespasserP}", "____", $Content);

} else {
    $Content = str_replace("{TrespasserN}", $trespasser['Name'], $Content);
    $Content = str_replace("{TrespasserS}", $trespasser['Surname'], $Content);

    if (strlen(trim($trespasser['BornPlace'])) > 0) {
        $Content = str_replace("{TrespasserBC}", $trespasser['BornPlace'], $Content);
    } else {
        $Content = str_replace("{TrespasserBC}", "____________________", $Content);
    }

    if (strlen(trim($trespasser['BornPlace'])) > 0 && !((strlen($trespasser['BornDate']) != 10) || (is_null($trespasser['BornDate'])))) {
        $Content = str_replace("{TrespasserBD}", DateOutDB($trespasser['BornDate']), $Content);
    } else {
        $Content = str_replace("{TrespasserBD}", "________________", $Content);
    }

    $Content = str_replace("{TrespasserR}", $trespasser['City'] . " " . $trespasser['Address'], $Content);
    $Content = str_replace("{TrespasserP}", $trespasser['Province'], $Content);

    $Content = str_replace("{TrespasserC}", "____________________________________________", $Content);
    $Content = str_replace("{TrespasserCC}", "___________________________________", $Content);
    $Content = str_replace("{TrespasserCA}", "Via _______________________________________ n&#176; __", $Content);
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


$Content = str_replace("{Code}", $r_FineHistory['Code'], $Content);

$RndCode = "";
for ($i = 0; $i < 5; $i++) {
    $n = rand(1, 24);
    $RndCode .= substr($strCode, $n, 1);
    $n = rand(0, 9);
    $RndCode .= $n;
}


$Content = str_replace("{ProtocolId}", $ProtocolNumber, $Content);


$strProtocolNumber = "";
for ($k = strlen((string)$ProtocolNumber); $k < 9; $k++) {
    $strProtocolNumber .= "0";
}
$strProtocolNumber .= $ProtocolNumber;

$Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_" . $RndCode . ".pdf";


if ($r_Fine['TrespasserTypeId'] == 11) {
    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=10");
    $r_trespasser = mysqli_fetch_array($rs_trespasser);

    $Content = str_replace("{TrespasserRentName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
    $Content = str_replace("{DateRent}", DateOutDB($r_Fine['ReceiveDate']), $Content);

}

$aMainPart = explode("<main_part>", $Content);
$aRow = explode("<row>", $aMainPart[1]);
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'L', true);
$pdf->LN(10);

if ($r_Customer['PDFRefPrint']) {
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'L', true);
    $pdf->LN(9);
} else {
    $pdf->writeHTMLCell(190, 0, 10, '', '', 0, 0, 1, true, 'L', true);
    $pdf->LN(9);
}


$pdf->writeHTMLCell(100, 0, 110, '', '<h4>' . $a_GenreLetter[$trespasser['Genre']] . " " . substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 35) . '</h4>', 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(100, 0, 110, '', $trespasser['Address'], 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(100, 0, 110, '', $trespasser['ZIP'] . ' ' . $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ")", 0, 0, 1, true, 'L', true);
$pdf->LN(20);


$pdf->SetFont('helvetica', '', 8);

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[3]), 0, 0, 1, true, 'J', true);
$pdf->LN();
$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[4]), 0, 0, 1, true, 'C', true);
$pdf->LN();
$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
$pdf->LN();

$a_Payment = explode("<PAYMENT>", $aRow[6]);
$a_PaymentType = explode("<PAYMENTTYPE>", $aRow[7]);

if ($r_ArticleTariff['ReducedPayment']) {
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_Payment[1]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    if ($r_Customer['LumpSum'] == 1) {
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[1]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[2]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[3]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
    } else {
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[10]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
    }

}


if (!$r_ArticleTariff['ReducedPayment']) $a_Payment[2] = str_replace("DAL 6", "DAL 1", $a_Payment[2]);
$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_Payment[2]), 0, 0, 1, true, 'L', true);
$pdf->LN();
if ($r_Customer['LumpSum'] == 1) {
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[4]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[5]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[6]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
} else {
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[11]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
}


if (!$r_ArticleTariff['ReducedPayment']) {
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_Payment[3]), 0, 0, 1, true, 'L', true);
    $pdf->LN();
    if ($r_Customer['LumpSum'] == 1) {
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[7]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[8]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[9]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
    } else {
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[12]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
    }
}
if ($r_Customer['LumpSum'] == 1) {
    $pdf->SetFont('helvetica', '', 6);
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[9]), 0, 0, 1, true, 'J', true);
    $pdf->LN(6);
}

$pdf->LN(4);


$aCol = explode("<col>", $aRow[10]);
$y = $pdf->getY();
if ($r_Fine['TrespasserTypeId'] == 11) {
    $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'J', true);
    $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[3]), 0, 0, 1, true, 'J', true);
} else {
    $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'J', true);
    $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
}
$pdf->LN(5);


$aCol = explode("<col>", $aRow[11]);
$y = $pdf->getY();
if ($r_Fine['TrespasserTypeId'] == 11) {
    $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'J', true);
    $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[3]), 0, 0, 1, true, 'J', true);
} else {
    $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'J', true);
    $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
}
$pdf->LN(5);


$aCol = explode("<col>", $aRow[12]);
$y = $pdf->getY();
if ($r_Fine['TrespasserTypeId'] == 11) {
    $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'J', true);
    $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[3]), 0, 0, 1, true, 'J', true);
} else {
    $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'J', true);
    $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
}
$pdf->LN(10);

$y = $pdf->getY();
$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[13]), 0, 0, 1, true, 'J', true);
$pdf->LN(5);

$aCol = explode("<col>", $aRow[14]);
$y = $pdf->getY();

if (isset($aCol[1])) {
    $pdf->writeHTMLCell(90, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(90, 5, 120, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'L', true);
    $pdf->LN(2);

} else {
    $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
    $pdf->LN(2);
}


$y = $pdf->getY();


if (isset($aCol[2])) {
    //$pdf->writeHTMLCell(90, 5, 10, $y, utf8_encode($aRow[15]), 0, 0, 1, true, 'L', true);
    //$pdf->writeHTMLCell(90, 5, 100, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'L', true);
    //$pdf->LN(2);

    $str_Aggiuntiva = " " . $aCol[2];

} else {


    //$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[15]), 0, 0, 1, true, 'J', true);
    //$pdf->LN(5);

    $str_Aggiuntiva = "";
}
$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[15] . $str_Aggiuntiva), 0, 0, 1, true, 'J', true);
$pdf->LN(5);


$y = $pdf->getY();
$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[16]), 0, 0, 1, true, 'J', true);
$pdf->LN(5);


$y = $pdf->getY();
$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[17]), 0, 0, 1, true, 'J', true);
$pdf->LN(5);

$y = $pdf->getY();
$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[18]), 0, 0, 1, true, 'J', true);
$pdf->LN(5);


$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[19]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[20]), 0, 0, 1, true, 'J', true);


//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////
////
////    Fine page 2
////
////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
$pdf->Temporary();
$pdf->RightHeader = false;
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10, '', true);


$pdf->LN(1);

$aRow = explode("<row>", $aMainPart[2]);

$pdf->SetFont('helvetica', '', 8);
$pdf->writeHTMLCell(190, 20, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
$pdf->LN(15);

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[3]), 0, 0, 1, true, 'J', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[4]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[6]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'J', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[9]), 0, 0, 1, true, 'J', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[10]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[11]), 0, 0, 1, true, 'J', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[12]), 0, 0, 1, true, 'C', true);
$pdf->LN();

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[13]), 0, 0, 1, true, 'J', true);
$pdf->LN(15);

$pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[14]), 0, 0, 1, true, 'J', true);
$pdf->LN();


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
    $pdf->Temporary();
    $pdf->RightHeader = false;
    $pdf->AddPage();
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10, '', true);


    if (strlen($r_Customer['ManagerName']) > 22) {
        $pdf->writeHTMLCell(60, 0, 10, '', '<h3>' . trim($r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName']) . '</h3>', 0, 0, 1, true, 'L', true);
        $pdf->LN(10);

    } else {

        $pdf->writeHTMLCell(60, 0, 10, '', '<h3>' . trim($r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName']) . '</h3>', 0, 0, 1, true, 'L', true);
        $pdf->LN(5);
    }

    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerSector'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
//            $pdf->Line(7, 34, 200, 34);
    $pdf->LN(10);


    $aRow = explode("<row>", $aMainPart[3]);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
    $pdf->LN();
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'C', true);
    $pdf->LN();


    $pdf->SetFont('helvetica', '', 9);


    $aCol = explode("<col>", $aRow[3]);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(30, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(150, 5, 30, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
    $pdf->LN(8);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(150, 5, 30, $y, utf8_encode($aRow[4]), 0, 0, 1, true, 'L', true);
    $pdf->LN(10);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[5]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[6]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[9]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[10]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[11]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[12]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[13]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[14]), 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[15]), 0, 0, 1, true, 'C', true);
    $pdf->LN();
    $pdf->LN(2);
    $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[16]), 0, 0, 1, true, 'J', true);
    $pdf->LN();

    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[17]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);
    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[18]), 0, 0, 1, true, 'C', true);
    $pdf->LN();


    //////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////
    ////
    ////
    ////    LicensePoint page 2
    ////
    ////
    //////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////
    $pdf->Temporary();
    $pdf->RightHeader = false;
    $pdf->AddPage();
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10, '', true);

    $aRow = explode("<row>", $aMainPart[4]);

    $pdf->SetFont('helvetica', '', 9);

    $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
    $pdf->LN(10);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[2]), 0, 0, 1, true, 'J', true);
    $pdf->LN(32);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[3]), 0, 0, 1, true, 'J', true);
    $pdf->LN(24);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[4]), 0, 0, 1, true, 'J', true);
    $pdf->LN(17);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
    $pdf->LN(20);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[6]), 0, 0, 1, true, 'J', true);
    $pdf->LN(12);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[7]), 0, 0, 1, true, 'J', true);
    $pdf->LN(12);

    $pdf->SetFont('helvetica', 'B', 9, '', true);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[8]), 0, 0, 1, true, 'J', true);


}


/*
if ($ultimate){

    $aInsert = array(
        array('field'=>'NotificationTypeId','selector'=>'value','type'=>'int','value'=>$NotificationTypeId,'settype'=>'int'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_FineId,'settype'=>'int'),
        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Fine['TrespasserId'],'settype'=>'int'),
        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['TrespasserTypeId'],'settype'=>'int'),
        array('field'=>'CustomerNotificationFee','selector'=>'value','type'=>'flt','value'=>$r_Customer['CustomerNotificationFee'],'settype'=>'flt'),
        array('field'=>'CustomerResearchFee','selector'=>'value','type'=>'flt','value'=>$r_Customer['CustomerResearchFee'],'settype'=>'flt'),
        array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$NotificationFee,'settype'=>'flt'),
        array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$ResearchFee,'settype'=>'flt'),
        array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$controller['Id'],'settype'=>'int'),
        array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
    );
    $rs->Insert('FineHistory',$aInsert);

    $aUpdate = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
        array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber,'settype'=>'int'),
    );
    $rs->Update('Fine',$aUpdate, 'Id='.$r_FineId);

    $aInsert = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_FineId,'settype'=>'int'),
        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
        array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2),
    );
    $rs->Insert('FineDocumentation',$aInsert);


    if($_SESSION['usertype']==3) {
        $aInsert = array(
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['userid'], 'settype' => 'int'),
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineId, 'settype' => 'int'),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
        );
        $rs->Insert('DocumentationProtocol', $aInsert);
    }



    if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid']."/".$r_FineId)) {
        mkdir(NATIONAL_FINE."/".$_SESSION['cityid']."/".$r_FineId, 0777);
    }


    $FileName = $Documentation;

    $pdf->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
$rs->End_Transaction();
}

if ($ultimate){
if($r_Customer['DigitalSignature']==1){
    for($i=0; $i<count($a_DocumentationFineZip); $i++){
        copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i]);
    }

    $ftp_connection = false;
    $chk_inp_file = false;
    $server = '89.96.225.74';
    $username = 'velox';
    $password = 'Cd28+PeB';
    echo "Login FTP...<br />";
    $checkUpload = 0;
    $conn = ftp_connect($server);
    if ($conn){
        $login = ftp_login($conn, $username, $password);
        if ($login) {

            $ftp_connection = true;

            echo 'Connessione riuscita<br />';
            $path = "/".$_SESSION['username'];

            $origin = ftp_pwd($conn);
            // Controllo se esiste la cartella username
            if (ftp_chdir($conn, $_SESSION['username'])){
                // Se esiste torno alla cartella originale
                ftp_chdir($conn, $origin);
                $checkUpload = 1;
                for($i=0; $i<count($a_DocumentationFineZip); $i++){
                    $upload = ftp_put($conn,$path."/".$a_DocumentationFineZip[$i], NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i],FTP_BINARY);
                    if(!$upload){
                        $checkUpload = 0;
                        echo "<br />Upload files non completato!<br />Controllare file mancanti.";
                        DIE;

                    }else{
                        unlink(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);

                    }
                }
            }
            else{
                // La cartella non esiste
                echo "<br />Utente non abilitato alla firma o cartella inesistente!";
                DIE;
            }
        } else {
            echo '<br />Login fallita';
            DIE;
        }
    }
    else{
        echo '<br />Connessione fallita';
        DIE;
    }
    if($checkUpload==1){
        echo "<br />Upload dei seguenti file eseguito correttamente:";
        for($i=0; $i<count($a_DocumentationFineZip); $i++){
            echo "<br />".($i+1).") ".$a_DocumentationFineZip[$i];
            copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i]);
            unlink(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);
        }
        $rs->End_Transaction();
    }

    $_SESSION['Message'] = "Stampa definitiva avvenuta con successo.<br /> Sono stati creati e caricati nella cartella per la firma ".$int_ContFine." verbali.";
}
else{
    $str_Definitive = "Stampa definitiva avvenuta con successo!";
    for($i=0; $i<count($a_DocumentationFineZip); $i++){
        copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i]);
        unlink(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);
    }
    $_SESSION['Message'] = $str_Definitive;
}
}

*/




