<?php
include("../_path.php");
include(INC."parameter.php");
include(CLS."cls_db.php");
include(INC."function.php");


$id = CheckValue('id', 'n');

$rs= new CLS_DB();

$strOperationUser = "";

$operations_user = $rs->Select('vledger',"userid=$id","operationdate");
$OperationNumber = mysqli_num_rows($operations_user);

if($OperationNumber==0){
	$strOperationUser .= 'Nessuna registrazione presente';
}
else{
	while($operation_user = mysqli_fetch_array($operations_user)){
		$operation = $operation_user['cash'].' - '.$operation_user['causal'].' '.$operation_user['descr'];
		if(strlen($operation)>50) $operation = substr($operation,0,50).'...';
		$col1 = DateOutDB($operation_user['operationdate']);
		$col2 = $operation;
		$col3 = NumberDisplay($operation_user['amount']);
		$amountstyle = ($operation_user['operationtype']==0) ? "Amount_OUT" : "Amount_IN";



		$strOperationUser .= '<div style="position:relative;width:100%;margin:0 auto;">
			<div class="user_caption_H p_w15">'.$col1.'</div>
			<div class="user_caption_H p_w63" >'.$col2.'</div>
			<div class="user_caption_H p_w20 right '.$amountstyle.'" >'.$col3.'</div>
			<div class="clean_row HSpace4"></div>
		</div>';


	}
}
echo $strOperationUser;


