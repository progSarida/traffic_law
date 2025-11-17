<?php

/** Funzione per il controllo di validita' del codice fiscale/partita IVA.
// Corregge eventuali errori di battitura o tentativi di inserimento di tags.
// Considera le regole per la Partita IVA nel caso di contribuente di tipo "D" e
// le regole del Codice Fiscale in caso di tipo "M" o "F".
// Restituisce una stringa contenente la spiegazione dell'errore avvenuto, oppure
// "OK" in caso di esito positivo.
 * 
 * @param unknown $code
 * @param unknown $type
 * @return string
 */
function check_CFPI($code, $type)
{

	if($type==2)
	{
		// se la PI e' piu' corta di 11 caratteri allora segnala un errore
		if ( strlen($code) != 11 ) return 'lungh PI';

		// se esistono caratteri diversi da numeri segnala un errore
		if ( preg_match("/[^0-9]/",$code) ) return 'formato PI';
		
		return true;   // OK, il CF/la PI ha il formato corretto
	}
	else if($type==1)
	{
		// se il CF e' piu' corto di 16 caratteri allora segnala un errore
		if ( strlen($code) != 16 ) return 'lungh CF';

		// se esistono caratteri diversi da lettere e numeri segnala un errore
		if ( preg_match("/[^a-zA-Z0-9]/",$code) ) return 'cara CF';

		// se il formato non e' AAAAAADDADDADDDA segnala l'errore
		if ( !preg_match("/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/",$code) ) return 'formato CF';
		
		return true;   // OK, il CF/la PI ha il formato corretto

	}	
}


/** Calcola il codice fiscale di un contribuente a partire da cognome, nome,
// tipo, data di nascita e codice del comune di nascita (o del paese).
// N.B. - La funzione assume di ricevere parametri corretti, dal momento che le
//        procedure per controllare tale correttezza sono già state
//        implementate.
//        Ciò significa che:
//        - $tipo deve essere "M" oppure "F";
//        - $data deve essere nel formato aaaa-mm-gg;
//        - $cod_comune deve essere un codice esistente.
 * 
 * @param  string 	$cognome
 * @param  string 	$nome
 * @param  string 	$tipo
 * @param  date 	$data
 * @param  string 	$cod_comune
 * @return string
 */
function compute_CF($cognome, $nome, $tipo, $data, $cod_comune)
{
	$alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$vocali = "AEIOU";
	$numeri = "0123456789";
	$mesi = "ABCDEHLMPRST";
	$alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
	$numeri_disp = "10   2 3 4   5 6 7 8 9";

	$CF = "";
	$code = 0;
	if($tipo=="D") return "Impossibile generare il Codice Fiscale per una persona giuridica.";

	// Determina
	for($i=0; $i<=1; $i++)
	{
	$word = ($i==0 ? $cognome : $nome);
	$word = str_replace(" ","",$word);
	$word = str_replace("\'","",$word);
	$word = str_replace("à","a",$word);
	$word = str_replace("è","e",$word);
	$word = str_replace("é","e",$word);
	$word = str_replace("ì","i",$word);
	$word = str_replace("ò","o",$word);
	$word = str_replace("ù","u",$word);
	$word = strtoupper($word);

	$extracted_cons = "";
	$extracted_vocs = "";

	for($j=0; $j<strlen($word); $j++)
	{
	$char = substr($word,$j,1);
	$isthere = strrpos($vocali, $char);
	if($isthere===FALSE) // NOTA: I tre "=" sono voluti.
	$extracted_cons = $extracted_cons.$char;
	else
		$extracted_vocs = $extracted_vocs.$char;
	}

	$num_cons = strlen($extracted_cons);
	$num_vocs = strlen($extracted_vocs);

	if    ($num_cons>3 and $i==1)
		$CF = $CF.substr($extracted_cons,0,1).substr($extracted_cons,2,2);
		elseif($num_cons>2)
		$CF = $CF.substr($extracted_cons,0,3);
		elseif($num_cons==2 and $num_vocs>0)
		$CF = $CF.$extracted_cons.substr($extracted_vocs,0,1);
		elseif($num_cons==1 and $num_vocs==1)
		$CF = $CF.$extracted_cons.$extracted_vocs."X";
		elseif($num_cons==1 and $num_vocs>1)
		$CF = $CF.$extracted_cons.substr($extracted_vocs,0,2);
		elseif($numcons==0 and $num_vocs>2)
		$CF = $CF.substr($extracted_vocs,0,3);
		elseif($numcons==0 and $num_vocs==2)
		$CF = $CF.$extracted_vocs."X";
		else return "Le lettere che compongono cognome e nome non sono sufficienti per la generazione del Codice Fiscale. Controllare cognome e nome.";
	}

	$array_data = explode("-",$data);
	$CF = $CF.substr($array_data[0],2,2);
	$CF = $CF.substr($mesi,$array_data[1]-1,1);
	$CF = $CF.($tipo=="M" ? substr($array_data[2]+100,1,2) : substr($array_data[2]+140,1,2));

    $CF = $CF.$cod_comune;

    for($i=0; $i<strlen($CF); $i++)
    {
			$char = substr($CF,$i,1);
			if(($i%2)==0) // NOTA: se $i è pari, cioè se la lettera è dispari.
			$code = $code + strrpos($numeri_disp,$char) + strrpos($alfabeto_disp,$char);
			else
				$code = $code + strrpos($numeri,$char) + strrpos($alfabeto,$char);
}

$CF = $CF.substr($alfabeto,($code%26),1);

	if(strlen($CF)!=16) return "Non è stato possibile generare il Codice Fiscale.";

	return $CF;
}

?>