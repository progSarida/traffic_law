<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$allowed = array('png', 'jpg', 'pdf', 'doc');

if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

	$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

	if(!in_array(strtolower($extension), $allowed)){
		echo '{"status":"error"}';
		exit;
	}

    $CountryId = CheckValue('CountryId','s');
    $FineId = CheckValue('FineId','n');
    $DisputeId = CheckValue('DisputeId','n');

    $str_Folder = ($CountryId=='Z000') ? NATIONAL_DISPUTE : FOREIGN_DISPUTE;

    $DocumentName = $_FILES['upl']['name'];
    if (!is_dir($str_Folder."/".$_SESSION['cityid'])) {
        mkdir($str_Folder."/".$_SESSION['cityid'], 0777);
    }

    $a_fineDisputes = $rs->getResults($rs->ExecuteQuery("SELECT * FROM FineDispute WHERE DisputeId=".$DisputeId));

    foreach($a_fineDisputes as $a_fineDispute){
        if (!is_dir($str_Folder."/".$_SESSION['cityid']."/".$a_fineDispute['FineId'])) {
            mkdir($str_Folder."/".$_SESSION['cityid']."/".$a_fineDispute['FineId'], 0777);
        }

        if(move_uploaded_file($_FILES['upl']['tmp_name'], $str_Folder."/".$_SESSION['cityid']."/".$a_fineDispute['FineId']."/".$DocumentName)){
            $copyFile = $str_Folder."/".$_SESSION['cityid']."/".$a_fineDispute['FineId']."/".$DocumentName;
            $a_DisputeDocumentation = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$a_fineDispute['FineId'],'settype'=>'int'),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
            );
            $rs->Insert('DisputeDocumentation',$a_DisputeDocumentation);
        }
        else if(copy($copyFile,$str_Folder."/".$_SESSION['cityid']."/".$a_fineDispute['FineId']."/".$DocumentName)){
            $a_DisputeDocumentation = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$a_fineDispute['FineId'],'settype'=>'int'),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
            );
            $rs->Insert('DisputeDocumentation',$a_DisputeDocumentation);
        }
        else{
            echo '{"status":"error"}';
            exit;
        }
    }

}

echo '{"status":"error"}';
exit;