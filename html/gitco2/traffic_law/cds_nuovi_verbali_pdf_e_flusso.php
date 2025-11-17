<?php
	include $_SERVER['DOCUMENT_ROOT'] . "/gitco/percorsi.php";
	include LIBRERIE . "/form_lib.php";
	include LIBRERIE . "/funzioni_db.php";
	include LIBRERIE . "/cds_lib.php";
	include CLASSI . "/comuni_classi.php";
	include CLASSI . "/cds_classi.php";
	include CLASSI . "/posizioni_classi.php";
	include CLASSI . "/parametri_classi.php";
	include_once CLASSI . "../classi_new/NotificaVerbaleCds_classi.php";
	include CLASSI . "../classi_new/FtpAruba_classi.php";
	include CLASSI . "../classi_new/TestoVerbaleCds_classi.php";
	include CLASSI . "../classi_new/fotogrammi_cds_classi.php";
	include_once CLASSI . "../classi_new/VerbaleCompletoCds_classi.php";
	include LIBRERIE . "/connessione_db.php";
include LIBRERIE . "../funzioniDatabase/funzioniDatabase.php";
include LIBRERIE . "../funzioniDatabase/funzioniAiuto.php";
include LIBRERIE . "../fpdf/fpdf.php";




class NuovoTestoMyVerbale
{
	var $fileFlusso = NULL;
	var $filePdf = NULL;
	var $comune = NULL;
	var $margine = NULL;
	var $stampaconfoto = NULL;
	var $stemmacomune = NULL;
	
	var $Tipologia_Stampa = NULL;
	var $Tipologia_Atto = NULL;
	
	var $CodiceComune = NULL;
	
	var $NomeDestinatario = NULL;
	var $RecapitoDestinatario = NULL;
	var $IndirizzoDestinatario = NULL;
	
	//var $CittaDestinatario = NULL;  //  10
	var $CapDestinatario = NULL;
	var $CityDestinatario = NULL;
	var $ProvinciaDestinatario = NULL;
	
	var $StatoDestinatario = NULL;
	
	var $NumeroViolazione = NULL;
	var $NumeroVerbale = NULL;
	var $DataViolazione = NULL;
	var $OraViolazione = NULL;
	var $LuogoViolazione = NULL;
	var $ConducenteOppureTrasgressore = NULL;
	var $TipoVeicolo = NULL;
	var $TargaVeicolo = NULL;
	var $NumeriArticoliInfranti = NULL;  //  20
	var $PuntiDaDecurtare = NULL;
	var $Riduzione5GiorniAmmessa = NULL;
	var $RiduzioneEdittaleAmmessa = NULL;
	var $DescrizioneArticoliInfranti = NULL;
	var $MotivoMancataContestazione = NULL;
	var $SanzioniAccessorie = NULL;

	var $IdentificazioneTrasgressore = NULL;
	
	var $PrimaRigaProprietario = NULL;
	var $SecondaRigaProprietario = NULL;
	var $PrimaRigaTrasgressore = NULL;
	var $SecondaRigaTrasgressore = NULL;
	var $Esercente = NULL;
	var $PrimaRigaEsercente = NULL;
	var $SecondaRigaEsercente = NULL;
	
	//var $Spese_Notifica = NULL;
	var $RigaAccertatori = NULL;  //  40
	var $ResponsabileDati = NULL;
	var $ResponsabileProcedimento = NULL;
	var $LuogoDataVerbalizzazione = NULL;
	var $Verbalizzante = NULL;
	
	var $Prefettura = NULL;
	var $Giudice = NULL;
	
	var $ImportoRiduzione30 = NULL;
	var $speseRiduzione30 = NULL;
	var $speseCanRiduzione30 = NULL;
	var $speseCadRiduzione30 = NULL;
	var $TotaleRiduzione30 = NULL;
	var $TotaleCanRiduzione30 = NULL;
	var $TotaleCadRiduzione30 = NULL;
	
	var $ImportoEdittale = NULL;
	var $speseEdittale = NULL;
	var $speseCanEdittale = NULL;
	var $speseCadEdittale = NULL;
	var $TotaleEdittale = NULL;
	var $TotaleCanEdittale = NULL;
	var $TotaleCadEdittale = NULL;
	
	var $ImportoOltre60 = NULL;
	var $speseOltre60 = NULL;
	var $speseCanOltre60 = NULL;
	var $speseCadOltre60 = NULL;
	var $TotaleOltre60 = NULL;
	var $TotaleCanOltre60 = NULL;
	var $TotaleCadOltre60 = NULL;
	
	var $OggettoTesto = NULL;
	var $OggettoBollettino = NULL;
	var $OggettoAutorizzazione = NULL;
	var $OggettoDecurtazionePunti = NULL;
	
	function NuovoTestoMyVerbale ()
	{
		
	}

	function ApriFileFlusso ($comune, $marg, $nome_file_flusso)
	{
		$this->fileFlusso = fopen ($_SERVER['DOCUMENT_ROOT'] . "/file_stampa/" . $nome_file_flusso, "w+");
		if ($this->fileFlusso == NULL)
			return "ERRORE";
		//echo "<br>ee $this->fileFlusso ee  <br>";
		$this->comune = $comune;
// 		echo $comune;DIE;
		$this->margine = $marg;
		$this->IntestazioneFlusso();
		return 1;
	}

	function ChiudiFileFlusso ($nome_file_flusso, $stringaFine1, $stringaFine2)
	{
		$myFile = $_SERVER['DOCUMENT_ROOT'] . "/file_stampa/" . $nome_file_flusso;
		if ($this->fileFlusso == NULL)
		{
			alert ("mancata creazione flusso");
			return;
		}
		fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $stringaFine1);
		fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
		fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $stringaFine2);
		fclose ($this->fileFlusso);
		
		$tuttoTesto = file_get_contents($myFile);
		
		$esplodoRighe = explode(Chr(13).Chr(10), $tuttoTesto);
		if (count($esplodoRighe) <= 2){  // c'� file vuoto! solo intestazione!!! lo cancello
			alert("File vuoto!");
			unlink ($myFile);
		}
	}
	
	function IntestazioneFlusso ()
	{
		if ($this->fileFlusso == NULL)
		{
			alert ("mancata creazione flusso");
			return;
		}
		foreach ($this as $key => $value)
		{
			if ($key == "fileFlusso") {}
			else if ($key == "filePdf") {}
			else if ($key == "comune") {}
			else if ($key == "margine") {}
			else if ($key == "stampaconfoto") {}
			else if ($key == "stemmacomune") {}
			else if ($key == "OggettoTesto") {}
			else if ($key == "OggettoBollettino") {}
			else if ($key == "OggettoAutorizzazione") {}
			else if ($key == "OggettoDecurtazionePunti") {}
			else
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $key);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		$this->OggettoTesto = new TestoVerbaleCds(NULL);
		foreach ($this->OggettoTesto as $key => $value)
		{
			if ($key == "Tes_Progr") {}
			else if ($key == "CC_Comune") {}
			else if ($key == "Data_Creazione_Parametro") {}
			else
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $key);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		
		$this->OggettoBollettino = new BollettinoPostale(NULL);
		foreach ($this->OggettoBollettino as $key => $value)
		{
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $key);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		$this->OggettoAutorizzazione = new AutorizzazionePostale(NULL);
		foreach ($this->OggettoAutorizzazione as $key => $value)
		{
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $key);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		$this->OggettoDecurtazionePunti = new FoglioDecurtazionePunti(NULL);
		foreach ($this->OggettoDecurtazionePunti as $key => $value)
		{
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $key);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(13).Chr(10));
	}
	
	function FlussoSingolaRiga ()
	{
		if ($this->fileFlusso == NULL)
		{
			alert ("mancata creazione flusso");
			return;
		}
		foreach ($this as $key => $value)
		{
			if ($key == "fileFlusso") {}
			else if ($key == "filePdf") {}
			else if ($key == "comune") {}
			else if ($key == "margine") {}
			else if ($key == "stampaconfoto") {}
			else if ($key == "stemmacomune") {}
			else if ($key == "OggettoTesto") {}
			else if ($key == "OggettoBollettino") {}
			else if ($key == "OggettoAutorizzazione") {}
			else if ($key == "OggettoDecurtazionePunti") {}
			else
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $value);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		foreach ($this->OggettoTesto as $key => $value)
		{
			if ($key == "Tes_Progr") {}
			else if ($key == "CC_Comune") {}
			else if ($key == "Data_Creazione_Parametro") {}
			else
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $value);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		foreach ($this->OggettoBollettino as $key => $value)
		{
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $value);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		foreach ($this->OggettoAutorizzazione as $key => $value)
		{
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $value);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		foreach ($this->OggettoDecurtazionePunti as $key => $value)
		{
			{
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, $value);
				fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(9));
			}
		}
		fput_margine_new ($this->comune, $this->margine, $this->fileFlusso, Chr(13).Chr(10));
	}
	
	function ApriStampaPdf ($comune, $stampaFoto)
	{
		$queryStemma = "SELECT Com_Stemma FROM comune WHERE Com_CC = '" . $comune . "'";
		$stemma = single_answer_query($queryStemma);
		$tempStemma = explode (".", $stemma);
		$stemma = $_SERVER['DOCUMENT_ROOT'] . "/gitco/immagini/" . $tempStemma[0] . ".jpg";
	
		$this->filePdf = new FPDF("P", "mm", "A4");
		$this->stemmacomune = $stemma;
		$this->comune = $comune;
		if ($stampaFoto == "Y")
			$this->stampaconfoto = new FtpAruba();
	}
	
	function CreaPdf (
			$nomefilePdf, 
			$provDef,  // $provDef "Provvisoria" o "Definitiva"
			$formatoStampa,  // possono arrivare 6 (verbale) o 4 (bollettini)
			$nonfarlosemarco) 
	{
		if ($this->stampaconfoto != NULL)
			$this->stampaconfoto->CloseFtp();
		
		$mioNomeFilePdf = $_SERVER['DOCUMENT_ROOT'] . "/file_stampa/" . $nomefilePdf;
		$nomeStampatoPdf = $_SERVER['DOCUMENT_ROOT'] . "/stampati/" . $this->comune . "/" . $nomefilePdf;
		$mioApriPdf = "/file_stampa/" . $nomefilePdf;
		$this->filePdf->Output ($mioNomeFilePdf, F);
		
		if ($provDef == "Definitiva" && $formatoStampa != 4 && $nonfarlosemarco == 0)
		{
			copy ($mioNomeFilePdf, $nomeStampatoPdf);
		}
		
		echo <<< APRIPDF
			<script>
				window.open('$mioApriPdf');
			</script>
APRIPDF;
	}
	
	function PreparaSingolaRiga (
			$idtestoVerbale, 
			$idFoto,
			$stampaConFoto,  //  Y o N da richiesta
			$bol, 
			$autoriz, 
			$decurt, 
			$formatoStampa,  //  $formatoStampa  1 FLUSSO  3 PDF
			$copiaVerbale  //  $copiaVerbale   6 VERBALE   0 COPIAVERBALE  4 BOLLETTINO
	)  
	{
		$this->OggettoTesto = new TestoVerbaleCds($idtestoVerbale);
		
		if ($this->OggettoTesto->Tipologia_Verbale != "99999")
		{
			$tipoVerbale = "NORMALE";
		}
		else 
		{
			$tipoVerbale = "ASS/REV";
		}
		
		$this->OggettoBollettino = $bol;
		$this->OggettoAutorizzazione = $autoriz;
		$this->OggettoDecurtazionePunti = $decurt;
		
		if ($tipoVerbale == "NORMALE")
		{
			$this->OggettoTesto->Testo_1 = "Il giorno " . $this->DataViolazione . " alle ore " . $this->OraViolazione . " in " .
					$this->LuogoViolazione . ", il " . $this->ConducenteOppureTrasgressore . " del veicolo " . $this->TipoVeicolo . 
					" targato " . $this->TargaVeicolo . " ha violato l'art. " . $this->NumeriArticoliInfranti . " del C.d.S., poich� " .
					$this->DescrizioneArticoliInfranti;
		}
		else 
		{
			$this->OggettoTesto->Testo_1 = "Il giorno " . $this->DataViolazione . " alle ore " . $this->OraViolazione . " in " .
					$this->LuogoViolazione . ", � stato rilevato che il veicolo " . $this->TipoVeicolo . " " .
					" targato " . $this->TargaVeicolo . " " . $this->DescrizioneArticoliInfranti;
		}
		
		$cercoPunti = substr($this->DescrizioneArticoliInfranti, -4, 4);
		if (strpos ($cercoPunti, ".") == false)
		{
			//alert ("non ce punto  $this->TargaVeicolo ha violato l'art. $this->NumeriArticoliInfranti");
			$this->OggettoTesto->Testo_1 .= ".";
		}
		//else alert ("ce punto)");
		//return;
		$this->OggettoTesto->Testo_1 .= " " . $this->SanzioniAccessorie;
		
		if ($formatoStampa == 3)
		{
			if ($this->MotivoMancataContestazione != "" && $this->MotivoMancataContestazione != " ")
				$this->OggettoTesto->Testo_2 = "<b>La violazione non � stata immediatamente contestata:</b> " . $this->MotivoMancataContestazione;
			else 
				$this->OggettoTesto->Testo_2 = "";
		}
		else $this->OggettoTesto->Testo_2 = $this->MotivoMancataContestazione;
		
		//$temp = $this->OggettoTesto->Testo_2_B;
		//$temp = $this->SostituisciTestoTraGraffe ($temp, "{INFOPAGAMENTO}", "");
		
		$temp = $this->OggettoTesto->Testo_4;
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoRiduzione30);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseRiduzione30);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleRiduzione30);
		$this->OggettoTesto->Testo_4 = $temp;
		
		$temp = $this->OggettoTesto->Testo_5;
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoRiduzione30);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseCanRiduzione30);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleCanRiduzione30);
		$this->OggettoTesto->Testo_5 = $temp;
		
		$temp = $this->OggettoTesto->Testo_6;
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoRiduzione30);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseCadRiduzione30);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleCadRiduzione30);
		$this->OggettoTesto->Testo_6 = $temp;
		
		if ($this->Riduzione5GiorniAmmessa == "AMMESSARIDUZIONE5GIORNI") {}
		else if ($this->Riduzione5GiorniAmmessa == "NONRIDUZIONE5GIORNI")
		{
			if ($this->OggettoTesto->Testo_7 == "PAGAMENTO IN MISURA DEL MINIMO EDITTALE: DAL 6� GIORNO ED ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE")
			{
				$this->OggettoTesto->Testo_3 = "";
				$this->OggettoTesto->Testo_4 = "";
				$this->OggettoTesto->Testo_5 = "";
				$this->OggettoTesto->Testo_6 = "";
				$this->OggettoTesto->Testo_7 = "PAGAMENTO IN MISURA DEL MINIMO EDITTALE: ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE";
			}
			else alert ("errore nel testo7");
		}
		else alert ("la riduzione dei 5 giorni ha dei problemi.");
		
		if ($this->RiduzioneEdittaleAmmessa == "AMMESSARIDUZIONEEDITTALE") {}
		else if ($this->RiduzioneEdittaleAmmessa == "NONRIDUZIONEEDITTALE")
		{
			if ($this->OggettoTesto->Testo_7 == "PAGAMENTO IN MISURA DEL MINIMO EDITTALE: DAL 6� GIORNO ED ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE" ||
				$this->OggettoTesto->Testo_7 == "PAGAMENTO IN MISURA DEL MINIMO EDITTALE: ENTRO 60 GIORNI DALLA NOTIFICA DEL PRESENTE VERBALE")
			{
				$this->OggettoTesto->Testo_3 = "";
				$this->OggettoTesto->Testo_4 = "";
				$this->OggettoTesto->Testo_5 = "";
				$this->OggettoTesto->Testo_6 = "";
				$this->OggettoTesto->Testo_7 = "PAGAMENTO DELLA SANZIONE";
			}
			else alert ("errore nel testo7_2aparte");
		}
		else alert ("la riduzione all'importo minimo ha dei problemi. : $this->RiduzioneEdittaleAmmessa");
		
		
		$temp = $this->OggettoTesto->Testo_8;
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoEdittale);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseEdittale);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleEdittale);
		$this->OggettoTesto->Testo_8 = $temp;
		
		$temp = $this->OggettoTesto->Testo_9;
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoEdittale);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseCanEdittale);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleCanEdittale);
		$this->OggettoTesto->Testo_9 = $temp;
		
		$temp = $this->OggettoTesto->Testo_A0;
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoEdittale);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseCadEdittale);
		$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleCadEdittale);
		$this->OggettoTesto->Testo_A0 = $temp;
		
		if ($this->Riduzione5GiorniAmmessa == "AMMESSARIDUZIONE5GIORNI")
		{
			$this->OggettoTesto->Testo_A1 = "";
			$this->OggettoTesto->Testo_A2 = "";
			$this->OggettoTesto->Testo_A3 = "";
			$this->OggettoTesto->Testo_A4 = "";
		}
		else if ($this->Riduzione5GiorniAmmessa == "NONRIDUZIONE5GIORNI")
		{
			$temp = $this->OggettoTesto->Testo_A2;
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoOltre60);
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseOltre60);
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleOltre60);
			$this->OggettoTesto->Testo_A2 = $temp;
			
			$temp = $this->OggettoTesto->Testo_A3;
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoOltre60);
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseCanOltre60);
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleCanOltre60);
			$this->OggettoTesto->Testo_A3 = $temp;
			
			$temp = $this->OggettoTesto->Testo_A4;
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSANZIONE}", $this->ImportoOltre60);
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTOSPESE}", $this->speseCadOltre60);
			$temp = $this->SostituisciTestoTraGraffe ($temp, "{IMPORTODAPAGARE}", $this->TotaleCadOltre60);
			$this->OggettoTesto->Testo_A4 = $temp;
		}
		
		if ($idFoto == NULL)
		{
			$this->OggettoTesto->Testo_Informazioni_Foto = "";
		}
		
		if ($formatoStampa == 3)
		{
			$this->OggettoTesto->Testo_Informazioni_8_B = "A) Entro 60 giorni dalla notifica (o contestazione) della violazione, indirizzando il ricorso ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "al Prefetto competente per il luogo in cui la violazione e' avvenuta (<b>" . $this->Prefettura . "</b>). ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "Nello stesso termine, il ricorso pu� essere presentato presso la sede dell'ufficio o comando citato, ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "cui appartiene l'organo accertatore, dove pu� essere presentato a mani, o inviato mediante ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "lettera raccomandata con avviso di ricevimento, oppure direttamente al Prefetto, ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "a mezzo lettera raccomandata con A.R. Il ricorrente pu� richiedere l'audizione personale. ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "Al ricorso possono essere allegati gli eventuali documenti ritenuti idonei. ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "Il Prefetto, se riterr� fondato l'accertamento, emetter� ordinanza, ingiungendo il pagamento ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "di una somma non inferiore al doppio del minimo edittale previsto per ogni singola violazione ";
			$this->OggettoTesto->Testo_Informazioni_8_B .= "(art. 203 e 204 del C.d.S.).";
				
			$this->OggettoTesto->Testo_Informazioni_8_C = "B) Entro 30 giorni dalla notifica (o contestazione) della violazione, indirizzando il ricorso al ";
			$this->OggettoTesto->Testo_Informazioni_8_C .= "Giudice di Pace competente per il territorio del luogo in cui la violazione ";
			$this->OggettoTesto->Testo_Informazioni_8_C .= "e' avvenuta (<b>" . $this->Giudice . "</b>), osservando le modalit� previste dell'art. 204-bis ";
			$this->OggettoTesto->Testo_Informazioni_8_C .= "del D. Lgs. 16 dicembre 1992, n. 285 e successive modifiche ed integrazioni e come novellato ";
			$this->OggettoTesto->Testo_Informazioni_8_C .= "dal D.lgs. 150/2011.";
			
			if ($copiaVerbale == 6)
				$this->OggettoTesto->Testo_Specifica = "";  //  se � verbale non ci va "copia conforme ecc"
			else if ($copiaVerbale == 0)
			{}
			else if ($copiaVerbale == 4)  //  bollettini
			{}
			else alert ("La variabile $copiaVerbale non � n� 6 (verbale), n� 0 (copiaverbale), n� 4 (bollettini)");
		}
		else if ($formatoStampa == 1)
		{
			$this->OggettoTesto->Testo_Specifica = "";  //  se � flusso non ci va "copia conforme ecc"
		}
		
		//$this->OggettoDecurtazionePunti->UfficioPunti = $this->OggettoTesto->Intestazione2;
		//$this->OggettoDecurtazionePunti->ComunePunti = $this->OggettoTesto->Intestazione1;
		
		if ($formatoStampa == 1)  //  flusso
			$this->FlussoSingolaRiga();
		else if ($formatoStampa == 3)  //  pdf
		{
			if ($copiaVerbale == 4)
				$this->StampaSingoloBollettino();  // bollettino
			else if ($copiaVerbale == 6)
				$this->StampaSingolaRiga($idFoto, $stampaConFoto, $copiaVerbale);  //  verbale
			else if ($copiaVerbale == 0)
				$this->StampaSingolaRiga($idFoto, $stampaConFoto, $copiaVerbale);  //  copia verbale
		}
	
	}
	
	function SostituisciTestoTraGraffe ($myTesto, $testoGraffe, $cosaMettere, $bold = NULL)
	{
		if ($myTesto == "") return "";
		$temp = $myTesto;
		$lunghGraffa = strlen ($testoGraffe);
		$posto = strpos($temp, $testoGraffe);
		
		if ($posto == -1 || $posto == "")
		{
			alert ("Il campo $testoGraffe non � presente nel testo $temp");
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
		return $myTesto;
	}
	
	function Cella ($w, $h, $testo, $bordo, $allin, $capo)
	{
		$this->filePdf->Cell($w, $h, $testo, $bordo, 0, $allin, 0);
		if ($capo == 1)
			$this->filePdf->Ln($h);
	}
	
	function ControllaCella ($w, $h, $testo, $bordo, $allin, $capo)
	{
		if ($testo != "")
		{
			$this->Cella ($w, $h, $testo, $bordo, $allin, $capo);
		}
	}
	
	function ControllaTag ($larghezzaTag, $altezzaTag, $testo, $border, $allign, $spazioDopo)
	{
		if ($testo != "" && $testo != "<b></b>")
		{
			$this->filePdf->WriteTag ($larghezzaTag, $altezzaTag, $testo, $border, $allign, 0, 0);
			$this->filePdf->Ln($spazioDopo);
		}
	}
	
	function Grass ($testo)
	{
		return "<b>" . $testo . "</b>";
	}
	
	function StampaSingolaRiga ($mioIdFotogrammiCds, $stampaConFoto, $copyVerbale)
	{
		if ($this->filePdf == NULL)
		{
			alert ("mancata creazione pdf2");
			return;
		}
		
		$border = 0;
		$aCapo = 1;
		
		//$this->AssociaArrayAllaClasse ($arrayDati);
		//$myTestoVerbale = new TestoVerbaleCds($idTestoVerbale);
		$myTestoVerbale = $this->OggettoTesto;
		
		$larghezzaLogo = 25;
		$larghezzaComune = 65;
		$larghezzaTitolo = 100;
		$altezzaComune = 3;
		
		$larghezzaVuotaDest = 115;
		$larghezzaDestinatario = 95;
		$altezzaDestinatario = 5;
		
		$altezza4 = 4;
		
		$larghezzaTags = 190;
		$altezzaTags = 3;
		$metaAltTag = $altezzaTags / 2;
		$spazionullo = 0;
		
		$larghezzaProprietari = 47;
		
		$this->filePdf->AddPage();
		$this->filePdf->SetMargins (10, 10, 10);
		
		$this->Cella ($larghezzaLogo, 1, "", $border, "L", $aCapo);
		
		$this->filePdf->SetFont('Times','B',6);
		$this->Cella ($larghezzaLogo, $altezzaComune, "", $border, "L", 0);
		$this->Cella ($larghezzaComune, $altezzaComune, $myTestoVerbale->Intestazione1, $border, "L", 0);
		//$this->Cella ($larghezzaComune, $altezzaComune, "Provincia di Savona", $border, "L", 0);

		$this->filePdf->SetFont('Times','B',9);
		$this->Cella ($larghezzaTitolo, $altezzaComune, "VERBALE DI ACCERTAMENTO DI VIOLAZIONE", $border, "C", $aCapo);
		
		if ($this->stemmacomune != "")
			$this->filePdf->Image($this->stemmacomune,12,8,14,15,'jpg');
		
		$this->filePdf->SetFont('Times','B',6);
		$this->Cella ($larghezzaLogo, $altezzaComune, "", $border, "L", 0);
		$this->Cella ($larghezzaComune, $altezzaComune, $myTestoVerbale->Intestazione2, $border, "L", 0);
		$this->filePdf->SetFont('Times','B',9);
		$this->Cella ($larghezzaTitolo, $altezzaComune, "AL CODICE DELLA STRADA", $border, "C", $aCapo);
		
		$this->filePdf->SetFont('Times','B',6);
		$this->Cella ($larghezzaLogo, $altezzaComune, "", $border, "L", 0);
		$this->Cella ($larghezzaComune, $altezzaComune, $myTestoVerbale->Intestazione3, $border, "L", 0);
		$this->filePdf->SetFont('Times','B',9);
		$this->Cella ($larghezzaTitolo, $altezzaComune, "(Art. 201 del D.Lgs. 30/04/1992 N. 285 e successive modifiche)", $border, "C", $aCapo);
		
		$this->filePdf->SetFont('Times','B',6);
		
		$this->Cella ($larghezzaLogo, $altezzaComune, "", $border, "L", 0);
		$this->Cella ($larghezzaComune, $altezzaComune, $myTestoVerbale->Intestazione4, $border, "L", $aCapo);
		
		//if ($myTestoVerbale->Intestazione5 != "")
		{
			$this->Cella ($larghezzaLogo, $altezzaComune, "", $border, "L", 0);
			$this->Cella ($larghezzaComune, $altezzaComune, $myTestoVerbale->Intestazione5, $border, "L", $aCapo);
		}
		
		//if ($myTestoVerbale->Intestazione6 != "")
		{
			$this->Cella ($larghezzaLogo, $altezzaComune, "", $border, "L", 0);
			$this->Cella ($larghezzaComune, $altezzaComune, $myTestoVerbale->Intestazione6, $border, "L", $aCapo);
		}
		
		$this->filePdf->Ln(4);
		
		$this->filePdf->SetFont('Times','',9);
		$this->Cella ($larghezzaLogo, $altezzaComune, "Cron. N. ", $border, "L", 0);
		$this->filePdf->SetFont('Times','B',9);
		$this->Cella ($larghezzaComune, $altezzaComune, $this->NumeroViolazione, $border, "L", $aCapo);
		$this->filePdf->SetFont('Times','',9);
		$this->Cella ($larghezzaLogo, $altezzaComune, "Verbale N. ", $border, "L", 0);
		$this->filePdf->SetFont('Times','B',9);
		$this->Cella ($larghezzaComune, $altezzaComune, $this->NumeroVerbale, $border, "L", $aCapo);
		
		$this->filePdf->Ln(15);
		
		$this->filePdf->SetFont('Times','',10);
		$this->filePdf->SetStyle("b", "Times", "B", 0, "0,0,0");
		
		$this->Cella ($larghezzaVuotaDest, $altezzaDestinatario, "", $border, "L", 0);
		$this->Cella ($larghezzaDestinatario, $altezzaDestinatario, $this->NomeDestinatario, $border, "L", $aCapo);
		if ($this->RecapitoDestinatario != "")
		{
			$this->Cella ($larghezzaVuotaDest, $altezzaDestinatario, "", $border, "L", 0);
			$this->Cella ($larghezzaDestinatario, $altezzaDestinatario, $this->RecapitoDestinatario, $border, "L", $aCapo);
		}
		
		$this->Cella ($larghezzaVuotaDest, $altezzaDestinatario, "", $border, "L", 0);
		$this->Cella ($larghezzaDestinatario, $altezzaDestinatario, $this->IndirizzoDestinatario, $border, "L", $aCapo);
		$this->Cella ($larghezzaVuotaDest, $altezzaDestinatario, "", $border, "L", 0);
		
		$cittaDelDest = $this->CapDestinatario;
		if ($cittaDelDest != "") $cittaDelDest .= " ";
		$cittaDelDest .= $this->CityDestinatario;
		if ($this->ProvinciaDestinatario != "") $cittaDelDest .= " " . $this->ProvinciaDestinatario;
		if ($this->StatoDestinatario != "") $cittaDelDest .= " - " . $this->StatoDestinatario;
		//$cittaDelDest = $this->CapDestinatario . " " . $this->CityDestinatario . " " . $this->ProvinciaDestinatario;
		$this->Cella ($larghezzaDestinatario, $altezzaDestinatario, $cittaDelDest, $border, "L", $aCapo);
		$this->Cella ($larghezzaVuotaDest, $altezzaDestinatario, "", $border, "L", 0);
		//$this->ControllaCella ($larghezzaDestinatario, $altezzaDestinatario, $this->StatoDestinatario, $border, "L", $aCapo);
		
		$this->filePdf->Ln(7);
		$this->filePdf->Ln($altezzaTags);
		
		/*$this->ControllaTag ($larghezzaTags, $altezzaTags, $testo_1, $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $testo_2, $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $testo_3, $border, "J", $spazionullo);*/
		//$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_1, $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_1, $border, "J", $spazionullo);
		
		$this->filePdf->SetFont('Times','',8);
		//$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_2, $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_2, $border, "J", $spazionullo);
		
		$this->filePdf->SetFont('Times','',8);
		
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Testo_2_A . "</b>", $border, "C", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_2_B, $border, "J", $spazionullo);
		//$this->filePdf->Ln($altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Testo_3 . "</b>", $border, "C", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_4, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_5, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_6, $border, "J", $spazionullo);
		//$this->filePdf->Ln($altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Testo_7 . "</b>", $border, "C", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_8, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_9, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_A0, $border, "J", $spazionullo);
		//$this->filePdf->Ln($metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Testo_A1 . "</b>", $border, "C", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_A2, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_A3, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezza4, $myTestoVerbale->Testo_A4, $border, "J", $spazionullo);
		$this->filePdf->Ln($metaAltTag);
		
		$this->filePdf->SetFont('Times','',7);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Testo_A5 . "</b>", $border, "J", $spazionullo);
		//$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Testo_A6 . "</b>", $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Testo_A6 . "</b>", $border, "J", $spazionullo);
		
		$this->filePdf->SetFont('Times','',9);
		//$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Testo_A7 . "</b>", $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Testo_A7 . "</b>", $border, "J", $spazionullo);
		
		$this->ControllaCella(0, $altezzaTags, $this->IdentificazioneTrasgressore, $border, "L", $capo);
		
		$this->filePdf->SetFont('Times', 'B', 9);
		$this->Cella ($larghezzaProprietari, $altezza4, "Proprietario o Solidale:", $border, "L", 0);
		$this->filePdf->SetFont('Times', '', 9);
		$this->Cella (0, $altezza4, $this->PrimaRigaProprietario, $border, "L", $aCapo);
		$this->Cella ($larghezzaProprietari, $altezza4, "", $border, "L", 0);
		$this->Cella (0, $altezza4, $this->SecondaRigaProprietario, $border, "L", $aCapo);
		
		$this->filePdf->SetFont('Times', 'B', 9);
		$this->Cella ($larghezzaProprietari, $altezza4, "Conducente/Trasgressore:", $border, "L", 0);
		$this->filePdf->SetFont('Times', '', 9);
		$this->Cella (0, $altezza4, $this->PrimaRigaTrasgressore, $border, "L", $aCapo);
		$this->Cella ($larghezzaProprietari, $altezza4, "", $border, "L", 0);
		$this->Cella (0, $altezza4, $this->SecondaRigaTrasgressore, $border, "L", $aCapo);
		
		if ($this->Esercente != "")
		{
			$this->filePdf->SetFont('Times', 'B', 9);
			$this->Cella ($larghezzaProprietari, $altezza4, $this->Esercente, $border, "L", 0);
			//$this->Cella ($larghezzaProprietari, $altezzaTags, $this->Esercente:", $border, "L", 0);
			$this->filePdf->SetFont('Times', '', 9);
			$this->Cella (0, $altezza4, $this->PrimaRigaEsercente, $border, "L", $aCapo);
			$this->Cella ($larghezzaProprietari, $altezza4, "", $border, "L", 0);
			$this->Cella (0, $altezza4, $this->SecondaRigaEsercente, $border, "L", $aCapo);
		}
		
		$this->filePdf->Ln($altezzaTags);
		
		$this->filePdf->SetFont('Times', 'B', 9);
		$this->Cella ($larghezzaProprietari, $altezzaTags, "Accertatore/i:", $border, "L", 0);
		$this->filePdf->SetFont('Times', '', 9);
		//$this->Cella (0, $altezzaTags, $testoAccertatori, $border, "L", $aCapo);
		$this->Cella (0, $altezzaTags, $this->RigaAccertatori, $border, "L", $aCapo);
		
		$this->filePdf->SetFont('Times', 'B', 9);
		$this->Cella ($larghezzaProprietari, $altezzaTags, "Responsabile immissione dati:", $border, "L", 0);
		$this->filePdf->SetFont('Times', '', 9);
		$this->Cella ($larghezzaProprietari, $altezzaTags, $this->ResponsabileDati, $border, "L", 0);
		$this->filePdf->SetFont('Times', 'B', 9);
		$this->Cella ($larghezzaProprietari, $altezzaTags, "Responsabile del procedimento:", $border, "L", 0);
		$this->filePdf->SetFont('Times', '', 9);
		$this->Cella ($larghezzaProprietari, $altezzaTags, $this->ResponsabileProcedimento, $border, "L", $aCapo);
		
		$this->filePdf->SetFont('Times', '', 9);
		$this->Cella ($larghezzaProprietari*2, $altezzaTags, $this->LuogoDataVerbalizzazione, $border, "L", 0);
		$this->Cella ($larghezzaProprietari, $altezzaTags, "Il Verbalizzante:", $border, "L", 0);
		$this->Cella ($larghezzaProprietari, $altezzaTags, $this->Verbalizzante, $border, "L", $aCapo);
		$this->filePdf->Ln ($altezzaTags);
		
		$this->filePdf->SetFont('Times', '', 8);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Specifica, $border, "J", $altezzaTags);

		$this->filePdf->SetFont('Times','B',9);
		$this->filePdf->SetStyle("b","Times","B",0,"0,0,0");
		$this->filePdf->SetStyle("u","Times","U",0,"0,0,0");
		
		$this->filePdf->AddPage();
		
		$this->filePdf->SetFont('Times', '', 8);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>Cron. N. " . $this->NumeroVerbale . "</b>", $border, "C", $metaAltTag);
		
		$this->filePdf->Ln($metaAltTag);
		
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Titolo_Informazioni_1 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Testo_Informazioni_1 . "</b>", $border, "J", $altezzaTags);
		$this->ControllaTag ($larghezzaTags, $altezza4, "<b>" . $myTestoVerbale->Testo_Informazioni_Foto . "</b>", $border, "J", $altezzaTags);
		
		//alert ($myTestoVerbale->Testo_Informazioni_Foto);
		//$this->filePdf->Ln($altezzaTags);
		
		//$this->filePdf->Ln($altezzaTags);
		
		$this->filePdf->SetFont('Times','B',8);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_2 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_2, $border, "J", $altezzaTags);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_3 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_3, $border, "J", $altezzaTags);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_4 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_4_A, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_4_B, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_4_C, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_4_D, $border, "J", $spazionullo);
		
		if ($myTestoVerbale->Titolo_Informazioni_4)
			$this->filePdf->Ln($altezzaTags);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_5 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_A, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_B, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_C, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_D, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_E, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_F, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_G, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_H, $border, "J", $spazionullo);
		/*$this->ControllaTag ($larghezzaTags, $altezzaTags, $testoModalitaEstinzione_1, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $testoModalitaEstinzione_2, $border, "J", $spazionullo);*/
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_L, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_5_M, $border, "J", $spazionullo);
		
		if ($myTestoVerbale->Titolo_Informazioni_5)
			$this->filePdf->Ln($altezzaTags);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_6 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_6, $border, "J", $spazionullo);
		
		if ($myTestoVerbale->Titolo_Informazioni_6)
			$this->filePdf->Ln($altezzaTags);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_7 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_7, $border, "J", $spazionullo);
		
		if ($myTestoVerbale->Titolo_Informazioni_7)
			$this->filePdf->Ln($altezzaTags);
		
		$this->ControllaTag ($larghezzaTags, $altezzaTags, "<b>" . $myTestoVerbale->Titolo_Informazioni_8 . "</b>", $border, "C", $metaAltTag);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_A, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_B, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_C, $border, "J", $spazionullo);
		/*$this->ControllaTag ($larghezzaTags, $altezzaTags, $testoRicorso_2, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $testoRicorso_3, $border, "J", $spazionullo);*/
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_D, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_E, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_F, $border, "J", $spazionullo);
		$this->ControllaTag ($larghezzaTags, $altezzaTags, $myTestoVerbale->Testo_Informazioni_8_G, $border, "J", $spazionullo);
		
		
		
		
		
		
		
		
		// FOGLIO DECURTAZIONE PUNTI
		if ($this->PuntiDaDecurtare != 0 && $copyVerbale == 0)
		{
	
			//Scrivo l'allegato
			$this->filePdf->AddPage();
			$this->filePdf->SetFont('Helvetica','',11);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(100,5, $myTestoVerbale->Intestazione2, 0,0,'L',0);
			$this->filePdf->SetFont('Times','',8);
			
			$this->filePdf->SetFont('Helvetica','',11);
			$this->filePdf->LN(5);
			
			$this->filePdf->Cell(100,5, $myTestoVerbale->Intestazione1, 0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(100,5, $this->OggettoDecurtazionePunti->IndirizzoPunti, 0,0,'L',0);
			$this->filePdf->LN(5);
			
			$this->filePdf->Cell(100,5, $this->OggettoDecurtazionePunti->LocalitaPunti, 0,0,'L',0);
			$this->filePdf->LN(5);
			
			$this->filePdf->SetFont('Helvetica','B',14);
			$this->filePdf->Cell(0,5,"RACCOMANDATA A.R. o A MANI",0,0,'C',0);
			$this->filePdf->LN(4);
			$this->filePdf->Cell(0,5,"(procedura obbligatoria per la comunicazione)    ",0,0,'C',0);
			$this->filePdf->SetFont('Helvetica','',10);
			$this->filePdf->LN(10);
			$this->filePdf->Cell(20,5,"OGGETTO:",0,0,'L',0);
			$this->filePdf->Cell(0,5,"comunicazione OBBLIGATORIA dei dati anagrafici e della patente di guida del trasgressore, conducente",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(20,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,"del veicolo al momento dell'accertamento della violazione al C.d.S..",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->SetFont('Helvetica','B',11);
			$this->filePdf->Cell(20,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,"DA RESTITUIRSI ENTRO 60 GIORNI DALLA NOTIFICA DEL VERBALE",0,0,'L',0);
			$this->filePdf->SetFont('Helvetica','',11);
			$this->filePdf->LN(10);
			
			/*$proto_reg="N. $regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso]$par_definizione_cronologico_tmp$orte_verbale_velox Verbale N. $regi[Reg_Numero_Avviso]/$regi[Reg_Anno_Avviso]$par_definizione_verbale_tmp";
				
			$dt_avv_tmp=$regi[Reg_Data_Avviso];
			extract_date($dt_avv_tmp);
			if($current->Con_Tipo!='D')
			{
				$comune_nasc=strtoupper("$current->Con_Nome_Com_Nasc");
				store_date_cds($current->Con_Data_Nasc,$data_nasc_temp);
				extract_date($current->Con_Data_Nasc);
				if($comune_nasc==NULL)
				{
					$naz_nasc=strtoupper("$current->Con_Nome_Paese_Nasc");
					$cmn_nasc="in $naz_nasc il $current->Con_Data_Nasc";
				}
				else
				{
					$pro_nasc=single_answer_query("select Pro_Sigla from comune,provincia where Com_CC='$current->Con_Com_Nasc' and Pro_Progr=Com_Prov");
					$cmn_nasc="a $comune_nasc ($pro_nasc) il $current->Con_Data_Nasc";
				}
			}
			else
			{
				$cmn_nasc="";
			}
			if($current->Con_Tipo=='M')
			{
				$artico_tmp="il sottoscritto";
				$nome_trasgre_tmp="$cognome_upp $nome_upp";
				$nato_a="nato";
			}
			elseif($current->Con_Tipo=='F')
			{
				$artico_tmp="la sottoscritta";
				$nome_trasgre_tmp="$cognome_upp $nome_upp";
				$nato_a="nata";
			}
			else
			{
				$artico_tmp="il/la sottoscritto/a";
				$nome_trasgre_tmp="$cognome_upp $nome_upp";
				$nato_a="";
			}
			$civico_obbli="$current->Civ_Num$esp_num$esp_lett$scala$interno$piano";
			$indirizzo_res_obbli=strtoupper("$res_obbligato->Top_Nome $res_obbligato->Via_Nome $civico_obbli");
			if($current->Con_Tipo!='D')
			{
				$resident="residente in";
			}
			else
			{
				$resident="con sede in";
			}*/
			
			$testo_1 = "A seguito notifica di verbale di accertamento di violazione al C.d.S.. ";
			$testo_1 .= "Cron. N. " . $this->NumeroViolazione;
			$testo_1 .= " Verbale N. " . $this->NumeroVerbale;
			$testo_1 .= " del  " . $this->DataViolazione;
			$testo_1 .= " commessa con il veicolo targato  " . $this->TargaVeicolo;
			$testo_1 .= ", il/la sottoscritto/a " . $this->OggettoDecurtazionePunti->NomeTrasgressore;
			if ($this->OggettoDecurtazionePunti->DataNascitaTrasg != "" && $this->OggettoDecurtazionePunti->DataNascitaTrasg != "0000-00-00")
			{
				$testo_1 .= ", nato/a a " . $this->OggettoDecurtazionePunti->LuogoNascitaTrasg;
				$testo_1 .= " il " . $this->OggettoDecurtazionePunti->DataNascitaTrasg;
				$testo_1 .= " residente in " . $this->OggettoDecurtazionePunti->ComuneResidenzaTrasg;
			}
			else 
				$testo_1 .= " con sede in " . $this->OggettoDecurtazionePunti->ComuneResidenzaTrasg;
			
			$testo_1 .= " " . $this->OggettoDecurtazionePunti->IndirizzoResidenzaTrasg;
			$testo_1 .= " prov. " . $this->OggettoDecurtazionePunti->ProvinciaResidenzaTrasg;
			$testo_1 .= ", titolare della patente di guida cat. _____________ n� __________________ ";
			$testo_1 .= " rilasciata in data _________________________________ da(*) ____________________________________ ";
			$testo_1 .= " valida fino al __________________ (che si allega in copia) Tel. ______/_______________, ";
			$testo_1 .= " in qualit� di (_____________________________________________________):";
			
			
			$this->filePdf->MultiCell(0,5, $testo_1, 0,'J',0);
			$this->filePdf->Cell(0,5,"[ ] proprietario del veicolo",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"[ ] legale rappresentante della ditta denominata ________________________________________________",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"    con sede in __________________________________________________________________________",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"    Via ___________________________________________ n� __________ Tel.______/_______________ ",0,0,'L',0);
			$this->filePdf->SetFont('Helvetica','B',10);
			$this->filePdf->LN(5);
			$testo_2 = "consapevole dell sanzioni penali comminate in caso di dichiarazioni non veritiere e falsit� negli atti, ";
			$testo_2 .= "richiamate dall'art.76 del D.P.R. 445 del 28 Dicembre 2000, sotto la prorpia responsabilit� dichiara";
			$this->filePdf->MultiCell(0,5,"$testo_2",0,'J',0);
			$this->filePdf->SetFont('Helvetica','',10);
			$this->filePdf->Cell(0,5,"che il trasgressore, conducente del veicolo al momento dell'accertamento della violazione era:",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"[ ] se stesso",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"[ ] il Sig./la Sig.ra (cognome) __________________________________ (nome) ________________________________",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"nato a _____________________________ il ___/___/_____ residente in _____________________________________",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"Via ___________________________________________ n� __________ Prov. _____ Tel.______/_________________",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"titolare della patente di guida cat.______ n�__________________ rialsciata in data _______________",0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"da (*) ______________________________________ valida fino al ____________________ (che si allega in copia)",0,0,'L',0);
			$this->filePdf->LN(10);
			$this->filePdf->Cell(0,5,"Data _____/_____/________",0,0,'L',0);
			$this->filePdf->LN(10);
			$this->filePdf->Cell(0,5,"Firma dell'obbligato in solido (proprietario del veicolo-legale rapp.te della ditta) ________________________________",0,0,'L',0);
			$this->filePdf->LN(10);
			$this->filePdf->Cell(0,5,"Firma del trasgressore (conducente del veicolo al momento della commessa violazione) ________________________",0,0,'L',0);
			$this->filePdf->SetFont('Helvetica','B',10);
			$this->filePdf->LN(10);
			$this->filePdf->Cell(0,5,"===AVVERTENZE IMPORTANTI=================================================================",0,0,'L',0);
			$this->filePdf->LN(5);
			$testo_3="1) Nel caso in cui non si proceda alla corretta compilazione della presente comunicazione, ovvero non si ottemperi all�obbligo di comunicare i dati anagrafici e della patente del trasgressore ENTRO 60 GIORNI DALLA NOTIFICA DEL VERBALE, si proceder� ai sensi del dell�art. 126-bis del Codice della Strada e quindi alla notifica di un nuovo verbale con irrogazione della conseguente sanzione amministrativa.";
			$this->filePdf->MultiCell(0,5,"$testo_3",0,'J',0);
			$this->filePdf->Cell(0,5,"===============SUL RETRO SEGUONO ULTERIORI ISTRUZIONI PER LA COMPILAZIONE================",0,0,'L',0);
			$this->filePdf->AddPage();
			$this->filePdf->LN(5);
			$this->filePdf->SetFont('Times','',8);
			//$this->filePdf->Cell(0,3,"Pag. 4/$nm_tot_pg",0,0,'R',0);
			$this->filePdf->SetFont('Helvetica','B',10);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(0,5,"=================================ISTRUZIONI PER LA COMPILAZIONE=============================",0,0,'L',0);
			$this->filePdf->SetFont('Helvetica','',10);
			$this->filePdf->LN(10);
			$testo_4 = "(*) Indicare  Prefettura di........., oppure DTTSIS di........., oppure Motorizzazione civile di........., ovvero altra autorit� che ha rilasciato il documento in caso di patente di guida rilasciata da uno stato estero.
		
1) Nel caso in cui il proprietario del veicolo (obbligato in solido), fosse stato alla guida del veicolo al momento della commessa violazione, ai fini dell�applicazione delle conseguenze indicate nel verbale (decurtazione dei punti e/o sospensione della patente di guida), questi dovr� compilare la dichiarazione indicando i propri dati anagrafici e quelli della patente di guida, allegando un documento d�identit� valido (si consiglia copia fronte retro della patente di guida, al fine di poter sopperire ad eventuali mancanza nella compilazione della dichiarazione). La fotocopia del documento d�identit� dovr� recare inoltre la seguente dichiarazione �Io sottoscritto/a .........nato/a........il......... residente in.......... via....... n�....... DICHIARO che la fotocopia del presente documento � conforme all�originale in mio possesso - Firma ........luogo e data ...........�
		
2) Nel caso in cui il proprietario del veicolo (obbligato in solido), non fosse stato alla guida del veicolo al momento della commessa violazione, ai fini dell�applicazione delle conseguenze indicate nel verbale (decurtazione dei punti e/o sospensione della patente di guida), questi dovr� compilare la dichiarazione indicando anche i dati anagrafici e della patente di guida dell�effettivo trasgressore. Se il trasgressore proceder� alla firma congiunta della dichiarazione completa dei dati anagrafici e della patente di guida, di copia di un documento d�identit� (si consiglia fronte retro della patente di guida) integrato della prevista dichiarazione di conformit�, il verbale si considerer� notificato anche al trasgressore dichiarato.
Nel caso in cui l�effettivo trasgressore non voglia sottoscrivere la presente dichiarazione, o non voglia presentarne egli stesso una propria citando i riferimenti del verbale notificato, si dovr� procedere alla notifica del verbale anche all�effettivo trasgressore, come dichiarato, con ulteriore aggravio di spese a carico delle parti inadempienti.
		
3) IMPORTANTE � Come documento d�identit� si consiglia di allegare copia fronte retro della patente di guida, al fine di poter sopperire ad eventuali mancanze nella compilazione della dichiarazione.
Le fotocopie dei documenti d�identit� dovranno recare inoltre la seguente dichiarazione �Io sottoscritto/a .........nato/a........il......... residente in.......... via....... n�....... DICHIARO che la fotocopia del presente documento � conforme all�originale in mio possesso - Firma ........luogo e data ...........�
		
4) Le dichiarazioni, per poter essere ritenute valide, dovranno essere sempre effettuate nella forma dell�autocertificazione (sottoscrizione con allegato un documento d�identit� valido).
		
