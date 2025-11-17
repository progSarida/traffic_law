<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


//var_dump($_POST);die;
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

//$a_Documentation = $_POST['checked'];
$b_Rent = CheckValue('b_Rent','n');

$CountryId = CheckValue('CountryId','s');

$Locality= CheckValue('Locality','s');
if($Locality=="")$Locality = $_SESSION['cityid'];

$StatusTypeId = ($CountryId=='Z00Z') ? 2 : 1;

$code = CheckValue('Code', 's');
$CodePrefix = CheckValue('InputPrefix','s');
$NumerBlock = CheckValue('InputBlockNumber','n');

if ($code != ""){
    if ($CodePrefix != "")
        $code .= '/'.$CodePrefix;
    
    if ($NumerBlock != "")
        $code .= '/'.$NumerBlock;
        
    $code .= '/'.$_SESSION['year'];
}

$reason_Text = null;

if (!empty($_POST['Reason_Text'])){
    $reason_Text = $_POST['Reason_Text'];
}


$controllers = $_POST['ControllerId'];
$first_controller=$controllers[0];

$unique = array();
foreach($controllers as $value) {
    if($value != 0) {
        if (isset($unique[$value])) {
            header("location: ".$str_BackPage."&error=Si prega di non scegliere lo stesso accertatore più di una volta!&P=mgmt_violation.php&back=true&insertionType=3");
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
//                 header("location: ".$str_BackPage."&error=Si prega di non scegliere lo stesso articolo più di una volta!&P=mgmt_violation.php&back=true&insertionType=3");
//                 DIE;
//             }
//         }
//         $unique_article[$value_article] = '';
//     }
// }

$a_Fine = array(
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$code),
	array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
	array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
	array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$_SESSION['year']),
	array('field'=>'FineDate','selector'=>'field','type'=>'date'),
	array('field'=>'FineTime','selector'=>'field','type'=>'str'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$first_controller,'settype'=>'int'),
    array('field'=>'ControllerDate','selector'=>'field','type'=>'date'),
    array('field'=>'ControllerTime','selector'=>'field','type'=>'str'),
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
	array('field'=>'ViolationTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
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
    array('field'=>'LicensePoint','selector'=>'value','type'=>'int','value'=>$TotalPoints,'settype'=>'int'),
    array('field'=>'PrefectureFee','selector'=>'value','type'=>'flt','value'=>$PrefectureFee,'settype'=>'flt'),
    array('field'=>'PrefectureDate','selector'=>'value','type'=>'date','value'=>$PrefectureDate),
);

$rs->Insert('FineArticle',$a_FineArticle);




$str_ArticleText = trim(CheckValue('ArticleText_1','s'));
$reason = trim($reason_Text);
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


$ora = $_POST['FineTime'];
$data = $_POST['FineDate'];
$test = explode("/",$data);
$newdate = $test[2]."-".$test[1]."-".$test[0];
$targa = $_POST['VehiclePlate'];

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
        $all_files = glob(VIOLATION_FOLDER."/".$_SESSION['cityid']."/".$checkSize[3]."/*.*");
        $newLink = $checkSize[3]."/";
    }else{
        $all_files = glob(VIOLATION_FOLDER."/".$_SESSION['cityid']."/*.*");
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
            header("location: ".$str_BackPage."&error=Si prega di scegliere due immagini!&P=mgmt_violation.php&back=true&insertionType=3");
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
                copy(VIOLATION_FOLDER."/".$_SESSION['cityid']."/".$newLink.$val, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val);
                if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val)) {
                    unlink(VIOLATION_FOLDER ."/".$_SESSION['cityid']."/".$newLink.$val);
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
//     $checkSize = explode("/",$Documentation);
//     //var_dump($checkSize);
//     if (count($checkSize)==5){
//         $all_files = glob(VIOLATION_FOLDER."/".$_SESSION['cityid']."/".$checkSize[3]."/*.*");
//         $newLink = "/".$checkSize[3]."/";
//     }else{
//         $all_files = glob(VIOLATION_FOLDER."/".$_SESSION['cityid']."/*.*");
//         $newLink = null;
//     }
    
    
//     $str_Folder = ($CountryId == 'Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
//     $aDoc = explode("/",$Documentation);
//     $arr = array();
//     foreach ($all_files as $file){
//         $file = explode("/",$file);
//         $arr[] = $file[count($file)-1];
//     }
//     $DocumentName = $aDoc[count($aDoc)-1];
//     $moveArray = array();
//     array_push($moveArray, $DocumentName);
//     if (isset($_POST['checked'])){
//         array_push($moveArray,$_POST['checked'][0]);
//     }
//     if(isset($_POST['UploadNumber']) && $_POST['UploadNumber']==2){
//         if (count($moveArray) <2){
//             header("location: ".$str_BackPage."&answer=Si prega di scegliere due immagini!&P=mgmt_violation.php&back=true");
//             DIE;
//         }
//     }
//     if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
//         mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
//     }
//     if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
//         mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
//     }
//     if($AllFolder=='N'){
        
//         foreach ($moveArray as $val){
            
//             if (in_array($val, $arr))
//             {
//                 copy(VIOLATION_FOLDER."/".$_SESSION['cityid']."/".$newLink.$val, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val);
//                 if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val)) {
//                     unlink(VIOLATION_FOLDER ."/".$_SESSION['cityid']."/".$newLink.$val);
//                 } else {
//                     echo "Poblemi con la creazione del documento: ".$val;
//                     DIE;
//                 }
//                 $a_FineDocumentation = array(
//                     array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
//                     array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$val),
//                     array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>1, 'settype'=>'int'),
//                 );
//                 $rs->Insert('FineDocumentation',$a_FineDocumentation);
//             } else {
//                 echo "Match not found";
//             }
//         }
//     }else{
//         if (file_exists($SourceFolder."/Thumbs.db")) {
//             unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
//         }
        
//         $SourceFolder = str_replace($DocumentName,"",$Documentation);
//         $aListFile = scandir($SourceFolder);
        
//         for($i=0;$i<count($aListFile);$i++){
//             if($aListFile[$i]!='.' && $aListFile[$i]!='..' && $aListFile[$i]!='Thumbs.db'){
                
//                 copy(ROOT ."/". $SourceFolder.$newLink.$aListFile[$i], $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$aListFile[$i]);
//                 if (file_exists($SourceFolder."/Thumbs.db")) {
//                     unlink(ROOT ."/". $SourceFolder."/Thumbs.db");
//                 }
//                 if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$aListFile[$i])) {
//                     unlink(ROOT ."/". $SourceFolder.$newLink.$aListFile[$i]);
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
    $StatusTypeId = ($CountryId=='Z00Z') ? 2 : 10;

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
    
    if ($TrespasserType == 1){
        if ($a_TrespasserId[10] != 0){
            $StatusTypeId = 10;
        }
    }
    else if ($TrespasserType == 3){
        if ($a_TrespasserId[10] != 0 && $a_TrespasserId[11] != 0){
            $StatusTypeId = 10;
        }
    } else if ($TrespasserType == 4){
        if ($a_TrespasserId[10] != 0 && $a_TrespasserId[11] != 0 && $a_TrespasserId[12] != 0){
            $StatusTypeId = 10;
        }
    } else {
        if ($a_TrespasserId[10] != 0 && $a_TrespasserId[11] != 0){
            $StatusTypeId = 10;
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
//         $StatusTypeId = 10;
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
//             $BornDateDay = substr($TaxCode, 9, 2);
//             if ((int)$BornDateDay > 40) {
//                 $Genre = 'F';
//             }


//             $Surname = strtoupper(CheckValue('Surname','s'));
//             $Name = strtoupper(CheckValue('Name','s'));



//             $rs_Trespasser = $rs->Select('Trespasser', "TaxCode='" . $TaxCode . "'");

//             if (mysqli_num_rows($rs_Trespasser) == 0) {
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
//                 $a_Update = array(
//                     array('field' => 'Address', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'ZIP', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'City', 'selector' => 'field', 'type' => 'str'),
//                     array('field' => 'Province', 'selector' => 'field', 'type' => 'str'),
//                 );

//                 $rs->Update('Trespasser', $a_Update, 'Id=' . $TrespasserId);
//             }


//             $a_Insert = array(
//                 array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
//                 array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
//                 array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TresspasserTypeId, 'settype' => 'int'),
//                 array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => "Inserimento da Import motorizzazione"),
//             );
//             $rs->Insert('FineTrespasser', $a_Insert);

//             $a_Update = array(
//                 array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
//             );

//             $rs->Update('Fine', $a_Update, 'Id=' . $FineId);
//         }
//     }
}




if($b_Rent){

    $StatusTypeId = 10;

    $TrespasserId = CheckValue('TrespasserId10','n');

    if($TrespasserId > 0){
        $TrespasserTypeId = 10;
        $a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        );

        $rs->Insert('FineTrespasser',$a_FineTrespasser);

    } else $StatusTypeId = 2;

    $TrespasserId = CheckValue('TrespasserId11','n');


    if($TrespasserId > 0){
        $TrespasserTypeId = 11;
        $a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
            array('field'=>'ReceiveDate','selector'=>'field','type'=>'date'),
            array('field'=>'OwnerAdditionalFee','selector'=>'field','type'=>'flt', 'settype'=>'flt'),
            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        );

        $rs->Insert('FineTrespasser',$a_FineTrespasser);

    } else $StatusTypeId = 2;


    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
    );
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);

}





$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Inserito con successo.";
header("location: ".$str_BackPage."&P=mgmt_violation.php&back=true&insertionType=3");
