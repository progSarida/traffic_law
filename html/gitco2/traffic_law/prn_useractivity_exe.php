<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_prn_useractivity.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");

global $rs;

$a_ExtractTypes = unserialize(USERACTIVITY_EXTRACTTYPES);
$a_ExtractTypesViews = unserialize(USERACTIVITY_VIEWS);
$a_OrderOptions = unserialize(USERACTIVITY_ORDER_OPTIONS);
$a_OrderType = unserialize(USERACTIVITY_ORDER_TYPE);

$Action = CheckValue('Action', 's');

$FileName = "{$_SESSION['cityid']}_report_".
    str_replace(' ', '_', strtolower($Search_Type > 0 ? $a_ExtractTypes[$Search_Type]['Name'] : 'Tutti')).
    "_".date('Y-m-d')."_".date('H-i');

$a_ToExecute = $a_Results = array();



if($Search_Type > 0){
    $a_ToExecute[$Search_Type] = $a_ExtractTypesViews[$Search_Type];
} else {
    $a_ToExecute = $a_ExtractTypesViews;
}

$str_Where = prnUserActivityWhere();
$str_Order = prnUserActivityOrderBy();
$a_UsedFilters = prnUserActivityUsedFilters();

foreach ($a_ToExecute as $contentIndex => $content){
    $cls_view = new CLS_VIEW($content['View']);
    
    //Se sono previste delle union, vengono create le where per esse e applicate le sostituzioni definite in "ReplaceUnionWhere"
    if(isset($content['ReplaceUnionWhere'])){
        $a_UnionsWhere = array();
        foreach ($content['ReplaceUnionWhere'] as $unionName => $replacements){
            $a_UnionsWhere[$unionName] = strtr($str_Where, $replacements);
        }
        
        $cls_view->unionWheres = $a_UnionsWhere ?: null;
    }
    
    //Costruisce la query facendo le sostituzioni segnaposto => valore definite in "Replace"
    $query = strtr($cls_view->generateSelect($str_Where, null, $str_Order), $content['Replace']);
    $rs_Table = $rs->SelectQuery($query);
    $RowNumber = mysqli_num_rows($rs_Table);
    
    $a_Fields = array_column(mysqli_fetch_fields($rs_Table), 'name');
    $a_FieldsToExclude = preg_grep('/'.USERACTIVITY_PREF_COL_ESCLUSE.'/', $a_Fields);
    $a_Fields = array_values(array_diff_key($a_Fields, $a_FieldsToExclude));
    
    $a_Results[$contentIndex]['ResultSet'] = $rs_Table;
    $a_Results[$contentIndex]['RowNumber'] = $RowNumber;
    $a_Results[$contentIndex]['Fields'] = $a_Fields;
    $a_Results[$contentIndex]['FieldsToExclude'] = $a_FieldsToExclude;
}

