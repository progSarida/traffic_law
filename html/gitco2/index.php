<?php
include("_path.php");
?>
<!DOCTYPE html>
<html><head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<!-- Standard Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

	<!-- Site Properties -->
	<title>Login SARIDA applications</title>


	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/reset.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/site.css">

	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/container.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/grid.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/header.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/image.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/menu.css">

	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/divider.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/segment.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/form.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/input.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/button.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/list.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/message.css">
	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/icon.css">
	
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome/css/font-awesome.css">
	
	<?php define("JQ3", true);?>
	<?php define("BS5", true);?>
	
	
	<!-- Costante jquery 3 NB JQuery deve sempre essere importato prima di bootstrap.js (versione 4 o minore) sennò dà errore -->
	<?php if(defined('JQ3')):?>
		<script src="<?= JS ?>/jquery3/jquery-3.6.1.js" type="text/javascript"></script>
	<?php else:?>
		<script src="<?= JS ?>/jquery-1.12.0.js" type="text/javascript"></script>
	<?php endif;?>
	
	<!-- Costante bootstrap 5 -->
	<?php if(defined('BS5')):?>
    	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/bootstrap-5.0.2_css/bootstrap.css">
    	<script src="<?= JS ?>/bootstrap-5.0.2_js/bootstrap.js" type="text/javascript"></script>
	<?php else:?>
    	<link rel="stylesheet" type="text/css" href="<?= CSS ?>/bootstrap.css">
    	<script src="<?= JS ?>/bootstrap.js" type="text/javascript"></script>
	<?php endif;?>

	<script src="lib/validator/js/bootstrapValidator.js"></script>

	<style type="text/css">
		body {
			background-color: #E7E7E7;
		}
		body > .grid {
			height: 100%;
		}
		.image {
			margin-top: -100px;
		}
		.column {
			max-width: 450px;
		}
	</style>

</head>
<body>
<script>
/*
$(document).ready(function(){
if($(window).width() < 700){
    window.location = "";
    }
});
*/
</script>
<!--http://77.81.236.68/gitco2/ Ambiente di test su Aruba dismesso a giugno 2020-->
<!--<div style="position:absolute;top:1rem;right:1rem"><a href="http://77.81.236.68/gitco2/">TEST</a></div>-->

<!--https://gitcocoll.ovunque-si.it/gitco2/ Ambiente di collaudo da luglio 2020-->
<div style="position:absolute;top:1rem;right:1rem"><a href="https://gitcocoll.ovunque-si.it/gitco2/">TEST</a></div>
<div class="ui middle aligned center aligned grid">
	<div class="column">
		<h2 class="ui teal image header">
			<img src="<?= IMG ?>/logo.png" class="image">
			<div class="content">
				Log-in to your account
			</div>
		</h2>
		<form id="f_login" method="post" action="authentication_exe.php">
			<div class="ui stacked segment">
				<div class="field error">
					<div class="ui left icon input">
						<i class="fa fa-user-circle-o" style="position:absolute;top:6px;left:14px;font-size:20px;color:#35BDB2"></i>
						<input name="user" id="username" placeholder="Username" class="form-control" type="text">
					</div>
				</div>
				<div class="field error">
					<div class="ui left icon input">
						<i class="fa fa-asterisk" style="position:absolute;top:6px;left:14px;font-size:20px;color:#35BDB2"></i>
						<input name="pass" id="password" placeholder="Password" class="form-control" type="password">
					</div>
				</div>
				<input type="submit" value="Login" />
			</div>
		</form>
	</div>
</div>
<div style="position:absolute;bottom:1rem;right:1rem"><a href="http://18.216.151.237/">SVILUPPO</a></div>


<script>
	$(document).ready(function() {
			$('#f_login').bootstrapValidator({
				live: 'disabled',
				fields: {
					Username: {
						validators: {
							notEmpty: {message: 'Obbligatorio'},
						}
					},

					Password: {
						validators: {
							notEmpty: {message: 'Obbligatorio'},
						}
					}
				}
			});
		})
	;
</script>
</body></html>
