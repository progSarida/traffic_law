<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC . "/function_import.php");

include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
ini_set('max_execution_time', 3000);
ini_set('display_errors',"1");
$FileList = "";
$Cont = 0;
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;
$error_count = 0;
$msgProblem = "";

function dateDifference($start_date, $end_date)
{
    // calulating the difference in timestamps
    $diff = strtotime($start_date) - strtotime($end_date);

    // 1 day = 24 hours
    // 24 * 60 * 60 = 86400 seconds
    return ceil(abs($diff / 86400));
}

$zipFiles=glob($path."*.zip");
foreach($zipFiles as $zipFile){
    $zip = new ZipArchive();
    $res = $zip->open($zipFile);
    $zip->extractTo($path);
    unlink($zipFile);
}

$folders=glob($path."*");
foreach($folders as $folder)
    if(!strpos($folder,".")>0){
        $subfolders=glob($folder."/*");
        foreach($subfolders as $subfolder){
            if(!strpos($folder,".")>0) {
                $images=glob($subfolder."/*.jpg");
                foreach($images as $image){
                    copy($image, $path.basename($image));
                    unlink($image);
                }
                rmdir($subfolder);
            }
        }
        rmdir($folder);
    }

$chk_GlobalImage = false;
$a_VehiclePlate = array();






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
                <a href="imp_selea.php?ImportFile='.$file.'"><span class="fa fa-upload"></span></a>
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
    $delimiter = detectDelimiter($path . $ImportFile);
    $cont = 0;
    /*

	0	CARPLATE	
	1	TIMESTAMP	
	3	CAMERANAME
	4	REASON

	Polizza assicurativa scaduta il: 
	Revisione scaduta il: 

 


*/









    $str_out = '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12">IMPORTAZIONE FILE '.$ImportFile.'</div >
				<div class="clean_row HSpace4" ></div >	
			</div >
		</div >
        <div class="col-sm-12">
                    <form name="f_import" action="imp_selea_exe.php" action="POST">
            <div class="table_label_H col-sm-1">Img Riga</div>
            <div class="table_label_H col-sm-2">Targa</div>
            <div class="table_label_H col-sm-2">Data</div>
            <div class="table_label_H col-sm-3">Luogo</div>
            <div class="table_label_H col-sm-2">Sanzione</div>
            <div class="table_label_H col-sm-1">Precedente verbale</div>                        
            <div class="table_label_H col-sm-1">Importa</div>            

            <div class="clean_row HSpace4"></div>	
        </div>
        ';
    $row = fgets($file);
    if(is_resource($file)) {

        $rs_DetectorSelea = $rs->Select('DetectorSelea', "CityId='". $_SESSION['cityid'] ."'");

        $a_DetectorName = array();

        while($r_DetectorSelea = mysqli_fetch_array($rs_DetectorSelea)){
            $a_DetectorName[$r_DetectorSelea['Code']] = $r_DetectorSelea['Locality'] .' - '. $r_DetectorSelea['Address'];
        }







        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0]) && $row[0] != "CARPLATE") {

                $error = false;
                $cont++;
                $chk_VehiclePlate = true;


                $str_FixedWhere = "Fixed IS NULL";
                $chkFine = '';



                $strDetector = "";
                $ReasonId = null;


                $FineDate = $row[1];


                $VehiclePlate = strtoupper($row[0]);
                if (in_array($VehiclePlate, $a_VehiclePlate)) {
                    $chk_VehiclePlate = false;
                    $cont--;

                } else {
                    $a_VehiclePlate[] = $VehiclePlate;
                }


                if (!$chk_GlobalImage) {
                    $chk_GlobalImage = true;
                    $aDocViolation = glob($path . '*.jpg');

                }

                if (strpos($FineDate, 'T') !== false) {


                    $str_DateTime = str_replace("T", "_", $FineDate);
                    $str_DateTime = str_replace(":", "-", $str_DateTime);
                    $str_DateTime = str_replace(".", "-", $str_DateTime);

                    $a_DateTime = explode("T", $FineDate);

                    $FineDate = $a_DateTime[0];
                    $FineTime = $a_DateTime[1];


                }
                $aFineDate = explode("-", $FineDate);

                $FineDate = $aFineDate[0] . "-" . $aFineDate[1] . "-" . $aFineDate[2];
                $ProtocolYear = $aFineDate[0];


                $aFineTime = explode(":", $FineTime);
                if (strlen($aFineTime[0]) < 2) $aFineTime[0] = "0" . $aFineTime[0];
                if (strlen($aFineTime[1]) < 2) $aFineTime[1] = "0" . $aFineTime[1];
                $FineTime = $aFineTime[0] . ":" . $aFineTime[1];


                $str_Address = trim($row[4]);

                $Address = "";
                if (! isset($a_DetectorName[$str_Address])) {
                    $error = true;
                    $error_count ++;
                    $chk_Address = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Telecamera  ' . trim($str_Address) . ' non trovata</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                } else {
                    $chk_Address = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                    $Address = $a_DetectorName[$str_Address];
                }


                $rs_FineAll = $rs->Select('V_FineAll', "REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "' AND  ((Article=80 AND Paragraph=14 AND (Letter is null or Letter = '')) OR (Article=193 AND Paragraph =2 AND (Letter is null or Letter = '')))");

                $FindNumber = mysqli_num_rows($rs_FineAll);
                $chkFine = '';
                $str_day_diff_from_fine = '';
                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_warning_low';
                    $DB_FineDate = mysqli_fetch_array($rs_FineAll)['FineDate'];

                    $str_day_diff_from_fine = dateDifference($FineDate,$DB_FineDate). ' giorni';
                }

                $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


                $SpeedLimit 	= 0.00;
                $SpeedControl 	= 0.00;
                $Speed 			= 0.00;
                $DetectorId 	= 0;
                $DetectorCode 	= 0;

                $b_AdditionalArticle = false;



                $str_Art80 	= "REVISIONE SCADUTA IL:";
                $str_Art193 = "POLIZZA ASSICURATIVA SCADUTA IL:";

                $str_ArticleContent = strtoupper(trim($row[5]));

                $b_Art193 = false;



                // 80-14    193-2


                $str_Article = '';
                if (strpos($str_ArticleContent, $str_Art80) === false) {

                    $b_Art193 = true;

                } else {


                    $str_Article .= "80-14 ";

                    $rs_Article = $rs->Select('V_Article', $Where . " AND Id1=80 AND Id2='14' AND (Id3 is null or Id3 = '')");

                    $FindNumber = mysqli_num_rows($rs_Article);

                    if ($FindNumber == 0) {
                        $error = true;
                        $error_count ++;
                        $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
							<div class="col-sm-12">
							<div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
								<div class="table_caption_H col-sm-11 alert-danger">Articolo 80-14 anno ' . $ProtocolYear . ' non presente</div>
								<div class="clean_row HSpace4"></div>
							</div>    
							';

                    } else {


                        $r_Article = mysqli_fetch_array($rs_Article);

                        $ArticleId 		= $r_Article['Id'];
                        $Fee 			= $r_Article['Fee'];
                        $MaxFee 		= $r_Article['MaxFee'];


                        $ViolationTypeId = $r_Article['ViolationTypeId'];



                        $a_TMP_Date = explode($str_Art80, $str_ArticleContent);
                        $str_Date = substr(trim($a_TMP_Date[1]),0,10);

                        $d1 = new DateTime($FineDate);
                        $d2 = new DateTime($str_Date);

                        $diff = $d2->diff($d1);

                        if ($diff->y >= 4) {
                            $Fee = $Fee * 2;
                            $MaxFee = $MaxFee * 2;
                        };


                        $str_Article .= "(". $Fee ."/". $MaxFee .")";


                    }


                    if (! strpos($str_ArticleContent, $str_Art193) === false) $b_AdditionalArticle = true;

                }



                if($b_Art193 || $b_AdditionalArticle){

                    $str_Article .= "193-2 ";
                    $rs_Article = $rs->Select('V_Article', $Where . " AND Id1=193 AND Id2='2' AND (Id3 is null or Id3 = '')");


                    $FindNumber = mysqli_num_rows($rs_Article);

                    if ($FindNumber == 0) {
                        $error = true;
                        $error_count ++;
                        $strArticle = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
							<div class="col-sm-12">
							<div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
								<div class="table_caption_H col-sm-11 alert-danger">Articolo 193-2 anno ' . $ProtocolYear . ' non presente</div>
								<div class="clean_row HSpace4"></div>
							</div>    
							';

                    } else {


                        $r_Article = mysqli_fetch_array($rs_Article);

                        $ArticleId = $r_Article['Id'];
                        $Fee = $r_Article['Fee'];
                        $MaxFee = $r_Article['MaxFee'];



                        $ViolationTypeId = $r_Article['ViolationTypeId'];

                        $str_Article .= "(". $Fee ."/". $MaxFee .")";
                    }
                }


                if (!$error) {
                    $rs_reason = getReasonRs($ReasonId, $_SESSION['cityid'], $ViolationTypeId, $DetectorCode);
                    $FindNumber = mysqli_num_rows($rs_reason);
                    if ($FindNumber == 0) {
                        $error = true;
                        $error_count ++;
                        $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                        $msgProblem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger"></div>
                        <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
                    }
                }



                if ($chk_GlobalImage) {
                    $chkFile = "";


                    $matches = preg_grep('/' . strtoupper($VehiclePlate) . '/', $aDocViolation);
                    $keys = array_keys($matches);


                    foreach ($matches as $key => $value) {


                        if (strpos($value, $str_DateTime) !== false) {

                            if (file_exists($value)) {
                                $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                            } else {
                                echo $value . "<br>";
                                $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                            }

                        }
                    }
                    if ($chkFile == "") $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                } else {
                    $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';

                }

                $rs_fine = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND REPLACE(VehiclePlate,'  ','')='" . $VehiclePlate . "'");
                $FindNumber = mysqli_num_rows($rs_fine);


                if ($FindNumber > 0) {
                    $chkFine = ' table_caption_error';
                }


                if ($chk_VehiclePlate) {

                    $str_ImpRecord = '';
                    if($chkFine == ' table_caption_warning_low'){
                        $str_ImpRecord ='
							<select name="IMP_'. strtoupper($VehiclePlate) .'">
								<option value="0">NO</option>
								<option value="1">SI</option>
							</select>
						';
                    }


                    $str_out .= '
						<div class="col-sm-12"> 
							<div class="table_caption_H col-sm-1' . $chkFine . '">' . $chkFile . ' ' . $cont . '</div>
							<div class="table_caption_H col-sm-2' . $chkFine . '">' . strtoupper($VehiclePlate) . '</div>
							<div class="table_caption_H col-sm-2' . $chkFine . '">' . DateOutDB($FineDate) . ' ' . $FineTime . '</div>
							<div class="table_caption_H col-sm-3' . $chkFine . '">' . $chk_Address . ' ' . $Address . '</div>
							<div class="table_caption_H col-sm-2' . $chkFine . '">' . $str_Article . '</div>
							<div class="table_caption_H col-sm-1' . $chkFine . '">' . $str_day_diff_from_fine . '</div>
							<div class="table_caption_H col-sm-1' . $chkFine . '">' . $str_ImpRecord . '</div>							
							<div class="clean_row HSpace4"></div>
						</div>    
						';
                }

            }

        }
        fclose($file);
    }
    if($error_count <= 0){
        $str_out .= '
        <div class="col-sm-12">

            <input type="hidden" name="P" value="imp_selea.php">
            <input type="hidden" name="ImportFile" value="'.$ImportFile.'">
            
                    <div class="col-sm-1 BoxRowLabel">
                            Accertatore 1
                    </div>				
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelectConcat("SELECT Id, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId='".$_SESSION['cityid']."' ORDER BY Name","ControllerId1","Id","Name","",true,15) .'
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                            Accertatore 2
                    </div>				
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelectConcat("SELECT Id, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId='".$_SESSION['cityid']."' ORDER BY Name","ControllerId2","Id","Name","",false,15) .'
                    </div>

                    <div class="col-sm-3 BoxRowLabel">
                            Comprimi immagini
                    </div>				
                    <div class="col-sm-3 BoxRowCaption">
                        <select name="compress">
                            <option value="0">NO</option>
                            <option value="1">SI</option>
                        </select>
                    </div>
            
                    <div class="table_label_H col-sm-12">
                        <input type="submit" value="Importa" >                           
                    </div>
                  
		</div >
		</form>  
		';
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
