<?php
//TODO sulla createdynamicreminder i casi con sollecito vanno rivisti, qui vanno tolti perchè escludiamo gli atti che hanno già solleciti, dato
//che se ne occupa la crea nuovi/aggiorna/elimina solleciti

use Psr\Log\NullLogger;

require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_pagamenti.php");
require_once(INC."/function.php");
require_once(INC."/function_postalCharge.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");
require_once(CLS."/cls_dispute.php");
require_once(CLS."/cls_view.php");
require_once(TCPDF . "/fpdi.php");

//per tentare di risolvere l'errore del massimo tempo di 60 secondi superato
ini_set('max_execution_time', 3000);
//per tentare di risolvere l'errore del massimo spazio di 512 MB superato
//per enti grandi come Savona siamo passati a 2GB
ini_set('memory_limit', '2048M');

$PrintDate = CheckValue('PrintDate','s');
$ProcessingDate= DateInDB(CheckValue('ProcessingDate','s'));  // riformatata in yyyy-mm-dd
$CurrentYear = $_SESSION['year'];
$ultimate = CheckValue('ultimate','n');
$ElaborationType = CheckValue('ElaborationType','s');
$PrintOrderBy = CheckValue('PrintOrderBy','s');
$Filters = CheckValue('Filters','s');

$str_Table = CheckValue('Table','s');

$a_ToPrint = array();

//RECUPERO DATI DI CONFIGURAZIONIE DELL'ENTE RELATIVAMENTE AI PAGAMENTI
//E FILTRI DI SELEZIONE VERBALI DA ELABORARE
if($str_Table==""){

    //FILTRI VERBALI DA ELABORARE
    $Search_FromFineDate            = CheckValue('Filter_FromFineDate','s');
    $Search_ToFineDate              = CheckValue('Filter_ToFineDate','s');
    $Search_FromNotificationDate    = CheckValue('Filter_FromNotificationDate','s');
    $Search_ToNotificationDate      = CheckValue('Filter_ToNotificationDate','s');
    $Search_FromProtocolId          = CheckValue('Filter_FromProtocolId','s');
    $Search_ToProtocolId            = CheckValue('Filter_ToProtocolId','s');
    $Search_HasPaymentRate          = CheckValue('Filter_HasPaymentRate','n');
    $Search_HasDispute              = CheckValue('Filter_HasDispute','n');
    $s_TypePlate                    = CheckValue('Filter_TypePlate','s'); //dice se lavoro su nazionale N o estero
    
    $str_ProcessingTable = ($s_TypePlate=="N") ? "National" : "Foreign";
    
    //Controlla per primo il paese del trasgressore, poi quello del verbale
    $str_WhereCountry = ($s_TypePlate=="N") ? " AND COALESCE(T.CountryId,F.CountryId) = 'Z000'" : " AND COALESCE(T.CountryId,F.CountryId) != 'Z000'";

    if($Search_FromFineDate != "")      $str_Where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
    if($Search_ToFineDate != "")        $str_Where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
    if($Search_FromNotificationDate != "")      $str_Where .= " AND FN.NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
    if($Search_ToNotificationDate != "")        $str_Where .= " AND FN.NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";   
    if($Search_FromProtocolId>0)        $str_Where .= " AND F.ProtocolId>=".$Search_FromProtocolId;
    if($Search_ToProtocolId>0)          $str_Where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
    if ($Search_HasPaymentRate == 0) {
        $str_Where .= " AND PR.StatusRateId IS NULL";
    } else if($Search_HasPaymentRate == 2){
        $str_Where .= " AND PR.StatusRateId IS NOT NULL";
    }
    if ($Search_HasDispute == 0) {
        $str_Where .= " AND F.Id NOT IN(SELECT FineId FROM V_FineDispute)";
    } else if($Search_HasDispute == 2){
        $str_Where .= " AND F.Id IN(SELECT FineId FROM V_FineDispute)";
    }
    
    //SELEZIONE PARAMETRI PROCEDURA DI PAGAMENTO
    $rs_ProcessingData = $rs->Select('ProcessingDataPayment'.$str_ProcessingTable, "CityId='".$_SESSION['cityid']."'");
    
} else {

    $str_ProcessingTable = ($str_ProcessingTable=="National") ? "National" : "Foreign";
    $str_WhereCountry = ($str_ProcessingTable=="National") ? " AND COALESCE(T.CountryId,F.CountryId) = 'Z000'" :  " AND COALESCE(T.CountryId,F.CountryId) != 'Z000'";

    //SELEZIONE PARAMETRI PROCEDURA DI PAGAMENTO
    $rs_ProcessingData = $rs->Select('ProcessingDataPayment'.$str_ProcessingTable, "Disabled=0 AND Automatic=1");   
}

if(mysqli_num_rows($rs_ProcessingData) <= 0){
    $_SESSION['Message']['Error'] = "Non è possibile procedere con l'elaborazione se non sono stati impostati i parametri dell'ente competente.<br >Compilare i parametri dell'ente dal menù Ente\Procedure Ente.";
    $AdditionalFilters = array();
    $AdditionalFilters['PrintOrderBy'] = $PrintOrderBy;
    $AdditionalFilters['ElaborationType'] = $ElaborationType;
    $AdditionalFilters['PrintDate'] = CheckValue('PrintDate','s');
    $AdditionalFilters['ProcessingDate'] = CheckValue('ProcessingDate','s');
    $AdditionalFilters['btn_search'] = 1;
    
    header("location: ".impostaParametriUrl($AdditionalFilters, 'prc_payment.php'.$Filters));
    DIE;
}

