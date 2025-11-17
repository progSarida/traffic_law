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

$error = false;
$msgProblem = "";


$rs= new CLS_DB();

$str_Flow = " AND (
Documentation='Flusso_27_Verb_Ita_U480_2018-06-13_13-26-17_900.zip' OR
Documentation='Flusso_28_Verb_Ita_U480_2018-06-13_13-32-45_900.zip' OR
Documentation='Flusso_29_Verb_Ita_U480_2018-06-13_13-37-44_900.zip' OR
Documentation='Flusso_30_Verb_Ita_U480_2018-06-13_13-42-00_900.zip' OR
Documentation='Flusso_31_Verb_Ita_U480_2018-06-13_13-48-22_753.zip'
)";




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
                <a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>
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
				<div class="table_label_H col-sm-12">ELENCO FILE</div>
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



    $rs_Result = $rs->Select('Result', "Disabled=0");
    $a_chk_Result = array();
    while ($r_Result = mysqli_fetch_array($rs_Result)){
        $a_chk_Result[] = $r_Result['Description'];
        $a_Result[$r_Result['Description']] = $r_Result['Id'];
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
            <div class="table_label_H col-sm-1">Cron</div>
            <div class="table_label_H col-sm-1">Data not</div>
            <div class="table_label_H col-sm-2">Raccomandata</div>
            <div class="table_label_H col-sm-2">Ricevuta</div>
            <div class="table_label_H col-sm-2">Tipo Notifica</div>
            <div class="table_label_H col-sm-1">Data sped</div>
            <div class="table_label_H col-sm-1">Posizione</div>
            <div class="table_label_H col-sm-1">Data log</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';


    $n_NotificationRow = 0;
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && $row[0] != "cod_comune") {
                $cont++;

                $CityId = $row[0];

                $a_Row = explode("/", $row[1]);
                $ProtocolId = $a_Row[0];
                $ProtocolYear = $a_Row[1];

                $NotificationDate = $row[5];
                $LetterNumber = $row[13];
                $ReceiptNumber = $row[6];

                $NotificationType = trim($row[7]);
                $a_Notification = explode("-", $NotificationType);
                if (isset($a_Notification[1])) $NotificationType = trim($a_Notification[0]) . " - " . trim($a_Notification[1]);


                $NotificationStatus = trim($row[8]);
                $a_Notification = explode("-", $NotificationStatus);
                if (isset($a_Notification[1])) $NotificationStatus = trim($a_Notification[0]) . " - " . trim($a_Notification[1]);

                $ImgBack = $row[11];
                $ImgFront = $row[10];

                $SendDate = $row[14];
                $Box = $row[15];
                $Lot = $row[16];
                $Position = $row[17];

                $a_LogDate = explode(" ", $row[12]);
                $LogDate = $a_LogDate[0];


                $chkFine = "";
                $chkSendDate = "";
                $chkDeliveryDate = "";
                $chkNotification = "";

                $chkImgBack = (file_exists($path . "/" . $ImgBack)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";

                $chkImgFront = (file_exists($path . "/" . $ImgFront)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";


                $rs_Fine = $rs->Select('Fine', "CountryId='Z000' AND CityId='" . $CityId . "' AND ProtocolId=" . $ProtocolId . " AND ProtocolYear=" . $ProtocolYear . " AND Id IN( SELECT FineId FROM FineHistory WHERE NotificationTypeId=6" . $str_Flow . ")");


                if (mysqli_num_rows($rs_Fine) == 0) {
                    $error = true;
                    $chkFine = ' alert-danger ';
                    $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Verbale non trovato</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                } else {
                    for ($i = 1; $i <= mysqli_num_rows($rs_Fine); $i++) {
                        $r_Fine = mysqli_fetch_array($rs_Fine);

                        $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $r_Fine['Id'] . " AND NotificationTypeId=6" . $str_Flow);

                        if (mysqli_num_rows($rs_FineHistory) == 0) {
                            $msgProblem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-warning">' . $cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-warning">Verbale in history non trovato</div>
                            <div class="clean_row HSpace4"></div>
                        </div>';


                        } else {

                            $rs_FineNotification = $rs->Select('FineNotification', "FineId=" . $r_Fine['Id']);
                            if (mysqli_num_rows($rs_FineNotification) == 0) {

                                $chkImgBack = (file_exists($path . "/" . $ImgBack)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";

                                $chkImgFront = (file_exists($path . "/" . $ImgFront)) ? "<span class='glyphicon glyphicon-ok-circle' style='color: green;'></span> " : "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span> ";


                                if (in_array(trim($NotificationStatus), $a_chk_Result)) {

                                    $NotificationType = $a_Result[$NotificationStatus] . '/' . $NotificationStatus;


                                } else {
                                    if (!in_array(trim($NotificationType), $a_chk_Result)) {

                                        $error = true;
                                        $chkNotification = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                        $msgProblem .= '
                                        <div class="col-sm-12">
                                        <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                                            <div class="table_caption_H col-sm-11 alert-danger">Notifica ' . trim($NotificationType) . ' o ' . trim($NotificationStatus) . ' non presente</div>
                                            <div class="clean_row HSpace4"></div>
                                        </div>    
                                        ';
                                    } else {

                                        $NotificationType = $a_Result[$NotificationType] . '/' . $NotificationType;
                                    }

                                }
                                $n_NotificationRow++;
                                $str_out .= '
                                <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $n_NotificationRow . '</div>
                                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $CityId . ' ' . $cont . '</div>
                                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $ProtocolId . '/' . $ProtocolYear . '</div>
                                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkDeliveryDate . ' ' . $NotificationDate . '</div>
                                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $LetterNumber . '</div>
                                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $ReceiptNumber . '</div>
                                    <div class="table_caption_H col-sm-2' . $chkFine . '">' . $chkNotification . ' ' . $NotificationType . '</div>
                                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkSendDate . ' ' . $SendDate . '</div>
                                    <div class="table_caption_H col-sm-1' . $chkFine . '">' . $Box . ' - ' . $Lot . ' - ' . $Position . '</div>
    
                                    <div class="clean_row HSpace4"></div>
                                </div>    
                                ';

                            }

                        }


                    }

                }


            }

        }
        fclose($file);
    }
    if(!$error){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_mercury_exe.php">
            <input type="hidden" name="P" value="imp_mercury.php">
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







