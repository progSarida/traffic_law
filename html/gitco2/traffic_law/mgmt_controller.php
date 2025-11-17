<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_controller.php");
require_once(INC."/header.php");
require_once(INC."/initialization.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_UsedControllers = array();

$str_Where = mgmtControllerWhere();
$str_Order = mgmtControllerOrderBy();

$rs_Controller = $rs->Select("Controller", $str_Where, $str_Order);
$RowNumber = mysqli_num_rows($rs_Controller);
mysqli_data_seek($rs_Controller, $pagelimit);

$rs_UsedControllers = $rs->SelectQuery("
    SELECT NULLIF(F.ControllerId, 0),NULLIF(F.FineChiefControllerId, 0),NULLIF(F2.ControllerId, 0) 
    FROM Fine F 
    LEFT JOIN FineAdditionalController F2 ON F.Id = F2.FineId 
    WHERE F.CityId = '{$_SESSION['cityid']}' 
    GROUP BY F.ControllerId,F.FineChiefControllerId,F2.ControllerId");
while($r_UsedControllers = $rs->getArrayLine($rs_UsedControllers)){
    foreach ($r_UsedControllers as $value){
        if(!is_null($value)) $a_UsedControllers[] = $value;
    }
}

$a_UsedControllers = array_unique($a_UsedControllers);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_search" action="mgmt_controller.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?=$PageTitle;?>">

        <div class="col-sm-11" style="height:4.5rem; border-right:1px solid #E7E7E7;">
        	<div class="col-sm-1 BoxRowLabel">
        		Matricola
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" name="Search_Code" class="form-control frm_field_numeric" value="<?= $Search_Code; ?>">
        	</div>
    		<div class="col-sm-1 BoxRowLabel">
        		Nominativo
        	</div>
        	<div class="col-sm-3 BoxRowCaption">
        		<input type="text" name="Search_Name" class="form-control frm_field_string" value="<?= $Search_Name; ?>">
        	</div>
    		<div class="col-sm-1 BoxRowLabel">
        		Da data incarico
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" name="Search_FromDate" class="form-control frm_field_date" value="<?= $Search_FromDate; ?>">
        	</div>
    		<div class="col-sm-1 BoxRowLabel">
        		A data incarico
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" name="Search_ToDate" class="form-control frm_field_date" value="<?= $Search_ToDate; ?>">
        	</div>
        	<div class="col-sm-2 BoxRowLabel"></div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="col-sm-1 BoxRowLabel">
        		Qualifica
        	</div>
        	<div class="col-sm-2 BoxRowCaption">
        		<?= CreateSelect("QualificationType","CityId='".$_SESSION['cityid']."'","Description","Search_Genre","Description","Description",$Search_Genre,false); ?>
        	</div>
        	<div class="col-sm-9 BoxRowLabel"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="font-size:3rem;padding:0;margin:0;width:100%;height:100%">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div>
    </form>

    <div class="clean_row HSpace4"></div>

    <div class="col-sm-12">
        <div class="table_label_H col-sm-1">Matricola <?= sortButton('mgmt_controller.php'.$str_GET_Parameter, 'Order_Code', $Order_Code, null, null, null, 'color:white') ?></div>
    	<div class="table_label_H col-sm-2">Qualifica <?= sortButton('mgmt_controller.php'.$str_GET_Parameter, 'Order_Type', $Order_Type, null, null, null, 'color:white') ?></div>
        <div class="table_label_H col-sm-5">Nome <?= sortButton('mgmt_controller.php'.$str_GET_Parameter, 'Order_Name', $Order_Name, null, null, null, 'color:white') ?></div>
        <div class="table_label_H col-sm-1">Incarico dal <?= sortButton('mgmt_controller.php'.$str_GET_Parameter, 'Order_FromDate', $Order_FromDate, null, null, null, 'color:white') ?></div>
        <div class="table_label_H col-sm-1">Incarico al <?= sortButton('mgmt_controller.php'.$str_GET_Parameter, 'Order_ToDate', $Order_ToDate, null, null, null, 'color:white') ?></div>
        <div class="table_label_H col-sm-1">Abilitato</div>
        <div class="table_add_button col-sm-1">
            <?= ChkButton($aUserButton, 'add','
                <a href="mgmt_controller_add.php'.$str_GET_Parameter.'">
                    <span data-container="body" data-toggle="tooltip" data-placement="top" title="Inserisci" class="glyphicon glyphicon-plus-sign add_button tooltip-r" style="margin-right:0.3rem; "></span>
                </a>');?>
        </div>
        <div class="clean_row HSpace4"></div>

        <?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_Controller = mysqli_fetch_assoc($rs_Controller);?>
				<?php if (! empty($r_Controller)): ?>
    	            <div class="tableRow">
    	            	<div class="table_caption_H col-sm-1"><?= $r_Controller['Code']; ?></div>
    	            	<div class="table_caption_H col-sm-2"><?= $r_Controller['Qualification']; ?></div>
						<div class="table_caption_H col-sm-5"><?= $r_Controller['Name']; ?></div>
						<div class="table_caption_H col-sm-1"><?= DateOutDB($r_Controller['FromDate']); ?></div>
						<div class="table_caption_H col-sm-1"><?= DateOutDB($r_Controller['ToDate']); ?></div>
                        <div class="table_caption_H col-sm-1 text-center">
                            <?php if($r_Controller['Disabled'] == 1): ?>
                            	<span class="fa fa-times" aria-hidden="true" style="line-height: 2rem;color: red;font-size: 1.3rem;"></span>
                            <?php else: ?>
                            	<span class="fa fa-check" aria-hidden="true" style="line-height: 2rem;color: green;font-size: 1.3rem;"></span>
                            <?php endif; ?>
                        </div>
                        <div class="table_caption_button col-sm-1" style="line-height: 2.4rem;">
                        	<?= ChkButton($aUserButton, 'upd','<a href="mgmt_controller_viw.php'.$str_GET_Parameter.'&Id='.$r_Controller['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza" class="glyphicon glyphicon-eye-open tooltip-r"></span></a>&nbsp;');?>
                            <?= ChkButton($aUserButton, 'upd','<a href="mgmt_controller_upd.php'.$str_GET_Parameter.'&Id='.$r_Controller['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;');?>
                            <?php if(!in_array($r_Controller['Id'], $a_UsedControllers)): ?>
                            	<?= ChkButton($aUserButton, 'del','<a class="deletereason" href="mgmt_controller_del.php'.$str_GET_Parameter.'&Id='.$r_Controller['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Elimina" class="glyphicon glyphicon-remove-sign tooltip-r"></span></a>&nbsp;');?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
				<?php endif; ?>
			<?php endfor; ?>
			<?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage.$str_GET_Parameter, ''); ?>
		<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente
            </div>
		<?php endif; ?>
    </div>
</div>
<?php
include (INC . "/footer.php");