//selezione criterio ordinamento
$strOrder = "";
$html_ordinamento = "";

switch($PrintOrderBy) {
    case 'FineDate':
        $strOrder .= "F.FineDate";
        $html_ordinamento .= "Data accertamento";
        break;
    case 'FineNotificationDate':
        $strOrder .= "FN.NotificationDate";
        $html_ordinamento .= "Data notifica verbale";
        break;
    case 'TrespasserName':
        $strOrder .= "coalesce(T.CompanyName, T.Surname)";
        $html_ordinamento .= "Nome trasgressore";
        break;
    case 'ProtocolId':
    default:
        $strOrder .= "F.ProtocolId";
        $html_ordinamento .= "Cronologico";
        break;
}

//Inizializazione pdf
$html = '<h3 style="text-align: center;"><strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'<br />
        ELABORAZIONE DEI PAGAMENTI PER EMISSIONE SOLLECITI</strong></h3>
    
        <p style="text-align: center;">Stampato il '.$PrintDate.'</p>
        <br />
        Il pagamento del verbale originario doveva essere effettuato entro '.FINE_DAY_LIMIT.' giorni dalla data della notifica.
        <h3><strong>SPECIFICHE DELLA STAMPA</strong></h3>
        <u><strong>Cronologico</strong></u> : Cronologico del verbale.<br />
        <u><strong>Rif.to</strong></u> : Riferimento del verbale.<br />
        <u><strong>Trasgressore</strong></u> : trasgressore indicato sul verbale.<br />
        <u><strong>Data</strong></u> : data accertamento del verbale.<br />
        <u><strong>Ora</strong></u> :  ora accertamento del verbale.<br />
        <u><strong>Targa</strong></u> : targa del veicolo indicato sul verbale.<br />
        <u><strong>Pagato</strong></u> : totale pagamenti effettuati.<br />
        <u><strong>Dovuto</strong></u> : totale dovuto al lordo degli eventuali pagamenti.<br />
        <u><strong>Risultato</strong></u> : esito dell\'elaborazione.<br />
            
        <h3><strong>OPZIONI SELEZIONATE NEL MOMENTO DELLA STAMPA&nbsp;</strong></h3>
        <br />';

$page_format = array('Rotate'=>45);

//Inizializzazione PDF//////////////////////////////////////////////////////////////////////////////
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

$pdf->TemporaryPrint= 0;

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetSubject('');
$pdf->SetKeywords('');

$pdf->SetMargins(10,10,10);
$pdf->setCellHeightRatio(1.5);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
//////////////////////////////////////////////////////////////////////////////////////////////////
$FileName = "";
if ($ultimate){
    $FileName = $_SESSION['cityid'].'_chiusura_pagamenti_'.date("Y-m-d_H-i").'.pdf';
} else {
    $FileName = $_SESSION['cityid'].'_chiusura_pagamenti_PROVVISORIO.pdf';
}

