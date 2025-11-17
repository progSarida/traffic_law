<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$VehiclePlate = strtoupper(CheckValue('VehiclePlate','s'));
$n_ArticleNumber = CheckValue('ArticleNumber','n');
$Genre = strtoupper(CheckValue('Genre','s'));


$ArticleId = CheckValue('ArticleId_1','n');
$Fee = $_POST['Fee_1'];
$MaxFee= $_POST['MaxFee_1'];
$PrefectureFee = CheckValue('PrefectureFee_1','f');
$PrefectureDate = CheckValue('PrefectureDate_1','s');
$TotalPoints          = CheckValue('TotalPoints','n');

if (isset($_POST['Controlli']) && $_POST['Controlli'] == 'on'){
    $eludiControlli = 1;
}else{
    $eludiControlli = 0;
}

$AllFolder = CheckValue('AllFolder','s');

$DepartmentId = CheckValue('DepartmentId','n');

$Documentation = CheckValue('Documentation','s');

$FineTypeId = CheckValue('FineTypeId','n');
$InsertionType = CheckValue('InsertionType', 'n');

if ($InsertionType == 2) $FineTypeId = 2;

$CountryId = CheckValue('CountryId','s');

$Locality= CheckValue('Locality','s');


$reason_text = CheckValue('Reason_Text','s');


if($Locality=="")$Locality = $_SESSION['cityid'];

$StatusTypeId = ($FineTypeId==2) ? 13 : 14;
$ViolationTypeId = CheckValue('ViolationTypeId','n');

$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");

if (mysqli_num_rows($rs_RuleType) > 0){
    $r_RuleType = mysqli_fetch_array($rs_RuleType);
    $RuleTypeId = $r_RuleType['Id'];
    $str_WhereRule = " AND VT.RuleTypeId=$RuleTypeId";
} else $str_WhereRule = "";

//$rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year']);
//fare la select su Fine in join con FineArticle-ViolationType per avere il RuleTypeId
// e poter generare i cronologici divisi per regolamento
$rs_Protocol = $rs->SelectQuery(
    "SELECT
        IFNULL(MAX(ProtocolId)+1, 1) ProtocolId,
        IFNULL(MAX(ProtocolIdAssigned)+1, 1) ProtocolIdAssigned
        FROM Fine F
        inner join FineArticle FA on F.Id = FA.FineId
        inner join ViolationType VT on FA.ViolationTypeId = VT.Id
        WHERE F.CityId='" . $_SESSION['cityid'] . "' AND F.ProtocolYear=" . $_SESSION['year'].$str_WhereRule);

$r_Protocol = mysqli_fetch_array($rs_Protocol);

$n_Protocol_num = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

$ProtocolNumber = $n_Protocol_num;

$CodePrefix = CheckValue('InputPrefix','s');

if ($CodePrefix != "") {
    $code = $_POST['Code'].'/'.$CodePrefix.'/'.$_SESSION['year'];
} else $code = $_POST['Code'].'/'.$_SESSION['year'];




$controllers = $_POST['ControllerId'];
$first_controller=$controllers[0];

$unique = array();
foreach($controllers as $value) {
    if($value != 0) {
        if (isset($unique[$value])) {
            header("location: ".$str_BackPage."&error=Si prega di non scegliere lo stesso accertatore più di una volta!&back=true&P=mgmt_warning.php&insertionType=2");
            DIE;
        }
        $unique[$value] = '';
    }
}
// $articles = array('article_1'=>$_POST['ArticleId_1'],'article_2'=>$_POST['ArticleId_2'],'article_3'=>$_POST['ArticleId_3'],'article_4'=>$_POST['ArticleId_4'],'article_5'=>$_POST['ArticleId_5']);
// $unique_article = array();
// foreach($articles as $value_article) {
//     if($value_article != 0) {
//         if (isset($unique_article[$value_article])) {
//             if (isset($unique_article[$value_article])) {
//                 header("location: ".$str_BackPage."&error=Si prega di non scegliere lo stesso articolo più di una volta!&back=true&P=mgmt_warning.php&insertionType=2");
//                 DIE;
//             }
//         }
//         $unique_article[$value_article] = '';
//     }
// }

$a_Fine = array(
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$code),
	array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
	array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
	array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$_SESSION['year']),
    array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber,'settype'=>'int'),
	array('field'=>'FineDate','selector'=>'field','type'=>'date'),
	array('field'=>'FineTime','selector'=>'field','type'=>'str'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$first_controller,'settype'=>'int'),
	array('field'=>'TimeTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
    array('field'=>'StreetTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'Address','selector'=>'field','type'=>'str'),
	array('field'=>'VehicleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
    array('field'=>'TemporaryPlate','selector'=>'chkbox','type'=>'int','settype'=>'int'),
	array('field'=>'VehicleCountry','selector'=>'field','type'=>'str'),
	array('field'=>'CountryId','selector'=>'field','type'=>'str'),
    array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$DepartmentId,'settype'=>'int'),
	array('field'=>'VehicleBrand','selector'=>'field','type'=>'str'),
	array('field'=>'VehicleModel','selector'=>'field','type'=>'str'),
	array('field'=>'VehicleColor','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleMass','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
	array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
	array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
	array('field'=>'Note','selector'=>'field','type'=>'str'),
    array('field'=>'FineTypeId','selector'=>'value','type'=>'int','value'=>$FineTypeId,'settype'=>'int'),
    array('field'=>'ChkControl','selector'=>'value','type'=>'int','value'=>$eludiControlli,'settype'=>'int'),
);


$rs->Start_Transaction();

$FineId = $rs->Insert('Fine',$a_Fine);

if($FineId==0){
	echo "Poblemi con l'inserimento del verbale";
	DIE;
}

$rss = new CLS_DB();
array_shift($controllers);
foreach($controllers as $value) {
    if($value != 0) {
        $a_FineController = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=> $value, 'settype'=>'int'),
        );
        $rss->Insert('FineAdditionalController',$a_FineController);
    }
}

