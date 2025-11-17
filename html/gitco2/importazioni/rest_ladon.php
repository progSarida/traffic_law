<?php
ob_start();
require_once ('cost-sarida-gitco.php');
require_once ("../servizi/funzioni.php");
require_once ("DatiAtto.php");
require_once ("Documento.php");
require_once ("TipiDocumento.php");
require_once ("TipiVeicolo.php");
require_once ("Articolo.php");
require_once ("FileImportazione.php");
ob_clean();

const LADON_CARTELLA_DA_IMPORTARE = CARTELLA_DA_IMPORTARE."/ladon";
const LADON_CARTELLA_LOG = CARTELLA_TMP."/ladon";

if (!is_dir(LADON_CARTELLA_DA_IMPORTARE)) {
    mkdir(LADON_CARTELLA_DA_IMPORTARE);
    chmod(LADON_CARTELLA_DA_IMPORTARE, 0770);
}
if (!is_dir(LADON_CARTELLA_LOG)) {
    mkdir(LADON_CARTELLA_LOG);
    chmod(LADON_CARTELLA_LOG, 0770);
}

ini_set("display_errors", "0");
ini_set("error_log", LADON_CARTELLA_LOG."/ladon.log");
header("Content-Type: application/json; charset=utf-8");

class rest_ladon{
    const ESITO_OK = 1;
    const ESITO_JSON_MALFORMATO  = 2;
    const ESITO_ERRORE_INTERNO  = 3;
    const ESITO_NO_RISULTATI = 4;
    const ESITO_NO_PARAMETRI = 5;
    const ESITO_OP_SCONOSCIUTA = 6;
    const ESITO_GIA_PRESENTE = 7;
    
    const QUERY_TRUE = "true";
    const QUERY_FALSE = "false";
    const QUERY_FAIL = "fail";
    
    const DIR_ALTO = -1;
    const DIR_BASSO = 1;
    
    const TIPI_VEICOLO = array(
        "truck" => TipiVeicolo::AUTOCARRO,
        "bus" => TipiVeicolo::AUTOBUS,
        "van" => TipiVeicolo::FURGONE,
        "car" => TipiVeicolo::AUTOVEICOLO,
        "motorcycle" => TipiVeicolo::MOTOVEICOLO,
        "moto" => TipiVeicolo::MOTOVEICOLO, //TODO non documentato ma per qualche motivo arriva nell'xml, per ora mettiamo che corrisponde a motoveicolo, chiedere.
    );
    
    const NUM_GIORNI = 150;
    
    public $esito = self::ESITO_OK;
    public $dati;
    public $messaggio;
    private $xml;
    private $fileImportazione;
    private $assicurazioneScaduta;
    private $revisioneScaduta;
    private $timestamp;
    
    function __construct(){
        $this->dati = new DatiAtto();
        $this->fileImportazione = new FileImportazione(LADON_CARTELLA_DA_IMPORTARE);
    }
    
    private function impostaXml(string $xml){
        if(!empty($xml)){
            libxml_use_internal_errors(true);
            $errori = array();
            
            $doc = new DOMDocument();
            if($doc->loadXML($xml) && $doc->schemaValidate("Ladon.xsd")){
                $this->xml = new DOMXPath($doc);
                $this->salvaXml($doc);
            } else { //C'è stato un errore interno nella lettura dell'xml, lo ritorno
                foreach(libxml_get_errors() as $xmlError) {
                    $errori[] = $xmlError->message;
                }
                $this->impostaEsito(self::ESITO_ERRORE_INTERNO, implode(' | ', $errori));
                return false;
            }
            return true;
        } else {
            $this->impostaEsito(self::ESITO_NO_PARAMETRI, "XML vuoto.");
            return false;
        }
    }
    
    private function inizializzaFileImportazione(){
        $this->fileImportazione->setNomeImportazione("LADON");
        if(!$this->fileImportazione->apriImportazioneInScrittura()){
            throw new Exception("Errore nell'apertura del file di importazione.");
        }
    }
    
