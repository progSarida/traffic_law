<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$rs->Start_Transaction();

$Filters = CheckValue('Filters','s');

$FineId = CheckValue('FineId','n');
$TrespasserTypeId = CheckValue('TrespasserTypeId','n');
$MainTrespasserId = CheckValue('MainTrespasserId','n');
$TrespasserId = CheckValue('TrespasserId','n');

$CheckIncomplete = CheckValue('CheckIncomplete', 'n');

$CommunicationDate = CheckValue('CommunicationDate','s');
$CommunicationProtocol = CheckValue('CommunicationProtocol','s');

$LicenseDate = CheckValue('LicenseDate','s');
$LicenseDateDriver = CheckValue('LicenseDateDriver','s');

$DocumentTypeId =  CheckValue('DocumentTypeId','n');
$DocumentTypeIdDriver =  CheckValue('DocumentTypeIdDriver','n');
$LicenseCategory = CheckValue('LicenseCategory','s');
$LicenseCategoryDriver = CheckValue('LicenseCategoryDriver','s');
$LicenseNumber = CheckValue('LicenseNumber','s');
$LicenseNumberDriver = CheckValue('LicenseNumberDriver','s');
$LicenseOffice = CheckValue('LicenseOffice','s');
$LicenseOfficeDriver = CheckValue('LicenseOfficeDriver','s');

$DocumentCountryId = CheckValue('DocumentCountryId','s');
$DocumentCountryIdDriver = CheckValue('DocumentCountryIdDriver','s');

if($LicenseDate!= ""){
    $a_LicenseDate = explode("/",$LicenseDate);
    $LicenseDate = $a_LicenseDate[2]."-".$a_LicenseDate[1]."-".$a_LicenseDate[0];
}
else
    $LicenseDate = null;

if($LicenseDateDriver!= ""){
    $a_LicenseDate = explode("/",$LicenseDateDriver);
    $LicenseDateDriver = $a_LicenseDate[2]."-".$a_LicenseDate[1]."-".$a_LicenseDate[0];
}
else
    $LicenseDateDriver = null;



if($TrespasserTypeId==1){
    //$DocumentTypeId = 1;

} else{
    $a_Trespasser = array(
        array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeIdDriver, 'settype'=>'int'),
        array('field'=>'LicenseCategory','selector'=>'value','type'=>'str','value'=>$LicenseCategoryDriver),
        array('field'=>'LicenseNumber','selector'=>'value','type'=>'str','value'=>$LicenseNumberDriver),
        array('field'=>'LicenseDate','selector'=>'value','type'=>'date','value'=>$LicenseDateDriver),
        array('field'=>'LicenseOffice','selector'=>'value','type'=>'str','value'=>$LicenseOfficeDriver),
        array('field'=>'DocumentCountryId','selector'=>'value','type'=>'str','value'=>$DocumentCountryIdDriver)
    );

    $rs->Update('Trespasser',$a_Trespasser, 'Id='.$TrespasserId);

    $TrespasserTypeId = 3;

    $a_FineCommunication = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId, 'settype'=>'int'),
        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
        array('field'=>'CommunicationDate','selector'=>'value','type'=>'date','value'=>DateInDB($CommunicationDate)),
        array('field'=>'CommunicationProtocol','selector'=>'value','type'=>'str','value'=>$CommunicationProtocol),
        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    );
    
    if($CheckIncomplete > 0)
        $a_FineCommunication[] = array('field'=>'Incomplete','selector'=>'field','type'=>'int','settype'=>'int');

    $rs->Insert('FineCommunication',$a_FineCommunication);

    $TrespasserTypeId = 2;
}

$a_FineCommunication = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$MainTrespasserId, 'settype'=>'int'),
    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId, 'settype'=>'int'),
    array('field'=>'CommunicationDate','selector'=>'value','type'=>'date','value'=>DateInDB($CommunicationDate)),
    array('field'=>'CommunicationProtocol','selector'=>'value','type'=>'str','value'=>$CommunicationProtocol),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
);

if($CheckIncomplete > 0)
    $a_FineCommunication[] = array('field'=>'Incomplete','selector'=>'field','type'=>'int','settype'=>'int');

$rs->Insert('FineCommunication',$a_FineCommunication);


$a_Trespasser = array(
    array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeId, 'settype'=>'int'),
    array('field'=>'LicenseCategory','selector'=>'value','type'=>'str','value'=>$LicenseCategory),
    array('field'=>'LicenseNumber','selector'=>'value','type'=>'str','value'=>$LicenseNumber),
    array('field'=>'LicenseDate','selector'=>'value','type'=>'date','value'=>$LicenseDate),
    array('field'=>'LicenseOffice','selector'=>'value','type'=>'str','value'=>$LicenseOffice),
    array('field'=>'DocumentCountryId','selector'=>'value','type'=>'str','value'=>$DocumentCountryId)
);

$rs->Update('Trespasser',$a_Trespasser, 'Id='.$MainTrespasserId);


if(mysqli_num_rows($rs->Select('FineNotification', "FineId=$FineId")) > 0){
    $a_FineNotification = array(
        array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0, 'settype'=>'int'),
    );
    $rs->Update('FineNotification',$a_FineNotification, 'FineId='.$FineId);
    
} else {
    $rs_TMP_PaymentProcedure = $rs->Select('TMP_126BisProcedure', "FineId=" .$FineId);
    if (mysqli_num_rows($rs_TMP_PaymentProcedure) == 0) {
        $a_126TMP_BisProcedure = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
        );
        $rs->Insert('TMP_126BisProcedure',$a_126TMP_BisProcedure);
    }
}


$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";

header("location: ".impostaParametriUrl(array('Filter' => 1), 'mgmt_communication.php'.$Filters));