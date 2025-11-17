<?php

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         Reminder
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////


$str_CSSReminder = 'data-toggle="tab"';
$str_Reminder = "";
$str_tree_reminder = "";
$str_PDF = "";

$doc_rows = $rs->Select('FineReminder',"FineId=".$Id." AND Documentation IS NOT NULL", "Id");
$doc_n = mysqli_num_rows($doc_rows);

//Sollecito definitivo più recente usato in rate_data
$r_FineReminder = $rs->getArrayLine($rs->Select('FineReminder', "FineId=$Id AND FlowDate IS NOT NULL", "Id DESC", "1"));

if($doc_n>0){

    $doc_row = mysqli_fetch_array($doc_rows);
    $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE_HTML : FOREIGN_FINE_HTML;
    $File = $str_Folder.'/'.$_SESSION['cityid'].'/'.$r_Fine['Id'].'/'.$doc_row['Documentation'];

    $str_PDF = '
            $("#preview_rem").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");

            ';

    $str_tree_reminder ='
            $("#fileTreePDF").fileTree({ root:\''.$str_Folder.'/'.$_SESSION['cityid'].'/'.$r_Fine['Id'].'/\', script: \'jqueryFileTree.php\' }, function(file) {
            var FileType = file.substr(file.length - 3);

            $("#preview_rem").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");


        });
    ';
    $str_Reminder ='
        <div class="col-sm-12 BoxRowTitle" >
            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                SOLLECITI
            </div>
        </div>
        <div class="col-sm-12 BoxRow" style="width:100%;height:19rem;">
            <div class="example">
                <div id="fileTreePDF" class="BoxRowLabel" style="height:17rem;overflow:auto"></div>
            </div>
        </div>
        <div class="col-sm-12 BoxRow" style="width:100%;height:40.2rem;">
            <div id="preview_rem" style="height:60rem;overflow:auto;"></div>    
        </div>
    ';



} else $str_CSSReminder = ' style="color:#C43A3A; cursor:not-allowed;" ';



$str_Reminder_data = '
<div class="tab-pane" id="Reminder">            
    <div class="col-sm-12">
        '.$str_Reminder.'
    </div>
</div>
';