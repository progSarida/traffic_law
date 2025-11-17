<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$FormTypeId = CheckValue('FormTypeId','n');

$rs->Start_Transaction();

$rs_Form = $rs->Select('FormDynamic', "FormTypeId=$FormTypeId");

while ($r_Form = mysqli_fetch_array($rs_Form)){
    $a_FormHistory = array(
        array('field'=>'FormTypeId','selector'=>'value','type'=>'int','value'=>$r_Form['FormTypeId'],'settype'=>'int'),
        array('field'=>'NationalityId','selector'=>'value','type'=>'int','value'=>$r_Form['NationalityId'],'settype'=>'int'),
        array('field'=>'RuleTypeId','selector'=>'value','type'=>'int','value'=>$r_Form['RuleTypeId'],'settype'=>'int'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Form['CityId']),
        array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$r_Form['LanguageId'],'settype'=>'int'),
        array('field'=>'Title','selector'=>'value','type'=>'str','value'=>$r_Form['Title']),
        array('field'=>'Content','selector'=>'value','type'=>'str', 'value'=>$r_Form['Content']),
        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Form['UserId']),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Form['VersionDate']),
    );
    
    $rs->Insert('FormDynamicHistory',$a_FormHistory);
}

$a_Form = array(
    array('field'=>'Deleted','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
);

$rs->Update("FormDynamic",$a_Form,"FormTypeId=$FormTypeId");

$rs->End_Transaction();