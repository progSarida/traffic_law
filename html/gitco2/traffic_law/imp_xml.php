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
$path = PUBLIC_FOLDER."/_VIOLATION_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');
$chk_Tolerance = 0;
$error = false;

$str_Problem    = '';
$str_Detector   = '';
$str_Article    = '';


$a_Address = array("001" => "Loc. Vigalfo SP 235 intersezione Via Gioiello dir. Pavia");

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





    $rs_country = $rs->Select('Country', "BPCode!=''");
    $a_chk_country = array();
    while ($r_country = mysqli_fetch_array($rs_country)){
        $a_chk_country[$r_country['BPCode']]=$r_country['Title'];
    }

    $rs_controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    $a_chk_controller = array();
    $a_Controller = array();
    while ($r_controller = mysqli_fetch_array($rs_controller)){
        $a_chk_controller[$r_controller['Code']] = $r_controller['Name'];
        $a_Controller[$r_controller['Code']]=$r_controller['Id'];
    }



    $rs_detector = $rs->Select('Detector', "CityId='".$_SESSION['cityid']."'");
    $a_chk_detector = array();
    $a_Tolerance = array();
    $a_Detector = array();
    $a_Fixed = array();
    while ($r_detector = mysqli_fetch_array($rs_detector)){
        $a_chk_detector[$r_detector['Code']] = $r_detector['TitleIta'];
        $a_Tolerance[$r_detector['Code']] = $r_detector['Tolerance'];
        $a_Detector[$r_detector['Code']] = $r_detector['Id'];
        $a_Fixed[$r_detector['Code']] = $r_detector['Fixed'];

    }




    $aVehicleTypeId = array(
        "A"=>1,
        "M"=>2,
        "C"=>2,
        "J"=>1,
        "E"=>1,
        "B"=>1,
        "R"=>1,
        "AUTOVEICOLO"=>1,
        "VEICOLO"=>1,
        "MOTOCICLO"=>2,
        "RIMORCHIO"=>7,
        "F"=>1,
        "MOTOVEICOLO"=>2
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




    foreach($xml->children() as $xml_Import) {


        /*
        <trasgressori><trasgressore><tipo_trasgressore/><ragione_sociale_trasgressore/><cognome_trasgressore/><indirizzo_trasgressore/><cap_trasgressore/><provincia_trasgressore/><nazione_trasgressore/><luogo_nascita_trasgressore/><data_nascita_trasgressore/><numero_patente_trasgressore/><data_rilascio_trasgressore/><ufficio_rilascio_trasgressore/><categoria_patente_trasgressore/><data_acquisizione_dati_trasgressore/></trasgressore></trasgressori><documenti><documento><tipo_documento/><nome_documento/></documento></documenti>
 */
        $cont++;



        $DetectorCode = trim(str_replace("MATR. ","",strtoupper(trim($xml_Import->matricola_rilevatore))));
        $DetectorCode = trim(str_replace("T-XROAD ","",strtoupper(trim($DetectorCode))));


        $CountryId = (trim($xml_Import->nazione_veicolo)=="I") ? "Z000" : "ZZZZ";




        //$DetectorCode = '010101';

        //I684 ENVES EVO,  n° 3503 del 24/06/2011



        $SpeedLimit = $xml_Import->limite;
        $SpeedControl = $xml_Import->velocita_rilevata;
        $FineTime = $xml_Import->ora_violazione;
        $FineDate = $xml_Import->data_violazione;
        $Address = ($_SESSION['cityid']=='I684') ? trim($xml_Import->luogo_violazione) : trim($xml_Import->indirizzo_violazione);


        if(isset($a_Address[$Address]))  $Address = $a_Address[$Address];

        $VehiclePlate = trim(strtoupper($xml_Import->targa_veicolo));
        $VehicleTypeId = $aVehicleTypeId[trim(strtoupper($xml_Import->tipo_veicolo))];
        $ControllerCode = trim($xml_Import->matricola_accertatore);
        $chk_Mass = trim($xml_Import->comma);


        $TrespasserName = trim(trim($xml_Import->ragione_sociale_trasgressore).' '.trim($xml_Import->cognome_trasgressore));




        $ProtocolYear = substr($FineDate,0,4);
        $Where = "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $ProtocolYear;

        if(trim($xml_Import->articolo==7) && $_SESSION['cityid']=='I684' && $DetectorCode=="" &&  trim($xml_Import->comma!="1-14")) {
            $DetectorCode = '010101';
        }

        $ViolationTypeId = 0;
        if($DetectorCode!=""){
            if (isset($a_chk_detector[$DetectorCode])){

                $str_Detector = $a_chk_detector[$DetectorCode];
                $str_FixedWhere = "Fixed=" . $a_Fixed[$DetectorCode];

                if(trim($xml_Import->articolo==142)) {
                    $chk_Tolerance = $a_Tolerance[$DetectorCode];

                    $chk_Tolerance = ($chk_Tolerance > FINE_TOLERANCE) ? $chk_Tolerance : FINE_TOLERANCE;

                    $TolerancePerc = round($SpeedControl * FINE_TOLERANCE_PERC / 100);
                    $Tolerance = ($TolerancePerc < $chk_Tolerance) ? $chk_Tolerance : $TolerancePerc;


                    $Speed = $SpeedControl - $Tolerance;
                    $SpeedExcess = $Speed - $SpeedLimit;


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
                        //$ViolationTypeId = 0;
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

                        $find = mysqli_fetch_array($finds);

                        $ArticleId = $find['Id'];
                        $Fee = $find['Fee'];
                        $MaxFee = $find['MaxFee'];
                        $ViolationTypeId = $find['ViolationTypeId'];


                        $aTime = explode(":", $FineTime);

                        if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY && $aTime[0] != "00")) {
                            $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                            $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);

                        }

                        if ($chk_Mass == "7-11" || $chk_Mass == "8-11" || $chk_Mass == "9-11") {
                            $Fee = $Fee * FINE_MASS;
                            $MaxFee = $MaxFee * FINE_MASS;
                        }


                        $str_Article = $Fee . "/" . $MaxFee;
                    }
                }else{
                    $chk_Article = trim($xml_Import->articolo);

                    $a_Paragraph = explode("-", trim($xml_Import->comma));

                    $chk_Paragraph = $a_Paragraph[0];

                    $Where .= " AND Id1=".$chk_Article." AND Id2='".$chk_Paragraph."'";

                    $chk_Letter = "";
                    if(isset($a_Paragraph[1])){
                        $chk_Letter = $a_Paragraph[1];
                        $Where .= " AND Id3='".$chk_Letter."'";
                    } else{
                        $chk_Letter = trim($xml_Import->lettera);
                        if($chk_Letter != ""){
                            $Where .= " AND Id3='".$chk_Letter."'";
                        }
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
                    <div class="table_caption_H col-sm-11 alert-danger">Articolo '. $chk_Article . ' '. $chk_Paragraph . ' '. $chk_Letter .' anno '.$ProtocolYear.' non presente</div>
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




                    }
                }

            }else{
                $str_FixedWhere = "Fixed IS NULL";
                $error = true;
                $strDetector = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Rilevatore con cod '. $DetectorCode .' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
            }

        } else {
            $str_Detector = "";
            $chk_Article = trim($xml_Import->articolo);
            $str_FixedWhere = "Fixed IS NULL";

            $a_Paragraph = explode("-", trim($xml_Import->comma));

            $chk_Paragraph = $a_Paragraph[0];

            $Where .= " AND Id1=".$chk_Article." AND Id2='".$chk_Paragraph."'";

            $chk_Letter = "";
            if(isset($a_Paragraph[1])){
                $chk_Letter = $a_Paragraph[1];
                $Where .= " AND Id3='".$chk_Letter."'";
            } else{
                $chk_Letter = trim($xml_Import->lettera);
                if($chk_Letter != ""){
                    $Where .= " AND Id3='".$chk_Letter."'";
                }
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
                    <div class="table_caption_H col-sm-11 alert-danger">Articolo '. $chk_Article . ' '. $chk_Paragraph . ' '. $chk_Letter .' anno '.$ProtocolYear.' non presente</div>
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



        $strCountry="";
        foreach ($xml_Import->trasgressori->children() as $xml_Trespasser) {
            $TrespasserTypeId = trim($xml_Trespasser->tipo_trasgressore);
            if($TrespasserTypeId==1){
                $CompanyName = trim($xml_Trespasser->ragione_sociale_trasgressore. " ".$xml_Trespasser->cognome_trasgressore);
                $CompanyAddress = trim($xml_Trespasser->indirizzo_trasgressore);
                $CompanyCity = trim($xml_Trespasser->citta_trasgressore);
                $CompanyProvince = trim($xml_Trespasser->provincia_trasgressore);
                $CompanyZIP = trim($xml_Trespasser->cap_trasgressore);
            } else if($TrespasserTypeId==10){
                $TrespasserAddress = trim($xml_Trespasser->indirizzo_trasgressore);
                $TrespasserCity = trim($xml_Trespasser->citta_trasgressore);
                $TrespasserCountry = strtoupper(trim($xml_Trespasser->nazione_trasgressore));


                $TrespasserZIP = trim($xml_Trespasser->cap_trasgressore);

                $AdditionalCost = trim($xml_Import->costi_aggiuntivi);

                if (array_key_exists($TrespasserCountry,$a_chk_country)){
                    $strCountry = $a_chk_country[$TrespasserCountry];
                }else{
                    $TrespasserCountry = strtoupper(trim($xml_Trespasser->provincia_trasgressore));
                    if (array_key_exists($TrespasserCountry,$a_chk_country)){
                        $strCountry = $a_chk_country[$TrespasserCountry];
                    }else{

                        $strCountry = $a_chk_country['EE'];
                    }
                }

            }
        }

        $chkFile = "";
        foreach($xml_Import->documenti->children()  as $xml_Document) {

            $Documentation = trim($xml_Document->nome_documento);

            if (strpos($Documentation, 'http') === false){
                $str_DocumentType = strtoupper(substr($Documentation, -3));


                if($Documentation == ""){
                    $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">
                        Immagine non presente: targa '.$VehiclePlate.' - '.$Documentation .'
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                } else if (file_exists($path . $Documentation)) {
                    $chkFile = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
                } else {
                    $chkFile = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">' . $cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">
                        Immagine non presente: targa '.$VehiclePlate.' - '.$Documentation .'
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
                }
            }



        }

        $fines = $rs->Select('Fine', "CityId='".$_SESSION['cityid']."' AND FineDate='".$FineDate."' AND FineTime='".$FineTime."' AND REPLACE(VehiclePlate,'  ','')='".$VehiclePlate."'");
        $FindNumber = mysqli_num_rows($fines);

        $chkFine = '';
        if($FindNumber>0){
            $chkFine = ' table_caption_error';
        }


        if (isset($a_chk_controller[$ControllerCode])){
            $chkController = '<i class="glyphicon glyphicon-ok-sign" style="color:green"></i>';
        }else{
            $error = true;
            $chkController = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $str_Problem .= '
                <div class="col-sm-12">
                <div class="table_caption_H col-sm-1 alert-danger">'.$cont . '</div>
                    <div class="table_caption_H col-sm-11 alert-danger">Accertatore '.$ControllerCode.' non presente</div>
                    <div class="clean_row HSpace4"></div>
                </div>    
                ';
        }


        $str_out .='
        <div class="col-sm-12"> 
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$chkFile . ' ' .$cont . ' '.$chkController.'</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$VehiclePlate . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.DateOutDB($FineDate) . ' '.$FineTime .'</div>
            <div class="table_caption_H col-sm-3'.$chkFine.'">'.$Address . '</div>
            <div class="table_caption_H col-sm-2'.$chkFine.'">'.$str_Detector . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$SpeedControl . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$SpeedLimit . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$str_Article . '</div>
            <div class="table_caption_H col-sm-1'.$chkFine.'">'.$CountryId."/".$strCountry.'</div>
            <div class="clean_row HSpace4"></div>
        </div>    
        ';


    }
    if(!$error){
        $str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_xml_exe.php">
            <input type="hidden" name="P" value="imp_xml.php">
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


if(strlen($str_Problem)>0){
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $str_Problem;

}

/*


X BP
REF     N RACC      IMG FRONTE      IMG RETRO       DATA SPED       VERBALE     ESITO       DATA NOTIFICA



*/