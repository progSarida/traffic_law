<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');
require_once(PGFN."/fn_imp_publimail.php");

/** @var $rs CLS_DB */

//VIENE UTILIZZATA LA CARTELLA DA_IMPORTARE SENZA ENTE
if (!is_dir(IMPORT_FOLDER_PUBLIMAIL)) {
    mkdir(IMPORT_FOLDER_PUBLIMAIL);
    chmod(IMPORT_FOLDER_PUBLIMAIL, 0770);
}
chmod(IMPORT_FOLDER_PUBLIMAIL, 0770);
if (!is_dir(IMPORT_FOLDER_PUBLIMAIL . '/DA_ESTRARRE')) {
    mkdir(IMPORT_FOLDER_PUBLIMAIL . '/DA_ESTRARRE');
    chmod(IMPORT_FOLDER_PUBLIMAIL . '/DA_ESTRARRE', 0770);
    
}
if (!is_dir(IMPORT_FOLDER_PUBLIMAIL . '/ESTRATTI')) {
    mkdir(IMPORT_FOLDER_PUBLIMAIL . '/ESTRATTI');
    chmod(IMPORT_FOLDER_PUBLIMAIL . '/ESTRATTI', 0750);
}
if (!is_dir(IMPORT_FOLDER_PUBLIMAIL . '/ARCHIVIATI')) {
    mkdir(IMPORT_FOLDER_PUBLIMAIL . '/ARCHIVIATI');
    chmod(IMPORT_FOLDER_PUBLIMAIL . '/ARCHIVIATI', 0750);
}

//***DA RIMUOVERE PRE RILASCIO IN PROD***
chmod(IMPORT_FOLDER_PUBLIMAIL . '/DA_ESTRARRE', 0770);
chmod(IMPORT_FOLDER_PUBLIMAIL . '/ESTRATTI', 0770);
chmod(IMPORT_FOLDER_PUBLIMAIL . '/ARCHIVIATI', 0770);
chgrp(IMPORT_FOLDER_PUBLIMAIL . '/DA_ESTRARRE', "sviluppo");
chgrp(IMPORT_FOLDER_PUBLIMAIL . '/ESTRATTI', "sviluppo");
chgrp(IMPORT_FOLDER_PUBLIMAIL . '/ARCHIVIATI', "sviluppo");
//******

$ImportFile = CheckValue('ImportFile','s');

$CityId = $_SESSION['cityid'];
$filesPath = IMPORT_FOLDER_PUBLIMAIL;
//DA TOGLIERE PERCHE' SU MAGGIOLI SERVE PER VEDERE L'IMMAGINE. SUPERFLUO IN QUESTO CASO
//$filesPathHTML = IMPORT_FOLDER_MAGGIOLI_HTML."/".$CityId;

//$a_ConciliaVehicleTypeMapping = unserialize(IMP_MAGGIOLI_CONCILIA_VEHICLETYPE);

$str_Error = '';
$a_zipFiles = array();
$a_Folders = array();
$a_DataLines = array();
$a_Errors = array();
$a_Warnings = array();
$a_FoundFines = array();

//Apertura cartella per la ricerca di zip
if ($directory_handle = @opendir("$filesPath/DA_ESTRARRE/")) {
    while (($file = readdir($directory_handle)) !== false) {
        if(pathinfo($file,PATHINFO_EXTENSION)){
            $a_zipFiles[] = $file;
        }
    }
    closedir($directory_handle);
} else $str_Error .= "Impossibile aprire la cartella delle importazioni da estrarre.<br>";

