<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$str_Union = CreateSelectCustomerUnion($Search_Locality);


$str_Where .= " AND StatusTypeId<11 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

$strOrder = "FineDate ASC, FineTime ASC";

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}

$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-2 BoxRowLabel">
                
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" style="width:8rem" value="'.$Search_Plate.'">
            </div>          
            <div class="col-sm-1 BoxRowLabel">
                Prot/Ref
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:10rem" value="'.$Search_Ref.'">
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1 AND RuleTypeId = $RuleTypeId","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. $str_Union .'
            </div>              
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.$RuleTypeTitle.'
            </div>            
            
            <div class="clean_row HSpace4"></div>
            
            
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
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false,12) .'
            </div>                                                
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("StatusType","Id<=10","Id","Search_Status","Id","Title",$Search_Status,false,16) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" style="width:12rem">
            </div>             
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>	
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
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Rif.to</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">Articolo</div>
				<div class="table_label_H col-sm-2">Noleggio</div>
				<div class="table_label_H col-sm-2">Conducente</div>
				<div class="table_label_H col-sm-1">Stato</div>
        		<div class="table_add_button col-sm-1 right">
        			
        				'.ChkButton($aUserButton, 'add','
                            <a href="mgmt_report_add.php'.$str_GET_Parameter.'&insertionType=4">
                            <span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
                            </a>
                            ').'      				
					
				</div>
				<div class="clean_row HSpace4"></div>';



$table_rows = $rs->Select('V_ViolationRent_List',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($table_rows);

if ($RowNumber == 0) {
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {

        $rs_Document = $rs->SelectQuery("SELECT COUNT(*) Tot FROM FineDocumentation WHERE FineId=".$table_row['Id']);
        $r_Document = mysqli_fetch_array($rs_Document);

	    $str_StatusTypeId = ($table_row['StatusTypeId']==2) ? $str_StatusTypeId = '<span class="glyphicon glyphicon-list-alt" style="color:#337AB7;position:absolute;right:4px;top:4px;"> '.$r_Document['Tot'].'</span>' : '';


        $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Id'] .' '.$str_StatusTypeId.'</div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Code'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Article'] .'/'.$table_row['Paragraph'].' '. $table_row['Letter'] .'</div>';


		$Status = "";

        $n_StatusRent = 1;
        $str_Trespasser="";
        $rs_TrespasserRent = $rs->Select('V_FineTrespasser', "TrespasserTypeId=10 AND FineId=".$table_row['Id']);
        $FindNumber = mysqli_num_rows($rs_TrespasserRent);
        if($FindNumber==0){
            $n_StatusRent = 0;
        }else{
            $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
            $str_Trespasser = trim($r_TrespasserRent['CompanyName']." ".$r_TrespasserRent['Surname']." ".$r_TrespasserRent['Name']);
        }
        $str_out.= '<div class="table_caption_H col-sm-2">' . $str_Trespasser .'</div>';


        $str_Trespasser="";
        $rs_TrespasserRent = $rs->Select('V_FineTrespasser', "TrespasserTypeId=11 AND FineId=".$table_row['Id']);
        $FindNumber = mysqli_num_rows($rs_TrespasserRent);
        if($FindNumber==0){
            $n_StatusRent = 0;
        }else{
            $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
            $str_Trespasser = trim($r_TrespasserRent['CompanyName']." ".$r_TrespasserRent['Surname']." ".$r_TrespasserRent['Name']);
        }
        $str_out.= '<div class="table_caption_H col-sm-2">' . $str_Trespasser .'</div>';




		if($n_StatusRent) $Status = '#3c763d';
		elseif($table_row['StatusTypeId']==5) $Status = '#E0DB75';
		else $Status = '#C43A3A';

		$str_out.= '<div class="table_caption_H col-sm-1" style="text-align:center;"><i class="material-icons" style="color: ' . $Status .'">local_gas_station</i></div>';


		$str_out.= '<div class="table_caption_button col-sm-1">';
		$str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');

		$str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');

        if(!$n_StatusRent){
            $str_out.= ChkButton($aUserButton, 'act','<a href="mgmt_violation_trespasser.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-user" style="position:absolute;left:45px;top:5px;"></span></a>');
        }

		$str_out.= ChkButton($aUserButton, 'del','<a href="mgmt_violation_del.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-remove-sign" style="position:absolute;left:70px;top:5px;"></span></a>');

        $str_out.= ChkButton($aUserButton, 'exp','<a href="mgmt_violation_exp.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-remove-sign" style="position:absolute;left:70px;top:5px;"></span></a>');

		$str_out.= '</div>
			            <div class="clean_row HSpace4"></div>';
		}


}
$table_users_number = $rs->Select('V_ViolationRent_List',$str_Where, 'Id');
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");
$str_out.= '<div>
	</div>';


echo $str_out;
?>

<script type="text/javascript">

	$(document).ready(function () {
        <?= require ('inc/jquery/base_search.php')?>

		$('.table_add_button').click(function(){
			window.location.href='mgmt_violation_add.php';
		});
	});
</script>
<?php
include(INC."/footer.php");
