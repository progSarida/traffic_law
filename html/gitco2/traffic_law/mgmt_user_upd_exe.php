<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$str_GETLink = "?1";
$d_PasswordDate = date('Y-m-d');
$str_PasswordText = CheckValue('new_P','s');
$str_Password = addslashes($str_PasswordText);


	$aUpdate = array(
        array('field' => 'PasswordDate', 'selector' => 'value', 'type' => 'date', 'value' => $d_PasswordDate, 'settype' => 'date'),
        array('field' => 'Password', 'selector' => 'value', 'type' => 'str', 'value' => md5($str_Password)),
	    array('field' => 'TextFieldNoComment', 'selector' => 'value', 'type' => 'str', 'value' => $str_PasswordText),


        );
	$rs->Update(MAIN_DB.'.User', $aUpdate, "Id=" . $_SESSION['userid']);

$_SESSION['PasswordDay'] = 89;
$_SESSION['Message'] = "Password cambiata con successo";

header("location: index.php");