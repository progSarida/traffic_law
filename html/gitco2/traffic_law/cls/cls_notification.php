<?php
include_once (CLS . "/cls_ftp.php");
/**
 * Class cls_notification
 */
class cls_notification
{
    public $a_path;
    public $a_ftp;
    public $FTP;
    public $filterBySubstring = null;

    public function __construct($printerId, $printType=null, $ftpEnabled = true)
    {
        if($printerId>0){
            $this->a_path['toImport'] = PUBLIC_FOLDER . "/_NOTIFICATIONS_/TO_IMPORT/";
            $this->a_path['download'] = PUBLIC_FOLDER . "/_NOTIFICATIONS_/DOWNLOADED/";
            $this->setPrinterParams($printerId, $printType);
            $this->ftpEnabled = $ftpEnabled;
            if($this->ftpEnabled)
                $this->ftpConnect();
        }
        else{
            echo "IDENTIFICATIVO STAMPATORE ERRATO";
            die;
        }
    }

    public function setPrinterParams($printerId, $printType=null){
        switch($printerId){
            case 2:
                switch($printType){
                    case "AG":
                        $this->a_path['ftpToImport'] = '/Recupero_Immagini/AG/';
                        $this->a_path['ftpArchive'] = 'NOTIFICHE IMPORTATE/TRAFFIC_LAW/';
                        break;
                    case "AR":
                        $this->a_path['ftpToImport'] = '/Recupero_Immagini/AR_EE/';
                        $this->a_path['ftpArchive'] = 'NOTIFICHE IMPORTATE/TRAFFIC_LAW/';
                        break;
                    default:
                        echo "ERRORE NEL SETTAGGIO DELLA CARTELLA DELLO STAMPATORE";
                        die;
                }

                $this->a_ftp['FTP_HOST'] = 'ftp.mercurioservice.it';
                $this->a_ftp['FTP_USER'] = 'sarida';
                $this->a_ftp['FTP_PASS'] = '1ftp4sarida';
                $this->filterBySubstring = "verbali";

                break;

            case 4:
                $this->a_path['ftpNotifications'] = null;
                $this->a_path['ftpArchiveFolder'] = 'IMPORTED/TRAFFIC_LAW/';
                $this->a_ftp['FTP_HOST'] = null;
                $this->a_ftp['FTP_USER'] = null;
                $this->a_ftp['FTP_PASS'] = null;

                break;
            default:
                echo "STAMPATORE NON ABILITATO!";
                die;
        }
    }

    public function ftpConnect($isPassive = true){
        $this->FTP = new cls_ftp($this->a_ftp['FTP_HOST'], $this->a_ftp['FTP_USER'], $this->a_ftp['FTP_PASS'], $isPassive);
        $this->FTP->makeDir($this->a_path['ftpToImport'].$this->a_path['ftpArchiveFolder']);
    }

    public function downloadNotificationsZip($folderName){
        if(!$this->ftpEnabled)
            return false;

        mkdir($this->a_path['download'].$folderName,0777);
        $this->FTP->changeDir($this->a_path['ftpToImport'].$folderName);
        $this->FTP->downloadFolder($this->a_path['download'].$folderName."/",$this->filterBySubstring);
    }

    public function archivePrinterFiles($folderName){
        if(!$this->ftpEnabled)
            return false;

        $this->FTP->changeDir($this->a_path['ftpToImport']);
        $this->FTP->moveFiles($this->a_path['ftpToImport'],$folderName,$this->a_path['ftpArchiveFolder'].$folderName,$this->filterBySubstring);
    }

    public function getNotificationsFolder(){
        if(!$this->ftpEnabled)
            return array();

        $this->FTP->changeDir($this->a_path['ftpToImport']);
        $a_ftpTempFolders = $this->FTP->getDirListing();
        $a_ftpFolders = array();
        foreach($a_ftpTempFolders as $folder){
            $this->FTP->changeDir($this->a_path['ftpToImport'].$folder);
            $a_ftpFiles = $this->FTP->getDirListing();
            if(count($a_ftpFiles)==0)
                $this->FTP->deleteDirectory($this->a_path['ftpToImport'].$folder);
            foreach ($a_ftpFiles as $file){
                if(is_null($this->filterBySubstring) || strpos(strtoupper($file),strtoupper($this->filterBySubstring))!==false){
                    $a_ftpFolders[] = $folder;
                    break;
                }
            }
        }
        return $a_ftpFolders;
    }

