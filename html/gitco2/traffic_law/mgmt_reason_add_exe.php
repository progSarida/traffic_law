<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$Code = CheckValue('Code', 's');
$RuleTypeId = CheckValue('RuleTypeId', 'n');
$Fixed = CheckValue('Fixed', 's');
$Description = CheckValue('Description', 's');
$DescriptionIta = CheckValue('DescriptionIta', 's');
$ViolationTypeId = CheckValue('ViolationTypeId', 's');

$a_Lan = unserialize(LANGUAGE);

$rs->Start_Transaction();

$rs_Progressive = $rs->SelectQuery("SELECT MAX(Progressive)+1 AS Progressive FROM Reason R JOIN ViolationType VT ON R.ViolationTypeId = VT.Id WHERE R.CityId='{$_SESSION['cityid']}' AND VT.RuleTypeId=$RuleTypeId");
$r_Progressive = mysqli_fetch_assoc($rs_Progressive);

$a_Reason = array(
    array('field'=>'Progressive','selector'=>'value','type'=>'int','value'=>$r_Progressive['Progressive'],'settype'=>'int'),
    array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId, 'settype'=>'int'),
    array('field'=>'Description','selector'=>'value','type'=>'str','value'=>$Description),
    array('field'=>'Fixed','selector'=>'value','type'=>'str','value'=>$Fixed != '' ? $Fixed : null),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'DescriptionIta','selector'=>'value','type'=>'str','value'=>$DescriptionIta),
    array('field'=>'Disabled','selector'=>'chkbox','type'=>'int','settype'=>'int'),
);

foreach ($a_Lan as $lan){
    if ($lan != ''){
        $a_Reason[] = array('field'=>'Title'.$lan,'selector'=>'value','type'=>'str','value'=>CheckValue('Title'.$lan, 's'));
    }
}

if($Code != ''){
    $rs_Code = $rs->Select("Reason", "Code='".$Code."' AND CityId='".$_SESSION['cityid']."'");
    if(mysqli_num_rows($rs_Code) > 0){
        $_SESSION['Message']['Error'] = "La matricola è stata già usata.";
        header("location: ".impostaParametriUrl(array('P' => 'mgmt_reason.php'), 'mgmt_reason_add.php'.$Filters));
        DIE;
    } else {
        $a_Reason[] = array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code);
    }
} else {
    $a_Reason[] = array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code);
}

$rs->Insert('Reason', $a_Reason);

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("Location: mgmt_reason.php".$Filters);
