<?php

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

function mostra_file_path ( $path )
{
	return substr( $path , strpos( $path , "/archivio/" ));
}

function crea_file_rar ($nome_file , $path , $elimina_originale = 1 , $nome_rar = null , $estensione = ".txt")  // nome file senza estensione, $path percorso della cartella di salvataggio dei file 
{
	if($nome_rar == null)	$nome_rar = $nome_file;
	
	$file = $nome_file . $estensione;
	$file_rar = $nome_rar . ".rar";

	$cwd = getcwd();
	
	if (is_dir("C:/Programmi/WinRAR/")){ $percorsoWinRar = "C:/Programmi/WinRAR/";}
	else if (is_dir("C:/Program Files (x86)/WinRAR/")){ $percorsoWinRar = "C:/Program Files (x86)/WinRAR/";}
	else if	(is_dir("C:/Progra~1/WinRAR/")){ $percorsoWinRar = "C:/Progra~1/WinRAR/";}
	else
	{
		$testo_alert = "Nel server non c'è il programma WINRAR; cercare in 'archivio' ";
		$testo_alert.= "o contattare l'Amministrazione per ottenere il file ".$nome_file." appena creato.";
		
		alert( $testo_alert );

		return "";
	}

	$str_zip = $percorsoWinRar . "rar.exe a ";
	$str_zip = $str_zip . $file_rar .  " " . $file;
	
	$str_zip = str_replace("Program Files (x86)", "Progra~2", $str_zip);
	$str_zip = str_replace("Programmi", "Progra~1", $str_zip);

	chdir($path);
	
	exec ($str_zip);
	
	if($elimina_originale == 1)
		unlink($file);
	
	chdir($cwd);
	
	return $file_rar;
	
}

function aggiungi_file_rar ($file_da_allegare , $path , $nome_rar)  // nome file con estensione, $path percorso della cartella di presenza rar 
{
	if($file_da_allegare=="")
		return 1;
		
	$cwd = getcwd();
	
	if (is_dir("C:/Programmi/WinRAR/")){ $percorsoWinRar = "C:/Programmi/WinRAR/";}
	else if (is_dir("C:/Program Files (x86)/WinRAR/")){ $percorsoWinRar = "C:/Program Files (x86)/WinRAR/";}
	else if	(is_dir("C:/Progra~1/WinRAR/")){ $percorsoWinRar = "C:/Progra~1/WinRAR/";}
	else
	{
		$testo_alert = "Nel server non c'è il programma WINRAR; cercare in 'archivio' ";
		$testo_alert.= "o contattare l'Amministrazione per ottenere il file ".$nome_file." appena creato.";
		
		alert( $testo_alert );

		return "";
	}
	
	$esplodoFirma = explode ("/", $file_da_allegare);
	$nomeFirma = $esplodoFirma[count($esplodoFirma)-1];
	$pathFirma = substr($file_da_allegare, 0, -strlen($nomeFirma));
	
	$str_zip = $percorsoWinRar . "rar.exe a ";
	$str_zip = $str_zip . " " . $path . "/" . $nome_rar .  " " . $nomeFirma;
	
	$str_zip = str_replace("Program Files (x86)", "Progra~2", $str_zip);
	$str_zip = str_replace("Programmi", "Progra~1", $str_zip);
	
	chdir($pathFirma);
	
	exec ($str_zip);
	
	chdir($cwd);
	
	return 1;
	
}

function crea_elenco_file ($path, $html = "")
{
	$elencoFile = array();
	crea_dir($path);
	$handle = opendir($path);

	while (($file = readdir($handle)) != false)
	{
		if ($file != "." && $file != "..")
			$elencoFile[] = $file;
	}

	rsort($elencoFile);
	closedir($handle);
	
	if($html == "select")
	{
		$select = "";
		for( $k=0 ; $k<count($elencoFile) ; $k++ )
		{
			$select .= "<option>".$elencoFile[$k]."</option>";
		}
		$elencoFile = $select;
	}
	
	return $elencoFile;
}

