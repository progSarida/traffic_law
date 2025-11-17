<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_Where .= " AND (StatusTypeId=35 OR StatusTypeId=36 OR StatusTypeId=37) AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
$strOrder = "ProtocolYear DESC, ProtocolId ASC";

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}

if (isset($_SESSION['Archive']['Error'])) {
    $str_out .= '<div class="alert alert-danger"> '.$_SESSION['Archive']['Error'].'</div>';
}

if (isset($_SESSION['Archive']['Success'])) {
    $str_out .= '<div class="alert alert-success"> '.$_SESSION['Archive']['Success'].'</div>';
}

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_ProtocolId" type="text" style="width:10rem" value="'.$Search_ProtocolId.'">
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
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
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
                '. CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false,15) .'
            </div>                                                
            <div class="col-sm-4 BoxRowLabel">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" style="width:15rem" value="'.$Search_Trespasser.'">
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
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-4">Trasgressore</div>
				<div class="table_label_H col-sm-2">Stato pratica</div>
        		<div class="table_add_button col-sm-1 right">&nbsp;</div>
				<div class="clean_row HSpace4"></div>';


$table_rows = $rs->Select('V_FineComplete',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);


$RowNumber = mysqli_num_rows($table_rows);

if ($RowNumber == 0) {
	$str_out.= 'Nessun record presente';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {


		$str_out.=
			'<div class="table_caption_H col-sm-1"><i class="fa fa-reply" id="'.$table_row['FineId'].'"></i> ' . $table_row['FineId'] .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'].' / '.$table_row['ProtocolYear'].'</div>
        	<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
        	<div class="table_caption_H col-sm-1">' . $table_row['FineTime'] .'</div>
        	<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] .'</div>
			<div class="table_caption_H col-sm-4">' . $table_row['CompanyName'] .' '.$table_row['Surname'] .' '.$table_row['Name'] .'</div>
			';
		$Status = '';

		$rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['FineId']);
		if(mysqli_num_rows($rs_Row)>0){
			$r_Row = mysqli_fetch_array($rs_Row);
			$Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale pagato in data '. DateOutDB($r_Row['PaymentDate']).'"><i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
		}else $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';

		$str_out.=
			'<div class="table_caption_H col-sm-2">' . $Status .'</div>
			<div class="table_caption_button col-sm-1">
				'. ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'"><span class="glyphicon glyphicon-eye-open"></span></a>') .'
				&nbsp;
				'. ChkButton($aUserButton, 'prn','<a href="prn_archive.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'"><span class="fa fa-print" ></span></a>') .'
				&nbsp;		
					
			</div>
			<div class="clean_row HSpace4"></div>';
	}
}
$table_users_number = $rs->Select('V_FineComplete',$str_Where);
$UserNumberTotal = mysqli_num_rows($table_users_number);

$strLabel =' 
 		<div style="position:absolute; top:5px;font-size:1.2rem;color:#fff;width:430px;text-align: left">
	 		<div style="width:140px;float:left;">
	 			<i class="fa fa-check-square-o" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale creato
			</div>
			<div style="width:140px;float:left;">
				<i class="fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale protocollato
			</div>	
			<div style="width:140px;float:left;">
				<i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Flusso creato
			</div>					
			<div style="width:140px;float:left;">
	 			<i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale stampato
			</div>
			<div style="width:140px;float:left;">
				<i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale spedito
			</div>
			<div style="width:140px;float:left;">
	 			<i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale Notificato
			</div>
			<div style="width:140px;float:left;">
				<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale pagato
			</div>
			<div style="width:140px;float:left;">
				<i class="fa fa-exclamation" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale contestato
			</div>		
		</div>
		
		
		
		';


$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,$strLabel);
$str_out.= '<div>
	</div>';

unset($_SESSION['Archive']['Error']);
unset($_SESSION['Archive']['Success']);

echo $str_out;
?>

    <script type="text/javascript">


        $(document).ready(function () {

            $('.fa-reply').click(function() {
                var Id = $(this).attr('id');

                var c = confirm("Vuoi annullare archiviazione per questo verbale?");
                if(c){
                    window.location="mgmt_archive_act_exe.php?Id="+Id;
                }

            });


            <?= require ('inc/jquery/base_search.php')?>

        });

    </script>
<?php
include(INC."/footer.php");
