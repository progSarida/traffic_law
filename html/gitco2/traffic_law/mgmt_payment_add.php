<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Id = CheckValue('Id', 'n');

$str_Fine = "";

$str_Post = "";
$str_Bank = "";
$str_Documentation = "";
$str_Tree = "";
$str_SitePost = "";


$PaymentTypeId = CheckValue('PaymentTypeId', 'n');
if($PaymentTypeId==0) $PaymentTypeId = 2;



$rs_Customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_Customer = mysqli_fetch_array($rs_Customer);
if($r_Customer['NationalBankMgmt'] && $r_Customer['ForeignBankMgmt']){
    $str_BankMgmt1 = " SELECTED ";
    $str_BankMgmt0 = "";
} else if(!$r_Customer['NationalBankMgmt'] && !$r_Customer['ForeignBankMgmt'] ){
    $str_BankMgmt0 = " SELECTED ";
    $str_BankMgmt1 = "";
} else{
    $str_BankMgmt0 = "";
    $str_BankMgmt1 = "";
}






if($PaymentTypeId==2){
    $str_Bank = " SELECTED ";

}
else{
    $str_Post = " SELECTED ";
}



$str_out .='
<div class="row-fluid">
    <form name="f_payment" id="f_payment" action="mgmt_payment_add_exe.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
        <div class="col-sm-7">
            <div class="col-sm-3 BoxRowLabel">
                Elaborazione controllo pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="PaymentProcedure" class="form-control">
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </div>
            
            <div class="col-sm-3 BoxRowLabel">
                Metodo pagamento
            </div>
            <div class="col-sm-5 BoxRowCaption">
                '.CreateSelect(MAIN_DB.".PaymentType","Disabled=0","Title","PaymentTypeId","Id","Title",$PaymentTypeId,false) .'
            </div>
    
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-2 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-10 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" type="text" name="Name" style="width:30rem">	
            </div>
    
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-2 BoxRowLabel">
                Rata
            </div>
            <div class="col-sm-7 BoxRowCaption">
                <div class="col-sm-2">
                    <input class="form-control frm_field_numeric" type="text" name="PaymentFee" value="0" style="width:5rem">
                </div>
                
                <div class="col-sm-10">
                    <select class="form-control" name="InstallmentList" id="InstallmentList" style="visibility:hidden">
                        <!--Le option vengono inserite dalla risors ajax "search_installments.php"-->
                    </select>
                </div>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo documento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="DocumentType" id="DocumentType" style="width:5rem">	
            </div>
    
            <div class="clean_row HSpace4"></div>                     			                
    
            <div class="col-sm-2 BoxRowLabel">
                Data Pagamento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date frm_field_required" type="text" name="PaymentDate" id="PaymentDate" style="width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Data accredito
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" name="CreditDate" id="CreditDate" style="width:10rem">	
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Quinto campo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="FifthField" id="FifthField" style="max-width:25rem">	
            </div>
                                            
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-2 BoxRowLabel">
                Importo pagato
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" type="text" name="Amount" id="Amount" style="width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo pagamento
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <select class="form-control" name="PaymentDocumentId" id="PaymentDocumentId" style="max-width:25rem">
                    <option value="0">Ridotto
                    <option value="1">Normale
                    <option value="2">Maggiorato
                </select>	
            </div>
            
            
            <div class="clean_row HSpace4"></div>
            
            
            <div class="col-sm-2 BoxRowLabel">
                Sanzione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="Fee" id="Fee" value="0.00" style="max-width:10rem">	
            </div>       
            <div class="col-sm-2 BoxRowLabel">
                Ricerca
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="ResearchFee" value="0.00" id="ResearchFee" style="max-width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Notifica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="NotificationFee" value="0.00" id="NotificationFee" style="max-width:10rem">	
            </div>                  
            <div class="col-sm-2 BoxRowLabel">
                Magg. semestrale
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="PercentualFee" value="0.00" id="PercentualFee" style="max-width:10rem">	
            </div>                  
            <div class="col-sm-2 BoxRowLabel">
                CAN
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" value="0.00" name="CanFee" id="CanFee" style="max-width:10rem">	
            </div> 
            <div class="col-sm-2 BoxRowLabel">
                CAD
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="CadFee" id="CadFee" value="0.00" style="max-width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Costi addizionali ente
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="CustomerFee" id="CustomerFee" value="0.00" style="max-width:10rem">	
            </div> 
    
            <div class="col-sm-2 BoxRowLabel">
                Notifica tribunale
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="OfficeNotificationFee" id="OfficeNotificationFee" value="0.00" style="max-width:10rem">	
            </div> 
            <div class="col-sm-4 BoxRowHTitle">
            </div>
                          
            <div class="clean_row HSpace4"></div>      
    
    
            <div class="col-sm-12" id="Div_CashPayment" style="display:none">
                <div class="col-sm-3 BoxRowLabel">
                    Somma versata
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input class="form-control frm_field_currency" type="text" name="CashPayed" id="CashPayed" style="width:10rem">	
                </div>    
                <div class="col-sm-3 BoxRowLabel">
                    Resto da dare
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input readonly class="form-control frm_field_currency" type="text" name="CashBack" id="CashBack" style="width:10rem">	
                </div>
            </div>
    
    
    
            <div class="col-sm-2 BoxRowLabel" style="height:11rem">
                Note
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height:11rem">
                <textarea class="form-control frm_field_string" name="Note"  style="margin-left:0;height:10rem"></textarea>	
            </div>
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                C/Terzi:
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select id="BankMgmt" name="BankMgmt" class="form-control">
                    <option value=""></option>
                 <option value="0" '.$str_BankMgmt0 .'>SI</option>
                 <option value="1"  '.$str_BankMgmt1 .'>NO</option>
                </select>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Assegnazione pagamento:
            </div>
            <div class="col-sm-5 BoxRowCaption">            
                <span id="span_name"></span>  
                <input type="hidden" value="" id="Search_FineId" name="Search_FineId">
                <input type="hidden" value="1" name="TableId">
                
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Non associato
            </div>
    
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" id="chk_NoFine">
            </div>
            
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-1 BoxRowLabel">
                Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_Protocol" id="Search_Protocol" type="text">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ref
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Code" id="Search_Code" type="text">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>                            
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" id="Search_Trespasser" type="text">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>                              
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" id="Search_Plate" type="text">
            </div>
            <div class="col-sm-1 BoxRowCaption" style="text-align: center">
                <button id="SearchFine" type="button" class="btn btn-primary" style="margin:0;width:100%;height:100%;padding:0;">
                    <i class="glyphicon glyphicon-search" style="font-size:1.6rem;"></i>
                </button>
            </div> 
            
            
            <div class="clean_row HSpace4"></div> 
    
            <div class="col-sm-12 BoxRow" style="height:40rem;">
                <div id="fine_content" class="col-sm-12" style="height:150px;overflow:auto"></div>
                <div id="payment_content" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>
            </div>
    
        </div>

        <div class="col-sm-5">
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem">
                Documentazione
            </div>
                                                          
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem">
                <i style="font-size: 1.5rem;color:#ff1a1f;cursor:pointer;display:none;" id="ClearDocument" class="fa fa-times-circle-o tooltip-r" data-placement="top" title="Svuota"></i>
                <input name="PaymentDocumentation" id="PaymentDocumentation" type="file" style="margin-bottom:10px; margin-top:10px;display:inline-block;" accept="image/x-png,image/gif,image/jpeg">
            </div>

            <div class="clean_row HSpace4"></div> 

            <div class="BoxRow" style="height:69.4rem;">
                <img class="iZoom" width="100%" id="DocumentPreview">
            </div>
        </div>

        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="update" value="Salva" disabled />
                    <img src="'.IMG.'/progress.gif" style="display: none;" id="Progress"/>
                    <button class="btn btn-default" id="back">Indietro</button>
                 </div>    
            </div>
        </div>    
    </form>
            
