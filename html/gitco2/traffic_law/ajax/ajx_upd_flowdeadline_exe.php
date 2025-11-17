<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_message.php");
include(INC."/function.php");
require(INC."/initialization.php");


if($_POST) {

    $Id     = CheckValue('Id', 'n');
    $Type   = CheckValue('Type', 's');
    $Day    = CheckValue('Day', 'n');


    $a_Update = array(
        array('field'=>'Days_'.$Type,'selector'=>'value','type'=>'int','value'=>$Day,'settype'=>'int'),
    );
    $res = $rs->Update('FlowDeadlines',$a_Update,"Id=".$Id);

    echo json_encode(
        array(
            "RES" => $res,
        )
    );
}






