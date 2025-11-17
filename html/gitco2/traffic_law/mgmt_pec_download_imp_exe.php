<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_mail.php");
require_once(INC."/function.php");
require_once(CLS."/cls_progressbar.php");
require_once(INC."/initialization.php");

ini_set('max_execution_time', 3000);

//Crea il file di lock
file_put_contents(TMP.'/'.'pec_download_'.$_SESSION['cityid'].'.lock', '');

//Elimina il file di lock alla fine dell'esecuzione
register_shutdown_function(function (){
    if(file_exists(TMP.'/'.'pec_download_'.$_SESSION['cityid'].'.lock')){
        unlink(TMP.'/'.'pec_download_'.$_SESSION['cityid'].'.lock');
    }
});

if (!is_dir(MAILDOWNLOAD_FOLDER)) {
    mkdir(MAILDOWNLOAD_FOLDER);
    chmod(MAILDOWNLOAD_FOLDER, 0750);
}
if (!is_dir(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'])) {
    mkdir(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid']);
    chmod(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'], 0750);
}

global $rs;

$ProgressFileName = CheckValue("ProgressFile", "s");
$count = 0;
$CityId = CheckValue("CityId", 's');

//Se il cityid dell'operazione non corrisponde a quello in sessione, lancia errore
if($CityId != $_SESSION['cityid']){
    $_SESSION['Message']['Error'] = "Errore: l'ente usato per l'operazione non corrisponde a quello in sessione.";
    
    echo json_encode(
        array(
            "Count" => $count
        )
        );
    DIE;
}

//Gestore mail: inizializzazione///////////////////////////////////////////////////////////
$rs_CustomerMail = $rs->Select('CustomerMailAuthentication', "ConfigType=2 AND CityId='".$_SESSION['cityid']."'");
$r_CustomerMail = $rs->getArrayLine($rs_CustomerMail) ?? array();

try{
    $mail = new MAIL_HANDLER($r_CustomerMail);
    
    //Tenta di aprire il server in entrata
    $testmail = $mail->mailboxOpening();
    
    if($testmail !== true){
        $mailBoxError = $testmail;
    } else {
        //Cambia la cartella con quella salvata nelle configurazioni
        $selectFolder = $mail->mailboxSelectFolder($r_CustomerMail['IncomingMailbox']);
        
        if($selectFolder !== true){
            $mailBoxError = $selectFolder;
        }
    }
} catch (Exception $e) {
    $mailBoxError = $e->getMessage();
}
////////////////////////////////////////////////////////////////////////

if(isset($mailBoxError)){
    $_SESSION['Message']['Error'] = 'Errore server di posta in entrata: '.$mailBoxError;
    
    echo json_encode(
        array(
            "Count" => $count
        )
    );
    DIE;
}

$a_uids = $mail->mailboxGetMails();

$ProgressFile = TMP . "/".$ProgressFileName;
$ProgressBar = new CLS_PROGRESSBAR(count($a_uids));
$ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Inizializzazione'));

foreach($a_uids as $uid){
    $rs->Start_Transaction();
    
    $ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Recupero intestazioni in corso...'));
    $Header = $mail->mailboxGetMailHeader($uid);
    $HeaderInfo = $mail->mailboxGetMailHeaderInfo($uid);
    
    $MailSubject = preg_replace('/\s+/', ' ', trim(imap_utf8($Header['Subject'] ?? ''))) ;
    $MessageId = $Header['Message-ID'] ?? '';
    $MailFrom = $Header['From'] ?? '';
    $MailTo = $Header['To'] ?? '';
    $MailCC = $Header['Cc'] ?? '';
    $MailReplyTo = $Header['Reply-To'] ?? '';
    $UTime = $HeaderInfo->udate ?? 0;
    
    $a_InsertM = array(
        array('field'=>'MessageId','selector'=>'value','type'=>'str','value'=>$MessageId),
        array('field'=>'MailFrom','selector'=>'value','type'=>'str','value'=>$MailFrom),
        array('field'=>'MailTo','selector'=>'value','type'=>'str','value'=>$MailTo),
        array('field'=>'MailCC','selector'=>'value','type'=>'str','value'=>$MailCC),
        array('field'=>'MailReplyTo','selector'=>'value','type'=>'str','value'=>$MailReplyTo),
        array('field'=>'MailSubject','selector'=>'value','type'=>'str','value'=>$MailSubject),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    );
    
    if($UTime > 0){
        $a_InsertM[] = array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d", $UTime));
        $a_InsertM[] = array('field'=>'ReceiveTime','selector'=>'value','type'=>'str','value'=>date("H:i:s", $UTime));
    }
    
    $InsertId = $rs->Insert('DownloadEmail', $a_InsertM);
    $PathToSave = MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'].'/'.$InsertId.'/';
    
    if (!is_dir($PathToSave)) {
        mkdir($PathToSave);
        chmod($PathToSave, 0750);
    }
    
    $ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Salvataggio degli allegati in corso...'));
    $a_Parts = $mail->mailboxGetMailFullBody($uid);

    //Se ci sono allegati li salva e ne crea un record collegato all'id del record ella mail
    if(isset($a_Parts['Attachments'])){
        foreach ($a_Parts['Attachments'] as $attachment){
            $attachmentName = $attachment['Name'];
            
            $a_InsertA = array(
                array('field'=>'DownloadEmailId','selector'=>'value','type'=>'int','value'=>$InsertId),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$attachmentName)
            );
            
            $rs->Insert('DownloadEmailAttachments', $a_InsertA);
            
            file_put_contents(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'].'/'.$InsertId.'/'.$attachmentName, $attachment['Data']);
        }
    }
    
    $ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Salvataggio del messaggio in corso...'));
    
    //Scarica le mail (eml) e le cancella dalla casella
    $mail->mailboxExportSingleMail($uid, $PathToSave, $InsertId, true);
    
    //Aggancia i pezzi di corpo della mail in un unico file .html, in modo da poterlo poi visualizzare
    file_put_contents(MAILDOWNLOAD_FOLDER.'/'.$_SESSION['cityid'].'/'.$InsertId.'/'.$InsertId.'.html', implode('<br><br>', $a_Parts['Text']));

    $ProgressBar->writeJSON($count++, $ProgressFile, array('Passo' => ''));
    
    $rs->End_Transaction();
}

if($count > 0){
    $a_Import = array(
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>8),
        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>'Messaggi da '.$r_CustomerMail['MailAddress'].'. Casella: '.$r_CustomerMail['IncomingMailbox']),
        array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$count),
    );
    
    $rs->Insert('ImportedFiles',$a_Import);
}

$ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Completato'));

//$expuge true -> conferma la cancellazione dei messaggi
$mail->mailboxClosing(true);

$_SESSION['Message']['Success'] = "Azione eseguita con successo. Sono stati scaricati: $count messaggi";

echo json_encode(
    array(
        "Count" => $count
    )
);
