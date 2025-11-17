<?php
function curPageName() {
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}
function YesNoOutDB($val){

    $a_Response = array("NO", "SI");
    return $a_Response[$val];
}



function CheckValue($val, $t){

	if (isset($_REQUEST[$val])) {
		$val = $_REQUEST[$val];
		if(!is_numeric($val) && $t=="n") $val=0;
	}
	else{
		if($t=="n"){
			$val = 0;	
		}
		else{
			$val = "";
		}

	}
	return $val;
}


function DateInDB($d){
	$aD = explode('/',$d);

	$d = $aD[2]."-".$aD[1]."-".$aD[0];

	return $d;
}

function DateOutDB($d){
	if($d!=null || $d!=""){
		$aD = explode('-',$d);

		$d = $aD[2]."/".$aD[1]."/".$aD[0];
	}

	return $d;
}
function TimeOutDB($t){
	$aT = explode(':',$t);

	$t = $aT[0].":".$aT[1];

	return $t;

}
function StringOutDB($s){

	return utf8_encode($s);

}




function NumberDisplay($f){

	$f = number_format($f, 2, ',', '.');
	return $f;
}
function NumberDisplayXls($f){

	$f = str_replace(".",",",$f);
	return $f;
}



function CreateSelectCustomerUnion($Search_Locality){

    $rs_Function= new CLS_DB();


    $rs_Union = $rs_Function->Select('V_Customer',"CityId='".$_SESSION['cityid']."'");
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




function CreateSelectConcat($query, $selectname, $fieldid, $fieldvalue, $selectvalue,$required,$size=null, $class=null){

	$rs= new CLS_DB();
	$rows = $rs->SelectQuery($query);

    $str_add_class = ($class!=null) ? " ".$class : "";

	$str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectname.'" ';
    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
    $str.= '>';
	if(!$required) $str.='<option></option>';
	while($row = mysqli_fetch_array($rows)) {
		$str.='<option value="'.$row[$fieldid].'"';
		if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
		$str.='>'.$row[$fieldvalue].'</option>';
	}
	$str.='</select>';

	return $str;
}


function CreateSelect($table, $where, $order, $selectname, $fieldid, $fieldvalue, $selectvalue,$required, $size=null, $class=null){

	$rs= new CLS_DB();
	$rows = $rs->Select($table,$where,$order);

    $str_add_class = ($class!=null) ? " ".$class : "";

	$str = '<select class="form-control'.$str_add_class.'" name="'.$selectname.'" id="'.$selectname.'" ';
    if($size!=null)
        $str.= 'style="width:'.$size.'rem"';
    $str.= '>';

	if(!$required) $str.='<option></option>';
	while($row = mysqli_fetch_array($rows)) {
		$str.='<option value="'.$row[$fieldid].'"';
		if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
		$str.='>'.$row[$fieldvalue].'</option>';

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
		$str.='>'.substr($row[$fieldvalue],0,150).'...</option>';

	}
	$str.='</select>';

	return $str;
}
function CreateSelectQuery($query, $selectname, $fieldid, $fieldvalue, $selectvalue,$required){

	$rs= new CLS_DB();
	$rows = $rs->SelectQuery($query);
	$str = '<select name="'.$selectname.'" id="'.$selectname.'">';

	if(!$required) $str.='<option></option>';
	while($row = mysqli_fetch_array($rows)) {
		$str.='<option value="'.$row[$fieldid].'"';
		if ($selectvalue==$row[$fieldid]) $str.=' SELECTED ';
		$str.='>'.$row[$fieldvalue].'</option>';

	}
	$str.='</select>';

	return $str;
}




function CreateTxtChangeJQ($table, $where, $order, $changename, $fieldid, $fieldvalue, $spanview){

	$rs= new CLS_DB();
	$rows = $rs->Select($table,$where,$order);

	$str ='var a_'.$changename.' = {';
	while($row = mysqli_fetch_array($rows)) {
		$str.='"'.$row[$fieldid].'":"'.$row[$fieldvalue].'", ';
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
		$str.='"'.$row[$fieldid].'":"'.$row[$fieldvalue].'", ';
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















function CreatePagination($numberforpage, $totalnumber, $currentpage,$href,$legend){


	$TotPage = ($totalnumber<$numberforpage) ? 0 : number_format(($totalnumber / $numberforpage),0,".","");


	$an = $currentpage*$numberforpage + 1;
	$fn = $an + $numberforpage - 1;

	$strPagination = '
              <div class="row-fluid">
                <div class="table_label_H col-sm-12" style="height:8rem;">
			  		<ul class="pagination pagination-sm">';


	$strPagination .= '<li';
	if($currentpage==0) $strPagination .= ' class="disabled"';
	$strPagination .= '><a href="'.$href.'&page=0">&laquo;</a></li>';

	$startpage = (($currentpage-2)>0) ? $currentpage-2 : 0;
	$endpage = (($currentpage-2)>0) ? $currentpage+3 : 5;

	for($i=$startpage;$i<$endpage;$i++){
		$displaypage = $i+1;
		$strPagination .= '<li';
		if($currentpage==$i) $strPagination .= ' class="active"';
		if($i>$TotPage) $strPagination .= ' class="disabled" ><a href="#">'.$displaypage.'</a></li>';
		else $strPagination .= '><a href="'.$href.'&page='.$i.'">'.$displaypage.'</a></li>';
	}
	$strPagination .= '<li';
	if($currentpage==$TotPage) $strPagination .= ' class="disabled"';
	$strPagination .= '><a href="'.$href.'&page='.$TotPage.'">&raquo;</a></li>
			  </ul>

			' . $legend. '
			</div>
				<div class="totnav">
					'.$an.'-'.$fn.' di '.$totalnumber.'
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

function ErrorAlert($msgType,$msgText){
	// $msgType success(verde), info(azzurro), warning(giallo), danger(rosso)
	echo "<div class='alert alert-".$msgType."'>".$msgText."</div>";
	die;
}

function ToFloat($num) {
	$dotPos = strrpos($num, '.');
	$commaPos = strrpos($num, ',');
	$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
		((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

	if (!$sep) {
		return floatval(preg_replace("/[^0-9]/", "", $num));
	}

	return floatval(
		preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
		preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
	);
}

function DivTrespasserView($trespasser_row, $txt){

    $a_DocumentTypeId = array("","Patente","Carta identità","Passaporto","Altro");

	if($trespasser_row['BornDate']!=null)
		$bornDate = date('d/m/Y', strtotime($trespasser_row['BornDate']));
	else
		$bornDate = "";

	$strTrespasser = '
    <div class="col-sm-12 BoxRow">
            <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    ' . $txt . ' NON ASSOCIATO
                </div>
            </div>
            <div class="clean_row HSpace4"></div> 
	</div>';
	if($trespasser_row['Id']!=null) {

	    $str_Date = ($trespasser_row['LicenseDate']!="") ? DateOutDB($trespasser_row['LicenseDate']) : "";

        $strTrespasser = '
            <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    ' . $txt . '
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-3 BoxRowLabel">
                    Nominativo
                </div>
                <div class="col-sm-9 BoxRowCaption">
                    ' . $trespasser_row['CompanyName'] . ' ' . $trespasser_row['Surname'] . ' '. $trespasser_row['Name'] . '
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-3 BoxRowLabel">
                    Indirizzo
                </div>
                <div class="col-sm-9 BoxRowCaption">
                    '.$trespasser_row['Address'].'
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
			<div class="col-sm-12">
				<div class="col-sm-1 BoxRowLabel">
					Cap
				</div>
				<div class="col-sm-2 BoxRowCaption">
					'.$trespasser_row['ZIP'].'
				</div>
				<div class="col-sm-1 BoxRowLabel">
					Città
				</div>
				<div class="col-sm-4 BoxRowCaption">
					'.$trespasser_row['City'].'
				</div>
				<div class="col-sm-2 BoxRowLabel">
					Provincia
				</div>
				<div class="col-sm-2 BoxRowCaption">
					'.$trespasser_row['Province'].'
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="col-sm-12">
				<div class="col-sm-3 BoxRowLabel">
					Paese
				</div>
				<div class="col-sm-9 BoxRowCaption">
					'.$trespasser_row['CountryTitle'].'
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="col-sm-12">
				<div class="col-sm-2 BoxRowLabel">
					Mail
				</div>
				<div class="col-sm-4 BoxRowCaption">
					'.$trespasser_row['Mail'].'
				</div>
				<div class="col-sm-2 BoxRowLabel">
					Telefono
				</div>
				<div class="col-sm-4 BoxRowCaption">
					'.$trespasser_row['Phone'].'
				</div>
			</div>
			<div class="clean_row HSpace4"></div>    
            <div class="col-sm-12">
                <div class="col-sm-3 BoxRowLabel">
                    Documento
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '.$a_DocumentTypeId[$trespasser_row['DocumentTypeId']].'
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Numero
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '.$trespasser_row['LicenseNumber'].'
                </div>
            </div>
            <div class="clean_row HSpace4"></div>    
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Data
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.$str_Date.'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Categoria
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.$trespasser_row['LicenseCategory'].'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Rilascio
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '.$trespasser_row['LicenseOffice'].'
                </div>
            </div>          
            
 	 	 	 	
        ';
	}
	return $strTrespasser;
}




function DivTrespasserUpdate($trespasser_row, $txt){

	if($trespasser_row['BornDate']!=null)
		$bornDate = date('d/m/Y', strtotime($trespasser_row['BornDate']));
	else
		$bornDate = "";

	$strTrespasser = '
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">
        	TRASGRESSORE NON ASSOCIATO
		</div>
	</div>';
	if($trespasser_row['Id']!=null) {

        $activeCompany = "";
        $activeTrespasser = "";
        $checkM = "";
        $checkF = "";
        if ($trespasser_row['Genre'] == "D")
            $activeCompany = "active";
        else {
            $activeTrespasser = "active";
            if ($trespasser_row['Genre'] == "M")
                $checkM = "CHECKED";
            else
                $checkF = "CHECKED";
        }


        $strTrespasser = '
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    ' . $txt . '
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12" style="height:4rem">
                <div class="col-sm-12" style="height:4rem">
                <input type="hidden" name="TrespasserId" value="'.$trespasser_row['Id'].'">
                <input type="hidden" name="Genre" id="Genre" value="'.$trespasser_row['Genre'].'">
                    <ul class="nav nav-tabs" id="mioTab" style="background-color: #dfe7e7">
                        <li class="' .$activeCompany.'" id="tab_company"><a href="#company" data-toggle="tab">DITTA</a></li>
                        <li class="'.$activeTrespasser.'" id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">PERSONA</a></li>
                    </ul>
                </div>
            </div> 
          
			<div class="tab-content">
				<div class="tab-pane '.$activeCompany.'" id="company">
					<div class="row-fluid">
						<div class="col-sm-12">
							<div class="col-sm-3 BoxRowLabel">
								Ragione sociale
							</div>
							<div class="col-sm-9 BoxRowCaption">
								<input name="CompanyName" class="form-control frm_field_string" type="text" style="width:40rem" value="'.$trespasser_row['CompanyName'].'">
							</div>
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
				</div>
				<div class="tab-pane '.$activeTrespasser. '" id="Trespasser">
					<div class="row-fluid">
						<div class="col-sm-12">
							<div class="col-sm-3 BoxRowLabel">
								Sesso
							</div>
							<div class="col-sm-9 BoxRowCaption" >
							    <div style="position:relative;top:-0.4rem">
                                    <input style="font-size:1rem;line-height: 1rem;" type="radio" value="M" name="Sex" id="sexM" ' .$checkM.'>M &nbsp; 
                                    <input style="font-size:1rem;line-height: 1rem;" type="radio" id="sexF" value="F" name="Sex" '.$checkF.'>F
							    </div>
							</div>
						</div>
					</div>
					<div class="clean_row HSpace4"></div>

					<div class="col-sm-12">
						<div class="col-sm-2 BoxRowLabel">
							Cognome
						</div>
						<div class="col-sm-4 BoxRowCaption">
							<input type="text" class="form-control frm_field_string" name="Surname" style="width:15rem" value="' . $trespasser_row['Surname'] . '">
						</div>
						<div class="col-sm-2 BoxRowLabel">
							Nome
						</div>
						<div class="col-sm-4 BoxRowCaption">
							<input type="text" class="form-control frm_field_string" name="Name" style="width:15rem" value="' . $trespasser_row['Name'] . '">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>

					<div class="col-sm-12">
						<div class="col-sm-3 BoxRowLabel">
							Data di nascita
						</div>
						<div class="col-sm-3 BoxRowCaption">
							<input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:10rem" value="' . $bornDate . '">
						</div>
						<div class="col-sm-3 BoxRowLabel">
							Luogo di nascita
						</div>
						<div class="col-sm-3 BoxRowCaption">
							<input class="form-control frm_field_string" type="text" name="BornPlace" id="BornPlace" style="width:15rem" value="' . $trespasser_row['BornPlace'] . '">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
					
					<div class="col-sm-12">
						<div class="col-sm-3 BoxRowLabel">
							Codice fiscale
						</div>
						<div class="col-sm-9 BoxRowCaption">
							<input class="form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="width:20rem; text-transform:uppercase" value="' . $trespasser_row['TaxCode'] . '">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
				</div>
			</div>

			<div class="col-sm-12">
				<div class="col-sm-3 BoxRowLabel">
					Indirizzo
				</div>
				<div class="col-sm-9 BoxRowCaption">
					<input class="form-control frm_field_string" type="text" name="Address" style="width:40rem" value="'.$trespasser_row['Address'].'">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>

			<div class="col-sm-12">
				<div class="col-sm-1 BoxRowLabel">
					Cap
				</div>
				<div class="col-sm-2 BoxRowCaption">
					<input type="text" class="form-control frm_field_string" name="ZIP" style="width:7rem" value="'.$trespasser_row['ZIP'].'">
				</div>
				<div class="col-sm-1 BoxRowLabel">
					Città
				</div>
				<div class="col-sm-4 BoxRowCaption">
					<input class="form-control frm_field_string" type="text" name="City" style="width:20rem" value="'.$trespasser_row['City'].'">
				</div>
				<div class="col-sm-2 BoxRowLabel">
					Provincia
				</div>
				<div class="col-sm-2 BoxRowCaption">
					<input class="form-control frm_field_string" type="text" name="Province" style="width:8rem" value="'.$trespasser_row['Province'].'">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>

			<div class="col-sm-12">
				<div class="col-sm-3 BoxRowLabel">
					Paese
				</div>
				<div class="col-sm-9 BoxRowCaption">
					'.CreateSelect("Country","1=1","Title","TrespasserCountryId","Id","Title",$trespasser_row['CountryId'],false, 40,"frm_field_required"). '
				</div>
			</div>
			<div class="clean_row HSpace4"></div>

			<div class="col-sm-12">
				<div class="col-sm-2 BoxRowLabel">
					Mail
				</div>
				<div class="col-sm-4 BoxRowCaption">
					<input type="text" class="form-control frm_field_string" name="Mail" style="width:15rem" value="'.$trespasser_row['Mail'].'">
				</div>
				<div class="col-sm-2 BoxRowLabel">
					Telefono
				</div>
				<div class="col-sm-4 BoxRowCaption">
					<input type="text" class="form-control frm_field_string" name="Phone" style="width:15rem" value="'.$trespasser_row['Phone'].'">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
        ';
	}
	return $strTrespasser;
}

/**
 * @param $a_FifthField ( City, Service, Id, Year )
 * @return string
 */
function SetFifthField($a_FifthField, $rate = 1)
{
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

	//CAMPO DI SCORTA 1 CIFRA per ora impostato a 0
	//TODO Campo da gestire
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


/**
 *
 * @param int $FinePaymentSpecificationType
 * @param int $PaymentDocumentId
 * @param int $PaymentDirect
 * @param float $Amount
 * @param int $FineId
 * @param int $Year
 *
 * @return array $a_Fee
 */
function FinePaymentSpecificationType($FinePaymentSpecificationType, $PaymentDocumentId, $PaymentDirect, $Amount, $FineId, $Year){
    $rs_Func= new CLS_DB();

    $rs_Row = $rs_Func->SelectQuery("
        SELECT  
        FA.Fee,
        FA.MaxFee,
        
        ArT.ReducedPayment,
        
        FH.NotificationTypeId,
        FH.CustomerNotificationFee,
        FH.CustomerResearchFee,
        FH.NotificationFee,
        FH.CanFee, 	
        FH.CadFee,
        FH.NotifierFee,
        FH.OtherFee,
        FH.ResearchFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId 
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=" . $FineId . " AND NotificationTypeId=6 AND ArT.Year=" . $Year);

    $r_Row = mysqli_fetch_array($rs_Row);


    $NotificationFee            = $r_Row['NotificationFee'] + $r_Row['NotifierFee'] + $r_Row['OtherFee'];
    $ResearchFee                = $r_Row['ResearchFee'];

    $CustomerNotificationFee    = $r_Row['CustomerNotificationFee'];
    $CustomerResearchFee        = $r_Row['CustomerResearchFee'];

    $CanFee                     = $r_Row['CanFee'];
    $CadFee                     = $r_Row['CadFee'];


    if ($PaymentDocumentId == 0) {
        $Fee = number_format(($r_Row['Fee'] * FINE_PARTIAL), 2);
    } else if ($PaymentDocumentId == 1) {
        $Fee = $r_Row['Fee'];
    } else {
        $Fee = $r_Row['MaxFee']/2;
    }

    $AdditionalFee = $NotificationFee + $ResearchFee + $CustomerNotificationFee + $CustomerResearchFee + $CanFee + $CadFee;
    $CurrentFee = $r_Row['Fee'];
    $CurrentMaxFee = $r_Row['MaxFee'];

    if ($PaymentDirect) {
        $CustomerNotificationFee    = 0.00;
        $CustomerResearchFee        = 0.00;
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
                $CustomerNotificationFee    = 0.00;
                $CustomerResearchFee        = 0.00;
                $ResearchFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee) < 0) {
                $ResearchFee                = number_format(($Amount - $Fee), 2, '.', '');
                $CustomerNotificationFee    = 0.00;
                $CustomerResearchFee        = 0.00;
                $NotificationFee            = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerNotificationFee) < 0) {
                $CustomerNotificationFee    = $Amount - $Fee - $ResearchFee;
                $NotificationFee            = 0.00;
                $CustomerResearchFee        = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee) < 0) {
                $CustomerResearchFee = $Amount - $Fee - $ResearchFee - $CustomerNotificationFee;
                $NotificationFee            = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee - $NotificationFee) < 0) {
                $NotificationFee            = number_format(($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee), 2, '.', '');
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            }  else if (($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee - $NotificationFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee - $NotificationFee), 2, '.', '');
                $CadFee                     = 0.00;
            }  else if (($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee - $NotificationFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $Fee - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee - $NotificationFee - $CanFee), 2, '.', '');
            } else {
                $Fee = number_format(($Amount - $ResearchFee - $CustomerNotificationFee - $CustomerResearchFee - $NotificationFee - $CanFee - $CadFee), 2, '.', '');
            }
        } else if ($FinePaymentSpecificationType == 2) {

            if ($Amount < $NotificationFee) {
                $NotificationFee = $Amount;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CustomerNotificationFee    = 0.00;
                $CustomerResearchFee        = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $NotificationFee - $CustomerNotificationFee) < 0) {
                $CustomerNotificationFee    = $Amount - $NotificationFee;
                $CustomerResearchFee        = 0.00;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerNotificationFee - $CanFee) < 0) {
                $CanFee                     = number_format(($Amount - $NotificationFee -$CustomerNotificationFee), 2, '.', '');
                $CustomerResearchFee        = 0.00;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee) < 0) {
                $CadFee                     = number_format(($Amount - $NotificationFee -$CustomerNotificationFee - $CanFee), 2, '.', '');
                $CustomerResearchFee        = 0.00;
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee- $CustomerResearchFee) < 0) {
                $CustomerResearchFee        = number_format(($Amount - $NotificationFee - $CustomerNotificationFee), 2, '.', '');
                $Fee                        = 0.00;
                $ResearchFee                = 0.00;
            } else if (($Amount - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee- $CustomerResearchFee - $ResearchFee) < 0) {
                $ResearchFee = number_format(($Amount - $NotificationFee - $CustomerNotificationFee - $CustomerResearchFee), 2, '.', '');
                $Fee = 0.00;
            } else {
                $Fee = number_format(($Amount - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee - $CustomerResearchFee - $ResearchFee), 2, '.', '');
            }
        } else {
            if ($Amount < $ResearchFee) {
                $ResearchFee                = $Amount;
                $Fee                        = 0.00;
                $NotificationFee            = 0.00;
                $CustomerNotificationFee    = 0.00;
                $CustomerResearchFee        = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee) < 0) {
                $Fee                        = number_format(($Amount - $ResearchFee), 2, '.', '');
                $NotificationFee            = 0.00;
                $CustomerNotificationFee    = 0.00;
                $CustomerResearchFee        = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee) < 0) {
                $NotificationFee = $Amount - $ResearchFee - $Fee;
                $CustomerNotificationFee    = 0.00;
                $CustomerResearchFee        = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee) < 0) {
                $CustomerNotificationFee = $Amount - $ResearchFee - $Fee - $NotificationFee;
                $CustomerResearchFee        = 0.00;
                $CanFee                     = 0.00;
                $CadFee                     = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee - $CanFee) < 0) {

                $CanFee = number_format(($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee), 2, '.', '');

                $CustomerResearchFee        = 0.00;
                $CadFee                     = 0.00;
            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee) < 0) {
                $CadFee = number_format(($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee - $CanFee), 2, '.', '');
                $CustomerResearchFee        = 0.00;

            } else if (($Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee - $CustomerResearchFee) < 0) {
                $CustomerResearchFee = $Amount - $ResearchFee - $Fee - $NotificationFee - $CustomerNotificationFee;
            } else {
                $Fee = number_format(($Amount - $NotificationFee - $CustomerNotificationFee - $CanFee - $CadFee - $CustomerResearchFee - $ResearchFee), 2, '.', '');
            }
        }
    }

    $a_Fee = array(
        "CanFee" => $CanFee,
        "CadFee" => $CadFee,
        "CustomerNotificationFee" => $CustomerNotificationFee,
        "CustomerResearchFee" => $CustomerResearchFee,
        "NotificationFee" => $NotificationFee,
        "ResearchFee" => $ResearchFee,
        "Fee" => $Fee,
        "ReducedPayment" => $r_Row['ReducedPayment'],
        "AdditionalFee" => $AdditionalFee,
        "CurrentFee" => $CurrentFee,
        "CurrentMaxFee" => $CurrentMaxFee,
    );
    return $a_Fee;
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

    foreach($a_Resp ['dettaglioAutoveicoloComproprietariResponse']["dettaglioVeicoloComproprietariOutput"] AS $key1 => $val1) {
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
    return $a_Res;
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