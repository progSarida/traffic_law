<?php
class Articolo{
    private $CodiceArticolo;
    private $Comma;
    private $DescrizioneArticolo;
    private $SanzioneRidotta30Perc;
    private $SanzioneMinima;
    private $SanzioneRuolo;
    
    public function getCodiceArticolo()
    {
        return $this->CodiceArticolo;
    }

    public function getComma()
    {
        return $this->Comma;
    }

    public function getDescrizioneArticolo()
    {
        return $this->DescrizioneArticolo;
    }

    public function getSanzioneRidotta30Perc()
    {
        return $this->SanzioneRidotta30Perc;
    }

    public function getSanzioneMinima()
    {
        return $this->SanzioneMinima;
    }

    public function getSanzioneRuolo()
    {
        return $this->SanzioneRuolo;
    }
    
    

    public function setCodiceArticolo($CodiceArticolo)
    {
        $this->CodiceArticolo = $CodiceArticolo ?? '';
    }

    public function setComma($Comma)
    {
        $this->Comma = $Comma ?? '';
    }

    public function setDescrizioneArticolo($DescrizioneArticolo)
    {
        $this->DescrizioneArticolo = $DescrizioneArticolo ?? '';
    }

    public function setSanzioneRidotta30Perc($SanzioneRidotta30Perc)
    {
        $this->SanzioneRidotta30Perc = $SanzioneRidotta30Perc ?? 0;
    }

    public function setSanzioneMinima($SanzioneMinima)
    {
        $this->SanzioneMinima = $SanzioneMinima ?? 0;
    }

    public function setSanzioneRuolo($SanzioneRuolo)
    {
        $this->SanzioneRuolo = $SanzioneRuolo ?? 0;
    }
}