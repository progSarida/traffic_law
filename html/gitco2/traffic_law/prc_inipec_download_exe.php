<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include (INC . "/chilkat_9_5_0.php");
require_once(INC."/initialization.php");

/** @var $rs CLS_DB */
$rs->SetCharset("utf8");

if (!is_dir(LOG)){
    mkdir(LOG, 0700, true);
    chmod(LOG, 0700);
}
if ( !is_dir( INIPEC_RESPONSE ) ) {
    mkdir( INIPEC_RESPONSE, 0700, true);
    chmod(INIPEC_RESPONSE, 0700);
}

define("INIPEC_COD_FORNITURA_LAV", "OK");
define("INIPEC_COD_FORNITURA_SCAD", "EC1");
define("INIPEC_COD_FORNITURA_IN_LAV", "EB9");

function callInipecDownload($username, $password, $idRichiesta, &$avviso){
    global $rs;
    trigger_error("INIPEC: richiesta recupero fornitura inipec effettuata da $username per la richiesta $idRichiesta",E_USER_NOTICE);
    $glob=new CkGlobal();
    $csv = array();
    $success = $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);
    $http = new CkHttp();
    $http->put_SessionLogFilename(LOG."/servizioIniPec.log");
    $soapXml = new CkXml();
    $soapXml->put_EmitXmlDecl(false);
    $soapXml->put_Tag('ws:richiestaScaricoFornituraPec');
    $soapXml->AddAttribute('xmlns:ws', 'http://ws.fpec.gemo.infocamere.it');
    $soapXml->NewChild2('tokenRichiestaInfocamere|tipoRichiesta', 'FORNITURA_FPEC');
    $soapXml->NewChild2('tokenRichiestaInfocamere|idRichiesta',str_pad($idRichiesta,10,"0",STR_PAD_LEFT));
    $soapXml->GetRoot2();
    $attributes=array('xmlns:soap'=>'http://schemas.xmlsoap.org/soap/envelope/');
    $attributes['xmlns:ws']='http://ws.fpec.gemo.infocamere.it';
    $soapXml = createSoapXml($soapXml,null,$attributes);
    $xmlBody = $soapXml->getXml();
    $req = new CkHttpRequest();
    $req->put_HttpVerb('POST');
    $req->put_Path('/fpec/ServizioFornituraPec');
    $req->AddHeader('SOAPAction', 'scaricoFornituraPec');
    $req->AddHeader('Authorization', "Basic ".base64_encode("{$username}:{$password}"));
    $req->LoadBodyFromString($xmlBody,"utf-8");
    $xmlResponse = new CkXml();
    $domain=splitUrl(INIPEC_URL)['domain'];
    $resp = $http->SynchronousRequest($domain, 443, true, $req);
    
    $updateRequest = array();
    $errore = null;
    
    if ($http->get_LastMethodSuccess()) {
        if($xmlResponse->LoadXml($resp->bodyStr())){
            if(!empty($xmlResponse->ChilkatPath('/T/faultstring|*'))){
                $avviso = $xmlResponse->ChilkatPath('/T/faultstring|*');
            } else {
                switch($xmlResponse->ChilkatPath('/T/codiceErrore|*')){
                    case INIPEC_COD_FORNITURA_LAV: {
                        $mime = new CkMime();
                        $respBody = new CkBinData();
                        $resp->GetBodyBd($respBody);
                        $mime->LoadMimeBd($respBody);
                        if($mime->get_LastMethodSuccess()){
                            $part1 = $mime->GetPart(1);
                            $zipData = new CkBinData();
                            if($part1){
                                $part1->GetBodyBd($zipData);
                                $zipData->WriteFile(INIPEC_RESPONSE."/response_{$username}_{$idRichiesta}.zip");
                                $zip = new ZipArchive();
                                if ($zip->open(INIPEC_RESPONSE."/response_{$username}_{$idRichiesta}.zip", ZipArchive::CREATE)) {
                                    $updateRequest[] = array('field' => 'rse_esito','selector' => 'value','type' => 'str','value' => 'S');
                                    $data = str_replace('"','',$zip->getFromIndex(0));
                                    $zip->close();
                                    $rows=explode(PHP_EOL,$data);
                                    foreach($rows as $row){
                                        array_push($csv,explode("~",$row));
                                    }
                                } else $errore = "Errore nell'apertura del file zip: response_{$username}_{$idRichiesta}.zip";
                            } else $errore = "Errore nella lettura del contenuto della risposta";
                        } else $errore = $mime->lastErrorText();
                        break;
                    }
                    case INIPEC_COD_FORNITURA_IN_LAV: {
                        $avviso = $xmlResponse->ChilkatPath('/T/descrizioneErrore|*');
                    }
                    case INIPEC_COD_FORNITURA_SCAD: {
                        $avviso = $xmlResponse->ChilkatPath('/T/descrizioneErrore|*');
                        $updateRequest[] = array('field' => 'rse_esito','selector' => 'value','type' => 'str','value' => 'S');
                        $updateRequest[] = array('field' => 'rse_desc_errore','selector' => 'value','type' => 'str','value' => $xmlResponse->ChilkatPath('/T/descrizioneErrore|*'));
                        $updateRequest[] = array('field' => 'rse_cod_errore','selector' => 'value','type' => 'str','value' => $xmlResponse->ChilkatPath('/T/codiceErrore|*'));
                        break;
                    }
                    
                    default: {
                        //TODO ci sono diversi codici di errore, bisogna capire quali sono definitivi, ovvero che la fornitura non è ottenibile e quali no
                        //Per ora viene restituito errore a video e non si fa nulla
                        $avviso = $xmlResponse->ChilkatPath('/T/descrizioneErrore|*');
                    }
                }
            }
        } else $errore = "Impossibile caricare xml da risposta: ".$http->bodyStr();
    } else $errore = $http->lastErrorText();
    
    if($errore){
        trigger_error("ERRORE INIPEC: $errore",E_USER_WARNING);
        $_SESSION['Message']['Error'] = "Problemi nel recupero della fornitura: Errore imprevisto.";
        header("location: prc_inipec.php");
        DIE;
    } else {
        if(!empty($updateRequest)){
            $updateRequest[] = array('field' => 'rse_ute_risposta','selector' => 'value','type' => 'str','value' => $_SESSION['username']);
            $rs->Update("richieste_servizi_esterni", $updateRequest, "rse_tipo=4 AND rse_id_richiesta='$idRichiesta'");
        }
    }
    
    return $csv;
}

