<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/views.php");
require_once(INC."/initialization.php");

$ActiveTab = CheckValue('ActiveTab', 'n');
$ActiveTabPrinter = CheckValue('ActiveTabPrinter', 'n');
$Filters = CheckValue('Filters', 's');
$CityId = $_SESSION['cityid'];

$TaxCode = strtoupper(CheckValue('ManagerTaxCode', 's'));
$VAT = CheckValue('ManagerVAT', 's');
$Country = CheckValue('ManagerCountry', 's');
$Web = CheckValue('ManagerWeb', 's');
$PatronalFeastDay = CheckValue('PatronalFeastDay', 'n');
$PatronalFeastMonth = CheckValue('PatronalFeastMonth', 'n');
$INIPECUserName = CheckValue('INIPECUserName', 's');
$INIPECPassword = CheckValue('INIPECPassword', 's');
$MCTCFileInput=CheckValue('MCTCFileInput', 's');
$MCTCFileOk = CheckValue('MCTCFileOk', 's');
$MCTCFileOutput = CheckValue('MCTCFileOutput', 's');
$MCTCFtp = CheckValue('MCTCFtp', 's');
$MCTCName = CheckValue('MCTCName', 's');
$MCTCProvince = CheckValue('MCTCProvince', 's');
$MCTCPosition = CheckValue('MCTCPosition', 'n');

$MCTCCrossBorderUserName = CheckValue('MCTCCrossBorderUserName', 's');
$MCTCCrossBorderPassword = CheckValue('MCTCCrossBorderPassword', 's');

$MCTCMassiveUsername = CheckValue('MCTCMassiveUsername', 's');
$MCTCMassivePassword = CheckValue('MCTCMassivePassword', 's');

if ($PatronalFeastDay != "" && $PatronalFeastMonth != "")
  $PatronalFeast = "0000-" . $PatronalFeastMonth . "-" . $PatronalFeastDay;
else
  $PatronalFeast = NULL;

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

$MCTCUserVPN = CheckValue('MCTCUserVPN','s');
$MCTCPasswordVPN = CheckValue('MCTCPasswordVPN', 's');
$MCTCExpirationDateVPN = CheckValue('MCTCExpirationDateVPN', 's');

$IncomingPassword = CheckValue('IncomingPassword', 's');
$OutgoingPassword = CheckValue('OutgoingPassword', 's');
$MCTCUsername = CheckValue('MCTCUsername', 's');
$MCTCPassword = CheckValue('MCTCPassword', 's');
$MCTCCrossBorderPassword = CheckValue('MCTCCrossBorderPassword', 's');
$LicensePointFtpPassword = CheckValue('LicensePointFtpPassword', 's');

$a_ToReplace = array(
    "@CityId" => $CityId,
);

$cls_view = new CLS_VIEW(TBL_CUSTOMER_PRINTERPARAMETER);
$rs_PrinterParameter = $rs->SelectQuery(strtr($cls_view->generateSelect(), $a_ToReplace));
$a_PrinterParameter = $rs->getResults($rs_PrinterParameter);
$a_PrinterParameter = array_column($a_PrinterParameter, null, 'PrinterId');

$rs->Start_Transaction();

