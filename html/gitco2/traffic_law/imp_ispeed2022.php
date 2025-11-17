<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

/**
 * imp_ispeed2022.php.
 * Legge in input un array contenente la definizione delle colonne del file CSV,
 * il separatore di campi del file CSV,
 * il flusso del file in apertura.
 * Restituisce per ogni riga letta un'associazione nomeColonna => valore.
 * @param $columnsArray array un array contenente la definizione delle colonne del file CSV
 * @param $csvSeparator string il separatore di campi
 * @param $fileStream resource il flusso del file in apertura
 * @return array
 */
function buildLinesArray($columnsArray, $csvSeparator, $fileStream){
    $n_CSVReadLine = 0;
    $a_IndexedLines = array();
    
    while (($a_CSVLine = fgetcsv($fileStream, 0, $csvSeparator)) !== false){
        foreach($a_CSVLine as $lineIndex => $lineValue){
            $a_IndexedLines[$n_CSVReadLine][$columnsArray[$lineIndex]] = $lineValue;
        }
        $n_CSVReadLine++;
    }
    return $a_IndexedLines;
}

/**
 * imp_ispeed2022.php.
 * Restituisce un icona in base al valore booleano passato:
 * verde se vero, rosso se falso.
 * @param $invalid bool
 * @return string
 */
function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-question-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}

$path = VIOLATION_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');

$str_Error = '';
$str_Separator = ';';
$n_FileCount = 0;
$n_LineCount = 0;
$a_FileLinks = array();
$a_DataLines = array();
$a_Errors = array();
$a_Warnings = array();
$a_IndexedLines = array();
$a_FoundFines = array();

$a_CSVColumns = array (
    'serial',
    'transit_date',
    'type',
    'lane',
    'speed',
    'status',
    'image_name',
    'plate',
    'reliability',
    'vehicle_class',
    'length',
    'image_width',
    'image_height',
    'height',
    'detection_score',
    'detection_serial',
    'controller'
);

$rs_VehicleType = $rs->SelectQuery('SELECT Id,TitleIta FROM VehicleType');
$a_VehicleType =  array_column(mysqli_fetch_all($rs_VehicleType,MYSQLI_ASSOC), 'TitleIta', 'Id');

$a_VehicleTypeMapping = array(
    0 => 1
);

if ($directory_handle = opendir($path)) {
    while (($file = readdir($directory_handle)) !== false) {
        if(isset(pathinfo($file)['extension']) && pathinfo($file)['extension'] == 'csv'){
            $a_FileLinks[$file] = ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>');
        }
    }
    closedir($directory_handle);
}

