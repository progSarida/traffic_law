<?php

function esegui_query ($query)
{
	$myDb = new db_class();
	$res = $myDb->esegui_query($query);
	return $res;
}

function ultimo_id_query ()
{
	$myDb = new db_class();
	$res = $myDb->last_id_query($query);
	return $res;
}

function risultati_query ($risultatoQuery)
{
	$myDb = new db_class();
	$riga = $myDb->fetch_assoc_query($risultatoQuery);
	return $riga;
}

function numero_risposte_query ($risultatoQuery)
{
	$myDb = new db_class();
	$numero = $myDb->num_rows_query($risultatoQuery);
	return $numero;
}

function safe_query($query = "")
{
	// Query MySQL : interfaccia uniforme per l'esecuzione di query
	// quando una query fallisce questa funzione restituisce dei risultati che
	// consentono di capire meglio cosa e' andato storto.
	
	//alertAllGlobalVariables();
	
	$queryAut = "SELECT ID FROM autenticazione WHERE ID='" . $_SESSION['aut_progr'] . "' AND Tipo ='" . $_SESSION['aut_tipo'] . "'";
	//echo ("<br>" . $query . "<br>");
	$result = mysql_query ( $queryAut );
	
	 
	
	//$result = mysql_query ( "SELECT ID FROM autenticazione WHERE ID=\"".$_SESSION['aut_progr']."\" AND Tipo =\"".$_SESSION['aut_tipo']."\"" );
    $num = mysql_num_rows($result);
    
	if (empty ($query)) { return FALSE; }
    if ($num==1)
    {
        $result = mysql_query ( $query )
                  or die ("<hr><b>Errore!</b> la query e' fallita : "
                          ."<ul><li>error n = " . mysql_errno()
                          ."<li>error = " . mysql_error()
                          ."<li>query = " . $query
                          ."</ul><hr>");
    }
    else
    {	
        $result = mysql_query ( $query ) or die ("Query fallita: informare il webmaster.");
    }
    
    return $result;
}


function single_answer_query($query="")
{
	/** ***********************************************************************
	* single_answer_query:                                                    *
	* serve ogni volta che occorre fare un'interrogazione al db che debba     *
	* restituire un singolo campo.                                            *
	* Ritorna direttamente il valore della risposta.                          *
	**************************************************************************/
	$result = safe_query($query);
	$array_result = mysql_fetch_row($result);
	$answer = $array_result[0];
	return $answer;
}

function single_query($query)
{
	/** ***********************************************************************
	* single_query:                                                    		  *
	* serve ogni volta che occorre fare un'interrogazione al db che debba     *
	* restituire un singolo campo.                                            *
	* Ritorna direttamente il valore della risposta.                          *
	**************************************************************************/
	
	$result = mysql_query($query);
	$array_result = mysql_fetch_row($result);
	$answer = $array_result[0];
	return $answer;
}


function table_insert_record($table, $fields_to_insert, $values_to_insert)
{
	/** --------------------------------------------------------------------------
	* id table_insert_record($table, $fields_to_insert, $values_to_insert)
	*
	* Funzione di autocomposizione query per l'inserimento di nuovi record in una
	* tabella $table.
	* Restituisce 0 in caso di richiesta errata e l'identificatore del nuovo record
	* in caso di inserimento avvenuto correttamente.
	*----------------------------------------------------------------------------*/
	
    $dim1 = count($fields_to_insert);
    $dim2 = count($values_to_insert);
    
    if($dim1!=$dim2 || $dim1==0 || $dim2==0) return 0;

    $i = 0;
    $clause = "";
    for($i; $i<$dim1; $i++)
    {
        $clause = $clause.$fields_to_insert[$i];
        if($i<$dim1-1) $clause = $clause.", ";
    }
    
    $query = "insert into $table (".$clause.") values (";
    $clause = "";
    $i = 0;
    for($i; $i<$dim1; $i++)
    {
        $clause = $clause."\"".$values_to_insert[$i]."\"";
        if($i<$dim1-1) $clause = $clause.", ";
    }
    
    $query = $query.$clause.")";
    $control_query = safe_query($query);
    $progr = mysql_insert_id();
    
    return $progr;
}

