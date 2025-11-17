<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$ProtocolId     = CheckValue('ProtocolId','n');
$PaymentType    = CheckValue('PaymentType','n');


$a_PaymentType = array("","","");

if($ProtocolId==0) $ProtocolId="";
if($PaymentType==0) $PaymentType=1;


$a_PaymentType[$PaymentType] = " checked";

$str_out .='
<div class="row-fluid">
    <form id="f_Print" action="prn_pagopa_exe.php" method="post">

    <div class="col-sm-12" >
       
        <div class="col-sm-1 BoxRowLabel">
            Cron
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_numeric" type="text" name="ProtocolId" value="'. $ProtocolId .'" style="width:12rem">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Pagamento
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input type="radio" name="PaymentType" value="1" '. $a_PaymentType[1] .'>Ridotto
            <input type="radio" name="PaymentType" value="2" '. $a_PaymentType[2] .'>Normale
        </div>   
        <div class="col-sm-5 BoxRowLabel"></div>
        
        <div class="clean_row HSpace4"></div>
    </div>     
    <div class="col-sm-12 BoxRow" style="height:6rem;">
        <div class="col-sm-12" style="text-align:center;line-height:6rem;">
            <input class="btn btn-default" name="print" style="margin-top:1rem;" type="submit" value="Stampa" />
        </div>
    </div>
     </form>	
           


</div>';


echo $str_out;

include(INC."/footer.php");
