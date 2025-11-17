<?php

class cls_html
{
    public function getOptions (array $a_records, array $a_selection, $a_optionClass=null )
    {
        /**
         *  firstOpt => 0 or 1
         *  value => field of $a_records to put in value tag
         *  selected => value selected
         *  text => array to insert in text option. Quando si ha un elemento stringa dentro le parentesi quadre
         *  questo va sostituito con l'elemento dell'array $a_records altrimenti si utilizza direttamente la stringa.
         */


        $class = null;
        if(is_array($a_optionClass) && $a_optionClass!=null){
            $class = "class='";
            for($i=0;$i<count($a_optionClass);$i++){
                if($i>0)
                    $class.=" ";
                $class.= $a_optionClass[$i];
            }
            $class.= "'";
        }

        $option = "";
        if($a_selection['firstOpt']==1)
            $option = "<option></option>";
        for($i=0;$i<count($a_records);$i++){

            $option .= "<option ".$class." value='".$a_records[$i][$a_selection['value']]."'";
            if($a_selection['selected'] == $a_records[$i][$a_selection['value']])
                $option.= " selected";

            $option.= ">";
            if(is_array($a_selection['text'])){
                $text = "";
                for($j=0;$j<count($a_selection['text']);$j++){
                    if(substr($a_selection['text'][$j],0,1)=="[" && substr($a_selection['text'][$j],strlen($a_selection['text'][$j])-1,1)=="]"){
                        if (strpos($a_selection['text'][$j], 'Date') !== false) {
                            $text.= DateOutDB($a_records[$i][substr($a_selection['text'][$j],1, strlen($a_selection['text'][$j])-2)]);
                        }
                        else
                            $text.= $a_records[$i][substr($a_selection['text'][$j],1, strlen($a_selection['text'][$j])-2)];
                    }
                    else
                        $text.= $a_selection['text'][$j];
                }
                $option.= $text;
            }
            else{
                $option.= $a_records[$i][$a_selection['value']];
            }

            $option.= "</option>";

        }

        return $option;
    }

}

?>