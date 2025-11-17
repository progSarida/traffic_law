<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(PGFN."/fn_mgmt_controller.php");
require_once(INC . "/header.php");
require_once(INC . "/initialization.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//Se c'è stato un errore, compila i campi con i valori precedentemente usati dall'utente, eccetto l'immagine
$a_postData = $_SESSION['postdata']['Controller'][$Id];
unset($_SESSION['postdata']['Controller']);

$Id = CheckValue('Id', 'n');
$a_Controllers = array();

$str_Where = mgmtControllerWhere();
$str_Order = mgmtControllerOrderBy();

$rs_Controllers = $rs->Select("Controller", $str_Where, $str_Order);
while ($r_Controller = mysqli_fetch_assoc($rs_Controllers)){
    $a_Controllers[] = $r_Controller['Id'];
}

$Key = array_search($Id, $a_Controllers);
$NextId = array_key_exists(($Key+1),$a_Controllers) ? $a_Controllers[$Key+1] : null;
$PreviousId = array_key_exists(($Key-1),$a_Controllers) ? $a_Controllers[$Key-1] : null;

$rs_Controller = $rs->Select("Controller", "Id=$Id");
$r_Controller = $rs->getArrayLine($rs_Controller);

$SignImageName = explode("/",$r_Controller['Sign']);
$SignImageName = $SignImageName[count($SignImageName)-1];

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_controller" enctype="multipart/form-data" method="post" action="mgmt_controller_upd_exe.php">
    	<input type="hidden" name="Id" value="<?= $Id; ?>">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">

    	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
    		<?php if(!empty($PreviousId)): ?>
    			<a href="<?= impostaParametriUrl(array('Id' => $PreviousId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Precedente" class="tooltip-r glyphicon glyphicon-arrow-left" style="font-size:3rem;color:#fff"></i></a>
    		<?php endif; ?>
        </div>
        <div class="col-sm-10 BoxRowTitle" style="width:83.33%;">
            Modifica accertatore
        </div>
    	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
    		<?php if(!empty($NextId)): ?>
    			<a href="<?= impostaParametriUrl(array('Id' => $NextId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Prossimo" class="tooltip-r glyphicon glyphicon-arrow-right" style="font-size:3rem;color:#fff"></i></a>
    		<?php endif; ?>
        </div>
        
        <div class="clean_row HSpace4"></div>

		<div class="col-sm-2 BoxRowLabel">
            Nominativo
        </div>                    
        <div class="col-sm-4 BoxRowCaption">
            <input type="text" name="Name" class="form-control frm_field_string frm_field_required" value="<?= $a_postData['Name'] ?? StringOutDB($r_Controller['Name']) ?>" required>
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Matricola
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="text" name="Code" class="form-control frm_field_numeric" value="<?= $a_postData['Code'] ?? $r_Controller['Code'] ?>">
        </div>  
        <div class="col-sm-1 BoxRowLabel">
            Tipo
        </div>
        <div class="col-sm-1 BoxRowCaption">
        	<?= CreateArraySelect(array(1 => 'Effettivo', 2 => 'Ausiliario'), true, 'ControllerTypeId', 'ControllerTypeId', $a_postData['ControllerTypeId'] ?? $r_Controller['ControllerTypeId'], true) ?>
        </div>
        <div class="col-sm-1 BoxRowLabel">
             Disabilitato
        </div>                    
        <div class="col-sm-1 BoxRowCaption">
            <input type="checkbox" name="Disabled" value="1" type="checkbox" <?= ChkCheckButton($a_postData['Disabled'] ?? $r_Controller['Disabled']); ?>>
        </div>
                          
	    <div class="clean_row HSpace4"></div>
	    
        <div class="col-sm-2 BoxRowLabel">
             File immagine firma
        </div>
        <?php if($r_Controller['Sign'] == ''): ?>
        	<div class="col-sm-6">
        		<input type="file" name="fileToUpload" id="fileToUpload" class="form-control BoxRowCaption" accept="image/*">
    		</div>
        <?php else: ?>
        	<div class="col-sm-3 BoxRowCaption">
                 <?= $SignImageName; ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">Cambia </div>
            <div class="col-sm-2 BoxRowCaption">
                 <input type="file" name="fileToUpload" id="fileToUpload" class="BoxRowCaption form-control" accept="image/*">
            </div>
        <?php endif; ?>

		<div class="col-sm-1 BoxRowLabel">
             Incarico dal
        </div>                    
        <div class="col-sm-1 BoxRowCaption">
             <input type="text" name="FromDate" value="<?= $a_postData['FromDate'] ?? DateOutDB($r_Controller['FromDate']); ?>" class="form-control frm_field_date">
        </div>                    
        <div class="col-sm-1 BoxRowLabel">
             al
        </div>                    
        <div class="col-sm-1 BoxRowCaption">
             <input type="text" name="ToDate" value="<?= $a_postData['ToDate'] ?? DateOutDB($r_Controller['ToDate']); ?>" class="form-control frm_field_date">
        </div>  
               
    	<div class="clean_row HSpace4"></div>                    

        <div class="col-sm-2 BoxRowLabel">
             Verbalizzante/Firmatario
        </div>                    
        <div class="col-sm-1 BoxRowCaption" >
            <input id="ChiefController" name="ChiefController" value="1" type="checkbox" <?= ChkCheckButton($a_postData['ChiefController'] ?? $r_Controller['ChiefController']); ?>>
        </div>
        <div class="col-sm-2 BoxRowLabel">
             Firma digitale certificata
        </div>                    
        <div class="col-sm-1 BoxRowCaption" >
            <input id="DigitalSign" name="DigitalSign" value="1" type="checkbox" <?= ChkCheckButton($a_postData['DigitalSign'] ?? $r_Controller['DigitalSign']); ?>>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Abilita a firma verbale digitale
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<input name="FineDigitalSign" value="1" type="checkbox" <?= ChkCheckButton($a_postData['FineDigitalSign'] ?? $r_Controller['FineDigitalSign']); ?>>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Abilita a firma relata di notifica digitale
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<input name="NotificationDigitalSign" value="1" type="checkbox" <?= ChkCheckButton($a_postData['NotificationDigitalSign'] ?? $r_Controller['NotificationDigitalSign']); ?>>
        </div>

    	<div class="clean_row HSpace4"></div>

        <div class="col-sm-2 BoxRowLabel">
             Qualifica
        </div>                    
        <div class="col-sm-4 BoxRowCaption">
             <?= CreateSelect("QualificationType","CityId='".$_SESSION['cityid']."'","Description","Qualification","Description","Description",$a_postData['Qualification'] ?? $r_Controller['Qualification'],false,null, 
                 isset($a_postData['ControllerTypeId']) ? ($a_postData['ControllerTypeId'] == 1 ? 'frm_field_required' : '') : ($r_Controller['ControllerTypeId'] == 1 ? 'frm_field_required' : '')) ?>
        </div>
        <div class="col-sm-6 BoxRowHTitle">
        </div>    

    	<div class="clean_row HSpace4"></div>
    	
        <div class="col-sm-2 BoxRowLabel" style="height: 10rem">
             Poteri
        </div>                    
        <div class="col-sm-10 BoxRowCaption" style="height: 10rem">
            <textarea name="Note" class="form-control frm_field_string" style="height: 9.8rem;margin:0;"><?= StringOutDB($a_postData['Note'] ?? $r_Controller['Note']); ?></textarea>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="table_label_H HSpace4" style="height:8rem;">
            <button class="btn btn-success" type="submit" style="margin-top:2rem"><i class="fa fa-save fa-fw"></i> Salva</button>
            <button class="btn btn-default" id="back" type="button" style="margin-top:2rem">Indietro</button>
        </div>
    </form>
</div>

<script type="text/javascript">

$(document).ready(function(){

	const validationOptions = 
		{
            selector: '.frm_field_required',
            validators: {
                notEmpty: {
                    message: 'Richiesto'
                }
            }
        };

    $('#ControllerTypeId').on('change', function () {
    	$('#f_controller').data('bootstrapValidator').resetForm();
        if ($(this).val() != 2){
            $("#Qualification").addClass("frm_field_required");
            $("#f_controller").bootstrapValidator('addField', $('#Qualification'), validationOptions);
        } else {
            $("#Qualification").removeClass("frm_field_required");
            $("#f_controller").bootstrapValidator('removeField', $('#Qualification'));
        }
        $('#f_controller').data('bootstrapValidator').resetForm();
    });

    $('#back').click(function () {
        window.location = "<?= $str_BackPage; ?>";
        return false;
    });
    
    $('#f_controller').bootstrapValidator({
        live: 'disabled',
        fields: {
            frm_field_required: validationOptions
        }
    }).on('success.form.bv', function(e){
    });
});

</script>
<?php
require_once(INC . "/footer.php");