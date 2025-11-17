<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");



$Id= CheckValue('Id','n');

$Note= CheckValue('Note','s');
$Filters = CheckValue('Filters', 's');

$NotificationDate = date("Y-m-d");

$Note .= " (PRATICA ANNULLATA DA UTENTE ".$_SESSION['username'].")";
$StatusTypeId=90;
$NotificationTypeId = 10;



$rs->Start_Transaction();

//Sulla Fine "PreviousId" id sorgente
//Update su FineNotification setto 126bis o 180(presentation) = 1
$a_fineArticle = $rs->getArrayLine($rs->ExecuteQuery("SELECT ViolationTypeId, PreviousId FROM `V_FineArticle` WHERE Id=".$Id));
if($a_fineArticle && $a_fineArticle['ViolationTypeId']==5){
    $a_fineNotif = $rs->getArrayLine($rs->ExecuteQuery("SELECT * FROM `FineNotification` WHERE FineId=".$a_fineArticle['PreviousId']));
    if($a_fineNotif){
        $aUpdateNotif = array(
            array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>1),
            array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int', 'value'=>1)
        );
        $rs->Update('FineNotification',$aUpdateNotif, 'FineId='.$a_fineArticle['PreviousId']);

    }
}

$aFine = array(
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
    array('field'=>'Note','selector'=>'value','value'=>$Note, 'type'=>'str')
);

$rs->Update('Fine',$aFine, 'Id='.$Id);


$rs->Delete('FineAnomaly', 'FineId='.$Id);


$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("location: ".'mgmt_violation.php'.$Filters);