</div> 
';




echo $str_out;
?>
    <script type="text/javascript">

	function leggiImmagine(controllo) {
		if (controllo.files && controllo.files[0]) {
    		var lettore = new FileReader();
    
    		lettore.onload = function (e) {
    			$('#DocumentPreview').attr('src', e.target.result).fadeIn('slow');
    		};
    		lettore.readAsDataURL(controllo.files[0]);
		}
	}
    
        $('document').ready(function () {

        	$('#Amount, #PaymentDate, #PaymentDocumentId').change(function () {

                var FineId = $('#Amount').attr("fineid");
                var ProtocolYear = $('#Amount').attr("year")
                var PaymentDocumentId = parseInt($("#PaymentDocumentId").val());
                var PaymentDate = $("#PaymentDate").val();
                var Amount = parseFloat($('#Amount').val());
                
                if(Amount){
                    $.ajax({
                        url: 'ajax/ajx_ref_payment.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {FineId: FineId, Amount: Amount, ProtocolYear:ProtocolYear, PaymentDocumentId:PaymentDocumentId, PaymentDate:PaymentDate},
                        success: function (data) {
                            $("#Fee").val(data.Fee);
                            $("#ResearchFee").val(data.ResearchFee);
                            $("#NotificationFee").val(data.NotificationFee);
                            $("#PercentualFee").val(data.PercentualFee);
                            $("#CustomerFee").val(data.CustomerFee);
                            $("#CanFee").val(data.CanFee);
                            $("#CadFee").val(data.CadFee);
                            $("#OfficeNotificationFee").val("0.00");
                        },
                        error: function (data) {
                            console.log(data);
                            alert("error: " + data.responseText);
                        }
                    });
                }
            });

        	$("#PaymentDocumentation").change(function(){
        		leggiImmagine(this);
        		if ($(this).val()){
            		$('#ClearDocument').show();
        		} else {
        			$('#ClearDocument').hide();
        			$('#DocumentPreview').attr('src', '');
        		}
    		});

        	$('#ClearDocument').click(function(){
        		$("#PaymentDocumentation").val('');
        		$("#PaymentDocumentation").change();
        	});
    		
        	$('#DocumentPreview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

            $(document).on('fineadd', function(e, id, year){
            	$('#Amount').attr("fineid", id);
            	$('#Amount').attr("year", year);
            	$('#Amount').change();
            });
            
            $('#chk_NoFine').click(function () {

                if($("#chk_NoFine").prop('checked')){
                    $("#update").prop("disabled", false);
                } else {
                    $("#update").prop("disabled", true);
                }
            });



            $('#save').click(function() {

                $('#save').hide();
                $('#Progress').show();
                $('#f_payment').submit();
            });

            $('#f_payment').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });
            
            $('#SearchFine').click(function () {
                $('#SearchFine i').removeClass('glyphicon-search glyphicon').addClass('fas fa-circle-notch fa-spin');
                $(this).prop('disabled', true);

                var Search_Protocol = $('#Search_Protocol').val();
                var Search_Trespasser = $('#Search_Trespasser').val();
                var Search_Plate = $('#Search_Plate').val();
                var Search_Code = $('#Search_Code').val();
                var Amount= $('#Amount').val();


                $.ajax({
                    url: 'ajax/search_fine.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Amount:Amount, Search_Code: Search_Code, Search_Protocol: Search_Protocol, Search_Trespasser: Search_Trespasser, Search_Plate:Search_Plate},
                    success: function (data) {
                        $('#fine_content').show();
                        $('#fine_content').html(data.Trespasser);
                        $('#SearchFine i').removeClass('fas fa-circle-notch fa-spin').addClass('glyphicon-search glyphicon');
                        $('#SearchFine').prop('disabled', false);
                    },
                    error: function (data) {
                        console.log(data);
                        alert("error: " + data.responseText);
                    }
                });


            });
            $('#back').click(function () {
                window.location = "<?= $str_BackPage ?>";
                return false;
            });

            $('#PaymentTypeId').on('change', function() {
                var cash = ( this.value );
                $('#CashPayed').val('');
                $('#CashBack').val('');


                if(cash==4){
                    $('#Div_CashPayment').show();
                }else{
                    $('#Div_CashPayment').hide();
                }
            });

            $('#CashPayed').on('change', function() {
                var CashPayed   = ( this.value );
                var Amount      = $('#Amount').val();
                var CashBack = CashPayed - Amount;

                $('#CashBack').val(CashBack);

            });

            $('#f_payment').bootstrapValidator({
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


                    frm_field_date: {
                        selector: '.frm_field_date',
                        validators: {
                            date: {
                                format: 'DD/MM/YYYY',
                                message: 'Data non valida'
                            }
                        }
                    }

                }
            });
        });
    </script>
<?php
include(INC . "/footer.php");