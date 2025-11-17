<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');

$DateType = CheckValue('DateType', 'n');
$FromPaymentDate = CheckValue('FromPaymentDate','s');
$ToPaymentDate = CheckValue('ToPaymentDate','s');

$FromRegDate= CheckValue('FromRegDate','s');
$ToRegDate= CheckValue('ToRegDate','s');

$FromFineDate= CheckValue('FromFineDate','s');
$ToFineDate= CheckValue('ToFineDate','s');

$FinePaymentSpecificationType = $r_Customer['FinePaymentSpecificationType'];

//Preleva le checkbox degli atti selezionati dalla sessione, in modo da riselezionarli, utile per quando si fa anteprima di stampa
$a_SelectedCheckboxes = $_SESSION['Checkboxes']['mgmt_payment_fees.php'] ?? array();
unset($_SESSION['Checkboxes']['mgmt_payment_fees.php']);

$FromProtocolId= CheckValue('FromProtocolId','n');
$ToProtocolId= CheckValue('ToProtocolId','n');

$FromAmount= str_replace(",",".",CheckValue('FromAmount','s'));
$ToAmount= str_replace(",",".",CheckValue('ToAmount','s'));

$PaymentTypeId= CheckValue('PaymentTypeId','n');

$CurrentYear = CheckValue('CurrentYear','n');

$str_CheckCurrentYear = "";

$str_DateColumn = $DateType > 0 ? 'P.CreditDate' : 'P.PaymentDate';
$str_Where = "P.CityId='".$_SESSION['cityid']."'";


if($FromPaymentDate!=""){
	$str_CurrentPage .="&FromPaymentDate=".$FromPaymentDate;
	$str_Where .= " AND $str_DateColumn >= '".DateInDB($FromPaymentDate)  ."'";
}
if($ToPaymentDate!=""){
	$str_CurrentPage .="&ToPaymentDate=".$ToPaymentDate;
	$str_Where .= " AND $str_DateColumn <= '".DateInDB($ToPaymentDate)  ."'";
}

if($FromRegDate!=""){
    $str_Where .= " AND P.RegDate >= '".DateInDB($FromRegDate)  ."'";
    $str_CurrentPage .="&FromRegDate=".$FromRegDate;
}

if($ToRegDate!=""){
    $str_Where .= " AND P.RegDate <= '".DateInDB($ToRegDate)  ."'";
    $str_CurrentPage .="&ToRegDate=".$ToRegDate;
}

if($FromFineDate!=""){
    $str_Where .= " AND P.FineDate >= '".DateInDB($FromFineDate)  ."'";
    $str_CurrentPage .="&FromFineDate=".$FromFineDate;
}
if($ToFineDate!=""){
    $str_Where .= " AND P.FineDate <= '".DateInDB($ToFineDate)  ."'";
    $str_CurrentPage .="&ToFineDate=".$ToFineDate;
}




if($FromProtocolId>0){
    $str_Where .= " AND P.ProtocolId >= $FromProtocolId";
    $str_CurrentPage .="&FromProtocolId=".$FromProtocolId;
}else{
    $FromProtocolId="";
}

if($ToProtocolId>0){
    $str_Where .= " AND P.ProtocolId <= $ToProtocolId";
    $str_CurrentPage .="&ToProtocolId=".$ToProtocolId;
}else{
    $ToProtocolId="";
}

if($PaymentTypeId>0){
    $str_Where .= ($PaymentTypeId==10) ? " AND (P.PaymentTypeId = 1 OR P.PaymentTypeId = 2 OR P.PaymentTypeId = 18)" : " AND P.PaymentTypeId = " .$PaymentTypeId;

    $str_CurrentPage .="&PaymentTypeId=".$PaymentTypeId;
}

if($CurrentYear){
    $str_Where .= " AND P.ProtocolYear =".$_SESSION['year'];
    $str_CurrentPage .="&CurrentYear=".$CurrentYear;
    $str_CheckCurrentYear =" CHECKED ";
}

