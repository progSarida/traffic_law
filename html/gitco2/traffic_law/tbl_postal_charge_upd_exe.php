<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$PageTitle = CheckValue("PageTitle", "s");
$CityId= $_SESSION['cityid'];
$a_Update = array (
    array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"),'settype'=>'date'),
);
$rs->Update("PostalCharge", $a_Update, "ToDate IS NULL and CityId='$CityId'");
$a_Insert = array (
    array('field'=>'Zone0','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Zone1','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Zone2','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Zone3','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CanFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CadFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone0','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone1','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone2','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone3','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'FromDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"),'settype'=>'date'),
    array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>NULL,'settype'=>'date'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId)
);
$rs->Insert("PostalCharge", $a_Insert);
header("location: tbl_postal_charge.php?PageTitle=".$PageTitle.'&answer=Modificato con successo.');