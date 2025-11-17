<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/menu_".$_SESSION['UserMenuType'].".php");

$filesFolder = "public/DA_IMPORTARE/CAN_CAD";

if (!is_dir(ARCHIVIO."/$filesFolder")) {
    mkdir(ARCHIVIO."/$filesFolder");
    chmod(ARCHIVIO."/$filesFolder", 0770);
}

echo $str_out;
?>
<div class="row-fluid">
    <div class="col-sm-12">
        <div class="col-sm-12 alert alert-info" style="padding:5px; display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                <ul>
                    <li>Note:
                    <ul style="list-style-position: inside;">
                        <li>Selezionare il documento desiderato nell'elenco a sinistra <strong>(la cartella in lettura è DA_IMPORTARE/CAN_CAD)</strong>.</li>
                        <li>Impostare i filtri desiderati per cercare l'atto a cui attribuire il documento e fare click sul bottone con la lente di ingrandimento.</li>
                        <li>In fondo viene restituito un elenco con i risultati di ricerca. Fare click sul pulsante <strong>"Assegna"</strong> nella riga corrispondente all'atto a cui si desidera allegare il documento.</li>
                    </ul>
                </li></ul>
            </div>
        </div>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
	<div class="col-sm-12">
		<div class="col-sm-3 table_caption_I">
            Elenco documenti
        </div>
    	<div class="col-sm-9 table_caption_I">
            Anteprima
        </div>
    	<div id="fileTree" class="col-sm-3 BoxRow BoxRowLabel" style="height:40rem;overflow:auto"></div>
    	<div class="col-sm-9 BoxRow" style="height:40rem;">
    		<div id="preview_doc" style="height:100%"></div>
            <div class="imgWrapper" id="preview_img" style="display:none;height:100%;width:100%;display:flex;align-items:center;justify-content:center;">
                <img id="preview" class="iZoom"/>
            </div>
    	</div>
	</div>
	
	<div class="clean_row HSpace4"></div>
	
	<div class="col-sm-12">
		<div class="col-sm-2 table_caption_I">
            Selezionato
        </div>
		<div class="col-sm-10 table_caption_I">
            Ricerca verbale
        </div>
	</div>
	
	<div class="clean_row HSpace4"></div>
	
	<div class="col-sm-12">
    	<div id="fileSelected" class="col-sm-2 BoxRowCaption">
    		<span></span>
    		<input type="hidden">
        </div>
    	<div class="col-sm-10">
    		<form id="searchFine">
	            <div class="col-sm-3" style="padding:0">
                    <div class="col-sm-5 BoxRowLabel">
                        Ente
                    </div>
                    <div class="col-sm-7 BoxRowCaption">
                        <?= CreateSelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle", "Search_CityId", "CityId", "CityTitle", $_SESSION['cityid'], true) ?>
                    </div>
                </div>
                <div class="col-sm-2" style="padding:0">
                    <div class="col-sm-6 BoxRowLabel">
                        Anno
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <select class="form-control" name="Search_ProtocolYear" id="Search_ProtocolYear"></select>
                    </div>
                </div>
                <div class="col-sm-3" style="padding:0">
                    <div class="col-sm-5 BoxRowLabel">
                        N. Raccomandata
                    </div>
                    <div class="col-sm-7 BoxRowCaption">
                        <input class="form-control frm_field_numeric" name="Search_LetterNumber" id="Search_LetterNumber" type="text">
                    </div>
                </div>
                <div class="col-sm-3" style="padding:0">
                    <div class="col-sm-4 BoxRowLabel">
                        Trasgressore
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <input class="form-control frm_field_string" name="Search_Trespasser" id="Search_Trespasser" type="text">
                    </div>
                </div>
                <div class="col-sm-1 BoxRowCaption text-right">
                
                    <button id="search" type="button" data-container="body" data-placement="left" title="Cerca vebale" class="btn btn-primary tooltip-r" style="margin:0;width:50%;height:100%;padding:0">
                        <i class="glyphicon glyphicon-search"></i>
                    </button>
                 </div> 
    		</form>
        </div>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
    <div id="resultsDiv" class="hidden col-sm-12">
        <div class="col-sm-12 table_caption_I">
            Risultati ricerca
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-1 table_label_H">
            Cronologico
        </div>
        <div class="col-sm-1 table_label_H">
            Anno
        </div>
        <div class="col-sm-5 table_label_H">
            Trasgressori
        </div>
        <div class="col-sm-4 table_label_H">
            N. Raccomandata
        </div>
        <div class="col-sm-1 table_label_H">
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div id="results" style="height:11.4rem;overflow: auto;">
        </div>
    </div>
</div>

