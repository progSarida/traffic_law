<?php

class cls_help{

    function getVar( $varname )
    {
        /** GET_VAR
         * Ricezione delle variabili globali $_POST, $_GET e $_SESSION */
        if(isset($_POST[$varname]))
            return $_POST[$varname];
        else if(isset($_GET[$varname]))
            return $_GET[$varname];
        else
            return null;
    }

    function alert($message)
    {
        echo "<script>alert(\"".$message."\");</script>";
    }

    function ErrorAlert($msgType,$msgText){
        // $msgType success(verde), info(azzurro), warning(giallo), danger(rosso)
        echo "<div class='alert alert-".$msgType."'>".$msgText."</div>";
        die;
    }

    function crea_dir( $path )
    {
    	$folder = explode("/",$path);

    	$control_path = $folder[0];

    	for($l=1;$l<count($folder);$l++)
    	{
    		$control_path .= "/".$folder[$l];
    		if( is_dir( $control_path ) == false )
    		{
    			mkdir( $control_path );
    		}
    	}

    	return $path;
    }

    //CAMBIA IL FORMATO DELLA DATA AL FORMATO ITALIANO
    function toItalianDate($date) {

        $date_array = str_split($date);

        if(strlen($date) == 8)
        {
            $day = "".$date_array[0].$date_array[1];
            $month = "".$date_array[2].$date_array[3];
            $year = "".$date_array[4].$date_array[5].$date_array[6].$date_array[7];

            return $day."/".$month."/".$year;

        }
        else if(strlen($date) == 10)
        {
            if ( ($date_array[4] == '-' && $date_array[7] == '-') || ($date_array[4] == '/' && $date_array[7] == '/') )
            {
                if($date_array[0]==0)
                    return "";

                $day = "".$date_array[8].$date_array[9];
                $month = "".$date_array[5].$date_array[6];
                $year = "".$date_array[0].$date_array[1].$date_array[2].$date_array[3];

                return $day."/".$month."/".$year;
            }
            else if( ($date_array[2] == '-' && $date_array[5] == '-') || ($date_array[2] == '/' && $date_array[5] == '/') )
            {
                if($date_array[0].$date_array[1]== 0 || $date_array[0].$date_array[1]>31)
                    return "";

                $day = "".$date_array[0].$date_array[1];
                $month = "".$date_array[3].$date_array[4];
                $year = "".$date_array[6].$date_array[7].$date_array[8].$date_array[9];

                return $day."/".$month."/".$year;
            }
        }
        else
            return "";

    }

//CAMBIA IL FORMATO DELLA DATA AL FORMATO DB
    function toDbDate($date) {

        $date_array = str_split($date);

        if(strlen($date) == 8)
        {
            $day = "".$date_array[0].$date_array[1];
            $month = "".$date_array[2].$date_array[3];
            $year = "".$date_array[4].$date_array[5].$date_array[6].$date_array[7];

            return $year."-".$month."-".$day;
        }
        if(strlen($date) == 10)
        {
            if ( ($date_array[4] == '-' && $date_array[7] == '-') || ($date_array[4] == '/' && $date_array[7] == '/') )
            {
                if($date_array[0]==0)
                    return null;

                $day = "".$date_array[8].$date_array[9];
                $month = "".$date_array[5].$date_array[6];
                $year = "".$date_array[0].$date_array[1].$date_array[2].$date_array[3];

                return $year."-".$month."-".$day;
            }
            else if( ($date_array[2] == '-' && $date_array[5] == '-') || ($date_array[2] == '/' && $date_array[5] == '/') )
            {
                if($date_array[0].$date_array[1]== 0 || $date_array[0].$date_array[1]>31)
                    return null;

                $day = "".$date_array[0].$date_array[1];
                $month = "".$date_array[3].$date_array[4];
                $year = "".$date_array[6].$date_array[7].$date_array[8].$date_array[9];

                return $year."-".$month."-".$day;
            }
        }
        else
            return null;

    }

    function stringToFloat($number){
        return floatval(str_replace(',', '.', str_replace('.', '', $number)));
    }

    function floatToString($number){
        return number_format($number,2,",",".");
    }
}


?>
