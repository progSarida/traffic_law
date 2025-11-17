<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

$rs->Start_Transaction();

$DocumentTypeId = CheckValue('DocumentationTypeId','n');
$FineId = CheckValue('FineId','n');
$CountryId = CheckValue('CountryId','s');
$PresentationDate = CheckValue('PresentationDate','s');
$note = CheckValue('Note','s');

$str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;

if (!is_dir($str_Folder."/".$_SESSION['cityid'])) {
    mkdir($str_Folder."/".$_SESSION['cityid'], 0777);
}
if (!is_dir($str_Folder."/".$_SESSION['cityid']."/".$FineId)) {
    mkdir($str_Folder."/".$_SESSION['cityid']."/".$FineId, 0777);
}
$image_name = basename( $_FILES['image']['name']);

$a_FinePresentation = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
    array('field'=>'DocumentationTypeId','selector'=>'field','type'=>'int','value'=>$DocumentTypeId, 'settype'=>'int'),
    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$note),
    array('field'=>'PresentationDate','selector'=>'value','type'=>'date','value'=>DateInDB($PresentationDate)),

);

if(isset($_FILES['image'])){
    $targetfolder =$str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$image_name;

    if(move_uploaded_file($_FILES['image']['tmp_name'],$targetfolder)){
        $a_FinePresentation[] = array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$image_name);
    }

}
$rs->Insert('FinePresentation',$a_FinePresentation);

//Mette il flag art. 180 a NO
if(mysqli_num_rows($rs->Select('FineNotification', "FineId=$FineId")) > 0){
    $a_FineNotification = array(
        array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>0, 'settype'=>'int'),
    );
    $rs->Update('FineNotification',$a_FineNotification, 'FineId='.$FineId);
    
} else {
    $rs_TMP_PresentationDocumentProcedure = $rs->Select('TMP_PresentationDocumentProcedure', "FineId=" .$FineId);
    if (mysqli_num_rows($rs_TMP_PresentationDocumentProcedure) == 0) {
        $a_TMP_PresentationDocumentProcedure = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
        );
        $rs->Insert('TMP_PresentationDocumentProcedure',$a_TMP_PresentationDocumentProcedure);
    }
}

$rs->End_Transaction();

header("location: mgmt_presentation.php");