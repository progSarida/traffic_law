<?php
require_once(CLS . '/cls_iuv.php');
require_once(INC."/function_postalCharge.php");
require_once(CLS."/avvisiPagoPA/ModelloCDS.php");

define("ADDITIONAL_SANCTION_NOT_EXPECTED", "non_prevista");

//Funzione per sositutire le variabili nel sottotesto dei trasgressori
function replaceTrespasserData($str_trespassertype, $a_trespasser, $isrenttrespasser = false){
    $Genre = $a_trespasser['Genre'];
    switch ($Genre) {
        case 'F': $GenreBornIn = ' Nata a '; break;
        case 'M': $GenreBornIn = ' Nato a '; break;
        default: $GenreBornIn = ' Nato/a a ';
    }
    $ReceiveDate = DateOutDB($a_trespasser['ReceiveDate']);
    
    $str_trespassertype = preg_replace('/' . ($isrenttrespasser ? '{TrespasserRentName}' : '{TrespasserName}') . '/', StringOutDB((!empty($a_trespasser['CompanyName']) ? $a_trespasser['CompanyName'].' ' : '') . $a_trespasser['Surname'] . ' ' . $a_trespasser['Name']), $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{TrespasserAddress}' . '/', StringOutDB($a_trespasser['Address'] . " " . $a_trespasser['ZIP']), $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{TrespasserCity}' . '/', StringOutDB($a_trespasser['City']), $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{TrespasserProvince}' . '/', StringOutDB($a_trespasser['Province']), $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{TrespasserBornCity}' . '/', $Genre != 'D' ? StringOutDB($a_trespasser['BornPlace']) : '', $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{TrespasserBornDate}' . '/', $Genre != 'D' ? DateOutDB($a_trespasser['BornDate']) : '', $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{NatoA}' . '/', $Genre != 'D' ? (!empty($a_trespasser['BornPlace']) ? $GenreBornIn : '') : '', $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{Il}' . '/', $Genre != 'D' ? (!empty($a_trespasser['BornDate']) ? ' il ' : '') : '', $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{ResIn}' . '/', $Genre != 'D' ? 'Res: in ' : 'Con sede: in ', $str_trespassertype, 1);
    $str_trespassertype = preg_replace('/' . '{DateRent}' . '/', $ReceiveDate ? '<br />(Identificazione dati avvenuta in data: '.$ReceiveDate.')' : '', $str_trespassertype, 1);
    
    return $str_trespassertype;
}

function executeQueries($action, $table, $array, $where = null){
    global $rs;
    if(!empty($array)){
        foreach($array as $data){
            if(empty($where)){
                $rs->$action($table, $data);
            } else {
                $rs->$action($table, $data, $where);
            }
        }
    }
}

function buildManagerInfo($r_Customer){
    $str_Info = '';
    
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ManagerPhone'] != ''){
        $str_Info .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Info .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Info .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Info .= $r_Customer['ManagerPhone'] ? ' - TEL: '.$r_Customer['ManagerPhone'] : '';
        $str_Info .= $r_Customer['ManagerPEC'] ? ' - PEC: '.$r_Customer['ManagerPEC'] : '';
    }
    
    return $str_Info;
}

//SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE////////////////////////////////////////////
function applyTemporaryLabel($pdf){
    $TotalPages = $pdf->PageNo();
    for ($i=1; $i<=$TotalPages; $i++){
        $pdf->setPage($i, true);
        $pdf->SetXY($pdf->pixelsToUnits(80), $pdf->pixelsToUnits(675));
        $pdf->StartTransform();
        $pdf->Rotate(50);
        $pdf->SetFont('helvetica', '', 22);
        $pdf->SetTextColor(190);
        $pdf->Cell($pdf->pixelsToUnits(650),0,'S   T   A   M   P   A         P   R   O   V   V   I   S   O   R   I   A',0,1,'C',0,'');
        $pdf->StopTransform();
    }
}

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: frm_senddynamic_kindfine.php".$Filters);
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

//Coordinata inizio stampa testo dinamico
$TextStartCoord = array('X'=>10, 'Y'=>88);

$str_Warning = '';
$str_Error = '';

//Determina quanti sono stati processati con successo
$n_Successful = 0;

$ProtocolNumber = 0;
$CreationType = 1;

$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$str_Speed = "";

$a_Lan = unserialize(LANGUAGE);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT_CITYID)[$_SESSION['cityid']];
$a_AdditionalMass = unserialize(ADDITIONAL_MASS_CITYID)[$_SESSION['cityid']];
if($a_AdditionalNight == null)
    $a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
    
    if($a_AdditionalMass == null)
        $a_AdditionalMass = unserialize(ADDITIONAL_MASS);
$a_FineSendType = unserialize(FINE_SEND_TYPE);

$FormTypeId = 82;
$PrinterId = 1;
//$PrinterId = 9; stampa distinta per la posta

// todo gestione stampatore


$a_Documentation = array();
//Contiene i FineId di cui non è stato possibile generare/aggiornare PagoPA
$a_FailedPagoPA = array();


$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");
$PrintTypeId = 3; //tipo di Avviso bonario su traffic_law.Print_Type
$DocumentTypeId = 16; //bonario estero traffic_law.Document_Type
$NotificationTypeId = 30; //Invio bonario traffic_law.NotificationType
$NotificationDate = $KindCreateDate = DateInDB($CreationDate);

if($CreationType==5){
    $rs_Customer = $rs->Select("V_Customer", "CreationType=5 AND CityId='".$_SESSION['cityid']."'");
    $r_Customer  = mysqli_fetch_array($rs_Customer);
}

$str_WhereCity = ($r_Customer['CityUnion']>=1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONTROLLI PRELIMINARI//////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Bug 2869 PagoPA funziona per ora solo per CDS
$b_PagoPAEnabled = $r_Customer['PagoPAPaymentForeign'] > 0 && $_SESSION['ruletypeid'] == RULETYPE_CDS;
$b_PrintBill = ($b_PagoPAEnabled ? $r_Customer['PagoPAPaymentNoticeForeign'] <= 0 : true) && !empty($r_Customer['ForeignPostalType']);
$b_PrintBillPagoPA = $b_PagoPAEnabled && !empty($r_Customer['ForeignPostalTypePagoPA']) && $r_Customer['PagoPAPaymentNoticeForeign'] > 0;

//Parametri stampatore////////////////////////////
$str_Mod23LSubject          = $r_Customer['ForeignMod23LSubject'];
$str_Mod23LCustomerName     = $r_Customer['ForeignMod23LCustomerName'];

$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrinterId AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_SmaName                = $r_PrintParameter['ForeignSmaName'] ?? '';
$str_SmaAuthorization       = $r_PrintParameter['ForeignSmaAuthorization'] ?? '';
$str_SmaPayment             = $r_PrintParameter['ForeignSmaPayment'] ?? '';

$str_Mod23LCustomerSubject  = $r_PrintParameter['ForeignMod23LCustomerSubject'] ?? '';
$str_Mod23LCustomerAddress  = $r_PrintParameter['ForeignMod23LCustomerAddress'] ?? '';
$str_Mod23LCustomerCity     = $r_PrintParameter['ForeignMod23LCustomerCity'] ?? '';

$str_PostalAuthorization = trim($r_PrintParameter['ForeignPostalAuthorization']) ?? '';
$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['ForeignPostalAuthorizationPagoPA'] ?? '');
//////////////////////////////////////////////////

//Controlli parametri
if($b_PagoPAEnabled){
    //Se l'ente non ha CF/PIVA impostati
    if(empty($r_Customer['ManagerTaxCode'])){
        $str_Error .= 'È necessario specificare il codice fiscale dell\'ente per il funzionamento della gestione PagoPA (Ente > Gestione Ente > Indirizzo).<br>';
    }
}
if($b_PrintBill){
    if(empty($r_Customer['ForeignBankAccount'])){
        $str_Error .= 'Per la stampa del bollettino è necessario impostare il codice del conto corrente nei parametri dell\'ente.<br>';
    }
    if(empty($str_PostalAuthorization)){
        $str_Error .= 'Per la stampa del bollettino è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente in base alla Destinazione di stampa selezionata (Ente > Gestione Ente > Posta).<br>';
    }
}
if($b_PrintBillPagoPA){
    //Se la stampa all'avviso di pagamento PagoPA e la stampa del bollettino postale PagoPA sono attive ma non è impostata l'autorizzazione alla stampa
    if(empty($str_PostalAuthorizationPagoPA)){
        $str_Error .= 'Per la stampa del bollettino postale PagoPA è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente in base alla Destinazione di stampa selezionata (Ente > Gestione Ente > Posta).<br>';
    }
}

