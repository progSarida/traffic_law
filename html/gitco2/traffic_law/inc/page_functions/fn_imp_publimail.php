<?php

define("IMP_PUBLIMAIL_CSV_SEPARATOR",",");

//Colonne documento
$a_CSVColumns = array (
    'stampatore',
    'numero_raccomandata',
    'numero_ricevuta_ritorno',
    'cc',
    'nazione',
    'tipo_stampa',
    'id_applicativo',
    'id_tipo_documento',
    'id_documento',
    'cronologico',
    'anno',
    'destinatario',
    'indirizzo',
    'cap',
    'localita',
    'cod_mancato_recapito',
    'mancato_recapito',
    'data_mancato_recapito',
    'cod_notifica',
    'notifica',
    'data_notifica',
    'data_spedizione',
    'data_log',
    'scatola',
    'lotto',
    'posizione',
    'img_unica',
    'img_fronte',
    'img_retro'
);

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
//**************
//DA CONTROLLARE
//**************
define('IMP_PUBLIMAIL_FULLPROTOCOL_RGX', '/^[1-9]\d*\/[2][0-9]{3}$/');
function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-minus-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}
//**************
function deleteFolder($path, $folderName) {
    //Come sicurezza, controlla che il nome della cartella non sia vuoto e non contenga (.) (/) e (\)  
    if(empty($folderName) || strpos($folderName, '.') !== false || strpos($folderName, '/') !== false || strpos($folderName, '\\') !== false){
        return false;
    }
    
    $content = glob($path.'/'.$folderName.'/*');
    foreach ($content as $file) {
        if(is_dir($file)){
            if(!deleteFolder(pathinfo($file, PATHINFO_DIRNAME), pathinfo($file, PATHINFO_BASENAME))){
                return false;
            }
        } else {
            unlink($file);
        }
    }
    rmdir($path.'/'.$folderName);
    return true;
}

function compareColumns($a, $b) {
    $diffs = array();
    empty(($diffA = array_diff($a, $b))) ?: $diffs['missing'] = $diffA;
    empty(($diffB = array_diff($b, $a))) ?: $diffs['extra'] = $diffB;
    return $diffs;
}

function translateResultId($letter){
    $id = null;
    $description = '';
    switch($letter){
        case 'Y': $description = 'Irreperibile'; $id = 11; break;
        case 'D': $description = 'Destinatario sconosciuto'; $id = 19; break;
        case 'C': $description = 'Consegnato al destinatario (NO CAN)'; $id = 1; break;
        case 'Z': $description = 'Ritirato alla POSTA (SI CAD)'; $id = 2; break;
        case 'N': $description = 'Non ritirato (SI CAD)'; $id = 20; break;
        case 'A': $description = 'Consegnato ad altri (SI CAN)'; break; //???????????
        //Mancano altri casi
    }
    return $description;
}

//Tronca ed aggiunge i punti sospensivi alle stringhe troppo lunghe delle immagini
function imageSubString($type, $name){
    $imageName = $name;
    if($type == 'single' && (strlen($name) > 53)):
        $imageName = substr($name,0,50)."...";
    elseif($type == 'multiple' && (strlen($name) > 23)):
        $imageName = substr($name,0,20)."...";
    endif;
    return $imageName;
}