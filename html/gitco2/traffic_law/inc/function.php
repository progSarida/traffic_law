<?php
ini_set('display_errors',0);
require_once("function_reminder.php");
require_once("function_trespasser.php");
require_once("function_fine.php");
require_once("cli_function.php");
require_once(CLS."/cls_pagamenti.php");
//require_once("function_postalCharge.php");

function curPageName() {
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

function hasPrivateKey(string $userId){
    return file_exists(SIGNATURES . "/$userId".CERT_EXTENSION);
}

function checkTrespasser($trespasser,$name)
{
    $a_anomalyItems = array();
    if (trim($trespasser['BornDate']) == "")
        $a_anomalyItems[] = "Data di nascita";
    if($trespasser['DocumentCountryId']!= null and $trespasser['DocumentCountryId']!='' and $trespasser['DocumentCountryId']!='Z000')
        $a_anomalyItems[] = "Nazionalità patente";
    if (trim($trespasser['BornCountryId']) == "")
        $a_anomalyItems[] = "Stato di nascita";
    if (trim($trespasser['BornCountryId']) == "Z000" && trim($trespasser['BornPlace']) == "")
        $a_anomalyItems[] = "Città di nascita";
    if (trim($trespasser['Address']) == "")
        $a_anomalyItems[] = "Indirizzo";
    if (trim($trespasser['ZIP']) == "")
        $a_anomalyItems[] = "CAP";
    if (trim($trespasser['TaxCode']) == "")
        $a_anomalyItems[] = "Codice fiscale";
    if ($name == "")
        $a_anomalyItems[] = "Nominativo";
    return  !empty($a_anomalyItems) ? "Dati trasgressore errati: <br>".implode(', ', $a_anomalyItems) : null;
}

function checkLicense($trespasser){
  $str_anomalyText = "Dati patente incompleti: <br>";

  $a_anomalyItems = array();

  if (trim($trespasser['LicenseNumber']) == "")
    $a_anomalyItems[] = "Numero patente";
    if (trim($trespasser['LicenseDate']) == "")
      $a_anomalyItems[] = "Data patente";
      if (trim($trespasser['LicenseCategory']) == "")
        $a_anomalyItems[] = "Categoria patente";

        $str_anomalyText = !empty($a_anomalyItems) ? $str_anomalyText.implode(', ', $a_anomalyItems) : null;

        return $str_anomalyText;

}
function checkPasswordExpiration(CLS_DB $rs, string $cityId,string $serviceName,CLS_MESSAGE &$message,string &$str_out){
    trigger_error("SERVICE NAME: ".$serviceName);
    $rs_customerService=$rs->Select("CustomerService","CityId='$cityId' and ServiceId in (Select Id from Service where name='$serviceName')");
    $customerService=mysqli_fetch_array($rs_customerService);
    if($customerService['PasswordExpiration']!=null && $customerService['PasswordExpiration']!=''){
      $expirationDate=date_create($customerService['PasswordExpiration']);
      checkPasswordExpirationDate($serviceName,$expirationDate,$message,$str_out);
    }
}
function checkPasswordExpirationDate($serviceName, $expirationDate,CLS_MESSAGE &$message,string &$str_out){
    $diff = date_diff(new DateTime("now"),$expirationDate );
    if ($diff->invert == 1) {
      $message->addError("La password per la connessione al servizio $serviceName è scaduta.");
      $str_out .= $message->getMessagesString();
      echo $str_out;
      die();
    } else if ($diff->days < 10)
      $message->addWarning("La password per la connessione al servizio $serviceName scadrà il giorno {$expirationDate->format("d/m/Y")}");

}


function getArticleString($article,$paragraph,$letter):string{
    return $article. ($paragraph != '' ? '/' . $paragraph : ' ') . ($letter != '' ? '/' . $letter : '');
}

function YesNoOutDB($val){

    $a_Response = array("NO", "SI");
    return $a_Response[$val];
}

function chk_DoubleNumber($val){
    if($val==""|| $val=='0')  $val = 0.00;
    return (float)$val;

}

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

function ChkButtonLink(array $userButtons, string $button, string $href, int $position=5)
{
    $buttonDefinition=ACTIONS[$button];
    $iconClass=$buttonDefinition['icon'];
    $tooltip=$buttonDefinition['tooltip'];
    return ChkButton($userButtons,$buttonDefinition['button'],"<a href='$href' ><span class='tooltip-r $iconClass' title='$tooltip' data-placement='top' data-original-title='$tooltip' style='width:20px;position:absolute;left:{$position}px;top:5px;'></span></a>");
}
function convertToGetParam($name,$value): string
{
    if($value!='')
        return "&$name=".$value;
    return "";
}


function CheckbuttonOutDB($v){
    $str_Checkbutton = ($v=="1" OR $v=="on") ? "SI" : "NO";

    return $str_Checkbutton;
}


function AddSpaceUpperStr($str, $n){

    $str = strtolower($str);

    $str =str_replace("à","a'", $str);
    $str =str_replace("è","e'", $str);
    $str =str_replace("é","e'", $str);
    $str =str_replace("ì","i'", $str);
    $str =str_replace("ò","o'", $str);
    $str =str_replace("ù","u'", $str);

    $str=trim(substr(strtoupper($str),0, $n));

    $n_Len = strlen($str);

    for($i=0; $i<($n-$n_Len); $i++) {
        $str .= " ";
    }

    return $str;

}





function ChkPaymentProcedure($FineId, $rs){


    $rs_TMP_PaymentProcedure = $rs->Select('TMP_PaymentProcedure',"FineId=".$FineId);
    $n_TMP_PaymentProcedure = (mysqli_num_rows($rs_TMP_PaymentProcedure)>0) ? 0 : 1;
    return $n_TMP_PaymentProcedure;
}

function f_Exp_DateOutDB_Maggioli($d_Date){
    if($d_Date!=null || $d_Date!=""){
        $a_Date = explode('-',$d_Date);

        $d_Date = $a_Date[0]."/".$a_Date[1]."/".$a_Date[2];
    }

    return $d_Date;
}


function checkIsAValidDate($myDateString){
    return (bool)strtotime($myDateString);
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

function SkipFestiveDays($d, $cityId = null, $cls_db = null){
    //13/04/2023 dato che questa funzione è usata da cls/PagoPAER/PagoPAERService.php in cui viene istanziata la classe CLS_DB in versione xml,
    //viene passata attraverso il parametro $cls_db, altrimenti per retrocompatibilità si usa la golbale $rs che è l'istanza usata solitamente in
    //tutta l'applicazione
    if(isset($cls_db)) $rs = $cls_db;
    else global $rs;

    if (strpos($d, '/') !== false) $d = DateInDB($d);
    $a_date = explode("-",$d);
    $y = $a_date[0];

    $festiveDays = Array(
        '01-01' => 'Capodanno',
        '01-06' => 'Epifania',
        '04-25' => 'Liberazione',
        '05-01' => 'Festa Lavoratori',
        '06-02' => 'Festa della Repubblica',
        '08-15' => 'Ferragosto',
        '11-01' => 'Tutti Santi',
        '12-08' => 'Immacolata',
        '12-25' => 'Natale',
        '12-26' => 'Santo Stefano'
        );

    // calcola le date di Pasqua e Pasquetta
    $easterDays = easter_days($y);
    $easterMondayDays = $easterDays+1;
    $tmp = date('Y-m-d', strtotime('21 march ' . $y));
    $easterDay = date('m-d', strtotime($tmp . ' +' . $easterDays . 'days'));
    $easterMonday = date('m-d', strtotime($tmp . ' +' . $easterMondayDays . 'days'));

    // aggiunge le date di Pasqua e Pasquetta nell'elenco festività
    $festiveDays[$easterDay] = 'Pasqua';
    $festiveDays[$easterMonday] = 'Pasquetta';

    //aggiunge la festa patronale dell'ente nell'elenco festività se presente
    $PatronalFeast = $rs->getArrayLine($rs->SelectQuery("SELECT PatronalFeast FROM Customer WHERE CityId='".($cityId ?? $_SESSION['cityid'])."'"))['PatronalFeast'] ?? '';
    if (!empty($PatronalFeast)){
        $patronalFeast = date('m-d', strtotime($PatronalFeast));
        $festiveDays[$patronalFeast] = 'Festa Patronale';
    }

    $keys = array_keys($festiveDays);
    
    do{
        $monthDay = date('m-d', strtotime($d));
        if(in_array($monthDay, $keys) || date('w', strtotime($d)) == 0){
            $d = date('Y-m-d', strtotime($d. ' + 1 days'));
            $monthDay = date('m-d', strtotime($d));
        }
    } while(in_array($monthDay, $keys) || date('w', strtotime($d)) == 0);
    
    return $d;
}

function detectDelimiter($csvFile){
    $delimiters = array(
        ';' => 0,
        ',' => 0
    );

    $handle = fopen($csvFile, "r");
    $firstLine = fgets($handle);
    fclose($handle);
    foreach ($delimiters as $delimiter => &$count) {
        $count = count(str_getcsv($firstLine, $delimiter));
    }

    return array_search(max($delimiters), $delimiters);
}




function NumberDisplay($f){

	$f = number_format($f, 2, ',', '.');
	return $f;
}
function NumberDisplayXls($f){

	$f = str_replace(".",",",$f);
	return $f;
}

function createDropArea($elementId, $action, $fileInputName){
    return <<<DROP
        <form id="$elementId" method="post" action="{$action}" enctype="multipart/form-data">
            <div data-jqupload-droparea class="center-block">             
                <div>
                    <i class="fas fa-cloud-upload-alt fa-5x"></i>
                    <h3><strong>Trascina qui</strong> i file da caricare</h3>
                    <h5>oppure</h5>
                    <button type="button" class="btn" data-jqupload-button>Scegli un file</button>
                    <input data-jqupload-fileinput type="file" name="{$fileInputName}" multiple="">
                </div>
            </div>
            <ul data-jqupload-ul class="center-block">
            </ul>
        </form>
    DROP;
}

function CreateSelectCustomerUnion($Search_Locality){

    $rs_Function= new CLS_DB();


    $rs_Union = $rs_Function->Select('V_Customer',"CreationType=1 AND CityId='".$_SESSION['cityid']."'");
    $r_Union = mysqli_fetch_array($rs_Union);

    if($r_Union['CityUnion']>1){
        $str_Union = CreateSelect(MAIN_DB.".City","UnionId='".$_SESSION['cityid']."'","Title","Search_Locality","Id","Title",$Search_Locality,false);
    }else{
        $str_Union ='
    <select class="form-control" name="Search_Locality">
        <option value="'.$_SESSION['cityid'].'">'.$_SESSION['citytitle'].'</option>
    </select>
    ';
    }

    return $str_Union;
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


function CreateSelect($table, $where, $order, $selectname, $fieldid, $fieldvalue, $selectvalue,$required, $size=null, $class=null, $emptyoptiontxt=null, $extrahtml=null){

	$rs= new CLS_DB();
	$rows = $rs->Select($table,$where,$order);

    $str_add_class = ($class!=null) ? " ".$class : "";

	$str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectname.'"';
    if($size!=null)
        $str.= ' style="width:'.$size.'rem"';
    if($extrahtml!=null)
        $str.= ' '.$extrahtml;
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

function CreateSelectGroup($table, $where, $order, $selectname, $selectid, $fieldid, $fieldvalue, $fieldtogroupby, $selectvalue,$required, $size=null, $class=null){

    $rs= new CLS_DB();
    $rows = $rs->Select($table,$where,$order);
    $a_groups = array();

    while($row = mysqli_fetch_array($rows)) {
        $a_groups[$row[$fieldtogroupby]][] = array('Id' => $row[$fieldid], 'Value' => StringOutDB($row[$fieldvalue]));
    }

    $str_add_class = ($class!=null) ? " ".$class : "";

    $str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectid.'" ';

    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
    $str.= '>';

    if(!$required) $str.='<option></option>';

    foreach($a_groups as $groupname => $elements) {
        $str.= '<optgroup label="' . htmlspecialchars($groupname) . '">';
        foreach($elements as $element){
            $str.='<option value="'.$element['Id'].'"';
            if ($selectvalue==$element['Id']) $str.=' SELECTED ';
            $str.='>'.$element['Value'].'</option>';
        }
        $str.= '</optgroup>';
    }
    $str.='</select>';

    return $str;
}

function CreateSelectQueryGroup($query, $selectname, $selectid, $fieldid, $fieldvalue, $fieldtogroupby, $selectvalue,$required, $size=null, $class=null){
    
    $rs= new CLS_DB();
    $rows = $rs->SelectQuery($query);
    $a_groups = array();
    
    while($row = mysqli_fetch_array($rows)) {
        $a_groups[$row[$fieldtogroupby]][] = array('Id' => $row[$fieldid], 'Value' => StringOutDB($row[$fieldvalue]));
    }
    
    $str_add_class = ($class!=null) ? " ".$class : "";
    
    $str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectid.'" ';
    
    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
        $str.= '>';
        
        if(!$required) $str.='<option></option>';
        
        foreach($a_groups as $groupname => $elements) {
            $str.= '<optgroup label="' . htmlspecialchars($groupname) . '">';
            foreach($elements as $element){
                $str.='<option value="'.$element['Id'].'"';
                if ($selectvalue==$element['Id']) $str.=' SELECTED ';
                $str.='>'.$element['Value'].'</option>';
            }
            $str.= '</optgroup>';
        }
        $str.='</select>';
        
        return $str;
}

function CreateArraySelect(array $array, bool $keyandvalue, $selectname, $selectid, $selectvalue=null, bool $required=false, $size=null, $class=null, $emptyoptiontxt=null, bool $disabled=false){

    $str_add_class = ($class!=null) ? " ".$class : "";
    $b_emptySelectValue = false;

    $str = '<select'.($disabled ? ' disabled' : '').' class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectid.'" ';
    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
        $str.= '>';

    if(!$required) $str.='<option>'.$emptyoptiontxt.'</option>';
    
    if(is_null($selectvalue) || $selectvalue === '') $b_emptySelectValue = true;

    if($keyandvalue){
        foreach($array as $key => $value){
            $str.='<option value="'.$key.'"'.($b_emptySelectValue ? '' : ($key == $selectvalue ? ' selected' : '')).'>'.$value.'</option>';
        }
    } else {
        foreach($array as $value){
            $str.='<option value="'.$value.'"'.($b_emptySelectValue ? '' : ($value == $selectvalue ? ' selected' : '' )).'>'.$value.'</option>';
        }
    }
    $str.='</select>';

    return $str;
}

function CreateSelectExtended($table, $where, $order, $selectname, $selectid, $fieldid, $fieldvalue, $selectvalue,$required,$disabled, $size=null, $class=null, $emptyoptiontxt=null, $extrahtml=null){

    $rs= new CLS_DB();
    $rows = $rs->Select($table,$where,$order);

    $str_add_class = ($class!=null) ? " ".$class : "";

    $disable = ($disabled == true) ? " disabled" : "";

    $str = '<select'.$disable.' class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectid.'"';
    if($size!=null)
        $str.= ' style="width:'.$size.'rem"';
    if($extrahtml!=null)
        $str.= ' '.$extrahtml;
    $str.= '>';

        if(!$required) $str.='<option>'.$emptyoptiontxt.'</option>';
        while($row = mysqli_fetch_array($rows)) {
            $str.='<option value="'.$row[$fieldid].'"';
            if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
            $str.='>'.utf8_encode($row[$fieldvalue]).'</option>';

        }
        $str.='</select>';

        return $str;
}

function CreateSelectShort($table, $where, $order, $selectname, $fieldid, $fieldvalue, $selectvalue,$required, $size=null){

	$rs= new CLS_DB();
	$rows = $rs->Select($table,$where,$order);

	$str = '<select class="form-control" name="'.$selectname.'" id="'.$selectname.'" ';
	if($size!=null)
		$str.= 'style="width:'.$size.'rem"';
	$str.= '>';

	if(!$required) $str.='<option></option>';
	while($row = mysqli_fetch_array($rows)) {
		$str.='<option value="'.$row[$fieldid].'"';
		if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
		$str.='>'.utf8_encode(substr($row[$fieldvalue],0,150)).'...</option>';

	}
	$str.='</select>';

	return $str;
}
function CreateSelectQuery($query, $selectname, $fieldid, $fieldvalue, $selectvalue, $required, $size=null, $class=null, $emptyoptiontxt=null){

	$rs= new CLS_DB();
	$rows = $rs->SelectQuery($query);

	$str_add_class = ($class!=null) ? " ".$class : "";

	$str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectname.'"';
	if($size!=null)
	    $str.= ' style="width:'.$size.'rem"';
    $str.= '>';

    if(!$required) $str.='<option'.($emptyoptiontxt ? ' value=""' : '').'>'.$emptyoptiontxt.'</option>';
	while($row = mysqli_fetch_array($rows)) {
		$str.='<option value="'.$row[$fieldid].'"';
		if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
		$str.='>'.StringOutDB($row[$fieldvalue]).'</option>';

	}
	$str.='</select>';

	return $str;
}

function CreateSelectQueryExtended($query, $selectname, $selectid, $mainfieldid, $mainfieldvalue, array $optattrvalues, $selectmainvalue, $required, $size=null, $class=null, array $style=array()){

    $rs= new CLS_DB();
    $rows = $rs->SelectQuery($query);

    $str_add_class = ($class!=null) ? " ".$class : "";

    $str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectid.'"';

    if($style){
        $str.= ' style="';
        foreach($style as $rule => $value){
            $str.= $rule.':'.$value.';';
        }
        if($size!=null){
            $str.= 'width:'.$size.'rem';
        }
        $str .= '"';
    } else {
        if($size!=null)
            $str.= ' style="width:'.$size.'rem"';
    }
        $str.= '>';

        if(!$required) $str.='<option></option>';
        while($row = mysqli_fetch_array($rows)) {
            $str.='<option value="'.$row[$mainfieldid].'"';
            foreach ($optattrvalues as $dbcolumnname){
                $str.=' data-'.strtolower($dbcolumnname).'="'.$row[$dbcolumnname].'"';
            }
            if ($selectmainvalue==$row[$mainfieldid]) $str.=' SELECTED ';
            $str.='>'.StringOutDB($row[$mainfieldvalue]).'</option>';

        }
        $str.='</select>';

        return $str;
}




function CreateTxtChangeJQ($table, $where, $order, $changename, $fieldid, $fieldvalue, $spanview){

	$rs= new CLS_DB();
	$rows = $rs->Select($table,$where,$order);

	$str ='var a_'.$changename.' = {';
	while($row = mysqli_fetch_array($rows)) {
	    $field = preg_replace("/[\r\n]*/","",$row[$fieldvalue]);
	    $str.='"'.$row[$fieldid].'":"'.addslashes($field).'", ';
	}
	$str.='}

		$(\'#'.$changename.'\').change(function () {

		if(a_'.$changename.'[$(\'#'.$changename.'\').val()]== undefined){
			$(\'#'.$spanview.'\').html("ERR - Non trovato");
		}else{
			$(\'#'.$spanview.'\').html(a_'.$changename.'[$(\'#'.$changename.'\').val()]);
		}
	});';

	return $str;
}


function CreateSltChangeJQ($table, $where, $order, $changename, $fieldid, $fieldvalue, $select){

	$rs= new CLS_DB();
	$rows = $rs->Select($table,$where,$order);

	$str ='var a_'.$changename.' = {';
	while($row = mysqli_fetch_array($rows)) {
	    $field = preg_replace("/[\r\n]*/","",$row[$fieldvalue]);
	    $str.='"'.$row[$fieldid].'":"'.addslashes($field).'", ';
	}
	$str.='}


		$(\'#'.$changename.'\').change(function () {
		var cng_name = a_'.$changename.'[$(\'#'.$changename.'\').val()];


		if(a_'.$changename.'[$(\'#'.$changename.'\').val()]== undefined){
			$(\'#'.$select.'\').val("");
		}else{

			$(\'#'.$select.'\').val(cng_name);

		}
	});';

	return $str;
}
function extract_substr($str,$pos,$len){

	$str = trim(substr($str, $pos, $len));
	return $str;
}




/*
ATTENZIONE:
1. verificare i kb dell'immagine originale (meglio sotto i 1000 kb) altrimenti � facile che php va hout memory
2. non verifica l'orientamento dell'immagine, quindi devi verificare tu che l'immagine sia diritta
3. non cambia nome all'immagine e quindi se in destinazione esistente, sovrascive, per non sovrascivere da mod la riga rename
4. funziona solo per formato jpg
*/
function riduci_carica($nome_file){
	$cart_p="da_ridurre/";//cartella di partenza
	$cart_d="ridotte/";//cartella di destinazione
	list($w_orig, $h_orig) = getimagesize($cart_p.$nome_file);//leggo larghezza e altezza dell immagine originale
	if($w_orig == 0 || $h_orig ==0){//se rileva 0 l'immagine non � trattabile
		echo "immagnine non compatibile <br />";
	}else{
		$max_w = 640;//definisco le misure massime a cui voglio ridurre
		$max_h = 480;
		$ratio = @min($max_w/$w_orig,$max_h/$h_orig);//cerco il rapporto minimo
		if($ratio < 1 ){
			$w_rid = (int)($w_orig*$ratio); //calcolo le dimensioni a cui ridurre
			$h_rid  =(int)($h_orig*$ratio);
			$tn = imagecreatetruecolor($w_rid, $h_rid);
			$image = imagecreatefromjpeg($cart_p.$nome_file);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $w_rid, $h_rid , $w_orig, $h_orig);
			imagejpeg($tn, $cart_d.$nome_file, 90);
		}
		echo "trattato: $nome_file<br>";
		@rename($cart_p.$nome_file, $cart_d.$nome_file);// sposta nella cartella di destinazione (attenzione sovrascrive se esiste)
		//per svuotare la cartella di partenza in automatico dei file trattat (altrimenti commentare la riga)
		@unlink($cart_p.$nome_file);
	}
}//fine function riduci_ricarica
//**************************************
/*
$imm_da_spost=array_merge(glob("da_ridurre/*.jpg"),glob("da_ridurre/*.JPG"));//legge i file da cartella di partenza
foreach($imm_da_spost as $immagine){
	$immagine=basename($immagine);
	riduci_carica($immagine);
}
echo "fatto";

/*
http://www.php.net/imagecreatefromjpeg
o
http://www.php.net/imagecreatefrompng
o
http://www.php.net/imagecreatefromgif

e via dicendo, per aprire il file immagine

dopo di che utilizzi
http://www.php.net/imagejpeg
*/











function GET_ControllerBankCode($str_Number){
    $a_ChkRow = array();

    $a_BankRow[] = array(0, 9, 4, 6, 8, 2, 7, 1, 3, 5, 0);
    $a_BankRow[] = array(9, 4, 6, 8, 2, 7, 1, 3, 5, 0, 9);
    $a_BankRow[] = array(4, 6, 8, 2, 7, 1, 3, 5, 0, 9, 8);
    $a_BankRow[] = array(6, 8, 2, 7, 1, 3, 5, 0, 9, 4, 7);
    $a_BankRow[] = array(8, 2, 7, 1, 3, 5, 0, 9, 4, 6, 6);
    $a_BankRow[] = array(2, 7, 1, 3, 5, 0, 9, 4, 6, 8, 5);
    $a_BankRow[] = array(7, 1, 3, 5, 0, 9, 4, 6, 8, 2, 4);
    $a_BankRow[] = array(1, 3, 5, 0, 9, 4, 6, 8, 2, 7, 3);
    $a_BankRow[] = array(3, 5, 0, 9, 4, 6, 8, 2, 7, 1, 2);
    $a_BankRow[] = array(5, 0, 9, 4, 6, 8, 2, 7, 1, 3, 1);

    for($i=0; $i<=9; $i++){
        foreach ($a_BankRow[$i] as $key => $value){
            $a_ChkRow[$i][$key] = $value;
        }
    }

    $n_Pos = 0;
    for($n_Count=1; $n_Count<=strlen($str_Number); $n_Count++){
        $n_TmpPos = $n_Count-1;
        $n_Num = substr($str_Number,$n_TmpPos,1);
        $n_Pos = $a_ChkRow[$n_Pos][$n_Num];
    }

    return $a_ChkRow[$n_Pos][10];

}



function CreatePagination($numberforpage, $totalnumber, $currentpage,$href,$legend){


	$TotPage = ($totalnumber<$numberforpage) ? 0 : number_format(($totalnumber / $numberforpage),0,".","");
    if($totalnumber>($TotPage*$numberforpage)) $TotPage++;



	$strPagination = '
              <div class="row-fluid">
                <div class="table_label_H col-sm-12" style="height:8rem; position:relative">
			  		<ul class="pagination pagination-sm">';


	$strPagination .= '<li';
	if($currentpage<=1) {
        $currentpage = 1;
	    $strPagination .= ' class="disabled"';
        $strPagination .= '><a href="#">&laquo;</a></li>';
        $an = 1;
        $fn = ($totalnumber<$numberforpage) ? $totalnumber : $numberforpage;

    }else {
        $an = ($currentpage-1)*$numberforpage+1;
        $fn = ($totalnumber<($numberforpage*$currentpage)) ? $totalnumber : $numberforpage*$currentpage;
        $strPagination .= '><a href="'.$href.'&page=1">&laquo;</a></li>';
    }




	$n_ResB = $currentpage-2;
    $n_ResF = $currentpage+2;


	$startpage = (($n_ResB)>0) ? $n_ResB : 1;
	$endpage = (($n_ResB)>0) ? $n_ResF : 5;

	for($i=$startpage;$i<=$endpage;$i++){

		$strPagination .= '<li';
		if($currentpage==$i) $strPagination .= ' class="active"';
		if($i>$TotPage) $strPagination .= ' class="disabled" ><a href="#">'.$i.'</a></li>';
		else $strPagination .= '><a href="'.$href.'&page='.$i.'">'.$i.'</a></li>';
	}



	$n_TotPage = ceil($totalnumber/$numberforpage);

	$str_SelectPage = '';

    if($n_TotPage > 5){
        $str_SelectPage .= '<select onChange = "window.location.href=\''.$href.'&page=\'+this.value" name="n_Page" id="n_Page" style="font-size:1rem; color:#000; width:5rem">';
        $str_SelectPage .= '<option></option>';

        for($i=6; $i<=$n_TotPage; $i++){
            $str_SelectPage .= '<option value="'. $i .'"';
            if($currentpage==$i) $str_SelectPage .= ' SELECTED ';

            $str_SelectPage .= '>'. $i. '</option>';
        }


        $str_SelectPage .= '</select>';
    }




	$strPagination .= '<li';
	if($currentpage==$TotPage) {
	    $strPagination .= ' class="disabled"';
        $strPagination .= '><a href="#">&raquo;</a></li>';
    } else {
        $strPagination .= '><a href="'.$href.'&page='.$TotPage.'">&raquo;</a></li>';
    }
	$strPagination .= '
			  </ul>
			' . $legend. '
			</div>
				<div class="totnav">
					'.$an.'-'.$fn.' di '.$totalnumber.' '.$str_SelectPage.'
			  	</div>

		</div>';

	return $strPagination;

}


function ChkButton($a, $b, $t){

	$str = (in_array($b, $a)) ? $t : "&nbsp;";
	return $str;

}

function ChkCheckButton($v){
	$str = ($v==1) ? "checked" : "";
	return $str;

}

/**CONTROLLO COMUNE E ANNO PER UTENTE
 *
 * @param string $city
 * @param int $year
 * @param int $ruletype
 *
 * @return array $return
 */
function chkCityYear($city, $year, $ruletype){
    //CONTROLLO COMUNE/ANNO
    $return['city'] = array_search($city, array_column($_SESSION['CityArray'][MENU_ID], 'CityId')) !== false;
    $return['year'] = $return['city'] && in_array($year, $_SESSION['YearArray'][MENU_ID][$city]);
    $return['ruletype'] = in_array($ruletype, $_SESSION['RuleTypeArray'][$city]);
    return $return;
}

/**
 * @param $a_FifthField ( City, Service, Id, Year )
 * @return string
 */
function SetFifthField($a_FifthField, $rate = 1)
{
    //coattiva
    // 3 -> comune,     2 -> sorgente
    // 2 -> sorgente    2 -> rata
    // 2 -> rata        1 -> tipo pag
    // 2 -> anno cron   1 -> campo pag
    // 7 -> id cron     10 -> id

	$FifthField = "";

	//ID TABELLA 2 CIFRE
	//TODO Creare tabella TableType o qualcosa del genere (A meno che non si possa utilizzare qualche tabella del mysql gia esistente)
	for($i=0; $i< 2-strlen($a_FifthField['Table']) ;$i++)
		$FifthField .= "0";
	$FifthField .= $a_FifthField['Table'];

	//NUMERO RATA 2 CIFRE
	for($i=0; $i< 2-strlen($rate) ;$i++)
		$FifthField .= "0";
	$FifthField .= $rate;

	//TIPO PAGAMENTO 1 CIFRA 0(Ridotto), 1(Normale), 2(Maggiorato)
	//TODO Creare tabella PaymentType o qualcosa del genere
	$FifthField .= $a_FifthField['PaymentType'];

	//TIPO DI DOCUMENTO per cui è emesso il quinto campo (0 atto giudizioario notificato (verbale), 3 sollecito)
	//TODO gestire l'importazione pagamento da quinto campo e le pagine dei pagamenti per mostrare il dato
	if (isset($a_FifthField['DocumentType']))// AND strlen("".$a_FifthField['DocumentType']) == 1
	    $FifthField .= $a_FifthField['DocumentType'];
	else
	    $FifthField .= "0";

	//ATTO 10 CIFRE
	for($i=0; $i< 10-strlen($a_FifthField['Id']) ;$i++)
		$FifthField .= "0";
	$FifthField .= $a_FifthField['Id'];

	//COD POSTA 2 CIFRE
	$cod_posta = fmod($FifthField,93);
	for($i=0; $i< 2-strlen($cod_posta) ;$i++)
		$FifthField .= "0";

	$FifthField.= $cod_posta;

	return $FifthField;
}

function SetFifthFieldFee($fee){
	$FifthFieldFee = "";

	$a_fee = explode(".",number_format($fee,2,".",""));
	for($i=0;$i<(8-strlen($a_fee[0]));$i++)
		$FifthFieldFee.="0";
	$FifthFieldFee.=$a_fee[0];
	$FifthFieldFee.="+".$a_fee[1];

	return $FifthFieldFee;
}


function ChkAddressInsertDB($StreetType, $StreetName, $StreetNumber){



	$a_NoNumber = array("SNC", "SCN", "SN", "snc", "Snc", "/SNC", "S.N.");

	$StreetNumber = str_replace($a_NoNumber,"SNC",$StreetNumber);
	if(trim($StreetNumber)==".") $StreetNumber = "";

	//$F = preg_match('/[0-9]/', $myString);

	if(trim($StreetNumber)==""){

		$a_StreetName = explode(" ",$StreetName);
		if(is_numeric(substr($a_StreetName[0],0,1))){
			$StreetName = "";
			for($i=1;$i<count($a_StreetName);$i++){
				$StreetName .= $a_StreetName[$i]." ";

			}
			$StreetName .= $a_StreetName[0];
		}

	} else {
		$StreetNumber = str_replace("-","/",$StreetNumber);
		switch ($StreetType)
		{
			case "V":
			case "V.":
			case "VIA": $StreetType = "VIA"; break;

			case "LOC":
			case "LOC.":
			case "LCLTA": $StreetType = "LOC."; break;

			case "PLE":
			case "P.ZLE": $StreetType = "P.LE"; break;

			case "PZZA":
			case "P.ZZA": $StreetType = "P.ZA"; break;

			case "FRAZ.":
			case "FRZIN":
			case "FRAZ": $StreetType = "FRAZ"; break;

			case "C.SO":
			case "CSO":
			case "CORSO": $StreetType = "CORSO"; break;

			case "BORGO": $StreetType = "BORGO"; break;

			case "CDA": $StreetType = "C.DA"; break;

			case "VLE": $StreetType = "VIALE"; break;
			case "STR": $StreetType = "STR."; break;

		}

	}
	$Street = trim(trim($StreetType)." ".trim($StreetName)." ".trim($StreetNumber));
	return $Street;




}
function DateDiff($type, $start, $end)
{
    switch ($type)
    {
        case "Y" : $type = 365;
            break;
        case "M" : $type = (365 / 12);
            break;
        case "W" : $type = (365 / 52);
            break;
        case "D" : $type = 1;
            break;
    }
    $a_StartDate = explode("-", $start);
    $a_EndDate = explode("-", $end);

    $n_Diff = mktime(12, 0, 0, $a_EndDate[1], $a_EndDate[2], $a_EndDate[0]) - mktime(12, 0, 0, $a_StartDate[1], $a_StartDate[2], $a_StartDate[0]);
    $n_Diff = floor(($n_Diff / 60 / 60 / 24) / $type);
    return $n_Diff;
}




//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////
////
////    PAYMENT FUNCTION
////
////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


function separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, $PaymentDirect, $Amount, $FineId, $CityId, $Year, $PaymentDate = null, $ReminderDate = null, $cls_db = null){
    if(empty($ReminderDate)){
        return FinePaymentSpecificationType($FinePaymentSpecificationType, $PaymentDocumentId, $PaymentDirect, $Amount, $FineId, $Year, $cls_db);
    } else {
        return ReminderPaymentSpecificationType($FinePaymentSpecificationType, $PaymentDirect, $Amount, $FineId, $CityId, $PaymentDate, $cls_db);
    }
}

function ReminderPaymentSpecificationType($FinePaymentSpecificationType, $PaymentDirect, $Amount, $FineId, $CityId, $PaymentDate, $cls_db = null){
    if(!isset($cls_db)) $cls_db= new CLS_DB();
    
    $cls_pagamenti = new cls_pagamenti($FineId, $CityId, $PaymentDate, $cls_db);
    
    $CustomerFee                = $cls_pagamenti->getCustomerFee();
    $NotificationFee            = $cls_pagamenti->getLastReminderTotalNotificationFee() + $cls_pagamenti->getLastReminderNotificationFee()  + $cls_pagamenti->getOtherFee() - $cls_pagamenti->getFineResearchFee() - $CustomerFee;
    $ResearchFee                = $cls_pagamenti->getFineResearchFee();
    $CanFee                     = $cls_pagamenti->getCanFee();
    $CadFee                     = $cls_pagamenti->getCadFee();
    $Fee                        = $cls_pagamenti->getFineMaxFee();
    $PercentualFee              = $cls_pagamenti->getSurcharge();
    $AdditionalFee              = $cls_pagamenti->getAdditionalFee();
    $CurrentFee                 = $cls_pagamenti->getFineFee();
    $CurrentMaxFee              = $cls_pagamenti->getFineMaxFee();
    
    if ($PaymentDirect) {
        $CustomerFee                = 0.00;
        $NotificationFee            = 0.00;
        $ResearchFee                = 0.00;
        $CanFee                     = 0.00;
        $CadFee                     = 0.00;
        $PercentualFee              = 0.00;
        $Fee                        = $Amount;
    } else {
        if ($FinePaymentSpecificationType == 1) {
            if ($Amount < $Fee) {
                $Fee                        = $Amount;
                $ResearchFee                = 0.00;
                $NotificationFee            = 0.00;
                $PercentualFee              = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            }  else if (($Amount - $Fee - $ResearchFee) < 0) {
                $ResearchFee                = number_format(($Amount - $Fee), 2, '.', '');
                $CustomerFee                = 0.00;
                $NotificationFee            = 0.00;
                $PercentualFee              = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerFee) < 0) {
                $CustomerFee                = $Amount - $Fee - $ResearchFee;
                $NotificationFee            = 0.00;
                $PercentualFee              = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee) < 0) {
                $NotificationFee            = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee), 2, '.', '');
                $PercentualFee              = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            //ipotizzando sia dopo notifiche (caso 1), l'if va messo qui
            } else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee) < 0) {
                $PercentualFee              = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee), 2, '.', '');
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee), 2, '.', '');
                $CadFee                     = 0.00;
            }  else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee), 2, '.', '');
            } else {
                $Fee                        = number_format(($Amount - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee - $CadFee), 2, '.', '');
            }
        } else if ($FinePaymentSpecificationType == 2) {
            if ($Amount < $NotificationFee) {
                $NotificationFee = $Amount;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee) < 0) {
                $CustomerFee                = $Amount - $NotificationFee;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $NotificationFee -$CustomerFee), 2, '.', '');
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $NotificationFee -$CustomerFee - $CanFee), 2, '.', '');
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee) < 0) {
                $ResearchFee                = number_format(($Amount - $NotificationFee - $CustomerFee), 2, '.', '');
                $Fee                        = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee - $Fee) < 0) {
                $Fee                        = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee), 2, '.', '');
                $PercentualFee              = 0.00;
            } else {
                $PercentualFee              = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee - $Fee), 2, '.', '');
            }
        } else {
            if ($Amount < $ResearchFee) {
                $ResearchFee                = $Amount;
                $Fee                        = 0.00;
                $NotificationFee            = 0.00;
                $PercentualFee              = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $NotificationFee) < 0) {
                $NotificationFee            = number_format(($Amount - $ResearchFee), 2, '.', '');
                $Fee                        = 0.00;
                $PercentualFee              = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $NotificationFee - $Fee) < 0) {
                $Fee                        = $Amount - $ResearchFee - $NotificationFee;
                $PercentualFee              = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if(($Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee) < 0){
                $PercentualFee              = $Amount - $ResearchFee - $NotificationFee - $Fee;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee - $CustomerFee) < 0) {
                $CustomerFee                = $Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee - $CustomerFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee - $CustomerFee), 2, '.', '');
                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee - $CustomerFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $ResearchFee - $NotificationFee - $Fee - $PercentualFee - $CustomerFee - $CanFee), 2, '.', '');
            } else {
                $Fee                        = number_format(($Amount - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee), 2, '.', '');
            }
        }
    }
    
    return array(
        "CanFee" => chk_DoubleNumber($CanFee),
        "CadFee" => chk_DoubleNumber($CadFee),
        "CustomerFee" => chk_DoubleNumber($CustomerFee),
        "NotificationFee" => chk_DoubleNumber($NotificationFee),
        "ResearchFee" => chk_DoubleNumber($ResearchFee),
        "Fee" => chk_DoubleNumber($Fee),
        "ReducedPayment" => chk_DoubleNumber($cls_pagamenti->isReduced()),
        "AdditionalFee" => chk_DoubleNumber($AdditionalFee),
        "PercentualFee" => chk_DoubleNumber($PercentualFee),
        "CurrentFee" => chk_DoubleNumber($CurrentFee),
        "CurrentMaxFee" => chk_DoubleNumber($CurrentMaxFee),
    );
}

