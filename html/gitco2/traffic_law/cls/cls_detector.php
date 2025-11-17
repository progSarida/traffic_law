<?php


class cls_detector
{
    public $a_mainArticles = null;
    public $a_detectorArticles = null;
    public function __construct(array $a_articles, array $a_detectorArticles){
        $this->setArticleArray($a_articles);
        $this->a_detectorArticles = $a_detectorArticles;
    }

    public function setArticleArray(array $a_article){
        $a_return = array();
        if(count($a_article)>0){
            $temp = array(
                "Article"=>$a_article[0]['Article'],
                "Paragraph"=>$a_article[0]['Paragraph'],
                "Letter"=>$a_article[0]['Letter']
            );

            $count = 0;
            for ($i=0;$i<count($a_article);$i++){
                $row = $a_article[$i];
                if($temp['Article']!=$row['Article'] || $temp['Paragraph']!=$row['Paragraph'] || $temp['Letter']!=$row['Letter']){
                    $temp['Article'] = $row['Article'];
                    $temp['Paragraph'] = $row['Paragraph'];
                    $temp['Letter'] = $row['Letter'];
                    $count++;
                }
                $a_return[$count][] = $row;
            }
        }
        $this->a_mainArticles = $a_return;
    }

    public function getStringViewArticle(){
        if($this->a_mainArticles==null){
            echo "ERROR: SET array MAIN ARTICLES";
            return false;
            die;
        }

        if($this->a_detectorArticles==null && !is_array($this->a_detectorArticles)){
            echo "ERROR: SET array DETECTOR ARTICLES";
            return false;
            die;
        }

        $str_articles = '';
        foreach($this->a_mainArticles as $keyMainArticle=>$a_article){
            $articleName = "Articolo ".$a_article[0]['Article'];
            if($a_article[0]['Paragraph']!="")
                $articleName.= " Comma ".$a_article[0]['Paragraph'];
            if($a_article[0]['Letter']!="")
                $articleName.= " ".$a_article[0]['Letter'];

            $textArticle = "";
            foreach($a_article as $key=>$article){
                $optionText = '';
                if($article['Id1']!="")
                    $optionText.= 'Personaliz. '.$article['Id1'];
                if($article['Id2']!="")
                    $optionText.= " Comma ".$article['Id2'];
                if($article['Id3']!="")
                    $optionText.= " ".$article['Id3'];
                if($optionText!='')
                    $optionText.=' - '.StringOutDB($article['DescriptionIta']);
                else
                    $optionText= 'No personaliz. - '.StringOutDB($article['DescriptionIta']);


                for($i=0;$i<count($this->a_detectorArticles);$i++){
                    if($this->a_detectorArticles[$i]['ArticleId']==$article['Id']){
                        $textArticle = $optionText;
                    }
                }
            }

            $str_articles.='
                <div class="col-sm-2 BoxRowLabel font_small">
                    '.$articleName.'
                </div>
                <div class="col-sm-10 BoxRowCaption" style="min-height:2.2rem;height:auto;">
                   '. $textArticle.'
                </div>
                <div class="clean_row HSpace4"></div>
            ';

        }

        return $str_articles;
    }

    public function getStringUpdateArticle(){
        if($this->a_mainArticles==null){
            echo "ERROR: SET array MAIN ARTICLES";
            return false;
            die;
        }

        if($this->a_detectorArticles==null && !is_array($this->a_detectorArticles)){
            echo "ERROR: SET array DETECTOR ARTICLES";
            return false;
            die;
        }


        $str_articles = '';
        foreach($this->a_mainArticles as $keyMainArticle=>$a_article){
            $articleName = "Articolo ".$a_article[0]['Article'];
            if($a_article[0]['Paragraph']!="")
                $articleName.= " Comma ".$a_article[0]['Paragraph'];
            if($a_article[0]['Letter']!="")
                $articleName.= " ".$a_article[0]['Letter'];

            $optionsArticle = '<option></option>';
            foreach($a_article as $key=>$article){
                $optionText = '';
                if($article['Id1']!="")
                    $optionText.= 'Personaliz. '.$article['Id1'];
                if($article['Id2']!="")
                    $optionText.= " Comma ".$article['Id2'];
                if($article['Id3']!="")
                    $optionText.= " ".$article['Id3'];
                if($optionText!='')
                    $optionText.=' - '.StringOutDB($article['DescriptionIta']);
                else
                    $optionText= 'No personaliz. - '.StringOutDB($article['DescriptionIta']);

                $selectedArticle = "";
                for($i=0;$i<count($this->a_detectorArticles);$i++){
                    if($this->a_detectorArticles[$i]['ArticleId']==$article['Id']){
                        $selectedArticle = "selected";
                        break;
                    }
                }

                $optionsArticle.='
                    <option title="'.$article['DescriptionIta'].'" '.$selectedArticle.' value="'.$article['Id'].'">
                        '.$optionText.'    
                    </option>
                ';
            }

            $selectArticle = '
                <select class="frm_field_required form-control" name="DetectorArticle['.$keyMainArticle.']" style="width: 100%;">
                    '.$optionsArticle.'
                </select>
            ';

            $str_articles.='
                <div class="col-sm-2 BoxRowLabel font_small">
                    '.$articleName.'
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    '.$selectArticle.'
                </div>
                <div class="clean_row HSpace4"></div>
            ';
        }

        return $str_articles;

    }

