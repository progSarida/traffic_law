<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 3000);

include_once TCPDF . "/tcpdf.php";


$Search_Trespasser = CheckValue('Search_Trespasser', 's');
$Search_Office = CheckValue('Search_Office', 'n');
$FromProtocolId = CheckValue('FromProtocolId', 'n');
$ToProtocolId = CheckValue('ToProtocolId', 'n');

$FromHearingDate = CheckValue('FromHearingDate', 's');
$ToHearingDate = CheckValue('ToHearingDate', 's');

$FromCityId = CheckValue('FromCityId', 's');
$ToCityId = CheckValue('ToCityId', 's');

$GradeType          = CheckValue('GradeType','n');




$str_WhereCity = " AND CityId='" . $_SESSION['cityid'] . "' ";

if ($FromCityId != "" || $ToCityId != "") {
    $str_WhereCity = "";
    if ($FromCityId != "") $str_WhereCity .= " AND CityId>='" . $FromCityId . "' ";
    if ($ToCityId != "") $str_WhereCity .= " AND CityId<='" . $ToCityId . "' ";
}


$d_PrintDate = Date("d-m-Y");


$str_Where .= $str_WhereCity;
//$str_Where.= "AND TypeHearing!='No udienza' ";
if ($Search_Trespasser != "") {
    $str_Where .= " AND (CompanyName LIKE '%" . $Search_Trespasser . "%' OR Surname LIKE '%" . $Search_Trespasser . "%' OR Name LIKE '%" . $Search_Trespasser . "%')";
}


if ($FromProtocolId > 0) {
    $str_Where .= " AND ProtocolId >= $FromProtocolId";
} else {
    $FromProtocolId = "";
}

if ($ToProtocolId > 0) {
    $str_Where .= " AND ProtocolId <= $ToProtocolId";
} else {
    $ToProtocolId = "";
}


if ($Search_Office > 0) {
    $str_Where .= " AND OfficeId =" . $Search_Office;
}

if($FromHearingDate!=""){
    $str_Where .= " AND  (IFNULL(DateHearing, DateFile)  >= '".DateInDB($FromHearingDate)  ."')";
}

if($ToHearingDate!=""){
    $str_Where .= " AND (IFNULL(DateHearing, DateFile) <= '".DateInDB($ToHearingDate)  ."')";
}
if($GradeType>0){
    $str_Where .= " AND GradeTypeId=".$GradeType;
}

$strOrder = "Number, DateHearing";


$P = "mgmt_dispute.php";


$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor("Ricorsi");
$pdf->SetTitle('Request');
$pdf->SetSubject('Request');
$pdf->SetKeywords('');

$pdf->SetAutoPageBreak(false);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(10, 10, 10);

$r_Payments = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_Payment = mysqli_fetch_array($r_Payments);

$MangerName = $r_Payment['ManagerName'];
$ManagerAddress = $r_Payment['ManagerAddress'];
$ManagerCity = $r_Payment['ManagerZIP'] . " " . $r_Payment['ManagerCity'] . " (" . $r_Payment['ManagerProvince'] . ")";
$ManagerPhone = $r_Payment['ManagerPhone'];


$pdf->AddPage();
$pdf->SetFont('arial', '', 9, '', true);

$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);


$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

$pdf->LN(10);


$pdf->writeHTMLCell(200, 0, 30, '', "DATA STAMPA $d_PrintDate", 0, 0, 1, true, 'C', true);
$pdf->LN(10);

$pdf->writeHTMLCell(200, 0, 30, '', "ELENCO RICORSI", 0, 0, 1, true, 'C', true);
$pdf->LN(10);


//$pdf->AddPage();
//$pdf->SetFont('arial', '', 7, '', true);
//
//$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//
//$pdf->SetFillColor(255, 255, 255);
//$pdf->SetTextColor(0, 0, 0);
//
//$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);
//
//
//$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
//$pdf->LN(4);
//$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
//$pdf->LN(4);
//$pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
//$pdf->LN(4);
//$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

$pdf->LN(10);


$y = $pdf->getY();
$pdf->SetFont('arial', '', 8, '', true);

