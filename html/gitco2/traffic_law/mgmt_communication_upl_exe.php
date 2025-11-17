<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$allowed = array('pdf', 'png', 'jpg', 'gif');

if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

    if(!in_array(strtolower($extension), $allowed)){
        echo '{"status":"error"}';
        exit;
    }

    $CountryId = CheckValue('CountryId','s');
    $Id = CheckValue('Id','n');
    $DocumentationTypeId = CheckValue('DocumentationTypeId','n');
    $str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
    $str_P = CheckValue('P','s');


    $DocumentName = $_FILES['upl']['name'];

    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
        mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
    }
    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $Id)) {
        mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $Id, 0777);
    }




    if(move_uploaded_file($_FILES['upl']['tmp_name'], $str_Folder."/".$_SESSION['cityid']."/".$Id."/".$DocumentName)){

        $a_FineDocumentation = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
            array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
        );
        $rs->Insert('FineDocumentation',$a_FineDocumentation);

    }
}

header("Location: ". $str_P .$str_GET_Parameter."&Id=".$Id);