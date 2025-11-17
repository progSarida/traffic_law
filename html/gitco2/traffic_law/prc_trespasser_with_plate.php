<?php
require("_path.php");
require(INC ."/parameter.php");
require(CLS ."/cls_db.php");
include(CLS . "/cls_message.php");
require(INC ."/function.php");
require(INC ."/header.php");
require(INC .'/menu_'.$_SESSION['UserMenuType'].'.php');

$message=new CLS_MESSAGE();
$PageTitle = CheckValue('PageTitle','s');

$RecordLimit 	= CheckValue('RecordLimit','n') == 0 ? 5 : CheckValue('RecordLimit','n');
$ControllerId 	= CheckValue('Search_ControllerId','n');
$FromFineDate	= CheckValue('Search_FromFineDate','s');
$FromToDate		= CheckValue('Search_ToFineDate','s');

if(!PRODUCTION){
    $message->addWarning("Il collegamento FTP è disponibile solo in ambiente di produzione, lettura/scrittura su cartella locale TESTVISURE_FOLDER/massive_ws");
}

$str_Where = "CityId='". $_SESSION['cityid'] ."' AND ControllerId IS NOT NULL AND (StatusTypeId=1 OR StatusTypeId=14) AND CountryId='Z000' AND Id NOT IN (SELECT FineId FROM FineTrespasser) AND Id NOT IN (SELECT FineId FROM FineAnomaly)";

if($ControllerId>0) 		$str_Where .= " AND ControllerId=". $ControllerId;  
if($FromFineDate!='') 		$str_Where .= " AND Finedate>='". DateInDB($FromFineDate) ."'";  
if($FromToDate!='') 		$str_Where .= " AND Finedate<='". DateInDB($FromToDate) ."'";    


$str_out .= '
<div class="row-fluid">
    '.$message->getMessagesString().'
    <form id="f_search" action="prc_trespasser_with_plate.php'.$str_GET_Parameter.'" method="post" autocomplete="off">
        <input type="hidden" name="PageTitle" value="'.$PageTitle.'">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array(5,25,50,100,200), false, 'RecordLimit', 'RecordLimit', $RecordLimit, true).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Accertatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelectQuery("SELECT Id, CONCAT(Code,' - ',Name) AS ControllerName FROM Controller WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 ORDER BY Name","Search_ControllerId","Id","ControllerName",$Search_ControllerId,false) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" id="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" id="Search_ToFineDate">
            </div>            
        </div>    
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align:center;">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:6.8rem;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';



$str_out .='        
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="prc_trespasser_with_plate_exe.php'.$str_GET_Parameter.'" method="post">
        	<input type="hidden" name="P" value="prc_trespasser_with_plate.php" />
        	<input type="hidden" name="RegularPostalFine" value="'. $RegularPostalFine .'" />
            <input type="hidden" name="PageTitle" value="'.$PageTitle.'">
            
        	<div class="col-sm-12">
                <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked /></div>
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-3">Codice</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-3">Targa</div>
				<div class="table_label_H col-sm-1">Nazione</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				
				<div class="clean_row HSpace4"></div>';



	$strOrder = "FineDate ASC, FineTime ASC, Id ASC";


	if($RecordLimit>0){
		$strOrder .= " LIMIT $RecordLimit";

	}
	$table_rows = $rs->Select('Fine',$str_Where, $strOrder);

	$RowNumber = mysqli_num_rows($table_rows);
    $n_ContRow = 0;
	$str_out.= '
			<input type="hidden" name="TypePlate" value="'.$s_TypePlate.'">';

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12 text-center">
			    Nessun record presente
		    </div>';
	} else {
	    $n_FineId = 0;
	    $n_Row = 1;
		while ($table_row = mysqli_fetch_array($table_rows)) {
		       
            $str_CssController = "";

            if($n_FineId!=$table_row['Id']){
                $str_Check ='			
				    <input checked type="checkbox" name="checkbox[]" value="' . $table_row['Id'] . '"/>
			        ';
                $n_FineId = $table_row['Id'];
            } else $str_Check = '';


            if($table_row['ControllerId']=="") $str_CssController = ' style="background-color:rgba(107,155,29,0.76)"';

            if($table_row['KindSendDate'] != ''){
                $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Invito in AG"></i>';
            } else $FineIcon = $a_FineTypeId[$table_row['FineTypeId']];
            
			$str_out.= '
            <div class="tableRow">
    			<div class="col-sm-1" style="text-align:center;padding:0">
        			<div class="table_caption_button col-sm-6" style="text-align:center;">
                        '. $str_Check .'
    				</div>
        			<div class="table_caption_H col-sm-6" style="text-align:center;">
        				'. $n_Row++ .'
    				</div>
    			</div>
				<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>
    			<div class="table_caption_H col-sm-3">' . $table_row['Code'] .'</div>
    			<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
    			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>
    			<div class="table_caption_H col-sm-3">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>
    			<div class="table_caption_H col-sm-1">' . $table_row['VehicleCountry'] .'</div>
    	
    			<div class="table_caption_button col-sm-1">
    			'. ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>') .'
    			&nbsp;
    			'. ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id']."&#38;TypePlate=".$s_TypePlate."&ControllerId=".$Search_ControllerId."&RecordLimit=".$RecordLimit.'"><span class="glyphicon glyphicon-pencil"></span></a>') .'
    			&nbsp;
    			</div>
            </div>
			<div class="clean_row HSpace4"></div>';
		}



        $str_out.= '
    	    <div class="table_label_H HSpace4" style="height:8rem;">
    	    	<div style="padding-top:2rem;">
        	    	<button type="submit" id="sub_Button" class="btn btn-success" style="width:16rem;">Invia richiesta</button>
    	    	</div>
            </div>
		</form>
	</div>';



	}




echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_print").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_print").trigger( "check" );
        });


        $('#sub_Button').click(function() {
            $("#sub_Button").hide();
        });

    });
</script>
<?php
include(INC."/footer.php");
