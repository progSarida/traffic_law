<?php
class VerbaleSoggetto{
    private $Verbale;
    private $Soggetto;
    
    public function getVerbale()
    {
        return $this->Verbale;
    }

    public function getSoggetto()
    {
        return $this->Soggetto;
    }
    
    

    public function setVerbale($Verbale)
    {
        $this->Verbale = $Verbale;
    }

    public function setSoggetto($Soggetto)
    {
        $this->Soggetto = $Soggetto;
    }
}