if($FromAmount>0){
    $str_Where .= " AND P.Amount >= $FromAmount";
    $str_CurrentPage .="&FromAmount=".$FromAmount;
}

if($ToAmount>0){
    $str_Where .= " AND P.Amount <= $ToAmount";
    $str_CurrentPage .="&ToAmount=".$ToAmount;
}

$str_CurrentPage .="&DateType=".$DateType;

$str_fineArticle = '';

$strOrder = "PaymentDate";

echo $str_out;
?>

<form id="f_Search" action="<?=$str_CurrentPage?>" method="post">
<input type="hidden" name="FinePaymentSpecificationType" value="<?=$r_Customer['FinePaymentSpecificationType']?>">
<input type="hidden" name="PageTitle" value="<?=$PageTitle?>">
<div class="row-fluid">        
    <div class="col-sm-12" >
        <div class="col-sm-11" style="height:6.9rem; border-right:1px solid #E7E7E7;">

            <div class="col-sm-1 BoxRowLabel BoxRowCaption">
                <?=CreateArraySelect(array('Pagamento', 'Accredito'), true, 'DateType', 'DateType', $DateType, true);?>
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromPaymentDate" type="text" style="width:9rem" value="<?=$FromPaymentDate?>">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToPaymentDate" type="text" style="width:9rem" value="<?=$ToPaymentDate?>">
            </div>
            
            <div class="col-sm-1 BoxRowCaption" style="font-size:1rem;text-align:right">
                <input type="checkbox" name="CurrentYear" value="1" <?=$str_CheckCurrentYear?>>
            </div>
            <div class="col-sm-1 BoxRowCaption">
                Anno corrente
            </div>
            
            <?php if($r_Customer['CityUnion']>1){
                $Locality= CheckValue('Locality','s');
            
                if($Locality!=""){
                    $str_Where .= " AND Locality = '".$Locality."'";
                    $str_CurrentPage .="&Locality=".$Locality;
                }
                ?>
                <div class="col-sm-2 BoxRowLabel">
                    Località:
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?=CreateSelect(MAIN_DB.".City","UnionId='".$_SESSION['cityid']."'","Title","Locality","Id","Title",$Locality,false)?>
                </div>
            <?php }else{?>
                <div class="col-sm-5 BoxRowLabel">
                </div>
            <?php } ?>
                     
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Registrazione:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromRegDate" type="text" style="width:9rem" value="<?=$FromRegDate?>">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToRegDate" type="text" style="width:9rem" value="<?=$ToRegDate?>">
            </div>
            
            <div class="col-sm-1 BoxRowLabel">
                Accertamento:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromFineDate" type="text" style="width:9rem" value="<?=$FromFineDate?>">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToFineDate" type="text" style="width:9rem" value="<?=$ToFineDate?>">
            </div>
            
            <div class="col-sm-2 BoxRowLabel"> &nbsp;</div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Importo:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                 <input class="form-control frm_field_currency" name="FromAmount" type="text" style="width:8rem" value="<?=$FromAmount?>">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>         
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_currency" name="ToAmount" type="text" style="width:8rem" value="<?=$ToAmount?>">
            </div>
                     
            <div class="col-sm-1 BoxRowLabel">
                Cron:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">                
                <input class="form-control frm_field_numeric" name="FromProtocolId" type="text" style="width:8rem" value="<?=$FromProtocolId?>">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption"> 
                <input class="form-control frm_field_numeric" name="ToProtocolId" type="text" style="width:8rem" value="<?=$ToProtocolId?>">
            </div>

            <div class="col-sm-2 BoxRowLabel"></div>
                                                           
        </div>
        <div class="col-sm-1 table_caption_H" style="height:6.9rem;padding:0;">
            <button class="btn btn-primary" id="btn_src"  style="margin-top:0;width:100%;height:100%;">
                <i class="glyphicon glyphicon-search" style="margin-top:0.2rem;font-size:3.5rem;"></i>
            </button>
        </div>        
    </div>
