<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PaymentId= CheckValue('PaymentId','n');

$n_PaymentProcedure = 1;
$FineId = 0;

$a_PaymentDocumentId = array("","","","");
$a_PaymentTypeId = array("","","","");
$a_BankMgmt = array("","");
$a_PaymentProcedureId = array("","");
$a_Payments = array();

$Key = $PreviousId = $NextId = null;
$str_Folder = $Documentation = $Document = '';

$rs_Payment = $rs->Select('V_FinePayment', "PaymentId=" . $PaymentId);
if(mysqli_num_rows($rs_Payment)>0){
    $r_Payment = mysqli_fetch_array($rs_Payment);
    $FineId = $r_Payment['FineId'];
    
    $rs_Payments = $rs->Select('V_FinePayment', "FineId=".$FineId);
    while ($r_Payments = mysqli_fetch_assoc($rs_Payments)){
        $a_Payments[] = $r_Payments['PaymentId'];
    }
    
    $Key = array_search($PaymentId, $a_Payments);
    $PreviousId = array_key_exists(($Key-1),$a_Payments) ? $a_Payments[$Key-1] : null;
    $NextId = array_key_exists(($Key+1),$a_Payments) ? $a_Payments[$Key+1] : null;
    
    $rs_Row = $rs->SelectQuery("
        SELECT  
        FA.Fee,
        FA.MaxFee,
        
        ArT.ReducedPayment,
        
        FH.NotificationTypeId,
        FH.NotificationFee,
        FH.ResearchFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=".$FineId." AND NotificationTypeId=6");


    $r_Row = mysqli_fetch_array($rs_Row);
    
    $AdditionalFee = $r_Row['NotificationFee'] +	$r_Row['ResearchFee'];
    $Fee = NumberDisplay($r_Row['Fee'] + $AdditionalFee);
    $MaxFee = NumberDisplay($r_Row['MaxFee'] + $AdditionalFee);

    $ReducedFee = "";
    if($r_Row['ReducedPayment']){
        $ReducedFee = ($r_Row['Fee']*FINE_PARTIAL)+$AdditionalFee;
        $ReducedFee = NumberDisplay($ReducedFee);
    }

    $SendDate = DateOutDB($r_Row['SendDate']);
    $DeliveryDate = DateOutDB($r_Row['DeliveryDate']);


    $TrespasserName = $r_Payment['CompanyName']." ".$r_Payment['Surname']." ".$r_Payment['Name'];
    $TrespasserAddress = $r_Payment['Address'] . " - " . $r_Payment['ZIP'] . " " . $r_Payment['City'] . " (" . $r_Payment['Province'] . ") " . $r_Payment['CountryTitle'];

    $n_PaymentProcedure = ChkPaymentProcedure($FineId, $rs);
    
    $str_Folder = ($r_Payment['FineCountryId'] == 'Z000') ? NATIONAL_FINE_HTML : FOREIGN_FINE_HTML;
    
    //TODO Tenere monitorato
    if($r_Payment['Documentation']!=null)	//In caso il nome dell'immagine fosse presente all'interno della riga, alla colonna "Documentation"
    {
        $Document = $r_Payment['Documentation'];
        $Documentation = $str_Folder.'/'.$r_Payment['CityId'].'/'.$FineId.'/'.$Document;
    } else {
        $rs_Document = $rs->Select("FineDocumentation", "DocumentationTypeId=15 AND Note='".$PaymentId."'");
        $r_Document = mysqli_fetch_array($rs_Document);
        
        if($r_Document['Documentation'] != ""){
            $Document = $r_Document['Documentation'];
            $Documentation = $str_Folder.'/'.$r_Payment['CityId'].'/'.$FineId.'/'.$Document;
        }
    }
    
    $PaymentFee = $r_Payment['PaymentFee'];
    $InstallmentId = $r_Payment['InstallmentId'];

} else {
    $rs_Payment = $rs->Select('V_FinePaymentAll', "PaymentId=" . $PaymentId);
    $r_Payment = mysqli_fetch_array($rs_Payment);

    $AdditionalFee = "";
    $Fee = "";
    $MaxFee = "";
    $ReducedFee = "";

    $SendDate = "";
    $DeliveryDate = "";


    $TrespasserName = "";
    $TrespasserAddress = "";

    $str_Folder = PAYMENT_RECLAIM_HTML;
    
    if($r_Payment['Documentation'] != ""){
        $Document = $r_Payment['Documentation'];
        $Documentation = $str_Folder.'/'.$_SESSION['cityid'].'/'.$Document;
    }

}
if($Documentation != ''){
    $MimeType = @mime_content_type($Documentation) ?: '';
    
    if($MimeType == "image/jpeg" || $MimeType == "image/png"){
        $str_Document = '<img class="iZoom" src="'.$Documentation.'" width="100%" id="DocumentPreview">';
    } else {
        $str_Document = '<iframe style="width:100%; height:100%;background:white;" src="'.$Documentation.'"></iframe>';
    }
} else {
    $str_Document = '<i class="fas fa-file-excel" style="position: absolute;left: 35%;top: 50%;font-size: 25rem;line-height: inherit;opacity: 0.2;"></i>';
}

    $a_PaymentProcedureId[$n_PaymentProcedure]  = " SELECTED ";
    $a_PaymentDocumentId[$r_Payment['PaymentDocumentId']] =" SELECTED ";
    $a_PaymentTypeId[$r_Payment['PaymentTypeId']] =" SELECTED ";



    $a_BankMgmt[$r_Payment['BankMgmt']] =" SELECTED ";
    $str_out .= '
<div class="row-fluid">
    <form name="f_payment" id="f_payment" method="post" action="mgmt_payment_upd_exe.php">
    <input type="hidden" name="Id" value="'.$PaymentId.'">  
    <input type="hidden" name="FineId" value="'. $FineId .'">
    <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
        <div class="col-sm-7">
            <div class="col-sm-1 text-center BoxRowLabel" style="background-color: #294A9C;">
                '.(!empty($PreviousId)
                    ? '<a href='.impostaParametriUrl(array('PaymentId' => $PreviousId,)).'><i data-container="body" data-toggle="tooltip" data-placement="top" title="Indietro" class="tooltip-r glyphicon glyphicon-arrow-left" style="color:white;line-height:2rem;"></i></a>' 
                    : '').'
            </div>
            <div class="col-sm-10 text-center BoxRowLabel" style="background-color: #294A9C;">
                PAGAMENTO
            </div>
            <div class="col-sm-1 text-center BoxRowLabel" style="background-color: #294A9C;">
                '.(!empty($NextId)
                    ? '<a href='.impostaParametriUrl(array('PaymentId' => $NextId,)).'><i data-container="body" data-toggle="tooltip" data-placement="top" title="Avanti" class="tooltip-r glyphicon glyphicon-arrow-right" style="color:white;line-height:2rem;"></i></a>' 
                    : '').'
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel" >
                Protocollo
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '.($r_Payment['ProtocolId'] != '' ? $r_Payment['ProtocolId'].'/'.$r_Payment['ProtocolYear'] : '').'
            </div>
            <div class="col-sm-2 BoxRowLabel" >
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '.$r_Payment['Code'].'
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Elaborazione controllo pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="PaymentProcedure" class="form-control">
                    <option value="1"' .$a_PaymentProcedureId[1]. '>Si</option>
                    <option value="0"' .$a_PaymentProcedureId[0]. '>No</option>
                </select>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel" >
                Targa
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '.$r_Payment['VehiclePlate'].'
            </div>            
            <div class="col-sm-2 BoxRowLabel" >
            Data Infrazione
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '.DateOutDB($r_Payment['FineDate']).'
            </div>
            <div class="col-sm-2 BoxRowLabel" >
            Ora Infrazione
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '.$r_Payment['FineTime'].'
            </div>
   
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel" >
                Nominativo
            </div>
            <div class="col-sm-4 BoxRowCaption" >
            '. $TrespasserName .'
            </div>
            <div class="col-sm-2 BoxRowLabel" >
                Indirizzo
            </div>
            <div class="col-sm-4 BoxRowCaption" >
            '. $TrespasserAddress .'
            </div> 	 

            <div class="clean_row HSpace4"></div>  
                 
            <div class="col-sm-2 BoxRowLabel" >
                Ridotto
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '.$ReducedFee.'
            </div>
            <div class="col-sm-2 BoxRowLabel" >
                Minima
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '. $Fee .'
            </div>
            <div class="col-sm-2 BoxRowLabel" >
                Massima
            </div>
            <div class="col-sm-2 BoxRowCaption" >
            '. $MaxFee .'
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel" >
                Rata
            </div>
            <div class="col-sm-10 BoxRowCaption" >
                <div class="col-sm-2">
                    <input class="form-control frm_field_numeric" type="text" name="PaymentFee" value="'.$PaymentFee.'" style="width:5rem">
                </div>
                
                <div class="col-sm-10">
                    <select class="form-control" name="InstallmentList" id="InstallmentList" style="visibility:hidden">
                        <option value="0">
                    </select>
                </div>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel" >
            Verbale spedito
            </div>
            <div class="col-sm-3 BoxRowCaption" >
            '. $SendDate .'
            </div>
            <div class="col-sm-3 BoxRowLabel" >
            Verbale ricevuto
            </div>
            <div class="col-sm-3 BoxRowCaption" >
            '. $DeliveryDate .'
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Metodo pagamento
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.CreateSelect("sarida.PaymentType","Disabled=0","Title","PaymentTypeId","Id","Title",$r_Payment['PaymentTypeId'],false) .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo pagamento
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <select class="form-control" name="PaymentDocumentId" id="PaymentDocumentId">
                    <option value="0"'.$a_PaymentDocumentId[0].'>Ridotto
                    <option value="1"'.$a_PaymentDocumentId[1].'>Normale
                    <option value="2"'.$a_PaymentDocumentId[2].'>Maggiorato
                </select>	
            </div>
            <div class="col-sm-1 BoxRowLabel">
                C/TERZI
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="BankMgmt">
                    <option value="0"'.$a_BankMgmt[0].'>SI
                    <option value="1"'.$a_BankMgmt[1].'>NO
                </select>	
            </div>                
            
            <div class="clean_row HSpace4"></div> 
         
            <div class="col-sm-2 BoxRowLabel">
                Importo pagato
                <i class="fa fa-refresh" name="'.$r_Payment['FineId'].'_'.$r_Payment['ProtocolYear'].'" style="margin-right: 1rem;line-height: 2rem;float: right;"></i>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" type="text" name="Amount" id="Amount" style="max-width:10rem" value="' . $r_Payment['Amount'] . '">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Data Pagamento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" name="PaymentDate" id="PaymentDate" value="' . DateOutDB($r_Payment['PaymentDate']) . '" style="max-width:10rem">	 
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Data accredito
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" name="CreditDate" id="CreditDate" value="' . DateOutDB($r_Payment['CreditDate']) . '" style="max-width:10rem">	 
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Sanzione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="Fee" id="Fee" value="' . $r_Payment['Fee'] . '" style="max-width:10rem">	
            </div>       
            <div class="col-sm-2 BoxRowLabel">
                Ricerca
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="ResearchFee" value="' . $r_Payment['ResearchFee'] . '" id="ResearchFee" style="max-width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Notifica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="NotificationFee" value="' . $r_Payment['NotificationFee'] . '" id="NotificationFee" style="max-width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Magg. semestrale
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="PercentualFee" value="' . $r_Payment['PercentualFee'] . '" id="PercentualFee" style="max-width:10rem">	
            </div>                  
            <div class="col-sm-2 BoxRowLabel">
                CAN
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="CanFee" id="CanFee" value="' . $r_Payment['CanFee'] . '" style="max-width:10rem">	
            </div> 
            <div class="col-sm-2 BoxRowLabel">
                CAD
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="CadFee" id="CadFee" value="' . $r_Payment['CadFee'] . '" style="max-width:10rem">	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Costi addizionali ente
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="CustomerFee" id="CustomerFee" value="' . $r_Payment['CustomerFee'] . '" style="max-width:10rem">	
            </div> 
            <div class="col-sm-2 BoxRowLabel">
                Notifica tribunale
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required chk_sum" type="text" name="OfficeNotificationFee" id="OfficeNotificationFee" value="' . $r_Payment['OfficeNotificationFee'] . '" style="max-width:10rem">	
            </div>
            <div class="col-sm-4 BoxRowHTitle">
            </div>            
        
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Nominativo 
            </div>
            <div class="col-sm-10 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Name" style="width:30rem" value="' . $r_Payment['PaymentName'] . '">	
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Tipo documento 
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control" type="text" name="DocumentType" id="DocumentType" style="max-width:5rem" value="' . $r_Payment['DocumentType'] . '">	
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Quinto campo 
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="FifthField" id="FifthField" style="max-width:25rem" value="' . $r_Payment['FifthField'] . '">
            </div>					
        
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel" style="height:12rem">
                Note
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height:12rem">
               <textarea class="form-control frm_field_string" name="Note"  style="margin-left:0;height:10rem">' . $r_Payment['Note'] . '</textarea> 			
            </div>
        </div>

        <div class="col-sm-5">
            <div class="col-sm-4 BoxRowLabel">
                Documentazione
            </div>
                                                          
            <div class="col-sm-8 BoxRowCaption">
                '.$Document.'
            </div>

            <div class="clean_row HSpace4"></div> 

            <div class="BoxRow" style="height:39.2rem;">
                '.$str_Document.'
            </div>
        </div>

        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                   <input type="submit" class="btn btn-default" id="update" value="Modifica"  />
                   <button class="btn btn-default" id="back">Indietro</button>
                </div>   
            </div>
        </div>              	
         
        </div>
  	</form>
    
