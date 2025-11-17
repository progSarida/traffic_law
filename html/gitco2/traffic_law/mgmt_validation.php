<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$rs->SetCharset("utf8");

$str_Where = "CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND RuleTypeId=".$_SESSION['ruletypeid'];
$str_Order = "FineDate ASC, FineTime ASC";
$PageTitle = CheckValue('PageTitle','s') ?: '/';

if ($s_TypePlate == "N") {
    $str_Where .= " AND CountryId='Z000'";
} else if ($s_TypePlate == "F"){
    $str_Where .= " AND CountryId!='Z000'";
}
if ($Search_Plate != "") {
    $str_Where .= " AND VehiclePlate='$Search_Plate'";
}
if ($Search_Code != "") {
    $str_Where .= " AND Code LIKE '%$Search_Code%'";
}
if ($Search_FromFineDate != "") {
    $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
}
if ($Search_ToFineDate != "") {
    $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
}
if ($Search_Locality != "") {
    $str_Where .= " AND Locality='$Search_Locality'";
}
if ($Search_Violation > 0) {
    $str_Where .= " AND ViolationTypeId=$Search_Violation";
}
if ($Search_Country != "") {
    $str_Where .= " AND CountryId='$Search_Country'";
}

$rs_Validation = $rs->Select("V_Validation", $str_Where, $str_Order);
$RowNumber = mysqli_num_rows($rs_Validation);
mysqli_data_seek($rs_Validation, $pagelimit);

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_mgmt_validation" action="mgmt_validation.php" method="post" autocomplete="off">
	    <input type="hidden" name="PageTitle" value="<?= $PageTitle ?>">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= $_SESSION['ruletypetitle'] ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array('N' => 'Nazionali', 'F' => 'Estere'), true, 'TypePlate', 'TypePlate', $s_TypePlate); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" value="<?= $Search_Plate; ?>">
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                Prot/Ref
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Code" type="text" value="<?= $Search_Code; ?>">
            </div>   
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectCustomerUnion($Search_Locality); ?>
            </div> 
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate">
            </div>     
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
	</form>
	
	<div class="clean_row HSpace4"></div>
	
	<div class="col-sm-12">
		<div class="table_label_H col-sm-2">Rif.to</div>
		<div class="table_label_H col-sm-1">Data violazione</div>
		<div class="table_label_H col-sm-1">Ora violazione</div>
		<div class="table_label_H col-sm-1">Targa</div>
		<div class="table_label_H col-sm-3">Indirizzo</div>
		<div class="table_label_H col-sm-2">Violazione</div>
		<div class="table_label_H col-sm-1">Articolo</div>
		<div class="table_label_H col-sm-1"></div>
        
        <div class="clean_row HSpace4"></div>
	
    	<?php if ($RowNumber > 0):?>
    		<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
    			<?php if (! empty($r_Validation = $rs->getArrayLine($rs_Validation))): ?>
    		        <div class="tableRow">
                        <div class="table_caption_H col-sm-2"><?= $r_Validation['Code']; ?></div>
        				<div class="table_caption_H col-sm-1"><?= DateOutDB($r_Validation['FineDate']); ?></div>
        				<div class="table_caption_H col-sm-1"><?= TimeOutDB($r_Validation['FineTime']); ?></div>
        				<div class="table_caption_H col-sm-1"><?= $r_Validation['VehiclePlate']; ?> <i class="<?= $aVehicleTypeId[$r_Validation['VehicleTypeId']]; ?>" style="color:#337AB7;"></i></div>
                        <div class="table_caption_H col-sm-3"><?= $r_Validation['Address']; ?></div>
                        <div class="table_caption_H col-sm-2"><?= $r_Validation['ViolationTitle']; ?> </div>
                        <div class="table_caption_H col-sm-1"><?= $r_Validation['Article'] .'/'.$r_Validation['Paragraph'].' '. $r_Validation['Letter']; ?> <?= $r_Validation['ArticleNumber']>1 ? '<i class="fa fa-list-ol" style="position:absolute;right:2rem;top:0.3rem; color:#337AB7; font-size:1.6rem;"></i>' : ''; ?></div>
                        <div class="table_caption_button col-sm-1">
                        	<?= ChkButton($aUserButton, 'upd','<a href="mgmt_validation_upd.php'.$str_GET_Parameter.'&Id='.$r_Validation['Id'].'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="margin-top: 0.4rem;"></span></a>'); ?>
                        </div>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
    			<?php endif; ?>
    		<?php endfor; ?>
	    <?php else: ?>
        	<div class="table_caption_H col-sm-12">
        		Nessun record presente.
        	</div>
        	<div class="clean_row HSpace4"></div>
        <?php endif; ?>
        
        <?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage.$str_GET_Parameter,""); ?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
      	$(".tableRow").mouseover(function(){
  			$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
    });
</script>
<?php
require_once(INC."/footer.php");
