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
        if (strtolower($aFile[count($aFile) - 1]) == "txt" && strpos(strtolower($file), "errori") === false) {
            $Cont++;
            $FileList .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">' . $Cont . '</div>
            <div class="table_caption_H col-sm-10">' . $file . '</div>
            <div class="table_caption_button col-sm-1">
                ' . ChkButton($aUserButton, 'imp', '<a href="imp_megasp.php?1&ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>') . '
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


    $controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
    $a_chk_controller = array();
    while ($controller = mysqli_fetch_array($controllers)) {
        $a_chk_controller[] = $controller['MegaspCode'];
        $a_controllername[$controller['MegaspCode']] = $controller['Name'];
        $a_controllerid[$controller['MegaspCode']] = $controller['Id'];

    }


    $CityAddresses = $rs->Select('CityAddress', "CityId='" . $_SESSION['cityid'] . "'");
    $a_chk_address = array();
    while ($CityAddress = mysqli_fetch_array($CityAddresses)) {
        $a_chk_address[] = $CityAddress['MegaspCode'];
        $a_addressid[$CityAddress['MegaspCode']] = $CityAddress['StreetType'] . " " . $CityAddress['StreetTitle'];

    }


    $file = fopen($path . $ImportFile, "r");


    $a_File = explode("_", $ImportFile);

    $f_AddFile = fopen($path . $a_File[0], "r");


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

    $row = fgets($file);
    $row = fgets($f_AddFile);



    if(is_resource($file)) {
        while (!feof($file)) {

            $row = fgets($file);

            $row_add = fgets($f_AddFile);


            $a_Row = explode($delimiter, $row);
            if (isset($a_Row[1])) {

                $Cont++;
                $strCountry = "";
                $strCountryCode = "";
                $str_FixedWhere = "Fixed IS NULL";

                $str_Folder = trim($a_Row[0]);
                $Code = trim($a_Row[5]);
                $VehiclePlate = trim($a_Row[3]);


                $FineDate = trim($a_Row[6]);
                $FineTime = $a_Row[7];
                $Id1Megasp = trim($a_Row[8]);
                $Id2Megasp = trim($a_Row[9]);
                $AddressId = trim($a_Row[10]);


                $DetectorCode = trim($a_Row[20]);
                if ($DetectorCode != "") {
                    $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND IdMegasp=" . $DetectorCode);
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
                    }
                } else {
                    $DetectorCode = 0;

                }


                if ($Id1Megasp == 7 and $Id2Megasp == 383) {
                    $Id1Megasp = 7;
                    $Id2Megasp = 100;
                }
                if ($Id1Megasp == 7 and $Id2Megasp == 276) {
                    $Id1Megasp = 7;
                    $Id2Megasp = 160;
                }
                if ($Id1Megasp == 7 and $Id2Megasp == 273) {
                    $Id1Megasp = 7;
                    $Id2Megasp = 160;
                }
                if ($Id1Megasp == 7 and $Id2Megasp == 80) {
                    $Id1Megasp = 7;
                    $Id2Megasp = 33;
                }
                if ($Id1Megasp == 7 and $Id2Megasp == 360) {
                    $Id1Megasp = 7;
                    $Id2Megasp = 78;
                }


                if ($Id1Megasp == 146 and $Id2Megasp == 60) {
                    $Id1Megasp = 146;
                    $Id2Megasp = 66;
                }


                if ($Id1Megasp == 157 and $Id2Megasp == 303) {
                    $Id1Megasp = 157;
                    $Id2Megasp = 306;
                }
                if ($Id1Megasp == 157 and $Id2Megasp == 146) {
                    $Id1Megasp = 157;
                    $Id2Megasp = 306;
                }


                if ($Id1Megasp == 158 and $Id2Megasp == 200) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 8;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 5) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 4;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 6) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 174;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 135) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 406;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 281) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 465;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 336) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 133;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 314) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 292;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 145) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 174;
                }
                if ($Id1Megasp == 158 and $Id2Megasp == 34) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 4;
                }

                if ($Id1Megasp == 158 and $Id2Megasp == 270) {
                    $Id1Megasp = 158;
                    $Id2Megasp = 3;
                }


                $ChkCode = trim(substr($row_add, 43, 9));
                $ChkFineDate = substr($row_add, 265, 10);
                $ChkVehiclePlate = trim(substr($row_add, 275, 10));


                if ($str_Folder != $ChkCode) {
                    $error = true;

                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Cartelle non coincidenti</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }

                if ($FineDate != $ChkFineDate) {
                    $error = true;

                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Date non coincidenti</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }

                if ($VehiclePlate != $ChkVehiclePlate) {
                    $error = true;

                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Targhe non coincidenti</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';


                }
                $VehiclePlate = str_replace("-", "", $VehiclePlate);
                $VehiclePlate = str_replace(",", "", $VehiclePlate);


                $NameSurname = trim(substr($row_add, 83, 44));
                $Address = trim(substr($row_add, 128, 45));
                $ZIPCity = str_replace("00000", "", trim(substr($row_add, 173, 9)));
                $Country = trim(substr($row_add, 183, 40));
                $CountryCode = trim(substr($row_add, 3317, 3));


                $Fee = trim(substr($row_add, 229, 7));
                $PartialFee = trim(substr($row_add, 3424, 7));
                $VehicleModel = trim(substr($row_add, 3217, 40));


                if ($VehicleModel = "00") $VehicleModel = "";


                $str_Article = "";
                $chkArticle = "";
                $articles = $rs->Select('Article', "Id1Megasp=" . $Id1Megasp . " AND Id2Megasp=" . $Id2Megasp);
                if (mysqli_num_rows($articles) == 0) {
                    $error = true;
                    $chkArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo con cod megasp ' . $Id1Megasp . ' / ' . $Id2Megasp . ' non trovato</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                } else if (mysqli_num_rows($articles) > 1) {
                    $error = true;
                    $chkArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Trovati più articoli con cod megasp ' . $Id1Megasp . ' / ' . $Id2Megasp . '</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                } else {
                    $article = mysqli_fetch_array($articles);
                    $chkArticle = '';
                    //$str_Article = $article['Article'] . " " . $article['Paragraph'] . " " . $article['Letter'];
                    $ArticleId = $article['Id'];
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
                                <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione per Articolo con cod megasp ' . $Id1Megasp . ' / ' . $Id2Megasp . ' non presente:' . $str_WhereReason . '</div>
                                <div class="clean_row HSpace4"></div>
                            </div>    
                            ';

                    }


                    $a_Year = explode("/", $FineDate);
                    $Year = $a_Year[2];

                    $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $ArticleId . " AND Year=" . $Year);

                    if (mysqli_num_rows($rs_ArticleTariff) == 0) {
                        $error = true;
                        $chkArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo (id ' . $ArticleId . ') ' . $article['Article'] . " " . $article['Paragraph'] . " " . $article['Letter'] . ' anno ' . $Year . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    } else {
                        $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
                        $str_Article = $r_ArticleTariff['Fee'];
                    }
                }


                $ControllerName = "";
                if (in_array(trim($a_Row[11]), $a_chk_controller)) {
                    $chkController = '';
                    $ControllerName = $a_controllername[$a_Row[11]];
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($a_Row[11]) . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }

                $CityAddress = "";


                if (in_array($AddressId, $a_chk_address)) {
                    $chkAddress = '';
                    $CityAddress = $a_addressid[$AddressId];
                } else {
                    $error = true;
                    $chkAddress = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Via non trovata cod megasp ' . $AddressId . '</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }

                $VehicleType = "";
                if (array_key_exists(trim($a_Row[1]), $a_VehicleTypeId)) {
                    $chkVehicleType = '';
                    $VehicleType = $a_VehicleTypeId[$a_Row[1]];
                } else {
                    $error = true;
                    $chkVehicleType = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $str_Folder . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Veicolo megasp ' . $a_Row[1] . ' non trovato</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                if ($CountryCode != "") {

                    if (array_key_exists($CountryCode, $a_chk_country_code)) {
                        $strCountryCode = $a_chk_country_code[$CountryCode];
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

                if (array_key_exists($a_Row[4], $a_chk_country)) {
                    $strCountry = $a_chk_country[$a_Row[4]];
                } else {
                    $error = true;
                    $strCountry = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-warning">' . $str_Folder . '</div>
                    <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $a_Row[4] . ' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
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


                if ($CountryCode == "") {
                    $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . DateInDB($FineDate) . "' AND FineTime='" . $FineTime . "' AND (REPLACE(VehiclePlate,'  ','') = '" . htmlentities($VehiclePlate) . "')");
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
                }


                $str_out .= '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">(' . $Cont . ') ' . $str_CheckFolder . $str_Folder . '</div>
            <div class="table_caption_H col-sm-1">' . $VehicleType . '</div>
            <div class="table_caption_H col-sm-2">' . $VehiclePlate . '</div>
            <div class="table_caption_H col-sm-2">' . $strCountry . '</div>
            <div class="table_caption_H col-sm-1">' . $FineDate . '</div>
            <div class="table_caption_H col-sm-1">' . $FineTime . '</div>
            <div class="table_caption_H col-sm-1">' . $chkController . $ControllerName . '</div>
            <div class="table_caption_H col-sm-2">' . $chkAddress . $CityAddress . '</div>
            <div class="table_caption_H col-sm-1">' . $chkArticle . $str_Article . '</div>



            <div class="table_caption_H col-sm-1">' . $ChkCode . '</div>
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
    fclose($f_AddFile);
    if (!$error) {
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_megasp_exe.php">
            <input type="hidden" name="P" value="imp_megasp.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-12">
                <input type="submit" value="Importa" > 
                AIRE:
                 <select name="AIRE">
                    <option value="0">NO</option>
                    <option value="1">SI</option>
                 </select>                          
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
