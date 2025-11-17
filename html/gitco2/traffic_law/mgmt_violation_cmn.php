<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC."/header.php");
const LIST_ORDER = "FineDate ASC, FineTime ASC";
const LIST_TABLE="V_mgmt_ViolationTrespasser";

$Search_Violation;
$Search_ViolationArticle;
$Search_ValidationType;
$Search_HasKindSendDate;
$Search_HasSpeedAnomaly;
$Search_Detector;
$Search_VehicleType;
$Search_SendType;

function readparametersAndBuildWhere(){
    global $str_GET_Parameter;
    global $str_GET_FilterPage;
    global $str_Where;
    global $Search_Violation;
    global $Search_ViolationArticle;
    global $Search_ValidationType;
    global $Search_HasKindSendDate;
    global $Search_Detector;
    global $Search_VehicleType;
    global $Search_HasSpeedAnomaly;
    global $Search_SendType;
    $Search_Violation= CheckValue('Search_Violation','n');
    $Search_ViolationArticle= CheckValue("Search_ViolationArticle", "s");
    $Search_HasKindSendDate= CheckValue("Search_HasKindSendDate", "n");
    $Search_HasSpeedAnomaly= CheckValue("Search_HasSpeedAnomaly", "n");
    $Search_Detector= CheckValue("Search_Detector", "n");
    $Search_VehicleType= CheckValue("Search_VehicleType", "n");
    $isValidation=CheckValue("Validation",'b');
    $Search_SendType= CheckValue("Search_SendType", "s");

    $Search_ValidationType=CheckValue("Search_ValidationType",'s');
    $str_GET_Parameter.="&Search_ValidationType=$Search_ValidationType";
    $str_GET_FilterPage.="&Search_ValidationType=$Search_ValidationType";
    $str_GET_Parameter .= "&Search_ViolationArticle=$Search_ViolationArticle";
    $str_GET_FilterPage.="&Search_ViolationArticle=$Search_ViolationArticle";
    $str_GET_Parameter .= "&Search_HasKindSendDate=$Search_HasKindSendDate";
    $str_GET_FilterPage.="&Search_HasKindSendDate=$Search_HasKindSendDate";
    $str_GET_Parameter .= "&Search_HasSpeedAnomaly=$Search_HasSpeedAnomaly";
    $str_GET_FilterPage.="&Search_HasSpeedAnomaly=$Search_HasSpeedAnomaly";
    $str_GET_Parameter .= "&Search_Detector=$Search_Detector";
    $str_GET_FilterPage.="&Search_Detector=$Search_Detector";
    $str_GET_Parameter .= "&Search_VehicleType=$Search_VehicleType";
    $str_GET_FilterPage.="&Search_VehicleType=$Search_VehicleType";
    $str_GET_FilterPage.="&Search_SendType=$Search_SendType";

    if($_SESSION['usertype']==2 && $isValidation) {
        $str_ValidationType="Da validare";
    } else $str_ValidationType=$Search_ValidationType;
    
    $str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND RuleTypeId = ".$_SESSION['ruletypeid'];
    
    if($str_ValidationType=='Da validare')
        $str_Where.=' and ControllerId is null';
    else if($str_ValidationType=="Validato")
        $str_Where .= ' and ControllerId is not null';
    if ($Search_Violation == 5)
        if ($Search_ViolationArticle == 2)
            $str_Where .= " AND (126Bis=1 OR Article=126)";
        else if ($Search_ViolationArticle == 3)
            $str_Where .= " AND (PresentationDocument=1 OR Article=180)";
        
    if ($Search_HasKindSendDate > 0) {
        $str_Where .= " AND KindCreateDate IS NOT NULL and StatusTypeId not in (8,9)";
    }
    if ($Search_HasSpeedAnomaly > 0) {
        $str_Where .= " AND (Speed > 0 AND SpeedLimit >= Speed)";
    }
    if ($Search_Detector > 0){
        $str_Where .= " AND DetectorId=$Search_Detector";
    }
    if ($Search_VehicleType > 0) {
        $str_Where .= " AND VehicleTypeId = $Search_VehicleType";
    }
    if($Search_SendType == 'PEC')
        $str_Where .= " AND PEC LIKE '%@%'";
    elseif($Search_SendType == 'Stampatore')
        $str_Where .= " AND PEC = ''";
    
    return $str_Where;
}

function residualDays($rs, $r_Violation){
    $days = 0;
    //Verbale nazionale straniero
    $NotificationDays = $r_Violation['CountryId'] == 'Z000' ? NOTIFICATIONDAYS_NATIONAL : NOTIFICATIONDAYS_FOREIGN;
    if(empty($r_Violation['PreviousId'])){
        //Generato normalmente
        $date = !empty($r_Violation['ControllerDate']) ? $r_Violation['ControllerDate'] : $r_Violation['FineDate'];
    } else {
        if ($r_Violation['Article'] == 126 && $r_Violation['Paragraph'] == '0' && $r_Violation['Letter'] == 'bis'){
            //Generato da 126 bis
            $rs_FineDispute = $rs->Select('FineDispute', 'FineId='.$r_Violation['PreviousId']);
            if (mysqli_num_rows($rs_FineDispute) > 0){
                return 'dispute';
            } else {
                $rs_FineHistory = $rs->SelectQuery("SELECT DeliveryDate FROM FineHistory WHERE TrespasserTypeId IN(1,11,3) And NotificationTypeId=6 AND FineId=".$r_Violation['PreviousId']);
                $date = mysqli_fetch_assoc($rs_FineHistory)['DeliveryDate'];
                $date = date('Y-m-d', strtotime($date. ' + 60 days'));
            }
        } /*else if ($r_Violation['Article'] == 180 && $r_Violation['Paragraph'] == 8 && empty($r_Violation['Letter'])){
        //Generato da 180/8
        $rs_FineCommunication = $rs->SelectQuery("SELECT PresentationDate FROM FinePresentation WHERE TrespasserTypeId IN(1,11,3) AND FineId=".$r_Violation['PreviousId']);
        $date = mysqli_fetch_assoc($rs_FineCommunication)['PresentationDate'];
        }*/
        else if (isset($r_Violation['Note']) && strpos($r_Violation['Note'], 'Violazione duplicata: ID') !== false){
            //Generato da rinotifica
            $rs_FineDispute = $rs->Select('FineDispute', 'FineId='.$r_Violation['PreviousId']);
            if (mysqli_num_rows($rs_FineDispute) > 0){
                return 'dispute';
            } else {
                $rs_FineTrespasser = $rs->SelectQuery("SELECT ReceiveDate FROM FineTrespasser WHERE TrespasserTypeId IN(1,11,3) AND FineId=".$r_Violation['FineId']);
                $date = mysqli_fetch_assoc($rs_FineTrespasser)['ReceiveDate'];
            }
        } else {
            //Fallback
            $date = !empty($r_Violation['ControllerDate']) ? $r_Violation['ControllerDate'] : $r_Violation['FineDate'];
        }
    }
    //Aggiunge i giorni della notifica (+1 giorno)
    $date = date('Y-m-d', strtotime($date. ' + '.($NotificationDays+1).' days'));
    //Calcola la differenza tra oggi e la data con i giorni sommati
    $days = DateDiff('D', date('Y-m-d'), $date);
    //Ritorna 0 se i giorni sono negativi
    return $days <= 0 ? 0 : $days;
}
