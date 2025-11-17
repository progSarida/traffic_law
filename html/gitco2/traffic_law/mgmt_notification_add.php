<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
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
$a_ResultIds_For_ValidatedAddress = unserialize(RESULTIDS_FOR_VALIDATEDADDRESS);
$n_MaxFileSize = GENERIC_DOCUMENT_MAX_FILE_SIZE;

$FineId= CheckValue('Id','n');

$rs_Fine = $rs->Select('V_FineAll', "Id=" . $FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);

$str_DocumentFolder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/". $FineId ."/": FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$FineId ."/";


$a_doc = array();

$rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $FineId." AND DocumentationTypeId IN(10,11,12,82)");
while($r_Documentation = mysqli_fetch_array($rs_Documentation)) {
    $a_doc[$r_Documentation['DocumentationTypeId']] = array(
        "Id" => $r_Documentation['Id'],
        "Documentation" => $r_Documentation['Documentation'],
    );
}

$rs_FineHistory = $rs->Select('FineHistory', "NotificationTypeId=6 AND FineId=" . $FineId);
$SendDate = mysqli_fetch_array($rs_FineHistory)['SendDate'];

$str_SendDate = ($SendDate!="") ? DateOutDB($SendDate) : "";


$str_out .= '
    <div class="row-fluid">
        <div class="col-sm-6">
            <form name="f_notification" method="post" action="mgmt_notification_add_exe.php">
            <input type="hidden" name="FineId" value="'. $FineId .'">
            <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
            <div class="col-sm-4 BoxRowLabel">
                Protocollo
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Fine['ProtocolId'].'/'.$r_Fine['ProtocolYear'].'
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Data Spedizione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date frm_field_required" type="text" name="SendDate" value="'.$str_SendDate.'" style="width:10rem;" />
            </div>                         
            <div class="col-sm-2 BoxRowLabel">
                Data notifica
            </div>             
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" name="NotificationDate" style="width:10rem;" />
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                Esito
            </div>
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                '. CreateSelect("Result","Disabled=0","Id","ResultId","Id","Title","",true) .'
            </div>               
            
            <div class="clean_row HSpace4"></div>            
            
            <div class="col-sm-2 BoxRowLabel">
                Raccomandata
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="LetterNumber"> 
            </div>  
            <div class="col-sm-2 BoxRowLabel">
                Ricevuta
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="ReceiptNumber">  
            </div>
            <div id="ValidatedAddressDIV" class="hidden">
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-2 BoxRowLabel">
                    Indirizzo validato
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.CreateArraySelect(array(0 => "NO", 1 => "SI"), true, "ValidatedAddress", "ValidatedAddress", 0, true).'
                </div>
                <div class="col-sm-8 BoxRowLabel">
                </div>
            </div>
           
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-12" style="text-align:center;line-height:6rem;background-color: rgb(40, 114, 150);">
                <button class="btn btn-success" id="save" type="submit">Salva</button>
                <button class="btn btn-default" id="back" type="button">Indietro</button>
            </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div class="col-sm-12 BoxRowLabel">Estensioni consentite: '.implode(' ', array_keys($a_AllowedExtensions)).'</div>
            <div class="col-sm-12 BoxRowLabel">Dimensione massima: '.$n_MaxFileSize.' MB</div>
            <div class="clean_row HSpace4"></div>
            '.documentEntry($FineId, $r_Fine['CountryId'], 'Fronte', $a_doc, 10).'
            <div class="clean_row HSpace4"></div>   
            '.documentEntry($FineId, $r_Fine['CountryId'], 'Retro', $a_doc, 11).'
            <div class="clean_row HSpace4"></div>   
            '.documentEntry($FineId, $r_Fine['CountryId'], 'CAN/CAD', $a_doc, 12).'
            <div class="clean_row HSpace4"></div>
            '.documentEntry($FineId, $r_Fine['CountryId'], 'Documento unico', $a_doc, 82).'
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
                <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
            </div>     
    
        </div>

        <div class="col-sm-12 BoxRow" style="height:6rem;">

        </div>
        </form>
';



echo $str_out;

?>
<script type="text/javascript">
    $('document').ready(function(){
    	var enableValidatedAddressResultIDs = <?= json_encode($a_ResultIds_For_ValidatedAddress); ?>;
    	
        $('#back').click(function(){
            window.location = "<?= $str_BackPage ?>";
            return false;
        });

        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        $('[id^=DocName]').click(function () {
            var path = '<?= $str_DocumentFolder; ?>';
            var file = path+$(this).attr('file');

            var FileType = file.split('.').pop();

            if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc' || FileType.toLowerCase()=='html'){
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%; background:white\" src='"+file+"'></iframe>");
                $("#preview_doc").show();
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }

        });
        
        $("#ResultId").on("change", function(){
        	var val = parseInt($(this).val());
    		$("#ValidatedAddressDIV").toggleClass("hidden", enableValidatedAddressResultIDs.indexOf(val) < 0);
    		if(enableValidatedAddressResultIDs.indexOf(val) < 0){
    			$("#ValidatedAddress").val(0);
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
                if(!confirm("Si sta per cancellare l'immagine. Continuare?")){
                    return false;
                } else if (!confirm('Sei proprio sicuro di voler procedere?'))
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
                    button.prop('disabled', false);
                    button.find('i').removeClass('fa-circle-notch fa-spin');
                    button.find('i').addClass(operation == 'del' ? 'fa-times' : 'fa-plus');
                },
	            error: function (data) {
	                console.log(data);
	                alert("error: " + data.responseText);
	                button.prop('disabled', false);
	            }
             });
        	
        });

        $('#f_notification').bootstrapValidator({
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


include(INC."/footer.php");

