<?php
use traffic_law\cls\visureMCTC\Controlli;

require("_path.php");
require(INC ."/parameter.php");
require(CLS ."/cls_db.php");
require(INC ."/function.php");
require(INC ."/function_visure.php");
require_once(CLS."/visureMCTC/Controlli.php");
require(INC ."/initialization.php");
require(CLS ."/cls_ws_mctc.php");

global $rs;

if (!is_dir(NATIONAL_REQUEST_MCTC_WS)) {
    mkdir(NATIONAL_REQUEST_MCTC_WS, true);
    chmod(NATIONAL_REQUEST_MCTC_WS, 0750);
}

$DataSourceId = 2;
$DataSourceDate = date("Y-m-d");
$DataSourceTime = date('H:i:s');

$PageTitle = CheckValue('PageTitle','s');
$CityId = $_SESSION['cityid'];
$UserName = $_SESSION['username'];

$rs_MCTCLogin = $rs->Select('Customer',"CityId='".$CityId."'");
$r_MCTCLogin = mysqli_fetch_array($rs_MCTCLogin);

$CountryId      = 'Z000';
$ZoneId         = 1;
$LanguageId     = 1;

$server = 'infoftp.dtt';
$cont = $err_cont = 0;

$str_FileNameResponse = "Response_" . date("Y-m-d_H-i");
$str_FileNameRequest = "Request_" . date("Y-m-d_H-i");

if(PRODUCTION){
    $conn = @ftp_connect($server);
    if (! $conn) {
        
        $output = shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
        sleep(3);
        $conn = @ftp_connect($server);
        if (! $conn) {
            
            echo "VPN non attiva. Error:".$output;
            
            DIE;
            
        }
    }
}

