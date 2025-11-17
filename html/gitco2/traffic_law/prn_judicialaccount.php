<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_Month = array("","GENNAIO","FEBBRAIO","MARZO","APRILE","MAGGIO","GIUGNO","LUGLIO","AGOSTO","SETTEMBRE","OTTOBRE","NOVEMBRE","DICEMBRE");




$str_out.= '
    	<div class="row-fluid">
        	<div class="col-sm-12">

                <div class="table_label_H col-sm-12">Estremi riscossione</div>
            </div>
            
            <div class="clean_row HSpace4"></div>   
            
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-2">N Ord.</div>
                <div class="table_label_H col-sm-4">Periodo riscossione</div>
                <div class="table_label_H col-sm-3">Numero ricevute</div>
                <div class="table_label_H col-sm-3">Importo</div>
            </div>
        ';






$rs_Payment = $rs->SelectQuery("
  SELECT 
    COUNT(*) TotalPayment, 
    MONTH(PaymentDate) PaymentMonth, 
    SUM(Amount) TotalAmount
  FROM `FinePayment` 
  WHERE BankMgmt=1 AND CityId='". $_SESSION['cityid'] ."' AND YEAR(PaymentDate)=". $_SESSION['year'] ." GROUP BY MONTH(PaymentDate)



");





$rs_Refund = $rs->SelectQuery("
  SELECT 
    COUNT(*) TotalRefund, 
    MONTH(RefundDate) RefundMonth, 
    SUM(Amount) TotalAmount
  FROM `FineRefund` 
  WHERE CityId='". $_SESSION['cityid'] ."' AND YEAR(RefundDate)=". $_SESSION['year'] ." GROUP BY MONTH(RefundDate)

");


$a_Refund = array();
while ($r_Refund = mysqli_fetch_array($rs_Refund)) {
    $a_Refund[$r_Refund['RefundMonth']]=array("tot"=>$r_Refund['TotalRefund'],
        "amount"=>$r_Refund['TotalAmount']);
}


$n_CountRow = 0;
$n_TotalPayment = 0;
$f_TotalAmount = 0.00;


while ($r_Payment = mysqli_fetch_array($rs_Payment)) {

    $n_CountRow++;

    if($n_CountRow<$r_Payment['PaymentMonth']){
        for($i=$n_CountRow; $i<$r_Payment['PaymentMonth'];$i++ ){
            $str_out .= '
            <div class="table_caption_H col-sm-2">' . $n_CountRow .'</div>
            <div class="table_caption_H col-sm-4">' . $a_Month[$i] .'</div>
            <div class="table_caption_H col-sm-3"></div>
            <div class="table_caption_H col-sm-3"></div>
            
            <div class="clean_row HSpace4"></div>   
        ';

        }

        $n_CountRow=$r_Payment['PaymentMonth'];
    }



    $n_TotalPaymentMonth = $r_Payment['TotalPayment'];
    $f_TotalAmountMonth = $r_Payment['TotalAmount'];

    if(isset($a_Refund[$n_CountRow])){
        $a_RefundMonth = $a_Refund[$n_CountRow];
        $n_TotalPaymentMonth += $a_RefundMonth['tot'];
        $f_TotalAmountMonth -= $a_RefundMonth['amount'];
    }

    $str_out .= '
        <div class="table_caption_H col-sm-2">' . $r_Payment['PaymentMonth'] .'</div>
        <div class="table_caption_H col-sm-4">' . $a_Month[$r_Payment['PaymentMonth']] .'</div>
        <div class="table_caption_H col-sm-3">' . $n_TotalPaymentMonth .'</div>
        <div class="table_caption_H col-sm-3">' . $f_TotalAmountMonth .'</div>
    ';

    $n_TotalPayment += $n_TotalPaymentMonth;
    $f_TotalAmount += $f_TotalAmountMonth;



    $str_out .= '    
        <div class="clean_row HSpace4"></div>          
        ';


}

$str_out .= '
    <div class="table_caption_H col-sm-2"></div>
    <div class="table_caption_H col-sm-4">TOTALE</div>
    <div class="table_caption_H col-sm-3">' . $n_TotalPayment .'</div>
    <div class="table_caption_H col-sm-3">' . $f_TotalAmount .'</div>
';


$str_out .= '    
    <div class="clean_row HSpace4"></div>          
';

$str_out .= ' 
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                <button class="btn btn-primary" id="btn_pdf" style="width:6rem;height:4rem;">    
                    <i class="fa fa-file-pdf-o" style="font-size:1.5rem;"></i>            
                </button>
                <button class="btn btn-primary" id="btn_xls" style="width:6rem;height:4rem;">
                    <i class="fa fa-file-excel-o" style="font-size:1.5rem;"></i>
                </button>
             </div>    
        </div>
    </div>    
';

echo $str_out;

?>

<script type="text/javascript">

	$(document).ready(function () {

        $("#btn_pdf").on('click',function(){
            window.location = 'prn_judicialaccount_exe.php';
        });


        $("#btn_xls").on('click',function(e){
            window.location = 'prn_judicialaccount_exe.php?CSV=1';
        });


	});
</script>
<?php
include(INC."/footer.php");