//LETTURA PARAMETRI 
while($r_ProcessingData = mysqli_fetch_array($rs_ProcessingData)){
    
    //apro la transazione perché potrei fare più operazioni su db per l'elaborazione
    $rs->Start_Transaction();

    //LETTURA PARAMETRI PROCEDURA DI PAGAMENTO
    $CityId                         = $r_ProcessingData['CityId'];
    $f_AmountLimit                  = $r_ProcessingData['AmountLimit']; //Importo da pagare soglia sotto cui non va emesso il sollecito

    //TODO Servono?
    $WaitDay = $r_ProcessingData['WaitDay']; //Giorni ulteriore attesa che vanno aggiunti ai termini per elaborare il sollecito (data notifica + 60gg)
    //$ApplyPercentualOnPrefectureFee = $r_ProcessingData['ApplyPercentualOnPrefectureFee']; //dise se applicare la maggiorazione semestrale alla sanzione fissata nel ricorso
    $n_ReducedPaymentDayAccepted    = $r_ProcessingData['ReducedPaymentDayAccepted']; //gg pagamento ridotti sono i gg di tolleranza per valutare il ritardo di pagamento nel caso dei pagati ridotti
    $n_PaymentDayAccepted           = $r_ProcessingData['PaymentDayAccepted']; //gg pagamento normale  sono i gg di tolleranza per valutare il ritardo di pagamento nel caso dei pagati normali
    
    $IncludeNotificationResearch = $r_ProcessingData['IncludeNotificationResearch']; //dice se includere le spese quando la sanzione è fissata nel ricorso
    
    //LETTURA PARAMETRI DELL'ENTE
    //TODO Controllare se ha senso rifare qui la select quando c'è la stessa select in "initialization.php"
    $rs_Customer = $rs->Select('V_Customer', "CityId='" . $_SESSION['cityid'] . "' AND CreationType = 1");
    $r_Customer = mysqli_fetch_array($rs_Customer);
    
    $s_NationalityPercentual = $s_TypePlate == "N" ? "NationalPercentualReminder" : "ForeignPercentualReminder";
    
//     echo "<br >festa patronale";
//     print_r($r_Customer);

    if(!isset($r_Customer['PatronalFeast'] ) || empty($r_Customer['PatronalFeast']) ){
        $PageTitle = CheckValue('PageTitle','s');
        $_SESSION['Message'] = "Non è possibile procedere con l'elaborazione se non è stata indicata la data della festa patronale tra le configurazioni dell'ente competente.<br >Compilare il campo Festa patronale nella scheda Indirizzo del menù Ente\Gestione Ente.";
        header("location: prc_payment.php?PageTitle=".$PageTitle."&btn_search=1&TypePlate=".$s_TypePlate."&Search_FromFineDate=".$Search_FromFineDate."&Search_ToFineDate=".$Search_ToFineDate."&Search_FromNotificationDate=".$Search_FromNotificationDate."&Search_ToNotificationDate=".$Search_ToNotificationDate."&Search_FromProtocolId=".$Search_FromProtocolId."&Search_ToProtocolId=".$Search_ToProtocolId."&PrintOrderBy=".$PrintOrderBy."&ElaborationType=".$ElaborationType);
        DIE;
    }
    
    $rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentNationalPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$CityId."'");
    $PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentNationalPaymentDayReminder'];
    
    //Per escludere gli inviti in AG
    $str_Where .= " AND (F.KindSendDate IS NULL OR (F.KindSendDate IS NOT NULL AND F.Id IN(SELECT FineId FROM FineHistory WHERE NotificationTypeId = 30))) ";
    
    //recupera tutti i record dei verbali notificati in un certo stato per cui esaminare i pagamenti
    //$rs_FineProcedure = $rs->Select('V_PaymentProcedure',$str_Where.$str_WhereCountry." AND CityId='".$CityId."' AND ProtocolYear=".$CurrentYear, $strOrder);
    $cls_view = new CLS_VIEW(PRC_PAYMENT);
    $query = $cls_view->generateSelect($str_Where.$str_WhereCountry." AND F.CityId='".$CityId."' AND F.ProtocolYear=".$CurrentYear, null, $strOrder, $RecordLimit);
    $rs_FineProcedure = $rs->SelectQuery($query);
    
    //trigger_error("SELECT * FROM V_PaymentProcedure WHERE ".$str_Where.$str_WhereCountry." AND CityId='".$CityId."' AND ProtocolYear=".$CurrentYear." ORDER BY ".$strOrder);
    
    //**************************INIZIO MODIFICA CLS_PAGAMENTI**********************
    
    //RECUPERA I DATI DEI PAGAMENTI
    if(mysqli_num_rows($rs_FineProcedure)>0){

        //Prima pagina//////////////////////////////////////////////////////////////////////////////
        $pdf->AddPage('L', $page_format);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->setCellPaddings(1, 0.5, 1, 0.5);
        $pdf->MultiCell(35, 0, 'Ordina stampa per', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, 'Tipologia targhe', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, 'Da data verb.', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, 'A data verb.', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(35, 0, 'Da data not.', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, 'A data not.', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, 'Da cron', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, 'A cron', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, 'Numero record', 1, '', 0, 0, '', '', true);
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(35, 0, $html_ordinamento, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, ($s_TypePlate == "N" ? "Nazionali" : "Estere"), 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $Search_FromFineDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $Search_ToFineDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(35, 0, $Search_FromNotificationDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $Search_ToNotificationDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $Search_FromProtocolId, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, $Search_ToProtocolId, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, $RecordLimit, 1, '', 0, 0, '', '', true);
        
        $pdf->SetFont('helvetica', '', 10);
        ////////////////////////////////////////////////////////////////////////////////
        

        //ora inizio elaborazione serve solo per indicare la durata dell'elaborazione
        $ProcessingStartTime = date("H:i:s"); 

        //numero di verbali elaborati
        $n_CountFine                = 0;    //Numero verbali coinvolti
        
        $n_CountRow                 = 0;    //Numero di riga

        $n_CountFineClosed          = 0;    //Verbali pagati (vanno in stato 30)
        $n_CountFineNotPayed        = 0;    //Verbali non pagati (vanno in stato 27)
        $n_CountFinePartialPayed    = 0;    //Verbali pagati parzialmente (vanno in stato 28)
        $n_CountFineLatePayed       = 0;    //Verbali pagati parzialmente in ritardo (vanno in stato 28)
        $n_CountFineDispute         = 0;    //Verbali con ricorso aperto (vengono messi in lista ma saltati dall'elaborazione)
        $n_CountFineOutDate         = 0;    //Verbali con termini ancora non scaduti (vengono messi in lista ma saltati dall'elaborazione)
        $n_CountFinePrescription    = 0;    //Verbali prescritti (vengono messi in lista ma saltati dall'elaborazione)
        $n_CountFineDisputeClosed   = 0;    //Verbali con ricorso chiuso accolto (vengono saltati dall'elaborazione finchè non passano i termini poi vanno in stato 27)

        $str_Content                = '';

        //STAMPA quanti verbali saranno elaborati
        //if($str_Table!="") echo " ". mysqli_num_rows($rs_FineProcedure)." <br />";

        //*****************QUI BISOGNA INSERIRE LE LOGICHE DELLA CLS_PAGAMENTI**************
            
        while($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)){
            $b_pagato = $b_parziale = $b_parzialeNoRit = $b_parzialeRit = $b_omesso = false;

            $cls_pagamenti = new cls_pagamenti($r_FineProcedure['Id'], $r_FineProcedure['CityId'], $ProcessingDate);
            
            $a_CronScaglioni = $cls_pagamenti->getCronScaglioni();
            $a_CronPagamanti = $cls_pagamenti->getCronPagamenti();
            
            //Totale pagato
            $TotalAmountPayed = 0;
            //Totale dovuto
            $TotalAmount = 0;
            
            // data di notifica
            $NotificationDate = $r_FineProcedure['NotificationDate'];
            
            //data che determina se il sollecito è elaborabile perchè passati i termini necessari
            $ElaborationStartingDate = date('Y-m-d', strtotime($NotificationDate. ' + '.(FINE_DAY_LIMIT + $WaitDay).' days'));
            
            $b_HasDisputeFee = $cls_pagamenti->getDisputeAmount() > 0;
            $b_HasFinePrefectureFee = $cls_pagamenti->getFinePrefectureFee() > 0;
            
            
//             trigger_error("Verifica cronologia scaglioni verbale: ".$r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']);
//             foreach($a_CronScaglioni as $scaglione => $importo)
//                 trigger_error("Scaglione ".$scaglione." Importo: ".$importo);
            
            //Se non ha ricorsi oppure ne ha senza spese fissate, guarda i pagamenti nelle tolleranze. Il ricorso in tutti i casi non dev'essere accolto
            if((!$cls_pagamenti->getHasDispute() || ($cls_pagamenti->getHasDispute() && !$b_HasDisputeFee)) && ($cls_pagamenti->getStatus()!=6)){
                //NOTA: Dovuto e pagato sono passate per riferimento, perchè la funzione ne deve aggiornare i valori
                $scaglionePagatoTolleranze = controllaTolleranze($CityId, $NotificationDate, $a_CronScaglioni, $a_CronPagamanti, $cls_pagamenti->isReduced(), $n_ReducedPaymentDayAccepted, $n_PaymentDayAccepted, $f_AmountLimit, $TotalAmount, $TotalAmountPayed);
                //trigger_error("Ritorno dalla funzione: ".($scaglionePagatoTolleranze == true ? "vero" : "falso")." --> ".($r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']));
                $b_pagato = ($scaglionePagatoTolleranze != false);
            }
            //Se il ricorso è accolto è considerato pagato
            else if($cls_pagamenti->getStatus() == 6){
                $b_pagato = true;
            }
            
            $n_CountFine++; //contatore righe elaborate
            
            $str_Trespasser = implode(' ', array(trim($r_FineProcedure['CompanyName']), trim($r_FineProcedure['Surname']), trim($r_FineProcedure['Name'])));
            
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
                
                $CurrentReminderNotificationFee = $cls_pagamenti->getCurrentCustomerReminderNotificationFee();
                $n_Semesters = $cls_pagamenti->getSemester();
                $AdditionalFee = $cls_pagamenti->getAdditionalFee();
                $MaxFee = 0;
                $Fee = 0;
                $HalfMaxFee = 0;
                
                //Se ho un importo fissato nel ricorso, tutti gli importi corrispondono ad esso, altrimenti a quelli del verbale
                if($b_HasDisputeFee){
                    $Fee = $cls_pagamenti->getDisputeAmount();
                    $HalfMaxFee = 0;
                    $MaxFee = 0;
                } 
                elseif($b_HasFinePrefectureFee){
                    $Fee = $cls_pagamenti->getFinePrefectureFee();
                    $HalfMaxFee = 0;
                    $MaxFee = 0;
                }
                else {
                    //Importo entro 60 gg
                    $Fee = $cls_pagamenti->getFineFee();
                    //Metà del massimo
                    $HalfMaxFee = $cls_pagamenti->getFineMaxFee();
                    //Importo da aggiungere a quello entro i 60 giorni per raggiungere la metà del massimo (questo dato è principalmente salvato per le stampe)
                    $MaxFee = $HalfMaxFee - $cls_pagamenti->getFineFee();
                }
                //Totale pagato
                $TotalAmountPayed = $cls_pagamenti->getPayed();
                //Totale dovuto
                $TotalAmount = $cls_pagamenti->getFee();
                //Parte di maggiorazione
                $Surcharge = $cls_pagamenti->getSurcharge();
                
                $b_pagato =
                ($cls_pagamenti->getStatus() == 3 || $cls_pagamenti->getStatus() == 4) ||
                (($cls_pagamenti->getStatus() == 1 || $cls_pagamenti->getStatus() == 2) && (($TotalAmount - ($TotalAmountPayed + $f_AmountLimit))) < 0.01);
                
                //Se ho verificato che non è pagato considerando le tolleranze dei solleciti, guardo se è omesso oppure parziale
                if(!$b_pagato){
                    $b_omesso = $cls_pagamenti->getStatus() == 0;
                    $b_parzialeNoRit = $cls_pagamenti->getStatus() == 1;    //Parziale non in ritardo
                    $b_parzialeRit = $cls_pagamenti->getStatus() == 2;      //Parziale in ritardo
                    $b_parziale = $b_parzialeNoRit || $b_parzialeRit;       //Parziale senza specificare se in ritardo o meno
                }
                //trigger_error("Verbale ".$r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']." prz. rit: ".($b_parzialeRit == true ? "vero" : "falso")." prz. NO RIT: ".($b_parzialeNoRit == true ? "vero" : "falso")." prz. generico: ".($b_parziale == true ? "vero" : "falso"));
            }
            
            //NOTA!!!!! L'ordine degli if conta
            
            //Termini non trascorsi
            if($ProcessingDate < $ElaborationStartingDate) {
                $n_CountRow++;
                //La stampa dei verbali con termini non ancora trascorsi viene fatta solo sui provvisori
                if(!$ultimate)
                {
                    //non son ancora passati i termini per emettere il sollecito e non lo elaboriamo
                    $n_CountFineOutDate++;
                    $a_ToPrint[] = array(
                        $pdf,
                        $n_CountRow,
                        $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'],
                        $r_FineProcedure['Code'],
                        $str_Trespasser,
                        DateOutDB($r_FineProcedure['FineDate']),
                        TimeOutDB($r_FineProcedure['FineTime']),
                        $r_FineProcedure['VehiclePlate'],
                        null,
                        null,
                        "TERMINI NON ANCORA TRASCORSI",
                        array(255, 255, 153),
                        array(0,0,0)
                    );
                }
                continue;
            }
            
            //Prescrizione
            if($cls_pagamenti->getStatus() == 7 && !$b_pagato) {
                $n_CountRow++;
                
                //Prescritto non va avanti nell'elaborazione
                $n_CountFinePrescription++;
                
                $a_ToPrint[] = array(
                    $pdf,
                    $n_CountRow,
                    $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'],
                    $r_FineProcedure['Code'],
                    $str_Trespasser,
                    DateOutDB($r_FineProcedure['FineDate']),
                    TimeOutDB($r_FineProcedure['FineTime']),
                    $r_FineProcedure['VehiclePlate'],
                    null,
                    null,
                    "PRESCRITTO in Data: ".DateOutDB($cls_pagamenti->getPrescriptionDate()),
                    array(255,255,255),
                    array(0,0,0)
                );
                
                if ($ultimate) {
                    $PaymentProcedure = 0;
                    $a_FineNotification = array(
                        array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
                        array('field'=>'PaymentProcedureOffReason','selector'=>'value','type'=>'str','value'=>MOTIVO_PRESCRIZIOME),                        
                    );
                    
                    $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);
                }
                continue;  //passa al record del verbale successivo
            }
            
            //Ricorso in attesa o accolto
            if($cls_pagamenti->getStatus() == 5 || $cls_pagamenti->getStatus() == 6)
                {
                $n_CountRow++;
                //il ricorso è accolto, sospeso o in attesa e non faccio niente
                //ricorso rinviato o accolto da oltre 215 gg ed è stato chiuso o non faccio niente ma qui non ci dovrei arrivare
                $cls_pagamenti->getStatus() == 5 ? $n_CountFineDispute++ : $n_CountFineDisputeClosed++;
                
                $a_ToPrint[] = array(
                    $pdf,
                    $n_CountRow,
                    $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'],
                    $r_FineProcedure['Code'],
                    $str_Trespasser,
                    DateOutDB($r_FineProcedure['FineDate']),
                    TimeOutDB($r_FineProcedure['FineTime']),
                    $r_FineProcedure['VehiclePlate'],
                    null,
                    null,
                    $cls_pagamenti->getStatus() == 5 ? "RICORSO IN ATTESA" : "RICORSO ACCOLTO",
                    array(255, 255, 153),
                    array(0,0,0)
                );
                continue;
                }
    
                //Se pagato oppure pagato parziale ma pagamento + importo esclusione sollecito > dovuto allora è da considerarsi pagato
                if ($b_pagato) {
                        if($ElaborationType == 'closed' || $ElaborationType == 'any') {
                            $n_CountRow++;
                            $n_CountFineClosed++;
                            //stampa cifre e scaglione
                            $a_ToPrint[] = array(
                                $pdf,
                                $n_CountRow,
                                $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'],
                                $r_FineProcedure['Code'],
                                $str_Trespasser,
                                DateOutDB($r_FineProcedure['FineDate']),
                                TimeOutDB($r_FineProcedure['FineTime']),
                                $r_FineProcedure['VehiclePlate'],
                                $TotalAmountPayed,
                                $TotalAmount,
                                messaggioRiga($b_pagato, $cls_pagamenti, $CityId, $n_ReducedPaymentDayAccepted, $n_PaymentDayAccepted),
                                array(255, 255, 255),
                                coloreTestoChiusi($cls_pagamenti->getPayed(), $cls_pagamenti->getFee(), $f_AmountLimit)
                            );
                        }
                    }
                              
                if ($b_omesso) { //se il totale pagato è zero è un omesso pagamento
                   if(($ElaborationType == 'omitted' || $ElaborationType == 'any')) {
                       $n_CountRow++;
                       $n_CountFineNotPayed++;
                       // echo $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " NON PAGATO<br />";
                       $a_ToPrint[] = array(
                           $pdf,
                           $n_CountRow,
                           $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'],
                           $r_FineProcedure['Code'],
                           $str_Trespasser,
                           DateOutDB($r_FineProcedure['FineDate']),
                           TimeOutDB($r_FineProcedure['FineTime']),
                           $r_FineProcedure['VehiclePlate'],
                           0,
                           $TotalAmount, //TODO Capire come sostituire questo dato
                           messaggioRiga($b_pagato, $cls_pagamenti, $CityId, $n_ReducedPaymentDayAccepted, $n_PaymentDayAccepted),
                           array(255,255,255),
                           array(0,0,0)
                       );
                   }
                }
                   
                //Pagato parziale o parziale ritardo
                if ($b_parziale) {
                   //trigger_error("Entra nel parziale per il verbale ".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']);
                   //parziale non tardivo, tutti, tardivi
                   if(($ElaborationType == 'partial' || $ElaborationType == 'any' || $ElaborationType == 'late')) {
                       $b_Print = false;
                       
                       if($b_parzialeRit && ($ElaborationType == 'any' || $ElaborationType == 'late' || $ElaborationType == 'partial')){
                           $n_CountFineLatePayed++;
                           $b_Print = true;
                           //trigger_error("Entra nel parziale ritardo ".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']);
                       }
                       if($b_parzialeNoRit && ($ElaborationType == 'any' || $ElaborationType == 'partial')){
                           $n_CountFinePartialPayed++;
                           $b_Print = true;
                           //trigger_error("Entra nel parziale NO RITARDO per il verbale ".$r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']);
                       }
                       
                       if($b_Print){
                           $n_CountRow++;
                           //ritardato e selezione late
                           //stampa cifre e scaglione
                           $a_ToPrint[] = array(
                               $pdf,
                               $n_CountRow,
                               $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear'],
                               $r_FineProcedure['Code'],
                               $str_Trespasser,
                               DateOutDB($r_FineProcedure['FineDate']),
                               TimeOutDB($r_FineProcedure['FineTime']),
                               $r_FineProcedure['VehiclePlate'],
                               $TotalAmountPayed,
                               $TotalAmount,
                               messaggioRiga($b_pagato, $cls_pagamenti, $CityId, $n_ReducedPaymentDayAccepted, $n_PaymentDayAccepted),
                               array(255,255,255),
                               array(0,0,0)
                           );
                       }
                   }
                }
                
            //costruzione array valori per creare il sollecito
            if ($ultimate){
                
                $a_FineReminder = array(
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['Id'],'settype'=>'int'),
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['TrespasserId'],'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['TrespasserTypeId'],'settype'=>'int'),
                    array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$CurrentReminderNotificationFee,'settype'=>'flt'), //spese di invio sollecito + eventuali solleciti precedenti
                    array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>null,"nullable" => true), //viene valorizzato a null perché esiste ancora il pdf
                    array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>(int)$PaymentDays,'settype'=>'int'), //DataPaymentNationalPaymentDayReminder
                    array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$MinPaymentDate,'settype'=>'date'), //data del primo pagamento
                    array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>(int)$DayFromNotificationToFirstPayment,'settype'=>'int'), //giorni dalla notifica al primo pagamento 
                    array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>(int)($DayFromNotificationToFirstPayment-FINE_DAY_LIMIT),'settype'=>'int'), //giorni dalla notifica al primo pagamento  - gg limite
                    array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>(int)$n_Semesters,'settype'=>'int'), //numero semestri maturati 
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>(float)$Fee,'settype'=>'flt'), //$Fee è il minimo edittale dopo 60 gg o il 70% del min edittale
                    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>(float)$MaxFee,'settype'=>'flt'), //Max edittale al netto del minimo già conteggiato
                    array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>(float)$HalfMaxFee,'settype'=>'flt'), //metà del max edittale
                    array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>(float)$AdditionalFee,'settype'=>'flt'), //totale spese notifica verbale + ricreca + notifica solleciti precedenti (NON METTEREI I SOLLECITI PRECEDENTI QUI)
                    array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>(float)$TotalAmountPayed,'settype'=>'flt'), //totale pagato
                    array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>(float)($TotalAmount + $CurrentReminderNotificationFee),'settype'=>'flt'), //totale delle sanzioni dovute al lordo del pagato
                    array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>(float)$r_Customer[$s_NationalityPercentual],'settype'=>'flt'), //percentuale maggiorazione semestrale
                    array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>(float)$Surcharge,'settype'=>'flt'), //totale maggiorazione semestrale
                );
                
                
                if ($b_pagato) {
                    if($ElaborationType == 'closed' || $ElaborationType == 'any') {
                        $a_Fine = array(
                            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>30,'settype'=>'int'),
                            array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                        );
                        $rs->Update('Fine',$a_Fine,"Id=".$r_FineProcedure['Id']);
                        
                        $PaymentProcedure = 0;
                        $a_FineNotification = array(
                            array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
                            array('field'=>'PaymentProcedureOffReason','selector'=>'value','type'=>'str','value'=>MOTIVO),
                        );
                        $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);
                    }
                        
                } else if($b_omesso && ($ElaborationType == 'omitted' || $ElaborationType == 'any')) { 
                        $a_Fine = array(
                            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>27,'settype'=>'int'),
                            array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                            array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                        );
                        //Bug 3514: avendo messo in left join la FineReminder con Id nullo oppure documentation nullo, i dati che ci servono sono già recuperati dalla vista
                        //cerco sollecito provvisorio
