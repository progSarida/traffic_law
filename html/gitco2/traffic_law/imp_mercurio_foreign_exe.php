<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");

ini_set('max_execution_time', 0);


$P = CheckValue('P', 's');
$compress = CheckValue('compress', 'n');


$a_City = array();
$a_NotificationCount = array();

$a_Notification = array(
    "" => "01 - AR",
    "Indirizzo Inesatto" => "03 - Indirizzo inesatto",
    "Indirizzo Insufficiente" => "04 - Indirizzo insufficiente",
    "Indirizzo Inesistente" => "03 - Indirizzo inesatto",
    "Irreperibile" => "07 - Irreperibile",
    "Non ritirato" => "07 - Irreperibile",
    "Trasferito" => "10 - Trasferito",
    "Assente" => "20 - Assente",
    "Rifiutato" => "02 - Rifiutato",
    "Sconosciuto" => "08 - Sconosciuto",
    "Deceduto" => "09 - Deceduto",
    "Compiuta Giacenza" => "21 - Compiuta Giacenza",
    "Rubato o Smarrito" => "07 - Irreperibile",
    
);

$path = PUBLIC_FOLDER . "/_MERCURIO_EE_/IMPORT/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;


//$file = fopen($path . $ImportFile, "r");
//$delimiter = detectDelimiter($path . $ImportFile);


$rs_Result = $rs->Select('Result', "Disabled=0");
$a_chk_Result = array();
while ($r_Result = mysqli_fetch_array($rs_Result)) {
    $a_chk_Result[] = $r_Result['Description'];
    $a_Result[$r_Result['Description']] = $r_Result['Id'];
}

ini_set("auto_detect_line_endings", true);
$csv = array();
$lines = file($path . $ImportFile);
foreach ($lines as $key_line => $a_line)
{
    $csv[$key_line] = str_getcsv($a_line);
    foreach ($csv[$key_line] as $key => $value)
    {
        $csv[$key_line][$key] = trim($value);
    }
}

