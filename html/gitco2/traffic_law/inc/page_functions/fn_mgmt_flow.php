<?php
require_once("_path.php");

define('MGMT_FLOW_INDEX_DELIVERED', 'CONSEGNATO');
define('MGMT_FLOW_INDEX_PAYED', 'PAGATO');
define('MGMT_FLOW_INDEX_PROCESSED', 'LAVORATO');
define('MGMT_FLOW_INDEX_UPLOAD', 'UPLOAD');
define('MGMT_FLOW_INDEX_CREATED', 'CREATO');
define('MGMT_FLOW_INDEX_UNKNOWN', 'SCONOSCIUTO');

define('MGMT_FLOW_STATUS', serialize(array(
    MGMT_FLOW_INDEX_DELIVERED => array('Field' => 'SendDate', 'Colour' => 'green'),
    MGMT_FLOW_INDEX_PAYED => array('Field' => 'PaymentDate', 'Colour' => 'orange'),
    MGMT_FLOW_INDEX_PROCESSED => array('Field' => 'ProcessingDate', 'Colour' => 'orange'),
    MGMT_FLOW_INDEX_UPLOAD => array('Field' => 'UploadDate', 'Colour' => 'blue'),
    MGMT_FLOW_INDEX_CREATED => array('Field' => 'CreationDate', 'Colour' => 'red'),
    MGMT_FLOW_INDEX_UNKNOWN => array('Field' => '', 'Colour' => 'gray'),
)));

//Tipi di flussi di cui è prevista la visualizzazione del dettaglio delle notifiche postali
//Tabella Document_Type
define('MGMT_FLOW_TYPES_NOTIFICATION_INFO_IDS', serialize(array(
    1,5,17,18
)));
//Tipi di flussi di cui è prevista la visualizzazione del dettaglio delle ricevute PEC
//Tabella Document_Type
define('MGMT_FLOW_TYPES_PEC_INFO_IDS', serialize(array(
    17,18
)));

function mgmtFlowWhere() {
    global $Search_PrintNumber;
    global $Search_Year;
    global $Search_CityId;
    global $Search_Step;
    global $Search_StepDate;
    global $Search_MissedStep;
    global $Search_Type;
    global $Search_Flow;
    global $Search_Status;
    
    $a_status = unserialize(MGMT_FLOW_STATUS);
    
    $str_Where = "1=1 AND RuleTypeId={$_SESSION['ruletypeid']}";
    
    if ($Search_PrintNumber > 0){
        $str_Where .= " AND PrintTypeId=$Search_PrintNumber";
    }
    if ($Search_Year != ''){
        $str_Where .= " AND Year=$Search_Year";
    }
    if($_SESSION['userlevel'] >= 3){
        if($Search_CityId != ''){
            $str_Where .= " AND CityId='$Search_CityId'";
        }
    } else $str_Where .= " AND CityId='{$_SESSION['cityid']}'";
    if($Search_Step != ''){
        
        if(!empty($a_status[$Search_Step]['Field'])){
            $str_Where.= " AND ".$a_status[$Search_Step]['Field'];
            if($Search_StepDate != ''){
                $str_Where.= "='".DateInDB($Search_StepDate)."'";
            } else {
                $str_Where.= " IS NOT NULL";
            }
        }
        
        foreach($a_status as $status => $field){
            if($Search_Step == $status){
                break;
            } else if(!empty($field['Field'])) {
                $str_Where.= " AND {$field['Field']} IS NULL";
            }
        }
        
    }
    if($Search_MissedStep != ''){
        $str_Where.= " AND ".$a_status[$Search_MissedStep]['Field']." IS NULL";
    }
    if($Search_Type > 0){
        $str_Where.= " AND PrinterId=$Search_Type";
    }
    if($Search_Flow != ''){
        $str_Where.= " AND Number=$Search_Flow";
    }
    if($Search_Status != ''){
        $str_Where.= " AND DocumentTypeId=$Search_Status";
    }
    
    return $str_Where;
}

function mgmtFlowStatus($r_Flow){
    $a_status = unserialize(MGMT_FLOW_STATUS);
    
    foreach ($a_status as $status => $field){
        if(!empty($r_Flow[$field['Field']])){
            return $status;
        }
    }
}

function mgmtFlowTotZone($r_Flow){
    return
    ($r_Flow['Zone0Number']*$r_Flow['Zone0Postage'])+
    ($r_Flow['Zone1Number']*$r_Flow['Zone1Postage'])+
    ($r_Flow['Zone2Number']*$r_Flow['Zone2Postage'])+
    ($r_Flow['Zone3Number']*$r_Flow['Zone3Postage']);
}

function mgmtFlowPath($flusso){
    $elementi = explode('_', $flusso['FileName']);
    return $elementi[3] == 'Ita' 
    ? NATIONAL_FLOW_HTML.'/'.$flusso['CityId'].'/'.$flusso['FileName']
    : FOREIGN_FLOW_HTML.'/'.$flusso['CityId'].'/'.$flusso['FileName'];
}