function table_insert_record_query($table, $fields_to_insert, $values_to_insert)
{
	//------------------------------------------------------------------------------
	// id table_insert_record($table, $fields_to_insert, $values_to_insert)
	//
	// Funzione di autocomposizione query per l'inserimento di nuovi record in una
	// tabella $table.
	// Restituisce 0 in caso di richiesta errata e l'identificatore del nuovo record
	// in caso di inserimento avvenuto correttamente.
	//------------------------------------------------------------------------------

	$dim1 = count($fields_to_insert);
	$dim2 = count($values_to_insert);

	if($dim1!=$dim2 || $dim1==0 || $dim2==0) return 0;

	$i = 0;
	$clause = "";
	for($i; $i<$dim1; $i++)
	{
	$clause = $clause.$fields_to_insert[$i];
	if($i<$dim1-1) $clause = $clause.", ";
	}

	$query = "insert into $table (".$clause.") values (";
			$clause = "";
    $i = 0;
	for($i; $i<$dim1; $i++)
	{
	$clause = $clause."\"".$values_to_insert[$i]."\"";
	if($i<$dim1-1) $clause = $clause.", ";
	}

	$query = $query.$clause.")";
	//echo "<br>$query";

	return $query;

}

function table_update_record( $table, $fields_to_update, $values_to_update, $campo , $value )
{
	//------------------------------------------------------------------------------
	// Funzione di autocomposizione query per l'aggiornamento di record
	// preesistenti. Il valore del campo del record da modificare deve essere
	// passato nel parametro $value mentre il campo nel parametro $campo.
	// Restituisce FALSE in caso di impossibilità ad eseguire l'aggiornamento,
	// e TRUE in caso di aggiornamento eseguito con successo.
	//------------------------------------------------------------------------------
	
    $dim1 = count($fields_to_update);
    $dim2 = count($values_to_update);
    
    if($dim1!=$dim2 || $dim1==0) return false;
    if($value =="" || $value == NULL) return false;
    if($campo =="" || $campo == NULL) return false;
    
    $i = 0;
    $clause = "";
    for($i; $i<$dim1; $i++)
    {
        $clause = $clause.$fields_to_update[$i]."=\"".$values_to_update[$i]."\"";
        if($i<$dim1-1) $clause = $clause.", ";
    }
    
    $query = "UPDATE ".$table." SET ".$clause." WHERE ".$campo." =\"".$value."\"";
    $res = safe_query($query);
    
    if($res!=NULL)
    {
        return true;
    }
    else
    {
    	return false;
    }
    	
}

function table_update_record_query( $table, $fields_to_update, $values_to_update, $campo , $value )
{
	//------------------------------------------------------------------------------
	// Funzione di autocomposizione query per l'aggiornamento di record
	// preesistenti. Il valore del campo del record da modificare deve essere
	// passato nel parametro $value mentre il campo nel parametro $campo.
	// Restituisce FALSE in caso di impossibilità ad eseguire l'aggiornamento,
	// e TRUE in caso di aggiornamento eseguito con successo.
	//------------------------------------------------------------------------------

	$dim1 = count($fields_to_update);
	$dim2 = count($values_to_update);

	if($dim1!=$dim2 || $dim1==0) return false;
	if($value =="" || $value == NULL) return false;
	if($campo =="" || $campo == NULL) return false;

	$i = 0;
	$clause = "";
	for($i; $i<$dim1; $i++)
	{		
		$clause = $clause.$fields_to_update[$i]."=\"".$values_to_update[$i]."\"";
		if($i<$dim1-1) $clause = $clause.", ";
	}

	$query = "UPDATE ".$table." SET ".$clause." WHERE ".$campo." =\"".$value."\"";
	
	//echo "<br>$query";
	return $query;
	 
}

