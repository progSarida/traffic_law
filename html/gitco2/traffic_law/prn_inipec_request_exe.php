<?php
use  \navigami\html\Archistampa;

include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
require_once (INC . "/function.php");
require (INC . "/initialization.php");
require_once('html/richiesta.iphp');
require_once('html/archistampa.iphp');

if(CheckValue("PrintType", 's') == 'Excel'){
    $tipoStampa = Archistampa::FORMATO_EXCEL;
    $nomeReport = 'richieste_servizi_esterni_xls';
} else {
    $tipoStampa = Archistampa::FORMATO_BINARIO;
    $nomeReport = 'richieste_servizi_esterni';
}

$codCatastaleEnte = CheckValue("Search_Customer", 's');
$daData = CheckValue("Search_FromSendDate", 's') ?: '0001-01-01';
$aData = CheckValue("Search_ToSendDate", 's') ?: '9999-12-31';
$codUtente = CheckValue("Search_Username", 's') ?: '_null_';

$ente = $rs->getArrayLine($rs->Select("Customer", "CityId='$codCatastaleEnte'"));
$codEnte = $ente['ent_codice'];

$condizioni = v(
    1,
    'INIPEC',
    $daData,
    $aData,
    $codUtente
);

$richiesta->ImpostaConnessione(r(NULL));
$richiesta->utente->VerificaPChiave(NAVIGAMI_ARCHI_UTENTE, NAVIGAMI_ARCHI_PCHIAVE);
$richiesta->utente->CambiaAzienda($codEnte);

$archistampa = new Archistampa(NAVIGAMI_ARCHI_CHIAMANTE, $nomeReport);
$archistampa->ImpOperazione(Archistampa::OP_VOLATILE);
$archistampa->ImpFormato($tipoStampa);

$urlArchistampa = $archistampa->GeneraUrl($condizioni);

$_SESSION['Documentation'] = $urlArchistampa;

header("location: " . impostaParametriUrl(array('Filter' => 1, 'Print' => true), "mgmt_inipec_request.php" . $str_GET_Parameter));
    
    