function limita_dim_immagine ($img, $maxLarghezza, $maxAltezza)  //  img deve arrivare in formato "/gitco2/stemmi/cogorno.png"
{
	$arrayDimensioni = array();
	$imgCompleto = $_SERVER['DOCUMENT_ROOT'] . $img;
	if (file_exists($imgCompleto))
	{
		$dimensioni = getimagesize($imgCompleto);
		$larghezza = $dimensioni[0];
		$altezza = $dimensioni[1];
		
		$rapporto = $larghezza / $altezza;
		
		if($larghezza > $altezza)
		{
			$new_larghezza  =   $maxLarghezza;
			$new_altezza    =   $altezza*($new_larghezza/$larghezza);
			
			if($new_altezza>$maxAltezza)
			{
				$new_altezza    =   $maxAltezza;
				$new_larghezza  =   $larghezza*($new_altezza/$altezza);
			}
		}
		
		if($larghezza < $altezza)
		{
			$new_altezza    =   $maxAltezza;
			$new_larghezza  =   $larghezza*($new_altezza/$altezza);

			if($new_larghezza>$maxLarghezza)
			{
				$new_larghezza  =   $maxLarghezza;
				$new_altezza    =   $altezza*($new_larghezza/$larghezza);
			}
		}
		
		if($larghezza == $altezza)
		{
			if($maxLarghezza<$maxAltezza)
			{
				$new_larghezza  =   $maxLarghezza;
				$new_altezza    =   $maxLarghezza;
			}
			else 
			{
				$new_larghezza  =   $maxAltezza;
				$new_altezza    =   $maxAltezza;
			}			
		}
		
		
// 		if ($larghezza > $maxLarghezza)
// 		{
// 			if ($altezza > $maxAltezza)
// 			{
// 				if ($larghezza < $altezza)
// 				{
// 					$larghezza = $maxLarghezza;
// 					$altezza = $maxLarghezza / $rapporto;
// 				}
// 				else
// 				{
// 					$altezza = $maxAltezza;
// 					$larghezza = $maxAltezza * $rapporto;
// 				}
// 			}
// 			else 
// 			{
// 				$larghezza = $maxLarghezza;
// 				$altezza = $maxLarghezza / $rapporto;
// 			}
// 		}
// 		else if ($altezza > $maxAltezza)
// 		{
// 			$altezza = $maxAltezza;
// 			$larghezza = $maxAltezza * $rapporto;
// 		}
		
		$arrayDimensioni[0] = $new_larghezza;
		$arrayDimensioni[1] = $new_altezza;
		
	}
	else 
	{
		alert ("Il file $imgCompleto non esiste (/librerie/php/file_function.php)");
		$arrayDimensioni[0] = 0;
		$arrayDimensioni[1] = 0;
	}
	return $arrayDimensioni;
}

function options_select_array ( $array , $campo = "Descrizione" , $campo_trailer = null )
{
	$options = "";
	for($i=0;$i<count($array);$i++)
	{
		$options.= "<option value='".$array[$i]['ID']."'>".$array[$i][$campo];
		
		if($campo_trailer!=null)
			$options.= " - ".$array[$i][$campo_trailer];
		
		$options.= "</option>";
	}

	return $options;
}

/*
 * $path in formato $_SERVER['DOCUMENT_ROOT'] . "cartella/cart/"
 * $prefisso testuale, la prima parte del nome, e alla fine c'è data
 * 			 (esempio     pdf_elenco_richieste_dati_2014-06-24_15-53-58.pdf: $prefisso = pdf_elenco_richieste_dati)
 * 			 (esempio     flusso_verbale_A658_1_2014-06-24_15-53-58.pdf: $prefisso = flusso_verbale)
 * 			 (esempio     flusso_verbale_A658_1_2013_1_3.rar: $prefisso = flusso_verbale VIENE ESCLUSO)
 */
function delete_files_after_X_days ($path, $prefisso, $giorni)
{
	$lungFissa = strlen ($prefisso);
	$dataSqlOdierna = date("Y-m-d");
	$handle = opendir($path);
	//echo "<br>" . $percorsoElencoProv;
	while (($file = readdir($handle)) != false)
	{
		//echo "<br>" . $file;
		if (substr($file, 0, $lungFissa) == $prefisso)
		{
			$esplodoEstensione = explode (".", $file);
			$senzaEstens = $esplodoEstensione[0];
			//$secondaParteNome = substr($file, $lungFissa);
			$esplodoTrattini = explode ("_", $senzaEstens);
			$totPezzi = count($esplodoTrattini);
			
			$dataFile = $esplodoTrattini[$totPezzi-2];  //  il penultimo è la data?
			$dataOk = false;
			$esplodoMeno = explode ("-", $dataFile);
			if (count($esplodoMeno) == 3)
			{
				if (strlen($esplodoMeno[0]) == 4 && 
					strlen($esplodoMeno[1]) == 2 && 
					strlen($esplodoMeno[2]) == 2)
						$dataOk = true;
			}
			if ($dataOk == false)
			{
				$dataFile = $esplodoTrattini[$totPezzi-1];  //  l'ultimo è la data?
				$dataOk = false;
				$esplodoMeno = explode ("-", $dataFile);
				if (count($esplodoMeno) == 3)
				{
					if (strlen($esplodoMeno[0]) == 4 &&
						strlen($esplodoMeno[1]) == 2 &&
						strlen($esplodoMeno[2]) == 2)
							$dataOk = true;
				}
			}
			
			//if ($dataFile == $dataSqlOdierna)
			if ($dataOk == true)
			{
				//echo "<br>" . $dataSqlOdierna . " - " . $dataFile;
				$differenzaDate = (strtotime ($dataSqlOdierna) - strtotime($dataFile)) / (60 * 60 * 24);
				if ($differenzaDate > $giorni)
					unlink ($path . $file);
				//return;
			}
		}
		//alert ("cip");
	}
	closedir($handle);
}

