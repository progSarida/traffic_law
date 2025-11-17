<?php
define('STAT_FINE_COLLECTION_MONTHS', serialize(array(
    '01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo',
    '04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno',
    '07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre',
    '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre',
)));

define('STAT_FINE_COLLECTION_VIOLATIONTYPE', serialize(array(
    1 => 'Tutte', 
    2 => 'Velocità (art. 142/7-8-9 e 9-bis)', 
    3 => 'Altri articoli'
)));

define('STAT_FINE_COLLECTION_VIOLATIONTYPE_FILENAME_SUFFIX', serialize(array(
    1 => 'TUTTE',
    2 => 'VEL',
    3 => 'ALTRO'
)));

function statFineCollectionWhere() {
    global $Search_Type;
    
    $str_Where = "1=1";
    
    if($Search_Type == 2) {
        $str_Where .= " AND A.ViolationTypeId = 2 AND A.Article=142 and A.Paragraph in('7','8','9') AND COALESCE(TRIM(A.Letter), '') in('bis','')";
    } else if($Search_Type == 3) {
        $str_Where .= " AND NOT(A.ViolationTypeId = 2 AND A.Article=142 and A.Paragraph in('7','8','9') AND COALESCE(TRIM(A.Letter), '') in('bis',''))";
    }
    
    return $str_Where;
}

function formatCellValue($value){
    return ($value >= 0 ? '+' : '').number_format(round($value, 2), 2, ',', '.').' '.htmlentities('€');
}
