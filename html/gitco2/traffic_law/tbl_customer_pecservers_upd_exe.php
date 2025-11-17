<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$CityId = $_SESSION['cityid'];
$ActiveTab = CheckValue('ActiveTab','n');

$PagoPAService = CheckValue('PagoPAService','s');
$CustomerServicePassword = CheckValue('CustomerServicePassword','s');
$PagoPAAuxCode = CheckValue('PagoPAAuxCode','s') == '' ? null : CheckValue('PagoPAAuxCode','s');
$PagoPAApplicationCode = CheckValue('PagoPAApplicationCode','s') == '' ? null : CheckValue('PagoPAApplicationCode','s');

$DisableAuthProperties = CheckValue('DisableAuthProperties','s') != '' ? CheckValue('DisableAuthProperties','s') : null;

$MCTCUserVPN = CheckValue('MCTCUserVPN','s');
$MCTCPasswordVPN = CheckValue('MCTCPasswordVPN', 's');
$MCTCExpirationDateVPN = CheckValue('MCTCExpirationDateVPN', 's');

$rs->Start_Transaction();

//Customer
$a_Customer = array(
    array('field'=>'PagoPAAlias','selector'=>'field','type'=>'str'),
    array('field'=>'PagoPAIban','selector'=>'field','type'=>'str'),
    array('field'=>'PagoPAAuxCode','selector'=>'value','type'=>'str','value'=>$PagoPAAuxCode),
    array('field'=>'PagoPAApplicationCode','selector'=>'value','type'=>'str','value'=>$PagoPAApplicationCode),
    array('field'=>'SondrioServizio','selector'=>'field','type'=>'str'),
    array('field'=>'SondrioSottoservizio','selector'=>'field','type'=>'str'),
    array('field'=>'PagoPAService','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'IsIuvCodiceAvviso','selector'=>'chkbox','type'=>'int','settype'=>'int'),
);

$rs->Update('Customer',$a_Customer,"CityId='".$CityId."'");

//CustomerService

$a_CustomerService = array(
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>$MCTCUserVPN),
    array('field'=>'Password','selector'=>'value','type'=>'str','value'=>$MCTCPasswordVPN),
    array('field'=>'PasswordExpiration','selector'=>'value','type'=>'date','value'=>DateInDB($MCTCExpirationDateVPN)),
);

$rs->Update('CustomerService',$a_CustomerService,"ServiceId=6 AND CityId='".ENTE_BASE."'");

//Parametri autenticazione email
$a_CustomerMailAuthentication = array(
    array('field'=>'IncomingSecurity','selector'=>'field','type'=>'str'),
    array('field'=>'IncomingMailServer','selector'=>'field','type'=>'str'),
    array('field'=>'IncomingProtocol','selector'=>'field','type'=>'str'),
    array('field'=>'IncomingPort','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'OutgoingSecurity','selector'=>'field','type'=>'str'),
    array('field'=>'OutgoingMailServer','selector'=>'field','type'=>'str'),
    array('field'=>'OutgoingProtocol','selector'=>'field','type'=>'str'),
    array('field'=>'OutgoingPort','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'DisableAuthProperties','selector'=>'value','type'=>'str','value'=>$DisableAuthProperties),
);

$rs_CustomerMailAuthentication = $rs->Select('CustomerMailAuthentication',"ConfigType=1 AND CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerMailAuthentication)==0){
    $a_CustomerMailAuthentication[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('CustomerMailAuthentication', $a_CustomerMailAuthentication);
} else{
    $rs->Update('CustomerMailAuthentication', $a_CustomerMailAuthentication,"ConfigType=1 AND CityId='".$CityId."'");
}

//CustomerService
$aCustomerService = array(
    array('field'=>'Password','selector'=>'value','type'=>'str','value'=>$CustomerServicePassword),
    array('field'=>'PasswordExpiration','selector'=>'field','type'=>'date'),
);

$rs_CustomerService = $rs->Select("CustomerService","CityId='{$_SESSION['cityid']}' AND ServiceId=$PagoPAService");
if(mysqli_num_rows($rs_CustomerService)==0){
    $aCustomerService[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']);
    $aCustomerService[] = array('field'=>'ServiceId','selector'=>'value','type'=>'int','value'=>$PagoPAService,'settype'=>'int');
    $rs->Insert("CustomerService", $aCustomerService);
} else {
    $rs->Update("CustomerService", $aCustomerService, "CityId='{$_SESSION['cityid']}' AND ServiceId=$PagoPAService");
}

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Aggiornamento effettuato.";

header("location: ".impostaParametriUrl(array('ActiveTab' => $ActiveTab), "tbl_customer_pecservers_upd.php".$Filters));

