<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_mail.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

global $rs;

$FileName = CheckValue('FileName', 's');
$EmailDownloadId = CheckValue('Id', 'n');

if(file_exists(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'].'/'.$EmailDownloadId.'/'.$FileName)){
    //Per sicurezza, restringe il percorso accessibile alla cartella /doc/mail_download/<codiceEnte>
    $oldlimitRead = ini_get('open_basedir');
    ini_set('open_basedir', MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid']);
    
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename= $FileName");
    header("Content-Transfer-Encoding: binary");
    
    ob_clean();
    
    readfile(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'].'/'.$EmailDownloadId.'/'.$FileName);
} else {
    $_SESSION['Message']['Error'] = "L'allegato non esiste: $FileName.";
    header("location: mgmt_pec_download.php");
}

//Annulla la restrizione
ini_set('open_basedir', $oldlimitRead);
