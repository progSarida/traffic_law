<?php
class CLS_MESSAGE
{
    CONST warningClass='table_caption_H col-sm-12 alert-warning';
    CONST errorClass='table_caption_H col-sm-12 alert-danger';
    CONST infoClass='table_caption_H col-sm-12 alert-success';
    
    public $messages=array();
    private $messageTemplate='<div class="$cssClass">$text</div>';
    
    public function addError(string $message){
        $this->addMessage($message,self::errorClass);
    }
    public function addWarning(string $message){
        $this->addMessage($message,self::warningClass);
    }
    public function addInfo(string $message){
        $this->addMessage($message,self::infoClass);
    }
    public function addPlainText(string $message){
        $this->messages[]=$message;
    }
    public function addMessage(string $message,string $cssClass){
        $vars = array(
            '$cssClass' => $cssClass,
            '$text' => $message,
        );
        $this->messages[]=strtr($this->messageTemplate, $vars);
    }
    public function hasOnlyInfo():bool{
       return !($this->hasError() && $this->hasWarning());
    }
    public function hasError():bool{
        foreach ($this->messages as $m)
            if(strstr($m,self::errorClass)!=false)
                return true;
        return false;
}
    public function hasWarning():bool{
        foreach ($this->messages as $m)
            if(strstr($m,self::warningClass)!=false)
                return true;
        return false;
    }

    public function getMessagesString():string{
        $m='';
        foreach ($this->messages as $msg)
            $m.=$msg;
        return $m;
    }
}

?>