function select_mysql_array ( $select_fields, $from_table, $where=' ', $order_field = ' ', $order="ASC", $distinct = "no" )
{
	/**
	 * Ritorna un Array multidimensionale con i risultati della SELECT
	 *
	 * PARAMETRI:
	 * $select_fields -> STRINGA: Campi da selezionare separati da virgole.
	 * $from_table    -> STRINGA: Nome Tabella.
	 * $where         -> (facoltativo) STRINGA: Inserire campi uguali a valori (es. field = '1' AND field_2 = 'Y'). Default: "".
	 * $order_field   -> (facoltativo) STRINGA: campo di ordinamento. Default: "".
	 * $order         -> (facoltativo) STRINGA: ASC o DESC tipo di ordinamento. Default: "ASC".
	 *
	 */

	$stringa = "SELECT ";
	if($distinct == "si")
		$stringa.= "DISTINCT ";
	$stringa.= $select_fields ." FROM ". $from_table." ";
	
	
	if($where!=" ")
	{
		$stringa .= "WHERE ".$where." ";
	}

	if($order_field!=" ")
	{
		$stringa .= "ORDER BY ".$order_field." ".$order." ";
	}

	$query = mysql_query($stringa);

	$results = array();

	while($line = mysql_fetch_array($query, MYSQL_ASSOC))
	{
		$results[] = $line;
	}
	
	return $results;
}

function mysql_array ( $query )
{
	$res = mysql_query( $query );
	
	$results = array();

	while($line = mysql_fetch_assoc( $res ))
	{
		$results[] = $line;
	}

	return $results;
}

function da_a_utente ( $c , $dacognome = null , $acognome = null , $danome = null , $anome = null )
{
	$query = "(SELECT ID, Nome, Cognome AS utente_cognome FROM utente ";
	$query.= "WHERE Cognome != '' AND CC_Comune = '".$c."' ";
	if($dacognome != null)
	{
		$query.= "AND ( ( Cognome > '".addslashes($dacognome)."' ) ";
		$query.= "AND ( Cognome < '".addslashes($acognome)."' ) ";
		$query.= "OR ( Cognome = '".addslashes($dacognome)."' ";
		if($danome != null)
		{	
			$query.= "AND Nome >= '".addslashes($danome)."' ";
		}
		
		$query.= ") OR ( Cognome = '".addslashes($acognome)."' ";
		if($anome != null)
		{
			$query.= "AND Nome <= '".addslashes($anome)."' ";
		}	
		$query.= ") ) ";
	}
	
	$query.= " ) ";
	
	$query.= "UNION ";
	$query.= "(SELECT ID, Nome, Ditta AS utente_cognome FROM utente ";
	$query.= "WHERE Ditta != '' AND CC_Comune = '".$c."' ";
	
	if($dacognome != null)
	{
		$query.= "AND ( Ditta >= '".addslashes($dacognome)."' AND Ditta <= '".addslashes($acognome)."' ) ";
	}
	$query.= ") ";
	$query.= "ORDER BY utente_cognome ASC, Nome ASC";
	
	return $query;
}

function da_a_partita( $c , $da_n_elenco = null , $a_n_elenco = null , $where = null )
{	
	$query = "SELECT * FROM partita_tributi ";
	$query.= "WHERE CC = '".$c."' ";
	if($da_n_elenco != null)
	{
		$query.= "AND ( Comune_ID >= '".$da_n_elenco."' AND Comune_ID <= '".$a_n_elenco."' ) ";
	}
	if($where != null)
	{
		$query.= "AND ".$where." ";
	}
	
	$query.= "ORDER BY Comune_ID ASC";
	
	return $query;
}

