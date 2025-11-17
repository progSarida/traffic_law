<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

include_once TCPDF . "/tcpdf.php";

$Filters = CheckValue('Filters','s');
$Search_InvoiceId = CheckValue('Search_InvoiceId','n');

$rs= new CLS_DB();
$str_Where = "(F.PrintInvoiceId='".$Search_InvoiceId."' OR F.PostageInvoiceId='".$Search_InvoiceId."')";

$query = "SELECT * FROM Flow_Invoices WHERE Id=".$Search_InvoiceId;
$a_invoice = $rs->getArrayLine($rs->SelectQuery($query));
$a_flow = $rs->getResults($rs->SelectQuery("SELECT F.*, C.ManagerName FROM V_Flow F JOIN Customer C ON F.CityId=C.CityId WHERE ".$str_Where." ORDER BY F.Year DESC, F.Number ASC"));


$pdf = new TCPDF("P", "mm", "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false);
$pdf->SetCellPadding(0);
$pdf->AddPage("P");
$pdf->SetMargins(7.0, 10.0, 7.0);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 0, "FATTURA NUMERO ".$a_invoice['Number']."/".$a_invoice['Year']." del ".DateOutDB($a_invoice['Date']) , 0, "C", 0,1);
$pdf->Ln(10);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+195,$y);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 0, "Codice Ente" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(20, 0, "Flussi" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(25, 0, "Data upload" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(15, 0, "Records" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(35, 0, "Stampa e imbus." , 0, 0, 'C', 0, '', 0);
$pdf->Cell(35, 0, "Spese postali" , 0, 0, 'C', 0, '', 0);
$pdf->Cell(30, 0, "Totale" , 0, 1 , 'C', 0, '', 0);
$pdf->Ln(2);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+195,$y);
$pdf->Ln(2);
$totalPrint = 0;
$totalPostage = 0;
$totalRecords = 0;
$cont = 1;
for ($i=0;$i<count($a_flow);$i++) {
    $pdf->SetFont('Arial', '', 9);
    $totalRecords+=$a_flow[$i]['RecordsNumber'];
    $pdf->Cell(30, 0, $a_flow[$i]['CityId'] , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(20, 0, $a_flow[$i]['Number']."/".$a_flow[$i]['Year'] , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(25, 0, DateOutDB($a_flow[$i]['CreationDate']) , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(15, 0, $a_flow[$i]['RecordsNumber'] , 0, 0, 'C', 0, '', 0);
    $printCost = 0;
    if($a_flow[$i]['PrintInvoiceId']==$Search_InvoiceId){
        $printCost = $a_flow[$i]['PrintCost']*$a_flow[$i]['RecordsNumber'];
        $totalPrint+= $printCost;
    }
    $pdf->Cell(35, 0, NumberDisplay($printCost)." Euro" , 0, 0, 'C', 0, '', 0);

    $postage = 0;
    if($a_flow[$i]['PostageInvoiceId']==$Search_InvoiceId){
        $postageZone0 = $a_flow[$i]['Zone0Postage']*$a_flow[$i]['Zone0Number'];
        $postageZone1 = $a_flow[$i]['Zone1Postage']*$a_flow[$i]['Zone1Number'];
        $postageZone2 = $a_flow[$i]['Zone2Postage']*$a_flow[$i]['Zone2Number'];
        $postageZone3 = $a_flow[$i]['Zone3Postage']*$a_flow[$i]['Zone3Number'];
        $postage = $postageZone0+$postageZone1+$postageZone2+$postageZone3;
        $totalPostage+= $postage;
    }
    $pdf->Cell(35, 0, NumberDisplay($postage)." Euro" , 0, 0, 'C', 0, '', 0);

    $pdf->Cell(35, 0, NumberDisplay($printCost+$postage)." Euro" , 0, 1, 'C', 0, '', 0);

    $pdf->Ln(2);
    if($cont==40){
        $pdf->AddPage("P");
        $pdf->SetMargins(7.0, 10.0, 7.0);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->MultiCell(0, 0, "FATTURA NUMERO ".$a_invoice['Number']."/".$a_invoice['Year']." del ".DateOutDB($a_invoice['Date']) , 0, "C", 0,1);
        $pdf->Ln(10);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Line($x,$y,$x+195,$y);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 0, "Codice Ente" , 0, 0, 'C', 0, '', 0);
        $pdf->Cell(20, 0, "Flussi" , 0, 0, 'C', 0, '', 0);
        $pdf->Cell(25, 0, "Data upload" , 0, 0, 'C', 0, '', 0);
        $pdf->Cell(15, 0, "Records" , 0, 0, 'C', 0, '', 0);
        $pdf->Cell(35, 0, "Stampa e imbus." , 0, 0, 'C', 0, '', 0);
        $pdf->Cell(35, 0, "Spese postali" , 0, 0, 'C', 0, '', 0);
        $pdf->Cell(30, 0, "Totale" , 0, 1 , 'C', 0, '', 0);
        $pdf->Ln(2);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Line($x,$y,$x+195,$y);
        $pdf->Ln(2);
        $cont = 1;
    }
    else
        $cont++;
}

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Line($x,$y,$x+195,$y);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(75, 0, "TOTALI FATTURA" , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(15, 0, $totalRecords , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(35, 0, NumberDisplay($totalPrint)." Euro" , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(35, 0, NumberDisplay($totalPostage)." Euro" , 0, 0, 'C', 0, '', 0);
    $pdf->Cell(35, 0, NumberDisplay($totalPrint+$totalPostage)." Euro" , 0, 1, 'C', 0, '', 0);

    $pdf->Ln(2);
    $FileName = 'export.pdf';

    $pdf->Output(ROOT."/doc/print/flow/".$FileName, "F");

$_SESSION['Documentation'] = $MainPath.'/doc/print/flow/'.$FileName;

header("location: ".impostaParametriUrl(array('Filter' => 1, 'Search_InvoiceId' => $Search_InvoiceId), 'mgmt_flow.php'.$Filters));
