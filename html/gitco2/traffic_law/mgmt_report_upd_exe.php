<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

// echo "<pre>"; print_r($_POST); echo "</pre>";
// exit;

//Funzione per aggiornare le tabelle TMP
function updateTMP($b_ChangedArticles, $fineid, $isdeceased, $b_180, $b_126Bis, $b_communication, $b_presentation, $totalpoints) {
    global $rs;
    
    $LicensePointProcedure          = $totalpoints;
    //Se l'articolo non prevede 180 oppure la comunicazione 180 è già stata creata, imposta 0 (NO)
    $PresentationDocumentProcedure  = $b_presentation ? 0 : ($b_180 ? 1 : 0);
    //Se l'articolo non prevede 126bis oppure la comunicazione 126bis è già stata creata, imposta 0 (NO)
    $BisProcedure                   = $b_communication ? 0 : ($b_126Bis ? 1 : 0);
    
    //Esegue solo se cambia articolo
    if($b_ChangedArticles){
        $rs->Delete('TMP_LicensePointProcedure', "FineId=$fineid");
        if($LicensePointProcedure==0){
            $a_TMP_LicensePointProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$fineid,'settype'=>'int'),
                array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>($isdeceased) ? 0 : $LicensePointProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_LicensePointProcedure',$a_TMP_LicensePointProcedure);
        }
    }

    $rs->Delete('TMP_PresentationDocumentProcedure', "FineId=$fineid");
    if($PresentationDocumentProcedure==0){
        $a_TMP_PresentationDocumentProcedure = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$fineid,'settype'=>'int'),
            array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>($isdeceased) ? 0 : $PresentationDocumentProcedure,'settype'=>'int'),
        );
        $rs->Insert('TMP_PresentationDocumentProcedure',$a_TMP_PresentationDocumentProcedure);       
    }
    
    //Esegue solo se cambia articolo
    if($b_ChangedArticles){
        $rs->Delete('TMP_126BisProcedure', "FineId=$fineid");
        if($BisProcedure==0){
            $a_126TMP_BisProcedure = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$fineid,'settype'=>'int'),
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>($isdeceased) ? 0 : $BisProcedure,'settype'=>'int'),
            );
            $rs->Insert('TMP_126BisProcedure',$a_126TMP_BisProcedure);
            
        }
    }
}

//Funzione per verificare se gli articoli sono cambiati
function checkArticles($fineid,$articlenumber){
    global $rs;
    $a_SavedArticles = array();
    $a_NewArticles = array();
    
    $rs_FineArticle = $rs->Select('FineArticle', "FineId=$fineid");
    $r_FineArticle = $rs->getArrayLine($rs_FineArticle);
    
    $a_SavedArticles[] = $r_FineArticle['ArticleId'];
    
    for($i=1; $i<=$articlenumber; $i++){
        if(($ArticleId = CheckValue('ArticleId_'.$i,'n')) > 0){
            $a_NewArticles[] = $ArticleId;
        }
    }
    
    $rs_FineAdditionalArticle = $rs->Select('FineAdditionalArticle', "FineId=$fineid");
    if(mysqli_num_rows($rs_FineAdditionalArticle) > 0){
        while($r_FineAdditionalArticle = $rs->getArrayLine($rs_FineAdditionalArticle)){
            $a_SavedArticles[] = $r_FineAdditionalArticle['ArticleId'];
        }
    }

    return $a_NewArticles != $a_SavedArticles;
}

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}

$FineId = CheckValue('Id','n');
$InsertionType = CheckValue('InsertionType','n');
$CountryId = CheckValue('CountryId','s');
$StatusTypeId = CheckValue('StatusTypeId','n');
$KindCreateDate = CheckValue('KindCreateDate','s');
$KindSendDate = CheckValue('KindSendDate','s');

if ($InsertionType == 1)
    $Back = "mgmt_report.php";
else if ($InsertionType == 2)
    $Back = "mgmt_warning.php";
else $Back = "mgmt_violation.php";

if ($InsertionType == 3)
    $StatusTypeId = CheckValue('StatusTypeSelect','n');
//     $StatusTypeId = ($CountryId=='Z00Z') ? 2 : 1;

