<?php
$str_verbalization_Data= "";
$str_ControllerSelect = '<option></option>';
$FineChiefControllerId = "";
$FineNotificationDate = "";

if ($isPageUpdate){
    $FineChiefControllerId = $r_Fine['FineChiefControllerId'];
    $FineNotificationDate = DateOutDB($r_Fine['FineNotificationDate']);
} else if ($isLatestFine) {
    $FineChiefControllerId = $r_PreviousFine['FineChiefControllerId'];
    $FineNotificationDate = DateOutDB($r_PreviousFine['FineDate']);
}

$rs_Controller = $rs->SelectQuery("SELECT Id, Code, CONCAT(Code,' ',Qualification,' ',Name) AS Name FROM Controller WHERE CityId ='".$_SESSION['cityid']."' AND disabled = 0 AND ((Sign IS NOT NULL AND Sign != '') OR DigitalSign = 1) ORDER BY Name");
while($r_Controller = mysqli_fetch_array($rs_Controller)){
    $selected = (isset($FineChiefControllerId) && $FineChiefControllerId == $r_Controller['Id']) ? " selected=\"selected\"" : "";
    $str_ControllerSelect .= '<option'. $selected .' value="'. $r_Controller['Id'] .'">'. $r_Controller['Name'] .'</option>';
}

$str_verbalization_Data .='
<div class="clean_row HSpace4"></div>

<div id="VerbalizationData" class="col-sm-12" style="display:none;">
    <div class="col-sm-2 BoxRowLabel">
        Verbalizzante
    </div>
    <div class="col-sm-5 BoxRowCaption ">
        <select class="form-control" name="FineChiefControllerId" id="FineChiefControllerId" class="col-sm-11">
            '. $str_ControllerSelect .'
        </select>
    </div>
    <div class="col-sm-3 BoxRowLabel">
        Data Verbalizzazione
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <input value="'.$FineNotificationDate.'" type="text" name="FineNotificationDate" id="FineNotificationDate" class="form-control frm_field_date">
    </div>
</div>
';

?>
