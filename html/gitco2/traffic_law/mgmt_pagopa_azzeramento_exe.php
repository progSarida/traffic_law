<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
ini_set('max_execution_time', 3000);
$rs= new CLS_DB();
if (isset($_POST['checkbox'])) {
    foreach ($_POST['checkbox'] as $FineId){  
        $fineRow=$rs->Select("Fine"," Id= ".$FineId);
        $fine = mysqli_fetch_array($fineRow);
        //Azzeriamo solo i preinserimenti
        //echo "<br> stato: ". $fine['StatusTypeId']; die;
        if($fine['StatusTypeId'] == 10) {
            $aUpdate = array(
                array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => null),
                array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => null),
            );
            $rs->Update('Fine', $aUpdate, "Id=" . $FineId);
        }
    }
}
header("location: mgmt_pagopa.php".$str_GET_Parameter.
    "&answer=Cancellazione dei codici PagoPA riuscita per le righe selezionate");
?>