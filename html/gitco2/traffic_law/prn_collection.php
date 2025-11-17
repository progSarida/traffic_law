<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$A_text = 'Proventi complessivi delle sanzioni derivanti dall\'accertamento di tutte le violazioni al codice della strada (ad eccezione delle sole violazioni di cui all\'art. 142, comma 12-bis)';
$B_text = 'Proventi complessivi delle sanzioni derivanti dall\'accertamento delle violazioni dei limiti massimi di velocità di cui all\'art. 142, comma 12-bis, comminate dai propri organi di polizia stradale sulle strade di competenza e in concessione';
$C_text = '50% del totale dei proventi delle sanzioni derivanti dall\'accertamento delle violazioni dei limiti massimi di velocita di cui all\'art. 142, comma 12-bis, comminate dai propri organi di polizia stradale sulle strade non di proprietà dell\'ente locale';
$D_text = '50% del totale dei proventi delle sanzioni derivanti dall\'accertamento delle violazioni dei limiti massimi di velocita di cui all\'art. 142, comma 12-bis, comminate su strade di proprietà dell\'ente locale da parte di organi di polizia stradale dipendenti da altri enti.';
$Bottom1_text = 'TOTALE PROVENTI VIOLAZIONI LIMITI MASSIMI Dl VELOCITA EX ART. 142, COMMA 12-BIS ';
$Bottom2_text = 'TOTALE PROVENTI VIOLAZIONI AL CODICE DELLA STRADA';

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_Print" action="prn_collection_exe.php" method="post" autocomplete="off">
		<input type="hidden" id="Action" name="Action" value="">
        <div class="col-sm-11">
            <div class="col-sm-2 BoxRowLabel">
                Ente
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?= CreateSelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle", "Search_CityId", "CityId", "CityTitle", $Search_CityId ?: $_SESSION['cityid'], true, null, 'frm_field_required') ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<select class="form-control frm_field_required" name="Search_Year" id="Search_Year"></select>
            </div>
            <div class="col-sm-5 BoxRowLabel">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Considera:
            </div>
            <div class="col-sm-10 BoxRowCaption">
                <input style="top:0;" type="radio" name="Search_Type" value="0" <?= $Search_Type == 0 ? 'checked' : '' ?>><span style="position:relative;top:-0.3rem"> Solo la sanzione amministrativa riscossa</span>&nbsp;&nbsp;&nbsp;
                <input style="top:0;" type="radio" name="Search_Type" value="1" <?= $Search_Type == 1 ? 'checked' : '' ?>><span style="position:relative;top:-0.3rem"> Tutte le componenti riscosse: spese di ricerca, postali/notifica</span> 
            </div>    
        </div>

        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
<?= ChkButton($aUserButton, 'prn','<button type="button" data-toggle="tooltip" data-placement="top" title="Calcola" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:33.3%;height:100%;"><i class="glyphicon glyphicon-search" style="font-size:1.5rem;"></i></button>'); ?>
<?= ChkButton($aUserButton, 'prn','<button type="submit" data-action="Pdf" data-toggle="tooltip" data-placement="top" title="Stampa" class="tooltip-r btn btn-warning" id="printPdf" name="printPdf" style="margin-top:0;width:33.3%;height:100%;"><i class="fa fa-file-pdf-o" style="font-size:1.5rem;"></i></button>'); ?>
<?= ChkButton($aUserButton, 'prn','<button type="submit" data-action="Excel" data-toggle="tooltip" data-placement="top" title="Stampa" class="tooltip-r btn btn-success" id="printExcel" name="printExcel" style="margin-top:0;width:33.3%;height:100%;"><i class="fa fa-file-excel-o" style="font-size:1.5rem;"></i></button>'); ?>
        </div>
	</form>
</div>

<div class="clean_row HSpace4"></div>

