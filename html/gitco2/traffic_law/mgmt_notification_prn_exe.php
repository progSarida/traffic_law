<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");



include_once TCPDF . "/tcpdf.php";


$FineId= CheckValue('Id','n');



$str_Back = "";
$str_Front = "";


$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('Request');
$pdf->SetSubject('Request');
$pdf->SetKeywords('');


$pdf->SetMargins(10,10,10);

$table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$table_row = mysqli_fetch_array($table_rows);

$MangerName = $table_row['ManagerName'];
$ManagerAddress = $table_row['ManagerAddress'];
$ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
$ManagerPhone = $table_row['ManagerPhone'];



$pdf->AddPage();
$pdf->SetFont('arial', '', 9, '', true);

$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


$rs_Notification = $rs->Select('V_FineNotification', "FineId=" . $FineId);
$r_Notification = mysqli_fetch_array($rs_Notification);




$str_DocumentFolder = ($r_Notification['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$r_Notification['FineId'] : FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$r_Notification['FineId'];


$rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $FineId." AND (DocumentationTypeId=10 OR DocumentationTypeId=11)");
while($r_Documentation = mysqli_fetch_array($rs_Documentation)) {
    if ($r_Documentation['DocumentationTypeId'] == 10) {
        $str_Front = $str_DocumentFolder."/".$r_Documentation['Documentation'];


    }

    if ($r_Documentation['DocumentationTypeId'] == 11) {
        $str_Back = $str_DocumentFolder."/".$r_Documentation['Documentation'];

    }
}


$pdf->LN(10);

$y = $pdf->getY();
$pdf->SetFont('arial', '', 9, '', true);
$pdf->writeHTMLCell(30, 4, 10, $y, "CRON", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(40, 4, 40, $y, $r_Notification['ProtocolId'].'/'.$r_Notification['ProtocolYear'], 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(40, 4, 80, $y, "Raccomandata", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 120, $y, $r_Notification['LetterNumber'], 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(40, 4, 180, $y, "Ricevuta", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 220, $y, $r_Notification['ReceiptNumber'], 1, 0, 1, true, 'C', true);


$pdf->LN(6);
$y = $pdf->getY();
$pdf->SetFont('arial', '', 9, '', true);
$pdf->writeHTMLCell(30, 4, 10, $y, "Data Spedizione", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(30, 4, 40, $y, DateOutDB($r_Notification['SendDate']), 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 70, $y, "Data Notifica", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(30, 4, 100, $y, DateOutDB($r_Notification['NotificationDate']), 1, 0, 1, true, 'C', true);

$pdf->writeHTMLCell(30, 4, 130, $y, "Esito", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(120, 4, 160, $y, $r_Notification['Title'], 1, 0, 1, true, 'C', true);



$pdf->Image($str_Back, 40, 60, 190, 120);

$pdf->AddPage();

$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


$pdf->LN(10);

$y = $pdf->getY();
$pdf->SetFont('arial', '', 9, '', true);
$pdf->writeHTMLCell(30, 4, 10, $y, "CRON", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(40, 4, 40, $y, $r_Notification['ProtocolId'].'/'.$r_Notification['ProtocolYear'], 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(40, 4, 80, $y, "Raccomandata", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 120, $y, $r_Notification['LetterNumber'], 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(40, 4, 180, $y, "Ricevuta", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(60, 4, 220, $y, $r_Notification['ReceiptNumber'], 1, 0, 1, true, 'C', true);


$pdf->LN(6);
$y = $pdf->getY();
$pdf->SetFont('arial', '', 9, '', true);
$pdf->writeHTMLCell(30, 4, 10, $y, "Data Spedizione", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(30, 4, 40, $y, DateOutDB($r_Notification['SendDate']), 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 70, $y, "Data Notifica", 1, 0, 1, true, 'L', true);
$pdf->writeHTMLCell(30, 4, 100, $y, DateOutDB($r_Notification['NotificationDate']), 1, 0, 1, true, 'C', true);

$pdf->writeHTMLCell(30, 4, 130, $y, "Esito", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(120, 4, 160, $y, $r_Notification['Title'], 1, 0, 1, true, 'C', true);



$pdf->Image($str_Front, 40, 60, 190, 120);


$FileName = 'export.pdf';

$pdf->Output(ROOT."/doc/print/notification/".$FileName, "F");
$_SESSION['Documentation'] = $MainPath.'/doc/print/notification/'.$FileName;


header("location: mgmt_notification_viw.php?Id=".$FineId);


