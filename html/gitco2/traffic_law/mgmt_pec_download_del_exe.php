<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_mail.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

function deleteFolder($path, $folderName) {
    //Per sicurezza, restringe il percorso accessibile alla cartella /doc/mail_download/<codiceEnte>
    $oldlimitRead = ini_get('open_basedir');
    ini_set('open_basedir', $path);
    
    $content = glob($path.'/'.$folderName.'/*') ?: array();
    foreach ($content as $file) {
        if(is_dir($file)){
            if(!deleteFolder(pathinfo($file, PATHINFO_DIRNAME), pathinfo($file, PATHINFO_BASENAME))){
                return false;
            }
        } else {
            if(!unlink($file)) return false;
        }
    }
    if(!rmdir($path.'/'.$folderName)) return false;
    
    ini_set('open_basedir', $oldlimitRead);
    return true;
}

global $rs;

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 'n');

$str_Error = '';

$r_DownloadEmail = $rs->getArrayLine($rs->Select('DownloadEmail', "Id=$Id AND CityId='{$_SESSION['cityid']}'"));

if($r_DownloadEmail){
    //Elimina la cartella contenente la mail e gli allegati, successivamente elimina i record del messaggio e dei suoi allegati/categorie
    if(deleteFolder(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'], $Id)){
        $rs->Start_Transaction();
        
        $rs->Delete('DownloadEmail', "Id=$Id");
        $rs->Delete('DownloadEmailAttachments', "DownloadEmailId=$Id");
        $rs->Delete('DownloadEmailCategories', "DownloadEmailId=$Id");
        
        $rs->End_Transaction();
    } else $str_Error = 'Errore nell\'eliminazione dei dati. Contattare un amministratore.';
} else $str_Error = 'Impossibile aggiornare i dati. Verificare che il messaggio selezionato appartenga all\'ente in uso.';

if($str_Error != '') {
    $_SESSION['Message']['Error'] = $str_Error;
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: mgmt_pec_download.php".$Filters);

