<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$Description = CheckValue('Description', 's');
$DescriptionIta = CheckValue('DescriptionIta', 's');

$a_Lan = unserialize(LANGUAGE);

$rs->Start_Transaction();

$rs_Progressive = $rs->SelectQuery("SELECT MAX(A.Progressive)+1 AS Progressive FROM AdditionalSanction A JOIN AdditionalSanctionType AST ON A.AdditionalSanctionTypeId = AST.Id WHERE A.CityId IN('','{$_SESSION['cityid']}') AND AST.RuleTypeId={$_SESSION['ruletypeid']}");
$r_Progressive = mysqli_fetch_assoc($rs_Progressive);

$a_AdditionalSanction = array(
    array('field'=>'Progressive','selector'=>'value','type'=>'int','value'=>$r_Progressive['Progressive'],'settype'=>'int'),
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

$rs->Insert('AdditionalSanction', $a_AdditionalSanction);

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("Location: tbl_additionalsanction.php".$Filters);