/**
 *
 * @param int $FinePaymentSpecificationType preso dalle configurazioni dell'ente, serve per dire come ripartire l'importo pagato tra le varie voci di "spesa" quando è inferiore al totale dovuto
 * 1 riempie prima la componente di sanzione
 * 2 riempie prima la componente di Notifica
 * 3 riempie prima la componente di ricerca
 * @param int $PaymentDocumentId
 * indica se la sanzione che si vuole scorporare è per l'ammontare ridotto, minimo o maggiorato
 * @param int $PaymentDirect
 * indica se è pagamento diretto assegna tutto l'importo pagato alla componente sanzione
 * @param float $Amount
 * @param int $FineId
 * @param int $Year
 *
 * @return array $a_Fee
 */
function FinePaymentSpecificationType($FinePaymentSpecificationType, $PaymentDocumentId, $PaymentDirect, $Amount, $FineId, $Year, $cls_db = null){
    //06/05/2022 dato che questa funzione è usata da cls/PagoPAER/PagoPAERService.php in cui viene istanziata la classe CLS_DB in versione xml,
    //viene passata attraverso il parametro $cls_db, altrimenti per retrocompatibilità si usa quella che ha sempre istanziato
    
    if(isset($cls_db)) $rs_Func = $cls_db;
    else $rs_Func= new CLS_DB();

    $rs_Row = $rs_Func->SelectQuery("
        SELECT
        FA.Fee,
        FA.MaxFee,

        ArT.ReducedPayment,

        FH.NotificationTypeId,
        FH.CustomerFee,
        FH.NotificationFee,
        FH.ResearchFee,
        FH.CanFee,
        FH.CadFee,
        FH.NotifierFee,
        FH.OtherFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM Fine F 
        JOIN FineArticle FA ON F.Id = FA.FineId 
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=" . $FineId . " AND (NotificationTypeId=6 OR NotificationTypeId=15) AND ArT.Year=" . $Year);

    $r_Row = mysqli_fetch_array($rs_Row);

    //inizializzazione di tutte le voci componenti la sanzione
    $CustomerFee                = $r_Row['CustomerFee'];
    $NotificationFee            = $r_Row['NotificationFee'] + $r_Row['NotifierFee'] + $r_Row['OtherFee']-$CustomerFee;
    $ResearchFee                = $r_Row['ResearchFee'];


    $CanFee                     = $r_Row['CanFee'];
    $CadFee                     = $r_Row['CadFee'];
    $PercentualFee              = 0.00;

    if ($PaymentDocumentId == 0) {
        $Fee = round(($r_Row['Fee'] * FINE_PARTIAL), 2);
    } else if ($PaymentDocumentId == 1) {
        $Fee = $r_Row['Fee'];
    } else {
        $Fee = $r_Row['MaxFee']/2;
    }

    $AdditionalFee = $NotificationFee + $ResearchFee + $CustomerFee + $CanFee + $CadFee;
    $CurrentFee = $r_Row['Fee'];
    $CurrentMaxFee = $r_Row['MaxFee'];

    //in base alla tipologia di ripartizione dell'importo pagato tra le spese vengono azzerate le componenti non coperte dalla cifra pagata
    if ($PaymentDirect) {
        $CustomerFee                = 0.00;
        $NotificationFee            = 0.00;
        $ResearchFee                = 0.00;
        $CanFee                     = 0.00;
        $CadFee                     = 0.00;
        $Fee                        = $Amount;
    } else {
        if ($FinePaymentSpecificationType == 1) {
            if ($Amount < $Fee) {
                $Fee                        = $Amount;
                $NotificationFee            = 0.00;
                $CustomerFee    = 0.00;
                $ResearchFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee) < 0) {
                $ResearchFee                = number_format(($Amount - $Fee), 2, '.', '');
                $CustomerFee    = 0.00;
                $NotificationFee            = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerFee) < 0) {
                $CustomerFee    = $Amount - $Fee - $ResearchFee;
                $NotificationFee            = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee) < 0) {
                $NotificationFee            = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee), 2, '.', '');
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            }  else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee), 2, '.', '');
                $CadFee                     = 0.00;
            }  else if (($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $Fee - $ResearchFee - $CustomerFee - $NotificationFee - $CanFee), 2, '.', '');
            } else {
                $Fee = number_format(($Amount - $ResearchFee - $CustomerFee - $NotificationFee - $CanFee - $CadFee), 2, '.', '');
            }
        } else if ($FinePaymentSpecificationType == 2) {

            if ($Amount < $NotificationFee) {
                $NotificationFee = $Amount;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CustomerFee    = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $NotificationFee - $CustomerFee) < 0) {
                $CustomerFee    = $Amount - $NotificationFee;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $NotificationFee -$CustomerFee), 2, '.', '');
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $NotificationFee -$CustomerFee - $CanFee), 2, '.', '');
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee) < 0) {
                $ResearchFee = number_format(($Amount - $NotificationFee - $CustomerFee), 2, '.', '');
                $Fee = 0.00;
            } else {
                $Fee = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee), 2, '.', '');
            }
        } else {
            if ($Amount < $ResearchFee) {
                $ResearchFee                = $Amount;
                $Fee                        = 0.00;
                $NotificationFee            = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $NotificationFee) < 0) {
                $NotificationFee            = number_format(($Amount - $ResearchFee), 2, '.', '');
                $Fee                        = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee) < 0) {
                $Fee = $Amount - $ResearchFee - $NotificationFee;
                $CustomerFee    = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerFee) < 0) {
                $CustomerFee = $Amount - $ResearchFee - $Fee - $NotificationFee;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerFee - $CanFee) < 0) {

                $CanFee = number_format(($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerFee), 2, '.', '');

                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerFee - $CanFee - $CadFee) < 0) {
                $CadFee = number_format(($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerFee - $CanFee), 2, '.', '');
            } else {
                $Fee = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee), 2, '.', '');
            }
        }
    }


    $a_Fee = array(
        "CanFee" => chk_DoubleNumber($CanFee),
        "CadFee" => chk_DoubleNumber($CadFee),
        "CustomerFee" => chk_DoubleNumber($CustomerFee),
        "NotificationFee" => chk_DoubleNumber($NotificationFee),
        "ResearchFee" => chk_DoubleNumber($ResearchFee),
        "Fee" => chk_DoubleNumber($Fee),
        "ReducedPayment" => chk_DoubleNumber($r_Row['ReducedPayment']),
        "AdditionalFee" => chk_DoubleNumber($AdditionalFee),
        "PercentualFee" => chk_DoubleNumber($PercentualFee),
        "CurrentFee" => chk_DoubleNumber($CurrentFee),
        "CurrentMaxFee" => chk_DoubleNumber($CurrentMaxFee),
    );
    return $a_Fee;
}
 
