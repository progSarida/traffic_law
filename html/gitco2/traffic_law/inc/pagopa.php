<?php
require_once (INC . "/chilkat_9_5_0.php");
require_once (CLS . "/cls_iuv.php");
require_once (CLS . "/cls_pagoPA.php");

const PAGOPA_PREFIX_FINE_PARTIAL = 1;
//Usata in contesto verbali, solo per lo iuv dell'importo ridotto/minimo (partial_<IdDaTabellaFine>)
const PAGOPA_PREFIX_FINE_TOTAL = 2;
const PAGOPA_PREFIX_INSTALLMENT = 3;

define("PAGOPA_PREFIX_LIST", serialize(array(
    PAGOPA_PREFIX_FINE_PARTIAL => "partial_",
    PAGOPA_PREFIX_FINE_TOTAL => "",
    PAGOPA_PREFIX_INSTALLMENT => "installment_"
)));

//Id gestori definiti su banca dati
const PAGOPA_GESTORE_LIGURIA = 1;
const PAGOPA_GESTORE_MYPAY_LOMBARDIA = 2;
const PAGOPA_GESTORE_SONDRIO = 3;
const PAGOPA_GESTORE_MYPAY_VENETO = 9;
const PAGOPA_GESTORE_NEXTSTEP = 10;
const PAGOPA_GESTORE_SISCOM = 11;

const CODICEIPA_COLLAUDO_LOMBARDIA = 'C_A849';
const CODICEIPA_COLLAUDO_VENETO = 'R_VENETO';

const SISCOM_STATO_NOIUV = 0; //Registrazione effettuata ma senza emissione IUV (Davide: restituito anche in caso di annullamento IUV già annullato)
const SISCOM_STATO_IUVATTESA = 2; //Richiesta inviata, in attesa di IUV (nel caso di CSI Piemonte)
const SISCOM_STATO_IUVGENERATO = 10; //IUV generato, disponibile per il pagamento
const SISCOM_STATO_IUVPAGATO = 20; //IUV Pagato
const SISCOM_STATO_IUVANNULLATO = 90; //IUV Annullato (mediante apposita chiamata)
const SISCOM_STATO_IUVINCASSATO = 100; //IUV Incassato: ricezione del flusso di pagamenti in contabilità

const MYPAY_EXPORTFLUSSO_STATO_NODOVUTI = "EXPORT_ESEGUITO_NESSUN_DOVUTO_TROVATO";
const MYPAY_EXPORTFLUSSO_STATO_INELAB = "EXPORT_IN_ELAB";
const MYPAY_EXPORTFLUSSO_STATO_INATTESA = "LOAD_EXPORT";
const MYPAY_EXPORTFLUSSO_STATO_ESEGUITO = "EXPORT_ESEGUITO";


define("MYPAY_COLONNE_CSV_EXPORT_PAGAMENTI", serialize(array(
    "iuf",
    "numRigaFlusso",
    "codIud",
    "codIuv",
    "versioneOggetto",
    "identificativoDominio",
    "identificativoStazioneRichiedente",
    "identificativoMessaggioRicevuta",
    "dataOraMessaggioRicevuta",
    "riferimentoMessaggioRichiesta",
    "riferimentoDataRichiesta",
    "tipoIdentificativoUnivoco",
    "codiceIdentificativoUnivoco",
    "denominazioneAttestante",
    "codiceUnitOperAttestante",
    "denomUnitOperAttestante",
    "indirizzoAttestante",
    "civicoAttestante",
    "capAttestante",
    "localitaAttestante",
    "provinciaAttestante",
    "nazioneAttestante",
    "enteBenefTipoIdentificativoUnivoco",
    "enteBenefCodiceIdentificativoUnivoco",
    "denominazioneBeneficiario",
    "codiceUnitOperBeneficiario",
    "denomUnitOperBeneficiario",
    "indirizzoBeneficiario",
    "civicoBeneficiario",
    "capBeneficiario",
    "localitaBeneficiario",
    "provinciaBeneficiario",
    "nazioneBeneficiario",
    "soggVersTipoIdentificativoUnivoco",
    "soggVersCodiceIdentificativoUnivoco",
    "anagraficaVersante",
    "indirizzoVersante",
    "civicoVersante",
    "capVersante",
    "localitaVersante",
    "provinciaVersante",
    "nazioneVersante",
    "emailVersante",
    "soggPagTipoIdentificativoUnivoco",
    "soggPagCodiceIdentificativoUnivoco",
    "anagraficaPagatore",
    "indirizzoPagatore",
    "civicoPagatore",
    "capPagatore",
    "localitaPagatore",
    "provinciaPagatore",
    "nazionePagatore",
    "emailPagatore",
    "codiceEsitoPagamento",
    "importoTotalePagato",
    "identificativoUnivocoVersamento",
    "codiceContestoPagamento",
    "singoloImportoPagato",
    "esitoSingoloPagamento",
    "dataEsitoSingoloPagamento",
    "identificativoUnivocoRiscoss",
    "causaleVersamento",
    "datiSpecificiRiscossione",
    "tipoDovuto"
)));


//Funzioni di importazione pagamenti/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

define("MYPAY_COLONNE_CSV_EXPORT_PAGAMENTI", serialize(array(
    "iuf",
    "numRigaFlusso",
    "codIud",
    "codIuv",
    "versioneOggetto",
    "identificativoDominio",
    "identificativoStazioneRichiedente",
    "identificativoMessaggioRicevuta",
    "dataOraMessaggioRicevuta",
    "riferimentoMessaggioRichiesta",
    "riferimentoDataRichiesta",
    "tipoIdentificativoUnivoco",
    "codiceIdentificativoUnivoco",
    "denominazioneAttestante",
    "codiceUnitOperAttestante",
    "denomUnitOperAttestante",
    "indirizzoAttestante",
    "civicoAttestante",
    "capAttestante",
    "localitaAttestante",
    "provinciaAttestante",
    "nazioneAttestante",
    "enteBenefTipoIdentificativoUnivoco",
    "enteBenefCodiceIdentificativoUnivoco",
    "denominazioneBeneficiario",
    "codiceUnitOperBeneficiario",
    "denomUnitOperBeneficiario",
    "indirizzoBeneficiario",
    "civicoBeneficiario",
    "capBeneficiario",
    "localitaBeneficiario",
    "provinciaBeneficiario",
    "nazioneBeneficiario",
    "soggVersTipoIdentificativoUnivoco",
    "soggVersCodiceIdentificativoUnivoco",
    "anagraficaVersante",
    "indirizzoVersante",
    "civicoVersante",
    "capVersante",
    "localitaVersante",
    "provinciaVersante",
    "nazioneVersante",
    "emailVersante",
    "soggPagTipoIdentificativoUnivoco",
    "soggPagCodiceIdentificativoUnivoco",
    "anagraficaPagatore",
    "indirizzoPagatore",
    "civicoPagatore",
    "capPagatore",
    "localitaPagatore",
    "provinciaPagatore",
    "nazionePagatore",
    "emailPagatore",
    "codiceEsitoPagamento",
    "importoTotalePagato",
    "identificativoUnivocoVersamento",
    "codiceContestoPagamento",
    "singoloImportoPagato",
    "esitoSingoloPagamento",
    "dataEsitoSingoloPagamento",
    "identificativoUnivocoRiscoss",
    "causaleVersamento",
    "datiSpecificiRiscossione",
    "tipoDovuto"
)));


//Funzioni di importazione pagamenti/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function importPayment(CLS_DB $rs, $city){
    //TODO controllar la condizione su F.CityId che è stata aggiunta alla versione originale
    $rs_fines = $rs->SelectQuery("
            SELECT distinct f.*
            FROM Fine f JOIN
            FineArticle fa ON (fa.FineId =f.Id) JOIN
            ArticleTariff at ON (at.ArticleId =fa.ArticleId and at.`Year` =f.ProtocolYear) LEFT JOIN
            V_AdditionalArticle vaa ON (vaa.FineId=f.Id)
            WHERE f.StatusTypeId in (9,20,23,25,27,28) and f.Id NOT IN (SELECT FineId FROM FinePayment WHERE PaymentTypeId=9) and f.CityId = '".$city['CityId']."'"
    );
    importPagoPAPaymentLombardia($rs, $city, PAGOPA_GESTORE_MYPAY_LOMBARDIA, CODICEIPA_COLLAUDO_LOMBARDIA);
    readExportedLombardia($rs, $city, PAGOPA_GESTORE_MYPAY_LOMBARDIA, CODICEIPA_COLLAUDO_LOMBARDIA);
    importPagoPAPaymentVeneto($rs, $city, PAGOPA_GESTORE_MYPAY_VENETO, CODICEIPA_COLLAUDO_VENETO);
    readExportedVeneto($rs, $city, PAGOPA_GESTORE_MYPAY_VENETO, CODICEIPA_COLLAUDO_VENETO);
    importPagoPAPaymentSondrio($rs, $city, $rs_fines);
}

function getPassword(CLS_DB $rs, int $serviceId, string $cityId){
    $rs_customerService = $rs->select("CustomerService", "ServiceId=$serviceId and CityId='$cityId'");
    if ($customerService = mysqli_fetch_array($rs_customerService)){
        return $customerService['Password'];
    }
    return null;
}

function getServiceUrl(CLS_DB $rs, int $serviceId, string $name){
    $rs_serviceUrl = $rs->select("ServiceUrl", "ServiceId=$serviceId and Name='$name'");
    if ($serviceUrl = mysqli_fetch_array($rs_serviceUrl)){
        return $serviceUrl;
    }
    return null;
}

function getUrlFromService(array $service): string {
    if ($service == null) return '';
    return PRODUCTION ? $service['UrlProduzione'] : $service['UrlCollaudo'];
}

