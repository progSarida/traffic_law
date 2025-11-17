<?php
function crea_dir( $path )
{
    if (!is_dir($path)) {
        $folder = explode("/",$path);

        $control_path = $folder[0];

        for($l=1;$l<count($folder);$l++)
        {
            $control_path .= "/".$folder[$l];
            if( is_dir( $control_path ) == false )
            {
                mkdir( $control_path );
            }
        }
    }
    return $path;
}

function sizeFormat($bytes, $unit = "", $decimals = 2) {
    $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

    $value = 0;
    if ($bytes > 0) {
        if (!array_key_exists($unit, $units)) {
            $pow = floor(log($bytes)/log(1024));
            $unit = array_search($pow, $units);
        }
        $value = ($bytes/pow(1024,floor($units[$unit])));
    }
    if (!is_numeric($decimals) || $decimals < 0) {
        $decimals = 2;
    }
    return sprintf('%.' . $decimals . 'f '.$unit, $value);
}

$lastInsertFile = array();
$output = "";
if(isset($_FILES['file']['name'][0])){
    //echo "OK";
    foreach ($_FILES['file']['name'] as $key => $values){
        $name = str_replace("'","_",$values);
        $name = str_replace(" ","",$name);
        move_uploaded_file($_FILES['file']['tmp_name'][$key],crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"])."/".$name);
        $lastInsertFile[] = str_replace("/","\\",$_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]."/".$name);
    }
}


function getDirContents($dir, &$results = array())
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

$allFile = getDirContents($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]);
$flagDimensio = true;
$flagExtention = true;

/*var_dump($lastInsertFile);

 var_dump($allFile);
die;*/
$dimension = 0;
for($i=0; $i < count($allFile); $i++){
    $path_parts = pathinfo($allFile[$i]);
    $ext = strtolower($path_parts['extension']);
    if($ext != "pdf" && $ext != "jpeg" && $ext != "jpg")
    {
        unlink($allFile[$i]);
        for($x=0;$x<count($lastInsertFile);$x++)
        {
            if($allFile[$i]==$lastInsertFile[$x]) {
                array_splice($lastInsertFile, $x, 1);
                break;
            }
        }
        array_splice($allFile, $i, 1);
        $flagExtention = false;
        $i--;
        //break;
    }
    //$dimension += filesize($allFile[$i]);
}
for($i=0; $i < count($allFile); $i++){
    $dimension += filesize($allFile[$i]);
}

if(sizeFormat($dimension,"MB") >= 2){
    for($y=0;$y<count($lastInsertFile); $y++){
        for($z=0;$z<count($allFile); $z++)
        {
            if($lastInsertFile[$y]==$allFile[$z]){
                unlink($allFile[$z]);
                array_splice($allFile, $z, 1);
                break;
            }
        }
    }
}

$output = "<div class='row-fluid' style='margin-top: 2%;'>";

for($i = 0; $i < count($allFile); $i++){
    $path_parts = pathinfo($allFile[$i]);
    $id = str_replace(")","_",str_replace("(","_",str_replace("'", "_",str_replace(" ","",$path_parts['filename']))));

    if($i%2==0&&$i!=0) $output .= "</div><div class='row-fluid' style='margin-top: 2%;'>";
    $output .= '<div class="span6" id="file_'.$i."_".$id.'"><a style="word-wrap: break-word;white-space: normal;height:auto;float: left;margin-left:2%;" target="_blank" href="inc/uploads/'.$_POST["folder_name"].'/'.$path_parts['filename'].".".$path_parts['extension'].'">'.$path_parts['filename'].".".$path_parts['extension'].'</a><img onclick="EliminaFile(\''.str_replace("\\","\\\\",$allFile[$i]).'\',\'file_'.$i."_".$id.'\');" title="Elimina file" style="width: 15px;cursor: pointer;float:right;margin-right: 2%;" src="img/elimina_icon.png"></div>';

}
$output .= "</div>";

if(!$flagExtention){
    echo json_encode(
        array(
            "content" => $output,
            "numberRow" => count($allFile),
            "error" => 1
        )
    );
    die;
}else if(sizeFormat($dimension,"MB") >= 2){

    echo json_encode(
        array(
            "content" => $output,
            "numberRow" => count($allFile),
            "error" => 2
        )
    );
    die;
}

echo json_encode(
    array(
        "content" => $output,
        "numberRow" => count($allFile),
        "error" => 0
    )
);