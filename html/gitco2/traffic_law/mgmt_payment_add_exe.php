<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters','s');

$FineId = CheckValue('Search_FineId','n');

$PaymentTypeId = CheckValue('PaymentTypeId','n');
$PaymentProcedure = CheckValue('PaymentProcedure','n');

$ImportationId = 0;

$Name = strtoupper(CheckValue('Name','s'));

$PaymentDate = DateInDB(CheckValue('PaymentDate','s'));
$CreditDate = DateInDB(CheckValue('CreditDate','s'));
$FifthField = CheckValue('FifthField','s');
if($FifthField>16)$FifthField=substr($FifthField,0,16);

$TableId = CheckValue('TableId','n');
$PaymentFee = CheckValue('PaymentFee','n');

$BankMgmt = strtoupper(CheckValue('BankMgmt','n'));

$InstallmentId = CheckValue('InstallmentList','n');

$rs->Start_Transaction();

$a_Payment = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$Name),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'BankMgmt','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'PaymentTypeId','selector'=>'value','type'=>'int','value'=>$PaymentTypeId,'settype'=>'int'),
    array('field'=>'PaymentDocumentId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ImportationId','selector'=>'value','type'=>'int','value'=>$ImportationId,'settype'=>'int'),
    array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$PaymentDate),
    array('field'=>'CreditDate','selector'=>'value','type'=>'date','value'=>$CreditDate),
    array('field'=>'TableId','selector'=>'value','type'=>'int','value'=>$TableId, 'settype'=>'int'),
    array('field'=>'PaymentFee','selector'=>'value','type'=>'int','value'=>$PaymentFee, 'settype'=>'int'),
    array('field'=>'Amount','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Fee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CanFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CadFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CustomerFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'PercentualFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'OfficeNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'DocumentType','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'FifthField','selector'=>'value','type'=>'str','value'=>$FifthField),
    array('field'=>'Note','selector'=>'field','type'=>'str'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
);

if($InstallmentId != 0)
    array_push($a_Payment,array('field'=>'InstallmentId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$InstallmentId));

$n_FinePayment = $rs->Insert('FinePayment',$a_Payment);

$rs_Fine = $rs->Select('Fine', "Id=".$FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);
$FineTypeId     = $r_Fine['FineTypeId'];
$StatusTypeId   = $r_Fine['StatusTypeId'];

//se è un preavviso come tipo e stato basta un pagamento indipendentemente dalla cifra per segnarlo come pagato?
if($FineTypeId==2 && $StatusTypeId=13){
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>30),
    );
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
}
else{   //Quando viene inserito un pagamento viene cambiato lo status del verbale in base a tutti i pagamenti che gli sono associati
    $classePagamenti = new cls_pagamenti($FineId, $_SESSION['cityid']);
    
    $StatusVerbale = 0;
    //trigger_error("***Dovuto: ".($classePagamenti->getFee())." Pagato: ".($classePagamenti->getPayed())." Status: ".($classePagamenti->getStatus()));
    switch($classePagamenti->getStatus()){
        case 0: $StatusVerbale = 27; break;      //Non pagato
        case 1:                                  //Pagato parziale
        case 2: $StatusVerbale = 28; break;      //Pagato parziale in ritardo
        case 3:                                  //Pagato
        case 4: $StatusVerbale = 30; break;      //Pagato in eccesso
        default: $StatusVerbale = $StatusTypeId; //TODO In caso di ricorso viene tenuto lo status. Valutare cosa fare
        }
        
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusVerbale,'settype'=>'int'),
        );
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
}











if($PaymentProcedure==0 AND $FineId>0){
    $rs_PaymentProcedure = $rs->Select('FineNotification', "FineId=" .$FineId);
    if (mysqli_num_rows($rs_PaymentProcedure) > 0) {
        $a_FineNotification = array(
            array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
        );

        $rs->Update('FineNotification',$a_FineNotification, "FineId=".$FineId);
    } else{

        $rs_PaymentProcedure = $rs->Select('TMP_PaymentProcedure', "FineId=" .$FineId);

        if (mysqli_num_rows($rs_PaymentProcedure) == 0) {
            $a_TMP_PaymentProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>$PaymentProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_PaymentProcedure',$a_TMP_PaymentProcedure);
        }
    }

}


if(isset($_FILES['PaymentDocumentation']) && $_FILES['PaymentDocumentation']['error'] == 0) {

    $str_Documentation = basename($_FILES['PaymentDocumentation']['name']);

    if ($FineId > 0) {


        $rs_Fine = $rs->Select("Fine", "Id=" . $FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);

        $str_Folder = ($r_Fine['CountryId'] == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;

        if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
            mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
        }

        if (move_uploaded_file($_FILES['PaymentDocumentation']['tmp_name'], $str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $str_Documentation)) {

            $DocumentationTypeId = 15;
            $a_FineDocumentation = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $str_Documentation),
                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'int', 'value' => $n_FinePayment, 'settype' => 'int'),
                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
            );
            $rs->Insert('FineDocumentation', $a_FineDocumentation);
        }

    } else {
        
        if (!is_dir(PAYMENT_RECLAIM . "/" . $_SESSION['cityid'])) {
            mkdir(PAYMENT_RECLAIM . "/" . $_SESSION['cityid'], 0777);
        }

        $str_Documentation = date("Ymdis") . $str_Documentation;

        if (move_uploaded_file($_FILES['PaymentDocumentation']['tmp_name'], PAYMENT_RECLAIM . "/" . $_SESSION['cityid'] . "/" . $str_Documentation)) {
            $a_FinePayment = array(
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $str_Documentation),
            );
            $rs->Update('FinePayment', $a_FinePayment, "Id=" . $n_FinePayment);
        }


    }
}

$rs->End_Transaction();


if($PaymentTypeId==4 || $PaymentTypeId==17){
    header("location: mgmt_payment_prn_exe.php".$Filters."&FineId=". $FineId ."&Id=".$n_FinePayment."&AddPage=1");
} else {
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_payment.php'), 'mgmt_payment_add.php'.$Filters));
}