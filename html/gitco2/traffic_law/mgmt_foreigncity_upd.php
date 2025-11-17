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

$Id = CheckValue('Id','n');

$rs_ForeignCity = $rs->SelectQuery('SELECT FC.*, C.Title CountryTitle FROM ForeignCity FC JOIN Country C ON FC.CountryId=C.Id WHERE FC.Id='.$Id);
$r_ForeignCity = mysqli_fetch_array($rs_ForeignCity);

$LandOptions = "<option></option>";

if($r_ForeignCity['CountryId'] == "Z102" || $r_ForeignCity['CountryId'] == "Z112"){
    $rs_Land = $rs->Select("sarida.Land", 'CountryId="'.$r_ForeignCity['CountryId'].'"');
    while ($r_Land = mysqli_fetch_array($rs_Land)){
        $LandOptions .= ($r_ForeignCity['LandId'] == $r_Land['Id'])
            ? '<option selected value='.$r_Land['Id'].'>'.StringOutDB($r_Land['Title']).'</option>'
            : '<option value='.$r_Land['Id'].'>'.StringOutDB($r_Land['Title']).'</option>';
    }
}

$str_out .='
    <div class="col-sm-12">
        <form name="f_foreigncity" id="f_foreigncity" action="mgmt_foreigncity_upd_exe.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="ForeignCityId" Id="ForeignCityId" value="'.$Id.'">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Modifica
        </div>
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.StringOutDB($r_ForeignCity['CountryTitle']).'
                <input type="hidden" value="'.$r_ForeignCity['CountryId'].'" id="CountryId" name="CountryId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input value="'.StringOutDB($r_ForeignCity['Title']).'" type="text" class="form-control frm_field_required frm_field_string text-uppercase" name="Title" id="Title">
                <span id="span_code"></span>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                CAP
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input value="'.StringOutDB($r_ForeignCity['Zip']).'" type="text" class="form-control frm_field_string" value="'.$Search_ZIP.'" name="Zip" id="Zip">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Land
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <select class="form-control frm_field_required" name="LandId" id="LandId"'.($r_ForeignCity['CountryId'] == "Z102" || $r_ForeignCity['CountryId'] == "Z112" ? "" : " disabled").'>
                    '.$LandOptions.'
                </select>
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                <input type="submit" class="btn btn-default button" id="save" style="margin-top:1rem;" value="Modifica">
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
	$('#Title').change(function() {
        var CountryId = $('#CountryId').val();
        var Title = $('#Title').val();
        var ForeignCityId = $('#ForeignCityId').val();
            
        $.ajax({
            url: 'ajax/ajx_check_foreignCity.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId, Title:Title, ForeignCityId:ForeignCityId},
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


