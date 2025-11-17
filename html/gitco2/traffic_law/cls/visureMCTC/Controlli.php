<?php
namespace traffic_law\cls\visureMCTC;
use CLS_DB;

class Controlli{
    const LUNG_MAX_INDIRIZZO = 46;
    const SIGLA_NO_CIVICO = "SNC";
    const SIGLE_NO_CIVICO = array(
        "SCN", "SN", "snc", "Snc", "/SNC", "S.N."
    );
    
    const QUERY_DATI_CITTA = "
        SELECT C.Id, C.Title, C.ZIP, P.Title AS ProvinceTitle, P.ShortTitle AS ProvinceShortTitle 
        FROM ".MAIN_DB.".City C JOIN ".MAIN_DB.".Province P on C.ProvinceId = P.Id 
        WHERE C.Title  = '%s'
    ";
    
    const QUERY_DATI_CITTA_MCTC = "
        SELECT C.Id, C.Title, C.ZIP, P.Title AS ProvinceTitle, P.ShortTitle AS ProvinceShortTitle
        FROM ".MAIN_DB.".City C JOIN ".MAIN_DB.".Province P on C.ProvinceId = P.Id
        WHERE C.Com_Denominazione_Ita_Ted = '%s'
    ";
    
    private $bd;
    private $listaCittàCappate;
    private $datiCittà;
    private $città;
    public $listaAnomalie = array();
    
    function __construct(CLS_DB $bd, array $listaCittàCappate, string $città){
        $this->bd = $bd;
        $this->listaCittàCappate = $listaCittàCappate;
        $this->città = $città;
        $this->datiCittà = $this->bd->getArrayLine($this->bd->SelectQuery(sprintf(self::QUERY_DATI_CITTA, addslashes($città))));
        if ($this->datiCittà == null)
            $this->datiCittà = $this->bd->getArrayLine($this->bd->SelectQuery(sprintf(self::QUERY_DATI_CITTA_MCTC, addslashes($città))));
    }
    
    private function prendiDatoCittà(string $cosa){
        return $this->datiCittà[$cosa] ?? null;
    }
    
    public function controllaProvincia(?string $provincia = null){
        if(empty($provincia) || strlen($provincia) != 2){
            $provincia = $this->prendiDatoCittà('ProvinceShortTitle');
        }
        if(empty($provincia)){
            $this->listaAnomalie[] = "Provincia non trovata";
        }
        return $provincia;
    }
    
    public function controllaCittàResidenza(){
        $codCatastale = $this->prendiDatoCittà('Id');
        if(empty($codCatastale)){
            $this->listaAnomalie[] = "Comune di residenza non trovato: {$this->città}";
        }
        return $codCatastale;
    }
    
    public function controllaLungIndirizzo($indirizzo){
        if(strlen($indirizzo) > self::LUNG_MAX_INDIRIZZO){
            $this->listaAnomalie[] = "Indirizzo trasgressore troppo lungo per imbustamento";
            return false;
        } else return true;
    }
    
    public function controllaCap(string $indirizzo, ?string $cap=null){
        $anomalia = '';
        if(empty($cap)){
            $capFinale = $this->prendiDatoCittà('ZIP');
        } else $capFinale = $cap;
        
        if (strlen(trim($capFinale)) != 5 || $capFinale == "00000" || !is_numeric($capFinale)) {
            $anomalia = 'Controllare CAP utente';
        }
        
        if (in_array(strtoupper($this->città), $this->listaCittàCappate) && $this->prendiDatoCittà('ZIP') == $capFinale) {
            $vPartiIndirizzo = explode(" ", $indirizzo);
            $cond = "";
            
            for ($i = 1; $i < count($vPartiIndirizzo) - 1; $i++) {
                $cond .= "AND StreetName LIKE '%" . str_replace(".", "", addslashes($vPartiIndirizzo[$i])) . "%' ";
            }
            
            $rsCittàCappate = $this->bd->SelectQuery("SELECT ZIP FROM sarida.ZIPCity WHERE Title LIKE '%" . $this->città . "%' " . $cond);
            $nRisultati = mysqli_num_rows($rsCittàCappate);
            
            if($nRisultati == 0){
                $anomalia = "NON Trovato cappato";
            } else if ($nRisultati == 1){
                $capFinale = $this->bd->getArrayLine($rsCittàCappate)['ZIP'];
                $anomalia = '';
            } else {
                $capFinale = $this->bd->getArrayLine($rsCittàCappate)['ZIP'];
                
                $rsCittàCappate = $this->bd->SelectQuery("SELECT ZIP FROM sarida.ZIPCity WHERE Title LIKE '%" . $this->città . "%' " . $cond . " AND StreetName LIKE '%" . addslashes($vPartiIndirizzo[0]) . "%'");
                $nRisultati = mysqli_num_rows($rsCittàCappate);
                if ($nRisultati == 1) {
                    $capFinale = $this->bd->getArrayLine($rsCittàCappate)['ZIP'];
                    $anomalia = '';
                } else {
                    $anomalia = "Trovati più cap: $capFinale";
                }
            }
        }
        
        if($anomalia) $this->listaAnomalie[] = $anomalia;
        return $capFinale;
    }
    
    public static function normalizzaIndirizzo(string $indirizzo, ?string $toponimo = null, ?string $civico = null){
        if(!empty($civico)){
            $civico = str_replace(self::SIGLE_NO_CIVICO, self::SIGLE_NO_CIVICO, $civico);
            if(trim($civico)==".") $civico = "";
        }
        
        if(trim($civico)==""){
            $vIndirizzo = explode(" ",$indirizzo);
            if(is_numeric(substr($vIndirizzo[0],0,1))){
                $indirizzo = "";
                for($i=1; $i < count($vIndirizzo); $i++){
                    $indirizzo .= $vIndirizzo[$i]." ";
                }
                $indirizzo .= $vIndirizzo[0];
            }
        } else {
            $civico = str_replace("-","/",$civico);
            switch ($toponimo)
            {
                case "V":
                case "V.":
                case "VIA": $toponimo = "VIA"; break;
                
                case "LOC":
                case "LOC.":
                case "LCLTA": $toponimo = "LOC."; break;
                
                case "PLE":
                case "P.ZLE": $toponimo = "P.LE"; break;
                
                case "PZZA":
                case "P.ZZA": $toponimo = "P.ZA"; break;
                
                case "FRAZ.":
                case "FRZIN":
                case "FRAZ": $toponimo = "FRAZ"; break;
                
                case "C.SO":
                case "CSO":
                case "CORSO": $toponimo = "CORSO"; break;
                
                case "BORGO": $toponimo = "BORGO"; break;
                
                case "CDA": $toponimo = "C.DA"; break;
                
                case "VLE": $toponimo = "VIALE"; break;
                case "STR": $toponimo = "STR."; break;
            }
        }
        return trim(implode(array(trim($toponimo), trim($indirizzo), trim($civico)), ' '));
    }
}