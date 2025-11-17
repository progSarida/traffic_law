<?php 
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');

$CityId = $_SESSION['cityid'];

$Id = CheckValue('Id', 's');
$Name = CheckValue('Name', 's');
$Code = CheckValue('Code', 's');
$ToDate = CheckValue('ToDate', 's');
$FromDate = CheckValue('FromDate', 's');

$Qualification = CheckValue('Qualification', 's');

$str_Error = '';
$str_Warning = '';

$rs->Start_Transaction();

$upd = array(
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
    array('field'=>'ControllerTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'QualificationTypeId', 'selector'=>'value', 'type' => 'int','value'=>NULL),//TODO da considerare se verranno modificate le qualifiche
    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$Name),
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code != '' ? $Code: null),
    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>''),
    array('field'=>'Disabled','selector'=>'chkbox','type'=>'int','settype'=>'int'),
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
    
    $upd[] = array('field'=>'Sign','selector'=>'value','type'=>'str','value'=>$Sign);
}

if ($Code != ''){
    if ($FromDate != '' && $ToDate != '' && DateInDB($FromDate) >= DateInDB($ToDate)){
        $str_Error = "Intervallo di date non valido.";
    }
    
    if ($str_Error == ''){
        $rs_PreviousController = $rs->SelectQuery("SELECT Id,Code,FromDate,ToDate,Name FROM Controller WHERE Code = '".$Code."' AND CityId ='".$CityId."' AND Id < $Id ORDER BY Id DESC LIMIT 1");
        if(mysqli_num_rows($rs_PreviousController) > 0){
            $r_PreviousController = mysqli_fetch_assoc($rs_PreviousController);
            if (DateInDB($FromDate) <= $r_PreviousController['ToDate'])
                $str_Error = "É stata specificata una data di inizio validità inferiore alla data di fine validità del precedente accertatore con matricola $Code: {$r_PreviousController['Name']}. Specificare una data successiva al: ".DateOutDB($r_PreviousController['ToDate']).".";
        }
    }
    if ($str_Error == ''){
        $rs_NextController = $rs->SelectQuery("SELECT Id,Code,FromDate,ToDate,Name FROM Controller WHERE Code = '".$Code."' AND CityId ='".$CityId."' AND Id > $Id ORDER BY Id ASC LIMIT 1");
        if(mysqli_num_rows($rs_NextController) > 0){
            $r_NextController = mysqli_fetch_assoc($rs_NextController);
            if (DateInDB($ToDate) >= $r_NextController['FromDate'])
                $str_Error = "É stata specificata una data di fine validità superiore alla data di inizio validità del prossimo accertatore con matricola $Code: {$r_NextController['Name']}. Specificare una data precedente al: ".DateOutDB($r_NextController['FromDate']).".";
        }
    }
}

if($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
} else {
    $rs->Update('Controller',$upd,"Id=".$Id);
    
    if($_FILES["fileToUpload"]['size'] > 0 && !move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $Path)){
        $str_Warning .= 'Errore nel salvataggio dell\'immagine della firma, controllare le cartelle';
    }
    
    if($str_Warning != ''){
        $_SESSION['Message']['Warning'] = $str_Warning;
    } else $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

$rs->End_Transaction();

if($str_Error != ''){
    $_SESSION['postdata']['Controller'][$Id] = $_POST;
    header("location: ".impostaParametriUrl(array('Id' => $Id, 'P' => 'mgmt_controller.php'), "mgmt_controller_upd.php".$Filters));
} else {
    header("location: mgmt_controller.php".$Filters);
}

