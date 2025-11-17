<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_prn_useractivity.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

//PROBLEMI:
//Se un utente cambia nominativo, data la struttura delle tabelle, i dati nuovi si troveranno per il nuovo nome e non per quello vecchio

$PageTitle = CheckValue('PageTitle','s') ?: '/';
$Filter = CheckValue('Filter', 'n');

$a_ExtractTypes = unserialize(USERACTIVITY_EXTRACTTYPES);
$a_ExtractTypesViews = unserialize(USERACTIVITY_VIEWS);
$a_FineTypes = unserialize(USERACTIVITY_FINETYPES);
$a_OrderOptions = unserialize(USERACTIVITY_ORDER_OPTIONS);
$a_OrderType = unserialize(USERACTIVITY_ORDER_TYPE);

$a_ExtractTypesSelect = array_diff(array_combine(array_keys($a_ExtractTypes), array_column($a_ExtractTypes, 'Name')), [null]);
$a_OrderOptionsSelect = array_diff(array_combine(array_keys($a_OrderOptions), array_column($a_OrderOptions, 'Name')), [null]);
$a_OrderTypeSelect = array_diff(array_combine(array_keys($a_OrderType), array_column($a_OrderType, 'Name')), [null]);

$Order_Name = empty($Order_Name) ? 1 : $Order_Name;
$a_ToExecute = $a_Results = array();

