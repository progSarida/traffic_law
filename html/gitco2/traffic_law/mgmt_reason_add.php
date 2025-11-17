<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC."/initialization.php");
require(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

$a_Lan = unserialize(LANGUAGE_KEYS);
$rs_Detector = $rs->selectQuery("SELECT Id FROM Detector WHERE CityId='".$_SESSION['cityid']."'");

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_reason" method="post" action="mgmt_reason_add_exe.php">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div class="BoxRowTitle col-sm-12">
           Inserisci motivo contestazione
        </div>

        <div class="clean_row HSpace4"></div>
        
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Parametri motivo contestazione</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="BoxRowLabel col-sm-1">
            Matricola
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input id="Code" name="Code" type="text" class="form-control frm_field_numeric">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Tipo regolamento
        </div>
        <div class="BoxRowCaption col-sm-1">
 			<?= $_SESSION['ruletypetitle'] ?>
 			<input type="hidden" id="RuleTypeId" name="RuleTypeId" value="<?= $_SESSION['ruletypeid'] ?>"/>
        </div>
        <div class="BoxRowLabel col-sm-2">
            Tipo violazione
        </div>
        <div class="BoxRowCaption col-sm-2">
            <select name="ViolationTypeId" id="ViolationTypeId" class="form-control"></select>
        </div>
        <?php if(mysqli_num_rows($rs_Detector)>0): ?>
            <div class="BoxRowLabel col-sm-1">
                Post. Fissa
            </div>
            <div class="BoxRowCaption col-sm-1">
                <select name="Fixed" class="form-control">
                	<option></option>
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </div>
        <?php endif; ?>
        <div class="BoxRowLabel col-sm-1">
            Disabilitato
        </div>
        <div class="BoxRowCaption col-sm-1">
            <input type="checkbox" name="Disabled" value="1">
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
       		Descrizione (max 60 caratteri)
    	</div>
        <div class="col-sm-10 BoxRowCaption">
       		<input id="Description" name="Description" type="text" class="form-control frm_field_string txt-warning" >
    	</div>
    	
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
       		Testo verbale nazionale
    	</div>
        <div class="col-sm-10 BoxRowCaption" style="height:6.4rem">
       		<textarea name="DescriptionIta" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;"></textarea>
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
            	<textarea name="Title<?= $tag; ?>" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;"></textarea>
            </div>
            
            <?php if($LangN % 2 == 0): ?>
            	<div class="clean_row HSpace4"></div>
        	<?php endif; ?>
    	<?php endforeach; ?>
        
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12 text-center" style="line-height:6rem;">
                    <button class="btn btn-success" type="submit"><i class="fa fa-plus fa-fw"></i> Inserisci</button>
                    <button class="btn btn-default" id="back" type="button">Indietro</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function requestViolationTypes(ruleTypeId) {
    $.ajax({
        url: 'ajax/ajx_get_violationsByRuleTypeId.php',
        type: 'POST',
        dataType: 'json',
        data: {RuleTypeId: ruleTypeId},
        success: function(data){
            var options = "";
        	$.each(data.Result, function(i, value) {
        	    options = options + '<option value='+value.Id+'>'+value.Title+'</option>';
        	});
        	$('#ViolationTypeId').html(options);
        },
        error: function(data){
            console.log(data);
            alert("error: " + data.responseText);
        },
    });
}

$('document').ready(function(){
	requestViolationTypes($('#RuleTypeId').val());
	
    $(document).on('change','#RuleTypeId',function (e) {
        var ruleTypeId = $('#RuleTypeId').val();
        requestViolationTypes(ruleTypeId);
    });

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