//Scorporo rateizzazioni
//TODO come struttura va copiata quella del sollecito e inserito nei vari casi InterestFee
function InstallmentPaymentSpecificationType($r_PaymentRateNumber, $Amount, $FineId, $CityId, $RequestDate, $FinePaymentSpecificationType, $installmentSpecificationType = 0, $cls_db = null){
    if(!isset($cls_db)) $cls_db= new CLS_DB();
    
    $cls_pagamenti = new cls_pagamenti($FineId, $CityId, $RequestDate, $cls_db);
    
    $TotaleDovutoAtto = $cls_pagamenti->getFee();
    $QuotaCapitale = $r_PaymentRateNumber['ShareAmount'];
    
    $Fee = $QuotaCapitale;
    $NotificationFee = 0.00;
    $ResearchFee = 0.00;
    $PercentualFee = 0.00;
    $CanFee = 0.00;
    $CadFee = 0.00;
    $CustomerFee = 0.00;
    $InterestFee = $r_PaymentRateNumber['InterestsAmount'];
    
    //17/01/2024 per il momento come richiesto tramite mail dell'11/01/24 viene messa tutta la quota capitale sulla sanzione
    /*18/01/2024 Riccardo dice: Davide buongiorno, la soluzione che ha proposto manca di un pezzetto e quindi non si adatta a tutti i comuni
    Se scorpori in base al numero di rate oltre alla quota capitale/sanzione, anche l'importo delle spese di notifica e delle spese di ricerca 
    invece nessuno ci può venire a dire niente, neppure Formigine, perchè sarà tutto proporzionale in base al numero di rate ed alla fine facendo
    la somma dei vari pagamenti rateali come scorporati risulterà che tizio ha pagato la sanzione, le spese di notifica e di ricerca. */
    switch($installmentSpecificationType){
        case 0:{    //Caso quota capitale tutta su sanzione
            if ($Amount < $QuotaCapitale) {
                $Fee                        = $Amount;
                $InterestFee                = 0.00;
                $ResearchFee                = 0.00;
                $NotificationFee            = 0.00;
                $PercentualFee              = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            } else if (($Amount - $QuotaCapitale - $InterestFee) < 0) {
                $InterestFee                = number_format(($Amount - $QuotaCapitale), 2, '.', '');
                $ResearchFee                = 0.00;
                $NotificationFee            = 0.00;
                $PercentualFee              = 0.00;
                $CustomerFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
                $PercentualFee              = 0.00;
            } else {
                $Fee = number_format(($Amount - $InterestFee), 2, '.', '');
            }
            
            break;
        }
        case 1:{ //Caso quota capitale che copre proporzionalmente tutte le voci di volta in volta
            $CustomerFee                = $cls_pagamenti->getCustomerFee();
            $ResearchFee                = $cls_pagamenti->getFineResearchFee();
            $CanFee                     = $cls_pagamenti->getCanFee();
            $CadFee                     = $cls_pagamenti->getCadFee();
            $Fee                        = $cls_pagamenti->getBaseFee();
            $PercentualFee              = $cls_pagamenti->getSurcharge();
            $NotificationFee            = $cls_pagamenti->getCurrentNotificationFee();
            
            $CustomerFee = number_format(($QuotaCapitale * $CustomerFee) / $TotaleDovutoAtto, 2, '.', '');
            $ResearchFee = number_format(($QuotaCapitale * $ResearchFee) / $TotaleDovutoAtto, 2, '.', '');
            $CanFee = number_format(($QuotaCapitale * $CanFee) / $TotaleDovutoAtto, 2, '.', '');
            $CadFee = number_format(($QuotaCapitale * $CadFee) / $TotaleDovutoAtto, 2, '.', '');
            $Fee = number_format(($QuotaCapitale * $Fee) / $TotaleDovutoAtto, 2, '.', '');
            $PercentualFee = number_format(($QuotaCapitale * $PercentualFee) / $TotaleDovutoAtto, 2, '.', '');
            $NotificationFee = number_format(($QuotaCapitale * $NotificationFee) / $TotaleDovutoAtto, 2, '.', '');
            
            if ($FinePaymentSpecificationType == 1) {
                //Aggiungo lo scarto alla sanzione
                $Fee += number_format($QuotaCapitale - array_sum(array($Fee,$ResearchFee,$CustomerFee,$NotificationFee,$PercentualFee,$CanFee,$CadFee)), 2 , '.', '');
                
                if ($Amount < $Fee) {
                    $Fee                        = $Amount;
                    $InterestFee                = 0.00;
                    $ResearchFee                = 0.00;
                    $NotificationFee            = 0.00;
                    $PercentualFee              = 0.00;
                    $CustomerFee                = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $PercentualFee              = 0.00;
                } else if (($Amount - $Fee - $InterestFee) < 0) {
                    $InterestFee                = number_format(($Amount - $Fee), 2, '.', '');
                    $ResearchFee                = 0.00;
                    $CustomerFee                = 0.00;
                    $NotificationFee            = 0.00;
                    $PercentualFee              = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $PercentualFee              = 0.00;
                } else if (($Amount - $Fee - $InterestFee - $ResearchFee) < 0) {
                    $ResearchFee                = number_format(($Amount - $InterestFee - $Fee), 2, '.', '');
                    $CustomerFee                = 0.00;
                    $NotificationFee            = 0.00;
                    $PercentualFee              = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $PercentualFee              = 0.00;
                } else if (($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee) < 0) {
                    $CustomerFee                = number_format($Amount - $Fee - $InterestFee - $ResearchFee, 2, '.', '');
                    $NotificationFee            = 0.00;
                    $PercentualFee              = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                } else if (($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee) < 0) {
                    $NotificationFee            = number_format(($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee), 2, '.', '');
                    $PercentualFee              = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                } else if (($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee) < 0) {
                    $PercentualFee              = number_format(($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee), 2, '.', '');
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                } else if (($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee) < 0) {
                    $CanFee                     = number_format(($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee), 2, '.', '');
                    $CadFee                     = 0.00;
                }  else if (($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee - $CadFee) < 0) {
                    $CadFee                     = number_format(($Amount - $Fee - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee), 2, '.', '');
                } else {
                    $Fee                        = number_format(($Amount - $InterestFee - $ResearchFee - $CustomerFee - $NotificationFee - $PercentualFee - $CanFee - $CadFee), 2, '.', '');
                }
            } else if ($FinePaymentSpecificationType == 2) {
                //Aggiungo lo scarto alla notifica
                $NotificationFee += number_format($QuotaCapitale - array_sum(array($Fee,$ResearchFee,$CustomerFee,$NotificationFee,$PercentualFee,$CanFee,$CadFee)), 2 , '.', '');
                
                if ($Amount < $NotificationFee) {
                    $NotificationFee            = $Amount;
                    $CustomerFee                = 0.00;
                    $ResearchFee                = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $PercentualFee              = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $NotificationFee - $CustomerFee) < 0) {
                    $CustomerFee                = $Amount - $NotificationFee;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $ResearchFee                = 0.00;
                    $PercentualFee              = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee) < 0) {
                    $CanFee                     = number_format(($Amount - $NotificationFee -$CustomerFee), 2, '.', '');
                    $CadFee                     = 0.00;
                    $ResearchFee                = 0.00;
                    $PercentualFee              = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee) < 0) {
                    $CadFee                     = number_format(($Amount - $NotificationFee -$CustomerFee - $CanFee), 2, '.', '');
                    $ResearchFee                = 0.00;
                    $PercentualFee              = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee) < 0) {
                    $ResearchFee                = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee), 2, '.', '');
                    $PercentualFee              = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee - $PercentualFee) < 0) {
                    $PercentualFee              = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee), 2, '.', '');
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee - $PercentualFee - $InterestFee) < 0) {
                    $InterestFee              = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee - $PercentualFee), 2, '.', '');
                    $Fee                        = 0.00;
                } else {
                    $Fee              = number_format(($Amount - $NotificationFee - $CustomerFee - $CanFee - $CadFee - $ResearchFee - $PercentualFee - $InterestFee), 2, '.', '');
                }
            } else {
                //Aggiungo lo scarto alla ricerca
                $ResearchFee += number_format($QuotaCapitale - array_sum(array($Fee,$ResearchFee,$CustomerFee,$NotificationFee,$PercentualFee,$CanFee,$CadFee)), 2 , '.', '');
                
                if ($Amount < $ResearchFee) {
                    $ResearchFee                = $Amount;
                    $NotificationFee            = 0.00;
                    $PercentualFee              = 0.00;
                    $CustomerFee                = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $ResearchFee - $NotificationFee) < 0) {
                    $NotificationFee            = number_format(($Amount - $ResearchFee), 2, '.', '');
                    $PercentualFee              = 0.00;
                    $CustomerFee                = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if(($Amount - $ResearchFee - $NotificationFee - $PercentualFee) < 0){
                    $PercentualFee              = number_format($Amount - $ResearchFee - $NotificationFee, 2, '.', '');
                    $CustomerFee                = 0.00;
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee) < 0) {
                    $CustomerFee                = number_format($Amount - $ResearchFee - $NotificationFee - $PercentualFee, 2, '.', '');
                    $CanFee                     = 0.00;
                    $CadFee                     = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee) < 0) {
                    $CanFee                     = number_format(($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee), 2, '.', '');
                    $CadFee                     = 0.00;
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee - $CadFee) < 0) {
                    $CadFee                     = number_format(($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee), 2, '.', '');
                    $InterestFee                = 0.00;
                    $Fee                        = 0.00;
                } else if (($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee - $CadFee - $InterestFee) < 0) {
                    $InterestFee                = number_format(($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee - $CadFee), 2, '.', '');
                    $Fee                        = 0.00;
                } else {
                    $Fee                        = number_format(($Amount - $ResearchFee - $NotificationFee - $PercentualFee - $CustomerFee - $CanFee - $CadFee - $InterestFee), 2, '.', '');
                }
            }
        } 
    }
    
    return array(
        "Amount" => chk_DoubleNumber($Amount),
        "Fee" => chk_DoubleNumber($Fee),
        "NotificationFee" => chk_DoubleNumber($NotificationFee),
        "ResearchFee" => chk_DoubleNumber($ResearchFee),
        "PercentualFee" => chk_DoubleNumber($PercentualFee),
        "CanFee" => chk_DoubleNumber($CanFee),
        "CadFee" => chk_DoubleNumber($CadFee),
        "CustomerFee" => chk_DoubleNumber($CustomerFee),
        "InterestFee" => chk_DoubleNumber($InterestFee),
    );
}