//                         $rs_FineReminderPreview = $rs->SelectQuery("SELECT * FROM FineReminder WHERE FineId=". $r_FineProcedure['Id']. " AND FlowDate IS NULL");
//                         $r_FineReminderPreview = mysqli_fetch_array($rs_FineReminderPreview);
                        
                        //se esiste un sollecito non emesso 
                        if ($r_FineProcedure['FineReminderId'] > 0) {
                            $rs->Update('FineReminder',$a_FineReminder,"Id = ".$r_FineProcedure['FineReminderId']);
                        }
                        else {//se non esistono solleciti non emessi inserisci
                            $rs->Insert('FineReminder',$a_FineReminder);
                        }
                        $rs->Update('Fine',$a_Fine,"Id=".$r_FineProcedure['Id']);
                
                } else if(($b_parzialeRit && ($ElaborationType == 'any' || $ElaborationType == 'late' || $ElaborationType == 'partial')) ||
                        ($b_parziale && ($ElaborationType == 'any' || $ElaborationType == 'partial'))){
                        $a_Fine = array(
                            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>28,'settype'=>'int'),
                            array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                            array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate,'settype'=>'date'),
                        );
                        
                        //cerco sollecito provvisorio
                        //Bug 3514: avendo messo in left join la FineReminder con Id nullo oppure documentation nullo, i dati che ci servono sono già recuperati dalla vista