if (isset($_POST['ReasonId']) && $_POST['ReasonId'] > 0){
    $reasonId = $_POST['ReasonId'];
}else{
    $reasonId = $_POST['ReasonId_Second'];
}

$a_FineArticle = array(
	array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
	array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
	array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId, 'settype'=>'int'),
    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$reasonId,'settype'=>'int'),
    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
	array('field'=>'DetectorId','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'SpeedLimit','selector'=>'field','type'=>'flt','settype'=>'flt'),
	array('field'=>'SpeedControl','selector'=>'field','type'=>'flt','settype'=>'flt'),
	array('field'=>'Speed','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'TimeTLightFirst','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'TimeTLightSecond','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
    array('field'=>'LicensePoint','selector'=>'field','type'=>'int','value'=>$TotalPoints,'settype'=>'int'),
    array('field'=>'PrefectureFee','selector'=>'value','type'=>'flt','value'=>$PrefectureFee,'settype'=>'flt'),
    array('field'=>'PrefectureDate','selector'=>'value','type'=>'date','value'=>$PrefectureDate),
);

$rs->Insert('FineArticle',$a_FineArticle);





$str_ArticleText = trim(CheckValue('ArticleText_1','s'));
$reason = trim($reason_text);
$AdditionalSanctionText = trim(CheckValue('AdditionalSanctionInputText_1','s'));

$a_FineOwner = array( array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int') );
if($str_ArticleText!=""){
    $a_FineOwner [] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
}
if ($reason != "") {
    $a_FineOwner []= array('field' => 'ReasonDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => $reason);
}
if($AdditionalSanctionText!="") {
    $a_FineOwner [] = array('field' => 'AdditionalDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => $AdditionalSanctionText);
}
if ($AdditionalSanctionText!="" || $reason != "" || $str_ArticleText!=""){
    $rs->Insert('FineOwner', $a_FineOwner);
}




if($n_ArticleNumber>1){
    for($i=2;$i<=$n_ArticleNumber;$i++){

        $ArticleId = CheckValue('ArticleId_'.$i,'n');
        $Fee = CheckValue('Fee_'.$i,'n');
        $MaxFee= CheckValue('MaxFee_'.$i,'n');
        $PrefectureFee = CheckValue('PrefectureFee_'.$i,'f');
        $PrefectureDate = CheckValue('PrefectureDate_'.$i,'s');
        
        if($ArticleId>0){
            $a_FineAdditionalArticle = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
                array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
                array('field'=>'ArticleOrder','selector'=>'value','type'=>'int','value'=>$i,'settype'=>'int'),
                array('field'=>'PrefectureFee','selector'=>'value','type'=>'flt','value'=>$PrefectureFee,'settype'=>'flt'),
                array('field'=>'PrefectureDate','selector'=>'value','type'=>'date','value'=>$PrefectureDate),
            );
            $str_ArticleText = trim(CheckValue('ArticleText_'.$n_ArticleNumber,'s'));

            if($str_ArticleText!=""){
                $a_FineAdditionalArticle[] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
            }
            $rs->Insert('FineAdditionalArticle',$a_FineAdditionalArticle);
        }



    }
}



if(strlen($Documentation)>0){
    
    $checkSize = explode("/",$Documentation);
    
    //var_dump($checkSize);
    if (count($checkSize)==5){
        $all_files = glob(WARNING_FOLDER."/".$_SESSION['cityid']."/".$checkSize[3]."/*.*");
        $newLink = "/".$checkSize[3]."/";
    }else{
        $all_files = glob(WARNING_FOLDER."/".$_SESSION['cityid']."/*.*");
        $newLink = null;
    }
    
    $str_Folder = ($CountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
    $aDoc = explode("/",$Documentation);
    $arr = array();
    foreach ($all_files as $file){
        $file = explode("/",$file);
        $arr[] = $file[count($file)-1];
    }
    $DocumentName = $aDoc[count($aDoc)-1];
    $moveArray = array();
    array_push($moveArray, $DocumentName);
    if (isset($_POST['checked'])){
        array_push($moveArray,$_POST['checked'][0]);
    }
    if(isset($_POST['UploadNumber']) && $_POST['UploadNumber']==2){
        if (count($moveArray) <2){
            header("location: ".$str_BackPage."&error=Si prega di scegliere due immagini!&P=mgmt_warning.php&back=true&insertionType=2");
            DIE;
        }
    }
    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
        mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
    }
    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
        mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
    }
    if($AllFolder=='N'){
        
        foreach ($moveArray as $val){
            
            if (in_array($val, $arr))
            {
                copy(WARNING_FOLDER."/".$_SESSION['cityid']."/".$newLink.$val, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val);
                if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val)) {
                    unlink(WARNING_FOLDER ."/".$_SESSION['cityid']."/".$newLink.$val);
                } else {
                    echo "Poblemi con la creazione del documento: ".$val;
                    DIE;
                }
                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$val),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);
            } else {
                echo "Match not found";
            }
        }
    }else{
        if (file_exists($SourceFolder."/Thumbs.db")) {
            unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
        }
        
        $SourceFolder = str_replace($DocumentName,"",$Documentation);
        $aListFile = scandir($SourceFolder);
        
        for($i=0;$i<count($aListFile);$i++){
            if($aListFile[$i]!='.' && $aListFile[$i]!='..' && $aListFile[$i]!='Thumbs.db'){
                
                copy(ROOT ."/". $SourceFolder.$aListFile[$i], $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$aListFile[$i]);
                if (file_exists($SourceFolder."/Thumbs.db")) {
                    unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
                }
                if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$aListFile[$i])) {
                    unlink(ROOT ."/". $SourceFolder.$aListFile[$i]);
                }
                else{
                    echo "Poblemi con la creazione del documento: ".$aListFile[$i];
                    DIE;
                }
                
                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$aListFile[$i]),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);
            }
        }
        rmdir(ROOT ."/". $SourceFolder);
    }
}

