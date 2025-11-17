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
?>