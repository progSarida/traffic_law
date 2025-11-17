<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");


$rs= new CLS_DB();
$rs->Start_Transaction();
$id = $_POST['id'];
if (isset($_POST['delete_zipcity'])){
    $deletedAddress = $rs->ExecuteQuery("DELETE FROM sarida.ZIPAddress WHERE ZIPCityId = '$id'");
    $deleted = $rs->ExecuteQuery("DELETE FROM sarida.ZIPCity WHERE Id = '$id'");
    
    if ($deletedAddress && $deleted){
        echo json_encode(['202'=>'Successfully Deleted']);
    }else{
        echo json_encode(['errore'=>'ERROR: Not Deleted!']);
    }
}else if (isset($_POST['delete_zipadress'])){
    $deleted = $rs->ExecuteQuery("DELETE FROM sarida.ZIPAddress WHERE Id = '$id'");
    if ($deleted){
        echo json_encode(['202'=>'Successfully Deleted']);
    }else{
        echo json_encode(['errore'=>mysqli_error($deleted)]);
    }
}
$rs->End_Transaction();


