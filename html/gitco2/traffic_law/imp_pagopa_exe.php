<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

ini_set('max_execution_time', 3000);



$rs = new CLS_DB(new cls_db_gestoreErroriRaccogli());
$rs->SetCharset('utf8');

/*
alias               30                      Valore fisso ricevuto in input durante la fese di richiesta.
codTrans            2 a 30                  Codice transazione ricevuto in input durante la fese di richiesta.
importo             25.73 -> 2573           Importo complessivo
mac
esito               2 (“OK” o “KO”)         Esito transazione
divisa              3 caratteri (“EUR”)     Divisa
data                yyyymmdd                Data della transazione
orario              hhmmsss                 Orario della transazione
email               1 a 150                 Indirizzo e-mail del pagatore
cognome             1 a 30                  Cognome del pagatore
nome                1 a 30                  Nome del pagatore
IUV                                         Identificativo univoco del pagamento di PagoPA
uidriscossione                              Identificativo univoco del pagamento per il PSP
ParametriAggiuntivi                         Eventuali parametri aggiuntivi



//https://nodopagamenti-test.regione.liguria.it/portale/nodopagamenti/rest/pagamentodiretto/inviarichiesta?iuv=002600000214792

CARTA NOT ON US
4349 9401 9999 6934
03/2022
867
OK
*/

$str_Minkiam ="";
foreach ($_POST as $key => $value) $str_Minkiam.= "Key: $key Val: $value ";
file_put_contents("test.txt",$str_Minkiam);

$Result = CheckValue('esito','s');
$Name = CheckValue('cognome','s');
$Name .= " ". CheckValue('nome','s');
$IUV = CheckValue('IUV','s');
$Date = CheckValue('data','s');
$Time = CheckValue('orario','s');
$Amount =CheckValue('importo','s');

$Code = CheckValue('codTrans','s');
$Currency = CheckValue('divisa','s');
$Email = CheckValue('email','s');
$Note = CheckValue('ParametriAggiuntivi','s');
$Id=  CheckValue('uidriscossione','s');
$FifthField = strlen($Id) == 16 ? $Id : null;

/*
$Result = "OK";
$Date = "20190809";
$Time = "120210";
$IUV = "002600000214590";
$Amount = "5723";
$Currency = "EUR";
$Email = "pagatore@email.it";
$Name = "CENERE GIORGIO";
$Id = '192210000161';
*/


if($Result=="OK"){

    $PaymentDate = substr($Date,0,4)."-".substr($Date,4,2)."-".substr($Date,6,2);
    $PaymentTime = substr($Time,0,2).":".substr($Time,2,2).":".substr($Time,4,2);
    $Amount = substr($Amount,0,(strlen($Amount)-2)).".".substr($Amount,(strlen($Amount)-2),2);
    $Causal = $Code." ".$Email." ".$Id. " ".$Currency." ".$Amount." ".$PaymentDate." ".$PaymentTime." ".$Note;

    $rs_Fine = $rs->Select('Fine',"PagoPA1='". $IUV ."' OR PagoPA2='". $IUV ."'");
    $r_Fine = mysqli_fetch_array($rs_Fine);
    
    $ProtocolYear = $r_Fine['ProtocolYear'];

    $rs_customer = $rs->Select('Customer', "CityId='" . $r_Fine['CityId'] . "'");
    $r_customer = mysqli_fetch_array($rs_customer);


    $FinePaymentSpecificationType = $r_customer['FinePaymentSpecificationType'];
    $BankMgmt = ($r_Fine['CountryId']=='Z000') ? $r_customer['NationalBankMgmt'] : $r_customer['ForeignBankMgmt'];

    $PaymentTypeId = 9;
    $ImportationId = 1;
    $TableId=1;


    $a_Payment = array(
        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['Id'], 'settype' => 'int'),
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $r_Fine['CityId']),
        array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
        array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => trim($Name)),
        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
        array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => trim($Causal)),
        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => 'UBYSRV'),
        array('field' => 'FifthField', 'selector' => 'value', 'type' => 'str', 'value' => $FifthField),
        array('field' => 'ImportReceiptNumber', 'selector' => 'value', 'type' => 'str', 'value' => $Id),
    );

    $a_Fee = separatePayment($FinePaymentSpecificationType, 0, false, $Amount, $r_Fine['Id'], $r_Fine['CityId'], $ProtocolYear, $PaymentDate. $r_Fine['ReminderDate']);

    $a_Payment[]= array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$a_Fee['Fee'],'settype'=>'flt');
    $a_Payment[]= array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['ResearchFee'],'settype'=>'flt');
    $a_Payment[]= array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['NotificationFee'],'settype'=>'flt');
    $a_Payment[] = array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt');
    $a_Payment[]= array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CustomerFee'],'settype'=>'flt');
    $a_Payment[]= array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CanFee'],'settype'=>'flt');
    $a_Payment[]= array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CadFee'],'settype'=>'flt');

    $a = $rs->Insert('FinePayment', $a_Payment);
    
    if(count($rs->gestoreErrori->getErrori()) > 0)
        {
            $a_Insert = array(
                array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => "Pagamenti PagoPA"),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $r_Fine['CityId']),
                array('field' => 'Message', 'selector' => 'value', 'type' => 'str', 'value' => "Errore salvataggio importazione pagamento Liguria"),
                array('field' => 'Severity', 'selector' => 'value', 'type' => 'str', 'value' => "ERROR"),
                array('field' => 'EventDate', 'selector' => 'value', 'type' => 'str', 'value' => date('Y-m-d H:i:s')),
            );
            
            $rs->Insert('Events', $a_Insert);
        }
}








