<?php
require_once(CLS.'/cls_literal_number.php');
require_once(CLS . '/cls_iuv.php');
require_once(CLS."/avvisiPagoPA/ModelloBase.php");

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
$rs_CityParameters = $rs->Select('ProcessingDataPaymentNational', "CityId='".$_SESSION['cityid']."'");
$r_CityParameters = mysqli_fetch_array($rs_CityParameters);

$PaymentDayAccepted = number_format($r_CityParameters['PaymentDayAccepted'],0);
$ReducedPaymentDayAccepted = number_format($r_CityParameters['ReducedPaymentDayAccepted'],0);
//Giorni entro i quali pagare il sollecito
$PaymentDayReminder = number_format($r_CityParameters['PaymentDayReminder']);

$n_LanguageId = 1;
$FormTypeId = 30;

//PARAMETRI DELL'ENTE
$rs_Customer = $rs->Select("V_Customer", "CityId = '".$_SESSION['cityid']."' AND CreationType = 1 ", "FromDate DESC", 1);
$r_Customer  = mysqli_fetch_array($rs_Customer);

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrintDestinationFold AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_FoldReturn = $r_PrintParameter['NationalReminderFoldReturn'] ?? '';
$str_PostalAuthorization = trim($r_PrintParameter['NationalReminderPostalAuthorization'] ?? '');
$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['NationalReminderPostalAuthorizationPagoPA'] ?? '');
////////////////////////////////////////////////

$b_PagoPAEnabled = $r_Customer['PagoPAPayment'] > 0;
$b_PrintBill = ($b_PagoPAEnabled ? $r_Customer['PagoPAPaymentNoticeNational'] <= 0 : true) && !empty($r_Customer['NationalPostalType']);
$b_PrintBillPagoPA = $b_PagoPAEnabled && !empty($r_Customer['NationalPostalTypePagoPA']) && $r_Customer['PagoPAPaymentNoticeNational'] > 0;

//Se è previsto PagoPA, forzo la scelta di "Opzioni di stampa" ad entrambi
if($b_PagoPAEnabled && $PrintType!= 1) $PrintType = 1;

//Controlli parametri
if($b_PagoPAEnabled){
    //Se l'ente non ha CF/PIVA impostati
    if(empty($r_Customer['ManagerTaxCode'])){
        $str_Error .= 'È necessario specificare il codice fiscale dell\'ente per il funzionamento della gestione PagoPA (Ente > Gestione Ente > Indirizzo).<br>';
    }
}
//Se non sono state impostate opzioni di stampa o non è stato impostato "Senza bollettino"
if($b_PrintBill && $PrintType != 3){
    if(empty($r_Customer['NationalReminderBankAccount'])){
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
if($b_PrintBillPagoPA){
    //Se la stampa all'avviso di pagamento PagoPA e la stampa del bollettino postale PagoPA sono attive ma non è impostata l'autorizzazione alla stampa
    if(empty($str_PostalAuthorizationPagoPA)){
        $str_Error .= 'Per la stampa del bollettino postale PagoPA è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente o degli stampatori in base alla Destinazione di stampa selezionata.<br>';
    }
}

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

//SERVIZIO PAGOPA ENTE
//se è abilitato pagoPA interroghiamo la base dati per prendere le configurazioni
if($b_PagoPAEnabled){
    $pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
    $pagopaService=mysqli_fetch_array($pagopaServicequery);
}

$str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}

//Firmatario
$rs_Signer = $rs->Select('Controller', "Id=".$n_ControllerId);
$r_Signer = mysqli_fetch_array($rs_Signer);
$Signer = (isset($r_Signer['Qualification']) ? $r_Signer['Qualification'].' ' : '').$r_Signer['Name'];

$ultimate = CheckValue('ultimate','n');
$NoElegibleFine = true;

$str_Warning = '';
$a_DocumentationFineZip = array();
//Contiene i FineId di cui non è stato possibile generare/aggiornare PagoPA
$a_FailedPagoPA = array();

$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");

$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$CurrentDate = date("Y-m-d");
$n_ReminderCount = 0;

$a_Lan = unserialize(LANGUAGE);

if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid'])) {
    mkdir(NATIONAL_FINE."/".$_SESSION['cityid'], 0777);
}

