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
$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chk_Tolerance = 0;
$error = false;
$msgProblem = "";


$a_VehicleTypeId = array(
    "1"=>"Autoveicolo",
    "2"=>"Autoveicolo",
    "3"=>"Autocarro",
    "4"=>"Autocarro",
    "5"=>"Autobus",
    "6"=>"Motociclo",
    "8"=>"Rimorchio",
);



$a_Locality = array(
    "3"=>"B031",
    "8"=>"B524",
    "1"=>"B563",
    "5"=>"E684",
    "9"=>"F033",
    "11"=>"G688",
    "2"=>"H893",
    "4"=>"I207",
    "10"=>"L349",
    "6"=>"L934",
    "7"=>"L979",
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



    $countries = $rs->Select('Country', "PolcityCode!=''");
    $a_chk_country = array();
    while ($country = mysqli_fetch_array($countries)){
        $a_chk_country[$country['PolcityCode']]=$country['Title'];
    }

    $controllers = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_chk_controller = array();
    while ($controller = mysqli_fetch_array($controllers)){
        $a_chk_controller[$controller['Code']] = $controller['Name'];
    }


    $file = fopen($path.$ImportFile,  "r");
    $delimiter = detectDelimiter($path . $ImportFile);
    $cont = 0;


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
            <div class="table_label_H col-sm-2">Rilevatore</div>
            <div class="table_label_H col-sm-1">Velocità</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>

            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0])) {
                $cont++;
                $ViolationTypeId = "";
                $DetectorCode = $row[17];
                $SpeedLimit = $row[14];
                $SpeedControl = ceil(str_replace(",", ".", $row[15]));
                $FineTime = $row[4];
                $FineDate = $row[3];
                $VehicleTypeId = $a_VehicleTypeId[$row[8]];

                $VehiclePlate = strtoupper($row[9]);

                $str_Address = $row[5];
                $a_AddressLocality = explode(";", $str_Address);
                $a_AddressStreet = explode("->", $a_AddressLocality[1]);


                $str_Address = "al km " . $row[7] . " " . $a_AddressStreet[0] . " DIREZIONE " . $row[6];

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
                    $chk_Tolerance = $detector['Tolerance'];
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


                $aFineDate = explode("/", $FineDate);


                $FineDate = $aFineDate[2] . "-" . $aFineDate[1] . "-" . $aFineDate[0];
                $ProtocolYear = $aFineDate[2];

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


                    if ($row[8] == 3 || $row[8] == 5) {
                        $VehicleMass = 3.5;
                    } else if ($row[8] == 4 || $row[8] == 2) {
                        $VehicleMass = 12;
                    } else $VehicleMass = 0.00;


                    if ($VehicleMass >= MASS) {
                        $Fee = $Fee * FINE_MASS;
                        $MaxFee = $MaxFee * FINE_MASS;
                    }


                    $aTime = explode(":", $FineTime);

                    if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                        $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                        $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                    }

                    $strArticle = $Fee . "/" . $MaxFee;

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

                }

                if (isset($a_chk_country[$row[10]])) {
                    $strCountry = $a_chk_country[$row[10]];
                } else {
                    $error = true;
                    $strCountry = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-warning">Nazione ' . $row[10] . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }


                if (file_exists($path . $row[1] . "/Thumbs.db")) {
                    unlink($path . $row[1] . "/Thumbs.db");
                }

                if (file_exists($path . $row[1] . "/scheda.jpg")) {
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


                $fines = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . strtoupper($row[9]) . "'");
                $FindNumber = mysqli_num_rows($fines);

                $chkFine = '';
                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_error';
                }

                $row[18] = '09';
                if (isset($a_chk_controller[$row[18]])) {
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


                $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='" . $_SESSION['cityid'] . "' AND VehiclePlate='" . strtoupper($row[9]) . "'");

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
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . $a_Locality[$a_AddressLocality[0]] . ' ' . $str_Address . '</div>
                <div class="table_caption_H col-sm-2' . $chkFine . '">' . $strDetector . '</div>
                <div class="table_caption_H col-sm-1' . $chkFine . '">' . $SpeedControl . '</div>
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
    if(!$error){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_polcity_exe.php">
            <input type="hidden" name="P" value="imp_polcity.php">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
            <div class="table_label_H col-sm-12">
                Comprimi immagini
                <select name="compress">
                    <option value="0" SELECTED>NO</option>
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
