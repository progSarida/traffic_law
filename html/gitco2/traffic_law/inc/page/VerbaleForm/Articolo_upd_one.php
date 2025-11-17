<?php
    $str_AdditionalArticle = '';

    $str_CssDisplay = '';
    $str_Caret = '
        <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="upart"></i>
        <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="downart"></i>
    ';
    $str_out .= '	
                <div class="col-sm-12" id="BoxArticle_1" ' . $str_CssDisplay . '>	
                    <div class="col-sm-12" >
                        <div class="col-sm-1 BoxRowLabel">
                        Codice
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string article_change_comune" id="artcomune_1" type="text" data-number="1" style="width:6rem">
                    </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Articolo
                            <i id="ArticleSearch_1" class="glyphicon glyphicon-search" style="position:absolute; right:0.1rem;font-size:1.7rem;top:0.2rem;"></i>
                             <input type="hidden"  id="ArtNum1" value="1">
                             <input type="hidden"  name="ArticleId_1" id="ArticleId_1" value="' . $table_row['ArticleId'] . '">';

$AdditionalDescriptionIta = $rs->Select('FineOwner', 'FineId='.$table_row['Id']);
$AdditionalDescriptionIta = mysqli_fetch_array($AdditionalDescriptionIta);
$AdditionalDescriptionIta = trim($AdditionalDescriptionIta['AdditionalDescriptionIta']);


$result = $rs->SelectQuery('SELECT * FROM ArticleTariff LEFT JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id WHERE ArticleTariff.ArticleId='.$table_row['ArticleId']." AND ArticleTariff.Year=".$_SESSION['year']);
$result = mysqli_fetch_array($result);
$AdditionalSanction = $result['TitleIta'];
$addMass = $result['AdditionalMass'];


if($addMass == 1 && $table_row['VehicleMass']>MASS) $str_out.= '<input type="hidden" id="art_1" fee="' . $table_row['Fee']/2 . '" maxFee="' . $table_row['MaxFee']/2 . '" addMass="'.$addMass.'" desc="'.$str_ArticleDescription.'">';
else $str_out.= '<input type="hidden" id="art_1" fee="' . $table_row['Fee'] . '" maxFee="' . $table_row['MaxFee'] . '" addMass="'.$addMass.'" desc="'.utf8_encode($str_ArticleDescription).'">';



$str_out.='
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                        
                            <input class="form-control frm_field_numeric article_change_1" type="text" value=" ' . $table_row['Article'] . '" data-number="1" name="id1_1" id="id1_1" style="width: 8rem">   				
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Comma
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            
                            <input class="form-control frm_field_string article_change_1" type="text" value="' . $table_row['Paragraph'] . '" name="id2_1" id="id2_1" style="width: 8rem">
                        </div>
        
                        <div class="col-sm-1 BoxRowLabel">
                            Lettera
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            
                            <input class="form-control frm_field_string article_change_1" type="text" value="' . $table_row['Letter'] . '" name="id3_1" id="id3_1" style="width: 8rem"> 
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                                  Punti
                        </div>
                          
                          <div class="col-sm-1 BoxRowCaption" id="YoungLicensePoint_1">'.$result['LicensePoint'].'
                          </div>
                    </div>    
                    <div class="col-sm-12" style="height:6rem; style="position:relative;">
                        <input type="hidden" id="Article_Id_1" value="1">
                        <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                            <i class="fa fa-pencil-square-o" id="EditArticle_1" style="position:absolute; top:1px;right:25px;font-size: 2rem;"></i>
                            <div id="ArticleText_1" style="background-color:#C7EBE0; position:absolute; display: none; top:0px;left:0px; width:60rem; height;2rem">  
                                <textarea class="form-control frm_field_string" name="ArticleText_1" id="textarea_1" style="font-size:1.5rem; color:#294A9C; top:1px;left:1px; padding-left: 2rem;width:60rem; height;2rem"></textarea>
                                
                            </div>    
                            <span id="span_Article_1" style="position:relative;font-size:1rem;"> ' . utf8_encode($str_ArticleDescription) . '</span>
                            ' . $str_Caret . '
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-12">
                        <div class="col-sm-12" id="AdditionalSanction_1"';

if($AdditionalDescriptionIta!='') $str_out.= 'style="display: none"';
$str_out.='>
                            <div class="col-sm-3 BoxRowLabel">
                                Sanzione addizionale
                            </div>
                            <div class="col-sm-8 BoxRowCaption" id="AdditionalSanctionSelect_1">
                                <span>'.utf8_encode($AdditionalSanction).'</span>
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                <i class="fa fa-edit EditAdditionalSanction" id="EditAdditionalSanction_1" style="position:absolute; top:1px;right:15px;font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="col-sm-12" id="AdditionalSanctionInput_1" ';

