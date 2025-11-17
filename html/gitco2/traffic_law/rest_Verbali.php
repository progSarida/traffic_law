<?php
ob_start();
require_once ('_path.php');
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/views.php");
ob_clean();

header('Content-Type: application/json; charset=utf-8');

class RICHIESTA{
    const JSON_MALFORMATO  = 'JSON malformato';
    const ERRORE_INTERNO  = 'Errore interno';
    const NO_RISULTATI = 'Nessun risultato';
    const NO_PARAMETRI = 'Uno o più parametri sono richiesti o non validi';
    const OP_SCONOSCIUTA = 'Operazione sconosciuta';
    
    const QUERY_VERBALI = array(
        'aliases' => array(
            'Id' => 'F.Id',
            'TipoViolazione' => 'VIT.Title',
            'Cronologico' => 'F.ProtocolId',
            'Anno' => 'F.ProtocolYear',
            'DataAccertamento' => 'F.FineDate',
            'OraAccertamento' => 'F.FineTime',
            'TipoVeicolo' => 'VET.TitleIta',
            'Targa' => 'F.VehiclePlate',
            'Marca' => 'F.VehicleBrand',
            'Modello' => 'F.VehicleModel',
            'Articolo' => 'A.Article',
            'Comma' => 'A.Paragraph',
            'Lettera' => 'A.Letter',
            'Accertatore' => 'C.Name',
            'QualificaAccertatore' => 'C.Qualification',
            'Verbalizzante' => 'C2.Name',
            'QualificaVerbalizzante' => 'C2.Qualification',
        ),
        'from' => 'Fine F
            LEFT JOIN Controller C2 ON C2.Id = COALESCE(F.FineChiefControllerId, F.UIFineChiefControllerId)
            JOIN FineArticle FA ON F.Id = FA.FineId
            JOIN Article A ON A.Id = FA.ArticleId
            JOIN Controller C ON C.Id = F.ControllerId
            JOIN VehicleType VET ON F.VehicleTypeId = VET.Id
            JOIN ViolationType VIT ON FA.ViolationTypeId = VIT.Id',
        'where' => '1=1'
    );
    
    const QUERY_VERBALI_TRASGRESSORI = array(
        'aliases' => array(
            'IdVerbale' => 'FT.FineId',
            'DataNotifica' => 'COALESCE(FH.DeliveryDate, FT.FineCreateDate, FT.ReceiveDate)',
            'Codice' => 'T.Code',
            'Nome' => 'T.Name',
            'Cognome' => 'T.Surname',
            'NominativoDitta' => 'T.CompanyName',
            'Genere' => 'T.Genre',
            'CodiceFiscale' => 'T.TaxCode',
            'PIVA' => 'T.VatCode',
        ),
        'from' => 'FineTrespasser FT
            LEFT JOIN FineHistory FH ON FT.FineId = FH.FineId AND FH.NotificationTypeId = 6 AND FH.TrespasserId = FT.TrespasserId AND FT.FineCreateDate IS NULL
            JOIN Trespasser T ON T.Id = FT.TrespasserId',
        'where' => '1=1'
    );
    
    const QUERY_VERBALI_ARTICOLI = array(
        'aliases' => array(
            'IdVerbale' => 'FAA.FineId',
            'Articolo' => 'A.Article',
            'Comma' => 'A.Paragraph',
            'Lettera' => 'A.Letter',
        ),
        'from' => 'FineAdditionalArticle FAA
            JOIN Article A ON A.Id = FAA.ArticleId',
        'where' => '1=1'
    );
    
    const QUERY_VERBALI_ACCERTATORI = array(
        'aliases' => array(
            'IdVerbale' => 'FAC.FineId',
            'Nome' => 'C.Name',
            'Qualifica' => 'C.Qualification'
        ),
        'from' => 'FineAdditionalController FAC
            JOIN Controller C ON C.Id = FAC.ControllerId',
        'where' => '1=1'
    );
    
    /** @var CLS_DB $rs */
    private $rs;
    public $ente;
    public $anno;
    public $esito = 'OK';
    public $dati = array();
    public $risultati = 0;
    
    function __construct($rs){
        $this->rs = $rs;
    }
    
    private function Log(String $tipo, String $nomeMetodo, String $messaggio){
        switch($tipo){
            case 'N': trigger_error("<REST_VERBALI: $nomeMetodo> DEBUG -> $messaggio", E_USER_NOTICE); break;
            case 'W': trigger_error("<REST_VERBALI: $nomeMetodo> ATTENZIONE -> $messaggio", E_USER_WARNING); break;
            case 'D': trigger_error("<REST_VERBALI: $nomeMetodo> ERRORE -> $messaggio", E_USER_WARNING); break;
            default : trigger_error("<REST_VERBALI: $nomeMetodo> DEBUG -> $messaggio", E_USER_NOTICE); break;
        }
    }
    
    private function verificaParametri(...$parametro){
        foreach($parametro as $p){
            if (trim($p) === '') return false;
        }
        return true;
    }
    
    private function query($sql, $tipi, $valori){
        try{
            /** @var mysqli_stmt $stmt */
            $stmt = mysqli_prepare($this->rs->conn, $sql);
            $stmt->bind_param($tipi, ...$valori);
            $stmt->execute();
            return $stmt->get_result();
        } catch (mysqli_sql_exception $e){
            throw $e;
        }
    }

