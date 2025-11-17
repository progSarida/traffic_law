<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/function_import.php");
include(INC . "/header.php");
require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

ini_set('max_execution_time', 3000);


$Cont = 0;
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$chk_Tolerance = 0;
$error = false;

$str_Problem = '';
$str_Detector = '';
$str_Article = '';

$str_Country = "Z000";

$str_FileXMLList = "";
if ($directory_handle = opendir($path)) {

    $str_FileXMLList = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-1">Accertatore</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-3">Luogo</div>
            <div class="table_label_H col-sm-2">Rilevatore</div>
            <div class="table_label_H col-sm-1">Velocità</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>

            <div class="clean_row HSpace4"></div>	
        </div>
        ';


    $rs_controller = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
    $a_Controllers = controllersByCodeArray($rs_controller);
    //$rs_controller = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "'");
mysqli_data_seek($rs_controller,0);
    $a_ControllersByName = controllersByFieldArray($rs_controller,'Name');

    $rs_detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "'");
    $a_chk_detector = array();
    $a_Tolerance = array();
    $a_Detector = array();
    $a_Fixed = array();

    $cont = 0;



    while ($r_detector = mysqli_fetch_array($rs_detector)) {
        $a_chk_detector[$r_detector['Code']] = $r_detector['TitleIta'];
        $a_Tolerance[$r_detector['Code']] = $r_detector['Tolerance'];
        $a_Detector[$r_detector['Code']] = $r_detector['Id'];
        $a_Fixed[$r_detector['Code']] = $r_detector['Fixed'];

    }


    $aVehicleTypeId = array(
        "C" => 1,
        "T" => 2,
    );






    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".", "$file");
        if (strtolower($aFile[count($aFile) - 1]) == "xml") {

            $cont++;


            $obj_Dom = new DOMDocument();
            $obj_Dom->load( $path . $file );

            $a_Fine = array();

            showDOMNode($obj_Dom);

            $chk_Speed = true;

            $ControllerCode = $a_Fine['Operator1'];
            $RoadSideDistance = $a_Fine['RoadSideDistance'];

//$ControllerCode = 2;

            $DetectorCode = $a_Fine['InstrumentId'];
            $SpeedLimit = $a_Fine['RoadSpeedLimit'];
            $SpeedControl = $a_Fine['Speed'];
            $FineTime = $a_Fine['ShotTimeVB'];
            $FineDate = $a_Fine['ShotDate'];
            $Locality = $a_Fine['Location'];
            $Address = $a_Fine['Rsv2'];



            $a_TmpAddress = explode("DirectionDescription:",$Address);
            $a_Address = explode("|",$a_TmpAddress[1]);
            $Address = $a_Address[0];

            $VehiclePlate = "";
            $VehicleTypeId = $aVehicleTypeId[$a_Fine['VehicleType']];

            $ProtocolYear = substr($FineDate, 0, 4);
                if ($DetectorCode != "") {
                    if (isset($a_chk_detector[$DetectorCode])) {

                        $str_Detector = $a_chk_detector[$DetectorCode];
                        $str_FixedWhere = "Fixed=" . $a_Fixed[$DetectorCode];

                        if ($SpeedLimit>0) {
                            $chk_Tolerance = $a_Tolerance[$DetectorCode];
                            $SpeedExcess=getSpeedExcess($SpeedControl,$SpeedLimit,$chk_Tolerance);
                            if($SpeedExcess<=0) $chk_Speed = false;
                            $detector=getDetector($_SESSION['cityid'],$DetectorCode);
                            if(gettype($detector)=="string"){
                                $error = true;
                                $str_Problem .= $detector;
                            }else{
                                $DetectorId = $detector['Id'];
                                $ReasonId = $detector['ReasonId'];
                                if($ReasonId==0){
                                    $error = true;
                                    $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                    $str_Problem .= '
                                    <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-1 alert-danger"></div>
                                        <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione legata al rilevatore non presente</div>
                                        <div class="clean_row HSpace4"></div>
                                    </div>
                                    ';
                                }
                            }
                            $find = getVArticle($detector['Id'],$_SESSION['cityid'],$SpeedExcess,$ProtocolYear);
                            $ViolationTypeId = 0;
                            if ($find == null) {
                                $ViolationTypeId = 0;
                                $error = true;
                                $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                $str_Problem .= '
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


                                $str_Article = $Fee . "/" . $MaxFee;
                            }
                        }

                    } else {
                        $str_FixedWhere = "Fixed IS NULL";
                        $error = true;
                        $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod ' . $DetectorCode . ' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                    }
                    if(!$chk_Speed){
                        $str_Problem .= '
                                    <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-1 alert-warning">'.$cont . '</div>
                                        <div class="table_caption_H col-sm-11 alert-warning">Velocità '. $SpeedControl . ' inferiore al limite '. $SpeedLimit .' - immagine non verrà importata</div>
                                        <div class="clean_row HSpace4"></div>
                                    </div>    
                                    ';
                    }


                }

                $str_Where = $str_FixedWhere . " AND ReasonTypeId=1 AND CityId='" . $_SESSION['cityid'] . "'";
                switch ($ViolationTypeId) {
                    case 4:
                    case 6:
                        $str_Where .= ($DetectorCode == "") ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" . $ViolationTypeId;
                        break;

                    default:
                        $str_Where .= " AND ViolationTypeId=" . $ViolationTypeId;
                }

            $chkFile = "";


            $Documentation = $aFile[0].".jpg";

            if (file_exists($path . $Documentation)) {
                $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
            } else {
                $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $str_Problem .= '
                     <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">
                                Immagine non presente: targa ' . $VehiclePlate . ' - ' . $Documentation . '
                            </div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                    }

            $fines = $rs->ExecuteQuery("select * from Fine f join FineDocumentation fd on fd.FineId=f.Id where fd.Documentation='$Documentation' and f.CityId='" . $_SESSION['cityid'] . "' AND f.FineDate='$FineDate' ");
                $FindNumber = mysqli_num_rows($fines);

                $chkFine = '';
                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_error';
                }
                if (!is_null(getControllerByCode($a_Controllers, $FineDate, $ControllerCode)) || !is_null(getControllerByField($a_ControllersByName, $FineDate, $ControllerCode))) {
                    $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $error = true;
                    $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Accertatore ' . $ControllerCode . ' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                }


                $str_FileXMLList .= '
        <div class="col-sm-12"> 
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . ' ' . $chkController . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $ControllerCode . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
            <div class="table_caption_H col-sm-3' . $chkFine . '">' . "(".$Locality.") ".$Address . '</div>
            <div class="table_caption_H col-sm-2' . $chkFine . '">' . $str_Detector . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $SpeedControl . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $SpeedLimit . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $str_Article . '</div>
            <div class="table_caption_H col-sm-1' . $chkFine . '">' . $str_Country . '</div>
            <div class="clean_row HSpace4"></div>
        </div>    
        ';


        }
    }

    closedir($directory_handle);
}

$str_out .= $str_FileXMLList;
if (!$error) {
    $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_106_exe.php">
            <input type="hidden" name="P" value="imp_106.php">
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


echo $str_out;


if (strlen($str_Problem) > 0) {
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $str_Problem;

}
