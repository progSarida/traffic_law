<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_brandmodel.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');

$str_Where = mgmtAnomalyBrandModelWhere();
$str_Order = mgmtAnomalyBrandModelOrderBy();

$cls_view = new CLS_VIEW(MGMT_BRANDMODEL);
$query = $cls_view->generateSelect($str_Where, null, $str_Order);
$rs_AnomalyBrandModel = $rs->SelectQuery($query);
$RowNumber = mysqli_num_rows($rs_AnomalyBrandModel);
mysqli_data_seek($rs_AnomalyBrandModel, $pagelimit);

echo $str_out;
?>
<div class="row-fluid">
    <form name="f_search" id="f_search" action="mgmt_brandmodel.php" method="post">
        <input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <div class="col-sm-11" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-2 BoxRowLabel">
                Data importazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input value="<?= $Search_Date; ?>" type="text" class="form-control frm_field_date" id="Search_Date" name="Search_Date">  
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Marca
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input value="<?= $Search_Brand; ?>" type="text" class="form-control frm_field_string" id="Search_Brand" name="Search_Brand">  
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Modello
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input value="<?= $Search_Model; ?>" type="text" class="form-control frm_field_string" id="Search_Model" name="Search_Model">  
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect('VehicleType', '1=1', 'TitleIta', 'Search_VehicleType', 'Id', 'TitleIta', $Search_VehicleType, false); ?>
            </div>
            
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

	<div class="table_label_H col-sm-1">Data imp. <?= sortButton('mgmt_brandmodel.php'.$str_GET_Parameter, 'Order_Date', $Order_Date, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-4">Marca <?= sortButton('mgmt_brandmodel.php'.$str_GET_Parameter, 'Order_Brand', $Order_Brand, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-4">Modello <?= sortButton('mgmt_brandmodel.php'.$str_GET_Parameter, 'Order_Model', $Order_Model, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-2">Tipo veicolo <?= sortButton('mgmt_brandmodel.php'.$str_GET_Parameter, 'Order_Type', $Order_Type, null, null, null, 'color:white') ?></div>
    <div class="table_label_H col-sm-1"></div>

    <div class="clean_row HSpace4"></div>
    
    <?php if ($RowNumber > 0):?>
		<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
			<?php $r_AnomalyBrandModel = $rs->getArrayLine($rs_AnomalyBrandModel);?>
			<?php if (! empty($r_AnomalyBrandModel)): ?>
		        <div class="tableRow">
                	<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($r_AnomalyBrandModel['DataSourceDate']); ?></div>
                	<div class="table_caption_H col-sm-4 text-center"><?= $r_AnomalyBrandModel['Brand']; ?></div>
                	<div class="table_caption_H col-sm-4 text-center"><?= $r_AnomalyBrandModel['Model']; ?></div>
                	<div class="table_caption_H col-sm-2 text-center"><?= $r_AnomalyBrandModel['VehicleTypeTitle']; ?></div>
                	<div class="table_caption_button col-sm-1 text-center">
                		<?= ChkButton($aUserButton, 'act','<a href="mgmt_brandmodel_act.php'.$str_GET_Parameter.'&AnomalyBrandModelId='.$r_AnomalyBrandModel['Id'].'&Action=fix"><span data-toggle="tooltip" data-placement="top" title="Correggi dati errati" class="tooltip-r fas fa-wrench fa-fw" style="margin-top: 0.4rem;"></span></a>'); ?>
                		<?php if(mgmtAnomalyBrandModelCanConfirm($r_AnomalyBrandModel)): ?>
                			<?= ChkButton($aUserButton, 'act','<a href="mgmt_brandmodel_act.php'.$str_GET_Parameter.'&AnomalyBrandModelId='.$r_AnomalyBrandModel['Id'].'&Action=confirm"><span data-toggle="tooltip" data-placement="top" title="Contrassegna come corretto in origine" class="tooltip-r fas fa-clipboard-check fa-fw"></span></a>'); ?>
                		<?php endif; ?>
                		<?= ChkButton($aUserButton, 'del','<a href="mgmt_brandmodel_del.php'.$str_GET_Parameter.'&AnomalyBrandModelId='.$r_AnomalyBrandModel['Id'].'"><span data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r fas fa-times fa-fw"></span></a>'); ?>
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
    
    <?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage,""); ?>
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