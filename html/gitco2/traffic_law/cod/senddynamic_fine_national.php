<?php
require_once(CLS . '/cls_iuv.php');
include(INC."/function_postalCharge.php");

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".$P);
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$str_Success = $str_Warning = '';

$n_LanguageId = 1;
$a_Fine = array();
$a_ContractFine = array();
//Contiene i FineId di cui non è stato possibile generare/aggiornare PagoPA
$a_FailedPagoPA = array();
//Determina quanti sono stati processati con successo
$n_Successful = 0;

$ZoneId=0;

$PrintTypeId = ($RegularPostalFine) ? 4 : 1;
$DocumentTypeId = 1;
$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");

$StatusTypeId = ($RegularPostalFine) ? 9 : 20;

$NotificationTypeId = 6;

$a_Lan = unserialize(LANGUAGE);

$ultimate = CheckValue('ultimate','s');

$a_PrinterConfigs = unserialize(PRINTER_FTP_CONFIG);
$a_PrinterConf = $a_PrinterConfigs[$PrinterId] ?? null;

//Se viene selezionato uno stampatore per cui è previsto l'invio del flusso tramite FTP, tenta la connessione
if($a_PrinterConf && $ultimate && PRODUCTION){
    $phpFTP = PhpFTPFactory::create(
        $a_PrinterConf['Type'],
        $a_PrinterConf['Host'],
        $a_PrinterConf['Username'],
        $a_PrinterConf['Password'],
        $a_PrinterConf['Port']);
    if(!$phpFTP->connect()){
        $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
        header("location: ".$P);
        DIE;
    } else {
        $phpFTP->disconnect();
    }
}

//SERVIZIO PAGOPA ENTE
//se è abilitato pagoPA interroghiamo la base dati per prendere le configurazioni
if($r_Customer['PagoPAPayment']==1){
    $pagopaServicequery=$rs->Select("PagoPAService","Id={$r_Customer['PagoPAService']}");
    $pagopaService=mysqli_fetch_array($pagopaServicequery);
    $pagopaServiceId = $pagopaService['Id'] ?? null;
} else $pagopaServiceId = null;

//Parametri stampatore////////////////////////////
$str_Mod23LSubject          = $r_Customer['NationalMod23LSubject'];
$str_Mod23LCustomerName     = $r_Customer['NationalMod23LCustomerName'];

$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrinterId AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_SmaName                = $r_PrintParameter['NationalSmaName'] ?? '';
$str_SmaAuthorization       = $r_PrintParameter['NationalSmaAuthorization'] ?? '';
$str_SmaPayment             = $r_PrintParameter['NationalSmaPayment'] ?? '';

$str_Mod23LCustomerSubject  = $r_PrintParameter['NationalMod23LCustomerSubject'] ?? '';
$str_Mod23LCustomerAddress  = $r_PrintParameter['NationalMod23LCustomerAddress'] ?? '';
$str_Mod23LCustomerCity     = $r_PrintParameter['NationalMod23LCustomerCity'] ?? '';

$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['NationalPostalAuthorizationPagoPA'] ?? '');
////////////////////////////////////////////////

