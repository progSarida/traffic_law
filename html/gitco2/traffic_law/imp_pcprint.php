<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_pcprint.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

/** @var CLS_DB $rs */
//TODO viene fatto questo perchè anche se initialization.php lo fa già, menu_top.php reinizializza un'altra istanza $rs
//che quindi non avrà il charset impostato. Da rimuovere una volta che viene rimosso $rs da menut_top oppure se verrà già impostato nel costruttore di cls_db
$rs->SetCharset('utf8');

$ImportFile = CheckValue('ImportFile','s');
$CompressImages = CheckValue("CompressImages", "s") != "" ? CheckValue("CompressImages", "s") : 1;

$CityId = $_SESSION['cityid'];

$path = PUBLIC_FOLDER."/_VIOLATION_/$CityId";
$str_DocumentIcon = '<i class="fas fa-picture-o tooltip-r %s" data-container="body" data-toggle="tooltip" data-placement="right" title="%s" style="font-size:1.8rem;margin-top:0.2rem"></i>';

$str_Error = '';
$n_FileCount = 0;
$n_LineCount = 0;
$a_FileLinks = array();
$a_DataLines = array();
$a_Errors = array();
$a_Warnings = array();
$a_IndexedLines = array();
$a_FoundFines = array();
$a_FileColumns = array();

$b_IsSpeed = $b_IsTraffic = false;

if ($directory_handle = opendir($path)) {
    while (($file = readdir($directory_handle)) !== false) {
        if(isset(pathinfo($file)['extension']) && strtolower(pathinfo($file)['extension']) == 'log'){
            $a_FileLinks[$file] = ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>');
        }
    }
    closedir($directory_handle);
}

