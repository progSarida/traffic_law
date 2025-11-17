<?php
define("EMPTYSESSION_NOREDIRECT", true);

ob_start();
require_once ("../_path.php");
require_once("../inc/parameter.php");
require_once ("funzioni.php");
require_once("../cls/cls_db.php");
ob_clean();

header("Content-Type: application/json; charset=utf-8");

class rest_telecamere{
    const ESITO_OK = 1;
    const ESITO_JSON_MALFORMATO  = 2;
    const ESITO_ERRORE_INTERNO  = 3;
    const ESITO_NO_RISULTATI = 4;
    const ESITO_NO_PARAMETRI = 5;
    const ESITO_OP_SCONOSCIUTA = 6;
    
    /** @var CLS_DB $rs */
    private $rs;
    public $esito = self::ESITO_OK;
    public $messaggio;
    public $dati;
    
    function __construct($rs){
        $this->rs = $rs;
    }
    
    public function impostaEsito(int $esito, ?string $messaggio){
        $this->esito = $esito;
        $this->messaggio = $messaggio;
    }
    
    private function verificaParametri(...$parametro){
        foreach($parametro as $p){
            if (trim($p) === "") return false;
        }
        return true;
    }
    
    private function query($sql, $tipi, ...$valori){
        try{
            $stmt = mysqli_prepare($this->rs->conn, $sql);
            $stmt->bind_param($tipi, ...$valori);
            $stmt->execute();
            return $stmt->get_result();
        } catch (mysqli_sql_exception $e){
            throw $e;
        }
    }
    
    public function recuperaTelecamere($cod){
        try{
            if(!empty($cod)){
                $this->dati = $this->rs->getArrayLine($this->query("SELECT * FROM DetectorSelea WHERE Code = ?", "s", $cod), "object");
            } else {
                $this->dati = $this->rs->getResults($this->rs->SelectQuery("SELECT * FROM DetectorSelea"), "object");
            }
            if(empty($this->dati)) $this->impostaEsito(self::ESITO_NO_RISULTATI, "Nessun risultato.");
        } catch(Exception $e){
            $this->impostaEsito(self::ESITO_ERRORE_INTERNO, "Errore: ".$e->getMessage());
        }
    }
    
    public function verificaJSON($output){
        if(!json_decode($output)){
            http_response_code(500);
            echo json_encode(
                array(
                    "Esito" => self::ESITO_JSON_MALFORMATO,
                    "Messaggio" => "JSON malformato: $output",
                    "Dati" => $this->dati,
                )
            );
        } else echo $output;
    }
}

$op = CheckValue('op', 's'); //Operazione
$cod = CheckValue('cod', 's');

$rs = new CLS_DB(new cls_db_gestoreErroriJSON(false));
$rs->SetCharset("utf8");
$richiesta = new rest_telecamere($rs);

switch($op){
    case 'prendi':        $richiesta->recuperaTelecamere($cod); break;
    default:              $richiesta->impostaEsito(rest_telecamere::ESITO_OP_SCONOSCIUTA, "Operazione sconosciuta.");
}

echo json_encode(
    array(
        "Esito" => $richiesta->esito,
        "Messaggio" => $richiesta->messaggio,
        "Dati" => $richiesta->dati
    )
);

$output = ob_get_contents();

ob_end_clean();

$richiesta->verificaJSON($output);