    private function salvaXml(DOMDocument $xml){
        $rand = rand();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        file_put_contents(LADON_CARTELLA_LOG."/".date('Y-m-d_H-i-s')."_$rand.xml", $xml->saveXML());
    }
    
    private function valoreNodo(string $percorso){
        $ricerca = $this->getXml()->evaluate($percorso);
        if($ricerca instanceof DOMNodeList){
            return $ricerca->length == 0 ? '' : $ricerca->item(0)->nodeValue;
        } else if($ricerca === false) return '';
        else return $ricerca;
    }
    
    private function url(string $percorso, array $parametri){
        return sprintf(
            "%s://%s%s/%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            CONTESTO_URL,
            $percorso,
            !empty($parametri) ? "?".http_build_query($parametri) : ''
        );
    }
    
    private function recuperaTelecamera(){
        $codTelecamera = $this->valoreNodo("//cam/serial");
        $direzione = "";
        
        $datiTelecamera = json_decode(file_get_contents($this->url("servizi/rest_telecamere.php", array("op" => "prendi", "cod" => $codTelecamera))));
        if($datiTelecamera && $datiTelecamera->Esito == self::ESITO_OK){
            
            switch($this->valoreNodo("//plate/direction")){
                case self::DIR_ALTO: $direzione = !empty($datiTelecamera->Dati->DirectionUp) ? " - Dir. ".$datiTelecamera->Dati->DirectionUp : ""; break;
                case self::DIR_BASSO: $direzione = !empty($datiTelecamera->Dati->DirectionDown) ? " - Dir. ".$datiTelecamera->Dati->DirectionDown : ""; break;
            }
            
            $this->dati->setLuogoInfrazione($datiTelecamera->Dati->Address.$direzione);
            $this->dati->setCitta($datiTelecamera->Dati->CityId);
            $this->dati->setLocalita($datiTelecamera->Dati->Locality);
        } else throw new Exception("Telecamera con codice: $codTelecamera non trovata.");
    }
    
    private function recuperaNazioneTarga(){
        $nazioneTarga = $this->valoreNodo("//plate/country");
        
        $datiNazione = json_decode(file_get_contents($this->url("servizi/rest_paesi.php", array("op" => "prendi", "iso3" => $nazioneTarga)), false));
        if($datiNazione && isset($datiNazione->Dati->Id)){
            $this->dati->setNazioneTarga($datiNazione->Dati->Id);
        } else throw new Exception("Nazione con codice ISO3: $nazioneTarga non trovata.");
    }
    
    private function recuperaImmagine(){
        $base64Immagine = $this->valoreNodo("//images/ocr/data");
        $nomeImmagine = null;
        $datiImmagine = null;
        
        if(!empty($base64Immagine)){
            $nomeImmagine = ($this->dati->getRiferimento() ?: intval(microtime(true) * 100))."_full.jpg";
            $datiImmagine = base64_decode($base64Immagine, true);
        } else {
            $urlImmagine = $this->valoreNodo("//images/ocr/url");
            if(!empty($urlImmagine)){
                $headers = get_headers($urlImmagine, 1);
                if (strpos($headers['Content-Type'], "image/") !== false) {
                    $nomeImmagine = pathinfo($urlImmagine, PATHINFO_BASENAME);
                    $datiImmagine = file_get_contents($urlImmagine);
                }
            }
        }
        
        if($datiImmagine && file_put_contents($this->fileImportazione->getPercorsoDocumenti()."/$nomeImmagine", $datiImmagine)){
            $this->dati->setDocumento(new Documento($nomeImmagine, TipiDocumento::FOTOGRAMMA));
        } else throw new Exception("Non è stato possibile recuperare l'immagine per targa {$this->dati->getTarga()}.");
    }
    