</div>';


echo $str_out;
?>
    <script type="text/javascript">
        $('document').ready(function(){
			var fineId = '<?= $FineId?>';
			var selectedInstallment = '<?= $InstallmentId?>';
			
            $('#back').click(function(){
                window.location= "<?= $str_BackPage ?>";
                return false;
            });


            $(".fa-refresh").hover(function(){
                $(this).css("color","#2684b1");
                $(this).css("cursor","pointer");
            },function(){
                $(this).css("color","#fff");
                $(this).css("cursor","");
            });

            $('.fa-refresh').click(function () {

                var a_Name = $(this).attr("name").split("_");
                var FineId = a_Name[0];
                var ProtocolYear = a_Name[1];
                var PaymentDocumentId =  $("#PaymentDocumentId").val();
				var PaymentDate = $("#PaymentDate").val();
                var Amount = $('#Amount').val();
                
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
                    }
                });

            });

            $('#DocumentPreview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

			$.ajax({
        		url: 'ajax/search_installments.php',
        		type: 'GET',
        		dataType: 'json',
        		cache: false,
        		data: {Search_FineId: fineId},
        		success: function (data) {
                    var options = "<option value='0'>";
        			for(var i = 0; i < data.length; i++){
                        var installmentId = data[i].InstallmentId;
                        var status = data[i].Status;
                        var regDate = data[i].RegDate;
                        var installmentType = data[i].InstallmentType;
                        var rateName = data[i].RateName;
                        var position = data[i].Position;
                        
                        options += '<option value="'+installmentId+'" id="InstallmentType_'+installmentId+'">'+status+" - "+regDate+"  "+installmentType+"  "+rateName+"  "+position
                        }
                    $('#InstallmentList').html(options);
                    if(data.length > 0)
                        $('#InstallmentList').css("visibility","visible");
                    else
                        $('#InstallmentList').css("visibility","hidden");
                        
                    if(selectedInstallment != '')
						$('#InstallmentType_'+selectedInstallment).attr('selected','selected');
        		},
                error: function(ts) { console.log(ts.responseText) }
        	});
			
        });

    </script>
<?php



include(INC."/footer.php");