function getServiceUrlFromId(CLS_DB $rs, int $serviceId, string $name): string{
    $res = getServiceUrl($rs, $serviceId, $name);
    return $res != '' ? getUrlFromService(getServiceUrl($rs, $serviceId, $name)) : '';
}

function getLastRequest(CLS_DB $rs, int $serviceId, $cityId){
  $rs_serviceRequest = $rs->select("ServiceRequest", "ServiceId=$serviceId and CityId='$cityId'", "RequestDate desc");
  if ($serviceRequest = mysqli_fetch_assoc($rs_serviceRequest)){
      return $serviceRequest['RequestDate'];
  }
  return null;
}

function importPagoPAPaymentLombardia(CLS_DB $rs, $city, $serviceId, $codiceIpaCollaudo){
    importPagoPAPaymentMyPay($rs, $city, $serviceId, $codiceIpaCollaudo);
}

function importPagoPAPaymentVeneto(CLS_DB $rs, $city, $serviceId, $codiceIpaCollaudo){
    importPagoPAPaymentMyPay($rs, $city, $serviceId, $codiceIpaCollaudo);
}

function readExportedLombardia(CLS_DB $rs, $city, $serviceId, $codiceIpaCollaudo){
    readExportedMyPay($rs, $city, $serviceId, $codiceIpaCollaudo);
}

function readExportedVeneto(CLS_DB $rs, $city, $serviceId, $codiceIpaCollaudo){
    readExportedMyPay($rs, $city, $serviceId, $codiceIpaCollaudo);
}

function importPagoPAPaymentMyPay(CLS_DB $rs, $city, $serviceId, $codiceIpaCollaudo){
    $service = getServiceUrl($rs, $serviceId, 'Export payment');
    $url = getUrlFromService($service);
    $action = $service['SoapAction'];
    $cityId = $city['CityId'];
    $codiceIpa = "C_" . $cityId;
    $lastRequest = getLastRequest($rs, $serviceId, $cityId);
    trigger_error("Richiesta pagamenti MyPay: Data ultima richiesta: $lastRequest");
    
    $limitDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
    if ($lastRequest == null || $limitDate > $lastRequest){
        $lastRequest = $limitDate;
    }
    if ($lastRequest > date("Y-m-d")){
        return null;
    }
    
    $password = getPassword($rs, $serviceId, $cityId);
    $body = new CkXml();
    $body->put_Tag('ente:paaSILPrenotaExportFlusso');
    $body->NewChild('password', $password);
    $dateFrom = substr($lastRequest, 0, 11);
    $dateTo = date("Y-m-d");
    $body->NewChild('dateFrom', $dateFrom);
    $body->NewChild('dateTo', $dateTo);
    $header = new CkXml();
    $header->put_Tag('ppt:intestazionePPT');
    $header->NewChild("codIpaEnte", !PRODUCTION ? $codiceIpaCollaudo : $codiceIpa); //C_A849 per lombarida R_VENETO per veneto
    
    trigger_error("Richiesta pagamenti MyPay: Invio nuova richiesta per $cityId dal $dateFrom al $dateTo", E_USER_NOTICE);
    
    $soapBody = createSoapXml($body, $header, array("xmlns:ppt" => "http://www.regione.veneto.it/pagamenti/ente/ppthead","xmlns:ente" => "http://www.regione.veneto.it/pagamenti/ente/"));
    $xmlResponse = callSoapUrlWithSoapBody($url, "POST", $soapBody, $action, null, null, "text/xml");
    $requestToken = null;
    
    if($xmlResponse){
        $requestTokenNode = $xmlResponse->SearchForTag($xmlResponse, 'requestToken');
        if ($requestTokenNode){
            $requestToken = $requestTokenNode->content();
        } 
    }
    
    if($requestToken){
        $insert = array(
            array('field' => 'ServiceId','selector' => 'value','type' => 'int','settype' => 'int','value' => 2),
            array('field' => 'RequestId','selector' => 'value','type' => 'str','value' => $requestToken),
            array('field' => 'RequestState','selector' => 'value','type' => 'str','value' => 'Created'),
            array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $cityId),
            array('field' => 'RequestDate','selector' => 'value','type' => 'str','value' => date("Y-m-d H:i:s"))
        );
        $rs->Insert("ServiceRequest", $insert);
        
        trigger_error("Richiesta pagamenti MyPay: Aggiunta richiesta: $requestToken per $cityId", E_USER_NOTICE);
    } 
    else {
        creaEventoInformativo($rs, $cityId, "Pagamenti PagoPA MyPay", "Richiesta importazione pagamenti fallita", "ERROR");
        trigger_error("Richiesta pagamenti MyPay: Richiesta fallita: ".($xmlResponse->getXml() ?: ""), E_USER_WARNING);
        };
    
    return $requestToken;
}

