<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(INC . "/header.php");
require_once(INC. "/initialization.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Id = CheckValue('Id', 's');

$rs_Controller = $rs->SelectQuery("SELECT * FROM Controller WHERE Id= $Id");
$r_Controller = $rs->getArrayLine($rs_Controller);

$a_ControllerType = array(1 => 'Effettivo', 2 => 'Ausiliario');

echo $str_out;
?>

<form id="f_controller" method="post" action="mgmt_controller_del_exe.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $Id; ?>">
	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
    <div class="row-fluid">
    	<div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Elimina Accertatore
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
            Nominativo
        </div>                    
        <div class="col-sm-4 BoxRowCaption">
        	<?= StringOutDB($r_Controller['Name']); ?>
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Matricola
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<?= $r_Controller['Code']; ?>
        </div>  
        <div class="col-sm-1 BoxRowLabel">
            Tipo
        </div>
        <div class="col-sm-1 BoxRowCaption">
        	<?= $a_ControllerType[$r_Controller['ControllerTypeId']] ?>
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Disabilitato
        </div>
        <div class="col-sm-1 BoxRowCaption">
        	<?= YesNoOutDB($r_Controller['Disabled']); ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
			File immagine firma
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <?= $r_Controller['Sign'] ?>
        </div>
        <div class="col-sm-3 BoxRowLabel"></div>
        <div class="col-sm-1 BoxRowLabel">
            Incarico dal
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<?= $r_Controller['FromDate']; ?>
        </div> 
        <div class="col-sm-1 BoxRowLabel">
            al
        </div>
        <div class="col-sm-1 BoxRowCaption">
			<?= $r_Controller['ToDate']; ?>
        </div> 
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
             Verbalizzante/Firmatario
        </div>                    
        <div class="col-sm-1 BoxRowCaption" >
        	<?= YesNoOutDB($r_Controller['ChiefController']); ?>
        </div>
        <div class="col-sm-2 BoxRowLabel">
             Firma digitale certificata
        </div>                    
        <div class="col-sm-1 BoxRowCaption" >
        	<?= YesNoOutDB($r_Controller['DigitalSign']); ?>
        </div>
        <div class="col-md-2 BoxRowLabel">
            Abilita a firma verbale digitale
        </div>
        <div class="col-md-1 BoxRowCaption">
			<?= YesNoOutDB($r_Controller['FineDigitalSign']); ?>
        </div>
        <div class="col-md-2 BoxRowLabel">
            Abilita a firma relata di notifica digitale
        </div>
        <div class="col-md-1 BoxRowCaption">
			<?= YesNoOutDB($r_Controller['NotificationDigitalSign']); ?>
        </div>       
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
             Qualifica
        </div>                    
        <div class="col-sm-4 BoxRowCaption">
             <?= $r_Controller['Qualification']; ?>
        </div>
        <div class="col-sm-6 BoxRowHTitle">
        </div> 
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height: 10rem">
             Poteri
        </div>                    
        <div class="col-sm-10 BoxRowCaption" style="height: 10rem">
            <?= StringOutDB($r_Controller['Note']) ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="table_label_H HSpace4" style="height:8rem;">
            <button type="submit" class="btn btn-danger" style="margin-top:2rem"><i class="fa fa-trash"></i> Elimina</button>
            <button id="back" type="button" class="btn btn-default" style="margin-top:2rem">Indietro</button>
        </div>
    </div>
</form>

<script type="text/javascript">

$(document).ready(function(){

	$('#f_controller').on('submit', function(e){
		if(confirm('Si stà per eliminare l\'accertatore in modo definitivo. Continuare?')){
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
require_once(INC . "/footer.php");