//                         $rs_FineReminderPreview = $rs->SelectQuery("SELECT * FROM FineReminder WHERE FineId=". $r_FineProcedure['Id']. " AND FlowDate IS NULL");
//                         $r_FineReminderPreview = mysqli_fetch_array($rs_FineReminderPreview);
                        
                        //se esiste un sollecito non emesso
                        if ($r_FineProcedure['FineReminderId'] > 0) {
                            $rs->Update('FineReminder',$a_FineReminder,"Id = ".$r_FineProcedure['FineReminderId']);
                        }
                        else {//se non esistono solleciti non emessi inserisci
                            $rs->Insert('FineReminder',$a_FineReminder);
                        }
                        $rs->Update('Fine',$a_Fine,"Id=".$r_FineProcedure['Id']);
                }
                
                //Imposta ad 1 gli StatusRateId dei verbali dei quali si stanno elaborando i solleciti
                if($r_FineProcedure["StatusRateId"] != null){
                    $a_PaymentProcedure = array(
                        array('field'=>'StatusRateId','selector'=>'value','type'=>'int','value'=>RATEIZZAZIONE_CHIUSA,'settype'=>'int'),
                        array('field'=>'ClosingDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'))
                    );
                    $rs->Update('PaymentRate',$a_PaymentProcedure,"FineId={$r_FineProcedure['Id']} AND StatusRateId=".RATEIZZAZIONE_APERTA);
                }
            }  //caso ultimate
            //echo $n_CountRow.'<br>';
        } //Fine ciclo su verbali da esaminare
        
        //**************QUI FINISCONO LE LOGICHE DELLA CLS_PAGAMENTI************
        //Nuova pagina//////////////////////////////////////////////////////////////////////////////
        newBasePageType1($pdf, $r_Customer, $n_CountFine);
        
        foreach($a_ToPrint as $index => $a_row){
            //In caso di un numero di righe maggiore al consentito per la pagina, viene aggiunta una nuova pagina e viene ristampato tutto il template
            //Da riconteggiare il numero di righe massime
            if(($index+1)%27 == 0){
                //Nuova pagina//////////////////////////////////////////////////////////////////////////////
                newBasePageType1($pdf, $r_Customer, $n_CountFine);
            }
            //Funzione che stampa la riga basandosi sulle informazioni presenti in ogni posizione dell'array
            call_user_func_array('printPDFRowType1', $a_row);
        }
    }
    
    //************************FINE MODIFICA CLS_PAGAMENTI**********************************
        
    //Fine elaborazione 
    $ProcessingEndTime= date("H:i:s");
    
    $n_total_row = $n_CountFine;
    if ($ElaborationType == 'partial'){
        $n_total_row = $n_CountFinePartialPayed;
        $n_total_row += $n_CountFineLatePayed;
        }
    if ($ElaborationType == 'late')
        $n_total_row = $n_CountFineLatePayed;
    if ($ElaborationType == 'omitted')
        $n_total_row = $n_CountFineNotPayed;
    if ($ElaborationType == 'closed')
        $n_total_row = $n_CountFineClosed;
    
    //trigger_error("Conteggio --> Parziali: ".$n_CountFinePartialPayed." Parziali ritardo: ".$n_CountFineLatePayed." Omessi: ".$n_CountFineNotPayed." pagati: ".$n_CountFineClosed);
    
    //Stampo la tabella con i totalizzatori
    printSummaryTableType1($pdf, $n_total_row, $ProcessingDate);
    
    if($n_CountFine>0){
    
    
        $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
        while($r_UserMail = mysqli_fetch_array($rs_UserMail)){
    
            $str_Content = $r_UserMail['CityTitle'].": sono stati elaborati n. ".$n_CountFine." verbali";
    
    
            if($n_CountFineClosed>0){
            $str_Content .= "<br />VERBALI CHIUSI PER PAGAMENTO CORRETTO: ".$n_CountFineClosed." <br />";
            }
            if($n_CountFineNotPayed>0){
            $str_Content .= "<br />VERBALI NON PAGATI: ".$n_CountFineNotPayed." <br />";
            }
            if($n_CountFinePartialPayed>0){
            $str_Content .= "<br />VERBALI PAGATI PARZIALMENTE: ".$n_CountFinePartialPayed." <br />";
            }
            if($n_CountFineLatePayed>0){
                $str_Content .= "<br />VERBALI PAGATI IN RITARDO: ".$n_CountFineLatePayed." <br />";
            }
            if($n_CountFineOutDate>0){
                $str_Content .= "<br />VERBALI PER CUI NON SONO TRASCORSI TERMINI PER SOLLECITO: ".$n_CountFineOutDate." <br />";
            }
            if($n_CountFineDispute>0){
                $str_Content .= "<br />VERBALI IMPUGNATI IN ATTESA: ".$n_CountFineDispute." <br />";
            }
            if($n_CountFineDisputeClosed>0){
                $str_Content .= "<br />VERBALI IMPUGNATI CON ESITO ACCOLTO: ".$n_CountFineDisputeClosed." <br />";
            }
            if($n_CountFinePrescription>0){
                $str_Content .= "<br />VERBALI PRESCRITTI: ".$n_CountFinePrescription." <br />";
            }
    
            $a_Mail = array(
                array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Elaborazione pagamenti"),
                array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
                array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
                array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
            );
    
            if ($ultimate)
                $rs->Insert('Mail',$a_Mail);
    
        }
    }
    
    $a_ProcessingTime = array(
        array('field'=>'ProcessingDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate),
        array('field'=>'ProcessingStartTime','selector'=>'value','type'=>'str','value'=>$ProcessingStartTime),
        array('field'=>'ProcessingEndTime','selector'=>'value','type'=>'str','value'=>$ProcessingEndTime),
    );
    
    if ($ultimate) {
        $rs->Update('ProcessingDataPayment'.$str_ProcessingTable,$a_ProcessingTime,"CityId='".$CityId."'");
        
        $a_ProcessingPaymentsMade = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
            array('field'=>'NationalityId','selector'=>'value','type'=>'int','value'=>($str_ProcessingTable=="National") ? 1:2),
            array('field'=>'ProcessingDate','selector'=>'value','type'=>'date','value'=>$ProcessingDate),
            array('field'=>'ProcessingStartTime','selector'=>'value','type'=>'str','value'=>$ProcessingStartTime),
            array('field'=>'ProcessingEndTime','selector'=>'value','type'=>'str','value'=>$ProcessingEndTime),
            array('field'=>'ReportDocumentName','selector'=>'value','type'=>'str','value'=>$FileName),
        );
        $rs->Insert('ProcessingPaymentsMade', $a_ProcessingPaymentsMade);
    }
        
    $rs->End_Transaction();

}//Fine ciclo su configurazioni

