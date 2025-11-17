<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 5000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$n_ContFine = 0;

$CityId = $_SESSION['cityid'];


$UserId = "'".$_SESSION['username']."'";

$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;
$str_FixedWhere = "Fixed IS NULL";

$file = fopen($path.$ImportFile,  "r");
$delimiter = ";";




$a_VehicleTypeId = array(
    "CICLOMOTORE"=>2,
    "MOTOVEICOLO"=>2
);

$a_country_id = $a_country_title = array();

$a_country_id["falso"]='Z000';
$a_country_id["vero"]='ZZZZ';


$a_country_title["falso"]='Italia';
$a_country_title["vero"]='Da assegnare';




$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
    $a_Street_Type[$streettype['Title']] = $streettype['Id'];
}




$controllers = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_controller = array();
while ($controller = mysqli_fetch_array($controllers)){
    $a_controller[$controller['Code']] = $controller['Id'] ;
}




if(is_resource($file)) {
    while (!feof($file)) {


        $row = fgetcsv($file, 10000, $delimiter);
        if (isset($row[0]) && strlen(trim($row[0])) > 8) {

            $rs->Start_Transaction();


            $StatusTypeId = 1;

            $IuvCode = $row[0];
            $CityId = $row[1];
            $FineDate = $row[2];
            $FineTime = $row[3];
            $i = 4;
            if (trim($row[$i]) == '') $i++;

            $Address = $row[$i];
            $i++;

            $SpeedLimit = $row[$i];
            $i++;
            $SpeedControl = $row[$i];
            $i++;
            $i++;
            if (isset($a_VehicleTypeId[strtoupper($row[$i])]))
                $VehicleTypeId = $a_VehicleTypeId[strtoupper($row[$i])];
            else
                $VehicleTypeId = 1;
            $i++;
            $VehiclePlate = strtoupper($row[$i]);
            $i++;
            $VehicleBrand = $row[$i];
            $i++;
            $VehicleModel = $row[$i];
            $i++;
            $DetectorCode = $row[$i];
            $i++;
            $i++;
            $CountryId = strtolower($row[$i]);
            $i++;
            $i++;
            $ControllerDate = $row[$i];
            $i++;
            $ControllerTime = $row[$i];
            $i++;
            $ControllerCode = $row[$i];


            $a_FineTime = explode(".", $FineTime);
            $FineTime = $a_FineTime[0];


            if (strpos($FineDate, '/') !== false) {
                $aFineDate = explode("/", $FineDate);

                $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                $ProtocolYear = $aFineDate[2];

            } else {
                $ProtocolYear = substr($FineDate, 0, 4);
            }


            $a_Address = explode(" ", $Address);
            $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


            $Note = '';


            $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='" . $_SESSION['cityid'] . "' AND VehiclePlate='" . $VehiclePlate . "'");

            if (mysqli_num_rows($rs_VehicleWhiteList) > 0) {
                $StatusTypeId = 90;
                $Note = 'White list ente';
            }


            $VehicleMass = CheckValue('VehicleMass', 'n');


            $aFineDate = explode("/", $FineDate);


            $Code = $IuvCode . "/" . $ProtocolYear;


            $DepartmentId = CheckValue('DepartmentId', 'n');


            $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode . "'");
            $detector = mysqli_fetch_array($detectors);
            $DetectorId = $detector['Id'];
            $ReasonId = $detector['ReasonId'];

            $chkTolerance = $detector['Tolerance'];
            $str_FixedWhere = 'Fixed=' . $detector['Fixed'];

            $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

            $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
            $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


            $chkTolerance = $detector['Tolerance'];
            $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

            $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
            $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


            $Speed = $SpeedControl - $Tolerance;
            $SpeedExcess = $Speed - $SpeedLimit;

            $Where = "DetectorArticle.DetectorId=" . $DetectorId . " AND DetectorArticle.Disabled=0 AND 
            V_Article.Disabled=0 AND V_Article.CityId='" . $_SESSION['cityid'] . "' AND V_Article.Year=" . $ProtocolYear;
            if ($SpeedExcess <= 10) {
                $Where .= " AND V_Article.Article=142 AND V_Article.Paragraph='7'";
            } elseif ($SpeedExcess <= 40) {
                $Where .= " AND V_Article.Article=142 AND V_Article.Paragraph='8'";
            } elseif ($SpeedExcess <= 60) {
                $Where .= " AND V_Article.Article=142 AND V_Article.Paragraph='9' AND V_Article.Letter!='bis'";
            } else {
                $Where .= " AND V_Article.Article=142 AND V_Article.Paragraph='9' AND V_Article.Letter='bis'";
            }


            $finds = $rs->SelectQuery('
                SELECT V_Article.* FROM V_Article 
                JOIN DetectorArticle ON V_Article.Id = DetectorArticle.ArticleId
                WHERE ' . $Where
            );
            $find = mysqli_fetch_array($finds);

            $ArticleId = $find['Id'];


            $ViolationTypeId = $find['ViolationTypeId'];
            $ViolationTitle = $find['ViolationTitle'];

            $Fee = $find['Fee'];
            $MaxFee = $find['MaxFee'];
            $ViolationTypeId = $find['ViolationTypeId'];
            $aTime = explode(":", $FineTime);


            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                //FINE_MINUTE_START_DAY
                //FINE_MINUTE_END_DAY
                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
            }

            $VehicleCountry = $a_country_title[$CountryId];
            $CountryId = $a_country_id[$CountryId];


            $ControllerId = $a_controller[$ControllerCode];


            $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND IuvCode='" . $IuvCode . "'");
            $FindNumber = mysqli_num_rows($fines);

            if ($FindNumber == 0) {

                $a_Fine = array(
                    array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                    array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
                    array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
                    array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
                    array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => $ControllerDate),
                    array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'str', 'value' => $ControllerTime),
                    array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'StreetTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StreetTypeId, 'settype' => 'int'),
                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
                    array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
                    array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
                    array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                    array('field' => 'DepartmentId', 'selector' => 'value', 'type' => 'int', 'value' => $DepartmentId),
                    array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleMass, 'settype' => 'int'),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                    array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                    array('field' => 'IuvCode', 'selector' => 'value', 'type' => 'str', 'value' => $IuvCode),

                );

                $FineId = $rs->Insert('Fine', $a_Fine);
                $n_ContFine++;
                if ($FineId == 0) {
                    echo "Poblemi con l'inserimento del verbale con targa: " . $VehiclePlate;
                    die;
                }
                $a_FineArticle = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
                    array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                    array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
                    array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
                    array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
                    array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
                );
                $rs->Insert('FineArticle', $a_FineArticle);
            } else {
                $a_Fine = array(
                    array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime)
                );

                $a_fines = $rs->getArrayLine($fines);
                if ($a_fines['Id'] > 0 && $a_fines['FineTime'] != $FineTime && ($a_fines['StatusTypeId'] < 15 || substr($a_fines['FineTime'], 0, 5) == substr($FineTime, 0, 5))) {
                    $rs->Update('Fine', $a_Fine, "Id=" . $a_fines['Id']);
                }
            }
            $rs->End_Transaction();
        }
    }
    fclose($file);
}

//unlink($path.$ImportFile);

if($n_ContFine>0){

    $a_Import = array(
        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>1),
        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$ImportFile),
        array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_ContFine),
    );
    $rs->Start_Transaction();
    $rs->Insert('ImportedFiles',$a_Import);
    $rs->End_Transaction();

    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

        $str_Content = $r_UserMail['CityTitle'].": sono state elaborate n. ".$n_ContFine." violazioni.";

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