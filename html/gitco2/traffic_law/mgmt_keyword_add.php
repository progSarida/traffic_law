<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

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

$str_out .='
    <div class="col-sm-12">
        <form name="f_keyword" id="f_keyword" action="mgmt_keyword_add_exe.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="Disabled" value="0">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Inserimento
        </div>
        <div class="col-sm-6">
            <div class="col-sm-3 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.$_SESSION['citytitle'].'
                <input type="hidden" name="CityId" value="'.$_SESSION['cityid'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelect(MAIN_DB.".Rule","1=1","Title","RuleTypeId","Id","Title","",false,"","frm_field_required") .'
            </div>      
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <select id="Search_NationalityId" name="Search_NationalityId" class="form-control frm_field_required">
                        <option value=""></option>                        		
                        <option value="1">Nazionale</option>
                		<option value="0">Straniero</option>
                </select>
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Lingua
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '. CreateSelect("Language","1=1","Title","LanguageId","Id","Title","",false,"","frm_field_required form-control") .'
            </div>
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-3 BoxRowLabel">
                Tipo Testo
            </div>
            <div class="col-sm-9 BoxRowCaption">
               '. CreateSelect("FormType","1=1","Title","FormTypeId","Id","Title","",false,"","frm_field_required form-control") .'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <input name="Title" type="text" class="form-control frm_field_string">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Descrizione
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                <textarea name="Description" class="form-control frm_field_string frm_field_required" style="width:40rem;height:4rem"></textarea>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Note
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                <textarea name="Notes" class="form-control frm_field_string" style="width:40rem;height:4rem"></textarea>
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