</div>                    
</form>

<div class="row-fluid">
    <form name="f_fees" id="f_fees" action="mgmt_payment_fees_exe.php<?=$str_GET_Parameter?>" method="post">
    	<div class="col-sm-12">
    		<div class="col-sm-12" style="padding:0">
        		<div class="table_label_H col-sm-4"></div>
                <div class="table_label_H col-sm-4">Importi registrati</div>
                <div class="table_label_H col-sm-4">Importi attesi</div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3" style="padding:0">
            	<div class="table_label_H col-sm-3">Selez. <input type="checkbox" id="checkAll" checked /></div>
        		<div class="table_label_H col-sm-6">Data Pag./ID/Cron</div>
        		<div class="table_label_H col-sm-3">Riscosso</div>
        	</div>
            <div class="col-sm-9" style="padding:0">
            	<div class="col-sm-6" style="padding:0">
	        		<div class="table_label_H col-sm-2">Sanzione</div>
            		<div class="table_label_H col-sm-2">Ricerca</div>
            		<div class="table_label_H col-sm-2">Notifica</div>
            		<div class="table_label_H col-sm-2">Magg.</div>
            		<div class="table_label_H col-sm-2">Spese Comune</div>
            		<div class="table_label_H col-sm-1">Can</div>
            		<div class="table_label_H col-sm-1">Cad</div>
            	</div>
            	<div class="col-sm-6" style="padding:0">
	        		<div class="table_label_H col-sm-2">Sanzione</div>
            		<div class="table_label_H col-sm-2">Ricerca</div>
            		<div class="table_label_H col-sm-2">Notifica</div>
            		<div class="table_label_H col-sm-2">Magg.</div>
            		<div class="table_label_H col-sm-2">Spese Comune</div>
            		<div class="table_label_H col-sm-1">Can</div>
            		<div class="table_label_H col-sm-1">Cad</div>
            	</div>
            </div>
    		<div class="clean_row HSpace4"></div>
		</div>

