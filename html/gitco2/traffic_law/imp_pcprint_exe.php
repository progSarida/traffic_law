<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_pcprint.php");
require_once(INC."/initialization.php");

ini_set('max_execution_time', 3000);

$ImportFile = CheckValue('ImportFile', 's');
$Filters = CheckValue('Filters', 's');
$CompressImages = CheckValue('CompressImages', 's');

$CityId = $_SESSION['cityid'];
$StatusTypeId = 0;
$DocumentationTypeId = 1;
$VehicleTypeId = 1;

$path = PUBLIC_FOLDER."/_VIOLATION_/$CityId";

$str_Error = '';
$n_LineCount = 0;
$a_IndexedLines = array();
$a_FileColumns = array();
$n_ContFine = 0;

$b_IsSpeed = $b_IsTraffic = false;

if($CompressImages > 0 && !class_exists('Imagick')){
    $_SESSION['Message']['Error'] = "Impossibile comprimere immagini. Contattare l'assistenza.";
    header("location: ".impostaParametriUrl(array('ImportFile' => $ImportFile, 'CompressImages' => $CompressImages), "imp_pcprint.php".$Filters));
    DIE;
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
                    $b_SkipNext = false;
                    
                    for($i = 1; $i <= count($a_IndexedLines); $i++){
                        
                        if($b_SkipNext){
                            $b_SkipNext = false;
                            continue;
                        }
                        
                        $a_CSVLine = $a_IndexedLines[$i-1];
                        if(($i % 2 != 0 && $b_IsTraffic) || $b_IsSpeed){
                            $n_LineCount++;
                            $SpeedExcess=0;
                            $Tolerance=0;
                            $Locality = '';
                            $Address = '';
                            $ControllerId = null;
                            $VehiclePlate = '';
                            $a_Documents = array();
                            
                            //DATA E ORA VIOLAZIONE
                            if(($dt_FineDate = validateDateFormat($a_CSVLine['DATA'], 'd/m/Y')) &&
                                ($dt_FineTime = validateDateFormat($a_CSVLine['ORA'], 'H:i:s'))){
                                    $FineDate = $dt_FineDate->format('d/m/Y');
                                    $FineTime = $dt_FineTime->format('H:i:s');
                                    $FineHour = $dt_FineTime->format('G');
                                    $FineMinute = $dt_FineTime->format('i');
                                    $ProtocolYear = $dt_FineDate->format('Y');
                            } else {
                                if($b_IsTraffic) $b_SkipNext = true;
                                $a_ErrorLines[$n_LineCount] = 'Data/ora violazione assente.';
                                continue;
                            }
                            
                            //TARGA
                            if(preg_match( '/^[a-zA-Z0-9]+$/', $a_CSVLine['TARGA']) >= 1){
                                $VehiclePlate = $a_CSVLine['TARGA'];
                            }
                            
                            //RILEVATORE
                            if($a_CSVLine['MATRICOLA_FT1D'] != ''){
                                $r_Detector = $rs->getArrayLine($rs->Select('Detector', "CityId='$CityId' AND Code='{$a_CSVLine['MATRICOLA_FT1D']}'"));
                                
                                if(!empty($r_Detector)){
                                    $DetectorId = $r_Detector['Id'];
                                    $ReasonId= $r_Detector['ReasonId'];
                                    $Tolerance = $r_Detector['Tolerance'];
                                } else {
                                    if($b_IsTraffic) $b_SkipNext = true;
                                    $a_ErrorLines[$n_LineCount] = "Rilevatore non trovato per codice: {$a_CSVLine['MATRICOLA_FT1D']}";
                                    continue;
                                }
                            } else {
                                if($b_IsTraffic) $b_SkipNext = true;
                                $a_ErrorLines[$n_LineCount] = 'Codice rilevatore assente.';
                                continue;
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
                                    $Locality = $r_Locality['Id'];
                                } else {
                                    if($b_IsTraffic) $b_SkipNext = true;
                                    $a_ErrorLines[$n_LineCount] = "Località non trovata.";
                                    continue;
                                }
                                
                                if(empty($Address)){
                                    if($b_IsTraffic) $b_SkipNext = true;
                                    $a_ErrorLines[$n_LineCount] = "Indirizzo assente.";
                                    continue;
                                }
                            } else {
                                if($b_IsTraffic) $b_SkipNext = true;
                                $a_ErrorLines[$n_LineCount] = "Indirizzo e località assenti.";
                                continue;
                            }
                            
                            //ACCERTATORE
                            if($a_CSVLine["OPERATORE"] != ''){
                                if (isset($FineDate)) {
                                    $ControllerId = getControllerByField($a_Controllers, $FineDate, trim($a_CSVLine["OPERATORE"]));
                                }
                                if(empty($ControllerId)){
                                    if($b_IsTraffic) $b_SkipNext = true;
                                    $a_ErrorLines[$n_LineCount] = "Accertatore non trovato.";
                                    continue;
                                }
                            } else {
                                if($b_IsSpeed) $b_SkipNext = true;
                                $a_ErrorLines[$n_LineCount] = "Accertatore assente.";
                                continue;
                            }
                            
                            
                            if($b_IsSpeed){
                                //LIMITE
                                if($a_CSVLine["LIMITE"] != '' && $a_CSVLine["VELOCITA'"] != ''){
                                    $Tolerance = ($Tolerance>FINE_TOLERANCE) ? $Tolerance : FINE_TOLERANCE;
                                    $SpeedLimit = (int) $a_CSVLine["LIMITE"];
                                    $SpeedControl = (int) $a_CSVLine["VELOCITA'"];
                                    $Speed = $SpeedControl - $Tolerance;
                                    $SpeedExcess=getSpeedExcess($SpeedControl, $SpeedLimit, $Tolerance);
                                    
                                    if($SpeedExcess <= 0){
                                        continue;
                                    }
                                } else {
                                    $a_ErrorLines[$n_LineCount] = 'Limite o velocità assenti.';
                                    continue;
                                }
                                
                                //IMMAGINE
                                if(!empty($a_CSVLine['NOME_FILE'])){
                                    if(file_exists("$path/{$a_CSVLine['NOME_FILE']}")) {
                                        $a_Documents[] = $a_CSVLine['NOME_FILE'];
                                    } else {
                                        $a_ErrorLines[$n_LineCount] = 'Immagine non trovata.';
                                        continue;
                                    }
                                }
                            }
                            
                            if($b_IsTraffic){
                                //TEMPO PRIMO FOTOGRAMMA
                                if($a_CSVLine["TEMPO_DAL_ROSSO"] != ''){
                                    $TliFirst = $a_CSVLine["TEMPO_DAL_ROSSO"];
                                } else {
                                    $b_SkipNext = true;
                                    $a_ErrorLines[$n_LineCount] = 'Manca tempo primo fotogramma.';
                                    continue;
                                }
                                
                                //PRIMO FOTOGRAMMA
                                if(!empty($a_CSVLine['NOME_FILE'])){
                                    if(file_exists("$path/{$a_CSVLine['NOME_FILE']}")) {
                                        $a_Documents[] = $a_CSVLine['NOME_FILE'];
                                    } else {
                                        $b_SkipNext = true;
                                        $a_ErrorLines[$n_LineCount] = 'Primo fotogramma non trovato.';
                                        continue;
                                    }
                                }
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
                                    $ViolationTypeId = $r_Article['ViolationTypeId'];
                                    $ArticleId = $r_Article['Id'];
                                    
                                    if($AdditionalNight==1){
                                        if($FineHour < FINE_HOUR_START_DAY || $FineHour > FINE_HOUR_END_DAY || ($FineHour == FINE_HOUR_END_DAY && $FineMinute != "00")){
                                            $Fee = $Fee + ceil(($Fee/FINE_NIGHT)*100)/100;
                                            $MaxFee = $MaxFee + ceil(($MaxFee/FINE_NIGHT)*100)/100;
                                        }
                                    }
                                } else {
                                    if($b_IsTraffic) $b_SkipNext = true;
                                    $a_ErrorLines[$n_LineCount] = 'Articolo non trovato sul rilevatore.';
                                    continue;
                                }
                            } else {
                                if($b_IsTraffic) $b_SkipNext = true;
                                $a_ErrorLines[$n_LineCount] = 'Dati non sufficenti per determinare sanzione.';
                                continue;
                            }
                            
                        } else if($b_IsTraffic){
                            //TEMPO SECONDO FOTOGRAMMA
                            if($a_CSVLine["TEMPO_DAL_ROSSO"] != ''){
                                $TliSecond = $a_CSVLine["TEMPO_DAL_ROSSO"];
                            } else {
                                $a_ErrorLines[$n_LineCount] = 'Manca tempo secondo fotogramma.';
                                continue;
                            }
                            
                            //SECONDO FOTOGRAMMA
                            if(!empty($a_CSVLine['NOME_FILE'])){
                                if(file_exists("$path/{$a_CSVLine['NOME_FILE']}")) {
                                    $a_Documents[] = $a_CSVLine['NOME_FILE'];
                                } else {
                                    $a_ErrorLines[$n_LineCount] = 'Manca secondo fotogramma.';
                                    continue;
                                }
                            }
                        }
                        