foreach($a_PrinterParameter as $data){
    $a_InsertUpdate = array(
        array('field' => 'PrinterId','selector' => 'value','type' => 'int', 'settype' => 'int',
            'value' => $data['PrinterId']),
        array('field' => 'CityId','selector' => 'value','type' => 'str',
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

$a_CustomerService = array(
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
    array('field'=>'ServiceId','selector'=>'value','type'=>'int','value'=>6,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>$MCTCUserVPN),
    array('field'=>'Password','selector'=>'value','type'=>'str','value'=>$MCTCPasswordVPN),
    array('field'=>'PasswordExpiration','selector'=>'value','type'=>'date','value'=>DateInDB($MCTCExpirationDateVPN)),
);

$rs->InsertOrUpdateIfExist('CustomerService',$a_CustomerService);

$a_City = array(
    array('field' => 'TaxCode','selector' => 'value','type' => 'str','value' => $TaxCode),
    array('field' => 'VAT','selector' => 'value','type' => 'str','value' => $VAT),
    array('field' => 'Country','selector' => 'value','type' => 'str','value' => $Country),
    array('field' => 'Web','selector' => 'value','type' => 'str','value' => $Web),
    array('field' => 'NationalProtocolLetterType1','selector' => 'field','type' => 'str'),  
    array('field' => 'ForeignProtocolLetterType1','selector' => 'field','type' => 'str'),
    array('field' => 'NationalProtocolLetterType2','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignProtocolLetterType2','selector' => 'field','type' => 'str')
);

$rs->Update(MAIN_DB . '.City', $a_City, "Id='" . $CityId . "'");

$a_Customer = array(
    array('field' => 'ManagerName','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerSector','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerCity','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerProvince','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerAddress','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerZIP','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerPhone','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerFax','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerMail','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerPEC','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerInfo','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerOfficeInfo','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerProcessName','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerDataEntryName','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerSignName','selector' => 'field','type' => 'str'),
    array('field' => 'ManagerTaxCode','selector' => 'value','type' => 'str','value' => $TaxCode),
    array('field' => 'ManagerVAT','selector' => 'value','type' => 'str','value' => $VAT),
    array('field' => 'ManagerWeb','selector' => 'value','type' => 'str','value' => $Web),
    array('field' => 'NationalBankOwner','selector' => 'field','type' => 'str'),
    array('field' => 'NationalBankName','selector' => 'field','type' => 'str'),
    array('field' => 'NationalBankAccount','selector' => 'field','type' => 'str'),
    array('field' => 'NationalBankIban','selector' => 'field','type' => 'str'),
    array('field' => 'NationalBankSwift','selector' => 'field','type' => 'str'),
    array('field' => 'NationalBankMgmt','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'ForeignBankOwner','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignBankName','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignBankAccount','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignBankIban','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignBankSwift','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignBankMgmt','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'NationalReminderBankOwner','selector' => 'field','type' => 'str'),
    array('field' => 'NationalReminderBankName','selector' => 'field','type' => 'str'),
    array('field' => 'NationalReminderBankAccount','selector' => 'field','type' => 'str'),
    array('field' => 'NationalReminderBankIban','selector' => 'field','type' => 'str'),
    array('field' => 'NationalReminderBankSwift','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignReminderBankOwner','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignReminderBankName','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignReminderBankAccount','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignReminderBankIban','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignReminderBankSwift','selector' => 'field','type' => 'str'),
    array('field' => 'Reference','selector' => 'field','type' => 'str'),
    //array('field' => 'ReturnPlace','selector' => 'field','type' => 'str'),
    array('field' => 'LumpSum','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'DigitalSignature','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'ExternalRegistration','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    // array('field'=>'FinePaymentSpecificationType','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field' => 'FinePDFList','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'ChiefControllerList','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'Validation','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'FifthField','selector' => 'field','type' => 'str'),
    array('field' => 'RegularPostalFine','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PatronalFeast','selector' => 'value','type' => 'str','value' => $PatronalFeast),
    //array('field' => 'NationalAnticipateCost','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    //array('field' => 'ForeignAnticipateCost','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'NationalMod23LSubject','selector' => 'field','type' => 'str'),
    array('field' => 'NationalMod23LCustomerName','selector' => 'field','type' => 'str'),
//     array('field' => 'NationalMod23LCustomerSubject','selector' => 'field','type' => 'str'),
//     array('field' => 'NationalMod23LCustomerAddress','selector' => 'field','type' => 'str'),
//     array('field' => 'NationalMod23LCustomerCity','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignMod23LSubject','selector' => 'field','type' => 'str'),
    array('field' => 'ForeignMod23LCustomerName','selector' => 'field','type' => 'str'),
//     array('field' => 'ForeignMod23LCustomerSubject','selector' => 'field','type' => 'str'),
//     array('field' => 'ForeignMod23LCustomerAddress','selector' => 'field','type' => 'str'),
//     array('field' => 'ForeignMod23LCustomerCity','selector' => 'field','type' => 'str'),
    array('field' => 'MCTCUserName','selector' => 'field','type' => 'str'),
    array('field' => 'MCTCPassword','selector' => 'field','type' => 'str'),
    array('field' => 'MCTCDate','selector' => 'field','type' => 'date'),
    array('field' => 'LicensePointOffice','selector' => 'field','type' => 'str'),
    array('field' => 'LicensePointCode','selector' => 'field','type' => 'str'),
    array('field' => 'LicensePointFtpUser','selector' => 'field','type' => 'str'),
    array('field' => 'LicensePointFtpPassword','selector' => 'field','type' => 'str'),
    array('field' => 'LicensePointFtpServer','selector' => 'field','type' => 'str'),
    array('field' => 'LicensePointFtpPasswordExpiration','selector' => 'field','type' => 'date'),
    array('field' => 'PagoPAPayment','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPAPaymentForeign','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPAPaymentNoticeNational','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPAPaymentNoticeNationalPEC','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPAPaymentNoticeForeign','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPACPPOwner','selector' => 'field','type' => 'str'),  
    array('field' => 'PagoPAPaymentSubject','selector' => 'field','type' => 'str'),
    array('field' => 'PagoPACBILL','selector' => 'field','type' => 'str'),
    array('field' => 'PagoPAPaymentInfo','selector' => 'field','type' => 'str'),
    //array('field' => 'FineNonDeliveryAddress','selector' => 'field','type' => 'str'),
    //array('field' => 'ReminderNonDeliveryAddress','selector' => 'field','type' => 'str'),
    //array('field' => 'FineNonDeliveryAddressForeign','selector' => 'field','type' => 'str'),
    //array('field' => 'ReminderNonDeliveryAddressForeign','selector' => 'field','type' => 'str'),
    array('field' => 'IpaCode','selector' => 'field','type' => 'str'),
    array('field' => 'EnableSignAll','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'EnableINIPECDigitalSignature','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    // array('field'=>'EnableINIPECNotificationSignature','selector'=>'chkbox','type'=>'int','settype'=>'int'),//TODO colonna non usata. EnableINIPECNotification si occupa sia di notifica e firma di essa
    array('field' => 'EnableINIPECNotification','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'EnableINIPECProtocol','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'ManagePEC','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'NationalPrinter','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'ForeignPrinter','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'NationalPrinterReminder','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'ForeignPrinterReminder','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'PrefectCommunicationSigner','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'InstallmentMethod','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'ApplyInstallmentRates','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'InstallmentRatesMinimumAmount','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentYearlyIncomeLimit','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentAdditionalIncomePerFamilyMember','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentMinimumFeeLimit','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentFeeLimit1','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentRateLimit1','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'InstallmentFeeLimit2','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentRateLimit2','selector' => 'field','type' => 'int','settype' => 'int'),
    //array('field' => 'InstallmentFeeLimit3','selector' => 'field','type' => 'flt','settype' => 'flt'),
    array('field' => 'InstallmentRateLimit3','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'InstallmentFreeRateLimit','selector' => 'field','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPAPaymentNoticeNationalInstallment','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'PagoPAPaymentNoticeForeignInstallment','selector' => 'chkbox','type' => 'int','settype' => 'int'),
    array('field' => 'InstallmentControllerApproval','selector' => 'chkbox','type' => 'int','settype' => 'int'),
);

$rs->Update('Customer', $a_Customer, "CityId='" . $CityId . "'");

// $a_CustomerCharge = array(
//     array('field'=>'NationalPostalAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalPostalAuthorizationPagoPA','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalReminderPostalAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalReminderPostalAuthorizationPagoPA','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalSmaName','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalSmaAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalSmaPayment','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalReminderSmaName','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalReminderSmaAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'NationalReminderSmaPayment','selector'=>'field','type'=>'str'),
    
//     array('field'=>'ForeignPostalAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignPostalAuthorizationPagoPA','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignReminderPostalAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignReminderPostalAuthorizationPagoPA','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignSmaName','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignSmaAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignSmaPayment','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignReminderSmaName','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignReminderSmaAuthorization','selector'=>'field','type'=>'str'),
//     array('field'=>'ForeignReminderSmaPayment','selector'=>'field','type'=>'str'),
// );

// $rs->Update('CustomerCharge', $a_CustomerCharge, "CityId='" . $CityId . "'");

// Utente Password Verbali INI-PEC
$a_IniPecProcessingInsertUpdate = array(
  array('field' => 'UserName','selector' => 'value','type' => 'str','value' => $INIPECUserName),
  array('field' => 'Password','selector' => 'value','type' => 'str','value' => $INIPECPassword),
  array('field' => 'INIPECPasswordExpiration','selector' => 'field','type' => 'date')
);

$rs_IniPecProcessing = $rs->Select('IniPecProcessing', "CityId='" . $CityId . "'");
if (mysqli_num_rows($rs_IniPecProcessing) == 0)
  {
  $a_IniPecProcessingInsertUpdate[] = array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $CityId);
  $rs->Insert('IniPecProcessing', $a_IniPecProcessingInsertUpdate);
  }
else
  {
  $rs->Update('IniPecProcessing', $a_IniPecProcessingInsertUpdate, "CityId='" . $CityId . "'");
  }

  // Parametri autenticazione email
$a_CustomerMailAuthentication = array(
  array('field' => 'MailAddress','selector' => 'field','type' => 'str'),
  array('field' => 'ShownName','selector' => 'field','type' => 'str'),
  array('field' => 'IncomingUserName','selector' => 'field','type' => 'str'),
  array('field' => 'IncomingPassword','selector' => 'field','type' => 'str'),
  array('field' => 'IncomingMailbox','selector' => 'field','type' => 'str'),
  array('field' => 'OutgoingUserName','selector' => 'field','type' => 'str'),
  array('field' => 'OutgoingPassword','selector' => 'field','type' => 'str'),
  array('field' => 'UseOutgoingAuthentication','selector' => 'chkbox','type' => 'int','settype' => 'int'),
  array('field' => 'ReplyToManagerPEC','selector' => 'chkbox','type' => 'int','settype' => 'int')
);

$rs_CustomerMailAuthentication = $rs->Select('CustomerMailAuthentication', "ConfigType=1 AND CityId='" . $CityId . "'");
if (mysqli_num_rows($rs_CustomerMailAuthentication) == 0)
  {
  $a_CustomerMailAuthentication[] = array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $CityId);
  $rs->Insert('CustomerMailAuthentication', $a_CustomerMailAuthentication);
  }
else
  {
  $rs->Update('CustomerMailAuthentication', $a_CustomerMailAuthentication, "ConfigType=1 AND CityId='" . $CityId . "'");
  }
  
$mctcProcessing=array(
  array('field' => 'Position','selector' => 'value','type' => 'int','settype' => 'int','value' => $MCTCPosition),
  array('field' => 'Username','selector' => 'value','type' => 'str','value' => $MCTCMassiveUsername),
  array('field' => 'Password','selector' => 'value','type' => 'str','value' => $MCTCMassivePassword),
  array('field' => 'FileInput','selector' => 'value','type' => 'str','value' => $MCTCFileInput),
  array('field' => 'FileOutput','selector' => 'value','type' => 'str','value' => $MCTCFileOutput),
  array('field' => 'FileFlag','selector' => 'value','type' => 'str','value' => $MCTCFileOk),
  array('field' => 'FTP','selector' => 'value','type' => 'str','value' => $MCTCFtp),
  array('field' => 'Province','selector' => 'value','type' => 'str','value' => $MCTCProvince),
  array('field' => 'Name','selector' => 'value','type' => 'str','value' => $MCTCName),
  array('field' => 'UsernameCrossBorder','selector' => 'value','type' => 'str','value' => $MCTCCrossBorderUserName),
  array('field' => 'PasswordCrossBorder','selector' => 'value','type' => 'str','value' => $MCTCCrossBorderPassword),
);

$rs_mctcprocessing=$rs->Select("ProcessingMCTC","CityId='{$_SESSION['cityid']}'");
if(mysqli_num_rows($rs_mctcprocessing)>0)
  {
  $rs->Update("ProcessingMCTC",$mctcProcessing,"CityId='{$_SESSION['cityid']}'");
  } else {
  $mctcProcessing[]=array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $_SESSION['cityid']);
  $rs->Insert("ProcessingMCTC",$mctcProcessing);
  }

if (CheckValue('JudgeCity', 's') != "" && CheckValue('JudgeProvince', 's') != "")
  {
  $rs_Office = $rs->Select('JudicialOffice', "OfficeId=1 AND CityId='" . $_SESSION['cityid'] . "'");
  $a_Office = array(
    array('field' => 'City','selector' => 'value','type' => 'str','value' => CheckValue('JudgeCity', 's')),
    array('field' => 'Province','selector' => 'value','type' => 'str','value' => strtoupper(CheckValue('JudgeProvince', 's'))),
    array('field' => 'Address','selector' => 'value','type' => 'str','value' => CheckValue('JudgeAddress', 's')),
    array('field' => 'ZIP','selector' => 'value','type' => 'str','value' => CheckValue('JudgeZIP', 's')),
    array('field' => 'Phone','selector' => 'value','type' => 'str','value' => CheckValue('JudgePhone', 's')),
    array('field' => 'Fax','selector' => 'value','type' => 'str','value' => CheckValue('JudgeFax', 's')),
    array('field' => 'Mail','selector' => 'value','type' => 'str','value' => strtolower(CheckValue('JudgeMail', 's'))),
    array('field' => 'PEC','selector' => 'value','type' => 'str','value' => strtolower(CheckValue('JudgePEC', 's'))),
    array('field' => 'Web','selector' => 'value','type' => 'str','value' => CheckValue('JudgeWeb', 's')),
    array('field' => 'Disabled','selector' => 'value','type' => 'int','settype' => 'int','value' => CheckValue('JudgeDisabled', 'n'))
  );
  
  if (mysqli_num_rows($rs_Office) > 0)
    {
    $rs->Update("JudicialOffice", $a_Office, "OfficeId=1 AND CityId='" . $_SESSION['cityid'] . "'");
    }
  else
    {
    $a_Office[] = array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $_SESSION['cityid']);
    $a_Office[] = array('field' => 'OfficeId','selector' => 'value','type' => 'int','value' => 1,'settype' => 'int');

    $rs->Insert("JudicialOffice", $a_Office);
    }
  }

if (CheckValue('PrefectureCity', 's') != "" && CheckValue('PrefectureProvince', 's') != "")
  {
  $rs_Office = $rs->Select('JudicialOffice', "OfficeId=2 AND CityId='" . $_SESSION['cityid'] . "'");
  $a_Office = array(
    array('field' => 'City','selector' => 'value','type' => 'str','value' => CheckValue('PrefectureCity', 's')),
    array('field' => 'Province','selector' => 'value','type' => 'str','value' => strtoupper(CheckValue('PrefectureProvince', 's'))),
    array('field' => 'Address','selector' => 'value','type' => 'str','value' => CheckValue('PrefectureAddress', 's')),
    array('field' => 'ZIP','selector' => 'value','type' => 'str','value' => CheckValue('PrefectureZIP', 's')),
    array('field' => 'Phone','selector' => 'value','type' => 'str','value' => CheckValue('PrefecturePhone', 's')),
    array('field' => 'Fax','selector' => 'value','type' => 'str','value' => CheckValue('PrefectureFax', 's')),
    array('field' => 'Mail','selector' => 'value','type' => 'str','value' => strtolower(CheckValue('PrefectureMail', 's'))),
    array('field' => 'PEC','selector' => 'value','type' => 'str','value' => strtolower(CheckValue('PrefecturePEC', 's'))),
    array('field' => 'Web','selector' => 'value','type' => 'str','value' => CheckValue('PrefectureWeb', 's')),
    array('field' => 'Disabled','selector' => 'value','type' => 'int','settype' => 'int','value' => CheckValue('PrefectureDisabled', 'n'))
  );
  if (mysqli_num_rows($rs_Office) > 0)
    {
    $rs->Update("JudicialOffice", $a_Office, "OfficeId=2 AND CityId='" . $_SESSION['cityid'] . "'");
    }
  else
    {
    $a_Office[] = array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $_SESSION['cityid']);
    $a_Office[] = array('field' => 'OfficeId','selector' => 'value','type' => 'int','value' => 2,'settype' => 'int');

    $rs->Insert("JudicialOffice", $a_Office);
    }
  }

