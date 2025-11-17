<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");



$Id= CheckValue('Id','n');
$Filters = CheckValue('Filters','s');


$a_Fine = array(
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>32),
    array('field'=>'NoteProcedure','selector'=>'field','type'=>'str'),

);
$rs->Update('Fine',$a_Fine, 'Id='.$Id);


$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
header("location: mgmt_fine.php".$Filters);