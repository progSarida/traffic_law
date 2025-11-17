<?php
class cls_iuv{
    //https://docs.italia.it/pagopa/pagopa_docs/pagopa-codici-docs/it/v1.4.0/_docs/Capitolo2.html
    
    const AUX_DIGIT = array(0,1,2,3);
    const AUX_CHECKDIGIT_DIVISOR = 93;
    
    const ERR_PREFIX_INVALID = 'Suffisso di Aux digit e codice segregazione/Application code non valido.';
    const ERR_AUXDIGIT_INVALID = 'Codice Aux digit non valido.';
    const ERR_SEGAPPCODE_INVALID = 'Codice segregazione/Application code non valido.';
    const ERR_SEGAPPCODE_REQUIRED = 'Codice segregazione/Application code richiesto per il codice Aux digit specificato.';
    const ERR_IUDCODE_INVALID = 'Codice IUD non valido.';
    const ERR_IUV_INVALID = 'Codice IUV non valido. Prevista sequenza di cifre di 15 o 17 caratteri.';
    const ERR_NOTICECODE_INVALID = 'Codice avviso non valido. Prevista sequenza di cifre di 18 caratteri';
    const ERR_IUV_LENGTH = 'Lunghezza IUV errata per il codice Aux digit specificato.';
    
    public function __construct(){
    }
    
    /**
     * Genera un codice IUV servendosi dei parametri della classe istanziata
     * @return String il codice IUV generato, null se la generazione non è andata
     * a buon fine.
     */
    public function generateIUV(String $IUDCode, ?int $auxDigit, ?String $segAppCode = null, ?String $prefix = null){
        if(!$this->checkAuxDigit($auxDigit)){
            throw new Exception(self::ERR_AUXDIGIT_INVALID);
        }
        
        switch ($auxDigit){
            case 0:
                $IUVBase = $this->buildIUVBase($IUDCode, $auxDigit, $prefix);
                $checkDigit =  $this->buildCheckDigit($IUVBase, $auxDigit, $segAppCode);
                return $IUVBase.$checkDigit;
            case 1:
                $IUVBase = $this->buildIUVBase($IUDCode, $auxDigit, $prefix);
                return $IUVBase;
            case 2:
                $IUVBase = $this->buildIUVBase($IUDCode, $auxDigit, $prefix);
                $checkDigit =  $this->buildCheckDigit($IUVBase, $auxDigit);
                return $IUVBase.$checkDigit;
            case 3:
                $IUVBase = $this->buildIUVBase($IUDCode, $auxDigit, $prefix);
                $checkDigit =  $this->buildCheckDigit($IUVBase, $auxDigit, $segAppCode);
                return $segAppCode.$IUVBase.$checkDigit;
            default:
                return null;
        }
    }
    
    public function generateNoticeCode(String $IUV, ?int $auxDigit, ?String $segAppCode = null){
        if(!$this->checkAuxDigit($auxDigit)){
            throw new Exception(self::ERR_AUXDIGIT_INVALID);
        }
        if(!$this->checkIuv($IUV)){
            throw new Exception(self::ERR_IUV_INVALID);
        }
        
        switch ($auxDigit){
            case 0:
                if(strlen($IUV) == 15){
                    if(!empty($segAppCode)){
                        return $auxDigit.$segAppCode.$IUV;
                    } else throw new Exception(self::ERR_SEGAPPCODE_REQUIRED);
                } else throw new Exception(self::ERR_IUV_LENGTH);
            case 1:
            case 2:
            case 3:
                if(strlen($IUV) == 17){
                    return $auxDigit.$IUV;
                } else throw new Exception(self::ERR_IUV_LENGTH);
            default:
                return null;
        }
    }
    
    public function extractIUV(String $noticeCode){
        if(!$this->checkNoticeCode($noticeCode)){
            throw new Exception(self::ERR_NOTICECODE_INVALID);
        }
        
        $auxDigit = substr($noticeCode, 0, 1);
        
        switch ($auxDigit){
            case 0:
                return substr($noticeCode, 3);
            case 1:
            case 2:
            case 3:
                return substr($noticeCode, 1);
            default:
                return null;
        }
    }
    
