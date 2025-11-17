<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_file_upload.php");
include(INC."/function.php");
require(INC."/initialization.php");

$imgUploader = new cls_file_upload();
$a_AllowedTypes = unserialize(GENERIC_DOCUMENT_ALLOWED_TYPES);

$rs->Start_Transaction();

$FineId = CheckValue('Id','n');
$DocumentationId = CheckValue('DocumentationId','n');
$FilePath = CheckValue('FilePath','s');
$DocumentationTypeId = CheckValue('DocumentationTypeId','n');

if ($DocumentationTypeId >= 25 && $DocumentationTypeId <=28)
    $str_Table = "FinePresentation";
else if ($DocumentationTypeId == 60)
    $str_Table = "DisputeDocumentation";
else $str_Table = "FineDocumentation";

$str_Where = "Id = $DocumentationId";

$str_File = ROOT.'/'.$FilePath;

$select = $rs->Select($str_Table, $str_Where);

if (mysqli_num_rows($select) > 0) {
    if (in_array($DocumentationTypeId, $a_AllowedTypes)) {
        $imgUploader->delete($str_File);
        
        if(!$imgUploader->error){
            $rs->Delete($str_Table, $str_Where);
        } 
    } else $imgUploader->error = "L'eliminazione di questo tipo di documento non è prevista.";
} else $imgUploader->error = "Il riferimento sul database al file selezionato non combacia: <br />Id documento: $DocumentationId";

$rs->End_Transaction();

if ($imgUploader->error) {
    $error = $imgUploader->error;
    $_SESSION['Document_viw']['Error'] = $error;
} else {
    $_SESSION['Document_viw']['Success'] = "Documento eliminato con successo.";
}

header("location: mgmt_document_viw.php" . $str_GET_Parameter . '&P=mgmt_document.php' . '&Id=' . $FineId);