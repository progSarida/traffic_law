<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

global $rs;

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 's');

$r_AnomalyBrandModel = $rs->getArrayLine($rs->Select('AnomalyBrandModel', "Id=$Id"));

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
    
    $rs->Insert("AnomalyBrandModelHistory", $a_AnomalyBrandModelHistory);
    
    $rs->Delete("AnomalyBrandModel", "Id=$Id");
    $n_AffectedRows = mysqli_affected_rows($rs->conn);

    if ($n_AffectedRows > 0){
        $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
    } else {
        $_SESSION['Message']['Error'] = "Impossibile eliminare l'identificativo marca/modello.";
    }

    $rs->End_Transaction();
} else {
    $_SESSION['Message']['Error'] = "Identificativo marca/modello non presente su banca dati.";
}

header("location: mgmt_brandmodel.php".$Filters);