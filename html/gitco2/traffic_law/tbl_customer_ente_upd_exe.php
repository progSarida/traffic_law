<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
header('Content-type: text/html;charset=utf-8');
$CityId = CheckValue('CityId','s');
$steps = CheckValue('steps','s');
$check = CheckValue('check','s');

$rs->SetCharset('utf8');
if ($steps == "1") {
    $a_Customer = array(
        array('field'=>'ManagerName','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerName']),
        array('field'=>'ManagerSector','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerSector']),
        array('field'=>'ManagerProvince','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerProvince']),
        array('field'=>'ManagerAddress','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerAddress']),
        array('field'=>'ManagerZIP','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerZIP']),
        array('field'=>'ManagerPhone','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerPhone']),
        array('field'=>'ManagerFax','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerFax']),
        array('field'=>'ManagerMail','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerMail']),
        array('field'=>'ManagerPEC','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerPEC']),
        array('field'=>'ManagerInfo','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerInfo']),
        array('field'=>'ManagerProcessName','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerProcessName']),
        array('field'=>'ManagerDataEntryName','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerDataEntryName']),
        array('field'=>'ManagerSignName','selector'=>'value','type'=>'str', 'value'=>$_REQUEST['ManagerSignName']),
    );
    $rs->Update('Customer',$a_Customer,"CityId='".$CityId."'");
    header("location: tbl_customer_ente_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
}
if ($steps == "3") {
    $a_Customer = array(
        array('field'=>'NationalBankOwner','selector'=>'field','type'=>'str'),
        array('field'=>'NationalBankName','selector'=>'field','type'=>'str'),
        array('field'=>'NationalBankAccount','selector'=>'field','type'=>'str'),
        array('field'=>'NationalBankIban','selector'=>'field','type'=>'str'),
        array('field'=>'NationalBankSwift','selector'=>'field','type'=>'str'),
        array('field'=>'NationalBankMgmt','selector'=>'chkbox','type'=>'int'),
        array('field'=>'ForeignBankOwner','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignBankName','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignBankAccount','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignBankIban','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignBankSwift','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignBankMgmt','selector'=>'chkbox','type'=>'int'),
    );
    $rs->Update('Customer',$a_Customer,"CityId='".$CityId."'");
    header("location: tbl_customer_ente_upd.php?Id=" . $CityId . "&tab=" . $steps . "");

}
if ($steps == "2") {
    if (isset($_REQUEST['ChiefControllerList'])) {
        $ChiefControllerList = 1;
    } else {
        $ChiefControllerList = 0;
    }
    $a_Customer = array(
        array('field' => 'LumpSum', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'ExternalRegistration', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'NationalAnticipateCost', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'ForeignAnticipateCost', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'DigitalSignature', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'PDFRefPrint', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'CityUnion', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'FinePaymentSpecificationType', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'FinePDFList', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'ChiefControllerList', 'selector' => 'chkbox', 'type' => 'int'),
        array('field' => 'MCTCUserName', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'MCTCPassword', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'ReturnPlace', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Reference', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'FifthField', 'selector' => 'value', 'type' => 'int', 'value' => (int)$_REQUEST['FifthField']),
        array('field' => 'ChiefControllerList', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefControllerList),
    );
    $rs->Update('Customer', $a_Customer, "CityId='" . $CityId . "'");
    header("location: tbl_customer_ente_upd.php?Id=" . $CityId . "&tab=" . $steps . "");

}
//if ($steps == "3") {
//    $a_Customer = array(
//        array('field'=>'NationalBankOwner','selector'=>'field','type'=>'str'),
//        array('field'=>'NationalBankName','selector'=>'field','type'=>'str'),
//        array('field'=>'NationalBankAccount','selector'=>'field','type'=>'str'),
//        array('field'=>'NationalBankIban','selector'=>'field','type'=>'str'),
//        array('field'=>'NationalBankSwift','selector'=>'field','type'=>'str'),
//        array('field'=>'NationalBankMgmt','selector'=>'chkbox','type'=>'int'),
//        array('field'=>'ForeignBankOwner','selector'=>'field','type'=>'str'),
//        array('field'=>'ForeignBankName','selector'=>'field','type'=>'str'),
//        array('field'=>'ForeignBankAccount','selector'=>'field','type'=>'str'),
//        array('field'=>'ForeignBankIban','selector'=>'field','type'=>'str'),
//        array('field'=>'ForeignBankSwift','selector'=>'field','type'=>'str'),
//        array('field'=>'ForeignBankMgmt','selector'=>'chkbox','type'=>'int'),
//    );
//    $rs->Update('Customer',$a_Customer,"CityId='".$CityId."'");
//    header("location: tbl_customer_ente_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
//
//}

