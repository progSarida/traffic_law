<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");

include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER . "/" . $_SESSION['cityid'] . "/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;
$error = false;
$msgProblem = "";

$rs= new CLS_DB();
$rs->SetCharset('utf8');

if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "csv" && strpos(strtolower($file), "errori") === false) {
            $Cont++;
            $FileList .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . $Cont . '</div>
            <div class="table_caption_H col-sm-10">' . $file . '</div>
            <div class="table_caption_button col-sm-1">
                <a href="imp_meg.php?1&ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>
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

    $countries = $rs->Select('Country', "MegaspCode!=''");
    $a_chk_country = array();
    while ($country = mysqli_fetch_array($countries)) {
        $a_chk_country[$country['MegaspCountry']] = $country['Title'];
    }



    $rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_chk_controller = array();
    while ($r_Controller = mysqli_fetch_array($rs_Controller)){
        $a_chk_controller[$r_Controller['Code']] = $r_Controller['Name'];
    }

    $a_VehicleTypeId = array(
        "AUTOVEICOLO" => "Autoveicolo",
        "AUTOVETTURA" => "Autoveicolo",
        "MOTOVEICOLO" => "Motoveicolo",
        "AUTOVETTURA PUBBLICA" => "Autoveicolo",
        "AUTOCARRO" => "Autocarro",
        "TRATTORE STRADALE" => "Trattore",
        "CAMPER" => "Autocaravan",
        "AUTOCARAVAN" => "Autocaravan",
        "RIMORCHIO" => "Rimorchio",
        "AUTOBUS" => "Autobus",
        "AUTOBUS EXTRAURBANA" => "Autobus",
        "AUTOTRENO CON RIMORCHIO" => "Autoarticolato",
        "MOTOCICLO"=>"Motoveicolo",
        "CICLOMOTORE"=>"Ciclomotore",
        "AUTOSNODATO O AUTOARTICOL" => "Autoarticolato",
    );






    $file = fopen($path . $ImportFile, "r");
    $delimiter = detectDelimiter($path . $ImportFile);
    $Cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Veicolo</div>
            <div class="table_label_H col-sm-2">Targa</div>
            <div class="table_label_H col-sm-2">Nazione</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-1">Ora</div>
            <div class="table_label_H col-sm-1">Accertatore</div>
            <div class="table_label_H col-sm-2">Via</div>
            <div class="table_label_H col-sm-1">Articolo</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';





    if(is_resource($file)) {
        while (!feof($file)) {

            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && trim($row[0]) != "Ida") {

                $Cont++;
                $strCountry = "";
                $strCountryCode = "";
                $str_FixedWhere = "Fixed IS NULL";

                $str_Folder = trim($row[0]);
                $Code = trim($row[3]) . "/" . trim($row[2]) . "/" . trim($row[1]);
                $VehiclePlate = trim($row[11]);
                $VehiclePlate = str_replace("-", "", $VehiclePlate);
                $VehiclePlate = str_replace(",", "", $VehiclePlate);


                $FineDate = trim($row[4]);

                $FineTime = trim($row[5]);

                $ControllerCode = trim($row[20]);
                $a_ControllerCode = explode("-", $ControllerCode);
                $ControllerCode = $a_ControllerCode[0];

                $DetectorCode = trim($row[14]);


                if (isset($a_chk_controller[$ControllerCode])) {
                    $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore matricola ' . $ControllerCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                $aFineDate = explode("/", $FineDate);


                $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];

                $ProtocolYear = $aFineDate[2];

                $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;

                if ($DetectorCode != "") {
                    $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode . "'");
                    $FindNumber = mysqli_num_rows($detectors);

                    if ($FindNumber == 0) {
                        $error = true;
                        $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod ' . $DetectorCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    } else {
                        $detector = mysqli_fetch_array($detectors);
                        $strDetector = $detector['Kind'];
                        $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
                        $chk_Tolerance = $detector['Tolerance'];
                        $chk_Tolerance = ($chk_Tolerance > FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;
                        $SpeedLimit = trim($row[8]);
                        $SpeedControl = trim($row[9]);
                        $Speed = trim($row[10]);


                        $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                        $Tolerance = ($TolerancePerc < $chk_Tolerance) ? $chk_Tolerance : $TolerancePerc;


                        if ($SpeedLimit >= $Speed) {


                            $error = true;
                            $SpeedLimit .= '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Limite: ' . $SpeedLimit . ' - Velocità:' . $Speed . '</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                        }

                        $Speed = $SpeedControl - $Tolerance;
                        $SpeedExcess = $Speed - $SpeedLimit;

                        if ($SpeedExcess <= 10) {
                            $Where .= " AND Article=142 AND Paragraph=7";
                        } elseif ($SpeedExcess <= 40) {
                            $Where .= " AND Article=142 AND Paragraph=8";
                        } elseif ($SpeedExcess <= 60) {
                            $Where .= " AND Article=142 AND Paragraph=9 AND Letter!='bis'";
                        } else {
                            $Where .= " AND Article=142 AND Paragraph=9 AND Letter='bis'";
                        }


                    }
                } else {
                    $DetectorCode = 0;
                    $SpeedLimit = 0;
                    $SpeedControl = 0;
                    $Speed = 0;

                    $Where .= " AND Article=126 AND Paragraph='0' AND Letter='bis'";


                }


                $finds = $rs->Select('V_Article', $Where);
                $FindNumber = mysqli_num_rows($finds);

                if ($FindNumber == 0) {
                    $error = true;
                    $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo con velocità ' . $SpeedControl . ' / ' . $SpeedLimit . ' anno ' . $ProtocolYear . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                } else {

                    $article = mysqli_fetch_array($finds);

                    $ArticleId = $article['Id'];
                    $Fee = $article['Fee'];
                    $MaxFee = $article['MaxFee'];

                    $aTime = explode(":", $FineTime);

                    if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                        $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                        $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                    }

                    $strArticle = $Fee . "/" . $MaxFee;


                    $ViolationTypeId = $article['ViolationTypeId'];


                    $str_WhereReason = $str_FixedWhere . " AND ReasonTypeId=1 AND CityId='" . $_SESSION['cityid'] . "'";
                    switch ($ViolationTypeId) {
                        case 4:
                        case 6:
                            $str_WhereReason .= ($DetectorCode == 0) ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" . $ViolationTypeId;
                            break;

                        default:
                            $str_WhereReason .= " AND ViolationTypeId=" . $ViolationTypeId;
                    }

                    $rs_Reason = $rs->Select('Reason', $str_WhereReason);
                    if (mysqli_num_rows($rs_Reason) == 0) {
                        $error = true;
                        $chkArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                            <div class="col-sm-12">
                            <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                                <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione per Articolo non presente:' . $str_WhereReason . '</div>
                                <div class="clean_row HSpace4"></div>
                            </div>    
                            ';

                    }


                }


                $NameSurname = trim($row[29]) . " " . trim($row[30]);
                $Address = trim($row[35]);
                $ZIPCity = trim($row[34]);
                $Country = trim($row[33]);
                $CountryCode = trim($row[12]);


                $str_Article = "";
                $chkArticle = "";


                $CityAddress = trim($row[7]);


                if ($CountryCode != "") {

                    if (array_key_exists($CountryCode, $a_chk_country)) {
                        $strCountryCode = $a_chk_country[$CountryCode];
                    } else {
                        $error = true;
                        $strCountryCode = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $CountryCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }
                }

                $str_CheckFolder = "";
                if (!file_exists($path . $str_Folder)) {
                    $str_CheckFolder = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                } else {
                    $a_File = array_diff(scandir($path . $str_Folder), array('.', '..'));

                    foreach ($a_File as $key => $value) {

                        if (strpos($value, "." === false)) {
                            $error = true;
                            $strCountry = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            $msgProblem .= '
                            <div class="col-sm-12">
                            <div class="table_caption_H col-sm-1 alert-warning">' . $str_Folder . '</div>
                                <div class="table_caption_H col-sm-11 alert-warning">Controllare cartella e file</div>
                                <div class="clean_row HSpace4"></div>
                            </div>
                            ';

                        }
                    }
                }


                $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND (REPLACE(VehiclePlate,'  ','') = '" . htmlentities($VehiclePlate) . "')");
                $FindNumber = mysqli_num_rows($fines);

                if ($FindNumber > 0) {

                    $msgProblem .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-info">' . $str_Folder . '</div>
                <div class="table_caption_H col-sm-11 alert-info">Verbale con targa ' . htmlentities($VehiclePlate) . ' già presente</div>
                <div class="clean_row HSpace4"></div>
            </div>    
            ';

                }


                $str_out .= '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">(' . $Cont . ') ' . $str_CheckFolder . $str_Folder . '</div>
            <div class="table_caption_H col-sm-2">' . $VehiclePlate . '</div>
            <div class="table_caption_H col-sm-2">' . $strCountry . '</div>
            <div class="table_caption_H col-sm-1">' . $FineDate . '</div>
            <div class="table_caption_H col-sm-1">' . $FineTime . '</div>
            <div class="table_caption_H col-sm-4">' . $CityAddress . '</div>
            <div class="table_caption_H col-sm-1">' . $chkArticle . $str_Article . '</div>



            <div class="table_caption_H col-sm-2">' . $Code . '</div>
            
            <div class="table_caption_H col-sm-3">' . $NameSurname . '</div>
            <div class="table_caption_H col-sm-3">' . $Address . '</div>
            <div class="table_caption_H col-sm-3">' . $ZIPCity . ' ' . $Country . ' ' . $strCountryCode . '</div>
            <div class="table_caption_H col-sm-1">' . $Fee . '</div>



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
            <form name="f_import" action="imp_meg_exe.php">
            <input type="hidden" name="P" value="imp_meg.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-12">
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