    private function esisteMultaSuFile(){
        //Controlla sul csv aperto se la targa è già presente attraverso un grep
        $comando = "grep \"%s\" %s 2>&1";
        $output = $cod = null;
        exec(sprintf($comando, FileImportazione::SEPARATORE.$this->dati->getTarga().FileImportazione::SEPARATORE, LADON_CARTELLA_DA_IMPORTARE."/".$this->fileImportazione->getNomeFile()), $output, $cod);
        
        switch($cod){
            case 0: 
                //La targa è presente nel file
                $this->impostaEsito(self::ESITO_GIA_PRESENTE, "Il file di importazione corrente contiene già la targa {$this->dati->getTarga()}.");
                return true;
            case 1:
                return false;
            default: throw new Exception("Errore nella lettura del file ".LADON_CARTELLA_DA_IMPORTARE."/".$this->fileImportazione->getNomeFile()." per il controllo della targa {$this->dati->getTarga()}.");
        }
    }
    
    private function esisteMulta(Articolo $articolo){
        //Verifica se esiste già sulla banca dati una multa entro NUM_GIORNI giorni per l'articolo passato
        $parametri = array(
            'op' => 'esiste',
            'ente' => $this->dati->getCitta(),
            'targa' => $this->dati->getTarga(),
            'articolo' => $articolo->getArticolo(),
            'comma' => $articolo->getComma(),
            'lettera' => $articolo->getLettera(),
            'numGiorni' => self::NUM_GIORNI,
        );
        
        $datiMulta = json_decode(file_get_contents($this->url("servizi/rest_multe.php", $parametri)));
        if($datiMulta){
            if($datiMulta->Esito == self::ESITO_OK){
                trigger_error("Trovate multe entro i ".self::NUM_GIORNI." giorni per targa {$this->dati->getTarga()} e articolo {$articolo->getArticolo()} - {$articolo->getComma()} - {$articolo->getLettera()}");
                return true;
            } else if($datiMulta->Esito == self::ESITO_NO_RISULTATI) return false;
        }

        throw new Exception("Problemi con il recupero delle multe. ".$datiMulta->Messaggio);        
    }
    
    public function impostaEsito(int $esito, ?string $messaggio){
        $this->esito = $esito;
        $this->messaggio = $messaggio;
    }
    
    public function scriviImportazione(){
        if($this->esito == self::ESITO_OK){
            $articoli = $this->dati->getArticoli();
            $documenti = $this->dati->getDocumenti();
            
            $this->fileImportazione->scriviSeparatoreGruppiRighe();
            $this->fileImportazione->scriviRigaDatiAtto($this->dati);
            foreach($articoli as $articolo){
                $this->fileImportazione->scriviRigaArticolo($articolo);
            }
            foreach($documenti as $documento){
                $this->fileImportazione->scriviRigaDocumento($documento);
            }
            $this->fileImportazione->chiudiImportazione();
        }
    }
    