function selezione_date_query($table, $campo_data, $dadata, $adata)
{
	$query = "";
	for( $i=0 ; $i<count($dadata) ; $i++ )
	{
		if( $dadata[$i] != null && $dadata[$i] != "" )
		{
	
			if($query!="")
				$query.= "AND ";
			
			$query.= "( ".$table.".".$campo_data[$i]." >= '".$dadata[$i]."' AND ".$table.".".$campo_data[$i]." <= '".$adata[$i]."' ) ";
			
		}
	}
	
	return $query;
}

function da_a_partita_order( $c , $da_n_elenco = null , $a_n_elenco = null , $where = null, $order = null )
{
	$query = "SELECT DISTINCT PA.*, TR.Info_Cartella FROM partita_tributi AS PA, tributo AS TR ";
	$query.= "WHERE PA.CC = '".$c."' AND PA.ID = TR.Partita_ID ";
	if($da_n_elenco != null)
	{
		$query.= "AND ( PA.Comune_ID >= '".$da_n_elenco."' AND PA.Comune_ID <= '".$a_n_elenco."' ) ";
	}
	if($where != null)
	{
		$query.= "AND ".$where." ";
	}

	if($order=="cronologico" || $order==null)
		$query.= "ORDER BY PA.Anno_Riferimento, PA.Comune_ID ASC";
	else if($order=="info")
		$query.= "ORDER BY TR.Info_Cartella ASC";
	else if($order=="verbale")
		$query.= "ORDER BY ABS(TR.Titolo_Sanzione) ASC";

	return $query;
}

function da_a_data( $c , $atto , $campo_data , $dadata = null , $adata = null , $where = null , $ctrl_anno = true )
{
	$query = "SELECT * FROM atto ";
	$query.= "WHERE CC = '".$c."' ";
	
	if( ($atto == "Ingiunzione" || $atto == "Avviso di intimazione ad adempiere") && $campo_data == "Data_Notifica" )
	{
		$query.= "AND ";
		$query.= "( ";
		
		$query.= "( Atto = 'Ingiunzione' AND ( Data_Notifica is not null AND Data_Notifica != '0000-00-00' ) ";
		
		if($ctrl_anno == true)//SCADENZA INGIUNZIONE DOPO UN ANNO
			$query.= "AND ( Data_Notifica < '". date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" )) ."' ) ";
		
		$query.= ") OR ( Atto = '".$atto."' AND ( Data_Notifica is not null AND Data_Notifica != '0000-00-00' ) ";
		
		if($ctrl_anno == true)//SCADENZA AVVISO DOPO 6 MESI
			$query.= "AND ( Data_Notifica < '". date("Y-m-d" , strtotime( date('Y-m-d')."-6 month" )) ."' ) ";

		$query.= ") OR ( Rielabora_Flag = 'si' ) ) ";
		
	}
	
	if($dadata != null)
	{
		$query.= "AND ( ".$campo_data." >= '".$dadata."' AND ".$campo_data." <= '".$adata."' ) ";
	}
	
	if($where != null)
	{
		$query.= "AND ".$where." ";
	}
	$query.= "ORDER BY Comune_ID ASC";

	return $query;
}

function da_a_data_array ( $c , $atto , $campo_data , $dadata , $adata , $where = null )
{
	$query = "SELECT * FROM atto ";
	$query.= "WHERE CC = '".$c."' AND Atto = '".$atto."' ";

	for( $i=0 ; $i<count($dadata) ; $i++ )
	{		
		if( $dadata[$i] != null && $dadata[$i] != "" )
		{
			
			$query.= "AND ( ".$campo_data[$i]." >= '".$dadata[$i]."' AND ".$campo_data[$i]." <= '".$adata[$i]."' ) ";
		}
	}	

	if($where != null)
	{
		$query.= "AND (".$where.") ";
	}
	
	$query.= "ORDER BY Atto ASC, Anno_Cronologico ASC , ID_Cronologico ASC, Anno_Flusso ASC , Numero_Flusso ASC ";

	return $query;
}

