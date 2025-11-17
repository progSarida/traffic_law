<?php
class Importo {
    private $nomeImporto;
    private $importo;
    private $codiceAvviso;
    private $descImporto;
    private $dataScadenza;
    private $numeroRata;
    
    public function __construct(float $importo, string $codiceAvviso, ?string $nomeImporto = null, ?string $descImporto = null, ?string $dataScadenza = null, ?int $numeroRata = null){
        $this->nomeImporto = $nomeImporto;
        $this->importo = $importo;
        $this->codiceAvviso = $codiceAvviso;
        $this->descImporto = $descImporto;
        $this->dataScadenza = $dataScadenza;
        $this->numeroRata = $numeroRata;
    }
    
    /**
     * @return string
     */
    public function getNomeImporto()
    {
        return $this->nomeImporto;
    }

    /**
     * @return float
     */
    public function getImporto()
    {
        return $this->importo;
    }
    
    /**
     * @return string
     */
    public function getCodiceAvviso()
    {
        return $this->codiceAvviso;
    }
    
    /**
     * @return string
     */
    public function getDescImporto()
    {
        return $this->descImporto;
    }
    
    /**
     * @return string
     */
    public function getDataScadenza()
    {
        return $this->dataScadenza;
    }
    
    /**
     * @return int
     */
    public function getNumeroRata()
    {
        return $this->numeroRata;
    }

    /**
     * @param string $nomeImporto
     */
    public function setNomeImporto(string $nomeImporto)
    {
        $this->nomeImporto = $nomeImporto;
    }

    /**
     * @param float $importo
     */
    public function setImporto(float $importo)
    {
        $this->importo = $importo;
    }
    
    /**
     * @param string $codiceAvviso
     */
    public function setCodiceAvviso(string $codiceAvviso)
    {
        $this->codiceAvviso = $codiceAvviso;
    }

    /**
     * @param string $descImporto
     */
    public function setDescImporto(string $descImporto)
    {
        $this->descImporto = $descImporto;
    }
    
    /**
     * @param string $dataScadenza
     */
    public function setDataScadenza(string $dataScadenza)
    {
        $this->dataScadenza = $dataScadenza;
    }
    
    /**
     * @param string $dataScadenza
     */
    public function setNumeroRata(string $numeroRata)
    {
        $this->numeroRata = $numeroRata;
    }
}

