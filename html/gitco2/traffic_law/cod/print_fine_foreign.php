<?php
$rs->SetCharset('utf8');

$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$rs_FineHistory = $rs->Select('V_FineHistory', "Id=" . $FineId . " AND NotificationTypeId=2");
$r_FineHistory = mysqli_fetch_array($rs_FineHistory);


$ProtocolYear = $r_FineHistory['ProtocolYear'];
$ProtocolNumber = $r_FineHistory['ProtocolId'];

$StatusTypeId = 15;
$NotificationTypeId = 2;


$NotificationDate = $r_FineHistory['NotificationDate'];
$ResearchFee = $r_FineHistory['ResearchFee'];


$a_Lan = unserialize(LANGUAGE);
$a_Rent = unserialize(RENT);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
$a_AdditionalMass = unserialize(ADDITIONAL_MASS);




$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
}


$controllers = $rs->Select('Controller', "CityId='" . $r_FineHistory['CityId'] . "' AND Id=" . $r_FineHistory['FineControllerId']);
$controller = mysqli_fetch_array($controllers);


$pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);


$pdf->TemporaryPrint = 1;
$pdf->NationalFine = 0;
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



$pdf->AddPage();


$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

$ManagerName = $r_Customer['ManagerName'];
$pdf->customer = $ManagerName;


$rs_Fine = $rs->Select('V_FineArticle', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND Id=" . $FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);
$ViolationTypeId = $r_Fine['ViolationTypeId'];


$ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType1'];
$ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType2'];


$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "'");
$r_RuleType = mysqli_fetch_array($rs_RuleType);


$RuleTypeId = $r_RuleType['Id'];

$Fee = $r_FineHistory['Fee'];

$rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_FineHistory['ArticleId'] . " AND Year=" . $r_FineHistory['ProtocolYear']);
$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

$trespassers = $rs->Select('V_Trespasser', "Id=" . $r_FineHistory['TrespasserId']);
$trespasser = mysqli_fetch_array($trespassers);


$ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
$FormTypeId = $r_RuleType['ForeignFormId'];


$a_PrintObject = explode("*", $r_RuleType['PrintObject' . $a_Lan[$n_LanguageId]]);


$pdf->SetFont('arial', '', 10, '', true);
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

