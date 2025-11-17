<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$str_Where .= " AND CityId='".$_SESSION['cityid']."'";
$strOrder = "Id DESC";

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
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
            </div>  
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false,24) .'
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
				<div class="table_label_H col-sm-1">Protocollo</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-4">Messaggio</div>
				<div class="table_label_H col-sm-4">Errore</div>
				<div class="clean_row HSpace4"></div>';



$table_rows = $rs->Select('V_Protocol',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($table_rows);

if ($RowNumber == 0) {
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {

		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['ExternalDate']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['ExternalTime'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-4">' . $table_row['ProtocolMessage'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-4">' . $table_row['ProtocolError'] .'</div>';

		$str_out.= '<div class="clean_row HSpace4"></div>';
		}


}
$table_users_number = $rs->Select('V_Protocol',$str_Where, 'Id');
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");
$str_out.= '<div>
	</div>';


echo $str_out;
?>
<script type="text/javascript">


    $(document).ready(function () {
        <?= require ('inc/jquery/base_search.php')?>

    });

</script>
<?php
include(INC."/footer.php");
