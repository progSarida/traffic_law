<?php
define('IMP_MAGGIOLI_FULLPROTOCOL_RGX', '/^[1-9]\d*\/[2][0-9]{3}$/');
define('IMP_MAGGIOLI_CONCILIA_VEHICLETYPE', serialize(array(
    'A' => 1, //autoveicolo (es. auto, furgoni, camper)
    'M' => 2, //motoveicolo
    'B' => 4, //autocarro (veicoli sopra i 35q)
    'D' => 12, //autoarticolato
    'E' => 8, //autobus
    'R' => 7, //rimorchio (tutti i rimorchi pesanti o leggeri con targa propria)
)));

function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-minus-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}

function checkAndReadXml($filePath){
    $str_Error = '';
    $rootNodeName = 'Maggioli';
    $childrensNodeNames = 'Posizione';
    $a_items = array();
    
    libxml_use_internal_errors(true);
    
    //Leggo l'xml e verifico il nodo radice e i nodi figli
    if($xml = simplexml_load_file($filePath)){
        if($xml->getName() === $rootNodeName){
            $count = 1;
            foreach ($xml->children() as $element) {
                if($element->getName() === $childrensNodeNames){
                    foreach($element->children() as $item){
                        $a_items[$count][$item->getName()] = $item->__toString();
                    }
                } else $str_Error .= "Nodo n.$count non valido. Previsto: $childrensNodeNames, Letto: {$element->getName()}";
                $count ++;
            }
        } else $str_Error .= "Nodo radice non valido. Previsto: $rootNodeName Letto: {$xml->getName()}";
    } else {
        //C'è stato un errore interno nella lettura dell'xml, lo ritorno
        foreach(libxml_get_errors() as $xmlError) {
            $str_Error .= $xmlError->message.'<br>';
        }
    }
    
    //Ritorno o un array con dentro i dati di ogni posizione, oppure un errore
    return $str_Error ?: $a_items;
}

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

function archiveZip($path, $destinationPath, $zipName){
    //Come sicurezza, controlla che il nome dello zip non sia vuoto, sia diverso da (.) e non contenga (..) (/) e (\)
    if(empty($zipName) || $zipName == '.' || strpos($zipName, '..') !== false || strpos($zipName, '/') !== false || strpos($zipName, '\\') !== false){
        return false;
    }
    return rename("$path/$zipName", "$destinationPath/$zipName");
}