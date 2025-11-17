<?php


$a_TrespasserType = array("","Proprietario/Trasgressore","Obbligato","Trasgressore");
$a_TrespasserType[10]= "Ditta noleggio/Leasing";
$a_TrespasserType[11]= "Locatario";
$a_TrespasserType[12]= "Conducente";
$a_TrespasserType[15]= "Patria potestà Proprietario/Obbligato";
$a_TrespasserType[16]= "Patria potestà Trasgressore";


$str_Trespasser = '';





$rs_FineTrespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id, "TrespasserTypeId");

while($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){

    $license = $r_FineTrespasser['LicenseDate'];
    $TrespasserId = $r_FineTrespasser['TrespasserId'];
    $TrespasserTyepId = $r_FineTrespasser['TrespasserTypeId'];
    //$finenotificationdate = $r_FineTrespasser['FineNotificationDate']; la data di notifica è già mostrata sul tab Notifica
    $receivedate = DateOutDB($r_FineTrespasser['ReceiveDate']);
    $rs_Trespasser = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);

    $str_Trespasser .= DivTrespasserView($r_Trespasser, $a_TrespasserType[$TrespasserTyepId],'',$receivedate);

}


$str_Trespasser_Data = '
<div class="tab-pane" id="Trespasser">            
    <div class="col-sm-12">
        '.$str_Trespasser.'
    </div>
</div>
';