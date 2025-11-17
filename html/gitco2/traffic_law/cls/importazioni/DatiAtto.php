<?php
class DatiAtto implements JsonSerializable {
    private $citta;
    private $localita;
    private $riferimento;
    private $luogoInfrazione;
    private $targa;
    private $nazioneTarga;
    private $tipoVeicolo;
    private $dataViolazione;
    private $oraViolazione;
    private $articoli = array();
    private $documenti = array();
    
    public function jsonSerialize() {
        return array(
            'citta' => $this->citta,
            'localita' => $this->localita,
            'riferimento' => $this->riferimento,
            'luogoInfrazione' => $this->luogoInfrazione,
            'targa' => $this->targa,
            'nazioneTarga' => $this->nazioneTarga,
            'tipoVeicolo' => $this->tipoVeicolo,
            'dataViolazione' => $this->dataViolazione,
            'oraViolazione' => $this->oraViolazione,
            'articoli' => $this->articoli,
            'documenti' => $this->documenti,
        );
    }
    
    public function getCitta()
    {
        return $this->citta;
    }
    
    public function getLocalita()
    {
        return $this->localita;
    }
    
    public function getNazioneTarga()
    {
        return $this->nazioneTarga;
    }
    
    public function getTipoVeicolo()
    {
        return $this->tipoVeicolo;
    }
    
    public function getRiferimento()
    {
        return $this->riferimento;
    }

    public function getLuogoInfrazione()
    {
        return $this->luogoInfrazione;
    }

    public function getTarga()
    {
        return $this->targa;
    }

    public function getDataViolazione()
    {
        return $this->dataViolazione;
    }

    public function getOraViolazione()
    {
        return $this->oraViolazione;
    }

    public function setRiferimento(string $riferimento)
    {
        $this->riferimento = $riferimento;
    }

    public function setLuogoInfrazione(string $luogoInfrazione)
    {
        $this->luogoInfrazione = $luogoInfrazione;
    }

    public function setTarga(string $targa)
    {
        $this->targa = $targa;
    }

    public function setDataViolazione(string $dataViolazione)
    {
        $this->dataViolazione = $dataViolazione;
    }

    public function setOraViolazione(string $oraViolazione)
    {
        $this->oraViolazione = $oraViolazione;
    }

    public function setCitta($citta)
    {
        $this->citta = $citta;
    }
    
    public function setLocalita($localita)
    {
        $this->localita = $localita;
    }
    
    public function setNazioneTarga($nazioneTarga)
    {
        $this->nazioneTarga = $nazioneTarga;
    }
    
    public function setTipoVeicolo($tipoVeicolo)
    {
        $this->tipoVeicolo = $tipoVeicolo;
    }
    
    public function getArticoli()
    {
        return $this->articoli;
    }
    
    public function getDocumenti()
    {
        return $this->documenti;
    }
    
    public function setArticolo(Articolo $articolo)
    {
        $this->articoli[] = $articolo;
    }
    
    public function setDocumento(Documento $documento)
    {
        $this->documenti[] = $documento;
    }
}