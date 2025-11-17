<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" id="viewport">
    <meta name="robots" content="noindex">

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <title>SARIDA - Traffic Law</title>

    <link href="<?= LIB ?>/datepicker/css/bootstrap-datepicker.css" rel="stylesheet" media="screen">

    <link rel="stylesheet" href="<?= CSS ?>/magnifier.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= LIB ?>/filetree/css/jqueryFileTree.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= LIB ?>/upload/css/style.css" type="text/css" media="all" />

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

	<!-- Costante jquery 3 NB JQuery deve sempre essere importato prima di bootstrap.js (versione 4 o minore) sennò dà errore -->
	<?php if(defined('JQ3')):?>
		<script src="<?= JS ?>/jquery3/jquery-3.6.1.js" type="text/javascript"></script>
	<?php else:?>
    	<script src="<?= JS ?>/jquery-1.12.0.js" type="text/javascript"></script>
    <?php endif;?>
    
    <script src="<?= JS ?>/jquery-ui-1.12.1.js"></script>
    
    <!-- Costante bootstrap 5 -->
	<?php if(defined('BS5')):?>
    	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/bootstrap-5.0.2_css/bootstrap.css">
    	<script src="<?= JS ?>/bootstrap-5.0.2_js/bootstrap.min.js" type="text/javascript"></script>
	<?php else:?>
		<link rel="stylesheet" href="<?= CSS ?>/bootstrap.css" type="text/css" media="all" />
		<script src="<?= JS ?>/bootstrap.min.js" type="text/javascript"></script>
	<?php endif;?>
	
	<link rel="stylesheet" href="<?= CSS ?>/bootstrap-theme.css" type="text/css" media="all" />
    <script type="text/javascript" src="<?= LIB ?>/datepicker/js/bootstrap-datepicker.js" charset="UTF-8"></script>
    <script type="text/javascript" src="<?= LIB ?>/filetree/js/jqueryFileTree.js"></script>
    <script src="<?= LIB ?>/zoom/js/zoom.js"></script>
    <script src="<?= LIB ?>/taxcode/js/taxcode.js"></script>
    <script type="text/javascript" src="<?= LIB ?>/upload/js/jquery.knob.js"></script>
    <script type="text/javascript" src="<?= LIB ?>/validator/js/bootstrapValidator.js"></script>
    <script src="<?= JS ?>/Magnifier.js" type="text/javascript"></script>
    <script src="<?= JS ?>/Event.js" type="text/javascript"></script>

    <script src="https://cdn.ckeditor.com/4.11.1/full-all/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    
    <link rel="stylesheet" href="<?= CSS ?>/sarida.css" type="text/css" media="all" />
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    
    <script type="text/javascript">
    	const SARIDA = {
    		mostraCaricamento(testo = "Caricamento in corso..."){
    			$("#LoadingModal").modal("show");
    			$("#LoadingModal .modal-text").html(testo);
    		},
    		nascondiCaricamento(){
    			setTimeout(function(){
    				$("#LoadingModal").modal("hide");
    			}, 750);
    		}
    	}
    	
        //Bug1972 - Crea il campo password nei form in modo che resti nascosto ed allo stesso tempo eviti l'autocompletamento del browser
		// function passwordField(superId, name, shownId, hiddenId, value) {
        //     var superDiv = document.querySelector('#' + superId);
        //     var fake = document.createElement("input");
        //     var real = document.createElement("input");
    
    	// 	fake.type = "text";
        //     fake.className = "frm_field_string form-control fakePswField";
        //     real.name = name;
        //     real.id = hiddenId;
        //     real.type = "hidden";
        //     fake.value = value; //aggiungere n pallini per il numero di lettere del vero valore
        //     real.value = value;
        //     fake.addEventListener("input", function(event) { //fare in modo di mantenere il valore vecchio ed aggiungere le lettere nuove senza i pallini del fake
        //         real.value = event.target.value;
        //     });
    
        //     superDiv.appendChild(fake);
        //     superDiv.appendChild(real);
        // };
        function passwordField(superId, name, elemId, value, disabled) {
            var superDiv = document.querySelector('#' + superId);
            var elem = document.createElement("input");
    
            elem.type = "text";
            //elem.className = "frm_field_string form-control fakePswField";
            elem.className = "frm_field_string form-control";
            elem.id = elemId;
            elem.name = name;
            elem.value = value;
            elem.oncopy = e => e.preventDefault();
            elem.oncut = e => e.preventDefault();
            elem.setAttribute("autocomplete","new-password");
            elem.setAttribute("autofill","off");
            elem.style["-webkit-text-security"] = "disc";
            if(disabled)
                {
                    elem.setAttribute("disabled","true");
                }
    
            superDiv.appendChild(elem);
        };
    </script>
    
    <style>
        .labelcol{
            color: #636b6f
        }
        /* Bug1972 - Trasforma l'input di testo in una serie di dischi */
        /* .fakePswField {
        	-webkit-text-security: disc;
        	-mox-text-security: disc;
        	-moz-text-security: disc;
        } */
    </style>
<?php

require(INC."/initialization.php");

?>

</head>
