<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");
include(INC."/header.php");
include(CLS . "/cls_message.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chk_Tolerance = 0;
$error = false;
$msgProblem = "";

$message=new CLS_MESSAGE();
$message->addWarning("<b>Avviso agli operatori</b> :<br>
La procedura corrente è stata aggiornata il 18 novembre 2021 ma non è stato possibile verificarla per mancanza di dati recenti.<br> Contattare l'assistenza per testarla in ambiente di collaudo prima di utilizzarla.");
echo $message->getMessagesString();

$a_VehicleTypeId = array(
    "1"=>1,
    "12"=>1,
    "Motoveicolo"=>2,
    "Motociclo"=>9,
    "non_definito"=>6,
    "3"=>8,
    "5"=>4,
    "Autoarticolato"=>12,
    "6"=>12,
    "A"=>1,
    "U"=>4,
);


$a_chk_country = array (
    "I" => "Italia",
    "RO" => "Romania",
    "F"=>"Francia",
    "D"=>"Germania",
    "SLO"=>"Slovenia",
    "CH"=>"Svizzera",
    "MC"=>"Principato di Monaco",
    "PL"=>"Polonia"
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

    $controllers = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_chk_controller = mysqli_fetch_all($controllers,MYSQLI_ASSOC);
    $file = fopen($path.$ImportFile,  "r");
    $delimiter = detectDelimiter($path . $ImportFile);
    $cont = 0;
    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.' '.date("Y-m-d H:i", filemtime($path.$ImportFile)).'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Targa</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-3">Luogo</div>
            <div class="table_label_H col-sm-2">Vel/Lim - Rilev </div>
            <div class="table_label_H col-sm-1">Lat</div>
            <div class="table_label_H col-sm-1">Long</div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>

            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && $row[0] != 'TARGA') {
                $cont++;
                /*

                0   TARGA           EB275XD
                1   CODMEZZO        005
                2   MARCA
                3   MODELLO
                4   NAZIONE         I
                5   STRADA1         SS 596 DEI CAIROLI
                6   STRADA2         DIREZIONE GARLASCO
                7   CIVICO
                8   DATAORA         201904121449
                9   IDSCOUT         0004042
                10  TARGAMEZZO      DZ579YE
                11  ACCERTATORE1    0 005
                12  ACCERTATORE2    0 007
                13  IDACCERTAMENTO  0004042-20190412-000003
                14  IMG1            0004042-20190412-000003-1
                15  IMG2            0004042-20190412-000003-2
                16  IMG3
                17  COD1
                18  COD2
                19  COD3
                20  CODACCERTATORE1 005
                21  CODACCERTATORE2 007
                22  ANNO2           19
                23  ANNO4           2019
                24  MESE            04
                25  GIORNO          12
                26  ORA             14
                27  MINUTI          49
                28  CODSTRADA1      112
                29  CODSTRADA2      194
                30  SPEEDTARGET     60
                31  SPEEDPATROL     -
                32  SPEEDSOGLIA     50
                33  SPEEDMODE       MC
                34  GPS             45,19894 N  8,910268 E
                35  GPSDIR          O



                */

                $DetectorCode = $row[9];
                $SpeedLimit = $row[32];
                $SpeedControl = $row[30];
                $FineTime = (strlen($row[26]) == 1) ? "0" . $row[26] : $row[26];
                $FineTime .= ":";
                $FineTime .= (strlen($row[27]) == 1) ? "0" . $row[27] : $row[27];


                $FineDate = (strlen($row[25]) == 1) ? "0" . $row[25] : $row[25];
                $FineDate .= "/";
                $FineDate .= (strlen($row[24]) == 1) ? "0" . $row[24] : $row[24];
                $FineDate .= "/" . $row[23];
                $VehicleTypeId = $a_VehicleTypeId[$row[1]];
                $VehiclePlate = strtoupper($row[0]);

                $a_Gps = explode("  ", trim($row[34]));
                $GpsLat = $a_Gps[0];
                $GpsLong = $a_Gps[1];

                $Address = $row[5] . " " . $row[6];

                $detector = getDetector($_SESSION['cityid'], $DetectorCode, $cont);
                if (gettype($detector) == 'string') {
                    $error = true;
                    $msgProblem .= $detector;
                } else {
                    $strDetector = $detector['Kind'];
                    $chk_Tolerance = $detector['Tolerance'];
                    $DetectorId = $detector['Id'];
                }
                $SpeedExcess = getSpeedExcess($SpeedControl, $SpeedLimit, $chk_Tolerance);
                if ($SpeedExcess <= 0) {
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


                $aFineDate = explode("/", $FineDate);

                $detector=getDetector($_SESSION['cityid'],$DetectorCode,$cont);
                $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                $ProtocolYear = $aFineDate[2];
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
                    $aTime = explode(":", $FineTime);
                    if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                        $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                        $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
                    }
                    $strArticle = $Fee . "/" . $MaxFee;
                }

                if (array_key_exists($row[4], $a_chk_country)) {
                    $strCountry = $a_chk_country[$row[4]];
                } else {
                    $error = true;
                    $strCountry = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $row[4] . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                $Img1 = trim($row[14]);
                $Img2 = trim($row[15]);

                if (file_exists($path . $Img1 . ".jpg")) {
                    $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Immagine/i non presente: ' . $Img1 . '</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }
                if (file_exists($path . $Img2 . ".jpg")) {
                    $chkFile .= '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkFile .= '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Immagine/i non presente: ' . $Img2 . '</div>
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

                $controller=getControllerFromArrayByField($a_chk_controller,array('Code'=>trim($row[20])),$FineDate);

                if ($controller!=null) {
                    $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    $controller2=getControllerFromArrayByField($a_chk_controller,array('Code'=>trim($row[21])),$FineDate);
                    if ($controller==null) {
                        $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:yellow"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Secondo Accertatore ' . trim($row[21]) . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }


                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Primo Accertatore ' . trim($row[20]) . ' non presente</div>
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


                $str_out .= '
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkController . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $VehiclePlate . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                <div class="table_caption_H col-sm-3' . $chkFine . '">' . $Address . '</div>
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . $SpeedControl . "/" . $SpeedLimit . "-" . $strDetector . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $GpsLat . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $GpsLong . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strArticle . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $strCountry . '</div>
                <div class="clean_row HSpace4"></div>
			</div>    
            ';
            }

        }
        fclose($file);
    }
    if(!$error){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_scout_exe.php">
            <input type="hidden" name="P" value="imp_scout.php">
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
/*

Garlasco: via L. da Vinci 70, via Pavia 79, via S. Biagio, via Tromello.
Alagna: SP 29 (campo sportivo Alagna) e cimitero Alagna.


*/