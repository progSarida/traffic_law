<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$result = "";
$ReceiptNumber = "";
$StartNumber = "";
$EndNumber = "";

$tipoAttoWhere = $ActType!= null ? " AND TipoAtto=".$ActType : "";
$controllerIdWhere = $ControllerId!= null ?
" AND (ControllerId=".$ControllerId. " OR ControllerId=0)"
    : "AND ControllerId=0" ;
    $rs_Receipt = $rs->Select("Receipt", "CityId='".$_SESSION['cityid']."' AND Session_Year= ".$_SESSION['year'].$tipoAttoWhere.$controllerIdWhere );

//$rs_Receipt = $rs->Select("Receipt", "TipoAtto='".$ActType."' AND ControllerId=".$ControllerId." AND CityId='".$_SESSION['cityid']."' AND Session_Year= ".$_SESSION['year']);
$r_Receipt = mysqli_fetch_array($rs_Receipt);

if(mysqli_num_rows($rs_Receipt)>0){
    $ReceiptNumber = $r_Receipt['Numero_blocco'];
    $ReceiptPrefix = $r_Receipt['Preffix'];
    $StartNumber = $r_Receipt['StartNumber'];
    $EndNumber = $r_Receipt['EndNumber'];
    $result = "OK";
}
else
    $result = "NO";

echo json_encode(
    array(
        "Result" => $result,
        "ReceiptNumber" => $ReceiptNumber,
        "Prefix" => $ReceiptPrefix,
        "StartNumber" => $StartNumber,
        "EndNumber" => $EndNumber,
    )
    );
