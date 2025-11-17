<?php
require_once(CLS."/cls_pagamenti.php");
require_once(CLS.'/cls_literal_number.php');
include(INC."/function_postalCharge.php");
require_once(CLS."/cls_dispute.php");
require_once(CLS."/cls_view.php");

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_REMINDER_LOCKED_PAGE."_{$_SESSION['cityid']}'");
$r_Locked = mysqli_fetch_assoc($rs_Locked);

if($r_Locked){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".$P);
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".CREATE_REMINDER_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => CREATE_REMINDER_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//IMPORTANTE: NON cambiare il font 'dejavusans', serve il supporto per i caratteri UTF8 lituani e non tutti i font lo hanno
$font = 'dejavusans';

$FormTypeId = 29;
$str_Error = '';

//leggo data elaborazione
$ProcessingDate = CheckValue('ProcessingDate','s');
$ProcessingDate = DateInDB($ProcessingDate);
// echo "<br >data elaborazione";
// print_r($ProcessingDate);
$CreationDate = CheckValue('CreationDate','s');
$n_ControllerId = CheckValue('ControllerId','s');

//RECUPERO PARAMETRI PER CREAZIONE SOLLECITO
//$FinePDFList = $r_Customer['FinePDFList'];

//PARAMETRI DELL'ENTE
$rs_Customer = $rs->Select("V_Customer", "CityId = '".$_SESSION['cityid']."' AND CreationType = 1 "
    . "AND ((FromDate <= '".$ProcessingDate."' AND ToDate >= '".$ProcessingDate."') OR (COALESCE(FromDate, '0001-01-01') <= '".$ProcessingDate."' AND ToDate IS NULL))", "FromDate DESC", 1);
$r_Customer  = mysqli_fetch_array($rs_Customer);

//     echo "<br >festa patronale";
//     print_r($r_Customer);

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrintDestinationFold AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_FoldReturn = $r_PrintParameter['ForeignReminderFoldReturn'] ?? '';
$str_PostalAuthorization = trim($r_PrintParameter['ForeignReminderPostalAuthorization'] ?? '');
//$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['NationalReminderPostalAuthorizationPagoPA']) ?: $r_Customer['NationalReminderPostalAuthorizationPagoPA'];
////////////////////////////////////////////////

$b_PagoPAEnabled = $r_Customer['PagoPAPaymentForeign'] > 0;
//TODO da rivedere con l'introduzione della gestione PagoPA sui solleciti.
$b_PrintBill = /*($b_PagoPAEnabled ? $r_Customer['PagoPAPaymentNoticeNational'] <= 0 : true) &&*/ !empty($r_Customer['ForeignPostalType']);
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
if(empty($r_Customer['PatronalFeast']) ){
    $str_Error .= "Non è possibile procedere con l'elaborazione se non è stata indicata la data della festa patronale tra le configurazioni dell'ente competente.<br >Compilare il campo Festa patronale nella scheda Indirizzo del menù Ente\Gestione Ente.<br>";
}

if(!empty($str_Error)){
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_REMINDER_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $_SESSION['Message']['Error'] = $str_Error;
    header("location: ".impostaParametriUrl(array('P' => 'frm_senddynamic_reminder.php'), 'frm_senddynamic_reminder.php'));
    DIE;
}

$str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
}

//SELEZIONE PARAMETRI PROCEDURA DI PAGAMENTO
$rs_ProcessingData = $rs->Select('ProcessingDataPaymentForeign', "CityId='".$_SESSION['cityid']."'");
$r_ProcessingData = mysqli_fetch_array($rs_ProcessingData);

//LETTURA PARAMETRI PROCEDURA DI PAGAMENTO
$CityId                         = $r_ProcessingData['CityId'];
$RangeDayMin                    = $r_ProcessingData['RangeDayMin']; //Tempo (gg) minimo: tempo minimo entro cui va pagato il sollecito
$RangeDayMax                    = $r_ProcessingData['RangeDayMax']; //Tempo (gg) massimo: giorni massimi da considerare (180 default)
$f_AmountLimit                  = $r_ProcessingData['AmountLimit']; //Importo da pagare soglia sotto cui non va emesso il sollecito
$n_ReducedPaymentDayAccepted    = $r_ProcessingData['ReducedPaymentDayAccepted']; //gg pagamento ridotti sono i gg di tolleranza per valutare il ritardo di pagamento nel caso dei pagati ridotti
$n_PaymentDayAccepted           = $r_ProcessingData['PaymentDayAccepted']; //gg pagamento normale  sono i gg di tolleranza per valutare il ritardo di pagamento nel caso dei pagati normali
//Giorni entro i quali pagare il sollecito
$PaymentDayReminder = number_format($r_ProcessingData['PaymentDayReminder']);

$WaitDay = $r_ProcessingData['WaitDay']; //Giorni ulteriore attesa

$IncludeNotificationResearch = $r_ProcessingData['IncludeNotificationResearch']; //dice se includere le spese quando la sanzione è fissata nel ricorso
$ApplyPercentualOnPrefectureFee = $r_ProcessingData['ApplyPercentualOnPrefectureFee']; //dise se applicare la maggiorazione semestrale alla sanzione fissata nel ricorso

//ALTRI PARAMETRI


$rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentForeignPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$CityId."'");
$PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentForeignPaymentDayReminder'];

$ultimate = CheckValue('ultimate','n');
$NoElegibleFine = true;

$str_Warning = '';

$a_DocumentationFineZip = array();
$a_ReminderId = array();
//$a_InvalidReminders = array();


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

//$CurrentDate = date("Y-m-d");
$n_ReminderCount = 0;


