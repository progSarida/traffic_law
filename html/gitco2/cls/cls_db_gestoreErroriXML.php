<?php
require_once(CLS.'/cls_db_gestoreErrori.php');

class cls_db_gestoreErroriXML implements cls_db_gestoreErrori{
    private $esci;
    
    public function __construct(bool $esci = false){
        $this->esci = $esci;
    }
    
    public function esci(){
        if ($this->esci) die;
    }
    
    public function EsecuzioneRiuscita(){
    }
    
    public function ErrorAlert(String $msgType, String $msgText){
        throw new Exception($msgText);
    }
}