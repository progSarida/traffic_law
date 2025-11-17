<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/function_postalCharge.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_Error = '';
$str_Where = "
    CityId='{$_SESSION['cityid']}' AND CountryId='Z000' 
    AND ProtocolYear={$_SESSION['year']} AND StatusTypeId=10 
    AND (PagoPA1 IS NULL OR PagoPA2 IS NULL) AND (TrespasserTypeId=1 OR TrespasserTypeId=11)";

//Se "Disabilita elaborazione PagoPA per i preinserimenti di inviti in AG (per art. 193/2 e 80/14)" è attivato in Procedure Ente,
//i preinserimenti con art 193/2 80/14 non verranno restituiti, a meno che non siano creati da inviti in AG oppure rinotifiche
if($r_Customer['DisableKindPagoPAProcessing']){
    $str_Where .= " AND !(((Article = 80 AND Paragraph = '14' AND COALESCE(Letter,'') = '') OR (Article = 193 AND Paragraph = '2' AND COALESCE(Letter,'') = '')) AND KindCreateDate IS NOT NULL AND PreviousId <= 0)";
}

$PagoPAService = $rs->getArrayLine($rs->Select("PagoPAService","Id={$r_Customer['PagoPAService']}"));

if($r_Customer['PagoPAPayment'] == 0){
    $str_Error .= 'L\'ente in uso non è abilitato alle elaborazioni PagoPA. Controllare i dati di configurazione dell\'ente.<br>';
}
if(!$PagoPAService){
    $str_Error .= 'L\'ente in uso non dispone di un servizio PagoPA configurato. Controllare i dati di configurazione dell\'ente.<br>';
}

if($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
}

$limit = $PagoPAService['RowLimit'] ?? 50;

