<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$VehiclePlate= strtoupper(CheckValue('VehiclePlate','s'));
$n_ArticleNumber = CheckValue('ArticleNumber','n');
$Code = $_POST['Code'].'/'.$_SESSION['year'];
$Address = CheckValue('FineAddress','s');
$TrespasserType = strtoupper(CheckValue('TrespasserType','n'));
$CustomerAdditionalFee = $_POST['CustomerAdditionalFee'];
$Id= CheckValue('Id','n');
$chk_NotificationDate = true;
$rs = new CLS_DB();
$articles = array('article_1'=>$_POST['ArticleId_1'],'article_2'=>$_POST['ArticleId_2'],'article_3'=>$_POST['ArticleId_3'],'article_4'=>$_POST['ArticleId_4'],'article_5'=>$_POST['ArticleId_5']);
$unique_article = array();
foreach($articles as $value_article) {
    if($value_article != 0) {
        if (isset($unique_article[$value_article])) {
            if (isset($unique_article[$value_article])) {
                header("location: mgmt_fine_upd.php".$str_GET_Parameter."&Id=".$Id."&answer=Si prega di non scegliere lo stesso articolo più di una volta!");
                DIE;
            }
        }
        $unique_article[$value_article] = '';
    }
}

$controllers = $_POST['ControllerId'];

$first_controller=$controllers[0];

$unique = array();
foreach($controllers as $value) {
    if($value != 0) {
        if (isset($unique[$value])) {
            if (isset($unique[$value])) {
                header("location: mgmt_fine_upd.php".$str_GET_Parameter."&Id=".$Id."&answer=Si prega di non scegliere lo stesso accertatore più di una volta!");
                DIE;
            }
        }
        $unique[$value] = '';

    }
}

$ArticleId = CheckValue('ArticleId_1','n');
$Fee = $_POST['Fee_1'];
$MaxFee= $_POST['MaxFee_1'];
    $CountryId = CheckValue('CountryId','s');
    $rs_row = $rs->Select('Fine','Id='.$Id);
    $r_row = mysqli_fetch_array($rs_row);

    $aFine = array(
        array('field'=>'FineDate','selector'=>'field','type'=>'date'),
        array('field'=>'FineTime','selector'=>'field','type'=>'str'),
        array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$first_controller,'settype'=>'int'),
        array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
        array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code),
        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
        array('field'=>'FineTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'NoteProcedure','selector'=>'field','type'=>'str'),
    );
    $rs->Update('Fine',$aFine, 'Id='.$Id);
$rs->Delete('FineAnomaly', 'FineId='.$Id);
    $rs->Delete('FineAdditionalController','FineId='.$Id);
    array_shift($controllers);
    foreach($controllers as $value) {
        if($value != 0) {
            $a_FineController = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id, 'settype'=>'int'),
                array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=> $value, 'settype'=>'int'),
            );
            $rs->Insert('FineAdditionalController',$a_FineController);
        }
    }


//////////////update Article
    $a_FineArticle = array(
        array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
        array('field'=>'ReasonId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
        array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
        array('field'=>'TimeTLightFirst','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'TimeTLightSecond','selector'=>'field','type'=>'int','settype'=>'int'),
    );

    $rs->Update('FineArticle',$a_FineArticle, 'FineId='.$Id);

    $str_ArticleText = trim(CheckValue('ArticleText_1','s'));


    $rs->Delete('FineAdditionalArticle', 'FineId='.$Id);
    if($n_ArticleNumber>1){
        for($i=2;$i<=$n_ArticleNumber;$i++){
            $ArticleId = CheckValue('ArticleId_'.$i,'n');
            $Fee = $_POST['Fee_'.$i];
            $MaxFee= $_POST["MaxFee_".$i];
            if($ArticleId>0){
                $a_FineAdditionalArticle = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
                    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
                    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
                    array('field'=>'ArticleOrder','selector'=>'value','type'=>'int','value'=>$i,'settype'=>'int'),
                );
                $str_ArticleText = trim(CheckValue('ArticleText_'.$i,'s'));

                if($str_ArticleText!=""){
                    $a_FineAdditionalArticle[] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
                }
                $rs->Insert('FineAdditionalArticle',$a_FineAdditionalArticle);
            }
        }
    }
    if (isset($_POST['CloseFine']) && $_POST['CloseFine'] == 1){
        $aStatusId = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>32),
        );
        $rs->Update('Fine',$aStatusId, 'Id='.$Id);
    }

    $trespasserType = $_POST["datatypeid"];
    $array_Trespasser = unserialize(base64_decode($trespasserType));
/*
    foreach ($array_Trespasser as $typeId){

        if (isset($_POST['NotificationDate_'.$typeId]) && $_POST['NotificationDate_'.$typeId] ==""){
            $rs->Delete('FineHistory', 'FineId='.$Id);
            $rs->Delete('FineNotification', 'FineId='.$Id);
            $aStatusId = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>14),
            );
            $rs->Update('Fine',$aStatusId, 'Id='.$Id);
            $fineNotificationDate = array(
                array('field'=>'FineNotificationDate','selector'=>'value','type'=>'date','value'=>$_POST['NotificationDate_'.$typeId]),
            );
            $rs->Update('FineTrespasser',$fineNotificationDate, 'FineId='.$Id.' AND TrespasserTypeId ='.$typeId);
        }
    }
*/
header("location: mgmt_fine_upd.php".$str_GET_Parameter.'&Id='.$Id.'&answer=Modificato con successo!');