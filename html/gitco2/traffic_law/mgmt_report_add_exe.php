<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

// echo "<pre>"; print_r($_POST); echo "</pre>";
// exit;


$VehiclePlate = strtoupper(CheckValue('VehiclePlate','s'));
$n_ArticleNumber = CheckValue('ArticleNumber','n');
$n_NotificationType = CheckValue('NotificationType','n');
$TrespasserType = strtoupper(CheckValue('TrespasserType','n'));
$CustomerAdditionalFee = CheckValue('CustomerAdditionalFee','f');
$TotalPoints          = CheckValue('TotalPoints','n');

$StreetTypeId = CheckValue('StreetTypeId','n');



$n_CloseFine = CheckValue('CloseFine','n');

$chk_NotificationDate = false;

$b_126Bis = false;
$b_180 = false;


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


$ArticleId = CheckValue('ArticleId_1','n');
$Fee = $_POST['Fee_1'];
$MaxFee= $_POST['MaxFee_1'];
$PrefectureFee = CheckValue('PrefectureFee_1','f');
$PrefectureDate = CheckValue('PrefectureDate_1','s');


$StatusTypeId = 14;
$ViolationTypeId = CheckValue('ViolationTypeId','n');

$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");


if (mysqli_num_rows($rs_RuleType) > 0){
    $r_RuleType = mysqli_fetch_array($rs_RuleType);
    $RuleTypeId = $r_RuleType['Id'];
    $str_WhereRule = " AND VT.RuleTypeId=$RuleTypeId";
} else $str_WhereRule = "";


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

$ProtocolNumber = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

$DepartmentId = CheckValue('DepartmentId','n');

$AllFolder = CheckValue('AllFolder','s');

$Documentation = CheckValue('Documentation','s');

$CountryId = CheckValue('CountryId','s');

$Locality= CheckValue('Locality','s');
if($Locality=="")$Locality = $_SESSION['cityid'];

$code = CheckValue('Code', 's');
$CodePrefix = CheckValue('InputPrefix','s');
$NumerBlock = CheckValue('InputBlockNumber','n');

if ($CodePrefix != "") 
    $code .= '/'.$CodePrefix;

if ($NumerBlock != "")
    $code .= '/'.$NumerBlock;
    
$code .= '/'.$_SESSION['year'];

$str_ReasonOwner = CheckValue('ReasonOwner','s');

$FineChiefControllerId = CheckValue('FineChiefControllerId','s');