// if(strlen($Documentation)>0){
//     $aDoc = explode("/",$Documentation);
    
//     $DocumentName = $aDoc[count($aDoc)-1];
    
//     $str_Folder = ($CountryId=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
    
    
    
//     if (!is_dir($str_Folder."/".$_SESSION['cityid'])) {
//         mkdir($str_Folder."/".$_SESSION['cityid'], 0777);
//     }
//     if (!is_dir($str_Folder."/".$_SESSION['cityid']."/".$FineId)) {
//         mkdir($str_Folder."/".$_SESSION['cityid']."/".$FineId, 0777);
//     }
    
    
//     if($AllFolder=='N'){
//         copy(ROOT ."/". $Documentation, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$DocumentName);
//         if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$DocumentName)) {
//             unlink(ROOT ."/". $Documentation);
//         } else {
//             echo "Poblemi con la creazione del documento: ".$DocumentName;
//             DIE;
//         }
        
//         $a_FineDocumentation = array(
//             array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
//             array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
//             array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
//         );
//         $rs->Insert('FineDocumentation',$a_FineDocumentation);
        
//     }else{
//         if (file_exists($SourceFolder."/Thumbs.db")) {
//             unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
//         }
        
//         $SourceFolder = str_replace($DocumentName,"",$Documentation);
//         $aListFile = scandir($SourceFolder);
        
