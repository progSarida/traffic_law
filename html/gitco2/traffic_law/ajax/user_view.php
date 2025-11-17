<?php
include("../_path.php");
include(INC."parameter.php");
include(CLS."cls_db.php");
include(INC."function.php");

$rs= new CLS_DB();
$strUser='
<div id="FormLedger">
	<div class="BoxRow">
		<div class="BoxRowTitle" id="BoxRowTitle">
			Dati anagrafica
		</div>
	</div>
    <div class="clean_row HSpace4"></div>';

$id = $_POST['id'];

$user = $rs->Select('ledger_user',"id=".$id);



$row = mysqli_fetch_array($user);

if($row['type']=='D'){
	$strUser='
<div class="BoxRow">
	<div class="BoxRowLabel">
		Ragione sociale
	</div>
	<div class="BoxRowCaption">
		'.$row['companyname'].'

	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		P.IVA
	</div>
	<div class="BoxRowCaption">
		'.$row['vatnumber'].'
	</div>
</div>
<div class="clean_row HSpace4"></div>';


}else{
	$strUser='
<div class="BoxRow">
	<div class="BoxRowLabel">
		Cognome
	</div>
	<div class="BoxRowCaption">
		'.$row['surname'].'

	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		Nome
	</div>
	<div class="BoxRowCaption">
		'.$row['name'].'
	</div>
</div>
<div class="clean_row HSpace4"></div>';


}

$strUser .= '
<div class="BoxRow">
	<div class="BoxRowLabel">
		C.F.
	</div>
	<div class="BoxRowCaption">
		'.$row['taxcode'].'

	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		IBAN
	</div>
	<div class="BoxRowCaption">
		'.$row['iban'].'
	</div>
</div>
<div class="clean_row HSpace4"></div>

<div class="BoxRow">
	<div class="BoxRowLabel">
		mail
	</div>
	<div class="BoxRowCaption">
		'.$row['mail'].'
	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		PEC
	</div>
	<div class="BoxRowCaption">
		'.$row['PEC'].'
	</div>
</div>

</div>
';


echo $strUser;