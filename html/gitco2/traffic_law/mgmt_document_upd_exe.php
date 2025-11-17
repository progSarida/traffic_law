<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_file_upload.php");
include(INC."/function.php");
require(INC."/initialization.php");

 //echo "<pre>"; print_r($_POST); echo "</pre>";
 //exit

$FineId = CheckValue('FineId','n');
$a_Attachment = $_POST['Attach'];
$a_DocsId = $_POST['DocId'];
$a_DocsTypeId = $_POST['DocTypeId'];
$n_AffectedRows = 0;

$rs->Start_Transaction();

foreach( $a_DocsId as $key ) {
    $docCheck = isset($a_Attachment[$key]) ? 1:0;
    //echo "<br><pre>"; print_r($docCheck); echo "</pre>";
    
    if($a_DocsTypeId[$key] == 60) {
        //aggiornare DisputeDocumentation
        $a_DisputeDocumentation = array(
            array('field'=>'Attachment','selector'=>'value','type'=>'int','value'=>$docCheck)
        );
        $rs->Update('DisputeDocumentation',$a_DisputeDocumentation, 'Id='.$key. ' AND FineId='.$FineId);
        $n_AffectedRows += mysqli_affected_rows($rs->conn);

    } else if($a_DocsTypeId[$key] >= 25 && $a_DocsTypeId[$key] <= 28 ) {
        $a_FinePresentation = array(
            array('field'=>'Attachment','selector'=>'value','type'=>'int','value'=>$docCheck)
        );
        $rs->Update('FinePresentation',$a_FinePresentation, 'Id='.$key. ' AND FineId='.$FineId);
        $n_AffectedRows += mysqli_affected_rows($rs->conn);
    }
    else {
        /*echo "<pre>"; print_r($docCheck); echo "</pre>";
        echo "<pre>"; print_r($key); echo "</pre>";
        echo "<pre>"; print_r($FineId); echo "</pre>";*/
  
        $a_FineDocumentation = array(
            array('field'=>'Attachment','selector'=>'value','type'=>'int','value'=>$docCheck)
        );
        $rs->Update('FineDocumentation',$a_FineDocumentation, 'Id='.$key. ' AND FineId='.$FineId);
        $n_AffectedRows += mysqli_affected_rows($rs->conn);
    }  
}

$rs->End_Transaction();

if ($n_AffectedRows == 0)
    $error = "Non è stato selezionato alcun documento da allegare.";


if ($error) {
    $_SESSION['Message']['Error'] = $error;
} else {
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

header("location: ".impostaParametriUrl(array('P' => 'mgmt_document.php', 'Id' => $FineId), 'mgmt_document_upd.php' . $str_GET_Parameter));
