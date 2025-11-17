<?php
const BRAND_MODEL_MAP_SEPARATOR = '>-<';

function insertTrespsserHistory($r_Trespasser, $a_oldTrespasserData, $a_newTrespasserData, $DataSourceId){
    global $rs;
    $AddressFH = isset($r_Trespasser['Address']) ? $r_Trespasser['Address'] : '';
    $CityFH = isset($r_Trespasser['City']) ? $r_Trespasser['City'] : '';
    $CountryIdFH = isset($r_Trespasser['CountryId']) ? $r_Trespasser['CountryId'] : '';
    $BornPlaceFH = isset($r_Trespasser['BornPlace']) ? $r_Trespasser['BornPlace'] : '';
    $ZoneIdFH = isset($r_Trespasser['ZoneId']) ? $r_Trespasser['ZoneId'] : -1;
    $LanguageIdFH = isset($r_Trespasser['LanguageId']) ? $r_Trespasser['LanguageId'] : -1;
    $a_Trespasser = array(
        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Trespasser['Id'],'settype'=>'int'),
        array('field'=>'Genre','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Genre']),
        array('field'=>'CompanyName','selector'=>'value','type'=>'str','value'=>$r_Trespasser['CompanyName']),
        array('field'=>'Surname','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Surname']),
        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Name']),
        array('field'=>'BornDate','selector'=>'value','type'=>'str','value'=>$r_Trespasser['BornDate']),
        array('field'=>'BornPlace','selector'=>'value','type'=>'str','value'=>$BornPlaceFH),
        array('field'=>'BornCountryId','selector'=>'value','type'=>'str','value'=>$r_Trespasser['BornCountryId']),
        array('field'=>'TaxCode','selector'=>'value','type'=>'str','value'=>$r_Trespasser['TaxCode']),
        array('field'=>'ForcedTaxCode','selector'=>'value','type'=>'str','value'=>$r_Trespasser['ForcedTaxCode']),
        array('field'=>'VatCode','selector'=>'value','type'=>'str','value'=>$r_Trespasser['VatCode']),
        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$AddressFH),
        array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>$r_Trespasser['StreetNumber']),
        array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Ladder']),
        array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Indoor']),
        array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Plan']),
        array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>$r_Trespasser['ZIP']),
        array('field'=>'City','selector'=>'value','type'=>'str','value'=>$CityFH),
        array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Province']),
        array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryIdFH),
        array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>$r_Trespasser['PEC']),
        array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Mail']),
        array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Phone']),
        array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Phone2']),
        array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Fax']),
        array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Notes']),
        array('field'=>'ZoneId','selector'=>'value','type'=>'int','value'=>$ZoneIdFH,'settype'=>'int'),
        array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageIdFH,'settype'=>'int'),
        array('field'=>'DeathDate','selector'=>'value','type'=>'date','value'=>$r_Trespasser['DeathDate']),
        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Trespasser['UserId']),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Trespasser['VersionDate']),
        array('field'=>'UpdateDataSourceId','selector'=>'value','type'=>'int','value'=>$DataSourceId,'settype'=>'int'),
        array('field'=>'UpdateDataSourceDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
        array('field'=>'UpdateDataSourceTime','selector'=>'value','type'=>'str','value'=>date('H:i:s')),
    );
    
    if($a_oldTrespasserData != $a_newTrespasserData)
        $rs->Insert('TrespasserHistory',$a_Trespasser);
}

function checkModelBrand(&$anomaliesMap,$brand,$model,$vehicleTypeId,$FineId,$NotificationTypeId,$DataSourceId,&$str_Message = ''){
    global $rs;
    
    $key = $brand.BRAND_MODEL_MAP_SEPARATOR.$model.BRAND_MODEL_MAP_SEPARATOR.$vehicleTypeId;
    $a_AnomalyBrandModel = array();
    $a_Insert = null;
    
    if(!array_key_exists ($key,$anomaliesMap)){
        $a_Insert = array(
            array('field' => 'Brand','selector' => 'value','type' => 'str', 'value' => $brand),
            array('field' => 'Model', 'selector' => 'value', 'type' => 'str', 'value' => $model),
            array('field' => 'Valid', 'selector' => 'value', 'type' => 'int', 'value' => 0,'settype'=>'int'),
            array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
            array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
            array('field' => 'DataSourceTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
            array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $vehicleTypeId, 'settype'=>'int')
        );
        
        $a_AnomalyBrandModel['Brand'] = $brand;
        $a_AnomalyBrandModel['Model'] = $model;
        $a_AnomalyBrandModel['VehicleTypeId'] = $vehicleTypeId;
        $a_AnomalyBrandModel['CorrectBrand'] = null;
        $a_AnomalyBrandModel['CorrectModel'] = null;
        $a_AnomalyBrandModel['CorrectVehicleTypeId'] = null;
        $a_AnomalyBrandModel['Valid'] = 0;
        $a_AnomalyBrandModel['Id'] = $rs->insert("AnomalyBrandModel",$a_Insert);
        
        $anomaliesMap[$key] = $a_AnomalyBrandModel;
    } else {
        $a_AnomalyBrandModel = $anomaliesMap[$key];
    }
    
    //Se esiste un'anomalia marca modello ma non è stata contrassegnata come già corretta, salva un record in FineAnomaly, se non esiste già
    if($a_AnomalyBrandModel['Valid'] == 0){
        $rowCount = mysqli_num_rows($rs->Select("FineAnomaly","FineId=".$FineId));
        if($rowCount<=0){
            $a_Insert = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => 'Marca / modello del veicolo da correggere'),
                array('field' => 'AnomalyBrandModelId', 'selector' => 'value', 'type' => 'int', 'value' => $a_AnomalyBrandModel['Id'], 'settype' => 'int')
            );
            $rs->insert("FineAnomaly",$a_Insert);
        }
        return false;
    }
    
    return true;
}

function buildAnomalyBrandModelMap(){
    global $rs;
    
    $a_AnomalyBrandModel = $rs->getResults($rs->Select("AnomalyBrandModel"));
    $a_AnomalyBrandModelMap = array();
    
    foreach($a_AnomalyBrandModel as $record){
        $a_AnomalyBrandModelMap[
            $record['Brand'].BRAND_MODEL_MAP_SEPARATOR.
            $record['Model'].BRAND_MODEL_MAP_SEPARATOR.
            $record['VehicleTypeId']
        ] = $record;
    }
    return $a_AnomalyBrandModelMap;
}