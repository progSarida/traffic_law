<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters','s');

$Id = CheckValue('Id','n');
$Documentation = CheckValue('Documentation','s');
$mgmtPayment = CheckValue('mgmtPayment','n');
$PaymentTypeId = CheckValue('PaymentTypeId', 'n');

$str_Error = '';

//probabilmente in sessione ora c'è anche Id quindi cancella il pagamento
// inverto il comportamento cercando prima il documento e poi se non c'è e c'è l'Id cancella il pagamento
trigger_error("Cancello pagamento con Documentation $Documentation e Id $Id",E_USER_NOTICE);
if (isset($Documentation) && !empty(($Documentation))) {
    $int_Position = strpos($Documentation,$_SESSION['cityid'].'/')+5;
    $str_Documentation  = substr($Documentation,$int_Position);
    $path = PAYMENT_RECLAIM."/".$_SESSION['cityid'] ."/";
    if (file_exists($path.$str_Documentation)){
        unlink($path.$str_Documentation);
    }
    
    //cancello il pagamento associato al documento solo se non è associato ad alcuni verbale
    $rs->Delete("FinePayment", "Documentation='".$str_Documentation."' AND FineId = 0");
}
else if($Id>0){
    $rs->Delete("FinePayment", "Id=".$Id);
} 
else {
    $str_Error = 'Impossibile cancellare il pagamento.';
}

if($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
} else $_SESSION['Message']['Success'] = "Azione eseguita con successo.";

if($mgmtPayment){
    header("location: mgmt_payment.php".$Filters);
} else {
    header("location: ".impostaParametriUrl(array('PaymentTypeId' => $PaymentTypeId), 'frm_reclaim_payment.php'.$Filters));
}