if (isset($_POST['Controlli']) && $_POST['Controlli'] == 'on'){
    $eludiControlli = 1;
}else{
    $eludiControlli = 0;
}
$controllers = $_POST['ControllerId'];
$first_controller=$controllers[0];
$unique = array();
foreach($controllers as $value) {
    if($value != 0) {
        if (isset($unique[$value])) {
            header("location: ".$str_BackPage."&error=Si prega di non scegliere lo stesso accertatore più di una volta!&back=true&P=mgmt_report.php&insertionType=1");
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
//             header("location: ".$str_BackPage."&error=Si prega di non scegliere lo stesso articolo più di una volta!&back=true&P=mgmt_report.php&insertionType=1");
//             DIE;
//         }
//         $unique_article[$value_article] = '';
//     }
// }

$a_Fine = array(
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$code),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
    array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$_SESSION['year']),
    array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber,'settype'=>'int'),
    array('field'=>'FineNotificationDate','selector'=>'field','type'=>'date'),
    array('field'=>'FineDate','selector'=>'field','type'=>'date'),
    array('field'=>'FineTime','selector'=>'field','type'=>'str'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$first_controller,'settype'=>'int'),
    array('field'=>'FineChiefControllerId','selector'=>'value','type'=>'int','value'=>$FineChiefControllerId,'settype'=>'int'),
    array('field'=>'TimeTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
    array('field'=>'StreetTypeId','selector'=>'value','type'=>'int','value'=>$StreetTypeId, 'settype'=>'int'),
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
    array('field'=>'FineTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ChkControl','selector'=>'value','type'=>'int','value'=>$eludiControlli,'settype'=>'int'),
    //array('field'=>'NoteProcedure','selector'=>'field','type'=>'str'),
);


$rs->Start_Transaction();
$FineId = $rs->Insert('Fine',$a_Fine);
if($FineId==0){
    echo "Poblemi con l'inserimento del verbale";
    DIE;
}


array_shift($controllers);

foreach($controllers as $value) {
    if($value != 0) {
        $a_FineController = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=> $value, 'settype'=>'int'),
        );
        $rs->Insert('FineAdditionalController',$a_FineController);
    }
}
if ($n_NotificationType == 2){
    $ReasonId = 100;
}else{
    $ReasonId = CheckValue('ReasonId','n');
}
$a_FineArticle = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'ViolationTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
    array('field'=>'DetectorId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'SpeedLimit','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'SpeedControl','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Speed','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'TimeTLightFirst','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'TimeTLightSecond','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
    array('field'=>'DayNumber_180','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'LicensePoint','selector'=>'value','type'=>'int', 'value'=>$TotalPoints,'settype'=>'int'),
    array('field'=>'PrefectureFee','selector'=>'value','type'=>'flt','value'=>$PrefectureFee,'settype'=>'flt'),
    array('field'=>'PrefectureDate','selector'=>'value','type'=>'date','value'=>$PrefectureDate),
);

$rs->Insert('FineArticle',$a_FineArticle);


$rs_Article= $rs->Select("V_Article","Id=". $ArticleId ." AND CityId='". $_SESSION['cityid'] ."' AND Year=". $_SESSION['year']);
$r_Article = mysqli_fetch_assoc($rs_Article);
$b_126Bis = $r_Article['126Bis'] >= 1 ? true : false ;
$b_180 = $r_Article['PresentationDocument'] >= 1 ? true : false ;


$str_ArticleText = trim(CheckValue('ArticleText_1','s'));

$AdditionalSanctionText = trim(CheckValue('AdditionalSanctionInputText_1','s'));

$a_FineOwner = array( array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int') );
if($str_ArticleText!=""){
    $a_FineOwner [] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
}
if ($str_ReasonOwner != "") {
    $a_FineOwner []= array('field' => 'ReasonDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => $str_ReasonOwner);
}
if($AdditionalSanctionText!="") {
    $a_FineOwner [] = array('field' => 'AdditionalDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => $AdditionalSanctionText);
}
if ($AdditionalSanctionText!="" || $str_ReasonOwner != "" || $str_ArticleText!=""){
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
            $str_ArticleText = CheckValue('ArticleText_'.$n_ArticleNumber,'s');

            if($str_ArticleText!=""){
                $a_FineAdditionalArticle[] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
            }

            $rs->Insert('FineAdditionalArticle',$a_FineAdditionalArticle);


            if(!$b_126Bis || !$b_180){                
                $rs_Article= $rs->Select("V_Article","Id=". $ArticleId ." AND CityId='". $_SESSION['cityid'] ."' AND Year=". $_SESSION['year']);
                $r_Article = mysqli_fetch_assoc($rs_Article);
                if(!$b_126Bis)
                    $b_126Bis = $r_Article['126Bis'] >= 1 ? true : false ;
                if(!$b_180)
                    $b_180 = $r_Article['PresentationDocument'] >= 1 ? true : false ;
            }
        }
    }
}



// $DocumentName = "";
// if(strlen($Documentation)>0){
//     $aDoc = explode("/",$Documentation);

//     $DocumentName = $aDoc[count($aDoc)-1];

//     $str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;



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
//             array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2, 'settype'=>'int'),
//         );
//         $rs->Insert('FineDocumentation',$a_FineDocumentation);
        
//     }else{
        
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
//         if (file_exists($SourceFolder."/Thumbs.db")) {
//             unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
//         }
        
//         rmdir(ROOT ."/". $SourceFolder);
//     }
// }
if(strlen($Documentation)>0){
    
    $checkSize = explode("/",$Documentation);
    
    //var_dump($checkSize);
    if (count($checkSize)==5){
        $all_files = glob(REPORT_FOLDER."/".$_SESSION['cityid']."/".$checkSize[3]."/*.*");
        $newLink = "/".$checkSize[3]."/";
    }else{
        $all_files = glob(REPORT_FOLDER."/".$_SESSION['cityid']."/*.*");
        $newLink = null;
    }
    
    $str_Folder = ($CountryId == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;
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
        if ($DocumentName != $_POST['checked'][0])
            array_push($moveArray,$_POST['checked'][0]);
    }
    if(isset($_POST['UploadNumber']) && $_POST['UploadNumber']==2){
        if (count($moveArray) <2){
            header("location: ".$str_BackPage."&error=Si prega di scegliere due immagini!&P=mgmt_report.php&back=true&insertionType=1");
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
                copy(REPORT_FOLDER."/".$_SESSION['cityid']."/".$newLink.$val, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val);
                if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val)) {
                    unlink(REPORT_FOLDER ."/".$_SESSION['cityid']."/".$newLink.$val);
                } else {
                    echo "Poblemi con la creazione del documento: ".$val;
                    DIE;
                }
                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$val),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2, 'settype'=>'int'),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);
            } else {
                echo "Match not found";
            }
        }
    }else{
        
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
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2, 'settype'=>'int'),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);
            }
        }
        if (file_exists($SourceFolder."/Thumbs.db")) {
            unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
        }
        rmdir(ROOT ."/". $SourceFolder);
    }
}