if(!empty($str_Error)){
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $_SESSION['Message']['Error'] = $str_Error;
    header("location: frm_senddynamic_kindfine.php".$Filters);
    DIE;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//SERVIZIO PAGOPA ENTE
//se è abilitato pagoPA interroghiamo la base dati per prendere le configurazioni
if($b_PagoPAEnabled){
    $pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
    $pagopaService=mysqli_fetch_array($pagopaServicequery);
}

if(isset($_POST['check'])) {
    
    $rs->Start_Transaction();
    
    $ultimate = CheckValue('ultimate','s');
    
    /////////////////////////////////////////////
    //Detector
    /////////////////////////////////////////////
    
    $a_DetectorPosition = array();
    $a_DetectorKind = array();
    $a_DetectorCode = array();
    $a_DetectorNumber = array();
    $a_DetectorTolerance = array();
    $a_DetectorRatification = array();
    $a_DetectorAdditionalTextIta = array();
    $a_DetectorTypeId = array();
    $a_SpeedLengthAverage = array();
    
    $rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0");
    while ($r_Detector = mysqli_fetch_array($rs_Detector)) {
        $a_DetectorPosition[$r_Detector['Id']] = ($r_Detector['Position']=="") ? "" : StringOutDB($r_Detector['Position']);
        $a_DetectorKind[$r_Detector['Id']] = ($r_Detector['Kind']=="") ? "" : StringOutDB($r_Detector['Kind']);
        $a_DetectorCode[$r_Detector['Id']] = ($r_Detector['Code']=="") ? "" : $r_Detector['Code'];
        $a_DetectorNumber[$r_Detector['Id']] = ($r_Detector['Number']=="") ? "" : $r_Detector['Number'];
        $a_DetectorTolerance[$r_Detector['Id']] = ($r_Detector['Tolerance']=="") ? "" : $r_Detector['Tolerance'];
        $a_DetectorRatification[$r_Detector['Id']] = ($r_Detector['Ratification']=="") ? "" : StringOutDB($r_Detector['Ratification']);
        $a_DetectorAdditionalTextIta[$r_Detector['Id']] = ($r_Detector['AdditionalTextIta']=="") ? "" : StringOutDB($r_Detector['AdditionalTextIta']);
        $a_DetectorTypeId[$r_Detector['Id']] = ($r_Detector['DetectorTypeId']=="") ? "" : $r_Detector['DetectorTypeId'];
        
        if ($r_Detector['DetectorTypeId'] == 1)
            $a_SpeedLengthAverage[$r_Detector['Id']] = $r_Detector['SpeedLengthAverage'];
    }
    /////////////////////////////////////////////
    
    /////////////////////////////////////////////
    //Offices
    /////////////////////////////////////////////
    $offices = $rs->Select('V_JudicialOffice', "CityId='" . $_SESSION['cityid'] . "'");
    
    $a_Office = array();
    
    while ($office = mysqli_fetch_array($offices)) {
        
        for ($i=1; $i<count($a_Lan); $i++){
            $a_Office[$office['OfficeId']]['OfficeTitle'.$a_Lan[$i]] = isset($office['OfficeTitle'.$a_Lan[$i]]) ? StringOutDB($office['OfficeTitle'.$a_Lan[$i]]) : "";
        }
        $a_Office[$office['OfficeId']]['City'] = isset($office['City']) ? StringOutDB($office['City']) : "";
        $a_Office[$office['OfficeId']]['Province'] = isset($office['Province']) ? StringOutDB($office['Province']) : "";
        $a_Office[$office['OfficeId']]['Address'] = isset($office['Address']) ? StringOutDB($office['Address']) : "";
        $a_Office[$office['OfficeId']]['ZIP'] = isset($office['ZIP']) ? $office['ZIP'] : "";
        $a_Office[$office['OfficeId']]['Phone'] = isset($office['Phone']) ? $office['Phone'] : "";
        $a_Office[$office['OfficeId']]['Fax'] = isset($office['Fax']) ? StringOutDB($office['Fax']) : "";
        $a_Office[$office['OfficeId']]['Mail'] = isset($office['Mail']) ? StringOutDB($office['Mail']) : "";
        $a_Office[$office['OfficeId']]['PEC'] = isset($office['PEC']) ? StringOutDB($office['PEC']) : "";
        $a_Office[$office['OfficeId']]['Web'] = isset($office['Web']) ? StringOutDB($office['Web']) : "";
    }
    /////
    /////////////////////////////////////////////
    
    
    //INIZIALIZZAZIONE FILE DI TESTO FLUSSO//////////////////////////////////////////////////////////////////////
    if($ultimate){
        $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
        $flow = mysqli_fetch_array($flows);
        $int_FlowNumber = $flow['Number']+1;
        $FileName = "Flusso_".$int_FlowNumber."_BONARIO_Estero_".$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['check']);
    } else {
        $FileName = "Flusso_BONARIO_Estero_".$_SESSION['cityid']."_PROVVISORIO";
    }
    $DocumentationTxt = $FileName.".txt";
    $DocumentationZip = $FileName.".zip";
    
    $path = FOREIGN_FLOW."/".$_SESSION['cityid']."/";
    $tmp_path = FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/TMP/";
    if (!is_dir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/TMP")) {
        mkdir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/TMP", 0777);
    }
    
    $tempTXT = tempnam($tmp_path, $DocumentationTxt);
    $myfile = fopen($tempTXT, "w") or die("Unable to open file!");
    
    $a_FlowColumns = array(
        'FineId',
        'CRON',
        'STAMPA_AVVISO_PAGOPA',
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
    //////////////////////////////////////////////////////////////////////////////////////
    

    //FPDI///////////////////////////////////////////////////////
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array($font, '', 8));
    $pdf_union->setFooterFont(array($font, '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);
    ///////////////////////////////////////////////////////////
    
    foreach ($_POST['check'] as $FineId){
        
        $a_Documentation[$FineId] = array();
        $a_InsFineHistory= array();
        $a_InsFineDocumentation = array();
        $a_UpdFine = array();
        $a_FlowRows = array();
        
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);
        
        $TotalPages = 0;
        
        //TIPI TRASGRESSORE
        $a_TrespasserTypes = array();
        $rs_Trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $FineId);
        while ($r_Trespassers = mysqli_fetch_assoc($rs_Trespassers)){
            $a_TrespasserTypes['T'.$r_Trespassers['TrespasserTypeId']] = $r_Trespassers;
        }
        /////////////////////////////////////////////
        
        while($r_Fine = mysqli_fetch_array($rs_Fine)){
            //TCPDF//////////////////////////////////////////
            $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);
            $pdf->TemporaryPrint = $ultimate;
            $pdf->NationalFine = 1;
            $pdf->CustomerFooter = 0;
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_SESSION['citytitle']);
            $pdf->SetTitle('Kind fine');
            $pdf->SetSubject('');
            $pdf->SetKeywords('');
            $pdf->setHeaderFont(array($font, '', 8));
            $pdf->setFooterFont(array($font, '', 8));
            $pdf->SetMargins(10, 10, 10);
            /////////////////////////////////////////////////
            
            $chk_180            = false;
            $chk_126Bis         = false;
            $chk_ReducedPayment = false;
            $n_LicensePoint     = 0;
            $n_TotPartialFee    = 0;
            $n_TotFee           = 0;
            $n_TotMaxFee        = 0;
            
            $ViolationTypeId = $r_Fine['ViolationTypeId'];
            $ProtocolYear = $r_Fine['ProtocolYear'];
            
            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);
            
            $n_LanguageId = $trespasser['LanguageId'];
            $ZoneId= $trespasser['ZoneId'];
            
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$r_Fine['ViolationTypeId']." AND CityId='".$_SESSION['cityid']."'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            $RuleTypeId = $r_RuleType['Id'];
            //$ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
            
            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
            
            $rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
            $r_FineOwner = mysqli_fetch_array($rs_FineOwner);
            
            $AdditionalFee = 0;
            $AdditionalPartialFee = 0;
            $NotificationFee = 0;
            $ChargeTotalFee = 0;
            $ResearchFee = 0;
            $ResearchFee2 = 0; //per il secondo Iuv
            $KindFee = 0;
            
            if($r_Customer['ForeignTotalFee']>0) $ChargeTotalFee = $r_Customer['ForeignTotalFee'];
            else{
                if($r_Customer['ForeignNotificationFee']>0){
                    $NotificationFee = $r_Customer['ForeignNotificationFee'];
                }
                $ResearchFee = $r_Customer['ForeignResearchFee'];
            }
            
            $str_AdditionalNight = "";
            if ($r_ArticleTariff['AdditionalNight'] == 1) {
                
                $a_Time = explode(":", $r_Fine['FineTime']);
                
                if ($a_Time[0] < FINE_HOUR_START_DAY || ($a_Time[0] > FINE_HOUR_END_DAY) || ($a_Time[0] == FINE_HOUR_END_DAY && $a_Time[1] != "00")) {
                    $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId];
                }
            }
            
            $str_AdditionalMass = "";
            if ($r_ArticleTariff['AdditionalMass'] == 1) {
                if ($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
            }
            
            if ($r_ArticleTariff['126Bis'] == 1) {
                $chk_126Bis = true;
                $n_LicensePoint += $r_ArticleTariff['LicensePoint'];
            }
            
            if ($r_ArticleTariff['PresentationDocument'] == 1) {
                $chk_180 = true;
            }
            
            
            if($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2"){
                if($InsuranceDate!=""){
                    $n_Day = DateDiff("D", $r_Fine['ExpirationDate'], DateInDB($InsuranceDate));
                    
                    if($n_Day<=30){
                        $r_Fine['Fee'] = $r_Fine['Fee'] * FINE_INSURANCE_REDUCED;
                        $r_Fine['MaxFee'] = $r_Fine['MaxFee'] * FINE_INSURANCE_REDUCED;
                        
                        
                        $a_FineArticle = array(
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_Fine['Fee'], 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_Fine['MaxFee'], 'settype' => 'flt'),
                        );
                        
                    }
                }
            }
            
            
            $n_TotFee += $r_Fine['Fee'];
            $n_TotMaxFee += $r_Fine['MaxFee'];
            if ($r_ArticleTariff['ReducedPayment'] == 1) {
                $chk_ReducedPayment = true;
                $n_TotPartialFee += $r_Fine['Fee'] * FINE_PARTIAL;
            } else {
                $n_TotPartialFee += $r_Fine['Fee'];
            }
            
            $PreviousProtocolId = "";
            $PreviousProtocolYear = "";
            $PreviousFineDate = "";
            
            //Gestione verbalizzazione da tabella Fine
            //if($r_Fine['ReportNotificationDate']!="") $NotificationDate = $r_Fine['ReportNotificationDate'];
            $postalcharge=getPostalCharge($_SESSION['cityid'], $r_Fine['FineDate']);
            
            if($ChargeTotalFee>0){
                $ResearchFee = $ChargeTotalFee - $postalcharge['Zone'.$ZoneId];
                $NotificationFee = $postalcharge['Zone'.$ZoneId];
            }else{
                if($NotificationFee==0){
                    $NotificationFee = $postalcharge['Zone'.$ZoneId];
                }
            }
            
        	$KindFee = $postalcharge['ReminderZone'.$ZoneId];
        
            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;
            
        	$AdditionalFee = $NotificationFee + $ResearchFee2 + $KindFee;
        	$AdditionalPartialFee = $ResearchFee + $KindFee;
            
            
            //         if ($ChiefControllerId == 0) {
            //             if ($r_Customer['CityUnion'] > 1) {
            
            //                 $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Sign !='' AND Disabled=0 AND Locality='" . $r_Fine['Locality'] . "'");
            //                 $r_ChiefController = mysqli_fetch_array($rs_ChiefController);
            
            //                 $ChiefControllerName = trim($r_ChiefController['Qualification'] . " " . $r_ChiefController['Name']);
            
            //             }
            //         }
            
            /////////////////////////////////////////////
            //Additional Article
            /////////////////////////////////////////////
            $str_ListArticle = "";
            $str_ArticleDescription = "";
            
            if ($r_Fine['ArticleNumber'] > 1) {
                
                $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $FineId, "ArticleOrder");
                while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                    
                    if ($r_AdditionalArticle['126Bis'] == 1) {
                        $chk_126Bis = true;
                        $n_LicensePoint += $r_AdditionalArticle['LicensePoint'];
                    }
                    
                    
                    if($r_AdditionalArticle['Article']==193 && $r_AdditionalArticle['Paragraph']=="2"){
                        if($InsuranceDate!=""){
                            $n_Day = DateDiff("D", $r_Fine['ExpirationDate'], DateInDB($InsuranceDate));
                            if($n_Day<=30){
                                $r_AdditionalArticle['Fee'] = $r_AdditionalArticle['Fee'] * FINE_INSURANCE_REDUCED;
                                $r_AdditionalArticle['MaxFee'] = $r_AdditionalArticle['MaxFee'] * FINE_INSURANCE_REDUCED;
                                
                                $a_FineAdditionalArticle = array(
                                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_AdditionalArticle['Fee'], 'settype' => 'flt'),
                                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_AdditionalArticle['MaxFee'], 'settype' => 'flt'),
                                );
                                
                                
                            }
                        }
                    }
                    
                    $n_TotFee += $r_AdditionalArticle['Fee'];
                    $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
                    if ($r_AdditionalArticle['ReducedPayment'] == 1) {
                        $chk_ReducedPayment = true;
                        $n_TotPartialFee += $r_AdditionalArticle['Fee'] * FINE_PARTIAL;
                    } else {
                        $n_TotPartialFee += $r_AdditionalArticle['Fee'];
                    }
                    
                    $str_AdditionalArticleDescription = (strlen($r_AdditionalArticle['AdditionalArticleDescription' . LAN]) > 0) ? $r_AdditionalArticle['AdditionalArticleDescription' . LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];
                    
                    $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate']!="") ? DateOutDB($r_AdditionalArticle['ExpirationDate']) : "";
                    $str_AdditionalArticleDescription = str_replace("{ExpirationDate}", $str_ExpirationDate, $str_AdditionalArticleDescription);
                    
                    $Paragraph = ($r_AdditionalArticle['Paragraph'] == "0") ? "" : $r_AdditionalArticle['Paragraph'] . " ";
                    $Letter = ($r_AdditionalArticle['Letter'] == "0") ? "" : $r_AdditionalArticle['Letter'];
                    
                    
                    $str_ListArticle .= " e Art. " . $r_AdditionalArticle['Article'] . "/" . $Paragraph . $Letter;
                    $str_ArticleDescription .= " " . $str_AdditionalArticleDescription;
                    
                    if ($r_AdditionalArticle['PrefectureFixed'] == 1){
                        $str_ArticleDescription .= "con importo fissato da prefettura di euro ".$r_AdditionalArticle['PrefectureFee']." in data ".DateOutDB($r_AdditionalArticle['PrefectureDate']);
                    }
                    
                }
            }
            
            ////////////////////////////////////////////////////////////////////////////////////////////
            //INTESTAZIONE
            ///////////////////////////////////////////////////////////////////////////////////////////
            $pdf->RightHeader = true;
            $pdf->PrintObject1 = 'INVITO AL PAGAMENTO';
            $pdf->PrintObject2 = 'DI VIOLAZIONE AL CODICE DELLA STRADA';
            $pdf->PrintObject3 = 'IN FORMA AGEVOLATA';
            $page_format = array('Rotate'=>45);
            $pdf->SetMargins(10,8,10);
            $pdf->AddPage('P', $page_format);
            //Se provvisorio evidenzia il testo in giallo
            $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
            $pdf->SetTextColor(0, 0, 0);
            
            $pdf->Image($_SESSION['blazon'], 7, 8, 12, 17);
            $ManagerName = $r_Customer['ManagerName'];
            $pdf->customer = $ManagerName;
            
            $pdf->SetFont($font, '', 8, '', true);
            
            $pdf->writeHTMLCell(68, 0, 24, 9, '<strong>'.$r_Customer['ManagerName'].'</strong>', 0, 0, 1, true, 'L', true);
            $pdf->LN(3);
            $pdf->writeHTMLCell(68, 0, 24, '', $r_Customer['ManagerSector'], 0, 0, 1, true, 'L', true);
            $pdf->LN(3);
            if ($r_Customer['FineNonDeliveryAddressForeign'] != ""){
                $pdf->SetFont($font, '', 7, '', true);
                $pdf->writeHTMLCell(70, 0, 24, '', "RESTITUZIONE PIEGO IN CASO DI MANCATO RECAPITO:", 0, 0, 1, true, 'L', true);
                $pdf->LN(3);
                $pdf->writeHTMLCell(70, 0, 24, '', StringOutDB(strtoupper($r_Customer['FineNonDeliveryAddressForeign'])), 0, 0, 1, true, 'L', true);
                $pdf->SetFont($font, '', 8, '', true);
            }
            $pdf->writeHTMLCell(68, 0, 24, $r_Customer['FineNonDeliveryAddressForeign'] != "" ? 27 : '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
            $pdf->LN(3);
            $pdf->writeHTMLCell(68, 0, 24, '', $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")".(isset($r_Customer['ManagerMail']) ? ' MAIL: '.$r_Customer['ManagerMail'] : ''), 0, 0, 1, true, 'L', true);
            $pdf->LN(3);
            $pdf->writeHTMLCell(68, 0, 24, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
            
            $pdf->LN(10);
            
            //Stampa le finestre delle buste
            $window = true;
            if (!$ultimate && $window){
                $pdf->RoundedRect(2.2, 6.2, 21.1, 21.3, 3, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(24.3, 6.2, 68.1, 21.3, 3, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(112, 64.5, 90.7, 14.5, 3, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(112, 39.6, 90.7, 23.7, 3, '1111', '', array('color' => array(255,0,0)), '');
                $pdf->RoundedRect(0.1, 0.1, 209.9, 83.6, 0.5, '1111', '', array('color' => array(0,0,255)), '');
                $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            }
            
            //INTESTAZIONE TRASGRESSORE////////////////////////////////////////////////////////////////////////////////////
            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
                );
            
            $pdf->SetFont('', '', 10, '', true);
            $pdf->SetCellPadding(0);
            $pdf->setCellHeightRatio(1);
            $pdf->MultiCell(87, 0, strtoupper(StringOutDB((isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' ' . $trespasser['Name'])), 0, 'L', 1, 1, 114, 64.3, true);
            $pdf->MultiCell(87, 0, strtoupper(StringOutDB($str_TrespasserAddress != "" ? $str_TrespasserAddress : "")), 0, 'L', 1, 1, 114, '', true);
            $pdf->MultiCell(87, 0, StringOutDB($trespasser['ZIP']).' '.strtoupper(StringOutDB($trespasser['City'])).(!empty($trespasser['Province']) ? ' ('.strtoupper(StringOutDB($trespasser['Province'])).')' : ''), 0, 'L', 1, 1, 114, '', true);
            $pdf->MultiCell(87, 0, strtoupper(StringOutDB($trespasser['CountryTitle'])), 0, 'L', 1, 1, 114, '', true);
            $pdf->setCellHeightRatio(1.25);
            $pdf->SetFont('', '', 8, '', true);
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //IMPOSTA COORDINATA INIZIO TESTO DINAMICO
            $pdf->SetXY($TextStartCoord['X'], $TextStartCoord['Y']);
            ///////////////////////////////////////////////////////////////////////////////////////
            
            ///////////////////////////////////////////////////////////////////////////////////////
            
            $forms = $rs->Select('FormDynamic', "FormTypeId=" . $FormTypeId . " AND CityId='" . $_SESSION['cityid'] . "' AND LanguageId=" . $n_LanguageId. " AND NationalityId=2");
            $form = mysqli_fetch_array($forms);
            
            $Content = $form['Content'];
            
            ////////////////////////////////////////////////////////////////////////////////////////////
            //SOTTOTESTI
            ///////////////////////////////////////////////////////////////////////////////////////////
            $EmptyPregMatch = false;
            //Continua a cercare per variabili di sottotesti da sostituire finchè non trova nulla
            while(!$EmptyPregMatch){
                $a_Variables = array();
                $a_Matches = array();
                
                if(preg_match_all("/\{\{.*?\}\}/", $Content, $a_Variables) > 0){
                    $a_Matches = $a_Variables[0];
                    
                    foreach ($a_Matches as $var){
                        
                        $a_Types = array();
                        $rs_variable = $rs->Select('FormVariable',"Id='$var' AND FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId." And NationalityId=2");

                        while ($r_variable = mysqli_fetch_array($rs_variable)){
                            $a_Types[$r_variable['Type']] = StringOutDB($r_variable['Content']);
                        }
                        
                        //Sottotesto dati per tipo trasgressore
                        if ($var == "{{TrespasserType}}"){
                            $str_TrespasserType = "";
                            if ($r_Fine['TrespasserTypeId'] == 11) {
                                //Noleggio / Noleggiante(Obbligato/Trasgressore)
                                $str_TrespasserType = $a_Types[2];
                                
                                $a_FirstTrespasser = $a_TrespasserTypes['T10'];
                                $a_SecondTrespasser = $a_TrespasserTypes['T11'];
                                
                                //PRIMA FIGURA
                                $str_TrespasserType = replaceTrespasserData($str_TrespasserType, $a_FirstTrespasser, true);
                                //SECONDA FIGURA
                                $str_TrespasserType = replaceTrespasserData($str_TrespasserType, $a_SecondTrespasser);
                            } else if($chk_180 && $r_Fine['TrespasserId1_180']>0) {
                                //ART 180
                                $str_TrespasserType = $a_Types[3];
                                
                                $str_TrespasserType = replaceTrespasserData($str_TrespasserType, $a_TrespasserTypes['T1']);
                            } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16){
                                //Obbligato / Trasgressore (Proprietario + Trasgressore/conducente)
                                $str_TrespasserType = $a_Types[4];
                                
                                //di base il primo è il T2 proprietario e il secondo è T3
                                //nel caso della patria potestà
                                //T15 Patria potestà Proprietario/Obbligato è prima figura
                                //T16 Patria potestà Trasgressore è seconda figura
                                $a_FirstTrespasser = in_array('T15', $a_TrespasserTypes) ? $a_TrespasserTypes['T15'] : $a_TrespasserTypes['T2'];
                                $a_SecondTrespasser = in_array('T16', $a_TrespasserTypes) ? $a_TrespasserTypes['T16'] : $a_TrespasserTypes['T3'];
                                
                                //PRIMA FIGURA
                                $str_TrespasserType = replaceTrespasserData($str_TrespasserType, $a_FirstTrespasser);
                                //SECONDA FIGURA
                                $str_TrespasserType = replaceTrespasserData($str_TrespasserType, $a_SecondTrespasser);
                            } else {
                                //Proprietario (Proprietario/Trasgressore)
                                $str_TrespasserType = $a_Types[1];
                                
                                $str_TrespasserType = replaceTrespasserData($str_TrespasserType, $a_TrespasserTypes['T1']);
                            }
                            
                            $Content = str_replace("{{TrespasserType}}", $str_TrespasserType, $Content);
                            
                        }
                        
                        //Pagina notifiche
                        else if ($var == "{{NotificationPage}}"){
                            if($CreationType==5 && $_SESSION['cityid']!='A175' && $_SESSION['cityid']!='D925')
                                $Content = str_replace("{{NotificationPage}}", $a_Types[1], $Content);
                                else $Content = str_replace("{{NotificationPage}}", '', $Content);
                        }
                        
                        //Pagina decurtazione punti - testo
                        else if ($var == "{{LicensePointPage}}"){
                            //var_dump($FormTypeId, $chk_126Bis, $r_Fine['ReasonId'], $r_ArticleTariff['126Bis'], $r_AdditionalArticle['126Bis']);
                            if ($chk_126Bis && $FormTypeId!=101 && $r_Fine['ReasonId']!=100){
                                $Content = str_replace("{{LicensePointPage}}", $a_Types[1], $Content);
                            }
                            else $Content = str_replace("{{LicensePointPage}}", '', $Content);
                        }
                        //Pagina decurtazione punti - trasgressore
                        else if ($var == "{{LicensePointPageHead}}"){
                            if ($chk_126Bis && $FormTypeId!=101 && $r_Fine['ReasonId']!=100){
                                $str_LicensePointPageTresp = "";
                                if ($trespasser['Genre'] != 'D'){
                                    $str_LicensePointPageTresp = $a_Types[1];
                                    $str_LicensePointPageTresp = str_replace("{TrespasserName}", StringOutDB($trespasser['Name'] . ' ' . $trespasser['Surname']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserAddress}", StringOutDB($trespasser['Address']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $str_LicensePointPageTresp);
                                }
                                else {
                                    $str_LicensePointPageTresp = $a_Types[2];
                                    $str_LicensePointPageTresp = str_replace("{TrespasserName}", StringOutDB($trespasser['CompanyName']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserAddress}", StringOutDB($trespasser['Address']), $str_LicensePointPageTresp);
                                }
                                $Content = str_replace("{{LicensePointPageHead}}", $str_LicensePointPageTresp, $Content);
                            } else $Content = str_replace("{{LicensePointPageHead}}", "", $Content);
                        }
                        
                        //Rilevatore
                        else if ($var == "{{DetectorText}}"){
                            if (isset($a_DetectorTypeId[$r_Fine['DetectorId']])){
                                //Velocità
                                if ($a_DetectorTypeId[$r_Fine['DetectorId']] == 1)
                                    $Content = str_replace("{{DetectorText}}", $a_Types[1], $Content);
                                    //Semaforo
                                    else if ($a_DetectorTypeId[$r_Fine['DetectorId']] == 2)
                                        $Content = str_replace("{{DetectorText}}", $a_Types[2], $Content);
                                        //Non gestito
                                        else $Content = str_replace("{{DetectorText}}", '', $Content);
                            } else $Content = str_replace("{{DetectorText}}", '', $Content);
                        }
                        
                        //Mancata contestazione
                        else if ($var == "{{ReasonText}}"){
                            $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescriptionIta'])) > 0) ? $r_FineOwner['ReasonDescriptionIta'] : $r_Fine['ReasonTitleIta'];
                            if ($CreationType==5)
                                $Content = str_replace("{{ReasonText}}", $a_Types[2], $Content);
                                else if ($str_ReasonDescription != "")
                                    $Content = str_replace("{{ReasonText}}", $a_Types[1], $Content);
                                    else $Content = str_replace("{{ReasonText}}", '', $Content);
                        }
                        
                        else $Content = str_replace($var, $a_Types[1], $Content);
                        
                    }
                } else $EmptyPregMatch = true;
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            /////////////////////////////////////////////
            //VARIABILI
            /////////////////////////////////////////////
            
            $Content = str_replace("{SendType}", $a_FineSendType[1], $Content);
            
            //Trespasser/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $rs_trespasser = $rs->Select('Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $r_trespasser = mysqli_fetch_array($rs_trespasser);
            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
                );
            
            $TrespasserTaxCode = PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode']);
            $TrespasserName = StringOutDB((isset($r_trespasser['CompanyName']) ? $r_trespasser['CompanyName'].' ' : '') . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name']);
            
            $Content = str_replace("{TrespasserName}", StringOutDB((isset($r_trespasser['CompanyName']) ? $r_trespasser['CompanyName'].' ' : '') . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name']), $Content);
            $Content = str_replace("{TrespasserGenre}", $a_GenreLetter[$trespasser['Genre']], $Content);
            $Content = str_replace("{TrespasserCity}", StringOutDB($r_trespasser['City']), $Content);
            $Content = str_replace("{TrespasserProvince}", StringOutDB($r_trespasser['Province']), $Content);
            $Content = str_replace("{TrespasserAddress}", StringOutDB($str_TrespasserAddress), $Content);
            $Content = str_replace("{TrespasserZip}", StringOutDB($r_trespasser['ZIP']), $Content);
            $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
            $Content = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $Content);
            $Content = str_replace("{DateRent}", $r_Fine['ReceiveDate'] ? '<br />(Identificazione dati avvenuta in data: '.DateOutDB($r_Fine['ReceiveDate']).')' : '', $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Article/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $str_ArticleDescription = "";
            $str_AdditionalSanction = "";
            $Paragraph = ($r_Fine['Paragraph'] == "0") ? "" : $r_Fine['Paragraph'] . " ";
            $Letter = ($r_Fine['Letter'] == "0") ? "" : $r_Fine['Letter'];
            $str_ArticleId = $r_Fine['Article'] . "/" . $Paragraph . $Letter;
            
            if (isset($r_FineOwner['AdditionalDescriptionIta'])
                && strlen(trim($r_FineOwner['AdditionalDescriptionIta'])) > 0){
                    $str_AdditionalSanction = "SANZIONE ACCESSORIA: ".StringOutDB($r_FineOwner['AdditionalDescriptionIta']);
            } else {
                if (($r_ArticleTariff['UseAdditionalSanction'] != ADDITIONAL_SANCTION_NOT_EXPECTED) && ($r_ArticleTariff['AdditionalSanctionId'] > 0)) {
                    $rs_AdditionalSanction = $rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']);
                    $r_AdditionalSanction = mysqli_fetch_array($rs_AdditionalSanction);
                    $str_AdditionalSanction = "SANZIONE ACCESSORIA: ".StringOutDB($r_AdditionalSanction['TitleIta']);
                } else {
                    $str_AdditionalSanction = "SANZIONI ACCESSORIE: non previste";
                }
            }
            
            $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
            
            $str_ArticleDescription = str_replace("{Speed}", intval($r_Fine['Speed']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedExcess}", intval(intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit'])), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_ArticleDescription);
            
            $str_ExpirationDate = (isset($r_Fine['ExpirationDate']) || $r_Fine['ExpirationDate']!="") ? DateOutDB($r_Fine['ExpirationDate']) : "";
            $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescriptionIta'])) > 0) ? $r_FineOwner['ReasonDescriptionIta'] : $r_Fine['ReasonTitleIta'];
            $str_ArticleDescription = $str_ArticleDescription;
            
            $Content = str_replace("{ReasonId}", StringOutDB($str_ReasonDescription), $Content);
            $Content = str_replace("{ArticleId}", $str_ArticleId .$str_ListArticle, $Content);
            $Content = str_replace("{ArticleDescription}", StringOutDB($str_ArticleDescription), $Content);
            $Content = str_replace("{ArticleAdditionalNight}", $str_AdditionalNight, $Content);
            $Content = str_replace("{ArticleAdditionalMass}", $str_AdditionalMass, $Content);
            $Content = str_replace("{AdditionalSanctionId}", $str_AdditionalSanction, $Content);
            $Content = str_replace("{ExpirationDate}", $str_ExpirationDate, $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Detector/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $SpeedExcess = 0;
            if ($r_Fine['DetectorId'] > 0) {
                
                $rs_DetectorRatification = $rs->Select('DetectorRatification', "DetectorId={$r_Fine['DetectorId']} AND ((FromDate <= '{$r_Fine['FineDate']}' AND (ToDate >= '{$r_Fine['FineDate']}' OR ToDate IS NULL)) OR (FromDate IS NULL AND ToDate IS NULL))");
                $r_DetectorRatification = $rs->getArrayLine($rs_DetectorRatification);
                
                if (isset($a_SpeedLengthAverage[$r_Fine['DetectorId']])){
                    $SpeedLengthAverage = $a_SpeedLengthAverage[$r_Fine['DetectorId']];
                    $SpeedTimeAverage = $r_Fine['SpeedTimeAverage'] > 0 ? $r_Fine['SpeedTimeAverage'] : ($SpeedLengthAverage * 3.6) / $r_Fine['SpeedControl'];
                    $SpeedExcess = intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit']);
                } else {
                    $SpeedLengthAverage = $SpeedTimeAverage = $SpeedExcess = 0;
                }
                
                
                $Content = str_replace("{Speed}", intval($r_Fine['Speed']), $Content);
                $Content = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $Content);
                $Content = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $Content);
                $Content = str_replace("{SpeedExcess}", intval($SpeedExcess), $Content);
                $Content = str_replace("{TimeTypeId}", StringOutDB($r_Fine['TimeDescriptionIta']), $Content);
                
                $Content = str_replace("{DetectorPosition}", $a_DetectorPosition[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorKind}", $a_DetectorKind[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorCode}", $a_DetectorCode[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorNumber}", $a_DetectorNumber[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorTolerance}", intval($a_DetectorTolerance[$r_Fine['DetectorId']]), $Content);
                $Content = str_replace("{DetectorRatification}", $a_DetectorRatification[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorAdditionalText}", $a_DetectorAdditionalTextIta[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorSpeedLengthAverage}", $SpeedLengthAverage, $Content);
                $Content = str_replace("{DetectorSpeedTimeAverage}", number_format($SpeedTimeAverage, 3, ',', '.'), $Content);
                $Content = str_replace("{DetectorSpeedTimeHourAverage}", number_format($SpeedTimeAverage/3600, 3, ',', '.'), $Content);
                $Content = str_replace("{CalibrationText}", StringOutDB($r_DetectorRatification['Ratification']), $Content);
                $Content = str_replace("{CalibrationFromDate}", DateOutDB($r_DetectorRatification['FromDate']), $Content);
                $Content = str_replace("{CalibrationToDate}", DateOutDB($r_DetectorRatification['ToDate']), $Content);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //Fine/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($r_ArticleTariff['ReducedPayment']) {
                $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
                
            } else {
                $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
            }
            
            $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
            $Content = str_replace("{FineTime}", ($r_Fine['FineTime'] != "" || $r_Fine['FineTime'] || null) ? TimeOutDB($r_Fine['FineTime']) : "", $Content);
            $Content = str_replace("{VehicleTypeId}", StringOutDB($r_Fine['VehicleTitleIta']), $Content);
            $Content = str_replace("{VehiclePlate}", StringOutDB($r_Fine['VehiclePlate']), $Content);
            $Content = str_replace("{VehicleBrand}", StringOutDB($r_Fine['VehicleBrand']), $Content);
            $Content = str_replace("{VehicleModel}", StringOutDB($r_Fine['VehicleModel']), $Content);
            $Content = str_replace("{VehicleColor}", StringOutDB($r_Fine['VehicleColor']), $Content);
            $Content = str_replace("{IuvCode}", StringOutDB($r_Fine['IuvCode']), $Content);
            $Content = str_replace("{Code}", $r_Fine['Code'], $Content);
            
            $QRCode1 = false;
            $QRCode2 = false;
            $QRCodeURL1 = false;
            $QRCodeURL2 = false;
            if (strpos($Content, '{QRCode1}') !== false){
                $QRCode1 = true;
                $Content = str_replace("{QRCode1}", '', $Content);
            }
            if (strpos($Content, '{QRCode2}') !== false){
                $QRCode2 = true;
                $Content = str_replace("{QRCode2}", '', $Content);
            }
            if (strpos($Content, '{QRCodeURL1}') !== false){
                $QRCodeURL1 = true;
                $Content = str_replace("{QRCodeURL1}", '', $Content);
            }
            if (strpos($Content, '{QRCodeURL2}') !== false){
                $QRCodeURL2 = true;
                $Content = str_replace("{QRCodeURL2}", '', $Content);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Controller////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $str_ControllerName = trim(StringOutDB($r_Fine['ControllerQualification']." ".$r_Fine['ControllerName']));
            $str_ControllerCode = trim($r_Fine['ControllerCode']);
            
            $rs_FineAdditionalController = $rs->Select('V_AdditionalController', "FineId=" . $FineId);
            while ($r_FineAdditionalController = mysqli_fetch_array($rs_FineAdditionalController)){
                $str_ControllerCode="";
                $str_ControllerName .= ", ".trim(StringOutDB($r_FineAdditionalController['ControllerQualification']." ".$r_FineAdditionalController['ControllerName']));
            }
            
            $str_ControllerDate = DateOutDB($r_Fine['ControllerDate']);
            $str_ControllerTime = ($r_Fine['ControllerTime'] != "" || $r_Fine['ControllerTime'] || null) ? TimeOutDB($r_Fine['ControllerTime']) : "";
            $str_ControllerConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$str_ControllerName. " Matr. ". $str_ControllerCode;
            $str_ChiefConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime;//." - ".$ChiefControllerName;
            
            $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
            $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);
            $Content = str_replace("{ControllerDate}", $str_ControllerDate, $Content);
            $Content = str_replace("{ControllerTime}", $str_ControllerTime, $Content);
            $Content = str_replace("{ChiefConvalidation}", $str_ChiefConvalidation, $Content);
            $Content = str_replace("{ControllerConvalidation}", $str_ControllerConvalidation, $Content);
            //$Content = str_replace("{ChiefControllerName}", $ChiefControllerName, $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            /////////////////////////////////////////////
            //126 BIS
            /////////////////////////////////////////////
            $str_ArticleAdditionalText = "";
            $article126bis = null;
            if ($chk_126Bis) {
                $articles126bis = $rs->SelectQuery("Select A.AdditionalTextIta, ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId WHERE A.CityId='" . $_SESSION['cityid'] . "' AND A.Article=126 AND A.Letter='bis' AND ART.Year = " . $_SESSION['year']);
                $article126bis = mysqli_fetch_array($articles126bis);
                
                $str_ArticleAdditionalText = ($r_Fine['ArticleNumber'] > 1) ? $article126bis['AdditionalTextIta'] : $r_Fine['ArticleAdditionalTextIta'];
            } else {
                if ($r_Fine['Article']==126 ){
                    $str_ArticleAdditionalText = "";
                } else{
                    $str_ArticleAdditionalText = $r_Fine['ArticleAdditionalTextIta'];
                }
            }
            
            $str_Date = "";
            if ($r_Customer['ManagerSignName'] == "") {
                $str_Date = $r_Fine['CityTitle'] . " " . DateOutDB($NotificationDate);
            } else {
                $str_Date =  $r_Customer['ManagerSignName'] . " " . DateOutDB($NotificationDate);
            }
            
            $PartialFee = $n_TotPartialFee;
            $MaxFee = $n_TotMaxFee * FINE_MAX;
            
            $TotalPartialFee = $PartialFee + $AdditionalPartialFee;
            
            $TotalFee = $n_TotFee + $AdditionalFee;
            
            $TotalMaxFee = $MaxFee + $AdditionalFee;
            
            $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType1'];
            $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType2'];
            $str_ProtocolLetter = ($RuleTypeId == 1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
            
            $Content = str_replace("{ArticleAdditionalText}", StringOutDB($str_ArticleAdditionalText), $Content);
            
            $Content = str_replace("{DecurtationPoints}", $n_LicensePoint, $Content);
            $Content = str_replace("{Fee126bis}", (!empty($article126bis) ? NumberDisplay($article126bis['Fee']) :  NumberDisplay(0)), $Content);
            $Content = str_replace("{MaxFee126bis}", (!empty($article126bis) ? NumberDisplay($article126bis['MaxFee']) :  NumberDisplay(0)), $Content);
            
            $Content = str_replace("{PreviousProtocolId}", $PreviousProtocolId, $Content);
            $Content = str_replace("{PreviousProtocolYear}", $PreviousProtocolYear, $Content);
            $Content = str_replace("{PreviousFineDate}", $PreviousFineDate, $Content);
            
            $Content = str_replace("{Locality}", StringOutDB($r_Fine['CityTitle']), $Content);
            $Content = str_replace("{Address}", StringOutDB($r_Fine['Address']), $Content);
            
            $Content = str_replace("{Gps}", (trim($r_Fine['GpsLat'])=="") ? "" : "( ".$r_Fine['GpsLat'].", ".$r_Fine['GpsLong']. " )", $Content);
            
            $Content = str_replace("{BankOwner}",StringOutDB($r_Customer['ForeignBankOwner']),$Content);
            $Content = str_replace("{BankName}",StringOutDB($r_Customer['ForeignBankName']),$Content);
            $Content = str_replace("{BankAccount}",$r_Customer['ForeignBankAccount'],$Content);
            $Content = str_replace("{BankSwift}",$r_Customer['ForeignBankSwift'],$Content);
            $Content = str_replace("{BankIban}",$r_Customer['ForeignBankIban'],$Content);
            
            $Content = str_replace("{CurrentDate}", DateOutDB($NotificationDate), $Content);
            $Content = str_replace("{CurrentTime}", date("H:i"), $Content);
            $Content = str_replace("{Date}", $str_Date, $Content);
            
            $Content = str_replace("{ManagerDataEntryName}", StringOutDB($r_Customer['ManagerDataEntryName']), $Content);
            $Content = str_replace("{ManagerProcessName}", StringOutDB($r_Customer['ManagerProcessName']), $Content);
            
            $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
            $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
            $Content = str_replace("{ManagerAdditionalName}", StringOutDB($r_Customer['ManagerAdditionalName']), $Content);
            $Content = str_replace("{ManagerSector}", StringOutDB($r_Customer['ManagerSector']), $Content);
            $Content = str_replace("{ManagerAddress}", StringOutDB($r_Customer['ManagerAddress']), $Content);
            $Content = str_replace("{ManagerZIP}", $r_Customer['ManagerZIP'], $Content);
            $Content = str_replace("{ManagerCity}", StringOutDB($r_Customer['ManagerCity']), $Content);
            $Content = str_replace("{ManagerProvince}", StringOutDB($r_Customer['ManagerProvince']), $Content);
            $Content = str_replace("{ManagerPhone}", $r_Customer['ManagerPhone'] ? 'TEL: '.$r_Customer['ManagerPhone'] : '', $Content);
            $Content = str_replace("{ManagerFax}", $r_Customer['ManagerFax'] ? 'FAX: '.$r_Customer['ManagerFax'] : '', $Content);
            $Content = str_replace("{ManagerMail}", $r_Customer['ManagerMail'] ? 'MAIL: '.$r_Customer['ManagerMail'] : '', $Content);
            $Content = str_replace("{ManagerPEC}", $r_Customer['ManagerPEC'], $Content);
            $Content = str_replace("{ManagerWeb}", $r_Customer['ManagerWeb'], $Content);
            $Content = str_replace("{ManagerTaxCode}", $r_Customer['ManagerTaxCode'], $Content);
            //Località firma verbale
            $Content = str_replace("{ManagerSignName}", $r_Customer['ManagerSignName'], $Content);
            
            $Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);
            
            $Content = str_replace("{PaymentDays}", $r_Customer['PaymentDaysForeign'], $Content);
            
            $Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
            $Content = str_replace("{TotalPartialFee}", NumberDisplay($TotalPartialFee), $Content);
            
            $Content = str_replace("{Fee}", NumberDisplay($n_TotFee), $Content);
            $Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);
            
            $Content = str_replace("{ResearchFee}", NumberDisplay($ResearchFee), $Content);
            $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);
            $Content = str_replace("{ChargeTotalFee}", NumberDisplay($ChargeTotalFee), $Content);
            $Content = str_replace("{KindFee}", NumberDisplay($KindFee), $Content);
            
            $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
            $Content = str_replace("{TotalMaxFee}", NumberDisplay($TotalMaxFee), $Content);
            
            $Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content);
            $Content = str_replace("{AdditionalPartialFee}", NumberDisplay($AdditionalPartialFee), $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Judicial Office////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $Content = str_replace("{Judge}", isset($a_Office[1]) ? $a_Office[1]['OfficeTitleIta'] : "", $Content);
            $Content = str_replace("{JudgeCity}", isset($a_Office[1]) ? $a_Office[1]['City'] : "" , $Content);
            $Content = str_replace("{JudgeProvince}", isset($a_Office[1]) ? $a_Office[1]['Province'] : "" , $Content);
            $Content = str_replace("{JudgeAddress}", isset($a_Office[1]) ? $a_Office[1]['Address'] : "" , $Content);
            $Content = str_replace("{JudgeZIP}", isset($a_Office[1]) ? $a_Office[1]['ZIP'] : "" , $Content);
            $Content = str_replace("{JudgePhone}", isset($a_Office[1]) ? $a_Office[1]['Phone'] : "" , $Content);
            $Content = str_replace("{JudgeFax}", isset($a_Office[1]) ? $a_Office[1]['Fax'] : "" , $Content);
            $Content = str_replace("{JudgeMail}", isset($a_Office[1]) ? $a_Office[1]['Mail'] : "" , $Content);
            $Content = str_replace("{JudgePEC}", isset($a_Office[1]) ? $a_Office[1]['PEC'] : "" , $Content);
            $Content = str_replace("{JudgeWeb}", isset($a_Office[1]) ? $a_Office[1]['Web'] : "" , $Content);
            
            $Content = str_replace("{Prefect}", isset($a_Office[2]) ? $a_Office[2]['OfficeTitleIta'] : "" , $Content);
            $Content = str_replace("{PrefectCity}", isset($a_Office[2]) ? $a_Office[2]['City'] : "" , $Content);
            $Content = str_replace("{PrefectProvince}", isset($a_Office[2]) ? $a_Office[2]['Province'] : "" , $Content);
            $Content = str_replace("{PrefectAddress}", isset($a_Office[2]) ? $a_Office[2]['Address'] : "" , $Content);
            $Content = str_replace("{PrefectZIP}", isset($a_Office[2]) ? $a_Office[2]['ZIP'] : "" , $Content);
            $Content = str_replace("{PrefectPhone}", isset($a_Office[2]) ? $a_Office[2]['Phone'] : "" , $Content);
            $Content = str_replace("{PrefectFax}", isset($a_Office[2]) ? $a_Office[2]['Fax'] : "" , $Content);
            $Content = str_replace("{PrefectMail}", isset($a_Office[2]) ? $a_Office[2]['Mail'] : "" , $Content);
            $Content = str_replace("{PrefectPEC}", isset($a_Office[2]) ? $a_Office[2]['PEC'] : "" , $Content);
            $Content = str_replace("{PrefectWeb}", isset($a_Office[2]) ? $a_Office[2]['Web'] : "" , $Content);
            
            $Content = str_replace("{Court}", isset($a_Office[3]) ? $a_Office[3]['OfficeTitleIta'] : "" , $Content);
            $Content = str_replace("{CourtCity}", isset($a_Office[3]) ? $a_Office[3]['City'] : "" , $Content);
            $Content = str_replace("{CourtProvince}", isset($a_Office[3]) ? $a_Office[3]['Province'] : "" , $Content);
            $Content = str_replace("{CourtAddress}", isset($a_Office[3]) ? $a_Office[3]['Address'] : "" , $Content);
            $Content = str_replace("{CourtZIP}", isset($a_Office[3]) ? $a_Office[3]['ZIP'] : "" , $Content);
            $Content = str_replace("{CourtPhone}", isset($a_Office[3]) ? $a_Office[3]['Phone'] : "" , $Content);
            $Content = str_replace("{CourtFax}", isset($a_Office[3]) ? $a_Office[3]['Fax'] : "" , $Content);
            $Content = str_replace("{CourtMail}", isset($a_Office[3]) ? $a_Office[3]['Mail'] : "" , $Content);
            $Content = str_replace("{CourtPEC}", isset($a_Office[3]) ? $a_Office[3]['PEC'] : "" , $Content);
            $Content = str_replace("{CourtWeb}", isset($a_Office[3]) ? $a_Office[3]['Web'] : "" , $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            
            //Protocol///////////////////////////////////////////////////////////////////////////
            if ($ultimate) {
                //Se il cronologico è presente viene usato quello
                if($r_Fine['ProtocolId'] > 0) $ProtocolNumber = $r_Fine['ProtocolId'];
                //Se non è presente e quello precedente neppure, viene calcolato
                else if ($r_Fine['ProtocolIdAssigned'] == 0) {
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                    $ProtocolNumber = $n_Protocol + 1;
                } 
                //Se è presente quello precedente, viene usato quest'ultimo
                else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];
                
                $Content = str_replace("{ProtocolId}", $ProtocolNumber, $Content);
                
            } else {
                if ($ProtocolNumber == 0) {
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                    $ProtocolNumber = $n_Protocol;
                }
                $ProtocolNumber++;
                $Content = str_replace("{ProtocolId}", $ProtocolNumber . " - PROVV", $Content);
            }
            ///////////////////////////////////////////////////////////////////////////////////////
            
            //PAGOPA//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $PagoPACode1 = $r_Fine['PagoPA1'];
            $PagoPACode2 = $r_Fine['PagoPA2'];
            //se è abilitato pagoPA interroghiamo i servizi e salviamo gli importi
            if($b_PagoPAEnabled){
                //FIXME trovare una soluzione per fare in modo di dividere gli importi per ogni articolo
                //dentro a getFineFees quando si gestiranno più capitoli di bilancio e creare la struttura dati secondo calcolaImporti
                $a_Importi = array(
                    'Amounts' => array(
                        array(
                            'ViolationTypeId' => $ViolationTypeId
                        )
                    ),
                    'Sum' => array(
                        'ReducedPartial'=>number_format((float)$TotalPartialFee, 2, '.', ''),
                        'ReducedTotal'=>number_format((float)$TotalFee, 2, '.', ''),
                        'Partial'=>number_format((float)$TotalFee, 2, '.', ''),
                        'Total'=>number_format((float)$TotalMaxFee, 2, '.', ''),
                    )
                );
                
                $cls_iuv = new cls_iuv();
                
//         	    $tipoContabilità = '9';
//         	    $codiceContabilità = '000';
//         	    $tipoDovuto = 'ALTRO';
        	    
        	    //$GenreParemeter D è per ditta/impresa e P per privato
        	    $GenreParemeter = ($trespasser['Genre'] == "D")? "D" : "P";
        	    //Kind='S' è tipologia sanzione
        	    $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
        	    $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
        	    
//         	    if(mysqli_num_rows($rs_PagoPAServiceParameter)>0) {
//         	        $r_PagoPAServiceParameter = mysqli_fetch_array($rs_PagoPAServiceParameter);
//         	        $tipoContabilità = $r_PagoPAServiceParameter['Type'];
//         	        $codiceContabilità = $r_PagoPAServiceParameter['Code'];
//         	        $tipoDovuto = $r_PagoPAServiceParameter['TypeDue'];
//         	    }
        	    
        	    $TrespasserType = ($trespasser['Genre'] == "D") ? "G" : "F";
        	    $FineText = 'Anno ' . $r_Fine['ProtocolYear'] . ' targa ' . $r_Fine['VehiclePlate'];

        	    if ($ultimate && !in_array($FineId, $a_FailedPagoPA)){
        	        if($chk_ReducedPayment){
        	            $fullFee = 'ReducedTotal'; //sanizione minima
        	            $partialFee = 'ReducedPartial'; //sanzione ridotta
        	        }else{
        	            $fullFee = 'Total'; //metà del massimo
        	            $partialFee = 'Partial'; //sanzione minima
        	        }
        	        
        	        $b_PagoPA1ChangedAmount = $a_Importi['Sum']['ReducedPartial'] != $r_Fine['PagoPAReducedPartial'] || $a_Importi['Sum']['Partial'] != $r_Fine['PagoPAPartial'];
        	        $b_PagoPA2ChangedAmount = $a_Importi['Sum']['ReducedTotal'] != $r_Fine['PagoPAReducedTotal'] || $a_Importi['Sum']['Total'] != $r_Fine['PagoPATotal'];
        	        $b_PagoPAFail1 = $b_PagoPAFail2 = false;
        	        $aPagoPAUpdate = array();
        	        
        	        if(empty($PagoPACode1)){
        	            if(!empty(trim($r_Fine['VehiclePlate']))){
        	                $PagoPACode1 = callPagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $partialFee, $r_Customer, $FineId, $r_Fine['FineDate'], $TrespasserType, $trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams);
        	                if(!empty($PagoPACode1)){
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1);
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedPartial'] ,'settype'=>'flt');
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedTotal'] ,'settype'=>'flt');
        	                } else $b_PagoPAFail1 = true;
        	            }
        	        } else {
        	            if ($b_PagoPA1ChangedAmount){
        	                if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $partialFee, $PagoPACode1, $FineId, $r_Fine['FineDate'], $TrespasserType, $trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedPartial'] ,'settype'=>'flt');
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedTotal'] ,'settype'=>'flt');
        	                } else $b_PagoPAFail1 = true;
        	            } else {
        	                trigger_error('ID '.$FineId.': IUV1 importi uguali. Aggiornamento sanzioni associate allo IUV non invocato.', E_USER_NOTICE);
        	            }
        	        }
        	        
        	        if(empty($PagoPACode2)){
        	            if(!empty(trim($r_Fine['VehiclePlate']))){
        	                $PagoPACode2 = callPagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $fullFee, $r_Customer, $FineId, $r_Fine['FineDate'], $TrespasserType, $trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams);
        	                if(!empty($PagoPACode2)){
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2);
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Partial'] ,'settype'=>'flt');
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
        	                } else $b_PagoPAFail2 = true;
        	            }
        	        } else {
        	            if ($b_PagoPA2ChangedAmount){
        	                if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $fullFee, $PagoPACode2, $FineId, $r_Fine['FineDate'], $TrespasserType, $trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Partial'] ,'settype'=>'flt');
        	                    $aPagoPAUpdate[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
        	                } else $b_PagoPAFail2 = true;
        	            } else {
        	                trigger_error('ID '.$FineId.': IUV2 importi uguali. Aggiornamento sanzioni associate allo IUV non invocato.', E_USER_NOTICE);
        	            }
        	        }
        	        
        	        if(!empty($aPagoPAUpdate)){
        	            $rs->Update('Fine', $aPagoPAUpdate, "Id=" . $FineId);
        	        }
        	        if($b_PagoPAFail1 || $b_PagoPAFail2){
        	            trigger_error('ID '.$FineId.': Chiamata PagoPA fallita.', E_USER_WARNING);
        	            $str_Warning .= 'ID '.$FineId.': Chiamata PagoPA fallita, il verbale non è stato elaborato.<br>';
        	            $a_FailedPagoPA[] = $FineId;
        	        }
        	    }
        	    
        	    $PagoPAPaymentNotice1 = $PagoPAPaymentNotice2 = '';
        	    
        	    if (!empty($PagoPACode1)){
        	        //Se l'ente prevede di usare codici avviso invece che IUV, usa direttamente quello, altrimenti tenta di costruirlo
        	        //Se fallisce a costruirlo non processa l'atto e restituisce un avviso
        	        if($r_Customer['IsIuvCodiceAvviso'] != 1){
        	            try {
        	                $PagoPAPaymentNotice1 = $cls_iuv->generateNoticeCode($PagoPACode1, $r_Customer['PagoPAAuxCode'], $r_Customer['PagoPAApplicationCode']);
        	            } catch (Exception $e) {
        	                if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
        	                trigger_error('ID '.$FineId.': Errore nella costruzione del codice avviso PagoPA1: '.$e, E_USER_WARNING);
        	                $str_Warning .= 'ID '.$FineId.': Errore nella costruzione del codice avviso PagoPA1, l\'atto non verrà processato. Verificare il codice IUV e le configurazioni.<br>';
        	                $PagoPAPaymentNotice1 = '';
        	            }
        	        } else $PagoPAPaymentNotice1 = $PagoPACode1;
        	        
        	        $Content = str_replace("{PagoPA1}", $PagoPACode1. " per ".$str_PaymentDay1, $Content);
        	        $Content = str_replace("{PagoPA1PaymentNotice}", $PagoPAPaymentNotice1, $Content);
        	    } else {
        	        $Content = str_replace("{PagoPA1}", 'XXXXXXXXX', $Content);
        	        $Content = str_replace("{PagoPA1PaymentNotice}", 'XXXXXXXXX', $Content);
        	    }
        	    
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
        	        
        	        $Content = str_replace("{PagoPA2}", $PagoPACode2. " per ".$str_PaymentDay2, $Content);
        	        $Content = str_replace("{PagoPA2PaymentNotice}", $PagoPAPaymentNotice2, $Content);
        	    } else {
        	        $Content = str_replace("{PagoPA2}", 'XXXXXXXXX', $Content);
        	        $Content = str_replace("{PagoPA2PaymentNotice}", 'XXXXXXXXX', $Content);
        	    }
        	}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                            
            $pdf->SetAutoPageBreak(true, 0);
            $pdf->SetPrintHeader(false);
            
            //#writeHTML(html, ln = true, fill = 0, reseth = false, cell = false, align = '')
            $pdf->writeHTML($Content, true, $ultimate ? 0 : 1, true, false, '');
            
            //Conta le pagine attuali, se sono dispari aggiunge una pagina bianca in fondo
            $PageNo= $pdf->PageNo();
            if($PageNo%2 == 1){
                $pdf->AddPage('P', $page_format);
            }
            
            $TotalPages = $pdf->PageNo();
            
//Nome del file//////////////////////////////
            $RndCode = "";
            for ($i = 0; $i < 5; $i++) {
                $n = rand(1, 24);
                $RndCode .= substr($strCode, $n, 1);
                $n = rand(0, 9);
                $RndCode .= $n;
            }
            
            $strProtocolNumber = "";
            for ($k = strlen((string)$ProtocolNumber); $k < 9; $k++) {
                $strProtocolNumber .= "0";
            }
            $strProtocolNumber .= $ProtocolNumber;
            $Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_" . $RndCode . ".pdf";
            $a_Documentation[$FineId][] = $Documentation;
/////////////////////////////////////////////
            
//////////QRCODE O AVVISO DI PAGAMENTO////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $Bollettino1Fee = $chk_ReducedPayment ? $TotalPartialFee : $TotalFee;
            $Bollettino2Fee = $chk_ReducedPayment ? $TotalFee : $TotalMaxFee;
            
            //Se l'ente è abilitato alla stampa degli avvisi di pagamento stampa quelli, altrimenti aggiunge i qrcode alla vecchia maniera
            if($r_Customer['PagoPAPaymentNoticeForeign'] > 0 && $b_PagoPAEnabled){
                if ($chk_ReducedPayment) {
                    $descImporto1 = 'entro 5 giorni';
                    $descImporto2 = 'dal 6º al 60º giorno';
                    $nomeImporto1 = 'Importo scontato del 30%';
                    $nomeImporto2 = 'Importo ridotto';
                    
                } else {
                    $descImporto1 = 'entro 60 giorni';
                    $descImporto2 = 'dal 61º al 180º giorno';
                    $nomeImporto1 = 'Importo ridotto';
                    $nomeImporto2 = 'Importo maggiorato';
                }
                $oggettoAvviso = 'Verbale C.D.S Prot. '.$ProtocolNumber.(!$ultimate ? ' - PROVV' : '').'/'.$ProtocolYear.'/'.$str_ProtocolLetter;
                $causaleBollettino = 'Cron ' . $ProtocolNumber . '/' . $ProtocolYear . '/' . $str_ProtocolLetter . ' targa ' . $r_Fine['VehiclePlate'] . ' ' . $r_Fine['Code'] . ' DEL ' . DateOutDB($r_Fine['FineDate']);
                $b_ErroreAvviso = false;
                
                try{
                    $o_Avviso = new Avviso($oggettoAvviso, $_SESSION['blazon']);
                    $o_Ente = new Ente($r_Customer['ManagerName'], $r_Customer['ManagerSector'], buildManagerInfo($r_Customer), trim($r_Customer['ManagerTaxCode']), $r_Customer['PagoPACBILL']);
                    $o_Destinatario = new Destinatario($TrespasserTaxCode, $TrespasserName, $str_TrespasserAddress);
                    $o_Importo1 = new Importo($Bollettino1Fee, $PagoPAPaymentNotice1, $nomeImporto1, $descImporto1);
                    $o_Importo2 = new Importo($Bollettino2Fee, $PagoPAPaymentNotice2, $nomeImporto2, $descImporto2);
                    
                    $avviso = new ModelloCDS($o_Avviso, $o_Ente, $o_Destinatario, $o_Importo1, $o_Importo2);
                    if ($b_PrintBillPagoPA){
                        $avviso->setBollettino(new Bollettino($str_PostalAuthorizationPagoPA, $r_Customer['ForeignBankAccount'], $r_Customer['ForeignBankOwner'], $r_Customer['ManagerTaxCode'], $causaleBollettino));
                    }
                    $avviso->costruisci(true);
                    
                    if (!$ultimate){
                        applyTemporaryLabel($avviso);
                    }
                    
                    $avviso->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . 'avviso_'.$Documentation , "F");
                } catch (Error $e){
                    if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
                    trigger_error('ID '.$FineId.': Errore nella costruzione dell\'avviso di pagamento PagoPA: '.$e, E_USER_WARNING);
                    $str_Warning .= 'ID '.$FineId.': Errore nella costruzione dell\'avviso di pagamento PagoPA, l\'atto non verrà processato. Verificare i dati e contattare l\'amministrazione di sistema.<br>';
                    $b_ErroreAvviso = true;
                }
            } else {
                $PagoPACode1Full = AvvisoBase::buildQRCode($PagoPAPaymentNotice1, trim($r_Customer['ManagerTaxCode']), $Bollettino1Fee);
                $PagoPACode2Full = AvvisoBase::buildQRCode($PagoPAPaymentNotice2, trim($r_Customer['ManagerTaxCode']), $Bollettino2Fee);
                
                //Muove il puntatore alla seconda pagina per stampare il qrcode
                $CurrentPage = $pdf->PageNo();
                $pdf->setPage(2, true);
                $pdf->SetXY(0, 0);
                
                //QRCODE DIRETTO
                if ($PagoPACode1!='' && $PagoPACode2!='' && $QRCode1 && $QRCode2){
                    $pdf->write2DBarcode($PagoPACode1Full, 'QRCODE,M', 40, 240, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                    $pdf->writeHTMLCell(70, 0, 20, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                    
                    $pdf->write2DBarcode($PagoPACode2Full, 'QRCODE,M', 140, 240, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                    $pdf->writeHTMLCell(70, 0, 120, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
                } else {
                    if ($PagoPACode1!='' && $QRCode1){
                        $pdf->write2DBarcode($PagoPACode1Full, 'QRCODE,M', 87, 240, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                        $pdf->writeHTMLCell(70, 0, 68, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                    }
                    if ($PagoPACode2!='' && $QRCode2) {
                        $pdf->write2DBarcode($PagoPACode2Full, 'QRCODE,M', 87, 240, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                        $pdf->writeHTMLCell(70, 0, 68, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
                    }
                }
                
                //QRCODE URL
                if(isset($pagopaService)){
                    $url_PagoPAPage1 = pickPagoPAPaymentUrl($pagopaService['Id'], array('iuv' => $PagoPACode1));
                    $url_PagoPAPage2 = pickPagoPAPaymentUrl($pagopaService['Id'], array('iuv' => $PagoPACode2));
                    
                    if ($PagoPACode1!='' && $PagoPACode2!='' && $QRCodeURL1 && $QRCodeURL2){
                        if(!empty($url_PagoPAPage1)){
                            $pdf->write2DBarcode($url_PagoPAPage1, 'QRCODE,M', 40, 237, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                            $pdf->writeHTMLCell(70, 0, 20, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                            $pdf->writeHTMLCell(70, 0, 20, 268, 'IUV: '.$PagoPACode1, 0, 0, 1,true, true, 'C', true);
                        }
                        if(!empty($url_PagoPAPage2)){
                            $pdf->write2DBarcode($url_PagoPAPage2, 'QRCODE,M', 140, 237, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                            $pdf->writeHTMLCell(70, 0, 120, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
                            $pdf->writeHTMLCell(70, 0, 120, 268, 'IUV: '.$PagoPACode2, 0, 0, 1,true, true, 'C', true);
                        }
                    } else {
                        if ($PagoPACode1!='' && $QRCodeURL1){
                            if(!empty($url_PagoPAPage1)){
                                $pdf->write2DBarcode($url_PagoPAPage1, 'QRCODE,M', 87, 237, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                                $pdf->writeHTMLCell(70, 0, 68, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                                $pdf->writeHTMLCell(70, 0, 68, 268, 'IUV: '.$PagoPACode1, 0, 0, 1,true, true, 'C', true);
                            }
                        }
                        
                        if ($PagoPACode2!='' && $QRCodeURL2) {
                            if(!empty($url_PagoPAPage2)){
                                $pdf->write2DBarcode($url_PagoPAPage2, 'QRCODE,M', 87, 237, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                                $pdf->writeHTMLCell(70, 0, 68, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
                                $pdf->writeHTMLCell(70, 0, 68, 268, 'IUV: '.$PagoPACode2, 0, 0, 1,true, true, 'C', true);
                            }
                        }
                    }
                }
                
                //Muove il puntatore alla posizione precedente
                $pdf->setPage($CurrentPage, true);
            }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            if(!$ultimate){
                applyTemporaryLabel($pdf);
            }
            
            //Crea il file pdf del record corrente/////////////////////////////////////////////////////////////////////////////////
            $pdf->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation, "F");
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Allega l'avviso di pagamento generato se era abilitata la gestione
            if($r_Customer['PagoPAPaymentNoticeForeign'] > 0 && $b_PagoPAEnabled && !$b_ErroreAvviso){
                //Inizializza pdf-union
                $pdf_unionavviso = new FPDI();
                $pdf_unionavviso->setHeaderFont(array('helvetica', '', 8));
                $pdf_unionavviso->setFooterFont(array('helvetica', '', 8));
                $pdf_unionavviso->setPrintHeader(false);
                $pdf_unionavviso->setPrintFooter(false);
                
                try {
                    $n_PageCount = $pdf_unionavviso->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Documentation);
                    for ($p = 1; $p <= $n_PageCount; $p++) {
                        $tmp_Page = $pdf_unionavviso->ImportPage($p);
                        $tmp_Size = $pdf_unionavviso->getTemplatesize($tmp_Page);
                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                        $pdf_unionavviso->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_unionavviso->useTemplate($tmp_Page);
                    }
                    
                    $TotalPages = $n_PageCount;
                    
                    $n_PageCount = $pdf_unionavviso->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
                    for ($p = 1; $p <= $n_PageCount; $p++) {
                        $tmp_Page = $pdf_unionavviso->ImportPage($p);
                        $tmp_Size = $pdf_unionavviso->getTemplatesize($tmp_Page);
                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                        $pdf_unionavviso->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_unionavviso->useTemplate($tmp_Page);
                    }
                    
                    $TotalPages += $n_PageCount;
                    
                } catch (Exception $e) {
                    if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
                    trigger_error("<CREAAVVISOBONARIO> ATTENZIONE -> Errore nell\'unione del pdf dell'avviso di pagamento avviso_$Documentation: $e",E_USER_WARNING);
                    $str_Warning .= 'ID '.$FineId.': Errore nella fusione del verbale e dell\'avviso di pagamento PagoPA. Contattare l\'amministrazione di sistema.<br>';
                }
                
                $pdf_unionavviso->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
                
                unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
            }
            
            //POPOLA RIGHE DEL FILE TXT NEL FLUSSO///////////////////////////////////////////////////////////////////////
            $str_CustomerAddress = $r_Customer['ManagerAddress'];
            $str_CustomerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
                
            $a_FlowRows[] = array(
                /*FineId*/                      $FineId,
                /*CRON*/                        $ProtocolNumber.'/'.$r_Fine['ProtocolYear'],
                /*STAMPA_AVVISO_PAGOPA*/        "NO", //$r_Customer['PagoPAPaymentNoticeForeign'] ? "SI" : "NO",
                /*NUMAVVIUV1*/                  $PagoPACode1 != '' ? $PagoPAPaymentNotice1: 'NON PRESENTE',
                /*NUMAVVIUV2*/                  $PagoPACode2 != '' ? $PagoPAPaymentNotice2 : 'NON PRESENTE',
                /*QRCODE1_LINK*/                $url_PagoPAPage1,
                /*QRCODE2_LINK*/                $url_PagoPAPage2,
                /*QRCODE1*/                     $PagoPACode1 != '' ? $PagoPACode1Full : 'NON PRESENTE',
                /*QRCODE2*/                     $PagoPACode2 != '' ? $PagoPACode2Full : 'NON PRESENTE',
                /*STRINGA_QRCODE1*/             $str_PaymentDay1,
                /*STRINGA_QRCODE2*/             $str_PaymentDay2,
                /*IMPORTO1*/                    $Bollettino1Fee,
                /*IMPORTO2*/                    $Bollettino2Fee,
                /*ENTE_CREDITORE*/              $r_Customer['ManagerName'],
                /*SETTORE_ENTE_CREDITORE*/      $r_Customer['ManagerSector'],
                /*INFO_ENTE_CREDITORE*/         $r_Customer['PagoPAPaymentInfo'],
                /*CF_ENTE_CREDITORE*/           $r_Customer['ManagerTaxCode'],
                /*CBILL_ENTE_CREDITORE*/        $r_Customer['PagoPACBILL'],
                /*INTESTATARIO_CPP*/            $r_Customer['PagoPACPPOwner'],
                /*OGGETTO_PAGAMENTO*/           $r_Customer['PagoPAPaymentSubject'],
                /*NUMERO_CPP*/                  $r_Customer['ForeignBankAccount'],
                /*TIPOLOGIA_STAMPA*/            'Atto Giudiziario',
                /*TIPOLOGIA_ATTO*/              'AVVISO BONARIO',
                /*TIPOLOGIA_FLUSSO*/            $FormTypeId,
                /*RICHIESTA_DATI*/              ($chk_126Bis) ? "SI" : "NO",
                /*CodiceComune*/                $_SESSION['cityid'],
                /*HeaderRow1*/                  $r_Customer['ManagerName'],
                /*HeaderRow2*/                  $r_Customer['ManagerSector'],
                /*HeaderRow3*/                  $str_CustomerAddress,
                /*HeaderRow4*/                  $str_CustomerCity,
                /*HeaderRow5*/                  $r_Customer['ManagerPhone'],
                /*Spese_Anticipate*/            $str_SmaPayment,
                /*Intestatario_SMA*/            $str_SmaName,
                /*Numero_SMA*/                  $str_SmaAuthorization,
                /*Mod23_Soggetto_Mittente*/     $str_Mod23LSubject,
                /*Mod23_Ente_Gestito*/          $str_Mod23LCustomerName,
                /*Mod23_Recapito_Soggetto*/     $str_Mod23LCustomerSubject,
                /*Mod23_Indirizzo_Soggetto*/    $str_Mod23LCustomerAddress,
                /*Mod23_Citta_Soggetto*/        $str_Mod23LCustomerCity,
                /*Recipient_Row1*/              $a_GenreLetter[$trespasser['Genre']] . " " . $trespasser['CompanyName'] . " " . $trespasser['Surname'] . " " . $trespasser['Name'],
                /*Recipient_Row2*/              $str_TrespasserAddress,
                /*Recipient_Row3_1*/            $trespasser['ZIP'],
                /*Recipient_Row3_2*/            $trespasser['City'],
                /*Recipient_Row3_3*/            $trespasser['Province'],
                /*CODICE_FISCALE*/              PickVatORTaxCode($trespasser['Genre'],$trespasser['VatCode'],$trespasser['TaxCode']),
                /*NOME_FLUSSO*/                 $DocumentationZip,
                /*NOME_DOCUMENTO*/              $Documentation,
                /*N_PAGINE*/                    $TotalPages,
            );
            
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Inserisce il record per il flusso su FineHistory //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($ultimate){
                $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $FineId." AND NotificationTypeId=".$NotificationTypeId);
                
                if(mysqli_num_rows($rs_FineHistory)==0){
                    $aInsertHistory = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                        array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['ControllerId'], 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $KindCreateDate),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $KindCreateDate),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $KindCreateDate),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentationZip),
                    );
                    $a_InsFineHistory[] = $aInsertHistory;
                    //$rs->Insert('FineHistory', $aInsertHistory);
                }
                
                //Crea il record del documento su FineDocumentation come Avviso Bonario//////////////////////////////////////////
                $aInsertDoc = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 80),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                $a_InsFineDocumentation[] = $aInsertDoc;
                //$rs->Insert('FineDocumentation', $aInsertDoc);
            }
        }
        
        if ($ultimate){
            //Aggiorna il verbale inserendovi la data di crezione del bonario, imposta lo stato a 8 e aggiorna il cronologico//////////////////////////////////////////////////////
            $aUpdateFine = array(
                array('field' => 'KindCreateDate', 'selector' => 'value', 'type' => 'date', 'value' => $KindCreateDate),
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 8),
                array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolNumber, 'settype' => 'int'),
            );
            $a_UpdFine[] = $aUpdateFine;
            //$rs->Update('Fine', $aUpdateFine, 'Id=' . $FineId);
        }
        
        //PARTE DB///////////////////////////////////////////////////////////////////////////////////////////////////
        //Se l'id del verbale non è fra quelli di cui PagoPA ha fallito, esegue le query, scrive i dati nel file di testo e aggiunge i pdf generati al pdf unito
        if(!in_array($FineId, $a_FailedPagoPA)){
            $n_Successful ++;
            
            if($ultimate){
                executeQueries('Insert', 'FineHistory', $a_InsFineHistory);
                executeQueries('Insert', 'FineDocumentation', $a_InsFineDocumentation);
                executeQueries('Update', 'Fine', $a_UpdFine, "Id=$FineId");
            }
            
            //Scrive il file di testo
            $str_txt = '';
            foreach($a_FlowRows as $FineRows){
                foreach($FineRows as $Value){
                    $str_txt .= $Value.";";
                }
            }
            fwrite($myfile, $str_txt.PHP_EOL);
            
            foreach($a_Documentation[$FineId] as $Doc){
                //PDF UNION////////////////////////////////////////////////////////////////////////////////////
                $n_PageCount = $pdf_union->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
                for ($p = 1; $p <= $n_PageCount; $p++) {
                    
                    $tmp_Page = $pdf_union->ImportPage($p);
                    $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                    
                    $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                    
                    $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                    $pdf_union->useTemplate($tmp_Page);
                }
                //////////////////////////////////////////////////////////////////////////////////////////////
            }
        } else {
            foreach($a_Documentation[$FineId] as $Doc){
                unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    }
    
    fclose($myfile);
    
    //Se definitivo e almeno un atto è stato processato con successo, crea il record del flusso e ne attribuisce l'id a ogni FineHistory
    if($ultimate && $n_Successful > 0){
        $Zone0Number=count($_POST['check']);
        $aInsertFlow = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
            array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
            array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>$PrintTypeId,'settype'=>'int'),
            array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeId,'settype'=>'int'),
            array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['check'])),
            array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>$KindCreateDate),
            array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
            array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$PrinterId,'settype'=>'int'),
            array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
            array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
        );
        $FlowId = $rs->Insert('Flow',$aInsertFlow);
        
        $aUpdateFineHistory = array();
        
        $aUpdateFineHistory[] = array(
            array('field' => 'FlowId', 'selector' => 'value', 'type' => 'int', 'value' => $FlowId, 'settype' => 'int')
        );
        
        executeQueries('Update', 'FineHistory', $aUpdateFineHistory, "Documentation='$DocumentationZip' AND FlowNumber=$int_FlowNumber");
    }
    
    //Crea il file pdf di tutti i pdf uniti////////////////////////////////////////////////////////////////////////////////////////
    if ($ultimate){
        $UnionDocumentation = $_SESSION['cityid'] . "_" . date("Y-m-d_H-i-s") . ".pdf";
        
        if (!is_dir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/create")) {
            mkdir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/create", 0777);
        }
        
        $pdf_union->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/create/' . $UnionDocumentation, "F");
    } else {
        $UnionDocumentation = 'export_senddynamic_kindfine_foreign.pdf';
        $pdf_union->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $UnionDocumentation, "F");
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    //Crea lo zip e vi inserisce i pdf e il txt del flusso/////////////////////////////////////////////////////////
    if($n_Successful > 0){
        $zip = new ZipArchive();
        if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($tempTXT,$DocumentationTxt);
            
            //Aggiunge i pdf
            foreach ($a_Documentation as $DocFineId => $a_Doc){
                foreach ($a_Doc as $Doc){
                    $zip->addFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc, $Doc);
                }
            }
            
            $zip->close();
            
            //Sposta (se stampa definitiva) ed elimina i pdf
            foreach ($a_Documentation as $DocFineId => $a_Doc){
                if ($ultimate){
                    if (!is_dir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId)) {
                        mkdir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId, 0777);
                    }
                    
                    foreach ($a_Doc as $Doc){
                        copy(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc, FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . "/" . $Doc);
                    }
                }
                foreach ($a_Doc as $Doc){
                    unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc);
                }
            }
            
            unlink($tempTXT);
            
            $_SESSION['Documentation'] = $MainPath.'/doc/foreign/flow/'.$_SESSION['cityid'].'/'.$DocumentationZip;
        } else {
            $str_Warning = "Errore nella creazione dell\' archivio del flusso.</br>";
        }
    } else {
        $str_Warning .= "Errore nella creazione dell\' archivio del flusso: Nessun atto da includere.</br>";
        unlink($tempTXT);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
    } else {
        if($ultimate){
            $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
        }
    }
    
    //$rs->UnlockTables();
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $rs->End_Transaction();
}