//Apertura cartella per la lettura degli estratti
if ($directory_handle = @opendir("$filesPath/ESTRATTI/")) {
    while (($file = readdir($directory_handle)) !== false) {
        if(is_dir("$filesPath/ESTRATTI/$file") && !in_array($file, array('.','..'))){
            $a_Folders[] = $file;
        }
    }
    closedir($directory_handle);
} else $str_Error .= "Impossibile aprire la cartella delle importazioni estratte.<br>";
//Lettura file estratti
if($ImportFile != ''){
    $importPath = "$filesPath/ESTRATTI/$ImportFile";    //Cartella "ESTRATTI"
    //$importPathHTML = "$filesPathHTML/ESTRATTI/$ImportFile";
    trigger_error("UNO");
    if(is_dir($importPath)){
        if(!empty($csvFiles = glob("$importPath/*.csv"))){  //Vedo tutti i csv presenti al suo interno
            $csvFile = $csvFiles[0];    //Prendo solo il primo
            //csvFile in questa posizione assume il nome di tutto il percorso del file, compreso il nome+estensione del file stesso
            $fileStream = @fopen($csvFile,  "r");   //Apro e leggo il file
            trigger_error("DUE ");
            if(is_resource($fileStream)){
                trigger_error("TRE");
                $a_CSVFirstLine = fgetcsv($fileStream, 0, IMP_PUBLIMAIL_CSV_SEPARATOR); //Recupera la prima riga con le descrizioni delle colonne
                $a_CSVColumnsDiffs = compareColumns($a_CSVColumns, $a_CSVFirstLine); //Confronta le colonne previste con la prima riga del file
//                 $counterFL = 1;
//                 $counterM = 1;
//                 $counterE = 1;
//                 foreach($a_CSVFirstLine as $fl){
//                     trigger_error("PRIMA LINEA $counterFL: ".$fl);
//                     $counterFL++;
//                 }
//                 foreach($a_CSVColumnsDiffs['missing'] as $m){
//                     trigger_error("MISSING $counterM: ".$m);
//                     $counterM++;
//                 }
//                 foreach($a_CSVColumnsDiffs['extra'] as $e){
//                     trigger_error("EXTRA $counterE: ".$e);
//                     $counterE++;
//                 }
                if(empty($a_CSVColumnsDiffs)){
                    if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                        
                        $a_IndexedLines = buildLinesArray($a_CSVColumns, IMP_PUBLIMAIL_CSV_SEPARATOR, $fileStream);
                        trigger_error("QUATTRO");
                        $ln = 1;
                        foreach($a_IndexedLines as $a_CSVLine){
                            trigger_error("CINQUE");
                            $MailNumber = '';
                            $ReceiptNumber = '';
                            $CityIdCode = '';
                            $ProtocolNumber = 0;
                            $ProtocolYear = 0;
                            $ResultId = 0;
                            $NotificationDate  = '';    //Vale anche per il mancato recapito
                            $SendDate = '';
                            $LogDate = '';
                            $Box = '';
                            $Lot = '';
                            $Position = '';
                            $UniqueImage = '';
                            $ImageFront = '';
                            $ImageBack = '';
                            
                            $n_LineCount++;
                            
                            //Numero raccomandata
                            if($a_CSVLine['numero_raccomandata'] != ''){
                                $MailNumber = $a_CSVLine['numero_raccomandata'];
                                $a_DataLines[$ln]['numero_raccomandata']['error'] = 'S';
                                $a_DataLines[$ln]['numero_raccomandata']['value'] = $MailNumber;
                            } else {
                                $a_DataLines[$ln]['numero_raccomandata']['error'] = 'D';
                                $a_Errors[$ln][] = 'Numero raccomandata assente.';
                            }
                            
                            //Numero ricevuta ritorno
                            if($a_CSVLine['numero_ricevuta_ritorno'] != ''){
                                $ReceiptNumber = $a_CSVLine['numero_ricevuta_ritorno'];
                                $a_DataLines[$ln]['numero_ricevuta_ritorno']['error'] = 'S';
                                $a_DataLines[$ln]['numero_ricevuta_ritorno']['value'] = $ReceiptNumber;
                            } else {
                                $a_DataLines[$ln]['numero_ricevuta_ritorno']['error'] = 'D';
                                $a_Errors[$ln][] = 'Numero ricevuta di ritorno assente.';
                            }
                            
                            //Ente
                            if($a_CSVLine['cc'] != ''){
                                $CityIdCode = $a_CSVLine['cc'];
                                $a_DataLines[$ln]['cc']['error'] = 'S';
                                $a_DataLines[$ln]['cc']['value'] = $CityIdCode;
                            } else {
                                $a_DataLines[$ln]['cc']['error'] = 'D';
                                $a_Errors[$ln][] = 'Codice ente assente.';
                            }
                            
                            //Cronologico
                            if($a_CSVLine['cronologico'] != ''){
                                $ProtocolNumber = $a_CSVLine['cronologico'];
                                $a_DataLines[$ln]['cronologico']['error'] = 'S';
                                $a_DataLines[$ln]['cronologico']['value'] = $ProtocolNumber;
                            } else {
                                $a_DataLines[$ln]['cronologico']['error'] = 'D';
                                $a_Errors[$ln][] = 'Cronologico assente.';
                            }
                            
                            //Anno
                            if($a_CSVLine['anno'] != ''){
                                $ProtocolYear = $a_CSVLine['anno'];
                                $a_DataLines[$ln]['anno']['error'] = 'S';
                                $a_DataLines[$ln]['anno']['value'] = $ProtocolYear;
                            } else {
                                $a_DataLines[$ln]['anno']['error'] = 'D';
                                $a_Errors[$ln][] = 'Anno assente.';
                            }
                            
                            //Esito notifica
                            if($a_CSVLine['cod_mancato_recapito'] != ''){
                                $ResultId = translateResultId($a_CSVLine['cod_mancato_recapito']);
                                $a_DataLines[$ln]['cod_mancato_recapito']['error'] = 'S';
                                $a_DataLines[$ln]['cod_mancato_recapito']['value'] = $ResultId;
                                //Data mancato recapito
                                if($a_CSVLine['data_mancato_recapito'] != ''){
                                    $NotificationDate = $a_CSVLine['data_mancato_recapito'];
                                    $a_DataLines[$ln]['data_mancato_recapito']['error'] = 'S';
                                    $a_DataLines[$ln]['data_mancato_recapito']['value'] = $NotificationDate;
                                } else {
                                    $a_DataLines[$ln]['data_mancato_recapito']['error'] = 'D';
                                    $a_Errors[$ln][] = 'Data mancato recapito assente.';
                                }
                            } elseif($a_CSVLine['CodNotifica'] != ''){
                                $ResultId = translateResultId($a_CSVLine['cod_notifica']);
                                $a_DataLines[$ln]['cod_notifica']['error'] = 'S';
                                $a_DataLines[$ln]['cod_notifica']['value'] = $ResultId;
                                //Data notifica
                                if($a_CSVLine['data_notifica'] != ''){
                                    $NotificationDate = $a_CSVLine['data_notifica'];
                                    $a_DataLines[$ln]['data_notifica']['error'] = 'S';
                                    $a_DataLines[$ln]['data_notifica']['value'] = $NotificationDate;
                                } else {
                                    $a_DataLines[$ln]['data_notifica']['error'] = 'D';
                                    $a_Errors[$ln][] = 'Data di notifica assente.';
                                }
                            } else {
                                $a_DataLines[$ln]['cod_notifica']['error'] = 'D';
                                $a_Errors[$ln][] = 'Codice recapito o mancato recapito assente.';
                            }
                            
                            
                            
                            //Data spedizione
                            if($a_CSVLine['data_spedizione'] != ''){
                                $SendDate = $a_CSVLine['data_spedizione'];
                                $a_DataLines[$ln]['data_spedizione']['error'] = 'S';
                                $a_DataLines[$ln]['data_spedizione']['value'] = $SendDate;
                            } else {
                                $a_DataLines[$ln]['data_spedizione']['error'] = 'D';
                                $a_Errors[$ln][] = 'Data di spedizione assente.';
                            }
                            
                            //Data LOG
                            if($a_CSVLine['data_log'] != ''){
                                $LogDate = $a_CSVLine['data_log'];
                                $a_DataLines[$ln]['data_log']['error'] = 'S';
                                $a_DataLines[$ln]['data_log']['value'] = $LogDate;
                            } else {
                                $a_DataLines[$ln]['data_log']['error'] = 'D';
                                $a_Errors[$ln][] = 'Data di LOG assente.';
                            }
                            
                            //Scatola
                            if($a_CSVLine['scatola'] != ''){
                                $Box = $a_CSVLine['scatola'];
                                $a_DataLines[$ln]['scatola']['error'] = 'S';
                                $a_DataLines[$ln]['scatola']['value'] = $Box;
                            } else {
                                $a_DataLines[$ln]['scatola']['error'] = 'D';
                                $a_Errors[$ln][] = 'Scatola assente.';
                            }
                            
                            //Lotto
                            if($a_CSVLine['lotto'] != ''){
                                $Lot = $a_CSVLine['lotto'];
                                $a_DataLines[$ln]['lotto']['error'] = 'S';
                                $a_DataLines[$ln]['lotto']['value'] = $Lot;
                            } else {
                                $a_DataLines[$ln]['lotto']['error'] = 'D';
                                $a_Errors[$ln][] = 'Lotto assente.';
                            }
                            
                            //Posizione
                            if($a_CSVLine['posizione'] != ''){
                                $Position = $a_CSVLine['posizione'];
                                $a_DataLines[$ln]['posizione']['error'] = 'S';
                                $a_DataLines[$ln]['posizione']['value'] = $Position;
                            } else {
                                $a_DataLines[$ln]['posizione']['error'] = 'D';
                                $a_Errors[$ln][] = 'Posizione assente.';
                            }
                            
                            //Immagine unica
                            if($a_CSVLine['img_unica'] != ''){
                                $UniqueImage = $a_CSVLine['img_unica'];
                                $a_DataLines[$ln]['img_unica']['error'] = 'S';
                                $a_DataLines[$ln]['img_unica']['value'] = $UniqueImage;
                            } else {
                                //Immagine fronte
                                if($a_CSVLine['img_fronte'] != ''){
                                    $ImageFront = $a_CSVLine['img_fronte'];
                                    $a_DataLines[$ln]['img_fronte']['error'] = 'S';
                                    $a_DataLines[$ln]['img_fronte']['value'] = $ImageFront;
                                } else {
                                    $a_DataLines[$ln]['img_fronte']['error'] = 'D';
                                    $a_Errors[$ln][] = 'Immagine fronte assente.';
                                }
                                
                                //Immagine retro
                                if($a_CSVLine['img_retro'] != ''){
                                    $ImageBack = $a_CSVLine['img_retro'];
                                    $a_DataLines[$ln]['img_retro']['error'] = 'S';
                                    $a_DataLines[$ln]['img_retro']['value'] = $ImageBack;
                                } else {
                                    $a_DataLines[$ln]['img_retro']['error'] = 'D';
                                    $a_Errors[$ln][] = 'Immagine retro assente.';
                                }
                                $a_DataLines[$ln]['img_unica']['error'] = 'D';
                                $a_Errors[$ln][] = 'Immagine unica assente.';
                            }
                            
                            //ERRORI NELLA RIGA
                            $a_DataLines[$ln]['Riga']['error'] = empty($a_Errors[$ln]) ? (empty($a_Warnings[$ln]) ? 'S' : 'W') : 'D';
                    
                            //************DA VALUTARE************
                            //SI POTREBBE USARE IL CRON ASSOCIATO AL CITYID PER CONTROLLARE SE ESISTONO
                            //CONTROLLO VERBALI GIà ESISTENTI
                            if(isset($Code)){
                                $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND Code='$Code'");
                                if(mysqli_num_rows($rs_Fine) > 0){
                                    $a_FoundFines[] = $ln;
                                    unset($a_Errors[$ln]);
                                    unset($a_Warnings[$ln]);
                                }
                            }
                            //***********************************
                        $ln++;
                        }
                        fclose($fileStream);
                    }
                }
            } else $str_Error .= "Impossibile leggere il file .csv: $csvFile";
        } else $str_Error .= "Nessun file .csv trovato all'interno della cartella: $ImportFile";
    } else $str_Error .= "La cartella non esiste: $ImportFile";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_publimail" action="imp_publimail_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="ImportFile" value="<?= $ImportFile; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="table_caption_I col-sm-12 text-center">IMPORTAZIONE NOTIFICHE PUBLIMAIL</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="table_label_H col-sm-6">CARTELLE IMPORTAZIONI ESTRATTE</div>
		
		<div class="table_label_H col-sm-6">ARCHIVI DA ESTRARRE</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowCaption col-sm-6" style="min-height:20rem;padding:0;">
			<?php if(!empty($a_Folders)): ?>
				<?php foreach($a_Folders as $folderName): ?>
					<div class="tableRow">
						<div class="table_caption_H col-sm-10<?= $folderName == $ImportFile ? ' alert-info' : ''; ?>"><?= $folderName; ?></div>
    					<div class="table_caption_H col-sm-2" style="padding:0;">
    						<?= ChkButton($aUserButton, 'imp','<button type="button" data-toggle="tooltip" data-container="body" data-placement="top" title="Apri" name="OpenFolder" onclick="window.location=\''.impostaParametriUrl(array('ImportFile' => $folderName)).'\'" class="btn btn-warning pull-left tooltip-r" style="height:100%;width:50%;padding:0;"><i class="fa fa-folder-open"></i></button>'); ?>
    						<?= ChkButton($aUserButton, 'imp','<button type="button" data-toggle="tooltip" data-container="body" data-placement="top" title="Elimina" name="DeleteFolder" value="'.$folderName.'" class="btn btn-danger tooltip-r" style="height:100%;width:50%;padding:0;"><i class="fa fa-times"></i></button>'); ?>
    					</div>
					</div>
					
					<div class="clean_row HSpace4"></div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="table_caption_H col-sm-12">Nessuna cartella da importare.</div>
				
				<div class="clean_row HSpace4"></div>
			<?php endif; ?>
		</div>
		<div class="BoxRowCaption col-sm-6" style="min-height:20rem;padding:0;">
			<?php if(!empty($a_zipFiles)): ?>
				<?php foreach($a_zipFiles as $zipName): ?>
					<div class="tableRow">
						<div class="table_caption_H col-sm-10"><?= $zipName; ?></div>
        				<div class="table_caption_H col-sm-2" style="padding:0;">
    						<?= ChkButton($aUserButton, 'imp','<button type="button" data-toggle="tooltip" data-container="body" data-placement="top" title="Estrai" name="Extract" value="'.$zipName.'" class="btn btn-success pull-left tooltip-r" style="height:100%;width:50%;padding:0;"><i class="fas fa-box-open"></i></button>'); ?>
    						<?= ChkButton($aUserButton, 'imp','<button type="button" data-toggle="tooltip" data-container="body" data-placement="top" title="Archivia" name="Archive" value="'.$zipName.'" class="btn btn-danger tooltip-r" style="height:100%;width:50%;padding:0;"><i class="fa fa-archive"></i></button>'); ?>
        				</div>
					</div>
					
					<div class="clean_row HSpace4"></div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="table_caption_H col-sm-12">Nessun archivio da estrarre.</div>
				
				<div class="clean_row HSpace4"></div>
			<?php endif; ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<?php if($ImportFile != ''): ?>
			<?php if($str_Error == ''): ?>
				<div class="table_caption_I col-sm-12 text-center"><?= $ImportFile; ?></div>
				
				<div class="clean_row HSpace4"></div>
				<div class="table_label_H col-sm-2" style="padding:0px;">
    				<div class="table_label_H col-sm-2">Riga</div>
    	            <div class="table_label_H col-sm-5">N° raccomandata</div>
    	            <div class="table_label_H col-sm-5">N° ric. ritorno</div>
	            </div>
	            <div class="table_label_H col-sm-1" style="padding:0px;">CC</div>
	            <div class="table_label_H col-sm-1" style="padding:0px;">Cron/Anno</div>
	            <div class="table_label_H col-sm-1" style="padding:0px;">Esito notifica</div>
	            <div class="table_label_H col-sm-1" style="padding:0px;">Data notifica</div>
	            <div class="table_label_H col-sm-1" style="padding:0px;">Data spedizione</div>
	            <div class="table_label_H col-sm-1" style="padding:0px;">Data log</div>
	            <div class="table_label_H col-sm-4" style="padding:0px;">
    	            <div class="table_label_H col-sm-2">
    	            Scatola
    	            </div>
    	            <div class="table_label_H col-sm-2">
    	            Lotto
    	            </div>
    	            <div class="table_label_H col-sm-2">
    	            Posizione
    	            </div>
            		<div class="table_label_H col-sm-6">Immagine</div>
	            </div>
	            <div class="clean_row HSpace4"></div>
				<?php foreach ($a_DataLines as $lineIndex => $line): ?>
					<div class="tableRow">
					<?php if(in_array($lineIndex, $a_FoundFines)): ?>
			            <div class="table_caption_H col-sm-1">
			            	1
						</div>
			            <div class="table_caption_H col-sm-1">
							Uno
						</div>
        	            <div class="table_caption_H col-sm-1">Due</div>
        	            <div class="table_caption_H col-sm-2">Tre</div>
        	            <div class="table_caption_H col-sm-1">Quattro</div>
        	            <div class="table_caption_H col-sm-1">Cinque</div>
        	            <div class="table_caption_H col-sm-1">Sei</div>
        	            <div class="table_caption_H col-sm-1">Sette</div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">
            	            <div class="table_caption_H col-sm-4">
            	            Otto
            	            </div>
            	            <div class="table_caption_H col-sm-4">
            	            Nove
            	            </div>
            	            <div class="table_caption_H col-sm-4">
            	            Dieci
            	            </div>
        	            </div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">Undici</div>
					<?php else: ?>
						<div class="table_caption_H col-sm-2" style="padding:0px;">
    			            <div class="table_caption_H col-sm-2">
    			            	<?php echo $lineIndex;?>
    						</div>
    			            <div class="table_caption_H col-sm-5">
    							<?= printIcon($line['numero_raccomandata']['error'] ?? null); ?> <?= $line['numero_raccomandata']['value'] ?? ''; ?>
    						</div>
            	            <div class="table_caption_H col-sm-5">
            	            	<?= printIcon($line['numero_ricevuta_ritorno']['error'] ?? null); ?> <?= $line['numero_ricevuta_ritorno']['value'] ?? ''; ?>
            	            </div>
        	            </div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">
        	            	<?= printIcon($line['cc']['error'] ?? null); ?> <?= $line['cc']['value'] ?? ''; ?>
        	            </div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">
        	            	<?= printIcon($line['cronologico']['error'] ?? null); ?> <?= $line['cronologico']['value'] ?? ''; ?>
        	            </div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">
        	            	<?= printIcon($line['cod_notifica']['error'] ?? null); ?> <?= $line['cod_notifica']['value'] ?? ''; ?>
        	            </div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">
        	            	<?= printIcon($line['data_notifica']['error'] ?? null); ?> <?= $line['data_notifica']['value'] ?? ''; ?>
        	            </div>
        	            <div class="table_caption_H col-sm-1" style="padding:0px;">
							<?= printIcon($line['data_spedizione']['error'] ?? null); ?> <?= $line['data_spedizione']['value'] ?? ''; ?>
						</div>
						<div class="table_caption_H col-sm-1" style="padding:0px;">
							<?= printIcon($line['data_log']['error'] ?? null); ?> <?= $line['data_log']['value'] ?? ''; ?>
						</div>
        	            <div class="table_caption_H col-sm-4" style="padding:0px;" style="padding:0px;">
            	            <div class="table_caption_H col-sm-2">
            	            	<?= printIcon($line['scatola']['error'] ?? null); ?> <?= $line['scatola']['value'] ?? ''; ?>
            	            </div>
            	            <div class="table_caption_H col-sm-2">
            	            	<?= printIcon($line['lotto']['error'] ?? null); ?> <?= $line['lotto']['value'] ?? ''; ?>
            	            </div>
            	            <div class="table_caption_H col-sm-2">
            	            	<?= printIcon($line['posizione']['error'] ?? null); ?> <?= $line['posizione']['value'] ?? ''; ?>
            	            </div>
        	            
        	            <?php if($line['img_unica']['error'] != 'D'):?>
            	            <div class="table_caption_H col-sm-6" style="padding:0px;">
            	            	<?= printIcon($line['img_unica']['error'] ?? null); ?> <?= imageSubString("single",$line['img_unica']['value']) ?? ''; ?>
            	            </div>
            	        <?php elseif($line['img_fronte']['error'] != 'D' && $line['img_retro']['error'] != 'D'):?>
        	            	<div class="table_caption_H col-sm-3">
        	            		<?= printIcon($line['img_fronte']['error'] ?? null); ?> <?= imageSubString("multiple",$line['img_fronte']['value']) ?? ''; ?>
        	            	</div>
        	            	<div class="table_caption_H col-sm-3">
        	            		<?= printIcon($line['img_retro']['error'] ?? null); ?> <?= imageSubString("multiple",$line['img_retro']['value']) ?? ''; ?>
        	            	</div>
        	            <?php endif;?>
        	            </div>
					<?php endif; ?>
					</div>
                    
                    <div class="clean_row HSpace16"></div>
				<?php endforeach; ?>
				
				<?php if(!empty($a_Errors) || !empty($a_Warnings)): ?>
					<div class="clean_row HSpace48"></div>	
					
        			<div class="table_caption_I col-sm-12 text-center">PROBLEMI RISCONTRATI</div>
        			
        			<div class="clean_row HSpace4"></div>
        			
		            <div class="table_label_H col-sm-1">Riga</div>
	            	<div class="table_label_H col-sm-11">Avviso</div>	
        			
        			<div class="clean_row HSpace4"></div>
        			<?php foreach($a_Errors as $errorLine => $errors): ?>
        				<?php foreach($errors as $errorMessage): ?>
		                    <div class="table_caption_H col-sm-1 alert-danger"><?= $errorLine; ?></div>
                    		<div class="table_caption_H col-sm-11 alert-danger"><?= $errorMessage; ?></div>
                    		
                    		<div class="clean_row HSpace4"></div>
        				<?php endforeach; ?>
        			<?php endforeach; ?>
        			<?php foreach($a_Warnings as $warningLine => $warnings): ?>
        				<?php foreach($warnings as $warningMessage): ?>
		                    <div class="table_caption_H col-sm-1 alert-warning"><?= $warningLine; ?></div>
                    		<div class="table_caption_H col-sm-11 alert-warning"><?= $warningMessage; ?></div>
                    		
                    		<div class="clean_row HSpace4"></div>
        				<?php endforeach; ?>
        			<?php endforeach; ?>
				<?php endif; ?>
				
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	<div class="col-sm-3"></div>
        	    	<div class="col-sm-6">
	        	    	<?php if(empty($a_Errors) && count($a_FoundFines) < count($a_DataLines)): ?>
            	    		<?= ChkButton($aUserButton, 'imp','<button id="Import" name="Import" type="button" class="btn btn-success" style="width:10rem; margin-top:2rem;">Importa</button>'); ?>
            	    	<?php endif;?>
        	    	</div>
        	    	<div class="col-sm-3"></div>
                </div>
			<?php else: ?>
                <div class="text-center table_caption_error table_caption_I col-sm-12" style="height:auto;">ERRORE: <?= $str_Error; ?></div>
            
            	<div class="clean_row HSpace4"></div>	
			<?php endif; ?>
		<?php endif; ?>
	</form>
