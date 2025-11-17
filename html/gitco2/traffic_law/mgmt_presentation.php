<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Presentation = CheckValue('Presentation','n');

$str_CheckPresentation0 = $str_CheckPresentation1 = $str_CheckPresentation2 = "";

if($Presentation==1){
    $str_CheckPresentation1 = " CHECKED ";
} else if($Presentation==2){
    $str_CheckPresentation2 = " CHECKED ";
} else{
    $str_CheckPresentation0 = " CHECKED ";
}



$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];


if($Presentation==0){
    $str_Where .= " AND PresentationDate IS NULL ";
} else if($Presentation==2){
    $str_Where .= " AND PresentationDate IS NOT NULL ";
}



$strOrder = "ProtocolId ASC";

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}
$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Id/Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_ProtocolId" type="text" style="width:10rem" value="'.$Search_ProtocolId.'">
            </div>        
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>								
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" style="width:8rem" value="'.$Search_Plate.'">
            </div>          
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:8rem" value="'.$Search_Ref.'">
            </div>            
 
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false,24) .'
            </div>                                                

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                Tragressore
            </div>
            <div class="col-sm-4 BoxRowCaption" style="font-size:1rem">
                <input name="Search_Trespasser" type="text" style="width:20rem" value="'.$Search_Trespasser.'">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Documenti presentati:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Presentation" value="0" '.$str_CheckPresentation0.'><span  style="position:relative;top:-1rem">Escludi</span> 
                <input type="radio" name="Presentation" value="1" '.$str_CheckPresentation1.'><span  style="position:relative;top:-1rem">Includi</span>
                <input type="radio" name="Presentation" value="2" '.$str_CheckPresentation2.'><span  style="position:relative;top:-1rem">Solo loro</span>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
            </div> 
           
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;margin-left:1rem;font-size:2.5rem;"></i>
                  
                <i class="fa fa-file-pdf-o" style="margin-top:0.6rem;margin-left:1rem;font-size:2.5em;"></i>            
                
            </div>
        </div>
    </form>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';


$str_out .='        
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">#</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Ref</div>				
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-3">Nominativo</div>
				<div class="table_label_H col-sm-1">Articolo</div>				
				<div class="table_label_H col-sm-2">Stato pratica</div>

				<div class="clean_row HSpace4"></div>';

$page = CheckValue("page","n");


$rs_Fine = $rs->Select('V_FinePresentation',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);

$n_RowNumber = mysqli_num_rows($rs_Fine);

if ($n_RowNumber == 0) {
	$str_out.= 'Nessun record presente';
} else {
	while ($r_Fine = mysqli_fetch_array($rs_Fine)) {

        $str_Trespasser = $r_Fine['CompanyName'] .' '.$r_Fine['Surname'] .' '.$r_Fine['Name'];
        $str_Trespasser = (strlen($str_Trespasser)>42) ? substr($str_Trespasser,0,40).'...' : $str_Trespasser;

		$str_out.=
			'
            <div class="table_caption_H col-sm-1">' . $r_Fine['Id'] .'</div>
			<div class="table_caption_H col-sm-1">' . $r_Fine['ProtocolId'].' / '.$r_Fine['ProtocolYear'].'</div>
			<div class="table_caption_H col-sm-1">' . $r_Fine['Code'].'</div>
        	<div class="table_caption_H col-sm-1">' . DateOutDB($r_Fine['FineDate']) .'</div>
        	<div class="table_caption_H col-sm-1">' . $r_Fine['FineTime'] .'</div>
        	<div class="table_caption_H col-sm-1">' . StringOutDB($r_Fine['VehiclePlate']) .'</div>
        	<div class="table_caption_H col-sm-3">' . StringOutDB($str_Trespasser).'</div>
        	<div class="table_caption_H col-sm-1">' . $r_Fine['Article'] .' '.$r_Fine['Paragraph'].' '.$r_Fine['Letter'].'</div>
			';
		$Status = '';
		
		

		$Status .= (! is_null($r_Fine['PresentationDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Documento presentata in data '.DateOutDB($r_Fine['PresentationDate']).'"><i class="fa fa-address-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Documento non e stata presentata"><i class="fa fa-address-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';

        $Status .= '<i class="fa fa-sort-numeric-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';



		$str_out.=
			'<div class="table_caption_H col-sm-1">' . $Status .'</div>
			<div class="table_caption_button col-sm-1">
				'. ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$r_Fine['Id'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');


        if(is_null($r_Fine['PresentationDate'])) $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_presentation_upd.php'.$str_GET_Parameter.'&Id='.$r_Fine['Id'].'"><span class="fa fa-user" style="font-size:1.6rem;position:absolute;left:25px;top:2px;"></span></a>');
        $str_out.=
            '	
			</div>
			<div class="clean_row HSpace4"></div>
			';
	}
}
$table_users_number = $rs->Select('V_FinePresentation',$str_Where);
$UserNumberTotal = mysqli_num_rows($table_users_number);

$strLabel =' 
 		<div style="position:absolute; top:5px;font-size:1.2rem;color:#fff;width:405px;text-align: left">
	 		<div style="width:200px;float:left;">
	 			<i class="fa fa-address-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Documento presentata
			</div>
			<div style="width:200px;float:left;">
				<i class="fa fa-sort-numeric-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Punti decurtati
			</div>	
			<div style="width:200px;float:left;">
				<i class="fa fa-envelope-o" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> 126Bis spedito
			</div>					

		</div>
		
		
		
		';


$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,$strLabel);
$str_out.= '<div>
	</div>';


echo $str_out;
?>


<script type="text/javascript">


    $(document).ready(function () {
        <?= require ('inc/jquery/base_search.php')?>


        $(".fa-file-pdf-o").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_presentation_exe.php');
            $('.glyphicon-search').hide();
            $('.fa-file-pdf-o').hide();

            $('#f_Search').submit();
        });


        $(".glyphicon-search, .fa-file-pdf-o").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });


    });

</script>

<?php
include(INC."/footer.php");