if (isset($_POST['checkbox'])) {
    $listaCittàCappate = array();
    $rsCittàCappate = $rs->SelectQuery("SELECT DISTINCT Title FROM sarida.ZIPCity");
    
    while ($vCittàCappata = mysqli_fetch_array($rsCittàCappate))
        $listaCittàCappate[] = strtoupper($vCittàCappata['Title']);
    
    //Carica la mappa delle marca modello eventualmente già corrette
    $a_AnomalyBrandModelMap = buildAnomalyBrandModelMap();
    
    foreach ($_POST['checkbox'] as $FineId) {
        $Genre          = "";
        $a_Trespasser   = array();
        $a_Vehicle      = array();
        $a_Error        = array();

        $rs_Fine=$rs->Select('Fine', "Id=".$FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);

        $VehicleTypeId      = $r_Fine['VehicleTypeId'];
        $VehiclePlate       = $r_Fine['VehiclePlate'];
        
        $b_ErrorSoap = false;

        $ws = new cls_ws_mctc();
        
        if(PRODUCTION){
            $ws->setLogin($r_MCTCLogin['MCTCUserName'],$r_MCTCLogin['MCTCPassword']);
            
            if($VehicleTypeId==2) {
                $str_Service = "WS_GET_MotorcycleTrespasser_With_Plate";
            } else if($VehicleTypeId==9){
                $str_Service = "WS_GET_MopedTrespasser_With_Plate";
            } else if($VehicleTypeId==7) {
                $str_Service = "WS_GET_TruckTrespasser_With_Plate";
            } else{
                $str_Service = "WS_GET_CarTrespasser_With_Plate";
            }
            
            $ws->setService($str_Service);
            $ws->setParameters(array('numeroTarga'=>$VehiclePlate));
            
            $response = $ws->soapConnect();
            
            //Salva l'xml della richiesta
            file_put_contents(
                NATIONAL_REQUEST_MCTC_WS."/$str_FileNameRequest",
                "$FineId---------------------------------".PHP_EOL.($ws->lastRequest ?: '').PHP_EOL,
                FILE_APPEND);
            
            //Salva l'xml della risposta
            file_put_contents(
                NATIONAL_REQUEST_MCTC_WS."/$str_FileNameResponse",
                "$FineId---------------------------------".PHP_EOL.($ws->lastResponse ?: '').PHP_EOL,
                FILE_APPEND);
            
            if( isset($response['SOAP-ENV:Fault']['faultcode'])){
                $b_ErrorSoap = true;
                $a_Error['ErrorCode'] = "0X0";
                $a_Error['ErrorDescription'] = $response['SOAP-ENV:Fault']['faultstring']['_value'];
            } else if(isset($response["dettaglioAutoveicoloComproprietariResponse"]["errore"])){
                $b_ErrorSoap = true;
                $a_Error['ErrorCode'] = (isset($response["dettaglioAutoveicoloComproprietariResponse"]["errore"]["codiceErrore"])) ? $response["dettaglioAutoveicoloComproprietariResponse"]["errore"]["codiceErrore"] : '';
                $a_Error['ErrorDescription'] = '';
            } else {
                $a_response = Return_Array_GET_Trespasser_With_Plate($response);
            }
            
            if(isset($a_response['ErrorCode']) || $b_ErrorSoap){
                $ResponseId = 0;
                if(! $b_ErrorSoap){
                    $a_Error['ErrorCode'] = $a_response['ErrorCode'];
                    $a_Error['ErrorDescription'] = $a_response['ErrorDescription'];
                }
                
            } else {
                $ResponseId = 1;
            }
        } else {
            if (!is_dir(TESTVISURE_FOLDER."/massive_ws")) {
                mkdir(TESTVISURE_FOLDER."/massive_ws", true);
                chmod(TESTVISURE_FOLDER."/massive_ws", 0770);
            }
            
            $xmlTest = file_get_contents(TESTVISURE_FOLDER."/massive_ws/$FineId");
            
            if (!$xmlTest) continue;
            
            $root = new DOMDocument();
            $root->loadXML($xmlTest);
            $xmlArray = $ws->xml_to_array($root,'inf');
            
            $a_response = Return_Array_GET_Trespasser_With_Plate($xmlArray['SOAP-ENV:Envelope']['SOAP-ENV:Body']);
            
            $ResponseId = 1;
        }
        
        $insertRequest = array(
            array('field' => 'rse_ente','selector' => 'value','type' => 'str','value' => $CityId),
            array('field' => 'rse_utente_servizio','selector' => 'value','type' => 'str','value' => $r_MCTCLogin['MCTCUserName']),
            array('field' => 'rse_ute_richiesta','selector' => 'value','type' => 'str','value' => $UserName),
            array('field' => 'rse_ute_risposta','selector' => 'value','type' => 'str','value' => $UserName),
            array('field' => 'rse_data_richiesta','selector' => 'value','type' => 'date','value' => date("Y-m-d")),
            array('field' => 'rse_ora_richiesta','selector' => 'value','type' => 'str','value' => date("H:m:i")),
            array('field' => 'rse_tipo','selector' => 'value','type' => 'int','value' => 13, 'settype' => 'int'),
            array('field' => 'rse_desc_errore','selector' => 'value','type' => 'str','value' => substr($a_Error['ErrorDescription'] ?? '', 0, 200) ?: null, 'nullable' => true),
            array('field' => 'rse_cod_errore','selector' => 'value','type' => 'str','value' => $a_Error['ErrorCode'] ?? null, 'nullable' => true),
            array('field' => 'rse_esito','selector' => 'value','type' => 'str','value' => empty($a_Error) ? 'S' : 'N'),
        );
        
        $codRichiesta = $rs->insert("richieste_servizi_esterni", $insertRequest);

        if($ResponseId){
            $insertRequest = array(
                array('field' => 'der_cod_richiesta','selector' => 'value','type' => 'str','value' => $codRichiesta, 'settype' => 'str'),
                array('field' => 'der_progressivo','selector' => 'value','type' => 'int','value' => 1, 'settype' => 'int'),
                array('field' => 'der_oggetto','selector' => 'value','type' => 'str','value' => $VehiclePlate),
            );
            
            if(isset($a_response['denominazionePersonaGiuridica'])){
                $Genre = "D";
                $a_Trespasser['CompanyName']                                = (isset($a_response['denominazionePersonaGiuridica'])) ? $a_response['denominazionePersonaGiuridica'] : "" ;
                $a_Trespasser['CompanyType']                                = (isset($a_response['tipoSocieta'])) ? $a_response['tipoSocieta'] : "" ;
                $a_Trespasser['Province']                                   = (isset($a_response['siglaProvincia'])) ? $a_response['siglaProvincia'] : "" ;
                $a_Trespasser['City']                                       = (isset($a_response['descrizioneComune'])) ? $a_response['descrizioneComune'] : "" ;
                $a_Trespasser['Address']                                    = (isset($a_response['indirizzo'])) ? $a_response['indirizzo'] : "" ;
                $a_Trespasser['VatCode']                                    = (isset($a_response['codiceFiscalePartitaIVA'])) ? $a_response['codiceFiscalePartitaIVA'] : "" ;

                $insertRequest[] = array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $a_Trespasser['CompanyName']);
            } else {
                $Genre = "M";
                $a_Trespasser['VatCode']                                    = "";
                $a_Trespasser['Name']                                       = (isset($a_response['nome'])) ? $a_response['nome'] : "" ;
                $a_Trespasser['Surname']                                    = (isset($a_response['cognome'])) ? $a_response['cognome'] : "" ;
                $a_Trespasser['BornDate']                                   = (isset($a_response['dataNascita'])) ? $a_response['dataNascita'] : "" ;
                if(strpos($a_Trespasser['BornDate'], '-') !== false) $a_Trespasser['BornDate'] = DateOutDB($a_Trespasser['BornDate']);
                $a_Trespasser['TaxCode']                                    = (isset($a_response['codiceFiscale'])) ? $a_response['codiceFiscale'] : "" ;
                $a_Trespasser['City']                                       = (isset($a_response['comuneResidenza'])) ? $a_response['comuneResidenza'] : "" ;
                $a_Trespasser['Address']                                    = (isset($a_response['indirizzoResidenza'])) ? $a_response['indirizzoResidenza'] : "" ;
                $a_Trespasser['Province']                                   = (isset($a_response['provinciaResidenza'])) ? $a_response['provinciaResidenza'] : "" ;

                if(isset($a_response['localitaEstera'])){
                    $a_Trespasser['BornPlace']                              = $a_response['localitaEstera']. ' - ' .$a_response['codiceInternazionaleEstero'];
                    $BornCountryId                                          = '';
                }else{
                    $a_Trespasser['BornPlace']                              = $a_response['descrizioneComune'];
                    $BornCountryId                                          = 'Z000';
                }
                
                $insertRequest[] = array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $a_Trespasser['Name'].' '.$a_Trespasser['Surname']);
            }
            
            $rs->insert("dettaglio_richieste_servizi_est", $insertRequest);

            $a_Vehicle['VehiclePlate']                                      = (isset($a_response['targaVeicolo'])) ? $a_response['targaVeicolo'] : "" ;
            $a_Vehicle['VehicleType']                                       = (isset($a_response['tipoVeicolo'])) ? $a_response['tipoVeicolo'] : "" ;
            $a_Vehicle['dataInizioProprieta']                               = (isset($a_response['dataInizioProprieta'])) ? $a_response['dataInizioProprieta'] : "" ;
            $a_Vehicle['dataPrimaImmatricolazione']                         = (isset($a_response['dataPrimaImmatricolazione'])) ? $a_response['dataPrimaImmatricolazione'] : "" ;
            $a_Vehicle['numeroTelaio']                                      = (isset($a_response['numeroTelaio'])) ? $a_response['numeroTelaio'] : "" ;
            $a_Vehicle['codiceOmologazione']                                = (isset($a_response['codiceOmologazione'])) ? $a_response['codiceOmologazione'] : "" ;
            $a_Vehicle['VehicleModel']                                      = (isset($a_response['denominazioneCommercialeVeicolo'])) ? $a_response['denominazioneCommercialeVeicolo'] : "" ;
            $a_Vehicle['origine']                                           = (isset($a_response['origine'])) ? $a_response['origine'] : "" ;
            $a_Vehicle['VehicleBrand']                                      = (isset($a_response['modelloVeicolo'])) ? $a_response['modelloVeicolo'] : "" ;
            $a_Vehicle['VehicleColor']                                      = (isset($a_response['carrozzeria'])) ? $a_response['carrozzeria'] : "" ;
            $a_Vehicle['categoria']                                         = (isset($a_response['categoria'])) ? $a_response['categoria'] : "" ;
            $a_Vehicle['usoVeicolo']                                        = (isset($a_response['usoVeicolo'])) ? $a_response['usoVeicolo'] : "" ;
            $a_Vehicle['VehicleLastRevisionDate']                           = (isset($a_response['dataUltimaRevisione'])) ? $a_response['dataUltimaRevisione'] : "" ;
            if(strpos($a_Vehicle['VehicleLastRevisionDate'], '-') !== false) $a_Vehicle['VehicleLastRevisionDate'] = DateOutDB($a_Vehicle['VehicleLastRevisionDate']);
            $a_Vehicle['VehicleLastRevisionResult']                         = (isset($a_response['esitoUltimaRevisione'])) ? $a_response['esitoUltimaRevisione'] : "" ;
            $a_Vehicle['codiceAntifalsificazioneTagliandoUltimaRevisione']  = (isset($a_response['codiceAntifalsificazioneTagliandoUltimaRevisione'])) ? $a_response['codiceAntifalsificazioneTagliandoUltimaRevisione'] : "" ;
            $a_Vehicle['numeroCartaCircolazione']                           = (isset($a_response['numeroCartaCircolazione'])) ? $a_response['numeroCartaCircolazione'] : "" ;
            $a_Vehicle['siglaUMC']                                          = (isset($a_response['siglaUMC'])) ? $a_response['siglaUMC'] : "" ;
            $a_Vehicle['lunghezzaVeicoloInMetri']                           = (isset($a_response['lunghezzaVeicoloInMetri'])) ? $a_response['lunghezzaVeicoloInMetri'] : "" ;
            $a_Vehicle['larghezzaVeicoloInMetri']                           = (isset($a_response['larghezzaVeicoloInMetri'])) ? $a_response['larghezzaVeicoloInMetri'] : "" ;
            $a_Vehicle['numeroPostiTotali']                                 = (isset($a_response['numeroPostiTotali'])) ? $a_response['numeroPostiTotali'] : "" ;

            $a_Vehicle['VehicleMass']                                       = (isset($a_response['massaComplessivaInKG'])) ? $a_response['massaComplessivaInKG']/1000 : 0.00;
            $a_Vehicle['massaComplessivaRimorchioInKG']                     = (isset($a_response['massaComplessivaRimorchioInKG'])) ? $a_response['massaComplessivaRimorchioInKG'] : 0.00 ;
            $a_Vehicle['taraInKG']                                          = (isset($a_response['taraInKG'])) ? $a_response['taraInKG'] : 0.00 ;

            if(is_array($a_Vehicle['VehicleBrand'])) $a_Vehicle['VehicleBrand'] = "";
            if(is_array($a_Vehicle['VehicleModel'])) $a_Vehicle['VehicleModel'] = "";
            if(is_array($a_Vehicle['VehicleColor'])) $a_Vehicle['VehicleColor'] = "";
            
            
            $TaxCode = (isset($a_Trespasser['TaxCode'])) ? $a_Trespasser['TaxCode'] : '';
            $VatCode = (isset($a_Trespasser['VatCode'])) ? $a_Trespasser['VatCode'] : '';
            $VehicleBrand = $a_Vehicle['VehicleBrand'];
            $VehicleModel = $a_Vehicle['VehicleModel'];
            
            if (strlen(trim($VatCode)) == 0 && strlen(trim($TaxCode)) == 0) {
                $a_Error['ErrorCode'] = "0X0";
                $a_Error['ErrorDescription'] = "P.Iva - C.F. non presente";
            }

            if(empty($VehicleBrand)){
                $a_Error['ErrorCode'] = "0X0";
                $a_Error['ErrorDescription'] = "Marca veicolo non presente";
            }
    
            if(empty($a_Error)){
                $controlli = new Controlli($rs, $listaCittàCappate, $a_Trespasser['City']);
                
                $indirizzoResidenza = controlli::normalizzaIndirizzo($a_Trespasser['Address']);
                $controlli->controllaLungIndirizzo($indirizzoResidenza);
                $codCatastaleResidenza = $controlli->controllaCittàResidenza();
                $ZIP = $controlli->controllaCap($indirizzoResidenza);
                $City		= $a_Trespasser['City'];
                $Province = $controlli->controllaProvincia($a_Trespasser['Province']);
    
                $TrespasserTypeId = 1;
                $StatusTypeId = 10;
    
                $VehicleMass = str_replace(",",".", $a_Vehicle['VehicleMass']) ;
    
                if ($Genre == 'D') {
                    $CompanyName =  $a_Trespasser['CompanyName'];
    
                } else {
    
                    $Surname =  $a_Trespasser['Surname'];
                    $Name =  $a_Trespasser['Name'];
    
                    $BornDate = $a_Trespasser['BornDate'];
                    if($BornDate!=''){
                        if (strlen($BornDate)==10){
                            if($BornDate!=''){
                                $BornDateDay = substr($TaxCode, 9, 2);
                                if ((int)$BornDateDay > 40) {
                                    $Genre = 'F';
                                }
                            }
                        } else $BornDate = '';
                    }
    
                    $BornPlace = (isset($a_Trespasser['BornPlace'])) ? $a_Trespasser['BornPlace'] : '';
                }
    
                if ($Genre == 'D' && strlen(trim($VatCode)) > 0) {
                    $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$CityId."'");
                    $Code = mysqli_fetch_array($rs_Code)['Code'];
    
                    $rs_Trespasser = $rs->Select('Trespasser', "VatCode='" . $VatCode . "' AND CustomerId='" . $CityId . "'");
                    $a_Insert = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                        array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $indirizzoResidenza),
                        array('field' => 'VatCode', 'selector' => 'value', 'type' => 'str', 'value' => $VatCode),
                        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $City),
                        array('field' => 'CityId', 'selector'=>'value', 'type'=>'str', 'value'=>$codCatastaleResidenza),
                        array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $Province),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                        array('field' => 'DataSourceTime','selector'=>'value','type'=>'str','value'=>$DataSourceTime),
                        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate)
                    );
                    
                    if (mysqli_num_rows($rs_Trespasser) == 0) {
                        $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
                    } else {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                        $TrespasserId = $r_Trespasser['Id'];
    
    
                        $a_oldTrespasserData = array(
                            "CompanyName" => StringOutDB($r_Trespasser['CompanyName']),
                            "CountryId" => StringOutDB($r_Trespasser['CountryId']),
                            "City" => StringOutDB($r_Trespasser['City']),
                            "Province" => StringOutDB($r_Trespasser['Province']),
                            "Address" => StringOutDB($r_Trespasser['Address']),
                            "ZIP" => StringOutDB($r_Trespasser['ZIP']),
                        );
    
                        $a_newTrespasserData = array(
                            "CompanyName" => trim($CompanyName),
                            "CountryId" => $CountryId,
                            "City" => trim($City),
                            "Province" => trim($Province),
                            "Address" => trim($indirizzoResidenza),
                            "ZIP" => $ZIP,
                        );
    
                        insertTrespsserHistory($r_Trespasser, $a_oldTrespasserData, $a_newTrespasserData, $DataSourceId);
    
                        $a_Update = $a_Insert;;
    
                        $rs->Update('Trespasser', $a_Update, "Id=" . $TrespasserId . " AND CustomerId='" . $CityId . "'");
    
                    }
    
                } else if (strlen(trim($TaxCode)) > 0){
                    $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$CityId."'");
                    $Code = mysqli_fetch_array($rs_Code)['Code'];
    
                    $rs_Trespasser = $rs->Select('Trespasser', "TaxCode='" . $TaxCode . "' AND CustomerId='" . $CityId . "'");
    
                    $a_BornPlace = explode(' ', $BornPlace);
                    $n_Count = count($a_BornPlace)-1;
                    if($n_Count>1){
                        $str_BornPlace_TMP = '';
    
                        for($i=0; $i<$n_Count; $i++){
                            $str_BornPlace_TMP .= $a_BornPlace[$i].' ';
                        }
    
                        $BornPlace = trim($str_BornPlace_TMP);
                    }
    
                    $a_Insert = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                        array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => $Surname),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Name),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $indirizzoResidenza),
                        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $City),
                        array('field' => 'CityId', 'selector'=>'value', 'type'=>'str', 'value'=>$codCatastaleResidenza),
                        array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $Province),
                        array('field' => 'BornDate', 'selector' => 'value', 'type' => 'date', 'value' => $BornDate),
                        array('field' => 'BornPlace', 'selector' => 'value', 'type' => 'str', 'value' => $BornPlace),
                        array('field' => 'BornCountryId', 'selector' => 'value', 'type' => 'str', 'value' => $BornCountryId),
                        array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                        array('field' => 'DataSourceTime','selector'=>'value','type'=>'str','value'=>$DataSourceTime),
                        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate)
                    );
                    
                    if (mysqli_num_rows($rs_Trespasser) == 0) {
                        $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
                    } else {
                        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                        $TrespasserId = $r_Trespasser['Id'];
    
                        $a_oldTrespasserData = array(
                            "Surname" => StringOutDB($r_Trespasser['Surname']),
                            "Name" => StringOutDB($r_Trespasser['Name']),
                            "CountryId" => StringOutDB($r_Trespasser['CountryId']),
                            "City" => StringOutDB($r_Trespasser['City']),
                            "Province" => StringOutDB($r_Trespasser['Province']),
                            "Address" => StringOutDB($r_Trespasser['Address']),
                            "ZIP" => StringOutDB($r_Trespasser['ZIP']),
                        );
    
                        $a_newTrespasserData = array(
                            "Surname" => trim($Surname),
                            "Name" => trim($Name),
                            "CountryId" => $CountryId,
                            "City" => trim($City),
                            "Province" => trim($Province),
                            "Address" => trim($indirizzoResidenza),
                            "ZIP" => $ZIP,
                        );
    
                        insertTrespsserHistory($r_Trespasser, $a_oldTrespasserData, $a_newTrespasserData, $DataSourceId);
    
                        $a_Update = $a_Insert;
    
                        $rs->Update('Trespasser', $a_Update, "Id=" . $TrespasserId . " AND CustomerId='" . $CityId . "'");
                    }
    
                } 
    
                $a_Insert = array(
                    array(  'field' => 'FineId',            'selector' => 'value',      'type' => 'int',        'value' => $FineId,                 'settype' => 'int'  ),
                    array(  'field' => 'TrespasserId',      'selector' => 'value',      'type' => 'int',        'value' => $TrespasserId,           'settype' => 'int'  ),
                    array(  'field' => 'TrespasserTypeId',  'selector' => 'value',      'type' => 'int',        'value' => $TrespasserTypeId,      'settype' => 'int'  ),
                    array(  'field' => 'Note',              'selector' => 'value',      'type' => 'str',        'value' => "Inserimento da Import motorizzazione"       ),
                );
    
                $rs->Insert('FineTrespasser', $a_Insert);
    
                $cont++;

                //INIZIO verifica marca modello
                $b_ValidBrandModel = checkModelBrand($a_AnomalyBrandModelMap,$VehicleBrand,$VehicleModel,$VehicleTypeId,$FineId,1,$DataSourceId);
                //FINE verifica marca modello
                
                $a_Update = array(
                    array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand),
                    array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel),
                    array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'flt', 'value' => $VehicleMass, 'settype' => 'flt'),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                );
                
                if($b_ValidBrandModel){
                    $a_Update[] = array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand);
                    $a_Update[] = array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel);
                    
                    $a_HistoryFineAnomalyBrandModel = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>'WrongBrand','selector'=>'value','type'=>'str','value'=>StringOutDB($r_Fine['VehicleBrand'])),
                        array('field'=>'WrongModel','selector'=>'value','type'=>'str','value'=>StringOutDB($r_Fine['VehicleModel'])),
                        array('field'=>'WrongVehicleTypeId','selector'=>'value','type'=>'int','value'=>$VehicleTypeId,'settype'=>'int'),
                        array('field'=>'CorrectBrand','selector'=>'value','type'=>'str','value'=>$VehicleBrand),
                        array('field'=>'CorrectModel','selector'=>'value','type'=>'str','value'=>$VehicleModel),
                        array('field'=>'CorrectVehicleTypeId','selector'=>'value','type'=>'int','value'=>$VehicleTypeId,'settype'=>'int'),
                    );
                    $rs->Insert("HistoryFineAnomalyBrandModel", $a_HistoryFineAnomalyBrandModel);
                }
    
                $rs->Update('Fine', $a_Update, 'Id=' . $FineId);
    
                if ($VehicleMass > MASS) {
                    $rs_rows = $rs->SelectQuery("
                        SELECT F.FineTime, AT.ArticleId, AT.Fee, AT.MaxFee, AT.AdditionalNight
                        FROM Fine F JOIN FineArticle FA ON F.Id = FA.FineId
                        JOIN ArticleTariff AT ON AT.ArticleId = FA.ArticleId
                        WHERE AT.AdditionalMass = 1 AND FA.FineId=" . $FineId);
    
    
                    while ($rs_row = mysqli_fetch_array($rs_rows)) {
                        $Fee = $rs_row['Fee'] * FINE_MASS;
                        $MaxFee = $rs_row['MaxFee'] * FINE_MASS;
                        //NOTA 30/06/2022 condizioniamo il calcolo della maggiorazione notturna agli articoli che la prevedono altrimenti viene applicata sempre
                        // si è visto comunque che non è un problema perché gli articoli che prevedono addizionale massa prevedono anche la notturna (almeno fino ad ora)
                        if ($rs_row['AdditionalNight'] == 1)
                        {
                            $FineTime = $rs_row['FineTime'];
                            $aTime = explode(":", $FineTime);
    
                            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                                //FINE_MINUTE_START_DAY
                                //FINE_MINUTE_END_DAY
                                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
                            }
                        }
    
                        $a_Update = array(
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                        );
    
                        $rs->Update('FineArticle', $a_Update, 'ArticleId=' . $rs_row['ArticleId'] . " AND FineId=" . $FineId);
                    }
                }
            }
            
            if(!empty($controlli->listaAnomalie)){
                $a_Error['ErrorDescription'] = implode("; ", $controlli->listaAnomalie);
            }
        }
        
        if(!empty($a_Error)){
            $err_cont++;
            $rs_FineAnomaly = $rs->Select("FineAnomaly", "FineId=$FineId");
            if(mysqli_num_rows($rs_FineAnomaly) <= 0){
                $a_Insert = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                    array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => $a_Error['ErrorDescription']),
                );
                $rs->Insert('FineAnomaly', $a_Insert);
            }
        }
        
        sleep(2);
    }

    $str_Success = "Sono stati elaborati ". $cont . " record";
    if($err_cont>0){
        $str_Success .= "<br>". $err_cont ." record sono stati inseriti in anomalie";

    }

    $_SESSION['Message']['Success'] = $str_Success;
}
DIE;
header("Location: prc_trespasser_with_plate.php?PageTitle=". $PageTitle);