switch ($Action){
    case 'Pdf':{
        $FileName .= '.pdf';
        $rowsPerPage = 8;
        
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
        
        $cellsHeight = 15;
        $maxCellTextLength = abs(50);
        
        foreach($a_Results as $resultIndex => $result){
            $count = 0;
            $pdf->AddPage();
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 0));
            
            $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>REPORT LAVORAZIONI UTENTI: '.$a_ExtractTypes[$resultIndex]['Name'].'</strong></h3>', true, false, true, false, '');
            $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>Elenco ordinato per: '.$a_OrderOptions[$Order_Name]['Name'].' - '.$a_OrderType[$Order_Type]['Name'].'</strong></h3>', true, false, true, false, '');
            $pdf->LN(1);
            $pdf->writeHTML('<h5 style="margin:0;"><strong>Filtri:</strong></h5>', true, false, true, false, '');
            $pdf->LN(3);
            
            $pdf->SetFont('helvetica', '', 8);
            
            foreach ($a_UsedFilters as $filterName => $filterValue){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 2), 0, '', '' , "$filterName : $filterValue", 1, next($a_UsedFilters) === false ? 1 : 0, 0, true, 'L', true);
            }
            
            $pdf->LN(5);
            
            $pdf->SetLineStyle(array('width' => 0.1, 'dash' => 5));
            $pdf->SetFont('courier', 'B', 8);
            
            pdfPrintPageHeader($pdf, $result['Fields'], $a_ExtractTypesViews[$resultIndex]['ColSizes'], $cellsHeight);
            
            while($r_Table = array_values(array_diff_key($rs->getArrayLine($result['ResultSet'], MYSQLI_NUM) ?? array(), $result['FieldsToExclude']))){
                $count ++;
                
                $pdf->SetFont('courier', '', 8);
                
                //Numero riga
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellsHeight, '', '' , $count, 1, 0, 0, true, 'L', true);
                
                foreach($r_Table as $index => $fieldValue){
                    $a_fieldValues = explode(' | ', $fieldValue);
                    
                    //Alcuni campo sono troppo lunghi, quindi si aggiungono i ... per i valori che superano $maxCellTextLength
                    array_walk($a_fieldValues, function(&$value, $key, $maxCellTextLength){
                        if(strlen($value) > $maxCellTextLength) {
                            $value = preg_replace("/^(.{1,$maxCellTextLength})(\s.*|$)/s", '\\1...', $value);
                        }
                    }, $maxCellTextLength);
                        
                        $pdf->writeHTMLCell(pdfColumnSize($pdf, $a_ExtractTypesViews[$resultIndex]['ColSizes'][$index] ?? 12), $cellsHeight, '', '' , implode($a_fieldValues, '<br>'), 1, next($r_Table) === false ? 1 : 0, 0, true, 'L', true);
                }
                
                if($count % $rowsPerPage == 0 && $count < $result['RowNumber']){
                    $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
                    
                    $pdf->AddPage();
                    
                    $pdf->SetFont('courier', 'B', 8);
                    pdfPrintPageHeader($pdf, $a_Fields, $a_ExtractTypesViews[$resultIndex]['ColSizes'], $cellsHeight);
                    
                } else if($count == $result['RowNumber']){
                    $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
                }
            }
        }
        
        $pdf->Output(PRINT_FOLDER.'/'.$FileName, "F");
        $_SESSION['Documentation'] = PRINT_FOLDER_HTML.'/'.$FileName;
        
        break;
    }
    
    case 'Excel':{
        //Estensione file
        $FileName .= '.xls';
        ob_start(); 
        foreach($a_Results as $resultIndex => $result){
            $n_Count = 1; ?>
        	<table>
	    		<tr></tr>
    			<tr></tr>
        		<tr><td>REPORT LAVORAZIONI UTENTI : <?= $a_ExtractTypes[$resultIndex]['Name']; ?></td></tr>
    			<tr></tr>
    			<tr><td>Filtri:</td></tr>
    			<?php foreach($a_UsedFilters as $filterName => $filterValue): ?>
    				<tr>
    					<td><?= $filterName; ?>:</td>
    					<td><?= $filterValue; ?></td>
    				</tr>
    			<?php endforeach; ?>
    			<tr></tr>
        		<tr>
        			<td>Elenco ordinato per: <?= $a_OrderOptions[$Order_Name]['Name'].' - '.$a_OrderType[$Order_Type]['Name']; ?></td>
    			</tr>
        		<tr></tr>
        		<tr><td>Risultati: <?= $RowNumber ?></td></tr>
        		<tr></tr>
    		</table>
    		<table border="1">
        		<tr bgcolor="lightblue">
        			<th colspan="1">Prog.</th>
        			<?php foreach($result['Fields'] as $fieldIndex => $fieldName): ?>
        				<th colspan="<?= $a_ExtractTypesViews[$resultIndex]['ColSizes'][$fieldIndex] + 1; ?>"><?= str_replace('|','<br>',$fieldName); ?></th>
        			<?php endforeach; ?>
            	</tr>
    			<?php while($r_Table = array_values(array_diff_key($rs->getArrayLine($result['ResultSet'], MYSQLI_NUM), $result['FieldsToExclude']))):?>
        			<tr>
        				<th colspan="1">
        					<?=$n_Count?>
        				</th>
            			<?php foreach($r_Table as $tableFieldIndex => $fieldValue):?>
            				<td colspan="<?= $a_ExtractTypesViews[$resultIndex]['ColSizes'][$tableFieldIndex] + 1; ?>">
            					<?=str_replace('|',' ',$fieldValue);?>
            				</td>
        				<?php endforeach;?>
        			</tr>
    				<?php $n_Count++;?>
    			<?php endwhile; ?>
    		</table>
    	<?php
        }
    	$table = ob_get_clean();
    	
    	//Scrive il report in formato xls sul file system
    	file_put_contents(PRINT_FOLDER.'/'.$FileName, "\xEF\xBB\xBF".$table);
    	//Carica il percorso del file in sessione
    	$_SESSION['Documentation'] = PRINT_FOLDER_HTML.'/'.$FileName;
    	break;
    }
}

header("location: ".impostaParametriUrl(array('Filter' => 1), "prn_useractivity.php".$str_GET_Parameter));