<?php
class Scorporo{
    private $DataCompetenza;
    private $ImportoEmesso;
    private $ImportoPagato;
    private $CausaleImporto;
    private $AliquotaIva;
    private $Descrizione;
    private $VoceDiCosto;
    private $Iva;
    private $Quantita;
    
    public function getDataCompetenza()
    {
        return $this->DataCompetenza;
    }

    public function getImportoEmesso()
    {
        return $this->ImportoEmesso;
    }

    public function getImportoPagato()
    {
        return $this->ImportoPagato;
    }

    public function getCausaleImporto()
    {
        return $this->CausaleImporto;
    }

    public function getAliquotaIva()
    {
        return $this->AliquotaIva;
    }

    public function getDescrizione()
    {
        return $this->Descrizione;
    }

    public function getVoceDiCosto()
    {
        return $this->VoceDiCosto;
    }

    public function getIva()
    {
        return $this->Iva;
    }

    public function getQuantita()
    {
        return $this->Quantita;
    }
    
    

    public function setDataCompetenza($DataCompetenza)
    {
        $this->DataCompetenza = $DataCompetenza;
    }

    public function setImportoEmesso($ImportoEmesso)
    {
        $this->ImportoEmesso = $ImportoEmesso ?? 0;
    }

    public function setImportoPagato($ImportoPagato)
    {
        $this->ImportoPagato = $ImportoPagato ?? 0;
    }

    public function setCausaleImporto($CausaleImporto)
    {
        $this->CausaleImporto = $CausaleImporto ?? '';
    }

    public function setAliquotaIva($AliquotaIva)
    {
        $this->AliquotaIva = $AliquotaIva ?? '';
    }

    public function setDescrizione($Descrizione)
    {
        $this->Descrizione = $Descrizione ?? '';
    }

    public function setVoceDiCosto($VoceDiCosto)
    {
        $this->VoceDiCosto = $VoceDiCosto ?? '';
    }

    public function setIva($Iva)
    {
        $this->Iva = $Iva ?? 0;
    }

    public function setQuantita($Quantita)
    {
        $this->Quantita = $Quantita ?? 0;
    }
}