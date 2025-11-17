<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");

include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;
$error = false;
$msgProblem = "";

$zipFiles=glob($path."*.zip");
foreach($zipFiles as $zipFile){
    $zip = new ZipArchive();
    $res = $zip->open($zipFile);
    $zip->extractTo($path);
    unlink($zipFile);
}
$zipFiles=glob($path."*.zip");
foreach($zipFiles as $zipFile) {

    $zip = new ZipArchive();
    $res = $zip->open($zipFile);
    $i=0;
    while($entryName = $zip->getNameIndex($i)){
        $i++;
        if(strpos($entryName,".jpg"))
            $zip->ExtractTo($path,$entryName);
    }
    unlink($zipFile);
}
$folders=glob($path."*");
foreach($folders as $folder)
    if(!strpos($folder,".")>0){
        $subfolders=glob($folder."/*");
        foreach($subfolders as $subfolder){
            if(!strpos($folder,".")>0) {
                $images=glob($subfolder."/*.jpg");
                foreach($images as $image){
                    copy($image, $path.basename($image));
                    unlink($image);
                }
                rmdir($subfolder);
            }
        }
        rmdir($folder);
    }

$chk_GlobalImage = false;
$a_VehiclePlate = array();

/*
B038
Ciao, abbiamo installato due nuove telecamere, posizionate in via Roma
civico 237 e in via maestra alla frazione Torrazza al civico 104. Mi

*/






if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".","$file");
        if (strtolower($aFile[count($aFile)-1])=="csv"){
            $Cont++;
            $FileList .=  '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$Cont . '</div>
            <div class="table_caption_H col-sm-10">'.$file . '</div>
            <div class="table_caption_button col-sm-1">
                '. ChkButton($aUserButton, 'imp','<a href="imp_owner.php?ImportFile='.$file.'"><span class="fa fa-upload"></span></a>') .'
                &nbsp;
            </div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}
if($Cont==0){
    $FileList =  '
            <div class="col-sm-12">
                <div class="table_caption_H col-sm-11">Nessun file presente</div>
                <div class="table_caption_button col-sm-1"></div>
			    <div class="clean_row HSpace4"></div>
			</div>    
			';
}

$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
				</div>
			</div>
		</div>		
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-11">ELENCO FILE</div>
				<div class="table_add_button col-sm-1 right">
        			'.ChkButton($aUserButton, 'add','<a href="mgmt_violation_add.php"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>').'      				
				</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList;

echo $str_out;


