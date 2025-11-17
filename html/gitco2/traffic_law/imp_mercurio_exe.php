<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_postalCharge.php");
require_once(INC."/initialization.php");
ini_set('max_execution_time', 5000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$a_City = array();
$a_NotificationCount = array();

$path = PUBLIC_FOLDER."/_MERCURIO_II_/IMPORT/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;


//$file = fopen($path.$ImportFile,  "r");
//$delimiter = ";";


$rs_Result = $rs->Select('Result', "Disabled=0");
$a_chk_Result = array();
while ($r_Result = mysqli_fetch_array($rs_Result)){
    $a_chk_Result[] = $r_Result['Description'];
    $a_Result[$r_Result['Description']] = $r_Result['Id'];
}


ini_set("auto_detect_line_endings", true);
$csv = array();
$lines = file($path . $ImportFile);
foreach ($lines as $key_line => $a_line)
{
    $csv[$key_line] = str_getcsv($a_line,";");
    foreach ($csv[$key_line] as $key => $value)
    {
        $csv[$key_line][$key] = trim($value);
    }
}

trigger_error("MERCURIO INIZIO inserisce dati notifiche su db", E_USER_WARNING);
foreach ($csv as $row) {
    //	$row = fgetcsv($file, 1000, $delimiter);
    if(isset($row[0]) && strtolower($row[0])!="cod_comune"){
        
        $rs->Start_Transaction();
        
        
        $CityId = $row[0];
        
        $a_Row = explode("/",$row[1]);
        $ProtocolId = $a_Row[0];
        $ProtocolYear = $a_Row[1];
        
        $NotificationDate = $row[5];
        $LetterNumber = $row[13];
        $ReceiptNumber = $row[6];
        
        $NotificationType = trim($row[7]);
        $a_Notification = explode("-",$NotificationType);
        if(isset($a_Notification[1])) $NotificationType = trim($a_Notification[0])." - ".trim($a_Notification[1]);
        
        
        $NotificationStatus = trim($row[8]);
        $a_Notification = explode("-",$NotificationStatus);
        if(isset($a_Notification[1])) $NotificationStatus = trim($a_Notification[0])." - ".trim($a_Notification[1]);
        
        $ImgFront = $row[10];
        $ImgBack = $row[11];
        
        //Nel caso i campi delle immagini siano di estensione diversa da jpg o uno di essi è vuoto, tento di dedurre i nomi attesi di entrambe
        if(!empty($ImgFront)){
            $a_ImgFront = pathinfo($ImgFront);
            $ImgFrontBase = str_replace("_F", "", $a_ImgFront['filename']);
            
            if($a_ImgFront['extension'] != 'jpg'){
                $ImgFront = $ImgFrontBase.'_F.jpg';
            } else $ImgFront = $a_ImgFront['basename'];
            
            if(empty($ImgBack)){
                $ImgBack = $ImgFrontBase.'_R.jpg';
            }
        }
        
        if(!empty($ImgBack)){
            $a_ImgBack = pathinfo($ImgBack);
            $ImgBackBase = str_replace("_R", "", $a_ImgBack['filename']);
            
            if($a_ImgBack['extension'] != 'jpg'){
                $ImgBack = $ImgBackBase.'_R.jpg';
            } else $ImgBack = $a_ImgBack['basename'];
            
            if(empty($ImgFront)){
                $ImgFront = $ImgBackBase.'_F.jpg';
            }
        }
        
        $SendDate = $row[14];
        $Box = $row[15];
        $Lot = $row[16];
        $Position = $row[17];
        
        $chkFine = "";
        $chkSendDate = "";
        $chkDeliveryDate = "";
        $chkNotification = "";
        
        $a_LogDate = explode(" ",$row[12]);
        $LogDate = $a_LogDate[0];
        
        
        $rs_Fine = $rs->Select('Fine', "CountryId='Z000' AND CityId='" . $CityId . "' AND ProtocolId=" . $ProtocolId ."  AND ProtocolYear=".$ProtocolYear." AND Id NOT IN(SELECT FineId Id FROM FineNotification) ");
        
        
        if (mysqli_num_rows($rs_Fine) == 0) {
            // todo Verbale non presente
            
            
        }else{
            
            if (! in_array($CityId, $a_City)) {
                $a_City[] = $CityId;
                $a_NotificationCount[$CityId] = 0;
            }
            
            
            $a_NotificationCount[$CityId] = $a_NotificationCount[$CityId]+1;
            
            
            for($i=1;$i<=mysqli_num_rows($rs_Fine);$i++){
                $r_Fine = mysqli_fetch_array($rs_Fine);
                
                $FineId=$r_Fine['Id'];
                $CountryId = $r_Fine['CountryId'];
                
                
                
                
                $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $FineId. " AND NotificationTypeId=6");
                $r_FineHistory = mysqli_fetch_array($rs_FineHistory);
                
                if($r_FineHistory['SendDate']!=DateInDB($SendDate)){
                    // todo Data invio non coincidente
                }
                if(is_null($r_FineHistory['DeliveryDate'])){
                    
                    $ResultId =  (in_array(trim($NotificationStatus), $a_chk_Result)) ? $a_Result[$NotificationStatus] : $a_Result[$NotificationType];
                    $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $FineId);
                    $r_Tariff = mysqli_fetch_array($rs_Tariff);
                    
                    $rs_Article = $rs->Select('V_FineArticle', "Id=" . $FineId);
                    $r_Article = mysqli_fetch_array($rs_Article);
                    
                    $LicensePointProcedure = $r_Tariff['LicensePoint'];
                    $rs_TMP_PaymentProcedure = $rs->Select('TMP_LicensePointProcedure', "FineId=" .$FineId);
                    if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                        $LicensePointProcedure = 0;
                        $rs->Delete('TMP_LicensePointProcedure', "FineId=".$FineId);
                    }
                    
                    //se l'articolo prevede la presentazione documenti per 180/8 controllo TMP_PresentationDocumentProcedure
                    $PresentationDocumentProcedure = $r_Tariff['PresentationDocument'];
                    $rs_TMP_PaymentProcedure = $rs->Select('TMP_PresentationDocumentProcedure', "FineId=" .$FineId);
                    if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                        $PresentationDocumentProcedure = 0;
                        $rs->Delete('TMP_PresentationDocumentProcedure', "FineId=".$FineId);
                    }
                    // se sull'articolo non è richiesta comunicazione art. 180/8 devo però controllare anche se è un 193/2 o 80/14
                    //(`F`.`KindCreateDate` is not null and `F`.`KindSendDate` is not null and ((`A`.`Article`=193 AND `A`.`Paragraph`='2') OR (`A`.`Article`=80 AND `A`.`Paragraph`='14')))
                    if($PresentationDocumentProcedure == 0) {
                        if (isset($r_Fine['KindCreateDate']) && isset($r_Fine['KindSendDate'])
                            && (($r_Article['Article']=193 AND $r_Article['Paragraph']='2') || ($r_Article['Article']=80 AND $r_Article['Paragraph']='14'))) {
                                
                                $PresentationDocumentProcedure = 1;
                                $rs_TMP_PaymentProcedure = $rs->Select('TMP_PresentationDocumentProcedure', "FineId=" .$FineId);
                                if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                                    $PresentationDocumentProcedure = 0;
                                    $rs->Delete('TMP_PresentationDocumentProcedure', "FineId=".$FineId);
                                }
                            }
                    }
                    
                    $BisProcedure = $r_Tariff['126Bis'];
                    $rs_TMP_PaymentProcedure = $rs->Select('TMP_126BisProcedure', "FineId=" .$FineId);
                    if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                        $BisProcedure = 0;
                        $rs->Delete('TMP_126BisProcedure', "FineId=".$FineId);
                    }
                    
                    //In questo caso il valore è opposto, se c'è il record su TMP è SI, altrimenti NO
                    $rs_ReminderAdditionalFeeProcedure = $rs->Select("TMP_ReminderAdditionalFeeProcedure", "FineId=". $FineId);
                    if (mysqli_num_rows($rs_ReminderAdditionalFeeProcedure) > 0)
                    {
                        $ReminderAdditionalFeeProcedure = 1;
                        $rs->Delete('TMP_ReminderAdditionalFeeProcedure', "FineId=".$FineId);
                    }
                    else
                    {
                        $ReminderAdditionalFeeProcedure = 0;
                    }
                    
                    $rs_InjunctionProcedure = $rs->Select("TMP_InjunctionProcedure", "FineId=". $FineId);
                    $InjunctionProcedure = (mysqli_num_rows($rs_InjunctionProcedure)==0) ? 1 : 0;
                    
                    
                    $HabitualProcedure = $r_Tariff['Habitual'];
                    $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                    $LossLicenseProcedure = $r_Tariff['LossLicense'];
                    
                    
                    $a_FineNotification = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>DateInDB($SendDate)),
                        array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>DateInDB($LogDate)),
                        array('field'=>'Box','selector'=>'value','type'=>'str','value'=>$Box),
                        array('field'=>'Lot','selector'=>'value','type'=>'str','value'=>$Lot),
                        array('field'=>'Position','selector'=>'value','type'=>'str','value'=>$Position),
                        array('field'=>'ReceiptNumber','selector'=>'value','type'=>'str','value'=>$ReceiptNumber),
                        array('field'=>'LetterNumber','selector'=>'value','type'=>'str','value'=>$LetterNumber),
                        array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
                        array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                        array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>$PresentationDocumentProcedure,'settype'=>'int'),
                        array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>$LicensePointProcedure,'settype'=>'int'),
                        array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>$HabitualProcedure,'settype'=>'int'),
                        array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>$SuspensionLicenseProcedure,'settype'=>'int'),
                        array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>$LossLicenseProcedure,'settype'=>'int'),
                        array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>$InjunctionProcedure,'settype'=>'int'),
                        array('field'=>'ReminderAdditionalFeeProcedure','selector'=>'value','type'=>'int','value'=>$ReminderAdditionalFeeProcedure,'settype'=>'int'),
                        array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                        array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                    );
                    
                    
                    $rs_TMP_PaymentProcedure = $rs->Select('TMP_PaymentProcedure', "FineId=" .$FineId);
                    if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                        array_push($a_FineNotification, array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'));
                        $rs->Delete('TMP_PaymentProcedure', "FineId=".$FineId);
                    }
                    
                    
                    
                    $a_FineHistory = array(
                        array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>$ResultId,'settype'=>'int'),
                    );
                    
                    
                    if($ResultId<10 || $ResultId == 22){
                        $StatusTypeId = 25;
                        array_push($a_FineNotification, array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>DateInDB($NotificationDate)));
                        array_push($a_FineHistory,array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>DateInDB($NotificationDate)));
                        
                        if($ResultId>=2 && $ResultId<=4){
                            $r_CadCanFee=getPostalCharge($_SESSION['cityid'],DateInDB($NotificationDate));
                            
                            if($ResultId==2 OR $ResultId==4){
                                $CadFee = $r_CadCanFee['CadFee'];
                                
                                array_push($a_FineHistory,array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$CadFee,'settype'=>'flt'));
                            }
                            if($ResultId==3){
                                $CanFee = $r_CadCanFee['CanFee'];
                                
                                array_push($a_FineHistory,array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$CanFee,'settype'=>'flt'));
                            }
                        }
                        
                    }else{
                        $StatusTypeId = 23;
                    }
                    
                    if($r_Fine['StatusTypeId']==20){
                        
                        $a_Fine = array(
                            array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
                        );
                        
                        $rs->Update('Fine',$a_Fine, "Id=".$FineId);
                    }
                    
                    $rs->Insert('FineNotification',$a_FineNotification);
                    
                    $rs->Update('FineHistory',$a_FineHistory, "FineId=".$FineId." AND NotificationTypeId=6");
                    
                    
                    $str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
                    
                    //creo le cartelle se non esistono per ente e verbale
                    if (!is_dir($str_Folder . "/" . $CityId)) {
                        mkdir($str_Folder . "/" . $CityId, 0777);
                    }
                    if (!is_dir($str_Folder . "/" . $CityId . "/" . $FineId)) {
                        mkdir($str_Folder . "/" . $CityId . "/" . $FineId, 0777);
                    }
                    
                    if (file_exists($path.$ImgFront) && !is_dir($path.$ImgFront)) {
                        
                        $DocumentName = $ImgFront;
                        
                        $DocumentationTypeId = 10;
                        
                        $a_FineDocumentation = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
                            array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                            array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                        );
                        $rs->Insert('FineDocumentation',$a_FineDocumentation);
                        
                        if($compress){
                            $img = new Imagick($path.$DocumentName);
                            $width = intval($img->getimagewidth());
                            $height = intval($img->getimageheight());
                            $img->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
                            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                            $img->setImageCompressionQuality(50);
                            $img->stripImage();
                            $img->writeImage($str_Folder."/".$CityId."/".$FineId."/".$DocumentName);
                            $img->destroy();
                            
                        } else{
                            copy($path.$DocumentName, $str_Folder."/".$CityId."/".$FineId."/".$DocumentName);
                        }
                        
                        if (file_exists($str_Folder."/".$CityId."/".$FineId."/".$DocumentName)) {
                            unlink($path.$DocumentName);
                        }
                        else{
                            echo "Poblemi con la creazione del documento: ".$DocumentName;
                            DIE;
                        }
                        
                    }
                    
                    if (file_exists($path.$ImgBack) && !is_dir($path.$ImgBack)) {
                        
                        $DocumentName = $ImgBack;
                        
                        $DocumentationTypeId = 11;
                        
                        $a_FineDocumentation = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
                            array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                            array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                        );
                        $rs->Insert('FineDocumentation',$a_FineDocumentation);
                        
                        if($compress){
                            $img = new Imagick($path.$DocumentName);
                            $width = intval($img->getimagewidth());
                            $height = intval($img->getimageheight());
                            $img->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
                            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                            $img->setImageCompressionQuality(50);
                            $img->stripImage();
                            $img->writeImage($str_Folder."/".$CityId."/".$FineId."/".$DocumentName);
                            $img->destroy();
                            
                            if(file_exists($path.$DocumentName)) unlink($path.$DocumentName);
                            
                        } else{
                            copy($path.$DocumentName, $str_Folder."/".$CityId."/".$FineId."/".$DocumentName);
                        }
                        
                        
                        if (file_exists($str_Folder."/".$CityId."/".$FineId."/".$DocumentName)) {
                            if (file_exists($path.$DocumentName)) {
                                unlink($path.$DocumentName);
                            }
                        }
                        else{
                            echo "Poblemi con la creazione del documento: ".$DocumentName;
                            DIE;
                        }
                        
                    }
                    
                    
                    break;
                }else{
                    //todo chk_LogDate
                    
                }
            }
            
        }
        $rs->End_Transaction();
    }
    trigger_error("MERCURIO FINE inserisce dati notifiche su db", E_USER_WARNING);
}

//fclose($file);



unlink($path.$ImportFile);


for($i=0; $i<count($a_City);$i++){
    
    
    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$a_City[$i]."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){
        
        $str_Content = $r_UserMail['CityTitle'].": sono state importate n. ".$a_NotificationCount[$a_City[$i]]." notifiche.";
        
        
        $a_Mail = array(
            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione"),
            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail',$a_Mail);
        $rs->End_Transaction();
    }
    
}

header("location: ".$P);