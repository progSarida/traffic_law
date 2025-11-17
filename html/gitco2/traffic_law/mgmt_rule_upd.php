<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$a_Lan = unserialize(LANGUAGE);
$CityId = CheckValue('CityId','s');
$RuleTypeId = CheckValue('RuleTypeId','n');

$Search_RuleType = CheckValue('Search_RuleType','s');
$Search_CityId = CheckValue('Search_CityId','s');
$Filter = CheckValue('Filter','n');

$str_GET_Parameter .= "&Search_CityId=$Search_CityId&Search_RuleType=$Search_RuleType";

$rs_Rule = $rs->Select('RuleType', "Id=" . $RuleTypeId. " AND CityId='". $CityId . "'");
$r_Rule = mysqli_fetch_array($rs_Rule);

$rs_Customer = $rs_Rule = $rs->SelectQuery("SELECT ManagerCity FROM Customer WHERE CityId='".$CityId."'");
$r_Customer = mysqli_fetch_array($rs_Customer);

$str_HeadersObjects = '';

for ($i = 1; $i < count($a_Lan); $i++) {
    $str_PrintHeader = StringOutDB($r_Rule["PrintHeader$a_Lan[$i]"]);
    $str_PrintObject = StringOutDB($r_Rule["PrintObject$a_Lan[$i]"]);
    
    $str_HeadersObjects .= '
        <div class="col-sm-12" style="height:6rem">
            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Header <img src="' . IMG . '/' . $aLan[$i] . '" style="width:16px" />
            </div>
            <div class="col-sm-4 BoxRowCaption" style="height:6rem">
                <textarea class="frm_field_required frm_field_string" name="PrintHeader'. $a_Lan[$i] .'" style="height:5.4rem; width:40rem;">'.str_replace("*", PHP_EOL, $str_PrintHeader).'</textarea>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Object <img src="' . IMG . '/' . $aLan[$i] . '" style="width:16px" />
            </div>
            <div class="col-sm-4 BoxRowCaption" style="height:6rem">
                <textarea class="frm_field_required frm_field_string" name="PrintObject'. $a_Lan[$i] .'" style="height:5.4rem; width:40rem;">'.str_replace("*", PHP_EOL, $str_PrintObject).'</textarea>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
    ';
}




$str_out .='
    <div class="col-sm-12">
        <div class="col-sm-12" >
            <form name="f_rule" id="f_rule" action="mgmt_rule_upd_exe.php'.$str_GET_Parameter.'" method="post">
                <input type="hidden" name="CityId" id="CityId" value="'.$CityId.'">
                <input type="hidden" name="RuleTypeId" id="RuleTypeId" value="'.$RuleTypeId.'">
                <div class="col-sm-12 table_label_H" style="text-align:center">
                    Modifica Ruolo
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Ente
                </div>
                <div class="col-sm-3 BoxRowCaption">
                   <span>'.$r_Rule['CityId'].' - '.$r_Customer['ManagerCity'].'</span>
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Genere
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <span>'.$r_Rule['Title'].'</span>
                </div>
                        
                <div class="clean_row HSpace4"></div>
                        
                '. $str_HeadersObjects .'
                    
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                        <input type="submit" class="btn btn-default button" id="save" style="margin-top:1rem;" value="Modifica">
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
	window.location="<?= $str_BackPage.$str_GET_Parameter."&Filter=".$Filter ?>";
});

</script>


<?php
include(INC."/footer.php");