    public function buildIUVBase($IUDCode, $auxDigit, $prefix = null){
        if(!$this->checkIUDCode($IUDCode)){
            throw new Exception(self::ERR_IUDCODE_INVALID);
        }
        if(!$this->checkAuxDigit($auxDigit)){
            throw new Exception(self::ERR_AUXDIGIT_INVALID);
        }
        if(isset($prefix) && !$this->checkPrefix($prefix)){
            throw new Exception(self::ERR_PREFIX_INVALID);
        }
            
        switch ($auxDigit){
            case 0:
            case 3:
                $IUVBase = $this->fillWithZeros($IUDCode, 13);
                $IUVBase = isset($prefix) ? $prefix.substr($IUVBase, strlen($prefix)) : $IUVBase;
                return $IUVBase;
            case 1:
                $IUVBase = $this->fillWithZeros($IUDCode, 17);
                $IUVBase = isset($prefix) ? $prefix.substr($IUVBase, strlen($prefix)) : $IUVBase;
                return $IUVBase;
            case 2:
                $IUVBase = $this->fillWithZeros($IUDCode, 15);
                $IUVBase = isset($prefix) ? $prefix.substr($IUVBase, strlen($prefix)) : $IUVBase;
                return $IUVBase;
            default:
                return null;
        }
    }
    
    public function buildCheckDigit($IUVBase, $auxDigit, $segAppCode = null){
        if(!$this->checkAuxDigit($auxDigit)){
            throw new Exception(self::ERR_IUDCODE_INVALID);
        }
        if(!empty($segAppCode) && !$this->checkSegAppCode($segAppCode)){
            throw new Exception(self::ERR_SEGAPPCODE_INVALID);
        }
        
        switch ($auxDigit){
            case 0:
            case 3:
                if(!empty($segAppCode)){
                    $checkDigit = ($auxDigit.$segAppCode.$IUVBase) % self::AUX_CHECKDIGIT_DIVISOR;
                    $checkDigit = $this->fillWithZeros($checkDigit, 2);
                    return $checkDigit;
                } else throw new Exception(self::ERR_SEGAPPCODE_REQUIRED);
            case 2:
                $checkDigit = ($auxDigit.$IUVBase) % self::AUX_CHECKDIGIT_DIVISOR;
                $checkDigit = $this->fillWithZeros($checkDigit, 2);
                return $checkDigit;
            default:
                return null;
        }
    }
    
    /**
     * Verifica il formato del codice IUD, il quale deve essere di sole cifre.
     * @return bool
     */
    public function checkIUDCode($IUDCode){
        return ctype_digit($IUDCode);
    }
    
    /**
     * Verifica che il codice Aux digit specificato sia supportato.
     * @return bool
     */
    public function checkAuxDigit($auxDigit){
        return !is_null($auxDigit) && in_array($auxDigit, self::AUX_DIGIT);
    }
    
    /**
     * Verifica il formato del codice segregazione/Application code, il quale deve essere di due cifre
     * e compreso fra 00 e 99
     * @return bool
     */
    public function checkSegAppCode($segAppCode){
        return preg_match('/^[0-9][0-9]$/', $segAppCode) > 0;
    }
    
    /**
     * Verifica il formato del suffisso da agganciare ad Aux digit e segregazione/Application code
     * il quale deve essere di una cifra e la lunghezza non deve superare i 3 caratteri
     * @return bool
     */
    public function checkPrefix($prefix){
        return is_numeric($prefix) && strlen($prefix) <= 3;
    }
    
    /**
     * Verifica il formato dello IUV
     * il quale deve essere una sequenza di cifre di 15 o 17 caratteri
     * @return bool
     */
    public function checkIuv($IUV){
        return preg_match('/^([0-9]{15}|[0-9]{17})$/', $IUV) > 0;
    }
    
    /**
     * Verifica il formato del codice avviso
     * il quale deve essere una sequenza di cifre di 18 caratteri
     * @return bool
     */
    public function checkNoticeCode($noticeCode){
        return preg_match('/^[0-9]{18}$/', $noticeCode) > 0;
    }
    
    
    private function fillWithZeros($n, $nZero){
        $n_Diff = $nZero - strlen($n);
        for($i=0; $i<($n_Diff); $i++){
            $n = "0".$n;
        }
        return $n;
    }
}