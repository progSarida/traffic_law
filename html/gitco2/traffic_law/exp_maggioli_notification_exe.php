<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 0);



include_once TCPDF . "/tcpdf.php";


$table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$table_row = mysqli_fetch_array($table_rows);

$MangerName = $table_row['ManagerName'];
$ManagerAddress = $table_row['ManagerAddress'];
$ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
$ManagerPhone = $table_row['ManagerPhone'];

$a_FileImgList = array();


$file_Document = fopen(ROOT."/public/_TMP_EXP/".'file.csv', 'w');

$strDocumentCsvFile = 'Identificativo violazione;Data violazione;Targa;Data notifica;Data ritorno;Numero Raccomandata;Immagine'.PHP_EOL;
fwrite($file_Document, $strDocumentCsvFile);




$a_FileImgList[] = 'file.csv';

$rs_Notification = $rs->SelectQuery("
SELECT 
F.Id,
F.CityId,
F.Code,
F.FineDate,
F.ProtocolId,
F.ProtocolYear,
F.VehiclePlate,
F.CountryId,

FN.SendDate,
FN.NotificationDate,
FN.LetterNumber,
FN.ReceiptNumber,

R.Title

FROM Fine F JOIN FineNotification FN ON F.Id = FN.FineId
JOIN Result R ON FN.ResultId=R.Id


WHERE F.CityId='".$_SESSION['cityid']."' AND FineDate='2017-06-26' AND F.CountryId!='Z000'
");


while ($r_Notification = mysqli_fetch_array($rs_Notification)) {
/*
Identificativo violazione 	Data violazione	    Targa	    Data notifica	    Data ritorno	Numero Raccomandata	    Immagine
190479/2014	                03/10/2014	        DN-AD8139	17/02/2016	        12/04/2016	    RA690223209	            CARTOLINE\RIC_KS6133.pdf
198168/2014	                29/11/2014	        TI234502	22/07/2015	        15/09/2015	    RA689470640	            CARTOLINE\RIC_KB6497.pdf


*/
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Request');
    $pdf->SetSubject('Request');
    $pdf->SetKeywords('');


    $pdf->SetMargins(10,10,10);

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



    $str_DocumentFolder = ($r_Notification['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$r_Notification['Id'] : FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$r_Notification['Id'];

    $rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $r_Notification['Id']." AND (DocumentationTypeId=10 OR DocumentationTypeId=11)");



    $str_Front = $str_Back = '';
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
    $pdf->writeHTMLCell(30, 4, 40, $y, f_Exp_DateOutDB_Maggioli($r_Notification['SendDate']), 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(30, 4, 70, $y, "Data Notifica", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 100, $y, f_Exp_DateOutDB_Maggioli($r_Notification['NotificationDate']), 1, 0, 1, true, 'C', true);

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
    $pdf->writeHTMLCell(30, 4, 40, $y, f_Exp_DateOutDB_Maggioli($r_Notification['SendDate']), 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(30, 4, 70, $y, "Data Notifica", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 100, $y, f_Exp_DateOutDB_Maggioli($r_Notification['NotificationDate']), 1, 0, 1, true, 'C', true);

    $pdf->writeHTMLCell(30, 4, 130, $y, "Esito", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(120, 4, 160, $y, $r_Notification['Title'], 1, 0, 1, true, 'C', true);


    $pdf->Image($str_Front, 40, 60, 190, 120);


    $FileName = $r_Notification['LetterNumber'].'.pdf';
    $a_FileImgList[] = $FileName;
    $pdf->Output(ROOT."/public/_TMP_EXP/".$FileName, "F");






    $NotificationDate = strlen(trim($r_Notification['NotificationDate'])==0) ? '' : f_Exp_DateOutDB_Maggioli($r_Notification['NotificationDate']);



    $strDocumentCsvFile =  $r_Notification['Code'].';'.
        f_Exp_DateOutDB_Maggioli($r_Notification['FineDate']).';'.
        $r_Notification['VehiclePlate'].';'.
        $NotificationDate.';'.
        $NotificationDate.';'.
        $r_Notification['LetterNumber'].';'.
        $FileName.PHP_EOL;

    fwrite($file_Document, $strDocumentCsvFile);


}
fclose($file_Document);

$str_OutMonitor = "File creato con successo!";

$str_FileNameZip = "EXP_" . $_SESSION['cityid'] . "_" . date("Y-m-d") . "_" . date("H-i-s") . "_" . count($a_FileImgList) . ".zip";

$obj_Zip = new ZipArchive();
if($obj_Zip->open(ROOT."/public/_TMP_EXP/". $str_FileNameZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)===true){
    $_SESSION['Documentation'] = $MainPath . 'public/_TMP_EXP/' . $str_FileNameZip;

    for ($i = 0; $i < count($a_FileImgList); $i++) {
        $obj_Zip->addFile(ROOT."/public/_TMP_EXP/". $a_FileImgList[$i], $a_FileImgList[$i]);
        sleep(1);
    }
    $obj_Zip->close();

    for ($i = 0; $i < count($a_FileImgList); $i++) {
        unlink(ROOT."/public/_TMP_EXP/". $a_FileImgList[$i]);
    }

    $_SESSION['Message'] = $str_OutMonitor;
}