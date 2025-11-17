<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$a_LegalFormIndividual = unserialize(LEGALFORM_INDIVIDUALCOMPANY);

$rs= new CLS_DB();
$rs->SetCharset('utf8');
$strTrespasser = "";
$str_Where = "";

$Genre = CheckValue('Genre','s');

$InputField = CheckValue('InputField','obj');
$InputName = CheckValue('InputName','obj');

$VehiclePlate = CheckValue('VehiclePlate','s');
$Id = CheckValue('Id','n');
$FineDate = CheckValue('FineDate','s');

$strTrespasser .='
	<div class="table_label_H col-sm-5">Nominativo</div>
	<div class="table_label_H col-sm-3">Citta</div>
	<div class="table_label_H col-sm-3">Stato</div>
	<div class="table_label_H col-sm-1">&nbsp;</div>
';

if($Genre=="D"){
    $CompanyName = $_POST['CompanyName'];
    $str_Where = "Genre = 'D'";
    $str_Where .= " AND CompanyName LIKE '%".addslashes($CompanyName)."%'";
}else{
    $Surname = $_POST['Surname'];
    $Name = $_POST['Name'];
    $str_Where = "(Genre != 'D' OR (Genre = 'D' AND LegalFormId IN(".implode(',', $a_LegalFormIndividual).")))";

    if(trim(strlen($Surname))>0) $str_Where .= " AND Surname LIKE '".addslashes($Surname)."%'";
    if(trim(strlen($Name))>0) $str_Where .= " AND Name LIKE '".addslashes($Name)."%'";
}

$str_Where.= " AND CustomerId='".$_SESSION['cityid']."'";
$trespassers = $rs->Select('V_Trespasser',$str_Where,"CompanyName, Surname, Name");
$Number = mysqli_num_rows($trespassers);
$a_trespasser = array();
if($Number==0) {
    $strTrespasser .= '<div class="table_caption_H col-sm-12">Nessun nominativo trovato</div>  ';
}else{

    while($trespasser = mysqli_fetch_array($trespassers)){
        foreach ($trespasser as $i => $value) {
            if ($value === null) $trespasser[$i] = "";
        }
        $a_trespasser[$trespasser['Id']] = $trespasser;
        $NameOut = substr((!empty($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' '. $trespasser['Name'],0,25);
        $Genre = in_array($trespasser['LegalFormId'], $a_LegalFormIndividual) ? 'DI' : $trespasser['Genre'];
        $n_Age = 0;
        
        if($trespasser['BornDate']!='' && $FineDate != ''){
            $n_Age = DateDiff('Y', $trespasser['BornDate'], DateInDB($FineDate));
        }

        $strTrespasser .= '		
        <div class="table_caption_H col-sm-5">'.$NameOut.'</div>   
        <div class="table_caption_H col-sm-3">'.$trespasser['City'].'</div>
        <div class="table_caption_H col-sm-3">'.$trespasser['CountryTitle'].'</div>
        <div class="table_caption_H col-sm-1"><a href="#">
        <span class="fa fa-share" data-genre="'.$Genre.'" data-age="'.$n_Age.'" data-id="' . $trespasser['Id'] . '" data-alt="'.(!empty($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' '. $trespasser['Name'].'"
        ></span>
        </a></div>		
    ';
    }
}

$strTrespasser .= '
    <script>
        var inputName = '.$InputName.';
        var inputField = '.$InputField.';
        var a_trespasser = '.json_encode($a_trespasser).';
    $(".fa-share").click(function(){
        var id = $(this).data("id");
        var name = $(this).data("alt");
        var age = $(this).data("age");
        var genre = $(this).data("genre"); 

        for(var i=0;i<Object.keys(inputName).length;i++){
            var string = inputField[i].toLowerCase();
            expr = /date/;
            
            if(string.match(expr)){
                var dateString = a_trespasser[id][inputField[i]];
                a_trespasser[id][inputField[i]] = dateString.replace(/(\d{4})-(\d{2})-(\d{2})/, \'$3/$2/$1\');
            }
            
            $("[name="+inputName[i]+"]").val(a_trespasser[id][inputField[i]]);
        }

        $(document).trigger("TrespasserAdd", {Id:id, Name:name, Age:age, Genre:genre});
    
        return false;
    });
    </script>
    ';

echo json_encode(
	array(
		"Trespasser" => $strTrespasser
		)
);
