<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(INC . "/header.php");
require_once(INC . "/initialization.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//Se c'è stato un errore, compila i campi con i valori precedentemente usati dall'utente, eccetto l'immagine
$a_postData = $_SESSION['postdata']['Controller']['add'];
unset($_SESSION['postdata']['Controller']);

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_controller" enctype="multipart/form-data" method="post" action="mgmt_controller_add_exe.php">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">

        <div class="col-sm-12 BoxRowTitle">
            Inserisci Accertatore
        </div>
        
        <div class="clean_row HSpace4"></div>

		<div class="col-sm-2 BoxRowLabel">
            Nominativo
        </div>                    
        <div class="col-sm-4 BoxRowCaption">
            <input type="text" name="Name" class="form-control frm_field_string frm_field_required" value="<?= $a_postData['Name'] ?? ''; ?>">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Matricola
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input type="text" name="Code" class="form-control frm_field_numeric" value="<?= $a_postData['Code'] ?? ''; ?>">
        </div>  
        <div class="col-sm-1 BoxRowLabel">
            Tipo
        </div>
        <div class="col-sm-1 BoxRowCaption">
        	<?= CreateArraySelect(array(1 => 'Effettivo', 2 => 'Ausiliario'), true, 'ControllerTypeId', 'ControllerTypeId', $a_postData['ControllerTypeId'] ?? 1, true) ?>
        </div>
        <div class="col-sm-2 BoxRowHTitle">
        </div>
                          
	    <div class="clean_row HSpace4"></div>
	    
        <div class="col-sm-2 BoxRowLabel">
             File immagine firma
        </div>
    	<div class="col-sm-6">
    		<input type="file" name="fileToUpload" id="fileToUpload" class="form-control BoxRowCaption" accept="image/*">
		</div>

		<div class="col-sm-1 BoxRowLabel">
             Incarico dal
        </div>                    
        <div class="col-sm-1 BoxRowCaption">
             <input type="text" name="FromDate" value="<?= $a_postData['FromDate'] ?? ''; ?>" class="form-control frm_field_date">
        </div>                    
        <div class="col-sm-1 BoxRowLabel">
             al
        </div>                    
        <div class="col-sm-1 BoxRowCaption">
             <input type="text" name="ToDate" value="<?= $a_postData['ToDate'] ?? ''; ?>" class="form-control frm_field_date">
        </div>  
               
    	<div class="clean_row HSpace4"></div>                    
        
        <div class="col-sm-2 BoxRowLabel">
             Verbalizzante/Firmatario
        </div>                    
        <div class="col-sm-1 BoxRowCaption" >
            <input id="ChiefController" name="ChiefController" value="1" type="checkbox" <?= ($a_postData['ChiefController'] ?? 0) == 1 ? 'checked' : ''; ?>>
        </div>  
        <div class="col-sm-2 BoxRowLabel">
             Firma digitale certificata
        </div>                    
        <div class="col-sm-1 BoxRowCaption" >
            <input id="DigitalSign" name="DigitalSign" value="1" type="checkbox" <?= ($a_postData['DigitalSign'] ?? 0) == 1 ? 'checked' : ''; ?>>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Abilita a firma verbale digitale
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<input name="FineDigitalSign" value="1" type="checkbox" <?= ($a_postData['FineDigitalSign'] ?? 0) == 1 ? 'checked' : ''; ?>>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Abilita a firma relata di notifica digitale
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<input name="NotificationDigitalSign" value="1" type="checkbox" <?= ($a_postData['NotificationDigitalSign'] ?? 0) == 1 ? 'checked' : ''; ?>>
        </div>     

    	<div class="clean_row HSpace4"></div>

        <div class="col-sm-2 BoxRowLabel">
             Qualifica
        </div>                    
        <div class="col-sm-4 BoxRowCaption">
             <?= CreateSelect("QualificationType","CityId='".$_SESSION['cityid']."'","Description","Qualification","Description","Description",$a_postData['Qualification'] ?? null,false,null,
                 isset($a_postData['ControllerTypeId']) ? ($a_postData['ControllerTypeId'] == 1 ? 'frm_field_required' : '') : 'frm_field_required') ?>
        </div>
        <div class="col-sm-6 BoxRowHTitle">
        </div>    

    	<div class="clean_row HSpace4"></div>
    	
        <div class="col-sm-2 BoxRowLabel" style="height: 10rem">
             Poteri
        </div>                    
        <div class="col-sm-10 BoxRowCaption" style="height: 10rem">
            <textarea name="Note" class="form-control frm_field_string" style="height: 9.8rem;margin:0;"><?= $a_postData['Note'] ?? ''; ?></textarea>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="table_label_H HSpace4" style="height:8rem;">
            <button class="btn btn-success" type="submit" style="margin-top:2rem"><i class="fa fa-plus fa-fw"></i> Inserisci</button>
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