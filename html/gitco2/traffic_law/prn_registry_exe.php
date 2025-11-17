<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/function_postalCharge.php");
require(INC . "/initialization.php");
require_once(PGFN . "/fn_prn_registry.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");
require(TCPDF . "/fpdi.php");



global $rs;

$FileName = "{$_SESSION['cityid']}_print_registry_".date('Y-m-d')."_".date('H-i');


switch ($Search_Genre){
    case 1:
        $str_Genre = "Persona fisica";
        break;
    case 2:
        $str_Genre = "Persona giuridica";
        break;
    default:
        $str_Genre = "Tutti";
}

switch ($n_TypeViolation){
    case 1:
        $str_TypeViolation = "Preavvisi";
        break;
    case 2:
        $str_TypeViolation = "Verbali";
        break;
    case 3:
        $str_TypeViolation = "Solleciti emessi";
        break;
    default:
        $str_TypeViolation = "";
}

switch ($n_TypePayment){
    case 1:
        $str_TypePayment = "A credito";
        break;
    case 2:
        $str_TypePayment = "Pagato";
        break;
    case 3:
        $str_TypePayment = "Non pagato";
        break;
    case 4:
        $str_TypePayment = "Parziale";
        break;
    default:
        $str_TypePayment = "";
}

switch ($n_HasInjunction){
    case 1:
        $str_TypeInjunction = "Iscritti";
        break;
    case 2:
        $str_TypeInjunction = "Non iscritti";
        break;
    default:
        $str_TypeInjunction = "";
}

switch ($n_TypeNotification){
    case 1:
        $str_TypeNotification = "Notificati via posta";
        break;
    case 2:
        $str_TypeNotification = "Ancora da notificare";
        break;
    case 3:
        $str_TypeNotification = "Non notificati";
        break;
    case 4:
        $str_TypeNotification = "Notif. pec, messo o altro";
        break;
    case 5:
        $str_TypeNotification = "Inviati via posta";
        break;
    case 6:
        $str_TypeNotification = "Inviati via pec, messo o altro";
        break;
    default:
        $str_TypeNotification = "";
}


switch ($FineArchive){
    case 1:
        $str_FineArchive = "Includi";
        break;
    case 2:
        $str_FineArchive = "Solo loro";
        break;
    default:
        $str_FineArchive = "Escludi";
}

switch ($FineDispute){
    case 1:
        $str_FineDispute = "Includi";
        break;
    case 2:
        $str_FineDispute = "Solo loro";
        break;
    default:
        $str_FineDispute = "Escludi";
}

switch($Search_ForeignFineNotPayed){
    case 0:
        $str_ForeignFineNotPayed = "NO";
        break;
    case 1:
        $str_ForeignFineNotPayed = "SI";
        break;
}

switch($Search_HasKindSendDate){
    case 0:
        $str_HasKindSendDate = "NO";
        break;
    case 1:
        $str_HasKindSendDate = "SI";
        break;
}

switch($Search_NationalityId){
    case 1:
        $str_IsNational = "Italiano";
        break;
    case 2:
        $str_IsNational = "Estero";
        break;
}

switch($Search_NotificationStatus){
    case 0:
        $str_Renotified = "NO";
        break;
    case 1:
        $str_Renotified = "SI";
        break;
}

switch($Search_HasPEC){
    case 0:
        $str_HasPEC = "NO";
        break;
    case 1:
        $str_HasPEC = "SI";
        break;
}

switch($Search_TypeArchive){
    case 35:
        $str_TypeArchive = "Archiv. da ente";
        break;
    case 36:
        $str_TypeArchive = "Archiv. per noleggio";
        break;
    case 37:
        $str_TypeArchive = "Archiv. d'ufficio";
        break;
}

//Rilevatore 
if(!empty($Search_Detector))
    {
    $rs_Detector = $rs->Select("Detector","Id =".$Search_Detector);
    $r_Detector = $rs->getArrayLine($rs_Detector);
    $str_Detector = $r_Detector["TitleIta"];
}

//Violazione
if(!empty($Search_Violation))
        {
    $rs_Violation = $rs->Select("ViolationType","Id =".$Search_Violation);
    $r_Violation = $rs->getArrayLine($rs_Violation);
    $str_Violation = $r_Violation["Title"];
    }

//Anno corrente
if($Search_CurrentYear)
    {
    $str_CurrentYear = "SI";
}
else
    {
    $str_CurrentYear = "NO";
}

//Località
if(!empty($Search_Locality))
    {
    $rs_Locality = $rs->Select("sarida.City","Id =".$Search_Locality);
    $r_Locality = $rs->getArrayLine($rs_Locality);
    $str_Locality = $r_Locality["Title"];
}

//Articolo
if(!empty($Search_ArticleId))
    {
    $rs_Article = $rs->Select("Article","Id =".$Search_ArticleId);
    $r_Article = $rs->getArrayLine($rs_Article);
    $str_Article = $r_Article["Article"]."/".$r_Article["Paragraph"];
    }

$str_MoreData = unserialize(MORE_DATA);

$str_Query = creaQuery();
        
$rs_Registry = $rs->SelectQuery($str_Query);
//echo $str_Query; die;

$a_Results = $rs->getResults($rs_Registry);
$a_FinalResults = array();

///////////////////////////////////////////////////////////////////////////////////////////////////////

$n_Owned = 0;   //omessi
$n_Partial = 0;  //parziali
$n_Payed = 0;   //saldati
$n_OverPayed = 0; //a credito

$GrandTotalFee = 0;

$GrandTotalOmitted = 0;
$GrandTotalResidual = 0;
$GrandTotalCredit = 0;
$GrandTotalExceded = 0;
$GrandTotalPartial = 0;
$GrandTotalSettled = 0;
$GrandTotalOwned = 0;

$n_FineWithReminder = 0;
$n_FineWithReminderEmitted = 0;
$n_FineWithoutReminder = 0;

//Variabili per il conteggio dei solleciti
$LastReminderFineId = 0; //Conserva il FineId dell'ultimo verbale con sollecito
$MaxReminderId = 0; //Conserva il ReminderId dell'ultimo sollecito
$ReminderCount = 0; //Contatore solleciti per stesso FineId

