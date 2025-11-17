<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_file_upload.php");
include(INC."/function.php");
require(INC."/initialization.php");

$imgUploader = new cls_file_upload();
$a_AllowedExtensions = unserialize(GENERIC_DOCUMENT_EXT);

$Filters = CheckValue('Filters', 's');
$DocumentTypeId = CheckValue('DocumentationTypeId','n');
$FineId = CheckValue('FineId','n');
$CountryId = CheckValue('CountryId','s');
$Note = CheckValue('Note','s');

$a_BackFilters = array(
    'P' => 'mgmt_document.php',
    'DocumentationTypeId' => $DocumentTypeId,
    'Id' => $FineId,
    'Note' => $Note,
);

$str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;

if (!is_dir($str_Folder."/".$_SESSION['cityid'])) {
    mkdir($str_Folder."/".$_SESSION['cityid'], 0777);
}
if (!is_dir($str_Folder."/".$_SESSION['cityid']."/".$FineId)) {
    mkdir($str_Folder."/".$_SESSION['cityid']."/".$FineId, 0777);
}

$image_name = basename( $_FILES['image']['name']);

$rs->Start_Transaction();

if(isset($_FILES['image'])){
    if($imgUploader->checkUploadErrors($_FILES['image']['error'])){
        $imgUploader->setDestination($str_Folder."/".$_SESSION['cityid']."/".$FineId."/");
        $imgUploader->setAllowedExtensions($a_AllowedExtensions);
        $imgUploader->setFileName($image_name);
        $imgUploader->setMaxSize(GENERIC_DOCUMENT_MAX_FILE_SIZE);
        
        $imgUploader->validate($_FILES['image']);
        
        $validated_name = $imgUploader->getFileName($image_name);
        
        $a_FineDocumentation = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'DocumentationTypeId','selector'=>'field','type'=>'int','value'=>$DocumentTypeId, 'settype'=>'int'),
            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$validated_name),
            array('field'=>'Note','selector'=>'value','type'=>'str','value'=>!empty($Note) ? $Note : NULL),
            
        );
        
        $select = $rs->Select("FineDocumentation", "FineId=$FineId AND Documentation='$validated_name' AND DocumentationTypeId=$DocumentTypeId");
        if(mysqli_num_rows($select) > 0){
            $_SESSION['Message']['Error'] = 'Il documento caricato è già presente.';
            header("location: ".impostaParametriUrl($a_BackFilters, 'mgmt_document_add.php' . $Filters));
            DIE;
        }

        if(file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$validated_name)) {
            $_SESSION['Message']['Error'] = 'Il documento caricato è già presente con un tipo diverso da generico.';
            header("location: ".impostaParametriUrl($a_BackFilters, 'mgmt_document_add.php' . $Filters));
            DIE;
        }
        
        $imgUploader->upload($_FILES['image']);
        
        if(!$imgUploader->error){
            $rs->Insert('FineDocumentation',$a_FineDocumentation);
        }
    } else {
        $_SESSION['Message']['Error'] = $imgUploader->error;
        header("location: ".impostaParametriUrl($a_BackFilters, 'mgmt_document_add.php' . $Filters));
        DIE;
    }
} else {
    //Se si finisce qui potrebbe essere per il fatto che il file è troppo grande e viola la direttiva post_max_size, php purtroppo in questo caso non riempe $_POST e si perdono i dati inseriti
    $_SESSION['Message']['Error'] = 'Si è verificato un errore. Il file caricato potrebbe essere troppo grande. Si prega di riprovare.';
    header("location: mgmt_document.php". $Filters);
}

$rs->End_Transaction();

if ($imgUploader->error) {
    $_SESSION['Message']['Error'] = $imgUploader->error;
    header("location: ".impostaParametriUrl($a_BackFilters, 'mgmt_document_add.php' . $Filters));
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
    header("location: mgmt_document.php". $Filters);
}