<?php if($FromPaymentDate == "" && $ToPaymentDate=="") {?>
        <div class="table_caption_H col-sm-12">
			Scegliere un periodo di pagamento/accredito
		</div>
<?php }else{

        $n_Cont = 0;
    
        $str_query = "SELECT * FROM V_FinePayment P";
        $rs_Payment = $rs->SelectQuery("$str_query $str_fineArticle WHERE $str_Where ORDER BY $strOrder");
        while ($r_Payment = mysqli_fetch_array($rs_Payment)) {
                    
                //Scorporo attuale
                $Fee = round($r_Payment['Fee'],2);
                $NotificationFee =  round($r_Payment['NotificationFee'],2);
                $PercentualFee =  round($r_Payment['PercentualFee'],2);
                $ResearchFee = round($r_Payment['ResearchFee'],2);
                $CustomerFee = round($r_Payment['CustomerFee'],2);
                $CanFee = round($r_Payment['CanFee'],2);
                $CadFee = round($r_Payment['CadFee'],2);

                $str_FineLetter = ($r_Payment['FineCountryId']=="Z000") ? "U" : "ES";
                
                //Parametri per calcolo scorporo atteso
                $FineId = $r_Payment['FineId'];
                $PaymentDocumentId = $r_Payment['PaymentDocumentId'];
                $ReminderDate = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"))['ReminderDate'] ?? null;
                //Pagato
                $Amount = $r_Payment['Amount'];
                $ProtocolYear = $r_Payment['ProtocolYear'];
                //Calcolo scorporo atteso
                $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $Amount, $FineId, $_SESSION['cityid'], $ProtocolYear, $r_Payment['PaymentDate'], $ReminderDate);
                
                //trigger_error("********Return da FPST: ".json_encode($a_Fee));
                
                //Scorporo atteso
                $RightFee = isset($a_Fee['Fee']) ? round($a_Fee['Fee'],2) : 0.00;
                $RightNotificationFee = isset($a_Fee['NotificationFee']) ? round($a_Fee['NotificationFee'],2) : 0.00;
                $RightPercentualFee = isset($a_Fee['PercentualFee']) ? round($a_Fee['PercentualFee'],2) : 0.00;
                $RightResearchFee = isset($a_Fee['ResearchFee']) ? round($a_Fee['ResearchFee'],2) : 0.00;
                $RightCustomerFee = isset($a_Fee['CustomerFee']) ? round($a_Fee['CustomerFee'],2) : 0.00;
                $RightCanFee = isset($a_Fee['CanFee']) ? round($a_Fee['CanFee'],2) : 0.00;
                $RightCadFee = isset($a_Fee['CadFee']) ? round($a_Fee['CadFee'],2) : 0.00;
                
                //trigger_error("********Dati --> Notification: ".$RightNotificationFee." Research: ".$RightResearchFee."Customer notification: ".$RightCustomerFee);
                
                //Se non ci sono differenze tra le spese attuali e quelle attese, salta la riga. Vengono mostrate solo le anomalie
                //NB tutti gli importi da confrontare vengono arrotondati alla seconda cifra decimale
                if($Fee == $RightFee && $NotificationFee == $RightNotificationFee && $PercentualFee == $RightPercentualFee && $ResearchFee == $RightResearchFee && $CustomerFee == $RightCustomerFee && $CanFee == $RightCanFee && $CadFee == $RightCadFee){
                    continue;
                } else $n_Cont++;
                //Incremento il numero della riga solo dopo aver effettuato il controllo
                ?>
                <!-- Parametri della ricerca da mandare nell'url in modo che al ritorno rivalorizzi i filtri automaticamente-->
                <input type="hidden" name="ReturnPage" value="<?=$str_CurrentPage?>">
                <input type="hidden" name="FinePaymentSpecificationType" value="<?=$r_Customer['FinePaymentSpecificationType']?>">
				<div class="col-sm-3" style="padding:0">
					<!-- Check/Progressivo -->
                    <div class="col-sm-3" style="text-align:center;padding:0">
                    	<!-- Check -->
                    	<div class="table_caption_button col-sm-6" style="text-align:center;">
                    		<input <?=(in_array($r_Payment['PaymentId'], $a_SelectedCheckboxes) || empty($a_SelectedCheckboxes) ? 'checked ' : '')?> type="checkbox" name="checkbox[]" value="<?= $r_Payment['PaymentId']?>"/>
                    	</div>
                    	<!-- Progressivo -->
                    	<div class="table_caption_H col-sm-6" style="text-align:center;">
                    		<?=$n_Cont?>
                		</div>
            		</div>
            		<!-- Data pagamento/Cron -->
                    <div class="table_caption_H col-sm-6" style="text-align:left"><?=DateOutDB($r_Payment['PaymentDate'])?>&nbsp&nbsp-&nbsp&nbsp<?=$r_Payment['FineId']?>&nbsp&nbsp-&nbsp&nbsp<?=$r_Payment['ProtocolId'] .'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter?></div>
                    <!-- Riscosso -->
                    <div class="table_caption_H col-sm-3 "><?=NumberDisplay($r_Payment['Amount'])?></div>
                </div>
                
                <div class="col-sm-9" style="padding:0">
                	<!-- Fees errate -->
                	<div class="col-sm-6" style="padding:0">
                        <!-- Sanzione -->
                        <div class="table_caption_H col-sm-2 table_caption_error"><?=NumberDisplay($Fee)?></div>
                        <!-- Spese di ricerca (registrate) -->
                        <div class="table_caption_H col-sm-2 table_caption_error"><?=NumberDisplay($ResearchFee)?></div>
                        <!-- Spese di notifica (registrate) -->
                        <div class="table_caption_H col-sm-2 table_caption_error"><?=NumberDisplay($NotificationFee)?></div>
                        <!-- Spese di magg. semestrale (registrate) -->
                        <div class="table_caption_H col-sm-2 table_caption_error"><?=NumberDisplay($PercentualFee)?></div>
                        <!-- Altre spese (registrate) -->
                        <div class="table_caption_H col-sm-2 table_caption_error"><?=NumberDisplay($CustomerFee)?></div>
                        <!-- Spese CAN (registrate) -->
                        <div class="table_caption_H col-sm-1 table_caption_error"><?=NumberDisplay($CanFee)?></div>
                        <!-- Spese CAD (registrate) -->
                        <div class="table_caption_H col-sm-1 table_caption_error"><?=NumberDisplay($CadFee)?></div>
                	</div>
                	<!-- Fees corrette -->
                	<div class="col-sm-6" style="padding:0">
                        <!-- Sanzione -->
                        <div class="table_caption_H col-sm-2 table_caption_success"><?=NumberDisplay($RightFee)?></div>
                        <!-- Spese di ricerca (calcolate) -->
        				<div class="table_caption_H col-sm-2 table_caption_success"><?=NumberDisplay($RightResearchFee)?></div>
        				<!-- Spese di notifica (calcolate) -->
        				<div class="table_caption_H col-sm-2 table_caption_success"><?=NumberDisplay($RightNotificationFee)?></div>
                        <!-- Spese di magg. semestrale (registrate) -->
                        <div class="table_caption_H col-sm-2 table_caption_success"><?=NumberDisplay($RightPercentualFee)?></div>
        				<!-- Altre spese (calcolate) -->
        				<div class="table_caption_H col-sm-2 table_caption_success"><?=NumberDisplay($RightCustomerFee)?></div>
        				<!-- Spese CAN (calcolate) -->
        				<div class="table_caption_H col-sm-1 table_caption_success"><?=NumberDisplay($RightCanFee)?></div>
        				<!-- Spese CAD (calcolate) -->
        				<div class="table_caption_H col-sm-1 table_caption_success"><?=NumberDisplay($RightCadFee)?></div>
                	</div>
    			</div>
                
	            <div class="clean_row HSpace4"></div>
            <?php
        }
    }
        ?>
        	</form>
        	<?php if($n_Cont > 0){ ?>
            	<div class="table_caption_H col-sm-12" style="height:5rem;text-align:center;">
        			<input type="button" class="btn btn-success sub_Button" data-toggle="tooltip" title="Correggi gli importi" style="margin-top:1rem;" value="Aggiorna gli importi">
        		</div>
    		<?php } else { ?>
	            <div class="table_caption_H col-sm-12">
    		        Nessun pagamento da correggere presente
    	        </div>
    		<?php } ?>
        </div>
	<div>
</div>
 
<script type="text/javascript">

	$(document).ready(function () {

        $("#btn_src").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'mgmt_payment_fees.php');
            $('#btn_src').hide();
            $('#Progress').show();

            $('#f_Search').submit();

        });
		
    	$('#checkAll').click(function() {
                $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
                $("#f_fees").trigger( "check" );
            });
            
        $('input[name=checkbox\\[\\]]').change(function() {
                $("#f_fees").trigger( "check" );
            }); 
    	
    	$("#f_fees").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0){
        		$('.sub_Button').prop('disabled', false);
        		}
        	else
        		{
        		$('.sub_Button').prop('disabled', true);
        		}
        });
    	
    	$('.sub_Button').on('click', ()=>{
    		if(confirm("Si stanno per modificare gli importi delle spese in maniera definitiva. Continuare?")){
    			 $('#f_fees').submit();
    			 }
    		else {
            	e.preventDefault();
            	return false;
    			}
    	});
	});
        
</script>
<?php
include(INC."/footer.php");