function readExportedMyPay(CLS_DB $rs, $city, $serviceId, $codiceIpaCollaudo){
    $rs_ServiceRequest = $rs->Select("ServiceRequest", "ServiceId=$serviceId and RequestState!='Completed'");
    $service = getServiceUrl($rs, $serviceId, 'State export payment');
    $prefixList = unserialize(PAGOPA_PREFIX_LIST);
    
    $url = getUrlFromService($service);
    $action = $service['SoapAction'];
    $number = 0;
    
    $cityId = $city['CityId'];
    
    $rs->Start_Transaction();
    
    while ($serviceRequest = mysqli_fetch_array($rs_ServiceRequest)){
        trigger_error("Importazione pagamenti MyPay: Verifica flusso {$serviceRequest['RequestId']}", E_USER_NOTICE);
        
        $filesPath = ARCHIVIO . "/doc/pagopapayment/MyPay/".$serviceRequest['CityId'];
        
        if (! file_exists($filesPath)) {
            mkdir($filesPath, 0770, true);
            chmod($filesPath, 0770);
        }
        
        $filePath = "$filesPath/{$serviceRequest['RequestId']}.zip";
        $update = array();
        
        if(empty($serviceRequest['ResultUrl'])){
            $password = getPassword($rs, $serviceId, $serviceRequest['CityId']);
            $codiceIpa = "C_" . $serviceRequest['CityId'];
            $body = new CkXml();
            $body->put_Tag('ente:paaSILChiediStatoExportFlusso');
            $body->NewChild('password', $password);
            $body->NewChild('requestToken', $serviceRequest['RequestId']);
            $header = new CkXml();
            $header->put_Tag('ppt:intestazionePPT');
            $header->NewChild("codIpaEnte", !PRODUCTION ? $codiceIpaCollaudo : $codiceIpa); //C_A849 per lombarida R_VENETO per veneto
            $soapBody = createSoapXml($body, $header, array("xmlns:ppt" => "http://www.regione.veneto.it/pagamenti/ente/ppthead","xmlns:ente" => "http://www.regione.veneto.it/pagamenti/ente/"));
            $xmlResponse = callSoapUrlWithSoapBody($url, "POST", $soapBody, $action, null, null, "text/xml");
            $downloadUrl = null;
            
            if($xmlResponse){
                if(($stato = $xmlResponse->SearchForTag($xmlResponse, 'stato'))){
                    trigger_error("Importazione pagamenti MyPay: Flusso: {$serviceRequest['RequestId']}, stato: ".$stato->content(), E_USER_NOTICE);
                    switch ($stato->content()){
                        case MYPAY_EXPORTFLUSSO_STATO_INATTESA:
                        case MYPAY_EXPORTFLUSSO_STATO_INELAB:
                            break;
                        case MYPAY_EXPORTFLUSSO_STATO_NODOVUTI:
                            $update[] = array('field' => 'RequestState','selector' => 'value','type' => 'str','value' => 'Completed');
                            break;
                        case MYPAY_EXPORTFLUSSO_STATO_ESEGUITO:
                            $downloadUrlNode = $xmlResponse->SearchForTag($xmlResponse, 'downloadUrl');
                            if ($downloadUrlNode){
                                $downloadUrl = $downloadUrlNode->content();
                                $update[] = array('field' => 'ResultUrl','selector' => 'value','type' => 'str','value' => $downloadUrl);
                            }
                            break;
                    }
                } else {
                    $faultCode = $xmlResponse->SearchForTag($xmlResponse,"faultCode") ? $xmlResponse->SearchForTag($xmlResponse,"faultCode")->content() : '';
                    $faultString = $xmlResponse->SearchForTag($xmlResponse,"faultString") ? $xmlResponse->SearchForTag($xmlResponse,"faultString")->content() : '';
                    creaEventoInformativo($rs, $cityId, "Lettura pagamenti PagoPA MyPay", "Errore nella risposta", "ERROR");
                    trigger_error("Importazione pagamenti MyPay: Errore nella risposta $faultCode $faultString", E_USER_WARNING);
                }
            } else {
                    creaEventoInformativo($rs, $cityId, "Lettura pagamenti PagoPA MyPay", "Risposta vuota", "ERROR");
                    trigger_error("Importazione pagamenti MyPay: Risposta vuota.", E_USER_WARNING);
                    }
            
            if (empty($downloadUrl)){
                trigger_error("Importazione pagamenti MyPay: Nessun file da scaricare", E_USER_NOTICE);
            } 
        } else $downloadUrl = $serviceRequest['ResultUrl'];
        
        if(!file_exists($filePath) && $downloadUrl){
            if (!copy($downloadUrl, $filePath)){
                creaEventoInformativo($rs, $cityId, "Lettura pagamenti PagoPA MyPay", "Impossibile scaricare il file $downloadUrl in $filePath", "ERROR");
                trigger_error("Importazione pagamenti MyPay: Impossibile scaricare il file $downloadUrl in $filePath", E_USER_WARNING);
            } else trigger_error("Importazione pagamenti MyPay: File scaricato: $downloadUrl in $filePath", E_USER_NOTICE);
        }
        
        $zip = new ZipArchive();
        
        if(file_exists($filePath)){
            if ($zip->open($filePath) == true){
                $fileContent = $zip->getFromIndex(0);
                $csv = fopen("php://temp", 'r+');
                
                if(!empty($fileContent) && $csv){
                    $str_Separator = ";";
                    fputs($csv, $fileContent);
                    rewind($csv);
                    $a_ColonneCSV = fgetcsv($csv, 0, $str_Separator);
                    $a_Colonne = unserialize(MYPAY_COLONNE_CSV_EXPORT_PAGAMENTI);
                    
                    $a_Intersezione = array_intersect($a_Colonne, $a_ColonneCSV);
                    $a_ColonneDiverse = array_merge(array_diff($a_Colonne, $a_Intersezione), array_diff($a_ColonneCSV, $a_Intersezione));
                    
                    if(empty($a_ColonneDiverse)){
                        for($a_Righe=[]; $a_RigaCSV=fgetcsv($csv, 0, $str_Separator); $a_Righe[]=array_combine($a_ColonneCSV,$a_RigaCSV));
                        fclose($csv);
                        
                        $righeTotali = count($a_Righe);
                        trigger_error("Importazione pagamenti MyPay: Pagamenti da registare: $righeTotali", E_USER_NOTICE);
                        
                        foreach ($a_Righe as $a_Riga){
                            try{
                                $IUD = $a_Riga["codIud"];
                                $amount = $a_Riga["importoTotalePagato"];
                                $name = $a_Riga["anagraficaPagatore"];
                                $date = $a_Riga["dataEsitoSingoloPagamento"];
                                $receipt = $a_Riga["identificativoUnivocoRiscoss"];
                                $causal = $a_Riga["causaleVersamento"];
                                $FineId = $PaymentRateId = $numeroRata = 0;
                                if (!empty($IUD)){
                                    trigger_error("Importazione Pagamento MyPay IUD: $IUD, Nome: $name, Data: $date, Importo: $amount, IdentificativoRisc: $receipt, Causale: $causal", E_USER_NOTICE);
                                    
                                    //Pagamento rata
                                    if(strpos($IUD, $prefixList[PAGOPA_PREFIX_INSTALLMENT]) !== false){
                                        $IUD = str_replace($prefixList[PAGOPA_PREFIX_INSTALLMENT], '', $IUD);
                                        list($PaymentRateId, $numeroRata) = explode("_", $IUD);
                                        
                                        if($PaymentRateId > 0){
                                            $r_PaymentRate = $rs->getArrayLine($rs->Select("PaymentRate", "Id=$PaymentRateId"));
                                            $FineId = $r_PaymentRate['FineId'] ?? 0;
                                        }
                                    }
                                    //Pagamento verbale minimo/meta massimo
                                    else if(strpos($IUD, $prefixList[PAGOPA_PREFIX_FINE_PARTIAL]) === false){
                                        if(is_numeric($IUD)){
                                            $r_Fine = $rs->getArrayLine($rs->Select("Fine", "CityId='{$serviceRequest['CityId']}' AND Id=$IUD"));
                                            $FineId = $r_Fine['Id'] ?? 0;
                                        }
                                    }
                                    //Pagamento verbale ridotto/minimo
                                    else {
                                        $IUD = str_replace($prefixList[PAGOPA_PREFIX_FINE_PARTIAL], '', $IUD);
                                        if(is_numeric($IUD)){
                                            $r_Fine = $rs->getArrayLine($rs->Select("Fine", "CityId='{$serviceRequest['CityId']}' AND Id=$IUD"));
                                            $FineId = $r_Fine['Id'] ?? 0;
                                        }
                                    }
                                    
                                    //TODO inserire scorporo
                                    insertPayment($rs, $serviceRequest['CityId'], $FineId, $name, $date, $amount, $receipt, $causal, $PaymentRateId, $numeroRata);
                                    $number ++;
                                }
                            } catch (TypeError $e) {
                                creaEventoInformativo($rs, $cityId, "Lettura pagamenti PagoPA MyPay", "Errore lettura riga CSV", "ERROR");
                                trigger_error("Importazione pagamenti MyPay: Errore lettura riga CSV: ".$e->getMessage(), E_USER_WARNING);
                            }
                        }
                        
                        trigger_error("Importazione pagamenti MyPay: Registrati $number pagamenti per " . $serviceRequest['CityId'],E_USER_NOTICE);
                        
                        if($righeTotali == $number){
                            trigger_error("Importazione pagamenti MyPay: Tutti i pagamenti registrati per flusso: " . $serviceRequest['RequestId'],E_USER_NOTICE);
                            $update[] = array('field' => 'RequestState','selector' => 'value','type' => 'str','value' => 'Completed');
                        }
                    }
                } else {
                    creaEventoInformativo($rs, $cityId, "Lettura pagamentiPagoPA MyPay", "Errore lettura file CSV", "ERROR");
                    trigger_error("Importazione pagamenti MyPay: Errore lettura CSV", E_USER_WARNING);
                    }
                $zip->close();
            } else {
                creaEventoInformativo($rs, $cityId, "Lettura pagamentiPagoPA MyPay", "Errore lettura archivio", "ERROR");
                trigger_error("Importazione pagamenti MyPay: Errore lettura archivio", E_USER_WARNING);
                }
        }

        if(!empty($update)){
            $rs->update("ServiceRequest", $update, "CityId='{$serviceRequest['CityId']}' and ServiceId=$serviceId and RequestId='{$serviceRequest['RequestId']}'");
        }
    }
    
    $rs->End_Transaction();
}

function importPagoPAPaymentSondrio(CLS_DB $rs, $city, $rs_fines){
    $serviceUrl = getServiceUrl($rs, 3, "Import payment");
    $url = getUrlFromService($serviceUrl);
    $username = $serviceUrl['SoapAction'];
    $password = $serviceUrl['Password'];
    $cityId = $city['CityId'];
    $sftp = new \phpseclib3\Net\SFTP($url);
    $login=$sftp->login($username, $password);
    
    trigger_error("Login PagoPA Sondrio per $cityId con $username, $password:$login", E_USER_NOTICE);

    $fileList = $sftp->nlist("/PagoPA/{$city['SondrioServizio']}/Rendicontazioni");

    trigger_error("Import pagamenti PagoPA Sondrio per $cityId", E_USER_NOTICE);
  
    $number = 0;
    $fileNameTemplate = "RE_{$cityId}_{$city['SondrioSottoservizio']}";
    $filesToDownload = array();
    
    if($fileList){
        for ($i = 0; $i < count($fileList); $i ++){
            if (substr_count($fileList[$i], $fileNameTemplate) > 0){
                array_push($filesToDownload, ROOT . "/pagoPAPayment/" . $fileList[$i]);
                $sftp->get($fileList[$i], ROOT . "/pagoPAPayment/" . $fileList[$i]);
            }
        }
        for ($i = 0; $i < sizeof($filesToDownload); $i ++){
            fopen($filesToDownload[$i], "r");
            $fileContent = fread($filesToDownload[$i], filesize($filesToDownload[$i]));
        }
        
        $rs->Start_Transaction();
        
        if(isset($fileContent)){
            $rows = explode(PHP_EOL, $fileContent);
            $csv = array();
            foreach ($rows as $row){
                $csv[] = explode(";", $row);
            }
            while ($fine = mysqli_fetch_array($rs_fines)){
                $fines[] = $fine;
            }
              
            foreach ($fines as $fine){
                foreach ($csv as $csvRow){
                    $iuv = str_replace("'", "", $csvRow[17]);
                    
                    if ($fine['PagoPA1'] == $iuv || $fine['PagoPA2'] == $iuv){
                        error_log("Trovato iuv $iuv in verbale  {$fine['Id']}, importo {$csvRow[24]}");
                        $paymentDate = date("Y-m-d", strtotime(str_replace("/", "-", $csvRow[32])));
                        try{
                            $number ++;
                            //TODO inserire scorporo
                            insertPayment($rs, $city['CityId'], $fine['Id'], $csvRow[6], $paymentDate, str_replace(",", ".", $csvRow[24]), str_replace("'", "", $csvRow[37]));
                        } catch (TypeError $e){
                            creaEventoInformativo($rs, $cityId, "Pagamenti PagoPA Sondrio", "Richiesta importazione pagamenti fallita per il verbale Cron: ".$fine['Code'], "ERROR");
                            trigger_error("PagoPA import payment error: " . $e->getCode() . " " . $e->getMessage(), E_USER_WARNING);
                        }
                    }
                }
            }
            
            trigger_error("Added $number payment from PagoPA Sondrio for " . $city['CityId'],E_USER_NOTICE);

        }
        
        $rs->End_Transaction();
    }
}

