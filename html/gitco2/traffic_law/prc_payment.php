<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/header.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//per tentare di risolvere l'errore del massimo tempo di 60 secondi superato
ini_set('max_execution_time', 3000);
//per tentare di risolvere l'errore del massimo spazio di 512 MB superato
//per enti grandi come Savona siamo passati a 2GB
ini_set('memory_limit', '2048M');

$PageTitle = CheckValue('PageTitle','s');
$CurrentDate = date("d/m/Y");

$Search_FromFineDate = CheckValue("Search_FromFineDate", "s");
$Search_ToFineDate = CheckValue("Search_ToFineDate", "s");
$Search_FromNotificationDate = CheckValue("Search_FromNotificationDate", "s");
$Search_ToNotificationDate = CheckValue("Search_ToNotificationDate", "s");
$Search_FromProtocolId = CheckValue("Search_FromProtocolId", "s");
$Search_ToProtocolId = CheckValue("Search_ToProtocolId", "s");
$PrintOrderBy = CheckValue("PrintOrderBy", "s") != "" ? CheckValue("PrintOrderBy", "s") : "ProtocolId";
$ElaborationType = CheckValue('ElaborationType','s') != "" ? CheckValue("ElaborationType", "s") : "any";
$PrintDate = CheckValue("PrintDate", "s") != "" ? CheckValue("PrintDate", "s") : $CurrentDate;
$ProcessingDate = CheckValue('ProcessingDate','s') != "" ? CheckValue("ProcessingDate", "s") : $CurrentDate;

//Controlla la data, se è il 29/02, controlla se l'anno è bisestile. In caso negativa la trasforma in 28/02
aggiustaBisestile($Search_FromFineDate);
aggiustaBisestile($Search_ToFineDate);
aggiustaBisestile($Search_FromNotificationDate);
aggiustaBisestile($Search_ToNotificationDate);

$btn_search = CheckValue('btn_search','n');

$rs_ProcessingPaymentsMade = $rs->SelectQuery("SELECT * FROM ProcessingPaymentsMade WHERE CityId='".$_SESSION['cityid']."' order by Id desc");
$n_ProcessingPaymentsMade = mysqli_num_rows($rs_ProcessingPaymentsMade);

