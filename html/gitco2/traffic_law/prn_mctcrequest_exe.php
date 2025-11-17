<?php
use  \navigami\html\Archistampa;

include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
require_once('html/richiesta.iphp');
require_once('html/archistampa.iphp');

ini_set('max_execution_time', 3000);
ini_set('memory_limit', '2048M');

$codCatastaleEnte = CheckValue("Search_Customer", 's');
$daData = CheckValue("Search_FromSendDate", 's');
$aData = CheckValue("Search_ToSendDate", 's');
$mese = CheckValue("Search_Month", 's');
$anno = CheckValue("Search_Year", 's');

if($anno>0){
    if($mese>0){
        if($mese<10) $mese = "0".$mese;
        $daData = str_pad($anno, 2, '0', STR_PAD_LEFT).'-'.str_pad($mese, 2, '0', STR_PAD_LEFT).'-01';
        $aData = ($mese==12) ? ($anno+1).'-01-01' :  $anno.'-'.($mese+1).'-01';
    }else{
        $daData = str_pad($anno, 2, '0', STR_PAD_LEFT).'-01-01';
        $aData = str_pad($anno, 2, '0', STR_PAD_LEFT).'-12-31';
    }
} else {
    $daData = $daData ? DateInDB($daData) : '0001-01-01';
    $aData = $aData ? DateInDB($aData) : '9999-12-31';
}

if(CheckValue("PrintType", 's') == 'Excel'){
    $tipoStampa = Archistampa::FORMATO_EXCEL;
    $nomeReport = 'richieste_servizi_esterni_xls';
} else {
    $tipoStampa = Archistampa::FORMATO_BINARIO;
    $nomeReport = 'richieste_servizi_esterni';
}

$ente = $rs->getArrayLine($rs->Select("Customer", "CityId='$codCatastaleEnte'"));
$codEnte = $ente['ent_codice'];

$condizioni = v(
    1,
    'MCTC',
    $daData,
    $aData,
    '_null_'
    );

$richiesta->ImpostaConnessione(r(NULL));
$richiesta->utente->VerificaPChiave(NAVIGAMI_ARCHI_UTENTE, NAVIGAMI_ARCHI_PCHIAVE);
$richiesta->utente->CambiaAzienda($codEnte);

$archistampa = new Archistampa(NAVIGAMI_ARCHI_CHIAMANTE, $nomeReport);
$archistampa->ImpOperazione(Archistampa::OP_VOLATILE);
$archistampa->ImpFormato($tipoStampa);

$urlArchistampa = $archistampa->GeneraUrl($condizioni);

$_SESSION['Documentation'] = $urlArchistampa;

header("location: prn_mctcrequest.php" . $str_GET_Parameter);
