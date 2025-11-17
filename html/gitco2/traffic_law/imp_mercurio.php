<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS . "/cls_ftp.php");
require_once(INC."/function.php");
require_once(INC."/header.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

ini_set('max_execution_time', 0);

$FileList = "";
$Cont = 0;

$path = PUBLIC_FOLDER . "/_MERCURIO_II_/IMPORT/";
$downloadPath = PUBLIC_FOLDER . "/_MERCURIO_II_/DOWNLOAD/";

$DeleteImportFile = CheckValue('DeleteImportFile','s');
if($DeleteImportFile!=null)
    if(is_file($path.$DeleteImportFile))
        unlink($path.$DeleteImportFile);

$DeleteImportFile = CheckValue('DeleteImportFile','s');
if($DeleteImportFile!=null)
    if(is_file($path.$DeleteImportFile))
        unlink($path.$DeleteImportFile);

$folderToDownload = CheckValue('folderToDownload', 's');
$folderToArchive = CheckValue('folderToArchive', 's');
$extractArchives = CheckValue('extractArchives', 's');
$deleteArchives = CheckValue('deleteArchives', 's');

define('FTP_HOST', 'ftp.mercurioservice.it');
define('FTP_USER', 'sarida');
define('FTP_PASS', '1ftp4sarida');
$ftp = new cls_ftp(FTP_HOST, FTP_USER, FTP_PASS,true);
$folder_mercurio = '/Recupero_Immagini/AG/';
if($folderToDownload!=""){
    mkdir($downloadPath.$folderToDownload,0777);
    $ftp->changeDir($folder_mercurio.$folderToDownload);
    $ftp->downloadFolder($downloadPath.$folderToDownload."/","verbali");
}
else if($folderToArchive!=""){
    $ftp->changeDir($folder_mercurio);
    $ftp->moveFiles($folder_mercurio,$folderToArchive,"NOTIFICHE IMPORTATE/TRAFFIC_LAW/".$folderToArchive,"VERBALI");
}

$ftp->changeDir($folder_mercurio);
$a_ftpTempFolders = $ftp->getDirListing();
$a_ftpFolders = array();
foreach($a_ftpTempFolders as $folder){
    $ftp->changeDir($folder_mercurio.$folder);
    $a_ftpFiles = $ftp->getDirListing();
    if(count($a_ftpFiles)==0)
        $ftp->deleteDirectory($folder_mercurio.$folder);
    foreach ($a_ftpFiles as $file){
        if(strpos($file,"VERBALI")!==false){
            $a_ftpFolders[] = $folder;
            break;
        }
    }
}

$downloadedFolder = "";
if($directory_handle = opendir($downloadPath)){
    while (($file = readdir($directory_handle)) !== false) {
        if($file!="." && $file!=".."){
            $downloadedFolder = $file;
            break;
        }
    }
    closedir($directory_handle);
}
$a_downloadedFiles = array();
if($downloadedFolder!=""){
    trigger_error("MERCURIO downloadedFolder", E_USER_WARNING);
    chmod($downloadPath.$downloadedFolder."/",0777);
    if($directory_handle = opendir($downloadPath.$downloadedFolder)){
        while (($file = readdir($directory_handle)) !== false) {
            if($file!="." && $file!=".."){
                $expFile = explode(".",$file);
                chmod($downloadPath.$downloadedFolder."/".$file,0777);
                if($deleteArchives==1 && is_file($downloadPath.$downloadedFolder."/".$file))
                    unlink($downloadPath.$downloadedFolder."/".$file);
                else if(strtolower($expFile[count($expFile)-1])=="zip")
                    $a_downloadedFiles[] = $file;

            }
        }
        closedir($directory_handle);
    }
    trigger_error("MERCURIO FINE downloadedFolder", E_USER_WARNING);
    
    if($deleteArchives==1){
        trigger_error("MERCURIO deleteArchives 1", E_USER_WARNING);
        if(is_dir($downloadPath.$downloadedFolder))
            rmdir($downloadPath.$downloadedFolder);
        $downloadedFolder = "";
    }

}

if($extractArchives==1){
    trigger_error("MERCURIO extractArchives 1", E_USER_WARNING);
    $zip = new ZipArchive();
    foreach($a_downloadedFiles as $archiveToExtract){
        $zip->open($downloadPath.$downloadedFolder."/".$archiveToExtract, ZipArchive::CREATE);
        $exts=array('jpg','jpeg','png','csv','txt','TXT','JPG','JPEG','PNG','CSV');
        trigger_error("MERCURIO extractArchives 1 - nome file: ".$archiveToExtract, E_USER_WARNING);
        trigger_error("MERCURIO extractArchives 1: ".$zip->numFiles, E_USER_WARNING);
        for($i = 0; $i < $zip->numFiles; $i++) {
            $file_name = $zip->getNameIndex($i);

            if(strpos($file_name,"_MACOS")===false){
                $ext = pathinfo( $file_name, PATHINFO_EXTENSION );
                $basename = pathinfo( $file_name, PATHINFO_BASENAME );

                /* store a reference to the file name for extraction or copy */
                if( in_array( $ext, $exts ) ) {
                    $files[]=$file_name;
                    $checkFileInsideZip=true;
                    if($ext=="csv" || $ext=="CSV" || $ext=="txt" || $ext=="TXT"){
                        $expZipName = explode(".",$archiveToExtract);
                        $replaceName = str_replace(" ","",$expZipName[0]);
                        $handle = fopen('zip://'.$downloadPath.$downloadedFolder."/".$archiveToExtract.'#'.$file_name, 'r');
                        $result = fread($handle,100);
                        fclose($handle);
                        $expFile = explode(";",$result);
                        echo substr($result,0,10);
                        if(count($expFile)>2){
                            for($z=0;$z<3;$z++){
                                switch($z){
                                    case 0: if($expFile[$z]!="cod_comune")   $checkFileInsideZip = false;  break;
                                    case 1: if($expFile[$z]!="num_viol")   $checkFileInsideZip = false;  break;
                                    case 2: if($expFile[$z]!="REC_NOME")   $checkFileInsideZip = false;  break;
                                    default: $checkFileInsideZip = false;  break;
                                }
                            }
                        }
                        else if(substr($result,0,10)!="cod_comune"){
                            $checkFileInsideZip = false;
                        }

                        $basename = $replaceName."_".date("Y-m-d_h-i-s").".csv";
                        sleep(1);
                    }

                    /* To extract files and ignore directory structure */
                    if($checkFileInsideZip===true){
                        $res = copy( 'zip://'.$downloadPath.$downloadedFolder."/".$archiveToExtract.'#'.$file_name, $path . $basename );
                    }
//                echo ( $res ? 'Copiato: '.$basename : 'Impossibile copiare: '.$basename ) . '<br />';
                }
            }
        }
        $zip->close();
        trigger_error("MERCURIO FINE extractArchives 1: ".$zip->numFiles, E_USER_WARNING);
        trigger_error("MERCURIO FINE extractArchives 1: nome file csv".$path . $basename, E_USER_WARNING);
    }
}

$checkFile = false;
if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "csv") {
            $checkFile = true;
            $Cont++;
            $FileList .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . $Cont . '</div>
            <div class="table_caption_H col-sm-10">' . $file . '</div>
            <div class="table_caption_button col-sm-1">
                ' . ChkButton($aUserButton, 'imp', '<a href="' . $str_CurrentPage . '&ImportFile=' . $file . '"><span class="fa fa-download"></span></a>') . '
                &nbsp;'.ChkButton($aUserButton, 'imp', '<a href="' . $str_CurrentPage . '&DeleteImportFile=' . $file . '"><span class="fa fa-trash"></span></a>').'
            </div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}