$rs->Start_Transaction();

if(isset($_POST['checkbox'])) {
        
    $pdf_union = new FPDI();
    
    foreach($_POST['checkbox'] as $ReminderId) {
        $rs->Begin_Transaction();
        
        $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
        $pdf->TemporaryPrint= $ultimate;
        $pdf->NationalFine= 1;
        $pdf->CustomerFooter = 0;
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Reminder');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 8));
        $pdf->SetMargins(10,10,10);
        
        //echo "ReminderId $ReminderId";
        
        //leggo i dati del sollecito ultimo creato associato al verbale per creare il pdf
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
                F.Code,
                F.ProtocolId,
                F.ProtocolYear,
                F.Locality,
                F.Address,
                F.VehiclePlate,
                F.FineDate,
                F.FineTime,
                F.CityId,
                F.PagoPA1,
                F.PagoPA2,
                F.PagoPAReducedPartial,
                F.PagoPAReducedTotal,
                F.PagoPAPartial,
                F.PagoPATotal,
                F.StatusTypeId,
                F.ReminderDate AS ReminderDate,
            
                FH.NotificationTypeId,
                FH.FineId,
                FH.TrespasserTypeId,
                FH.ControllerId,
                FH.SendDate,
                FH.DeliveryDate,
            
                FA.ViolationTypeId,
                FA.ArticleId,
                TAR.ReducedPayment,

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
            
                VT.TitleIta VehicleType,
                CO.Title CountryTitle,
                FN.NotificationDate NotificationDate
            
                FROM FineReminder FR
                JOIN Fine F ON FR.FineId = F.Id AND FR.PrintDate = F.ReminderDate
                JOIN FineHistory FH ON FR.FineId=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON FR.FineId=FA.FineId
                JOIN ArticleTariff TAR ON TAR.ArticleId=FA.ArticleId and TAR.Year = F.ProtocolYear           
                JOIN FineNotification FN ON FN.FineId = FR.FineId
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN sarida.City C on C.Id = F.Locality
                JOIN sarida.Country CO ON CO.Id=T.CountryId
                JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
                WHERE FR.Id=".$ReminderId
            );
        
        $r_Reminder = mysqli_fetch_assoc($rs_Reminder);
        
        $FineId = $r_Reminder['ReminderFineId'];
        
        $a_DocumentationFineZip[$FineId] = array();
        
        $ViolationTypeId = $r_Reminder['ViolationTypeId'];
        $TrespasserName = StringOutDB((isset($r_Reminder['CompanyName']) ? $r_Reminder['CompanyName'].' ' : '') . $r_Reminder['Surname'] . ' ' . $r_Reminder['Name']);
        
        $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType1'];
        $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType2'];
        
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        
        $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Reminder['ArticleId'] . " AND Year=" . $r_Reminder['ProtocolYear']);
        $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
        
        $rs_Trespasser = $rs->Select("Trespasser", "Id={$r_Reminder['TrespasserId']}");
        $r_Trespasser = $rs->getArrayLine($rs_Trespasser);
        
        $RuleTypeId = $r_RuleType['Id'];
        
        $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
        
        $rs_ReminderLetter = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter FROM FineReminder WHERE FineId=".$FineId." AND FlowDate IS NOT NULL");
        $r_ReminderLetter = mysqli_fetch_array($rs_ReminderLetter);
        $n_ReminderLetter = $r_ReminderLetter['ReminderLetter'];
        
        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;
        
        $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
        $NotificationFee = $r_Customer['NationalReminderNotificationFee'];
        
        if ($r_Customer['IncreaseNationalNotificationFee'] != 0){
            $rs_RemindersNumber = $rs->Select("FineReminder", "FineId=$FineId");
            $n_Reminders = mysqli_num_rows($rs_RemindersNumber);
            $NotificationFee = $r_Customer['NationalReminderNotificationFee']*$n_Reminders;
        }
        
        $TaxCode = trim($r_Reminder['TaxCode']);
        $VatCode = trim($r_Reminder{'VatCode'});
        $TrespasserCode = $TaxCode != null ? $TaxCode : ($VatCode != null ? $VatCode : "");
        
        $TotaleDovuto = $r_Reminder['TotalAmount'] - $r_Reminder['Amount'];
        
        //Coordinata inizio stampa testo dinamico
        $TextStartCoord = array('X'=>10, 'Y'=>92);
        
        //Se provvisorio evidenzia il testo in giallo
        $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
        $pdf->SetTextColor(0, 0, 0);
        
        //Se l'ammontare totale è negativo il verbale non è elegibile alla creazione del sollecito, altrimenti procedi
        if ($r_Reminder['TotalAmount'] < 0){
            $a_InvalidReminders[] = $FineId." - Targa: ".$r_Reminder['VehiclePlate'];
        } else {
            
            $NoElegibleFine = false;
            //echo "ReminderId $ReminderId is a valid reminder";
            //nel caso solo bollettino non stampa nulla
            if($PrintType != 2){
                $pdf->RightHeader = false;            
                
                $page_format = array('Rotate'=>45);
                $pdf->SetMargins(10,8,10);
                $pdf->AddPage('P', $page_format);            
                //Prende il contenuto del testo
                $forms = $rs->Select('FormDynamic',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
                $form = mysqli_fetch_array($forms);
                $Content = StringOutDB($form['Content']);
                $pdf->setPrintHeader(true);
                //INTESTAZIONE
                //Se Intestazione SARIDA è abilitata nei parametri ente, scrive l'intestazione di Sarida, altrimenti quella dell'ente
                if ($r_Customer['NationalReminderHeaderSarida'] == 1){
                    $pdf->Image('img/sarida.jpg', 3, 10, 12, 17);
                    
                    $ManagerName = $r_Customer['ManagerName'];
                    $pdf->customer = $ManagerName;
                    
                    $pdf->SetFont('helvetica', '', 8, '', true);
                    
                    $pdf->writeHTMLCell(75, 0, 15, '', "<strong>Concessionario Sarida srl</strong>", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(75, 0, 15, '', "Via Monsignor Vattuone, 9/6 - 16039 Sestri Levante (GE)", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(75, 0, 15, '', "P.IVA 01338160995", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(75, 0, 15, '', "Tel: 01851830468 - Fax: 0185457447", 0, 0, 1, true, 'L', true);
                    $pdf->LN(3);
                    $pdf->writeHTMLCell(75, 0, 15, '', "eMail: posta@sarida.it - Sito: www.sarida.it", 0, 0, 1, true, 'L', true);
                    
                    if ($str_FoldReturn != ""){
                        $pdf->SetFont('helvetica', '', 7, '', true);
                        $pdf->LN(3);
                        $pdf->writeHTMLCell(75, 0, 15, '', "Restituzione piego in caso di mancato recapito:", 0, 0, 1, true, 'L', true);
                        $pdf->LN(3);
                        $pdf->writeHTMLCell(75, 0, 15, '', $str_FoldReturn, 0, 0, 1, true, 'L', true);
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
                $pdf->MultiCell(90, 0, strtoupper(StringOutDB($a_GenreLetter[$r_Reminder['Genre']].' '.$TrespasserName)), 0, 'L', 1, 1, 110, 55.5, true);
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
                            $rs_variable = $rs->Select('FormVariable',"Id='$var' AND FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId." And NationalityId=1");
                            while ($r_variable = mysqli_fetch_array($rs_variable)){
                                $a_Types[$r_variable['Type']] = $r_variable['Content'];
                                //trigger_error("Variabile: ".$r_variable['Type']." Testo: ".$a_Types[$r_variable['Type']] = $r_variable['Content']);
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
                            }
                            //Sottotesto contenuto
                            if ($var == "{{ReminderContent}}"){
                                $str_ReminderContent = "";
                                if ($r_Reminder['Amount']>=0.01) {
                                    if($r_Reminder['DaysFromNotificationDate']<=FINE_DAY_LIMIT){
                                        $str_ReminderContent = $a_Types[2];
                                    } else $str_ReminderContent = $a_Types[3];
                                } else $str_ReminderContent = $a_Types[1];
                                $Content = str_replace("{{ReminderContent}}", $str_ReminderContent, $Content);
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
                            }
                            else $Content = str_replace($var, $a_Types[1], $Content);
                        }
                    } else $EmptyPregMatch = true;
                }
                //Sostituisce le variabili
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
                $Content = str_replace("{TrespasserName}", $TrespasserName, $Content);
                $Content = str_replace("{TrespasserGenre}", $a_GenreLetter[$r_Reminder['Genre']], $Content);
                $Content = str_replace("{TrespasserCity}", $r_Reminder['City'], $Content);
                $Content = str_replace("{TrespasserProvince}", $r_Reminder['Province'], $Content);
                $Content = str_replace("{TrespasserAddress}", $r_Reminder['TrespasserAddress'], $Content);
                $Content = str_replace("{TrespasserZip}", $r_Reminder['ZIP'], $Content);
                $Content = str_replace("{TrespasserCountry}", $r_Reminder['CountryTitle'], $Content);
                $Content = str_replace("{TaxCode}", $TaxCode,$Content);
                $Content = str_replace("{FineDate}", DateOutDB($r_Reminder['FineDate']),$Content);
                $Content = str_replace("{FineTime}", TimeOutDB($r_Reminder['FineTime']),$Content);
                $Content = str_replace("{CurrentDate}", $CreationDate, $Content);
                $Content = str_replace("{Address}", StringOutDB($r_Reminder['Address']), $Content);
                $Content = str_replace("{VehiclePlate}", StringOutDB($r_Reminder['VehiclePlate']), $Content);
                $Content = str_replace("{VehicleType}", StringOutDB($r_Reminder['VehicleType']), $Content);
                $Content = str_replace("{Code}", $r_Reminder['Code'],$Content);
                $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
                $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
                $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);
                $Content = str_replace("{Fee}", NumberDisplay($r_Reminder['Fee']), $Content);
                $Content = str_replace("{PaymentDayReminder}", $PaymentDayReminder, $Content);
                
                $QRCode = false;
                $QRCodeURL = false;
                if (strpos($Content, '{QRCode1}') !== false){
                    $QRCode = true;
                    $Content = str_replace("{QRCode1}", '', $Content);
                }
                if (strpos($Content, '{QRCodeURL1}') !== false){
                    $QRCodeURL = true;
                    $Content = str_replace("{QRCodeURL1}", '', $Content);
                }
                
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
                $Content = str_replace("{BankOwner}", $r_Customer['NationalReminderBankOwner'],$Content);
                $Content = str_replace("{BankName}", $r_Customer['NationalReminderBankName'],$Content);
                $Content = str_replace("{BankAccount}", $r_Customer['NationalReminderBankAccount'],$Content);
                $Content = str_replace("{BankSwift}", $r_Customer['NationalReminderBankSwift'],$Content);
                $Content = str_replace("{BankIban}", $r_Customer['NationalReminderBankIban'],$Content);
                $Content = str_replace("{ManagerDataEntryName}",$r_Customer['ManagerDataEntryName'], $Content);
                $Content = str_replace("{ManagerProcessName}",$r_Customer['ManagerProcessName'], $Content);
                $Content = str_replace("{ManagerCity}",$r_Customer['ManagerCity'], $Content);
                $Content = str_replace("{ManagerWeb}",$r_Customer['ManagerWeb'], $Content);
                $Content = str_replace("{Date}",$CreationDate,$Content);
                $Content = str_replace("{ReminderCode}",$str_ReminderCode, $Content);
                $Content = str_replace("{ReminderId}",$r_Reminder['ReminderId'], $Content);
                
                $pdf->SetFont('helvetica', '', 8);
                
                //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
                $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
                $TCPDF_pagebreak = '<br pagebreak="true" />';
                preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
                
                $pdf->SetAutoPageBreak(true, 0);
                $pdf->SetPrintHeader(false);       
                
                $pdf->writeHTML($Content, true, true, true, false, '');
                
                //PAGOPA
                $PagoPACode1 = $r_Reminder['PagoPA1'];
                $PagoPACode2 = $r_Reminder['PagoPA2'];
                if($b_PagoPAEnabled){
                    //FIXME trovare una soluzione per fare in modo di dividere gli importi per ogni articolo
                    //dentro a getFineFees quando si gestiranno più capitoli di bilancio e creare la struttura dati secondo calcolaImporti
                    $feeIndex = "Total";
                    $a_Importi = array(
                        'Amounts' => array(
                            array(
                                $feeIndex=>number_format(((float)$TotaleDovuto), 2, '.', ''),
                                'ViolationTypeId' => $ViolationTypeId
                            )
                        ),
                        'Sum' => array(
                            $feeIndex=>number_format(((float)$TotaleDovuto), 2, '.', ''),
                        )
                    );
                    
                    $cls_iuv = new cls_iuv();
                    $GenreParemeter = ($r_Reminder['Genre'] == "D")? "D" : "P";
                    $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
                    $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
                    $TrespasserType = ($r_Reminder['Genre'] == "D") ? "G" : "F";
                    $FineText = 'Anno ' . $r_Reminder['ProtocolYear'] . ' targa ' . $r_Reminder['VehiclePlate'];
                    $IUV = null;
                    $b_PagoPAFail1 = $b_PagoPAFail2 = false;
                    
                    $a_FineUpd = array();
                    
                    if ($ultimate && !isset($a_FailedPagoPA[$FineId])){
                        if(empty($PagoPACode1)){
                            if(empty($PagoPACode2)){
                                $IUV = callPagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $feeIndex, $r_Customer, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams);
                                if(!empty($IUV)){
                                    $a_FineUpd[] = array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                                    $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                    $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                    $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                    $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                } else $b_PagoPAFail2 = true;
                            } else {
                                if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $feeIndex, $PagoPACode2, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                                    $IUV = $PagoPACode2;
                                    $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                    $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                    $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                    $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                } else $b_PagoPAFail2 = true;
                            }
                        } else if(empty($PagoPACode2)){
                            if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $feeIndex, $PagoPACode1, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                                $IUV = $PagoPACode1;
                                $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                            } else $b_PagoPAFail1 = true;
                        } else {
                            if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $feeIndex, $PagoPACode1, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                                $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                            } else $b_PagoPAFail1 = true;

                            if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $feeIndex, $PagoPACode2, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                                $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                                $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                            } else $b_PagoPAFail2 = true;
                            
                            if(!$b_PagoPAFail1 && !$b_PagoPAFail2){
                                $IUV = $PagoPACode2;
                            }
                        }
                        
                        if(!empty($a_FineUpd)){
                            $a_FinePagoPAHistory = array(
                                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                                array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1),
                                array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2),
                                array('field'=>'ReminderId','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                                array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPAReducedPartial'] ,'settype'=>'flt'),
                                array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPAPartial'] ,'settype'=>'flt'),
                                array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPAReducedTotal'] ,'settype'=>'flt'),
                                array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPATotal'] ,'settype'=>'flt'),
                            );
                            
                            $rs->Update('Fine', $a_FineUpd, "Id=" . $FineId);
                            $rs->Insert("FinePagoPAHistory", $a_FinePagoPAHistory);
                        }
                        if($b_PagoPAFail1 || $b_PagoPAFail2){
                            trigger_error('ID '.$FineId.': Chiamata PagoPA fallita.', E_USER_WARNING);
                            $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Chiamata PagoPA fallita, il sollecito non è stato elaborato.<br>';
                        }
                    }

                    $PagoPAPaymentNotice = '';
                    
                    if (!empty($IUV)){
                        //Se l'ente prevede di usare codici avviso invece che IUV, usa direttamente quello, altrimenti tenta di costruirlo
                        //Se fallisce a costruirlo non processa l'atto e restituisce un avviso
                        if($r_Customer['IsIuvCodiceAvviso'] != 1){
                            try {
                                $PagoPAPaymentNotice = $cls_iuv->generateNoticeCode($IUV, $r_Customer['PagoPAAuxCode'], $r_Customer['PagoPAApplicationCode']);
                            } catch (Exception $e) {
                                if(!isset($a_FailedPagoPA[$FineId])) $a_FailedPagoPA[$FineId] = 'ID '.$FineId.": Errore nella costruzione del codice avviso PagoPA: $e. Il sollecito non verrà processato. Verificare il codice IUV e le configurazioni.";
                                $PagoPAPaymentNotice = '';
                            }
                        } else $PagoPAPaymentNotice = $IUV;
                        
                        $Content = str_replace("{PagoPA1}", $IUV, $Content);
                        $Content = str_replace("{PagoPA1PaymentNotice}", $PagoPAPaymentNotice, $Content);
                    } else {
                        $Content = str_replace("{PagoPA1}", 'XXXXXXXXX', $Content);
                        $Content = str_replace("{PagoPA1PaymentNotice}", 'XXXXXXXXX', $Content);
                    }
                    
                }
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
                
                $Documentation = str_replace("/","-", $str_ReminderCode)."_".date("Y-m-d")."_".$_SESSION['cityid']."_".$RndCode.".pdf";
            }
            
            $a_DocumentationFineZip[$FineId] = $Documentation;
            
            ////    BILL
            if($b_PrintBill && $PrintType != 3){
                $page_format = array('Rotate'=>-90);
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false);            
                $pdf->AddPage('L', $page_format);
                $pdf->crea_bollettino();            
                //Calcoli quinto campo
                $a_FifthField = array("Table" => 1, "Id" => $FineId);
                $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 0 : 1;
                $a_FifthField['DocumentType'] = 3; //DocumentType a 3 indica il sollecito
                $str_FifthField = SetFifthField($a_FifthField);
                $a_Address = array();
                $a_Address['Riga1'] = $r_Reminder['TrespasserAddress'];
                $a_Address['Riga2'] = '';
                $a_Address['Riga3'] = $r_Reminder['ZIP'];
                $a_Address['Riga4'] = $r_Reminder['City']. ' '."(".$r_Reminder['Province'].')';
                
                $NW = new CLS_LITERAL_NUMBER();
                $numeroLetterale = $NW->converti_numero_bollettino($TotaleDovuto);
                $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'],$str_FifthField,number_format((float)$TotaleDovuto, 2, ',', ''),'si',$r_Customer['NationalReminderBankAccount']);
                $pdf->iban_bollettino($r_Customer['NationalReminderBankIban']);
                $pdf->intestatario_bollettino(substr($r_Customer['NationalReminderBankOwner'], 0, 50));
                $pdf->causale_bollettino('Sollecito '. $str_ReminderCode,'verbale cron '.$r_Reminder['ProtocolId'].'/'.$r_Reminder['ProtocolYear'].'/'.$str_ProtocolLetter);
                $pdf->zona_cliente_bollettino(substr($TrespasserName,0,35),$a_Address);
                $pdf->importo_in_lettere_bollettino($numeroLetterale);
                $pdf->set_quinto_campo($r_Customer['NationalPostalType'], $str_FifthField);
                $pdf->autorizzazione_bollettino($str_PostalAuthorization);
                
                $page_format = array('Rotate'=>45);
                $pdf->AddPage('P', $page_format);
            }
            
