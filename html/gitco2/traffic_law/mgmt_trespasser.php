<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(INC . "/header.php");
require_once(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

// echo "<pre>"; print_r($_GET); echo "</pre>";

//Determina se si stà arrivando da mgmt_trespasser_city.php
$CityPage = isset($CityPage) ? $CityPage : false;

$Search_VatCode = CheckValue('Search_VatCode','s');
$Search_TrespCode = CheckValue('Search_TrespCode','s');
$Search_Province = CheckValue('Search_Province','s');
$Search_TaxCode = CheckValue('Search_TaxCode','s');
$Search_CityTitle = CheckValue('Search_CityTitle','s');
$Search_Genre = CheckValue('Search_Genre','s');

$Filter = CheckValue('Filter','n');

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
    
}


$strOrder = "TrespasserFullName asc";

$order1 = 'Code asc';
$order2 = 'TrespasserFullName desc';
$link = 'Code asc';
$link1 = 'TrespasserFullName desc';
if(isset($_GET['order1']) && $_GET['order1']!=""){
    $order1 = $_GET['order1'];
    $strOrder = $order1;
    $link = $order1=='Code asc'? $order1 = 'Code desc': $order1 = 'Code asc';
}
if(isset($_GET['order2']) && $_GET['order2']!=""){
    $order2 = $_GET['order2'];
    $strOrder = $order2;
    
    $link1 = $order2=='TrespasserFullName asc'? $order2 = 'TrespasserFullName desc': $order2 = 'TrespasserFullName asc';
    
}
if (!isset($_GET['order2']) && !isset($_GET['order1'])){
    $strOrder = "TrespasserFullName asc";
}

$next = isset($_GET['order1'])? $_GET['order1']:null;
$next1 = isset($_GET['order2'])? $_GET['order2']:null;

if(isset($_GET['page'])){
    $page = $_GET['page'];
} else {
    $page = 1;
}
$min = $page*PAGE_NUMBER-PAGE_NUMBER;

//Se l'utente ha un valore di permessi <=50 oppure $CityPage è vero
if($_SESSION['usertype']<=50 || $CityPage){
    $str_Where.=" AND CustomerId='".$_SESSION['cityid']."' ";
    // $str_Where.=" AND T.CustomerId='".$_SESSION['cityid']."' ";
}

if($Search_VatCode!=""){
    $str_Where .= " AND VatCode LIKE '%". $Search_VatCode ."%'";
    $str_CurrentPage .= "&Search_VatCode=$Search_VatCode";
}

if($Search_TrespCode!=""){
    $str_Where .= " AND Code='". $Search_TrespCode ."'";
    $str_CurrentPage .= "&Search_TrespCode=$Search_TrespCode";
}

if($Search_Province!=""){
    $str_Where .= " AND Province='". $Search_Province ."'";
    $str_CurrentPage .= "&Search_Province=$Search_Province";
}

if($Search_TaxCode!=""){
    $str_Where .= " AND TaxCode LIKE '%". $Search_TaxCode ."%'";
    $str_CurrentPage .= "&Search_TaxCode=$Search_TaxCode";
}

if($Search_CityTitle!=""){
    $Search_CityTitle_sql = mysqli_real_escape_string($rs->conn, $Search_CityTitle);
    $str_Where .= " AND City='". $Search_CityTitle_sql ."'";
    $str_CurrentPage .= "&Search_CityTitle=$Search_CityTitle";
}

if($Search_Genre!=""){
    if($Search_Genre=="PF")
        $str_Where .= " AND Genre <> 'D'";
        if($Search_Genre=="PG")
            $str_Where .= " AND Genre = 'D'";
            $str_CurrentPage .= "&Search_Genre=$Search_Genre";
}

$rs_city = $rs->Select(MAIN_DB.".City","1=1","Title ASC");

$chh_FindFilter = trim($str_Where);

$str_GET_Parameter .= "&Search_VatCode=$Search_VatCode&Search_TrespCode=$Search_TrespCode&Search_Province=$Search_Province&Search_TaxCode=$Search_TaxCode&Search_CityTitle=$Search_CityTitle";

$str_out .= '
    
    
    
