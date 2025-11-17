<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");


ini_set('max_execution_time', 0);

$FileList = "";
$n_ContFine = 0;

$CityId = CheckValue('CityId', 's');
$ProtocolYear = CheckValue('ProtocolYear', 'n');
$SessionCityId = $_SESSION['cityid'];
$message=new CLS_MESSAGE();
if($SessionCityId==null){
    $message->addError("Sessione vuota, impossibile procedere conl' importazione");
    echo $message->getMessagesString();
}
$error = false;
$msg_Problem = "";

$DocumentPath = PUBLIC_FOLDER . "/C933/DocsTargheEstere/".$CityId."/";
$PhotoPath = PUBLIC_FOLDER . "/C933/FotoTargheEstere/".$CityId."/";
$NotificationPath = PUBLIC_FOLDER . "/C933/Notifiche/".$CityId."/";


$str_FolderViolation = FOREIGN_VIOLATION;
$str_FolderFine = FOREIGN_FINE;

$b_FileSystemError = false;
$str_FileSystem = "";


$n_PreviousIdTable = 0;

$n_Communication = 0;
$chk_Archive = true;
$chk_Payment = true;

$DocumentationTypeId = 1;

$ReceiveDate = "";

$TableId = 1;
$ImportationId = 0;
$a_Vehicle = array(
    "autoveicolo" => 1,
    "motoveicolo" => 2,
    "furgone" => 3,
    "autocarro" => 4,
    "autocaravan" => 5,
    "altro" => 6,
    "rimorchio" => 7,
    "autobus" => 8,
    "ciclomotore" => 9
);


$a_Notification = array(
    "Indirizzo Inesatto" => "03 - Indirizzo inesatto",
    "Indirizzo Insufficiente" => "04 - Indirizzo insufficiente",
    "Irreperibile" => "07 - Irreperibile",
    "Non ritirato" => "11 - Non notificato",
    "Trasferito" => "10 - Trasferito",
    "Rifiutato" => "02 - Rifiutato",
    "Sconosciuto" => "08 - Sconosciuto",
    "Deceduto" => "09 - Deceduto",


);

$a_PaymentTypeId = array(
    "CC" => 2,
    "PAYPAL" => 3,
    "ASSEGNO" => 5,
    "BANCOMAT" => 2,
    "POS" => 2,
    "VAGLIA" => 2,

);


$a_TrespasserTypeId = array(
    "COINCIDENTE" => 1,
    "OBBLIGATO" => 2,
    "TRASGRESSORE" => 3,
);


$rs_Result = $rs->Select('Result', "Disabled=0");
$a_chk_Result = array();
while ($r_Result = mysqli_fetch_array($rs_Result)) {
    $a_chk_Result[] = $r_Result['Description'];
    $a_Result[$r_Result['Description']] = $r_Result['Id'];
}


$a_Controller = array();
$rs_Controller = $rs->Select('Controller', "CityId='" . $CityId . "'");

while ($r_Controller = mysqli_fetch_array($rs_Controller)) {

    $a_Controller[$r_Controller['Name']] = $r_Controller['Id'];

}


$rs_StreetType = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($r_StreetType = mysqli_fetch_array($rs_StreetType)) {
    $a_Street_Type[$r_StreetType['Title']] = $r_StreetType['Id'];
}


$rs_ReasonArchive = $rs->Select('Reason', "ReasonTypeId=2");
$a_ReasonArchive = array();
while ($r_ReasonArchive = mysqli_fetch_array($rs_ReasonArchive)){
    $a_ReasonArchive[$r_ReasonArchive['TitleIta']] = $r_ReasonArchive['Id'];
}


$rs_V_Gitco = $rs->Select('gitco2.V_Gitco', "Reg_Comune_Violazione='".$CityId."' AND Reg_Anno=".$ProtocolYear." AND Reg_Progr NOT IN(SELECT Reg_Progr_Provenienza Reg_Progr FROM gitco2.registro_cronologico_cds)", "Reg_Progr_Registro LIMIT 500");
//$rs_V_Gitco = $rs->Select('gitco2.V_Gitco', "Reg_Comune_Violazione='".$CityId."' AND Reg_Anno=".$ProtocolYear." AND Reg_Progr=14181", "Reg_Progr_Registro DESC LIMIT 5");

