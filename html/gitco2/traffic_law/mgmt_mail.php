<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$a_Mail = array(
    array('field'=>'ReadStatus','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
);



$rs->Update('Mail',$a_Mail,"UserId=".$_SESSION['userid']);




$strOrder = "SendDate DESC, Id DESC";

$str_out .='
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-2">Oggetto</div>
				<div class="table_label_H col-sm-8">Contenuto</div>
        		<div class="table_add_button col-sm-1 right">
        			'.ChkButton($aUserButton, 'add','<a href="#"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>').'      				
				</div>
				<div class="clean_row HSpace4"></div>';



$rs_Mail = $rs->Select('Mail',"UserId=".$_SESSION['userid'],$strOrder);


$RowNumber = mysqli_num_rows($rs_Mail);

if ($RowNumber == 0) {
	$str_out.= '<div class="table_caption_H col-sm-12">Nessuna mail presente</div>';
} else {
	while ($r_Mail = mysqli_fetch_array($rs_Mail)) {



		$str_out.='
        	<div class="table_caption_H col-sm-1" style="height:10rem;">' . DateOutDB($r_Mail['SendDate']) .'</div>
        	<div class="table_caption_H col-sm-2" style="height:10rem;">' . $r_Mail['Object'] .'</div>
        	<div class="table_caption_H col-sm-8"style="height:10rem;overflow:auto;">' . $r_Mail['Content'] .'</div>
	
			<div class="table_caption_button col-sm-1" style="height:10rem;">
                <a href="mgmt_mail_del_exe.php'.$str_GET_Parameter.'&Id='.$r_Mail['Id'].'"><span class="glyphicon glyphicon-remove" style="position:absolute;left:25px;top:5px;"></span></a>
				&nbsp;
	&nbsp;		
							</div>
			<div class="clean_row HSpace4"></div>';
	}
}

$str_out.= '<div>
	</div>';


echo $str_out;

include(INC."/footer.php");
