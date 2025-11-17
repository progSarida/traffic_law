<?php

mysqli_set_charset($rs->conn,'utf8_unicode_ci');
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
                    '. ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>') .'
                    &nbsp;
                </div>
                <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}

$str_out .='
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">ELENCO FILE</div>
            <div class="clean_row HSpace4"></div>
        </div>';

if($Cont==0){
    $str_out .='
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-12">Nessun file presente</div>
		    <div class="clean_row HSpace4"></div>
		</div>    
		';
} else {
    $str_out .= $FileList;

}

if($ImportFile==""){
    if($Cont>0){
        $str_out .= '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>
			</div>
		</div>';
    }
}else{

    $str_out .= '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.'</div >
			</div >
		</div >

        <div class="clean_row HSpace4" ></div >

        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Targa</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-3">Luogo</div>
            <div class="table_label_H col-sm-2">Rilevatore</div>
            <div class="table_label_H col-sm-1">Velocità</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Sanzione</div>
            <div class="table_label_H col-sm-1">Nazione</div>
        </div>

        <div class="clean_row HSpace4"></div>';


    $countries = $rs->Select('Country', "KriaCode!=''");
    $a_chk_country = array();
    while ($country = mysqli_fetch_array($countries)){
        $a_chk_country[$country['KriaCode']]=$country['Title'];
    }

    $rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_Chk_ControllerName = $a_Chk_ControllerCode = array();
    while ($r_Controller = mysqli_fetch_array($rs_Controller)){
        $a_Chk_ControllerName[] = $r_Controller['Name'];
        $a_Chk_ControllerCode[] = $r_Controller['Code'];
    }


    $file = fopen($path.$ImportFile,  "r");
    $delimiter = ";";
    $cont = 0;

    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            
            if (isset($row[0]) && $row[0] != 'Targa') {
                //Converte ogni valore nella riga in UTF-8, nel caso vi siano caratteri non validi
                $row = array_map(function($entry){
                    return mb_convert_encoding($entry,"UTF-8",mb_detect_encoding($entry, 'UTF-8,ISO-8859-1,WINDOWS-1252', true) ?: null);
                }, $row);
                
                $cont++;
                $DetectorCode = $row[11];
                $rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode."'");
                $n_Record = mysqli_num_rows($rs_Detector);
                
                //CONTROLLO ESISTENZA RILEVATORE
                if ($n_Record == 0) {
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
                    $r_Detector = mysqli_fetch_array($rs_Detector);
                    $strDetector = $r_Detector['Kind'];
                    $VehiclePlate=$row[0];
                    $VehiclePlate = str_replace("<?>", "*", strtoupper($VehiclePlate));
                    $FineTime = $row[2];
                    $FineDate = $row[1];
                    $VehicleTypeId = $a_VehicleTypeId[strtolower($row[7])];
                    $aFineDate = explode("/", $FineDate);
                    $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                    $ProtocolYear = $aFineDate[2];
                    $SpeedControl = $row[5];
                    $str_Address = $row[3];

                    //VELOCITà
                    if ($r_Detector['DetectorTypeId'] == 1) {
                        $chk_Tolerance = $r_Detector['Tolerance'];
                        //02/02/2022: è stato modificato il tracciato e il nuovo limite fornito è basato sul tipo di veicolo, nel caso fosse assente usa quello
                        //standard usato precedentemente
                        $SpeedLimit = !empty($row[21]) ? $row[21] : $row[6];
                        $str_Locality = "";
                        $chk_Tolerance = ($chk_Tolerance > FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;
                        $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                        $Tolerance = ($TolerancePerc < $chk_Tolerance) ? $chk_Tolerance : $TolerancePerc;
                        $Speed = $SpeedControl - $Tolerance;
                        $SpeedExcess = $Speed - $SpeedLimit;
                        
                        //CONTROLLO LIMITE >= VELOCITà
                        if ($SpeedLimit >= $Speed) {
                            $SpeedLimit .= ' <i class="glyphicon glyphicon-exclamation-sign" style="color:orange"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-warning">Limite: ' . $SpeedLimit . ' - Velocità:' . $Speed . '</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';
                        }

                        //CONTROLLO ARTICOLI
                        if (!$ArticleRow = getVArticle($r_Detector['Id'], $_SESSION['cityid'], $SpeedExcess, $ProtocolYear)) {
                            $error = true;
                            $strDetector .= ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Articolo non trovato. Controllare la configurazione del rilevatore con codice import: '.$DetectorCode.'</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>
                                ';
                        }

                        //CONTROLLO ACCERTATORE
                        if (in_array(trim($row[13]), $a_Chk_ControllerName)) {
                            $chkController = ' <i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                        } else {
                            $error = true;
                            $chkController = ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($row[13]) . ' non presente</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>
                                ';
                        }
                        
                        //CONTROLLO IMMAGINE
                        if (file_exists($path . $row[9])) {
                            $chkFile = ' <i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                        } else {
                            $error = true;
                            $chkFile = ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Immagine non presente</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';
                        }
                    } 
                    //SEMAFORO
                    else {
                        $chk_Tolerance = 0;
                        $SpeedLimit = 0;
                        
                        //CONTROLLO PRESENZA VELOCITà
                        if ($SpeedControl != "-1") {
                            $error = true;
                            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Velocità ' . $SpeedControl . ' maggiore di 0</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';
                        }
                        
                        //CONTROLLO ARTICOLI
                        if (!$ArticleRow = getSArticle($r_Detector['Id'], $_SESSION['cityid'], $ProtocolYear)) {
                            $error = true;
                            $strDetector .= ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Articolo non trovato. Controllare la configurazione del rilevatore con codice import: '.$DetectorCode.'</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>
                                ';
                        }

                        $a_Locality = explode("(", $str_Address);
                        $str_Locality = $a_Locality[0];
                        $str_Address = trim(substr($str_Address, strpos($str_Address, ')') + 1));
                        $rs_Locality = $rs->Select(MAIN_DB . '.City', "Title='" . $str_Locality . "'");

                        //CONTROLLO LOCALITà
                        if (mysqli_num_rows($rs_Locality) == 0) {
                            $error = true;
                            $chkController = ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Località non trovata</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';
                        } else {
                            $r_Locality = mysqli_fetch_array($rs_Locality);
                            $str_Locality = $r_Locality['Title'] . " - ";
                        }

                        //CONTROLLO ACCERTATORE
                        if (in_array(trim($row[13]), $a_Chk_ControllerCode)) {
                            $chkController = ' <i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                        } else {
                            $error = true;
                            $chkController = ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($row[13]) . ' non presente</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>
                                ';
                        }
                        
                        //CONTROLLO IMMAGINI
                        if (file_exists($path . $row[9]) && file_exists($path . $row[18])) {
                            $chkFile = ' <i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                        } else {

                            $error = true;
                            $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                                <div class="col-sm-12">
                                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                    <div class="table_caption_H col-sm-11 alert-danger">Immagine non presente</div>
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';
                        }
                    }
                    
                    //SE L'ARTICOLO C'è PRENDO I DATI
                    if($ArticleRow){
                        $ArticleId = $ArticleRow['Id'];
                        $Fee = $ArticleRow['Fee'];
                        $MaxFee = $ArticleRow['MaxFee'];
                        $ViolationTypeId = $ArticleRow['ViolationTypeId'];
                        $AdditionalNight = $ArticleRow['AdditionalNight'];
                        
                        if ($AdditionalNight) {
                            $aTime = explode(":", $FineTime);
                            
                            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                                $Fee = number_format($Fee + round($Fee / FINE_NIGHT, 2),2,'.','');
                                $MaxFee = number_format($MaxFee + round($MaxFee / FINE_NIGHT, 2),2,'.','');
                                
                            }
                        };
                        
                        $strArticle = $Fee . " / " . $MaxFee;
                        
                        //CONTROLLO MANCATA CONTESTAZIONE
                        if(mysqli_num_rows(getReasonRs($r_Detector['ReasonId'], $_SESSION['cityid'], $ViolationTypeId, $DetectorCode)) <= 0){
                            $error = true;
                            $strDetector .= ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                            <div class="col-sm-12">
                            <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione non presente. Controllare le mancate contestazioni o la configurazione del rilevatore con codice import: '.$DetectorCode.'</div>
                                <div class="clean_row HSpace4"></div>
                            </div>
                            ';
                        }
                    } else {
                        $strArticle = '';
                    }

                    $Country = $row[8];
                    if ($Country == "I") $Country = "IT";

                    //CONTROLLO NAZIONE
                    if (array_key_exists($Country, $a_chk_country)) {
                        $strCountry = $a_chk_country[$Country];
                    } else {
                        $error = true;
                        $strCountry = ' <i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $row[8] . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }


                    $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . $VehiclePlate . "' ");
                    $FindNumber = mysqli_num_rows($fines);

                    $chkFine = '';
                    //SE IL VERBALE ESISTE COLORO LA RIGA DI ROSSO
                    if ($FindNumber > 0) {
                        $chkFine = ' table_caption_error';
                    }

                    $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='" . $_SESSION['cityid'] . "' AND VehiclePlate='" . $VehiclePlate . "'");

                    //CONTROLLO WHITELIST
                    if (mysqli_num_rows($rs_VehicleWhiteList) > 0) {
                        $msgProblem .= '
                            <div class="col-sm-12">
                            <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                <div class="table_caption_H col-sm-11 alert-danger">Targa ' . $VehiclePlate . ' presente nella white list</div>
                                <div class="clean_row HSpace4"></div>
                            </div>    
                            ';
                    }

                }


                $str_out .= '
                    <div class="col-sm-12"> 
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkController . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $VehiclePlate . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                        <div class="table_caption_H col-sm-3' . $chkFine . '">' . $str_Locality . $str_Address . '</div>
                        <div class="table_caption_H col-sm-2' . $chkFine . '">' . $strDetector . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $row[5] . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $SpeedLimit . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strArticle . '</div>
                        <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strCountry . '</div>
                        <div class="clean_row HSpace4"></div>
        			</div>    
                    ';
            }

        }
        fclose($file);
    } else {
        $error = true;
        $_SESSION['Message']['Error'] = 'Errore nell\'apertura del file: '.$ImportFile;
    }
    
    if(!$error && $cont > 0){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_kria_exe.php">
            <input type="hidden" name="P" value="imp_kria.php">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
            <div class="table_label_H col-sm-12">
                Comprimi immagini
                <select name="compress">
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
                <input type="submit" value="Importa" >                           
            </div >
		</div >';
    }

}

if(strlen($msgProblem)>0){
    $str_out .= '
		<div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>
		</div>'. $msgProblem;
    
}

echo $str_out;


/*
richiesta spc


3395799150      andrea pappaianni

*/