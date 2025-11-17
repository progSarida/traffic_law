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

$rs_ForeignCity = $rs->SelectQuery('
    SELECT FC.*, C.Title CountryTitle, L.Title LandTitle
    FROM ForeignCity FC 
    LEFT JOIN Country C ON FC.CountryId=C.Id 
    LEFT JOIN sarida.Land L ON FC.LandId=L.Id 
    WHERE FC.Id='.$Id);
$r_ForeignCity = mysqli_fetch_array($rs_ForeignCity);

$str_out .='
    <div class="col-sm-12">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Visualizza
        </div>
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.StringOutDB($r_ForeignCity['CountryTitle']).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Titolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.StringOutDB($r_ForeignCity['Title']).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                CAP
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.StringOutDB($r_ForeignCity['Zip']).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Land
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.StringOutDB($r_ForeignCity['LandTitle']).'
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                <input type="button" id="back" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
            </div>
        </div>
    </div>';




echo $str_out;
?>

<script type="text/javascript">

$('#back').click(function(){
   window.location="<?= "mgmt_foreigncity.php".$str_GET_Parameter."&Filter=".$Filter ?>"
});

</script>


<?php
include(INC."/footer.php");


