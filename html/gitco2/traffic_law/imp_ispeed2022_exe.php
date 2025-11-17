<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 5000);

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

$ImportFile = CheckValue('ImportFile','s');
$Filters = CheckValue('Filters', 's');

$str_Error = '';
$str_Separator = ';';
$n_LineCount = 0;
$n_CompletedCount = 0;
$a_ErrorLines = array();

$DocumentationTypeId = 1;
$StatusTypeId = 1;
$VehicleCountry = "Italia";
$CountryId = "Z000";
$DepartmentId = 0;
$VehicleMass = 0;

$CityId = $_SESSION['cityid'];
$path = VIOLATION_FOLDER."/".$_SESSION['cityid']."/";

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

$a_VehicleTypeMapping = array(
    0 => 1
);

$rs_Controller = $rs->Select('Controller', "CityId='".$CityId."'");
$a_Controllers = controllersByFieldArray($rs_Controller);

if($ImportFile != ''){
    $fileStream = @fopen($path.$ImportFile,  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, $str_Separator);
        $a_CSVMissingColumns = array_diff($a_CSVColumns, $a_CSVFirstLine);
        
        if(empty($a_CSVMissingColumns)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                
                $a_IndexedLines = buildLinesArray($a_CSVColumns, $str_Separator, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $n_LineCount ++;

////////////////////CONTROLLO DATI

                    //LUOGO
                    if($a_CSVLine['lane'] != ''){
                        $Address = $a_CSVLine['lane'];
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Indirizzo assente.';
                        continue;
                    }
                    
                    //TARGA
                    $VehiclePlate = null;
                    if($a_CSVLine['plate'] != ''){
                        $VehiclePlate = $a_CSVLine['plate'];
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Targa assente.';
                        continue;
                    }
                    
                    //TIPO VEICOLO
                    if($a_CSVLine['vehicle_class'] != '' && isset($a_VehicleTypeMapping[$a_CSVLine['vehicle_class']])){
                        $VehicleTypeId = $a_VehicleTypeMapping[$a_CSVLine['vehicle_class']];
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Tipo di veicolo non riconosciuto.';
                        continue;
                    }
                    
                    //DATA E ORA
                    $Code = $a_CSVLine['transit_date'];
                    $FineDate = null;
                    $FineTime = null;
                    $DateTime = str_replace(array('D','H','M'), '', $Code);
                    list($ViolationDate, $ViolationTime) = array_pad(explode('_',$DateTime), 2, null);
                    
                    if(isset($ViolationDate,$ViolationTime) && ($FormattedDate = validateDateFormat($ViolationDate.$ViolationTime, 'YmdHis'))){
                        $Year = $FormattedDate->format('Y');
                        $FineDate = $FormattedDate->format('Y-m-d');
                        $FineTime = $FormattedDate->format('H:i:s');
                        $FineHour = $FormattedDate->format('G');
                        $FineMinute = $FormattedDate->format('i');
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Data violazione assente o non valida.';
                        continue;
                    }
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($VehiclePlate,$FineDate,$FineTime)){
                        $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND VehiclePlate='$VehiclePlate' AND FineDate='$FineDate' AND FineTime='$FineTime'");
                        if(mysqli_num_rows($rs_Fine) > 0){
                            //Se trova il verbale, non prosegue e passa alla prossima riga
                            continue;
                        }
                    }
                    
                    //IMMAGINE
                    if($a_CSVLine['image_name'] != ''){
                        $Documentation = pathinfo($a_CSVLine['image_name'], PATHINFO_FILENAME).'.jpg';
                        
                        if(!file_exists($path.$Documentation)) {
                            $a_ErrorLines[$n_LineCount] = 'Immagine non trovata.';
                            continue;
                        }
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Impossibile determinare il nome dell\'immagine: data di violazione assente.';
                        continue;
                    }
                    
                    //ACCERTATORI
                    $a_AdditionalControllerIds = array();
                    $FirstControllerId = null;
                    $ControllerErrorCode = null;
                    if($a_CSVLine['controller'] != ''){
                        if($FineDate){
                            $a_ControllerCodes = explode('+', $a_CSVLine['controller']);
                            foreach($a_ControllerCodes as $code){
                                if (!is_null($ControllerId = getControllerByCode($a_Controllers, $FineDate, trim($code)))) {
                                    if(!$FirstControllerId){
                                        $FirstControllerId = $ControllerId;
                                    } else $a_AdditionalControllerIds[] = $ControllerId;
                                } else {
                                    $ControllerErrorCode = $code;
                                    break;
                                }
                            }
                            if(isset($ControllerErrorCode)){
                                $a_ErrorLines[$n_LineCount] = "Accertatore non trovato per la seguente matricola: $ControllerErrorCode";
                                continue;
                            }
                        } else {
                            $a_ErrorLines[$n_LineCount] = "Non è possibile determinare gli accertatori: errore nella data di violazione.";
                            continue;
                        }
                    } else {
                        $a_ErrorLines[$n_LineCount] = "Accertatori assenti.";
                        continue;
                    }
                    
                    //RILEVATORE
                    $Detector = null;
                    if($a_CSVLine['detection_serial'] != ''){
                        $DetectorCode = $a_CSVLine['detection_serial'];
                        $rs_Detector = $rs->Select('Detector', "CityId='{$_SESSION['cityid']}' AND Code='$DetectorCode'");
                        $Detector = $rs->getArrayLine($rs_Detector);
                        
                        if($Detector){
                            $DetectorId = $Detector['Id'];
                            $ReasonId = $Detector['ReasonId'];
                            $chk_Tolerance = $Detector['Tolerance'];
                            
                            //VELOCITà
                            if($a_CSVLine['speed'] > 0){
                                $SpeedControl = number_format($a_CSVLine['speed'],2,'.','');
                            } else {
                                $a_ErrorLines[$n_LineCount] = "Velocità rilevata assente o non valida.";
                                continue;
                            }
                            if($a_CSVLine['status'] > 0){
                                $SpeedLimit = number_format($a_CSVLine['status'],2,'.','');
                            } else {
                                $a_ErrorLines[$n_LineCount] = "Limite velocità assente o non valido.";
                                continue;
                            }
                            
                            //ARTICOLO
                            $chk_Tolerance = ($chk_Tolerance>FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;
                            $SpeedExcess = getSpeedExcess($SpeedControl,$SpeedLimit,$chk_Tolerance);
                            
                            $Article = getVArticle($Detector['Id'],$_SESSION['cityid'],$SpeedExcess,$Year);
                            
                            if($Article){
                                $ArticleId = $Article['Id'];
                                $Fee = $Article['Fee'];
                                $MaxFee = $Article['MaxFee'];
                                $ViolationTypeId = $Article['ViolationTypeId'];
                                $AdditionalNight = $Article['AdditionalNight'];
                                
                                if($AdditionalNight){
                                    if($FineHour < FINE_HOUR_START_DAY || $FineHour > FINE_HOUR_END_DAY || ($FineHour == FINE_HOUR_END_DAY && $FineMinute != "00")){
                                        $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                                        $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);
                                    }
                                }
                                
                                //MANCATA CONTESTAZIONE
                                $rs_Reasons = getReasonRs($ReasonId,$_SESSION['cityid'],$ViolationTypeId,$DetectorCode);
                                $Reason = $rs->getArrayLine($rs_Reasons);
                                
                                if($Reason){
                                } else {
                                    $a_ErrorLines[$n_LineCount] = "Mancata contestazione assente.";
                                    continue;
                                }
                            } else {
                                $a_ErrorLines[$n_LineCount] = "Non è possibile determinare la sanzione: articolo assente.";
                                continue;
                            }

                        } else {
                            $a_ErrorLines[$n_LineCount] = 'Rilevatore non trovato per codice import: '.$a_CSVLine['detection_serial'];
                            continue;
                        }
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Codice import o matricola rilevatore non specificati.';
                        continue;
                    }
                    
/////////////////////INSERIMENTO DATI
                    $a_Fine = array(
                        array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                        array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $Year),
                        array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                        array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $FirstControllerId, 'settype' => 'int'),
                        array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                        array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                        array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                        array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'DepartmentId', 'selector' => 'value', 'type' => 'int', 'value' => $DepartmentId),
                        array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleMass, 'settype' => 'int'),
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                    );
                    
                    $FineId = $rs->Insert('Fine', $a_Fine);
                    
                    if ($FineId == 0) {
                        $_SESSION['Message']['Error'] = "Poblemi con l'inserimento del verbale con targa: " . $VehiclePlate;
                        header("location: ".impostaParametriUrl(array('ImportFile' => $ImportFile), "imp_ispeed2022.php"));
                        DIE;
                    }
                    
                    if(!empty($a_AdditionalControllerIds)){
                        foreach($a_AdditionalControllerIds as $additionalControllerId){
                            $a_FineAdditionalController = array(
                                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                                array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=> $additionalControllerId, 'settype'=>'int'),
                            );
                            $rs->Insert('FineAdditionalController',$a_FineAdditionalController);
                        }
                    }
                    
                    $a_FineArticle = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
                        array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
                        array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                        array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                        array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                        array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                        array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                        array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit+$SpeedExcess, 'settype' => 'flt'),
                    );
                    
                    $rs->Insert('FineArticle', $a_FineArticle);
                    
                    
                    $str_Folder = ($CountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                    
                    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
                        mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
                    }
                    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                        mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                    }
                    
                    $a_FineDocumentation = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                    );
                    $rs->Insert('FineDocumentation', $a_FineDocumentation);
                    
                    if(copy($path . $Documentation, $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $Documentation)){
                        unlink($path . $Documentation);
                    } else {
                        $_SESSION['Message']['Error'] = "Poblemi con la copia del documento $Documentation nella cartella del verbale $FineId";
                        header("location: ".impostaParametriUrl(array('ImportFile' => $ImportFile), "imp_ispeed2022.php"));
                        DIE;
                    }
                    
                    $n_CompletedCount++;
                }
                
                if($n_CompletedCount>0){
                    $a_Import = array(
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>3),
                        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$ImportFile),
                        array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_CompletedCount),
                    );
                    
                    $rs->Insert('ImportedFiles',$a_Import);
                    
                    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
                    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){
                        $str_Content = $r_UserMail['CityTitle'].": sono state elaborate n. ".$n_CompletedCount." violazioni.";
                        $a_Mail = array(
                            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                            array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione"),
                            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
                            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
                            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
                        );
                        $rs->Insert('Mail',$a_Mail);
                    }
                }
                
                unlink($path.$ImportFile);
            } else $str_Error = 'File CSV non valido per questa importazione.<br>Colonne previste: '.count($a_CSVColumns).', Colonne identificate: '.count($a_CSVFirstLine);
        } else $str_Error = 'File CSV non valido per questa importazione.<br>La struttura non presenta i seguenti campi: '.implode(', ', $a_CSVMissingColumns);
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
} else $str_Error = "Specificare il file da importare.";

if($str_Error != '') {
    $_SESSION['Message']['Error'] = $str_Error;
} else if(!empty($a_ErrorLines)){
    $str_Warning = 'Non è stato possibile inserire alcuni dati:<br>';
    foreach($a_ErrorLines as $line => $message){
        $str_Warning .= "Riga $line: $message";
    }
    $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: imp_ispeed2022.php".$Filters);
