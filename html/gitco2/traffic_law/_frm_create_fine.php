<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

include(INC."/menu.php");

$LinkPage = curPageName();


$FormPage = $LinkPage;
$LinkPage .="?1";
$LanguageId = 1;
$CurrentYear = $_SESSION['year'];



$s_TypePlate = CheckValue('TypePlate','s');
$n_RecordLimit = CheckValue('RecordLimit','n');
if($n_RecordLimit==0) $n_RecordLimit=5;
$n_ControllerId = CheckValue('ControllerId','n');

$aVehicleTypeId = array("","fa fa-car","fa fa-motorcycle","fa fa-desktop","fa fa-truck","fa fa-bus","fa fa-rocket","fa fa-desktop","fa fa-bus","fa fa-bicycle", "fa fa-desktop", "fa fa-desktop","fa fa-desktop");



$rs= new CLS_DB();

$s_SelPlateN = ($s_TypePlate=="N") ? " SELECTED " : "";
$s_SelPlateF = ($s_TypePlate=="F") ? " SELECTED " : "";

$s_Limit5 = ($n_RecordLimit==5) ? " SELECTED " : "";
$s_Limit25 = ($n_RecordLimit==25) ? " SELECTED " : "";
$s_Limit50 = ($n_RecordLimit==50) ? " SELECTED " : "";
$s_Limit100 = ($n_RecordLimit==100) ? " SELECTED " : "";
$s_Limit200 = ($n_RecordLimit==200) ? " SELECTED " : "";







$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-6" style="background-color: #fff">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
				</div>
				<div class="col-sm-6 BoxRow" style="height:6.8rem;">
					<div class="col-sm-12 BoxRow">
						<div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
							Numero record
						</div>
						<div class="col-sm-2 BoxRowCaption" style="font-size:1rem">
							<select name="RecordLimit" id="RecordLimit" />
								<option value="5"'.$s_Limit5.'>5</option>
								<option value="25"'.$s_Limit25.'>25</option>
								<option value="50"'.$s_Limit50.'>50</option>
								<option value="100"'.$s_Limit100.'>100</option>		
								<option value="200"'.$s_Limit200.'>200</option>
	
														
							</select>
						</div>
						<div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
							Accertatore
						</div>
						<div class="col-sm-6 BoxRowCaption" style="font-size:1rem">
							'. CreateSelectQuery("SELECT Id, CONCAT(Code,' - ',Name) AS ControllerName FROM Controller WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 ORDER BY Name","ControllerId","Id","ControllerName",$n_ControllerId,false) .'
						</div>
					</div>	
					<div class="col-sm-12 BoxRow">
						<div class="col-sm-4 BoxRowLabel" style="font-size:1.2rem">
							Tipologia targhe
						</div>
						<div class="col-sm-8 BoxRowCaption" style="font-size:1rem">
							<select name="TypePlate" id="TypePlate">
								<option></option>
								<option value="N"'.$s_SelPlateN.'>Nazionali</option>
								<option value="F"'.$s_SelPlateF.'>Estere</option>								
							</select>
						</div>
					</div>	
				</div>	
         	</div>
        </div>
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_create_fine_exe.php" method="post">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-2">Codice</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Allega</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-3">Nazione</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				
				<div class="clean_row HSpace4"></div>';
if($s_TypePlate==""){
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Scegliere tipologia targa
		</div>';
} else {





	$strWhere = "CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND StatusTypeId=10";


	if($s_TypePlate=="N"){
		$strWhere .= " AND CountryId='Z000'";  //AND (Id=28871 OR Id=28876 OR Id=28881 OR Id=29165)
	} else {
		$strWhere .= " AND CountryId!='Z000'";
	}

	if($n_ControllerId > 0) $strWhere .= " AND ControllerId=$n_ControllerId ";

//	$strOrder = "VehiclePlate LIMIT 10,2";
	$strOrder = "FineDate ASC, FineTime ASC";


	if($n_RecordLimit>0) $strOrder .= " LIMIT $n_RecordLimit";


	$table_rows = $rs->Select('V_Fine',$strWhere, $strOrder);
	$RowNumber = mysqli_num_rows($table_rows);

	$str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
	} else {
		while ($table_row = mysqli_fetch_array($table_rows)) {


			$str_out.= '
			<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>
			<div class="table_caption_H col-sm-2">' . $table_row['Code'] .'</div>
			<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>
			<div class="table_caption_button col-sm-1" style="text-align:center">
				<input type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '" checked />
			</div>
			
			<div class="table_caption_H col-sm-2">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
			<div class="table_caption_H col-sm-3">' . $table_row['VehicleCountry'] .'</div>
	
			<div class="table_caption_button col-sm-1">
			'. ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php?Id='.$table_row['Id'].'&P='.$LinkPage."&#38;TypePlate=".$s_TypePlate."&ControllerId=".$n_ControllerId."&RecordLimit=".$n_RecordLimit.'"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>') .'
			&nbsp;
			'. ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php?Id='.$table_row['Id'].'&P='.$LinkPage."&#38;TypePlate=".$s_TypePlate."&ControllerId=".$n_ControllerId."&RecordLimit=".$n_RecordLimit.'"><span class="glyphicon glyphicon-pencil" id="' . $table_row['Id'] . '"></span></a>') .'
			&nbsp;
			</div>
			<div class="clean_row HSpace4"></div>';

		}

	}
	$strButtons = '<input type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1rem;" value="Anteprima di stampa" />
    		<input type="checkbox" value=1 name="ultimate" id="ultimate" style="margin-left:5rem;">DEFINITIVA';


	$str_out.= '
		<div class="col-sm-12 table_caption_H" id="div_FineButton" style="height:6rem;text-align:center;line-height:6rem;">
		'.ChkButton($aUserButton, 'act',$strButtons) .'    		 		
		</div>
		</form>
	</div>';
}


echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {

		$('#TypePlate').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
			$(window.location).attr('href', "<?= $LinkPage ?>&TypePlate="+TypePlate+"&ControllerId="+ControllerId+"&RecordLimit="+RecordLimit);
		});

		$('#ControllerId').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
			if(TypePlate!=""){
				$(window.location).attr('href', "<?= $LinkPage ?>&TypePlate="+TypePlate+"&ControllerId="+ControllerId+"&RecordLimit="+RecordLimit);
			}
		});
		$('#RecordLimit').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
			if(TypePlate!=""){
				$(window.location).attr('href', "<?= $LinkPage ?>&TypePlate="+TypePlate+"&ControllerId="+ControllerId+"&RecordLimit="+RecordLimit);
			}
		});
		
		$('#ultimate').click(function(){
			if($('#ultimate').is(":checked")) {
				$('#sub_Button').val('Stampa defitiva');
				$('#sub_Button').removeClass( "btn-success" ).addClass( "btn-warning" );
			}else{
				$('#sub_Button').val('Anteprima di stampa');
				$('#sub_Button').removeClass( "btn-warning" ).addClass( "btn-success" );
			}
		});



		$('#f_print').submit(function() {
			if($('#ultimate').is(":checked")) {
				var c = confirm("Si stanno per creare i verbali in maniera definitiva. Continuare?");
				return c;
			}
			$('sub_Button').attr("disabled", true);
			$('#div_FineButton').html("<img src='<?= IMG ?>/progress.gif' />");
		});



	});
</script>
<?php
include(INC."/footer.php");
