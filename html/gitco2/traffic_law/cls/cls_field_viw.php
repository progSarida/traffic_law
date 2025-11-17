<?php
class  CLS_FIELD_VIW{
    public $str_TypeCol;
    public $str_TopMargin;
    public $str_LabelCss;
    public $str_CaptionCss;

    function OpenRow($str_class){
		echo '<div class="'. $str_class .'">';
	}

    function OpenCol($n_cols){
        echo '<div class="'. $this->str_TypeCol . $n_cols .'" style="margin-top: '. $this->str_TopMargin .'rem">';
    }


    function WriteColCtn($n_cols, $str_value){
        echo '
            <div class="'. $this->str_TypeCol . $n_cols .' '. $this->str_CaptionCss .'">
                '. $str_value .'
            </div>
            ';
    }
    function WriteColLbl($n_cols, $str_value){
        echo '
            <div class="'. $this->str_TypeCol . $n_cols .' '. $this->str_LabelCss .'">
                '. $str_value .'
            </div>
            ';
    }

    function WriteCol($n_cols, $str_class, $str_value, $str_Css='', $str_TagList=''){
        echo '
            <div class="'. $this->str_TypeCol . $n_cols .' '. $str_class .'" '. $str_Css .' '.$str_TagList.'>
                '. $str_value .'
            </div>
            ';
    }

    function CloseCol(){
        echo '</div>';
    }
    function CloseRow(){
        echo '</div>';
    }


}