    public function getDownloadedFolder(){
        if($directory_handle = opendir($this->a_path['download'])){
            while (($file = readdir($directory_handle)) !== false) {
                if($file!="." && $file!=".."){
                    return $file;
                }
            }
            closedir($directory_handle);
        }

        return null;
    }

    public function getDownloadedFiles($folderName, $deleteArchives = null){
        $a_downloadedFiles = array();
        if(!is_null($folderName)){
//            echo $this->a_path['download'].$folderName;
            exec("chmod -R 0777 ".$this->a_path['download'].$folderName."/");
//            chmod($this->a_path['download'].$folderName."/",0777);
            if($directory_handle = opendir($this->a_path['download'].$folderName)){
                while (($file = readdir($directory_handle)) !== false) {
                    if($file!="." && $file!=".."){
                        $expFile = explode(".",$file);
                        exec("chmod -R 0777 ".$this->a_path['download'].$folderName."/".$file);
//                        chmod($this->a_path['download'].$folderName."/".$file,0777);
                        if($deleteArchives==1 && is_file($this->a_path['download'].$folderName."/".$file))
                            unlink($this->a_path['download'].$folderName."/".$file);
                        else if(strtolower($expFile[count($expFile)-1])=="zip")
                            $a_downloadedFiles[] = $file;

                    }
                }
                closedir($directory_handle);
            }
            if($deleteArchives==1){
                if(is_dir($this->a_path['download'].$folderName))
                    rmdir($this->a_path['download'].$folderName);
            }
        }

        return $a_downloadedFiles;
    }

    public function extractAndImport($folderName, $files){
        $zip = new ZipArchive();
        foreach($files as $archiveToExtract){
            $zip->open($this->a_path['download'].$folderName."/".$archiveToExtract, ZipArchive::CREATE);
            $exts=array('jpg','jpeg','png','csv','txt','pdf','TXT','JPG','JPEG','PNG','CSV','PDF');
            for($i = 0; $i < $zip->numFiles; $i++) {
                $file_name = $zip->getNameIndex($i);

                if(strpos($file_name,"_MACOS")===false){
                    $ext = pathinfo( $file_name, PATHINFO_EXTENSION );
                    $basename = pathinfo( $file_name, PATHINFO_BASENAME );
                    if( in_array( $ext, $exts ) ) {
                        $files[]=$file_name;
                        $checkFileInsideZip=true;
                        if($ext=="csv" || $ext=="CSV" || $ext=="txt" || $ext=="TXT"){
                            $expZipName = explode(".",$archiveToExtract);
                            $replaceName = str_replace(" ","",$expZipName[0]);
                            $handle = fopen('zip://'.$this->a_path['download'].$folderName."/".$archiveToExtract.'#'.$file_name, 'r');
                            $result = fread($handle,100);
                            fclose($handle);
                            $expFile = explode(";",$result);
//                            echo substr($result,0,10);
//                            if(count($expFile)>2){
//                                for($z=0;$z<3;$z++){
//                                    switch($z){
//                                        case 0: if($expFile[$z]!="cod_comune")   $checkFileInsideZip = false;  break;
//                                        case 1: if($expFile[$z]!="num_viol")   $checkFileInsideZip = false;  break;
//                                        case 2: if($expFile[$z]!="REC_NOME")   $checkFileInsideZip = false;  break;
//                                        default: $checkFileInsideZip = false;  break;
//                                    }
//                                }
//                            }
//                            else if(substr($result,0,10)!="cod_comune"){
//                                $checkFileInsideZip = false;
//                            }

                            $basename = $replaceName."_".date("Y-m-d_h-i-s").".csv";
                            sleep(1);
                        }

                        if($checkFileInsideZip===true){
                            $res = copy( 'zip://'.$this->a_path['download'].$folderName."/".$archiveToExtract.'#'.$file_name, $this->a_path['toImport'] . $basename );
                        }
                    }
                }
            }
            $zip->close();
        }
    }

    public function getCsv(){
        $a_csv = array();
        if ($directory_handle = opendir($this->a_path['toImport'])) {

            while (($file = readdir($directory_handle)) !== false) {
                $aFile = explode(".", "$file");
                if (strtolower($aFile[count($aFile) - 1]) == "csv") {
                    $a_csv[] = $file;
                }
            }
            closedir($directory_handle);
        }
        return $a_csv;
    }

    public function removeImportedFile($filename){
        if($filename!=null)
            if(is_file($this->a_path['toImport'].$filename))
                unlink($this->a_path['toImport'].$filename);
    }
}