<?php
define('USERACTIVITY_PREF_COL_ESCLUSE', 'QUERY_');
define('USERACTIVITY_EXTRACTTYPES', serialize(array(
    1 => array(
        'Name' => 'Atti registrati manualmente',
        'SubFilters' => array(
            'Search_FineType',
            'Search_CityId'
        ),
    ),
    2 => array(
        'Name' => 'Notifiche registrate manualmente',
        'SubFilters' => array(
            'Search_CityId'
        ),
    ),
    3 => array(
        'Name' => 'Tragressori registrati manualmente',
        'SubFilters' => array(
            'Search_CityId'
        ),
    ),
    4 => array(
        'Name' => 'Anagrafiche registrate e modificate manualmente',
        'SubFilters' => array(
            'Search_CityId'
        ),
    ),
    5 => array(
        'Name' => 'Atti archiviati (sola archiviazione)',
        'SubFilters' => array(
            'Search_CityId'
        ),
    ),
    6 => array(
        'Name' => 'Documenti inseriti',
        'SubFilters' => array(
            'Search_CityId'
        ),
    ),
    7 => array(
        'Name' => 'Comunicazioni 126bis inserite',
        'SubFilters' => array(
            'Search_CityId'
        ),
    ),
    8 => array(
        'Name' => 'Anomalie marca modello corrette',
        'SubFilters' => array(),
    ),
//     5 => array(
//         'Name' => 'Operazioni eseguite',
//         'SubFilters' => array(),
//     )
)));

