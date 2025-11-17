<?php 

$str_ArticleBox = '';
$str_CssDisplay = '';
$str_Caret = '
    <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none;" id="upart"></i>
    <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem; display:none;" id="downart"></i>
';
$inserted_article = $nr>0? $article:null;
$inserted_comma = $nr>0? $comma:null;
$inserted_letter = $nr>0? $letter:null;
for($i=1; $i<=5; $i++){
    $article = $i==1? $inserted_article:null;
    $comma = $i==1? $inserted_comma:null;
    $letter = $i==1? $inserted_letter:null;

    $str_ArticleBox .= '
           <div class="col-sm-12" id="BoxArticle_'.$i.'" '.$str_CssDisplay.'>     
            <div class="col-sm-12">
                <div class="col-sm-1 BoxRowLabel">
                    Codice
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string article_change_comune" id="artcomune_'.$i.'" type="text" data-number="'.$i.'" style="width:6rem">
                </div>
              <div class="col-sm-2 BoxRowLabel">
                Articolo
                    <i id="ArticleSearch_'.$i.'" class="glyphicon glyphicon-search" style="position:absolute; right:0.1rem;font-size:1.7rem;top:0.2rem;"></i>
                    <input type="hidden"  name="ArticleId_'.$i.'" id="ArticleId_'.$i.'">
              </div>
              <div class="col-sm-1 BoxRowCaption">
                  <input class="form-control frm_field_numeric article_change" value="'.$article .'" type="text" name="id1_'.$i.'" id="id1_'.$i.'" data-number="'.$i.'" style="width:8rem">            
              </div>
              <div class="col-sm-1 BoxRowLabel">
                    Comma
              </div>
              <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string article_change" value="'.$comma.'" type="text" name="id2_'.$i.'" id="id2_'.$i.'" style="width:6rem">
              </div>
    
                  <div class="col-sm-1 BoxRowLabel">
                    Lettera
              </div>
              <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string article_change" value="'.$letter.'" type="text" name="id3_'.$i.'" id="id3_'.$i.'" style="width:5rem">
              </div>
              <div class="col-sm-2 BoxRowLabel">
                  Punti
              </div>
              <div class="col-sm-1 BoxRowCaption" id="YoungLicensePoint_'.$i.'">
              </div>
            </div>  
            <div id="DayNumber_'.$i.'" style="display: none;">
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                  Day Number
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    <input type="text" name="DayNumber_180_'.$i.'" class="form-control frm_field_string" id="DayNumber_180_'.$i.'" style="width: 10rem;">
                </div>
                <div class="clean_row HSpace4"></div>
            </div>
             
            <div class="col-sm-12" style="height:6rem; style="position:relative;">
                <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                    <i class="fa fa-pencil-square-o" id="EditArticle_'.$i.'" style="position:absolute; top:1px;right:15px;font-size: 2rem; display:none"></i>
                    <div id="ArticleText_'.$i.'" style="background-color:#C7EBE0; position:absolute; display: none; top:5px;left:0px; width:60rem; height;2rem">  
                        <textarea class="form-control frm_field_string" id="textarea_'.$i.'" name="ArticleText_'.$i.'" style="font-size:1.5rem; color:#294A9C; top:1px;left:1px; padding-left: 2rem;width:60rem; height;2rem"></textarea>
                    </div>
                    <span id="span_Article_'.$i.'" style="position:relative;font-size:1rem;"></span>
                    '. $str_Caret .'
                </div>
                
            </div>';

    if($i==1) $str_ArticleBox.= '
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="col-sm-12" id="AdditionalSanction_'.$i.'">
            <div class="col-sm-3 BoxRowLabel">
            Sanzione accessoria
            </div>
            <div class="col-sm-8 BoxRowCaption" id="AdditionalSanctionSelect_'.$i.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                <i class="fa fa-edit EditAdditionalSanction" id="EditAdditionalSanction_'.$i.'" style="position:absolute; top:1px;right:15px;font-size: 2rem;"></i>
            </div>
        </div>
        <div class="col-sm-12" id="AdditionalSanctionInput_'.$i.'" style="display: none">
            <div class="col-sm-3 BoxRowLabel">
                Sanzione accessoria
            </div>
            <div class="col-sm-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="AdditionalSanctionInputText_'.$i.'" id="AdditionalSanctionInputText_'.$i.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                <i class="fa fa-edit EditAdditionalSanction" id="EditAdditionalSanction_'.$i.'" style="position:absolute; top:1px;right:15px;font-size: 2rem;"></i>
            </div>
        </div>
    </div>';


    $str_ArticleBox.='
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="col-sm-3 BoxRowLabel">
        Min edittale
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input type="hidden" name="Fee_'.$i.'" id="Fee_'.$i.'">
            <span id="span_Fee_'.$i.'"></span>
        </div>
        <div class="col-sm-3 BoxRowLabel">
            Max edittale
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input type="hidden" name="MaxFee_'.$i.'" id="MaxFee_'.$i.'">
            <span id="span_MaxFee_'.$i.'"></span>
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';
    $str_CssDisplay = 'style="display: none;"';
    $str_Caret = '';
}