$str_out .='
<div class="row-fluid">
    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota bene:
                    <ul style="list-style-position: inside;">
                        <li>
                            Le operazioni in questa pagina sono fare solo per il primo sollecito di un verbale, in quanto il calcolo degli importi non tiene conto di eventuali solleciti pregressi.
                        </li>
                        <li>
                            Per la creazione di solleciti successivi al primo, andare in "Operazioni solleciti definitivi" impostando "Crea solleciti".
                        </li>
                    </ul>
            	</li>
                <li>
                    Legenda degli stati di elaborazione definitiva.
                    <ul style="list-style-position: inside;">
                        <li>
                            <b>Omessi:</b> Verbali senza alcun pagamento. Il sollecito viene creato.
                        </li>
                        <li>
                            <b>Parziali:</b> Verbali con almeno un pagamento la cui somma non colma l\'intero dovuto. E\' composto anche di tardivi, cioè di pagamenti completi effettuati dopo lo scadere del relativo scaglione. Il sollecito viene creato.
                        </li>
                        <li>
                            <b>Sospesi:</b> Verbali attivi che sono in attesa di un iter che li sblocchi (Es.: ricorso in corso). Vengono saltati dall\'elaborazione.
                        </li>
                        <li>
                            <b>Chiusi:</b> Verbali i cui pagamenti coprono per intero somma dovuta. Vengono contrassegnati come pagati ed escono dal giro dei solleciti.
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <form id="f_Search" action="prc_payment.php" method="post">
    <input type="hidden" name="btn_search" value="1">
    <input type="hidden" name="PageTitle" value="'.$PageTitle.'">

        <div class="col-sm-11" style="height:6.9rem;">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>								
                </select>
            </div>  
            
            <div class="col-sm-2 BoxRowLabel">
                Da data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
			    Da cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_FromProtocolId. '" id="Search_FromProtocolId" name="Search_FromProtocolId" style="width:8rem">
			</div>
            <div class="col-sm-1 BoxRowLabel">		
			    A cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_ToProtocolId. '" id="Search_ToProtocolId" name="Search_ToProtocolId" style="width:8rem">
		    </div>       
                                      
            <div class="clean_row HSpace4"></div>                 
            
            <div class="col-sm-1 BoxRowLabel">Numero record</div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(5,10,20,50,100,200,500,800), false, 'RecordLimit', 'RecordLimit', $RecordLimit, true).'
            </div>

            <div class="col-sm-2 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromNotificationDate.'" name="Search_FromNotificationDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToNotificationDate.'" name="Search_ToNotificationDate">
            </div>
            <div class="col-sm-4 BoxRowCaption"></div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Ricorsi
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_HasDispute" value="0" style="top:0;"'.($Search_HasDispute == 0 ? ' checked' : '').'><span style="position:relative;top:-0.3rem"> Escludi</span>
                <input type="radio" name="Search_HasDispute" value="1" style="top:0;"'.($Search_HasDispute == 1 ? ' checked' : '').'><span style="position:relative;top:-0.3rem"> Includi</span>
                <input type="radio" name="Search_HasDispute" value="2" style="top:0;"'.($Search_HasDispute == 2 ? ' checked' : '').'><span style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Rateizzazioni
            </div>
            <div class="col-sm-2 BoxRowCaption" id="PaymentRateRadio">
                <input type="radio" id="PaymentRateRadio0" name="Search_HasPaymentRate" value="0" style="top:0;"'.($Search_HasPaymentRate == 0 ? ' checked' : '').'><span style="position:relative;top:-0.3rem"> Escludi</span>
                <input type="radio" id="PaymentRateRadio1" name="Search_HasPaymentRate" value="1" style="top:0;"'.($Search_HasPaymentRate == 1 ? ' checked' : '').'><span style="position:relative;top:-0.3rem"> Includi</span>
                <input type="radio" id="PaymentRateRadio2" name="Search_HasPaymentRate" value="2" style="top:0;"'.($Search_HasPaymentRate == 2 ? ' checked' : '').'><span style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
            <div class="col-sm-6 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:6.9rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;"><i class="glyphicon glyphicon-search" style="font-size:2.5rem;"></i></button>
        </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';
