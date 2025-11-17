<?php
require_once(TCPDF."/tcpdf.php");
require_once(CLS."/cls_pdf.php");
require_once(CLS."/cls_iuv.php");
require_once(INC."/pagopa.php");
require_once(CLS."/pagopaER/PagoPAERConst.php");
require_once(CLS."/pagopaER/Articolo.php");
require_once(CLS."/pagopaER/Scorporo.php");
require_once(CLS."/pagopaER/SoggettoPM.php");
require_once(CLS."/pagopaER/Verbale.php");
require_once(CLS."/pagopaER/VerbaleSoggetto.php");
require_once(CLS."/pagopaER/IdentificativoUnivoco.php");
require_once(CLS."/pagopaER/RendiContazionePagamento.php");
require_once(CLS."/pagopaER/response/VerificaVerbaleResponse.php");
require_once(CLS."/pagopaER/response/RiceviVerbaliResponse.php");
require_once(CLS."/pagopaER/response/NotificaPagamentoVerbaleResponse.php");
require_once(CLS."/pagopaER/response/InviaPagamentoResponse.php");
require_once(CLS."/pagopaER/response/VerificaInvioPagamentoResponse.php");
require_once(CLS."/pagopaER/response/RiceviStatoRiconciliazionePagamentoResponse.php");

class PagoPAERService
{
    /** @var CLS_DB $db */
    
    private $db;
    private $trespasserid;
    private $paymentRateId;
    private $customer;
    private $esito;
    private $esitoDescrizione;
    private $IUVScaduto = false;

    public $wsdl;
    public $location;
    public $metodoChiamato;

    public function __construct($wsdl,$location,$db){
        $this->wsdl=$wsdl;
        $this->location=$location;
        $this->db=$db;
    }

    public function AvviaServer():SoapServer{
        $server = new SoapServer($this->wsdl,array(
            'soap_version' => PagoPAERConst::SOAP_VERSION,
            "exceptions" => true,  
            'uri' => $this->location));
        $server->setObject($this);
        return $server;
    }

    private function f($number){
        return $number > 0 ? number_format($number,2,'.','') : 0;
    }
    
    private function ValidaData($date, $format = 'Y-m-d H:i:s'){
        if(DateTime::createFromFormat($format, $date)){
            return true;
        } else {
            $this->Log('W',__FUNCTION__, "Errore nella validazione: Data: $date, Formato: $format");
            return false;
        }
    }
    
    private function ValidaXml($xml){
        $parser = xml_parser_create("UTF-8");
        if(xml_parse($parser, $xml, true) == 1){
            return true;
        } else {
            $this->Log('W',__FUNCTION__, 'Errore XML: '.xml_error_string(xml_get_error_code($parser)));
            return false;
        }
    }
    
    private function ValidaGUID($GUID){
        return preg_match('/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?$/', $GUID) > 0;
    }
    
    private function EsitoImposta(int $esito, ?String $descrizione = null){
        $this->esito = $esito;
        $this->esitoDescrizione = $descrizione;
    }
    
    private function ValutaRisposta(){
        switch (http_response_code()){
            case 401:
                $this->EsitoImposta(7, 'Autenticazione fallita.');
            return false;
            case 500:
                $this->EsitoImposta(7, 'Errore interno.');
                return false;
            default:
                return true;
        }
    }
    
    public function Log(?String $tipo, ?String $nomeMetodo, ?String $messaggio){
        switch($tipo){
            case 'N': trigger_error("<PAGOPAERSERVICE: $nomeMetodo> DEBUG -> $messaggio", E_USER_NOTICE); break;
            case 'W': trigger_error("<PAGOPAERSERVICE: $nomeMetodo> ATTENZIONE -> $messaggio", E_USER_WARNING); break;
            case 'D': trigger_error("<PAGOPAERSERVICE: $nomeMetodo> ERRORE -> $messaggio", E_USER_WARNING); break;
            default : trigger_error("<PAGOPAERSERVICE: $nomeMetodo> DEBUG -> $messaggio", E_USER_NOTICE); break;
        }
    }
    
