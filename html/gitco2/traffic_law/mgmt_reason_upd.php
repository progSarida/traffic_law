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
$rs_Detector = $rs->SelectQuery("SELECT Id FROM Detector WHERE CityId='".$_SESSION['cityid']."'");

$rs_Reason = $rs->Select("Reason", "Id=$Id");
$r_Reason = $rs->getArrayLine($rs_Reason);

$rs_Reasons = $rs->SelectQuery("SELECT Id FROM Reason where CityId = '{$_SESSION['cityid']}' order by Progressive");
while ($r_Reasons = mysqli_fetch_assoc($rs_Reasons)){
    $a_Reasons[] = $r_Reasons['Id'];
}
$Key = array_search($Id, $a_Reasons);
$PreviousId = array_key_exists(($Key-1),$a_Reasons) ? $a_Reasons[$Key-1] : null;
$NextId = array_key_exists(($Key+1),$a_Reasons) ? $a_Reasons[$Key+1] : null;

$q_Violation = "SELECT * FROM ViolationType WHERE RuleTypeId=(SELECT RuleTypeId FROM ViolationType WHERE Id = {$r_Reason['ViolationTypeId']})";

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_reason" method="post" action="mgmt_reason_upd_exe.php">
    	<input type="hidden" name="Id" value="<?= $Id; ?>">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
    	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
    		<?php if(!empty($PreviousId)): ?>
            	<a href="<?= impostaParametriUrl(array('Id' => $PreviousId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Precedente" class="tooltip-r glyphicon glyphicon-arrow-left" style="font-size:3rem;color:#fff"></i></a>
        	<?php endif; ?>
        </div>
        <div class="BoxRowTitle col-sm-10" style="width:83.33%;">
           Modifica motivo contestazione
        </div>
    	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
    		<?php if(!empty($NextId)): ?>
            	<a href="<?= impostaParametriUrl(array('Id' => $NextId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Prossimo" class="tooltip-r glyphicon glyphicon-arrow-right" style="font-size:3rem;color:#fff"></i></a>
        	<?php endif; ?>
        </div>

        <div class="clean_row HSpace4"></div>
        
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Parametri motivo contestazione</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="BoxRowLabel col-sm-1">
            Progressivo
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input class="form-control text-center" type="text" disabled value="<?= $r_Reason['Progressive']; ?>">
        </div>
        <div class="BoxRowLabel col-sm-1">
            Matricola
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input value="<?= $r_Reason['Code']; ?>" id="Code" name="Code" type="text" class="form-control frm_field_numeric">
        </div>
        <div class="BoxRowLabel col-sm-2">
            Tipo violazione
        </div>
        <div class="BoxRowCaption col-sm-2">
            <?= CreateSelectQuery($q_Violation, "ViolationTypeId", "Id", "Title", $r_Reason['ViolationTypeId'], true) ?>
        </div>
        <?php if(mysqli_num_rows($rs_Detector)>0): ?>
            <div class="BoxRowLabel col-sm-1">
                Post. Fissa
            </div>
            <div class="BoxRowCaption col-sm-1">
                <?= CreateArraySelect(array("No","Si"), true, "Fixed", "Fixed", $r_Reason['Fixed'], false) ?>
            </div>
        <?php endif; ?>
        <div class="BoxRowLabel col-sm-1">
            Disabilitato
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input type="checkbox" name="Disabled" value="1" <?= ChkCheckButton($r_Reason['Disabled']); ?>>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
       		Descrizione (max 60 caratteri)
    	</div>
        <div class="col-sm-10 BoxRowCaption">
       		<input id="Description" name="Description" type="text" class="form-control frm_field_string txt-warning" value="<?= StringOutDB($r_Reason['Description']); ?>">
    	</div>
    	
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
       		Testo verbale nazionale
    	</div>
        <div class="col-sm-10 BoxRowCaption" style="height:6.4rem">
       		<textarea name="DescriptionIta" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;"><?= StringOutDB($r_Reason['DescriptionIta']); ?></textarea>
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
            	<textarea name="Title<?= $tag; ?>" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;"><?= StringOutDB($r_Reason['Title'.$tag]); ?></textarea>
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

    $('#f_reason').bootstrapValidator({
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
