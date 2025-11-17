<?php
define("PRN_VALIDATEDADDRESS_VALIDATEDOPT_NO", 1);
define("PRN_VALIDATEDADDRESS_VALIDATEDOPT_SI", 2);
define("PRN_VALIDATEDADDRESS_VALIDATEDOPT", serialize(array(
    PRN_VALIDATEDADDRESS_VALIDATEDOPT_SI => "SI",
    PRN_VALIDATEDADDRESS_VALIDATEDOPT_NO => "NO"
)));

function prnValidatedAddressWhere() {
    global $Search_Plate;
    global $Search_Code;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_ValidatedAddress;
    
    $a_ResultIds_For_ValidatedAddress = unserialize(RESULTIDS_FOR_VALIDATEDADDRESS);
    $str_Where = "1=1 AND FN.ResultId IN(".implode(',', $a_ResultIds_For_ValidatedAddress).")";
    
    if ($Search_Plate != "") {
        $str_Where .= " AND F.VehiclePlate='$Search_Plate'";
    }
    if ($Search_Code != "") {
        $str_Where .= " AND F.Code LIKE '%$Search_Code%'";
    }
    if ($Search_FromFineDate != "") {
        $str_Where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_Where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
    }
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
    switch($Search_ValidatedAddress){
        case PRN_VALIDATEDADDRESS_VALIDATEDOPT_NO: $str_Where .= " AND FN.ValidatedAddress<=0"; break;
        case PRN_VALIDATEDADDRESS_VALIDATEDOPT_SI: $str_Where .= " AND FN.ValidatedAddress>0"; break;
    }
        
    return $str_Where;
}

function prnValidatedAddressUsedFilters(){
    $a_Filters = array();
    
    global $Search_Plate;
    global $Search_Code;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_ValidatedAddress;
    
    $a_ValidatedAddress_Opt = unserialize(PRN_VALIDATEDADDRESS_VALIDATEDOPT);
    
    if ($Search_Plate != "") {
        $a_Filters['Targa'] = $Search_Plate;
    }
    if ($Search_Code != "") {
        $a_Filters['Riferimento'] = $Search_Code;
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
    if ($Search_FromProtocolYear != "") {
        $a_Filters['Da anno'] = $Search_FromProtocolYear;
    }
    if ($Search_ToProtocolYear != "") {
        $a_Filters['Ad anno'] = $Search_ToProtocolYear;
    }
    if($Search_ValidatedAddress > 0){
        $a_Filters['Indirizzo validato'] = $a_ValidatedAddress_Opt[$Search_ValidatedAddress];
    }
    
    return $a_Filters;
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