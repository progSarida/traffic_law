<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$Filters = CheckValue('Filters','s');

$Content = stripslashes(CheckValue('Content','s'));
$FormTypeId = CheckValue('FormTypeId', 'n');
$CityId = CheckValue('CityId', 's');
$RuleTypeId = CheckValue('RuleTypeId', 's');
$NationalityId = CheckValue('NationalityId', 's');
$LanguageId = CheckValue('LanguageId', 's');
$Deleted = CheckValue('Deleted', 's');
$Title = CheckValue('Title', 's');

$rs->Start_Transaction();

$rs_Form = $rs->Select('FormDynamic', "FormTypeId=$FormTypeId AND Title='$Title' AND CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Deleted=$Deleted");
$r_Form = mysqli_fetch_array($rs_Form);

$a_FormHistory = array(
    array('field'=>'FormTypeId','selector'=>'value','type'=>'int','value'=>$r_Form['FormTypeId'],'settype'=>'int'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Form['CityId']),
    array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$r_Form['LanguageId'],'settype'=>'int'),
    array('field'=>'NationalityId','selector'=>'value','type'=>'int','value'=>$r_Form['NationalityId'],'settype'=>'int'),
    array('field'=>'RuleTypeId','selector'=>'value','type'=>'int','value'=>$r_Form['RuleTypeId'],'settype'=>'int'),
    array('field'=>'Content', 'selector'=>'value','type'=>'str', 'value'=>$r_Form['Content']),
    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Form['UserId']),
    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Form['VersionDate']),
    array('field'=>'Title','selector'=>'value','type'=>'str','value'=>$r_Form['Title']),
);

$rs->Insert('FormDynamicHistory',$a_FormHistory);

$a_Form= array(
	array('field'=>'Content', 'selector'=>'value','type'=>'str', 'value'=>$Content),
    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d")),
);

$rs->Update('FormDynamic',$a_Form,"FormTypeId=$FormTypeId AND CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Deleted=$Deleted");

$rs->End_Transaction();

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';

header("location: ".impostaParametriUrl(array('Filter' => 1), 'tbl_form.php'.$Filters));
