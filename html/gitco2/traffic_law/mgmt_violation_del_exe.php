<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id= CheckValue('Id','n');

$CountryId = CheckValue('CountryId','s');




$str_Folder = ($CountryId=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


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

//se fosse una rinotifica dovrebbe anche sistemare lo stato 

$rs->Delete('FineHistory', "CityId='".$_SESSION['cityid']."' AND FineId=".$Id);

$rs->Delete('Fine', 'Id='.$Id);
$rs->Delete('FineArticle', 'FineId='.$Id);
$rs->Delete('FineAdditionalArticle', 'FineId='.$Id);
$rs->Delete('FineAdditionalController', 'FineId='.$Id);
$rs->Delete('FineOwner', 'FineId='.$Id);
$rs->Delete('FineTrespasser', 'FineId='.$Id);
$rs->Delete('FineAnomaly', 'FineId='.$Id);



$doc_rows = $rs->Select('FineDocumentation',"FineId=".$Id);
$doc_n = mysqli_num_rows($doc_rows);

if($doc_n>0){


    while($doc_row=mysqli_fetch_array($doc_rows)){
        unlink($str_Folder."/".$_SESSION['cityid']."/".$Id."/".$doc_row['Documentation']);
    }
    rmdir($str_Folder."/".$_SESSION['cityid']."/".$Id);

}

$rs->Delete('FineDocumentation', 'FineId='.$Id);


$rs->End_Transaction();
header("location: ".$str_BackPage);