//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////
////
////    MCTC function
////
////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

function Return_Array_GET_Trespasser_With_Plate($a_Resp){

        $a_Res = array();


        if(isset($a_Resp ['dettaglioAutoveicoloComproprietariResponse']['errore'])){
            $a_Res['ErrorCode'] = $a_Resp ['dettaglioAutoveicoloComproprietariResponse']['errore']['codiceErrore'];
            $a_Res['ErrorDescription'] =  $a_Resp ['dettaglioAutoveicoloComproprietariResponse']['errore']['descrizioneErrore'];

        }else if(isset($a_Resp ['dettaglioMotoveicoloComproprietariResponse']['errore'])){
            $a_Res['ErrorCode'] = $a_Resp ['dettaglioMotoveicoloComproprietariResponse']['errore']['codiceErrore'];
            $a_Res['ErrorDescription'] =  $a_Resp ['dettaglioMotoveicoloComproprietariResponse']['errore']['descrizioneErrore'];

        }else if(isset($a_Resp ['dettaglioCiclomotoreComproprietariResponse']['errore'])){
            $a_Res['ErrorCode'] = $a_Resp ['dettaglioCiclomotoreComproprietariResponse']['errore']['codiceErrore'];
            $a_Res['ErrorDescription'] =  $a_Resp ['dettaglioCiclomotoreComproprietariResponse']['errore']['descrizioneErrore'];
        }else if(isset($a_Resp ['dettaglioRimorchioComproprietariResponse']['errore'])){
            $a_Res['ErrorCode'] = $a_Resp ['dettaglioRimorchioComproprietariResponse']['errore']['codiceErrore'];
            $a_Res['ErrorDescription'] =  $a_Resp ['dettaglioRimorchioComproprietariResponse']['errore']['descrizioneErrore'];
        } else{
            if(isset($a_Resp ['dettaglioAutoveicoloComproprietariResponse']["dettaglioVeicoloComproprietariOutput"])){
                $str_ArrayIndex = "dettaglioAutoveicoloComproprietari";
                $str_ArrayIndex2 = "dettaglioVeicoloComproprietari";
            } else if(isset($a_Resp ['dettaglioMotoveicoloComproprietariResponse']["dettaglioVeicoloComproprietariOutput"])){
                $str_ArrayIndex = "dettaglioMotoveicoloComproprietari";
                $str_ArrayIndex2 = "dettaglioVeicoloComproprietari";
            }  else if(isset($a_Resp ['dettaglioRimorchioComproprietariResponse']["dettaglioRimorchioComproprietariOutput"])){
                $str_ArrayIndex = "dettaglioRimorchioComproprietari";
                $str_ArrayIndex2 = "dettaglioRimorchioComproprietari";
            }  else if(isset($a_Resp ['dettaglioCiclomotoreComproprietariResponse']["dettaglioCiclomotoreComproprietariOutput"])){
                $str_ArrayIndex = "dettaglioCiclomotoreComproprietari";
                $str_ArrayIndex2 = "dettaglioCiclomotoreComproprietari";
            }


            foreach($a_Resp [$str_ArrayIndex.'Response'][$str_ArrayIndex2.'Output'] AS $key1 => $val1) {
                foreach ($val1 AS $key2 => $val2)
                    switch($key2){
                        case 'datiVeicolo':
                        case 'datiUltimaRevisione':
                        case 'datiCartaCircolazione':
                        case 'datiAggiuntiviTecnici':
                            foreach ($val2 AS $k => $v) $a_Res[$k] = $v;
                            break;
                        case 'datiProprietario':
                            if(isset($val2['personaFisica'])) {
                                foreach ($val2['personaFisica'] AS $k_prop => $v_prop){
                                    foreach ($v_prop AS $k => $v){

                                        if($k=="luogoNascita"){
                                            if(isset($v_prop[$k]["luogoItaliano"])){
                                                foreach ($v_prop[$k]["luogoItaliano"] AS $k_born => $v_born) $a_Res[$k_born] = $v_born;
                                            }else if(isset($v_prop[$k]["luogoEstero"])){
                                                foreach ($v_prop[$k]["luogoEstero"] AS  $k_born => $v_born) $a_Res[$k_born] = $v_born;

                                            }
                                        }
                                        else $a_Res[$k] = $v;
                                    }
                                }
                            }else if(isset($val2['personaGiuridica'])){
                                foreach ($val2['personaGiuridica'] AS $k_prop => $v_prop){
                                    if($k_prop=="sedeItaliana"){
                                        foreach ($val2['personaGiuridica'][$k_prop] AS $k_place => $v_place) $a_Res[$k_place] = $v_place;

                                    }else $a_Res[$k_prop] = $v_prop;
                                }






                            }
                            break;
                    }
            }
        }


        return $a_Res;


}

function showDOMNode(DOMNode $domNode) {
    global $a_Fine;
    foreach ($domNode->childNodes as $node){
        if($node->nodeName=="z:row"){

            if ($node->hasAttributes()) {
                foreach ($node->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;

                    $a_Fine[$name]=$value;


                }


                break;
            }

        }
        if($node->hasChildNodes()) {
            showDOMNode($node);
        }
    }
}




//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////
////
////    Admin function
////
////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////






function getServerMemoryUsage(){

    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;

    return $memory_usage;
}



function getServerCpuUsage(){

    $load = sys_getloadavg();
    return $load[0];

}


function convertCheckBoxValue($val){

    if ($val == "on") {
        $val = 1;
    } else {
        $val = 0;
    }
    return $val;

}

function createModal(String $title, String $body, String $htmlButtons, String $type = 'info', $id = null){
    switch($type){
        case 'question':
            $color = '';
            $icon = 'fa-question-circle';
            break;
        case 'info':
            $color = 'bg-info-dark';
            $icon = 'fa-info-circle';
            break;
        case 'success':
            $color = 'bg-success-dark';
            $icon = 'fa-check-circle';
            break;
        case 'warning':
            $color = 'bg-warning-dark';
            $icon = 'fa-warning';
            break;
        case 'error':
            $color = 'bg-danger-dark';
            $icon = 'fa-times-circle';
            break;
        default:
            $color = 'bg-info-dark';
            $icon = 'fa-info-circle';
            break;
    }
    
    $html= '
        <div'.($id ? ' id="'.$id.'"' : '').' class="modal modal-center fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content" style="overflow:hidden;">
                    <div class="modal-header text-center '.$color.'">
                        <i class="fa '.$icon.' fa-fw" style="font-size: 2.5rem;"></i><strong style="vertical-align: text-bottom;">'.$title.'</strong>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
            <div class="modal-body text-center">
                '.$body.'
            </div>';
    if($htmlButtons!=null)
        $html .= '<div class="modal-footer">'.$htmlButtons.'</div>';
    $html .= '</div></div></div>';
    
    return $html;
}

function getDocumentationPath($countryId,$DocumentationTypeId,$FineId,$CityId,$Documentation){
    $path = ($countryId=='Z000') ? "doc/national" : "doc/foreign";
    if ($DocumentationTypeId == 60)
        $path .= "/dispute/". $CityId.'/'.$FineId.'/';
    else if($DocumentationTypeId == 70)
        $path = "doc/print/"; //per non dover distinguere tra nazionali ed esteri il path è unico per la stampa
    else if ($DocumentationTypeId == 1)
        $path .= "/violation/". $CityId.'/'.$FineId.'/';
    else if ($DocumentationTypeId == 36){
        $path .= "/rate/". $CityId.'/'.$FineId.'/';
        }
    else
        $path .= "/fine/". $CityId.'/'.$FineId.'/';
    return $path .$Documentation;
}

