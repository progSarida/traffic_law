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
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;
$error = false;
$msgProblem = "";

$rs= new CLS_DB();
$rs->SetCharset('utf8');

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
                <a href="imp_etruria.php?1&ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>&nbsp;
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
        $a_chk_country_code[$country['MegaspCode']] = $country['Title'];
    }


    $countries = $rs->Select('Country', "MegaspCountry!=''");
    $a_chk_country = array();
    while ($country = mysqli_fetch_array($countries)) {
        $a_chk_country[$country['MegaspCountry']] = $country['Title'];
    }


    $a_VehicleTypeId = array(
        "A" => "Autoveicolo",
    );









    $Controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
    while ($Controller = mysqli_fetch_array($Controllers)) {
        $a_controllername[$Controller['Code']] = $Controller['Name'];
        $a_controllerid[$Controller['Code']] = $Controller['Id'];

    }



    $file = fopen($path . $ImportFile, "r");





    $delimiter = ";";
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

            $a_Row = fgetcsv($file, 10000, $delimiter);


            if (isset($a_Row[0]) && trim($a_Row[0]) != 'NOME FILE IMMAGINE VERBALE') {

                $Cont++;
                $strCountry = "";
                $strCountryCode = "";
                $str_FixedWhere = "Fixed IS NULL";

                $a_Documentation = array();
                if (trim($a_Row[0]) != "") $a_Documentation[] = trim($a_Row[0]);
                if (trim($a_Row[43]) != "") $a_Documentation[] = trim($a_Row[43]);
                if (trim($a_Row[63]) != "") $a_Documentation[] = trim($a_Row[63]);


                $Code = trim($a_Row[1]);
                $VehiclePlate = trim($a_Row[8]);
                $VehiclePlate = str_replace("-", "", $VehiclePlate);
                $VehiclePlate = str_replace(",", "", $VehiclePlate);
                $VehiclePlate = str_replace("*", "", $VehiclePlate);


                $VehicleModel = trim($a_Row[10]);

                $ZIPCity = "";
                $strCountryCode = $NameSurname = $Address = $Country = "";


                if (trim($a_Row[9]) == "") {
                    // nazione Z000 + noleggio
                    //data accertamento $a_Row[48]
                    $CountryCode = "Z000";
                    $NameSurname = trim($a_Row[56]) . " " . trim($a_Row[57]);
                    $Address = trim($a_Row[58]);
                    $Country = trim($a_Row[62]);


                } else $CountryCode = trim($a_Row[9]);


                $FineDate = trim($a_Row[3]);
                $FineTime = $a_Row[4];


                $ProtocolYear = substr($FineDate, 0, 4);


                $CityAddress = trim(trim($a_Row[6] . " " . $a_Row[7]));


                $ArticleId1 = trim($a_Row[19]);
                $ArticleId3 = "";

                if (strpos($a_Row[20], "e") === false) {
                    $ArticleId2 = trim($a_Row[20]);
                } else {
                    $a_Article = explode("e", trim($a_Row[20]));
                    $ArticleId2 = trim($a_Article[0]);
                    $ArticleId3 = trim($a_Article[1]);
                }


                $str_Article = trim($ArticleId1 . "/" . $ArticleId2 . " " . $ArticleId3);


                $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;

                $SpeedLimit = trim($a_Row[44]);
                if ($SpeedLimit == "") $SpeedLimit = 0;

                if ($SpeedLimit > 0) {


                    $SpeedDifference = trim($a_Row[45]);

                    $DetectorCode = trim($a_Row[41]);

                    $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Number='" . $DetectorCode . "'");
                    $FindNumber = mysqli_num_rows($detectors);

                    if ($FindNumber == 0) {
                        $error = true;
                        $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod ' . $DetectorCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    } else {
                        $detector = mysqli_fetch_array($detectors);
                        $strDetector = $detector['Kind'];
                        $chkTolerance = $detector['Tolerance'];
                        $str_FixedWhere = 'Fixed=' . $detector['Fixed'];
                    }


                    if ($SpeedDifference > 0) {
                        $Speed = $SpeedLimit += $chkTolerance;
                        $SpeedControl = $SpeedDifference;

                    } else {
                        $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

                        $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                        $Tolerance = ($TolerancePerc < $chkTolerance) ? $chkTolerance : $TolerancePerc;


                        $Speed = $SpeedControl - $Tolerance;

                    }

                    $SpeedExcess = $Speed - $SpeedLimit;


                    if ($SpeedExcess <= 10) {
                        $Where .= " AND Article=142 AND Paragraph='7'";
                    } elseif ($SpeedExcess <= 40) {
                        $Where .= " AND Article=142 AND Paragraph='8'";
                    } elseif ($SpeedExcess <= 60) {
                        $Where .= " AND Article=142 AND Paragraph='9' AND Letter!='bis'";
                    } else {
                        $Where .= " AND Article=142 AND Paragraph='9' AND Letter='bis'";
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

                    }

                } else {
                    $SpeedLimit = 0;
                    $SpeedControl = 0;
                    $Speed = 0;

                    $DetectorCode = 0;


                    $Where .= " AND Id1=" . $ArticleId1 . " AND Id2='" . $ArticleId2 . "'";

                    if ($ArticleId3 != "") $Where .= " AND Id3='" . $ArticleId3 . "'";

                    $finds = $rs->Select('V_Article', $Where);
                    $FindNumber = mysqli_num_rows($finds);

                    if ($FindNumber == 0) {
                        $error = true;
                        $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo ' . $ArticleId1 . ' / ' . $ArticleId2 . ' anno ' . $ProtocolYear . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                    } else {

                        $find = mysqli_fetch_array($finds);

                        $ArticleId = $find['Id'];
                        $Fee = $find['Fee'];
                        $MaxFee = $find['MaxFee'];

                        $ViolationTypeId = $find['ViolationTypeId'];

                    }


                }


                $ControllerName = "";
                if (isset($a_controllername[$a_Row[37]])) {
                    $chkController = '';
                    $ControllerName = $a_controllername[$a_Row[37]];
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($a_Row[37]) . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                $VehicleType = "";
                if (array_key_exists(trim($a_Row[12]), $a_VehicleTypeId)) {
                    $chkVehicleType = '';
                    $VehicleType = $a_VehicleTypeId[$a_Row[12]];
                } else {
                    $error = true;
                    $chkVehicleType = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Veicolo  ' . $a_Row[1] . ' non trovato</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                if ($CountryCode != "Z000") {

                    if (array_key_exists($CountryCode, $a_chk_country)) {
                        $strCountryCode = $a_chk_country[$CountryCode];
                    } else {
                        $error = true;
                        $strCountryCode = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $CountryCode . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }
                }


                for ($i = 0; $i < count($a_Documentation); $i++) {
                    if (file_exists($path . $a_Documentation[$i])) {
                        $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    } else {
                        $error = true;
                        $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Immagine non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                        break;
                    }


                }

                $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND (REPLACE(VehiclePlate,'  ','') = '" . htmlentities($VehiclePlate) . "')");
                $FindNumber = mysqli_num_rows($fines);

                $chkFine = '';
                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_error';
                }


                $str_out .= '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-1' . $chkFine . '">(' . $Cont . ') ' . $chkFile . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $VehicleType . '</div>
            <div class="table_caption_H col-sm-2' . $chkFine . '">' . $VehiclePlate . '</div>
            <div class="table_caption_H col-sm-2' . $chkFine . '">' . $strCountry . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $FineDate . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $FineTime . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkController . $ControllerName . '</div>
            <div class="table_caption_H col-sm-2' . $chkFine . '">' . $CityAddress . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $str_Article . '</div>



            <div class="table_caption_H col-sm-1"></div>
            <div class="table_caption_H col-sm-1">' . $Code . '</div>
            
            <div class="table_caption_H col-sm-1">' . $VehicleModel . '</div>
            <div class="table_caption_H col-sm-2">' . $NameSurname . '</div>
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
            <form name="f_import" action="imp_etruria_exe.php">
            <input type="hidden" name="P" value="imp_etruria.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-12">
                <input type="submit" value="Importa" >
            </div>   
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
