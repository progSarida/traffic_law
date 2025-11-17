<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

echo $str_out;

include(INC . "/module/mod_zip.php");
include(INC . "/module/mod_foreignzip.php");
include(INC . "/module/mod_foreigncity_add.php");

$BackPage = strtok($str_BackPage, '?');

$Search_VatCode = CheckValue('Search_VatCode','s');
$Search_TrespCode = CheckValue('Search_TrespCode','s');
$Search_Province = CheckValue('Search_Province','s');
$Search_TaxCode = CheckValue('Search_TaxCode','s');
$Search_CityTitle = CheckValue('Search_CityTitle','s');
$str_GET_Parameter .= "&Search_VatCode=$Search_VatCode&Search_TrespCode=$Search_TrespCode&Search_Province=$Search_Province&Search_TaxCode=$Search_TaxCode&Search_CityTitle=$Search_CityTitle";

$str_TabForwarding = "";
$str_TabDomicile = "";
$str_TabDwelling = "";

$rs_city = $rs->Select(MAIN_DB.".City","1=1","Title ASC");

//FORWARDING
$n_ForwardingId = 1;
$str_TabForwarding .=
'<div id="ForwardingFields'.$n_ForwardingId.'">
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="forwarding_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                Recapito n. '.$n_ForwardingId.'
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Presso c/o
            </div>
            <div class="col-sm-6 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_Nominative'.$n_ForwardingId.'" name="Forwarding_Nominative[]">
            </div>
            <div class="col-sm-4 BoxRowHTitle">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Forwarding_CountryId[]", "Forwarding_CountryId".$n_ForwardingId, "Id", "Title", "Z000", true, false, 15) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Città
                <span id="Forwarding_FCityAdd'.$n_ForwardingId.'" fieldid="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input id="Forwarding_CityInput'.$n_ForwardingId.'" class="form-control frm_field_required frm_field_string" type="text" name="Forwarding_CityInput[]" style="display:none; width:20rem">
                <select id="Forwarding_ForeignCitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_ForeignCitySelect[]" style="display:none;"></select>
                <input type="hidden" id="Forwarding_LandId'.$n_ForwardingId.'" name="Forwarding_LandId[]">
                <select id="Forwarding_CitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_CitySelect[]">
                     <option></option>';
