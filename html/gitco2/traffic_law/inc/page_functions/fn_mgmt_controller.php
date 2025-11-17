<?php

function mgmtControllerWhere() {
    global $Search_Code;
    global $Search_Name;
    global $Search_FromDate;
    global $Search_ToDate;
    global $Search_Genre;
    
    $str_Where = "1=1 AND CityId='{$_SESSION['cityid']}'";
    
    if ($Search_Code > 0){
        $str_Where .= " AND Code=$Search_Code";
    }
    if ($Search_Name != ""){
        $str_Where .= " AND Name LIKE '%$Search_Name%'";
    }
    if ($Search_FromDate != ""){
        $str_Where .= " AND FromDate >= '".DateInDB($Search_FromDate)."'";
    }
    if ($Search_ToDate != ""){
        $str_Where .= " AND FromDate >= '".DateInDB($Search_ToDate)."'";
    }
    if ($Search_Genre != ""){
        $str_Where .= " AND Qualification LIKE '%$Search_Genre%'";
    }
    
    return $str_Where;
}

function mgmtControllerOrderBy() {
    global $Order_Code;
    global $Order_Name;
    global $Order_Type;
    global $Order_FromDate;
    global $Order_ToDate;
    
    $Order_Code = empty($Order_Code) ? 'desc' : $Order_Code;
    $Order_FromDate = empty($Order_FromDate) ? 'desc' : $Order_FromDate;
    $Order_ToDate = empty($Order_ToDate) ? 'desc' : $Order_ToDate;
    
    $str_Order = array();
    
    if($Order_Code == 'asc'){
        $str_Order[] = 'CAST(Code AS UNSIGNED) ASC';
    } else if($Order_Code == 'desc'){
        $str_Order[] = 'CAST(Code AS UNSIGNED) DESC';
    }
    if($Order_Name == 'asc'){
        $str_Order[] = 'Name ASC';
    } else if($Order_Name == 'desc'){
        $str_Order[] = 'Name DESC';
    }
    if($Order_Type == 'asc'){
        $str_Order[] = 'Qualification ASC';
    } else if($Order_Type == 'desc'){
        $str_Order[] = 'Qualification DESC';
    }
    if($Order_FromDate == 'asc'){
        $str_Order[] = 'FromDate ASC';
    } else if($Order_FromDate == 'desc'){
        $str_Order[] = 'FromDate DESC';
    }
    if($Order_ToDate == 'asc'){
        $str_Order[] = '-ToDate ASC';
    } else if($Order_ToDate == 'desc'){
        $str_Order[] = '-ToDate DESC';
    }
    
    return implode(',', $str_Order) ?: null;
}