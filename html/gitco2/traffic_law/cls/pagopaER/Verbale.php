<?php
class Verbale{
    private $IUV;
    private $Targa;
    private $DataOraAccertamento;
    private $DataOraViolazione;
    private $DataArchiviazione;
    private $DataAnnullamento;
    private $DataUltimoPagamento;
    private $DataNotifica;
    private $Anno;
    private $NumeroVerbale;
    private $Serie;
    private $TipoVeicolo;
    private $TipoViolazione;
    private $LuogoInfrazione;
    private $AgentiVerbalizzanti;
    private $CausaleDiNonEsigibilita;
    private $Marca;
    private $Modello;
    private $Colore;
    private $SanzioniAccessorie;
    private $ImportoMinimo;
    private $ImportoRidotto;
    private $ImportoARuolo;
    private $ImportoIUV;
    private $ImportoPagato;
    private $Stato;
    private $Pagabile;
    private $VerbaleStraniero;
    private $Articoli;
    private $Scorpori;
    private $CodiceEnte;
    private $IUV_ImportoMinimo;
    private $IUV_ImportoRidotto;
    private $IUV_ImportoARuolo;
    
    public function getIUV()
    {
        return $this->IUV;
    }
    
    public function getTarga()
    {
        return $this->Targa;
    }
    
    public function getDataOraAccertamento()
    {
        return $this->DataOraAccertamento;
    }
    
    public function getDataOraViolazione()
    {
        return $this->DataOraViolazione;
    }

    public function getDataArchiviazione()
    {
        return $this->DataArchiviazione;
    }

    public function getDataAnnullamento()
    {
        return $this->DataAnnullamento;
    }

    public function getDataUltimoPagamento()
    {
        return $this->DataUltimoPagamento;
    }

    public function getDataNotifica()
    {
        return $this->DataNotifica;
    }

    public function getAnno()
    {
        return $this->Anno;
    }

    public function getNumeroVerbale()
    {
        return $this->NumeroVerbale;
    }

    public function getSerie()
    {
        return $this->Serie;
    }

    public function getTipoVeicolo()
    {
        return $this->TipoVeicolo;
    }

    public function getTipoViolazione()
    {
        return $this->TipoViolazione;
    }

    public function getLuogoInfrazione()
    {
        return $this->LuogoInfrazione;
    }

    public function getAgentiVerbalizzanti()
    {
        return $this->AgentiVerbalizzanti;
    }

    public function getCausaleDiNonEsigibilita()
    {
        return $this->CausaleDiNonEsigibilita;
    }

    public function getMarca()
    {
        return $this->Marca;
    }

    public function getModello()
    {
        return $this->Modello;
    }

    public function getColore()
    {
        return $this->Colore;
    }

    public function getSanzioniAccessorie()
    {
        return $this->SanzioniAccessorie;
    }

    public function getImportoMinimo()
    {
        return $this->ImportoMinimo;
    }

    public function getImportoRidotto()
    {
        return $this->ImportoRidotto;
    }

    public function getImportoARuolo()
    {
        return $this->ImportoARuolo;
    }

    public function getImportoIUV()
    {
        return $this->ImportoIUV;
    }

    public function getImportoPagato()
    {
        return $this->ImportoPagato;
    }

    public function getStato()
    {
        return $this->Stato;
    }

    public function getPagabile()
    {
        return $this->Pagabile;
    }

    public function getVerbaleStraniero()
    {
        return $this->VerbaleStraniero;
    }

    public function getArticoli()
    {
        return $this->Articoli;
    }

    public function getScorpori()
    {
        return $this->Scorpori;
    }

    public function getCodiceEnte()
    {
        return $this->CodiceEnte;
    }

    public function getIUV_ImportoMinimo()
    {
        return $this->IUV_ImportoMinimo;
    }

    public function getIUV_ImportoRidotto()
    {
        return $this->IUV_ImportoRidotto;
    }

    public function getIUV_ImportoARuolo()
    {
        return $this->IUV_ImportoARuolo;
    }
    
    

    public function setIUV($IUV)
    {
        $this->IUV = $IUV ?? '';
    }
    
    public function setTarga($Targa)
    {
        $this->Targa = $Targa ?? '';
    }

