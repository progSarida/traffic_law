<?php
define('FRM_REMINDERLIST_ACTION_PRINTPDF', "PDF");
define('FRM_REMINDERLIST_ACTION_PRINTXLS', "XLS");

define("INDEX_PAYMENT_OMITTED", 1);
define("INDEX_PAYMENT_PARTIAL", 2);
define("INDEX_PAYMENT_PAYED", 3);

define("INDEX_OPERATION_LIST_CREATED", 1);
define("INDEX_OPERATION_LIST_EMITTED", 2);

define('FRM_REMINDERLIST_PAGES', serialize(array(
    INDEX_OPERATION_LIST_CREATED => 'frm_create_printlist_reminder.php',
    INDEX_OPERATION_LIST_EMITTED => 'frm_reminderlist.php'
)));
define('FRM_REMINDERLIST_PRINTTYPE', serialize(array(
    INDEX_OPERATION_LIST_CREATED => 'Provv.',
    INDEX_OPERATION_LIST_EMITTED => 'Inv.'
)));

define("INDEX_NATIONALITY_NATIONAL", 'N');
define("INDEX_NATIONALITY_FOREIGN", 'F');

define("INDEX_EXCLUSIVE", 1);
define("INDEX_INCLUSIVE", 2);
define("INDEX_STRICT", 3);

define('FRM_REMINDERLIST_NATIONALITY', serialize(array(
    INDEX_NATIONALITY_NATIONAL => 'Italiana', 
    INDEX_NATIONALITY_FOREIGN => 'Estera'
)));

define('FRM_REMINDERLIST_PAYMENT_OPTIONS', serialize(array(
    INDEX_PAYMENT_OMITTED => 'Omessi',
    INDEX_PAYMENT_PARTIAL => 'Parziali',
    INDEX_PAYMENT_PAYED => 'Pagati',
)));

define('FRM_REMINDERLIST_ORDER_OPTIONS', serialize(array(
    1 => array('Name' => 'Cronologico', 'Order' => 'F.ProtocolId ASC'),
    2 => array('Name' => 'Data verbale', 'Order' => 'F.FineDate ASC'),
    3 => array('Name' => 'Data notifica', 'Order' => 'FN.NotificationDate ASC'),
    4 => array('Name' => 'Trasgressore', 'Order' => "COALESCE(NULLIF(T.CompanyName, ''), T.Surname) ASC"),
)));

define('FRM_REMINDERLIST_HASDOCUMENT_OPTIONS', serialize(array(
    INDEX_EXCLUSIVE => 'Escludi',
    INDEX_INCLUSIVE => 'Includi',
    INDEX_STRICT => 'Solo loro',
)));

