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
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Visualizza
        </div>
        <div class="col-sm-6">
            <div class="col-sm-3 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.StringOutDB($r_city['ManagerCity']).'

            </div>
            <div class="col-sm-3 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '.StringOutDB($r_rule['Title']).'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '.$nationality.'
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Lingua
            </div>
            <div class="col-sm-3 BoxRowCaption">
               '.StringOutDB($r_lang['Title']).'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Tipo testo
            </div>
            <div class="col-sm-9 BoxRowCaption">
               '.StringOutDB($r_type['Title']).'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-6 BoxRowCaption">
                '.StringOutDB($r_Keyword['Title']).'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Disabilitato
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.($r_Keyword['Disabled'] ? 'Si' : 'No').'
            </div>
        </div>
        <div class="col-sm-6">
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Descrizione
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                '.StringOutDB($r_Keyword['Description']).'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Note
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                '.StringOutDB($r_Keyword['Notes']).'
            </div>
        </div>
                   
        <div class="clean_row HSpace4"></div>
                   
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                <input type="button" id="back" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
            </div>
        </div>
    </div>
                   
';




echo $str_out;
?>

<script type="text/javascript">

$('#back').click(function(){
   window.location="<?= $str_BackPage.$str_GET_Parameter."&Filter=".$Filter ?>"
});

</script>


<?php
include(INC."/footer.php");