$str_download = '<div class="col-sm-6">
                    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>CARTELLA NOTIFICHE SCARICATE '.$downloadedFolder.'</b></div>
				    <div class="clean_row HSpace4"></div>
				    <div class="table_caption_H col-sm-12" style="height:35rem;overflow:auto">';
foreach($a_downloadedFiles as $donwloadedFile){
    $str_download.='<div class="table_caption_H col-sm-12">'.$donwloadedFile.'</div>';
}
if($checkFile===true || count($a_downloadedFiles)==0){
    $disabled = "disabled";
    $backgroundcolor = "lightgrey";
    $backgroundcolor2 = "lightgrey";
    $backgroundcolor3 = "lightgrey";
}
else{
    $disabled = "";
    $backgroundcolor = "#78ba71";
    $backgroundcolor2 = "#decb4d";
    $backgroundcolor3 = "#e7767a";
}

$str_download.= '</div>
<div class="table_label_H col-sm-12">
<button id="extract_btn" style="font-size:12px; background-color: ' .$backgroundcolor.'; width: 15rem; height:95%;" '.$disabled. '><b>ESTRAI</b></button>
<button id="delete_btn" style="font-size:12px;background-color: '.$backgroundcolor2.'; width: 15rem; height:95%;" ' .$disabled. '><b>ELIMINA</b></button>
<button id="delete_archive_btn" data-id="'.$downloadedFolder.'" style="font-size:12px;background-color: '.$backgroundcolor3.'; width: 15rem; height:95%;" ' .$disabled.'><b>ELIMINA E ARCHIVIA</b></button>
</div>
</div>';