function insertPayment(CLS_DB $rs, $cityId, int $fineId, $name, $paymentDate, $amount, $receiptNumber, $causal='', $installmentId = null, $installmentNumber = null){
    $rs_finePayment=$rs->Select("FinePayment", sprintf("FineId=$fineId and ImportReceiptNumber='%s'", mysqli_real_escape_string($rs->conn, $receiptNumber)));
    
    if(mysqli_num_rows($rs_finePayment)>0){
        trigger_error("Pagamento ImportRecepitNumber='$receiptNumber' già importato",E_USER_WARNING);
        return;
    }
    
    $paymentInsert = array(
        array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $cityId),
        array('field' => 'FineId','selector' => 'value','type' => 'int','value' => $fineId,'settype' => 'int'),
        array('field' => 'TableId','selector' => 'value','type' => 'int','value' => 1,'settype' => 'int'),
        array('field' => 'BankMgmt','selector' => 'value','type' => 'int','value' => 1,'settype' => 'int'),
        array('field' => 'PaymentFee','selector' => 'value','type' => 'int','value' => $installmentNumber ?: 0,'settype' => 'int'),
        array('field' => 'PaymentTypeId','selector' => 'value','type' => 'int','value' => 9,'settype' => 'int'),
        array('field' => 'Name','selector' => 'value','type' => 'str','value' => $name),
        array('field' => 'Note','selector' => 'value','type' => 'str','value' => $causal),
        array('field' => 'PaymentDocumentId','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'ImportationId','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'PaymentDate','selector' => 'value','type' => 'str','value' => $paymentDate),
        array('field' => 'RegDate','selector' => 'value','type' => 'str','value' => date('Y-m-d')),
        array('field' => 'RegTime','selector' => 'value','type' => 'str','value' => date('h:m:s')),
        array('field' => 'UserId','selector' => 'value','type' => 'str','value' => 'admin'),
        array('field' => 'ReclaimOrder','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'RefundStatus','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'PaymentDirect','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'Amount','selector' => 'value','type' => 'flt','value' => $amount,'settype' => 'flt'),
        array('field' => 'OfficeNotificationFee','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'Hidden','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'ReceiptNumber','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'ImportReceiptNumber','selector' => 'value','type' => 'str','value' => $receiptNumber),
    );
    
    if($installmentId > 0){
        $paymentInsert[] = array('field' => 'InstallmentId','selector' => 'value','type' => 'int','value' => $installmentId,'settype' => 'int');
    }
    
    $rs->Insert("FinePayment", $paymentInsert);
}


function callPagoPALiguria($url, $a_Fees, $feeIndex, $alias, $idUnivocoDovuto, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $iban, $causale){
    if($identificativoFiscalePAgatore==null || $identificativoFiscalePAgatore==''){
        $identificativoFiscalePAgatore="ANONIMO";
    }
    
    $Nominativo = substr(trim($anagraficaPagatore['CompanyName'] . ' ' . $anagraficaPagatore['Surname'] . ' ' . $anagraficaPagatore['Name']), 0, 139);
    $Nominativo = str_replace("&", "E", $Nominativo);
    
    $importo = $a_Fees['Sum'][$feeIndex];
    $data = array(
        "alias" => $alias,
        "urlPost" => PAGOPA_URL,
        "codTrans" => $idUnivocoDovuto,
        "tipoPagatore" => $tipoPagatore,
        "anagraficaPagatore" => $Nominativo,
        "identificativoFiscalePagatore" => $identificativoFiscalePAgatore,
        "email" => "pagatore@email.it",
        "listaDatiSingoloPagamento" => array(
            array(
                "ibanBeneficiario" => $iban,
                "divisa" => "EUR",
                "causale" => $causale,
                "importo" => str_replace(".", "", $importo)
            )
        )
    );

    $myvars = 'richiesta=' . json_encode($data, JSON_UNESCAPED_SLASHES);
    
    trigger_error("Parametri: ".print_r($data,true), E_USER_NOTICE);
    trigger_error("anagraficaPagatore: ".$Nominativo, E_USER_NOTICE);
    trigger_error("URL: ".$url);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $myvars);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    trigger_error("Chiamata PagoPA Liguria per $alias IUD $idUnivocoDovuto", E_USER_NOTICE);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)){
        trigger_error('Errore: '.curl_error($ch), E_USER_WARNING);
        return null;
    }
    
    trigger_error('Risposta: '.$response, E_USER_NOTICE);
    
    $obj_Payment = json_decode($response);
    
    if (! isset($obj_Payment) && ! isset($obj_Payment->iuv)){
        trigger_error($obj_Payment, E_USER_NOTICE);
        return null;
    }
    
    return $obj_Payment->iuv;
}
  
//NOTA: funziona sulla base di calcolaImporti e su un array di risultati di PagoPAServiceParameter
function rendiParametriImporto($a_Fees, $feeIndex, $a_PagoPAServiceParams, $forzaTipoViolAltro = false){
    $a_dati = array();
    $a_ViolationTypeIdParams = array_column($a_PagoPAServiceParams, null, 'ViolationTypeId');
    foreach ($a_Fees['Amounts'] as $fee){
        //TODO per il momento tutti i tipi violazione diversi da 2 (velocità) vengono considerati come 8 (altro)
        $ViolationTypeId = ($fee['ViolationTypeId'] != 2 || $forzaTipoViolAltro) ? 8 : 2;
        
        if(!isset($a_ViolationTypeIdParams[$ViolationTypeId])){
            trigger_error("ERRORE: Parametri PagoPA non presenti per TipoViolazione: $ViolationTypeId", E_USER_WARNING);
            return null;
        }
        
        if(!isset($a_dati['Principale'])){
            $a_dati['Principale'] = array(
                'Importo' => $a_Fees['Sum'][$feeIndex],
                'TipoContabilità' => $a_ViolationTypeIdParams[$ViolationTypeId]['Type'] ?? null,
                'CodiceContabilità' => $a_ViolationTypeIdParams[$ViolationTypeId]['Code'] ?? null,
                'TipoDovuto' => $a_ViolationTypeIdParams[$ViolationTypeId]['TypeDue'] ?? null,
                'CodiceCapitolo' => $a_ViolationTypeIdParams[$ViolationTypeId]['CodiceCapitolo'] ?? null,
                'NumeroAccertamento' => $a_ViolationTypeIdParams[$ViolationTypeId]['NumeroAccertamento'] ?? null
            );
        }
        
        $a_dati['Bilancio'][$ViolationTypeId] = array(
            'Importo' => ($a_dati['Bilancio'][$ViolationTypeId]['Importo'] ?? 0) + $fee[$feeIndex],
            'CodiceCapitolo' => $a_ViolationTypeIdParams[$ViolationTypeId]['CodiceCapitolo'] ?? null,
            'NumeroAccertamento' => $a_ViolationTypeIdParams[$ViolationTypeId]['NumeroAccertamento'] ?? null
        );
    }
    return $a_dati;
}

function generaCtVersamento($a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePagatore, $causale, $a_PagoPAServiceParams, $azione, $iuvCode = null){
    $a_dati = rendiParametriImporto($a_Fees, $feeIndex, $a_PagoPAServiceParams);
    
    if(!$a_dati){
        return null;
    }
    
    $Nominativo = substr(trim($anagraficaPagatore['CompanyName'] . ' ' . $anagraficaPagatore['Surname'] . ' ' . $anagraficaPagatore['Name']), 0, 139);
    $Nominativo = str_replace("&", "E", $Nominativo);
    
    $tipoContabilità = !PRODUCTION ? '9' : $a_dati['Principale']['TipoContabilità'];
    $codiceContabilità = !PRODUCTION ? '000' : $a_dati['Principale']['CodiceContabilità'];
    $tipoDovuto = !PRODUCTION ? 'ALTRO' : $a_dati['Principale']['TipoDovuto'];
    $codiceCapitolo = $a_dati['Principale']['CodiceCapitolo'];
    $numeroAccertamento = $a_dati['Principale']['NumeroAccertamento'];
    $importo = $a_dati['Principale']['Importo'];
    
    $ctVersamento = new CkXml();
    $ctVersamento->put_Tag('Versamento');
    $ctVersamento->AddAttribute("xmlns", "http://www.regione.veneto.it/schemas/2012/Pagamenti/Ente/");
    $ctVersamento->AddAttribute("xmlns:schemaLocation", "http://www.regione.veneto.it/schemas/2012/Pagamenti/Ente/schema.xsd");
    $ctVersamento->AddAttribute("xmlns:XSI", "http://www.w3.org/2001/XMLSchema-instance");
    $ctVersamento->NewChild('versioneOggetto', "6.0");
    $ctVersamento->NewChild('soggettoPagatore|identificativoUnivocoPagatore|tipoIdentificativoUnivoco', $tipoPagatore);
    $ctVersamento->NewChild('soggettoPagatore|identificativoUnivocoPagatore|codiceIdentificativoUnivoco', $identificativoFiscalePagatore);
    $ctVersamento->NewChild('soggettoPagatore|anagraficaPagatore', $Nominativo);
    $ctVersamento->NewChild('datiVersamento|dataEsecuzionePagamento', date('Y-m-d', strtotime($fineDate. ' + '. EXPIRATION_PERIOD))); //indica la data di scadenza del dovuto secondo il formato ISO 8601 [YYYY]-[MM]-[DD];  //mettere data violazione + 1 anno
    
    trigger_error("data scadenza ".date('Y-m-d', strtotime($fineDate. ' + '. EXPIRATION_PERIOD)), E_USER_NOTICE);
    
    $ctVersamento->NewChild('datiVersamento|tipoVersamento', 'ALL');
    
    if ($azione != 'I' && $azione != 'A'){
        $ctVersamento->NewChild('datiVersamento|identificativoUnivocoVersamento', $iuvCode);
    }
    
    $ctVersamento->NewChild('datiVersamento|identificativoUnivocoDovuto', $fineId);
    $ctVersamento->NewChild('datiVersamento|importoSingoloVersamento', $importo);
    
    trigger_error("tipo dovuto ".$tipoDovuto."-", E_USER_NOTICE);
    
    $ctVersamento->NewChild('datiVersamento|identificativoTipoDovuto', $tipoDovuto);//PL_SAN_STR per prod - ALTRO in collaudo
    $ctVersamento->NewChild('datiVersamento|causaleVersamento', $causale);
    $ctVersamento->NewChild('datiVersamento|datiSpecificiRiscossione', $tipoContabilità . '/' . $codiceContabilità); // 9/000
    
    $bilancio = $ctVersamento->NewChild('datiVersamento|bilancio', '');
    
    //TODO per ora nel bilancio mettiamo un solo capitolo, con i parametri della violazione principale
    $capitolo = $bilancio->NewChild('capitolo', '');
    $capitolo->NewChild('codCapitolo', $codiceCapitolo);
    $accertamento = $capitolo->NewChild('accertamento', '');
    //Se nei parametri c'è il numero accertamento, ne inserisce tag e valore, altrimenti no
    if(!empty($numeroAccertamento)){
        $accertamento->NewChild('codAccertamento', $numeroAccertamento);
    }
    $accertamento->NewChild('importo', $importo);
    
    //TODO da usare invece che i parametri principali nel caso poi ci saranno da dividere gli importi in capitoli
//     foreach($a_dati['Bilancio'] as $dato){
//         $capitolo = $bilancio->NewChild('capitolo', '');
//         $capitolo->NewChild('codCapitolo', $dato['CodiceCapitolo']);
//         $accertamento = $capitolo->NewChild('accertamento', '');
//         //Se nei parametri c'è il numero accertamento, ne inserisce tag e valore, altrimenti no
//         if(!empty($dato['NumeroAccertamento'])){
//           $accertamento->NewChild('codAccertamento', $dato['NumeroAccertamento']);
//         }
//         $accertamento->NewChild('importo', $dato['Importo']);
//     }
    
    $ctVersamento->NewChild('azione', $azione);
    
    return $ctVersamento;
}

