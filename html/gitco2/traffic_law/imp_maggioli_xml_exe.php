<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_maggioli_xml.php");
require_once(INC."/initialization.php");

/** @var $rs CLS_DB */

$Filters = CheckValue('Filters', 's');

$str_Error = '';
$str_Warning = '';
$a_Errors = array();

$CityId = $_SESSION['cityid'];
$filesPath = IMPORT_FOLDER_MAGGIOLI."/".$CityId;

if(isset($_POST['Extract'])){
    $zipFile = CheckValue('Extract', 's');
    $folderName = pathinfo($zipFile, PATHINFO_FILENAME);
    
    //TODO convertire in funzione come negli altri casi
    if(!is_dir("$filesPath/ESTRATTI/$folderName")){
        $zip = new ZipArchive;
        if ($zip->open("$filesPath/DA_ESTRARRE/$zipFile") === true) {
            $zip->extractTo("$filesPath/ESTRATTI/$folderName");
            $zip->close();
        } else $str_Error .= "Errore nell'apertura del file $zipFile.<br>";
    } else $str_Error .= "La cartella esiste già: $folderName.<br>";
    
} else if(isset($_POST['Archive'])){
    $zipFile = CheckValue('Archive', 's');
    
    if(!archiveZip("$filesPath/DA_ESTRARRE", "$filesPath/ARCHIVIATI", $zipFile)){
        $str_Error .= "Errore nell'archiviazione del file $zipFile.<br>";
    }
} else if(isset($_POST['DeleteFolder'])){
    $folderName = CheckValue('DeleteFolder', 's');

    if(!deleteFolder("$filesPath/ESTRATTI", $folderName)){
        $str_Error .= "Errore nell'eliminazione della cartella $folderName.<br>";
    }
} else if(isset($_POST['Import'])){
    $ImportFile = CheckValue('ImportFile','s');
    
    $LeasingDocTypeId = 43;
    $ViolationDocTypeId = 1;
    $n_CompletedCount = 0;
    
    $importPath = "$filesPath/ESTRATTI/$ImportFile";
    
    $a_ConciliaVehicleTypeMapping = unserialize(IMP_MAGGIOLI_CONCILIA_VEHICLETYPE);
    
    if(!empty($xmlFiles = glob("$importPath/*.xml"))){
        $xmlFile = $xmlFiles[0];
        
        if(is_array($checkedXml = checkAndReadXml($xmlFile))){
            $rs_Controller = $rs->Select('Controller', "CityId='{$CityId}'");
            $a_Controllers = controllersByFieldArray($rs_Controller);
            
            $rs_Country = $rs->SelectQuery('SELECT Id,ISO3,Title FROM Country');
            $a_Countries = array_column($rs->getResults($rs_Country), 'ISO3', 'Id');
            
            foreach($checkedXml as $ln => $data){
                
                $a_Documents = array();
                $a_Trespassers = array();

//////////////////CONTROLLO DATI
                
                //CodiceUID (Riferimento)
                if(!empty($data['CodiceUID'])){
                    $Code = $data['CodiceUID'];
                } else {
                    $a_Errors[$ln] = 'CodiceUID (Riferimento) assente.';
                    continue;
                }
                
                if(isset($Code)){
                    $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND Code='$Code'");
                    if(mysqli_num_rows($rs_Fine) > 0){
                        //Se trova il verbale, non prosegue e passa alla prossima riga
                        continue;
                    }
                }
                
                //Protocollo/Anno/Lettera
                if(!empty($data['NumeroProtocollo'])){
                    $FullProtocol = $data['NumeroProtocollo'];
                    if(preg_match(IMP_MAGGIOLI_FULLPROTOCOL_RGX, $FullProtocol)){
                        list($ExternalProtocol, $ProtocolYear) = explode('/', $FullProtocol);
                    } else {
                        $a_Errors[$ln] = 'Protocollo, anno o lettera non validi.';
                        continue;
                    }
                } else {
                    $a_Errors[$ln] = 'Numero protocollo assente.';
                    continue;
                }
                
                //DOCUMENTI
                if(!empty($data['ImmagineViolazione'])){
                    $imagePath = str_replace('\\', '/', $data['ImmagineViolazione']);
                    if(file_exists("$importPath/$imagePath")) {
                        $a_Documents[$ViolationDocTypeId] = "$importPath/$imagePath";
                    } else {
                        $a_Errors[$ln] = 'Immagine non trovata.';
                        continue;
                    }
                }
                //TODO in realtà questo campo prevederebbe il valore di un'eventuale cartolina di notifica (CARTOLINE/<nomeFile>)
                //e non della comunicazione al locatario di noleggio, che sarebbe prevista in DocumentoNoleggio
                if(!empty($data['ImmagineCartolina'])){
                    $leasingPath = str_replace('\\', '/', $data['ImmagineCartolina']);
                    if(file_exists("$importPath/$leasingPath")) {
                        $a_Documents[$LeasingDocTypeId] = "$importPath/$leasingPath";
                    } else {
                        $a_Errors[$ln] = 'Comunicazione locatario non trovata.';
                        continue;
                    }
                }
                
                //TRASGRESSORI
                if(!empty($data['ProprietarioNominativo']) && !empty($data['TrasgressoreNominativo'])){
                    $a_Trespassers[11] = array(
                        'Name' => $data['TrasgressoreNominativo'],
                        'Address' => $data['TrasgressoreIndirizzo'] ?? '',
                        'City' => $data['TrasgressoreLocalita'] ?? '',
                        'Country' => $data['TrasgressoreNazione'] ?? '',
                        'BornDate' => $data['TrasgressoreDataNascita'] ?? '',
                        'BornPlace' => $data['TrasgressoreLocalitaNascita'] ?? '',
                    );
                    $a_Trespassers[10] = array(
                        'Name' => $data['ProprietarioNominativo'],
                        'Address' => $data['ProprietarioIndirizzo'] ?? '',
                        'City' => $data['ProprietarioLocalita'] ?? '',
                        'Country' => $data['ProprietarioNazione'] ?? '',
                        'BornDate' => $data['ProprietarioDataNascita'] ?? '',
                        'BornPlace' => $data['ProprietarioLocalitaNascita'] ?? '',
                    );
                }
                else if(!empty($data['ProprietarioNominativo'])){
                    $a_Trespassers[1] = array(
                        'Name' => $data['ProprietarioNominativo'],
                        'Address' => $data['ProprietarioIndirizzo'] ?? '',
                        'City' => $data['ProprietarioLocalita'] ?? '',
                        'Country' => $data['ProprietarioNazione'] ?? '',
                        'BornDate' => $data['ProprietarioDataNascita'] ?? '',
                        'BornPlace' => $data['ProprietarioLocalitaNascita'] ?? '',
                    );
                }
                
                //NAZIONE TARGA
                if(!empty($data['NazioneTarga'])){
                    $Country = $rs->getArrayLine($rs->Select('Country', "ISO3='{$data['NazioneTarga']}'"));
                    if($Country){
                        $FineCountryId = $Country['Id'];
                        $VehicleCountry = $Country['Title'];
                    } else {
                        $a_Errors[$ln] = "Nazione non riconosciuta sulla banca dati: {$data['NazioneTarga']}.";
                        continue;
                    }
                } else {
                    $FineCountryId = 'ZZZZ';
                    $VehicleCountry = 'Da assegnare';
                }
                
                //TARGA
                if(!empty($data['Targa'])){
                    $VehiclePlate = $data['Targa'];
                } else {
                    $a_Errors[$ln] = 'Targa assente.';
                    continue;
                }
                
                //TIPO VEICOLO
                if(!empty($data['TipoVeicoloConcilia']) && isset($a_ConciliaVehicleTypeMapping[$data['TipoVeicoloConcilia']])){
                    $VehicleTypeId = $a_ConciliaVehicleTypeMapping[$data['TipoVeicoloConcilia']];
                } else {
                    $a_Errors[$ln] = 'Tipo di veicolo non riconosciuto.';
                    continue;
                }
                
                //DATA E ORA VIOLAZIONE
                if(($dt_FineDate = validateDateFormat($data['DataViolazione'], 'd/m/Y')) &&
                   ($dt_FineTime = validateDateFormat($data['OraViolazione'], 'H:i'))){
                    $FineDate = $dt_FineDate->format('Y-m-d');
                    $FineTime = $dt_FineTime->format('H:i');
                    $FineHour = $dt_FineTime->format('G');
                    $FineMinute = $dt_FineTime->format('i');
                    $Year = $dt_FineDate->format('Y');
                } else {
                    $a_Errors[$ln] = 'Data/ora verbale assente o non valida.';
                    continue;
                }
                
                //DATA E ORA ACCERTAMENTO
                $ControllerDate = null;
                $ControllerTime = null;
                if(!empty($data['DataVerbale'])){
                    if($dt_ControllerDate = validateDateFormat($data['DataVerbale'], 'd/m/Y')){
                        $ControllerDate = $dt_ControllerDate->format('Y-m-d');
                    } else {
                        $a_Errors[$ln] = 'Data di accertamento non valida.';
                        continue;
                    }
                } 
                if(!empty($data['OraVerbale'])){
                    if($dt_ControllerTime = validateDateFormat($data['OraVerbale'], 'H:i')){
                        $ControllerTime = $dt_ControllerTime->format('H:i');
                    } else {
                        $a_Errors[$ln] = 'Ora di accertamento non valida.';
                        continue;
                    }
                } 
                
                //LUOGO
                if(!empty(trim($data['LuogoInfrazione']))){
                    $Address = 
                        trim($data['LuogoInfrazione']).
                        (!empty($data['DirezioneVeicolo']) ? ' - Dir. '.trim($data['DirezioneVeicolo']) : '').
                        (!empty($data['AltezzaVeicolo']) ? ' - Altezza: '.trim($data['AltezzaVeicolo']) : '').
                        (!empty($data['CodiceQuartiere']) ? ' - C.Q: '.trim($data['CodiceQuartiere']) : '');
                } else {
                    $a_Errors[$ln] = 'Luogo infrazione assente.';
                    continue;
                }
                
                //ACCERTATORI
                $a_ControllerCodes = array_filter(preg_grep_keys('/MatricolaAgente/', $data));
                $a_AdditionalControllerIds = array();
                $FirstControllerId = null;
                $ControllerErrorCode = null;
                if(!empty($a_ControllerCodes)){
                    if(isset($FineDate)){
                        foreach($a_ControllerCodes as $code){
                            if (!is_null($ControllerId = getControllerByCode($a_Controllers, $FineDate, trim($code)))) {
                                if(!isset($FirstControllerId)){
                                    $FirstControllerId = $ControllerId;
                                } else $a_AdditionalControllerIds[] = $ControllerId;
                            } else {
                                $ControllerErrorCode = $code;
                                break;
                            }
                        }
                        if(isset($ControllerErrorCode)){
                            $a_Errors[$ln] = "Accertatore non trovato per la seguente matricola: $ControllerErrorCode";
                            continue;
                        }
                    } else {
                        $a_Errors[$ln] = "Impossibile determinare gli accertatori: errore nella data di violazione.";
                        continue;
                    }
                } else {
                    $a_Errors[$ln] = "Accertatori assenti.";
                    continue;
                }
                
                //RILEVATORE
                $SpeedLimit = 0.00;
                $SpeedControl = 0.00;
                $Speed = 0.00;
                $DetectorId = 0;
                $DetectorCode = null;
                $ReasonId = null;
                if(!empty($data['CodiceVeloxConcilia'])){
                    $DetectorCode = $data['CodiceVeloxConcilia'];
                    $rs_Detector = $rs->Select('Detector', "CityId='{$CityId}' AND Code='$DetectorCode'");
                    $r_Detector = $rs->getArrayLine($rs_Detector);
                    
                    if($r_Detector){
                        $DetectorId = $r_Detector['Id'];
                        $ReasonId = $r_Detector['ReasonId'];
                        
                        //VELOCITà
                        $SpeedLimit = $data['LimiteVelocita'];
                        $SpeedControl = $data['VelocitaRilevata'];
                        $Speed = round($data['VelocitaDifferenza']);
                        
                        if(empty($SpeedLimit) || empty($SpeedControl) || empty($Speed)){
                            $a_Errors[$ln] = "Una o più velocità sono assenti.";
                            continue;
                        }
                        
                        //TARATURA
                        if(!empty($data['DataInizioValiditaTaratura'])){
                            $rs_DetectorRatification = $rs->Select('DetectorRatification', "DetectorId={$r_Detector['Id']} AND ((FromDate <= '".DateInDB($FineDate)."' AND (ToDate >= '".DateInDB($FineDate)."' OR ToDate IS NULL)) OR (FromDate IS NULL AND ToDate IS NULL))");
                            $DetectorRatification = $rs->getArrayLine($rs_DetectorRatification);
                            
                            if(!$DetectorRatification){
                                $a_Errors[$ln] = "Il rilevatore prevede una validità di taratura non registrata a sistema. Data inizio validità: {$data['DataInizioValiditaTaratura']}";
                                continue;
                            }
                        }
                    } else {
                        $a_Errors[$ln] = "Rilevatore non trovato per codice import: {$data['CodiceVeloxConcilia']}";
                        continue;
                    }
                }
                
                //ARTICOLI
                $a_Articles = array_filter(preg_grep_keys('/ArticoloConcilia/', $data));
                $a_AdditionalArticles = array();
                $a_FirstArticle = array();
                $ViolationTypeId = 0;
                $ArticleError = null;
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
                                
                                if(empty($a_FirstArticle)){
                                    $a_FirstArticle = array(
                                        'ArticleId' => $Article['Id'],
                                        'Fee' => $Fee,
                                        'MaxFee' => $MaxFee,
                                        'ViolationTypeId' => $ViolationTypeId
                                    );
                                } else {
                                    $a_AdditionalArticles[] = array(
                                        'ArticleId' => $Article['Id'],
                                        'Fee' => $Fee,
                                        'MaxFee' => $MaxFee
                                    );
                                }
                            } else {
                                $ArticleError = $article;
                                break;
                            }
                        }
                        if(isset($ArticleError)){
                            $a_Errors[$ln] = "Articolo non trovato per l'anno $Year: $ArticleError";
                            continue;
                        }
                    } else {
                        $a_Errors[$ln] = "Impossibile determinare gli articoli: anno verbale assente.";
                        continue;
                    }
                } else {
                    $a_Errors[$ln] = "Articoli assenti.";
                    continue;
                }
                
                //MANCATA CONTESTAZIONE
                $rs_Reasons = getReasonRs($ReasonId, $CityId, $ViolationTypeId, $DetectorCode);
                $Reason = $rs->getArrayLine($rs_Reasons);
                if($Reason){
                    $ReasonId = $Reason['Id'];
                } else {
                    $a_Errors[$ln] = "Mancata contestazione assente.";
                    continue;
                }
                
