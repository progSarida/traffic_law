<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 'n');
$Code = CheckValue('Code', 's');
$Fixed = CheckValue('Fixed', 's');
$Description = CheckValue('Description', 's');
$DescriptionIta = CheckValue('DescriptionIta', 's');
$ViolationTypeId = CheckValue('ViolationTypeId', 's');

$a_Lan = unserialize(LANGUAGE);

$rs->Start_Transaction();

$a_Reason = array(
    array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId, 'settype'=>'int'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'Description','selector'=>'value','type'=>'str','value'=>$Description),
    array('field'=>'Fixed','selector'=>'value','type'=>'str','value'=>$Fixed != '' ? $Fixed : null),
    array('field'=>'DescriptionIta','selector'=>'value','type'=>'str','value'=>$DescriptionIta),
    array('field'=>'Disabled','selector'=>'chkbox','type'=>'int','settype'=>'int'),
);

foreach ($a_Lan as $lan){
    if ($lan != ''){
        $a_Reason[] = array('field'=>'Title'.$lan,'selector'=>'value','type'=>'str','value'=>CheckValue('Title'.$lan, 's'));
    }
}

if($Code != ''){
    $rs_Code = $rs->Select("Reason", "Code='".$Code."' AND CityId='".$_SESSION['cityid']."' AND Id != $Id");
    if(mysqli_num_rows($rs_Code) > 0){
        $_SESSION['Message']['Error'] = "La matricola è già stata usata.";
        header("location: ".impostaParametriUrl(array('Id' => $Id, 'P' => 'mgmt_reason.php'), 'mgmt_reason_upd.php'.$Filters));
        DIE;
    } else {
        $a_Reason[] = array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code);
    }
} else {
    $a_Reason[] = array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code);
}

$rs->Update('Reason', $a_Reason, "Id=$Id");

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("Location: mgmt_reason.php".$Filters);

