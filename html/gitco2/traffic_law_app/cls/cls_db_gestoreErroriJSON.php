<?php
require_once(CLS.'/cls_db_gestoreErrori.php');

class cls_db_gestoreErroriJSON implements cls_db_gestoreErrori{
    private $esci;
    
    public function __construct(bool $esci = false){
        $this->esci = $esci;
    }
    
    public function esci(){
        if($this->esci === true) die;
    }
    
    public function ErrorAlert(String $msgType, String $msgText){
        echo strip_tags(str_replace('<', ' <', $msgText));
    }
}