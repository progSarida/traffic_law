<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_prefectcommunication.php");
require_once(INC."/header.php");
require_once (INC . "/initialization.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

function documentEntry($fineId, $countryId, $entryTitle, $a_doc, $docType){
    return '
    <div class="col-sm-4 BoxRowLabel">
        '. $entryTitle . '
    </div>
    <div class="col-sm-7 table_caption_H">
        <form name="f_Upl'. $docType .'" id="f_Upl'. $docType .'" enctype="multipart/form-data" action="#" method="post">
            <input type="hidden" name="Id" value="'. $fineId .'">
            <input type="hidden" name="CountryId" value="'. $countryId . '">
            <input type="hidden" name="DocumentationTypeId" value="'. $docType .'">
            <input type="hidden" name="DocumentationId" value="'.(isset($a_doc[$docType]) ? $a_doc[$docType]['Id'] : '').'" id="DocumentationId'. $docType .'">
            <input class="'.(isset($a_doc[$docType]) ? 'hidden' : '').'" type="file" id="upl_file'. $docType .'" name="upl_file">
        </form>
        <a id="DocName'. $docType .'" href="javascript:void(0)" file="'.(isset($a_doc[$docType]) ?  $a_doc[$docType]['Documentation'] : '').'">'.(isset($a_doc[$docType]) ?  $a_doc[$docType]['Documentation'] : '').'</a>
    </div>
    <div class="col-sm-1 BoxRowCaption">
        <button id="upload_button'. $docType .'" data-doctype="'. $docType .'" data-toggle="tooltip" data-container="body" data-placement="left" title="Carica" data-btnaction="upl" class="tooltip-r btn btn-success'.(isset($a_doc[$docType]) ? ' hidden' : '').'" style="width: 100%;height: 100%;padding: 0;"><i class="fa fa-plus"></i></button>
        <button id="delete_button'. $docType .'" data-doctype="'. $docType .'" data-toggle="tooltip" data-container="body" data-placement="left" title="Elimina" data-btnaction="del" class="tooltip-r btn btn-danger'.(!isset($a_doc[$docType]) ? ' hidden' : '').'" style="width: 100%;height: 100%;padding: 0;"><i class="fa fa-times"></i></button>
    </div>';
}

$a_AllowedExtensions = unserialize(GENERIC_DOCUMENT_EXT);
$n_MaxFileSize = GENERIC_DOCUMENT_MAX_FILE_SIZE;

$FineId= CheckValue('FineId','n');

$r_Fine = $rs->getArrayLine($rs->Select('V_FineAll', "Id=$FineId"));
$r_PrefectCommunication = $rs->getArrayLine($rs->Select('FinePrefectCommunication', "FineId=$FineId"));
$r_JucicialOffice = $rs->getArrayLine($rs->Select('JudicialOffice', "CityId='{$_SESSION['cityid']}' AND OfficeId=2"));
$r_Signer = $rs->getArrayLine($rs->Select("Controller", "Id={$r_Customer['PrefectCommunicationSigner']}"));

$str_DocumentFolder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/". $FineId ."/": FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$FineId ."/";

$a_doc = array();
$rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $FineId." AND DocumentationTypeId IN(16,17,18)");
while($r_Documentation = $rs->getArrayLine($rs_Documentation)) {
    $a_doc[$r_Documentation['DocumentationTypeId']] = array(
        "Id" => $r_Documentation['Id'],
        "Documentation" => $r_Documentation['Documentation'],
    );
}

echo $str_out;
?>

