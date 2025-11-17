<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

require(INC."/initialization.php");

$str_Where = "CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];


$ProtocolId     = CheckValue('ProtocolId','n');
$PaymentType    = CheckValue('PaymentType','s');


$str_Where .= " AND ProtocolId=".$ProtocolId. " AND PagoPA1 IS NOT NULL AND PagoPA2 IS NOT NULL AND ProtocolId>0" ;

$rs_Fine = $rs->Select('Fine',$str_Where);




$RowNumber = mysqli_num_rows($rs_Fine);

if($RowNumber>0){
    $r_Fine = mysqli_fetch_array($rs_Fine);
    $PagoPa = $r_Fine['PagoPA'.$PaymentType];
    $str_Iuv = "iuv=".$PagoPa;


    $url = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/servlet/avvisopagamento";

    $ch = curl_init( $url );

    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $str_Iuv);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec( $ch );


    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename="'. $PagoPa .'"');
    echo $response;

} else {
    $_SESSION['Message'] = "Iuv non trovato";
    header("Location: prn_pagopa.php?ProtocolId=". $ProtocolId ."&PaymentType".$PaymentType);
}

