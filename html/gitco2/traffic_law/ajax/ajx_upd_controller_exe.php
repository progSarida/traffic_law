<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_message.php");
include(INC."/function.php");
require(INC."/initialization.php");

$message=new CLS_MESSAGE();
if($_POST) {
    $FineId = CheckValue('Id','n');

    if($_SESSION['controllerid']==0){
        $message->addPlainText("L' utente non ha un accertatore associato. Rivolgersi all' assistenza per associare un accertatore all' utente");
        echo json_encode(array("Message" =>$message->getMessagesString()));
    }
    else{
        $controllers = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Id =".$_SESSION['controllerid']." AND Disabled=0");
        $controller = mysqli_fetch_array($controllers);
        $str_ControllerId = $controller['Code'].' - '.$controller['Name'];
        $ControllerDate = date("Y-m-d");
        $ControllerTime = date("H:i:s");
        $a_Fine = array(
            array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$_SESSION['controllerid'],'settype'=>'int'),
            array('field'=>'ControllerDate','selector'=>'value','type'=>'date','value'=>$ControllerDate),
            array('field'=>'ControllerTime','selector'=>'value','type'=>'str','value'=>$ControllerTime),
        );
        $rs->Update('Fine',$a_Fine,"Id=".$FineId);

        $str_ControllerDate = DateOutDB($ControllerDate) .' '. $ControllerTime;

        echo json_encode(
            array(
                "ControllerId" => $str_ControllerId,
                "ControllerDate" => $str_ControllerDate
            )
        );

    }


}






