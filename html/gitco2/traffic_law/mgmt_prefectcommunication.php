<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_prefectcommunication.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

global $rs;
$rs->SetCharset('utf8');

$PageTitle = CheckValue('PageTitle','s') ?: '/';
$Filter = CheckValue('Filter', 'n');

$a_FineTypes = unserialize(MGMT_PREFECTCOMMUNICATION_FINE_OPT);
$a_SendTypes = unserialize(MGMT_PREFECTCOMMUNICATION_SENDTYPE_OPT);
$a_StatusTypes = unserialize(MGMT_PREFECTCOMMUNICATION_STATUS_OPT);
$a_TrespasserTypes = unserialize(MGMT_PREFECTCOMMUNICATION_TRESPASSER_OPT);
$a_HabitualOpt = unserialize(MGMT_PREFECTCOMMUNICATION_HABITUAL_OPT);

if($Filter > 0){
    $str_Where = mgmtPrefectCommunicationWhere();
    $str_Where .= " AND F.CityId='{$_SESSION['cityid']}'";
    
    $cls_view = new CLS_VIEW(MGMT_PREFECTCOMMUNICATION);
    
    $query = $cls_view->generateSelect($str_Where, null);
    $a_Results = $rs->getResults($rs->SelectQuery($query)) ?: array();
    
    $a_Results = mgmtPrefectCommunicationPostProcess($a_Results);
}

