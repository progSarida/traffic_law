<?php
$str_AdditionalArticle = '';

$str_out .= '	
                <div class="col-sm-12" id="BoxArticle_1">	
                    <div class="col-sm-12" >
                    
                        <div class="col-sm-2 BoxRowLabel">
                            Articolo ' . $table_row['ArticleId'] . '
                            
                             <input type="hidden"  name="ArticleId_1" id="ArticleId_1" value="' . $table_row['ArticleId'] . '">';

                        $AdditionalDescriptionIta = $rs->Select('FineOwner', 'FineId='.$table_row['Id']);
                        $AdditionalDescriptionIta = mysqli_fetch_array($AdditionalDescriptionIta);
                        $AdditionalDescriptionIta = trim($AdditionalDescriptionIta['AdditionalDescriptionIta']);


                        $result = $rs->SelectQuery('SELECT AdditionalSanction.TitleIta, ArticleTariff.AdditionalMass FROM ArticleTariff LEFT JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id WHERE ArticleTariff.ArticleId='.$table_row['ArticleId']." AND ArticleTariff.Year=".$_SESSION['year']);
                        $result = mysqli_fetch_array($result);
                        $AdditionalSanction = $result['TitleIta'];
                        $addMass = $result['AdditionalMass'];


                        if($addMass == 1 && $table_row['VehicleMass']>MASS) $str_out.= '<input type="hidden" id="art_1" fee="' . $table_row['Fee']/2 . '" maxFee="' . $table_row['MaxFee']/2 . '" addMass="'.$addMass.'" desc="'.$str_ArticleDescription.'">';
                        else $str_out.= '<input type="hidden" id="art_1" fee="' . $table_row['Fee'] . '" maxFee="' . $table_row['MaxFee'] . '" addMass="'.$addMass.'" desc="'.utf8_encode($str_ArticleDescription).'">';



                        $str_out.='
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                        
                             ' . $table_row['Article'] . '  				
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Comma
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            ' . $table_row['Paragraph'] . '
                        </div>
        
                        <div class="col-sm-2 BoxRowLabel">
                            Lettera
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            
                            ' . $table_row['Letter'] . '
                        </div>
                    </div>    
                    <div class="col-sm-12" style="height:6rem; style="position:relative;">
                        <input type="hidden" id="Article_Id_1" value="1">
                        <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                             
                            <span id="span_Article_1" style="position:relative;font-size:1rem;"> ' . utf8_encode($str_ArticleDescription) . '</span>
                         
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
                            <div class="col-sm-9 BoxRowCaption" id="AdditionalSanctionSelect_1">
                                <span>'.utf8_encode($AdditionalSanction).'</span>
                            </div>
                           
                        </div>
                        <div class="col-sm-12" id="AdditionalSanctionInput_1" ';

                        if($AdditionalDescriptionIta=='') $str_out.= 'style="display: none"';
                        $str_out.='>
                            <div class="col-sm-3 BoxRowLabel">
                                Sanzione addizionale
                            </div>
                            <div class="col-sm-9 BoxRowCaption">
                                '.utf8_encode($AdditionalDescriptionIta).'
                            </div>
                      
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div> 
                    <div class="col-sm-12">
                          <div class="col-sm-3 BoxRowLabel">
                            Min edittale
                           </div>
                           <div class="col-sm-3 BoxRowCaption">
                               
                                <span id="span_Fee_1">' . NumberDisplay($table_row['Fee']) . '</span>
                           </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Max edittale
                        </div>
                         <div class="col-sm-3 BoxRowCaption">
                           
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
        $addMass = $addMass['AdditionalMass'];
        if($addMass == 1 && $table_row['VehicleMass']>MASS) $str_out.= '<input type="hidden" id="art_' . $i . '" fee="' . $r_AdditionalArticle['Fee']/2 . '" maxFee="' . $r_AdditionalArticle['MaxFee']/2 . '" addMass="'.$addMass.'" desc="'.$str_AdditionalArticleDescription.'">';
        else $str_out.= '<input type="hidden" id="art_' . $i . '" fee="' . $r_AdditionalArticle['Fee'] . '" maxFee="' . $r_AdditionalArticle['MaxFee'] . '" addMass="'.$addMass.'" desc="'.$str_AdditionalArticleDescription.'">';



        $str_out.='
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12" >
                            
                            <div class="col-sm-2 BoxRowLabel">
                                Articolo ' . $r_AdditionalArticle['ArticleId'] . '
                            
                            </div>
                            <div class="col-sm-2 BoxRowCaption">       
                                ' . $r_AdditionalArticle['Article'] . '              
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Comma
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                             ' . $r_AdditionalArticle['Paragraph'] . '
                            </div>
                        
                            <div class="col-sm-2 BoxRowLabel">
                                Lettera
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                ' . $r_AdditionalArticle['Letter'] . '
                            </div>
                        </div>
                        <div class="col-sm-12" style="height:6rem; style="position:relative;">
                            <input type="hidden" id="Article_Id_' . $i . '" value="' . $i . '">
                            <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
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

                                        <span id="span_Fee_' . $i . '">' . NumberDisplay($r_AdditionalArticle['Fee']) . '</span>
                                   </div>
                                <div class="col-sm-3 BoxRowLabel">
                                    Max edittale
                                </div>
                                 <div class="col-sm-3 BoxRowCaption">
                                    <span id="span_MaxFee_' . $i . '">' . NumberDisplay($r_AdditionalArticle['MaxFee']) . '</span>
                                </div>
                            </div>
                        </div>
                         <div class="clean_row HSpace4"></div> 
                    ';

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
