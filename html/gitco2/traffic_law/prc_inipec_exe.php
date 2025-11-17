<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(INC . "/chilkat_9_5_0.php");
require_once(INC."/initialization.php");

/** @var $rs CLS_DB */
$rs->SetCharset("utf8");

if (!is_dir(LOG)){
    mkdir(LOG, 0700, true);
    chmod(LOG, 0700);
}
if (!is_dir(INIPEC_REQUEST)){
    mkdir(INIPEC_REQUEST, 0700, true);
    chmod(INIPEC_REQUEST, 0700);
}

function callInipecRequest($username, $password, $nomeFile, $tempDir, &$errore)
{
    global $rs;
    trigger_error("INIPEC: Richiesta inipec effettuata da $username con il file $nomeFile",E_USER_NOTICE);
    $idRichiesta = null;
    $glob=new CkGlobal();
    $success = $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);
    $http = new CkHttp();
    $http->put_SessionLogFilename(LOG."/servizioIniPec.log");
    $soapXml = new CkXml();
    $soapXml->put_EmitXmlDecl(false);
    $soapXml->put_Tag('ws:richiestaRichiestaFornituraPec');
    $soapXml->AddAttribute('xmlns:ws', 'http://ws.fpec.gemo.infocamere.it');
    $soapXml->NewChild2('elencoCf|nomeDocumento', $nomeFile);
    $soapXml->NewChild2('elencoCf|tipoDocumento', 'zip');
    $include=$soapXml->NewChild('elencoCf|documento|inc:Include', null);
    $include->AddAttribute('xmlns:inc', INIPEC_NS);
    $include->AddAttribute('href', $nomeFile.'.zip');
    $soapXml->GetRoot2();
    $attributes=array('xmlns:soap'=>'http://schemas.xmlsoap.org/soap/envelope/');
    $attributes['xmlns:ws']=INIPEC_NS;
    $soapXml = createSoapXml($soapXml,null,$attributes);
    $xmlBody = $soapXml->getXml();
    $req = new CkHttpRequest();
    $req->put_HttpVerb('POST');
    $req->put_Path('/fpec/ServizioFornituraPec');
    $req->put_ContentType("multipart/related; type='text/xml'");
    $req->AddHeader('SOAPAction', 'richiestaFornituraPec');
    $req->AddHeader('Authorization', "Basic ".base64_encode("{$username}:{$password}"));
    $req->AddStringForUpload2('', '', $xmlBody, 'utf-8', 'application/xop+xml; type=\'text/xml\'; charset=utf-8');
    $bd = new CkBinData();
    $success = $bd->LoadFile($tempDir . "/" .$nomeFile . '.zip');
    $req->AddBdForUpload($tempDir . "/" .$nomeFile . '.zip', $tempDir .  "/" .$nomeFile . '.zip',$bd,'application/zip; name='.$nomeFile . '.zip"');
    $req->AddSubHeader(1,'Content-ID','<'.$nomeFile . '.zip>');
    $req->AddSubHeader(1,'Content-Transfer-Encoding','Binary');
    $req->AddSubHeader(1,'Content-Disposition','attachment  name="'.$nomeFile . '.zip"; filename="'.$nomeFile . '.zip"');
    $xmlResponse = new CkXml();
    $domain=splitUrl(INIPEC_URL)['domain'];
    
    $resp = $http->SynchronousRequest($domain, 443, true, $req);
    
    if ($http->get_LastMethodSuccess() != true) {
        $errore = "Errore nell'invio della richiesta";
        trigger_error('ERRORE INIPEC: '.$http->lastErrorText(),E_USER_WARNING);
    } else {
        if($xmlResponse->LoadXml($resp->bodyStr())){
            if(!empty($xmlResponse->ChilkatPath('/T/faultstring|*'))){
                $errore = $xmlResponse->ChilkatPath('/T/faultstring|*');
            } else {
                if($xmlResponse->ChilkatPath('/T/esito|esito|*') == 'true'){
                    $idRichiesta = $xmlResponse->ChilkatPath('/T/idRichiesta|*');
                } else {
                    $errore = $xmlResponse->ChilkatPath('/T/descrizioneEsito|*');
                }
            }
        } else {
            $errore = "Errore imprevisto";
            trigger_error('ERRORE INIPEC: impossibile caricare xml da risposta: '.$http->bodyStr(),E_USER_WARNING);
        }
    }

    return $idRichiesta;
}

$errore = '';
$username_pw_rs = $rs->SelectQuery("SELECT UserName, Password, INIPECPasswordExpiration from IniPecProcessing where CityId='{$_SESSION['cityid']}'");
$username_pw = mysqli_fetch_array($username_pw_rs);
$passwordExpiration=$username_pw['INIPECPasswordExpiration'];
//se non è indicata la scadenza si presume che la parola chiave non scada
if($passwordExpiration!=null && strtotime(date('Y-m-d'))>strtotime($passwordExpiration)) {
    $_SESSION['Message']['Error'] = 'Password scaduta, modificarla presso http://telemaco.infocamere.it.';
    header("location: prc_inipec.php");
    die;
}