$VehiclePlate = strtoupper(CheckValue('VehiclePlate','s'));
$n_ArticleNumber = CheckValue('ArticleNumber','n');
$n_NotificationType = CheckValue('NotificationType','n');
$TrespasserType = strtoupper(CheckValue('TrespasserType','n'));
$CustomerAdditionalFee = CheckValue('CustomerAdditionalFee','f');


$StreetTypeId = CheckValue('StreetTypeId','n');
$TotalPoints          = CheckValue('TotalPoints','n');

$n_CloseFine = CheckValue('CloseFine','n');

$chk_NotificationDate = false;

//Determina se è già presente la comunicazione 126bis sul verbale
$b_Communication = mysqli_num_rows($rs->Select('FineCommunication', "FineId=$FineId")) > 0;
//Determina se è già presente la comunicazione 180 8 sul verbale
$b_Presentation = mysqli_num_rows($rs->Select('FinePresentation', "FineId=$FineId")) > 0;
//Determina se sono stati cambiati gli articoli
$b_ChangedArticles = checkArticles($FineId, $n_ArticleNumber);
//Determina se il verbale prevede comunicazione 126 bis in base agli art
$b_126Bis = false;
//Determina se il verbale prevede 180 in base agli articoli
$b_180 = false;

$Address = CheckValue('Address','s');

if (isset($_POST['VehicleMass'])) {
    $VehicleMass = $_POST['VehicleMass'];
} else {
    $VehicleMass = 0.0;
}

$VehicleTypeId = CheckValue('VehicleTypeId','n');

if ($VehicleTypeId==2 || $VehicleTypeId==9) {
    $VehicleMass = 0.0;
}

$controllers = $_POST['ControllerId'];
$first_controller=$controllers[0];

