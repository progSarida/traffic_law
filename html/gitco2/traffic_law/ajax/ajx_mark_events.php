<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

global $rs;

$n_Error = $n_Warning = $n_Success = 0;
$EventId = CheckValue("Id", 'n');

$a_Update = array(
    array('field'=>'IsRead','selector'=>'value','type'=>'str','value'=>'S'),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
);

$rs->Update("Events", $a_Update, "EventId=$EventId AND CityId='{$_SESSION['cityid']}'");

$rs_Events = $rs->Select("Events", "CityId='{$_SESSION['cityid']}' AND IsRead='N' AND DATE(EventDate) > DATE(NOW() - INTERVAL 5 DAY)");

while($event = $rs->getArrayLine($rs_Events)){
    switch($event['Severity']){
        case 'SUCCESS': $n_Success++; break;
        case 'WARNING': $n_Warning++; break;
        case 'ERROR': $n_Error++; break;
    }
}

echo json_encode(
    array(
        "Error" => $n_Error,
        "Warning" => $n_Warning,
        "Success" => $n_Success
    )
);