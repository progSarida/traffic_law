<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$Id= CheckValue('Id','n');


$rs_Fine = $rs->Select('Fine',"CityId='".$_SESSION['cityid']."' AND StatusTypeId=13 AND FineTypeId=2 AND Id=".$Id);
$ProtocolIdAssigned = mysqli_fetch_array($rs_Fine)['ProtocolId'];



if($ProtocolIdAssigned>0){
    $ProtocolId = 0;
    $StatusTypeId = 1;

    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
        array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolId, 'settype'=>'int'),
        array('field'=>'ProtocolIdAssigned','selector'=>'value','type'=>'int','value'=>$ProtocolIdAssigned, 'settype'=>'int'),
    );

    $rs->Update('Fine',$a_Fine, 'Id='.$Id);

    $str_Mex = "Modificato con successo!";

} else $str_Mex = "Problemi con il preavviso id ". $Id;



$_SESSION['Message'] = $str_Mex;
header("location: mgmt_warning.php".$str_GET_Parameter);