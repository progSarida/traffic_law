<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(CLS . "/cls_ftp.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');


ini_set('max_execution_time', 0);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER . "/_MERCURIO_EE_/IMPORT/";
$downloadPath = PUBLIC_FOLDER . "/_MERCURIO_EE_/DOWNLOAD/";

$folderToDownload = CheckValue('folderToDownload', 's');
$folderToArchive = CheckValue('folderToArchive', 's');
$extractArchives = CheckValue('extractArchives', 's');
$deleteArchives = CheckValue('deleteArchives', 's');

define('FTP_HOST', 'ftp.mercurioservice.it');
define('FTP_USER', 'sarida');
define('FTP_PASS', '1ftp4sarida');
$ftp = new cls_ftp(FTP_HOST, FTP_USER, FTP_PASS,true);
$folder_esteri = '/Recupero_Immagini/AR_EE/';
if($folderToDownload!=""){
    mkdir($downloadPath.$folderToDownload,0777);
    $ftp->changeDir($folder_esteri.$folderToDownload);
    $ftp->downloadFolder($downloadPath.$folderToDownload."/");
}
else if($folderToArchive!=""){
    $ftp->changeDir($folder_esteri);
    $ftp->moveTo($folderToArchive,"NOTIFICHE IMPORTATE/".$folderToArchive);
}

$ftp->changeDir($folder_esteri);
$a_ftpFolders = $ftp->getDirListing();

$key = array_keys($a_ftpFolders,"NOTIFICHE IMPORTATE");
unset($a_ftpFolders[$key[0]]);
array_values($a_ftpFolders);

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
    if($deleteArchives==1){
        if(is_dir($downloadPath.$downloadedFolder))
            rmdir($downloadPath.$downloadedFolder);
        $downloadedFolder = "";
    }

}

