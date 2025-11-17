<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_brandmodel.php");
require_once(INC."/initialization.php");

global $rs;

$Filters = CheckValue('Filters', 's');

$Action = CheckValue('Action', 's');
$Id = CheckValue('Id', 's');

$r_AnomalyBrandModel = $rs->getArrayLine($rs->Select('AnomalyBrandModel', "Id=$Id"));

if(!mgmtAnomalyBrandModelCanConfirm($r_AnomalyBrandModel) && $Action != 'fix'){
    $_SESSION['Message']['Error'] = "Questa anomalia marca modello non è eligibile per essere contrassegnata come corretta in origine.";
    header("location: mgmt_brandmodel.php".$Filters);
    DIE;
}

if($r_AnomalyBrandModel){
    $rs->Start_Transaction();
    
    $a_AnomalyBrandModelHistory = array(
        array('field'=>'AnomalyBrandModelId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
        array('field'=>'Brand','selector'=>'value','type'=>'str','value'=>StringOutDB($r_AnomalyBrandModel['Brand'])),
        array('field'=>'Model','selector'=>'value','type'=>'str','value'=>StringOutDB($r_AnomalyBrandModel['Model'])),
        array('field'=>'VehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_AnomalyBrandModel['VehicleTypeId'],'settype'=>'int'),
        array('field'=>'CorrectBrand','selector'=>'value','type'=>'str','value'=>StringOutDB($r_AnomalyBrandModel['CorrectBrand'])),
        array('field'=>'CorrectModel','selector'=>'value','type'=>'str','value'=>StringOutDB($r_AnomalyBrandModel['CorrectModel'])),
        array('field'=>'CorrectVehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_AnomalyBrandModel['CorrectVehicleTypeId'],'settype'=>'int'),
        array('field'=>'Valid','selector'=>'value','type'=>'int','value'=>$r_AnomalyBrandModel['Valid'],'settype'=>'int'),
        array('field'=>'DataSourceId','selector'=>'value','type'=>'int','value'=>$r_AnomalyBrandModel['DataSourceId'],'settype'=>'int'),
        array('field'=>'DataSourceDate','selector'=>'value','type'=>'str','value'=>$r_AnomalyBrandModel['DataSourceDate']),
        array('field'=>'DataSourceTime','selector'=>'value','type'=>'str','value'=>$r_AnomalyBrandModel['DataSourceTime']),
        array('field'=>'UpdateDataSourceId','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
        array('field'=>'UpdateDate','selector'=>'value','type'=>'str','value'=>date('Y-m-d')),
        array('field'=>'UpdateTime','selector'=>'value','type'=>'str','value'=>date('H:i:s')),
        array('field'=>'UpdateUserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    );
    
    //Correzione
    if ($Action == "fix"){
        $CorrectBrand = strtoupper(CheckValue('Brand', 's'));
        $CorrectModel = strtoupper(CheckValue('Model', 's'));
        $CorrectVehicleTypeId = CheckValue('VehicleTypeId', 'n');
        
        $a_AnomalyBrandModel = array(
            array('field'=>'CorrectBrand','selector'=>'value','type'=>'str','value'=>$CorrectBrand),
            array('field'=>'CorrectModel','selector'=>'value','type'=>'str','value'=>$CorrectModel),
            array('field'=>'CorrectVehicleTypeId','selector'=>'value','type'=>'int','value'=>$CorrectVehicleTypeId,'settype'=>'int'),
        );
        $rs->Update("AnomalyBrandModel", $a_AnomalyBrandModel, "Id=$Id");
        $rs->Insert("AnomalyBrandModelHistory", $a_AnomalyBrandModelHistory);
    }
    //Corretto in origine
    else if ($Action == "confirm"){
        $CorrectBrand = $r_AnomalyBrandModel['Brand'];
        $CorrectModel = $r_AnomalyBrandModel['Model'];
        $CorrectVehicleTypeId = $r_AnomalyBrandModel['VehicleTypeId'];
        
        $a_AnomalyBrandModel = array(
            array('field'=>'Valid','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int')
        );
        
        $rs->Update("AnomalyBrandModel", $a_AnomalyBrandModel, "Id=$Id");
        $rs->Insert("AnomalyBrandModelHistory", $a_AnomalyBrandModelHistory);
    }
    
    if(isset($a_AnomalyBrandModel)){
        $a_FineId = $_POST['FineId'];
        
        foreach ($a_FineId as $FineId){
            $r_Fine = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"));
            $r_FineAnomaly = $rs->getArrayLine($rs->Select('FineAnomaly', "FineId=$FineId"));
            
            //Controllo aggiuntivo nel caso nel frattempo l'anomalia sia stata già eliminata
            if($r_FineAnomaly){
                $a_HistoryFineAnomalyBrandModel = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'WrongBrand','selector'=>'value','type'=>'str','value'=>StringOutDB($r_Fine['VehicleBrand'])),
                    array('field'=>'WrongModel','selector'=>'value','type'=>'str','value'=>StringOutDB($r_Fine['VehicleModel'])),
                    array('field'=>'WrongVehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['VehicleTypeId'],'settype'=>'int'),
                    array('field'=>'CorrectBrand','selector'=>'value','type'=>'str','value'=>$CorrectBrand),
                    array('field'=>'CorrectModel','selector'=>'value','type'=>'str','value'=>$CorrectModel),
                    array('field'=>'CorrectVehicleTypeId','selector'=>'value','type'=>'int','value'=>$CorrectVehicleTypeId,'settype'=>'int'),
                );
                $rs->Insert("HistoryFineAnomalyBrandModel", $a_HistoryFineAnomalyBrandModel);
                
                $a_Fine = array(
                    array('field'=>'VehicleBrand','selector'=>'value','type'=>'str','value'=>$CorrectBrand),
                    array('field'=>'VehicleModel','selector'=>'value','type'=>'str','value'=>$CorrectModel),
                    array('field'=>'VehicleTypeId','selector'=>'value','type'=>'str','value'=>$CorrectVehicleTypeId),
                );
                
                $rs->Update("Fine", $a_Fine, "Id=$FineId");
                $rs->Delete("FineAnomaly", "FineId=$FineId");
            }
        }
    }
    
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
    
    $rs->End_Transaction();
}

header("location: mgmt_brandmodel.php".$Filters);