while ($r_V_Gitco = mysqli_fetch_array($rs_V_Gitco)) {


    /*
    $PreviousId`,

    Reg_Progr_Provenienza



    */



    $chk_Fine = "";


    $ChiefController = 0;
    $ProtocolId = 0;

    if(isset($a_Controller[$r_V_Gitco['Acc_Accertatore']])){
        $ControllerId = $a_Controller[$r_V_Gitco['Acc_Accertatore']];
    } else {
        $ControllerId = 225;
    }

    $StatusTypeId = 1;
    $VehicleCountry = "Da assegnare";
    $CountryId = "ZZZZ";


    if (strlen(trim($r_V_Gitco['Reg_Ente_Per_Richiesta'])) > 0) {
        $VehicleCountry = $r_V_Gitco['Tar_Nazione_Nome'];
        $CountryId = $r_V_Gitco['CC_Paese_Estero'];
    }


    $rs_V_GitcoNotifica = $rs->SelectQuery("SELECT DISTINCT * FROM  gitco2.V_GitcoNotifica WHERE Riferimento_Notifica=" . $r_V_Gitco['Reg_Progr']. " ORDER BY Tipo_Trasgressore DESC");


    if($CountryId==""){
        if(mysqli_num_rows($rs_V_GitcoNotifica)==2){
            $StatusTypeId = 2;
            $VehicleCountry = "Italia Noleggi";
            $CountryId = "Z00Z";
        } else{
            $VehicleCountry = "Da assegnare";
            $CountryId = "ZZZZ";
        }


    }
    if($r_V_Gitco['Reg_Genere_Infrazione']=="AUTOVELOX"){
        if($r_V_Gitco['Tar_Codice_3']!="bis"){

            $Id3 = str_replace("b","bis",$r_V_Gitco['Tar_Codice_3']);
        } else{
            $Id3 = $r_V_Gitco['Tar_Codice_3'];
        }

    } else {
        $Id3 = $r_V_Gitco['Tar_Codice_3'];
    }

    $Id1 = $r_V_Gitco['Tar_Codice_1'];
    $Id2 = $r_V_Gitco['Tar_Codice_2'];

    if($CityId=="C933"){
        $Id1 = $r_V_Gitco['Tar_Articolo'];
        $Id2 = $r_V_Gitco['Tar_Comma'];
        $Id3 = $r_V_Gitco['Tar_Lettera'];



        if($Id1=="7" && $Id2=="0" && $Id3==""){

            $Id1=$r_V_Gitco['Tar_Codice_1'];
            $Id2=$r_V_Gitco['Tar_Codice_2'];
            $Id3=$r_V_Gitco['Tar_Codice_3'];

        }
        else if($Id1=="7" && $Id2=="1" && $Id3=="1r"){
            $Id1="7";
            $Id2="0";
            $Id3="1r";

        }else if($Id1=="7" && $Id2=="9"){

            $Id1="7";
            $Id2="9";
            $Id3="14";

        } else if($Id1=="7" && $Id2=="1" && $Id3=="a"){
            $Id1="7";
            $Id2="0";
            $Id3="g";

        }else if($Id1=="40" && $Id2=="2" && $Id3=="146"){
            $Id1="40";
            $Id2="0";
            $Id3="2";

        }else if($Id1=="99" && $Id2=="0" && $Id3=="RPM"){
            $Id1="99";
            $Id2="0";
            $Id3="0";

        }else if($Id1=="146" && $Id2=="1" && $Id3==""){
            $Id1="146";
            $Id2="2";
            $Id3="40";

        }else if($Id1=="157" && $Id2=="0" && $Id3=="E"){
            $Id1="157";
            $Id2="0";
            $Id3="e";

        }else if($Id1=="158" && $Id2=="1" && $Id3=="h"){
            $Id1="158";
            $Id2="0";
            $Id3="p";

        }
    }
    $rs_Article = $rs->Select('V_Article', "Id1='".$Id1."' AND Id2='".$Id2."' AND Id3='".$Id3."' AND Year =" .$ProtocolYear. " AND CityId='".$CityId."'");

    if(mysqli_num_rows($rs_Article)==0){
        echo $Id1." - ".$Id2." - ".$Id3." :  non presente";
        DIE;
    }else{

        $r_Article = mysqli_fetch_array($rs_Article);
        $ArticleId = $r_Article['Id'];
        $ViolationTypeId = $r_Article['ViolationTypeId'];


        $rs_Reasons = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=" . $ViolationTypeId . " AND Disabled=0 AND CityId='" . $CityId . "'");
        $n_rsNumber = mysqli_num_rows($rs_Reasons);


        $rs_Reason = mysqli_fetch_array($rs_Reasons);
        $ReasonId = $rs_Reason['Id'];
    }



    $Fee = $r_V_Gitco['Reg_Importo_Amministrativo'];
    $MaxFee = $r_V_Gitco['Reg_Importo_Sanzione_Massima'];


    $DetectorId = 0;

    $SpeedLimit = 0.00;
    $SpeedControl = 0.00;
    $Speed = 0.00;

    $TimeTLightFirst = 0;
    $TimeTLightSecond = 0;

    if ($r_V_Gitco['Reg_Rilevatore_Elettronico'] > 0) {
        $rs_Detector = $rs->Select('Detector', "Code='" . $r_V_Gitco['Ril_Matricola_Sistema'] . "' AND CityId='" . $CityId . "'");


        $r_Detector = mysqli_fetch_array($rs_Detector);
        $DetectorId = $r_Detector['Id'];


        if($CityId=="E555"){
            if($r_V_Gitco['Reg_Genere_Infrazione']=="AUTOVELOX"){
                $a_Speed = explode("**", $r_V_Gitco['Reg_Dati_Infrazione']);


                $SpeedLimit = $a_Speed[1];
                $SpeedControl = $a_Speed[0];

                $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
                $Tolerance = ($TolerancePerc<5) ? 5 : $TolerancePerc;

                $Speed = $SpeedControl - $Tolerance;

            }


        } else if($CityId=="H416"){
            $a_Speed = explode("**", $r_V_Gitco['Reg_Dati_Infrazione']);

            if($r_V_Gitco['Reg_Velocita_Effettiva']>0){

                $Speed = $r_V_Gitco['Reg_Velocita_Effettiva'];
                $SpeedLimit = $a_Speed[1];
                $SpeedControl = $a_Speed[0];
            }else{
                $SpeedLimit = $a_Speed[1];
                $SpeedControl = $a_Speed[0];

                $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
                $Tolerance = ($TolerancePerc<5) ? 5 : $TolerancePerc;

                $Speed = $SpeedControl - $Tolerance;
            }



        } else{
            if ($r_V_Gitco['Reg_Velocita_Effettiva'] > 0) {
                $a_Speed = explode("**", $r_V_Gitco['Reg_Dati_Infrazione']);

                $Speed = $r_V_Gitco['Reg_Velocita_Effettiva'];
                $SpeedLimit = $a_Speed[1];
                $SpeedControl = $a_Speed[0];

            } else {
                if($r_V_Gitco['Reg_Genere_Infrazione']=="SEMAFORO"){
                    $a_TLight = explode("**", $r_V_Gitco['Reg_Dati_Infrazione']);

                    $TimeTLightFirst = $a_TLight[0];
                    $TimeTLightSecond = $a_TLight[1];

                }

            }

        }

    }


    $a_Address = explode(" ", $r_V_Gitco['Reg_Localita_Violazione']);
    $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]), $a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;


    $Code = $r_V_Gitco['Reg_Provenienza'];

    $FineDate = $r_V_Gitco['Reg_Data_Avviso'];
    $FineTime = $r_V_Gitco['Reg_Ora_Avviso'];
    $Locality = $CityId;
    $CityAddress = $r_V_Gitco['Reg_Localita_Violazione'];

    if(isset($a_Vehicle[$r_V_Gitco['Reg_Tipo_Veicolo']])) {
        $VehicleTypeId = $a_Vehicle[$r_V_Gitco['Reg_Tipo_Veicolo']];
    } else {
        $VehicleTypeId = 1;
    }


    $VehiclePlate = $r_V_Gitco['Reg_Targa_Veicolo'];
    $VehicleBrand = $r_V_Gitco['Reg_Marca_Veicolo'];
    $VehicleModel = $r_V_Gitco['Reg_Tipo_Veicolo'];
    $VehicleColor = $r_V_Gitco['Reg_Colore_Veicolo'];
    $VehicleMass = (is_numeric($r_V_Gitco['Reg_Massa_Veicolo'])) ? $r_V_Gitco['Reg_Massa_Veicolo'] : 0.00;
    $VehicleColor = $r_V_Gitco['Reg_Colore_Veicolo'];

    $Note = trim($r_V_Gitco['Reg_Note_Interne'] . ' ' . $r_V_Gitco['Reg_Note']);
    $RegDate = ($r_V_Gitco['Reg_Data_Registrazione']=="" || $r_V_Gitco['Reg_Data_Registrazione']='0000-00-00') ? Date('Y-m-d') : $r_V_Gitco['Reg_Data_Registrazione'];
    $RegTime = $r_V_Gitco['Reg_Ora_Registrazione'];
    $UserId = $r_V_Gitco['Reg_Operatore'];


    $rs_Fine = $rs->Select('Fine', "CityId='" . $CityId . "' AND FineDate='" . $FineDate . "' AND FineTime='" . $FineTime . "' AND VehiclePlate='" . $VehiclePlate. "'");


    if (mysqli_num_rows($rs_Fine) == 0) {
        $rs->Start_Transaction();

        $a_Fine = array(
            array('field' => 'Code', 'selector' => 'value', 'type' => 'str', 'value' => $Code),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
            array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolId, 'settype' => 'int'),
            array('field' => 'ProtocolYear', 'selector' => 'value', 'type' => 'year', 'value' => $ProtocolYear),
            array('field' => 'FineDate', 'selector' => 'value', 'type' => 'date', 'value' => $FineDate),
            array('field' => 'FineTime', 'selector' => 'value', 'type' => 'time', 'value' => $FineTime),
            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ControllerId, 'settype' => 'int'),
            array('field' => 'Locality', 'selector' => 'value', 'type' => 'str', 'value' => $Locality),
            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $CityAddress),
            array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value' => $ReasonId, 'settype' => 'int'),
            array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $VehicleTypeId, 'settype' => 'int'),
            array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
            array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel),
            array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand),
            array('field' => 'VehicleColor', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleColor),
            array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'flt', 'value' => $VehicleMass, 'settype'=> 'flt'),
            array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleCountry),
            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
            array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => $RegDate),
            array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => $RegTime),
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $UserId),
            array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),


            );

        $FineId = $rs->Insert('Fine', $a_Fine);
        $n_ContFine++;


        if (!is_dir($str_FolderFine . "/" . $CityId . "/" . $FineId)) {
            mkdir($str_FolderFine . "/" . $CityId . "/" . $FineId, 0777);
        }
        if(trim($r_V_Gitco['Reg_Documentazione'])!=""){

            if (!is_dir($str_FolderViolation . "/" .$CityId . "/" . $FineId)) {
                mkdir($str_FolderViolation . "/" . $CityId . "/" . $FineId, 0777);
            }



            $Doc = str_replace("***", "**", $r_V_Gitco['Reg_Documentazione']);

            $Doc = str_replace("Visure**", "", $r_V_Gitco['Reg_Documentazione']);
            $Doc = str_replace("VerbaleOriginario**", "", $Doc);
            $Doc = str_replace("Noleggio**", "", $Doc);
            $Doc = str_replace("NotificaOriginaria**", "", $Doc);
            $Doc = str_replace("Fotogramma**", "", $Doc);
            $Doc = str_replace("VerbaleEstero**", "", $Doc);
            $Doc = str_replace("NotificaEstera**", "", $Doc);
            $Doc = str_replace("Libretto**", "", $Doc);
            $Doc = str_replace("AIRE**", "", $Doc);
            $a_Doc = explode ("**", $Doc);

            for($i=0;$i<count($a_Doc);$i++){

                $FileName = $a_Doc[$i];
                if (! file_exists($DocumentPath . $r_V_Gitco['Reg_Data_Avviso']."/".$FileName)) {
                    if (strpos("JPG",$FileName)===false) $FileName = str_replace("jpg","JPG",$FileName);
                    else $FileName = str_replace("JPG","jpg",$FileName);


                }
                $DocumentationTypeId = 1;
                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$FileName),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);


                if(copy($DocumentPath . $r_V_Gitco['Reg_Data_Avviso'] . "/" . $FileName, $str_FolderViolation."/".$CityId."/".$FineId."/".$FileName)){

                    unlink($DocumentPath . $r_V_Gitco['Reg_Data_Avviso'] . "/" . $FileName);
                } else {
                    $b_FileSystemError = true;

                    $str_FileSystem .= $FineId.": Errore nel copiare il file ".$FileName."<br />";

                }
            }


        }
        if(trim($r_V_Gitco['Reg_Immagini'])!=""){

            if (!is_dir($str_FolderViolation . "/" . $CityId . "/" . $FineId)) {
                mkdir($str_FolderViolation . "/" . $CityId . "/" . $FineId, 0777);
            }


            $a_Photo = explode ("**", $r_V_Gitco['Reg_Immagini']);

            for($i=0;$i<count($a_Photo);$i++){
                $FileName = $a_Photo[$i];


                if (! file_exists($PhotoPath . $r_V_Gitco['Reg_Data_Avviso']."/".$FileName)) {

                    if (strpos("JPG",$FileName)===false) $FileName = str_replace("jpg","JPG",$FileName);
                    else $FileName = str_replace("JPG","jpg",$FileName);


                }
                $DocumentationTypeId = 1;
                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$FileName),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                );
                $rs->Insert('FineDocumentation',$a_FineDocumentation);


                if(copy($PhotoPath . $r_V_Gitco['Reg_Data_Avviso']."/".$FileName, $str_FolderViolation."/".$CityId."/".$FineId."/".$FileName)){

                    unlink($PhotoPath . $r_V_Gitco['Reg_Data_Avviso'] . "/" . $FileName);
                } else {
                    $b_FileSystemError = true;

                    $str_FileSystem .= $FineId.": Errore nel copiare il file ".$FileName."<br />";

                }

            }
        }

        
        $a_FineArticle = array(
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
            array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $ArticleId, 'settype' => 'int'),
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
            array('field' => 'ViolationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $ViolationTypeId, 'settype' => 'int'),
            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
            array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int'),
            array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedLimit, 'settype' => 'flt'),
            array('field' => 'SpeedControl', 'selector' => 'value', 'type' => 'flt', 'value' => $SpeedControl, 'settype' => 'flt'),
            array('field' => 'Speed', 'selector' => 'value', 'type' => 'flt', 'value' => $Speed, 'settype' => 'flt'),
            array('field' => 'TimeTLightFirst', 'selector' => 'value', 'type' => 'int', 'value' => $TimeTLightFirst, 'settype' => 'int'),
            array('field' => 'TimeTLightSecond', 'selector' => 'value', 'type' => 'int', 'value' => $TimeTLightSecond, 'settype' => 'int'),
        );

        $rs->Insert('FineArticle', $a_FineArticle);


        $TrespasserId = 0;
        $DataSourceId = 9;
        $n_Leasing = false;



        while ($r_V_GitcoNotifica = mysqli_fetch_array($rs_V_GitcoNotifica)) {

            $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$CityId."'");
            $Code = mysqli_fetch_array($rs_Code)['Code'];

            if ($r_V_GitcoNotifica['Genere'] == "D") {
                $StatusTypeId = 10;
                $rs_Trespasser = $rs->Select('Trespasser', "CompanyName = '" . addslashes($r_V_GitcoNotifica['Cognome']) . "' AND Address = '" . addslashes($r_V_GitcoNotifica['Indirizzo1']) . "' AND CountryId='" . $r_V_Gitco['CC_Paese_Estero'] . "'");
                if (mysqli_num_rows($rs_Trespasser) == 0) {
                    $rs_Country = $rs->Select('Country', "Id='" . $CountryId . "'");
                    $r_Country = mysqli_fetch_array($rs_Country);


                    $ZoneId = $r_Country['ZoneId'];
                    $LanguageId = $r_Country['LanguageId'];
                    if ($CountryId == "Z133" || $LanguageId == 0 || $LanguageId == "") $LanguageId = 1;

                    $str_City = $r_V_GitcoNotifica['Indirizzo2'] . " " . $r_V_GitcoNotifica['Indirizzo3'] . " " . $r_V_GitcoNotifica['Indirizzo4'] . " " . $r_V_GitcoNotifica['Indirizzo5'] . " " . $r_V_GitcoNotifica['Indirizzo6'];


                    $DataSourceDate = ($r_V_GitcoNotifica['Data_Registrazione']=='0000-00-00') ? date('Y-m-d') : $r_V_GitcoNotifica['Data_Registrazione'];

                    
                    $a_Trespasser = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                        
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Genere']),
                        array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Cognome']),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Indirizzo1']),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $str_City),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                    );
                    $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);


                } else {
                    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                    $TrespasserId = $r_Trespasser['Id'];
                }

            } else {
                $StatusTypeId = 10;
                $rs_Trespasser = $rs->Select('Trespasser', "Surname = '" . addslashes($r_V_GitcoNotifica['Cognome']) . "' AND Name = '" . addslashes($r_V_GitcoNotifica['Nome']) . "' AND Address = '" . addslashes($r_V_GitcoNotifica['Indirizzo1']) . "' AND CountryId='" . $r_V_Gitco['CC_Paese_Estero'] . "'");
                if (mysqli_num_rows($rs_Trespasser) == 0) {
                    $rs_Country = $rs->Select('Country', "Id='" . $CountryId . "'");
                    $r_Country = mysqli_fetch_array($rs_Country);


                    $ZoneId = $r_Country['ZoneId'];
                    $LanguageId = $r_Country['LanguageId'];
                    if ($CountryId == "Z133" || $LanguageId = 0 || $LanguageId = "") $LanguageId = 1;

                    $str_City = $r_V_GitcoNotifica['Indirizzo2'] . " " . $r_V_GitcoNotifica['Indirizzo3'] . " " . $r_V_GitcoNotifica['Indirizzo4'] . " " . $r_V_GitcoNotifica['Indirizzo5'] . " " . $r_V_GitcoNotifica['Indirizzo6'];
                    $DataSourceDate = ($r_V_GitcoNotifica['Data_Registrazione']=='0000-00-00') ? date('Y-m-d') : $r_V_GitcoNotifica['Data_Registrazione'];
                    $a_Trespasser = array(
                        array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                        
                        array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Genere']),
                        array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Cognome']),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Nome']),
                        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Indirizzo1']),
                        array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $str_City),
                        array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                        array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                        array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                        array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                        array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                    );
                    $TrespasserId = $rs->Insert('Trespasser', $a_Trespasser);

                } else {
                    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                    $TrespasserId = $r_Trespasser['Id'];
                }
            }


            if ($r_V_GitcoNotifica['Tipo_Trasgressore'] == "NOLEGGIO") {
                $TrespasserTypeId = 10;
                $n_Leasing = true;

            } else if ($r_V_GitcoNotifica['Tipo_Trasgressore'] == "COINCIDENTE" && $n_Leasing) {
                $TrespasserTypeId = 11;
            } else {
                $TrespasserTypeId = 1;
            }

            if ($r_V_GitcoNotifica['Tipo_Trasgressore'] != "TRASGRESSORE") {

                $a_FineTrespasser = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                );

                if($TrespasserTypeId==10){
                    $ReceiveDate = $r_V_GitcoNotifica['Data_Comunicazione_Noleggio'] ;
                }
                if($TrespasserTypeId==11){
                    if($ReceiveDate != "" && $ReceiveDate != "0000-00-00"){
                        array_push($a_FineTrespasser,array('field'=>'ReceiveDate','selector'=>'value','type'=>'date','value'=>$ReceiveDate));
                        $ReceiveDate = "";
                    }
                }




                $rs->Insert('FineTrespasser', $a_FineTrespasser);
            }


            if ($r_V_GitcoNotifica['Tipo_Trasgressore'] != "NOLEGGIO" && $r_V_GitcoNotifica['Tipo_Trasgressore'] != "TRASGRESSORE") {
                $a_Fine = array(
                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                );


                $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);


                if ($r_V_Gitco['Reg_Progr_Registro'] > 0) {


                    $ProtocolId = $r_V_Gitco['Reg_Progr_Registro'];


                    $ChiefController = $a_Controller[$r_V_Gitco['Acc_Verbalizzante']];


                    $CustomerNotificationFee = ($r_V_GitcoNotifica['Spese_Notifiche_Comune'] > 0) ? $r_V_GitcoNotifica['Spese_Notifiche_Comune'] : 0.00;
                    $CustomerResearchFee = ($r_V_GitcoNotifica['Spese_Ricerche_Comune'] > 0) ? $r_V_GitcoNotifica['Spese_Ricerche_Comune'] : 0.00;
                    $NotificationFee = ($r_V_GitcoNotifica['Spese_Notifiche_Sarida'] > 0) ? $r_V_GitcoNotifica['Spese_Notifiche_Sarida'] : 0.00;
                    $ResearchFee = ($r_V_GitcoNotifica['Spese_Ricerche_Sarida'] > 0) ? $r_V_GitcoNotifica['Spese_Ricerche_Sarida'] : 0.00;

                    $NotificationDate = ($r_V_Gitco['Reg_Data_Verbalizzazione']=="0000-00-00") ? "" : $r_V_Gitco['Reg_Data_Verbalizzazione'];


                    $StatusTypeId = 15;
                    $NotificationTypeId = 2;

                    $aInsert = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                        array('field' => 'CustomerNotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerNotificationFee, 'settype' => 'flt'),
                        array('field' => 'CustomerResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerResearchFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefController, 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                    );
                    $rs->Insert('FineHistory', $aInsert);

                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                        array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolId, 'settype' => 'int'),
                    );
                    $rs->Update('Fine', $aUpdate, 'Id=' . $FineId);

                }
                if ($r_V_GitcoNotifica['Data_Creazione_Flusso'] != "0000-00-00" && $r_V_GitcoNotifica['Data_Creazione_Flusso'] != "") {



                    $rs_Flow = $rs->Select('gitco2.flussi_tabella', "Tipo='V_EST' AND CC_Comune='".$CityId."' AND Anno=".$ProtocolYear." AND Num_Flusso=" . $r_V_GitcoNotifica['Numero_Flusso']);
                    if(mysqli_num_rows($rs_Flow)==1){
                        $r_Flow = mysqli_fetch_array($rs_Flow);
                        $Documentation = $r_Flow['Nome_Flusso_Rar'];
                    }else{
                        $Documentation= "";
                    }


                    $StatusTypeId = 20;
                    $NotificationTypeId = 6;

                    $NotificationDate = ($r_V_GitcoNotifica['Data_Stampa_Notifica']=="0000-00-00") ? "" : $r_V_GitcoNotifica['Data_Stampa_Notifica'];
                    $PrintDate = $r_V_GitcoNotifica['Data_Stampa_Notifica'];
                    $FlowDate = $r_V_GitcoNotifica['Data_Creazione_Flusso'];
                    $FlowNumber = $r_V_GitcoNotifica['Numero_Flusso'];

                    $aInsert = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                        array('field' => 'CustomerNotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerNotificationFee, 'settype' => 'flt'),
                        array('field' => 'CustomerResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerResearchFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefController, 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $PrintDate),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $FlowDate),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $FlowNumber, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                    );
                    $rs->Insert('FineHistory', $aInsert);

                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int')
                    );
                    $rs->Update('Fine', $aUpdate, 'Id=' . $FineId);















                    $rs_Flow = $rs->Select('gitco2.notifiche_importate', "Riferimento=" . $r_V_GitcoNotifica['ID']. " AND Tipo_Atto='VERBALIESTERI' AND CC_Comune='".$CityId."'","Tipo_Notifica");

                    if (mysqli_num_rows($rs_Flow) > 0) {
                        $r_Flow = mysqli_fetch_array($rs_Flow);


                        if (trim($r_Flow['Tipo_Notifica']) == "01 - AR") {
                            $ResultId = 1;
                        } else if (isset($a_Notification[$r_Flow['Stato_Notifica']])) {
                            if(isset($a_Notification[$r_Flow['Tipo_Notifica']])){
                                $NotificationStatus = $a_Notification[$r_Flow['Tipo_Notifica']];

                                $ResultId = $a_Result[$NotificationStatus];
                            }else{
                                $ResultId = 19;
                            }
                        } else {
                            $ResultId = 19;
                        }


                        $SendDate = ($r_Flow['Data_Spedizione']=="0000-00-00") ? "" : $r_Flow['Data_Spedizione'];
                        if($SendDate == ""){

                            $SendDate =  date('Y-m-d',strtotime($FlowDate.' + 5 days'));

                        }

                        $Note = $r_Flow['Note'];

                        $LogDate = ($r_Flow['Log_Modificato_Data'] == "0000-00-00" || $r_Flow['Log_Modificato_Data'] == "") ? date("Y-m-d") : $r_Flow['Log_Modificato_Data'];
                        $Box = $r_Flow['Scatola'];
                        $Lot = $r_Flow['Lotto'];
                        $Position = $r_Flow['Posizione'];

                        $LetterNumber = $r_Flow['Ms_Rac_Num'];
                        $ReceiptNumber = "";


                        $RegDate = ($r_Flow['Data_Importazione']=="" || $r_Flow['Data_Importazione']=="0000-00-00" ) ? Date('Y-m-d') : $r_Flow['Data_Importazione'];


                        $RegTime = date("H:i");
                        $UserId = $r_Flow['Operatore'];


                        $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $FineId);
                        $r_Tariff = mysqli_fetch_array($rs_Tariff);


                        $LicensePointProcedure = $r_Tariff['LicensePoint'];
                        $PresentationDocumentProcedure = $r_Tariff['PresentationDocument'];
                        $BisProcedure = $r_Tariff['126Bis'];
                        $HabitualProcedure = $r_Tariff['Habitual'];
                        $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                        $LossLicenseProcedure = $r_Tariff['LossLicense'];


                        $a_FineNotification = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                            array('field' => 'LogDate', 'selector' => 'value', 'type' => 'date', 'value' => $LogDate),
                            array('field' => 'Box', 'selector' => 'value', 'type' => 'str', 'value' => $Box),
                            array('field' => 'Lot', 'selector' => 'value', 'type' => 'str', 'value' => $Lot),
                            array('field' => 'Position', 'selector' => 'value', 'type' => 'str', 'value' => $Position),
                            array('field' => 'ReceiptNumber', 'selector' => 'value', 'type' => 'str', 'value' => $ReceiptNumber),
                            array('field' => 'LetterNumber', 'selector' => 'value', 'type' => 'str', 'value' => $LetterNumber),
                            array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int'),
                            array('field' => '126BisProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $BisProcedure, 'settype' => 'int'),
                            array('field' => 'PresentationDocumentProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $PresentationDocumentProcedure, 'settype' => 'int'),
                            array('field' => 'LicensePointProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $LicensePointProcedure, 'settype' => 'int'),
                            array('field' => 'HabitualProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $HabitualProcedure, 'settype' => 'int'),
                            array('field' => 'SuspensionLicenseProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $SuspensionLicenseProcedure, 'settype' => 'int'),
                            array('field' => 'LossLicenseProcedure', 'selector' => 'value', 'type' => 'int', 'value' => $LossLicenseProcedure, 'settype' => 'int'),
                            array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => $RegDate),
                            array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => $RegTime),
                            array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $UserId),
                            //Bug3309 si aggiungono le impostazioni alle 3 seguenti voci che precedentemente erano lasciate di default
                            array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
                            array('field'=>'ReminderAdditionalFeeProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                            array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
                        );


                        $a_FineHistory = array(
                            array('field' => 'ResultId', 'selector' => 'value', 'type' => 'int', 'value' => $ResultId, 'settype' => 'int'),
                            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate, 'settype' => 'date'),
                        );


                        if ($ResultId < 10) {
                            $StatusTypeId = 25;
                            $DeliveryDate = ($r_Flow['Data_Notifica']=='0000-00-00') ? "" : $r_Flow['Data_Notifica'];


                            array_push($a_FineNotification, array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $DeliveryDate));
                            array_push($a_FineHistory, array('field' => 'DeliveryDate', 'selector' => 'value', 'type' => 'date', 'value' => $DeliveryDate));

                        } else {
                            $StatusTypeId = 23;
                        }


                        $a_Fine = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                        );


                        $rs->Update('Fine', $a_Fine, "Id=" . $FineId);

                        $rs->Insert('FineNotification', $a_FineNotification);

                        $rs->Update('FineHistory', $a_FineHistory, "FineId=" . $FineId . " AND NotificationTypeId=6");



                        if(trim($r_Flow['Immagine_Fronte'])!=""){

                            $FileName = $r_Flow['Immagine_Fronte'];


                            if (! file_exists($NotificationPath .$FileName)) {

                                if (strpos("JPG",$FileName)===false) $FileName = str_replace("jpg","JPG",$FileName);
                                else $FileName = str_replace("JPG","jpg",$FileName);

                            }


                            if (file_exists($NotificationPath .$FileName)) {

                                $DocumentationTypeId = 10;
                                $a_FineDocumentation = array(
                                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$FileName),
                                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                                );
                                $rs->Insert('FineDocumentation',$a_FineDocumentation);


                                if(copy($NotificationPath .$FileName, $str_FolderFine."/".$CityId."/".$FineId."/".$FileName)){

                                    unlink($NotificationPath .$FileName);
                                } else {
                                    $b_FileSystemError = true;

                                    $str_FileSystem .= $FineId.": Errore nel copiare il file ".$FileName."<br />";

                                }


                            }




                        }
                        if(trim($r_Flow['Immagine_Retro'])!=""){

                            $FileName = $r_Flow['Immagine_Retro'];


                            if (! file_exists($NotificationPath .$FileName)) {

                                if (strpos("JPG",$FileName)===false) $FileName = str_replace("jpg","JPG",$FileName);
                                else $FileName = str_replace("JPG","jpg",$FileName);

                            }

                            if (file_exists($NotificationPath .$FileName)) {
                                $DocumentationTypeId = 11;
                                $a_FineDocumentation = array(
                                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$FileName),
                                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                                );
                                $rs->Insert('FineDocumentation',$a_FineDocumentation);

                                if(copy($NotificationPath .$FileName, $str_FolderFine."/".$CityId."/".$FineId."/".$FileName)){

                                    unlink($NotificationPath .$FileName);
                                } else {
                                    $b_FileSystemError = true;

                                    $str_FileSystem .= $FineId.": Errore nel copiare il file ".$FileName."<br />";

                                }

                            }
                        }

                    } else if($r_V_GitcoNotifica['Numero_Flusso']==0 AND $r_V_GitcoNotifica['Data_Creazione_Flusso']!="" AND $r_V_GitcoNotifica['Data_Creazione_Flusso']!="0000-00-00"){
                        $SendDate = $r_V_GitcoNotifica['Data_Creazione_Flusso'];
                        $a_FineHistory = array(

                            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate, 'settype' => 'date'),
                        );


                        $rs->Update('FineHistory', $a_FineHistory, "FineId=" . $FineId . " AND NotificationTypeId=6");

                    }

                }




            }




            if ($r_V_GitcoNotifica['Data_Comunicazione_Dati'] != "" && $r_V_GitcoNotifica['Data_Comunicazione_Dati'] != "0000-00-00" && $r_V_GitcoNotifica['Tipo_Trasgressore'] != "NOLEGGIO") {

                $TrespasserTypeId = $a_TrespasserTypeId[$r_V_GitcoNotifica['Tipo_Trasgressore']];
                $CommunicationDate = $r_V_GitcoNotifica['Data_Comunicazione_Dati'];
                $RegCommunicationDate = $r_V_GitcoNotifica['Data_Registrazione_Comunicazione'];
                $RegCommunicationTime = date("H:i");
                $RegCommunicationUser = $r_V_GitcoNotifica['Operatore_Comunicazione'];

                if($RegCommunicationUser == "") $RegCommunicationUser = $_SESSION['username'];

                $a_FineCommunication = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserTypeId, 'settype' => 'int'),
                    array('field' => 'CommunicationDate', 'selector' => 'value', 'type' => 'date', 'value' => $CommunicationDate),
                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => $RegCommunicationDate),
                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => $RegCommunicationTime),
                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $RegCommunicationUser),
                );
                $rs->Insert('FineCommunication', $a_FineCommunication);

                $a_Trespasser = array(
                    array('field' => 'LicenseNumber', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Numero_Patente']),
                    array('field' => 'LicenseDate', 'selector' => 'value', 'type' => 'date', 'value' => $r_V_GitcoNotifica['Data_Rilascio_Patente']),
                    array('field' => 'LicenseCategory', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Categoria_Patente']),
                    array('field' => 'LicenseOffice', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_GitcoNotifica['Autorita_Patente']." ".$r_V_GitcoNotifica['Stato_Patente']),
                );

                $rs->Update('Trespasser', $a_Trespasser, "Id=".$TrespasserId);




                $a_FineNotification = array(
                    array('field' => '126BisProcedure', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),

                );
                $rs->Update('FineNotification', $a_FineNotification, 'FineId=' . $FineId);


            }
        }





        if ($r_V_Gitco['Reg_Data_Annullamento'] != "0000-00-00" && $r_V_Gitco['Reg_Data_Annullamento']!="") {

            $ArchiveDate = $r_V_Gitco['Reg_Data_Annullamento'];
            $ArchiveRegDate = $r_V_Gitco['Reg_Data_Annullamento'];
            $ArchiveRegTime = date("H:i");
            $StatusTypeId = 35;
            $ArchiveNote = $r_V_Gitco['Reg_Motivo_Annullamento'];
            $ReasonId = (isset($a_ReasonArchive[trim($r_V_Gitco['Reg_Motivo_Annullamento'])])) ? $a_ReasonArchive[trim($r_V_Gitco['Reg_Motivo_Annullamento'])] : 15;


            $aFine = array(
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
            );

            $rs->Update('Fine', $aFine, 'Id=' . $FineId);


            $a_FineArchive = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'ReasonId', 'selector' => 'value', 'type' => 'int', 'value'=> $ReasonId, 'settype' => 'int'),
                array('field' => 'ArchiveDate', 'selector' => 'value', 'type' => 'date', 'value' => $ArchiveDate),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $ArchiveNote),
                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => $ArchiveRegDate),
                array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => $ArchiveRegTime),
                array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),

            );
            $rs->Insert('FineArchive', $a_FineArchive);
        }


        if ($r_V_Gitco['Reg_Data_Esecuzione_Impossibile'] != "0000-00-00" && $r_V_Gitco['Reg_Data_Esecuzione_Impossibile']!="") {

            $StatusTypeId = 35;

            $aFine = array(
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $r_V_Gitco['Reg_Motivi_Esecuzione_Impossibile']),
            );

            $rs->Update('Fine', $aFine, 'Id=' . $FineId);
        }

        $rs_Payment = $rs->Select('gitco2.targhe_estere_pagamenti', "Pag_Registro = ".$r_V_Gitco['Reg_Progr']);
        while ($r_Payment = mysqli_fetch_array($rs_Payment)) {

            $PaymentName = ($r_Payment['Pag_Trasgressore'] != "") ? $r_Payment['Pag_Trasgressore'] : $r_V_GitcoNotifica['Cognome'] . " " . $r_V_GitcoNotifica['Nome'];
            $BankMgmt = ($r_Payment['Pag_Riscossore'] == "SARIDA") ? 1 : 0;
            $PaymentTypeId = $a_PaymentTypeId[$r_Payment['Pag_Tipo_Pag']];
            $PaymentDate = $r_Payment['Pag_Data_Pag'];
            $Amount = $r_Payment['Pag_Importo_Pag'];
            $PaymentNote = trim($r_Payment['Pag_Note'] . " " . $r_Payment['Pag_Blocco_Riscossione']);
            $PaymentRegDate = $r_Payment['Pag_Data_Reg'];
            $PaymentRegTime = $r_Payment['Pag_Ora_Registrazione'];
            $PaymentUser = $r_Payment['Pag_Operatore'];

            $a_Payment = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentName),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
                array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentNote),
                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentRegDate),
                array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentRegTime),
                array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $PaymentUser),
            );


            $rs->Insert('FinePayment', $a_Payment);

        }







        $rs->Delete("gitco2.registro_cronologico_cds", "Reg_Progr=".$r_V_Gitco['Reg_Progr']);



        $rs->End_Transaction();

        if($b_FileSystemError){
            echo $str_FileSystem;
            DIE;
        }


    }

}


if ($n_ContFine > 0) {


    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM " . MAIN_DB . ".V_UserCity WHERE UserLevel>=3 AND CityId='" . $CityId . "'");
    while ($r_UserMail = mysqli_fetch_array($rs_UserMail)) {

        $str_Content = $r_UserMail['CityTitle'] . ": sono state inserite n. " . $n_ContFine . " violazioni.";

        $a_Mail = array(
            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
            array('field' => 'SendTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i:s")),
            array('field' => 'Object', 'selector' => 'value', 'type' => 'str', 'value' => "Nuova importazione"),
            array('field' => 'Content', 'selector' => 'value', 'type' => 'str', 'value' => $str_Content),
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_UserMail['UserId'], 'settype' => 'int'),
            array('field' => 'Sender', 'selector' => 'value', 'type' => 'str', 'value' => "Server"),
        );
        $rs->Start_Transaction();
        $rs->Insert('Mail', $a_Mail);
        $rs->End_Transaction();
    }

}

header("location: imp_gitco.php");