<div class="row-fluid">
    <form id="f_Search" action="' . $str_CurrentPage .'&Filter=1" method="post" autocomplete="off">
        <div class="col-sm-12">
            <div class="col-sm-11" style="height:4.6rem; border-right:1px solid #E7E7E7;">
        
                <div class="col-sm-1 BoxRowLabel">
                    ID
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_TrespCode" type="text" style="width:15rem" value="' . $Search_TrespCode . '">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Trasgressore
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_TrespasserFullName" type="text" value="' . $Search_TrespasserFullName . '">
                </div>
                        
                <div class="col-sm-1 BoxRowLabel">
                    C.Fiscale
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_TaxCode" type="text" value="' . $Search_TaxCode . '">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    P.IVA
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_VatCode" type="text" value="' . $Search_VatCode . '">
                </div>
                        
                <div class="clean_row HSpace4"></div>
                 <div class="col-sm-1 BoxRowLabel">
                    Nazionalità
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <select class="form-control" name="TypePlate" id="TypePlate">
                        <option></option>
                        <option value="N"' . $s_SelPlateN . '>Nazionali</option>
                        <option value="F"' . $s_SelPlateF . '>Estere</option>
                    </select>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title", "Search_Country", "CountryId", "Title", $Search_Country, false, 12) . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Provincia
                </div>
                <div class="col-sm-1 BoxRowCaption">
                   ' . CreateSelectExtended(MAIN_DB . ".Province", "1=1", "Title", "Search_Province", "Search_Province", "ShortTitle", "Title", $Search_Province, false, ($s_TypePlate!="F" ? false : true)) . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Città
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <select id="Search_CityTitle" class="form-control" name="Search_CityTitle"'.($s_TypePlate!="F" ? "" : " disabled").'>
                        <option></option>';
while($r_City = mysqli_fetch_array($rs_city)) {
    $str_out .= (strtoupper($r_City['Title']) == strtoupper($Search_CityTitle))
    ? '<option selected value="'.$r_City['Title'].'">'.$r_City['Title'].'</option>'
        : '<option value="'.$r_City['Title'].'">'.$r_City['Title'].'</option>';
}
$str_out .= '</select>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Persona
                </div>
                <div class="col-sm-2 BoxRowCaption">
        	    <select class="form-control" name="Search_Genre" id="Genre">
                        <option></option>';
$str_out .= $Search_Genre=="PF"?'<option value="PF" selected>Fisica</option>':'<option value="PF">Fisica</option>';
$str_out .= $Search_Genre=="PG"?'<option value="PG" selected>Giuridica</option>':'<option value="PG">Giuridica</option>';
$str_out .= '</select>
                </div>
            </div>
            <div class="col-sm-1 BoxRow" style="height:4.6rem;">
                <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                    <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>
                </div>
            </div>
        </div>
    </form>
    
    <div class="col-sm-12">
        <div class="table_label_H col-sm-1">ID Utente
            <a href="'.$str_CurrentPage.'&order1='.$link.'">
                <span class="glyphicon glyphicon-sort SortBtnCodice" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
        <!--<div class="table_label_H col-sm-2">Ente</div>-->
                
        <div class="table_label_H col-sm-1">Genere</div>
                
                
        <div class="table_label_H col-sm-3">Nominativo
            <a href="'.$str_CurrentPage.'&order2='.$link1.'">
                <span class="glyphicon glyphicon-sort SortBtnNom" style="float: right; padding-top: 0.7rem; padding-right: 0.4rem; color: white"></span>
            </a>
        </div>
                
        <div class="table_label_H col-sm-3">Città</div>
        <div class="table_label_H col-sm-3">Paese</div>
        <div class="table_add_button col-sm-1 right">
            ' . ChkButton($aUserButton, 'add', '<a href="mgmt_trespasser_add.php' . $str_GET_Parameter.'&Filter='.$Filter.'"><span class="tooltip-r glyphicon glyphicon-plus-sign add_button" title="Inserisci" data-placement="left" style="margin-right:0.3rem; "></span></a>') . '
        </div>
    </div>
    <div class="clean_row HSpace4"></div>
';

