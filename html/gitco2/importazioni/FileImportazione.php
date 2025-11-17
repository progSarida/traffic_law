<?php
require_once ("DatiAtto.php");
require_once ("Documento.php");
require_once ("TipiDocumento.php");
require_once ("TipiVeicolo.php");
require_once ("Articolo.php");

class FileImportazione {
    const IDENTIFICATORE_TESTATA = "@-@";
    const IDENTIFICATORE_GRUPPI_RIGHE = "#-#";
    const SEPARATORE = ";";
    const RIGA_DATIATTO = 1;
    const RIGA_ARTICOLO = 2;
    const RIGA_DOCUMENTO = 3;
    
    private $nomeFile;
    private $percorsoFile;
    private $percorsoDocumenti;
    private $nomeImportazione;
    private $risorsa;
    
    function __construct($percorsoFile){
        $this->percorsoFile = $percorsoFile;
    }
    
    private function scriviRiga($riga){
        if(!$this->verificaRisorsa()) return false;
        return fputcsv($this->risorsa, $riga, self::SEPARATORE) > 0;
    }
    
    public function verificaRisorsa(){
        return is_resource($this->risorsa);
    }
    
    public function apriImportazioneInScrittura(){
        $csvTrovati = glob("{$this->percorsoFile}/*.csv");
        if(empty($csvTrovati)){
            $nomeBase = pathinfo(tempnam($this->percorsoFile, "{$this->nomeImportazione}_"), PATHINFO_FILENAME);
            
            if(file_exists("{$this->percorsoFile}/$nomeBase")){
                if (!is_dir($this->percorsoFile."/$nomeBase.d")) {
                    mkdir($this->percorsoFile."/$nomeBase.d");
                    chmod($this->percorsoFile."/$nomeBase.d", 0770);
                }
                if (is_dir($this->percorsoFile."/$nomeBase.d")) {
                    $this->percorsoDocumenti = $this->percorsoFile."/$nomeBase.d";
                    $this->nomeFile = "$nomeBase.csv";
                    $this->risorsa = fopen($this->percorsoFile."/{$this->nomeFile}", "a");
                    
                    if(!$this->verificaRisorsa()) return false;
                    flock($this->risorsa, LOCK_EX);
                    return $this->scriviTestata($this->percorsoFile."/$nomeBase.d");
                }
            }
        } else {
            $nomeBase = pathinfo($csvTrovati[0], PATHINFO_FILENAME);
            $this->percorsoDocumenti = $this->percorsoFile."/$nomeBase.d";
            $this->nomeFile = "$nomeBase.csv";
            $this->risorsa = fopen($this->percorsoFile."/{$this->nomeFile}", "a");
            
            return $this->verificaRisorsa();
        }
        
        return false;
    }
    
    public function apriImportazioneInLettura(string $nomeFile){
        $this->nomeFile = $nomeFile;
        $this->risorsa = fopen($this->percorsoFile."/$nomeFile", "r");
        
        return $this->verificaRisorsa();
    }
    
    public function chiudiImportazione(){
        if(!$this->verificaRisorsa()) return false;
        flock($this->risorsa, LOCK_UN);
        return fclose($this->risorsa);
    }
    
    public function scriviTestata($cartellaDocumenti){
        if(!$this->verificaRisorsa()) return false;
        return $this->scriviRiga(array(self::IDENTIFICATORE_TESTATA, $this->nomeImportazione, $cartellaDocumenti));
    }
    
    public function scriviSeparatoreGruppiRighe(){
        if(!$this->verificaRisorsa()) return false;
        return $this->scriviRiga(array(self::IDENTIFICATORE_GRUPPI_RIGHE));
    }
    
