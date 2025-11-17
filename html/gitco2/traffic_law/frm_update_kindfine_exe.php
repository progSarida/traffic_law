<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$KindSendDate = CheckValue('KindSendDate','s');

if(isset($_POST['checkbox'])) {
    $rs->Start_Transaction();
    foreach($_POST['checkbox'] as $FineId) {
        //echo DateInDB($KindSendDate) . ' - ' .$FineId; die;
        $a_Fine = array(
            array('field'=>'KindSendDate','selector'=>'value','type'=>'date','value'=>DateInDB($KindSendDate)),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
        
    }
    $rs->End_Transaction();
         
}

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";

header("location: frm_update_kindfine.php".$Filters);