if($ImportFile==""){
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
}else{

    $rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_Controllers = controllersByCodeArray($rs_Controller);

    $rs_VehicleType = $rs->Select('VehicleType', "Disabled=0");
    $a_chk_VehicleType = array();
    while ($r_VehicleType = mysqli_fetch_array($rs_VehicleType)){
        $a_chk_VehicleType[strtoupper($r_VehicleType['TitleIta'])] = $r_VehicleType['Id'];
    }



    $file = fopen($path.$ImportFile,  "r");
    $delimiter = detectDelimiter($path . $ImportFile);
    $cont = 0;
    /*

0 Numero / Riferimento
1 Data Infrazione
2 Ora Infrazione
3 Sigla Nazione Targa
4 Targa Veicolo
5 Tipo Veicolo
6 Descrizione Veicolo
7 Colore Veicolo
8 Codice Infrazione 1
9 Descrizione Infrazione 1
10 Matricola dispositivo
11 Matricola accertatore
12 Nominativo accertatore
13 Comune Rilevazione
14 Ulteriore Localizzazione
15 Kilometro
16 Direzione
17 Importo Verbale
18 Spese sostenute ente
19 Velocita' rilevata
20 Velocita' Ammessa
21 Differenza velocità
22 Foto semaforo 1
23 foto semaforo 2
24 Nominativo trasgressore
25 Comune Residenza trasgr
26 Provincia Residenza trasgr
27 Indirizzo Residenza trasgr
28 Cap Residenza trasgr
29 Sigla Nazione trasgr
30 Data Comunicazione Autonoleggio
31 Societa' di Noleggio
32 Via Autonoleggio
33 Localita' Autonoleggio
34 Provincia Autonoleggio
35 Cap Autonoleggio
36 Sigla Nazione Noleggio
37 Immagine

*/





    $a_Country = array(
        "BG" => "Z104",
        "A" => "Z102",
        "I" => "Z000",
        "CH" => "Z133",
        "LT" => "Z146",
        "H" => "Z134",
        "SK" => "Z155",
        "D" => "Z112",
        "RO" => "Z129",
        "F" => "Z110",
        "EE" => "ZZZZ",
        "GB" => "Z114",
        "PL" => "Z127",
        "NL" => "Z126",

    );




    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Targa</div>
            <div class="table_label_H col-sm-2">Data</div>
            <div class="table_label_H col-sm-2">Luogo</div>
            <div class="table_label_H col-sm-2">Ril./Data scad</div>
            <div class="table_label_H col-sm-1">Velocità</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>

            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    $row = fgets($file);
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && $row[0] != "Numero / Riferimento") {

                $cont++;
                $chk_VehiclePlate = true;


                $str_FixedWhere = "Fixed IS NULL";
                $chkFine = '';


                /*
                11      Descrizione Infrazione
                19      Importo Verbale
                20      Spese Comando
                */


                $strDetector = "";
                $ReasonId = null;

                $DetectorCode = $row[10];
                $DetectorCode = str_replace("Matricola: ", "", $DetectorCode);

                $SpeedLimit = $row[20];
                $SpeedControl = $row[19];
                $SpeedDifference = $row[21];

                $Code = $row[0];
                $FineDate = $row[1];
                $FineTime = $row[2];


                $VehiclePlate = strtoupper($row[4]);
                $VehicleTypeId = strtoupper($row[5]);


                if ($VehicleTypeId == "AUTOVETTURA") $VehicleTypeId = "AUTOVEICOLO";
                if ($VehicleTypeId == "CICLOMOTORE") $VehicleTypeId = "CICLOMOTORE";
                if ($VehicleTypeId == "A") $VehicleTypeId = "AUTOVEICOLO";
                if ($VehicleTypeId == "M") $VehicleTypeId = "MOTOVEICOLO";
                if ($VehicleTypeId == "ARTICOLATO") $VehicleTypeId = "AUTOARTICOLATO";
                if ($VehicleTypeId == "CISTERNA") $VehicleTypeId = "AUTOARTICOLATO";
                if ($VehicleTypeId == "MOTOCICLO") $VehicleTypeId = "MOTOVEICOLO";


                $VehicleBrand = $row[6];
                $VehicleColor = $row[7];


                if (strpos($FineDate, 'T') !== false) {


                    $str_DateTime = str_replace("T", "_", $FineDate);
                    $str_DateTime = str_replace(":", "-", $str_DateTime);
                    $str_DateTime = str_replace(".", "-", $str_DateTime);

                    $a_DateTime = explode("T", $FineDate);

                    $FineDate = $a_DateTime[0];
                    $FineTime = $a_DateTime[1];


                } else if ($FineTime == "") {
                    $str_DateTime = str_replace(" ", "_", $FineDate);
                    $str_DateTime = str_replace(":", "-", $str_DateTime);
                    $str_DateTime = str_replace(".", "-", $str_DateTime);


                    $a_DateTime = explode(" ", $FineDate);

                    $FineDate = $a_DateTime[0];
                    $FineTime = $a_DateTime[1];
                }

                if (strpos($FineDate, '/') !== false) {
                    $aFineDate = explode("/", $FineDate);

                    $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                    $ProtocolYear = $aFineDate[2];

                } else {
                    $ProtocolYear = substr($FineDate, 0, 4);
                }

                $aFineTime = explode(":", $FineTime);
                if (strlen($aFineTime[0]) < 2) $aFineTime[0] = "0" . $aFineTime[0];
                if (strlen($aFineTime[1]) < 2) $aFineTime[1] = "0" . $aFineTime[1];
                $FineTime = $aFineTime[0] . ":" . $aFineTime[1];


                if (strpos($row[11], "+") === false) {

                    $ControllerCode = $row[11];
                } else {

                    $a_ControllerCode = explode("+", trim($row[11]));

                    $ControllerCode = $a_ControllerCode[0];


                    if (!is_null(getControllerByCode($a_Controllers, $FineDate, $a_ControllerCode[1]))) {
                        $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    } else {
                        $error = true;
                        $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore seconda matricola ' . $a_ControllerCode[1] . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }

                }


                $Locality = strtoupper($row[13]);
                $Address = trim($row[14]);
                if (trim($row[15]) != "") $Address .= " " . trim($row[15]);


                if (trim($row[16]) != "") $Address .= " Direzione " . trim($row[16]);


                if (isset($a_Country[trim($row[3])])) {
                    $strCountry = $a_Country[trim($row[3])];
                } else {
                    $strCountry = "ZZZZ";
                }


                $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;

                $str_Date = "";
                    if ($SpeedLimit > 0) {
                        $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code=" . $DetectorCode);
                        $FindNumber = mysqli_num_rows($detectors);

                    if ($FindNumber == 0) {
                        $error = true;
                        $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod ' . $DetectorCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    } else {
                        $detector = mysqli_fetch_array($detectors);
                        $strDetector = $detector['Kind'];
                        $chkTolerance = $detector['Tolerance'];
                        $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
                        $ReasonId = $detector['ReasonId'];
                    }

                    if ($SpeedDifference > 0) {
                        $Speed = $SpeedControl;
                        $SpeedControl += $chkTolerance;


                    } else {
                        $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

                        $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                        $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


                        $Speed = $SpeedControl - $Tolerance;

                    }
                    $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chkTolerance);
                    $find = getVArticle($detector['Id'], $_SESSION['cityid'], $SpeedExcess, $ProtocolYear);
                    if ($find == null) {
                        $error = true;
                        $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo con velocità ' . $SpeedControl . ' / ' . $SpeedLimit . ' anno ' . $ProtocolYear . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                    } else {
                        $ArticleId = $find['Id'];
                        $Fee = $find['Fee'];
                        $MaxFee = $find['MaxFee'];
                        $ViolationTypeId = $find['ViolationTypeId'];

                        $aTime = explode(":", $FineTime);

                        if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                            $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                            $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                        }

                        $strArticle = $Fee . "/" . $MaxFee;


                    }


                } else {
                    $SpeedLimit = 0;
                    $SpeedControl = 0;
                    $Speed = 0;

                    if ($DetectorCode != "") {
                        $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='$DetectorCode'");
                        $FindNumber = mysqli_num_rows($detectors);

                        if ($FindNumber == 0) {
                            $error = true;
                            $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod ' . $DetectorCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                        } else {
                            $detector = mysqli_fetch_array($detectors);
                            $strDetector = $detector['Kind'];
                            $str_FixedWhere = 'Fixed=' . $detector['Fixed'];

                        }
                    } else {
                        $DetectorCode = 0;
                    }

                    $b_AdditionalArticle = false;

                    if (strpos($row[8], "+") === false) {
                        if (strpos($row[8], "/") === false) {
                            $a_Article = explode("-", trim($row[8]));
                        } else {
                            $a_Article = explode("/", trim($row[8]));
                        }
                    } else {
                        $b_AdditionalArticle = true;
                        $a_AdditionalArticle = explode("+", trim($row[8]));
                        $a_Article = explode("-", trim($a_AdditionalArticle[0]));
                    }


                    $Where .= " AND Id1=" . trim($a_Article[0]) . " AND Id2='" . trim($a_Article[1]) . "'";
                    if (isset($a_Article[2])) $Where .= " AND Id3='" . trim($a_Article[2]) . "'";


                    $finds = $rs->Select('V_Article', $Where);
                    $FindNumber = mysqli_num_rows($finds);

                    if ($FindNumber == 0) {
                        $error = true;
                        $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo ' . $a_Article[0] . ' / ' . $a_Article[1] . ' anno ' . $ProtocolYear . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                    } else {


                        $find = mysqli_fetch_array($finds);

                        if ($_SESSION['cityid'] == 'B509' || ($find['Article'] == 80 and $find['Paragraph'] == '14') || ($find['Article'] == 193 and $find['Paragraph'] == '2')) {

                            if (!$_SESSION['cityid'] == 'B509') {
                                if (in_array($VehiclePlate, $a_VehiclePlate)) {
                                    $chk_VehiclePlate = false;
                                    $cont--;

                                } else {
                                    $a_VehiclePlate[] = $VehiclePlate;
                                }
                            }


                            if (!$chk_GlobalImage) {
                                $chk_GlobalImage = true;
                                $aDocViolation = glob($path . '*.jpg');

                            }


                            $a_ArticleDate = explode(":", trim($row[9]));

                            if ($b_AdditionalArticle) {
                                $a_ArticleAdditionalDate = explode(" ", trim($a_ArticleDate[1]));
                                $str_Date = $a_ArticleAdditionalDate[0];
                            } else
                                $str_Date = substr (trim($a_ArticleDate[1]),0,10);

                            $rs_FineAll = $rs->Select('V_FineAll', "REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "' AND  ((Article=80 AND Paragraph=14) OR (Article=193 AND Paragraph =2))");
                            $FindNumber = mysqli_num_rows($rs_FineAll);
                            $chkFine = '';
                            if ($FindNumber > 0) {
                                $chkFine = ' table_caption_warning';
                            }
                        }

                        $ArticleId = $find['Id'];
                        $Fee = $find['Fee'];
                        $MaxFee = $find['MaxFee'];

                        if ($find['Article'] == 80 and $find['Paragraph'] == '14') {

                            $d1 = new DateTime($FineDate);
                            $d2 = new DateTime($str_Date);

                            $diff = $d2->diff($d1);

                            if ($diff->y >= 4) {
                                $Fee = $Fee * 2;
                                $MaxFee = $MaxFee * 2;
                            };


                        }


                        $strArticle = $Fee . "/" . $MaxFee;


                        $ViolationTypeId = $find['ViolationTypeId'];


                    }


                }


                if (!$error) {
                    $rs_reason = getReasonRs($ReasonId, $_SESSION['cityid'], $ViolationTypeId, $DetectorCode);
                    $FindNumber = mysqli_num_rows($rs_reason);
                    if ($FindNumber == 0) {
                        $error = true;
                        $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger"></div>
                        <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }
                }


                $rs_Locality = $rs->Select(MAIN_DB . '.City', "UPPER(Title)='" . trim($Locality) . "'");
                $FindNumber = mysqli_num_rows($rs_Locality);


                if ($FindNumber == 0) {
                    $error = true;
                    $Locality = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Localita ' . trim($Locality) . ' non trovata</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                } else {
                    $r_Locality = mysqli_fetch_array($rs_Locality);
                    $Locality = $r_Locality['Id'];
                }


                if ($chk_GlobalImage) {
                    $chkFile = "";

                    if ($_SESSION['cityid'] == 'B509') {
                        $str_Document = trim($row[37]);
                        $matches = preg_grep('/' . $str_Document . '/', $aDocViolation);
                        print_r($matches);
                        die;

                    } else {
                        $matches = preg_grep('/' . strtoupper($VehiclePlate) . '/', $aDocViolation);

                    }


                    $keys = array_keys($matches);


                    foreach ($matches as $key => $value) {


                        if ($_SESSION['cityid'] == 'B509') {
                            $str_DateTime = $str_Document;
                        }

                        if (strpos($value, $str_DateTime) !== false) {

                            if (file_exists($value)) {
                                $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                            } else {
                                echo $value . "<br>";
                                $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            }

                        }
                    }
                    if ($chkFile == "") $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                } else if (isset($row[37]) and strlen(trim($row[37])) > 0) {
                    $str_Document = trim($row[37]);

                    if (file_exists($path . $str_Document)) {
                        $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    } else {
                        $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    }

                } else {
                    $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                }

                $rs_fine = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
                $FindNumber = mysqli_num_rows($rs_fine);


                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_error';
                }


                if (!is_null(getControllerByCode($a_Controllers, $FineDate, $ControllerCode))) {
                    $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore matricola ' . $ControllerCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }

                if (isset($a_chk_VehicleType[$VehicleTypeId])) {
                    $chkVehicle = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkVehicle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Veicolo ' . $VehicleTypeId . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                if ($chk_VehiclePlate) {

                    $str_out .= '
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkController . ' ' . $chkVehicle . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . strtoupper($VehiclePlate) . '</div>
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                <div class="table_caption_H col-sm-3' . $chkFine . '">' . $Locality . ' ' . $Address . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strDetector . $str_Date . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $SpeedControl . '=>' . $Speed . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $SpeedLimit . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strArticle . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strCountry . '</div>
                <div class="clean_row HSpace4"></div>
			</div>    
            ';
                }

            }

        }
        fclose($file);
    }
    if(!$error){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_owner_exe.php">
            <input type="hidden" name="P" value="imp_owner.php">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
            <div class="table_label_H col-sm-12">
                Comprimi immagini
                <select name="compress">
                    <option value="0">NO</option>
                    <option value="1">SI</option>
                </select>
                <input type="submit" value="Importa" >                           
            </div >
		</div >';
    }
}

echo $str_out;


if(strlen($msgProblem)>0){
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $msgProblem;

}
