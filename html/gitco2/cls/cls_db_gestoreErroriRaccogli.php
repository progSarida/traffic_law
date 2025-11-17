<?php
require_once(CLS.'/cls_db_gestoreErrori.php');

class cls_db_gestoreErroriRaccogli implements cls_db_gestoreErrori{
    private $errori = array();
    private $numero = 1;
    
    public function getErrori()
    {
        return $this->errori;
    }

    public function esci(){
    }
    
    public function ErrorAlert(String $msgType, String $msgText){
        $oggettoErrore = new stdClass();
        // $msgType success(verde), info(azzurro), warning(giallo), danger(rosso)
        $oggettoErrore->Tipo = $msgType;
        $oggettoErrore->Testo = $msgText;
        $oggettoErrore->Numero = $this->numero++;
        
        $this->errori[] = $oggettoErrore;
    }
    
    public function EsecuzioneRiuscita(){
        $this->numero++;
    }
    
    public function UltimaEsecuzioneInErrore(){
        return !empty($this->errori) && $this->numero == $this->errori[count($this->errori) - 1]->Numero + 1;
    }
    
    public function AssociaAdErrore($datiUtente){
        if ($this->UltimaEsecuzioneInErrore()){
            $this->errori[count($this->errori) - 1]->DatiUtente = $datiUtente;
        }
    }
}