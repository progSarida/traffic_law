<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$Id = CheckValue('Id','n');
$Search_RuleType = CheckValue('Search_RuleType','s');
$Search_CityId = CheckValue('Search_CityId','s');
$Filter = CheckValue('Filter','n');

$str_GET_Parameter .= "&Search_CityId=$Search_CityId&Search_RuleType=$Search_RuleType";

$rs_ViolationType = $rs->Select('ViolationType', "Id=$Id");
$r_ViolationType = mysqli_fetch_array($rs_ViolationType);

$rs_Rule = $rs->Select('Rule', "Id='".$r_ViolationType['RuleTypeId']."'");
$r_Rule = mysqli_fetch_array($rs_Rule);

$RuleTypeId = $r_ViolationType['RuleTypeId'];
$CityId = $_SESSION['cityid'];
$resultNational = "";
$resultForeign = "";

$rs_FormNational = $rs->SelectQuery('
    SELECT distinct f.FormTypeId, f.Title AS TypeTitle
    FROM Form AS f
    WHERE RuleTypeId='.$RuleTypeId.' AND CityId="'.$CityId.'" AND NationalityId=1');

while ($r_FormNational = mysqli_fetch_array($rs_FormNational)){
    $resultNational .= '<option value='.$r_FormNational['FormTypeId'].'>'.$r_FormNational['TypeTitle'].'</option>';
}

$rs_FormForeign = $rs->SelectQuery('
    SELECT distinct f.FormTypeId, f.Title AS TypeTitle
    FROM Form AS f
    WHERE RuleTypeId='.$RuleTypeId.' AND CityId="'.$CityId.'" AND NationalityId<>1');

while ($r_FormForeign = mysqli_fetch_array($rs_FormForeign)){
    $resultForeign .= '<option value='.$r_FormForeign['FormTypeId'].'>'.$r_FormForeign['TypeTitle'].'</option>';
}

$str_out .='
    <div class="col-sm-12">
        <form name="f_violation_type" id="f_violation_type" action="mgmt_violation_type_upd_exe.php'.$str_GET_Parameter.'" method="post">
        <input type="hidden" name="Disabled" value="'.$r_ViolationType['Disabled'].'">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Modifica
        </div>
        <div class="col-sm-6">
            <div class="col-sm-3 BoxRowLabel">
                Id
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <span>'.$Id.'</span>
                <input value="'.$Id.'" name="ViolationTypeId" type="hidden">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Ruolo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <span>'.$r_Rule['Title'].'</span>
                <input value="'.$r_ViolationType['RuleTypeId'].'" name="RuleTypeId" type="hidden">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Documento nazionale associato
            </div>
            <div class="col-sm-9 BoxRowCaption">
               <select'.(($r_ViolationType['NationalFormId'] == 0) ? ' disabled' : '').' id="NationalFormId" class="form-control frm_field_required" name="NationalFormId">'.$resultNational.'</select>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Documento straniero associato
            </div>
            <div class="col-sm-9 BoxRowCaption">
               <select'.(($r_ViolationType['ForeignFormId'] == 0) ? ' disabled' : '').' id="ForeignFormId" class="form-control frm_field_required" name="ForeignFormId">'.$resultForeign.'</select>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <input value="'.StringOutDB($r_ViolationType['Title']).'" name="Title" type="text" class="form-control frm_field_string frm_field_required">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="col-sm-4 BoxRowLabel" style="height:4.4rem;">
                Descrizione
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:4.4rem;">
                <textarea name="Description" class="form-control frm_field_string" style="width:40rem;height:4rem">'.StringOutDB($r_ViolationType['Description']).'</textarea>
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


