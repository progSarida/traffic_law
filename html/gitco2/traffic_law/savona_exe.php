<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
require(CLS . "/cls_literal_number.php");
include(INC . "/function.php");
require(INC . "/initialization.php");

include_once TCPDF . "/tcpdf.php";

/*
IdDocumento             Int             Identificativo del documento inserito nel protocollo
AnnoProtocollo          Short           Anno del protocollo inserito
NumeroProtocollo        Int             Numero del protocollo inserito
DataProtocollo          Data            Data del protocollo inserito
Messaggio               Stringa         Eventuale messaggio di conferma o di errore
Registro                Complesso       Contiene gli estremi degli eventuali numeri di registri assegnati (max 50)
    TipoRegistro        Stringa         Eventuale tipo registro
    AnnoRegistro        Short           Eventuale anno di registro
    NumeroRegistro      short           Eventuale numero di registro

Errore                  Stringa         Eventuale messaggio di errore (*)
*/


ini_set('max_execution_time', 5000);


$str_Connection = "";
$str_Log = "";



$FineId=181789;



$rs_FineTrespasser = $rs->Select('V_FineTrespasser', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND FineId=" . $FineId);
$r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser);


$rs_FineDocumentation = $rs->Select('FineDocumentation', "DocumentationTypeId=2 AND FineId=" . $FineId);
$r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation);
$a_FileName = explode(".",$r_FineDocumentation['Documentation']);