$unique = array();
foreach($controllers as $value) {
    if($value != 0) {
        if (isset($unique[$value])) {
            header("location: ".$str_BackPage."&Id=".$FineId."&error=Si prega di non scegliere lo stesso accertatore più di una volta!");
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
//             header("location: ".$str_BackPage."&Id=".$FineId."&error=Si prega di non scegliere lo stesso articolo più di una volta!");
//             DIE;
//         }
//         $unique_article[$value_article] = '';
//     }
// }

$ArticleId = CheckValue('ArticleId_1','n');
$Fee = $_POST['Fee_1'];
$MaxFee= $_POST['MaxFee_1'];
$PrefectureFee = CheckValue('PrefectureFee_1','f');
$PrefectureDate = CheckValue('PrefectureDate_1','s');

$DetectorId = CheckValue('DetectorId','n');

if ($DetectorId=="") $DetectorId = 0;

$ViolationTypeId = CheckValue('ViolationTypeId','n');

//$str_WhereRule = ($RuleTypeId==1) ? " AND RuleTypeId=1" : " AND RuleTypeId!=1";

$DepartmentId = CheckValue('DepartmentId','n');

$AllFolder = CheckValue('AllFolder','s');

$Documentation = CheckValue('Documentation','s');


$Locality= CheckValue('Locality','s');
if($Locality=="")$Locality = $_SESSION['cityid'];

$code = CheckValue('Code','s');
$CodePrefix = CheckValue('InputPrefix','s');
$NumberBlock = CheckValue('InputBlockNumber','n');

if($InsertionType != 3){
    if ($code != ""){
        if ($CodePrefix != "")
            $code .= '/'.$CodePrefix;
            
        if ($NumberBlock != "")
            $code .= '/'.$NumberBlock;
            
            $code .= '/'.$_SESSION['year'];
    }
} 

$str_ReasonOwner = CheckValue('ReasonOwner','s');

$FineChiefControllerId = CheckValue('FineChiefControllerId','s');

if (isset($_POST['Controlli'])){
    $eludiControlli = 1;
}else{
    $eludiControlli = 0;
}


$rs->Start_Transaction();

//DOCUMENTATION
$rs_row = $rs->Select('Fine','Id='.$FineId);
$r_row = mysqli_fetch_array($rs_row);
$DocFolderNational = ($InsertionType == 1) ? NATIONAL_FINE : NATIONAL_VIOLATION;
$DocFolderForeign = ($InsertionType == 1) ? FOREIGN_FINE : FOREIGN_VIOLATION;
$isStillForeign = false;

if (($r_row['CountryId']!='Z000') && ($CountryId!='Z000'))
    $isStillForeign = true;

if($CountryId!=$r_row['CountryId'] && !$isStillForeign){
    
    if($CountryId=='Z000') {
        if (!is_dir($DocFolderNational."/".$_SESSION['cityid'])) {
            mkdir($DocFolderNational."/".$_SESSION['cityid'], 0777);
        }
        if (!is_dir($DocFolderNational."/".$_SESSION['cityid']."/".$FineId)) {
            mkdir($DocFolderNational."/".$_SESSION['cityid']."/".$FineId, 0777);
        }
        $str_OldFolder = $DocFolderForeign;
        $str_NewFolder = $DocFolderNational;
        
    } else {
        if (!is_dir($DocFolderForeign."/".$_SESSION['cityid'])) {
            mkdir($DocFolderForeign."/".$_SESSION['cityid'], 0777);
        }
        if (!is_dir($DocFolderForeign."/".$_SESSION['cityid']."/".$FineId)) {
            mkdir($DocFolderForeign."/".$_SESSION['cityid']."/".$FineId, 0777);
        }
        
        $str_OldFolder = $DocFolderNational;
        $str_NewFolder = $DocFolderForeign;
    }
    
    $rs_row = $rs->Select('FineDocumentation','FineId='.$FineId);
    while($r_row = mysqli_fetch_array($rs_row)){
        copy($str_OldFolder."/".$_SESSION['cityid']."/".$FineId."/".$r_row['Documentation'],$str_NewFolder."/".$_SESSION['cityid']."/".$FineId."/".$r_row['Documentation']);
        if (file_exists($str_NewFolder."/".$_SESSION['cityid']."/".$FineId."/".$r_row['Documentation'])) {
            unlink($str_OldFolder."/".$_SESSION['cityid']."/".$FineId."/".$r_row['Documentation']);
        }
    }
    
    rmdir($str_OldFolder."/".$_SESSION['cityid']."/".$FineId);
}

//FINE
$a_Fine = array(
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$code),
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
    array('field'=>'FineNotificationDate','selector'=>'field','type'=>'date'),
    array('field'=>'FineDate','selector'=>'field','type'=>'date'),
    //array('field'=>'KindCreateDate','selector'=>'field','type'=>'date'),
    //array('field'=>'KindSendDate','selector'=>'field','type'=>'date'),

    array('field'=>'KindCreateDate','selector'=>'field','type'=>'date'),
    array('field'=>'KindSendDate','selector'=>'field','type'=>'date'),

    array('field'=>'FineTime','selector'=>'field','type'=>'str'),
    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$first_controller,'settype'=>'int'),
    array('field'=>'FineChiefControllerId','selector'=>'value','type'=>'int','value'=>$FineChiefControllerId,'settype'=>'int'),
    array('field'=>'ControllerDate','selector'=>'field','type'=>'date'),
    array('field'=>'ControllerTime','selector'=>'field','type'=>'str'),
    array('field'=>'TimeTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'StreetTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'VehicleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
    array('field'=>'TemporaryPlate','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'VehicleCountry','selector'=>'field','type'=>'str'),
    array('field'=>'CountryId','selector'=>'field','type'=>'str'),
    array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$DepartmentId,'settype'=>'int'),
    array('field'=>'VehicleBrand','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleModel','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleColor','selector'=>'field','type'=>'str'),
    array('field'=>'Note','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$VehicleMass,'settype'=>'flt'),
    array('field'=>'ChkControl','selector'=>'value','type'=>'int','value'=>$eludiControlli,'settype'=>'int'),
    //array('field'=>'NoteProcedure','selector'=>'field','type'=>'str'),
);

$rs->Update('Fine',$a_Fine, 'Id='.$FineId);

$rs->Delete('FineAnomaly', 'FineId='.$FineId);
$rs->Delete('FineAdditionalController','FineId='.$FineId);

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
    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
    array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=> $ViolationTypeId,'settype'=>'int'),
    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
    array('field'=>'DetectorId','selector'=>'value','type'=>'int','value'=>$DetectorId,'settype'=>'int'),
    array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
    array('field'=>'SpeedLimit','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'SpeedControl','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Speed','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'TimeTLightFirst','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'TimeTLightSecond','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'LicensePoint','selector'=>'value','type'=>'int', 'value'=>$TotalPoints,'settype'=>'int'),
    array('field'=>'PrefectureFee','selector'=>'value','type'=>'flt','value'=>$PrefectureFee,'settype'=>'flt'),
    array('field'=>'PrefectureDate','selector'=>'value','type'=>'date','value'=>$PrefectureDate),
);

$rs->Update('FineArticle',$a_FineArticle, 'FineId='.$FineId);

$rs_Article= $rs->Select("V_Article","Id=". $ArticleId ." AND CityId='". $_SESSION['cityid'] ."' AND Year=". $_SESSION['year']);
$r_Article = mysqli_fetch_assoc($rs_Article);
$b_126Bis = $r_Article['126Bis'] >= 1 ? true : false ;
$b_180 = $r_Article['PresentationDocument'] >= 1 ? true : false ;
if ($b_180 == false) {
    //controllo che non sia 80/14 o 193/2
    if (!empty($KindCreateDate) && !empty($KindSendDate)
        && (($r_Article['Article']=193 AND $r_Article['Paragraph']='2') || ($r_Article['Article']=80 AND $r_Article['Paragraph']='14'))) {
            
        $b_180 = true;
    }
}

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
}else {
    $a_FineOwner [] = array('field' => 'AdditionalDescriptionIta', 'selector' => 'value', 'type' => 'str', 'value' => '');
}

