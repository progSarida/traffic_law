<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/header.php");
require(INC . '/menu.php');



$rs= new CLS_DB();


$selectedFile = CheckValue('filename', 's');

//$qyq = $rs->SelectQuery('Select * from Customer');


// $filepath = glob('../traffic_law/public/_SELEA_/*.csv');
 

// print_r($filepath);


$files = glob('../traffic_law/public/_SELEA_/*.csv');

$images = glob('../traffic_law/public/_SELEA_/*.jpg');



$str_out ='
    <div class="row-fluid">
    <div class="col-sm-12">

    <div class="table_label_H col-sm-10">Elenco File</div>
    <div class="table_label_H col-sm-2">Upload file</div>
    <div class="clean_row HSpace4"></div>';
    foreach($files as $file) {
     $str_out.= '
     <div class="table_caption_H col-sm-10">
     ' . basename($file) . '
     </div>
      <div class="table_caption_H col-sm-2">
                  <div class="table_caption_H col-sm-1">
       <a href="mgmt_importa_selea.php?filename=' . basename($file) . '">
       <span class="fa fa-download"></span></a>
     
       </div>
           
      </div>
    <div class="clean_row HSpace4"></div>';

            echo '</div>

    <div class="clean_row HSpace4"></div>';
    }







if ($selectedFile) {
    $handle = fopen('../traffic_law/public/_SELEA_/'.$selectedFile, 'r');
    $delimiter =';';
    if ($handle) {
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
        
            $errorCheck = count(array_filter($images, function ($v) use ($line) {
                return $line[0] == explode('-', explode('_', basename($v))[1])[0];   
            })) > 0 ? 'OK' : 'ERROR';

            if ($line[0] == 'CARPLATE') $errorCheck = '#';

            $str_out.= '
            <div class="clean_row HSpace4"></div>
               <div class="table_caption_H col-sm-1">'. $errorCheck .'</div>
               <div class="table_caption_H col-sm-1">'. $line[0] .'</div>
               <div class="table_caption_H col-sm-2">'. $line[1] .'</div>
               <div class="table_caption_H col-sm-2">'. $line[2] .'</div>
               <div class="table_caption_H col-sm-3">'. $line[3] .'</div>
               <div class="table_caption_H col-sm-3">'. $line[4] .'</div>
            </div>';

        }
   
              

        fclose($handle);

        $str_out .= '
        <div class="clean_row HSpace4"></div>
          <a href="mgmt_importa_selea_exe.php?file='.$selectedFile.'"> <button class="btn btn-primary">Importa</button></a>';
    } else {
        $str_out .= 'ERROR: Could not open file!';
    } 
}


echo $str_out;
