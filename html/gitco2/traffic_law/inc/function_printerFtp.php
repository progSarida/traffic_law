<?php
require_once(CLS."/ftp/PhpFTPFactory.php");

function flowMercurio($phpFTP, $flowFile) {
    /** @var PhpFTPInterface $phpFTP */
    
    if(!flowVerifyFiles($flowFile)){
        $phpFTP->handle_error('Non è stato possibile soddisfare i requisiti necessari per l\'invio del flusso allo stampatore.');
        return false;
    }
    if(!$phpFTP->put($flowFile['LocalFile'], $flowFile['RemoteFile'])){
        return false;
    }
    
    return true;
}

function flowPubblimail($phpFTP, $flowFile) {
    /** @var PhpFTPInterface $phpFTP */
    
    if(!flowVerifyFiles($flowFile)){
        $phpFTP->handle_error('Non è stato possibile soddisfare i requisiti necessari per l\'invio del flusso allo stampatore.');
        return false;
    }
    if(!$phpFTP->put($flowFile['LocalFile'], $flowFile['RemoteFile'])){
        return false;
    }
    
    return true;
}

function flowKoine($phpFTP, $flowFile) {
    /** @var PhpFTPInterface $phpFTP */
    
    if(!flowVerifyFiles($flowFile)){
        $phpFTP->handle_error('Non è stato possibile soddisfare i requisiti necessari per l\'invio del flusso allo stampatore.');
        return false;
    }
    
    $a_localPath = pathinfo($flowFile['LocalFile']);
    $a_remotePath = pathinfo($flowFile['RemoteFile']);
    //File .acc (per ora .tmp) ha lo stesso nome dello zip
    $accFile = $a_remotePath['filename'];
    
    if(!flowKoineWriteAcc("{$a_localPath['dirname']}/$accFile.tmp")){
        $phpFTP->handle_error('Errore nella creazione del file di appoggio .acc');
        return false;
    }
    
    if(!$phpFTP->put("{$a_localPath['dirname']}/$accFile.tmp", "{$a_remotePath['dirname']}/$accFile.tmp")) return false;
    if(!$phpFTP->put($flowFile['LocalFile'], $flowFile['RemoteFile'])) return false;
    if(!$phpFTP->rename("{$a_remotePath['dirname']}/$accFile.tmp", "{$a_remotePath['dirname']}/$accFile.acc")) return false;
    
    return true;
}

//Verifica che i riferimenti dei file da inviare siano stati passati correttamente
function flowVerifyFiles(...$files){
    //Indici richiesti
    $requiredIndexes = array (
        'RemoteFile',
        'LocalFile',
    );
    
    foreach($files as $file){
        //Controlla che i valori degli indici non siano vuoti
        $file = array_filter($file, function($v){return !is_null($v) && $v !== '';});
        
        if (count(array_diff($requiredIndexes, array_keys($file))) > 0) {
            return false;
        }
    }
    
    return true;
}

//Crea il file .acc (.tmp) per i flussi Koinè
function flowKoineWriteAcc($writePath){
    $a_accFile = pathinfo($writePath);
    
    $delimiter = '|';
    $a_KoineAcc = array(
        'Header' => array(
            'Head' => 'HEAD',
            'CodiceCliente' => 'sarid',
            'CodiceLavorazione' => 'verbpdf',
            'ModalitaPostale' => 'xx',
            'TipoStampa' => 'fr',
            'UsoColore' => 'bn',
            'NomeLotto' => $a_accFile['filename'],
            //'DataPostalizzazione' => date('Y-m-d'),
            //'Versione' => '5.0'
        )
    );
    
    if($res_Acc = fopen($writePath, "w")){
        fwrite($res_Acc, implode($delimiter, $a_KoineAcc['Header']));
        fwrite($res_Acc, PHP_EOL);
        fwrite($res_Acc, "{$a_accFile['filename']}.zip");
        fclose($res_Acc);
    } else return false;
    
    return true;
}