function callPagoPASondrio($url, $a_Fees, $feeIndex, $cityId, $password, $fineId, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $scadenza, $servizio, $sottoservizio){
    $a_dati = rendiParametriImporto($a_Fees, $feeIndex, $a_PagoPAServiceParams, true);
    
    if(!$a_dati){
        return null;
    }
    
    $Nominativo = substr(trim($anagraficaPagatore['CompanyName'] . ' ' . $anagraficaPagatore['Surname'] . ' ' . $anagraficaPagatore['Name']), 0, 139);
    $Nominativo = str_replace("&", "E", $Nominativo);
    
    $tipoContabilità = $a_dati['Principale']['TipoContabilità'];
    $codiceContabilità = $a_dati['Principale']['CodiceContabilità'];
    $importo = $a_dati['Principale']['Importo'];
    
    $date = new DateTime();
    $createRequest = new CkXml();
    $createRequest->put_Tag('v1:IUVOnlineCreateRequest');
    $createRequest->NewChild("testata|id_transazione", $date->getTimestamp());
    $createRequest->NewChild("testata|codice_servizio", $servizio);
    $createRequest->NewChild("testata|codice_sottoservizio", $sottoservizio);
    $createRequest->NewChild("IUVOnlineCreateRequestData|numero_disposizioni", 1);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_banca|codice_servizio", $servizio);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_banca|codice_sottoservizio", $sottoservizio);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_debitore|tipo_identificativo_univoco", $tipoPagatore);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_debitore|codice_debitore", $identificativoFiscalePAgatore);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_debitore|anagrafica_debitore", $Nominativo);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|progressivo", 1);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|importo", $importo);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|scadenza", $scadenza);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|causale_bollettino", $causale);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|dati_specifici_riscossione|tipo_contabilita", $tipoContabilità);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|dati_specifici_riscossione|codice_contabilita", $codiceContabilità);
    $createRequest->NewChild("IUVOnlineCreateRequestData|informazioni_pagamento|causale_RPT|causaleVersamento", $scadenza);

    $soapBody = createSoapXml($createRequest, null, array('xmlns:v1' => 'http://schema.iuvonline.nodospcit.ws.popso.it/v1'));
    
    trigger_error("Chiamata PagoPA Sondrio per $servizio $sottoservizio  verbale $fineId: " . $createRequest->getXml(), E_USER_NOTICE);
  
    $xmlResp = callSoapUrlWithSoapBody($url, "POST", $soapBody, 'http://scrittura.iuvonline.nodospcit.ws.popso.it/v1/IUVOnlineCreate', CERT_PATH."sondrio$cityId.pfx", $password, "text/xml");
    
    if (!$xmlResp){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        trigger_error("Risposta Sondrio: " . $xmlResp->getXml(), E_USER_NOTICE);
        
        $found = $xmlResp->SearchForTag($xmlResp, 'codice_identificativo_bollettino');
        
        if ($found){
            trigger_error("IUV assegnato: " . $found->content(), E_USER_NOTICE);
            return $found->content();
        } else {
            trigger_error("ERRORE: assegnazione fallita.", E_USER_WARNING);
        }
    }
    
    return null;
}

function callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, $azione, $codiceIpaCollaudo, $iuvCode = null){
    $body = new CkXml();
    $body->put_Tag('ente:paaSILImportaDovuto');
    $ctVersamento = generaCtVersamento($a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $azione, $iuvCode)->getXml();
    
    trigger_error('XML Versamento: '.$ctVersamento, E_USER_NOTICE);
    
    if(!$ctVersamento){
        return null;
    }
    
    $body->NewChild('password', $password);
    $body->NewChild('dovuto', base64_encode($ctVersamento));
    
    if ($azione == 'I'){
        $body->NewChild('flagGeneraIuv', "true");
    }
    
    $header = new CkXml();
    $header->put_Tag('ppt:intestazionePPT');
    $codiceIpa = !PRODUCTION ? $codiceIpaCollaudo : "C_" . $_SESSION['cityid'];
    $header->NewChild("codIpaEnte", $codiceIpa); //C_A849 per lombarida R_VENETO per veneto
    
    trigger_error("Chiamata PagoPA MyPay per $codiceIpa verbale $fineId action $azione", E_USER_NOTICE);
    
    $soapBody = createSoapXml($body, $header, array("xmlns:ppt" => "http://www.regione.veneto.it/pagamenti/ente/ppthead","xmlns:ente" => "http://www.regione.veneto.it/pagamenti/ente/"));
    $xmlResponse = callSoapUrlWithSoapBody($url, "POST", $soapBody, "paaSILImportaDovuto", null, null, "text/xml");
    
    if (!$xmlResponse){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        trigger_error("Risposta MyPay: " . $xmlResponse->getXml(), E_USER_NOTICE);
        
        $esito = $xmlResponse->SearchForTag($xmlResponse, "esito");
        
        if ($esito){
            if($esito->content() != 'OK'){
                $faultCode = $xmlResponse->SearchForTag($xmlResponse,"faultCode");
                trigger_error('ERRORE: '.($faultCode ? $faultCode->content() : "Sconosciuto"), E_USER_WARNING);
            } else {
                if ($azione == 'I'){
                    $identificativoUnivocoVersamento = $xmlResponse->SearchForTag($xmlResponse, "identificativoUnivocoVersamento");
                    if($identificativoUnivocoVersamento){
                        trigger_error("IUV assegnato: ". $identificativoUnivocoVersamento->content(), E_USER_NOTICE);
                        return $identificativoUnivocoVersamento->content();
                    } else {
                        trigger_error("ERRORE: Impossibile recuperare IUV. Vedere risposta.", E_USER_WARNING);
                    }
                } else if ($azione == 'M'){
                    trigger_error("IUV aggiornato: ".$iuvCode, E_USER_NOTICE);
                    return true;
                } else if ($azione == 'A'){
                    trigger_error("IUV annullato: ".$iuvCode, E_USER_NOTICE);
                    return true;
                }
            }
        } else {
            trigger_error("ERRORE: Impossibile recuperare IUV. Vedere risposta.", E_USER_WARNING);
        }
    }
    
    return $azione == 'I' ? null : false;
}

function callPagoPASiscom($a_Fees, $feeIndex, $fineId, $fineDate, $customer, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams){
    $a_dati = rendiParametriImporto($a_Fees, $feeIndex, $a_PagoPAServiceParams, true);
    
    $tipoContabilità = $a_dati['Principale']['TipoContabilità'];
    $codiceContabilità = $a_dati['Principale']['CodiceContabilità'];
    
    $Nominativo = substr(trim($anagraficaPagatore['CompanyName'] . ' ' . $anagraficaPagatore['Surname'] . ' ' . $anagraficaPagatore['Name']), 0, 139);
    $Nominativo = str_replace("&", "E", $Nominativo);
    $Nome = trim($anagraficaPagatore['Name']);
    $Cognome = trim($anagraficaPagatore['Surname']);
    
    $dataScadenza = date('Y-m-d', strtotime($fineDate. ' + '. EXPIRATION_PERIOD));
    trigger_error("Data scadenza: $dataScadenza");
    
    $paramsGenera = array(
        "ContTipoContabilita" => PRODUCTION ? $tipoContabilità : 9,
        "ContCodiceContabilita" => PRODUCTION ? $codiceContabilità : '',
        "CodiceServizio" => PRODUCTION ? $customer['SondrioServizio'] : 1,
        "pCodFiscaleEnte" => PRODUCTION ? $customer['ManagerTaxCode'] : '12345678901',
        "RifEnte" => PRODUCTION ? $customer['ManagerTaxCode'] : '12345678901',
        "ProceduraChiave" => $fineId,
        "PagamentoImporto" => $a_Fees['Sum'][$feeIndex],
        "IUV" => null,
        
        "DebitNatura" => $tipoPagatore,//F (fisica) o G (giuridica) OBBLIGATORIO
        
        "DebitRagSocCognome" => $tipoPagatore == 'G' ? $Nominativo : $Cognome,//Ditta o Cognome
        "DebitNome" => $tipoPagatore == 'F' ? $Nome : '',//Nome
        "PagamentoCausale" => $causale,
        "DebitCodFiscale" => $identificativoFiscalePAgatore,//CF
        
        "DataEmissione" => date('Y-m-d'),//Data Odierna di Emissione
        "DataScadenza" => $dataScadenza,//Data in cui lo IUV si elimina
        "DataScadStampa" => $dataScadenza,//Data di scadenza che viene mostrata sul pdf
    );
    
    $soapPagoPA = new cls_pagoPA($customer['CityId'], 11, new CLS_DB(), PRODUCTION);
    $soapPagoPA->setRequest('create', $paramsGenera);
    
    trigger_error("Chiamata PagoPA Siscom per {$customer['SondrioServizio']} {$customer['SondrioSottoservizio']} verbale $fineId: " . $soapPagoPA->xml->getXml(), E_USER_NOTICE);
    
    $soapPagoPA->callRequest();
    
    if(!$soapPagoPA->xmlResponse){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        trigger_error("Risposta Siscom: " . $soapPagoPA->xmlResponse->getXml(), E_USER_NOTICE);
        
        if(!empty($soapPagoPA->a_response['IUV'])){
            trigger_error("IUV assegnato: " . $soapPagoPA->a_response['IUV'], E_USER_NOTICE);
            return $soapPagoPA->a_response['IUV'];
        } else {
            trigger_error("ERRORE: assegnazione fallita.", E_USER_WARNING);
        }
    }
    
    return null;
}
  
