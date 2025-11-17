<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
require_once(CLS."/cls_view.php");

if($_POST) {
    if($r_Customer['LicensePointPaymentCompletion']==0)
      $licensepoint=new CLS_VIEW(V_LICENSEPOINT0);
      else
        $licensepoint=new CLS_VIEW(V_LICENSEPOINT1);
    $str_Where = " CommunicationStatus!=3 and CountryId='Z000' AND CityId='".$_SESSION['cityid']."' ";
    $str_Fine = "";
    $Search_Protocol = trim(CheckValue('Search_Protocol', 'n'));
    $Search_Plate = trim(CheckValue('Search_Plate', 's'));
    $Search_Trespasser = trim(CheckValue('Search_Trespasser', 's'));
    $Search_Code = trim(CheckValue('Search_Code', 's'));
    $Search_Year = trim(CheckValue('Search_Year', 's'));

    if ($Search_Code=="" && $Search_Protocol<=0 && $Search_Plate=="" && $Search_Trespasser=="") {
        echo json_encode(
            array(
                "Trespasser" => '<div class="table_caption_H alert-danger col-sm-12">Inserire almeno un dato tra Cron, Riferimento, Trasgressore o Targa</div>'
            )
        );
        DIE;
    }
    
    if($Search_Year!='')
        $str_Where.=" And ProtocolYear=$Search_Year";
    if ($Search_Protocol > 0) {
        $str_Where .= " AND ProtocolId = " . $Search_Protocol;
    }
    if ($Search_Code !="") {
        $str_Where .= " AND Code LIKE '%" . $Search_Code."%'";
    }
    if ($Search_Plate != "") {
        $str_Where .= " AND VehiclePlate LIKE '" . $Search_Plate . "%'";
    }
    if ($Search_Trespasser != "") {
        $str_Where .= " AND 
		(CompanyName LIKE '" . $Search_Trespasser . "%' OR 
		Surname LIKE '" . $Search_Trespasser . "%')";
    }
    $rs_LicensePoint= $rs->selectQuery($licensepoint->generateSelect($str_Where));
    $n_Number = mysqli_num_rows($rs_LicensePoint);
    trigger_error("Trovate $n_Number con $str_Where",E_USER_NOTICE);
    $str_out .= ' 
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-3">Trasgressore</div>
				<div class="table_label_H col-sm-2">CF</div>
				<div class="table_label_H col-sm-4">Dati Patente</div>
				<div class="table_label_H col-sm-1">Punti</div>
	        </div>';
    $str_Fine .= '<div class="row-fluid">';
    if ($n_Number == 0) {
        $str_out .= '<div class="table_caption_H col-sm-12">Nessun verbale trovato</div>  ';
    } else {
        while ($r_LicensePoint = mysqli_fetch_array($rs_LicensePoint)) {

            $str_CssRow = "";
            $str_Trespasser = trim(trim($r_LicensePoint['Surname']) .' '. trim($r_LicensePoint['Name']));

            $n_LicenseYear = $r_LicensePoint['LicenseYear'];
            $n_Point =($n_LicenseYear>=3) ? $r_LicensePoint['LicensePoint'] : $r_LicensePoint['YoungLicensePoint'];
            if($r_LicensePoint['ArticleNumber']>1){
                $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=".$r_LicensePoint['Id']." AND LicensePoint>0");
                while($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)){
                    $n_PointLicense =($n_LicenseYear>=3) ? $r_AdditionalArticle['LicensePoint'] : $r_AdditionalArticle['YoungLicensePoint'];
                    $n_Point+= $n_PointLicense;
                }
            }
            if ($r_LicensePoint['CommunicationStatus'] != 9)
                $point_sign = "<span class='col-sm-12' style='text-align: right; color:red ; '>-$n_Point</span>";
            else
                $point_sign = "<span class='col-sm-12' style='text-align:right; color:green ; a'>+$n_Point</span>";
            if($n_Point>15) $n_Point = 15;
                $str_out.= '
                <div class="table_caption_H col-sm-1'.$str_CssRow.'"><a href="#"><span class="fa fa-fw fa-share" id="' . $r_LicensePoint['Id'] . '" alt="'. $str_Trespasser .'"></span></a> ' . $r_LicensePoint['ProtocolId'] .'/'.$r_LicensePoint['ProtocolYear'].'</div>
                <div class="table_caption_H col-sm-1'.$str_CssRow.'">' . DateOutDB($r_LicensePoint['FineDate']) .'</div>
                <div class="table_caption_H col-sm-3'.$str_CssRow.'">' . $str_Trespasser .'</div>
                <div class="table_caption_H col-sm-2'.$str_CssRow.'">' . $r_LicensePoint['TaxCode'] .'</div>
                <div class="table_caption_H col-sm-4'.$str_CssRow.'">' . $r_LicensePoint['LicenseNumber'] .' ('.$r_LicensePoint['LicenseCategory'] .')' .' - ' . $r_LicensePoint['LicenseOffice'] .' ' . DateOutDB($r_LicensePoint['LicenseDate']).'</div>              
                <div class="table_caption_H col-sm-1'.$str_CssRow.'">' . $point_sign .'</div>';
        }
    }

    $str_out .= '</div>';
    $str_out .= '
<script>
$(".fa-share").click(function(){

	var id = $(this).attr("id");
	var name = $(this).attr("alt");

	$("#span_name").html(name).addClass("alert-success");
	$("#Search_FineId").val(id);
    
	
	$("#save").prop("disabled", false);


	return false;
});
</script>
';
    echo json_encode(
        array(
            "Trespasser" => $str_out,
        )
    );
}