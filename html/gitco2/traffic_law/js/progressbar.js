function progressBar_elaborate(file, ajaxURL, params){
	if (params.constructor === Object){
		params['ProgressFile'] = file;
		var type = 'POST';
	} else {
		params += '&ProgressFile=' + file;
		var type = 'GET';
	}
	return $.ajax({
        url: ajaxURL,
        type: type,
        dataType: 'json',
        cache: false,
        data: params,
        success: function (result) {
        	console.log(result);
        },
        error: function (result) {
        	alert("error: " + result.responseText);
            console.log(result);
        }
	});
}

function progressBar_fetch(file, button){
    $.getJSON('public/TMP/' + file, function(json) {
        var percent = Math.round((json.Contati / json.Totali) * 100);
        //console.log(json);
        $('#progressbar').width(percent + '%');
        $('#progressbar').html(percent + '%');
        $('#' + button.id).trigger('progressGet', json);
    })
}

//Funzione principale
function progressBar_start(ajaxURL, button, params){ 
	var currentDate = new Date();
	var tick = button.getAttribute("progress-tick") || 1000;
	
	var fileDate = "" +
		currentDate.getFullYear() +
		(currentDate.getMonth()+1) +
		currentDate.getDate() + "_" +
		currentDate.getHours() +
		currentDate.getMinutes() +
		currentDate.getSeconds();
	
	var file = button.id + '_' + fileDate + '.json';

	button.disabled=true;
	button.innerHTML='<i class="fas fa-circle-notch fa-spin" style="font-size:3rem;">';
	
	var interval = setInterval(function() {
		progressBar_fetch(file, button);
	}, tick);
	
	$.when(progressBar_elaborate(file, ajaxURL, params)).done(function(data){
		progressBar_fetch(file, button);
		clearInterval(interval);
		button.innerHTML='<i class="fa fa-check" style="font-size:3rem;">';
		$('#' + button.id).trigger('progressDone', data);
	}).fail(function(data){
		progressBar_fetch(file, button);
		clearInterval(interval);
		button.innerHTML='<i class="fa fa-times" style="font-size:3rem;">';
		$('#' + button.id).trigger('progressFail', data);
	});

}
