<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");

include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');




ini_set('max_execution_time', 0);

$FileList = "";
$Cont = 0;



$error = false;
$msg_Problem = "";
$CityId = "C933";
$ProtocolYear = 2015;





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


$n_Cont = 0;


$a_Controller = array();
$rs_Controller = $rs->Select('Controller', "CityId='".$CityId."'");

while($r_Controller = mysqli_fetch_array($rs_Controller)) {

    $a_Controller[$r_Controller['Name']] = $r_Controller['Id'];

}







$rs_StreetType = $rs->Select('StreetType', "Disabled=0");
$a_Street_Type = array();
while ($r_StreetType = mysqli_fetch_array($rs_StreetType)){
    $a_Street_Type[$r_StreetType['Title']] = $r_StreetType['Id'];
}


$rs_ReasonArchive = $rs->Select('Reason', "ReasonTypeId=2");
$a_ReasonArchive = array();
while ($r_ReasonArchive = mysqli_fetch_array($rs_ReasonArchive)){
    $a_ReasonArchive[$r_ReasonArchive['TitleIta']] = $r_ReasonArchive['Id'];
}



$DocumentPath = PUBLIC_FOLDER . "/C933/DocsTargheEstere/".$CityId."/";
$PhotoPath = PUBLIC_FOLDER . "/C933/FotoTargheEstere/".$CityId."/";
$NotificationPath = PUBLIC_FOLDER . "/C933/Notifiche/".$CityId."/";




$rs= new CLS_DB();
$rs->SetCharset('utf8');





$str_out = '
    <div class="col-sm-12">
        <div class="table_label_H col-sm-1">Img Riga</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Controller</div>
        <div class="table_label_H col-sm-2">Località</div>
        <div class="table_label_H col-sm-1">Veicolo</div>
        <div class="table_label_H col-sm-1">Targa</div>
        <div class="table_label_H col-sm-2">Marca</div>
        <div class="table_label_H col-sm-1">Nazione</div>
        <div class="table_label_H col-sm-1">Art</div>

        
        <div class="clean_row HSpace4"></div>	
    </div>
    ';

$rs_V_Gitco = $rs->Select('gitco2.V_Gitco', "Reg_Comune_Violazione='".$CityId."' AND Reg_Anno=".$ProtocolYear." AND Reg_Progr NOT IN(SELECT Reg_Progr_Provenienza Reg_Progr FROM gitco2.registro_cronologico_cds)", "Reg_Progr_Registro LIMIT 500");
//$rs_V_Gitco = $rs->Select('gitco2.V_Gitco', "Reg_Comune_Violazione='".$CityId."' AND Reg_Anno=".$ProtocolYear." AND Reg_Progr=14181", "Reg_Progr_Registro DESC LIMIT 5");

