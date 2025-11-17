<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

if($_GET) {
    $FineId = $_GET['FineId'];

    $rs_FineNotification = array(
        array('field'=>'ValidatedAddress','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int')
    );


    $rs->Update('FineNotification',$rs_FineNotification, "FineId=".$FineId);
    echo json_encode('dsfsdf');

}