function da_a_data_array_order ( $c , $atto , $campo_data , $dadata , $adata , $where = null , $order = null, $where2 = null, $where3 = null)
{
	$query = "SELECT DISTINCT atto.* FROM atto, tributo ";
	
	if($order=="alfabetico")
	{
		
		$query.= ", partita_tributi, ";
		$query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
		$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
		$query.="UNION ";
		$query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
		$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
		$query.="AS UNIONE_UTENTE ";
		
	}
	
	if($order=="tribunale")
	{
		$query.= ", partita_tributi, utente, indirizzo, ufficio_giudiziario ";
	}
	
	$query.= "WHERE atto.CC = '".$c."' AND atto.Atto = '".$atto."' AND atto.Partita_ID = tributo.Partita_ID ";
	
	if($order=="alfabetico")
	{
		$query.= " AND partita_tributi.ID = Atto.partita_ID AND UNIONE_UTENTE.ID = partita_tributi.Utente_ID ";
	}
	
	if($order=="tribunale")
	{
		$query.= " AND partita_tributi.ID = atto.partita_ID AND utente.ID = partita_tributi.Utente_ID AND ";
		$query.= " indirizzo.Utente_ID = utente.ID AND ufficio_giudiziario.Tipo = 'tribunale' AND ufficio_giudiziario.CC = indirizzo.CC_Indirizzo ";
	}
	
	for( $i=0 ; $i<count($dadata) ; $i++ )
	{
		if( $dadata[$i] != null && $dadata[$i] != "" )
		{
				
			$query.= "AND ( atto.".$campo_data[$i]." >= '".$dadata[$i]."' AND atto.".$campo_data[$i]." <= '".$adata[$i]."' ) ";
		}
	}

	if($where != null)
	{
		$query.= "AND (".$where.") ";
	}
	
	if($where2 != null)
	{
		$query.= "AND (".$where2.") ";
	}
	
	if($where3 != null)
	{
		$query.= "AND (".$where3.") ";
	}

	$query.= "ORDER BY ";
	
	if($order=="verbale")
		$query.= "ABS(tributo.Titolo_Sanzione) ASC, ";
	if($order=="info")
		$query.= "atto.Info_Cartella ASC, ";
	if($order=="alfabetico")
		$query.= "UNIONE_UTENTE.NOME_UTENTE ASC , UNIONE_UTENTE.Nome ASC, ";
	if($order=="tribunale")
		$query.= "ufficio_giudiziario.CC_Ufficio ASC, ";
	
	$query.= "Atto ASC, Anno_Cronologico ASC, ID_Cronologico ASC";

	return $query;
}

function select_pignoramento_presso_terzi ( $c , $where = null , $order = null, $where2 = null )
{
	$query = "SELECT DISTINCT TERZI.Azienda, TERZI.Terzo_ID, GEN.Partita_ID FROM pignoramento_presso_terzi AS TERZI, pignoramento_generale AS GEN, tributo ";

	if($order=="alfabetico")
	{

		$query.= ", partita_tributi, ";
		$query.="(	( SELECT utente.ID, utente.Cognome AS NOME_UTENTE, utente.Nome FROM utente  ";
		$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere != 'D' ) ";
		$query.="UNION ";
		$query.="( SELECT utente.ID, utente.Ditta AS NOME_UTENTE, utente.Nome FROM utente  ";
		$query.="WHERE utente.CC_Comune = '".$c."' AND utente.Genere = 'D' )	) ";
		$query.="AS UNIONE_UTENTE ";

	}

	$query.= "WHERE GEN.CC = '".$c."' AND TERZI.Pignoramento_ID = GEN.ID AND GEN.Tipo = 'terzi' ";
	$query.= "AND tributo.Partita_ID = GEN.Partita_ID AND TERZI.Azienda!='' AND TERZI.Terzo_ID = 0 ";

	if($order=="alfabetico")
	{
		$query.= " AND partita_tributi.ID = GEN.partita_ID AND UNIONE_UTENTE.ID = partita_tributi.Utente_ID ";
	}

	if($where != null)
	{
		$query.= "AND (".$where.") ";
	}

	if($where2 != null)
	{
		$query.= "AND (".$where2.") ";
	}

	$query.= "ORDER BY ";

	if($order=="verbale")
		$query.= "ORDER BY tributo.Anno_Tributo ASC, ABS(tributo.Titolo_Sanzione) ASC ";
	if($order=="info")
		$query.= "tributo.Info_Cartella ASC, ";
	if($order=="alfabetico")
		$query.= "UNIONE_UTENTE.NOME_UTENTE ASC , UNIONE_UTENTE.Nome ASC, ";

	$query.= "TERZI.ID ASC";

	return $query;
}


