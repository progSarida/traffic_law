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
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="<?= FONT ?>/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?= FONT ?>/foundation-icons/foundation-icons.css" />

	<!-- Costante jquery 3  NB JQuery deve sempre essere importato prima di bootstrap.js (versione 4 o minore) sennò dà errore -->
	<?php if(defined('JQ3')):?>
		<script src="<?= JS ?>/jquery3/jquery-3.6.1.js" type="text/javascript"></script>
	<?php else:?>
    	<script src="<?= JS ?>/jquery-1.12.0.js" type="text/javascript"></script>
    <?php endif;?>

	<!-- Costante bootstrap 5 -->
	<?php if(defined('BS5')):?>
    	<link rel="stylesheet" href="<?= CSS ?>/bootstrap-5.0.2_css/bootstrap.css" type="text/css" media="all" />
        <script src="<?= JS ?>/bootstrap-5.0.2_js/bootstrap.js" type="text/javascript"></script>
    <?php else:?>
        <link rel="stylesheet" href="<?= CSS ?>/bootstrap.css" type="text/css" media="all" />
        <script src="<?= JS ?>/bootstrap.js" type="text/javascript"></script>
	<?php endif;?>
	
    <script src="<?= JS ?>/jquery-ui-1.12.1.js"></script>
    
    <script type="text/javascript" src="<?= LIB ?>/datepicker/js/bootstrap-datepicker.js" charset="UTF-8"></script>

    <script type="text/javascript" src="<?= LIB ?>/validator/js/bootstrapValidator.js"></script>

    <link rel="stylesheet" href="<?= LIB ?>/filetree/css/jqueryFileTree.css" type="text/css" media="all" />
    <script type="text/javascript" src="<?= LIB ?>/filetree/js/jqueryFileTree.js"></script>


    <script src="<?= LIB ?>/zoom/js/zoom.js"></script>

    <link rel="stylesheet" href="<?= LIB ?>/upload/css/style.css" type="text/css" media="all" />
    <script type="text/javascript" src="<?= LIB ?>/upload/js/jquery.knob.js"></script>


    <link rel="stylesheet" href="<?= CSS ?>/sarida.css" type="text/css" media="all" />

</head>