$rs_IstatCode = $rs->SelectQuery("
            SELECT C.IstatCode
            FROM ".MAIN_DB.".City C JOIN ".MAIN_DB.".ZIPCity ZC ON C.Id = ZC.CityId
            WHERE ZC.ZIP='".$r_FineTrespasser['ZIP']."'");
if(mysqli_num_rows($rs_IstatCode)==0){
    $rs_IstatCode = $rs->Select(MAIN_DB.".City", "ZIP='".$r_FineTrespasser['ZIP']."'");
}

$r_IstatCode = mysqli_fetch_array($rs_IstatCode);
$IstatCode = $r_IstatCode['IstatCode'];

$str_FileFine           = NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $a_FileName[0].".pdf";
$str_FileNotification   = NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $a_FileName[0]."_N.pdf";


if ($fhandle = fopen($str_FileFine, "r")) {
    $obj_DataFileFine = fread($fhandle, filesize($str_FileFine));
    fclose($fhandle);

} else {

   echo "Problemi apertura ".$str_FileFine;
    DIE;
}
if ($fhandle = fopen($str_FileNotification, "r")) {
    $obj_DataFileNotification = fread($fhandle, filesize($str_FileNotification));
    fclose($fhandle);

} else {

    echo "Problemi apertura ".$str_FileNotification;
    DIE;
}
try {

    if ($r_FineTrespasser['Genre'] == "D") {
        $TrespasserName = $r_FineTrespasser['CompanyName'];
    } else {
        $TrespasserName = $r_FineTrespasser['Surname'] . " " . $r_FineTrespasser['Name'];
    }


    $month = date('m');
    //$client = new SoapClient("http://89.96.225.74:8088/ulisse/iride/web_services_20/WSProtocolloDM/WSProtocolloDM.asmx?wsdl",array('cache_wsdl'=>WSDL_CACHE_NONE));
    //http://89.96.225.72:8088/ulisse/iride/web_services_20/WSProtocolloDM/WSProtocolloDM.asmx?wsdl
    //$client = new SoapClient("http://srvdmz01.provincia.savona.it/wsiride.xml", array('cache_wsdl' => WSDL_CACHE_NONE));

    $client = new SoapClient("http://89.96.225.72:8088/ulisse/iride/web_services_20/WSProtocolloDM/WSProtocolloDM.asmx?wsdl", array('cache_wsdl' => WSDL_CACHE_NONE));



    $parametri = array(
        "Data" => "",
        "Classifica" => "002.012.005",
        "TipoDocumento" => "VBCDS",
        "Oggetto" => "VERBALE DI ACCERTAMENTO N. " . $r_FineTrespasser['ProtocolId'] . "/" .$r_FineTrespasser['ProtocolYear'] ." A CARICO DI " . htmlentities($TrespasserName) . " TARGA " . $r_FineTrespasser['VehiclePlate'],

        "Origine" => "P",
        "MittenteInterno" => "AVX",
        "MittentiDestinatari" => array(
            "MittenteDestinatario" => array(
                "CodiceFiscale" => $r_FineTrespasser['TaxCode'],
                "CognomeNome" => htmlentities($TrespasserName),
                "CodiceComune Residenza" => $IstatCode,
                "Mezzo" => "NT",
            ),
        ),
        "NumeroAllegati" => 2,
        "Allegati" => array(
            "Allegato" => array(
                "TipoFile" => "pdf",
                "Image" => $obj_DataFileFine,
            ),
            "Allegato" => array(
                "TipoFile" => "pdf",
                "Image" => $str_FileNotification,
            ),
        ),
        "AggiornaAnagrafiche" => "S",
        "InCaricoA" => "AVX",
        "AnnoPratica" => $_SESSION['year'],
        "NumeroPratica" => "0000" . $month,
        "Utente" => "AVELOX",
        "Ruolo" => "AVX",
    );

    $richiesta = array('ProtoIn' => $parametri);


    //sleep(1);

    $risposta = $client->InserisciDocumento($richiesta);

    $externalProtocol = $risposta->InserisciDocumentoEAnagraficheResult->NumeroProtocollo;
    $externalYear = $risposta->InserisciDocumentoEAnagraficheResult->AnnoProtocollo;
    $externalDate = explode('T', $risposta->InserisciDocumentoEAnagraficheResult->DataProtocollo);

    $str_Error = "";
    $str_Message = $risposta->InserisciDocumentoEAnagraficheResult->Messaggio;
    if (isset($risposta->InserisciDocumentoEAnagraficheResult->Errore)) {
        $str_Error = $risposta->InserisciDocumentoEAnagraficheResult->Errore;
    }
    /*
                                if(trim($str_Error)!=""){
                                    if ($externalProtocol > 0 && $externalYear > 0) {
                                        $rs->Start_Transaction();

                                        $aInsert = array(
                                            array('field' => 'Id', 'selector' => 'value', 'type' => 'int', 'value' => $externalProtocol, 'settype' => 'int'),
                                            array('field' => 'ProtocolMessage', 'selector' => 'value', 'type' => 'str', 'value' => $str_Message),
                                            array('field' => 'ProtocolError', 'selector' => 'value', 'type' => 'str', 'value' => $str_Error),
                                            array('field' => 'ProtocolSoap', 'selector' => 'value', 'type' => 'str', 'value' => var_dump($risposta)),
                                        );
                                        $rs->Insert('Protocol', $aInsert);

                                        $rs->End_Transaction();
                                    }


                                    echo "Errore nella protocollazione al WS: <br />";
                                    echo $str_Message. " <br />";
                                    echo $str_Error. " <br />";
                                    echo var_dump($risposta);


                                    $str_Log .= date("H:i:s").' Errore nella protocollazione al WS:'.$str_Message.' '.$str_Error.' '.var_dump($risposta).'.***';

                                    $aInsert = array(
                                        array('field' => 'LogMessage', 'selector' => 'value', 'type' => 'str', 'value' => $str_Log),
                                        array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                                    );
                                    $rs->Insert('LogError', $aInsert);

                                    $aUpdate = array(
                                        array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
                                        array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                                    );
                                    $rs->Update('LockedPage',$aUpdate, "Title='frm_protocol_fine'");


                                    DIE;
                                }
    */




    echo $externalProtocol. " ".$externalYear. " ".$externalDate;
    DIE;


    if ($externalProtocol > 0 && $externalYear > 0) {
        $rs->Start_Transaction();

        $aInsert = array(
            array('field' => 'Id', 'selector' => 'value', 'type' => 'int', 'value' => $externalProtocol, 'settype' => 'int'),
            array('field' => 'ProtocolMessage', 'selector' => 'value', 'type' => 'str', 'value' => $str_Message),
            array('field' => 'ProtocolError', 'selector' => 'value', 'type' => 'str', 'value' => $str_Error),
        );
        $rs->Insert('Protocol', $aInsert);


        $aUpdate = array(
            array('field' => 'ExternalProtocol', 'selector' => 'value', 'type' => 'int', 'value' => $externalProtocol, 'settype' => 'int'),
            array('field' => 'ExternalYear', 'selector' => 'value', 'type' => 'year', 'value' => $externalYear, 'settype' => 'year'),
            array('field' => 'ExternalDate', 'selector' => 'value', 'type' => 'date', 'value' => $externalDate[0], 'settype' => 'date'),
            array('field' => 'ExternalTime', 'selector' => 'value', 'type' => 'time', 'value' => $externalDate[1], 'settype' => 'time'),
        );

        $rs->Update('Fine', $aUpdate, 'Id=' . $fine['Id']);

        $aInsert = array(
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fine['Id'], 'settype' => 'int'),
            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $p7m),
            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 3),
        );
        $rs->Insert('FineDocumentation', $aInsert);


        $rs->End_Transaction();
    } else {
        $str_Connection .= '<br>Errore protocollo: Nr. prot. ' . $externalProtocol . ' del ' . $externalDate[0];

        $str_Log .= date("H:i:s") . ' Errore protocollo: Nr. prot. ' . $externalProtocol . ' del ' . $externalDate[0] . '.***';

        $aInsert = array(
            array('field' => 'LogMessage', 'selector' => 'value', 'type' => 'str', 'value' => $str_Log),
            array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
        );
        $rs->Insert('LogError', $aInsert);

        $aUpdate = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $aUpdate, "Title='frm_protocol_fine'");


        echo $str_Connection;
        DIE;
    }


} catch (SoapFault $exception) {



    var_dump($exception);

    DIE;


}