5) Le dichiarazioni devono essere firmate in originale e restituite (possibilmente con raccomandata, o a mani proprie), all'Ufficio di Polizia o all'Ufficio Protocolo dell'ente accertatore, entro 60 giorni dalla notifica del verbale.
";
			$this->filePdf->MultiCell(0,5,"$testo_4",0,'J',0);
			$this->filePdf->LN(5);
			$this->filePdf->SetFont('Helvetica','B',10);
			$testo_5="6) Nel caso in cui non si proceda alla corretta compilazione della presente comunicazione, ovvero non si ottemperi all�obbligo di comunicare i dati anagrafici e della patente del trasgressore ENTRO 60 GIORNI DALLA NOTIFICA DEL VERBALE, si proceder� ai sensi dell�art. 126-bis del Codice della Strada e quindi alla notifica di un nuovo verbale con irrogazione della conseguente sanzione amministrativa.";
			$this->filePdf->MultiCell(0,5,"$testo_5",0,'J',0);
				
			
		}
		// FINE FOGLIO DECURTAZIONE PUNTI
		
		
		
		
		if ($stampaConFoto == "Y" && $this->stampaconfoto != NULL)
			$this->StampaPaginaFotogramma ($mioIdFotogrammiCds);
	}
	
	function StampaPaginaFotogramma ($idFoto)  //  arriva id del fotogramma ( fotogrammi_cds )
	{
		if ($idFoto == NULL) return;
		
		$oggettoFoto = new Fotogrammi_Cds($idFoto);
		$nomeFoto = explode ("**", $oggettoFoto->Fot_Stringa_Foto);
		if (count($nomeFoto) == 2)
		{
			$tipoFoto = "SEMAFORO";
		}
		else 
		{
			$tipoFoto = "VELOX";
		}
		
		for ($k = 0; $k < count($nomeFoto); $k++)
		{
			$percorsoNomeFoto = $this->stampaconfoto->LinkFotoFtpOppureFotocds($nomeFoto[$k], $this->comune);
			
			$dasplittare = explode ("_", $nomeFoto[$k]);
			$queryRil = "SELECT Ril_Tipo, Ril_Marca FROM rilevatori_velocita WHERE Ril_Matricola_Sistema = '$dasplittare[0]'";
			$resRil = safe_query($queryRil);
			$rigaRil = mysql_fetch_assoc($resRil);
			$stringamatricola = $rigaRil['Ril_Tipo'] . " " . $rigaRil['Ril_Marca'];
			
			$this->filePdf->AddPage("L");
			
			//Carta Intestata
			$this->filePdf->SetFont('Times','I',10);
			$this->filePdf->SetTextColor(255,0,0);
			
			if ($this->stemmacomune != "")
			{
				$logo_comune = $this->stemmacomune;
				$dimensioni = getimagesize($logo_comune);
				$larghezza = $dimensioni[0];
				$altezza = $dimensioni[1];
				//alert ("  $larghezza  x  $altezza");
				if ($altezza > 15)
				{
					$molt = $larghezza / $altezza;
					$altezza = 15;
					$larghezza = $altezza * $molt;
					//alert ("  $larghezza  x  $altezza");
				}
				if ($larghezza > 15)
				{
					$molt = $larghezza / $altezza;
					$larghezza = 15;
					$altezza = $altezza / $molt;
					//alert ("  $larghezza  x  $altezza");
				}
				
				$this->filePdf->Image($logo_comune,15,15,$larghezza,$altezza,'jpg');
			}
		
			$border = 0;
			$this->filePdf->LN(5);
			$this->filePdf->SetFont('Helvetica','B',36);
			$this->filePdf->Cell(280,25, $this->OggettoTesto->Intestazione1,$border,0,'C',0);
			//$this->filePdf->Cell(280,25, "Provincia di Savona",$border,0,'C',0);
			$this->filePdf->LN(20);
			$this->filePdf->SetTextColor(0,0,0);
			$this->filePdf->LN(5);
			//Fine Carta Intestata
			
			
			$maxLarghezzaFoto = 170;
			$maxAltezzaFoto = 110;
			
			if ($percorsoNomeFoto != "")
			{
				//echo "<br>$percorsoNomeFoto";
				$percorsoLungo = /*$_SERVER['DOCUMENT_ROOT'] .*/ "../.." . $percorsoNomeFoto;
				//echo "<br>$mmmm";
				
				/*if ($_SESSION['username'] == "marcom")
				{
					if (file_exists($percorsoLungo))
						$dimensioni = getimagesize($percorsoLungo);
					else 
					{
						echo "<br>in locale non ho la foto<br>";
						$dimensioni = array (10,10);
					}
				}
				else */
					$dimensioni = getimagesize($percorsoLungo);
				$larghezza = $dimensioni[0];
				$altezza = $dimensioni[1];
				//alert ("A  $larghezza  x  $altezza");
			}
			else if ($_SESSION['username'] == "marcom")
			{
				echo "<br>in locale non ho la foto (2)  $percorsoNomeFoto     ->  $nomeFoto[$k]<br>";
				$larghezza = $maxLarghezzaFoto;
				$altezza = $maxAltezzaFoto;
			}
			else 
			{
				$larghezza = $maxLarghezzaFoto;
				$altezza = $maxAltezzaFoto;
			}
		
			if ($altezza > $maxAltezzaFoto)
			{
				$molt = $larghezza / $altezza;
				$altezza = $maxAltezzaFoto;
				$larghezza = $altezza * $molt;
				//alert ("B  $larghezza  x  $altezza");
			}
			if ($larghezza > $maxLarghezzaFoto)
			{
				$molt = $larghezza / $altezza;
				$larghezza = $maxLarghezzaFoto;
				$altezza = $altezza / $molt;
				//alert ("C  $larghezza  x  $altezza");
			}
		
			if ($percorsoNomeFoto != "")
			{
				//echo "<br>$percorsoNomeFoto";
				$percorsoLungo = /*$_SERVER['DOCUMENT_ROOT'] .*/ "../.." . $percorsoNomeFoto;
				//echo "<br>$percorsoLungo";
				/*if ($_SESSION['username'] == "marcom")
				{
					if (file_exists($percorsoLungo))
						$this->filePdf->Image($percorsoLungo, 10, 40, $larghezza, $altezza, 'JPG');
					else 
					{
						echo "<br>in locale non ho la foto (2)  $percorsoLungo  <br>";
					}
				}
				else */
					$this->filePdf->Image($percorsoLungo, 10, 40, $larghezza, $altezza, 'JPG');
				//return;
			}
			else if ($_SESSION['username'] == "marcom")
			{
				echo "<br>in locale non ho la foto (2)  $percorsoNomeFoto    ->   $nomeFoto[$k]<br>";
			}
		
			$border = 0;
			
			if ($tipoFoto == "SEMAFORO")
			{
				$dasplittare = explode ("**", $oggettoFoto->Fot_Tempi_Dal_Rosso);
				$tempo1 = $dasplittare[0];
				$tempo2 = $dasplittare[1];
				$rigaUno = "TEMPO TRASCORSO DALLO SCATTO";
				if ($tempo1 != "" && $tempo1 != '0') $rigaDue = "DEL ROSSO:   $tempo1  decimi di secondo";
				else $rigaDue = "DEL ROSSO:  vedi foto";
				$indiceFoto = "FOTOGRAMMA " . ($k+1) . "/2";
			}
			else if ($tipoFoto == "VELOX")
			{
				$tempo1 = $oggettoFoto->Fot_Limite_Velocita;
				$tempo2 = $oggettoFoto->Fot_Velocita_Rilevata;
				$rigaUno = "LIMITE VELOCITA': $tempo1 km/h";
				$rigaDue = "VELOCITA' RILEVATA: $tempo2 km/h";
				$indiceFoto = "";
			}
		
			$this->filePdf->SetFont('Helvetica','B',14);
		
			$this->filePdf->Cell($larghezza + 10, 7, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 7, "CRONOLOGICO VERBALE", $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 7, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 7, $this->NumeroVerbale, $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, "", $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 7, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 7, "NOME FILE", $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 7, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 7, $nomeFoto[$k], $border, 1, 'L', 0);
			$this->filePdf->Cell($larghezza + 10, 20, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 20, "TARGA: " . $oggettoFoto->Fot_Targa, $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, "", $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, $indiceFoto, $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, "DATA: " . $this->DataViolazione, $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, "ORA: " . $this->OraViolazione, $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, "", $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 7, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 7, $rigaUno, $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 7, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 7, $rigaDue, $border, 1, 'L', 0);
		
			$this->filePdf->Cell(280, 5, "", $border, 1, 'L', 0);
		
			//$this->filePdf->Cell(280, 10, $indirizzo, $border, 1, 'L', 0);$stringamatricola
			$this->filePdf->Cell($larghezza + 10, 10, "", $border, 0, 'C', 0);
			$this->filePdf->Cell(90, 10, "", $border, 1, 'L', 0);
		
			$this->filePdf->Cell($larghezza + 10, 10, $indirizzo, $border, 0, 'L', 0);
			$this->filePdf->Cell(90, 10, "SISTEMA: $stringamatricola", $border, 1, 'L', 0);
		}
	}
	
	function ApriBollettinoPdf ($comune)
	{
		$this->filePdf = new FPDF("L", "mm", "A4");
		$this->comune = $comune;
	}
	
	function StampaSingoloBollettino ()
	{
		if ($this->filePdf == NULL)
		{
			alert ("mancata creazione pdf3");
			return;
		}
		$this->filePdf->AddPage();
		$this->filePdf->SetMargins (0, 3, 5);
		
		for ($z = 0; $z < 2; $z++)
		{
			$this->filePdf->SetFont('Courier','B',17);
		
			if ($z == 0)
			{
				$importo = $this->OggettoBollettino->ImportoNumeroBoll1;
				$lettere = $this->OggettoBollettino->ImportoLettereBoll1;
				
				$this->filePdf->LN(1);
			}
			else if ($z == 1)
			{
				$importo = $this->OggettoBollettino->ImportoNumeroBoll2;
				$lettere = $this->OggettoBollettino->ImportoLettereBoll2;
				
				$this->filePdf->LN(38);    //Riga da decommantare all'arrivo dei bollettini fogli A4
				if ($_SESSION['username'] == "marcom")
					alert ($importo . " e 1");
			}
			
			$this->filePdf->Cell(24,5,"",0,0,'L',0); // uso queste celle vuote per lasciare lo spazio giusto in mm
			$this->filePdf->Cell(83,5,$this->OggettoBollettino->ContoCorrente,0,0,'L',0);
			
			$virgola = strpos($importo,",");//Cerco la posizione della stringa
			$decimali = substr($importo,($virgola+1));//Recupero i decimali
			$interi = substr($importo,0,$virgola);//Recupero gli interi
			$this->filePdf->Cell(9,5,$interi,0,0,'R',0);
			$this->filePdf->Cell(1,5,"",0,0,'L',0);
			$this->filePdf->Cell(9,5,$decimali,0,0,'L',0);
			$this->filePdf->Cell(39,5,"",0,0,'L',0);
			
			$this->filePdf->Cell(107,5,$this->OggettoBollettino->ContoCorrente,0,0,'L',0);
			$this->filePdf->Cell(15,6,$interi,0,0,'R',0);
			$this->filePdf->Cell(1,6,"",0,0,'L',0);
			$this->filePdf->Cell(10,6,$decimali,0,0,'L',0);
			
			$this->filePdf->SetFont('Courier','B',10);
			$this->filePdf->LN(7);
			$this->filePdf->Cell(18,5,"",0,0,'L',0);
			$this->filePdf->Cell(118,5,$lettere,0,0,'L',0);
			$this->filePdf->Cell(25,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,$lettere,0,0,'L',0);
			
			$this->filePdf->LN(6);
			$this->filePdf->Cell(18,5,"",0,0,'L',0);
			$this->filePdf->Cell(119,5,$this->OggettoBollettino->IntestatarioConto,0,0,'L',0);
			$this->filePdf->SetFont('Courier','B',11);
			$this->filePdf->Cell(0,10,$this->OggettoBollettino->IntestatarioConto,0,0,'L',0);
			$this->filePdf->SetFont('Courier','B',10);
			
			
			$datiPersona = $this->OggettoBollettino->CognomeLimitato . " " . $this->OggettoBollettino->NomeLimitato;
			$datiPersona .= " " . $this->OggettoBollettino->CapLimitato . " " . $this->OggettoBollettino->ComuneLimitato;
			$datiPersona .= " " . $this->OggettoBollettino->IndirizzoLimitato;
			$datiPersona_a = substr($datiPersona, 0, 50);
			$datiPersona_b = substr($datiPersona, 0, 70);
			$this->filePdf->SetFont('Courier','B',10);
			$this->filePdf->LN(7);
			$this->filePdf->Cell(18,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,$datiPersona_a,0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(152,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,8,$datiPersona_b,0,0,'L',0);
			
			$nomeComune = strtoupper(get_Com_Nome($this->comune));
	
			$this->filePdf->LN(3);
			$this->filePdf->Cell(58,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,$nomeComune,0,0,'L',0);
			$this->filePdf->LN(6);
			$this->filePdf->Cell(136,5,"",0,0,'L',0);
			$this->filePdf->SetFont('Courier','',17);
			$this->filePdf->Cell(0,8,$nomeComune,0,0,'L',0);
			$this->filePdf->LN(4);
			$this->filePdf->SetFont('Courier','',19);
			$this->filePdf->Cell(84,5,$this->OggettoBollettino->CodiceFiscale,0,0,'L',0);
			$this->filePdf->Cell(137,5,"",0,0,'L',0);
			$this->filePdf->SetFont('Courier','',19);
			$this->filePdf->Cell(0,12,$this->OggettoBollettino->CodiceFiscale,0,0,'L',0);
			$this->filePdf->SetFont('Courier','B',10);
			$this->filePdf->LN(7);
			$this->filePdf->Cell(41,5,"",0,0,'L',0);
			
			$dataBoll_a = date ("d/m/Y");
			$dataBoll_b = date ("dm") + substr(date ("Y"), 2, 2);
			$this->filePdf->Cell(0,5,$this->OggettoBollettino->NumeroCronologico,0,0,'L',0); //sinistra prima bollettino
			$this->filePdf->LN(8);
			$this->filePdf->Cell(5,5,"",0,0,'L',0);
			$this->filePdf->Cell(30,5,$dataBoll_a,0,0,'L',0); //sinistra primo bollettino
			$this->filePdf->SetFont('Courier','B',18);
			$this->filePdf->LN(1);
			
			$this->filePdf->Cell(205,5,"",0,0,'L',0);
			
			$this->filePdf->SetFont('Courier','B',11);
			$this->filePdf->Cell(60,5,$this->OggettoBollettino->NumeroCronologico,0,0,'R',0); //destra prima bollettino
			$this->filePdf->Cell(20,5,"$dataBoll_b",0,0,'R',0); //destra prima bollettino
			
			$this->filePdf->LN(12);
			$this->filePdf->SetFont('Courier','B',11);
			$this->filePdf->Cell(62,5,"",0,0,'L',0);
			$this->filePdf->Cell(2,5,"X",0,0,'L',0);
			
			$this->filePdf->Cell(1,5," CDS",0,0,'L',0);
			$this->filePdf->Cell(220,5,"",0,0,'L',0);
			
			$this->filePdf->Cell(7,1,"X CDS",0,0,'L',0);
		}
		
		//Secondo bollettino******************************************************************//
		// posso scegliere se fare BOLLETTINO UNICO o 2 BOLLETTINI (in base a scelta del COMUNE)
		
		if (0)
		//if ($cur_par->Par_Numero_Bollettini != 1)
		{
			$totale_ridotto_uno=$regi[Reg_Totale_Ridotto]+$notif[Not_Spese_Notifica]+$notif[Not_Spese_Ricerca]+$cur_par->Par_Spese_Doppia_Notifica_Verbale-$gia_pagato;
		
			if($totale_ridotto_uno<0){$totale_ridotto_uno=$cur_par->Par_Spese_Doppia_Notifica_Verbale;}
			$totale_ridotto_uno_it=number_format($totale_ridotto_uno,2,',','.');
			$this->filePdf->LN(39);    //Riga da decommantare all'arrivo dei bollettini fogli A4
			$this->filePdf->SetFont('Courier','B',17);
			$this->filePdf->Cell(24,5,"",0,0,'L',0); // uso queste celle vuote per lasciare lo spazio giusto in mm
			$this->filePdf->Cell(83,5,"$num_ccp",0,0,'L',0);
				
			$virgola=strpos($totale_ridotto_uno_it,",");//Cerco la posizione della stringa.
			$decimali=substr($totale_ridotto_uno_it,($virgola+1));//Recupero i decimali.
			$interi=substr($totale_ridotto_uno_it,0,$virgola);//Recupero gli interi
			$decimali_due=$decimali;
			$interi_due=$interi;
			//alert ($interi);
				
			$this->filePdf->Cell(9,5,"$interi",0,0,'R',0);
			$this->filePdf->Cell(1,5,"",0,0,'L',0);
			$this->filePdf->Cell(9,5,"$decimali",0,0,'L',0);
			$this->filePdf->Cell(39,5,"",0,0,'L',0);
			$this->filePdf->Cell(107,5,"$num_ccp",0,0,'L',0);
				
			$this->filePdf->Cell(15,6,"$interi",0,0,'R',0);
			$this->filePdf->Cell(1,6,"",0,0,'L',0);
			$this->filePdf->Cell(10,6,"$decimali",0,0,'L',0);
				
			$num_lett=trasforma_numero($interi,$c,$tipo_stampante,$fh);
			$nuovo_num="$num_lett/$decimali";
			$this->filePdf->SetFont('Courier','B',10);
			$this->filePdf->LN(7);
			$this->filePdf->Cell(18,5,"",0,0,'L',0);
			$this->filePdf->Cell(118,5,"$nuovo_num",0,0,'L',0);
			$this->filePdf->Cell(21,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,"$nuovo_num",0,0,'L',0);
				
			$intestazione=substr("$intestazione",0,53);        // limito la lunghezza del campo
			$this->filePdf->LN(6);
			$this->filePdf->Cell(18,5,"",0,0,'L',0);
			$this->filePdf->Cell(118,5,"$intestazione",0,0,'L',0);
			$intestazione_uno=strtoupper($intestazione);
			if(strlen($intestazione)>53)
			{
				$intestazione_uno=substr($intestazione_uno,0,53);
			}
				
			$this->filePdf->SetFont('Courier','B',11);
			$this->filePdf->Cell(0,8,"$intestazione_uno",0,0,'L',0);
			$this->filePdf->SetFont('Courier','B',11);
				
			$spese_notifica_it=number_format($cur_par->Par_Spese_Doppia_Notifica_Verbale,2,',','.');
			$query_art_bol="select * from tariffa_cds where Tar_Progr=$regi[Reg_Articolo_Uno]";
			$ris_art_bol=safe_query($query_art_bol);
			$art_bol=mysql_fetch_assoc($ris_art_bol);
			store_date_cds($regi[Reg_Data_Avviso],$data_tmp);
			extract_date($regi[Reg_Data_Avviso]);
				
				
			if(($regi[Reg_Rif_Numero_Avviso]==0 or $regi[Reg_Rif_Numero_Avviso]==NULL) and ($regi[Reg_Protocollo]!=0 and $regi[Reg_Protocollo]!=NULL))
			{
		
					
				$protocollo_reg="N. $regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso] del $regi[Reg_Data_Avviso] Tg.$regi[Reg_Targa_Veicolo]";
					
				$numero_acc1="$regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso]$par_definizione_cronologico_tmp";
					
				$numero_acc="$regi[Reg_Progr_Registro]$regi[Reg_Anno_Avviso]$par_definizione_cronologico";
		
				$aa_acc=substr($regi[Reg_Data_Avviso],8,2);
				$mm_acc=substr($regi[Reg_Data_Avviso],3,2);
				$gg_acc=substr($regi[Reg_Data_Avviso],0,2);
				$data_acc="$gg_acc$mm_acc$aa_acc";
				$data_acc1=$regi[Reg_Data_Avviso];
			}
			elseif(($regi[Reg_Rif_Numero_Avviso]!=0 and $regi[Reg_Rif_Numero_Avviso]!=NULL) and ($regi[Reg_Protocollo]!=0 and $regi[Reg_Protocollo]!=NULL))
			{
		
					
				$protocollo_reg="N. $regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso] del $regi[Reg_Data_Avviso] Tg.$regi[Reg_Targa_Veicolo]";
					
				$numero_acc1="$regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso]$par_definizione_cronologico_tmp";
					
				$numero_acc="$regi[Reg_Progr_Registro]$regi[Reg_Anno_Avviso]$par_definizione_cronologico";
		
				$aa_acc=substr($regi[Reg_Data_Avviso],8,2);
				$mm_acc=substr($regi[Reg_Data_Avviso],3,2);
				$gg_acc=substr($regi[Reg_Data_Avviso],0,2);
				$data_acc="$gg_acc$mm_acc$aa_acc";
				$data_acc1=$regi[Reg_Data_Avviso];
			}
			elseif(($regi[Reg_Rif_Numero_Avviso]!=0 and $regi[Reg_Rif_Numero_Avviso]!=NULL) and ($regi[Reg_Protocollo]==0 or $regi[Reg_Protocollo]==NULL))
			{
		
					
				$protocollo_reg="N. $regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso] Rif.$regi[Reg_Rif_Numero_Avviso]/$regi[Reg_Anno_Avviso] del $regi[Reg_Data_Avviso] Tg.$regi[Reg_Targa_Veicolo]";
					
				$numero_acc1="$regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso]$par_definizione_cronologico_tmp";
					
				$numero_acc="$regi[Reg_Progr_Registro]$regi[Reg_Anno_Avviso]$par_definizione_cronologico";
					
				$aa_acc=substr($regi[Reg_Data_Avviso],8,2);
				$mm_acc=substr($regi[Reg_Data_Avviso],3,2);
				$gg_acc=substr($regi[Reg_Data_Avviso],0,2);
				$data_acc="$gg_acc$mm_acc$aa_acc";
				$data_acc1=$regi[Reg_Data_Avviso];
			}
			else
			{
					
				$protocollo_reg="N. $regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso] Anno $regi[Reg_Anno_Avviso] del $regi[Reg_Data_Avviso] Tg.$regi[Reg_Targa_Veicolo]";
					
				$numero_acc1="$regi[Reg_Progr_Registro]/$regi[Reg_Anno_Avviso]$par_definizione_cronologico_tmp";
					
				$numero_acc="$regi[Reg_Progr_Registro]$regi[Reg_Anno_Avviso]$par_definizione_cronologico";
					
				$aa_acc=substr($regi[Reg_Data_Avviso],8,2);
				$mm_acc=substr($regi[Reg_Data_Avviso],3,2);
				$gg_acc=substr($regi[Reg_Data_Avviso],0,2);
				$data_acc="$gg_acc$mm_acc$aa_acc";
				$data_acc1=$regi[Reg_Data_Avviso];
			}
				
				
			$nome_upp=strtoupper(stripslashes($current->Con_Nome));
			$cognome_upp=strtoupper(stripslashes($current->Con_Cognome));
			$indirizzo_upp=strtoupper($indirizzo);
			$indirizzo_cap_upp=strtoupper($indirizzo_cap);
			$cognome_lim=substr("$cognome_upp",0,28); // limito la lunghezza del campo
			$nome_lim=substr("$nome_upp",0,28);        // limito la lunghezza del campo
			$ind_prova="$address->Com_Nome";
			$ind_prova_up=strtoupper($ind_prova);
			$indirizzo_lim=substr("$indirizzo_upp",0,23);   				// limito la lunghezza del campo
			$comune_upp=strtoupper($address->Com_Nome);
			$comune_lim=substr("$comune_upp",0,23);  				// limito la lunghezza del campo
				
			$ennesimo_comune="COMUNE DI $nome_comune_upp";
			$ennesimo_comune=substr("$ennesimo_comune",0,53);   // limito la lunghezza del campo
				
			$this->filePdf->SetFont('Courier','B',10);
			$this->filePdf->LN(7);
			$this->filePdf->Cell(18,5,"",0,0,'L',0);
			$str_contr=substr("$cognome_lim $nome_lim $cap $comune_lim   $indirizzo_lim",0,45);
			$this->filePdf->Cell(0,5,$str_contr,0,0,'L',0);
			$this->filePdf->LN(5);
			$this->filePdf->Cell(152,5,"",0,0,'L',0);
			$str_contr=substr("$cognome_lim $nome_lim   $cap $comune_lim   $indirizzo_lim",0,60);
			$this->filePdf->Cell(0,8,$str_contr,0,0,'L',0);
				
			$this->filePdf->LN(3);
			$this->filePdf->Cell(58,5,"",0,0,'L',0);
			$this->filePdf->Cell(0,5,"$nome_comune",0,0,'L',0);
			$this->filePdf->LN(6);
			$this->filePdf->Cell(136,5,"",0,0,'L',0);
			$this->filePdf->SetFont('Courier','',17);
			$nome_comune_upp=strtoupper($nome_comune);
			$this->filePdf->Cell(0,8,"$nome_comune_upp",0,0,'L',0);
			$this->filePdf->LN(4);
			$this->filePdf->SetFont('Courier','',19);
			if($current->Con_CDF!=NULL and $current->Con_CDF!='00000000000')
			{
				$this->filePdf->Cell(84,4,"$current->Con_CDF",0,0,'L',0);
			}
			else
			{
				$this->filePdf->Cell(84,4,"",0,0,'L',0);
			}
			$this->filePdf->Cell(137,5,"",0,0,'L',0);
			$this->filePdf->SetFont('Courier','',19);
			if($current->Con_CDF!=NULL and $current->Con_CDF!='00000000000')
			{
				$this->filePdf->Cell(0,12,"$current->Con_CDF",0,0,'L',0);
			}
			$this->filePdf->SetFont('Courier','B',10);
			$this->filePdf->LN(7);
			$this->filePdf->Cell(43,5,"",0,0,'L',0);
				
			$this->filePdf->Cell(0,5,"$c/$regi[Reg_Anno]/$regi[Reg_Progr_Registro]$par_definizione_cronologico_tmp/2",0,0,'L',0);
				
			$this->filePdf->LN(8);
			$this->filePdf->Cell(5,5,"",0,0,'L',0);
			$this->filePdf->Cell(30,5,"$data_acc1",0,0,'L',0);
			$this->filePdf->SetFont('Courier','B',18);
			$this->filePdf->LN(1);
			$this->filePdf->Cell(205,5,"",0,0,'L',0);
				
			$this->filePdf->SetFont('Courier','B',11);
			$this->filePdf->Cell(60,6,"$c/$regi[Reg_Anno]/$regi[Reg_Progr_Registro]$par_definizione_cronologico_tmp/2",0,0,'R',0);
				
			$this->filePdf->Cell(20,6,"$data_acc",0,0,'R',0);
				
				
			$this->filePdf->LN(12);
			$this->filePdf->SetFont('Courier','B',11);
			$this->filePdf->Cell(62,5,"",0,0,'L',0);
			$this->filePdf->Cell(2,5,"X",0,0,'L',0);
				
			$this->filePdf->Cell(1,5," CDS",0,0,'L',0);
			$this->filePdf->Cell(220,5,"",0,0,'L',0);
				
			$this->filePdf->Cell(7,1,"X CDS",0,0,'L',0);
		}
	}
	
}