if(isset($_POST['checkbox'])) {
    $TrespasserTypeId = ($CountryId=='Z00Z') ? 11 : 1;
       
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
    $FineDate = CheckValue('FineDate','s');
    $tres = array();
    $chk_NotificationDate= true;
    
    foreach($a_TrespasserId as $key=>$value) {
        $TrespasserId = CheckValue('TrespasserId'.$key,'n');
        
        
        
        if($TrespasserId > 0){
            array_push($tres,$TrespasserId);
            $a_TrespasserId[$key] = $TrespasserId;    
            $NotificationDate = CheckValue('NotificationDate_'.$key,'s');
            $TrespasserTypeId = $a_TrespasserTypeId[$key];
            $a_FineTrespasser = array(
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                array('field'=>'CustomerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$CustomerAdditionalFee, 'settype'=>'flt'),
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
            );
            
            if($TrespasserType == 2 || $TrespasserType==3 || $TrespasserType==4){
                if ($TrespasserTypeId == 11 || $TrespasserTypeId == 3)
                    $a_FineTrespasser[] = array('field'=>'ReceiveDate','selector'=>'field','type'=>'date');
            }
            
            if($NotificationDate!=""){
                $str_NotificationDate = $NotificationDate; 
    
    
                $NotificationType = CheckValue('NotificationType_'.$key,'n');
    
                $a_FineTrespasser[] = array('field'=>'FineCreateDate','selector'=>'value','type'=>'date','value'=>$FineDate);
                $a_FineTrespasser[] = array('field'=>'FineSendDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                $a_FineTrespasser[] = array('field'=>'FineNotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                $a_FineTrespasser[] = array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>$NotificationType, 'settype'=>'int');
               
                //echo "<br> data di notifica presente prima? " . $chk_NotificationDate;
                //echo "<br> tipo notifica generale ? $n_NotificationType";
                //echo "<br> tipo combinazione trasgressori ".$TrespasserType;
                //echo "<br> tipo trasgressore ".$a_TrespasserTypeId[10];
                //echo "<br> tipo trasgressore ".$a_TrespasserTypeId[11];
                
                
                //se la notifica è su strada e il trasgressore notificato è il conducente 
                /*if($n_NotificationType==2 && 
                    ((($TrespasserType==3 || $TrespasserType = 4) and $a_TrespasserTypeId[11] = 11) 
                        || ($TrespasserType==1 && $a_TrespasserTypeId[10] = 1)
                        || ($TrespasserType==2 && $a_TrespasserTypeId[11] = 3))
                    )
                    $chk_NotificationDate=true;*/
                
                    //echo "<br> data di notifica presente dopo? " . $chk_NotificationDate;
                    
            } else 
            {
                //Imposto comunque il tipo di rinotifica
                $NotificationType = CheckValue('NotificationType_'.$key,'n');
                array_push($notification_Type,$NotificationType);
                $a_FineTrespasser[] = array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>$NotificationType, 'settype'=>'int');
                
                $chk_NotificationDate= false;
            }
            
            //echo "<br> data di notifica presente fine ciclo? " . ($chk_NotificationDate === true ? "vero" : "falso");
            
            $rs->Insert('FineTrespasser',$a_FineTrespasser);
            $a_FineCommunication = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                array('field'=>'CommunicationDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
            );
            
            if($b_126Bis){
                $checkLicense = ($TrespasserTypeId == 10) ? $_POST['LicenseDatePropretario'] : $_POST['LicenseDateTrasgressore'];
                if ( $n_NotificationType == 2  && $checkLicense !=""){
                    $rs->Insert('FineCommunication',$a_FineCommunication);
                }
            }
        }
    }    
    
    $isDeceased = false;
    
    foreach ($tres as $value){
        $rs_InsertedTrespasser = $rs->Select('FineTrespasser', "FineId=".$FineId." AND TrespasserId=$value");
        $r_InsertedTrespasser = mysqli_fetch_array($rs_InsertedTrespasser);
        
        if ($r_InsertedTrespasser['TrespasserTypeId'] == 1){
            $rs_Trespasser = $rs->Select('Trespasser', "Id=$value");
            $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
            $DeathDate = $r_Trespasser['DeathDate'];
            $d_FineDate = date('Y-m-d',strtotime(str_replace('/', '-', $FineDate)));
            
            if ($DeathDate != "" && $DeathDate > $d_FineDate) $isDeceased = true;
        }
    }
    
    //echo "<br> data di notifica presente? " . ($chk_NotificationDate === true ? "vero" : "falso"); 
    //echo "<br> tipo notifica generale ? $n_NotificationType";
    //echo "<br> tipo notifica 10 ? ".$_POST['NotificationType_10'];
    //echo "<br> tipo notifica 11 ? ".$_POST['NotificationType_11'] ;
  
  
    if($chk_NotificationDate && count($tres) > 0){
        $NotificationTypeId     = 2;
        $CustomerFee            = $CustomerAdditionalFee;
        $NotificationFee        = 0.00;
        $ResearchFee            = 0.00;   
        
        //se anche il tipo notifica generale è "Su strada" si valutano le notiifche ai singoli trasgressori
        if ($n_NotificationType==1){
            if ($_POST['NotificationType_10'] ==1){
                $resultId = 8;
            }elseif ($_POST['NotificationType_10'] == 2){
                $resultId = 1;
            }else{
                $resultId = 9;
            }
        }else{
            $resultId = 8;
        }
        $Date = $str_NotificationDate;
        $aInsert = array(
            array('field'=>'NotificationTypeId','selector'=>'value','type'=>'int','value'=>$NotificationTypeId,'settype'=>'int'),
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$a_TrespasserId[10],'settype'=>'int'),
            array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$resultId,'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserType,'settype'=>'int'),
            array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$CustomerFee,'settype'=>'flt'),
            array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$NotificationFee,'settype'=>'flt'),
            array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$ResearchFee,'settype'=>'flt'),
            array('field'=>'ControllerId','selector'=>'field','type'=>'int','settype'=>'int'),
            array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$Date),
            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$Date),
            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
        );
        $a_FineHistory = array(
            array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int'),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
            array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $a_TrespasserId[10], 'settype' => 'int'),
            array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserType, 'settype' => 'int'),
            array('field' => 'ResultId','selector'=>'value','type'=>'int','value'=>$resultId,'settype'=>'int'),
            array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerFee, 'settype' => 'flt'),
            array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
            array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
            array('field' => 'ControllerId','selector'=>'field','type'=>'int','settype'=>'int'),
            array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $Date),
            array('field' => 'SendDate','selector'=>'value','type'=>'date','value'=>$Date),
            array('field' => 'DeliveryDate','selector'=>'value','type'=>'date','value'=>$Date),
            array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $Date),
            array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $Date),
            array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        );    
        $rs->Insert('FineHistory',$aInsert);
        $rs->Insert('FineHistory',$a_FineHistory);
        $StatusTypeId = 25;
        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
        $r_TariffQuery = $rs->Select('V_FineTariff', "FineId=".$FineId);
        $r_Tariff = mysqli_fetch_array($r_TariffQuery);
        
        $HabitualProcedure = $r_Tariff['Habitual'];
        $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
        $LossLicenseProcedure = $r_Tariff['LossLicense'];    
        $PaymentProcedure               = 1; //CheckValue('Payment','n');
        $LicensePointProcedure          = CheckValue('TotalPoints','n');
        $ReminderAdditionalFeeProcedure = 0;
        $PresentationDocumentProcedure  = $b_180 ? 1 : 0;//CheckValue('PresentationDocument','n');
        $BisProcedure                   = $b_126Bis ? 1 : 0; //CheckValue('126Bis','n');
        $InjunctionProcedure            = 1;
        
        
        //se anche il tipo notifica generale è "Differita" si valutano le notiifche ai singoli trasgressori
        // $n_NotificationType tipo notifica generale
        //1 differita
        //2 su strada
        //$_POST['NotificationType_XX'] tipo di notifica per trasgressore
        //1 Su strada
        //2 Messo
        //3 Ufficio
        if ($n_NotificationType==1){
            //se i conducenti sono notificati su strada imposto il risultato della notifica a 8 => "Notificato su strada"
            if ($_POST['NotificationType_11'] ==1 || $_POST['NotificationType_10'] ==1 || $_POST['NotificationType_12'] ==1){
                $resultId = 8;
            }elseif ($_POST['NotificationType_11'] ==2 || $_POST['NotificationType_10'] ==2){
                $resultId = 1;
            }else{
                $resultId = 9;
            }
        }else{
            $resultId = 8;
        }
        
        $a_FineNotification = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$Date),
            array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>$Date),
            array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$resultId,'settype'=>'int'),
            array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $BisProcedure,'settype'=>'int'),
            array('field'=>'ReminderAdditionalFeeProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $ReminderAdditionalFeeProcedure,'settype'=>'int'),
            array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $PresentationDocumentProcedure,'settype'=>'int'),
            array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $LicensePointProcedure,'settype'=>'int'),
            array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $PaymentProcedure,'settype'=>'int'),
            array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $HabitualProcedure,'settype'=>'int'),
            array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $SuspensionLicenseProcedure,'settype'=>'int'),
            array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $LossLicenseProcedure,'settype'=>'int'),
            array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $InjunctionProcedure,'settype'=>'int'),
            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
        );
    
        //se è notificato su strada assegno anche la data di notifica
        if ($n_NotificationType==2){
            $a_FineNotification[] = array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$Date);
        }
        
        $rs->Insert('FineNotification',$a_FineNotification);
    }else{
        $ReminderAdditionalFeeProcedure = 0;
        $PaymentProcedure               = 1; //CheckValue('Payment','n');
        $LicensePointProcedure          = CheckValue('TotalPoints','n');
        $PresentationDocumentProcedure  = $b_180 ? 1 : 0; //CheckValue('PresentationDocument','n');
        $BisProcedure                   = $b_126Bis ? 1 : 0; //CheckValue('126Bis','n');
    
        if($PaymentProcedure==0){
            $a_TMP_PaymentProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $PaymentProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_PaymentProcedure',$a_TMP_PaymentProcedure);
        }
    
    
    
        if($LicensePointProcedure==0){
            $a_TMP_LicensePointProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $LicensePointProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_LicensePointProcedure',$a_TMP_LicensePointProcedure);
        }
    
    
        if($PresentationDocumentProcedure==0){
            $a_TMP_PresentationDocumentProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $PresentationDocumentProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_PresentationDocumentProcedure',$a_TMP_PresentationDocumentProcedure);
    
        }
    
        if($BisProcedure==0){
            $a_126TMP_BisProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>($isDeceased) ? 0 : $BisProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_126BisProcedure',$a_126TMP_BisProcedure);
    
        }
    
    }
}

if ($n_CloseFine == 1){
    $StatusTypeId = 32;
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
    );
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
}


$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Inserito con successo. Protocollo n.$ProtocolNumber";
header("location: ".$str_BackPage."&back=true&P=mgmt_report.php&insertionType=1");