    public function getStringUpdateReason(array $a_reasons, $fixed, $reasonId=null){

        $str_reason = '';
        $notvalid = false;
        $optionsReason = '<option></option>';
        foreach($a_reasons as $a_reason){
            if($a_reason['Id']==$reasonId){
                $selectedReason = "selected";
                if ($fixed != $a_reason['Fixed'] || $a_reason['Disabled']) $notvalid = true;
            } else {
                $selectedReason = "";
            }
            $optionsReason.='
                    <option'.($a_reason['Fixed'] != $fixed && $selectedReason == '' ? ' disabled ' : ' ').'fixed="'.$a_reason['Fixed'].'" title="'.$a_reason['DescriptionIta'].'"   '.$selectedReason.' value="'.$a_reason['Id'].'">
                        '.StringOutDB($a_reason['Progressive'].' - '.$a_reason['TitleIta']).'    
                    </option>
                ';
        }
        if($notvalid){
            $str_reason .= '
                <div class="table_caption_H col-sm-12 alert-warning">
                    <i class="fas fa-fw fa-warning col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;Il motivo contestazione differita salvato risulta disabilitato o la sua postazione non risulta congrua a quella del rilevatore.
                </div>';
        }
        
        $str_reason .= '
                <div class="col-sm-2 BoxRowLabel font_small">
                    Mancata contestazione
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    <select Id="ReasonId" name="ReasonId" class="form-control frm_field_required">
                    '.$optionsReason.'
                    </select>
                </div>
                <div class="clean_row HSpace4"></div>
            ';

        return $str_reason;
    }
    
    public function getStringUpdateAppMinN($appMinN, $del){
        
        $s_appMinN = '';
        $s_appMinN .= '
                <div class="col-sm-2 BoxRowLabel font_small">
                    App. Min. N.
                </div>
                <div class="col-sm-10 BoxRowCaption">
                     <input type="text" name="AppMinN" id="AppMinN" class="form-control" value="'.$appMinN.'">
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                    Del
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    <input type="text" name="Del" id="Del" class="calfromdate form-control frm_field_date" value="'. DateOutDB($del) .'">
                </div>
                ';
        
        return $s_appMinN;
    }

    public function getStringViewReason($a_reason){
        $str_reason = '';
        if(isset($a_reason['Id'])){
            if($a_reason['Id']>0){
                $str_reason='
                <div class="col-sm-2 BoxRowLabel font_small" style="height:7rem">
                    Mancata contestazione
                </div>
                <div class="col-sm-10 BoxRowCaption" style="min-height:7rem;height:auto;">
                    '.$a_reason['Progressive'].' - '.StringOutDB($a_reason['TitleIta']).'
                </div>
                <div class="clean_row HSpace4"></div>
            ';
            }
        }

        return $str_reason;
    }
    
    public function getStringViewAppMin($a_detector){
        $str_app_min_n = '';

        $str_app_min_n='
        <div class="col-sm-2 BoxRowLabel font_small">
            App. Min. N.
        </div>
        <div class="col-sm-10 BoxRowCaption">
            '.$a_detector['AppMinN'].'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel font_small">
            del
        </div>
        <div class="col-sm-10 BoxRowCaption">
            '.$a_detector['Del'].'
        </div>
        ';
        
        return $str_app_min_n;
    }
}