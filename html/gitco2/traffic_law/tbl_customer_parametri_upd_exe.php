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
    if (isset($_REQUEST['Automatic'])) {
        $Automatic = 1;
    } else {
        $Automatic = 0;
    }
    $WaitDay = $_REQUEST['WaitDay'];
    $RangeDayMin = $_REQUEST['RangeDayMin'];
    $RangeDayMax = $_REQUEST['RangeDayMax'];
    $ReducedPaymentDayAccepted = $_REQUEST['ReducedPaymentDayAccepted'];
    $PaymentDayAccepted = $_REQUEST['PaymentDayAccepted'];
    $a_Customer = array(
        array('field'=>'Automatic','selector'=>'value','type'=>'int', 'value'=>$Automatic),
        array('field'=>'WaitDay','selector'=>'value','type'=>(int)'int', 'value'=>$WaitDay),
        array('field'=>'RangeDayMin','selector'=>'value','type'=>(int)'int', 'value'=>$RangeDayMin),
        array('field'=>'RangeDayMax','selector'=>'value','type'=>(int)'int', 'value'=>$RangeDayMax),
        array('field'=>'ReducedPaymentDayAccepted','selector'=>'value','type'=>(int)'int', 'value'=>$ReducedPaymentDayAccepted),
        array('field'=>'PaymentDayAccepted','selector'=>'value','type'=>(int)'int', 'value'=>$PaymentDayAccepted),
        array('field'=>'ProcessingDate','selector'=>'value','type'=>'date', 'value'=>date('Y-m-d')),
        array('field'=>'ProcessingStartTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
        array('field'=>'ProcessingEndTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
    );
    $rs->Update('ProcessingDataPaymentNational',$a_Customer,"CityId='".$CityId."'");
    if (isset($_REQUEST['AutomaticForeign'])) {
        $AutomaticForeign = 1;
    } else {
        $AutomaticForeign = 0;
    }
    $WaitDayForeign = $_REQUEST['WaitDayForeign'];
    $RangeDayMinForeign = $_REQUEST['RangeDayMinForeign'];
    $RangeDayMaxForeign = $_REQUEST['RangeDayMaxForeign'];
    $ReducedPaymentDayAcceptedForeign = $_REQUEST['ReducedPaymentDayAcceptedForeign'];
    $PaymentDayAcceptedForeign = $_REQUEST['PaymentDayAcceptedForeign'];
    $a_Customer1 = array(
        array('field'=>'Automatic','selector'=>'value','type'=>'int', 'value'=>$AutomaticForeign),
        array('field'=>'WaitDay','selector'=>'value','type'=>'int', 'value'=>(int)$WaitDayForeign),
        array('field'=>'RangeDayMin','selector'=>'value','type'=>'int', 'value'=>(int)$RangeDayMinForeign),
        array('field'=>'RangeDayMax','selector'=>'value','type'=>'int', 'value'=>(int)$RangeDayMaxForeign),
        array('field'=>'ReducedPaymentDayAccepted','selector'=>'value','type'=>'int', 'value'=>(int)$ReducedPaymentDayAcceptedForeign),
        array('field'=>'PaymentDayAccepted','selector'=>'value','type'=>'int', 'value'=>(int)$PaymentDayAcceptedForeign),
        array('field'=>'ProcessingDate','selector'=>'value','type'=>'date', 'value'=>date('Y-m-d')),
        array('field'=>'ProcessingStartTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
        array('field'=>'ProcessingEndTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
    );
    $rs->Update('ProcessingDataPaymentForeign',$a_Customer1,"CityId='".$CityId."'");
   header("location: tbl_customer_parametri_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
}
if ($steps == "2") {
    var_dump($_POST);
    if (isset($_REQUEST['Automatic'])) {
        $Automatic = 1;
    } else {
        $Automatic = 0;
    }
    $WaitDay = $_REQUEST['WaitDay'];
    $RangeDayMin = $_REQUEST['RangeDayMin'];
    $RangeDayMax = $_REQUEST['RangeDayMax'];
    $ControllerI = $_REQUEST['ControllerId'];
    $a_Customer = array(
        array('field'=>'Automatic','selector'=>'value','type'=>'int', 'value'=>$Automatic),
        array('field'=>'WaitDay','selector'=>'value','type'=>'int', 'value'=>(int)$WaitDay),
        array('field'=>'RangeDayMin','selector'=>'value','type'=>'int', 'value'=>(int)$RangeDayMin),
        array('field'=>'RangeDayMax','selector'=>'value','type'=>'int', 'value'=>(int)$RangeDayMax),
        array('field'=>'ControllerId','selector'=>'value','type'=>'int', 'value'=>(int)$ControllerI),
        array('field'=>'ProcessingDate','selector'=>'value','type'=>'date', 'value'=>date('Y-m-d')),
        array('field'=>'ProcessingStartTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
        array('field'=>'ProcessingEndTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
    );
    $rs->Update('ProcessingData126BisNational',$a_Customer,"CityId='".$CityId."'");
    if (isset($_REQUEST['AutomaticForeign'])) {
        $AutomaticForeign = 1;
    } else {
        $AutomaticForeign = 0;
    }
    $WaitDayForeign = $_REQUEST['WaitDayForeign'];
    $RangeDayMinForeign = $_REQUEST['RangeDayMinForeign'];
    $RangeDayMaxForeign = $_REQUEST['RangeDayMaxForeign'];
    $ControllerId = $_REQUEST['ControllerIdForeign'];
    $a_Customer1 = array(
        array('field'=>'Automatic','selector'=>'value','type'=>'int', 'value'=>$AutomaticForeign),
        array('field'=>'WaitDay','selector'=>'value','type'=>'int', 'value'=>(int)$WaitDayForeign),
        array('field'=>'RangeDayMin','selector'=>'value','type'=>'int', 'value'=>(int)$RangeDayMinForeign),
        array('field'=>'RangeDayMax','selector'=>'value','type'=>'int', 'value'=>(int)$RangeDayMaxForeign),
        array('field'=>'ControllerId','selector'=>'value','type'=>'int', 'value'=>(int)$ControllerId),
        array('field'=>'ProcessingDate','selector'=>'value','type'=>'date', 'value'=>date('Y-m-d')),
        array('field'=>'ProcessingStartTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
        array('field'=>'ProcessingEndTime','selector'=>'value','type'=>'time', 'value'=>date('h:i:s')),
    );
    $rs->Update('ProcessingData126BisForeign',$a_Customer1,"CityId='".$CityId."'");

    header("location: tbl_customer_parametri_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
}
if ($steps == "3") {

    $a_Customer = array(
        array('field'=>'NationalMod23LSubject','selector'=>'field','type'=>'str'),
        array('field'=>'NationalMod23LCustomerName','selector'=>'field','type'=>'str'),
        array('field'=>'NationalMod23LCustomerSubject','selector'=>'field','type'=>'str'),
        array('field'=>'NationalMod23LCustomerAddress','selector'=>'field','type'=>'str'),
        array('field'=>'NationalMod23LCustomerCity','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignMod23LSubject','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignMod23LCustomerName','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignMod23LCustomerSubject','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignMod23LCustomerAddress','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignMod23LCustomerCity','selector'=>'field','type'=>'str'),
    );
    $rs->Update('traffic_law.Customer',$a_Customer,"CityId='".$CityId."'");


    header("location: tbl_customer_parametri_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
}
if ($steps == "4") {

    $a_Customer1 = array(
        array('field'=>'NationalTotalFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'NationalNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'NationalResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'NationalPECNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'NationalPECResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'ForeignTotalFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'ForeignNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'ForeignResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'ForeignPECNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'ForeignPECResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
        array('field'=>'NationalPostalType','selector'=>'field','type'=>'str'),
        array('field'=>'NationalPostalAuthorization','selector'=>'field','type'=>'str'),
        array('field'=>'NationalSmaName','selector'=>'field','type'=>'str'),
        array('field'=>'NationalSmaAuthorization','selector'=>'field','type'=>'str'),
        array('field'=>'NationalSmaPayment','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignPostalType','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignPostalAuthorization','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignSmaName','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignSmaAuthorization','selector'=>'field','type'=>'str'),
        array('field'=>'ForeignSmaPayment','selector'=>'field','type'=>'str'),


    );
    $rs->Update('traffic_law.CustomerCharge',$a_Customer1,"CityId='".$CityId."'");

    header("location: tbl_customer_parametri_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
}
if ($steps == "5") {
    if (isset($_REQUEST['Disabled'])) {
        $disabled = 1;
    } else {
        $disabled = 0;
    }
    $a_judicalOffice = array(

        array('field'=>'OfficeId','selector'=>'field','type'=>'str'),
        array('field'=>'City','selector'=>'field','type'=>'str'),
        array('field'=>'Province','selector'=>'field','type'=>'str'),
        array('field'=>'Address','selector'=>'field','type'=>'str'),
        array('field'=>'ZIP','selector'=>'field','type'=>'str'),
        array('field'=>'Phone','selector'=>'field','type'=>'str'),
        array('field'=>'Fax','selector'=>'field','type'=>'str'),
        array('field'=>'Mail','selector'=>'field','type'=>'str'),
        array('field'=>'PEC','selector'=>'field','type'=>'str'),
        array('field'=>'Web','selector'=>'field','type'=>'str'),
        array('field'=>'Disabled','selector'=>'value','type'=>'int','value'=>$disabled,'set_type'=>'int'),


    );
    $rs->Update('traffic_law.JudicialOffice',$a_judicalOffice,"CityId='".$CityId."'");

    header("location: tbl_customer_parametri_upd.php?Id=" . $CityId . "&tab=" . $steps . "");
}