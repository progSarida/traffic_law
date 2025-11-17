<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/views.php");
require_once(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$ActiveTab = CheckValue('ActiveTab', 'n');

$CityId = $_SESSION['cityid'];

$a_NationalFineFoldReturn = CheckValue('NationalFineFoldReturn', 'a');
$a_NationalMod23LCustomerSubject = CheckValue('NationalMod23LCustomerSubject', 'a');
$a_NationalMod23LCustomerAddress = CheckValue('NationalMod23LCustomerAddress', 'a');
$a_NationalMod23LCustomerCity = CheckValue('NationalMod23LCustomerCity', 'a');
$a_NationalSmaName = CheckValue('NationalSmaName', 'a');
$a_NationalSmaAuthorization = CheckValue('NationalSmaAuthorization', 'a');
$a_NationalSmaPayment = CheckValue('NationalSmaPayment', 'a');
$a_NationalPostalAuthorization = CheckValue('NationalPostalAuthorization', 'a');
$a_NationalPostalAuthorizationPagoPA = CheckValue('NationalPostalAuthorizationPagoPA', 'a');

$a_ForeignFineFoldReturn = CheckValue('ForeignFineFoldReturn', 'a');
$a_ForeignMod23LCustomerSubject = CheckValue('ForeignMod23LCustomerSubject', 'a');
$a_ForeignMod23LCustomerAddress = CheckValue('ForeignMod23LCustomerAddress', 'a');
$a_ForeignMod23LCustomerCity = CheckValue('ForeignMod23LCustomerCity', 'a');
$a_ForeignSmaName = CheckValue('ForeignSmaName', 'a');
$a_ForeignSmaAuthorization = CheckValue('ForeignSmaAuthorization', 'a');
$a_ForeignSmaPayment = CheckValue('ForeignSmaPayment', 'a');
$a_ForeignPostalAuthorization = CheckValue('ForeignPostalAuthorization', 'a');
$a_ForeignPostalAuthorizationPagoPA = CheckValue('ForeignPostalAuthorizationPagoPA', 'a');

$a_NationalReminderFoldReturn = CheckValue('NationalReminderFoldReturn', 'a');
$a_NationalReminderSmaName = CheckValue('NationalReminderSmaName', 'a');
$a_NationalReminderSmaAuthorization = CheckValue('NationalReminderSmaAuthorization', 'a');
$a_NationalReminderSmaPayment = CheckValue('NationalReminderSmaPayment', 'a');
$a_NationalReminderPostalAuthorization = CheckValue('NationalReminderPostalAuthorization', 'a');
$a_NationalReminderPostalAuthorizationPagoPA = CheckValue('NationalReminderPostalAuthorizationPagoPA', 'a');

$a_ForeignReminderFoldReturn = CheckValue('ForeignReminderFoldReturn', 'a');
$a_ForeignReminderSmaName = CheckValue('ForeignReminderSmaName', 'a');
$a_ForeignReminderSmaAuthorization = CheckValue('ForeignReminderSmaAuthorization', 'a');
$a_ForeignReminderSmaPayment = CheckValue('ForeignReminderSmaPayment', 'a');
$a_ForeignReminderPostalAuthorization = CheckValue('ForeignReminderPostalAuthorization', 'a');
$a_ForeignReminderPostalAuthorizationPagoPA = CheckValue('ForeignReminderPostalAuthorizationPagoPA', 'a');

$cls_view = new CLS_VIEW(MGMT_PRINTERPARAMETER);
$rs_Table = $rs->SelectQuery($cls_view->generateSelect());
$a_PrinterParameter = $rs->getResults($rs_Table);

foreach($a_PrinterParameter as $data){
    $a_InsertUpdate = array(
        array('field' => 'PrinterId','selector' => 'value','type' => 'int', 'settype' => 'int',
            'value' => $data['PrinterId']),
        array('field' => 'CityId','selector' => 'value','type' => 'str', 'settype' => 'str',
            'value' => $CityId),
        array('field' => 'NationalFineFoldReturn','selector' => 'value','type' => 'str',
            'value' => $a_NationalFineFoldReturn[$data['PrinterId']] ?? ''),
        array('field' => 'NationalMod23LCustomerSubject','selector' => 'value','type' => 'str',
            'value' => $a_NationalMod23LCustomerSubject[$data['PrinterId']] ?? ''),
        array('field' => 'NationalMod23LCustomerAddress','selector' => 'value','type' => 'str',
            'value' => $a_NationalMod23LCustomerAddress[$data['PrinterId']] ?? ''),
        array('field' => 'NationalMod23LCustomerCity','selector' => 'value','type' => 'str',
            'value' => $a_NationalMod23LCustomerCity[$data['PrinterId']] ?? ''),
        array('field' => 'NationalSmaName','selector' => 'value','type' => 'str',
            'value' => $a_NationalSmaName[$data['PrinterId']] ?? ''),
        array('field' => 'NationalSmaAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_NationalSmaAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'NationalSmaPayment','selector' => 'value','type' => 'str',
            'value' => $a_NationalSmaPayment[$data['PrinterId']] ?? ''),
        array('field' => 'NationalPostalAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_NationalPostalAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'NationalPostalAuthorizationPagoPA','selector' => 'value','type' => 'str',
            'value' => $a_NationalPostalAuthorizationPagoPA[$data['PrinterId']] ?? ''),
        
        array('field' => 'ForeignFineFoldReturn','selector' => 'value','type' => 'str',
            'value' => $a_ForeignFineFoldReturn[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignMod23LCustomerSubject','selector' => 'value','type' => 'str',
            'value' => $a_ForeignMod23LCustomerSubject[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignMod23LCustomerAddress','selector' => 'value','type' => 'str',
            'value' => $a_ForeignMod23LCustomerAddress[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignMod23LCustomerCity','selector' => 'value','type' => 'str',
            'value' => $a_ForeignMod23LCustomerCity[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignSmaName','selector' => 'value','type' => 'str',
            'value' => $a_ForeignSmaName[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignSmaAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_ForeignSmaAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignSmaPayment','selector' => 'value','type' => 'str',
            'value' => $a_ForeignSmaPayment[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignPostalAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_ForeignPostalAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignPostalAuthorizationPagoPA','selector' => 'value','type' => 'str',
            'value' => $a_ForeignPostalAuthorizationPagoPA[$data['PrinterId']] ?? ''),
        
        array('field' => 'NationalReminderFoldReturn','selector' => 'value','type' => 'str',
            'value' => $a_NationalReminderFoldReturn[$data['PrinterId']] ?? ''),
        array('field' => 'NationalReminderSmaName','selector' => 'value','type' => 'str',
            'value' => $a_NationalReminderSmaName[$data['PrinterId']] ?? ''),
        array('field' => 'NationalReminderSmaAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_NationalReminderSmaAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'NationalReminderSmaPayment','selector' => 'value','type' => 'str',
            'value' => $a_NationalReminderSmaPayment[$data['PrinterId']] ?? ''),
        array('field' => 'NationalReminderPostalAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_NationalReminderPostalAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'NationalReminderPostalAuthorizationPagoPA','selector' => 'value','type' => 'str',
            'value' => $a_NationalReminderPostalAuthorizationPagoPA[$data['PrinterId']] ?? ''),
        
        array('field' => 'ForeignReminderFoldReturn','selector' => 'value','type' => 'str',
            'value' => $a_ForeignReminderFoldReturn[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignReminderSmaName','selector' => 'value','type' => 'str',
            'value' => $a_ForeignReminderSmaName[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignReminderSmaAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_ForeignReminderSmaAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignReminderSmaPayment','selector' => 'value','type' => 'str',
            'value' => $a_ForeignReminderSmaPayment[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignReminderPostalAuthorization','selector' => 'value','type' => 'str',
            'value' => $a_ForeignReminderPostalAuthorization[$data['PrinterId']] ?? ''),
        array('field' => 'ForeignReminderPostalAuthorizationPagoPA','selector' => 'value','type' => 'str',
            'value' => $a_ForeignReminderPostalAuthorizationPagoPA[$data['PrinterId']] ?? ''),
    );
    
    $rs->InsertOrUpdateIfExist('PrinterParameter', $a_InsertUpdate);
}

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';

header("location: ".impostaParametriUrl(array('ActiveTab' => $ActiveTab), "mgmt_printerparameter_upd.php".$Filters));
