<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_installments.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");

global $rs;

$str_Folder = PRINT_FOLDER.'/rateizzazioni';
$str_Folder_html = PRINT_FOLDER_HTML.'/rateizzazioni';

if (!is_dir($str_Folder)) {
    mkdir($str_Folder, 0770, true);
    chmod($str_Folder, 0770);
}

$PrintType = CheckValue('PrintType', 's');

$FileName = "{$_SESSION['cityid']}_rateizzazioni_".date('Y-m-d')."_".date('H-i');

$a_UsedFilters = mgmtInstallmentsUsedFilters();
$str_Where = mgmtInstallmentsWhere();
$str_Where .= " AND F.CityId='{$_SESSION['cityid']}'";

$cls_view = new CLS_VIEW(MGMT_INSTALLMENTS);

$query = $cls_view->generateSelect($str_Where, null);
$a_Results = $rs->getResults($rs->SelectQuery($query)) ?: array();

switch ($PrintType){
    case MGMT_INSTALLMENTS_ACTION_PRINTPDF:{
        $FileName .= '.pdf';
        $rowsPerPage = 9;
        //Indica quante righe scartare nella prima pagina dato che c'è l'intestazione
        $rowsFirstPageSubtract = 2;
        $count = 0;
        $countFilter = 0;
        
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Rateizzazioni');
        $pdf->SetPrintHeader(false);
        $pdf->SetMargins(10,10,10);
        $pdf->setCellHeightRatio(1.5);
        $pdf->setCellPaddings(1, 0.5, 1, 0.5);
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $cellsHeight = 9.5;
        $cellsHeightNarrow = 4.75;
        
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 0));
        
        $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).'</strong></h3>', true, false, true, false, '');
        $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>REPORT RATEIZZAZIONI</strong></h3>', true, false, true, false, '');
        $pdf->LN(1);
        $pdf->writeHTML('<p style="margin:0;">Stampato il '.date('d/m/Y').'</p>', true, false, true, false, 'C');
        $pdf->LN(6);
        $pdf->writeHTML('<h5 style="margin:0;"><strong>OPZIONI SELEZIONATE AL MOMENTO DELLA STAMPA:</strong></h5>', true, false, true, false, '');
        $pdf->LN(3);
        
        $pdf->SetFont('helvetica', '', 8);
        
        foreach ($a_UsedFilters as $filterName => $filterValue){
            $countFilter++;
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 4), 0, '', '' , "$filterName : $filterValue", 1, (next($a_UsedFilters) === false || $countFilter % 3 == 0) ? 1 : 0, 0, true, 'L', true);
        }
        
        $pdf->LN(5);
        
        $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 5));
        $pdf->SetFont('courier', 'B', 7);
        
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'N.<br>Data chiusura', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Cron.', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Tipo', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 'Nome<br>Posizione', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 'Trasgressore', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Data richiesta', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Data dec. prima rata', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Imp. Totale', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'N. Rate<br>Interesse %', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Imp. Singola rata', 1, 1, 0, true, 'L', true);
        
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeightNarrow, '', '' , 'Richiesta stampata', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeightNarrow, '', '' , 'Esito stampato', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeightNarrow, '', '' , 'Boll. Stampati', 1, 0, 0, true, 'L', true);
        $pdf->writeHTMLCell(pdfColumnSize($pdf, 8), $cellsHeightNarrow, '', '' , '', 1, 1, 0, true, 'L', true);
        
        foreach($a_Results as $result){
            $r_PaymentRateNumber = $rs->getArrayLine($rs->Select("PaymentRateNumber", "PaymentRateId = {$result['Id']} AND RateNumber=1"));
            
            switch($result['RequestOutcome']){
                case null: $requestOutcomeText = '(esito: in attesa)'; break;
                case 0: $requestOutcomeText = '(esito: respinta)'; break;
                case 1: $requestOutcomeText = '(esito: accolta)'; break;
                default: $requestOutcomeText = '';
            }
            
            $count ++;
            
            $pdf->SetFont('courier', '', 8);
            
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 
                $count.
                ($result["StatusRateId"] == RATEIZZAZIONE_CHIUSA ? "<br>".DateOutDB($result['ClosingDate']) : ""), 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , $result['ProtocolId'].'/'.$result['ProtocolYear'], 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , $result['DocumentTypeTitle'], 1, 0, 0, true, 'L', true);
            $pdf->SetFont('courier', '', 7);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 
                $result['RateName']."<br>".
                $result['Position'], 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , $result['TrespasserFullName'], 1, 0, 0, true, 'L', true);
            $pdf->SetFont('courier', '', 8);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , DateOutDB($result["RequestDate"]), 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , DateOutDB($result["StartDate"]), 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , "€ ".number_format($result['InstalmentAmount'], 2, ',', '.')."<br>", 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 
                $result['InstalmentNumber']."<br>".
                number_format($result['InterestsPercentual'], 2, ",", ".")." %", 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , ($r_PaymentRateNumber ? "€ ".number_format($r_PaymentRateNumber['Amount'], 2, ',', '.') : ""), 1, 1, 0, true, 'L', true);
            
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeightNarrow, '', '' , $result['RequestStatusId'] >= 1 ? "SI ".$requestOutcomeText : "NO", 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeightNarrow, '', '' , $result['ResponseStatusId'] >= 1 ? "SI" : "NO", 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeightNarrow, '', '' , $result['BillStatusId'] >= 1 ? "SI" : "NO", 1, 0, 0, true, 'L', true);
            $pdf->writeHTMLCell(pdfColumnSize($pdf, 8), $cellsHeightNarrow, '', '' , '', 1, 1, 0, true, 'L', true);
            
            if(($pdf->PageNo() > 1 ? $count-$rowsPerPage+$rowsFirstPageSubtract : $count) % ($pdf->PageNo() > 1 ? $rowsPerPage : $rowsPerPage-$rowsFirstPageSubtract) == 0 && $count < count($a_Results)){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
                
                $pdf->AddPage();
                
                $pdf->SetFont('helvetica', '', 10);
                $pdf->writeHTML('<h2 style="margin:0;"><strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).'</strong></h2>', true, false, true, false, '');
                $pdf->SetLineStyle(array('width' => 0.2, 'dash' => 0));
                $pdf->Line(10, 20, pdfColumnSize($pdf, 12)+10, 20);
                $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 5));
                $pdf->LN(8);
                
                $pdf->SetFont('courier', 'B', 7);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'N.<br>Data chiusura', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Cron.', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Tipo', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 'Nome<br>Posizione', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeight, '', '' , 'Trasgressore', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Data richiesta', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Data dec. prima rata', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Imp. Totale', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'N. Rate<br>Interesse %', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , 'Imp. Singola rata', 1, 1, 0, true, 'L', true);
                
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), $cellsHeightNarrow, '', '' , 'Richiesta stampata', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeightNarrow, '', '' , 'Esito stampato', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeightNarrow, '', '' , 'Boll. Stampati', 1, 0, 0, true, 'L', true);
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 8), $cellsHeightNarrow, '', '' , '', 1, 1, 0, true, 'L', true);
                
            } else if($count == count($a_Results)){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
            }
        }
        
        $pdf->Output($str_Folder.'/'.$FileName, "F");
        $_SESSION['Documentation'] = $str_Folder_html.'/'.$FileName;
        
        break;
    }
    
    case MGMT_INSTALLMENTS_ACTION_PRINTXLS:{
        $FileName .= '.xls';
        ob_start();
        $n_Count = 1; ?>
    	<table>
    		<tr></tr>
			<tr></tr>
    		<tr><td>REPORT Rateizzazioni</td></tr>
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
    			<th colspan="1">Tipo</th>
    			<th colspan="3">Nome</th>
    			<th colspan="3">Posizione</th>
    			<th colspan="4">Trasgressore</th>
    			<th colspan="1">N. Rate</th>
    			<th colspan="1">Interesse %</th>
    			<th colspan="2">Imp. Totale</th>
    			<th colspan="2">Data richiesta</th>
    			<th colspan="2">Data dec. Prima rata</th>
    			<th colspan="2">Imp. Singola rata</th>
    			<th colspan="2">Richiesta stampata</th>
    			<th colspan="2">Esito stampato</th>
    			<th colspan="2">Boll. Stampati</th>
        	</tr>
        	<?php foreach($a_Results as $result): ?>
        	<?php 
            	$r_PaymentRateNumber = $rs->getArrayLine($rs->Select("PaymentRateNumber", "PaymentRateId = {$result['Id']} AND RateNumber=1"));
            	
            	switch($result['RequestOutcome']){
            	    case null: $requestOutcomeText = '(esito: in attesa)'; break;
            	    case 0: $requestOutcomeText = '(esito: respinta)'; break;
            	    case 1: $requestOutcomeText = '(esito: accolta)'; break;
            	    default: $requestOutcomeText = '';
            	}
        	?>
    		<tr>
    			<td colspan="1"><?= $n_Count++; ?></td>
    			<td colspan="2"><?= $result['ProtocolId'].'/'.$result['ProtocolYear']; ?></td>
    			<td colspan="1"><?= $result['DocumentTypeTitle']; ?></td>
    			<td colspan="3"><?= $result['RateName']; ?></td>
    			<td colspan="3"><?= $result['Position']; ?></td>
    			<td colspan="4"><?= $result['TrespasserFullName']; ?></td>
    			<td colspan="1"><?= $result['InstalmentNumber']; ?></td>
    			<td colspan="1"><?= number_format($result['InterestsPercentual'], 2, ",", ".")." %"; ?></td>
    			<td colspan="2"><?= "€ ".number_format($result['InstalmentAmount'], 2, ',', '.'); ?></td>
    			<td colspan="2"><?= DateOutDB($result['RequestDate']); ?></td>
    			<td colspan="2"><?= DateOutDB($result['StartDate']); ?></td>
    			<td colspan="2"><?= ($r_PaymentRateNumber ? "€ ".number_format($r_PaymentRateNumber['Amount'], 2, ',', '.') : ""); ?></td>
    			<td colspan="2"><?= $result['RequestStatusId'] >= 1 ? "SI ".$requestOutcomeText : "NO"; ?></td>
    			<td colspan="2"><?= $result['ResponseStatusId'] >= 1 ? "SI" : "NO"; ?></td>
    			<td colspan="2"><?= $result['BillStatusId'] >= 1 ? "SI" : "NO"; ?></td>
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

header("location: ".impostaParametriUrl(array('Filter' => 1), "prn_installments.php".$str_GET_Parameter));