if($ImportFile != ''){
    $fileStream = @fopen("$path/$ImportFile",  "r");
    if(is_resource($fileStream)){
        
        for($i=0; $i<=IMP_PCPRINT_COLUMNSROW_INDEX; $i++){
            $buffer = fgets($fileStream, 4096);
            if ($i == 2) {
                $a_FileColumns = str_getcsv(trim($buffer), ' ', '"');
                $b_IsSpeed = $a_FileColumns == unserialize(IMP_PCPRINT_SPEED_COLUMNS);
                $b_IsTraffic = $a_FileColumns == unserialize(IMP_PCPRINT_TRAFFICLIGHT_COLUMNS);
            }
        }

        if(!empty($a_FileColumns)){
            if($b_IsSpeed || $b_IsTraffic){
                $a_IndexedLines = impPcprintBuildLinesArray($a_FileColumns, ' ', '"', $fileStream, $str_Error);
                    
                if(empty($str_Error)){
                    
                    $rs_Controllers = $rs->Select('Controller', "CityId='$CityId'");
                    $a_Controllers = controllersByFieldArray($rs_Controllers, 'Name');
                    
                    $TliFirst = $TliSecond = 0;
                    $FirstTrafficImage = $SecondTrafficImage = null;
                    $b_MissingImage = false;
                    
                    for($i = 1; $i <= count($a_IndexedLines); $i++){
                        $ProtocolYear = null;
                        $SpeedExcess=0;
                        $Tolerance=0;
                        $Locality = '';
                        $Address = '';
                        $SpeedExcess = 0;
                       
                        
                        $a_CSVLine = $a_IndexedLines[$i-1];
                        if(($i % 2 != 0 && $b_IsTraffic) || $b_IsSpeed){
                            $Code = null;
                            $n_LineCount++;
                            
                            //DATA E ORA VIOLAZIONE
                            if(($dt_FineDate = validateDateFormat($a_CSVLine['DATA'], 'd/m/Y')) &&
                            ($dt_FineTime = validateDateFormat($a_CSVLine['ORA'], 'H:i:s'))){
                                $FineDate = $dt_FineDate->format('d/m/Y');
                                $FineHour = $dt_FineTime->format('G');
                                $FineMinute = $dt_FineTime->format('i');
                                $ProtocolYear = $dt_FineDate->format('Y');
                                $a_DataLines[$n_LineCount]['Data']['error'] = 'S';
                                $a_DataLines[$n_LineCount]['Data']['value'] = $FineDate.' '.$dt_FineTime->format('H:i:s');
                            } else {
                                $a_DataLines[$n_LineCount]['Data']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Data/ora violazione assente.';
                            }
                            
                            //TARGA
                            if(preg_match( '/^[a-zA-Z0-9]+$/', $a_CSVLine['TARGA']) < 1){
                                $a_DataLines[$n_LineCount]['Targa']['error'] = 'W';
                                $a_Warnings[$n_LineCount][] = 'Targa assente o non valida.';
                            } else {
                                $a_DataLines[$n_LineCount]['Targa']['error'] = 'S';
                            }
                            $a_DataLines[$n_LineCount]['Targa']['value'] = $a_CSVLine['TARGA'];
                            
                            //RILEVATORE
                            if($a_CSVLine['MATRICOLA_FT1D'] != ''){
                                $r_Detector = $rs->getArrayLine($rs->Select('Detector', "CityId='$CityId' AND Code='{$a_CSVLine['MATRICOLA_FT1D']}'"));
                                
                                if(!empty($r_Detector)){
                                    $DetectorId = $r_Detector['Id'];
                                    $Tolerance = $r_Detector['Tolerance'];
                                    
                                    $a_DataLines[$n_LineCount]['Rilevatore']['error'] = 'S';
                                    $a_DataLines[$n_LineCount]['Rilevatore']['value'] = $r_Detector['TitleIta'];
                                } else {
                                    $a_DataLines[$n_LineCount]['Rilevatore']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = "Rilevatore non trovato per codice: {$a_CSVLine['MATRICOLA_FT1D']}";
                                }
                            } else {
                                $a_DataLines[$n_LineCount]['Rilevatore']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = "Codice rilevatore assente.";
                            }
                            
                            //LOCALITA' E INDIRIZZO
                            if($a_CSVLine["LOCALITA'_INFO"] != ''){
                                if (strpos($a_CSVLine["LOCALITA'_INFO"], ';') === false) {
                                    //es: "TORRIGLIA (GE) KM 32+700 SS 45 Direzione Piacenza"
                                    $a_Address = explode(')', $a_CSVLine["LOCALITA'_INFO"]);
                                    $str_Locality = trim(strtok($a_Address[0], '('));
                                    $Address = trim($a_Address[1]);
                                } else {
                                    //es: "Cogorno (GE) ; SP 33 KM 0,115 DIR Carasco ;   Apparecchiatura FTRD  Matr 4888 "
                                    $a_Address = explode(';', $a_CSVLine["LOCALITA'_INFO"]);
                                    $str_Locality = trim(strtok($a_Address[0], '('));
                                    $Address = trim($a_Address[1]);
                                }
                                    
                                $r_Locality = $rs->getArrayLine($rs->Select(MAIN_DB . '.City', "LOWER(Title)='".strtolower($str_Locality)."'"));
                                if(!empty($r_Locality)){
                                    $Locality = $r_Locality['Title']." ({$r_Locality['Id']})";
                                }
                                
                                if(!empty($Address)){
                                    $a_DataLines[$n_LineCount]['Indirizzo']['error'] = 'S';
                                    $a_DataLines[$n_LineCount]['Indirizzo']['value'] = $Address;
                                } else {
                                    $a_DataLines[$n_LineCount]['Indirizzo']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = 'Indirizzo assente.';
                                }
                                if(!empty($Locality)){
                                    $a_DataLines[$n_LineCount]['Localita']['error'] = 'S';
                                    $a_DataLines[$n_LineCount]['Localita']['value'] = $Locality;
                                } else {
                                    $a_DataLines[$n_LineCount]['Localita']['error'] = 'D';
                                    $a_DataLines[$n_LineCount]['Localita']['value'] = $str_Locality;
                                    $a_Errors[$n_LineCount][] = 'Località non trovata.';
                                }
                            } else {
                                $a_DataLines[$n_LineCount]['Localita']['error'] = 'D';
                                $a_DataLines[$n_LineCount]['Indirizzo']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Indirizzo e località assenti.';
                            }
                            
                            //ACCERTATORE
                            if($a_CSVLine["OPERATORE"] != ''){
                                $a_DataLines[$n_LineCount]['Accertatore']['value'] = $a_CSVLine["OPERATORE"];
                                
                                if (!(isset($FineDate) && !is_null(getControllerByField($a_Controllers, $FineDate, trim($a_CSVLine["OPERATORE"]))))) {
                                    $a_DataLines[$n_LineCount]['Accertatore']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = 'Accertatore non trovato.';
                                } else {
                                    $a_DataLines[$n_LineCount]['Accertatore']['error'] = 'S';
                                }
                            } else {
                                $a_DataLines[$n_LineCount]['Accertatore']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Accertatore assente.';
                            }
                            
                            
                            if($b_IsSpeed){
                                //LIMITE
                                if($a_CSVLine["LIMITE"] != '' && $a_CSVLine["VELOCITA'"] != ''){
                                    $Tolerance = ($Tolerance>FINE_TOLERANCE) ? $Tolerance : FINE_TOLERANCE;
                                    $SpeedLimit = (int) $a_CSVLine["LIMITE"];
                                    $SpeedControl = (int) $a_CSVLine["VELOCITA'"];
                                    
                                    $a_DataLines[$n_LineCount]['VelocitaTempo']['value'] = "$SpeedControl / $SpeedLimit";
                                    
                                    $SpeedExcess=getSpeedExcess($SpeedControl, $SpeedLimit, $Tolerance);
                                    if($SpeedExcess <= 0){
                                        $a_DataLines[$n_LineCount]['VelocitaTempo']['error'] = 'W';
                                        $a_Warnings[$n_LineCount][] = 'Velocità '. $SpeedControl . ' inferiore al limite '. $SpeedLimit .' - immagine non verrà importata.';
                                    } else {
                                        $a_DataLines[$n_LineCount]['VelocitaTempo']['error'] = 'S';
                                    }
                                } else {
                                    $a_DataLines[$n_LineCount]['VelocitaTempo']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = 'Limite o velocità assenti.';
                                }
                                
                                //IMMAGINE
                                if(!empty($a_CSVLine['NOME_FILE'])){
                                    if(file_exists("$path/{$a_CSVLine['NOME_FILE']}")) {
                                        $a_DataLines[$n_LineCount]['Documenti']['error'] = 'S';
                                        $a_DataLines[$n_LineCount]['Documenti']['value'] = sprintf($str_DocumentIcon, '', $a_CSVLine['NOME_FILE']);
                                    } else {
                                        $a_DataLines[$n_LineCount]['Documenti']['error'] = 'D';
                                        $a_DataLines[$n_LineCount]['Documenti']['value'] = sprintf($str_DocumentIcon, 'opaque', 'Immagine non trovata');
                                    }
                                }
                            }
                            
                            if($b_IsTraffic){
                                //TEMPO PRIMO FOTOGRAMMA
                                if($a_CSVLine["TEMPO_DAL_ROSSO"] != ''){
                                    $TliFirst = $a_CSVLine["TEMPO_DAL_ROSSO"];
                                }
                                
                                //PRIMO FOTOGRAMMA
                                if(!empty($a_CSVLine['NOME_FILE'])){
                                    if(file_exists("$path/{$a_CSVLine['NOME_FILE']}")) {
                                        $FirstTrafficImage = sprintf($str_DocumentIcon, '', $a_CSVLine['NOME_FILE']);
                                    } else {
                                        $FirstTrafficImage = sprintf($str_DocumentIcon, 'opaque', $a_CSVLine['NOME_FILE']);
                                        $b_MissingImage = true;
                                    }
                                }
                            }
                            
                            //CODICE
                            if(!empty($a_CSVLine['NOME_FILE']) && isset($ProtocolYear)){
                                $Code = impPcprintGetCode($a_CSVLine['NOME_FILE'], $ProtocolYear);
                            }
                            
                            //SANZIONE
                            if(isset($DetectorId) && isset($ProtocolYear) && isset($FineHour) && isset($FineMinute)){
                                $r_Article = array();
                                if($b_IsSpeed && $SpeedExcess){
                                    $r_Article = getVArticle($DetectorId, $CityId, $SpeedExcess, $ProtocolYear);
                                } else if($b_IsTraffic){
                                    $r_Article = getSArticle($DetectorId, $CityId, $ProtocolYear);
                                }
                                if(!empty($r_Article)){
                                    $Fee = $r_Article['Fee'];
                                    $MaxFee = $r_Article['MaxFee'];
                                    $AdditionalNight = $r_Article['AdditionalNight'];
                                    
                                    if($AdditionalNight==1){
                                        if($FineHour < FINE_HOUR_START_DAY || $FineHour > FINE_HOUR_END_DAY || ($FineHour == FINE_HOUR_END_DAY && $FineMinute != "00")){
                                            $Fee = $Fee + ceil(($Fee/FINE_NIGHT)*100)/100;
                                            $MaxFee = $MaxFee + ceil(($MaxFee/FINE_NIGHT)*100)/100;
                                        }
                                    }
                                    
                                    $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'S';
                                    $a_DataLines[$n_LineCount]['Sanzione']['value'] = "€ $Fee / $MaxFee";
                                } else {
                                    $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = 'Articolo non trovato sul rilevatore.';
                                }
                            } else {
                                $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Dati non sufficenti per determinare sanzione.';
                            }
                            
                        } else if($b_IsTraffic){
                            //TEMPO SECONDO FOTOGRAMMA
                            if($a_CSVLine["TEMPO_DAL_ROSSO"] != ''){
                                $TliSecond = $a_CSVLine["TEMPO_DAL_ROSSO"];
                            }
                            $a_DataLines[$n_LineCount]['VelocitaTempo']['value'] = "$TliFirst / $TliSecond";
                            
                            if($TliFirst > 0 || $TliSecond > 0){
                                $a_DataLines[$n_LineCount]['VelocitaTempo']['error'] = 'S';
                            } else {
                                $a_DataLines[$n_LineCount]['VelocitaTempo']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Manca tempo del primo o secondo fotogramma.';
                            }
                            
                            //SECONDO FOTOGRAMMA
                            if(!empty($a_CSVLine['NOME_FILE'])){
                                if(file_exists("$path/{$a_CSVLine['NOME_FILE']}")) {
                                    $SecondTrafficImage = sprintf($str_DocumentIcon, '', $a_CSVLine['NOME_FILE']);
                                } else {
                                    $SecondTrafficImage = sprintf($str_DocumentIcon, 'opaque', $a_CSVLine['NOME_FILE']);
                                    $b_MissingImage = true;
                                }
                            }
                            if(!$b_MissingImage){
                                $a_DataLines[$n_LineCount]['Documenti']['error'] = 'S';
                            } else {
                                $a_DataLines[$n_LineCount]['Documenti']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Manca primo o secondo fotogramma.';
                            }
                            $a_DataLines[$n_LineCount]['Documenti']['value'] = "$FirstTrafficImage&nbsp;$SecondTrafficImage";

                            $b_MissingImage = false;
                            $TliFirst = $TliSecond = 0;
                            $FirstTrafficImage = $SecondTrafficImage = null;
                        }
                        
                        $a_DataLines[$n_LineCount]['Riga']['value'] = $n_LineCount;
                        $a_DataLines[$n_LineCount]['Riga']['error'] = empty($a_Errors[$n_LineCount]) ? (empty($a_Warnings[$n_LineCount]) ? 'S' : 'W') : 'D';
                    
                        //CONTROLLO VERBALI GIà ESISTENTI
                        if(($i % 2 == 0 && $b_IsTraffic) || $b_IsSpeed){
                            if(isset($Code)){
                                $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND Code='$Code'");
                                if(mysqli_num_rows($rs_Fine) > 0){
                                    $a_FoundFines[] = $n_LineCount;
                                    unset($a_Errors[$n_LineCount]);
                                    unset($a_Warnings[$n_LineCount]);
                                }
                            }
                        }
                    }
                }
            } else $str_Error = 'File CSV non valido per questa importazione.';
        } else $str_Error = "Non è stato possibile identificare le colonne nel file: $ImportFile. Controllare la struttura";
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;

echo $str_out;
?>
<div class="row-fluid">
    <div class="col-sm-12 alert alert-warning" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Avviso agli operatori:
                <ul>
                    <li>
                    Prima di eseguire l'importazione verificare che i rilevatori per le violazioni relative a velocità e semaforo siano completi delle configurazioni: <br>
                    in particolare devono essere compilate le sezioni relative agli articoli e al motivo di mancata contestazione collegati.<br>
                    A fine importazione verificare che il campo Località dell'atto sia stato riempito correttamente. In caso di problemi contattare l'assistenza.
                    </li>
                    <li>
                    <strong>Gli atti importati dovranno essere validati in Preinserimenti > Validazione dati.</strong>
                    </li>
                </ul>
            </li></ul>
        </div>
    </div>
	<form id="f_pcprint" action="imp_pcprint_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="ImportFile" value="<?= $ImportFile; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="table_label_H col-sm-12">ELENCO FILE</div>
		
		<div class="clean_row HSpace4"></div>
		<?php if(!empty($a_FileLinks)): ?>
			<?php foreach($a_FileLinks as $file => $fileLink): ?>
				<?php $n_FileCount++; ?>
				<div class="tableRow">
		            <div class="table_caption_H col-sm-1"><?= $n_FileCount; ?></div>
                	<div class="table_caption_H col-sm-10"><?= $file; ?></div>
                	<div class="table_caption_button col-sm-1"><?= $fileLink; ?></div>
				</div>
				
            	<div class="clean_row HSpace4"></div>
			<?php endforeach; ?>
			<?php if($ImportFile != ''): ?>
				<div class="table_caption_I col-sm-12 text-center"><?= $ImportFile; ?></div>
				
				<div class="clean_row HSpace4"></div>
				<?php if($str_Error == ''): ?>
					<div class="table_label_H col-sm-1">Riga</div>
					<div class="table_label_H col-sm-1">Immagini</div>
                    <div class="table_label_H col-sm-1">Data/Ora</div>
                    <div class="table_label_H col-sm-1">Località</div>
                    <div class="table_label_H col-sm-2">Luogo</div>
                    <div class="table_label_H col-sm-1">Accertatore</div>
                    <div class="table_label_H col-sm-2">Rilevatore</div>
                    <div class="table_label_H col-sm-1"><?= $b_IsSpeed ? 'Velocità/Limite' : ($b_IsTraffic ? 'Tempo fotogrammi' : '') ?></div>
                    <div class="table_label_H col-sm-1">Targa</div>    
                    <div class="table_label_H col-sm-1">Sanzione</div> 
					<?php foreach ($a_DataLines as $lineIndex => $line): ?>
						<div class="tableRow">
							<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
								<?= impPcprintPrintIcon($line['Riga']['error'] ?? null); ?> <?= $line['Riga']['value'] ?? ''; ?>
							</div>
								<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
								<?= impPcprintPrintIcon($line['Documenti']['error'] ?? null); ?> <?= $line['Documenti']['value'] ?? ''; ?>
							</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
    							<?= impPcprintPrintIcon($line['Data']['error'] ?? null); ?> <?= $line['Data']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
    							<?= impPcprintPrintIcon($line['Localita']['error'] ?? null); ?> <?= $line['Localita']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-2">
    							<?= impPcprintPrintIcon($line['Indirizzo']['error'] ?? null); ?> <?= $line['Indirizzo']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
    							<?= impPcprintPrintIcon($line['Accertatore']['error'] ?? null); ?> <?= $line['Accertatore']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-2">
    							<?= impPcprintPrintIcon($line['Rilevatore']['error'] ?? null); ?> <?= $line['Rilevatore']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
    							<?= impPcprintPrintIcon($line['VelocitaTempo']['error'] ?? null); ?> <?= $line['VelocitaTempo']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
    							<?= impPcprintPrintIcon($line['Targa']['error'] ?? null); ?> <?= $line['Targa']['value'] ?? ''; ?>
    						</div>
    						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'table_caption_error ' : ''; ?>table_caption_H col-sm-1">
    							<?= impPcprintPrintIcon($line['Sanzione']['error'] ?? null); ?> <?= $line['Sanzione']['value'] ?? ''; ?>
    						</div>
						</div>
						
						<div class="clean_row HSpace4"></div>
					<?php endforeach; ?>
					
					<?php if(!empty($a_Errors) || !empty($a_Warnings)): ?>
						<div class="clean_row HSpace48"></div>	
						
            			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
            			
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
					<div class="col-sm-2 BoxRowLabel">
						Comprimi immagini
					</div>
					<div class="col-sm-1 BoxRowCaption">
						<input type="checkbox" value="1" name="CompressImages" <?= ChkCheckButton($CompressImages); ?>>
					</div>
					<div class="col-sm-9 BoxRowHTitle"></div>
					
					<div class="clean_row HSpace4"></div>
					
	        	    <div class="table_label_H HSpace4" style="height:8rem;">
	        	    	<div class="col-sm-3"></div>
	        	    	<div class="col-sm-6">
		        	    	<?php if(empty($a_Errors)): ?>
                	    		<?= ChkButton($aUserButton, 'imp','<button id="import" type="submit" class="btn btn-success" style="width:10rem; margin-top:2rem;">Importa</button>'); ?>
                	    	<?php endif;?>
	        	    	</div>
	        	    	<div class="col-sm-3"></div>
                    </div>
				<?php else: ?>
	                <div class="text-center bg-danger-dark col-sm-12" style="height:auto;">ERRORE: <?= $str_Error; ?></div>
                
                	<div class="clean_row HSpace4"></div>	
				<?php endif; ?>
			<?php else: ?>
                <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L'IMPORTAZIONE</div>
                
                <div class="clean_row HSpace4"></div>	
			<?php endif; ?>
		<?php else: ?>
            <div class="table_caption_H col-sm-11">Nessun file presente</div>
            <div class="table_caption_button col-sm-1"></div>
		<?php endif; ?>
	</form>
</div>
<script>
$(document).ready(function () {
  	$(".tableRow").mouseover(function(){ 
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css({"background-color" : "#cfeaf7c7", "color" : "black"});
  	});
  	$(".tableRow").mouseout(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css({"background-color" : "", "color" : ""});
  	});
	
    $('#f_pcprint').on('submit', function(e){
    	if (confirm("Si stanno per importare i dati mostrati. Continuare?")){
			$('#import').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
			$('#import').prop('disabled', true);
    	} else {
        	e.preventDefault();
        	return false;
    	}
    });
});
</script>
<?php
include (INC . "/footer.php");