$Count = $rs->getArrayLine($rs->SelectQuery("
    SELECT COUNT(*) AS Count 
    FROM V_ViolationPagoPA 
    WHERE $str_Where"));

$a_ProcessingPagoPA = $rs->getResults($rs->Select('V_ViolationPagoPA', $str_Where, "FineDate,FineTime LIMIT $limit"));

echo $str_out;
?>
<?php if (empty($str_Error)): ?>
<div class="row-fluid">
	<div class="col-sm-2 BoxRowLabel">
		Numero di righe da elaborare:
	</div>
	<div class="col-sm-1 BoxRowCaption">
		<?= $Count['Count']; ?>
	</div>
	<div class="col-sm-2 BoxRowLabel">
		Esecuzioni necessarie:
	</div>
	<div class="col-sm-1 BoxRowCaption">
		<?= ceil($Count['Count']/$limit); ?>
	</div>
	<div class="col-sm-6 BoxRowLabel">
	</div>
	
	<div class="clean_row HSpace4"></div>
	
    <div class="col-sm-12 table_label_H text-center" style="height:8rem;">
    	<?php if($Count['Count'] > 0): ?>
			<button id="prc_pagopa" type="button" progress-tick="500" class="btn btn-info" style="margin-top:1rem;height:5rem;"><strong><img style="width:3rem;" src="<?= IMG.'/pagopa/LogoPagoPA.svg' ?>"/> | Elabora</strong></button>
    	<?php else: ?>
    		<button disabled type="button" class="btn btn-info" style="margin-top:1rem;height:5rem;"><strong><img style="width:3rem;" src="<?= IMG.'/pagopa/LogoPagoPA.svg' ?>"/> | Nessun dato da elaborare</strong></button>
    	<?php endif; ?>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
	<div class="col-sm-12" id="DIV_Progress" style="display:none;">
		<div class="table_label_H col-sm-12" style="background-color:#294A9C;color:white;">AVANZAMENTO DELL' OPERAZIONE</div>
		
		<div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12 table_caption_H"  style="height:auto;text-align:center;padding:0;">
            <div class="progress" style="margin-bottom:0;">
    			<div id="progressbar" class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
    		</div>
            <div id="DIV_Rows" class="col-sm-12">Elaborati: <span></span></div>
            <div id="DIV_Messages" class="col-sm-12" style="display:none">
            	<div class="clean_row HSpace4"></div>
            
    			<div class="table_label_H col-sm-12">Messaggi</div>
    			
    			<div class="clean_row HSpace4"></div>
            </div>
            <div id="DIV_Results" class="col-sm-12" style="display:none;">
            	<div class="clean_row HSpace4"></div>
            
            	<div class="table_label_H col-sm-12">Dati elaborati</div>
            	
            	<div class="clean_row HSpace4"></div>
            	
    			<div class="table_label_H col-sm-1">ID Verbale</div>
    			<div class="table_label_H col-sm-1">Targa</div>
    			<div class="table_label_H col-sm-2">Data</div>
    			<div class="table_label_H col-sm-1">Riferimento</div>
                <div class="table_label_H col-sm-1">TaxCode</div>
    			<div class="table_label_H col-sm-1">PagoPA1</div>
    			<div class="table_label_H col-sm-1">PagoPA2</div>
                <div class="table_label_H col-sm-1 font_small">Parziale Ridotto</div>
    			<div class="table_label_H col-sm-1 font_small">Totale Ridotto</div>
                <div class="table_label_H col-sm-1">Parziale</div>
    			<div class="table_label_H col-sm-1">Totale</div>
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
	</div>
	
    <div class="col-sm-12 table_label_H" style="background-color:#294A9C;color:white;">
		DATI IN PROSSIMA ELABORAZIONE
	</div>
	
	<div class="clean_row HSpace4"></div>
	
	<div class="table_label_H col-sm-2">ID Verbale</div>
	<div class="table_label_H col-sm-2">Targa</div>
	<div class="table_label_H col-sm-2">Data violazione</div>
	<div class="table_label_H col-sm-2">Riferimento</div>
	<div class="table_label_H col-sm-2">PagoPA1</div>
	<div class="table_label_H col-sm-2">PagoPA2</div>
	
	<div class="clean_row HSpace4"></div>
	

	<?php foreach($a_ProcessingPagoPA as $row): ?>
		<div class="tableRow">
	        <div class="table_caption_H col-sm-2"><?= $row['Id']; ?></div>
            <div class="table_caption_H col-sm-2"><?= $row['VehiclePlate']; ?></div>
            <div class="table_caption_H col-sm-2"><?= DateOutDB($row['FineDate']); ?></div>
            <div class="table_caption_H col-sm-2"><?= $row['Code']; ?></div>
            <div class="table_caption_H col-sm-2"><?= $row['PagoPA1']; ?></div>
            <div class="table_caption_H col-sm-2"><?= $row['PagoPA2']; ?></div>
		</div>
        
        <div class="clean_row HSpace4"></div>
	<?php endforeach; ?>
	
</div>

<script src="<?= JS ?>/progressbar.js" type="text/javascript"></script>

<script type="text/javascript">
	$(document).ready(function () {
      	$(document).on('mouseover', '.tableRow', function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error, .table_caption_success').css("background-color", "#cfeaf7c7");
      	});
      	$(document).on('mouseout', '.tableRow', function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error, .table_caption_success').css("background-color", "");
      	});
      	
	    $('#prc_pagopa').on('click', function(){
	    	$('#DIV_Progress').show();
    		progressBar_start('prc_pagopa_exe.php', this, {PagoPAService: '<?= $r_Customer['PagoPAService']; ?>'});
	    });
	    
        $('#prc_pagopa').on('progressDone', function(e, data){
            $('#progressbar').removeClass('progress-bar-info progress-bar-striped active');
			$('#progressbar').addClass(data.Esito == 1 ? (data.Messaggi.length > 0 ? 'progress-bar-warning' : 'progress-bar-success') : 'progress-bar-danger');
			
			if(data.Dati.length > 0){
	        	$.each(data.Dati, function(i, row) {
            		var $row = $('<div>', { 
                        class : 'tableRow'
                    });
                    
                	$.each(row, function(i, entry) {
                		$row.append($('<div>', { 
                            class : 'table_caption_H '+entry.Class,
                            text : entry.Value,
                            style : 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;'
                        }));
                	});
                	$('#DIV_Results').append($row);
            		$('#DIV_Results').append($('<div>', { 
                        class : 'clean_row HSpace4'
                    }));
            	});		
    			$('#DIV_Results').show();
			}
			
			if(data.Messaggi.length > 0){
	        	$.each(data.Messaggi, function(i, row) {
            		$('#DIV_Messages').append($('<div>', { 
                        class : row.Class,
                        text : row.Value
                    }));
            		$('#DIV_Messages').append($('<div>', { 
                        class : 'clean_row HSpace4'
                    }));
            	});		
    			$('#DIV_Messages').show();
			}
			
			if(data.Esito > 0){
				window.open( data.File, ''); 
			}
        });
        $('#prc_pagopa').on('progressFail', function(e, data){
            $('#progressbar').removeClass('progress-bar-info progress-bar-striped active');
            $('#progressbar').addClass('progress-bar-danger');
    		$('#DIV_Messages').append($('<div>', { 
                class : 'alert-danger col-sm-12',
                text : 'Errore: '+data.responseText
            }));
            $('#DIV_Messages').show();
        });
        $('#prc_pagopa').on('progressGet', function(e, data){
            $('#DIV_Rows span').html(data.Contati + ' / ' + data.Totali);
        });
	});
</script>
<?php endif; ?>
<?php
require_once (INC . "/footer.php");