if($extractArchives==1){
    $zip = new ZipArchive();
    foreach($a_downloadedFiles as $archiveToExtract){
        $zip->open($downloadPath.$downloadedFolder."/".$archiveToExtract, ZipArchive::CREATE);
        $exts=array('jpg','jpeg','png','csv','JPG','JPEG','PNG','CSV');
        for($i = 0; $i < $zip->numFiles; $i++) {
            $file_name = $zip->getNameIndex($i);

            if(strpos($file_name,"_MACOS")===false){
                $ext = pathinfo( $file_name, PATHINFO_EXTENSION );
                $basename = pathinfo( $file_name, PATHINFO_BASENAME );

                /* store a reference to the file name for extraction or copy */
                if( in_array( $ext, $exts ) ) {
                    $files[]=$file_name;

                    if($ext=="csv" || $ext=="CSV"){
                        $expZipName = explode(".",$archiveToExtract);
                        $replaceName = str_replace(" ","",$expZipName[0]);
                        $basename = $replaceName."_".date("Y-m-d_h-i-s").".csv";
                        sleep(1);
                    }
                    /* To extract files and ignore directory structure */
                    $res = copy( 'zip://'.$downloadPath.$downloadedFolder."/".$archiveToExtract.'#'.$file_name, $path . $basename );
//                echo ( $res ? 'Copiato: '.$basename : 'Impossibile copiare: '.$basename ) . '<br />';
                }
            }
        }
        $zip->close();
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
                ' . ChkButton($aUserButton, 'imp', '<a href="' . $str_CurrentPage . '&ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>') . '
                &nbsp;
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
}
else{
    $disabled = "";
    $backgroundcolor = "#78ba71";
    $backgroundcolor2 = "#de9eb4";
}

$str_download.= '</div>
<div class="table_label_H col-sm-12">
<button id="extract_btn" style="background-color: ' .$backgroundcolor.'; width: 30rem; height:95%;" '.$disabled.'>ESTRAI PER IMPORTAZIONE</button>
<button id="delete_btn" style="background-color: '.$backgroundcolor2.'; width: 30rem; height:95%;" '.$disabled.'>ELIMINA CARTELLA</button>
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
                    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>FTP MERCURIO ESTERI</b></div>
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

$ImportFile = CheckValue('ImportFile', 's');

$error = false;
$msgProblem = "";


$a_Notification = array(
    "" => "01 - AR",
    "Indirizzo Inesatto" => "03 - Indirizzo inesatto",
    "Indirizzo Insufficiente" => "04 - Indirizzo insufficiente",
    "Indirizzo Inesistente" => "03 - Indirizzo inesatto",
    "Irreperibile" => "07 - Irreperibile",
    "Non ritirato" => "07 - Irreperibile",
    "Trasferito" => "10 - Trasferito",
    "Assente" => "20 - Assente",
    "Rifiutato" => "02 - Rifiutato",
    "Sconosciuto" => "08 - Sconosciuto",
    "Deceduto" => "09 - Deceduto",
    "Compiuta Giacenza" => "21 - Compiuta Giacenza",
    "Rubato o Smarrito" => "07 - Irreperibile",
);


$rs = new CLS_DB();

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
            	
            ' . $FileList .'
        </div>';

echo $str_out;


if ($ImportFile == "") {
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
} else {


    $rs_Result = $rs->Select('Result', "Disabled=0");
    $a_chk_Result = array();
    while ($r_Result = mysqli_fetch_array($rs_Result)) {
        $a_chk_Result[] = $r_Result['Description'];
        $a_Result[$r_Result['Description']] = $r_Result['Id'];
    }

//    $file = fopen($path . $ImportFile, "r");
//    $delimiter = detectDelimiter($path . $ImportFile);
    $cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Cron</div>
            <div class="table_label_H col-sm-1">Data not</div>
            <div class="table_label_H col-sm-2">Raccomandata</div>
            <div class="table_label_H col-sm-2">Ricevuta</div>
            <div class="table_label_H col-sm-2">Tipo Notifica</div>
            <div class="table_label_H col-sm-1">Data sped</div>
            <div class="table_label_H col-sm-1">Posizione</div>
            <div class="table_label_H col-sm-1">Data log</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    ini_set("auto_detect_line_endings", true);
    $csv = array();
    $lines = file($path . $ImportFile);
    foreach ($lines as $key_line => $a_line)
    {
        $csv[$key_line] = str_getcsv($a_line);
        foreach ($csv[$key_line] as $key => $value)
        {
            $csv[$key_line][$key] = trim($value);
        }
    }

    $b_Header = true;
    foreach ($csv as $row) {
//        $row = fgetcsv($file, 0, $delimiter);

        if (isset($row[0])) {
            $a_chkRow = explode("_", $row[0]);

            if (!$b_Header) {

                $cont++;

                $CityId = $a_chkRow[4];





                if(isset($row[11])){
                    $NotificationDate = trim($row[11]);
                    $NotificationDescription = trim($row[12]);
                    $a_Row = explode("/", $row[3]);
                    $LetterNumber = $row[4];
                    $SendDate = trim($row[5]);

                    $Box = $row[14];
                    $Lot = $row[15];
                    $Position = $row[16];


                } else {
                    $NotificationDate = trim($row[4]);
                    $NotificationDescription = trim($row[5]);
                    $a_Row = explode("/", $row[1]);
                    $LetterNumber = $row[2];
                    $SendDate = trim($row[3]);

                    $Box = $row[7];
                    $Lot = $row[8];
                    $Position = $row[9];


                }

                $ProtocolId = $a_Row[0];
                $ProtocolYear = $a_Row[1];


                if ($NotificationDate != "") $NotificationDate = str_replace("-", "/", $NotificationDate);
                if (strlen($NotificationDate == 8)) {
                    $a_Date = explode("/", $NotificationDate);
                    //$NotificationDate = $a_Date[0]."/".$a_Date[1]."/"."20".$a_Date[2];
                }


                $ReceiptNumber = "";



                $NotificationStatus = $a_Notification[$NotificationDescription];

                $ImgBack = $LetterNumber . "_R.jpg";
                $ImgFront = $LetterNumber . "_F.jpg";

                $a_Documentation = array($ImgFront, $ImgBack);

                if (strlen($SendDate) == 8) {
                    $a_Date = explode("/", $SendDate);
                    $SendDate = $a_Date[0] . "/" . $a_Date[1] . "/" . "20" . $a_Date[2];
                }


                //$a_LogDate = explode(" ",$row[12]);
                //$LogDate = $a_LogDate[0];


                $LogDate = date("Y-m-d");

                $chkFine = "";
                $chkSendDate = "";
                $chkDeliveryDate = "";
                $chkNotification = "";


                $chkImgBack = (file_exists($path . "/" . $ImgBack)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";

                $chkImgFront = (file_exists($path . "/" . $ImgFront)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";


                $rs_Fine = $rs->Select('Fine', "CountryId!='Z000' AND CityId='" . $CityId . "' AND ProtocolId=" . $ProtocolId . " AND ProtocolYear=" . $ProtocolYear);


                if (mysqli_num_rows($rs_Fine) == 0) {
                    $error = true;
                    $chkFine = ' alert-danger ';
                    $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Verbale non trovato</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                } else {
                    $r_Fine = mysqli_fetch_array($rs_Fine);


                    $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $r_Fine['Id'] . " AND NotificationTypeId=6");
                    $r_FineHistory = mysqli_fetch_array($rs_FineHistory);

                    if ($r_FineHistory['SendDate'] != DateInDB($SendDate)) {


                        $chkSendDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-warning">Data non concidente (' . $r_FineHistory['Id'] . ')</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                    }
                    if (!is_null($r_FineHistory['DeliveryDate'])) {
                        $chkDeliveryDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-warning">Data notifica già presente</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                    }


                }


                if (in_array(trim($NotificationStatus), $a_chk_Result)) {

                    $NotificationType = $a_Result[$NotificationStatus] . '/' . $NotificationStatus;


                } else {


                    $error = true;
                    $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Notifica ' . trim($NotificationType) . ' o ' . trim($NotificationStatus) . ' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';


                }


                $str_out .= '
                    <div class="col-sm-12"> 
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $CityId . ' ' . $chkImgBack . ' ' . $cont . ' ' . $chkImgFront . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $ProtocolId . '/' . $ProtocolYear . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkDeliveryDate . ' ' . $NotificationDate . '</div>
                        <div class="table_caption_H col-sm-2' . $chkFine . '">' . $LetterNumber . '</div>
                        <div class="table_caption_H col-sm-2' . $chkFine . '">' . $ReceiptNumber . '</div>
                        <div class="table_caption_H col-sm-2' . $chkFine . '">' . $chkNotification . ' ' . $NotificationType . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkSendDate . ' ' . $SendDate . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $Box . ' - ' . $Lot . ' - ' . $Position . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $LogDate . '</div>
                        
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

            } else $b_Header = false;
        }

    }
//    fclose($file);
    if (!$error) {
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_mercurio_foreign_exe.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-12">
                Comprimi immagini
                <select name="compress">
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
                <input type="submit" value="Importa" >                           
            </div >
		</div >';
    }

}

echo $str_out;


if (strlen($msgProblem) > 0) {
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $msgProblem;

}


?>

<script>
    var folderToDownload = "<?php if(isset($a_ftpFolders[0])) echo $a_ftpFolders[0]; else '';?>";
// When the document is ready
$(document).ready(function () {

    $("#extract_btn").click(function(){
        alert("EXTRACT");
        location.href = "imp_mercurio_foreign.php<?php echo $str_GET_Parameter;?>&extractArchives=1";
    });

    $("#delete_btn").click(function(){
        alert("DELETE");
        location.href = "imp_mercurio_foreign.php<?php echo $str_GET_Parameter;?>&deleteArchives=1";
    });

    $(".ftp_download_btn").click(function(){
        var download_id = $(this).attr("id");
        var result = download_id.split('-');
        if(confirm("Vuoi scaricare la cartella "+result[1]+"?")===true){
            location.href = "imp_mercurio_foreign.php<?php echo $str_GET_Parameter;?>&folderToDownload="+result[1];
            return true;
        }
        return false;
    });

    $(".ftp_store_btn").click(function(){
        var download_id = $(this).attr("id");
        var result = download_id.split('-');
        if(confirm("Vuoi archiviare la cartella "+result[1]+"?")===true){
            location.href = "imp_mercurio_foreign.php<?php echo $str_GET_Parameter;?>&folderToArchive="+result[1];
            return true;
        }
        return false;
    });


});

</script>