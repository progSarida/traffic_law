<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id= CheckValue('Id','n');
$Filters= CheckValue('Filters','s');

$str_Number = CheckValue('Number','s');
$int_Year = CheckValue('Year','n');
$str_Date    = DateInDB(CheckValue('Date','s'));
$str_Notes    = CheckValue('Notes','s');

$a_save = array(
    "Number"=>array("s",$str_Number),
    "Year"=>array("s",$int_Year),
    "Date"=>array("s",$str_Date),
    "Notes"=>array("s",$str_Notes),

);

if(!$Id>0){
    $rs->bindInsertArray("Flow_Invoices",$a_save);
}
else{
    $filter = "WHERE Id=".$Id;
    $rs->bindUpdateArray("Flow_Invoices",$a_save,$filter);
}

header("location: ".impostaParametriUrl(array('Filter' => 1), "mgmt_flow.php".$Filters));