// calcola importi da usare per sapre quanto uno deve pagare null'atto in creazione
function calcolaImporti($r_ProcessingPagoPA,$rs_AdditionalArticle,$r_ArticleTariff,$r_Customer,$NotificationDate,$ZoneId,$PEC,$cityid=null,$cls_db=null){
    $a_Result = array();
    $NotificationFee = 0;
    $ChargeTotalFee = 0;
    $ResearchFee = 0;
    $n_TotPartialFee = 0;
    $n_TotFee = 0;
    $n_TotMaxFee = 0;

    //Se il destinatario è PEC  e non è rinotifica USA QUESTE
    if ($r_Customer['ManagePEC'] > 0 && $PEC != null && $PEC != '' && empty($r_ProcessingPagoPA['PreviousId'])) {
        $NotificationFee = $r_Customer['NationalPECNotificationFee'];
        $ResearchFee = $r_Customer['NationalPECResearchFee'];
    }
    else {
        if ($r_Customer['NationalTotalFee'] > 0){
            $ChargeTotalFee = $r_Customer['NationalTotalFee'];
        } else {
            if ($r_Customer['NationalNotificationFee'] > 0){
                $NotificationFee = $r_Customer['NationalNotificationFee'];
            }
            $ResearchFee = $r_Customer['NationalResearchFee'];
        }
        
        $postalCharge=getPostalCharge($cityid ?? $_SESSION['cityid'],$NotificationDate,$cls_db);
        
        if ($ChargeTotalFee > 0) {
            $ResearchFee = $ChargeTotalFee - $postalCharge['Zone' . $ZoneId];
            $NotificationFee = $postalCharge['Zone' . $ZoneId];
        } else {
            if ($NotificationFee == 0){
                $NotificationFee = $postalCharge['Zone' . $ZoneId];
            }
        }
    }
    
    $CustomerFee = $r_ProcessingPagoPA['CustomerAdditionalFee'];
    $NotificationFee += $r_ProcessingPagoPA['OwnerAdditionalFee'] + $CustomerFee;
    
    
    //Totale spese addizionali ente
    $AdditionalFee = $NotificationFee + $ResearchFee;
    
    $a_Result['AdditionalFee'] = number_format((float)$AdditionalFee, 2, '.', '');
    
    //Importi ridotti, minimi e massimi articolo principale
    $n_PartialFee = $r_ArticleTariff['ReducedPayment'] == 1
        ? $r_ProcessingPagoPA['Fee'] * FINE_PARTIAL
        : $r_ProcessingPagoPA['Fee'];
    
    $a_Result['Amounts'][] = array(
        'ReducedPartial' => number_format($n_PartialFee, 2, '.', ''),
        'ReducedTotal' => number_format($r_ProcessingPagoPA['Fee'], 2, '.', ''),
        'Partial' => number_format($r_ProcessingPagoPA['Fee'], 2, '.', ''),
        'Total' => number_format($r_ProcessingPagoPA['MaxFee'] * FINE_MAX, 2, '.', ''),
        'ViolationTypeId' => $r_ProcessingPagoPA['ViolationTypeId']
    );
    
    $n_TotFee += $r_ProcessingPagoPA['Fee'];
    $n_TotMaxFee += $r_ProcessingPagoPA['MaxFee'];
    $n_TotPartialFee += $n_PartialFee;
    
    //Importi ridotti, minimi e massimi articoli aggiuntivi
    if ($r_ProcessingPagoPA['ArticleNumber'] > 1){
        while ($r_AdditionalArticle = mysqli_fetch_assoc($rs_AdditionalArticle)) {
            $n_AddPartialFee = $r_AdditionalArticle['ReducedPayment'] == 1
                ? $r_AdditionalArticle['Fee'] * FINE_PARTIAL
                : $r_AdditionalArticle['Fee'];
            
            $a_Result['Amounts'][] = array(
                'ReducedPartial' => number_format($n_AddPartialFee, 2, '.', ''),
                'ReducedTotal' => number_format($r_AdditionalArticle['Fee'], 2, '.', ''),
                'Partial' => number_format($r_AdditionalArticle['Fee'], 2, '.', ''),
                'Total' => number_format($r_AdditionalArticle['MaxFee'] * FINE_MAX, 2, '.', ''),
                'ViolationTypeId' => $r_AdditionalArticle['ViolationTypeId']
            );
            
            $n_TotFee += $r_AdditionalArticle['Fee'];
            $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
            $n_TotPartialFee += $n_AddPartialFee;
        }
    }
    
    //Importi totali con spese addizionali
    $a_Result['Sum'] = array(
        'ReducedPartial'=>number_format(($n_TotPartialFee + $AdditionalFee), 2, '.', ''),
        'ReducedTotal'=>number_format(((float)$n_TotFee + (float)$AdditionalFee), 2, '.', ''),
        'Partial'=>number_format(((float)$n_TotFee + (float)$AdditionalFee), 2, '.', ''),
        'Total'=>number_format(((float)$n_TotMaxFee * FINE_MAX + (float)$AdditionalFee), 2, '.', ''),
    );
    
    return $a_Result;
}

// calcola il totale dovuto in base a quanto salvato sul verbale
function calcolaImportiSulVerbale($r_ProcessingPagoPA,$rs_AdditionalArticle,$r_ArticleTariff,$r_Customer){
    $NotificationFee = 0;
    $ResearchFee = 0;
    $n_TotPartialFee = 0;
    $n_TotFee = 0;
    $n_TotMaxFee = 0;

    $CustomerFee = $r_ProcessingPagoPA['CustomerFee']; //$r_ProcessingPagoPA se è preso da $FineHistory 6
    $NotificationFee += $r_ProcessingPagoPA['NotificationFee']; //$r_ProcessingPagoPA se è preso da $FineHistory 6 ha già le Owner e le customer di FineTrespasser sommate nelle notification quindi non vanno rissommate secondo me
    $ResearchFee = $r_ProcessingPagoPA['ResearchFee'];
    $AdditionalFee = $NotificationFee + $ResearchFee; // + $CustomerFee; //B2720 NOTA: le customer fee dovrebbero esser già assorbite nelle customer e quindi da qui andrebbero tolte
        $n_TotFee += $r_ProcessingPagoPA['Fee'];
        $n_TotMaxFee += $r_ProcessingPagoPA['MaxFee'];
        if ($r_ArticleTariff['ReducedPayment'] == 1)
            $n_TotPartialFee += $r_ProcessingPagoPA['Fee'] * FINE_PARTIAL;
        else
            $n_TotPartialFee += $r_ProcessingPagoPA['Fee'];
        if ($r_ProcessingPagoPA['ArticleNumber'] > 1)
            while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                $n_TotFee += $r_AdditionalArticle['Fee'];
                $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
                if ($r_AdditionalArticle['ReducedPayment'] == 1)
                    $n_TotPartialFee += $r_AdditionalArticle['Fee'] * FINE_PARTIAL;
                    else
                        $n_TotPartialFee += $r_AdditionalArticle['Fee'];
            }
    return array('ReducedPartial'=>number_format(($n_TotPartialFee + $AdditionalFee), 2, '.', ''),
        'ReducedTotal'=>number_format(((float)$n_TotFee + (float)$AdditionalFee), 2, '.', ''),
        'Partial'=>number_format(((float)$n_TotFee + (float)$AdditionalFee), 2, '.', ''),
        'Total'=>number_format(((float)$n_TotMaxFee * FINE_MAX + (float)$AdditionalFee), 2, '.', ''),
        'AdditionalFee'=>number_format((float)$AdditionalFee, 2, '.', ''),
        'NotificationFee'=>number_format((float)$NotificationFee, 2, '.', ''),
        'ResearchFee'=>number_format((float)$ResearchFee, 2, '.', ''),
        'CustomerFee'=>number_format((float)$CustomerFee, 2, '.', ''));
}

function unparse_url($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

    return "$scheme$user$pass$host$port$path$query$fragment";
}

/**Costruisce la url assoluta della pagina corrente
 *
 * @return string $url
 */
function prendiUrlAssoluto(){
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $url = "https";
    else
        $url = "http";

    $url .= "://";
    $url .= $_SERVER['HTTP_HOST'];
    $url .= $_SERVER['REQUEST_URI'];

    return $url;
}

/**Costruisce la url relativa della pagina corrente
 *
 * @param bool $soloNome [opzionale] Se impostato a true restituisce solo la parte finale del percorso
 *
 * @return string $url
 */
function prendiUrlRelativo(bool $soloNome = false){
    if($soloNome)
        return basename($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING'];
    else
        return $_SERVER['REQUEST_URI'];
}

/**Prende una url e ne sostituisce/imposta i parametri definiti
 *
 * @param array $parametri i parametri da impostare
 * @param string $url [opzionale] se non definito chiama prendiUrl()
 *
 * @return string $urlRiscritta Se la url è malformata o il percoso non è specificato, ritorna null
 */
function impostaParametriUrl(array $parametri, string $url = null){
    $a_NuoviParametri = array();
    if (empty($url)){
        $url = prendiUrlRelativo(true);
    }
    if (!parse_url($url)){
        return null;
    } else {
        $a_url = parse_url($url);
        if (!empty($a_url['path'])){
            if (isset($a_url['query'])){
                parse_str($a_url['query'], $a_NuoviParametri);
            }
        } else {
            return null;
        }
    }
    foreach ($parametri as $parametro => $valore){
        $a_NuoviParametri[$parametro] = $valore;
    }
    $a_url['query'] = http_build_query($a_NuoviParametri);
    return unparse_url($a_url);
}

function pdfdigitalSign($pdf,$password,$reason){
    $info = array(
        'Name' => $_SESSION['username'],
        'Location' => $_SESSION['cityid'],
        'Reason' => $reason);
    $certificate = "file://".ROOT."/doc/signatures/".$_SESSION['userid'].CERT_EXTENSION;
    $pdf->setSignature($certificate, $certificate, $password, '', 2, $info);

}

function digitalSign($filename,$newName,$password,$reason,$signerTxt = null, $xPos = 0, $yPos = 0){
    $pdf=new FPDI();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 8));
    $pageCount =$pdf->setSourceFile($filename);
    for($pageNumber=1;$pageNumber<=$pageCount;$pageNumber++){
        $id=$pdf->importPage($pageNumber);
        $pdf->AddPage();
        $pdf->useTemplate($id);

        //Scrive la riga del firmatario nella prima pagina alle coordinate date se presente
        if($pageNumber == 1 && !empty($signerTxt)){
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 8, '', true);
            $pdf->SetXY($xPos, $yPos);
            $pdf->writeHTML($signerTxt, true, false, true, false, '');
        }
    }
    pdfdigitalSign($pdf, $password, $reason);
    $pdf->Output(    $newName, "F");
}

function calcolaImportoArticle($fee,$maxFee,$article,$paragraph,$expirationDate,$InsuranceDate){
    $fees = array();
    $fees['Fee'] = $fee;
    $fees['MaxFee'] = $maxFee;
    if($article==193 && $paragraph=="2"){
        if(!empty($InsuranceDate) && !empty($expirationDate)){
            $n_Day = DateDiff("D", $expirationDate, DateInDB($InsuranceDate));
            if($n_Day<=30){
                $fees['Fee'] = $fees['Fee'] * FINE_INSURANCE_REDUCED;
                $fees['MaxFee'] = $fees['MaxFee'] * FINE_INSURANCE_REDUCED;
            }
        }
    }
    return $fees;
}

//le spese sono prese normalmente dalle configurazioni dell'ente per le spese postali tranne nel caso di trasgressore con PEC per cui 
// sono prese dalla configurazione per l'invio PEC
//***NAZIONALE***
function getTrespasserFees($rs,$trespasser,$customer,$postalcharge,$CreationType,$ZoneId,$RegularPostalFine){
    $rs_PECfineHistory=$rs->Select("FineHistory", "FineId={$trespasser['FineId']} and NotificationTypeId=15");
    $isPECFine=(mysqli_fetch_array($rs_PECfineHistory))!=null;
    $totalFees['AdditionalFee']=0;
    $totalFees['AdditionalFeeCAN']=0;
    $totalFees['AdditionalFeeCAD']=0;
    
    $ChargeTotalFee = $customer['NationalTotalFee'];
    //Se le spese di notifica nazionali sono zero allora le prende dalla zona 0
    $NotificationFee = $customer['NationalNotificationFee'] > 0 ? $customer['NationalNotificationFee'] : $postalcharge['Zone' . $ZoneId];
    $ResearchFee = $customer['NationalResearchFee'];
    $PecNotificationFee = $customer['NationalPECNotificationFee'];
    $PecResearchFee = $customer['NationalPECResearchFee'];
    
    //se il trasgressore ha la pec e non è una rinitofica dovrebbe valere questo (vedere se $isPECFine tiene conto della rinotifica)
    if($isPECFine){
        $totalFees['NotificationFee'] = $PecNotificationFee;
        $totalFees['ResearchFee'] = $PecResearchFee;
    } else {
        if ($ChargeTotalFee > 0)
            $totalFees['ChargeTotalFee'] = $ChargeTotalFee;
        else if ($NotificationFee > 0) {
            $totalFees['NotificationFee'] = $NotificationFee;
            $totalFees['ResearchFee'] = $ResearchFee;
        }
        if($CreationType==5){
            $totalFees['ResearchFee'] = $ResearchFee;
            $totalFees['NotificationFee'] = $customer['NationalNotificationFee'];//si leggono direttamente dal record per essere sicuri che nessuna logica ci metta altro
            $totalFees['AdditionalFee'] = $totalFees['NotificationFee'] + $totalFees['ResearchFee'];
        } else {
            if($RegularPostalFine==1){
                $totalFees['ResearchFee'] = 0.00;
                //in questo caso NotificationFee sono prese dalle spese dei solleciti perchè i solleciti sono inviati per posta ordinaria e non raccomandata
                $totalFees['NotificationFee'] = $postalcharge['ReminderZone' . $ZoneId];
            } else {
                if ($totalFees['ChargeTotalFee'] > 0) {
                    $totalFees['ResearchFee'] =  $totalFees['ChargeTotalFee'] - $NotificationFee;
                    $totalFees['NotificationFee'] = $NotificationFee;
                }
            }
        }
    }
    $totalFees['AdditionalFee']=$totalFees['NotificationFee']+$totalFees['ResearchFee'];
    $totalFees['AdditionalFeeCAN']=$totalFees['AdditionalFee']+$postalcharge['CanFee'];
    $totalFees['AdditionalFeeCAD']=$totalFees['AdditionalFee']+$postalcharge['CadFee'];
    return $totalFees;
}