$pdf->writeHTMLCell(60, 4, 10, $y, "Grado", "TL", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(90, 4, 70, $y, "Riferimento", "TL", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 160, $y, "R.G. N", "TL", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(50, 4, 220, $y, "Data udienza", "LTR", 0, 1, true, 'L', true);

$pdf->LN(4);
$y = $pdf->getY();

$pdf->writeHTMLCell(60, 4, 10, $y, "Cron", "L", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(90, 4, 70, $y, "Autorità", "L", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 160, $y, "Note", "L", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(50, 4, 220, $y, "Tipo udienza", "LR", 0, 1, true, 'L', true);

$pdf->LN(4);
$y = $pdf->getY();

$pdf->writeHTMLCell(60, 4, 10, $y, "Anagrafica trasgressore", "LB", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(90, 4, 70, $y, "", "LB", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 160, $y, "", "LB", 0, 1, true, 'L', true);
$pdf->writeHTMLCell(50, 4, 220, $y, "", "LRB", 0, 1, true, 'L', true);




$a_GradeType = array("","I","II","III");

$table_rows = $rs->Select('V_FineDispute',$str_Where, $strOrder);

$n_Cont = 0;
$countRows = mysqli_num_rows($table_rows);
while ($table_row = mysqli_fetch_array($table_rows)) {
    $n_Cont++;


    $str_Number = ($table_row['Number']!="") ? " (".$table_row['Number'].")" : "";
    $pdf->LN(4);
    $y = $pdf->getY();

    $pdf->writeHTMLCell(60, 4, 10, $y, $a_GradeType[$table_row['GradeTypeId']], "L", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(90, 4, 70, $y, $table_row['ProtocolNumber'], "L", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(60, 4, 160, $y, "Ricorso".$str_Number, "L", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 220, $y, DateOutDB($table_row['DateHearing'])." ".$table_row['TimeHearing'], "LR", 0, 1, true, 'L', true);

    $pdf->LN(4);
    $y = $pdf->getY();


    $str_Note = (strlen($table_row['Note'])>115) ? strtolower(substr($table_row['Note'],0,112))."..." : strtolower($table_row['Note']);

    $pdf->writeHTMLCell(60, 4, 10, $y, "(".$table_row['CityId'].") ". $table_row['ProtocolId']."/".$table_row['ProtocolYear'], "L", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(90, 4, 70, $y, $table_row['OfficeTitle']." ".$table_row['OfficeCity'], "L", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(60, 4, 160, $y, $str_Note, "L", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 220, $y, $table_row['TypeHearing'], "LR", 0, 1, true, 'L', true);

    $pdf->LN(4);
    $y = $pdf->getY();


    $pdf->writeHTMLCell(60, 0, 10, $y, substr($table_row['CompanyName'] .' '.$table_row['Surname'] .' '.$table_row['Name'],0,33), "LB", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(90, 0, 70, $y, substr($table_row['OfficeAdditionalData'],0,50), "LB", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(60, 0, 160, $y, "", "LB", 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 0, 220, $y, "", "LRB", 0, 1, true, 'L', true);

    if($y>180){
        $pdf->AddPage();

        $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);


        $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

        $pdf->LN(10);


        $y = $pdf->getY();
        $pdf->SetFont('arial', '', 8, '', true);

        $pdf->writeHTMLCell(60, 4, 10, $y, "Grado", "TL", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(90, 4, 70, $y, "Riferimento", "TL", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(60, 4, 160, $y, "R.G. N", "TL", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 220, $y, "Data udienza", "LTR", 0, 1, true, 'L', true);

        $pdf->LN(4);
        $y = $pdf->getY();

        $pdf->writeHTMLCell(60, 4, 10, $y, "Cron", "L", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(90, 4, 70, $y, "Autorità", "L", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(60, 4, 160, $y, "Note", "L", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 220, $y, "Tipo udienza", "LR", 0, 1, true, 'L', true);

        $pdf->LN(4);
        $y = $pdf->getY();

        $pdf->writeHTMLCell(60, 4, 10, $y, "Anagrafica trasgressore", "LB", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(90, 4, 70, $y, "", "LB", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(60, 4, 160, $y, "", "LB", 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 220, $y, "", "LRB", 0, 1, true, 'L', true);

        $n_Cont=0;
    }

}



$FileName = $_SESSION['cityid'] . '_stampa_ricorsi_' . date("Y-m-d_H-i") . '.pdf';

$pdf->Output(ROOT . "/doc/print/dispute/" . $FileName, "F");
$_SESSION['Documentation'] = $MainPath . '/doc/print/dispute/' . $FileName;


header("location: " . $P);
