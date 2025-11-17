<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Cont = 0;


$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRow" style="height:4.6rem;">
        <div class="col-sm-2 BoxRowLabel">
            Tipologia targhe
        </div>
        <div class="col-sm-10 BoxRowCaption">
            <select name="TypePlate" id="TypePlate" style="width:12rem;">
                <option></option>
                <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                <option value="F"'.$s_SelPlateF.'>Estere</option>
            </select>
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';


$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" action="frm_send_reminder_exe.php" method="post">
        	<div class="col-sm-12">
 	   	    	<div class="table_label_H col-sm-1">Allega</div>
 	   	    	<div class="table_label_H col-sm-1">Riga</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-2">Data sollecito</div>
				<div class="table_label_H col-sm-2">Violazione</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				

				<div class="clean_row HSpace4"></div>';

if($s_TypePlate==""){
    $str_out.=
        '<div class="table_caption_H col-sm-12">
			Scegliere tipologia targa
		</div>';
} else {

    $str_Where .= " AND ReminderDate IS NOT NULL AND ProtocolId>0 AND (StatusTypeId=27 OR StatusTypeId=28) AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
    $str_Where .= " AND Id IN (SELECT FineId FROM FineReminder WHERE CityId='".$_SESSION['cityid']."' AND FlowDate IS NULL )";

    $strOrder = "ProtocolId";


    if ($s_TypePlate == "N") {
        $str_Where .= " AND CountryId='Z000'";
    } else {
        $str_Where .= " AND CountryId!='Z000'";
    }

    $strOrder = "ProtocolId LIMIT 800";


    $table_rows = $rs->Select('V_FineReminder', $str_Where, $strOrder);
    $RowNumber = mysqli_num_rows($table_rows);

    $str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';

    if ($RowNumber == 0) {
        $str_out .=
            '<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
    } else {
        while ($table_row = mysqli_fetch_array($table_rows)) {
            $Cont++;

            $str_out .= '
				<div class="table_caption_button col-sm-1" style="text-align:center">
					<input type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '" checked />
				</div>';

            $str_out .= '<div class="table_caption_H col-sm-1">' . $Cont . '</div>';

            $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] . ' / ' . $table_row['ProtocolYear'] . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-2">' . DateOutDB($table_row['PrintDate']) . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-2">' . $table_row['ViolationTitle'] . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-2">' . $table_row['VehiclePlate'] . '<i class="' . $aVehicleTypeId[$table_row['VehicleTypeId']] . '" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
            $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) . '</div>';


            $str_out .= '<div class="table_caption_button col-sm-1">';
            $str_out .= ChkButton($aUserButton, 'viw', '<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id=' . $table_row['Id'] . '"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>');
            $str_out .= '&nbsp;';
//            $str_out .= ChkButton($aUserButton, 'prn', '<a href="mgmt_fine_prn.php?Id=' . $table_row['Id'] . '&P=' . $FormPage . '"><span class="fa fa-print" id="' . $table_row['Id'] . '"></span></a>');
//            $str_out .= '&nbsp;';
            $str_out .= '</div>
			            <div class="clean_row HSpace4"></div>';

        }

    }

//    $strButtons = '<button type="submit" class="btn btn-default" style="margin-top:1rem;">Invia</button>';
    $strButtons = '<input type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1rem;" value="Anteprima flusso" />
    		<input type="checkbox" name="ultimate" id="ultimate" style="margin-left:5rem;">DEFINITIVO';
    $str_out .= '

        <div class="col-sm-12 BoxRowCaption" style="text-align:center;line-height:6rem;">
            <select name="PrinterId" id="PrinterId" style="width:12rem;">
                <option value="1">Mercurio</option>
                <option value="0">Posta</option>
            </select>

        </div>
		<div class="col-sm-12 table_caption_H" style="height:6rem;text-align:center;line-height:6rem;">
    		' . ChkButton($aUserButton, 'act', $strButtons) . '
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
            $(window.location).attr('href', "frm_send_reminder.php?PageTitle=Moduli/Invio verbali&TypePlate="+TypePlate);
        });

        $('#ultimate').click(function(){
            if($('#ultimate').is(":checked")) {
                $('#sub_Button').val('Flusso definitivo');
                $('#sub_Button').removeClass( "btn-success" ).addClass( "btn-warning" );
            }else{
                $('#sub_Button').val('Anteprima flusso');
                $('#sub_Button').removeClass( "btn-warning" ).addClass( "btn-success" );
            }
        });
	});
</script>
<?php
include(INC."/footer.php");