function where_campi ( $array_fields , $array_values )
{
	$num = count($array_fields);
	$ctrl_num = count($array_values);

	if($num != $ctrl_num) return false;
	
	$query = "";
	for( $i=0; $i<$num; $i++ )
	{
		if($array_values[$i] != null && $array_values[$i] != "")
		{
			$query .= $array_fields[$i]." = '".$array_values[$i]."' AND ";
		}
	}
	
	$query .= "1";
	
	return $query;
	
}

function where_stati_notifica ( $array_fields , $array_values )
{
	$num = count($array_fields);
	$ctrl_num = count($array_values);

	if($num != $ctrl_num) return false;

	$query = "";
	for( $i=0; $i<$num; $i++ )
	{
		if($array_values[$i] != null && $array_values[$i] != "")
		{
			$valore = $array_values[$i];
			if($valore == "Nessuno" || $valore == "Nessuna") $valore = "0";
			
			if($valore == "Tutti" || $valore == "Tutte")
				$query .= $array_fields[$i]." != '0' AND ";
			else
				$query .= $array_fields[$i]." = '".$valore."' AND ";
				
		}
	}

	$query .= "1";

	return $query;

}

function where_date_vuote ($array_date)
{
	$num = count($array_date);
	
	$query = "";
	for( $i=0; $i<$num; $i++ )
	{
		$query .= "( ".$array_date[$i]." = null OR ".$array_date[$i]." = '0000-00-00' ) AND ";
	}
	
	$query .= "1";
	
	return $query;
}

function where_giacenza ($giacenza, $ind_validato)
{
	$query = "";
	if($giacenza == "Nessuno")
	{
		$query .= "atto.Stato_Notifica = 0 ";
		if($ind_validato=="attesa")	$query .= "AND atto.Indirizzo_Validato!='si'";
		else if($ind_validato=="Validato") $query .= "AND atto.Indirizzo_Validato='si'";
	}
	else if($giacenza == "Tutti")
	{
		$query .= "atto.Stato_Notifica != 0 ";
		if($ind_validato=="attesa")	$query .= "AND atto.Indirizzo_Validato!='si'";
		else if($ind_validato=="Validato") $query .= "AND atto.Indirizzo_Validato='si'";
	}
	else if($giacenza!="")
	{
		$query .= "atto.Stato_Notifica = '".$giacenza."' ";
		if($ind_validato=="attesa")	$query .= "AND atto.Indirizzo_Validato!='si'";
		else if($ind_validato=="Validato") $query .= "AND atto.Indirizzo_Validato='si'";
	}
	else if($giacenza=="")
	{
		if($ind_validato=="attesa")	$query .= "atto.Indirizzo_Validato!='si'";
		else if($ind_validato=="Validato") $query .= "atto.Indirizzo_Validato='si'";
	}

	return $query;
}