function frmReminderListWhere($operation = INDEX_OPERATION_LIST_EMITTED) {
    global $s_TypePlate;
    global $Search_Status;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromId;
    global $Search_ToId;
    global $Search_Date;
    global $Search_HasDocumentation;
    global $Search_IsLastEmitted;
    global $Search_Dispute;
    
    switch ($operation){
        case INDEX_OPERATION_LIST_EMITTED: {
            $str_Where = "COALESCE(FR.Documentation,'') != '' AND FR.FlowDate IS NOT NULL AND F.ReminderDate IS NOT NULL";
            
            switch($Search_Status){
                case INDEX_PAYMENT_OMITTED: $str_Where.= " AND (F.StatusTypeId=27 OR (F.StatusTypeId=40 AND FP.Id IS NULL))"; break;
                case INDEX_PAYMENT_PARTIAL: $str_Where.= " AND (F.StatusTypeId=28 OR (F.StatusTypeId=40 AND FP.Id IS NOT NULL))"; break;
                case INDEX_PAYMENT_PAYED: $str_Where.= " AND F.StatusTypeId=30"; break;
                default: $str_Where.= " AND F.StatusTypeId IN(27,28,30,40)";
            }
            break;
        }
        case INDEX_OPERATION_LIST_CREATED: {
            $str_Where = "F.ReminderDate IS NOT NULL";
            
            switch($Search_Status){
                case INDEX_PAYMENT_OMITTED: $str_Where.= " AND F.StatusTypeId=27"; break;
                case INDEX_PAYMENT_PARTIAL: $str_Where.= " AND F.StatusTypeId=28"; break;
                default: $str_Where.= " AND F.StatusTypeId IN(27,28)";
            }
            switch ($Search_HasDocumentation){
                case INDEX_EXCLUSIVE: $str_Where .= " AND COALESCE(FR.Documentation,'') = ''"; break;
                case INDEX_INCLUSIVE: break;
                case INDEX_STRICT: $str_Where .= " AND COALESCE(FR.Documentation,'') != ''"; break;
            }
            break;
        }
    }
    
    switch($s_TypePlate){
        case INDEX_NATIONALITY_NATIONAL: $str_Where .= " AND COALESCE(T.CountryId,F.CountryId) = 'Z000'"; break;
        case INDEX_NATIONALITY_FOREIGN: $str_Where .= " AND COALESCE(T.CountryId,F.CountryId) != 'Z000'"; break;
    }
    
    if ($Search_FromFineDate != "") {
        $str_Where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_Where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
    }
    if ($Search_FromNotificationDate != "") {
        $str_Where .= " AND FN.NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
    }
    if ($Search_ToNotificationDate != "") {
        $str_Where .= " AND FN.NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";
    }
    if ($Search_FromProtocolId != "") {
        $str_Where .= " AND F.ProtocolId>=".$Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != "") {
        $str_Where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
    }
    if ($Search_FromId != "") {
        $str_Where .= " AND FR.Id>=".$Search_FromId;
    }
    if ($Search_ToId != "") {
        $str_Where .= " AND FR.Id<=".$Search_ToId;
    }
    if ($Search_Date != "") {
        $str_Where .= " AND F.ProcessingPaymentDateTime='".DateInDB($Search_Date)."'";
    }
    if ($Search_IsLastEmitted > 0){
        $str_Where .= " AND F.ReminderDate = FR.PrintDate";
    }
    if ($Search_Dispute > 0){
        $str_Where .= " AND DisputeStatusId IN(".RICORSO_INAMMISSIBILE.",".RICORSO_RESPINTO.")";
    }
    
    return $str_Where;
}

function frmReminderListUsedFilters($operation){
    $a_Filters = array();
    
    global $s_TypePlate;
    global $Search_Status;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromId;
    global $Search_ToId;
    global $Search_Date;
    global $Search_HasDocumentation;
    global $Search_IsLastEmitted;
    global $Search_Dispute;
    
    switch ($operation){
        case INDEX_OPERATION_LIST_EMITTED: {
            break;
        }
        case INDEX_OPERATION_LIST_CREATED: {
            $a_Filters['Con doc. esistente'] = unserialize(FRM_REMINDERLIST_HASDOCUMENT_OPTIONS)[$Search_HasDocumentation] ?? '';
            break;
        }
    }
    
    if ($Search_FromFineDate != "") {
        $a_Filters['Da data violazione'] = $Search_FromFineDate;
    }
    if ($Search_ToFineDate != "") {
        $a_Filters['A data violazione'] = $Search_ToFineDate;
    }
    if ($Search_FromProtocolId != "") {
        $a_Filters['Da cron.'] = $Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != "") {
        $a_Filters['A cron.'] = $Search_ToProtocolId;
    }
    if ($Search_FromNotificationDate != "") {
        $a_Filters['Da data notifica'] = $Search_FromNotificationDate;
    }
    if ($Search_ToNotificationDate != "") {
        $a_Filters['A data violazione'] = $Search_ToNotificationDate;
    }
    if ($Search_FromId != "") {
        $a_Filters['Da sollecito'] = $Search_FromId;
    }
    if ($Search_ToId != "") {
        $a_Filters['A sollecito'] = $Search_ToId;
    }
    if ($Search_Date != "") {
        $a_Filters['Data elaborazione solleciti'] = $Search_Date;
    }
    
    $a_Filters['Solo ultimi emessi'] = $Search_IsLastEmitted > 0 ? "SI" : "NO";
    $a_Filters['Presenza ricorso'] = $Search_Dispute > 0 ? "SI" : "NO";
    $a_Filters['Stato pagamento'] = unserialize(FRM_REMINDERLIST_PAYMENT_OPTIONS)[$Search_Status] ?? "Tutti";
    $a_Filters['Nazionalità'] = unserialize(FRM_REMINDERLIST_NATIONALITY)[$s_TypePlate] ?? '';
    
    return $a_Filters;
}

