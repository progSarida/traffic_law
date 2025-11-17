<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$VehiclePlate= strtoupper(CheckValue('VehiclePlate','s'));

$n_ArticleNumber = CheckValue('ArticleNumber','n');
$Id= CheckValue('Id','n');
//var_dump($str_BackPage);DIE;
$reason_text = null;
$articles = array('article_1'=>$_POST['ArticleId_1'],'article_2'=>$_POST['ArticleId_2'],'article_3'=>$_POST['ArticleId_3'],'article_4'=>$_POST['ArticleId_4'],'article_5'=>$_POST['ArticleId_5']);
$unique_article = array();
foreach($articles as $value_article) {
    if($value_article != 0) {
        if (isset($unique_article[$value_article])) {
            if (isset($unique_article[$value_article])) {
                header("location:mgmt_warning_upd.php".$str_GET_Parameter."&Id=".$Id."&answer=Si prega di non scegliere lo stesso articolo più di una volta!");
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
                header("location:mgmt_warning_upd.php".$str_GET_Parameter."&Id=".$Id."&answer=Si prega di non scegliere lo stesso accertatore più di una volta!");
                DIE;
            }
        }
        $unique[$value] = '';

    }
}
if (!empty($_POST['Reason_Text'])){
    $reason_text = $_POST['Reason_Text'];
}

$ArticleId = CheckValue('ArticleId_1','n');
$Fee = $_POST['Fee_1'];
$MaxFee= $_POST['MaxFee_1'];
$ReasonId = $_POST['ReasonId'];

if (isset($_POST['Controlli']) && $_POST['Controlli'] == 'on'){
    $eludiControlli = 1;
}else{
    $eludiControlli = 0;
}

$code = $_POST['Code'].'/'.$_SESSION['year'];

