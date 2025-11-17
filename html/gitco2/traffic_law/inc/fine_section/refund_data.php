<?php


//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         REFUND
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////


$str_CSSRefund = 'data-toggle="tab"';
$str_Refund = "";


$rs_Refund = $rs->Select('FineRefund', "FineId=" . $Id);
if(mysqli_num_rows($rs_Refund)>0){
    $r_Refund = mysqli_fetch_array($rs_Refund);



    $str_Refund = '
            <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    RIMBORSO
                </div>
            </div>
            <div class="clean_row HSpace4"></div>                  
            <div class="col-sm-12">
                <div class="col-sm-3 BoxRowLabel">
                    Data Rimborso
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    ' . DateOutDB($r_Refund['RefundDate']) . '
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Importo
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    ' . $r_Refund['Amount'] . '	
                </div>
            </div>
            ';

} else $str_CSSRefund = ' style="color:#C43A3A; cursor:not-allowed;" ';



$str_Refund_data = '
<div class="tab-pane" id="Refund">            
    <div class="col-sm-12">
        '.$str_Refund.'
    </div>
</div>
';