foreach($a_Results as $result){
    $AmountPayed = 0;
    $AmountOwned = 0;
    $AmountFee = 0; //spese addizionali
    $PaymentDate = null;
    
    $PaymentStatus = "";
    $ReminderStatus = "";
    $PreviousProtocol = "";
    $ReducedDate = null;
    
    //PAYMENT
    $r_Payment = $rs->getArrayLine($rs->SelectQuery('SELECT MIN(PaymentDate) AS PaymentDate, SUM(Amount) AS Amount FROM FinePayment WHERE FineId=' . $result['FineId']));
    
    //ADDITIONAL ARTICLE
    $rs_AdditionalArticle=null;
    if ($result['ArticleNumber'] > 1) {
        $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $result['FineId'], "ArticleOrder");
    }
    //ARTICLE TARIFF
    $r_ArticleTariff = $rs->getArrayLine($rs->Select('ArticleTariff', "ArticleId=" . $result['ArticleId'] . " AND Year=" . $result['ProtocolYear']));
    
    //Controlla se vi è un PreviousId su Fine e ne prende il cron
    if (isset($result['PreviousId']) && $result['PreviousId'] != 0){
        $rs_PreviousProtocolId = $rs->SelectQuery("SELECT CONCAT(ProtocolId,'/',ProtocolYear) AS PreviousProtocol FROM Fine WHERE Id=".$result['PreviousId']);
        $PreviousProtocol = $rs->getArrayLine($rs_PreviousProtocolId)['PreviousProtocol'];
    }
    
    //Cerca la comunicazione dati trasgressore per la decurtazione punti e ne aggiunge la riga
    $rs_Communication = $rs->SelectQuery('SELECT ReducedDate FROM FineCommunication WHERE ReducedDate IS NOT NULL AND FineId='.$result['FineId']);
    $ReducedDate = $rs->getArrayLine($rs_Communication)['ReducedDate'] ?? null;
    
    $AmountPayed = number_format($r_Payment['Amount'], 2, '.', '');
    
    //Pagamenti dovuti/////////////////////////////////////////////////////////////////////////////////////
    $n_Interval = 0;
    $NotificationDate = isset($result['NotificationDate']) ? $result['NotificationDate'] : $result['FineDate'];
    $a_OwnedPayment = calcolaImportiSulVerbale($result,$rs_AdditionalArticle,$r_ArticleTariff,$r_Customer);
    $AmountFee = number_format($a_OwnedPayment['AdditionalFee'], 2, '.', '');
    $ReminderNotificationFee = $result['ReminderNotificationFee'];
    
    //Calcola la differenza tra la data di (notifica/verbale) e (pagamento/creazione del report)
    if (isset($r_Payment['PaymentDate'])){
        $PaymentDate = $r_Payment['PaymentDate'];
        $n_Interval = date_diff(date_create($NotificationDate), date_create($PaymentDate));
    } else {
        $n_Interval = date_diff(date_create($NotificationDate), date_create(DateInDB($d_PrintDate)));
    }
    $n_Interval = $n_Interval->format('%a');
    //Se il tipo di violazione selezionato è preavvisi/verbali oppure nulla ma il verbale attuale non ha sollecito
    //Effettua il calcolo del dovuto
    if($n_TypeViolation < 3){
        //Calcola il dovuto in base al risultato della differenza (spese incluse addizionali)
        //08/02/2021 calcoliamo il dato approssimato a quanti giorni sono passati tra il pagamento o il calcolo del prospetto
        // e la notifica o la data infrazione in assenza di essa.
        if ($n_Interval <= FINE_DAY_LIMIT_REDUCTION){
            $AmountOwned = number_format($a_OwnedPayment['ReducedPartial'], 2, '.', '');
        } else if ($n_Interval <= FINE_DAY_LIMIT){
            $AmountOwned = number_format($a_OwnedPayment['ReducedTotal'], 2, '.', '');
        } else {
            $AmountOwned = number_format($a_OwnedPayment['Total'], 2, '.', '');
        }
    }
    //Se il tipo di violazione selezionato è solleciti emessi oppure nulla ma il verbale attuale ha sollecito
    //Recupera il dovuto del sollecito
    if($n_TypeViolation == 3){
        $AmountOwned = $result['TotalAmount'];
    }
    
    //Controlla se il verbale ha almeno un sollecito con flusso associato
    $hasReminderFlow = isset($result['ReminderId']);
    
    //Se ha almeno un sollecito ed il FineId attuale è diverso da quello appena precedente azzero il conteggio dei solleciti legati al FineId in corso
    if($hasReminderFlow && ($result['FineId'] != $LastReminderFineId)){
        $ReminderCount = 1;
        $LastReminderFineId = $result['FineId']; //Segno il FineId attuale come id con sollecito corrente
        //Cerco il ReminderId massimo legato al FineId corrente in modo da non fare cumulo dei pagamenti e dei dovuti di tutti i solleciti legati allo stesso verbale
        $rs_Reminders = $rs->SelectQuery("SELECT MAX(Id) AS ReminderId FROM FineReminder WHERE FineId = ".$result['FineId']." AND FlowDate IS NOT NULL ORDER BY Id, FineId");
        $r_Reminders = mysqli_fetch_array($rs_Reminders);
        $MaxReminderId = $r_Reminders['ReminderId'];
        
    }
    //Se ha almeno un sollecito e se il FineId attuale è uguale a quello processato in precedenza incremento il conteggio dei solleciti perchè multipli
    elseif ($hasReminderFlow && ($result['FineId'] == $LastReminderFineId)){
        $ReminderCount++;
    }
    
    //Controlla se il sollecito attuale è l'ultimo disponibile per il verbale
    $isLastReminderAvailable = ($hasReminderFlow && ($result['ReminderId'] == $MaxReminderId));
    
    //Definisce se un verbale sia o meno da mostrare
    $isFineShowable = (($n_TypeViolation != 3 && !$hasReminderFlow) || ($n_TypeViolation != 3 && $hasReminderFlow && $isLastReminderAvailable));
    
    //Definisce se il sollecito sia o meno da mostrare
    $isReminderShowable = ($n_TypeViolation == 3 && $Search_IsLastEmitted && $hasReminderFlow && ($result['ReminderId'] == $MaxReminderId)) || ($n_TypeViolation == 3 && !$Search_IsLastEmitted && $hasReminderFlow);
    
    
    if(!$Search_ForeignFineNotPayed){
        if($n_TypePayment == 2 && $AmountOwned > $AmountPayed) continue;
        elseif($n_TypePayment == 3 && $AmountPayed != 0) continue;
        elseif(($n_TypePayment == 4 && ($AmountOwned <= $AmountPayed)) || ($n_TypePayment == 4 && $AmountPayed == 0)) continue;
    }
    
    //Verifica se il risultato è considerabile per la stampa o meno
    //In caso di verbale collegato a più solleciti, prende la riga associata al sollecito più recente
    if($n_TypeViolation < 3 && !$isFineShowable) continue;
    if($n_TypeViolation == 3 && !$isReminderShowable) continue;
    
    
////////////////////////////////////////////////////////////////////////////////////////

    if(($isReminderShowable && $isLastReminderAvailable) || $isFineShowable){
        //Somma il totale spese della pagina e assoluto
        $GrandTotalFee += $AmountFee;
        $GrandTotalOwned += $AmountOwned;
        //Somma i totali dei solleciti
        if($n_TypeViolation == 3){
            $GrandTotalFee += $ReminderNotificationFee;
        }
    }
    
    //Controlla l'ammontare del pagato
    if (isset($r_Payment['Amount']) && $r_Payment['Amount'] > 0){
        
        if ($AmountPayed < $AmountOwned){
            $PaymentStatus = "Pag. il\n".DateOutDB($PaymentDate);
            if($n_TypeViolation ==3){
                $ReminderStatus = "Pagato parziale";
            }
            if(($isReminderShowable && $isLastReminderAvailable) || $isFineShowable){
                $n_Partial++;
                $GrandTotalPartial += $AmountPayed;
                
                $GrandTotalResidual += ($AmountOwned - $AmountPayed);
            }
        } else if ($AmountPayed == $AmountOwned){
            $PaymentStatus = "Sal. il\n".DateOutDB($PaymentDate);
            if($n_TypeViolation ==3){
                $ReminderStatus = "Pagato totale";
            }
            if(($isReminderShowable && $isLastReminderAvailable) || $isFineShowable){
                $n_Payed++;
                $GrandTotalSettled += $AmountPayed;
            }
        } else if ($AmountPayed > $AmountOwned){
            $PaymentStatus = "A credito";
            if($n_TypeViolation ==3){
                $ReminderStatus = "Pagato totale";
            }
            if(($isReminderShowable && $isLastReminderAvailable) || $isFineShowable){
                $n_OverPayed++;
                $GrandTotalCredit += $AmountOwned; //($AmountPayed - $AmountOwned);
                $GrandTotalExceded += ($AmountPayed - $AmountOwned);
            }
        }
    } else {
        $PaymentStatus = "Da saldare";
        if($n_TypeViolation ==3){
            $ReminderStatus = "Non pagato";
        }
        if(($isReminderShowable && $isLastReminderAvailable) || $isFineShowable){
            $n_Owned++;
            $GrandTotalOmitted += $AmountOwned;
        }
    }
    
    
    
    if(($isReminderShowable && $isLastReminderAvailable) || $isFineShowable){
        //Controlla se sono presenti solleciti per il verbale
        if ($hasReminderFlow){
            $PaymentStatus .= "\nSollecito emesso";
            $n_FineWithReminder ++;
            $n_FineWithReminderEmitted ++;
        } else {
            //Controlla se sono presenti solleciti creati e mai spediti
            if(isset($result['ReminderDate'])){
                $rs_ReminderCreated = $rs->Select("FineReminder", "FineId=".$result['FineId']." AND PrintDate='".$result['ReminderDate']."'");
                
                if (mysqli_num_rows($rs_ReminderCreated) > 0) {
                    $PaymentStatus .= "\nSollecito creato";
                    $n_FineWithReminder ++;
                } else
                    $n_FineWithoutReminder ++;
                    
            } else
                $n_FineWithoutReminder ++;
        }
    }
    
