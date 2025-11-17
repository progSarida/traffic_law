<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_prn_validatedaddress.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");

global $rs;

$str_Folder = PRINT_FOLDER.'/notificati_cad';
$str_Folder_html = PRINT_FOLDER_HTML.'/notificati_cad';

if (!is_dir($str_Folder)) {
    mkdir($str_Folder, 0770, true);
    chmod($str_Folder, 0770);
}

$Action = CheckValue('Action', 's');

$FileName = "{$_SESSION['cityid']}_noitificati_cad_".date('Y-m-d')."_".date('H-i');

$a_UsedFilters = prnValidatedAddressUsedFilters();
$str_Where = prnValidatedAddressWhere();
$str_Where .= " AND F.CityId='{$_SESSION['cityid']}'";

$cls_view = new CLS_VIEW(PRN_VALIDATEDADDRESS);
$query = $cls_view->generateSelect($str_Where);
$a_Results = $rs->getResults($rs->SelectQuery($query));

switch ($Action){
    case 'Pdf':{
        $FileName .= '.pdf';
        $rowsPerPage = 16;
        //Indica quante righe scartare nella prima pagina dato che c'è l'intestazione
        $rowsFirstPageSubtract = 3;
        $count = 0;
        $countFilter = 0;
        
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('User Activity');
        $pdf->SetPrintHeader(false);
        $pdf->SetMargins(10,10,10);
        $pdf->setCellHeightRatio(1.5);
        $pdf->setCellPaddings(1, 0.5, 1, 0.5);
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $cellsHeight = 9.5;
        
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 0));
        
        $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>REPORT ATTI NOTIFICATI CAD</strong></h3>', true, false, true, false, '');
        $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>Ente: '.$_SESSION['citytitle']." ({$_SESSION['cityid']})".'</strong></h3>', true, false, true, false, '');
        $pdf->LN(1);
        $pdf->writeHTML('<h5 style="margin:0;"><strong>Filtri:</strong></h5>', true, false, true, false, '');
        $pdf->LN(3);
        
        $pdf->SetFont('helvetica', '', 8);
        
        foreach ($a_UsedFilters as $filterName => $filterValue){
            $countFilter++;
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), 0, '', '' , "$filterName : $filterValue", 1, next($a_UsedFilters) === false || $countFilter % 6 == 0 ? 1 : 0, 0, true, 'L', true);
        }
        
        $pdf->LN(5);
        
        $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 5));
        $pdf->SetFont('courier', 'B', 8);
        
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'N.', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Cron.', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 3), $cellsHeight, '', '' , 'Riferimento', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 'Dati atto', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 4), $cellsHeight, '', '' , 'Esito notifica', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Indirizzo validato', 1, 1, 0, true, 'L', true);
        
        foreach($a_Results as $result){
            $count ++;
            
            $pdf->SetFont('courier', '', 8);
            
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , $count, 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , $result['ProtocolId'].'/'.$result['ProtocolYear'], 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 3), $cellsHeight, '', '' , $result['Code'], 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , DateOutDB($result['FineDate']).' - '.TimeOutDB($result['FineTime']).' '.$result['VehiclePlate'], 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 4), $cellsHeight, '', '' , $result['ResultTitle'], 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , YesNoOutDB($result['ValidatedAddress']), 1, 1, 0, true, 'L', true);
            
            if(($pdf->PageNo() > 1 ? $count-$rowsPerPage+$rowsFirstPageSubtract :$count) % ($pdf->PageNo() > 1 ? $rowsPerPage : $rowsPerPage-$rowsFirstPageSubtract) == 0 && $count < count($a_Results)){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
                
                $pdf->AddPage();
                
                $pdf->SetFont('courier', 'B', 8);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'N.', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Cron.', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 3), $cellsHeight, '', '' , 'Riferimento', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 'Dati atto', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 4), $cellsHeight, '', '' , 'Esito notifica', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Indirizzo validato', 1, 1, 0, true, 'L', true);
                
            } else if($count == count($a_Results)){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
            }
        }
        
        $pdf->Output($str_Folder.'/'.$FileName, "F");
        $_SESSION['Documentation'] = $str_Folder_html.'/'.$FileName;
        
        break;
    }
    
    case 'Excel':{
        $FileName .= '.xls';
        ob_start(); 
        $n_Count = 1; ?>
    	<table>
    		<tr></tr>
			<tr></tr>
    		<tr><td>REPORT ATTI NOTIFICATI CAD</td></tr>
    		<tr><td>Ente: <?= $_SESSION['citytitle']." ({$_SESSION['cityid']})"; ?></td></tr>
			<tr></tr>
			<tr><td>Filtri:</td></tr>
			<?php foreach($a_UsedFilters as $filterName => $filterValue): ?>
				<tr>
					<td><?= $filterName; ?>:</td>
					<td><?= $filterValue; ?></td>
				</tr>
			<?php endforeach; ?>
			<tr></tr>
    		<tr></tr>
    		<tr><td>Risultati: <?= count($a_Results) ?></td></tr>
    		<tr></tr>
		</table>
		<table border="1">
    		<tr bgcolor="lightblue">
    			<th colspan="1">N.</th>
    			<th colspan="2">Cron.</th>
    			<th colspan="3">Riferimento</th>
    			<th colspan="3">Dati atto</th>
    			<th colspan="4">Esito notifica</th>
    			<th colspan="2">Indirizzo validato</th>
        	</tr>
        	<?php foreach($a_Results as $result): ?>
    		<tr>
    			<td colspan="1"><?= $n_Count++; ?></td>
    			<td colspan="2"><?= $result['ProtocolId'].'/'.$result['ProtocolYear']; ?></td>
    			<td colspan="3"><?= $result['Code']; ?></td>
    			<td colspan="3"><?= DateOutDB($result['FineDate']).' - '.TimeOutDB($result['FineTime']).' '.$result['VehiclePlate']; ?></td>
    			<td colspan="4"><?= $result['ResultTitle']; ?></td>
    			<td colspan="2"><?= YesNoOutDB($result['ValidatedAddress']); ?></td>
        	</tr>
        	<?php endforeach; ?>
		</table>
    	<?php
    	$table = ob_get_clean();
    	
    	//Scrive il report in formato xls sul file system
    	file_put_contents($str_Folder.'/'.$FileName, "\xEF\xBB\xBF".$table);
    	//Carica il percorso del file in sessione
    	$_SESSION['Documentation'] = $str_Folder_html.'/'.$FileName;
    	break;
    }
}

header("location: ".impostaParametriUrl(array('Filter' => 1), "prn_validatedaddress.php".$str_GET_Parameter));