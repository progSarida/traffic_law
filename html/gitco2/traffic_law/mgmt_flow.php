<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_flow.php");
require_once(INC."/header.php");
require_once(INC."/initialization.php");
require_once(INC.'/menu_' . $_SESSION['UserMenuType'] . '.php');

$Filter = CheckValue('Filter', 'n');
$PageTitle = CheckValue('PageTitle', 's');

$a_status = unserialize(MGMT_FLOW_STATUS);

if ($Filter == 1){
    $str_Where = mgmtFlowWhere();
    $rs_Flow = $rs->Select('V_Flow', $str_Where, 'Year DESC, Number DESC, CreationDate DESC');
    
    $RowNumber = mysqli_num_rows($rs_Flow);
    mysqli_data_seek($rs_Flow, $pagelimit);
}

echo $str_out;
?>
<script>
SARIDA.mostraCaricamento("Caricamento in corso...");
</script>
<div class="row-fluid">
	<form id="f_search" action="mgmt_flow.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">      
                <?= $_SESSION['ruletypetitle']; ?>
            </div>    
            <div class="col-sm-1 BoxRowLabel">
                Ente
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?php if($_SESSION['userlevel'] >= 3): ?>
            		<?= CreateSelectQuery("SELECT SC.Title, C.CityId FROM Customer C JOIN sarida.City SC ON SC.Id=C.CityId","Search_CityId","CityId","Title",$Filter != 1 ? $_SESSION['cityid'] : $Search_CityId,false); ?>
            	<?php else: ?>
            		<?= $_SESSION['citytitle']; ?>
            	<?php endif; ?>
            </div>   
            <div class="col-sm-1 BoxRowLabel">
                Flusso N°
            </div>    
            <div class="col-sm-1 BoxRowCaption">      
                <input class="form-control frm_field_numeric" name="Search_Flow" type="text" value="<?= $Search_Flow ?>">
            </div>    
            <div class="col-sm-1 BoxRowLabel">
                Anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect(range(date('Y'), 2012), false, 'Search_Year', 'Search_Year', $Search_Year != '' ? $Search_Year : $_SESSION['year'], true); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo invio
            </div>
            <div class="col-sm-2 BoxRowCaption"> 
                <?= CreateSelect("Print_Type", "Id!=6", "Id", "Search_PrintNumber", "Id", "Name", $Search_PrintNumber, false); ?>
            </div>
			<div class = "col-sm-1 BoxRowLabel">
			</div>
			
            <div class="clean_row HSpace4"></div>

			<div class = "col-sm-1 BoxRowLabel">
				Tipo flusso
			</div>
			<div class = "col-sm-2 BoxRowCaption">
				<?= CreateSelect('Document_Type', 'CollectionTypeId = 2', 'Id', 'Search_Status', 'Id', 'Description', $Search_Status, false); ?>
			</div>
            <div class="col-sm-1 BoxRowLabel">
                Stampatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateSelect('Printer', '1=1', 'Id', 'Search_Type', 'Id', 'Name', $Search_Type, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Status attivo
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array_keys($a_status), false, "Search_Step", "Search_Step", $Search_Step); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Status mancante
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array_keys($a_status), false, "Search_MissedStep", "Search_MissedStep", $Search_MissedStep); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Data Status
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input <?= $Search_Step != '' ? '' : 'disabled' ?> class="form-control frm_field_date" name="Search_StepDate" id="Search_StepDate" type="text" value="<?= $Search_StepDate; ?>">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_Print" action="mgmt_flow_prn_exe.php" method="post">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div class="col-sm-11">
            <div class="col-sm-9 BoxRowLabel"></div>                     
            <div class="col-sm-1 BoxRowLabel">
                Fattura	N°		
            </div>
            <div class="col-sm-2 BoxRowCaption">                       
            	<?= CreateSelectQuery("SELECT Id,CONCAT_WS(' ', Number, '/', Year, 'del', DATE_FORMAT(Date, '%d/%m/%Y')) AS Invoice FROM Flow_Invoices ORDER BY Date DESC, Year DESC, Number DESC", "Search_InvoiceId", "Id", 'Invoice', $Search_InvoiceId, false); ?>
            </div> 
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="min-height:2.2rem;">
        	<?= ChkButton($aUserButton, 'act', '<button type="button" id="invoicePrint" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa fattura" class="tooltip-r btn btn-warning col-sm-4" style="height: 2.2rem;padding: 0;"><i class="fa fa-file-pdf-o"></i></button>'); ?>
			<?= ChkButton($aUserButton, 'act', '<button type="button" id="invoiceAdd" data-toggle="tooltip" data-container="body" data-placement="top" title="Crea fattura" class="tooltip-r btn btn-warning col-sm-4" style="height: 2.2rem;padding: 0;"><i class="fa fa-plus"></i></button>'); ?>
			<?= ChkButton($aUserButton, 'act', '<button type="button" id="invoiceUpdate" data-toggle="tooltip" data-container="body" data-placement="top" title="Modifica fattura" class="tooltip-r btn btn-warning col-sm-4" style="height: 2.2rem;padding: 0;"><i class="fa fa-pencil"></i></button>'); ?>
        </div>              
    </form>
    
    <div class="clean_row HSpace4"></div>
    
    <div class="table_label_H col-sm-1">Numero</div>
    <div class="table_label_H col-sm-1">Tipo</div>
    <div class="table_label_H col-sm-1">Stampatore</div>
    <div class="table_label_H col-sm-1">Status</div>
    <div class="table_label_H col-sm-1">Data status</div>				
    <div class="table_label_H col-sm-3">Stampa e imbustamento</div>
    <div class="table_label_H col-sm-3">Spese postali</div>
    <div class="table_label_H col-sm-1"></div>
    
    <div class="clean_row HSpace4"></div>
    
	<?php if ($Filter != 1):?>
        <div class="table_caption_H col-sm-12 text-center">
        	Inserire criteri di ricerca
        </div>
    <?php else: ?>
		<?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_Flow = $rs->getArrayLine($rs_Flow);?>
				<?php if (! empty($r_Flow)): ?>
					<?php $status = mgmtFlowStatus($r_Flow); ?>
					<div class="tableRow">
						<div class="table_caption_H col-sm-1">
							(<?= $r_Flow['CityId']; ?>) <?= $r_Flow['Number']; ?>/<?= $r_Flow['Year']; ?>
						</div>
						<div class="table_caption_H col-sm-1">
							<?= $r_Flow['DocumentType']; ?>
						</div>
						<div class="table_caption_H col-sm-1">
							<?= $r_Flow['Printer']; ?>
						</div>
						<div class="table_caption_H col-sm-1" style="color:<?= $a_status[$status]['Colour'] ?>">
							<?= $status; ?>
						</div>
						<div class="table_caption_H col-sm-1">
							<?= DateOutDB($r_Flow[$a_status[$status]['Field']]); ?>
						</div>
						<div class="col-sm-3" style="padding:0;">
							<div class="col-sm-3 table_caption_I">Quantità</div>
							<div class="col-sm-3 table_caption_H"><?= $r_Flow['RecordsNumber']; ?></div>
							<div class="col-sm-3 table_caption_I">Costo</div>
							<div class="col-sm-3 table_caption_H">€ <?= NumberDisplay($r_Flow['PrintCost']*$r_Flow['RecordsNumber']); ?></div>
						</div>
						<div class="col-sm-3" style="padding:0;">
							<div class="col-sm-3 table_caption_I">Quantità</div>
							<div class="col-sm-3 table_caption_H"><?= ($r_Flow['Zone0Number']+$r_Flow['Zone1Number']+$r_Flow['Zone2Number']+$r_Flow['Zone3Number']); ?></div>
							<div class="col-sm-3 table_caption_I">Costo</div>
							<div class="col-sm-3 table_caption_H">€ <?= NumberDisplay(mgmtFlowTotZone($r_Flow)); ?></div>
						</div>
						<div class="table_caption_button col-sm-1">
							<?= ChkButton($aUserButton, 'upd','<a href="mgmt_flow_upd.php'.$str_GET_Parameter.'&Id='.$r_Flow['Id'].'"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="margin-top:0.3rem;"></i></a>'); ?>
							<?= ChkButton($aUserButton, 'imp','<a href="mgmt_flow_additional_info.php'.$str_GET_Parameter.'&FlowId='.$r_Flow['Id'].'"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Gestisci numeri di raccomandata" class="tooltip-r glyphicon glyphicon-envelope" style="margin-top:0.3rem;"></i></a>'); ?>
						</div>
						
						<div class="clean_row HSpace4"></div>
						
						<div class="table_caption_I col-sm-1 font_small">N° Record inviati</div>
						<div class="col-sm-1" style="padding:0;">
							<div class="col-sm-6 table_caption_H text-center"><?= $r_Flow['HistoryDocs']; ?></div>
							<div class="col-sm-6 table_caption_H text-center">
								<a href="<?= mgmtFlowPath($r_Flow); ?>">
									<i data-toggle="tooltip" data-container="body" data-placement="top" title="Scarica archivio" class="tooltip-r fa fa-download" style="font-size:2rem;margin-top:0.2rem;"></i>
								</a>
							</div>
						</div>
						<div class="table_caption_I col-sm-1 font_small">Notifiche ritornate</div>
						<div class="col-sm-1 table_caption_H">
							<?php if(isset($r_Flow['TotalNotifications'])): ?>
								<?= $r_Flow['TotalNotifications']; ?> di cui ( <span style="color: green"><?= $r_Flow['PositiveNotifications']; ?></span> / <span style="color: red"><?= $r_Flow['NegativeNotifications']; ?></span> )
							<?php endif; ?>
						</div>
						<div class="col-sm-1" style="padding:0;">
							<div class="col-sm-6 table_caption_H text-center">
								<?php if(in_array($r_Flow['DocumentTypeId'], unserialize(MGMT_FLOW_TYPES_NOTIFICATION_INFO_IDS))): ?>
    								<?= ChkButton($aUserButton, 'act', '
        								<a href="mgmt_flow_detail_viw.php'.$str_GET_Parameter.'&FlowId='.$r_Flow['Id'].'">
        									<i data-toggle="tooltip" data-container="body" data-placement="top" title="Consulta notifiche" class="tooltip-r fa fa-info-circle" style="font-size:2rem;margin-top:0.1rem;"></i>
        								</a>
                                    '); ?>
                                <?php endif; ?>
							</div>
							<div class="col-sm-6 table_caption_H text-center">
								<?php if(in_array($r_Flow['DocumentTypeId'], unserialize(MGMT_FLOW_TYPES_PEC_INFO_IDS))): ?>
									<?= ChkButton($aUserButton, 'act', '
    									<a href="mgmt_flow_pecdetail_upd.php'.$str_GET_Parameter.'&FlowId='.$r_Flow['Id'].'">
        									<i data-toggle="tooltip" data-container="body" data-placement="top" title="Consulta ricevute PEC" class="tooltip-r fas fa-at" style="font-size:2rem;margin-top:0.1rem;"></i>
        								</a>
                                    '); ?>
								<?php /*TODO Riattivare una volta integrata la lettura dei riscontri dei flussi da Koinè
								<?php elseif($r_Flow['PrinterId'] == 5): ?>
									<?= ChkButton($aUserButton, 'act', '
    									<a href="mgmt_flow_checkstatus_exe.php'.$str_GET_Parameter.'&FlowId='.$r_Flow['Id'].'">
        									<i data-toggle="tooltip" data-container="body" data-placement="top" title="Controlla lo stato del flusso" class="tooltip-r fas fa-redo" style="font-size:2rem;margin-top:0.1rem;"></i>
        								</a>
                                    '); ?>*/?>
								<?php endif; ?>
							</div>
						</div>
						<div class="col-sm-3" style="padding:0;">
							<div class="col-sm-3 table_caption_I">Fattura</div>
							<div class="col-sm-3 table_caption_H"><?= $r_Flow['PrintInvoiceNumber']; ?></div>
							<div class="col-sm-3 table_caption_I">Data</div>
							<div class="col-sm-3 table_caption_H"><?= DateOutDB($r_Flow['PrintInvoiceDate']); ?></div>
						</div>
						<div class="col-sm-3" style="padding:0;">
							<div class="col-sm-3 table_caption_I">Fattura</div>
							<div class="col-sm-3 table_caption_H"><?= $r_Flow['PostageInvoiceNumber']; ?></div>
							<div class="col-sm-3 table_caption_I">Data</div>
							<div class="col-sm-3 table_caption_H"><?= DateOutDB($r_Flow['PostageInvoiceDate']); ?></div>
						</div>
						<div class="table_caption_H col-sm-1">
						</div>
            		</div>
            		<div class="clean_row HSpace16"></div>
        		<?php endif; ?>
			<?php endfor; ?>
			<?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, impostaParametriUrl(array('Filter' => 1), $str_CurrentPage.$str_GET_Parameter), '');?>
    	<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente
            </div>
    	<?php endif; ?>
    <?php endif; ?>

</div>

<script type="text/javascript">
	$(document).ready(function () {
	    SARIDA.nascondiCaricamento();
	    
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
      	
      	$("#Search_Step").on('change', function(){
      		if($(this).val() == ''){
      			$('#Search_StepDate').prop('disabled', true).val('');
      		} else {
      			$('#Search_StepDate').prop('disabled', false);
      		}
      	});
      	
        $("#invoicePrint").on('click', function(){
		    if($("#Search_InvoiceId").val() > 0){
		    	SARIDA.mostraCaricamento("Caricamento in corso...");
		    	$("#f_Print").submit();
	    	} else {
	    		alert("Selezionare la fattura da stampare!");
	    	}
        });
      	
        $("#invoiceUpdate").on('click', function(){
            var invoiceId = $("#Search_InvoiceId").val();
            if(invoiceId > 0){
            	SARIDA.mostraCaricamento("Caricamento in corso...");
            	location.href = "mgmt_flow_act.php<?= $str_GET_Parameter;?>&actType=updInvoice&Id="+invoiceId;
            } else {
            	alert("Selezionare la fattura da modificare!");
            }            
        });
        
        $('#f_search').on('submit', function () {
        	SARIDA.mostraCaricamento("Caricamento in corso...");
        });
        
        $("#invoiceAdd").on('click', function(){
        	SARIDA.mostraCaricamento("Caricamento in corso...");
			location.href = "mgmt_flow_act.php<?= $str_GET_Parameter;?>&actType=addInvoice";          
        });
	});
</script>
<?php
require_once(INC."/footer.php");
