<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$rs->Start_Transaction();

$Id = CheckValue('FormKeywordId','n');
$RuleTypeId = CheckValue('RuleTypeId','n');
$FormTypeId = CheckValue('FormTypeId','n');
$CityId = CheckValue('CityId','s');
$LanguageId = CheckValue('LanguageId','n');
$Title = CheckValue('Title','s');
$NationalityId = CheckValue('NationalityId','n');
$Disabled = CheckValue('Disabled','s');

$Disabled = ($Disabled != "") ? 1 : 0;

$rs_FormKeyword = $rs->Select('FormKeyword', "Id=$Id");
$r_FormKeyword = mysqli_fetch_array($rs_FormKeyword);

$rs_CheckFormKeyword = $rs->Select('FormKeyword', "FormTypeId=".$FormTypeId." AND RuleTypeId=".$RuleTypeId." AND CityId='' AND LanguageId=" .$LanguageId." AND Title='".$Title ."' AND Title<>'".$r_FormKeyword['Title']."'");

if (mysqli_num_rows($rs_CheckFormKeyword) > 0){
    header("location: mgmt_keyword_upd.php".$str_GET_Parameter."&P=mgmt_keyword.php&error=Titolo già presente per questi dati");
    DIE;
} else {
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
        array('field'=>'RuleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'FormTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        //array('field'=>'CityId','selector'=>'field','type'=>'str'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>''),
        array('field'=>'LanguageId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'NationalityId','selector'=>'value','type'=>'int','value'=>$NationalityId, 'settype'=>'int'),   
        array('field'=>'Title','selector'=>'field','type'=>'str'),
        array('field'=>'Description','selector'=>'field','type'=>'str'),
        array('field'=>'Notes','selector'=>'field','type'=>'str'),
        array('field'=>'Disabled','selector'=>'value','type'=>'int','value'=>$Disabled,'settype'=>'int'),
        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d")),
    );
    
    $rs->Update('FormKeyword', $a_FormKeyword, "Id=$Id");
}

$rs->End_Transaction();


header("location: mgmt_keyword.php".$str_GET_Parameter."&answer=Modificato con successo.");