</div>
<script>
$(document).ready(function () {
    $(".tableRow").mouseover(function(){
        $( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error').css("background-color", "#cfeaf7c7");
    });
    $(".tableRow").mouseout(function(){
        $( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error').css("background-color", "");
    });
    
    $('[name="Archive"], [name="DeleteFolder"], [name="Import"], [name="Extract"], [name="OpenFolder"]').on('click', function(e){
        action = $(this).attr('name');
        value = $(this).val();
        
    	if(action == "Archive"){
    		if(!confirm("Si sta per archiviare il file selezionato. Continuare?")) return false;
    	} else if(action == "DeleteFolder"){
    		if(!confirm("Si sta per eliminare la cartella selezionata e il suo contenuto in modo definitivo. Continuare?")) return false;
    	} else if(action == "Import"){
    		if(!confirm("Si stanno per importare i dati visualizzati. Continuare?")) return false;
    	}
    	
    	$('[name="Archive"], [name="DeleteFolder"], [name="Import"], [name="Extract"], [name="OpenFolder"]').prop('disabled', true);
    	$(this).html(`
    		<i class="fas fa-circle-notch fa-spin"></i>
		`);
		
		if(action != 'OpenFolder'){
	        $input = $('<input>', {
                type:'hidden',
                name:action,
                value:value
            });
            $('#f_publimail').append($input).submit();
		}
    });
    
});
</script>
<?php
require_once (INC . "/footer.php");

