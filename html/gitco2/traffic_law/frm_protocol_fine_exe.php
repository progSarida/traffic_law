<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require(CLS."/cls_literal_number.php");
include(INC."/function.php");
require(INC."/initialization.php");

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

$P = "frm_protocol_fine.php";
$str_Connection = "";
$str_Log = "";


if(isset($_POST['checkbox'])) {



    $rs_Locked = $rs->Select('LockedPage', "Title='frm_protocol_fine'");
    $r_Locked = mysqli_fetch_array($rs_Locked);

    if($r_Locked['Locked']==1){
        $_SESSION['Message'] = "Errori presenti nel log. Impossibile protocollare al momento.";

        header("location: ".$P);
        DIE;
    }


    $str_Log .= " ------ UTENTE ".$_SESSION['userid']. " ------ ";



    $ftp_connection = false;
    $chk_inp_file = false;
    $server = '89.96.225.74';
    $username = 'velox';
    $password = 'Cd28+PeB';
    $folderName = $_SESSION['username'];

    $conn = @ftp_connect($server);
    if ($conn) {
        $login = @ftp_login($conn, $username, $password);
        if ($login) {
            $ftp_connection = true;
            $str_Connection = 'Connessione riuscita';
            $str_Log .= date("H:i:s").' Connessione riuscita***';


            $path = "/";
            //                    $contents_on_server = ftp_nlist($conn, $path);
            //                    print_r($contents_on_server);
            $path .= $folderName;
            $origin = ftp_pwd($conn);
            // Controllo se esiste la cartella username
            if (@ftp_chdir($conn, $folderName)) {
                $str_Connection.= '<br>Login riuscita';
                $str_Log .= date("H:i:s").' Login riuscita***';
                // Se esiste torno alla cartella originale
                ftp_chdir($conn, $origin);

                $cont = 0;
                foreach ($_POST['checkbox'] as $key=>$FineId) {

                    $rs_Locked = $rs->Select('LockedPage', "Title='frm_protocol_fine'");
                    $r_Locked = mysqli_fetch_array($rs_Locked);

                    if($r_Locked['Locked']==1){
                        $_SESSION['Message'] = "Errori presenti nel log. Impossibile protocollare al momento.";

                        header("location: ".$P);
                        DIE;
                    }


                    $fines = $rs->SelectQuery("SELECT * FROM V_Fine WHERE Id=".$FineId);
                    $fine = mysqli_fetch_array($fines);

                    $trespassers = $rs->Select('V_Trespasser', "Id=".$fine['TrespasserId']);
                    $trespasser = mysqli_fetch_array($trespassers);

                    if($_POST['pdf'][$key]!=""){
                        $pdf = $_POST['pdf'][$key];

                        $rs_row = $rs->Select('DocumentationProtocol',"UserId=".$_SESSION['userid']." AND CityId='".$_SESSION['cityid']."' AND Documentation='".$pdf."'");
                        $n_row =  mysqli_num_rows($rs_row);
                        if($n_row!=1){
                            $str_Connection .= "Errore nella protocollazione del file. File " . $pdf. " non presente o già stato protocollato.";
                            $str_Log .= date("H:i:s").' Errore nella protocollazione del file. File ' . $pdf. ' non presente o già stato protocollato.***';

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



                            echo $str_Connection;
                            DIE;
                        }

                    }

                    else{
                        $str_Connection.= '<br>File .pdf del verbale '.$fine['Code'].' non trovato';
                        $_SESSION['Message'] = $str_Connection;
                        header("location: ".$P);
                    }
                        

                    if($_POST['p7m'][$key]!="")
                        $p7m = $_POST['p7m'][$key];
                    else{
                        $str_Connection.= '<br>File .p7m del verbale '.$fine['Code'].' non trovato';
                        $_SESSION['Message'] = $str_Connection;
                        header("location: ".$P);
                    }


                    $rs_p7m = $rs->Select('FineDocumentation',"FineId=".$FineId." AND DocumentationTypeId=3");
                    $RowNumber = mysqli_num_rows($rs_p7m);

                    if ($RowNumber >0) {
                        $str_Connection .= "Errore nell'import file. Verbale con id " . $FineId. " ha già un verbale protocollato assegnato.";

                        $str_Log .= date("H:i:s").' Errore nell\'import file. Verbale con id ' . $FineId. ' ha già un verbale protocollato assegnato.***';

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



                        echo $str_Connection;
                        DIE;
                    }


                    $rs_p7m = $rs->Select('FineDocumentation',"Documentation='".$p7m."'");
                    $RowNumber = mysqli_num_rows($rs_p7m);

                    if ($RowNumber >0) {
                        $str_Connection .= "Errore nell'import file. File " . $p7m. " già protocollato.";

                        $str_Log .= date("H:i:s").' Errore nell\'import file. File ' . $p7m. ' già protocollato.***';

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


                        echo $str_Connection;
                        DIE;
                    }

                    $download = @ftp_get($conn, NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $p7m, $path."/".$p7m, FTP_BINARY);
                    sleep(3);
                    if ($download) {

                        $fname = NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $p7m;

                        if ($fhandle = fopen($fname, "r")) {
                            $dati = fread($fhandle, filesize($fname));
                            fclose($fhandle);

                        } else {

                            $str_Connection .= "<br>Connessione WS riuscita ma il file " . $fname . " non è apribile";

                            $str_Log .= date("H:i:s").' Connessione WS riuscita ma il file ' . $fname. ' non è apribile.***';

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



                            echo $str_Connection;
                            DIE;
                        }

                        try {
                            if($trespasser['Genre']=="D"){
                                $TrespasserName = $trespasser['CompanyName'];
                            }
                            else{
                                $TrespasserName = $trespasser['Surname'] . " " . $trespasser['Name'];
                            }

                            $month = date('m');
                            //$client = new SoapClient("http://89.96.225.74:8088/ulisse/iride/web_services_20/WSProtocolloDM/WSProtocolloDM.asmx?wsdl",array('cache_wsdl'=>WSDL_CACHE_NONE));
                            //http://89.96.225.72:8088/ulisse/iride/web_services_20/WSProtocolloDM/WSProtocolloDM.asmx?wsdl
                            $client = new SoapClient("http://srvdmz01.provincia.savona.it/wsiride.xml", array('cache_wsdl' => WSDL_CACHE_NONE));
                            $parametri = array(
                                "Data" => "",
                                "Classifica" => "002.012.005",
                                "TipoDocumento" => "VBCDS",
                                "Oggetto" => "VERBALE DI ACCERTAMENTO N. " . $fine['ProtocolId'] . "/" .$fine['ProtocolYear'] ." A CARICO DI " . htmlentities($TrespasserName) . " TARGA " . $fine['VehiclePlate'],
                                "Origine" => "P",
                                "MittenteInterno" => "AVX",
                                "MittentiDestinatari" => array(
                                    "MittenteDestinatario" => array(
                                        "CodiceFiscale" => "01338160995",
                                        "CognomeNome" => "",
                                        "Mezzo" => "NT",
                                    ),
                                ),
                                "NumeroAllegati" => 1,
                                "Allegati" => array(
                                    "Allegato" => array(
                                        "TipoFile" => "pdf.p7m",
                                        "Image" => $dati,
                                    ),
                                ),
                                "AggiornaAnagrafiche" => "N",
                                "InCaricoA" => "AVX",
                                "AnnoPratica" => $_SESSION['year'],
                                "NumeroPratica" => "0000" . $month,
                                "Utente" => "AVELOX",
                                "Ruolo" => "AVX",
                            );

                            $richiesta = array('ProtoIn' => $parametri);


                            //sleep(1);

                            $risposta = $client->InserisciProtocollo($richiesta);

                            $externalProtocol = $risposta->InserisciProtocolloEAnagraficheResult->NumeroProtocollo;
                            $externalYear = $risposta->InserisciProtocolloEAnagraficheResult->AnnoProtocollo;
                            $externalDate = explode('T', $risposta->InserisciProtocolloEAnagraficheResult->DataProtocollo);

                            $str_Error = "";
                            $str_Message = $risposta->InserisciProtocolloEAnagraficheResult->Messaggio;
                            if(isset($risposta->InserisciProtocolloEAnagraficheResult->Errore)){
                                $str_Error = $risposta->InserisciProtocolloEAnagraficheResult->Errore;
                            }

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

                                

                                    
                                $deletePdf = @ftp_delete($conn, $path . "/" . $pdf);
                                if (!$deletePdf) {
                                    $str_Connection.= '<br>Cancellazione file '.$pdf.' non riuscita ma file protocollato';


                                    $str_Log .= date("H:i:s").' Cancellazione file '.$pdf.' non riuscita ma file protocollato.***';

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


                                    $rs->End_Transaction();
                                    echo $str_Connection;
                                    DIE;

                                }
                                    
                                $deleteP7m = @ftp_delete($conn, $path . "/" . $p7m);
                                if (!$deleteP7m) {
                                    $str_Connection.= '<br>Cancellazione file '.$p7m.' non riuscita ma file protocollato';

                                    $str_Log .= date("H:i:s").' Cancellazione file '.$p7m.' non riuscita ma file protocollato.***';

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

                                    $rs->End_Transaction();
                                    echo $str_Connection;
                                    DIE;

                                }

                                $rs->Delete("DocumentationProtocol","UserId=".$_SESSION['userid']." AND CityId='".$_SESSION['cityid']."' AND Documentation='".$pdf."'");




                                $cont++;
                                $rs->End_Transaction();
                            } else {
                                $str_Connection.= '<br>Errore protocollo: Nr. prot. '.$externalProtocol.' del '.$externalDate[0];

                                $str_Log .= date("H:i:s").' Errore protocollo: Nr. prot. '.$externalProtocol.' del '.$externalDate[0].'.***';

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


                                echo $str_Connection;
                                DIE;
                            }


                        } catch (SoapFault $exception) {


                            print($exception->faultcode . "-" . $exception->faultstring) . "<br>/";
                            //print_r($exception);
                            $str_Connection.= '<br>Problemi con la chiamata AL WS';

                            $str_Log .= date("H:i:s").' Errore protocollo: Nr. prot. '.$externalProtocol.' del '.$externalDate[0].'.***';

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



                            echo $str_Connection;
                            DIE;
                            
                            
                        }
                    }
                    else{
                        $str_Connection.= '<br>Dowload non riuscito';

                        $str_Log .= date("H:i:s").' Dowload non riuscito.***';

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



                        echo $str_Connection;
                        DIE;
                    }

                }
            } else {
                // La cartella non esiste
                $str_Connection .= "<br>Utente non abilitato alla firma o cartella inesistente!";


                $str_Log .= date("H:i:s").' Utente non abilitato alla firma o cartella inesistente.***';

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


                $_SESSION['Message'] = $str_Connection;
                header("location: ".$P);
            }
        } else {
            $str_Connection = 'Login fallita';

            $str_Log .= date("H:i:s").' Login fallita.***';

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

            $_SESSION['Message'] = $str_Connection;
            header("location: ".$P);
        }
    } else {
        $str_Connection = 'Connessione fallita';

        $str_Log .= date("H:i:s").' Connessione fallita.***';

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


        $_SESSION['Message'] = $str_Connection;
        header("location: ".$P);
    }
}
if(count($_POST['checkbox'])==0){
    $str_Connection.= "Nessun verbale da protocollare!";
}
else{
    $str_Connection.= '<br>Sono stati protocollati '.$cont.' verbali su '.count($_POST['checkbox']);
}

$_SESSION['Message'] = $str_Connection;
header("location: ".$P);
