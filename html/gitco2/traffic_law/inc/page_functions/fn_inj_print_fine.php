<?php
define('INJ_PRINT_FINE_DIVISOR', '|');

define('INJ_PRINT_FINE_NATIONALITY', serialize(array('N' => 'Nazionale', 'F' => 'Estera')));
define('INJ_PRINT_FINE_GENRE', serialize(array('P' => 'Persone fisiche', 'D' => 'Ditte')));
define('INJ_PRINT_FINE_FIGURES', serialize(array(1 => 'Una figura', 2 => 'Più figure')));
define('INJ_PRINT_FINE_ACTIONTYPES', serialize(array(0 => 'Includi', 1 => 'Escludi', 2 => 'Solo loro')));
define('INJ_PRINT_FINE_ANOMALYTYPES', serialize(array(
    1 => 'Senza data di notifica e con pagamento',
    2 => 'Senza data di notifica ad una delle parti',
    3 => 'Senza data di notifica ad una delle parti e con pagamento',
    4 => 'Verbali con ricorso',
//     5 => 'Ordinanza ingiunzione Prefettizia emanata oltre 180 giorni dal deposito del ricorso (con esito favorevole all’Ente)',
//     6 => 'Ordinanza ingiunzione Prefettizia notificata oltre 150 giorni dalla sua adozione (con esito favorevole all’Ente)',
//     7 => 'Compiuta giacenza senza CAD',
    8 => 'Data di notifica del verbale antecedente alla creazione del flusso',
    //9 => 'Notifiche a più soggetti tutte attive/regolari'
)));
define('INJ_PRINT_FINE_FINETYPES', serialize(array(
    1 => 'Preinserimento',
    2 => 'Preavviso',
    3 => 'Verbale normale',
    4 => 'Verbale contratto',
    5 => 'Verbale d\'ufficio'
)));

