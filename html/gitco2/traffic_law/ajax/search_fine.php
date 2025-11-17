<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(CLS."/cls_view.php");

$Search_Iuv = CheckValue('Search_Iuv','s');
$Search_Protocol = trim(CheckValue('Search_Protocol', 'n'));
$Search_Plate = trim(CheckValue('Search_Plate', 's'));
$Search_Trespasser = trim(CheckValue('Search_Trespasser', 's'));
$Search_Code = trim(CheckValue('Search_Code', 's'));
$Search_Id = CheckValue('Search_Id','n');


$rs = new CLS_DB();
$rs->SetCharset('utf8');

$str_Fine = "";

$str_City = $_SESSION['cityid'];

if($_SESSION['cityid']=='A950'||$_SESSION['cityid']=='C730')
    $str_WhereCity = " ( CityId='A950' OR CityId='C730') ";
else if($_SESSION['cityid']=='H763'||$_SESSION['cityid']=='H416')
    $str_WhereCity = " ( CityId='H763' OR CityId='H416') ";
else
    $str_WhereCity = " CityId='". $_SESSION['cityid'] ."' ";

$table_rows = $rs->Select('Customer', "CityId='" . $str_City . "'");
$table_row = mysqli_fetch_array($table_rows);
$FinePaymentSpecificationType = $table_row['FinePaymentSpecificationType'];

$str_Where = " ProtocolId>0 AND (StatusTypeId>=12 or StatusTypeId=9) AND RuleTypeId={$_SESSION['ruletypeid']} AND ".$str_WhereCity;

if ($Search_Code==""&&$Search_Protocol==""&&$Search_Plate==""&&$Search_Trespasser=="") DIE;

$Amount = CheckValue('Amount', 'n');
$PaymentTypeId = CheckValue('PaymentDocumentId', 'n');

$str_Fine .= '
<div class="table_label_H col-sm-1">Cron</div>
<div class="table_label_H col-sm-1">Ref</div>
<div class="table_label_H col-sm-1">Data</div>
<div class="table_label_H col-sm-1">Targa</div>
<div class="table_label_H col-sm-4">Nominativo</div>
<div class="table_label_H col-sm-1">Ridotto</div>
<div class="table_label_H col-sm-1">Minimo</div>
<div class="table_label_H col-sm-1">Maggiorato</div>
<div class="table_label_H col-sm-1">&nbsp;</div>
';

if ($Search_Id > 0)
    $str_Where .= " AND f.Id = " . $Search_Id;
if ($Search_Protocol > 0)
    $str_Where .= " AND ProtocolId = " . $Search_Protocol;
if ($Search_Code !="")
    $str_Where .= " AND Code LIKE '%" . $Search_Code."%'";
if ($Search_Plate != "")
    $str_Where .= " AND VehiclePlate LIKE '" . $Search_Plate . "%'";
if ($Search_Trespasser != "")
    $str_Where .= " AND 
    (CompanyName LIKE '" . $Search_Trespasser . "%' OR 
    Surname LIKE '" . $Search_Trespasser . "%')";
if($Search_Iuv!="")
    $str_Where .= " AND '$Search_Iuv' in (PagoPA1,PagoPA2)";

//Per escludere gli inviti in AG
$str_Where .= " AND (f.KindSendDate IS NULL OR (f.KindSendDate IS NOT NULL AND f.Id IN(SELECT fhu.FineId FROM FineHistory fhu WHERE fhu.NotificationTypeId = 30))) ";
    
$searchFineTrespasser = new CLS_VIEW(V_SEARCH_FINETRESPASSER);

$rs_fine = $rs->ExecuteQuery($searchFineTrespasser->generateSelect($str_Where,null, " FineId, ProtocolId, CompanyName, Surname, Name"));
$n_Number = mysqli_num_rows($rs_fine);

$str_Fine .= '<div class="row-fluid">';

