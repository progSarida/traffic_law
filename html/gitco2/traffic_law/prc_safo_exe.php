<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
//Per SFTP
include 'vendor/autoload.php';
use phpseclib3\Crypt\RSA;
//use phpseclib\Net\SSH2;
use phpseclib3\Net\SFTP;

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

if (isset($_POST["checkBox"])){
    //Creo il file CSV
    $filename = $_SESSION['cityid'].'_Request_'.date('Y_m_d_H_i').'.csv';
    $testata=array( "NumeroVerbale"=>"Numero verbale",
                    "Targa"=>"Targa",
                    "TipoVeicolo"=>"Tipo Veicolo",
                    "DataInfrazione"=>"Data Infrazione",
                    "OraInfrazione"=>"Ora Infrazione",
                    "DataScadenza"=>"Data Scadenza",
                    "SocietaDiNoleggio"=>"Societa Di Noleggio",
                    "ArticleEComma"=>"Article E Comma",
                    "DescrizioneInfrazione"=>"Descrizione Infrazione",
                    "Localita"=>"Localita");
    
    // directory creation
    $directory='doc/national/safo_request/';
    if (!is_dir($directory))
        mkdir($directory, 0777, true);
    $directory=$directory.$_SESSION['cityid'].'/';
    if (!is_dir($directory))
        mkdir($directory, 0777, true);
    // file creation
    $file = fopen($directory.$filename,"w");
    fputcsv($file,$testata,";");

//    $fixed_export_data = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {      
//            return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
//    },$_POST['export_data'] );
//    $export_data = unserialize($fixed_export_data);
    
    $export_data = unserialize_POSTed_string($_POST['export_data']);
    foreach ($export_data as $line){
        foreach ($_POST['checkBox'] as $nFineId)
        {
            if ((!is_null($nFineId))&&($line['FineId']==$nFineId))
            {
                $lineWoutFineId=$line; 
                unset($lineWoutFineId["FineId"]); 
                
                fputcsv($file,$lineWoutFineId,";");
            }
        }
    }
    fclose($file);
    echo "File CSV Creato Correttamente<br>";
    
    //Faccio l'SFTP sul sito SAFO
    $key = new RSA();
    $key->loadKey(file_get_contents('Sarida.key'));
    $sftp = new SFTP(SAFO_GATEWAY);
    if (!$sftp->login("Sarida", $key)) {
            throw new Exception('Login failed');
    }
    
    $upload = $sftp->put("uploads/".$filename,$directory.$filename, SFTP::SOURCE_LOCAL_FILE);
    //$success_t = $sftp->put($remote_directory . $temps_file,$local_directory . $temps_file);
    if($upload){    
        //Creo le tuple nella tabella tmp_safofineupload
        // Loop over the sent-in / selected checkboxes
        foreach ($_POST['checkBox'] as $nFineId){
            //inserisco il record nella tabella tmp_safofineupload
            if (is_numeric($nFineId)){
                $aInsert = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$nFineId, 'settype'=>'int'),
                    array('field'=>'UploadDate','selector'=>'value','type'=>'date','value'=>date('d/m/Y')),
                );
                $rs->Insert('TMP_SafoFineUpload',$aInsert);
            }
            else
                continue;
        }
        echo ("Upload su Server SAFO OK <br>");
    }
    else
        echo ("KO : Upload su Server SAFO non riuscito");
    

}
else{
    echo "Nessun elemento selezionato per l'invio<br>";
    ?><a href="javascript:history.go(-1)" onMouseOver="self.status=document.referrer;return true">←Indietro</a><?php
}

function unserialize_POSTed_string(string $PostedString): array{
    if(isset($PostedString)){
        $fixed_export_data = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {      
                    return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
            },$PostedString );
        return unserialize($fixed_export_data);
    }
}

include(INC."/footer.php");
?>

 