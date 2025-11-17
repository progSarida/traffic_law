<?php
require_once(CLS."/cls_pagamenti.php");
require_once(CLS.'/cls_literal_number.php');
require_once(CLS."/cls_dispute.php");
require_once(CLS . '/cls_iuv.php');
require_once(CLS."/avvisiPagoPA/ModelloBase.php");
require_once(CLS."/cls_view.php");

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

$n_LanguageId = 1;
$FormTypeId = 30;
$str_Error = '';
//$a_Cities = unserialize(REMINDER_NATIONAL_CITIES);

//leggo data elaborazione
$ProcessingDate = CheckValue('ProcessingDate','s');
$ProcessingDate = DateInDB($ProcessingDate);
//echo "<br >data elaborazione";
$CreationDate = CheckValue('CreationDate','s');
$n_ControllerId = CheckValue('ControllerId','s');

//RECUPERO PARAMETRI PER CREAZIONE SOLLECITO
//$FinePDFList = $r_Customer['FinePDFList'];


//PARAMETRI DELL'ENTE
$rs_Customer = $rs->Select("V_Customer", "CityId = '".$_SESSION['cityid']."' AND CreationType = 1 "
    . "AND ((FromDate <= '".$ProcessingDate."' AND ToDate >= '".$ProcessingDate."') OR (COALESCE(FromDate, '0001-01-01') <= '".$ProcessingDate."' AND ToDate IS NULL))", "FromDate DESC", 1);
$r_Customer  = mysqli_fetch_array($rs_Customer);

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrintDestinationFold AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_FoldReturn = $r_PrintParameter['NationalReminderFoldReturn'];
$str_PostalAuthorization = trim($r_PrintParameter['NationalReminderPostalAuthorization'] ?? '');
$str_PostalAuthorizationPagoPA = trim($r_PrintParameter['NationalReminderPostalAuthorizationPagoPA']) ?? '';
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

//SELEZIONE PARAMETRI PROCEDURA DI PAGAMENTO
$rs_ProcessingData = $rs->Select('ProcessingDataPaymentNational', "CityId='".$_SESSION['cityid']."'");
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


$rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentNationalPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$CityId."'");
$PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentNationalPaymentDayReminder'];

$ultimate = CheckValue('ultimate','n');
$NoElegibleFine = true;

$str_Warning = '';
$a_DocumentationFineZip = array();
//Contiene i FineId di cui non è stato possibile generare/aggiornare PagoPA
$a_FailedPagoPA = array();
//$a_InvalidReminders = array();


$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

//$CurrentDate = date("Y-m-d");
$n_ReminderCount = 0;

$a_Lan = unserialize(LANGUAGE);

$rs->Start_Transaction();

