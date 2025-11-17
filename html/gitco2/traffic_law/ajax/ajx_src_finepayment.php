<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");




if($_POST) {
    $rs = new CLS_DB();
    $rs->SetCharset('utf8');

    $str_Payment = "";

    $FineId = CheckValue('FineId','n');

    $str_WhereCity = ($_SESSION['cityid']=='A950'||$_SESSION['cityid']=='C730') ? " (CityId='A950' OR CityId='C730') " : " CityId='". $_SESSION['cityid'] ."' ";
    $str_WhereCity = ($_SESSION['cityid']=='H763'||$_SESSION['cityid']=='H416') ? " (CityId='H763' OR CityId='H416') " : " CityId='". $_SESSION['cityid'] ."' ";

    $str_Where = " FineId=0 AND ".$str_WhereCity;

    $Search_Protocol = trim(CheckValue('Search_Protocol', 'n'));
    $Search_Plate = trim(CheckValue('Search_Plate', 's'));
    $Search_Name = trim(CheckValue('Search_Name', 's'));
    $Search_Code = trim(CheckValue('Search_Code', 's'));


    if ($Search_Code==""&&$Search_Protocol==""&&$Search_Plate==""&&$Search_Name=="") DIE;



    $str_Payment .= '
	<div class="table_label_H col-sm-2">Cron</div>
	<div class="table_label_H col-sm-2">Ref</div>
	<div class="table_label_H col-sm-2">Data</div>
	<div class="table_label_H col-sm-2">Targa</div>
	<div class="table_label_H col-sm-4">Nominativo</div>
';


    if ($Search_Protocol > 0) {
        $str_Where .= " AND ProtocolId = " . $Search_Protocol;
    }
    if ($Search_Code !="") {
        $str_Where .= " AND Code LIKE '%" . $Search_Code."%'";
    }

    if ($Search_Plate != "") {
        $str_Where .= " AND VehiclePlate LIKE '" . $Search_Plate . "%'";
    }
    if ($Search_Name != "") {
        $str_Where .= " AND  Name LIKE '%" . $Search_Name . "%'";
    }


    $rs_FinePayment = $rs->Select('FinePayment', $str_Where, "PaymentDate, Name");
    $n_Number = mysqli_num_rows($rs_FinePayment);

    $str_Payment .= '<div class="row-fluid">';

    if ($n_Number == 0) {
        $str_Payment .= '<div class="table_caption_H col-sm-12">Nessun pagamento trovato</div>  ';
    } else {
        while ($r_FinePayment = mysqli_fetch_array($rs_FinePayment)) {

            $str_Payment .= '
			<div class="table_caption_H col-sm-2">
			    <a href="#"><span class="fa fa-share" fineid="' . $FineId . '" paymentid="' . $r_FinePayment['Id'] . '"></span></a> 
			    ' . $r_FinePayment['ProtocolId'] . ' 
			</div>
			<div class="table_caption_H col-sm-2">' . $r_FinePayment['Code'] . '</div>
						
			<div class="table_caption_H col-sm-2">' . DateOutDB($r_FinePayment['PaymentDate']) . '</div>
			<div class="table_caption_H col-sm-2">' . $r_FinePayment['VehiclePlate'] . '</div>
			<div class="table_caption_H col-sm-4">' . $r_FinePayment['Name'] . '</div>   

		';
        }
    }

    $str_Payment .= '</div>';


  $str_Payment .= '
<script>
$(".fa-share").click(function(){

	var FineId = $(this).attr("fineid");
	var PaymentId = $(this).attr("paymentid");
	
    $.ajax({
        url: \'ajax/ajx_upd_finepayment_exe.php\',
        type: \'POST\',
        dataType: \'json\',
        cache: false,
        data: {FineId: FineId, PaymentId: PaymentId}
    });

    $(\'#payment_content\').html(\'\');
    $(\'#DIV_SrcPayment\').hide();
    $(\'[fineid="\'+ FineId +\'"]\').hide();
});
</script>
';


    echo json_encode(
        array(
            "Payment" => $str_Payment,

        )
    );
}