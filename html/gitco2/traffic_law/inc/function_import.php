<?php

/**
 * Ricava lo statusTypeId definito dal rilevatore,
 * @param int $DetectorId l' id del rilevatore
 * @return string "0" se il rilevatore prevede la validazione "1" altrimenti
 */
function getStatusTypeId(int $DetectorId):string{
    global $rs;
    $rs_Customer = $rs->Select('Detector', "Id='" . $DetectorId . "'");
    $n_Validation = mysqli_fetch_array($rs_Customer)['Validation'];
    return ($n_Validation == 1) ? 0 : 1;
}

/**
 * Calcola l' eccesso di velocità
 * @param int $SpeedControl Velocità rilevata
 * @param int $SpeedLimit Limite di velocità
 * @param int $chkTolerance percentuale di tolleranza
 * @return int valore dell' eccesso di velocità
 */
function getSpeedExcess(int $SpeedControl,int $SpeedLimit,int $chkTolerance):int{
    $chkTolerance = ($chkTolerance>FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;
    $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
    $Tolerance = ($TolerancePerc<$chkTolerance) ? $chkTolerance : $TolerancePerc;
    $Speed = $SpeedControl - $Tolerance;
    return $Speed - $SpeedLimit;
}

/**
 * Calcola la velocità considerando le tolleranze
 * @param int $SpeedControl Velocità rilevata
 * @param int $SpeedLimit Limite di velocità
 * @param int $chkTolerance percentuale di tolleranza
 * @return int valore della velocità
 */
function getSpeed(int $SpeedControl,int $SpeedLimit,int $chkTolerance):int{
    $chkTolerance = ($chkTolerance>FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;
    $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
    $Tolerance = ($TolerancePerc<$chkTolerance) ? $chkTolerance : $TolerancePerc;
    return $SpeedControl - $Tolerance;
}

/**
 * Restituisce una riga della vista V_Article per gli articoli di velocità
 * @param $DetectorId l' identificatore del rilevatore
 * @param string $cityId l' identificativo dell' ente
 * @param $SpeedExcess valore dell' ccesso di velocità
 * @param int $ProtocolYear anno di protocollo del verbale
 * @return array|false|null VArticle trovato
 */
function getVArticle($DetectorId,string $cityId,$SpeedExcess,int $ProtocolYear){
    global $rs;
    $join="";
    
    if($DetectorId==null){
        $Where = "v.Disabled=0 AND v.CityId='" . $_SESSION['cityid'] . "' AND v.Year=" . $ProtocolYear;
    } else {
        $Where = "da.DetectorId=".$DetectorId." AND da.Disabled=0 AND v.Disabled=0 AND v.CityId='$cityId' AND v.Year='$ProtocolYear'";
        $join="JOIN DetectorArticle da ON v.Id = da.ArticleId";
    }
    $Where .= " AND v.Article=142";
    
    if($SpeedExcess<=10){
        $Where .= " AND v.Paragraph='7'";
    } else if($SpeedExcess<=40){
        $Where .= " AND v.Paragraph='8'";
    } else if($SpeedExcess<=60){
        $Where .= " AND v.Paragraph='9' AND v.Letter !='bis'";
    } else {
        $Where .= " AND v.Paragraph='9' AND v.Letter='bis'";
    }
        
    $rs_articles= $rs->SelectQuery("SELECT v.* FROM V_Article v $join WHERE $Where");

    return mysqli_num_rows($rs_articles)<=0 ? null : mysqli_fetch_array($rs_articles);
}

/**
 * Restituisce una riga della vista V_Article per l'articolo di semaforo
 * @param $DetectorId l' identificatore del rilevatore
 * @param string $cityId l' identificativo dell' ente
 * @param $SpeedExcess valore dell' ccesso di velocità
 * @param int $ProtocolYear anno di protocollo del verbale
 * @return array|false|null VArticle trovato
 */
function getSArticle($DetectorId,string $cityId,int $ProtocolYear){
    global $rs;
    $join="";
    
    if($DetectorId==null){
        $Where = "v.Disabled=0 AND v.CityId='" . $_SESSION['cityid'] . "' AND v.Year=" . $ProtocolYear;
    } else {
        $Where = "da.DetectorId=".$DetectorId." AND da.Disabled=0 AND v.Disabled=0 AND v.CityId='$cityId' AND v.Year='$ProtocolYear'";
        $join="JOIN DetectorArticle da ON v.Id = da.ArticleId";
    }
    $Where .= " AND v.Article=146 AND v.Paragraph=3";
    $rs_articles= $rs->SelectQuery("SELECT v.* FROM V_Article v $join WHERE $Where");
    if(mysqli_num_rows($rs_articles)<=0){
        return null;
    }
    return mysqli_fetch_array($rs_articles);
}

/**
 * Trova un rilevatore basandosi sul codice e il codice ente
 * @param string $cityId codice del' ente
 * @param string $detectorCode codice del rilevatore
 * @param null $errorCount conteggio degli errori, viene aggiornato dalla funzione
 * @return array|string Il rilevatore oppure un messaggio di errore in html
 */
function getDetector(string $cityId, string $detectorCode,&$errorCount=null){
    global $rs;
    $detectors = $rs->Select('Detector', "CityId='$cityId' AND Code='$detectorCode'");
    $FindNumber = mysqli_num_rows($detectors);
    if($FindNumber==0){
        $error = true;
        $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
        if($errorCount==null)
            $errorCount='';
            else
            $errorCount+=1;
        return '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-danger">'.$errorCount . '</div>
                <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod '. $detectorCode .' non presente</div>
                <div class="clean_row HSpace4"></div>
            </div>
            ';
    }
    return mysqli_fetch_array($detectors);
}

/**
 * Restituisce un ResultSet contentente Reason
 * @param $ReasonId l' identificativo di Reason
 * @param $CityId il codice ente
 * @param $ViolationTypeId l' identificativo del tipo di violazione
 * @param $DetectorCode il codice del rilevatore
 * @return bool|mysqli_result|void il resultset contenente reason
 */
function getReasonRs($ReasonId,$CityId,$ViolationTypeId,$DetectorCode){
    global $rs;
    if($ReasonId!=null)
        $str_Where ="Id=$ReasonId";
    else{
        $str_Where = ($DetectorCode==0 ? 'Fixed IS NULL ' : 'Fixed IS NOT NULL ')."AND ReasonTypeId=1 AND CityId='$CityId' AND Disabled = 0";
        switch($ViolationTypeId){
            case 4:
            case 6:
                $str_Where .= ($DetectorCode==0) ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" .$ViolationTypeId;
                break;
            default:
                $str_Where .= " AND ViolationTypeId=" .$ViolationTypeId;
        }
    }
    return $rs->Select('Reason', $str_Where);
}

/**
 * Restituisce i rilevatori in un array chiave valore.
 * @param $rs_Controllers resultset risultato della ricerca su Controller
 * @return array LA chiave e il codice mentre il valore è un array contenente le varie versioni di accertatore per quel Code
 */
function controllersByCodeArray($rs_Controllers){
    return controllersByFieldArray($rs_Controllers);
}

/**
 * Restituisce i rilevatori in un array chiave valore.
 * @param $rs_Controllers resultset risultato della ricerca su Controller
 * @param field il nome del campo da controllare
 * @return array LA chiave e il campo identificato da field mentre il valore è un array contenente le varie versioni di accertatore per quel campo
 */

function controllersByFieldArray($rs_Controllers,$field='Code'){
    $a_controllers = array();
    while ($r_Controller = mysqli_fetch_array($rs_Controllers)){
        $a_controllers[strtolower($r_Controller[$field])][] = array('Id' => $r_Controller['Id'], 'ToDate' => $r_Controller['ToDate'],'Name' => $r_Controller['Name']);
    }
    return $a_controllers;
}

/**
 * Cerca un accertatore
 * @param $a_Controllers un array di accertatori chiave valore secondo la specifica di controllersByFieldArray
 * @param $finedate la data del verbale
 * @param $value il valore su cui eseguire il controllo
 * @return false|int|string|null l' identificatore dell' accertatore
 */
function getControllerByField($a_Controllers, $finedate, $value){
    $a_toDates = array();
    $controllerId = null;
    $lowerValue=strtolower($value);
    $compareDate=DateInDB($finedate);

    if(isset($a_Controllers[$lowerValue])){
        foreach ($a_Controllers[$lowerValue] as $controller){
            $a_toDates[$controller['Id']] = $controller['ToDate'];
        }
        arsort($a_toDates);

        foreach ($a_toDates as $id => $date){
            if($compareDate <= $date)
                $controllerId = $id;
        }
        if (is_null($controllerId) && array_search(null, $a_toDates) !== false){
            $controllerId = array_search(null, $a_toDates);
        }
    }
    return $controllerId;
}
/**
 * Cerca un accertatore utilizzando $fieldValues e la data del verbale
 * @param $a_Controllers un array di accertatori
 * @param $finedate la data del verbale
 * @param $fieldValues un' array chiave valore con campo => valore da controllare in AND per identificare l' accertatore
 * @return false|int|string|null l' identificatore dell' accertatore
 */
function getControllerFromArrayByField(array $controllerArray, array $fieldValues, $fineDate){
    $fieldNames=array_keys($fieldValues);
    foreach ($controllerArray as $c){
        $found=$fineDate<=$c['ToDate'] && ($fineDate>=$c['FromDate'] || $c['FromDate']==null);
        if($found)
        foreach ($fieldNames as $fieldName)
            $found=($c[$fieldName]==$fieldValues[$fieldName]) && $found;
        if($found)
            return $c;
    }
    return null;
}
/**
 * Cerca un accertatore
 * @param $a_Controllers un array di accertatori chiave valore secondo la specifica di controllersByFieldArray
 * @param $finedate la data del verbale
 * @param $value il valore su cui eseguire il controllo
 * @return false|int|string|null l' identificatore dell' accertatore
 */
function getControllerByCode($a_Controllers, $finedate, $code){
    return getControllerByField($a_Controllers, $finedate, $code);
}
?>