//////////////////INSERIMENTO DATI

                $VehicleBrand = $data['MarcaVeicolo'] ?? '';
                $VehicleModel = $data['ModelloVeicolo'] ?? '';
                
                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1),
                    array('field' => 'ExternalProtocol', 'selector' => 'value', 'type' => 'int', 'value' => $ExternalProtocol, 'settype' => 'int'),
                    array('field' => 'ExternalYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                    array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                    array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                    array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                    array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => $ControllerDate ?? null),
                    array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'time', 'value' => $ControllerTime ?? null),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $FirstControllerId, 'settype' => 'int'),
                    array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                    array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                    array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                    array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                    array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand),
                    array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel),
                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $FineCountryId),
                    array('field' => 'DepartmentId', 'selector' => 'value', 'type' => 'int', 'value' => 0),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                );
                
                $FineId = $rs->Insert('Fine', $a_Fine);
                
                if ($FineId == 0) {
                    $_SESSION['Message']['Error'] = "Poblemi con l'inserimento del verbale con targa: " . $VehiclePlate;
                    header("location: ".impostaParametriUrl(array('ImportFile' => $ImportFile), "imp_maggioli_xml.php"));
                    DIE;
                }
                
                if(!empty($a_Trespassers)){
                    foreach($a_Trespassers as $TrespasserTypeId => $trespasserData){
                        $TrespasserId = 0;
                        
                        //Controlla se esiste già un trasgressore con gli stessi dati ancora da sanare, se non esiste,
                        //Quindi presumibilmente sono stati corretti, guarda nello storico dei trasgressori.
                        //Se si trova, viene usato il TrespasserId trovato, altrimenti si genera un nuovo trasgressore
                        $Trespasser = $rs->getArrayLine($rs->Select('Trespasser',
                            "CustomerId='$CityId' AND
                            Surname='".mysqli_real_escape_string($rs->conn, $trespasserData['Name'])."' AND 
                            Address='".mysqli_real_escape_string($rs->conn, $trespasserData['Address'])."'
                            "));
                        if(!$Trespasser){
                            $TrespasserHistory = $rs->getArrayLine($rs->Select('TrespasserHistory',
                            "Surname='".mysqli_real_escape_string($rs->conn, $trespasserData['Name'])."' AND
                            Address='".mysqli_real_escape_string($rs->conn, $trespasserData['Address'])."'
                            "));
                            
                            $TrespasserId = $TrespasserHistory['TrespasserId'] ?? 0;
                        } else {
                            $TrespasserId = $Trespasser['Id'];
                        }
                        
                        if($TrespasserId == 0){
                            $ZoneId = $LanguageId = 0;
                            
                            $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='$CityId'");
                            $Code = mysqli_fetch_array($rs_Code)['Code'];
                            //Recupera il codice catastale della nazione e si assicura che nel caso non sia presente e non ci siano corrispondenze, equivalga a stringa vuota
                            $TrespasserCountryId = !empty($trespasserData['Country']) ? (array_search($trespasserData['Country'], $a_Countries) ?: '') : '';
                            
                            if(!empty($TrespasserCountryId)){
                                $TrespasserCountry = $rs->getArrayLine($rs->Select('Country', "Id='$TrespasserCountryId'"));
                                if($TrespasserCountry){
                                    $ZoneId = $TrespasserCountry['ZoneId'];
                                    $LanguageId = $TrespasserCountry['LanguageId'];
                                }
                            }
                            
                            $Notes = implode(', ', array(
                                'Indirizzo: '.($trespasserData['Address']),
                                'Localita: '.$trespasserData['City'],
                                'Nazione: '.$trespasserData['Country'],
                                'Data Nascita: '.$trespasserData['BornDate'],
                                'Localita Nascita: '.$trespasserData['BornPlace'],
                            ));
                            
                            $a_Trespasser = array(
                                array('field'=>'Code','selector'=>'value','type'=>'int', 'value'=>$Code, 'settype'=>'int'),
                                array('field'=>'CustomerId','selector'=>'value','type'=>'str','value'=>$CityId),
                                array('field'=>'Surname','selector'=>'value','type'=>'str','value'=>$trespasserData['Name']),
                                array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$trespasserData['City'].' - '.$trespasserData['Address']),
                                array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$TrespasserCountryId),
                                array('field'=>'ZoneId','selector'=>'value','type'=>'int','value'=>$ZoneId,'settype'=>'int'),
                                array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
                                array('field'=>'BornDate','selector'=>'value','type'=>'date','value'=>$trespasserData['BornDate']),
                                array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>'Dati importati da Maggioli. Da verificare con visura. '.$Notes),
                                array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
                                array('field'=>'VersionDate','selector'=>'value','type'=>'str','value'=>date("Y-m-d")),
                                array('field'=>'DataSourceId','selector'=>'value','type'=>'int','value'=>23,'settype'=>'int'),
                                array('field'=>'DataSourceDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                            );
                            
                            $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);
                        }
                        
                        $a_FineTrespasser = array(
                            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId,'settype'=>'int'),
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId,'settype'=>'int'),
                            array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Dati importati da Maggioli.'),
                            array('field'=>'AssociatedOnImport','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
                            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                        );
                        if($TrespasserTypeId == 11 || $TrespasserTypeId == 1){
                            $OwnerAdditionalFee =
                            str_replace(',', '.', str_replace('.', '', $data['SpeseNotifica'] ?? 0)) +
                            str_replace(',', '.', str_replace('.', '', $data['SpeseGestione'] ?? 0)) +
                            str_replace(',', '.', str_replace('.', '', $data['SpeseProcedurali'] ?? 0));
                            
                            $a_FineTrespasser[] = array('field'=>'OwnerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$OwnerAdditionalFee);
                            
                            //DATA COMUNICAZIONE DATI
                            if($TrespasserTypeId == 11 && ($dt_ReceiveDate = validateDateFormat($data['DataRispostaAutonoleggio'], 'd/m/Y'))){
                                $ReceiveDate = $dt_ReceiveDate->format('Y-m-d');
                                $a_FineTrespasser[] = array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>$ReceiveDate);
                            }
                        }
                        
                        $rs->Insert('FineTrespasser', $a_FineTrespasser);
                    }
                    
                    //Sono presenti dei trasgressori, lo stato del verbale passa da 1 (Preinserimento) a 10 (Completato)
                    $rs->Update('Fine', array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 10)
                    ), "Id=$FineId");
                }

                foreach($a_AdditionalControllerIds as $additionalControllerId){
                    $a_FineAdditionalController = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                        array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=> $additionalControllerId, 'settype'=>'int'),
                    );
                    $rs->Insert('FineAdditionalController',$a_FineAdditionalController);
                }
                
                $a_FineArticle = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $a_FirstArticle['ArticleId'], 'settype' => 'int'),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                    array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
                    array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_FirstArticle['Fee'], 'settype' => 'flt'),
                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_FirstArticle['MaxFee'], 'settype' => 'flt'),
                    array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                    array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                    array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                    array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                );
                
                $rs->Insert('FineArticle', $a_FineArticle);
                
                if(!empty($a_AdditionalArticles)){
                    foreach($a_AdditionalArticles as $index => $additionalArticle){
                        $a_FineAdditionalArticle = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$additionalArticle['ArticleId'],'settype'=>'int'),
                            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                            array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$additionalArticle['Fee'],'settype'=>'flt'),
                            array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$additionalArticle['MaxFee'],'settype'=>'flt'),
                            array('field'=>'ArticleOrder','selector'=>'value','type'=>'int','value'=>$index + 2,'settype'=>'int'),
                        );
                        
                        $rs->Insert('FineAdditionalArticle', $a_FineAdditionalArticle);
                    }
                }
                
                foreach($a_Documents as $DocumentationTypeId => $DocumentationPath){
                    if($DocumentationTypeId == 1){
                        $str_Folder = ($FineCountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                    } else {
                        $str_Folder = ($FineCountryId == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;
                    }
                    
                    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
                        mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
                    }
                    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                        mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                    }
                    
                    $Documentation = pathinfo($DocumentationPath, PATHINFO_BASENAME);
                    
                    $a_FineDocumentation = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                    );
                    
                    $rs->Insert('FineDocumentation', $a_FineDocumentation);
                    
                    if(!copy($DocumentationPath, $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $Documentation)){
                        $_SESSION['Message']['Error'] = "Poblemi con la copia del documento $Documentation nella cartella del verbale $FineId";
                        header("location: ".impostaParametriUrl(array('ImportFile' => $ImportFile), "imp_maggioli_xml.php"));
                        DIE;
                    }
                }
                
                $n_CompletedCount++;
            }
            
//////////////INSERIMENTO RECORD IMPORTAZIONE

            if($n_CompletedCount>0){
                $a_Import = array(
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                    array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'Type','selector'=>'value','type'=>'int','value'=>7),
                    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$ImportFile),
                    array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_CompletedCount),
                );
                
                $rs->Insert('ImportedFiles',$a_Import);
                
                $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
                while($r_UserMail = $rs->getArrayLine($rs_UserMail)){
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
        } else $str_Error .= "Impossibile leggere il file .xml: $checkedXml";
    } else $str_Error .= "Nessun file .xml trovato all'interno della cartella: $ImportFile";
}

if($str_Error != '') {
    $_SESSION['Message']['Error'] = $str_Error;
} else if(!empty($a_Errors)){
    $str_Warning = 'Non è stato possibile inserire alcuni dati:<br>';
    foreach($a_Errors as $line => $message){
        $str_Warning .= "Riga $line: $message<br>";
    }
    $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: imp_maggioli_xml.php".$Filters);
