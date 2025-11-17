<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$DisplayMsg = CheckValue('DisplayMsg','n');

$Search_Violation = CheckValue('Search_Violation','n');

$CreationDate = date('d/m/Y');

if($DisplayMsg){
	include(INC."/display.php");
	DIE;
}





$n_RecordLimit = CheckValue('RecordLimit','n');
if($n_RecordLimit==0) $n_RecordLimit=5;





$s_Limit5 = ($n_RecordLimit==5) ? " SELECTED " : "";
$s_Limit25 = ($n_RecordLimit==25) ? " SELECTED " : "";
$s_Limit50 = ($n_RecordLimit==50) ? " SELECTED " : "";
$s_Limit100 = ($n_RecordLimit==100) ? " SELECTED " : "";
$s_Limit200 = ($n_RecordLimit==200) ? " SELECTED " : "";




$str_WhereCountry = '';
if ($s_TypePlate == "F"){
    $CountryBankId = CheckValue('CountryBankId','s');

    $str_Sql = "SELECT CB.CountryId CountryId, C.Title
        FROM CountryBank CB JOIN Country C ON CB.CountryId=C.Id
        WHERE CB.CityId='". $_SESSION['cityid'] ."' ORDER BY C.Title
    ";

    $str_SelectCountryBank = CreateSelectQuery($str_Sql,"CountryBankId","CountryId","Title",$CountryBankId,false);


    if($CountryBankId!=''){
        $str_WhereCountry .= " AND CountryId='". $CountryBankId ."'";
    } else {
        $rs_CountryBank = $rs->SelectQuery($str_Sql);
        if(mysqli_num_rows($rs_CountryBank)>0){
            while($r_CountryBank= mysqli_fetch_array($rs_CountryBank)){
                $str_WhereCountry .= " AND CountryId !='". $r_CountryBank['CountryId'] ."'";
            }

        }
    }




}else $str_SelectCountryBank = "<select name='CountryBankId' id='CountryBankId'><option></option></select>";


$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12" >
        <div class="col-sm-12 BoxRow"  style="height:4.6rem;">
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="RecordLimit" id="RecordLimit" />
                    <option value="5"'.$s_Limit5.'>5</option>
                    <option value="25"'.$s_Limit25.'>25</option>
                    <option value="50"'.$s_Limit50.'>50</option>
                    <option value="100"'.$s_Limit100.'>100</option>
                    <option value="200"'.$s_Limit200.'>200</option>                          
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <select class="form-control" name="Search_Status" id="Search_Status" style="width:9rem;">
                    <option value="0"></option>
                    <option value="27" ' .$a_Search_Status[27] .'>Non pagato</option>
                    <option value="28" ' .$a_Search_Status[28] .'>Pagato parziale</option>
                </select>
            </div>         
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" id="Search_FromFineDate" style="width:9rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" id="Search_ToFineDate" style="width:9rem">
            </div>             
    
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-3 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <select name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>								
                </select>
            </div>
            <div class="col-sm-3 BoxRowLabel">
                C/C Paese
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. $str_SelectCountryBank .'
            </div> 
        </div>
    </div>    	
</div>
<div class="clean_row HSpace4"></div>
';



$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_create_reminder_exe.php'.$str_Parameters.'&Search_Status='.$Search_Status.'" method="post">
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-1">Riga</div>
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-2">Codice</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Allega</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-2">Nazione</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				
				<div class="clean_row HSpace4"></div>';
if($s_TypePlate==""){
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Scegliere tipologia targa
		</div>';
} else {




    $str_Where .= " AND ReminderDate IS NULL AND ProtocolId>0 AND (StatusTypeId=27 OR StatusTypeId=28) AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
    $strOrder = "ProtocolId";




    if($Search_Violation > 0){
        $str_Where .= " AND ViolationTypeId=$Search_Violation ";

    }


    $str_Where .= $str_WhereCountry;





	if($n_RecordLimit>0){
		$strOrder .= " LIMIT $n_RecordLimit";

	}


	$table_rows = $rs->Select('V_ViolationAll',$str_Where, $strOrder);
	$RowNumber = mysqli_num_rows($table_rows);
    $n_ContRow = 0;
	$str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12">
			    Nessun record presente
		    </div>';
	} else {
		while ($table_row = mysqli_fetch_array($table_rows)) {
            $n_ContRow++;
            $str_CssController = "";

            if($table_row['ControllerId']=="") $str_CssController = ' style="background-color:rgba(107,155,29,0.76)"';

			$str_out.= '
            <div class="table_caption_H col-sm-1"'.$str_CssController.'>' . $n_ContRow .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] .'</div>
			<div class="table_caption_H col-sm-2">' . $table_row['Code'] .'</div>
			<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>
			<div class="table_caption_button col-sm-1" style="text-align:center">
				<input type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '" checked />
			</div>
			
			<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
			<div class="table_caption_H col-sm-2">' . $table_row['VehicleCountry'] .'</div>
	
			<div class="table_caption_button col-sm-1">
			'. ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>') .'
			&nbsp;
			'. ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id']."&#38;TypePlate=".$s_TypePlate."&Search_Status=".$Search_Status."&RecordLimit=".$n_RecordLimit.'"><span class="glyphicon glyphicon-pencil"></span></a>') .'
			&nbsp;
			</div>
			<div class="clean_row HSpace4"></div>';

		}



        $str_out.=
            '

            <div class="col-sm-3 BoxRowLabel">
                Data invio
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input type="text" class="form-control frm_field_date frm_field_required" name="CreationDate" id="CreationDate" value="'.$CreationDate.'" style="width:12rem">
            </div>
            <div class="col-sm-6 BoxRowCaption"></div>
            <div class="clean_row HSpace4"></div>
            ';


        $strButtons =
            '
            <input type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1rem;" value="Anteprima di stampa" />
    		<input type="checkbox" value=1 name="ultimate" id="ultimate" style="margin-left:5rem;">DEFINITIVA';


        $str_out.= '
		<div class="col-sm-12 table_caption_H"  style="height:6rem;text-align:center;line-height:6rem;">
		'.ChkButton($aUserButton, 'act',$strButtons) .'  
		<img src="'.IMG.'/progress.gif" style="display: none;" id="Progress"/>
		</div>
		</form>
	</div>';



	}

}


echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {

		$('#TypePlate, #CountryBankId, #Search_Status, #RecordLimit').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
            var Search_Status = $( "#Search_Status" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
            var Search_Violation = $( "#Search_Violation" ).val();
            var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
            var CountryBankId = $( "#CountryBankId" ).val();

            if(TypePlate!=""){
                $(window.location).attr('href', "<?= $str_CurrentPage ?>&Search_Violation="+Search_Violation+"&TypePlate="+TypePlate+"&Search_Status="+Search_Status+"&RecordLimit="+RecordLimit+"&Search_FromFineDate="+Search_FromFineDate+"&Search_ToFineDate="+Search_ToFineDate+"&CountryBankId="+CountryBankId);
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



		$('#sub_Button').click(function() {
			if($('#ultimate').is(":checked")) {
				var c = confirm("Si stanno per creare i solleciti in maniera definitiva. Continuare?");
				if(c){
					$('#sub_Button').hide();
					$('#Progress').show();
					$('#ultimate').hide();
					$('#f_print').submit();
				}

			}else{
				$('#f_print').submit();
			}
		});


        $('#f_print').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },


                CreationDate:{
                    validators: {
                        notEmpty: {message: 'Richiesto'},

                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    }

                },
            }
        });
	});
</script>
<?php
include(INC."/footer.php");
