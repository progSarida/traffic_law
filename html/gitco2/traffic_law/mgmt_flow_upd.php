<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_html.php");
require_once(CLS."/cls_flow.php");
require_once(INC."/function.php");
require_once(INC."/header.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

function flowNationality($r_Flow){
    $path = NATIONAL_FLOW."/".$r_Flow['CityId']."/";
    
    return file_exists($path.$r_Flow['FileName'])
    ? 'N' 
    : 'F';
}

$Id= CheckValue('Id','n');

$cls_flow = new cls_flow();

$cls_html = new cls_html();
$rs_Row = $rs->Select('V_Flow',"Id=".$Id);
$r_Row = mysqli_fetch_array($rs_Row);

$a_deadlineDays = $cls_flow->getDeadlinesDays($r_Row['PrintTypeId'], flowNationality($r_Row));

$frm_required = array("Upload"=>"","Processing"=>"","Payment"=>"", "Bank"=>"", "Send"=>"", "Shipping"=>"");
$readonly = array("Payment"=>"readonly", "Send"=>"readonly");
if($r_Row['UploadDate']!=null){
    $frm_required['Upload'] = "frm_field_required";
}
if($r_Row['ProcessingDate']!=null){
    $readonly['Payment'] = "";
    $frm_required['Processing'] = "frm_field_required";
}
if($r_Row['PaymentDate']!=null && $r_Row['PaymentBank']!=null)
    $readonly['Send'] = "";

if($r_Row['PaymentDate']!=null){
    $frm_required['Payment'] = "frm_field_required";
    if($r_Row['PaymentBank']!=null)
        $frm_required['Bank'] = "frm_field_required";
}
if($r_Row['SendDate']!=null){
    $frm_required['Send'] = "frm_field_required";
    if($r_Row['ShippingOffice']!=null)
        $frm_required['Shipping'] = "frm_field_required";
}


$query = "SELECT Title FROM sarida.City WHERE Id='".$r_Row['CityId']."'";
$cityRow = $rs->getArrayLine($rs->ExecuteQuery($query));

$a_invoices = $rs->getResults($rs->Select('Flow_Invoices',"Year>=".$r_Row['Year'],"Date DESC, Year DESC, Number DESC"));
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>$r_Row['PrintInvoiceId'], "text"=>array("[Number]"," / ","[Year]"," del ","[Date]"));
$opt_printInvoice = $cls_html->getOptions($a_invoices,$a_selection);
$a_selection = array("value"=>"Id","firstOpt"=>1,"selected"=>$r_Row['PostageInvoiceId'], "text"=>array("[Number]"," / ","[Year]"," del ","[Date]"));
$opt_postageInvoice = $cls_html->getOptions($a_invoices,$a_selection);

$TotZone = $r_Row['Zone0Postage']*$r_Row['Zone0Number']+$r_Row['Zone1Postage']*$r_Row['Zone1Number']+$r_Row['Zone2Postage']*$r_Row['Zone2Number']+$r_Row['Zone3Postage']*$r_Row['Zone3Number'];
$Tot = $TotZone + $r_Row['PrintCost']*$r_Row['RecordsNumber'];

$str_out = '
    	<div class="row-fluid" style="height:6rem;background-color: #fff;">
        	<div class="col-sm-12" >
        		<div class="col-sm-6">
                    <span class="title_city">('.$r_Row['CityId'].') '. $cityRow['Title'] .'</span>
                </div>
				
        		<div class="col-sm-6">
				</div>
			</div>	
            <div class="col-sm-12" id="div_message_page">
            </div>
        </div>

        ';

