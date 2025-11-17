<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

if($_POST) {

    $Id1 = CheckValue('Id1','n');
    $RuleTypeId = CheckValue('RuleTypeId','n');
    $ArticleCounter = CheckValue('ArticleCounter','n');
    //TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
//     $VehicleTypeId = CheckValue('VehicleTypeId','n');
    
    if($ArticleCounter<=0) $ArticleCounter = 1;
    
    $str_Article = "";
    $str_Where = "RuleTypeId=". $RuleTypeId ." AND CityId='".$_SESSION['cityid']."' AND Year=".$_SESSION['year'];

    if($Id1>0) $str_Where .= " AND Id1=".$Id1;
    
    //TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
    //if($VehicleTypeId > 0) $str_Where .= " AND (VehicleTypeId IS NULL OR VehicleTypeId=$VehicleTypeId)";

    $str_Order = "Id1, Id2, Id3";


    $rs_Article = $rs->Select('V_Article', $str_Where, $str_Order);

    $str_Article .= '
        <div class="row-fluid">
            <div class="col-sm-12" >
            <div class="table_label_H col-sm-1"></div>
            <div class="table_label_H col-sm-1">Articolo</div>
            <div class="table_label_H col-sm-1">Comma</div>
            <div class="table_label_H col-sm-1">Lettera</div>
            <div class="table_label_H col-sm-1 font_small">Tipo veicolo</div>
            <div class="table_label_H col-sm-7">Descrizione</div>   

    ';
    if(mysqli_num_rows($rs_Article)==0){
        $str_Article .= '
            <div class="table_caption_H col-sm-12">
                Nessun articolo trovato
            </div>  
        ';
    } else {


        while($r_Article = mysqli_fetch_array($rs_Article)){
                //TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
//             $str_Article .= '
//                 <div class="table_caption_H col-sm-1"> 
//                  <i class="fa fa-plus-square" considervehicle="'.($r_Article['VehicleTypeId'] > 0 ? 1 : 0).'" id="'. $r_Article['Id1'] .'*'. $r_Article['Id2'] .'*'. $r_Article['Id3'] .'"></i>
                 
//                 </div>                    
            $str_Article .= '
                <div class="table_caption_H col-sm-1"> 
                    <i class="fa fa-plus-square" id="'. $r_Article['Id1'] .'*'. $r_Article['Id2'] .'*'. $r_Article['Id3'] .'"></i>
                </div>      
                <div class="table_caption_H col-sm-1">
                      '. $r_Article['Article'] .'  
                </div>
                <div class="table_caption_H col-sm-1">
                      '. $r_Article['Paragraph'] .'  
                </div>
                <div class="table_caption_H col-sm-1">
                      '. $r_Article['Letter'] .'  
                </div>
                <div class="table_caption_H col-sm-1" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                      '. $r_Article['VehicleTypeTitle'] .'  
                </div>
                <div class="table_caption_H col-sm-7" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                      '. StringOutDB($r_Article['ArticleDescriptionIta']) .'
                </div>                        
                <div class="clean_row HSpace4"></div>            
            ';

        }

    }

    $str_Article .= '
        </div>
	</div>
	';

    $str_Article .= '
    <script>
        $(".fa-plus-square").click(function(){
            var a_id = $(this).attr("id").split("*");
            var considervehicle = $(this).attr("considervehicle");
            var id1 = a_id[0];
            var id2 = a_id[1];
            var id3 = a_id[2];

            //TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
            //$("#ConsiderVehicleTypeId_'.$ArticleCounter.'").val(considervehicle);

            $("#id1_'.$ArticleCounter.'").val(id1);
            $("#id2_'.$ArticleCounter.'").val(id2);
            $("#id3_'.$ArticleCounter.'").val(id3);
            $("#id1_'.$ArticleCounter.'").focus();
            $("#BoxArticleSearch").hide();
            $("#id3_'.$ArticleCounter.'").focus();

            //specifico per mgmt_violation_upd
            $(".article_change_'.$ArticleCounter.'").change();
        });
    </script>
';

    echo json_encode(
        array(
            "Article" => $str_Article,
        )
    );



}






