<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_GETLink = "?1";


$str_Where = "CityId='".$_SESSION['cityid']."' AND FineId>0";

if($Search_FromPaymentDate=="") {
    $Search_FromPaymentDate = "01/01/".$_SESSION['year'];
}
$str_Where .= " AND PaymentDate>='".DateInDB($Search_FromPaymentDate)."'";
if($Search_ToPaymentDate==""){
    $Search_ToPaymentDate ="31/12/".$_SESSION['year'];
}
$str_Where .= " AND PaymentDate<='".DateInDB($Search_ToPaymentDate)."'";










$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="mgmt_payment.php?PageTitle=Verbali/Pagamenti" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:2.3rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" style="width:8rem" value="'.$Search_Plate.'">
            </div>          
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:8rem" value="'.$Search_Ref.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_PaymentName" type="text" style="width:30rem" value="'.$Search_PaymentName.'">
            </div>             
        </div>
        <div class="col-sm-1 BoxRow"  style="height:2.3rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.3rem;font-size:1.6rem;"></i>
            </div>    	
        </div>
        </form>
    </div>
';


$str_out.= '
    
<form id="f_Analysis" action="mgmt_payment_act.php?PageTitle=Verbali/Pagamenti" method="post">
<div class="col-sm-12" >
    <div class="col-sm-11 BoxRow" style="height:2.3rem; border-right:1px solid #E7E7E7;">
        <div class="col-sm-6 BoxRowLabel">
            &nbsp;
        </div>    
        <div class="col-sm-1 BoxRowLabel">
            Da data
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_date" name="Search_FromPaymentDate" type="text" style="width:12rem" value="'.$Search_FromPaymentDate.'">
        </div>             
        <div class="col-sm-1 BoxRowLabel">
            A data
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_date" name="Search_ToPaymentDate" type="text" style="width:12rem" value="'.$Search_ToPaymentDate.'">
        </div>  
    </div>
    <div class="col-sm-1 BoxRow"  style="height:2.3rem;">
        <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            <i class="glyphicon glyphicon-stats" style="margin-top:0.3rem;font-size:1.6rem;"></i>
        </div>    	
    </div>
</div>	
</form>    
';

$str_out.= '
</div>
<div class="clean_row HSpace4"></div>
';



$str_Post = "";
$str_Bank = "";
$str_Paypal = "";

$str_label = "";
$a_data = array();
$str_Data = "";
$i = 0;
$flt_Post = 0;
$flt_Bank = 0;
$flt_Paypal = 0;


$flt_Total = 0;


$rs_Payment = $rs->SelectQuery("SELECT SUM(Amount) TotAmount, PaymentDate, PaymentTypeId FROM FinePayment WHERE ".$str_Where ."GROUP BY PaymentDate, PaymentTypeId ORDER BY PaymentDate");



if (mysqli_num_rows($rs_Payment)== 0) {
    $str_out.=
		'<div class="table_caption_H col-sm-12">
			Nessun pagamento presente
		</div>';
} else {


	while ($r_Payment = mysqli_fetch_array($rs_Payment)) {
	    if($str_Data!= $r_Payment['PaymentDate'] ){
            $i++;
            $str_Data =  $r_Payment['PaymentDate'];
            $a_data[$i] = $r_Payment['PaymentDate'];
        }

        if($r_Payment['PaymentTypeId']==1){
	        $str_Post .="[".$i.",".$r_Payment['TotAmount']."],";
            $flt_Post += $r_Payment['TotAmount'];
        } else if($r_Payment['PaymentTypeId']==2){
            $str_Bank .="[".$i.",".$r_Payment['TotAmount']."],";
            $flt_Bank += $r_Payment['TotAmount'];
        } else if($r_Payment['PaymentTypeId']==3){
            $str_Paypal .="[".$i.",".$r_Payment['TotAmount']."],";
            $flt_Paypal += $r_Payment['TotAmount'];
        }

    }

    for($i=1; $i<=count($a_data);$i++){
	    $a_Day = explode("-",$a_data[$i]);
        $str_label .= "[".$i.",'".$a_Day[2]."/".$a_Day[1]."'],";
    }
    $flt_Total = $flt_Post + $flt_Bank + $flt_Paypal;




}


echo $str_out;
?>
    <script type="text/javascript" src="<?= LIB ?>/flot/js/flot.js"></script>
    <script type="text/javascript" src="<?= LIB ?>/flot/js/flot.stack.js"></script>

<div class="row-fluid">
    <div class="col-sm-12" >
        <div class="col-sm-12">
            <div class="col-sm-10 BoxRowLabel">
                Totale posta:
            </div>

            <div class="col-sm-2 BoxRowLabel">
                <?= NumberDisplay($flt_Post) ?>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-10 BoxRowLabel">
                Totale banca:
            </div>
            <div class="col-sm-2 BoxRowLabel">
                <?= NumberDisplay($flt_Bank) ?>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">

            <div class="col-sm-10 BoxRowLabel">
                Totale Paypal:
            </div>

            <div class="col-sm-2 BoxRowLabel">
                <?= NumberDisplay($flt_Paypal) ?>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-10 BoxRowLabel">
                Totale periodo:
            </div>
            <div class="col-sm-2 BoxRowLabel">
                <?= NumberDisplay($flt_Total) ?>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12" style="background-color: rgba(50,169,237,0.11);min-height:620px">
            <div class="col-sm-12">
                <div id="placeholder" style="width:1200px;height:600px;"></div>
            </div>
        </div>

    </div>
</div>
<script>
    $(function () {
        var css_id = "#placeholder";
        var data = [
            {label: 'Posta', data: [<?= $str_Post ?> ]},
            {label: 'Banca', data: [<?= $str_Bank ?>]},
            {label: 'Paypal', data: [<?= $str_Paypal ?>]}


        ];
        var options = {
            series: {stack: 0,
                lines: {show: false, steps: false },
                bars: {show: true, barWidth: 0.9, align: 'center',},},
            xaxis: {ticks: [<?= $str_label ?>]},
        };

        $.plot($(css_id), data, options);
    });
</script>


<script type="text/javascript">

	$(document).ready(function () {

        $('.glyphicon-search').click(function(){
            $('#f_Search').submit();
        });

        $('#f_Search').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                $("#f_Search").submit();
            }
        });


        $(".glyphicon-stats").click(function(){
            $("#f_Analysis").submit();
        });


        $('#f_Analysis').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                $("#f_Analysis").submit();
            }
        });
	});
</script>
<?php
include(INC."/footer.php");
