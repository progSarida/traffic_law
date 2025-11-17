<?php
require_once(CLS.'/cls_literal_number.php');
include(INC."/function_postalCharge.php");

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_REMINDER_DOCUMENT_LOCKED_PAGE."_{$_SESSION['cityid']}'");
$r_Locked = mysqli_fetch_assoc($rs_Locked);

if($r_Locked){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di procedere.";
        header("location: ".$P);
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".CREATE_REMINDER_DOCUMENT_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => CREATE_REMINDER_DOCUMENT_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Parametri ente
$rs_CityParameters = $rs->Select('ProcessingDataPaymentForeign', "CityId='".$_SESSION['cityid']."'");
$r_CityParameters = mysqli_fetch_array($rs_CityParameters);

$PaymentDayAccepted = number_format($r_CityParameters['PaymentDayAccepted'],0);
$ReducedPaymentDayAccepted = number_format($r_CityParameters['ReducedPaymentDayAccepted'],0);
//Giorni entro i quali pagare il sollecito
$PaymentDayReminder = number_format($r_CityParameters['PaymentDayReminder']);

//PARAMETRI DELL'ENTE
$rs_Customer = $rs->Select("V_Customer", "CityId = '".$_SESSION['cityid']."' AND CreationType = 1 ", "FromDate DESC", 1);
$r_Customer  = mysqli_fetch_array($rs_Customer);

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrintDestinationFold AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_FoldReturn = $r_PrintParameter['ForeignReminderFoldReturn'] ?? '';
$str_PostalAuthorization = trim($r_PrintParameter['ForeignReminderPostalAuthorization'] ?? '');
////////////////////////////////////////////////

$b_PagoPAEnabled = $r_Customer['PagoPAPaymentForeign'] > 0;
//TODO da rivedere con l'introduzione della gestione PagoPA sui solleciti.
$b_PrintBill = /*($b_PagoPAEnabled ? $r_Customer['PagoPAPaymentNoticeNational'] <= 0 : true) &&*/ !empty($r_Customer['NationalPostalType']);
//$b_PrintBillPagoPA = $b_PagoPAEnabled && !empty($r_Customer['NationalPostalTypePagoPA']) && $r_Customer['PagoPAPaymentNoticeNational'] > 0;

//Controlli parametri
if($b_PagoPAEnabled){
    //Se l'ente non ha CF/PIVA impostati
    if(empty($r_Customer['ManagerTaxCode'])){
        $str_Error .= 'È necessario specificare il codice fiscale dell\'ente per il funzionamento della gestione PagoPA (Ente > Gestione Ente > Indirizzo).<br>';
    }
}
//Se non sono state impostate opzioni di stampa o non è stato impostato "Senza bollettino"
if($b_PrintBill && $PrintType != 3){
    if(empty($r_Customer['ForeignReminderBankAccount'])){
        $str_Error .= "Non è possibile procedere con l'elaborazione se non sono state inserite le coordinate bancarie per la riscossione dei solleciti tra le configurazioni dell'ente competente.<br >Compilare i campi sotto 'Dati per solleciti' nella scheda Banca del menù Ente > Gestione Ente > Pagamenti.<br>";
    }
    if(empty($str_PostalAuthorization)){
        $str_Error .= 'Per la stampa del bollettino è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente in base alla Destinazione di stampa selezionata (Ente > Gestione Ente > Posta).<br>';
    }
}
//Se si tenta di stampare solo il bollettino ma non è definito il TD nelle configurazioni
if(!$b_PrintBill && $PrintType == 2){
    $str_Error .= 'Non è possibile procedere con la sola stampa del solo bollettino se l\'ente non ha previsto la stampa dei bollettini nelle sue configurazioni.<br>';
}
// if($b_PrintBillPagoPA){
//     //Se la stampa all'avviso di pagamento PagoPA e la stampa del bollettino postale PagoPA sono attive ma non è impostata l'autorizzazione alla stampa
//     if(empty($str_PostalAuthorizationPagoPA)){
//         $str_Error .= 'Per la stampa del bollettino postale PagoPA è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente o degli stampatori in base alla Destinazione di stampa selezionata.<br>';
//     }
// }

if(!empty($str_Error)){
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_REMINDER_DOCUMENT_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $_SESSION['Message']['Error'] = $str_Error;
    header("location: ".impostaParametriUrl(array('P' => 'frm_createdynamic_reminder.php'), 'frm_createdynamic_reminder.php'));
    DIE;
}

if(isset($r_Customer['ForeignPostalType']) && $r_Customer['ForeignPostalType']!='' && (!isset($r_Customer['ForeignReminderBankAccount'] ) || empty($r_Customer['ForeignReminderBankAccount'])) ){
    $P = "frm_createdynamic_reminder.php";
    $_SESSION['Message'] = "Non è possibile procedere con l'elaborazione se non sono state inserite le coordinate bancarie per la riscossione dei solleciti tra le configurazioni dell'ente competente.<br >Compilare i campi sotto 'Dati per solleciti ESTERO' nella scheda Banca del menù Ente\Gestione Ente.";
    header("location: ".$P."?DisplayMsg=1");
    DIE;
}



//$FinePDFList = $r_Customer['FinePDFList'];

$str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
}