mysqli_data_seek($rs_city, 0);
while($row = mysqli_fetch_array($rs_city)) {
    $str_TabForwarding .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
}
$str_TabForwarding .='
                </select>
            </div>
            <div id="Forwarding_DIV_Province'.$n_ForwardingId.'">
                <div class="col-sm-1 BoxRowLabel">
                    <span class="col-sm-6">Provincia</span>
                    <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
        </div>
                <div class="col-sm-1 BoxRowCaption">
                    <span id="Forwarding_span_Province'.$n_ForwardingId.'"></span>
                </div>
            </div>
        </div>
                        
        <div class="clean_row HSpace4"></div>
                        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_required frm_field_string" type="text" id="Forwarding_Address'.$n_ForwardingId.'" name="Forwarding_Address[]">
            </div>
            <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                <span id="Forwarding_RoadIcon'.$n_ForwardingId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                Cap
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_ZIP'.$n_ForwardingId.'" name="Forwarding_ZIP[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Civico
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_StreetNumber'.$n_ForwardingId.'" name="Forwarding_StreetNumber[]" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Scala
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Forwarding_Ladder[]" id="Forwarding_Ladder'.$n_ForwardingId.'" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Interno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Forwarding_Indoor[]" id="Forwarding_Indoor'.$n_ForwardingId.'" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Piano
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Forwarding_Plan[]" id="Forwarding_Plan'.$n_ForwardingId.'" style="width:6rem">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_Mail'.$n_ForwardingId.'" name="Forwarding_Mail[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_Phone'.$n_ForwardingId.'" name="Forwarding_Phone[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Fax
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_Fax'.$n_ForwardingId.'" name="Forwarding_Fax[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono 2
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_Phone2'.$n_ForwardingId.'" name="Forwarding_Phone2[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Forwarding_PEC'.$n_ForwardingId.'" name="Forwarding_PEC[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Valido fino al
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" id="Forwarding_ValidUntil'.$n_ForwardingId.'" name="Forwarding_ValidUntil[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                Annotazioni
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height: 6.4rem;">
                <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Forwarding_Notes'.$n_ForwardingId.'" name="Forwarding_Notes[]"></textarea>
            </div>
        </div>
        </div>';

//DOMICILE
$n_DomicileId = 1;
$str_TabDomicile .=
'<div id="DomicileFields'.$n_DomicileId.'">
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="domicile_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                Domicilio n. '.$n_DomicileId.'
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Domicile_CountryId[]", "Domicile_CountryId".$n_DomicileId, "Id", "Title", "Z000", true, false, 15) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Città
                <span id="Domicile_FCityAdd'.$n_DomicileId.'" fieldid="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input id="Domicile_CityInput'.$n_DomicileId.'" class="form-control frm_field_required frm_field_string" type="text" name="Domicile_CityInput[]" style="display:none; width:20rem">
                <select id="Domicile_ForeignCitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_ForeignCitySelect[]" style="display:none;"></select>
                <input type="hidden" id="Domicile_LandId'.$n_DomicileId.'" name="Domicile_LandId[]">
                <select id="Domicile_CitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_CitySelect[]">
                     <option></option>';
mysqli_data_seek($rs_city, 0);
while($row = mysqli_fetch_array($rs_city)) {
    $str_TabDomicile .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
}
$str_TabDomicile .='
                </select>
            </div>
            <div id="Domicile_DIV_Province'.$n_DomicileId.'">
                <div class="col-sm-1 BoxRowLabel">
                    <span class="col-sm-6">Provincia</span>
                    <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <span id="Domicile_span_Province'.$n_DomicileId.'"></span>
                </div>
            </div>
        </div>
                        
        <div class="clean_row HSpace4"></div>
                        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_required frm_field_string" type="text" id="Domicile_Address'.$n_DomicileId.'" name="Domicile_Address[]">
            </div>
            <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                <span id="Domicile_RoadIcon'.$n_DomicileId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                Cap
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_ZIP'.$n_DomicileId.'" name="Domicile_ZIP[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Civico
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_StreetNumber'.$n_DomicileId.'" name="Domicile_StreetNumber[]" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Scala
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Domicile_Ladder[]" id="Domicile_Ladder'.$n_DomicileId.'" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Interno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Domicile_Indoor[]" id="Domicile_Indoor'.$n_DomicileId.'" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Piano
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Domicile_Plan[]" id="Domicile_Plan'.$n_DomicileId.'" style="width:6rem">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_Mail'.$n_DomicileId.'" name="Domicile_Mail[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_Phone'.$n_DomicileId.'" name="Domicile_Phone[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Fax
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_Fax'.$n_DomicileId.'" name="Domicile_Fax[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono 2
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_Phone2'.$n_DomicileId.'" name="Domicile_Phone2[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Domicile_PEC'.$n_DomicileId.'" name="Domicile_PEC[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Valido fino al
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" id="Domicile_ValidUntil'.$n_DomicileId.'" name="Domicile_ValidUntil[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                Annotazioni
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height: 6.4rem;">
                <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Domicile_Notes'.$n_DomicileId.'" name="Domicile_Notes[]"></textarea>
            </div>
        </div>
        </div>';

//TAB_DWELLING
$n_DwellingId = 1;
$str_TabDwelling .=
'<div id="DwellingFields'.$n_DwellingId.'">
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="dwelling_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                Dimora n. '.$n_DwellingId.'
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Dwelling_CountryId[]", "Dwelling_CountryId".$n_DwellingId, "Id", "Title", "Z000", true, false, 15) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Città
                <span id="Dwelling_FCityAdd'.$n_DwellingId.'" fieldid="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input id="Dwelling_CityInput'.$n_DwellingId.'" class="form-control frm_field_required frm_field_string" type="text" name="Dwelling_CityInput[]" style="display:none; width:20rem">
                <select id="Dwelling_ForeignCitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_ForeignCitySelect[]" style="display:none;"></select>
                <input type="hidden" id="Dwelling_LandId'.$n_DwellingId.'" name="Dwelling_LandId[]">
                <select id="Dwelling_CitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_CitySelect[]">
                     <option></option>';
mysqli_data_seek($rs_city, 0);
while($row = mysqli_fetch_array($rs_city)) {
    $str_TabDwelling .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
}
$str_TabDwelling .='
                </select>
            </div>
            <div id="Dwelling_DIV_Province'.$n_DwellingId.'">
                <div class="col-sm-1 BoxRowLabel">
                    <span class="col-sm-6">Provincia</span>
                    <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <span id="Dwelling_span_Province'.$n_DwellingId.'"></span>
                </div>
            </div>
        </div>
                        
        <div class="clean_row HSpace4"></div>
                        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_required frm_field_string" type="text" id="Dwelling_Address'.$n_DwellingId.'" name="Dwelling_Address[]">
            </div>
            <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                <span id="Dwelling_RoadIcon'.$n_DwellingId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                Cap
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_ZIP'.$n_DwellingId.'" name="Dwelling_ZIP[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Civico
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_StreetNumber'.$n_DwellingId.'" name="Dwelling_StreetNumber[]" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Scala
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Dwelling_Ladder[]" id="Dwelling_Ladder'.$n_DwellingId.'" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Interno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Dwelling_Indoor[]" id="Dwelling_Indoor'.$n_DwellingId.'" style="width:6rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Piano
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Dwelling_Plan[]" id="Dwelling_Plan'.$n_DwellingId.'" style="width:6rem">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_Mail'.$n_DwellingId.'" name="Dwelling_Mail[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_Phone'.$n_DwellingId.'" name="Dwelling_Phone[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Fax
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_Fax'.$n_DwellingId.'" name="Dwelling_Fax[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono 2
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_Phone2'.$n_DwellingId.'" name="Dwelling_Phone2[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Dwelling_PEC'.$n_DwellingId.'" name="Dwelling_PEC[]">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Valido fino al
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" id="Dwelling_ValidUntil'.$n_DwellingId.'" name="Dwelling_ValidUntil[]">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                Annotazioni
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height: 6.4rem;">
                <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Dwelling_Notes'.$n_DwellingId.'" name="Dwelling_Notes[]"></textarea>
            </div>
        </div>
        </div>';

?>

<form id="f_trespasser" method="post" action="mgmt_trespasser_add_exe.php<?php echo $str_GET_Parameter."&BackPage=".$BackPage; ?>" enctype="multipart/form-data">
	<input type="hidden" id="Overwrite" name="Overwrite">
	<input type="hidden" id="TrespasserId" name="TrespasserId">
    <div class="row-fluid">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Soggetto
        </div>
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li id="tab_Subject" class="active" ><a href="#Subject" data-toggle="tab">Dati soggetto</a></li>
                <li id="tab_Forwarding"><a href="#Forwarding" data-toggle="tab">Recapito</a></li>
                <li id="tab_Domicile"><a href="#Domicile" data-toggle="tab">Domicilio</a></li>
                <li id="tab_Dwelling"><a href="#Dwelling" data-toggle="tab">Dimora</a></li>
            </ul>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="tab-content">

            <!-- TAB SUBJECT -->
            <div class="tab-pane active" id="Subject">
                <div class="col-sm-12">
                	<div class="col-sm-12">
	                    <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
	                    	<strong>DATI SOGGETTO</strong>
                        </div>
	                    <div class="col-sm-2 BoxRowLabel">
                            Tipologia soggetto
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <div class="col-sm-6">
                                <input type="radio"  value="P" name="Typology" id="checkPerson" checked>
                                <label style="vertical-align: top;"> Persona fisica</label>
                            </div>
                            <div class="col-sm-6">
                                <input type="radio"  value="D" name="Typology" id="checkCompany">
                                <label style="vertical-align: top;"> Ditta</label>
                            </div>
                        </div>
                        <div id="LegalFormLabel" class="col-sm-2 BoxRowLabel">
                            Impresa Individuale
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                        	<?= CreateSelectGroup('LegalForm', '1=1', 'Type', 'CompanyLegalFormId', 'CompanyLegalFormId', 'Id', 'Description', 'Type', '', false, null, 'hidden'); ?>
							<?= CreateSelectGroup('LegalForm', 'Id IN(23,24)', 'Type', 'PersonLegalFormId', 'PersonLegalFormId', 'Id', 'Description', 'Type', '', false); ?>
						</div>
                    <div class="clean_row HSpace4"></div>
                	</div>

                    <div id="PersonData" class="col-sm-12">
	                    <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Sesso
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input type="radio"  value="M" name="Genre" id="sexM" checked>M &nbsp;
                            <input type="radio"  value="F" name="Genre" id="sexF">F
                            <span id="sex_code"></span>
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Cognome
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input data-validate="true" class="text-uppercase form-control frm_field_string frm_field_required" type="text" id="Surname" name="Surname">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Nome
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="text-uppercase form-control frm_field_string frm_field_required" type="text" id="Name" name="Name">
                        </div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                    
                    <div id="CompanyData" class="col-sm-12" style="display:none;">
	                    <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ragione sociale
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="text-uppercase form-control frm_field_string frm_field_required" name="CompanyName" id="CompanyName" type="text">
                        </div>
                        <div class="col-sm-4 BoxRowHTitle"></div>
                        <div class="clean_row HSpace4"></div>
                	</div>
                	
                    <div id="DIV_DeathDate">
                        <div class="col-sm-12">
                        	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data decesso
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_date" type="text" name="DeathDate">
                            </div>
                            <div class="col-sm-7 BoxRowHTitle"></div>
                        </div>
                    </div>
    
                	<div class="clean_row HSpace16"></div>
                	
                	<div id="DIV_BornData">
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DATI NASCITA</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-8 BoxRowCaption">
                            <?= CreateSelectQueryExtended("SELECT Id,Title,UPPER(Title) AS UpperTitle FROM ".MAIN_DB.".Country ORDER BY Title", "BornCountry", "BornCountry", "Id", "Title", array('UpperTitle'), "Z000", false); ?>
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Città
                            <span id="FBornCityAdd" fieldid="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input id="BornCityInput" class="text-uppercase form-control frm_field_string" type="text" name="BornCityInput" style="display:none;">
                            <select id="ForeignBornCitySelect" class="form-control" name="ForeignBornCitySelect" style="display:none;"></select>
                            <?= CreateSelectQueryExtended("SELECT Id,Title,UPPER(Title) AS UpperTitle FROM ".MAIN_DB.".City ORDER BY Title ASC", 'BornCitySelect', 'BornCitySelect', 'Id', 'Title', array('UpperTitle'), null, false); ?>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data Nascita
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate">
                        </div>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    </div>
                    
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                    		<strong>RIFERIMENTI FISCALI</strong>
                    	</div>
                        <div id="DIV_TaxCode">
                            <div class="col-sm-2 BoxRowLabel">
                                <span class="col-sm-9">
                                    C.F
                                </span>
                                <span class="col-sm-3 text-right">
                                    <i id="DisassembleTaxCode" data-container="body" data-toggle="tooltip" data-placement="top" title="Deduci dati di nascita da C.F" class="tooltip-r fa fa-id-card" style="margin-top:0.4rem;margin-right:1rem;font-size:1.4rem;"></i>
                                </span>
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                            	<span id="span_TaxCode"></span>
                                <input class="text-uppercase form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="display:none;">
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                            	Forza C.F
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input type="checkbox" name="ForcedTaxCode" id="ForcedTaxCode">
                            </div>
                        </div>
                        <div id="DIV_CompanyTaxCode" style="display:none;">
                            <div class="col-sm-2 BoxRowLabel">
                                C.F
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="text-uppercase form-control frm_field_string" type="text" name="CompanyTaxCode" id="CompanyTaxCode">
                            </div>
                        </div>
                        <div id="DIV_VatCode" style="display:none;">
                            <div class="col-sm-2 BoxRowLabel">
                                P.IVA
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="VatCode" id="VatCode">
                            </div>
                        </div>
                    </div>
                    
                    <div class="clean_row HSpace16"></div>
                	
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong id="LABEL_Residence">DATI RESIDENZA</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <?php echo CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "TrespasserCountryId", "Id", "Title", "Z000", true); ?>
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Città
                            <span id="FCityAdd" fieldid="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input id="CityInput" class="text-uppercase form-control frm_field_string frm_field_required" type="text" name="CityInput" style="display:none;">
                            <select id="ForeignCitySelect" class="form-control frm_field_required" name="ForeignCitySelect" style="display:none;"></select>
                            <select id="CitySelect" class="form-control frm_field_required" name="CitySelect">
                                 <option></option>
                                 <?php 
                                 mysqli_data_seek($rs_city, 0);
                                 while($row = mysqli_fetch_array($rs_city)) {
                                     echo '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                                 }
                                 ?>
                            </select>
                        </div>
                    	<div id="DIV_Province">
                        <div class="col-sm-1 BoxRowLabel">
                                <span class="col-sm-6">Provincia</span>
                                <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell'associazione tra città e provincia contattare l'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="span_Province"></span>
                        </div>
                        </div>
                    </div>
                    
                  	<div id="DIV_Land" style="display:none;">
                  	<div class="clean_row HSpace4"></div>
                  	
                  	<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Land
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <span id="span_Land"></span>
                            <input type="hidden" id="LandId" name="LandId">
                        </div>
                        <div class="BoxRowHTitle col-sm-4"></div>
                    </div>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="AddressT" name="AddressT">
                        </div>
                        <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                            <span id="RoadIcon" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
                        </div>
                        <div class="BoxRowHTitle col-sm-2"></div>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Civico
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" id="StreetNumber" name="StreetNumber">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Scala
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Ladder" id="Ladder">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Interno
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Indoor" id="Indoor">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Piano
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Plan" id="Plan">
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="ZIP" id="ZIP">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            PEC
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="PEC" id="PEC">
                        </div>
                        <div class="col-sm-3 BoxRowHTitle"></div>
                    </div>
                    
                    <div class="clean_row HSpace16"></div>
                    
                    <div id="DIV_LicenseData">
					<div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DATI PATENTE</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Categoria
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="LicenseCategory">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Numero
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" id="LicenseNumber" name="LicenseNumber" style="width:12rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <?php echo CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "LicenseCountryId", "Id", "Title", "Z000", false, 15); ?>
                        </div>
					</div>
                                
                    <div class="clean_row HSpace4"></div>
                                
					<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data rilascio
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_date" type="text" name="LicenseDate">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ente rilascio
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="LicenseOffice">
                        </div>
                        <div class="col-sm-2 BoxRowHTitle"></div>
					</div>
					
					<div class="clean_row HSpace16"></div>
					
					<div class="col-sm-12">
                       	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DOCUMENTO DI IDENTITÀ</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Tipo di documento
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select class="form-control" name="DocumentTypeId2">
                        		<option value="2">Carta d'identità</option>
                                <option value="3">Passaporto</option>
                                <option value="4">Altro</option>
                            </select>
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <?php echo CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "DocumentCountryId2", "Id", "Title", "Z000", false, 15); ?>
                        </div>
					</div>
					
					<div class="clean_row HSpace4"></div>
					
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            N°
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="text-uppercase form-control frm_field_string" type="text" name="DocumentNumber">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Rilasciato da
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input id="DocumentOfficeInput" class="text-uppercase form-control frm_field_string" type="text" name="DocumentOfficeInput" style="display:none;">
                            <select id="ForeignDocumentOfficeSelect" class="form-control" name="ForeignDocumentOfficeSelect"></select>
                            <select id="DocumentOfficeSelect" class="form-control" name="DocumentOfficeSelect">
                                 <option></option>
                                 <?php 
                                 mysqli_data_seek($rs_city, 0);
                                 while($row = mysqli_fetch_array($rs_city)) {
                                     echo '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                                 }
                                 ?>
                            </select>
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
					
					<div class="clean_row HSpace4"></div>
					
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            In data
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_date" type="text" name="DocumentValidFrom">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Valido fino al
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_date" type="text" name="DocumentValidTo">
                        </div>
                        <div class="col-sm-3 BoxRowHTitle"></div>
                    </div>
					<div class="clean_row HSpace16"></div>
					</div>
             
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                    		<strong>DATI CONTATTO</strong>
                    	</div>
                        <div class="col-sm-2 BoxRowLabel">
                            Mail
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Mail">
                        </div>
                        <div class="col-sm-5 BoxRowHTitle"></div>
                    </div>
                                    
                    <div class="clean_row HSpace4"></div>
                                    
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Fax
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Fax">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Telefono
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Phone">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Telefono 2
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control frm_field_string" type="text" name="Phone2">
                        </div>
                    </div>
                                    
                    <div class="clean_row HSpace4"></div>
                                    
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;height: 6.4rem;"></div>
                        <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                            Annotazioni
                        </div>
                        <div class="col-sm-8 BoxRowCaption" style="height: 6.4rem;">
                            <textarea class="form-control frm_field_string" style="font-weight: bold;height: 5.4rem;" name="Notes"></textarea>
                        </div>
                    </div>
                    
                    <div class="clean_row HSpace16"></div>
                    
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                    		<strong>LINGUA</strong>
                    	</div>
                    	<div class="col-sm-2 BoxRowLabel">
                    		<span class="col-sm-9">Forza cambio lingua</span>
                    		<input class="col-sm-3" name="ForceLanguage" type="checkbox" disabled>
						</div>
                        <div class="col-sm-3 BoxRowCaption">
                            <div id="select_Language">
                                <?php echo CreateSelectExtended('Language', '1=1', 'Id', 'LanguageSelect', 'LanguageSelect', 'Id', 'Title', 1, true, true) ?>
                                <input type="hidden" value="1" name="LanguageId" id="LanguageId">
                            </div>
                        </div>
                        <div id="ZoneIdDiv">
                            <div class="col-sm-2 BoxRowLabel">
                                Zona
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                <span id="span_ZoneId">1 - Europa e aree mediterranee</span>
                                <input value="1" type="hidden" class="form-control" id="ZoneId" name="ZoneId">
                            </div>
                        </div>
            		</div>
                
                </div>
            </div>
    
            <!-- TAB FORWARDING -->
            <div class="tab-pane" id="Forwarding">
                <input type="hidden" id="ForwardingNumber" value="1" name="ForwardingNumber">
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            <?php echo TRESPASSER_CONTACT_INFO; ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:4rem;line-height: 4rem;text-align:center;">
                        Aggiungi recapito
                        <i class="fa fa-caret-up" style="position:absolute;top:3px;right: 10px;font-size: 2rem;display:none" id="forwardingUp"></i>
                        <i class="fa fa-caret-down" style="position:absolute;bottom:3px;right: 10px;font-size: 2rem;" id="forwardingDown"></i>
                    </div>
                </div>
                <?php echo $str_TabForwarding; ?>
            </div>
                 
            <!-- TAB DOMICILE -->   
            <div class="tab-pane" id="Domicile">
            <input type="hidden" id="DomicileNumber" value="1" name="DomicileNumber">
            <div class="col-sm-12">
                <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                    <div class="col-sm-11" style="font-size: 1.2rem;">
                        <?php echo TRESPASSER_CONTACT_INFO; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:4rem;line-height: 4rem;text-align:center;">
                    Aggiungi Domicilio
                    <i class="fa fa-caret-up" style="position:absolute;top:3px;right: 10px;font-size: 2rem;display:none" id="domicileUp"></i>
                    <i class="fa fa-caret-down" style="position:absolute;bottom:3px;right: 10px;font-size: 2rem;" id="domicileDown"></i>
                </div>
            </div>
                <?php echo $str_TabDomicile; ?>
            </div>
                    
            <!-- TAB DWELLING -->
            <div class="tab-pane" id="Dwelling">
            <input type="hidden" id="DwellingNumber" value="1" name="DwellingNumber">
            <div class="col-sm-12">
                <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                    <div class="col-sm-11" style="font-size: 1.2rem;">
                        <?php echo TRESPASSER_CONTACT_INFO; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:4rem;line-height: 4rem;text-align:center;">
                    Aggiungi Dimora
                    <i class="fa fa-caret-up" style="position:absolute;top:3px;right: 10px;font-size: 2rem;display:none" id="dwellingUp"></i>
                    <i class="fa fa-caret-down" style="position:absolute;bottom:3px;right: 10px;font-size: 2rem;" id="dwellingDown"></i>
                </div>
            </div>
                <?php echo $str_TabDwelling; ?>
            </div>
        </div>
                                    
        <div class="clean_row HSpace4"></div>
                                    
    	<div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                <input class="btn btn-default" type="submit" id="btn_Save" value="Salva">
                <button class="btn btn-default" id="back">Indietro</button>
            </div>
        </div>
        
        <div class="col-sm-12 alert alert-warning" id="errorcf" style="display:none;">
        	Il C.F esiste già nel database
        </div>
        <div class="col-sm-12 alert alert-warning" id="errorpiva" style="display:none;">
        	La P.IVA esiste già nel database
        </div>
    </div>
</form>

<?php include(INC . "/footer.php"); ?>

<script src="<?= LIB ?>/codicefiscalejs/dist/codice.fiscale.js"></script>

<script type="text/javascript">

function checkUniqueTrespasser(){
	var Typology = $('input[name="Typology"]:checked').val();
	var Genre = $('input[name="Genre"]:checked').val();
	var Name = "";
	var Surname = "";
	var CompanyName = "";
	var VatCode = "";
	var TaxCode = "";
	var BornDate = "";
	var BornCountry = "";
	var BornCity = "";

	if (Typology == "D"){
		var isIndividualCompany = $("#CompanyLegalFormId option:selected").parent("optgroup").attr("label") == "Impresa individuale";
		VatCode = $("#VatCode").val();
		CompanyName = $("#CompanyName").val();
		if (isIndividualCompany){
			Name = $("#Name").val();
			Surname = $("#Surname").val();
		}
	} else {
		TaxCode = $("#TaxCode").val();
		Name = $("#Name").val();
		Surname = $("#Surname").val();
		BornDate = $("#BornDate").val();
		BornCountry = $("#BornCountry option:selected").val();
		
		//Italia, Austria e Germania hanno combo per selezione città, gli altri stati un input libero
		if (BornCountry == 'Z000' || BornCountry == 'Z102' || BornCountry == 'Z112')
			BornCity = $("#BornCitySelect option:selected").text();
		else
			BornCity = $("BornCityInput").val();
	}
	
    return $.ajax({
        url: 'ajax/checkUniqueTrespasser.php',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {Typology:Typology, VatCode:VatCode, TaxCode:TaxCode, Name:Name, Surname:Surname, CompanyName:CompanyName, Genre:Genre, BornDate:BornDate, BornCountry:BornCountry, BornCity:BornCity},
        error: function (data) {
            console.log(data);
            alert("error");
        }
    });
}

//NOTA: è stata modificata la libreria CodiceFiscaleJS (lib/codicefiscalejs/dist/codice.fiscale.js) in modo da restituire il codice catastale
//e selezionare lo stato/città in base ad esso, invece che la nomenclatura, tenere monitorato
function fillDecodedCFFields(cfData){
    $("input[name=Genre][value="+cfData.gender+"]").prop('checked', true);
    $("#BornDate").val(new Date(cfData.birthday).toLocaleDateString('it-IT', {year: "numeric",month: "2-digit",day: "2-digit"}));
    if (cfData.birthplaceProvincia == 'EE'){
    	//$('#BornCountry option[data-uppertitle="' + cfData.birthplace + '"]').prop("selected", true).change();
		$('#BornCountry').val(cfData.birthplaceId).change();
    } else {
        $("#BornCountry").val('Z000').change();
        $('#BornCitySelect').val(cfData.birthplaceId);
        //$('#BornCitySelect option[data-uppertitle="' + cfData.birthplace + '"]').prop("selected", true);
    }
}

$(document).ready(function(){

	var submitted = false;
	
// 	$('#ForeignCitySelect').select2();
// 	$('#ForeignCitySelect').next(".select2-container").hide();
// 	$('#ForeignBornCitySelect').select2();
// 	$('#ForeignBornCitySelect').next(".select2-container").hide();
// 	$('#Forwarding_ForeignCitySelect1').select2();
// 	$('#Forwarding_ForeignCitySelect1').next(".select2-container").hide();
// 	$('#Domicile_ForeignCitySelect1').select2();
// 	$('#Domicile_ForeignCitySelect1').next(".select2-container").hide();
// 	$('#Dwelling_ForeignCitySelect1').select2();
// 	$('#Dwelling_ForeignCitySelect1').next(".select2-container").hide();

    $('#f_trespasser').bootstrapValidator({
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

            frm_field_date:{
                selector: '.frm_field_date',
                validators: {
                    date: {
                        format: 'DD/MM/YYYY',
                        message: 'Data non valida'
                    }

                }

            },

            VatCode: {
                validators: {
                    regexp: {
                        regexp: '^[0-9]{11}$',
                        message: 'P.IVA non valida'
                    }
                }

            },

            TaxCode: {
            	validators: {
                    regexp: {
                        regexp: '^[a-zA-Z0-9]{16}$',
                        message: 'C.F non valido'
                    }
                }

            },
        }
    }).on('success.form.bv', function(event){
    	event.preventDefault();

    	var validateform = true;
    	var VatCode = $('#VatCode').val()
    	var TaxCode = $('#TaxCode').val()

    	if (!$("#tab_Subject").hasClass("active")){
        	$("#tab_Subject a[data-toggle='tab']").click();
            setTimeout(function(){
            	  $("#btn_Save").click();
              }, 100);
    	}

    	validateform = $("#f_trespasser").data('bootstrapValidator').isValid();
    	console.log(validateform);

    	if (validateform && !submitted){
    	    $.when(checkUniqueTrespasser()).done(function(data){
    		    if (data.Exists == "Not Exists"){
    		    	submitted = true;
    		    	$('#f_trespasser').off('submit').submit();
    		    }
    		    
    		    /*
                if (data.Exists == "Exists"){
                    if (confirm(data.Message + "\nSi desidera aggiornarne le informazioni con quanto inserito?")){
                    	$('#f_trespasser').attr('action', 'mgmt_trespasser_upd_exe.php<?= $str_GET_Parameter ?>');
                    	$('#Overwrite').val(1);
                    	$('#TrespasserId').val(data.TrespasserId);
                    	submitted = true;
        		    	$('#f_trespasser').off('submit').submit();
                    }
                }*/
                
                if (data.Exists == "Exists"){
                	if (confirm(data.Message + "\nProcedere comunque con l'inserimento del trasgressore anche se ne esiste già un'altra versione?")){
        		    	submitted = true;
        		    	$('#f_trespasser').off('submit').submit();
                	} else return false;
                }
                
                if (data.Exists == "Error"){
                    alert("Qualcosa è andato storto");
                }
            });
    	}

    });

    $('#back').click(function(){
        window.location="<?= $BackPage.$str_GET_Parameter ?>"
    });

//DATI SOGGETTO

	//Pulsante per dedurre i dati di nascita tramite C.F (usa libreria codice.fiscale.js)
	$('#DisassembleTaxCode').on('click', function(){
		var cf = $('#TaxCode').val().toUpperCase();
        if(CodiceFiscale.check(cf)){
            var cfData = CodiceFiscale.computeInverse(cf);
			fillDecodedCFFields(cfData);
        } else alert('Codice fiscale non valido.');
	});

	//Cambio Ditta/Persona
	$('input[type=radio][name=Typology]').change(function() {
		var value = $('input[name="Typology"]:checked').val();
		var isIndividualCompany = $("#CompanyLegalFormId option:selected").parent("optgroup").attr("label") == "Impresa individuale";
		var isIndividualPerson = $("#PersonLegalFormId").val() == 23 || $("#PersonLegalFormId").val() == 24;

		if (value=="P"){
			$("#PersonData, #PersonLegalFormId, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate, #DIV_LicenseData").show();
			$("#CompanyData, #DIV_VatCode, #DIV_CompanyTaxCode").hide();
			$("#CompanyLegalFormId").addClass('hidden');
			$("#LegalFormLabel").text("Impresa Individuale");
			$("#LABEL_Residence").text("DATI RESIDENZA");
			if(isIndividualPerson) 
				$("#DIV_VatCode").show(); 
			else 
				$("#DIV_VatCode").hide();
		} else if (value=="D"){
			$("#CompanyData, #DIV_VatCode, #DIV_CompanyTaxCode").show();
			$("#CompanyLegalFormId").removeClass('hidden');
			$("#PersonLegalFormId, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate, #DIV_LicenseData").hide();
			$("#LegalFormLabel").text("Forma Giuridica");
			if(isIndividualCompany){
				$("#LABEL_Residence").text("DATI RESIDENZA");
				$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").show();
				$("#DIV_CompanyTaxCode").hide();
			} else {
				$("#LABEL_Residence").text("DATI SEDE");
				$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").hide();
				$("#DIV_CompanyTaxCode").show();
			}
		}

	});

	//Azioni ditta forma giuridica se "impresa individuale" è selezionata
    $('#CompanyLegalFormId').change( function(){
    	var isIndividualCompany = $("#CompanyLegalFormId option:selected").parent("optgroup").attr("label") == "Impresa individuale";
        if (isIndividualCompany){
        	$("#LABEL_Residence").text("DATI RESIDENZA");
        	$("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").show();
        	$("#DIV_CompanyTaxCode").hide();
        } else {
        	$("#LABEL_Residence").text("DATI SEDE");
            $("#PersonData, #DIV_TaxCode, #DIV_BornData, #DIV_DeathDate").hide();
            $("#DIV_CompanyTaxCode").show();
        }
    });

	//Azioni persona forma giuridica se "lavoratore autonomo" o "libero professionista" è selezionata
    $('#PersonLegalFormId').change( function(){
    	var isIndividualPerson = $("#PersonLegalFormId").val() == 23 || $("#PersonLegalFormId").val() == 24;
        if (isIndividualPerson){
        	$("#DIV_VatCode").show();
        } else $("#DIV_VatCode").hide();
    });

	//Cambia la nazione in Italia in dati patente se la nazione di residenza è Italia
    $("#TrespasserCountryId").change(function () {
        if ($(this).val() == "Z000") 
            $("#LicenseCountryId").val("Z000");
    });

	//Se la nazioni di residenza o nascita sono Italiane, mostra selezione provincia, se Austria o Germania mostra selezione città straniere, altrimenti campo di testo libero
    $("#BornCountry, #TrespasserCountryId, #DocumentCountryId2").change(function () {
        if ($(this).attr("id")=="BornCountry"){
            if ($(this).val() == "Z000"){
            	$("#BornCityInput").hide();
            	$("#ForeignBornCitySelect").hide();
            	$('#ForeignBornCitySelect').next(".select2-container").hide();
            	$("#BornCitySelect").show();
            	$("#FBornCityAdd").hide();
            	$("#FBornCityAdd").attr('fieldid', '').attr('country', '');
            } else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            	$("#BornCityInput").hide();
            	$("#ForeignBornCitySelect").show();
            	$('#ForeignBornCitySelect').select2();
            	$("#BornCitySelect").hide();
            	$("#FBornCityAdd").show();
            	$("#FBornCityAdd").attr('fieldid', 'BornCountry').attr('country', $(this).val());
            } else {
            	$("#BornCityInput").show();
            	$("#ForeignBornCitySelect").hide();
            	$('#ForeignBornCitySelect').next(".select2-container").hide();
            	$("#BornCitySelect").hide();
            	$("#FBornCityAdd").show();
            	$("#FBornCityAdd").attr('fieldid', 'BornCityInput').attr('country', $(this).val());
            }
        }
        if ($(this).attr("id")=="TrespasserCountryId"){
            if ($(this).val() == "Z000"){
            	$("#CityInput").hide();
            	$("#ForeignCitySelect, #DIV_Land").hide();
            	$('#ForeignCitySelect').next(".select2-container").hide();
            	$("#CitySelect, #DIV_Province").show();
            	$("#FCityAdd").hide();
            	$("#FCityAdd").attr('fieldid', '').attr('country', '');
            } else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            	$("#CityInput").hide();
            	$("#span_Land").html("");
            	$("#ForeignCitySelect, #DIV_Land").show();
            	$('#ForeignCitySelect').select2();
            	$("#CitySelect, #DIV_Province").hide();
            	$("#FCityAdd").show();
            	$("#FCityAdd").attr('fieldid', 'TrespasserCountryId').attr('country', $(this).val());
            } else {
            	$("#CityInput").show();
            	$("#ForeignCitySelect, #DIV_Land").hide();
            	$('#ForeignCitySelect').next(".select2-container").hide();
            	$("#CitySelect, #DIV_Province").hide();
            	$("#FCityAdd").show();
            	$("#FCityAdd").attr('fieldid', 'CityInput').attr('country', $(this).val());
            }
        }
        if ($(this).attr("id")=="DocumentCountryId2"){
            if ($(this).val() == "Z000"){
            	$("#DocumentOfficeInput").hide();
            	$("#ForeignDocumentOfficeSelect").hide();
            	$("#DocumentOfficeSelect").show();
            } else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
            	$("#DocumentOfficeInput").hide();
            	$("#ForeignDocumentOfficeSelect").show();
            	$("#DocumentOfficeSelect").hide();
            } else {
            	$("#DocumentOfficeInput").show();
            	$("#ForeignDocumentOfficeSelect").hide();
            	$("#DocumentOfficeSelect").hide();
            }
        }

		if ($(this).val() == "Z102" || $(this).val() == "Z112"){
	        var CountryId = $(this).val();
	        var ElementId = $(this).attr("id");
            
	        $.ajax({
	            url: 'ajax/ajx_get_foreignCities.php',
	            type: 'POST',
	            dataType: 'json',
	            data: {CountryId:CountryId},
	            success: function (data) {
		            console.log(data);
	            	if (ElementId=="TrespasserCountryId")
	            		$('#ForeignCitySelect').html(data.Options);
	            	if (ElementId=="BornCountry")
	            		$('#ForeignBornCitySelect').html(data.Options);
	            	if (ElementId=="DocumentCountryId2")
	            		$('#ForeignDocumentOfficeSelect').html(data.Options);
	            },
	            error: function (result) {
	                console.log(result);
	                alert("error: " + result.responseText);
	            }
	        });
		}

    });

	//Prende il ZoneId e LanguageId
    $("#TrespasserCountryId").change(function () {
        var CountryId = $(this).val();
        
        if (CountryId != "Z000"){
        	$('input[name="ForceLanguage"]').prop("disabled", false);
        }
    	else {
    		$('input[name="ForceLanguage"]').prop("checked", false);
    		$('input[name="ForceLanguage"]').prop("disabled", true);
    		$('input[name="ForceLanguage"]').change();
    	}

            
        $.ajax({
            url: 'ajax/ajx_get_zoneId.php',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {CountryId:CountryId},
            success: function (data) {
            	$("#span_ZoneId").html(data.ZoneId + " - " + data.ZoneDescription);
            	//$("#span_Language").html(data.Language);
            	$("#ZoneId").val(data.ZoneId);
            	$("#LanguageSelect").val(data.LanguageId);
            	$("#LanguageId").val(data.LanguageId);
            },
        });
    });

	//Scrive il LanguageId nel campo nascosto a ogni cambio lingua
    $("#LanguageSelect").change(function () {
    	$("#LanguageId").val($(this).val());
    });

	//Genera il CF
    $("#BornCountry, #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #TrespasserCountryId").on('blur change', function(){

        var Surname = $('#Surname').val();
        var Name = $('#Name').val();
        var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
        var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");

        var BornDate = $('#BornDate').val();
        var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();
        //console.log(Surname, Name, Sex, BornDate, BornCitySelect);

        if (!ForcedTaxCode) {
            if (Surname && Name && Sex && BornDate && BornCitySelect) {
                var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);

                if (TaxCode.length == 16){
                    $('#TaxCode').val(TaxCode);
                    $('#span_TaxCode').html(TaxCode);
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-success');
                } else {
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                }

            }
        }
    });

    //Forza il CF
    $('#ForcedTaxCode').change(function () {
    	var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");

    	if (ForcedTaxCode){
            var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
            
    		$('#TaxCode').show();
    		$('#span_TaxCode').hide();
    		$('#ForcedTaxCode').val(Sex);
    	} else {
            var Surname = $('#Surname').val();
            var Name = $('#Name').val();
            var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
            var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");
            var BornDate = $('#BornDate').val();
            var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();

            if (Surname && Name && Sex && BornDate && BornCitySelect) {
                var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);

                if (TaxCode.length == 16){
                    $('#TaxCode').val(TaxCode);
                    $('#span_TaxCode').html(TaxCode);
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-success');
                } else {
                    $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                }

            }
	$('#TaxCode').hide();
    		$('#span_TaxCode').show();
    		$('#ForcedTaxCode').val('');
    	}
    });

    //Valorizza nel caso venga cambiato il genere forzato se "Forza C.f" è selezionato
    $('#sexM, #sexF').change(function () {
    	var Sex = $('#sexM').prop('checked') ? 'M' : 'F';
    	var ForcedTaxCode = $('#ForcedTaxCode').prop("checked");

    	if (ForcedTaxCode)
    		$('#ForcedTaxCode').val(Sex);
    });
	
	//Genera il cap
    $("#CityInput, #ForeignCitySelect, #BornCitySelect, #CitySelect, #AddressT, #StreetNumber").change(function () {

        var str_FieldNaneId = $(this).attr("id");
        var Type = "";

        if(str_FieldNaneId=="BornCitySelect") $('#BornCity').val($("#"+str_FieldNaneId+" option:selected" ).text());
        else {
            var CityId="";
            if($("#CitySelect").is(':visible')){
                $('#City').val($("#"+str_FieldNaneId+" option:selected" ).text());
                CityId =  $('#CitySelect').val();
            } else if ($("#CityInput").is(':visible')) {
                CityId =  $('#CityInput').val();
            } else if ($("#ForeignCitySelect").is(':visible')) {
                CityId =  $('#ForeignCitySelect').val();
                Type = "Foreign";
            }

            var Address = $('#AddressT').val();
            var CountryId = $('#TrespasserCountryId').val();
            var StreetNumber = $('#StreetNumber').val();

            if(CityId!=""){

                $.ajax({
                    url: 'ajax/ajx_src_zip.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId:CityId, Address:Address, Type:Type},
                    success: function (data) {
                        if (CountryId == "Z102" || CountryId == "Z112"){
                        	$("#span_Land").html(data.LandTitle);
                        	$("#LandId").val(data.LandId);
                        }
                        if(data.ZIP !="" ){
                            $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#ZIP").val(data.ZIP);
                        } else $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                    },
                    error: function (data) {
                    	console.log(data);
                    }
                });

            } else {
            	$("#ZIP").val('').removeClass('txt-success txt-warning txt-danger');
            	$("#LandId").val('');
            	$("#span_Land").html('');
            }
        }

        if(str_FieldNaneId=="CitySelect") {
            var CityId = $(this).val();
            $.ajax({
                url: 'ajax/ajx_src_prov_shortTitle.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {CityId:CityId},
                success: function (data) {
                    $("#span_Province").html(data.Province);
                },
                error: function (data) {
                	console.log(data);
                }
            });
        }
    });

    //Rimuove bordo rosso se cap compilato
    $("#ZIP").change(function () {
    	$("#ZIP").removeClass('txt-danger');
    });

    //Abilita il campo lingua
    $('input[name="ForceLanguage"]').change(function () {
        if ($(this).is(':checked'))
        	$("#LanguageSelect").prop("disabled", false);
        else
        	$("#LanguageSelect").prop("disabled", true);
    });

    //Controlla il numero patente
    $("#LicenseNumber, #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #BornCityInput").change(function () {
    	var Genre = $('input[name="Genre"]:checked').val();
    	var Name = "";
    	var Surname = "";
    	var BornDate = "";
    	var BornCity = "";
    	var BornCountry = "";
    	var LicenseNumber = "";

		Name = $("#Name").val();
		Surname = $("#Surname").val();
		BornDate = $("#BornDate").val();
		LicenseNumber = $("#LicenseNumber").val();
		BornCountry = $("#BornCountry option:selected").val();
		
		//Italia, Austria e Germania hanno combo per selezione citt�, gli altri stati un input libero
		if (BornCountry == 'Z000' || BornCountry == 'Z102' || BornCountry == 'Z112')
			BornCity = $("#BornCitySelect option:selected").text();
		else
			BornCity = $("BornCityInput").val();

		if (LicenseNumber != ""){
	        $.ajax({
	            url: 'ajax/checkLicenseNumber.php',
	            type: 'POST',
	            dataType: 'json',
	            cache: false,
	            data: {Name:Name, Surname:Surname, Genre:Genre, BornDate:BornDate, BornCity:BornCity, LicenseNumber:LicenseNumber},
	            success: function (data) {
	            	if (data.Exists == "Exists"){
	                	alert(data.Message);
	            	}
	            },
	            error: function (data) {
	                console.log(data);
	                alert("error: " + data.responseText);
	            }
	        });
		}
    });

    //Controlla lunghezza totale indirizzo (per stampe)
    $("#AddressT, #StreetNumber, #Ladder, #Indoor, #Plan").change(function () {
        var Address = 
            ($("#AddressT").val().length > 0 ? $("#AddressT").val() : "") +
    		($("#StreetNumber").val().length > 0 ? " " + $("#StreetNumber").val() : "") +
			($("#Ladder").val().length > 0 ? " " + $("#Ladder").val() : "") +
			($("#Indoor").val().length > 0 ? " " + $("#Indoor").val() : "") +
			($("#Plan").val().length > 0 ? " " + $("#Plan").val() : "");
        if (Address.length > 46) 
            alert("Attenzione: l'indirizzo potrebbe risultare troncato su eventuali stampe in quanto non deve eccedere 46 caratteri (spazi compresi). Spazi contati: " + Address.length + " \n\n" + Address);
    });


//INTERAZIONI MODALE STRADARIO

	//Stili frecce frazionamento
    $(document).on("mouseenter", ".glyphicon-road, .glyphicon-plus-sign, .fa-id-card", function(){
        $(this).css("color","#2684b1");
        $(this).css("cursor","pointer");
    }).on("mouseleave",".glyphicon-road, .glyphicon-plus-sign, .fa-id-card",  function(){
        $(this).css("color","#fff");
        $(this).css("cursor","");
    });

    //Popola Lo stradario con la città di residenza in base all'icona cliccata
	$(document).on("click", "#RoadIcon, [id^=Forwarding_RoadIcon], [id^=Domicile_RoadIcon], [id^=Dwelling_RoadIcon]", function () {
		var id = $(this).attr('id');
		var nid = id.slice(id.length - 1);
		
		if (id.includes('Forwarding')){
			if ($("#Forwarding_CountryId" + nid).val() == "Z000"){
				if($("#Forwarding_CitySelect" + nid).val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#Forwarding_CitySelect" + nid).val());
			        $('#city_title').html($( "#Forwarding_CitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#Forwarding_CountryId" + nid).val() == "Z102" || $("#Forwarding_CountryId" + nid).val() == "Z112"){
				if($("#Forwarding_ForeignCitySelect" + nid).val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#Forwarding_ForeignCitySelect" + nid).val());
			        $('#foreign_city_title').html($( "#Forwarding_ForeignCitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
			
		} else if (id.includes('Domicile')){
			if ($("#Domicile_CountryId" + nid).val() == "Z000"){
				if($("#Domicile_CitySelect" + nid).val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#Domicile_CitySelect" + nid).val());
			        $('#city_title').html($( "#Domicile_CitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#Domicile_CountryId" + nid).val() == "Z102" || $("#Domicile_CountryId" + nid).val() == "Z112"){
				if($("#Domicile_ForeignCitySelect" + nid).val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#Domicile_ForeignCitySelect" + nid).val());
			        $('#foreign_city_title').html($( "#Domicile_ForeignCitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
			
		} else if (id.includes('Dwelling')){
			if ($("#Dwelling_CountryId" + nid).val() == "Z000"){
				if($("#Dwelling_CitySelect" + nid).val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#Dwelling_CitySelect" + nid).val());
			        $('#city_title').html($( "#Dwelling_CitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#Dwelling_CountryId" + nid).val() == "Z102" || $("#Dwelling_CountryId" + nid).val() == "Z112"){
				if($("#Dwelling_ForeignCitySelect" + nid).val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#Dwelling_ForeignCitySelect" + nid).val());
			        $('#foreign_city_title').html($( "#Dwelling_ForeignCitySelect" + nid + " option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
			
		} else {
			if ($("#TrespasserCountryId").val() == "Z000"){
				if($("#CitySelect").val() != ""){
					$('#new_modal').modal('show');
			        $('#city_id').val($("#CitySelect").val());
			        $('#city_title').html($( "#CitySelect option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else if ($("#TrespasserCountryId").val() == "Z102" || $("#TrespasserCountryId").val() == "Z112"){
				if($("#ForeignCitySelect").val() != ""){
					$('#new_modal_foreign').modal('show');
			        $('#foreign_city_id').val($("#ForeignCitySelect").val());
			        $('#foreign_city_title').html($( "#ForeignCitySelect option:selected" ).text());
				} else alert ("Selezionare la città per aprire lo stradario");
			} else {
				alert("Stradario non disponibile per questa nazione");
			}
		}
	});

//INTEREZIONI MODALE CITTà STRANIERE
	$(document).on("click", ".add_fcity", function(){
		$('#mod_foreigncity_add').modal('show');
		$('#mod_foreigncity_add').attr('fieldid', $(this).attr('fieldid'));
		$('#fcity_CountryId').val($(this).attr('country'));
		$('#fcity_CountryId').change();
	});

    $( "#f_foreigncity_add" ).on( "submitted", function( event, data ) {
        var FieldId = "#" + $('#mod_foreigncity_add').attr('fieldid')
    	$("#f_foreigncity_add").trigger("reset");
    	$("#fcity_LandId").html('<option></option>');
    	$("#fcity_LandId").prop("disabled", true);
    	
    	if ($(FieldId).is("select")){
    		$(FieldId).change();
    	} else {
    		$(FieldId).val(data.CityTitle).change();
    	}
	});
    
    
//DATI CONTATTI

    //Recapiti
    var nF = parseInt($("#ForwardingNumber").val());
    var saved_nF = parseInt($("#ForwardingNumber").val());

    $("#forwardingDown").click(function () {
        var emptyValidUntil = false;
        var emptyFields = false;
        
        $('input[name^="Forwarding_ValidUntil"]').each( function() {
        	if (this.value == "") emptyValidUntil = true;
	    });

        $('[name^="Forwarding_Address"], [name^="Forwarding_CitySelect"]:visible, [name^="Forwarding_CityInput"]:visible').each( function() {
        	if (this.value == "") emptyFields = true;
        });

        if (!emptyFields){
    	    if (!emptyValidUntil){
    	    	nF ++;
                var clone = $("#ForwardingFields1").clone()
                
                clone.attr("id","ForwardingFields" + nF);
                clone.find("[id]").each(function( index ) {
                    this.id = this.id.slice(0,-1) + nF;
                });
                clone.find('input:checkbox').each(function( index ) {
                    var name = $(this).attr('name').replace("[0]", "["+(nF-1)+"]");
                    $(this).attr('name', name);
                });
                clone.find("#deleteForwarding1").remove();
                //Attributi della select2 clonata da rimuovere per poterla rigenerare
                clone.find("#Forwarding_ForeignCitySelect" + nF).removeAttr('data-select2-id').removeClass('select2-hidden-accessible');
                
                $("#Forwarding").append(clone);

                $("#ForwardingFields" + nF + " :input").prop('disabled', false);
                $("#ForwardingFields" + nF + " .forwarding_number").html("Recapito n. " + nF);
                $("#ForwardingFields" + nF + " input").val("");
                $("#ForwardingFields" + nF + " select").val("");
                $("#ForwardingFields" + nF + " #Forwarding_CountryId" + nF).val("Z000");
                $("#ForwardingFields" + nF + " textarea").val("");
                $("#ForwardingNumber").val(nF);
                $("#Forwarding_CityInput" + nF).hide();
                $("#Forwarding_ForeignCitySelect" + nF).hide();
                //Rimuove la select2 clonata
                $('#Forwarding_ForeignCitySelect' + nF).next(".select2-container").remove();
                $("#Forwarding_CitySelect" + nF).show();
                $("#Forwarding_DIV_Province" + nF).show();
                $("#Forwarding_span_Province" + nF).html("");
                $("#Forwarding_FCityAdd" + nF).hide();
                $("#forwardingUp").show();
            } else alert("Compilare la fine validità dei recapiti già inseriti per poterne inserire uno nuovo");
        } else alert("Compilare i campi richiesti per inserire un nuovo recapito");
        
    });

    $("#forwardingUp").click(function () {
        $("#ForwardingFields" + nF).remove();
        nF--;
        $("#ForwardingNumber").val(nF);
        if (nF > 1) $("#forwardingUp").show();
        else $("#forwardingUp").hide();
    });
	//

	//Domicili
	var nDo = parseInt($("#DomicileNumber").val());
    var saved_nDo = parseInt($("#DomicileNumber").val());

    $("#domicileDown").click(function () {
    	 var emptyValidUntil = false;
         var emptyFields = false;
         
         $('input[name^="Domicile_ValidUntil"]').each( function() {
         	if (this.value == "") emptyValidUntil = true;
 	    });

         $('[name^="Domicile_Address"], [name^="Domicile_CitySelect"]:visible, [name^="Domicile_CityInput"]:visible').each( function() {
         	if (this.value == "") emptyFields = true;
         });

         if (!emptyFields){
     	    if (!emptyValidUntil){
             	nDo ++;
                 var clone = $("#DomicileFields1").clone()
                 
                 clone.attr("id","DomicileFields" + nDo);
                 clone.find("[id]").each(function( index ) {
                     this.id = this.id.slice(0,-1) + nDo;
                 });
                 clone.find("#deleteDomicile1").remove();
                 //Attributi della select2 clonata da rimuovere per poterla rigenerare
                 clone.find("#Domicile_ForeignCitySelect" + nDo).removeAttr('data-select2-id').removeClass('select2-hidden-accessible');
                 
                 $("#Domicile").append(clone);
                 
                 $("#DomicileFields" + nDo + " :input").prop('disabled', false);
                 $("#DomicileFields" + nDo + " .domicile_number").html("Domicilio n. " + nDo);
                 $("#DomicileFields" + nDo + " input").val("");
                 $("#DomicileFields" + nDo + " select").val("");
                 $("#DomicileFields" + nDo + " #Domicile_CountryId" + nDo).val("Z000");
                 $("#DomicileFields" + nDo + " textarea").val("");
                 $("#DomicileNumber").val(nDo);
                 $("#Domicile_CityInput" + nDo).hide();
                 $("#Domicile_ForeignCitySelect" + nDo).hide();
                 //Rimuove la select2 clonata
                 $('#Domicile_ForeignCitySelect' + nDo).next(".select2-container").remove();
                 $("#Domicile_CitySelect" + nDo).show();
                 $("#Domicile_DIV_Province" + nDo).show();
                 $("#Domicile_span_Province" + nDo).html("");
                 $("#Domicile_FCityAdd" + nDo).hide();
                 $("#domicileUp").show();
     	    } else alert("Compilare la fine validità dei domicili già inseriti per poterne inserire uno nuovo");
         } else alert("Compilare i campi richiesti per inserire un nuovo domicilio");
         
    });

    $("#domicileUp").click(function () {
        $("#DomicileFields" + nDo).remove();
        nDo--;
        $("#DomicileNumber").val(nDo);
        if (nDo > 1) $("#domicileUp").show();
        else $("#domicileUp").hide();
    });
    //
    
    //Dimore
    var nDw = parseInt($("#DwellingNumber").val());
    var saved_nDw = parseInt($("#DwellingNumber").val());

    $("#dwellingDown").click(function () {
    	  var emptyValidUntil = false;
          var emptyFields = false;
          
          $('input[name^="Dwelling_ValidUntil"]').each( function() {
          	if (this.value == "") emptyValidUntil = true;
       		 });
    
          $('[name^="Dwelling_Address"], [name^="Dwelling_CitySelect"]:visible, [name^="Dwelling_CityInput"]:visible').each( function() {
          	if (this.value == "") emptyFields = true;
          });
    
          if (!emptyFields){
      	    if (!emptyValidUntil){
                  nDw ++;
                  var clone = $("#DwellingFields1").clone()
                  
                  clone.attr("id","DwellingFields" + nDw);
                  clone.find("[id]").each(function( index ) {
                      this.id = this.id.slice(0,-1) + nDw;
                  });
                  clone.find("#deleteDwelling1").remove();
                  //Attributi della select2 clonata da rimuovere per poterla rigenerare
                  clone.find("#Dwelling_ForeignCitySelect" + nDw).removeAttr('data-select2-id').removeClass('select2-hidden-accessible');
                  
                  $("#Dwelling").append(clone);
    
                  $("#DwellingFields" + nDw + " :input").prop('disabled', false);
                  $("#DwellingFields" + nDw + " .dwelling_number").html("Dimora n. " + nDw);
                  $("#DwellingFields" + nDw + " input").val("");
                  $("#DwellingFields" + nDw + " select").val("");
                  $("#DwellingFields" + nDw + " #Dwelling_CountryId" + nDw).val("Z000");
                  $("#DwellingFields" + nDw + " textarea").val("");
                  $("#DwellingNumber").val(nDw);
                  $("#Dwelling_CityInput" + nDw).hide();
                  $("#Dwelling_ForeignCitySelect" + nDw).hide();
                  //Rimuove la select2 clonata
                  $('#Dwelling_ForeignCitySelect' + nDw).next(".select2-container").remove();
                  $("#Dwelling_CitySelect" + nDw).show();
                  $("#Dwelling_DIV_Province" + nDw).show();
                  $("#Dwelling_span_Province" + nDw).html("");
                  $("#Dwelling_FCityAdd" + nDw).hide();
                  $("#dwellingUp").show();
      	    } else alert("Compilare la fine validità delle dimore già inserite per poterne inserire una nuova");
          } else alert("Compilare i campi richiesti per inserire una nuova dimora");
    });

    $("#dwellingUp").click(function () {
        $("#DwellingFields" + nDw).remove();
        nDw--;
        $("#DwellingNumber").val(nDw);
        if (nDw > 1) $("#dwellingUp").show();
        else $("#dwellingUp").hide();
    });
    //

    //CONTROLLO NAZIONE PER CITTà
    $("#Forwarding, #Domicile, #Dwelling").on("change", "[id^=Forwarding_CountryId], [id^=Domicile_CountryId], [id^=Dwelling_CountryId]", function(){
        var id = $(this).attr("id");

        if (id.includes('Forwarding_CountryId')){
        	id = id.replace('Forwarding_CountryId','');
        	if ($(this).val() == "Z000"){
        		$("#Forwarding_CitySelect" + id).show();
        		$("#Forwarding_ForeignCitySelect" + id).hide();
        		$('#Forwarding_ForeignCitySelect' + id).next(".select2-container").hide();
        		$("#Forwarding_CityInput" + id).hide();
        		$("#Forwarding_DIV_Province" + id).show();
        		$("#Forwarding_FCityAdd" + id).hide();
        		$("#Forwarding_FCityAdd" + id).attr('fieldid', '').attr('country', '');
        	} else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
        		$("#Forwarding_CitySelect" + id).hide();
        		$("#Forwarding_ForeignCitySelect" + id).show();
        		$("#Forwarding_ForeignCitySelect" + id).select2();
        		$("#Forwarding_CityInput" + id).hide();
        		$("#Forwarding_DIV_Province" + id).hide();
        		$("#Forwarding_FCityAdd" + id).show();
        		$("#Forwarding_FCityAdd" + id).attr('fieldid', 'Forwarding_CountryId' + id).attr('country', $(this).val());
            } else {
        		$("#Forwarding_CitySelect" + id).hide();
        		$("#Forwarding_ForeignCitySelect" + id).hide();
        		$('#Forwarding_ForeignCitySelect' + id).next(".select2-container").hide();
        		$("#Forwarding_CityInput" + id).show();
        		$("#Forwarding_DIV_Province" + id).hide();
        		$("#Forwarding_FCityAdd" + id).show();
        		$("#Forwarding_FCityAdd" + id).attr('fieldid', 'Forwarding_CityInput' + id).attr('country', $(this).val());
        	}
        } else if (id.includes('Domicile_CountryId')) {
        	id = id.replace('Domicile_CountryId','');
        	if ($(this).val() == "Z000"){
        		$("#Domicile_CitySelect" + id).show();
        		$("#Domicile_ForeignCitySelect" + id).hide();
        		$('#Domicile_ForeignCitySelect' + id).next(".select2-container").hide();
        		$("#Domicile_CityInput" + id).hide();
        		$("#Domicile_DIV_Province" + id).show();
        		$("#Domicile_FCityAdd" + id).hide();
        		$("#Domicile_FCityAdd" + id).attr('fieldid', '').attr('country', '');
        	} else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
        		$("#Domicile_CitySelect" + id).hide();
        		$("#Domicile_ForeignCitySelect" + id).show();
        		$("#Domicile_ForeignCitySelect" + id).select2();
        		$("#Domicile_CityInput" + id).hide();
        		$("#Domicile_DIV_Province" + id).hide();
        		$("#Domicile_FCityAdd" + id).show();
        		$("#Domicile_FCityAdd" + id).attr('fieldid', 'Domicile_CountryId' + id).attr('country', $(this).val());
            } else {
        		$("#Domicile_CitySelect" + id).hide();
        		$("#Domicile_ForeignCitySelect" + id).hide();
        		$('#Domicile_ForeignCitySelect' + id).next(".select2-container").hide();
        		$("#Domicile_CityInput" + id).show();
        		$("#Domicile_DIV_Province" + id).hide();
        		$("#Domicile_FCityAdd" + id).show();
        		$("#Domicile_FCityAdd" + id).attr('fieldid', 'Domicile_CityInput' + id).attr('country', $(this).val());
        	}
        } else if (id.includes('Dwelling_CountryId')) {
        	id = id.replace('Dwelling_CountryId','');
        	if ($(this).val() == "Z000"){
        		$("#Dwelling_CitySelect" + id).show();
        		$("#Dwelling_ForeignCitySelect" + id).hide();
        		$('#Dwelling_ForeignCitySelect' + id).next(".select2-container").hide();
        		$("#Dwelling_CityInput" + id).hide();
        		$("#Dwelling_DIV_Province" + id).show();
        		$("#Dwelling_FCityAdd" + id).hide();
        		$("#Dwelling_FCityAdd" + id).attr('fieldid', '').attr('country', '');
        	} else if ($(this).val() == "Z102" || $(this).val() == "Z112") {
        		$("#Dwelling_CitySelect" + id).hide();
        		$("#Dwelling_ForeignCitySelect" + id).show();
        		$("#Dwelling_ForeignCitySelect" + id).select2();
        		$("#Dwelling_CityInput" + id).hide();
        		$("#Dwelling_DIV_Province" + id).hide();
        		$("#Dwelling_FCityAdd" + id).show();
        		$("#Dwelling_FCityAdd" + id).attr('fieldid', 'Dwelling_CountryId' + id).attr('country', $(this).val());
            } else {
        		$("#Dwelling_CitySelect" + id).hide();
        		$("#Dwelling_ForeignCitySelect" + id).hide();
        		$('#Dwelling_ForeignCitySelect' + id).next(".select2-container").hide();
        		$("#Dwelling_CityInput" + id).show();
        		$("#Dwelling_DIV_Province" + id).hide();
        		$("#Dwelling_FCityAdd" + id).show();
        		$("#Dwelling_FCityAdd" + id).attr('fieldid', 'Dwelling_CityInput' + id).attr('country', $(this).val());
        	}
        }

if ($(this).val() == "Z102" || $(this).val() == "Z112"){
	        var CountryId = $(this).val();
	        var id = $(this).attr("id");
            
	        $.ajax({
	            url: 'ajax/ajx_get_foreignCities.php',
	            type: 'POST',
	            dataType: 'json',
	            data: {CountryId:CountryId},
	            success: function (data) {
		            console.log(data);
	            	if (id.includes('Forwarding_CountryId')){
	            		id = id.replace('Forwarding_CountryId','');
	            		$('#Forwarding_ForeignCitySelect' + id).html(data.Options);
	            	}
	            	if (id.includes('Domicile_CountryId')){
	            		id = id.replace('Domicile_CountryId','');
	            		$('#Domicile_ForeignCitySelect' + id).html(data.Options);
	            	}
	            	if (id.includes('Dwelling_CountryId')){
	            		id = id.replace('Dwelling_CountryId','');
	            		$('#Dwelling_ForeignCitySelect' + id).html(data.Options);
	            	}
	            },
	            error: function (result) {
	                console.log(result);
	                alert("error: " + result.responseText);
	            }
	        });
		}
    });

    //Controlla lunghezza totale indirizzo (per stampe)
    $("#Forwarding, #Domicile, #Dwelling").on("change", 
    	    "[id^=Forwarding_Address], [id^=Forwarding_StreetNumber], [id^=Forwarding_Ladder], [id^=Forwarding_Indoor], [id^=Forwarding_Plan], " +
    	    "[id^=Domicile_Address], [id^=Domicile_StreetNumber], [id^=Domicile_Ladder], [id^=Domicile_Indoor], [id^=Domicile_Plan]," +
    	    "[id^=Dwelling_Address], [id^=Dwelling_StreetNumber], [id^=Dwelling_Ladder], [id^=Dwelling_Indoor], [id^=Dwelling_Plan]", function () {
    	var id = $(this).attr("id");
        var n = id[id.length -1];
        var element = "";

        if (id.includes("Forwarding"))
            element = "#Forwarding";
        else if (id.includes("Domicile"))
            element = "#Domicile";
        else if(id.includes("Dwelling"))
    		element = "#Dwelling";

        var Address = 
            ($(element + "_Address" + n).val().length > 0 ? $(element + "_Address" + n).val() : "") +
    		($(element + "_StreetNumber" + n).val().length > 0 ? " " + $(element + "_StreetNumber" + n).val() : "") +
			($(element + "_Ladder" + n).val().length > 0 ? " " + $(element + "_Ladder" + n).val() : "") +
			($(element + "_Indoor" + n).val().length > 0 ? " " + $(element + "_Indoor" + n).val() : "") +
			($(element + "_Plan" + n).val().length > 0 ? " " + $(element + "_Plan" + n).val() : "");
        if (Address.length > 46) 
            alert("Attenzione: l'indirizzo potrebbe risultare troncato su eventuali stampe in quanto non deve eccedere 46 caratteri (spazi compresi). Spazi contati: " + Address.length + " \n\n" + Address);
    });

    //ZIP RECAPITI
    $("#Forwarding").on("change", "[id^=Forwarding_Address], [id^=Forwarding_CityInput], [id^=Forwarding_CitySelect], [id^=Forwarding_ForeignCitySelect], [id^=Forwarding_StreetNumber]", function () {

        var id = $(this).attr("id");
        var n = id[id.length -1];
        var CityId="";
        var Type = "";

        if($("#Forwarding_CitySelect" + n).is(':visible')){
            CityId =  $('#Forwarding_CitySelect' + n).val();
        } else if ($("#Forwarding_CityInput" + n).is(':visible')) {
            CityId =  $('#Forwarding_CityInput' + n).val();
        } else if ($("#Forwarding_ForeignCitySelect" + n).is(':visible')) {
            CityId =  $('#Forwarding_ForeignCitySelect' + n).val();
            Type = "Foreign";
        }

        var Address = $('#Forwarding_Address' + n).val();
        var CountryId = $('#Forwarding_CountryId' + n).val();
        var StreetNumber = $('#Forwarding_StreetNumber' + n).val();

        if(CityId!=""){

            $.ajax({
                url: 'ajax/ajx_src_zip.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId: CityId, Address:Address, Type:Type},
                success: function (data) {
                    if (CountryId == "Z102" || CountryId == "Z112"){
                    	$("#Forwarding_LandId" + n).val(data.LandId);
                    }
                    if(data.ZIP !="" ){
                        $("#Forwarding_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                        $("#Forwarding_ZIP" + n).val(data.ZIP);
                    } else $("#Forwarding_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                }
            });

 } else {
        	$("#Forwarding_ZIP" + n).val('').removeClass('txt-success txt-warning txt-danger');
        	$("#Forwarding_LandId" + n).val('');
        }

        if(id.includes("Forwarding_CitySelect")) {
            $.ajax({
                url: 'ajax/ajx_src_prov_shortTitle.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {CityId:CityId},
                success: function (data) {
                    $("#Forwarding_span_Province" +n).html(data.Province);
                },
                error: function (data) {
                	console.log(data);
                }
            });
        }
    });

    //ZIP DOMICILI
    $("#Domicile").on("change", "[id^=Domicile_Address], [id^=Domicile_CityInput], [id^=Domicile_CitySelect], [id^=Domicile_ForeignCitySelect], [id^=Domicile_StreetNumber]", function () {

        var id = $(this).attr("id");
        var n = id[id.length -1];
        var CityId="";
        var Type = "";

        if($("#Domicile_CitySelect" + n).is(':visible')){
            CityId =  $('#Domicile_CitySelect' + n).val();
        } else if ($("#Domicile_CityInput" + n).is(':visible')) {
            CityId =  $('#Domicile_CityInput' + n).val();
        } else if ($("#Domicile_ForeignCitySelect" + n).is(':visible')) {
            CityId =  $('#Domicile_ForeignCitySelect' + n).val();
            Type = "Foreign";
        }

        var Address = $('#Domicile_Address' + n).val();
        var CountryId = $('#Domicile_CountryId' + n).val();
        var StreetNumber = $('#Domicile_StreetNumber' + n).val();

        if(CityId!=""){

            $.ajax({
                url: 'ajax/ajx_src_zip.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId: CityId, Address:Address, Type:Type},
                success: function (data) {
                    if (CountryId == "Z102" || CountryId == "Z112"){
                    	$("#Domicile_LandId" + n).val(data.LandId);
                    }
                    if(data.ZIP !="" ){
                        $("#Domicile_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                        $("#Domicile_ZIP" + n).val(data.ZIP);
                    } else $("#Domicile_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                }
            });

        } else {
        	$("#Domicile_ZIP" + n).val('').removeClass('txt-success txt-warning txt-danger');
        	$("#Domicile_LandId" + n).val('');
        }

        if(id.includes("Domicile_CitySelect")) {
            $.ajax({
                url: 'ajax/ajx_src_prov_shortTitle.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {CityId:CityId},
                success: function (data) {
                    $("#Domicile_span_Province" +n).html(data.Province);
                },
                error: function (data) {
                	console.log(data);
                }
            });
        }
    });

    //ZIP DIMORE
    $("#Dwelling").on("change", "[id^=Dwelling_Address], [id^=Dwelling_CityInput], [id^=Dwelling_CitySelect], [id^=Dwelling_ForeignCitySelect], [id^=Dwelling_StreetNumber]", function () {

        var id = $(this).attr("id");
        var n = id[id.length -1];
        var CityId="";
        var Type = "";
        
        if($("#Dwelling_CitySelect" + n).is(':visible')){
            CityId =  $('#Dwelling_CitySelect' + n).val();
        } else if ($("#Dwelling_CityInput" + n).is(':visible')) {
            CityId =  $('#Dwelling_CityInput' + n).val();
        } else if ($("#Dwelling_ForeignCitySelect" + n).is(':visible')) {
            CityId =  $('#Dwelling_ForeignCitySelect' + n).val();
            Type = "Foreign";
        }

        var Address = $('#Dwelling_Address' + n).val();
        var CountryId = $('#Dwelling_CountryId' + n).val();
        var StreetNumber = $('#Dwelling_StreetNumber' + n).val();

        if(CityId!=""){

            $.ajax({
                url: 'ajax/ajx_src_zip.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {StreetNumber:StreetNumber, CountryId:CountryId, CityId: CityId, Address:Address, Type:Type},
                success: function (data) {
                    if (CountryId == "Z102" || CountryId == "Z112"){
                    	$("#Dwelling_LandId" + n).val(data.LandId);
                    }
                    if(data.ZIP !="" ){
                        $("#Dwelling_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                        $("#Dwelling_ZIP" + n).val(data.ZIP);
                    } else $("#Dwelling_ZIP" + n).removeClass('txt-success txt-warning txt-danger').addClass('txt-danger');
                }
            });

         } else {
        	$("#Dwelling_ZIP" + n).val('').removeClass('txt-success txt-warning txt-danger');
        	$("#Dwelling_LandId" + n).val('');
        }

        if(id.includes("Dwelling_CitySelect")) {
            $.ajax({
                url: 'ajax/ajx_src_prov_shortTitle.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {CityId:CityId},
                success: function (data) {
                    $("#Dwelling_span_Province" +n).html(data.Province);
                },
                error: function (data) {
                	console.log(data);
                }
            });
        }
    });
    
    //
    
});

</script>