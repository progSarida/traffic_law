<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

function resultColour($r_FN, $rgb = false){
    if($r_FN['ResultId'] > 0){
        if(($r_FN['ResultId'] > 9 && $r_FN['ResultId'] < 21) || ($r_FN['ResultId'] == 21 && $r_FN['ValidatedAddress'] != 1) || $r_FN['ResultId'] == 23){
            return $rgb ? array(140,0,0) : 'red';
        } else return $rgb ? array(0,120,0) : 'green';
    } else return $rgb ? array(255,255,255) : '';
}

ini_set('max_execution_time', 3000);

$FlowId = CheckValue('FlowId','n');
$n_CSV  = CheckValue('CSV','n');
$Filters = CheckValue('Filters', 's');

$n_Count = 0;

$rs_Flow = $rs->Select('Flow', "Id=$FlowId");
$r_Flow = mysqli_fetch_assoc($rs_Flow);

$cls_view = new CLS_VIEW(MGMT_FLOW_DETAIL);
$rs_FineNotification = $rs->SelectQuery($cls_view->generateSelect("FH.FlowId=$FlowId", null, 'F.ProtocolId ASC'));

if($n_CSV){
    $filename="export.xls";
    header ("Content-Type: application/vnd.ms-excel");
    header ("Content-Disposition: inline; filename=$filename");
    
    $str_Content = '
    <table border="1">
        <tr>
            <td> Flusso N.</td>
            <td>'. '('.$r_Flow['CityId'].') '.$r_Flow['Number'].'/'.$r_Flow['Year'] .'</td>
            <td> Del</td>
            <td>'. DateOutDB($r_Flow['CreationDate']) .'</td>
            <td> Spedito il</td>
            <td>'. DateOutDB($r_Flow['SendDate']) .'</td>
         </tr>
    </table>
    <br><br>
    <table border="1">
        <tr>
            <td></td>
            <td>Cronologico</td>
            <td>Data Notifica</td>
            <td>Raccomandata</td>
            <td>Ricevuta ritorno</td>
            <td>Esito</td>
        </tr>';
    
    while ($r_FineNotification = $rs->getArrayLine($rs_FineNotification)) {
        
        $n_Count++;
        
        $str_Content .= '
        <tr>
            <td>
                '. $n_Count .'
            </td>
            <td>
                '. $r_FineNotification['ProtocolId'] .'/'. $r_FineNotification['ProtocolYear'] .'
            </td>
            <td>
                '. DateOutDB($r_FineNotification['NotificationDate']) .'
            </td>
            <td>
                '. $r_FineNotification['LetterNumber'] .'
            </td>
            <td>
                '. $r_FineNotification['ReceiptNumber'] .'
            </td>
            <td bgcolor='. resultColour($r_FineNotification) .'>
                '. $r_FineNotification['ResultTitle'] .'
            </td>
        </tr>';
    }
    
    $str_Content .= '</table>';
    
    echo $str_Content;
}else {
    require_once TCPDF . "/tcpdf.php";
    
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Request');
    $pdf->SetSubject('Request');
    $pdf->SetKeywords('');
    
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
    
    
    $n_Count = 0;
    $n_Row = 0;
    
    $n_ChangePage = 35;
    
    $pdf->writeHTMLCell(200, 0, 30, '', "Flusso N. ". '('.$r_Flow['CityId'].') '.$r_Flow['Number'].'/'.$r_Flow['Year'] ." Del ". DateOutDB($r_Flow['CreationDate']). " Spedito il ". DateOutDB($r_Flow['SendDate']), 0, 0, 1, true, 'C', true);
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
    
    
    $y = $pdf->getY();
    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(40, 4, 20, $y, "Cronologico", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(30, 4, 60, $y, "Data Notifica", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(50, 4, 90, $y, "Raccomandata", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(50, 4, 140, $y, "Ricevuta ritorno", 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(80, 4, 190, $y, "Esito", 1, 0, 1, true, 'C', true);
    
    $pdf->LN(4);
    
    while ($r_FineNotification = mysqli_fetch_array($rs_FineNotification)) {
        
        $n_Count++;
        $n_Row++;
        
        if ($n_Row > $n_ChangePage) {
            
            $pdf->AddPage();
            
            $pdf->LN(10);
            
            $y = $pdf->getY();
            $pdf->SetFont('arial', '', 8, '', true);
            $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(40, 4, 20, $y, "Cronologico", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 60, $y, "Data Notifica", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(50, 4, 90, $y, "Raccomandata", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(50, 4, 140, $y, "Ricevuta ritorno", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(80, 4, 190, $y, "Esito", 1, 0, 1, true, 'C', true);
            
            $pdf->LN(4);
            
            $pdf->SetFont('arial', '', 8, '', true);
            $n_Row = 0;
        }
        
        $y = $pdf->getY();
        
        $pdf->writeHTMLCell(10, 4, 10, $y, $n_Count, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(40, 4, 20, $y, $r_FineNotification['ProtocolId'] .'/'. $r_FineNotification['ProtocolYear'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 60, $y, DateOutDB($r_FineNotification['NotificationDate']), 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(50, 4, 90, $y, $r_FineNotification['LetterNumber'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 140, $y, $r_FineNotification['ReceiptNumber'], 1, 0, 1, true, 'L', true);
        
        call_user_func_array([$pdf, 'SetFillColor'], resultColour($r_FineNotification, true));
        $pdf->SetTextColor(255, 255, 255);
        $pdf->writeHTMLCell(80, 4, 190, $y, $r_FineNotification['ResultTitle'], 1, 0, 1, true, 'L', true);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->LN(4);
    }
    
    $FileName = $_SESSION['cityid'].'_flusso_'.date("Y-m-d_H-i").'.pdf';
    
    $pdf->Output(ROOT."/doc/print/flow/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/flow/'.$FileName;
    
    
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_flow.php', 'FlowId' => $FlowId), 'mgmt_flow_detail_viw.php'.$Filters));
}

