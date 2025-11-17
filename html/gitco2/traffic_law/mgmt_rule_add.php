<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$a_Lan = unserialize(LANGUAGE);

$Search_RuleType = CheckValue('Search_RuleType','s');
$Search_CityId = CheckValue('Search_CityId','s');
$Filter = CheckValue('Filter','n');

$str_GET_Parameter .= "&Search_CityId=$Search_CityId&Search_RuleType=$Search_RuleType";

if($_SESSION['usertype']>50){
    $str_CustomerSearch = "1=1";
} else {
    $str_CustomerSearch = "CityId='".$_SESSION['cityid']."'";
}

$CityId = $_SESSION['cityid'];




$str_HeadersObjects = '';

for ($i = 1; $i < count($a_Lan); $i++) {
    $str_HeadersObjects .= '
        <div class="col-sm-12" style="height:6rem">
            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Header <img src="' . IMG . '/' . $aLan[$i] . '" style="width:16px" />
            </div>
            <div class="col-sm-4 BoxRowCaption" style="height:6rem">
                <textarea class="frm_field_required frm_field_string" name="PrintHeader'. $a_Lan[$i] .'" style="height:5.4rem; width:40rem;"></textarea>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Object <img src="' . IMG . '/' . $aLan[$i] . '" style="width:16px" />
            </div>
            <div class="col-sm-4 BoxRowCaption" style="height:6rem">
                <textarea class="frm_field_required frm_field_string" name="PrintObject'. $a_Lan[$i] .'" style="height:5.4rem; width:40rem;"></textarea>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
    ';
}




$str_out .='
    <div class="col-sm-12">
        <div class="col-sm-12" >
            <form name="f_rule" id="f_rule" action="mgmt_rule_add_exe.php'.$str_GET_Parameter.'" method="post">
            <div class="col-sm-12 table_label_H" style="text-align:center">
                Inserisci Ruolo
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Ente
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '. CreateSelect("Customer",$str_CustomerSearch,"ManagerCity","CityId","CityId","ManagerCity", $CityId,false,30,"frm_field_required") .'
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelect(MAIN_DB.".Rule","1=1","Title","RuleTypeId","Id","Title","",false,30,"frm_field_required") .'
                <span id="span_code"></span>
            </div>
            
            <div class="clean_row HSpace4"></div>
                    
            '. $str_HeadersObjects .'
                    
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                    <input type="submit" class="btn btn-default button" id="save" style="margin-top:1rem;" value="Inserisci">
                    <input type="button" id="back" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                </div>
            </div>
            </form>
        </div>
    </div>
                    
';




echo $str_out;
?>

<script type="text/javascript">

$(document).ready(function () {
	$('#CityId, #RuleTypeId').change(function() {
	    if ($('#CityId').val() != "" && $('#RuleTypeId').val() != "") {
	        var CityId = $('#CityId').val();
	        var RuleTypeId = $('#RuleTypeId').val();
	        $.ajax({
	            url: 'ajax/ajx_check_rule.php',
	            type: 'POST',
	            dataType: 'json',
	            data: {CityId: CityId, RuleTypeId: RuleTypeId},
	            success: function (data) {
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
	    }
	});
});

$('#f_rule').bootstrapValidator({
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


