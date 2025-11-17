<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');

$str_Where = "1=1 AND CityId='{$_SESSION['cityid']}'";

if($Search_FromProtocolId != ""){
    $str_Where .= " AND ProtocolId=". $Search_FromProtocolId;
}
if($Search_Plate!=""){
    $str_Where .= " AND VehiclePlate='". $Search_Plate."'";
}
if($Search_PaymentName!=""){
    $str_Where .= " AND PaymentName='". $Search_PaymentName."'";
}
if($Search_Ref!=""){
    $str_Where .= " AND Code='". $Search_Ref."'";
}
if($_SESSION['userlevel']>0){
    if ($Search_CurrentYear < 2) {
        $str_Where .= " AND (ProtocolYear =" . $_SESSION['year']." OR ProtocolYear IS NULL)";
    }
} else {
    if ($Search_CurrentYear < 2) {
        $str_Where .= " AND ProtocolYear =" . $_SESSION['year'];
    } else {
        $str_Where .= " AND ProtocolYear IS NOT NULL";
    }
}
if($Search_FifthField!=""){
    $str_Where .= " AND FifthField='". $Search_FifthField ."'";
}
if($Search_Id!=""){
    $str_Where .= " AND FineId=". $Search_Id;
}
if ($Search_FromPaymentDate != "") {
    $str_Where .= " AND PaymentDate>='".DateInDB($Search_FromPaymentDate)."'";
}
if ($Search_ToPaymentDate != "") {
    $str_Where .= " AND PaymentDate<='".DateInDB($Search_ToPaymentDate)."'";
}
if ($Search_FromCreditDate != "") {
    $str_Where .= " AND CreditDate>='".DateInDB($Search_FromCreditDate)."'";
}
if ($Search_ToCreditDate != "") {
    $str_Where .= " AND CreditDate<='".DateInDB($Search_ToCreditDate)."'";
}

$n_ProtocolId=0;
$f_TotalAmount = 0.00;

