<?php
class Avviso {
    private $oggettoAvviso;
    private $percorsoLogo;
    private $dataScadenza;
    
    public function __construct(string $oggettoAvviso, string $percorsoLogo, ?string $dataScadenza = null){
        $this->oggettoAvviso = $oggettoAvviso;
        $this->percorsoLogo = $percorsoLogo;
        $this->dataScadenza = $dataScadenza;
    }
    
    /**
     * @return string
     */
    public function getOggettoAvviso()
    {
        return $this->oggettoAvviso;
    }

    /**
     * @return string
     */
    public function getPercorsoLogo()
    {
        return $this->percorsoLogo;
    }

    /**
     * @return string
     */
    public function getDataScadenza()
    {
        return $this->dataScadenza;
    }

    /**
     * @param string $oggettoAvviso
     */
    public function setOggettoAvviso(string $oggettoAvviso)
    {
        $this->oggettoAvviso = $oggettoAvviso;
    }

    /**
     * @param string $percorsoLogo
     */
    public function setPercorsoLogo(string $percorsoLogo)
    {
        $this->percorsoLogo = $percorsoLogo;
    }

    /**
     * @param string $dataScadenza
     */
    public function setDataScadenza(?string $dataScadenza)
    {
        $this->dataScadenza = $dataScadenza;
    }
}