    private function RiempiVerbaleDaBD(String $keyType, array $a_VerbBD, Verbale $verbaleOgg, $importoInput = 0, $trespasserId = null):Verbale{
        if(!empty($a_VerbBD)){
            $FineReminder = $PaymentRate = $PaymentRateNumberIUV = null;
            $b_PagamentoRidotto = $b_IUVPagato = false;
            $a_Scorpori = $a_Articoli = $a_Spese = $a_Pago = array();
            $a_ProtocolLetterLocality = array();
            $f_ImportoPagato = $f_PagateSanzione = $f_SpesePagateNotifica = $f_SpesePagateVarie = $f_PagateMaggSemestrale = $f_ImportoEffettivo = $f_SanzioneMassima = $f_SanzioneMinima = $f_SanzioneRidotta = $f_SpeseNotifica = $f_SpeseVarie = $f_MaggSemestrale = 0;
            $d_DataUltimoPagamento = null;
            $b_IUVRidotto = $b_IUVRata = false;
            
            //Determina se c'è un sollecito
            if(!empty($a_VerbBD['ReminderDate'])){
                $FineReminder =             $this->db->getArrayLine($this->db->Select('FineReminder', "FineId={$a_VerbBD['Id']} AND PrintDate='{$a_VerbBD['ReminderDate']}' AND FlowDate IS NOT NULL"));
            }
            //Determina se c'è una rateizzazione aperta
            $PaymentRate =                  $this->db->getArrayLine($this->db->Select("PaymentRate", "FineId={$a_VerbBD['Id']} AND StatusRateId = ".RATEIZZAZIONE_APERTA));
                
            //indica se la ricerca è per Iuv o meno
            $b_RicercaIUV = $keyType == PagoPAERConst::KEYT_IUV;
            //indica che lo Iuv in riechiesta è quello per il pagamento ridotto/minimo del verbale
            $b_IUVRidotto = !empty($verbaleOgg->getIUV()) && $verbaleOgg->getIUV() == $a_VerbBD['PagoPA1'] && empty($FineReminder);
            //indica che lo Iuv in riechiesta è quello per il pagamento di una rata
            $b_IUVRata = $this->paymentRateId > 0;

            $VehicleType=                   $this->db->getArrayLine($this->db->Select("VehicleType", "Id={$a_VerbBD['VehicleTypeId']}"));
            $FineArticle=                   $this->db->getArrayLine($this->db->Select("FineArticle","FineId={$a_VerbBD['Id']}"));
            $FineArchive=                   $this->db->getArrayLine($this->db->Select("FineArchive", "FineId={$a_VerbBD['Id']}"));
            $FinePaymenNotification=        $this->db->getArrayLine($this->db->Select("FinePaymentNotification","FineId={$a_VerbBD['Id']} and Deleted = 0"));
            //$FineHistoryCancellation=       $this->db->getArrayLine($this->db->Select("FineHistory","FineId={$a_VerbBD['Id']} and NotificationTypeId=10"));
            //$FineHistoryArchiviation=       $this->db->getArrayLine($this->db->Select("FineHistory","FineId={$a_VerbBD['Id']} and NotificationTypeId=11"));
            $Customer=                      $this->db->getArrayLine($this->db->Select("V_Customer","CityId='{$a_VerbBD['CityId']}'"));
            $StatusType=                    $this->db->getArrayLine($this->db->Select("StatusType","Id={$a_VerbBD['StatusTypeId']}"));
            $Article=                       $this->db->getArrayLine($this->db->Select("Article","Id={$FineArticle['ArticleId']}"));
            $ArticleTariff=                 $this->db->getArrayLine($this->db->Select("ArticleTariff","ArticleId={$FineArticle['ArticleId']} and Year={$a_VerbBD['ProtocolYear']}"));
            $AdditionalSanction=            $this->db->getArrayLine($this->db->Select("AdditionalSanction","Id={$ArticleTariff['AdditionalSanctionId']}"));
            $ViolationType=                 $this->db->getArrayLine($this->db->Select("ViolationType", "Id={$FineArticle['ViolationTypeId']}"));
            $ViolationTypeLetter=           $this->db->getArrayLine($this->db->Select("ViolationTypeLetter", "ViolationTypeId={$FineArticle['ViolationTypeId']} and CityId='{$a_VerbBD['CityId']}'"));
            $RuleType =                     $this->db->getArrayLine($this->db->Select('V_RuleType', "ViolationTypeId={$FineArticle['ViolationTypeId']} AND CityId='{$a_VerbBD['CityId']}'"));
            
            $rs_ProtocolLetter =            $this->db->Select(MAIN_DB.'.City', ($Customer['CityUnion'] > 1) ? "UnionId='{$a_VerbBD['CityId']}'" : "Id='{$a_VerbBD['CityId']}'");
            $rs_AdditionalArticle =         $this->db->Select('V_AdditionalArticle', "FineId=" . $a_VerbBD['Id'], "ArticleOrder");
            $rs_FineHistoryFlow =           $this->db->Select("FineHistory","FineId={$a_VerbBD['Id']} and NotificationTypeId in (6)");
            $rs_FineHistoryCreation =       $this->db->Select("FineHistory","FineId={$a_VerbBD['Id']} and NotificationTypeId in (2,15)");
            
            $a_FineHistoryCreation=         array_column($this->db->getResults($rs_FineHistoryCreation), null, 'TrespasserId');
            $a_FineHistoryFlow=             array_column($this->db->getResults($rs_FineHistoryFlow), null, 'TrespasserId'); //anche i flussi PEC hanno NotificationTypeId 6 e su quello viene salvato l'esito della notifica
            
            if($a_VerbBD['FineChiefControllerId']!=null){
                $Controller=                $this->db->getArrayLine($this->db->select("Controller","Id={$a_VerbBD['FineChiefControllerId']}"));
            }
            //Se stò interrogando il servizio con lo iuv di una rata, prelevo i dati dalla banca dati e altero la gestione
            if($this->paymentRateId > 0){
                $PaymentRateNumberIUV =     $this->db->getArrayLine($this->db->Select("PaymentRateNumber", "PagoPAIUV='{$verbaleOgg->getIUV()}'"));
                
                $ImportReceiptNumber = $this->customer['IsIuvCodiceAvviso'] == 1
                ? $verbaleOgg->getIUV()
                : $this->ConvertiIUV($verbaleOgg->getIUV(), true);
                
                $rs_FinePayment=            $this->db->Select("FinePayment","FineId={$a_VerbBD['Id']} AND ImportReceiptNumber='$ImportReceiptNumber'", "Id DESC");
            } else {
                $rs_FinePayment=            $this->db->Select("FinePayment","FineId={$a_VerbBD['Id']}", "Id DESC");
            }
            
            if($trespasserId){
                $Trespasser=                $this->db->getArrayLine($this->db->Select("Trespasser", "Id={$trespasserId}"));
                $ProcessingPagoPA =         $this->db->getArrayLine($this->db->Select("V_ViolationPagoPA","Id='{$a_VerbBD['Id']}' AND TrespasserId={$trespasserId}"));
                
                //Verbale creato (flusso)
                if(!empty($a_FineHistoryFlow)){
                    //Sollecito
                    if(!empty($FineReminder)){
                        $ProcessingPagoPA['NotificationFee'] = $a_Spese['NotificationFee'] = $FineReminder['TotalNotification'] + $FineReminder['NotificationFee'] - $a_FineHistoryFlow[$trespasserId]['ResearchFee'];
                        $ProcessingPagoPA['ResearchFee'] = $a_Spese['ResearchFee'] = $a_FineHistoryFlow[$trespasserId]['ResearchFee'];
                        $ProcessingPagoPA['CustomerFee'] = $a_Spese['CustomerFee'] = $FineReminder['CustomerFee'];
                        $a_Spese['PercentualFee'] = $FineReminder['PercentualAmount'];
                        
                        $a_Pago =               calcolaImportiSulVerbale($ProcessingPagoPA, $rs_AdditionalArticle, $ArticleTariff, $Customer);
                        $a_Pago['Total'] = $FineReminder['TotalAmount'];
                    } else {
                        $ProcessingPagoPA['NotificationFee'] = $a_Spese['NotificationFee'] = $a_FineHistoryFlow[$trespasserId]['NotificationFee'];
                        $ProcessingPagoPA['ResearchFee'] = $a_Spese['ResearchFee'] = $a_FineHistoryFlow[$trespasserId]['ResearchFee'];
                        $ProcessingPagoPA['CustomerFee'] = $a_Spese['CustomerFee'] = $a_FineHistoryFlow[$trespasserId]['CustomerFee'];
                        
                        $a_Pago =               calcolaImportiSulVerbale($ProcessingPagoPA, $rs_AdditionalArticle, $ArticleTariff, $Customer);
                    }
                //Verbale creato
                } else if(!empty($a_FineHistoryCreation)) {
                    $ProcessingPagoPA['NotificationFee'] = $a_Spese['NotificationFee'] = $a_FineHistoryCreation[$trespasserId]['NotificationFee'];
                    $ProcessingPagoPA['ResearchFee'] = $a_Spese['ResearchFee'] = $a_FineHistoryCreation[$trespasserId]['ResearchFee'];
                    $ProcessingPagoPA['CustomerFee'] = $a_Spese['CustomerFee'] = $a_FineHistoryCreation[$trespasserId]['CustomerFee'];
                    
                    $a_Pago =               calcolaImportiSulVerbale($ProcessingPagoPA, $rs_AdditionalArticle, $ArticleTariff, $Customer);
                //Preinserimento
                } else {
                    $a_Spese =              getFineFees($this->db, $Customer, $a_VerbBD['Id'], $ArticleTariff, '', 1, 0, date('Y-m-d'), 0, false, $a_VerbBD['CityId']);
                    $a_Pago =               calcolaImporti($ProcessingPagoPA, $rs_AdditionalArticle, $ArticleTariff, $Customer, date('Y-m-d'), 0, $Trespasser['PEC'] ?? null, $this->customer['CityId'], $this->db);
                    $a_Pago =               $a_Pago['Sum'];
                }
                
                $this->Log('N', 'A_PAGO', print_r($a_Pago, true));
                
                mysqli_data_seek($rs_AdditionalArticle, 0);
            }
            
            
            //ARTICOLO
            if($Article){
                $o_Articolo = new Articolo();
                $o_Articolo->setCodiceArticolo($Article['Article']);
                $o_Articolo->setComma($Article['Paragraph']);
                $o_Articolo->setDescrizioneArticolo(StringOutDB($Article['DescriptionIta']));
                $o_Articolo->setSanzioneMinima($this->f($ArticleTariff['Fee']));
                $o_Articolo->setSanzioneRidotta30Perc($this->f($ArticleTariff['ReducedPayment']
                    ? $ArticleTariff['Fee'] * FINE_PARTIAL
                    : $ArticleTariff['Fee']));
                $o_Articolo->setSanzioneRuolo($this->f($ArticleTariff['MaxFee']));
                
                $b_PagamentoRidotto = $ArticleTariff['ReducedPayment'] == 1 ? true : false;
                
                array_push($a_Articoli, $o_Articolo);
            }
            
            //ARTICOLI ADDIZIONALI
            while($FineAdditionalArticle = $this->db->getArrayLine($rs_AdditionalArticle)){
                $o_Articolo = new Articolo();

                $o_Articolo->setCodiceArticolo($FineAdditionalArticle['Article']);
                $o_Articolo->setComma($FineAdditionalArticle['Paragraph']);
                $o_Articolo->setDescrizioneArticolo($FineAdditionalArticle['ArticleDescriptionIta']);
                $o_Articolo->setSanzioneMinima($this->f($FineAdditionalArticle['Fee']));
                $o_Articolo->setSanzioneRidotta30Perc($this->f($FineAdditionalArticle['ReducedPayment']
                    ? $FineAdditionalArticle['Fee'] * FINE_PARTIAL
                    : $FineAdditionalArticle['Fee']));
                $o_Articolo->setSanzioneRuolo($this->f($FineAdditionalArticle['MaxFee']));
                
                array_push($a_Articoli, $o_Articolo);
            }


            //PAGAMENTI
            while($FinePayment = $this->db->getArrayLine($rs_FinePayment)){
                $d_DataUltimoPagamento = $d_DataUltimoPagamento ?? $FinePayment['PaymentDate'];
                $f_ImportoPagato += $FinePayment['Amount'];
                $f_SpesePagateNotifica += $FinePayment['NotificationFee'];
                $f_PagateMaggSemestrale += $FinePayment['PercentualFee'];
                $f_PagateSanzione += $FinePayment['Fee'];
                $f_SpesePagateVarie += (
                    $FinePayment['ResearchFee']+
                    $FinePayment['CanFee']+
                    $FinePayment['CadFee']+
                    $FinePayment['CustomerFee']+
                    $FinePayment['OfficeNotificationFee']);
            }
            
            //VERBALE
            $verbaleOgg->setVerbaleStraniero($a_VerbBD['VehicleCountry'] == 'Italia' ? false : true);
            $verbaleOgg->setDataOraViolazione(isset($a_VerbBD['FineDate'],$a_VerbBD['FineTime'])
                ? $a_VerbBD['FineDate']."T".$a_VerbBD['FineTime']
                : null);
            $verbaleOgg->setDataOraAccertamento(isset($a_VerbBD['ControllerDate'],$a_VerbBD['ControllerTime'])
                ? $a_VerbBD['ControllerDate']."T".$a_VerbBD['ControllerTime']
                : $a_VerbBD['FineDate']."T".$a_VerbBD['FineTime']);
            $verbaleOgg->setCodiceEnte($a_VerbBD['CityId']);
            $verbaleOgg->setAnno($a_VerbBD['ProtocolYear']);
            $verbaleOgg->setNumeroVerbale($a_VerbBD['ProtocolId']);
            
            //SERIE
            while ($r_ProtocolLetter = $this->db->getArrayLine($rs_ProtocolLetter)) {
                $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
                $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
            }
            
            if(trim($Article['ArticleLetterAssigned'])!=''){
                $Serie = $Article['ArticleLetterAssigned'];
            } else if(!empty($ViolationTypeLetter) && trim($ViolationTypeLetter['ViolationLetterAssigned'])!=''){
                $Serie = $ViolationTypeLetter['ViolationLetterAssigned'];
            } else {
                $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$a_VerbBD['Locality']]['NationalProtocolLetterType1'];
                $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$a_VerbBD['Locality']]['NationalProtocolLetterType2'];
                $Serie = ($RuleType['Id'] == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            }
            
            $verbaleOgg->setTarga($a_VerbBD['VehiclePlate']);
            $verbaleOgg->setSerie($Serie);
            $verbaleOgg->setLuogoInfrazione($a_VerbBD['Address']);
            $verbaleOgg->setMarca($a_VerbBD['VehicleBrand']);
            $verbaleOgg->setModello($a_VerbBD['VehicleModel']);
            $verbaleOgg->setColore($a_VerbBD['VehicleColor']);
            $verbaleOgg->setSanzioniAccessorie($AdditionalSanction['TitleIta'] ?? '');
            $verbaleOgg->setTipoVeicolo($VehicleType['TitleIta']);
            $verbaleOgg->setTipoViolazione($ViolationType['Title']);
            $verbaleOgg->setStato($StatusType['Title']);
            $verbaleOgg->setAgentiVerbalizzanti($Controller['Name'] ?? '');
            $verbaleOgg->setDataUltimoPagamento($d_DataUltimoPagamento);
            $verbaleOgg->setImportoPagato($this->f($f_ImportoPagato));

            //TODO capire su cosa basarsi per estrarre le date di archiviazione e annullamento
            //if($FineHistoryCancellation!=null) $verbaleOgg->DataAnnullamento=$FineHistoryCancellation['NotificationDate'];
            //if($FineHistoryArchiviation!=null) $verbaleOgg->DataArchiviazione=$FineHistoryArchiviation['NotificationDate'];
            
            $verbaleOgg->setDataNotifica($this->DeterminaDataNotifica($rs_FineHistoryFlow));
            
            if(!empty($a_Pago)){
                $f_SanzioneRidotta = !$b_RicercaIUV ? ($b_PagamentoRidotto ? $a_Pago['ReducedPartial'] : $a_Pago['Partial']) : ($a_VerbBD['PagoPAReducedPartial'] ?? 0);
                $f_SanzioneMinima = !$b_RicercaIUV ? $a_Pago['Partial'] : ($a_VerbBD['PagoPAPartial'] ?? 0);
                $f_SanzioneMassima = !$b_RicercaIUV ? $a_Pago['Total'] : ((empty($FineReminder) ? $a_VerbBD['PagoPATotal'] : $a_Pago['Total']) ?? 0);
            }
            
            if($PaymentRateNumberIUV){
                //In caso di rata, la data limite è la data limite pagamento, la data calcolo è la data odierna
                $dataCalcolo = date('Y-m-d');
                $dataLimite = $PaymentRateNumberIUV['PaymentDate'];
            } else {
                //in assenza di notifica si rientra nei casi che assegnano ad ImportoIuv gli importi minimi
                //che sono quelli validi al momento immediatamente successivo all'avvenuta notifica
                $dataCalcolo = $verbaleOgg->getDataNotifica() ?: date('Y-m-d');
                $dataLimite = date('Y-m-d');
            }

            $str_TipoSanzione = $this->DeterminaTipoSanzione($dataCalcolo, $dataLimite, $b_RicercaIUV, $b_IUVRidotto, $b_PagamentoRidotto, !empty($FineReminder), $b_IUVRata, !empty($PaymentRate));
            
            if(!empty($PaymentRateNumberIUV)){
                $f_ImportoEffettivo = $PaymentRateNumberIUV['Amount']-$f_ImportoPagato;
            } else {
                if(!empty($PaymentRate)){
                    $f_ImportoEffettivo = $PaymentRate['InstalmentAmount']-$f_ImportoPagato;
                } else if(!empty($FineReminder)){
                    $f_ImportoEffettivo = $FineReminder['TotalAmount']-$f_ImportoPagato;
                } else {
                    switch($str_TipoSanzione){
                        case 'Ridotta':
                            $f_ImportoEffettivo = $this->f($f_SanzioneRidotta-$f_ImportoPagato);
                            break;
                        case 'Minima':
                            $f_ImportoEffettivo = $this->f($f_SanzioneMinima-$f_ImportoPagato);
                            break;
                        case 'Massima':
                            $f_ImportoEffettivo = $this->f($f_SanzioneMassima-$f_ImportoPagato);
                            break;
                    }
                }
            }

            
            $verbaleOgg->setImportoIUV($b_RicercaIUV ? ($f_ImportoEffettivo) : 0);
            
            //NOTA: campi introdotti da versione 1.6.5
            //abbiamo aggiunto IUV_ImportoRidotto, IUV_ImportoMinimo e IUV_ImportoARuolo, vanno valorizzati sencondo le stesse logiche degli importi e servono per la funzione RiceviVerbaliSoggetto
            //TODO verificare 
            $verbaleOgg->setImportoRidotto(0);
            $verbaleOgg->setIUV_ImportoRidotto(0);
            $verbaleOgg->setImportoMinimo(0);
            $verbaleOgg->setIUV_ImportoMinimo(0);
            $verbaleOgg->setImportoARuolo(0);
            $verbaleOgg->setIUV_ImportoARuolo(0);
            
            //SCORPORI
            
            if(!empty($PaymentRateNumberIUV)){
                array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], "Rata N. {$PaymentRateNumberIUV['RateNumber']}", $PaymentRateNumberIUV['Amount'], $f_ImportoPagato, PagoPAERConst::VOCE_COSTO_SANZIONE));
                $verbaleOgg->setImportoARuolo($f_ImportoEffettivo);
                $verbaleOgg->setIUV_ImportoARuolo($f_ImportoEffettivo);
            } else {
                //in questo contesto le spese le prendiamo da FineHistory dove dovrebbero esser salvate al momento della creazione del verbale perché la calcola importi è usata prima
                //SCORPORO SPESE NOTIFICA
                $f_SpeseNotifica = $a_Spese['NotificationFee'];
                array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[24], 'Spese di notifica', $f_SpeseNotifica, $f_SpesePagateNotifica, PagoPAERConst::VOCE_COSTO_SPESE_NOTIFICA));
                //SCORPORO SPESE VARIE
                $f_SpeseVarie = $a_Spese['ResearchFee']; //B2720 non dobbiamo sommare la customerFee che viene memorizzata per consultazione ma non contribuisce al totale atto perché già assorbita nelle notificationFee
                array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[25], 'Spese varie', $f_SpeseVarie, $f_SpesePagateVarie, PagoPAERConst::VOCE_COSTO_INTERESSI));
                //SCORPORO MAGGIORAZIONE SEMESTRALE
                if($FineReminder){
                    $f_MaggSemestrale = $a_Spese['PercentualFee'];
                    array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[25], 'Maggiorazione semestrale del 10%', $f_MaggSemestrale, $f_PagateMaggSemestrale, PagoPAERConst::VOCE_COSTO_MAGG_SEMESTRALE));
                }
                
                if($PaymentRate){
                    array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], "Rateizzazione", ($PaymentRate['InstalmentAmount']-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_ImportoPagato, PagoPAERConst::VOCE_COSTO_SANZIONE));
                    $verbaleOgg->setImportoARuolo($f_ImportoEffettivo);
                    $verbaleOgg->setIUV_ImportoARuolo($f_ImportoEffettivo);
                } else {
                    $sanzioneNotturna = PrevedeMaggiorazioneXViolazioneNotturna($a_VerbBD['FineTime']);
                    $sanzioneMassa = PrevedeMaggiorazioneXEccedenzaMassa($a_VerbBD['VehicleMass']);
                    $descrizioneSanzioneRidotta = 'Ridotto';
                    $descrizioneSanzioneMinima = 'Minimo';
                    $descrizioneSanzioneMassima = 'A ruolo';
                    if ($sanzioneNotturna && $sanzioneMassa){
                        $descrizioneSanzioneRidotta = 'Ridotto (con eccedenza massa e violazione notturna)';
                        $descrizioneSanzioneMinima = 'Minimo (con eccedenza massa e violazione notturna)';
                        $descrizioneSanzioneMassima = 'A ruolo (con eccedenza massa e violazione notturna)';
                    } else if ($sanzioneNotturna){
                        $descrizioneSanzioneRidotta = 'Ridotto (con violazione notturna)';
                        $descrizioneSanzioneMinima = 'Minimo (con violazione notturna)';
                        $descrizioneSanzioneMassima = 'A ruolo (con violazione notturna)';
                    } else if ($sanzioneMassa)
                    {
                        $descrizioneSanzioneRidotta = 'Ridotto (con eccedenza massa)';
                        $descrizioneSanzioneMinima = 'Minimo (con eccedenza massaa)';
                        $descrizioneSanzioneMassima = 'A ruolo (con eccedenza massa)';
                    }
                    
                    if($verbaleOgg->getDataNotifica()){
                        switch($str_TipoSanzione){
                            case 'Ridotta':
                                $verbaleOgg->setImportoRidotto($f_ImportoEffettivo);
                                $verbaleOgg->setIUV_ImportoRidotto($f_ImportoEffettivo);
                                array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], $descrizioneSanzioneRidotta, ($f_SanzioneRidotta-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_PagateSanzione, PagoPAERConst::VOCE_COSTO_SANZIONE));
                                break;
                            case 'Minima':
                                $verbaleOgg->setImportoMinimo($f_ImportoEffettivo);
                                $verbaleOgg->setIUV_ImportoMinimo($f_ImportoEffettivo);
                                array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], $descrizioneSanzioneMinima, ($f_SanzioneMinima-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_PagateSanzione, PagoPAERConst::VOCE_COSTO_SANZIONE));
                                break;
                            case 'Massima':
                                $verbaleOgg->setImportoARuolo($f_ImportoEffettivo);
                                $verbaleOgg->setIUV_ImportoARuolo($f_ImportoEffettivo);
                                array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], $descrizioneSanzioneMassima, ($f_SanzioneMassima-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_PagateSanzione, PagoPAERConst::VOCE_COSTO_SANZIONE));
                                break;
                        }
                    } else {
                        if(($b_RicercaIUV && $b_IUVRidotto) || $b_PagamentoRidotto){
                            array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], $descrizioneSanzioneRidotta, ($f_SanzioneRidotta-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_PagateSanzione, PagoPAERConst::VOCE_COSTO_SANZIONE));
                            $verbaleOgg->setImportoRidotto($this->f($f_SanzioneRidotta-$f_PagateSanzione));
                            $verbaleOgg->setIUV_ImportoRidotto($this->f($f_SanzioneRidotta-$f_PagateSanzione));
                        }
                        $verbaleOgg->setImportoMinimo($this->f($f_SanzioneMinima-$f_PagateSanzione));
                        $verbaleOgg->setIUV_ImportoMinimo($this->f($f_SanzioneMinima-$f_PagateSanzione));
                        array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], $descrizioneSanzioneMinima, ($f_SanzioneMinima-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_PagateSanzione, PagoPAERConst::VOCE_COSTO_SANZIONE));
                        $verbaleOgg->setImportoARuolo($this->f($f_SanzioneMassima-$f_PagateSanzione));
                        $verbaleOgg->setIUV_ImportoARuolo($this->f($f_SanzioneMassima-$f_PagateSanzione));
                        array_push($a_Scorpori, $this->CreaScorporo(PagoPAERConst::SCORPORO_CAUSALEIMPORTO[23], $descrizioneSanzioneMassima, ($f_SanzioneMassima-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale), $f_PagateSanzione, PagoPAERConst::VOCE_COSTO_SANZIONE));
                    }
                    
                    $this->Log('N', 'SCORPORO_RIDOTTA', "$f_SanzioneRidotta-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale");
                    $this->Log('N', 'SCORPORO_MINIMA', "$f_SanzioneMinima-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale");
                    $this->Log('N', 'SCORPORO_MASSIMA', "$f_SanzioneMassima-$f_SpeseNotifica-$f_SpeseVarie-$f_MaggSemestrale");
                }
            }

            if(!empty($a_Articoli)) $verbaleOgg->setArticoli($a_Articoli);
            if(!empty($a_Scorpori)) $verbaleOgg->setScorpori($a_Scorpori);
            
            //Pagabile deve anche tener conto di eventuali somme già pagate perché se non si fa Solleciti/Elabora solleciti le posizioni pagate non si chiudono in automatico
            //quidi andrebbe anche visto se gli impoti al netto di $f_ImportoPagato vanno a zero
            
            if($PaymentRateNumberIUV){
                $str_WhereIUVPagato = "Iuv = '{$PaymentRateNumberIUV['PagoPAIUV']}'";
            } else {
                $str_WhereIUVPagato = "Iuv IN('{$a_VerbBD['PagoPA1']}','{$a_VerbBD['PagoPA2']}')";
            }
            $b_IUVPagato = !empty($this->db->getArrayLine($this->db->Select('FinePaymentReporting', "$str_WhereIUVPagato AND COALESCE(Iuv, '') != ''")));
            
            $verbaleOgg->setPagabile(!in_array($a_VerbBD['StatusTypeId'], PagoPAERConst::STATUSTYPEID_NONPAGABILE)
                && !$b_IUVPagato
                && !$FinePaymenNotification
                && ($f_ImportoEffettivo > 0));
            
            if($a_VerbBD['StatusTypeId'] == PagoPAERConst::STATUSTYPEID_PAGAMENTO_CONCLUSO){
                $this->EsitoImposta(5);
                $esito = 5;
            } else if($this->IUVScaduto){
                $this->EsitoImposta(4);
                $esito = 4;
            } else if(in_array($a_VerbBD['StatusTypeId'], PagoPAERConst::STATUSTYPEID_PAGAMENTO_ANNULLATO)){
                if($FineArchive){
                    $verbaleOgg->setCausaleDiNonEsigibilita($FineArchive['Note']);
                } else {
                    $verbaleOgg->setCausaleDiNonEsigibilita($a_VerbBD['StatusTypeId'] == PagoPAERConst::STATUSTYPEID_VERBALE_CHIUSO
                        ? $a_VerbBD['NoteProcedure']
                        : $a_VerbBD['Note']);
                }
                $this->EsitoImposta(3);
                $esito = 3;
            } else if($b_RicercaIUV && $importoInput > 0 && $importoInput != $verbaleOgg->getImportoIUV()){
                $this->EsitoImposta(2);
                $esito = 2;
            } 
            
            $this->Log('N', __FUNCTION__, 'Id:'.$a_VerbBD['Id'].' esito '.($esito ?? 'NON IMPOSTATO'));

            return $verbaleOgg;
        } else return null;
    }
    
    private function DeterminaTipoSanzione(string $dataCalcolo, string $dataLimite, bool $b_RicercaIUV, bool $b_IUVRidotto, bool $b_PagamentoRidotto, bool $sollecito = false, bool $b_IUVRata = false, bool $rateizzazioneAperta = false):String{
        if($b_IUVRata){
            if($dataCalcolo > $dataLimite) $this->IUVScaduto = true; //IUV rata scaduto se $dataCalcolo (data odierna) > dataLimitePagamentoRata
            $str_TipoSanzione = 'Massima';
        } else {
            $dataLimite5gg = SkipFestiveDays(date('Y-m-d', strtotime($dataCalcolo . " + 5 day")), $this->customer['CityId'], $this->db);
            $dataLimite60gg = SkipFestiveDays(date('Y-m-d', strtotime($dataCalcolo . " + 60 day")), $this->customer['CityId'], $this->db);
            //NOTA 24/01/2022: Nel modello 3 per Iuv se è chiesto il pagamento per lo Iuv PagoPa1 e sono passati i termini del pagamento in base alla data di notifica va dato come non pagabile
            //NOTA 02/03/2022: se non c'è data di notifica segnamo il pagamento richiesto per chiave IUV come non scaduto
            //se la ricerca è per Iuv e hanno mandato il primo, controllo se è scaduto
            if ($b_RicercaIUV){
                if($b_IUVRidotto){
                    if($rateizzazioneAperta){
                        $this->IUVScaduto = true;
                    } else {
                        if($b_PagamentoRidotto) {
                            if($dataLimite > $dataLimite5gg) $this->IUVScaduto = true; //Primo IUV scaduto se pagamento ridotto e passati più di 5 gg
                        }
                        else {
                            if($dataLimite > $dataLimite60gg) $this->IUVScaduto = true; //Primo IUV scaduto se pagamento NON ridotto e passati più di 60 gg
                        }
                    }
                } else {
                    if($b_PagamentoRidotto && $dataLimite > $dataLimite60gg && !$sollecito) $this->IUVScaduto = true; //Secondo IUV scaduto se pagamento ridotto e passati più di 60 gg
                }
            }
            
            if($sollecito || $rateizzazioneAperta) {
                $str_TipoSanzione = 'Massima';
            } else {
                if($b_RicercaIUV){
                    if ($b_IUVRidotto){ //chiesto il primo importo
                        if($b_PagamentoRidotto){
                            $str_TipoSanzione = 'Ridotta';
                        } else $str_TipoSanzione = 'Minima';
                    } else { //chiesto secondo importo
                        if($b_PagamentoRidotto){
                            if($dataLimite <= $dataLimite60gg){
                                $str_TipoSanzione = 'Minima';
                            } else $str_TipoSanzione = 'Massima';
                        } else $str_TipoSanzione = 'Massima';
                    }
                } else {
                    if($b_PagamentoRidotto && $dataLimite <= $dataLimite5gg) $str_TipoSanzione = 'Ridotta';
                    else if($dataLimite <= $dataLimite60gg) $str_TipoSanzione = 'Minima';
                    else $str_TipoSanzione = 'Massima';
                }
            }
            
        }
 
        $this->Log('N', 'DeterminaTipoSanzione', $str_TipoSanzione);
        
        return $str_TipoSanzione;
    }
    
    private function DeterminaDataNotifica($rs_FineHistoryFlow):?String{
        mysqli_data_seek($rs_FineHistoryFlow, 0);
        $DataNotifica = null;
        
        //Su FineHistory con NotificationTypeId = 6 la data di notifica è la DeliveryDate
        //Su FineNotification la data di notifica è la NotificationDate
        
        //In caso di più figure se tutti hanno resultid > 0 devo prendere la data di notifica maggiore, altrimenti NO
        while ($fineHistoryFlow = $this->db->getArrayLine($rs_FineHistoryFlow)){
            if($fineHistoryFlow['ResultId'] > 0){
                $DataNotifica=($fineHistoryFlow['DeliveryDate'] > $DataNotifica) ? $fineHistoryFlow['DeliveryDate'] : $DataNotifica;
            } else {
                $DataNotifica = null;
                break;
            }
        }
        
        return $DataNotifica;
    }

    private function RiempiSoggettoDaBD(array $a_VerbBD, SoggettoPM $soggettoPMOgg):SoggettoPM{
        if(!empty($a_VerbBD)){
            $a_TrespasserTypes = array();
            $rs_Trespasser=$this->db->Select("V_FineTrespasser","FineId={$a_VerbBD['Id']}");

            while ($Trespasser = $this->db->getArrayLine($rs_Trespasser)){
                $a_TrespasserTypes['T'.$Trespasser['TrespasserTypeId']] = $Trespasser;
            }
            if(isset($a_TrespasserTypes['T1'])){
                $Trespasser = $a_TrespasserTypes['15'] ?? $a_TrespasserTypes['T1'];
            } else if(isset($a_TrespasserTypes['T3'])){
                $Trespasser = $a_TrespasserTypes['T16'] ?? $a_TrespasserTypes['T3'];
            } else if(isset($a_TrespasserTypes['T11'])){
                $Trespasser = $a_TrespasserTypes['16'] ?? ($a_TrespasserTypes['T12'] ?? $a_TrespasserTypes['T11']);
            }
            
            if($Trespasser){
                $soggettoPMOgg->setCap($Trespasser['ZIP']);
                $soggettoPMOgg->setCivico($Trespasser['StreetNumber']);
                $soggettoPMOgg->setCodiceFiscale($Trespasser['TaxCode']);
                $soggettoPMOgg->setCognome($Trespasser['Surname']);
                $soggettoPMOgg->setComune($Trespasser['City']);
                $soggettoPMOgg->setEmail($Trespasser['Mail']);
                $soggettoPMOgg->setIndirizzo($Trespasser['Address']);
                $soggettoPMOgg->setTipoNaturaGiuridica($Trespasser['Genre']=='D' || in_array($Trespasser['Genre'], unserialize(LEGALFORM_INDIVIDUALCOMPANY))
                    ? PagoPAERConst::SOGGETTO_TIPONATURAGIURIDICA[3]
                    : PagoPAERConst::SOGGETTO_TIPONATURAGIURIDICA[2]);
                $soggettoPMOgg->setNaturaGiuridicaSoggetto($soggettoPMOgg->getTipoNaturaGiuridica() != PagoPAERConst::SOGGETTO_TIPONATURAGIURIDICA[2] 
                    ? 'G' 
                    : 'F');
                $soggettoPMOgg->setNazione($Trespasser['CountryTitle']);
                $soggettoPMOgg->setNome($Trespasser['Name']);
                $soggettoPMOgg->setPartitaIva($Trespasser['VatCode']);
                $soggettoPMOgg->setProvincia($Trespasser['Province']);
                $soggettoPMOgg->setRagioneSociale($Trespasser['CompanyName']);
                $this->trespasserid = $Trespasser['TrespasserId'];

                return $soggettoPMOgg;
            } else {
                $this->trespasserid = null;
                return null;
            }
        } else return null;
    }
    
    //TODO completare
    private function CreaScorporo(String $causale, String $descrizione, float $emesso, float $pagato, String $voceDiCosto):Scorporo{
        $o_Scorporo = new Scorporo();
        
        $o_Scorporo->setAliquotaIva(PagoPAERConst::SCORPORO_ALIQUOTAIVA[6]);
        $o_Scorporo->setCausaleImporto($causale);
        //$o_Scorporo->setDataCompetenza();//TODO chiedere a NextStep
        $o_Scorporo->setDescrizione($descrizione);
        $o_Scorporo->setImportoEmesso($this->f($emesso));
        $o_Scorporo->setImportoPagato($this->f($pagato));
        $o_Scorporo->setIva(0);
        $o_Scorporo->setQuantita(1);
        $o_Scorporo->setVoceDiCosto($voceDiCosto);
        
        return $o_Scorporo;
    }
    
    //24/01/2022 non sappiamo ancora a cosa serve DatiExtra
    //25/05/2022 ha detto Andrea che non ci interessa e possiamo non usarlo
    private function RicercaTipoChiave(String $nomeMetodo, String $tipoChiave, $iuv = null, $numeroVerbale = null, $dataOraViolazione = null, $serie = null, $targa = null, $datiExtra  = null, $richiestaDaAppCDS1Click = false){
        $b_VerificaSerie = true;
        
        switch($tipoChiave){
            case PagoPAERConst::KEYT_IUV:
                $this->Log('N', $nomeMetodo, "TipoChiave: {$tipoChiave}, IdentificativoUnivocoVersamento: {$iuv}");
                
                if(!empty($iuv)){
                    //se lo iuv è di una rata, recupero l'id del verbale risalendo alla rateizzazione a cui appartiene
                    $r_PaymentRate = $this->db->getArrayLine($this->db->SelectQuery("SELECT * FROM PaymentRateNumber PRN JOIN PaymentRate PR ON PRN.PaymentRateId = PR.Id WHERE PRN.PagoPAIUV='$iuv'"));
                    if($r_PaymentRate){
                        $str_Where = "Id={$r_PaymentRate['FineId']} AND CityId='{$this->customer['CityId']}'";
                        $this->paymentRateId = $r_PaymentRate['PaymentRateId'];
                    } else {
                        $str_Where = "StatusTypeId NOT IN(".implode(',', PagoPAERConst::STATUSTYPEID_PREINSERIMENTI).") AND CityId='{$this->customer['CityId']}' AND (PagoPA1= '$iuv' OR PagoPA2='$iuv')";
                    }
                    
                    $Fine = $this->db->getArrayLine($this->db->Select("Fine",$str_Where));
                }
                break;
            case PagoPAERConst::KEYT_CDS:
                $this->Log('N', $nomeMetodo, "TipoChiave: {$tipoChiave}, RichiestaDaAppCDS1Click: ".($richiestaDaAppCDS1Click === true ? 'true' : 'false').", DataOraViolazione: {$dataOraViolazione}, NumeroVerbale: {$numeroVerbale}, Serie: {$serie}, Targa: {$targa}");
                //se vale true la violazione CDS può non avere la targa tra i dati chiave
                if($richiestaDaAppCDS1Click === true)
                {
                    if(!empty($dataOraViolazione) && is_numeric($numeroVerbale) && !empty($serie)){
                        list($DataViolazione, $OraViolazione) = array_pad(explode('T',$dataOraViolazione), 2, null);
                        $str_Where = "StatusTypeId NOT IN(".implode(',', PagoPAERConst::STATUSTYPEID_ESCLUSICDS).") AND ProtocolId='$numeroVerbale' AND CityId='{$this->customer['CityId']}'";
                        $str_Where .= "AND FineDate='$DataViolazione'";
                        $Fine = $this->db->getArrayLine($this->db->Select("Fine", $str_Where));
                        $b_VerificaSerie = $Fine ? $this->SerieVerifica($Fine['Id'], $serie) : false;
                    }
                } else {
                    if(!empty($dataOraViolazione) && is_numeric($numeroVerbale) && !empty($serie) && !empty($targa)){
                        list($DataViolazione, $OraViolazione) = array_pad(explode('T',$dataOraViolazione), 2, null);
                        $str_Where = "StatusTypeId NOT IN(".implode(',', PagoPAERConst::STATUSTYPEID_ESCLUSICDS).") AND ProtocolId='$numeroVerbale' AND VehiclePlate='$targa' AND CityId='{$this->customer['CityId']}'";
                        $str_Where .= " AND FineDate='$DataViolazione'";
                        $Fine = $this->db->getArrayLine($this->db->Select("Fine", $str_Where));
                        $b_VerificaSerie = $Fine ? $this->SerieVerifica($Fine['Id'], $serie) : false;
                    }
                }
                break;
            case PagoPAERConst::KEYT_EXTRACDS:
                $this->Log('N', $nomeMetodo, "TipoChiave: {$tipoChiave}, DataOraViolazione: {$dataOraViolazione}, NumeroVerbale: {$numeroVerbale}, Serie: {$serie}");
                if(!empty($dataOraViolazione) && is_numeric($numeroVerbale) && !empty($serie)){
                    list($DataViolazione, $OraViolazione) = array_pad(explode('T',$dataOraViolazione), 2, null);
                    $str_Where = "StatusTypeId NOT IN(".implode(',', PagoPAERConst::STATUSTYPEID_ESCLUSICDS).") AND ProtocolId='$numeroVerbale' AND CityId='{$this->customer['CityId']}'";
                    $str_Where .= " AND FineDate='$DataViolazione'";
                    $Fine = $this->db->getArrayLine($this->db->Select("Fine", $str_Where));
                    $b_VerificaSerie = $Fine ? $this->SerieVerifica($Fine['Id'], $serie) : false;
                }
            case PagoPAERConst::KEYT_IMMAGINE:
                $this->Log('N', $nomeMetodo, "TipoChiave: {$tipoChiave}");
                break;
        }
        
        return isset($Fine) && $b_VerificaSerie ? $Fine : null;
    }
    
    /**
     * Verifica se l'articolo primario del verbale specificato contiene una particella verbale.
     * Se si, la confronta con la serie passata e ritorna se corrisponde, altrimenti ritorna true.
     * @return bool
     */
    private function SerieVerifica($fineid, $serie){
        $this->Log('N', __FUNCTION__, "Serie su richiesta: ".$serie);
        
        $FineArticle=$this->db->getArrayLine($this->db->SelectQuery("SELECT A.ArticleLetterAssigned, A.ViolationTypeId FROM FineArticle FA JOIN Article A ON FA.ArticleId=A.Id WHERE FA.FineId=$fineid"));
        $ViolationTypeLetter=$this->db->getArrayLine($this->db->Select("ViolationTypeLetter", "ViolationTypeId={$FineArticle['ViolationTypeId']} and CityId='{$this->customer['CityId']}'"));
        
        $str_WhereCity = ($this->customer['CityUnion'] > 1) ? "UnionId='{$this->customer['CityId']}'" : "Id='{$this->customer['CityId']}'";
        $rs_ProtocolLetter = $this->db->Select(MAIN_DB . '.City', $str_WhereCity);
        $a_ProtocolLetterLocality = array();
        //TODO dato che ad oggi non sappiamo con certezza se la nazionalità di un verbale è basata su targa o trasgressore, facciamo la verifica sulla serie sia nazionale che estera
        while ($r_ProtocolLetter = $this->db->getArrayLine($rs_ProtocolLetter)) {
            $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
            $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
            $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
            $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
        }
        
        $rs_RuleType = $this->db->Select('V_RuleType', "ViolationTypeId=" . $FineArticle['ViolationTypeId'] . " AND CityId='{$this->customer['CityId']}'");
        $r_RuleType = $this->db->getArrayLine($rs_RuleType);
        $RuleTypeId = $r_RuleType['Id'];
        
        if(trim($FineArticle['ArticleLetterAssigned'])!=''){
            $SerieArticolo = $FineArticle['ArticleLetterAssigned'];
            $this->Log('N', __FUNCTION__, "Serie su articolo: $SerieArticolo");
            
            return $SerieArticolo == $serie;
        } else if(trim($ViolationTypeLetter['ViolationLetterAssigned'])!=''){
            $SerieViolazione = $ViolationTypeLetter['ViolationLetterAssigned'];
            $this->Log('N', __FUNCTION__, "Serie su tipo di violazione: $SerieViolazione");
            
            return $SerieViolazione == $serie;
        } else {
            //uso {$this->customer['CityId']} invece di Locality su Fine che ha rilevanza per le unioni di comuni tipo Garlasco e Unione dal Tobbio al Colma ma non per le altre
            $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$this->customer['CityId']]['NationalProtocolLetterType1'];
            $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$this->customer['CityId']]['NationalProtocolLetterType2'];
            $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$this->customer['CityId']]['ForeignProtocolLetterType1'];
            $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$this->customer['CityId']]['ForeignProtocolLetterType2'];
            
            $SerieNazionale = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            $SerieEstera = ($RuleTypeId == 1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
            $this->Log('N', __FUNCTION__, "Serie su ente nazionale: $SerieNazionale, Serie su ente estera: $SerieEstera");
            
            if(!empty($SerieNazionale) && !empty($SerieEstera)){
                return $SerieNazionale == $serie || $SerieEstera == $serie;
            }
        }
        
        return true;
    }
    
    private function SalvaImmaginePagamento(string $fileType, string $fileName, ?string $content, ?string $url, string $path){
        $this->Log('N', __FUNCTION__, "Tipo: $fileType, Nome: $fileName, Percorso: $path, Url: $url");
        $FileSalvato = null;
        
        try{
            switch($fileType){
                //Salvataggio tipo PNG
                case PagoPAERConst::IMGT_PNG:
                    if(!empty($content)){
                        if(file_put_contents($path.$fileName, $content)){
                            if($image = imagecreatefrompng($path.$fileName)){
                                if(imagejpeg($image, $path.pathinfo($fileName)['filename'].'.jpg', 70)){
                                    imagedestroy($image);
                                    unlink($path.$fileName);
                                    $FileSalvato = pathinfo($fileName)['filename'].'.jpg';
                                } else $this->Log('W', __FUNCTION__, "Impossibile convertire l'immagine in .jpg :$fileName");
                            } else $this->Log('W', __FUNCTION__, "Impossibile recuperare l'immagine: $fileName.");
                        } else $this->Log('W', __FUNCTION__, "Impossibile salvare l'immagine originale sul file system.");
                    } else $this->Log('W', __FUNCTION__, 'Contenuto immagine vuoto.');
                    break;
                //Salvataggio tipo HTML
                case PagoPAERConst::IMGT_HTML:
                    if(!empty($content)){
                        $fileName = pathinfo($fileName, PATHINFO_EXTENSION) == 'html' ? $fileName : "$fileName.html";
                        if(file_put_contents($path.$fileName, $content)){
                            $FileSalvato = $fileName;
                        } else $this->Log('W', __FUNCTION__, "Impossibile salvare l'immagine originale sul file system.");
                    } else $this->Log('W', __FUNCTION__, 'Contenuto immagine vuoto.');
                    break;
                //Salvataggio tipo WebUrl
                case PagoPAERConst::IMGT_WEBURL:
                    if(!empty($url)){
                        if($image = imagecreatefrompng($url)){
                            if(imagejpeg($image, $path.$fileName.'.jpg', 70)){
                                imagedestroy($image);
                                $FileSalvato = $fileName.'.jpg';
                            } else $this->Log('W', __FUNCTION__, "Impossibile convertire l'immagine in .jpg da: $url.");
                        } else $this->Log('W', __FUNCTION__, "Impossibile recuperare l'immagine da: $url.");
                    } else $this->Log('W', __FUNCTION__, 'Url immagine vuoto.');
                    break;
            }
        } catch (Exception $e){
            $this->Log('D', __FUNCTION__, "Errore nel salvataggio dell'immagine del pagamento: $e");
        }
        
        return $FileSalvato;
    }
    
    private function ConvertiIUV($codice, $inAvviso = false){
        $cls_iuv = new cls_iuv();
        try{
            if($inAvviso){
                return $cls_iuv->generateNoticeCode($codice, $this->customer['PagoPAAuxCode']);
            } else {
                return $cls_iuv->extractIUV($codice);
            }
        } catch (Exception $e){
            $this->Log('D', __FUNCTION__, 'Errore di conversione codice IUV: '.$e);
            return null;
        }
    }
    
    private function CercaVerbaleDaDatiPagamento($modalitàPagamento, $quintoCampo, $causale){
        $this->Log('N', __FUNCTION__, "Modalità: $modalitàPagamento, Quinto campo: $quintoCampo, Causale: $causale");
        
        $tipoPagamento = PagoPAERConst::MODALITA_PAYMENTTYPEID[$modalitàPagamento];
        $Fine = null;
        
        switch($tipoPagamento){
            case 1:
            case 18:
                if(strlen($quintoCampo) == 18){
                    $FineId = abs(substr($quintoCampo, 6, -2));
                    
                    $this->Log('N', __FUNCTION__, "Ricerca verbale per ID: $FineId estratto da quinto campo");
                    
                    $Fine = $this->db->getArrayLine($this->db->Select("Fine", "Id=$FineId"));
                } else {
                    $this->Log('W', __FUNCTION__, "Il quinto campo non è della lunghezza prevista (18 caratteri) per poter estrarre l'ID del verbale.");
                }
                break;
            case 2:
                $matches = array();
                preg_match(PagoPAERConst::REGEX_DATI_VERBALE_CAUSALE, $causale, $matches);
                if(!empty($matches)){
                    array_shift($matches);
                    list($protocolId, $protocolYear, $protocolLetter) = $matches;
                    
                    $this->Log('N', __FUNCTION__, "Ricerca verbale per Cron: $protocolId, Anno: $protocolYear, Lettera: $protocolLetter");
                    
                    if(!empty($protocolId) && !empty($protocolYear) && !empty($protocolLetter)){
                        $Fine = $this->db->getArrayLine($this->db->Select("Fine", "StatusTypeId NOT IN(".implode(',', PagoPAERConst::STATUSTYPEID_ESCLUSICDS).") AND ProtocolId='$protocolId' AND ProtocolYear=$protocolYear AND CityId='{$this->customer['CityId']}'"));
                        if($Fine){
                            $Fine = $this->SerieVerifica($Fine['Id'], $protocolLetter) ? $Fine : null;
                        }
                    } else $this->Log('W', __FUNCTION__, "Non è possibile effetturare la ricerca in quanto i dati sono incompleti.");
                }
                break;
        }
        
        if(!$Fine){
            $this->Log('N', __FUNCTION__, "Non è stato possibile risalire ad alcun verbale tramite i dati di pagamento.");
        }

        return $Fine;
    }
    
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//METODI WSDL//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    function IntestazioneFO($header){
        if(isset($header->Username,$header->Password,$header->CodiceFiscaleEnte)){
            $this->Log('N', __FUNCTION__, "Username: {$header->Username}, Password: {$header->Password}, CodiceFiscaleEnte: {$header->CodiceFiscaleEnte}");
            
            $Customer = $this->db->getArrayLine($this->db->Select("Customer", "'{$header->CodiceFiscaleEnte}' IN(ManagerVAT,ManagerTaxCode)"));
            if($Customer){
                
                //la password è stata salvata in MD5 per questo servizio
                $CustomerService = $this->db->getArrayLine($this->db->Select("CustomerService", "CityId='{$Customer['CityId']}' AND ServiceId={$Customer['PagoPAService']} AND UserName='{$header->Username}' AND Password='{$header->Password}'"));
                if($CustomerService) $this->customer=$Customer;
            }
        }
        if(!isset($this->customer)){
            $this->Log('D', __FUNCTION__, "Errore in autenticazione.");
            http_response_code(401);
        }
    }
    
    function VerificaVerbale($r):VerificaVerbaleResponse {
        $this->metodoChiamato = __FUNCTION__;
        $VerificaVerbaleResponse = new VerificaVerbaleResponse();
        
        try{
            if(isset($this->customer)){
                $Verbale = new Verbale();
                $Soggetto = new SoggettoPM();
                
                if($r->TipoChiave == PagoPAERConst::KEYT_IUV){
                    $iuv = $this->customer['IsIuvCodiceAvviso'] == 1
                        ? $r->IdentificativoUnivocoVersamento
                        : $this->ConvertiIUV($r->IdentificativoUnivocoVersamento);
                    $Verbale->setIUV($iuv);
                } else $iuv = null;
                
                $Fine = $this->RicercaTipoChiave(__FUNCTION__, $r->TipoChiave, $iuv, $r->Verbale ?? null, $r->DataViolazione ?? null, $r->Serie ?? null, $r->Targa ?? null, $r->DatiExtra ?? null, $r->RichiestaDaAppCDS1Click ?? false);
                
                if(isset($Fine)) {
                    $VerificaVerbaleResponse->Soggetto = $this->RiempiSoggettoDaBD($Fine, $Soggetto);
                    $VerificaVerbaleResponse->Verbale = $this->RiempiVerbaleDaBD($r->TipoChiave, $Fine, $Verbale, $r->Importo, $this->trespasserid);
                } else $this->EsitoImposta(6, 'La ricerca non ha prodotto risultati.');
            }
        } catch (Throwable $e){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, $e);
            $VerificaVerbaleResponse = new VerificaVerbaleResponse();
        }
        
        $this->ValutaRisposta();
        
        $VerificaVerbaleResponse->Esito = PagoPAERConst::ESITI[$this->esito ?? 1];
        $VerificaVerbaleResponse->Descrizione = $this->esitoDescrizione ?? null;
        
        $this->Log('N', __FUNCTION__, "Esito: {$VerificaVerbaleResponse->Esito}, Descrizione: {$VerificaVerbaleResponse->Descrizione}");
            
        return $VerificaVerbaleResponse;
    }

    function RiceviVerbaliSoggetto($r):RiceviVerbaliResponse {
        $this->metodoChiamato = __FUNCTION__;
        
        /** @var IdentificativoUnivoco $IdUni */
        $IdUni = $r->IdentificativoUnivoco;
        $RiceviVerbaliResponse = new RiceviVerbaliResponse();
        
        try{
            if(isset($this->customer)){
                $this->Log('N', __FUNCTION__, "Tipo: {$IdUni->Tipo}, Codice: {$IdUni->Codice}");
                
                $a_Verbali = array();
                
                if(isset($IdUni->Tipo, $IdUni->Codice) && in_array($IdUni->Tipo, array('G','F'))){
                    $Genere = $IdUni->Tipo == "G" ? "'D'" : "'F','M'";
                    $Trespasser=$this->db->getArrayLine($this->db->SelectQuery("SELECT Id FROM Trespasser WHERE Genre IN($Genere) AND '{$IdUni->Codice}' IN(TaxCode,VatCode) AND CustomerId='{$this->customer['CityId']}'"));
                    
                    if($Trespasser){
                        $rs_FineTrespasser=$this->db->SelectQuery("SELECT FineId FROM FineTrespasser WHERE TrespasserId={$Trespasser['Id']}");
                        while($FineTrespasser = $this->db->getArrayLine($rs_FineTrespasser)){
                            $rs_Fine = $this->db->Select("Fine","StatusTypeId NOT IN(".implode(',', PagoPAERConst::STATUSTYPEID_PREINSERIMENTI).") AND Id={$FineTrespasser['FineId']} AND CityId='{$this->customer['CityId']}'");
                            while ($Fine = $this->db->getArrayLine($rs_Fine)){
                                array_push($a_Verbali,$this->RiempiVerbaleDaBD('', $Fine, new Verbale(), null, $Trespasser['Id']));
                                //al primo verbale che trovo con errori mi fermo e rendo errore interno
                                if ($this->esito == 7)
                                    break;
                            }
                        }
                    }
                }
                //se la lista non è vuota e l'ultimo verbale riempito è andato in errore
                if(!empty($a_Verbali)){
                    if ($this->esito == 7)
                        $this->EsitoImposta(7, 'La ricerca ha prodotto risultati ma ci sono errori nel recupero dei dati.');
                        else {
                            $this->EsitoImposta(1);
                            $RiceviVerbaliResponse->Verbali = $a_Verbali;
                        }
                } else {
                    $this->EsitoImposta(6, 'La ricerca non ha prodotto risultati.');
                }
            }
        } catch(Throwable $e){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, $e);
            $RiceviVerbaliResponse = new RiceviVerbaliResponse();
        }
        
        $this->ValutaRisposta();

		$RiceviVerbaliResponse->Esito = PagoPAERConst::ESITI[$this->esito];
        $RiceviVerbaliResponse->Descrizione = $this->esitoDescrizione ?? null;
        
        $this->Log('N', __FUNCTION__, "Esito: {$RiceviVerbaliResponse->Esito}, Descrizione: {$RiceviVerbaliResponse->Descrizione}");
        
        return $RiceviVerbaliResponse;
    }
    
    function NotificaPagamentoVerbale($r):NotificaPagamentoVerbaleResponse {
        $this->metodoChiamato = __FUNCTION__;
        $NotificaPagamentoVerbaleResponse  = new NotificaPagamentoVerbaleResponse();
        
        try{
            if(isset($this->customer)){
                
                if($r->TipoChiave == PagoPAERConst::KEYT_IUV){
                    $iuv = $this->customer['IsIuvCodiceAvviso'] == 1
                    ? $r->IdentificativoUnivocoVersamento
                    : $this->ConvertiIUV($r->IdentificativoUnivocoVersamento);
                } else $iuv = null;
                
                $Fine = $this->RicercaTipoChiave(__FUNCTION__, $r->TipoChiave, $iuv, $r->Verbale ?? null, $r->DataViolazione ?? null, $r->Serie ?? null, $r->Targa ?? null, $r->DatiExtra ?? null, $r->RichiestaDaAppCDS1Click ?? false);
                
                if(isset($Fine)) {
                    if(!empty($r->IdentificativoIncasso) && !empty($r->RicevutaTelematica) && $this->ValidaXml(base64_decode($r->RicevutaTelematica)) && !empty($r->DataVersamento)){
                        //cerco se esiste già una prenotazionie con lo stesso identificativo pagamento
                        $FinePaymentNotification=$this->db->getArrayLine($this->db->Select("FinePaymentNotification","FineId={$Fine['Id']} AND Deleted=0"));
                        if (isset($FinePaymentNotification)){
                            if ($FinePaymentNotification['IdentificativoIncasso'] == $r->IdentificativoIncasso)
                                $this->EsitoImposta(8, "La notifica di pagamento risulta già in SCDS con l’identificativo pagamento EntraNext: {$r->IdentificativoIncasso}");
                            else
                                $this->EsitoImposta(7, "Errore imprevisto. Notifica di pagamento esistente con altro identificativo pagamento EntraNext.");
                        } else {
                            $PercorsoRicevutaTele = ($Fine['VehicleCountry'] == 'Italia' ? NATIONAL_FINE : FOREIGN_FINE).'/'.$this->customer['CityId'].'/'.$Fine['Id'].'/';
                            $FileRicevutaTele = 'ricevuta_telematica_'.$r->IdentificativoIncasso.'_'.date('Y-m-d_H-i').'.xml';
                            
                            $a_FinePaymentNotification = array(
                                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Fine['Id'],'settype'=>'int'),
                                array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$r->Importo,'settype'=>'flt'),
                                array('field'=>'IdentificativoUnivocoRiscossione','selector'=>'value','type'=>'str','value'=>$r->IdentificativoUnivocoRiscossione),
                                array('field'=>'IdentificativoTransazione','selector'=>'value','type'=>'str','value'=>$r->IdentificativoTransazione),
                                array('field'=>'CodiceContestoPagamento','selector'=>'value','type'=>'str','value'=>$r->CodiceContestoPagamento),
                                array('field'=>'IdentificativoPSP','selector'=>'value','type'=>'str','value'=>$r->IdentificativoPSP),
                                array('field'=>'DescrizionePSP','selector'=>'value','type'=>'str','value'=>$r->DescrizionePSP),
                                array('field'=>'IdentificativoIncasso','selector'=>'value','type'=>'int','value'=>$r->IdentificativoIncasso,'settype'=>'int'),
                                array('field'=>'RicevutaTelematica','selector'=>'value','type'=>'str','value'=>$FileRicevutaTele),
                                array('field'=>'DataVersamento','selector'=>'value','type'=>'str','value'=>$r->DataVersamento),
                                array('field'=>'Deleted','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                            );
                            
                            if (!is_dir($PercorsoRicevutaTele)) mkdir($PercorsoRicevutaTele, 0777);
                            
                            if(file_put_contents($PercorsoRicevutaTele.$FileRicevutaTele, base64_decode($r->RicevutaTelematica))){
                                $NotificaPagamentoVerbaleResponse->IdentificativoAccettazioneIncasso = $r->IdentificativoIncasso;
                                $this->db->Start_Transaction();
                                $this->db->Insert("FinePaymentNotification", $a_FinePaymentNotification);
                                $this->db->End_Transaction();
                            } else $this->EsitoImposta(7, "Errore nel salvataggio dei dati, riprovare.");
                        }
                    } else $this->EsitoImposta(7, "È necessario fornire identificativo incasso, ricevuta telematica e data versamento validi.");
                } else $this->EsitoImposta(6, "La ricerca non ha prodotto alcun risultato.");
            }
        } catch(Throwable $e){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, $e);
            $NotificaPagamentoVerbaleResponse  = new NotificaPagamentoVerbaleResponse();
        }
        
        $this->ValutaRisposta();
        
        $NotificaPagamentoVerbaleResponse->Esito = PagoPAERConst::ESITI[$this->esito ?? 1];
        $NotificaPagamentoVerbaleResponse->Descrizione = $this->esitoDescrizione ?? null;
        
        $this->Log('N', __FUNCTION__, "Esito: {$NotificaPagamentoVerbaleResponse->Esito}, Descrizione: {$NotificaPagamentoVerbaleResponse->Descrizione}");
        
        return $NotificaPagamentoVerbaleResponse;
    }
    
    function InviaPagamento($r):InviaPagamentoResponse {
        $this->metodoChiamato = __FUNCTION__;
        $NomeFileSalvato = null;
        $b_Error = false;
        
        /** @var RendiContazionePagamento $RendPag */
        $RendPag = $r->RendicontazionePagamento;
        $InviaPagamentoResponse = new InviaPagamentoResponse();
        
        try{
            if(isset($this->customer)){
                $FinePaymentReporting = $this->db->getArrayLine($this->db->SelectQuery("SELECT FinePaymentId FROM FinePaymentReporting WHERE IdentificativoPagamentoEntraNext='{$RendPag->IdentificativoPagamentoEntraNext}'"));
                
                if(!$FinePaymentReporting){
                    
                    if(isset($RendPag->Iuv)){
                        $iuv = $this->customer['IsIuvCodiceAvviso'] ==1 
                            ? $this->ConvertiIUV($RendPag->Iuv, true) 
                            : $RendPag->Iuv;
                    } else $iuv = null;
                    
                    $Fine = $this->RicercaTipoChiave(__FUNCTION__, $RendPag->TipoChiave, $iuv, $RendPag->NumeroVerbale ?? null, $RendPag->DataOraViolazione ?? null, $RendPag->Serie ?? null, $RendPag->Targa ?? null, $r->DatiExtra ?? null, $r->RichiestaDaAppCDS1Click ?? false);

                    $Nominativo = !empty($RendPag->Nominativo) 
                        ? $RendPag->Nominativo 
                        : (!empty($RendPag->SoggettoPagatore) 
                            ? $RendPag->SoggettoPagatore->Cognome.' '.$RendPag->SoggettoPagatore->Nome 
                            : '');
                    $NomeFile = !empty($RendPag->NomeFile) 
                        ? $RendPag->NomeFile 
                        : $RendPag->IdentificativoPagamentoEntraNext;
                        
                    if($RendPag->TipoChiave == PagoPAERConst::KEYT_IMMAGINE){
                        //TODO nel caso di rata è difficile dedurre il verbale a cui va agganciata quindi viene mandata nei pagamenti da bonificare. nel caso dei bollettini il quinto campo non è affidabile perchè se si prova ad estrarre l'id della rata, potrebbe anche esistere un verbale con lo stesso id
                        if(!$this->paymentRateId){
                            $Fine = $this->CercaVerbaleDaDatiPagamento($RendPag->Modalita, $RendPag->QuintoCampo, $RendPag->CausaleBonifico) ?: null;
                        }
                        
                        //Se è stato trovato un verbale a cui agganciare il pagamento, salva l'immagine nella cartella del verbale, altrimenti in quella dei pagamenti da bonificare
                        $PercorsoImmagine = empty($Fine)
                            ? PAYMENT_RECLAIM.'/'.$this->customer['CityId'].'/'
                            : ($Fine['CountryId'] == 'Z000' ? NATIONAL_FINE : FOREIGN_FINE). "/".$this->customer['CityId']."/".$Fine['Id'].'/';
                        
                        if (!is_dir($PercorsoImmagine)) mkdir($PercorsoImmagine, 0770, true);
                        
                        $NomeFileSalvato = $this->SalvaImmaginePagamento($RendPag->TipoImmagine, $NomeFile, $RendPag->Immagine ?? null, $RendPag->Url ?? null, $PercorsoImmagine);
                        $b_Error = empty($NomeFileSalvato);
                    }
                    
                    $FineId = !empty($Fine) ? $Fine['Id'] : 0;
                    
                    if(!$b_Error){
                        $this->db->Start_Transaction();
                        $ImportationId = 1; //altre importazioni pagoPa come quella di Savona usano 1 e così le importazioni. Il valore 2 è usato dalla bonifica pagamenti.
                        $a_FinePayment = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                            array('field'=>'PaymentTypeId','selector'=>'value','type'=>'int','value'=>PagoPAERConst::MODALITA_PAYMENTTYPEID[$RendPag->Modalita] ?? 9, 'settype'=>'int'), //TODO
                            array('field'=>'TableId','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
                            array('field'=>'ImportationId','selector'=>'value','type'=>'int','value'=>$ImportationId, 'settype'=>'int'),        
                            array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$Nominativo),
                            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$NomeFileSalvato ?? ''),
                            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$this->customer['CityId']),
                            array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$this->f($RendPag->Importo), 'settype'=>'flt'),
                            array('field'=>'PaymentDate','selector'=>'value','type'=>'str','value'=>$RendPag->DataPagamento ?? ($RendPag->DataVersamento ?? null), 'nullable' => true),
                            array('field'=>'CreditDate','selector'=>'value','type'=>'str','value'=>$RendPag->DataAccredito ?? null, 'nullable' => true),
                            array('field'=>'ImportReceiptNumber','selector'=>'value','type'=>'str','value'=>$RendPag->QuintoCampo), //Facciamo la stessa cosa fatta su imp_pagopa_exe.php, perchè il quinto campo che ci passano non è di 16 caratteri
                            array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$RendPag->CausaleBonifico ?? ''),
                            array('field'=>'RegDate','selector'=>'value','type'=>'str','value'=>date("Y-m-d")),
                            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>'EntraNext'),
                        );
                        
                        //se ho i dati del verbale faccio lo scorporo che altrimenti sarà fatto in fase di Bonifica pagamenti
                        if($FineId>0)
                        {
                            $this->Log('N', __FUNCTION__, "Pagamento da associare a ID verbale: $FineId");
                            //Determina se c'è un sollecito
                            if(!empty($Fine['ReminderDate'])){
                                $FineReminder =             $this->db->getArrayLine($this->db->Select('FineReminder', "FineId=$FineId AND PrintDate='{$Fine['ReminderDate']}' AND FlowDate IS NOT NULL"));
                            } else $FineReminder = null;
                            
                            $b_RicercaIUV = $RendPag->TipoChiave == PagoPAERConst::KEYT_IUV;
                            $b_IUVRidotto = $b_RicercaIUV && isset($iuv) && $iuv == $Fine['PagoPA1'] && empty($FineReminder);
                            $b_IUVRata = $this->paymentRateId > 0;
                            $b_PagamentoRidotto = false;
                            $FineArticle=                   $this->db->getArrayLine($this->db->Select("FineArticle","FineId=$FineId"));
                            $ArticleTariff=                 $this->db->getArrayLine($this->db->Select("ArticleTariff","ArticleId={$FineArticle['ArticleId']} and Year={$Fine['ProtocolYear']}"));
                            $PaymentRate =                  $this->db->getArrayLine($this->db->Select("PaymentRate", "FineId=$FineId AND StatusRateId = ".RATEIZZAZIONE_APERTA));
                            
                            $rs_FineHistoryFlow =           $this->db->Select("FineHistory","FineId=$FineId and NotificationTypeId in (6)");;
                            
                            $DataNotifica=$this->DeterminaDataNotifica($rs_FineHistoryFlow);
                            $DataPagamento=$RendPag->DataPagamento ?? $RendPag->DataVersamento ?? date('Y-m-d');
                            
                            if($ArticleTariff){
                                $b_PagamentoRidotto = $ArticleTariff['ReducedPayment'] == 1;
                            }
                            
                            $str_TipoSanzione = $this->DeterminaTipoSanzione($DataNotifica ?: date('Y-m-d'), date_create($DataPagamento)->format('Y-m-d'), $b_RicercaIUV, $b_IUVRidotto, $b_PagamentoRidotto, !empty($FineReminder), $b_IUVRata, !empty($PaymentRate));
                            
                            $this->Log('N', 'DeterminaTipoSanzione', $str_TipoSanzione);
                            
                            switch($str_TipoSanzione){
                                case 'Ridotta': $PaymentDocumentId = 0; break; //0 ridotto
                                case 'Minima':  $PaymentDocumentId = 1; break; //1 normale
                                case 'Massima': $PaymentDocumentId = 2; break; //2 Maggiorato
                                default:        $PaymentDocumentId = 0;
                            }
                            
                            $this->Log('N', __FUNCTION__, "Importo pagato: ".$this->f($RendPag->Importo));
                            
                            // FinePaymentSpecificationType Da capitolato: gestione somme riscosse 1)prima copre sanzione - 2) prima copre notifica 3) prima copre la ricerca 
                            $a_Fee = separatePayment($this->customer['FinePaymentSpecificationType'], $PaymentDocumentId, false, $this->f($RendPag->Importo), $FineId, $this->customer['CityId'], $Fine['ProtocolYear'], date_create($DataPagamento)->format('Y-m-d'), $Fine['ReminderDate'], $this->db);
                            
                            $a_FinePayment[]= array('field'=>'PaymentDocumentId','selector'=>'value','type'=>'int','value' => $PaymentDocumentId,'settype'=>'int');
                            $a_FinePayment[]= array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$a_Fee['Fee'],'settype'=>'flt');
                            $a_FinePayment[]= array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['ResearchFee'],'settype'=>'flt');
                            $a_FinePayment[]= array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['NotificationFee'],'settype'=>'flt');
                            $a_FinePayment[]= array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt');
                            $a_FinePayment[]= array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CustomerFee'],'settype'=>'flt');
                            $a_FinePayment[]= array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CanFee'],'settype'=>'flt');
                            $a_FinePayment[]= array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CadFee'],'settype'=>'flt');
                        }
                        
                        $FinePaymentId = $this->db->Insert("FinePayment", $a_FinePayment);
                        
                        $a_FinePaymentReporting = array(
                            array('field'=>'FinePaymentId','selector'=>'value','type'=>'int','value'=>$FinePaymentId, 'settype'=>'int'),
                            array('field'=>'IdentificativoPagamentoEntraNext','selector'=>'value','type'=>'str','value'=>$RendPag->IdentificativoPagamentoEntraNext),
                            array('field'=>'CodiceFiscaleEnte','selector'=>'value','type'=>'str','value'=>$RendPag->CodiceFiscaleEnte ?? ''),
                            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$this->customer['CityId']),
                            array('field'=>'Provenienza','selector'=>'value','type'=>'str','value'=>$RendPag->Provenienza ?? ''),
                            array('field'=>'DescrizioneProvenienza','selector'=>'value','type'=>'str','value'=>$RendPag->DescrizioneProvenienza ?? ''),
                            array('field'=>'Modalita','selector'=>'value','type'=>'str','value'=>$RendPag->Modalita ?? ''),
                            array('field'=>'DescrizioneModalita','selector'=>'value','type'=>'str','value'=>$RendPag->DescrizioneModalita ?? ''),
                            array('field'=>'AnnoImposta','selector'=>'value','type'=>'int','value'=>$RendPag->AnnoImposta ?? 0,'settype'=>'int'),
                            array('field'=>'RiferimentoPraticaEsterna','selector'=>'value','type'=>'str','value'=>$RendPag->RiferimentoPraticaEsterna ?? ''),
                            array('field'=>'CodiceFiscale','selector'=>'value','type'=>'str','value'=>$RendPag->CodiceFiscale ?? ''),
                            array('field'=>'Cassa','selector'=>'value','type'=>'str','value'=>$RendPag->Cassa ?? ''),
                            array('field'=>'TipoPagamento','selector'=>'value','type'=>'str','value'=>$RendPag->TipoPagamento ?? ''),
                            array('field'=>'PresenteRT','selector'=>'value','type'=>'int','value'=>$RendPag->PresenteRT ? 1 : 0),
                            array('field'=>'IdentificativoIncasso','selector'=>'value','type'=>'int','value'=>$RendPag->IdentificativoIncasso ?? 0,'settype'=>'int'),
                        );
                        
                        !empty($RendPag->DataVersamento) ? $a_FinePaymentReporting[] = array('field'=>'DataVersamento','selector'=>'value','type'=>'str','value'=>$RendPag->DataVersamento) : null;
                        !empty($RendPag->DataRiversamento) ? $a_FinePaymentReporting[] = array('field'=>'DataRiversamento','selector'=>'value','type'=>'str','value'=>$RendPag->DataRiversamento) : null;
                        !empty($RendPag->DataAccredito) ? $a_FinePaymentReporting[] = array('field'=>'DataAccredito','selector'=>'value','type'=>'str','value'=>$RendPag->DataAccredito) : null;
                        !empty($RendPag->DataConsolidamento) ? $a_FinePaymentReporting[] = array('field'=>'DataConsolidamento','selector'=>'value','type'=>'str','value'=>$RendPag->DataConsolidamento) : null;
                        !empty($RendPag->DataScadenza) ? $a_FinePaymentReporting[] = array('field'=>'DataScadenza','selector'=>'value','type'=>'str','value'=>$RendPag->DataScadenza) : null;
                        !empty($RendPag->DataInserimento) ? $a_FinePaymentReporting[] = array('field'=>'DataInserimento','selector'=>'value','type'=>'str','value'=>$RendPag->DataInserimento) : null;
                        !empty($RendPag->ID_FLUSSO) ? $a_FinePaymentReporting[] = array('field'=>'ID_FLUSSO','selector'=>'value','type'=>'int','value'=>$RendPag->ID_FLUSSO,'settype'=>'int') : null;
                        !empty($RendPag->Quietanza) ? $a_FinePaymentReporting[] = array('field'=>'Quietanza','selector'=>'value','type'=>'int','value'=>$RendPag->Quietanza,'settype'=>'int') : null;
                        !empty($RendPag->ContoCorrente) ? $a_FinePaymentReporting[] = array('field'=>'ContoCorrente','selector'=>'value','type'=>'str','value'=>$RendPag->ContoCorrente) : null;
                        !empty($RendPag->IBAN) ? $a_FinePaymentReporting[] = array('field'=>'IBAN','selector'=>'value','type'=>'str','value'=>$RendPag->IBAN) : null;
                        !empty($RendPag->Iuv) ? $a_FinePaymentReporting[] = array('field'=>'Iuv','selector'=>'value','type'=>'str','value'=>$RendPag->Iuv) : null;
                        !empty($RendPag->CodiceAvviso) ? $a_FinePaymentReporting[] = array('field'=>'CodiceAvviso','selector'=>'value','type'=>'str','value'=>$RendPag->CodiceAvviso) : null;
                        !empty($RendPag->PagatoAFronteDi) ? $a_FinePaymentReporting[] = array('field'=>'PagatoAFronteDi','selector'=>'value','type'=>'str','value'=>$RendPag->PagatoAFronteDi) : null;
                        !empty($RendPag->RiferimentoDocumentoPagamento) ? $a_FinePaymentReporting[] = array('field'=>'RiferimentoDocumentoPagamento','selector'=>'value','type'=>'str','value'=>$RendPag->RiferimentoDocumentoPagamento) : null;
                        !empty($RendPag->TipoImmagine) ? $a_FinePaymentReporting[] = array('field'=>'TipoImmagine','selector'=>'value','type'=>'str','value'=>$RendPag->TipoImmagine) : null;
                        !empty($RendPag->Url) ? $a_FinePaymentReporting[] = array('field'=>'Url','selector'=>'value','type'=>'str','value'=>$RendPag->Url) : null;
                        !empty($RendPag->NomeFile) ? $a_FinePaymentReporting[] = array('field'=>'NomeFile','selector'=>'value','type'=>'str','value'=>$RendPag->NomeFile) : null;
                        !empty($RendPag->CausaleBonifico) ? $a_FinePaymentReporting[] = array('field'=>'CausaleBonifico','selector'=>'value','type'=>'str','value'=>substr($RendPag->CausaleBonifico, 0, 512)) : null;
                        !empty($RendPag->NumeroProvvisorioEntrata) ? $a_FinePaymentReporting[] = array('field'=>'NumeroProvvisorioEntrata','selector'=>'value','type'=>'int','value'=>$RendPag->NumeroProvvisorioEntrata,'settype'=>'int') : null;
                        !empty($RendPag->AnnoProvvisorioEntrata) ? $a_FinePaymentReporting[] = array('field'=>'AnnoProvvisorioEntrata','selector'=>'value','type'=>'int','value'=>$RendPag->AnnoProvvisorioEntrata,'settype'=>'int') : null;
                        !empty($RendPag->IdentificativoFlussoPagoPA) ? $a_FinePaymentReporting[] = array('field'=>'IdentificativoFlussoPagoPA','selector'=>'value','type'=>'str','value'=>$RendPag->IdentificativoFlussoPagoPA) : null;
                        !empty($RendPag->IdentificativoRegolamentoFlussoPagoPA) ? $a_FinePaymentReporting[] = array('field'=>'IdentificativoRegolamentoFlussoPagoPA','selector'=>'value','type'=>'str','value'=>$RendPag->IdentificativoRegolamentoFlussoPagoPA) : null;
                        !empty($RendPag->IdentificativoRiscossioneFlussoPagoPA) ? $a_FinePaymentReporting[] = array('field'=>'IdentificativoRiscossioneFlussoPagoPA','selector'=>'value','type'=>'str','value'=>$RendPag->IdentificativoRiscossioneFlussoPagoPA) : null;
                        !empty($RendPag->CodiceEsitoDettaglioFlussoPagoPA) ? $a_FinePaymentReporting[] = array('field'=>'CodiceEsitoDettaglioFlussoPagoPA','selector'=>'value','type'=>'int','value'=>$RendPag->CodiceEsitoDettaglioFlussoPagoPA, 'settype' => 'int') : null;
                        !empty($RendPag->EsitoDettaglioFlussoPagoPA) ? $a_FinePaymentReporting[] = array('field'=>'EsitoDettaglioFlussoPagoPA','selector'=>'value','type'=>'str','value'=>$RendPag->EsitoDettaglioFlussoPagoPA) : null;
                        !empty($RendPag->CodiceFiscaleEnteBeneficiario) ? $a_FinePaymentReporting[] = array('field'=>'CodiceFiscaleEnteBeneficiario','selector'=>'value','type'=>'str','value'=>$RendPag->CodiceFiscaleEnteBeneficiario) : null;
                        !empty($RendPag->CanalePagamentoPagoPA) ? $a_FinePaymentReporting[] = array('field'=>'CanalePagamentoPagoPA','selector'=>'value','type'=>'str','value'=>$RendPag->CanalePagamentoPagoPA) : null;
                        !empty($RendPag->DenominazionePSP) ? $a_FinePaymentReporting[] = array('field'=>'DenominazionePSP','selector'=>'value','type'=>'str','value'=>$RendPag->DenominazionePSP) : null;
                        !empty($RendPag->IdentificativoPSP) ? $a_FinePaymentReporting[] = array('field'=>'IdentificativoPSP','selector'=>'value','type'=>'str','value'=>$RendPag->IdentificativoPSP) : null;
                        
                        $a_FinePaymentNotification = array(
                            array('field'=>'Deleted','selector'=>'value','type'=>'int','value'=>1),
                        );
                        
                        $this->db->Insert("FinePaymentReporting", $a_FinePaymentReporting);
                        $this->db->Update("FinePaymentNotification", $a_FinePaymentNotification, "IdentificativoIncasso='{$RendPag->IdentificativoIncasso}'");
                        $this->db->End_Transaction();
                        
                        $this->EsitoImposta(1, 'Il pagamento è stato registrato da SCDS con successo');
                    } else $this->EsitoImposta(7, "Errore nel salvataggio dei dati, riprovare.");
                } else $this->EsitoImposta(8, 'Il pagamento è stato scartato da SCDS in quanto risulta già in SCDS un versamento con l’identificativo pagamento EntraNext passatoci.');
            }
        } catch (Throwable $e){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, $e);
            $InviaPagamentoResponse = new InviaPagamentoResponse();
        }
        
        $this->ValutaRisposta();
        
        $InviaPagamentoResponse->Esito = PagoPAERConst::ESITI[$this->esito];
        $InviaPagamentoResponse->Descrizione = $this->esitoDescrizione ?? null;
        
        $this->Log('N', __FUNCTION__, "Esito: {$InviaPagamentoResponse->Esito}, Descrizione: {$InviaPagamentoResponse->Descrizione}");
        
        return $InviaPagamentoResponse;
    }
    
    function VerificaInvioPagamento($r):VerificaInvioPagamentoResponse {
        $this->metodoChiamato = __FUNCTION__;
        
        $VerificaInvioPagamentoResponse = new VerificaInvioPagamentoResponse();
        
        try{
            if(isset($this->customer)){
                $this->Log('N', __FUNCTION__, "IdentificativoPagamentoEntraNext: {$r->IdentificativoPagamentoEntraNext}");
                
                if(!empty($r->IdentificativoPagamentoEntraNext) && $this->ValidaGUID($r->IdentificativoPagamentoEntraNext))
                {
                    $FinePaymentReporting=$this->db->getArrayLine($this->db->Select("FinePaymentReporting","IdentificativoPagamentoEntraNext='{$r->IdentificativoPagamentoEntraNext}' and CityId = '{$this->customer['CityId']}'"));
                    if (isset($FinePaymentReporting))
                    {
                        $FinePayment=$this->db->getArrayLine($this->db->Select("FinePayment","Id={$FinePaymentReporting['FinePaymentId']}"));
                        
                        if ($FinePayment['FineId'] >0)
                            $this->EsitoImposta(1, "Il pagamento risulta già registrato in SCDS.");
                            else $this->EsitoImposta(6, "SCDS non ha alcun pagamento con identificativo pari al GUID passato in input associato ad un verbale.");
                    } else $this->EsitoImposta(9, "SCDS non ha alcun pagamento con identificativo pari al GUID passato in input inserito a sistema.");
                } else $this->EsitoImposta(7, "È necessario fornire un identificativo all'incasso valido.");
            }
        } catch (Throwable $e){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, $e);
            $VerificaInvioPagamentoResponse = new VerificaInvioPagamentoResponse();
        }
        
        $this->ValutaRisposta();
        
        $VerificaInvioPagamentoResponse->Esito = PagoPAERConst::ESITI[$this->esito ?? 1];
        $VerificaInvioPagamentoResponse->Descrizione = $this->esitoDescrizione ?? null;
        
        $this->Log('N', __FUNCTION__, "Esito: {$VerificaInvioPagamentoResponse->Esito}, Descrizione: {$VerificaInvioPagamentoResponse->Descrizione}");
        
        return $VerificaInvioPagamentoResponse;
    }
    
    function RiceviStatoRiconciliazionePagamento($r):RiceviStatoRiconciliazionePagamentoResponse {
        $this->metodoChiamato = __FUNCTION__;
        $RiceviStatoRiconciliazionePagamentoResponse = new RiceviStatoRiconciliazionePagamentoResponse();
        
        try{
            if(isset($this->customer)){
                $this->Log('N', __FUNCTION__, "IdentificativoPagamentoEntraNext: {$r->IdentificativoPagamentoEntraNext}");
                
                $a_VerbaliSoggetto = array();
                $FineId = null;
                $ImportoInput = null;
                
                if(!empty($r->IdentificativoPagamentoEntraNext) && $this->ValidaGUID($r->IdentificativoPagamentoEntraNext)){
                    $FinePaymentReporting=$this->db->getArrayLine($this->db->Select("FinePaymentReporting","IdentificativoPagamentoEntraNext='{$r->IdentificativoPagamentoEntraNext}' and CityId = '{$this->customer['CityId']}'"));
                    
                    if (isset($FinePaymentReporting)){
                        
                        $FinePayment=$this->db->getArrayLine($this->db->Select("FinePayment","Id={$FinePaymentReporting['FinePaymentId']}"));
                        $FineId = $FinePayment['FineId'];
                        $ImportoInput = $FinePayment['Amount'];
                        if ($FineId >0){
                            $rs_Fine = $this->db->Select("Fine","Id = {$FineId}");
                            while ($Fine = $this->db->getArrayLine($rs_Fine)){
                                $Verbale = new Verbale();
                                $Soggetto = new SoggettoPM();
                                $VerbaleSoggetto = new VerbaleSoggetto();
                                $VerbaleSoggetto->setSoggetto($this->RiempiSoggettoDaBD($Fine, $Soggetto));
                                $VerbaleSoggetto->setVerbale($this->RiempiVerbaleDaBD(PagoPAERConst::KEYT_CDS, $Fine, $Verbale, $ImportoInput, $this->trespasserid));
                                array_push($a_VerbaliSoggetto, $VerbaleSoggetto);
                            }
                            
                            if(!empty($a_VerbaliSoggetto)){
                                if ($this->esito == 7)
                                    $this->EsitoImposta(7, "Si è verificato un errore nel recupero dei dati del verbale rendicontato.");
                                else {
                                    $this->EsitoImposta(1, "SCDS ha effettuato la riconciliazione del pagamento.");
                                    $RiceviStatoRiconciliazionePagamentoResponse->VerbaliAssociati = $a_VerbaliSoggetto;
                                }
                            } else {
                                $this->EsitoImposta(6, 'La ricerca non ha prodotto risultati.');
                            }
                        } else $this->EsitoImposta(10, "Il pagamento è in fase di riconciliazione da parte di SCDS.");
                    } else $this->EsitoImposta(6, "SCDS non conosce il GUID del pagamento in input.");
                } else $this->EsitoImposta(7, "È necessario fornire un identificativo all'incasso valido.");
            }
        } catch(Throwable $e){
            http_response_code(500);
            $this->Log('D', __FUNCTION__, $e);
            $RiceviStatoRiconciliazionePagamentoResponse = new RiceviStatoRiconciliazionePagamentoResponse();
        }
        
        $this->ValutaRisposta();
        
        $RiceviStatoRiconciliazionePagamentoResponse->Esito = PagoPAERConst::ESITI[$this->esito];
        $RiceviStatoRiconciliazionePagamentoResponse->Descrizione = $this->esitoDescrizione ?? null;
        
        $this->Log('N', __FUNCTION__, "Esito: {$RiceviStatoRiconciliazionePagamentoResponse->Esito}, Descrizione: {$RiceviStatoRiconciliazionePagamentoResponse->Descrizione}");
        
        return $RiceviStatoRiconciliazionePagamentoResponse;
    }
}