/////////////////////INSERIMENTO DATI
                        if(($i % 2 == 0 && $b_IsTraffic) || $b_IsSpeed){
                            $Code = impPcprintGetCode($a_Documents[0], $ProtocolYear);
                            
                            //CONTROLLO VERBALI GIà ESISTENTI
                            $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND Code='$Code'");
                            if(mysqli_num_rows($rs_Fine) > 0){
                                continue;
                            }
                            
                            $rs->Start_Transaction();
                            
                            $a_Fine = array(
                                array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                                array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                                array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                                array('field' => 'FineTime', 'selector' => 'value', 'type' => 'str', 'value' => $FineTime),
                                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                                array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $Locality),
                                array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                                array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                                array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                                array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => "Da assegnare"),
                                array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => 'ZZZZ'),
                                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                                array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
                                array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => "Caricamento da file"),
                            );
                            
                            $FineId = $rs->Insert('Fine', $a_Fine);
                            
                            $a_FineArticle = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
                                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                                array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
                                array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
                                array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                                array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                                array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                                array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                                array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                                array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                                array('field' => 'TimeTLightFirst', 'selector' => 'value', 'type' => 'int', 'value' => $TliFirst, 'settype' => 'int'),
                                array('field' => 'TimeTLightSecond', 'selector' => 'value', 'type' => 'int', 'value' => $TliSecond, 'settype' => 'int'),
                                
                            );
                            
                            $rs->Insert('FineArticle', $a_FineArticle);
                            
                            $str_Folder = FOREIGN_VIOLATION . "/" . $CityId . "/" . $FineId;
                            
                            foreach($a_Documents as $doc){
                                if (!is_dir($str_Folder)) {
                                    mkdir($str_Folder, 0750, true);
                                    chmod($str_Folder, 0750);
                                }
                                
                                if ($CompressImages) {
                                    try{
                                        $img = new Imagick("$path/$doc");
                                        $width = intval($img->getimagewidth() / 3);
                                        $height = intval($img->getimageheight() / 3);
                                        $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                                        $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                                        $img->setImageCompressionQuality(40);
                                        $img->stripImage();
                                        $img->writeImage("$str_Folder/$doc");
                                        $img->destroy();
                                        
                                        unlink("$path/$doc");
                                    } catch (ImagickException $e) {
                                        $a_ErrorLines[$n_LineCount] = "Errore nella compressione e copia dell'immagine $doc.";
                                        continue;
                                    }
                                } else {
                                    if(!copy("$path/$doc", "$str_Folder/$doc")){
                                        $a_ErrorLines[$n_LineCount] = "Errore nella copia dell'immagine $doc.";
                                        continue;
                                    } else {
                                        unlink("$path/$doc");
                                    }
                                }
                                
                                $a_FineDocumentation = array(
                                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $doc),
                                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                                );
                                $rs->Insert('FineDocumentation', $a_FineDocumentation);
                                
                            }
                            
                            $rs->End_Transaction();
                            
                            $n_ContFine++;
                            
                            $TliFirst = $TliSecond = 0;
                        }
                        
                    }
                    
                    if(empty($a_ErrorLines)){
                        unlink("$path/$ImportFile");
                    }
                }
            } else $str_Error = 'File CSV non valido per questa importazione.';
        } else $str_Error = "Non è stato possibile identificare le colonne nel file: $ImportFile. Controllare la struttura";
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";

    if($n_ContFine > 0){
        $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM " . MAIN_DB . ".V_UserCity WHERE UserLevel>=3 AND CityId='" . $CityId . "'");
        while ($r_UserMail = $rs->getArrayLine($rs_UserMail)) {
            
            $str_Content = $r_UserMail['CityTitle'] . ": sono stati elaborati n. " . $n_ContFine . " preinserimenti";
            
            $a_Mail = array(
                array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
                array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Nuova importazione"),
                array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
                array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_UserMail['UserId'], 'settype' => 'int'),
                array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
            );
            $rs->Start_Transaction();
            $rs->Insert('Mail', $a_Mail);
            $rs->End_Transaction();
        }
    }
}

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

header("location: ".impostaParametriUrl(array('CompressImages' => $CompressImages), "imp_pcprint.php".$Filters));
