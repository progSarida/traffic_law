<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");



/*
    DROP VIEW GITCO_PAYMENT;
    create view GITCO_PAYMENT
    AS SELECT
    RCC.Reg_Progr_Registro, RCC.Reg_Comune_Violazione, RCC.Reg_Anno, TEP.Pag_Data_Pag, TEP.Pag_Importo_Pag
    FROM registro_cronologico_cds RCC
    JOIN targhe_estere_pagamenti TEP ON RCC.Reg_Progr = TEP.Pag_Registro
    WHERE RCC.Reg_Comune_Violazione='C826' AND RCC.Reg_Anno=2013
ORDER BY Reg_Progr_Registro


G213
L298
H416
B509
C933
E555
G687




 */



/*
$rs_GP = $rs->Select('gitco2.GITCO_PAYMENT',"1=1");

 while ($r_GP = mysqli_fetch_array($rs_GP)) {
     $rs_FP = $rs->SelectQuery("
     
     SELECT F.ProtocolId, F.ProtocolYear, FP.FineId, FP.PaymentDate, FP.Amount 
     FROM FinePayment FP JOIN Fine F ON FP.FineId = F.Id 
     WHERE  F.ProtocolId=" . $r_GP['Reg_Progr_Registro'] . " 
     AND F.CityId='" . $r_GP['Reg_Comune_Violazione'] . "' 
     AND F.ProtocolYear=". $r_GP['Reg_Anno']
     );

    if(mysqli_num_rows($rs_FP)==0){
        echo "Pagamento cron " . $r_GP['Reg_Progr_Registro'] . "/" . $r_GP['Reg_Anno'] . " NON TROVATO<br />";
    } else if(mysqli_num_rows($rs_FP)>1){
        $r_FP = mysqli_fetch_array($rs_FP);
        echo "Pagamento cron " . $r_FP['ProtocolId'] . "/" . $r_FP['ProtocolYear'] . " - ". $r_FP['Amount'] . " + di 1<br />";
    }else{
        $r_FP = mysqli_fetch_array($rs_FP);
        if($r_FP['Amount']!=$r_GP['Pag_Importo_Pag']){
            echo $r_FP['ProtocolId'] . "/" . $r_FP['ProtocolYear'] . " - importi diversi: ". $r_FP['Amount']."/".$r_GP['Pag_Importo_Pag']."<br />";
        }
        if($r_FP['PaymentDate']!=$r_GP['Pag_Data_Pag']){
            echo $r_FP['ProtocolId'] . "/" . $r_FP['ProtocolYear'] . " - date diverse: ". $r_FP['PaymentDate']."/".$r_GP['Pag_Data_Pag']."<br />";
        }
    }



 }
*/
$a_PaymentTypeId = array(
    "CC" => 2,
    "PAYPAL" => 3,
    "ASSEGNO" => 5,
    "BANCOMAT" => 2,
    "POS" => 2,
    "VAGLIA" => 2,

);
$CityId = 'G687';
$TableId = 1;
$ImportationId = 0;
$rs_Payment = $rs->Select('gitco2.targhe_estere_pagamenti',"Pag_Comune_CC='".$CityId."' AND ( Pag_Notifica= 0 OR Pag_Registro=0)");

while ($r_Payment = mysqli_fetch_array($rs_Payment)) {


    $PaymentName = $r_Payment['Pag_Trasgressore'];
    $BankMgmt = ($r_Payment['Pag_Riscossore'] == "SARIDA") ? 1 : 0;
    $PaymentTypeId = $a_PaymentTypeId[$r_Payment['Pag_Tipo_Pag']];
    $PaymentDate = $r_Payment['Pag_Data_Pag'];
    $Amount = $r_Payment['Pag_Importo_Pag'];
    $PaymentNote = trim($r_Payment['Pag_Note'] . " " . $r_Payment['Pag_Blocco_Riscossione']);
    $PaymentRegDate = $r_Payment['Pag_Data_Reg'];
    $PaymentRegTime = $r_Payment['Pag_Ora_Registrazione'];
    $PaymentUser = $r_Payment['Pag_Operatore'];



    $a_Payment = array(
        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentName),
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
        array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
        array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
        array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentNote),
        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentRegDate),
        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentRegTime),
        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentUser),
    );

    $rs->Insert('FinePayment',$a_Payment);

}

echo "DONE";