$str_invoices = '';
if(DateOutDB($r_Row['SendDate'])!=null && $r_Row['ShippingOffice']!=null){
    $str_invoices = '<div class="clean_row HSpace16"></div>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel">
                        Stampa e imbustamento
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fattura numero
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select style="width: 99%;" name="PrintInvoiceId">
                        '.$opt_printInvoice.'
                        </select>
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        
                    </div>
                    
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    
                    <div class="col-sm-1 BoxRowLabel">
                        Quantità
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric changeAmounts" type="text" id="PrintNumber" name="PrintNumber" value="'.$r_Row['RecordsNumber'].'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Costo unitario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_currency changeAmounts" type="text" id="PrintCost" name="PrintCost" value="'.number_format($r_Row['PrintCost'],2).'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Totale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        &euro; <span id="PrintTotal">'.number_format($r_Row['RecordsNumber']*$r_Row['PrintCost'],2).'</span>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        
                    </div>
                </div>
                <div class="clean_row HSpace16"></div>

                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel">
                        Spese postali
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fattura numero
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select style="width: 99%;" name="PostageInvoiceId">
                        '.$opt_postageInvoice.'
                        </select>
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        
                    </div>
                    
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    
                    <div class="col-sm-1 BoxRowLabel">
                        Quantità ITA
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric changeAmounts" type="text" id="Zone0Number" onchange="" name="Zone0Number" value="'.$r_Row['Zone0Number'].'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Costo unitario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_currency changeAmounts" type="text" id="Zone0Postage" name="Zone0Postage" onchange="" value="'.number_format($r_Row['Zone0Postage'],2).'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Totale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        &euro; <span id="Zone0Total">'.number_format($r_Row['Zone0Number']*$r_Row['Zone0Postage'],2).'</span>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    
                   
                    <div class="col-sm-1 BoxRowLabel">
                        Quantità Zona 1
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric changeAmounts" type="text" id="Zone1Number" onchange="" name="Zone1Number" value="'.$r_Row['Zone1Number'].'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Costo unitario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_currency changeAmounts" type="text" id="Zone1Postage" name="Zone1Postage" onchange="" value="'.number_format($r_Row['Zone1Postage'],2).'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Totale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        &euro; <span id="Zone1Total">'.number_format($r_Row['Zone1Number']*$r_Row['Zone1Postage'],2).'</span>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    
                    <div class="col-sm-1 BoxRowLabel">
                        Quantità Zona 2
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric changeAmounts" type="text" id="Zone2Number" onchange="" name="Zone2Number" value="'.$r_Row['Zone2Number'].'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Costo unitario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_currency changeAmounts" type="text" id="Zone2Postage" name="Zone2Postage" onchange="" value="'.number_format($r_Row['Zone2Postage'],2).'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Totale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        &euro; <span id="Zone2Total">'.number_format($r_Row['Zone2Number']*$r_Row['Zone2Postage'],2).'</span>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        
                    </div>
                    
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    

                    <div class="col-sm-1 BoxRowLabel">
                        Quantità Zona 3
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_numeric changeAmounts" type="text" id="Zone3Number" onchange="" name="Zone3Number" value="'.$r_Row['Zone3Number'].'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Costo unitario
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_currency changeAmounts" type="text" id="Zone3Postage" name="Zone3Postage" onchange="" value="'.number_format($r_Row['Zone3Postage'],2).'" style="width:9rem">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Totale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        &euro; <span id="Zone3Total">'.number_format($r_Row['Zone3Number']*$r_Row['Zone3Postage'],2).'</span>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        
                    </div>
                </div>
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-12">
                    <div class="col-sm-5 BoxRowLabel">
                        TOTALE COMPLESSIVO
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                          &euro; <span id="Total">'.number_format($Tot,2).'</span>
                    </div>    
                    <div class="col-sm-6 BoxRowCaption">
                          
                    </div>  
                </div>';
}