function frmReminderListOrderBy() {
    global $Order_Type;
    
    $a_OrderOptions = unserialize(FRM_REMINDERLIST_ORDER_OPTIONS);
    
    return $a_OrderOptions[$Order_Type]['Order'] ?? null;
}

function pdfColumnSize($pdf, int $col){
    $max = 12;
    $min = 1;
    
    $dimensions = [
        //unità di default (PDF_UNIT) in millimetri
        "margins"   => $pdf->GetMargins(),
        "width"     => $pdf->getPageWidth(),
    ];
    $singleCol = round(($dimensions["width"] - ($dimensions["margins"]["left"] + $dimensions["margins"]["right"])) / $max);
    return $singleCol * ($col > $max ? $max : ($col < $min ? $min : $col));
}

//Intestazione elenco solleciti
/**
 * @desc intestazione elenco solleciti
 * **/
function frmReminderListNewPDFPage($pdf, $operation, $year, $city){
    //Nuova pagina//////////////////////////////////////////////////////////////////////////////
    $pdf->Header = false;
    $pdf->SetMargins(10,10,10);
    $pdf->AddPage('L', '');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTML('<h2>COMUNE DI '.strtoupper($city).' - GESTIONE ANNO '.$year.'</h2>', true, false, true, false, '');
    $pdf->LN(2);
    
    //Celle header//////////////////////////////////////////////////////////////////////////////
    $pdf->SetFont('helvetica', 'B', 8);
    //Prima riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(275, 0, 'Trasgressore', 1, '', 0, 0, '', '', true);
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Seconda riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(80, 0, 'Data definizione', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(30, 0, 'Anno', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'San.Am.Dov', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Dovute', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Mag.Dov.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Solle.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Tot. Dovuto', 1, 'R', 0, 0, '', '', true);
    //Cella destra di 4 righe
    //Altezza della cella grande di destra
    $pdf->setCellPadding(8.15);
    $pdf->MultiCell(40, 0, 'Ulteriori Dati', 1, 'C', 0, 0, '', '', true);
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Terza riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(60, 0, 'N.Cronol+Targa', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Data versamento', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Stamp.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'San.Am.Pag', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Pagate', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Mag.Pag.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Sem.Magg.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Tot. Pagato', 1, 'R', 0, 0, '', '', true);
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Quarta riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(60, 0, 'Data Verb. - Data Not.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Dettaglio Pag.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, '', 0, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Data Calc.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Dettag. Ricorso', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, '', 0, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'G.Agg.DataDef.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Differenza', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Quinta riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    switch ($operation){
        case INDEX_OPERATION_LIST_EMITTED: {
            $pdf->MultiCell(60, 0, 'Data elaborazione sollecito', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(60, 0, 'Data stampa sollecito', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(60, 0, 'Data creazione flusso di stampa', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(55, 0, 'Data invio flusso', 1, '', 0, 0, '', '', true);
            break;
        }
        case INDEX_OPERATION_LIST_CREATED: {
            $pdf->MultiCell(235, 0, 'Data elaborazione sollecito', 1, '', 0, 0, '', '', true);
            break;
        }
    }
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->LN(3);
    //////////////////////////////////////////////////////////////////////////////
}

//Sommario elenco solleciti
/**
 * @desc sommario elenco solleciti
 * **/
function frmReminderListPDFSummary($pdf,$RownNumber,$GrandTotalAmount,$GrandNotificationFees,$GrandPercentualAmount,$GrandReminderFees,
    $GrandOriginalAmount,$PrintDate,$GrandPayedAmountFee,$GrandPayedFees,$GrandPayedPercentual,$GrandTotalAmountPayed,
    $GrandDifference,$GrandSemester){
        //Celle footer//////////////////////////////////////////////////////////////////////////////
        $pdf->setCellHeightRatio(1.5);
        
        $pdf->SetFont('helvetica', 'B', 8);
        
        $pdf->LN(4);
        
        $pdf->MultiCell(110, 0, 'NUMERO TOTALE ATTI: '.$RownNumber, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandTotalAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandNotificationFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandPercentualAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandReminderFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandOriginalAmount, 2, ',', '.'), 1, 'R', 0, 1, '', '', true);
        
        $pdf->MultiCell(110, 0, 'Stampa del '.$PrintDate, 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandPayedAmountFee, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandPayedFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandPayedPercentual, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $GrandSemester, 1, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandTotalAmountPayed, 2, ',', '.'), 1, 'R', 0, 1, '', '', true);
        
        $pdf->MultiCell(210, 0, '', 0, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($GrandDifference, 2, ',', '.'), 1, 'R', 0, 1, '', '', true);
}

//Restituisce il vettore dei solleciti emessi ordinato in base al numero di flusso con un numero progressivo in base ai solleciti generati dallo stesso verbale
function orderEmittedRemindersProgressive($rs_Reminder)
    {
        global $rs;
        $a_Data = array();
        while($r_Reminder = $rs->getArrayLine($rs_Reminder)){
            $a_Data[] = $r_Reminder;
            }
        $ascendingFlowNumberReminders = $a_Data;
        usort($ascendingFlowNumberReminders, "sortBySendDate"); //...ordinato in base al numero di flusso
        //NB Non è possibile toccare l'ordinamento dell'array originale perchè il suo ordinamento è definito in modo variabile dall'utente tramite un filtro esplicito
        assignGrowingNumber($ascendingFlowNumberReminders);                //Assegno il numero progressivo ai singoli solleciti
        //Creo un vettore con lista idSollecito => progressivo, da utilizzare come indice di assegnazione
        $reminderAscendingList = array_column($ascendingFlowNumberReminders, "Prog", "FineReminderId");
        //Assegno il progressivo alle righe del vettore originale passato per riferimento
        for($i = 0; $i < count($a_Data); $i++)
        {
            $a_Data[$i]["Prog"] = $reminderAscendingList[$a_Data[$i]["FineReminderId"]];
        }
        
        return $a_Data;
    }

//Ordina i risultati in ordine crescente in base alla data di invio del flusso (da usare con la funzione usort)
//NB Funzione legata ad "orderEmittedRemindersProgressive"
function sortBySendDate($a,$b){
    if($a['SendDate'] > $b['SendDate']){
        return 1;
    }
    elseif($a['SendDate'] == $b['SendDate']){
        return 0;
    }
    else {
        return -1;
    }
}

//Assegna un progressivo alle righe dei solleciti emessi in base al numero di solleciti legati allo stesso verbale
//NB Funzione legata ad "orderEmittedRemindersProgressive"
function assignGrowingNumber(&$a_Dati)
    {
    $counters = array();
    for($i = 0; $i < count($a_Dati); $i++)
        {
        //Se c'è già una occorrenza allora incrementa
        if(array_key_exists($a_Dati[$i]["FineId"],$counters)){
            $counters[$a_Dati[$i]["FineId"]]++;
            $a_Dati[$i]["Prog"] = $counters[$a_Dati[$i]["FineId"]];
            }
        //Sennò crea una nuova occorrenza che parte da 1
        else if(!array_key_exists($a_Dati[$i]["FineId"],$counters)){
            $counters[$a_Dati[$i]["FineId"]] = 1;
            $a_Dati[$i]["Prog"] = $counters[$a_Dati[$i]["FineId"]];
            }
        }
    }
