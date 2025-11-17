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

$rs_Reason = $rs->SelectQuery("SELECT R.*,V.Title AS ViolationTitle FROM Reason R JOIN ViolationType V ON R.ViolationTypeId = V.Id WHERE R.Id=$Id");
$r_Reason = $rs->getArrayLine($rs_Reason);

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_reason" method="post" action="mgmt_reason_del_exe.php">
    	<input type="hidden" name="Id" value="<?= $Id; ?>">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">

        <div class="BoxRowTitle col-sm-12">
           Motivo contestazione
        </div>

        <div class="clean_row HSpace4"></div>
        
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Parametri motivo contestazione</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="BoxRowLabel col-sm-1">
            Progressivo
        </div>
        <div class="BoxRowCaption col-sm-1">
            <?= $r_Reason['Progressive']; ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Matricola
        </div>
        <div class="BoxRowCaption col-sm-1">
            <?= $r_Reason['Code']; ?>
        </div>
        <div class="BoxRowLabel col-sm-2">
            Tipo violazione
        </div>
        <div class="BoxRowCaption col-sm-2">
            <?= $r_Reason['ViolationTitle']; ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Post. Fissa
        </div>
        <div class="BoxRowCaption col-sm-1">
            <?= YesNoOutDB($r_Reason['Fixed']); ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Disabilitato
        </div>
        <div class="BoxRowCaption col-sm-1">
            <?= YesNoOutDB($r_Reason['Disabled']); ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
       		Descrizione
    	</div>
        <div class="col-sm-10 BoxRowCaption">
       		<?= $r_Reason['Description']; ?>
    	</div>
    	
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
       		Testo verbale nazionale
    	</div>
        <div class="col-sm-10 BoxRowCaption" style="height:6.4rem">
       		<?= $r_Reason['DescriptionIta']; ?>
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
            	<?= StringOutDB($r_Reason['Title'.$tag]); ?>
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

	$('#f_reason').on('submit', function(e){
		if(confirm('Si stà per eliminare il motivo di mancata contestazione in modo definitivo. Continuare?')){
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
