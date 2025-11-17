<?php

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         DOCUMENTATION
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////



$str_Documentation_data = '';
$str_tree = "";
$str_Img = "";

$doc_rows = $rs->Select('FineDocumentation',"FineId=".$Id." AND DocumentationTypeId=1", "Id");
$doc_n = mysqli_num_rows($doc_rows);


    if($doc_n>0){
        
        $doc_row = mysqli_fetch_array($doc_rows);
        
        $str_Folder = ($r_Fine['CountryId']=='Z000') ? 'doc/national/violation' : 'doc/foreign/violation';
        
        $File = $str_Folder.'/'.$_SESSION['cityid'].'/'.$r_Fine['Id'].'/'.$doc_row['Documentation'];
        
        if (strtolower(substr($doc_row['Documentation'],-3))=="jpg") {
            $str_Img = '
            $("#preview").attr("src","' . $File . '");
            $("#preview_img").show();
        ';
        } else if (strtolower(substr($doc_row['Documentation'],-3))=="mp4") {
            $str_Img = '
            $("#preview_video").attr("src","' . $File . '");
            $("#preview_video").show();
         ';
            
        }else{
            $str_Img = '
            $("#preview_doc").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");
            $("#preview_doc").show();
            ';
        }
        
    }


$str_tree ='
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
            var FileType = file.split('.').pop();
                
            if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\' || FileType.toLowerCase()==\'html\'){
                $("#preview_img").hide();
                $("#preview_video").hide();
                
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%; background:white;\" src=\'"+file+"\'></iframe>");
                $("#preview_doc").show();
                
            }else if(FileType.toLowerCase()==\'mp4\'){
                $("#preview_img").hide();
                $("#preview_doc").hide();
                
                $("#preview_video").attr("src",file);
                $("#preview_video").show();
                
            }else{
                $("#preview_doc").hide();
                $("#preview_video").hide();
                
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }
        });
    ';

//PEZZO PER INIBIRE LE VISUALIZZAZIONI
/*if ($_SESSION['cityid'] == 'A373' && $_SESSION['usercity'] == 'A373')
{
    $str_Documentation_data = '
        <div class="col-sm-12" >
            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                DOCUMENTAZIONE NON TROVATA
            </div>
        </div>
        <div id="FileTreeBox" class="col-sm-12 BoxRow" style="width:100%;height:10rem;">
            <div class="example">
                <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
            Error: path not found
        </div>
';
}
else
{*/
    $str_Documentation_data = '
        <div class="col-sm-12 DocumentPreview" >
            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                DOCUMENTAZIONE
            </div>
        </div>
        <div id="FileTreeBox" class="col-sm-12 BoxRow DocumentPreview" style="width:100%;height:10rem;">
            <div class="example">
                <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
            </div>
        </div>
        <div class="clean_row HSpace4 DocumentPreview"></div>
        <div id="preview_section" class="col-sm-12 BoxRow DocumentPreview" style="width:100%;height:55.2rem; position:relative;">
            <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                <img id="preview" class="iZoom"  />
                <iframe id="preview_iframe_img" style="display:none;"  ></iframe>
            </div>
            <div id="preview_doc" style="height:55rem;overflow:auto; display: none;"></div>
            <video id="preview_video" style="display: none" controls></video>
        </div>
';
/*} //CHIUSURA ELSE DELL?INIBIZIONE VISUALIZZAZIONE*/ 