//Firmatario
$rs_Signer = $rs->Select('Controller', "Id=".$n_ControllerId);
$r_Signer = mysqli_fetch_array($rs_Signer);
$Signer = (isset($r_Signer['Qualification']) ? $r_Signer['Qualification'].' ' : '').$r_Signer['Name'];

$ultimate = CheckValue('ultimate','n');
$NoElegibleFine = true;

$a_DocumentationFineZip = array();
$a_ReminderId = array();
$a_InvalidReminders = array();


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$CurrentDate = date("Y-m-d");
$n_ReminderCount = 0;


$a_Lan = unserialize(LANGUAGE);
$a_LanKeys = unserialize(LANGUAGE_KEYS);
$a_Reminder =  unserialize(REMINDER);

//IMPORTANTE: NON cambiare il font 'dejavusans', serve il supporto per i caratteri UTF8 lituani e non tutti i font lo hanno
$font = 'dejavusans';

$FormTypeId = 29;

if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid'])) {
    mkdir(FOREIGN_FINE."/".$_SESSION['cityid'], 0777);
}

if(isset($_POST['checkbox'])) {
    
    $pdf_union = new FPDI();
    
    foreach($_POST['checkbox'] as $ReminderId) {
        $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
        $pdf->TemporaryPrint= $ultimate;
        $pdf->NationalFine= 0;
        $pdf->CustomerFooter = 0;
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Reminder');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setHeaderFont(array($font, '', 8));
        $pdf->setFooterFont(array($font, '', 8));
        $pdf->SetMargins(10,10,10);
        
        //recupero i dati del sollecito ultimo associato per creare il pdf
        $rs_Reminder = $rs->SelectQuery(
            "SELECT
                FR.Id ReminderId,
                FR.FineId ReminderFineId,
                FR.PaymentDays,
                FR.PaymentDate,
                FR.DaysFromNotificationDate,
                FR.DelayDays,
                FR.Semester,
                FR.Fee,
                FR.MaxFee,
                FR.HalfMaxFee,
                FR.TotalNotification,
                FR.Amount,
                FR.TotalAmount,
                FR.Percentual,
                FR.PercentualAmount,
                FR.PrintDate,
                FR.NotificationFee,   
   
                F.Code,
                F.ProtocolId,
                F.ProtocolYear,
                F.Locality,
                F.Address,
                F.VehiclePlate,
                F.VehicleTypeId,
                F.FineDate,
                F.FineTime,
                F.CityId,
                F.PagoPA1,
                F.PagoPA2,
                F.StatusTypeId,
                F.ReminderDate AS ReminderDate,
                TAR.ReducedPayment,

                FH.NotificationTypeId,
                FH.FineId,
                FH.TrespasserId,
                FH.TrespasserTypeId,
                FH.ControllerId,
                FH.SendDate,
                FH.DeliveryDate,
            
                FA.ViolationTypeId,
                FA.ArticleId,
            
                T.Id TrespasserId,
                T.Code TrespasserCode,
                T.Genre,
                T.CompanyName,
                T.Surname,
                T.Name,
                T.Address TrespasserAddress,
                T.StreetNumber,
                T.Ladder,
                T.Indoor,
                T.Plan,
                T.ZIP,
                T.City,
                T.Province,
                T.TaxCode,
                T.VatCode,
                T.ZoneId,
                T.LanguageId,
            
                C.Title CityTitle,
                L.Title Language,
                CO.Title CountryTitle,
                FN.NotificationDate NotificationDate
            
                FROM FineReminder FR
                JOIN Fine F ON FR.FineId = F.Id AND FR.PrintDate = F.ReminderDate
                JOIN FineHistory FH ON FR.FineId=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON FR.FineId=FA.FineId
                JOIN ArticleTariff TAR ON TAR.ArticleId=FA.ArticleId and TAR.Year = F.ProtocolYear           
                JOIN FineNotification FN ON FN.FineId = FR.FineId
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN Language L ON T.LanguageId = L.Id
                JOIN sarida.City C on C.Id = F.Locality
                JOIN sarida.Country CO ON CO.Id=T.CountryId
                JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
                WHERE FR.Id=".$ReminderId
            );
             
        $r_Reminder = mysqli_fetch_assoc($rs_Reminder);
        $FineId = $r_Reminder['ReminderFineId'];
        $n_LanguageId = $r_Reminder['LanguageId'];
        //trigger_error("lingua " .$n_LanguageId, E_USER_WARNING);
        
        $rs_VehicleType = $rs->SelectQuery('SELECT Title'.$a_LanKeys[ucfirst($r_Reminder['Language'])].' AS VehicleType FROM VehicleType WHERE Id='.$r_Reminder['VehicleTypeId']);
        $VehicleType = StringOutDB(mysqli_fetch_array($rs_VehicleType)['VehicleType']);        
        
        $ViolationTypeId = $r_Reminder['ViolationTypeId'];
        
        
        $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType1'];
        $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType2'];
        
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        
        $RuleTypeId = $r_RuleType['Id'];
        
        $str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
        
        $rs_ReminderLetter = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter FROM FineReminder WHERE FineId=".$FineId." AND FlowDate IS NOT NULL");
        $r_ReminderLetter = mysqli_fetch_array($rs_ReminderLetter);
        $n_ReminderLetter = $r_ReminderLetter['ReminderLetter'];
        
        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;
        
        
        $trespassers = $rs->Select('V_Trespasser', "Id=".$r_Reminder['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);
        
        //$ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
        
        $n_LanguageId = $trespasser['LanguageId'];
        $ZoneId= $trespasser['ZoneId'];

        $r_PostalCharge=getPostalCharge($_SESSION['cityid'],DateInDB($CreationDate));
        
        $ManagerSubject = "Serv. risc. Violazioni al C.D.S.";
        $NotificationFee = $r_Reminder['NotificationFee'];
        
        //Se provvisorio evidenzia il testo in giallo
        $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
        $pdf->SetTextColor(0, 0, 0);
        
        $TaxCode = trim($r_Reminder['TaxCode']);
        $VatCode = trim($r_Reminder{'VatCode'});
        $TrespasserCode = $TaxCode != null ? $TaxCode : ($VatCode != null ? $VatCode : "");
        
        $TotaleDovuto = $r_Reminder['TotalAmount'] - $r_Reminder['Amount'];
        
        //Coordinata inizio stampa testo dinamico
        $TextStartCoord = array('X'=>10, 'Y'=>92);
        
        //Se l'ammontare totale è negativo il il verbale non è elegibile alla creazione del sollecito, altrimenti procedi
        if ($r_Reminder['TotalAmount'] < 0){
            $a_InvalidReminders[] = $FineId;
        } else {
            $NoElegibleFine = false;
            
            //nel caso solo bollettino non stampa nulla
            if($PrintType != 2)
                {
                $page_format = array('Rotate'=>45);
                $pdf->SetMargins(10,8,10);
                $pdf->AddPage('P');
                
                //Prende il contenuto del testo
                $forms = $rs->Select('FormDynamic',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
                $form = mysqli_fetch_array($forms);
                
                $Content = StringOutDB($form['Content']);
                //trigger_error($Content, E_USER_WARNING);
                
                //INTESTAZIONE
                //Se Intestazione SARIDA è abilitata nei parametri ente, scrive l'intestazione di Sarida, altrimenti quella dell'ente
                if ($r_Customer['ForeignReminderHeaderSarida'] == 1){
                    $pdf->Image('img/sarida.jpg', 3, 10, 12, 17);
                    
                    $ManagerName = $r_Customer['ManagerName'];
                    $pdf->customer = $ManagerName;
                    
                    $pdf->SetFont($font, '', 8, '', true);
                    
                    $pdf->writeHTMLCell(100, 0, 15, '', "<strong>Concessionario Sarida srl</strong>", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(100, 0, 15, '', "Via Monsignor Vattuone, 9/6 - 16039 Sestri Levante (GE)", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(100, 0, 15, '', "P.IVA 01338160995", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(100, 0, 15, '', "Tel: 01851830468 - Fax: 0185457447", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(100, 0, 15, '', "eMail: posta@sarida.it - Sito: www.sarida.it", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(100, 0, 15, '', "Gestione: ".$ManagerName, 0, 0, 1, true, 'L', true);
                    
                    if ($str_FoldReturn != ""){
                        $pdf->SetFont($font, '', 7, '', true);
                        $pdf->LN(3);
                        $pdf->writeHTMLCell(130, 0, 15, '', "Restituzione piego in caso di mancato recapito:", 0, 0, 1, true, 'L', true);
                        $pdf->LN(3);
                        $pdf->writeHTMLCell(130, 0, 15, '', $str_FoldReturn, 0, 0, 1, true, 'L', true);
                    }
                    $pdf->LN(8);
                    $pdf->writeHTMLCell(80, 0, 110, '', $_SESSION['citytitle']." li, ".$CreationDate, 0, 0, 1, true, 'L', true);
                } else {
                    $pdf->Image($_SESSION['blazon'], 3, 10, 12, 17);
                    
                    $ManagerName = $r_Customer['ManagerName'];
                    $ManagerPEC = $r_Customer['ManagerPEC'];
                    
                    $pdf->customer = $ManagerName;
                    
                    $pdf->SetFont('helvetica', '', 5, '', true);
                    
                    $pdf->writeHTMLCell(75, 0, 15, 9, '<strong>' . $ManagerName . '</strong>', 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(75, 0, 15, '', buildHeaderReminder($r_Customer, $str_FoldReturn), 0, 0, 1, true, 'L', true);
                    
                    $pdf->SetFont('helvetica', '', 8, '', true);
                    
                    if($r_Customer['ManagerPEC'] != ''){
                        $pdf->MultiCell(80, 0, 'PEC: '.$ManagerPEC, 0, 'L', 1, 1, 10, 30, true);
                    }
                    $pdf->writeHTMLCell(80, 0, 110, '', $_SESSION['citytitle']." li, ".$CreationDate, 0, 0, 1, true, 'L', true);
                }
                
                //Stampa le finestre delle buste
                $window = true;
                 if (!$ultimate && $window){
                     $pdf->RoundedRect(2, 8, 90, 21, 3.50, '1111', '', array('color' => array(145)), '');
                     $pdf->RoundedRect(93, 38, 115, 45, 3.50, '1111', '', array('color' => array(145)), '');
                     $pdf->RoundedRect(0.1, 0.1, 209.6, 89.7, 0.5, '1111', '', array('color' => array(0,0,255)), '');
                     $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
                }
                
                //INTESTAZIONE CRON E REF////////////////////////////////////////////////////////////////////////////////////
                $pdf->SetFont('', 'B', 10, '', true);
                $pdf->MultiCell(80, 0, 'Cron. Nr: '.$r_Reminder['ProtocolId'].(!$ultimate ? ' - PROVV' : '').'/'.$r_Reminder['ProtocolYear'].'/'.$str_ProtocolLetter, 0, 'L', 1, 1, 10, 40, true);
                $pdf->MultiCell(80, 0, 'Ref. nr: '.$r_Reminder['Code'], 0, 'L', 1, 1, 10, '', true);
                $pdf->SetFont('', '', 10, '', true);
                $pdf->MultiCell(80, 0, 'Codice fiscale: '.$TrespasserCode, 0, 'L', 1, 1, 10, '', true);
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                
                //INTESTAZIONE TRASGRESSORE////////////////////////////////////////////////////////////////////////////////////
                $str_TrespasserAddress =  trim(
                    $r_Reminder['TrespasserAddress'] ." ".
                    $r_Reminder['StreetNumber'] ." ".
                    $r_Reminder['Ladder'] ." ".
                    $r_Reminder['Indoor'] ." ".
                    $r_Reminder['Plan']
                    );
                
                $pdf->SetFont('', '', 10, '', true);
                $pdf->SetCellPadding(0);
                $pdf->MultiCell(90, 0, strtoupper(StringOutDB($a_GenreLetter[$r_Reminder['Genre']].' '.(isset($r_Reminder['CompanyName']) ? $r_Reminder['CompanyName'].' ' : '') . $r_Reminder['Surname'] . ' ' . $r_Reminder['Name'])), 0, 'L', 1, 1, 110, 55.5, true);
                $pdf->MultiCell(90, 0, strtoupper(StringOutDB($str_TrespasserAddress != "" ? $str_TrespasserAddress : "")), 0, 'L', 1, 1, 110, '', true);
                $pdf->MultiCell(90, 0, StringOutDB($r_Reminder['ZIP']).' '.strtoupper(StringOutDB($r_Reminder['City'])).(!empty($r_Reminder['Province']) ? ' ('.strtoupper(StringOutDB($r_Reminder['Province'])).')' : ''), 0, 'L', 1, 1, 110, '', true);
                $pdf->SetFont('', '', 8, '', true);
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                
                
                //IMPOSTA COORDINATA INIZIO TESTO DINAMICO
                $pdf->SetXY($TextStartCoord['X'], $TextStartCoord['Y']);
                
                //FINE INTESTAZIONE
                
                //SOTTOTESTI
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
                            //trigger_error("query ". "Id='$var' AND FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId." And NationalityId=2", E_USER_WARNING);
                            while ($r_variable = mysqli_fetch_array($rs_variable)){
                                $a_Types[$r_variable['Type']] = $r_variable['Content'];
                                //trigger_error("sottotesto".$r_variable['Type']. " - " .$r_variable['Content'], E_USER_WARNING);
                            }
                            
                            //TODO togliere
                            if ($var == "{{HeaderSarida}}"){
                                $Content = str_replace("{{HeaderSarida}}", '', $Content);
                            }
                            //Sottotesto maggiorazione
                            if ($var == "{{Surcharge}}"){
                                $str_Surcharge = "";
                                if ($r_Reminder['Percentual'] > 0){
                                    $str_Surcharge .= $a_Types[1];
                                }
                                $Content = str_replace("{{Surcharge}}", $str_Surcharge, $Content);
                            }
                            //Sottotesto oggetto
                            if ($var == "{{ReminderObject}}"){
                                $str_ReminderObject = "";
                                if ($r_Reminder['Amount']>=0.01) {
                                    if($r_Reminder['DaysFromNotificationDate']<=FINE_DAY_LIMIT){
                                        $str_ReminderObject = $a_Types[2];
                                    } else $str_ReminderObject = $a_Types[3];
                                } else $str_ReminderObject = $a_Types[1];
                                $Content = str_replace("{{ReminderObject}}", $str_ReminderObject, $Content);
                                //trigger_error("oggetto ".$str_ReminderObject, E_USER_WARNING);
                            }
                            //Sottotesto contenuto
                            if ($var == "{{ReminderContent}}"){
                                $str_ReminderContent = "";
                                if ($r_Reminder['Amount']>=0.01) {
                                    if($r_Reminder['DaysFromNotificationDate'] <=FINE_DAY_LIMIT){
                                        $str_ReminderContent = $a_Types[2];
                                    } else $str_ReminderContent = $a_Types[3];
                                } else $str_ReminderContent = $a_Types[1];
                                $Content = str_replace("{{ReminderContent}}", $str_ReminderContent, $Content);
                                //trigger_error("contenuto ".$str_ReminderContent, E_USER_WARNING);
                            }
                            //Sottotesto motivazione
                            if ($var == "{{ReminderReason}}"){
                                $cls_pagamenti = new cls_pagamenti($r_Reminder['ReminderFineId'], $r_Reminder['CityId'], $r_Reminder['ReminderDate']);
                                $status = $cls_pagamenti->getStatus();                       //Stato pagamento
                                $latePaymentStatus = $cls_pagamenti->getLatePaymentStatus(); //Stato pagamento in ritardo
                                $str_ReminderReason = "";
                                //$a_Types[n] contiene i vari sottotesti da accoppiare
                                //$a_Types: 1 - Omesso, 2 - Parziale, 3 - Tardivo ridotto, 4 - Tardivo 60gg
                                if ($status == 0)
                                    {
                                    $str_ReminderReason = $a_Types[1];
                                    }
                                elseif($status == 1)
                                    {
                                    $str_ReminderReason = $a_Types[2];
                                    }
                                elseif($status == 2)
                                    {
                                        if($latePaymentStatus != -1){   //Se è presente il dettaglio dello scaglione di pagamento in ritardo
                                            if(($r_Reminder['ReducedPayment'] == 1) && ($latePaymentStatus == 1))
                                                $str_ReminderReason =  $a_Types[3];
                                            elseif($latePaymentStatus == 2)
                                                $str_ReminderReason = $a_Types[4];
                                            else 
                                                $str_ReminderReason = $a_Types[2];
                                            }
                                        else    //In caso non dovesse esserci (in caso di errore) restituirebbe comunque il sottotesto del parziale
                                            {
                                            $str_ReminderReason = $a_Types[2];
                                            }
                                    }
                                $Content = str_replace("{{ReminderReason}}", $str_ReminderReason, $Content);
                            }
                            //Sottotesto termini
                            if ($var == "{{ReminderPaymentTerms}}"){
                                $str_ReminderPaymentTerms = "";
                                if ($r_Reminder['Amount']>=0.01) {
                                    if($r_Reminder['DaysFromNotificationDate']<=FINE_DAY_LIMIT){
                                        $str_ReminderPaymentTerms = $a_Types[2];
                                    } else $str_ReminderPaymentTerms = $a_Types[3];
                                } else $str_ReminderPaymentTerms = $a_Types[1];
                                $Content = str_replace("{{ReminderPaymentTerms}}", $str_ReminderPaymentTerms, $Content);
                                //trigger_error("termini pagamento ".$str_ReminderPaymentTerms, E_USER_WARNING);
                            }
                            else $Content = str_replace($var, $a_Types[1], $Content);
                            
                        }
                    } else $EmptyPregMatch = true;
                }
                
                //Sostituisce le variabili
    //             $rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentNationalPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$r_Reminder['CityId']."'");
    //             $PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentNationalPaymentDayReminder'];
                $Content = str_replace("{Signer}", StringOutDB($Signer), $Content);
                $Content = str_replace("{Blazon}", '<img src="img/sarida.jpg" width="60" height="70">', $Content); //NOTA: I TAG IMG CON ATTRIBUTO src='' NON FUNZIONANO, USARE src="" (doppie graffe)
                $Content = str_replace("{ManagerName}", $r_Customer['ManagerName'], $Content);
                $Content = str_replace("{ManagerSubject}", $ManagerSubject, $Content);           
                $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
                $Content = str_replace("{ReminderOfficeInfo}", StringOutDB($r_Customer['ReminderOfficeInfo']), $Content);
                $Content = str_replace("{PaymentDays}", $r_Reminder['PaymentDays'], $Content);
                $Content = str_replace("{PaymentDate}", DateOutDB($r_Reminder['PaymentDate']), $Content);
                $Content = str_replace("{DaysFromNotificationDate}", $r_Reminder['DaysFromNotificationDate'], $Content);
                $Content = str_replace("{DelayDay}", $r_Reminder['DelayDays'], $Content);
                $Content = str_replace("{TrespasserName}", StringOutDB($r_Reminder['CompanyName']) . ' ' . StringOutDB($r_Reminder['Surname']) . ' ' . StringOutDB($r_Reminder['Name']), $Content);
                $Content = str_replace("{TrespasserGenre}", /*$a_GenreLetter[$r_Reminder['Genre']]*/"", $Content);
                $Content = str_replace("{TrespasserCity}", $r_Reminder['City'], $Content);
                $Content = str_replace("{TrespasserProvince}", $r_Reminder['Province'], $Content);
                $Content = str_replace("{TrespasserAddress}", $r_Reminder['TrespasserAddress'], $Content);
                $Content = str_replace("{TrespasserZip}", $r_Reminder['ZIP'], $Content);
                $Content = str_replace("{TrespasserCountry}", $r_Reminder['CountryTitle'], $Content);
                $Content = str_replace("{TaxCode}", $TaxCode,$Content);    
                $Content = str_replace("{FineDate}", DateOutDB($r_Reminder['FineDate']),$Content);
                $Content = str_replace("{FineTime}", TimeOutDB($r_Reminder['FineTime']),$Content);
                $Content = str_replace("{CurrentDate}", $CreationDate, $Content);
                //$Content = str_replace("{ReminderType}", $str_ReminderType, $Content);
                $Content = str_replace("{Address}", StringOutDB($r_Reminder['Address']), $Content);
                $Content = str_replace("{VehiclePlate}", StringOutDB($r_Reminder['VehiclePlate']), $Content);
                $Content = str_replace("{VehicleType}", StringOutDB($r_Reminder['VehicleType']), $Content);
                $Content = str_replace("{Code}", $r_Reminder['Code'],$Content);
                $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
                $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
                $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);         
                $Content = str_replace("{Fee}", NumberDisplay($r_Reminder['Fee']), $Content);
                $Content = str_replace("{PaymentDayReminder}", $PaymentDayReminder, $Content);
                
                //il valore della sanzione dipende dallo scaglione in cui è stato calcolato il ricorso
                // che è stato salvato con valore negativo
                // differenza tra la data di creazione del sollecito e la data di notifica
                $ReminderDays = DateDiff("D", $r_Reminder['NotificationDate'], $r_Reminder['PrintDate']);
                //echo "da elaborazione a notifica: $ReminderDays". "date notifica e creazione sollecito ". $r_Reminder['NotificationDate'] ." - " . $r_Reminder['PrintDate'];
                
                //se non sono ancora passati 60 giorni dalla notifica non va mostrata la maggiorazione fino alla metà del massimo
                //FIXME non sono state gestite $b_dispute e $DisputeFee
                if($r_Reminder['ReducedPayment'] == 1)
                {
                    //5gg+toll
                    if ($ReminderDays<=(FINE_DAY_LIMIT_REDUCTION+$ReducedPaymentDayAccepted))
                        $Content = str_replace("{MaxFee}", NumberDisplay(0), $Content);
                    //60gg+toll
                    else if ($ReminderDays<=(FINE_DAY_LIMIT+$PaymentDayAccepted))
                        $Content = str_replace("{MaxFee}", NumberDisplay(0), $Content);
                    //Oltre i 60gg
                    else if ($ReminderDays > FINE_DAY_LIMIT)
                        $Content = str_replace("{MaxFee}", NumberDisplay($r_Reminder['MaxFee']), $Content);
                }
                else
                {
                    //60gg+toll
                    if($ReminderDays<=(FINE_DAY_LIMIT+$PaymentDayAccepted))
                        $Content = str_replace("{MaxFee}", NumberDisplay(0), $Content);
                    //Oltre i 60gg
                    else if ($ReminderDays > FINE_DAY_LIMIT)
                        $Content = str_replace("{MaxFee}", NumberDisplay($r_Reminder['MaxFee']), $Content);
                }
                
                $Content = str_replace("{HalfMaxFee}", NumberDisplay($r_Reminder['HalfMaxFee']), $Content);
                $Content = str_replace("{TotalNotification}", NumberDisplay($r_Reminder['TotalNotification']), $Content);
                $Content = str_replace("{AdditionalFee}", NumberDisplay($r_Reminder['TotalNotification']), $Content); //In questo caso, essendo il primo sollecito, TotalNotification rappresenta le spese del sollecito attuale.
                $Content = str_replace("{NotificationFeeHistory}", NumberDisplay(0), $Content); //Questa pagina è usata per il primo sollecito, quindi non ci sono spese di notifica pregresse
                $Content = str_replace("{Amount}", NumberDisplay($r_Reminder['Amount']), $Content);
                $Content = str_replace("{TotalAmount}", NumberDisplay($TotaleDovuto), $Content);
                $Content = str_replace("{Percentual}", NumberDisplay($r_Reminder['Percentual']), $Content);
                $Content = str_replace("{PercentualAmount}", NumberDisplay($r_Reminder['PercentualAmount']), $Content);
                $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);
                $Content = str_replace("{Semesters}", $r_Reminder['Semester'], $Content);
                $Content = str_replace("{Locality}", $r_Reminder['CityTitle'],$Content);
                $Content = str_replace("{CityTitle}", $r_Reminder['CityId'],$Content);
                $Content = str_replace("{TrespasserId}", $r_Reminder['TrespasserCode'],$Content);
                $Content = str_replace("{SendDate}", DateOutDB($r_Reminder['SendDate']),$Content);
                $Content = str_replace("{DeliveryDate}", DateOutDB($r_Reminder['DeliveryDate']),$Content);
                $Content = str_replace("{BankOwner}", $r_Customer['ForeignReminderBankOwner'],$Content);
                $Content = str_replace("{BankName}", $r_Customer['ForeignReminderBankName'],$Content);
                $Content = str_replace("{BankAccount}", $r_Customer['ForeignReminderBankAccount'],$Content);
                $Content = str_replace("{BankSwift}", $r_Customer['ForeignReminderBankSwift'],$Content);
                $Content = str_replace("{BankIban}", $r_Customer['ForeignReminderBankIban'],$Content);
                $Content = str_replace("{ManagerDataEntryName}",$r_Customer['ManagerDataEntryName'], $Content);
                $Content = str_replace("{ManagerProcessName}",$r_Customer['ManagerProcessName'], $Content);
                $Content = str_replace("{ManagerCity}",$r_Customer['ManagerCity'], $Content);
                $Content = str_replace("{ManagerWeb}",$r_Customer['ManagerWeb'], $Content);
                $Content = str_replace("{Date}",$CreationDate,$Content);
                $Content = str_replace("{ReminderCode}",$str_ReminderCode, $Content);
                $Content = str_replace("{ReminderId}",$r_Reminder['ReminderId'], $Content);
                
                $pdf->SetFont($font, '', 8);
                
                //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
                $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
                $TCPDF_pagebreak = '<br pagebreak="true" />';
                preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
                
                $pdf->SetAutoPageBreak(true, 0);
                $pdf->SetPrintHeader(false);         
                $pdf->writeHTML($Content, true, true, true, false, '');
                
                //PAGOPA
//                 if($b_PagoPAEnabled){
//                     $style = array(
//                         'border' => 1,
//                         'vpadding' => 'auto',
//                         'hpadding' => 'auto',
//                         'fgcolor' => array(0,0,0),
//                         'bgcolor' => false, //array(255,255,255)
//                         'module_width' => 1, // width of a single module in points
//                         'module_height' => 1 // height of a single module in points
//                     );
                    
//                     if ($r_ArticleTariff['ReducedPayment']) {
//                         $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
//                         $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
                        
//                     } else {
//                         $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
//                         $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
//                     }
                    
//                     $url_PagoPAPage = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";
                    
//                     if($r_Reminder['PagoPA1']!='' && $r_Reminder['PagoPA2']!=''){
//                         $pdf->write2DBarcode($url_PagoPAPage.$r_Reminder['PagoPA1'], 'QRCODE,H', 60, 240, 30, 30, $style, 'N');
//                         $pdf->writeHTMLCell(30, 0, 60, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
//                         $pdf->write2DBarcode($url_PagoPAPage.$r_Reminder['PagoPA2'], 'QRCODE,H', 120, 240, 30, 30, $style, 'N');
//                         $pdf->writeHTMLCell(30, 0, 120, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
//                     }
//                 }
                //FINE PAGOPA
                
                //Conta le pagine attuali, se sono dispari aggiunge una pagina bianca in fondo
                $PageNo= $pdf->PageNo();
                if($PageNo%2 == 1){
                    $pdf->AddPage('P', $page_format);
                }
            }
            
            //NOME FILE
            $Documentation = str_replace("/","-", $str_ReminderCode)."_".date("Y-m-d")."_".$_SESSION['cityid']."_".$FineId.".pdf";
            
            if($ultimate){
                
                $RndCode = "";
                for($i=0;$i<5;$i++){
                    $n = rand(1, 24);
                    $RndCode .= substr($strCode,$n,1);
                    $n = rand(0, 9);
                    $RndCode .= $n;
                }
                
                if($n_ReminderCount==0) $rs->Start_Transaction();
                
                $Documentation = str_replace("/","-", $str_ReminderCode)."_".date("Y-m-d")."_".$_SESSION['cityid']."_".$RndCode.".pdf";
            }
            
            $a_DocumentationFineZip[] = $Documentation;
            $a_ReminderId[] = $FineId;


            $n_ReminderCount++;
            
            
            $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$Documentation, "F");
            //printtype
            $n_PageCount = $pdf_union->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Documentation);
            $startImport=1;
            $endImport=$n_PageCount;
            for($p=$startImport;$p<=$endImport;$p++){
                $tmp_Page = $pdf_union->ImportPage($p);
                $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                $str_Format = ($tmp_Size['w']>$tmp_Size['h']) ? 'L' : 'P';
                $pdf_union->setPrintHeader(false);
                $pdf_union->setPrintFooter(false);
                $pdf_union->AddPage($str_Format, array($tmp_Size['w'],$tmp_Size['h']),false);
                $pdf_union->useTemplate($tmp_Page);
            }
            
            if ($ultimate){
                
                $a_Insert = array(
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                    array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>DateInDB($CreationDate),'settype'=>'date'),                        
                    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$n_ControllerId),
                );
                $rs->Update('FineReminder',$a_Insert,"Id=".$ReminderId);               
                $a_Insert = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>30),
                );
                $rs->Insert('FineDocumentation',$a_Insert);
                
                $FileName = $Documentation;
            } else $FileName = 'export_createdocument_reminder_f.pdf';
        }
    }
    
    //Se si ha selezionato almeno un verbale elegibile alla creazione del sollecito procedi
    if (!$NoElegibleFine){
        
        if(!$ultimate){
            //SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE
            $TotalPages = $pdf_union->PageNo();
            for ($i=1; $i<=$TotalPages; $i++){
                $pdf_union->setPage($i, true);
                $pdf_union->SetXY(10, 250);
                $pdf_union->StartTransform();
                $pdf_union->Rotate(50);
                $pdf_union->SetFont($font, '', 22);
                $pdf_union->SetTextColor(190);
                $pdf_union->Cell(280,0,'S   T   A   M   P   A         P   R   O   V   V   I   S   O   R   I   A',0,1,'C',0,'');
                $pdf_union->StopTransform();
            }
        }
        
        if ($ultimate){
            
            $str_Definitive = "Stampa definitiva avvenuta con successo!";
            for($i=0; $i<count($a_DocumentationFineZip); $i++){
                if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid']."/".$a_ReminderId[$i])) {
                    mkdir(FOREIGN_FINE."/".$_SESSION['cityid']."/". $a_ReminderId[$i], 0777);
                }
                copy(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $a_ReminderId[$i] . "/" . $a_DocumentationFineZip[$i]);
                unlink(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);
            }
            
            $FileName = $_SESSION['cityid']."_".date("Y-m-d_H-i-s").".pdf";
            
            if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid']."/create")) {
                mkdir(FOREIGN_FINE."/".$_SESSION['cityid']."/create", 0777);
            }
            
            $pdf_union->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
            
            $_SESSION['Message'] = $str_Definitive;
            
        }else{
            //Rimuove pdf temporanei fatti per creare anteprima di stampa
            for($i=0; $i<count($a_DocumentationFineZip); $i++){
                unlink(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);
            }
            
            if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid']."/create")) {
                mkdir(FOREIGN_FINE."/".$_SESSION['cityid']."/create", 0777);
            }
            
            $pdf_union->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
        }
    }
    
}

//$rs->UnlockTables();
$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);
$rs->Update('LockedPage',$aUpdate, "Title='".CREATE_REMINDER_DOCUMENT_LOCKED_PAGE."_{$_SESSION['cityid']}'");
$rs->End_Transaction();