if(isset($_POST['checkbox'])) {
    
    $cls_iuv = new cls_iuv();
    $rs->Start_Transaction();
    
    if($_SESSION['cityid']=="H452"){
        $str_CustomerAddress = "Art.57 CPP e Art.11 c.1 L.a) e b) CDS";
        $str_CustomerCity = $r_Customer['ManagerAddress']. " " .$r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
    } else {
        $str_CustomerAddress = $r_Customer['ManagerAddress'];
        $str_CustomerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
    }

    //INIZIALIZZAZIONE FILE DI TESTO FLUSSO//////////////////////////////////////////////////////////////////////
    if($ultimate){
        $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
        $flow = mysqli_fetch_array($flows);
        $int_FlowNumber = $flow['Number']+1;
        $str_FlowType = ($RegularPostalFine) ? "_PostaNorm_Ita_" : "_Verb_Ita_";
        $FileNameDoc = "Flusso_".$int_FlowNumber.$str_FlowType.$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);
        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";
    } else {
        $FileNameDoc = "Flusso_Verb_Ita_".$_SESSION['cityid']."_PROVVISORIO";
        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";
    }

    if (!is_dir(NATIONAL_FLOW . "/" . $_SESSION['cityid'])) {
        mkdir(NATIONAL_FLOW . "/" . $_SESSION['cityid'], 0777);
    }
    
    $path = NATIONAL_FLOW."/".$_SESSION['cityid']."/";
    $tmp_path = NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/TMP/";
    if (!is_dir(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/TMP")) {
        mkdir(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/TMP", 0777);
    }
    
    $tempTXT = tempnam($tmp_path, $Documentation);
    $myfile = fopen($tempTXT, "w") or die("Unable to open file!");
    
    $a_FlowColumns = array(
        'FineId',
        'CRON',
        'STAMPA_AVVISO_PAGOPA',
        'BOL_PA_P1_intestatario_CCP',
        'BOL_PA_P1_oggetto_del_pagamento',
        'BOL_PA_P1_ente_creditore',
        'BOL_PA_P1_CF_ente_creditore',
        'BOL_PA_P1_CCP_ente_creditore',
        'BOL_PA_P1_settore_ente_creditore',
        'BOL_PA_P1_info_ente_creditore',
        'BOL_PA_P1_cbill_ente_creditore',
        'BOL_PA_P1_nome_cognome_destinatario',
        'BOL_PA_P1_CF_PIVA',
        'BOL_PA_P1_indirizzo_destinatario_completo',
        'BOL_PA_P1_autorizzazione',
        'NUMAVVIUV1',
        'NUMAVVIUV2',
        'QRCODE1_LINK',
        'QRCODE2_LINK',
        'QRCODE1',
        'QRCODE2',
        'STRINGA_QRCODE1',
        'STRINGA_QRCODE2',
        'IMPORTO1',
        'IMPORTO2',
        'ENTE_CREDITORE',
        'SETTORE_ENTE_CREDITORE',
        'INFO_ENTE_CREDITORE',
        'CF_ENTE_CREDITORE',
        'CBILL_ENTE_CREDITORE',
        'INTESTATARIO_CPP',
        'OGGETTO_PAGAMENTO',
        'NUMERO_CPP',
        'TIPOLOGIA_STAMPA',
        'TIPOLOGIA_ATTO',
        'TIPOLOGIA_FLUSSO',
        'RICHIESTA_DATI',
        'CodiceComune',
        'HeaderRow1',
        'HeaderRow2',
        'HeaderRow3',
        'HeaderRow4',
        'HeaderRow5',
        'Spese_Anticipate',
        'Intestatario_SMA',
        'Numero_SMA',
        'Mod23_Soggetto_Mittente',
        'Mod23_Ente_Gestito',
        'Mod23_Recapito_Soggetto',
        'Mod23_Indirizzo_Soggetto',
        'Mod23_Citta_Soggetto',
        'Recipient_Row1',
        'Recipient_Row2',
        'Recipient_Row3_1',
        'Recipient_Row3_2',
        'Recipient_Row3_3',
        'CODICE_FISCALE',
        'NOME_FLUSSO',
        'NOME_DOCUMENTO',
        'N_PAGINE',
    );
    
    $str_txt = '';
    foreach($a_FlowColumns as $value){
        $str_txt .= $value.";";
    }
    
    fwrite($myfile, $str_txt.PHP_EOL);
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //fputcsv($myfile, $a_FlowColumns, ';');

    foreach($_POST['checkbox'] as $FineId) {
        $NotificationFee            = 0;
        $ResearchFee                = 0;
        $ChargeTotalFee             = 0;
        $chk_126Bis                 = false;
        $chk_ReducedPayment         = false;
        
        //Uso questa data per trovare le spese di spedizione uguali a quelle indicate sul verbale il giorno della sua creazione
        $NotificationDate = date("Y-m-d");
        $rs_FinePrintDate = $rs->Select('FineHistory', "NotificationTypeId=2 AND FineId=" . $FineId);
        $r_FinePrintDate = mysqli_fetch_array($rs_FinePrintDate);
        if (isset($r_FinePrintDate['NotificationDate']))
            $NotificationDate = $r_FinePrintDate['NotificationDate'];
        
        //qui legge la tabella delle spese postali
        //Viene utilizzata una funzione che recupera gli importi corretti in base alla data e al CityId
        $postalcharge = getPostalCharge($_SESSION['cityid'], $NotificationDate);
        // $RegularPostalFine indica l'invio per posta ordinaria
        // e allora le spese erano state prese per comodità dalle variabili dei solleciti 
        // che sono inviati per posta oridinaria invece che per raccomandata
        $NotificationFee = $r_Customer['NationalNotificationFee'] > 0 ? $r_Customer['NationalNotificationFee'] : $postalcharge['Zone' . $ZoneId];
        
        //Se il forfettario è impostato, reimposta la variabile
        if ($r_Customer['NationalTotalFee'] > 0) 
            $ChargeTotalFee = $r_Customer['NationalTotalFee'];
        //Se le spese di notifica sono impostate, reimposta le variabili
        else if ($r_Customer['NationalNotificationFee'] > 0) {
            $ResearchFee = $r_Customer['NationalResearchFee'];
        }
        
        //Prima di arrivare a questo blocco, le spese forfettario, notifica e ricerca sono già state caricate nelle variabili
        //Se è spedito per posta ordinaria, le spese di ricerca vengono azzerate
        if($RegularPostalFine){
            $ResearchFee = 0.00;
            $NotificationFee = $postalcharge['ReminderZone' . $ZoneId];
        } else {
            //Se il forfettario è impostato, le spese di ricerca sono uguali alla differenza tra il forfettario e le spese di notifica
            if ($ChargeTotalFee > 0) {
                $ResearchFee = $ChargeTotalFee - $NotificationFee;
            }
        }

        $rs_Fine = $rs->Select('V_FineArticle', "Id=" . $FineId. " 
        AND (TrespasserTypeId=1 OR TrespasserTypeId=11
            OR (TrespasserTypeId=2 AND FineSendDate IS NULL)
            OR (TrespasserTypeId=3 AND FineSendDate IS NULL)
            OR (TrespasserTypeId=15 AND FineSendDate IS NULL)
            OR (TrespasserTypeId=16 AND FineSendDate IS NULL))
        "
        );

        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
            
            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;
            
            $rs_Row = $rs->Select('V_FineHistory', "(NotificationTypeId=2 || NotificationTypeId=3) AND Id=" . $FineId);
            $r_Row = mysqli_fetch_array($rs_Row);
            
            $controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Id =" . $r_Row['FineControllerId']);
            $controller = mysqli_fetch_array($controllers);
            
            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);
            
            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
                );

            $ViolationTypeId = $r_Fine['ViolationTypeId'];

            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            
            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
            
            
            if ($r_ArticleTariff['126Bis'] == 1) {
                $chk_126Bis = true;
            }
            
            if ($r_ArticleTariff['ReducedPayment'] == 1) {
                $chk_ReducedPayment = true;
            }
            
            if ($r_Fine['ArticleNumber'] > 1) {
                $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $FineId, "ArticleOrder");
                while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                    if ($r_AdditionalArticle['ReducedPayment'] == 1) {
                        $chk_ReducedPayment = true;
                    }
                }
            }

            $ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            $FormTypeId = $r_RuleType['NationalFormId'];

            //CERCA IL FILE PDF
            $rs_FineDocumentation = $rs->Select('FineHistory', "FineId=$FineId AND NotificationTypeId=2 AND TrespasserId={$r_Fine['TrespasserId']}");
            $r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation);
            $FineFileName = $r_FineDocumentation['Documentation'];
            //
            
            //DATI PAGOPA////////////////////////////////////////////////////////////////////////
            if ($r_ArticleTariff['ReducedPayment']) {
                $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
                
            } else {
                $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
            }
            $PagoPACode1 = $r_Fine['PagoPA1'];
            $PagoPACode2 = $r_Fine['PagoPA2'];
            
            if (!empty($PagoPACode1)){
                //Se l'ente prevede di usare codici avviso invece che IUV, usa direttamente quello, altrimenti tenta di costruirlo
                //Se fallisce a costruirlo non processa l'atto e restituisce un avviso
                if($r_Customer['IsIuvCodiceAvviso'] != 1){
                    try {
                        $PagoPAPaymentNotice1 = $cls_iuv->generateNoticeCode($PagoPACode1, $r_Customer['PagoPAAuxCode'], $r_Customer['PagoPAApplicationCode']);
                    } catch (Exception $e) {
                        if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
                        trigger_error('ID '.$FineId.': Errore nella costruzione del codice avviso PagoPA1: '.$e, E_USER_WARNING);
                        $str_Warning .= 'ID '.$FineId.': Errore nella costruzione del codice avviso PagoPA1, l\'atto non verrà processato. Verificare il codice IUV dati e le configurazioni.<br>';
                        $PagoPAPaymentNotice1 = '';
                    }
                } else $PagoPAPaymentNotice1 = $PagoPACode1;
            } else $PagoPAPaymentNotice1 = '';
            
            if (!empty($PagoPACode2)){
                //Se l'ente prevede di usare codici avviso invece che IUV, usa direttamente quello, altrimenti tenta di costruirlo
                //Se fallisce a costruirlo non processa l'atto e restituisce un avviso
                if($r_Customer['IsIuvCodiceAvviso'] != 1){
                    try {
                        $PagoPAPaymentNotice2 = $cls_iuv->generateNoticeCode($PagoPACode2, $r_Customer['PagoPAAuxCode'], $r_Customer['PagoPAApplicationCode']);
                    } catch (Exception $e) {
                        if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
                        trigger_error('ID '.$FineId.': Errore nella costruzione del codice avviso PagoPA2: '.$e, E_USER_WARNING);
                        $str_Warning .= 'ID '.$FineId.': Errore nella costruzione del codice avviso PagoPA2, l\'atto non verrà processato. Verificare il codice IUV e le configurazioni.<br>';
                        $PagoPAPaymentNotice2 = '';
                    }
                } else $PagoPAPaymentNotice2 = $PagoPACode2;
            } else $PagoPAPaymentNotice2 = '';
            
            //Se l'id del verbale non è fra quelli di cui PagoPA ha fallito, esegue le query, scrive i dati nel file di testo e aggiunge i pdf generati al pdf unito
            if(!in_array($FineId, $a_FailedPagoPA)){
                $PagoPAReducedPartial = $r_Fine['PagoPAReducedPartial'] ?? '';
                $PagoPAPartial = $r_Fine['PagoPAPartial'] ?? '';
                $PagoPAReducedTotal = $r_Fine['PagoPAReducedTotal'] ?? '';
                $PagoPATotal = $r_Fine['PagoPATotal'] ?? '';
                
                $Bollettino1Fee = $chk_ReducedPayment ? $PagoPAReducedPartial : $PagoPAPartial;
                $Bollettino2Fee = $chk_ReducedPayment ? $PagoPAReducedTotal : $PagoPATotal;
                $str_PagoPa1Fee = AddNZeroToNumber(str_replace(".","",number_format((float)$Bollettino1Fee, 2, '.', '')),6);
                $str_PagoPa2Fee = AddNZeroToNumber(str_replace(".","",number_format((float)$Bollettino2Fee, 2, '.', '')),6);
                
                $url_PagoPAPage1 = pickPagoPAPaymentUrl($pagopaServiceId, array('iuv' => $PagoPACode1));
                $url_PagoPAPage2 = pickPagoPAPaymentUrl($pagopaServiceId, array('iuv' => $PagoPACode2));
                
                $PagoPACode1Full = "PAGOPA|002|".$PagoPAPaymentNotice1."|".trim($r_Customer['ManagerTaxCode'])."|".$str_PagoPa1Fee;
                $PagoPACode2Full = "PAGOPA|002|".$PagoPAPaymentNotice2."|".trim($r_Customer['ManagerTaxCode'])."|".$str_PagoPa2Fee;
                
                //POPOLA CSV
                $a_FlowRows = array(
                    /*FineId*/                                          $FineId,
                    /*CRON*/                                            $r_Fine['ProtocolId'].'/'.$r_Fine['ProtocolYear'],
                    /*STAMPA_AVVISO_PAGOPA*/                            "NO", //$r_Customer['PagoPAPaymentNoticeNational'] ? "SI" : "NO",
                    /*BOL_PA_P1_intestatario_CCP*/                      $r_Customer['PagoPACPPOwner'],
                    /*BOL_PA_P1_oggetto_del_pagamento*/                 $r_Customer['PagoPAPaymentSubject'],
                    /*BOL_PA_P1_ente_creditore*/                        $r_Customer['ManagerName'],
                    /*BOL_PA_P1_CF_ente_creditore*/                     $r_Customer['ManagerTaxCode'],
                    /*BOL_PA_P1_CCP_ente_creditore*/                    $r_Customer['NationalBankAccount'],
                    /*BOL_PA_P1_settore_ente_creditore*/                "Anno ". $r_Fine['ProtocolYear'] ." targa ".$r_Fine['VehiclePlate'],
                    /*BOL_PA_P1_info_ente_creditore*/                   $r_Customer['PagoPAPaymentInfo'],
                    /*BOL_PA_P1_cbill_ente_creditore*/                  $r_Customer['PagoPACBILL'],
                    /*BOL_PA_P1_nome_cognome_destinatario*/             str_replace("&","E",(!empty($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . " " . $trespasser['Name']),
                    /*BOL_PA_P1_CF_PIVA*/                               PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode']),
                    /*BOL_PA_P1_indirizzo_destinatario_completo*/       '',
                    /*BOL_PA_P1_autorizzazione*/                        $str_PostalAuthorizationPagoPA,
                    /*NUMAVVIUV1*/                                      $PagoPACode1 != '' ? $PagoPAPaymentNotice1 : 'NON PRESENTE',
                    /*NUMAVVIUV2*/                                      $PagoPACode2 != '' ? $PagoPAPaymentNotice2 : 'NON PRESENTE',
                    /*QRCODE1_LINK*/                                    $url_PagoPAPage1,
                    /*QRCODE2_LINK*/                                    $url_PagoPAPage2,
                    /*QRCODE1*/                                         $PagoPACode1 != '' ? $PagoPACode1Full : 'NON PRESENTE',
                    /*QRCODE2*/                                         $PagoPACode2 != '' ? $PagoPACode2Full : 'NON PRESENTE',
                    /*STRINGA_QRCODE1*/                                 $str_PaymentDay1,
                    /*STRINGA_QRCODE2*/                                 $str_PaymentDay2,
                    /*IMPORTO1*/                                        $Bollettino1Fee,
                    /*IMPORTO2*/                                        $Bollettino2Fee,
                    /*ENTE_CREDITORE*/                                  $r_Customer['ManagerName'],
                    /*SETTORE_ENTE_CREDITORE*/                          $ManagerSubject,
                    /*INFO_ENTE_CREDITORE*/                             $r_Customer['PagoPAPaymentInfo'],
                    /*CF_ENTE_CREDITORE*/                               $r_Customer['ManagerTaxCode'],
                    /*CBILL_ENTE_CREDITORE*/                            $r_Customer['PagoPACBILL'],
                    /*INTESTATARIO_CPP*/                                $r_Customer['PagoPACPPOwner'],
                    /*OGGETTO_PAGAMENTO*/                               $r_Customer['PagoPAPaymentSubject'],
                    /*NUMERO_CPP*/                                      $r_Customer['NationalBankAccount'],
                    /*TIPOLOGIA_STAMPA*/                                'Atto Giudiziario',
                    /*TIPOLOGIA_ATTO*/                                  'VERBALI',
                    /*TIPOLOGIA_FLUSSO*/                                ($r_Fine['FineTypeId']==4) ? '40' : $FormTypeId,
                    /*RICHIESTA_DATI*/                                  ($chk_126Bis) ? "SI" : "NO",
                    /*CodiceComune*/                                    $_SESSION['cityid'],
                    /*HeaderRow1*/                                      $r_Customer['ManagerName'],
                    /*HeaderRow2*/                                      $ManagerSubject,
                    /*HeaderRow3*/                                      $str_CustomerAddress,
                    /*HeaderRow4*/                                      $str_CustomerCity,
                    /*HeaderRow5*/                                      $r_Customer['ManagerPhone'],
                    /*Spese_Anticipate*/                                $str_SmaPayment,
                    /*Intestatario_SMA*/                                $str_SmaName,
                    /*Numero_SMA*/                                      $str_SmaAuthorization,
                    /*Mod23_Soggetto_Mittente*/                         $str_Mod23LSubject,
                    /*Mod23_Ente_Gestito*/                              $str_Mod23LCustomerName,
                    /*Mod23_Recapito_Soggetto*/                         $str_Mod23LCustomerSubject,
                    /*Mod23_Indirizzo_Soggetto*/                        $str_Mod23LCustomerAddress,
                    /*Mod23_Citta_Soggetto*/                            $str_Mod23LCustomerCity,
                    /*Recipient_Row1*/                                  $a_GenreLetter[$trespasser['Genre']] . " " . (!empty($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . " " . $trespasser['Name'],
                    /*Recipient_Row2*/                                  $str_TrespasserAddress,
                    /*Recipient_Row3_1*/                                $trespasser['ZIP'],
                    /*Recipient_Row3_2*/                                $trespasser['City'],
                    /*Recipient_Row3_3*/                                $trespasser['Province'],
                    /*CODICE_FISCALE*/                                  PickVatORTaxCode($trespasser['Genre'],$trespasser['VatCode'],$trespasser['TaxCode']),
                    /*NOME_FLUSSO*/                                     $DocumentationZip,
                    /*NOME_DOCUMENTO*/                                  $FineFileName,
                );
                
                //fputcsv($myfile, $a_FlowRows, ';');
                
                //In caso di verbale contratto, nel pdf della relata, vengono aggiunti tra il frontespizio e i bollettini i file della scansione del pdf originale che non vengono inseriti nella creazione del verbale
                //TODO chiedere dove inserire gli allegati
                //Ipotesi: apriamo il file generato in creazione, gli inseriamo all'interno gli allegati prima dei bollettini e richiudiamo il file da inserire nello zip
                
                $pdf_union = new FPDI();
                $pdf_union->setHeaderFont(array('helvetica', '', 8));
                $pdf_union->setFooterFont(array('helvetica', '', 8));
                $pdf_union->setPrintHeader(false);
                $pdf_union->setPrintFooter(false);
                
                if($r_Fine['FineTypeId']==4){
                    
                    $a_ContractFine[$FineId][] = $FineFileName;
                    $a_Contract = array();
                    
                    //Questo pezzo gestisce il caso di verbale contratto con più trasgressori in cui l'allegato orginale va inserito con la ralata per tutti
                    $strProtocolNumber = "";
                    for ($k = strlen((string)$r_Fine['ProtocolId']); $k < 9; $k++) {
                        $strProtocolNumber .= "0";
                    }
                    $strProtocolNumber .= $r_Fine['ProtocolId'];
                    
                    $prefissoFine = $r_Fine['ProtocolYear']. "_" . $strProtocolNumber;
                    $rs_FDContract = $rs->SelectQuery("SELECT Documentation FROM FineDocumentation WHERE FineId=$FineId AND DocumentationTypeId=2 AND Documentation NOT LIKE '$prefissoFine%'");
                    
                    //Crea un vettore con il documento della relata preso precedentemente da FineHistory e il verbale, preso ora da FineDocumentation
                    $a_Contract['Notification'] = $FineFileName;
                    if($r_FDContract = $rs->getArrayLine($rs_FDContract)){
                        $a_Contract['Fine'] = $r_FDContract['Documentation'];
                    }
                    
                    $Row = 0;
                    $a_BillPages = array();
                    
                    foreach($a_Contract as $Type => $FileName){
                        try {
                            $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $FileName);
                            for ($p = 1; $p <= $n_PageCount; $p++) {
                                
                                //Prende le pagine del bollettino ($n_PageCount-2) e le tengo da parte, Row == 1 serve a sapere che va fatto sul primo record, quello che contiene il verbale creato
                                if($p > ($n_PageCount-2) && $Type == 'Notification'){
                                    $a_BillPages[] = $pdf_union->ImportPage($p);
                                } else {
                                    $tmp_Page = $pdf_union->ImportPage($p);
                                    $pdf_union->AddPage();
                                    $pdf_union->useTemplate($tmp_Page);
                                }
                                
                            }
                            
                            //Aggiunge una pagina bianca al verbale allegato se le pagine sono dispari
                            if($Type == 'Fine' && $n_PageCount%2 == 1){
                                $pdf_union->AddPage();
                            }
                        } catch (Exception $e) {
                            trigger_error("Errore nell'unione di verbale contratto e relata di notifica. ID:$FineId : ".$e, E_USER_WARNING);
                            $str_Warning .= "Errore nell'unione di verbale contratto e relata di notifica. ID: $FineId<br>";
                        }
                    }
                    
                    //Aggancio le pagine tenute da parte
                    if(!empty($a_BillPages)){
                        foreach($a_BillPages as $BillPage){
                            $pdf_union->AddPage();
                            $pdf_union->useTemplate($BillPage);
                        }
                    }
                    
                    $pdf_union->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' . $FineFileName, "F");
                    
                    try {
                        //Reinizializza FPDI per evitare PHP Notice:  Undefined property: FPDI::$PDFVersion
                        $pdf_union = new FPDI();
                        $txtNumberOfPages = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' . $FineFileName);
                    } catch (Exception $e) {
                        $txtNumberOfPages = "DOCUMENTO_ASSENTE";
                    }
                    
                } else {
                    $a_Fine[$FineId][] = $FineFileName;
                    
                    try {
                        //Reinizializza FPDI per evitare PHP Notice:  Undefined property: FPDI::$PDFVersion
                        $pdf_union = new FPDI();
                        $txtNumberOfPages = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $FineFileName);
                    } catch (Exception $e) {
                        $txtNumberOfPages = "DOCUMENTO_ASSENTE";
                    }
                }
                
                $a_FlowRows[] = $txtNumberOfPages;
                
                //Scrive il file di testo
                $str_txt = '';
                foreach($a_FlowRows as $value){
                    $str_txt .= $value.";";
                }
                
                fwrite($myfile, $str_txt.PHP_EOL);
                
                if ($ultimate) {
                    
                    $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $r_Fine['Id']." AND NotificationTypeId=".$NotificationTypeId);
                    
                    if(mysqli_num_rows($rs_FineHistory)==0){
                        $aInsert = array(
                            array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['Id'], 'settype' => 'int'),
                            array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                            array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                            array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                            array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                            array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $controller['Id'], 'settype' => 'int'),
                            array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                            array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                            array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                            array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentationZip),
                        );
                        $rs->Insert('FineHistory', $aInsert);
                        
                        $aUpdate = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int')
                        );
                        $rs->Update('Fine', $aUpdate, 'Id=' . $r_Fine['Id']);
                    }
                }
                
                $n_Successful ++;
            }
        }
    }
    fclose($myfile);

    if($n_Successful > 0){
        $zip = new ZipArchive();
        if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($tempTXT,$Documentation);
            
            //Sposta i file nello zip
            foreach ($a_Fine as $DocFineId => $a_Doc){
                foreach ($a_Doc as $Doc){
                    $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . "/" . $Doc, $Doc);
                }
            }
            
            //Sposta le fusioni verbali contratto nello zip
            foreach ($a_ContractFine as $DocFineId => $a_Doc){
                foreach ($a_Doc as $Doc){
                    $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' . $Doc, $Doc);
                }
            }
            //
            
            $zip->close();
            
            //Rimozione file temporanei
            unlink($tempTXT);
            
            //Rimozione fusioni verbali contratto
            foreach ($a_ContractFine as $DocFineId => $a_Doc){
                foreach ($a_Doc as $Doc){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' . $Doc);
                }
            }
            //
            
            $_SESSION['Documentation'] = NATIONAL_FLOW_HTML.'/'.$_SESSION['cityid'].'/'.$DocumentationZip;
        } else {
            $str_Warning .= "Errore nella creazione dell\'archivio del flusso.<br>";
        }
    } else {
        $str_Warning .= "Errore nella creazione dell\' archivio del flusso: Nessun atto da includere.</br>";
        //Rimozione file temporanei
        unlink($tempTXT);
    }
    
    //Se definitivo e almeno un atto è stato processato con successo, crea il record del flusso e ne attribuisce l'id a ogni FineHistory
    if($ultimate && $n_Successful > 0){
        $Zone0Number=count($_POST['checkbox']);
        $aInsertFlow = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
            array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
            array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$PrinterId,'settype'=>'int'),
            array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>$PrintTypeId,'settype'=>'int'),
            array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeId,'settype'=>'int'),
            array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
            array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
            array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
            array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
            array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
        );
        $FlowId = $rs->Insert('Flow',$aInsertFlow);
        
        $aUpdateFineHistory = array(
            array('field' => 'FlowId', 'selector' => 'value', 'type' => 'int', 'value' => $FlowId, 'settype' => 'int')
        );
        
        $rs->Update('FineHistory', $aUpdateFineHistory, "Documentation='$DocumentationZip' AND FlowNumber=$int_FlowNumber");
    }
    
    //Se il verbale è da inviare ad uno stampatore, chiama una funzione specifica definita nel parametri di configurazione
    //passandogli i riferimenti al file del flusso come parametri
    if($a_PrinterConf && $ultimate && PRODUCTION){
        if(!$phpFTP->connect()){
            $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
            header("location: ".$P);
            DIE;
        } else {
            //Riferimenti zip flusso
            $a_Flow = array(
                'LocalFile' => $path.$DocumentationZip,
                'RemoteFile' => isset($a_PrinterConf['Path']['VERBALI'])
                ? $a_PrinterConf['Path']['VERBALI'].'/'.$DocumentationZip
                : $DocumentationZip
            );
            
            if(call_user_func_array($a_PrinterConf['Function'], array($phpFTP, $a_Flow))){
                $a_UpdateFlow = array(
                    array('field'=>'UploadDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'))
                );
                
                $rs->Update('Flow', $a_UpdateFlow, "Id=$FlowId");
                
                $str_Success .= "Flusso caricato con successo.<br>";
            } else {
                $str_Warning .= 'Errore nell\'invio del flusso allo stampatore:<br>'.implode('<br>', $phpFTP->errors());
            }
            
            $phpFTP->disconnect();
        }
    }
    
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
    } else if ($ultimate) {
        $str_Success .= 'Azione eseguita con successo.';
        $_SESSION['Message']['Success'] = $str_Success;
    }
    
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $rs->End_Transaction();
}