$pdf->Line(7, 9, 200, 9);
$pdf->writeHTMLCell(150, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
$pdf->LN(5);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
$pdf->Line(7, 34, 200, 34);
$pdf->LN(20);


$pdf->writeHTMLCell(100, 0, 110, '', '<h4>' . $trespasser['CompanyName'] . $trespasser['Surname'] . ' ' . $trespasser['Name'] . '</h4>', 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(100, 0, 110, '', $trespasser['Address'], 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(100, 0, 110, '', $trespasser['ZIP'] . ' ' . $trespasser['City'], 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(100, 0, 110, '', strtoupper($trespasser['CountryTitle']), 0, 0, 1, true, 'L', true);
$pdf->LN(15);

$str_AdditionalNight = "";
if($r_ArticleTariff['AdditionalNight']==1){

    $a_Time = explode(":",$r_Fine['FineTime']);

    if($a_Time[0]<FINE_HOUR_START_DAY ||  ($a_Time[0]>FINE_HOUR_END_DAY) || ($a_Time[0]==FINE_HOUR_END_DAY && $a_Time[1]!="00")){
        $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId] ;
    }
}

$str_AdditionalMass = "";
if($r_ArticleTariff['AdditionalMass']==1){
    if($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
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


$TotalFee = $Fee + $ResearchFee + $NotificationFee;

$FineTime = $r_FineHistory['FineTime'];

if ($r_FineHistory['DetectorId'] == 0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector', "Id=" . $r_Fine['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title' . $a_Lan[$n_LanguageId]];
    $SpeedLengthAverage = $detector['SpeedLengthAverage'];
}

$forms = $rs->Select('Form', "CityId='" . $_SESSION['cityid'] . "' AND FormTypeId=" . $FormTypeId . " AND LanguageId=" . $n_LanguageId);
if (mysqli_num_rows($forms) == 0) {
    $forms = $rs->Select('Form', "FormTypeId=" . $FormTypeId . " AND LanguageId=" . $n_LanguageId);
}


$form = mysqli_fetch_array($forms);

$str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
$Content = $form['Content'];

$Content = str_replace("{PrintObjectRow1}", $a_PrintObject[0], $Content);
$Content = str_replace("{PrintObjectRow2}", $a_PrintObject[1], $Content);
$Content = str_replace("{PrintObjectRow3}", $a_PrintObject[2], $Content);


$Content = str_replace("{FineDate}", DateOutDB($r_FineHistory['FineDate']), $Content);
$Content = str_replace("{FineTime}", TimeOutDB($FineTime), $Content);
$Content = str_replace("{VehicleTypeId}", $r_FineHistory['VehicleTitle' . $a_Lan[$n_LanguageId]], $Content);
$Content = str_replace("{VehiclePlate}", $r_FineHistory['VehiclePlate'], $Content);

$Content = str_replace("{VehicleBrand}", $r_FineHistory['VehicleBrand'], $Content);
$Content = str_replace("{VehicleModel}", $r_FineHistory['VehicleModel'], $Content);
$Content = str_replace("{VehicleColor}", $r_FineHistory['VehicleColor'], $Content);

$Paragraph = ($r_FineHistory['Paragraph'] == "0" || $r_FineHistory['Paragraph'] == "") ? "" : " / " . $r_FineHistory['Paragraph'];
$Letter = ($r_FineHistory['Letter'] == "0") ? "" : $r_FineHistory['Letter'];

$Content = str_replace("{ArticleId}", $r_FineHistory['Article'] . $Paragraph . " " . $Letter, $Content);

/////////////////////////////////////////////
//Article Owner
/////////////////////////////////////////////
$rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);

$str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
$Content = str_replace("{ArticleDescription}", $str_ArticleDescription, $Content);

$str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ReasonTitle' . $a_Lan[$n_LanguageId]];
$Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);

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

$Content = str_replace("{Speed}", $r_FineHistory['Speed'], $Content);
$Content = str_replace("{SpeedLimit}", NumberDisplay($r_FineHistory['SpeedLimit']), $Content);
$Content = str_replace("{SpeedControl}", NumberDisplay($r_FineHistory['SpeedControl']), $Content);

$Content = str_replace("{Locality}", $r_FineHistory['CityTitle'], $Content);
$Content = str_replace("{Address}", $r_FineHistory['Address'], $Content);
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

$Content = str_replace("{ControllerName}", $r_Fine['ControllerName'], $Content);
$Content = str_replace("{ControllerCode}", $r_Fine['ControllerCode'], $Content);

$Content = str_replace("{ChiefControllerName}", $controller['Name'], $Content);
$Content = str_replace("{AdditionalSanction}",$str_AdditionalNight.$str_AdditionalMass,$Content);

$Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
$Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);

if ($r_Fine['TrespasserTypeId'] == 11) {
    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=10");
    $r_trespasser = mysqli_fetch_array($rs_trespasser);

    $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
} else {
    $Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
}

$Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);


if (strlen($trespasser['BornDate']) == 10) {
    $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
} else {
    $Content = str_replace("{TrespasserBornDate}", "", $Content);
}
$Content = str_replace("{TrespasserAddress}", $trespasser['Address'] . " " . $trespasser['ZIP'] . ' ' . $trespasser['City'], $Content);
$Content = str_replace("{TrespasserCountry}", $trespasser['CountryTitle'], $Content);


$offices = $rs->Select('V_JudicialOffice', "CityId='" . $r_FineHistory['CityId'] . "'");
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

$Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . $NotificationDate . "_" . $r_FineHistory['CityId'] . "_" . $RndCode . ".pdf";


$aMainPart = explode("<main_part>", $Content);
$aRow = explode("<row>", $aMainPart[1]);

$pdf->writeHTMLCell(180, 0, 10, '', $aRow[1], 0, 0, 1, true, 'L', true);
$pdf->LN(10);

$pdf->writeHTMLCell(180, 0, 10, '', $aRow[2], 0, 0, 1, true, 'L', true);
$pdf->LN(10);

$pdf->writeHTMLCell(180, 0, 10, '', $aRow[3], 0, 0, 1, true, 'C', true);
$pdf->LN(5);
$pdf->writeHTMLCell(180, 0, 10, '', $aRow[4], 0, 0, 1, true, 'C', true);
$pdf->LN(5);
$pdf->writeHTMLCell(180, 0, 10, '', $aRow[5], 0, 0, 1, true, 'C', true);
$pdf->LN(15);


$pdf->writeHTMLCell(180, 0, 10, '', $aRow[6], 0, 0, 1, true, 'J', true);
$pdf->LN(15);
$pdf->writeHTMLCell(180, 0, 10, '', $aRow[7], 0, 0, 1, true, 'J', true);
$pdf->LN(30);

$pdf->SetFont('arial', '', 8, '', true);
for ($i = 8; $i < 21; $i++) {
    $aCol = explode("<col>", $aRow[$i]);

    if ($i == 18) {
        if ($r_Fine['TrespasserTypeId'] == 11) {

            $aCol[0] = $a_Rent[$n_LanguageId];
            $aCol[1] = $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'];

        }
    }


    if (!(trim($aCol[1]) == "0" || trim($aCol[1]) == "" || trim($aCol[1]) == "//")) {

        $y = $pdf->getY();
        $height = 4;

        $pdf->writeHTMLCell(60, $height, 20, $y, $aCol[0], 0, 0, 1, true, 'L', true);

        if ($i == 13) {
            if ($r_Fine['Speed'] > 0)
                $aCol[1] .= "<br>" . $aCol[2];
        }
        $height += $height * (floor(strlen($aCol[1]) / 60));
        $pdf->writeHTMLCell(100, $height, 80, $y, $aCol[1], 0, 0, 1, true, 'L', true);
        $pdf->LN();

    }
}

$pdf->LN(10);
$pdf->SetFont('arial', '', 9, '', true);
$y = $pdf->getY();
$pdf->writeHTMLCell(180, 0, 10, $y, $aRow[21], 0, 0, 1, true, 'J', true);
$pdf->LN(10);
$pdf->writeHTMLCell(180, 0, 10, '', $aRow[22], 0, 0, 1, true, 'R', true);
$pdf->LN(5);
$pdf->writeHTMLCell(180, 0, 10, '', $aRow[23], 0, 0, 1, true, 'R', true);

if ($r_ArticleTariff['ReducedPayment']) {
    $pdf->LN(5);
    $pdf->writeHTMLCell(180, 0, 10, '', $aRow[24], 0, 0, 1, true, 'L', true);
}


$pdf->Temporary();
$pdf->RightHeader = false;
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('arial', '', 10, '', true);

$pdf->SetFillColor(255, 255, 255);

$pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

$ManagerName = $r_Customer['ManagerName'];

// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
$pdf->Line(7, 9, 200, 9);
$pdf->writeHTMLCell(150, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
$pdf->LN(5);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
$pdf->Line(7, 34, 200, 34);
$pdf->LN(20);

$pdf->writeHTMLCell(180, 0, 10, '', $aRow[25], 0, 0, 1, true, 'J', true);
$pdf->LN(25);

$pdf->writeHTMLCell(180, 0, 10, '', $aRow[26], 0, 0, 1, true, 'J', true);

if ($r_ArticleTariff['126Bis'] == 1) {
    $query = "Select ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId ";
    $query .= "WHERE A.CityId='" . $r_FineHistory['CityId'] . "' AND A.Article=126 AND A.Letter='bis' AND ART.Year = " . $r_FineHistory['ProtocolYear'];

    $articles126bis = $rs->SelectQuery($query);
    $article126bis = mysqli_fetch_array($articles126bis);
    $pdf->LN(55);

    $aRow[27] = str_replace("{DecurtationPoints}", $r_ArticleTariff['LicensePoint'], $aRow[27]);
    $aRow[27] = str_replace("{Fee126bis}", NumberDisplay($article126bis['Fee']), $aRow[27]);
    $aRow[27] = str_replace("{MaxFee126bis}", NumberDisplay($article126bis['MaxFee']), $aRow[27]);
    $pdf->writeHTMLCell(180, 0, 10, '', $aRow[27], 0, 0, 1, true, 'J', true);
    $pdf->LN(30);
} else
    $pdf->LN(60);


$pdf->writeHTMLCell(180, 0, 10, '', $aRow[28], 0, 0, 1, true, 'J', true);
$pdf->LN(30);

$pdf->writeHTMLCell(180, 60, 10, '', $aRow[29], 0, 0, 1, true, 'J', true);
$pdf->LN(15);

$pdf->writeHTMLCell(180, 0, 10, '', $aRow[30], 0, 0, 1, true, 'L', true);
$pdf->LN(10);

$pdf->writeHTMLCell(180, 60, 10, '', $r_Customer['ManagerName'] . ", " . DateOutDB($NotificationDate), 0, 0, 1, true, 'L', true);
$pdf->LN(10);


if (strlen($controller['Name']) > 0) {
    $pdf->writeHTMLCell(90, 60, 90, '', $aRow[31], 0, 0, 1, true, 'C', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(90, 60, 90, '', $aRow[32], 0, 0, 1, true, 'C', true);
    $pdf->LN(4);


    $y = $pdf->getY();
    //Image($file, $x='', $y='', $w=0, $h=0,
    $pdf->Image(SIGN . "/" . $r_Customer['CityId'] . "/" . $controller['Sign'], 90, $y, 80, 35);
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
    $pdf->Temporary();
    $pdf->RightHeader = false;
    $pdf->AddPage();
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('arial', '', 10, '', true);

    $aRow = explode("<row>", $aMainPart[2]);
    $aRow[1] = $ManagerSubject;

    // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
    $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);
    $pdf->Line(7, 9, 200, 9);
    $pdf->writeHTMLCell(150, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
    $pdf->LN(5);
    $pdf->writeHTMLCell(150, 0, 30, '', strtoupper($aRow[1]), 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
    $pdf->Line(7, 34, 200, 34);
    $pdf->LN(25);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->writeHTMLCell(190, 0, 10, '', strtoupper($aRow[2]), 0, 0, 1, true, 'C', true);
    $pdf->LN();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->writeHTMLCell(190, 0, 10, '', $aRow[3], 0, 0, 1, true, 'C', true);
    $pdf->LN();
    $pdf->LN(5);

    $pdf->SetFont('Arial', '', 10);


    $aCol = explode("<col>", $aRow[4]);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(30, 5, 10, $y, $aCol[0], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->writeHTMLCell(150, 5, 30, $y, $aCol[1], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $y = $pdf->getY();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->writeHTMLCell(150, 5, 30, $y, strtoupper($aCol[2]), 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(5);

    $pdf->SetFont('Arial', '', 10);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[5], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->LN(5);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[6], 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[7], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->LN(5);

    $y = $pdf->getY();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[8], 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $y = $pdf->getY();
    $pdf->SetFont('Arial', '', 10);
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[9], 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[10], 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[11], 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[12], 0, 0, 1, true, 'L', true);
    $pdf->LN();

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[13], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->LN(5);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[14], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->LN(10);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[15], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->LN(10);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[16], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(5);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[17], 0, 0, 1, true, 'J', true);
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

    $pdf->SetFont('arial', '', 10, '', true);
    // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
    $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);
    $pdf->Line(7, 9, 200, 9);
    $pdf->writeHTMLCell(150, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
    $pdf->LN(5);
    $pdf->writeHTMLCell(150, 0, 30, '', strtoupper($aRow[1]), 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
    $pdf->Line(7, 34, 200, 34);
    $pdf->LN(25);

    $aRow = explode("<row>", $aMainPart[3]);

    $pdf->SetFont('Arial', 'B', 10);

    $pdf->writeHTMLCell(190, 0, 10, '', $aRow[1], 0, 0, 1, true, 'C', true);
    $pdf->LN();
    $pdf->LN(5);

    $pdf->SetFont('Arial', '', 10);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[2], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(5);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[3], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(5);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[4], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[5], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[6], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[7], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[8], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[9], 0, 0, 1, true, 'J', true);
    $pdf->LN();
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[10], 0, 0, 1, true, 'J', true);


}