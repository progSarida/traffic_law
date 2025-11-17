<?php
define("IMP_PCPRINT_COLUMNSROW_INDEX", 2);

define("IMP_PCPRINT_SPEED_COLUMNS", serialize(array(
    "DATA",
    "ORA",
    "LOCALITA'_INFO",
    "OPERATORE",
    "LIMITE",
    "VELOCITA'",
    "NOME_FILE",
    "MATRICOLA_FT1D",
    "MODELLO_CAMERA",
    "MATRICOLA_CAMERA",
    "ESPOSIZIONE",
    "LUMINOSITA'",
    "TARGA",
)));

define("IMP_PCPRINT_TRAFFICLIGHT_COLUMNS", serialize(array(
    "DATA",
    "ORA",
    "LOCALITA'_INFO",
    "OPERATORE",
    "TEMPO_DAL_ROSSO",
    "FOTOGRAMMA",
    "NOME_FILE",
    "MATRICOLA_FT1D",
    "MODELLO_CAMERA",
    "MATRICOLA_CAMERA",
    "ESPOSIZIONE",
    "LUMINOSITA'",
    "TARGA",
)));

function impPcprintBuildLinesArray($columns, $separator, $wrapper, $fileStream, &$error){
    $a_Rows = array();
    $count = 0;
    while (($buffer = fgets($fileStream, 4096)) !== false ) {
        $count++;
        if(!empty(trim($buffer))){
            $a_Row = str_getcsv(trim($buffer), $separator, $wrapper);
            
            if(count($a_Row) != count($columns)){
                $error .= "La riga $count contiene una struttura dati non valida. Controllare gli spazi.";
            } else {
                $a_indexedRow = array_combine($columns, $a_Row);
                
                if(empty($a_indexedRow)){
                    $a_indexedRow = array_fill_keys($columns, '');
                }
                
                $a_Rows[] = $a_indexedRow;
            }
        }
    };
    return $a_Rows;
}

function impPcprintGetCode($documentName, $protocolYear){
    $aCode = explode("_", $documentName);
    $letter = substr($aCode[3], -5, 1);
    $Code = $aCode[2] . intval($aCode[3]) . $letter . "/" . $protocolYear;
    return $Code;
}

function impPcprintPrintIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-question-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}