<?php

include_once "cls_db.php";
include_once "cls_help.php";
include_once "cls_DateTimeInLine.php";


class cls_Utils{

  public $cls_db;
  public $cls_date;

  public function __construct($dbConnect)
  {
    $this->cls_db = new cls_db($dbConnect["HOST"],$dbConnect["USERNAME"],$dbConnect["PASSWORD"],$dbConnect["DBNAME"]);
    $this->cls_date = new cls_DateTimeI("IT",false);
  }

    function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

  function crea_dir( $path )
  {
    if (!is_dir($path)) {
      $folder = explode("/",$path);
//var_dump($folder);
      $control_path = $folder[0];

      for($l=1;$l<count($folder);$l++)
      {
        $control_path .= "/".$folder[$l];
        if( is_dir( $control_path ) == false )
        {
          mkdir( $control_path );
        }
      }
    }
    return $path;
  }

  function check_folder($path){
      if ( !file_exists( $path ) && !is_dir( $path ) )
          return false;
      else
          return true;
  }

    public static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

  public function GetObjectQuery($arrayField,$table,$arrayWhere = null)
  {
    $a_params = array(
				'table' => $table,
				'fields'=> array(
				)
		);

    $query = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$table."'";
    $allType = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

    //print_r($allType);

		foreach($arrayField as $key => $val) {
      $Type = "";
      for($i=0; $i<count($allType); $i++)
      {
        if($key == $allType[$i]["COLUMN_NAME"])
        {
          $Type = $this->GetTypeObject($allType[$i]["DATA_TYPE"]);
          break;
        }
      }
      if($Type=="")
		    $Type = is_numeric($val)?"int":"string";
		  //if($val=='null'){$Type = "int"; $val="";}
		  //echo $Type.": $key = $val<br>";
			array_push($a_params['fields'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
		}

    if($arrayWhere!=null)
    {
        $a_params['updateField'] = array();
      foreach($arrayWhere as $key => $val) {
        $Type = "";
        for($i=0; $i<count($allType); $i++)
        {
          if($key == $allType[$i]["COLUMN_NAME"])
          {
            $Type = $this->GetTypeObject($allType[$i]["DATA_TYPE"]);
            break;
          }
        }
        if(gettype($val)!="object") if($Type=="") $Type = is_numeric($val)?"int":"string";
        else if($Type=="") $Type = is_numeric($val->value)?"int":"string";
  		  //if($val=='null'){$Type = "int"; $val="";}
  		  //echo $Type.": $key = $val<br>";
          if(gettype($val)=="object")
          {
              //if($i==0) $a_params['updateField'] = array(  'name' => $key, 'type' => $Type, 'value' =>  $val->value, 'operator' => $val->operator);
               array_push($a_params['updateField'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val->value, 'operator' => $val->operator));
          }
          else
          {
              //if($i==0) $a_params['updateField'] = array(  'name' => $key, 'type' => $Type, 'value' =>  $val);
              array_push($a_params['updateField'],array(  'name' => $key, 'type' => $Type, 'value' =>  $val));
          }

  		}
    }

    return $a_params;
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

  private function GetTypeObject($Type)
  {
    $result = "";
    switch(strtolower($Type))
    {
      case "int": $result = "int"; break;
      case "tinyint": $result = "int"; break;
      case "smallint": $result = "int"; break;
      case "mediumint": $result = "int"; break;
      case "bigint": $result = "int"; break;
      case "decimal": $result = "int"; break;
      case "float": $result = "int"; break;
      case "double": $result = "int"; break;
      case "real": $result = "int"; break;
      case "bit": $result = "int"; break;
      case "boolean": $result = "int"; break;
      case "serial": $result = "int"; break;
      case "date": $result = "date"; break;
      case "datetime": $result = "date"; break;
      case "timestamp": $result = "date"; break;
      case "time": $result = "date"; break;
      case "year": $result = "date"; break;
      default : $result = "string"; break;
    }
    return $result;
  }

  function mostra_file_path ( $path )
  {
  	return substr( $path , strpos( $path , "/archivio/" ));
  }

  function SostituisciTestoTraGraffe (&$myTesto, $testoGraffe, $cosaMettere, $bold = NULL)
  {
  	$temp = $myTesto;

  	$lunghGraffa = strlen ($testoGraffe);
  	$posto = strpos($temp, $testoGraffe);

  	if ($posto === false)
  	{
  		echo "<script>alert ('Il campo ".$testoGraffe." non è presente nel testo ".$temp."');</script>";
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

  function next_months ( $date , $num )
  {
  	if( substr($date,2,1) == "/" )
  		$date = $this->cls_date->GetDateDB($date,"IT");

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

    function decode_CF( $CF )
    {
        $array_CF = Array();

        $alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
        $numeri = "0123456789";
        $numeri_disp = "10   2 3 4   5 6 7 8 9";

        $lettere_mesi = "ABCDEHLMPRST";
        $lettere_omocodia = "LMNPQRSTUV";
        $checkOmocodia = 0;

        $cognome = substr($CF,0,3);
        $array_CF['COGNOME'] = $cognome;
        $nome = substr($CF,3,3);
        $array_CF['NOME'] = $nome;

        $annoStr = substr($CF,6,2);
        $anno = "";
        for($i=0;$i<strlen($annoStr);$i++){
            if (preg_match("/^\d+$/", substr($annoStr,$i,1)))
                $anno.= substr($annoStr,$i,1);
            else{
                $checkOmocodia = 1;
                $anno.= strpos($lettere_omocodia, substr($annoStr,$i,1));
            }
        }

        $mese = substr($CF,8,1);
        $mese = strpos($lettere_mesi, $mese)+1;
        if(strlen($mese)<2)		$mese_nascita = "0".$mese;
        else					$mese_nascita = $mese;

        $giornoStr = substr($CF,9,2);
        $giorno = "";
        for($i=0;$i<strlen($giornoStr);$i++){
            if (preg_match("/^\d+$/", substr($giornoStr,$i,1)))
                $giorno.= substr($giornoStr,$i,1);
            else{
                $checkOmocodia = 1;
                $giorno.= strpos($lettere_omocodia, substr($giornoStr,$i,1));
            }
        }

        if(intval($giorno) > 40){
            $array_CF['SESSO'] = "F";
            $giorno = intval($giorno) - 40;
            $giorno = strval($giorno);
        }
        else{
            $array_CF['SESSO'] = "M";
        }

        if(strlen($giorno)<2)	$giorno_nascita = "0".$giorno;
        else					$giorno_nascita = $giorno;

        $anno_odierno = date('Y');
        $pref_anno = substr($anno_odierno,0,2);
        $pref_anno_int = intval($pref_anno);
        $post_anno = substr($anno_odierno,2,2);
        $post_anno_int = intval($post_anno);

        if( $anno - $post_anno_int >= -5 )
            $pref_anno = strval( $pref_anno_int - 1 );

        $anno_nascita = $pref_anno . $anno;
        $array_CF['DATA_NASCITA'] = $anno_nascita."-".$mese_nascita."-".$giorno_nascita;

        $ccStr = substr($CF,12,3);
        $CC = substr($CF,11,1);

        for($i=0;$i<strlen($ccStr);$i++){
            if (preg_match("/^\d+$/", substr($ccStr,$i,1))){
                $CC.= substr($ccStr,$i,1);
            }
            else {
                $checkOmocodia = 1;
                $CC.= strpos($lettere_omocodia, substr($ccStr, $i, 1));
            }
        }

        $array_CF['CC_NASCITA'] = $CC;

        if($CC != null)
        {
            $verifica_stato = substr($CC,0,1);
            if($verifica_stato=="Z")
            {
                $query = "SELECT * FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$CC."'";
                $stato_control = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];
                //$stato_control = new stato_estero($CC);
                $array_CF['STATO_NASCITA'] = $stato_control;
                $array_CF['COMUNE_NASCITA'] = "";
            }
            else
            {
                $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$CC."'";
                $comune_control = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"comuni_lista")["Com_Nome"];
                //$comune_control = new comune($CC);
                $array_CF['STATO_NASCITA'] = "Italia";
                $array_CF['COMUNE_NASCITA'] = $comune_control;
            }
        }
        else
        {
            $array_CF['STATO_NASCITA'] = "";
            $array_CF['COMUNE_NASCITA'] = "";
        }

        $array_CF['OMOCODIA'] = $checkOmocodia;

        $sommaCod = 0;
        for($i=0;$i<strlen($CF)-1;$i++){
            $char = substr($CF,$i,1);
            if(($i%2)==0)
                $sommaCod+= strrpos($numeri_disp,$char) + strrpos($alfabeto_disp,$char);
            else
                $sommaCod+= strrpos($numeri,$char) + strrpos($alfabeto,$char);
        }

        $array_CF['CODICE_CONTROLLO'] = substr($alfabeto,($sommaCod%26),1);
        if($array_CF['CODICE_CONTROLLO']!=substr($CF,15,1))
            return false;
        else
            return $array_CF;

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
//	echo "CONTROLLO OMONIMIA ".$genere."<br>";
        //CONTROLLO DITTA
        if($genere=="D")
        {
//	    echo $PI."<br>";

            if($PI>0)
            {
                $where = "Genere = \"D\" AND Partita_Iva = \"".$PI."\" AND CC_Comune = \"".$cc_com."\"";
                $query = "SELECT ".$select_fields." FROM utente WHERE ".$where;
                $omo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
                //$omo = select_mysql_array($select_fields, "utente", $where);
                $num = count($omo);
//			echo $num."<br><br>";
                if($num!=0)
                {
                    for($i=0; $i<$num; $i++)
                    {
                        //PARTITA IVA
                        if($omo[$i]['Partita_Iva']>0)
                        {
                            if($PI == $omo[$i]['Partita_Iva'])
                                return "omo ".$omo[$i]['Comune_ID'];
                        }
                    }
                }
            }

            //DITTA
            $where = "Genere = \"D\" AND Ditta = \"".$ditta."\" AND CC_Comune = \"".$cc_com."\"";
            $query = "SELECT * FROM utente WHERE ".$where;

            //$result = mysql_query($query);
            $omo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
            //while($line = mysql_fetch_array($result, MYSQL_ASSOC))
              //  $omo[] = $line;

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

            $query = "SELECT ".$select_fields." FROM utente WHERE ".$where;
            $omo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            //$omo = select_mysql_array($select_fields, "utente", $where);

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
}

 ?>
