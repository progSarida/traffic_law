<style>
.hidden {display:none !important;}
</style>
<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');	//Qui (menu_top) viene gestita la barra superiore dei titoli ed in una sua classe madre (title_top) la barra che indica il percorso

$AdvancedUser=$_SESSION['userlevel']<51?TRUE:FALSE;

$n_Year         = CheckValue('Search_Year','n');
$n_Month        = CheckValue('Search_Month','n');
$Search_FromSendDate   = CheckValue('Search_FromSendDate','s');
$Search_ToSendDate     = CheckValue('Search_ToSendDate','s');
$Search_Customer = CheckValue('Search_Customer','s');

$CityId="";
if($AdvancedUser){
    if(!empty($Search_Customer)){
        $CityId = $Search_Customer;
    }
}else{
    $CityId= $_SESSION['cityid'];
}

$mesi = array(
    '1' => 'Gennaio', '2' => 'Febbraio', '3' => 'Marzo',
    '4' => 'Aprile', '5' => 'Maggio', '6' => 'Giugno',
    '7' => 'Luglio', '8' => 'Agosto', '9' => 'Settembre',
    '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre',
);

$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12">
        <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                <div>
                I dati sono presentati in base alla data effettuazione della visura e non in base all\'anno di esercizio del verbale. Esempio: selezionando gennaio 2021 si hanno insieme le visure registrate per i primi atti del 2021 e per gli ultimi atti del 2020.
                </div>
            </div>
        </div>
    </div>
    <form id="f_search" action="prn_mctcrequest.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="PrintType" id="PrintType" value="1">
        <div class="col-sm-12" >
';
if($AdvancedUser){
    $str_out .= '
    <div class="col-sm-11">
        <div class="col-sm-1 BoxRowLabel">
            Ente:
        </div>
        <div class="col-sm-4 BoxRowCaption">
            '.CreateSelectExtended("Customer", "1=1", "CityId", "Search_Customer", "Search_Customer", "CityId", "ManagerName", $CityId, true, false).'
        </div>
        <div class="col-sm-7 BoxRowLabel"></div>
        <div class="clean_row HSpace4"></div>';
}
else
{
    $str_out .= '
                <div class="col-sm-11">
                    <input type="hidden" name="Search_Customer" value="'.$CityId.'">
    ';
}
$str_out .= '	
                <div class="col-sm-1 BoxRowLabel">
                    Anno:
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '.CreateArraySelect(range(2016, date('Y')), false, 'Search_Year', 'Search_Year', $Search_Year, false).'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Mese
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '.CreateArraySelect($mesi, true, "Search_Month", "Search_Month", $Search_Month, false).'
                </div>
                                
                <div class="col-sm-1 BoxRowLabel" style="text-align: right;">
                    Periodo:
                </div>
                <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                    Dal
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" name="Search_FromSendDate" id="Search_FromSendDate" type="text" value="'.$Search_FromSendDate.'">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                    Al
                </div>        
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" name="Search_ToSendDate" id="Search_ToSendDate" type="text" value="'.$Search_ToSendDate.'">
                </div>
                <div class="col-sm-3 BoxRowLabel">
                </div>     
            </div>
		    <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
	           <button type="submit" id="search" name="search" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
    	       <button type="submit" data-printtype="Excel" id="printExcel" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;padding:0;"><i class="fa fa-file-excel" style="font-size:2rem;"></i></button>
    	       <button type="submit" data-printtype="Pdf" id="printPdf" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;padding:0;float:left;"><i class="fa fa-file-pdf" style="font-size:2rem;"></i></button>
			</div>

        </div>';

