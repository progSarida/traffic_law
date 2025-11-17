<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS . "/cls_file.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
include(CLS."/cls_notification.php");
require_once(CLS . "/cls_message.php");

ini_set('display_errors',1);
ini_set('max_execution_time', 0);

$message=new CLS_MESSAGE();
$message->addWarning("<b>Avviso agli operatori</b> :<br>
La procedura corrente è stata aggiornata a maggio 2022 per integrare la gestione del flag \"Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta \" quando si importano notifiche per atti con violazione relativa ad assicurazione e revisioni.<br> Contattare l'assistenza per verifiche se l'ente tratta questo tipo di verbali.");
echo $message->getMessagesString();

$PrinterId = CheckValue('PrinterId','s');
if($PrinterId=="")
    $PrinterId = 4;
$str_CurrentPage.="&PrinterId=".$PrinterId;

$a_printer = $rs->getArrayLine($rs->ExecuteQuery("SELECT * FROM Printer WHERE Id=".$PrinterId));
$cls_not = new cls_notification($PrinterId, null);

$ImportFile = CheckValue('ImportFile','s');
$DeleteImportFile = CheckValue('DeleteImportFile','s');

$folderToDownload = CheckValue('folderToDownload', 's');
$folderToArchive = CheckValue('folderToArchive', 's');

$extractArchives = CheckValue('extractArchives', 's');
$deleteArchives = CheckValue('deleteArchives', 's');

$cls_not->removeImportedFile($DeleteImportFile);

if($folderToDownload!="")
    $cls_not->downloadNotificationsZip($folderToDownload);
else if($folderToArchive!="")
    $cls_not->archivePrinterFiles($folderToArchive);

$a_ftpFolders = $cls_not->getNotificationsFolder();

$downloadedFolder = $cls_not->getDownloadedFolder();
$a_downloadedFiles = $cls_not->getDownloadedFiles($downloadedFolder, $deleteArchives);
if($extractArchives)
    $cls_not->extractAndImport($downloadedFolder, $a_downloadedFiles);

$a_csv = $cls_not->getCsv();

if(count($a_csv)>0){
    $FileList = "";
    foreach ($a_csv as $key=>$value){
        $FileList .= '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . ($key+1) . '</div>
            <div class="table_caption_H col-sm-10">' . $value . '</div>
            <div class="table_caption_button col-sm-1">
                ' . ChkButton($aUserButton, 'imp', '<a href="' . $str_CurrentPage . '&ImportFile=' . $value . '"><span class="fa fa-download"></span></a>') . '
                &nbsp;'.ChkButton($aUserButton, 'imp', '<a href="' . $str_CurrentPage . '&DeleteImportFile=' . $value . '"><span class="fa fa-trash"></span></a>').'
            </div>
            <div class="clean_row HSpace4"></div>
		</div>    
	';
    }
}
else{
    $FileList = '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-11">Nessun file presente</div>
            <div class="table_caption_button col-sm-1"></div>
			<div class="clean_row HSpace4"></div>
		</div>    
	';
}

