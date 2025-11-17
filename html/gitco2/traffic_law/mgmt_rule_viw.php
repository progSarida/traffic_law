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
                <span name="PrintHeader'. $a_Lan[$i] .'">'.str_replace("*", "<br/>", $str_PrintHeader).'</span>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Object <img src="' . IMG . '/' . $aLan[$i] . '" style="width:16px" />
            </div>
            <div class="col-sm-4 BoxRowCaption" style="height:6rem">
                <span name="PrintObject'. $a_Lan[$i] .'">'.str_replace("*", "<br/>", $str_PrintObject).'</span>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
    ';
}




$str_out .='
    <div class="col-sm-12">
        <div class="col-sm-12" >
            <div class="col-sm-12 table_label_H" style="text-align:center">
                Visualizza Ruolo
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
                    <input type="button" id="back" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                </div>
            </div>
        </div>
    </div>
                    
';




echo $str_out;
?>

<script type="text/javascript">

$('#back').click(function(){
	window.location="<?= $str_BackPage.$str_GET_Parameter."&Filter=".$Filter ?>";
});

</script>


<?php
include(INC."/footer.php");