$b_Header = true;
foreach ($csv as $row) {
    //    $row = fgetcsv($file, 1000, $delimiter);
    
    if (isset($row[0])) {
        
        $a_chkRow = explode("_", $row[0]);
        
        
        if (!$b_Header) {
            
            
            if(isset($row[11])){
                $NotificationDate = trim($row[11]);
                $NotificationDescription = trim($row[12]);
                $a_Row = explode("/", $row[3]);
                $LetterNumber = $row[4];
                $SendDate = trim($row[5]);
                
                $Box = $row[14];
                $Lot = $row[15];
                $Position = $row[16];
                
                
            } else {
                $NotificationDate = trim($row[4]);
                $NotificationDescription = trim($row[5]);
                $a_Row = explode("/", $row[1]);
                $LetterNumber = $row[2];
                $SendDate = trim($row[3]);
                
                $Box = $row[7];
                $Lot = $row[8];
                $Position = $row[9];
                
                
            }
            
            $ImgBack = $LetterNumber . "_R.jpg";
            $ImgFront = $LetterNumber . "_F.jpg";
            //$ImgBack = $LetterNumber."001.jpg";
            //$ImgFront = $LetterNumber."002.jpg";
            $a_Documentation = array($ImgFront, $ImgBack);
            $CityId = $a_chkRow[4];
            
            $ProtocolId = $a_Row[0];
            $ProtocolYear = $a_Row[1];
            $ReceiptNumber = "";
            
            
            $NotificationStatus = $a_Notification[$NotificationDescription];
            
            
            if (strlen($SendDate) == 8) {
                $a_Date = explode("/", $SendDate);
                $SendDate = $a_Date[0] . "/" . $a_Date[1] . "/" . "20" . $a_Date[2];
            }
            
            
            $LogDate = date("Y-m-d");
            
            $rs_Fine = $rs->Select('Fine', "CountryId!='Z000' AND CityId='" . $CityId . "' AND ProtocolId=" . $ProtocolId . " AND ProtocolYear=" . $ProtocolYear);
            
            
            if (mysqli_num_rows($rs_Fine) == 0) {
                // todo Verbale non presente
                
            } else {
                
                if (!in_array($CityId, $a_City)) {
                    $a_City[] = $CityId;
                    $a_NotificationCount[$CityId] = 0;
                }
                
                
                $a_NotificationCount[$CityId] = $a_NotificationCount[$CityId] + 1;
                
                $rs->Start_Transaction();
                
                $r_Fine = mysqli_fetch_array($rs_Fine);
                
                $FineId = $r_Fine['Id'];
                
                
                $CountryId = $r_Fine['CountryId'];
                
                
                $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $FineId . " AND NotificationTypeId=6");
                $r_FineHistory = mysqli_fetch_array($rs_FineHistory);
                
                if ($r_FineHistory['SendDate'] != DateInDB($SendDate)) {
                    // todo Data invio non coincidente
                    /*
                     $a_FineHistory = array(
                     array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$SendDate),
                     );
                     
                     $rs->Update('FineHistory',$a_FineHistory, "FineId=".$FineId." AND NotificationTypeId=6");
                     
                     */
                    
                }
                //if(is_null($r_FineHistory['DeliveryDate'])){
                
                $ResultId = $a_Result[$NotificationStatus];
                $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $FineId);
                $r_Tariff = mysqli_fetch_array($rs_Tariff);
                
                
                $LicensePointProcedure = $r_Tariff['LicensePoint'];
                $PresentationDocumentProcedure = $r_Tariff['PresentationDocument'];
                $BisProcedure = $r_Tariff['126Bis'];
                $rs_InjunctionProcedure = $rs->Select("TMP_InjunctionProcedure", "FineId=". $FineId);
                $InjunctionProcedure = (mysqli_num_rows($rs_InjunctionProcedure)==0) ? 1 : 0;
                
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
                
                $HabitualProcedure = $r_Tariff['Habitual'];
                $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                $LossLicenseProcedure = $r_Tariff['LossLicense'];
                
                
                $SendDate = DateInDB(str_replace("-", "/", $SendDate));
                
                
                $a_FineNotification = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => $LogDate),
                    array('field' => 'Box', 'selector' => 'value', 'type' => 'str', 'value' => $Box),
                    array('field' => 'Lot', 'selector' => 'value', 'type' => 'str', 'value' => $Lot),
                    array('field' => 'Position', 'selector' => 'value', 'type' => 'str', 'value' => $Position),
                    array('field' => 'ReceiptNumber', 'selector' => 'value', 'type' => 'str', 'value' => $ReceiptNumber),
                    array('field' => 'LetterNumber', 'selector' => 'value', 'type' => 'str', 'value' => $LetterNumber),
                    array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int'),
                    array('field' => '126BisProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $BisProcedure, 'settype' => 'int'),
                    array('field' => 'PresentationDocumentProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $PresentationDocumentProcedure, 'settype' => 'int'),
                    array('field' => 'LicensePointProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $LicensePointProcedure, 'settype' => 'int'),
                    array('field' => 'HabitualProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $HabitualProcedure, 'settype' => 'int'),
                    array('field' => 'SuspensionLicenseProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $SuspensionLicenseProcedure, 'settype' => 'int'),
                    array('field' => 'LossLicenseProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $LossLicenseProcedure, 'settype' => 'int'),
                    array('field' => 'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>$InjunctionProcedure,'settype'=>'int'),
                    array('field' => 'ReminderAdditionalFeeProcedure','selector'=>'value','type'=>'int','value'=>$ReminderAdditionalFeeProcedure,'settype'=>'int'),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                );
                
                $rs_TMP_PaymentProcedure = $rs->Select('TMP_PaymentProcedure', "FineId=" . $FineId);
                if (mysqli_num_rows($rs_TMP_PaymentProcedure) > 0) {
                    array_push($a_FineNotification, array('field' => 'PaymentProcedure', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'));
                    $rs->Delete('TMP_PaymentProcedure', "FineId=" . $FineId);
                }


                $a_FineHistory = array(
                    array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int'),
                );
                
                
                if ($ResultId < 10) {
                    $StatusTypeId = 25;
                    
                    
                    if ($NotificationDate != "") $NotificationDate = str_replace("-", "/", $NotificationDate);
                    if (strlen($NotificationDate) == 8) {
                        $a_Date = explode("/", $NotificationDate);
                        $NotificationDate = $a_Date[0] . "/" . $a_Date[1] . "/" . "20" . $a_Date[2];
                    }
                    
                    if ($NotificationDate != "") $NotificationDate = DateInDB($NotificationDate);
                    
                    
                    if ($NotificationDate == $SendDate || $NotificationDate == "") {
                        
                        $NotificationDate = date('Y-m-d', strtotime($SendDate . ' + 5 days'));
                        
                        
                    }
                    
                    
                    array_push($a_FineNotification, array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate));
                    array_push($a_FineHistory, array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate));
                    
                } else {
                    $StatusTypeId = 23;
                }
                
                
                if ($r_Fine['StatusTypeId'] == 20) {
                    
                    $a_Fine = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                    );
                    
                    $rs->Update('Fine', $a_Fine, "Id=" . $FineId);
                }
                
                
                $rs_Notification = $rs->Select('FineNotification', "FineId=" . $FineId);
                if (mysqli_num_rows($rs_Notification) == 0) {
                    $rs->Insert('FineNotification', $a_FineNotification);
                }
                
                
                $rs->Update('FineHistory', $a_FineHistory, "FineId=" . $FineId . " AND NotificationTypeId=6");
                
                
                $str_Folder = ($CountryId == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;
                //creo le cartelle se non esistono per ente e verbale
                if (!is_dir($str_Folder . "/" . $CityId)) {
                    mkdir($str_Folder . "/" . $CityId, 0777);
                }
                if (!is_dir($str_Folder . "/" . $CityId . "/" . $FineId)) {
                    mkdir($str_Folder . "/" . $CityId . "/" . $FineId, 0777);
                }
                
                for ($i = 0; $i < count($a_Documentation); $i++) {
                    if (file_exists($path . "/" . $a_Documentation[$i])) {
                        
                        $DocumentName = $a_Documentation[$i];
                        
                        $DocumentationTypeId = ($i == 0) ? 10 : 11;
                        
                        
                        $rs_FineDocumentation = $rs->Select('FineDocumentation', "FineId=" . $FineId . " AND Documentation='" . $DocumentName . "'");
                        if (mysqli_num_rows($rs_FineDocumentation) == 0) {
                            $a_FineDocumentation = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
                                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                            );
                            $rs->Insert('FineDocumentation', $a_FineDocumentation);
                            
                            if ($compress) {
                                $img = new Imagick($path . $DocumentName);
                                $width = intval($img->getimagewidth());
                                $height = intval($img->getimageheight());
                                $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                                $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                                $img->setImageCompressionQuality(50);
                                $img->stripImage();
                                $img->writeImage($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                                $img->destroy();
                                
                                if (file_exists($path . $DocumentName)) unlink($path . $DocumentName);
                            } else {
                                if (copy($path . $DocumentName, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
                                    unlink($path . $DocumentName);
                                } else {
                                    echo "Poblemi con la creazione del documento: " . $DocumentName;
                                    DIE;
                                }
                            }
                            
                        }
                        
                    }
                }
                
                
                //}else{
                //todo chk_LogDate
                
                //}
                $rs->End_Transaction();
            }
            
            
        } else $b_Header = false;
    }
}

//fclose($file);


unlink($path . $ImportFile);
for ($i = 0; $i < count($a_City); $i++) {
    
    
    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM " . MAIN_DB . ".V_UserCity WHERE UserLevel>=3 AND CityId='" . $a_City[$i] . "'");
    while ($r_UserMail = mysqli_fetch_array($rs_UserMail)) {
        
        $str_Content = $r_UserMail['CityTitle'] . ": sono state importate n. " . $a_NotificationCount[$a_City[$i]] . " notifiche.";
        
        
        $a_Mail = array(
            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
            array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
            array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Nuova importazione"),
            array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_UserMail['UserId'], 'settype' => 'int'),
            array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail', $a_Mail);
        $rs->End_Transaction();
    }
    
}
header("location: imp_mercurio_foreign.php");