if($ImportFile != ''){
    $fileStream = @fopen($path.$ImportFile,  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, $str_Separator);
        $a_CSVMissingColumns = array_diff($a_CSVColumns, $a_CSVFirstLine);
        
        if(empty($a_CSVMissingColumns)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                $rs_Controller = $rs->Select('Controller', "CityId='{$_SESSION['cityid']}'");
                $a_Controllers = controllersByFieldArray($rs_Controller);
                
                $a_IndexedLines = buildLinesArray($a_CSVColumns, $str_Separator, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $n_LineCount++;
                    
////////////////////CONTROLLO DATI
                    
                    //LUOGO
                    if($a_CSVLine['lane'] != ''){
                        $a_DataLines[$n_LineCount]['Luogo']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Luogo']['value'] = $a_CSVLine['lane'];
                    } else {
                        $a_DataLines[$n_LineCount]['Luogo']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Luogo assente.';
                    }
                    
                    //TARGA
                    $VehiclePlate = null;
                    if($a_CSVLine['plate'] != ''){
                        $VehiclePlate = $a_CSVLine['plate'];
                        $a_DataLines[$n_LineCount]['Targa']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Targa']['value'] = $VehiclePlate;
                    } else {
                        $a_DataLines[$n_LineCount]['Targa']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Targa assente.';
                    }
                    
                    //TIPO VEICOLO
                    if($a_CSVLine['vehicle_class'] != '' && isset($a_VehicleTypeMapping[$a_CSVLine['vehicle_class']])){
                        $VehicleType = $a_VehicleTypeMapping[$a_CSVLine['vehicle_class']];
                        $a_DataLines[$n_LineCount]['TipoVeicolo']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['TipoVeicolo']['value'] = 
                            '<i class="'.$aVehicleTypeId[$VehicleType].' tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$a_VehicleType[$VehicleType].'" style="color:#337AB7;position:absolute;right:0.3rem;top:0.5rem;"></i>';
                    } else {
                        $a_DataLines[$n_LineCount]['TipoVeicolo']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Tipo di veicolo non riconosciuto.';
                    }
                    
                    //DATA E ORA
                    $FineDate = null;
                    $FineTime = null;
                    $DateTime = str_replace(array('D','H','M'), '', $a_CSVLine['transit_date']);
                    list($ViolationDate, $ViolationTime) = array_pad(explode('_',$DateTime), 2, null);
                    
                    if(isset($ViolationDate,$ViolationTime) && ($FormattedDate = validateDateFormat($ViolationDate.$ViolationTime, 'YmdHis'))){
                        $a_DataLines[$n_LineCount]['Data']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Data']['value'] = $FormattedDate->format('d/m/Y H:i:s');
                        $Year = $FormattedDate->format('Y');
                        $FineDate = $FormattedDate->format('Y-m-d');
                        $FineTime = $FormattedDate->format('H:i:s');
                        $FineHour = $FormattedDate->format('G');
                        $FineMinute = $FormattedDate->format('i');
                    } else {
                        $a_DataLines[$n_LineCount]['Data']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Data violazione assente o non valida.';
                    }
                    
                    //IMMAGINE
                    if($a_CSVLine['image_name'] != ''){
                        $Image = pathinfo($a_CSVLine['image_name'], PATHINFO_FILENAME).'.jpg';
                        $a_DataLines[$n_LineCount]['Immagine']['value'] = $Image;
                        
                        if(!file_exists($path.$Image)) {
                            $a_DataLines[$n_LineCount]['Immagine']['error'] = 'D';
                            $a_Errors[$n_LineCount][] = 'Immagine non trovata.';
                        } else {
                            $a_DataLines[$n_LineCount]['Immagine']['error'] = 'S';
                        }
                    } else {
                        $a_DataLines[$n_LineCount]['Immagine']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Impossibile determinare il nome dell\'immagine: data di violazione assente.';
                    }
                    
                    //ACCERTATORI
                    if($a_CSVLine['controller'] != ''){
                        if(isset($FineDate)){
                            $str_Controllers = '';
                            $a_ControllerCodes = explode('+', $a_CSVLine['controller']);
                            foreach($a_ControllerCodes as $code){
                                if (!is_null($ControllerId = getControllerByCode($a_Controllers, $FineDate, trim($code)))) {
                                    $rs_Controller = $rs->Select('Controller', "Id=$ControllerId");
                                    $r_Controller = $rs->getArrayLine($rs_Controller);
                                    $str_Controllers .=
                                    '<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$r_Controller['Code'].' - '.$r_Controller['Name'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                                } else {
                                    $str_Controllers .=
                                    '<i class="fas fa-user opaque" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                                    $a_DataLines[$n_LineCount]['Accertatori']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = "Accertatore non trovato per la seguente matricola: $code";
                                }
                            }
                            $a_DataLines[$n_LineCount]['Accertatori']['error'] = 'S';
                            $a_DataLines[$n_LineCount]['Accertatori']['value'] = $str_Controllers;
                        } else {
                            $a_DataLines[$n_LineCount]['Accertatori']['error'] = 'D';
                            $a_Errors[$n_LineCount][] = "Impossibile determinare gli accertatori: errore nella data di violazione.";
                        }
                    } else {
                        $a_DataLines[$n_LineCount]['Accertatori']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = "Accertatori assenti.";
                    }
                    
                    //RILEVATORE
                    $Detector = null;
                    if($a_CSVLine['detection_serial'] != ''){
                        $DetectorCode = $a_CSVLine['detection_serial'];
                        $rs_Detector = $rs->Select('Detector', "CityId='{$_SESSION['cityid']}' AND Code='$DetectorCode'");
                        $Detector = $rs->getArrayLine($rs_Detector);
                        
                        if($Detector){
                            $a_DataLines[$n_LineCount]['Rilevatore']['error'] = 'S';
                            $a_DataLines[$n_LineCount]['Rilevatore']['value'] = $Detector['Kind'];
                            $ReasonId = $Detector['ReasonId'];
                            $chk_Tolerance = $Detector['Tolerance'];
                            
                            //VELOCITà
                            if($a_CSVLine['speed'] > 0){
                                $SpeedControl = number_format($a_CSVLine['speed'],2,'.','');
                                $a_DataLines[$n_LineCount]['Velocità']['value'] = $SpeedControl;
                            } else {
                                $a_DataLines[$n_LineCount]['Velocità']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = "Velocità rilevata assente o non valida.";
                            }
                            if($a_CSVLine['status'] > 0){
                                $SpeedLimit = number_format($a_CSVLine['status'],2,'.','');
                                $a_DataLines[$n_LineCount]['Limite']['value'] = $SpeedLimit;
                            } else {
                                $a_DataLines[$n_LineCount]['Velocità']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = "Limite velocità assente o non valido.";
                            }
                            
                            //ARTICOLO
                            if(isset($SpeedControl,$SpeedLimit)){
                                $chk_Tolerance = ($chk_Tolerance>FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;
                                $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chk_Tolerance);
                                $Speed =  number_format(getSpeed($SpeedControl, $SpeedLimit, $chk_Tolerance),2,'.','');
                                
                                //Se la velocità è minore del limite rende un avviso
                                if ($SpeedLimit >= $Speed){
                                    $a_DataLines[$n_LineCount]['Velocità']['error'] = 'W';
                                    $a_Warnings[$n_LineCount][] = "Velocità non supera il limite. Limite: $SpeedLimit - Velocità con tolleranza: $Speed";
                                } else {
                                    $a_DataLines[$n_LineCount]['Velocità']['error'] = 'S';
                                }
                                
                                if(isset($Year)){
                                    $Article = getVArticle($Detector['Id'],$_SESSION['cityid'],$SpeedExcess,$Year);
                                    
                                    if($Article){
                                        $Fee = $Article['Fee'];
                                        $MaxFee = $Article['MaxFee'];
                                        $ViolationTypeId = $Article['ViolationTypeId'];
                                        $AdditionalNight = $Article['AdditionalNight'];
                                        
                                        if($AdditionalNight){
                                            if($FineHour < FINE_HOUR_START_DAY || $FineHour > FINE_HOUR_END_DAY || ($FineHour == FINE_HOUR_END_DAY && $FineMinute != "00")){
                                                $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                                                $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);
                                            }
                                        };
                                        
                                        $a_DataLines[$n_LineCount]['Sanzione']['error']  = 'S';
                                        $a_DataLines[$n_LineCount]['Sanzione']['value']  = $Fee." / ".$MaxFee;
                                        
                                        //MANCATA CONTESTAZIONE
                                        $rs_Reasons = getReasonRs($ReasonId,$_SESSION['cityid'],$ViolationTypeId,$DetectorCode);
                                        $Reason = $rs->getArrayLine($rs_Reasons);
                                        
                                        if($Reason){
                                        } else {
                                            $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                                            $a_Errors[$n_LineCount][] = "Mancata contestazione assente.";
                                        }
                                    } else {
                                        $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                                        $a_Errors[$n_LineCount][] = "Non è possibile determinare la sanzione: articolo assente.";
                                    }
                                } else {
                                    $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                                    $a_Errors[$n_LineCount][] = 'Impossibile determinare la sanzione: errore nelle data di violazione.';
                                }
                            } else {
                                $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                                $a_Errors[$n_LineCount][] = 'Impossibile determinare la sanzione: errore nelle velocità.';
                            }
                        } else {
                            $a_DataLines[$n_LineCount]['Rilevatore']['error'] = 'D';
                            $a_Errors[$n_LineCount][] = 'Rilevatore non trovato per codice import: '.$a_CSVLine['detection_serial'];
                        }
                    } else {
                        $a_DataLines[$n_LineCount]['Rilevatore']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Codice import o matricola rilevatore non specificati.';
                    }
                    
                    //Se il rilevatore non è presente, velocità e sanzione non sono calcolabili
                    if(!isset($Detector)){
                        $a_DataLines[$n_LineCount]['Sanzione']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Impossibile determinare la sanzione: rilevatore assente.';
                        $a_DataLines[$n_LineCount]['Velocità']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Impossibile determinare la velocità: rilevatore assente.';
                    }
                    
                    $a_DataLines[$n_LineCount]['Riga']['value'] = $n_LineCount;
                    $a_DataLines[$n_LineCount]['Riga']['error'] = empty($a_Errors[$n_LineCount]) ? (empty($a_Warnings[$n_LineCount]) ? 'S' : 'W') : 'D';
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($VehiclePlate,$FineDate,$FineTime)){
                        $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND VehiclePlate='$VehiclePlate' AND FineDate='$FineDate' AND FineTime='$FineTime'");
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $a_FoundFines[] = $n_LineCount;
                            unset($a_Errors[$n_LineCount]);
                        }
                    }
                }
            } else $str_Error = 'File CSV non valido per questa importazione.<br>Colonne previste: '.count($a_CSVColumns).', Colonne identificate: '.count($a_CSVFirstLine);
        } else $str_Error = 'File CSV non valido per questa importazione.<br>La struttura non presenta i seguenti campi: '.implode(', ', $a_CSVMissingColumns);
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_ispeed_new" action="imp_ispeed2022_exe.php" method="post" autocomplete="off">
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
				<?php if($str_Error == ''): ?>
		            <div class="table_label_H col-sm-1">Riga</div>
		            <div class="table_label_H col-sm-2">Immagine</div>	
                    <div class="table_label_H col-sm-2">Targa/Data/Tipo veicolo</div>
                    <div class="table_label_H col-sm-2">Luogo</div>
                    <div class="table_label_H col-sm-2">Rilevatore</div>
                    <div class="table_label_H col-sm-1">Accertatori</div>
                    <div class="table_label_H col-sm-1">Velocità/Limite</div>
                    <div class="table_label_H col-sm-1">Sanzione</div>            
					<?php foreach ($a_DataLines as $lineIndex => $line): ?>
						<div class="tableRow">
							<?php if(in_array($lineIndex, $a_FoundFines)): ?>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Riga']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= $line['Immagine']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2">
        							<?= $line['Targa']['value'] ?? ''; ?> <?= $line['Data']['value'] ?? ''; ?> <?= $line['TipoVeicolo']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= $line['Luogo']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= $line['Rilevatore']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Accertatori']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Velocità']['value'] ?? ''; ?> / <?= $line['Limite']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Sanzione']['value'] ?? ''; ?>
        						</div>
        					<?php else: ?>
        						<div class="table_caption_H col-sm-1">
        							<?= printIcon($line['Riga']['error'] ?? null); ?> <?= $line['Riga']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Immagine']['error'] ?? null); ?> <?= $line['Immagine']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_H col-sm-2">
        							<?= printIcon($line['Targa']['error'] ?? null); ?> <?= $line['Targa']['value'] ?? ''; ?> 
        							<?= printIcon($line['Data']['error'] ?? null); ?> <?= $line['Data']['value'] ?? ''; ?> 
        							<?= printIcon($line['TipoVeicolo']['error'] ?? null); ?> <?= $line['TipoVeicolo']['value'] ?? ''; ?> 
        						</div>
        						<div class="table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Luogo']['error'] ?? null); ?> <?= $line['Luogo']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Rilevatore']['error'] ?? null); ?> <?= $line['Rilevatore']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_H col-sm-1">
        							<?= printIcon($line['Accertatori']['error'] ?? null); ?> <?= $line['Accertatori']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_H col-sm-1">
        							<?= printIcon($line['Velocità']['error'] ?? null); ?> <?= $line['Velocità']['value'] ?? ''; ?> / <?= $line['Limite']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_H col-sm-1">
        							<?= printIcon($line['Sanzione']['error'] ?? null); ?> <?= $line['Sanzione']['value'] ?? ''; ?>
        						</div>
        					<?php endif; ?>
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
					
	        	    <div class="table_label_H HSpace4" style="height:8rem;">
	        	    	<div class="col-sm-3"></div>
	        	    	<div class="col-sm-6">
		        	    	<?php if(empty($a_Errors) && count($a_FoundFines) < count($a_DataLines)): ?>
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
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
  	});
  	$(".tableRow").mouseout(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
  	});
	
    $('#f_ispeed_new').on('submit', function(e){
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

