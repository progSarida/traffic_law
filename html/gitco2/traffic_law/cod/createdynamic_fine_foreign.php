<?php
require_once(CLS . '/cls_iuv.php');
require_once(INC."/function_postalCharge.php");
require_once(CLS."/avvisiPagoPA/ModelloCDS.php");

//Usato nel caso questo script sia incluso in un altro script che già inizializza una transazione
//mgmt_fine_exp_ag_exe.php
$b_DisableTransaction = $b_DisableTransaction ?? false;

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

function buildHeader($r_Customer, $PrintDestinationFold){
    $str_Header = '<span style="line-height:1.1">';
    if($r_Customer['ManagerSector'] != ''){
        $str_Header .= $r_Customer['ManagerSector'] != '' ? $r_Customer['ManagerSector'] : '';
        $str_Header .= '<br>';
    }
    if ($PrintDestinationFold != ''){
        $str_Header .= '<span style="font-size:7rem">RESTITUZIONE PIEGO IN CASO DI MANCATO RECAPITO:<br>';
        $str_Header .= strtoupper($PrintDestinationFold).'</span><br>';
    }
    if($r_Customer['ManagerAddress'] != ''){
        $str_Header .= $r_Customer['ManagerAddress'].'<br>';
    }
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ManagerPhone'] != ''){
        $str_Header .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Header .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Header .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Header .= $r_Customer['ManagerMail'] ? 'MAIL: '.$r_Customer['ManagerMail'] : '';
        $str_Header .= '<br>';
    }
    if($r_Customer['ManagerCountry'] != ''){
        $str_Header .= $r_Customer['ManagerCountry'];
    }
    return $str_Header.'</span>';
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

if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid'])) {
    mkdir(FOREIGN_FINE."/".$_SESSION['cityid'], 0777);
}

if(!$b_DisableTransaction){
    //BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
    //NOTA BENE: il blocco della tabella serve ad impedire di creare atti con cronologici duplicati
    //es: si creano verbali dinamici nello stesso momento in cui si creano verbali PEC
    $a_LockTables = array("LockedPage WRITE");
    $rs->LockTables($a_LockTables);
    
    $rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
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
            $rs->Update('LockedPage', $UpdateLockedPage, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
        }
    } else {
        $InsertLockedPage = array(
            array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}"),
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
            );
        $rs->Insert('LockedPage', $InsertLockedPage);
    }
    
    $rs->UnlockTables();
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

//IMPORTANTE: NON cambiare il font 'dejavusans', serve il supporto per i caratteri UTF8 lituani e non tutti i font lo hanno
$font = 'dejavusans';

$a_Lan = unserialize(LANGUAGE);
$a_Rent = unserialize(RENT);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT_CITYID)[$_SESSION['cityid']];
$a_AdditionalMass = unserialize(ADDITIONAL_MASS_CITYID)[$_SESSION['cityid']];
if($a_AdditionalNight == null)
    $a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
    
    if($a_AdditionalMass == null)
        $a_AdditionalMass = unserialize(ADDITIONAL_MASS);
$a_FineSendType = unserialize(FINE_SEND_TYPE);

//Coordinata inizio stampa testo dinamico
$TextStartCoord = array('X'=>10, 'Y'=>92);

$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$a_DocumentationFineZip = array();
//Contiene i FineId di cui non è stato possibile generare/aggiornare PagoPA
$a_FailedPagoPA = array();

$ProtocolNumber = 0;

$StatusTypeId = 15;
$NotificationTypeId = 2;

if($_SESSION['usertype']==2){
    $rs_Time = $rs->SelectQuery("SELECT MAX( ControllerTime ) ControllerTime FROM Fine WHERE ControllerDate='".date("Y-m-d")."'");
    $r_Time =  mysqli_num_rows($rs_Time);
    $Time = ($r_Time['ControllerTime']=="") ? "08:00:11" : $r_Time['ControllerTime'];
}

$NotificationDate = date("Y-m-d");

$ultimate = CheckValue('ultimate','s');

$FinePDFList = $r_Customer['FinePDFList'];

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

//Controlli verbali
if (isset($_POST['checkbox'])) {
    foreach ($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);
        
        
        if ($r_Fine['StatusTypeId'] != 9 && $r_Fine['StatusTypeId'] != 10 && $r_Fine['StatusTypeId'] != 14) {
            $str_Error .= "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Azione non prevista per verbale in stato ".$r_Fine['StatusTypeId'].".<br>";
        }
        
        $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);
        
        if ($trespasser['CountryId'] == 'ZZZZ') {
            $str_Error .= "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Stato del trasgressore non presente.<br>";
        }
        
        if ($r_Fine['ControllerId'] == "" && $_SESSION['usertype'] != 2) {
            $str_Error .= "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Manca il verbalizzante.<br>";
        } else if ($r_Fine['ControllerId']=="" &&  $_SESSION['usertype']==2 && $ultimate) {
            $ControllerDate = date("Y-m-d");
            $ControllerTime = date("H:i:s", strtotime('+41 seconds', strtotime($Time)));
            
            $Time = $ControllerTime;
            
            $a_Fine = array(
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['controllerid'], 'settype' => 'int'),
                array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => $ControllerDate),
                array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'str', 'value' => $ControllerTime),
            );
            
            $rs->Update('Fine', $a_Fine, "Id=" . $FineId);
        }
    }
}

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrintDestinationFold AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_FoldReturn = $r_PrintParameter['ForeignFineFoldReturn'];
$str_PostalAuthorization = trim($r_PrintParameter['ForeignPostalAuthorization'] ?? '');
$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['ForeignPostalAuthorizationPagoPA'] ?? '');
////////////////////////////////////////////////

//Bug 2869 PagoPA funziona per ora solo per CDS
$b_PagoPAEnabled = $r_Customer['PagoPAPaymentForeign'] > 0 && $_SESSION['ruletypeid'] == RULETYPE_CDS;
$b_PrintBill = ($b_PagoPAEnabled ? $r_Customer['PagoPAPaymentNoticeForeign'] <= 0 : true) && !empty($r_Customer['ForeignPostalType']);
$b_PrintBillPagoPA = $b_PagoPAEnabled && !empty($r_Customer['ForeignPostalTypePagoPA']) && $r_Customer['PagoPAPaymentNoticeForeign'] > 0;

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
    $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $_SESSION['Message']['Error'] = $str_Error;
    header("location: ".impostaParametriUrl(array('P' => $P), 'frm_createdynamic_fine.php'));
    DIE;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//SERVIZIO PAGOPA ENTE
