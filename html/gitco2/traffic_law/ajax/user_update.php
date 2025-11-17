<?php
include("../_path.php");
include(INC."parameter.php");
include(CLS."cls_db.php");
include(INC."function.php");

$rs= new CLS_DB();


$id = $_POST['id'];

$user = $rs->Select('ledger_user',"id=".$id);



$row = mysqli_fetch_array($user);

$strUser='
<div id="FormUser">
	<form name="fupd" id="fupd" action="mgmt_user_upd_exe.php" method="post">
	<input type="hidden" name="id" value="'.$id.'" />
	<div class="BoxRow">
		<div class="BoxRowTitle" id="BoxRowTitle">
			Modifica anagrafica
		</div>
	</div>
    <div class="clean_row HSpace4"></div>';



if($row['type']=='D'){
	$strUser .='
<div class="BoxRow">
	<div class="BoxRowLabel">
		Ragione sociale
	</div>
	<div class="BoxRowCaption">
		<input type="text" name="companyname" id="companyname"  value="'.$row['companyname'].'" />
	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		P.IVA
	</div>
	<div class="BoxRowCaption">
		<input type="text" name="vatnumber" id="vatnumber"  value="'.$row['vatnumber'].'" />
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
		<input type="text" name="surname" id="surname"  value="'.$row['surname'].'" />
	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		Nome
	</div>
	<div class="BoxRowCaption">
		<input type="text" name="name" id="name"  value="'.$row['name'].'" />
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
		<input type="text" name="taxcode" id="taxcode"  value="'.$row['taxcode'].'" />
	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		IBAN
	</div>
	<div class="BoxRowCaption">
		<input type="text" name="iban" id="iban"  value="'.$row['iban'].'" />
	</div>
</div>
<div class="clean_row HSpace4"></div>

<div class="BoxRow">
	<div class="BoxRowLabel">
		mail
	</div>
	<div class="BoxRowCaption">
		<input type="text" name="mail" id="mail"  value="'.$row['mail'].'" />
	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowLabel">
		PEC
	</div>
	<div class="BoxRowCaption">
		<input type="text" name="PEC" id="PEC"  value="'.$row['PEC'].'" />
	</div>
</div>
<div class="clean_row HSpace4"></div>
<div class="BoxRow">
	<div class="BoxRowButton" id="BoxRowButton">
		<input type="submit" value="Modifica" class="btn btn-primary" />
	</div>
</div>
</form>
</div>
';


echo $strUser;