<div class="row-fluid">
	<?php if(empty($a_doc[18])): ?>
	    <div class="col-sm-12 table_label_H text-center">
        	Inserisci comunicazione prefetto
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12 table_caption_I">
        	Dati prefetto
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <form name="f_prefectcommunication_doccreate" id="f_prefectcommunication_doccreate" method="post" action="mgmt_prefectcommunication_upd_exe.php">
        	<input type="hidden" name="Action" value="<?= MGMT_PREFECTCOMMUNICATION_ACTION_CREATEDOC; ?>">
            <input type="hidden" name="FineId" value="<?= $FineId; ?>">
            <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
            <div class="col-sm-12">
                <div class="col-sm-1 BoxRowLabel">
                    Città
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= StringOutDB($r_JucicialOffice['City']); ?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Provincia
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= StringOutDB($r_JucicialOffice['Province']); ?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Indirizzo
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= StringOutDB($r_JucicialOffice['Address']); ?>
                </div>
        
                <div class="clean_row HSpace4"></div>
        
                <div class="col-sm-1 BoxRowLabel">
                    Zip
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= $r_JucicialOffice['ZIP']; ?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nr. Telefono
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= $r_JucicialOffice['Phone']; ?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Fax
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= $r_JucicialOffice['Fax']; ?>
                </div>
        
                <div class="clean_row HSpace4"></div>
        
                <div class="col-sm-1 BoxRowLabel">
                    Mail
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= StringOutDB($r_JucicialOffice['Mail']); ?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Pec
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= StringOutDB($r_JucicialOffice['PEC']); ?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Web
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= StringOutDB($r_JucicialOffice['Web']); ?>
                </div>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 table_caption_I">
            	Firmatario comunicazione prefetto
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?php if(!empty($r_Signer)): ?>
            		<?= $r_Signer['Code'].' - '.$r_Signer['Qualification'].' '.$r_Signer['Name']; ?>
            	<?php endif; ?>
            </div>
            <div class="col-sm-8 BoxRowLabel">
            </div>
            
            <div class="clean_row HSpace4"></div>
        
            <div class="table_label_H HSpace4" style="height:8rem; line-height: 8rem;">
    	        <button type="submit" id="print" class="btn btn-success" style="width:30rem;">Anteprima comunicazione prefetto</button>
    	        <button class="btn btn-default" id="back" type="button">Indietro</button>
        		<span id="span_ultimate"><input type="checkbox" name="ultimate" id="ultimate" style="margin-left:5rem;"> DEFINITIVO</span>
            </div>
        </form>
	<?php else: ?>
	    <div class="col-sm-12 table_label_H text-center">
        	Modifica dati comunicazione prefetto
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-6">
        	<form name="f_prefectcommunication_upd" id="f_prefectcommunication_upd" method="post" action="mgmt_prefectcommunication_upd_exe.php">
        		<input type="hidden" name="Action" value="<?= MGMT_PREFECTCOMMUNICATION_ACTION_UPDATE; ?>">
                <input type="hidden" name="FineId" value="<?= $FineId; ?>">
                <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
                <div class="col-sm-3 BoxRowLabel">
                    Protocollo
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?= $r_Fine['ProtocolId'].'/'.$r_Fine['ProtocolYear']; ?>
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Modalità di trasmissione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?= CreateArraySelect(array(1 => "Raccomandata", 2 => "PEC"), true, 'SendType', 'SendType', $r_PrefectCommunication['SendType'] ?? 1); ?>
                </div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-2 BoxRowLabel">
                    Data trasmissione
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input value="<?= DateOutDB($r_PrefectCommunication['SendDate'] ?? ''); ?>" class="form-control frm_field_date frm_field_required" type="text" name="SendDate"/>
                </div>                         
                <div class="col-sm-2 BoxRowLabel">
                    Data notifica
                </div>             
                <div class="col-sm-2 BoxRowCaption">
                    <input value="<?= DateOutDB($r_PrefectCommunication['NotificationDate'] ?? ''); ?>" class="form-control frm_field_date" type="text" name="NotificationDate"/>
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Esito
                </div>
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <?= CreateSelect("Result","Disabled=0","Id","ResultId","Id","Title",$r_PrefectCommunication['ResultId'] ?? '',false); ?>
                </div>
                
            	<div class="clean_row HSpace4"></div>            
                
                <div id="DIV_RegisteredMailFields"<?= isset($r_PrefectCommunication['SendType']) && $r_PrefectCommunication['SendType'] != 1 ? ' class="hidden"': ''; ?>>
                    <div class="col-sm-2 BoxRowLabel">
                        Raccomandata
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input value="<?= $r_PrefectCommunication['LetterNumber'] ?? ''; ?>" type="text" class="form-control frm_field_string" id="LetterNumber" name="LetterNumber"> 
                    </div>  
                    <div class="col-sm-2 BoxRowLabel">
                        Ricevuta
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input value="<?= $r_PrefectCommunication['ReceiptNumber'] ?? ''; ?>" type="text" class="form-control frm_field_string" id="ReceiptNumber" name="ReceiptNumber">  
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                </div>
                
                <div class="col-sm-12" style="text-align:center;line-height:6rem;background-color: rgb(40, 114, 150);">
                    <button class="btn btn-success" id="save" type="submit">Salva</button>
                    <button class="btn btn-default" id="back" type="button">Indietro</button>
                </div>
        	</form>
        </div>
        <div class="col-sm-6">
        	<div class="col-sm-12 table_caption_I">Comunicazione</div>
        	<div class="clean_row HSpace4"></div>
        	<div class="col-sm-11 table_caption_H">
        		<a id="DocName18" href="javascript:void(0)" file="<?= $a_doc[18]['Documentation'] ?? ''; ?>"><?= $a_doc[18]['Documentation'] ?? ''; ?></a>
        	</div>
        	<?php if(empty($r_PrefectCommunication['SendDate'])): ?>
        		<form name="f_prefectcommunication_docdelete" id="f_prefectcommunication_docdelete" method="post" action="mgmt_prefectcommunication_upd_exe.php">
                    <div class="col-sm-1 BoxRowCaption">
    	                <input type="hidden" name="FineId" value="<?= $FineId; ?>">
                    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
            			<input type="hidden" name="Action" value="<?= MGMT_PREFECTCOMMUNICATION_ACTION_DELETEDOC; ?>">
            			<button type="submit" id="delete_communication" data-toggle="tooltip" data-container="body" data-placement="left" title="Elimina" class="tooltip-r btn btn-danger" style="width: 100%;height: 100%;padding: 0;"><i class="fa fa-times"></i></button>
        			</div>
        		</form>
    		<?php else: ?>
    			<div class="col-sm-1 BoxRowCaption">
    			</div>
    		<?php endif; ?>
        	<div class="clean_row HSpace4"></div>
        	<div class="col-sm-12 table_caption_I">Notifica</div>
        	<div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRowLabel">Estensioni consentite: <?= implode(' ', array_keys($a_AllowedExtensions)); ?></div>
            <div class="col-sm-12 BoxRowLabel">Dimensione massima: <?= $n_MaxFileSize; ?>MB</div>
            <div class="clean_row HSpace4"></div>
            <?= documentEntry($FineId, $r_Fine['CountryId'], 'Fronte', $a_doc, 16) ?>
            <div class="clean_row HSpace4"></div>   
            <?= documentEntry($FineId, $r_Fine['CountryId'], 'Retro', $a_doc, 17) ?>
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
                <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
            </div>     
        </div>
	<?php endif; ?>