function cancella_files ($path, $giorni)
{
	/**
	 * Cancella i file creati da un determinato numero di giorni
	 * @param path string
	 * percorso file
	 * @param giorni int
	 * giorni passati dall'ultima modifica del file
	 */
	
	$handle = opendir($path);

	while (($file = readdir($handle)) != false)
	{
		if($file!="." && $file!="..")
		{
			$data_modifica = date('Y-m-d',filemtime($path."/".$file));
			$differenzaDate = ( strtotime (date('Y-m-d')) - strtotime ($data_modifica) ) / (60 * 60 * 24);
			
			if ($differenzaDate >= $giorni)
				unlink ($path."/".$file);
		}
	}
	
	closedir($handle);
}

function ElencoComuni ($comuneselected, $annoselected, $autorizzazione)
{
	if ($autorizzazione == 1)
		$query = "select CC, Denominazione from enti_gestiti order by Denominazione ASC";
	else if ($autorizzazione == 2)  //  utente solo di un comune
		$query = "select CC, Denominazione from enti_gestiti where CC = '$comuneselected'";
	
	$result = mysql_query($query);
	$num = mysql_num_rows($result);
	$stringaSelect = "
		<select id='sceglicomune' name='sceglicomune' size=1 onchange='cambiocomune();'>
			<option value=''>Selezionare un Ente</option>
			<option value=''>-----------------------------------------------------</option>
	";

	while ($cliente = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		if ($cliente['CC'] == $comuneselected) $selectedcom = " selected ";
		else $selectedcom = "";
			
		$stringaSelect .= "<option value=" . $cliente['CC'] . " " . $selectedcom .
					" title='Nome del Comune - Codice Catastale (Progressivo del Comune)'>" .
					$cliente['Denominazione'] . " - " . $cliente['CC'] . " </option>\n";
	
	}
	$stringaSelect .= "</select>";
	
	return $stringaSelect;
}

function ElencoAnni ($tiporicerca, $comuneselected, $annoselected)
{
	$annoTrovato = false;
	if ($comuneselected == 'YYYY' || $comuneselected == 'XXXX')
	{
		// non so cosa deve fare
	}
	else
	{
		// amministratori e tutti quanti i comuni
		$tempcomune = get_var('c');
		if ($tempcomune != $comuneselected)
		{
			$_SESSION['c'] = $comuneselected;
		}
		
		switch ($tiporicerca)
		{
			case "COATTIVA": $aggiunta = " AND Gestione_Coattiva = 'Y' "; break;
			case "TARGHEESTERE": $aggiunta = " AND Gestione_Targhe_Estere = 'Y' "; break;
			case "PUBBLICITA": $aggiunta = " AND Gestione_Pubblicita = 'Y' "; break;
			default: alert ("In ElencoAnni (file_function.php) manca un parametro"); $aggiunta = ""; break;
		}

		$query ="select Anno from anni_gestiti where CC_Anno ='$comuneselected' $aggiunta order by Anno DESC";
		
		$result = safe_query($query);
		$res = mysql_num_rows($result);

		if ($res == NULL)
		{
			echo ("Non ci sono anni gestiti per l'Ente selezionato. Inserire un anno.");
		}
		else
		{
			echo <<< LISTAANNI
				<select id="sceglianno" size=1 onchange='cambiocomune();'>
						<option value=''>Anno</option>
						<option value=''>---------------</option>\n
		
LISTAANNI;
			
			while ($year = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				if ($year['Anno'] == $annoselected) { $selectedyear = " selected "; $annoTrovato = true; }
				else $selectedyear = "";

				echo "<option ".$selectedyear." value=\"".$year['Anno']."\">".$year['Anno']."</option>\n";

			}
			
			echo "</select>";

		}
	}
	return $annoTrovato;
}

function ElencoEsteriComuni ($comuneselected, $annoselected, $autorizzazione)
{
	if ($autorizzazione == 1)
	{
		$query = "SELECT DISTINCT CC, Denominazione 
				FROM enti_gestiti, anni_gestiti
				WHERE CC_Anno = CC AND Gestione_Targhe_Estere = 'Y'
				order by Denominazione ASC";
	}
	else if ($autorizzazione == 2)  //  utente solo di un comune
	{
		$query = "select CC, Denominazione from enti_gestiti where CC = '$comuneselected'";
	}
	
	$result = mysql_query($query);
	$num = mysql_num_rows($result);
	$stringaSelect = "
		<select id='sceglicomune' name='sceglicomune' size=1 onchange='cambiocomune();'>
			<option value=''>Selezionare un Ente</option>
			<option value=''>-----------------------------------------------------</option>
	";

	while ($cliente = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		if ($cliente['CC'] == $comuneselected) $selectedcom = " selected ";
		else $selectedcom = "";
			
		$stringaSelect .= "<option value=" . $cliente['CC'] . " " . $selectedcom .
					" title='Nome del Comune - Codice Catastale (Progressivo del Comune)'>" .
					$cliente['Denominazione'] . " - " . $cliente['CC'] . " </option>\n";
	
	}
	$stringaSelect .= "</select>";
	
	return $stringaSelect;
}
?>