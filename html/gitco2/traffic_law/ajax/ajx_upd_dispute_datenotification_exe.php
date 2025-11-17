<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


if($_POST) {
    $DisputeId = CheckValue('DisputeId','n');
    $GradeTypeId = CheckValue('GradeTypeId','n');
    $DateNotification = CheckValue('DateNotification','s');

    $a_DisputeDate = array(
        array('field'=>'DateNotification','selector'=>'value','type'=>'date','value'=>$DateNotification),
    );

    $rs->Update('DisputeDate', $a_DisputeDate,"DisputeId=".$DisputeId. " AND GradeTypeId=".$GradeTypeId);

}