if(count($a_csv)>0 || count($a_downloadedFiles)==0){
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

$str_download = '
    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>CARTELLA NOTIFICHE SCARICATE '.$downloadedFolder.'</b></div>
    <div class="clean_row HSpace4"></div>
    <div class="table_caption_H col-sm-12" style="height:35rem;overflow:auto">
';
foreach($a_downloadedFiles as $donwloadedFile){
    $str_download.='<div class="table_caption_H col-sm-12">'.$donwloadedFile.'</div>';
}
$str_download.= '
    </div>
    <div class="table_label_H col-sm-12">
        <button id="extract_btn" style="font-size:12px; background-color: ' .$backgroundcolor.'; width: 15rem; height:95%;" '.$disabled. '><b>ESTRAI</b></button>
        <button id="delete_btn" style="font-size:12px;background-color: '.$backgroundcolor2.'; width: 15rem; height:95%;" ' .$disabled. '><b>ELIMINA</b></button>
        <button id="delete_archive_btn" data-id="'.$downloadedFolder.'" style="font-size:12px;background-color: '.$backgroundcolor3.'; width: 15rem; height:95%;" ' .$disabled.'><b>ELIMINA E ARCHIVIA</b></button>
    </div>
';

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

$str_ftp = '
    <div class="table_label_H col-sm-12" style="color: whitesmoke"><b>FTP '.$a_printer['Name'].'</b></div>
	<div class="clean_row HSpace4"></div>
	<div class="table_caption_H col-sm-12" style="height:35rem;overflow:auto">
';

foreach($a_ftpFolders as $ftpFolder){
    $str_ftp.='
        <div class="table_caption_H col-sm-6">'.$ftpFolder.'</div>
        <div class="table_caption_H col-sm-6">
            <button id="download-'.$ftpFolder.'" class="ftp_download_btn" style="background-color: '.$backgroundcolor.'; width: 8rem; height:95%;" '.$disabled.'>SCARICA</button>
            <button id="store-'.$ftpFolder.'" class="ftp_store_btn" style="background-color: '.$backgroundcolor2.'; width: 8rem; height:95%;" '.$disabled.'>ARCHIVIA</button>
        </div>
';
}
$str_ftp.='
    </div>
    <div class="table_label_H col-sm-12"></div>
';

$error = false;
$imported = false;
$msgProblem = "";
$str_out = '
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="' . $_SESSION['blazon'] . '" style="width:50px;">
					<span class="title_city">' . $_SESSION['citytitle'] . ' ' . $_SESSION['year'] . '</span>
				</div>
			</div>
		</div>		
		<div class="row-fluid">
		    <div class="col-sm-6">
		    '.$str_download.'
		    </div>
		    <div class="col-sm-6">
		    '.$str_ftp.'
		    </div>
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
            	
            ' . $FileList;

echo $str_out;

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

    $queryResult = "SELECT PR.*, R.Title FROM Result R JOIN PrinterResult PR ON PR.ResultId = R.Id ";
    $queryResult.= "WHERE PR.PrinterId=".$PrinterId." AND R.Disabled=0";     ;
    $rs_Results = $rs->getResults($rs->ExecuteQuery($queryResult));
    if(count($rs_Results)==0){
        echo "LISTA NOTIFICHE STAMPATORE NON TROVATA";
        die;
    }
    $a_results = array();
    foreach ($rs_Results as $key=>$a_result){
        $a_results[$a_result['Code']] = $a_result;
    }

    $cont = 0;

    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Cron</div>
            <div class="table_label_H col-sm-1">Data not</div>
            <div class="table_label_H col-sm-1">Raccomandata</div>
            <div class="table_label_H col-sm-1">Ricevuta</div>
            <div class="table_label_H col-sm-2">Notifica / Mancato recapito</div>
            <div class="table_label_H col-sm-1">Data sped</div>
            <div class="table_label_H col-sm-1">Posizione</div>
            <div class="table_label_H col-sm-1">Data log</div>
            <div class="table_label_H col-sm-2">Controllo</div>
            <div class="clean_row HSpace4"></div>	
        </div>
    ';

$cls_file = new cls_file();
$a_csv = $cls_file->getArrayFromCsv($cls_not->a_path['toImport'].$ImportFile);
$a_header = array(
    "stampatore", "numero_raccomandata", "numero_ricevuta_ritorno", "cc",
    "nazione", "tipo_stampa", "id_applicativo", "id_tipo_documento", "id_documento",
    "cronologico", "anno", "destinatario", "indirizzo", "cap", "localita",
    "cod_mancato_recapito", "mancato_recapito", "data_mancato_recapito",
    "cod_notifica", "notifica", "data_notifica",
    "data_spedizione", "data_log", "scatola", "lotto", "posizione",
    "img_unica", "img_fronte", "img_retro"
);

//var_dump($a_csv);
foreach ($a_csv as $row) {
    if($cont==0){
        $a_missingHeader = $cls_file->checkFileHeaderInArray(array_keys($a_csv[0]), $a_header);
        if($a_missingHeader!=null){
            echo "INTESTAZIONE NON CONFORME AL MODELLO <br><br>Le seguenti colonne non sono state trovate: ";
            foreach ($a_missingHeader as $header)
                echo $header."<br>";
            die;
        }

    }

    $cont++;
    $chkFine = "";
    $chkControlColor = "";
    $chkSendDate = "";
    $chkDeliveryDate = "";
    $chkNotification = "";

    $notification = "";
//CHECK NOTIFICA
    if($row['cod_notifica']!=""){
        if(array_key_exists($row['cod_notifica'],$a_results)===false){
            $error = true;
            $chkMessage = 'Notifica non tabellata: ' . $row['cod_notifica'] . ' '.$row['notifica'];
            $chkControlColor = ' alert-danger ';
            $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
        }
        else{
            $notification = $a_results[$row['cod_notifica']]['Title']." ( ".$a_results[$row['cod_notifica']]['Code']." - ".$a_results[$row['cod_notifica']]['Description']." )";
            if(is_null(DateInDB($row['data_notifica']))){
                $error = true;
                $chkMessage = 'Data di notifica assente';
                $chkControlColor = ' alert-danger ';
                $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            }
        }
    }
    else if($row['cod_mancato_recapito']!=""){
        if(array_key_exists($row['cod_mancato_recapito'],$a_results)===false){
            $error = true;
            $chkMessage = 'Mancato recapito non tabellato: ' . $row['cod_mancato_recapito'] . ' '.$row['mancato_recapito'];
            $chkControlColor = ' alert-danger ';
            $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
        }
        else{
            $notification = $a_results[$row['cod_mancato_recapito']]['Title']." ( ".$a_results[$row['cod_mancato_recapito']]['Code']." - ".$a_results[$row['cod_mancato_recapito']]['Description']." )";
            if(is_null(DateInDB($row['data_mancato_recapito']))){
                $error = true;
                $chkMessage = 'Data mancato recapito assente';
                $chkControlColor = ' alert-danger ';
                $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            }
        }
    }
    else{
        $error = true;
        $chkMessage = 'Notifica e Mancato recapito assenti';
        $chkControlColor = ' alert-danger ';
        $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
    }

    if($row['id_documento']>0 && $row['cc']!=""){
        $queryFine = "SELECT F.Id, FN.* FROM Fine F LEFT JOIN FineNotification FN ON F.Id=FN.FineId ";
        $queryFine.= "WHERE F.Id=" . $row['id_documento'] . " AND F.CityId='" . $row['cc'] . "' ";
        if($row['cronologico']>0)
            $queryFine.= "AND F.ProtocolId=" . $row['cronologico'] ." ";
        if($row['anno']>0)
            $queryFine.= "AND F.ProtocolYear=" . $row['anno'] ." ";        ;
        $rs_Fine = $rs->getResults($rs->ExecuteQuery($queryFine));
    }

    //CHECK IMMAGINI CARTOLINE
    $imgIcon = 'glyphicon-ok-circle';
    $imgColor = 'green';
    $imgTitle = '';
    if($row['img_unica']!=""){
        if(!file_exists($cls_not->a_path['toImport']."/".$row['img_unica'])){
            $imgIcon = 'glyphicon-exclamation-sign';
            $imgColor = 'red';
            $imgTitle = 'Immagini assenti! File '.$row['img_unica']." non trovato";
        }
        else
            $imgTitle = $row['img_unica'];
    }
    else if($row['img_fronte']!="" || $row['img_retro']!=""){
        if(!file_exists($cls_not->a_path['toImport']."/".$row['img_fronte'])){
            $imgIcon = 'glyphicon-exclamation-sign';
            $imgColor = 'red';
            $imgTitle.= '- File '.$row['img_fronte']." [FRONTE] non trovato! ";
        }
        else
            $imgTitle.= $row['img_fronte']." ";

        if(!file_exists($cls_not->a_path['toImport']."/".$row['img_retro'])){
            $imgIcon = 'glyphicon-exclamation-sign';
            $imgColor = 'red';
            $imgTitle.= '- File '.$row['img_retro']." [RETRO] non trovato! ";
        }
        else
            $imgTitle.= $row['img_retro']." ";
    }
    else{
        $imgIcon = 'glyphicon-exclamation-sign';
        $imgColor = 'red';
        $imgTitle.= 'File immagini assenti nel file CSV';
    }
    $showImg = "<span class='glyphicon ".$imgIcon."' style='color: ".$imgColor.";' title='".$imgTitle."'></span>";

    $chkMessage = "";
    if ($row['id_documento']=="" || $row['cc']=="" || count($rs_Fine) == 0) {
        $sendDate = null;
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
            $sendDate = null;
            if($row['numero_raccomandata']==$r_Fine['LetterNumber'] && $row['numero_ricevuta_ritorno']==$r_Fine['ReceiptNumber']){
                $imported = true;
                $chkControlColor = ' alert-success ';
                $chkMessage = 'Notifica gia importata';

                break;
            }
            else{
                $r_FineHistory = $rs->getArrayLine($rs->ExecuteQuery("SELECT * FROM FineHistory WHERE FineId=" . $r_Fine['Id']. " AND NotificationTypeId=6"));

                if(isset($r_FineHistory['SendDate'])){//CONTROLLO SE DATA SPEDIZIONE COINCIDE CON DATA FLUSSO
                    if($r_FineHistory['SendDate']==DateInDB($row['data_spedizione'])){
                        if(!is_null($r_FineHistory['DeliveryDate'])){//CONTROLLO SE DATA DI CONSEGNA PRESENTE
                            $chkMessage = 'Data notifica già presente';
                            $chkControlColor = ' alert-warning ';
                            $chkDeliveryDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        }
                        break;
                    }
                    else{
                        if((int)substr($row['data_spedizione'],-4)<(int)$row['anno'])
                            $chkMessage = 'Data sped. errata - Sost. per importazione con '.DateOutDB($r_FineHistory['SendDate']);
                        else
                            $chkMessage = 'Data spedizione non concidente';

                        $chkControlColor = ' alert-warning ';
                        $chkSendDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $sendDate = "(".DateOutDB($r_FineHistory['SendDate']).")";
                    }
                }
                else {
                    if($i==count($rs_Fine)){//CONTROLLO ATTIVO SOLO SU ULTIMO VERBALE
                        $chkMessage = 'Data spedizione non concidente';
                        $chkControlColor = ' alert-warning ';
                        $chkSendDate = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                        if(isset($r_FineHistory['DeliveryDate']) && !is_null($r_FineHistory['DeliveryDate'])){//CONTROLLO SE DATA DI CONSEGNA PRESENTE
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

    $str_out .='
    <div class="col-sm-12"> 
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $row['id_documento'].' '.$row['cc'].' '. $showImg . ' ' .$cont .'</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $row['cronologico'] . '/' . $row['anno'].'</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $chkDeliveryDate .' '. $row['data_notifica'].$row['data_mancato_recapito'] .'</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $row['numero_raccomandata'] . '</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $row['numero_ricevuta_ritorno'] . '</div>
        <div class="table_caption_H col-sm-2'.$chkFine.'">'. $chkNotification .$notification.'</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $chkSendDate .' '.$row['data_spedizione'] . ' '.$sendDate.'</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $row['scatola']. ' - '.$row['lotto'] .' - '.$row['posizione'].'</div>
        <div class="table_caption_H col-sm-1'.$chkFine.'">'. $row['data_log'].'</div>
        <div class="table_caption_H col-sm-2'.$chkControlColor.'">'. $chkMessage.'</div>
        <div class="clean_row HSpace4"></div>
    </div>    
    ';
}
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
            <form name="f_import" action="imp_notifiche.php">
            <input type="hidden" name="PrinterId" value="'.$PrinterId.'">
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
            <form name="f_import" action="imp_notifiche_exe.php">
            <input type="hidden" name="P" value="imp_notifiche.php">
            <input type="hidden" name="PrinterId" value="'.$PrinterId.'">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
            <div class="table_label_H col-sm-12">
                <input type="submit" value="Importa" >                           
            </div >
		</div ><br><br>';
    }
}

echo $str_out;

?>

<script>
    var folderToDownload = "<?php if(isset($a_ftpFolders[0])) echo $a_ftpFolders[0]; ?>";
    // When the document is ready
    $(document).ready(function () {

        $("#extract_btn").click(function(){
            alert("EXTRACT");
            location.href = "imp_notifiche.php<?php echo $str_GET_Parameter;?>&extractArchives=1&PrinterId=<?=$PrinterId?>";
        });

        $("#delete_btn").click(function(){
            alert("DELETE");
            location.href = "imp_notifiche.php<?php echo $str_GET_Parameter;?>&deleteArchives=1&PrinterId=<?=$PrinterId?>";
        });

        $("#delete_archive_btn").click(function(){
            let folderDelArch = $('#delete_archive_btn').attr('data-id');
            alert("DELETE AND ARCHIVE "+folderDelArch);
            if(confirm("Vuoi eliminare e archiviare la cartella "+folderDelArch+"?")===true){
                location.href = "imp_notifiche.php<?php echo $str_GET_Parameter;?>&deleteArchives=1&folderToArchive="+folderDelArch+"&PrinterId=<?=$PrinterId?>";
                return true;
            }

        });

        $(".ftp_download_btn").click(function(){
            var download_id = $(this).attr("id");
            var result = download_id.split('-');
            if(confirm("Vuoi scaricare la cartella "+result[1]+"?")===true){
                location.href = "imp_notifiche.php<?php echo $str_GET_Parameter;?>&folderToDownload="+result[1]+"&PrinterId=<?=$PrinterId?>";
                return true;
            }
            return false;
        });

        $(".ftp_store_btn").click(function(){
            var download_id = $(this).attr("id");
            var result = download_id.split('-');
            if(confirm("Vuoi archiviare la cartella "+result[1]+"?")===true){
                location.href = "imp_notifiche.php<?php echo $str_GET_Parameter;?>&folderToArchive="+result[1]+"&PrinterId=<?=$PrinterId?>";
                return true;
            }
            return false;
        });
    });

</script>
