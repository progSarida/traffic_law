<?php
if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "csv") {
            $Cont++;
            $FileList .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . $Cont . '</div>
            <div class="table_caption_H col-sm-10">' . $file . '</div>
            <div class="table_caption_button col-sm-1">
                ' . ChkButton($aUserButton, 'imp', '<a href="' . $str_CurrentPage . '&ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>') . '
                &nbsp;
            </div>
            <div class="clean_row HSpace4"></div>
			</div>    
			';
        }
    }

    closedir($directory_handle);
}
if ($Cont == 0) {
    $FileList = '
            <div class="col-sm-12">
                <div class="table_caption_H col-sm-11">Nessun file presente</div>
                <div class="table_caption_button col-sm-1"></div>
			    <div class="clean_row HSpace4"></div>
			</div>    
			';
}

$str_out = '
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="' . $_SESSION['blazon'] . '" style="width:50px;">
					<span class="title_city">' . $_SESSION['citytitle'] . ' ' . $_SESSION['year'] . '</span>
				</div>
			</div>
		</div>		
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-11">ELENCO FILE</div>
				<div class="table_add_button col-sm-1 right">
        			' . ChkButton($aUserButton, 'add', '<a href="mgmt_violation_add.php"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>') . '      				
				</div>
				<div class="clean_row HSpace4"></div>	
			</div>
            	
            ' . $FileList;

echo $str_out;


if ($ImportFile == "") {
    $str_out =
        '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L\'IMPORTAZIONE</div>
				<div class="clean_row HSpace4"></div>	
			</div>
		</div>';
} else {


    $countries = $rs->Select('Country', "KriaCode!=''");
    $a_chk_country = array();
    while ($country = mysqli_fetch_array($countries)) {
        $a_chk_country[$country['KriaCode']] = $country['Title'];
    }

    $rs_Controller = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
    $a_Chk_ControllerName = $a_Chk_ControllerCode = array();
    while ($r_Controller = mysqli_fetch_array($rs_Controller)) {
        $a_Chk_ControllerName[] = $r_Controller['Name'];
        $a_Chk_ControllerCode[] = $r_Controller['Code'];
    }


    $file = fopen($path . $ImportFile, "r");
    $delimiter = ";";
    $cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Targa</div>
            <div class="table_label_H col-sm-2">Data</div>
            <div class="table_label_H col-sm-2">Luogo</div>
            <div class="table_label_H col-sm-2">Rilevatore</div>
            <div class="table_label_H col-sm-1">Velocità</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>

            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    if(is_resource($file)) {
        $fileEncoding=null;
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if($fileEncoding==null)
                $fileEncoding=mb_detect_encoding($row[0]);

            if (isset($row[0]) && $row[0] != 'Targa') {
                $cont++;
                $DetectorCode = $row[11];
                $rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode."'");
                $n_Record = mysqli_num_rows($rs_Detector);


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
                    $VehiclePlate=mb_convert_encoding($row[0],"UTF-8",$fileEncoding);
                    $VehiclePlate = str_replace("<?>", "*", strtoupper($VehiclePlate));

                    $FineTime = $row[2];
                    $FineDate = $row[1];
                    $VehicleTypeId = $a_VehicleTypeId[strtolower($row[7])];


                    $aFineDate = explode("/", $FineDate);


                    $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                    $ProtocolYear = $aFineDate[2];

                    $SpeedControl = $row[5];
                    $str_Address = $row[3];

                    if ($r_Detector['DetectorTypeId'] == 1) {
                        $chk_Tolerance = $r_Detector['Tolerance'];
                        //$DetectorCode = $row[4];
                        $SpeedLimit = $row[6];
                        $str_Locality = "";

                        if ($DetectorCode == 2085) {
                            $SpeedLimit = ($VehicleTypeId == 2 || $VehicleTypeId == 9) ? 60 : 80;
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


                        $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


                        if ($SpeedExcess <= 10) {
                            $Where .= " AND Article=142 AND Paragraph=7";
                        } elseif ($SpeedExcess <= 40) {
                            $Where .= " AND Article=142 AND Paragraph=8";
                        } elseif ($SpeedExcess <= 60) {
                            $Where .= " AND Article=142 AND Paragraph=9 AND Letter!='bis'";
                        } else {
                            $Where .= " AND Article=142 AND Paragraph=9 AND Letter='bis'";
                        }


                        if (file_exists($path . $row[9])) {
                            $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
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


                        if (in_array(trim($row[13]), $a_Chk_ControllerName)) {
                            $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                        } else {
                            $error = true;
                            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($row[13]) . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                        }


                    } else {
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


                        $a_Locality = explode("(", $str_Address);
                        $str_Locality = $a_Locality[0];

                        $str_Address = trim(substr($str_Address, strpos($str_Address, ')') + 1));


                        $rs_Locality = $rs->Select(MAIN_DB . '.City', "Title='" . $str_Locality . "'");

                        if (mysqli_num_rows($rs_Locality) == 0) {

                            $error = true;
                            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
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


                        $chk_Tolerance = 0;
                        $SpeedLimit = 0;

                        $Where = "Article=146 AND Paragraph=3 AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;

                        if (in_array(trim($row[13]), $a_Chk_ControllerCode)) {
                            $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                        } else {
                            $error = true;
                            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($row[13]) . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                        }

                        if (file_exists($path . $row[9]) && file_exists($path . $row[18])) {
                            $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
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


                    $finds = $rs->Select('V_Article', $Where);
                    $FindNumber = mysqli_num_rows($finds);

                    if ($FindNumber == 0) {
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

                        $find = mysqli_fetch_array($finds);

                        $ArticleId = $find['Id'];
                        $Fee = $find['Fee'];
                        $MaxFee = $find['MaxFee'];
                        $ViolationTypeId = $find['ViolationTypeId'];
                        $AdditionalNight = $find['AdditionalNight'];


                        if ($AdditionalNight) {
                            $aTime = explode(":", $FineTime);

                            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                            }

                        };


                        $strArticle = $Fee . "/" . $MaxFee;
                    }
                    $rs_Reasons = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=" . $ViolationTypeId . " AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "'");
                    $n_rsNumber = mysqli_num_rows($rs_Reasons);

                    if ($n_rsNumber == 0) {
                        $error = true;
                        $str_ChkLocality = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Contestazione non presente per quest\'articolo </div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    } else {
                        $rs_Reason = mysqli_fetch_array($rs_Reasons);
                        $ReasonId = $rs_Reason['Id'];
                    }

                    $Country = $row[8];
                    if ($Country == "I") $Country = "IT";

                    if (array_key_exists($Country, $a_chk_country)) {
                        $strCountry = $a_chk_country[$Country];
                    } else {
                        $error = true;
                        $strCountry = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $row[8] . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }


                    $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . $VehiclePlate . "'");
                    $FindNumber = mysqli_num_rows($fines);

                    $chkFine = '';
                    if ($FindNumber > 0) {
                        $chkFine = ' table_caption_error';
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

                }


                $str_out .= '
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkController . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $VehiclePlate . '</div>
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . $str_Locality . $str_Address . '</div>
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
    }
    if (!$error) {
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_kria_U480_exe.php">
            <input type="hidden" name="P" value="imp_kria.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
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

echo $str_out;


if (strlen($msgProblem) > 0) {
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $msgProblem;

}

/*
richiesta spc


3395799150      andrea pappaianni

*/