echo $str_out;
?>
<div class="row-fluid">
	<div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota bene:
                    <ul style="list-style-position: inside;">
                        <li>
                            Il filtro "Tipologia verbale" discrimina in base ai seguenti parametri:
                        </li>
                        <li><b>Definitivi:</b> verbali senza ricorso oppure con ricorso respinto e con più di 60gg trascorsi dalla data di notifica.</li>
                        <li><b>Non definitivi:</b> verbali che abbiano un ricorso in attesa, riniviato, sospeso, accolto e/o meno di 60gg trascorsi dalla data di notifica.</li>
                    </ul>
            	</li>
            </ul>
        </div>
    </div>
	<form id="f_mgmt_prefectcommunication" action="mgmt_prefectcommunication.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
		<input type="hidden" id="PrintType" name="PrintType" value="">
		
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
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear ?: $_SESSION['year'], true);?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ad anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear ?: $_SESSION['year'], true);?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Tipologia verbale
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_FineTypes, true, "Search_Definitive", "Search_Definitive", $Search_Definitive == "" ? 1 : $Search_Definitive, false, null, null, "Tutti"); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Stato comunicazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($a_StatusTypes, true, "Search_Type", "Search_Type", $Search_Type, false, null, null, "Tutti"); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo trasgressore
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_TrespasserTypes, true, "Search_Trespasser", "Search_Trespasser", $Search_Trespasser, false, null, null, "Tutti"); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Rapporto trasgressore/violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_HabitualOpt, true, "Search_HasHabitual", "Search_HasHabitual", $Search_HasHabitual, false, null, null, "Tutti i trasgressori"); ?>
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:6.8rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        	<button type="submit" data-printtype="<?= MGMT_PREFECTCOMMUNICATION_ACTION_PRINTXLS ?>" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        	<button type="submit" data-printtype="<?= MGMT_PREFECTCOMMUNICATION_ACTION_PRINTPDF ?>" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
        </div>

    	<div class="clean_row HSpace4"></div>
    	
    	<div class="table_label_H col-sm-1">N.</div>
        <div class="table_label_H col-sm-1">Cron.</div>
    	<div class="table_label_H col-sm-2">Riferimento</div>
        <div class="table_label_H col-sm-2">Dati atto</div>
        <div class="table_label_H col-sm-1">Data trasmissione</div>
        <div class="table_label_H col-sm-1">Data notifica</div>
        <div class="table_label_H col-sm-1">Esito notifica</div>
        <div class="table_label_H col-sm-1">Mod. trasmissione</div>
        <div class="table_label_H col-sm-2">Stato pratica</div>

		<?php if($Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Selezionare i criteri di ricerca.
			</div>
        <?php else: ?>
        	<?php if (!empty($a_Results)): ?>
        		<?php $count = 1; ?>
                <?php foreach($a_Results as $result): ?>
        			<div class="tableRow">
        				<div class="table_caption_H col-sm-1">
    						<?= $count++; ?>
    						<?php if($result[INDEX_HABITUALFINES] > 0): ?>
    							<i class="tooltip-r fa fa-repeat fa-fw" style="font-size:1.3rem;line-height:inherit" data-html="true" data-container="body" data-toggle="tooltip" data-placement="right" title="Recidiva nel biennio"></i>
    						<?php endif; ?>
						</div>
        				<div class="table_caption_H col-sm-1"><?= $result['ProtocolId'].'/'.$result['ProtocolYear']; ?></div>
        				<div class="table_caption_H col-sm-2"><?= $result['Code']; ?></div>
        				<div class="table_caption_H col-sm-2">
    						<div class="col-sm-5">
    							<?= $a_FineTypeId[$result['FineTypeId']].' '.DateOutDB($result['FineDate']).' - '.TimeOutDB($result['FineTime']); ?>
    						</div>
    						<div class="col-sm-3">
    							<?= $result['Article'].' - '.$result['Paragraph'].' - '.$result['Letter']; ?>
    						</div>
							<div class="col-sm-4 text-right">
								<?= $result['VehiclePlate']; ?> <i class="<?= $aVehicleTypeId[$result['VehicleTypeId']]; ?>" style="color:#337AB7;"></i>
							</div>
        				</div>
        				<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($result['SendDate']); ?></div>
        				<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($result['PrefectNotificationDate']); ?></div>
        				<div class="table_caption_H col-sm-1 text-center"><?= $result['ResultTitle']; ?></div>
        				<div class="table_caption_H col-sm-1 text-center"><?= $a_SendTypes[$result['SendType']] ?? "" ?></div>
        				<div class="table_caption_H col-sm-1">
        					<?php if(!empty($result['FineDocumentationId'])): ?>
        						<i class="fa fa-file-text fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Comunicazione creata"></i>
        					<?php else: ?>
        						<i class="fa fa-file-text fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Comunicazione non creata"></i>
        					<?php endif; ?>
        					
        					<?php if(!empty($result['SendDate'])): ?>
        						<i class="fa fa-paper-plane fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Comunicazione trasmessa il <?= DateOutDB($result['SendDate']); ?>"></i>
        					<?php else: ?>
        						<i class="fa fa-paper-plane fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Comunicazione non trasmessa"></i>
        					<?php endif; ?>
                			
                			<?php if(!empty($result['PrefectNotificationDate'])): ?>
                				<i class="fas fa-clipboard-check fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Comunicazione notificata il <?= DateOutDB($result['PrefectNotificationDate']); ?>"></i>
                			<?php else: ?>
                				<i class="fas fa-clipboard-check fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="" style="margin-top: 0.2rem;font-size: 1.8rem;" data-original-title="Comunicazione non notificata"></i>
                			<?php endif; ?>
        				</div>
        				<div class="table_caption_button col-sm-1" style="line-height: 2.4rem;">
        					<?= ChkButton($aUserButton, 'upd','<a href="mgmt_prefectcommunication_upd.php'.$str_GET_Parameter.'&FineId='.$result['FineId'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;');?>
        				</div>
        			</div>
                		
                    <div class="clean_row HSpace4"></div>
        		<?php endforeach; ?>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
        	<?php endif; ?>
        <?php endif; ?>
        <div class="table_label_H col-sm-12" style="height:auto;color:white;">
            <div class="col-sm-6 text-left" style="padding:1rem;">
     		    <div class="col-sm-12">Leggenda:</div>
     		    <div class="col-sm-3">
     		    	<i class="fa fa-file-text"></i> Comunicazione creata
                </div>
     		    <div class="col-sm-3">
     		    	<i class="fa fa-paper-plane"></i> Comunicazione trasmessa
                </div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa-clipboard-check"></i> Comunicazione notificata
                </div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa fa-repeat"></i> Recidiva nel biennio
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
        		$('#f_mgmt_prefectcommunication').attr('action', 'mgmt_prefectcommunication_prn_exe.php');
        		$('#PrintType').val($(this).data('printtype'));
        	}
        });
        
        $("#Search_FromProtocolYear, #Search_FromProtocolId").change(function() {
            if($("#Search_FromProtocolId").val()!='' || $("#Search_ToProtocolId").val()!='')
                $("#Search_ToProtocolYear").val($("#Search_FromProtocolYear").val());
        });
        $("#Search_FromProtocolYear").change(function(){
        	if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolYear").val())
        		$("#Search_ToProtocolYear").val($(this).val());
        });
        $("#Search_ToProtocolYear").change(function(){
        	if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolYear").val())
        		$("#Search_FromProtocolYear").val($(this).val());
        });
        
        $('#f_mgmt_prefectcommunication').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        });
        
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
	});
</script>

<?php
require_once(INC."/footer.php");