if(isset($_POST['checkbox'])) {
    
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array('helvetica', '', 8));
    $pdf_union->setFooterFont(array('helvetica', '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);

    //TODO Qui venivano recuperate le informazioni su last reminder
    
    //Firmatario
    $rs_Signer = $rs->Select('Controller', "Id=".$n_ControllerId);
    $r_Signer = mysqli_fetch_array($rs_Signer);
    $Signer = (isset($r_Signer['Qualification']) ? $r_Signer['Qualification'].' ' : '').$r_Signer['Name'];

    //*************************INIZIO CICLO VERBALI**************************
    
    $n_CountFineNotPayed = 0; //Verbali non pagati (vanno in stato 27)
    $n_CountFinePartialPayed = 0; //Verbali pagati parzialmente (vanno in stato 28)
    $n_CountFineLatePayed = 0; //Verbali pagati parzialmente in ritardo (vanno in stato 28)
    $n_CountFineDispute = 0; //Verbali con ricorso aperto (vengono messi in lista ma saltati dall'elaborazione)
    $n_CountFineDisputeClosed = 0; //Verbali con ricorso chiuso accolto (vengono saltati dall'elaborazione finchè non passano i termini poi vanno in stato 27)
    $n_CountFinePrescription = 0; //Verbali prescritti (vengono messi in lista ma saltati dall'elaborazione)
    $n_CountFineClosed = 0; //Verbali pagati (vanno in stato 30)
    
    foreach(array_unique($_POST['checkbox']) as $FineId) {
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
                FH.NotificationFee,
                FH.ResearchFee,
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

                VT.TitleIta VehicleType,
                CO.Title CountryTitle,

                FR.FineId AS ReminderFineId,
                FR.PrintDate
              
                FROM Fine F 
                JOIN FineHistory FH ON F.Id=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN ArticleTariff TAR ON TAR.ArticleId=FA.ArticleId and TAR.Year = F.ProtocolYear
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN sarida.City C on C.Id = F.Locality
                JOIN sarida.Country CO ON CO.Id=T.CountryId
                JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
                LEFT JOIN FineReminder FR ON F.Id = FR.FineId
                WHERE F.Id=".$FineId
        );
        
        $r_Reminder = $rs->getArrayLine($rs_Reminder);
        
        $a_DocumentationFineZip[$FineId] = array();
        
        $ViolationTypeId = $r_Reminder['ViolationTypeId'];
        $TrespasserName = StringOutDB((isset($r_Reminder['CompanyName']) ? $r_Reminder['CompanyName'].' ' : '') . $r_Reminder['Surname'] . ' ' . $r_Reminder['Name']);
        
        $rs_Trespasser = $rs->Select("Trespasser", "Id={$r_Reminder['TrespasserId']}");
        $r_Trespasser = $rs->getArrayLine($rs_Trespasser);
        
        //Recupera tutti i dati dell'ultimo sollecito disponibile
        $rs_FineReminderH = $rs->SelectQuery("SELECT * FROM FineReminder WHERE Id=(SELECT MAX(Id) FROM FineReminder WHERE FineId=$FineId AND CityId='".$_SESSION['cityid']."')");
        $r_FineReminderH = $rs->getArrayLine($rs_FineReminderH);
        
        
        //VARIABILI PER SANZIONI
        $AdditionalFee = $cls_pagamenti->getAdditionalFee(); //spese addizionali prese da FineHistory
        
        $b_hasDisputeFee = $cls_pagamenti->getDisputeAmount() > 0;
        $DisputeFee = $cls_pagamenti->getDisputeAmount();    //sanzione amministrativa fissata da autorità giudiziaria
        $PrefectureFee = 0; //sanzioni fissate da prefettura per determinati articoli
        
        $FineFee = $cls_pagamenti->getFineFee();           //sanzione amministrativa complessiva per tutti gli articoli
        $FineHalfMaxFee = $cls_pagamenti->getFineMaxFee();    //Metà del totale massimo edittale
        $FineMaxFee = 0;        //importo da aggiungere al normale per arrivare alla metà del massimo
        
        
        $LastReminderNotificationFee = $cls_pagamenti->getLastReminderNotificationFee();
        $LastReminderTotalNotification = $cls_pagamenti->getLastReminderTotalNotificationFee();
        $NotificationFeeHistory = $LastReminderNotificationFee+$LastReminderTotalNotification; //spese di invio solleciti precedenti
        $ReminderCurrentNotificationFee = $r_Customer['IncreaseNationalNotificationFee'] > 0 ? $cls_pagamenti->getCurrentCustomerReminderNotificationFee() : 0;
        $n_Semesters = $cls_pagamenti->getSemester();
        $NotificationDate = $cls_pagamenti->getNotificationDate();
        $PreviousRemindersNotificationFeeSum = $cls_pagamenti->getPreviousReminderNotificationFeesSum() ?? 0;
        
        //esiti
        $b_pagato = $b_parziale = $b_parzialeNoRit = $b_parzialeRit = $b_omesso = false;
        
        //esame ricorsi
        $b_dispute = $cls_pagamenti->getHasDispute(); // indica che c'era un ricorso
        //indica se c'è un ricorso accettato da non elaborare
        //$b_FineDispute = false;
        //durata sospensiva ricorso (TODO DA SISTEMARE)
        //$n_DisputeDay = 0;
        //NOTA: ELIMINARE
        //esame ricorsi
        
        $b_disputeBlock = ($cls_pagamenti->getStatus() == 5 || $cls_pagamenti->getStatus() == 6);
        
        //se non c'è ricorso o è stato respinto
        if(!$b_disputeBlock){
            
            //calcolo dei giorni trascorsi dalla notifica per decidere se va emesso sollecito
            //giorni dalla notifica
            $n_Day = DateDiff("D", $NotificationDate, $ProcessingDate)+1;
            
            //giorni dalla notifica oltre la scadenza minima di 60 gg
            $n_CalcDay = $n_Day - (FINE_DAY_LIMIT + $WaitDay);
            
            //data che determina se il sollecito è elaborabile perchè passati i termini necessari
            $ElaborationStartingDate = date('Y-m-d', strtotime($NotificationDate. ' + '.(FINE_DAY_LIMIT + $WaitDay).' days'));
            
            //TODO Sistemare la questione dei giorni aggiuntivi del ricorso
            
            //giorni oltre la scadenza massima + giorni durata ricorso
//             if($b_dispute) {
//                 //se c'è la sospensiva accordata viene considerata
//                 if($r_FineDispute['FineSuspension'] == 1 && $r_FineDispute['DateMerit']!=NULL)
//                     $n_DisputeDay = DateDiff("D", $r_FineDispute['DateFile'], $r_FineDispute['DateMerit'])+1;
                    
//                 if ($n_NumFineDispute>1) {
//                     //se ci sono altri ricordi precedenti sommiamo le loro sospensive
//                     while($r_FineDispute_i =mysqli_fetch_array($rs_FineDispute)){
//                         if($r_FineDispute_i['FineSuspension'] == 1)
//                             $n_DisputeDay += DateDiff("D", $r_FineDispute_i['DateFile'], $r_FineDispute_i['DateMerit'])+1;
//                     }
//                 }
                    
// //                     echo "<br> giorni sospensione N ricorso <br>";
// //                     print_r($n_DisputeDay);
//                 $RangeDayMax += $n_DisputeDay;
//                 //se c'è il ricorso i giorni dopo cui può essere elaborato il sollecito sono
//                 // i giorni trascorsi dalla notifica + 180 gg + giorni di sospsensiva
//                 $n_CalcDay = $n_Day - $RangeDayMax;
//             }
         
            //VALUTAZIONE PRESCRIZIONE
            //Se la data di notifica avanti di 5 anni (5 anni + 270 gg per esterno) + i giorni di ricorso
            // + shift per festività è < $ProcessingDate siamo in prescrizione
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
                
                //TODO Questa si può ricavare comunque dalla cls_pagamenti dalla cronologia pagamenti
                $DayFromNotificationToFirstPayment = -1;
                if (!empty($MinPaymentDate))
                    $DayFromNotificationToFirstPayment = DateDiff("D", $NotificationDate, $r_FinePayment['MinPaymentDate']);
                
                //echo date('Y-m-d', strtotime($NotificationDate. ' + '. "". FINE_DAY_LIMIT . " days ")) . " data scadenza pagamento verbale"."<br/>";
                                    

                
                //************INIZIO INTRODUZIONE BLOCCO CALCOLO DA LOGICA ELABORAZIONE SOLLECITI***********
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
                    //trigger_error("Verbale ".$r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']." prz. rit: ".($b_parzialeRit == true ? "vero" : "falso")." prz. NO RIT: ".($b_parzialeNoRit == true ? "vero" : "falso")." prz. generico: ".($b_parziale == true ? "vero" : "falso"));
                }
                //************FINE INTRODUZIONE BLOCCO CALCOLO DA LOGICA ELABORAZIONE SOLLECITI***********
                
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
            }
            else {
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
        
        $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType1'];
        $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType2'];
        
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        
        $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Reminder['ArticleId'] . " AND Year=" . $r_Reminder['ProtocolYear']);
        $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

        $RuleTypeId = $r_RuleType['Id'];
    
        $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
        
        //Conteggia il numero di solleciti in base al tipo di operazione desiderata
        //TODO update non sta ritornando il numero corretto, verificare
        if($Operation == "create")
            $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId." AND FlowDate IS NOT NULL AND SendDate IS NOT NULL");
        elseif($Operation == "update")
            $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId." AND SendDate IS NOT NULL");
        
        $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);

        $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];
        

        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;   

        $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
        $TaxCode = trim($r_Reminder['TaxCode']);
        $VatCode = trim($r_Reminder{'VatCode'});
        $TrespasserCode = $TaxCode != null ? $TaxCode : ($VatCode != null ? $VatCode : "");
        
        //Coordinata inizio stampa testo dinamico
        $TextStartCoord = array('X'=>10, 'Y'=>92);
        
        $Percentual_parameter =  $r_Customer['NationalPercentualReminder'];
        
        //Se provvisorio evidenzia il testo in giallo
        $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
        $pdf->SetTextColor(0, 0, 0);
        
        //La maggiorazione viene applicata solo se il flag è attivo
        $PercentualAmount = 0;
        if(!$b_dispute || ($b_dispute && $DisputeFee == 0) || ($b_dispute && $DisputeFee > 0 && $ApplyPercentualOnPrefectureFee))
            $PercentualAmount = $Surcharge;
        
        //Le spese di notifica dei solleciti precedenti sono condizionate più in alto. Se la spunta non fosse impostata varrebbero zero
        $TotalNotification = $AdditionalFee + $PreviousRemindersNotificationFeeSum;
        $TotalAmount += $ReminderCurrentNotificationFee;
        $TotaleResiduo = ($TotalAmount - $TotalAmountPayed);
        
        $NoElegibleFine = false;
        
        //Se non sono state impostate opzioni di stampa o non è stato impostato "Solo bollettino"
        if ($PrintType != 2){
            $pdf->RightHeader = false;
            $page_format = array('Rotate'=>45);
            $pdf->SetMargins(10,8,10);
            $pdf->AddPage('P', $page_format);
            
            //Prende il contenuto del testo
            $forms = $rs->Select('FormDynamic',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
            $form = mysqli_fetch_array($forms);
            
            $Content = StringOutDB($form['Content']);
            
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
                            if ($TotalAmount>=0.01) {
                                if($DayFromNotificationToFirstPayment<=FINE_DAY_LIMIT){
                                    $str_ReminderObject = $a_Types[2];
                                } else $str_ReminderObject = $a_Types[3];
                            } else $str_ReminderObject = $a_Types[1];
                            $Content = str_replace("{{ReminderObject}}", $str_ReminderObject, $Content);
                        }
                        //Sottotesto contenuto
                        if ($var == "{{ReminderContent}}"){
                            $str_ReminderContent = "";
                            if ($TotalAmount>=0.01) {
                                if($DayFromNotificationToFirstPayment<=FINE_DAY_LIMIT){
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
                            if ($TotalAmount>=0.01) {
                                if($DayFromNotificationToFirstPayment<=FINE_DAY_LIMIT){
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
            $rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentNationalPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$r_Reminder['CityId']."'");
            $PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentNationalPaymentDayReminder'];
            
            $Content = str_replace("{Signer}", StringOutDB($Signer), $Content);
            $Content = str_replace("{Blazon}", '<img src="img/sarida.jpg" width="60" height="70">', $Content); //NOTA: I TAG IMG CON ATTRIBUTO src='' NON FUNZIONANO, USARE src="" (doppie graffe)
            $Content = str_replace("{ManagerName}", $r_Customer['ManagerName'], $Content);
            $Content = str_replace("{ManagerSubject}", $ManagerSubject, $Content);
            $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
            $Content = str_replace("{ReminderOfficeInfo}", StringOutDB($r_Customer['ReminderOfficeInfo']), $Content);
            
            $Content = str_replace("{PaymentDays}", $PaymentDays, $Content);
            $Content = str_replace("{PaymentDate}", $MinPaymentDate, $Content);
            $Content = str_replace("{DaysFromNotificationDate}", $DayFromNotificationToFirstPayment, $Content); //giorni dalla notifica all'elaborazione pagamento 
            $Content = str_replace("{DelayDay}", $DayFromNotificationToFirstPayment-FINE_DAY_LIMIT, $Content); //giorni dalla notifica all'elaborazione pagamento - gg limite
            
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
            //$Content = str_replace("{ReminderType}", $str_ReminderType, $Content);
            $Content = str_replace("{Address}", StringOutDB($r_Reminder['Address']), $Content);
            $Content = str_replace("{VehiclePlate}", StringOutDB($r_Reminder['VehiclePlate']), $Content);
            $Content = str_replace("{VehicleType}", StringOutDB($r_Reminder['VehicleType']), $Content);
            
            $Content = str_replace("{Code}", $r_Reminder['Code'],$Content);
            $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
            $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);
            
            $Content = str_replace("{Fee}", NumberDisplay($FineFee), $Content);
            
            $Content = str_replace("{PaymentDayReminder}", $PaymentDayReminder, $Content);
            
            $Content = str_replace("{MaxFee}", NumberDisplay($FineMaxFee), $Content);
            
            //trigger_error("Diff da versare: ".$TotaleResiduo." AdditionalFee: ".$AdditionalFee." Maggiorazione: ".$PercentualAmount);
            //trigger_error("Voci: ".$FineHalfMaxFee." -> ".$PreviousRemindersTotalNotification." -> ".$AdditionalFee." -> ".$NotificationFeeHistory." -> ".$TotalAmountPayed." -> ".$TotaleResiduo." -> ".$Percentual_parameter." -> ".$PercentualAmount." -> ".$ReminderCurrentNotificationFee);
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
            
            if(isset($_POST['Operation']) && $_POST['Operation'] == 'create')
                $ReminderId = $r_ReminderMax+1;
            else
                $ReminderId = $r_ReminderMax;
            
            $Content = str_replace("{ReminderId}",$ReminderId, $Content);
            
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
            
            $pdf->SetFont('helvetica', '', 8);
            
            //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
            $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
            $TCPDF_pagebreak = '<br pagebreak="true" />';
            preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
            //
            
            $pdf->SetAutoPageBreak(true, 0);
            $pdf->SetPrintHeader(false);
            
            $pdf->writeHTML($Content, true, true, true, false, '');
            
            //PAGOPA
            $PagoPACode1 = $r_Reminder['PagoPA1'];
            $PagoPACode2 = $r_Reminder['PagoPA2'];
            if($b_PagoPAEnabled){
                //FIXME trovare una soluzione per fare in modo di dividere gli importi per ogni articolo
                //dentro a getFineFees quando si gestiranno più capitoli di bilancio e creare la struttura dati secondo calcolaImporti
                $a_Importi = array(
                    'Amounts' => array(
                        array(
                            'Total'=>number_format(((float)$TotaleResiduo), 2, '.', ''),
                            'ViolationTypeId' => $ViolationTypeId
                        )
                    ),
                    'Sum' => array(
                        'Total'=>number_format(((float)$TotaleResiduo), 2, '.', ''),
                    )
                );
                
                $cls_iuv = new cls_iuv();
                $GenreParemeter = ($r_Reminder['Genre'] == "D")? "D" : "P";
                $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
                $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
                $TrespasserType = ($r_Reminder['Genre'] == "D") ? "G" : "F";
                $FineText = 'Anno ' . $r_Reminder['ProtocolYear'] . ' targa ' . $r_Reminder['VehiclePlate'];
                $IUV = null;
                
                $a_FineUpd = array(
                    array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt'),
                    array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt'),
                    array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt'),
                    array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt'),
                );
                
                if ($ultimate){
                    if(empty($PagoPACode1)){
                        if(empty($PagoPACode2)){
                            $a_IUV = callFullPagoPA($pagopaService, $a_Importi, 'Total', null, $r_Customer, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams);
                            if(isset($a_IUV[1])){
                                $IUV = $a_IUV[1];
                                $a_FineUpd[] = array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                                $a_FineUpd[] = array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                            } else $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Generazione PagoPA fallita, il sollecito non è stato elaborato';
                        } else {
                            $a_IUV = updateFullPagoPA($pagopaService, $a_Importi, 'Total', null, null, $PagoPACode2, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio']);
                            if(isset($a_IUV[1])){
                                $IUV = $PagoPACode2;
                                $a_FineUpd[] = array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                            } else $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito, il sollecito non è stato elaborato.';
                        }
                    } else if(empty($PagoPACode2)){
                        if(empty($PagoPACode1)){
                            $a_IUV = callFullPagoPA($pagopaService, $a_Importi, 'Total', null, $r_Customer, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams);
                            if(isset($a_IUV[1])){
                                $IUV = $a_IUV[1];
                                $a_FineUpd[] = array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                                $a_FineUpd[] = array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                            } else $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Generazione PagoPA fallita, il sollecito non è stato elaborato';
                        } else {
                            $a_IUV = updateFullPagoPA($pagopaService, $a_Importi, null, 'Total', $PagoPACode1, null, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio']);
                            if(isset($a_IUV[0])){
                                $IUV = $PagoPACode1;
                                $a_FineUpd[] = array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $IUV);
                            } else $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito, il sollecito non è stato elaborato.';
                        }
                    } else {
                        $a_IUV = updateFullPagoPA($pagopaService, $a_Importi, 'Total', 'Total', $PagoPACode1, $PagoPACode2, $FineId, $r_Reminder['FineDate'], $TrespasserType, $r_Trespasser, $TaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio']);
                        if(isset($a_IUV[0]) && isset($a_IUV[1])){
                            $IUV = $PagoPACode2;
                        } else $a_FailedPagoPA[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito, il sollecito non è stato elaborato.';
                    }
                    
                    if(!isset($a_FailedPagoPA[$FineId])) {
                        $a_FinePagoPAHistory = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1),
                            array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2),
                            array('field'=>'ReminderId','selector'=>'value','type'=>'int','value'=>$r_FineReminderH['Id'],'settype'=>'int'),
                            array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPAReducedPartial'] ,'settype'=>'flt'),
                            array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPAPartial'] ,'settype'=>'flt'),
                            array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPAReducedTotal'] ,'settype'=>'flt'),
                            array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Reminder['PagoPATotal'] ,'settype'=>'flt'),
                        );
                        
                        $rs->Insert("FinePagoPAHistory", $a_FinePagoPAHistory);
                        $rs->Update('Fine', $a_FineUpd, "Id=$FineId");
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
                    
                    $Content = str_replace("{PagoPA1}", $IUV);
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
                
                
        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        ////
        ////
        ////    BILL
        ////
        ////
        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
                
                
        //Se non sono state impostate opzioni di stampa o non è stato impostato "Senza bollettino"
        if ($b_PrintBill && $PrintType != 3){
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
            //
            
            $a_Address = array();
            $a_Address['Riga1'] = $r_Reminder['TrespasserAddress'];
            $a_Address['Riga2'] = '';
            $a_Address['Riga3'] = $r_Reminder['ZIP'];
            $a_Address['Riga4'] = $r_Reminder['City']. ' '."(".$r_Reminder['Province'].')';
            
            $NW = new CLS_LITERAL_NUMBER();
            $numeroLetterale = $NW->converti_numero_bollettino($TotaleResiduo);
            $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'],$str_FifthField,number_format((float)($TotaleResiduo), 2, ',', ''),'si',$r_Customer['NationalReminderBankAccount']);
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
                $o_Importo = new Importo($TotaleResiduo, $PagoPAPaymentNotice);
                
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
            $PagoPACodeFull = AvvisoBase::buildQRCode($PagoPAPaymentNotice, trim($r_Customer['ManagerTaxCode']), $TotaleResiduo);
            
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
        
        //Output documento singolo
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
                trigger_error("<OPERAZIONISOLLECITI> ATTENZIONE -> Errore nell\'unione del pdf dell'avviso di pagamento avviso_$Documentation: $e",E_USER_WARNING);
            }
            
            $pdf_unionavviso->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation , "F");
            
            unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . 'avviso_'.$Documentation);
        }
        
        
        if(!isset($a_FailedPagoPA[$FineId])){
            //Concatenazione documenti
            $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE."/".$_SESSION['cityid']."/".$Documentation);
            $startImport=1;
            $endImport=$n_PageCount;
            for($p=$startImport;$p<=$endImport;$p++){
                $tmp_Page = $pdf_union->ImportPage($p);
                $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                $str_Format = ($tmp_Size['w']>$tmp_Size['h']) ? 'L' : 'P';
                $pdf_union->AddPage($str_Format, array($tmp_Size['w'],$tmp_Size['h']),false);
                $pdf_union->useTemplate($tmp_Page);
            }
            //trigger_error("***MAX FEE: ".$FineHalfMaxFee." - ".$FineFee);
            
            if ($ultimate){
                $a_Insert = array(
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['TrespasserId'],'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['TrespasserTypeId'],'settype'=>'int'),
                    array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>(float)$ReminderCurrentNotificationFee,'settype'=>'flt'), //spese di invio sollecito + eventuali solleciti precedenti
                    array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                    array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>null,'settype'=>'date'),
                    array('field'=>'FlowNumber','selector'=>'value','type'=>'int','value'=>(int)null),
                    array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>DateInDB($CreationDate),'settype'=>'date'),
                    
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                    
                    array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>(int)$PaymentDays,'settype'=>'int'), //DataPaymentNationalPaymentDayReminder
                    array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$MinPaymentDate,'settype'=>'date'), //data del primo pagamento
                    array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>(int)$DayFromNotificationToFirstPayment,'settype'=>'int'), //giorni dalla notifica al primo pagamento
                    array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>(int)($DayFromNotificationToFirstPayment-FINE_DAY_LIMIT),'settype'=>'int'), //giorni dalla notifica al primo pagamento  - gg limite
                    array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>(int)$n_Semesters,'settype'=>'int'), //numero semestri maturati
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>(float)$FineFee,'settype'=>'flt'), //$FineFee è il minimo edittale dopo 60 gg o il 70% del min edittale
                    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>(float)($FineMaxFee),'settype'=>'flt'), //Max edittale
                    array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>(float)$FineHalfMaxFee,'settype'=>'flt'), //metà del max edittale
                    array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>(float)$TotalNotification,'settype'=>'flt'), //totale spese notifica verbale + ricreca + notifica solleciti precedenti (NON METTEREI I SOLLECITI PRECEDENTI QUI)
                    array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>(float)$TotalAmountPayed,'settype'=>'flt'), //totale pagato
                    array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>(float)($TotalAmount),'settype'=>'flt'), //totale delle sanzioni dovute al lordo del pagato
                    array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>(float)$r_Customer['NationalPercentualReminder'],'settype'=>'flt'), //percentuale maggiorazione semestrale
                    array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>(float)$PercentualAmount,'settype'=>'flt'), //totale maggiorazione semestrale
                    array('field'=>'ControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$n_ControllerId)//Firmatario
                );
                
                //Se Operation è valorizzato (frm_senddynamic_reminder)
                //                     echo "<br > operazione";
                //                     print_r($_POST['Operation']);
                
                if(isset($_POST['Operation'])){
                    If($_POST['Operation'] === "update"){
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
            }
            
            if ($ultimate){
                if ($b_omesso) {
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
            
            $r_ReminderMax++;
        } else {
            foreach($a_DocumentationFineZip[$FineId] as $Doc){
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
            }
        }
        
        $n_ReminderCount++;
    }// fine ciclo
        
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
        } else {
            $FileName = 'export_createdynamic_reminder_n.pdf';

            //Rimuove pdf temporanei fatti per creare anteprima di stampa
            foreach ($a_DocumentationFineZip as $DocFineId => $Doc){
                if(!isset($a_FailedPagoPA[$DocFineId])){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc);
                    }
                }
            
            applyTemporaryLabel($pdf_union);
            
            $pdf_union->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
        }
    }
    
}

foreach($a_FailedPagoPA as $pagopaErrMessage){
    trigger_error($pagopaErrMessage, E_USER_WARNING);
    $str_Warning .= $pagopaErrMessage.'<br>';
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