//         for($i=0;$i<count($aListFile);$i++){
//             if($aListFile[$i]!='.' && $aListFile[$i]!='..' && $aListFile[$i]!='Thumbs.db'){
//                 copy(ROOT ."/". $SourceFolder."/".$aListFile[$i], $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$aListFile[$i]);
//                 if (file_exists($SourceFolder."/Thumbs.db")) {
//                     unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
//                 }
//                 if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$aListFile[$i])) {
//                     unlink(ROOT ."/". $SourceFolder."/".$aListFile[$i]);
//                 }
//                 else{
//                     echo "Poblemi con la creazione del documento: ".$aListFile[$i];
//                     DIE;
//                 }
                
//                 $a_FineDocumentation = array(
//                     array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
//                     array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$aListFile[$i]),
//                     array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
//                 );
//                 $rs->Insert('FineDocumentation',$a_FineDocumentation);
//             }
//         }
//         rmdir(ROOT ."/". $SourceFolder);
//     }
// }



if(isset($_POST['checkbox'])) {
    $TrespasserTypeId = ($CountryId=='Z00Z') ? 11 : 1;
   //i preavvisi hanno stato 13
   //$StatusTypeId = ($CountryId=='Z00Z') ? 2 : 10;
    
    foreach($_POST['checkbox'] as $TrespasserId) {
        
        $a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId,'settype'=>'int'),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId,'settype'=>'int'),
            array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Contravventore già esistente inserito in automatico'),
            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
        );
        
        $rs->Insert('FineTrespasser',$a_FineTrespasser);
        
        
        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
    }
    
} else {
    
    $TrespasserType = strtoupper(CheckValue('TrespasserType','n'));
    $a_TrespasserId = array();
    $a_TrespasserTypeId = array();
    if($TrespasserType==1){
        $a_TrespasserTypeId[10] = 1; //proprietario
        $a_TrespasserTypeId[15] = 15; //patria potestà Proprietario
        $a_TrespasserId[10]=0;
        $a_TrespasserId[15]=0;
    } else if($TrespasserType==3){
        $a_TrespasserTypeId[10] = 10; //ditta di noleggio NOLEGGIANTE
        $a_TrespasserTypeId[11] = 11; //obbligato/trasgressore NOLEGGIO (chi ha noleggiato e conduce)
        $a_TrespasserId[10]=0;
        $a_TrespasserId[11]=0;
    } else if($TrespasserType==4){
        $a_TrespasserTypeId[10] = 10; //ditta di noleggio NOLEGGIANTE
        $a_TrespasserTypeId[11] = 11; //obbligato/trasgressore NOLEGGIO (chi ha noleggiato)
        $a_TrespasserTypeId[12] = 12; //conducente
        $a_TrespasserTypeId[17] = 16; //patria potestà trasgressore
        $a_TrespasserId[10]=0;
        $a_TrespasserId[11]=0;
        $a_TrespasserId[12]=0;
        $a_TrespasserId[17]=0;
    } else {
        $a_TrespasserTypeId[10] = 2; //obbligato (proprietario)
        $a_TrespasserTypeId[11] = 3; //trasgressore (conducente)
        $a_TrespasserTypeId[15] = 15; //patria potestà Proprietario
        $a_TrespasserTypeId[16] = 16; //patria potestà trasgressore
        $a_TrespasserId[10]=0;
        $a_TrespasserId[11]=0;
        $a_TrespasserId[15]=0;
        $a_TrespasserId[16]=0;
    }
    
    $tres = array();
    
    foreach($a_TrespasserId as $key=>$value) {
        
        $TrespasserId = CheckValue('TrespasserId'.$key,'n');
        
        if($TrespasserId > 0){
            
            //i preavvisi hanno stato 13
            //$StatusTypeId = 10;
            
            array_push($tres,$TrespasserId);
            $a_TrespasserId[$key] = $TrespasserId;
            
            $TrespasserTypeId = $a_TrespasserTypeId[$key];
            
            $a_FineTrespasser = array(
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                //array('field'=>'Note','selector'=>'field','type'=>'str'),
            );
            
            if($TrespasserType==3 || $TrespasserType==4){
                if ($TrespasserTypeId == 11) {
                    $a_FineTrespasser[] = array('field'=>'ReceiveDate','selector'=>'field','type'=>'date');
                    $a_FineTrespasser[] = array('field'=>'OwnerAdditionalFee','selector'=>'field','type'=>'flt','settype'=>'flt');
                }
            }
            
            $rs->Insert('FineTrespasser',$a_FineTrespasser);
            
        }
    }
    
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
    );
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
    
