<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

global $rs;

$Id = CheckValue('Id', 's');

$a_Out = array('Success' => true, 'Message' => '');
$r_PaymentRate = $rs->getArrayLine($rs->Select('PaymentRate', "Id=$Id"));

if($r_PaymentRate){
    $rs->Start_Transaction();
    
    $a_PaymentRate = array(
        array('field'=>'StatusRateId','selector'=>'value','type'=>'int','value'=>RATEIZZAZIONE_CHIUSA,'settype'=>'int'),
        array('field'=>'ClosingDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'))
    );
    
    $rs->Update("PaymentRate", $a_PaymentRate, "Id=$Id");
    $n_AffectedRows = mysqli_affected_rows($rs->conn);
    
    if ($n_AffectedRows <= 0){
        $a_Out['Success'] = false;
        $a_Out['Message'] = "Impossibile chiudere la rateizzazione selezionata.";
    }
    
    $rs->End_Transaction();
} else {
    $a_Out['Success'] = false;
    $a_Out['Message'] = "Identificativo rateizzazione non presente su banca dati.";
}

echo json_encode($a_Out);

