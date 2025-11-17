<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC."/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');

echo $str_out;

?>

<div class="row-fluid">
	<form id="f_articletariff" action="tbl_customer_articletariffcsv_add_exe.php" method="post" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Copia tariffe nuovo anno esercizio
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:9rem;">
        	Carica file delle tariffe
        </div>
        <div class="col-sm-4 BoxRowLabel table_caption_error" style="height:9rem;">
        <strong>Prima di caricare il csv accertarsi che:</strong>
        	<ul style="list-style-position:inside;">
                <li>sia stato usato il ; come separatore</li>
                <li>sia stato usato il . come separatore delle migliaia</li>
                <li>la virgola sia usata come separatore decimale</li>
            </ul>
        </div>
        <div class="col-sm-6 BoxRowCaption" style="height:9rem;">
        	<input class="frm_field_required" name="InputCsv" id="InputCsv" type="file" style="margin-bottom:10px; margin-top:10px;display:inline-block;">
            <div><input type="radio" id="CopyChoice1" name="Action" value="1" style="vertical-align: text-top;" checked> Copia applicando le modifiche nel file</div>
            <div><input type="radio" id="CopyChoice2" name="Action" value="2" style="vertical-align: text-top;"> Copia senza applicare modifiche alla tariffa</div>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-6 BoxRowLabel">
        	Anno da copiare
        </div>
        <div class="col-sm-6 BoxRowCaption">
        	<?= CreateSelectQuery('SELECT DISTINCT Year FROM ArticleTariff ORDER BY Year DESC', 'Year', 'Year', 'Year', $_SESSION['year'], true); ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-6 BoxRowLabel">
        	Anno da inserire
        </div>
        <div class="col-sm-6 BoxRowCaption">
        	<select name="NewYear" id="NewYear" class="form-control">
        	<?php 
        	foreach (range(2000, 2050) as $number) {
        	    echo '<option'.(($_SESSION['year'] + 1) == $number ? ' selected' : '').' value="'.$number.'">' . $number . "</option>";
        	}
        	?>
        	</select>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-6 BoxRowLabel">
        	Enti
        </div>
        <div class="col-sm-6 BoxRowCaption">
            <select id="CityId" name="CityId" class="form-control">
            	<option value="All">TUTTI</option>
            	<option value="<?= ENTE_BASE; ?>">BASE</option>
            	<?php
            	$cities = $rs->SelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle;");
            	while($city = mysqli_fetch_array($cities)){
            	    echo '<option value="'.$city['CityId'].'">'.$city['CityTitle'].'</option>';
            	}
            	?>
            </select>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
	    <div class="table_label_H HSpace4" style="height:8rem;">
			<?= ChkButton($aUserButton, 'add','<button type="submit" id="insert" class="btn btn-success" style="margin-top:2rem;">Esegui</button>'); ?>
        </div>
        
	</form>
</div>

<script type="text/javascript">

	$(document).ready(function () {

	    $('#f_articletariff').bootstrapValidator({
	        live: 'disabled',
	        fields: {
	            frm_field_required: {
	                selector: '#f_articletariff .frm_field_required',
	                validators: {
	                    notEmpty: {
	                        message: 'Richiesto'
	                    }
	                }
	            },
	        }
	    });

	    $('input[type=radio][name=Action]').change(function() {
	    	if ($('#CopyChoice1').is(':checked'))
	    		$('#InputCsv').prop('disabled', false);
	    	else
	    		$('#InputCsv').prop('disabled', true);
	    });

	});
</script>

<?php
include(INC."/footer.php");