function callPagoPAEntraNext($fineId, $cityId, $auxDigit, $segAppCode, $numeroRata){
    return callPagoPAGeneric($fineId, $cityId, $auxDigit, $segAppCode, $numeroRata);
}
  
function callPagoPAGeneric(String $IUD, String $cityId, int $auxDigit, String $segAppCode = null, ?int $numeroRata){
    global $rs;
    $IUVCode = $prefix = null;
    $prefixList = unserialize(PAGOPA_PREFIX_LIST);
    
    try{
        if(strpos($IUD, $prefixList[PAGOPA_PREFIX_INSTALLMENT]) !== false){
            if(!is_null($numeroRata)){
                $prefix = '2'.str_pad($numeroRata, 2, 0, STR_PAD_LEFT);
                $IUD = str_replace($prefixList[PAGOPA_PREFIX_INSTALLMENT], '', $IUD);
            } else {
                trigger_error("Errore nella generazione del codice IUV per $IUD: per la generazione di IUV per rate è necessario specificare il numero rata.", E_USER_WARNING);
                return null;
            }
        } else if(strpos($IUD, $prefixList[PAGOPA_PREFIX_FINE_PARTIAL]) === false){
            $prefix = 1;
        } else {
            $prefix = 0;
            $IUD = str_replace($prefixList[PAGOPA_PREFIX_FINE_PARTIAL], '', $IUD);
        }
    
        $cls_iuv = new cls_iuv();
        $IUVCode = $cls_iuv->generateIUV($IUD, $auxDigit, $segAppCode, $prefix);
    } catch (Exception $e) {
        trigger_error("Errore nella generazione del codice IUV per $IUD: $e", E_USER_WARNING);
        return null;
    }
    
    if(!empty($IUVCode)){
        if(mysqli_num_rows($rs_PagoPAIUV = $rs->Select('PagoPAIUV', "IUVCode='$IUVCode'")) <= 0){
            $a_PagoPAIUV = array(
                array('field'=>'CityId', 'selector'=>'value', 'type'=>'str', 'value'=>$cityId),
                array('field'=>'IUVCode', 'selector'=>'value', 'type'=>'str', 'value'=>$IUVCode),
            );
            $rs->Insert('PagoPAIUV', $a_PagoPAIUV);
        } else {
            $r_PagoPAIUV = $rs->getArrayLine($rs_PagoPAIUV);
            trigger_error("Codice IUV $IUVCode già presente: {$r_PagoPAIUV['Id']}, {$r_PagoPAIUV['CityId']}, {$r_PagoPAIUV['RegDateTime']}", E_USER_WARNING);
            return null;
        }
    } else return null;
  
    return $IUVCode;
}
  
/**
 * @deprecated
 */
//NOTA: $a_Fees si basa sul return di calcolaImporti
function callFullPagoPA(int $typePrefix, array $service, $a_Fees, $feeIndex, $partialFeeIndex, $customer, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams){
    $iuvCodes = array();
    if ($partialFeeIndex != null){
        $iuvCodes[0] = callPagoPA($typePrefix, $service, $a_Fees, $partialFeeIndex, $customer, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams);
    }
    if ($feeIndex != null){
        $iuvCodes[1] = callPagoPA($typePrefix, $service, $a_Fees, $feeIndex, $customer, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams);
    }
    return $iuvCodes;
}

//NOTA: $a_Fees si basa sul return di calcolaImporti
function callPagoPA(int $typePrefix, array $service, $a_Fees, $feeIndex, $customer, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $numeroRata = null){
    $prefixList = unserialize(PAGOPA_PREFIX_LIST);
    if(!array_key_exists($typePrefix, $prefixList)) throw new Exception("Prefisso sconosciuto");
    
    $identificativoFiscalePAgatore = trim($identificativoFiscalePAgatore);
    $rs = new CLS_DB();
    $url = getServiceUrlFromId($rs, $service['Id'], 'Insert');
    
    $TotalFeeFineId = $idUnivocoDovuto;
    //Concateno il prefisso a $idUnivocoDovuto
    $idUnivocoDovuto = $prefixList[$typePrefix].$idUnivocoDovuto;

    $customerService_rs = $rs->Select("CustomerService", "CityId='{$customer['CityId']}' and ServiceId={$service['Id']}");
    $customerService = mysqli_fetch_array($customerService_rs);
    switch($service['Id']){
        case PAGOPA_GESTORE_LIGURIA:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}, Azione: Insert",E_USER_WARNING);
                return null;
            }
            return callPagoPALiguria($url, $a_Fees, $feeIndex, $customer['PagoPAAlias'], $TotalFeeFineId, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $customer['PagoPaIban'], $causale);
        
        case PAGOPA_GESTORE_MYPAY_LOMBARDIA:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}, Azione: Insert",E_USER_WARNING);
                return null;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}", E_USER_WARNING);
                return null;
            }
            return callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $customerService['Password'], 'I', CODICEIPA_COLLAUDO_LOMBARDIA);
        
        case PAGOPA_GESTORE_SONDRIO:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}, Azione: Insert",E_USER_WARNING);
                return null;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}", E_USER_WARNING);
                return null;
            }
            return callPagoPASondrio($url, $a_Fees, $feeIndex, $customer['CityId'], $customer['PagoPAPassword'], $TotalFeeFineId, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, date('Y-m-d'), $customer['SondrioServizio'], $customer['SondrioSottoservizio']);
        
        case PAGOPA_GESTORE_MYPAY_VENETO:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}, Azione: Insert",E_USER_WARNING);
                return null;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}", E_USER_WARNING);
                return null;
            }
            return callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $customerService['Password'], 'I', CODICEIPA_COLLAUDO_VENETO);
        
        case PAGOPA_GESTORE_NEXTSTEP:
            return callPagoPAEntraNext($fineId, $customer['CityId'], $customer['PagoPAAuxCode'], $customer['PagoPAApplicationCode'], $numeroRata);
        
        case PAGOPA_GESTORE_SISCOM:
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$customer['CityId']}", E_USER_WARNING);
                return null;
            }
            return callPagoPASiscom($a_Fees, $feeIndex, $fineId, $fineDate, $customer, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams);
            
        default:
            return null;
    }
}

/**
 * @deprecated
 */
//NOTA: $a_Fees si basa sul return di calcolaImporti
function updateFullPagoPA(int $typePrefix, array $service, $a_Fees, $feeIndex, $partialFeeIndex, $iuvCode1, $iuvCode2, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio){
    $iuvCodes = array();
    if ($partialFeeIndex != null){
        $iuvCodes[0] = updatePagoPA($typePrefix, $service, $a_Fees, $partialFeeIndex, $iuvCode1, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio);
    }
    if ($feeIndex != null){
        $iuvCodes[1] = updatePagoPA($typePrefix, $service, $a_Fees, $feeIndex, $iuvCode2, $idUnivocoDovuto, $dataScadenza, $tipoPagatore,  $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio);
    }
    return $iuvCodes;
}

function updatePagoPA(int $typePrefix, array $service, $a_Fees, $feeIndex, $iuvCode, $idUnivocoDovuto, $dataScadenza, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio, $numeroRata = null){
    $prefixList = unserialize(PAGOPA_PREFIX_LIST);
    if(!array_key_exists($typePrefix, $prefixList)) throw new Exception("Prefisso sconosciuto");
    
    global $r_Customer;
    $identificativoFiscalePAgatore = trim($identificativoFiscalePAgatore);
    $rs = new CLS_DB();
    $url = getServiceUrlFromId($rs, $service['Id'], 'Update');
    
    //Concateno il prefisso a $idUnivocoDovuto
    $idUnivocoDovuto = $prefixList[$typePrefix].$idUnivocoDovuto;
  
    switch($service['Id']){
        case PAGOPA_GESTORE_LIGURIA:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Update",E_USER_WARNING);
                return false;
            }
            return updatePagoPALiguria($url, $iuvCode, $a_Fees, $feeIndex);
          
        case PAGOPA_GESTORE_MYPAY_LOMBARDIA:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Update",E_USER_WARNING);
                return false;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, 'M', CODICEIPA_COLLAUDO_LOMBARDIA, $iuvCode);
          
        case PAGOPA_GESTORE_SONDRIO:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Update",E_USER_WARNING);
                return false;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return updatePagoPASondrio($url, $a_Fees, $feeIndex, $iuvCode, $r_Customer['CityId'], $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio);
          
        case PAGOPA_GESTORE_MYPAY_VENETO:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Update",E_USER_WARNING);
                return false;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, 'M', CODICEIPA_COLLAUDO_VENETO, $iuvCode);
          
        case PAGOPA_GESTORE_NEXTSTEP:
            return updatePagoPAEntraNext($a_Fees, $feeIndex, $iuvCode);
          
        case PAGOPA_GESTORE_SISCOM:
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return updatePagoPASiscom($a_Fees, $feeIndex, $fineId, $fineDate, $r_Customer, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $iuvCode);
            
        default:
            return false;
    }
}

