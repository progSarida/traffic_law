<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id = CheckValue('id', 'n');
$Filters = CheckValue('Filters', 's');
$a_UsedControllers = array();

$rs_UsedControllers = $rs->SelectQuery("
    SELECT NULLIF(F.ControllerId, 0),NULLIF(F.FineChiefControllerId, 0),NULLIF(F2.ControllerId, 0)
    FROM Fine F
    LEFT JOIN FineAdditionalController F2 ON F.Id = F2.FineId
    WHERE F.CityId = '{$_SESSION['cityid']}'
    GROUP BY F.ControllerId,F.FineChiefControllerId,F2.ControllerId");
while($r_UsedControllers = $rs->getArrayLine($rs_UsedControllers)){
    foreach ($r_UsedControllers as $value){
        if(!is_null($value)) $a_UsedControllers[] = $value;
    }
}

$a_UsedControllers = array_unique($a_UsedControllers);

if (in_array($Id, $a_UsedControllers)) {
    $_SESSION['Message']['Error'] = "L'Accertatore non puo essere cancellato in quanto associato ad un verbale esistente.";
} else {
    $rs->Delete('Controller',"Id=".$Id);
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

header("location: mgmt_controller.php".$Filters);