//le spese sono prese normalmente dalle configurazioni dell'ente per le spese postali tranne nel caso di trasgressore con PEC per cui
// sono prese dalla configurazione per l'invio PEC
//***ESTERO***
function getTrespasserFeesForeign($rs,$trespasser,$customer,$postalcharge,$CreationType,$ZoneId,$RegularPostalFine){
    $rs_PECfineHistory=$rs->Select("FineHistory", "FineId={$trespasser['FineId']} and NotificationTypeId=15");
    $isPECFine=(mysqli_fetch_array($rs_PECfineHistory))!=null;
    $totalFees['AdditionalFee']=0;
    $totalFees['AdditionalFeeCAN']=0;
    $totalFees['AdditionalFeeCAD']=0;
    
    //se il trasgressore ha la pec e non è una rinitofica dovrebbe valere questo (vedere se $isPECFine tiene conto della rinotifica)
    if($isPECFine){
        $totalFees['NotificationFee']=$customer['ForeignPECNotificationFee'];
        $totalFees['ResearchFee']=$customer['ForeignPECResearchFee'];
    } else{
        if ($customer['ForeignTotalFee'] > 0)
            $totalFees['ChargeTotalFee'] = $customer['ForeignTotalFee'];
        else if ($postalcharge['Zone'.$ZoneId] > 0) {
            $totalFees['NotificationFee'] = $postalcharge['Zone'.$ZoneId];
            $totalFees['ResearchFee'] = $customer['ForeignResearchFee'];
        }
        if($CreationType==5){
            $totalFees['ResearchFee'] = $customer['ForeignResearchFee'];
            $totalFees['NotificationFee'] = $postalcharge['Zone'.$ZoneId];
            $totalFees['AdditionalFee'] = $totalFees['NotificationFee'] + $totalFees['ResearchFee'];
        } else {
            if($RegularPostalFine==1){
                $totalFees['ResearchFee'] = 0.00;
                $totalFees['NotificationFee'] = $postalcharge['ReminderZone' . $ZoneId];
            } else {
                if ($totalFees['ChargeTotalFee'] > 0) {
                    $totalFees['ResearchFee'] =  $totalFees['ChargeTotalFee'] - $postalcharge['Zone' . $ZoneId];
                    $totalFees['NotificationFee'] = $postalcharge['Zone' . $ZoneId];
                } else{
                    if ($totalFees['NotificationFee'] == 0)
                        $totalFees['NotificationFee'] = $postalcharge['Zone' . $ZoneId];
                }
            }
        }
    }
    $totalFees['AdditionalFee']=$totalFees['NotificationFee']+$totalFees['ResearchFee'];
    $totalFees['AdditionalFeeCAN']=$totalFees['AdditionalFee']+$postalcharge['CanFee'];
    $totalFees['AdditionalFeeCAD']=$totalFees['AdditionalFee']+$postalcharge['CadFee'];
    return $totalFees;
}

function getFineFees(CLS_DB $rs , array $r_Customer, $fineId, array $r_ArticleTariff,string $InsuranceDate,string $CreationType,string $RegularPostalFine,$NotificationDate,$ZoneId,$update,$cityid = null,$s_TypePlate = 'N'){

    $postalcharge=getPostalCharge($cityid ?? $_SESSION['cityid'],$NotificationDate,$rs);
    
    $totalFees=array('NotificationFee' => 0,'ChargeTotalFee'=> 0,'ResearchFee'=> 0);
    
    $totalFees['CustomerFee']=0;
    $totalFees['AdditionalFee']=0;
    $totalFees['AdditionalFeeCAN']=0;
    $totalFees['AdditionalFeeCAD']=0;
    $totalFees['CANFee']=$postalcharge['CanFee'];
    $totalFees['CADFee']=$postalcharge['CadFee'];
    $fineArticle=mysqli_fetch_array($rs->Select("V_ViolationArticle","Id=$fineId"));
    if($fineArticle==null)
        return null;
    //Applica un moltiplicatore in caso di assicurazione scaduta
    $fees=calcolaImportoArticle($fineArticle['Fee'],$fineArticle['MaxFee'],$fineArticle['Article'],$fineArticle['Paragraph'],$fineArticle['ExpirationDate'],$InsuranceDate);
    
    $totalFees['TotalFee'] = $fees['Fee'];
    $totalFees['MaxFee'] = $fees['MaxFee'];
    
    //Gestione del ridotto
    if ($r_ArticleTariff['ReducedPayment'] == 1)
        $totalFees['PartialFee'] = $fees['Fee'] * FINE_PARTIAL;
    else
        $totalFees['PartialFee'] = $fees['Fee'];
    
    //Aggiorna il relativo FineArticle
    if($update && $fees['Fee']!=$fineArticle['Fee']){
        $a_FineArticle = array(
            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $fees['Fee'], 'settype' => 'flt'),
            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $fees['MaxFee'], 'settype' => 'flt'),
        );
        $rs->Update('FineArticle', $a_FineArticle, 'FineId=' . $fineId);
    }
    //Gestione articoli aggiuntivi
    $rs_AdditionalArticle=$rs->Select('V_AdditionalArticle', "FineId=" . $fineId, "ArticleOrder");
    while($additionalArticle = mysqli_fetch_array($rs_AdditionalArticle)){
        $fees=calcolaImportoArticle($additionalArticle['Fee'],$additionalArticle['MaxFee'],$additionalArticle['Article'],$additionalArticle['Paragraph'],$additionalArticle['ExpirationDate'],$InsuranceDate);
        $totalFees['TotalFee'] += $fees['Fee'];
        $totalFees['MaxFee'] += $fees['MaxFee'];
        if ($r_ArticleTariff['ReducedPayment'] == 1)
            $totalFees['PartialFee'] += $fees['Fee'] * FINE_PARTIAL;
        else
            $totalFees['PartialFee'] += $fees['Fee'];
        if($update && $fees['Fee']!=$fineArticle['Fee']){
            $a_FineAdditionalArticle = array(
                array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $fees['Fee'], 'settype' => 'flt'),
                array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $fees['MaxFee'], 'settype' => 'flt'),
            );
            $rs->Update('FineAdditionalArticle', $a_FineAdditionalArticle, 'FineId=' .$fineId);
        }
    }
    
    $rs_Trespassers = $rs->Select('V_FineTrespasser', "FineId=$fineId AND FineCreateDate IS NULL");
    
    //TODO 26/05/2022 Davide-Daniela: è fatto così perchè si pensa sia giusto sommare le spese di ogni trasgressore in modo che nel caso di
    //più figure, chi paga si prende carico delle spese di spedizione dell'altro, chiedere conferma a Sarida
    while ($trespasser = mysqli_fetch_assoc($rs_Trespassers)){
        //prendo soli i dati dei trasgressori in determinati stati previsti nella V_ViolationArticle
        // che sono i principali o ri risultanti da rinotifiche
        if ($trespasser['TrespasserTypeId'] == 1 || $trespasser['TrespasserTypeId'] == 11
            || ($trespasser['TrespasserTypeId'] == 2 && $trespasser['FineCreateDate'] == null)
            || ($trespasser['TrespasserTypeId'] == 3 && $trespasser['FineCreateDate'] == null)
            || ($trespasser['TrespasserTypeId'] == 15 && $trespasser['FineCreateDate'] == null)
            || ($trespasser['TrespasserTypeId'] == 16 && $trespasser['FineCreateDate'] == null)) {
                
                //da $trespasser vengono prese le spese di rinotifica o aggintive salvate su FineTrespasser
                //da $trespasserFees vengono prese le spese di spedizione via posta o via pec nazionali o estere da configurazione dell'ente

                if($s_TypePlate == 'N')
                    $trespasserFees=getTrespasserFees($rs,$trespasser,$r_Customer,$postalcharge,$CreationType,$ZoneId,$RegularPostalFine);
                else 
                    $trespasserFees=getTrespasserFeesForeign($rs,$trespasser,$r_Customer,$postalcharge,$CreationType,$ZoneId,$RegularPostalFine);
                    
                //spese di notifica e ricerca della notifica precedente se ci sono vengono sommate alle spese di notifica
                // e a quelle addizionali
                $totalFees['CustomerFee'] += $trespasser['CustomerAdditionalFee']; //sono spese di rinotifica all'utente o spese addizionali insertite sul verbale
                $totalFees['AdditionalFee']+= $trespasser['OwnerAdditionalFee'] +$trespasser['CustomerAdditionalFee']; //CustomerAdditionalFee sono costi aggiuntivi inseriti sul verbale o in rinotifica
                //AdditionalFee dovrebbero essere quelle di notifica e quelle di ricerca
                //NotificationFee devono tener conto anche delle spese di notifica delle versioni precedenti quando l'atto corrente è una rinotifica
                $totalFees['NotificationFee']+=$trespasserFees['NotificationFee'] + $trespasser['OwnerAdditionalFee'] + $trespasser['CustomerAdditionalFee'];
                $totalFees['ChargeTotalFee']+=$trespasserFees['ChargeTotalFee'];
                $totalFees['ResearchFee']+=$trespasserFees['ResearchFee'];
                $totalFees['AdditionalFee']+=$trespasserFees['AdditionalFee'];
                $totalFees['AdditionalFeeCAN']+=$trespasserFees['AdditionalFeeCAN'];
                $totalFees['AdditionalFeeCAD']+=$trespasserFees['AdditionalFeeCAD'];
        }
        trigger_error("Spese precedenti: ".($trespasser['OwnerAdditionalFee'] +$trespasser['CustomerAdditionalFee']),E_USER_NOTICE);
        trigger_error("Spese correnti: ".$trespasserFees['NotificationFee'],E_USER_NOTICE);
    }
    trigger_error("spese totali notifica : ".$totalFees['NotificationFee'] ,E_USER_NOTICE);
    trigger_error("spese totali ricerca : ".$totalFees['ResearchFee'] ,E_USER_NOTICE);

    $totalFees['MaxFee'] *= FINE_MAX;
    $totalFees['TotalPartialFee'] = $totalFees['PartialFee'] + $totalFees['AdditionalFee'];
    $totalFees['TotalPartialFeeCAN'] = $totalFees['PartialFee'] + $totalFees['AdditionalFeeCAN'];
    $totalFees['TotalPartialFeeCAD'] = $totalFees['PartialFee'] + $totalFees['AdditionalFeeCAD'];
    $totalFees['TotalFeeCAN'] = $totalFees['TotalFee'] +  $totalFees['AdditionalFeeCAN'];
    $totalFees['TotalFeeCAD'] = $totalFees['TotalFee'] + $totalFees['AdditionalFeeCAD'];
    $totalFees['n_TotFee'] = $totalFees['TotalFee'];
    $totalFees['TotalFee'] += $totalFees['AdditionalFee'];
    $totalFees['TotalMaxFee'] = $totalFees['MaxFee'] + $totalFees['AdditionalFee'];
    $totalFees['TotalMaxFeeCAN']  = $totalFees['MaxFee'] +  $totalFees['AdditionalFeeCAN'];
    $totalFees['TotalMaxFeeCAD']  = $totalFees['MaxFee'] + $totalFees['AdditionalFeeCAD'];
    return $totalFees;
}

function getLicensePointCodeMex(CLS_DB $rs){
    $rs_LicensePointMex = $rs->Select('LicensePointMex', "1=1");
    $a_LicensePointId = array();
    $a_LicensePointDescription = array();
    while ($r_LicensePointMex = mysqli_fetch_array($rs_LicensePointMex))
    {
        $a_LicensePointId[] = $r_LicensePointMex['Id'];
        $a_LicensePointDescription[] = $r_LicensePointMex['Description'];
    }
    return array_combine($a_LicensePointId, $a_LicensePointDescription);

}

