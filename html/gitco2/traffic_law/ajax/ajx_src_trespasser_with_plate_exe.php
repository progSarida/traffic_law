<?php
use traffic_law\cls\visureMCTC\Controlli;

include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC ."/function_visure.php");
require_once(CLS."/visureMCTC/Controlli.php");
require(INC."/initialization.php");
include(CLS . "/cls_ws_mctc.php");

if (!is_dir(NATIONAL_REQUEST_MCTC_WS)) {
    mkdir(NATIONAL_REQUEST_MCTC_WS, true);
    chmod(NATIONAL_REQUEST_MCTC_WS, 0750);
}

if($_POST) {
    $CityId = $_SESSION['cityid'];
    $UserName = $_SESSION['username'];

    $rs_MCTCLogin = $rs->Select('Customer',"CityId='".$CityId."'");
    $r_MCTCLogin = mysqli_fetch_array($rs_MCTCLogin);

    $Genre          = "";
    $a_Trespasser   = array();
    $a_Vehicle      = array();
    $a_Error        = array();

    $VehicleTypeId = CheckValue('VehicleTypeId','n');
    $VehiclePlate = CheckValue('VehiclePlate','s');
    //$FineDate = DateInDB(CheckValue('FineDate','s'));

    $b_ErrorSoap = false;
    $server = 'infoftp.dtt';
    
    $str_FileNameResponse = "Response_singular_" . date("Y-m-d_H-i");
    $str_FileNameRequest = "Request_singular_" . date("Y-m-d_H-i");

    $ws = new cls_ws_mctc();
    
    if(PRODUCTION){
        $conn = @ftp_connect($server);
        if (! $conn) {
            
            $output = shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
            sleep(3);
            $conn = @ftp_connect($server);
            if (! $conn) {
                $a_Error['ErrorCode'] = "0000";
                $a_Error['ErrorDescription'] = "VPN non attiva. Error:".$output;
                
                echo json_encode(
                    array(
                        "Genre" => $Genre,
                        "Trespasser" => $a_Trespasser,
                        "Vehicle"   => $a_Vehicle,
                        "Error" => $a_Error,
                    )
                    );
                DIE;
                
            }
        }
        
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
        
        /*
         ENTELLA
         MATRICOLA : CMGEP23501
         PSW: PLUNI*17
         CODICE ENTE: DMHZM
         */
        
        $ws->setService($str_Service);
        $ws->setParameters(array('numeroTarga'=>$VehiclePlate));
        
        $response = $ws->soapConnect();
        
        //Salva l'xml della richiesta
        file_put_contents(
            NATIONAL_REQUEST_MCTC_WS."/$str_FileNameRequest",
            "$VehicleTypeId---------------------------------".PHP_EOL.($ws->lastRequest ?: '').PHP_EOL,
            FILE_APPEND);
        
        //Salva l'xml della risposta
        file_put_contents(
            NATIONAL_REQUEST_MCTC_WS."/$str_FileNameResponse",
            "$VehicleTypeId---------------------------------".PHP_EOL.($ws->lastResponse ?: '').PHP_EOL,
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
        
        $xmlTest = file_get_contents(TESTVISURE_FOLDER.'/test_response_ws');
        
        if (!$xmlTest) {
            $a_Error['ErrorDescription'] = 'File prova di risposta non trovato';
            echo json_encode(
                array(
                    "Error" => $a_Error,
                )
                );
            DIE;
        }
        
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
        array('field' => 'rse_desc_errore','selector' => 'value','type' => 'str','value' => substr($a_Error['ErrorDescription'], 0, 200) ?: null, 'nullable' => true),
        array('field' => 'rse_cod_errore','selector' => 'value','type' => 'str','value' => $a_Error['ErrorCode'] ?? null, 'nullable' => true),
        array('field' => 'rse_esito','selector' => 'value','type' => 'str','value' => empty($a_Error) ? 'S' : 'N'),
    );
    
    $codRichiesta = $rs->insert("richieste_servizi_esterni", $insertRequest);

    if($ResponseId){
        $listaCittàCappate = array();
        $rsCittàCappate = $rs->SelectQuery("SELECT DISTINCT Title FROM sarida.ZIPCity");
        
        while ($vCittàCappata = mysqli_fetch_array($rsCittàCappate))
            $listaCittàCappate[] = strtoupper($vCittàCappata['Title']);
        
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

            $insertRequest[] = array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $a_Trespasser['CompanyName']);
        } else {
            $Genre = "M";
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
            }else{
                $a_Trespasser['BornPlace']                              = $a_response['descrizioneComune']. ' '.$a_response['siglaProvincia'];
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
        $a_Vehicle['VehicleBrand']                                      = (isset($a_response['denominazioneCommercialeVeicolo'])) ? $a_response['denominazioneCommercialeVeicolo'] : "" ;
        $a_Vehicle['origine']                                           = (isset($a_response['origine'])) ? $a_response['origine'] : "" ;
        $a_Vehicle['VehicleModel']                                      = (isset($a_response['modelloVeicolo'])) ? $a_response['modelloVeicolo'] : "" ;
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


/*
        if($FineDate >= $a_Vehicle['dataInizioProprieta'] ){
            echo "CI SIAMO";
        } else {
            echo "NON CI SIAMO";


            $str_Service = "WS_GET_CarTrespasserOwner_With_Date";


            $ws->setService($str_Service);
            $ws->setParameters(array('numeroTarga'=>$VehiclePlate,'situazioneAl'=>$FineDate));

            $a_response = $ws->soapConnect();

            print_r($a_response);
        }
*/
        $controlli = new Controlli($rs, $listaCittàCappate, $a_Trespasser['City']);
        
        $a_Trespasser['Address'] = controlli::normalizzaIndirizzo($a_Trespasser['Address']);
        $controlli->controllaLungIndirizzo($a_Trespasser['Address']);
        $controlli->controllaCittàResidenza();
        $a_Trespasser['ZIP'] = $controlli->controllaCap($a_Trespasser['Address']);
        $a_Trespasser['Province'] = $controlli->controllaProvincia($a_Trespasser['Province']);
        
    }

    echo json_encode(
        array(
            "Genre" => $Genre,
            "Trespasser" => $a_Trespasser,
            "Vehicle"   => $a_Vehicle,
            "Error" => $a_Error,
        )
    );

}

/*
<inf:listaMessaggi>
<inf:messaggio>
  <inf:testoMessaggio>PASSAGGIO DI PROPRIETA':  23/06/2016 CODICE: J2VLD4       DEL 23/06/2016</inf:testoMessaggio>
</inf:messaggio>
<inf:messaggio>
  <inf:testoMessaggio>IL VEICOLO RISULTA ASSICURATO.</inf:testoMessaggio>
</inf:messaggio>
</inf:listaMessaggi>
 * */
