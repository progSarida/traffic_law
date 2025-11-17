<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC."/initialization.php");
require(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

$Id = CheckValue('Id', 'n');

$a_Lan = unserialize(LANGUAGE_KEYS);

$rs_AdditionalSanction = $rs->Select("AdditionalSanction", "Id=$Id");
$r_AdditionalSanction = $rs->getArrayLine($rs_AdditionalSanction);

$rs_AdditionalSanctions = $rs->SelectQuery("SELECT A.Id FROM AdditionalSanction A JOIN AdditionalSanctionType AST ON A.AdditionalSanctionTypeId=AST.Id WHERE A.CityId = '{$_SESSION['cityid']}' AND AST.RuleTypeId={$_SESSION['ruletypeid']} ORDER BY Progressive");
while ($r_AdditionalSanctions = mysqli_fetch_assoc($rs_AdditionalSanctions)){
    $a_AdditionalSanctions[] = $r_AdditionalSanctions['Id'];
}
$Key = array_search($Id, $a_AdditionalSanctions);
$PreviousId = array_key_exists(($Key-1),$a_AdditionalSanctions) ? $a_AdditionalSanctions[$Key-1] : null;
$NextId = array_key_exists(($Key+1),$a_AdditionalSanctions) ? $a_AdditionalSanctions[$Key+1] : null;

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_additionalsanction" method="post" action="tbl_additionalsanction_upd_exe.php">
    	<input type="hidden" name="Id" value="<?= $Id; ?>">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
    	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
    		<?php if(!empty($PreviousId)): ?>
            	<a href="<?= impostaParametriUrl(array('Id' => $PreviousId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Precedente" class="tooltip-r glyphicon glyphicon-arrow-left" style="font-size:3rem;color:#fff"></i></a>
        	<?php endif; ?>
        </div>
        <div class="BoxRowTitle col-sm-10" style="width:83.33%;">
           Modifica sanzione accessoria
        </div>
    	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
    		<?php if(!empty($NextId)): ?>
            	<a href="<?= impostaParametriUrl(array('Id' => $NextId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Prossimo" class="tooltip-r glyphicon glyphicon-arrow-right" style="font-size:3rem;color:#fff"></i></a>
        	<?php endif; ?>
        </div>

        <div class="clean_row HSpace4"></div>
        
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Parametri sanzione accessoria</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="BoxRowLabel col-sm-1">
            Progressivo
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input class="form-control text-center" type="text" disabled value="<?= $r_AdditionalSanction['Progressive']; ?>">
        </div>
        <div class="BoxRowLabel col-sm-1">
            Tipologia
        </div>
        <div class="BoxRowCaption col-sm-7">
        	<?= CreateSelectQuery("SELECT Id,CONCAT(Id, ' - ', Title) AS Title FROM AdditionalSanctionType WHERE RuleTypeId={$_SESSION['ruletypeid']}", 'AdditionalSanctionTypeId', 'Id', 'Title', $r_AdditionalSanction['AdditionalSanctionTypeId'], true) ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Disabilitato
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input type="checkbox" name="Disabled" value="1" <?= ChkCheckButton($r_AdditionalSanction['Disabled']); ?>>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
       		Descrizione (max 60 caratteri)
    	</div>
        <div class="col-sm-10 BoxRowCaption">
       		<input id="Description" name="Description" type="text" class="form-control frm_field_string txt-warning" value="<?= StringOutDB($r_AdditionalSanction['Description']); ?>">
    	</div>
    	
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
       		Testo verbale nazionale
    	</div>
        <div class="col-sm-10 BoxRowCaption" style="height:6.4rem">
       		<textarea name="DescriptionIta" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;"><?= StringOutDB($r_AdditionalSanction['DescriptionIta']); ?></textarea>
    	</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Testi per verbali esteri</div>
        
        <div class="clean_row HSpace4"></div>
        
        <?php $LangN = 0; ?>
    	<?php foreach ($a_Lan as $name => $tag): ?>
    		<?php $LangN++; ?>
			<div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
            	<img src="<?= IMG.'/f_'.strtolower($tag).'.png'; ?>" style="width:16px" alt="<?= $tag; ?>" /> Testo <?= $name; ?>
            <?php if ($name == 'Italiano'): ?>
            	(Usato anche nelle descrizioni delle listbox)
            <?php endif; ?>
            </div>
            <div class="col-sm-4 BoxRowCaption" style="height:6.4rem">
            	<textarea name="Title<?= $tag; ?>" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;"><?= StringOutDB($r_AdditionalSanction['Title'.$tag]); ?></textarea>
            </div>
            
            <?php if($LangN % 2 == 0): ?>
            	<div class="clean_row HSpace4"></div>
        	<?php endif; ?>
    	<?php endforeach; ?>
        
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12 text-center" style="line-height:6rem;">
                    <button class="btn btn-success" type="submit"><i class="fa fa-save fa-fw"></i> Salva</button>
                    <button class="btn btn-default" id="back" type="button">Indietro</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$('document').ready(function(){
    $('#f_additionalsanction').bootstrapValidator({
        live: 'disabled',
        fields: {
            frm_field_required: {
                selector: '.frm_field_required',
                validators: {
                    notEmpty: {
                        message: 'Richiesto'
                    }
                }
            },
            Description: {
                validators: {
                    stringLength: {
                        max: 60,
                        message: 'Max 60 caratteri'
                    },
                    notEmpty: {
                        message: 'Richiesto'
                    }
                }
            },
        }
    });

    $('#back').click(function () {
        window.location = "<?= $str_BackPage; ?>";
        return false;
    });
});
</script>

<?php
include(INC."/footer.php");