$rs_FinePayment = $rs->Select('V_FinePaymentAll', $str_Where, 'ProtocolYear DESC,ProtocolId DESC');
$RowNumber = mysqli_num_rows($rs_FinePayment);
mysqli_data_seek($rs_FinePayment, $pagelimit);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_Search" action="mgmt_payment.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_FromProtocolId" type="text" value="<?= $Search_FromProtocolId; ?>">
            </div>           
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" value="<?= $Search_Plate; ?>">
            </div>          
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" value="<?= $Search_Ref; ?>">
            </div>           
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_PaymentName" type="text" value="<?= $Search_PaymentName; ?>">
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                ID verbale
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_Id" type="text" value="<?= $Search_Id; ?>">
            </div>    
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Quinto campo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_FifthField" type="text" value="<?= $Search_FifthField; ?>">
            </div>     
            <div class="col-sm-1 BoxRowLabel font_small">
                Da data pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_FromPaymentDate" type="text" value="<?= $Search_FromPaymentDate; ?>">
            </div>             
            <div class="col-sm-1 BoxRowLabel font_small">
                A data pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_ToPaymentDate" type="text" value="<?= $Search_ToPaymentDate; ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Da data accredito
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_FromCreditDate" type="text" value="<?= $Search_FromCreditDate; ?>">
            </div>             
            <div class="col-sm-1 BoxRowLabel font_small">
                A data accredito
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="Search_ToCreditDate" type="text" value="<?= $Search_ToCreditDate; ?>">
            </div>

            <div class="col-sm-1 BoxRowCaption font_small">
            	<input type="hidden" value="2" name="Search_CurrentYear">
                <input type="checkbox" value="1" name="Search_CurrentYear" <?= ChkCheckButton($Search_CurrentYear < 2 ? 1 : 0); ?>>
                <span style="vertical-align: text-bottom;"> Anno corrente</span>
            </div>
		</div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:4.5rem">
            	<button type="button" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" style="margin-top:0;width:50%;height:100%;float:left">
            		<i class="glyphicon glyphicon-search" style="font-size:2rem;"></i>
        		</button>
        		<?= ChkButton($aUserButton, 'act','
                    <button type="button" data-toggle="tooltip" data-placement="top" title="Analisi pagamenti" class="tooltip-r btn btn-warning" id="analysis" style="margin-top:0;width:50%;height:100%">
            		  <i class="glyphicon glyphicon-stats" style="font-size:2rem;"></i>
                    </button>"></i>'); 
                ?>
            </div>
        </div>
	</form>
	
	<div class="clean_row HSpace4"></div>
	
	<div class="table_label_H col-sm-1">Cron</div>
	<div class="table_label_H col-sm-2">Dati atto</div>
	<div class="table_label_H col-sm-4">Nominativo pagamento</div>
	<div class="table_label_H col-sm-1">Importo</div>
    <div class="table_label_H col-sm-1">Data reg.</div>
	<div class="table_label_H col-sm-1">Data pag.</div>
	<div class="table_label_H col-sm-1">Data accr.</div>
	<div class="table_add_button col-sm-1 right">
		<a href="mgmt_payment_add.php<?= $str_GET_Parameter; ?>">
			<?= ChkButton($aUserButton, 'add','<span data-toggle="tooltip" data-placement="top" title="Inserisci" class="tooltip-r glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>'); ?>
		</a>
	</div>
	
	<div class="clean_row HSpace4"></div>
	
	<?php if($RowNumber > 0): ?>
		<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
			<?php $r_FinePayment = $rs->getArrayLine($rs_FinePayment);?>
			<?php if(!empty($r_FinePayment)): ?>
				<?php
				    $f_TotalAmount += $r_FinePayment['Amount'];
    				if($r_FinePayment['ProtocolId']==''){
    				    $str_FineData = '';
    				    $FineId = 0;
    				    $ProtocolId = 'NON ASSOC.';
    				}else{
    				    $str_FineData = DateOutDB($r_FinePayment['FineDate']) . ' - ' . TimeOutDB($r_FinePayment['FineTime']) . ' <span style="position:absolute; right:0.5rem;">' . StringOutDB($r_FinePayment['VehiclePlate']) . '</span>';
    				    $FineId = $r_FinePayment['FineId'];
    				    $ProtocolId = $r_FinePayment['ProtocolId'].'/'.$r_FinePayment['ProtocolYear'];
    				}
				?>
				<?php if($n_ProtocolId != $r_FinePayment['ProtocolId'] && $n_ProtocolId > 0): ?>
					<div class="clean_row HSpace4"></div>
				<?php endif; ?>
				<?php $n_ProtocolId = $r_FinePayment['ProtocolId']; ?>
				<div class="tableRow">
					<div class="table_caption_H col-sm-1<?= $r_FinePayment['ProtocolId']== '' ? ' text-danger' : ''?>"><?= $ProtocolId; ?></div>
					<div class="table_caption_H col-sm-2"><?= $str_FineData; ?></div>
                    <div class="table_caption_H col-sm-4"><?= $r_FinePayment['PaymentName']; ?></div>
					<div class="table_caption_H col-sm-1 text-right" style="padding-right:0.5rem;"><?= number_format($r_FinePayment['Amount'], 2, '.', ''); ?> €</div>
					<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($r_FinePayment['RegDate']); ?></div>
					<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($r_FinePayment['PaymentDate']); ?></div>
					<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($r_FinePayment['CreditDate']); ?></div>
					<div class="table_caption_button col-sm-1">
						<?= ChkButton($aUserButton, 'viw','<a href="mgmt_payment_viw.php'.$str_GET_Parameter.'&PaymentId='.$r_FinePayment['PaymentId'].'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="top:0.5rem;"></span></a>'); ?>
						<?= ChkButton($aUserButton, 'upd','<a href="mgmt_payment_upd.php'.$str_GET_Parameter.'&PaymentId='.$r_FinePayment['PaymentId'].'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="top:0.5rem;"></span></a>'); ?>
						<?= ChkButton($aUserButton, 'exp','<a href="mgmt_payment_exp.php'.$str_GET_Parameter.'&PaymentId='.$r_FinePayment['PaymentId'].'&FineId='.$FineId.'"><span data-toggle="tooltip" data-placement="top" title="Rimborsa" class="tooltip-r fa fa-money glyphicon" style="top:0.4rem;"></span></a>'); ?>
						<?= ChkButton($aUserButton, 'del','<a href="mgmt_payment_del.php'.$str_GET_Parameter.'&PaymentId='.$r_FinePayment['PaymentId'].'"><span data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r glyphicon glyphicon-remove-sign" style="top:0.5rem;"></span></a>'); ?>
					</div>
					<?php if ($r_FinePayment['RefundStatus']): ?>
            			<?php $rs_FineRefund = $rs->Select('FineRefund',"PaymentId=".$r_FinePayment['PaymentId']); ?>
            			<?php while($r_FineRefund = mysqli_fetch_array($rs_FineRefund)): ?>
							<div class="table_caption_H col-sm-9 text-right">Rimborso </div>
							<div class="table_caption_H col-sm-1"><?= number_format($r_FineRefund['Amount'], 2, '.', ''); ?> €</div>
							<div class="table_caption_H col-sm-1 text-right" style="padding-right:0.5rem;"><?= DateOutDB($r_FineRefund['RefundDate']); ?></div>
							<div class="table_caption_H col-sm-1"></div>
        				<?php endwhile; ?>
            		<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endfor; ?>
        <div class="table_caption_I col-sm-3 table-bordered"></div>
        <div class="table_caption_I col-sm-5 table-bordered text-right" style="padding-right:0.5rem;line-height: 2.2rem;"><strong>TOTALE</strong></div>
        <div class="table_caption_I col-sm-1 table-bordered text-right" style="padding-right:0.5rem;line-height: 2.2rem;"><strong><?= number_format($f_TotalAmount, 2, '.', ''); ?> €</strong></div>
        <div class="table_caption_I col-sm-2 table-bordered"></div>
        <div class="table_caption_I col-sm-1 table-bordered"></div>
        
        <div class="clean_row HSpace4"></div>
		<?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage.$str_GET_Parameter, null);?>
	<?php else: ?>
        <div class="table_caption_H col-sm-12 text-center">
        	Nessun record presente.
        </div>
	<?php endif; ?>
</div>
<script type="text/javascript">
	$(document).ready(function () {
        $('#search, #analysis').click(function () {
        	if ($(this).attr("id") == 'analysis'){
        		$('#f_Search').attr('action', 'mgmt_payment_act.php');
        	}
            $("#search, #analysis").prop('disabled', true);
			$(this).html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;"></i>');
            $('#f_Search').submit();
        });

        $('#f_Search').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                $("#f_Search").submit();
            }
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
