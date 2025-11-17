<?php
class Destinatario {
    private $cfDest;
    private $nominativoDest;
    private $indirizzoDest;
    
    public function __construct(?string $cfDest, ?string $nominativoDest, ?string $indirizzoDest){
        $this->cfDest = $cfDest;
        $this->nominativoDest = $nominativoDest;
        $this->indirizzoDest = $indirizzoDest;
    }
    
    /**
     * @return string
     */
    public function getCfDest()
    {
        return $this->cfDest;
    }

    /**
     * @return mixed
     */
    public function getNominativoDest()
    {
        return $this->nominativoDest;
    }

    /**
     * @return string
     */
    public function getIndirizzoDest()
    {
        return $this->indirizzoDest;
    }

    /**
     * @param string $cfDest
     */
    public function setCfDest(?string $cfDest)
    {
        $this->cfDest = $cfDest;
    }

    /**
     * @param string $nominativoDest
     */
    public function setNominativoDest(?string $nominativoDest)
    {
        $this->nominativoDest = $nominativoDest;
    }

    /**
     * @param string $indirizzoDest
     */
    public function setIndirizzoDest(?string $indirizzoDest)
    {
        $this->indirizzoDest = $indirizzoDest;
    }
}