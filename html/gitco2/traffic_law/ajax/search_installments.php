<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$Search_FineId = CheckValue('Search_FineId','n');

$rs_Installments = $rs->Select("PaymentRate","FineId = $Search_FineId","Id, StatusRateId ASC");

$a_InstallmentList = array();
while($r_Installment = mysqli_fetch_array($rs_Installments)){
    $r_DocumentType = $rs->getArrayLine($rs->Select("Document_Type","Id = ".$r_Installment['DocumentTypeId']));
    array_push($a_InstallmentList,array(
        "InstallmentId" => $r_Installment['Id'],
        "RateName" => $r_Installment['RateName'],
        "Position" => $r_Installment['Position'],
        "RegDate" => DateOutDB($r_Installment['RegDate']),
        "TrespasserId" => $r_Installment['TrespasserId'],
        "InstallmentType" => $r_DocumentType['Description'],
        "RequestStatusId" => $r_Installment['RequestStatusId'],
        "ResponseStatusId" => $r_Installment['ResponseStatusId'],
        "Status" => (($r_Installment['StatusRateId'] == RATEIZZAZIONE_APERTA) ?  "Rat. aperta": "Rat. chiusa")));
}


echo json_encode(
    $a_InstallmentList
);