$find = $rs->SelectQuery("SELECT * FROM FineOwner WHERE FineId = $FineId");
$find_num = mysqli_num_rows($find);
if ($find_num==0){
    if ($AdditionalSanctionText!="" || $str_ReasonOwner != "" || $str_ArticleText!=""){
        $rs->Insert('FineOwner', $a_FineOwner);
    }
}else{
    $rs->Update('FineOwner',$a_FineOwner,"FineId=".$FineId);
}

$rs->Delete('FineAdditionalArticle', 'FineId='.$FineId);

//INIZIO blocco esame flag su articoli addizionali
if($n_ArticleNumber>1){
    for($i=2;$i<=$n_ArticleNumber;$i++){
        
        $ArticleId = CheckValue('ArticleId_'.$i,'n');
        $Fee = CheckValue('Fee_'.$i,'n');
        $MaxFee = CheckValue('MaxFee_'.$i,'n');
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
                if(!$b_180) {
                    $b_180 = $r_Article['PresentationDocument'] >= 1 ? true : false ;
                    //se falso controllo tipo articolo
                    if(!$b_180) {
                        // articoli 80/14 o 193/2
                        if (!empty($KindCreateDate) && !empty($KindSendDate)
                            && (($r_Article['Article']=193 AND $r_Article['Paragraph']='2') || ($r_Article['Article']=80 AND $r_Article['Paragraph']='14'))) {
                            
                            $b_180 = true;
                        }
                    }
                }
            }
        }
    }
}
//FINE blocco esame flag su articoli addizionali

if(strlen($Documentation)>0){
    
    switch ($InsertionType){
        case 1: 
            $TypeFolder = REPORT_FOLDER;
            $DocFolderN = NATIONAL_FINE;
            $DocFolderF = FOREIGN_FINE;
            break;
        case 1:
            $TypeFolder = WARNING_FOLDER;
            $DocFolderN = NATIONAL_VIOLATION;
            $DocFolderF = FOREIGN_VIOLATION;
            break;
        case 3:
            $TypeFolder = VIOLATION_FOLDER;
            $DocFolderN = NATIONAL_VIOLATION;
            $DocFolderF = FOREIGN_VIOLATION;
            break;
        default:
            $TypeFolder = REPORT_FOLDER;
            $DocFolderN = NATIONAL_FINE;
            $DocFolderF = FOREIGN_FINE;
    }
    
    $checkSize = explode("/",$Documentation);
    
    //var_dump($checkSize);
    if (count($checkSize)==5){
        $all_files = glob($TypeFolder."/".$_SESSION['cityid']."/".$checkSize[3]."/*.*");
        $newLink = "/".$checkSize[3]."/";
    }else{
        $all_files = glob($TypeFolder."/".$_SESSION['cityid']."/*.*");
        $newLink = null;
    }
    
    $str_Folder = ($CountryId == 'Z000') ? $DocFolderN : $DocFolderF;
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
            header("location: ".impostaParametriUrl(array('P' => $back), 'mgmt_report_upd.php'.$str_GET_Parameter.'&Id='.$FineId.'&error=Si prega di scegliere due immagini!'));
            DIE;
        }
    }
    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'])) {
        mkdir($str_Folder . "/" . $_SESSION['cityid'], 0777);
    }
    if (!is_dir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
        mkdir($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
    }
        
    foreach ($moveArray as $val){
        if (in_array($val, $arr)){
            copy($TypeFolder."/".$_SESSION['cityid']."/".$newLink.$val, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val);
            if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$val)) {
                unlink($TypeFolder ."/".$_SESSION['cityid']."/".$newLink.$val);
            } else {
                echo "Poblemi con la creazione del documento: ".$val;
                DIE;
            }
            $a_FineDocumentation = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$val),
                array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2, 'settype'=>'int'),
                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
            );
            $rs->Insert('FineDocumentation',$a_FineDocumentation);
        } else {
            echo "Match not found";
        }
    }
}