if($n_Year==0 && $Search_FromSendDate==""){
    $str_out.= '
    <div class="table_caption_H col-sm-12">Scegliere anno + mese oppure indicare un intervallo di date</div>';
} else {
    if($n_Year>0){
        if($n_Month>0){
            if($n_Month<10) $n_Month = "0".$n_Month;
            $str_FromDate = str_pad($n_Year, 2, '0', STR_PAD_LEFT).'-'.str_pad($n_Month, 2, '0', STR_PAD_LEFT).'-01'; 
            $str_ToDate = ($n_Month==12) ? ($n_Year+1).'-01-01' :  $n_Year.'-'.($n_Month+1).'-01';
        }else{
            $str_FromDate = str_pad($n_Year, 2, '0', STR_PAD_LEFT).'-01-01'; 
            $str_ToDate = str_pad($n_Year, 2, '0', STR_PAD_LEFT).'-12-31';
        }
    } else {
        $str_FromDate   = DateInDB($Search_FromSendDate);
        if($Search_ToSendDate=='')
            $Search_ToSendDate=date("d/m/Y");
        $str_ToDate     = DateInDB($Search_ToSendDate);
    }
    
    $richiesteFTP = $rs->SelectQuery("
        SELECT 
        der.der_oggetto,
        rse.rse_data_richiesta
        from richieste_servizi_esterni rse 
        join dettaglio_richieste_servizi_est der on rse.rse_codice =der.der_cod_richiesta 
        join Customer c on c.CityId=rse.rse_ente
        WHERE (rse.rse_esito != 'N' OR rse.rse_esito IS NULL) 
        AND rse.rse_tipo=8 AND rse.rse_ente='".$CityId."' 
        AND rse.rse_data_richiesta <='".$str_ToDate."' AND rse.rse_data_richiesta >='".$str_FromDate."'");
    
    $richiesteWS = $rs->SelectQuery("
        SELECT
        der.der_oggetto,
        rse.rse_data_richiesta
        from richieste_servizi_esterni rse
        join dettaglio_richieste_servizi_est der on rse.rse_codice =der.der_cod_richiesta
        join Customer c on c.CityId=rse.rse_ente
        WHERE (rse.rse_esito != 'N' OR rse.rse_esito IS NULL) 
        AND rse.rse_tipo=13 AND rse.rse_ente='".$CityId."' 
        AND rse.rse_data_richiesta <='".$str_ToDate."' AND rse.rse_data_richiesta >='".$str_FromDate."'");

    $totRichiesteFTP = mysqli_num_rows($richiesteFTP);
    $totRichiesteWS = mysqli_num_rows($richiesteWS);
    
    //Stampa finestre con conteggio e pulsanti dettagli
    $totElem= $totRichiesteFTP + $totRichiesteWS;
    $str_out .= '
        <div>
            <div class="col-sm-12">
                <div class="table_label_H col-sm-12">N.ro richieste per il periodo selezionato: '.$totElem.'</div>
            </div>
            <div class="clean_row HSpace4"></div>
                ';
    
    $str_OutRequest= '
                
        <div class="col-sm-12">
            <div class="table_label_H col-sm-6">RICHIESTE INVIATE TRAMITE FTP: '.$totRichiesteFTP.'</div>
            <div class="table_label_H col-sm-6">RICHIESTE INVIATE IN MODALITA’ PUNTUALE: '.$totRichiesteWS.'</div>
        </div>
        <div class="clean_row HSpace4"></div>
        ';

    $str_OutBtn= '
    <div class="col-sm-12">
        <div class="table_label_H col-sm-6" style="text-align: center; height:5.6rem;">
            <button type="button" class="btn btn-primary btn-sm" id="btn_detsarida">Dettagli</button>
        </div>
        <div class="table_label_H col-sm-6" style="text-align: center; height:5.6rem;">
            <button type="button" class="btn btn-primary btn-sm" id="btn_detdirette">Dettagli</button>
        </div>
    </div>
</div>
    ';
    
    $str_out .= $str_OutRequest.$str_OutBtn;
    $str_out .='
<div> 
    ';
    
    //Stampa intestazione dettaglio sarida da scoprire con il relativo tasto
    $str_out_detsarida = '   
   <div id="detsarida" class="col-sm-6 hidden">
        <div class="justify-content-start">
            <div class="table_caption_H col-sm-3">Targa</div>
            <div class="table_caption_H col-sm-3">Data richiesta</div>
            <div class="clean_row HSpace4"></div>
        </div>
    ';
    
    //Stampa dettagli targa/data sarida e caricamento dati nel relativo array
    while($record = mysqli_fetch_array($richiesteFTP)){
        $str_out_detsarida .= '
        <div class="justify-content-start">
            <div class="BoxRowCaption col-sm-3">'.strtoupper($record['der_oggetto']).'</div>
            <div class="BoxRowCaption col-sm-3">'.DateOutDB($record['rse_data_richiesta']).'</div>
            <div class="clean_row HSpace4"></div>
        </div>
        ';
    }
    
    $str_out_detsarida .= '
    </div>';
    
    //Stampa intestazione dettaglio dirette da scoprire con il relativo tasto
    $str_out_detdirette = '
    <div id="detdirette" class="col-sm-6 hidden col-md-offset-6">
        <div class="ustify-content-start">
            <div class="table_caption_H col-sm-3">Targa</div>
            <div class="table_caption_H col-sm-3">Data richiesta</div>
            <div class="clean_row HSpace4"></div>
        </div>';
    
    //Stampa dettagli targa/data dirette e caricamento dati nel relativo array
    while($record = mysqli_fetch_array($richiesteWS)){
        $str_out_detdirette .= '
        <div class="justify-content-start">
            <div class="BoxRowCaption col-sm-3">'.strtoupper($record['der_oggetto']).'</div>
            <div class="BoxRowCaption col-sm-3">'.DateOutDB($record['rse_data_richiesta']).'</div>
            <div class="clean_row HSpace4"></div>
        </div>
        ';
    }
    
    $str_out_detdirette .= '
    </div>
   ';
    
    //Chiusura tag e stampa dettagli nascosti richieste sarida e dirette
    $str_out .='
    </form>
</div>'.$str_out_detsarida.$str_out_detdirette.'</div>';
}

echo $str_out;
?>
<script type="text/javascript">
    $(document).ready(function () {
        
    $('#printExcel, #printPdf, #search').click(function () {
    	if ($(this).attr("id") == 'printExcel' || $(this).attr("id") == 'printPdf'){
	    	if($('#Search_Year').val() || $('#Search_FromSendDate').val() || $('#Search_ToSendDate').val()){
	    		$('#f_search').attr('action', 'prn_mctcrequest_exe.php');
				$('#PrintType').val($(this).data('printtype'));
				$('#f_search').submit();
			} else {
				alert('Scegliere anno + mese oppure indicare un intervallo di date');
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

    $('#btn_detsarida').click(function() {
        $('#detsarida').toggleClass('hidden');
        $('#detdirette').toggleClass('col-md-offset-6');
    });
    
    $('#btn_detdirette').click(function() {
        $('#detdirette').toggleClass('hidden');
    });
 
    $('#Search_Year, #Search_Month').change(function(){
        var selectedText = $("#Search_Year option:selected").html()+$("#Search_Month option:selected").html();
        if(selectedText.length > 0){
            $('#Search_Month, #Search_Year').prop('disabled', false);   //Abilito la select dei mesi
            $("#Search_FromSendDate" ).prop( "disabled", true); //disabilito la data libere Dal
            $("#Search_FromSendDate").val('');                  //Cancello il contenuto di Dal
            $("#Search_ToSendDate").prop("disabled", true);     //disabilito la data libere Al
            $("#Search_ToSendDate").val('');                    //Cancello il contenuto di Al
        }
        else{
            $("#Search_Month").val($("#Search_Month option:first").val());  //Seleziono il mese vuoto
            $("#Search_Year").val($("#Search_Year option:first").val());  //Seleziono anno vuoto
            $("#Search_FromSendDate").prop("disabled", false);  //abilito la data libere Dal
            $("#Search_ToSendDate").prop("disabled", false);    //abilito la data libere Al
        }
    });

    $('#Search_FromSendDate, #Search_ToSendDate').on('input change',function(e){
        var selectedText = $("#Search_FromSendDate").val()+$("#Search_ToSendDate").val();
        if(selectedText.length > 0){
            $("#Search_ToSendDate, #Search_FromSendDate").prop("disabled", false);    //abilito la data libere Al
            $("#Search_Year").val($("#Search_Year option:first").val());    //Seleziono l'anno vuoto
            $("#Search_Month").val($("#Search_Month option:first").val());  //Seleziono il mese vuoto
            $('#Search_Year').prop('disabled', true);     //disabilito Anno
            $('#Search_Month').prop('disabled', true);    //disabilito Mese
        }
        else{
            $("#Search_ToSendDate, #Search_FromSendDate").val('');                       //Cancello il contenuto di Al 
            $('#Search_Year').prop('disabled', false);       //Abilito l'anno
            $('#Search_Month').prop('disabled', false);      //Abilito il mese
 
        }
    });
    
    $('#Search_Year').change();
    $('#Search_FromSendDate').change();
});
</script>
<?php
include(INC . "/footer.php");
