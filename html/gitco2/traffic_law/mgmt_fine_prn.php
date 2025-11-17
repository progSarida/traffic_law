<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Id = CheckValue('Id', 'n');


$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12">
        <form name="f_print" id="f_print" action="mgmt_fine_prn_exe.php" method="post">
        <input type="hidden" name="FineId" value="' . $Id . '">

        <div class="table_label_H col-sm-1">&nbsp;</div>
        <div class="table_label_H col-sm-6">Nominativo</div>
        <div class="table_label_H col-sm-3">Citta</div>
        <div class="table_label_H col-sm-2">Paese</div>

        <div class="clean_row HSpace4"></div>';


$rs_Trespasser = $rs->Select("V_FineTrespasser", "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND FineId=" . $Id);


while ($r_Trespasser = mysqli_fetch_array($rs_Trespasser)) {
    $str_out .= '
        <input type="hidden" name="CountryId" value="' . $r_Trespasser['VehicleCountryId'] . '">
        <div class="table_caption_H col-sm-3">CRON: ' . $r_Trespasser['ProtocolId'] . '/' . $r_Trespasser['ProtocolYear'] . '</div>
        <div class="table_caption_H col-sm-3">DATA INFRAZIONE: ' . DateOutDB($r_Trespasser['FineDate']) . '</div>
        <div class="table_caption_H col-sm-3">ORA INFRAZIONE: ' . TimeOutDB($r_Trespasser['FineTime']) . '</div>
        <div class="table_caption_H col-sm-3">TARGA VEICOLO: ' . $r_Trespasser['VehiclePlate'] . '</div>
        
        
        <div class="table_caption_H col-sm-3">' . $r_Trespasser['CompanyName'] . ' ' . $r_Trespasser['Surname'] . ' ' . $r_Trespasser['Name'] . '</div>
        <div class="table_caption_H col-sm-4">' . $r_Trespasser['Address'] . '</div>
        <div class="table_caption_H col-sm-3">' . $r_Trespasser['ZIP'] . ' ' . $r_Trespasser['City'] . '</div>
        <div class="table_caption_H col-sm-2">' . $r_Trespasser['CountryTitle'] . '</div>
        <div class="clean_row HSpace4"></div>
    ';
}


$rs_FineNotification = $rs->Select("FineNotification", "FineId=" . $Id);
$Count = mysqli_num_rows($rs_FineNotification);


$str_Reprint = '';


$str_out .= '
<div class="table_caption_H col-sm-12" style="height:256px">
    Lingua:<br />
    <input type="radio" name="LanguageId" value="1" checked> <img src="' . IMG . '/' . $aLan[1] . '" style="position:relative;top:-5px;width:16px;" />&nbsp;
    <input style="margin-left:40px;" type="radio" name="LanguageId" value="2"> <img src="' . IMG . '/' . $aLan[2] . '" style="position:relative;top:-5px;width:16px" />&nbsp;
    <input style="margin-left:40px;" type="radio" name="LanguageId" value="3"> <img src="' . IMG . '/' . $aLan[3] . '" style="position:relative;top:-5px;width:16px" />&nbsp;
    <input style="margin-left:40px;" type="radio" name="LanguageId" value="4"> <img src="' . IMG . '/' . $aLan[4] . '" style="position:relative;top:-5px;width:16px" />&nbsp;
    <input style="margin-left:40px;" type="radio" name="LanguageId" value="5"> <img src="' . IMG . '/' . $aLan[5] . '" style="position:relative;top:-5px;width:16px" />&nbsp;
    <br /><br />
    
    ' . $str_Reprint . '
</div>';

$strButtons = '<input type="submit" class="btn btn-success"  value="Stampa semplice" style="margin-top:1rem;">';

$str_out .= '

    <div class="col-sm-12 table_caption_H" style="height:6rem;text-align:center;line-height:6rem;">
    ' . $strButtons . '    		 		
    </div>
    </form>
	</div>';


$str_out .= '<div>
</div>';


echo $str_out;

include(INC . "/footer.php");
