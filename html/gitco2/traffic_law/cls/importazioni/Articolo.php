<?php
class Articolo implements JsonSerializable{
    private $articolo;
    private $comma;
    private $lettera;
    private $dataScadenzaAssicurazioneRevisione;
    
    function __construct($articolo = null, $comma = null, $lettera = null){
        $this->setArticolo($articolo);
        $this->setComma($comma);
        $this->setLettera($lettera);
    }
    
    public function jsonSerialize() {
        return array(
            'articolo' => $this->articolo,
            'comma' => $this->comma,
            'lettera' => $this->lettera,
            'dataScadenzaAssicurazioneRevisione' => $this->dataScadenzaAssicurazioneRevisione
        );
    }
    
    public function getArticolo()
    {
        return $this->articolo;
    }
    
    public function getComma()
    {
        return $this->comma;
    }
    
    public function getLettera()
    {
        return $this->lettera;
    }
    
    public function getDataScadenzaAssicurazioneRevisione()
    {
        return $this->dataScadenzaAssicurazioneRevisione;
    }
    
    public function setArticolo(?int $articolo)
    {
        $this->articolo = $articolo;
    }
    
    public function setComma(?string $comma)
    {
        $this->comma = $comma;
    }
    
    public function setLettera(?string $lettera)
    {
        $this->lettera = $lettera;
    }
    
    public function setDataScadenzaAssicurazioneRevisione(?string $dataScadenzaAssicurazioneRevisione)
    {
        $this->dataScadenzaAssicurazioneRevisione = $dataScadenzaAssicurazioneRevisione;
    }
    
}