$idRichiesta = CheckValue("IdRichiesta", 's');

if($idRichiesta){
    $avviso = '';
    $username_pw_rs=$rs->SelectQuery("SELECT UserName,Password, INIPECPasswordExpiration from IniPecProcessing where CityId='{$_SESSION['cityid']}'");
    $username_pw=mysqli_fetch_array($username_pw_rs);
    $passwordExpiration=$username_pw['INIPECPasswordExpiration'];
    
    //se non è indicata la scadenza si presume che la parola chiave non scada
    if($passwordExpiration!=null && strtotime(date('Y-m-d'))>strtotime($passwordExpiration)) {
        $_SESSION['Message']['Error'] = "Password scaduta, modificarla presso http://telemaco.infocamere.it.";
        header("location: prc_inipec.php");
        die;
    }
    $username=$username_pw["UserName"];
    $password=$username_pw["Password"];
    //$table_rows=$rs->SelectQuery("select * from IniPecRequest where EsitoRichiesta='true' and EsitoFornitura is null and UserName='{$username}' ");
    
    $richieste = $rs->getResults($rs->Select("richieste_servizi_esterni", "rse_tipo=4 AND rse_id_richiesta='$idRichiesta' AND rse_esito IS NULL"));
    $codiciRichieste = array_column($richieste, 'rse_codice', 'Id');
    
    if(!empty($codiciRichieste)){
        $csv=callInipecDownload($username,$password,$idRichiesta, $avviso);
        foreach($csv as $row){
            //echo "<br>trova pec <br>";
            //print_r($row);
            if(!isset($row[2])) break;
            
            if($row[8]=='OK'){
                $pec=$row[7];
            }else if($row[18]=='OK'){
                $pec=$row[17];
            } else $pec=null;
            
            $codiceFiscale=str_replace('"', '',$row[2]);
            $pec=str_replace('"', '',$pec);
            if (!empty($pec) && !empty($codiceFiscale)){
                $rs->Start_Transaction();
                $rs_trespasser=$rs->Select("Trespasser","VatCode='$codiceFiscale' or TaxCode='$codiceFiscale'");
                while ($r_trespasser = mysqli_fetch_array($rs_trespasser)) {
                    if($r_trespasser['PEC']!=$pec){
                        $rs->ExecuteQuery("insert into TrespasserHistory (TrespasserId,Genre,CompanyName,Surname,Name,
                            Address,StreetNumber, Ladder, Indoor, Plan,ZIP, City, Province, CountryId, BornPlace,
                            BornCountryId,BornDate, TaxCode,ForcedTaxCode,VatCode,Phone,Phone2, Fax, Notes, PEC, UserId,
                            VersionDate, Mail, ZoneId,LanguageId,DeathDate,LandId)
                            select Id,Genre,CompanyName,Surname,Name,
                            Address,StreetNumber, Ladder, Indoor, Plan,ZIP, City, Province, CountryId, BornPlace,
                            BornCountryId,BornDate, TaxCode,ForcedTaxCode,VatCode,Phone,Phone2, Fax, Notes, PEC, '".
                                        $_SESSION['username']."', '".DateInDB(date("d/m/Y")).
                                        "', Mail, ZoneId,LanguageId,DeathDate,LandId from Trespasser
                            where Id={$r_trespasser['Id']}");
                                    
                        $updateTrespasser=array(
                            array('field' => 'PEC', 'selector' => 'value','type' => 'str', 'value'=>$pec)
                        );
                        
                        $rs->Update("Trespasser",$updateTrespasser,"Id={$r_trespasser['Id']}");
                    }
                }
                $updateRequestPec = array(
                    array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $pec),
                );
                $rs->Update("dettaglio_richieste_servizi_est", $updateRequestPec, "der_cod_richiesta IN(".implode(',',$codiciRichieste).") AND der_oggetto ='$codiceFiscale'");
                
                $rs->End_Transaction();
            }
        }
    }
}

if ($avviso){
    $_SESSION['Message']['Warning'] = "Problemi nel recupero della fornitura: $avviso";
} else {
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

header("location: prc_inipec.php");