function check_omonimi($genere, $PI, $ditta, $CF, $nome, $cognome, $CC , $data , $cc_com)
{
	/**
	* LA FUNZIONE RITORNA "omo" e gli ID omonimi SE ESISTONO UTENTI OMONIMI
	*
	* SE ALCUNI CAMPI COMBACIANO MA NON SI HA LA CERTEZZA DI OMONIMIA
	* SI RITORNA "dubbi" e gli ID dubbi SE ESISTONO UTENTI CON DUBBIA OMONIMIA
	*
	* SE NON SI HA OMONIMIA SI RITORNA "no"
	*/

	$select_fields = "ID, Comune_ID, Genere, Partita_Iva, Ditta, Codice_Fiscale, Nome, Cognome, CC_Nascita , Data_Nascita";

	$progr_omo = array();
	$progr_omo_dubbi = array();
	$omonimiID = "";
	$dubbiID = "";
	
	//CONTROLLO DITTA
	if($genere=="D")
	{
		if($PI!="" && $PI!="00000000000")
		{
			$where = "Genere = \"D\" AND Partita_Iva = \"".$PI."\" AND CC_Comune = \"".$cc_com."\"";
			$omo = select_mysql_array($select_fields, "utente", $where);
			$num = count($omo);
			if($num!=0)
			{
				for($i=0; $i<$num; $i++)
				{
					//PARTITA IVA
					if($omo[$i]['Partita_Iva']!=null && $omo[$i]['Partita_Iva']!="00000000000")
					{
						if($PI == $omo[$i]['Partita_Iva'])	
							return "omo ".$omo[$i]['Comune_ID'];	
					}
				}
			}
		}		
		
		//DITTA
		$where = "Genere = \"D\" AND Ditta = \"".$ditta."\" AND CC_Comune = \"".$cc_com."\"";
		$omo = select_mysql_array($select_fields, "utente", $where);
		$num = count($omo);
		if($num==0)	{	return "no";	}
		else 
		{
			for($i=0; $i<$num; $i++)
			{
				//PARTITA IVA
				if($PI!=null && $omo[$i]['Partita_Iva']!=null)
				{
					if($PI == $omo[$i]['Partita_Iva'])	{	$progr_omo[] = $omo[$i]['Comune_ID'];	}
				}
				else 
				{
					$progr_omo_dubbi[] = $omo[$i]['Comune_ID'];
				}
			}
			
			if(count($progr_omo)>0)
			{
				$stringa = "";
				for($y=0;$y<count($progr_omo);$y++)
				{
					if($y==count($progr_omo)-1)
					{
						$stringa .= $progr_omo[$y];
					}
					else
					{
						$stringa .= $progr_omo[$y]." ";
					}
				}
				
				$omonimiID = "omo ".$stringa;
			}
			
			if(count($progr_omo_dubbi)>0)
			{
				$stringa = "";
				for($y=0;$y<count($progr_omo_dubbi);$y++)
				{
					if($y==count($progr_omo_dubbi)-1)
					{
						$stringa .= $progr_omo_dubbi[$y];
					}
					else 
					{
						$stringa .= $progr_omo_dubbi[$y]." ";
					}
				}
				
				$dubbiID = "dubbi ".$stringa;
			}	
				
			if(count($progr_omo)==0 && count($progr_omo_dubbi)==0)
				return "no";
			else
				return $omonimiID.$dubbiID;
		}
		
	}
	//CONTROLLO UTENTE
	else if($genere!="D")
	{
		//COGNOME NOME
		$where = "Genere = \"".$genere."\" AND Cognome = \"".$cognome."\" AND Nome = \"".$nome."\" AND CC_Comune = \"".$cc_com."\"";
		$omo = select_mysql_array($select_fields, "utente", $where);
		
		$num = count($omo);
		
		if($num==0)	{	return "no";	}
		else
		{
			for($i=0; $i<$num; $i++)
			{
				//PARTITA IVA
				if($CF!=null && $omo[$i]['Codice_Fiscale']!= null)
				{
					if($CF == $omo[$i]['Codice_Fiscale'])	{	$progr_omo[] = $omo[$i]['Comune_ID'];	}
				}
				else 
				{
					$Cod = "";
					$datanasc = "";
					
					//CC_NASCITA
					if($CC!=null)
					{
						if($CC == $omo[$i]['CC_Nascita'])		{	$Cod = "si";		}
						else if($omo[$i]['CC_Nascita']!=null)	{	$Cod = "no";		}
					}
					
					//DATA_NASCITA
					if($data!=null)
					{
						if($data == $omo[$i]['Data_Nascita'])	{	$datanasc = "si";		}
						else if($omo[$i]['Data_Nascita']!=null)	{	$datanasc = "no";		}
					}
					
					if($Cod!="no" && $datanasc!="no")
					{
						if($Cod=="si" && $datanasc=="si")
						{
							$progr_omo[] = $omo[$i]['Comune_ID'];
						}
						else
						{
							$progr_omo_dubbi[] = $omo[$i]['Comune_ID'];
						}
					}
				}
			}
			
			if(count($progr_omo)>0)
			{
				$stringa = "";
				for($y=0;$y<count($progr_omo);$y++)
				{
					if($y==count($progr_omo)-1)
					{
						$stringa .= $progr_omo[$y];
					}
					else
					{
						$stringa .= $progr_omo[$y]." ";
					}
				}
			
				$omonimiID = "omo ".$stringa;				
			}
							
			if(count($progr_omo_dubbi)>0)
			{
				$stringa = "";
				for($y=0;$y<count($progr_omo_dubbi);$y++)
				{
					if($y==count($progr_omo_dubbi)-1)
					{
						$stringa .= $progr_omo_dubbi[$y];
					}
					else 
					{
						$stringa .= $progr_omo_dubbi[$y]." ";
					}
				}
			
				$dubbiID = "dubbi ".$stringa;
			}
		
			if(count($progr_omo)==0 && count($progr_omo_dubbi)==0)
				return "no";
			else 
				return $omonimiID.$dubbiID;
			
		}
	}
}

