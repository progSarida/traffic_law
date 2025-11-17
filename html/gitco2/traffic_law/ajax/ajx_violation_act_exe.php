<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


if($_POST) {
    $FineId = CheckValue('Id','n');
    $rs_Fine = $rs->Select('Fine',"Id=". $FineId);


    $StatusTypeId = mysqli_fetch_array($rs_Fine)['StatusTypeId'];

    $StatusTypeId = ($StatusTypeId==7) ? 10 : 7;


    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
    );

    $rs->Update('Fine',$a_Fine,"Id=".$FineId);



}






