<?php
require_once(CLS .'/cls_literal_number.php');
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

function buildHeader($r_Customer){
    $str_Header = '<span style="line-height:1.1">';
    if($r_Customer['ManagerSector'] != ''){
        $str_Header .= $r_Customer['ManagerSector'] != '' ? $r_Customer['ManagerSector'] : '';
        $str_Header .= '<br>';
    }
    if($_SESSION['cityid']=="H452"){
        $str_Header .= 'Art.57 CPP e Art.11 c.1 L.a) e b) CDS<br>';
    } else if($r_Customer['ManagerAddress'] != ''){
        $str_Header .= $r_Customer['ManagerAddress'].'<br>';
    }
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ManagerPhone'] != ''){
        $str_Header .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Header .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Header .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Header .= $r_Customer['ManagerPhone'] ? 'TEL: '.$r_Customer['ManagerPhone'] : '';
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

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
//NOTA BENE: il blocco della tabella serve ad impedire di creare atti con cronologici duplicati
//es: si creano verbali dinamici nello stesso momento in cui si creano verbali PEC
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_createdynamic_pec.php'.$Filters));
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

$privateKeyFound = false;
//Controlla l'esistenza della chiave privata per l'utente se la firma è abilitata
if(file_exists(SIGNATURES . '/' . $_SESSION['userid'].CERT_EXTENSION)){
    $privateKeyFound = true;
}

if (!is_dir(NATIONAL_PEC . "/" . $_SESSION['cityid'])) {
    mkdir(NATIONAL_PEC . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(NATIONAL_FINE . "/" . $_SESSION['cityid'])) {
    mkdir(NATIONAL_FINE . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(SIGNED_FOLDER)) {
    mkdir(SIGNED_FOLDER, 0777);
}

if (!is_dir(TOSIGN_FOLDER)) {
    mkdir(TOSIGN_FOLDER, 0777);
}

if (!is_dir(SIGNED_FOLDER . "/" . $_SESSION['cityid'])) {
    mkdir(SIGNED_FOLDER . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(TOSIGN_FOLDER . "/" . $_SESSION['cityid'])) {
    mkdir(TOSIGN_FOLDER . "/" . $_SESSION['cityid'], 0777);
}


//TODO capire dove venivano valorizzati e se servono
$CreationType = 1;
$RegularPostalFine = 0;

//Coordinata inizio stampa testo dinamico
$TextStartCoord = array('X'=>10, 'Y'=>92);

$str_Warning = '';
$str_Error = '';

$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$a_Documentation = array();
//Contiene i FineId di cui non è stato possibile generare/aggiornare PagoPA
$a_FailedPagoPA = array();

$a_GenreLetter = array("D" => "Spett.le", "M" => "Sig.", "F" => "Sig.ra");

$ProtocolNumber = 0;

//personalizzazione per gli utenti di tipo 2 che sono tutti ad oggi accertatori della provincia di Savona
if ($_SESSION['usertype'] == 2) {
    $rs_Time = $rs->SelectQuery("SELECT MAX( ControllerTime ) ControllerTime FROM Fine WHERE ControllerDate='" . date("Y-m-d") . "'");
    $r_Time = mysqli_fetch_array($rs_Time);
    $Time = ($r_Time['ControllerTime'] == "") ? date("H:i:s") : $r_Time['ControllerTime'];
}

//FIRMATARIO/////
$rs_SignController = $rs->Select("Controller", "Id=".$_SESSION['controllerid']);
$r_SignController = mysqli_fetch_array($rs_SignController);
$SignController = (isset($r_SignController['Qualification']) ? $r_SignController['Qualification'].' ' : '').$r_SignController['Name'].' (Matr: '.$r_SignController['Code'].')';
////////////////

$NotificationDate = (strlen(trim($CreationDate)) == 0) ? date("Y-m-d") : DateInDB($CreationDate);


$a_Lan = unserialize(LANGUAGE);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT_CITYID)[$_SESSION['cityid']];
$a_AdditionalMass = unserialize(ADDITIONAL_MASS_CITYID)[$_SESSION['cityid']];
if($a_AdditionalNight == null)
    $a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
    
    if($a_AdditionalMass == null)
        $a_AdditionalMass = unserialize(ADDITIONAL_MASS);
$a_FineSendType = unserialize(FINE_SEND_TYPE);

$n_LanguageId = 1;
$ZoneId = 0;
$ultimate = CheckValue('ultimate', 'n');

$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}

$StatusTypeId = 12;
$DocumentationTypeId = 2;
$NotificationTypeId = 15;



//TODO valutare se serve
//$FinePDFList = $r_Customer['FinePDFList'];


$int_ContFine = 0;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//CONTROLLI PRELIMINARI//////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=1 AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_PostalAuthorization = trim($r_PrintParameter['NationalPostalAuthorization']) ?? '';
$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['NationalPostalAuthorizationPagoPA'] ?? '');
//////////////////////////////////////////////////

//Bug 2869 PagoPA funziona per ora solo per CDS
$b_PagoPAEnabled = $r_Customer['PagoPAPayment'] > 0 && $_SESSION['ruletypeid'] == RULETYPE_CDS;
$b_PrintBill = ($b_PagoPAEnabled ? $r_Customer['PagoPAPaymentNoticeNationalPEC'] <= 0 : true) && !empty($r_Customer['NationalPostalType']);
$b_PrintBillPagoPA = $b_PagoPAEnabled && !empty($r_Customer['NationalPostalTypePagoPA']) && $r_Customer['PagoPAPaymentNoticeNationalPEC'] > 0;

//Controlla verbali da protocollare
$rs_row = $rs->Select('DocumentationProtocol', "UserId=" . $_SESSION['userid'] . " AND CityId='" . $_SESSION['cityid'] . "'");
if (mysqli_num_rows($rs_row) > 0) {
    $str_Error .= "Ci sono verbali da protocollare. Impossibile crearne altri.<br>";
}

//Controlli parametri
if($b_PagoPAEnabled){
    //Se l'ente non ha CF/PIVA impostati
    if(empty($r_Customer['ManagerTaxCode'])){
        $str_Error .= 'È necessario specificare il codice fiscale dell\'ente per il funzionamento della gestione PagoPA (Ente > Gestione Ente > Indirizzo).<br>';
    }
}
if($b_PrintBill){
    if(empty($r_Customer['NationalBankAccount'])){
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

//Controlli verbali
if (isset($_POST['checkbox'])) {
    foreach ($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);
        
        if ($r_Fine['StatusTypeId'] !=7 && $r_Fine['StatusTypeId'] != 9 && $r_Fine['StatusTypeId'] != 10 && $r_Fine['StatusTypeId'] != 14) {
            $str_Error .= "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Azione non prevista per verbale in stato ".$r_Fine['StatusTypeId'].".";
        }
        
        $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);
        
        if ($trespasser['CountryId'] == 'ZZZZ') {
            $str_Error .= "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Stato del trasgressore non presente.";
        }
        
        if ($r_Fine['ControllerId'] == "" && $_SESSION['usertype'] != 2) {
            $str_Error .= "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Manca il verbalizzante.";
        } else if (($r_Fine['ControllerId'] == "" && $_SESSION['usertype'] == 2 && $ultimate) || ($_SESSION['controllerid']!=0 && $ultimate && $CreationType==5)) {
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

if(!empty($str_Error)){
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $_SESSION['Message']['Error'] = $str_Error;
    header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_createdynamic_pec.php'.$Filters));
    DIE;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//SERVIZIO PAGOPA ENTE
//se è abilitato pagoPA interroghiamo la base dati per prendere le configurazioni
if($b_PagoPAEnabled){
    $pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
    $pagopaService=mysqli_fetch_array($pagopaServicequery);
}

/*
    La segnalazione della postazione di rilevamento della velocita' dei veicoli e' stata effettuata mediante
    esposizione di appositi cartelli segnaletici informativi, posizionati prima della postazione di rilevamento,
    ai sensi del D. Interm. Del 15 agosto 2007, e in modo da garantire l'avvistamento della postazione di rilevamento
    della velocita' dei veicoli e la salvaguardia della sicurezza della circolazione stradale.
*/

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
$a_DetectorTitleIta = array(); 

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
    $a_DetectorTitleIta[$r_Detector['Id']] = ($r_Detector['TitleIta']=="") ? "" : $r_Detector['TitleIta'];
    
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
    $a_Office[$office['OfficeId']]['OfficeTitleIta'] = isset($office['OfficeTitleIta']) ? StringOutDB($office['OfficeTitleIta']) : "";
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

if (isset($_POST['checkbox'])) {
    $rs->Start_Transaction();
    
    //Inizializza pdf-union
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array('helvetica', '', 8));
    $pdf_union->setFooterFont(array('helvetica', '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);

    foreach ($_POST['checkbox'] as $FineId) {
        $rs->Begin_Transaction();
        
        $a_Documentation[$FineId] = array();
        $a_InsFineHistory= array();
        $a_InsFineDocumentation = array();
        $a_InsFineNotification = array();
        $a_UpdFine = array();
        $a_UpdFineArticle = array();
        $a_UpdFineAdditionalArticle = array();

        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=$FineId");
        
        //TIPI TRASGRESSORE
        $a_TrespasserTypes = array();
        $rs_Trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $FineId);
        while ($r_Trespassers = mysqli_fetch_assoc($rs_Trespassers)){
            $a_TrespasserTypes['T'.$r_Trespassers['TrespasserTypeId']] = $r_Trespassers;
        }
        /////////////////////////////////////////////
        
        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
        	
        	//TCPDF/////////////////////////////////////////////////////////
        	$pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);
        	$pdf->TemporaryPrint = $ultimate;
        	$pdf->NationalFine = 1;
        	$pdf->CustomerFooter = 0;
        	//$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);
        	$pdf->SetCreator(PDF_CREATOR);
        	$pdf->SetAuthor($_SESSION['citytitle']);
        	$pdf->SetTitle('Fine PEC');
        	$pdf->SetSubject('');
        	$pdf->SetKeywords('');
        	$pdf->setHeaderFont(array('helvetica', '', 8));
        	$pdf->setFooterFont(array('helvetica', '', 8));
        	$pdf->SetMargins(10, 10, 10);
        	/////////////////////////////////////////////////////////
        	
            $chk_180            = false;
            $chk_126Bis         = false;
            $chk_ReducedPayment = false;
            $n_LicensePoint     = 0;
            $n_TotPartialFee    = 0;
            $n_TotFee           = 0;
            $n_TotMaxFee        = 0;

            $ViolationTypeId = $r_Fine['ViolationTypeId'];
            $ProtocolYear = $r_Fine['ProtocolYear'];

            if(trim($r_Fine['ArticleLetterAssigned'])!=''){
                $NationalProtocolLetterType1 = $r_Fine['ArticleLetterAssigned'];
                $NationalProtocolLetterType2 = $r_Fine['ArticleLetterAssigned'];
            } else if(trim($r_Fine['ViolationLetterAssigned'])!=''){
                $NationalProtocolLetterType1 = $r_Fine['ViolationLetterAssigned'];
                $NationalProtocolLetterType2 = $r_Fine['ViolationLetterAssigned'];
            } else {
                $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType1'];
                $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType2'];
            }

            //In questo caso "Id" corrisponde al RuleTypeId
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "' AND Id=".$_SESSION['ruletypeid']);
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            
            $rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
            $r_FineOwner = mysqli_fetch_array($rs_FineOwner);
            
            $RuleTypeId = $r_RuleType['Id'];

            //$ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            $FormTypeId = $r_RuleType['NationalFormId'];

            $a_PrintObject = explode("*", $r_RuleType['PrintObject' . $a_Lan[$n_LanguageId]]);

            //testo invito in AG - - Garlasco, Albuzzano e Borgo San Siro
            if((($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2") || ($r_Fine['Article']==80 && $r_Fine['Paragraph']=="14")) && $r_Fine['KindSendDate']!=''){
                $rs_Form= $rs->Select('FormDynamic', "FormTypeId=101 AND CityId='" . $_SESSION['cityid'] . "' AND NationalityId=1");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            } 
            //Tolta cablatura per Arcola per i testi della velocità
            /*else if($r_Fine['Article']==142){
                $rs_Form= $rs->Select('Form', "FormTypeId=102 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];
            }*/

            //Testo particolare per casistica che non conosciamo - Garlasco, Albuzzano e Borgo San Siro
            if($CreationType==5){
                $rs_Form= $rs->Select('Form', "FormTypeId=100 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            }

            //Se il verbale è di tipo contratto viene generato il pdf con testo tipo 40 che prevede solo relata e bollettini
            if($r_Fine['FineTypeId']==4) $FormTypeId=40;

            $ChargeTotalFee = 0;
            $NotificationFee = $r_Customer['NationalPECNotificationFee'];
            $ResearchFee = $r_Customer['NationalPECResearchFee'];

//Gestione forfettario, vale solo per verbali normali (principalmente estero)
//             
//             if ($r_Customer['NationalTotalFee'] > 0) 
//                 $ChargeTotalFee = $r_Customer['NationalTotalFee'];
//             else {
//                 if ($r_Customer['NationalNotificationFee'] > 0) {
//                     $NotificationFee = $r_Customer['NationalNotificationFee'];
//                 }
//                 $ResearchFee = $r_Customer['NationalResearchFee'];
//             }

            $int_ContFine++;

            //$pdf->Temporary();
            $pdf->SetPrintHeader(true);
            $pdf->RightHeader = true;
            $pdf->PrintObject1 = $a_PrintObject[0];
            $pdf->PrintObject2 = $a_PrintObject[1];
            $pdf->PrintObject3 = $a_PrintObject[2];
            $pdf->SetMargins(10, 8, 10);
            $pdf->SetCellPadding(0);

            $page_format = ($int_ContFine > 1) ? array('Rotate' => 45) : array();


            $pdf->AddPage('P', $page_format);
            //Se provvisorio evidenzia il testo in giallo
            $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Image($_SESSION['blazon'], 7, 7, 12, 17);

            $ManagerName = $r_Customer['ManagerName'];
            $pdf->customer = $ManagerName;
            
            $pdf->SetFont('helvetica', '', 8, '', true);
            
            $pdf->writeHTMLCell(73, 0, 22, 5.5, '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
            $pdf->LN(3);
            
            $pdf->writeHTMLCell(73, 0, 22, '', buildHeader($r_Customer), 0, 0, 1, true, 'L', true);
            
            if($r_Customer['ManagerPEC'] != ''){
                $pdf->MultiCell(85, 0, 'PEC: '.$r_Customer['ManagerPEC'], 0, 'L', 1, 1, 10, 30, true);
            }
            
            //Stampa le finestre delle buste
            $window = true;
            if (!$ultimate && $window){
                $pdf->RoundedRect(3.5, 5.3, 89.9, 21.3, 3, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(94.2, 37.6, 110, 44.1, 3, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(10.4, 50.9, 70, 29.9, 3, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(0.1, 0.1, 209.9, 89.7, 0.5, '1111', '', array('color' => array(0,0,255)), '');
                $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            }
            $pdf->SetFont('helvetica', '', 8, '', true);

            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);


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

            //TODO caso portato dall'originale create_fine_national, dove se l'articolo riguardava l'assicurazione scaduta,
            //prende una data da interfaccia (mgmt_violation_act) e calcola con la data di scadenza dell'assicurazione se applicare la riduzione
            //Su crea e firma verbali pec non è presente il campo quindi non entrerebbe mai in questo pezzo di codice
//             if($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2"){
//                 if($InsuranceDate!=""){
//                     $n_Day = DateDiff("D", $r_Fine['ExpirationDate'], DateInDB($InsuranceDate));

//                     if($n_Day<=30){
//                         $r_Fine['Fee'] = $r_Fine['Fee'] * FINE_INSURANCE_REDUCED;
//                         $r_Fine['MaxFee'] = $r_Fine['MaxFee'] * FINE_INSURANCE_REDUCED;


//                         $a_FineArticle = array(
//                             array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_Fine['Fee'], 'settype' => 'flt'),
//                             array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_Fine['MaxFee'], 'settype' => 'flt'),
//                         );

//                     }
//                 }
//             }


            $n_TotFee += $r_Fine['Fee'];
            $n_TotMaxFee += $r_Fine['MaxFee'];
            if ($r_ArticleTariff['ReducedPayment'] == 1) {
                $chk_ReducedPayment = true;
                $n_TotPartialFee += $r_Fine['Fee'] * FINE_PARTIAL;
            } else {
                $n_TotPartialFee += $r_Fine['Fee'];
            }


            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);
            
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
            $pdf->MultiCell(90, 0, strtoupper(StringOutDB($a_GenreLetter[$trespasser['Genre']].' '.(isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' ' . $trespasser['Name'])), 0, 'L', 1, 1, 110, 55.5, true);
            $pdf->MultiCell(90, 0, strtoupper(StringOutDB($str_TrespasserAddress != "" ? $str_TrespasserAddress : "")), 0, 'L', 1, 1, 110, '', true);
            $pdf->MultiCell(90, 0, StringOutDB($trespasser['ZIP']).' '.strtoupper(StringOutDB($trespasser['City'])).(!empty($trespasser['Province']) ? ' ('.strtoupper(StringOutDB($trespasser['Province'])).')' : ''), 0, 'L', 1, 1, 110, '', true);
            $pdf->SetFont('', '', 8, '', true);
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if (($r_Fine['Article'] == 126 AND $r_Fine['Paragraph'] == '0' AND $r_Fine['Letter'] == 'bis') || ($r_Fine['Article'] == 180 AND $r_Fine['Paragraph'] == '8' AND $r_Fine['Letter'] == '')) {
                $PreviousId = $r_Fine['PreviousId'];

                $rs_PreviousFine = $rs->Select('Fine', "Id=" . $PreviousId);
                $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);

                $PreviousProtocolId = $r_PreviousFine['ProtocolId'];
                $PreviousProtocolYear = $r_PreviousFine['ProtocolYear'];
                $PreviousFineDate = DateOutDB($r_PreviousFine['FineDate']);


            } else {
                $PreviousProtocolId = "";
                $PreviousProtocolYear = "";
                $PreviousFineDate = "";
            }


//             if($CreationType==5){
//                 $ResearchFee = $r_Customer['NationalPECResearchFee'];
//                 $NotificationFee = $r_Customer['NationalTotalFee']; //TODO forse sbagliato

//                 $AdditionalFee = $NotificationFee + $ResearchFee;

//             } else {
//                 $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);

//                 //ChargeTotalFee
//                 if ($ChargeTotalFee > 0) {
//                     $ResearchFee = $ChargeTotalFee - $postalcharge['Zone' . $ZoneId];
//                     $NotificationFee = $postalcharge['Zone' . $ZoneId];
//                 } else {
//                     if ($NotificationFee == 0) {
//                         $NotificationFee = $postalcharge['Zone' . $ZoneId];
//                     }
//                 }
//             }


            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;

            $AdditionalFee = $NotificationFee + $ResearchFee;
            
            

//             $CANFee = $postalcharge['CanFee'];
//             $CADFee = $postalcharge['CadFee'];

//             $AdditionalFeeCAN = $AdditionalFee + $CANFee;
//             $AdditionalFeeCAD = $AdditionalFee + $CADFee;
            
            //SCELTA VERBALIZZANTE///////////////////////////////////////////////////////////
            $ChiefControllerName = '';
            $ChiefControllerId = null;
            if($r_Fine['ReportChiefControllerId'] > 0){
                //Se è presente il verbalizzante nel verbale usa quello
                $ChiefControllerId = $r_Fine['ReportChiefControllerId'];
            } else if($r_Fine['UIFineChiefControllerId'] > 0){
                //Altrimenti se è presente un "verbalizzante stampa" usa quello
                $ChiefControllerId = $r_Fine['UIFineChiefControllerId'];
            } else if($SelectChiefControllerId > 0){
                //Altrimenti se è scelto da tendina usa quello
                $ChiefControllerId = $SelectChiefControllerId;
            }
            
            if($ChiefControllerId > 0){
                $rs_ChiefController = $rs->Select('Controller', "Id=$ChiefControllerId");
                $r_ChiefController = mysqli_fetch_array($rs_ChiefController);
                $ChiefControllerName = trim($r_ChiefController['Qualification'] . " " . $r_ChiefController['Name']);
            } 
            ///////////////////////////////////////////////////////////////////////////////
            
//             //(caso rinotifica) Controlla se era già stato salvato il verbalizzante scelto da interfaccia in crea verbali e usa quest'ultimo nella variabile {ChiefControllerName}
//             $b_ValidChiefController = true;
//             if ($r_Fine['UIFineChiefControllerId'] > 0) {
//                 $str_DateWhere = " AND ('$NotificationDate' >= FromDate OR FromDate IS NULL) AND ('$NotificationDate' <= ToDate OR ToDate IS NULL)";
//                 $rs_ChiefController = $rs->Select('Controller', "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Id =" . $r_Fine['UIFineChiefControllerId'].$str_DateWhere);
                
//                 if(mysqli_num_rows($rs_ChiefController) > 0){
//                     $r_ChiefController = mysqli_fetch_array($rs_ChiefController);
//                     $ChiefControllerName = trim($r_ChiefController['Qualification']." ".$r_ChiefController['Name']);
//                 } else $b_ValidChiefController = false;
//             } 
            
            /////////////////////////////////////////////
            //Additional Article
            /////////////////////////////////////////////
            $str_AdditionalArticle = "";
            
            if ($r_Fine['ArticleNumber'] > 1) {
                $str_AdditionalArticle = "Sono stati inoltre violati i seguenti articoli:";
                
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
                        $chk_ReducedPayment = $chk_ReducedPayment || true;
                        $n_TotPartialFee += $r_AdditionalArticle['Fee'] * FINE_PARTIAL;
                    } else {
                        $n_TotPartialFee += $r_AdditionalArticle['Fee'];
                    }
                    
                    $str_AdditionalArticleDescription = (strlen($r_AdditionalArticle['AdditionalArticleDescription' . LAN]) > 0) ? $r_AdditionalArticle['AdditionalArticleDescription' . LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];
                    
                    $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate']!="") ? DateOutDB($r_AdditionalArticle['ExpirationDate']) : "";
                    $str_AdditionalArticleDescription = str_replace("{ExpirationDate}", $str_ExpirationDate, $str_AdditionalArticleDescription);
                    
                    $Paragraph = ($r_AdditionalArticle['Paragraph'] == "0") ? "" : $r_AdditionalArticle['Paragraph'] . " ";
                    $Letter = ($r_AdditionalArticle['Letter'] == "0") ? "" : $r_AdditionalArticle['Letter'];
                    
                    $str_AdditionalArticle .= " Art. " . $r_AdditionalArticle['Article'] . "/" . $Paragraph . $Letter;
                    $str_AdditionalArticle .= " " . $str_AdditionalArticleDescription;
                    
                    if ($r_AdditionalArticle['PrefectureFixed'] == 1){
                        $str_AdditionalArticle .= "con importo fissato da prefettura di euro ".$r_AdditionalArticle['PrefectureFee']." in data ".DateOutDB($r_AdditionalArticle['PrefectureDate']);
                    }
                    $str_AdditionalArticle .= ".";
                    
                    //Aggiunta del testo secondario dell'articolo addizionale in caso di decurtazione punti
                    if ($r_AdditionalArticle['126Bis'] == 1){
                        $str_AdditionalArticle .= "<br>".$r_AdditionalArticle['AdditionalTextIta'];
                    }
                    
                }
            }

            $Content=getDynamicContent($FormTypeId,$_SESSION['cityid']);
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
                        $a_Types =  getFormVariables($var,$_SESSION['cityid'],$FormTypeId, 1, $n_LanguageId);
                        
                        //TODO Togliere la gestione della descrizione del trasgressore una volta che il sottotesto è definito su db
                        //Sottotesto trasgressore
                        if ($var == "{{TrespasserDescription}}"){
                            $Content = str_replace("{{TrespasserDescription}}", "", $Content);
                        }
                        
                        //Sottotesto pagamenti
                        if ($var == "{{PaymentDescription}}"){
                            $str_Payment = "";
                            $b_lumpsum = $r_Customer['LumpSum'] > 0 ? true : false;
                            $b_reduced = $r_ArticleTariff['ReducedPayment'] > 0 ? true : false;
                            
                            switch ([$b_lumpsum, $b_reduced]) {
                                case [false, true]:
                                    //Ridotto
                                    $str_Payment .= $a_Types[1].'<br>'; //PAGAMENTO IN MISURA RIDOTTA DEL 30%: ENTRO 5 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    $str_Payment .= $a_Types[2]; //PAGAMENTO IN MISURA DEL MINIMO EDITTALE: DAL 6° GIORNO ED ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    break;
                                case [false, false]:
                                    //NON Ridotto
                                    $str_Payment .= $a_Types[9].'<br>'; //PAGAMENTO IN MISURA DEL MINIMO EDITTALE: DAL 1° GIORNO ED ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    $str_Payment .= $a_Types[3]; //PAGAMENTO IN MISURA DELLA META' DEL MASSIMO: DAL 61° GIORNO ED ENTRO 6 MESI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    break;
                                case [true, true]:
                                    //Ridotto (CAN, CAD)
                                    $str_Payment .= $a_Types[4].'<br>'; //PAGAMENTO IN MISURA RIDOTTA DEL 30%: ENTRO 5 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    $str_Payment .= $a_Types[5].'<br>'; //PAGAMENTO IN MISURA DEL MINIMO EDITTALE: DAL 6° GIORNO ED ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    $str_Payment .= $a_Types[7].'<br>'; //CAN
                                    $str_Payment .= $a_Types[8]; //CAD
                                    break;
                                case [true, false]:
                                    //NON Ridotto (CAN, CAD)
                                    $str_Payment .= $a_Types[10].'<br>'; //PAGAMENTO IN MISURA DEL MINIMO EDITTALE: DAL 1° GIORNO ED ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    $str_Payment .= $a_Types[6].'<br>'; //PAGAMENTO IN MISURA DELLA META' DEL MASSIMO: DAL 61° GIORNO ED ENTRO 6 MESI DALLA NOTIFICA DEL PRESENTE VERBALE
                                    $str_Payment .= $a_Types[7].'<br>'; //CAN
                                    $str_Payment .= $a_Types[8]; //CAD
                                    break;
                            }
                            $Content = str_replace("{{PaymentDescription}}", $str_Payment, $Content);
                        }
                        
                        //Sottotesto dati per tipo trasgressore
                        else if ($var == "{{TrespasserType}}"){
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
                            if($r_FineOwner && strlen(trim($r_FineOwner['ReasonDescriptionIta'])) > 0){
                                $str_ReasonDescription = $r_FineOwner['ReasonDescriptionIta'];
                            } else $str_ReasonDescription = $r_Fine['ReasonDescription' . $a_Lan[$n_LanguageId]];
                            
                            if ($CreationType==5)
                                $Content = str_replace("{{ReasonText}}", $a_Types[2], $Content);
                            else if ($str_ReasonDescription != "")
                                $Content = str_replace("{{ReasonText}}", $a_Types[1], $Content);
                            else $Content = str_replace("{{ReasonText}}", '', $Content);
                        }
                        
                        //Invito in AG: descrizione pagamento
                        else if ($var == "{{AGPaymentDescription}}"){
                            if($r_Customer['LumpSum'] == 0)
                                $Content = str_replace("{{AGPaymentDescription}}", $a_Types[1], $Content);
                            else $Content = str_replace("{{AGPaymentDescription}}", '', $Content);
                        }
                        
                        //Invito in AG: inottemperanza invito
                        else if ($var == "{{AGNonCompliance}}"){
                            if($r_Customer['LumpSum'] == 0 && $r_ArticleTariff['ReducedPayment'] == 0)
                                $Content = str_replace("{{AGNonCompliance}}", $a_Types[1], $Content);
                            else $Content = str_replace("{{AGNonCompliance}}", '', $Content);
                        }
                        
                        else $Content = str_replace($var, $a_Types[1], $Content);
                        
                    }
                } else $EmptyPregMatch = true;
            }
            //
            
            /////////////////////////////////////////////
            //VARIABILI
            /////////////////////////////////////////////
            
            $Content = str_replace("{SendType}", $a_FineSendType[2], $Content);
            
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
            
            switch ($trespasser['Genre']) {
                case 'F': $GenreBornIn = 'nata a'; $GenrePrefix = 'la '; break;
                case 'M': $GenreBornIn = 'nato a'; $GenrePrefix = 'il '; break;
                default: $GenreBornIn = 'nato/a a'; $GenrePrefix = '';
            }
            
            $TrespasserTaxCode = PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode']);
            $TrespasserName = StringOutDB((isset($r_trespasser['CompanyName']) ? $r_trespasser['CompanyName'].' ' : '') . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name']);
            
            $Content = str_replace("{TrespasserName}", $TrespasserName, $Content);
            $Content = str_replace("{TrespasserGenre}", $GenrePrefix.$a_GenreLetter[$trespasser['Genre']], $Content);
            $Content = str_replace("{TrespasserCity}", StringOutDB($r_trespasser['City']), $Content);
            $Content = str_replace("{TrespasserProvince}", StringOutDB($r_trespasser['Province']), $Content);
            $Content = str_replace("{TrespasserAddress}", StringOutDB($str_TrespasserAddress), $Content);
            $Content = str_replace("{TrespasserZip}", StringOutDB($r_trespasser['ZIP']), $Content);
            $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
            $Content = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $Content);
            $Content = str_replace("{DateRent}", $r_Fine['ReceiveDate'] ? '<br />(Identificazione dati avvenuta in data: '.DateOutDB($r_Fine['ReceiveDate']).')' : '', $Content);
            $Content = str_replace("{NatoA}", $GenreBornIn, $Content);
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
            
            if($r_FineOwner && strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0){
                $str_ArticleDescription = $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]];
            } else $str_ArticleDescription = $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
            
            $str_ArticleDescription = str_replace("{Speed}", intval($r_Fine['Speed']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedExcess}", intval(intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit'])), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_ArticleDescription);
            
            $str_ExpirationDate = (isset($r_Fine['ExpirationDate']) || $r_Fine['ExpirationDate']!="") ? DateOutDB($r_Fine['ExpirationDate']) : "";
            
            if($r_FineOwner && strlen(trim($r_FineOwner['ReasonDescriptionIta'])) > 0){
                $str_ReasonDescription = $r_FineOwner['ReasonDescriptionIta'];
            } else $str_ReasonDescription = $r_Fine['ReasonDescription' . $a_Lan[$n_LanguageId]];
            
            $Content = str_replace("{ReasonId}", StringOutDB($str_ReasonDescription), $Content);
            $Content = str_replace("{ArticleId}", $str_ArticleId, $Content);
            $Content = str_replace("{ArticleDescription}", StringOutDB($str_ArticleDescription), $Content);
            $Content = str_replace("{ArticleAdditionalNight}", $str_AdditionalNight, $Content);
            $Content = str_replace("{ArticleAdditionalMass}", $str_AdditionalMass, $Content);
            $Content = str_replace("{AdditionalSanctionId}", $str_AdditionalSanction, $Content);
            $Content = str_replace("{AdditionalArticle}", $str_AdditionalArticle, $Content);
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
                
                $Content = str_replace("{DetectorId}", $a_DetectorTitleIta[$r_Fine['DetectorId']], $Content);
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
            if ($chk_ReducedPayment) {
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
            $str_ChiefConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$ChiefControllerName;
            
            $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
            $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);
            $Content = str_replace("{ControllerDate}", $str_ControllerDate, $Content);
            $Content = str_replace("{ControllerTime}", $str_ControllerTime, $Content);
            $Content = str_replace("{ChiefConvalidation}", $str_ChiefConvalidation, $Content);
            $Content = str_replace("{ControllerConvalidation}", $str_ControllerConvalidation, $Content);
            $Content = str_replace("{ChiefControllerName}", $ChiefControllerName, $Content);
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
            
            $TotalPartialFee = $PartialFee + $AdditionalFee;
//             $TotalPartialFeeCAN = $PartialFee + $AdditionalFeeCAN;
//             $TotalPartialFeeCAD = $PartialFee + $AdditionalFeeCAD;
            
            $TotalFee = $n_TotFee + $AdditionalFee;
//             $TotalFeeCAN = $n_TotFee + $AdditionalFeeCAN;
//             $TotalFeeCAD = $n_TotFee + $AdditionalFeeCAD;
            
            $TotalMaxFee = $MaxFee + $AdditionalFee;
//             $TotalMaxFeeCAN = $MaxFee + $AdditionalFeeCAN;
//             $TotalMaxFeeCAD = $MaxFee + $AdditionalFeeCAD;
            
            $str_ProtocolLetter = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            
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

            $Content = str_replace("{BankOwner}", StringOutDB($r_Customer['NationalBankOwner']), $Content);
            $Content = str_replace("{BankName}", StringOutDB($r_Customer['NationalBankName']), $Content);
            $Content = str_replace("{BankAccount}", $r_Customer['NationalBankAccount'], $Content);
            $Content = str_replace("{BankSwift}", $r_Customer['NationalBankSwift'], $Content);
            $Content = str_replace("{BankIban}", $r_Customer['NationalBankIban'], $Content);

            $Content = str_replace("{CurrentDate}", DateOutDB($NotificationDate), $Content);
            $Content = str_replace("{CurrentTime}", date("H:i"), $Content);
            $Content = str_replace("{Date}", $str_Date, $Content);

            $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
            $Content = str_replace("{ManagerDataEntryName}", StringOutDB($r_Customer['ManagerDataEntryName']), $Content);
            $Content = str_replace("{ManagerProcessName}", StringOutDB($r_Customer['ManagerProcessName']), $Content);
            
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

            $Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
            $Content = str_replace("{TotalPartialFee}", NumberDisplay($TotalPartialFee), $Content);
//             $Content = str_replace("{TotalPartialFeeCAN}", NumberDisplay($TotalPartialFeeCAN), $Content);
//             $Content = str_replace("{TotalPartialFeeCAD}", NumberDisplay($TotalPartialFeeCAD), $Content);

            $Content = str_replace("{Fee}", NumberDisplay($n_TotFee), $Content);
            $Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);
//             $Content = str_replace("{TotalFeeCAN}", NumberDisplay($TotalFeeCAN), $Content);
//             $Content = str_replace("{TotalFeeCAD}", NumberDisplay($TotalFeeCAD), $Content);

            $Content = str_replace("{ResearchFee}", NumberDisplay($ResearchFee), $Content);
            $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);
            $Content = str_replace("{ChargeTotalFee}", NumberDisplay($ChargeTotalFee), $Content);
//             $Content = str_replace("{CANFee}", NumberDisplay($CANFee), $Content);
//             $Content = str_replace("{CADFee}", NumberDisplay($CADFee), $Content);

            $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
            $Content = str_replace("{TotalMaxFee}", NumberDisplay($TotalMaxFee), $Content);
//             $Content = str_replace("{TotalMaxFeeCAN}", NumberDisplay($TotalMaxFeeCAN), $Content);
//             $Content = str_replace("{TotalMaxFeeCAD}", NumberDisplay($TotalMaxFeeCAD), $Content);

            $Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content);
//             $Content = str_replace("{AdditionalFeeCAN}", NumberDisplay($AdditionalFeeCAN), $Content);
//             $Content = str_replace("{AdditionalFeeCAD}", NumberDisplay($AdditionalFeeCAD), $Content);
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


//             $str_ReceiveDate = ($r_Fine['TrespasserTypeId'] == 1 && $r_Fine['ReceiveDate'] != "") ? ' - data id trasgr.' . DateOutDB($r_Fine['ReceiveDate']) : '';


//             if($chk_180 && $r_Fine['TrespasserId1_180']>0){

//                 $rs_trespasser = $rs->Select('Trespasser', "Id=" . $r_Fine['TrespasserId1_180']);
//                 $r_trespasser = mysqli_fetch_array($rs_trespasser);
//                 $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
//                 $Content = str_replace("{TrespasserCity}", $r_trespasser['City'], $Content);
//                 $Content = str_replace("{TrespasserProvince}", $r_trespasser['Province'], $Content);

//             } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {
//                 $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=2");
//                 $r_trespasser = mysqli_fetch_array($rs_trespasser);
//                 $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
//                 $Content = str_replace("{TrespasserCity}", $r_trespasser['City'], $Content);

//                 if(strlen($r_trespasser['BornDate']) == 10 && strlen(trim($r_trespasser['BornPlace'])) > 0){
//                     $Content = str_replace("{TrespasserBornDate}", DateOutDB($r_trespasser['BornDate']), $Content);
//                     $Content = str_replace("{TrespasserBornCity}", $r_trespasser['BornPlace'], $Content);
//                 } else {
//                     $Content = str_replace(" il {TrespasserBornDate}", "", $Content);
//                     $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
//                 }

//                 $str_Province = (isset($r_trespasser['Province']) && $r_trespasser['Province']!='') ?  " (".$r_trespasser['Province'].")" : "";

//                 $Content = str_replace("{TrespasserAddress} {TrespasserCity} ({TrespasserProvince})<col>", $r_trespasser['Address']. " ".$r_trespasser['City'].$str_Province."<col>", $Content);

//             } else {
//                 $Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
//                 $Content = str_replace("{TrespasserCity}", $trespasser['City'], $Content);
//                 if(isset($trespasser['Province']) && trim($trespasser['Province'])!=''){
//                     $Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);

//                 } else {
//                     $Content = str_replace("({TrespasserProvince})", '', $Content);

//                 }

//             }

//             if(strlen($trespasser['BornDate']) == 10 && strlen(trim($trespasser['BornPlace'])) > 0){
//                 $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']) . $str_ReceiveDate, $Content);
//                 $Content = str_replace("{TrespasserBornCity}", $trespasser['BornPlace'], $Content);
//             } else {
//                 $Content = str_replace(" il {TrespasserBornDate}", $str_ReceiveDate, $Content);
//                 $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
//             }
            
            /////////////////////////////////////////////
            //Protocol
            /////////////////////////////////////////////
            if ($ultimate) {
                if($r_Fine['ProtocolId'] > 0) $ProtocolNumber = $r_Fine['ProtocolId'];
                else if ($r_Fine['ProtocolIdAssigned'] == 0) {
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                    $ProtocolNumber = $n_Protocol + 1;
                } else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];

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
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            /////////////////////////////////////////////
            //SOTTOTESTI FISSI
            /////////////////////////////////////////////
            
            $HeaderFineNumber = "{{HeaderFineNumber}}";
            
            $a_FixedVariables = $rs->getResults($rs->Select("FormVariable", "CityId='{$_SESSION['cityid']}' AND Type=1 AND LanguageId=$n_LanguageId And NationalityId=1 AND FormTypeId=".TIPO_SOTTOTESTI_FISSI_NAZ));
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

            $Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_".$RndCode.'.'.PDFA;
            $signedName = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_".$RndCode."_signed.".PDFA;
            
            $a_Documentation[$FineId][] = array('Doc' => $Documentation, 'Sign' => false);
            if ($r_Customer['EnableINIPECDigitalSignature'] == 1 && $privateKeyFound)
                $a_Documentation[$FineId][] = array('Doc' => $signedName, 'Sign' => true);
            
            /////////////////////////////////////////////

            //PAGOPA//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $PagoPACode1 = $r_Fine['PagoPA1'];
            $PagoPACode2 = $r_Fine['PagoPA2'];
            //se è abilitato pagoPA e non si tratta di invito in AG, interroghiamo i servizi altrimenti e salviamo gli importi
            if($b_PagoPAEnabled && $FormTypeId != 101){
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
                
//                 if(mysqli_num_rows($rs_PagoPAServiceParameter)>0) {
//                     $r_PagoPAServiceParameter = mysqli_fetch_array($rs_PagoPAServiceParameter);
//                     $tipoContabilità = $r_PagoPAServiceParameter['Type'];
//                     $codiceContabilità = $r_PagoPAServiceParameter['Code'];
//                     $tipoDovuto = $r_PagoPAServiceParameter['TypeDue'];
//                 }
                
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
            
            $pdf->SetAutoPageBreak(true, 0);
            $pdf->SetPrintHeader(false);

            //#writeHTML(html, ln = true, fill = 0, reseth = false, cell = false, align = '')
            $pdf->writeHTML($Content, true, $ultimate ? 0 : 1, true, false, '');
            
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            ////
            ////
            ////    BILL
            ////
            ////
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////

            $extraPages = 0;

            if ($b_PrintBill && $FormTypeId!=101) {

                $page_format = array('Rotate' => -90);
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false);

                $pdf->AddPage('L', $page_format);



                if ($r_Customer['LumpSum'] == 1) {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFeeCAD : $TotalFeeCAD;
                }else {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFee : $TotalFee;
                }


                $pdf->crea_bollettino();

                //$pdf->logo_bollettino($_SESSION['blazon']);

                $a_Address = array();
                $a_Address['Riga1'] = $trespasser['Address'];
                $a_Address['Riga2'] = '';
                $a_Address['Riga3'] = $trespasser['ZIP'];
                $a_Address['Riga4'] = $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ')';

                $a_FifthField = array("Table" => 1, "Id" => $FineId);

                $NW = new CLS_LITERAL_NUMBER();

                $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 0 : 1;
                $str_FifthField = SetFifthField($a_FifthField);
                $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);

                $str_Object = substr('Cron ' . $ProtocolNumber . '/' . $ProtocolYear . '/' . $str_ProtocolLetter . ' targa ' . $r_Fine['VehiclePlate'] . ' ' . $r_Fine['Code'] . ' DEL ' . DateOutDB($r_Fine['FineDate']), 0, 66);




                $numeroLetterale = $NW->converti_numero_bollettino($flt_Amount);


                $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['NationalBankAccount']);
                $pdf->iban_bollettino($r_Customer['NationalBankIban']);
                $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'], 0, 50));
                $pdf->causale_bollettino($str_Object, $str_PaymentDay1);
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address);
                $pdf->importo_in_lettere_bollettino($numeroLetterale);
                $pdf->set_quinto_campo($r_Customer['NationalPostalType'], $str_FifthField);
                $pdf->autorizzazione_bollettino($str_PostalAuthorization);



                if ($r_Customer['LumpSum'] == 1) {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFeeCAD : $TotalMaxFeeCAD;
                }else {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFee : $TotalMaxFee;
                }


                $pdf->crea_bollettino_inverso();

                $a_Address = array();
                $a_Address['Riga1'] = $trespasser['Address'];
                $a_Address['Riga2'] = '';
                $a_Address['Riga3'] = $trespasser['ZIP'];
                $a_Address['Riga4'] = $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ')';


                $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 1 : 2;
                $str_FifthField = SetFifthField($a_FifthField);
                $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);


                $numeroLetterale = $NW->converti_numero_bollettino($flt_Amount);


                $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['NationalBankAccount'], 'due');
                $pdf->iban_bollettino($r_Customer['NationalBankIban'], 'due');
                $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'], 0, 50), 'due');
                $pdf->causale_bollettino($str_Object, $str_PaymentDay2, 'due');
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address, 'due');
                $pdf->importo_in_lettere_bollettino($numeroLetterale, 'due');
                $pdf->autorizzazione_bollettino($str_PostalAuthorization, 'due');

                $page_format = array('Rotate'=>45);
                $pdf->AddPage('P', $page_format);
                // BOLLETTINI 674   451     896 ->

                $extraPages = 2;
            }

            //Conta le pagine attuali, se sono dispari aggiunge una pagina bianca in fondo, porta il puntatore alla pagina prima del bollettino e la muove in quella posizione
            $PageNo= $pdf->PageNo();
            if($PageNo%2 == 1){
                $pdf->AddPage('P', $page_format);
                $pdf->movePage($PageNo, $PageNo-1);
            }
            
            //QRCODE O AVVISO DI PAGAMENTO////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $Bollettino1Fee = $chk_ReducedPayment ? $TotalPartialFee : $TotalFee;
            $Bollettino2Fee = $chk_ReducedPayment ? $TotalFee : $TotalMaxFee;
            $b_ErroreAvviso = false;
            
            //Se l'ente è abilitato alla stampa degli avvisi di pagamento stampa quelli, altrimenti aggiunge i qrcode alla vecchia maniera
            if($r_Customer['PagoPAPaymentNoticeNationalPEC'] > 0 && $b_PagoPAEnabled && $FormTypeId != 101){
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
                
                try{
                    $o_Avviso = new Avviso($oggettoAvviso, $_SESSION['blazon']);
                    $o_Ente = new Ente($r_Customer['ManagerName'], $r_Customer['ManagerSector'], buildManagerInfo($r_Customer), trim($r_Customer['ManagerTaxCode']), $r_Customer['PagoPACBILL']);
                    $o_Destinatario = new Destinatario($TrespasserTaxCode, $TrespasserName, $str_TrespasserAddress);
                    $o_Importo1 = new Importo($Bollettino1Fee, $PagoPAPaymentNotice1, $nomeImporto1, $descImporto1);
                    $o_Importo2 = new Importo($Bollettino2Fee, $PagoPAPaymentNotice2, $nomeImporto2, $descImporto2);
                    
                    $avviso = new ModelloCDS($o_Avviso, $o_Ente, $o_Destinatario, $o_Importo1, $o_Importo2);
                    if ($b_PrintBillPagoPA){
                        $avviso->setBollettino(new Bollettino($str_PostalAuthorizationPagoPA, $r_Customer['NationalBankAccount'], $r_Customer['NationalBankOwner'], $r_Customer['NationalPostalTypePagoPA'], $causaleBollettino));
                    }
                    $avviso->costruisci(true);
                    
                    if (!$ultimate && $r_Fine['FineTypeId']!=4){
                        applyTemporaryLabel($avviso);
                    }
                    
                    $extraPages = $avviso->PageNo();
                    
                    $avviso->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . 'avviso_'.$Documentation , "F");
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
            
            if ($ultimate) {

                if(isset($a_FineArticle)){
                    //Indice fisso per non ripetere update nel caso di più trasgressori
                    $a_UpdFineArticle[0] = $a_FineArticle;
                    //$rs->Update('FineArticle', $a_FineArticle, 'FineId=' . $FineId);
                }
                if(isset($a_FineAdditionalArticle)){
                    //Indice fisso per non ripetere update nel caso di più trasgressori
                    $a_UpdFineAdditionalArticle[0] = $a_FineAdditionalArticle;
                    //$rs->Update('FineArticle', $a_FineAdditionalArticle, 'FineId=' . $FineId);
                }

                /////////////////////////////////////////////////
                //
                // REGULAR / AG
                //
                /////////////////////////////////////////////////

                $aInsert = array(
                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                    array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                    array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                    array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefControllerId, 'settype' => 'int'),
                    array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                );
                $a_InsFineHistory[] = $aInsert;
                //$rs->Insert('FineHistory', $aInsert);

                if($RegularPostalFine==1) {
                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                        array('field' => 'ProtocolIdAssigned', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolNumber, 'settype' => 'int'),
                    );
                } else {
                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                        array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolNumber, 'settype' => 'int'),
                    );

                }
                
                //Salva il verbalizzante stampa se non presente
                if($r_Fine['UIFineChiefControllerId'] <= 0){
                    $aUpdate[] = array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','value'=>$SelectChiefControllerId,'settype' => 'int');
                }
                
                //Indice fisso per non ripetere update nel caso di più trasgressori
                $a_UpdFine[0] = $aUpdate;
                //$rs->Update('Fine', $aUpdate, 'Id=' . $FineId);


                $aInsert = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                //$rs->Insert('FineDocumentation', $aInsert);
                $a_InsFineDocumentation[] = $aInsert;

                if($CreationType==5){

                    $a_FineHistory = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                        array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefControllerId, 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                        array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
                    );

                    if($CreationType==5){
                        $a_FineHistory[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int');
                        $a_FineHistory[] = array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                    } else {
                        $a_FineHistory[] = array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                    }
                    
                    $a_InsFineHistory[] = $a_FineHistory;
                    //$rs->Insert('FineHistory', $a_FineHistory);
                    
                    if($CreationType==5){
                        $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $FineId);
                        $r_Tariff = mysqli_fetch_array($rs_Tariff);
                        $LicensePointProcedure = $r_Tariff['LicensePoint'];
                        $PresentationDocumentProcedure = 0;
                        $BisProcedure = $r_Tariff['126Bis'];
                        $HabitualProcedure = $r_Tariff['Habitual'];
                        $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                        $LossLicenseProcedure = $r_Tariff['LossLicense'];


                        $a_FineNotification = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int'),
                            array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                            array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>$PresentationDocumentProcedure,'settype'=>'int'),
                            array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>$LicensePointProcedure,'settype'=>'int'),
                            array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>$HabitualProcedure,'settype'=>'int'),
                            array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>$SuspensionLicenseProcedure,'settype'=>'int'),
                            array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>$LossLicenseProcedure,'settype'=>'int'),
                            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                        );
                        //$rs->Insert('FineNotification', $a_FineNotification);
                        $a_FineNotification[] = $a_FineNotification;

                        $a_Fine = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 25, 'settype' => 'int')
                        );

                    } else {
                        $a_Fine = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 20, 'settype' => 'int')
                        );
                    }
                    //Indice fisso per non ripetere update nel caso di più trasgressori
                    $a_UpdFine[1] = $a_Fine;
                    //$rs->Update('Fine', $a_Fine, 'Id=' . $FineId);
                    
                }

                if (!is_dir(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                    mkdir(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                }
            }
            
            //Applica l'etichetta STAMPA PROVVISORIA solo il verbale non è contratto, perchè in tal caso deve riaprire il pdf, unire le pagine e a quel punto applicarla
            if (!$ultimate && $r_Fine['FineTypeId']!=4){
                applyTemporaryLabel($pdf);
            }
            
            //Scrive il file del verbale sul file system////////////////////////////////////////////////////////////////////////////////////
            $pdf->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
            
            //Allega l'avviso di pagamento generato se era abilitata la gestione
            if($r_Customer['PagoPAPaymentNoticeNationalPEC'] > 0 && $b_PagoPAEnabled && $FormTypeId != 101 && !$b_ErroreAvviso){
                //Inizializza pdf-union
                $pdf_unionavviso = new FPDI();
                $pdf_unionavviso->setHeaderFont(array('helvetica', '', 8));
                $pdf_unionavviso->setFooterFont(array('helvetica', '', 8));
                $pdf_unionavviso->setPrintHeader(false);
                $pdf_unionavviso->setPrintFooter(false);
                
                try {
                    $n_PageCount = $pdf_unionavviso->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Documentation);
                    for ($p = 1; $p <= $n_PageCount; $p++) {
                        $tmp_Page = $pdf_unionavviso->ImportPage($p);
                        $tmp_Size = $pdf_unionavviso->getTemplatesize($tmp_Page);
                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                        $pdf_unionavviso->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_unionavviso->useTemplate($tmp_Page);
                    }
                    
                    $n_PageCount = $pdf_unionavviso->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
                    for ($p = 1; $p <= $n_PageCount; $p++) {
                        $tmp_Page = $pdf_unionavviso->ImportPage($p);
                        $tmp_Size = $pdf_unionavviso->getTemplatesize($tmp_Page);
                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                        $pdf_unionavviso->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_unionavviso->useTemplate($tmp_Page);
                    }
                } catch (Exception $e) {
                    if(!in_array($FineId, $a_FailedPagoPA)) $a_FailedPagoPA[] = $FineId;
                    trigger_error("<CREAVERBALIPEC> ATTENZIONE -> Errore nell\'unione del pdf dell'avviso di pagamento avviso_$Documentation: $e",E_USER_WARNING);
                    $str_Warning .= 'ID '.$FineId.': Errore nella fusione del verbale e dell\'avviso di pagamento PagoPA. Contattare l\'amministrazione di sistema.<br>';
                }
                
                $pdf_unionavviso->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
                
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
            }
            
            //Se il verbale è contratto, eseguo le operazioni per unire il documento allegato al verbale in un unico pdf
            if($r_Fine['FineTypeId']==4){
                
                //Inizializza pdf-union
                $pdf_union4 = new FPDI();
                $pdf_union4->setHeaderFont(array('helvetica', '', 8));
                $pdf_union4->setFooterFont(array('helvetica', '', 8));
                $pdf_union4->setPrintHeader(false);
                $pdf_union4->setPrintFooter(false);
                
                $a_BillPages = array();
                
                //Metto nell'union il documento appena generato
                try {
                    $n_PageCount = $pdf_union4->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Documentation);
                    for ($p = 1; $p <= $n_PageCount; $p++) {
                        //Prendo le pagine del bollettino ($n_PageCount-$extraPages) e le tengo da parte, Row == 1 serve a sapere che va fatto sul primo record, quello che contiene il verbale creato
                        if($p > ($n_PageCount-$extraPages)){
                            $a_BillPages[] = $pdf_union4->ImportPage($p);
                        } else {
                            $tmp_Page = $pdf_union4->ImportPage($p);
                            $tmp_Size = $pdf_union4->getTemplatesize($tmp_Page);
                            $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                            $pdf_union4->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                            $pdf_union4->useTemplate($tmp_Page);
                        }
                    }
                } catch (Exception $e) {
                    trigger_error("<CREAFIRMAVERBALIPEC> ATTENZIONE -> Errore nell\'unione del pdf '$Documentation' per verbale contratto: $e",E_USER_WARNING);
                }
                
                //Metto nell'union il documento legato al verbale contratto
                $rs_FineDocumentation = $rs->Select('FineDocumentation', "FineId=$FineId AND DocumentationTypeId=2", "Id DESC");
                
                while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
                    $FileName = $r_FineDocumentation['Documentation'];

                    try {
                        $n_PageCount = $pdf_union4->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $FileName);
                        for ($p = 1; $p <= $n_PageCount; $p++) {
                            $tmp_Page = $pdf_union4->ImportPage($p);
                            $tmp_Size = $pdf_union4->getTemplatesize($tmp_Page);
                            $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                            $pdf_union4->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                            $pdf_union4->useTemplate($tmp_Page);
                        }
                    } catch (Exception $e) {
                        trigger_error("<CREAFIRMAVERBALIPEC> ATTENZIONE -> Errore nell\'unione del pdf '$FileName' per verbale contratto: $e",E_USER_WARNING);
                    }
                    
                }
                
                //Aggancio le pagine tenute da parte
                if(!empty($a_BillPages)){
                    foreach($a_BillPages as $BillPage){
                        $tmp_Size = $pdf_union4->getTemplatesize($BillPage);
                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                        $pdf_union4->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_union4->useTemplate($BillPage);
                    }
                }
                
                if (!$ultimate){
                    applyTemporaryLabel($pdf_union4);
                }
                
                $pdf_union4->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
                
            }
            
            //Controlla l'esistenza della chiave privata per l'utente in sessione e se il flag della firma dei verbali pec è abilitata
            if ($r_Customer['EnableINIPECDigitalSignature'] == 1 && $privateKeyFound){
                digitalSign(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation, NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $signedName, $SignaturePwd, 'Verbale PEC', "Questo documento è firmato digitalmente da <strong>".$SignController."</strong>", 10, 98);
            }
            
        }
        
        //PARTE DB///////////////////////////////////////////////////////////////////////////////////////////////////
        //Se l'id del verbale non è fra quelli di cui PagoPA ha fallito, esegue le query e aggiunge i pdf generati al pdf unito
        if(!in_array($FineId, $a_FailedPagoPA)){
            if($ultimate){
                executeQueries('Update', 'FineArticle', $a_UpdFineArticle, "FineId=$FineId");
                executeQueries('Update', 'FineAdditionalArticle', $a_UpdFineAdditionalArticle, "FineId=$FineId");
                executeQueries('Insert', 'FineHistory', $a_InsFineHistory);
                executeQueries('Insert', 'FineDocumentation', $a_InsFineDocumentation);
                executeQueries('Insert', 'FineNotification', $a_InsFineNotification);
                executeQueries('Update', 'Fine', $a_UpdFine, "Id=$FineId");
            }
            
            foreach($a_Documentation[$FineId] as $Doc){
                $UseSigned = $r_Customer['EnableINIPECDigitalSignature'] == 1 && $privateKeyFound;
                if($UseSigned === $Doc['Sign']){
                    //UNION///////////////////////////////////////////////////////////////////////////////////////////////////////
                    try{
                        $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc']);
                        for ($p = 1; $p <= $n_PageCount; $p++) {
                            
                            $tmp_Page = $pdf_union->ImportPage($p);
                            $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                            
                            $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                            
                            $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                            $pdf_union->useTemplate($tmp_Page);
                        }
                    } catch (Exception $e){
                        trigger_error("<CREAFIRMAVERBALIPEC> ATTENZIONE -> Errore nell\'unione dei pdf dei verbali selezionati. File:'{$Doc['Doc']}' errore: $e",E_USER_WARNING);
                    }
                    /////////////////////////////////////////////////////////////////////////////////////////////////////////
                }
            }
        } else {
            foreach($a_Documentation[$FineId] as $Doc){
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc['Doc']);
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $rs->End_Transaction();
    }
    
    if ($ultimate){
        $UnionDocumentation = $_SESSION['cityid'] . "_" . date("Y-m-d_H-i-s") . ".".PDFA;
        if (!is_dir(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/create')) {
            mkdir(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/create', 0777);
        }
        
        $pdf_union->Output(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/create/' . $UnionDocumentation, "F");
        
        //Smista i file nelle apposite cartelle
        foreach ($a_Documentation as $DocFineId => $a_Doc){
            if(!in_array($DocFineId, $a_FailedPagoPA)){
                foreach ($a_Doc as $Doc){
                    if($Doc['Sign']){
                        //Se il documento è firmato lo sposta in public/FIRMATI/<ente>/
                        if(!copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'], SIGNED_FOLDER . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'])){
                            $str_Warning .= "Copia file da percorso temporaneo a " . SIGNED_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                            trigger_error("Copia file da percorso temporaneo a " . SIGNED_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                        }
                    } else {
                        //Se il documento NON è firmato lo sposta in public/DA_FIRMARE/<ente>/ e doc/national/fine/<ente>/<idverbale>/
                        if(!copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'], TOSIGN_FOLDER . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'])){
                            $str_Warning .= "Copia file da percorso temporaneo a " . TOSIGN_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                            trigger_error("Copia file da percorso temporaneo a " . TOSIGN_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                        }
                        
                        if(!copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . "/" . $Doc['Doc'])){
                            $str_Warning .= "Copia file da percorso temporaneo a " . NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . " fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                            trigger_error("Copia file da percorso temporaneo a " . NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . " fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                        }
                    }
                    
                    //Elimina il file dal percorso temporaneo
                    if(!unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc['Doc'])){
                        $str_Warning .= "Eliminazione file da percorso temporaneo fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                        trigger_error("Eliminazione file da percorso temporaneo fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                    }
                }
            }
        }
        
        $_SESSION['Documentation'] = $MainPath . '/doc/national/pec/' . $_SESSION['cityid'] . '/create/' . $UnionDocumentation;
    } else {
        $UnionDocumentation = 'export_createdynamic_pec_national.'.PDFA;
        
        $pdf_union->Output(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/' . $UnionDocumentation, "F");
        
        foreach ($a_Documentation as $DocFineId => $a_Doc){
            if(!in_array($DocFineId, $a_FailedPagoPA)){
                foreach ($a_Doc as $Doc){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc['Doc']);
                }
            }
        }
        
        $_SESSION['Documentation'] = $MainPath . '/doc/national/pec/' . $_SESSION['cityid'] . '/' . $UnionDocumentation;
    }
    
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
    } else {
        if($ultimate){
            $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
        }
    }

    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    
    $rs->Begin_Transaction();
    $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'"); 
    $rs->End_Transaction();
}
