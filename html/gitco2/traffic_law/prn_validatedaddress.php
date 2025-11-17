<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_prn_validatedaddress.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

global $rs;
$rs->SetCharset('utf8');

$PageTitle = CheckValue('PageTitle','s') ?: '/';
$Filter = CheckValue('Filter', 'n');

$a_ValidatedAddress_Opt = unserialize(PRN_VALIDATEDADDRESS_VALIDATEDOPT);

if($Filter > 0){
    $str_Where = prnValidatedAddressWhere();
    $str_Where .= " AND F.CityId='{$_SESSION['cityid']}'";
    
    $cls_view = new CLS_VIEW(PRN_VALIDATEDADDRESS);
    $query = $cls_view->generateSelect($str_Where);
    $a_Results = $rs->getResults($rs->SelectQuery($query));
}

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_prn_validatedaddress" action="prn_validatedaddress.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" id="Action" name="Action" value="">
		<input type="hidden" name="Filter" value="1">
		
		<div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" value="<?= $Search_Plate; ?>" name="Search_Plate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" value="<?= $Search_Code; ?>" name="Search_Code">
            </div>
            <div class="col-sm-7 BoxRowLabel">
            </div>
		
			<div class="clean_row HSpace4"></div>
		
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da cron.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="<?= $Search_FromProtocolId; ?>" id="Search_FromProtocolId" name="Search_FromProtocolId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A cron.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="<?= $Search_ToProtocolId; ?>" id="Search_ToProtocolId" name="Search_ToProtocolId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear, true);?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ad anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear, true);?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
  			<div class="col-sm-2 BoxRowLabel">
            	Indirizzo validato
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($a_ValidatedAddress_Opt, true, "Search_ValidatedAddress", "Search_ValidatedAddress", $Search_ValidatedAddress, false); ?>
            </div>
  			<div class="col-sm-9 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:6.8rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        	<button type="submit" data-action="Excel" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        	<button type="submit" data-action="Pdf" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
        </div>

    	<div class="clean_row HSpace4"></div>
    	
    	<div class="table_label_H col-sm-1">N.</div>
        <div class="table_label_H col-sm-1">Cron.</div>
    	<div class="table_label_H col-sm-2">Riferimento</div>
        <div class="table_label_H col-sm-2">Dati atto</div>
        <div class="table_label_H col-sm-4">Esito notifica</div>
        <div class="table_label_H col-sm-1">Indirizzo validato</div>
        <div class="table_label_H col-sm-1"></div>
        
        <div class="clean_row HSpace4"></div>

		<?php if($Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Inserire i criteri di ricerca.
			</div>
        <?php else: ?>
        	<?php if(!empty($a_Results)): ?>
        		<?php $count = 1; ?>
        		<?php foreach($a_Results as $result): ?>
        			<div class="tableRow">
        				<div class="table_caption_H col-sm-1"><?= $count++; ?></div>
        				<div class="table_caption_H col-sm-1"><?= $result['ProtocolId'].'/'.$result['ProtocolYear']; ?></div>
        				<div class="table_caption_H col-sm-2"><?= $result['Code']; ?></div>
        				<div class="table_caption_H col-sm-2">
    						<div class="col-sm-8">
    							<?= $a_FineTypeId[$result['FineTypeId']].' '.DateOutDB($result['FineDate']).' - '.TimeOutDB($result['FineTime']) ?>
    						</div>
							<div class="col-sm-4 text-right">
								<?= $result['VehiclePlate']; ?> <i class="<?= $aVehicleTypeId[$result['VehicleTypeId']]; ?>" style="color:#337AB7;"></i>
							</div>
        				</div>
        				<div class="table_caption_H col-sm-4 text-center"><?= $result['ResultTitle']; ?></div>
        				<div class="table_caption_H col-sm-1 text-center"><?= YesNoOutDB($result['ValidatedAddress']); ?></div>
        				<div class="table_caption_button col-sm-1"></div>
        			</div>
        			
        			<div class="clean_row HSpace4"></div>
        		<?php endforeach; ?>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
        	<?php endif; ?>
        <?php endif; ?>

	</form>
</div>
<script>
	$(document).ready(function () {
	    $(".tableRow").mouseover(function(){
            $( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error').css("background-color", "#cfeaf7c7");
        });
        $(".tableRow").mouseout(function(){
            $( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error').css("background-color", "");
        });
        
        $("#Search_FromProtocolYear").change(function(){
        	if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolYear").val())
        		$("#Search_ToProtocolYear").val($(this).val());
        });
        $("#Search_ToProtocolYear").change(function(){
        	if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolYear").val())
        		$("#Search_FromProtocolYear").val($(this).val());
        });
        
        $("#Search_FromProtocolId").change(function(){
        	if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolId").val())
        		$("#Search_ToProtocolId").val($(this).val());
        });
        $("#Search_ToProtocolId").change(function(){
        	if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolId").val())
        		$("#Search_FromProtocolId").val($(this).val());
        });
        
        $('#printExcel, #printPdf, #search').click(function () {
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
        		$('#f_prn_validatedaddress').attr('action', 'prn_validatedaddress_exe.php');
        		$('#Action').val($(this).data('action'));
        	}
        });
        
        $('#f_prn_validatedaddress').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        });
	});
</script>

<?php
require_once(INC."/footer.php");