function updateCommunicationStatus($rs, $fineId, $status, $licensePointCode, $reducePoints = null, $reduceDate = null, $previousState = null)
{
    $a_FineCommunication = array(
        array(
            'field' => 'CommunicationStatus',
            'selector' => 'value',
            'type' => 'int',
            'value' => $status,
            'settype' => 'int'),
        array(
            'field' => 'LicensePointId',
            'selector' => 'value',
            'type' => 'str',
            'value' => $licensePointCode),
        array(
            'field'=>'RegDate',
            'selector'=>'value',
            'type'=>'date',
            'value'=>date("Y-m-d")),
        array(
            'field'=>'RegTime',
            'selector'=>'value',
            'type'=>'str',
            'value'=>date("H:i")));
    if ($status != 3)
        $a_FineCommunication[] = array(
            'field' => 'ReducedDate',
            'selector' => 'value',
            'type' => 'date',
            'value' => $reduceDate);
    //if ($previousState != 9) //nella vecchia versione i punti venivano aggiornati anche se il vecchio stato era 9 - da riattribuire
        $a_FineCommunication[] = array(
            'field' => 'ReducedPoint',
            'selector' => 'value',
            'type' => 'int',
            'value' => $reducePoints,
            'settype' => 'int');
    $rs->ExecuteQuery("INSERT INTO FineCommunicationHistory(FineId,Trespasserid,TrespasserTypeId,CommunicationProtocol,CommunicationDate,ReducedPoint,CommunicationStatus,LicensePointId,RegDate,RegTime,UserId)
          SELECT FineId,Trespasserid,TrespasserTypeId,CommunicationProtocol,CommunicationDate,ReducedPoint,CommunicationStatus,LicensePointId,RegDate,RegTime,UserId
          FROM FineCommunication where FineId=$fineId");
    $where = "FineId=" . $fineId . " AND (TrespasserTypeId =1 OR TrespasserTypeId=3 OR TrespasserTypeId=10)";
    if ($previousState != null)
        $where .= " and CommunicationStatus=$previousState";
    $rs->Update('FineCommunication', $a_FineCommunication, $where);
    trigger_error("Updated FineCommunicationl with status $status, code $licensePointCode points $reducePoints", E_USER_NOTICE);
}

function createCommunicationFilter($s_TypePlate,$Search_Country,$Search_FromProtocolId,$Search_ToProtocolId,$Search_Plate,$Search_Trespasser,$Search_Violation,$LicenseYoung,$Search_FromFineDate,$Search_ToFineDate,$Search_Ref,$Search_FromNotificationDate,$Search_ToNotificationDate,$Search_PaymentDate,$Search_FromProtocolYear,$Search_ToProtocolYear,$Search_CommunicationStatus,$Search_Com126Bis,$additionalDays126Bis=null,$Flag126bis,$FlagDispute,$Search_NotificationStatus){
    global $rs;
    $str_Where="";
    $FixedFromDate = '2021-03-30';
    
    if ($s_TypePlate == "N")
        $str_Where .= " AND CountryId='Z000'";
    else if ($s_TypePlate == "F") {
        if ($Search_Country != '')
            $str_Where .= " AND CountryId='$Search_Country'";
        else
            $str_Where .= " AND CountryId!='Z000'";
    }
    if ($Search_FromProtocolId > 0 && $Search_ToProtocolId <= 0)
        $str_Where .= " AND ProtocolId  = '" . $Search_FromProtocolId . "'";
    if ($Search_FromProtocolId > 0 && $Search_ToProtocolId > 0)
        $str_Where .= " AND ProtocolId >= '" . $Search_FromProtocolId . "' AND ProtocolId  <= '" . $Search_ToProtocolId . "'";
    if ($Search_FromProtocolYear != '' && $Search_ToProtocolYear == '')
        $str_Where .= " AND ProtocolYear=" . $Search_FromProtocolYear;
    if ($Search_FromProtocolYear != '' && $Search_ToProtocolYear != '')
        $str_Where .= " AND ProtocolYear>=" . $Search_FromProtocolYear. " AND ProtocolYear<=" . $Search_ToProtocolYear;

    if ($Search_Plate != "")
        $str_Where .= " AND VehiclePlate LIKE '%" . addslashes($Search_Plate) . "%'";
    if ($Search_Trespasser != "")
        $str_Where .= " AND (CompanyName LIKE '%" . addslashes($Search_Trespasser) . "%' OR Surname LIKE '%" . addslashes($Search_Trespasser) . "%')";
    if ($Search_Violation != "")
        $str_Where .= " AND ViolationTypeId=" . $Search_Violation;
    switch ($Search_CommunicationStatus){
        case 1:
            $str_Where.="  AND CommunicationDate IS NULL";
            break;
        case 2:
            $str_Where .= " AND CommunicationDate IS NOT NULL ";
        break;
        case 3:
            $str_Where.=" AND CommunicationDate-60-$additionalDays126Bis>NotificationDate";
            break;
        case 4:
            $str_Where .=" AND CommunicationDate IS NOT NULL AND CommunicationStatus!=5";
            break;
        case 5:
            $str_Where .=" AND CommunicationStatus=5";
            break;
    }
    if ($Search_Ref != '')
         $str_Where .= " AND Code='" . $Search_Ref . "'";
    
     $str_WhereNotificationDate = '';
     /*if($Search_ToNotificationDate != ''){
         //Filtro implicito per filtrare via atti già prescritti
         $r_Processing = $rs->getArrayLine($rs->SelectQuery("
            SELECT RangeDayMax+RangeDayMin+WaitDay AS ProcessingDays
            FROM ".($s_TypePlate != 'F' ? 'ProcessingData126BisNational' : 'ProcessingData126BisForeign')."
            WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));
         $ProcessingDays = $r_Processing['ProcessingDays'];
         $MinNotificationDate = date('Y-m-d', strtotime("-{$ProcessingDays} days"));
         
         //if(DateInDB($Search_ToNotificationDate) > $MinNotificationDate)
         //    $Search_ToNotificationDate = $MinNotificationDate;
     }*/
         
     if ($Search_FromNotificationDate == "" || ($Search_ToNotificationDate == "")){
         $FixedFromFineDate = date('Y-m-d', strtotime('-60 days', strtotime($FixedFromDate)));
         
         if ($Search_FromFineDate != ""){
             $str_Where .= " AND FineDate>='" . (($FlagDispute == 0 || $FlagDispute == 1) && DateInDB($Search_FromFineDate) < $FixedFromFineDate ? $FixedFromFineDate : DateInDB($Search_FromFineDate)) . "'";
         }
         if ($Search_ToFineDate != "")
             $str_Where .= " AND FineDate<='" . DateInDB($Search_ToFineDate) . "'";
     } else {
         if ($Search_FromFineDate != "")
             $str_Where .= " AND FineDate>='" . DateInDB($Search_FromFineDate) . "'";
         if ($Search_ToFineDate != "")
             $str_Where .= " AND FineDate<='" . DateInDB($Search_ToFineDate) . "'";
     }

     //Per controllo su data di notifica non oltre i termini usare $r_Processing['ProcessingDays'] da sommare a NotificationDate per confrontare con il limite superiore della notifica nella pagina
     // esempio condizione 
     //$str_WhereNotificationDate .= " AND DATE_ADD(NotificationDate, INTERVAL $ProcessingDays DAY)<='" . DateInDB($Search_ToNotificationDate)."'";
     // ma la tolgo perché altrimenti non tornano i conti rispetto ad Elaborazioni 126 bis
    if ($Search_FromNotificationDate != "")
        $str_WhereNotificationDate .= " AND NotificationDate>='" . (($FlagDispute == 0 || $FlagDispute == 1) && DateInDB($Search_FromNotificationDate) < $FixedFromDate ? $FixedFromDate : DateInDB($Search_FromNotificationDate)) ."'";
    else if ($FlagDispute == 0 || $FlagDispute == 1)
        $str_WhereNotificationDate .= " AND NotificationDate>='2021-03-30'";
        //prove per filtri comunicazione 126 bis oltre i termini
        //if($MinNotificationDate <= '2021-03-30')
        //    $MinNotificationDate = '2021-03-30';
        //$str_WhereNotificationDate .= " AND NotificationDate>='$MinNotificationDate'";
    if ($Search_ToNotificationDate != "")
        $str_WhereNotificationDate .= " AND NotificationDate<='" . DateInDB($Search_ToNotificationDate)."'";

    if($Search_PaymentDate != 1){
        if ($Search_FromNotificationDate != "" || $Search_ToNotificationDate != "")
            $str_Where .= $str_WhereNotificationDate." AND ((((StatusTypeId >= 25) AND (StatusTypeId <= 30)) OR (StatusTypeId = 12))
           AND isnull(KindSendDate)
           AND (NotificationDate is not null)
           AND ((ResultId <= 9) OR (ResultId = 21) OR (ResultId = 22)))";
    } else {
        $str_Where .= " AND ((1=1";
        
        if ($Search_FromNotificationDate != "" || $Search_ToNotificationDate != "")
            $str_Where .= $str_WhereNotificationDate." AND ((((StatusTypeId >= 25) AND (StatusTypeId <= 30)) OR (StatusTypeId = 12))
           AND isnull(KindSendDate)
           AND (NotificationDate is not null)
           AND ((ResultId <= 9) OR (ResultId = 21) OR (ResultId = 22)))";
                    
        $str_Where .= ") OR (1=1";
                
        if ($Search_FromNotificationDate != "" || $Search_ToNotificationDate != ""){
            $str_Where .= " AND Id IN(SELECT FineId FROM FinePayment WHERE 1=1";
            
            if ($Search_FromNotificationDate != "")
                $str_Where .= " AND PaymentDate>='" . DateInDB($Search_FromNotificationDate) ."'";
            if ($Search_ToNotificationDate != "")
                $str_Where .= " AND PaymentDate<='" . DateInDB($Search_ToNotificationDate) ."'";
                
            $str_Where .= ")";
        }
        $str_Where .= "))";
    }

    if ($LicenseYoung == 1)
        $str_Where .= " AND (LicenseYead > 2 OR LicenseYead IS NULL)";
    else if ($LicenseYoung == 2)
        $str_Where .= " AND LicenseYead < 3";
    if ($Flag126bis == 2)
        $str_Where .= " AND (126BisProcedure = 0 or 126BisProcedure is null)";
    else if ($Flag126bis == 1)
        $str_Where .= " AND 126BisProcedure = 1";
    if ($Search_Trespasser != "")
        $str_Where .= " AND (CompanyName LIKE '%" . addslashes($Search_Trespasser) . "%' OR Surname LIKE '%" . addslashes($Search_Trespasser) . "%')";
    if($Search_Com126Bis == 1)
        $str_Where.= " AND Id NOT IN (select distinct pf.PreviousId from Fine pf Where pf.PreviousId is not null AND StatusTypeId<35 AND Note = 'Creazione automatica 126 BIS')";
    else if($Search_Com126Bis == 2)
        $str_Where.= " AND Id IN (select distinct pf.PreviousId from Fine pf Where pf.PreviousId is not null AND StatusTypeId<35 AND Note = 'Creazione automatica 126 BIS')";
    if ($FlagDispute == 2)
        $str_Where .= " AND Id IN (select distinct fd.FineId from FineDispute fd where DisputeStatusId=3)";
    else if ($FlagDispute == 1)
        $str_Where .= " AND (
            Id not IN (select distinct fd.FineId from FineDispute fd) 
            or Id IN (select distinct fd.FineId from FineDispute fd join Dispute d on fd.DisputeId=d.Id where DisputeStatusId<>3 and d.FineSuspension = 0 and d.SuspensiveDate is null))";
    //In attesa
    if($Search_NotificationStatus == 1)
        $str_Where .= " AND ResultId IS NULL";
    //Notificati
    elseif($Search_NotificationStatus == 2)
        $str_Where .= " AND ResultId IN(1,2,3,4,5,6,7,8,9,22,24)";
    //Non notificati
    elseif($Search_NotificationStatus == 3)
        $str_Where .= " AND ResultId IN(10,11,12,13,17,18,19,20,21,23)";
    
    return $str_Where;
}
function getRadioSelected(string $name,int $optionsNumber,int $defaultId):array{
    $value = CheckValue($name, 's');
    $return=array();
    $found=false;
    for($i=0;$i<$optionsNumber;$i++)
        if($i==$value && $value != ''){
            $found=true;
            $return[]=CHECKED;
        } else
            $return[]='';
    if(!$found)
        $return[$defaultId]=CHECKED;
    return $return;
}

function createLicensePointWhere(){
    global $r_Customer;
    global $Search_LicenseType;
    global $Search_LicenseCountry;
global $Search_Ref;
global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
global $Search_FromProtocolId;
global $Search_ToProtocolId;
    global $Search_FromFineDate;
global $Search_ToFineDate;
global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
global $Search_Anomalies;
global $Search_AnomalyType;
global $Search_LicenseYoung;
    global $Search_LicenseHidden;
global $Search_Trespasser;
global $Search_Reattribution;
global $Search_Reattributed;
    global $Search_Discrepancy;
global $Search_DiscrepancyType;
global $Search_HasDispute;
global $s_TypePlate;
global $Search_Country;
    $str_Where=" AND CommunicationStatus!=5";
     if ($s_TypePlate == "N")
            $str_Where .= " AND CountryId='Z000'";
        else
        {
            if ($Search_Country != '')
                $str_Where .= " AND CountryId='$Search_Country'";
            else
                $str_Where .= " AND CountryId='Z000'";
        }

    if ($Search_LicenseType == "N")
        $str_Where .= " AND (DocumentCountryId='Z000' or DocumentCountryId is null or DocumentCountryId='')";
    else
    {
        if ($Search_LicenseCountry != '')
            $str_Where .= " AND (DocumentCountryId='$Search_LicenseCountry' or DocumentCountryId is null or DocumentCountryId='')";
        else
            $str_Where .= " AND (DocumentCountryId='Z000' or DocumentCountryId is null or DocumentCountryId='')";
    }
    if ($Search_Ref != '')
        $str_Where .= " AND Code='" . $Search_Ref . "'";
    if ($Search_FromProtocolYear != '')
        $str_Where .= " AND ProtocolYear>=" . $Search_FromProtocolYear;
    if ($Search_ToProtocolYear != '')
        $str_Where .= " AND ProtocolYear<=" . $Search_ToProtocolYear;
    if ($Search_FromProtocolId != '')
        $str_Where .= " AND ProtocolId >=" . $Search_FromProtocolId;
    if ($Search_ToProtocolId != '')
        $str_Where .= " AND ProtocolId <=" . $Search_ToProtocolId;
    if ($Search_FromFineDate != "")
        $str_Where .= " AND FineDate>='" . DateInDB($Search_FromFineDate) . "'";
    if ($Search_ToFineDate != "")
        $str_Where .= " AND FineDate<='" . DateInDB($Search_ToFineDate) . "'";
    if ($Search_FromNotificationDate != "")
        $str_Where .= " AND ( NotificationDate>='" . DateInDB($Search_FromNotificationDate) . "' OR ( NotificationDate is null AND FineDate>='" . DateInDB($Search_FromNotificationDate) . "'))";
    if ($Search_ToNotificationDate != "")
        $str_Where .= " AND ( NotificationDate<='" . DateInDB($Search_ToNotificationDate) . "' OR ( NotificationDate is null AND FineDate<='" . DateInDB($Search_ToNotificationDate) . "'))";

    if ($Search_LicenseYoung == 1)
        $str_Where .= " AND LicenseYear > 2";
    else if ($Search_LicenseYoung == 2)
        $str_Where .= " AND LicenseYear < 3";
    if ($Search_HasDispute == 1)
        $str_Where .= " AND DisputeId IS NULL";
    else if ($Search_HasDispute == 2)
        $str_Where .= " AND DisputeId IS NOT NULL";
    if ($Search_Trespasser != "")
        $str_Where .= " AND (CompanyName LIKE '%" . addslashes($Search_Trespasser) . "%' OR Surname LIKE '%" . addslashes($Search_Trespasser) . "%')";
    if ($Search_Reattributed>0)
        $str_Where .= " AND ( CommunicationStatus=0 or Id in(select FineId from FineCommunicationHistory fch where fch.CommunicationStatus=0)) and Id in (select FineId from FineCommunication fch where fch.CommunicationStatus=5)";

    if ($Search_Reattribution>0)
        $str_Where .= " AND CommunicationStatus = 9";
    else
    {
        if ($Search_LicenseHidden == 0)
            $str_Where .= " AND CommunicationStatus != 1";
        else if ($Search_LicenseHidden == 2)
            $str_Where .= " AND CommunicationStatus = 1";
    }
    if ($Search_Anomalies == 0)
        $str_Where .= " AND CommunicationStatus!=3";
    else if ($Search_Anomalies == 2)
        $str_Where .= " AND CommunicationStatus=3";

    $discrepancy="false ";
    if($Search_DiscrepancyType=="Tutte" || $Search_DiscrepancyType=="Trasgressore")
    {
        $discrepancy.=" OR CONCAT_WS(' ',TRIM(t.Name),TRIM(t.Surname)) = ''
            OR TRIM(ZIP) = '' or ZIP is null
            OR TRIM(TaxCode) = '' or TaxCode is null
            OR TRIM(BornDate) = '' or BornDate is null
            OR TRIM(BornCountryId) = '' or BornCountryId is null
            OR (BornCountryId='Z000' and (TRIM(BornPlace) = '' or BornPlace is null))
            OR TRIM(t.Address) = '' or t.Address is null ";
    }
    if($Search_DiscrepancyType=="Tutte" || $Search_DiscrepancyType=="Patente")
    {
          $discrepancy.="  OR TRIM(LicenseNumber) = '' or LicenseNumber is null
            OR TRIM(LicenseDate) = '' or LicenseDate is null
            OR TRIM(LicenseCategory) = '' or LicenseCategory is null";
    }
    if($r_Customer['LicensePointPaymentCompletion'] == 0 && ($Search_DiscrepancyType=="Tutte" || $Search_DiscrepancyType=="Data notifica"))
        $discrepancy.=" OR TRIM(NotificationDate) = '' or NotificationDate is null";
    if($Search_DiscrepancyType=="Tutte" || $Search_DiscrepancyType=="Articolo")
        $discrepancy.=" OR LicensePointCode1 is null OR (LicensePointCode2 is null AND Habitual=1)";
    if($Search_DiscrepancyType=="Tutte" || $Search_DiscrepancyType=="Comunicazione")
        $discrepancy.= "OR DocumentTypeId != 1";
    if ($Search_Discrepancy == 0)
          $str_Where .= " AND not ($discrepancy)";
    else if ($Search_Discrepancy == 2)
        $str_Where .= " AND ($discrepancy)";
    $str_Where.= " AND Id not in (select distinct pf.PreviousId from Fine pf Where pf.PreviousId is not null AND StatusTypeId<35)";
    return $str_Where;
}

function processInipecRow(CLS_DB $rs,array $row){
    if(!isset($row[2]))
        return;
    if($row[8]=='OK')
        $pec=$row[7];
    else if($row[18]=='OK')
        $pec=$row[17];
    else $pec=null;
    $codiceFiscale=str_replace('"', '',$row[2]);
    $pec=str_replace('"', '',$pec);
    if ($pec!=null && $pec!= '')
    {
        $rs->Start_Transaction();
        $rs_trespasser=$rs->Select("Trespasser","VatCode='$codiceFiscale' or TaxCode='$codiceFiscale'");
        while ($r_trespasser = mysqli_fetch_array($rs_trespasser)) {
            if($r_trespasser['PEC']!=$pec){
                $rs->ExecuteQuery("insert into TrespasserHistory (TrespasserId,Genre,CompanyName,Surname,Name,
                            Address,StreetNumber, Ladder, Indoor, Plan,ZIP, City, Province, CountryId, BornPlace,
                            BornCountryId,BornDate, TaxCode,ForcedTaxCode,VatCode,Phone,Phone2, Fax, Notes, PEC, UserId,
                            VersionDate, Mail, ZoneId,LanguageId,DeathDate,LandId) 
                            select Id,Genre,CompanyName,Surname,Name,
                            Address,StreetNumber, Ladder, Indoor, Plan,ZIP, City, Province, CountryId, BornPlace,
                            BornCountryId,BornDate, TaxCode,ForcedTaxCode,VatCode,Phone,Phone2, Fax, Notes, PEC, '".
                    $_SESSION['username']."', '".DateInDB(date("d/m/Y")).
                    "', Mail, ZoneId,LanguageId,DeathDate,LandId from Trespasser 
                            where Id={$r_trespasser['Id']}");
                $updateTrespasser=array(array('field' => 'PEC', 'selector' => 'value','type' => 'str', 'value'=>$pec));
                $rs->Update("Trespasser",$updateTrespasser,"Id={$r_trespasser['Id']}");
            }
            $updateRequestPec=array(
                array('field' => 'Pec', 'selector' => 'value', 'type' => 'str', 'value'=>$pec)
            );
            $rs->Update("IniPecRequestPec",$updateRequestPec, "CodiceFiscale='$codiceFiscale' and CityId = '{$r_trespasser['CustomerId']}'");
        }
        $rs->End_Transaction();
    }

}
function addCustomIdCode(CLS_DB $rs,int $trespasserId,string $customerId){
    trigger_error("SELECT IFNULL(MAX(Code)+1, 1) as nextCode from Trespasser group by CustomerId having CustomerId='$customerId'",E_USER_NOTICE);
    $nextCode=mysqli_fetch_array( $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) as nextCode from Trespasser group by CustomerId having CustomerId='$customerId'"));
    trigger_error("Update trespasser code ".$nextCode['nextCode']." customerId $customerId id $trespasserId",E_USER_NOTICE);
    $rs->Begin_Transaction();
    $a_Trespasser=array(
        array('field'=>'CustomerId','selector'=>'value','type'=>'str','value'=>$customerId,'settype'=>'str'),
        array('field'=>'Code','selector'=>'value','type'=>'int','value'=>$nextCode['nextCode'],'settype'=>'int'));
    trigger_error("Update Trespasser set Code=".$nextCode['nextCode'].", CustomerId='$customerId' where Id=$trespasserId and Code=0");

    $rs->ExecuteQuery("Update Trespasser set Code=".$nextCode['nextCode'].", CustomerId='$customerId' where Id=$trespasserId and Code=0");
    $rs->End_Transaction();
}

