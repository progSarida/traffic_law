<?php
function CheckValue($val, $t){
    if (isset($_REQUEST[$val])) {
        $val = $_REQUEST[$val];
        if(!is_numeric($val))
            switch($t) {
                case "n":
                    return 0;
                case "f":
                    return 0.00;
                case "b":
                    return false;
            }
        return $val;
    }
    switch($t){
        case "n":
            return 0;
        case "f":
            return 0.00;
        case "b":
            return false;
        default:
            return "";
    }
}




function CreateSelect($table, $where, $order, $selectname, $fieldid, $fieldvalue, $selectvalue,$required, $size=null, $class=null, $emptyoptiontxt=null){

    $rs= new CLS_DB();
    $rows = $rs->Select($table,$where,$order);

    $str_add_class = ($class!=null) ? " ".$class : "";

    $str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectname.'" ';
    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
    $str.= '>';

    if(!$required) $str.='<option>'.$emptyoptiontxt.'</option>';
    while($row = mysqli_fetch_array($rows)) {
        $str.='<option value="'.$row[$fieldid].'"';
        if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
        $str.='>'.StringOutDB($row[$fieldvalue]).'</option>';

    }
    $str.='</select>';

    return $str;
}

function CreateSelectConcat($query, $selectname, $fieldid, $fieldvalue, $selectvalue,$required,$size=null, $class=null, $disabled=false, $emptyoptiontxt=null){

    $rs= new CLS_DB();
    $rows = $rs->SelectQuery($query);

    $str_add_class = ($class!=null) ? " ".$class : "";

    $str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectname.'" ';
    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
    if($disabled)
        $str.= ' disabled';
    $str.= '>';
    if(!$required) $str.='<option>'.$emptyoptiontxt.'</option>';
    while($row = mysqli_fetch_array($rows)) {
        $str.='<option value="'.$row[$fieldid].'"';
        if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
        $str.='>'.$row[$fieldvalue].'</option>';

    }
    $str.='</select>';

    return $str;
}



function StringOutDB($s){

    return utf8_encode($s);

}

function getaddress($lat,$lng)
{
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&key='. MAP_KEY;
    $json = @file_get_contents($url);
    $data=json_decode($json);
    $status = $data->status;
    if($status=="OK") return $data->results[0]->address_components[1]->long_name;
    else
        return false;
}


function DateInDB($d){
    if(!$d)
        return null;
    if(strpos($d,"/")>0){
        $aD = explode('/',$d);
        $d = $aD[2]."-".$aD[1]."-".$aD[0];
    }
    return $d;
}
function b_ValidateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function ToFloat($num) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

    if (!$sep) {
        return floatval(preg_replace("/[^0-9]/", "", $num));
    }

    return floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
    );
}

function DateOutDB($d){
    if($d!=null || $d!=""){
        $aD = explode('-',$d);

        $d = $aD[2]."/".$aD[1]."/".$aD[0];
    }

    return $d;
}
function TimeOutDB($t){
    $aT = explode(':',$t);

    $t = $aT[0].":".$aT[1];

    return $t;

}