if (CheckValue('CourtCity', 's') != "" && CheckValue('CourtProvince', 's') != "")
  {
  $rs_Office = $rs->Select('JudicialOffice', "OfficeId=3 AND CityId='" . $_SESSION['cityid'] . "'");
  $a_Office = array(
    array('field' => 'City','selector' => 'value','type' => 'str','value' => CheckValue('CourtCity', 's')),
    array('field' => 'Province','selector' => 'value','type' => 'str','value' => strtoupper(CheckValue('CourtProvince', 's'))),
    array('field' => 'Address','selector' => 'value','type' => 'str','value' => CheckValue('CourtAddress', 's')),
    array('field' => 'ZIP','selector' => 'value','type' => 'str','value' => CheckValue('CourtZIP', 's')),
    array('field' => 'Phone','selector' => 'value','type' => 'str','value' => CheckValue('CourtPhone', 's')),
    array('field' => 'Fax','selector' => 'value','type' => 'str','value' => CheckValue('CourtFax', 's')),
    array('field' => 'Mail','selector' => 'value','type' => 'str','value' => strtolower(CheckValue('CourtMail', 's'))),
    array('field' => 'PEC','selector' => 'value','type' => 'str','value' => strtolower(CheckValue('CourtPEC', 's'))),
    array('field' => 'Web','selector' => 'value','type' => 'str','value' => CheckValue('CourtWeb', 's')),
    array('field' => 'Disabled','selector' => 'value','type' => 'int','settype' => 'int','value' => CheckValue('CourtDisabled', 'n'))
  );

  
  if (mysqli_num_rows($rs_Office) > 0)
    {
    $rs->Update("JudicialOffice", $a_Office, "OfficeId=3 AND CityId='" . $_SESSION['cityid'] . "'");
    }
  else
    {
    $a_Office[] = array('field' => 'CityId','selector' => 'value','type' => 'str','value' => $_SESSION['cityid']);
    $a_Office[] = array('field' => 'OfficeId','selector' => 'value','type' => 'int','value' => 3,'settype' => 'int');

    $rs->Insert("JudicialOffice", $a_Office);
    }
  }

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Aggiornamento effettuato.";

header("location: ".impostaParametriUrl(array('ActiveTab' => $ActiveTab, 'ActiveTabPrinter' => $ActiveTabPrinter), 'tbl_customer_upd.php'.$Filters));
