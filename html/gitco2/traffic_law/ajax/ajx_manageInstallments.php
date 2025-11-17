<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_installment.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

global $rs;

$CityId = CheckValue("CityId", 's');
$Dovuto = CheckValue("Dovuto", 'n');
$DataInizio = DateInDB(CheckValue("DataInizio", 's'));
$DataRichiesta = DateInDB(CheckValue("DataRichiesta", 's'));
$Reddito = CheckValue("Reddito", 'n');
$NFamiliari = CheckValue("Familiari", 'n');
$NRate = CheckValue("Rate", 'n');
$Metodo = CheckValue("Metodo", 's');
$FineId = CheckValue("FineId", 'n');

$success = true;
$a_Messages = array();
$RedditoMaxCalcolato = 0;
$TassoInteresse = 0;
$r_PaymentRate = null;

if($FineId > 0){
    $r_PaymentRate = $rs->getArrayLine($rs->Select("PaymentRate", "FineId=$FineId AND StatusRateId != ".RATEIZZAZIONE_CHIUSA));
    if($r_PaymentRate){
        $TassoInteresse = $r_PaymentRate['InterestsPercentual'];
    } else {
        if($r_Customer['ApplyInstallmentRates'] > 0){
            $r_InstallmentRates = $rs->getArrayLine($rs->Select('InstallmentRates', "CityId='$CityId' AND (COALESCE(FromDate,'1000-01-01') <= '$DataRichiesta' AND COALESCE(ToDate,'9999-12-31') >= '$DataRichiesta')"));
            $TassoInteresse = $r_InstallmentRates['Percentual'] ?? 0;
        }
    }
}

if($DataInizio){
    $d = DateTime::createFromFormat('Y-m-d', $DataInizio);
    if(!$d || $d->format('Y-m-d') != $DataInizio){
        $success = false;
        $a_Messages[] = "data decorrenza prima rata non valida";
        $DataInizio = null;
    }
}

$cls_installment = new cls_installment($CityId, $Metodo, $DataInizio, $TassoInteresse);

if(empty($r_PaymentRate)){
    $d = DateTime::createFromFormat('Y-m-d', $DataRichiesta);
    if(!$d || $d->format('Y-m-d') != $DataRichiesta){
        $success = false;
        $a_Messages[] = "data richiesta non valida";
    }
    
    if($Dovuto < $r_Customer['InstallmentMinimumFeeLimit']){
        $success = false;
        $a_Messages[] = "impossibile procedere con la rateizzazione: l'importo dovuto dal contravventore non supera l'importo minimo per la rateizzazione di € {$r_Customer['InstallmentMinimumFeeLimit']}";
    }
    if($r_Customer['InstallmentMethod'] == cls_installment::TIPOLOGIA_IMP_LEGISLATIVO){
        $RedditoMax = $r_Customer['InstallmentYearlyIncomeLimit'];
        $RedditoAggFamiliari = $r_Customer['InstallmentAdditionalIncomePerFamilyMember'];
        $RedditoMaxCalcolato = cls_installment::calcolaReddittoMax($NFamiliari, $RedditoAggFamiliari, $RedditoMax);
        
        if($Reddito > $RedditoMaxCalcolato){
            $success = false;
            $a_Messages[] = "impossibile procedere con la rateizzazione: Il reddito del richiedente supera il reddito massimo calcolato di € $RedditoMaxCalcolato";
        }
    } else {
        if($NRate <= 0 || $NRate > $r_Customer['InstallmentFreeRateLimit']){
            $success = false;
            $a_Messages[] = "numero di rate non valido. Il numero di rate non può essere maggiore di {$r_Customer['InstallmentFreeRateLimit']}";
        }
    }
}

if($success){
    $a_RateMaxImporti = array(
        $r_Customer['InstallmentRateLimit1'] => $r_Customer['InstallmentFeeLimit1'],
        $r_Customer['InstallmentRateLimit2'] => $r_Customer['InstallmentFeeLimit2'],
        $r_Customer['InstallmentRateLimit3'] => $r_Customer['InstallmentFeeLimit3'],
    );
    $cls_installment->calcolaRate($Dovuto, $NRate, $r_Customer['InstallmentRatesMinimumAmount'], $a_RateMaxImporti);
}

echo json_encode(
    array(
        "Messages" => $a_Messages,
        "Success" => $success,
        "Data" => $cls_installment->rate,
        "InterestTax" => (float)$TassoInteresse,
        "MaxIncome" => $RedditoMaxCalcolato,
        "InstallmentsNumber" => count($cls_installment->rate)
    )
);