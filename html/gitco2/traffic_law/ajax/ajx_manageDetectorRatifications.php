<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$DetectorId = CheckValue('DetectorId', 'n');
$Action = CheckValue('Action', 's');
$CalibrationText = CheckValue('CalibrationText', 's');
$CalibrationId = CheckValue('CalibrationId', 'n');
$d_FromDate = DateTime::createFromFormat("d/m/Y", CheckValue('FromDate', 's'));
$d_ToDate = DateTime::createFromFormat("d/m/Y", CheckValue('ToDate', 's'));
$FromDate = $d_FromDate ? $d_FromDate->format('Y-m-d') : null;
$ToDate = $d_ToDate ? $d_ToDate->format('Y-m-d') : null;
$CurrentYear = $_SESSION['year'];

$a_Dates = array();
$a_NewDates = array();
$b_Success = true;
$MaxToDate = null;
$MinFromDate = null;
$o_Result = new stdClass();

$rs_DetectorRatification = $rs->Select('DetectorRatification', ($Action == 'edit' ? "Id!=$CalibrationId AND " : '')."DetectorId=$DetectorId", 'ToDate IS NULL DESC, Todate DESC');
while($r_DetectorRatification = $rs->getArrayLine($rs_DetectorRatification)){
    $a_Dates[$r_DetectorRatification['Id']]['FromDate'] = $r_DetectorRatification['FromDate'];
    $a_Dates[$r_DetectorRatification['Id']]['ToDate'] = $r_DetectorRatification['ToDate'];
}

$rs_MaxMin = $rs->SelectQuery("SELECT NULLIF(MAX(IFNULL(ToDate,'9999-12-31')),'9999-12-31') AS MaxToDate, MIN(FromDate) AS MinFromDate FROM DetectorRatification WHERE ".($Action == 'edit' ? "Id!=$CalibrationId AND " : '')."DetectorId=$DetectorId");
$r_MaxMin = $rs->getArrayLine($rs_MaxMin);
if($r_MaxMin){
    $MaxToDate = $r_MaxMin['MaxToDate'];
    $MinFromDate = $r_MaxMin['MinFromDate'];
}

$a_Insert = array(
    array('field' => 'Ratification', 'selector' => 'value', 'type' => 'str', 'value'=>$CalibrationText),
    array('field' => 'FromDate', 'selector' => 'value', 'type' => 'date', 'value'=>$FromDate),
    array('field' => 'ToDate', 'selector' => 'value', 'type' => 'date', 'value'=>$ToDate)
);

