<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters','s');
$SendType = CheckValue('SendType','n');

$a_PrefectCommunication = array(
    array('field'=>'FineId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'SendDate','selector'=>'field','type'=>'date'),
    array('field'=>'NotificationDate','selector'=>'field','type'=>'date'),
    array('field'=>'SendType','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ResultId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
);

if($SendType == 1){
    $a_PrefectCommunication[] = array('field'=>'ReceiptNumber','selector'=>'field','type'=>'str');
    $a_PrefectCommunication[] = array('field'=>'LetterNumber','selector'=>'field','type'=>'str');
}

$rs->Start_Transaction();

$rs->Insert('FinePrefectCommunication',$a_PrefectCommunication);

$rs->End_Transaction();

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
header("location: mgmt_fine.php".$Filters);