$FineOwner = CheckValue('FineOwner','n');
if($FineOwner){

    $str_BackPage .= "&Id=".$Id;
    $AdditionalArticleId = CheckValue('AdditionalArticleId','s');
    $a_Language = unserialize(LANGUAGE);

    $aFineOwner = array();
    if($AdditionalArticleId>0){
        for ($i = 1; $i <= 5; $i++) {
            array_push($aFineOwner, array('field' => 'ArticleDescription' . $a_Language[$i], 'selector' => 'field', 'type' => 'str'));
        }

        $rs->Update('FineAdditionalArticle',$aFineOwner, 'FineId='.$Id. ' AND ArticleId='.$AdditionalArticleId);

    } else {
        $rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);


        $a_TabId = array("Article","Reason","Additional","Declaration","Damage","Removal","Note");


        for($n_Tab=0;$n_Tab<7;$n_Tab++) {
            for ($i = 1; $i <= 5; $i++) {
                array_push($aFineOwner, array('field' => $a_TabId[$n_Tab] . 'Description' . $a_Language[$i], 'selector' => 'field', 'type' => 'str'));
            }
        }


        if(mysqli_num_rows($rs_FineOwner)==0){
            array_push($aFineOwner, array('field' => 'FineId', 'selector' => 'value', 'type' => 'int','value'=> $Id, 'settype'=>'int'));

            $rs->Insert('FineOwner',$aFineOwner);
        } else {

            $rs->Update('FineOwner',$aFineOwner, 'FineId='.$Id);
        }


    }






} else {


    $CountryId = CheckValue('CountryId','s');
    $rs_row = $rs->Select('Fine','Id='.$Id);
    $r_row = mysqli_fetch_array($rs_row);

    $Locality= CheckValue('Locality','s');
    if($Locality=="")$Locality=$_SESSION['cityid'];

    if(($CountryId!=$r_row['CountryId'])&&($CountryId=='Z000'||$r_row['CountryId']=='Z000')){

        if($CountryId=='Z000'){
            if (!is_dir(NATIONAL_VIOLATION."/".$_SESSION['cityid']."/".$Id)) {
                mkdir(NATIONAL_VIOLATION."/".$_SESSION['cityid']."/".$Id, 0777);

            }
            $str_OldFolder = FOREIGN_VIOLATION;
            $str_NewFolder = NATIONAL_VIOLATION;

        } else{
            if (!is_dir(FOREIGN_VIOLATION."/".$_SESSION['cityid']."/".$Id)) {
                mkdir(FOREIGN_VIOLATION."/".$_SESSION['cityid']."/".$Id, 0777);

            }

            $str_OldFolder = NATIONAL_VIOLATION;
            $str_NewFolder = FOREIGN_VIOLATION;
        }


        $rs_row = $rs->Select('FineDocumentation','FineId='.$Id);
        while($r_row = mysqli_fetch_array($rs_row)){
            copy($str_OldFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation'],$str_NewFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation']);
            if (file_exists($str_NewFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation'])) {
                unlink($str_OldFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation']);
            }
        }

        rmdir($str_OldFolder."/".$_SESSION['cityid']."/".$Id);
    }




    $DepartmentId = CheckValue('DepartmentId','n');
    $Address = CheckValue('FineAddress','s');
    if(isset($_POST['VehicleMass'])) $VehicleMass = $_POST['VehicleMass'];
    else $VehicleMass = 0.0;
    $VehicleTypeId = CheckValue('VehicleTypeId','n');
    if($VehicleTypeId==2 || $VehicleTypeId==9) $VehicleMass = 0.0;



    $aFine = array(
        array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$code),
        array('field'=>'StatusTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'FineDate','selector'=>'field','type'=>'date'),
        array('field'=>'FineTime','selector'=>'field','type'=>'str'),
        array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
        array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$first_controller,'settype'=>'int'),
        array('field'=>'TimeTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'StreetTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
        array('field'=>'VehicleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
        array('field'=>'VehicleCountry','selector'=>'field','type'=>'str'),
        array('field'=>'CountryId','selector'=>'field','type'=>'str'),
        array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$DepartmentId,'settype'=>'int'),
        array('field'=>'VehicleBrand','selector'=>'field','type'=>'str'),
        array('field'=>'VehicleModel','selector'=>'field','type'=>'str'),
        array('field'=>'VehicleColor','selector'=>'field','type'=>'str'),
        array('field'=>'Note','selector'=>'field','type'=>'str'),
        array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$VehicleMass,'settype'=>'flt'),
        array('field'=>'ChkControl','selector'=>'value','type'=>'int','value'=>$eludiControlli,'settype'=>'int'),

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
    if (isset($_POST['ReasonId']) && $_POST['ReasonId'] > 0){
        $reasonId = $_POST['ReasonId'];
    }elseif (isset($_POST['ReasonId_Second']) && $_POST['ReasonId_Second'] > 0){
        $reasonId = $_POST['ReasonId_Second'];
    }else{
        $reasonId = $_POST['ReasonId_Default'];
    }
    //////////////update Article
    $a_FineArticle = array(
        array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
        array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$reasonId,'settype'=>'int'),
        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
        array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
    );


    $rs->Update('FineArticle',$a_FineArticle, 'FineId='.$Id);

    $str_ArticleText = trim(CheckValue('ArticleText_1','s'));
    $reason = trim($reason_text);
    $AdditionalSanctionText = trim(CheckValue('AdditionalSanctionInputText_1','s'));

    $a_FineOwner = array( array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'));
    if($str_ArticleText!=""){
        $a_FineOwner [] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
    }
    if ($reason != "") {
        $a_FineOwner [] = array('field' => 'ReasonDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => $reason);
    }
    if($AdditionalSanctionText!="") {
        $a_FineOwner [] = array('field' => 'AdditionalDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => $AdditionalSanctionText);
    } else {
        $a_FineOwner [] = array('field' => 'AdditionalDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => '');
    }

    $find = $rs->SelectQuery("SELECT * FROM FineOwner WHERE FineId = $Id");
    $find_num = mysqli_num_rows($find);
    if ($find_num==0){
        if ($AdditionalSanctionText!="" || $reason != "" || $str_ArticleText!=""){
            $rs->Insert('FineOwner', $a_FineOwner);
        }
    }else{
        $rs->Update('FineOwner',$a_FineOwner,"FineId=".$Id);
    }

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


    $TrespasserId = CheckValue('TrespasserId','n');



    if(isset($_POST['checkbox'])) {
        $TrespasserTypeId = ($CountryId=='Z00Z') ? 11 : 1;
        $StatusTypeId = ($CountryId=='Z00Z') ? 2 : 10;

        foreach($_POST['checkbox'] as $TrespasserId) {

            $a_FineTrespasser = array(
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId,'settype'=>'int'),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId,'settype'=>'int'),
                array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Contravventore già esistente inserito in automatico'),
            );

            $rs->Insert('FineTrespasser',$a_FineTrespasser);

            $a_Fine = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
            );
            $rs->Update('Fine',$a_Fine, 'Id='.$Id);
        }

    }
    else if($TrespasserId>0){
        $Genre = CheckValue('Genre','s');
        $CountryId = CheckValue('TrespasserCountryId','s');

        $table_rows = $rs->Select('Country',"Id='".$CountryId."'");
        $table_row = mysqli_fetch_array($table_rows);




        if($LanguageId==0) $LanguageId = $table_row['LanguageId'];

        $ZoneId = $table_row['ZoneId'];
        $Address = strtoupper(CheckValue('Address','s'));
        $City = strtoupper(CheckValue('City','s'));
        $Province = strtoupper(CheckValue('Province','s'));
        $CompanyName = strtoupper(CheckValue('CompanyName','s'));

        $TaxCode = strtoupper(CheckValue('TaxCode','s'));
        $BornPlace = strtoupper(CheckValue('BornPlace','s'));
        $Surname = strtoupper(CheckValue('Surname','s'));
        $Name = strtoupper(CheckValue('Name','s'));



        $a_Trespasser = array(
            array('field'=>'Genre','selector'=>'value','type'=>'str','value'=>$Genre),
            array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
            array('field'=>'ZIP','selector'=>'field','type'=>'str','value'=>'ZIP'),
            array('field'=>'City','selector'=>'value','type'=>'str','value'=>$City),
            array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$Province),
            array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryId),
            array('field'=>'Mail','selector'=>'field','type'=>'str','value'=>'Mail'),
            array('field'=>'Phone','selector'=>'field','type'=>'str','value'=>'Phone'),
            array('field'=>'ZoneId','selector'=>'value','type'=>'int','value'=>$ZoneId,'settype'=>'int'),
            array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
            array('field'=>'InipecLoaded','selector'=>'value','type'=>'date', 'value'=>NULL)
        );



        if($Genre=="D"){
            $a_Trespasser[] = array('field'=>'CompanyName','selector'=>'value','type'=>'str', 'value'=>$CompanyName);

        }else{

            $BornDate = CheckValue('BornDate','s');
            if($BornDate!= ""){
                $aBornDate = explode("/",$BornDate);
                $BornDate = $aBornDate[2]."-".$aBornDate[1]."-".$aBornDate[0];
            }
            else
                $BornDate = null;

            $a_Trespasser[] = array('field'=>'BornDate','selector'=>'value','type'=>'date','value'=>$BornDate);
            $a_Trespasser[] = array('field'=>'TaxCode','selector'=>'value','type'=>'str', 'value'=>$TaxCode);
            $a_Trespasser[] = array('field'=>'BornPlace','selector'=>'value','type'=>'str', 'value'=>$BornPlace);
            $a_Trespasser[] = array('field'=>'Surname','selector'=>'value','type'=>'str', 'value'=>$Surname);
            $a_Trespasser[] = array('field'=>'Name','selector'=>'value','type'=>'str', 'value'=>$Name);
        }


        $rs->Update('Trespasser',$a_Trespasser, "Id=".$TrespasserId);


    } else if($CountryId=='Z00Z'){
        $StatusTypeId =  2;
        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$Id);
    }



}


header("location: mgmt_warning.php".$str_GET_Parameter."&answer=Modificato con successo!");