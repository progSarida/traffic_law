<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");



if ($_POST['validate'] =='semaforo'){
    $str_Folder = VALIDATE_FOLDER.'/'.$_SESSION['cityid'].'/SEMAFORO/';

}
if ($_POST['validate'] =='velocita'){
    $str_Folder = VALIDATE_FOLDER.'/'.$_SESSION['cityid'].'/VELOCITA/';
}
$violationFolder = PUBLIC_FOLDER."/_VIOLATION_";
$all_files = glob($str_Folder."*.*");
if (isset( $_POST['first'])) $first = $_POST['first'];
if (isset( $_POST['second'])) $second = $_POST['second'];

if(isset($_POST['image_pointer'])) $image_pointer = $_POST['image_pointer'];


if (isset($_POST['validate']) && $_POST['validate'] =='velocita'){

    if (!is_dir($violationFolder . "/" . $_SESSION['cityid'])) {
        mkdir($violationFolder . "/" . $_SESSION['cityid'], 0777);
    }
    $name = explode("/",$all_files[$image_pointer]);
    $new_name = $name[count($name)-1];

    copy($all_files[$image_pointer], $violationFolder . "/" . $_SESSION['cityid'] . "/" . $new_name);

    if (file_exists($violationFolder . "/" . $_SESSION['cityid'] . "/" . $new_name)) {
        unlink($all_files[$image_pointer]);
        $all_files_end = glob($str_Folder."*.*");
    } else {
        echo "Poblemi con la creazione del documento: ";

    }

}else {
    $array = array();
   if ($first!="" && $second!=""){
       $element_1 = explode("/",$first);
       $filename_1 = $element_1[count($element_1)-1];
       $element_2 = explode("/",$second);
       $filename_2 = $element_2[count($element_2)-1];
       array_push($array,$filename_1,$filename_2);

       if (!is_dir($violationFolder . "/" . $_SESSION['cityid'])) {
           mkdir($violationFolder . "/" . $_SESSION['cityid'], 0777);
       }
       foreach ($array as $item) {
           copy($str_Folder . "/" . $item, $violationFolder . "/" . $_SESSION['cityid'] . "/" . $item);
           if (file_exists($violationFolder . "/" . $_SESSION['cityid'] . "/" . $item)) {
               unlink($str_Folder."/".$item);
               $all_files_end = glob($str_Folder."*.*");

           } else {
               echo "Poblemi con la creazione del documento: ";

           }
       }
   }

}
if (count($all_files_end) == 0){
    header("location: imp_autovelox.php?PageTitle=Importazione/Contravvenzioni%20Autovelox");
}else{
    header("location: imp_autovelox.php?PageTitle=Importazione/Contravvenzioni%20Autovelox&validate=".$_POST['validate']."&image_pointer=$image_pointer");
}
