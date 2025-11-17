<?php

include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");
ini_set("display_errors",0);
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


$a_Address = array(
    "A"=> "S.S. ex S.P. 432 Km 9+405 dir. Romito Magra",
    "B"=> "S.S. ex S.P. 432 Km 9+405 dir. Senato di Lerici"
);




/*
2144_2_A_D20200101_H002236_M523.pdf
01/01/2020
00:22
50,00
69,00
5,00
Autoveicolo
FX035LE
0|||1|2144|davide14||03/03/2020|16:24|0|0


*/
$a_VehicleTypeId = array(
    "Autoveicolo"=>1,
    "Motoveicolo"=>2,
    "Ciclomotore"=>9,
    "non_definito"=>6,
    "Autobus"=>8,
    "Autocarro"=>4,
    "Autoarticolato"=>12,
);






$rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_Controllers = controllersByFieldArray($rs_Controller,'IspeedCode');




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

            
            
    $file = fopen($path.$ImportFile,  "r");
    $delimiter = "|";
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
            <div class="table_label_H col-sm-1">Velocit�</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Sanzione</div>            
            <div class="table_label_H col-sm-1">Nazione</div>

            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    if(is_resource($file)) {
    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);
        if(isset($row[0])){
            $cont++;


            $a_RowData = explode("_",$row[0]);




            $DetectorCode = $row[11]; //$a_RowData[1]; //forse prima leggeva il dato dal nome file
            $str_Address = $a_Address[$a_RowData[2]];
                $r_Detector=getDetector($_SESSION['cityid'], $DetectorCode,$cont);
                $VehiclePlate = strtoupper($row[7]);
                $FineTime = $row[2];
                $VehicleTypeId = $a_VehicleTypeId[$row[6]];
                $FineDate = $row[1];

                $aFineDate = explode("/",$FineDate);
                $FineDate = $aFineDate[2]."-".$aFineDate[1]."-".$aFineDate[0];
                $ProtocolYear = $aFineDate[2];
                $SpeedControl = $row[4];

                if($r_Detector==null){
                $error = true;
                $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod '. $DetectorCode .' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
            }else{

                $strDetector = $r_Detector['Kind'];
                $DetectorId=$r_Detector['Id'];
                $ReasonId=$r_Detector['ReasonId'];
                $chk_Tolerance = $r_Detector['Tolerance'];
                $SpeedLimit = $row[3];
                $str_Locality = $_SESSION['cityid'];


                $chk_Tolerance = ($chk_Tolerance>FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;
                    $SpeedExcess=getSpeedExcess(str_replace(",",".",$SpeedControl),str_replace(",",".",$SpeedLimit),$chk_Tolerance);

                if($SpeedExcess<=0){
                $Speed=$SpeedLimit+$SpeedExcess;
                    $error = true;
                    $SpeedLimit .= '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Limite: '. $SpeedLimit .' - Velocit�:'.$Speed.'</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                }

                if (file_exists($path.$row[0])) {
                    $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                }else{
                    $error = true;
                    $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Immagine non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                }

                    $find= getVArticle($r_Detector['Id'],$_SESSION['cityid'],$SpeedExcess,$ProtocolYear);
                $ViolationTypeId = null;
                
                if ($find == null) {
                    $error = true;
                    $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Articolo con velocit� '. $SpeedControl . ' / '. $SpeedLimit .' anno '.$ProtocolYear.' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';

                } else {
                    $ArticleId = $find['Id'];
                    $Fee = $find['Fee'];
                    $MaxFee = $find['MaxFee'];
                    $ViolationTypeId = $find['ViolationTypeId'];
                    $AdditionalNight = $find['AdditionalNight'];


                    if($AdditionalNight){
                        $aTime = explode(":",$FineTime);

                        if($aTime[0]<FINE_HOUR_START_DAY || ($aTime[0]>FINE_HOUR_END_DAY && $aTime[0]!="00")){
                            $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                            $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);

                        }

                    };


                    $strArticle = $Fee."/".$MaxFee;
                }
                $s_violation_where = $ViolationTypeId!= null ? "AND ViolationTypeId=".$ViolationTypeId : "";
                    $rs_Reasons = getReasonRs($ReasonId,$_SESSION['cityid'],$ViolationTypeId,$DetectorCode);
                $n_rsNumber = mysqli_num_rows($rs_Reasons);

                if($n_rsNumber==0){
                    $error = true;
                    $str_ChkLocality = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Contestazione non presente per quest\'articolo </div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                }else{
                    $rs_Reason = mysqli_fetch_array($rs_Reasons);
                    $ReasonId = $rs_Reason['Id'];
                }

                $Country = "Z000";
                $strCountry = "Italia";

                $fines = $rs->Select('Fine', "CityId='".$_SESSION['cityid']."' AND FineDate='".$FineDate."' AND FineTime='".$FineTime."' AND VehiclePlate='".$VehiclePlate."'");
                $FindNumber = mysqli_num_rows($fines);

                $chkFine = '';
                if($FindNumber>0){
                    $chkFine = ' table_caption_error';
                }
            }




            $str_Controller = "";
                if (!is_null(getControllerByCode($a_Controllers, $FineDate, trim($row[13])))) {
                $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    $str_Controller = getControllerByField($a_Controllers,$FineDate,trim($row[13]))['Name'];
            }else{
                $error = true;
                $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Accertatore '.trim($row[13]).' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
            }


            $ControllerDate = $row[15];
            $ControllerTime = $row[16];




            $str_out .='
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1'.$chkFine.'">'.$chkFile . ' ' .$cont . ' '.$chkController.'</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'.$VehiclePlate . '</div>
                <div class="table_caption_H col-sm-2'.$chkFine.'">'.DateOutDB($FineDate) . ' '.$FineTime .'</div>
                <div class="table_caption_H col-sm-2'.$chkFine.'">'.$str_Locality.$str_Address . '</div>
                <div class="table_caption_H col-sm-2'.$chkFine.'">'.$strDetector . ' / '.$str_Controller.'</div>
                <div class="table_cption_H col-sm-1'.$chkFine.'">'.$SpeedControl . '</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'.$SpeedLimit . '</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'.$strArticle . '</div>
                <div class="table_caption_H col-sm-1'.$chkFine.'">'.$strCountry . '</div>
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
            <form name="f_import" action="imp_ispeed_exe.php">
            <input type="hidden" name="P" value="imp_ispeed.php">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
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
