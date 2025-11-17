<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$Filters = CheckValue('Filters','s');

$Save = CheckValue('Save','s');

$Content = stripslashes(CheckValue('Content','s'));
$Id= CheckValue('Id','s');
$Type= CheckValue('Type','s');
$FormTypeId= CheckValue('FormTypeId','s');
$RuleTypeId= CheckValue('RuleTypeId','s');
$CityId= CheckValue('CityId','s');
$LanguageId= CheckValue('LanguageId','s');
$NationalityId= CheckValue('NationalityId','s');
$Disabled = CheckValue('Disabled','n');

if ($Save == 1 || $Save == ""){
    $rs->Start_Transaction();
    
    $rs_FormVariable = $rs->Select('FormVariable', "Id='$Id' AND FormTypeId=$FormTypeId AND CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Type=$Type");
    
    $r_FormVariable = mysqli_fetch_array($rs_FormVariable);
    
    $a_FormVariableHistory = array(
        array('field'=>'FormVariableId','selector'=>'value','type'=>'str','value'=>$r_FormVariable['Id']),
        array('field'=>'NationalityId','selector'=>'value','type'=>'int','value'=>$r_FormVariable['NationalityId'],'settype'=>'int'),
        array('field'=>'FormTypeId','selector'=>'value','type'=>'int','value'=>$r_FormVariable['FormTypeId'],'settype'=>'int'),
        array('field'=>'RuleTypeId','selector'=>'value','type'=>'int','value'=>$r_FormVariable['RuleTypeId'],'settype'=>'int'),
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_FormVariable['CityId']),
        array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$r_FormVariable['LanguageId'],'settype'=>'int'),
        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>$r_FormVariable['Type'],'settype'=>'int'),
        array('field'=>'Description','selector'=>'value','type'=>'str', 'value'=>$r_FormVariable['Description']),
        array('field'=>'Content','selector'=>'value','type'=>'str', 'value'=>$r_FormVariable['Content']),
        array('field'=>'Disabled','selector'=>'value','type'=>'int', 'value'=>$r_FormVariable['Disabled'],'settype'=>'int'),
        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_FormVariable['UserId']),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_FormVariable['VersionDate']),
    );
    
    $rs->Insert('FormVariableHistory',$a_FormVariableHistory);
    
    
    $a_FormVariable= array(
        array('field'=>'Content', 'selector'=>'value','type'=>'str', 'value'=>$Content),
        array('field'=>'Disabled', 'selector'=>'value','type'=>'int', 'value'=>$Disabled,'settype'=>'int'),
        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d H:i:s")),
    );
    
    $rs->Update('FormVariable',$a_FormVariable,"Id='$Id' AND FormTypeId=$FormTypeId AND CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Type=$Type");
    
    $rs->End_Transaction();
    
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

if ($Save == "") {
    header("location: ".impostaParametriUrl(array('Filter' => 1), 'mgmt_variable.php'.$Filters));
}