    public function elencaVerbali($tipoViolazione, $daData, $aData){
        if($this->verificaParametri($this->ente, $this->anno)){
            try{
                //VERBALE
                $cls_view = new CLS_VIEW(self::QUERY_VERBALI);
                $where = array('F.CityId=?', 'F.ProtocolYear=?');
                $arguments = array($this->ente, $this->anno);
                $types = 'si';
                if($tipoViolazione > 0){ $where[] = 'FA.ViolationTypeId=?'; $arguments[] = $tipoViolazione; $types .= 'i';}
                if($daData != ''){ $where[] = 'F.FineDate>=?'; $arguments[] = $daData; $types .= 's';}
                if($aData != ''){ $where[] = 'F.FineDate<=?'; $arguments[] = $aData; $types .= 's';}
                
                $sql = $cls_view->generateSelect(implode(' AND ', $where));
                $a_Verbali = $this->rs->getResults($this->query($sql, $types, $arguments), 'object');
                $a_Ids = array_column($a_Verbali, 'Id');
                
                //TRASGRESSORI
                $a_Trasgressori = array();
                $cls_view = new CLS_VIEW(self::QUERY_VERBALI_TRASGRESSORI);
                $where = array('FT.FineId IN('.implode(',', array_fill(0, count($a_Ids), '?')).')');
                $types = str_repeat('i', count($a_Ids));
                
                $sql = $cls_view->generateSelect(implode(' AND ', $where));
                $a_Result = $this->rs->getResults($this->query($sql, $types, $a_Ids), 'object');
                
                foreach ($a_Result as $item) {
                    $a_Trasgressori[$item->IdVerbale][] = $item;
                }
                
                //Articoli
                $a_Articoli = array();
                $cls_view = new CLS_VIEW(self::QUERY_VERBALI_ARTICOLI);
                $where = array('FAA.FineId IN('.implode(',', array_fill(0, count($a_Ids), '?')).')');
                $types = str_repeat('i', count($a_Ids));
                
                $sql = $cls_view->generateSelect(implode(' AND ', $where));
                $a_Result = $this->rs->getResults($this->query($sql, $types, $a_Ids), 'object');
                
                foreach ($a_Result as $item) {
                    $a_Articoli[$item->IdVerbale][] = $item;
                }
                
                //Accertatori
                $a_Accertatori = array();
                $cls_view = new CLS_VIEW(self::QUERY_VERBALI_ACCERTATORI);
                $where = array('FAC.FineId IN('.implode(',', array_fill(0, count($a_Ids), '?')).')');
                $types = str_repeat('i', count($a_Ids));
                
                $sql = $cls_view->generateSelect(implode(' AND ', $where));
                $a_Result = $this->rs->getResults($this->query($sql, $types, $a_Ids), 'object');
                
                foreach ($a_Result as $item) {
                    $a_Accertatori[$item->IdVerbale][] = $item;
                }
                
                foreach ($a_Verbali as $o_Verbale){
                    $o_Verbale->Trasgressori = $a_Trasgressori[$o_Verbale->Id] ?? array();
                    $o_Verbale->ArticoliAddizionali = $a_Articoli[$o_Verbale->Id] ?? array();
                    $o_Verbale->AccertatoriAddizionali = $a_Accertatori[$o_Verbale->Id] ?? array();
                    array_push($this->dati, $o_Verbale);
                }
                $this->risultati = count($a_Verbali);
            } catch (Exception $e){
                $this->esito = self::ERRORE_INTERNO;
                $this->Log('D', __FUNCTION__, $e->getMessage());
            }
        } else $this->esito = self::NO_PARAMETRI;
    }
    
    public function impostaRisorsa($risorsa){
        $a_Risorsa = explode('/', trim($risorsa, '/'));
        $this->ente = $a_Risorsa[0] ?? null;
        $this->anno = $a_Risorsa[1] ?? null;
    }
    
    public function verificaJSON($output){
        if(json_last_error_msg() !== false){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, "JSON malformato: ".json_last_error_msg());
            $this->esito = self::JSON_MALFORMATO;
            $this->risultati = 0;
            $this->dati = array();
            $this->stampaJSON();
            return;
        }
        if(!json_decode($output)){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, "JSON malformato: ".json_last_error_msg());
            $this->esito = self::JSON_MALFORMATO;
            $this->risultati = 0;
            $this->dati = array();
            $this->stampaJSON();
        } else echo $output;
    }
    
    public function stampaJSON(){
        echo json_encode(
            array(
                "Esito" => $this->esito,
                "Risultati" => $this->risultati,
                "Dati" => $this->dati
            )
        );
    }
}

$risorsa = CheckValue('risorsa', 's'); //Risorsa (ente/anno)
$tipoViolazione = CheckValue('tipoViolazione', 'n'); //Tipo violazione
$daData = CheckValue('daData', 's'); //Da data accertamento
$aData = CheckValue('aData', 's'); //A data accertamento

$rs = new CLS_DB(new cls_db_gestoreErroriJSON(false));
$rs->SetCharset('utf8');
$richiesta = new RICHIESTA($rs);

$richiesta->impostaRisorsa($risorsa);
$richiesta->elencaVerbali($tipoViolazione, $daData, $aData);
$richiesta->stampaJSON();

$output = ob_get_contents();

ob_end_clean();

$richiesta->verificaJSON($output);