$TipoPersona = '';
if (isset($_POST['TipoPersona'])){
    $TipoPersona = $_POST['TipoPersona'];
    $username = $username_pw["UserName"];
    $password = $username_pw["Password"];
    $fileText = '';
    $nomeFile = $username . date('Ymdhis', time());
    $cfToCity=array();
    $cfRichiedibili=0;
    foreach ($_POST['checkbox'] as $CityId) {
        $queryString = "SELECT distinct t.Id as Id, t.Genre as genre, t.VatCode as vatCode, t.TaxCode as taxCode, f.CityId as CityID
	FROM Fine f JOIN
	FineTrespasser ft on (f.Id=ft.FineId) join
	Trespasser t ON (ft.TrespasserId =t.Id) join Customer c on (c.CityId=f.CityId)
	where f.ProtocolYear ='{$_SESSION['year']}' and
	f.StatusTypeId  in (10,14) and(
	ft.TrespasserTypeId in (1,11)or
	( ft.TrespasserTypeId  in(2,3,15,16) and
    ft.FineCreateDate is null)) and f.CityId='{$CityId}' 
 and (InipecLoaded is null or datediff('".date('Y-m-d')."',t.InipecLoaded)>7)";
        if ($TipoPersona == 'f')
            $queryString .= " and not(genre='D')";
        else if ($TipoPersona == 'd')
            $queryString .= " and genre='D'";

        $table_rows = $rs->SelectQuery($queryString);
        while ($table_row = mysqli_fetch_array($table_rows)) {
            $nomeDocumento = null;
            $vatCode = $table_row['vatCode'];
            $taxCode = $table_row['taxCode'];
            $genre = $table_row['genre'];
            if ($taxCode != null || $vatCode != null)
                $nomeDocumento = PickVatORTaxCode($genre, $vatCode, $taxCode);
            //echo "<br> $nomeDocumento contiene: " . $nomeDocumento;
            $nomeDocumento = trim($nomeDocumento);
            if (!empty($nomeDocumento)){
                if(!isset($cfToCity[$table_row['CityID']])) $cfToCity[$table_row['CityID']] = array();
                
                if(!in_array($nomeDocumento,$cfToCity[$table_row['CityID']])){
                    $cfToCity[$table_row['CityID']][] = $nomeDocumento;
                    $cfRichiedibili++;
                    $fileText .= $nomeDocumento . "\n";
                }
                    
            }
        }
    }
    if($cfRichiedibili<=0){
        $_SESSION['Message']['Warning'] = "Nessun trasgressore valido trovato per la richiesta a IniPec. Contattare l' assistenza per la verifica dei dati.";
    } else {
        $myfile = fopen(INIPEC_REQUEST . "/" .$nomeFile . ".txt", "w");
        fwrite($myfile, $fileText);
        fclose($myfile);
        $zip = new ZipArchive();
        if ($zip->open(INIPEC_REQUEST . "/" . $nomeFile . '.zip', ZipArchive::CREATE)) {
            $zip->addFile(INIPEC_REQUEST .  "/" .$nomeFile . ".txt", $nomeFile . ".txt");
            $zip->close();
            
            $idRichiesta=callInipecRequest($username, $password, $nomeFile, INIPEC_REQUEST, $errore);
            
            $dataRichiesta = date('Y-m-d');
            $oraRichiesta = date('H:i:s');
            
            foreach ($cfToCity as $cfCityId => $a_cf){
                $insertRequest = array(
                    array('field' => 'rse_ente','selector' => 'value','type' => 'str','value' => $cfCityId),
                    array('field' => 'rse_utente_servizio','selector' => 'value','type' => 'str','value' => $username),
                    array('field' => 'rse_ute_richiesta','selector' => 'value','type' => 'str','value' => $_SESSION['username']),
                    array('field' => 'rse_data_richiesta','selector' => 'value','type' => 'date','value' => $dataRichiesta),
                    array('field' => 'rse_ora_richiesta','selector' => 'value','type' => 'str','value' => $oraRichiesta),
                    array('field' => 'rse_tipo','selector' => 'value','type' => 'int','value' => 4, 'settype' => 'int'),
                    array('field' => 'rse_id_richiesta','selector' => 'value','type' => 'str','value' => $idRichiesta ?: null, 'settype' => 'str', 'nullable' => true),
                    array('field' => 'rse_desc_errore','selector' => 'value','type' => 'str','value' => substr($errore, 0, 200) ?: null, 'nullable' => true),
                    array('field' => 'rse_esito','selector' => 'value','type' => 'str','value' => $errore ? 'N' : null, 'nullable' => true),
                );
                
                $codRichiesta = $rs->insert("richieste_servizi_esterni", $insertRequest);
                
                if($idRichiesta){
                    trigger_error("INIPEC: Richiesta inipec effettuata, idRichiesta: $idRichiesta",E_USER_NOTICE);
                    $progressivo = 1;
                    
                    foreach ($a_cf as $cf){
                        
                        $insertRequest = array(
                            array('field' => 'der_cod_richiesta','selector' => 'value','type' => 'str','value' => $codRichiesta, 'settype' => 'str'),
                            array('field' => 'der_progressivo','selector' => 'value','type' => 'int','value' => $progressivo, 'settype' => 'int'),
                            array('field' => 'der_oggetto','selector' => 'value','type' => 'str','value' => $cf),
                        );
                        $rs->insert("dettaglio_richieste_servizi_est", $insertRequest);
                            
                        $updateTrespasser=array(
                            array('field' => 'InipecLoaded','selector' => 'value','type' => 'str','value'=>$dataRichiesta));
                        $rs->Update("Trespasser",$updateTrespasser,"TaxCode='$cf' or VatCode='$cf'");
                            
                        $progressivo++;
                    }
                }
            }
            unlink(INIPEC_REQUEST .  "/" .$nomeFile . '.zip');
            unlink(INIPEC_REQUEST .  "/" .$nomeFile . '.txt');
        }
        
        if ($errore){
            $_SESSION['Message']['Error'] = "Impossibile effettuare la richiesta: $errore";
        } else {
            $_SESSION['Message']['Success'] = "Richiesta effettuata con successo";
        }
    }
}
header("location: prc_inipec.php");
?>
