<?php
use ForceUTF8\Encoding;
require_once(CLS."/ForceUTF8/Encoding.php");

define('IMP_PAGOPA_SPONTANEOUS_TYPE2_SEPARATOR', ';');

/**
 * imp_pagopa_spontaneous_type2.php.
 * Legge in input un array contenente la definizione delle colonne del file CSV,
 * il separatore di campi del file CSV,
 * il flusso del file in apertura.
 * Restituisce per ogni riga letta un'associazione nomeColonna => valore.
 * @param $columnsArray array un array contenente la definizione delle colonne del file CSV
 * @param $csvSeparator string il separatore di campi
 * @param $fileStream resource il flusso del file in apertura
 * @return array
 */
function buildLinesArray($columnsArray, $csvSeparator, $fileStream){
    $n_CSVReadLine = 0;
    $a_IndexedLines = array();
    
    while (($a_CSVLine = fgetcsv($fileStream, 0, $csvSeparator)) !== false){
        foreach($a_CSVLine as $lineIndex => $lineValue){
            //Potrebbero arrivare dei documenti con codifica diversa da UTF-8 e causare errore. Per questo motivo modifichiamo la codifica verso UTF-8.
            $a_IndexedLines[$n_CSVReadLine][$columnsArray[$lineIndex]] = Encoding::toUTF8($lineValue);
        }
        $n_CSVReadLine++;
    }
    return $a_IndexedLines;
}

/**
 * imp_pagopa_spontaneous_type2.php.
 * Restituisce un icona in base al valore booleano passato:
 * verde se vero, rosso se falso.
 * @param $invalid bool
 * @return string
 */
function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-question-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}

/**
 * imp_pagopa_spontaneous_type2.php.
 * Prende in ingresso la nota completa e recupera ProtocolId e ProtocolYear
 * @param $note string
 * @return array
 */
function getFineDataFromNote($note){
    $noteUpper = strtoupper($note); //Tutto maiuscolo
    $list = explode(' ',$noteUpper); //Creo un array di stringhe separate da spazi
    $ProtocolId = "";
    $ProtocolYear = "";
    foreach($list as $elem) //Ciclo le stringhe alla ricerca di quella contenente "/VM"
    {
        if(strpos($elem,"/VM") != false)
        {
            $cron = str_replace('/VM', '', $elem); //Tolgo "/VM" dalla stringa
            $cronArray = explode('/',$cron);       //Esplodo protocolid e protocolyear
            /*I seguenti cicli servono per considerare solo i numeri e non l'eventuale testo presente accanto al cron
             in caso sia stato inserito il cron attaccato ad altro testo*/
            /*TODO Se il ProtocolId/ProtocolYear non sono separati con lo spazio da una stringa che abbia dei numeri al suo interno, falsa i dati aggiungendo quei numeri
             Es.: Da "TE123STO456/2022/VM" verrebbe fuori ProtocolId = 123456 e ProtocolYear = 2022 */
            for($s=0; $s<count($cronArray); $s++) //Cicla le stringhe
            {
                for($c=0;$c<strlen($cronArray[$s]);$c++) //Cicla i caratteri
                {
                    if($s==0 && is_numeric($cronArray[$s][$c])) //ProtocolId
                        $ProtocolId .= $cronArray[$s][$c];
                        if($s==1 && is_numeric($cronArray[$s][$c])) //ProtocolYear
                            $ProtocolYear .= $cronArray[$s][$c];
                }
            }
            break;
        }
    }
    if($ProtocolId!="" && $ProtocolYear !=""){
        return array($ProtocolId,$ProtocolYear);
    } else return null;
}

function compareColumns($a, $b) {
    $diffs = array();
    empty(($diffA = array_diff($a, $b))) ?: $diffs['missing'] = $diffA;
    empty(($diffB = array_diff($b, $a))) ?: $diffs['extra'] = $diffB;
    return $diffs;
}