</div>

<script type="text/javascript">

$('document').ready(function(){
    $('#ultimate').click(function(){
        if($('#ultimate').is(":checked")) {
            $('#print').html('Stampa comunicazione definitiva');
            $('#print').removeClass( "btn-success" ).addClass( "btn-warning" );
        }else{
            $('#print').html('Anteprima comunicazione prefetto');
            $('#print').removeClass( "btn-warning" ).addClass( "btn-success" );
        }
    });
    
    setTimeout(function(){
    	$('#DocName18').click();
    }, 500); 

    $('#back').click(function(){
        window.location = "<?= impostaParametriUrl(array("Filter" => 1), $str_BackPage); ?>";
        return false;
    });
    
	$('#SendType').on('change', function(){
		if($(this).val() == 1){
			$('#DIV_RegisteredMailFields').removeClass('hidden');
		} else {
			$('#DIV_RegisteredMailFields').addClass('hidden');
		}
	});

    $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

    $('[id^=DocName]').click(function () {
        var path = '<?= $str_DocumentFolder; ?>';
        var file = path+$(this).attr('file');

        var FileType = file.substr(file.length - 3);

        if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc'){
            $("#preview_img").hide();
            $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src='"+file+"'></iframe>");
            $("#preview_doc").show();
        }else{
            $("#preview_doc").hide();
            $("#preview").attr("src",file);
            $("#preview_img").show();
        }

    });

    $('[id^=upload_button], [id^=delete_button]').click(function () {
        var button = $(this);
        var doctype = button.data('doctype');
        var operation = button.data('btnaction');
        
    	if (operation == 'upl'){
    		if ($('#upl_file'+doctype).get(0).files.length == 0){
    			alert ('È necessario selezionare un file');
    			return false;
    		}
    	} else if (operation == 'del'){
            if(!confirm("Si sta per cancellare il documento in maniera definitiva. Continuare?")){
                return false;
            } else if (!confirm('Si è proprio sicuri di voler procedere?'))
                return false;
    	}
    	
        var formdata = new FormData($('#f_Upl'+doctype)[0]);
        formdata.append('Operation', operation);
        if(operation == 'del'){
        	formdata.append('DocumentationId', $('#DocumentationId'+doctype).val());
        }

        button.prop('disabled', true);
        button.find('i').removeClass('fa-plus fa-times');
        button.find('i').addClass('fa-circle-notch fa-spin');
            
        $.ajax({
            url: 'ajax/ajx_upl_communication_exe.php',
            dataType: 'JSON',
            cache: false,
            contentType: false,
            processData: false,
            data: formdata,                         
            type: 'POST',
            success: function(data){
                button.prop('disabled', false);
                button.find('i').removeClass('fa-circle-notch fa-spin');
                button.find('i').addClass(operation == 'del' ? 'fa-times' : 'fa-plus');
                if(data.Status){
                    if(data.Operation == 'upl'){
                        alert(data.Message);
                        $('#DocumentationId'+doctype).val(data.DocumentationId);
                        $('#upl_file'+doctype+', #upload_button'+doctype+', #delete_button'+doctype).toggleClass('hidden');
                        $('#DocName'+doctype).html(data.Documentation).attr('file', data.Documentation).click();
                        $('#upl_file'+doctype).val('');
                    }
                    if(data.Operation == 'del'){
                    	alert(data.Message);
                        $('#DocumentationId'+doctype).val('');
                        $('#upl_file'+doctype+', #upload_button'+doctype+', #delete_button'+doctype).toggleClass('hidden');
                        $('#DocName'+doctype).html('').attr('file', '');
                        $("#preview_doc").html('');
                        $("#preview").attr("src",'');
                    }
                } else {
                    if(data.Message) alert(data.Message);
                }
            },
            error: function (data) {
                console.log(data);
                alert("error: " + data.responseText);
                button.prop('disabled', false);
            }
         });
    	
    });

    $('#f_prefectcommunication_upd').bootstrapValidator({
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
    
    $('#f_prefectcommunication_doccreate').on("submit", function(){
    	if($('#ultimate').is(":checked")) {
			if(!confirm("Si sta per creare la comunicazione al prefetto in maniera definitiva. Continuare?")){
				return false;
    		}
    	}
		$('#print').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
		$('#print, #back').prop('disabled', true);
		$('#span_ultimate').hide();
    });
    
    $('#f_prefectcommunication_docdelete').on("submit", function(){
		if(!confirm("Si sta per eliminare la comunicazione al prefetto in maniera definitiva. Continuare?")){
			return false;
		} else if(!confirm('Si è proprio sicuri di voler procedere?')){
            return false;
        } else {
	        $("#delete_communication").prop('disabled', true);
            $("#delete_communication").find('i').removeClass('fa-plus fa-times');
            $("#delete_communication").find('i').addClass('fa-circle-notch fa-spin');
		}
    });
});

</script>

<?php 
require_once(INC."/footer.php");