<script>
	function loadFileTree(){
		var path = "<?= $filesFolder; ?>";
		
		$("#preview_img img").attr("src", "").hide();
		$("#preview_doc").html("").hide();
		$("#fileSelected span").text("");
		$("#fileSelected input").val("");
		
        $("#fileTree").fileTree({ root:path, script: 'jqueryFileTree.php', reload:true}, function(file) {
            var FileType = file.split('.').pop();
                
            if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc' || FileType.toLowerCase()=='html'){
                $("#preview_img").hide();
                $("#preview_doc").html('<iframe style="width:100%; height:100%; background:white;" src="'+file+'"></iframe>');
                $("#preview_doc").show();
                
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }
        }).on('filetreeinitiated', function(e, data){
        	$('#fileTree').find("li:not(.directory) a").first().click();
    	}).on('filetreeclicked', function(e, data){ 
    		$("#fileSelected span").text(data.value);
    		$("#fileSelected input").val(data.value);
    	});
	}
	
	function loadYears(cityId){
		var y = $.ajax({
			url: "ajax/year.php",
			type: "POST",
			data: {id:cityId},
			dataType: "text"
		});

		y.done(function(data){
			$('#Search_ProtocolYear').html(data);
		});
		y.fail(function(jqXHR, textStatus){
			alert("Errore: " + textStatus );
		});
	}
	
	function showResults(data){
		$('#resultsDiv').removeClass("hidden");
		$('#results').html("");
        $.each(data.Result, function( index, result ) {
            var tableRow = $('<div>', {class: "tableRow"});
            var info = "\nCron: "+formatValue(result.ProtocolId)+"\nAnno: "+formatValue(result.ProtocolYear)+"\nTrasgressori: "+formatValue(result.TrespasserName)+"\nN. Raccomandata: "+formatValue(result.LetterNumber);
    		var button = $('<button>', {"data-fineid": result.FineId, "data-info": info, class: "assignButton btn btn-success", text: "Assegna", css: {width: "100%", height: "100%", padding: 0}})
            tableRow.append(buildElement(1,result.ProtocolId));
            tableRow.append(buildElement(1,result.ProtocolYear));
            tableRow.append(buildElement(5,result.TrespasserName));
            tableRow.append(buildElement(4,result.LetterNumber));
            tableRow.append(buildElement(1,"",true).append(button));
            tableRow.append($('<div>', {class: "clean_row HSpace4"}));
            tableRow.appendTo('#results');
        });
        $('#results').scrollTop(0);
	}
	
	function buildElement(size, value, isButton){
		return $('<div>', {
            class: (isButton ? 'table_caption_warning' : 'table_caption_H')+' col-sm-'+size,
            text: formatValue(value)
        });
	}
	
	function formatValue(value){
		return value === null || value.trim() === "" ? "" : value
	}
	
	function executeAjax(inputData){
        return $.ajax({
            url: 'ajax/ajx_imp_cancad.php',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: inputData,
        });
	}
	
	function searchFines(){
    	$('#search i').removeClass('glyphicon glyphicon-search').addClass('fas fa-circle-notch fa-spin');
    	$('#search').prop('disabled', true);
    	$('.assignButton').prop('disabled', true);
		console.log("QueryString: "+$('#searchFine').serialize()+"&Action=search");
		$.when(executeAjax($('#searchFine').serialize()+"&Action=search").done(function (data) {
        	showResults(data);
		}).fail(function(data){
            console.log(data);
            alert("Errore: " + data.responseText);
		}).always(function(data){
            $('#search i').addClass('glyphicon glyphicon-search').removeClass('fas fa-circle-notch fa-spin');
        	$('#search').prop('disabled', false);
        	$('.assignButton').prop('disabled', false);
		})); 
	}
	
	$('document').ready(function(){
		loadFileTree();
		loadYears($('#Search_CityId').val());
		
		$('#Search_CityId').change(function(){
			loadYears($(this).val());
		});
        $('#search').click(searchFines); 
        
      	$("#results").on("mouseover", ".tableRow", function(){
      		$( this ).find( '.table_caption_H, .table_caption_warning' ).css("background-color", "#cfeaf7c7");
      	});
      	$("#results").on("mouseout", ".tableRow", function(){
      		$( this ).find( '.table_caption_H, .table_caption_warning' ).css("background-color", "");
      	});
        
        $('#results').on("click", ".assignButton", function () {
        	var fineid = $(this).data("fineid");
        	var info = $(this).data("info");
        	var documentname = $("#fileSelected input").val();
        	
        	if(documentname.trim() != ""){
        		if(confirm("Si sta per assegnare "+documentname+" al seguente atto:\n"+info+"\n\nContinuare?")){
	        		$('#search').prop('disabled', true);
            		$('.assignButton').prop('disabled', true);
            	
    	    		$.when(executeAjax({Action: "assign", FineId : fineid, DocumentName: documentname}).done(function (data) {
                    	if(data.Success){
                    		alert("Azione eseguita con successo.");
                    		loadFileTree();
                    	} else {
                    		alert("Errore: "+data.Message);
                    	}
        			}).fail(function(data){
                        console.log(data);
                        alert("Errore: " + data.responseText);
        			}).always(function(data){
        				searchFines();
        			})); 
        		}
        	} else alert("Si prega di selezionare un documento.");
        });
	});
</script>
<?php
require_once (INC . "/footer.php");
