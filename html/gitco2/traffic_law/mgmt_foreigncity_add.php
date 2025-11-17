<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$Search_CountryId = CheckValue('Search_CountryId','s');
$Search_Title = CheckValue('Search_Title','s');
$Search_ZIP = CheckValue('Search_ZIP','s');
$Search_LandId = CheckValue('Search_LandId','s');
$Filter = CheckValue('Filter','n');

$str_GET_Parameter .= "&Search_CountryId=$Search_CountryId&Search_Title=$Search_Title&Search_ZIP=$Search_ZIP&Search_LandId=$Search_LandId";

$str_out .='
    <div class="col-sm-12">
        <form name="f_foreigncity" id="f_foreigncity" action="mgmt_foreigncity_add_exe.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="Disabled" value="0">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Inserimento
        </div>
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("Country","Id NOT IN ('Z000','Z00Z')","Title","CountryId","Id","Title","",false,"","frm_field_required") .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" class="form-control frm_field_string text-uppercase" name="Title" id="Title">
                <span id="span_code"></span>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                CAP
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="Zip" id="Zip">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Land
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <select class="form-control frm_field_required" name="LandId" id="LandId" disabled>
                    <option></option>
                </select>
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
	$('#CountryId, #Title').change(function() {
        var CountryId = $('#CountryId').val();
        var Title = $('#Title').val();
            
        $.ajax({
            url: 'ajax/ajx_check_foreignCity.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId, Title:Title},
            success: function (data) {
                //console.log(data);
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
                alert("error: " + result.responseText);
            }
        });
	});

	$('#CountryId').change(function() {
		if ($(this).val() == "Z102" || $(this).val() == "Z112")
			$('#LandId').prop("disabled", false);
		else
			$('#LandId').prop("disabled", true);

        var CountryId = $(this).val();
            
        $.ajax({
            url: 'ajax/ajx_get_land.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId},
            success: function (data) {
            	$('#LandId').html(data.Options);
            },
            error: function (result) {
                console.log(result);
                alert("error: " + result.responseText);
            }
        });
	});

});

$('#f_foreigncity').bootstrapValidator({
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
   window.location="<?= "mgmt_foreigncity.php".$str_GET_Parameter."&Filter=".$Filter ?>"
});

</script>


<?php
include(INC."/footer.php");


