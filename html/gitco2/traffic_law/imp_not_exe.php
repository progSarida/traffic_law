<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 3000);


$P = CheckValue('P','s');


$CityId = $_SESSION['cityid'];

$ImportFile = CheckValue('ImportFile','s');
$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";


$file = fopen($path.$ImportFile,  "r");
$delimiter = ",";

$rs->Start_Transaction();

if(is_resource($file)) {
    while (!feof($file)) {

        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0]) && trim($row[0]) != "Id") {

            $ProtocolId = $row[0];
            $ProtocolYear = $row[5];

            $ExternalProtocol = $row[2];
            $ExternalYear = $row[1];
            $ExternalDate = $row[3];
            $ExternalTime = date("H:i");

            $IrideCode = $row[16];

            $a_SendDate = explode(" ", trim($row[18]));
            $SendDate = trim($a_SendDate[0]);


            if (strlen(trim($row[19])) > 0) {
                $a_NotificationDate = explode(" ", trim($row[19]));
                $NotificationDate = trim($a_NotificationDate[0]);
            } else {
                $NotificationDate = "";
            }


            $rs_Fine = $rs->Select('V_FineTrespasser', "CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $ProtocolId . " AND ProtocolYear=" . $ProtocolYear);


            $r_Fine = mysqli_fetch_array($rs_Fine);
            $FineId = $r_Fine['FineId'];
            $TrespasserId = $r_Fine['TrespasserId'];


            $a_Fine = array(
                array('field' => 'ExternalProtocol', 'selector' => 'value', 'type' => 'int', 'value' => $ExternalProtocol, 'settype' => 'int'),
                array('field' => 'ExternalYear', 'selector' => 'value', 'type' => 'int', 'value' => $ExternalYear, 'settype' => 'int'),
                array('field' => 'ExternalDate', 'selector' => 'value', 'type' => 'date', 'value' => $ExternalDate, 'settype' => 'date'),
                array('field' => 'ExternalTime', 'selector' => 'value', 'type' => 'time', 'value' => $ExternalTime, 'settype' => 'time'),

            );

            $rs->Update('Fine', $a_Fine, "Id=" . $FineId);


            $a_Trespasser = array(
                array('field' => 'IrideCode', 'selector' => 'value', 'type' => 'int', 'value' => $IrideCode, 'settype' => 'int'),
            );
            $rs->Update('Trespasser', $a_Trespasser, "Id=" . $TrespasserId);

            $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $FineId . " AND NotificationTypeId=15");
            $r_FineHistory = mysqli_fetch_array($rs_FineHistory);

            $NotificationTypeId = 6;
            $int_FlowNumber = 0;
            $ResultId = 1;
            $a_FineHistory = array(
                array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineHistory['TrespasserId'], 'settype' => 'int'),
                array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineHistory['TrespasserTypeId'], 'settype' => 'int'),
                array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistory['CustomerFee'], 'settype' => 'flt'),
                array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistory['NotificationFee'], 'settype' => 'flt'),
                array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_FineHistory['ResearchFee'], 'settype' => 'flt'),
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_FineHistory['ControllerId'], 'settype' => 'int'),
                array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => 'Invio PEC'),
            );

            if ($NotificationDate != "") {
                $a_FineHistory[] = array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate);
                $a_FineHistory[] = array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int');


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
                    array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                    array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                    array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),

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

                $rs->Insert('FineNotification', $a_FineNotification);
            }

            $rs->Insert('FineHistory', $a_FineHistory);


        }

    }
    $rs->End_Transaction();
    fclose($file);
}
unlink($path.$ImportFile);

header("location: ".$P);

