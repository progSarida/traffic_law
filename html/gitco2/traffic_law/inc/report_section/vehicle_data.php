<?php

$VehicleTypeId = "";
$VehicleBrand = "";
$VehicleModel = "";
$VehicleColor = "";
$VehicleMass = "";
$VehiclePlate = "";
$TemporaryPlate = "";
$CountryId = "";

if ($isPageUpdate){
    $VehicleTypeId = StringOutDB($r_Fine['VehicleTypeId']);
    $VehicleBrand = StringOutDB($r_Fine['VehicleBrand']);
    $VehicleModel = StringOutDB($r_Fine['VehicleModel']);
    $VehicleColor = StringOutDB($r_Fine['VehicleColor']);
    $VehicleMass = $r_Fine['VehicleMass'];
    $VehiclePlate = $r_Fine['VehiclePlate'];
    $TemporaryPlate = $r_Fine['TemporaryPlate'];
    $CountryId = $r_Fine['CountryId'];
} else {
    $VehicleTypeId = 1;
    $CountryId = "Z000";
}

$str_Vehicle_Data =  '
    <div class="col-sm-12">
        <div class="col-sm-2 BoxRowLabel">
            Tipo veicolo
        </div>
        <div class="col-sm-3 BoxRowCaption">
            '. CreateSelect("VehicleType","Disabled=0","Title".LAN,"VehicleTypeId","Id","Title".LAN, $VehicleTypeId,true) .'
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Targa
            <i class="fa fa-share" style="position:absolute; right:0.1rem;font-size:1.7rem;top:0.2rem;"></i>
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" value="'.$VehiclePlate.'" class="form-control frm_field_string '.($_SESSION['ruletypeid'] == RULETYPE_CDS ? 'frm_field_required' : '').'" name="VehiclePlate" id="VehiclePlate" style="float:left;text-transform:uppercase">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            P
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="checkbox" value="1" '.ChkCheckButton($TemporaryPlate).' id="TemporaryPlate" name="TemporaryPlate" data-container="body" data-toggle="tooltip" data-placement="top" title="Targa prova" class="tooltip-r" tabindex="-1">
        </div>
        <div id="massa">
            <div class="col-sm-1 BoxRowLabel">
                Massa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" value="'.$VehicleMass.'" class="form-control frm_field_numeric" name="VehicleMass" id="VehicleMass">	
            </div>
        </div>
        <div id="toHide2" hidden class="col-sm-6 BoxRowCaption">
        </div>
    </div>

    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="col-sm-2 BoxRowLabel">
            Colore
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="form-control frm_field_string" name="VehicleColor" id="VehicleColor" value="'.$VehicleColor.'" class="find_list">	
            <ul id="VehicleColor_List" class="ul_SearchList"></ul>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Marca
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="form-control frm_field_string" name="VehicleBrand" id="VehicleBrand" value="'.$VehicleBrand.'" class="find_list">	
            <ul id="VehicleBrand_List" class="ul_SearchList"></ul>
        </div>

        <div class="col-sm-2 BoxRowLabel">
            Modello
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" class="form-control frm_field_string" name="VehicleModel" id="VehicleModel" value="'.$VehicleModel.'" class="find_list">	
            <ul id="VehicleModel_List" class="ul_SearchList"></ul>
        </div>

    </div>
    <div class="col-sm-12" id="div_chkPlate">
        <div id="trespasser_content" class="col-sm-12" style="display: none;"></div>
        <div id="fine_content" class="col-sm-12" style="display: none;"></div>
    </div>
';