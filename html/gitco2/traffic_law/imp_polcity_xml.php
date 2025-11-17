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

$str_Problem    = '';
$str_Detector   = '';
$str_Article    = '';

if ($directory_handle = opendir($path)) {

    while (($file = readdir($directory_handle)) !== false) {
        $aFile = explode(".","$file");
        if (strtolower($aFile[count($aFile)-1])=="xml"){
            $Cont++;
            $FileList .=  '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$Cont . '</div>
            <div class="table_caption_H col-sm-10">'.$file . '</div>
            <div class="table_caption_button col-sm-1">
                '. ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>') .'
                <a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>
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






    $rs_country = $rs->Select('Country', "MegaspCode!=''");
    $a_chk_country = array();


    while ($r_country = mysqli_fetch_array($rs_country)){

        $a_chk_country[$r_country['MegaspCode']] = $r_country['Title'];

    }

    $rs_controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_chk_controller = array();
    $a_Controller = array();
    while ($r_controller = mysqli_fetch_array($rs_controller)){
        $a_chk_controller[$r_controller['Code']] = $r_controller['Name'];
        $a_Controller[$r_controller['Code']]=$r_controller['Id'];
    }


    $a_VehicleTypeId = array(
        "AUTOVETTURA"=>1,
        "AUTOVEICOLO"=>1,
        "MOTOVEICOLO"=>2,
        "MOTOCICLO"=>2,
        "AUTOCARAVAN"=>1,
        "AUTOCARRO"=>4,
        "CICLOMOTORE"=>9,
        "MOTOCARRO"=>4,
        "AUTOBUS"=>1,
    );


    $xml=simplexml_load_file($path.$ImportFile) or die("Error: Cannot create object");

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
            <div class="table_label_H col-sm-1">Data\</div>
            <div class="table_label_H col-sm-3">Località</div>
            <div class="table_label_H col-sm-2">Nazione</div>
            <div class="table_label_H col-sm-1">Articolo</div>
            <div class="clean_row HSpace4"></div>	
        </div>
        ';



    foreach($xml->children() as $xml_Import) {
        $chkFine = "";
        $cont++;



        $Code = $xml_Import->Numero->Numero."/".$xml_Import->Numero->Tipoa."/".$xml_Import->Numero->Anno;
        $IuvCode = $xml_Import->Numero->IDUnivoco;

        $FineDate = $xml_Import->DataRilevazione;
        $ProtocolYear = substr($FineDate,0,4);

        $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;


        $FineTime = $xml_Import->Ore;


        $ControllerId = (int)$xml_Import->Agenti->Matricola;



        $Address = $xml_Import->Luogo->Descrizione;

        if(isset($xml_Import->Luogo->Civico)){
            $Address .= " " . $xml_Import->Luogo->Civico;
        }

        $VehiclePlate = strval($xml_Import->Targa);
        $VehicleTypeId = strval($xml_Import->Veicolo->Descrizione);
        $VehicleBrand = strval($xml_Import->Marca);
        $VehicleModel = strval($xml_Import->Modello);

        $VehicleCountryId = strval($xml_Import->Stato->Codice);
        //<Descrizione>FRANCIA</Descrizione>


        $DetectorId = 0;
        $DetectorCode = "";

        $str_FixedWhere = "Fixed IS NULL";
        $str_Article = trim($xml_Import->CDSInfrazioni->Articolo);

        $a_Article = explode("-",$str_Article);

        $Article = trim($a_Article[0]);

        if(isset($a_Article[1])){
            $a_Paragraph = explode("/", trim($a_Article[1]));

            $Paragraph = $a_Paragraph[0];

            $Letter = (isset($a_Paragraph[1])) ?$a_Paragraph[1] : "";

        }else{
            $Paragraph = "0";
            $Letter = "";
        }






        $Where .= " AND Id1=".$Article." AND Id2='".$Paragraph."'";

        if($Letter!=""){
            $Where .= " AND Id3='".$Letter."'";
        }


        $finds = $rs->Select('V_Article', $Where);
        $FindNumber = mysqli_num_rows($finds);


        if ($FindNumber == 0) {
            $ViolationTypeId = 0;
            $error = true;
            $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Articolo '. $Article . ' '. $Paragraph . ' '. $Letter .' anno '.$ProtocolYear.' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';

        } else {

            $find = mysqli_fetch_array($finds);

            $ArticleId= $find['Id'];
            $Fee = $find['Fee'];
            $MaxFee = $find['MaxFee'];

            $str_Article = $Fee."/".$MaxFee;

            $ViolationTypeId = $find['ViolationTypeId'];
            $AdditionalNight = $find['AdditionalNight'];

            if($AdditionalNight==1){

                $aTime = explode(":",$FineTime);

                if($aTime[0]<FINE_HOUR_START_DAY || ($aTime[0]>FINE_HOUR_END_DAY && $aTime[0]!="00")){
                    $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                    $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);

                }
            }
        }















        $str_Where = $str_FixedWhere . " AND ReasonTypeId=1 AND CityId='".$_SESSION['cityid']."'";
        switch($ViolationTypeId){
            case 4:
            case 6:
                $str_Where .= ($DetectorCode=="") ? " AND ViolationTypeId=1" : " AND ViolationTypeId=" .$ViolationTypeId;
                break;

            default:
                $str_Where .= " AND ViolationTypeId=" .$ViolationTypeId;
        }



        $rs_reason = $rs->Select('Reason', $str_Where);

        $FindNumber = mysqli_num_rows($rs_reason);

        if($FindNumber==0){
            $error = true;
            $str_Article = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Mancata contestazione non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
        }

        $fines = $rs->Select('Fine', "CityId='".$_SESSION['cityid']."' AND FineDate='".$FineDate."' AND FineTime='".$FineTime."' AND REPLACE(VehiclePlate,'  ','')='".$VehiclePlate."'");
        $FindNumber = mysqli_num_rows($fines);

        $chkFine = '';
        if($FindNumber>0){
            $chkFine = ' table_caption_error';
        }


        if (isset($a_Controller[$ControllerId])){
            $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
        }else{
            $error = true;
            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Accertatore '.$ControllerId.' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
        }

        $str_Country = "";


        if (array_key_exists($VehicleCountryId, $a_chk_country)){
            $str_Country = $a_chk_country[$VehicleCountryId];
        }else{
            $error = true;
            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Nazione '.$VehicleCountryId.' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
        }


        if (array_key_exists($VehicleTypeId, $a_VehicleTypeId)){
            $str_Vehicle = $a_VehicleTypeId[$VehicleTypeId];
        }else{
            $error = true;
            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Veicolo '.$VehicleTypeId.' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
        }









        $str_out .='
        <div class="col-sm-12"> 
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$cont . ' '.$chkController.'</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$VehiclePlate . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.DateOutDB($FineDate) . ' '.$FineTime .'</div>
            <div class="table_caption_H col-sm-3'.$chkFine.'">'.$Address . '</div>
            <div class="table_caption_H col-sm-2'.$chkFine.'">'.$str_Country . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$str_Article . '</div>
            <div class="clean_row HSpace4"></div>
        </div>    
        ';


    }

if(!$error){
    $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_polcity_xml_exe.php">
            <input type="hidden" name="P" value="imp_polcity_xml.php">
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

echo $str_out;


if(strlen($str_Problem)>0){
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $str_Problem;

}
}