define('USERACTIVITY_VIEWS', serialize(array(
    //Atti registrati manualmente
    1 => array(
        'View' => USERACTIVITY_FINE_REGISTERED,
        'Replace' => array(
            "@RegDate" => 'F.RegDate',
            "@CityId" => 'F.CityId',
            "@UserName" => 'F.UserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(1,1,2,1,2,1,1,1,1)
    ),
    //Notifiche registrate
    2 => array(
        'View' => USERACTIVITY_NOTIFICATION_REGISTERED,
        'Replace' => array(
            "@RegDate" => 'FN.RegDate',
            "@CityId" => 'F.CityId',
            "@UserName" => 'FN.UserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(1,2,1,3,1,1,1,1)
    ),
    //Trasgressori registrati
    3 => array(
        'View' => USERACTIVITY_TRESPASSER_REGISTERED,
        'Replace' => array(
            "@RegDate" => 'FT.RegDate',
            "@CityId" => 'F.CityId',
            "@UserName" => 'FT.UserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(1,2,1,3,1,1,1,1)
    ),
    //Anagrafiche registrate
    4 => array(
        'View' => USERACTIVITY_ANAG_REGISTERED,
        'Replace' => array(
            "@RegDate" => 'COALESCE(T.DataSourceDate, T.VersionDate)',
            "@CityId" => 'T.CustomerId',
            "@UserName" => 'T.UserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ReplaceUnionWhere' => array(
            'Modifiche' => array(
                "@RegDate" => 'TH.UpdateDataSourceDate',
                "@UserName" => 'TH.UpdateUserId',
            )
        ),
        'ColSizes' => array(1,6,1,1,1,1)
    ),
    
    5 => array(
        'View' => USERACTIVITY_ARCHIVED,
        'Replace' => array(
            "@RegDate" => 'FAR.RegDate',
            "@CityId" => 'F.CityId',
            "@UserName" => 'FAR.UserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(1,1,2,1,3,1,1,1)
    ),
    6 => array(
        'View' => USERACTIVITY_DOCUMENT,
        'Replace' => array(
            "@RegDate" => 'FD.VersionDate',
            "@CityId" => 'F.CityId',
            "@UserName" => 'FD.UserId',
            "@DocumentationTypes" => implode(',', unserialize(GENERIC_DOCUMENT_TYPES)),
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(2,1,2,1,2,1,1,1)
    ),
    7 => array(
        'View' => USERACTIVITY_COMMUNICATION_126BIS,
        'Replace' => array(
            "@RegDate" => 'FC.RegDate',
            "@CityId" => 'F.CityId',
            "@UserName" => 'FC.UserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(1,1,3,1,1,1,1,1,1,1,1)
    ),
    8 => array(
        'View' => USERACTIVITY_ANOMALY_BRANDMODEL,
        'Replace' => array(
            "@RegDate" => 'ABMH.UpdateDate',
            "@UserName" => 'ABMH.UpdateUserId',
            "@QUERY_RegDate" => 'QUERY_RegDate',
            "@QUERY_UserName" => 'QUERY_UserName',
        ),
        'DefaultOrderBy' => array(
            "QUERY_Id"
        ),
        'ColSizes' => array(1,1,1,2,2,1,1,1,1)
    ),
//     5 => array(
//         'View' => QUERY_LOG,
//     )
)));
define('USERACTIVITY_ORDER_OPTIONS', serialize(array(
    1 => array('Name' => 'Data reg.', 'Field' => '@QUERY_RegDate'),
    2 => array('Name' => 'Utente', 'Field' => '@QUERY_UserName'),
)));
define('USERACTIVITY_ORDER_TYPE', serialize(array(
    1 => array('Name' => 'Ascendente', 'Order' => 'ASC'),
    2 => array('Name' => 'Discendente', 'Order' => 'DESC'),
)));
define('USERACTIVITY_FINETYPES', serialize(array(
    1 => 'Rinotifiche',
    3 => 'Verbali',
    4 => 'Verbali contratto',
    5 => 'Verbali d\'ufficio'
)));

function prnUserActivityWhere() {
    global $Search_Type;
    global $Search_CityId;
    global $Search_FromDate;
    global $Search_ToDate;
    global $Search_UserName;
    global $Search_FineType;
    
    $a_ExtractTypes = unserialize(USERACTIVITY_EXTRACTTYPES);
    
    $str_Where = "1=1";
    
    if (in_array('Search_CityId', $a_ExtractTypes[$Search_Type]['SubFilters']) && $Search_CityId != ''){
        $str_Where .= " AND @CityId='$Search_CityId'";
    }
    if ($Search_FromDate != "") {
        $str_Where .= " AND DATE(@RegDate)>='".DateInDB($Search_FromDate)."'";
    }
    if ($Search_ToDate != "") {
        $str_Where .= " AND DATE(@RegDate)<='".DateInDB($Search_ToDate)."'";
    }
    if ($Search_UserName != "") {
        $str_Where .= " AND @UserName ='$Search_UserName'";
    }
    if (in_array('Search_FineType', $a_ExtractTypes[$Search_Type]['SubFilters']) && $Search_FineType > 0) {
        $str_Where .= " AND F.FineTypeId = $Search_FineType";
    }
    
    return $str_Where;
}

function prnUserActivityOrderBy() {
    global $Search_Type;
    global $Order_Name;
    global $Order_Type;
    
    $a_OrderOptions = unserialize(USERACTIVITY_ORDER_OPTIONS);
    $a_OrderType = unserialize(USERACTIVITY_ORDER_TYPE);
    $a_ExtractTypesViews = unserialize(USERACTIVITY_VIEWS);
    
    $a_Order = array("{$a_OrderOptions[$Order_Name]['Field']} {$a_OrderType[$Order_Type]['Order']}");
    
    //Se sono impostati dei campi di ordinamento di default li aggiunge alla
    //ORDER BY, usando l'ordinamento selezionato da interfaccia
    if($Search_Type > 0 && isset($a_ExtractTypesViews[$Search_Type]['DefaultOrderBy'])){
        foreach ($a_ExtractTypesViews[$Search_Type]['DefaultOrderBy'] as $orderColumn){
            $a_Order[] = "$orderColumn {$a_OrderType[$Order_Type]['Order']}";
        }
    }
    
    return implode(', ', $a_Order) ?: null;
}

function prnUserActivityUsedFilters(){
    $a_Filters = array();
    
    /** @var CLS_DB $rs */
    global $rs;
    global $Search_CityId;
    global $Search_FromDate;
    global $Search_ToDate;
    global $Search_UserName;
    global $Search_FineType;
    
    $a_FineTypes = unserialize(USERACTIVITY_FINETYPES);
    
    if ($Search_CityId != ''){
        $cityTitle = $rs->getArrayLine($rs->SelectQuery('SELECT Title FROM '.MAIN_DB.".City WHERE Id='{$Search_CityId}'"))['Title'] ?? '';
        $a_Filters['Ente'] = $cityTitle;
    }
    if ($Search_FromDate != "") {
        $a_Filters['Da data reg.'] = $Search_FromDate;
    }
    if ($Search_ToDate != "") {
        $a_Filters['A data reg.'] = $Search_ToDate;
    }
    if ($Search_UserName != "") {
        $a_Filters['Utente'] = $Search_UserName;
    }
    if ($Search_FineType > 0) {
        $a_Filters['Tipologia atto'] = $a_FineTypes[$Search_FineType];
    }
    
    return $a_Filters;
}

function pdfPrintPageHeader($pdf, $row, array $cellsWidth, $cellHeight){
    //Colonna N. riga
    $pdf->writeHTMLCell(pdfColumnSize($pdf, 1), $cellHeight, '', '' , 'N.', 1, 0, 0, true, 'L', true);
    
    foreach($row as $index => $fieldValue){
        $pdf->writeHTMLCell(pdfColumnSize($pdf, $cellsWidth[$index] ?? 12), $cellHeight, '', '' , str_replace(' | ', '<br>', $fieldValue), 1, !next($row) ? 1 : 0, 0, true, 'L', true);
    }
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