function createInipecRequestFilter(){
    global $Search_Customer;
    global $Search_UserName;
    global $Search_FromSendDate;
    global $Search_ToSendDate;
    $str_Where = '1=1';
    if($Search_Customer!=null)
        $str_Where.=" and iprp.CityId='$Search_Customer'";
    if($Search_UserName!=null)
        $str_Where.=" and ipr.UserName='$Search_UserName'";
    if($Search_FromSendDate!=null)
        $str_Where.=" and CAST(ipr.DataRichiesta as Date)>='".DateInDB($Search_FromSendDate)."'";
    if($Search_ToSendDate!=null)
        $str_Where.=" and CAST(ipr.DataRichiesta as Date)<='".DateInDB($Search_ToSendDate)."'";
    return $str_Where;
}

function generateHtmlRow(array $values){
    $content="";
    foreach ($values as $value){
        $content.="<td>$value</td>";
    }
    return "<tr>$content</tr>";
}
function getRowsForNext(string $tableName, string $orderBy, string $idField="Id", string $whereOverride=null){
    global $rs;
    global $str_Where;
    if($whereOverride!=null)
        $where=$whereOverride;
    else
        $where=$str_Where;
    $rs_Id = $rs->SelectQuery("SELECT $idField as Id FROM $tableName WHERE $where ORDER BY $orderBy");
    return mysqli_fetch_all($rs_Id,MYSQLI_ASSOC);
}

function getNextId(array $rows,int $currentId, bool $forward=true){
    $previousId=null;
    if($rows==null)
        return null;
    for($i=0;$i<count($rows);$i++){
        $id=$rows[$i]['Id'];
        if($id==$currentId){
            if($forward ) {
                if ($i+1<count($rows))
                    return $rows[$i+1]['Id'];
                else return null;
            } else
                return $previousId;
        } else $previousId=$id;
    }
    return null;
}

/**
 * Salva il file indicato dalla url nel path specificato, il nome del file sarà l' ultima parte della url: http://a.com/nomefile.php => nomefile.php
 * @param string $url la url da cui scaricare il file, viene utilizzata anche per ottenere il nome del file
 * @param string $savePath il path in cui salvare, senza / finale
 * @param string $filename il nome del file che verrà salvato
 * @return void
 */
function downloadFile(string $url,string $savePath, string $filename=null){
    $ch = curl_init($url);
    if($filename==null)
    $filename = basename($url);
    $fp = fopen("$savePath/$filename", 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

function formatBytes(int $size, int $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    
    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}

/**
 * Filtra un array restituendo solo gli indici => valori i cui indici corrispondono all'espressione regolare passata
 * @param string $pattern
 * @param array $input
 * @param int $flags PREG_GREP_INVERT (inverti)
 * @return array
 */
function preg_grep_keys($pattern, $input, $flags = 0) {
    return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
}

/**
 * Valida una data passata secondo il formato passato e ritorna un oggetto DateTime.
 * Es: 
 * @param string $date
 * @param string $format
 * @return DateTime o false se fallisce
 */
function validateDateFormat($date, $format) {
    $dateTime = DateTime::createFromFormat($format, $date);
    $errors = DateTime::getLastErrors();
    if (!empty($errors['warning_count'])) {
        return false;
    }
    return $dateTime;
}

function sortButton($url, $orderFilterName, $selectedOrder, $classUnsort=null, $classAsc=null, $classDesc=null, $style=null){
    $icon = '
        <a href="%s">
            <span data-container="body" data-toggle="tooltip" data-placement="top" title="Ordine: %s"  class="tooltip-r %s" style="%s"></span>
        </a>';
    $classUnsort = empty($classUnsort) ? 'glyphicon glyphicon-sort' : $classUnsort;
    $classAsc = empty($classAsc) ? 'glyphicon glyphicon-sort-by-attributes' : $classAsc;
    $classDesc = empty($classDesc) ? 'glyphicon glyphicon-sort-by-attributes-alt' : $classDesc;
    switch ($selectedOrder){
        case 'unsorted': 
            $class = $classUnsort;
            $param = 'asc';
            $label = 'NESSUNO';
            break;
        case 'asc': 
            $class = $classAsc;
            $param = 'desc';
            $label = 'CRESCENTE';
            break;
        case 'desc': 
            $class = $classDesc;
            $param = 'unsorted';
            $label = 'DECRESCENTE';
            break;
        default: 
            $class = $classUnsort;
            $param = 'asc';
            $label = 'NESSUNO';
            break;
    }
    $newUrl = impostaParametriUrl(array($orderFilterName => $param), $url);
    return sprintf($icon, $newUrl, $label, $class, $style ?? '');
}

/**
 * Restituisce il DateTime della data passata se è nel formato specificato,
 * altrimenti falso.
 * @param $format string
 * @param $date string
 * @return DateTime|bool false
 */
function checkDateString($format, $date){
    If($dateTime = DateTime::createFromFormat($format, $date)){
        if (!empty(DateTime::getLastErrors()['warning_count'])) {
            return false;
        }
        return $dateTime;
    } else return false;
}

/** Determina in base all'ora dell'infrazione se va applicata la sanzione maggiorata per violazione notturna **/
function PrevedeMaggiorazioneXViolazioneNotturna($FineTime){
    $aTime = explode(":",$FineTime);
    
    if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
        return true;
    }
    return false;
}

/** Determina in base alla massa del veicolo se va applicata la sanzione maggiorata per eccedenza massa **/
function PrevedeMaggiorazioneXEccedenzaMassa($VehicleMass){
    if($VehicleMass > MASS) {
        return true;
    }
    return false;
}

//In caso di rateizzazione ripartisce il pagato sulle varie voci di scorporo utilizzando l'apposita funzione
function splitInstallmentAmount($r_Customer,$InstallmentId,$InstallmentRateNumber,$CityId,$FineId,$Amount,&$Fee,&$NotificationFee,&$ResearchFee,&$PercentualFee,&$CanFee,&$CadFee,&$CustomerFee,&$InterestFee){
    global $rs;
    
    $FinePaymentSpecificationType = $r_Customer['FinePaymentSpecificationType'];
    $InstallmentPaymentSpecificationType = $r_Customer['InstallmentPaymentSpecificationType'];
    
    $rs_PaymentRate = $rs->Select("PaymentRate","Id = $InstallmentId");
    $r_PaymentRate = $rs->getArrayLine($rs_PaymentRate);
    $r_PaymentRateNumber = $rs->getArrayLine($rs->Select("PaymentRateNumber","PaymentRateId = $InstallmentId AND RateNumber = $InstallmentRateNumber"));
    $RequestDate = $r_PaymentRate['RequestDate'];
    
    $scorporo = InstallmentPaymentSpecificationType($r_PaymentRateNumber, $Amount, $FineId, $CityId, $RequestDate, $FinePaymentSpecificationType, $InstallmentPaymentSpecificationType);
    
    $Fee = $scorporo['Fee'];
    $NotificationFee = $scorporo['NotificationFee'];
    $ResearchFee = $scorporo['ResearchFee'];
    $PercentualFee = $scorporo['PercentualFee'];
    $CanFee = $scorporo['CanFee'];
    $CadFee = $scorporo['CadFee'];
    $CustomerFee = $scorporo['CustomerFee'];
    $InterestFee = $scorporo['InterestFee'];
    }

/**
 * @param array $r_Customer Il risultato della query sulla tabella Customer o la vista V_Customer 
 * @param string $PrintDestinationFold Restituzione piego in caso di mancato recapito
 * @param int $headerType Tipo di stampa per l'header. Contenuto nell'array legato alla costante BUILDHEADER_TYPES di parameter.php
 * @return string L'header completo
 * **/
function buildHeader($r_Customer, $PrintDestinationFold = null, int $headerType){
        $typeList = unserialize(BUILDHEADER_TYPES);
        $str_Header = '<span style="line-height:1.1">';
        if(!empty($r_Customer['ManagerSector'])){
            $str_Header .= $r_Customer['ManagerSector'];
            $str_Header .= '<br>';
        }
        if (!empty($PrintDestinationFold)){
            $str_Header .= '<span style="font-size:7rem">RESTITUZIONE PIEGO IN CASO DI MANCATO RECAPITO:<br>';
            $str_Header .= strtoupper($PrintDestinationFold).'</span><br>';
        }
        if($_SESSION['cityid']=="H452" && $headerType != $typeList["FOREIGN_FINE"]){
            $str_Header .= 'Art.57 CPP e Art.11 c.1 L.a) e b) CDS<br>';
        } else if(!empty($r_Customer['ManagerAddress'])){
            $str_Header .= $r_Customer['ManagerAddress'].'<br>';
        }
        
        if(!empty($r_Customer['ManagerZIP']) || !empty($r_Customer['ManagerCity']) || !empty($r_Customer['ManagerProvince'])){
            $str_Header .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
            $str_Header .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
            $str_Header .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
            }
            
        switch($headerType){
            //Nazionale e PEC sono praticamente lo stesso caso con la differenza che PEC non ha la restituzione piego mancato recapito
            case $typeList["NATIONAL_FINE"]: {};
            case $typeList["PEC"]: {
                    if(!empty($r_Customer['ManagerPhone'])){
                        $str_Header .= 'TEL: '.$r_Customer['ManagerPhone'];
                    }
                    break;
                };
            case $typeList["FOREIGN_FINE"]: {
                    if(!empty($r_Customer['ManagerMail'])){
                        $str_Header .=  'MAIL: '.$r_Customer['ManagerMail'];
                        $str_Header .= '<br>';
                    }
                    if(!empty($r_Customer['ManagerCountry'])){
                        $str_Header .= $r_Customer['ManagerCountry'];
                    }
                    break;
                };
            case $typeList["REMINDER"]: {
                    if(!empty($r_Customer['ReminderPhone'])){
                        $str_Header .= 'TEL: '.$r_Customer['ReminderPhone'];
                    }
                    break;
                };
            }
            
        return $str_Header.'</span>';
    }
    
    //valutazione prescrizione covid
    //NOTA:
    //in teoria pagamenti fatti tra D_COVID_I e D_COVID_F interromperebbero la prescrizione 
    // ma per ora ce ne freghiamo
    //https://www.studiolegaletributariolai.it/proroga-dei-termini-per-la-notifica-della-cartella/
    //Ipotesi a): Per i termini di prescrizione o decadenza che sarebbero scaduti in data successiva 
    //al periodo di sospensione (cioè dopo il 31 agosto 2021), 
    //si applica unicamente la sospensione per un periodo pari a quello del blocco delle attività di riscossione 
    //(542/543 giorni), come previsto dall’art. 12, comma 1, del D.Lgs. n. 159/2015.
    //https://www.lapostadelsindaco.it/servizi-pubblica-amministrazione/28871/notifica-atti-di-ingiunzione-relativi-a-mancato-pagamento-di-sanzioni-amministrative-per-violazioni-al-cds
    /*L’articolo prolunga di due anni il termine di decadenza/prescrizione dei provvedimenti in scadenza al 
     * 31 dicembre degli anni in cui è stata decisa la sospensione (2020 e 2021) mentre, 
     * per le ingiunzioni di pagamento non in scadenza nel 2020 e 2021, per le quali il relativo termine di 
     * decadenza era pendente alla data dell’8 marzo 2020, possono intendersi prorogate, 
     * per quanto disposto dal comma 1 dell’art. 12 del D.Lgs. 159/2015 richiamato dall’art. 67 del D.L. 18/2020, 
     * ovvero per la durata del periodo di sospensione ovvero pari, in questo caso, a 542 giorni.
    */
    //in virtù di questo commento controllo solo la data di notiifca, se valesse l'altro articolo dovrei valutare anche 
    //la data di prescrizione secondo l'ipotesi a) con questa condizione "&& $PrescriptionDateTemp >= D_COVID_F"
    //ma gli esempi non sono chiari e parlano di un caso in cui la proroga è due anni
    /*https://www.lexced.com/giurisprudenza-civile/prescrizione-cartella-stop-del-giudice-per-covid/
    *per verificare la prescrizione cartella, non è sufficiente considerare in blocco il periodo di sospensione Covid. 
    *È necessario calcolare con precisione la data di scadenza del termine di prescrizione per ogni singolo credito. 
    *Se tale data cade prima dell’8 marzo 2020, il debito è da considerarsi estinto e qualsiasi successiva richiesta 
    *di pagamento è illegittima. Se, invece, la scadenza cadeva all’interno o dopo il periodo di sospensione, 
    *è molto probabile che le proroghe legislative abbiano salvato il credito, 
    *rendendo vana l’eccezione di prescrizione*/
    // secgliamo di ragionare solo sulla data di notifica andando a ritroso nel tempo 
    function AggiornaPrescizionePerSospensioneCovid($PrescriptionDateTemp, $NotificationDate, $d_inizio_covid, $d_fine_covid){
        
        if ($NotificationDate >= $d_inizio_covid && $NotificationDate <= $d_fine_covid){
            $PrescriptionDateTemp = date('Y-m-d', strtotime($PrescriptionDateTemp. ' + '. PRESCRIPTION_COVID_DAYS));
        }
        return $PrescriptionDateTemp;
    }
    
    //valutazione data elaborazione in caso di sospensione per Covid
    //se ho aumentato il periodo di prescrizione devo decrementare la data di elaborazione dei giorni di sospensione per calcolare i semestri di maggiorane
    //NOTA:
    //in teoria pagamenti fatti tra D_COVID_I e D_COVID_F interromperebbero la prescrizione
    // ma per ora ce ne freghiamo
    //https://www.studiolegaletributariolai.it/proroga-dei-termini-per-la-notifica-della-cartella/
    function AnticipaDataElaborazionePerSospensioneCovid($ProcessingDate, $NotificationDate, $d_inizio_covid, $d_fine_covid){
        
        if ($NotificationDate >= $d_inizio_covid && $NotificationDate <= $d_fine_covid){
            $ProcessingDate = date('Y-m-d', strtotime($ProcessingDate. ' - '. PRESCRIPTION_COVID_DAYS));
        }
        return $ProcessingDate;
    }
    
    /* per semplificare le condizioni stimo la data di notifica di un atto che scade nella sospensione Covid */
    function DeterminaDataNotificaMinima($DataInizioSospensione, $Nationality){
        //VALUTAZIONE PRESCRIZIONE
        //Se la data di notifica avanti di 5 anni (5 anni + 270 gg per estero) + i giorni di ricorso
        // + shift per festività è < $ProcessingDate siamo in prescrizione
        $DataInizioSospensione = date('Y-m-d', strtotime($DataInizioSospensione. ' - '. PRESCRIPTION_YEARS));
        if($Nationality==cls_pagamenti::ESTERO){
            // per l'estero la prescrizione va valutata su 5 anni + 270 gg
            $DataInizioSospensione = date('Y-m-d', strtotime($DataInizioSospensione. ' - '. PRESCRIPTION_FOREIGN_DAYS));
        }  
    }