function injPrintFineWhereHaving() {
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $s_TypePlate;
    global $Search_Ref;
    global $Search_Violation;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_Plate;
    global $Search_VehicleType;
    global $Search_Genre;
    global $Search_Number;
    global $Search_Type;
    global $Search_Anomalies;
    global $Search_Trespasser;
    
    $a_AnomalyTypes = unserialize(INJ_PRINT_FINE_ANOMALYTYPES);
    $a_Nationality = unserialize(INJ_PRINT_FINE_NATIONALITY);
    $a_Genre = unserialize(INJ_PRINT_FINE_GENRE);
    $a_Figures = unserialize(INJ_PRINT_FINE_FIGURES);
    $a_ActionTypes = unserialize(INJ_PRINT_FINE_ACTIONTYPES);
    
    $return = array(
        'Where' => "1=1 AND CityId='{$_SESSION['cityid']}'",
        'Having' => '',
        'UsedFilters' => array()
    );
    
    if ($Search_FromProtocolYear != ''){
        $return['Where'] .= " AND F.ProtocolYear >= $Search_FromProtocolYear";
        $return['UsedFilters']['Da anno'] = $Search_FromProtocolYear;
    }
    if ($Search_ToProtocolYear != ''){
        $return['Where'] .= " AND F.ProtocolYear <= $Search_ToProtocolYear";
        $return['UsedFilters']['Ad anno'] = $Search_ToProtocolYear;
    }
    if ($Search_FromProtocolId != ""){
        $return['Where'] .= " AND F.ProtocolId >= $Search_FromProtocolId'";
        $return['UsedFilters']['Da cron.'] = $Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != ""){
        $return['Where'] .= " AND F.ProtocolId <= $Search_ToProtocolId'";
        $return['UsedFilters']['A cron.'] = $Search_ToProtocolId;
    }
    if ($Search_FromFineDate != ""){
        $return['Where'] .= " AND F.FineDate >= '".DateInDB($Search_FromFineDate)."'";
        $return['UsedFilters']['Da data accert.'] = $Search_FromFineDate;
    }
    if ($Search_ToFineDate != ""){
        $return['Where'] .= " AND F.FineDate <= '".DateInDB($Search_ToFineDate)."'";
        $return['UsedFilters']['A data accert.'] = $Search_ToFineDate;
    }
    if ($Search_Ref != ""){
        $return['Where'] .= " AND F.Code = '$Search_Ref'";
        $return['UsedFilters']['Riferimento'] = $Search_Ref;
    }
    if ($Search_Violation > 0){
        $return['Where'] .= " AND FA.ViolationTypeId = $Search_Violation";
        $return['UsedFilters']['Violazione'] = $Search_Violation;
    }
    if ($Search_FromNotificationDate != ""){
        $return['Having'] .= " AND DeliveryDate >= '".DateInDB($Search_FromNotificationDate)."'";
        $return['UsedFilters']['Da data notifica'] = $Search_FromNotificationDate;
    }
    if ($Search_ToNotificationDate != ""){
        $return['Having'] .= " AND DeliveryDate <= '".DateInDB($Search_ToNotificationDate)."'";
        $return['UsedFilters']['A data notifica'] = $Search_ToNotificationDate;
    }
    if ($Search_Plate != ""){
        $return['Where'] .= " AND F.VehiclePlate = '$Search_Plate'";
        $return['UsedFilters']['Targa'] = $Search_Plate;
    }
    if ($Search_VehicleType > 0){
        $return['Where'] .= " AND F.VehicleTypeId = $Search_VehicleType";
        $return['UsedFilters']['Tipo veicolo'] = $Search_ToProtocolYear;
    }
    if ($Search_Trespasser != ''){
        $return['Having'] .= " AND '$Search_Trespasser' IN(T.CompanyName, T.Surname, T.Name)";
        $return['UsedFilters']['Trasgressore'] = $Search_Trespasser;
    }

    if ($s_TypePlate != ''){
        switch($s_TypePlate){
            case 'F' : $return['Where'] .= " AND F.CountryId != 'Z000'"; break;
            case 'N' :
            default : $return['Where'] .= " AND F.CountryId = 'Z000'";
        }
        $return['UsedFilters']['Nazionalità'] = $a_Nationality[$s_TypePlate] ?? '';
    }
    if ($Search_Genre != ''){
        switch($Search_Genre){
            case 'D' : $return['Where'] .= " AND T.Genre = 'D'"; break;
            case 'P' : $return['Where'] .= " AND T.Genre != 'D'";
        }
        $return['UsedFilters']['Genere'] = $a_Genre[$Search_Genre] ?? $Search_Genre;
    }
    if ($Search_Number != ''){
        switch($Search_Number){
            case 1 : $return['Where'] .= " AND FT.TrespasserTypeId = 1"; break;
            case 2 : $return['Where'] .= " AND FT.TrespasserTypeId != 1";
        }
        $return['UsedFilters']['N. Figure'] = $a_Figures[$Search_Number] ?? $Search_Number;
    }
    if ($Search_Anomalies != ''){
        switch($Search_Anomalies){
            //Senza data di notifica e con pagamento
            case 1 :
                if($Search_Type == 1)       $return['Having'] .= " AND !(DeliveryDate IS NULL AND PaymentId IS NOT NULL)"; 
                else if($Search_Type == 2)  $return['Having'] .= " AND DeliveryDate IS NULL AND PaymentId IS NOT NULL"; 
                break;
            //Senza data di notifica ad una delle parti
            case 2 :
                if($Search_Type == 1)       $return['Having'] .= " AND !(COALESCE(DeliveryDate, '') REGEXP '^(^$\\|.*\\\|\\|\\\|.*\\|\\\|)$')";
                else if($Search_Type == 2)  $return['Having'] .= " AND COALESCE(DeliveryDate, '') REGEXP '^(^$\\|.*\\\|\\|\\\|.*\\|\\\|)$'"; 
                break;
            case 3 :
                if($Search_Type == 1)       $return['Having'] .= " AND !(COALESCE(DeliveryDate, '') REGEXP '^(^$\\|.*\\\|\\|\\\|.*\\|\\\|)$' AND PaymentId IS NOT NULL)";
                else if($Search_Type == 2)  $return['Having'] .= " AND COALESCE(DeliveryDate, '') REGEXP '^(^$\\|.*\\\|\\|\\\|.*\\|\\\|)$' AND PaymentId IS NOT NULL"; 
                break;
            case 4 :
                if($Search_Type == 1)       $return['Where'] .= " AND !(FD.DisputeId IS NOT NULL)";
                else if($Search_Type == 2)  $return['Where'] .= " AND FD.DisputeId IS NOT NULL";
                break;
            case 8 :
                if($Search_Type == 1)       $return['Having'] .= " AND !(COALESCE((FineNotificationDate < FlowDate), (FineNotificationDate IS NOT NULL AND FlowDate IS NULL)))";
                else if($Search_Type == 2)  $return['Having'] .= " AND COALESCE((FineNotificationDate < FlowDate), (FineNotificationDate IS NOT NULL AND FlowDate IS NULL))";
                break;
        }
        $return['UsedFilters']['Tipo anomalia'][] = $a_AnomalyTypes[$Search_Anomalies] ?? $Search_Anomalies;
        $return['UsedFilters']['Tipo anomalia'][] = $a_ActionTypes[$Search_Type] ?? '';
    }
    
    return $return;
}