//////////////////////////////////////////////////////////////////////////////////
    
    
    $rs_AdditionalTrespasser = $rs->SelectQuery("
            SELECT
            FT.TrespasserId,
            T.Code,
            T.CompanyName,
            T.Name,
            T.Surname,
            T.TaxCode,
            T.VatCode
            FROM FineTrespasser FT
            JOIN Trespasser T ON FT.TrespasserId = T.Id
            WHERE TrespasserTypeId IN(3,10,15,16) AND FT.FineId=".$result['FineId']);
    
    $result[INDEX_TRESPASSERS] = $rs->getResults($rs_AdditionalTrespasser);
    
    
    ////////
    
    $result[INDEX_AMOUNTOWNED] = $AmountOwned;
    $result[INDEX_AMOUNTPAYED] = $n_TypeViolation < 3 ? $AmountPayed : $result['Amount']; //In caso di sollecito prende solo il pagamento del sollecito in base alla data di pagamento
    $result[INDEX_AMOUNTFEE] = $AmountFee;
    $result[INDEX_PREVIOUSPROTOCOL] = $PreviousProtocol;
    $result[INDEX_REMINDERCOUNT] = $ReminderCount;
    $result[INDEX_REMINDERSTATUS] = $ReminderStatus;
    $result[INDEX_REDUCEDDATE] = $ReducedDate;
    $result[INDEX_REMINDERNOTIFICATIONFEE] = $ReminderNotificationFee;
    $result[INDEX_PAYMENTSTATUS] = $PaymentStatus;
    
    $result[INDEX_REMINDERCOUNT] = $ReminderCount;
    
    $result[INDEX_ISLASTREMINDERAVAILABLE] = $isLastReminderAvailable;
    $result[INDEX_ISFINESHOWABLE] = $isFineShowable;
    $result[INDEX_ISREMINDERSHOWABLE] = $isReminderShowable;
    
    $ProtocolId = 0;
    
    $a_FinalResults[] = $result;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////STAMPE////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

switch ($Action){
    case 'Pdf': {
        $PageTotalFee = 0;
        $PageTotalPayed = 0;
        $PageTotalOwned = 0;
        
        //PARAMETRI PDF/////////////////////////////////////
        $pdf = new TCPDF('L','mm','A4', true,'UTF-8',false,true);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Registro cronologico');
        $pdf->SetPrintHeader(false);
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        
        $pdf->SetMargins(10,10,10);
        $pdf->setCellHeightRatio(1.5);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
        ////////////////////////////////////////////////
        
        //PAGINA FILTRI////////////////////////////////
        
        $html = '<h3 style="text-align: center;margin:0;">
        <strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'</strong>
        </h3>
        <p style="text-align: center;">Registro cronologico dei verbali ART.200 DLGS. 285/92 - Art.383 del Regolamento</p>
        <br />
        <h3 style="margin:0;">
        <strong>OPZIONI SELEZIONATE AL MOMENTO DELLA STAMPA&nbsp;</strong>
        </h3>';
        
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 9);
        
        $pdf->LN(5);
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->setCellPaddings(1, 0.5, 1, 0.5);
        $pdf->MultiCell(40, 0, 'Accertamento', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Notifica', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Postalizzazione', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Archiviazione', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Cronologico', 1, '', 0, 1, '', '', true);
        
        $pdf->SetFont('helvetica', '', 8);
        
        $pdf->MultiCell(20, 0, 'Da', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'A', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'Da', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'A', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'Da', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'A', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'Da', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'A', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'Da', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'A', 1, '', 0, 1, '', '', true);
        
        $pdf->MultiCell(20, 0, $Search_FromFineDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_ToFineDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_FromNotificationDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_ToNotificationDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_FromSendDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_ToSendDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_FromArchiveDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_ToArchiveDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_FromProtocolId, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $Search_ToProtocolId, 1, '', 0, 1, '', '', true);
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(40, 0, 'Nominativo', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, 'Genere', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Comune', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, 'Articolo', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Violazione', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, 'Anno corrente', 1, '', 0, 1, '', '', true);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(40, 0, $Search_Trespasser, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, $str_Genre, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_Locality, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(20, 0, $str_Article, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_Violation, 1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(30, 0, $str_CurrentYear, 1, 'L', 0, 1, '', '', true);
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(120, 0, 'Luogo infrazione', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(120, 0, 'Rilevatore', 1, '', 0, 1, '', '', true);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(120, 0, $Search_Address, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(120, 0, $str_Detector, 1, 'L', 0, 1, '', '', true);
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(40, 0, 'Archiviati', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Tipo archiv.', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Ricorsi', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Pagamento', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Tipo', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Ruolo', 1, '', 0, 1, '', '', true);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(40, 0, $str_FineArchive, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_TypeArchive, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_FineDispute, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_TypePayment, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_TypeViolation, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_TypeInjunction, 1, '', 0, 1, '', '', true);
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(40, 0, 'Notifica/Invio', 1, '', 0, 1, '', '', true);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(40, 0, $str_TypeNotification, 1, '', 0, 1, '', '', true);
        
        $pdf->LN(5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(40, 0, 'Solo verb. esteri non pagati', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Solo rinotifiche', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, 'Solo verb. PEC', 1, '', 0, 0, '', '', true);
        if($_SESSION['userlevel'] > 2){
            $pdf->MultiCell(40, 0, 'Includi inviti in AG', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(40, 0, 'Nazionalità trasgressori', 1, '', 0, 1, '', '', true);
        }
        else{
            $pdf->MultiCell(40, 0, 'Includi inviti in AG', 1, '', 0, 1, '', '', true);
        }
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(40, 0, $str_ForeignFineNotPayed, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_Renotified, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_HasPEC, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(40, 0, $str_HasKindSendDate, 1, '', 0, 0, '', '', true);
        if($_SESSION['userlevel'] > 2){
            $pdf->MultiCell(40, 0, $str_IsNational, 1, '', 0, 0, '', '', true);
        }
        
        
        $pdf->SetFont('helvetica', '', 10);
        //////////////////////////////////////////////////////////
        
        //Nuova pagina
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetMargins(6,6,6);
        
        $pdf->LN(1);
        $pdf->writeHTML('Data: '.$d_PrintDate.' - COMUNE DI '.strtoupper($_SESSION['citytitle']).' - ANNO '.$_SESSION['year'], true, false, true, false, '');
        $pdf->LN(5);
        ////////////////////////////////////////////////////////////////////////////////
        
        //Parametrizzo la larghezza delle celle così da centralizzarne l'impostazione
        $w_Id = 15;
        $w_Trespasser = 46;
        $w_Register = 20;
        $w_Article = 50;
        $w_FineDate = 20;
        $w_PaymentStatus = 27;
        $w_Amount = 27;
        $w_PrintStatus = 34;
        $w_FineLink = 20;
        $w_NotificationStatus = 25;
        $w_ReminderDate = 34;
        $w_ReminderNumber = 20;
        $w_ReminderCounter = 20;
        $w_ReminderStatus = 27;
        $w_Motorizzazione1 = 15;
        $w_Motorizzazione2 = 244;
        $w_Motorizzazione3 = 25;
        $w_AdditionalTrespasserName = 244;
        
        //Parametrizzo l'altezza delle celle
        $h_Title = 12;
        $h_Cell = 15;
        
        //Parametrizzo il numero di celle dopo le quali fare una nuova pagina
        $n_MaxRows = 8;
        
        //Se imposto la visualizzazione degli archiviati (includi, solo loro) cambio la dimensione delle celle in modo da ospitare il campo note
        if($FineArchive > 0 && $n_TypeViolation < 3){
            $w_Trespasser = 40;
            $w_Article = 40;
            $w_PaymentStatus = 20;
            $w_Amount = 25;
            $w_FineLink = 18;
            $w_NotificationStatus = 27;
            $w_ArchiveNote = 29;
            $h_Cell = 20;
            $n_MaxRows = 6;
            $w_AdditionalTrespasserName = 217;
            $w_Motorizzazione3 = 29;
        }
        
        //Celle header//////////////////////////////////////////////////////////////////////////////
        $pdf->setCellPaddings(2, 0, 2, 0);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell($w_Id, $h_Title, "Id\nCod.", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        $pdf->MultiCell($w_Trespasser, $h_Title, "Trasgressore\nObbligato in Solido", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        $pdf->MultiCell($w_Register, $h_Title, "Reg.Crono\nTarga", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        $pdf->MultiCell($w_Article, $h_Title, "Tipo Veicolo/Articolo\nLuogo infrazione", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        $pdf->MultiCell($w_FineDate, $h_Title, "Data Verb.\nData Noti.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        $pdf->MultiCell($w_PaymentStatus, $h_Title, "Stato pagamento", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        $pdf->MultiCell($w_Amount, $h_Title, "Totale Pagato\nTotale Dovuto", 1, 'R', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
        
        if($n_TypeViolation < 3){ //Nulla, Verbali o Preinserimenti
            $pdf->MultiCell($w_PrintStatus, $h_Title, "Stato stampa Not.\nPostalizzazione", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
            $pdf->MultiCell($w_FineLink, $h_Title, "Verb. Collegato", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
            if($FineArchive > 0){
                $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_ArchiveNote, $h_Title, "Data/Note archiviazione", 1, '', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
            }
            else{
                $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
            }
        }
        elseif($n_TypeViolation == 3){ //Solleciti emessi
            $w_Motorizzazione3 = 27;
            $pdf->MultiCell($w_ReminderDate, $h_Title, "Data creaz. soll.\nData invio soll.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
            if($Search_IsLastEmitted){
                $pdf->MultiCell($w_ReminderNumber, $h_Title, "Num. soll. emessi", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                }
            else{
                $pdf->MultiCell($w_ReminderCounter, $h_Title, "Progr. sollecito", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                }
            $pdf->MultiCell($w_ReminderStatus, $h_Title, "Stato soll.", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
        }
        
        $pdf->SetFont('helvetica', '', 8);
        //////////////////////////////////////////////////////////////////////////////////////////////
        
        $a_Articles = array();
        
        $RowNumber = 0;
        $RecordRow = 0;
        $RecordNumber = count($a_FinalResults);
    
        foreach ($a_FinalResults as $r_Registry) {
            $RecordRow++;
            $RowNumber++;

            //Incrementa i totali conteggiando solo l'ultimo sollecito del verbale
            if(($r_Registry[INDEX_ISREMINDERSHOWABLE] && $r_Registry[INDEX_ISLASTREMINDERAVAILABLE]) || $r_Registry[INDEX_ISFINESHOWABLE]){
                //Somma il totale spese della pagina
                $PageTotalFee += $r_Registry[INDEX_AMOUNTFEE];
                //Somma il totale pagato della pagina
                $PageTotalPayed += $r_Registry[INDEX_AMOUNTPAYED];
                //Somma il totale dovuto della pagina
                $PageTotalOwned += $r_Registry[INDEX_AMOUNTOWNED];
                //Somma i totali dei solleciti
                if($n_TypeViolation == 3){
                    $PageTotalFee += $r_Registry[INDEX_REMINDERNOTIFICATIONFEE];
                }
            }
            
            $Trespasser = trim($r_Registry['CompanyName']." ".$r_Registry['Surname']." ".$r_Registry['Name']);
            $Trespasser = trim(preg_replace('/\s\s+/', ' ', $Trespasser));
            $Trespasser = mb_strimwidth($Trespasser, 0, 44, "...");
            
            $TaxCode = isset($r_Registry['TaxCode']) ? $r_Registry['TaxCode'] : $r_Registry['VatCode'];
            
            $Address = trim($r_Registry['Address']);
            $Address = trim(preg_replace('/\s\s+/', ' ', $Address));
            $Address = mb_strimwidth($Address, 0, 45, "...");
            
            $NotificationDate = isset($r_Registry['NotificationDate']) ? DateOutDB($r_Registry['NotificationDate']) : "Da notif.";
            $NotificationResult = isset($r_Registry['ResultTitle']) ? $r_Registry['ResultTitle'] : "-";
            $DeliveryDate = isset($r_Registry['DeliveryDate']) ? DateOutDB($r_Registry['DeliveryDate']) : "-";
            $SendDate = isset($r_Registry['SendDate']) ? DateOutDB($r_Registry['SendDate']) : "-";
            $PrintDate = isset($r_Registry['PrintDate']) ? DateOutDB($r_Registry['PrintDate']) : "-";
            $FlowDate = isset($r_Registry['FlowDate']) ? DateOutDB($r_Registry['FlowDate']) : "-";
            $ArchiveDate = isset($r_Registry['ArchiveDate']) ? DateOutDB($r_Registry['ArchiveDate']) : "-";
            $ArchiveNote = isset($r_Registry['ArchiveNote']) ? $r_Registry['ArchiveNote'] : "-";
            $PreviousArchiveNote = isset($r_Registry['PreviousArchiveNote']) ? $r_Registry['PreviousArchiveNote'] : "-";
            
            $TrespasserId = $r_Registry['TrespasserId'];
            $ProtocolId = $r_Registry['ProtocolId'];
            $Code = $r_Registry['Code'];
            $VehiclePlate = $r_Registry['VehiclePlate'];
            $VehicleTitle = $r_Registry['VehicleTitle'];
            $FullArticle = $r_Registry['FullArticle'];
            $FineDate = DateOutDB($r_Registry['FineDate']);
            
            
            //Riempe l'array degli articoli
            $a_Articles[$FullArticle] = (isset($a_Articles[$FullArticle]) ? $a_Articles[$FullArticle] : 0) + 1;
            
            //Riga dati////////////////////////////////////////////////////////////
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($w_Id, $h_Cell, $TrespasserId."\n".$Code, 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell($w_Trespasser, $h_Cell, $Trespasser."\n".$TaxCode, 1, '', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($w_Register, $h_Cell, $ProtocolId."\n".$VehiclePlate, 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell($w_Article, $h_Cell, $VehicleTitle."/".trim($FullArticle)."\n".$Address, 1, '', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($w_FineDate, $h_Cell, $FineDate."\n".$NotificationDate, 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell($w_PaymentStatus, $h_Cell, $r_Registry[INDEX_PAYMENTSTATUS], 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($w_Amount, $h_Cell, NumberDisplay($r_Registry[INDEX_AMOUNTPAYED])." €\n".NumberDisplay($r_Registry[INDEX_AMOUNTOWNED])." €", 1, 'R', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
            if($n_TypeViolation < 3){ //Nulla Verbali o Preinserimenti
                $pdf->MultiCell($w_PrintStatus, $h_Cell, $DeliveryDate."\n".$SendDate, 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                $pdf->MultiCell($w_FineLink, $h_Cell, $r_Registry[INDEX_PREVIOUSPROTOCOL], 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                if($FineArchive > 0){
                    $pdf->MultiCell($w_NotificationStatus, $h_Cell, $NotificationResult, 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                    $pdf->SetFont('helvetica', '', 6);
                    $pdf->MultiCell($w_ArchiveNote, $h_Cell, $ArchiveDate."\n".$ArchiveNote."\n".$PreviousArchiveNote, 1, '', 0, 1, '', '', true, 0, false, true, $h_Cell, 'M');
                    $pdf->SetFont('helvetica', '', 8);
                }
                else{
                    $pdf->MultiCell($w_NotificationStatus, $h_Cell, $NotificationResult, 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Cell, 'M');
                }
            }
            elseif($n_TypeViolation == 3){ //Solleciti emessi
                $pdf->MultiCell($w_ReminderDate, $h_Cell, $PrintDate."\n".$FlowDate, 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                $pdf->MultiCell($w_ReminderCounter, $h_Cell, $r_Registry[INDEX_REMINDERCOUNT], 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                $pdf->MultiCell($w_ReminderStatus, $h_Cell, $r_Registry[INDEX_REMINDERSTATUS], 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Cell, 'M');
            }
            
            ///////////////////////////////////////////////////////////////////////
            
            //Cerca i trasgressori addizionali e ne aggiunge le righe
            foreach ($r_Registry[INDEX_TRESPASSERS] as $r_AdditionalTrespasser){
                
                if ($RowNumber % $n_MaxRows == 0){
                    //Celle footer//////////////////////////////////////////////////////////////////////////////
                    $pdf->LN(5);
                    
                    $pdf->MultiCell(61, 12, "PARZIALI DI PAGINA", 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(178, 12, "Totali di pagina pagati:\nTotali di pagina dovuti (spese incluse):", 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(47, 12, NumberDisplay($PageTotalPayed)." €\n".NumberDisplay($PageTotalOwned)." €", 1, 'R', 0, 1, '', '', true);
                    
                    //Resetta il totale pagati e dovuti per la pagina corrente
                    $PageTotalPayed = 0;
                    $PageTotalOwned = 0;
                    
                    $pdf->MultiCell(61, 12, "", 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(178, 12, "Totale spese di pagina:", 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(47, 12, NumberDisplay($PageTotalFee)." €", 1, 'R', 0, 1, '', '', true);
                    
                    //Resetta il totale spese per la pagina corrente
                    $PageTotalFee = 0;
                    //////////////////////////////////////////////////////////////////////////////////////////////
                    
                    //Nuova pagina
                    $pdf->AddPage();
                    
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->SetMargins(6,6,6);
                    
                    $pdf->LN(1);
                    $pdf->writeHTML('Data: '.$d_PrintDate.' - COMUNE DI '.strtoupper($_SESSION['citytitle']).' - ANNO '.$_SESSION['year'], true, false, true, false, '');
                    $pdf->LN(5);
                    ////////////////////////////////////////////////////////////////////////////////
                    
                    //Celle header//////////////////////////////////////////////////////////////////////////////
                    $pdf->setCellPaddings(2, 0, 2, 0);
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->MultiCell($w_Id, $h_Title, "Id\nCod.", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Trespasser, $h_Title, "Trasgressore\nObbligato in Solido", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Register, $h_Title, "Reg.Crono\nTarga", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Article, $h_Title, "Tipo Veicolo/Articolo\nLuogo infrazione", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_FineDate, $h_Title, "Data Verb.\nData Noti.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_PaymentStatus, $h_Title, "Stato pagamento", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Amount, $h_Title, "Totale Pagato\nTotale Dovuto", 1, 'R', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    
                    if($n_TypeViolation < 3){
                        $pdf->MultiCell($w_PrintStatus, $h_Title, "Stato stampa Not.\nPostalizzazione", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        $pdf->MultiCell($w_FineLink, $h_Title, "Verb. Collegato", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        if($FineArchive > 0){
                            $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                            $pdf->MultiCell($w_ArchiveNote, $h_Title, "Data/Note archiviazione", 1, '', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                        }
                        else{
                            $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                        }
                    }
                    elseif($n_TypeViolation == 3){
                        $pdf->MultiCell($w_ReminderDate, $h_Title, "Data creaz. soll.\nData invio soll.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        if($Search_IsLastEmitted)
                            $pdf->MultiCell($w_ReminderNumber, $h_Title, "Num. soll. emessi", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                            else
                                $pdf->MultiCell($w_ReminderCounter, $h_Title, "Progr. sollecito", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                                $pdf->MultiCell($w_ReminderStatus, $h_Title, "Stato soll.", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                    }
                    $pdf->SetFont('helvetica', '', 8);
                    //////////////////////////////////////////////////////////////////////////////////////////////
                }
                
                $AdditionalTrespasser = trim($r_AdditionalTrespasser['CompanyName']." ".$r_AdditionalTrespasser['Surname']." ".$r_AdditionalTrespasser['Name']);
                $AdditionalTrespasser = trim(preg_replace('/\s\s+/', ' ', $AdditionalTrespasser));
                $AdditionalTaxCode = isset($r_AdditionalTrespasser['TaxCode']) ? $r_AdditionalTrespasser['TaxCode'] : $r_AdditionalTrespasser['VatCode'];
                
                //Riga dati Trasgressore aggiuntivo////////////////////////////////////////////////////
                $pdf->MultiCell(15, $h_Cell, $r_AdditionalTrespasser['TrespasserId']."\n".$r_AdditionalTrespasser['Code'], 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                $pdf->SetFont('helvetica', '', 7);
                $pdf->MultiCell($w_AdditionalTrespasserName, $h_Cell, $AdditionalTrespasser."\n".$AdditionalTaxCode, 1, '', 0, 0, '', '', true, 0, false, true, $h_Cell, 'M');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->MultiCell($w_NotificationStatus, $h_Cell, $NotificationResult, 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Cell, 'M');
                
                //////////////////////////////////////////////////////////////////////////////////////////
                
                $RowNumber++;
                
            }
            
            //Aggiunge la riga della la comunicazione dati trasgressore per la decurtazione punti
            if (!empty($r_Registry[INDEX_REDUCEDDATE])){
                
                if ($RowNumber % $n_MaxRows == 0){
                    //Celle footer//////////////////////////////////////////////////////////////////////////////
                    $pdf->LN(5);
                    
                    $pdf->MultiCell(61, 12, "PARZIALI DI PAGINA", 1, 'C', 0, 0, '', '', true, 0, false, true, 12, 'M');
                    $pdf->MultiCell(178, 12, "Totali di pagina pagati:\nTotali di pagina dovuti (comprensivi di spese):", 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(47, 12, NumberDisplay($PageTotalPayed)." €\n".NumberDisplay($PageTotalOwned)." €", 1, 'R', 0, 1, '', '', true);
                    
                    //Resetta il totale pagati e dovuti per la pagina corrente
                    $PageTotalPayed = 0;
                    $PageTotalOwned = 0;
                    
                    $pdf->MultiCell(61, 12, "", 1, 'C', 0, 0, '', '', true);
                    $pdf->MultiCell(178, 12, "Totale spese di pagina:", 1, 'L', 0, 0, '', '', true);
                    $pdf->MultiCell(47, 12, NumberDisplay($PageTotalFee)." €", 1, 'R', 0, 1, '', '', true);
                    
                    //Resetta il totale spese per la pagina corrente
                    $PageTotalFee = 0;
                    //////////////////////////////////////////////////////////////////////////////////////////////
                    
                    //Nuova pagina
                    $pdf->AddPage();
                    
                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->SetMargins(6,6,6);
                    
                    $pdf->LN(1);
                    $pdf->writeHTML('Data: '.$d_PrintDate.' - COMUNE DI '.strtoupper($_SESSION['citytitle']).' - ANNO '.$_SESSION['year'], true, false, true, false, '');
                    $pdf->LN(5);
                    ////////////////////////////////////////////////////////////////////////////////
                    
                    //Celle header//////////////////////////////////////////////////////////////////////////////
                    $pdf->setCellPaddings(2, 0, 2, 0);
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->MultiCell($w_Id, $h_Title, "Id\nCod.", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Trespasser, $h_Title, "Trasgressore\nObbligato in Solido", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Register, $h_Title, "Reg.Crono\nTarga", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Article, $h_Title, "Tipo Veicolo/Articolo\nLuogo infrazione", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_FineDate, $h_Title, "Data Verb.\nData Noti.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_PaymentStatus, $h_Title, "Stato pagamento", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_Amount, $h_Title, "Totale Pagato\nTotale Dovuto", 1, 'R', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    
                    if($n_TypeViolation < 3){
                        $pdf->MultiCell($w_PrintStatus, $h_Title, "Stato stampa Not.\nPostalizzazione", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        $pdf->MultiCell($w_FineLink, $h_Title, "Verb. Collegato", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        if($FineArchive > 0){
                            $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                            $pdf->MultiCell($w_ArchiveNote, $h_Title, "Data/Note archiviazione", 1, '', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                        }
                        else{
                            $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                        }
                    }
                    elseif($n_TypeViolation == 3){
                        $pdf->MultiCell($w_ReminderDate, $h_Title, "Data creaz. soll.\nData invio soll.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        if($Search_IsLastEmitted)
                            $pdf->MultiCell($w_ReminderNumber, $h_Title, "Num. soll. emessi", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                            else
                                $pdf->MultiCell($w_ReminderCounter, $h_Title, "Progr. sollecito", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                                $pdf->MultiCell($w_ReminderStatus, $h_Title, "Stato soll.", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                    }
                    $pdf->SetFont('helvetica', '', 8);
                    //////////////////////////////////////////////////////////////////////////////////////////////
                }
                
                //Riga dati motorizzazione////////////////////////////////////////////////////
                $pdf->MultiCell($w_Motorizzazione1, 12, '', 1, 'C', 0, 0, '', '', true);
                $pdf->MultiCell($w_Motorizzazione2, 12, 'Trasmissione Dati Motorizzazione in data: '.DateOutDB($r_Registry[INDEX_REDUCEDDATE]), 1, '', 0, 0, '', '', true, 0, false, true, 12, 'M');
                $pdf->MultiCell($w_Motorizzazione3, 12, '', 1, 'C', 0, 1, '', '', true);
                ///////////////////////////////////////////////////////////////////////////////////////
                
                $RowNumber++;
            }
            
            //Ogni tot righe crea una nuova pagina e scrive le celle header e footer
            if (($RowNumber % $n_MaxRows == 0) && ($RecordNumber != $RecordRow)){
                //Celle footer//////////////////////////////////////////////////////////////////////////////
                $pdf->LN(5);
                
                $pdf->MultiCell(61, 12, "PARZIALI DI PAGINA", 1, 'C', 0, 0, '', '', true, 0, false, true, 12, 'M');
                $pdf->MultiCell(178, 12, "Totali di pagina pagati:\nTotali di pagina dovuti (comprensivi di spese):", 1, 'L', 0, 0, '', '', true);
                $pdf->MultiCell(47, 12, NumberDisplay($PageTotalPayed)." €\n".NumberDisplay($PageTotalOwned)." €", 1, 'R', 0, 1, '', '', true);
                
                //Resetta il totale pagati e dovuti per la pagina corrente
                $PageTotalPayed = 0;
                $PageTotalOwned = 0;
                
                $pdf->MultiCell(61, 12, "", 1, 'C', 0, 0, '', '', true);
                $pdf->MultiCell(178, 12, "Totale spese di pagina:", 1, 'L', 0, 0, '', '', true);
                $pdf->MultiCell(47, 12, NumberDisplay($PageTotalFee)." €", 1, 'R', 0, 1, '', '', true);
                
                //Resetta il totale spese per la pagina corrente
                $PageTotalFee = 0;
                //////////////////////////////////////////////////////////////////////////////////////////////
                
                //Nuova pagina
                $pdf->AddPage();
                
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetMargins(6,6,6);
                
                $pdf->LN(1);
                $pdf->writeHTML('Data: '.$d_PrintDate.' - COMUNE DI '.strtoupper($_SESSION['citytitle']).' - ANNO '.$_SESSION['year'], true, false, true, false, '');
                $pdf->LN(5);
                ////////////////////////////////////////////////////////////////////////////////
                
                //Celle header//////////////////////////////////////////////////////////////////////////////
                $pdf->setCellPaddings(2, 0, 2, 0);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->MultiCell($w_Id, $h_Title, "Id\nCod.", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_Trespasser, $h_Title, "Trasgressore\nObbligato in Solido", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_Register, $h_Title, "Reg.Crono\nTarga", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_Article, $h_Title, "Tipo Veicolo/Articolo\nLuogo infrazione", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_FineDate, $h_Title, "Data Verb.\nData Noti.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_PaymentStatus, $h_Title, "Stato pagamento", 1, '', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                $pdf->MultiCell($w_Amount, $h_Title, "Totale Pagato\nTotale Dovuto", 1, 'R', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                
                if($n_TypeViolation < 3){
                    $pdf->MultiCell($w_PrintStatus, $h_Title, "Stato stampa Not.\nPostalizzazione", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    $pdf->MultiCell($w_FineLink, $h_Title, "Verb. Collegato", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    if($FineArchive > 0){
                        $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        $pdf->MultiCell($w_ArchiveNote, $h_Title, "Data/Note archiviazione", 1, '', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                    }
                    else{
                        $pdf->MultiCell($w_NotificationStatus, $h_Title, "Stato notifica", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                    }
                }
                elseif($n_TypeViolation == 3){
                    $pdf->MultiCell($w_ReminderDate, $h_Title, "Data creaz. soll.\nData invio soll.", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                    if($Search_IsLastEmitted)
                        $pdf->MultiCell($w_ReminderNumber, $h_Title, "Num. soll. emessi", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                        else
                            $pdf->MultiCell($w_ReminderCounter, $h_Title, "Progr. sollecito", 1, 'C', 0, 0, '', '', true, 0, false, true, $h_Title, 'M');
                            $pdf->MultiCell($w_ReminderStatus, $h_Title, "Stato soll.", 1, 'C', 0, 1, '', '', true, 0, false, true, $h_Title, 'M');
                }
                $pdf->SetFont('helvetica', '', 8);
                //////////////////////////////////////////////////////////////////////////////////////////////
            }
        }
        
        //Celle footer//////////////////////////////////////////////////////////////////////////////
        $pdf->LN(5);
        
        $pdf->MultiCell(61, 12, "PARZIALI DI PAGINA", 1, 'C', 0, 0, '', '', true, 0, false, true, 12, 'M');
        $pdf->MultiCell(178, 12, "Totali di pagina pagati:\nTotali di pagina dovuti (comprensivi di spese):", 1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(47, 12, NumberDisplay($PageTotalPayed)." €\n".NumberDisplay($PageTotalOwned)." €", 1, 'R', 0, 1, '', '', true);
        
        //Resetta il totale pagati e dovuti per la pagina corrente
        $PageTotalPayed = 0;
        $PageTotalOwned = 0;
        
        $pdf->MultiCell(61, 12, "", 1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell(178, 12, "Totale spese di pagina:", 1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(47, 12, NumberDisplay($PageTotalFee)." €", 1, 'R', 0, 1, '', '', true);
        
        //Resetta il totale spese per la pagina corrente
        $PageTotalFee = 0;
        //////////////////////////////////////////////////////////////////////////////////////////////
        
        //Resoconto//////////////////////////////////////////////////////////////////////////////
        $pdf->AddPage();
        
        $pdf->LN(1);
        $pdf->writeHTML('Data: '.$d_PrintDate.' - COMUNE DI '.strtoupper($_SESSION['citytitle']).' - ANNO '.$_SESSION['year'], true, false, true, false, '');
        $pdf->LN(5);
        
        $pdf->MultiCell(58, 42, "TOTALI", 1, 'C', 0, 0, '', '', true, 0, false, true, 42, 'M');
        $pdf->MultiCell(120, 42,
            "1 - Totale generale atti :".
            "\n2 - Totale omessi:".
            "\n3 - Totale generale pagati:".
            "\n        di cui".
            "\n        3.1 - Totale saldati:".
            "\n        3.2 - Totale a credito (saldato + eccedenza):".
            "\n        3.3 - Totale parziali pagamenti:".
            "\n".
            "\n4 - Residuo parziali pagamenti (rif. 3.3):".
            "\n5 - Totale eccedenza pagamenti a credito (rif. 3.2):", 1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(54, 42,
            "Num. ".($n_Owned+$n_Partial+$n_OverPayed+$n_Payed).
            "\nNum. ".$n_Owned.
            "\nNum. ".($n_Payed+$n_OverPayed+$n_Partial).
            "\n".
            "\nNum. ".$n_Payed.
            "\nNum. ".$n_OverPayed.
            "\nNum. ".$n_Partial.
            "\n".
            "\n".
            "\n", 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(54, 42,
            //number_format($GrandTotalPayed, 2, '.', '')." €\n".
            NumberDisplay($GrandTotalOwned)." €\n".
            NumberDisplay($GrandTotalOmitted)." €\n".
            NumberDisplay(($GrandTotalSettled+$GrandTotalCredit+$GrandTotalExceded+$GrandTotalPartial))." €\n".
            "\n".
            NumberDisplay($GrandTotalSettled)." €\n".
            NumberDisplay(($GrandTotalCredit + $GrandTotalExceded))." €\n".
            NumberDisplay($GrandTotalPartial)." €\n".
            "\n".
            NumberDisplay($GrandTotalResidual)." €\n".
            NumberDisplay($GrandTotalExceded)." €", 1, 'R', 0, 1, '', '', true);
        
        $pdf->MultiCell(58, 12, "", 1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell(120, 12, "Totale spese generale:", 1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(54, 12, "", 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(54, 12, NumberDisplay($GrandTotalFee)." €", 1, 'R', 0, 1, '', '', true);
        
        $pdf->MultiCell(58, 12, "NUMERO VERBALI SENZA SOLLECITO:\nNUMERO VERBALI CON SOLLECITO:\nDI CUI CON SOLL. EMESSO:", 1, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(25, 12, $n_FineWithoutReminder."\n".$n_FineWithReminder."\n".$n_FineWithReminderEmitted, 1, 'R', 0, 1, '', '', true);
        
        //////////////////////////////////////////////////////////////////////////////////////////////
        
        //Percentuali/////////////////////////////////////////////////////////////
        //NOTA: nella parcentaule dei pagati sono inclusi solo i saldati e quelli a credito, non i pagamenti parziali
        $pdf->LN(5);
        $pdf->writeHTML('Percentuale di verbali pagati rispetto a quelli emessi: '.round((($n_OverPayed+$n_Payed)/($n_Owned+$n_Partial+$n_OverPayed+$n_Payed))*100).'%', true, false, true, false, '');
        //La percentuale rispetto agli importi sarà da ripristinare quando i conteggi saranno più corretti
        //$pdf->LN(2);
        //$pdf->writeHTML('Percentuale dell\'importo pagato rispetto all\'importo dovuto (spese addizionali incluse): '.round((($GrandTotalSettled+$GrandTotalCredit+$GrandTotalPartial)/$GrandTotalOwned)*100).'%', true, false, true, false, '');
        //////////////////////////////////////////////////////////////////////////
        
        //Articoli/////////////////////////////////////////////////////////////////
        $pdf->AddPage();
        $pdf->LN(5);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $yReset = null;
        
        $n_ArticlesFound = 0;
        
        $pdf->MultiCell(35, 5, "Articolo", 1, 'C', 0, 0, '', '', true);
        $pdf->MultiCell(18, 5, "N. totale", 1, 'C', 0, 1, '', '', true);
        
        foreach ($a_Articles as $article => $number){
            $pdf->MultiCell(35, 5, $article, 1, 'C', 0, 0, $x, '', true);
            $pdf->MultiCell(18, 5, $number, 1, 'C', 0, 1, '', '', true);
            
            $n_ArticlesFound++;
            
            //Ogni 20 articoli sposta la generazione delle celle più a destra in modo da non finire in un'altra pagina
            if ($n_ArticlesFound % 18 == 0){
                $yReset = $pdf->GetY();
                $x += 55;
                $pdf->SetY($y);
                
                $pdf->MultiCell(35, 5, "Articolo", 1, 'C', 0, 0, $x, '', true);
                $pdf->MultiCell(18, 5, "N. totale", 1, 'C', 0, 1, '', '', true);
            }
        }
        
        //Reimposta il puntatore
        if ($yReset) $pdf->SetY($yReset);
        /////////////////////////////////////////////////////////
        
        $FileName .= '.pdf';
        
        $pdf->Output(PRINT_FOLDER.'/'.$FileName, "F");
        break;
    }
    
    case 'Excel': {
        $RecordNumber = count($a_FinalResults);
        $FileName .= '.xls';
        ob_start();
        $n_count = 0;
        $a_Articles = array();
        ?>
    	<table>
    		<tr></tr>
			<tr></tr>
    		<tr><td colspan="4">COMUNE DI <?= strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year']; ?></td></tr>
    		<tr><td colspan="6">Registro cronologico dei verbali ART.200 DLGS. 285/92 - Art.383 del Regolamento</td></tr>
			<tr></tr>
			<tr><td colspan="5">OPZIONI SELEZIONATE AL MOMENTO DELLA STAMPA</td></tr>
				<tr>
					<td colspan="2">Accertamento</td>
					<td>Da: <?= $Search_FromFineDate; ?></td>
					<td>A: <?= $Search_ToFineDate; ?></td>
				</tr>
				<tr>
					<td colspan="2">Notifica</td>
					<td>Da: <?= $Search_FromNotificationDate; ?></td>
					<td>A: <?= $Search_ToNotificationDate; ?></td>
				</tr>
				<tr>
					<td colspan="2">Postalizzazione</td>
					<td>Da: <?= $Search_FromSendDate; ?></td>
					<td>A: <?= $Search_ToSendDate; ?></td>
				</tr>
				<tr>
					<td colspan="2">Archiviazione</td>
					<td>Da: <?= $Search_FromArchiveDate; ?></td>
					<td>A: <?= $Search_ToArchiveDate; ?></td>
				</tr>
				<tr>
					<td colspan="2">Cronologico</td>
					<td>Da: <?= $Search_FromProtocolId; ?></td>
					<td>A: <?= $Search_ToProtocolId; ?></td>
				</tr>
				<tr>
					<td colspan="2">Nominativo</td>
					<td><?= $Search_Trespasser; ?></td>
				</tr>
				<tr>
					<td colspan="2">Genere</td>
					<td><?= $str_Genre; ?></td>
				</tr>
				<tr>
					<td colspan="2">Comune</td>
					<td><?= $str_Locality; ?></td>
				</tr>
				<tr>
					<td colspan="2">Articolo</td>
					<td><?= $str_Article; ?></td>
				</tr>
				<tr>
					<td colspan="2">Violazione</td>
					<td><?= $str_Violation; ?></td>
				</tr>
				<tr>
					<td colspan="2">Anno corrente</td>
					<td><?= $str_CurrentYear; ?></td>
				</tr>
				<tr>
					<td colspan="2">Luogo infrazione</td>
					<td><?= $Search_Address; ?></td>
				</tr>
				<tr>
					<td colspan="2">Rilevatore</td>
					<td><?= $str_Detector; ?></td>
				</tr>
				<tr>
					<td colspan="2">Archiviati</td>
					<td><?= $str_FineArchive; ?></td>
				</tr>
				<tr>
					<td colspan="2">Tipo archiv.</td>
					<td><?= $str_TypeArchive; ?></td>
				</tr>
				<tr>
					<td colspan="2">Ricorsi</td>
					<td><?= $str_FineDispute; ?></td>
				</tr>
				<tr>
					<td colspan="2">Pagamento</td>
					<td><?= $str_TypePayment; ?></td>
				</tr>
				<tr>
					<td colspan="2">Tipo</td>
					<td><?= $str_TypeViolation; ?></td>
				</tr>
				<tr>
					<td colspan="2">Ruolo</td>
					<td><?= $str_TypeInjunction; ?></td>
				</tr>
				<tr>
					<td colspan="2">Notifica/Invio</td>
					<td><?= $str_TypeNotification; ?></td>
				</tr>
				<tr>
					<td colspan="2">Solo verb. esteri non pagati</td>
					<td><?= $str_ForeignFineNotPayed; ?></td>
				</tr>
				<tr>
					<td colspan="2">Solo rinotifiche</td>
					<td><?= $str_Renotified; ?></td>
				</tr>
				<tr>
					<td colspan="2">Solo verb. PEC</td>
					<td><?= $str_HasPEC; ?></td>
				</tr>
				<tr>
					<td colspan="2">Includi inviti in AG</td>
					<td><?= $str_HasKindSendDate; ?></td>
				</tr>
				<?php if($_SESSION['userlevel'] > 2): ?>
				<tr>
					<td colspan="2">Nazionalità trasgressori</td>
					<td><?= $str_IsNational; ?></td>
				</tr>
				<?php endif; ?>
			<tr></tr>
    		<tr></tr>
    		<tr><td>Risultati: <?= count($a_FinalResults) ?></td></tr>
    		<tr></tr>
		</table>
		<table border="1">
    		<tr bgcolor="lightblue">
    			<th colspan="1">Progressivo</th>
    			<th colspan="1">Cron.</th>
    			<th colspan="1">Id</th>
    			<th colspan="1">Cod.</th>
    			<th colspan="1">Trasgressore/Obbligato in solido</th>
    			<th colspan="1">C.F./P.IVA</th>
    			<th colspan="1">Targa</th>
    			<th colspan="1">Tipo veicolo</th>
    			<th colspan="1">Articolo</th>
    			<th colspan="1">Luogo infrazione</th>
    			<th colspan="1">Data verbale</th>
    			<th colspan="1">Data notifica</th>
    			<th colspan="1">Stato pagamento</th>
    			<th colspan="1">Tot. pagato</th>
    			<th colspan="1">Tot. dovuto</th>
    			<?php if($n_TypeViolation < 3){ ?>
        			<th colspan="1">Stato stampa notifica</th>
        			<th colspan="1">Postalizzazione</th>
        			<th colspan="1">Verb. collegato</th>
        			<?php if($FineArchive > 0){ ?>
        				<th colspan="1">Stato notifica</th>
        				<th colspan="1">Data archiviazione</th>
        				<th colspan="1">Note archiviazione</th>
        			<?php }else{ ?>
        				<th colspan="1">Stato notifica</th>
        			<?php } ?>
    			<?php } 
    			      elseif($n_TypeViolation == 3){?>
        			      <th colspan="1">Data creazione sollecito</th>
        			      <th colspan="1">Data invio sollecito</th>
        			      <?php if($Search_IsLastEmitted){?>
        			      			<th colspan="1">Num. solleciti emessi</th>
        			      <?php }
        			            else{?>
            			      		<th colspan="1">Prog. sollecito</th>
        			      <?php }?>
        			      <th colspan="1">Stato sollecito</th>
			    <?php } ?>
        	</tr>
        	<?php foreach($a_FinalResults as $r_Registry){
        	        //***Raccolta dati del singolo atto***
            	    $Trespasser = trim($r_Registry['CompanyName']." ".$r_Registry['Surname']." ".$r_Registry['Name']);
            	    $Trespasser = trim(preg_replace('/\s\s+/', ' ', $Trespasser));
            	    $Trespasser = mb_strimwidth($Trespasser, 0, 44, "...");
            	    
            	    $TaxCode = isset($r_Registry['TaxCode']) ? $r_Registry['TaxCode'] : $r_Registry['VatCode'];
            	    
            	    $Address = trim($r_Registry['Address']);
            	    $Address = trim(preg_replace('/\s\s+/', ' ', $Address));
            	    $Address = mb_strimwidth($Address, 0, 45, "...");
            	    
            	    $NotificationDate = isset($r_Registry['NotificationDate']) ? DateOutDB($r_Registry['NotificationDate']) : "Da notif.";
            	    $NotificationResult = isset($r_Registry['ResultTitle']) ? $r_Registry['ResultTitle'] : "-";
            	    $DeliveryDate = isset($r_Registry['DeliveryDate']) ? DateOutDB($r_Registry['DeliveryDate']) : "-";
            	    $SendDate = isset($r_Registry['SendDate']) ? DateOutDB($r_Registry['SendDate']) : "-";
            	    $PrintDate = isset($r_Registry['PrintDate']) ? DateOutDB($r_Registry['PrintDate']) : "-";
            	    $FlowDate = isset($r_Registry['FlowDate']) ? DateOutDB($r_Registry['FlowDate']) : "-";
            	    $ArchiveDate = isset($r_Registry['ArchiveDate']) ? DateOutDB($r_Registry['ArchiveDate']) : "-";
            	    $ArchiveNote = isset($r_Registry['ArchiveNote']) ? $r_Registry['ArchiveNote'] : "-";
            	    $PreviousArchiveNote = isset($r_Registry['PreviousArchiveNote']) ? $r_Registry['PreviousArchiveNote'] : "-";
            	    
            	    $TrespasserId = $r_Registry['TrespasserId'];
            	    $ProtocolId = $r_Registry['ProtocolId'];
            	    $Code = $r_Registry['Code'];
            	    $VehiclePlate = $r_Registry['VehiclePlate'];
            	    $VehicleTitle = $r_Registry['VehicleTitle'];
            	    $FullArticle = $r_Registry['FullArticle'];
            	    $FineDate = DateOutDB($r_Registry['FineDate']);
            	    
            	    //Riempe l'array degli articoli
            	    $a_Articles[$FullArticle] = (isset($a_Articles[$FullArticle]) ? $a_Articles[$FullArticle] : 0) + 1;
            	    
            	    //******
            	    ?>
            		<tr>
            			<td colspan="1"><?= $n_count++; ?></td>
            			<td colspan="1"><?= $ProtocolId; ?></td>
            			<td colspan="1"><?= $TrespasserId; ?></td>
            			<td colspan="1"><?= $Code; ?></td>
            			<td colspan="1"><?= $Trespasser; ?></td>
            			<td colspan="1"><?= $TaxCode; ?></td>
            			<td colspan="1"><?= $VehiclePlate; ?></td>
            			<td colspan="1"><?= $VehicleTitle; ?></td>
            			<td colspan="1"><?= trim($FullArticle); ?></td>
            			<td colspan="1"><?= $Address; ?></td>
            			<td colspan="1"><?= $FineDate; ?></td>
            			<td colspan="1"><?= $NotificationDate; ?></td>
            			<td colspan="1"><?= $r_Registry[INDEX_PAYMENTSTATUS]; ?></td>
            			<td colspan="1"><?= NumberDisplay($r_Registry[INDEX_AMOUNTPAYED]); ?>€</td>
            			<td colspan="1"><?= NumberDisplay($r_Registry[INDEX_AMOUNTOWNED]); ?>€</td>
            			<?php if($n_TypeViolation < 3){ ?>
                			<td colspan="1"><?= $DeliveryDate; ?></td>
                			<td colspan="1"><?= $SendDate; ?></td>
                			<td colspan="1"><?= $r_Registry[INDEX_PREVIOUSPROTOCOL]; ?></td>
                			<?php if($FineArchive > 0){ ?>
                				<td colspan="1"><?= $NotificationResult; ?></td>
                				<td colspan="1"><?= $ArchiveDate; ?></td>
                				<td colspan="1"><?= $ArchiveNote." ".$PreviousArchiveNote; ?></td>
                			<?php }else{ ?>
                				<td colspan="1"><?= $NotificationResult; ?></td>
                			<?php }?>
            			<?php } 
            			      elseif($n_TypeViolation == 3){?>
                			      <td colspan="1"><?= $PrintDate; ?></td>
                			      <td colspan="1"><?= $FlowDate; ?></td>
                			      <td colspan="1"><?= $r_Registry[INDEX_REMINDERCOUNT]; ?></td>
                			      <td colspan="1"><?= $r_Registry[INDEX_REMINDERSTATUS]; ?></td>
            			<?php } ?>
                	</tr>
            	<?php 
            	    //Aggiunge la riga dei trasgressori addizionali
                	foreach ($r_Registry[INDEX_TRESPASSERS] as $r_AdditionalTrespasser){
                	    $AdditionalTrespasser = trim($r_AdditionalTrespasser['CompanyName']." ".$r_AdditionalTrespasser['Surname']." ".$r_AdditionalTrespasser['Name']);
                	    $AdditionalTrespasser = trim(preg_replace('/\s\s+/', ' ', $AdditionalTrespasser));
                	    $AdditionalTaxCode = isset($r_AdditionalTrespasser['TaxCode']) ? $r_AdditionalTrespasser['TaxCode'] : $r_AdditionalTrespasser['VatCode'];
                	    ?>
                	    <tr>
                	    	<td bgcolor="lightblue" colspan="1"><?= $n_count?></td>
                	    	<td bgcolor="lightblue" colspan="1"></td>
                			<td bgcolor="lightblue" colspan="1"><?= $TrespasserId; ?></td>
                			<td bgcolor="lightblue" colspan="1"><?= $Code; ?></td>
                			<td bgcolor="lightblue" colspan="1"><?= $AdditionalTrespasser; ?></td>
                			<td bgcolor="lightblue" colspan="1"><?= $AdditionalTaxCode; ?></td>
                			<td bgcolor="lightblue" colspan="15"></td>
                			<?php if($n_TypeViolation < 3){ ?>
                    			<td bgcolor="lightblue" colspan="1"></td>
                    			<?php if($FineArchive > 0){ ?>
                    				<td bgcolor="lightblue" colspan="1"><?= $NotificationResult; ?></td>
                    				<td bgcolor="lightblue" colspan="1"></td>
                    			<?php }else{ ?>
                    				<td bgcolor="lightblue" colspan="1"><?= $NotificationResult; ?></td>
                    			<?php } ?>
                			<?php } 
                			      elseif($n_TypeViolation == 3){?>
                			      		<td colspan="1"><?= $r_Registry[INDEX_REMINDERSTATUS]; ?></td>
                			      <?php } ?>
                	    </tr>
        	    <?php } ?>
        		<?php 
            	    //Aggiunge la riga della la comunicazione dati trasgressore per la decurtazione punti
                    if (!empty($r_Registry[INDEX_REDUCEDDATE])){?>
                    	<tr>
                    		<td bgcolor="lightgreen" colspan="1"><?= $n_count; ?></td>
                			<td bgcolor="lightgreen" colspan="1"></td>
                			<td bgcolor="lightgreen" colspan="22"><?= 'Trasmissione Dati Motorizzazione in data: '.DateOutDB($r_Registry[INDEX_REDUCEDDATE]); ?>€</td>
                			<?php if($n_TypeViolation < 3){ ?>
                    			<td bgcolor="lightgreen" colspan="1"></td>
                    			<?php if($FineArchive > 0){ ?>
                    				<td bgcolor="lightgreen" colspan="1"></td>
                    			<?php }else{ ?>
                    				<td bgcolor="lightgreen" colspan="1"></td>
                    			<?php } ?>
                			<?php } ?>
                    	</tr>
                <?php }?>
        	<?php } ?>
		</table>
		<table>                       <!-- ***TOTALI*** -->
			<tr></tr>
			<tr>
    			<td align="left" colspan="4"><b>1 - Totale generale atti:</b></td>
    			<td align="right"><?= ($n_Owned+$n_Partial+$n_OverPayed+$n_Payed) ?></td>
    			<td align="right"><?= NumberDisplay($GrandTotalOwned)?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>2 - Totale omessi:</b></td>
    			<td align="right"><?= $n_Owned ?></td>
    			<td align="right"><?= NumberDisplay($GrandTotalOmitted)?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>3 - Totale generale pagati:</b></td>
    			<td align="right"><?= ($n_Payed+$n_OverPayed+$n_Partial) ?></td>
    			<td align="right"><?= NumberDisplay($GrandTotalSettled+$GrandTotalCredit+$GrandTotalExceded+$GrandTotalPartial) ?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>Di cui:</b></td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>   3.1 - Totale saldati:</b></td>
    			<td align="right"><?= $n_Payed ?></td>
    			<td align="right"><?= NumberDisplay($GrandTotalSettled)?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>   3.2 - Totale a credito (saldato + eccedenza):</b></td>
    			<td align="right"><?= $n_OverPayed ?></td>
    			<td align="right"><?= NumberDisplay($GrandTotalCredit + $GrandTotalExceded)?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>   3.3 - Totale parziali pagamenti:</b></td>
    			<td align="right"><?= $n_Partial ?></td>
    			<td align="right"><?= NumberDisplay($GrandTotalPartial)?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>4 - Residuo parziali pagamenti (rif. 3.3):</b></td>
    			<td align="right"></td>
    			<td align="right"><?= NumberDisplay($GrandTotalResidual)?>€</td>
			</tr>
			<tr>
    			<td align="left" colspan="4"><b>5 - Totale eccedenza pagamenti a credito (rif. 3.2):</b></td>
    			<td align="right"></td>
    			<td align="right"><?= NumberDisplay($GrandTotalExceded) ?>€</td>
			</tr>
			<tr>
    			<td align="left" bgcolor="yellow" colspan="4"><b>Totale spese generale:</b></td>
    			<td align="right" bgcolor="yellow"></td>
    			<td align="right" bgcolor="yellow"><?= NumberDisplay($GrandTotalFee) ?>€</td>
			</tr>
			<tr></tr>
			<tr>
    			<td align="left" bgcolor="yellow" colspan="4"><b>NUMERO VERBALI SENZA SOLLECITO:</b></td>
    			<td align="right" bgcolor="yellow" colspan="2"><?= $n_FineWithoutReminder ?></td>
			</tr>
			<tr>
    			<td align="left" bgcolor="yellow" colspan="4"><b>NUMERO VERBALI CON SOLLECITO:</b></td>
    			<td align="right" bgcolor="yellow" colspan="2"><?= $n_FineWithReminder ?></td>
			</tr>
			<tr>
    			<td align="left" bgcolor="yellow" colspan="4"><b>DI CUI CON SOLL. EMESSO:</b></td>
    			<td align="right" bgcolor="yellow" colspan="2"><?= $n_FineWithReminderEmitted ?></td>
			</tr>
			<tr></tr>
			<tr>
    			<td align="left" bgcolor="yellow" colspan="4"><b>Percentuale di verbali pagati rispetto a quelli emessi:</b></td>
    			<td align="right" bgcolor="yellow" colspan="2"><?= round((($n_OverPayed+$n_Payed)/($n_Owned+$n_Partial+$n_OverPayed+$n_Payed))*100) ?>%</td>
			</tr>
			<tr></tr>
			<tr>
		    	<td align="left" bgcolor="yellow" colspan="1"><b>Articolo</b></td>
				<td align="left" bgcolor="yellow" colspan="1"><b>N.totale</b></td>
		    </tr>
			<?php 
			foreach ($a_Articles as $article => $number){?>
			    <tr>
			    	<td align="left" colspan="1"><b><?= $article ?></b></td>
    				<td align="right" colspan="1"><?= $number ?></td>
			    </tr>
			<?php }
			?>
		</table>
    	<?php
    	$table = ob_get_clean();
    	
    	//Scrive il report in formato xls sul file system
    	file_put_contents(PRINT_FOLDER.'/'.$FileName, "\xEF\xBB\xBF".$table);
    	break;
    }
}


$_SESSION['Documentation'] = PRINT_FOLDER_HTML.'/'.$FileName;


header("location: prn_registry.php" . $str_GET_Back_Page . "&btn_search=1&Search_TypePayment=" . $n_TypePayment . "&Search_ArticleId=" . $Search_ArticleId . "&FineArchive=" . $FineArchive . "&Search_TypeNotification=" . $n_TypeNotification. "&FineDispute=" . $FineDispute. "&Search_TypeViolation=".$n_TypeViolation."&Search_ForeignFineNotPayed=".$Search_ForeignFineNotPayed."&Search_TypeRule=".$Search_TypeRule);