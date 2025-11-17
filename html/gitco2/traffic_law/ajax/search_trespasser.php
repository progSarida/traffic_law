<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset('utf8');
$strTrespasser = "";
$strPlate = "";
$str_Where = "";

$Genre = CheckValue('Genre','s');
$VehiclePlate = CheckValue('VehiclePlate','s');
$Id = CheckValue('Id','n');
$CountryId = CheckValue('CountryId','s');


$AddTrespasser = CheckValue('AddTrespasser','n');



$strTrespasser .='
	<div class="table_label_H col-sm-5">Nominativo</div>
	<div class="table_label_H col-sm-2">C.F / P.IVA</div>
	<div class="table_label_H col-sm-2">Citta</div>
	<div class="table_label_H col-sm-3">Stato</div>
';

$strPlate .= '
	<div class="table_label_H col-sm-12">Altri verbali con la stessa targa</div>
	<div class="table_label_H col-sm-2">Assegna</div>
	<div class="table_label_H col-sm-2">Codice</div>
	<div class="table_label_H col-sm-2">Data</div>
	<div class="table_label_H col-sm-2">Ora</div>
	<div class="table_label_H col-sm-4">Località</div>

';

if($AddTrespasser==0){
    if($Genre=="D"){
        $CompanyName = $_POST['CompanyName'];
        $str_Where = "Genre = 'D'";
        $str_Where .= " AND CompanyName LIKE '%".addslashes($CompanyName)."%'";
    }else{
        $Surname = $_POST['Surname'];
        $Name = $_POST['Name'];
        $str_Where = "Genre != 'D'";

        if(trim(strlen($Surname))>0) $str_Where .= " AND Surname LIKE '".addslashes($Surname)."%'";
        if(trim(strlen($Name))>0) $str_Where .= " AND Name LIKE '".addslashes($Name)."%'";

    }
    
    //Se l'utente ha un valore di permessi <=50 o la targa è Italiana
    if($_SESSION['usertype']<=50 || $CountryId == 'Z000'){
        $str_Where.=" AND CustomerId='".$_SESSION['cityid']."' ";
    }

    $trespassers = $rs->Select('V_Trespasser',$str_Where,"CompanyName, Surname, Name");
    $Number = mysqli_num_rows($trespassers);


    if($Number==0) {
        $strTrespasser .= '<div class="table_caption_H col-sm-12">Nessun nominativo trovato</div>  ';
    }else{
        while($trespasser = mysqli_fetch_array($trespassers)){
            $NameOut = substr($trespasser['CompanyName']." ".$trespasser['Surname']." ".$trespasser['Name'],0,25);

            $strTrespasser .= '		
			<div class="table_caption_H col-sm-5"><a href="#"><span class="fa fa-share" id="' . $trespasser['Id'] . '" alt="' . $trespasser['CompanyName'] ." ".$trespasser['Surname']." ".$trespasser['Name'].'"></span></a> '.$NameOut.'</div>   
			<div class="table_caption_H col-sm-2">'.$trespasser['TaxCode'].'</div>
			<div class="table_caption_H col-sm-2">'.$trespasser['City'].'</div>
			<div class="table_caption_H col-sm-3">'.$trespasser['CountryTitle'].'</div>
		';
        }
    }

    $strTrespasser .= '
        <script>
        $(".fa-share").click(function(){
            var id = $(this).attr("id");
            var name = $(this).attr("alt");
        
            $("#span_name").html(name);
            $("#TrespasserId").val(id);
        
            $("#update").prop("disabled", false);
             return false;
        });
        </script>
        ';

}




$plates = $rs->Select('V_Fine',"Id != $Id AND VehiclePlate='".$VehiclePlate."' AND StatusTypeId<10 AND TrespasserId IS NULL","Id DESC");
$Number = mysqli_num_rows($plates);


if($Number==0) {
	$strPlate .= '<div class="table_caption_H col-sm-12">Nessun\'altra infrazione da assegnare</div>  ';
}else{
	while($plate = mysqli_fetch_array($plates)){
		$strPlate .= '		
			<div class="table_caption_H col-sm-2"><input type="checkbox" name="checkbox[]" value="' . $plate['Id'] . '" checked /></div>
			<div class="table_caption_H col-sm-2">'.$plate['Code'].'</div>   
			<div class="table_caption_H col-sm-2">'.DateOutDB($plate['FineDate']).'</div>
			<div class="table_caption_H col-sm-2">'.TimeOutDB($plate['FineTime']).'</div>
			<div class="table_caption_H col-sm-4">'.$plate['Address'].'</div>

		';
	}
}


echo json_encode(
	array(
		"Trespasser" => $strTrespasser,
		"Plate" => $strPlate
		)
);
