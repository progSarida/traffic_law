<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-12 BoxRow" style="height:2.3rem;">
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <div id="Search_RuleTypeId" value="'.$RuleTypeId.'">'.$RuleTypeTitle.'</div>
            </div>  
        
            <div class="col-sm-2 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelectQuery("SELECT DISTINCT CountryId, VehicleCountry FROM Fine WHERE CityId='".$_SESSION['cityid']."' AND StatusTypeId=1 AND CountryId!='Z000' ORDER BY VehicleCountry","Search_Country","CountryId","VehicleCountry",$Search_Country,false) .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Ricerca zona
            </div>
            <div class="col-sm-3 BoxRowCaption">';
if($Search_Zone!=""){
	$str_out .=	CreateSelect("Entity","CountryId='".$Search_Country."'","Region","Search_Zone","Region","Region",$Search_Zone,false);
}else{
	$str_out .='<select name="Search_Zone" id="Search_Zone"></select>';
}

$str_out .= '
            </div>
        </div>							
    </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
        ';



$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" action="frm_send_request_exe.php" method="post">
    	<input type="hidden" name="Search_Country" value="' .$Search_Country.'">
    	<input type="hidden" name="Search_Zone" value="'.$Search_Zone.'">
        	<div class="col-sm-12">
 	   	    	<div class="table_label_H col-sm-1">Allega</div>
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-2">Codice</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-3">Nazione</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				

				<div class="clean_row HSpace4"></div>';

$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND CountryId!='Z000'";


$strOrder = "VehiclePlate";

$table_rows = $rs->Select('V_ViolationQuery',$str_Where, $strOrder);
$RowNumber = mysqli_num_rows($table_rows);


$VehiclePlate = "";
$Cont = 0;
if ($RowNumber == 0) {
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {

		if($table_row['VehiclePlate']!=$VehiclePlate){
			$Cont++;
			$str_out.= '
					<div class="table_caption_button col-sm-1" style="text-align:center">
						'.$Cont.' <input type="checkbox" name="checkbox[]" value="' . StringOutDB($table_row['VehiclePlate']) . '" checked />
					</div>';
			$VehiclePlate = $table_row['VehiclePlate'];
		}else{
			$str_out.= '
					<div class="table_caption_button col-sm-1" style="text-align:center"></div>';

		}

		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-2">' . $table_row['Code'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['FineTime'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-2">' . StringOutDB($table_row['VehiclePlate']) .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
		$str_out.= '<div class="table_caption_H col-sm-3">' . $table_row['VehicleCountry'] .'</div>';

		$str_out.= '<div class="table_caption_button col-sm-1">';
		$str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open"></span></a>');
		$str_out.= '&nbsp;';
		$str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-pencil"></span></a>');
		$str_out.= '&nbsp;';
		$str_out.= '</div>
			        <div class="clean_row HSpace4"></div>';
	if($Cont==90) break;
	}

}

$strButtons = '<button type="submit" class="btn btn-default" style="margin-top:1rem;width:14rem;">Stampa richieste</button>
				<input type="checkbox" name="ultimate" style="margin-left:5rem;">STAMPA DEFINITIVA';

if($Search_Country!="" && $RowNumber > 0){
	$str_out.= '

        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    '.ChkButton($aUserButton, 'prn',$strButtons) .'
                 </div>    
            </div>
        </div>

		</form>
	</div>';
}else{
	$str_out.= '';
}

echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {
		var Search_Country = "<?= $Search_Country ?>";

        $('#Search_Country').change(function(){
			Search_Country = $( "#Search_Country" ).val();
            var Search_RuleTypeId = $( "#Search_RuleTypeId" ).val();

			if(Search_Country=='Z133' || Search_Country=='Z102'){
				var ent = $.ajax({
					url: "ajax/entity.php",
					type: "POST",
					data: {id:Search_Country},
					dataType: "text"
				});
				ent.done(function(data){
					$('#Search_Zone').html(data);
				});
				ent.fail(function(jqXHR, textStatus){
					alert( "Request failed: " + textStatus );
				});
			}else{
				$('#Search_Zone').html('<option value=""></option>');
				$(window.location).attr('href', "frm_send_request.php?Search_RuleTypeId="+Search_RuleTypeId+"&Search_Country="+Search_Country);
			}

		});
		$('#Search_Zone').change(function(){
			var Search_Zone = $( "#Search_Zone" ).val();
            var Search_RuleTypeId = $( "#Search_RuleTypeId" ).val();
			$(window.location).attr('href', "frm_send_request.php?Search_RuleTypeId="+Search_RuleTypeId+"&Search_Country="+Search_Country+"&Search_Zone="+Search_Zone);
		});






	});
</script>
<?php
include(INC."/footer.php");