function injPrintFineOrderBy() {
    global $Order_Code;
    global $Order_FineDate;
    global $Order_ProtocolId;
    
    $Order_FineDate = empty($Order_FineDate) ? 'desc' : $Order_FineDate;
    
    $return = array(
        'OrderBy' => null,
        'UsedOrders' => array()
    );
    $str_Order = array();
    
    if($Order_Code == 'asc'){
        $str_Order[] = 'F.Code ASC';
        $return['UsedOrders']['Riferimento'] = 'Ascendente';
    } else if($Order_Code == 'desc'){
        $str_Order[] = 'F.Code DESC';
        $return['UsedOrders']['Riferimento'] = 'Discendente';
    }
    if($Order_FineDate == 'asc'){
        $str_Order[] = 'F.FineDate ASC';
        $str_Order[] = 'F.FineTime ASC';
        $return['UsedOrders']['Data accert.'] = 'Ascendente';
    } else if($Order_FineDate == 'desc'){
        $str_Order[] = 'F.FineDate DESC';
        $str_Order[] = 'F.FineTime DESC';
        $return['UsedOrders']['Data accert.'] = 'Discendente';
    }
    if($Order_ProtocolId == 'asc'){
        $str_Order[] = 'F.ProtocolYear ASC';
        $str_Order[] = 'F.ProtocolId ASC';
        $return['UsedOrders']['Anno/Cron.'] = 'Ascendente';
    } else if($Order_ProtocolId == 'desc'){
        $str_Order[] = 'F.ProtocolYear DESC';
        $str_Order[] = 'F.ProtocolId DESC';
        $return['UsedOrders']['Anno/Cron.'] = 'Discendente';
    }
    
    $return['OrderBy'] = implode(',', $str_Order) ?: null;
    
    return $return;
}

function injPrintFineGroupResults(string $indexToUse, array $data, $divisor = INJ_PRINT_FINE_DIVISOR){
    $result = array();
    $indexes = explode($divisor, $data[$indexToUse] ?? '');
    
    $data = array_map(function($arr) use($indexes, $divisor){
        return array_pad(explode($divisor, $arr), count($indexes), '');
    }, 
    $data);

    foreach($indexes as $key => $value){
        $result[$value] = array_combine(array_keys($data), array_column($data, $key));
    }
    
    return $result;
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