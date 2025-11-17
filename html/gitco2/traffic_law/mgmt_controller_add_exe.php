<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');

$CityId = $_SESSION['cityid'];

$Name = CheckValue('Name', 's');
$Code = CheckValue('Code', 's');
$ToDate = CheckValue('ToDate', 's');
$FromDate = CheckValue('FromDate', 's');

$Qualification = CheckValue('Qualification', 's');

$str_Error = '';
$str_Warning = '';

// if($_REQUEST['Qualification'] != ""){
//     $rs_Qualification = $rs->Select("QualificationType","Id=".CheckValue('Qualification', 's'));
//     $r_Qualification = mysqli_fetch_array($rs_Qualification);
//     $QualificationTypeId = (int)$r_Qualification['Id'];
//     $Qualification = $r_Qualification['Description'];
// } else {
//     $QualificationTypeId = NULL;
//     $Qualification = "";
// }

$rs->Start_Transaction();

$ins = array(
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
    array('field'=>'ControllerTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'QualificationTypeId', 'selector'=>'value', 'type' => 'int','value'=>NULL),//TODO da considerare se verranno modificate le qualifiche
    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$Name),
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code != '' ? $Code: null),
    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>''),
    array('field'=>'Disabled','selector'=>'value','type'=>'int','value'=>0),
    array('field'=>'DigitalSign','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'FineDigitalSign','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'NotificationDigitalSign','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'ChiefController','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'FromDate','selector'=>'field','type'=>'date'),
    array('field'=>'ToDate','selector'=>'field','type'=>'date'),
    array('field'=>'Note','selector'=>'field','type'=>'str'),
    array('field'=>'MegaspCode','selector'=>'value','type'=>'str','value'=>' '),
    array('field'=>'Qualification', 'selector'=>'value', 'type' => 'str', 'value' => $Qualification),
);

if($_FILES["fileToUpload"]['size'] > 0){
    $Sign = $_FILES["fileToUpload"]["name"];
    $Path = SIGN_FOLDER.'/'.$CityId.'/'.$Sign;
    
    if (!is_dir(SIGN_FOLDER.'/'.$CityId.'/')) {
        mkdir(SIGN_FOLDER.'/'.$CityId.'/', 0777);
    }
    
    $ins[] = array('field'=>'Sign','selector'=>'value','type'=>'str','value'=>$Sign);
}

if($Code != ''){
    if ($FromDate != '' && $ToDate != '' && DateInDB($FromDate) >= DateInDB($ToDate)){
        $str_Error = "Intervallo di date non valido.";
    }
    
    if($str_Error == ''){
        $CheckCode = $rs->SelectQuery("SELECT Id,Code,FromDate,ToDate,Name FROM Controller WHERE Code = '".$Code."' AND CityId ='".$CityId."' ORDER BY Id DESC LIMIT 1");
        if (mysqli_num_rows($CheckCode) > 0) {
            $r_CheckCode = mysqli_fetch_assoc($CheckCode);
            if (empty($r_CheckCode['ToDate'])){
                $str_Error = "Accertatore con matricola $Code già esistente e privo di data di fine validità. Specificare la data di fine validità per {$r_CheckCode['Name']} prima di inserire un nuovo accertatore per questa matricola.";
            } else if (DateInDB($FromDate) <= $r_CheckCode['ToDate']) {
                $str_Error = "É stata specificata una data di inizio validità non successiva alla data di fine validità dell' accertatore {$r_CheckCode['Name']} con matricola $Code già esistente. Specificare una data successiva al: ".DateOutDB($r_CheckCode['ToDate']).".";
            }
        }
    }
}

if($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
} else {
    $rs->Insert("Controller",$ins);
    
    if($_FILES["fileToUpload"]['size'] > 0 && !move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $Path)){
        $str_Warning .= 'Errore nel salvataggio dell\'immagine della firma, controllare le cartelle';
    }
    
    if($str_Warning != ''){
        $_SESSION['Message']['Warning'] = $str_Warning;
    } else $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

$rs->End_Transaction();

if($str_Error != ''){
    $_SESSION['postdata']['Controller']['add'] = $_POST;
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_controller.php'), "mgmt_controller_add.php".$Filters));
} else {
    header("location: mgmt_controller.php".$Filters);
}

