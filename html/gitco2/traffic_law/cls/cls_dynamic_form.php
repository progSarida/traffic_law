<?php

class cls_dynamic_form
{
    private $NationalityId;
    private $FormTypeId;
    private $LanguageId;
    private $CityId;

    private $a_Form = array();
    private $a_Variables = array();

    public $ModelContent;
    public $Content;

    public function __construct($params){
        $this->NationalityId = $params['NationalityId'];
        $this->FormTypeId = $params['FormTypeId'];
        $this->LanguageId = $params['LanguageId'];
        $this->CityId = $params['CityId'];
    }

    public function setForm(array $a_form, array $a_variables){
        $this->a_Form = $a_form;
        if(isset($this->a_Form['Content']))
            $this->ModelContent = $this->a_Form['Content'];
        else
            $this->ModelContent = null;
        $this->a_Variables = $a_variables;
    }

    public function replaceText($search, $value){
        $this->Content = str_replace($search,$value,$this->Content);
    }

    public function replaceVariables(){

        $this->Content = $this->ModelContent;
        
        while(strpos($this->Content,"{{") > -1){
            foreach($this->a_Variables as $a_Variable){

                if(strpos($this->Content,$a_Variable['Id'])!==false){
                    $this->replaceText($a_Variable['Id'],$a_Variable['Content']);
                }

            }
        }

    }

    public function replaceKeywords(array $a_keywords){
        foreach($a_keywords as $key=>$value){
            $keyId = "{".$key."}";
            $checkVar = 0;
            while($checkVar==0){
                if (strpos($this->Content, $keyId) !== false) {
                    $this->replaceText($keyId,$value);
                }
                else
                    $checkVar=1;
            }
        }
    }

    public function getFormQuery(){
        $query = '
            SELECT * FROM FormDynamic 
            WHERE NationalityId="'.$this->NationalityId.'" AND FormTypeId = '.$this->FormTypeId.' 
            AND LanguageId = '.$this->LanguageId.' AND CityId = "'.$this->CityId.'"
        ';
        return $query;
    }

    public function getKeywordsQuery(){
        $query = '
            SELECT * FROM FormKeyword 
            WHERE NationalityId="'.$this->NationalityId.'" AND FormTypeId = '.$this->FormTypeId.' 
            AND LanguageId = '.$this->LanguageId.' AND CityId = "'.$this->CityId.'"
        ';
        return $query;
    }

    public function getVariablesQuery(){
        $query = '
            SELECT * FROM FormVariable 
            WHERE NationalityId="'.$this->NationalityId.'" AND FormTypeId = '.$this->FormTypeId.' 
            AND LanguageId = '.$this->LanguageId.' AND CityId = "'.$this->CityId.'"
        ';
        return $query;
    }

}

?>