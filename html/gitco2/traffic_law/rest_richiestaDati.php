<?php
define("EMPTYSESSION_NOREDIRECT", true);

ob_start();
require_once ('_path.php');
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
ob_clean();

header('Content-Type: application/json; charset=utf-8');

class RICHIESTA{
    const JSON_MALFORMATO  = 'JSON malformato';
    const ERRORE_INTERNO  = 'Errore interno';
    const NO_RISULTATI = 'Nessun risultato';
    const NO_PARAMETRI = 'Uno o più parametri sono richiesti';
    const OP_SCONOSCIUTA = 'Operazione sconosciuta';
        
    private $rs;
    public $esito = 'OK';
    public $dati = array();
    
    function __construct($rs){
        $this->rs = $rs;
    }
    
    private function verificaParametri(...$parametro){
        foreach($parametro as $p){
            if (trim($p) === '') return false;
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
    
    public function prendiFineTrespasser($cv, $nv, $yv, $dC, $hC, $pv){
        if($this->verificaParametri($cv, $nv, $yv, $dC, $hC, $pv)){
            $o_Verbale = new stdClass();
            $a_Trasgressori = array();
            
            try{
                $r_Fine = $this->rs->getArrayLine($this->query(
                    "SELECT Id FineId, Address FROM Fine WHERE CityId=? AND ProtocolId=? AND ProtocolYear=? AND FineDate=? AND DATE_FORMAT(FineTime,'%H:%i')=? AND REPLACE(VehiclePlate,' ','')=?",
                    'siisss',
                    $cv, $nv, $yv, $dC, $hC, $pv));
                if($r_Fine){
                    foreach($r_Fine as $indice => $valore){
                        $o_Verbale->$indice = $valore;
                    }
                    $rs_FineTrespasser = $this->rs->SelectQuery("
                    SELECT FT.TrespasserTypeId,T.*
                    FROM FineTrespasser FT
                    JOIN Trespasser T ON FT.TrespasserId=T.Id
                    WHERE FT.FineId={$r_Fine['FineId']}");
                    while($r_FineTrespasser = $this->rs->getArrayLine($rs_FineTrespasser)){
                        $o_Trasgressore = new stdClass();
                        foreach($r_FineTrespasser as $indice => $valore){
                            $o_Trasgressore->$indice = $valore;
                        }
                        array_push($a_Trasgressori, $o_Trasgressore);
                    }
                    $o_Verbale->Trespasser = $a_Trasgressori;
                    array_push($this->dati, $o_Verbale);
                } else $this->esito = self::NO_RISULTATI;
            } catch (Exception $e){
                $this->esito = RICHIESTA::ERRORE_INTERNO;
                $this->dati = $e;
            }
        } else $this->esito = self::NO_PARAMETRI;
    }
    
    public function prendiCustomer($cv){
        if($this->verificaParametri($cv)){
            try{
                $r_Customer = $this->rs->getArrayLine($this->query(
                    "SELECT ManagerSector, ManagerCity, ManagerProvince, ManagerZIP, ManagerAddress, ManagerPEC FROM Customer WHERE CityId=?",
                    's',
                    $cv));
                if($r_Customer){
                    $o_Ente = new stdClass();
                    foreach($r_Customer as $indice => $valore){
                        $o_Ente->$indice = $valore;
                    }
                    array_push($this->dati, $o_Ente);
                } else $this->esito = self::NO_RISULTATI;
            } catch (Exception $e){
                $this->esito = RICHIESTA::ERRORE_INTERNO;
                $this->dati = $e;
            }
        } else $this->esito = self::NO_PARAMETRI;
    }
    
    public function prendiCountry(){
        $rs_Country = $this->rs->SelectQuery("SELECT Id,Title FROM Country ORDER BY Title ASC");
        if(mysqli_num_rows($rs_Country) > 0){
            while($r_Country = $this->rs->getArrayLine($rs_Country)){
                $o_Stato = new stdClass();
                foreach($r_Country as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        } else $this->esito = self::NO_RISULTATI;
    }
    
    public function prendiCountrySarida(){
        $rs_Country = $this->rs->SelectQuery("SELECT Id,Title FROM ".MAIN_DB.".Country ORDER BY Title ASC");
        if(mysqli_num_rows($rs_Country) > 0){
            while($r_Country = $this->rs->getArrayLine($rs_Country)){
                $o_Stato = new stdClass();
                foreach($r_Country as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        } else $this->esito = self::NO_RISULTATI;
    }
    
    public function prendiCitySarida($cv = null){
        if(!empty($cv)){
            $rs_City = $this->query("SELECT Id,Title FROM ".MAIN_DB.".City WHERE Id=? ORDER BY Title ASC", 's', $cv);
        } else {
            $rs_City = $this->rs->SelectQuery("SELECT Id,Title FROM ".MAIN_DB.".City ORDER BY Title ASC");
        }
        if(mysqli_num_rows($rs_City) > 0){
            while($r_City = $this->rs->getArrayLine($rs_City)){
                $o_Città = new stdClass();
                foreach($r_City as $indice => $valore){
                    $o_Città->$indice = $valore;
                }
                array_push($this->dati, $o_Città);
            }
        } else $this->esito = self::NO_RISULTATI;
    }
    
    public function verificaJSON($output){
        if(!json_decode($output)){
            http_response_code(500);
            echo json_encode(
                array(
                    "Esito" => RICHIESTA::JSON_MALFORMATO,
                    "Dati" => $output,
                )
            );
        } else echo $output;
    }
}

$op = CheckValue('op', 's'); //Operazione
$cv = CheckValue('cv', 's'); //CityId
$nv = CheckValue('nv', 'n'); //ProtocolId
$yv = CheckValue('yv', 'n'); //ProtocolYear
$dC = CheckValue('dC', 's'); //FineDate
$hC = CheckValue('hC', 's'); //FineTime
$pv = strtoupper(str_replace(' ', '', CheckValue('pv', 's'))); //VehiclePlate

$rs = new CLS_DB(new cls_db_gestoreErroriJSON(false));
$richiesta = new RICHIESTA($rs);

switch($op){
    case 'customer':        $richiesta->prendiCustomer($cv); break;
    case 'country':         $richiesta->prendiCountry(); break;
    case 'finetrespasser':  $richiesta->prendiFineTrespasser($cv, $nv, $yv, $dC, $hC, $pv); break;
    case 'countryS':        $richiesta->prendiCountrySarida(); break;
    case 'cityS':           $richiesta->prendiCitySarida($cv); break;
    default:                $richiesta->esito = RICHIESTA::OP_SCONOSCIUTA;
}

echo json_encode(
    array(
        "Esito" => $richiesta->esito,
        "Dati" => $richiesta->dati
    )
);

$output = ob_get_contents();

ob_end_clean();

$richiesta->verificaJSON($output);

