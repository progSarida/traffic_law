<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC."/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_UsedReasons = array();

$str_Where .= " AND R.CityId='{$_SESSION['cityid']}'";

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$rs_Reason = $rs->SelectQuery("SELECT R.*, V.Title ViolationTypeTitle FROM Reason R JOIN ViolationType V on R.ViolationTypeId = V.Id WHERE $str_Where ORDER BY Progressive");
$RowNumber = mysqli_num_rows($rs_Reason);
mysqli_data_seek($rs_Reason, $pagelimit);

//Questa query viene eseguita per vincolare la cancellazione solo a motivi non utilizzati.
//Non viene discriminata per regolamento perchè potrebbe esserci qualche caso in cui l'operatore sbaglia ad assegnare il regolamento e cancelli erroneamente dei motivi che non deve
$rs_UsedReasons = $rs->SelectQuery("SELECT ReasonId FROM FineArticle WHERE CityId='{$_SESSION['cityid']}' GROUP BY ReasonId");
while($r_UsedReasons = $rs->getArrayLine($rs_UsedReasons)){
    $a_UsedReasons[] = $r_UsedReasons['ReasonId'];
}

echo $str_out;
?>

<div class="row-fluid">
    <div class="col-sm-12">
    	<div class="table_label_H col-sm-1">Progressivo</div>
        <div class="table_label_H col-sm-1">Tipo</div>
        <div class="table_label_H col-sm-3">Descrizione</div>
        <div class="table_label_H col-sm-3">Testo verbale nazionale</div>
        <div class="table_label_H col-sm-1">Matricola</div>
        <div class="table_label_H col-sm-1">Post. Fissa</div>
        <div class="table_label_H col-sm-1">Abilitato</div>
        <div class="table_add_button col-sm-1">
            <?= ChkButton($aUserButton, 'add','<a href="mgmt_reason_add.php'.$str_GET_Parameter.'"><span data-container="body" data-toggle="tooltip" data-placement="left" title="Inserisci"  class="add_button glyphicon glyphicon-plus-sign tooltip-r" style="margin-right:0.3rem; "></span></a>');?>
        </div>
        <div class="clean_row HSpace4"></div>

        <?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_Reason = mysqli_fetch_assoc($rs_Reason);?>
				<?php if (! empty($r_Reason)): ?>
    	            <div class="tableRow">
    	            	<div class="table_caption_H col-sm-1"><?= $r_Reason['Progressive']; ?></div>
                        <div class="table_caption_H col-sm-1" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= StringOutDB($r_Reason['ViolationTypeTitle']); ?></div>
                        <div class="table_caption_H col-sm-3" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= StringOutDB($r_Reason['Description']); ?></div>
                        <div class="table_caption_H col-sm-3" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= StringOutDB($r_Reason['DescriptionIta']); ?></div>
                        <div class="table_caption_H col-sm-1"><?= $r_Reason['Code']; ?></div>
                        <div class="table_caption_H col-sm-1"><?= isset($r_Reason['Fixed']) ? ($r_Reason['Fixed'] == 1 ? 'SI' : 'NO') : ''; ?></div>
                        <div class="table_caption_H col-sm-1 text-center">
                            <?php if($r_Reason['Disabled'] == 1): ?>
                            	<span class="fa fa-times" aria-hidden="true" style="line-height: 2rem;color: red;font-size: 1.3rem;"></span>
                            <?php else: ?>
                            	<span class="fa fa-check" aria-hidden="true" style="line-height: 2rem;color: green;font-size: 1.3rem;"></span>
                            <?php endif; ?>
                        </div>
                        <div class="table_caption_button col-sm-1" style="line-height: 2.4rem;">
                            <?= ChkButton($aUserButton, 'upd','<a href="mgmt_reason_upd.php'.$str_GET_Parameter.'&Id='.$r_Reason['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;');?>
                            <?php if(!in_array($r_Reason['Id'], $a_UsedReasons)): ?>
                            	<?= ChkButton($aUserButton, 'del','<a href="mgmt_reason_del.php'.$str_GET_Parameter.'&Id='.$r_Reason['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Elimina" class="glyphicon glyphicon-remove-sign tooltip-r"></span></a>&nbsp;');?>
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

