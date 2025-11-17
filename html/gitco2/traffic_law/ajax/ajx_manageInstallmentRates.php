<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

global $rs;

$add = $_POST['add'] ?? array();
$upd = $_POST['upd'] ?? array();
$Operation = CheckValue('Operation', 's');
$CityId = CheckValue('CityId', 's');

$message = '';
$success = true;

if($Operation == "update"){
    $a_dates = array();
    
    if(!empty($add)){
        if((!empty($add['FromDate']) || !empty($add['ToDate']))){
            $a_dates[] = array('FromDate' => DateInDB($add['FromDate']), 'ToDate' => DateInDB($add['ToDate']), 'Percentual' => $add['Percentual'], 'Norm' => $add['Norm']);
        }
    }
    if(!empty($upd)){
        foreach($upd as $toUpdate){
            $a_dates[] = array('FromDate' => DateInDB($toUpdate['FromDate']), 'ToDate' => DateInDB($toUpdate['ToDate']), 'Percentual' => $toUpdate['Percentual'], 'Norm' => $toUpdate['Norm'], 'Id' => $toUpdate['Id']);
        }
    }
    
    if(!empty($a_dates)){
        $a_dates = array_reverse($a_dates);
        
        $count = 0;
        $previousToDate = '';
        
        foreach($a_dates as $dates){
            $count ++;
            
            if(!empty($dates['FromDate']) && !empty($dates['ToDate']) && $dates['FromDate'] > $dates['ToDate']){
                $message = "Intervallo non valido. Da data: {$dates['FromDate']} A data: {$dates['ToDate']}";
                $success = false;
                break;
            }
            
            if(empty($dates['Norm']) || empty($dates['Percentual'])){
                $message = "E' necessario specificare tasso di interesse e norma per la periodicità n. $count";
                $success = false;
                break;
            }
            
            if(count($a_dates) > 1){
                if($count == 1){
                    if(empty($dates['ToDate'])){
                        $message ="In presenza di più periodicità, solo la data di fine dell'ultima può essere vuota";
                        $success = false;
                        break;
                    }
                } else if($count == count($a_dates)) {
                    if(empty($dates['FromDate'])){
                        $message ="In presenza di più periodicità, solo la data di inizio della prima può essere vuota";
                        $success = false;
                        break;
                    } else if($dates['FromDate'] != date('Y-m-d', strtotime("$previousToDate + 1 days"))){
                        $message ="La data di inizio della periodicità n. $count deve essere il giorno successivo alla data di fine della precedente: ".DateOutDB($previousToDate);
                        $success = false;
                        break;
                    }
                } else {
                    if(empty($dates['FromDate']) || empty($dates['ToDate'])){
                        $message ="La periodicità n. $count non può contenere date vuote";
                        $success = false;
                        break;
                    } else if($dates['FromDate'] != date('Y-m-d', strtotime("$previousToDate + 1 days"))){
                        $message ="La data di inizio della periodicità n. $count deve essere il giorno successivo alla data di fine della precedente: ".DateOutDB($previousToDate);
                        $success = false;
                        break;
                    }
                }
            }
            
            $previousToDate = $dates['ToDate'];
        }
        
        if($success){
            $rs->Start_Transaction();
            foreach($a_dates as $dates){
                $a_InstallmentRates = array(
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                    array('field'=>'FromDate','selector'=>'value','type'=>'date','value'=>$dates['FromDate'] ?: null, 'nullable' => true),
                    array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>$dates['ToDate'] ?: null, 'nullable' => true),
                    array('field'=>'Norm','selector'=>'value','type'=>'str','value'=>$dates['Norm']),
                    array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>$dates['Percentual'],'settype'=>'flt'),
                );
                
                if(!empty($dates['Id'])){
                    $rs->Update("InstallmentRates", $a_InstallmentRates, "Id = {$dates['Id']}");
                } else {
                    $rs->Insert("InstallmentRates", $a_InstallmentRates);
                }
            }
            $rs->End_Transaction();
        }
    }
} else if($Operation == "delete"){
    $Id = CheckValue('Id', "n");
    
    if($Id > 0){
        $rs->Start_Transaction();
        $rs->Delete("InstallmentRates", "Id=$Id");
        $rs->End_Transaction();
    }
}

echo json_encode(
    array(
        "Message" => $message,
        "Success" => $success
    )
);