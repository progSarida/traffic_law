<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_mail.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

global $rs;

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 'n');
$Processed = CheckValue('Processed', 'n');

$str_Error = '';

$r_DownloadEmail = $rs->getArrayLine($rs->Select('DownloadEmail', "Id=$Id AND CityId='{$_SESSION['cityid']}'"));

if($r_DownloadEmail){
    $Status = 'read';
    
    $rs->Start_Transaction();
    
    $rs->Delete('DownloadEmailCategories', "DownloadEmailId=$Id");
    
    if(isset($_POST['Categories'])){
        foreach($_POST['Categories'] as $categoryId){
            $a_Insert = array(
                array('field'=>'DownloadEmailId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
                array('field'=>'CategoryId','selector'=>'value','type'=>'int','value'=>$categoryId,'settype'=>'int'),
            );
            
            $rs->Insert('DownloadEmailCategories', $a_Insert);
        }
        $Status = 'toprocess';
    } else if($r_DownloadEmail['Status'] == 'toprocess' || $r_DownloadEmail['Status'] == 'processed'){
        $Status = 'toprocess';
    }
    
    if($Processed > 0){
        $Status = 'processed';
    } 
    
    if($r_DownloadEmail['Status'] != $Status){
        $a_Update = array(
            array('field'=>'Status','selector'=>'value','type'=>'str','value'=>$Status),
            array('field'=>'VersionDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
            array('field'=>'VersionTime','selector'=>'value','type'=>'str','value'=>date('H:i:s')),
        );
        
        $rs->Update('DownloadEmail', $a_Update, "Id=$Id");
    }
    
    $rs->End_Transaction();
} else $str_Error = 'Impossibile aggiornare i dati. Verificare che il messaggio selezionato appartenga all\'ente in uso.';

if($str_Error != '') {
    $_SESSION['Message']['Error'] = $str_Error;
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: mgmt_pec_download.php".$Filters);

