<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require_once(INC."/function_postalCharge.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 5000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$a_City = array();
$a_NotificationCount = array();

$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;

$str_Flow = " AND (
Documentation='Flusso_27_Verb_Ita_U480_2018-06-13_13-26-17_900.zip' OR
Documentation='Flusso_28_Verb_Ita_U480_2018-06-13_13-32-45_900.zip' OR
Documentation='Flusso_29_Verb_Ita_U480_2018-06-13_13-37-44_900.zip' OR
Documentation='Flusso_30_Verb_Ita_U480_2018-06-13_13-42-00_900.zip' OR
Documentation='Flusso_31_Verb_Ita_U480_2018-06-13_13-48-22_753.zip'
)";




$file = fopen($path.$ImportFile,  "r");
$delimiter = ";";


$rs_Result = $rs->Select('Result', "Disabled=0");
$a_chk_Result = array();
while ($r_Result = mysqli_fetch_array($rs_Result)){
    $a_chk_Result[] = $r_Result['Description'];
    $a_Result[$r_Result['Description']] = $r_Result['Id'];
}



if(is_resource($file)) {
    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0]) && $row[0] != "cod_comune") {

            $rs->Start_Transaction();


            $CityId = $row[0];

            $a_Row = explode("/", $row[1]);
            $ProtocolId = $a_Row[0];
            $ProtocolYear = $a_Row[1];

            $NotificationDate = $row[5];
            $LetterNumber = $row[13];
            $ReceiptNumber = $row[6];

            $NotificationType = trim($row[7]);
            $a_Notification = explode("-", $NotificationType);
            if (isset($a_Notification[1])) $NotificationType = trim($a_Notification[0]) . " - " . trim($a_Notification[1]);


            $NotificationStatus = trim($row[8]);
            $a_Notification = explode("-", $NotificationStatus);
            if (isset($a_Notification[1])) $NotificationStatus = trim($a_Notification[0]) . " - " . trim($a_Notification[1]);


            $ImgBack = $row[11];
            $ImgFront = $row[10];

            $SendDate = $row[14];
            $Box = $row[15];
            $Lot = $row[16];
            $Position = $row[17];

            $chkFine = "";
            $chkSendDate = "";
            $chkDeliveryDate = "";
            $chkNotification = "";

            $a_LogDate = explode(" ", $row[12]);
            $LogDate = $a_LogDate[0];


            $rs_Fine = $rs->Select('Fine', "CountryId='Z000' AND CityId='" . $CityId . "' AND ProtocolId=" . $ProtocolId . " AND ProtocolYear=" . $ProtocolYear . " AND Id IN( SELECT FineId FROM FineHistory WHERE NotificationTypeId=6" . $str_Flow . ")");


            if (mysqli_num_rows($rs_Fine) == 0) {
                // todo Verbale non presente


            } else {

                if (!in_array($CityId, $a_City)) {
                    $a_City[] = $CityId;
                    $a_NotificationCount[$CityId] = 0;
                }


                for ($i = 1; $i <= mysqli_num_rows($rs_Fine); $i++) {
                    $r_Fine = mysqli_fetch_array($rs_Fine);

                    $FineId = $r_Fine['Id'];
                    $CountryId = $r_Fine['CountryId'];


                    $rs_FineNotification = $rs->Select('FineNotification', "FineId=" . $r_Fine['Id']);
                    if (mysqli_num_rows($rs_FineNotification) == 0) {

                        $a_NotificationCount[$CityId] = $a_NotificationCount[$CityId] + 1;
                        $ResultId = (in_array(trim($NotificationStatus), $a_chk_Result)) ? $a_Result[$NotificationStatus] : $a_Result[$NotificationType];
                        $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $FineId);
                        $r_Tariff = mysqli_fetch_array($rs_Tariff);


                        $LicensePointProcedure = $r_Tariff['LicensePoint'];
                        $PresentationDocumentProcedure = $r_Tariff['PresentationDocument'];
                        $BisProcedure = $r_Tariff['126Bis'];
                        $HabitualProcedure = $r_Tariff['Habitual'];
                        $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                        $LossLicenseProcedure = $r_Tariff['LossLicense'];


                        $a_FineNotification = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($SendDate)),
                            array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($LogDate)),
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


                        if ($ResultId < 10 || $ResultId == 22) {
                            $StatusTypeId = 25;
                            array_push($a_FineNotification, array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($NotificationDate)));
                            array_push($a_FineHistory, array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($NotificationDate)));

                            if ($ResultId >= 2 && $ResultId <= 4) {
                                $r_CadCanFee = getPostalCharge($_SESSION['cityid'], DateInDB($NotificationDate));

                                if ($ResultId == 2 || $ResultId == 4) {
                                    $CadFee = $r_CadCanFee['CadFee'];

                                    array_push($a_FineHistory, array('field' => 'CadFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CadFee, 'settype' => 'flt'));
                                }
                                if ($ResultId == 3) {
                                    $CanFee = $r_CadCanFee['CanFee'];

                                    array_push($a_FineHistory, array('field' => 'CanFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CanFee, 'settype' => 'flt'));
                                }
                            }

                        } else {
                            $StatusTypeId = 23;
                        }

                        if ($r_Fine['StatusTypeId'] == 20) {

                            $a_Fine = array(
                                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                            );

                            $rs->Update('Fine', $a_Fine, "Id=" . $FineId);
                        }

                        $rs->Insert('FineNotification', $a_FineNotification);

                        $rs->Update('FineHistory', $a_FineHistory, "FineId=" . $FineId . " AND NotificationTypeId=6");


                        $str_Folder = ($CountryId == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;


                        if (file_exists($path . "/" . $ImgFront)) {

                            $DocumentName = $ImgFront;

                            $DocumentationTypeId = 10;

                            $a_FineDocumentation = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
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

                            } else {
                                copy($path . $DocumentName, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                            }

                            if (file_exists($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
                                unlink($path . $DocumentName);
                            } else {
                                echo "Poblemi con la creazione del documento: " . $DocumentName;
                                die;
                            }

                        }

                        if (file_exists($path . "/" . $ImgBack)) {

                            $DocumentName = $ImgBack;

                            $DocumentationTypeId = 11;

                            $a_FineDocumentation = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentName),
                                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
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
                                copy($path . $DocumentName, $str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName);
                            }


                            if (file_exists($str_Folder . "/" . $CityId . "/" . $FineId . "/" . $DocumentName)) {
                                if (file_exists($path . $DocumentName)) {
                                    unlink($path . $DocumentName);
                                }
                            } else {
                                echo "Poblemi con la creazione del documento: " . $DocumentName;
                                die;
                            }

                        }


                        break;
                    }
                }

            }
            $rs->End_Transaction();
        }
    }
    fclose($file);
}



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