$a_LanKeys = unserialize(LANGUAGE_KEYS);
$a_Reminder =  unserialize(REMINDER);

$rs->Start_Transaction();

if(isset($_POST['checkbox'])) {
    
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array('helvetica', '', 8));
    $pdf_union->setFooterFont(array('helvetica', '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);
    
    //Firmatario
    $rs_Signer = $rs->Select('Controller', "Id=".$n_ControllerId);
    $r_Signer = mysqli_fetch_array($rs_Signer);
    $Signer = (isset($r_Signer['Qualification']) ? $r_Signer['Qualification'].' ' : '').$r_Signer['Name'];
    
    $n_CountFineNotPayed = 0; //Verbali non pagati (vanno in stato 27)
    $n_CountFinePartialPayed = 0; //Verbali pagati parzialmente (vanno in stato 28)
    $n_CountFineLatePayed = 0; //Verbali pagati parzialmente in ritardo (vanno in stato 28)
    $n_CountFineDispute = 0; //Verbali con ricorso aperto (vengono messi in lista ma saltati dall'elaborazione)
    $n_CountFineDisputeClosed = 0; //Verbali con ricorso chiuso accolto (vengono saltati dall'elaborazione finchè non passano i termini poi vanno in stato 27)
    $n_CountFinePrescription = 0; //Verbali prescritti (vengono messi in lista ma saltati dall'elaborazione)
    $n_CountFineClosed = 0; //Verbali pagati (vanno in stato 30)
    
    foreach($_POST['checkbox'] as $FineId) {
        $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
        $pdf->TemporaryPrint= $ultimate;
        $pdf->NationalFine= 0; //forse ci va 2
        $pdf->CustomerFooter = 0;
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Reminder');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setHeaderFont(array($font, '', 8));
        $pdf->setFooterFont(array($font, '', 8));
        $pdf->SetMargins(10,10,10);
        
        //Vedere come prendere il cityid dal verbale
        $cls_pagamenti = new cls_pagamenti($FineId, $_SESSION['cityid'], $ProcessingDate);
        
        //recupera tutti i record dei verbali notificati in un certo stato per cui esaminare i pagamenti
        $rs_FineProcedure = $rs->Select('V_PaymentProcedure', "Id=".$FineId);
        $r_FineProcedure = mysqli_fetch_array($rs_FineProcedure);
        
        $r_ReminderMax = 0;
        $rs_ReminderMax = $rs->SelectQuery("SELECT Max(Id) as LastId FROM FineReminder");
        if(mysqli_num_rows($rs_ReminderMax)>0)
            $r_ReminderMax = mysqli_fetch_array($rs_ReminderMax)['LastId'];
        
        //recupero dati del verbale da riportare sul sollecito
        $rs_Reminder = $rs->SelectQuery(
            "SELECT
            F.Id,
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
            
            FH.NotificationTypeId,
            FH.FineId,
            FH.TrespasserId,
            FH.TrespasserTypeId,
            FH.NotificationFee,
            FH.ResearchFee,
            FH.OtherFee,
            FH.ControllerId,
            FH.SendDate,
            FH.DeliveryDate,
            
            FA.Fee,
            FA.MaxFee,
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

            L.Title Language,

            CO.Title CountryTitle,
            
            FR.FineId AS ReminderFineId,
            FR.PrintDate
            
            FROM Fine F
            JOIN FineHistory FH ON F.Id=FH.FineId AND FH.NotificationTypeId=6
            JOIN FineArticle FA ON F.Id=FA.FineId
            JOIN ArticleTariff TAR ON TAR.ArticleId=FA.ArticleId and TAR.Year = F.ProtocolYear
            JOIN Trespasser T ON FH.TrespasserId = T.Id
            JOIN Language L ON T.LanguageId = L.Id
            JOIN sarida.City C on C.Id = F.Locality
            JOIN sarida.Country CO ON CO.Id=T.CountryId
			JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
            LEFT JOIN FineReminder FR ON F.Id = FR.FineId
			WHERE F.Id=".$FineId
            );
        
        
        $r_Reminder = mysqli_fetch_array($rs_Reminder);
        //         echo "<br >reminder ";
        //         print_r($r_Reminder);
        
        $n_LanguageId = $r_Reminder['LanguageId'];
        
        //VARIABILI PER SANZIONI
        $AdditionalFee = $cls_pagamenti->getAdditionalFee(); //spese addizionali prese da FineHistory
        
        $b_hasDisputeFee = $cls_pagamenti->getDisputeAmount() > 0;
        $DisputeFee = $cls_pagamenti->getDisputeAmount();    //sanzione amministrativa fissata da autorità giudiziaria
        $PrefectureFee = 0; //sanzioni fissate da prefettura per determinati articoli
        
        $FineFee = $cls_pagamenti->getFineFee();           //sanzione amministrativa complessiva per tutti gli articoli
        $FineHalfMaxFee = $cls_pagamenti->getFineMaxFee();    //Metà del totale massimo edittale
        $FineMaxFee = 0;                                        //totale massimo edittale
        
        $LastReminderNotificationFee = $cls_pagamenti->getLastReminderNotificationFee();
        $LastReminderTotalNotification = $cls_pagamenti->getLastReminderTotalNotificationFee();
        $NotificationFeeHistory = $LastReminderNotificationFee+$LastReminderTotalNotification; //spese di invio solleciti precedenti
        //Spese di notifica del presente sollecito. Se flag "Spese aumentate per ulteriori solleciti" non è attivo, rimangono a 0
        $ReminderCurrentNotificationFee = $r_Customer['IncreaseForeignNotificationFee'] > 0 ? $cls_pagamenti->getCurrentCustomerReminderNotificationFee() : 0;
        $n_Semesters = $cls_pagamenti->getSemester();
        $NotificationDate = $cls_pagamenti->getNotificationDate();
        $PreviousRemindersNotificationFeeSum = $cls_pagamenti->getPreviousReminderNotificationFeesSum() ?? 0;
        
        //esisti
        $b_pagato = $b_parziale = $b_parzialeNoRit = $b_parzialeRit = $b_omesso = false;
        
        //esame ricorsi
        $b_dispute = $cls_pagamenti->getHasDispute(); // indica che c'era un ricorso; // indica che c'era un ricorso
        //indica se c'è un ricorso accettato da non elaborare
        //$b_FineDispute = false;
        //durata sospensiva ricorso
        //$n_DisputeDay = 0;
        
        $b_disputeBlock = ($cls_pagamenti->getStatus() == 5 || $cls_pagamenti->getStatus() == 6);
        
        //se non c'è ricorso o è stato respinto
        if(!$b_disputeBlock){
            //calcolo dei giorni trascorsi dalla notifica per decidere se va emesso sollecito
            //giorni dalla notifica
            $n_Day = DateDiff("D", $NotificationDate, $ProcessingDate)+1;
            //             echo '<br > differenza giorni da notifica';
            //             print_r($n_Day);
            
            //giorni dalla notifica oltre la scadenza minima di 60 gg
            //$n_CalcDay = $n_Day - ($RangeDayMin + $WaitDay);
            
            //data che determina se il sollecito è elaborabile perchè passati i termini necessari
            $ElaborationStartingDate = date('Y-m-d', strtotime($NotificationDate. ' + '.(FINE_DAY_LIMIT + $WaitDay).' days'));
            
            //TODO Sistemare la questione dei giorni aggiuntivi del ricorso
            
            //giorni oltre la scadenza massima + giorni durata ricorso
//                 if($b_dispute) {
//                     //se c'è la sospensiva accordata viene considerata
//                     if($r_FineDispute['FineSuspension'] == 1 && $r_FineDispute['DateMerit']!=NULL)
//                         $n_DisputeDay = DateDiff("D", $r_FineDispute['DateFile'], $r_FineDispute['DateMerit'])+1;
                        
//                         if ($n_NumFineDispute>1) {
//                             //se ci sono altri ricordi precedenti sommiamo le loro sospensive
//                             while($r_FineDispute_i =mysqli_fetch_array($rs_FineDispute)){
//                                 if($r_FineDispute_i['FineSuspension'] == 1)
//                                     $n_DisputeDay += DateDiff("D", $r_FineDispute_i['DateFile'], $r_FineDispute_i['DateMerit'])+1;
//                             }
//                         }
                        
//                         //                     echo "<br> giorni sospensione N ricorso <br>";
//                         //                     print_r($n_DisputeDay);
                        
//                         $RangeDayMax += $n_DisputeDay;
//                         //se c'è il ricorso i giorni dopo cui può essere elaborato il sollecito sono
//                         // i giorni trascorsi dalla notifica + 180 gg + giorni di sospsensiva
//                         $n_CalcDay = $n_Day - $RangeDayMax;
//                 }
            
            //VALUTAZIONE PRESCRIZIONE
            //Se la data di notifica avanti di 5 anni (5 anni + 270 gg per esterno) + i giorni di ricorso
            // + shitp per festività è < $ProcessingDate siamo in prescrizione
            if ($cls_pagamenti->getStatus() == 7) {
                //Prescritto non va avanti nell'elaborazione
                if ($ultimate) {
                    $PaymentProcedure = 0;
                    $a_FineNotification = array(
                        array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
                        array('field'=>'PaymentProcedureOffReason','selector'=>'value','type'=>'str','value'=>MOTIVO_PRESCRIZIOME),
                    );
                    
                    $rs->Update('FineNotification',$a_FineNotification,"FineId=".$FineId);
                }
                $n_CountFinePrescription++;
                $str_Warning .= 'ID '.$FineId.': Atto in prescrizione, non è più possibile elaborare il sollecito.<br>';
                continue;  //passa al record del verbale successivo
            }
            
            //CASO SPESE FISSATE NEL RICORSO
            if ($b_dispute && $b_hasDisputeFee) {
                    $DisputeFee = $cls_pagamenti->getDisputeAmount();
            } else {
                //SPESE CALCOLATE DA IMPORTO PREFETTURA
                if ($cls_pagamenti->getFinePrefectureFee() > 0) {
                    $PrefectureFee = $cls_pagamenti->getFinePrefectureFee();
                }
            }
            
            if($ProcessingDate > $ElaborationStartingDate) {
                
                //seleziono i pagamenti totati
                $rs_FinePayment = $rs->SelectQuery("SELECT Count(*) CountTot, SUM(Amount) Amount, MIN(PaymentDate) MinPaymentDate FROM FinePayment WHERE FineId=". $FineId);
                $r_FinePayment = mysqli_fetch_array($rs_FinePayment);
                //                 echo "<br /> pagamenti totali";
                //                 print_r($r_FinePayment);
                //$CountTotPayment = (isset($r_FinePayment['CountTot']) && !empty($r_FinePayment['CountTot'])) ? $r_FinePayment['CountTot'] : 0;
                $MinPaymentDate = (isset($r_FinePayment['MinPaymentDate']) && !empty($r_FinePayment['MinPaymentDate'])) ? DateOutDB($r_FinePayment['MinPaymentDate']) : NULL;
                $DayFromNotificationToFirstPayment = -1;
                if (!empty($MinPaymentDate))
                    $DayFromNotificationToFirstPayment = DateDiff("D", $NotificationDate, $r_FinePayment['MinPaymentDate']);
                    
                //se vanno sommate le spese dei precedenti solleciti
                
                $a_CronScaglioni = $cls_pagamenti->getCronScaglioni();
                $a_CronPagamanti = $cls_pagamenti->getCronPagamenti();
                
                //Totale pagato
                $TotalAmountPayed = $cls_pagamenti->getPayed();
                //Totale dovuto (aggiungo le spese di notifica del sollecito corrente)
                $TotalAmount = $cls_pagamenti->getFee();
                //trigger_error("Fee: ".$cls_pagamenti->getFee()." Current reminder fee: ".$cls_pagamenti->getCurrentCustomerReminderNotificationFee()." Payed : ".$cls_pagamenti->getPayed());
                //Parte di maggiorazione
                $Surcharge = $cls_pagamenti->getSurcharge();
                
                // data di notifica
                $NotificationDate = $r_FineProcedure['NotificationDate'];
                
                //data che determina se il sollecito è elaborabile perchè passati i termini necessari
                $ElaborationStartingDate = date('Y-m-d', strtotime($NotificationDate. ' + '.(FINE_DAY_LIMIT + $WaitDay).' days'));
                
                //Se non ha ricorsi oppure ne ha senza spese fissate, guarda i pagamenti nelle tolleranze. Il ricorso in tutti i casi non dev'essere accolto
                if((!$cls_pagamenti->getHasDispute() || ($cls_pagamenti->getHasDispute() && !$b_hasDisputeFee)) && ($cls_pagamenti->getStatus()!=6)){
                    //NOTA: Dovuto e pagato sono passate per riferimento, perchè la funzione ne deve aggiornare i valori
                    $scaglionePagatoTolleranze = controllaTolleranze($CityId, $NotificationDate, $a_CronScaglioni, $a_CronPagamanti, $cls_pagamenti->isReduced(), $n_ReducedPaymentDayAccepted, $n_PaymentDayAccepted, $f_AmountLimit, $TotalAmount, $TotalAmountPayed);
                    //trigger_error("Ritorno dalla funzione: ".($scaglionePagatoTolleranze == true ? "vero" : "falso")." --> ".($r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']));
                    $b_pagato = ($scaglionePagatoTolleranze != false);
                    }
                //Se il ricorso è accolto è considerato pagato
                else if($cls_pagamenti->getStatus() == 6){
                    $b_pagato = true;
                    }
                
                if(!$b_pagato){
                    // data primo pagamanto e giorni dalla data di notifica a data primo pagamento
                    $DayFromNotificationToFirstPayment = -1;
                    $MinPaymentDate = null;
                    $r_FinePayment = $rs->getArrayLine($rs->SelectQuery("SELECT MIN(PaymentDate) MinPaymentDate FROM FinePayment WHERE FineId=". $r_FineProcedure['Id']));
                    
                    if($r_FinePayment){
                        $MinPaymentDate = $r_FinePayment['MinPaymentDate'] ?: null;
                        if (!empty($MinPaymentDate))
                            $DayFromNotificationToFirstPayment = DateDiff("D", $NotificationDate, $r_FinePayment['MinPaymentDate']);
                    }
                    
                    //Se ho un importo fissato nel ricorso, tutti gli importi corrispondono ad esso, altrimenti a quelli del verbale
                    if($b_hasDisputeFee){
                        $FineFee = $DisputeFee;
                        $FineMaxFee = 0;
                        $FineHalfMaxFee = 0;
                    } else {
                        //Se non c'è ricorso ma l'importo è comunque fissato dalla prefettura, comunque quello sarà l'importo base
                        if($PrefectureFee > 0)
                            {
                            $FineFee = $PrefectureFee;
                            $FineMaxFee = 0;
                            $FineHalfMaxFee = 0;
                            }
                        //Altrimenti è quello preso dagli articoli
                        else{
                            $FineMaxFee = ($FineHalfMaxFee - $FineFee);
                        }
                    }
                    
                    $b_pagato =
                    ($cls_pagamenti->getStatus() == 3 || $cls_pagamenti->getStatus() == 4) ||
                    (($cls_pagamenti->getStatus() == 1 || $cls_pagamenti->getStatus() == 2) && (($TotalAmount - ($TotalAmountPayed + $f_AmountLimit))) < 0.01);
                    $b_omesso = $cls_pagamenti->getStatus() == 0;
                    $b_parzialeNoRit = $cls_pagamenti->getStatus() == 1;    //Parziale non in ritardo
                    $b_parzialeRit = $cls_pagamenti->getStatus() == 2;      //Parziale in ritardo
                    $b_parziale = $b_parzialeNoRit || $b_parzialeRit;       //Parziale senza specificare se in ritardo o meno
                }
                
                //La maggiorazione viene applicata solo se il flag è attivo
                $PercentualAmount = 0;
                if(!$b_dispute || ($b_dispute && $DisputeFee == 0) || ($b_dispute && $DisputeFee > 0 && $ApplyPercentualOnPrefectureFee))
                    $PercentualAmount = $Surcharge;
                    
                //Le spese di notifica dei solleciti precedenti sono condizionate più in alto. Se la spunta non fosse impostata varrebbero zero
                $TotalNotification = $AdditionalFee + $PreviousRemindersNotificationFeeSum;
                $TotalAmount += $ReminderCurrentNotificationFee;
                $TotaleResiduo = ($TotalAmount - $TotalAmountPayed);
                
                if ($b_omesso) { //se il totale pagato è zero è un omesso pagamento
                    $n_CountFineNotPayed++;
                    trigger_error("sollecito caso omesso 2".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'], E_USER_WARNING);
                } else if ($b_parzialeNoRit) {
                    $n_CountFinePartialPayed++;
                    trigger_error("sollecito caso parziale no ritardo 2".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'], E_USER_WARNING);
                } else if ($b_parzialeRit){
                    $n_CountFineLatePayed++;
                    trigger_error("sollecito caso parziale ritardo 2 ".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'], E_USER_WARNING);
                } else if ($b_pagato){
                    $n_CountFineClosed++;
                    trigger_error("sollecito caso pagato 3 (entro scaglione non completamente maturato) ".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'], E_USER_WARNING);
                }
                    
            } else {
                //non son ancora passati i termini per emettere il sollecito e non lo elaboriamo
                $str_Warning .= 'ID '.$FineId.': Termini non ancora trascorsi, non è possibile elaborare il sollecito.<br>';
                continue;
            }
        }
        else {
            //il ricorso è in attesa e non faccio niente
            //ricorso rinviato o accolto da oltre 215 gg ed è stato chiuso o non faccio niente ma qui non ci dovrei arrivare
            $str_Warning .= 'ID '.$FineId.': Ricorso in attesa, non è possibile elaborare il sollecito.<br>';
            $cls_pagamenti->getStatus() == 5 ? $n_CountFineDispute++ : $n_CountFineDisputeClosed++;
            continue;
        }
        
        if ($b_pagato) {
            
            if($ultimate) {
                $a_Fine = array(
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>30,'settype'=>'int'),
                    array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                );
                $rs->Update('Fine',$a_Fine,"Id=".$FineId);
                
                $PaymentProcedure = 0;
                $a_FineNotification = array(
                    array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
                    array('field'=>'PaymentProcedureOffReason','selector'=>'value','type'=>'str','value'=>MOTIVO),
                );
                $rs->Update('FineNotification',$a_FineNotification,"FineId=".$FineId);
            }
            continue;
        }
        
        $rs_VehicleType = $rs->SelectQuery('SELECT Title'.$a_LanKeys[ucfirst($r_Reminder['Language'])].' AS VehicleType FROM VehicleType WHERE Id='.$r_Reminder['VehicleTypeId']);
        $VehicleType = StringOutDB(mysqli_fetch_array($rs_VehicleType)['VehicleType']);
        
        $ViolationTypeId = $r_Reminder['ViolationTypeId'];
        
        $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType1'];
        $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType2'];
        
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        
        
        $RuleTypeId = $r_RuleType['Id'];
        
        $str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
        
        if($Operation == "create")
            $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId." AND FlowDate IS NOT NULL AND SendDate IS NOT NULL");
        elseif($Operation == "update")
            $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId." AND FlowDate IS NOT NULL AND SendDate IS NOT NULL");
        
        $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);
        
        $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];
        
        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;
        
        
        $trespassers = $rs->Select('V_Trespasser', "Id=".$r_Reminder['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);
        
        //$ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
        $ManagerSubject = "Serv. risc. Violazioni al C.D.S.";
        
        $n_LanguageId = $trespasser['LanguageId'];
        
        $TaxCode = trim($r_Reminder['TaxCode']);
        $VatCode = trim($r_Reminder{'VatCode'});
        $TrespasserCode = $TaxCode != null ? $TaxCode : ($VatCode != null ? $VatCode : "");
        
        //Coordinata inizio stampa testo dinamico
        $TextStartCoord = array('X'=>10, 'Y'=>92);
        
        $Percentual_parameter =  $r_Customer['ForeignPercentualReminder'];
        
        //Se provvisorio evidenzia il testo in giallo
        $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
        $pdf->SetTextColor(0, 0, 0);
        
        //se non sono ancora passati 60 giorni dalla notifica non va mostrata la maggiorazione fino alla metà del massimo
        //FIXME non sono state gestite $b_dispute e $DisputeFee
        
        $PercentualAmount = $Surcharge;
        
        if ($b_parziale) {
            $str_ReminderType = $a_Reminder['AfterDay'][$n_LanguageId];
            $str_ReminderType = str_replace("{Amount}",NumberDisplay($TotalAmountPayed), $str_ReminderType);
            $str_ReminderType = str_replace("{PaymentDate}",DateOutDB($MinPaymentDate), $str_ReminderType);
            $str_ReminderType = str_replace("{DayNumber}",$n_Day, $str_ReminderType);
            
        } else if ($b_omesso) {
            
            $str_ReminderType = $a_Reminder['Missed'][$n_LanguageId];
        }
        
        $NoElegibleFine = false;
        
        //             echo "<br /> PrintType";
        //             print_r($PrintType);
        //Se non sono state impostate opzioni di stampa o non è stato impostato "Solo bollettino"
        if ($PrintType != 2){
            
            $page_format = array('Rotate'=>45);
            //$pdf->SetMargins(10,8,10);
            $pdf->AddPage('P');
            
            //Prende il contenuto del testo
            $forms = $rs->Select('FormDynamic',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
            $form = mysqli_fetch_array($forms);
            
            $Content = StringOutDB($form['Content']);
            
            //INTESTAZIONE
            //Se Intestazione SARIDA è abilitata nei parametri ente, scrive l'intestazione di Sarida, altrimenti quella dell'ente
            if ($r_Customer['ForeignReminderHeaderSarida'] == 1){
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
                //$pdf->LN(3);
                //$pdf->writeHTMLCell(100, 0, 15, '', "Gestione: ".$ManagerName, 0, 0, 1, true, 'L', true);
                
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
                        
                        while ($r_variable = mysqli_fetch_array($rs_variable)){
                            $a_Types[$r_variable['Type']] = $r_variable['Content'];
                        }
                        
                        //TODO togliere
                        if ($var == "{{HeaderSarida}}"){
                            $Content = str_replace("{{HeaderSarida}}", '', $Content);
                        }
                        //Sottotesto maggiorazione
                        if ($var == "{{Surcharge}}"){
                            $str_Surcharge = "";
                            if ($Percentual_parameter > 0){
                                $str_Surcharge .= $a_Types[1];
                            }
                            $Content = str_replace("{{Surcharge}}", $str_Surcharge, $Content);
                        }
                        //Sottotesto oggetto
                        if ($var == "{{ReminderObject}}"){
                            $str_ReminderObject = "";
                            if ($TotalAmountPayed>=0.01) {
                                if($n_Day<=FINE_DAY_LIMIT_REDUCTION){
                                    $str_ReminderObject = $a_Types[2];
                                } else $str_ReminderObject = $a_Types[3];
                            } else $str_ReminderObject = $a_Types[1];
                            $Content = str_replace("{{ReminderObject}}", $str_ReminderObject, $Content);
                        }
                        //Sottotesto contenuto
                        if ($var == "{{ReminderContent}}"){
                            $str_ReminderContent = "";
                            if ($TotalAmountPayed>=0.01) {
                                if($n_Day<=FINE_DAY_LIMIT_REDUCTION){
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
                            if ($TotalAmountPayed>=0.01) {
                                if($n_Day<=FINE_DAY_LIMIT_REDUCTION){
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
            $rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentForeignPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$r_Reminder['CityId']."'");
            $PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentForeignPaymentDayReminder'];
            
            $Content = str_replace("{Signer}", StringOutDB($Signer), $Content);
            $Content = str_replace("{Blazon}", '<img src="img/sarida.jpg" width="60" height="70">', $Content); //NOTA: I TAG IMG CON ATTRIBUTO src='' NON FUNZIONANO, USARE src="" (doppie graffe)
            $Content = str_replace("{ManagerName}", $r_Customer['ManagerName'], $Content);
            $Content = str_replace("{ManagerSubject}", $ManagerSubject, $Content);
            $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
            $Content = str_replace("{ReminderOfficeInfo}", StringOutDB($r_Customer['ReminderOfficeInfo']), $Content);
            
            $Content = str_replace("{PaymentDays}", $PaymentDays, $Content);
            $Content = str_replace("{PaymentDate}", $MinPaymentDate, $Content);
            $Content = str_replace("{DaysFromNotificationDate}", $DayFromNotificationToFirstPayment, $Content);
            $Content = str_replace("{DelayDay}", $DayFromNotificationToFirstPayment-FINE_DAY_LIMIT, $Content);
            
            $Content = str_replace("{TrespasserName}", StringOutDB($r_Reminder['CompanyName']) . ' ' . StringOutDB($r_Reminder['Surname']) . ' ' . StringOutDB($r_Reminder['Name']), $Content);
            $Content = str_replace("{TrespasserGenre}", '', $Content);
            $Content = str_replace("{TrespasserCity}", $r_Reminder['City'], $Content);
            $Content = str_replace("{TrespasserProvince}", $r_Reminder['Province'], $Content);
            $Content = str_replace("{TrespasserAddress}", $r_Reminder['TrespasserAddress'], $Content);
            $Content = str_replace("{TrespasserZip}", $r_Reminder['ZIP'], $Content);
            $Content = str_replace("{TrespasserCountry}", $r_Reminder['CountryTitle'], $Content);
            
            $Content = str_replace("{TaxCode}", $TaxCode,$Content);
            
            $Content = str_replace("{FineDate}", DateOutDB($r_Reminder['FineDate']),$Content);
            $Content = str_replace("{FineTime}", TimeOutDB($r_Reminder['FineTime']),$Content);
            $Content = str_replace("{CurrentDate}", $CreationDate, $Content);
            $Content = str_replace("{ReminderType}", $str_ReminderType, $Content);
            $Content = str_replace("{Address}", StringOutDB($r_Reminder['Address']), $Content);
            $Content = str_replace("{VehiclePlate}", StringOutDB($r_Reminder['VehiclePlate']), $Content);
            $Content = str_replace("{VehicleType}", $VehicleType, $Content);
            
            $Content = str_replace("{Code}", $r_Reminder['Code'],$Content);
            $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
            $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);
            
            $Content = str_replace("{Fee}", NumberDisplay($FineFee), $Content);
            
            $Content = str_replace("{PaymentDayReminder}", $PaymentDayReminder, $Content);
            
            $Content = str_replace("{MaxFee}", NumberDisplay($FineMaxFee), $Content);
            
            $Content = str_replace("{HalfMaxFee}", NumberDisplay($FineHalfMaxFee), $Content);
            $Content = str_replace("{TotalNotification}", NumberDisplay($TotalNotification), $Content); //include spese notifica solleciti precedenti se richiesto di sommarle
            $Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content); //Totale spese del sollecito attuale
            $Content = str_replace("{NotificationFeeHistory}", NumberDisplay($NotificationFeeHistory), $Content); //Totale spese solleciti precedenti
            $Content = str_replace("{Amount}", NumberDisplay($TotalAmountPayed), $Content);
            $Content = str_replace("{TotalAmount}", NumberDisplay($TotaleResiduo), $Content); //totale dovuto comprensivo di notifiche vecchie e nuove al netto del pagato
            $Content = str_replace("{Percentual}", NumberDisplay($Percentual_parameter), $Content);
            $Content = str_replace("{PercentualAmount}", NumberDisplay($PercentualAmount), $Content);
            $Content = str_replace("{NotificationFee}", NumberDisplay($ReminderCurrentNotificationFee), $Content);
            $Content = str_replace("{Semesters}", $n_Semesters, $Content);
            
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
            
            $Content = str_replace("{Date}", $CreationDate,$Content);
            
            $Content = str_replace("{ReminderCode}",$str_ReminderCode, $Content);
            
            if(isset($_POST['Operation']) && $_POST['Operation'] === 'create')
                $ReminderId = $r_ReminderMax + 1;
                else
                    $ReminderId = $r_ReminderMax;
                    
                    $Content = str_replace("{ReminderId}",$ReminderId, $Content);
                            
                                //PAGOPA
//                                 if($r_Customer['PagoPAPayment']==1){
//                                     $style = array(
//                                         'border' => 1,
//                                         'vpadding' => 'auto',
//                                         'hpadding' => 'auto',
//                                         'fgcolor' => array(0,0,0),
//                                         'bgcolor' => false, //array(255,255,255)
//                                         'module_width' => 1, // width of a single module in points
//                                         'module_height' => 1 // height of a single module in points
//                                     );
                                    
//                                     if ($r_ArticleTariff['ReducedPayment']) {
//                                         $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
//                                         $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
                                        
//                                     } else {
//                                         $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
//                                         $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
//                                     }
                                    
//                                     //$url_PagoPAPage = "https://nodopagamenti-test.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";
//                                     $url_PagoPAPage = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";
                                    
//                                     if($r_Reminder['PagoPA1']!='' && $r_Reminder['PagoPA2']!=''){
//                                         $pdf->write2DBarcode($url_PagoPAPage.$r_Reminder['PagoPA1'], 'QRCODE,H', 60, 240, 30, 30, $style, 'N');
//                                         $pdf->writeHTMLCell(60, 0, 45, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                                        
                                        
//                                         $pdf->write2DBarcode($url_PagoPAPage.$r_Reminder['PagoPA2'], 'QRCODE,H', 120, 240, 30, 30, $style, 'N');
//                                         $pdf->writeHTMLCell(80, 0, 100, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
//                                     }
//                                 }
                                //FINE PAGOPA
            }
            
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
            $a_DocumentationFineZip[] = $Documentation;
            $a_ReminderId[] = $r_Reminder['Id'];
            
            $pdf->SetFont($font, '', 8);
            
            //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
            $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
            $TCPDF_pagebreak = '<br pagebreak="true" />';
            preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
            //
            
            $pdf->SetAutoPageBreak(true, 0);
            $pdf->SetPrintHeader(false);
           
            $pdf->writeHTML($Content, true, true, true, false, '');
            
            //Conta le pagine attuali, se sono dispari aggiunge una pagina bianca in fondo
            $PageNo= $pdf->PageNo();
            if($PageNo%2 == 1){
                $pdf->AddPage('P', $page_format);
            }
            
            $n_ReminderCount++;
            
            //Output documento singolo
            $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$Documentation, "F");
            
            //Concatenazione documenti
            $n_PageCount = $pdf_union->setSourceFile(FOREIGN_FINE."/".$_SESSION['cityid']."/".$Documentation);
            $startImport=1;
            $endImport=$n_PageCount;
            for($p=$startImport;$p<=$endImport;$p++){
                $tmp_Page = $pdf_union->ImportPage($p);
                $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                $str_Format = ($tmp_Size['w']>$tmp_Size['h']) ? 'L' : 'P';
                $pdf_union->AddPage($str_Format, array($tmp_Size['w'],$tmp_Size['h']),false);
                $pdf_union->useTemplate($tmp_Page);
            }
        //
        
        if ($ultimate){
            
            $a_Insert = array(
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Reminder['TrespasserId'],'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_Reminder['TrespasserTypeId'],'settype'=>'int'),
                array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>(float)$ReminderCurrentNotificationFee,'settype'=>'flt'), //spese di invio sollecito + eventuali solleciti precedenti
                array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>null,'settype'=>'date'),
                array('field'=>'FlowNumber','selector'=>'value','type'=>'int','value'=>(int)null),
                array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>DateInDB($CreationDate),'settype'=>'date'),
                
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                
                array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>(int)$PaymentDays,'settype'=>'int'), //DataPaymentForeignPaymentDayReminder
                array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$MinPaymentDate,'settype'=>'date'), //data del primo pagamento
                array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>(int)$DayFromNotificationToFirstPayment,'settype'=>'int'), //giorni dalla notifica all'elaborazione pagamento
                array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>(int)($DayFromNotificationToFirstPayment-FINE_DAY_LIMIT),'settype'=>'int'), //giorni dalla notifica all'elaborazione pagamento - gg limite
                array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>(int)$n_Semesters,'settype'=>'int'), //numero semestri maturati
                array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>(float)$FineFee,'settype'=>'flt'), //$FineFee è il minimo edittale dopo 60 gg o il 70% del min edittale
                array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>(float)($FineMaxFee),'settype'=>'flt'), //Max edittale
                array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>(float)$FineHalfMaxFee,'settype'=>'flt'), //metà del max edittale
                array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>(float)$TotalNotification,'settype'=>'flt'), //totale spese notifica verbale + ricreca + notifica solleciti precedenti (NON METTEREI I SOLLECITI PRECEDENTI QUI)
                array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>(float)$TotalAmountPayed,'settype'=>'flt'), //totale pagato
                array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>(float)($TotalAmount),'settype'=>'flt'), //totale delle sanzioni dovute al lordo del pagato
                array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>(float)$r_Customer['ForeignPercentualReminder'],'settype'=>'flt'), //percentuale maggiorazione semestrale
                array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>(float)$PercentualAmount,'settype'=>'flt'), //totale maggiorazione semestrale
                array('field'=>'ControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$n_ControllerId)//Firmatario
            );
            
            //Se Operation è valorizzato (frm_senddynamic_reminder)
            if(isset($_POST['Operation'])){
                If($_POST['Operation'] === "update"){
                    $rs_FineReminderH = $rs->SelectQuery("SELECT * FROM FineReminder WHERE Id=(SELECT MAX(Id) FROM FineReminder WHERE FineId=$FineId AND CityId='".$_SESSION['cityid']."')");
                    $r_FineReminderH = mysqli_fetch_array($rs_FineReminderH);
                    
                    $a_InsertHistory = array(
                        array('field'=>'FineReminderId','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['Id'],'settype'=>'int'),
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['TrespasserId'],'settype'=>'int'),
                        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['TrespasserTypeId'],'settype'=>'int'),
                        array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['NotificationFee'],'settype'=>'flt'),
                        array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$r_FineReminderH['PrintDate'],'settype'=>'date'),
                        array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$r_FineReminderH['SendDate'],'settype'=>'date'),
                        
                        array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>$r_FineReminderH['FlowDate'],'settype'=>'date'),
                        array('field'=>'FlowNumber','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['FlowNumber'],'settype'=>'int'),
                        
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$r_FineReminderH['Documentation']),
                        
                        array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['PaymentDays'],'settype'=>'int'),
                        array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$r_FineReminderH['PaymentDate'],'settype'=>'date'),
                        array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['DaysFromNotificationDate'],'settype'=>'int'),
                        array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['DelayDays'],'settype'=>'int'),
                        array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['Semester'],'settype'=>'int'),
                        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['Fee'],'settype'=>'flt'),
                        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['MaxFee'],'settype'=>'flt'),
                        array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['HalfMaxFee'],'settype'=>'flt'),
                        array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['TotalNotification'],'settype'=>'flt'),
                        array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['Amount'],'settype'=>'flt'),
                        array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['TotalAmount'],'settype'=>'flt'),
                        array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['Percentual'],'settype'=>'flt'),
                        array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>$r_FineReminderH['PercentualAmount'],'settype'=>'flt'),
                    );
                    
                    $rs->Insert('FineReminderHistory',$a_InsertHistory);
                    $rs->Update('FineReminder',$a_Insert,"Id=".$r_FineReminderH['Id']);
                }
                        
                if($_POST['Operation'] === "create"){
                    
                    $a_Insert[] = array('field'=>'Id','selector'=>'value','type'=>'int','value'=>$ReminderId,'settype'=>'int');
                    $rs->Insert('FineReminder',$a_Insert);
                }
                
                $a_Update = array(
                    array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                );
                $rs->Update('Fine',$a_Update, 'Id='.$FineId);
                
                
                $a_Insert = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>30),
                    array('field'=>'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                if($_POST['Operation'] === "create"){
                    $rs->Insert('FineDocumentation',$a_Insert);
                } else if($_POST['Operation'] === "update"){
                    $rs->Update('FineDocumentation', $a_Insert, "FineId=$FineId AND DocumentationTypeId=30 AND Documentation='{$r_FineReminderH['Documentation']}'");
                }
                
            }
                    
            $FileName = $Documentation;
                    
        } else $FileName = 'export_createdynamic_reminder_f.pdf';
        
        $r_ReminderMax++;
        
        if ($ultimate){
            if ($b_omesso ) {
                
                $a_Fine = array(
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>27,'settype'=>'int'),
                    array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                    array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                );
                $rs->Update('Fine',$a_Fine,"Id=".$FineId);
                
            } else if ($b_parziale) {
                
                $a_Fine = array(
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>28,'settype'=>'int'),
                    array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                    array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                );
                $rs->Update('Fine',$a_Fine,"Id=".$FineId);
            }
            
        } //fine ultimate per aggiornare gli stati
    }// fine ciclo
    
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
            //
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

