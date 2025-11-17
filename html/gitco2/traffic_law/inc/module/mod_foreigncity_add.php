<?php
?>
<div id="mod_foreigncity_add" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        	<form id="f_foreigncity_add" method="post" action="" accept-charset="UTF-8" enctype='multipart/form-data'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 id="fcity_ModalTitle" class="modal-title">Aggiungi città estera</h4>
                </div>
                <div class="modal-body">
                    <div id="fcity_groupNation" class="form-group">
                    	<label>Nazione</label>
                    	<?php echo CreateSelect(MAIN_DB . ".Country", "Id NOT IN ('Z000','Z00Z')", "Title", "fcity_CountryId", "Id", "Title", "", true); ?>
                    </div>
                    <div id="fcity_groupTitle" class="form-group">
                    	<label>Titolo</label>
                    	<input type="text" id="fcity_Title" name="fcity_Title" class="form-control text-uppercase" required>
                    	<small style="display:none;" id="fcity_errorTitle" class="form-text text-danger">Città già presente per questa nazione.</small>
                    </div>
                    <div id="fcity_CAP" class="form-group">
                    	<label>CAP</label>
                    	<input type="text" id="fcity_Zip" name="fcity_Zip" class="form-control">
                    </div>
                    <div id="groupDescription" class="form-group">
                    	<label>Land</label>
    	                <select class="form-control" name="fcity_LandId" id="fcity_LandId" disabled required>
                            <option></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                	<button id="fcity_SaveButton" type="submit" class="btn btn-success ">Inserisci</button>
                	<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                </div>
            </form>
        </div>

    </div>
</div>

<script type="text/javascript">

$(document).ready(function () {
	$('#fcity_CountryId, #fcity_Title').change(function() {
        var CountryId = $('#fcity_CountryId').val();
        var Title = $('#fcity_Title').val();
            
        $.ajax({
            url: 'ajax/ajx_check_foreignCity.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId, Title:Title},
            success: function (data) {
                //console.log(data);
                if (data.Result == "NO") {
                    $("#fcity_SaveButton").prop("disabled", true);
                    $("#fcity_errorTitle").show();
                }
                if (data.Result == "OK") {
                    $("#fcity_SaveButton").prop("disabled", false);
                    $("#fcity_errorTitle").hide();
                }
            },
            error: function (result) {
                console.log(result);
                alert("error: " + result.responseText);
            }
        });
	});

	$('#fcity_CountryId').change(function() {
		if ($(this).val() == "Z102" || $(this).val() == "Z112")
			$('#fcity_LandId').prop("disabled", false);
		else
			$('#fcity_LandId').prop("disabled", true);

        var CountryId = $(this).val();
            
        $.ajax({
            url: 'ajax/ajx_get_land.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId},
            success: function (data) {
            	$('#fcity_LandId').html(data.Options);
            },
            error: function (result) {
                console.log(result);
                alert("error: " + result.responseText);
            }
        });
	});

	$('#f_foreigncity_add').submit(function(e) {
		e.preventDefault();
		var CountryId = $('#fcity_CountryId').val();
		var Title = $('#fcity_Title').val();
		var Zip = $('#fcity_Zip').val();
		var LandId = $('#fcity_LandId').val();
            
        $.ajax({
            url: 'ajax/ajx_add_foreigncity_exe.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId, Title:Title, Zip:Zip, LandId:LandId},
            success: function (data) {
                if (data.Answer == "NO") {
                    alert(data.Message);
                }
                if (data.Answer == "OK") {
                	$('#mod_foreigncity_add').modal('hide');
	    		    //Evento a cui agganciarsi
    		    	$("#f_foreigncity_add").trigger("submitted", data );
                }
            },
            error: function (result) {
                console.log(result);
                alert("error: " + result.responseText);
            },
        });
	});

});

</script>