    public function leggi(string $xml){
        try{
            $this->inizializzaFileImportazione();
            if($this->impostaXml($xml)){
                $this->setRevisioneScaduta($this->valoreNodo('//queries/no_inspection') == self::QUERY_TRUE);
                $this->setAssicurazioneScaduta($this->valoreNodo('//queries/no_insurance') == self::QUERY_TRUE);
                $this->setTimestamp($this->valoreNodo("//timestamp"));
                
                $dataOraViolazione = date_create($this->valoreNodo('//date'));
                $this->dati->setTarga($this->valoreNodo('//plate/value'));
                $this->dati->setTipoVeicolo(self::TIPI_VEICOLO[$this->valoreNodo("//plate/vehicle")]);
                $this->dati->setDataViolazione($dataOraViolazione->format('Y-m-d'));
                $this->dati->setOraViolazione($dataOraViolazione->format('H:i:s'));
                $this->dati->setRiferimento($this->getTimestamp()."-".$this->dati->getTarga());
                $this->recuperaTelecamera();
                $this->recuperaNazioneTarga();
                $this->recuperaImmagine();
                
                if(!$this->esisteMultaSuFile()){
                    if($this->getRevisioneScaduta()){
                        $articolo = new Articolo(80, 14, '');
                        if(!$this->esisteMulta($articolo)){
                            $this->dati->setArticolo($articolo);
                            $dataScadenzaRevisione = $this->valoreNodo('normalize-space(substring-after(//info/note[contains(text(),"Scadenza revisione:")], "Scadenza revisione:"))');
                            if($dataScadenzaRevisione) $articolo->setDataScadenzaAssicurazioneRevisione(DateTime::createFromFormat('d/m/Y', $dataScadenzaRevisione)->format('Y-m-d') ?: '');
                        }
                    }
                    
                    if($this->getAssicurazioneScaduta()){
                        $articolo = new Articolo(193, 2, '');
                        if(!$this->esisteMulta($articolo)){
                            $this->dati->setArticolo($articolo);
                            $dataScadenzaAssicurazione = $this->valoreNodo('normalize-space(substring-after(//info/note[contains(text(),"Scadenza assicurazione:")], "Scadenza assicurazione:"))');
                            if($dataScadenzaAssicurazione) $articolo->setDataScadenzaAssicurazioneRevisione(DateTime::createFromFormat('d/m/Y', $dataScadenzaAssicurazione)->format('Y-m-d') ?: '');
                        }
                    }
                    
                    //Se gli articoli da inserire sono vuoti vuol dire che sono già presenti a sistema violazioni per quanto rilevato
                    if(empty($this->dati->getArticoli())){
                        $this->impostaEsito(self::ESITO_GIA_PRESENTE, "Nessun dato da inserire.");
                    }
                }
                return true;
            }
        } catch (Exception $e) {
            $this->impostaEsito(self::ESITO_ERRORE_INTERNO, "ERRORE: ".$e->getMessage());
        }
        
        return false;
    }
    
    public function verificaJSON($output){
        if(!json_decode($output)){
            http_response_code(500);
            echo json_encode(
                array(
                    "Esito" => self::ESITO_JSON_MALFORMATO,
                    "Messaggio" => "JSON malformato: $output",
                    "Dati" => null,
                )
            );
        } else echo $output;
    }
    
    public function getXml() {return $this->xml;}
    public function getAssicurazioneScaduta() {return $this->assicurazioneScaduta;}
    public function getRevisioneScaduta() {return $this->revisioneScaduta;}
    public function getTimestamp() {return $this->timestamp;}
    public function getFileImportazione() {return $this->fileImportazione;}
    
    public function setXml($xml) {$this->xml = $xml;}
    public function setAssicurazioneScaduta($assicurazioneScaduta) {$this->assicurazioneScaduta = $assicurazioneScaduta;}
    public function setRevisioneScaduta($revisioneScaduta) {$this->revisioneScaduta = $revisioneScaduta;}
    public function setTimestamp($timestamp) {$this->timestamp = $timestamp;}
    public function setFileImportazione($fileImportazione) {$this->fileImportazione = $fileImportazione;}
}

$richiesta = new rest_ladon();
if($richiesta->leggi(implode(' ',file("php://input")))){
    $richiesta->scriviImportazione();
}

echo json_encode(
    array(
        "Esito" => $richiesta->esito,
        "Messaggio" => $richiesta->messaggio,
        "Dati" => $richiesta->dati,
        "AssicurazioneScaduta" => $richiesta->getAssicurazioneScaduta(),
        "RevisioneScaduta" => $richiesta->getRevisioneScaduta(),
    )
);

$output = ob_get_contents();

ob_end_clean();

if($richiesta->esito != rest_ladon::ESITO_OK){
    trigger_error($richiesta->messaggio, E_USER_WARNING);
} else trigger_error("{$richiesta->dati->getTarga()} : OK");

$richiesta->verificaJSON($output);