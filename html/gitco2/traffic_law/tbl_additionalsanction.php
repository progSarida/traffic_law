<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC."/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_UsedASanctions = array();

$rs_AdditionalSanction = $rs->SelectQuery("SELECT A.*, CONCAT(AST.Id, ' - ', AST.Title) AdditionalSanctionTypeTitle FROM AdditionalSanction A JOIN AdditionalSanctionType AST on A.AdditionalSanctionTypeId = AST.Id WHERE CityId='{$_SESSION['cityid']}' AND AST.RuleTypeId={$_SESSION['ruletypeid']} ORDER BY Progressive");
$RowNumber = mysqli_num_rows($rs_AdditionalSanction);
mysqli_data_seek($rs_AdditionalSanction, $pagelimit);

$rs_UsedASanctions = $rs->SelectQuery("
    SELECT NULLIF(A1.AdditionalSanctionId , 0), NULLIF(A2.AdditionalSanctionId , 0)
    FROM Fine F 
    JOIN FineArticle F2 ON F2.FineId = F.Id
    LEFT JOIN FineAdditionalArticle F3 ON F3.FineId = F.Id 
    LEFT JOIN ArticleTariff A1 ON A1.ArticleId = F2.ArticleId
    LEFT JOIN ArticleTariff A2 ON A2.ArticleId = F3.ArticleId
    WHERE F.CityId = '{$_SESSION['cityid']}'
    GROUP BY A1.AdditionalSanctionId, A2.AdditionalSanctionId;
");
while($r_UsedASanctions = $rs->getArrayLine($rs_UsedASanctions)){
    foreach ($r_UsedASanctions as $value){
        if(!is_null($value)) $a_UsedASanctions[] = $value;
    }
}

$a_UsedASanctions = array_unique($a_UsedASanctions);

echo $str_out;
?>

<div class="row-fluid">
    <div class="col-sm-12">
    	<div class="table_label_H col-sm-1">Progressivo</div>
        <div class="table_label_H col-sm-3">Tipo</div>
        <div class="table_label_H col-sm-3">Descrizione</div>
        <div class="table_label_H col-sm-3">Testo verbale nazionale</div>
        <div class="table_label_H col-sm-1">Abilitato</div>
        <div class="table_add_button col-sm-1">
            <?= ChkButton($aUserButton, 'add','<a href="tbl_additionalsanction_add.php'.$str_GET_Parameter.'"><span data-container="body" data-toggle="tooltip" data-placement="left" title="Inserisci"  class="add_button glyphicon glyphicon-plus-sign tooltip-r" style="margin-right:0.3rem; "></span></a>');?>
        </div>
        <div class="clean_row HSpace4"></div>

        <?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_AdditionalSanction = mysqli_fetch_assoc($rs_AdditionalSanction);?>
				<?php if (! empty($r_AdditionalSanction)): ?>
    	            <div class="tableRow">
    	            	<div class="table_caption_H col-sm-1"><?= $r_AdditionalSanction['Progressive']; ?></div>
                        <div class="table_caption_H col-sm-3" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= StringOutDB($r_AdditionalSanction['AdditionalSanctionTypeTitle']); ?></div>
                        <div class="table_caption_H col-sm-3" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= StringOutDB($r_AdditionalSanction['Description']); ?></div>
                        <div class="table_caption_H col-sm-3" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><?= StringOutDB($r_AdditionalSanction['DescriptionIta']); ?></div>
                        <div class="table_caption_H col-sm-1 text-center">
                            <?php if($r_AdditionalSanction['Disabled'] == 1): ?>
                            	<span class="fa fa-times" aria-hidden="true" style="line-height: 2rem;color: red;font-size: 1.3rem;"></span>
                            <?php else: ?>
                            	<span class="fa fa-check" aria-hidden="true" style="line-height: 2rem;color: green;font-size: 1.3rem;"></span>
                            <?php endif; ?>
                        </div>
                        <div class="table_caption_button col-sm-1" style="line-height: 2.4rem;">
                            <?= ChkButton($aUserButton, 'upd','<a href="tbl_additionalsanction_upd.php'.$str_GET_Parameter.'&Id='.$r_AdditionalSanction['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;');?>
                            <?php if(!in_array($r_AdditionalSanction['Id'], $a_UsedASanctions)): ?>
                            	<?= ChkButton($aUserButton, 'del','<a href="tbl_additionalsanction_del.php'.$str_GET_Parameter.'&Id='.$r_AdditionalSanction['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Elimina" class="glyphicon glyphicon-remove-sign tooltip-r"></span></a>&nbsp;');?>
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

