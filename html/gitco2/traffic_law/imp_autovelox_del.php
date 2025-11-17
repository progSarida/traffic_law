<?php
require("_path.php");
require(INC . "/parameter.php");

if ($_POST['validation'] =='semaforo'){
    $str_Folder = VALIDATE_FOLDER.'/'.$_SESSION['cityid'].'/SEMAFORO/';
}
if ($_POST['validation'] =='velocita'){
    $str_Folder = VALIDATE_FOLDER.'/'.$_SESSION['cityid'].'/VELOCITA/';
}
$all_files = glob($str_Folder."*.*");

if (isset($_POST['delete'])){

    if(isset($_POST['image_pointer'])) $image_pointer = $_POST['image_pointer'];
    $arr = array();
    if ($_POST['validation'] =='velocita'){

        unlink($all_files[$image_pointer]);

        $all_files_end = glob($str_Folder."*.*");
        if (count($all_files_end) > 0){
            echo "200";
        }else{
            echo "300";
        }

    }else{
        if (isset( $_POST['first'])) $first = $_POST['first'];
        if (isset( $_POST['second'])) $second = $_POST['second'];

        $array = array();
        if ($first!="" && $second!="") {
            $element_1 = explode("/", $first);
            $filename_1 = $element_1[count($element_1)-1];
            $element_2 = explode("/", $second);
            $filename_2 = $element_2[count($element_2)-1];
            array_push($array, $filename_1, $filename_2);
            foreach ($array as $item) {
                unlink($str_Folder.$item);
            }
        }else{
            $element_1 = explode("/", $first);
            $filename_1 = $element_1[count($element_1)-1];
            array_push($array, $filename_1);
            foreach ($array as $item) {
                unlink($str_Folder.$item);
            }
        }


        $all_files_end = glob($str_Folder."*.*");
        if (count($all_files_end) > 0){
            echo "200";
        }else{
            echo "300";
        }
    }

}