/**
 * @deprecated
 */
function deleteFullPagoPA(int $typePrefix, array $service, $iuvCode1, $iuvCode2, $idUnivocoDovuto, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causaleAnnullamento, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio){
    $iuvCodes = array();
    //MK 20241118 da rev10144
    if ($partialFeeIndex != null){
        $iuvCodes[0] = deletePagoPA($typePrefix, $service, $iuvCode1, $idUnivocoDovuto, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causaleAnnullamento, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio);
    }
    if ($feeIndex != null){
        $iuvCodes[1] = deletePagoPA($typePrefix, $service, $iuvCode2, $idUnivocoDovuto, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causaleAnnullamento, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio);
    }
    //MK 20241118 fine da rev10144
    return $iuvCodes;
}

function deletePagoPA(int $typePrefix, array $service, $iuvCode, $idUnivocoDovuto, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causaleAnnulamento, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio, $numeroRata = null){
    $prefixList = unserialize(PAGOPA_PREFIX_LIST);
    if(!array_key_exists($typePrefix, $prefixList)) throw new Exception("Prefisso sconosciuto");
    
    global $r_Customer;
    $rs = new CLS_DB();
    
    $url = getServiceUrlFromId($rs, $service['Id'], 'Delete');
    
    //Concateno il prefisso a $idUnivocoDovuto
    $idUnivocoDovuto = $prefixList[$typePrefix].$idUnivocoDovuto;

    switch($service['Id']){
        case PAGOPA_GESTORE_LIGURIA:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Delete",E_USER_WARNING);
                return false;
            }
            return deletePagoPALiguria($url, $iuvCode, $causaleAnnulamento);
        
        case PAGOPA_GESTORE_MYPAY_LOMBARDIA:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Delete",E_USER_WARNING);
                return false;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, 'A', CODICEIPA_COLLAUDO_LOMBARDIA, $iuvCode);
            
        case PAGOPA_GESTORE_SONDRIO:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Delete",E_USER_WARNING);
                return false;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return deletePagoPASondrio($url, $r_Customer, $fineId, $iuvCode, $password, $servizio, $sottoServizio);
            
        case PAGOPA_GESTORE_MYPAY_VENETO:
            if(!$url){
                trigger_error("ERRORE: Nessuna url di chiamata al web service trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Delete",E_USER_WARNING);
                return false;
            }
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}", E_USER_WARNING);
                return false;
            }
            return callPagoPAMyPay($url, $a_Fees, $feeIndex, $fineId, $fineDate, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $password, 'A', CODICEIPA_COLLAUDO_VENETO, $iuvCode);
        
        case PAGOPA_GESTORE_NEXTSTEP:
            return deletePagoPAEntraNext($r_Customer, $iuvCode);
            
        case PAGOPA_GESTORE_SISCOM:
            if(empty($a_PagoPAServiceParams)) {
                trigger_error("Parametri PagoPA richiesti. Nessuna configurazione trovata per ServiceId: {$service['Id']}, Ente: {$r_Customer['CityId']}, Azione: Delete", E_USER_WARNING);
                return false;
            }
            return deletePagoPASiscom($r_Customer, $fineId, $iuvCode);
            
        default:
            return false;
    }
}

function updatePagoPALiguria($url, $iuvCode, $a_Fees, $feeIndex){
    $ch = curl_init($url);
    $importo = $a_Fees['Sum'][$feeIndex];
    $myvars = http_build_query(array('iuv' => "$iuvCode", 'importo' => $importo, 'causaleAnnullo' => ''));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $myvars);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    trigger_error("Update PagoPA Liguria per $iuvCode per l'importo $importo", E_USER_NOTICE);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)){
        trigger_error('Errore: '.curl_error($ch), E_USER_WARNING);
        return false;
    }
    
    trigger_error('Risposta: '.$response, E_USER_NOTICE);
    
    $obj_Payment = json_decode($response);
    
    if (! isset($obj_Payment) && ! isset($obj_Payment->result)){
        trigger_error("Errore nella decodifica della risposta: ".$obj_Payment, E_USER_WARNING);
    } else {
        if($obj_Payment->result == 1){
            trigger_error("IUV aggiornato: $iuvCode", E_USER_NOTICE);
            return true;
        } else {
            trigger_error("Errore nell'aggiornamento importo IUV: $iuvCode.", E_USER_WARNING);
        }
    }
    
    return false;
}

function updatePagoPASondrio($url, $a_Fees, $feeIndex, $iuvCode, $cityId, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $a_PagoPAServiceParams, $password, $servizio, $sottoServizio){
    $a_dati = rendiParametriImporto($a_Fees, $feeIndex, $a_PagoPAServiceParams, true);
    
    if(!$a_dati){
        return null;
    }
    
    $Nominativo = substr(trim($anagraficaPagatore['CompanyName'] . ' ' . $anagraficaPagatore['Surname'] . ' ' . $anagraficaPagatore['Name']), 0, 139);
    $Nominativo = str_replace("&", "E", $Nominativo);
    
    $tipoContabilità = $a_dati['Principale']['TipoContabilità'];
    $codiceContabilità = $a_dati['Principale']['CodiceContabilità'];
    $importo = $a_dati['Principale']['Importo'];
    
    $date = new DateTime();
    $createRequest = new CkXml();
    $createRequest->put_Tag('v1:IUVOnlineUpdateRequest');
    $createRequest->NewChild("testata|id_transazione", $date->getTimestamp());
    $createRequest->NewChild("testata|codice_servizio", $servizio);
    $createRequest->NewChild("testata|codice_sottoservizio", $sottoServizio);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_banca|codice_servizio", $servizio);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_banca|codice_sottoservizio", $sottoServizio);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_debitore_modifica|tipo_identificativo_univoco", $tipoPagatore);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_debitore_modifica|codice_debitore", $identificativoFiscalePAgatore);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_debitore_modifica|anagrafica_debitore", $Nominativo);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_pagamento_modifica|codice_identificativo_bollettino", $iuvCode, 18, "0");
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_pagamento_modifica|importo", $importo);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_pagamento_modifica|identificativo_disposizione", $iuvCode);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_pagamento_modifica|dati_specifici_riscossione|tipo_contabilita", $tipoContabilità);
    $createRequest->NewChild("IUVOnlineUpdateRequestData|informazioni_pagamento_modifica|dati_specifici_riscossione|codice_contabilita", $codiceContabilità);
    
    trigger_error("Update PagoPA Sondrio per $iuvCode", E_USER_NOTICE);
    
    $soapBody = createSoapXml($createRequest, null, array('xmlns:v1' => 'http://schema.iuvonline.nodospcit.ws.popso.it/v1'));
    
    $xmlResp = callSoapUrlWithSoapBody($url, "POST", $soapBody, 'http://scrittura.iuvonline.nodospcit.ws.popso.it/v1/IUVOnlineUpdate', CERT_PATH."sondrio$cityId.pfx", $password, "text/xml");
    
    if (!$xmlResp){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        trigger_error("Risposta Sondrio: " . $xmlResp->getXml(), E_USER_NOTICE);
        
        $found = $xmlResp->SearchForTag($xmlResp, 'codice_identificativo_bollettino');
        
        if ($found){
            trigger_error("IUV aggiornato: " . $found->content(), E_USER_NOTICE);
            return true;
        } else {
            trigger_error("ERRORE: aggiornamento fallito.", E_USER_NOTICE);
        }
    }
    
    return false;
}

function updatePagoPASiscom($a_Fees, $feeIndex, $fineId, $fineDate, $r_Customer, $tipoPagatore, $anagraficaPagatore, $identificativoFiscalePAgatore, $causale, $a_PagoPAServiceParams, $iuvCode){
    $a_dati = rendiParametriImporto($a_Fees, $feeIndex, $a_PagoPAServiceParams, true);
    
    $tipoContabilità = $a_dati['Principale']['TipoContabilità'];
    $codiceContabilità = $a_dati['Principale']['CodiceContabilità'];
    
    $Nominativo = substr(trim($anagraficaPagatore['CompanyName'] . ' ' . $anagraficaPagatore['Surname'] . ' ' . $anagraficaPagatore['Name']), 0, 139);
    $Nominativo = str_replace("&", "E", $Nominativo);
    $Nome = trim($anagraficaPagatore['Name']);
    $Cognome = trim($anagraficaPagatore['Surname']);
    
    $dataScadenza = date('Y-m-d', strtotime($fineDate. ' + '. EXPIRATION_PERIOD));
    trigger_error("Data scadenza: $dataScadenza");
    
    $paramsModifica = array(
        "ContTipoContabilita" => PRODUCTION ? $tipoContabilità : 9,
        "ContCodiceContabilita" => PRODUCTION ? $codiceContabilità : '',
        "CodiceServizio" => PRODUCTION ? $r_Customer['SondrioServizio'] : 1,
        "pCodFiscaleEnte" => PRODUCTION ? $r_Customer['ManagerTaxCode'] : '12345678901',
        "RifEnte" => PRODUCTION ? $r_Customer['ManagerTaxCode'] : '12345678901',
        "ProceduraChiave" => $fineId,
        "PagamentoImporto" => $a_Fees['Sum'][$feeIndex],
        "IUV" => $iuvCode,
        
        "DebitNatura" => $tipoPagatore,//F (fisica) o G (giuridica) OBBLIGATORIO
        
        "DebitRagSocCognome" => $tipoPagatore == 'G' ? $Nominativo : $Cognome,
        "DebitNome" => $tipoPagatore == 'F' ? $Nome : '',//Nome
        "PagamentoCausale" => $causale,
        "DebitCodFiscale" => $identificativoFiscalePAgatore,//CF
        
        "DataEmissione" => date('Y-m-d'),//Data Odierna di Emissione
        "DataScadenza" => $dataScadenza,//Data in cui lo IUV si elimina
        "DataScadStampa" => $dataScadenza,//Data di scadenza che viene mostrata sul pdf
    );
    
    $soapPagoPA = new cls_pagoPA($r_Customer['CityId'], 11, new CLS_DB(), PRODUCTION);
    $soapPagoPA->setRequest('update', $paramsModifica);
    
    trigger_error("Chiamata PagoPA Siscom per {$r_Customer['SondrioServizio']} {$r_Customer['SondrioSottoservizio']} verbale $fineId: " . $soapPagoPA->xml->getXml(), E_USER_NOTICE);
    
    $soapPagoPA->callRequest();
    
    if(!$soapPagoPA->xmlResponse){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        trigger_error("Risposta Siscom: " . $soapPagoPA->xmlResponse->getXml(), E_USER_NOTICE);
        
        if(!empty($soapPagoPA->a_response['IUV'])){
            trigger_error("IUV aggiornato: " . $soapPagoPA->a_response['IUV']." Importo: ".$a_Fees['Sum'][$feeIndex], E_USER_NOTICE);
            return $soapPagoPA->a_response['IUV'];
        } else {
            trigger_error("ERRORE: aggiornamento fallito.", E_USER_WARNING);
        }
    }
    
    return false;
}
  
