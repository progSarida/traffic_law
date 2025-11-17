<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

ini_set('max_execution_time', 0);

$PageTitle = CheckValue('PageTitle','s');

$RegularPostalFine = CheckValue('RegularPostalFine','n');
$PrinterId = CheckValue('PrinterId','n') <= 0 ? ($s_TypePlate == 'N' ? $r_Customer['NationalPrinter'] : $r_Customer['ForeignPrinter']) : CheckValue('PrinterId','n');

$a_RegularPostalFine    = array("", "");

$Cont = 0;
$str_Country = '
    <div class="col-sm-1 BoxRowLabel">
        Nazione
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <input type="hidden" name="Search_CountryId">
        <select disabled class="form-control" name="Search_CountryId" id="Search_CountryId"></select>
    </div>';
$str_RegularPostalFine = '
    <div class="col-sm-1 BoxRowLabel">
        Tipo Invio
    </div>
    <div class="col-sm-2 BoxRowCaption">
        <input type="hidden" name="RegularPostalFine" id="RegularPostalFine" value="0">
        Atto giudiziario
    </div>
    ';

if($r_Customer['RegularPostalFine']){
    $a_RegularPostalFine[$RegularPostalFine] = " SELECTED ";

    $str_RegularPostalFine = '
        <div class="col-sm-1 BoxRowLabel">
            Tipo invio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <select class="form-control" id="RegularPostalFine" name="RegularPostalFine">
             <option value="0" '.$a_RegularPostalFine[0] .'>Atto giudiziario</option>
             <option value="1"  '.$a_RegularPostalFine[1] .'>Posta ordinaria</option>
            </select>
        </div>
        ';

}


$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

if($RegularPostalFine){
    $str_Where .= " AND StatusTypeId=8";
}else {
    $str_Where .= " AND (StatusTypeId=14 OR StatusTypeId=15)";
}

if($s_TypePlate=='F'){
    $str_Country = '
        <div class="col-sm-1 BoxRowLabel">
            Nazione
        </div>
        <div class="col-sm-2 BoxRowCaption">
            '. CreateSelectQuery('SELECT DISTINCT CountryId, VehicleCountry FROM Fine WHERE '.$str_Where , 'Search_CountryId', 'CountryId', 'VehicleCountry', $Search_CountryId,true) .'
        </div>
    
        
    ';
    if($Search_CountryId!="") $str_Where .= " AND CountryID='". $Search_CountryId ."'";

    $str_WherePrinter = $r_Customer['ForeignPrinter'] > 0 ? "Id IN(1,{$r_Customer['ForeignPrinter']})" : "Id=1";
} else {
    $str_WherePrinter = $r_Customer['NationalPrinter'] > 0 ? "Id IN(1,{$r_Customer['NationalPrinter']})" : "Id=1";
}

$str_Where .= " AND RuleTypeId = ".$_SESSION['ruletypeid'];

$str_out .='        
	<div class="row-fluid">
    	<form id="f_search" action="frm_send_fine.php" method="post" autocomplete="off">
    		<input type="hidden" name="PageTitle" value="'.$PageTitle.'">
    
            <div class="col-sm-11" style="height:4.5rem; border-right:1px solid #E7E7E7;">
                <div class="col-sm-1 BoxRowLabel">
                    Genere
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '.$_SESSION['ruletypetitle'].'
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
                '. $str_Country . $str_RegularPostalFine .'
                <div class="col-sm-2 BoxRowLabel"></div>

                <div class="clean_row HSpace4"></div>
            	
            	<div class="col-sm-12 BoxRowLabel"></div>
            </div>
            <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
                <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="font-size:3rem;padding:0;margin:0;width:100%;height:100%">
                    <i class="glyphicon glyphicon-search"></i>
                </button>
            </div>
        </form>

        <div class="clean_row HSpace4"></div>

    	<form id="f_print" name="f_print" action="frm_send_fine_exe.php" method="post">
            <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
            <input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">
            <input type="hidden" name="RegularPostalFine" value="'. $RegularPostalFine .'" />

        	<div class="col-sm-12">
 	   	    	<div class="table_label_H col-sm-1">Seleziona <input type="checkbox" id="checkAll" checked /></div>
 	   	    	<div class="table_label_H col-sm-1">Riga</div>
                <div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-2">Cron</div>
				<div class="table_label_H col-sm-2">Data verbale</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				

				<div class="clean_row HSpace4"></div>';

