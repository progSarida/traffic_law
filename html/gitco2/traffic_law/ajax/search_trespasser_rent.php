<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset('utf8');
$strTrespasser = "";

$str_Where = "";


$a_Tutor = array();
$a_Tutor[10]=15;
$a_Tutor[11]=16;
$a_Tutor[12]=17;


$Genre = CheckValue('Genre','s');
$NumberTab = CheckValue('NumberTab','n');
$tableName = $Genre == "D"? "Partita Iva": "Codice Fiscale";
$AddTrespasser = CheckValue('AddTrespasser','n');
$FineDate = CheckValue('FineDate','s');
$TaxCode = CheckValue('TaxCode','s');
$CountryId = CheckValue('CountryId','s');

$strTrespasser .='
	<div class="table_label_H col-sm-3">Nominativo</div>
	<div class="table_label_H col-sm-3">Citta</div>
	<div class="table_label_H col-sm-3">Stato</div>
	<div class="table_label_H col-sm-3">'.$tableName.'</div>
	
';



if($AddTrespasser==0){
    if($Genre=="D"){
        $CompanyName = CheckValue('CompanyName','s');
        $str_Where = "Genre = 'D'";
        $str_Where .= " AND CompanyName LIKE '%".addslashes($CompanyName)."%'";
    }else{
        $Surname = CheckValue('Surname','s');
        $Name = CheckValue('Name','s');
        $str_Where = "Genre != 'D'";

        if(trim(strlen($Surname))>0) $str_Where .= " AND Surname LIKE '".addslashes($Surname)."%'";
        if(trim(strlen($Name))>0) $str_Where .= " AND Name LIKE '".addslashes($Name)."%'";

    }
    if($TaxCode!="") $str_Where.= " AND TaxCode LIKE '%".$TaxCode."%'";
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



            $n_Tutor = 0;
            $n_Young = 0;
            $deceased = 0;
            
            if($trespasser['BornDate']!=''){
                $d_Date1 = new DateTime($trespasser['BornDate']);
                $d_Date2 = new DateTime(DateInDB($FineDate));

                $d_Diff = $d_Date2->diff($d_Date1);
                $d_Diff->format('%R');
                $n_Age =  $d_Diff->y;

                if($n_Age<18) $n_Tutor = $n_Age;


            }
            
            if($trespasser['LicenseDate']!=''){
                $d_LicenseDate1 = new DateTime($trespasser['LicenseDate']);
                $d_LicenseDate2 = new DateTime(DateInDB($FineDate));
                
                $d_DiffLicense = $d_LicenseDate2->diff($d_LicenseDate1);
                $d_DiffLicense->format('%R');
                $n_YoungAge =  $d_DiffLicense->y;
                
                if($n_YoungAge<3) $n_Young = $n_YoungAge;
                
                
            }
            
            if($trespasser['DeathDate']!=''){
                $d_DeathDate1 = new DateTime($trespasser['DeathDate']);
                $d_DeathDate2 = new DateTime(DateInDB($FineDate));
                
                if($d_DeathDate1 < $d_DeathDate2) $deceased = 1;
            }

            $strTrespasser .= '
			<div class="table_caption_H col-sm-3" style="font-size: 1rem;"><a href="#"><span class="fa fa-share" tutor="'.$n_Tutor.'" younglicense="'.$n_Young.'" deceased="'.$deceased.'" id="' . $trespasser['Id'] . '" alt="' . $trespasser['CompanyName'] ." ".$trespasser['Surname']." ".$trespasser['Name'].'"></span></a> '.$NameOut.'</div>
			<div class="table_caption_H col-sm-3" style="font-size: 1rem;">'.$trespasser['City'].'</div>
			<div class="table_caption_H col-sm-3" style="font-size: 1rem;">'.$trespasser['CountryTitle'].'</div>
			<div class="table_caption_H col-sm-3" style="font-size: 1rem;">'.$trespasser['TaxCode'].'</div>
		';
        }
    }

    $str_CheckDiv = '';

    if($NumberTab==10){
        $str_CheckDiv = '
        if($("#TrespasserType").val()!=3 && $("#TrespasserType").val()!=4) {
            if(tutor==0){
                $("#DIV_Tutor_'.$NumberTab.'").hide();
                $("#DIV_Message_'.$NumberTab.' .minor").remove();
            } else {
                $("#DIV_Tutor_'.$NumberTab.'").show();
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"minor\"> Trattasi di trasgressore minorenne – inserire i dati dell\' esercente della patria potesta</li>");
                $("#TrespasserId'.$a_Tutor[$NumberTab].'").val("");
            }
        }
        
        if($("#TrespasserType").val()==1) {
            if(younglicense==0){
                $("#DIV_Message_'.$NumberTab.' .young").remove();
                $("[id^=LicensePoint_]").show();
                $("[id^=YoungLicensePoint_]").hide();
                young = false; 
            } else {
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"young\"> Trattasi di neopatentato – i punti decurtati saranno raddoppiati</li>");
                $("#TrespasserId'.$a_Tutor[$NumberTab].'").val("");
                $("[id^=LicensePoint_]").hide();
                $("[id^=YoungLicensePoint_]").show();
                young = true;
            }
            if(deceased==0){
                $("#DIV_Message_'.$NumberTab.' .deceased").remove();
            } else {
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"deceased\"> Trattasi di trasgressore con data di decesso antecedente alla data di infrazione</li>");
            }
        }
        ';
    }
    
    if($NumberTab==11){
        $str_CheckDiv = '
        $("#DIV_Message_'.$NumberTab.' li").remove();
            
        if($("#TrespasserType").val()!=3 && $("#TrespasserType").val()!=4) {
            if(tutor==0){
                $("#DIV_Tutor_'.$NumberTab.'").hide();
                $("#DIV_Message_'.$NumberTab.' .minor").remove();
            } else {
                $("#DIV_Tutor_'.$NumberTab.'").show();
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"minor\"> Trattasi di trasgressore minorenne – inserire i dati dell\' esercente della patria potesta</li>");
                $("#TrespasserId'.$a_Tutor[$NumberTab].'").val("");
            }
        }
                    
        if($("#TrespasserType").val()==2 || $("#TrespasserType").val()==3) {
            if(younglicense==0){
                $("#DIV_Message_'.$NumberTab.' .young").remove();
                $("[id^=LicensePoint_]").show();
                $("[id^=YoungLicensePoint_]").hide();
                young = false;
            } else {
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"young\"> Trattasi di neopatentato – i punti decurtati saranno raddoppiati</li>");
                $("#TrespasserId'.$a_Tutor[$NumberTab].'").val("");
                $("[id^=LicensePoint_]").hide();
                $("[id^=YoungLicensePoint_]").show();
                young = true; 
            }
        }
        ';
    }
    
    if($NumberTab==12){
        $str_CheckDiv = '
        if($("#TrespasserType").val()==4) {
            if(tutor==0){
                $("#DIV_Tutor_'.$NumberTab.'").hide();
                $("#DIV_Message_'.$NumberTab.'").css("display", "none");
                $("#DIV_Message_'.$NumberTab.' .minor").remove();
            } else {
                $("#DIV_Tutor_'.$NumberTab.'").show();
                $("#DIV_Message_'.$NumberTab.'").css("display", "flex");
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"minor\"> Trattasi di trasgressore minorenne – inserire i dati dell\' esercente della patria potesta</li>");
                $("#TrespasserId'.$a_Tutor[$NumberTab].'").val("");
            }

            if(younglicense==0){
                $("#DIV_Message_'.$NumberTab.' .young").remove();
                $("[id^=LicensePoint_]").show();
                $("[id^=YoungLicensePoint_]").hide();
                young = false;   
            } else {
                $("#DIV_Message_'.$NumberTab.' ul").append("<li class=\"young\"> Trattasi di neopatentato – i punti decurtati saranno raddoppiati</li>");
                $("#TrespasserId'.$a_Tutor[$NumberTab].'").val("");
                $("[id^=LicensePoint_]").hide();
                $("[id^=YoungLicensePoint_]").show();
                young = true;
            }
        }
        ';
    }


    $strTrespasser .= '
        <script>
        $(".fa-share").click(function(){
            var id = $(this).attr("id");
            var name = $(this).attr("alt");
            var tutor = $(this).attr("tutor"); 
            var younglicense = $(this).attr("younglicense");
            var deceased = $(this).attr("deceased");
            
            $("#span_name_'.$NumberTab.'").html(name);
            $("#span_name_'.$NumberTab.'").parent().css("background-color", "#299c35");
            $("#TrespasserId'.$NumberTab.'").val(id);
            $("#TrespasserId'.$NumberTab.'").change();
            $("#error_name_'.$NumberTab.'").removeClass("help-block");
            $("#trespasser_content_'.$NumberTab.'").hide();
            $("#error_name_'.$NumberTab.'").html("");
            $("#DIV_Message_'.$NumberTab.' li").remove();
            '.$str_CheckDiv.'
            
            if($("#DIV_Message_'.$NumberTab.' ul").has("li").length){
                $("#DIV_Message_'.$NumberTab.'").css("display", "flex");
            } else $("#DIV_Message_'.$NumberTab.'").css("display", "none");

            sumPoints();
            
            return false;
        });
        </script>
        ';

}





echo json_encode(
	array(
		"Trespasser" => $strTrespasser,
		)
);
