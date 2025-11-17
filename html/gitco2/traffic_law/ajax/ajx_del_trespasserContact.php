<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$ContactType = CheckValue('ContactType','s');
$ContactId = CheckValue('ContactId','s');
$TrespasserId = CheckValue('TrespasserId','s');
$NoForwardings = false;
$NoDomiciles = false;
$NoDwellings = false;

$rs->Start_Transaction();

$a_TrespasserContact = array(
    array('field'=>'Deleted','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
);

if ($ContactType == "Forwarding"){
    $rs->Update("TrespasserContact",$a_TrespasserContact, "Id=$ContactId AND TrespasserId=$TrespasserId AND ContactTypeId=1");
    $rs_Forwardings = $rs->Select("TrespasserContact", "TrespasserId=$TrespasserId AND ContactTypeId=1 AND Deleted=0");
    if (mysqli_num_rows($rs_Forwardings) == 0) $NoForwardings = true;
} else if ($ContactType == "Domicile") {
    $rs->Update("TrespasserContact",$a_TrespasserContact, "Id=$ContactId AND TrespasserId=$TrespasserId AND ContactTypeId=2");
    $rs_Forwardings = $rs->Select("TrespasserContact", "TrespasserId=$TrespasserId AND ContactTypeId=2 AND Deleted=0");
    if (mysqli_num_rows($rs_Forwardings) == 0) $NoDomiciles = true;
} else if ($ContactType == "Dwelling") {
    $rs->Update("TrespasserContact",$a_TrespasserContact, "Id=$ContactId AND TrespasserId=$TrespasserId AND ContactTypeId=3");
    $rs_Forwardings = $rs->Select("TrespasserContact", "TrespasserId=$TrespasserId AND ContactTypeId=3 AND Deleted=0");
    if (mysqli_num_rows($rs_Forwardings) == 0) $NoDwellings = true;
}


$rs->End_Transaction();

echo json_encode(
    array(
        "Id" => $TrespasserId,
        "Type" => $ContactType,
        "NoForwardings" => $NoForwardings,
        "NoDomiciles" => $NoDomiciles,
        "NoDwellings" => $NoDwellings,
        "ContactId" => $ContactId,
    )
);
    
    