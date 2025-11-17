<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 'n');
$a_UsedDetectors = array();

$rs_UsedDetectors = $rs->SelectQuery("
    SELECT DetectorId FROM FineArticle
    WHERE CityId = '{$_SESSION['cityid']}' AND DetectorId > 0
    GROUP BY DetectorId");
while($r_UsedDetectors = $rs->getArrayLine($rs_UsedDetectors)){
    $a_UsedDetectors[] = $r_UsedDetectors['DetectorId'];
}

if(!in_array($Id, $a_UsedDetectors)){
    $rs->Delete('Detector', "Id=$Id");
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
} else {
    $_SESSION['Message']['Error'] = "Impossibile eliminare: il rilevatore è usato almeno in un verbale.";
}

header("location: tbl_detector.php".$Filters);