if($Filter > 0){
    if($Search_Type > 0){
        $a_ToExecute[$Search_Type] = $a_ExtractTypesViews[$Search_Type];
    } else {
        $a_ToExecute = $a_ExtractTypesViews;
    }
    
    $str_Where = prnUserActivityWhere();
    $str_Order = prnUserActivityOrderBy();
    
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
        
        //Rimuove dalla lista dei nomi dei campi quelli restituiti dalla preg_grep e riordina gli indici del vettore
        $a_Fields = array_column(mysqli_fetch_fields($rs_Table), 'name');
        $a_FieldsToExclude = preg_grep('/'.USERACTIVITY_PREF_COL_ESCLUSE.'/', $a_Fields);
        $a_Fields = array_values(array_diff_key($a_Fields, $a_FieldsToExclude));
        
        $a_Results[$contentIndex]['ResultSet'] = $rs_Table;
        $a_Results[$contentIndex]['RowNumber'] = $RowNumber;
        $a_Results[$contentIndex]['Fields'] = $a_Fields;
        $a_Results[$contentIndex]['FieldsToExclude'] = $a_FieldsToExclude;
    }
}

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_prn_useractivity" action="prn_useractivity.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" id="Action" name="Action" value="">
		<input type="hidden" name="Filter" value="1">
		
		<div class="col-sm-11">
        	<div class="col-sm-1 BoxRowLabel font_small">
            	Tipo di estrazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_ExtractTypesSelect, true, 'Search_Type', 'Search_Type', $Search_Type, false); ?>
            </div>
        	<div class="col-sm-1 BoxRowLabel">
            	Utente
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateSelect(MAIN_DB.'.User', '1=1', 'UserName ASC', 'Search_UserName', 'UserName', 'UserName', $Search_UserName, false) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data reg.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromDate; ?>" name="Search_FromDate" id="Search_FromDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data reg.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToDate; ?>" name="Search_ToDate" id="Search_ToDate">
            </div>
            <div class="col-sm-3 BoxRowLabel">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
            	Ordina per
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?php foreach($a_OrderOptionsSelect as $value => $name): ?>
                	<input type="radio" name="Order_Name" value="<?= $value; ?>" style="top:0;"<?= $value == $Order_Name ? ' checked' : '' ?>>
                	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
            	Ordinamento
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_OrderTypeSelect, true, 'Order_Type', 'Order_Type', $Order_Type, true); ?>
            </div>
            <div class="col-sm-6 BoxRowLabel">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div id="SubFilters" class="col-sm-12 BoxRowLabel" style="padding:0">
				<div id="GRP_Search_CityId" class="<?= !in_array('Search_CityId', $a_ExtractTypes[$Search_Type]['SubFilters']) ? 'hidden' : '' ?>">
                	<div class="col-sm-1 BoxRowLabel">
                    	Ente
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    	<?= CreateSelectQuery('SELECT C.CityId CityId, CI.Title CityTitle FROM Customer C JOIN '.MAIN_DB.'.City CI ON C.CityId = CI.Id', 'Search_CityId', 'CityId', 'CityTitle', $Filter > 0  ? $Search_CityId : $_SESSION['cityid'], false); ?>
                    </div>
            	</div>
				<div id="GRP_Search_FineType" class="<?= !in_array('Search_FineType', $a_ExtractTypes[$Search_Type]['SubFilters']) ? 'hidden' : '' ?>">
                    <div class="col-sm-1 BoxRowLabel">
                        Tipologia atto
                    </div>
    	            <div class="col-sm-2 BoxRowCaption">
                        <?= CreateArraySelect($a_FineTypes, true, 'Search_FineType', 'Search_FineType', $Search_FineType, false); ?>
                    </div>
            	</div>
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:6.8rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        	<button type="submit" data-action="Excel" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        	<button type="submit" data-action="Pdf" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
        </div>

    	<div class="clean_row HSpace4"></div>

		<?php if($Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere il tipo di estrazione da effettuare.
			</div>
        <?php else: ?>
            <?php foreach($a_Results as $resultIndex => $result): ?>
            	<div class="table_caption_I col-sm-12 text-center"><?= $a_ExtractTypesSelect[$resultIndex] ?></div>
            	
            	<div class="clean_row HSpace4"></div>
            	
        		<div class="table_label_H col-sm-1" style="height:6rem;line-height:2rem;">N.</div>
        		<?php foreach($result['Fields'] as $fieldIndex => $fieldName): ?>
        			<div class="table_label_H <?= 'col-sm-'.($a_ExtractTypesViews[$resultIndex]['ColSizes'][$fieldIndex] ?? '12'); ?>" style="height:6rem;line-height:2rem;"><?= str_replace(' | ', '<br>', $fieldName); ?></div>
        		<?php endforeach; ?>    
        		
            	<?php if ($result['RowNumber'] > 0): ?>
            		<?php $count = 1; ?>
            		<?php while ($r_Table = array_values(array_diff_key($rs->getArrayLine($result['ResultSet'], MYSQLI_NUM) ?? array(), $result['FieldsToExclude']))): ?>
            			<div class="clean_row HSpace4"></div>
            			
                        <div class="tableRow">
                        	<div class="table_caption_H col-sm-1" style="height:4.4rem;line-height:1.5rem;"><?= $count++; ?></div>
                        	<?php foreach($r_Table as $tableFieldIndex => $fieldValue): ?>
    	                        <div class="table_caption_H <?= 'col-sm-'.($a_ExtractTypesViews[$resultIndex]['ColSizes'][$tableFieldIndex] ?? '12'); ?>" style="height:4.4rem;line-height:1.5rem">
                                	<?= str_replace(' | ', '<br>', $fieldValue); ?>
                            	</div>
                        	<?php endforeach; ?>
                        </div>
            		<?php endwhile; ?>
            		
                    <div class="clean_row HSpace4"></div>
            	
            	<?php else: ?>
        	        <div class="table_caption_H col-sm-12 text-center">
                    	Nessun record presente.
                    </div>
            	<?php endif; ?>
    		<?php endforeach; ?>
        <?php endif; ?>

	</form>
</div>
<script>
	$(document).ready(function () {
		var a_ExtractTypes = <?= json_encode($a_ExtractTypes); ?>;
	
		$('#Search_Type').on('change', function(){
			$("[id^=GRP_]").addClass('hidden');
			if($(this).val() in a_ExtractTypes){
				$.each(a_ExtractTypes[$(this).val()]['SubFilters'], function(i, v){
					$('#GRP_'+v).removeClass('hidden');
				});
			}
		});
		
        $('#printExcel, #printPdf, #search').click(function () {
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
        		$('#f_prn_useractivity').attr('action', 'prn_useractivity_exe.php');
        		$('#Action').val($(this).data('action'));
        	}
        });
        
        $('#f_prn_useractivity').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        });
	});
</script>

<?php
require_once(INC."/footer.php");