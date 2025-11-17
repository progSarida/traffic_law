<?php
require_once ("_path.php");
require_once (INC."/parameter.php");
require_once (CLS."/cls_db.php");
require_once (CLS."/importazioni/FileImportazione.php");
require_once (INC."/function.php");
require_once (INC."/function_import.php");

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

$rs = new CLS_DB();
$rs->SetCharset('utf8');

$nomeFile = $_GET['nomeFile'] ?? '';

$tipiVeicolo = array(
    TipiVeicolo::AUTOCARRO => 4,
    TipiVeicolo::AUTOBUS => 8,
    TipiVeicolo::FURGONE => 3,
    TipiVeicolo::AUTOVEICOLO => 1,
    TipiVeicolo::MOTOVEICOLO => 2,
);

$tipiDpcumento = array(
    TipiDocumento::FOTOGRAMMA => 1,
);

if(!empty($nomeFile)){
    $fileImportazione = new FileImportazione(VIOLATION_FOLDER);
    $fileImportazione->apriImportazioneInLettura($nomeFile);
    $datiLetti = $fileImportazione->leggi();
    $percorsoDocumenti = $fileImportazione->getPercorsoDocumenti();
    $fileImportazione->chiudiImportazione();
    
    $rs->Start_Transaction();
    
    if(!empty($datiLetti)){
        foreach($datiLetti as $dati){
            $articoli = $dati->getArticoli() ?? array();
            $documenti = $dati->getDocumenti() ?? array();
            
            if(!empty($articoli)){
                $n_Articoli = 0;
                $cityId = $dati->getCitta();
                $dataViolazione = DateTime::createFromFormat("Y-m-d", $dati->getDataViolazione());
                $annoViolazione = $dataViolazione->format('Y');
                
                $rs_VehicleCountry = $rs->Select("Country", "Id='{$dati->getNazioneTarga()}'");
                $r_VehicleCountry = $rs->getArrayLine($rs_VehicleCountry);
                
                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $dati->getRiferimento()),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $cityId),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 0),
                    array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $annoViolazione),
                    array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $dataViolazione->format('Y-m-d')),
                    array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $dati->getOraViolazione()),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $dati->getLuogoInfrazione()),
                    array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $dati->getLocalita()),
                    array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $tipiVeicolo[$dati->getTipoVeicolo()] ?? 0, 'settype' => 'int'),
                    array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $dati->getTarga()),
                    array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => ""),
                    array('field' => 'VehicleColor', 'selector' => 'value', 'type' => 'str', 'value' => ""),
                    array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $r_VehicleCountry['Title']),
                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $dati->getNazioneTarga()),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => "importazioneAutomatica"),
                );
                
                $FineId = $rs->Insert("Fine", $a_Fine);
                
                foreach($articoli as $articolo){
                    /** @var Articolo $articolo */
                    $n_Articoli++;
                    $rs_Article = $rs->Select('V_Article', "CityId='$cityId' AND Year={$annoViolazione} AND Article={$articolo->getArticolo()} AND Paragraph='{$articolo->getComma()}' AND Letter='{$articolo->getLettera()}'");
                    $r_Article = $rs->getArrayLine($rs_Article);
                    
                    $rs_reason = getReasonRs(null, $cityId, $r_Article['ViolationTypeId'], 0);
                    $r_Reason = mysqli_fetch_array($rs_reason);
                    
                    if($n_Articoli == 1){
                        $a_FineArticle = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Article['Id'], 'settype' => 'int'),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $cityId),
                            array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                            array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => 0.00, 'settype' => 'flt'),
                            array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => 0.00, 'settype' => 'flt'),
                            array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => 0.00, 'settype' => 'flt'),
                            array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Reason['Id'], 'settype' => 'int'),
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_Article['Fee'], 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_Article['MaxFee'], 'settype' => 'flt'),
                            array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Article['ViolationTypeId'], 'settype' => 'int'),
                            array('field' => 'ArticleNumber', 'selector' => 'value', 'type' => 'int', 'value' => count($articoli), 'settype' => 'int')
                        );
                        
                        if (!empty($articolo->getDataScadenzaAssicurazioneRevisione())) {
                            $a_FineArticle[] = array('field' => 'ExpirationDate', 'selector' => 'value', 'type' => 'date', 'value' => $articolo->getDataScadenzaAssicurazioneRevisione());
                        }
                        
                        $rs->Insert("FineArticle", $a_FineArticle);
                    } else {
                        $a_FineAdditionalArticle = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Article['Id'], 'settype' => 'int'),
                            array('field' => 'ArticleOrder', 'selector' => 'value', 'type' => 'int', 'value' => $n_Articoli, 'settype' => 'int'),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' =>  $cityId),
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_Article['Fee'], 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_Article['MaxFee'], 'settype' => 'flt'),
                        );
                        
                        if (!empty($articolo->getDataScadenzaAssicurazioneRevisione())) {
                            $a_FineArticle[] = array('field' => 'ExpirationDate', 'selector' => 'value', 'type' => 'date', 'value' => $articolo->getDataScadenzaAssicurazioneRevisione());
                        }
                        
                        $rs->Insert("FineAdditionalArticle", $a_FineAdditionalArticle);
                    }
                }
                
                foreach($documenti as $documento){
                    /** @var Documento $documento */
                    $nomeDocumento = $documento->getNome();
                    $a_FineDocumentation = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $nomeDocumento),
                        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $tipiDpcumento[$documento->getTipo()], 'settype' => 'int'),
                        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => "importazioneAutomatica"),
                    );
                    
                    $rs->Insert("FineDocumentation", $a_FineDocumentation);
                    
                    $cartella = ($dati->getNazioneTarga() == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
                    if (!is_dir("$cartella/$cityId")) {
                        mkdir("$cartella/$cityId", 0770);
                        chmod("$cartella/$cityId", 0770);
                    }
                    if (!is_dir("$cartella/$cityId/$FineId")) {
                        mkdir("$cartella/$cityId/$FineId", 0770);
                        chmod("$cartella/$cityId/$FineId", 0770);
                    }
                    if(!copy("$percorsoDocumenti/$nomeDocumento", "$cartella/$cityId/$FineId/$nomeDocumento")){
                        trigger_error("Errore nella copia del documento: da $percorsoDocumenti/$nomeDocumento a $cartella/$cityId/$FineId/$nomeDocumento", E_USER_WARNING);
                    }
                }
            }
        }
        
        $rs->End_Transaction();
        //Rimozione contenuto cartella immagini
        array_map('unlink', glob("$percorsoDocumenti/*.*"));
        //Rimozione file temporaneo
        unlink(pathinfo($percorsoDocumenti, PATHINFO_DIRNAME)."/".pathinfo($percorsoDocumenti, PATHINFO_FILENAME));
        //Rimozione cartella immagini
        rmdir($percorsoDocumenti);
        //Rimozione CSV
        unlink(VIOLATION_FOLDER."/$nomeFile");
    }
}