function updatePagoPAEntraNext($a_Fees, $feeIndex, $iuvCode){
    //visto che per EntraNext non vengono chiamati servizi esterni non serve chiedere l'allineamento degli importi associati allo Iuv su un sistema esterno.
    //restituiamo Iuv per uniformità con il resto
    $importo = $a_Fees['Sum'][$feeIndex];
    trigger_error("Update PagoPA EntraNext per $iuvCode per l'importo $importo", E_USER_NOTICE);
    return $iuvCode;
}

function deletePagoPALiguria(string $url, string $iuvCode, string $causale = ''){
    $ch = curl_init($url);
    $myvars = http_build_query(array('iuv' => $iuvCode, 'causaleAnnullo' => $causale));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $myvars);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    trigger_error("Delete PagoPA Liguria per $iuvCode", E_USER_NOTICE);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)){
        trigger_error('Errore: '.curl_error($ch), E_USER_WARNING);
        return false;
    }
    
    trigger_error('Risposta: '.$response, E_USER_NOTICE);
    
    $obj_Payment = json_decode($response);
    
    if (! isset($obj_Payment) && ! isset($obj_Payment->result)){
        trigger_error("Errore nella decodifica della risposta: ".$obj_Payment, E_USER_WARNING);
    } else {
        if($obj_Payment->result == 1){
            trigger_error("IUV anullato: $iuvCode", E_USER_NOTICE);
            return true;
        } else {
            trigger_error("IUV $iuvCode risulta già annullato.", E_USER_WARNING);
        }
    }
    
    return false;
}

function deletePagoPASondrio($url, $customer, $fineId, $iuvCode, $password, $servizio, $sottoServizio){
    $id_transazione = time().'-'.mt_rand();
    $createRequest = new CkXml();
    $createRequest->put_Tag('v1:IUVOnlineDeleteRequest');
    $createRequest->NewChild("testata|id_transazione", $id_transazione);
    $createRequest->NewChild("testata|codice_servizio", $servizio);
    $createRequest->NewChild("testata|codice_sottoservizio", $sottoServizio);
    
    $codiceIdentificativoBollettino = $iuvCode;
    if($customer['IsIuvCodiceAvviso'] <= 0){
        $cls_iuv = new cls_iuv();
        try{
            $codiceIdentificativoBollettino = $cls_iuv->generateNoticeCode($iuvCode, $customer['PagoPAAuxCode'], $customer['PagoPAApplicationCode'] ?: null);
        } catch (Exception $e){
            trigger_error("Errore di conversione codice IUV: $e");
            return false;
        }
    }
    
    $createRequest->NewChild("IUVOnlineDeleteRequestData|codice_identificativo_bollettino", $codiceIdentificativoBollettino);
    $soapBody = createSoapXml($createRequest, null, array('xmlns:v1' => 'http://schema.iuvonline.nodospcit.ws.popso.it/v1'));
    
    trigger_error("Chiamata PagoPA Sondrio per $servizio $sottoServizio  verbale $fineId: " . $createRequest->getXml(), E_USER_NOTICE);
    
    $xmlResp = callSoapUrlWithSoapBody($url, "POST", $soapBody, 'http://scrittura.iuvonline.nodospcit.ws.popso.it/v1/IUVOnlineDelete', CERT_PATH."sondrio{$customer['CityId']}.pfx", $password, "text/xml");
    
    if (!$xmlResp){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        if($xmlResp->SearchForTag($xmlResp, 'timestamp_annullamento')){
            trigger_error("IUV anullato: $iuvCode", E_USER_NOTICE);
            return true;
        } else {
            $faultString = $xmlResp->SearchForTag($xmlResp, 'faultstring');
            trigger_error("Errore: ".($faultString ? $faultString->content() : "Errore sconosciuto"), E_USER_WARNING);
        }
        trigger_error("Risposta Sondrio: " . $xmlResp->getXml(), E_USER_NOTICE);
    }
    
    return false;
}

function deletePagoPAEntraNext($customer, $iuvCode){
    return deletePagoPAGeneric($customer, $iuvCode);
}

function deletePagoPAGeneric($customer, $iuvCode){
    global $rs;
    //visto che per EntraNext non vengono chiamati servizi esterni non serve chiedere l'allineamento degli importi associati allo Iuv su un sistema esterno.
    //impostiamo lo IUV come cancellato
    if(!empty($iuvCode)){
        trigger_error("Delete PagoPA EntraNext per $iuvCode", E_USER_NOTICE);
        $rs->Delete('PagoPAIUV', "CityId= '{$r_Customer['CityId']}' AND IUVCode='$iuvCode'");
        return true;
    } else {
        trigger_error("Errore: lo IUV passato è vuoto.", E_USER_NOTICE);
    }
    
    return false;
}

function deletePagoPASiscom($r_Customer, $fineId, $iuvCode){
    $paramsDelete = array(
        "pCodFiscaleEnte" => PRODUCTION ? $r_Customer['ManagerTaxCode'] : '12345678901',//Codice fiscale ente OBBLIGATORIO
        "pIUVMovimento" => $iuvCode,//IUV da leggere
        "pCodiceServizio" => PRODUCTION ? $r_Customer['SondrioServizio'] : 1,
        "pOpeAnnulla" => null,
    );
    
    $soapPagoPA = new cls_pagoPA($r_Customer['CityId'], 11, new CLS_DB(), PRODUCTION);
    $soapPagoPA->setRequest('delete', $paramsDelete);
    
    trigger_error("Chiamata PagoPA Siscom per servizio: {$r_Customer['SondrioServizio']} {$r_Customer['SondrioSottoservizio']} verbale $fineId: " . $soapPagoPA->xml->getXml(), E_USER_NOTICE);
    
    $soapPagoPA->callRequest();
    
    if(!$soapPagoPA->xmlResponse){
        trigger_error("Risposta vuota.", E_USER_WARNING);
    } else {
        trigger_error("Risposta Siscom: " . $soapPagoPA->xmlResponse->getXml(), E_USER_NOTICE);
        
        $stato = $soapPagoPA->xmlResponse->SearchForTag($soapPagoPA->xmlResponse, 'SiscStato');
        
        if($stato){
            if($stato->content() == SISCOM_STATO_IUVANNULLATO){
                trigger_error("IUV anullato: $iuvCode", E_USER_NOTICE);
                return true;
            } else {
                $descrizione = $soapPagoPA->xmlResponse->SearchForTag($soapPagoPA->xmlResponse, 'RisDescrizione');
                trigger_error("Errore annullamento IUV $iuvCode, codice: ".$stato->content()." descrizione: ".($descrizione ? $descrizione->content() : "Iuv già annullato o errore generico."), E_USER_WARNING);
            }
        } else {
            trigger_error("Errore annullamento IUV $iuvCode, vedere risposta.", E_USER_WARNING);
        }
    }
    
    return false;
}

function pickPagoPAPaymentUrl($id,array $parameters = array()){
    switch ($id){
        case 1:
            return impostaParametriUrl(array('iuv' => $parameters['iuv']), PRODUCTION ? PAGOPAPAYMENT_LIGURIA_PROD : PAGOPAPAYMENT_LIGURIA_COLL);
            break;
            
        case 2:
            return PRODUCTION ? PAGOPAPAYMENT_LOMBARDIA_PROD : PAGOPAPAYMENT_LOMBARDIA_COLL;
            break;
            
        case 3:
            return PRODUCTION ? PAGOPAPAYMENT_SONDRIO_PROD : PAGOPAPAYMENT_SONDRIO_COLL;
            break;
            
        case 9:
            return PRODUCTION ? PAGOPAPAYMENT_VENETO_PROD : PAGOPAPAYMENT_VENETO_COLL;
            break;
            
        case 10:
            return PRODUCTION ? PAGOPAPAYMENT_ENTRANEXT_PROD : PAGOPAPAYMENT_ENTRANEXT_COLL;
            break;
            
        default:
            return '';
    }
}

function creaEventoInformativo($rs,$cityId,$title,$message,$severity)
    {
        $a_Insert = array(
            array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $title),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $cityId),
            array('field' => 'Message', 'selector' => 'value', 'type' => 'str', 'value' => $message),
            array('field' => 'Severity', 'selector' => 'value', 'type' => 'str', 'value' => $severity),
            array('field' => 'EventDate', 'selector' => 'value', 'type' => 'str', 'value' => date('Y-m-d H:i:s')),
        );
        
        $rs->Insert('Events', $a_Insert);
    }
?>