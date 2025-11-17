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
$n_ControllerId = CheckValue('ControllerId','n');




$s_Limit5 = ($n_RecordLimit==5) ? " SELECTED " : "";
$s_Limit25 = ($n_RecordLimit==25) ? " SELECTED " : "";
$s_Limit50 = ($n_RecordLimit==50) ? " SELECTED " : "";
$s_Limit100 = ($n_RecordLimit==100) ? " SELECTED " : "";
$s_Limit200 = ($n_RecordLimit==200) ? " SELECTED " : "";






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
                Accertatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelectQuery("SELECT Id, CONCAT(Code,' - ',Name) AS ControllerName FROM Controller WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 ORDER BY Name","ControllerId","Id","ControllerName",$n_ControllerId,false) .'
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
            <div class="col-sm-5 BoxRowCaption">
                <select name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>								
                </select>
            </div>
             
        </div>
    </div>    	
</div>
<div class="clean_row HSpace4"></div>
';



$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_create_pec_exe.php'.$str_Parameters.'&ControllerId='.$n_ControllerId.'" method="post">
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-1">Riga</div>
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-2">Codice</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Allega</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-2">Nazione</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				
				<div class="clean_row HSpace4"></div>';
if($s_TypePlate==""){
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Scegliere tipologia targa
		</div>';
} else {





	$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND (StatusTypeId=10 OR StatusTypeId=14) AND ControllerId IS NOT NULL";
    $str_Where .= ($s_TypePlate=='N') ? " AND PEC!=''" : "";


    //$str_Where .= " AND (VehiclePlate='FC724VN' OR VehiclePlate='CR151AY')";

    //$str_Where .= " AND (VehiclePlate='DZ593FA' OR  VehiclePlate='FC843JM' OR VehiclePlate='ET183PE')";



	if($n_ControllerId > 0){
		$str_Where .= " AND ControllerId=$n_ControllerId ";

	}

    if($Search_Violation > 0){
        $str_Where .= " AND ViolationTypeId=$Search_Violation ";

    }



//	$strOrder = "VehiclePlate LIMIT 10,2";
	$strOrder = "FineDate ASC, FineTime ASC";


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
			<div class="table_caption_H col-sm-2">' . $table_row['Code'] .'</div>
			<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>
			<div class="table_caption_button col-sm-1" style="text-align:center">
				<input type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '" checked />
			</div>
			
			<div class="table_caption_H col-sm-2">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
			<div class="table_caption_H col-sm-2">' . $table_row['VehicleCountry'] .'</div>
	
			<div class="table_caption_button col-sm-1">
			'. ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>') .'
			&nbsp;
			'. ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id']."&#38;TypePlate=".$s_TypePlate."&ControllerId=".$n_ControllerId."&RecordLimit=".$n_RecordLimit.'"><span class="glyphicon glyphicon-pencil"></span></a>') .'
			&nbsp;
			</div>
			<div class="clean_row HSpace4"></div>';

		}





        if($r_Customer['CityUnion']>1 || $_SESSION['usertype']==3 || $_SESSION['usertype']==2) {
            $str_ChiefController = '<div class="col-sm-6 BoxRowCaption"></div>';
        }else{

            if($r_Customer['ChiefControllerList']){
                $str_ChiefController =
                    '
                    <div class="col-sm-3 BoxRowLabel">
                            Verbalizzante
                    </div>				
                    <div class="col-sm-3 BoxRowCaption">
                        '. CreateSelectConcat("SELECT Id, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId='".$_SESSION['cityid']."' ORDER BY Name","ChiefControllerId","Id","Name","",false,15,"frm_field_required") .'
                    </div>
                    ';
            } else {
                $rs_ChiefController = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Sign !='' AND Disabled=0");
                $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

                $str_ChiefController =
                    '
                    <div class="col-sm-3 BoxRowLabel">
                        Verbalizzante
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <select name="ChiefControllerId">
                            <option value="'.$r_ChiefController['Id'].'">'. $r_ChiefController['Name'].' Matricola '.$r_ChiefController['Code'].'</option>
                        </select>
                    </div>
                    ';

            }
        }



        $str_out.=
            '

            <div class="col-sm-3 BoxRowLabel">
                Data verbalizzazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input type="text" class="form-control frm_field_date frm_field_required" name="CreationDate" id="CreationDate" value="'.$CreationDate.'" style="width:12rem">
            </div>
            '. $str_ChiefController .'
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

		$('#TypePlate').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
            var Search_Violation = $( "#Search_Violation" ).val();
            var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();


			$(window.location).attr('href', "<?= $str_CurrentPage ?>&Search_Violation="+Search_Violation+"&TypePlate="+TypePlate+"&ControllerId="+ControllerId+"&RecordLimit="+RecordLimit+"&Search_FromFineDate="+Search_FromFineDate+"&Search_ToFineDate="+Search_ToFineDate);
		});

		$('#ControllerId').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
            var Search_Violation = $( "#Search_Violation" ).val();
            var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
			if(TypePlate!=""){
                $(window.location).attr('href', "<?= $str_CurrentPage ?>&Search_Violation="+Search_Violation+"&TypePlate="+TypePlate+"&ControllerId="+ControllerId+"&RecordLimit="+RecordLimit+"&Search_FromFineDate="+Search_FromFineDate+"&Search_ToFineDate="+Search_ToFineDate);
			}
		});
		$('#RecordLimit').change(function(){
			var TypePlate = $( "#TypePlate" ).val();
			var ControllerId = $( "#ControllerId" ).val();
			var RecordLimit = $( "#RecordLimit" ).val();
            var Search_Violation = $( "#Search_Violation" ).val();
            var Search_FromFineDate = $( "#Search_FromFineDate" ).val();
            var Search_ToFineDate = $( "#Search_ToFineDate" ).val();
			if(TypePlate!=""){
                $(window.location).attr('href', "<?= $str_CurrentPage ?>&Search_Violation="+Search_Violation+"&TypePlate="+TypePlate+"&ControllerId="+ControllerId+"&RecordLimit="+RecordLimit+"&Search_FromFineDate="+Search_FromFineDate+"&Search_ToFineDate="+Search_ToFineDate);
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
        }).on('success.form.bv', function(e){
			if($('#ultimate').is(":checked")) {
				if(confirm("Si stanno per creare i verbali in maniera definitiva. Continuare?")){
					$('#sub_Button').hide();
					$('#Progress').show();
					$('#ultimate').hide();
					$('#f_print').submit();
				} else {
                	e.preventDefault();
                	return false;
				}
			}
        });
	});
</script>
<?php
include(INC."/footer.php");