if($AdditionalDescriptionIta=='') $str_out.= 'style="display: none"';
$str_out.='>
                            <div class="col-sm-3 BoxRowLabel">
                                Sanzione addizionale
                            </div>
                            <div class="col-sm-8 BoxRowCaption">
                                <input type="text" class="form-control frm_field_string" name="AdditionalSanctionInputText_1" id="AdditionalSanctionInputText_1" value="'.utf8_encode($AdditionalDescriptionIta).'">
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                <i class="fa fa-edit EditAdditionalSanction" id="EditAdditionalSanction_1" style="position:absolute; top:1px;right:15px;font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div> 
                    <div class="col-sm-12">
                          <div class="col-sm-3 BoxRowLabel">
                            Min edittale
                           </div>
                           <div class="col-sm-3 BoxRowCaption">
                                <input type="hidden" name="Fee_1" id="Fee_1" value="' . NumberDisplay($table_row['Fee']) . '">
                                <span id="span_Fee_1">' . NumberDisplay($table_row['Fee']) . '</span>
                           </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Max edittale
                        </div>
                         <div class="col-sm-3 BoxRowCaption">
                            <input type="hidden" name="MaxFee_1" id="MaxFee_1" value="' . NumberDisplay($table_row['MaxFee']) . '">
                            <span id="span_MaxFee_1">' . NumberDisplay($table_row['MaxFee']) . '</span>
                        </div>
                    </div>
                     
                    <div class="clean_row HSpace4"></div> 
                </div>
                ';
    if($table_row['ArticleNumber']>1) {
                $i=1;
                $i > $table_row['ArticleNumber'] ? $str_CssDisplay = 'style="display: none;"' : $str_CssDisplay = "";
                $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=$Id", "ArticleOrder");
                while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                    $i++;
                    $AdditionalFee += $r_AdditionalArticle['Fee'];

                    $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription' . LAN])) > 0) ? $r_AdditionalArticle['AdditionalArticleDescription' . LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];
                    $str_ButtonAdditionalArticle = '';
                    if ($_SESSION['userlevel'] >= 3) {
                        $str_ArticleTitle = '';
                        for ($i = 1; $i <= $n_Lan; $i++) {
                            $str_ArticleTitle .= '*' . trim($r_AdditionalArticle['AdditionalArticleDescription' . $a_Language[$i]]);
                        }

                        $str_ButtonAdditionalArticle = '
                        <span class="glyphicon glyphicon-pencil" id="' . $r_AdditionalArticle['ArticleId'] . '" alt="' . $str_ArticleTitle . '"  style="position:absolute;bottom:5px;right:5px;"></span>
                        ';
                    }


                    $str_out .= '
                        <div class="col-sm-12" id="BoxArticle_' . $i . '" ' . $str_CssDisplay . '>
                        ';
                    $addMass = $rs->Select('ArticleTariff', 'ArticleId='.$r_AdditionalArticle['ArticleId']." AND Year=".$_SESSION['year']);
                    $addMass = mysqli_fetch_array($addMass);
                    $youngLicense = $addMass['LicensePoint'];
                    $addMass = $addMass['AdditionalMass'];
    if($addMass == 1 && $table_row['VehicleMass']>MASS) $str_out.= '<input type="hidden" id="art_' . $i . '" fee="' . $r_AdditionalArticle['Fee']/2 . '" maxFee="' . $r_AdditionalArticle['MaxFee']/2 . '" addMass="'.$addMass.'" desc="'.$str_AdditionalArticleDescription.'">';
    else $str_out.= '<input type="hidden" id="art_' . $i . '" fee="' . $r_AdditionalArticle['Fee'] . '" maxFee="' . $r_AdditionalArticle['MaxFee'] . '" addMass="'.$addMass.'" desc="'.$str_AdditionalArticleDescription.'">';



    $str_out.='
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12" >
                            <div class="col-sm-1 BoxRowLabel">
                                Codice
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_string article_change_comune" id="artcomune_'.$i.'" type="text" data-number="'.$i.'" style="width:6rem">
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Articolo
                                <i id="ArticleSearch_' . $i . '" class="glyphicon glyphicon-search" style="position:absolute; right:0.1rem;font-size:1.7rem;top:0.2rem;"></i>
                                 <input type="hidden"  name="ArticleId_' . $i . '" id="ArticleId_' . $i . '" value="' . $r_AdditionalArticle['ArticleId'] . '">
                                  <input type="hidden"  id="ArtNum'.$i.'" value="'.$i.'">
                            </div>
                            <div class="col-sm-1 BoxRowCaption">       
                                <input class="form-control frm_field_numeric article_change_'.$i.'" type="text" value="' . $r_AdditionalArticle['Article'] . '" data-number="'.$i.'" name="id1_' . $i . '" id="id1_' . $i . '" style="width: 8rem">                 
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                Comma
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                              <input class="form-control frm_field_string article_change_'.$i.'" type="text" value="' . $r_AdditionalArticle['Paragraph'] . '" data-number="'.$i.'" name="id2_' . $i . '" id="id2_' . $i . '" style="width: 8rem">
                            </div>
                        
                            <div class="col-sm-1 BoxRowLabel">
                                Lettera
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_string article_change_'.$i.'" type="text" value="' . $r_AdditionalArticle['Letter'] . '" data-number="'.$i.'" name="id3_' . $i . '" id="id3_' . $i . '" style="width: 8rem"> 
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                  Punti
                             </div>
                             <div class="col-sm-1 BoxRowCaption" id="YoungLicensePoint_'.$i.'">'.$youngLicense.'
                             </div>
                        </div>
                        <div class="col-sm-12" style="height:6rem; style="position:relative;">
                            <input type="hidden" id="Article_Id_' . $i . '" value="' . $i . '">
                            <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                                <i class="fa fa-pencil-square-o" id="EditArticle_' . $i . '" style="position:absolute; top:1px;right:25px;font-size: 2rem;"></i>
                                <div id="ArticleText_' . $i . '" style="background-color:#C7EBE0; position:absolute; display: none; top:0px;left:0px; width:60rem; height;2rem">  
                                    <textarea class="form-control frm_field_string" id="textarea_'.$i.'" name="ArticleText_' . $i . '" style="font-size:1.5rem; color:#294A9C; top:1px;left:1px; padding-left: 2rem;width:60rem; height;2rem"></textarea>
                               
                                </div>    
                                <span id="span_Article_' . $i . '" style="position:relative;font-size:1rem;"> ' . utf8_encode($str_AdditionalArticleDescription) . '</span>
                                 ' . $str_ButtonAdditionalArticle . '
                            </div>
                        </div>  
                        <div class="clean_row HSpace4"></div>
                            
                            <div class="col-sm-12">
                                  <div class="col-sm-3 BoxRowLabel">
                                    Min edittale
                                   </div>
                                   <div class="col-sm-3 BoxRowCaption">
                                        <input type="hidden" name="Fee_' . $i . '" id="Fee_' . $i . '" value="' . NumberDisplay($r_AdditionalArticle['Fee']) . '">
                                        <span id="span_Fee_' . $i . '">' . NumberDisplay($r_AdditionalArticle['Fee']) . '</span>
                                   </div>
                                <div class="col-sm-3 BoxRowLabel">
                                    Max edittale
                                </div>
                                 <div class="col-sm-3 BoxRowCaption">
                                    <input type="hidden" name="MaxFee_' . $i . '" id="MaxFee_' . $i . '" value="' . NumberDisplay($r_AdditionalArticle['MaxFee']) . '">
                                    <span id="span_MaxFee_' . $i . '">' . NumberDisplay($r_AdditionalArticle['MaxFee']) . '</span>
                                </div>
                            </div>
                        </div>
                         <div class="clean_row HSpace4"></div> 
                    ';

                }
        $str_CssDisplay = 'style="display: none;"';
        $str_Caret = '';

        for ($i = $table_row['ArticleNumber']+1; $i <= 5; $i++) {

            $i > $table_row['ArticleNumber'] ? $str_CssDisplay = 'style="display: none;"' : $str_CssDisplay = "";
            $str_out .= '	
                    <div class="col-sm-12" id="BoxArticle_' . $i . '" ' . $str_CssDisplay . '>    												
                        <div class="col-sm-12" >
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
                                  <input type="hidden"  id="ArtNum'.$i.'" value="'.$i.'">
                                 <input type="hidden" id="art_'.$i.'" fee="" maxFee="" addMass="">
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                            <input type="hidden" id="article_id_'.$i.'" value="' . $table_row['Article'] . '">
                                <input class="form-control frm_field_numeric article_change_'.$i.'" type="text" name="id1_' . $i . '" id="id1_' . $i . '"  data-number="'.$i.'" style="width: 8rem">  				
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                Comma
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                
                                <input class="form-control frm_field_string article_change_'.$i.'" type="text" name="id2_' . $i . '" id="id2_' . $i . '" data-number="'.$i.'" style="width: 8rem">  				
                            </div>
        
                            <div class="col-sm-1 BoxRowLabel">
                                Lettera
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                
                            <input class="form-control frm_field_numeric article_change_'.$i.'" type="text" name="id3_' . $i . '" id="id3_' . $i . '" data-number="'.$i.'" style="width: 8rem">  				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                  Punti
                             </div>
                             <div class="col-sm-1 BoxRowCaption" id="YoungLicensePoint_'.$i.'">
                             </div>
                        </div>    					
                        <div class="col-sm-12" style="height:6rem; style="position:relative;">
                            <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                                <i class="fa fa-pencil-square-o" id="EditArticle_'.$i.'" style="position:absolute; top:1px;right:1px;font-size: 2rem; display:none"></i>
                                <div id="ArticleText_'.$i.'" style="background-color:#C7EBE0; position:absolute; display: none; top:0px;left:0px; width:60rem; height;2rem">  
                                    <textarea class="form-control frm_field_string" id="textarea_'.$i.'" name="ArticleText_'.$i.'" style="font-size:1.5rem; color:#294A9C; top:1px;left:1px; padding-left: 2rem;width:60rem; height;2rem"></textarea>
                                </div>    
                                <span id="span_Article_'.$i.'" style="position:relative;font-size:1rem;"></span>
                            </div>
                        </div>                                                                                                                                                    
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
                        <div class="clean_row HSpace4"></div>
                        
                    </div>';
                $str_CssDisplay = 'style="display: none;"';
                $str_Caret = '';
        }

    }else {
        for ($i = 2; $i <= 5; $i++) {
            $i > $table_row['ArticleNumber'] ? $str_CssDisplay = 'style="display: none;"' : $str_CssDisplay = "";
                $str_out .= '	
                    <div class="col-sm-12" id="BoxArticle_' . $i . '" ' . $str_CssDisplay . '>    												
                        <div class="col-sm-12" >
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
                                  <input type="hidden"  id="ArtNum'.$i.'" value="'.$i.'">
                                 <input type="hidden" id="art_'.$i.'" fee="" maxFee="" addMass="">
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                            <input type="hidden" id="article_id_'.$i.'" value="' . $table_row['Article'] . '">
                                <input class="form-control frm_field_numeric article_change_'.$i.'" type="text" name="id1_' . $i . '" id="id1_' . $i . '" data-number="'.$i.'" style="width: 8rem">  				
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                Comma
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_string article_change_'.$i.'" type="text" name="id2_' . $i . '" id="id2_' . $i . '" data-number="'.$i.'" style="width: 8rem">  				
                            </div>
        
                            <div class="col-sm-1 BoxRowLabel">
                                Lettera
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_string article_change_'.$i.'" type="text" name="id3_' . $i . '" id="id3_' . $i . '" data-number="'.$i.'" style="width: 8rem">  				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                  Punti
                              </div>
                              <div class="col-sm-1 BoxRowCaption" id="YoungLicensePoint_'.$i.'">
                              </div>
                        </div>    					
                        <div class="col-sm-12" style="height:6rem; style="position:relative;">
                         <input type="hidden" id="Article_Id_' . $i . '" value="' . $i . '">
                            <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                                <i class="fa fa-pencil-square-o" id="EditArticle_'.$i.'" style="position:absolute; top:1px;right:1px;font-size: 2rem; display:none"></i>
                                <div id="ArticleText_'.$i.'" style="background-color:#C7EBE0; position:absolute; display: none; top:0px;left:0px; width:60rem; height;2rem">  
                                    <textarea class="form-control frm_field_string" name="ArticleText_'.$i.'" id="textarea_'.$i.'" style="font-size:1.5rem; color:#294A9C; top:1px;left:1px; padding-left: 2rem;width:60rem; height;2rem"></textarea>
                                </div>    
                                <span id="span_Article_'.$i.'" style="position:relative;font-size:1rem;"></span>
                            </div>
                        </div>                                                                                                                                                    
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
                        <div class="clean_row HSpace4"></div>
                        
                    </div>';

            }
            $str_CssDisplay = 'style="display: none;"';
            $str_Caret = '';

}
$str_out .='
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Spese notifica
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <span id="AdditionalFee">' . $TotalCharge . '</span>
            </div>	        	
            <div class="col-sm-3 BoxRowLabel">
                Importo totale
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <span id="span_TotalFee">' . NumberDisplay($TotalFee) . '</span>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
    ';

    $str_out.= '
<script>
    $("document").ready(function () {
        $("#ReasonCode").keyup(function (e) {
            const code = $(this).val();
            let isFound = false;
            if(code==""){
                $("#ReasonId").val($("#ReasonId option:first").val());
                return;
            }
            $("#ReasonId").trigger("change");
            $("#ReasonId > option").each(function() {
                if ($(this).html().indexOf(code)>=0) {
                    $("#ReasonId").val($(this).val());
                    isFound = true;
                }
            });
            if(!isFound){
                $("#ReasonId").val($("#ReasonId option:first").val());
            }
        })
    });
</script>
    
    ';