if($downloadedFolder!=""){
    $disabled = "disabled";
    $backgroundcolor = "lightgrey";
    $backgroundcolor2 = "lightgrey";
}
else{
    $disabled = "";
    $backgroundcolor = "#78ba71";
    $backgroundcolor2 = "#de9eb4";
}

$str_ftp = '<div class="col-sm-6">
                    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>FTP MERCURIO NAZIONALI</b></div>
				<div class="clean_row HSpace4"></div>
				<div class="table_caption_H col-sm-12" style="height:35rem;overflow:auto">';
foreach($a_ftpFolders as $ftpFolder){
    $str_ftp.='<div class="table_caption_H col-sm-6">'.$ftpFolder.'</div>
                <div class="table_caption_H col-sm-6">
                <button id="download-'.$ftpFolder.'" class="ftp_download_btn" style="background-color: '.$backgroundcolor.'; width: 8rem; height:95%;" '.$disabled.'>SCARICA</button>
                <button id="store-'.$ftpFolder.'" class="ftp_store_btn" style="background-color: '.$backgroundcolor2.'; width: 8rem; height:95%;" '.$disabled.'>ARCHIVIA</button>
</div>
';
}

$str_ftp.='</div>
<div class="table_label_H col-sm-12">
</div>
</div>';



$error = false;
$imported = false;
$msgProblem = "";
if ($Cont == 0) {
    $FileList = '
            <div class="col-sm-12">
                <div class="table_caption_H col-sm-11">Nessun file presente</div>
                <div class="table_caption_button col-sm-1"></div>
			    <div class="clean_row HSpace4"></div>
			</div>    
			';
}

$str_out = '
    	<div class="row-fluid">
            <div class="col-sm-12 alert alert-warning" style="display: flex;margin: 0px;align-items: center;">
                <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                <div class="col-sm-11" style="font-size: 1.2rem;">
                    <ul>
                    	<li>
                            <strong>Avviso agli operatori</strong><br>
                            La procedura corrente è stata aggiornata a maggio 2022 per integrare la gestione del flag "Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta" quando si importano notifiche per atti con violazione relativa ad assicurazione e revisioni.<br>
                            Contattare l\'assistenza per verifiche se l\'ente tratta questo tipo di verbali.
                        </li>
                    </ul>
                </div>
            </div>
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="' . $_SESSION['blazon'] . '" style="width:50px;">
					<span class="title_city">' . $_SESSION['citytitle'] . ' ' . $_SESSION['year'] . '</span>
				</div>
			</div>
		</div>		
		<div class="row-fluid">
		    '.$str_download.$str_ftp.'
        </div>
        <div class="clean_row HSpace16"></div>
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-11">ELENCO FILE</div>
				<div class="table_add_button col-sm-1 right">
        			' . ChkButton($aUserButton, 'add', '<a href="mgmt_violation_add.php"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>') . '      				
				</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList. '
        </div>';

