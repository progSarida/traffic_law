<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$Search_RuleType = CheckValue('Search_RuleType','s');
$Search_CityId = CheckValue('Search_CityId','s');
$Filter = CheckValue('Filter','n');

$str_GET_Parameter .= "&Search_CityId=$Search_CityId&Search_RuleType=$Search_RuleType";

$str_out .='
    <div class="col-sm-12">
        <form name="f_violation_type" id="f_violation_type" action="mgmt_violation_type_add_exe.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="Disabled" value="0">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Inserimento
        </div>
        <div class="col-sm-6">
            <div class="col-sm-3 BoxRowLabel">
                Id
            </div>
            <div class="col-sm-3 BoxRowCaption">
               <input id="ViolationTypeId" name="ViolationTypeId" type="text" class="form-control frm_field_numeric frm_field_required" style="width:8rem">
               <span id="span_code"></span>
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Ruolo
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '. CreateSelect("Rule","1=1","Title","RuleTypeId","Id","Title",1,true,"","frm_field_required form-control") .'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Documento nazionale associato
            </div>
            <div class="col-sm-9 BoxRowCaption">
               <select id="NationalFormId" class="form-control frm_field_required" name="NationalFormId"></select>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Documento straniero associato
            </div>
            <div class="col-sm-9 BoxRowCaption">
               <select id="ForeignFormId" class="form-control frm_field_required" name="ForeignFormId"></select>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <input name="Title" type="text" class="form-control frm_field_string frm_field_required">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Descrizione
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                <textarea name="Description" class="form-control frm_field_string" style="width:40rem;height:4rem"></textarea>
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                <input type="submit" class="btn btn-default button" id="save" style="margin-top:1rem;" value="Inserisci">
                <input type="button" id="back" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
            </div>
        </div>
        </form>
    </div>
                    
';




echo $str_out;
?>

<script type="text/javascript">

$(document).ready(function () {
	$('#ViolationTypeId').change(function() {

        var Id = $('#ViolationTypeId').val();
        $.ajax({
            url: 'ajax/ajx_check_violationTypeId.php',
            type: 'POST',
            dataType: 'json',
            data: {Id: Id},
            success: function (data) {
                console.log(data);
                if (data.Result == "NO") {
                    $("#save").prop("disabled", true);
                    $("#span_code").addClass("help-block");
                    $("#span_code").html('Già presente!');
                }
                if (data.Result == "OK") {
                    $("#save").prop("disabled", false);
                    $("#span_code").removeClass("help-block");
                    $("#span_code").html('');
                }
            },
            error: function (result) {
                console.log(result);
                alert("error");
            }
        });
    
	});

	$('#RuleTypeId').change(function() {

        var RuleTypeId = $('#RuleTypeId').val();
        $.ajax({
            url: 'ajax/ajx_find_violationForm.php',
            type: 'POST',
            dataType: 'json',
            data: {RuleTypeId: RuleTypeId},
            success: function (data) {
                console.log(data);
                $('#NationalFormId').html(data.National);
                $('#ForeignFormId').html(data.Foreign);

                if (data.National == "")  $('#NationalFormId').attr('disabled', 'disabled'); else $('#NationalFormId').removeAttr('disabled');
                if (data.Foreign == "")  $('#ForeignFormId').attr('disabled', 'disabled'); else $('#ForeignFormId').removeAttr('disabled');
            },
            error: function (result) {
                console.log(result);
                alert("error");
            }
        });
    
	});

	$('#RuleTypeId').change();
});

$('#f_violation_type').bootstrapValidator({
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

    }
})

$('#back').click(function(){
   window.location="<?= $str_BackPage.$str_GET_Parameter."&Filter=".$Filter ?>"
});

</script>


<?php
include(INC."/footer.php");


