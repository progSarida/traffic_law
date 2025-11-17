<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chk_Tolerance = 0;
$error = false;
$msgProblem = "";
$str_FixedWhere = "Fixed IS NULL";
$a_chk_country = array("falso"=>"Z000", "vero"=>"ZZZZ");
$a_VehicleTypeId = array(
    "CICLOMOTORE"=>2,
    "MOTOVEICOLO"=>2
);



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
                <a href="imp_titan.php?ImportFile='.$file.'"><span class="fa fa-upload"></span></a> 
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
				<div class="table_label_H col-sm-12">ELENCO FILE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList;

echo $str_out;

if($ImportFile==""){
    $a_files = $rs->getResults($rs->ExecuteQuery("SELECT * FROM ImportedFiles WHERE CityId='".$_SESSION['cityid']."' AND Type=1 ORDER BY Date DESC"));
    $countImportedFiles = 0;
    $importedFileList = '
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-12">
				<div class="table_label_H col-sm-12">FILE IMPORTATI</div>
				<div class="clean_row HSpace4"></div>	
			</div>';
    foreach ($a_files as $a_file){
        $countImportedFiles++;
        $importedFileList .=  '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$countImportedFiles . '</div>
            <div class="table_caption_H col-sm-3">'.$a_file['Name'] . '</div>
            <div class="table_caption_H col-sm-1">'.$a_file['RowsCount'] . ' record</div>
            <div class="table_caption_H col-sm-2">Data importazione '.DateOutDB($a_file['Date']) . '</div>
            <div class="table_caption_H col-sm-5"></div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
    }
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>'. $importedFileList;
}else{





    $controllers = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_chk_controller = array();
    while ($controller = mysqli_fetch_array($controllers)){
        $a_chk_controller[] = $controller['Code'];
    }

    $file = fopen($path.$ImportFile,  "r");


    $delimiter = ";";
    $cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.' '.date("Y-m-d H:i", filemtime($path.$ImportFile)).'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">ADI</div>
            <div class="table_label_H col-sm-1">Targa</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-3">Luogo</div>
            <div class="table_label_H col-sm-2">Vel/Lim - Rilev </div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>
            <div class="table_label_H col-sm-2">Errore</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';

    $countSendedErr = 0;
    $countToFix = 0;
    $countToImport = 0;
    $countImported = 0;
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 10000, $delimiter);
            if (isset($row[0]) && strlen(trim($row[0])) > 8) {


                $cont++;
                /*
    0            ADI
    1            ID COMUNE
    2            DATA RILEVAZIONE
    3            ORA RILEVAZIONE
    4            LOCALITA' E DIREZ
    5            LIMITE
    6            VELOCITA'
    7            TOLLERANZA
    8            TIPOVEICOLO
    9            TARGA
    10            MARCA
    11            MODELLO
    12            MATRICOLA APPAR.
    13            MOTIVO MANCATA CONTESTAZIONE
    14            VEICOLO STRANIERO
    15            ACCERTATORE
    16            DATA ACCERTAMENTO
    17            ORA ACCERTAMENTO
    18            MATRICOLA ACCERTATORE
                */


                $Iuv = $row[0];
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


                $originalFineTime = $FineTime;
                $a_FineTime = explode(".", $FineTime);
                $FineTime = $a_FineTime[0];
                if (strlen($FineTime) != 8 || strpos($FineTime, ".") !== false || strpos($FineTime, ":") === false) {
                    $error = true;
                    $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">PROBLEMA RILEVATO NELL\'ORARIO DELLA RILEVAZIONE ' . $originalFineTime . ' FORMATTATO IN ' . $FineTime . '</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                }

                if (strpos($FineDate, '/') !== false) {
                    $aFineDate = explode("/", $FineDate);

                    $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                    $ProtocolYear = $aFineDate[2];

                } else {
                    $ProtocolYear = substr($FineDate, 0, 4);
                }


                $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode . "'");
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
                    $chk_Tolerance = $detector['Tolerance'];
                    $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
                    $DetectorId = $detector['Id'];
                    $ReasonId = $detector['ReasonId'];


                    if ($ReasonId == 0) {
                        $error = true;
                        $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger"></div>
                        <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione legata al rilevatore non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }

                }


                $chk_Tolerance = ($chk_Tolerance > FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;

                $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                $Tolerance = ($TolerancePerc < $chk_Tolerance) ? $chk_Tolerance : $TolerancePerc;


                $Speed = $SpeedControl - $Tolerance;
                $SpeedExcess = $Speed - $SpeedLimit;


                if ($SpeedLimit >= $Speed) {
                    $error = true;
                    $SpeedLimit .= '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Limite: ' . $SpeedLimit . ' - Velocità:' . $Speed . '</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


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
                $FindNumber = mysqli_num_rows($finds);

                if ($FindNumber == 0) {
                    $error = true;
                    $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo non assegnato al rilevatore</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                } else {
                    $find = mysqli_fetch_array($finds);

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


                if (array_key_exists($CountryId, $a_chk_country)) {
                    $strCountry = $a_chk_country[$CountryId];
                } else {
                    $error = true;
                    $strCountry = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $CountryId . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND IuvCode='" . $Iuv . "'");
                $FindNumber = mysqli_num_rows($fines);

                $a_fine = array();
                $chkFine = '';
                if ($FindNumber > 0) {
                    $chkFine = ' alert-success';
                    $a_fine = $rs->getArrayLine($fines);
                }

                if (in_array($ControllerCode, $a_chk_controller)) {
                    $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger"> Accertatore matr. ' . $ControllerCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='" . $_SESSION['cityid'] . "' AND VehiclePlate='" . $VehiclePlate . "'");

                if (mysqli_num_rows($rs_VehicleWhiteList) > 0) {
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Targa ' . $VehiclePlate . ' presente nella white list</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }

                $str_db = "";
                if (isset($a_fine['Id'])) {
                    if ($a_fine['FineTime'] != $FineTime) {
                        if ($a_fine['StatusTypeId'] < 15 || substr($a_fine['FineTime'], 0, 5) == substr($FineTime, 0, 5)) {
                            $chkFine = ' alert-warning';
                            $countToFix++;
                        } else {
                            $chkFine = ' alert-danger';
                            $countSendedErr++;
                        }

                        $str_db .= 'ORARIO!! File ' . $FineTime . ' - Database  ' . $a_fine['FineTime'];
                    } else {
                        $countImported++;
//                    continue;
                    }
                } else {
                    $countToImport++;
                }

                $str_out .= '
                <div class="col-sm-12"> 
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $Iuv . ' ' . $chkController . ' </div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $VehiclePlate . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                    <div class="table_caption_H col-sm-3' . $chkFine . '">' . $Address . '</div>
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $SpeedControl . "/" . $SpeedLimit . "-" . $strDetector . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strArticle . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strCountry . '</div>
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $str_db . '</div>
                    <div class="clean_row HSpace4"></div>
                </div>';
            }

        }
        fclose($file);
    }
    if(!$error){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_titan_exe.php">
            <input type="hidden" name="P" value="imp_titan.php">
            <input type="hidden" name="ImportFile" value="' .$ImportFile.'">
            <div class="table_label_H col-sm-12">
                <div class="table_caption_H col-sm-4"><b>TOTALE '.$cont.'</b></div>
                <div class="table_caption_H col-sm-2"><b>IMPORTATI '.$countImported.'</b></div>
                <div class="table_caption_H col-sm-2"><b>DA CORREGGERE '.$countToFix.'</b></div>
                <div class="table_caption_H col-sm-2"><b>DA IMPORTARE '.$countToImport.'</b></div>               
                <div class="table_caption_H col-sm-2"><b>USCITI ERRATI '.$countSendedErr.'</b></div>          
            </div >
            <div class="table_label_H col-sm-12">
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