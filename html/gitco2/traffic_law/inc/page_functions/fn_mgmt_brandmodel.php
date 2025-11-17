<?php
function mgmtAnomalyBrandModelWhere() {
    global $Search_Date;
    global $Search_Brand;
    global $Search_Model;
    global $Search_VehicleType;
    
    $str_Where = "1=1";
    
    if($Search_VehicleType > 0){
        $str_Where .= " AND ABM.VehicleTypeId=$Search_VehicleType";
    }
    if($Search_Date != ""){
        $str_Where .= " AND ABM.DataSourceDate='".DateInDB($Search_Date)."'";
    }
    if($Search_Brand != ""){
        $str_Where .= " AND ABM.Brand LIKE '".$Search_Brand."%'";
    }
    if($Search_Model != ""){
        $str_Where .= " AND ABM.Model LIKE '".$Search_Model."%'";
    }
    
    return $str_Where;
}

function mgmtAnomalyBrandModelCanConfirm($r_AnomalyBrandModel){
    return
    !empty($r_AnomalyBrandModel['Model']) &&
    !empty($r_AnomalyBrandModel['Brand']) &&
    $r_AnomalyBrandModel['VehicleTypeId'] > 0 &&
    empty($r_AnomalyBrandModel['CorrectModel']) &&
    empty($r_AnomalyBrandModel['CorrectBrand']) &&
    $r_AnomalyBrandModel['CorrectVehicleTypeId'] == 0;
}

function mgmtAnomalyBrandModelOrderBy() {
    global $Order_Date;
    global $Order_Brand;
    global $Order_Model;
    global $Order_Type;
    
    $Order_Date = empty($Order_Date) ? 'desc' : $Order_Date;
    $Order_Brand = empty($Order_Brand) ? 'asc' : $Order_Brand;
    $Order_Model = empty($Order_Model) ? 'asc' : $Order_Model;
    $Order_Type = empty($Order_Type) ? 'asc' : $Order_Type;
    
    $str_Order = array();
    
    if($Order_Type == 'asc'){
        $str_Order[] = 'VT.TitleIta ASC';
    } else if($Order_Type == 'desc'){
        $str_Order[] = 'VT.TitleIta DESC';
    }
    if($Order_Date == 'asc'){
        $str_Order[] = 'ABM.DataSourceDate ASC';
    } else if($Order_Date == 'desc'){
        $str_Order[] = 'ABM.DataSourceDate DESC';
    }
    if($Order_Brand == 'asc'){
        $str_Order[] = 'ABM.Brand ASC';
    } else if($Order_Brand == 'desc'){
        $str_Order[] = 'ABM.Brand DESC';
    }
    if($Order_Model == 'asc'){
        $str_Order[] = 'ABM.Model ASC';
    } else if($Order_Model == 'desc'){
        $str_Order[] = 'ABM.Model DESC';
    }
    
    return implode(',', $str_Order) ?: null;
}