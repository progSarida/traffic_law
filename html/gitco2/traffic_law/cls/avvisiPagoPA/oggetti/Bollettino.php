<?php
class Bollettino {
    private $autorizzazione; 
    private $numeroCc; 
    private $intestatarioCc; 
    private $tipoBollettino; 
    private $causale;
    
    public function __construct(string $autorizzazione, string $numeroCc, string $intestatarioCc, int $tipoBollettino, string $causale){
        $this->autorizzazione = $autorizzazione;
        $this->numeroCc = $numeroCc;
        $this->intestatarioCc = $intestatarioCc;
        $this->tipoBollettino = $tipoBollettino;
        $this->causale = $causale;
    }
    
    /**
     * @return string
     */
    public function getAutorizzazione()
    {
        return $this->autorizzazione;
    }

    /**
     * @return string
     */
    public function getNumeroCc()
    {
        return $this->numeroCc;
    }

    /**
     * @return string
     */
    public function getIntestatarioCc()
    {
        return $this->intestatarioCc;
    }

    /**
     * @return int
     */
    public function getTipoBollettino()
    {
        return $this->tipoBollettino;
    }

    /**
     * @return string
     */
    public function getCausale()
    {
        return $this->causale;
    }

    /**
     * @param string $autorizzazione
     */
    public function setAutorizzazione(string $autorizzazione)
    {
        $this->autorizzazione = $autorizzazione;
    }

    /**
     * @param string $numeroCc
     */
    public function setNumeroCc(string $numeroCc)
    {
        $this->numeroCc = $numeroCc;
    }

    /**
     * @param string $intestatarioCc
     */
    public function setIntestatarioCc(string $intestatarioCc)
    {
        $this->intestatarioCc = $intestatarioCc;
    }

    /**
     * @param int $tipoBollettino
     */
    public function setTipoBollettino(int $tipoBollettino)
    {
        $this->tipoBollettino = $tipoBollettino;
    }

    /**
     * @param string $causale
     */
    public function setCausale(string $causale)
    {
        $this->causale = $causale;
    }

}