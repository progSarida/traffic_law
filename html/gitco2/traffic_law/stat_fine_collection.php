<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_stat_fine_collection.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

/** @var $rs CLS_DB */

$Filter = CheckValue('Filter', 'n');
$PageTitle = CheckValue('PageTitle', 's');

$a_Months = unserialize(STAT_FINE_COLLECTION_MONTHS);
$a_ViolationType = unserialize(STAT_FINE_COLLECTION_VIOLATIONTYPE);

if ($Filter == 1){
    $str_Where = statFineCollectionWhere();
    
    $NextYear = $Search_Year+1;
    $a_MonthsToSearch = array_slice($a_Months, 0, array_search($Search_Month,array_keys($a_Months)) + 1, true);
    $FromDate = date('Y-m-d', strtotime("first day of january $Search_Year"));
    $ToDate = date('Y-m-t', strtotime("$Search_Year-$Search_Month-01"));
    
//DATI PRIMA TABELLA/////////////////
    //Incluse: determina se i valori della riga verranno sommati o no all riga delle somme totali (Sanzioni ACCERTATE: aggiornate)
    //Negative: determina se i valori della riga devono essere negativi
    $a_ViewsFirstTable = array(
        'Sanzioni ACCERTATE TOTALI' =>
        array('View' => STAT_FINE_COLLECTION_ACCERTATE, 'Where' => $str_Where, 'Incluse' => false, 'Negative' => false),
        'Sanzioni ACCERTATE INIVIATE' =>
        array('View' => STAT_FINE_COLLECTION_INVIATE, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => false),
        'RIDUZIONE 30%: VERBALI pag. nei 5gg dalla notifica o senza' =>
        array('View' => STAT_FINE_COLLECTION_RIDUZIONE30, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        'Verbali ARCHIVIATI' =>
        array('View' => STAT_FINE_COLLECTION_ARCHIVIATI, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        'Verbali RINOTIFICATI' =>
        array('View' => STAT_FINE_COLLECTION_RINOTIFICATI, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        'Verbali NON NOTIFICATI SOLO STRANIERI' =>
        array('View' => STAT_FINE_COLLECTION_NONOT_STRANIERI, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        'Verbali NON NOTIFICATI/STAM/RIST.' =>
        array('View' => STAT_FINE_COLLECTION_NONOT_STAM_RIST, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        'VERBALI INVIATI IN ATTESA DI NOTIFICA' =>
        array('View' => STAT_FINE_COLLECTION_WAITING, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        'VERBALI INESITATI DA OLTRE 90 GG DALLA CREAZIONE DEL FLUSSO' =>
        array('View' => STAT_FINE_COLLECTION_EXPIRED, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
        "Verbali accertati nel $Search_Year e notificati nel $NextYear" =>
        array('View' => STAT_FINE_COLLECTION_ACC2022_NOT2023, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => false),
        'DIFFERENZA sanzione in misura ridotta e importo a ruolo' =>
        array('View' => STAT_FINE_COLLECTION_DIFFSANZ_RID_RUOLO, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => false)
    );

    $a_RowsFirstTable = array();
        
    foreach($a_ViewsFirstTable as $rowTitle => $view){
        $a_Row = array();
        $RowSum = 0;
        
        $a_Row['Title'] = $rowTitle;
        $a_Row['Incluse'] = $view['Incluse'];
        $cls_view = new CLS_VIEW($view['View']);
        
        $a_ToReplace = array(
            "@Ente" => $_SESSION['cityid'],
            "@FromDate" => $FromDate,
            "@ToDate" => $ToDate,
            "@ProtocolYear" => $Search_Year,
            "@ProtocolNextYear" => $NextYear
        );
        
        
        $query = strtr($cls_view->generateSelect($view['Where']), $a_ToReplace);
        $a_Results = $rs->getResults($rs->SelectQuery($query));
        $a_Results = array_column($a_Results, null, 'Month');
        
        foreach(array_keys($a_Months) as $monthNumber){
            if(key_exists($monthNumber, $a_MonthsToSearch)){
                $monthValue = $a_Results[$monthNumber]['Sum'] ?? 0;
                
                if($monthValue > 0 && $view['Negative']){
                    $monthValue *= -1;
                }
                
                $a_Row[$monthNumber] = $monthValue;
                $RowSum += $monthValue;
            }
        }
        $a_Row['RowSum'] = $RowSum;
        $a_RowsFirstTable[] = $a_Row;
    }
    
    //RIGA DELLA SOMMA DELLE COLONNE
    $a_Row = array('Title' => 'Sanzioni ACCERTATE: aggiornate');
    foreach($a_RowsFirstTable as $row){
        $b_include = $row['Incluse'];
        //Esclude i primi due indici, quello del titolo e dell'inclusione nella somma
        foreach (array_slice($row, 2, count($row), true) as $column => $value){
            $a_Row[$column] = isset($a_Row[$column]) ? ($a_Row[$column] + ($b_include ? $value : 0)) : ($b_include ? $value : 0);
        }
    }
    
    $a_RowsFirstTable[] = $a_Row;
//////////////////////////////////////
    
//DATI SECONDA TABELLA/////////////////
    $a_ViewsSecondTable = array(
        "Sanzioni $Search_Year incassate nel $Search_Year" =>
        array('View' => STAT_FINE_COLLECTION_SANZ_INC_ANNO_COMP, 'Where' => $str_Where, 'Year' => $Search_Year, 'NextYear' => $Search_Year, 'Negative' => false),
        "Sanzioni $Search_Year incassate nel $NextYear" =>
        array('View' => STAT_FINE_COLLECTION_SANZ_INC_ANNO_COMP, 'Where' => $str_Where, 'Year' => $Search_Year, 'NextYear' => $NextYear, 'Negative' => false),
    );
    
    $a_SecondTable = array(
        'Per data di pagamento' => array(
            'Views' => $a_ViewsSecondTable,
            'ToReplace' => array(
                "@PaymentDateColumn" => 'FP.PaymentDate'
            )
        ),
        'Per data di accredito' => array(
            'Views' => $a_ViewsSecondTable,
            'ToReplace' => array(
                "@PaymentDateColumn" => 'FP.CreditDate'
            )
        ),
    );
    
    $a_RowsSecondTable = array();
    
    foreach($a_SecondTable as $sectionTitle => $data){
        
        $a_RowsSecondTable[$sectionTitle] = array();
        
        foreach ($data['Views'] as $rowTitle => $view){
            $a_Row = array();
            $RowSum = 0;
            
            $a_Row['Title'] = $rowTitle;
            $cls_view = new CLS_VIEW($view['View']);
            
            $a_ToReplace = array(
                "@Ente" => $_SESSION['cityid'],
                "@FromDate" => $FromDate,
                "@ToDate" => $ToDate,
                "@ProtocolYear" => $view['Year'],
                "@ProtocolNextYear" => $view['NextYear'],
            );
            
            $a_ToReplace = array_merge($a_ToReplace, $data['ToReplace']);
            
            $query = strtr($cls_view->generateSelect($view['Where']), $a_ToReplace);
            $a_Results = $rs->getResults($rs->SelectQuery($query));
            $a_Results = array_column($a_Results, null, 'Month');
            
            foreach(array_keys($a_Months) as $monthNumber){
                if(key_exists($monthNumber, $a_MonthsToSearch)){
                    $monthValue = $a_Results[$monthNumber]['Sum'] ?? 0;
                    
                    if($monthValue > 0 && $view['Negative']){
                        $monthValue *= -1;
                    }
                    
                    $a_Row[$monthNumber] = $monthValue;
                    $RowSum += $monthValue;
                }
            }
            $a_Row['RowSum'] = $RowSum;
            $a_RowsSecondTable[$sectionTitle][] = $a_Row;
        }
    }
//////////////////////////////////////

//DATI TERZA TABELLA/////////////////
    $a_ViewsThirdTable = array(
        "Sanzioni incassate nel $Search_Year" =>
        array('View' => STAT_FINE_COLLECTION_SANZ_INC, 'Where' => $str_Where, 'Year' => $Search_Year, 'NextYear' => $Search_Year, 'Negative' => false),
        "Sanzioni incassate nel $Search_Year non associate" =>
        array('View' => STAT_FINE_COLLECTION_SANZ_INC_NON_ASSOC, 'Where' => null, 'Year' => $Search_Year, 'NextYear' => $Search_Year, 'Negative' => false),
    );
    
    $a_ThirdTable = array(
        'Per data di pagamento' => array(
            'Views' => $a_ViewsThirdTable,
            'ToReplace' => array(
                "@PaymentDateColumn" => 'FP.PaymentDate'
            )
        ),
        'Per data di accredito' => array(
            'Views' => $a_ViewsThirdTable,
            'ToReplace' => array(
                "@PaymentDateColumn" => 'FP.CreditDate'
            )
        ),
    );
    
    $a_RowsThirdTable = array();
    
    foreach($a_ThirdTable as $sectionTitle => $data){
        
        $a_RowsThirdTable[$sectionTitle] = array();
        
        foreach ($data['Views'] as $rowTitle => $view){
            $a_Row = array();
            $RowSum = 0;
            
            $a_Row['Title'] = $rowTitle;
            $cls_view = new CLS_VIEW($view['View']);
            
            $a_ToReplace = array(
                "@Ente" => $_SESSION['cityid'],
                "@FromDate" => $FromDate,
                "@ToDate" => $ToDate,
                "@ProtocolYear" => $view['Year'],
                "@ProtocolNextYear" => $view['NextYear'],
            );
            
            $a_ToReplace = array_merge($a_ToReplace, $data['ToReplace']);
            
            $query = strtr($cls_view->generateSelect($view['Where']), $a_ToReplace);
            $a_Results = $rs->getResults($rs->SelectQuery($query));
            $a_Results = array_column($a_Results, null, 'Month');
            
            foreach(array_keys($a_Months) as $monthNumber){
                if(key_exists($monthNumber, $a_MonthsToSearch)){
                    $monthValue = $a_Results[$monthNumber]['Sum'] ?? 0;
                    
                    if($monthValue > 0 && $view['Negative']){
                        $monthValue *= -1;
                    }
                    
                    $a_Row[$monthNumber] = $monthValue;
                    $RowSum += $monthValue;
                }
            }
            $a_Row['RowSum'] = $RowSum;
            $a_RowsThirdTable[$sectionTitle][] = $a_Row;
        }
    }
}
//////////////////////////////////////

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_search" action="stat_fine_collection.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
		
        <div class="col-sm-11">
        	<div class="col-sm-1 BoxRowLabel">
        		Anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_Year', 'Search_Year', $Search_Year, true);?>
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		Fino al mese di
        	</div>
        	<div class="col-sm-2 BoxRowCaption">
        		<?= CreateArraySelect($a_Months, true, 'Search_Month', 'Search_Month', $Search_Month, true);?>
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		Tipo di violazione
        	</div>
        	<div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_ViolationType, true, 'Search_Type', 'Search_Type', $Search_Type, true); ?>
            </div>
        	
        	<div class="col-sm-2 BoxRowLabel">
        	</div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="col-sm-12 BoxRowLabel">
        	</div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:50%;height:100%;"><i class="glyphicon glyphicon-search" style="font-size:2.5rem;"></i></button>
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:50%;height:100%;"><i class="fa fa-file-excel" style="font-size:2.5rem;"></i></button>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
	<?php if ($Filter != 1):?>
        <div class="table_caption_H col-sm-12 text-center">
        	Inserire criteri di ricerca
        </div>
	<?php else: ?>
	    <div class="table_label_H table_caption_I col-sm-12">STATISTICA CDS</div>
    
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2" style="padding:0">
        	<div class="table_label_H col-sm-12 font_small">Avvisi/Verbali emessi nel periodo:</div>
        </div>
        
        <div class="col-sm-9" style="padding:0">
            <div class="table_label_H col-sm-1" style="background-color:darkturquoise">Gennaio</div>
            <div class="table_label_H col-sm-1" style="background-color:darkturquoise">Febbraio</div>
            <div class="table_label_H col-sm-1" style="background-color:darkturquoise">Marzo</div>
            <div class="table_label_H col-sm-1" style="background-color:darkturquoise">Aprile</div>
            <div class="table_label_H col-sm-1" style="background-color:orange">Maggio</div>				
            <div class="table_label_H col-sm-1" style="background-color:orange">Giugno</div>
            <div class="table_label_H col-sm-1" style="background-color:orange">Luglio</div>
            <div class="table_label_H col-sm-1" style="background-color:orange">Agosto</div>
            <div class="table_label_H col-sm-1" style="background-color:lightgreen">Settembre</div>
            <div class="table_label_H col-sm-1"	style="background-color:lightgreen">Ottobre</div>
            <div class="table_label_H col-sm-1"	style="background-color:lightgreen">Novembre</div>
            <div class="table_label_H col-sm-1"	style="background-color:lightgreen">Dicembre</div>
        </div>
        <div class="col-sm-1" style="padding:0">
        	<div class="table_label_H col-sm-12">Tot.</div>
        </div>
        
        <div class="clean_row HSpace4"></div>
	
		<div class="table">
    		<?php foreach($a_RowsFirstTable as $index => $row): ?>
    			<div class="tableRow">
    	            <div class="col-sm-2" style="padding:0">
                    	<div class="table_caption_H table_caption_I col-sm-12" style="height:6rem;line-height:3rem;"><?= $row['Title']; ?></div>
                    </div>
                    <div class="col-sm-9" style="padding:0">
                    	<?php foreach (array_keys($a_Months) as $monthNumber): ?>
                    		<?php if(key_exists($monthNumber, $a_MonthsToSearch)): ?>
                    			<div data-row="<?= $index; ?>" data-month="<?= $monthNumber; ?>" class="table_caption_H col-sm-1 text-right<?= $row[$monthNumber] < 0 ? ' text-danger' : '' ?><?= ($index+1) == count($a_RowsFirstTable) ? ' table_caption_warning' : '' ?>" style="height:6rem;line-height:6rem;padding-right:0.5rem;text-align:right;"><?= formatCellValue($row[$monthNumber]); ?></div>
                    		<?php else: ?>
                    			<div data-row="<?= $index; ?>" data-month="<?= $monthNumber; ?>" class="table_caption_H col-sm-1 disabled" style="height:6rem;line-height:6rem;background-color:lightgrey;"></div>
                    		<?php endif; ?>
                    	<?php endforeach; ?>
                    </div>
                    <div class="col-sm-1" style="padding:0">
                    	<div data-row="<?= $index; ?>" data-month="Sum" class="table_caption_H table_caption_button col-sm-12<?= $row['RowSum'] < 0 ? ' text-danger' : '' ?><?= ($index+1) == count($a_RowsFirstTable) ? ' table_caption_warning' : '' ?>" style="height:6rem;line-height:6rem;padding-right:0.5rem;text-align:right;"><?= formatCellValue($row['RowSum']); ?></div>
                    </div>
    			</div>
    			
    			<div class="clean_row HSpace4"></div>
    		<?php endforeach;?>
		</div>
		
    	<div class="table_label_H table_caption_I col-sm-12">SANZIONI CDS RISCOSSE</div>
    
    	<div class="clean_row HSpace4"></div>
		
		<div class="table">
			<?php $indexThirdTable = 0; ?>
			<?php foreach($a_RowsThirdTable as $sectionTitle => $sectionRows): ?>
	    		<div class="table_label_H col-sm-12"><?= $sectionTitle; ?></div>
    		
    			<div class="clean_row HSpace4"></div>
    			
	    		<?php foreach($sectionRows as $row): ?>
	    			<?php $indexThirdTable ++; ?>
        			<div class="tableRow">
        	            <div class="col-sm-2" style="padding:0">
                        	<div class="table_caption_H table_caption_I col-sm-12" style="height:6rem;line-height:3rem;"><?= $row['Title']; ?></div>
                        </div>
                        <div class="col-sm-9" style="padding:0">
                        	<?php foreach (array_keys($a_Months) as $monthNumber): ?>
                        		<?php if(key_exists($monthNumber, $a_MonthsToSearch)): ?>
                        			<div data-row="<?= $indexThirdTable; ?>" data-month="<?= $monthNumber; ?>" class="table_caption_H col-sm-1" style="height:6rem;line-height:6rem;padding-right:0.5rem;text-align:right;"><?= formatCellValue($row[$monthNumber]); ?></div>
                        		<?php else: ?>
                        			<div data-row="<?= $indexThirdTable; ?>" data-month="<?= $monthNumber; ?>" class="table_caption_H col-sm-1 disabled" style="height:6rem;line-height:6rem;background-color:lightgrey;"></div>
                        		<?php endif; ?>
                        	<?php endforeach; ?>
                        </div>
                        <div class="col-sm-1" style="padding:0">
                        	<div data-row="<?= $indexThirdTable; ?>" data-month="Sum" class="table_caption_H table_caption_button col-sm-12" style="height:6rem;line-height:6rem;padding-right:0.5rem;text-align:right;"><?= formatCellValue($row['RowSum']); ?></div>
                        </div>
        			</div>
        			
        			<div class="clean_row HSpace4"></div>
        		<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
		
    	<div class="table_label_H table_caption_I col-sm-12">SANZIONI CDS RISCOSSE PER ANNO DI COMPETENZA</div>
    
    	<div class="clean_row HSpace4"></div>
		
		<div class="table">
			<?php $indexSecondTable = 0; ?>
			<?php foreach($a_RowsSecondTable as $sectionTitle => $sectionRows): ?>
	    		<div class="table_label_H col-sm-12"><?= $sectionTitle; ?></div>
    		
    			<div class="clean_row HSpace4"></div>
    			
	    		<?php foreach($sectionRows as $row): ?>
	    			<?php $indexSecondTable ++; ?>
        			<div class="tableRow">
        	            <div class="col-sm-2" style="padding:0">
                        	<div class="table_caption_H table_caption_I col-sm-12" style="height:6rem;line-height:3rem;"><?= $row['Title']; ?></div>
                        </div>
                        <div class="col-sm-9" style="padding:0">
                        	<?php foreach (array_keys($a_Months) as $monthNumber): ?>
                        		<?php if(key_exists($monthNumber, $a_MonthsToSearch)): ?>
                        			<div data-row="<?= $indexSecondTable; ?>" data-month="<?= $monthNumber; ?>" class="table_caption_H col-sm-1" style="height:6rem;line-height:6rem;padding-right:0.5rem;text-align:right;"><?= formatCellValue($row[$monthNumber]); ?></div>
                        		<?php else: ?>
                        			<div data-row="<?= $indexSecondTable; ?>" data-month="<?= $monthNumber; ?>" class="table_caption_H col-sm-1 disabled" style="height:6rem;line-height:6rem;background-color:lightgrey;"></div>
                        		<?php endif; ?>
                        	<?php endforeach; ?>
                        </div>
                        <div class="col-sm-1" style="padding:0">
                        	<div data-row="<?= $indexSecondTable; ?>" data-month="Sum" class="table_caption_H table_caption_button col-sm-12" style="height:6rem;line-height:6rem;padding-right:0.5rem;text-align:right;"><?= formatCellValue($row['RowSum']); ?></div>
                        </div>
        			</div>
        			
        			<div class="clean_row HSpace4"></div>
        		<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
<script type="text/javascript">
	$(document).ready(function () {
	    $(".tableRow .table_caption_H, .tableRow .table_caption_button").not('.disabled, .table_caption_I').mouseover(function(){
	    	var row = $(this).data('row');
	    	var month = $(this).data('month');
			$(this).closest('.table').find('[data-row="'+row+'"], [data-month="'+month+'"]').not('.disabled').css("background-color", "#cfeaf7c7");
        });
        $(".tableRow .table_caption_H, .tableRow .table_caption_button").not('.disabled, .table_caption_I').mouseout(function(){
	    	var row = $(this).data('row');
	    	var month = $(this).data('month');
			$(this).closest('.table').find('[data-row="'+row+'"], [data-month="'+month+'"]').not('.disabled').css("background-color", "");
        });
        
        $('#printExcel, #search').click(function () {
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        	if ($(this).attr("id") == 'printExcel'){
        		$('#f_search').attr('action', 'stat_fine_collection_exe.php');
        	}
        });
        
        $('#f_search').on('submit', function () {
        	$('#search, #printExcel').prop('disabled', true);
        });
    });
</script>
<?php
require_once(INC."/footer.php");