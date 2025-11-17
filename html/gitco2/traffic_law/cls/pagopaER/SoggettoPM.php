<?php
class SoggettoPM{
    private $TipoNaturaGiuridica;
    private $NaturaGiuridicaSoggetto;
    private $CodiceFiscale;
    private $PartitaIva;
    private $Nome;
    private $Cognome;
    private $RagioneSociale;
    private $Indirizzo;
    private $Civico;
    private $Cap;
    private $Localita;
    private $Provincia;
    private $Comune;
    private $Nazione;
    private $Email;
    
    public function getTipoNaturaGiuridica()
    {
        return $this->TipoNaturaGiuridica;
    }

    public function getNaturaGiuridicaSoggetto()
    {
        return $this->NaturaGiuridicaSoggetto;
    }

    public function getCodiceFiscale()
    {
        return $this->CodiceFiscale;
    }

    public function getPartitaIva()
    {
        return $this->PartitaIva;
    }

    public function getNome()
    {
        return $this->Nome;
    }

    public function getCognome()
    {
        return $this->Cognome;
    }

    public function getRagioneSociale()
    {
        return $this->RagioneSociale;
    }

    public function getIndirizzo()
    {
        return $this->Indirizzo;
    }

    public function getCivico()
    {
        return $this->Civico;
    }

    public function getCap()
    {
        return $this->Cap;
    }

    public function getLocalita()
    {
        return $this->Localita;
    }

    public function getProvincia()
    {
        return $this->Provincia;
    }

    public function getComune()
    {
        return $this->Comune;
    }

    public function getNazione()
    {
        return $this->Nazione;
    }

    public function getEmail()
    {
        return $this->Email;
    }
    
    

    public function setTipoNaturaGiuridica($TipoNaturaGiuridica)
    {
        $this->TipoNaturaGiuridica = $TipoNaturaGiuridica ?? '';
    }

    public function setNaturaGiuridicaSoggetto($NaturaGiuridicaSoggetto)
    {
        $this->NaturaGiuridicaSoggetto = $NaturaGiuridicaSoggetto ?? '';
    }

    public function setCodiceFiscale($CodiceFiscale)
    {
        $this->CodiceFiscale = $CodiceFiscale ?? '';
    }

    public function setPartitaIva($PartitaIva)
    {
        $this->PartitaIva = $PartitaIva ?? '';
    }

    public function setNome($Nome)
    {
        $this->Nome = $Nome ?? '';
    }

    public function setCognome($Cognome)
    {
        $this->Cognome = $Cognome ?? '';
    }

    public function setRagioneSociale($RagioneSociale)
    {
        $this->RagioneSociale = $RagioneSociale ?? '';
    }

    public function setIndirizzo($Indirizzo)
    {
        $this->Indirizzo = $Indirizzo ?? '';
    }

    public function setCivico($Civico)
    {
        $this->Civico = $Civico ?? '';
    }

    public function setCap($Cap)
    {
        $this->Cap = $Cap ?? '';
    }

    public function setLocalita($Localita)
    {
        $this->Localita = $Localita ?? '';
    }

    public function setProvincia($Provincia)
    {
        $this->Provincia = $Provincia ?? '';
    }

    public function setComune($Comune)
    {
        $this->Comune = $Comune ?? '';
    }

    public function setNazione($Nazione)
    {
        $this->Nazione = $Nazione ?? '';
    }

    public function setEmail($Email)
    {
        $this->Email = $Email ?? '';
    }
}