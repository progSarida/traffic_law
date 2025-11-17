<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$FineId = CheckValue('fine_id','n');
$a_Attachment = isset($_POST['Attach']) ? $_POST['Attach'] : array();
$a_DocsId = $_POST['DocId'];
$a_DocsTypeId = $_POST['DocTypeId'];
$a_Paths = $_POST['Path'];
$n_Fail = 0;
$a_Files = array();

$rs_Fine = $rs->Select('Fine', "Id=$FineId");
$r_Fine = $rs->getArrayLine($rs_Fine);

$ZipPath = NATIONAL_FINE.'/'.$_SESSION['cityid'].'/'.$FineId.'/';
$ZipName = "Allegati_{$_SESSION['cityid']}_{$r_Fine['ProtocolId']}_{$r_Fine['ProtocolYear']}_".date('Y-m-d_H-i').".zip";

$rs->Start_Transaction();

foreach( $a_DocsId as $key ) {
    $docCheck = isset($a_Attachment[$key]) ? 1:0;
    if($docCheck == 1) $a_Files[] = $a_Paths[$key];
    
    //echo "<br><pre>"; print_r($docCheck); echo "</pre>";
    
    if($a_DocsTypeId[$key] == 60) {
        //aggiornare DisputeDocumentation
        $a_DisputeDocumentation = array(
            array('field'=>'Attachment','selector'=>'value','type'=>'int','value'=>$docCheck)
        );
        $rs->Update('DisputeDocumentation',$a_DisputeDocumentation, 'Id='.$key. ' AND FineId='.$FineId);
        
    } else if($a_DocsTypeId[$key] >= 25 && $a_DocsTypeId[$key] <= 28 ) {
        $a_FinePresentation = array(
            array('field'=>'Attachment','selector'=>'value','type'=>'int','value'=>$docCheck)
        );
        $rs->Update('FinePresentation',$a_FinePresentation, 'Id='.$key. ' AND FineId='.$FineId);
    }
    else {
        $a_FineDocumentation = array(
            array('field'=>'Attachment','selector'=>'value','type'=>'int','value'=>$docCheck)
        );
        $rs->Update('FineDocumentation',$a_FineDocumentation, 'Id='.$key. ' AND FineId='.$FineId);
    }
}

if(!empty($a_Files)){
    $zip = new ZipArchive();
    if ($zip->open($ZipPath.$ZipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        
        foreach($a_Files as $path){
            if(file_exists(ROOT.'/'.$path)){
                $zip->addFile(ROOT.'/'.$path,basename($path));
            } else $n_Fail++;
        }
        
        $zip->close();
    }
}

$rs->End_Transaction();

echo json_encode(
    array(
        "ZipPath" => file_exists($ZipPath.$ZipName) ? NATIONAL_FINE_HTML.'/'.$_SESSION['cityid'].'/'.$FineId.'/'.$ZipName : null,
        "MissingFiles" => $n_Fail
    )
);
