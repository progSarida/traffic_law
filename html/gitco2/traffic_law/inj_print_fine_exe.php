<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_inj_print_fine.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");

global $rs;

if (!is_dir(PRINT_FOLDER . '/role_fine_anomalies')) {
    mkdir(PRINT_FOLDER . '/role_fine_anomalies');
    chmod(PRINT_FOLDER . '/role_fine_anomalies', 0750);
}

$Action = CheckValue('Action', 's');

$a_FineTypes = unserialize(INJ_PRINT_FINE_FINETYPES);

$rs_VehicleType = $rs->SelectQuery('SELECT Id,TitleIta FROM VehicleType');
$a_VehicleTypes = array_column($rs->getResults($rs_VehicleType), 'TitleIta', 'Id');

$cls_view = new CLS_VIEW(INJ_FINE);

$a_WhereHaving = injPrintFineWhereHaving();
$a_OrderBy = injPrintFineOrderBy();

$query = $cls_view->generateSelect($a_WhereHaving['Where'], $a_WhereHaving['Having'], $a_OrderBy['OrderBy']);
$rs_Fine = $rs->SelectQuery($query);
$RowNumber = mysqli_num_rows($rs_Fine);

$FileName = "{$_SESSION['cityid']}_role_fine_anomalies_".date('Y-m-d')."_".date('H-i-s');

switch ($Action){
    case 'Pdfaaa':{
        $FileName .= '.pdf';
        $count = 0;
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
        
        $pdf->AddPage();
        
        $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>REPORT LAVORAZIONI UTENTI: '.$a_ExtractTypes[$Search_Type]['Name'].'</strong></h3>', true, false, true, false, '');
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
        
        pdfPrintPageHeader($pdf, $a_Fields, $a_ExtractTypesViews[$Search_Type]['ColSizes'], $cellsHeight);
        
        while($r_Table = array_values(array_diff_key($rs->getArrayLine($rs_Table, MYSQLI_NUM) ?? array(), $a_FieldsToExclude))){
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
                    
                    $pdf->writeHTMLCell(pdfColumnSize($pdf, $a_ExtractTypesViews[$Search_Type]['ColSizes'][$index] ?? 12), $cellsHeight, '', '' , implode($a_fieldValues, '<br>'), 1, next($r_Table) === false ? 1 : 0, 0, true, 'L', true);
            }
            
            if($count % $rowsPerPage == 0 && $count < $RowNumber){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
                
                $pdf->AddPage();
                
                $pdf->SetFont('courier', 'B', 8);
                pdfPrintPageHeader($pdf, $a_Fields, $a_ExtractTypesViews[$Search_Type]['ColSizes'], $cellsHeight);
                
            } else if($count == $RowNumber){
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 12), 10, '', '' , "<strong>Totale Generale: $count</strong>", 1, 1, 0, true, 'L', true);
            }
        }
        
        $pdf->Output(PRINT_FOLDER.'/'.$FileName, "F");
        $_SESSION['Documentation'] = PRINT_FOLDER_HTML.'/'.$FileName;
        
        break;
    }
    
    case 'Excel':{
        //Estensione file
        $FileName .= '.xls';
        $n_Count = 1;
        ob_start(); ?>
        	<table>
        		<tr><td>RUOLO: STAMPA ELENCO ANOMALIE VERBALI</td></tr>
    			<tr></tr>
    			<tr><td>Filtri:</td></tr>
    			<tr></tr>
    			<?php foreach($a_WhereHaving['UsedFilters'] as $filterName => $filterValues): ?>
    				<tr>
    					<td><?= $filterName; ?>:</td>
    					<?php if(is_array($filterValues)): ?>
        					<?php foreach($filterValues as $filterValue): ?>
        						<td><?= $filterValue; ?></td>
        					<?php endforeach; ?>
    					<?php else: ?>
    						<td><?= $filterValues; ?></td>
						<?php endif; ?>
    				</tr>
    			<?php endforeach; ?>
    			<tr></tr>
        		<tr><td>Elenco ordinato per:</td></tr>
    			<?php foreach($a_OrderBy['UsedOrders'] as $orderName => $orderValue): ?>
    				<tr>
    					<td><?= $orderName; ?>:</td>
    					<td><?= $orderValue; ?></td>
    				</tr>
    			<?php endforeach; ?>
        		<tr></tr>
        		<tr><td>Risultati: <?= $RowNumber ?></td></tr>
        		<tr></tr>
    		</table>
    		<table border="1">
        		<tr bgcolor="lightblue">
        			<th>Prog.</th>
        			<th>Riferimento.</th>
        			<th>Tipo atto</th>
        			<th>Data acc.</th>
        			<th>Ora acc.</th>
        			<th>Targa</th>
        			<th>Tipo veicolo</th>
        			<th>Proprietario/Obbligato/Noleggio</th>
        			<th>Data notifica</th>
        			<th>Trasgressore/Noleggiante</th>
        			<th>Data notifica</th>
            	</tr>
    			<?php while($r_Fine = $rs->getArrayLine($rs_Fine)):?>
					<?php 
					$a_Trespasser = injPrintFineGroupResults(
    			       'TrespasserTypeId', 
    				    array(
    			            'TrespasserTypeId' => $r_Fine['TrespasserTypeId'], 
    			            'TrespasserFullName' => $r_Fine['TrespasserFullName'], 
    			            'TrespasserId' => $r_Fine['TrespasserId'],
    				        'DeliveryDate' => $r_Fine['DeliveryDate']
    				    ));
					$TrespasserFullName_1 = $a_Trespasser[1]['TrespasserFullName'] ?? $a_Trespasser[2]['TrespasserFullName'] ?? $a_Trespasser[10]['TrespasserFullName'] ?? '';
					$TrespasserFullName_2 = $a_Trespasser[3]['TrespasserFullName'] ?? $a_Trespasser[11]['TrespasserFullName'] ?? '';
					$NotificationDate_1 = $a_Trespasser[1]['DeliveryDate'] ?? $a_Trespasser[2]['DeliveryDate'] ?? $a_Trespasser[10]['DeliveryDate'] ?? '';
					$NotificationDate_2 = $a_Trespasser[3]['DeliveryDate'] ?? $a_Trespasser[11]['DeliveryDate'] ?? '';
					?>
        			<tr>
        				<th><?= $n_Count++ ?></th>
						<td><?= $r_Fine['Code'] ?></td>
						<td><?= $a_FineTypes[$r_Fine['FineTypeId']] ?></td>
						<td><?= DateOutDB($r_Fine['FineDate']) ?></td>
						<td><?= TimeOutDB($r_Fine['FineTime']) ?></td>
						<td><?= $r_Fine['VehiclePlate'] ?></td>
						<td><?= $a_VehicleTypes[$r_Fine['VehicleTypeId']] ?></td>
						<th><?= $TrespasserFullName_1 ?></th>
						<th><?= $NotificationDate_1 ?></th>
						<th><?= $TrespasserFullName_2 ?></th>
						<th><?= $NotificationDate_2 ?></th>
        			</tr>
    			<?php endwhile; ?>
    		</table>
        	<?php
        	$table = ob_get_clean();
        	
        	//Scrive il report in formato xls sul file system
        	file_put_contents(PRINT_FOLDER.'/role_fine_anomalies/'.$FileName, "\xEF\xBB\xBF".$table);
        	//Carica il percorso del file in sessione
        	$_SESSION['Documentation'] = PRINT_FOLDER_HTML.'/role_fine_anomalies/'.$FileName;
        	break;
    }
}

header("location: ".impostaParametriUrl(array('Filter' => 1), "inj_print_fine.php".$str_GET_Parameter));