<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
require (CLS."/cls_message.php");
include (INC . "/function.php");
include (INC . "/header.php");
require (INC . "/initialization.php");
require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$a_Cities = $a_Countries = array();

$PageTitle = CheckValue('PageTitle', 's');
$Filter = CheckValue('Filter', 'n');

$str_Where = "T.Genre IN('F','M') AND COALESCE(T.TaxCode, '') != '' AND (((T.BornDate IS NULL OR COALESCE(T.BornPlace, '') = '' OR C.Id IS null) and T.BornCountryId='Z000') OR COALESCE(T.BornCountryId, '') = '')";
$str_Where_Date = "";

if ($Search_FromSendDate != ""){
    $str_Where_Date .= " AND T.DataSourceDate>='" . DateInDB($Search_FromSendDate) . "'";
}
if ($Search_ToSendDate != ""){
    $str_Where_Date .= " AND T.DataSourceDate<='" . DateInDB($Search_ToSendDate) . "'";
}
if ($Search_Discrepancy > 0) {
    if($str_Where_Date != ""){
        $str_Where_Date = ' AND ('.substr($str_Where_Date, 5).' OR T.DataSourceDate IS NULL)';
    }
} else {
    $str_Where_Date .= ' AND T.DataSourceDate IS NOT NULL';
}
if ($Search_City != "") {
    $str_Where .= " AND T.CustomerId='".$Search_City."'";
}