if($s_TypePlate==""){
    $str_out.=
        '<div class="table_caption_H col-sm-12 text-center">
			Scegliere nazionalità targa
		</div>';
} else {



    if($r_Customer['ExternalRegistration']==1) $str_Where.=" AND ExternalProtocol>0";

    if ($s_TypePlate == "N") {

        $strOrder = "FineDate ASC, FineTime ASC, Id ASC LIMIT 900";
    } else {

        $strOrder = "FineDate ASC, FineTime ASC, Id ASC LIMIT 900";
    }

    //$str_Where .= " AND Id=254790";


    $cls_view = new CLS_VIEW(FRM_SENDFINE);
    $table_rows = $rs->SelectQuery($cls_view->generateSelect($str_Where, null, $strOrder));
    $RowNumber = mysqli_num_rows($table_rows);

    $PreviousId = 0;

    if ($RowNumber == 0) {
        $str_out .=
            '<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
    } else {
        while ($table_row = mysqli_fetch_array($table_rows)) {
            $Cont++;
            
            $str_out .= '<div class="tableRow">';
            
            if($PreviousId!=$table_row['Id']){
                $str_out .= '
				<div class="table_caption_button col-sm-1" style="text-align:center">
					<input type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '" checked />
				</div>';
                $PreviousId = $table_row['Id'];

            } else {
                $str_out .= '
				<div class="table_caption_button col-sm-1" style="text-align:center">
				</div>';

            }

            $str_out .= '<div class="table_caption_H col-sm-1">' . $Cont . '</div>';

            $str_out .= '<div class="table_caption_H col-sm-1">' . $a_FineTypeId[$table_row['FineTypeId']] . $table_row['Id'] .'</div>';
            $str_out .= '<div class="table_caption_H col-sm-2">' . $table_row['ProtocolId'] . ' / ' . $table_row['ProtocolYear'] . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-2"></div>';
            //$str_out .= '<div class="table_caption_H col-sm-2">' . DateOutDB($table_row['NotificationDate']) . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-2">' . $table_row['VehiclePlate'] . '<i class="' . $aVehicleTypeId[$table_row['VehicleTypeId']] . '" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
            $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) . '</div>';
            $str_out .= '<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) . '</div>';


            $str_out .= '<div class="table_caption_button col-sm-1">';
            $str_out .= ChkButton($aUserButton, 'viw', '<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id=' . $table_row['Id'] . '"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>');
            $str_out .= '&nbsp;';
//            $str_out .= ChkButton($aUserButton, 'prn', '<a href="mgmt_fine_prn.php?Id=' . $table_row['Id'] . '&P=' . $FormPage . '"><span class="fa fa-print" id="' . $table_row['Id'] . '"></span></a>');
//            $str_out .= '&nbsp;';
            $str_out .= '</div>
			            <div class="clean_row HSpace4"></div>';
            
            $str_out .= '</div>';

        }
        
        $str_out .= '
            <div class="col-sm-4 BoxRowLabel table_caption_I"></div>
            <div class="col-sm-2 BoxRowLabel">
                Stampatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.CreateSelect('Printer', $str_WherePrinter, 'Id', 'PrinterId', 'Id', 'Name', $PrinterId, false, null, 'frm_field_required').'
            </div>
            <div class="col-sm-4 BoxRowLabel table_caption_I"></div>';
        
        //    $strButtons = '<button type="submit" class="btn btn-default" style="margin-top:1rem;">Invia</button>';
        $strButtons = '<button type="submit" id="print" class="btn btn-success" style="width:20rem;margin-top:1rem;">Anteprima flusso</button>
    		<input type="checkbox" name="ultimate" id="ultimate" style="margin-left:5rem;">DEFINITIVO';
        $str_out .= '
		<div class="col-sm-12 table_caption_H" style="height:6rem;text-align:center;line-height:6rem;">
    		' . ChkButton($aUserButton, 'viw', $strButtons) . '
		</div>
		</form>
	</div>';

    }
}

echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {

        $('#TypePlate, #RegularPostalFine, #Search_CountryId').change(function(){
            $('#f_search').submit();
        });

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
        });

        $('#ultimate').click(function(){
            if($('#ultimate').is(":checked")) {
                $('#print').html('Flusso definitivo');
                $('#print').removeClass( "btn-success" ).addClass( "btn-warning" );
            }else{
                $('#print').html('Anteprima flusso');
                $('#print').removeClass( "btn-warning" ).addClass( "btn-success" );
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
                }
            }
        }).on('success.form.bv', function(){
			if($('#ultimate').is(":checked")) {
				if(confirm("Si sta per creare il flusso in maniera definitiva. Continuare?")){
					$('#span_ultimate').hide();
					$('#print').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
					$('#print').prop('disabled', true);
				} else {
                	return false;
				}
			}
        });
        
	});
</script>
<?php
include(INC."/footer.php");