if ($n_ProcessingPaymentsMade>0) {
    $str_out_ElaborationsMade='
    <div id="collapse-header" class="col-sm-12">
        <div class="col-sm-12 BoxRowLabel">
            Elaborazioni precedenti <i class="fas fa-angle-down caret-toggle" id="heading" data-toggle="collapse" data-target="#collapse" aria-expanded="false" aria-controls="collapse"></i>
        </div>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 collapse" id="collapse" aria-labelledby="heading" data-parent="#accordion">
       <div class="col-sm-12">
           <div class="col-sm-1 BoxRowLabel" style="text-align:center">
    			Tipologia targhe 
    	   </div>
           <div class="col-sm-2 BoxRowLabel" style="text-align:center">
    			Data 
    	   </div>
           <div class="col-sm-1 BoxRowLabel" style="text-align:center">
    			Ora inizio 
    	   </div>
           <div class="col-sm-1 BoxRowLabel" style="text-align:center">
    			Ora fine 
    	   </div>
           <div class="col-sm-7 BoxRowLabel" style="text-align:center">
    			File report
    	   </div>
       </div> 
       <div class="clean_row HSpace4"></div>';
    $i = $n_ProcessingPaymentsMade;
    while($i>0){
        $r_ProcessingPaymentsMade = mysqli_fetch_array($rs_ProcessingPaymentsMade);
        $str_out_ElaborationsMade .= 
        '<div class="col-sm-12">
               <div class="col-sm-1 BoxRowCaption" style="text-align:center">
        			'.(($r_ProcessingPaymentsMade['NationalityId'] == 1) ? "Nazionali" : "Estere").'
        	   </div>
               <div class="col-sm-2 BoxRowCaption" style="text-align:center">
        			'.DateOutDB($r_ProcessingPaymentsMade['ProcessingDate']).'
        	   </div>
               <div class="col-sm-1 BoxRowCaption" style="text-align:center">
        			'.$r_ProcessingPaymentsMade['ProcessingStartTime'].'
        	   </div>
               <div class="col-sm-1 BoxRowCaption" style="text-align:center">
        			'.$r_ProcessingPaymentsMade['ProcessingEndTime'].'
        	   </div>
               <div class="col-sm-7 table_caption_H" style="text-align:center">
        			<a target="_blank" href="'.$MainPath.'/doc/print/payment/'.$r_ProcessingPaymentsMade['ReportDocumentName'].'" >'.$r_ProcessingPaymentsMade['ReportDocumentName'].'</a>
        	   </div>
        </div>';
   
    $i--;
    }
    
    $str_out_ElaborationsMade .= 
    '   </div>
     </div>
     <div class="clean_row HSpace4"></div>';
    $str_out .= $str_out_ElaborationsMade;
}
$str_out .='        
    	<div class="row-fluid">
            <form name="f_print" id="f_print" action="prc_payment_exe.php'.$str_GET_Back_Page.'" method="post">
                <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
                <input type="hidden" name="Filter_TypePlate" value="'.$s_TypePlate.'">
                <input type="hidden" name="Filter_FromFineDate" value="'.$Search_FromFineDate.'">
                <input type="hidden" name="Filter_ToFineDate" value="'.$Search_ToFineDate.'">
                <input type="hidden" name="Filter_FromNotificationDate" value="'.$Search_FromNotificationDate.'">
                <input type="hidden" name="Filter_ToNotificationDate" value="'.$Search_ToNotificationDate.'">
                <input type="hidden" name="Filter_FromProtocolId" value="'.$Search_FromProtocolId.'">
                <input type="hidden" name="Filter_ToProtocolId" value="'.$Search_ToProtocolId.'">
                <input type="hidden" name="Filter_HasDispute" value="'.$Search_HasDispute.'">
                <input type="hidden" name="Filter_HasPaymentRate" value="'.$Search_HasPaymentRate.'">
            	<div class="col-sm-12">
                    <div class="table_label_H col-sm-1">Riga</div>
    				<div class="table_label_H col-sm-1">Cronologico</div>
    				<div class="table_label_H col-sm-2">Rif.to</div>
                    <div class="table_label_H col-sm-2">Data notifica</div>
    				<div class="table_label_H col-sm-2">Data</div>
    				<div class="table_label_H col-sm-2">Ora</div>
    				<div class="table_label_H col-sm-2">Targa</div>
    				<!--<div class="table_label_H col-sm-4">Sanzione</div>-->
    
    				<div class="clean_row HSpace4"></div>';

    if($s_TypePlate=="") {
        $str_out.=
            '<div class="table_caption_H col-sm-12">
			Scegliere nazionalità
		    </div>';
    } else {

        $str_where = "1=1";
        //Controlla per primo il paese del trasgressore, poi quello del verbale
        $str_WhereCountry = ($s_TypePlate=="N") ? " AND COALESCE(T.CountryId, F.CountryId) = 'Z000'" : " AND COALESCE(T.CountryId,F.CountryId) != 'Z000'";
        
        if ($Search_FromFineDate != "") {
            $str_where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
        }
        if ($Search_ToFineDate != "") {
            $str_where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
        }
        if ($Search_FromNotificationDate != "") {
            $str_where .= " AND FN.NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
        }
        if ($Search_ToNotificationDate != "") {
            $str_where .= " AND FN.NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";
        }
        if ($Search_FromProtocolId != "") {
            $str_where .= " AND F.ProtocolId>=".$Search_FromProtocolId;
        }
        if ($Search_ToProtocolId != "") {
            $str_where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
        }
        if ($Search_HasPaymentRate == 0) {
            $str_where .= " AND PR.StatusRateId IS NULL";
        } else if($Search_HasPaymentRate == 2){
            $str_where .= " AND PR.StatusRateId IS NOT NULL";
        }
        if ($Search_HasDispute == 0) {
            $str_where .= " AND F.Id NOT IN(SELECT FineId FROM V_FineDispute)";
        } else if($Search_HasDispute == 2){
            $str_where .= " AND F.Id IN(SELECT FineId FROM V_FineDispute)";
        }
        
        //Per escludere gli inviti in AG
        $str_where .= " AND (F.KindSendDate IS NULL OR (F.KindSendDate IS NOT NULL AND F.Id IN(SELECT FineId FROM FineHistory WHERE NotificationTypeId = 30))) ";
        
        $str_where .= " AND F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year'];

        $strOrder = "F.ProtocolId";
        
        $cls_view = new CLS_VIEW(PRC_PAYMENT);
        $query = $cls_view->generateSelect($str_where.$str_WhereCountry, null, $strOrder, $RecordLimit);
        //$rs_FineProcedure = $rs->Select('V_PaymentProcedure',$str_where.$str_WhereCountry, $strOrder);
        $rs_FineProcedure = $rs->SelectQuery($query);
        $RowNumber = mysqli_num_rows($rs_FineProcedure);

        if ($RowNumber == 0) {
            $str_out.=
                '<div class="table_caption_H col-sm-12">
			        Nessun record presente
		        </div>';
        } else {
            $n_Row = 1;
            while ($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)) {
                $str_out.= '<div class="tableRow">';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $n_Row ++ .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['ProtocolId'] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2">' . $r_FineProcedure['Code'] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2">' . DateOutDB($r_FineProcedure['NotificationDate']) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2">' . DateOutDB($r_FineProcedure['FineDate']) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2">' . TimeOutDB($r_FineProcedure['FineTime']) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2">' . $r_FineProcedure['VehiclePlate'] .'</div>';
                //$str_out.= '<div class="table_caption_H col-sm-4">' . $r_FineProcedure['Fee'] .'/' . $r_FineProcedure['MaxFee'] .'</div>';

                $str_out.= '</div><div class="clean_row HSpace4"></div>';
            }
            $str_Buttons = '
                <input type="submit" class="btn btn-success" id="act_button" value="Anteprima elaborazione" style="margin-top:1rem;">
                <div>
                    <input type="checkbox" value="1" name="ultimate" id="ultimate">
                    <span style="color:#fff;"> Definitiva</span>
                </div>';

            $str_out.= '
            <div class="col-sm-2 BoxRowLabel">
                Ordina stampa per
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="ProtocolId" style="position: initial;vertical-align: top;"'.($PrintOrderBy == "ProtocolId" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Cronologico</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="FineDate" style="position: initial;vertical-align: top;"'.($PrintOrderBy == "FineDate" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Data verbale</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="FineNotificationDate" style="position: initial;vertical-align: top;"'.($PrintOrderBy == "FineNotificationDate" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Data di notifica</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="PrintOrderBy" value="TrespasserName" style="position: initial;vertical-align: top;"'.($PrintOrderBy == "TrespasserName" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Nome trasgressore</label>
            </div>
            <div class="col-sm-2 BoxRowCaption"></div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Elaborazione solleciti per
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="ElaborationType" value="any" style="position: initial;vertical-align: top;"'.($ElaborationType == "any" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Tutto</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="ElaborationType" value="closed" style="position: initial;vertical-align: top;"'.($ElaborationType == "closed" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Pagamenti chiusi</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="ElaborationType" value="omitted" style="position: initial;vertical-align: top;"'.($ElaborationType == "omitted" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Pagamenti omessi</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="ElaborationType" value="partial" style="position: initial;vertical-align: top;"'.($ElaborationType == "partial" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Pagamenti parziali</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="ElaborationType" value="late" style="position: initial;vertical-align: top;"'.($ElaborationType == "late" ? ' checked=""' : '').'>
                <label style="line-height:2;vertical-align: top;"> Pagamenti tardivi</label>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Data Stampa
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" class="form-control frm_field_date" name="PrintDate" id="PrintDate" value="'.$PrintDate.'">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Data Calcolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" class="form-control frm_field_date" name="ProcessingDate" id="ProcessingDate" value="'.$ProcessingDate.'">
            </div>
            <div class="col-sm-4 BoxRowCaption">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-12 table_label_H" style="height:8rem;text-align:center;">
            '.$str_Buttons .'    		 		
            </div>
            ';

        }

        $str_out.= '
		    </form>
	    </div>
	    ';
    }

	$str_out.= '<div>
</div>';

	echo $str_out;
?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#search').click(function(){
                $('#f_Search').submit();
            });
			
            $('#f_Search').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    $("#f_Search").submit();
                }
            });
            
            $('#ultimate').click(function(){
            	if($('#ultimate').is(":checked")) {
            		$('#act_button').val('Elaborazione defitiva');
            		$('#act_button').removeClass( "btn-success" ).addClass( "btn-warning" );
            	}else{
            		$('#act_button').val('Anteprima elaborazione');
            		$('#act_button').removeClass( "btn-warning" ).addClass( "btn-success" );
            	}
            });

          	$(".tableRow").mouseover(function(){
          		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
          	});
          	$(".tableRow").mouseout(function(){
          		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
          	});

			var alertText1 = "Si avvisa che la procedura di elaborazione dei solleciti chiuderà eventuali rateizzazioni in corso sui verbali elaborati e che non sarà più possibile creare rateizzazioni su tali verbali per il solo importo della sanzione. Si vuole procedere con l'operazione?";
			var alertText2 = "Si avvisa che la procedura di elaborazione dei solleciti chiuderà eventuali rateizzazioni in corso sui verbali elaborati e che non sarà più possibile creare rateizzazioni su tali verbali per il solo importo della sanzione.  Sei sicuro di voler procedere?";
            
            $('#f_print').bootstrapValidator({
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
            
            
                    PrintDate:{
                        validators: {
                            notEmpty: {message: 'Richiesto'},
            
                            date: {
                                format: 'DD/MM/YYYY',
                                message: 'Data non valida'
                            }
            
                        }
            
                    },

                    ProcessingDate:{
                        validators: {
                            notEmpty: {message: 'Richiesto'},
            
                            date: {
                                format: 'DD/MM/YYYY',
                                message: 'Data non valida'
                            }
            
                        }
            
                    },
                }
            }).on('success.form.bv', function(){
            	//In caso di definitiva con rateizzazioni escluse
                if($('#ultimate').is(":checked") && $('#PaymentRateRadio0').is(":checked"))
                	{
                	if(confirm("Si stanno per elaborare i dati in maniera definitiva. Continuare?")){
                			$('#act_button').prop('disabled', true);
                			$('#ultimate').hide();
                		}
            		else {
                			return false;
                		} 
                	}
                //In caso di definitiva con rateizzazioni non escluse
                if($('#ultimate').is(":checked") && !$('#PaymentRateRadio0').is(":checked")) {
            		if(confirm(alertText1)){
        				if(confirm(alertText2)){
            				$('#act_button').prop('disabled', true);
                			$('#ultimate').hide();
        				}
        				else
        					return false;
            		}
            		else
            			return false;
            	};

            $('.caret-toggle').on("click", function(){
            	$(this).toggleClass('fa-angle-up fa-angle-down');
            });
        });
    });
    </script>
<?php
include(INC."/footer.php");
