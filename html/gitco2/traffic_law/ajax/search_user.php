<?php
include("../_path.php");
include(INC."parameter.php");
include(CLS."cls_db.php");
include(INC."function.php");

$rs= new CLS_DB();
$strUser = "";
$keyword = '%'.$_POST['keyword'].'%';

$users = $rs->Select('ledger_user',"surname LIKE '$keyword' OR companyname LIKE '$keyword'","surname, companyname");
$UserNumber = mysqli_num_rows($users);


if($UserNumber==0){
	$strUser = '<li>Nessun nominativo trovato</li>';
}
else{
	while($user = mysqli_fetch_array($users)){

		$strUser .= "<li onclick=\"set_item('".str_replace("'", "\'", $user['companyname']." ".$user['surname']." ".$user['name'])."',".$user['id'].");\">".$user['companyname']." ".$user['surname']." ".$user['name']."</li>";
	}
}

echo $strUser;