if(($chh_FindFilter=="1=1" && $Filter!=1) || ($CityPage && $Filter!=1)){
    $str_out.= '
    <div class="table_caption_H col-sm-12" style="font-size:2rem;color:orange;text-align: center">
    Inserire criteri ricerca
    </div>
    ';
} else {
    
    $rs_Trespasser = $rs->Select("V_Trespasser",  $str_Where,  $strOrder . " LIMIT ".PAGE_NUMBER." OFFSET ".$min);
    
    $RowNumber = mysqli_num_rows($rs_Trespasser);
    
    
    if ($RowNumber == 0) {
        $str_out .= 'Nessun record presente';
    } else {
        
        
        $a_Genre = array("F"=>"Femmina","M"=>"Maschio","D"=>"Ditta");
        while ($r_Trespasser = mysqli_fetch_array($rs_Trespasser)) {
            
            $rs_CountFineTrespasser = $rs->SelectQuery("SELECT COUNT(*)Tot FROM FineTrespasser WHERE TrespasserId=".$r_Trespasser['Id']);
            $rs_CountFineCommunication = $rs->SelectQuery("SELECT COUNT(*) Tot FROM FineCommunication WHERE TrespasserId=".$r_Trespasser['Id']);
            
            $CountFineTrespasser = mysqli_fetch_array($rs_CountFineTrespasser);
            $CountFineCommunication = mysqli_fetch_array($rs_CountFineCommunication);
            
            $str_out .= '
            <div class="col-sm-12">
                <div class="table_caption_H col-sm-1">' . $r_Trespasser['Code'] . '</div>
                <!--<div class="table_caption_H col-sm-2">' . $r_Trespasser['ManagerCity']. '</div>-->
                    
                <div class="table_caption_H col-sm-1">' . $a_Genre[$r_Trespasser['Genre']]. '</div>
                    
                <div class="table_caption_H col-sm-3">' . StringOutDB($r_Trespasser['TrespasserFullName']) . '</div>
                <div class="table_caption_H col-sm-3">' . $r_Trespasser['City'] . '</div>
                <div class="table_caption_H col-sm-3">' . $r_Trespasser['CountryTitle'] . '</div>
                    
                    
                <div class="table_caption_button col-sm-1">
                    ' . ChkButton($aUserButton, 'viw', '<a href="mgmt_trespasser_viw.php' . $str_GET_Parameter . '&Id=' . $r_Trespasser['Id'] .'&Filter='.$Filter.'"><span class="tooltip-r glyphicon glyphicon-eye-open" title="Visualizza" data-placement="top" style="position:absolute;left:5px;top:5px;"></span></a>') . '
                    ' . ChkButton($aUserButton, 'upd', '<a href="mgmt_trespasser_upd.php' . $str_GET_Parameter . '&Id=' . $r_Trespasser['Id'] .'&Filter='.$Filter.'"><span class="tooltip-r glyphicon glyphicon-pencil" title="Modifica" data-placement="top" style="position:absolute;left:25px;top:5px;"></span></a>');
            if (isset($CountFineTrespasser['Tot']) && isset($CountFineCommunication['Tot'])
                && $CountFineTrespasser['Tot'] == 0 && $CountFineCommunication['Tot'] ==0){
                    $str_out .= ChkButton($aUserButton, 'del', '<a href="mgmt_trespasser_del.php' . $str_GET_Parameter . '&Id=' . $r_Trespasser['Id'] .'&Filter='.$Filter.'"><span class="tooltip-r glyphicon glyphicon-remove-sign" title="Elimina" data-placement="top" style="position:absolute;left:45px;top:5px;"></span></a>');
            }
            
            $str_out .= '</div>
            </div>
            <div class="clean_row HSpace4"></div>
		';
        }
        
    }
    $table_Trespassers_number = $rs->Select('V_Trespasser', $str_Where, 'Id');
    $TrespasserNumberTotal = mysqli_num_rows($table_Trespassers_number);
    
    $str_out .= CreatePagination(PAGE_NUMBER, $TrespasserNumberTotal, $page, $str_CurrentPage."&order1=".$next."&order2=".$next1."&Filter=".$Filter, "");
}

$str_out .= '
</div>
    
<div class="overlay" id="overlay" style="display:none;"></div>
';



echo $str_out;

include(INC . "/module/mod_trespasser.php");
include(INC . "/module/mod_zip.php");




?>
<script type="text/javascript">


$(document).ready(function () {

    <?= require ('inc/jquery/base_search.php')?>

    $(".glyphicon-filter").click(function () {
        $("#f_Search").submit();
    });

    $("#fa_submit").click(function () {
        $("#f_Search").submit();
    });

    $("#TypePlate").change(function () {
        if ($("#TypePlate").val() == "F"){
        	$("#Search_Province").prop("disabled", true);
        	$("#Search_CityTitle").prop("disabled", true);
        } else {
        	$("#Search_Province").prop("disabled", false);
        	$("#Search_CityTitle").prop("disabled", false);
        }
    });

});

</script>
<?php
include(INC . "/footer.php");