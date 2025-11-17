<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_imp_beta.php");
require_once(INC."/initialization.php");

ini_set('max_execution_time', 3000);

$ImportFile = CheckValue('ImportFile','s');

if (!is_dir(FOREIGN_VIOLATION."/{$_SESSION['cityid']}")){
    mkdir(FOREIGN_VIOLATION."/{$_SESSION['cityid']}", 0777);
}

$file = fopen(IMP_BETA_CSV_PATH . $ImportFile, "r");
$a_VehicleTypes = getImpBetaVehicleTypes();
$a_Controllers = getImpBetaControllers();

$rs->Start_Transaction();
if (is_resource($file)) {
    $r = fgetcsv($file);
    while ($r = fgetcsv($file)) {
        //Rimuove gli spazi bianchi dalla riga
        $r = array_map('trim', $r);
        
        //controllo se esiste già a sistema un verbale con gli stessi dati
        $fines = $rs->Select('Fine', "CityId='".$_SESSION['cityid']."' AND FineDate='".DateInDB($r[3])."' AND FineTime='".$r[4]."' AND REPLACE(VehiclePlate,'  ','')='".$r[7]. "'");
        $fineNumber = mysqli_num_rows($fines);
        if($fineNumber > 0)
            continue;
            
        $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code=" . $r[48]);
        $detector = mysqli_fetch_array($detectors);
        $controllerCode = ltrim(explode(")", explode("matr.", $r[20])[1])[0], '0');

        $controllerId = getControllerByCode($a_Controllers, $r[3], $controllerCode);

        $VehicleTypeId = $a_VehicleTypes[decodeVehicleType($r[8])];
        $article = getArticleFromBetaString($r[14], $r[2]);
        $rs_reason = getReasonRs($detector['ReasonId'], $_SESSION['cityid'], $article['ViolationTypeId'], $r[48]);
        $Fee=$article['Fee'];
        $MaxFee =  $article['MaxFee'];
        $hoursMins=explode(":",$r[4]);

        if ($article['AdditionalNight']) {
            if ($hoursMins[0] < FINE_HOUR_START_DAY || ($hoursMins[0] == FINE_HOUR_START_DAY && $hoursMins[1] < FINE_MINUTE_START_DAY) ||
            $hoursMins[0] > FINE_HOUR_END_DAY || ($hoursMins[0] == FINE_HOUR_END_DAY && $hoursMins[1] > FINE_MINUTE_END_DAY)) {
                $Fee =  $Fee+ round($Fee / FINE_NIGHT, 2);
                $MaxFee = $MaxFee + round($MaxFee/ FINE_NIGHT, 2);
                $MaxFee = $MaxFee + round($MaxFee/ FINE_NIGHT, 2);
            }
        }
        $a_Fine = array(
            array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $r[0]. "/" . $r[2]),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1),
            array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $_SESSION['year']),
            array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($r[55])),
            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => 'ZZZZ'),
            array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'str', 'value' => $r[56]),
            array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($r[3])),
            array('field' => 'FineTime', 'selector' => 'value', 'type' => 'str', 'value' => $r[4]),
            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $controllerId, 'settype' => 'int'),
            array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $r[47]),
            array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
            array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $r[7]),
            array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $r[9]),
            array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
            array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $FineId = $rs->Insert('Fine', $a_Fine);
        mkdir(FOREIGN_VIOLATION . "/{$_SESSION['cityid']}/$FineId", 0777);
        downloadFile($r[21], FOREIGN_VIOLATION . "/{$_SESSION['cityid']}/$FineId","$FineId.jpg");
        $a_FineArticle = array(
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
            array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $article['Id'], 'settype' => 'int'),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
            array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $article['ViolationTypeId'], 'settype' => 'int'),
            array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => mysqli_fetch_array($rs_reason)['Id'], 'settype' => 'int'),
            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
            array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $detector['Id'], 'settype' => 'int'),
            array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $r[52], 'settype' => 'flt'),
            array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $r[11], 'settype' => 'flt'),
            array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $r[12], 'settype' => 'flt'),
        );
        $rs->Insert('FineArticle', $a_FineArticle);
        $a_FineDocumentation = array(
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => "$FineId.jpg"),
            array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        );
        $rs->Insert('FineDocumentation', $a_FineDocumentation);
    }
}
$rs->End_Transaction();

unlink(IMP_BETA_CSV_PATH.$ImportFile);
header("location: imp_beta.php");