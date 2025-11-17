<?php
define('MGMT_INSTALLMENTS_ACTION_PRINTPDF', "PDF");
define('MGMT_INSTALLMENTS_ACTION_PRINTXLS', "XLS");

define("INDEX_STATUS_CREATED", 1);
define("INDEX_STATUS_REQUEST_PRINTED", 2);
define("INDEX_STATUS_RESULT_NEGATIVE", 3);
define("INDEX_STATUS_RESULT_POSITIVE", 4);
define("INDEX_STATUS_RESULT_PRINTED", 5);
define("INDEX_STATUS_BILL_PRINTED", 6);

define('MGMT_INSTALLMENTS_STATUS_OPTIONS', serialize(array(
    INDEX_STATUS_CREATED => 'Rateizzazione creata',
    INDEX_STATUS_REQUEST_PRINTED => 'Richiesta stampata e in attesa di esito',
    INDEX_STATUS_RESULT_NEGATIVE => 'Esito richiesta: respinta',
    INDEX_STATUS_RESULT_POSITIVE => 'Esito richiesta: accolta',
    INDEX_STATUS_RESULT_PRINTED => 'Esito stampato',
    INDEX_STATUS_BILL_PRINTED => 'Bollettini stampati',
)));

define('MGMT_INSTALLMENTS_STANDARD_RADIO', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));

function mgmtInstallmentsWhere() {
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_Trespasser;
    global $Search_Name;
    global $Search_Position;
    global $Search_Definitive;
    global $Search_Status;
    
    $str_Where = "1=1";
    
    if ($Search_FromProtocolId != "") {
        $str_Where .= " AND F.ProtocolId>=$Search_FromProtocolId";
    }
    if ($Search_ToProtocolId != "") {
        $str_Where .= " AND F.ProtocolId<=$Search_ToProtocolId";
    }
    if ($Search_FromProtocolYear != "") {
        $str_Where .= " AND F.ProtocolYear>=$Search_FromProtocolYear";
    }
    if ($Search_ToProtocolYear != "") {
        $str_Where .= " AND F.ProtocolYear<=$Search_ToProtocolYear";
    }
    if ($Search_Trespasser != "") {
        $str_Where .= " AND TrespasserFullName LIKE '%$Search_Trespasser%'";
    }
    if ($Search_Name != "") {
        $str_Where .= " AND PR.RateName LIKE '%$Search_Name%'";
    }
    if ($Search_Position != "") {
        $str_Where .= " AND PR.Position LIKE '%$Search_Position%'";
    }
    switch($Search_Definitive){
        case 0 : $str_Where .= " AND PR.StatusRateId=".RATEIZZAZIONE_APERTA; break;
        case 1 : break;
        case 2 : $str_Where .= " AND PR.StatusRateId=".RATEIZZAZIONE_CHIUSA; break;
    }
    switch($Search_Status){
        case INDEX_STATUS_CREATED:                  break;
        case INDEX_STATUS_REQUEST_PRINTED:          $str_Where .= " AND PR.RequestStatusId > 0 AND PR.RequestOutcome IS NULL"; break;
        case INDEX_STATUS_RESULT_NEGATIVE:          $str_Where .= " AND PR.RequestStatusId > 0 AND PR.RequestOutcome = 0"; break;
        case INDEX_STATUS_RESULT_POSITIVE:          $str_Where .= " AND PR.RequestStatusId > 0 AND PR.RequestOutcome = 1"; break;
        case INDEX_STATUS_RESULT_PRINTED:           $str_Where .= " AND PR.ResponseStatusId = 1"; break;
        case INDEX_STATUS_BILL_PRINTED:             $str_Where .= " AND PR.BillStatusId = 1"; break;
    }
    
    return $str_Where;
}

function mgmtInstallmentsUsedFilters(){
    $a_Filters = array();
    
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_Trespasser;
    global $Search_Name;
    global $Search_Position;
    global $Search_Definitive;
    global $Search_Status;
    
    $a_StandardRadioOpt = unserialize(MGMT_INSTALLMENTS_STANDARD_RADIO);
    $a_StatusOpt = unserialize(MGMT_INSTALLMENTS_STATUS_OPTIONS);
    
    if ($Search_FromProtocolId != "") {
        $a_Filters['Da cron.'] = $Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != "") {
        $a_Filters['A cron.'] = $Search_ToProtocolId;
    }
    if ($Search_FromProtocolYear != "") {
        $a_Filters['Da anno'] = $Search_FromProtocolYear;
    }
    if ($Search_ToProtocolYear != "") {
        $a_Filters['Ad anno'] = $Search_ToProtocolYear;
    }
    if ($Search_Trespasser != "") {
        $a_Filters['Trasgressore'] = $Search_Trespasser;
    }
    if ($Search_Name != "") {
        $a_Filters['Nominativo'] = $Search_Name;
    }
    if ($Search_Position != "") {
        $a_Filters['Posizione'] = $Search_Position;
    }
    
    $a_Filters['Rateizzazioni chiuse'] = $a_StandardRadioOpt[$Search_Definitive] ?? "";
    $a_Filters['Stato'] = $a_StatusOpt[$Search_Status] ?? "";
    
    return $a_Filters;
}

function mgmtInstallmentsPostProcess($a_Results){
//     $a_Return = array();
//     foreach($a_Results as $record){
//         $a_Return[$record['FineId']][] = $record;
//     }
//     return array_values($a_Return);
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