if ($TrespasserType != 0){
    //CASO NON INVARIATO
    
    //Controlla se sono erano importate da Maggioli conservo il flag 
    $rs_FineTrespasser = $rs->Select('FineTrespasser','FineId='.$FineId);
    $vecchiTrasgessori = $rs->getResults($rs_FineTrespasser);
    $vecchiTrasgessori = array_column($vecchiTrasgessori, null, 'TrespasserId');
    
    $rs->Delete('FineTrespasser', 'FineId='.$FineId);
    
    $FineDate = CheckValue('FineDate','s');
    $oldDate = "";
    $NotificationDate = null;
    $chk_NotificationDate= true;
    $notification_Type = array();
    $tres = array();
    
    $a_TrespasserId = array();
    $a_TrespasserTypeId = array();
    if($TrespasserType==1){
        $a_TrespasserTypeId[10] = 1;
        $a_TrespasserTypeId[15] = 15;
        $a_TrespasserId[10]=0;
        $a_TrespasserId[15]=0;
    } else if($TrespasserType==3){
        $a_TrespasserTypeId[10] = 10;
        $a_TrespasserTypeId[11] = 11;
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
        $a_TrespasserTypeId[10] = 2;
        $a_TrespasserTypeId[11] = 3;
        $a_TrespasserTypeId[15] = 15;
        $a_TrespasserTypeId[16] = 16;
        $a_TrespasserId[10]=0;
        $a_TrespasserId[11]=0;
        $a_TrespasserId[15]=0;
        $a_TrespasserId[16]=0;
    }
    
    foreach($a_TrespasserId as $key=>$value) {
    
        $TrespasserId = CheckValue('TrespasserId'.$key,'n');
    
        if($TrespasserId > 0){
            array_push($tres,$TrespasserId);
            $a_TrespasserId[$key] = $TrespasserId;
            $TrespasserTypeId = $a_TrespasserTypeId[$key];
            $NotificationDate = CheckValue('NotificationDate_'.$key,'s');
            $OwnerAdditionalFee = CheckValue('OwnerAdditionalFee', 'f');
            
    
            $a_FineTrespasser = array(
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                array('field'=>'CustomerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$CustomerAdditionalFee, 'settype'=>'flt'),
            );
            
            //debugArray($vecchiTrasgessori);
            if (isset($vecchiTrasgessori[$TrespasserId]) && $vecchiTrasgessori[$TrespasserId]['AssociatedOnImport'] == 1) {
                //echo "<br>".$vecchiTrasgessori[$TrespasserId]['AssociatedOnImport']; 
                $a_FineTrespasser[] = array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$vecchiTrasgessori[$TrespasserId]['Note'], 'settype'=>'str');
                $a_FineTrespasser[] = array('field'=>'AssociatedOnImport','selector'=>'value','type'=>'int','value'=>$vecchiTrasgessori[$TrespasserId]['AssociatedOnImport'], 'settype'=>'int');
                $a_FineTrespasser[] = array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>$vecchiTrasgessori[$TrespasserId]['RegDate']);
                $a_FineTrespasser[] = array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>$vecchiTrasgessori[$TrespasserId]['RegTime']);
                //echo "<br>".$OwnerAdditionalFee.".";
                if(($TrespasserTypeId == 11 || $TrespasserTypeId == 3) && $OwnerAdditionalFee>0.0 && $vecchiTrasgessori[$TrespasserId]['OwnerAdditionalFee'] != $OwnerAdditionalFee)
                    $a_FineTrespasser[] = array('field'=>'OwnerAdditionalFee','selector'=>'value','type'=>'flt','value'=>(float)$OwnerAdditionalFee, 'settype'=>'flt');
                else    
                    $a_FineTrespasser[] = array('field'=>'OwnerAdditionalFee','selector'=>'value','type'=>'flt','value'=>(float)$vecchiTrasgessori[$TrespasserId]['OwnerAdditionalFee'], 'settype'=>'flt');
            } else {
                $a_FineTrespasser[] = array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"));
                $a_FineTrespasser[] = array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i:s"));
                $a_FineTrespasser[] = array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']);
            }
                   
            if($TrespasserType == 2 || $TrespasserType==3 || $TrespasserType==4){
                if ($TrespasserTypeId == 11 || $TrespasserTypeId == 3)
                    $a_FineTrespasser[] = array('field'=>'ReceiveDate','selector'=>'field','type'=>'date');
            }
            
            if($NotificationDate!=""){
                if ($NotificationDate < $FineDate){
                    $oldDate = $NotificationDate;
                }
                $NotificationType = CheckValue('NotificationType_'.$key,'n');
                array_push($notification_Type,$NotificationType);
                $a_FineTrespasser[] = array('field'=>'FineCreateDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
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
                
                $chk_NotificationDate=false;
            }
            //debugArray($a_FineTrespasser); 
            $rs->Insert('FineTrespasser',$a_FineTrespasser);
    
            //TODO probabilmente codice morto a causa del fatto che mgmt_report_upd non valorizza 
            //il campo nascosto LicenseDatePropretario o LicenseDateTrasgressore, valutare se va ripristinata o cancellata
            if($b_126Bis){
                $rs_FineCommunication = $rs->Select('FineCommunication','FineId='.$FineId.' AND TrespasserId='.$TrespasserId);
                $n_FineCommunication = mysqli_num_rows($rs_FineCommunication);
                
                $a_FineCommunication = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
                    array('field'=>'CommunicationDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                );
    
                $checkLicense = ($TrespasserTypeId == 10) ? $_POST['LicenseDatePropretario'] : $_POST['LicenseDateTrasgressore'];
                if ( $n_NotificationType == 2  && $checkLicense !=""){
                    if ($n_FineCommunication>0)
                        $rs->Update('FineCommunication',$a_FineCommunication);
                    else
                        $rs->Insert('FineCommunication',$a_FineCommunication);
                }
            }
        }
    }
    
    if ($TrespasserType == 1 && $a_TrespasserId[10] != 0)
            $StatusTypeId = 10;
    else if ($TrespasserType == 3 && $a_TrespasserId[10] != 0 && $a_TrespasserId[11] != 0)
            $StatusTypeId = 10;
    else if ($TrespasserType == 4 && $a_TrespasserId[10] != 0 && $a_TrespasserId[11] != 0 && $a_TrespasserId[12] != 0)
            $StatusTypeId = 10;
    else if ($a_TrespasserId[10] != 0 && $a_TrespasserId[11] != 0)
            $StatusTypeId = 10;
    
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
        
        //Elimina esclusivamente i record da FineHistory Con NotificationTypeId = Invio verbale (2) o Flusso verbale (6)
        $rs->Delete('FineHistory', "FineId=$FineId AND NotificationTypeId IN(2,6)");
        $rs->Delete('FineNotification', 'FineId='.$FineId);
        
        $rs_FineDocumentation = $rs->SelectQuery("SELECT Documentation FROM FineDocumentation WHERE FineId = $FineId and DocumentationTypeId = 2 ORDER BY Id");
        $DocumentName = mysqli_fetch_array($rs_FineDocumentation)['Documentation'];
        
        $NotificationTypeId     = 2;
        $CustomerFee            = $CustomerAdditionalFee;
        $NotificationFee        = 0.00;
        $ResearchFee            = 0.00;
        
        $FineDate = CheckValue('FineDate','s');
        $Date = $oldDate !="" ? $oldDate: $NotificationDate;
        if ($notification_Type[0]==1){ //su strada
            $resultId = 8;
        }elseif($notification_Type[0]==2){ //messo
            $resultId = 1;
        }elseif($notification_Type[0]==3){ //ufficio
            $resultId = 9;
        }
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
        );
        
        if (mysqli_num_rows($rs_FineDocumentation)>0)
            $aInsert[] = array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName);
            
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
        
        $r_TariffQuery = $rs->Select('V_FineTariff', "FineId=".$FineId);
        $r_Tariff = mysqli_fetch_array($r_TariffQuery);
        
        $HabitualProcedure = $r_Tariff['Habitual'];
        $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
        $LossLicenseProcedure = $r_Tariff['LossLicense'];
        $ReminderAdditionalFeeProcedure = 0;
        $PaymentProcedure               = 1; //CheckValue('Payment','n');
        $LicensePointProcedure          = CheckValue('TotalPoints','n');
        $PresentationDocumentProcedure  = $b_Presentation ? 0 : ($b_180 ? 1 : 0);//CheckValue('PresentationDocument','n');
        //Se l'articolo non prevede 126bis oppure la comunicazione 126bis è già stata creata, imposta 0 (NO)
        $BisProcedure                   = $b_Communication ? 0 : ($b_126Bis ? 1 : 0); //CheckValue('126Bis','n');
        $InjunctionProcedure            = 1;
        
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
        
        //se anche il tipo notifica generale è "Differita" si valutano le notiifche ai singoli trasgressori
        // $n_NotificationType tipo notifica generale
        //1 differita
        //2 su strada
        //$_POST['NotificationType_XX'] tipo di notifica per trasgressore
        //1 Su strada
        //2 Messo
        //3 Ufficio
        
        //se è notificato su strada assegno anche la data di notifica
        if ($n_NotificationType==2){
            $a_FineNotification[] = array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$Date);
        }
        
        $rs->Insert('FineNotification',$a_FineNotification);
        
        $StatusTypeId = 25;
        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
            
    } else {
        //Ramo in cui i trasgressori non sono notificati
        
        //esegue le operazioni sulle tabelle TMP
        updateTMP($b_ChangedArticles, $FineId, $isDeceased, $b_180, $b_126Bis, $b_Communication, $b_Presentation, $TotalPoints);
        
        //Elimina esclusivamente i record da FineHistory Con NotificationTypeId = Invio verbale (2) o Flusso verbale (6)
        $rs->Delete('FineHistory', "FineId=$FineId AND NotificationTypeId IN(2,6)");
        if ($InsertionType==1) $StatusTypeId =14;
        else if ($InsertionType==2) $StatusTypeId =13;

        $a_Fine = array(
            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
        );
        $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
    }
    
} else {
    //CASO INVARIATO
    
    //Controlla se sono cambiate le date o tipo di notifica dei trasgressori se Invariato è selezionato
    $rs_FineTrespasser = $rs->Select('FineTrespasser','FineId='.$FineId);
    $chk_NotificationDate = true;
    $notification_Type = array();
    $tres = array();
    
    if (mysqli_num_rows($rs_FineTrespasser)>0){
    
        while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){
            array_push($tres,$r_FineTrespasser['TrespasserId']);
            $updateNotifications = false;
            $a_FineTrespasserNotification = array();
            $a_CustomerAdditionalFees = array();
            $Type = $r_FineTrespasser['TrespasserTypeId'];
            
            //Spese addizionali
            $a_CustomerAdditionalFees[] = array('field'=>'CustomerAdditionalFee','selector'=>'value','type'=>'flt','value'=>$CustomerAdditionalFee, 'settype'=>'flt');
            $rs->Update('FineTrespasser',$a_CustomerAdditionalFees, 'FineId='.$FineId.' AND TrespasserTypeId='.$Type);
            
            $NewNotificationDate = CheckValue('NewNotificationDate_'.$Type,'s');
            $NewNotificationType = CheckValue('NewNotificationType_'.$Type,'s');
            $NewReceiveDate = CheckValue('NewReceiveDate','s');
            
            if($NewNotificationDate!=""){
                if ($NewNotificationDate < $FineDate) {
                    $oldDate = $NewNotificationDate;
                }
                array_push($notification_Type,$NewNotificationType);
            } else $chk_NotificationDate = false;
            
            if ($r_FineTrespasser['FineNotificationDate'] != $NewNotificationDate){
                $a_FineTrespasserNotification[] = array('field'=>'FineCreateDate','selector'=>'value','type'=>'date','value'=>$NewNotificationDate);
                $a_FineTrespasserNotification[] = array('field'=>'FineSendDate','selector'=>'value','type'=>'date','value'=>$NewNotificationDate);
                $a_FineTrespasserNotification[] = array('field'=>'FineNotificationDate','selector'=>'value','type'=>'date','value'=>$NewNotificationDate);
                $updateNotifications = true;
            }
            
            if ($r_FineTrespasser['FineNotificationType'] != $NewNotificationType){
                if ($NewNotificationType == "")
                    $a_FineTrespasserNotification[] = array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>NULL);
                else
                    $a_FineTrespasserNotification[] = array('field'=>'FineNotificationType','selector'=>'value','type'=>'int','value'=>$NewNotificationType, 'settype'=>'int');
                $updateNotifications = true;
            }
            
            if ($Type == 11){
                if ($r_FineTrespasser['ReceiveDate'] != $NewReceiveDate){
                    $a_FineTrespasserNotification[] = array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>$NewReceiveDate);
                    $updateNotifications = true;
                }
            }
            

            if ($updateNotifications) $rs->Update('FineTrespasser',$a_FineTrespasserNotification, 'FineId='.$FineId.' AND TrespasserTypeId='.$Type);
        }
        
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
        
        if($chk_NotificationDate){
            
            //Elimina esclusivamente i record da FineHistory Con NotificationTypeId = Invio verbale (2) o Flusso verbale (6)
            $rs->Delete('FineHistory', "FineId=$FineId AND NotificationTypeId IN(2,6)");
            $rs->Delete('FineNotification', 'FineId='.$FineId);
            
            $rs_FineDocumentation = $rs->SelectQuery("SELECT Documentation FROM FineDocumentation WHERE FineId = $FineId and DocumentationTypeId = 2 ORDER BY Id");
            $DocumentName = mysqli_fetch_array($rs_FineDocumentation)['Documentation'];
            
            $NotificationTypeId     = 2;
            $CustomerFee            = $CustomerAdditionalFee;
            $NotificationFee        = 0.00;
            $ResearchFee            = 0.00;
            
            $FineDate = CheckValue('FineDate','s');
            $Date = $oldDate !="" ? $oldDate: $NewNotificationDate;
            if ($notification_Type[0]==1){
                $resultId = 8;
            }elseif($notification_Type[0]==2){
                $resultId = 1;
            }elseif($notification_Type[0]==3){
                $resultId = 9;
            }
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
            );
            
            if (mysqli_num_rows($rs_FineDocumentation)>0)
                $aInsert[] = array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName);
                
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
                
                $r_TariffQuery = $rs->Select('V_FineTariff', "FineId=".$FineId);
                $r_Tariff = mysqli_fetch_array($r_TariffQuery);
                
                $HabitualProcedure = $r_Tariff['Habitual'];
                $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                $LossLicenseProcedure = $r_Tariff['LossLicense'];
                $ReminderAdditionalFeeProcedure = 0;
                $PaymentProcedure               = 1; //CheckValue('Payment','n');
                $LicensePointProcedure          = CheckValue('TotalPoints','n');
                $PresentationDocumentProcedure  = $b_Presentation ? 0 : ($b_180 ? 1 : 0);//CheckValue('PresentationDocument','n');
                //Se l'articolo non prevede 126bis oppure la comunicazione 126bis è già stata creata, imposta 0 (NO)
                $BisProcedure                   = $b_Communication ? 0 : ($b_126Bis ? 1 : 0); //CheckValue('126Bis','n');
                $InjunctionProcedure            = 1;
                
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
                
                $rs->Insert('FineNotification',$a_FineNotification);
                
                $StatusTypeId = 25;
                $a_Fine = array(
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
                );
                $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
                
        } else {
            //esegue le operazioni sulle tabelle TMP
            updateTMP($b_ChangedArticles, $FineId, $isDeceased, $b_180, $b_126Bis, $b_Communication, $b_Presentation, $TotalPoints);
            
            //Elimina esclusivamente i record da FineHistory Con NotificationTypeId = Invio verbale (2) o Flusso verbale (6)
            $rs->Delete('FineHistory', "FineId=$FineId AND NotificationTypeId IN(2,6)");
            if ($InsertionType==1) $StatusTypeId =14;
            else if ($InsertionType==2) $StatusTypeId =13;

            $a_Fine = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
            );
            $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
        }
    }
}



if ($n_CloseFine == 1){
    $StatusTypeId = 32;
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
    );
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
}


$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("location: ".$Back.$Parameters);
