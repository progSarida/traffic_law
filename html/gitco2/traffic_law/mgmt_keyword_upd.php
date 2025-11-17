<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$Id = CheckValue('Id','n');

$Search_FormTypeId = CheckValue('Search_FormTypeId','s');
$Search_NationalityId = CheckValue('Search_NationalityId','s');
$Search_LanguageId = CheckValue('Search_LanguageId','s');
$Search_RuleType = CheckValue('Search_RuleType','s');
$Search_Title = CheckValue('Search_Title','s');
$Filter = CheckValue('Filter','n');

// if($_SESSION['usertype']>50){
//     $str_CitySearch = "1=1";
// } else {
//     $str_CitySearch = "CityId='".$_SESSION['cityid']."'";
// }

$str_GET_Parameter .= "&Search_NationalityId=$Search_NationalityId&Search_RuleType=$Search_RuleType&Search_FormTypeId=$Search_FormTypeId&Search_LanguageId=$Search_LanguageId&Search_Title=$Search_Title&Search_RuleType=$Search_RuleType";

$rs_Keyword = $rs->Select('FormKeyword', "Id=$Id");
$r_Keyword = mysqli_fetch_array($rs_Keyword);

//TEMP
$rs_lang = $rs->Select("Language", "Id=".$r_Keyword['LanguageId']);
$r_lang = mysqli_fetch_array($rs_lang);
$s_whereForm = "FormTypeId=".$r_Keyword['FormTypeId']. " AND RuleTypeId=".$r_Keyword['RuleTypeId'].
" AND LanguageId=".$r_Keyword['LanguageId']." AND NationalityId=".$r_Keyword['NationalityId'];
$rs_type = $rs->Select("Form", $s_whereForm); 
$r_type = mysqli_fetch_array($rs_type);
$rs_rule = $rs->Select(MAIN_DB.".Rule", "Id=".$r_Keyword['RuleTypeId']);
$r_rule = mysqli_fetch_array($rs_rule);
$rs_city = $rs->Select("Customer", "CityId='".$r_Keyword['CityId']."'");
$r_city = mysqli_fetch_array($rs_city);
$nationality = ($r_Keyword['NationalityId'] == 1 ? "Nazionale" : "Straniero");
//

$str_out .='
    <div class="col-sm-12">
        <form name="f_keyword" id="f_keyword" action="mgmt_keyword_upd_exe.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="FormKeywordId" value="'.$Id.'">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Modifica
        </div>
        <div class="col-sm-6">
            <div class="col-sm-3 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.StringOutDB($r_city['ManagerCity']).'
                <input type="hidden" name="CityId" value="'.$r_Keyword['CityId'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.StringOutDB($r_rule['Title']).'
                <input type="hidden" name="RuleTypeId" value="'.$r_Keyword['RuleTypeId'].'">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '.$nationality.'
                <input type="hidden" name="NationalityId" value="'.$r_Keyword['NationalityId'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Lingua
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '.StringOutDB($r_lang['Title']).'
                <input type="hidden" name="LanguageId" value="'.$r_Keyword['LanguageId'].'">
            </div>

            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Tipo Testo
            </div>
            <div class="col-sm-9 BoxRowCaption">
               '.StringOutDB($r_type['Title']).'
                <input type="hidden" name="FormTypeId" value="'.$r_Keyword['FormTypeId'].'">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-6 BoxRowCaption">
                <input name="Title" value="'.StringOutDB($r_Keyword['Title']).'" type="text" class="form-control frm_field_string">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Disabilitato
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" name="Disabled" '.($r_Keyword['Disabled'] ? 'checked' : '').'>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Descrizione
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                <textarea name="Description" class="form-control frm_field_string frm_field_required" style="width:40rem;height:4rem">'.StringOutDB($r_Keyword['Description']).'</textarea>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Note
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                <textarea name="Notes" class="form-control frm_field_string" style="width:40rem;height:4rem">'.StringOutDB($r_Keyword['Notes']).'</textarea>
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

});

$('#f_keyword').bootstrapValidator({
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

        Title: {
            validators: {
                notEmpty: {
                    message: 'Richiesto'
                },
                regexp: {
                    regexp: '^(\{\{[^{}]+?\}\}|\{[^{}]+?\})$',
                    message: 'Il titolo deve essere compreso tra { } o {{ }}'
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