//     if($Genre!=""){
//         $CountryId = "Z000";
//         $TresspasserTypeId = 1;
//         $ZoneId = 1;
//         $LanguageId = 1;
//         $DataSourceId = 2;
//         $DataSourceDate = date("Y-m-d");
    
    
//         $TrespasserAddress = strtoupper(CheckValue('TrespasserAddress','s'));
    
//         if ($Genre == 'D') {
//             $CompanyName = strtoupper(CheckValue('CompanyName','s'));
    
//             $rs_Trespasser = $rs->Select('Trespasser', "CompanyName='" . addslashes($CompanyName) . "' AND City='" . addslashes($City) . "'");
    
//             if (mysqli_num_rows($rs_Trespasser)==0) {
    
//                 $a_Insert = array(
//                     array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
//                     array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName),
//                     array('field' => 'Address', 'selector' => 'value', 'type' => 'str','value' => $TrespasserAddress),
//                     array('field' => 'ZIP', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'City', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'Province', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
//                     array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
//                     array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
//                     array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
//                     array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
//                     array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
//                 );
//                 $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
//                 if ($TrespasserId == 0) {
//                     echo "Poblemi con l'inserimento del trasgressore";
//                     DIE;
//                 }
//             } else {
//                 $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
//                 $TrespasserId = $r_Trespasser['Id'];
    
//             }
    
//         } else {
//             $TaxCode = CheckValue('TaxCode','s');
    
//             $rs_Trespasser = $rs->Select('Trespasser', "TaxCode='" . $TaxCode . "'");
    
//             if (mysqli_num_rows($rs_Trespasser)==0) {
    
//                 $BornDateDay = substr($TaxCode, 9, 2);
//                 if ((int)$BornDateDay > 40) {
//                     $Genre = 'F';
//                 }
    
    
//                 $Surname = strtoupper(CheckValue('Surname','s'));
//                 $Name = strtoupper(CheckValue('Name','s'));
    
    
//                 $a_Insert = array(
//                     array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
//                     array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => $Surname),
//                     array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Name),
//                     array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserAddress),
//                     array('field' => 'ZIP', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'City', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'Province', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'BornDate', 'selector' => 'field', 'type' => 'date'),
//                     array('field' => 'BornPlace', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
//                     array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
//                     array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
//                     array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
//                     array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
//                     array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
    
    
//                 );
//                 $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
//                 if ($TrespasserId == 0) {
//                     echo "Poblemi con l'inserimento del trasgressore";
//                     DIE;
//                 }
//             } else {
//                 $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
//                 $TrespasserId = $r_Trespasser['Id'];
    
//                 $str_ZIP = trim(CheckValue('ZIP','s'));
    
//                 $a_Update = array(
//                     array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserAddress),
//                     array('field' => 'City', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'Province', 'selector' => 'field', 'type' => 'str'),
//                 );
    
    
//                 if($str_ZIP!="") $a_Update[] = array('field' => 'ZIP', 'selector' => 'value','value'=>$str_ZIP, 'type' => 'str');
    
//                 $rs->Update('Trespasser', $a_Update, 'Id=' . $TrespasserId);
    
//             }
    
//         }
//         $a_Insert = array(
//             array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
//             array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
//             array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TresspasserTypeId, 'settype' => 'int'),
//         );
//         $rs->Insert('FineTrespasser', $a_Insert);
    
//     }
}



$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Inserito con successo.";
header("location: ".$str_BackPage."&back=true&P=mgmt_warning.php&insertionType=2");