    public function scriviRigaDatiAtto(DatiAtto $datiAtto){
        if(!$this->verificaRisorsa()) return false;
        
        $riga = array(
            self::RIGA_DATIATTO,
            $datiAtto->getCitta(),
            $datiAtto->getLocalita(),
            $datiAtto->getTarga(),
            $datiAtto->getNazioneTarga(),
            $datiAtto->getDataViolazione(),
            $datiAtto->getOraViolazione(),
            $datiAtto->getTipoVeicolo(),
            $datiAtto->getLuogoInfrazione(),
            $datiAtto->getRiferimento()
        );
        
        $this->scriviRiga($riga);
    }
    
    public function scriviRigaArticolo(Articolo $articolo){
        if(!$this->verificaRisorsa()) return false;
        
        $riga = array(
            self::RIGA_ARTICOLO,
            $articolo->getArticolo(),
            $articolo->getComma(),
            $articolo->getLettera(),
            $articolo->getDataScadenzaAssicurazioneRevisione()
        );
        
        $this->scriviRiga($riga);
    }
    
    public function scriviRigaDocumento(Documento $documento){
        if(!$this->verificaRisorsa()) return false;
        
        $riga = array(
            self::RIGA_DOCUMENTO,
            $documento->getNome(),
            $documento->getTipo()
        );
        
        $this->scriviRiga($riga);
    }
    
    public function leggi(){
        if(!$this->verificaRisorsa()) return false;
        
        $righe = array();
        $numeroGruppo = -1;
        $rigaTestata = fgetcsv($this->risorsa, 0, self::SEPARATORE);
        
        if($rigaTestata && $rigaTestata[0] == self::IDENTIFICATORE_TESTATA){
            $this->percorsoDocumenti = $rigaTestata[2];
            
            while (($riga = fgetcsv($this->risorsa, 0, self::SEPARATORE)) !== false){
                If($riga[0] == self::IDENTIFICATORE_GRUPPI_RIGHE){
                    $numeroGruppo++;
                }
                if($riga[0] == self::RIGA_DATIATTO){
                    $datiAtto = new DatiAtto();
                    $datiAtto->setCitta($riga[1]);
                    $datiAtto->setLocalita($riga[2]);
                    $datiAtto->setTarga($riga[3]);
                    $datiAtto->setNazioneTarga($riga[4]);
                    $datiAtto->setDataViolazione($riga[5]);
                    $datiAtto->setOraViolazione($riga[6]);
                    $datiAtto->setTipoVeicolo($riga[7]);
                    $datiAtto->setLuogoInfrazione($riga[8]);
                    $datiAtto->setRiferimento($riga[9]);
                    
                    $righe[$numeroGruppo] = $datiAtto;
                }
                
                if($riga[0] == self::RIGA_ARTICOLO){
                    $articolo = new Articolo();
                    $articolo->setArticolo($riga[1]);
                    $articolo->setComma($riga[2]);
                    $articolo->setLettera($riga[3]);
                    $articolo->setDataScadenzaAssicurazioneRevisione($riga[4]);
                    
                    if($righe[$numeroGruppo] instanceof DatiAtto) $righe[$numeroGruppo]->setArticolo($articolo);
                }
                
                if($riga[0] == self::RIGA_DOCUMENTO){
                    $documento = new Documento();
                    $documento->setNome($riga[1]);
                    $documento->setTipo($riga[2]);
                    
                    if($righe[$numeroGruppo] instanceof DatiAtto) $righe[$numeroGruppo]->setDocumento($documento);
                }
            }
        }
        
        return $righe;
    }
    
    public function getNomeImportazione(){
        return $this->nomeImportazione;
    }
    
    public function setNomeImportazione($nomeImportazione){
        $this->nomeImportazione = $nomeImportazione;
    }
    
    public function getPercorsoDocumenti(){
        return $this->percorsoDocumenti;
    }
    
    public function setPercorsoDocumenti($percorsoDocumenti){
        $this->percorsoDocumenti = $percorsoDocumenti;
    }
    
    public function getNomeFile(){
        return $this->nomeFile;
    }
    
    public function setNomeFile($nomeFile){
        $this->nomeFile = $nomeFile;
    }
}
