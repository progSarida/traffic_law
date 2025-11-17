<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
ini_set('max_execution_time', 3000);




$str_GETLink = "";

$str_CurrentPage = "?btn_search=1";


$s_TypePlate = CheckValue('TypePlate','s');


$str_CurrentPage .="&TypePlate=".$s_TypePlate."&RecordLimit=".$n_RecordLimit;

$Presentation = CheckValue('Presentation','n');






$str_Where = "CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
$strOrder = "ProtocolId ASC";


$d_PrintDate = date('d/m/Y');




if($Presentation==0){
    $str_Where .= " AND PresentationDate IS NULL ";
} else if($Presentation==2){
    $str_Where .= " AND PresentationDate IS NOT NULL ";
}

$str_CurrentPage .="&Presentation=".$Presentation;









include_once TCPDF . "/tcpdf.php";







$P = "mgmt_presentation.php";



$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('Art180');
$pdf->SetSubject('Art180');
$pdf->SetKeywords('');


$pdf->SetMargins(10,10,10);

$rs_Manager = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$r_Manager = mysqli_fetch_array($rs_Manager);

$MangerName = $r_Manager['ManagerName'];
$ManagerAddress = $r_Manager['ManagerAddress'];
$ManagerCity = $r_Manager['ManagerZIP']." ".$r_Manager['ManagerCity']." (".$r_Manager['ManagerProvince'].")";
$ManagerPhone = $r_Manager['ManagerPhone'];







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

$pdf->LN(10);



$pdf->writeHTMLCell(200, 0, 30, '', "DATA STAMPA $d_PrintDate", 0, 0, 1, true, 'C', true);
$pdf->LN(10);

$pdf->writeHTMLCell(200, 0, 30, '', "ELENCO ART. 180", 0, 0, 1, true, 'C', true);
$pdf->LN(10);







$pdf->AddPage();
$pdf->SetFont('arial', '', 7, '', true);

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


$pdf->LN(10);
$pdf->SetFont('arial', '', 9, '', true);
$pdf->writeHTMLCell(200, 0, 30, '', "ELENCO ART. 180", 0, 0, 1, true, 'C', true);
$pdf->LN(10);








$y = $pdf->getY();
$pdf->SetFont('arial', '', 8, '', true);
$pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 20, $y, "Cron", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(40, 4, 50, $y, "Ref", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(25, 4, 90, $y, "Data", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(25, 4, 115, $y, "Ora", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 140, $y, "Targa", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(90, 4, 170, $y, "Trasgressore", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(20, 4, 260, $y, "Articolo", 1, 0, 1, true, 'C', true);
$pdf->LN(4);


$n_Cont = 0;
$n_Row = 0;

$n_ChangePage = 30;



$rs_Presentation = $rs->Select('V_FinePresentation', $str_Where, $strOrder);



$pdf->SetFont('arial', '', 8, '', true);
while ($r_Presentation = mysqli_fetch_array($rs_Presentation)) {

    if($n_Row==$n_ChangePage ){

        $pdf->AddPage();
        $pdf->SetFont('arial', '', 8, '', true);

        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->LN(10);

        $pdf->writeHTMLCell(200, 0, 30, '', "ELENCO ART. 180", 0, 0, 1, true, 'C', true);
        $pdf->LN(10);


        $y = $pdf->getY();
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 20, $y, "Cron", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(40, 4, 50, $y, "Ref", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(25, 4, 90, $y, "Data", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(25, 4, 115, $y, "Ora", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 140, $y, "Targa", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(90, 4, 170, $y, "Trasgressore", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(20, 4, 260, $y, "Articolo", 1, 0, 1, true, 'C', true);
        $pdf->LN(4);

        $pdf->SetFont('arial', '', 8, '', true);
        $n_Row =0;
        $n_ChangePage = 30;

    }

    $n_Row++;
    $n_Cont++;


    $str_Trespasser = $r_Presentation['CompanyName'] .' '.$r_Presentation['Surname'] .' '.$r_Presentation['Name'];
    $str_Trespasser = (strlen($str_Trespasser)>42) ? substr($str_Trespasser,0,40).'...' : $str_Trespasser;

    $y = $pdf->getY();
    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->writeHTMLCell(10, 4, 10, $y, $n_Cont, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 20, $y, $r_Presentation['ProtocolId'] .'/'.$r_Presentation['ProtocolYear'], 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 50, $y, $r_Presentation['Code'], 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(25, 4, 90, $y, DateOutDB($r_Presentation['FineDate']), 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(25, 4, 115, $y, $r_Presentation['FineTime'], 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(30, 4, 140, $y, $r_Presentation['VehiclePlate'], 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(90, 4, 170, $y, $str_Trespasser, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(20, 4, 260, $y, $r_Presentation['Article'] .' '.$r_Presentation['Paragraph'].' '.$r_Presentation['Letter'], 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

}





    $FileName = $_SESSION['cityid'].'_art180_'.date("Y-m-d_H-i").'.pdf';

    $pdf->Output(ROOT."/doc/print/art180/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/art180/'.$FileName;


    header("location: ".$P.$str_CurrentPage);


