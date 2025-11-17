<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

if(isset($_POST['Disattivato'])){
$disable = 1;
}else{
$disable = 0;
}


$aDetector= array(
array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['cityid'])),
array('field' => 'OfficeId', 'selector' => 'value', 'type' => 'int', 'value' => ($_POST['Ufficio']),'settype' => 'int'),
array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Citta'])),
array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Provincia'])),
array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Indirizzo'])),
array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Zip'])),
array('field' => 'Phone', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Telefono'])),
array('field' => 'Fax', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Fax'])),
array('field' => 'Mail', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Mail'])),
array('field' => 'PEC', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Pec'])),
array('field' => 'Web', 'selector' => 'value', 'type' => 'str', 'value' => ($_POST['Web'])),
array('field' => 'Disabled', 'selector' => 'value', 'type' => 'int', 'value' => $disable, 'settype' => 'int'),
);
$rs = new CLS_DB();
$rs->Update('traffic_law.JudicialOffice', $aDetector, "CityId='".$_POST['cityid']."' and OfficeId=".$_POST['Ufficio']);
header("location:mgmt_judicial_office.php?answer=Aggiornato con successo!");