    public function setDataOraAccertamento($DataOraAccertamento)
    {
        $this->DataOraAccertamento = $DataOraAccertamento;
    }
    
    public function setDataOraViolazione($DataOraViolazione)
    {
        $this->DataOraViolazione = $DataOraViolazione;
    }

    public function setDataArchiviazione($DataArchiviazione)
    {
        $this->DataArchiviazione = $DataArchiviazione;
    }

    public function setDataAnnullamento($DataAnnullamento)
    {
        $this->DataAnnullamento = $DataAnnullamento;
    }

    public function setDataUltimoPagamento($DataUltimoPagamento)
    {
        $this->DataUltimoPagamento = $DataUltimoPagamento;
    }

    public function setDataNotifica($DataNotifica)
    {
        $this->DataNotifica = $DataNotifica;
    }

    public function setAnno($Anno)
    {
        $this->Anno = $Anno ?? 0;
    }

    public function setNumeroVerbale($NumeroVerbale)
    {
        $this->NumeroVerbale = $NumeroVerbale ?? '';
    }

    public function setSerie($Serie)
    {
        $this->Serie = $Serie ?? '';
    }

    public function setTipoVeicolo($TipoVeicolo)
    {
        $this->TipoVeicolo = $TipoVeicolo ?? '';
    }

    public function setTipoViolazione($TipoViolazione)
    {
        $this->TipoViolazione = $TipoViolazione ?? '';
    }

    public function setLuogoInfrazione($LuogoInfrazione)
    {
        $this->LuogoInfrazione = $LuogoInfrazione ?? '';
    }

    public function setAgentiVerbalizzanti($AgentiVerbalizzanti)
    {
        $this->AgentiVerbalizzanti = $AgentiVerbalizzanti ?? '';
    }

    public function setCausaleDiNonEsigibilita($CausaleDiNonEsigibilita)
    {
        $this->CausaleDiNonEsigibilita = $CausaleDiNonEsigibilita ?? '';
    }

    public function setMarca($Marca)
    {
        $this->Marca = $Marca ?? '';
    }

    public function setModello($Modello)
    {
        $this->Modello = $Modello ?? '';
    }

    public function setColore($Colore)
    {
        $this->Colore = $Colore ?? '';
    }

    public function setSanzioniAccessorie($SanzioniAccessorie)
    {
        $this->SanzioniAccessorie = $SanzioniAccessorie ?? '';
    }

    public function setImportoMinimo($ImportoMinimo)
    {
        $this->ImportoMinimo = $ImportoMinimo ?? 0;
    }

    public function setImportoRidotto($ImportoRidotto)
    {
        $this->ImportoRidotto = $ImportoRidotto ?? 0;
    }

    public function setImportoARuolo($ImportoARuolo)
    {
        $this->ImportoARuolo = $ImportoARuolo ?? 0;
    }

    public function setImportoIUV($ImportoIUV)
    {
        $this->ImportoIUV = $ImportoIUV ?? 0;
    }

    public function setImportoPagato($ImportoPagato)
    {
        $this->ImportoPagato = $ImportoPagato ?? 0;
    }

    public function setStato($Stato)
    {
        $this->Stato = $Stato ?? '';
    }

    public function setPagabile($Pagabile)
    {
        $this->Pagabile = $Pagabile;
    }

    public function setVerbaleStraniero($VerbaleStraniero)
    {
        $this->VerbaleStraniero = $VerbaleStraniero;
    }

    public function setArticoli($Articoli)
    {
        $this->Articoli = $Articoli;
    }

    public function setScorpori($Scorpori)
    {
        $this->Scorpori = $Scorpori;
    }

    public function setCodiceEnte($CodiceEnte)
    {
        $this->CodiceEnte = $CodiceEnte ?? '';
    }

    public function setIUV_ImportoMinimo($IUV_ImportoMinimo)
    {
        $this->IUV_ImportoMinimo = $IUV_ImportoMinimo ?? '';
    }

    public function setIUV_ImportoRidotto($IUV_ImportoRidotto)
    {
        $this->IUV_ImportoRidotto = $IUV_ImportoRidotto ?? '';
    }

    public function setIUV_ImportoARuolo($IUV_ImportoARuolo)
    {
        $this->IUV_ImportoARuolo = $IUV_ImportoARuolo ?? '';
    }
}