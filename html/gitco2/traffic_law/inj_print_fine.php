<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_inj_print_fine.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

global $rs;

$Filter = CheckValue('Filter', 'n');

$a_AnomalyTypes = unserialize(INJ_PRINT_FINE_ANOMALYTYPES);

$a_GradeType = array(
    "",
    "I",
    "II",
    "III"
);

$a_DisputeStatusId = array(
    "",
    "#FFF952",
    "#3C763D",
    "#A94442"
);

if ($Filter == 1){
    $cls_view = new CLS_VIEW(INJ_FINE);
    
    $a_WhereHaving = injPrintFineWhereHaving();
    $a_OrderBy = injPrintFineOrderBy();
    
    $query = $cls_view->generateSelect($a_WhereHaving['Where'], $a_WhereHaving['Having'], $a_OrderBy['OrderBy']);
    echo $query;
    $rs_Fine = $rs->SelectQuery($query);
    $RowNumber = mysqli_num_rows($rs_Fine);
    mysqli_data_seek($rs_Fine, $pagelimit);
}

echo $str_out;
?>
<div class="row-fluid">
    <form id="f_inj_print_fine" name="f_inj_print_fine" id="f_search" action="inj_print_fine.php" method="post">
        <input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <input type="hidden" name="Filter" value="1">
        <input type="hidden" id="Action" name="Action" value="">
        <div class="col-sm-11">
        	<div class="col-sm-1 BoxRowLabel">
        		Da anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear, false); ?>
        	</div>
        	<div class="col-sm-1 BoxRowLabel">
        		Ad anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear, false); ?>
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
                Da data accert.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data accert.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel font_small">
                Nazionalità targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(unserialize(INJ_PRINT_FINE_NATIONALITY), true, 'TypePlate', 'TypePlate', $s_TypePlate, false, null, null, ''); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" value="<?= $Search_Ref; ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("ViolationType", "1=1", "Id", "Search_Violation", "Id", "Title", $Search_Violation, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromNotificationDate; ?>" name="Search_FromNotificationDate" id="Search_FromNotificationDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToNotificationDate; ?>" name="Search_ToNotificationDate" id="Search_ToNotificationDate">
            </div>
            
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" value="<?= $Search_Plate; ?>" id="Search_Plate" name="Search_Plate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelect("VehicleType", "1=1", "Id", "Search_VehicleType", "Id", "TitleIta", $Search_VehicleType, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="<?= $Search_Trespasser; ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect(unserialize(INJ_PRINT_FINE_GENRE), true, 'Search_Genre', 'Search_Genre', $Search_Genre, false, null, null, 'Entrambi'); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                N. Figure
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateArraySelect(unserialize(INJ_PRINT_FINE_FIGURES), true, 'Search_Number', 'Search_Number', $Search_Number, false, null, null, 'Qualsiasi'); ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
            	Tipo anomalia
            </div>
            <div class="col-sm-6 BoxRowCaption">
            	<?= CreateArraySelect($a_AnomalyTypes, true, 'Search_Anomalies', 'Search_Anomalies', $Search_Anomalies, false, null, null, 'Tutte'); ?>
            </div>
            <div class="col-sm-4 BoxRowCaption">
            	<input <?= $Search_Type == 0 ? ' checked' : '' ?> type="radio" name="Search_Type" value="0" style="top:0;"><span style="position:relative;top:-0.3rem"> Includi</span>
            	<input <?= $Search_Type == 1 ? ' checked' : '' ?> type="radio" name="Search_Type" value="1" style="top:0;"><span style="position:relative;top:-0.3rem"> Escludi</span>
                <input <?= $Search_Type == 2 ? ' checked' : '' ?> type="radio" name="Search_Type" value="2" style="top:0;"><span style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:9.1rem">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        	<button type="submit" data-action="Excel" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        	<button type="submit" data-action="Pdf" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
        </div>
    </form>
    
    <div class="clean_row HSpace4"></div>

	<div class="table_label_H col-sm-1">Cron. <?= sortButton(impostaParametriUrl(array('Filter' => $Filter), 'inj_print_fine.php'.$str_GET_Parameter), 'Order_ProtocolId', $Order_ProtocolId, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-2">Riferimento <?= sortButton(impostaParametriUrl(array('Filter' => $Filter), 'inj_print_fine.php'.$str_GET_Parameter), 'Order_Code', $Order_Code, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-2">Dati atto <?= sortButton(impostaParametriUrl(array('Filter' => $Filter), 'inj_print_fine.php'.$str_GET_Parameter), 'Order_FineDate', $Order_FineDate, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-2 font_small">Proprietario/Obbligato/Noleggio</div>
    <div class="table_label_H col-sm-2 font_small">Trasgressore/Noleggiante</div>
    <div class="table_label_H col-sm-2">Stato pratica</div>
    <div class="table_label_H col-sm-1"></div>

    <div class="clean_row HSpace4"></div>
    
	<?php if ($Filter != 1):?>
        <div class="table_caption_H col-sm-12 text-center">
        	Inserire criteri di ricerca
        </div>
    <?php else: ?>
		<?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php if (!empty($r_Fine = $rs->getArrayLine($rs_Fine))): ?>
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
					<div class="tableRow">
						<div class="col-sm-1 table_caption_H">
							<?= $r_Fine['ProtocolId'].'/'.$r_Fine['ProtocolYear']; ?>
						</div>
    					<div class="col-sm-2 table_caption_H">
    						<?= $r_Fine['Code']; ?>
    					</div>
    					<div class="col-sm-2 table_caption_H">
    						<div class="col-sm-8">
    							<?= $a_FineTypeId[$r_Fine['FineTypeId']].' '.DateOutDB($r_Fine['FineDate']).' - '.TimeOutDB($r_Fine['FineTime']) ?>
    						</div>
							<div class="col-sm-4">
								<?= $r_Fine['VehiclePlate']; ?> <i class="<?= $aVehicleTypeId[$r_Fine['VehicleTypeId']]; ?>" style="color:#337AB7;"></i>
							</div>
    					</div>
    					<div class="col-sm-2 table_caption_H" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
    						<?php if(!empty($TrespasserFullName_1)): ?>
    							<i data-toggle="tooltip" data-placement="left" data-container="body" title="<?= empty($NotificationDate_1) ? 'Non Notificato' : 'Notificato in data: '.DateOutDB($NotificationDate_1) ?>" class="tooltip-r fa fa-calendar fa-fw <?= empty($NotificationDate_1) ? 'opaque' : '' ?>" style="font-size:1.3rem;"></i>
    						<?php endif; ?>
    						<?= $TrespasserFullName_1 ?>
    					</div>
    					<div class="col-sm-2 table_caption_H" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
    						<?php if(!empty($TrespasserFullName_2)): ?>
    							<i data-toggle="tooltip" data-placement="left" data-container="body" title="<?= empty($NotificationDate_2) ? 'Non Notificato' : 'Notificato in data: '.DateOutDB($NotificationDate_2) ?>" class="tooltip-r fa fa-calendar fa-fw <?= empty($NotificationDate_2) ? 'opaque' : '' ?>" style="font-size:1.3rem;"></i>
    						<?php endif; ?>
    						<?= $TrespasserFullName_2 ?>
    					</div>
    					<div class="col-sm-1 table_caption_H">
    						<?php if($r_Fine['DisputeId'] != ''): ?>
    							<i data-toggle="tooltip" data-placement="left" data-container="body" title="<?= $a_GradeType[$r_Fine['DisputeGradeTypeId']].' Grado - '.$r_Fine['DisputeOfficeTitleIta'].' '.$r_Fine['DisputeOfficeCity'].' Depositato in data '.DateOutDB($r_Fine['DisputeDateFile'])?>" class="tooltip-r fa fa-gavel fa-fw" style="font-size:1.3rem;margin-top:0.2rem;color:<?= $a_DisputeStatusId[$r_Fine['DisputeStatusId']]?>"></i>
    						<?php else: ?>
    							<i data-toggle="tooltip" data-placement="left" data-container="body" title="Nessun ricorso" class="tooltip-r fa fa-gavel fa-fw opaque" style="font-size:1.3rem;margin-top:0.2rem;"></i>
    						<?php endif; ?>
    					</div>
    					<div class="col-sm-2 table_caption_H"><?= $r_Fine['FineId']; ?></div>
					</div>
					
					<div class="clean_row HSpace4"></div>
				<?php endif; ?>
			<?php endfor;?>
			<?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, impostaParametriUrl(array('Filter' => 1), $str_CurrentPage.$str_GET_Parameter), $strLabel);?>
    	<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente
            </div>
    	<?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
	$(document).ready(function () {
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
      	
    	$('#printExcel, #printPdf, #search').click(function () {
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
        		$('#f_inj_print_fine').attr('action', 'inj_print_fine_exe.php');
        		$('#Action').val($(this).data('action'));
        	}
        });
        
        $('#f_inj_print_fine').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        });
	});
</script>
<?php 
require_once(INC.'/footer.php');