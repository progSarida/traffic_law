<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");

function costruisciEtichetta($a_fineData) {
    $ris = "DATA: ". $a_fineData["DataAtto"] . "   ORA:" .$a_fineData["FineTime"] . "  LIM: " .$a_fineData["SpeedLimit"] . "VEL: " . $a_fineData["SpeedControl"]
    . "   NUM: " . $a_fineData["num"] . "\\n" . $a_fineData["ManagerName"] . "   Matr." . $a_fineData["Model"] ."  " . $a_fineData["ManagerSector"]
    . "\\n " . $a_fineData["Kind"] . "App. Min. n. " .$a_fineData["AppMinNQuoted"]. " del: ". $a_fineData["DataDel"]. "   MATR.: " . $a_fineData["Number"] . "   FILE: " . $a_fineData["Documentation"];
    return $ris;
}

// ^/traffic_law/fotogramma/([^/]*)/(.*)$ /traffic_law/exp_fotogramma_dati.php?cod=$1&p=$2
/*
function leggiDatiVerbale($a_url, &$chiave, &$percorsoFile) {
    var_dump($a_url);
    $queryString = $a_url['query'];
    $parametri = explode('&', $queryString);
    //var_dump($parametri);
    $chiave = explode("=", $parametri[0])[1];
    //var_dump($chiave);
    $percorsoFile = explode("=", $parametri[1])[1];
    //var_dump($percorsoFile);
}*/

//url = 'https://'+ window.location.hostname + '/traffic_law/exp_fotogramma.php?percorsoFile='+file+'&testo="'+testo+'"';
function costruisciUrlFotogramma($serverName, $percorsoImmagine, $testo) {
    return 'https://' . $serverName . '/traffic_law/exp_fotogramma.php?percorsoFile='.$percorsoImmagine.'&testo='.urlencode($testo);
    //return 'https://' . $serverName . '/traffic_law/exp_fotogramma.php?percorsoFile='.$percorsoImmagine.'&testo="'.$testo.'"';
    //return "https://" . $serverName . "/traffic_law/exp_fotogramma.php?percorsoFile=".$percorsoImmagine."&testo=\"".$testo."\"";
}

//leggo Id del verbale dall'url
$Id = $_GET['cod'];
$percorsoImmagine = $_GET['p'];
//echo "chiave: ".$Id;
//echo "percorso: ".$percorsoImmagine;

$rs= new CLS_DB();
$rs->SetCharset('utf8');

// estrazione dati da db
$query = "
select
F.Id,
CONCAT('doc/',
(CASE When F.CountryId = 'Z000' THEN 'national' ELSE 'foreign' END),
'/',
CASE WHEN F.FineTypeId = 1 THEN 'violation' ELSE 'fine' END,
'/', F.CityId, '/', F.Id, '/', FD.Documentation) as percorsoFile,
DATE_FORMAT(F.FineDate, '%d-%m-%Y') as DataAtto,
F.FineTime,
FA.SpeedLimit,
FA.SpeedControl,
SUBSTRING(FD.Documentation,LOCATE('_', FD.Documentation, 20)+1,(LOCATE('.', FD.Documentation))-LOCATE('_', FD.Documentation, 20)-1) as num,
C.ManagerName,
D.Model, -- matricola seconda riga sembra il modello di rilevatore
C.ManagerSector,
D.Kind,
replace(D.AppMinN,'\"','\'') as AppMinNQuoted,
DATE_FORMAT(D.Del, '%d-%m-%Y') as DataDel,
D.Number,
FD.Documentation
FROM
Fine F
join Customer C on F.CityId = C.CityId
join FineArticle FA on F.Id = FA.FineId
join Detector D on D.Id = FA.DetectorId and D.CityId = F.CityId
join FineDocumentation FD on F.Id = FD.FineId and FD.DocumentationTypeId = 1
and F.Id = $Id";

$a_fineData = $rs->getArrayLine($rs->SelectQuery($query));
if($a_fineData!=null){
    //$percorsoImmagine = $a_fineData["percorsoFile"];
    $testo = costruisciEtichetta($a_fineData);
    //echo "Percorso immagine: " . $percorsoImmagine;
    //echo "Testo: ". $testo;
}

$url = costruisciUrlFotogramma($_SERVER['SERVER_NAME'], $percorsoImmagine, $testo);

//header('Location: ' . $url);
//header('Content-Type: text/plain');
header('Content-Type: image/jpeg');
readfile($url);
exit(0);
//url di test
//https://multe.ovunque-si.it/traffic_law/exp_fotogramma_dati.php?cod=665805&p=doc/national/violation/D711/665805/1000084559_2166_0_MeanSpeedViolation.jpg

//url di test con redirezione fotogramma
//https://multe.ovunque-si.it/traffic_law/fotogramma/665805/doc/national/violation/D711/665805/1000084559_2166_0_MeanSpeedViolation.jpg
?>