switch($Action){
    case 'add':
        //Se non ho validità già inserite per il rilevatore, la inserisco
        if(empty($a_Dates)){
            $b_Success = true;
        } else {
            foreach($a_Dates as $id => $dates){
                if(!isset($dates['FromDate'],$dates['ToDate'])){
                    $o_Result->Message = 'È necessario che tutte le validità già registrate abbiano le date di inizio e fine valorizzate per poterne inserire una nuova. Intervallo incompleto: DATA INIZIO: '
                        .(isset($dates['FromDate']) ? DateOutDB($dates['FromDate']) : 'VUOTO').' DATA FINE: '.(isset($dates['ToDate']) ? DateOutDB($dates['ToDate']) : 'VUOTO');
                    $b_Success = false;
                    break;
                }
                
                if(isset($FromDate,$ToDate)){
                    if(($FromDate <= $dates['ToDate']) && ($dates['FromDate'] <= $ToDate)) {
                        $o_Result->Message = 'Le date di inizio o fine validità rientrano già in un intervallo registrato per questo rilevatore: DATA INIZIO: '
                            .(isset($dates['FromDate']) ? DateOutDB($dates['FromDate']) : 'VUOTO').' DATA FINE: '.(isset($dates['ToDate']) ? DateOutDB($dates['ToDate']) : 'VUOTO');
                            $b_Success = false;
                            break;
                    }
                } else if(isset($FromDate)){
                    if($FromDate <= $MaxToDate){
                        $o_Result->Message = 'In assenza di data di fine validità definita, è necessario che la data di inizio validità specificata sia superiore alla data di fine validità massima già registrata per questo rilevatore: '.DateOutDB($MaxToDate);
                        $b_Success = false;
                        break;
                    } else $b_Success = true;
                } else {
                    $o_Result->Message = 'In presenza di altre validità già registrate, è necessario definire la data di inizio validità.';
                    $b_Success = false;
                    break;
                }
            }
        }
        
        if($b_Success){
            $a_Insert[] = array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value'=>$DetectorId, 'settype'=>'int');
            $rs->Insert('DetectorRatification', $a_Insert);
            $o_Result->Message = 'Validità inserita con successo.';
        }
        break;
    case 'edit':
        foreach($a_Dates as $id => $dates){
            if(isset($FromDate,$ToDate)){
                if(($FromDate <= $dates['ToDate']) && ($dates['FromDate'] <= $ToDate)) {
                    $o_Result->Message = 'Le date di inizio o fine validità rientrano già in un intervallo registrato: DATA INIZIO: '
                        .(isset($dates['FromDate']) ? DateOutDB($dates['FromDate']) : 'VUOTO').' DATA FINE: '.(isset($dates['ToDate']) ? DateOutDB($dates['ToDate']) : 'VUOTO');
                        $b_Success = false;
                        break;
                }
            } else if(isset($FromDate)){
                if(!isset($dates['ToDate'])){
                    $o_Result->Message = 'Non è possibile applicare la modifica in quanto esiste già una validità con data di fine indefinita: DATA INIZIO: '
                        .(isset($dates['FromDate']) ? DateOutDB($dates['FromDate']) : 'VUOTO').' DATA FINE: '.(isset($dates['ToDate']) ? DateOutDB($dates['ToDate']) : 'VUOTO');
                        $b_Success = false;
                        break;
                } else $b_Success = true;
            } else if(isset($ToDate)){
                $o_Result->Message = 'In presenza di altre validità, è necessario definire la data di inizio validità.';
                $b_Success = false;
                break;
            } else {
                $o_Result->Message = 'In presenza di altre validità, è necessario definire la data di inizio validità.';
                $b_Success = false;
                break;
            }
        }
        if($b_Success){
            $rs->Update('DetectorRatification', $a_Insert, "Id=$CalibrationId");
            $o_Result->Message = 'Validità modificata con successo.';
        }
        break;
    case 'del':
        if ($CalibrationId > 0 && array_key_exists($CalibrationId, $a_Dates)){
            $rs->Delete('DetectorRatification', "Id=$CalibrationId");
            $o_Result->Message = 'Validità eliminata con successo.';
        } else {
            $o_Result->Message = 'Validità non trovata. Impossibile eliminare.';
        }
        break;
}

$rs_UpdDetectorRatification = $rs->Select("DetectorRatification", "DetectorId=$DetectorId AND (($CurrentYear >= YEAR(FromDate) AND $CurrentYear <= YEAR(ToDate)) OR ($CurrentYear >= YEAR(FromDate) AND ToDate IS NULL) OR (FromDate IS NULL AND ToDate IS NULL))", "ToDate IS NULL DESC, Todate DESC");
$o_Result->Quantity = mysqli_num_rows($rs_UpdDetectorRatification);

if($b_Success){
    while($r_UpdDetectorRatification = $rs->getArrayLine($rs_UpdDetectorRatification)){
        $a_NewDates[] = array(
            'Id' => $r_UpdDetectorRatification['Id'],
            'FromDate' => DateOutDB($r_UpdDetectorRatification['FromDate']),
            'ToDate' => DateOutDB($r_UpdDetectorRatification['ToDate']),
            'Ratification' => $r_UpdDetectorRatification['Ratification']
        );
    }
}

$o_Result->NewDates = $a_NewDates;

echo json_encode(
    array(
        "Result" => $o_Result,
    )
);

