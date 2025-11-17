<?php
$str_CSSRate = 'data-toggle="tab"';
$str_Rate = '';

$a_PaymentRate = $rs->getResults($rs->SelectQuery("
    SELECT PR.*,DT.Description AS RateTypeName, CONCAT_WS(' ', T.CompanyName, T.Surname, T.Name) AS TrespasserName
    FROM PaymentRate PR
    LEFT JOIN Document_Type DT ON PR.DocumentTypeId = DT.Id
    LEFT JOIN Trespasser T ON PR.TrespasserId = T.Id
    WHERE PR.FineId = $Id ORDER BY Id"));

$a_RateStatus = array(
    RATEIZZAZIONE_APERTA => array('Value' => 'APERTA', 'Class' => 'table_caption_success'),
    RATEIZZAZIONE_CHIUSA => array('Value' => 'CHIUSA', 'Class' => 'table_caption_error')
);

$a_RateOutcomeResult = array(
    RATEIZZAZIONE_ESITO_ACCOLTA => 'Richiesta accolta',
    RATEIZZAZIONE_ESITO_RESPINTA => 'Richiesta respinta'
);

if(count($a_PaymentRate) > 0){
    $str_Rate .= '
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">
            LISTA RATEIZZAZIONI
        </div>';

    foreach($a_PaymentRate as $i_Rate => $r_PaymentRate){
        $str_RatePath = NATIONAL_RATE."/{$r_Fine['CityId']}/{$r_PaymentRate['FineId']}/{$r_PaymentRate['Id']}";
        $str_RatePathHtml = NATIONAL_RATE_HTML."/{$r_Fine['CityId']}/{$r_PaymentRate['FineId']}/{$r_PaymentRate['Id']}";
        $str_RateNumber = '';
        $i_Rate++;
        
        $a_RateDocs = array(
            'Request' => array(
                'Path' => "$str_RatePath/Richiesta_Rateizzazione.pdf",
                'PathHtml' => "$str_RatePathHtml/Richiesta_Rateizzazione.pdf",
                'Mime' => @mime_content_type("$str_RatePath/Richiesta_Rateizzazione.pdf") ?: ''),
            'Result' => array(
                'Path' => "$str_RatePath/Esito_Rateizzazione.pdf",
                'PathHtml' => "$str_RatePathHtml/Esito_Rateizzazione.pdf",
                'Mime' => @mime_content_type("$str_RatePath/Esito_Rateizzazione.pdf") ?: ''),
            'Bill' => array(
                'Path' => "$str_RatePath/Bollettini_Rateizzazione.pdf",
                'PathHtml' => "$str_RatePathHtml/Bollettini_Rateizzazione.pdf",
                'Mime' => @mime_content_type("$str_RatePath/Bollettini_Rateizzazione.pdf") ?: ''),
        );
        
        $a_PaymentRateNumber = $rs->getResults($rs->Select('PaymentRateNumber', "PaymentRateId={$r_PaymentRate['Id']}"));
        
        if(count($a_PaymentRateNumber) > 0){
            foreach($a_PaymentRateNumber as $r_PaymentRateNumber)
                $str_RateNumber .= '
                <div class="col-sm-3 BoxRowLabel">
                    Rata N. '.$r_PaymentRateNumber['RateNumber'].'
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    € '.NumberDisplay($r_PaymentRateNumber['Amount']).'
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Data
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '.DateOutDB($r_PaymentRateNumber['PaymentDate']).'
                </div>

                <div class="clean_row HSpace4"></div>
            ';
        } else {
            $str_RateNumber .= '
                <div class="cols-sm-12 table_caption_H">
                    Nessuna rata da mostrare.
                </div>
            ';
        }
        
        $str_Rate .= '
        <div class="clean_row HSpace4"></div>

        <div class="rate-group">
            <div class="col-sm-1 BoxRowLabel">
                N.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.$i_Rate.'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.$r_PaymentRate['RateTypeName'].'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Stato
            </div>
            <div class="col-sm-3 table_caption_H '.$a_RateStatus[$r_PaymentRate['StatusRateId']]['Class'].'">
                '.$a_RateStatus[$r_PaymentRate['StatusRateId']]['Value'].'
            </div>
            <div class="col-sm-2" style="padding:0;">
                <div class="col-sm-3 table_caption_I text-center" style="padding:0;">
                    <i class="fas fa-angle-down caret-toggle" data-toggle="collapse" data-target="#collapse_rate_'.$i_Rate.'" aria-expanded="false" aria-controls="collapse" style="margin-top: 0.2rem;font-size: 1.5rem;"></i>
                </div>
                <div class="col-sm-9 BoxRowLabel text-center" style="padding:0;">
                    <i data-toggle="tooltip" data-placement="top" title="Richiesta"
                       data-mime="'.$a_RateDocs['Request']['Mime'].'" 
                       data-file="'.$a_RateDocs['Request']['PathHtml'].'" 
                       class="'.(!file_exists($a_RateDocs['Request']['Path']) ? 'disabled' : '').' rate_request fas fa-file-signature fa-fw tooltip-r" 
                       style="'.(!file_exists($a_RateDocs['Request']['Path']) ? 'color: #909090;' : '').'margin-top: 0.2rem;font-size: 1.5rem;">
                    </i>
                    <i data-toggle="tooltip" data-placement="top" title="Esito"
                       data-mime="'.$a_RateDocs['Result']['Mime'].'" 
                       data-file="'.$a_RateDocs['Result']['PathHtml'].'" 
                       class="'.(!file_exists($a_RateDocs['Result']['Path']) ? 'disabled' : '').' rate_result fas fa-file-contract fa-fw tooltip-r" 
                       style="'.(!file_exists($a_RateDocs['Result']['Path']) ? 'color: #909090;' : '').'margin-top: 0.2rem;font-size: 1.5rem;"></i>
                    <i data-toggle="tooltip" data-placement="top" title="Bollettini"
                       data-mime="'.$a_RateDocs['Bill']['Mime'].'" 
                       data-file="'.$a_RateDocs['Bill']['PathHtml'].'" 
                       class="'.(!file_exists($a_RateDocs['Bill']['Path']) ? 'disabled' : '').' rate_bill fas fa-money-check-alt fa-fw tooltip-r" 
                       style="'.(!file_exists($a_RateDocs['Bill']['Path']) ? 'color: #909090;' : '').'margin-top: 0.2rem;font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-sm-1 table_caption_I" style="padding:0;">
                '.($r_PaymentRate['StatusRateId'] != RATEIZZAZIONE_CHIUSA
                    ? '<button data-rateid="'.$r_PaymentRate['Id'].'" class="btn btn-danger btn-xs close_rate" style="padding:0;height:100%;width:100%;">Chiudi</button>'
                    : '').'
            </div>
            <div id="accordion_rate_'.$i_Rate.'">
                <div class="col-sm-12 collapse" id="collapse_rate_'.$i_Rate.'" aria-labelledby="heading_rate_'.$i_Rate.'" data-parent="#accordion" aria-expanded="false" style="height:0;padding:0">
                    <div class="alert alert-info">
                        <div class="BoxRowHTitle">Dettaglio</div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-2 BoxRowLabel">
                            Trasgressore
                        </div>
                        <div class="col-sm-10 BoxRowCaption">
                            '.$r_PaymentRate['TrespasserName'].'
                        </div>

                        <div class="clean_row HSpace4"></div>

                        <div class="col-sm-2 BoxRowLabel">
                            Data reg.
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.DateOutDB($r_PaymentRate['RegDate']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Importo totale
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            € '.NumberDisplay($r_PaymentRate['InstalmentAmount']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Numero di rate
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.$r_PaymentRate['InstalmentNumber'].'
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-2 BoxRowLabel">
                            Nominativo
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.$r_PaymentRate['RateName'].'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Posizione
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.$r_PaymentRate['Position'].'
                        </div>

                        <div class="clean_row HSpace4"></div>

                        <div class="col-sm-2 BoxRowLabel">
                            Esito richiesta
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.$a_RateOutcomeResult[$r_PaymentRate['RequestOutcome']].'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data chiusura
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.DateOutDB($r_PaymentRate['ClosingDate']).'
                        </div>

                        <div class="clean_row HSpace4"></div>

                        <div class="col-sm-2 BoxRowLabel">
                            Motivazione
                        </div>
                        <div class="col-sm-10 BoxRowCaption">
                            '.$r_PaymentRate['ResponseReason'].'
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="BoxRowHTitle">Rate</div>
    
                        <div class="clean_row HSpace4"></div>
        
                        '.$str_RateNumber.'
                    </div>
                </div>
            </div>
        </div>
';
    }

} else $str_CSSRate = ' style="color:#C43A3A; cursor:not-allowed;" ';

$str_Rate_data = '
    <div class="tab-pane" id="Rate">            
        <div class="col-sm-12">
            '.$str_Rate.'
        </div>
    </div>';
?>
<script>
$(document).ready(function () {
    $('.caret-toggle').on("click", function(){
    	$(this).toggleClass('fa-angle-up fa-angle-down');
    });

	$('.rate_request:not(.disabled), .rate_result:not(.disabled), .rate_bill:not(.disabled)').on("click", function(){

		var file = $(this).data('file');
		var FileType = $(this).data('mime');
		
        if(FileType === 'application/pdf' || FileType === 'application/msword'){
            $("#preview_img").hide();
            $("#preview_doc").html("<iframe style='width:100%; height:100%' src='"+file+"'></iframe>");
            $("#preview_doc").show();
        } else {
            $("#preview_doc").hide();
            $("#preview").attr("src",file);
            $("#preview_iframe_img").attr("src",file);
            $("#preview_img").show();
        }
        
        $('.rate-group [data-file]').css("outline", "");
        $(this).css("outline", "dashed 4px orange");
        $('.rate-group').find('.BoxRowLabel, .BoxRowCaption, .table_caption_I').css("background-color", "");
        $(this).closest('.rate-group').find('.BoxRowLabel').css("background-color", "#96283c");
        $(this).closest('.rate-group').find('.BoxRowCaption').css("background-color", "#eaacc1");
        $(this).closest('.rate-group').find('.table_caption_I').css("background-color", "#6d1830");
    });


    $('.rate_request:not(.disabled), .rate_result:not(.disabled), .rate_bill:not(.disabled)').hover(function(){
        $(this).css("cursor","pointer");
    },function(){
        $(this).css("cursor","");
    });
    
    $('.close_rate').on('click', function (){
    	var id = $(this).data('rateid');
    	
    	if(confirm('Si stà per chiudere la rateizzazione selezionata, continuare?')){
			$('.close_rate').prop('disabled', true);
			
			$.ajax({
                url: 'ajax/ajx_close_rate.php',
                type: 'POST',
                dataType: 'json',
                data: {Id: id},
                ContentType: "application/json; charset=UTF-8",
                success: function (data) {
                	if(data.Success){
                		location.reload();
                	} else {
                		alert("Errore: " + data.Message);
                		$('.close_rate').prop('disabled', false);
                	}
                },
                error: function (data) {
                    console.log(data);
                    alert("error: " + data.responseText);
                    $('.close_rate').prop('disabled', false);
                }
            });
    	}
    });
})
</script>