while($r_V_Gitco = mysqli_fetch_array($rs_V_Gitco)){
    $n_Cont++;
    $chk_Fine = "";

    $ControllerId = 0;
    $ChiefController = 0;
    if(isset($a_Controller[$r_V_Gitco['Acc_Accertatore']])){
        $ControllerId = $a_Controller[$r_V_Gitco['Acc_Accertatore']];
    } else {
        $ControllerId = 225;

        $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
        $msg_Problem .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                <div class="table_caption_H col-sm-11 alert-danger">Accertatore '.$r_V_Gitco['Acc_Accertatore'].' non presente</div>
                <div class="clean_row HSpace4"></div>
            </div>
            ';

    }


    $StatusTypeId = 1;
    $VehicleCountry = "Da assegnare";
    $CountryId = "ZZZZ";



    if($r_V_Gitco['Reg_Progr_Registro']>0){






        $ProtocolId = $r_V_Gitco['Reg_Progr_Registro'];


        if(isset($a_Controller[$r_V_Gitco['Acc_Verbalizzante']])){
            $ChiefController = $a_Controller[$r_V_Gitco['Acc_Verbalizzante']];
        } else {
            $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $msg_Problem .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                <div class="table_caption_H col-sm-11 alert-danger">verbalizzante '.$r_V_Gitco['Acc_Verbalizzante'].' non presente</div>
                <div class="clean_row HSpace4"></div>
            </div>
            ';
        }

    } else {
        $ProtocolId = 0;



/*
    $StatusTypeId = 15;

    $PreviousId`,
*/
    }



    if(strlen(trim($r_V_Gitco['Reg_Ente_Per_Richiesta']))>0){
        $VehicleCountry = $r_V_Gitco['Tar_Nazione_Nome'];
        $CountryId = $r_V_Gitco['CC_Paese_Estero'];
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



        $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
        $msg_Problem .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                <div class="table_caption_H col-sm-11 alert-danger">Articolo '.$Id1.' '.$Id2.' '.$Id3.'/'.$ProtocolYear.' non presente</div>
                <div class="clean_row HSpace4"></div>
            </div>
            ';

    }else{
        $r_Article = mysqli_fetch_array($rs_Article);
        $ArticleId = $r_Article['Id'];
        $ViolationTypeId = $r_Article['ViolationTypeId'];


        $rs_Reasons = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=".$ViolationTypeId." AND Disabled=0 AND CityId='".$CityId."'");
        $n_rsNumber = mysqli_num_rows($rs_Reasons);

        if($n_rsNumber==0){
            $str_ChkLocality = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $msg_Problem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">'.$n_Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Contestazione non presente per quest\'articolo </div>
                        <div class="clean_row HSpace4"></div>
                    </div>    
                    ';
        }else{
            $rs_Reason = mysqli_fetch_array($rs_Reasons);
            $ReasonId = $rs_Reason['Id'];
        }





        $str_Article = $r_Article['Id1'].' '.$r_Article['Id2'].' '.$r_Article['Id3'];

        $Fee = $r_V_Gitco['Reg_Importo_Amministrativo'];
        $MaxFee = $r_V_Gitco['Reg_Importo_Sanzione_Massima'];


        $DetectorId = 0;

        $Speed = 0;
        $SpeedLimit = 0;
        $SpeedControl = 0;

        $TimeTLightFirst = 0;
        $TimeTLightSecond = 0;

        if($r_V_Gitco['Reg_Rilevatore_Elettronico']>0){
            $rs_Detector = $rs->Select('Detector', "Code='".$r_V_Gitco['Ril_Matricola_Sistema']."' AND CityId='".$CityId."'");

            if(mysqli_num_rows($rs_Detector)==0){
                $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $msg_Problem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Rilevatore '.$r_V_Gitco['Ril_Matricola_Sistema'].' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                    ';
            } else {
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
                    if($r_V_Gitco['Reg_Velocita_Effettiva']>0){
                        $a_Speed = explode("**",$r_V_Gitco['Reg_Dati_Infrazione']);

                        $Speed = $r_V_Gitco['Reg_Velocita_Effettiva'];
                        $SpeedLimit = $a_Speed[0];
                        $SpeedControl = $a_Speed[1];

                    } else {
                        if($r_V_Gitco['Reg_Genere_Infrazione']=="SEMAFORO"){
                            $a_TLight = explode("**", $r_V_Gitco['Reg_Dati_Infrazione']);

                            $TimeTLightFirst = $a_TLight[0];
                            $TimeTLightSecond = $a_TLight[1];

                            if(($TimeTLightSecond-$TimeTLightFirst-15)!=0){
                                $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                                $msg_Problem .= '
                        <div class="col-sm-12">
                        <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                            <div class="table_caption_H col-sm-11 alert-danger">Fotogrammi ' . $TimeTLightFirst .'/'. $TimeTLightSecond.' non uguali a 15</div>
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';
                            }

                        }





                    }
                }

            }

        }

    }




    if ($r_V_Gitco['Reg_Data_Annullamento'] != "0000-00-00" && $r_V_Gitco['Reg_Data_Annullamento']!="") {

        if( ! isset($a_ReasonArchive[trim($r_V_Gitco['Reg_Motivo_Annullamento'])])){
            $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
            $msg_Problem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Motivo annullamento ' . $r_V_Gitco['Reg_Motivo_Annullamento'] .' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                    ';
        }


    }






    $TrespasserId = "Non presente";
    $rs_V_GitcoNotifica = $rs->Select('gitco2.V_GitcoNotifica', "Riferimento_Notifica=".$r_V_Gitco['Reg_Progr'],"Tipo_Trasgressore DESC");
    while($r_V_GitcoNotifica = mysqli_fetch_array($rs_V_GitcoNotifica)){
        $TrespasserId = 0;
        if($r_V_GitcoNotifica['Genere']=="D"){
            $StatusTypeId = 10;
            $rs_Trespasser = $rs->Select('Trespasser', "CompanyName = '".addslashes($r_V_GitcoNotifica['Cognome'])."' AND Address = '".addslashes($r_V_GitcoNotifica['Indirizzo1'])."' AND CountryId='".$r_V_Gitco['CC_Paese_Estero']."'");
            if(mysqli_num_rows($rs_Trespasser)==0){
                $TrespasserId = "INSERT";
                /*

                    `Trespasser`.`Address`,         `targhe_estere_utenti`.`Indirizzo1`, + `targhe_estere_utenti`.`Indirizzo2` + `targhe_estere_utenti`.`Indirizzo3`,
                    `Trespasser`.`ZIP`,             `targhe_estere_utenti`.`Indirizzo4`,
                    `Trespasser`.`City`,            `targhe_estere_utenti`.`Indirizzo5`, +`targhe_estere_utenti`.`Indirizzo6`,

                */
            }else if(mysqli_num_rows($rs_Trespasser)>1){
                $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $msg_Problem .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                <div class="table_caption_H col-sm-11 alert-danger">Più trasgressori '.$r_V_GitcoNotifica['Cognome'].' '.$r_V_Gitco['CC_Paese_Estero'].' presenti</div>
                <div class="clean_row HSpace4"></div>
            </div>
            ';
            }else{
                $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                $TrespasserId = $r_Trespasser['Id'];
            }

        } else if($r_V_GitcoNotifica['Genere']=="M" || $r_V_GitcoNotifica['Genere']=="F"){
            $StatusTypeId = 10;
            $rs_Trespasser = $rs->Select('Trespasser', "Surname = '".addslashes($r_V_GitcoNotifica['Cognome'])."' AND Name = '".addslashes($r_V_GitcoNotifica['Nome'])."' AND Address = '".addslashes($r_V_GitcoNotifica['Indirizzo1'])."' AND CountryId='".$r_V_Gitco['CC_Paese_Estero']."'");
            if(mysqli_num_rows($rs_Trespasser)==0){
                $TrespasserId = "INSERT";
                /*

                    `Trespasser`.`Address`,         `targhe_estere_utenti`.`Indirizzo1`, + `targhe_estere_utenti`.`Indirizzo2` + `targhe_estere_utenti`.`Indirizzo3`,
                    `Trespasser`.`ZIP`,             `targhe_estere_utenti`.`Indirizzo4`,
                    `Trespasser`.`City`,            `targhe_estere_utenti`.`Indirizzo5`, +`targhe_estere_utenti`.`Indirizzo6`,

                */

            }else if(mysqli_num_rows($rs_Trespasser)>1){
                $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                $msg_Problem .= '
            <div class="col-sm-12">
            <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                <div class="table_caption_H col-sm-11 alert-danger">Più trasgressori '.$r_V_GitcoNotifica['Cognome'].' '.$r_V_GitcoNotifica['Nome']. ' '.$r_V_Gitco['CC_Paese_Estero'].' presenti</div>
                <div class="clean_row HSpace4"></div>
            </div>
            ';
            }else{
                $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                $TrespasserId = $r_Trespasser['Id'];
            }
        } else{
            $TrespasserId =  "Non presente";
        }

    }






    $a_Address = explode(" ",$r_V_Gitco['Reg_Localita_Violazione']);
    $StreetTypeId = (array_key_exists(strtoupper($a_Address[0]),$a_Street_Type)) ? $a_Street_Type[strtoupper($a_Address[0])] : 0;





    $Code = $r_V_Gitco['Reg_Provenienza'];


    $FineDate = $r_V_Gitco['Reg_Data_Avviso'];
    $FineTime = $r_V_Gitco['Reg_Ora_Avviso'];

    $Locality = $CityId;
    $CityAddress = $r_V_Gitco['Reg_Localita_Violazione'];


    if(isset($a_Vehicle[$r_V_Gitco['Reg_Tipo_Veicolo']])) {
        $VehicleTypeId = $a_Vehicle[$r_V_Gitco['Reg_Tipo_Veicolo']];


    } else {
        $VehicleTypeId = 1;

        $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
        $msg_Problem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">Veicolo ' . $r_V_Gitco['Reg_Tipo_Veicolo'] . ' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                    ';


    }




    $VehiclePlate = $r_V_Gitco['Reg_Targa_Veicolo'];
    $VehicleBrand = $r_V_Gitco['Reg_Marca_Veicolo'];
    $VehicleModel = $r_V_Gitco['Reg_Tipo_Veicolo'];
    $VehicleColor = $r_V_Gitco['Reg_Colore_Veicolo'];
    $VehicleMass = $r_V_Gitco['Reg_Massa_Veicolo'];
    $VehicleColor = $r_V_Gitco['Reg_Colore_Veicolo'];

    $Note = trim($r_V_Gitco['Reg_Note_Interne'] . ' '. $r_V_Gitco['Reg_Note']);
    $RegDate = $r_V_Gitco['Reg_Data_Registrazione'];
    $RegTime = $r_V_Gitco['Reg_Ora_Registrazione'];
    $UserId = $r_V_Gitco['Reg_Operatore'];



    if(trim($r_V_Gitco['Reg_Documentazione'])!=""){

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

                if (! file_exists($DocumentPath . $r_V_Gitco['Reg_Data_Avviso']."/".$FileName)) {
                    $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msg_Problem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">File '.$DocumentPath . $r_V_Gitco['Reg_Data_Avviso']."/".$a_Doc[$i].' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                    ';

                }
            }
        }


    }
    if(trim($r_V_Gitco['Reg_Immagini'])!=""){
        $a_Photo = explode ("**", $r_V_Gitco['Reg_Immagini']);

        for($i=0;$i<count($a_Photo);$i++){
            $FileName = $a_Photo[$i];


            if (! file_exists($PhotoPath . $r_V_Gitco['Reg_Data_Avviso']."/".$FileName)) {

                if (strpos("JPG",$FileName)===false) $FileName = str_replace("jpg","JPG",$FileName);
                else $FileName = str_replace("JPG","jpg",$FileName);
                if (! file_exists($DocumentPath . $r_V_Gitco['Reg_Data_Avviso']."/".$FileName)) {
                    $chk_Fine = '<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i>';
                    $msg_Problem .= '
                    <div class="col-sm-12">
                    <div class="table_caption_H col-sm-1 alert-danger">' . $n_Cont . '</div>
                        <div class="table_caption_H col-sm-11 alert-danger">File '.$PhotoPath . $r_V_Gitco['Reg_Data_Avviso']."/".$a_Photo[$i].' non presente</div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                    ';

                }
            }
        }
    }


    $str_out .= '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-1">'.$chk_Fine.' ('.$n_Cont.') '.$r_V_Gitco['Reg_Progr_Registro'].'</div>
            <div class="table_caption_H col-sm-1">' . $r_V_Gitco['Reg_Data_Avviso'] . '</div>
            <div class="table_caption_H col-sm-1">' . $r_V_Gitco['Reg_Ora_Avviso'] . '</div>
            <div class="table_caption_H col-sm-1">' . $ControllerId . ' - '.$ChiefController.'</div>
     
            <div class="table_caption_H col-sm-2">' . $r_V_Gitco['Reg_Localita_Violazione'] . '</div>
            <div class="table_caption_H col-sm-1">' . $VehicleTypeId . '</div>
            <div class="table_caption_H col-sm-1">' . $r_V_Gitco['Reg_Targa_Veicolo'] . '</div>
            <div class="table_caption_H col-sm-2">' . $r_V_Gitco['Reg_Marca_Veicolo']  . '</div>
   
            <div class="table_caption_H col-sm-1">' . $VehicleCountry . '</div>
            <div class="table_caption_H col-sm-1">' . $str_Article . '</div>
   
        
            <div class="table_caption_H col-sm-2">
                ' . $r_V_GitcoNotifica['Spese_Notifiche_Comune'] . ' 
                ' . $r_V_GitcoNotifica['Spese_Ricerche_Comune'] . ' 
                ' . $r_V_GitcoNotifica['Spese_Notifiche_Sarida'] . ' 
                ' . $r_V_GitcoNotifica['Spese_Ricerche_Sarida'] . '
            </div>

            <div class="table_caption_H col-sm-2">
                ' . $r_V_Gitco['Reg_Data_Verbalizzazione'] . ' 
                ' . $r_V_GitcoNotifica['Data_Stampa_Notifica'] . ' 
            </div>

            <div class="table_caption_H col-sm-2">Flusso
                ' . $r_V_GitcoNotifica['Numero_Flusso'] . ' del 
                ' . $r_V_GitcoNotifica['Data_Creazione_Flusso'] . ' 

            </div>
            <div class="table_caption_H col-sm-1">
                Notifica: ' . $r_V_GitcoNotifica['Data_Notifica'] . '
            </div>
            <div class="table_caption_H col-sm-1">
                Stato:
            </div>
             <div class="table_caption_H col-sm-4">
                Trasgressore: ' . $TrespasserId . '
            </div>
   
   
   
            <div class="clean_row HSpace4"></div>	
        </div>
        
        ';



}





$str_out .= '
        <div class="col-sm-12">
            <form name="f_import" action="imp_gitco_exe.php">
            <input type="hidden" name="CityId" value="' . $CityId. '">
            <input type="hidden" name="ProtocolYear" value="' . $ProtocolYear . '">
            <div class="table_label_H col-sm-12">
                <input type="submit" value="Importa" >                           
            </div >
		</div >';


echo $str_out;

if(strlen($msg_Problem)>0){
    echo '
		<div class="clean_row HSpace48"></div>	
        <div class="col-sm-12">
			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
			<div class="clean_row HSpace4"></div>	
		</div>
		' . $msg_Problem;

}
