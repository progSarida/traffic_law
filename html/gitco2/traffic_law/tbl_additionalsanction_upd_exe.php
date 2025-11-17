<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 'n');
$Description = CheckValue('Description', 's');
$DescriptionIta = CheckValue('DescriptionIta', 's');

$a_Lan = unserialize(LANGUAGE);

$rs->Start_Transaction();

$a_AdditionalSanction = array(
    array('field'=>'AdditionalSanctionTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'Description','selector'=>'value','type'=>'str','value'=>$Description),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'DescriptionIta','selector'=>'value','type'=>'str','value'=>$DescriptionIta),
    array('field'=>'Disabled','selector'=>'chkbox','type'=>'int','settype'=>'int'),
);

foreach ($a_Lan as $lan){
    if ($lan != ''){
        $a_AdditionalSanction[] = array('field'=>'Title'.$lan,'selector'=>'value','type'=>'str','value'=>CheckValue('Title'.$lan, 's'));
    }
}

$rs->Update('AdditionalSanction', $a_AdditionalSanction, "Id=$Id");

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("Location: tbl_additionalsanction.php".$Filters);
