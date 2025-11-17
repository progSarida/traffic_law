<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(CLS . "/cls_message.php");

include(INC . "/function.php");
include(INC . "/function_import.php");

include(INC . "/header.php");
require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER . "/_VIOLATION_/" . $_SESSION['cityid'] . "/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;
$error = false;
$msgProblem = "";
$message=new CLS_MESSAGE();
$message->addWarning("<b>Avviso agli operatori</b> :<br>
La procedura corrente è stata aggiornata il 13 ottobre 2021 ma non è stato possibile verificarla per mancanza di dati recenti.<br> Contattare l'assistenza per testarla in ambiente di collaudo prima di utilizzarla.");
echo $message->getMessagesString();
$TimeTLightFirst = 0;
$TimeTLightSecond = 0;
$b_TrafficLight = false;

$str_TLImageFirst = "";
$str_TLImageSecond = "";

$a_FileLetter = array("_A.jpg","_B.jpg","_F.jpg");


$a_chk_VehicleType = array(
    "1"=>1,
    "14"=>2,
    "10"=>4,
    "2"=>3,
    "5"=>4,
    "12"=>5

);


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


    $controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");

    $a_chk_controller = mysqli_fetch_all($controllers,MYSQLI_ASSOC);

    $file = fopen($path . $ImportFile, "r");
    $delimiter = detectDelimiter($path . $ImportFile);
    $cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-2">Data</div>
            <div class="table_label_H col-sm-1">Località</div>
            
            <div class="table_label_H col-sm-4">Luogo</div>
            <div class="table_label_H col-sm-1">Accertatore</div>
            
            <div class="table_label_H col-sm-2">Velocità/Fotogrammi</div>
            <div class="table_label_H col-sm-1">Targa</div>    
            <div class="table_label_H col-sm-1">Sanzione</div>            


            <div class="clean_row HSpace4"></div>	
        </div>
        ';

    if(is_resource($file)) {

        while (!feof($file)) {

            $row = fgetcsv($file, 1000, $delimiter);

            if (isset($row[0]) && $row[0] != 'Ticket') {
                $ViolationTypeId = "";
                $n_DetectorTypeId = 0;

                $chk_Detector = false;
                $chk_Speed = true;

                $cont++;
                /*

                0   Ticket
                1   NumADI
                2   ChkSum
                3   Rete
                4   IdVarco
                5   Varco
                6   DataAccertamento
                7   OraAccertamento
                8   Targa
                9   Telepass
                10  CodTipoTarga
                11  CodTipoVeicolo
                12  Toponomastica
                13  CodicePrimaVia
                14  CodiceD
                15  CodiceSecondaVia
                16  ZonaCPM
                17  CodInfrazione1
                18  CodMancataContestazione
                19  MarcaModello
                20  Colore
                21  DataVisione
                22  OraVisione
                23  MatrVerbContr
                24  MatrVisVerif
                25  MatrVerbVerif
                26  NomeVerbContr
                27  NomeVisVerif
                28  NomeVerbVerif
                29  Corsia
                30  Vlimite
                31  Vrilevata
                32  Veffettiva
                33  SecondiRosso
                34  CodInfrazione2
                35  CodInfrazione3
                36  Matricola
                37  idTransito

                */


                $DetectorCode = trim($row[4]);


                $rs_detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code='" . $DetectorCode . "'");

                $n_rsNumber = mysqli_num_rows($rs_detector);

                if ($n_rsNumber == 0) {
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
                    $r_detector = mysqli_fetch_array($rs_detector);
                    $strDetector = $r_detector['Kind'];
                    $chkTolerance = $r_detector['Tolerance'];
                    $n_DetectorTypeId = $r_detector['DetectorTypeId'];
                }


                $FineDate = trim($row[6]);
                $FineTime = trim($row[7]);

                $Address = trim($row[5]);

                $Locality = trim($row[3]);


                $ControllerName = trim($row[23]);
                $VehiclePlate = trim($row[8]);


                $aFineDate = explode("/", $FineDate);

                $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                $ProtocolYear = $aFineDate[2];

                $VehicleTypeId = trim($row[11]);


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


                if ($n_DetectorTypeId == 2) {

                    $b_TrafficLight = true;


                    $TimeTLightFirst = trim($row[33]);
                    $TimeTLightSecond = trim($row[33]);


                    $Where = "Article=146 AND Paragraph=3 AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


                    $finds = $rs->Select('V_Article', $Where);
                    $FindNumber = mysqli_num_rows($finds);

                    if ($FindNumber == 0) {
                        $error = true;
                        $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        echo '
                <div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-danger">Articolo non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                        die;
                    } else {
                        $find = mysqli_fetch_array($finds);
                    }


                    $ArticleId = $find['Id'];
                    $Fee = $find['Fee'];
                    $MaxFee = $find['MaxFee'];
                    $ViolationTypeId = $find['ViolationTypeId'];


                    $strArticle = $Fee . "/" . $MaxFee;


                } else {
                    $detector=getDetector($_SESSION['citryid'],$DetectorCode,$cont);
                    $SpeedLimit = trim($row[30]);
                    $SpeedControl = trim($row[31]);
                    $chkTolerance = ($chkTolerance > FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;
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

                    if (!$chk_Speed) {
                        $msgProblem .= '
                                    <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                                        <div class="table_caption_H col-sm-11 alert-warning">Velocità ' . $SpeedControl . ' inferiore al limite ' . $SpeedLimit . ' - immagine non verrà importata</div>
                                        <div class="clean_row HSpace4"></div>
                                    </div>    
                                    ';
                    }


                }


                for ($i = 0; $i < count($a_FileLetter); $i++) {

                    if (file_exists($path . trim($row[1]) . $a_FileLetter[$i])) {
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


                $Address = utf8_encode($Address);

                $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . strtoupper($VehiclePlate) . "'");
                $FindNumber = mysqli_num_rows($fines);

                $chkFine = '';
                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_error';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Verbale già presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }
                $controller=getControllerFromArrayByField($a_chk_controller,array('Name'=>trim($ControllerName)),$FineDate);
                if ($controller !=null) {
                    $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . trim($ControllerName) . ' non presente</div>
                            <div class="clean_row HSpace4"></div>
                        </div>    
                        ';
                }

                if ($VehiclePlate == "") {
                    $error = true;
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Targa non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';


                }
                $VehiclePlate = (trim(strtoupper($VehiclePlate) == '-')) ? "XXX" : strtoupper($VehiclePlate);

                $rs_Localities = $rs->Select(MAIN_DB . '.City', "Title='" . $Locality . "'");
                $FindNumber = mysqli_num_rows($rs_Localities);
                $str_ChkLocality = "";
                if ($FindNumber == 0) {
                    $error = true;
                    $str_ChkLocality = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Localita ' . trim($Locality) . ' non trovata</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                } else {
                    $rs_Locality = mysqli_fetch_array($rs_Localities);
                    $Locality = $rs_Locality['Id'];
                }
                getDetector($_SESSION['cityid'],$DetectorCode,$cont);
                $rs_Reasons=getReasonRs($detector['ReasonId'],$ViolationTypeId,$DetectorCode);
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

                if ($b_TrafficLight) {
                    $str_ArticleOut = $TimeTLightFirst . '/' . $TimeTLightSecond;
                    $TimeTLightFirst = 0;
                    $TimeTLightSecond = 0;
                    $b_TrafficLight = false;

                    $str_TLImageFirst = "";
                    $str_TLImageSecond = "";
                } else {
                    $str_ArticleOut = $SpeedControl . '/' . $SpeedLimit;
                }


                $str_out .= '
                <div class="col-sm-12"> 
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkController . ' ' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $str_ChkLocality . ' ' . $Locality . '</div>
                    <div class="table_caption_H col-sm-4' . $chkFine . '">' . $Address . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . trim($ControllerName) . '</div>
                    
    
                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $str_ArticleOut . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $VehiclePlate . '</div>
                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strArticle . '</div>
    
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
            <form name="f_import" action="imp_project_exe.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-4">
                Comprimi immagini
                <select name="compress">
                    <option value="0">NO</option>
                    <option value="1">SI</option>    
                </select>
            </div>
            <div class="table_label_H col-sm-4">
                Italiane
                <select name="country">
                    <option value="1">SI</option>
                    <option value="0">NO</option>    
                </select>
            </div>
            <div class="table_label_H col-sm-4">
                <input type="submit" value="Importa" >  
            </div>
                                         
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
