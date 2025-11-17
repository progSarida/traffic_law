<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$rs= new CLS_DB();

$deleted_id = $_POST['deleted_id'];
$deleted = $rs->ExecuteQuery("DELETE FROM Receipt WHERE Id = '$deleted_id'");
if ($deleted){
    echo json_encode(['202'=>'Cancellazione avvenuta correttamente']);
}else{
    echo json_encode(['errr'=>'ERROR: Contattare l\'amministratore']);
}