//se è abilitato pagoPA interroghiamo la base dati per prendere le configurazioni
if($b_PagoPAEnabled){
    $pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
    $pagopaService=mysqli_fetch_array($pagopaServicequery);
}

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
/////////////////////////////////////////////

if(isset($_POST['checkbox'])) {
    if(!$b_DisableTransaction) $rs->Start_Transaction();

    $rs_ChiefController = $rs->Select('Controller',"Id=".$SelectChiefControllerId);
    $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array($font, '', 8));
    $pdf_union->setFooterFont(array($font, '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);
    
    foreach($_POST['checkbox'] as $FineId) {
        if(!$b_DisableTransaction) $rs->Begin_Transaction();
        
        $a_DocumentationFineZip[$FineId] = array();
        $a_InsFineHistory= array();
        $a_InsFineDocumentation = array();
        $a_InsFineNotification = array();
        $a_InsDocumentationProtocol = array();
        $a_UpdFine = array();
        
        $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
        $pdf->TemporaryPrint= $ultimate;
        $pdf->NationalFine= 0;
        $pdf->CustomerFooter = 0;
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Violation');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setHeaderFont(array($font, '', 8));
        $pdf->setFooterFont(array($font, '', 8));
        $pdf->SetMargins(10,8,10);
        $pdf->SetCellPadding(0);
        
        

        $NotificationFee    = 0;
        $ChargeTotalFee     = 0;
        $ResearchFee        = 0;
        $n_TotFee           = 0;
        $n_TotMaxFee        = 0;
        $chk_126Bis         = false;
        $chk_ReducedPayment = false;
        $n_LicensePoint     = 0;
        $n_TotPartialFee    = 0;

        
        if($r_Customer['ForeignTotalFee']>0) 
            $ChargeTotalFee = $r_Customer['ForeignTotalFee'];
        else{ //valore ForeignNotificationFee poi sovrascritti dalle zone
            if($r_Customer['ForeignNotificationFee']>0){
                $NotificationFee = $r_Customer['ForeignNotificationFee'];
            }
            $ResearchFee = $r_Customer['ForeignResearchFee'];
        }

        $pdf->AddPage();
        $pdf->SetMargins(10,8,10);

        //Se provvisorio evidenzia il testo in giallo
        $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Image($_SESSION['blazon'], 7, 8, 12, 17);

        $ManagerName = $r_Customer['ManagerName'];
        $pdf->customer = $ManagerName;


        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=".$FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);
        $ViolationTypeId = $r_Fine['ViolationTypeId'];
        $ProtocolYear = $r_Fine['ProtocolYear'];

        //In questo caso "Id" corrisponde al RuleTypeId
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."' AND Id=".$_SESSION['ruletypeid']);
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        $RuleTypeId = $r_RuleType['Id'];

        
        $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=".$r_Fine['ArticleId']." AND Year=".$r_Fine['ProtocolYear']);
        $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

        $trespassers = $rs->Select('V_Trespasser', "Id=".$r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);
        
        $rs_FineOwner = $rs->Select('FineOwner',"FineId=".$FineId);
        $r_FineOwner = mysqli_fetch_array($rs_FineOwner);

        $n_LanguageId = $trespasser['LanguageId'];
        $ZoneId= $trespasser['ZoneId'];

        $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
        $FormTypeId = $r_RuleType['ForeignFormId'];

        $a_PrintObject = explode("*",$r_RuleType['PrintObject'.$a_Lan[$n_LanguageId]]);

        //Font helvetica per intestazione
        $pdf->SetFont('helvetica', '', 5, '', true);
        
        // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

        //$pdf->Line(7, 9, 200, 9);
        $pdf->writeHTMLCell(68, 0, 25, 6, '<strong>'.$r_Customer['ManagerName'].'</strong>', 0, 0, 1, true, 'L', true);
        $pdf->LN(3);
        $pdf->writeHTMLCell(68, 0, 25, '', buildHeader($r_Customer, $str_FoldReturn), 0, 0, 1, true, 'L', true);
        
        $pdf->SetFont('helvetica', '', 8, '', true);
        
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
        
        $pdf->SetFont($font, '', 8, '', true);

        $str_AdditionalNight = "";
        if($r_ArticleTariff['AdditionalNight']==1){

            $a_Time = explode(":",$r_Fine['FineTime']);

            if($a_Time[0]<FINE_HOUR_START_DAY ||  ($a_Time[0]>FINE_HOUR_END_DAY) || ($a_Time[0]==FINE_HOUR_END_DAY && $a_Time[1]!="00")){
                $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId];
            }
        }

        $str_AdditionalMass = "";
        if($r_ArticleTariff['AdditionalMass']==1){
            if($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
        }


        if($r_ArticleTariff['126Bis']==1){
            $chk_126Bis = true;
            $n_LicensePoint += $r_ArticleTariff['LicensePoint'];
        }

        $n_TotFee += $r_Fine['Fee'];
        $n_TotMaxFee += $r_Fine['MaxFee'];
        if($r_ArticleTariff['ReducedPayment']==1){
            $chk_ReducedPayment = true;
            $n_TotPartialFee +=  $r_Fine['Fee']*FINE_PARTIAL;
        } else {
            $n_TotPartialFee +=  $r_Fine['Fee'];
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
        
        
        
        
        

        if(($r_Fine['Article']==126 AND $r_Fine['Paragraph']=='0' AND $r_Fine['Letter']=='bis') || ($r_Fine['Article']==180 AND $r_Fine['Paragraph']=='8' AND $r_Fine['Letter']=='')){

            $PreviousId = $r_Fine['PreviousId'];

            $rs_PreviousFine = $rs->Select('Fine', "Id=".$PreviousId);
            $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);

            $PreviousProtocolId = $r_PreviousFine['ProtocolId'];
            $PreviousProtocolYear = $r_PreviousFine['ProtocolYear'];
            $PreviousFineDate = DateOutDB($r_PreviousFine['FineDate']);

        } else {


            $PreviousProtocolId = "";
            $PreviousProtocolYear = "";
            $PreviousFineDate = "";


        }

        $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);
        
        if($ChargeTotalFee>0){
            $ResearchFee = $ChargeTotalFee - $postalcharge['Zone'.$ZoneId];
            $NotificationFee = $postalcharge['Zone'.$ZoneId];


        }else{ //sovrascrive comunque la spesa di notifica generale con quella della zona
            if($NotificationFee==0){
                $NotificationFee = $postalcharge['Zone'.$ZoneId];
            }
        }


        $CustomerFee = $r_Fine['CustomerAdditionalFee'];
        $NotificationFee += $r_Fine['OwnerAdditionalFee']+$CustomerFee;

        $AdditionalFee =  $NotificationFee + $ResearchFee;

//         if($r_Fine['DetectorId']==0){
//             $DetectorTitle = "";
//             $SpeedLengthAverage = 0;
//         } else {
//             $detectors = $rs->Select('Detector',"Id=".$r_Fine['DetectorId']);
//             $detector = mysqli_fetch_array($detectors);

//             $DetectorTitle = $detector['Title'.$a_Lan[$n_LanguageId]];
//             $SpeedLengthAverage = $detector['SpeedLengthAverage'];
//         }
        $Content=getDynamicContent($FormTypeId,$_SESSION['cityid'],2,$n_LanguageId);
        /////////////////////////////////////////////
        //SOTTOTESTI
        /////////////////////////////////////////////
        $EmptyPregMatch = false;
        //Continua a cercare per variabili di sottotesti da sostituire finchè non trova nulla
        while(!$EmptyPregMatch){
            $a_Variables = array();
            $a_Matches = array();
            
            if(preg_match_all("/\{\{.*?\}\}/", $Content, $a_Variables) > 0){
                $a_Matches = $a_Variables[0];
                
                foreach ($a_Matches as $var){
                    $a_Types =  getFormVariables($var,$_SESSION['cityid'],$FormTypeId, 2, $n_LanguageId );
                    //Sottotesto trasgressore
                    if ($var == "{{TrespasserDescription}}"){
                        $str_TrespasserAddress =  trim(
                            $trespasser['Address'] ." ".
                            $trespasser['StreetNumber'] ." ".
                            $trespasser['Ladder'] ." ".
                            $trespasser['Indoor'] ." ".
                            $trespasser['Plan']
                            );
                        
                        $a_Types[1] = str_replace("{TrespasserName}", StringOutDB((isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' ' . $trespasser['Name']), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserAddress}", StringOutDB($str_TrespasserAddress != "" ? $str_TrespasserAddress : ""), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserCountry}", StringOutDB($trespasser['CountryTitle']), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserZip}", StringOutDB($trespasser['ZIP']), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $a_Types[1]);
                        $a_Types[1] = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $a_Types[1]);
                        
                        //TODO Togliere la gestione della descrizione del trasgressore una volta che il sottotesto è definito su db
                        //$Content = str_replace("{{TrespasserDescription}}", $a_Types[1], $Content);
                        $Content = str_replace("{{TrespasserDescription}}", "", $Content);
                    }
                    
                    //Sottotesto dati per tipo trasgressore
                    else if ($var == "{{TrespasserType}}"){
                        $str_Trespasser = "";
                        if($r_Fine['TrespasserTypeId']==1){
                            //Proprietario
                            $str_Trespasser = $a_Types[1];
                        } else if  ($r_Fine['TrespasserTypeId']==2 || $r_Fine['TrespasserTypeId']==3 || $r_Fine['TrespasserTypeId']==11){
                            //Obbligato/Nolleggio
                            $str_Trespasser = $a_Types[2];
                        }
                        $Content = str_replace("{{TrespasserType}}", $str_Trespasser, $Content);
                    } 
                    
                    //Sottotesto rilevatore
                    else if ($var == "{{Detector}}"){
                        $str_Detector = "";
                        if ($r_Fine['DetectorId']!=0){
                            $str_Detector = $a_Types[1];
                        }
                        $Content = str_replace("{{Detector}}", $str_Detector, $Content);
                    }
                    
                    //Sottotesto velocità rilevatore
                    else if ($var == "{{DetectorText}}"){
                        $str_DetectorText = "";
                        if ($r_Fine['DetectorId']!=0){
                            $str_DetectorText = $a_Types[1];
                        }
                        $Content = str_replace("{{DetectorText}}", $str_DetectorText, $Content);
                    }
                    
                    //Pagamento ridotto
                    else if ($var == "{{ReducedPayment}}"){
                        $str_ReducedPayment = "";
                        if($r_ArticleTariff['ReducedPayment']){
                            $str_ReducedPayment = $a_Types[1];
                        }
                        $Content = str_replace("{{ReducedPayment}}", $str_ReducedPayment, $Content);
                    }
                    
                    //Decurtazione 126 bis
                    else if ($var == "{{Decurtation126bis}}"){
                        $str_Decurtation126bis = "";
                        if($r_ArticleTariff['126Bis']==1){
                            $str_Decurtation126bis = $a_Types[1];
                        }
                        $Content = str_replace("{{Decurtation126bis}}", $str_Decurtation126bis, $Content);
                    }
                    
                    //Raccomandata
                    else if ($var == "{{LicensePointPage}}"){
                        $str_LicensePointPage = "";
                        if ($chk_126Bis){
                            $str_LicensePointPage = $a_Types[1];
                        }
                        $Content = str_replace("{{LicensePointPage}}", $str_LicensePointPage, $Content);
                    }

                    else $Content = str_replace($var, $a_Types[1], $Content);
                    
                }
            } else $EmptyPregMatch = true;
        }
        //
        
        /////////////////////////////////////////////
        //VARIABILI
        /////////////////////////////////////////////
        
        $Content = str_replace("{SendType}", $a_FineSendType[1], $Content);
        
        //TESTO STAMPA HEADER IN ALTO A DX////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Content = str_replace("{PrintObjectRow1}",utf8_decode($a_PrintObject[0]),$Content); //Riga 1
        $Content = str_replace("{PrintObjectRow2}",utf8_decode($a_PrintObject[1]),$Content); //Riga 2
        $Content = str_replace("{PrintObjectRow3}",utf8_decode($a_PrintObject[2]),$Content); //Riga 3
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //Trespasser////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $TrespasserTaxCode = PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode']);
        $TrespasserName = StringOutDB((isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' ' . $trespasser['Name']);
        
        if($r_Fine['TrespasserTypeId']==11){
            $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=".$FineId." AND TrespasserTypeId=10");
            $r_trespasser = mysqli_fetch_array($rs_trespasser);
            
            $Content = str_replace("{TrespasserName}",(isset($r_trespasser['CompanyName']) ? StringOutDB($r_trespasser['CompanyName']).' ' : '').StringOutDB($r_trespasser['Surname'].' '.$r_trespasser['Name']),$Content);
            $Content = str_replace("{TrespasserRentName}",(isset($trespasser['CompanyName']) ? StringOutDB($trespasser['CompanyName']).' ' : '').StringOutDB($trespasser['Surname'].' '.$trespasser['Name']),$Content);
            $Content = str_replace("{TrespasserAddress}",StringOutDB($trespasser['Address']." ".$trespasser['ZIP'].' '.$trespasser['City']),$Content);
            $Content = str_replace("{TrespasserCountry}",StringOutDB($trespasser['CountryTitle']),$Content);
            $Content = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $Content);
            $Content = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $Content);
            $Content = str_replace("{TrespasserZip}", StringOutDB($trespasser['ZIP']), $Content);
            $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
            $Content = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $Content);
            
        } else {
            if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {
                $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=2");
                $r_trespasser = mysqli_fetch_array($rs_trespasser);
                
                $Content = str_replace("{TrespasserName}",StringOutDB($r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name']),$Content);
                $Content = str_replace("{TrespasserNameRent}",StringOutDB($trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name']),$Content);
                $Content = str_replace("{TrespasserAddress}",StringOutDB($r_trespasser['Address']." ".$r_trespasser['ZIP'].' '.$r_trespasser['City']),$Content);
                $Content = str_replace("{TrespasserCountry}",StringOutDB($r_trespasser['CountryTitle']),$Content);
                $Content = str_replace("{TrespasserCity}", StringOutDB($r_trespasser['City']), $Content);
                $Content = str_replace("{TrespasserProvince}", StringOutDB($r_trespasser['Province']), $Content);
                $Content = str_replace("{TrespasserZip}", StringOutDB($r_trespasser['ZIP']), $Content);
                $Content = str_replace("{TrespasserBornDate}", DateOutDB($r_trespasser['BornDate']), $Content);
                $Content = str_replace("{TrespasserBornCity}", StringOutDB($r_trespasser['BornPlace']), $Content);
            } else {
                $Content = str_replace("{TrespasserName}",StringOutDB($trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name']),$Content);
                $Content = str_replace("{TrespasserAddress}",StringOutDB($trespasser['Address']." ".$trespasser['ZIP'].' '.$trespasser['City']),$Content);
                $Content = str_replace("{TrespasserCountry}",StringOutDB($trespasser['CountryTitle']),$Content);
                $Content = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $Content);
                $Content = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $Content);
                $Content = str_replace("{TrespasserZip}", StringOutDB($trespasser['ZIP']), $Content);
                $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
                $Content = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $Content);
            }
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //Article/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Paragraph = ($r_Fine['Paragraph']=="0" || $r_Fine['Paragraph']=="") ? "" : " / ".$r_Fine['Paragraph'];
        $Letter = ($r_Fine['Letter']=="0") ? "" : $r_Fine['Letter'];
        $str_ArticleId = $r_Fine['Article']. $Paragraph . $Letter;
        
        $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription'.$a_Lan[$n_LanguageId]]))>0) ?  $r_FineOwner['ArticleDescription'.$a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription'.$a_Lan[$n_LanguageId]] ;
        $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription'.$a_Lan[$n_LanguageId]]))>0) ?  $r_FineOwner['ReasonDescription'.$a_Lan[$n_LanguageId]] : $r_Fine['ReasonTitle'.$a_Lan[$n_LanguageId]] ;
        
        /////////////////////////////////////////////
        //Additional Article
        /////////////////////////////////////////////
        if($r_Fine['ArticleNumber']>1){
            $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=".$FineId, "ArticleOrder");
            while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
                
                if($r_ArticleTariff['126Bis']==1){
                    $chk_126Bis = true;
                    $n_LicensePoint += $r_AdditionalArticle['LicensePoint'];
                }
                
                $n_TotFee += $r_AdditionalArticle['Fee'];
                $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
                if($r_AdditionalArticle['ReducedPayment']==1){
                    $chk_ReducedPayment = $chk_ReducedPayment || true;
                    $n_TotPartialFee +=  $r_AdditionalArticle['Fee']*FINE_PARTIAL;
                } else {
                    $n_TotPartialFee +=  $r_AdditionalArticle['Fee'];
                }
                
                $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.$a_Lan[$n_LanguageId]]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.$a_Lan[$n_LanguageId]] : $r_AdditionalArticle['ArticleDescription' . $a_Lan[$n_LanguageId]];
                
                
                $Paragraph = ($r_AdditionalArticle['Paragraph']=="0") ? "" : $r_AdditionalArticle['Paragraph']." ";
                $Letter = ($r_AdditionalArticle['Letter']=="0") ? "" : $r_AdditionalArticle['Letter'];
                
                
                $str_ArticleDescription .= " Art. ". $r_AdditionalArticle['Article']."/".$Paragraph.$Letter;
                $str_ArticleDescription .= $str_AdditionalArticleDescription;
                
                if ($r_AdditionalArticle['PrefectureFixed'] == 1){
                    $str_ArticleDescription .= "con importo fissato da prefettura di euro ".$r_AdditionalArticle['PrefectureFee']." in data ".DateOutDB($r_AdditionalArticle['PrefectureDate']);
                }
            }
        }
        
        //$Content = str_replace("{AdditionalSanction}",$str_AdditionalSanction,$Content);
        $Content = str_replace("{ReasonId}", utf8_decode($str_ReasonDescription), $Content);
        $Content = str_replace("{ArticleId}",$str_ArticleId,$Content);
        $Content = str_replace("{ArticleDescription}",utf8_decode($str_ArticleDescription), $Content);
        $Content = str_replace("{ArticleAdditionalNight}", $str_AdditionalNight, $Content);
        $Content = str_replace("{ArticleAdditionalMass}", $str_AdditionalMass, $Content);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //Detector///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($r_Fine['DetectorId']==0){
            $DetectorTitle = "";
            $SpeedLengthAverage = 0;
        } else {
            $detectors = $rs->Select('Detector',"Id=".$r_Fine['DetectorId']);
            $detector = mysqli_fetch_array($detectors);
            
            $DetectorTitle = StringOutDB($detector['Title'.$a_Lan[$n_LanguageId]]);
            $SpeedLengthAverage = $detector['SpeedLengthAverage'];
        }
        
        if($SpeedLengthAverage>0) {
            $SpeedTimeAverage = $SpeedLengthAverage*3.6/$r_Fine['SpeedControl'];
            $Content = str_replace("{SpeedTimeAverage}", NumberDisplay($SpeedTimeAverage), $Content);
            $Content = str_replace("{SpeedLengthAverage}", $SpeedLengthAverage, $Content);
        }
        
        $Content = str_replace("{DetectorId}",$DetectorTitle,$Content);
        $Content = str_replace("{Speed}",$r_Fine['Speed'],$Content);
        $Content = str_replace("{SpeedLimit}",NumberDisplay($r_Fine['SpeedLimit']),$Content);
        $Content = str_replace("{SpeedControl}",NumberDisplay($r_Fine['SpeedControl']),$Content);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //Fine///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Content = str_replace("{FineDate}",DateOutDB($r_Fine['FineDate']),$Content);
        $Content = str_replace("{FineTime}", ($r_Fine['FineTime'] != "" || $r_Fine['FineTime'] || null) ? TimeOutDB($r_Fine['FineTime']) : "", $Content);
        $Content = str_replace("{VehicleTypeId}",StringOutDB($r_Fine['VehicleTitle'.$a_Lan[$n_LanguageId]]),$Content);
        $Content = str_replace("{VehiclePlate}",StringOutDB($r_Fine['VehiclePlate']),$Content);
        $Content = str_replace("{VehicleBrand}",StringOutDB($r_Fine['VehicleBrand']),$Content);
        $Content = str_replace("{VehicleModel}",StringOutDB($r_Fine['VehicleModel']),$Content);
        $Content = str_replace("{VehicleColor}",StringOutDB($r_Fine['VehicleColor']),$Content);
        $Content = str_replace("{IuvCode}", StringOutDB($r_Fine['IuvCode']), $Content);
        $Content = str_replace("{Code}",$r_Fine['Code'],$Content);
        
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
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //Controller/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $str_ControllerName = trim(StringOutDB($r_Fine['ControllerQualification']." ".$r_Fine['ControllerName']));
        $str_ControllerCode = trim($r_Fine['ControllerCode']);
        
        $rs_FineAdditionalController = $rs->Select('V_AdditionalController', "FineId=" . $FineId);
        while ($r_FineAdditionalController = mysqli_fetch_array($rs_FineAdditionalController)){
            $str_ControllerCode="";
            $str_ControllerName .= ", ".trim(StringOutDB($r_FineAdditionalController['ControllerQualification']." ".$r_FineAdditionalController['ControllerName']));
        }
        
        $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
        $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);
        $Content = str_replace("{ChiefControllerName}",StringOutDB($r_ChiefController['Name']),$Content);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////
        //126 BIS
        /////////////////////////////////////////////
        $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType1'];
        $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType2'];
        $str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
        
        $TotalFee = $n_TotFee+$AdditionalFee;
        $PartialFee = number_format($n_TotFee*FINE_PARTIAL,2);
        $TotalDiscountFee = $n_TotPartialFee + $ResearchFee + $NotificationFee;
        
        //TODO aggiunto per PagoPA, potrebbe necessitare modifiche
        $MaxFee = $n_TotMaxFee * FINE_MAX;
        //TODO aggiunto per PagoPA, potrebbe necessitare modifiche
        $TotalPartialFee = $PartialFee + $AdditionalFee;
        //TODO aggiunto per PagoPA, potrebbe necessitare modifiche
        $TotalMaxFee = $MaxFee + $AdditionalFee;
        
        $article126bis = null;
        if($r_ArticleTariff['126Bis']==1){
            $query = "Select ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId ";
            $query.= "WHERE A.CityId='".$_SESSION['cityid']."' AND A.Article=126 AND A.Letter='bis' AND ART.Year = ".$_SESSION['year'];
            
            $articles126bis = $rs->SelectQuery($query);
            $article126bis = mysqli_fetch_array($articles126bis);
        }
        
        $str_Date = "";
        if ($r_Customer['ManagerSignName'] == "") {
            $str_Date = $r_Fine['CityTitle'] . " " . DateOutDB($NotificationDate);
        } else {
            $str_Date =  $r_Customer['ManagerSignName'] . " " . DateOutDB($NotificationDate);
        }
        
        $Content = str_replace("{DecurtationPoints}",$r_ArticleTariff['LicensePoint'],$Content);
        $Content = str_replace("{Fee126bis}", (!empty($article126bis) ? NumberDisplay($article126bis['Fee']) :  NumberDisplay(0)), $Content);
        $Content = str_replace("{MaxFee126bis}", (!empty($article126bis) ? NumberDisplay($article126bis['MaxFee']) :  NumberDisplay(0)), $Content);
        
        $Content = str_replace("{PreviousProtocolId}",$PreviousProtocolId,$Content);
        $Content = str_replace("{PreviousProtocolYear}",$PreviousProtocolYear,$Content);
        $Content = str_replace("{PreviousFineDate}",$PreviousFineDate,$Content);

        $Content = str_replace("{Locality}",StringOutDB($r_Fine['CityTitle']),$Content);
        $Content = str_replace("{Address}",StringOutDB($r_Fine['Address']),$Content);

        $Content = str_replace("{BankOwner}",StringOutDB($r_Customer['ForeignBankOwner']),$Content);
        $Content = str_replace("{BankName}",StringOutDB($r_Customer['ForeignBankName']),$Content);
        $Content = str_replace("{BankAccount}",$r_Customer['ForeignBankAccount'],$Content);
        $Content = str_replace("{BankSwift}",$r_Customer['ForeignBankSwift'],$Content);
        $Content = str_replace("{BankIban}",$r_Customer['ForeignBankIban'],$Content);
        
        $Content = str_replace("{Date}", $str_Date, $Content);
        
        $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
        $Content = str_replace("{ManagerDataEntryName}", StringOutDB($r_Customer['ManagerDataEntryName']), $Content);
        $Content = str_replace("{ManagerProcessName}", StringOutDB($r_Customer['ManagerProcessName']), $Content);
        
        $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
        $Content = str_replace("{ManagerAddress}", StringOutDB($r_Customer['ManagerAddress']), $Content);
        $Content = str_replace("{ManagerZIP}", $r_Customer['ManagerZIP'], $Content);
        $Content = str_replace("{ManagerCity}", StringOutDB($r_Customer['ManagerCity']), $Content);
        $Content = str_replace("{ManagerProvince}", StringOutDB($r_Customer['ManagerProvince']), $Content);
        $Content = str_replace("{ManagerSubject}", StringOutDB($ManagerSubject), $Content);
        $Content = str_replace("{ManagerCountry}", StringOutDB($r_Customer['ManagerCountry']), $Content);
        $Content = str_replace("{ManagerPhone}", $r_Customer['ManagerPhone'] ? 'TEL: '.$r_Customer['ManagerPhone'] : '', $Content);
        $Content = str_replace("{ManagerFax}", $r_Customer['ManagerFax'] ? 'FAX: '.$r_Customer['ManagerFax'] : '', $Content);
        $Content = str_replace("{ManagerMail}", $r_Customer['ManagerMail'] ? 'MAIL: '.$r_Customer['ManagerMail'] : '', $Content);
        $Content = str_replace("{ManagerPEC}", $r_Customer['ManagerPEC'], $Content);
        $Content = str_replace("{ManagerWeb}", $r_Customer['ManagerWeb'], $Content);
        $Content = str_replace("{ManagerTaxCode}", $r_Customer['ManagerTaxCode'], $Content);
        //Località firma verbale
        $Content = str_replace("{ManagerSignName}", $r_Customer['ManagerSignName'], $Content);

        $Content = str_replace("{Fee}",NumberDisplay($n_TotFee),$Content);
        $Content = str_replace("{TotalFee}",NumberDisplay($TotalFee),$Content);

        $Content = str_replace("{TotalDiscountFee}",NumberDisplay($TotalDiscountFee),$Content);

        $Content = str_replace("{PartialFee}",NumberDisplay($PartialFee),$Content);
        $Content = str_replace("{ResearchFee}",NumberDisplay($ResearchFee),$Content);
        $Content = str_replace("{NotificatioFee}",NumberDisplay($NotificationFee),$Content);
        $Content = str_replace("{ChargeTotalFee}",NumberDisplay($ChargeTotalFee),$Content);

        $Content = str_replace("{ProtocolYear}",$ProtocolYear,$Content);
        $Content = str_replace("{ProtocolLetter}",$str_ProtocolLetter,$Content);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //Judicial Office////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Content = str_replace("{Judge}", isset($a_Office[1]) ? utf8_decode($a_Office[1]['OfficeTitle'.$a_Lan[$n_LanguageId]]) : "", $Content);
        $Content = str_replace("{JudgeCity}", isset($a_Office[1]) ? $a_Office[1]['City'] : "" , $Content);
        $Content = str_replace("{JudgeProvince}", isset($a_Office[1]) ? $a_Office[1]['Province'] : "" , $Content);
        $Content = str_replace("{JudgeAddress}", isset($a_Office[1]) ? $a_Office[1]['Address'] : "" , $Content);
        $Content = str_replace("{JudgeZIP}", isset($a_Office[1]) ? $a_Office[1]['ZIP'] : "" , $Content);
        $Content = str_replace("{JudgePhone}", isset($a_Office[1]) ? $a_Office[1]['Phone'] : "" , $Content);
        $Content = str_replace("{JudgeFax}", isset($a_Office[1]) ? $a_Office[1]['Fax'] : "" , $Content);
        $Content = str_replace("{JudgeMail}", isset($a_Office[1]) ? $a_Office[1]['Mail'] : "" , $Content);
        $Content = str_replace("{JudgePEC}", isset($a_Office[1]) ? $a_Office[1]['PEC'] : "" , $Content);
        $Content = str_replace("{JudgeWeb}", isset($a_Office[1]) ? $a_Office[1]['Web'] : "" , $Content);
        
        $Content = str_replace("{Prefect}", isset($a_Office[2]) ? utf8_decode($a_Office[2]['OfficeTitle'.$a_Lan[$n_LanguageId]]) : "", $Content);
        $Content = str_replace("{PrefectCity}", isset($a_Office[2]) ? $a_Office[2]['City'] : "" , $Content);
        $Content = str_replace("{PrefectProvince}", isset($a_Office[2]) ? $a_Office[2]['Province'] : "" , $Content);
        $Content = str_replace("{PrefectAddress}", isset($a_Office[2]) ? $a_Office[2]['Address'] : "" , $Content);
        $Content = str_replace("{PrefectZIP}", isset($a_Office[2]) ? $a_Office[2]['ZIP'] : "" , $Content);
        $Content = str_replace("{PrefectPhone}", isset($a_Office[2]) ? $a_Office[2]['Phone'] : "" , $Content);
        $Content = str_replace("{PrefectFax}", isset($a_Office[2]) ? $a_Office[2]['Fax'] : "" , $Content);
        $Content = str_replace("{PrefectMail}", isset($a_Office[2]) ? $a_Office[2]['Mail'] : "" , $Content);
        $Content = str_replace("{PrefectPEC}", isset($a_Office[2]) ? $a_Office[2]['PEC'] : "" , $Content);
        $Content = str_replace("{PrefectWeb}", isset($a_Office[2]) ? $a_Office[2]['Web'] : "" , $Content);
        
        //Per l'estero viene usata la Prefettura nelle variabili del tribunale quindi se tribunale non c'è usiamo la prefettur
        $Content = str_replace("{Court}", (isset($a_Office[2]) ? utf8_decode($a_Office[2]['OfficeTitle'.$a_Lan[$n_LanguageId]]) : ""), $Content);
        $Content = str_replace("{CourtCity}", (isset($a_Office[2]) ? $a_Office[2]['City'] : "" ), $Content);
        $Content = str_replace("{CourtProvince}", (isset($a_Office[2]) ? $a_Office[2]['Province'] : "" ), $Content);
        $Content = str_replace("{CourtAddress}", isset($a_Office[2]) ? $a_Office[2]['Address'] : "" , $Content);
        $Content = str_replace("{CourtZIP}", isset($a_Office[2]) ? $a_Office[2]['ZIP'] : "" , $Content);
        $Content = str_replace("{CourtPhone}", isset($a_Office[2]) ? $a_Office[2]['Phone'] : "" , $Content);
        $Content = str_replace("{CourtFax}", isset($a_Office[2]) ? $a_Office[2]['Fax'] : "" , $Content);
        $Content = str_replace("{CourtMail}", isset($a_Office[2]) ? $a_Office[2]['Mail'] : "" , $Content);
        $Content = str_replace("{CourtPEC}", isset($a_Office[2]) ? $a_Office[2]['PEC'] : "" , $Content);
        $Content = str_replace("{CourtWeb}", isset($a_Office[2]) ? $a_Office[2]['Web'] : "" , $Content);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
       
        /////////////////////////////////////////////
        //Protocol
        /////////////////////////////////////////////
        if($ultimate){
            if($r_Fine['ProtocolId'] > 0) $ProtocolNumber = $r_Fine['ProtocolId'];
            else if($r_Fine['ProtocolIdAssigned'] == 0){
                $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);
                $r_Protocol = mysqli_fetch_array($rs_Protocol);
                $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                $ProtocolNumber = $n_Protocol + 1;
            } else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];

            $Content = str_replace("{ProtocolId}",$ProtocolNumber,$Content);

        }else{
            if($ProtocolNumber==0){
                $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);
                $r_Protocol = mysqli_fetch_array($rs_Protocol);
                $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                $ProtocolNumber = $n_Protocol;
            }
            $ProtocolNumber++;
            $Content = str_replace("{ProtocolId}",$ProtocolNumber." - PROVV",$Content);
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        /////////////////////////////////////////////
        //SOTTOTESTI FISSI
        /////////////////////////////////////////////
        
        $HeaderFineNumber = "{{HeaderFineNumber}}";
        
        $a_FixedVariables = $rs->getResults($rs->Select("FormVariable", "CityId='{$_SESSION['cityid']}' AND Type=1 AND LanguageId=$n_LanguageId And NationalityId=2 AND FormTypeId=".TIPO_SOTTOTESTI_FISSI_EST));
        foreach ($a_FixedVariables as $fixedVar){
            if ($fixedVar['Id'] == $HeaderFineNumber){
                $HeaderFineNumber = $fixedVar['Content'];
                $HeaderFineNumber = str_replace("{ProtocolId}", $ProtocolNumber . ($ultimate ? "" : " - PROVV"), $HeaderFineNumber);
                $HeaderFineNumber = str_replace("{ProtocolYear}", $ProtocolYear, $HeaderFineNumber);
                $HeaderFineNumber = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $HeaderFineNumber);
                $HeaderFineNumber = str_replace("{Code}", $r_Fine['Code'], $HeaderFineNumber);
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //INTESTAZIONE CRON E REF////////////////////////////////////////////////////////////////////////////////////
        if($r_Customer['PDFRefPrint'] == 1 && $r_Fine['KindSendDate']){
            $pdf->SetFont('', 'B', 10, '', true);
            $pdf->MultiCell(80, 0, 'Cron. Nr: '.$ProtocolNumber.(!$ultimate ? ' - PROVV' : '').'/'.$ProtocolYear.'/'.$str_ProtocolLetter, 0, 'L', 1, 1, 10, 40, true);
            $pdf->SetFont('', '', 10, '', true);
            $pdf->MultiCell(80, 0, 'Invito Nr: '.$r_Fine['Code'], 0, 'L', 1, 1, 10, '', true);
            $pdf->SetFont('', '', 8, '', true);
        } else {
            $pdf->SetFont('', '', 10, '', true);
            $pdf->writeHTMLCell(80, 0, 10, 40, $HeaderFineNumber, 0, 1, false);
            $pdf->SetFont('', '', 8, '', true);
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //IMPOSTA COORDINATA INIZIO TESTO DINAMICO
        $pdf->SetXY($TextStartCoord['X'], $TextStartCoord['Y']);
        
        //Nome del file/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $RndCode = "";
        for($i=0;$i<5;$i++){
            $n = rand(1, 24);
            $RndCode .= substr($strCode,$n,1);
            $n = rand(0, 9);
            $RndCode .= $n;
        }
        
        $strProtocolNumber = "";
        for($k=strlen((string)$ProtocolNumber);$k<9;$k++)
        {
            $strProtocolNumber.= "0";
        }
        $strProtocolNumber.=$ProtocolNumber;
        
        $Documentation = $ProtocolYear."_".$strProtocolNumber."_".date("Y-m-d")."_".$_SESSION['cityid']."_".$RndCode.".pdf";
        $a_DocumentationFineZip[$FineId][] = $Documentation;
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //PAGOPA//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $PagoPACode1 = $r_Fine['PagoPA1'];
        $PagoPACode2 = $r_Fine['PagoPA2'];
        //se è abilitato pagoPA interroghiamo i servizi altrimenti e salviamo gli importi
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
            
            //$GenreParemeter D è per ditta/impresa e P per privato
            $GenreParemeter = ($trespasser['Genre'] == "D")? "D" : "P";
            //Kind='S' è tipologia sanzione
            $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
            $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
            
//             if(mysqli_num_rows($rs_PagoPAServiceParameter)>0) {
//                 $r_PagoPAServiceParameter = mysqli_fetch_array($rs_PagoPAServiceParameter);
//                 $tipoContabilità = $r_PagoPAServiceParameter['Type'];
//                 $codiceContabilità = $r_PagoPAServiceParameter['Code'];
//                 $tipoDovuto = $r_PagoPAServiceParameter['TypeDue'];
//             }
            
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
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
//         $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
//         $TCPDF_pagebreak = '<br pagebreak="true" />';
//         $Content = preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
        //
        
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->SetPrintHeader(false);
        
        //TODO MONITORARE NEL CASO IL PDF VENGA STAMPATO VUOTO
        if (!preg_match('!!u', $Content))
        {
            $Content = StringOutDB($Content);
        }
        
        //#writeHTML(html, ln = true, fill = 0, reseth = false, cell = false, align = '')
        $pdf->writeHTML($Content, true, $ultimate ? 0 : 1, true, false, '');
        
        //Conta le pagine attuali, se sono dispari aggiunge una pagina bianca in fondo
        $PageNo= $pdf->PageNo();
        if($PageNo%2 == 1){
            $pdf->AddPage();
        }
        
//////////QRCODE O AVVISO DI PAGAMENTO////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Bollettino1Fee = $chk_ReducedPayment ? $TotalPartialFee : $TotalFee;
        $Bollettino2Fee = $chk_ReducedPayment ? $TotalFee : $TotalMaxFee;
        $b_ErroreAvviso = false;
        
        //Se l'ente è abilitato alla stampa degli avvisi di pagamento stampa quelli, altrimenti aggiunge i qrcode alla vecchia maniera
        if($r_Customer['PagoPAPaymentNoticeForeign'] > 0 && $r_Customer['PagoPAPayment'] > 0){
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
            
            try {
                $o_Avviso = new Avviso($oggettoAvviso, $_SESSION['blazon']);
                $o_Ente = new Ente($r_Customer['ManagerName'], $r_Customer['ManagerSector'], buildManagerInfo($r_Customer), trim($r_Customer['ManagerTaxCode']), $r_Customer['PagoPACBILL']);
                $o_Destinatario = new Destinatario($TrespasserTaxCode, $TrespasserName, $str_TrespasserAddress);
                $o_Importo1 = new Importo($Bollettino1Fee, $PagoPAPaymentNotice1, $nomeImporto1, $descImporto1);
                $o_Importo2 = new Importo($Bollettino2Fee, $PagoPAPaymentNotice2, $nomeImporto2, $descImporto2);
                
                $avviso = new ModelloCDS($o_Avviso, $o_Ente, $o_Destinatario, $o_Importo1, $o_Importo2);
                if ($b_PrintBillPagoPA){
                    $avviso->setBollettino(new Bollettino($str_PostalAuthorizationPagoPA, $r_Customer['ForeignBankAccount'], $r_Customer['ForeignBankOwner'], $r_Customer['ForeignPostalTypePagoPA'], $causaleBollettino));
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
        
        if ($ultimate){

            $aInsert = array(
                array('field'=>'NotificationTypeId','selector'=>'value','type'=>'int','value'=>$NotificationTypeId,'settype'=>'int'),
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Fine['TrespasserId'],'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['TrespasserTypeId'],'settype'=>'int'),
                array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$CustomerFee,'settype'=>'flt'),
                array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$NotificationFee,'settype'=>'flt'),
                array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$ResearchFee,'settype'=>'flt'),
                array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$SelectChiefControllerId,'settype'=>'int'),
                array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
            );
            $a_InsFineHistory[] = $aInsert;
            //$rs->Insert('FineHistory',$aInsert);

            $aUpdate = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype' => 'int'),
                array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber, 'settype' => 'int'),
                array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','value'=>$SelectChiefControllerId,'settype' => 'int'),
            );
            
            $a_UpdFine[] = $aUpdate;
            //$rs->Update('Fine',$aUpdate, 'Id='.$FineId);

            $aInsert = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2),
                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
            );
            $a_InsFineDocumentation[] = $aInsert;
            //$rs->Insert('FineDocumentation',$aInsert);

            if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId)) {
                mkdir(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId, 0777);
            }

        }
        
        //SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE////////////////////////////////////////////
        if (!$ultimate){
            applyTemporaryLabel($pdf);
        }
        
        $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid']."/".$Documentation, "F");
        
        //Allega l'avviso di pagamento generato se era abilitata la gestione
        if($r_Customer['PagoPAPaymentNoticeForeign'] > 0 && $b_PagoPAEnabled > 0 && !$b_ErroreAvviso){
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
                
                $n_PageCount = $pdf_unionavviso->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
                for ($p = 1; $p <= $n_PageCount; $p++) {
                    $tmp_Page = $pdf_unionavviso->ImportPage($p);
                    $tmp_Size = $pdf_unionavviso->getTemplatesize($tmp_Page);
                    $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                    $pdf_unionavviso->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                    $pdf_unionavviso->useTemplate($tmp_Page);
                }
            } catch (Exception $e) {
                if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
                trigger_error("<CREAVERBALIDINAMICI> ATTENZIONE -> Errore nell\'unione del pdf dell'avviso di pagamento avviso_$Documentation: $e",E_USER_WARNING);
                $str_Warning .= 'ID '.$FineId.': Errore nella fusione del verbale e dell\'avviso di pagamento PagoPA. Contattare l\'amministrazione di sistema.<br>';
            }
            
            $pdf_unionavviso->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
            
            unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
        }
        
        //PARTE DB///////////////////////////////////////////////////////////////////////////////////////////////////
        //Se l'id del verbale non è fra quelli di cui PagoPA ha fallito, esegue le query e aggiunge i pdf generati al pdf unito
        if(!in_array($FineId, $a_FailedPagoPA)){
            if($ultimate){
                executeQueries('Insert', 'FineHistory', $a_InsFineHistory);
                executeQueries('Insert', 'FineDocumentation', $a_InsFineDocumentation);
                executeQueries('Update', 'Fine', $a_UpdFine, "Id=$FineId");
            }
            
            foreach($a_DocumentationFineZip[$FineId] as $Doc){
                //UNION///////////////////////////////////////////////////////////////////////////////////////////////////////
                $n_PageCount = $pdf_union->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc);
                for ($p = 1; $p <= $n_PageCount; $p++) {
                    
                    $tmp_Page = $pdf_union->ImportPage($p);
                    $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                    
                    $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                    
                    $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                    $pdf_union->useTemplate($tmp_Page);
                }
                /////////////////////////////////////////////////////////////////////////////////////////////////////////
            }
        } else {
            foreach($a_DocumentationFineZip[$FineId] as $Doc){
                unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if(!$b_DisableTransaction) $rs->End_Transaction();
    }
    

    if ($ultimate){
        
        foreach ($a_DocumentationFineZip as $DocFineId => $a_Doc){
            if(!in_array($DocFineId, $a_FailedPagoPA)){
                foreach ($a_Doc as $Doc){
                    copy(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc, FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . "/" . $Doc);
                    unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
                }
            }
        }
        
        if (!is_dir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/create')) {
            mkdir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/create', 0777);
        }

        $FileName = $_SESSION['cityid']."_".date("Y-m-d_H-i-s").".pdf";

        $pdf_union->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
        $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
    }
    else{
        foreach ($a_DocumentationFineZip as $DocFineId => $a_Doc){
            if(!in_array($DocFineId, $a_FailedPagoPA)){
                foreach ($a_Doc as $Doc){
                    unlink(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
                }
            }
        }
        $FileName = 'export_dynamic_fine_f.pdf';

        $pdf_union->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
        $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/'.$FileName;
    }

}

if($ultimate){
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
    } else $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);

if(!$b_DisableTransaction) $rs->Begin_Transaction();
$rs->Update('LockedPage',$aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
if(!$b_DisableTransaction) $rs->End_Transaction();


