<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

if($_POST) {

    $Protocolloid = CheckValue('Protocolloid','s');

    $str_ProtocolId = "";
    $str_Where = "ProtocolId LIKE '%". $Protocolloid ."%'";

    $rs_ProtocolId = $rs->Select('Fine', $str_Where);

    $str_ProtocolId .= '
        <div class="row-fluid">
            <div class="col-sm-12" >
            <div class="table_label_H col-sm-3"></div>
            <div class="table_label_H col-sm-3">ProtocolId</div>
            <div class="table_label_H col-sm-3">ProtocolYear</div>
            <div class="table_label_H col-sm-3">FineId</div>
        </div>
    ';
    if(mysqli_num_rows($rs_ProtocolId)==0){
        $str_ProtocolId .= '
            <div class="table_caption_H col-sm-12">
                Nessun protocollo trovato
            </div>  
        ';
    } else {


        while($r_ProtocolId = mysqli_fetch_array($rs_ProtocolId)){
            $str_ProtocolId .= '

                <div class="table_caption_H col-sm-3"> 
                 <i class="fa fa-plus-square" ProtocolId="'.$r_ProtocolId['ProtocolId'].'" id="'. $r_ProtocolId['Id'] .'"></i>
                </div>                    
                <div class="table_caption_H col-sm-3">
                      '. $r_ProtocolId['ProtocolId'] .' 
                </div>
                <div class="table_caption_H col-sm-3">
                      '. $r_ProtocolId['ProtocolYear'] .'  
                </div>
                <div class="table_caption_H col-sm-3">
                      '. $r_ProtocolId['Id'] .'  
                </div>                      
                <div class="clean_row HSpace4"></div>            
            ';

        }

    }

    $str_ProtocolId .= '
        </div>
  </div>
  ';

    $str_ProtocolId .= '
     <script>
         $(".fa-plus-square").click(function(){
             var p_id = $(this).attr("id");
             var Protocolloid = $(this).attr("ProtocolId");
             $("#Protocol_id").val(p_id);
             $("#Protocol_idsearch").text(Protocolloid);
             $("#BoxArticleSearch").hide();
         });
     </script>
    ';
    
    echo json_encode(
        array(
            "Protocolloid" => $str_ProtocolId,
        )
    );



}






