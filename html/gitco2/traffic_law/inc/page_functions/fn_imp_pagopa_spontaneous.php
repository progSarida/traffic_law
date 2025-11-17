<?php
define('IMP_PAGOPA_SPONTANEOUS_SEPARATOR', ';');

/**
 * imp_pagopa_spontaneous.php.
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
            $encoding = mb_detect_encoding($lineValue,'UTF-8, ASCII, ISO-8859-1');
            $a_IndexedLines[$n_CSVReadLine][$columnsArray[$lineIndex]] = mb_convert_encoding($lineValue, 'UTF-8', $encoding);
        }
        $n_CSVReadLine++;
    }
    return $a_IndexedLines;
}

/**
 * imp_pagopa_spontaneous.php.
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
 * imp_pagopa_spontaneous.php.
 * Verifica se il cron letto è in un formato valido (<cron>/<anno>{4} oppure <cron>/<anno>{4}/<lettera>).
 * @param $value string
 * @return bool
 */
function checkFineProtocol($value){
    return preg_match('/^\d+\/\d{4}(?:\/\p{L}+)?$/', $value) > 0;
}


function compareColumns($a, $b) {
    $diffs = array();
    empty(($diffA = array_diff($a, $b))) ?: $diffs['missing'] = $diffA;
    empty(($diffB = array_diff($b, $a))) ?: $diffs['extra'] = $diffB;
    return $diffs;
}