if ($n_Number == 0)
    $str_Fine .= '<div class="table_caption_H col-sm-12">Nessun verbale trovato</div>  ';
 else {
    while ($r_fine = mysqli_fetch_array($rs_fine)) {
        $str_Archive = $str_ProtocolId = "";
        $NameOut = substr($r_fine['CompanyName'] . " " . $r_fine['Surname'] . " " . $r_fine['Name'], 0, 25);
        if($r_fine['StatusTypeId']==13){
            $a_Fee = array();
            $rs_FineArticle = $rs->SelectQuery("
                SELECT 
                F.Id,
                F.CityId,
                F.Code,
                F.FineDate,
                F.ProtocolId,
                F.ProtocolYear,
                F.VehiclePlate,
                F.CountryId,                
                FA.Fee,
                FA.MaxFee
                FROM Fine F JOIN FineArticle FA ON F.Id= FA.FineId
                WHERE F.Id=".$r_fine['FineId']);
            $r_FineArticle = mysqli_fetch_array($rs_FineArticle);
            $Fee = $r_FineArticle['Fee'];
            $MaxFee = $r_FineArticle['MaxFee']*0.5;
            $ReducedFee = ($r_FineArticle['Fee'] * FINE_PARTIAL);
            $ReducedFee = NumberDisplay($ReducedFee);

            $r_Row = array();
            $r_Row['Fee'] = $Fee;
            $r_Row['ResearchFee']=0.00;
            $r_Row['NotificationFee']=0.00;
            $r_Row['CustomerFee']=0.00;
            $r_Row['CanFee']=0.00;
            $r_Row['CadFee']=0.00;
        }else{
            $Year = $r_fine['ProtocolYear'];
            $rs_Row = $rs->SelectQuery("
                SELECT  
                    FA.Fee,
                    FA.MaxFee,
                    ArT.ReducedPayment,
                    FH.CustomerFee,
                    FH.NotificationTypeId,
                    FH.NotificationFee,
                    FH.ResearchFee,
                    FH.CanFee, 	
                    FH.CadFee,
                    (
                      FH.CustomerFee+
                      FH.NotificationFee+
                      FH.ResearchFee+
                      FH.CanFee+ 	
                      FH.CadFee+
                      FH.NotifierFee+
                      FH.OtherFee) AdditionalFee,
                    FH.SendDate,
                    FH.DeliveryDate,
                    FH.ResultId
                FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
                JOIN FineHistory FH ON FA.FineId = FH.FineId
                WHERE FA.FineId=" . $r_fine['FineId'] . " AND (NotificationTypeId=6 OR NotificationTypeId=15 OR NotificationTypeId=30) 
                AND ArT.Year=" . $Year
            );

            $r_Row = mysqli_fetch_array($rs_Row);
            $AdditionalFee = $r_Row['AdditionalFee'];
            $Fee = $r_Row['Fee'] + $AdditionalFee;
            $MaxFee = $r_Row['MaxFee']*0.5 + $AdditionalFee;
            $ReducedFee = "";
            if ($r_Row['ReducedPayment']==1) {
                $ReducedFee = ($r_Row['Fee'] * FINE_PARTIAL) + $AdditionalFee;
                $ReducedFee = NumberDisplay($ReducedFee);
            }
        }
        if($r_fine['StatusTypeId']==35 || $r_fine['StatusTypeId']==37) {
            $rs_Archive = $rs->SelectQuery("
            SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
            FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
            WHERE FA.FineId=" . $r_fine['FineId']);
            $r_Archive = mysqli_fetch_array($rs_Archive);
            $str_Archive = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale archiviato in data ' . DateOutDB($r_Archive['ArchiveDate']) . ' ' . $r_Archive['ReasonTitle'] . ' ' . $r_Archive['Note'] . '"><i class="fa fa-info-circle" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
        } else if($r_fine['StatusTypeId']==90) {
            $str_Archive = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale annullato"><i class="fa fa-info-circle" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
        } else if ($r_fine['StatusTypeId']==33){
            $str_ProtocolId = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale rinotificato"><i class="fa fa-exchange" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
        }

        $str_Fine .= '
        <div class="table_caption_H col-sm-1">' . $r_fine['ProtocolId'] . '/' . $r_fine['ProtocolYear'] . ' ' . $str_Archive.$str_ProtocolId.' </div>
        <div class="table_caption_H col-sm-1">' . $r_fine['Code'] . '</div>            
        <div class="table_caption_H col-sm-1">' . DateOutDB($r_fine['FineDate']) . '</div>
        <div class="table_caption_H col-sm-1">' . $r_fine['VehiclePlate'] . '</div>
        <div class="table_caption_H col-sm-4">' . $NameOut . '</div>   
        <div class="table_caption_H col-sm-1">' . $ReducedFee . '</div>
        <div class="table_caption_H col-sm-1">' . NumberDisplay($Fee) . '</div>
        <div class="table_caption_H col-sm-1">' . NumberDisplay($MaxFee) . '</div>
        <div class="table_caption_H col-sm-1"><a href="#"><span class="fa fa-plus-circle" name="' . $r_Row['Fee'] . '_' . $r_Row['ResearchFee'] . '_' . $r_Row['NotificationFee'] . '_' . $r_Row['CustomerFee'] . '_' . $r_Row['CanFee'] . '_' . $r_Row['CadFee'] . '_' .$MaxFee . '_' .$ReducedFee . '_' .$Fee . '" id="' . $r_fine['FineId'] . '" year="'.$r_fine['ProtocolYear'].'" alt="' . $r_fine['CompanyName'] . " " . $r_fine['Surname'] . " " . $r_fine['Name'] . '"></span></a></div>
    ';
    }
}

$str_Fine .= '</div>
<script>
$(".fa-plus-circle").click(function(){
	var id = $(this).attr("id");
    var year = $(this).attr("year");
	var name = $(this).attr("alt");
    var a_Fee = $(this).attr("name").split("_");
	$("#span_name").html(name);
	$("#Search_FineId").val(id);
	
	if($("#PaymentDocumentId").val()==0) $("#Fee").val(a_Fee[7]);
	else if($("#PaymentDocumentId").val()==1) $("#Fee").val(a_Fee[8]);
	else if($("#PaymentDocumentId").val()==2) $("#Fee").val(a_Fee[6]);
	
	$("#information").attr("ridoto", a_Fee[7]);
	$("#information").attr("normale", a_Fee[8]);
	$("#information").attr("maggiorato", a_Fee[6]);
	
	$("#ResearchFee").val(a_Fee[1]);
	$("#NotificationFee").val(a_Fee[2]);
	$("#CustomerFee").val(a_Fee[3]);
	$("#CanFee").val(a_Fee[4]);
	$("#CadFee").val(a_Fee[5]);
	$("#OfficeNotificationFee").val("0.00");
	
	$("#update").prop("disabled", false);
	$("#save").prop("disabled", false);
	
	$.ajax({
		url: \'ajax/search_payment.php\',
		type: \'POST\',
		dataType: \'json\',
		cache: false,
		data: {Search_FineId: id},
		success: function (data) {
			$(\'#payment_content\').show();
			$(\'#payment_content\').html(data.Payment);
		}
	});
    
    $.ajax({
		url: \'ajax/search_installments.php\',
		type: \'GET\',
		dataType: \'json\',
		cache: false,
		data: {Search_FineId: id},
		success: function (data) {
            var options = "<option>";
			for(var i = 0; i < data.length; i++){
                var installmentId = data[i].InstallmentId;
                var status = data[i].Status;
                var regDate = data[i].RegDate;
                var installmentType = data[i].InstallmentType;
                var rateName = data[i].RateName;
                var position = data[i].Position;
                
                options += \'<option value="\'+installmentId+\'" id="InstallmentType_\'+installmentId+\'">\'+status+" - "+regDate+"  "+installmentType+"  "+rateName+"  "+position
                }
            $(\'#InstallmentList\').html(options);
            
            //Bollettini, Posta online e pagamenti manuali
            if(data.length > 0 && (typeof page == "undefined"))
                $(\'#InstallmentList\').css("visibility","visible");
            //Tutti gli altri della bonifica pagamenti
            else if(data.length > 0 && (typeof showInstallmentList != "undefined" && showInstallmentList == true))
                $(\'#InstallmentList\').css("visibility","visible");
            else
                $(\'#InstallmentList\').css("visibility","hidden");
            
		},
        error: function(ts) { console.log(ts.responseText) }
	});
    
    $(document).trigger("fineadd", [id, year]);
	return false;
});
</script>';

echo json_encode( array( "Trespasser" => $str_Fine ));