<?php

$str_out .= '
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
                    <div col-sm-6 style="display: none">
                        <div class="col-sm-2 BoxRowLabel" >
                            Tipo infrazione
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
                            <span id="span_ViolationTitle">&nbsp;</span>
                        </div>
                    </div>
                    '.$str_AccertatoreBox.'
                </div>
  				<div class="clean_row HSpace4"></div>
  				
	        	<div class="col-sm-12" id="div_Reason">
        			<div class="col-sm-3 BoxRowLabel">
        				Mancata contestazione
					</div>
					<div class="col-sm-8 BoxRowCaption add_text">
                        <div class="col-sm-1">
                            <input class="form-control frm_field_numeric" maxlength="2" type="text" name="ReasonCode" id="ReasonCode" style="width:5rem">
                        </div>
                        <div class="col-sm-11">
                            '. CreateSelect("Reason","CityId='".$_SESSION['cityid']."'","Id","ReasonId","Id","TitleIta","",false,10, "frm_field_required") .'
                        </div>
                        <div class="col-sm-11">
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
                        <textarea name="Note" class="form-control frm_field_string" style="width:40rem;height:5.5rem"></textarea>	
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
