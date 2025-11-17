<?php
define('MAGGIOLIEXP_PREF_COL_ESCLUSE', 'QUERY_');
define('MAGGIOLIEXP_UID_REGEX', '^[a-zA-Z]{1}[0-9]+[a-zA-Z]{1}\/[0-9]{4}$');

define('MAGGIOLIEXP_CODICI_CLIENTE', serialize(array(
    'U726' => 'PROVSI',
)));

define('MAGGIOLIEXP_UID_PREFIXES', serialize(array(
    'U726' => 'STD',
)));

define('MAGGIOLIEXP_MAP_TIPOLOGIAANAGRAFICA', serialize(array(
    'L' => 'Locatario',
    'P' => 'Propietario',
    'T' => 'Trasgressore'
)));

define('MAGGIOLIEXP_EXPORTTYPES', serialize(array(
    1, //Maggioli anagrafiche
    2, //Maggioli notifiche
    3, //Maggioli pagamenti
    4, //Maggioli spese notifica
    5  //Maggioli documenti
)));

define('MAGGIOLIEXP_MAP_ESITINOTIFICA', serialize(array(
    1 => 'Non reperibile',
    2 => 'Sconosciuto',
    3 => 'indirizzo errato o insufficiente',
    4 => 'Trasferito',
    5 => 'Deceduto',
    6 => 'Ditta cessata',
    7 => 'Respinto',
    8 => 'Compiuta giacenza',
    9 => 'Firmata',
    10 => 'Cartolina mai ritornata'
)));

function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-question-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}

function writeLog(String $tipo, String $messaggio){
    switch($tipo){
        case 'N': trigger_error("<EXP_MAGGIOLI> DEBUG -> $messaggio", E_USER_NOTICE); break;
        case 'W': trigger_error("<EXP_MAGGIOLI> ATTENZIONE -> $messaggio", E_USER_WARNING); break;
        case 'E': trigger_error("<EXP_MAGGIOLI> ERRORE -> $messaggio", E_USER_WARNING); break;
        default : trigger_error("<EXP_MAGGIOLI> DEBUG -> $messaggio", E_USER_NOTICE); break;
    }
}

/* Struttura xml:
 <$rootName>
 <$childrensName>
 <$key>$value</$key>
 <$key>$value</$key>
 <$key>$value</$key>
 </$childrensName>
 ......
 </$rootName>
 */
function buildMaggioliXml($rootName, $childrensName, $data){
    $dom = new DOMDocument();
    $dom->encoding = 'utf-8';
    $dom->xmlVersion = '1.0';
    $dom->formatOutput = true;
    $root = $dom->createElement($rootName);
    $dom->appendChild($root);
    
    foreach($data as $elements){
        $children = $dom->createElement($childrensName);
        foreach ($elements as $key => $value){
            //Se nelle alias delle viste ci sono colonne che contengono il valore di MAGGIOLIEXP_PREF_COL_ESCLUSE
            //esse non verranno incluse nella costruzione dell'xml
            if(strpos($key, MAGGIOLIEXP_PREF_COL_ESCLUSE) === false){
                $entry = $dom->createElement($key, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
                $children->appendChild($entry);
            }
        }
        $root->appendChild($children);
    }
    
    return $dom->saveXML();
}