<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_maggioli_xml.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

/** @var $rs CLS_DB */

if (!is_dir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'])) {
    mkdir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid']);
    chmod(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'], 0750);
}
if (!is_dir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/DA_ESTRARRE')) {
    mkdir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/DA_ESTRARRE');
    chmod(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/DA_ESTRARRE', 0770);
}
if (!is_dir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/ESTRATTI')) {
    mkdir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/ESTRATTI');
    chmod(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/ESTRATTI', 0750);
}
if (!is_dir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/ARCHIVIATI')) {
    mkdir(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/ARCHIVIATI');
    chmod(IMPORT_FOLDER_MAGGIOLI . '/' . $_SESSION['cityid'] . '/ARCHIVIATI', 0750);
}

$ImportFile = CheckValue('ImportFile','s');

$CityId = $_SESSION['cityid'];
$filesPath = IMPORT_FOLDER_MAGGIOLI."/".$CityId;
$filesPathHTML = IMPORT_FOLDER_MAGGIOLI_HTML."/".$CityId;

$a_ConciliaVehicleTypeMapping = unserialize(IMP_MAGGIOLI_CONCILIA_VEHICLETYPE);

$str_Error = '';
$a_zipFiles = array();
$a_Folders = array();
$a_DataLines = array();
$a_Errors = array();
$a_Warnings = array();
$a_FoundFines = array();

if ($directory_handle = @opendir("$filesPath/DA_ESTRARRE/")) {
    while (($file = readdir($directory_handle)) !== false) {
        if(pathinfo($file,PATHINFO_EXTENSION)){
            $a_zipFiles[] = $file;
        }
    }
    closedir($directory_handle);
} else $str_Error .= "Impossibile aprire la cartella delle importazioni da estrarre.<br>";

if ($directory_handle = @opendir("$filesPath/ESTRATTI/")) {
    while (($file = readdir($directory_handle)) !== false) {
        if(is_dir("$filesPath/ESTRATTI/$file") && !in_array($file, array('.','..'))){
            $a_Folders[] = $file;
        }
    }
    closedir($directory_handle);
} else $str_Error .= "Impossibile aprire la cartella delle importazioni estratte.<br>";

if($ImportFile != ''){
    $importPath = "$filesPath/ESTRATTI/$ImportFile";
    $importPathHTML = "$filesPathHTML/ESTRATTI/$ImportFile";
    
    if(is_dir($importPath)){
        if(!empty($xmlFiles = glob("$importPath/*.xml"))){
            $xmlFile = $xmlFiles[0];
            
            if(is_array($checkedXml = checkAndReadXml($xmlFile))){
                $rs_VehicleType = $rs->SelectQuery('SELECT Id,TitleIta FROM VehicleType');
                $a_VehicleType = array_column($rs->getResults($rs_VehicleType), 'TitleIta', 'Id');
                
                $rs_Controller = $rs->Select('Controller', "CityId='{$CityId}'");
                $a_Controllers = controllersByFieldArray($rs_Controller);
                
                foreach($checkedXml as $ln => $data){
                    
//////////////////////CONTROLLO DATI
                    
                    //CodiceUID (Riferimento)
                    if(!empty($data['CodiceUID'])){
                        $Code = $data['CodiceUID'];
                        $a_DataLines[$ln]['Riferimento']['error'] = 'S';
                        $a_DataLines[$ln]['Riferimento']['value'] = $data['CodiceUID'];
                    } else {
                        $a_DataLines[$ln]['Riferimento']['error'] = 'D';
                        $a_Errors[$ln][] = 'CodiceUID (Riferimento) assente.';
                    }
                    
                    //Protocollo/Anno
                    if(!empty($data['NumeroProtocollo'])){
                        $FullProtocol = $data['NumeroProtocollo'];
                        if(preg_match(IMP_MAGGIOLI_FULLPROTOCOL_RGX, $FullProtocol)){
                            $a_DataLines[$ln]['Protocollo']['error'] = 'S';
                            $a_DataLines[$ln]['Protocollo']['value'] = $FullProtocol;
                        } else {
                            $a_DataLines[$ln]['Protocollo']['error'] = 'D';
                            $a_Errors[$ln][] = 'Numero protocollo non valido.';
                        }
                    } else {
                        $a_DataLines[$ln]['Protocollo']['error'] = 'D';
                        $a_Errors[$ln][] = 'Numero protocollo assente.';
                    }
                    
                    //DOCUMENTI
                    $str_Documents = '';
                    $b_MissingDocuments = false;
                    $b_HasLeasingDoc = false;
                    if(!empty($data['ImmagineViolazione'])){
                        $imagePath = str_replace('\\', '/', $data['ImmagineViolazione']);
                        if(file_exists("$importPath/$imagePath")) {
                            $str_Documents .=
                            '<a target="_blank" href="'.$importPathHTML.'/'.$imagePath.'" class="fas fa-picture-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="'.pathinfo($imagePath, PATHINFO_BASENAME).'" style="font-size:1.8rem;margin-top:0.2rem"></a>&nbsp;';
                        } else {
                            $str_Documents .=
                            '<i class="fas fa-picture-o opaque" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                            $a_DataLines[$ln]['Documenti']['error'] = 'D';
                            $a_Errors[$ln][] = 'Immagine non trovata.';
                            $b_MissingDocuments = true;
                        }
                    }
                    //TODO in realtà questo campo prevederebbe il valore di un'eventuale cartolina di notifica (CARTOLINE/<nomeFile>)
                    //e non della comunicazione al locatario di noleggio, che sarebbe prevista in DocumentoNoleggio
                    if(!empty($data['ImmagineCartolina'])){
                        $leasingPath = str_replace('\\', '/', $data['ImmagineCartolina']);
                        if(file_exists("$importPath/$leasingPath")) {
                            $b_HasLeasingDoc = true;
                            $str_Documents .=
                            '<a target="_blank" href="'.$importPathHTML.'/'.$leasingPath.'" class="fas fa-file-text tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="'.pathinfo($leasingPath, PATHINFO_BASENAME).'" style="font-size:1.8rem;margin-top:0.2rem"></a>&nbsp;';
                        } else {
                            $str_Documents .=
                            '<i class="fas fa-file-text opaque" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                            $a_DataLines[$ln]['Documenti']['error'] = 'D';
                            $a_Errors[$ln][] = 'Comunicazione locatario non trovata.';
                            $b_MissingDocuments = true;
                        }
                    }
                    $a_DataLines[$ln]['Documenti']['error'] = $b_MissingDocuments ? 'D' : (!empty($str_Documents) ? 'S' : null);
                    $a_DataLines[$ln]['Documenti']['value'] = $str_Documents;
                    
                    //TRASGRESSORI
                    $str_Trespassers = '';
                    if(!empty($data['ProprietarioNominativo']) && !empty($data['TrasgressoreNominativo'])){
                        $str_Trespassers .=
                        '<i class="fas fa-user-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Noleggiante: '.$data['TrasgressoreNominativo'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                        $str_Trespassers .=
                        '<i class="fas fa-user-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Noleggio: '.$data['ProprietarioNominativo'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                        if($b_HasLeasingDoc){
                            $a_DataLines[$ln]['Trasgressori']['error'] = 'S';
                        } else {
                            $a_DataLines[$ln]['Trasgressori']['error'] = 'W';
                            $a_Warnings[$ln][] = "Comunicazione locatario prevista ma assente.";
                        }
                    }
                    else if(!empty($data['ProprietarioNominativo'])){
                        $a_DataLines[$ln]['Trasgressori']['error'] = 'S';
                        $str_Trespassers .=
                        '<i class="fas fa-user-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Proprietario: '.$data['ProprietarioNominativo'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                    }
                    $a_DataLines[$ln]['Trasgressori']['value'] = $str_Trespassers;
                    
                    //NAZIONE TARGA
                    if(!empty($data['NazioneTarga'])){
                        $Country = $rs->getArrayLine($rs->Select('Country', "ISO3='{$data['NazioneTarga']}'"));
                        if($Country){
                            $a_DataLines[$ln]['Nazione']['error'] = 'S';
                            $a_DataLines[$ln]['Nazione']['value'] = $Country['Title'];
                        } else {
                            $a_DataLines[$ln]['Nazione']['error'] = 'D';
                            $a_Errors[$ln][] = "Nazione non riconosciuta sulla banca dati: {$data['NazioneTarga']}.";
                        }
                    } else {
                        $a_DataLines[$ln]['Nazione']['error'] = 'W';
                        $a_Warnings[$ln][] = "Nazione targa assente. Salvataggio a sistema come 'Da assegnare'.";
                    }
                    
                    //TARGA
                    if(!empty($data['Targa'])){
                        $VehiclePlate = $data['Targa'];
                        $a_DataLines[$ln]['Targa']['error'] = 'S';
                        $a_DataLines[$ln]['Targa']['value'] = $VehiclePlate;
                    } else {
                        $a_DataLines[$ln]['Targa']['error'] = 'D';
                        $a_Errors[$ln][] = 'Targa assente.';
                    }
                    
                    //TIPO VEICOLO
                    if(!empty($data['TipoVeicoloConcilia']) && isset($a_ConciliaVehicleTypeMapping[$data['TipoVeicoloConcilia']])){
                        $VehicleType = $a_ConciliaVehicleTypeMapping[$data['TipoVeicoloConcilia']];
                        $a_DataLines[$ln]['TipoVeicolo']['error'] = 'S';
                        $a_DataLines[$ln]['TipoVeicolo']['value'] =
                        '<i class="'.$aVehicleTypeId[$VehicleType].' tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$a_VehicleType[$VehicleType].'" style="color:#337AB7;position:absolute;right:0.3rem;top:0.5rem;"></i>';
                    } else {
                        $a_DataLines[$ln]['TipoVeicolo']['error'] = 'D';
                        $a_Errors[$ln][] = 'Tipo di veicolo non riconosciuto.';
                    }
                    
                    //DATA E ORA VIOLAZIONE
                    if(($dt_FineDate = validateDateFormat($data['DataViolazione'], 'd/m/Y')) &&
                       ($dt_FineTime = validateDateFormat($data['OraViolazione'], 'H:i'))){
                        $FineDate = $dt_FineDate->format('d/m/Y');
                        $FineHour = $dt_FineTime->format('G');
                        $FineMinute = $dt_FineTime->format('i');
                        $Year = $dt_FineDate->format('Y');
                        $a_DataLines[$ln]['Data']['error'] = 'S';
                        $a_DataLines[$ln]['Data']['value'] = $FineDate.' '.$dt_FineTime->format('H:i');
                    } else {
                        $a_DataLines[$ln]['Data']['error'] = 'D';
                        $a_Errors[$ln][] = 'Data/ora violazione assente o non valida.';
                    }
                    
                    //DATA E ORA ACCERTAMENTO
                    if(!empty($data['DataVerbale']) && !validateDateFormat($data['DataVerbale'], 'd/m/Y')){
                        $a_Errors[$ln][] = 'Data di accertamento non valida.';
                    }
                    if(!empty($data['OraVerbale']) && !validateDateFormat($data['OraVerbale'], 'H:i')){
                        $a_Errors[$ln][] = 'Ora di accertamento non valida.';
                    }
                    
                    //LUOGO
                    if(!empty(trim($data['LuogoInfrazione']))){
                        $a_DataLines[$ln]['Luogo']['error'] = 'S';
                        $a_DataLines[$ln]['Luogo']['value'] = trim($data['LuogoInfrazione']);
                    } else {
                        $a_DataLines[$ln]['Luogo']['error'] = 'D';
                        $a_Errors[$ln][] = 'Luogo infrazione assente.';
                    }
                    
                    //ACCERTATORI
                    $a_ControllerCodes = array_filter(preg_grep_keys('/MatricolaAgente/', $data));
                    $b_MissingControllers = false;
                    if(!empty($a_ControllerCodes)){
                        if(isset($FineDate)){
                            $str_Controllers = '';
                            foreach($a_ControllerCodes as $code){
                                if (!is_null($ControllerId = getControllerByCode($a_Controllers, $FineDate, trim($code)))) {
                                    $rs_Controller = $rs->Select('Controller', "Id=$ControllerId");
                                    $r_Controller = $rs->getArrayLine($rs_Controller);
                                    $str_Controllers .=
                                    '<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$r_Controller['Code'].' - '.$r_Controller['Name'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                                } else {
                                    $str_Controllers .=
                                    '<i class="fas fa-user opaque" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                                    $a_Errors[$ln][] = "Accertatore non trovato per la seguente matricola: $code";
                                    $b_MissingControllers = true;
                                }
                            }
                            $a_DataLines[$ln]['Accertatori']['error'] = $b_MissingControllers ? 'D' : 'S';
                            $a_DataLines[$ln]['Accertatori']['value'] = $str_Controllers;
                        } else {
                            $a_DataLines[$ln]['Accertatori']['error'] = 'D';
                            $a_Errors[$ln][] = "Impossibile determinare gli accertatori: errore nella data di violazione.";
                        }
                    } else {
                        $a_DataLines[$ln]['Accertatori']['error'] = 'D';
                        $a_Errors[$ln][] = "Accertatori assenti.";
                    }
                    
                    //RILEVATORE
                    $b_InvalidRatification = false;
                    $DetectorCode = 0;
                    if(!empty($data['CodiceVeloxConcilia'])){
                        $DetectorCode = $data['CodiceVeloxConcilia'];
                        $rs_Detector = $rs->Select('Detector', "CityId='{$CityId}' AND Code='$DetectorCode'");
                        $r_Detector = $rs->getArrayLine($rs_Detector);
                        
                        if($r_Detector){
                            $str_Detector = '';
                            $ReasonId = $r_Detector['ReasonId'];
                            
                            //VELOCITà
                            $SpeedLimit = $data['LimiteVelocita'];
                            $SpeedControl = $data['VelocitaRilevata'];
                            $Speed = round($data['VelocitaDifferenza']);
                            
                            $a_DataLines[$ln]['Velocità']['value'] = implode(' / ', array(
                                NumberDisplay($SpeedLimit), 
                                NumberDisplay($SpeedControl), 
                                NumberDisplay($Speed)));
                            if(!empty($SpeedLimit) && !empty($SpeedControl) && !empty($Speed)){
                                $a_DataLines[$ln]['Velocità']['error'] = 'S';
                            } else {
                                $a_DataLines[$ln]['Velocità']['error'] = 'D';
                                $a_Errors[$ln][] = "Una o più velocità sono assenti.";
                            }
                            
                            //TARATURA
                            if(!empty($data['DataInizioValiditaTaratura']) && isset($FineDate)){
                                $rs_DetectorRatification = $rs->Select('DetectorRatification', "DetectorId={$r_Detector['Id']} AND ((FromDate <= '".DateInDB($FineDate)."' AND (ToDate >= '".DateInDB($FineDate)."' OR ToDate IS NULL)) OR (FromDate IS NULL AND ToDate IS NULL))");
                                $DetectorRatification = $rs->getArrayLine($rs_DetectorRatification);
                                
                                if($DetectorRatification){
                                    $str_Ratification = 'Validità taratura<br>'.
                                        (!empty($DetectorRatification['FromDate']) ? 'Inizio: '.DateOutDB($DetectorRatification['FromDate']).'<br>' : '').
                                        (!empty($DetectorRatification['ToDate']) ? 'Fine: '.DateOutDB($DetectorRatification['ToDate']).'<br>' : '').
                                        (!empty($DetectorRatification['Ratification']) ? $DetectorRatification['Ratification'] : '');
                                    $str_Detector .=
                                    '<i class="fa fa-tachometer tooltip-r" data-html="true" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$str_Ratification.'" style="font-size:1.8rem;margin-top:0.2rem;"></i>&nbsp;';
                                } else {
                                    $str_Detector .=
                                    '<i class="fa fa-tachometer opaque" style="font-size:1.8rem;margin-top:0.2rem"></i>&nbsp;';
                                    $a_Errors[$ln][] = "Il rilevatore prevede una validità di taratura non registrata a sistema. Data inizio validità: {$data['DataInizioValiditaTaratura']}";
                                    $b_InvalidRatification = true;
                                }
                            }
                            
                            $str_Detector .= $r_Detector['Kind'];
                            
                            $a_DataLines[$ln]['Rilevatore']['error'] = $b_InvalidRatification ? 'D' : 'S';
                            $a_DataLines[$ln]['Rilevatore']['value'] = $str_Detector;
    
                        } else {
                            $a_DataLines[$ln]['Rilevatore']['error'] = 'D';
                            $a_Errors[$ln][] = "Rilevatore non trovato per codice import: {$data['CodiceVeloxConcilia']}";
                        }
                    }
                    
                    //ARTICOLI
                    $a_Articles = array_filter(preg_grep_keys('/ArticoloConcilia/', $data));
                    $b_MissingArticles = false;
                    $ViolationTypeId = 0;
                    $str_Articles = '';
                    if(!empty($a_Articles)){
                        if(isset($Year)){
                            foreach($a_Articles as $index => $article){
                                //Usiamo ArtComune perchè nel caso di Siena, ci vengono passati articoli diversi per tipo di veicolo
                                //E non possiamo dedurne la composizione di Articolo, Comma e Lettera. Es: 142/7b, 142/11x ecc...
                                $Article = $rs->getArrayLine($rs->Select('V_Article', "CityId='{$CityId}' AND ArtComune='$article' AND Year=$Year"));
                                
                                if($Article){
                                    //SANZIONI
                                    $Fee = $Article['Fee'];
                                    $MaxFee = $Article['MaxFee'];
                                    $AdditionalNight = $Article['AdditionalNight'];
                                    //Se primo articolo (indice 0) valorizzo il tipo di violazione
                                    $ViolationTypeId = $index == 0 ? $Article['ViolationTypeId'] : $ViolationTypeId;
                                    
                                    if($AdditionalNight){
                                        if($FineHour < FINE_HOUR_START_DAY || $FineHour > FINE_HOUR_END_DAY || ($FineHour == FINE_HOUR_END_DAY && $FineMinute != "00")){
                                            $Fee = $Fee + ceil(($Fee/FINE_NIGHT)*100)/100;
                                            $MaxFee = $MaxFee + ceil(($MaxFee/FINE_NIGHT)*100)/100;
                                        }
                                    }
                                    
                                    $PartialFee = $Article['ReducedPayment'] ? $Fee * FINE_PARTIAL : $Fee;
                                    
                                    $str_Articles .=
                                    '<i class="fas fa-pencil-square-o tooltip-r" data-html="true" data-container="body" data-toggle="tooltip" data-placement="top" title="Art. '.$article.'<br>Ridotta: € '.NumberDisplay($PartialFee).'<br>Minima: € '.NumberDisplay($Fee).'<br>Massima: € '.NumberDisplay($MaxFee).'" style="font-size:1.6rem;margin-top:0.2rem;"></i>&nbsp;';
                                } else {
                                    $str_Articles .=
                                    '<i class="fas fa-pencil-square-o opaque" style="font-size:1.6rem;margin-top:0.2rem"></i>&nbsp;';
                                    $a_Errors[$ln][] = "Articolo non trovato per l'anno $Year: $article";
                                    $b_MissingArticles = true;
                                }
                            }
                            $a_DataLines[$ln]['Articoli']['error'] = $b_MissingArticles ? 'D' : 'S';
                            $a_DataLines[$ln]['Articoli']['value'] = $str_Articles;
                        } else {
                            $a_DataLines[$ln]['Articoli']['error'] = 'D';
                            $a_Errors[$ln][] = "Impossibile determinare gli articoli: anno verbale assente.";
                        }
                    } else {
                        $a_DataLines[$ln]['Articoli']['error'] = 'D';
                        $a_Errors[$ln][] = "Articoli assenti.";
                    }
                    
                    //MANCATA CONTESTAZIONE
                    $rs_Reasons = getReasonRs($ReasonId ?? null, $CityId ,$ViolationTypeId, $DetectorCode);
                    $Reason = $rs->getArrayLine($rs_Reasons);
                    if($Reason){
                        $a_DataLines[$ln]['MancataCont']['error'] = 'S';
                        $a_DataLines[$ln]['MancataCont']['value'] = "{$Reason['Progressive']} - {$Reason['TitleIta']}";
                    } else {
                        $a_DataLines[$ln]['MancataCont']['error'] = 'D';
                        $a_Errors[$ln][] = "Mancata contestazione assente.";
                    }
                    
                    //ERRORI NELLA RIGA
                    $a_DataLines[$ln]['Riga']['error'] = empty($a_Errors[$ln]) ? (empty($a_Warnings[$ln]) ? 'S' : 'W') : 'D';
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($Code)){
                        $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND Code='$Code'");
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $a_FoundFines[] = $ln;
                            unset($a_Errors[$ln]);
                            unset($a_Warnings[$ln]);
                        }
                    }
                }
            } else $str_Error .= "Impossibile leggere il file .xml: $checkedXml";
        } else $str_Error .= "Nessun file .xml trovato all'interno della cartella: $ImportFile";
    } else $str_Error .= "La cartella non esiste: $ImportFile";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_maggioli" action="imp_maggioli_xml_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="ImportFile" value="<?= $ImportFile; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="table_caption_I col-sm-12 text-center">IMPORTAZIONE NOTIFICHE MAGGIOLI</div>
		
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
				
	            <div class="table_label_H col-sm-1">Riga</div>
	            <div class="table_label_H col-sm-1">Prot. esterno</div>
                <div class="table_label_H col-sm-2">Riferimento</div> 
                <div class="table_label_H col-sm-2">Targa/Data/Tipo veicolo</div>
                <div class="table_label_H col-sm-2">Luogo</div>
                <div class="table_label_H col-sm-2">Nazione targa</div>	
                <div class="table_label_H col-sm-1">Accertatori</div>
                <div class="table_label_H col-sm-1">Documenti</div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="table_label_H col-sm-1"></div>
                <div class="table_label_H col-sm-1">Articoli</div>
                <div class="table_label_H col-sm-2">Rilevatore</div>
                <div class="table_label_H col-sm-2">Velocità Limite/Ril./Diff.</div>
	            <div class="table_label_H col-sm-5">Mancata contestazione</div>	
	            <div class="table_label_H col-sm-1">Trasgressori</div>
	            
	            <div class="clean_row HSpace4"></div>
                
				<?php foreach ($a_DataLines as $lineIndex => $line): ?>
					<div class="tableRow">
					<?php if(in_array($lineIndex, $a_FoundFines)): ?>
			            <div class="table_caption_H table_caption_error col-sm-1">
			            	<?= $lineIndex ?>
						</div>
			            <div class="table_caption_H table_caption_error col-sm-1">
							<?= $line['Protocollo']['value'] ?? ''; ?>
						</div>
                        <div class="table_caption_H table_caption_error col-sm-2">
							<?= $line['Riferimento']['value'] ?? ''; ?>
                    	</div>  
                        <div class="table_caption_H table_caption_error col-sm-2">
							<?= $line['Targa']['value'] ?? ''; ?> 
							<?= $line['Data']['value'] ?? ''; ?> 
							<?= $line['TipoVeicolo']['value'] ?? ''; ?> 
                        </div>
                        <div class="table_caption_H table_caption_error col-sm-2">
                        	<?= $line['Luogo']['value'] ?? ''; ?>
                        </div>
        	            <div class="table_caption_H table_caption_error col-sm-2">
        	            	<?= $line['Nazione']['value'] ?? ''; ?>
        	            </div>	
                        <div class="table_caption_H table_caption_error col-sm-1">
                        	<?= $line['Accertatori']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H table_caption_error col-sm-1">
                        	<?= $line['Documenti']['value'] ?? ''; ?>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="table_caption_H table_caption_error col-sm-1"></div>
                        <div class="table_caption_H table_caption_error col-sm-1">
                        	<?= $line['Articoli']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H table_caption_error col-sm-2">
                        	<?= $line['Rilevatore']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H table_caption_error col-sm-2">
                        	<?= $line['Velocità']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H table_caption_error col-sm-5" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        	<?= $line['MancataCont']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H table_caption_error col-sm-1">
                        	<?= $line['Trasgressori']['value'] ?? ''; ?>
                        </div>
					<?php else: ?>
			            <div class="table_caption_H col-sm-1">
			            	<?= printIcon($line['Riga']['error'] ?? null); ?> <?= $lineIndex ?>
						</div>
			            <div class="table_caption_H col-sm-1">
							<?= printIcon($line['Protocollo']['error'] ?? null); ?> <?= $line['Protocollo']['value'] ?? ''; ?>
						</div>
                        <div class="table_caption_H col-sm-2">
							<?= printIcon($line['Riferimento']['error'] ?? null); ?> <?= $line['Riferimento']['value'] ?? ''; ?>
                    	</div>  
                        <div class="table_caption_H col-sm-2">
							<?= printIcon($line['Targa']['error'] ?? null); ?> <?= $line['Targa']['value'] ?? ''; ?> 
							<?= printIcon($line['Data']['error'] ?? null); ?> <?= $line['Data']['value'] ?? ''; ?> 
							<?= printIcon($line['TipoVeicolo']['error'] ?? null); ?> <?= $line['TipoVeicolo']['value'] ?? ''; ?> 
                        </div>
                        <div class="table_caption_H col-sm-2">
                        	<?= printIcon($line['Luogo']['error'] ?? null); ?> <?= $line['Luogo']['value'] ?? ''; ?>
                        </div>
        	            <div class="table_caption_H col-sm-2">
        	            	<?= printIcon($line['Nazione']['error'] ?? null); ?> <?= $line['Nazione']['value'] ?? ''; ?>
        	            </div>	
                        <div class="table_caption_H col-sm-1">
                        	<?= printIcon($line['Accertatori']['error'] ?? null); ?> <?= $line['Accertatori']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H col-sm-1">
                        	<?= printIcon($line['Documenti']['error'] ?? null); ?> <?= $line['Documenti']['value'] ?? ''; ?>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="table_caption_H col-sm-1"></div>
                        <div class="table_caption_H col-sm-1">
                        	<?= printIcon($line['Articoli']['error'] ?? null); ?> <?= $line['Articoli']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H col-sm-2">
                        	<?= printIcon($line['Rilevatore']['error'] ?? null); ?> <?= $line['Rilevatore']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H col-sm-2">
                        	<?= printIcon($line['Velocità']['error'] ?? null); ?> <?= $line['Velocità']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H col-sm-5" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        	<?= printIcon($line['MancataCont']['error'] ?? null); ?> <?= $line['MancataCont']['value'] ?? ''; ?>
                        </div>
                        <div class="table_caption_H col-sm-1">
                        	<?= printIcon($line['Trasgressori']['error'] ?? null); ?> <?= $line['Trasgressori']['value'] ?? ''; ?>
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
            $('#f_maggioli').append($input).submit();
		}
    });
    
});
</script>
<?php
require_once (INC . "/footer.php");

