<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_installments.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

global $rs;
global $PrintPage; //prn_installments.php

$rs->SetCharset('utf8');

$PageTitle = CheckValue('PageTitle','s') ?: '/';
$Filter = CheckValue('Filter', 'n');

if($Filter > 0){
    $str_Where = mgmtInstallmentsWhere();
    $str_Where .= " AND F.CityId='{$_SESSION['cityid']}'";
    
    $cls_view = new CLS_VIEW(MGMT_INSTALLMENTS);
    
    $query = $cls_view->generateSelect($str_Where, null);
    $a_Results = $rs->getResults($rs->SelectQuery($query)) ?: array();
}

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_mgmt_installments" action="<?= $PrintPage ? "prn_installments.php" : "mgmt_installments.php"; ?>" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
		<input type="hidden" id="PrintType" name="PrintType" value="">
		
		<div class="col-sm-11">
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
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear ?: $_SESSION['year'], true);?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ad anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear ?: $_SESSION['year'], true);?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Rateizzazioni chiuse
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?php foreach(unserialize(MGMT_INSTALLMENTS_STANDARD_RADIO) as $value => $name): ?>
                	<input type="radio" name="Search_Definitive" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_Definitive ? ' checked' : '' ?>>
                	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php endforeach; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Stato
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect(unserialize(MGMT_INSTALLMENTS_STATUS_OPTIONS), true, 'Search_Status', 'Search_Status', $Search_Status, true) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<input class="form-control frm_field_string" type="text" value="<?= $Search_Name; ?>" id="Search_Name" name="Search_Name">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<input class="form-control frm_field_string" type="text" value="<?= $Search_Trespasser; ?>" id="Search_Trespasser" name="Search_Trespasser">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Posizione
            </div>
            <div class="col-sm-2 BoxRowCaption">
        		<input class="form-control frm_field_string" type="text" value="<?= $Search_Position; ?>" id="Search_Position" name="Search_Position">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
        	<?php if($PrintPage): ?>
	        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        		<button type="submit" data-printtype="<?= MGMT_INSTALLMENTS_ACTION_PRINTXLS ?>" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        		<button type="submit" data-printtype="<?= MGMT_INSTALLMENTS_ACTION_PRINTPDF ?>" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
        	<?php else: ?>
        		<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        	<?php endif; ?>
        </div>

    	<div class="clean_row HSpace4"></div>
    	
    	<div class="table_label_H col-sm-1">N.</div>
        <div class="table_label_H col-sm-1">Cron.</div>
    	<div class="table_label_H col-sm-1">Tipo Rateizzazione</div>
    	<div class="table_label_H col-sm-1">Nome</div>
    	<div class="table_label_H col-sm-1">Posizione</div>
        <div class="table_label_H col-sm-2">Trasgressore</div>
        <div class="table_label_H col-sm-1">N. Rate</div>
        <div class="table_label_H col-sm-1">Importo totale</div>
        <div class="table_label_H col-sm-1">Data richiesta</div>
        <div class="table_label_H col-sm-1">Stato</div>
        <div class="table_label_H col-sm-1"></div>
        
        <div class="clean_row HSpace4"></div>

		<?php if($Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Selezionare i criteri di ricerca.
			</div>
        <?php else: ?>
        	<?php if (!empty($a_Results)): ?>
        		<?php for($i=$pagelimit; $i < PAGE_NUMBER*$page; $i++): ?>
        			<?php if(!empty($a_Results[$i])): ?>
            			<div class="tableRow<?= $a_Results[$i]['StatusRateId'] == RATEIZZAZIONE_CHIUSA ? ' text-danger' : '' ?>">
            				<div class="table_caption_H col-sm-1">
            					<?= $i+1; ?>
        						<?php if($a_Results[$i]['StatusRateId'] == RATEIZZAZIONE_CHIUSA): ?>
        							<i class="fas fa-lock fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Rateizzazione chiusa in data: <?= DateOutDB($a_Results[$i]['ClosingDate']); ?>"></i>&nbsp;
        						<?php endif; ?>
        					</div>
                            <div class="table_caption_H col-sm-1"><?= $a_Results[$i]['ProtocolId'].'/'.$a_Results[$i]['ProtocolYear']; ?></div>
                        	<div class="table_caption_H col-sm-1"><?= $a_Results[$i]['DocumentTypeTitle']; ?></div>
                        	<div class="table_caption_H col-sm-1"><?= $a_Results[$i]['RateName']; ?></div>
                        	<div class="table_caption_H col-sm-1"><?= $a_Results[$i]['Position']; ?></div>
                            <div class="table_caption_H col-sm-2"><?= $a_Results[$i]['TrespasserFullName']; ?></div>
                            <div class="table_caption_H col-sm-1"><?= $a_Results[$i]['InstalmentNumber']; ?></div>
                            <div class="table_caption_H col-sm-1">€ <?= number_format($a_Results[$i]['InstalmentAmount'], 2, ',', '.'); ?></div>
                            <div class="table_caption_H col-sm-1">
                            	<?= DateOutDB($a_Results[$i]['RequestDate']); ?>
                        	</div>
                            <div class="table_caption_H col-sm-1">
                            	<?php if($a_Results[$i]['RequestStatusId'] > 0): ?>
                            		<?php if(is_null($a_Results[$i]['RequestOutcome'])): ?>
                            			<i class="fas fa-file-signature fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="color:yellow;margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Richiesta stampata con esito in attesa"></i>&nbsp;
                            		<?php elseif($a_Results[$i]['RequestOutcome'] == 1): ?>
                            			<i class="fas fa-file-signature fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="color:green;margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Richiesta stampata con esito accolto"></i>&nbsp;
                            		<?php elseif($a_Results[$i]['RequestOutcome'] == 0): ?>
                            			<i class="fas fa-file-signature fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="color:red;margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Richiesta stampata con esito respinto: <?= $a_Results[$i]['ResponseReason']?>"></i>&nbsp;
                            		<?php endif; ?>
                            	<?php else: ?>
                            		<i class="fas fa-file-signature fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Richiesta non stampata"></i>&nbsp;
                            	<?php endif; ?>
                            	<?php if($a_Results[$i]['ResponseStatusId'] > 0): ?>
                            		<i class="fas fa-file-contract fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Esito stampato"></i>&nbsp;
                        		<?php else: ?>
                        			<i class="fas fa-file-contract fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Esito non stampato"></i>&nbsp;
                            	<?php endif; ?>
                            	<?php if($a_Results[$i]['BillStatusId'] > 0): ?>
                            		<i class="fas fa-money-check-alt fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Bollettini stampati stampato"></i>&nbsp;
                            	<?php else: ?>
                            		<i class="fas fa-money-check-alt fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Bollettini non stampati"></i>&nbsp;
                            	<?php endif; ?>
                            </div>
                            <div class="table_caption_H col-sm-1">
                            	<?php if(!$PrintPage): ?>
                            		<?= ChkButton($aUserButton, 'upd','<a href="mgmt_installments_upd.php'.$str_GET_Parameter.'&Id='.$a_Results[$i]['Id'].'&FineId='.$a_Results[$i]['FineId'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;');?>
                            	<?php endif; ?>
                            </div>
            			</div>
                    	
                		<div class="clean_row HSpace4"></div>
        			<?php endif; ?>
        		<?php endfor; ?>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
        	<?php endif; ?>
        	
        	<?= CreatePagination(PAGE_NUMBER, count($a_Results), $page, impostaParametriUrl(array("Filter" => 1), $str_CurrentPage),""); ?>
        <?php endif; ?>
        <div class="table_label_H col-sm-12" style="height:auto;color:white;">
            <div class="col-sm-6 text-left" style="padding:1rem;">
     		    <div class="col-sm-12">Leggenda:</div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa-lock"></i> Rateizzazione chiusa
                </div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa-file-signature"></i> Richiesta stampata
                </div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa-file-contract"></i> Esito stampato
                </div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa-money-check-alt"></i> Bollettini stampati
                </div>
    		</div>
        </div>
	</form>
</div>
<script>
	$(document).ready(function () {
        $('#printExcel, #printPdf, #search').click(function () {
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
        		$('#f_mgmt_installments').attr('action', 'prn_installments_exe.php');
        		$('#PrintType').val($(this).data('printtype'));
        	}
        });
        
        $('#f_mgmt_installments').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        });
        
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
      	
        $("#Search_FromProtocolId").change(function(){
            if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolId").val())
                $("#Search_ToProtocolId").val($(this).val());
        });

        $("#Search_ToProtocolId").change(function(){
            if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolId").val())
                $("#Search_FromProtocolId").val($(this).val());
        });

        $("#Search_FromProtocolYear").change(function(){
            if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolYear").val())
                $("#Search_ToProtocolYear").val($(this).val());
        });

        $("#Search_ToProtocolYear").change(function(){
            if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolYear").val())
                $("#Search_FromProtocolYear").val($(this).val());
        });
	});
</script>

<?php
require_once(INC."/footer.php");