if($ultimate){
    $message = "
                <div>Azione eseguita con successo.</div>
                <div style='border:1px solid #B1D9C4; border-radius: 1rem; padding: 1rem;'>
                    <span><b style='color:red;'>Omessi: </b> $n_CountFineNotPayed  </span>
                    <span><b style='color:#DBC925;'>Parziali: </b> ".($n_CountFinePartialPayed+$n_CountFineLatePayed)." ($n_CountFineLatePayed tardivi)  </span>
                    <span><b style='color:#DBC925;'>Sospesi: </b> ".($n_CountFineDispute+$n_CountFineDisputeClosed+$n_CountFinePrescription)."</span>
                    <span><b style='color:green;'>Chiusi: </b> $n_CountFineClosed </span>
                </div>
               ";
    if ($str_Warning != ''){
        $message .= '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
    }
    if($n_CountFineNotPayed+$n_CountFinePartialPayed+$n_CountFineLatePayed+$n_CountFineClosed == 0 && $str_Warning != '')
        $_SESSION['Message']['Warning'] = $message;
    else
        $_SESSION['Message']['Success'] = $message;
}

//$rs->UnlockTables();
$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);
$rs->Update('LockedPage',$aUpdate, "Title='".CREATE_REMINDER_LOCKED_PAGE."_{$_SESSION['cityid']}'");
$rs->End_Transaction();