//////////////QRCODE O AVVISO DI PAGAMENTO////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //Se l'ente è abilitato alla stampa degli avvisi di pagamento stampa quelli, altrimenti aggiunge i qrcode alla vecchia maniera
            if($r_Customer['PagoPAPaymentNoticeNational'] > 0 && $b_PagoPAEnabled){
                
                $oggettoAvviso = 'Sollecito '.$str_ReminderCode.' del verbale Prot. '.$r_Reminder['ProtocolId'].(!$ultimate ? ' - PROVV' : '').'/'.$r_Reminder['ProtocolYear'].'/'.$str_ProtocolLetter;
                $causaleBollettino = 'Sollecito '.$str_ReminderCode.' Cron. ' . $r_Reminder['ProtocolId'] . '/' . $r_Reminder['ProtocolYear'] . '/' . $str_ProtocolLetter . ' targa ' . $r_Reminder['VehiclePlate'] . ' ' . $r_Reminder['Code'] . ' DEL ' . DateOutDB($CreationDate);
                $b_ErroreAvviso = false;
                
                try{
                    $o_Avviso = new Avviso($oggettoAvviso, $_SESSION['blazon']);
                    $o_Ente = new Ente($r_Customer['ManagerName'], $r_Customer['ManagerSector'], buildManagerInfo($r_Customer), trim($r_Customer['ManagerTaxCode']), $r_Customer['PagoPACBILL']);
                    $o_Destinatario = new Destinatario($TaxCode, $TrespasserName, $str_TrespasserAddress);
                    $o_Importo = new Importo($TotaleDovuto, $PagoPAPaymentNotice);
                    
                    $avviso = new ModelloBase($o_Avviso, $o_Ente, $o_Destinatario, $o_Importo);
                    if ($b_PrintBillPagoPA){
                        $avviso->setBollettino(new Bollettino($str_PostalAuthorizationPagoPA, $r_Customer['NationalBankAccount'], $r_Customer['NationalBankOwner'], $r_Customer['NationalPostalTypePagoPA'], $causaleBollettino));
                    }
                    $avviso->costruisci(true);
                    
                    $avviso->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . 'avviso_'.$Documentation , "F");
                } catch (Error $e){
                    if(!isset($a_FailedPagoPA[$FineId])) $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Errore nella costruzione dell\'avviso di pagamento PagoPA, l\'atto non verrà processato. Verificare i dati e contattare l\'amministrazione di sistema.<br>';
                    trigger_error('ID '.$FineId.': Errore nella costruzione dell\'avviso di pagamento PagoPA: '.$e, E_USER_WARNING);
                    $b_ErroreAvviso = true;
                }
            } else {
                $dettaglioImporto = "Importo entro $PaymentDayReminder giorni";
                $PagoPACodeFull = AvvisoBase::buildQRCode($PagoPAPaymentNotice, trim($r_Customer['ManagerTaxCode']), $TotaleDovuto);
                
                //Muove il puntatore alla seconda pagina per stampare il qrcode
                $CurrentPage = $pdf->PageNo();
                $pdf->setPage(2, true);
                $pdf->SetXY(0, 0);
                
                //QRCODE DIRETTO
                if (!empty($IUV) && $QRCode){
                    $pdf->write2DBarcode($PagoPACodeFull, 'QRCODE,M', 87, 240, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                    $pdf->writeHTMLCell(70, 0, 68, 271, $dettaglioImporto, 0, 0, 1,true, true, 'C', true);
                }
                
                //QRCODE URL
                if(isset($pagopaService)){
                    $url_PagoPAPage = pickPagoPAPaymentUrl($pagopaService['Id'], array('iuv' => $IUV));
                    if (!empty($IUV) && $QRCodeURL){
                        $pdf->write2DBarcode($url_PagoPAPage, 'QRCODE,M', 40, 237, 30, 30, AvvisoBase::QRCODE_STYLE, 'N');
                        $pdf->writeHTMLCell(70, 0, 20, 271, $dettaglioImporto, 0, 0, 1,true, true, 'C', true);
                        $pdf->writeHTMLCell(70, 0, 20, 268, 'IUV: '.$IUV, 0, 0, 1,true, true, 'C', true);
                    }
                }
                
                //Muove il puntatore alla posizione precedente
                $pdf->setPage($CurrentPage, true);
            }
            
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            $pdf->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$Documentation, "F");

            //Allega l'avviso di pagamento generato se era abilitata la gestione
            if($r_Customer['PagoPAPaymentNoticeNational'] > 0 && $b_PagoPAEnabled && !$b_ErroreAvviso){
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
                    if(!isset($a_FailedPagoPA[$FineId])) $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Errore nella fusione del verbale e dell\'avviso di pagamento PagoPA. Contattare l\'amministrazione di sistema.<br>';
                    trigger_error("<STAMPASOLLECITI> ATTENZIONE -> Errore nell\'unione del pdf dell'avviso di pagamento avviso_$Documentation: $e",E_USER_WARNING);
                }
                
                $pdf_unionavviso->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
                
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
            }
            
            //Se l'id del verbale non è fra quelli di cui PagoPA ha fallito, esegue le query e aggiunge i pdf generati al pdf unito
            if(!isset($a_FailedPagoPA[$FineId])){
                $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Documentation);
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
                }
            } else {
                foreach($a_DocumentationFineZip[$FineId] as $Doc){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
                }
            }
            
            if(!$ultimate){
                applyTemporaryLabel($pdf_union);
            }
            
            $n_ReminderCount++;
        }
        
        $rs->End_Transaction();
    }
    
    //Se si ha selezionato almeno un verbale elegibile alla creazione del sollecito procedi
    if (!$NoElegibleFine){
        if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid']."/create")) {
            mkdir(NATIONAL_FINE."/".$_SESSION['cityid']."/create", 0770, true);
            chmod(NATIONAL_FINE."/".$_SESSION['cityid']."/create", 0770);
        }
        if ($ultimate){
            $FileName = $_SESSION['cityid']."_".date("Y-m-d_H-i-s").".pdf";
            foreach ($a_DocumentationFineZip as $DocFineId => $Doc){
                if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid']."/".$DocFineId)) {
                    mkdir(NATIONAL_FINE."/".$_SESSION['cityid']."/". $DocFineId, 0770, true);
                    chmod(NATIONAL_FINE."/".$_SESSION['cityid']."/". $DocFineId, 0770);
                }
                copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc, NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . "/" . $Doc);
                unlink(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$Doc);
            }
            
            $pdf_union->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
        }else{
            $FileName = 'export_createdocument_reminder_n.pdf';
            
            //Rimuove pdf temporanei fatti per creare anteprima di stampa
            foreach ($a_DocumentationFineZip as $DocFineId => $Doc){
                if(!isset($a_FailedPagoPA[$DocFineId])){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
                }
            }
            
            $pdf_union->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/create/'.$FileName;

        }
    }
    
}


foreach($a_FailedPagoPA as $pagopaErrMessage){
    $str_Warning .= $pagopaErrMessage.'<br>';
}

if ($str_Warning != ''){
    $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
} else {
    if($ultimate){
        $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
    }
}

//$rs->UnlockTables();
$rs->Begin_Transaction();
$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);
$rs->Update('LockedPage',$aUpdate, "Title='".CREATE_REMINDER_DOCUMENT_LOCKED_PAGE."_{$_SESSION['cityid']}'");
$rs->End_Transaction();

