<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_file_upload.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Message = "";
$Status = "";

if(isset($_POST['Operation'])){
    
    $Operation = CheckValue('Operation','s');
    
    if($Operation == 'del'){
        $DocumentationId = CheckValue('DocumentationId','n');
        
        $rs_FineDocumentation = $rs->SelectQuery("
        SELECT
            F.Id,
            F.CountryId,
            FD.Documentation
            FROM Fine F JOIN FineDocumentation FD on F.Id=FD.FineId
            WHERE FD.Id=". $DocumentationId
            );
        $r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation);
        
        $CountryId      = $r_FineDocumentation['CountryId'];
        $Documentation  = $r_FineDocumentation['Documentation'];
        $FineId         = $r_FineDocumentation['Id'];
        
        $str_DocumentFolder = ($CountryId=='Z000') ? NATIONAL_FINE."/".$_SESSION['cityid']."/".$FineId."/" : FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId."/";
        
        if (file_exists($str_DocumentFolder.$Documentation)) {
            if(unlink($str_DocumentFolder.$Documentation)){
                $rs->Delete("FineDocumentation", "Id=".$DocumentationId);
                $Message = "Eliminato con successo.";
                $Status = true;
            } else {
                $Message = "Errore nell\'eliminazione del file: ".$str_DocumentFolder.$Documentation;
                $Status = false;
            }
        } else {
            $Message = "Il file non esiste: ".$str_DocumentFolder.$Documentation;
            $Status = false;
        }
    }
    
    if($Operation == 'upl'){
        $imgUploader = new cls_file_upload();
        $a_AllowedExtensions = unserialize(GENERIC_DOCUMENT_EXT);
        $n_MaxFileSize = ART126_DOCUMENT_MAX_FILE_SIZE;
        
        if(isset($_FILES['upl_file'])){
            if($imgUploader->checkUploadErrors($_FILES['upl_file']['error'])){
                $DocumentName = basename($_FILES['upl_file']['name']);
                $CountryId = CheckValue('CountryId','s');
                $Id = CheckValue('Id','n');
                $DocumentationTypeId = CheckValue('DocumentationTypeId','n');
                $str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
                
                $imgUploader->setPrintError(false);
                $imgUploader->setMaxSize($n_MaxFileSize);
                $imgUploader->setDestination($str_Folder."/".$_SESSION['cityid']."/".$Id."/");
                $imgUploader->setAllowedExtensions($a_AllowedExtensions);
                $imgUploader->setFileName($DocumentName);
                $imgUploader->setMaxSize($n_MaxFileSize);
                
                $imgUploader->validate($_FILES['upl_file']);
                
                $validated_name = $imgUploader->getFileName($DocumentName);
                
                if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
                    mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
                }
                if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $Id)) {
                    mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $Id, 0777);
                }
                
                if(file_exists($str_Folder."/".$_SESSION['cityid']."/".$Id."/".$validated_name)) {
                    $Message = 'Il documento caricato è già presente.';
                    $Status = false;
                } else {
                    $imgUploader->upload($_FILES['upl_file']);
                    
                    if(!$imgUploader->error){
                        
                        $a_FineDocumentation = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
                            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$validated_name),
                            array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                            array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                        );
                        $DocumentationId = $rs->Insert('FineDocumentation',$a_FineDocumentation);
                        $Message = "Documento caricato con successo.";
                        $Status = true;
                        
                    } else {
                        $Message = "Errore nel caricamento del documento: ".$imgUploader->error;
                        $Status = false;
                    }
                }
            } else {
                $Message = "Errore nel caricamento del documento: ".$imgUploader->error;
                $Status = false;
            }
        } else {
            $Message = "Errore nel caricamento del documento.";
            $Status = false;
        }
    }
}

echo json_encode(
    array(
        "Operation" => $Operation,
        "Status" => $Status,
        "Message" => $Message,
        "DocumentationId" => $DocumentationId,
        "Documentation" => $validated_name,
    )
);