function estraiArray( $stringa , $titoli , $separatore = " ")
{
	/** 
	 * ESTRAE ARRAY MULTIDIMENSIONALE DA UNA STRINGA
	 */
	
	//CONTROLLO SE I TITOLI DI SEPARAZIONE SONO NELLA STRINGA
	//E SELEZIONO SOLO QUELLI CHE TROVO
	$num = count($titoli);
	for($i=0; $i < $num;$i++)
	{
		if( gettype(strpos($stringa, $titoli[$i])) != "boolean" )
		{
			$controlTitoli[] = $titoli[$i];
		}
	}
	
	$titoli = array();
	$titoli = $controlTitoli;
	$num = count($titoli)-1;
	
	//CREO ARRAY PER OGNI TITOLO CON SEPARATORE INTERNO LO SPAZIO DI DEFAULT
	for($i=0; $i < $num+1;$i++)
	{
	
		$array = explode ( $titoli[$num-$i] , $stringa ) ;
		$val = count($array);
		if($val>1)
		{
			$stringa = $array[$val-2];
		}
				
		if($array[$val-1]!=null)
		{
			$nome = "array_" . $titoli[$num-$i];
		
			${$nome} = explode( $separatore , $array[$val-1]);
		}
		
		$superArray[$titoli[$num-$i]] = ${$nome};
	}
	
	return $superArray;
	
}

function conv_num($value)
{
	
	if($value == null)
		return "";
	
	$virgola = strpos($value, ",");
	$punto = strpos($value, ".");
	
	if($virgola != false && $punto != false)
	{
		if($virgola < $punto)
		{
			$value = str_replace(",", "", $value);
			$value = str_replace(".", ",", $value);
		}
		else
		{
			$value = str_replace(".", "", $value);
		}
	}
	else if ($virgola == false && $punto != false)
	{
		$value = number_format($value, 2);
		$value = str_replace(".", ",", $value);
	}
	else if ($virgola != false && $punto == false)
	{
		$value = str_replace(",", ".", $value);
	}
	
	return $value;
	
}

?>