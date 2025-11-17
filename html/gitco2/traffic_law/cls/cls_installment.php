<?php
require_once (INC."/function.php");

class cls_installment{
    const TIPOLOGIA_LIBERA = 0;
    const TIPOLOGIA_IMP_LEGISLATIVO = 1;
    const INDICE_QUOTA_CAP = "QuotaCapitale";
    const INDICE_ITERESSI = "Interessi";
    const INDICE_IMP_RATA = "ImportoRata";
    const INDICE_DATA_SCAD_RATA = "DataScadenza";
    
    private $tipologia;
    private $ente;
    public $nRate;
    public $importoRata;
    public $resto;
    public $tassoInteresse;
    public $dataInizio;
    public $rate = array();
    
    public function __construct(string $ente, int $tipologia = self::TIPOLOGIA_LIBERA, ?string $dataInizio = null, int $tassoInteresse = null){
        $this->ente = $ente;
        $this->tassoInteresse = $tassoInteresse;
        $this->tipologia = $tipologia;
        $this->dataInizio = $dataInizio;
    }
    
    public function calcolaRate(float $dovuto, int $nRate = 0, ?float $limiteSingolaRata = null, ?array $rateMaxImporti = null){
        switch($this->tipologia){
            case self::TIPOLOGIA_LIBERA:            $this->calcolaRateTipoLibera($dovuto, $nRate); break;
            case self::TIPOLOGIA_IMP_LEGISLATIVO:   $this->calcolaRateTipoImpianto($dovuto, $limiteSingolaRata, $rateMaxImporti); break;
        }
    }
    
    //r = importo rata; C = debito; i = tasso periodico (mensile); n = numero di rate;
    //Calcolo: (1+(1/((1+i)^n-1))) * i * C
    public static function calcolaRataConInteressi($dovuto, $tassoInteressi, $nRate){
        return (1+(1/((pow(1+($tassoInteressi/100/12), $nRate))-1)))*($tassoInteressi/100/12)*$dovuto;
    }
    
    //R = importo rata; i = tasso periodico (mensile); n = numero di rate; k = k-esima rata;
    //Calcolo: R / (1+i)^(n-k+1)
    public static function calcolaQuotaCapitale($importoRata, $tassoInteressi, $nRate, $nRata){
        return $importoRata/( pow((1+($tassoInteressi/100/12)),($nRate-$nRata+1)) );
    }
    
    public static function calcolaReddittoMax(int $nFamiliari, float $redditoAggFam, float $redditoMax){
        return round($redditoMax + ($nFamiliari * $redditoAggFam), 2);
    }
    
    private function calcolaRateTipoImpianto(float $dovuto, float $limiteSingolaRata, array $rateMaxImporti){
        $nRate = 0;
        foreach($rateMaxImporti as $nRateMax => $importoMax){
            $nRate = $nRateMax;
            if($dovuto <= $importoMax) break;
        }
    
        for($i = $nRate; $i > 1; $i--){
            if($this->tassoInteresse){
                $rata = round($this->calcolaRataConInteressi($dovuto, $this->tassoInteresse, $i), 2);
            } else $rata = floor(($dovuto/$i)*100)/100;
            
            if($rata >= $limiteSingolaRata){
                $this->nRate = $i;
                $this->importoRata = $rata;
                $this->resto = !$this->tassoInteresse ? round($dovuto - ($this->importoRata * $this->nRate), 2) : 0;
                
                for($i=1; $i<=$this->nRate; $i++){
                    $importoRata = $i == $this->nRate ? ($this->importoRata + $this->resto) : $this->importoRata;
                    if($this->dataInizio){
                        $dataScadenza = date('Y-m-d', strtotime("{$this->dataInizio} + $i months"));
                        $dataScadenza = date("Y-m-t", strtotime($dataScadenza));
                        $dataScadenza = SkipFestiveDays($dataScadenza, $this->ente);
                    } else $dataScadenza = null;
                    if($this->tassoInteresse){
                        $quotaCapitale = round($this->calcolaQuotaCapitale($this->importoRata, $this->tassoInteresse, $this->nRate, $i), 2);
                        $interessi = round($this->importoRata-$quotaCapitale, 2);
                    } else {
                        $quotaCapitale = $importoRata;
                        $interessi = 0;
                    }
                    $this->riempiVettoreRate($i, $importoRata, $quotaCapitale, $interessi, $dataScadenza);
                }
                return true;
            }
        }
        return false;
    }
    
    private function calcolaRateTipoLibera($dovuto, $nRate){
        if($this->tassoInteresse){
            $rata = round($this->calcolaRataConInteressi($dovuto, $this->tassoInteresse, $nRate), 2);
        } else $rata = floor(($dovuto/$nRate)*100)/100;
        
        $this->nRate = $nRate;
        $this->importoRata = $rata;
        $this->resto = !$this->tassoInteresse ? round($dovuto - ($this->importoRata * $this->nRate), 2) : 0;
        
        for($i=1; $i<=$this->nRate; $i++){
            $importoRata = $i == $this->nRate ? ($this->importoRata + $this->resto) : $this->importoRata;
            if($this->dataInizio){
                $dataScadenza = date('Y-m-d', strtotime("{$this->dataInizio} + $i months"));
                $dataScadenza = date("Y-m-t", strtotime($dataScadenza));
                $dataScadenza = SkipFestiveDays($dataScadenza, $this->ente);
            } else $dataScadenza = null;
            if($this->tassoInteresse){
                $quotaCapitale = round($this->calcolaQuotaCapitale($this->importoRata, $this->tassoInteresse, $this->nRate, $i), 2);
                $interessi = round($this->importoRata-$quotaCapitale, 2);
            } else {
                $quotaCapitale = $importoRata;
                $interessi = 0;
            }
            $this->riempiVettoreRate($i, $importoRata, $quotaCapitale, $interessi, $dataScadenza);
        }
    }
    
    private function riempiVettoreRate(int $nRata, float $importo, float $quotaCap, float $interessi, ?string $dataScadenza){
        $this->rate[$nRata][self::INDICE_IMP_RATA] = $importo;
        $this->rate[$nRata][self::INDICE_QUOTA_CAP] = $quotaCap;
        $this->rate[$nRata][self::INDICE_ITERESSI] = $interessi;
        $this->rate[$nRata][self::INDICE_DATA_SCAD_RATA] = $dataScadenza;
    }
}