<?php

function crea_riga( $pdf , $array_width , $array_value , $linea = "no" , $style=array() , $align = null , $height = 0 )
{
	/**
	 
		se $linea = "down" viene creata solo la linea al di sotto della riga
		se $linea = "up_down" vengono create entrambe le linee al di sopra e al di sotto della riga
		se $linea = "up" viene creata solo la linea al di sopra della riga
		se $linea = "no" non vengono create linee
				
	 */
	
	$tot = array_sum( $array_width );
	$margine = $pdf->getMargins();
	
	if( $linea == "up" || $linea == "up_down" ) 	$pdf->Line( $pdf->getX(),  $pdf->getY(), ( $tot + $margine['left'] ) ,  $pdf->getY(), $style ) ;
		
	$y = 0;
	for($k=0 ; $k < count($array_value)-1 ; $k++ )
	{
		if( $align == null ) 	$allinea = "L";
		else					$allinea = $align[$k];
		
		$pdf->startTransaction();
		$pdf->MultiCell( $array_width[$k] , $height, $array_value[$k] , 0 , $allinea , 0 , 1 , '' , '' , true );
		if( $pdf->getY() > $y ) 
		{
			$y = $pdf->getY();
		}
		$pdf->rollbackTransaction(true);

		$pdf->MultiCell( $array_width[$k] , $height, $array_value[$k] , 0 , $allinea , 0 , 0 , '' , '' , true );
	}
	
	if( $align == null ) 	$allinea = "L";
	else					$allinea = $align[count($array_value)-1];
	$pdf->MultiCell( $array_width[count($array_value)-1] , $height, $array_value[count($array_value)-1] , 0 , $allinea , 0 , 1 , '' , '' , true );
	if( $pdf->getY() > $y ) 
	{
		$y = $pdf->getY();
	}
		
	if( $linea == "down" || $linea == "up_down" ) $pdf->Line( $pdf->getX(), $y, ( $tot + $margine['left'] ) , $y, $style ) ;
	$pdf->setY($y);
	
	return $y;
	
}

function crea_linee ($pdf , $array_width , $y1 , $y2, $style , $orientation = "V" )
{
	if($orientation = "V")
	{
		$margine = $pdf->getMargins();
		$x = $margine['left'];
		for($k=0 ; $k < count($array_width)-1 ; $k++ )
		{
			$x += $array_width[$k];
			$pdf->Line( $x , $y1 , $x , $y2 , $style );
		}
		
	}
}

function SostituisciTestoTraGraffe (&$myTesto, $testoGraffe, $cosaMettere, $bold = NULL)
{
	$temp = $myTesto;
	
	$lunghGraffa = strlen ($testoGraffe);
	$posto = strpos($temp, $testoGraffe);
	
	if ($posto === false)
	{
		//alert ("Il campo $testoGraffe non è presente nel testo $temp");
		return;
	}

	$newTesto1 = substr ($temp, 0, $posto);
	$newTesto2 = substr ($temp, $posto+$lunghGraffa);

	$temp = $newTesto1;
	if ($bold == "B") $temp .= "<b>";
	$temp .= $cosaMettere;
	if ($bold == "B") $temp .= "</b>";
	$temp .= $newTesto2;
	$myTesto = $temp;
	
}

function SostituisciTestoFacoltativo ( &$testo , $array_variabili , $array_sostituzioni, $bold = null )
{
	
	for($i=0;$i<count($array_variabili);$i++)
	{
		$varLength = strlen ( $array_variabili[$i] );
		$posto = strpos( $testo, $array_variabili[$i]);
		
		if($posto >= 0)
		{
			$Testo1 = substr ($testo, 0, $posto);
			$Testo2 = substr ($testo, $posto+$varLength);
			
			$temp = $Testo1;
			if ($bold == "B") $temp .= "<b>";
			$temp .= $array_sostituzioni[$i];
			if ($bold == "B") $temp .= "</b>";
			$temp .= $Testo2;
			
			$testo = $temp;
		}
	}
		
}

function estraiVariabile ( $testo , $array_variabili )
{
	$posto = false;
	for($i=0;$i<count($array_variabili);$i++)
	{
		$posto = strpos( $testo, $array_variabili[$i]);
		
		if($posto !== false)
		{
			$variabile = $array_variabili[$i];
			break;
		}
	}
	
	if($posto===false)
		$variabile = "{VARMANUALE}";
	
	return $variabile;
}


?>