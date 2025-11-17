<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');

$str_out .='
<form name="f_search" id="f_search" action="mgmt_anomaly.php" method="post">
<input type="hidden" name="PageTitle" value="'.$PageTitle.'">
<div class="col-sm-12" style="background-color: rgb(51, 140, 181);">
    <div class="col-sm-11" style="height:4.6rem; border-right:1px solid #E7E7E7;">
        <div class="col-sm-2 BoxRowLabel">
            Verbali inseriti da/a
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input value="'.$Search_FromFineDate.'" type="text" class="form-control frm_field_date" id="Search_FromFineDate" name="Search_FromFineDate">  
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input value="'.$Search_ToFineDate.'" type="text" class="form-control frm_field_date" id="Search_ToFineDate" name="Search_ToFineDate">  
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Tipo Anomalie
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <select class="form-control" id="Search_AnomalyType" name="Search_AnomalyType">
                <option value="0"'.($Search_AnomalyType == 0 ? ' selected' : '').'>Generiche</option>
                <option value="1"'.($Search_AnomalyType == 1 ? ' selected' : '').'>Tutte</option>         
                <option value="2"'.($Search_AnomalyType == 2 ? ' selected' : '').'>Marca modello</option>
            </select>  
        </div>
    </div>
    <div class="col-sm-1" style="height:4.6rem;">
        <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            <button id="search" type="submit" class="btn btn-primary" style="margin-top:0;width:inherit;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
<div class="clean_row HSpace4"></div>
</form>';

$str_where = "1=1";
    
if($Search_FromFineDate!=""){
    $str_where .= " AND RegDate >='".DateInDB($Search_FromFineDate)."'";
}
if($Search_ToFineDate!=""){
    $str_where .= " AND RegDate <='".DateInDB($Search_ToFineDate)."'";
}

if($Search_AnomalyType==""){
    $str_where .= " AND AnomalyBrandModelId IS NULL";
}
if($Search_AnomalyType!=""){
    if ($Search_AnomalyType == 0)
        $str_where .= " AND AnomalyBrandModelId IS NULL";
    else if ($Search_AnomalyType == 2)
        $str_where .= " AND AnomalyBrandModelId IS NOT NULL";
}

$str_out .='
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Rif.to</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">Violazione</div>
				<div class="table_label_H col-sm-1">Articolo</div>
				<div class="table_label_H col-sm-4">Anomalia</div>
        		<div class="table_add_button col-sm-1 right">
				</div>
				<div class="clean_row HSpace4"></div>';


$table_rows = $rs->Select('V_FineAnomaly',$str_where." AND CityId='".$_SESSION['cityid']."'", "FineDate, FineTime", $pagelimit . ',' . PAGE_NUMBER);



$RowNumber = mysqli_num_rows($table_rows);

if ($RowNumber == 0) {
	$str_out.=
		'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {

		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Code'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] .'<i class="'.$aVehicleTypeId[$table_row['VehicleTypeId']].'" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['ViolationTitle'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Article'] .' ' .$table_row['Paragraph']. ' ' .$table_row['Letter'].'</div>';

		$Status = "";

		$str_out.= '<div class="table_caption_H col-sm-4">' . $table_row['Anomaly'] .'</div>';


		$str_out.= '<div class="table_caption_button col-sm-1">';
		$str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open"></span></a>');
		$str_out.= '&nbsp;';
		$str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-pencil"></span></a>');
		$str_out.= '&nbsp;';
		$str_out.= ChkButton($aUserButton, 'exp','<a href="mgmt_violation_exp.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-remove"></span></a>');

		$str_out.= '</div>
			            <div class="clean_row HSpace4"></div>';
		}


}
$table_users_number = $rs->Select('V_FineAnomaly',$str_where." AND CityId='".$_SESSION['cityid']."'", "FineDate, FineTime");
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");
$str_out.= '<div>
	</div>';


echo $str_out;

include(INC."/footer.php");
