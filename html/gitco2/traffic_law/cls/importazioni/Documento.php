<?php
class Documento implements JsonSerializable{
    private $nome;
    private $tipo;
    
    function __construct($nome = null, $tipo = null){
        $this->setNome($nome);
        $this->setTipo($tipo);
    }
    
    public function jsonSerialize() {
        return array(
            'nome' => $this->nome,
            'tipo' => $this->tipo,
        );
    }
    
    public function getNome()
    {
        return $this->nome;
    }
    
    public function getTipo()
    {
        return $this->tipo;
    }
    
    public function setNome(?string $nome)
    {
        $this->nome = $nome;
    }
    
    public function setTipo(?string $tipo)
    {
        $this->tipo = $tipo;
    }
}