if ($formato_carta == 1)  //  flusso dati
{
	$titoloFinestra = "Invio Flusso Verbali C.D.S.";
}
else if ($formato_carta == 3)  //  stampa pdf
{
	$titoloFinestra = "Stampa Verbali C.D.S.";
}



if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:accesso_negato.php");
		die;
	}
	//assegno alla variabile $a il valore della variabile $anni passata come sessione
	$a=$anni;
	
if ($_SESSION['username'] == "marcom")
{
	$nonfarlosemarco = 1;
	if ($stampa == 2)  // se definitiva
		alert ("attenzione: per l'utente marcom non verranno modificate tabelle sul database e non verranno salvati i file su STAMPATI!");
}
else $nonfarlosemarco = 0;

//$myClassVerbale = new TestoMioVerbale ();

$NuovoListone = new NuovoTestoMyVerbale ();
	
	//alertAllGlobalVariables();

?>
<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <title><?=$titoloFinestra?></title>
    <LINK REL=StyleSheet HREF="../stili.css" TYPE="text/css" MEDIA=screen>
    <script type="text/javascript" language="javascript" src="../libreria.js"></script>
    <script type="text/javascript" language="javascript" src="../jquery_1.7.2.js"></script>
	<script>
	function cambiacoloretd (campo, num)
	{
		var area = $("#" + campo + num);
		//alert (area);
		for (var i = 1; i < num; i++)
		{
			var altricampi = $("#" + campo + i);
			altricampi.attr("style", "background-color:rgb(0,250,150);");
		}
		area.attr("style", "background-color:rgb(0,250,150);");
	}
	</script>
</head>

<body class="finestra">

<table class="stileborder" width="100%" align="center">
<tr>
	<td width="25%">
		&nbsp;
	</td>
	<td width="50%">
					<table class="stileborder" align="center" border="1">
					<tr>
						<td width="10%">
							<div id="divbis1"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis2"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis3"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis4"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis5"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis6"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis7"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis8"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis9"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divbis10"><label>&nbsp;</label></div>
						</td>
					</tr>
					</table>
	</td>
	<td width="25%">
		&nbsp;
	</td>
</tr>
</table>

<table class="stileborder" width="100%" align="center">
<tr>
	<td width="25%">
		&nbsp;
	</td>
	<td width="50%">
					<table class="stileborder" align="center" border="1">
					<tr>
						<td width="10%">
							<div id="divdiv1"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv2"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv3"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv4"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv5"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv6"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv7"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv8"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv9"><label>&nbsp;</label></div>
						</td>
						<td width="10%">
							<div id="divdiv10"><label>&nbsp;</label></div>
						</td>
					</tr>
					</table>
	</td>
	<td width="25%">
		&nbsp;
	</td>
</tr>
</table>
<?php

/*
Valori del parametro Stampa
- se � 1 la stampa � PROVVIORIA
- se � 2 la stampa � DEFINITIVA
- se � 3 la stampa � dei BOLLETTINI
Valori del parametro $stampa_carta
- se � 4 la stampa � su CARTA INTESTATA
*/

echo "<br>NUOVA PAGINA VERBALI<br>";

if ($nonfarlosemarco == 0)
{
	$uscitaSenzaRisultati = "
			<script>
	        	alert('Nessun risultato trovato.');
				self.close();
	        </script>
	";
}
else
{
	$uscitaSenzaRisultati = "
		<script>
        	alert('Nessun risultato trovato.');
			//self.close();
        </script>
	";
}


$myFromGetComune = new comune($c);

//Parametro definizione cronologico e verbale
$myParametriCds = new parametri($c, $a, "CDS");
$par_definizione_cronologico=$myParametriCds->Par_Definizione_Cronologico;		
$par_definizione_verbale=$myParametriCds->Par_Definizione_Verbale;
$par_tipo_ente_accertatore=$myParametriCds->Par_Tipo_Ente_Accertatore;
$par_nome_altro_ente=$myParametriCds->Par_Nome_Altro_Ente;

//$par_NumeroBollettini = $myParametriCds->Par_Numero_Bollettini;
//$par_NumeroBollettini = 1;
$contatoredacancellare = 0;

if($par_definizione_cronologico!=NULL or $par_definizione_cronologico!="")
{	
	$par_definizione_cronologico_tmp="/$par_definizione_cronologico";
}
if($par_definizione_verbale!=NULL or $par_definizione_verbale!="")
{
	$par_definizione_verbale_tmp="/$par_definizione_verbale";
}
//$anteprima=2;
// Controllo
clean_string($tipo_stampante);
clean_string($daco);
clean_string($dano);
clean_string($acog);
clean_string($anom);
clean_string($stato);
// creo l'oggetto caratt_stampante
$stamp= new caratt_stampante ($tipo_stampante);
//assegno alla variabile $marg la propriet� dell'oggetto $stamp->Sta_Margine_Sinistro
$marg = $stamp->Margine_Sinistro;
//********************** Apertura file stampa e intestazione
//if($anteprima==2)
{	
	$trib = "V_CDS";
	$nome_file_flusso = $trib . "_TD896_" . $c;
	$query_num_stamp = "SELECT max(Num_Flusso) FROM gitco.num_stamp_a4
						WHERE Num_Comune='$c' and Num_Anno='$a' and Num_Tributo='$trib'";
	$num_max_flusso = single_answer_query($query_num_stamp);
	$flusso = $num_max_flusso + 1;
	$nome_file_app = $a . "_" . $nome_file_flusso . "_" . $flusso . ".txt";
}
/*else
{
	$nome_file_app=nome_stampa();
}*/

if ($stampa == 3)
{
	$tip = "Provvisoria";
	if ($formato_carta == 1)  //  flusso dati
	{
		alert ("Questa pagina gestisce solo flussi DEFINITIVI; non gestisce flussi PROVVISORI!");
		echo $uscitaSenzaRisultati;
		return;
	}
}
else if ($stampa == 2)
{
	$tip = "Definitiva";
}
else 
{
	alert ("Questa pagina gestisce solo file DEFINITIVI o PROVVISORI; non gestisce altre tipologie di stampa!");
	echo $uscitaSenzaRisultati;
	return;
}

if ($formato_stampa == 6) // VERBALE
{
	if ($formato_carta == 1)  //  flussi
	{
		alert ("Questa pagina gestisce solo flussi in COPIA VERBALE; non gestisce flussi in VERBALE!");
		echo $uscitaSenzaRisultati;
		return;
	}
}
else if ($formato_stampa == 0) // COPIA VERBALE
{
	if ($formato_carta == 3)  //  pdf
	{
		/*alert ("Questa pagina gestisce solo Pdf in VERBALE; non gestisce Pdf in COPIA VERBALE!");
		echo $uscitaSenzaRisultati;
		return;*/
	}
	else if ($formato_carta == 1)  //  flusso
	{
		//
	}
}
else if ($formato_stampa == 4) // BOLLETTINI
{
	if ($formato_carta != 3)  //  NON pdf
	{
		alert ("Questa pagina gestisce BOLLETTINI solo in Pdf; non gestisce altri formati!");
		echo $uscitaSenzaRisultati;
		return;
	}
	if ($stampa != 3)  //  NON provvisoria
	{
		alert ("Questa pagina gestisce BOLLETTINI solo PROVVISORI; non gestisce DEFINITIVI!");
		echo $uscitaSenzaRisultati;
		return;
	}
}
else
{
	alert ("Questa pagina gestisce solo VERBALI e COPIE VERBALI e BOLLETTINI; non gestisce altre tipologie di stampa!");
	echo $uscitaSenzaRisultati;
	return;
}

$data_registrazione = date("Y-m-d");
$ora_registrazione = date("H-i-s");
$ora_registrazione_db = date("H:i:s");

if ($formato_carta == 1)  //  flusso dati
{
	//$myClassVerbale->ApriFileFlusso($c, $marg, $nome_file_app);
	$risp = $NuovoListone->ApriFileFlusso($c, $marg, $nome_file_app);
	if ($risp == "ERRORE")
	{
		alert ("Non sono riuscito a creare il nuovo file di flusso. Controllare che non sia gi� aperto e riprovare.");
		return;
	}
}
else if ($formato_carta == 3)  //  stampa pdf
{
	if ($formato_stampa == 4) // BOLLETTINI
	{
		$nome_file_app = "Stampa_Bollettini_" . $tip . "_" . $c . "_" . $data_registrazione . "_" . $ora_registrazione . ".pdf";
		$NuovoListone->ApriBollettinoPdf ($c, $nome_file_app);
	}
	else   //  verbali
	{
		// da GET arriva $stampa_con_foto = Y o NULL
		$nome_file_app = "Stampa_Verbali_" . $tip . "_" . $c . "_" . $data_registrazione . "_" . $ora_registrazione . ".pdf";
		//$myClassVerbale->ApriStampaPdf ($c, $nome_file_app, $stampa_con_foto);
		$NuovoListone->ApriStampaPdf ($c, $stampa_con_foto);
	}
}
else
{
	alert ("Questa pagina gestisce solo INVIO DATI e STAMPA PDF; non gestisce altre tipologie di stampa!");
	echo $uscitaSenzaRisultati;
	return;
}


//$fh=fopen("$DOCUMENT_ROOT/file_stampa/$nome_file_app", "w");
$con_pag=1;
$con_righe=0;		// utilizzata per la selezione "distinte"
$co_app=1;
$contr_app[0]=0;
$cont_avvisi=0;

//Selezione dell'elenco dei contribuenti e dei loro rispettivi preavvisi.
if (($daco == NULL) AND ($dano==NULL) AND ($acog==NULL) AND ($anom==NULL))
{
	$daco_app=$dano_app=$acog_app=$anom_app="%";
	$tutti_com_app="((Con_Com_Ass='$c') AND (Con_Cognome like '$daco_app' AND Con_Nome like '$dano_app'))";
}
else
{
	$daco_app=$daco;
	$dano_app=$dano;
	$acog_app=$acog;
	$anom_app=$anom;
	$tutti_com_app="((Con_Com_Ass='$c') AND ((Con_Cognome>'$daco_app') or (Con_Cognome='$daco_app' and Con_Nome>='$dano_app')) AND ((Con_Cognome<'$acog_app') or (Con_Cognome='$acog_app' and Con_Nome<='$anom_app')))";
}


if ($da_anno==NULL && $ad_anno==NULL)
{
	$aggiuntaAnni = "";
}
else if ($da_anno==NULL && $ad_anno!=NULL)
{
	alert ("Non hai inserito DA anno E A anno!");
	echo $uscitaSenzaRisultati;
	return;
}
else if ($da_anno!=NULL && $ad_anno==NULL)
{
	alert ("Non hai inserito DA anno E A anno!");
	echo $uscitaSenzaRisultati;
	return;
}
else
{
	$aggiuntaAnni = " (Reg_Anno_Avviso>='$da_anno' AND Reg_Anno_Avviso<='$ad_anno') AND ";
}

if ($da_n_elenco==NULL && $a_n_elenco==NULL)
{
	$aggiuntaNumeri = "";
}
else if ($da_n_elenco==NULL && $a_n_elenco!=NULL)
{
	alert ("Non hai inserito DAL verbale E AL verbale!");
	echo $uscitaSenzaRisultati;
	return;
}
else if ($da_n_elenco!=NULL && $a_n_elenco==NULL)
{
	alert ("Non hai inserito DAL verbale E AL verbale!");
	echo $uscitaSenzaRisultati;
	return;
}
else 
{
	$aggiuntaNumeri = " (Reg_Progr_Registro>='$da_n_elenco' AND Reg_Progr_Registro<='$a_n_elenco') AND ";
}

if ($da_data==NULL && $a_data==NULL)
{
	$aggiuntaDate = "";
}
else if ($da_data==NULL && $a_data!=NULL)
{
	alert ("Non hai inserito DAL verbale E AL verbale!");
	echo $uscitaSenzaRisultati;
	return;
}
else if ($da_data!=NULL && $a_data==NULL)
{
	alert ("Non hai inserito DAL verbale E AL verbale!");
	echo $uscitaSenzaRisultati;
	return;
}
else
{

	if (substr($da_data, 2, 1) == "/")
		$tempDaData = substr($da_data, 6, 4) . "-" . substr($da_data, 3, 2) . "-" . substr($da_data, 0, 2);
	else
		$tempDaData = substr($da_data, 4, 4) . "-" . substr($da_data, 2, 2) . "-" . substr($da_data, 0, 2);
	
	if (substr($a_data, 2, 1) == "/")
		$tempAData = substr($a_data, 6, 4) . "-" . substr($a_data, 3, 2) . "-" . substr($a_data, 0, 2);
	else
		$tempAData = substr($a_data, 4, 4) . "-" . substr($a_data, 2, 2) . "-" . substr($a_data, 0, 2);
	$aggiuntaDate = " (Reg_Data_Avviso>='$tempDaData' AND Reg_Data_Avviso<='$tempAData') AND ";
}
/* controllo l'ordinamento */
if ($ord != 3)
{
	alert ("Questa pagina gestisce solo ORDINAMENTO PER CRONOLOGICO!");
	echo $uscitaSenzaRisultati;
	return;
}

$aggiuntaLocalita = "";
if ($localita_violazione != "")
{
	$aggiuntaLocalita = " Reg_Localita_Violazione = '" . $localita_violazione . "' AND ";
}


if ($via != NULL)
{
    alert ("Questa pagina NON gestisce ricerche per vie!");
	echo $uscitaSenzaRisultati;
	return;
}


//Seleziono lo stato di stampa 'Da stampare' (0) e in 'Sospeso' (11)
//di modo che quando seleziono stampa gli avvisi gi� stampati con autoimbustante o A4
//li prenda tutti.
$query = "SELECT Not_Progr FROM notifica_stato_cds WHERE Not_Codice='2' AND Not_Comune='$c'";
$notificaAnnullata_Codice2 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stato_cds WHERE Not_Codice='1' AND Not_Comune='$c'";
$notificaStrada_Codice1 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stato_cds WHERE Not_Codice='8' AND Not_Comune='$c'";
$notificaUfficio_Codice8 = single_answer_query($query);


$query = "SELECT Not_Progr FROM notifica_stampa_cds WHERE Not_Codice='0' AND Not_Comune='$c'";
$notificaStampaCodice0 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stampa_cds WHERE Not_Codice='1' AND Not_Comune='$c'";
$notificaStampaCodice1 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stampa_cds WHERE Not_Codice='2' AND Not_Comune='$c'";
$notificaStampaCodice2 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stampa_cds WHERE Not_Codice='4' AND Not_Comune='$c'";
$notificaStampaCodice4 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stampa_cds WHERE Not_Codice='5' AND Not_Comune='$c'";
$notificaStampaCodice5 = single_answer_query($query);
$query = "SELECT Not_Progr FROM notifica_stampa_cds WHERE Not_Codice='11' AND Not_Comune='$c'";
$notificaStampaCodice_11 = single_answer_query($query);

//Selezioni di stampa:
//-$formato_stampa=6: stampa degli atti ancora da stampare (verbale originario/copia atto)
//                    E' unico per entrambi i tipi di foglio (autoimbustante e A4)
//-$formato_stampa=0: formato autoimbustante
//-$formato_stampa=1: A4
//-$formato_stampa=4: bollettini di ccp
//-$formato_stampa=2: A.R.
//-$formato_stampa=3: Etichette
//-$formato_stampa=5: distinte (unico per entrambi i tipi di foglio)
if ($formato_stampa == 6)
{
    if($stampa_avv==1)
    {
        //Avvisi gi� stampati
		$stato_stampa_notifica = " (Not_Stato_Stampa_Notifica = '$notificaStampaCodice1' OR 
								(Not_Stato_Stampa_Notifica != '$notificaStampaCodice_11' AND 
								Not_Stato_Stampa_Notifica != '$notificaStampaCodice0')) AND ";
    }
    else
    {
        //Seleziono tutti gli avvisi da stampare (codice 0)
        $stato_stampa_notifica = " (Not_Stato_Stampa_Notifica = '$notificaStampaCodice0' OR 
        						Not_Stato_Stampa_Notifica = '0' OR 
        						Not_Stato_Stampa_Notifica = NULL) AND 
        						Not_Data_Notifica = '0000-00-00' AND ";
    }
}
else if ($formato_stampa == 0)
{
	if($stampa_avv==1)
    {
        //Avvisi gi� stampati
        $stato_stampa_notifica = " (Not_Stato_Stampa_Notifica = '$notificaStampaCodice5' OR 
        						(Not_Stato_Stampa_Notifica != '$notificaStampaCodice_11') OR 
        						Not_Stato_Stampa_Notifica = '$notificaStampaCodice2' OR 
        						Not_Stato_Stampa_Notifica = '$notificaStampaCodice4') AND ";
    }
    else
    {
        //Devo selezionare tutti gli avvisi che sono copie atti (codice 1)
        $stato_stampa_notifica = " (Not_Stato_Stampa_Notifica = '$notificaStampaCodice1') AND  
        						Not_Data_Notifica = '0000-00-00' AND ";
    }
}

//MODIFICA: il tipo della notifica sta creando problemi sui comuni e non si sa il motivo inserisco allora il controllo
//che mi fa la selezione sul progressivo del tipo della notifica solo se il formato di stampa � 0 (autoimbustante)
//o 1 (A4)... da controllare che sia corretto
if ($formato_stampa==0)
{
    $query="select Not_Progr from notifica_tipo_cds where Not_Descrizione='$tipo_noti' and Not_Comune='$c'";
    $not_tipo_progr=single_answer_query($query);
    if($not_tipo_progr!='0' and $not_tipo_progr!=NULL)
    {
        if($stampa_avv==1)
        {
            //Stampa gli avvisi gi� stampati
            $notifica_tipo_progressivo = " (Not_Tipo_Notifica = '$not_tipo_progr' OR Not_Tipo_Notifica LIKE '%') AND ";
        }
        else
        {
            $notifica_tipo_progressivo = " Not_Tipo_Notifica != '$not_tipo_progr' AND ";
        }
    }
    else
    {
        $notifica_tipo_progressivo = " Not_Tipo_Notifica LIKE '%' AND";
    }
}

if ($tipo_trasgre=='1') $trasgre_tipo="coincidente";
else if ($tipo_trasgre=='2') $trasgre_tipo="trasgressore";
else if ($tipo_trasgre=='3') $trasgre_tipo="obbligato";
else if ($tipo_trasgre=='0') $trasgre_tipo="tutti";

//Aggiungo il controllo che non si possono ristampare gli atti gi� notificati
//alert ($tipo_trasgre);

$queryRegistri = "SELECT DISTINCT Reg_Progr 
				FROM contribuente, trasgressore_cds, notifica_verbale_cds, registro_cronologico_cds
                WHERE Con_Progr = Tra_Trasgressore AND
                Not_Trasgressore = Tra_Progr AND
                Reg_Progr = Not_Progr_Registro AND
			    Reg_Annullato = 'N' AND
				Not_Stato_Notifica != '$notificaAnnullata_Codice2' AND 
				Not_Stato_Notifica != '$notificaStrada_Codice1' AND 
                $aggiuntaNumeri
                $aggiuntaAnni
                $aggiuntaDate 
                $aggiuntaLocalita
                $notifica_tipo_progressivo
                $stato_stampa_notifica
";

if ($tipo_trasgre != 0)
{
    $queryRegistri .= " Tra_Tipo_Trasgressore='$trasgre_tipo' AND ";
}

$queryRegistri .= " 1 ORDER BY Reg_Progr_Registro ASC";

$resRegistri = safe_query($queryRegistri);
$num_contr = mysql_num_rows($resRegistri);

if ($nonfarlosemarco == 1)
{
	echo "<br>$queryRegistri  -> $num_contr";
}

//return;

if ($num_contr==0)
{
	/*if ($nonfarlosemarco == 0)
	{
	    $data_stapa='0000-00-00';
		$query="delete from gitco.num_stamp_a4 where Num_Data_Flusso='$data_stapa'and Num_Numero_Stampe='$cont_avvisi'
				and Num_Comune='$c' and Num_Tributo='V_CDS' and Num_Flusso='$flusso' and Num_Anno='$a'";
		safe_query($query);
	}
	else echo "<br>vorrei fare un delete INIZIO<br>";*/
	echo $uscitaSenzaRisultati;
    die;
}

$oldSecondo = "";

$x = 0;
$mioProgr = array();
while ($result = mysql_fetch_array($resRegistri,MYSQL_ASSOC))
{
	$x ++;
    $mioProgr[] = $result['Reg_Progr'];
    $oldSecondo = AvanzaJavascript ($x, $num_contr, "divbis", $oldSecondo);
}

