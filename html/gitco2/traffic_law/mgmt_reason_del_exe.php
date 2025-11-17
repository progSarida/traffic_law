<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$PageTitle = CheckValue('PageTitle', 's');
$Id = CheckValue('Id', 'n');

$rs_UsedReasons = $rs->SelectQuery("SELECT ReasonId FROM FineArticle WHERE CityId='{$_SESSION['cityid']}' GROUP BY ReasonId");
while($r_UsedReasons = $rs->getArrayLine($rs_UsedReasons)){
    $a_UsedReasons[] = $r_UsedReasons['ReasonId'];
}

if(!in_array($Id, $a_UsedReasons)){
    $rs->Delete('Reason', "Id=$Id");
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
} else {
    $_SESSION['Message']['Error'] = "Impossibile eliminare: il motivo contestazione è usato almeno in un verbale.";
}

header("location: ".impostaParametriUrl(array('PageTitle' => $PageTitle), 'mgmt_reason.php'));