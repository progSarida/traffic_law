<?php

$FineNote = "";
$StatusTypeSelect = "";
$ReasonId = "";
$DynamicFormName = "";

if ($isPageUpdate){
    $FineNote = StringOutDB($r_Fine['Note']);
    $StatusTypeSelect = $r_Fine['StatusTypeId'];
    $ReasonId = $r_FirstArticle['ReasonId'];
    //Se ci sono più trasgressori e hanno lingue diverse non si può dire con chiarezza in che lingua sarà il verbale, dato che ogni trasgressore avrà il testo nella sua lingua
    $DynamicFormName = getFormDynamicTitle($r_Fine['FineTypeId'],$r_Fine['StatusTypeId'],$r_Fine['CityId'],$r_Fine['CountryId'],$r_Fine['CountryId'],$r_FirstArticle['ViolationTypeId']);
}

$str_out .= '
  				<div class="clean_row HSpace4"></div>

	        	<div class="col-sm-12" id="div_Reason">
        			<div class="col-sm-3 BoxRowLabel">
        				Mancata contestazione
					</div>
					<div class="col-sm-8 BoxRowCaption add_text">
                        <div class="col-sm-2">
                            <input class="form-control frm_field_numeric" maxlength="2" type="text" name="ReasonCode" id="ReasonCode">
                        </div>
                        <div class="col-sm-10">
                            '. CreateSelectQuery("SELECT R.Id,CONCAT(R.Progressive,' - ',R.TitleIta) AS Title FROM Reason R JOIN ViolationType V on R.ViolationTypeId = V.Id WHERE R.CityId='{$_SESSION['cityid']}' AND V.RuleTypeId={$_SESSION['ruletypeid']} AND ".(!empty($ReasonId) ? "(R.Disabled=0 OR R.Id=$ReasonId)" : "R.Disabled=0")." ORDER BY R.Id","ReasonId","Id","Title",$ReasonId,false,'', "frm_field_required") .'
                            <input name="ReasonOwner" id="ReasonOwner" type="text" class="form-control frm_field_string frm_field_required" style="display: none">
                        </div>
                    </div>
				
					<div class="col-sm-1 BoxRowLabel">
                        <i class="fa fa-edit" id="Edit_Contestazione" style="position:absolute; top:1px;right:1px;font-size: 2rem; display: none"></i>
					</div>
  				</div>   	
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12" style="height:6.4rem;">
        			<div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
        				Note operatore
					</div>
					<div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                        <textarea name="Note" class="form-control frm_field_string" style="height: 5.8rem;margin-left: 0;resize: none;">'.$FineNote.'</textarea>	
					</div>
  				</div>

                <div class="clean_row HSpace4"></div>';

if($isPageUpdate){
    $str_out .= '
  				<div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Testo dinamico collegato
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        '.$DynamicFormName.'
                    </div>
                </div>
  				<div class="clean_row HSpace4"></div>';
}
$str_out .= '
  				<div class="col-sm-12">
                    <div col-sm-6>
                        <div class="col-sm-2 BoxRowLabel" >
                            Tipo infrazione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
                            <span id="span_ViolationTitle">&nbsp;</span>
                        </div>
                    </div>
                    '.$str_ControllerBox.'
                </div>
  				<div class="clean_row HSpace4"></div>
  				
                <div id="StatusType" class="col-sm-12" style="display:none;">
                    <div class="clean_row HSpace4"></div>
        			<div class="col-sm-3 BoxRowLabel">
        				Stato pratica
					</div>
					<div class="col-sm-9 BoxRowCaption">
                        ' . CreateSelect("StatusType", "Id<=10", "Id", "StatusTypeSelect", "Id", "Title", $StatusTypeSelect, true) . '
					</div>
  				</div>
  				';

?>

<script>
    $('document').ready(function () {
        $('#NotificationType').change(function () {
            if($(this).val()==2){
                $('#ReasonId').removeClass('frm_field_required');
                $('#ReasonOwner').removeClass('frm_field_required');
                $('#ReasonId').append("<option></option>");
            } else {
                $('#ReasonId').addClass('frm_field_required');
                $('#ReasonOwner').addClass('frm_field_required');
            }
        });
        $('#ReasonCode').keyup(function (e) {
            const code = $(this).val();
            var isFound = false;
            if(code==''){
                $("#ReasonId").val($("#ReasonId option:first").val());
                return;
            }

            $("#ReasonId > option").each(function() {
                if ($(this).html().indexOf(code)>=0) {
                    $('#ReasonId').val($(this).val());
                    isFound = true;
                }
            });
            if(!isFound){
                $("#ReasonId").val($("#ReasonId option:first").val());
            }
        })
    });
</script>
