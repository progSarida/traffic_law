<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");


$aField = $_POST['aField'];
$table = $_POST['table'];
$qtype = $_POST['qtype'];

	if($qtype=='ins')
	{
		
		$rs->Insert($table,$aField);


	    echo "1 Record Added!";
	}
?>