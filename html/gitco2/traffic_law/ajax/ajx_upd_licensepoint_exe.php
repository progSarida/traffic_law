<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


if($_POST) {
    $FineId = CheckValue('Id','n');
    $CommunicationStatus = CheckValue('Status','n');


    $a_FineCommunication = array(
        array('field'=>'CommunicationStatus','selector'=>'value','type'=>'int','value'=>$CommunicationStatus,'settype'=>'int'),
        array('field'=>'LicensePointId','selector'=>'value','type'=>'str','value'=>''),
        );





    $rs->Update('FineCommunication',$a_FineCommunication,"FineId=".$FineId);

    $str_Status = ($CommunicationStatus==0) ? " tolta dagli esclusi" : " aggiuta agli esclusi";

    echo json_encode(
        array(
            "Mex" => $str_Status,

        )
    );


}






