<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");



$Documentation = CheckValue('Documentation','s');
$int_Position = strpos($Documentation,$_SESSION['cityid'].'/')+5;
$str_Documentation  = substr($Documentation,$int_Position);


$path = PAYMENT_RECLAIM."/".$_SESSION['cityid'] ."/";
$f_List = scandir($path,1);


if(isset($f_List[0])){

    $a_FileName = explode("_",$f_List[0]);

    if($a_FileName[0]=="ZZ"){
        $n_Value = (INT)$a_FileName[1]+1;
        $str_Name = $n_Value;
        $n_Count = 3-strlen($str_Name);
        if($n_Count>0){
            for($i=1;$i<=$n_Count;$i++){
               $str_Name = "0".$str_Name;
            }
        }
        $str_FileName = "ZZ_".$str_Name."_".str_replace($a_FileName[0]."_".$a_FileName[1]."_","",$str_Documentation);

    }else{
        $str_FileName = "ZZ_001_".$str_Documentation;
    }

    $a_FinePayment = array(
        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$str_FileName),
    );

    $rs->Update('FinePayment',$a_FinePayment, "Documentation='".$str_Documentation."'");

    rename($path.$str_Documentation,$path.$str_FileName);

}





if($mgmtPayment){
    header("location: mgmt_payment.php");
}else{
    header("location: frm_reclaim_payment.php?PaymentTypeId=1");
}
