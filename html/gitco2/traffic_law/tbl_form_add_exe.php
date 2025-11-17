<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

function insertFormDynamic($rs,$NationalityId,$FormTypeId,$LanguageId,$CityId,$Content,$username,$date,$Title){
    trigger_error("Insert FormDynamic with Title $Title",E_USER_NOTICE);
    $formVariable= array(
        array('field'=>'FormTypeId', 'selector'=>'value','type'=>'int','value'=>$FormTypeId,'settype'=>'int'),
        array('field'=>'NationalityId', 'selector'=>'value','type'=>'int','value'=>$NationalityId,'settype'=>'int'),
        array('field'=>'LanguageId', 'selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
        array('field'=>'RuleTypeId', 'selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'CityId', 'selector'=>'value','type'=>'str', 'value'=>$CityId),
        array('field'=>'Title', 'selector'=>'value','type'=>'str', 'value'=>$Title),
        array('field'=>'Content', 'selector'=>'value','type'=>'str', 'value'=>$Content),
        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$username),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$date)
    );
    $rs->Insert("FormDynamic", $formVariable);
    
    $rs_sottotesti=$rs->Select("FormVariable","CityId='' and NationalityId='$NationalityId' and LanguageId='$LanguageId' and FormTypeId='$FormTypeId'");
    while($rs_sottotesto=mysqli_fetch_array($rs_sottotesti)){   
        $Id=$rs_sottotesto['Id'];
        $Type=$rs_sottotesto['Type'];

        $rs_check_existance=$rs->SelectQuery("select count(*) as count from FormVariable where CityId='$CityId'and Id='$Id' and NationalityId='$NationalityId' and LanguageId='$LanguageId' and FormTypeId='$FormTypeId' and Type='$Type'");
        $r_count = mysqli_fetch_array($rs_check_existance);
        if ($r_count['count']==0){
                $sottotesto= array(
                    array('field'=>'Id', 'selector'=>'value','type'=>'str','value'=>$Id),
                    array('field'=>'NationalityId', 'selector'=>'value','type'=>'int','value'=>$NationalityId,'settype'=>'int'),
                    array('field'=>'FormTypeId', 'selector'=>'value','type'=>'int','value'=>$FormTypeId,'settype'=>'int'),
                    array('field'=>'RuleTypeId', 'selector'=>'value','type'=>'int','value'=>$rs_sottotesto['RuleTypeId'],'settype'=>'int'),
                    array('field'=>'CityId', 'selector'=>'value','type'=>'str','value'=>$CityId),
                    array('field'=>'LanguageId', 'selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
                    array('field'=>'Type', 'selector'=>'value','type'=>'int','value'=>$rs_sottotesto['Type'],'settype'=>'int'),
                    array('field'=>'Description', 'selector'=>'value','type'=>'str','value'=>$rs_sottotesto['Description']),
                    array('field'=>'Content', 'selector'=>'value','type'=>'str', 'value'=>$rs_sottotesto['Content']),
                    array('field'=>'Disabled', 'selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$username),
                    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$date)
                );
                $rs->Insert("FormVariable", $sottotesto);
        }
    }
}

$Filters = CheckValue('Filters','s');

$CityId = CheckValue('CityId','s');
$Title = CheckValue('Title','s');
$NationalityId = CheckValue('NationalityId','n');
$RuleTypeId = CheckValue('RuleTypeId','n');
$Content = stripslashes(CheckValue('Content','s'));
$LanguageId = ($NationalityId == 1) ? 1 : CheckValue('LanguageId','n');

$rs->Start_Transaction();
$rs_check_existence = $rs->Select("FormDynamic", "CityId='$CityId' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND Title='$Title' AND LanguageId=$LanguageId AND Deleted=0");
if (mysqli_num_rows($rs_check_existence) > 0){
    $_SESSION['Message']['Error'] = 'Testo dianmico già presente.';
    header("location: ".impostaParametriUrl(array('Filter' => 1), 'tbl_form_add.php'.$Filters));
    DIE;
} else {
    $FormTypeId = CheckValue('FormTypeId','s');
    $date=date("Y-m-d");
    if ($NationalityId == 2){
        $languages=$rs->Select("Language");
        while ($r_Language = mysqli_fetch_array($languages)){
            echo "ciclo su language ".$r_Language['Id']." ".$r_Language[0] ;
            print_r($r_Language);
            //recupera testo default lingua
            $rs_languageContent = $rs->Select("FormDynamic", "CityId='' "."AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=".$r_Language['Id']." AND Deleted=0");
            //echo "<br>CityId='' "."AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=".$r_Language['Id']." AND Deleted=0";
            if(mysqli_num_rows($rs_languageContent)>0 and $r_Language['Id']!= $LanguageId ) {
                $r_languageContent = mysqli_fetch_array($rs_languageContent);
                $Content = $r_languageContent['Content'];
                //echo "<br>$Content";
            }
            insertFormDynamic($rs,$NationalityId,$FormTypeId,$r_Language['Id'],$CityId,$Content,$_SESSION['username'],$date,$Title);
        }
    }
    else 
        insertFormDynamic($rs,$NationalityId,$FormTypeId,$LanguageId,$CityId,$Content,$_SESSION['username'],$date,$Title);
}
$rs->End_Transaction();

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';

header("location: ".impostaParametriUrl(array('Filter' => 1), 'tbl_form.php'.$Filters));