$oldPrimo = "";
//alert ("numero $num_contr");
for ($x = 0; $x < $num_contr; $x++)
{
	
	$oldPrimo = AvanzaJavascript ($x, $num_contr, "divdiv", $oldPrimo);
	
	//if ($ord==3)
    //{
	$query_tap = "select distinct Tra_Trasgressore from registro_cronologico_cds,
				trasgressore_cds,notifica_verbale_cds
				where Reg_Tipo='A' and Reg_Annullato='N' AND
				$aggiuntaNumeri
				$aggiuntaAnni
				$aggiuntaDate
				$aggiuntaLocalita
				Not_Progr_Registro=Reg_Progr
				and Not_Trasgressore=Tra_Progr
				and Not_Stato_Notifica!='$notificaAnnullata_Codice2' and
				Not_Stato_Notifica!='$notificaStrada_Codice1' AND 
				$notifica_tipo_progressivo
				$stato_stampa_notifica
				Reg_Progr = '$mioProgr[$x]'
				and Tra_Registro_Cronologico=Reg_Progr AND ";
	if ($tipo_trasgre != 0)
	{
		$query_tap .= " Tra_Tipo_Trasgressore='$trasgre_tipo' AND ";
	}
	$query_tap .= " 1 ORDER BY Reg_Progr ";
	$res_tap = safe_query($query_tap);
	//$result_tap=mysql_fetch_assoc($res_tap);
	
	$numTrasgressori = mysql_num_rows($res_tap);
	//$p_app=$result_tap[Tra_Trasgressore];
	$totaletrasgressori = 0;
	$arrayTrasgressori = array();
	while ($rigaTrasgressore = mysql_fetch_assoc($res_tap))
	{
		if ($totaletrasgressori == 0)
			$p_app = $rigaTrasgressore['Tra_Trasgressore'];
		$arrayTrasgressori[$totaletrasgressori] = $rigaTrasgressore['Tra_Trasgressore'];
		$totaletrasgressori++;
		if ($nonfarlosemarco == 1)
		{
			echo "<br><br>marcom " . $query_tap;
			echo "<br>tot " . $totaletrasgressori . " e " . $rigaTrasgressore['Tra_Trasgressore'];
		}
	}
	//$reggistro_progr[0]=$mioProgr[$x];
//}  // fine if ord 3
    
    if ($nonfarlosemarco == 1)
    {
    	//echo "<br>$query_tap<br>";
    	//$totaletrasgressori = 1;
    	//$trasgressore[0] = 7391;
    	//return;
    }
    //************************ Selezione del nominativo del contribuente attraverso la classe
	for ($contotrasgr = 0; $contotrasgr < $totaletrasgressori; $contotrasgr++)
	{
		if ($totaletrasgressori > 1) 
			alert ("trasgressori $totaletrasgressori -> registro $mioProgr[$x]");
		
		$questoContribuente = new contribuente($arrayTrasgressori[$contotrasgr],$c);
		if($questoContribuente->Con_Via_Sec!=NULL and $questoContribuente->Con_Via_Sec!=0)
		{
			//seleziono la classe indirizzo x il domicilio
			$via_progr=$questoContribuente->Con_Via_Sec;
		}
		else
		{
			//seleziono la classe indirizzo x la residenza
			$via_progr=$questoContribuente->Con_Via_Res;
		}
		$address = new indirizzo($via_progr);
		$civico = $questoContribuente->FunzioneInutileCivico();
		
		//alert ($via_progr . " e " . $civico . " e " . $address->Via_Nome);
		
		$nome = stripslashes("$questoContribuente->Con_Cognome $questoContribuente->Con_Nome");
		$myCognameContribuente = strtoupper(stripslashes($questoContribuente->Con_Cognome));
		$myNameContribuente = strtoupper(stripslashes($questoContribuente->Con_Nome));
		$myCodiceContribuente = $questoContribuente->Con_Progr;
		$myCodiceFiscale = $questoContribuente->Con_CDF;
		
		//Controllo se c'� il recapito per farlo uscire nella stampa				
		$recapito = $questoContribuente->Con_Presso;
		if($recapito!='')
		{				
			$recapito_tmp="c/o $recapito";
		}
		else
		{
			$recapito_tmp="";										
		}	
		
	    $indirDestinatario = stripslashes("$address->Top_Nome $address->Via_Nome $civico");
	    
	    $indirDestinatario = strtoupper($questoContribuente->IndirizzoResidenzaCompleto);
	    $indirDestinatario = substr($indirDestinatario,0,35);
	    
	    
	    
	    if($address->Via_Cap==NULL or $address->Via_Cap==0)
	    {
	        $cap=$address->Com_Cap;
	    }
	    else
	    {
	        $cap=$address->Via_Cap;
	    }
	    if($address->Pro_Sigla==NULL)
	    {
	        $query="select Via_Comune from via where Via_Progr='$via_progr'";
	        $via_comune=single_answer_query($query);
	        $query="select Com_Paese from comune where Com_Nome='$address->Com_Nome' and Com_CC='$via_comune'";
	        $paese=single_answer_query($query);
	        $query="select Naz_Nome from nazione where Naz_Cod='$paese'";
	        $naz_nome=single_answer_query($query);
	        //$maiuscoloCittaSigla="$cap $address->Com_Nome ($naz_nome)";
			$maiuscoloCittaSigla="$address->Com_Nome  $naz_nome";
	    }
	    else
	    {
	        //$maiuscoloCittaSigla="$cap $address->Com_Nome ($address->Pro_Sigla)";
			$naz_nome="Italia";
			$maiuscoloCittaSigla="$address->Com_Nome  $address->Pro_Sigla";
	    }
		//$maiuscoloCittaSigla=substr($maiuscoloCittaSigla,0,25);
		$maiuscoloCittaSigla=substr($maiuscoloCittaSigla,0,35);
		
		$nomeCityDelDestinatario = strtoupper($address->Com_Nome);
		$nomeCityDelDestinatario = substr($nomeCityDelDestinatario,0,35);
		$provinciaSigla = strtoupper($address->Pro_Sigla);
		
		/*$query = "select distinct Reg_Progr from registro_cronologico_cds,
				trasgressore_cds,notifica_verbale_cds
				where Reg_Tipo='A' and Reg_Annullato='N' AND
				$aggiuntaNumeri
				$aggiuntaAnni
				$aggiuntaDate
				$aggiuntaLocalita
				Not_Progr_Registro=Reg_Progr
				and Not_Trasgressore=Tra_Progr
				and (Not_Stato_Notifica!='$notificaAnnullata_Codice2' and Not_Stato_Notifica!='$notificaStrada_Codice1')
				$notifica_tipo_progressivo
				$stato_stampa_notifica
				and Tra_Trasgressore='$p_app'
				and Tra_Registro_Cronologico=Reg_Progr AND ";
				
		if ($tipo_trasgre!='0')
		{
			$query .= " Tra_Tipo_Trasgressore='$trasgre_tipo' AND ";
		}
		$query .= " 1 ORDER BY Reg_Progr";*/
	}  // fine for contatore trasgressori per unico verbale
    
    
	/*************** inizio selezione singoli avvisi **************************/
	
	
	if ($numTrasgressori > 0)
    {
    	$carriagereturnWindows = chr(13);
    	$carriagereturnWindows .= chr(10);
    		
    	for ($y=0; $y < $numTrasgressori; $y++)
		{
			$frase_uno="";
			$info_cc="";
			//Seleziono gli avvisi uno per volta
			/*if ($ord==3)  //  ordine cronologico
               $query="select * from registro_cronologico_cds where Reg_Progr='$reggistro_progr[0]'";
            else
               $query="select * from registro_cronologico_cds where Reg_Progr='$reggistro_progr[$y]'";
            $res=safe_query($query);
            $regi=mysql_fetch_array($res);*/
            
            
            $myRegistroVerbale = new VerbaleCompletoCds ($mioProgr[$x], $c);
            
            $myArticoloUno = new tariffa($myRegistroVerbale->Reg_Articolo_Uno, "CDS");
            if ($myRegistroVerbale->Reg_Articolo_Due != NULL)
            	$myArticoloDue = new tariffa($myRegistroVerbale->Reg_Articolo_Due, "CDS");
            if ($myRegistroVerbale->Reg_Articolo_Tre != NULL)
            	$myArticoloTre = new tariffa($myRegistroVerbale->Reg_Articolo_Tre, "CDS");
            
            $myLocalTestoVerbale = new TestoVerbaleCds(NULL);
            if ($myArticoloUno->Tar_Tipo_Art != "ASSICURAZIONE" && $myArticoloUno->Tar_Tipo_Art != "REVISIONE")
            {  //  articoli normali (non assicurazioni/revisioni)
            	$myId = $myLocalTestoVerbale->CercaParametroData($c, date("Y-m-d"), $c);
            }
            else
            {  //  assicurazioni/revisioni
            	$myId = $myLocalTestoVerbale->CercaParametroAssicurazioniRevisioniData($c, date("Y-m-d"), $c);
            }
            $myLocalTestoVerbale = new TestoVerbaleCds($myId);
            
            $myFotogramma = NULL;
            //if ($stampa_con_foto == "Y")
            {
            	$stringafoto = $myRegistroVerbale->Reg_Immagini;
	            $queryFoto = "SELECT Fot_Id FROM fotogrammi_cds WHERE Fot_Stringa_Foto = '$stringafoto'";
	            $myIdFoto = single_answer_query($queryFoto);
	            $myFotogramma = new Fotogrammi_Cds($myIdFoto);
            }
            
            
			//Seleziono i parametri del cds
			$myParametroDiQuestoAnno = new parametri($c,$myRegistroVerbale->Reg_Anno,"CDS");
			
			if($myParametroDiQuestoAnno->Par_Ufficio_Postale=='')
			{
				echo"<script>alert('Controllare che non sia vuoto il parametro Contravvenzioni->Gestione Violazioni->Parametri->Ente->Ufficio Postale di invio verbali.');self.close();</script>";
				exit;
			}
			
			//PROVA DI INSERIMENTO NOTE MOLTO LUNGHE SPEZZATE
			$info_cc=stripslashes($myParametroDiQuestoAnno->Par_Info_Corpo_Accertatore);
			//$frase_uno=strtoupper("per informazioni $info_cc");
			if($info_cc!=NULL or $info_cc!='')
			{
				$frase_uno="Per informazioni $info_cc";
			}
			else
			{
				$frase_uno="";
			}				
			//controllo la presenza del protocollo
			if ($myRegistroVerbale->Reg_Protocollo==0 and $myParametroDiQuestoAnno->Par_Tipo_Protocollo_Contenzioso!=0)
			{
				//$iopui=$reggistro_progr[$y];
    			echo"<script>alert('Manca il protocollo.');</script>";
    			echo"<script>self.close(); </script>";
    			die;
			}
			
			//Controllo che sia inseirito il conto corrento e l'intestazione
			if($myParametroDiQuestoAnno->Par_Tipo_Riscossione=="Diretta")
			{
				$cur_ccp_tmp = new ccp($c,"CDS",$c);
				$num_ccp_tmp=$cur_ccp_tmp->Ccp_Conto_Comune;
				$intestazione_tmp=$cur_ccp_tmp->Ccp_Intestazione;
			}
			elseif($myParametroDiQuestoAnno->Par_Tipo_Riscossione=="CNE")
			{
				$cur_cnc_tmp= new cnc($c,"CDS");				
				$cur_sede_cnc_tmp= new sede($cnc_progr,"CDS");
				$num_ccp_tmp=$cur_sede_cnc_tmp->Sed_Num_Ccp;
				$intestazione_tmp=$cur_sede_cnc_tmp->Sed_Intestazione_Ccp;
			}			
			if($num_ccp_tmp==NULL or $num_ccp_tmp=='' or $intestazione_tmp==NULL or $intestazione_tmp=='')
			{
				echo"<script>alert('Attenzione! Il campo del conto corrente e dell\'intestazione devono essere compilati, si prega di controllare in Parametri--> Riscossioni. Grazie!');history.back();self.close();</script>";
				die;
			}
			
			
			//Seleziono il corpo accertatore
			/*$query="select Com_Nome from comune where Com_CC='$myRegistroVerbale->Reg_Comune_Violazione]'";
            $nome_comune=single_answer_query($query);*/
            
            $myAttualeComune = new comune($myRegistroVerbale->Reg_Comune_Violazione);
            
			$nome_comune = stripslashes($myAttualeComune->Com_Nome);
            $nome_comune_upp=strtoupper($nome_comune);
            if($myParametroDiQuestoAnno->Par_Corpo_Accertatore=='PM')
            {
                $corpo_acc="UFFICIO POLIZIA MUNICIPALE";
            }
            elseif($myParametroDiQuestoAnno->Par_Corpo_Accertatore=='P')
            {
                $corpo_acc="UFFICIO POLIZIA COMUNALE";
            }
            elseif($myParametroDiQuestoAnno->Par_Corpo_Accertatore=='CF')
            {
                $corpo_acc="UFFICIO CORPO FORESTALE";
            }
            elseif($myParametroDiQuestoAnno->Par_Corpo_Accertatore=='C')
            {
                $corpo_acc="UFFICIO POLIZIA LOCALE";
            }			
            //Stampa definitiva
            //Cambio lo stato della notifica in Stampata
            if($tipo_trasgre=='0')
            {
                $query="select Not_Progr from notifica_verbale_cds,trasgressore_cds
                        where Not_Progr_Registro='$myRegistroVerbale->Reg_Progr'
                        and Not_Trasgressore=Tra_Progr
						and (Not_Stato_Notifica!='$notificaAnnullata_Codice2' and Not_Stato_Notifica!='$notificaStrada_Codice1')
                        and Tra_Trasgressore='$p_app'
                        and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
            }
            else
            {
                $query="select Not_Progr from notifica_verbale_cds,trasgressore_cds
                        where Not_Progr_Registro='$myRegistroVerbale->Reg_Progr'
                        and Not_Trasgressore=Tra_Progr
						and (Not_Stato_Notifica!='$notificaAnnullata_Codice2' and Not_Stato_Notifica!='$notificaStrada_Codice1')
                        and Tra_Trasgressore='$p_app'
                        and Tra_Tipo_Trasgressore='$trasgre_tipo'
                        and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
            }
            $notifica_pr=single_answer_query($query);
			if($ord==3 and $notifica_pr==NULL and $tipo_trasgre=='0')
			{
				$query="select Not_Progr from notifica_verbale_cds,trasgressore_cds
                        where Not_Progr_Registro='$myRegistroVerbale->Reg_Progr'
                        and Not_Trasgressore=Tra_Progr
						and (Not_Stato_Notifica!='$notificaAnnullata_Codice2' and Not_Stato_Notifica!='$notificaStrada_Codice1')
                        and Tra_Trasgressore!='$p_app'
                        and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
				$notifica_pr=single_answer_query($query);		
				//echo"N=$notifica_pr - $query - $numero_reg - x=$y - $ord<br>";
			}
			
			$myNotificaCorrente = new NotificaVerbaleCds($notifica_pr);
			
            if ($tip == "Definitiva")  // definitiva!
            {
                //Inserisco un controllo sull'esistenza della data di stampa: se c'� gi� non la posso modificare,
                //idem se esiste gi� la data di notifica.
                $data_stampa_notifica = $myNotificaCorrente->Not_Data_Stampa;
				$data_notifica_notifica = $myNotificaCorrente->Not_Data_Notifica;
				//$data_stapa = $data;
                store_date_cds($data_registrazione,$data_tmp);
                
                //Guardo lo stato di stampa precedente
                $query = "select Not_Codice from notifica_stampa_cds where Not_Progr = '$myNotificaCorrente->Not_Stato_Stampa_Notifica'";
                $noto_codice_stampa = single_answer_query($query);
                
                
                if ($formato_stampa==0)
                {
                    if ($nonfarlosemarco == 0)
                    {
	                    if($stampa_avv==1)
	                    {
	                        //if($noto_codice_stampa=='2')
	                        {
								/*$query="update notifica_verbale_cds set
										Not_Stato_Stampa_Notifica='$notificaStampaCodice2',
										Not_Data_Stampa='$data_stapa',
										Not_Numero_Flusso='$flusso',
										Not_Data_Flusso='$data_stapa'
										where Not_Progr='$notifica_pr'";*/
								$query="update notifica_verbale_cds set
											Not_Data_Stampa='$data_registrazione',
											Not_Numero_Flusso='$flusso',
											Not_Data_Flusso='$data_registrazione'
											where Not_Progr='$notifica_pr'";
	                            safe_query($query);
	                        }
	                        /*elseif($noto_codice_stampa=='4')
	                        {
	                            $query="update notifica_verbale_cds set
										Not_Stato_Stampa_Notifica='$notificaStampaCodice4',
										Not_Data_Stampa='$data_stapa',
										Not_Numero_Flusso='$flusso',
										Not_Data_Flusso='$data_stapa'
										where Not_Progr='$notifica_pr'";
	                            safe_query($query);
	                        }*/
	                    }
	                    else
	                    {
	                        $query="update notifica_verbale_cds set
									Not_Stato_Stampa_Notifica='$notificaStampaCodice5',
									Not_Tipo_Notifica='$not_tipo_progr',
									Not_Data_Stampa='$data_registrazione',
									Not_Numero_Flusso='$flusso',
									Not_Data_Flusso='$data_registrazione'
									where Not_Progr='$notifica_pr'";
	                        safe_query($query);
	                    }
                    }
                    else
                    	echo "<br>vorrei fare degli update 1<br>";
                }
                else if ($formato_stampa==6)
                {
					if ($nonfarlosemarco == 0)
					{
						/*if($stampa_avv==1)
						{
						    if($noto_codice_stampa==1)
						    {*/
						        $query="update notifica_verbale_cds set
										Not_Stato_Stampa_Notifica='$notificaStampaCodice1',
										Not_Data_Stampa='$data_registrazione'
										where Not_Progr='$notifica_pr'";
						        safe_query($query);
						/*    }
						}
						else
						{
						    if($noto_codice_stampa=='0' or $noto_codice_stampa==NULL)
						    {
						        $query="update notifica_verbale_cds set
										Not_Stato_Stampa_Notifica='$notificaStampaCodice1',
										Not_Data_Stampa='$data_stapa'
										where Not_Progr='$notifica_pr'";
						        safe_query($query);
						    }
						}*/
					}
					else
						echo "<br>vorrei fare degli update 3<br>";
                }
                
                if ($formato_carta == 3)  //  stampa pdf
                {
                	$query="insert into gitco.stampa_definitiva_documenti
		                	set
		                	Sta_Comune='$c',
		                	Sta_Anno='$a',
		                	Sta_Cronologico='$myRegistroVerbale->Reg_Progr',
		                	Sta_Tributo='CDS',
		                	Sta_Tipologia='Volontario',
		                	Sta_Nome_File='$nome_file_app',
		                	Sta_Descrizione='Verbale di Accertamento',
		                	Sta_Tipo='$tipo_sta',
		                	Sta_Utente='$_SESSION[username]',
		                	Sta_Data_Registrazione='$data_registrazione',
		                	Sta_Ora_Registrazione='$ora_registrazione_db'";
                	safe_query($query);
                }
            }  //  fine definitiva
			
				//***************Prima parte dell'avviso di accertamento ***********
				//*********************************************************************************************
				$tabulatore=chr(9);
				
				////fput_margine_new($c,$marg,$fh,"COMUNE DI $nome_comune_upp - $corpo_acc|COMUNE DI $nome_comune_upp - $corpo_acc|");
				//Parametrizzo il tipo di intestazione che pu� essere COMUNE o OMUNITA' MONTANA, per ora metto COMUNE
				$ufficio_postale = stripslashes($myParametroDiQuestoAnno->Par_Ufficio_Postale);
				
				$myUfficioPostale = $ufficio_postale;
				
				$bolComuneEnteAcc = $nome_comune_upp;
				if ($par_tipo_ente_accertatore=='Altro_Ente') {}
				else $bolComuneEnteAcc = "COMUNE DI " . $bolComuneEnteAcc;
				
				$myNomeComune = $bolComuneEnteAcc;
				
				$myCorpo = $corpo_acc;
				
				//L'ndirizzo a cui rispedire gli atti amministrativi diventa quello del comune
				$cur_ente=new enti($c);
				$cap_com = ($cur_ente->Via_Cap==NULL?$cur_ente->Com_Cap:$cur_ente->Via_Cap);
				$ind_com[0]="$cur_ente->Top_Nome $cur_ente->Via_Nome $cur_ente->Ent_Civico";
				$ind_com[1]="$cur_ente->Com_Nome ($cur_ente->Pro_Sigla)";				
				//fput_margine_new($c,$marg,$fh,"$ind_com[0]");
				
				$myIndirizzoComune = $ind_com[0];
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");					
			
				//esce... se il campo nome altro ente viene valorizzato e c'� selezionato altro ente modify 19-03-08
				if(($par_tipo_ente_accertatore=='Altro_Ente') and ($par_nome_altro_ente!="" or $par_nome_altro_ente!=NULL))
				{		
					//fput_margine_new($c,$marg,$fh,"$cap_com $par_nome_altro_ente");
					$tempCittaComune = "$cap_com $par_nome_altro_ente";
				}
				else
				{
					//fput_margine_new($c,$marg,$fh,"$cap_com $ind_com[1]");
					$tempCittaComune = "$cap_com $ind_com[1]";
				}
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				$myCittaComune = $tempCittaComune;
				
				$info_sped=$myParametroDiQuestoAnno->Par_Informazioni;
				$ind_arr=split("[0-9]{5}",$info_sped);
				$l_1=strlen($ind_arr[0]);
				$cap_ll=substr($info_sped,$l_1,5);
				//fput_margine_new($c,$marg,$fh,"$ind_arr[0]");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$cap_ll $ind_arr[1]");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$myImbustatoreIndirizzo_1 = $ind_arr[0];
				$myImbustatoreIndirizzo_2 = "$cap_ll $ind_arr[1]";
				
				$nome_upp=strtoupper($nome);
				$nome_upp=substr($nome_upp,0,25);
				$indirMaiuscoloDestinatario = strtoupper($indirDestinatario);
				$maiuscoloCapCitta=strtoupper($maiuscoloCittaSigla);				
				//fput_margine_new($c,$marg,$fh,"$nome_upp ($current->Con_Progr)");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$indirMaiuscoloDestinatario");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$cap");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$maiuscoloCapCitta");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$naz_nome");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$myNomeDestinatario = "$nome_upp ($questoContribuente->Con_Progr)";
				$myIndirizzoDestinatario = $indirMaiuscoloDestinatario;
				$myCapDestinatario = $cap;
				$myCittaDelDestinatario = $maiuscoloCapCitta;
				//$myStatoDestinatario = $naz_nome;
				
				/*if ($questoContribuente->Con_Paese_Nasc == NULL)
					$myStatoDestinatario = ""; // Italia":
				else $myStatoDestinatario = $questoContribuente->Con_Nome_Paese_Nasc;*/
				
				if ($address->Nome_Paese == "" || $address->Nome_Paese == 'Italia')
					$myStatoDestinatario = ""; // Italia":
				else $myStatoDestinatario = strtoupper($address->Nome_Paese);
				
				if($myRegistroVerbale->Reg_Provenienza!="180" and $myRegistroVerbale->Reg_Provenienza!='P' and $myRegistroVerbale->Reg_Provenienza!='B')
				{
					//if ($c=='A658' and $myRegistroVerbale->Reg_Note!=NULL) $proviene="/$myRegistroVerbale->Reg_Provenienza-$myRegistroVerbale->Reg_Note";
					if ($c=='A658' and $myRegistroVerbale->Reg_Note!=NULL) $proviene="  $myRegistroVerbale->Reg_Note";
					else $proviene="/$myRegistroVerbale->Reg_Provenienza";
				}
				else if($myRegistroVerbale->Reg_Provenienza=="P")
				{
					if ($c=='A658' and $myRegistroVerbale->Reg_Note!=NULL) $proviene="/$myRegistroVerbale->Reg_Provenienza-$myRegistroVerbale->Reg_Numero_Preavviso-$myRegistroVerbale->Reg_Note";
					else $proviene="/$myRegistroVerbale->Reg_Provenienza-$myRegistroVerbale->Reg_Numero_Preavviso";
				}
				else if($myRegistroVerbale->Reg_Provenienza=='B')
				{
					if ($myRegistroVerbale->Reg_Note!=NULL) $proviene="/$myRegistroVerbale->Reg_Provenienza-$myRegistroVerbale->Reg_Note";
					else $proviene="/$myRegistroVerbale->Reg_Provenienza";
				}
				else if($myRegistroVerbale->Reg_Provenienza=='180')
				{
					if($c=='A658' and $myRegistroVerbale->Reg_Note!=NULL) $proviene="/U-$myRegistroVerbale->Reg_Note";
					else $proviene="/U";
				}
				//VERBALE CHE PROVENGONO DEI PREAVVISI ELABORATI
				if($myRegistroVerbale->Reg_Preavviso_Elaborato=='SI')
				{
					//Controllo che il numero di preavviso inserito corrisponda ad un bollettario
					$query="select Bol_Codice from bollettario_cds where '$myRegistroVerbale->Reg_Numero_Preavviso' >= Bol_Da_Numero and '$myRegistroVerbale->Reg_Numero_Preavviso' <= Bol_A_Numero and Bol_Tipologia='P' and Bol_Anno='$a'";
					$rif_num_preavv=single_answer_query($query);
					if ($rif_num_preavv > 0 or $rif_num_preavv!=NULL) $bollettario=" Boll. $rif_num_preavv";
					else $bollettario="";
					$numero_preavviso=" (Preav. $myRegistroVerbale->Reg_Numero_Preavviso$bollettario)";
				}
				else $numero_preavviso="";
				
				$definizioneCrono = "$myRegistroVerbale->Reg_Progr_Registro/$myRegistroVerbale->Reg_Anno_Avviso$par_definizione_cronologico_tmp";
				$definizioneVerbale = "$myRegistroVerbale->Reg_Numero_Avviso/$myRegistroVerbale->Reg_Anno_Avviso$par_definizione_verbale_tmp$numero_preavviso";
				
				$myDefCronologico = $definizioneCrono;
				$myDefVerbale = $definizioneVerbale;
				if ($proviene != "") $myDefVerbale .= " " . $proviene;
				
				if(($myRegistroVerbale->Reg_Rif_Numero_Avviso==0 or $myRegistroVerbale->Reg_Rif_Numero_Avviso==NULL) and ($myRegistroVerbale->Reg_Protocollo!=0 and $myRegistroVerbale->Reg_Protocollo!=NULL))
				{
					//fput_margine_new($c,$marg,$fh,"$definizioneCrono");
					//fput_margine_new($c,$marg,$fh,"$definizioneVerbale");
				}
				elseif(($myRegistroVerbale->Reg_Rif_Numero_Avviso!=0 and $myRegistroVerbale->Reg_Rif_Numero_Avviso!=NULL) and ($myRegistroVerbale->Reg_Protocollo!=0 and $myRegistroVerbale->Reg_Protocollo!=NULL))
				{
					//fput_margine_new($c,$marg,$fh,"$definizioneCrono");
					//fput_margine_new($c,$marg,$fh,"$definizioneVerbale");
				}
				elseif(($myRegistroVerbale->Reg_Rif_Numero_Avviso!=0 and $myRegistroVerbale->Reg_Rif_Numero_Avviso!=NULL) and ($myRegistroVerbale->Reg_Protocollo==0 or $myRegistroVerbale->Reg_Protocollo==NULL))
				{
					//fput_margine_new($c,$marg,$fh,"$definizioneCrono");
					//fput_margine_new($c,$marg,$fh,"$definizioneVerbale Rif.: $myRegistroVerbale->Reg_Rif_Numero_Avviso]/$myRegistroVerbale->Reg_Anno_Avviso]");
					$myDefVerbale .= " Rif.: $myRegistroVerbale->Reg_Rif_Numero_Avviso/$myRegistroVerbale->Reg_Anno_Avviso";
				}
				else
				{
					//fput_margine_new($c,$marg,$fh,"$definizioneCrono");
					//fput_margine_new($c,$marg,$fh,"$definizioneVerbale");
				}
				$data_avv_new=$myRegistroVerbale->Reg_Data_Avviso;
				extract_date($data_avv_new);
				////fput_margine_new($c,$marg,$fh,"$data_avv_new|$data_avv_new|");
				//fput_margine_new($c,$marg,$fh,"$data_avv_new");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$myDataViolazione = $data_avv_new;
				
				$direzio_marcia="";
				$km_marcia="";
				$direzio_marcia_due="";
				$km_marcia_due="";
				if ($myRegistroVerbale->Reg_Direzione_Marcia!=NULL)
					$direzio_marcia="Dir.$myRegistroVerbale->Reg_Direzione_Marcia";
				if ($myRegistroVerbale->Reg_Km_Uno!=NULL)
					$km_marcia="Km $myRegistroVerbale->Reg_Km_Uno";
				if ($myRegistroVerbale->Reg_Direzione_Marcia_Due!=NULL)
					$direzio_marcia_due="Dir.$myRegistroVerbale->Reg_Direzione_Marcia_Due";
				if ($myRegistroVerbale->Reg_Km_Due!=NULL)
					$km_marcia_due="Km $myRegistroVerbale->Reg_Km_Due";
				//$via_uno=strtoupper("$myRegistroVerbale->Reg_Tipo_Strada_Uno] $myRegistroVerbale->Reg_Numero_Strada_Uno] $myRegistroVerbale->Reg_Via_Uno] $direzio_marcia$myRegistroVerbale->Reg_Civico_Uno] $km_marcia");
				$via_uno=strtoupper("$myRegistroVerbale->Reg_Tipo_Strada_Uno $myRegistroVerbale->Reg_Numero_Strada_Uno $myRegistroVerbale->Reg_Via_Uno $myRegistroVerbale->Reg_Civico_Uno $km_marcia $direzio_marcia");
				//echo "tipo strada: ", $myRegistroVerbale->Reg_Tipo_Strada_Uno], "numero strada: ", $myRegistroVerbale->Reg_Numero_Strada_Uno], "via uno: ", $myRegistroVerbale->Reg_Via_Uno], "direzio marcia: ", $direzio_marcia, "civico uno: ", $myRegistroVerbale->Reg_Civico_Uno], "km: ", $km_marcia;
				//exit;
				if($via_uno==NULL or $via_uno=='' or $via_uno=='    ')
					$via_uno=strtoupper(stripslashes("$myRegistroVerbale->Reg_Localita_Violazione"));
				else if(($via_uno!=NULL and $via_uno!='' and $via_uno!='    '))
					$via_uno=strtoupper(stripslashes("$myRegistroVerbale->Reg_Localita_Violazione $via_uno"));
				
				$via_uno = trim($via_uno);
				
				$via_uno=substr($via_uno,0,75);
				$data_no_sec=substr($myRegistroVerbale->Reg_Ora_Avviso,0,5);
				if($data_no_sec=='00:00' or $data_no_sec==NULL or $data_no_sec=='')
					$data_no_sec='=====';
				if($via_uno==NULL or $via_uno=='' or $via_uno=='    ')
					$via_uno='======================================================================';
				//fput_margine_new($c,$marg,$fh,"$data_no_sec");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$via_uno");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$myOraViolazione = $data_no_sec;
				$myLuogoViolazione = $via_uno;
				
				/*$query_art_bol="select * from tariffa_cds where Tar_Progr=$myRegistroVerbale->Reg_Articol_Uno]";
				$ris_art_bol=safe_query($query_art_bol);
				$art_bol=mysql_fetch_array($ris_art_bol);*/
				
				//$art_bol = $myArticoloUno;
				
				if (($myArticoloUno->Tar_Articolo=='126' && $myArticoloUno->Tar_Lettera=='bis') || ($myArticoloUno->Tar_Articolo=='180' && $myArticoloUno->Tar_Comma=='8'))
				{
					//fput_margine_new($c,$marg,$fh,"proprietario");
					$myPropCond = "proprietario";
				}
				else if ($myArticoloUno->Tar_Articolo=='94' && $myArticoloUno->Tar_Comma=='2')
				{
					//fput_margine_new($c,$marg,$fh,"proprietario");
					$myPropCond = "proprietario";
					// richiesta di emanuela per torriglia! 29/04/2015
				}
				else if ($myArticoloUno->Tar_Articolo=='193' && $myArticoloUno->Tar_Comma=='2')
				{
					//fput_margine_new($c,$marg,$fh,"proprietario");
					$myPropCond = "proprietario";
					// richiesta di emanuela per bargagli! 12/05/2015
				}
				else
				{
					//fput_margine_new($c,$marg,$fh,"conducente");
					$myPropCond = "conducente";
				}
				if($myRegistroVerbale->Reg_Tipologia_Veicolo=='altro')
					$tipo_veicolo=ucfirst($myRegistroVerbale->Reg_Altro_Veicolo);
				else
					$tipo_veicolo=ucfirst($myRegistroVerbale->Reg_Tipologia_Veicolo);
				if ($tipo_veicolo=='Autovettura')
					$tipo_veicolo="Autoveicolo";
				$veicolo=strtoupper("$myRegistroVerbale->Reg_Marca_Veicolo $myRegistroVerbale->Reg_Tipo_Veicolo");
				//fput_margine_new($c,$marg,$fh,"$tipo_veicolo $veicolo");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$myRegistroVerbale->Reg_Targa_Veicolo]");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$myTipoVeicolo = "$tipo_veicolo $veicolo";
				$myTargaVeicolo = $myRegistroVerbale->Reg_Targa_Veicolo;
				
				/******** selezione dell'art. della violazione *************/
				$comma_uno="";
				$lettera_uno="";
				$comma_due="";
				$lettera_due="";
				$comma_tre="";
				$lettera_tre="";
				$art_uno="";
				$art_due="";
				$art_tre="";
				/*$query="select * from tariffa_cds where Tar_Progr='$myRegistroVerbale->Reg_Articol_Uno]'";
				$ris1=safe_query($query);
				$articolo_uno=mysql_fetch_array($ris1);*/
				
				if ($myArticoloUno->Tar_Articolo != "") $art_uno = $myArticoloUno->Tar_Articolo;
				if ($myArticoloUno->Tar_Comma != "" && $myArticoloUno->Tar_Comma != "0") $art_uno .= "/" . $myArticoloUno->Tar_Comma;
				if ($myArticoloUno->Tar_Lettera != "") $art_uno .= " " . $myArticoloUno->Tar_Lettera;
				if ($myArticoloUno->Tar_Testo_Gen != "") $art_uno .= " " . $myArticoloUno->Tar_Testo_Gen;
				
				$tempListaArt = $art_uno;
				
				if ($myArticoloDue != NULL)
				{
					if ($myArticoloDue->Tar_Articolo != "") $art_due = $myArticoloDue->Tar_Articolo;
					if ($myArticoloDue->Tar_Comma != "" && $myArticoloDue->Tar_Comma != "0") $art_due .= "/" . $myArticoloDue->Tar_Comma;
					if ($myArticoloDue->Tar_Lettera != "") $art_due .= " " . $myArticoloDue->Tar_Lettera;
					if ($myArticoloDue->Tar_Testo_Gen != "") $art_due .= " " . $myArticoloDue->Tar_Testo_Gen;
					if ($art_due != "") $tempListaArt .= ", " . $art_due;
				}
				
				if ($myArticoloTre != NULL)
				{
					if ($myArticoloTre->Tar_Articolo != "") $art_tre = $myArticoloTre->Tar_Articolo;
					if ($myArticoloTre->Tar_Comma != "" && $myArticoloTre->Tar_Comma != "0") $art_tre .= "/" . $myArticoloTre->Tar_Comma;
					if ($myArticoloTre->Tar_Lettera != "") $art_tre .= " " . $myArticoloTre->Tar_Lettera;
					if ($myArticoloTre->Tar_Testo_Gen != "") $art_tre .= " " . $myArticoloTre->Tar_Testo_Gen;
					if ($art_tre != "") $tempListaArt .= ", " . $art_tre;
				}
				
				$myListaArticoli = $tempListaArt;
				
				$problemaRiduzione5giorni = "AMMESSARIDUZIONE5GIORNI";
				//$myArticoloUno->Tar_Riduzione_5_Giorni = 'N';
				if ($myArticoloUno->Tar_Riduzione_5_Giorni == 'N') $problemaRiduzione5giorni = "NONRIDUZIONE5GIORNI";
				if ($myArticoloDue != NULL)
				{
					if ($myArticoloDue->Tar_Riduzione_5_Giorni == 'N')
					{
						if ($problemaRiduzione5giorni == "AMMESSARIDUZIONE5GIORNI") $problemaRiduzione5giorni = "ERRORE";
					}
					else if ($myArticoloDue->Tar_Riduzione_5_Giorni == 'Y')
					{
						if ($problemaRiduzione5giorni == "NONRIDUZIONE5GIORNI") $problemaRiduzione5giorni = "ERRORE";
					}
				}
				if ($myArticoloTre != NULL)
				{
					if ($myArticoloTre->Tar_Riduzione_5_Giorni == 'N')
					{
						if ($problemaRiduzione5giorni == "AMMESSARIDUZIONE5GIORNI") $problemaRiduzione5giorni = "ERRORE";
					}
					else if ($myArticoloTre->Tar_Riduzione_5_Giorni == 'Y')
					{
						if ($problemaRiduzione5giorni == "NONRIDUZIONE5GIORNI") $problemaRiduzione5giorni = "ERRORE";
					}
				}
				
				$problemaNonEdittale = "AMMESSARIDUZIONEEDITTALE";
				//$myArticoloUno->Tar_Pagamento_Non_Ridotto = 'Y';
				if ($myArticoloUno->Tar_Pagamento_Non_Ridotto == 'Y') $problemaNonEdittale = "NONRIDUZIONEEDITTALE";
				if ($myArticoloDue != NULL)
				{
					if ($myArticoloDue->Tar_Pagamento_Non_Ridotto == 'Y')
					{
						if ($problemaNonEdittale == "AMMESSARIDUZIONEEDITTALE") $problemaNonEdittale = "ERRORE";
					}
					else if ($myArticoloDue->Tar_Pagamento_Non_Ridotto == 'N')
					{
						if ($problemaNonEdittale == "NONRIDUZIONEEDITTALE") $problemaNonEdittale = "ERRORE";
					}
				}
				if ($myArticoloTre != NULL)
				{
					if ($myArticoloTre->Tar_Pagamento_Non_Ridotto == 'Y')
					{
						if ($problemaNonEdittale == "AMMESSARIDUZIONEEDITTALE") $problemaNonEdittale = "ERRORE";
					}
					else if ($myArticoloTre->Tar_Pagamento_Non_Ridotto == 'N')
					{
						if ($problemaNonEdittale == "NONRIDUZIONEEDITTALE") $problemaNonEdittale = "ERRORE";
					}
				}
				
				if ($problemaRiduzione5giorni == "ERRORE")
				{
					$scrittaProblema = "Il verbale n. " . $myRegistroVerbale->Reg_Progr_Registro . "/" . $myRegistroVerbale->Reg_Anno;
					$scrittaProblema .= " NON VERRA' STAMPATO perch� uno degli articoli infranti non prevede il pagamento ";
					$scrittaProblema .= "ridotto del 30% nei primi 5 giorni dalla notifica, mentre gli altri s�; ";
					$scrittaProblema .= "� necessario dividere le infrazioni in pi� verbali.";
					alert ($scrittaProblema);
					//return;
				}
				else if ($problemaNonEdittale == "ERRORE")
				{
					$scrittaProblema = "Il verbale n. " . $myRegistroVerbale->Reg_Progr_Registro . "/" . $myRegistroVerbale->Reg_Anno;
					$scrittaProblema .= " NON VERRA' STAMPATO perch� uno degli articoli infranti non prevede il pagamento ";
					$scrittaProblema .= "ridotto al MINIMO EDITTALE, mentre gli altri s�; ";
					$scrittaProblema .= "� necessario dividere le infrazioni in pi� verbali.";
					alert ($scrittaProblema);
				}
				else 
				{
					$cont_avvisi++;
					
					if ($myRegistroVerbale->Reg_Motivo_Art_Uno == NULL) $descri_art_uno = $myArticoloUno->Tar_Descrizione;
					else $descri_art_uno = $myRegistroVerbale->Reg_Motivo_Art_Uno;
					$descri_art_uno = stripslashes($descri_art_uno);
					
					if ($myRegistroVerbale->Reg_Motivo_Art_Due == NULL) $descri_art_due = $myArticoloDue->Tar_Descrizione;
					else $descri_art_due = $myRegistroVerbale->Reg_Motivo_Art_Due;
					$descri_art_due = stripslashes($descri_art_due);
					
					if ($myRegistroVerbale->Reg_Motivo_Art_Tre == NULL) $descri_art_tre = $myArticoloTre->Tar_Descrizione;
					else $descri_art_tre = $myRegistroVerbale->Reg_Motivo_Art_Tre;
					$descri_art_tre = stripslashes($descri_art_tre);
					
					//fput_margine_new($c,$marg,$fh,"$tabulatore");				
					//*********** selezione del rivelatore di velocit�
					$rilev_frase="";
					if($myRegistroVerbale->Reg_Rilevatore_Velocita!=0 and $myRegistroVerbale->Reg_Rilevatore_Velocita!=NULL)
					{
						$query="select * from rilevatori_velocita where Ril_Progr='$myRegistroVerbale->Reg_Rilevatore_Velocita'";
						$ris_rilev=safe_query($query);
						$num_rilev = mysql_num_rows($ris_rilev);
						$rilev=mysql_fetch_array($ris_rilev);
						// calcolo di quanto ha superato il limite di velocit�
						$velocita=$myRegistroVerbale->Reg_Velocita_Effettiva-$myRegistroVerbale->Reg_Limite_Velocita;
						if ($num_rilev<>0)
						{
							if($myRegistroVerbale->Reg_Ora_Solare!=$myRegistroVerbale->Reg_Ora_Avviso && $myRegistroVerbale->Reg_Ora_Solare!="00:00:00" && $myRegistroVerbale->Reg_Ora_Solare!=NULL)
							{
								if($myRegistroVerbale->Reg_Impostazione_Ora_Solare=='Y')
								{
									$myRegistroVerbale->ora_legale="(impostato sull'ora solare)";
								}
								elseif($myRegistroVerbale->Reg_Impostazione_Ora_Legale=='Y')
								{
									$myRegistroVerbale->ora_legale="(impostato sull'ora legale)";
								}
								/*else
								{
									$myRegistroVerbale->ora_legale]="(impostato sull'ora solare)";
								}*/	
							}
							else
							{
								$myRegistroVerbale->ora_legale="";
							}
							$myRegistroVerbale->Reg_Differenza=$myRegistroVerbale->Reg_Velocita_Effettiva-$myRegistroVerbale->Reg_Limite_Velocita;
							$rilev[Ril_Testo]=stripslashes($rilev[Ril_Testo]);
							$eccedenza=$myRegistroVerbale->Reg_Velocita_Effettiva-$myRegistroVerbale->Reg_Limite_Velocita;						
							
							$arrayReg = array();
							foreach ($myRegistroVerbale as $key => $value)
								$arrayReg[$key] = $value;
							$rilev_frase=replace_velox($arrayReg,$rilev,$rilev[Ril_Testo]);
						}
					}
					
					$myDescrizioneArticolo = "";
					
					if($descri_art_due==NULL and $descri_art_tre==NULL)
					{
						//fput_margine_new($c,$marg,$fh,"$descri_art_uno $rilev_frase");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myDescrizioneArticolo = "$descri_art_uno $rilev_frase";
					}
					elseif($descri_art_due!=NULL and $descri_art_tre==NULL)
					{
						//fput_margine_new($c,$marg,$fh,"$descri_art_uno $descri_art_due $rilev_frase");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						if ($myArticoloUno->Tar_Tipo_Art != "ASSICURAZIONE" && $myArticoloUno->Tar_Tipo_Art != "REVISIONE")
						{
							$myDescrizioneArticolo = "$descri_art_uno $descri_art_due $rilev_frase";
						}
						else
						{
							$myDescrizioneArticolo = "$descri_art_uno Ed � stato altres� rilevato che il veicolo $descri_art_due $rilev_frase";
						}
					}
					elseif($descri_art_due!=NULL and $descri_art_tre!=NULL)
					{
						//fput_margine_new($c,$marg,$fh,"$descri_art_uno $descri_art_due $descri_art_tre $rilev_frase");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myDescrizioneArticolo = "$descri_art_uno $descri_art_due $descri_art_tre $rilev_frase";
					}
					else
					{
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
					}
					
					/****** fine selezione dell'art, ****************/
					//*************Seleziono le motivazioni di mancata contestazione immediata, gli ***************
					$motivo_contestazione="";
					if($myRegistroVerbale->Reg_Motivi_Mancata_Contestazione!=0)
					{
						$query="select Mot_Commento from motivi_mancata_contestazione_cds
								where Mot_Progr='$myRegistroVerbale->Reg_Motivi_Mancata_Contestazione'";
						$motivo_conte=single_answer_query($query);
						$motivo_conte=stripslashes($motivo_conte);
					}
					if($myRegistroVerbale->Reg_Motivi_Liberi!=NULL and $myRegistroVerbale->Reg_Motivi_Mancata_Contestazione!=0)
					{
						$myRegistroVerbale->Reg_Motivi_Liberi=stripslashes($myRegistroVerbale->Reg_Motivi_Liberi);
						$motivo_contestazione="$motivo_conte $myRegistroVerbale->Reg_Motivi_Liberi";
					}
					elseif($myRegistroVerbale->Reg_Motivi_Liberi!=NULL and $myRegistroVerbale->Reg_Motivi_Mancata_Contestazione==0)
					{
						$myRegistroVerbale->Reg_Motivi_Liberi=stripslashes($myRegistroVerbale->Reg_Motivi_Liberi);
						$motivo_contestazione="$myRegistroVerbale->Reg_Motivi_Liberi";
					}
					elseif($myRegistroVerbale->Reg_Motivi_Liberi==NULL and $myRegistroVerbale->Reg_Motivi_Mancata_Contestazione!=0)
					{
						$motivo_contestazione="$motivo_conte";
					}
					else
					{
						$motivo_contestazione=" ";
					}				
					//fput_margine_new($c,$marg,$fh,"$motivo_contestazione");
					
					$myMotivoMancataContestazione = $motivo_contestazione;
					
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					/************fine selezione motivi mancata contestazione*************************/
					//Selezione dei punti
					$tot_punti=0;
					$punti_frase='';
					$tot_punti = $myRegistroVerbale->Reg_Punti_Articolo_Uno + $myRegistroVerbale->Reg_Punti_Articolo_Due + $myRegistroVerbale->Reg_Punti_Articolo_Tre;
					if($tot_punti<>0)
					{
						////fput_margine_new($c,$marg,$fh,"Ai sensi dell'art.126-bis del C.d.S la violazione comporta a carico del");
					}
					else
					{
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
					}
					
					$myTotPuntiDecurtati = $tot_punti;
					
					//Sanzioni Accessorie
					$descri_acc="";
					$acc_frase="";
					$descri_punti="";
					
					//if($tot_punti<>0)
					{
						if($myRegistroVerbale->Reg_Sanz_Acc_Uno!=0)
						{
							$query="select San_Progr from sanzioni_accessorie
									where San_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Uno'";
							$san_progr=single_answer_query($query);
							if($san_progr!=0 and $san_progr!=NULL)
							{
								$query="select San_Descrizione from sanzioni_accessorie
										where San_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Uno'";
								$sanz_acc_uno=single_answer_query($query);
								$sanz='Y';
							}
							else
							{
								$query="select Tar_Sanz_Acc from tariffa_cds
										where Tar_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Uno'";
								$sanz_acc_uno=single_answer_query($query);
								$query="select Tar_Sanzione_Accessoria from tariffa_cds
										where Tar_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Uno'";
								$sanz=single_answer_query($query);
							}
							//echo "$sanz_acc_uno"; die;
							if($myRegistroVerbale->Reg_Sanz_Acc_Due!=0)
							{
								$query="select San_Progr from sanzioni_accessorie
										where San_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Due'";
								$san_progr=single_answer_query($query);
								if($san_progr!=0 and $san_progr!=NULL)
								{
									$query="select San_Descrizione from sanzioni_accessorie
											where San_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Due'";
									$sanz_acc_uno=single_answer_query($query);
								}
								else
								{
									$query="select Tar_Sanz_Acc from tariffa_cds
											where Tar_Progr='$myRegistroVerbale->Reg_Sanz_Acc_Due'";
									$sanz_acc_due=single_answer_query($query);
								}
							}
							if ((($myArticoloTre->Tar_Articolo=='142' && $myArticoloTre->Tar_Comma==9) || 
									($myArticoloDue->Tar_Articolo=='142' && $myArticoloDue->Tar_Comma==9) || 
									($myArticoloUno->Tar_Articolo=='142' && $myArticoloUno->Tar_Comma==9)))
							{
								$acc_frase="SANZIONE ACCESSORIA: $sanz_acc_uno $sanz_acc_due";
							}
							else if ((($myArticoloTre->Tar_Articolo=='193' && $myArticoloTre->Tar_Comma==2) || 
								($myArticoloDue->Tar_Articolo=='193' && $myArticoloDue->Tar_Comma==2) || 
								($myArticoloUno->Tar_Articolo=='193' && $myArticoloUno->Tar_Comma==2)))
							{
								$acc_frase = "SANZIONE ACCESSORIA: $sanz_acc_uno $sanz_acc_due";
							}
							else
							{
								$acc_frase="$sanz_acc_uno $sanz_acc_due";
							}
						}
						/*$query="select * from tariffa_cds where Tar_Progr='$myRegistroVerbale->Reg_Articol_Uno]'";
						$ris_uno=safe_query($query);
						$articolo_1=mysql_fetch_array($ris_uno);
						if($articolo_1[Tar_Articolo]=='142' and $articolo_1[Tar_Comma]==9)
						{
							$max_15_pti="";
						}
						else
						{
							if($articolo_1[Tar_Sanz_Penale]=='Y')
							{
								//$max_15_pti="";
							}
							else
							{
								//$max_15_pti=" (fino ad un massimo di 15 punti)";
							}	
						}
						
						if($myRegistroVerbale->Reg_Articol_Due]!=0 and $myRegistroVerbale->Reg_Articol_Due]!=NULL)
						{
							$query="select * from tariffa_cds where Tar_Progr='$myRegistroVerbale->Reg_Articol_Due]'";
							$ris_due=safe_query($query);
							$articolo_2=mysql_fetch_array($ris_due);
							if($articolo_2[Tar_Articolo]=='142' and $articolo_2[Tar_Comma]==9)
							{
								$max_15_pti="";
							}
							else
							{
								if($articolo_2[Tar_Sanz_Penale]=='Y')
								{
									$max_15_pti="";
								}
								else
								{
									//$max_15_pti=" (fino ad un massimo di 15 punti)";
								}
							}
						}
						if($myRegistroVerbale->Reg_Articol_Tre]!=0 and $myRegistroVerbale->Reg_Articol_Tre]!=NULL)
						{
							$query="select * from tariffa_cds where Tar_Progr='$myRegistroVerbale->Reg_Articol_Tre]'";
							$ris_tre=safe_query($query);
							$articolo_3=mysql_fetch_array($ris_tre);
							if($articolo_3[Tar_Articolo]=='142' and $articolo_3[Tar_Comma]==9)
							{
								$max_15_pti="";
							}
							else
							{
								if($articolo_3[Tar_Sanz_Penale]=='Y')
								{
									$max_15_pti="";
								}
								else
								{
									//$max_15_pti=" (fino ad un massimo di 15 punti)";
								}	
							}
						}*/
						$data_avviso_controllo=str_replace("-","",$myRegistroVerbale->Reg_Data_Avviso);
						$date_new_art="20070804";
						$descri_punti=$parte_uno_sanz_acc." ". $acc_frase;
						/*
						if((($articolo_3[Tar_Articolo]=='142' and $articolo_3[Tar_Comma]==9) or ($articolo_2[Tar_Articolo]=='142' and $articolo_2[Tar_Comma]==9) or ($articolo_1[Tar_Articolo]=='142' and $articolo_1[Tar_Comma]==9)) and $data_avviso_controllo>=$date_new_art)
						{
							if($c=='D054')
							{
								$descri_punti=$parte_uno_sanz_acc." trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con sanzione da un minimo di 250,00 E. ad un massimo di 1000,00 E.$max_15_pti. ".$acc_frase;
							}
							elseif($c=='C826')
							{
								$descri_punti=$parte_uno_sanz_acc." trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con sanzione da un minimo di 250,00 E. ad un massimo di 1000,00 E.$max_15_pti. ".$acc_frase;
							}
							elseif($c=='F202')
							{
								$descri_punti=$parte_uno_sanz_acc." trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con conseguente sanzione pecuniaria da un minimo di 250,00 E. ad un massimo di 1000,00 E.$max_15_pti. ".$acc_frase;
							}
							elseif($c=='A909')
							{
								$descri_punti=$parte_uno_sanz_acc." trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con conseguente sanzione pecuniaria da un minimo di 250,00 E. ad un massimo di 1000,00 E.$max_15_pti. ".$acc_frase;
							}
							else
							{
								$descri_punti=$parte_uno_sanz_acc." trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore(conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. $max_15_pti. ".$acc_frase;
							}
						}
						else
						{
							if($c=='D054')
							{
								//$descri_punti="trasgressore la decurtazione di n.$tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 30 giorni dalla definizione del procedimento, i dati personali e quelli della patente di guida del trasgressore(conducente del veicolo al momento dela commessa violazione). Non ottemperando all'invito si procedera' ai sensi dall'art.126-bis e dall'art.180 comma 8 del C.d.S. con sanzione da un minimo di 357,00 E. ad un massimo di 1433,00 E. Per i titolari di patente di guida conseguita dopo il 01/10/03, in caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio, la decurtazione dei punti ivi indicati avverra' in misura doppia (fino ad un massimo di 15 punti). ".$acc_frase;
								$descri_punti=" trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con sanzione da un minimo di 250,00 E. ad un massimo di 1000,00 E. In caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio della patente di guida, la decurtazione dei punti ivi indicati avverra' in misura doppia$max_15_pti. ".$acc_frase;
							}
							elseif($c=='C826')
							{
								$descri_punti=" trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con sanzione da un minimo di 250,00 E. ad un massimo di 1000,00 E. In caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio della patente di guida, la decurtazione dei punti ivi indicati avverra' in misura doppia$max_15_pti. ".$acc_frase;
							}
							elseif($c=='F202')
							{
								$descri_punti=" trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con conseguente sanzione pecuniaria da un minimo di 250,00 E. ad un massimo di 1000,00 E. In caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio della patente di guida, la decurtazione dei punti ivi indicati avverra' in misura doppia$max_15_pti. ".$acc_frase;
							}
							elseif($c=='A909')
							{
								$descri_punti=$parte_uno_sanz_acc." trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. con conseguente sanzione pecuniaria da un minimo di 250,00 E. ad un massimo di 1000,00 E. In caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio della patente di guida, la decurtazione dei punti ivi indicati avverra' in misura doppia$max_15_pti. ".$acc_frase;
							}
	
							else
							{
								//$descri_punti="trasgressore la decurtazione di n.$tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 30 giorni dalla definizione del procedimento, i dati personali e quelli della patente di guida del trasgressore(conducente del veicolo al momento dela commessa violazione). Non ottemperando all'invito si procedera' ai sensi dall'art.126-bis e dall'art.180 comma 8 del C.d.S. Per i titolari di patente di guida conseguita dopo il 01/10/03, in caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio, la decurtazione dei punti ivi indicati avverra' in misura doppia (fino ad un massimo di 15 punti). ".$acc_frase;
								$descri_punti=" trasgressore la decurtazione di n. $tot_punti punti dalla patente di guida. A tal fine il proprietario del veicolo dovra' fornire a questo organo di polizia, entro 60 gg dalla notifica del presente verbale, i dati personali e quelli della patente di guida del trasgressore (conducente del veicolo al momento della commessa violazione). Non ottemperando all'invito si procedera' ai sensi dell'art.126-bis del C.d.S. In caso di accertamento di violazione effettuato nei primi 3 anni dal rilascio della patente di guida, la decurtazione dei punti ivi indicati verra' raddoppiata$max_15_pti. ".$acc_frase;
							}
						}	
						*/
						//fput_margine_new($c,$marg,$fh,"$descri_punti");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
					}
					
					/*if ($_SESSION['username'] == "marcom")
					{
						alert ($descri_punti);
						echo "<br>" . $descri_punti;
						return;
					}*/
					
					$myDescrizioneSanzioniAccessorie = $descri_punti;
					$query_gesto = "SELECT Ges_Denominazione, Ges_P_Iva, Ges_Progr 
		                			FROM gestore, parametri_cds 
		                			WHERE Ges_Progr = Par_Gestore";
					$result_gesto = safe_query($query_gesto);
					$num_gesto = mysql_num_rows($result_gesto);
					$res_gesto = mysql_fetch_row($result_gesto);
					$cur_sede_ges=new ges_sede($res_gesto[2],'CDS');
					$cap_ges = ($cur_sede_ges->Via_Cap==NULL?$cur_sede_ges->Com_Cap:$cur_sede_ges->Via_Cap);
					$gestore=strtoupper($res_gesto[0]);
					$indirComune="$cap_ges $cur_sede_ges->Com_Nome ($cur_sede_ges->Pro_Sigla)";
					/*if($cur_sede_ges->Seg_Interno!=0 and $cur_sede_ges->Seg_Interno!=NULL)
					{
						$indirizo_comne_bis="$cur_sede_ges->Top_Nome $cur_sede_ges->Via_Nome $cur_sede_ges->Seg_Civico/$cur_sede_ges->Seg_Interno";
					}
					else
					{
						$indirizo_comne_bis="$cur_sede_ges->Top_Nome $cur_sede_ges->Via_Nome $cur_sede_ges->Seg_Civico";
					}*/
					if($myParametroDiQuestoAnno->Par_Tipo_Riscossione=="Diretta")
					{
						$cur_ccp = new ccp($c,"CDS");
						$ccp_progr=($cur_ccp->Ccp_Progr==NULL?0:$cur_ccp->Ccp_Progr);
						$num_ccp=$cur_ccp->Ccp_Conto_Comune;
						$intestazione=$cur_ccp->Ccp_Intestazione;
					}
					elseif($myParametroDiQuestoAnno->Par_Tipo_Riscossione=="CNE")
					{
						$cur_cnc= new cnc($c,"CDS");
						$cnc_progr=($cur_cnc->Coc_Progr==NULL?0:$cur_cnc->Coc_Progr);
						$cur_sede_cnc= new sede($cnc_progr,"CDS");
						$num_ccp=$cur_sede_cnc->Sed_Num_Ccp;
						$intestazione=$cur_sede_cnc->Sed_Intestazione_Ccp;
					}
					if($num_ccp==NULL or $num_ccp=='' or $num_ccp==0)
					{
						$num_ccp="        ";
					}				
					/*$query="select * from notifica_verbale_cds where Not_Progr='$notifica_pr'";
					$resu=safe_query($query);
					$notif=mysql_fetch_array($resu);*/
					// modalit� di pagamento in misura ridotta
					$tot_rid_it=number_format($myRegistroVerbale->Reg_Totale_Ridotto,2,',','.');
					//$totale_ridotto=$myRegistroVerbale->Reg_Totale_Ridotto]+$myParametroDiQuestoAnno->Par_Spese_Notifica_Verbale+$myParametroDiQuestoAnno->Par_Spese_Ricerca_Verbale;
					$totale_ridotto = $myRegistroVerbale->Reg_Totale_Ridotto + 
										$myNotificaCorrente->Not_Spese_Notifica +
										$myNotificaCorrente->Not_Spese_Ricerca - 
										$gia_pagato;
					if($totale_ridotto<0){$totale_ridotto=0;}
					$totale_ridotto_it=number_format($totale_ridotto,2,',','.');
					$totale_ridotto_uno = $totale_ridotto + 
											$myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale;
					if($totale_ridotto_uno<0){$totale_ridotto_uno=$myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale;}
					$totale_ridotto_it_bis=number_format($totale_ridotto_uno,2,',','.');
					$pag_misura_rid="";
					$query_misura_rid ="select Ccp_Intestazione,Ccp_Conto_Comune
									    from ccp_comune_cds
										where Ccp_Comune='$c'";
					$result_misura_rid = safe_query($query_misura_rid);
					$res_misura_rid= mysql_fetch_row($result_misura_rid);
					$tot_spese=$myParametroDiQuestoAnno->Par_Spese_Notifica_Verbale+$myParametroDiQuestoAnno->Par_Spese_Ricerca_Verbale;
					$tot_spese_it=number_format($tot_spese,2,',','.');	
					/*$tot_spese_tmp = $myNotificaCorrente->Not_Spese_Notifica +
										$myNotificaCorrente->Not_Spese_Ricerca;
					$tot_spese_tmp=number_format($tot_spese_tmp,2,',','.');*/
					
					//PER TUTTI I COMUNI
					$query="SELECT
						Count(registro_cronologico_cds.Reg_Progr) AS conta_pag,
						pagamenti_volontari_cds.Pag_Registro,
						pagamenti_volontari_cds.Pag_Data_Pag,
						Sum(pagamenti_volontari_cds.Pag_Importo_Pag) AS tot_impoto_pagato,
						pagamenti_volontari_cds.Pag_Tipo_Pag,
						pagamenti_volontari_cds.Pag_Trasgressore,
						pagamenti_volontari_cds.Pag_Tipo_Versamento,
						pagamenti_volontari_cds.Pag_Libero
						FROM
						registro_cronologico_cds ,
						pagamenti_volontari_cds
						WHERE
						registro_cronologico_cds.Reg_Progr ='$myRegistroVerbale->Reg_Progr'  AND
						registro_cronologico_cds.Reg_Progr =  pagamenti_volontari_cds.Pag_Registro
						GROUP BY
						registro_cronologico_cds.Reg_Progr";					
					$rex=safe_query($query);
					$tot_pagato_tmp=mysql_fetch_array($rex);
					//$numero_tot_pag=mysql_num_rows($rex);	
					$importo1=$totale_ridotto-$tot_pagato_tmp[tot_impoto_pagato];
					$importo2=$totale_ridotto_uno-$tot_pagato_tmp[tot_impoto_pagato];				
					
					//Per tutti i comuni tranne Orte, Riccardo a deciso di fare uscire anche per gli altri comuni nella fase di stampa dei verbali se ci fossero dei pagamenti fare la differenza.
					if($tot_pagato_tmp[conta_pag] > 0)
					{
						$pagamenti_efettuati=number_format($tot_pagato_tmp[tot_impoto_pagato],2,',','.');						
						$importo1=number_format($importo1,2,',','.');
						$importo2=number_format($importo2,2,',','.');
						extract_date($tot_pagato_tmp[Pag_Data_Pag]);						
						if($tot_pagato_tmp[conta_pag] > 1)
						{
							$titolo_pagamento="L'accertamento include $tot_pagato_tmp[conta_pag] pagamenti per un totale di � $pagamenti_efettuati.";
						}
						elseif($tot_pagato_tmp[conta_pag] == 1)
						{							
							$titolo_pagamento="L'accertamento include un pagamento di � $pagamenti_efettuati effettuato in data $tot_pagato_tmp[Pag_Data_Pag]. ";
						}
						else
						{
							$titolo_pagamento="";
						}
						/*if ($par_NumeroBollettini == 1)
							$pag_misura_rid="$titolo_pagamento E' ammesso il pagamento di � $importo1 (importo sanzione pecuniaria � $tot_rid_it, residuo spese di notifica e ricerca). Il pagamento del verbale dovr� essere effettuato entro 60 gg. dalla notifica, tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno";
						else 
							$pag_misura_rid="$titolo_pagamento E' ammesso il pagamento di � $importo1 solo quando la notifica dell'atto e' avvenuta direttamente nelle mani del destinatario. In tutti gli altri casi si rende dovuto l'importo di � $importo2. (importo sanzione pecuniaria � $tot_rid_it, residuo spese di notifica e ricerca). Il pagamento del verbale dovr� essere effettuato entro 60 gg. dalla notifica, tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno";*/
					}
					else					
					{
						/*if ($par_NumeroBollettini == 1)
							$pag_misura_rid="E' ammesso il pagamento di � $totale_ridotto_it (importo sanzione pecuniaria � $tot_rid_it, residuo spese di notifica e ricerca). Il pagamento del verbale dovr� essere effettuato entro 60 gg. dalla notifica, tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno"; //old scritta
						else
							$pag_misura_rid="E' ammesso il pagamento di � $totale_ridotto_it solo quando la notifica dell'atto e' avvenuta direttamente nelle mani del destinatario. In tutti gli altri casi si rende dovuto l'importo di � $totale_ridotto_it_bis. (importo sanzione pecuniaria � $tot_rid_it, residuo spese di notifica e ricerca). Il pagamento del verbale dovr� essere effettuato entro 60 gg. dalla notifica, tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno"; //old scritta*/
					}
					
					
					
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					/*********** selezione del trasgressore ********************/
					$query="select Tra_Tipo_Trasgressore from trasgressore_cds
							where Tra_Trasgressore='$questoContribuente->Con_Progr' and
							Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
					$tipo_trasgressore=single_answer_query($query);
					$myTrasgressore="";
					$esercente="";
					
					/*if ($myRegistroVerbale->Reg_Progr_Registro == 605)
						alert ("tipo trasgressore  "  . $tipo_trasgressore);*/
					
					if ($tipo_trasgressore=="obbligato")
					{
						$myObbligato = new contribuente($questoContribuente->Con_Progr, $c);
						//Seleziono il trasgressore se lo conosco.
						$query="select Tra_Trasgressore from trasgressore_cds where
								Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr' and
								Tra_Tipo_Trasgressore='trasgressore'";
						$trasgre_progr=single_answer_query($query);
						//Controllo se il trasgressore � minorenne, in caso affermativo seleziono l'esercante la patria potest�.
						$myTrasgressore = new contribuente($trasgre_progr,$c);
						$oggi=date("d/m/Y");
						extract_date($myTrasgressore->Con_Data_Nasc);
						$giorni=cds_giorni($a,$myTrasgressore->Con_Data_Nasc,$oggi);
						$anni_ok=floor($giorni/365);
						if($anni_ok<18)
						{
							//Seleziono l'esercente la patria potest�
							$query="select Tra_Trasgressore from trasgressore_cds
									where Tra_Altro_Trasgressore='Esercente La Patria Potesta\''
									and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
							$esercente_progr=single_answer_query($query);
							$esercente=new contribuente($esercente_progr,$c);
						}
					}
					elseif($tipo_trasgressore=="trasgressore")
					{
						$myTrasgressore = new contribuente($questoContribuente->Con_Progr,$c);
						$query="select Tra_Trasgressore from trasgressore_cds where
								Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr' and Tra_Tipo_Trasgressore='obbligato'";
						$obbli_progr=single_answer_query($query);
						$myObbligato = new contribuente($obbli_progr,$c);
						//Parte per scrivere l'indirizzo corretto nel caso si debba notificare la multa ad entrambi
						
						if ($myObbligato->Con_Via_Sec!=NULL && $myObbligato->Con_Via_Sec!=0)
						{
							//seleziono la classe indirizzo x il domicilio
							$via_progr = $myObbligato->Con_Via_Sec;
						}
						else
						{
							$via_progr = $myObbligato->Con_Via_Res;
						}
						$address = new indirizzo ($via_progr);
						$civico = $myObbligato->FunzioneInutileCivico();
						
						$nome="$myObbligato->Con_Cognome $myObbligato->Con_Nome";
						$nome=stripslashes($nome);
						$indirDestinatario="$address->Top_Nome $address->Via_Nome $civico";
						$indirDestinatario=stripslashes($indirDestinatario);
						//$indirizzo=substr($indirizzo,0,30);
						$indirDestinatario=substr($indirDestinatario,0,80);
						if($address->Via_Cap==NULL or $address->Via_Cap==0)
						{
							$cap=$address->Com_Cap;
						}
						else
						{
							$cap=$address->Via_Cap;
						}
						$cap=$cap;
						if($address->Pro_Sigla==NULL)
						{
							$query="select Via_Comune from via where Via_Progr='$via_progr'";
							$via_comune=single_answer_query($query);
							$query="select Com_Paese from comune where Com_Nome='$address->Com_Nome' and Com_CC='$via_comune'";
							$paese=single_answer_query($query);
							$query="select Naz_Nome from nazione where Naz_Cod='$paese'";
							$naz_nome=single_answer_query($query);
							$maiuscoloCittaSigla="$address->Com_Nome ($naz_nome)";
						}
						else
						{
							$maiuscoloCittaSigla="$address->Com_Nome ($address->Pro_Sigla)";
						}
						//$maiuscoloCittaSigla=substr($maiuscoloCittaSigla,0,30);
						$maiuscoloCittaSigla=substr($maiuscoloCittaSigla,0,40);
						
						
						$oggi=date("d/m/Y");
						extract_date($myTrasgressore->Con_Data_Nasc);
						$giorni=cds_giorni($a,$myTrasgressore->Con_Data_Nasc,$oggi);
						$anni_ok=floor($giorni/365);
						if($anni_ok<18)
						{
							//Seleziono l'esercente la patria potest�
							$query="select Tra_Trasgressore from trasgressore_cds
									where Tra_Altro_Trasgressore='Esercente La Patria Potesta\''
									and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
							$esercente_progr=single_answer_query($query);
							$esercente=new contribuente($esercente_progr,$c);
						}
					}
					else if ($tipo_trasgressore=="coincidente")
					{
						$myObbligato = new contribuente($questoContribuente->Con_Progr, $c);
						$myTrasgressore = NULL;
						$oggi=date("d/m/Y");
						extract_date($myObbligato->Con_Data_Nasc);
						$giorni=cds_giorni($a,$myObbligato->Con_Data_Nasc,$oggi);
						$anni_ok=floor($giorni/365);
						if($anni_ok<18)
						{
							//Seleziono l'esercente la patria potest�
							$query="select Tra_Trasgressore from trasgressore_cds
									where Tra_Altro_Trasgressore='Esercente La Patria Potesta\''
									and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
							$esercente_progr=single_answer_query($query);
							$esercente=new contribuente($esercente_progr,$c);
						}
					}
					else
					{
						$myObbligato = new contribuente($questoContribuente->Con_Progr, $c);
						$myTrasgressore = new contribuente($questoContribuente->Con_Progr, $c);
						$oggi=date("d/m/Y");
						extract_date($myObbligato->Con_Data_Nasc);
						$giorni=cds_giorni($a,$myObbligato->Con_Data_Nasc,$oggi);
						$anni_ok=floor($giorni/365);
						if($anni_ok<18)
						{
							//Seleziono l'esercente la patria potest�
							$query="select Tra_Trasgressore from trasgressore_cds
							where Tra_Altro_Trasgressore='Esercente La Patria Potesta\''
							and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
							$esercente_progr=single_answer_query($query);
							$esercente=new contribuente($esercente_progr,$c);
						}
					}
					/*********** fine selezione del trasgressore ***************/
					$data_identificazione_trasgre = $myNotificaCorrente->Not_Data_Identificazione_Trasgressore;
					extract_date($data_identificazione_trasgre);
					
					$tempIdentificazione = "";
					
					if($data_identificazione_trasgre!=NULL and $data_identificazione_trasgre!="00/00/0000")
					{
						$tipo_trasgressore_data="";
						if($esercente->Con_Progr!=0 and $esercente->Con_Progr!=NULL)
						{
							$tipo_trasgressore_data="esercente la patria potesta'";
						}
						else
						{
							if($tipo_trasgressore=='coincidente')
							{
								$tipo_trasgressore_data="proprietario o solidale";
							}
							elseif($tipo_trasgressore=='trasgressore')
							{
								$tipo_trasgressore_data="conducente/trasgressore";
							}
							elseif($tipo_trasgressore=='obbligato')
							{
								$tipo_trasgressore_data='proprietario o solidale';
							}
						}
						//fput_margine_new($c,$marg,$fh,"Identificazione dati ".$tipo_trasgressore_data." avvenuta in data $data_identificazione_trasgre");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$tempIdentificazione = "Identificazione dati ".$tipo_trasgressore_data." avvenuta in data $data_identificazione_trasgre";
					}
					else
					{
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
					}
					
					$myIdentificazione = $tempIdentificazione;
					
					$cognome_upp=strtoupper($myObbligato->Con_Cognome);
					$nome_upp=strtoupper($myObbligato->Con_Nome);
					$nome_assieme_upp="$cognome_upp $nome_upp";
					$nome_assieme_upp=substr($nome_assieme_upp,0,35);
					if($myObbligato->Con_Progr!=0 and $myObbligato->Con_Progr!=NULL)
					{
						$nome_proprietario="$nome_assieme_upp ($myObbligato->Con_Progr)";
					}
					else
					{
						$nome_proprietario="$nome_assieme_upp";
					}
					if($myTrasgressore->Con_Progr!=0 and $myTrasgressore->Con_Progr!=NULL)
					{
						$nome_assieme_trasgre_upp=strtoupper("$myTrasgressore->Con_Cognome $myTrasgressore->Con_Nome");
						$nome_assieme_trasgre_upp=substr($nome_assieme_trasgre_upp,0,35);
						$nome_trasgre_upp=strtoupper("$nome_assieme_trasgre_upp ($myTrasgressore->Con_Progr)");
					}
					else
					{
						$nome_assieme_trasgre_upp=strtoupper("$myTrasgressore->Con_Cognome $myTrasgressore->Con_Nome");
						$nome_assieme_trasgre_upp=substr($nome_assieme_trasgre_upp,0,35);
						$nome_trasgre_upp=strtoupper("$nome_assieme_trasgre_upp");
					}
					
					//fput_margine_new($c,$marg,$fh,"$nome_proprietario");
					$myNomeProprietario = $nome_proprietario;
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					// stampo luogo, data di nascita e residenza proprietario e trasgressore solo se non  � una ditta
					
					$tempNatoA = "";
					if($myObbligato->Con_Tipo!='D')
					{
						$comune_nasc=strtoupper($myObbligato->Con_Nome_Com_Nasc);
						store_date_cds($myObbligato->Con_Data_Nasc,$data_nasc_temp);
						extract_date($myObbligato->Con_Data_Nasc);
						if($comune_nasc==NULL)
						{
							$naz_nasc=strtoupper("$myObbligato->Con_Nome_Paese_Nasc");
							////fput_margine_new($c,$marg,$fh,"Nato in: $naz_nasc il $current->Con_Data_Nasc");						
							//fput_margine_new($c,$marg,$fh,"Nato il $current->Con_Data_Nasc");
							//fput_margine_new($c,$marg,$fh,"$tabulatore");
							$tempNatoA = "Nato il $myObbligato->Con_Data_Nasc";
						}
						else
						{
							//fput_margine_new($c,$marg,$fh,"Nato a: $comune_nasc il $current->Con_Data_Nasc");
							//fput_margine_new($c,$marg,$fh,"$tabulatore");
							$tempNatoA = "Nato a: $comune_nasc il $myObbligato->Con_Data_Nasc";
						}
					}
					else
					{
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
					}
					
					$myNascitaProprietario = $tempNatoA;
					
					$interno="";
					$esp_lett="";
					$esp_num="";
					$scala="";
					$piano="";
					//Devo selezionare la residenza dell'obbligato in solido e del trasgressore
					$res_obbligato = new indirizzo($myObbligato->Con_Via_Res);
					$res_trasgre = new indirizzo($myTrasgressore->Con_Via_Res);
					$comune_res_obbli=strtoupper("$res_obbligato->Com_Nome");
					if($myObbligato->Civ_Interno!=0 and $myObbligato->Civ_Interno!=NULL)
					{
						$interno="/$myObbligato->Civ_Interno";
					}
					else
					{
						$interno="";
					}
					if($myObbligato->Civ_Esponente_Lett!=NULL)
					{
						$esp_lett="/$myObbligato->Civ_Esponente_Lett";
					}
					else
					{
						$esp_lett="";
					}			
					if($myObbligato->Civ_Esponente!=NULL)
					{
						$esp_num="/$myObbligato->Civ_Esponente";
					}
					else
					{
						$esp_num="";
					}
					if($myObbligato->Civ_Scala!=NULL)
					{
						$scala="/$myObbligato->Civ_Scala";
					}
					else
					{
						$scala="";
					}
					if($myObbligato->Civ_Piano!=NULL)
					{
						$piano="/$myObbligato->Civ_Piano";
					}
					else
					{
						$piano="";
					}
					$civico_obbli="$myObbligato->Civ_Num$esp_num$esp_lett$scala$interno$piano";
					$indirResidenzaObbligato=strtoupper("$res_obbligato->Top_Nome $res_obbligato->Via_Nome $civico_obbli");
					if($myObbligato->Con_Tipo!='D')
					{
						//fput_margine_new($c,$marg,$fh,"Res. $comune_res_obbli ($res_obbligato->Pro_Sigla)");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$tempResid = "Res. $comune_res_obbli ($res_obbligato->Pro_Sigla)";
					}
					else
					{
						//fput_margine_new($c,$marg,$fh,"Con sede a $comune_res_obbli ($res_obbligato->Pro_Sigla)");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$tempResid = "Con sede a $comune_res_obbli ($res_obbligato->Pro_Sigla)";
					}					
					//fput_margine_new($c,$marg,$fh,"in $indirResidenzaObbligato");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					
					$myResidenzaProprietario = $tempResid;
					$myIndirizzoProprietario = "in $indirResidenzaObbligato";
					
					$myNomeTrasgressore = "";
					$myNascitaTrasgressore = "";
					$myResidenzaTrasgressore = "";
					$myIndirizzoTrasgressore = "";
					if($myTrasgressore->Con_Progr!=0 and $myTrasgressore->Con_Progr!=NULL)
					{
						//fput_margine_new($c,$marg,$fh,"$nome_trasgre_upp");					
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myNomeTrasgressore = $nome_trasgre_upp;
						
						$comune_nasc_trasgre=strtoupper("$myTrasgressore->Con_Nome_Com_Nasc");
						store_date_cds($myTrasgressore->Con_Data_Nasc,$data_nasc_temp);
						extract_date($myTrasgressore->Con_Data_Nasc);
						
						if($myTrasgressore->Con_Tipo!='D')
						{
							if($comune_nasc_trasgre==NULL)
							{
								$naz_nasc=strtoupper("$myTrasgressore->Con_Nome_Paese_Nasc");
								////fput_margine_new($c,$marg,$fh,"Nato in: $naz_nasc il $myTrasgressore->Con_Data_Nasc");
								//fput_margine_new($c,$marg,$fh,"Nato il $myTrasgressore->Con_Data_Nasc");
								//fput_margine_new($c,$marg,$fh,"$tabulatore");
								$myNascitaTrasgressore = "Nato il $myTrasgressore->Con_Data_Nasc";
							}
							else
							{
								//fput_margine_new($c,$marg,$fh,"Nato a: $comune_nasc_trasgre il $myTrasgressore->Con_Data_Nasc");
								//fput_margine_new($c,$marg,$fh,"$tabulatore");
								$myNascitaTrasgressore = "Nato a: $comune_nasc_trasgre il $myTrasgressore->Con_Data_Nasc";
							}
						}
						else
						{
							//fput_margine_new($c,$marg,$fh,"");
							//fput_margine_new($c,$marg,$fh,"$tabulatore");
							$myNascitaTrasgressore = "";
						}
						
						$comune_res_trasgre=strtoupper("$res_trasgre->Com_Nome");
						$interno_trasgre="";
						$esp_lett_trasgre="";
						$esp_num_trasgre="";
						$scala_trasgre="";
						$piano_trasgre="";
						if($myTrasgressore->Civ_Interno!=0 and $myTrasgressore->Civ_Interno!=NULL)
						{
							$interno_trasgre="/$myTrasgressore->Civ_Interno";
						}
						else
						{
							$interno_trasgre="";
						}
						if($myTrasgressore->Civ_Esponente_Lett!=NULL)
						{
							$esp_lett_trasgre="/$myTrasgressore->Civ_Esponente_Lett";
						}
						else
						{
							$esp_lett_trasgre="";
						}
						if($myTrasgressore->Civ_Esponente!=NULL)
						{
							$esp_num_trasgre="/$myTrasgressore->Civ_Esponente";
						}
						else
						{
							$esp_num_trasgre="";
						}
						if($myTrasgressore->Civ_Scala!=NULL)
						{
							$scala_trasgre="/$myTrasgressore->Civ_Scala";
						}
						else
						{
							$scala_trasgre="";
						}
						if($myTrasgressore->Civ_Piano!=NULL)
						{
							$piano_trasgre="/$myTrasgressore->Civ_Piano";
						}
						else
						{
							$piano_trasgre="";
						}
						$civico_trasgre="$myTrasgressore->Civ_Num$esp_num_trasgre$esp_lett_trasgre$scala_trasgre$interno_trasgre$piano_trasgre";
						$indirResidenzaTrasgressore=strtoupper("$res_trasgre->Top_Nome $res_trasgre->Via_Nome $civico_trasgre");
						if($myTrasgressore->Con_Tipo!='D')
						{
							//fput_margine_new($c,$marg,$fh,"Res. $comune_res_trasgre ($res_trasgre->Pro_Sigla)");
							//fput_margine_new($c,$marg,$fh,"$tabulatore");
							$myResidenzaTrasgressore = "Res. $comune_res_trasgre ($res_trasgre->Pro_Sigla)";
						}
						else
						{
							//fput_margine_new($c,$marg,$fh,"Con sede a $comune_res_trasgre ($res_trasgre->Pro_Sigla)");
							//fput_margine_new($c,$marg,$fh,"$tabulatore");
							$myResidenzaTrasgressore = "Con sede a $comune_res_trasgre ($res_trasgre->Pro_Sigla)";
						}
						//fput_margine_new($c,$marg,$fh,"in $indirResidenzaTrasgressore");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myIndirizzoTrasgressore = "in $indirResidenzaTrasgressore";
					}
					else
					{
						//fput_margine_new($c,$marg,$fh,"NON IDENTIFICATO");
						
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myNomeTrasgressore = "NON IDENTIFICATO";
					}
					//Dati dell'esercente la patria potest� (se c'�)
					if($esercente->Con_Progr!=0 and $esercente->Con_Progr!=NULL)
					{
						$eser_nome=strtoupper("$esercente->Con_Cognome $esercente->Con_Nome ($esercente->Con_Progr)");
					}
					else
					{
						$eser_nome=strtoupper("$esercente->Con_Cognome $esercente->Con_Nome");
					}
					////fput_margine_new($c,$marg,$fh,"$eser_nome|");
	                if($esercente->Con_Progr!=NULL and $esercente->Con_Progr!=0)
					{
						$comune_nasc_eser=strtoupper("$esercente->Con_Nome_Com_Nasc");
						store_date_cds($esecente->Con_Data_Nasc,$data_nasc_temp);
						extract_date($esercente->Con_Data_Nasc);
						if($comune_nasc_eser==NULL)
						{
							$naz_nasc=strtoupper("$esercente->Con_Nome_Paese_Nasc");
							//$nasc_eser="Nato in: $naz_nasc il $esercente->Con_Data_Nasc";
							$nasc_eser="Nato il $esercente->Con_Data_Nasc";
						}
						else
						{
							$nasc_eser="Nato a: $comune_nasc_eser il $esercente->Con_Data_Nasc";
						}
						////fput_margine_new($c,$marg,$fh,"Nato a: $comune_nasc_eser il $esercente->Con_Data_Nasc|");
					}
					if($esercente->Con_Progr!=NULL and $esercente->Con_Progr!=0)
					{
						$res_eser =new indirizzo($esercente->Con_Via_Res);
						$comune_res_eser=strtoupper("$res_eser->Com_Nome");
						if($esercente->Civ_Interno!=0 and $esercente->Civ_Interno!=NULL)
						{
							if($esercente->Civ_Esponente_Lett!=NULL)
							{
								$civico_eser="$esercente->Civ_Num/$esercente->Civ_Esponente_Lett/$esercente->Civ_Interno";
							}
							else
							{
								$civico_eser="$esercente->Civ_Num/$esercente->Civ_Interno";
							}
						}
						else
						{
							if($esercente->Civ_Esponente_Lett!=NULL)
							{
								$civico_eser="$esercente->Civ_Num/$esercente->Civ_Esponente_Lett";
							}
							else
							{
								$civico_eser="$esercente->Civ_Num";
							}
						}
						$indirResidenzaEsercente=strtoupper("$res_eser->Top_Nome $res_eser->Via_Nome $civico_eser");
						$ind_eser1="Res. $comune_res_eser ($res_eser->Pro_Sigla)";
						$ind_eser2="in $indirResidenzaEsercente";
						////fput_margine_new($c,$marg,$fh,"Res. $comune_res_eser in $indirResidenzaEsercente|");
					}
					
					
					$myEsercentePotesta_1 = "";
					$myEsercentePotesta_2 = "";
					$myEsercentePotesta_3 = "";
					$myEsercentePotesta_4 = "";
					$myEsercentePotesta_5 = "";
					
					if($esercente->Con_Progr!=NULL and $esercente->Con_Progr!=0)
					{
						//chr(11)  serve per andare a capo
						$testo_esercente="Esercente la Patria Potest�".$tabulatore."$eser_nome".$tabulatore."$nasc_eser".$tabulatore."$ind_eser1".$tabulatore."$ind_eser2";
						//fput_margine_new($c,$marg,$fh,"$testo_esercente");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myEsercentePotesta_1 = "Esercente la Patria Potest�";
						$myEsercentePotesta_2 = $eser_nome;
						$myEsercentePotesta_3 = $nasc_eser;
						$myEsercentePotesta_4 = $ind_eser1;
						$myEsercentePotesta_5 = $ind_eser2;
					}
					else
					{
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
					}
					
					$numero_spesa_notifica = $myParametroDiQuestoAnno->Par_Spese_Notifica_Verbale;
						//alert ($numero_spesa_notifica);
						//$numero_spesa_notifica = str_replace(".", ",", $numero_spesa_notifica);
					$numero_spesa_notifica = number_format ($numero_spesa_notifica, 2, ",", ".");
					//alert ($numero_spesa_notifica);
					//fput_margine_new($c,$marg,$fh,"$numero_spesa_notifica");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					
					$mySpeseNotifica = $numero_spesa_notifica;
					
					
					//*************    Selezione degli accertatori
					$query="select * from accertatori_cds where Acc_Progr='$myRegistroVerbale->Reg_Accertatore_Uno'";
					$risu1=safe_query($query);
					$accertatore_uno=mysql_fetch_array($risu1);
					$query="select * from accertatori_cds where Acc_Progr='$myRegistroVerbale->Reg_Accertatore_Due'";
					$risu2=safe_query($query);
					$accertatore_due=mysql_fetch_array($risu2);
					$query="select * from accertatori_cds where Acc_Progr='$myRegistroVerbale->Reg_Accertatore_Tre'";
					$risu3=safe_query($query);
					$accertatore_tre=mysql_fetch_array($risu3);
					$accertatore_uno[Acc_Accertatore]=stripslashes($accertatore_uno[Acc_Accertatore]);
					
					$myAccertatore1 = $accertatore_uno['Acc_Accertatore'];
					$myAccertatore2 = $accertatore_due['Acc_Accertatore'];
					$myAccertatore3 = $accertatore_tre['Acc_Accertatore'];
					
					$myAccertatore1 .= " (Matr: " . $accertatore_uno['Acc_Matricola'] . ")";
					$myListaAccertatori = $myAccertatore1;
					if ($myAccertatore2 != "")
					{
						$myAccertatore2 .= " (Matr: " . $accertatore_due['Acc_Matricola'] . ")";
						$myListaAccertatori .= ", " . $myAccertatore2;
					}
					if ($myAccertatore3 != "")
					{
						$myAccertatore3 .= " (Matr: " . $accertatore_tre['Acc_Matricola'] . ")";
						$myListaAccertatori .= ", " . $myAccertatore3;
					}
					
					//********  Responsabile immissione dati
					/*$query="select Par_Responsabile_Immissione_Dati, Par_Responsabile_Procedimento from
							parametri_cds where Par_Comune='$c' and Par_Anno='$a'";
					$ris_responsabile=safe_query($query);
					$responsabile=mysql_fetch_array($ris_responsabile);
					
					$responsabile[Par_Responsabile_Immissione_Dati]=stripslashes($responsabile[Par_Responsabile_Immissione_Dati]);
					
					//fput_margine_new($c,$marg,$fh,"$responsabile[Par_Responsabile_Immissione_Dati]");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					$myResponsabile = $responsabile['Par_Responsabile_Immissione_Dati'];*/
					
					$myResponsabile = stripslashes($myParametroDiQuestoAnno->Par_Responsabile_Immissione_Dati);
					
					if (strlen($myParametroDiQuestoAnno->Par_Responsabile_Procedimento)>0)
					{
						/*$responsabile[Par_Responsabile_Procedimento]=stripslashes($responsabile[Par_Responsabile_Procedimento]);
						//fput_margine_new($c,$marg,$fh,"$responsabile[Par_Responsabile_Procedimento]");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myRespProcedimento = $responsabile['Par_Responsabile_Procedimento'];*/
						$myRespProcedimento = stripslashes($myParametroDiQuestoAnno->Par_Responsabile_Procedimento);
					}
					else
					{
						//fput_margine_new($c,$marg,$fh," ");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$myRespProcedimento = "";
					}
			
			
				//Per stampare questa parte dall'inizio del bollettino devo togliere e mettere il carattere doppio per la prima riga
				//altrimenti stampa col carattere grosso tutto (io metterei addirittura il carattere condensato)
				//Prima della stampa del bollettino a lato ci andranno scritti gli orari di ricevimento salvati nella variabile $info
				if($myParametroDiQuestoAnno->Par_Informazioni_Verbale!=NULL)
				{
					$info=$myParametroDiQuestoAnno->Par_Informazioni_Verbale;
				}
				else
				{
					$info=$info;
				}
				$info=stripslashes($info);
				//Primo numero spazi prima e secondo numero spazi dopo: forse gli spazi liberi son 27 (da verificare)
				if($info!=NULL and $conferma_testo=='Y')
				{
					//fput_margine_new($c,$marg,$fh,"$info");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					$bolInformazioni = $info;
				}
				else
				{
					//fput_margine_new($c,$marg,$fh," ");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					$bolInformazioni = "";
				}
				////fput_margine_new($c,$marg,$fh,"$num_ccp|");
				
				// controllo se l'intestatario del bollettino � un trasgressore.
				//In caso affermativo quardo se l'obbligato ha gi� effettuato un pagamento. Se � cos�, il totale ridotto "nuovo"
				//sar� la differenza tra il $totale_ridotto e quello che � gi� stato pagato.
				$query="select Tra_Tipo_Trasgressore from trasgressore_cds where
						Tra_Trasgressore='$myObbligato->Con_Progr' and Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
				$tipo_trasgressore_app=single_answer_query($query);			
				
				//Riccardo ha deciso di fare questa modifica per tutti i comuni per fare uscire la differenza da pagare nel bollettino 
				//quando il trasgressore gia precedentemente ha fatto un versamento
				if ($tipo_trasgressore_app!='')
				{
					$query_paga="select sum(Pag_Importo_Pag) from pagamenti_volontari_cds where
								 Pag_Registro='$myRegistroVerbale->Reg_Progr' and Pag_Anno='$myRegistroVerbale->Reg_Anno'";
					$gia_pagato=single_answer_query($query_paga);
					//notifica di ufficio: prendo allora le spese relative alla notifica dell'obbligato
					if ($myNotificaCorrente->Not_Stato_Notifica == $notificaUfficio_Codice8)
					{
						$query="select Tra_Progr from trasgressore_cds where
								Tra_Progr!='$myNotificaCorrente->Not_Trasgressore' and
								Tra_Tipo_Trasgressore!='$tipo_trasgressore_app' and
								Tra_Registro_Cronologico='$myRegistroVerbale->Reg_Progr'";
						$tra_progr_trasgressore=single_answer_query($query);
						
						$query = "select * from notifica_verbale_cds where
								Not_Trasgressore='$tra_progr_trasgressore' and
								Not_Progr_Registro='$myRegistroVerbale->Reg_Progr'";
						$ris = safe_query($query);
						$notifica_sp = mysql_fetch_array($ris);
						$myNotificaCorrente->Not_Spese_Notifica = $notifica_sp['Not_Spese_Notifica'];
						$myNotificaCorrente->Not_Spese_Ricerca = $notifica_sp['Not_Spese_Ricerca'];
					}
				}
				else
				{
					$gia_pagato=0;
				}
				/*
				if ($tipo_trasgressore_app=='trasgressore')
				{
					$query_paga="select sum(Pag_Importo_Pag) from pagamenti_volontari_cds where
								 Pag_Registro='$reggistro_progr[$y]' and Pag_Anno='$myRegistroVerbale->Reg_Anno]'";
					$gia_pagato=single_answer_query($query_paga);
					//notifica di ufficio: prendo allora le spese relative alla notifica dell'obbligato
					$not_progr_uficio=single_answer_query("select Not_Progr from notifica_stat_cds where Not_Codic='8'");
					if($notif[Not_Stato_Notifica]==$not_progr_uficio)
					{
						$query="select Tra_Progr from trasgressore_cds where
								Tra_Progr!='$notif[Not_Trasgressore]' and
								Tra_Tipo_Trasgressore!='$tipo_trasgressore_app' and
								Tra_Registro_Cronologico='$reggistro_progr[$y]'";
						$tra_progr_trasgressore=single_answer_query($query);
						$query="select * from notifica_verbale_cds where
								Not_Trasgressore='$tra_progr_trasgressore' and
								Not_Progr_Registro='$reggistro_progr[$y]'";
						$ris=safe_query($query);
						$notifica_sp=mysql_fetch_array($ris);
						$notif[Not_Spese_Notifica]=$notifica_sp[Not_Spese_Notifica];
						$notif[Not_Spese_Ricerca]=$notifica_sp[Not_Spese_Ricerca];
					}
				}
				else
				{
					$gia_pagato=0;
				}
				*/
				
	
				$importoSanzioneRidotta = $myRegistroVerbale->Reg_Totale_Ridotto + 
											$myNotificaCorrente->Not_Spese_Notifica + 
											$myNotificaCorrente->Not_Spese_Ricerca - 
											$gia_pagato;
				
				/*echo <<< IMPORTI
				<br>
				$importoSanzioneRidotta = $myRegistroVerbale->Reg_Totale_Ridotto + 
											$myNotificaCorrente->Not_Spese_Notifica + 
											$myNotificaCorrente->Not_Spese_Ricerca - 
											$gia_pagato;
				<br>
	IMPORTI;*/
				if ($importoSanzioneRidotta < 0) $importoSanzioneRidotta = 0;
				$importoSanzioneNonRidotta = $myRegistroVerbale->Reg_Totale;
				$importo30PerCento = $importoSanzioneRidotta * 0.7;
				$bolImporto30PerCento = number_format ($importo30PerCento, 2, ',', '.');
				
				$importoSanzioneDoppiaNotif = $importoSanzioneRidotta + $myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale;
				if ($importoSanzioneDoppiaNotif < 0) $importoSanzioneDoppiaNotif = $myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale;
				
				
				/*switch ($myLocalTestoVerbale->Tipologia_Verbale)
				{
					case 1:  //  30% e ridotta; spese forfettarie
						$bolTotalePrimoBollettino = number_format ($importo30PerCento, 2, ',', '.');
						$bolParzialeSanzione = number_format ($myRegistroVerbale->Reg_Totale_Ridotto, 2, ',', '.');
						$bolTotaleNonRidotto = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
						
						$bolTotaleSecondoBollettino = number_format ($importoSanzioneRidotta, 2, ',', '.');
						break;
					case 2:  //  30% e ridotta; spese massime
						$bolTotalePrimoBollettino = number_format ($importo30PerCento, 2, ',', '.');
						$bolParzialeSanzione = number_format ($myRegistroVerbale->Reg_Totale_Ridotto, 2, ',', '.');
						$bolTotaleNonRidotto = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
						
						$bolTotaleSecondoBollettino = number_format ($importoSanzioneRidotta, 2, ',', '.');
						break;
					case 3:  //  30% e vuoto; senza spese
						$bolTotalePrimoBollettino = number_format ($importo30PerCento, 2, ',', '.');
						$bolParzialeSanzione = number_format ($myRegistroVerbale->Reg_Totale_Ridotto, 2, ',', '.');
						$bolTotaleNonRidotto = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
						
						$bolTotaleSecondoBollettino = "";
						break;
					case 4:  //  vuoto e vuoto
						$bolTotalePrimoBollettino = "";
						$bolParzialeSanzione = number_format ($myRegistroVerbale->Reg_Totale_Ridotto, 2, ',', '.');
						$bolTotaleNonRidotto = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
						
						$bolTotaleSecondoBollettino = "";
						break;
					case 5:  //  30% e massimo; spese ?
						$bolTotalePrimoBollettino = number_format ($importo30PerCento, 2, ',', '.');
						$bolParzialeSanzione = number_format ($myRegistroVerbale->Reg_Totale_Ridotto, 2, ',', '.');
						$bolTotaleNonRidotto = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
						
						$bolTotaleSecondoBollettino = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
						break;
					default:
						alert ("errore tipo verbale");
						break;
				}*/
				
				/*if ($myRegistroVerbale->Reg_Progr_Registro == 612 || $myRegistroVerbale->Reg_Progr_Registro == 613)
					$fammialert = 1;
				else $fammialert = 0;*/
				
				if ($fammialert) alert ("tipospese  " . $myLocalTestoVerbale->Tipologia_Spese);
				switch ($myLocalTestoVerbale->Tipologia_Spese)
				{
					case 1:  //  spese forfettarie
						$nuoveSpeseFisse = $myNotificaCorrente->Not_Spese_Notifica + $myNotificaCorrente->Not_Spese_Ricerca;//$myNotificaCorrente->Not_Spese_Notifica + $myNotificaCorrente->Not_Spese_Ricerca;
						$nuoveSpeseCan = $nuoveSpeseFisse;//$nuoveSpeseFisse + $myParametroDiQuestoAnno->Par_Spese_CAN;
						$nuoveSpeseCad = $nuoveSpeseFisse;//$nuoveSpeseFisse + $myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale;
						
						$testoSpeseFisse = number_format($nuoveSpeseFisse, 2, ",", ".");
						$testoSpeseCan = number_format($nuoveSpeseCan, 2, ",", ".");
						$testoSpeseCad = number_format($nuoveSpeseCad, 2, ",", ".");
						
						$nuovoImportoRiduzione30 = $myRegistroVerbale->Reg_Totale_Ridotto * 0.7;
						$nuovoTotaleRiduzione30 = $nuovoImportoRiduzione30 + $nuoveSpeseFisse;
						$nuovoTotaleCanRiduzione30 = $nuovoImportoRiduzione30 + $nuoveSpeseCan;
						$nuovoTotaleCadRiduzione30 = $nuovoImportoRiduzione30 + $nuoveSpeseCad;
						
						$nuovoImportoEdittale = $myRegistroVerbale->Reg_Totale_Ridotto;
						$nuovoTotaleEdittale = $nuovoImportoEdittale + $nuoveSpeseFisse;
						$nuovoTotaleCanEdittale = $nuovoImportoEdittale + $nuoveSpeseCan;
						$nuovoTotaleCadEdittale = $nuovoImportoEdittale + $nuoveSpeseCad;
						
						$nuovoImportoOltre60 = $myRegistroVerbale->Reg_Totale;
						$nuovoTotaleOltre60 = $nuovoImportoOltre60 + $nuoveSpeseFisse;
						$nuovoTotaleCanOltre60 = $nuovoImportoOltre60 + $nuoveSpeseCan;
						$nuovoTotaleCadOltre60 = $nuovoImportoOltre60 + $nuoveSpeseCad;
						
						$testoImportoRiduzione30 = number_format($nuovoImportoRiduzione30, 2, ",", ".");
						$testoTotaleRiduzione30 = number_format($nuovoTotaleRiduzione30, 2, ",", ".");
						$testoTotaleCanRiduzione30 = number_format($nuovoTotaleCanRiduzione30, 2, ",", ".");
						$testoTotaleCadRiduzione30 = number_format($nuovoTotaleCadRiduzione30, 2, ",", ".");
						
						$testoImportoEdittale = number_format($nuovoImportoEdittale, 2, ",", ".");
						$testoTotaleEdittale = number_format($nuovoTotaleEdittale, 2, ",", ".");
						$testoTotaleCanEdittale = number_format($nuovoTotaleCanEdittale, 2, ",", ".");
						$testoTotaleCadEdittale = number_format($nuovoTotaleCadEdittale, 2, ",", ".");
						
						$testoImportoOltre60 = number_format($nuovoImportoOltre60, 2, ",", ".");
						$testoTotaleOltre60 = number_format($nuovoTotaleOltre60, 2, ",", ".");
						$testoTotaleCanOltre60 = number_format($nuovoTotaleCanOltre60, 2, ",", ".");
						$testoTotaleCadOltre60 = number_format($nuovoTotaleCadOltre60, 2, ",", ".");
						break;
						
					case 2:  //  spese CAN e CAD
						$nuoveSpeseFisse = $myNotificaCorrente->Not_Spese_Notifica + $myNotificaCorrente->Not_Spese_Ricerca;
						$nuoveSpeseCan = $nuoveSpeseFisse + $myParametroDiQuestoAnno->Par_Spese_CAN;
						$nuoveSpeseCad = $nuoveSpeseFisse + $myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale;
						
						$testoSpeseFisse = number_format($nuoveSpeseFisse, 2, ",", ".");
						$testoSpeseCan = number_format($nuoveSpeseCan, 2, ",", ".");
						$testoSpeseCad = number_format($nuoveSpeseCad, 2, ",", ".");
						
						$nuovoImportoRiduzione30 = $myRegistroVerbale->Reg_Totale_Ridotto * 0.7;
						$nuovoTotaleRiduzione30 = $nuovoImportoRiduzione30 + $nuoveSpeseFisse;
						$nuovoTotaleCanRiduzione30 = $nuovoImportoRiduzione30 + $nuoveSpeseCan;
						$nuovoTotaleCadRiduzione30 = $nuovoImportoRiduzione30 + $nuoveSpeseCad;
						
						$nuovoImportoEdittale = $myRegistroVerbale->Reg_Totale_Ridotto;
						$nuovoTotaleEdittale = $nuovoImportoEdittale + $nuoveSpeseFisse;
						$nuovoTotaleCanEdittale = $nuovoImportoEdittale + $nuoveSpeseCan;
						$nuovoTotaleCadEdittale = $nuovoImportoEdittale + $nuoveSpeseCad;
						
						$nuovoImportoOltre60 = $myRegistroVerbale->Reg_Totale;
						$nuovoTotaleOltre60 = $nuovoImportoOltre60 + $nuoveSpeseFisse;
						$nuovoTotaleCanOltre60 = $nuovoImportoOltre60 + $nuoveSpeseCan;
						$nuovoTotaleCadOltre60 = $nuovoImportoOltre60 + $nuoveSpeseCad;
						
						$testoImportoRiduzione30 = number_format($nuovoImportoRiduzione30, 2, ",", ".");
						$testoTotaleRiduzione30 = number_format($nuovoTotaleRiduzione30, 2, ",", ".");
						$testoTotaleCanRiduzione30 = number_format($nuovoTotaleCanRiduzione30, 2, ",", ".");
						$testoTotaleCadRiduzione30 = number_format($nuovoTotaleCadRiduzione30, 2, ",", ".");
						
						$testoImportoEdittale = number_format($nuovoImportoEdittale, 2, ",", ".");
						$testoTotaleEdittale = number_format($nuovoTotaleEdittale, 2, ",", ".");
						$testoTotaleCanEdittale = number_format($nuovoTotaleCanEdittale, 2, ",", ".");
						$testoTotaleCadEdittale = number_format($nuovoTotaleCadEdittale, 2, ",", ".");
						
						$testoImportoOltre60 = number_format($nuovoImportoOltre60, 2, ",", ".");
						$testoTotaleOltre60 = number_format($nuovoTotaleOltre60, 2, ",", ".");
						$testoTotaleCanOltre60 = number_format($nuovoTotaleCanOltre60, 2, ",", ".");
						$testoTotaleCadOltre60 = number_format($nuovoTotaleCadOltre60, 2, ",", ".");
						break;
						
					default: alert ("errore scelta tipologia_spese"); break;
				}
				
				if ($fammialert) alert ("tipo verbale  $myLocalTestoVerbale->Tipologia_Verbale");
				switch ($myLocalTestoVerbale->Tipologia_Verbale)
				{
					case 1:  // bollettino con 30% e bollettino con ridotta
						$bolTotalePrimoBollettino = number_format ($nuovoTotaleCadRiduzione30, 2, ',', '.');
						$bolTotaleSecondoBollettino = number_format ($nuovoTotaleCadEdittale, 2, ',', '.');
						if ($problemaRiduzione5giorni == "NONRIDUZIONE5GIORNI")
						{
							$bolTotalePrimoBollettino = number_format ($nuovoTotaleCadEdittale, 2, ',', '.');
							$bolTotaleSecondoBollettino = number_format ($nuovoTotaleCadOltre60, 2, ',', '.');
						}
						if ($problemaNonEdittale == "NONRIDUZIONEEDITTALE")
						{
							$bolTotalePrimoBollettino = number_format ($nuovoTotaleCadOltre60, 2, ',', '.');
							$bolTotaleSecondoBollettino = "";
						}
						break;
					case 2:  // bollettino con 30% e bollettino compilato ma vuoto
						$bolTotalePrimoBollettino = number_format ($nuovoTotaleCadRiduzione30, 2, ',', '.');
						$bolTotaleSecondoBollettino = "";
						if ($problemaRiduzione5giorni == "NONRIDUZIONE5GIORNI")
						{
							$bolTotalePrimoBollettino = number_format ($nuovoTotaleCadEdittale, 2, ',', '.');
						}
						if ($problemaNonEdittale == "NONRIDUZIONEEDITTALE")
						{
							$bolTotalePrimoBollettino = number_format ($nuovoTotaleCadOltre60, 2, ',', '.');
						}
						break;
					case 3:  // bollettini compilati ma vuoti
						$bolTotalePrimoBollettino = "";
						$bolTotaleSecondoBollettino = "";
						break;
						
					case 99999:  // assicurazioni e revisioni
						$bolTotalePrimoBollettino = "";
						$bolTotaleSecondoBollettino = "";
						break;
						
					default: alert ("errore scelta tipologia_verbale"); break;
				}
				
				//$bolTotaleNonRidotto = number_format ($importoSanzioneNonRidotta, 2, ',', '.');
				
				$bolNumeroCCP = $num_ccp;
				//$bolTotalePrimBollettino = $totale_ridotto_it;
				//$bolTotaleRidotto = number_format ($myRegistroVerbale->Reg_Totale_Ridotto, 2, ',', '.');
				
				//$nuovo_num = CreaNumeroInLettera($bolTotalePrimoBollettino);
				
				$bolLettereImportoPrimoBollettino = $nuovo_num;
				
				if ($c=='C826')
				{
					//Per il comune di Cogorno l'intestazione del ccp va scritta su due righe dopo viol.
					$my_array_intestazione = explode("VIOL.",$intestazione,2);
					$my_array_intestazione[1] = trim($my_array_intestazione[1]);
					//$intestazione = $my_array_intestazione[0] . "VIOL." . chr(11) . $my_array_intestazione[1];
					$intestazione = $my_array_intestazione[0] . "VIOL." . $my_array_intestazione[1];
				}
				
				//echo "<br>$intestazione";
				
				$bolIntestazioneConto = $intestazione;
				$intestazione_uno=strtoupper($intestazione);
				$bolIntestazioneContoMaiuscolo = $intestazione_uno;
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				$spese_notifica_it=number_format($myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale,2,',','.');
				/*******selezione articolo violazione ************/
				/*$query_art_bol="select * from tariffa_cds where Tar_Progr=$myRegistroVerbale->Reg_Articol_Uno]";
				$ris_art_bol=safe_query($query_art_bol);
				$art_bol=mysql_fetch_array($ris_art_bol);*/
				store_date_cds($myRegistroVerbale->Reg_Data_Avviso,$data_tmp);
				extract_date($myRegistroVerbale->Reg_Data_Avviso);
				
				$tempCausale = "Pagamento Accertamento Violazione C.d.S. Art. " . $myArticoloUno->Tar_Articolo;
				if ($myArticoloUno->Tar_Comma != "") $tempCausale .= "/" . $myArticoloUno->Tar_Comma;
				if ($myArticoloUno->Tar_Lettera != "") $tempCausale .= " " . $myArticoloUno->Tar_Lettera;
				
				$causale = substr($tempCausale, 0, 53);    // limito la lunghezza del campo
				$bolCausale = $causale;
				
				$numeroAvviso = "N.$myRegistroVerbale->Reg_Progr_Registro/$myRegistroVerbale->Reg_Anno_Avviso$par_definizione_cronologico_tmp";
				
				if(($myRegistroVerbale->Reg_Rif_Numero_Avviso==0 or $myRegistroVerbale->Reg_Rif_Numero_Avviso==NULL) and ($myRegistroVerbale->Reg_Protocollo!=0 and $myRegistroVerbale->Reg_Protocollo!=NULL))
				{
					$protocollo_reg="$numeroAvviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				elseif(($myRegistroVerbale->Reg_Rif_Numero_Avviso!=0 and $myRegistroVerbale->Reg_Rif_Numero_Avviso!=NULL) and ($myRegistroVerbale->Reg_Protocollo!=0 and $myRegistroVerbale->Reg_Protocollo!=NULL))
				{
					$protocollo_reg="$numeroAvviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				elseif(($myRegistroVerbale->Reg_Rif_Numero_Avviso!=0 and $myRegistroVerbale->Reg_Rif_Numero_Avviso!=NULL) and ($myRegistroVerbale->Reg_Protocollo==0 or $myRegistroVerbale->Reg_Protocollo==NULL))
				{
					$protocollo_reg="$numeroAvviso Rif.$myRegistroVerbale->Reg_Rif_Numero_Avviso/$myRegistroVerbale->Reg_Anno_Avviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				else
				{
					$protocollo_reg="$numeroAvviso Anno $myRegistroVerbale->Reg_Anno_Avviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				$protocollo_reg=substr("$protocollo_reg",0,60);    // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$protocollo_reg|");
				$protocollo_reg_uno=strtoupper($protocollo_reg);
				//fput_margine_new($c,$marg,$fh,"$protocollo_reg_uno");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				$bolProtocollo = $protocollo_reg_uno;
				
				//$ennesimo_comune="COMUNE DI $nome_comune_upp $myRegistroVerbale->Reg_Anno]"; prima
				
				$ennesimo_comune = $nome_comune_upp . " " . $myRegistroVerbale->Reg_Anno;
				if ($par_tipo_ente_accertatore=='Altro_Ente') {}
				else $ennesimo_comune = "COMUNE DI " . $ennesimo_comune;
				$ennesimo_comune = substr($ennesimo_comune, 0, 53);   // limito la lunghezza del campo
				$bolEnnesimoComune = $ennesimo_comune;
				
				$cognome_lim=substr("$cognome_upp",0,28);        // limito la lunghezza del campo
				$bolCognomeLimitato = $cognome_lim;
				
				$nome_lim=substr("$nome_upp",0,28);        // limito la lunghezza del campo
				$bolNomeLimitato = $nome_lim;
				
				$bolNumeroCronologico = "Cron. $c/$myRegistroVerbale->Reg_Anno/$myRegistroVerbale->Reg_Progr_Registro$par_definizione_cronologico_tmp/1";
				
				$ind_prova="$address->Com_Nome";
				$ind_prova_up=strtoupper($ind_prova);
				$indirMaiuscoloLimitato=substr("$indirMaiuscoloDestinatario",0,23);        // limito la lunghezza del campo
				$bolIndirizzoLimitato = $indirMaiuscoloLimitato;
				
				$bolCapComune = $cap;
				
				$comune_upp=strtoupper($address->Com_Nome);
				$comune_lim=substr("$comune_upp",0,23);        // limito la lunghezza del campo
				$bolComuneLimitato = $comune_lim;
				
				//$nuovo_num = CreaNumeroInLettera($bolTotaleSecondoBollettino);
				
				$bolLettereImportoSecondoBollettino = $nuovo_num;
				
				$spese_notifica_it=number_format($myParametroDiQuestoAnno->Par_Spese_Doppia_Notifica_Verbale,2,',','.');
				/*$query_art_bol="select * from tariffa_cds where Tar_Progr=$myRegistroVerbale->Reg_Articol_Uno]";
				$ris_art_bol=safe_query($query_art_bol);
				$art_bol=mysql_fetch_array($ris_art_bol);*/
				
				
				
				/*store_date_cds($myRegistroVerbale->Reg_Data_Avviso],$data_tmp);
				extract_date($myRegistroVerbale->Reg_Data_Avviso]);
				if($art_bol[Tar_Comma]!=0 and $art_bol[Tar_Comma]!='NULL')
				{
					$causale="Pagamento Accertamento Violazione C.d.S. Art.$art_bol[Tar_Articolo]/$art_bol[Tar_Comma] $art_bol[Tar_Lettera]";
				}
				else
				{
					$causale="Pagamento Accertamento Violazione C.d.S. Art.$art_bol[Tar_Articolo] $art_bol[Tar_Lettera]";
				}*/
				
				if(($myRegistroVerbale->Reg_Rif_Numero_Avviso==0 or $myRegistroVerbale->Reg_Rif_Numero_Avviso==NULL) and ($myRegistroVerbale->Reg_Protocollo!=0 and $myRegistroVerbale->Reg_Protocollo!=NULL))
				{
					$protocollo_reg="$numeroAvviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				elseif(($myRegistroVerbale->Reg_Rif_Numero_Avviso!=0 and $myRegistroVerbale->Reg_Rif_Numero_Avviso!=NULL) and ($myRegistroVerbale->Reg_Protocollo!=0 and $myRegistroVerbale->Reg_Protocollo!=NULL))
				{
					$protocollo_reg="$numeroAvviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				elseif(($myRegistroVerbale->Reg_Rif_Numero_Avviso!=0 and $myRegistroVerbale->Reg_Rif_Numero_Avviso!=NULL) and ($myRegistroVerbale->Reg_Protocollo==0 or $myRegistroVerbale->Reg_Protocollo==NULL))
				{
					$protocollo_reg="$numeroAvviso Rif.$myRegistroVerbale->Reg_Rif_Numero_Avviso/$myRegistroVerbale->Reg_Anno_Avviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				else
				{
					$protocollo_reg="$numeroAvviso Anno $myRegistroVerbale->Reg_Anno_Avviso del $myRegistroVerbale->Reg_Data_Avviso Tg.$myRegistroVerbale->Reg_Targa_Veicolo";
				}
				$protocollo_reg = substr($protocollo_reg, 0, 60);    // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$protocollo_reg|");
				$protocollo_reg_uno=strtoupper($protocollo_reg);
				////fput_margine_new($c,$marg,$fh,"$protocollo_reg_uno|");
				
				
				////fput_margine_new($c,$marg,$fh,"$ennesimo_comune|");
				////fput_margine_new($c,$marg,$fh,"$ennesimo_comune|");
				$cognome_lim=substr("$cognome_upp",0,28);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$cognome_lim|");
				//$cognome_lim=substr("$cognome_upp",0,23);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$cognome_lim|");
				$nome_lim=substr("$nome_upp",0,28);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$nome_lim|");
				//$nome_lim=substr("$nome_upp",0,23);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$nome_lim|");
				//if($myRegistroVerbale->Reg_Numero_Avviso]==$myRegistroVerbale->Reg_Progr_Registro])
				//{
				/*if ($par_NumeroBollettini == 1)
				{
					//fput_margine_new($c,$marg,$fh,"");//non ha il secondo bollettino: crono_due ????
					$bolNumero2Cronologico = "";
				}
				else
				{
					//fput_margine_new($c,$marg,$fh,"Cron. $c/$myRegistroVerbale->Reg_Anno]/$myRegistroVerbale->Reg_Progr_Registro]$par_definizione_cronologico_tmp/2");
					$bolNumero2Cronologico = "Cron. $c/$myRegistroVerbale->Reg_Anno/$myRegistroVerbale->Reg_Progr_Registro$par_definizione_cronologico_tmp/2";
				}	*/			
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
				/*}
				else
				{
					$anno_2=substr($myRegistroVerbale->Reg_Anno],2);
					//fput_margine_new($c,$marg,$fh,"Cron. $c/$anno_2/$myRegistroVerbale->Reg_Progr_Registro]/$myRegistroVerbale->Reg_Numero_Avviso]/2|");
				}*/
				////fput_margine_new($c,$marg,$fh,"Cron. $c/$myRegistroVerbale->Reg_Anno]/$myRegistroVerbale->Reg_Numero_Avviso]/2|");
				$ind_prova="$address->Com_Nome";
				$ind_prova_up=strtoupper($ind_prova);
				$indirMaiuscoloLimitato=substr("$indirMaiuscoloDestinatario",0,23);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$indirMaiuscoloLimitato|");
				//$indirMaiuscoloLimitato=substr("$indirizo_up",0,23);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$indirMaiuscoloLimitato|");
				////fput_margine_new($c,$marg,$fh,"$cap|");
				$comune_upp=strtoupper($address->Com_Nome);
				$comune_lim=substr("$comune_upp",0,23);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$comune_lim|");
				////fput_margine_new($c,$marg,$fh,"$cap|");
				//$comune_lim=substr("$comune_upp",0,17);        // limito la lunghezza del campo
				////fput_margine_new($c,$marg,$fh,"$comune_lim|");
				//PARTE RELATIVA ALLA DICHIARAZIONE DEI DATI DEL CONTRIBUENTE
				//fput_margine_new($c,$marg,$fh,"$corpo_acc");
				$bolCorpo = $myCorpo;
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//PER ORA METTO COMUNE MA PUO' ESSERE CHE IN FUTURO CI VADA COMUNITA'  MONTANA A SECONDA DEI PARAMETRI
				
				////fput_margine_new($c,$marg,$fh,"COMUNE DI $nome_comune_upp");			 prima
				
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				$cur_ente=new enti($c);
				$cap1 = ($cur_ente->Via_Cap==NULL?$cur_ente->Com_Cap:$cur_ente->Via_Cap);
				
				//fput_margine_new($c,$marg,$fh,"$cur_ente->Top_Nome $cur_ente->Via_Nome $cur_ente->Ent_Civico");
				$bolIndirizzoEnte = "$cur_ente->Top_Nome $cur_ente->Via_Nome $cur_ente->Ent_Civico";
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$cap1");
				$bolCapEnte = $cap1;
				//fput_margine_new($c,$marg,$fh,"$tabulatore");			
				////fput_margine_new($c,$marg,$fh,"$cur_ente->Com_Nome ($cur_ente->Pro_Sigla)"); //prima
				
				$bolNomeEnte = "";
				if(($par_tipo_ente_accertatore=='Altro_Ente') and ($par_nome_altro_ente!="" or $par_nome_altro_ente!=NULL))
				{					
					//fput_margine_new($c,$marg,$fh,"$par_nome_altro_ente");
					$bolNomeEnte = $par_nome_altro_ente;
				}
				else
				{
					if($cur_ente->Pro_Sigla!='')
					{
						$sigla="($cur_ente->Pro_Sigla)";
					}
					else
					{
						$sigla="";
					}
					//fput_margine_new($c,$marg,$fh,"$cur_ente->Com_Nome $sigla");
					$bolNomeEnte = "$cur_ente->Com_Nome $sigla";
				}				
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");			
				/*if($myRegistroVerbale->Reg_Numero_Avviso==$myRegistroVerbale->Reg_Progr_Registro)
				{
					$proto_reg="N.$myRegistroVerbale->Reg_Numero_Avviso/$myRegistroVerbale->Reg_Anno_Avviso".$tabulatore;
				}
				else
				{
					$proto_reg="N.$myRegistroVerbale->Reg_Progr_Registro]/$myRegistroVerbale->Reg_Numero_Avviso]/$myRegistroVerbale->Reg_Anno_Avviso]".$tabulatore;
				}*/
				////fput_margine_new($c,$marg,$fh,"$proto_reg|$myRegistroVerbale->Reg_Data_Avviso]|$myRegistroVerbale->Reg_Targa_Veicolo]|$cognome_upp $nome_upp|");			
				//fput_margine_new($c,$marg,$fh,"$cognome_upp $nome_upp");
				$bolCognomeNome = "$cognome_upp $nome_upp";
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				$bolNascitaObbligato = "";
				$bolDataNascitaObbligato = "";
				if($myObbligato->Con_Tipo!='D')
				{
					$comune_nasc=strtoupper("$myObbligato->Con_Nome_Com_Nasc");
					store_date_cds($myObbligato->Con_Data_Nasc,$data_nasc_temp);
					extract_date($myObbligato->Con_Data_Nasc);
					if($comune_nasc==NULL)
					{
						$naz_nasc=strtoupper("$myObbligato->Con_Nome_Paese_Nasc");
						//fput_margine_new($c,$marg,$fh,"$naz_nasc");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh,"$current->Con_Data_Nasc");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$bolNascitaObbligato = $naz_nasc;
						$bolDataNascitaObbligato = $myObbligato->Con_Data_Nasc;
					}
					else
					{
						//fput_margine_new($c,$marg,$fh,"$comune_nasc");
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						//fput_margine_new($c,$marg,$fh,"$current->Con_Data_Nasc");	
						//fput_margine_new($c,$marg,$fh,"$tabulatore");
						$bolNascitaObbligato = $comune_nasc;
						$bolDataNascitaObbligato = $myObbligato->Con_Data_Nasc;
					}
				}
				else
				{
					//fput_margine_new($c,$marg,$fh," ");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					//fput_margine_new($c,$marg,$fh," ");
					//fput_margine_new($c,$marg,$fh,"$tabulatore");
					$bolNascitaObbligato = "";
					$bolDataNascitaObbligato = "";
				}
				$civico_obbli="$myObbligato->Civ_Num$esp_num$esp_lett$scala$interno$piano";
				$indirResidenzaObbligato=strtoupper("$res_obbligato->Top_Nome $res_obbligato->Via_Nome $civico_obbli");
				
				//fput_margine_new($c,$marg,$fh,"$comune_res_obbli");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$indirResidenzaObbligato");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				$bolComuneObbligato = $comune_res_obbli;
				$bolIndirizzoObbligato = $indirResidenzaObbligato;
				
				if($res_obbligato->Pro_Sigla!='')
				{
					//fput_margine_new($c,$marg,$fh,"($res_obbligato->Pro_Sigla)");
					$bolProvinciaObbligato = "(" . $res_obbligato->Pro_Sigla . ")";
				}
				else
				{
					//fput_margine_new($c,$marg,$fh,"");
					$bolProvinciaObbligato = "";
				}			
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$query="select Par_Giudice_Di_Pace from parametri_cds where Par_Comune='$c' and Par_Anno='$a'";
				$giudice_di_pace=single_answer_query($query);
				$query="select Par_Prefettura from parametri_cds where Par_Comune='$c' and Par_Anno='$a'";
				$prefettura=single_answer_query($query);
				if($prefettura!=NULL)
				{
					$prefettura=" $prefettura";
				}
				else
				{
					$prefettura="";
				}
				if($giudice_di_pace!=NULL)
				{
					$giudice_di_pace=" $giudice_di_pace";
				}
				else
				{
					$giudice_di_pace="";
				}
				/*$query="select Com_Istat from comune where Com_CC='$c'";
				$cod_istat=single_answer_query($query);*/
				
				$cod_istat = $myFromGetComune->Com_Istat;
				
				$anno_due=substr($myRegistroVerbale->Reg_Anno,2);
				$num_ccp_bol="";
				$numero_controllo_uno="";
				$numero_controllo_due="";
				$num_controllo_bol1="";
				$num_controllo_bol2="";
				$nm_bol1="1";
				$nm_bol2="2";
				$num_cifra_bol1="";
				$num_cifra_bol2="";+
				$bollettino_uno_interi="";
				$bollettino_due_interi="";
				$bollettino_uno="";
				$bollettino_due="";
				//$chr_13=chr(13);
				$lunghezza_ccp=strlen($num_ccp);
				if($lunghezza_ccp<8)
				{
					for($lnc=0;$lnc<(8-$lunghezza_ccp);$lnc++)
					{
						$num_ccp_bol.=0;
					}
				}
				$num_ccp_bol=$num_ccp_bol.$num_ccp;
				$ccp_uno="$num_ccp_bol<";
				
				/*//$numero_controllo_uno="$myRegistroVerbale->Reg_Progr]$anno_due$nm_bol1$cod_istat";
				$numero_controllo_uno="$myRegistroVerbale->Reg_Progr_Registro]$anno_due$nm_bol1$cod_istat";
				$codice_controllo1=fmod($numero_controllo_uno,93);
				if(strlen($codice_controllo1)==1){$codice_controllo1="0".$codice_controllo1;}
				$numero_controllo_uno_completo=$numero_controllo_uno.$codice_controllo1;
				$lunghezza_cc1=strlen($numero_controllo_uno_completo);
				if($lunghezza_cc1<18)
				{
					for($lnc1=0;$lnc1<(18-$lunghezza_cc1);$lnc1++)
					{
						$num_controllo_bol1.=0;
					}
				}
				$numero_controllo_bollettino_uno=$num_controllo_bol1.$numero_controllo_uno_completo;
				$codice_controllo_uno="<$numero_controllo_bollettino_uno>";*/
				
				$codice_controllo_uno = CreaCodiceControlloBollVerbale ($myRegistroVerbale->Reg_Progr_Registro, $anno_due, $nm_bol1, $cod_istat);
				
				//$numero_controllo_due="$myRegistroVerbale->Reg_Progr]$anno_due$nm_bol2$cod_istat";
				/*$numero_controllo_due="$myRegistroVerbale->Reg_Progr_Registro]$anno_due$nm_bol2$cod_istat";
				$codice_controllo2=fmod($numero_controllo_due,93);
				if(strlen($codice_controllo2)==1){$codice_controllo2="0".$codice_controllo2;}
				$numero_controllo_due_completo=$numero_controllo_due.$codice_controllo2;
				$lunghezza_cc2=strlen($numero_controllo_due_completo);
				if($lunghezza_cc2<18)
				{
					for($lnc2=0;$lnc2<(18-$lunghezza_cc2);$lnc2++)
					{
						$num_controllo_bol2.=0;
					}
				}
				$numero_controllo_bollettino_due=$num_controllo_bol2.$numero_controllo_due_completo;
				$codice_controllo_due="<$numero_controllo_bollettino_due>";*/
				
				$codice_controllo_due = CreaCodiceControlloBollVerbale ($myRegistroVerbale->Reg_Progr_Registro, $anno_due, $nm_bol2, $cod_istat);
				
				/*$lunghezza_cifra_uno=strlen($interi_uno);
				if($lunghezza_cifra_uno<8)
				{
					for($lnc3=0;$lnc3<(8-$lunghezza_cifra_uno);$lnc3++)
					{
						$num_cifra_bol1.=0;
					}
				}
				$bollettino_uno_interi=$num_cifra_bol1.$interi_uno;
				$bollettino_uno = "<" . $bollettino_uno_interi . "+" . $decimali_uno . ">";*/
				
				//$bollettino_uno = CreaCodiceBollettino($bolTotalePrimoBollettino);
				
				/*$lunghezza_cifra_due=strlen($interi_due);
				if($lunghezza_cifra_due<8)
				{
					for($lnc4=0;$lnc4<(8-$lunghezza_cifra_due);$lnc4++)
					{
						$num_cifra_bol2.=0;
					}
				}
				$bollettino_due_interi=$num_cifra_bol2.$interi_due;
				$bollettino_uno = "<" . $bollettino_due_interi . "+" . $decimali_due . ">";*/
				
				//$bollettino_due = CreaCodiceBollettino($bolTotaleSecondoBollettino);
				
				$inoltro_poste="";
				
				if ($myRegistroVerbale->Reg_Data_Verbalizzazione!='0000-00-00' and $myRegistroVerbale->Reg_Data_Verbalizzazione!=NULL)
				{
					extract_date($myRegistroVerbale->Reg_Data_Verbalizzazione);
					$data_verbalizzazione="$nome_comune, li' $myRegistroVerbale->Reg_Data_Verbalizzazione";
				}
				else
				{
					$data_verbalizzazione=" ";
				}			
				$myMemoVerbalizzante = "";
				if($myRegistroVerbale->Reg_Verbalizzante!=0 and $myRegistroVerbale->Reg_Verbalizzante!=NULL)
				{
					$query="select * from accertatori_cds where Acc_Progr='$myRegistroVerbale->Reg_Verbalizzante'";
					$res_verb=safe_query($query);
					$verbalizzante=mysql_fetch_array($res_verb);
					$verbalizzatore=stripslashes($verbalizzante[Acc_Accertatore]);
					$myMemoVerbalizzante = $verbalizzatore;
					$verbalizzatore="Il verbalizzante: $verbalizzatore";
				}
				else
				{
					$verbalizzatore=" ";
				}
				//Se lo stemma � quello della repubblica non metto niente come $c
				/*$query="select Com_Stemma from comune where Com_CC='$c'";
				$com_stemma=single_answer_query($query);*/
				
				$com_stemma = $myFromGetComune->Com_Stemma;
				
				/*if($com_stemma=='logo_rep.png')
				{
					$stemma_comune="";
				}
				else
				{*/
					$stemma_comune=$c;
				//}
				$data_autorizzazione=$myParametroDiQuestoAnno->Par_Data_Autorizzazione_Ccp;
				extract_date($data_autorizzazione);
				
				$autorizzazione_postale="AUT. $myParametroDiQuestoAnno->Par_Autorizzazione_Ccp DEL $data_autorizzazione";
				$autorizzazione_postale=strtoupper($autorizzazione_postale);
				
				//fput_margine_new($c,$marg,$fh,"$stemma_comune");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$tot_punti");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$prefettura");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$giudice_di_pace");
				
				$magStemma = $stemma_comune;
				$magPunti = $tot_punti;
				$magPrefettura = $prefettura;
				$magGiudice = $giudice_di_pace;
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$flusso");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$ccp_uno");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$codice_controllo_uno");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$bollettino_uno");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$magFlusso = $flusso;
				$magContoCorrente = $ccp_uno;
				$magCodiceControlloPrimoBollettino = $codice_controllo_uno;
				$magCodiceImportoPrimoBollettino = $bollettino_uno;
				
				//fput_margine_new($c,$marg,$fh,"$codice_controllo_due");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$bollettino_due");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$inoltro_poste");
				
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$data_verbalizzazione");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				
				$magCodiceControlloSecondoBollettino = $codice_controllo_due;
				$magCodiceImportoSecondoBollettino = $bollettino_due;
				$magInoltroPosteVuoto = "";
				$magDataVerbalizzazione = $data_verbalizzazione;
				$magNomeVerbalizzante = $verbalizzatore;
				$magAutorizzazionePostale = $autorizzazione_postale;
				$magProgressivoNotifica = $notifica_pr;
				$magRecapitoTemp = $recapito_tmp;
				
				
				//fput_margine_new($c,$marg,$fh,"$verbalizzatore");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
				//fput_margine_new($c,$marg,$fh,"$autorizzazione_postale");	
				//fput_margine_new($c,$marg,$fh,"$tabulatore");						
				//fput_margine_new($c,$marg,$fh,"$notifica_pr"); //Progressivo della notifica	
				//fput_margine_new($c,$marg,$fh,"$tabulatore");				
				//fput_margine_new($c,$marg,$fh,"$recapito_tmp");
				//fput_margine_new($c,$marg,$fh,"$tabulatore");
	
				//alert ($par_NumeroBollettini);
				/*if ($par_NumeroBollettini == 1)
				{
					$num_ccp_2 = "1";
					$intestazione_2 = "2";
					$intestazione_small_2 = "3";
					$causale_2 = "4";
					$protocollo_2 = "5";
					$comune_anno_2 = "6";
					$cognome_small_2 = "7";
					$nome_small_2 = "8";
					$indirizzo_small_2 = "9";
					$cap_small_2 = "10";
					$comune_small_2 = "11";
					
					$titolomodalita = "";
					
					$primarigagrassettomodalita = "";
					$primariganormalemodalita = "";
					$primarigasolomodalita = "";
					$primariganormale2modalita = "";
					$primarigasottolineatomodalita = "";
					
					$secondarigagrassetto = "";
					$secondariganormale = "";
					
					$numero_bollettini = 1;
				}
				else
				{
					$num_ccp_2 = $num_ccp;
					$intestazione_2 = $intestazione;
					$intestazione_small_2 = $intestazione_uno;
					$causale_2 = $causale;
					$protocollo_2 = $protocollo_reg_uno;
					$comune_anno_2 = $ennesimo_comune;
					$cognome_small_2 = $cognome_lim;
					$nome_small_2 = $nome_lim;
					$indirizzo_small_2 = $indirMaiuscoloLimitato;
					$cap_small_2 = $cap;
					$comune_small_2 = $comune_lim;
					
					$titolomodalita = "MODALITA' DI ESTINZIONE";
					
					$primarigagrassettomodalita = "Il bollettino di c.c.p. con l'importo pi� basso";
					$primariganormalemodalita = " deve essere utilizzato per il pagamento della sanzione ";
					$primarigasolomodalita = "solo";
					$primariganormale2modalita = " quando la notifica dell'atto � avvenuta ";
					$primarigasottolineatomodalita = "direttamente nelle mani del destinatario del piego.";
					
					$secondarigagrassetto = "IL BOLLETTINO DI C.C.P CON L'IMPORTO PIU' ALTO";
					$secondariganormale = " DEVE ESSERE UTILIZZATO PER IL PAGAMENTO DELLA SANZIONE IN TUTTI GLI ALTRI CASI, come ad esempio quando il piego e' stato ritirato da un soggetto diverso dal destinatario, quale potrebbe essere un familiare, moglie, marito, figlio, genero, nuora, un convivente, ecc., oppure quando il piego e' stato ritirato dal portiere dello stabile o dall'addetto alla ricezione della corrispondenza, ecc., o quando il piego e' stato ritirato preso l'ufficio postale.";
					
					$numero_bollettini = 2;
				}*/
				
				$testoTitoloModalita = $titolomodalita;
				$testoPrimaRigaGrassetto = $primarigagrassettomodalita;
				$testoPrimaRigaNormale = $primariganormalemodalita;
				$testoPrimaRigaSOLO = $primarigasolomodalita;
				$testoPrimaRiga2Normale = $primariganormale2modalita;
				$testoPrimaRigaSottolineato = $primarigasottolineatomodalita;
				$testoSecondaRigaGrassetto = $secondarigagrassetto;
				$testoSecondaRigaNormale = $secondariganormale;
				$valoreNumeroBollettini = $numero_bollettini;
				
				if ($tipo_trasgressore=="obbligato")
				{
					$luogoNascitaDest = strtoupper($myObbligato->Con_Nome_Com_Nasc);
					$dataNascitaDest = $myObbligato->Con_Data_Nasc;
					$comuneResidenzaTrasg = $bolComuneObbligato;
					$indirizzoResidenzaTrasg = $bolIndirizzoObbligato;
					$provinciaResidenzaTrasg = $bolProvinciaObbligato;
				}
				elseif($tipo_trasgressore=="trasgressore")
				{
					$luogoNascitaDest = strtoupper($myTrasgressore->Con_Nome_Com_Nasc);
					$dataNascitaDest = $myTrasgressore->Con_Data_Nasc;
					$comuneResidenzaTrasg = $bolComuneObbligato;
					$indirizzoResidenzaTrasg = $bolIndirizzoObbligato;
					$provinciaResidenzaTrasg = $bolProvinciaObbligato;
				}
				else if ($tipo_trasgressore=="coincidente")
				{
					$luogoNascitaDest = strtoupper($myObbligato->Con_Nome_Com_Nasc);
					$dataNascitaDest = $myObbligato->Con_Data_Nasc;
					$comuneResidenzaTrasg = $bolComuneObbligato;
					$indirizzoResidenzaTrasg = $bolIndirizzoObbligato;
					$provinciaResidenzaTrasg = $bolProvinciaObbligato;
				}
				else 
				{
					$luogoNascitaDest = strtoupper($myTrasgressore->Con_Nome_Com_Nasc);
					$dataNascitaDest = $myTrasgressore->Con_Data_Nasc;
					$comuneResidenzaTrasg = $bolComuneObbligato;
					$indirizzoResidenzaTrasg = $bolIndirizzoObbligato;
					$provinciaResidenzaTrasg = $bolProvinciaObbligato;
				}
				
					
				
				
				/*switch ($myLocalTestoVerbale->Tipologia_Verbale)
				{
					case 1:  //  30% e ridotta; spese forfettarie
						$aggiungiTesto3 = "Entro e non oltre 5 giorni dalla notifica e' ammesso il pagamento del presente verbale ";
						$aggiungiTesto3 .= "con riduzione del 30% dell'importo della sanzione pecuniaria (� " . $bolTotalePrimoBollettino . "). ";
						$aggiungiTesto3 .= "Entro e non oltre 60 giorni dalla notifica e' ammesso il pagamento del presente verbale ";
						$aggiungiTesto3 .= "in misura pari al minimo edittale (� " . $bolTotaleSecondoBollettino . "). ";
						$aggiungiTesto3 .= "Oltre 60 giorni ed entro 6 mesi dalla notifica e' ammesso il pagamento del presente verbale ";
						$aggiungiTesto3 .= "in misura pari alla meta' del massimo (� " .$bolTotaleNonRidotto . "). ";
						$aggiungiTesto3 .= "Per l'estinzione del verbale, gli importi sopraindicati devono essere integrati ";
						$aggiungiTesto3 .= "delle spese di ricerca e di notifica. L'importo complessivamente dovuto e' indicato ";
						$aggiungiTesto3 .= "sul retro del presente alla voce 'MODALITA' DI PAGAMENTO IN MISURA RIDOTTA'. ";
						$aggiungiTesto3 .= "Il pagamento del verbale dovra' essere effettuato per gli importi e per i termini indicati ";
						$aggiungiTesto3 .= "tramite versamento sul c.c.p. n. " . $bolNumeroCCP . " intestato a " . $intestazione;
						$aggiungiTesto3 .= $frase_uno;
						break;
					case 2:  //  30% e ridotta; spese massime
						$aggiungiTesto3 = "Entro e non oltre 5 giorni dalla notifica e' ammesso il pagamento del presente verbale ";
						$aggiungiTesto3 .= "con riduzione del 30% dell'importo della sanzione pecuniaria (� " . $bolTotalePrimoBollettino . "). ";
						$aggiungiTesto3 .= "Entro e non oltre 60 giorni dalla notifica e' ammesso il pagamento del presente verbale ";
						$aggiungiTesto3 .= "in misura pari al minimo edittale (� " . $bolTotaleSecondoBollettino . "). ";
						$aggiungiTesto3 .= "Oltre 60 giorni ed entro 6 mesi dalla notifica e' ammesso il pagamento del presente verbale ";
						$aggiungiTesto3 .= "in misura pari alla meta' del massimo (� " .$bolTotaleNonRidotto . "). ";
						$aggiungiTesto3 .= "Per l'estinzione del verbale, gli importi sopraindicati devono essere integrati ";
						$aggiungiTesto3 .= "delle spese di ricerca e di notifica. L'importo complessivamente dovuto e' indicato ";
						$aggiungiTesto3 .= "sul retro del presente alla voce 'MODALITA' DI PAGAMENTO IN MISURA RIDOTTA'. ";
						$aggiungiTesto3 .= "Il pagamento del verbale dovra' essere effettuato per gli importi e per i termini indicati ";
						$aggiungiTesto3 .= "tramite versamento sul c.c.p. n. " . $bolNumeroCCP . " intestato a " . $intestazione;
						$aggiungiTesto3 .= $frase_uno;
						break;
					case 3:  //  30% e vuoto; senza spese
						break;
					case 4:  //  vuoto e vuoto
						break;
					case 5:  //  30% e massimo; spese ?
						break;
					default:
						alert ("errore tipo verbale");
						break;
				}
					
					
					
				if ($myArticoloUno->Tar_Riduzione_5_Giorni == 'N' && $par_NumeroBollettini == 1)
				{
					$ammessoPag = "E' ammesso il pagamento di � $importoBollettino_1_it ";
					$ammessoPag .= "(importo sanzione pecuniaria � $tot_rid_it, residuo spese di notifica e ricerca).";
					$ammessoPag .= "Il pagamento del verbale dovr� essere effettuato entro 60 gg. dalla notifica, ";
					$ammessoPag .= "tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno";
						
					$testoModRidotta_1 = "Il pagamento della sanzione indicata nel presente verbale in misura ridotta, integrato delle spese di notifica, ";
					$testoModRidotta_1 .= "spedizione avviso di deposito atti giudiziari, e successive occorrenze, pu� essere eseguito ";
					$testoModRidotta_1 .= "entro e non oltre 60 giorni dalla notifica (o contestazione) della violazione.";
						
					$testo30PerCento = "";
				}
				else if ($par_NumeroBollettini == 1)
				{
					$ammessoPag = "E' ammesso il pagamento di � $importoBollettino_2_it entro 5 giorni dalla notifica con riduzione del 30% ";
					$ammessoPag .= "(importo sanzione pecuniaria � $differenza70_it, residuo spese di notifica e ricerca). ";
					$ammessoPag .= "E' ammesso il pagamento di � $importoBollettino_1_it oltre 5 giorni e non oltre i 60 giorni dalla notifica ";
					$ammessoPag .= "(importo sanzione pecuniaria � $tot_rid_it, residuo spese di notifica e ricerca). ";
					$ammessoPag .= "Il pagamento del verbale dovr� essere effettuato tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno";
					$testoModRidotta_1 = "Il pagamento della sanzione indicata nel presente verbale in misura ridotta, ";
					$testoModRidotta_1 .= "pu� essere eseguito con le seguenti modalit�: ";
						
					$testoModRidotta_2 = "a) entro 5 giorni dalla notifica (o contestazione) della violazione con beneficio della riduzione dell'importo della sanzione ";
					$testoModRidotta_2 .= "nella misura del minimo edittale di un ulteriore 30% (integrata delle spese di notifica, spedizione avviso di deposito atti giudiziari, e successive occorrenze, non soggette a riduzione). ";
					$testoModRidotta_3 = "b) OLTRE 5 giorni ed ENTRO 60 giorni dalla notifica (o contestazione) della violazione nella misura del minimo edittale ";
					$testoModRidotta_3 .= "(integrata delle spese di notifica, spedizione avviso di deposito atti giudiziari, e successive occorrenze, non soggette a riduzione). ";
						
					$testo30PerCento = "Il pagamento del verbale in misura ridotta del 30% rispetto all'importo indicato nel presente verbale, integrato nelle spese di notifica, ";
					$testo30PerCento .= "spedizione avviso di deposito atti giudiziari, e successive occorrenze, pu� essere eseguito entro e non oltre il termine di 5 giorni dalla ";
					$testo30PerCento .= "notifica (o contestazione) della violazione.";
				}
				else
				{
					$ammessoPag = "E' ammesso il pagamento di � $importoBollettino_1_it ";
					$ammessoPag = "solo quando la notifica dell'atto e' avvenuta direttamente nelle mani del destinatario. ";
					$ammessoPag .= "In tutti gli altri casi si rende dovuto l'importo di � $importoBollettino_2_it "; //old scritta
					$ammessoPag .= "Il pagamento del verbale dovr� essere effettuato entro 60 gg. dalla notifica, ";
					$ammessoPag .= "tramite il C.C.P. N. $num_ccp intestato a $intestazione. $frase_uno";
						
					$testoModRidotta_1 = "Il pagamento della sanzione indicata nel presente verbale in misura ridotta, integrato delle spese di notifica, ";
					$testoModRidotta_1 .= "spedizione avviso di deposito atti giudiziari, e successive occorrenze, pu� essere eseguito ";
					$testoModRidotta_1 .= "entro e non oltre 60 giorni dalla notifica (o contestazione) della violazione. ";
						
					$testoModRidotta_2 = "";
					$testoModRidotta_3 = "";
						
					$testo30PerCento = "";
				}
				$pag_misura_rid = $aggiungiTesto3;
					
					
					
					
				$myPagamentoMisuraRidotta = $pag_misura_rid;*/
				
				
				
				/*
				$arrayListone = array (
						$myUfficioPostale,  //  var $Ufficio_Postale_Spedizione = NULL;  //  0
						$myNomeComune,  //  var $Comune = NULL;
						$myCorpo,  //  var $Corpo_Accertatore = NULL;
						$myIndirizzoComune,  //  var $Rec_Comune_1 = NULL;
						$myCittaComune,  //  var $Rec_Comune_2 = NULL;
						$myImbustatoreIndirizzo_1,  //  var $Rec_Atti_Amministrativi_1 = NULL;
						$myImbustatoreIndirizzo_2,  //  var $Rec_Atti_Amministrativi_2 = NULL;
						$myNomeDestinatario,  //  var $Nome = NULL;
						$myIndirizzoDestinatario,  //  var $Indirizzo = NULL;
						$myCapDestinatario,  //  var $CAP = NULL;
						$myCittaDelDestinatario,  //  var $Citt� = NULL;  //  10
						$myStatoDestinatario,  //  var $Stato_Estero = NULL;
						$myDefCronologico,  //  var $Num_Viol = NULL;
						$myDefVerbale,  //  var $Num_Verb = NULL;
						$myDataViolazione,  //  var $Data_Viol = NULL;
						$myOraViolazione,  //  var $Ora_Viol = NULL;
						$myLuogoViolazione,  //  var $Luogo_Viol = NULL;
						$myPropCond,  //  var $Conducente_Trasgr = NULL;
						$myTipoVeicolo,  //  var $Tipo_Veicolo = NULL;
						$myTargaVeicolo,  //  var $Targa = NULL;
						$myListaArticoli,  //  var $Num_Articolo = NULL;  //  20
						$myDescrizioneArticolo,  //  var $Descrizione_Articolo = NULL;
						$myMotivoMancataContestazione,  //  var $Mancata_Contestazione = NULL;
						//?????  $myTotPuntiDecurtati,  //  var $Sanzione_Accessoria = NULL;
						$myDescrizioneSanzioniAccessorie,  //  var $Sanzione_Accessoria = NULL;
						$myPagamentoMisuraRidotta,  //  var $Pagamento = NULL;
						$myIdentificazione,  //  var $Data_Identificazione_Trasgressore = NULL;
						$myNomeProprietario,  //  var $Nome_Proprietario = NULL;
						$myNascitaProprietario,  //  var $Data_Luogo_Nascita_Proprietario = NULL;
						$myResidenzaProprietario,  //  var $Residenza_Proprietario_Comune = NULL;
						$myIndirizzoProprietario,  //  var $Residenza_Proprietario_Via = NULL;
						$myNomeTrasgressore,  //  var $Nome_Trasgressore = NULL;  //  30
						$myNascitaTrasgressore,  //  var $Data_Luogo_Nascita_Trasgressore = NULL;
						$myResidenzaTrasgressore,  //  var $Residenza_Trasgressore_Comune = NULL;
						$myIndirizzoTrasgressore,  //  var $Residenza_Trasgressore_Via = NULL;
						$myEsercentePotesta_1,  //  var $Esercente = NULL;
						$myEsercentePotesta_2,  //  var $Nome_Esercente = NULL;
						$myEsercentePotesta_3,  //  var $Data_Luogo_Nascita_Esercente = NULL;
						$myEsercentePotesta_4,  //  var $Residenza_Esercente_Comune = NULL;
						$myEsercentePotesta_5,  //  var $Residenza_Esercente_Via = NULL;
						$mySpeseNotifica,  //  var $Spese_Notifica = NULL;
						$myAccertatore1,  //  var $Accertatore1 = NULL;  //  40
						$myAccertatore2,  //  var $Accertatore2 = NULL;
						$myAccertatore3,  //  var $Accertatore3 = NULL;
						$myResponsabile,  //  var $Responsabile_Dati = NULL;
						$myRespProcedimento,  //  var $Responsabile_Procedimento = NULL;
						$bolInformazioni,  //  var $Informazioni = NULL;
						$bolNumeroCCP,  //  var $Num_Ccp = NULL;
						$bolTotalePrimoBollettino,  //  var $Importo_Uno = NULL;
						$bolLettereImportoPrimoBollettino,  //  var $Importo_Uno_Lettere = NULL;
						$bolIntestazioneConto,  //  var $Intestazione = NULL;
						$bolIntestazioneContoMaiuscolo,  //  var $Intestazione_Small = NULL;  //  50
						$bolCausale,  //  var $Causale = NULL;
						$bolProtocollo,  //  var $Protocollo = NULL;
						$bolEnnesimoComune,  //  var $Comune_Anno = NULL;
						$bolCognomeLimitato,  //  var $Cognome_Small = NULL;
						$bolNomeLimitato,  //  var $Nome_Small = NULL;
						$bolNumeroCronologico,  //  var $Crono_Uno = NULL;
						$bolIndirizzoLimitato,  //  var $Indirizzo_Small = NULL;
						$bolCapComune,  //  var $Cap_Small = NULL;
						$bolComuneLimitato,  //  var $Comune_Small = NULL;
						$bolTotaleSecondoBollettino,  //  var $Importo_Due = NULL;  //  60
						$bolLettereImportoSecondoBollettino,  //  var $Importo_Due_Lettere = NULL;
						$bolNumero2Cronologico,  //  var $Crono_Due = NULL;
						$bolCorpo,  //  var $Corpo_Acc = NULL;
						$bolComuneEnteAcc,  //  var $Comune_Uno = NULL;
						$bolIndirizzoEnte,  //  var $indirComune = NULL;
						$bolCapEnte,  //  var $Cap_Com = NULL;
						$bolNomeEnte,  //  var $Citta_Com = NULL;
						$bolCognomeNome,  //  var $Nome_Uno = NULL;
						$bolNascitaObbligato,  //  var $Luogo_Nasc = NULL;
						$bolDataNascitaObbligato,  //  var $Data_Nasc = NULL;  //  70
						$bolComuneObbligato,  //  var $Comune_Res = NULL;
						$bolIndirizzoObbligato,  //  var $Ind_Res = NULL;
						$bolProvinciaObbligato,  //  var $Prov_Res = NULL;
						
						$magStemma,  //  var $Cod_Comune = NULL;
						$magPunti,  //  var $Punti = NULL;
						$magPrefettura,  //  var $Prefettura = NULL;
						$magGiudice,  //  var $Giudice = NULL;
						$magFlusso,  //  var $Flusso = NULL;
						$magContoCorrente,  //  var $Ccp_Uno = NULL;
						$magCodiceControlloPrimoBollettino,  //  var $Codice_Cliente_Uno = NULL;  //  80
						$magCodiceImportoPrimoBollettino,  //  var $Bollettino_Uno = NULL;
						$magCodiceControlloSecondoBollettino,  //  var $Codice_Cliente_Due = NULL;
						$magCodiceImportoSecondoBollettino,  //  var $Bollettino_Due = NULL;
						$magInoltroPosteVuoto,  //  var $Inoltro_Poste = NULL;
						$magDataVerbalizzazione,  //  var $Data_Verbalizzazione = NULL;
						$magNomeVerbalizzante,  //  var $Verbalizzatore = NULL;
						$magAutorizzazionePostale,  //  var $Autorizzazione = NULL;
						$magProgressivoNotifica,  //  var $Progressivo_Notifica = NULL;
						$magRecapitoTemp,  //  var $Recapito = NULL;
						
						$testoTitoloModalita,  //  var $Titolo_Modalita = NULL;  //  90
						$testoPrimaRigaGrassetto,  //  var $Prima_Riga_Grassetto = NULL;
						$testoPrimaRigaNormale,  //  var $Prima_Riga_Normale = NULL;
						$testoPrimaRigaSOLO,  //  var $Prima_Riga_Solo = NULL;
						$testoPrimaRiga2Normale,  //  var $Prima_Riga_Normale_2 = NULL;
						$testoPrimaRigaSottolineato,  //  var $Prima_Riga_Sottolineato = NULL;
						$testoSecondaRigaGrassetto,  //  var $Seconda_Riga_Grassetto = NULL;
						$testoSecondaRigaNormale,  //  var $Seconda_Riga_Normale = NULL;
						
						$valoreNumeroBollettini  //  var $Numero_Bollettini = NULL;  // 98
				);
				*/
				
				$NuovoListone->Tipologia_Stampa = "ATTOGIUDIZIARIO";
				$NuovoListone->Tipologia_Atto = "VERBALI";
				
				$NuovoListone->CodiceComune = $c;
				
				$NuovoListone->NomeDestinatario = $myCognameContribuente . " " . $myNameContribuente . "(" . $myCodiceContribuente . ")";
				$NuovoListone->RecapitoDestinatario = $magRecapitoTemp;
				$NuovoListone->IndirizzoDestinatario = $myIndirizzoDestinatario;
				
				//$NuovoListone->CittaDestinatario = $myCapDestinatario . " " . $myCittaDelDestinatario;  //  10
				$NuovoListone->CapDestinatario = $myCapDestinatario;
				$NuovoListone->CityDestinatario = $nomeCityDelDestinatario;
				$NuovoListone->ProvinciaDestinatario = $provinciaSigla;
				
				$NuovoListone->StatoDestinatario = $myStatoDestinatario;
					
				$NuovoListone->NumeroViolazione = $myDefCronologico;
				$NuovoListone->NumeroVerbale = $myDefVerbale;
				$NuovoListone->DataViolazione = $myDataViolazione;
				$NuovoListone->OraViolazione = $myOraViolazione;
				$NuovoListone->LuogoViolazione = $myLuogoViolazione;
				$NuovoListone->ConducenteOppureTrasgressore = $myPropCond;
				$NuovoListone->TipoVeicolo = $myTipoVeicolo;
				$NuovoListone->TargaVeicolo = $myTargaVeicolo;
				$NuovoListone->NumeriArticoliInfranti = $myListaArticoli;  //  20
				$NuovoListone->PuntiDaDecurtare = $myTotPuntiDecurtati;
				$NuovoListone->Riduzione5GiorniAmmessa = $problemaRiduzione5giorni;
				$NuovoListone->RiduzioneEdittaleAmmessa = $problemaNonEdittale;
				$NuovoListone->DescrizioneArticoliInfranti = $myDescrizioneArticolo;
				$NuovoListone->MotivoMancataContestazione = $myMotivoMancataContestazione;
				$NuovoListone->SanzioniAccessorie = $myDescrizioneSanzioniAccessorie;
				
				$NuovoListone->IdentificazioneTrasgressore = $myIdentificazione;
				
				//alert ($myNomeProprietario . " " . $myNascitaProprietario . " e " . $myResidenzaProprietario . " " . $myIndirizzoProprietario); 
				$NuovoListone->PrimaRigaProprietario = $myNomeProprietario . " " . $myNascitaProprietario;
				$NuovoListone->SecondaRigaProprietario = $myResidenzaProprietario . " " . $myIndirizzoProprietario;
				$NuovoListone->PrimaRigaTrasgressore = $myNomeTrasgressore . " " . $myNascitaTrasgressore;
				$NuovoListone->SecondaRigaTrasgressore = $myResidenzaTrasgressore . " " . $myIndirizzoTrasgressore;
				$NuovoListone->Esercente = $myEsercentePotesta_1;
				$NuovoListone->PrimaRigaEsercente = $myEsercentePotesta_2 . " " . $myEsercentePotesta_3;
				$NuovoListone->SecondaRigaEsercente = $myEsercentePotesta_4 . " " . $myEsercentePotesta_5;
				
				//$NuovoListone->Spese_Notifica = NULL;
				$NuovoListone->RigaAccertatori = $myListaAccertatori;  //  40
				$NuovoListone->ResponsabileDati = $myResponsabile;
				$NuovoListone->ResponsabileProcedimento = $myRespProcedimento;
				$NuovoListone->LuogoDataVerbalizzazione = $magDataVerbalizzazione;
				$NuovoListone->Verbalizzante = $myMemoVerbalizzante;
				
				$NuovoListone->Prefettura = $magPrefettura;
				$NuovoListone->Giudice = $magGiudice;
				
				$NuovoListone->ImportoRiduzione30 = $testoImportoRiduzione30;
				$NuovoListone->speseRiduzione30 = $testoSpeseFisse;
				$NuovoListone->speseCanRiduzione30 = $testoSpeseCan;
				$NuovoListone->speseCadRiduzione30 = $testoSpeseCad;
				$NuovoListone->TotaleRiduzione30 = $testoTotaleRiduzione30;
				$NuovoListone->TotaleCanRiduzione30 = $testoTotaleCanRiduzione30;
				$NuovoListone->TotaleCadRiduzione30 = $testoTotaleCadRiduzione30;
				
				$NuovoListone->ImportoEdittale = $testoImportoEdittale;
				$NuovoListone->speseEdittale = $testoSpeseFisse;
				$NuovoListone->speseCanEdittale = $testoSpeseCan;
				$NuovoListone->speseCadEdittale = $testoSpeseCad;
				$NuovoListone->TotaleEdittale = $testoTotaleEdittale;
				$NuovoListone->TotaleCanEdittale = $testoTotaleCanEdittale;
				$NuovoListone->TotaleCadEdittale = $testoTotaleCadEdittale;
				
				$NuovoListone->ImportoOltre60 = $testoImportoOltre60;
				$NuovoListone->speseOltre60 = $testoSpeseFisse;
				$NuovoListone->speseCanOltre60 = $testoSpeseCan;
				$NuovoListone->speseCadOltre60 = $testoSpeseCad;
				$NuovoListone->TotaleOltre60 = $testoTotaleOltre60;
				$NuovoListone->TotaleCanOltre60 = $testoTotaleCanOltre60;
				$NuovoListone->TotaleCadOltre60 = $testoTotaleCadOltre60;
				
				//$myClassVerbale->PreparaSingolaRiga($arrayListone, $myId, $myFotogramma->Fot_Id, $formato_carta);
				
				/*$myBollettino = new Bollettino();
				
				$myBollettino->ContoCorrente = $bolNumeroCCP;
				$myBollettino->IntestatarioConto = $bolIntestazioneConto;
				$myBollettino->IntestatarioLimitatoConto = $bolIntestazioneContoMaiuscolo;
				$myBollettino->Causale = $bolCausale;
				$myBollettino->Protocollo = $bolProtocollo;
				$myBollettino->ComuneAnno = $bolEnnesimoComune;
				$myBollettino->NumeroCronologico = $bolNumeroCronologico;
				$myBollettino->CognomeLimitato = $myTrasgressore->Con_Cognome; //$bolCognomeLimitato;
				$myBollettino->NomeLimitato = $myTrasgressore->Con_Nome; //$bolNomeLimitato;
				$myBollettino->IndirizzoLimitato = $myTrasgressore->IndirizzoResidenzaCompleto; //$bolIndirizzoLimitato;
				$myBollettino->CapLimitato = $myTrasgressore->SoloCapResidenza; //$bolCapComune;
				$myBollettino->ComuneLimitato = $myTrasgressore->SoloCittaResidenza; //$bolComuneLimitato;
				
				$myBollettino->ImportoNumeroBoll1 = $bolTotalePrimoBollettino;
				$myBollettino->ImportoLettereBoll1 = $bolLettereImportoPrimoBollettino;
				$myBollettino->QuintoCampo_CodiceBoll1 = $magCodiceControlloPrimoBollettino;
				$myBollettino->QuintoCampo_ImportoBoll1 = $magCodiceImportoPrimoBollettino;
					
				$myBollettino->ImportoNumeroBoll2 = $bolTotaleSecondoBollettino;
				$myBollettino->ImportoLettereBoll2 = $bolLettereImportoSecondoBollettino;
				$myBollettino->QuintoCampo_CodiceBoll2 = $magCodiceControlloSecondoBollettino;
				$myBollettino->QuintoCampo_ImportoBoll2 = $magCodiceImportoSecondoBollettino;
				
				//alert ($bolIntestazioneConto . " - " . $bolIntestazioneContoMaiuscolo . " - " . $magCodiceControlloSecondoBollettino);
				
				$myBollettino->AggiustaDati();
				
				$NuovoListone->PreparaSingolaRiga($myId, $myFotogramma->Fot_Id, $myBollettino, $formato_carta);
				*/
				$myNewBollettino = new BollettinoPostale(
					$bolNumeroCCP,
					$bolIntestazioneConto,
					$bolCausale,
					$bolProtocollo,
					$bolEnnesimoComune,
					$bolNumeroCronologico,
					$myCognameContribuente, // $myTrasgressore->Con_Cognome,
					$myNameContribuente, // $myTrasgressore->Con_Nome,
					$myIndirizzoDestinatario, // $myTrasgressore->IndirizzoResidenzaCompleto,
					$myCapDestinatario, // $myTrasgressore->SoloCapResidenza,
					$nomeCityDelDestinatario, // $myTrasgressore->SoloCittaResidenza,
					$myCodiceFiscale,
					$bolTotalePrimoBollettino,  // stringa!! non numero
					$bolTotaleSecondoBollettino,  // stringa!! non numero
					$magCodiceControlloPrimoBollettino,
					$magCodiceControlloSecondoBollettino
				);
				
				$myNewAutorizzazione = new AutorizzazionePostale(
					$magAutorizzazionePostale,
					$myUfficioPostale,
					$myImbustatoreIndirizzo_1,
					$myImbustatoreIndirizzo_2
				);

				$myFoglioDecurtazionePunti = new FoglioDecurtazionePunti (
					//"da intestazione 2 del verbale",
					//"da intestazione 1 del verbale",
					$myIndirizzoComune,
					$myCittaComune,
					
					//$myDefCronologico,
					//$myDefVerbale,
					//$myDataViolazione,
					$myTargaVeicolo,
					$myCognameContribuente . " " . $myNameContribuente,
					$luogoNascitaDest,
					$dataNascitaDest,

					$nomeCityDelDestinatario,
					$myIndirizzoDestinatario,
					$provinciaSigla
				);
				
				$NuovoListone->PreparaSingolaRiga(
					$myId, 
					$myFotogramma->Fot_Id,
					$stampa_con_foto,
					$myNewBollettino,
					$myNewAutorizzazione, 
					$myFoglioDecurtazionePunti, 
					$formato_carta, 
					$formato_stampa);
			}  //  fine if problema5giorni
	  }  //  for 
    }  //  if

/*$questosi ++;
	if ($questosi < 30)
			alert ($mioProgr[$x]);*/
}  //  for

$oldPrimo = AvanzaJavascript (100, 0, "divdiv", $oldPrimo);
$oldSecondo = AvanzaJavascript (100, 0, "divbis", $oldSecondo);

// contatore per l'anteprima degli avvisi
//$oggi_data=date("d/m/Y");

//Intestazione distinte
/*$query="select Com_Nome from comune where Com_CC='$c'";
$res_comune=single_answer_query($query);*/

$res_comune = $myFromGetComune->Com_Nome;

$comune = strtoupper($res_comune);
$cur_ente = new enti($c);



/*$myNuovoIndirizzoComune = $cur_ente->Top_Nome . " " . $cur_ente->Via_Nome;
if ($cur_ente->Ent_Civico != "")
{
	$myNuovoIndirizzoComune .= " " . $cur_ente->Ent_Civico;
	if ($cur_ente->Ent_Civico != "") $myNuovoIndirizzoComune .= "/" . $cur_ente->Ent_Interno;
}*/




if(($cur_ente->Ent_Civico!=NULL and $cur_ente->Ent_Civico!=0) and ($cur_ente->Ent_Interno!=NULL and $cur_ente!=0))
	$civ_int="$cur_ente->Ent_Civico/$cur_ente->Ent_Interno";
elseif(($cur_ente->Ent_Civico!=NULL and $cur_ente->Ent_Civico!=0) and ($cur_ente->Ent_Interno==NULL or $cur_ente==0))
	$civ_int="$cur_ente->Ent_Civico";

if($cur_ente->Via_Cap==NULL or $cur_ente->Via_Cap==0)
	$cap1="$cur_ente->Com_Cap";
else
	$cap1="$cur_ente->Via_Cap";
$cur_sede_ges=new ges_sede($res_gesto[2],'CDS');
$cap_ges = ($cur_sede_ges->Via_Cap==NULL?$cur_sede_ges->Com_Cap:$cur_sede_ges->Via_Cap);
if($cur_sede_ges->Seg_Interno!=0 and $cur_sede_ges->Seg_Interno!=NULL)
{
    $ind_com_bis="$cur_sede_ges->Top_Nome $cur_sede_ges->Via_Nome $cur_sede_ges->Seg_Civico/$cur_sede_ges->Seg_Interno $cap_ges $cur_sede_ges->Com_Nome ($cur_sede_ges->Pro_Sigla) p.iva $res_gesto[1])";
}
else
{
    $ind_com_bis="$cur_sede_ges->Top_Nome $cur_sede_ges->Via_Nome $cur_sede_ges->Seg_Civico $cap_ges $cur_sede_ges->Com_Nome ($cur_sede_ges->Pro_Sigla) p.iva $res_gesto[1])";
}

//prima
//$indirizz_comune="$cur_ente->Top_Nome $cur_ente->Via_Nome $civ_int $cap1 $res_comune";
//$indirizo_comne_bis="(Spese anticipate in nome e per conto del Comune di $comune dal gestore $res_gesto[0]";


//quando il nome altro ente viene valorizzato
if(($par_tipo_ente_accertatore=='Altro_Ente') and ($par_nome_altro_ente!="" or $par_nome_altro_ente!=NULL))
{		
	$indirComune="$cur_ente->Top_Nome $cur_ente->Via_Nome $civ_int $cap1 $par_nome_altro_ente";	
	$speseAnticipate="(Spese anticipate in nome e per conto di $comune dal gestore $res_gesto[0]";
}
else
{
	$indirComune="$cur_ente->Top_Nome $cur_ente->Via_Nome $civ_int $cap1 $res_comune";	
	$speseAnticipate="(Spese anticipate in nome e per conto del Comune di $comune dal gestore $res_gesto[0]";
}	
$ind_comune=strtoupper($indirComune);
$ind_comune_bis=strtoupper($speseAnticipate);
$ind_com_bis_bis=strtoupper($ind_com_bis);


$query="select Par_Spese_Notifica from parametri_competenze_cds where Par_Comune='$c' and Par_Anno='$a'";
$par_spese_not=single_answer_query($query);
if($par_spese_not==NULL){$par_spese_not='sarida';}

if($cont_avvisi==0)
{
	if ($nonfarlosemarco == 0)
	{
		/*$data_stapa='0000-00-00';
		$query="delete from gitco.num_stamp_a4 where Num_Data_Flusso='$data_stapa'and Num_Numero_Stampe='$cont_avvisi'
				and Num_Comune='$c' and Num_Tributo='V_CDS' and Num_Flusso='$flusso' and Num_Anno='$a'";
		safe_query($query);*/
	}
	else echo "<br>vorrei fare un delete FINE<br>";
	
	$NuovoListone->ChiudiFileFlusso ($nome_file_app, $fineNumeroTotale, $fineMittente);
	
	echo $uscitaSenzaRisultati;
	die;
}

$fineNumeroTotale = "Numero Totale di Avvisi: $cont_avvisi al $data";

$fineMittente = "";
$fineTempMitt = "$comune $ind_comune";
if ($par_tipo_ente_accertatore == 'Altro_Ente') $fineTempMitt = "MITTENTE " . $fineTempMitt;
else $fineTempMitt = "MITTENTE COMUNE DI " . $fineTempMitt;

if ($par_spese_not == 'sarida') $fineTempMitt .= " $ind_comune_bis $ind_com_bis_bis ";

//fput_margine_new ($c,$marg,$fh, $fineTempMitt);  // prima
$fineMittente = $fineTempMitt;

if ($formato_carta == 1)
{
	//$myClassVerbale->ChiudiFileFlusso ($fineNumeroTotale, $fineMittente);
	$NuovoListone->ChiudiFileFlusso ($nome_file_app, $fineNumeroTotale, $fineMittente);
}
else
{ 
	//$myClassVerbale->CreaPdf ($nome_file_app);
	$NuovoListone->CreaPdf ($nome_file_app, $tip, $formato_stampa, $nonfarlosemarco);
}
	
if ($nonfarlosemarco == 0)
{
	if ($formato_carta == 1 && $stampa == 2 && $cont_avvisi != 0 && $num_contr != 0)
		// se FLUSSO definitivo, con numero diverso da zero 
	{
		/*$query="update gitco.num_stamp_a4 set Num_Data_Flusso='$data_stapa',Num_Numero_Stampe='$cont_avvisi'
				where Num_Comune='$c' and Num_Tributo='V_CDS' and Num_Flusso='$flusso' and Num_Anno='$a'";*/
		$query = "INSERT INTO gitco.num_stamp_a4 
				 (Num_Flusso, Num_Comune, Num_Anno, Num_Tributo, Num_Data_Flusso, Num_Numero_Stampe)
			      values
				 ('$flusso', '$c', '$a', '$trib', '$data_registrazione', '$cont_avvisi')";
		//echo "<br>$query";
		safe_query ($query);
	}
}
else
	echo "<br>vorrei fare degli update FINE<br>";

//fclose($fh);
//esec_stampa_verbali($anteprima,$nome_file_app,$tipo_stampante,$cont_avvisi);
//$tipo_stampante="";


if ($formato_carta == 3)  // PDF
{
	if ($nonfarlosemarco == 0)
	{
		echo "<script>self.close();</script>";
	}
}
else if ($formato_carta == 1)  // flusso
{
	/*$nomeTempRar = explode (".", $nome_file_app);
	$nomeRar = $nomeTempRar[0] . ".rar";
	alert ($nomeRar);*/
	//esec_stampa_verbali (2, $nome_file_app, "", $cont_avvisi);
	crea_nuovo_file_rar ($nome_file_app, "/gitco/cds_new/", $cont_avvisi);
}



function CreaCodiceControlloBollVerbale ($numVerb, $anno2Cifre, $numBol, $istat)
{
	$numero_controllo_uno = $numVerb . $anno2Cifre . $numBol . $istat;
	$codice_controllo1 = fmod($numero_controllo_uno, 93);
	if (strlen($codice_controllo1)==1)
		$codice_controllo1 = "0" . $codice_controllo1;
	$numero_controllo_uno_completo = $numero_controllo_uno . $codice_controllo1;
	$lunghezza_cc1=strlen($numero_controllo_uno_completo);
	if ($lunghezza_cc1 < 18)
	{
		for ($lnc1=0; $lnc1<(18-$lunghezza_cc1); $lnc1++)
		{
			$num_controllo_bol1 .= "0";
		}
	}
	$numero_controllo_bollettino_uno = $num_controllo_bol1 . $numero_controllo_uno_completo;
	//$codice_controllo_uno = "<" . $numero_controllo_bollettino_uno . ">";
	$codice_controllo_uno = $numero_controllo_bollettino_uno;   //  la mercurio li vuole senza < >
	return $codice_controllo_uno;
}
/*
function CreaCodiceBollettino ($importo)  //  arriva una stringa! non numero
{
	if ($importo == 0 || $importo == "" || $importo == "0,00") return "";
	
	$tempArrivo = "";
	for ($i = 0; $i < strlen($importo); $i++)
	{
		if ($importo[$i] != ".")
			$tempArrivo .= $importo[$i];
	}
	$tempImporto = explode (",", $tempArrivo);
	$lungIntero = strlen($tempImporto[0]);
	$new = $tempImporto[0];
	for ($i = $lungIntero; $i < 8; $i++)
		$new = "0" . $new;
	
	$new .= "+" . $tempImporto[1];
	//alert ($importo . " e " . $new);
	return $new;
}
*/
/*
function CreaNumeroInLettera ($numeroConVirgola)  //  arriva una stringa! non numero
{
	if ($numeroConVirgola == 0 || $numeroConVirgola == "" || $numeroConVirgola == "0,00") return "";
	
	$tempArrivo = "";
	for ($i = 0; $i < strlen($numeroConVirgola); $i++)
	{
		if ($numeroConVirgola[$i] != ".")
			$tempArrivo .= $numeroConVirgola[$i];
	}
	$virgola = strpos($tempArrivo, ",");//Cerco la posizione della stringa.
	$decimali = substr($tempArrivo, ($virgola+1));//Recupero i decimali.
	$interi = substr($tempArrivo, 0, $virgola);//Recupero gli interi
	$num_lett = trasforma_numero ($interi, "", "", "");
	$inLettere = $num_lett . "/" . $decimali;
	return $inLettere;
}
*/

function AvanzaJavascript ($i, $tot, $campo, $oldDiv)
{
	if ($tot == 0)
	{
		echo "<script>cambiacoloretd('$campo', 10);</script>\n\n";
		return;
	}
	//sleep(1);
	$parz = $i * 100 / $tot;
	if ($parz < 10) $numDiv = 1;
	else if ($parz < 20) $numDiv = 2;
	else if ($parz < 35) $numDiv = 3;
	else if ($parz < 45) $numDiv = 4;
	else if ($parz < 55) $numDiv = 5;
	else if ($parz < 70) $numDiv = 6;
	else if ($parz < 80) $numDiv = 7;
	else if ($parz < 90) $numDiv = 8;
	else if ($parz < 97) $numDiv = 9;
	else if ($parz >= 97) $numDiv = 10;
	else if ($i == $tot-1) $numDiv = 10;
	else $numDiv = 9;

	if ($campo == "trasferimenti" && $i != 0)
		echo "<script>cambiascritta($i,$tot);</script>\n";
	if ($oldDiv != $numDiv)
	{
		//alert ($tot);
		$oldDiv = $numDiv;
		flush();
		ob_flush();
		echo "<script>cambiacoloretd('$campo', $numDiv);</script>\n";
	}
	return $oldDiv;
}
?>

</body>
</html>