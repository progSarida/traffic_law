<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");

$n_LicensePoint = CheckValue('DecurtationPoint', 'n');
$FineId = CheckValue('Search_FineId', 'n');
$ReducedDate = CheckValue('DecurtationDate', 's');
$decurtation=CheckValue('operation', 's');
trigger_error("decurtation? $decurtation");
if($decurtation=='decurtation'){
    updateCommunicationStatus($rs, $FineId, 5, null, $n_LicensePoint, $ReducedDate);
    header("location: mgmt_licensepoint_add.php?answer=Decurtazione inserita");
}
else{
    updateCommunicationStatus($rs, $FineId, 0, null);
    header("location: mgmt_licensepoint_add.php?answer=Riattribuzione inserita");
}
