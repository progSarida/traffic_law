<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
include(INC."/function_postalCharge.php");
include(INC."/pagopa.php");

ini_set('max_execution_time', 3000);

$rs= new CLS_DB();

$FineId = CheckValue('FineId','n');  

$str_Warning = '';

$pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
$pagopaService=$rs->getArrayLine($pagopaServicequery);

$r_ProcessingPagoPA=$rs->getArrayLine($rs->Select("V_ViolationPagoPA"," Id= ".$FineId));
$NotificationDate = date("Y-m-d");
$ZoneId = 0;

$a_Failed = array();

if($r_ProcessingPagoPA){    
    $chk_ReducedPayment = false;
    $rs_AdditionalArticle=null;
    
    if ($r_ProcessingPagoPA['ArticleNumber'] > 1) {
        $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $FineId, "ArticleOrder");
        //TODO NON USATO
//         while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle))
//             if ($r_AdditionalArticle['ReducedPayment'] == 1)
//                 $chk_ReducedPayment = true;
    }
    
    $trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
    $trespasser = mysqli_fetch_assoc($trespassers);
    $str_TrespasserType = ($trespasser['Genre'] == "D") ? "G" : "F";
    $str_TrespasserTaxCode = trim(PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode'])) ?: "ANONIMO";
    $str_Fine = 'Anno ' . $r_ProcessingPagoPA['ProtocolYear'] . ' targa ' . $r_ProcessingPagoPA['VehiclePlate'];
    $fineDate = $r_ProcessingPagoPA['FineDate'];
    
    //TODO CAPIRE COME VENIVANO USATI
//     $tipoContabilità='1';
//     $codiceContabilità='2';
//     $tipoDovuto='ALTRO';
    
    //$GenreParemeter D è per ditta/impresa e P per privato
    $GenreParemeter = ($trespasser['Genre'] == "D")? "D" : "P";
    //Kind='S' è tipologia sanzione
    $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
    $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
    
//     if(mysqli_num_rows($rs_PagoPAServiceParameter)>0) {
//         $r_PagoPAServiceParameter = mysqli_fetch_array($rs_PagoPAServiceParameter);
//         $tipoContabilità = $r_PagoPAServiceParameter['Type'];
//         $codiceContabilità = $r_PagoPAServiceParameter['Code'];
//         $tipoDovuto = $r_PagoPAServiceParameter['TypeDue'];
//     }
    
    $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_ProcessingPagoPA['ArticleId'] . " AND Year=" . $r_ProcessingPagoPA['ProtocolYear']);
    $r_ArticleTariff = $rs->getArrayLine($rs_ArticleTariff);
    
    if ($r_ArticleTariff['ReducedPayment'] == 1){
        $chk_ReducedPayment = true;
    }
    
    $a_Importi = calcolaImporti($r_ProcessingPagoPA, $rs_AdditionalArticle, $r_ArticleTariff, $r_Customer, $NotificationDate, $ZoneId, $trespasser['PEC']);
    
    $PagoPACode1 = $r_ProcessingPagoPA['PagoPA1'];
    $PagoPACode2 = $r_ProcessingPagoPA['PagoPA2'];
    $b_PagoPAFail1 = $b_PagoPAFail2 = false;
    $a_FineUpd = array();
    
    if($chk_ReducedPayment){
        $fullFeeUpd = 'ReducedTotal'; //sanizione minima
        $partialFeeUpd = 'ReducedPartial'; //sanzione ridotta
    }else{
        $fullFeeUpd = 'Total'; //metà del massimo
        $partialFeeUpd = 'Partial'; //sanzione minima
    }
    
    if(!empty($PagoPACode1)){
        if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $partialFeeUpd, $PagoPACode1, $FineId, $fineDate, $str_TrespasserType, $trespasser, $str_TrespasserTaxCode, $str_Fine, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
            $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedPartial'] ,'settype'=>'flt');
            $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Partial'] ,'settype'=>'flt');
        } else $b_PagoPAFail1 = true;
    }
    
    if(!empty($PagoPACode2)){
        if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $fullFeeUpd, $PagoPACode2, $FineId, $fineDate, $str_TrespasserType, $trespasser, $str_TrespasserTaxCode, $str_Fine, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
            $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedTotal'] ,'settype'=>'flt');
            $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
        } else $b_PagoPAFail2 = true;
    }
    
    if($b_PagoPAFail1 || $b_PagoPAFail2){
        $a_Failed[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito per uno o più IUV.';
    }
    
    if(!empty($a_FineUpd)) {
        $a_FinePagoPAHistory = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'ReminderId','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
            array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1),
            array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2),
            array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPAReducedPartial'] ,'settype'=>'flt'),
            array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPAPartial'] ,'settype'=>'flt'),
            array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPAReducedTotal'] ,'settype'=>'flt'),
            array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPATotal'] ,'settype'=>'flt'),
        );
        
        $rs->Insert("FinePagoPAHistory", $a_FinePagoPAHistory);
        $rs->Update("Fine", $a_FineUpd, "Id=".$FineId);
    }
}

foreach($a_Failed as $failMessage){
    trigger_error($failMessage, E_USER_WARNING);
    $str_Warning .= $failMessage.'<br>';
}

if($str_Warning != ''){
    $_SESSION['Message']['Warning'] = $str_Warning;
} else {
    $_SESSION['Message']['Success'] = 'Aggiornamento dei codici PagoPA riuscito.';
}

header("location: mgmt_pagopa.php".$str_GET_Parameter);
?>