if(!$ultimate){
    //Stampa l'etichetta "STAMPA PROVVISORIA"
    printPrevisionalLabel($pdf);
}

if ($ultimate){
    $pdf->Output(ROOT."/doc/print/payment/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/payment/'.$FileName;
} else {
    $pdf->Output(ROOT."/doc/print/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/'.$FileName;
}

if($str_Table=="") {
    $AdditionalFilters = array();
    $AdditionalFilters['PrintOrderBy'] = $PrintOrderBy;
    $AdditionalFilters['ElaborationType'] = $ElaborationType;
    $AdditionalFilters['PrintDate'] = CheckValue('PrintDate','s');
    $AdditionalFilters['ProcessingDate'] = CheckValue('ProcessingDate','s');
    $AdditionalFilters['btn_search'] = 1;
    
    if($ultimate){
        $message = "
                    <div>Azione eseguita con successo.</div>
                    <div style='border:1px solid #B1D9C4; border-radius: 1rem; padding: 1rem;'>
                        <span><b style='color:red;'>Omessi: </b> $n_CountFineNotPayed  </span>
                        <span><b style='color:#DBC925;'>Parziali: </b> ".($n_CountFinePartialPayed+$n_CountFineLatePayed)." ($n_CountFineLatePayed tardivi)  </span>
                        <span><b style='color:#DBC925;'>Sospesi: </b> ".($n_CountFineDispute+$n_CountFineOutDate+$n_CountFineDisputeClosed+$n_CountFinePrescription)."</span>
                        <span><b style='color:green;'>Chiusi: </b> $n_CountFineClosed </span>
                    </div>
                   ";
        $_SESSION['Message']['Success'] = $message;
        }
    
    header("location: ".impostaParametriUrl($AdditionalFilters, 'prc_payment.php'.$Filters));
}