if ($Filter == 1){
    $rs_Trespasser = $rs->SelectQuery("
        SELECT T.*,C.Id AS CorrespondingCityId,UPPER(CO.Title) AS BornCountryTitle FROM Trespasser T 
        LEFT JOIN sarida.City C ON C.Title = T.BornPlace 
        LEFT JOIN Country CO ON CO.Id = T.BornCountryId 
        WHERE ".$str_Where.$str_Where_Date." LIMIT 3000");
    
    $RowNumber = mysqli_num_rows($rs_Trespasser);
    
    $rs_Cities = $rs->Select(MAIN_DB.'.City');
    while($r_Cities = $rs->getArrayLine($rs_Cities)){
        $a_Cities[$r_Cities['Id']] = strtoupper($r_Cities['Title']);
    }
    $json_Cities = json_encode($a_Cities);
    
    $rs_Countries = $rs->Select('Country');
    while($r_Countries = $rs->getArrayLine($rs_Countries)){
        $a_Countries[$r_Countries['Id']] = strtoupper($r_Countries['Title']);
    }
    $json_Countries = json_encode($a_Countries);
}

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_search" action="correggiDatiNascita.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?=$PageTitle;?>">
		<input type="hidden" name="Filter" value="1">

        <div class="col-sm-11" style="height:4.5rem; border-right:1px solid #E7E7E7;">
        	<div class="col-sm-1 BoxRowLabel">
        		Ente:
        	</div>
        	<div class="col-sm-2 BoxRowCaption">
        		<?= CreateSelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle;", "Search_City", "CityId", "CityTitle", $Search_City, true); ?>
        	</div>
        	<div class="col-sm-9 BoxRowLabel"></div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="col-sm-1 BoxRowLabel">
        		Da data ins.
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" value="<?= $Search_FromSendDate; ?>" id="Search_FromSendDate" name="Search_FromSendDate" class="form-control frm_field_date">
        	</div>
        	<div class="col-sm-1 BoxRowLabel">
        		A data ins.
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" value="<?= $Search_ToSendDate; ?>" id="Search_ToSendDate" name="Search_ToSendDate" class="form-control frm_field_date">
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		Includi date ins. nulle
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="checkbox" value="1" name="Search_Discrepancy" <?= ChkCheckButton($Search_Discrepancy); ?>>
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		Escludi non sanabili
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input disabled type="checkbox" value="1" name="Search_Type">
        	</div>
        	<div class="col-sm-2 BoxRowLabel"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="font-size:3rem;padding:0;margin:0;width:100%;height:100%">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div>
    </form>
    
    <div class="clean_row HSpace4"></div>
    
    <div id="f_fixtrespasser">
    	<div class="table_label_H col-sm-1">Riga | Id</div>
    	<div class="table_label_H col-sm-2">Trasgressore</div>
    	<div class="table_label_H col-sm-1" style="width:4.33%">Genere</div>
		<div class="table_label_H col-sm-1">Data nascita</div>
    	<div class="table_label_H col-sm-2">Codice Fiscale</div>
    	<div class="table_label_H col-sm-1">Data ins.</div>
    	<div class="table_label_H col-sm-1" style="width:12.33%">Nazione nascita</div>
    	<div class="table_label_H col-sm-2">Città nascita</div>
    	<div class="table_label_H col-sm-1">Errori</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <?php if ($Filter != 1):?>
            <div class="table_caption_H col-sm-12 text-center">
            	Inserire criteri di ricerca
            </div>
        <?php else: ?>
    		<?php if ($RowNumber > 0): ?>
    			<?php $n_Row = 1; ?>
    			<div class="alert alert-info col-sm-12 text-center" style="margin:0;">
    				<button id="Process" class="btn btn-info" type="button" style="width:18rem;height:5rem;" action="decode">Decodifica codici fiscali</button>
    			</div>
    			<?php while($r_Trespasser = mysqli_fetch_assoc($rs_Trespasser)):?>
        			<div Id="Trespasser_<?= $r_Trespasser['Id']; ?>" class="tableRow" data-id="<?= $r_Trespasser['Id']; ?>" data-taxcode="<?= $r_Trespasser['TaxCode']; ?>">
            			<div class="col-sm-1 table_caption_H">
            				<?= $n_Row++; ?> | <?= $r_Trespasser['Id']; ?>
        				</div>
            			<div class="table_caption_H col-sm-2">
            				<?= $r_Trespasser['Name'] . ' ' . $r_Trespasser['Surname']; ?>
        				</div>
            			<div class="table_caption_H col-sm-1 genre" style="width:4.33%">
            				<?= $r_Trespasser['Genre']; ?>
        				</div>
            			<div class="table_caption_H col-sm-1 borndate"<?= empty($r_Trespasser['BornDate']) ? ' style="color:red" data-err' : ''; ?>>
            				<?= DateOutDB($r_Trespasser['BornDate']) ?? '[VUOTO]'; ?>
        				</div>
            			<div class="table_caption_H col-sm-2">
            				<?= $r_Trespasser['TaxCode']; ?>
        				</div>
            			<div class="table_caption_H col-sm-1">
            				<?= DateOutDB($r_Trespasser['DataSourceDate']) ?? '[VUOTO]'; ?>
        				</div>
            			<div class="table_caption_H col-sm-1 borncountry"<?= empty($r_Trespasser['BornCountryId']) ? ' style="color:red;width:12.33%" data-err' : ' style="width:12.33%"'; ?>>
            				<?= StringOutDB(empty($r_Trespasser['BornCountryId']) ? '[VUOTO]' : $r_Trespasser['BornCountryTitle']); ?>
            				<input type="hidden" value="<?= $r_Trespasser['BornCountryId']; ?>">
        				</div>
            			<div class="table_caption_H col-sm-2 bornplace"<?= (empty($r_Trespasser['BornPlace']) || empty($r_Trespasser['CorrespondingCityId'])) ? ' style="color:red" data-err' : ''; ?>>
    						<?= StringOutDB(empty($r_Trespasser['BornPlace']) ? '[VUOTO]' : $r_Trespasser['BornPlace']); ?>
        				</div>
        				<div class="table_caption_H col-sm-1 status text-center">
        				</div>
    				</div>
				
    				<div class="clean_row HSpace4"></div>
    			<?php endwhile; ?>
			<?php else: ?>
                <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente
                </div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<script src="<?= LIB ?>/codicefiscalejs/dist/codice.fiscale.js"></script>

<script>
function decodeTaxCodes(citiesArray, countriesArray){
	var data = [];
	
	$('[id^=Trespasser_]').each(function(i){
		var errors = '';
		var warnings = '';
		var row = $(this);
		var cf = row.data('taxcode');
		var trespasserId = row.data('id');
		var discrepancy = [];	
		var borndate = borncountry = bornplace = genre = null;	

		if(CodiceFiscale.check(cf)){
			var cfData = CodiceFiscale.computeInverse(cf);
			$(this).children().each(function(i){
				var elem = $(this);
				if(elem.hasClass('borndate')){ //Campo data nascita
					borndate = String((cfData.day < 10 ? '0'+cfData.day : cfData.day))+'/'+String((cfData.month < 10 ? '0'+cfData.month : cfData.month))+'/'+String(cfData.year);
					if(elem[0].hasAttribute('data-err')){
						elem.text(borndate).css("color", "green");
					} else if(elem.text().trim() != borndate){
						elem.css("color", "red");
						borndate = null;
						discrepancy.push('borndate');
					}
				} else if (elem.hasClass('borncountry')){ //Campo stato nascita
					borncountry = cfData.birthplaceProvincia == 'EE' ? String(cfData.birthplaceId) : 'Z000';
					savedborncountry = elem.find('input').val().trim();
					if(elem[0].hasAttribute('data-err')){
						if(borncountry in countriesArray){
							elem.text(countriesArray[borncountry]).css("color", "green");
						} else errors += '- Codice catastale nazione non registrato: '+borncountry+'<br>';
					} else if(savedborncountry != borncountry){
						elem.css("color", "red");
						discrepancy.push('borncountry');
						warnings += '- Discrepanza tra nazione salvata '+countriesArray[savedborncountry]+' e nazione decodificata '+countriesArray[borncountry]+'<br>';
						borncountry = null;
					}
				} else if (elem.hasClass('bornplace')){ //Campo città nascita
					if(elem[0].hasAttribute('data-err')){
						if (cfData.birthplaceProvincia != 'EE'){
							if (cfData.birthplaceId in citiesArray){
								bornplace = String(citiesArray[cfData.birthplaceId])
								elem.text(bornplace).css("color", "green");
							} else errors += '- Codice catastale città non registrato: '+cfData.birthplaceId+'<br>';
						} else {
							elem.css("color", "orange");
							discrepancy.push('bornplace');
							warnings += '- Trattasi di nazionalità estera, la città non può essere decodificata<br>';
						}
					} 
				} else if (elem.hasClass('genre')){ //Campo genere
					genre = String(cfData.gender);
					if(elem.text().trim() != genre){
						elem.css("color", "red");
						discrepancy.push('genre');
						warnings += '- Discrepanza tra genere salvato e genere decodificato<br>';
					}
				}
			});
		} else errors += '- Codice fiscale non valido';

		if(!errors){
			data.push({id: trespasserId, borndate: borndate, borncountry: borncountry, bornplace: bornplace, discrepancy:discrepancy});
			if(warnings){
				row.find('.status').append(
					'<a target="_blank" href="mgmt_trespasser_upd.php<?= $str_GET_Parameter ?>&Id='+trespasserId+'"><i class="tooltip-r text-warning fas fa-exclamation-circle fa-fw" style="margin-top: 0.3rem;font-size: 1.4rem;" data-html="true" data-container="body" data-toggle="tooltip" data-placement="left" title="'+warnings+'"></i></a>');
			}
		} else {
			row.children().addClass('alert-danger');
			row.find('.status').append(
				'<a target="_blank" href="mgmt_trespasser_upd.php<?= $str_GET_Parameter ?>&Id='+trespasserId+'"><i class="tooltip-r text-danger fas fa-exclamation-circle fa-fw" style="margin-top: 0.3rem;font-size: 1.4rem;" data-html="true" data-container="body" data-toggle="tooltip" data-placement="left" title="'+errors+'"></i></a>');
		}
	});

	return JSON.stringify(data);
}

function saveData(json){
	return $.ajax({
        url: 'ajax/ajx_correggiDatiNascita.php',
        type: 'POST',
        dataType: 'json',
        data: {'Data': json},
        ContentType: "application/json; charset=UTF-8",
        success: function (data) {
        	console.log(data);
        },
        error: function (data) {
            console.log(data);
            alert("error: " + data.responseText);
        }
    });
}

$(document).ready(function () {
	var citiesArray = <?= $json_Cities ?? '[]'; ?>;
	var countriesArray = <?= $json_Countries ?? '[]'; ?>;
	var jsonData = '';

    $('#f_search').on('submit', function(e){
        try {
    		var fromDate = $.datepicker.parseDate('dd/mm/yy', $('#Search_FromSendDate').val());
    		var toDate = $.datepicker.parseDate('dd/mm/yy', $('#Search_ToSendDate').val());
        } catch (error) {
        	e.preventDefault();
            alert('Una o più date di inserimento specificate non sono valide');
        }

        if((fromDate && toDate) && (fromDate > toDate)){
        	e.preventDefault();
            alert('"Da data inserimento" non deve superare "A data inserimento"');
        }
    });
	
	$('#Process').on('click', function(){
		var button = $(this);

		if(button.attr('action') == 'decode'){
			button.prop('disabled', true).html('<i class="fa fa-2x fa-circle-notch fa-spin"></i>');
			
			setTimeout(function(){ 
				$.when(decodeTaxCodes(citiesArray, countriesArray)).done(function (data) {
					jsonData = data;
					button.prop('disabled', false).html('Applica correzioni').toggleClass('btn-info btn-success').attr('action', 'save');
				});
			}, 1000);
		} else if(button.attr('action') == 'save') {
			button.prop('disabled', true).html('<i class="fa fa-2x fa-circle-notch fa-spin"></i>');
			
			$.when(saveData(jsonData)).done(function (data) {
	        	$.each(data.Result, function(i, value) {
	        		$('#Trespasser_'+value.id).children().addClass('alert-'+value.status);
	        	});
				button.html('<i class="fa fa-2x fa-check"></i>');

				if(data.ReportFile)
					window.open(data.ReportFile);

				setTimeout(function(){ 
					alert('Correzioni applicate');
				}, 500);
			}).fail(function(data){
				button.html('<i class="fa fa-2x fa-times"></i>').toggleClass('btn-success btn-danger');
			});
		}
	});
	
});
</script>

<?php
include (INC . "/footer.php");

