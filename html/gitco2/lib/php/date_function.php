<?php

//CALCOLA IL NUMERO DI GIORNI TRA DUE DATE
function calcola_giorni($data_ini,$data_fine)
{
	if(($data_ini=="//" || $data_ini==NULL) && ($data_fine=="//" or $data_fine==NULL)) {return 0; die;}

	$a_ini = substr($data_ini,6);
	$mese_ini = substr($data_ini,3,2);
	$giorno_ini = substr($data_ini,0,2);
	
	$a_fine = substr($data_fine,6);
	$mese_fine = substr($data_fine,3,2);
	$giorno_fine = substr($data_fine,0,2);
	
	if($a_ini > $a_fine)
	{
		return 0;
		die;
	}
	else if($a_ini == $a_fine)
	{
		if($mese_ini > $mese_fine)
		{
			return 0;
			die;
		}
		else if($mese_ini == $mese_fine)
		{
			if($giorno_ini >= $giorno_fine)
			{
				return 0;
				die;
			}
			else if($giorno_ini < $giorno_fine)
			{
				$giorno_tmp = $giorno_fine - $giorno_ini;
			}
		}
		else if($mese_ini < $mese_fine)
		{
			$g_ini = cal_days_in_month(0,$mese_ini,$a_ini);
			$g_ini_tmp = $g_ini-$giorno_ini;
			$giorni = 0;
			$mese_tmp = $mese_fine - $mese_ini;
			for($m=1; $m <= $mese_tmp-1; $m++)
			{
				$mese = $mese_ini + $m;
				if($mese < 13)
				{
					$giorni_me = cal_days_in_month(0,$mese,$a_ini);
					$giorni += $giorni_me;
				}
			}
					$giorno_tmp = $giorni + $giorno_fine + $g_ini_tmp;
		}
	}
	else if($a_fine > $a_ini)
	{
		$diff_anni = $a_fine - $a_ini;
		$conto_anni = 0;
		if($diff_anni > 1)
		{
			$mol=$diff_anni-1;
			$conto_anni=365*$mol;
		}
		
		$ultimo = "31/12/".$a_ini;
		$primo = "01/01/".$a_fine;
		$gio_ini = calcola_giorni($data_ini,$ultimo);
		$gio_fine = calcola_giorni($primo,$data_fine);
				
		$giorno_tmp = $gio_ini + $gio_fine + $conto_anni;
	}
	return $giorno_tmp;
}

//CAMBIA IL FORMATO DELLA DATA AL FORMATO ITALIANO 
function from_mysql_date($date) {

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
function to_mysql_date($date) {
	
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
				return "";
			
			$day = "".$date_array[8].$date_array[9];
			$month = "".$date_array[5].$date_array[6];
			$year = "".$date_array[0].$date_array[1].$date_array[2].$date_array[3];
				
			return $year."-".$month."-".$day;
		}
		else if( ($date_array[2] == '-' && $date_array[5] == '-') || ($date_array[2] == '/' && $date_array[5] == '/') )
		{
			if($date_array[0].$date_array[1]== 0 || $date_array[0].$date_array[1]>31)
				return "";
			
			$day = "".$date_array[0].$date_array[1];
			$month = "".$date_array[3].$date_array[4];
			$year = "".$date_array[6].$date_array[7].$date_array[8].$date_array[9];
				
			return $year."-".$month."-".$day;
		}
	}
	else
		return "";
		
}

//CALCOLA INTERESSI
function calcola_interessi ( $data_inizio, $data_fine, $importo , $perc = 10 , $mesi = 6 )
{	
	if( substr($data_inizio,2,1) != "/" )
		$data_inizio = from_mysql_date($data_inizio);
	if( substr($data_fine,2,1) != "/" )
		$data_fine = from_mysql_date($data_fine);
	
	if( $data_inizio!=null && $data_inizio!="00/00/0000" && $data_inizio!="" )
	{
		$importo = abs($importo);
	
		$percImp = $importo * $perc / 100;
	
		$num_perc = floor( calcola_giorni( $data_inizio , $data_fine ) / ($mesi*30) );

		$interessi = $percImp * $num_perc;
		$interessi = number_format( $interessi , 2 );
	}
	else 
	{
		$interessi = 0;
		$interessi = number_format( $interessi , 2 );
	}
	
	return $interessi;
}

//CALCOLA MESE SUCCESSIVO
function next_months ( $date , $num )
{
	if( substr($date,2,1) == "/" )
		$date = to_mysql_date($date);
	
	$date_array = explode("-",$date);
	if(checkdate($date_array[1], $date_array[2], $date_array[0]) == false)
		return false;
		
	$iniMonth = number_format($date_array[1]);
	$stringaDate = "";
	
	for($i=0;$i<$num;$i++)
	{
		$dateMonth = date('d/m/Y',strtotime(date("Y-m-d", strtotime($date)) . "+".($i+1)." month"));
		
		$day = substr($dateMonth,0,2);
		$month = substr($dateMonth,3,2);
		$year = substr($dateMonth,6,4);
		
	if(checkdate($month, $day, $year) == true)
	{
		
		$prevMonth = ($iniMonth+$i)%12;
		$nextMonth = ($prevMonth+1)%12;
		
		if($prevMonth == 0)
			$prevMonth = 12;
		if($nextMonth == 0)
			$nextMonth = 12;
			
		if($nextMonth == $month)
		{
			$stringaDate .= $dateMonth."*";
		}
		else 
		{						
			$query_date = $year."-".$nextMonth."-".$day;
			$rightDate = date('t/m/Y', strtotime($query_date));
			
			$stringaDate .= $rightDate."*";
		}
	}
	
	}

	$stringaDate = substr($stringaDate,0,strlen($stringaDate)-1);

	return $stringaDate;
}

?>