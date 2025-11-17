<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
ini_set('max_execution_time', 3000);




$a_Month = array("","GENNAIO","FEBBRAIO","MARZO","APRILE","MAGGIO","GIUGNO","LUGLIO","AGOSTO","SETTEMBRE","OTTOBRE","NOVEMBRE","DICEMBRE");

$P = "prn_judicialaccount.php";
$n_CSV = CheckValue('CSV','n');



$r_Payments = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$MangerName = mysqli_fetch_array($r_Payments)['ManagerName'];

$rs_Payment = $rs->SelectQuery("
  SELECT 
    COUNT(*) TotalPayment, 
    MONTH(PaymentDate) PaymentMonth, 
    SUM(Amount) TotalAmount
  FROM `FinePayment` 
  WHERE BankMgmt=1 AND CityId='" . $_SESSION['cityid'] . "' AND YEAR(PaymentDate)=" . $_SESSION['year'] . " GROUP BY MONTH(PaymentDate)

");

$rs_Refund = $rs->SelectQuery("
  SELECT 
    COUNT(*) TotalRefund, 
    MONTH(RefundDate) RefundMonth, 
    SUM(Amount) TotalAmount
  FROM `FineRefund` 
  WHERE CityId='". $_SESSION['cityid'] ."' AND YEAR(RefundDate)=". $_SESSION['year'] ." GROUP BY MONTH(RefundDate)

");
$a_Refund = array();
while ($r_Refund = mysqli_fetch_array($rs_Refund)) {
    $a_Refund[$r_Refund['RefundMonth']]=array("tot"=>$r_Refund['TotalRefund'],
        "amount"=>$r_Refund['TotalAmount']);
}




$n_CountRow = 0;
$n_TotalPayment = 0;
$f_TotalAmount = 0.00;



if($n_CSV){
    $filename="export.xls";
    header ("Content-Type: application/vnd.ms-excel");
    header ("Content-Disposition: inline; filename=$filename");




    $str_Content = '
        <table border="1">
	        <tr>
	            <td colspan="7">'. $MangerName . ' - Gestione Violazione al Codice della Strada</td>    
	        </tr>    
	        <tr>
	            <td colspan="7">CONTO DELLA GESTIONE DELL\'AGENTE CONTABILE: SARIDA S.r.l. - ANNO ' . $_SESSION['year'] .'</td>
            </tr>
            <tr>
                <td colspan="4">Estremi riscossione</td>
                <td colspan="3">Versameno in tesoreria</td>
            </tr>

            <tr>
                <td>N Ord.</td>
                <td>Periodo riscossione</td>
                <td>Numero ricevute</td>
                <td>Importo</td>

                <td>N. Quietanza</td>
                <td>Importo</td>
                <td>Note (1)</td>
            </tr>               
	    ';




    while ($r_Payment = mysqli_fetch_array($rs_Payment)) {

        $n_CountRow++;
        if($n_CountRow<$r_Payment['PaymentMonth']){
            for($i=$n_CountRow; $i<$r_Payment['PaymentMonth'];$i++ ){
                $str_Content .= '
                <tr>
                    <td>'. $n_CountRow .'</td>
                    <td>'. $a_Month[$i] .'</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>   
            ';
            }

            $n_CountRow=$r_Payment['PaymentMonth'];
        }




        $n_TotalPaymentMonth = $r_Payment['TotalPayment'];
        $f_TotalAmountMonth = $r_Payment['TotalAmount'];

        if(isset($a_Refund[$n_CountRow])){
            $a_RefundMonth = $a_Refund[$n_CountRow];
            $n_TotalPaymentMonth += $a_RefundMonth['tot'];
            $f_TotalAmountMonth -= $a_RefundMonth['amount'];
        }


        $str_Content .= '
            <tr>
                <td>'. $r_Payment['PaymentMonth'] .'</td>
                <td>'. $a_Month[$r_Payment['PaymentMonth']] .'</td>
                <td>'. $n_TotalPaymentMonth .'</td>
                <td>'. $f_TotalAmountMonth .'</td>
        ';
        $n_TotalPayment += $n_TotalPaymentMonth;
        $f_TotalAmount += $f_TotalAmountMonth;


        $str_Content .= '
            <td></td>
            <td></td>
            <td></td>
        </tr>   
            
    ';


    }

    $str_Content .= '
        <tr>
            <td></td>
            <td>TOTALE</td>
            <td>' .$n_TotalPayment .'</td>
            <td>' .$f_TotalAmount .'</td>
            <td></td>

            <td></td>
            <td></td>

        </tr>   
    ';

    $str_Content .= '</table>';
    echo $str_Content;
}else {

    include_once TCPDF . "/tcpdf.php";

    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Request');
    $pdf->SetSubject('Request');
    $pdf->SetKeywords('');


    $pdf->SetMargins(10, 10, 10);



    $pdf->AddPage();
    $pdf->SetFont('arial', '', 9, '', true);

    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);


    $pdf->writeHTMLCell(270, 0, 10, '', $MangerName . " - Gestione Violazione al Codice della Strada", 0, 0, 1, true, 'C', true);
    $pdf->LN(6);
    $pdf->writeHTMLCell(270, 0, 10, '', "CONTO DELLA GESTIONE DELL'AGENTE CONTABILE: SARIDA S.r.l. - ANNO " . $_SESSION['year'], 0, 0, 1, true, 'C', true);

    $pdf->LN(15);


    $y = $pdf->getY();
    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->writeHTMLCell(135, 4, 10, $y, "Estremi riscossione", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(135, 4, 145, $y, "Versameno in tesoreria", 1, 0, 1, true, 'C', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(20, 4, 10, $y, "N Ord.", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(40, 4, 30, $y, "Periodo riscossione", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(40, 4, 70, $y, "Numero ricevute", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(35, 4, 110, $y, "Importo", 1, 0, 1, true, 'C', true);

    $pdf->writeHTMLCell(50, 4, 145, $y, "N. Quietanza", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(35, 4, 195, $y, "Importo", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(50, 4, 230, $y, "Note (1)", 1, 0, 1, true, 'C', true);


    $pdf->LN(4);





    while ($r_Payment = mysqli_fetch_array($rs_Payment)) {

        $n_CountRow++;

        if($n_CountRow<$r_Payment['PaymentMonth']){
            for($i=$n_CountRow; $i<$r_Payment['PaymentMonth'];$i++ ){
                $y = $pdf->getY();

                $pdf->writeHTMLCell(20, 4, 10, $y, $n_CountRow, 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(40, 4, 30, $y, $a_Month[$i], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(40, 4, 70, $y, "", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(35, 4, 110, $y, "", 1, 0, 1, true, 'C', true);

                $pdf->writeHTMLCell(50, 4, 145, $y, "", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(35, 4, 195, $y, "", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(50, 4, 230, $y, "", 1, 0, 1, true, 'C', true);

                $pdf->LN(4);
            }

            $n_CountRow=$r_Payment['PaymentMonth'];
        }



        $y = $pdf->getY();


        $n_TotalPaymentMonth = $r_Payment['TotalPayment'];
        $f_TotalAmountMonth = $r_Payment['TotalAmount'];

        if(isset($a_Refund[$n_CountRow])){
            $a_RefundMonth = $a_Refund[$n_CountRow];
            $n_TotalPaymentMonth += $a_RefundMonth['tot'];
            $f_TotalAmountMonth -= $a_RefundMonth['amount'];
        }



        $pdf->writeHTMLCell(20, 4, 10, $y, $r_Payment['PaymentMonth'], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(40, 4, 30, $y, $a_Month[$r_Payment['PaymentMonth']], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(40, 4, 70, $y, $n_TotalPaymentMonth, 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(35, 4, 110, $y, $f_TotalAmountMonth, 1, 0, 1, true, 'C', true);


        $n_TotalPayment += $n_TotalPaymentMonth;
        $f_TotalAmount += $f_TotalAmountMonth;



        $pdf->writeHTMLCell(50, 4, 145, $y, "", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(35, 4, 195, $y, "", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(50, 4, 230, $y, "", 1, 0, 1, true, 'C', true);

        $pdf->LN(4);


    }

    $y = $pdf->getY();

    $pdf->writeHTMLCell(40, 4, 30, $y, "TOTALE", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(40, 4, 70, $y, $n_TotalPayment, 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(35, 4, 110, $y, $f_TotalAmount, 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(35, 4, 195, $y, "", 1, 0, 1, true, 'C', true);


    $pdf->LN(20);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(40, 4, 10, $y, "Sestri levante lì", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(80, 4, 140, $y, "L'AGENTE CONTABILE", 0, 0, 1, true, 'C', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(100, 4, 10, $y, "Il presente conto contiene n° " . $n_TotalPayment . " registrazioni in ____ pagine", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(80, 4, 140, $y, "SARIDA S.r.l.", 0, 0, 1, true, 'C', true);
    $pdf->LN(10);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(100, 4, 10, $y, "VISTO DI REGOLARITA'", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(80, 4, 140, $y, "IL RESPONSABILE DEL SERVIZIO FINANZIARIO", 0, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(50, 4, 220, $y, "Timbro", 0, 0, 1, true, 'C', true);
    $pdf->LN(10);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(100, 4, 10, $y, "____________________ lì _________________", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(80, 4, 140, $y, "_________________________________________", 0, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(50, 4, 220, $y, "dell'ente", 0, 0, 1, true, 'C', true);
    $pdf->LN(10);




    $str_Note = "
        NOTE(2): Gli importi riscossi sono comprensivi delle spese di notifica/ricerca e non comprendono le riscossioni effettuate direttamente dagli
        incaricati del Comune, addetti al servizio di Polizia. Gli importi riversati all'ente sono decurtati delle spese di notifica, di ricerca se anticipate dalla
        SARIDA, oltre che dell'importo dell'IVA per la parte applicabile.
    ";

    $y = $pdf->getY();
    $pdf->writeHTMLCell(280, 8, 10, $y, $str_Note, 1, 0, 1, true, 'L', true);


    $FileName = $_SESSION['cityid'] . '_conto_giudiziale_' . date("Y-m-d_H-i") . '.pdf';

    $pdf->Output(ROOT . "/doc/print/payment/" . $FileName, "F");
    $_SESSION['Documentation'] = $MainPath . '/doc/print/payment/' . $FileName;

    header("location: ".$P);

}



