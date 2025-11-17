<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include(CLS . "/cls_ws_mctc.php");



if($_POST) {


    $a_ZIPCity = array();
/*
    $rs_ZIPCity = $rs->SelectQuery("SELECT DISTINCT Title FROM ". MAIN_DB .".ZIPCity");

    while ($r_ZIPCity = mysqli_fetch_array($rs_ZIPCity)) {
        $a_ZIPCity[] = strtoupper($r_ZIPCity['Title']);
    }

*/

    $a_ChkModelBrand = array(
        "FIAT",
        "NISSAN",
        "FORD",
        "AUDI",
        "MITSUBISHI",
        "DACIA",
        "TOYOTA",
        "ALFA",
        "MINI",
        "HONDA",
        "MAZDA",
        "LANCIA",
    );



    $CityId = $_SESSION['cityid'];
    $UserName = $_SESSION['username'];

    $rs_MCTCLogin = $rs->Select('Customer',"CityId='".$CityId."'");
    $r_MCTCLogin = mysqli_fetch_array($rs_MCTCLogin);


    $CountryId = "Z000";

    $DataSourceId = 2;
    $DataSourceDate = date("Y-m-d");


    $Genre          = "";
    $a_Trespasser   = array();
    $a_Vehicle      = array();
    $a_Error        = array();


    $VehicleTypeId  = CheckValue('VehicleTypeId','n');
    $VehiclePlate   = CheckValue('VehiclePlate','s');
    $FineId         = CheckValue('FineId','n');



    $server = 'infoftp.dtt';

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

    $ws = new cls_ws_mctc();




    $ws->setLogin($r_MCTCLogin['MCTCUserName'],$r_MCTCLogin['MCTCPassword']);


    if($VehicleTypeId==2) {
        $str_Service = "WS_GET_MotorcycleTrespasser_With_Plate";
    } else if($VehicleTypeId==9){
        $str_Service = "WS_GET_MopedTrespasser_With_Plate";
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



    $a_response = Return_Array_GET_Trespasser_With_Plate($ws->soapConnect());


    if(isset($a_response['ErrorCode'])){
        $ResponseId = 0;
        $a_Error['ErrorCode'] = $a_response['ErrorCode'];
        $a_Error['ErrorDescription'] = $a_response['ErrorDescription'];

    } else {
        $ResponseId = 1;
        $a_Error['ErrorCode'] = "";
        $a_Error['ErrorDescription'] = "";
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
            $a_Trespasser['TaxCode']                                    = (isset($a_response['partitaIva'])) ? $a_response['partitaIva'] : "" ;
            
            $insertRequest[] = array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $a_Trespasser['CompanyName']);
        } else {
            $Genre = "M";
            $a_Trespasser['Name']                                       = (isset($a_response['nome'])) ? $a_response['nome'] : "" ;
            $a_Trespasser['Surname']                                    = (isset($a_response['cognome'])) ? $a_response['cognome'] : "" ;
            $a_Trespasser['BornDate']                                   = (isset($a_response['dataNascita'])) ? $a_response['dataNascita'] : "" ;
            if(strpos($a_Trespasser['BornDate'], '-') !== false) $a_Trespasser['BornDate'] = $a_Trespasser['BornDate'];
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
        if(strpos($a_Vehicle['VehicleLastRevisionDate'], '-') !== false) $a_Vehicle['VehicleLastRevisionDate'] = $a_Vehicle['VehicleLastRevisionDate'];
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








        if (in_array($a_Trespasser['City'], $a_ZIPCity)) {
            $a_FullStreet = explode(" ", $a_Trespasser['Address']  );

            $str_Where = "";

            for ($i = 1; $i < count($a_FullStreet) - 1; $i++) {
                $str_Where .= "AND StreetName LIKE '%" . str_replace(".", "", addslashes($a_FullStreet[$i])) . "%' ";
            }

            $rs_ZIP = $rs->SelectQuery("SELECT ZIP FROM ".MAIN_DB.".ZIPCity WHERE Title LIKE '%" . addslashes($a_Trespasser['City']) . "%' " . $str_Where);

            if (mysqli_num_rows($rs_ZIP) == 0) $a_Trespasser['ZIP'] = "";
            else if (mysqli_num_rows($rs_ZIP) == 1) {
                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                $a_Trespasser['ZIP'] = $r_ZIP['ZIP'];
            } else {
                $rs_ZIP = $rs->SelectQuery("SELECT ZIP FROM ".MAIN_DB.".ZIPCity WHERE Title LIKE '%" . addslashes($a_Trespasser['City']) . "%' " . $str_Where . " AND StreetName LIKE '%" . addslashes($a_FullStreet[0]) . "%'");
                if (mysqli_num_rows($rs_ZIP) == 1) {
                    $r_ZIP = mysqli_fetch_array($rs_ZIP);
                    $a_Trespasser['ZIP'] = $r_ZIP['ZIP'];
                } else $a_Trespasser['ZIP'] = "";
            }
        }
        else{
            $rs_CityProvince = $rs->Select(MAIN_DB.".V_CityProvince","Title LIKE '%".addslashes($a_Trespasser['City'])."%'");

            if(mysqli_num_rows($rs_CityProvince)>0)
            {
                $r_CityProvince = mysqli_fetch_array($rs_CityProvince);

                $a_Trespasser['ZIP'] = $r_CityProvince['ZIP'];
                $a_Trespasser['Province'] = $r_CityProvince['ProvinceShortTitle'];
            } else {
                $rs_CityProvince = $rs->Select(MAIN_DB.".V_CityProvince","Title LIKE '%".addslashes($a_Trespasser['Province'])."%'");
                $r_CityProvince = mysqli_fetch_array($rs_CityProvince);
                $a_Trespasser['ZIP'] = "";
                $a_Trespasser['Province'] = $r_CityProvince['ProvinceShortTitle'];

            }





        }




        $TresspasserTypeId = 1;

        $ZoneId = 1;
        $LanguageId = 1;
        $StatusTypeId = 10;


        $City = $a_Trespasser['City'];
        $Province = $a_Trespasser['Province'];

        $Address = $a_Trespasser['Address'];

        $ZIP = $a_Trespasser['ZIP'];





        $VehicleMass = $a_Vehicle['VehicleMass'] ;


        $VehicleLastRevision = $a_Vehicle['VehicleLastRevisionResult'];
        if (!checkIsAValidDate($VehicleLastRevision)) $VehicleLastRevision = '';



        $VehicleBrand = $a_Vehicle['VehicleBrand'];
        $VehicleModel = $a_Vehicle['VehicleModel'];




        $TaxCode = $a_Trespasser['TaxCode'];






        if ($Genre == 'D') {
            $CompanyName =  $a_Trespasser['CompanyName'];
// $a_Trespasser['CompanyType']
        } else {

            $Surname =  $a_Trespasser['Surname'];
            $Name =  $a_Trespasser['Name'];


            $BornDate = $a_Trespasser['BornDate'];
            if (!checkIsAValidDate($BornDate)) $BornDate = '';



            $BornPlace = $a_Trespasser['BornPlace'];



            $BornDateDay = substr($TaxCode, 9, 2);
            if ((int)$BornDateDay > 40) {
                $Genre = 'F';
            }



        }


        $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$CityId."'");
        $Code = mysqli_fetch_array($rs_Code)['Code'];

            if ($Genre == 'D') {
                $rs_Trespasser = $rs->Select('Trespasser', "CompanyName='" . addslashes($CompanyName) . "' AND City='" . addslashes($City) . "' AND CustomerId='" . $CityId . "'");
                $i_RowNumber = mysqli_num_rows($rs_Trespasser);

                if ($i_RowNumber == 0) {

                    $a_Insert = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                        array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                        array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
                        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $City),
                        array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $Province),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                    );
                    $TrespasserId = $rs->Insert('Trespasser', $a_Insert);


                } else {
                    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                    $TrespasserId = $r_Trespasser['Id'];

                    $a_Update = array(
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                    );

                    $rs->Update('Trespasser', $a_Update, "Id=" . $TrespasserId . " AND CustomerId='" . $CityId . "'");
                    addCustomIdCode($rs,$TrespasserId, $CityId);

                }

            } else {
                if (strlen(trim($TaxCode)) > 0) {
                    $rs_Trespasser = $rs->Select('Trespasser', "TaxCode='" . $TaxCode . "' AND CustomerId='" . $CityId . "'");
                    $i_RowNumber = mysqli_num_rows($rs_Trespasser);

                } else $i_RowNumber = 0;


                if ($i_RowNumber == 0) {

                    $a_Insert = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                        array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => $Surname),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Name),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                        array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $City),
                        array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $Province),
                        array('field' => 'BornDate', 'selector' => 'value', 'type' => 'date', 'value' => $BornDate),
                        array('field' => 'BornPlace', 'selector' => 'value', 'type' => 'str', 'value' => $BornPlace),
                        array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),


                    );



                    $TrespasserId = $rs->Insert('Trespasser', $a_Insert);

                } else {
                    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                    $TrespasserId = $r_Trespasser['Id'];
                    $a_Update = array(
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                    );

                    $rs->Update('Trespasser', $a_Update, "Id=" . $TrespasserId . " AND CustomerId='" . $CityId . "'");
                    addCustomIdCode($rs,$TrespasserId, $CityId);

                }

            }




            $a_Insert = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TresspasserTypeId, 'settype' => 'int'),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => "Inserimento da Import motorizzazione"),
            );
            $rs->Insert('FineTrespasser', $a_Insert);


            for ($i = 0; $i < count($a_ChkModelBrand); $i++) {
                if (strpos($VehicleBrand, $a_ChkModelBrand[$i]) !== false && strpos($VehicleModel, $a_ChkModelBrand[$i]) !== false) {
                    $VehicleModel = str_replace($a_ChkModelBrand[$i] . " ", "", $VehicleModel);
                }
            }


            $a_Update = array(
                array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand),
                array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel),
                array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'flt', 'value' => $VehicleMass, 'settype' => 'flt'),
                array('field' => 'VehicleLastRevision', 'selector' => 'value', 'type' => 'date', 'value' => $VehicleLastRevision),
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
            );

            $rs->Update('Fine', $a_Update, 'Id=' . $FineId);

            if ($VehicleMass > MASS) {
                $rs_rows = $rs->SelectQuery("
                                    SELECT F.FineTime, AT.ArticleId, AT.Fee, AT.MaxFee
                                           FROM Fine F JOIN FineArticle FA ON F.Id = FA.FineId
                                           JOIN ArticleTariff AT ON AT.ArticleId = FA.ArticleId
                                           WHERE AT.AdditionalMass = 1 AND FA.FineId=" . $FineId);


                while ($rs_row = mysqli_fetch_array($rs_rows)) {
                    $Fee = $rs_row['Fee'] * FINE_MASS;
                    $MaxFee = $rs_row['MaxFee'] * FINE_MASS;
                    $FineTime = $rs_row['FineTime'];

                    $aTime = explode(":", $FineTime);

                    if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                        //FINE_MINUTE_START_DAY
                        //FINE_MINUTE_END_DAY
                        $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                        $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
                    }


                    $a_Update = array(
                        array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                        array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                    );

                    $rs->Update('FineArticle', $a_Update, 'ArticleId=' . $rs_row['ArticleId'] . " AND FineId=" . $FineId);


                }
            }


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
