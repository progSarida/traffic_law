<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 5000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$n_ContFine = 0;

$CityId = $_SESSION['cityid'];


$UserId = "'".$_SESSION['username']."'";

$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chkTolerance = 0;


$file = fopen($path.$ImportFile,  "r");
$delimiter = detectDelimiter($path . $ImportFile);


$a_VehicleTypeId = array(
    "1"=>1,
    "2"=>1,
    "3"=>4,
    "4"=>4,
    "5"=>8,
    "6"=>2,
    "8"=>7
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


$streetstype = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($streettype = mysqli_fetch_array($streetstype)){
    $a_Street_Type[$streettype['Title']] = $streettype['Id'];
}




$controllers = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
$a_controller = array();
while ($controller = mysqli_fetch_array($controllers)){
    $a_controller[$controller['Code']] = $controller['Id'] ;
}


$countries = $rs->Select('Country', "PolcityCode!=''");
$a_country_id = array();
$a_country_title = array();

while ($country = mysqli_fetch_array($countries)){
    $a_country_id[$country['PolcityCode']]=$country['Id'];
    $a_country_title[$country['PolcityCode']]=$country['Title'];
}






if(is_resource($file)) {
while (!feof($file)) {


    $row = fgetcsv($file, 1000, $delimiter);
    if (isset($row[0])) {

        $rs->Start_Transaction();

        $DocumentationTypeId = 1;
        $StatusTypeId = 1;

        $a_Address = explode(" ",$row[3]);
        $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]),$a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


        $DetectorCode = $row[17];
        $SpeedLimit = $row[14];
        $SpeedControl = round(str_replace(",",".",$row[15]));
        $FineTime = $row[4];
        $FineDate = $row[3];
        $VehicleTypeId = $a_VehicleTypeId[$row[8]];
        $Note = '';

        $str_Address = $row[5];
        $a_AddressLocality = explode(";",$str_Address);
        $a_AddressStreet =  explode("->",$a_AddressLocality[1]);



        $Address = "al km ".$row[7]." ".$a_AddressStreet[0]." DIREZIONE ".$row[6];


        $VehiclePlate= strtoupper($row[9]);


        $rs_VehicleWhiteList = $rs->Select('VehicleWhiteList', "CityId='".$_SESSION['cityid']."' AND VehiclePlate='".$VehiclePlate."'");

        if(mysqli_num_rows($rs_VehicleWhiteList)>0){
            $StatusTypeId = 90;
            $Note = 'White list ente';
        }



        if($row[8]==3 || $row[8]==5){
            $VehicleMass=3.5;
        } else if($row[8]==4 || $row[8]==2 || $row[8]==8){
            $VehicleMass=12;
        } else $VehicleMass=0.00;


        $aFineDate = explode("/",$FineDate);


        $FineDate = $aFineDate[2]."-".$aFineDate[1]."-".$aFineDate[0];
        $ProtocolYear = $aFineDate[2];

        $Code = $row[21]."/".$ProtocolYear;

        $row[18] = '09';
        $ControllerId = $a_controller[$row[18]];




        $DepartmentId = CheckValue('DepartmentId','n');


        $detectors = $rs->Select('Detector', "CityId='".$_SESSION['cityid']."' AND Code=".$DetectorCode);
        $detector = mysqli_fetch_array($detectors);


        $DetectorId = $detector['Id'];
        $chkTolerance = $detector['Tolerance'];

        $chkTolerance = ($chkTolerance>FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

        $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
        $Tolerance = ($TolerancePerc<$chkTolerance) ? $chkTolerance : $TolerancePerc;



        $chkTolerance = $detector['Tolerance'];
        $chkTolerance = ($chkTolerance>FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

        $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
        $Tolerance = ($TolerancePerc<$chkTolerance) ? $chkTolerance : $TolerancePerc;


        $Speed = $SpeedControl - $Tolerance;
        $SpeedExcess = $Speed - $SpeedLimit;

        $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


        if($SpeedExcess<=10){
            $Where .= " AND Article=142 AND Paragraph=7";
        }elseif($SpeedExcess<=40){
            $Where .= " AND Article=142 AND Paragraph=8";
        }elseif($SpeedExcess<=60){
            $Where .= " AND Article=142 AND Paragraph=9 AND Letter!='bis'";
        }else{
            $Where .= " AND Article=142 AND Paragraph=9 AND Letter='bis'";
        }


        $finds = $rs->Select('V_Article', $Where);


        $find = mysqli_fetch_array($finds);

        $ArticleId= $find['Id'];



        $ViolationTitle = $find['ViolationTitle'];

        $Fee = $find['Fee'];
        $MaxFee = $find['MaxFee'];
        $ViolationTypeId = $find['ViolationTypeId'];

        if($VehicleMass>=MASS){
            $Fee = $Fee*FINE_MASS;
            $MaxFee = $MaxFee*FINE_MASS;
        }


        $rs_Reasons = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=".$ViolationTypeId." AND Disabled=0 AND CityId='".$_SESSION['cityid']."'");
        $n_rsNumber = mysqli_num_rows($rs_Reasons);

        $rs_Reason = mysqli_fetch_array($rs_Reasons);
        $ReasonId = $rs_Reason['Id'];


        $aTime = explode(":",$FineTime);


        if($aTime[0]<FINE_HOUR_START_DAY || ($aTime[0]>FINE_HOUR_END_DAY) ||  ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
            //FINE_MINUTE_START_DAY
            //FINE_MINUTE_END_DAY
            $Fee = $Fee + round($Fee/FINE_NIGHT,2);
            $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);

        }



        //$countries = $rs->Select('Country', "Code='".$row[8]."'");

        //$country = mysqli_fetch_array($countries);

        $VehicleCountry = $a_country_title[$row[10]];
        $CountryId = $a_country_id[$row[10]];



        $fines = $rs->Select('Fine', "CityId='".$_SESSION['cityid']."' AND FineDate='".$FineDate."' AND FineTime='".$FineTime."' AND VehiclePlate='".$VehiclePlate."'");
        $FindNumber = mysqli_num_rows($fines);



        if($FindNumber==0){
            $a_Fine = array(
                array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$Code),
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
                array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$ProtocolYear),
                array('field'=>'FineDate','selector'=>'value','type'=>'date','value'=>$FineDate),
                array('field'=>'FineTime','selector'=>'value','type'=>'time','value'=>$FineTime),
                array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
                array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$a_Locality[$a_AddressLocality[0]]),
                array('field'=>'StreetTypeId','selector'=>'value','type'=>'int','value'=>$StreetTypeId,'settype'=>'int'),
                array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
                array('field'=>'VehicleTypeId','selector'=>'value','type'=>'int','value'=>$VehicleTypeId,'settype'=>'int'),
                array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
                array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$VehicleCountry),
                array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryId),
                array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$DepartmentId),
                array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$VehicleMass,'settype'=>'flt'),
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$Note),
            );



            $FineId = $rs->Insert('Fine',$a_Fine);

            $n_ContFine++;

            if($FineId==0){
                echo "Poblemi con l'inserimento del verbale con targa: ".$VehiclePlate;
                    die;
            }

            $a_FineArticle = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId,'settype'=>'int'),
                array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
                array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
                array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
                array('field'=>'DetectorId','selector'=>'value','type'=>'int','value'=>$DetectorId,'settype'=>'int'),
                array('field'=>'SpeedLimit','selector'=>'value','type'=>'flt','value'=>$SpeedLimit,'settype'=>'flt'),
                array('field'=>'SpeedControl','selector'=>'value','type'=>'flt','value'=>$SpeedControl,'settype'=>'flt'),
                array('field'=>'Speed','selector'=>'value','type'=>'flt','value'=>$Speed,'settype'=>'flt'),
            );

            $rs->Insert('FineArticle',$a_FineArticle);


            if (file_exists($path.$row[1]."/scheda.jpg")) {
                $DocumentName = "scheda.jpg";
                $str_Folder = ($a_country_id[$row[10]]=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


                if (!is_dir($str_Folder."/".$_SESSION['cityid'])) {
                    mkdir($str_Folder."/".$_SESSION['cityid'], 0777);
                }
                if (!is_dir($str_Folder."/".$_SESSION['cityid']."/".$FineId)) {
                    mkdir($str_Folder."/".$_SESSION['cityid']."/".$FineId, 0777);
                }



                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);



                if($compress){
                    $img = new Imagick($path.$row[1]."/".$DocumentName);
                    $width = intval($img->getimagewidth() / 3);
                    $height = intval($img->getimageheight() / 3);
                    $img->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
                    $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                    $img->setImageCompressionQuality(40);
                    $img->stripImage();
                    $img->writeImage($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$DocumentName);
                    $img->destroy();

                } else{
                    copy($path.$row[1]."/".$DocumentName, $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$DocumentName);
                }


                if (file_exists($str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$DocumentName)) {
                    unlink($path.$row[1]."/".$DocumentName);
                    rmdir($path.$row[1]);
                    } else {
                    echo "Poblemi con la creazione del documento: ".$DocumentName;
                        die;
                }



            }

        }
        $rs->End_Transaction();
    }
}

fclose($file);
}


unlink($path.$ImportFile);

if($n_ContFine>0){


    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

        $str_Content = $r_UserMail['CityTitle'].": sono state elaborate n. ".$n_ContFine." violazioni.";

        $a_Mail = array(
            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
            array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione"),
            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail',$a_Mail);
        $rs->End_Transaction();
    }

}




header("location: ".$P);