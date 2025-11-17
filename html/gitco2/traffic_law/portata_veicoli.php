<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");

ini_set('max_execution_time', 3000);


$P = "frm_create_fine.php";

$s_TypePlate= CheckValue('TypePlate','s');

include(INC."/header.php");

include(INC."/menu.php");

$str_CurrentPage = curPageName();

$aVehicleTypeId = array("","fa fa-car","fa fa-motorcycle","fa fa-desktop","fa fa-truck","fa fa-bus","fa fa-rocket","fa fa-desktop","fa fa-bus","fa fa-bicycle", "fa fa-desktop", "fa fa-desktop","fa fa-desktop");

$FormPage = $str_CurrentPage;
$str_CurrentPage .="?1";
$LanguageId = 1;
$CurrentYear = $_SESSION['year'];

$str_Where = "CityId='".$_SESSION['cityid']."' AND VehicleMass>3.5";

$strOrder = "FineTime ASC";


$table_rows = $rs->Select('V_FineArticle_VehicleMass',$str_Where, $strOrder);
$RowNumber = mysqli_num_rows($table_rows);



$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">'.$RowNumber.' ID</div>
				<div class="table_label_H col-sm-1">Codice</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Massa</div>
				<div class="table_label_H col-sm-1">Fee</div>
				<div class="table_label_H col-sm-1">MaxFee</div>
				<div class="table_label_H col-sm-1">FeeArticle</div>
				<div class="table_label_H col-sm-1">MaxFeeArticle</div>
				<div class="table_label_H col-sm-1">NewFee</div>
				<div class="table_label_H col-sm-1">NewMaxFee</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				<div class="clean_row HSpace4"></div>
			';

while ($table_row = mysqli_fetch_array($table_rows)) {

    if($table_row['FineTime']<"07:00:00" || $table_row['FineTime']>"22:00:00"){
        $fee = round($table_row['FeeArticle']*2+$table_row['FeeArticle']*2/3,2);
        $maxfee = round($table_row['MaxFeeArticle']*2+$table_row['MaxFeeArticle']*2/3,2);
    }
    else{
        $fee = round($table_row['FeeArticle']*2,2);
        $maxfee = round($table_row['MaxFeeArticle']*2,2);
    }

    if($fee!=$table_row['Fee'] && $maxfee!=$table_row['MaxFee']){
        $aUpdate = array(
            array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$fee,'settype'=>'flt'),
            array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$maxfee,'settype'=>'flt'),
        );
        $rs->Update('FineArticle',$aUpdate, 'FineId='.$table_row['Id']);
    }

    $str_out .= '
			<div class="table_caption_H col-sm-1">' . $table_row['Id'] . '</div>
			<div class="table_caption_H col-sm-1">' . $table_row['Code'] . '</div>
			<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) . '</div>
			<div class="table_caption_H col-sm-1" >' . $table_row['VehiclePlate'] . '</div>
			<div class="table_caption_H col-sm-1">' . $table_row['Fee'] . '</div>
			<div class="table_caption_H col-sm-1">' . $table_row['MaxFee'] . '</div>
			<div class="table_caption_H col-sm-1">' . $table_row['FeeArticle'] . '</div>
			<div class="table_caption_H col-sm-1">' . $table_row['MaxFeeArticle'] . '</div>
			<div class="table_caption_H col-sm-1">' . $fee . '</div>
			<div class="table_caption_H col-sm-1">' . $maxfee . '</div>
			<div class="table_caption_button col-sm-1"></div>
			<div class="clean_row HSpace4"></div>
';
}
$str_out .= '</div>
	</div>
</div>';
echo $str_out;
die;

header("location: ".$P."?TypePlate=".$s_TypePlate);