echo $str_out;

$ImportFile = CheckValue('ImportFile','s');
$rs= new CLS_DB();
if($ImportFile==""){
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
}else{



    $rs_Result = $rs->Select('Result', "Disabled=0");
    $a_chk_Result = array();
    while ($r_Result = mysqli_fetch_array($rs_Result)){
        $a_chk_Result[] = $r_Result['Description'];
        $a_Result[$r_Result['Description']] = $r_Result['Id'];
    }

//    $file = fopen($path.$ImportFile,  "r");
//    $delimiter = ";";
    $cont = 0;




    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Riga | Ente | Img</div>
            <div class="table_label_H col-sm-1">Cron</div>
            <div class="table_label_H col-sm-1">Data not</div>
            <div class="table_label_H col-sm-1">Raccomandata</div>
            <div class="table_label_H col-sm-1">Ricevuta</div>
            <div class="table_label_H col-sm-2">Tipo Notifica</div>
            <div class="table_label_H col-sm-1">Data sped</div>
            <div class="table_label_H col-sm-1">Posizione</div>
            <div class="table_label_H col-sm-1">Data log</div>
            <div class="table_label_H col-sm-2">Controllo</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';

    ini_set("auto_detect_line_endings", true);
    $csv = array();
    $lines = file($path . $ImportFile);
    foreach ($lines as $key_line => $a_line)
    {
        $csv[$key_line] = str_getcsv($a_line,";");
        foreach ($csv[$key_line] as $key => $value)
        {
            $csv[$key_line][$key] = trim($value);
        }
    }

    trigger_error("MERCURIO INIZIO estarzione dati da importare", E_USER_WARNING);
    foreach ($csv as $row) {
//        $row = fgetcsv($file, 1000, $delimiter);
        if(isset($row[0]) && strtolower($row[0])!="cod_comune"){
            $cont++;

            $CityId = $row[0];

            $a_Row = explode("/",$row[1]);
            $ProtocolId = $a_Row[0];
            $ProtocolYear = $a_Row[1];

            $NotificationDate = $row[5];
            $LetterNumber = $row[13];
            $ReceiptNumber = $row[6];

            $NotificationType = trim($row[7]);
            $a_Notification = explode("-",$NotificationType);
            if(isset($a_Notification[1])) $NotificationType = trim($a_Notification[0])." - ".trim($a_Notification[1]);


            $NotificationStatus = trim($row[8]);
            $a_Notification = explode("-",$NotificationStatus);
            if(isset($a_Notification[1])) $NotificationStatus = trim($a_Notification[0])." - ".trim($a_Notification[1]);

            $ImgFront = $row[10];
            $ImgBack = $row[11];
            
            //Nel caso i campi delle immagini siano di estensione diversa da jpg o uno di essi è vuoto, tento di dedurre i nomi attesi di entrambe
            if(!empty($ImgFront)){
                $a_ImgFront = pathinfo($ImgFront);
                $ImgFrontBase = str_replace("_F", "", $a_ImgFront['filename']);
                
                if($a_ImgFront['extension'] != 'jpg'){
                    $ImgFront = $ImgFrontBase.'_F.jpg';
                } else $ImgFront = $a_ImgFront['basename'];
                
                if(empty($ImgBack)){
                    $ImgBack = $ImgFrontBase.'_R.jpg';
                }
            }
            
            if(!empty($ImgBack)){
                $a_ImgBack = pathinfo($ImgBack);
                $ImgBackBase = str_replace("_R", "", $a_ImgBack['filename']);
                
                if($a_ImgBack['extension'] != 'jpg'){
                    $ImgBack = $ImgBackBase.'_R.jpg';
                } else $ImgBack = $a_ImgBack['basename'];
                
                if(empty($ImgFront)){
                    $ImgFront = $ImgBackBase.'_F.jpg';
                }
            }
            
            $SendDate = $row[14];
            $Box = $row[15];
            $Lot = $row[16];
            $Position = $row[17];

            $a_LogDate = explode(" ",$row[12]);
            $LogDate = $a_LogDate[0];


            $chkFine = "";
            $chkControlColor = "";
            $chkSendDate = "";
            $chkDeliveryDate = "";
            $chkNotification = "";

            $chkImgBack = (file_exists($path.$ImgBack) && !is_dir($path.$ImgBack)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";

            $chkImgFront = (file_exists($path.$ImgFront) && !is_dir($path.$ImgFront)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";



//            $rs_Fine = $rs->Select('Fine', "CountryId='Z000' AND CityId='" . $CityId . "' AND ProtocolId=" . $ProtocolId ." AND ProtocolYear=".$ProtocolYear);
            $queryFine = "
                SELECT F.Id, FN.* FROM Fine F
                LEFT JOIN FineNotification FN ON F.Id=FN.FineId
                WHERE F.CountryId='Z000' AND F.CityId='" . $CityId . "' AND F.ProtocolId=" . $ProtocolId ." 
                AND F.ProtocolYear=".$ProtocolYear." ORDER BY F.Id ASC";
            ;
            $rs_Fine = $rs->getResults($rs->ExecuteQuery($queryFine));
            $chkMessage = "";
            if (count($rs_Fine) == 0) {
                $error = true;
                $chkFine = ' alert-danger ';
                $chkControlColor = ' alert-danger ';
                $chkMessage = 'Verbale non trovato';
                $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Verbale non trovato</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
            }
            else{
                $i=1;
                foreach($rs_Fine as $r_Fine){
                    if($LetterNumber==$r_Fine['LetterNumber'] && $ReceiptNumber==$r_Fine['ReceiptNumber']){
                        $imported = true;
                        $chkControlColor = ' alert-success ';
                        $chkMessage = 'Notifica gia importata';

                        break;
                    }
                    else{
                        $r_FineHistory = $rs->getArrayLine($rs->ExecuteQuery("SELECT * FROM FineHistory WHERE FineId=" . $r_Fine['Id']. " AND NotificationTypeId=6"));

                        if($r_FineHistory['SendDate']==DateInDB($SendDate)){//CONTROLLO SE DATA SPEDIZIONE COINCIDE CON DATA FLUSSO

                            if(!is_null($r_FineHistory['DeliveryDate'])){//CONTROLLO SE DATA DI CONSEGNA PRESENTE
                                $chkMessage = 'Data notifica già presente';
                                $chkControlColor = ' alert-warning ';
                                $chkDeliveryDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                            }
                            break;

                        }
                        else {
                            if($i==count($rs_Fine)){//CONTROLLO ATTIVO SOLO SU ULTIMO VERBALE
                                $chkMessage = 'Data non concidente';
                                $chkControlColor = ' alert-warning ';
                                $chkSendDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                                if(!is_null($r_FineHistory['DeliveryDate'])){//CONTROLLO SE DATA DI CONSEGNA PRESENTE
                                    $chkMessage = 'Data notifica già presente';
                                    $chkControlColor = ' alert-warning ';
                                    $chkDeliveryDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                                }
                            }
                        }
                    }
                    $i++;
                }

            }

            if (in_array(trim($NotificationStatus), $a_chk_Result)) {

                $NotificationType = $a_Result[$NotificationStatus].'/'.$NotificationStatus;


            } else {
                if(!in_array(trim($NotificationType), $a_chk_Result)) {

                    $error = true;
                    $chkMessage = 'Notifica ' . trim($NotificationType) . ' o tipo "'.trim($NotificationStatus).'" non presente';
                    $chkControlColor = ' alert-danger ';
                    $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                }else{

                    $NotificationType = $a_Result[$NotificationType].'/'.$NotificationType;
                }

            }

            $str_out .='
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1'.$chkFine.'">
                    <div class="col-sm-3">'.$cont.'</div>
                    <div class="col-sm-3">'.$CityId.'</div>
                    <div class="col-sm-6 text-center">F '. $chkImgFront . ' R '.$chkImgBack.'</div>
                </div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $ProtocolId . '/' . $ProtocolYear.'</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $chkDeliveryDate .' '. $NotificationDate .'</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $LetterNumber . '</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $ReceiptNumber . '</div>
                <div class="table_caption_H col-sm-2'.$chkFine.'">'. $chkNotification .' '.$NotificationType . '</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $chkSendDate .' '.$SendDate . '</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $Box. ' - '.$Lot .' - '.$Position.'</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'. $LogDate.'</div>
                <div class="table_caption_H col-sm-2'.$chkControlColor.'">'. $chkMessage.'</div>
                <div class="clean_row HSpace4"></div>
			</div>    
            ';
        }

    }
    trigger_error("MERCURIO FINE estarzione dati da importare", E_USER_WARNING);
    
//    fclose($file);
    if($error===true){
        $str_out .= '
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 "></div>
			<div class="clean_row HSpace4"></div>
		</div>
		<br><br>';
    }
    else if($imported===true){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_mercurio.php">
            <input type="hidden" name="DeleteImportFile" value="'.$ImportFile.'">
            <div class="table_label_H col-sm-12">
                Elimina '.$ImportFile.'
                <input type="submit" value="Elimina" >
            </div >
		</div ><br><br>';
    }
    else{
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_mercurio_exe.php">
            <input type="hidden" name="P" value="imp_mercurio.php">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
            <div class="table_label_H col-sm-12">
                Comprimi immagini
                <select name="compress">
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
                <input type="submit" value="Importa" >                           
            </div >
		</div ><br><br>';
    }
}

echo $str_out;

?>

<script>
    var folderToDownload = "<?php if(isset($a_ftpFolders[0])) echo $a_ftpFolders[0]; else '';?>";
    // When the document is ready
    $(document).ready(function () {

        $("#extract_btn").click(function(){
            alert("EXTRACT");
            location.href = "imp_mercurio.php<?php echo $str_GET_Parameter;?>&extractArchives=1";
        });

        $("#delete_btn").click(function(){
            alert("DELETE");
            location.href = "imp_mercurio.php<?php echo $str_GET_Parameter;?>&deleteArchives=1";
        });

        $("#delete_archive_btn").click(function(){
            let folderDelArch = $('#delete_archive_btn').attr('data-id');
            alert("DELETE AND ARCHIVE "+folderDelArch);
            if(confirm("Vuoi eliminare e archiviare la cartella "+folderDelArch+"?")===true){
                location.href = "imp_mercurio.php<?php echo $str_GET_Parameter;?>&deleteArchives=1&folderToArchive="+folderDelArch;
                return true;
            }

        });

        $(".ftp_download_btn").click(function(){
            var download_id = $(this).attr("id");
            var result = download_id.split('-');
            if(confirm("Vuoi scaricare la cartella "+result[1]+"?")===true){
                location.href = "imp_mercurio.php<?php echo $str_GET_Parameter;?>&folderToDownload="+result[1];
                return true;
            }
            return false;
        });

        $(".ftp_store_btn").click(function(){
            var download_id = $(this).attr("id");
            var result = download_id.split('-');
            if(confirm("Vuoi archiviare la cartella "+result[1]+"?")===true){
                location.href = "imp_mercurio.php<?php echo $str_GET_Parameter;?>&folderToArchive="+result[1];
                return true;
            }
            return false;
        });


    });

</script>
