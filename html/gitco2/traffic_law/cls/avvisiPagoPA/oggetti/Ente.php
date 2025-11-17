<?php
class Ente {
    private $nomeEnte;
    private $settoreEnte;
    private $infoEnte;
    private $cfEnte;
    private $cBill;
    
    public function __construct(string $nomeEnte, string $settoreEnte, string $infoEnte, string $cfEnte, string $cBill){
        $this->nomeEnte = $nomeEnte;
        $this->settoreEnte = $settoreEnte;
        $this->infoEnte = $infoEnte;
        $this->cfEnte = $cfEnte;
        $this->cBill = $cBill;
    }
    
    /**
     * @return string
     */
    public function getNomeEnte()
    {
        return $this->nomeEnte;
    }

    /**
     * @return string
     */
    public function getSettoreEnte()
    {
        return $this->settoreEnte;
    }

    /**
     * @return string
     */
    public function getInfoEnte()
    {
        return $this->infoEnte;
    }

    /**
     * @return string
     */
    public function getCfEnte()
    {
        return $this->cfEnte;
    }
    
    /**
     * @return string
     */
    public function getCBill()
    {
        return $this->cBill;
    }
    
    /**
     * @param string $nomeEnte
     */
    public function setNomeEnte(string $nomeEnte)
    {
        $this->nomeEnte = $nomeEnte;
    }

    /**
     * @param string $settoreEnte
     */
    public function setSettoreEnte(string $settoreEnte)
    {
        $this->settoreEnte = $settoreEnte;
    }

    /**
     * @param string $infoEnte
     */
    public function setInfoEnte(string $infoEnte)
    {
        $this->infoEnte = $infoEnte;
    }

    /**
     * @param string $cfEnte
     */
    public function setCfEnte(string $cfEnte)
    {
        $this->cfEnte = $cfEnte;
    }
    
    /**
     * @param string $cBill
     */
    public function setCBill(string $cBill)
    {
        $this->cBill = $cBill;
    }
}