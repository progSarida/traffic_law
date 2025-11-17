<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

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

$rs_Notification = $rs->Select('V_FineNotification', "FineId=" . $FineId);
$r_Notification = mysqli_fetch_array($rs_Notification);


$NotificationDate = (trim($r_Notification['NotificationDate'])=="") ? "" : DateOutDB($r_Notification['NotificationDate']);

$str_DocumentFolder = ($r_Notification['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/". $FineId ."/": FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$FineId ."/";

$a_doc = array();

$rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $FineId." AND DocumentationTypeId IN(10,11,12,82)");
while($r_Documentation = mysqli_fetch_array($rs_Documentation)) {
    $a_doc[$r_Documentation['DocumentationTypeId']] = array(
        "Id" => $r_Documentation['Id'],
        "Documentation" => $r_Documentation['Documentation'],
    );
}


$rs_Row = $rs->Select("FineHistory", "FineId=".$FineId." AND NotificationTypeId=6");
$r_Row = mysqli_fetch_array($rs_Row);




if($_SESSION['userlevel']>=3){
    $str_NotificationFee = '
        <form id="f_notification" name="f_notification" method="post" action="mgmt_notification_upd_exe.php">
        <input type="hidden" name="FineId" value="'.$FineId.'">
        <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
            <div class="col-sm-2 BoxRowLabel">
                Notifica
            </div>            
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="NotificationFee" value="'.$r_Row['NotificationFee'].'" style="width:10rem;" />
            </div>
            <div class="col-sm-2 BoxRowLabel">
                CAN
            </div>              
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="CanFee" value="'.$r_Row['CanFee'].'" style="width:10rem;" />
            </div>
            <div class="col-sm-2 BoxRowLabel">
                CAD
            </div>            
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="CadFee" value="'.$r_Row['CadFee'].'" style="width:10rem;" />
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel"></div>             
            <div class="col-sm-2 BoxRowLabel">
                Notificatore
            </div>              
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="NotifierFee" value="'.$r_Row['NotifierFee'].'" style="width:10rem;" />
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Altro
            </div>             
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="OtherFee" value="'.$r_Row['OtherFee'].'" style="width:10rem;" />
            </div>
            
            <div class="clean_row HSpace4"></div>
                  
            <div class="col-sm-2 BoxRowLabel">
                Data Spedizione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date frm_field_required" type="text" name="SendDate" value="'.DateOutDB($r_Notification['SendDate']).'" style="width:10rem;" />
            </div>                         
            <div class="col-sm-2 BoxRowLabel">
                Data notifica
            </div>             
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" name="NotificationDate" value="'.$NotificationDate.'" style="width:10rem;" />
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                Esito
            </div>
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                '. CreateSelect("Result","Disabled=0","Id","ResultId","Id","Title",$r_Notification['ResultId'],true) .'
            </div>              
            
            <div class="clean_row HSpace4"></div>            
            
            <div class="col-sm-2 BoxRowLabel">
                Raccomandata
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="LetterNumber" value="'.$r_Notification['LetterNumber'].'"> 
            </div>  
            <div class="col-sm-2 BoxRowLabel">
                Ricevuta
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="ReceiptNumber" value="'.$r_Notification['ReceiptNumber'].'">  
            </div>

            <div id="ValidatedAddressDIV"'.(!in_array($r_Notification['ResultId'], $a_ResultIds_For_ValidatedAddress) ? ' class="hidden"' : '').'>
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-2 BoxRowLabel">
                    Indirizzo validato
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.CreateArraySelect(array(0 => "NO", 1 => "SI"), true, "ValidatedAddress", "ValidatedAddress", $r_Notification['ValidatedAddress'], true).'
                </div>
                <div class="col-sm-8 BoxRowLabel">
                </div>
            </div>

            <div class="clean_row HSpace4"></div>
           
            <div class="col-sm-12 BoxRowLabel" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;margin-top:1rem;">
                    <button class="btn btn-info" type="submit"><i class="fa fa-refresh"></i> Aggiorna</button>
                 </div>    
            </div>
        </form>                            
    ';
}else{
    $str_NotificationFee = '
        <div class="col-sm-2 BoxRowLabel">
            Notifica
        </div>            
        <div class="col-sm-2 BoxRowCaption">
              '.$r_Row['NotificationFee'].'
        </div>
        <div class="col-sm-2 BoxRowLabel">
            CAN
        </div>            
        <div class="col-sm-2 BoxRowCaption">
              '.$r_Row['CanFee'].'
        </div>        
        <div class="col-sm-2 BoxRowLabel">
            CAD
        </div>            
        <div class="col-sm-2 BoxRowCaption">
              '.$r_Row['CadFee'].'
        </div> 
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel"></div>  
        <div class="col-sm-2 BoxRowLabel">
            Notificatore
        </div>              
        <div class="col-sm-2 BoxRowCaption">
              '.$r_Row['NotifierFee'].'
        </div> 
        <div class="col-sm-2 BoxRowLabel">
            Altro
        </div>              
        <div class="col-sm-2 BoxRowCaption">
              '.$r_Row['OtherFee'].'
        </div>         
        ';


}




$str_out .= '
<div class="row-fluid">
    <div class="col-sm-6">
        <div class="col-sm-4 BoxRowLabel">
            Protocollo
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['ProtocolId'].'/'.$r_Notification['ProtocolYear'].'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            '.$str_NotificationFee.'
        </div>

        <div class="clean_row HSpace4"></div>           
   
        <div class="col-sm-2 BoxRowLabel">
            Data Spedizione
        </div>
        <div class="col-sm-2 BoxRowCaption">
            '.DateOutDB($r_Notification['SendDate']).'
        </div>            
        <div class="col-sm-2 BoxRowLabel">
            Data Notifica
        </div>
        <div class="col-sm-2 BoxRowCaption">
            '.DateOutDB($r_Notification['NotificationDate']).'
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Data LOG
        </div>
        <div class="col-sm-2 BoxRowCaption">
            '.DateOutDB($r_Notification['LogDate']).'
        </div>                
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Raccomandata
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['LetterNumber'].'
        </div>  
        <div class="clean_row HSpace4"></div>          
        <div class="col-sm-4 BoxRowLabel">
            Ricevuta
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['ReceiptNumber'].'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Esito
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['Title'].'
        </div> 
        
        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Scatola
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['Box'].'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Lotto
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['Lot'].'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Posizione
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Notification['Position'].'
        </div>                  
    </div>
    <div class="col-sm-6">
            <div class="col-sm-12 BoxRowLabel">Estensioni consentite: '.implode(' ', array_keys($a_AllowedExtensions)).'</div>
            <div class="col-sm-12 BoxRowLabel">Dimensione massima: '.$n_MaxFileSize.' MB</div>
            <div class="clean_row HSpace4"></div>
            '.documentEntry($FineId, $r_Notification['CountryId'], 'Fronte', $a_doc, 10).'
            <div class="clean_row HSpace4"></div>   
            '.documentEntry($FineId, $r_Notification['CountryId'], 'Retro', $a_doc, 11).'
            <div class="clean_row HSpace4"></div>   
            '.documentEntry($FineId, $r_Notification['CountryId'], 'CAN/CAD', $a_doc, 12).'
            <div class="clean_row HSpace4"></div>
            '.documentEntry($FineId, $r_Notification['CountryId'], 'Documento unico', $a_doc, 82).'
            <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
                <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                    <img id="preview" class="iZoom"  />
                </div>
                <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
            </div>      
    </div>

    <div class="clean_row HSpace4"></div>  

    <div class="table_label_H HSpace4" style="height:8rem;">
        <button type="button" id="print" class="btn btn-success" style="margin-top:2rem;width:inherit;"><i class="fa fa-print fa-fw"></i> Stampa</button>
    	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
    </div>      
        
</div>
';



echo $str_out;

?>
<script type="text/javascript">
    $('document').ready(function(){
    	var enableValidatedAddressResultIDs = <?= json_encode($a_ResultIds_For_ValidatedAddress); ?>;

        $('#back').click(function () {
            window.location = "<?= $str_BackPage ?>"
            return false;
        });

        $('#print').click(function(){
            window.location="mgmt_notification_prn_exe.php<?= $str_GET_Parameter ?>&Id=<?= $FineId ?>"
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