<div class="row-fluid">
	<div class="col-sm-12">
		<div class="table_label_H col-sm-12">Anteprima</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel text-center col-sm-1" style="border-right: 1px solid #E7E7E7;"></div>
		<div class="BoxRowLabel text-center col-sm-8" style="border-right: 1px solid #E7E7E7;">DESCRIZIONE</div>
		<div class="BoxRowLabel text-center col-sm-3">IMPORTO</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel text-center col-sm-1" style="border-right: 1px solid #E7E7E7;height: 4.4rem;line-height: 4.2rem;font-size: 2rem;">A</div>
		<div class="BoxRowCaption text-center col-sm-8" style="border-right: 1px solid #E7E7E7;height: 4.4rem;"><?= $A_text ?></div>
		<div class="table_caption_I col-sm-3" style="height: 4.4rem;line-height: 4.2rem;text-align:center"><span id="SumNot142">________________</span></div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel text-center col-sm-1" style="border-right: 1px solid #E7E7E7;height: 4.4rem;line-height: 4.2rem;font-size: 2rem;">B</div>
		<div class="BoxRowCaption text-center col-sm-8" style="border-right: 1px solid #E7E7E7;height: 4.4rem;"><?= $B_text ?></div>
		<div class="table_caption_I col-sm-3" style="height: 4.4rem;line-height: 4.2rem;text-align:center"><span id="Sum142">________________</span></div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel text-center col-sm-1" style="border-right: 1px solid #E7E7E7;height: 4.4rem;line-height: 4.2rem;font-size: 2rem;">C</div>
		<div class="BoxRowCaption text-center col-sm-8" style="border-right: 1px solid #E7E7E7;height: 4.4rem;"><?= $C_text ?></div>
		<div class="table_caption_button col-sm-3" style="height: 4.4rem;line-height: 4.2rem;text-align:center"><span id="SumC">Inserire importo</span></div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel text-center col-sm-1" style="border-right: 1px solid #E7E7E7;height: 4.4rem;line-height: 4.2rem;font-size: 2rem;">D</div>
		<div class="BoxRowCaption text-center col-sm-8" style="border-right: 1px solid #E7E7E7;height: 4.4rem;"><?= $D_text ?></div>
		<div class="table_caption_button col-sm-3" style="height: 4.4rem;line-height: 4.2rem;text-align:center"><span id="SumD">Inserire importo</span></div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowCaption text-center col-sm-1" style="border-right: 1px solid #E7E7E7;height: 4.4rem;line-height: 4.2rem;font-size: 2rem;"></div>
		<div class="BoxRowCaption text-center col-sm-8" style="border-right: 1px solid #E7E7E7;line-height: 4.2rem;height: 4.4rem;"><?= $Bottom1_text ?></div>
		<div class="table_caption_button col-sm-3" style="height: 4.4rem;line-height: 4.2rem;text-align:center">A + C + D</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowCaption text-center col-sm-1" style="border-right: 1px solid #E7E7E7;height: 4.4rem;line-height: 4.2rem;font-size: 2rem;"></div>
		<div class="BoxRowCaption text-center col-sm-8" style="border-right: 1px solid #E7E7E7;line-height: 4.2rem;height: 4.4rem;"><?= $Bottom2_text ?></div>
		<div class="table_caption_button col-sm-3" style="height: 4.4rem;line-height: 4.2rem;text-align:center">A + B + C + D</div>
	</div>
</div>			

<script type="text/javascript">
var searchIcon = $('<i>', {class: "glyphicon glyphicon-search", css:{"font-size" : "1.5rem"}});
var spinIcon = $('<i>', {class: "fas fa-circle-notch fa-spin", css:{"font-size" : "1.5rem"}});
var clickedButton;

function calculatePayments (city,year,allFee) {
	$('#search, #printPdf, #printExcel').prop('disabled', true);
	$('#search').html(spinIcon)
	
	if (city != "" && year != "" && allFee!=""){
        $.ajax({
            url: 'ajax/ajx_get_collectionPayments.php',
            type: 'POST',
            dataType: 'json',
            data: {Year:year, City:city, AllFee:allFee},
            success: function (result) {
            	$('#SumNot142').html(result.SumNot142 + " €");
            	$('#Sum142').html(result.Sum142 + " €");
            	$('#SumC').html(result.SumC);
        	    $('#SumD').html("Inserire importo");
            },
            error: function (result) {
                console.log(result);
                alert("error: " + result.responseText);
            },
            complete: function (){
            	$('#search, #printPdf, #printExcel').prop('disabled', false);
            	$('#search').html(searchIcon);
            }
        });
	} else {
    	$('#SumNot142').html("________________");
    	$('#Sum142').html("________________");
    	$('#SumC').html("Inserire importo");
    	$('#SumD').html("Inserire importo");
    	
    	$('#search, #printPdf, #printExcel').prop('disabled', false);
    	$('#search').html(searchIcon);
	}
}

function loadYears(cityId, select = ''){
	var y = $.ajax({
		url: "ajax/year.php",
		type: "POST",
		data: {id:cityId},
		dataType: "text"
	});

	y.done(function(data){
		$('#Search_Year').html(data);
		if(select != '') $('#Search_Year').val(select);
	});
	y.fail(function(jqXHR, textStatus){
		alert("Errore: " + textStatus );
	});
}

$(document).ready(function () {
	var selectYear = "<?= $Search_Year ?: ''  ?>";
	loadYears($('#Search_CityId').val(), selectYear);
	
	$('#Search_CityId').change(function(){
		loadYears($(this).val());
	});

	$('#f_Print').bootstrapValidator({
        live: 'disabled',
        fields: {
            frm_field_required: {
                selector: '.frm_field_required',
                validators: {
                    notEmpty: {
                        message: 'Richiesto'
                    }
                }
            }
        }
    }).on('success.form.bv', function(event){
    	$('#search, #printPdf, #printExcel').prop('disabled', true);
    	$('#Action').val(clickedButton.data('action'));
    	clickedButton.html(spinIcon);
    });

	setTimeout(() => {
		var City = $('#Search_CityId').val();
		var Year = $('#Search_Year').val();
		var AllFee = $("input[name='Search_Type']:checked").val();
		if (City != "" && Year != ""){
			calculatePayments(City,Year,AllFee);
		}
	}, 500);
	
    $('#printExcel, #printPdf, #search').click(function () {
    	var id = $(this).attr('id');
    	clickedButton = $(this);
    	
    	if(id == "search"){
			var City = $('#Search_CityId').val();
			var Year = $('#Search_Year').val();
			var AllFee = $("input[name='Search_Type']:checked").val();

			calculatePayments(City,Year, AllFee);
    	}
    });
});
</script>

<?php
include(INC."/footer.php");