<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$rs->Start_Transaction();

$RuleTypeId = CheckValue('RuleTypeId','n');
$FormTypeId = CheckValue('FormTypeId','n');
$CityId = CheckValue('CityId','s');
$NationalityId = CheckValue('NationalityId','n');
$LanguageId = CheckValue('LanguageId','n');
$Title = CheckValue('Title','s');


$rs_FormKeyword = $rs->Select('FormKeyword', "FormTypeId=".$FormTypeId." AND RuleTypeId=".$RuleTypeId." AND CityId='' AND NationalityId='".$NationalityId ."' AND LanguageId=" .$LanguageId." AND Title='".$Title ."'");

if(mysqli_num_rows($rs_FormKeyword)>0){
    header("location: mgmt_keyword_add.php".$str_GET_Parameter."&P=mgmt_keyword.php&error=Titolo già presente per questi dati");
    DIE;
}

$a_FormKeyword = array(
    array('field'=>'RuleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'FormTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    //array('field'=>'CityId','selector'=>'field','type'=>'str'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>''),
    array('field'=>'LanguageId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'NationalityId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'Title','selector'=>'field','type'=>'str'),
    array('field'=>'Description','selector'=>'field','type'=>'str'),
    array('field'=>'Notes','selector'=>'field','type'=>'str'),
    array('field'=>'Disabled','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d")),
);

$rs->Insert('FormKeyword', $a_FormKeyword);


$rs->End_Transaction();


header("location: mgmt_keyword.php".$str_GET_Parameter."&answer=Inserito con successo.");
