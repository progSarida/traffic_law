<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
require_once(CLS . "/cls_view.php");
include(CLS . "/cls_message.php");

function mgmtInipecRequestWhere() {
    global $Search_UserName;
    global $Search_Customer;
    global $Search_FromSendDate;
    global $Search_ToSendDate;
    
    $str_Where = "rse.rse_tipo=4 AND (rse.rse_esito != 'N' OR rse.rse_esito IS NULL)";

    if ($Search_FromSendDate != "") {
        $str_Where .= " AND rse.rse_data_richiesta >='".DateInDB($Search_FromSendDate)."'";
    }
    if ($Search_ToSendDate != "") {
        $str_Where .= " AND rse.rse_data_richiesta<='".DateInDB($Search_ToSendDate)."'";
    }
    if ($Search_Customer != "") {
        $str_Where .= " AND rse.rse_ente='".$Search_Customer."'";
    }
    if ($Search_UserName != "") {
        $str_Where .= " AND rse.rse_utente_servizio='".$Search_UserName."'";
    }
    
    return $str_Where;
}

$total_pec_requests = 0;
$total_pec_income = 0;

$btn_search = CheckValue('btn_search', 'n');
$print = CheckValue('Print', 'n'); //Verifica se si è di ritorno da una stampa (vedi js)
$PageTitle = CheckValue('PageTitle','s') ?: '/';
    
    if($btn_search == 1 || $print == 1){
        $richieste = $rs->SelectQuery("
            SELECT 
            rse.rse_ente,
            rse.rse_id_richiesta,
            rse.rse_utente_servizio,
            rse.rse_data_richiesta, 
            count(*) as PecRichieste,
            count(der.der_risposta) as PecRicevute
            from richieste_servizi_esterni rse 
            join dettaglio_richieste_servizi_est der on rse.rse_codice =der.der_cod_richiesta 
            WHERE ".mgmtInipecRequestWhere()." group by rse.rse_codice");
    }
        $str_out.= '<div class="clean_row HSpace4"></div>
    <div class="progress" style="display:none;margin:0;">
	   <div id="progressbar" class="progress-bar progress-bar-striped progress-bar-success" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
    <div class="row-fluid">
      <div id="message"></div>
    </div>
    <div class="clean_row HSpace4"></div>
            
            
        <div class="col-sm-12" >
		<form id="f_search" action="mgmt_inipec_request.php" method="post">
			<input type="hidden" name="btn_search" value="1">
			<input type="hidden" name="PrintType" id="PrintType" value="1">
            <input type="hidden" name="PageTitle" value="'.$PageTitle.'">
			    <div class="col-sm-11">
    				<div class="col-sm-1 BoxRowLabel">
    				    Codice utente infocamere
    				</div>
    				<div class="col-sm-1 BoxRowCaption">
    				    <input class="form-control frm_field_string" id="Search_UserName" name="Search_UserName" type="text" value="' . $Search_UserName . '">
    				</div>
    				<div class="col-sm-1 BoxRowLabel">
    				    Ente
    				</div>
    				<div class="col-sm-3 BoxRowCaption">
    				    <select disabled class="form-control" name="Search_Customer" id="Search_Customer" type="text" value="' . $Search_Customer . '"></select>
    				</div>
    				        
    				<div class="col-sm-1 BoxRowLabel">
    				    Da data richiesta
    				</div>
    				<div class="col-sm-1 BoxRowCaption">
    				    <input class="form-control frm_field_date" type="text" value="' . $Search_FromSendDate . '" name="Search_FromSendDate">
    				</div>
    				<div class="col-sm-1 BoxRowLabel">
    				    A data richiesta
    				</div>
    				<div class="col-sm-1 BoxRowCaption">
    				    <input class="form-control frm_field_date" type="text" value="' . $Search_ToSendDate . '" name="Search_ToSendDate">
    				</div>
    				<div class="col-sm-2 BoxRowCaption"></div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-12 BoxRowLabel"></div>
			    </div>
			    <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
    	           <button type="button" id="search" name="search" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        	       <button type="button" data-printtype="Excel" id="printExcel" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
        	       <button type="button" data-printtype="Pdf" id="printPdf" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
    			</div>

				<div class="clean_row HSpace4"></div>
		</form>
    ';
        $i=0;
        if(!isset($richieste)){
            $str_out .='
            <div class="table_caption_H col-sm-12 text-center">
				Selezionare i criteri di ricerca.
			</div>';
        } else if(mysqli_num_rows($richieste) > 0) {
            $str_out .= '
    	<div class="row-fluid">
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-2">Codice utente infocamere</div>
        	    <div class="table_label_H col-sm-3">Ente</div>
                    <div class="table_label_H col-sm-2">Data</div>
                    <div class="table_label_H col-sm-1">IdRichiesta</div>
		    <div class="table_label_H col-sm-1">Numero richieste</div>
		    <div class="table_label_H col-sm-1">Pec ricevute</div>
	            <div class="table_label_H col-sm-2">Operazioni</div>
                <div class="clean_row HSpace4"></div>';
            
            
            while($request=mysqli_fetch_array($richieste)){
                $str_out.='<div class="table_caption_H col-sm-2">' . $request['rse_utente_servizio'] . '</div>
                   <div class="table_caption_H col-sm-3">' . $request['rse_ente'] . '</div>
                   <div class="table_caption_H col-sm-2">' . DateOutDB($request['rse_data_richiesta']) . '</div>
                   <div class="table_caption_H col-sm-1">' . $request['rse_id_richiesta'] . '</div>
                   <div class="table_caption_H col-sm-1">' . $request['PecRichieste'] . '</div>
                   <div class="table_caption_H col-sm-1">' .$request['PecRicevute'].'</div>
                   <div class="table_caption_H col-sm-2">
                   <form id="reload'.$i.'">
                   <input type="hidden" id="IdRichiesta'.$i.'" value="'.$request['rse_id_richiesta'].'">
                   <input type="hidden" id="UserName'.$i.'" value="'.$request['rse_utente_servizio'].'">
                        <a class="tooltip-r" title="Reimporta dati IniPec"  id="btn_reload'.$i.'" >
                            <i class="fa fa-retweet fa-fw" style="position:absolute; left:0.1rem;font-size:1.7rem;top:0.2rem;"></i>
                        </a>
                    </form>
                   </div>
                   <div class="clean_row HSpace4"></div>';
                $total_pec_requests += intval($request['PecRichieste']);
                $total_pec_income += intval($request['PecRicevute']);
                $i++;
            }
            $str_out .= '<div class="table_caption_H col-sm-8" style="background-color:#6397e2; text-align:center;">Richieste totali: <b>'.mysqli_num_rows($richieste).'</b></div>
                   <div class="table_caption_H col-sm-1" style="background-color:#6397e2; text-align:center;">Totale: '.$total_pec_requests.'</div>
                   <div class="table_caption_H col-sm-1" style="background-color:#6397e2; text-align:center;">Totale: '.$total_pec_income.'</div>
                   <div class="table_caption_H col-sm-2" style="background-color:#6397e2; text-align:center;">';
            $str_out.='</div>';
        } else {
            $str_out .='
            <div class="table_caption_H col-sm-12 text-center">
				Nessun record presente..
			</div>';
        }
        
        echo $str_out;
        ?>

<script type="text/javascript">
	$(document).ready(function () {
	
        $('#printExcel, #printPdf, #search').click(function () {
        	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
        		if($('#Search_Customer').val()){
	        		$('#f_search').attr('action', 'prn_inipec_request_exe.php');
        			$('#PrintType').val($(this).data('printtype'));
        			$('#f_search').submit();
        		} else {
                 	alert('Per poter stampare il prospetto è necessario specificare il campo "Ente".');
                 	return false;
        		}
        	} else $('#f_search').submit();
        	
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        });
    	  
	   
       $('#f_search').on('submit', function () {
        	$('#search, #printPdf, #printExcel').prop('disabled', true);
        });
	   
	   //Controlla la tendina in base al fatto che il codice utente infocamere sia compilato o meno
       $("#Search_UserName").change(function() {
           var UserName=$("#Search_UserName").val();
           if(UserName!=''){
               $.ajax({
                   url: 'ajax/ajx_loadCityByUserName.php',
                   type: 'POST',
                   dataType: 'json',
                   cache: false,
                   data: {
                       UserName:$("#Search_UserName").val()},
                   success: function (data) {
                   		console.log(data.Options);
                   		console.log(data.Options.length);
                   		if(data.Options.length > 0){
	                       	$('#Search_Customer'). html(data.Options);
                       		$("#Search_Customer") . prop('disabled', false);
                   		} else {
                   			$('#Search_Customer'). html('');
                   			$("#Search_Customer") . prop('disabled', true);
                   		}
                       //Controlla ed imposta la tendina dell'ente
                       var searchCustomer = '<?= $Search_Customer ?>';
                       if(searchCustomer != '')
                         $('#Search_Customer option[value='+searchCustomer+']').attr("selected",true);
                   }
               });
           } else{
               $("#Search_Customer") . prop('disabled', true);
               $('#Search_Customer'). html('');
           }

       }).change();
       
       let rowNumber =<?php echo $i ?>;
       for(let i=0;i<rowNumber;i++){
           $("#btn_reload"+i) . on('click', function (e){
               $("#btn_reload"+i) . prop('disabled', true);
               $('#btn_reload') . html('<i class="fa fa-circle-notch fa-spin" ></i>');
               $.ajax({
                   url: 'ajax/ajx_inipec_reload_response.php',
                   type: 'POST',
                   dataType: 'json',
                   cache: false,
                   data: {
                       UserName:$("#UserName"+i).val(),
                       Id:$("#IdRichiesta"+i).val()},
                   success: function (data) {
                       $('#btn_reload'+i) . html('<i class="fa fa-retweet fa-fw" style="position:absolute; left:0.1rem;font-size:1.7rem;top:0.2rem;"></i>');
                       $("#btn_reload"+i) . prop('disabled', false);
                       alert(data.message);
                   },
               		error: function (data) {
                       $('#btn_reload'+i) . html('<i class="fa fa-retweet fa-fw" style="position:absolute; left:0.1rem;font-size:1.7rem;top:0.2rem;"></i>');
                       $("#btn_reload"+i) . prop('disabled', false);
                        console.log(data);
                        alert("error: " + data.responseText);
                    }
               });
           });
       }
       
    });
    
</script>
<?php
include(INC . "/footer.php");
?>