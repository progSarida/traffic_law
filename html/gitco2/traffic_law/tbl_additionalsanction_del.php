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

$rs_AdditionalSanction = $rs->SelectQuery("SELECT A.*, CONCAT(AST.Id, ' - ', AST.Title) AdditionalSanctionTypeTitle FROM AdditionalSanction A LEFT JOIN AdditionalSanctionType AST on A.AdditionalSanctionTypeId = AST.Id WHERE A.Id=$Id");
$r_AdditionalSanction = $rs->getArrayLine($rs_AdditionalSanction);

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_additionalsanction" method="post" action="tbl_additionalsanction_del_exe.php">
    	<input type="hidden" name="Id" value="<?= $Id; ?>">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div class="BoxRowTitle col-sm-12">
           Elimina sanzione accessoria
        </div>
        
        <div class="clean_row HSpace4"></div>
        
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Parametri sanzione accessoria</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="BoxRowLabel col-sm-1">
            Progressivo
        </div>
        <div class="BoxRowCaption col-sm-1">
            <?= $r_AdditionalSanction['Progressive']; ?>
        </div>
        <div class="BoxRowLabel col-sm-2">
            Tipologia
        </div>
        <div class="BoxRowCaption col-sm-6" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
        	<?= $r_AdditionalSanction['AdditionalSanctionTypeTitle']; ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Disabilitato
        </div>
        <div class="BoxRowCaption col-sm-1">
            <?= YesNoOutDB($r_AdditionalSanction['Disabled']); ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
       		Descrizione
    	</div>
        <div class="col-sm-10 BoxRowCaption">
       		<?= $r_AdditionalSanction['Description']; ?>
       	</div>
    	
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
       		Testo verbale nazionale
    	</div>
        <div class="col-sm-10 BoxRowCaption" style="height:6.4rem">
       		<?= $r_AdditionalSanction['DescriptionIta']; ?>    	</div>
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
            	<?= StringOutDB($r_AdditionalSanction['Title'.$tag]); ?>
            </div>
            
            <?php if($LangN % 2 == 0): ?>
            	<div class="clean_row HSpace4"></div>
        	<?php endif; ?>
    	<?php endforeach; ?>
        
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12 text-center" style="line-height:6rem;">
                    <button class="btn btn-danger" type="submit">Elimina</button>
                    <button class="btn btn-default" id="back" type="button">Indietro</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$('document').ready(function(){
	$('#f_additionalsanction').on('submit', function(e){
		if(confirm('Si stà per eliminare la sanzione accessoria in modo definitivo. Continuare?')){
			if(confirm('Si è proprio sicuri di voler continuare?'))
				return true;
			else return false;
		} else return false;
	});

    $('#back').click(function () {
        window.location = "<?= $str_BackPage; ?>";
        return false;
    });
});
</script>

<?php
include(INC."/footer.php");
