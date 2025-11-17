<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_pagamenti.php");
require_once(CLS."/cls_installment.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Id = CheckValue('Id','n');
$Tab = CheckValue('Tab','s');

//Lo Status è > 7 così rientrano anche gli avvisi bonari
$str_Where .= " AND StatusTypeId>7 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];

//echo $str_BackPage;

if($_SESSION['userlevel']<3){
    $str_WherePage = $str_Where." AND Id=".$Id;
} else {
    $str_WherePage = "Id=".$Id;
}


$strOrder = "Id DESC";


$rs_Fine = $rs->Select("V_Fine",$str_WherePage);

$FindNumber = mysqli_num_rows($rs_Fine);
if($FindNumber==0){
    $rs_Fine = $rs->Select('V_FineRent',$str_WherePage);
    $r_Fine = mysqli_fetch_array($rs_Fine);
    
} else {
    $r_Fine = mysqli_fetch_array($rs_Fine);
}

$cls_pagamenti = new cls_pagamenti($Id, $r_Fine['CityId']);

$str_FolderViolation = (($r_Fine['CountryId']=='Z000') ? 'doc/national/violation/' : 'doc/foreign/violation/').$_SESSION['cityid'].'/'.$Id.'/';
$str_FolderFine = (($r_Fine['CountryId']=='Z000') ? 'doc/national/fine/' : 'doc/foreign/fine/').$_SESSION['cityid'].'/'.$Id.'/';

$ProtocolId = $r_Fine['ProtocolId'];

//$n_NextProtocolId = $ProtocolId+1;
//$n_PreviousProtocolId = $ProtocolId-1;







$str_Next = "";
$str_Previous = "";
$str_Folder = "";





$rs_Id = $rs->SelectQuery("SELECT FineId AS NextId
           FROM V_mgmt_Fine
          WHERE ProtocolId > $ProtocolId AND StatusTypeId>10 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " ORDER BY ProtocolId LIMIT 1");
$r_Id = mysqli_fetch_array($rs_Id);

if (!is_null($r_Id['NextId'])) {
    $str_Next = '<a href="'.$str_CurrentPage.'&Id='.$r_Id['NextId'].'&P='.$str_BackPage.'"><i class="glyphicon glyphicon-arrow-right" style="font-size:3.6rem;color:#fff"></i></a>';
}


$rs_Id = $rs->SelectQuery("SELECT FineId AS PreviousId
           FROM V_mgmt_Fine
          WHERE ProtocolId < $ProtocolId AND StatusTypeId>10 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " ORDER BY ProtocolId DESC LIMIT 1");
$r_Id = mysqli_fetch_array($rs_Id);

if(!is_null($r_Id['PreviousId'])) {
    $str_Previous = '<a href="' . $str_CurrentPage. '&Id=' . $r_Id['PreviousId'] .'&P='.$str_BackPage. '"><i class="glyphicon glyphicon-arrow-left" style="font-size:3.6rem;color:#fff"></i></a>';
}




//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         Injunction
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
//$str_CSSMandatory = ' style="color:#C43A3A; cursor:not-allowed;" ';




require(INC."/fine_section/fine_data.php");
require(INC."/fine_section/trespasser_data.php");
require(INC."/fine_section/documentation_data.php");
require(INC."/fine_section/document_data_upd.php");
require(INC."/fine_section/notification_data.php");
require(INC."/fine_section/payment_data.php");
require(INC."/fine_section/reminder_data.php");
require(INC."/fine_section/dispute_data.php");
require(INC."/fine_section/refund_data.php");
require(INC."/fine_section/126bis_data.php");
require(INC."/fine_section/procedure_data.php");
require(INC."/fine_section/rate_data.php");
require(INC."/fine_section/injunction_data.php");

$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12">
        <div class="col-sm-12" >
            <div class="col-sm-1 BoxRowCaption" style="height:3.8rem">
                ' . $str_Previous . '
            </div>
            <div class="col-sm-10">
                <ul class="nav nav-tabs fine-tabs" id="mioTab" style="background-color: #dfe7e7;font-size:1rem;height:4rem;">
                    <li class="active" id="FineSection"><a href="#Fine" data-toggle="tab">VERBALE</a></li>
                    <li id="TrespasserSection"><a href="#Trespasser" data-toggle="tab">TRASGR/OBBLIGATO</a></li>
                    <li id="NotificationSection"><a href="#Notification"'. $str_CSSNotification .'>NOTIFICA</a></li>
                    <li id="paymentSection"><a href="#Payment"'. $str_CSSPayment .'>PAGAMENTO</a></li>
                    <li id="DocumentSection"><a href="#Document"'. $str_CSSDocument .'>DOCUMENTAZIONE</a></li>
                    <li id="ReminderSection"><a href="#Reminder"'. $str_CSSReminder .'>SOLLECITO</a></li>
                    <li id="DisputeSection"><a href="#Dispute"'. $str_CSSDispute.'>RICORSO</a></li>
                    <li id="RefundSection"><a href="#Refund"'. $str_CSSRefund .'>RIMBORSO</a></li>
                    <li id="RateSection"><a href="#Rate"'. $str_CSSRate .'>RATEIZZAZIONE</a></li>
                    <li id="InjunctionSection"><a href="#Injunction"'. $str_CSSInjunction .'>COATTIVA</a></li>
                    '.($b_126bis ? '<li><a href="#126Bis"'. $str_CSS126Bis .'>COM. 126 BIS</a></li>' : '').'
                    <li id="ProcedureSection"><a href="#Procedure"'. $str_CSSProcedure .'>ULTERIORI DATI</a></li>
                </ul>
            </div>
            <div class="col-sm-1 BoxRowCaption" style="height:3.8rem; text-align:right;">
                ' . $str_Next . '
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="col-sm-12" >
            <div class="tab-content">
                '. $str_Fine_Data .'
                '. $str_Trespasser_Data .'
                '. $str_Notification_Data .'
                '. $str_Payment_Data .'
                '. $str_Document_Data .'
                '. $str_Reminder_data .'
                '. $str_Dispute_data .'
                '. $str_Refund_data .'
                '. $str_Rate_data .'
                '.($b_126bis ? $str_126Bis_data : '').'
                '. $str_Procedure_data .'
                '. $str_Injunction_data .'
            </div>
         </div>
    </div>
                    
    <div class="col-sm-6">
        '. $str_Documentation_data .'
    </div>
            
            
            
            
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                <button class="btn btn-default" id="back" style="margin-top:1rem;">Indietro</button>
            </div>
        </div>
    </div>
</div>';



echo $str_out;
?>
    <script type="text/javascript">

	function loadFileTree(path){
        $("#fileTreeDemo_1").fileTree({ root:path, script: 'jqueryFileTree.php' }, function(file) {
            var FileType = file.split('.').pop();
                
            if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc' || FileType.toLowerCase()=='html'){
                $("#preview_img").hide();
                $("#preview_video").hide();
                
                $("#preview_doc").html('<iframe style="width:100%; height:100%; background:white;" src="'+file+'"></iframe>');
                $("#preview_doc").show();
                
            }else if(FileType.toLowerCase()=='mp4'){
                $("#preview_img").hide();
                $("#preview_doc").hide();
                
                $("#preview_video").attr("src",file);
                $("#preview_video").show();
                
            }else{
                $("#preview_doc").hide();
                $("#preview_video").hide();
                
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }
        });
	}
	
	function enableDisableProcedureChange(fieldName, value){
		var notes = $("#notes_"+fieldName).val();
		var checked = $("#input_"+fieldName).is(':checked') ? 'checked' : '';
		var changedNotes = notes != $(".procedure[data-field='"+fieldName+"']").attr('previous-note');
		var changedValue = checked != $(".procedure[data-field='"+fieldName+"']").attr('previous-value');
		
		console.log(fieldName, $(".procedure[data-field='"+fieldName+"']").attr('previous-value'), checked, changedValue);
    	$(".procedure[data-field='"+fieldName+"']").prop("disabled", !changedValue);
    	$("#notes_"+fieldName).prop("disabled", !changedValue);
	}
    
        $('document').ready(function(){
            var fine_id = <?=$Id?>;
            
        	<?php 
        	if($Tab == 'rate'){
        	       echo "$('[href=\"#Rate\"]').tab('show');";
        	}
        	?>
	    
            <?php if(isset($instalmentScript)) echo $instalmentScript; ?>

            //$('#preview').iZoom({diameter:200});
            $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

            <?= $str_tree ?>

            <?= $str_Img ?>



            <?= $str_PDF ?>
            <?= $str_tree_reminder ?>

            window.onbeforeunload = function(){
            	if($('.procedure').not(':disabled').length > 0){
            		$('[href="#Procedure"]').tab('show');
            		return ' ';
            	} else {
            		return undefinied;
            	}
            };


            $('#back').click(function(){
                //window.location="<?= $str_BackPage.$str_GET_Parameter ?>"
                window.location="<?= impostaParametriUrl(array('Filter' => 1), 'mgmt_fine.php'.$str_GET_Parameter); ?>";
            });
            $('.procedureNotes').keyup(function () {
            	enableDisableProcedureChange($(this).data('field-ref'), $(this).val());
            });
            $('.procedureInput').change(function () {
            	enableDisableProcedureChange($(this).data('field-ref'), $(this).is(':checked') ? 'checked' : '');
            });
            $('.procedure').click(function () {
                var Field = $(this).data("field");
                var Notes = $("#notes_"+Field).val();
                var Checked = $("#input_"+Field).is(':checked');
                var Id = $(this).data("fineid");
                var target = $(this);

				if(Notes.length > 0 && $(".procedure[data-field='"+Field+"']").attr('previous-note') != Notes){
	                target.prop('disabled', true);
                    target.find('i').toggleClass('fa-save fa-circle-notch fa-spin');
                    
                    $.ajax({
                        url: 'ajax/ajx_upd_finenotification_exe.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {Id: Id, Field: Field, Notes: Notes, Checked: Checked},
                        success: function (data) {
                            target.toggleClass('btn-primary btn-success');
                            target.find('i').toggleClass('fa-circle-notch fa-check fa-spin');
                    		target.attr('previous-note', Notes);
                        	target.attr('previous-value', Checked ? 'checked' : '');
                        	enableDisableProcedureChange(Field, Notes);
                            setTimeout(function(){ 
                            	target.toggleClass('btn-primary btn-success');
                            	target.find('i').toggleClass('fa-save fa-check');
                            }, 1500);
                        },
        	            error: function (data) {
        	                console.log(data);
        	                alert("error: " + data.responseText);
        	                
                            target.toggleClass('btn-primary btn-danger');
                            target.find('i').toggleClass('fa-circle-notch fa-times fa-spin');
                            setTimeout(function(){ 
                            	target.toggleClass('btn-primary btn-primary');
                            	target.find('i').toggleClass('fa-save fa-times');
                            	enableDisableProcedureChange(Field, Notes);
                            }, 1500);
        	            }
                    });
				} else {
					alert('È necessario che le note siano compilate e variate rispetto alle precedenti.');
				}


            });

            var ViolationFolder = '<?= $str_FolderViolation; ?>';
            var FineFolder = '<?= $str_FolderFine; ?>';

    		$('#mioTab a').click(function () {
    			if ($(this).attr('href') == "#Document"){
    				$("#FileTreeBox").hide();
    				$("#preview_doc").hide();
    				$("#accordion .glyphicon-eye-open").first().click();
    			} else {
    				if ($(this).attr('href') == "#126Bis"){
    					loadFileTree(FineFolder);
    				} else {
    					loadFileTree(ViolationFolder);
    				}
    				$("#FileTreeBox").show();
    				$("#preview_doc").hide();
    				$(".jqueryFileTree a").first().click();
    			}
    		});

            $("#instalment_result").click(function () {

            });

            $("#btn_CloseFine").click(function(){

                var c = confirm("Si sta per chiudere il verbale. Continuare?");
                if(c){
                    $("#f_FineClose").submit();
                }else return false;
            });

            $("#btn_Attachments").click(function(){
            	if($("input:checked[name*='Attach['").length > 0){
	            	$("#btn_Attachments").prop("disabled", true);
                	var data = $('#f_attachments').serialize();
                    
                    $.ajax({
                        url: 'ajax/ajx_save_fineUpdAttachments.php?fine_id='+fine_id,
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: data,
                        success: function (data) {
                        	var message = '';
                    		if(!data.ZipPath){
                    			message += 'Errore: impossible creare l\'archivio ZIP.';
                    		} else {
                    			message += 'Allegati salvati.';
                    			window.location = data.ZipPath;
                    		}
                    		
                    		if(data.MissingFiles > 0){
                    			message += data.MissingFiles+' documenti non sono stati trovati.';
                    		}
                    		
                    		alert(message);
                    		$("#btn_Attachments").prop("disabled", false);
                        },
                        error: function (data) {
        	                console.log(data);
        	                alert("error: " + data.responseText);
        	                $("#btn_Attachments").prop("disabled", false);
                        }
                    });
            	} else alert('Selezionare almeno un documento.');
            });
        });
        
        $('#paymentSection').click(()=>{$('#FileTreeBox').css("display","none")});	//Per nascondere la barra intermedia della schermata della documentazione
        $('#paymentSection').click(()=>{$('#preview').css("display","none")});
        //Gestisce il cambio di colore delle righe dei pagamenti alla selezione
        var prevRowId;
        
        function changeRowColor(id)
         {
         //Il cambiamento di colore avviene solo se la riga precedentemente evidenziata è diversa da quella selezionata ora
         if(prevRowId!=id)
	   	   {
	       //Rosso/Selezione
           $('#FileName_' + id + ' .BoxRowCaption').css("background-color", "#eaacc1");
	       $('#FileName_' + id + ' .BoxRowLabel').css("background-color", "#96283c");
	       $('#FileName_' + id + ' .BoxRowHTitle').css("background-color", "#6d1830");
	       //Blu/Deselezione
	       $('#FileName_' + prevRowId + ' .BoxRowCaption').css("background-color", "");
	       $('#FileName_' + prevRowId + ' .BoxRowLabel').css("background-color", "");
	       $('#FileName_' + prevRowId + ' .BoxRowHTitle').css("background-color", "#294A9C");
           prevRowId = id;
           }
          }
       
       //Impostazioni per il clic di tutti i tab tranne Documentazione e Rateizzazione
       $('#FineSection, #TrespasserSection, #NotificationSection, #paymentSection, #ReminderSection, #DisputeSection, #RefundSection, #InjunctionSection, #ProcedureSection').on('click',()=>{
       		$('#preview_iframe_img').hide();
       		$('#preview').hide();
       		});
	   //Impostazioni per il clic di tutti i tab tranne Rateizzazione
       $('#FineSection, #TrespasserSection, #NotificationSection, #PaymentSection, #DocumentSection, #ReminderSection, #DisputeSection, #RefundSection, #InjunctionSection, #ProcedureSection').on('click',()=>{
       		$('#preview_iframe_img').hide();
       		$('#preview_img').css('height','60rem');
       		$('#preview_section').css('height','55.2rem');
       		});
          
    </script>
<?php
include(INC."/footer.php");