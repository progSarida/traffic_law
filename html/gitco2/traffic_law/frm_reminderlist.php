<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_frm_reminderlist.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

global $rs;
$rs->SetCharset('utf8');

defined("REMINDER_OPERATION") ?: define("REMINDER_OPERATION", INDEX_OPERATION_LIST_EMITTED);
$action = unserialize(FRM_REMINDERLIST_PAGES)[REMINDER_OPERATION];

$a_OrderOptions = unserialize(FRM_REMINDERLIST_ORDER_OPTIONS);
$a_OrderOptionsSelect = array_diff(array_combine(array_keys($a_OrderOptions), array_column($a_OrderOptions, 'Name')), [null]);
$a_PaymentStatus = unserialize(FRM_REMINDERLIST_PAYMENT_OPTIONS);

//Se la pagina aperta è quella dei solleciti creati (non emessi), rimuovo l'opzione di stato pagamento: Pagato
if(REMINDER_OPERATION == INDEX_OPERATION_LIST_CREATED){
    unset($a_PaymentStatus[INDEX_PAYMENT_PAYED]);
}

$PageTitle = CheckValue('PageTitle','s') ?: '/';
$Filter = CheckValue('Filter', 'n');
$CreationDate = CheckValue("CreationDate", "s") ?: date('d/m/Y');
$Order_Type = $Order_Type != "" ? $Order_Type : key($a_OrderOptions);
$Search_HasDocumentation = $Search_HasDocumentation > 0 ? $Search_HasDocumentation : INDEX_EXCLUSIVE;

//Preleva le checkbox dei solleciti selezionati dalla sessione, in modo da riselezionarli
$a_SelectedCheckboxes = $_SESSION['Checkboxes']['frm_reminderlist.php'] ?? array();
unset($_SESSION['Checkboxes']['frm_reminderlist.php']);

if($Filter > 0){
    $str_Order = frmReminderListOrderBy();
    $str_Where = frmReminderListWhere(REMINDER_OPERATION);
    $str_Where .= " AND F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year'];
    
    $cls_view = new CLS_VIEW(FRM_REMINDER_LIST);
    
    $query = $cls_view->generateSelect($str_Where, null, $str_Order);
    $a_Results = $rs->getResults($rs->SelectQuery($query)) ?: array();
} else {
    $Search_IsLastEmitted = 1;
}