$str_out .='
        <form name="f_flow" id="f_flow" method="post" action="mgmt_flow_upd_exe.php">
        <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
        <input type="hidden" name="Id" value="'.$Id.'">
        <input type="hidden" name="P" value="'.basename($_SERVER['PHP_SELF']).'">
        <input type="hidden" name="FileName" value="'.$r_Row['FileName'].'">
        <input type="hidden" name="CreationDate" value="'.$r_Row['CreationDate'].'">
        <input type="hidden" name="PrinterId" value="'.$r_Row['PrinterId'].'">
        
    	<div class="row-fluid">
        	<div class="col-sm-12">
                <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4.4rem; font-size:2rem;">
                    <strong>Dettagli del flusso</strong>
                </div>
                <div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Numero
					</div>
					<div class="col-sm-10 BoxRowCaption">
        				'.$r_Row['Number']. '/' . $r_Row['Year'].'
					</div>
					
  				</div>
  				<div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Documento
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$r_Row['DocumentType'].'
					</div>
					<div class="col-sm-2 BoxRowLabel">
        				Tipologia
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$r_Row['PrintType'].'
					</div>
					<div class="col-sm-2 BoxRowLabel">
                        Record
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        '.$r_Row['HistoryDocs'].'
                    </div>
  				</div>
  				<div class="clean_row HSpace4"></div> 
  				<div class="col-sm-12">
  				    <div class="col-sm-2 BoxRowLabel">
                        Stampatore
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        '.$r_Row['Printer'].'
                    </div>
        			<div class="col-sm-2 BoxRowLabel">
                        File
					</div>
					<div class="col-sm-6 BoxRowCaption">
                        '.$r_Row['FileName'].'
                    </div>
  				</div>
  				
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">

                    <div class="col-sm-2 BoxRowLabel">
                        Notifiche
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$r_Row['TotalNotifications'].'
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="color: lightgreen">
                        '.$r_Row['PositiveNotifications'].' Positive
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="color: lightcoral">
                        '.$r_Row['NegativeNotifications'].' Negative
                    </div>
                    <div class="col-sm-6 BoxRowCaption">

                    </div>
                </div>
                <div class="clean_row HSpace4"></div> 
  				<div class="col-sm-12">

                    <div class="col-sm-2 BoxRowLabel">
                        Note
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Note" name="Note" value="'. $r_Row['Note'] .'" style="width:60rem">
                    </div>
                </div>
  				<div class="clean_row HSpace16"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
                        Data creazione
					</div>
					<div class="col-sm-6 BoxRowCaption">
                        '.DateOutDB($r_Row['CreationDate']).'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        STATUS CREATO
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        
                    </div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
                        Data upload
					</div>
					<div class="col-sm-6 BoxRowCaption">
                        <input class="form-control frm_field_date '.$frm_required['Upload'].'" type="text" id="UploadDate" name="UploadDate" value="'.DateOutDB($r_Row['UploadDate']).'" style="width:9rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        STATUS UPLOAD
                    </div>
                    <div class="col-sm-2 BoxRowCaption font_small">
                        '.(isset($a_deadlineDays['UPLOAD']) ? 'entro '.$a_deadlineDays['UPLOAD'].' giorni dalla data di creazione' : '').'
                    </div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
                        Data fine lavorazione
					</div>
					<div class="col-sm-6 BoxRowCaption">
                        <input class="form-control frm_field_date frm_check_required '.$frm_required['Processing'].'" type="text" id="ProcessingDate" name="ProcessingDate" value="'.DateOutDB($r_Row['ProcessingDate']).'" style="width:9rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        STATUS LAVORATO
                    </div>
                    <div class="col-sm-2 BoxRowCaption font_small">
                        '.(isset($a_deadlineDays['PROCESS']) ? 'entro '.$a_deadlineDays['PROCESS'].' giorni dalla data di upload' : '').'
                    </div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
                        Data pagamento
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_check_required frm_field_date '.$frm_required['Payment'].'" '.$readonly['Payment'].' type="text" id="PaymentDate" name="PaymentDate" value="'.DateOutDB($r_Row['PaymentDate']).'" style="width:9rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Banca
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_check_required frm_field_string '.$frm_required['Bank'].'" '.$readonly['Payment'].' type="text" id="PaymentBank" name="PaymentBank" value="'. $r_Row['PaymentBank'] .'" style="width:15rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        STATUS PAGATO
                    </div>
                    <div class="col-sm-2 BoxRowCaption font_small">
                        '.(isset($a_deadlineDays['PAYMENT']) ? 'entro '.$a_deadlineDays['PAYMENT'].' giorni dalla data di fine lavorazione' : '').'
                    </div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
                        Data consegna
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date '.$frm_required['Send'].'" '.$readonly['Send'].' type="text" id="SendDate" name="SendDate" value="'.DateOutDB($r_Row['SendDate']).'" style="width:9rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ufficio di consegna
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string '.$frm_required['Shipping'].'" '.$readonly['Send'].' type="text" id="ShippingOffice" name="ShippingOffice" value="'. $r_Row['ShippingOffice'] .'" style="width:15rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        STATUS CONSEGNATO
                    </div>
                    <div class="col-sm-2 BoxRowCaption font_small">
                        '.(isset($a_deadlineDays['SHIPMENT']) ? 'entro '.$a_deadlineDays['SHIPMENT'].' giorni dalla data di pagamento' : '').'
                    </div>
  				</div>
                   '.$str_invoices.'
                <div class="clean_row HSpace16"></div>
  		    </div>	

            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="update" value="Modifica" />
                    <button class="btn btn-default" id="back">Indietro</button>
                 </div>    
            </div>

    </form>	
    </div>';


echo $str_out;
?>
<script type="text/javascript">

    $('document').ready(function(){
        $('#back').click(function () {
            window.location = "<?= impostaParametriUrl(array('Filter' => 1), $str_BackPage); ?>"
            return false;
        });

        $('.frm_check_required').click(
            function(){

                switch($(this).attr('id')){
                    case "ProcessingDate":
                        if($("#UploadDate").val()!="")
                            $(this).prop('readonly',false);
                        else
                            $(this).prop('readonly',true);
                        break;
                    case "PaymentDate":
                    case "PaymentBank":
                        if($("#ProcessingDate").val()!="")
                            $(this).prop('readonly',false);
                        else
                            $(this).prop('readonly',true);
                        break;
                }
            }
        );

        function number_format (number, decimals, dec_point, thousands_sep) {

            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        function setAmounts(costId, NumberId, TotId){
            var Cost = $('#'+costId).val();
            Cost = parseFloat( Cost );
            $('#'+costId).val(number_format(Cost,2));
            var Number = $('#'+NumberId).val();
            Number = parseInt(Number);
            $('#'+TotId).text(number_format(Cost*Number,2));
            return Cost*Number;
        }

        $('.changeAmounts').change(function(){
            var totPrint = setAmounts("PrintCost","PrintNumber","PrintTotal");
            var totPostage = 0;
            for(var i=0; i<4; i++){
                totPostage+=setAmounts("Zone"+i+"Postage","Zone"+i+"Number","Zone"+i+"Total");
            }
            $('#Total').text(number_format(totPrint+totPostage,2));
        });


        $('#f_flow').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },

                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },

                frm_field_currency: {
                    selector: '.frm_field_currency',
                    validators: {
                        numeric: {
                            message: 'Euro'
                        }
                    }
                },

                frm_field_date: {
                    selector: '.frm_field_date',
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
                    }
                },
            }
        });

    });

</script>
<?php
include(INC."/footer.php");
