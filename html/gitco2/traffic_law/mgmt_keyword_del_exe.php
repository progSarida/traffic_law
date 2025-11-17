<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$Id = CheckValue('Id','n');

$rs->Start_Transaction();

$rs_FormKeyword = $rs->Select('FormKeyword', "Id=$Id");
$r_FormKeyword = mysqli_fetch_array($rs_FormKeyword);

$a_FormKeywordHistory = array(
    array('field'=>'FormKeywordId','selector'=>'value','type'=>'int','value'=>$r_FormKeyword['Id'],'settype'=>'int'),
    array('field'=>'RuleTypeId','selector'=>'value','type'=>'int','value'=>$r_FormKeyword['RuleTypeId'],'settype'=>'int'),
    array('field'=>'FormTypeId','selector'=>'value','type'=>'int','value'=>$r_FormKeyword['FormTypeId'],'settype'=>'int'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_FormKeyword['CityId']),
    array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$r_FormKeyword['LanguageId'],'settype'=>'int'),
    array('field'=>'NationalityId','selector'=>'value','type'=>'int','value'=>$r_FormKeyword['NationalityId'],'settype'=>'int'),
    array('field'=>'Title','selector'=>'value','type'=>'str','value'=>$r_FormKeyword['Title']),
    array('field'=>'Description','selector'=>'value','type'=>'str','value'=>$r_FormKeyword['Description']),
    array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$r_FormKeyword['Notes']),
    array('field'=>'Disabled','selector'=>'value','type'=>'int','value'=>$r_FormKeyword['Disabled'],'settype'=>'int'),
    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_FormKeyword['UserId']),
    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_FormKeyword['VersionDate']),
);

$rs->Insert('FormKeywordHistory',$a_FormKeywordHistory);

$a_FormKeyword = array(
    array('field'=>'Deleted','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
);

$rs->Update("FormKeyword",$a_FormKeyword,"Id=$Id");

$rs->End_Transaction();