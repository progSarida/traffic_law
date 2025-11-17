<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');


ini_set('max_execution_time', 3000);

$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER . "/" . $_SESSION['cityid'] . "/";
$ImportFile = CheckValue('ImportFile', 's');
$chkTolerance = 0;
$error = false;
$str_MsgProblem = "";


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
                <a href="imp_not.php?ImportFile=' . $file . '"><span class="fa fa-upload"></span></a>
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
				<div class="table_label_H col-sm-12">ELENCO FILE</div>
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


    $file = fopen($path . $ImportFile, "r");
    $delimiter = ",";
    $cont = 0;


    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE ' . $ImportFile . '</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Riga</div>
            <div class="table_label_H col-sm-1">Fine Id</div>
            <div class="table_label_H col-sm-1">Cron</div>
            <div class="table_label_H col-sm-1">Prot</div>            
            <div class="table_label_H col-sm-2">Date</div>
            
            <div class="table_label_H col-sm-1">Id Iride</div>            
            <div class="table_label_H col-sm-1">Nuova Id iride</div>

            <div class="table_label_H col-sm-2">Send date</div>
            <div class="table_label_H col-sm-2">Notification date</div>
            

            <div class="clean_row HSpace4"></div>	
        </div>
        ';

    $n_Row = 0;
    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && trim($row[0]) != "Id") {

                $n_Row++;
                $ProtocolId = $row[0];
                $ProtocolYear = $row[5];

                $ExternalProtocol = $row[2];
                $ExternalYear = $row[1];
                $ExternalDate = $row[3];
                $ExternalTime = date("H:i");

                //$TrespasserCode = $row[16];
                $IrideCode = $row[16];


                $a_SendDate = explode(" ", trim($row[18]));
                $SendDate = trim($a_SendDate[0]);

                if (strlen(trim($row[19])) > 0) {
                    $a_NotificationDate = explode(" ", trim($row[19]));
                    $NotificationDate = trim($a_NotificationDate[0]);
                } else {
                    $NotificationDate = "";
                }

                $rs_Fine = $rs->Select('V_FineTrespasser', "CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $ProtocolId . " AND ProtocolYear=" . $ProtocolYear);

                if (mysqli_num_rows($rs_Fine) == 0) {
                    $error = true;
                    $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $str_MsgProblem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $n_Row . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Verbale con cron ' . $ProtocolId . '/' . $ProtocolYear . ' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';

                } else {

                    $r_Fine = mysqli_fetch_array($rs_Fine);
                    $FineId = $r_Fine['FineId'];


                    $str_out .= '
            <div class="col-sm-12"> 
                <div class="table_caption_H col-sm-1">' . $n_Row . '</div>
                <div class="table_caption_H col-sm-1">' . $FineId . '</div>
                <div class="table_caption_H col-sm-1">' . $ProtocolId . '/' . $ProtocolYear . '</div>
                <div class="table_caption_H col-sm-1">' . $ExternalProtocol . '/' . $ExternalYear . '</div>
                <div class="table_caption_H col-sm-2">' . $ExternalDate . '/' . $ExternalTime . '</div>
                
                <div class="table_caption_H col-sm-1">' . $r_Fine['IrideCode'] . '</div>
                <div class="table_caption_H col-sm-1">' . $IrideCode . '</div>

                <div class="table_caption_H col-sm-2">' . $SendDate . '</div>
                <div class="table_caption_H col-sm-2">' . $NotificationDate . '</div>
 
                <div class="clean_row HSpace4"></div>
			</div>    
            ';


                }

            }


        }
        fclose($file);
    }
    if (!$error) {
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_not_exe.php">
            <input type="hidden" name="P" value="imp_not.php">
            <input type="hidden" name="ImportFile" value="' . $ImportFile . '">
            <div class="table_label_H col-sm-12">
                <input type="submit" value="Importa" >                           
            </div >
		</div >';
    }
}

echo $str_out;


if (strlen($str_MsgProblem) > 0) {
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $str_MsgProblem;

}