echo $str_out;
?>
<script>
SARIDA.mostraCaricamento("Caricamento in corso...");
</script>
<div class="row-fluid">
	<form id="f_frm_reminderlist" action="<?= $action; ?>" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
		<input type="hidden" id="PrintType" name="PrintType" value="">
		<input type="hidden" name="Operation" value="<?= REMINDER_OPERATION; ?>">
		
		<div class="col-sm-11">
            <div class="col-sm-2 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect(unserialize(FRM_REMINDERLIST_NATIONALITY), true, 'TypePlate', 'TypePlate', $s_TypePlate, false, null, null, ''); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Stato pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($a_PaymentStatus, true, 'Search_Status', 'Search_Status', $Search_Status, false, null, null, 'Tutti'); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Solo ultimi emessi
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" id="Search_IsLastEmitted" name="Search_IsLastEmitted" value="1" <?= $Search_IsLastEmitted > 0 ? "checked" : ""; ?>/>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Presenza ricorso
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" id="Search_Dispute" name="Search_Dispute" value="1" <?= $Search_Dispute > 0 ? "checked" : ""; ?>/>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Da data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_date" type="text" value="<?= $Search_FromNotificationDate; ?>" name="Search_FromNotificationDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_date" type="text" value="<?= $Search_ToNotificationDate; ?>" name="Search_ToNotificationDate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Da Cron.
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_numeric" type="text" value="<?= $Search_FromProtocolId; ?>" name="Search_FromProtocolId">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A cron.
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_numeric" type="text" value="<?= $Search_ToProtocolId; ?>" name="Search_ToProtocolId">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Da sollecito
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_numeric" type="text" value="<?= $Search_FromId; ?>" name="Search_FromId">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A sollecito
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_numeric" type="text" value="<?= $Search_ToId; ?>" name="Search_ToId">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Data elaborazione solleciti
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateSelectQuery("SELECT DISTINCT DATE_FORMAT(ProcessingPaymentDateTime, '%d/%m/%Y') ProcessingPaymentDateTime, DATE_FORMAT(ProcessingPaymentDateTime, '%Y/%m/%d') ProcessingPaymentDateTimeOrd FROM Fine WHERE ProcessingPaymentDateTime IS NOT NULL AND ReminderDate IS NOT NULL AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." ORDER BY DATE_FORMAT(ProcessingPaymentDateTime, '%Y/%m/%d') DESC", 'Search_Date', 'ProcessingPaymentDateTime', 'ProcessingPaymentDateTime', $Search_Date, false) ?>
            </div>
            <?php if(REMINDER_OPERATION == INDEX_OPERATION_LIST_CREATED): ?>
                <div class="col-sm-2 BoxRowLabel">
                    Con doc. esistente:
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?php foreach(unserialize(FRM_REMINDERLIST_HASDOCUMENT_OPTIONS) as $value => $name): ?>
                        <div class="col-sm-4">
                        	<input type="radio" name="Search_HasDocumentation" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_HasDocumentation ? ' checked' : '' ?>>
                        	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                    	</div>
                    <?php endforeach; ?>
                </div>
                <div class="col-sm-4 BoxRowLabel">
                </div>
            <?php else: ?>
                <div class="col-sm-9 BoxRowLabel">
                </div>
            <?php endif; ?>
            
        	<div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel table_caption_I">
                Ordina stampa per:
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?php foreach($a_OrderOptionsSelect as $value => $name): ?>
                    <div class="col-sm-3">
                    	<input type="radio" name="Order_Type" value="<?= $value; ?>" style="top:0;"<?= $value == $Order_Type ? ' checked' : '' ?>>
                    	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                	</div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-2 BoxRowLabel table_caption_I">
                Data creazione stampa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" class="form-control frm_field_date frm_field_required" name="CreationDate" id="CreationDate" value="<?= $CreationDate ?>">
            </div>
            <div class="col-sm-3 BoxRowLabel table_caption_I">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:11.4rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        	<button type="submit" data-printtype="<?= FRM_REMINDERLIST_ACTION_PRINTXLS ?>" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        	<button type="submit" data-printtype="<?= FRM_REMINDERLIST_ACTION_PRINTPDF ?>" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
        </div>

    	<div class="clean_row HSpace4"></div>
    	
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked /></div>
        <div class="table_label_H col-sm-1">ID sollecito</div>
		<div class="table_label_H col-sm-1">Cron</div>
		<div class="table_label_H col-sm-1">Data sollecito</div>
		<div class="table_label_H col-sm-2">Trasgressore</div>
        <div class="table_label_H col-sm-1">Targa</div>
        <div class="table_label_H col-sm-1">Data notifica</div>
		<div class="table_label_H col-sm-1">Data</div>
		<div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Stato pratica</div>
		<div class="table_label_H col-sm-1"></div>
		
		<div class="clean_row HSpace4"></div>

		<?php if($Filter <= 0 || empty($s_TypePlate)): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Selezionare nazionalità
			</div>
        <?php else: ?>
        	<?php if (!empty($a_Results)): ?>
                <?php for($i=$pagelimit; $i < PAGE_NUMBER*$page; $i++): ?>
                	<?php if(!empty($result = $a_Results[$i] ?? array())): ?>
    		            <div class="tableRow">
    	          			<div class="col-sm-1" style="text-align:center;padding:0">
    	            			<div class="table_caption_button col-sm-6" style="text-align:center;">
    	            				<input type="checkbox" name="checkbox[]" value="<?= $result['FineReminderId']; ?>" <?= in_array($result['FineReminderId'], $a_SelectedCheckboxes) || empty($a_SelectedCheckboxes) ? 'checked ' : ''; ?>/>
                				</div>
    	            			<div class="table_caption_H col-sm-6" style="text-align:center;">
                    				<?= (PAGE_NUMBER*($page-1))+($i+1); ?>
                				</div>
            				</div>
                            <div class="table_caption_H col-sm-1"><?= $result['FineReminderId']; ?></div>
                            <div class="table_caption_H col-sm-1"><?= $result['ProtocolId'] . ' / ' . $result['ProtocolYear']; ?></div>
                            <div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($result['PrintDate']); ?></div>
                            <div class="table_caption_H col-sm-2"><?= implode(' ', array($result['CompanyName'], $result['Surname'], $result['Name'])); ?></div>
                            <div class="table_caption_H col-sm-1"><?= $result['VehiclePlate']; ?><i class="<?= $aVehicleTypeId[$result['VehicleTypeId']]; ?>" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
                            <div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($result['NotificationDate']); ?></div>
                            <div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($result['FineDate']); ?></div>
                            <div class="table_caption_H col-sm-1 text-center"><?= TimeOutDB($result['FineTime']); ?></div>
                            <div class="table_caption_H col-sm-1 text-center">
                            	<?php if($result['StatusTypeId'] == 30): ?>
                            		<i id="<?= $result['FineId']; ?>" role="button" data-toggle="tooltip" data-placement="top" title="Verbale pagato" class="fa fa-eur tooltip-r" style="margin-top:0.2rem;font-size:1.8rem;color:#3C763D"></i>
                            	<?php elseif($result['StatusTypeId'] == 28 || ($result['StatusTypeId'] == 40 && $result['PaymentId'] > 0)): ?>
                            		<i id="<?= $result['FineId']; ?>" role="button" data-toggle="tooltip" data-placement="top" title="Verbale pagato parzialmente" class="fa fa-eur tooltip-r" style="margin-top:0.2rem;font-size:1.8rem;color:#FFF952"></i>
                            	<?php elseif($result['StatusTypeId'] == 27 || ($result['StatusTypeId'] == 40 && $result['PaymentId'] <= 0)): ?>
                            		<i data-toggle="tooltip" data-placement="top" title="Verbale non pagato" class="fa fa-eur tooltip-r" style="margin-top:0.2rem;font-size:1.8rem;color:#A94442"></i>
                            	<?php endif;?>
                            </div>
                            <div class="table_caption_button col-sm-1"></div>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                    <?php endif; ?>
        		<?php endfor; ?>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
                
                <div class="clean_row HSpace4"></div>
        	<?php endif; ?>
        	
        	<?= CreatePagination(PAGE_NUMBER, count($a_Results), $page, impostaParametriUrl(array("Filter" => 1), $str_CurrentPage),""); ?>
        <?php endif; ?>
        <div class="table_label_H col-sm-12" style="height:auto;color:white;">
            <div class="col-sm-6 text-left" style="padding:1rem;">
     		    <div class="col-sm-12">Leggenda:</div>
     		    <div class="col-sm-3">
     		    	<i class="fas fa-euro"></i> Stato pagamento
                </div>
    		</div>
        </div>
	</form>
</div>
<div id="overlay_PaymentView">
    <div id="FormPaymentTrespasser">
    </div>
</div>
<script>
	$(document).ready(function () {
		SARIDA.nascondiCaricamento();
		
		<?= require ('inc/jquery/overlay_search_payment.php')?>
		
        $('#printExcel, #printPdf, #search').click(function () {
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
        		$('#f_frm_reminderlist').attr('action', 'frm_reminderlist_exe.php');
        		$('#PrintType').val($(this).data('printtype'));
        	}
        });
        
        $('#f_frm_reminderlist').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        	SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
        });
        
        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_frm_reminderlist").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_frm_reminderlist").trigger( "check" );
        });
        
        $("#f_frm_reminderlist").on('check', function(){
    		$('#printExcel, #printPdf').prop('disabled', !$('input[name=checkbox\\[\\]]:checked').length > 0);
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