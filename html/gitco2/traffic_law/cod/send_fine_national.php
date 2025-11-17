<?php
include(INC."/function_postalCharge.php");

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_send_fine.php'.$Filters));
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$str_Success = $str_Warning = '';

$n_LanguageId = 1;
$a_CompressFine = array();

$ZoneId=0;
$str_ContractFine = "";

$PrintTypeId = ($RegularPostalFine) ? 4 : 1;
$DocumentTypeId = 1;

$a_DocumentationFineZip = array();

$a_Detector = array();
$a_SpeedLengthAverage = array();
$a_DetectorPosition = array();
$a_DetectorKind = array();

$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");

$ultimate = CheckValue('ultimate','s');

$a_PrinterConfigs = unserialize(PRINTER_FTP_CONFIG);
$a_PrinterConf = $a_PrinterConfigs[$PrinterId] ?? null;

//Se viene selezionato uno stampatore per cui è previsto l'invio del flusso tramite FTP, tenta la connessione
if($a_PrinterConf && $ultimate && PRODUCTION){
    $phpFTP = PhpFTPFactory::create(
        $a_PrinterConf['Type'],
        $a_PrinterConf['Host'],
        $a_PrinterConf['Username'],
        $a_PrinterConf['Password'],
        $a_PrinterConf['Port']);
    if(!$phpFTP->connect()){
        $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
        header("location: ".$P);
        DIE;
    } else {
        $phpFTP->disconnect();
    }
}

//Parametri stampatore////////////////////////////
$str_Mod23LSubject          = $r_Customer['NationalMod23LSubject'];
$str_Mod23LCustomerName     = $r_Customer['NationalMod23LCustomerName'];

$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrinterId AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_SmaName                = $r_PrintParameter['NationalSmaName'] ?? '';
$str_SmaAuthorization       = $r_PrintParameter['NationalSmaAuthorization'] ?? '';
$str_SmaPayment             = $r_PrintParameter['NationalSmaPayment'] ?? '';

$str_Mod23LCustomerSubject  = $r_PrintParameter['NationalMod23LCustomerSubject'] ?? '';
$str_Mod23LCustomerAddress  = $r_PrintParameter['NationalMod23LCustomerAddress'] ?? '';
$str_Mod23LCustomerCity     = $r_PrintParameter['NationalMod23LCustomerCity'] ?? '';
$str_PostalAuthorization    = trim($r_PrintParameter['NationalPostalAuthorization'] ?? '');

$str_PostalType             = $r_Customer['NationalPostalType'];
/////////////////////////////////////////////////

/*
    La segnalazione della postazione di rilevamento della velocita' dei veicoli e' stata effettuata mediante
    esposizione di appositi cartelli segnaletici informativi, posizionati prima della postazione di rilevamento,
    ai sensi del D. Interm. Del 15 agosto 2007, e in modo da garantire l'avvistamento della postazione di rilevamento
    della velocita' dei veicoli e la salvaguardia della sicurezza della circolazione stradale.
 */
if($_SESSION['cityid']=='U480') {
    $str_Detector = "Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. La velocita' rilevata dall'apparecchiatura elettronica e' stata di {SpeedControl} Km/h e detratta la tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e precisamente {Kind} matricola {Code} - {Ratification}{AdditionalTextIta}";
} else if ($_SESSION['cityid'] == 'A446') {
    $str_Detector = "Il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. La velocita' rilevata dall'apparecchiatura elettronica funzionante in presenza di quest'organo di Polizia e' stata di {SpeedControl} km/h, e detratta la tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} km/h, ne consegue la violazione ivi indicata. L'infrazione e' stata accertata mediante dispositivo di controllo di tipo omologato {TimeTypeId} e precisamente {Kind} matricola {Code} {Ratification}{AdditionalTextIta}";
} else if ($_SESSION['cityid'] == 'D925') {
    $str_Detector = "Dettaglio della violazione: la velocita' rilevata dall'organo di polizia e' stata di {SpeedControl} Km/h e detratta la tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e precisamente {Kind} matricola {Code} - {Ratification}{AdditionalTextIta}";
} else {
    $str_Detector = "Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. La velocita' rilevata dall'organo di polizia e' stata di {SpeedControl} Km/h e detratta la tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e precisamente {Kind} matricola {Code} - {Ratification}{AdditionalTextIta}";
}

$DetectorKind = '';
//velocità non scout
$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0 AND DetectorTypeId=1 AND Kind!='SCOUT SPEED'");
while ($r_Detector = mysqli_fetch_array($rs_Detector)){
    $str = str_replace("{Kind}",$r_Detector['Kind'],$str_Detector);
    $str = str_replace("{Code}",$r_Detector['Number'],$str);
    $str = str_replace("{Tolerance}",intval($r_Detector['Tolerance']),$str);
    $str = str_replace("{Ratification}",$r_Detector['Ratification'],$str);
    $str = str_replace("{AdditionalTextIta}", $r_Detector['AdditionalTextIta'], $str);

    $a_Detector[$r_Detector['Id']] = $str;
    $a_SpeedLengthAverage[$r_Detector['Id']] = $r_Detector['SpeedLengthAverage'];
    $a_DetectorPosition[$r_Detector['Id']] = ($r_Detector['Position']=="") ? " " : " ".$r_Detector['Position']." ";
    $a_DetectorKind[$r_Detector['Id']] = $r_Detector['Kind'];
}


        $str_Detector = "
        La velocita' riscontrata dallo strumento in modalita' dinamica e' di {SpeedControl} Km/h, in cui vige il limite di velocita' di {SpeedLimit} Km/h, superando quindi il limite imposto di {SpeedExcess} Km/h. 
        La velocita' rilevata dallo strumento e' stata ridotta del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96). Rilevatore utilizzato: {Kind} matricola {Code}{Ratification}{AdditionalTextIta}
     ";

//velocità scout
$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0 AND DetectorTypeId=1 AND Kind='SCOUT SPEED'");
while ($r_Detector = mysqli_fetch_array($rs_Detector)) {
    
    $str = str_replace("{Kind}", $r_Detector['Kind'], $str_Detector);
    $str = str_replace("{Code}", $r_Detector['Number'], $str);
    $str = str_replace("{Tolerance}", intval($r_Detector['Tolerance']), $str);
    $str = str_replace("{Ratification}", $r_Detector['Ratification'], $str);
    $str = str_replace("{AdditionalTextIta}", $r_Detector['AdditionalTextIta'], $str);

    $a_Detector[$r_Detector['Id']] = $str;
    $a_SpeedLengthAverage[$r_Detector['Id']] = $r_Detector['SpeedLengthAverage'];
    $a_DetectorPosition[$r_Detector['Id']] = ($r_Detector['Position']=="") ? " " : " ".$r_Detector['Position']." ";
    $a_DetectorKind[$r_Detector['Id']] = $r_Detector['Kind'];
}


$str_Detector = "L'infrazione e' stata accertata mediante dispositivo di controllo di tipo omologato , e precisamente {Kind} matricola {Code} {Ratification} {AdditionalTextIta}";

//semaforo
$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0 AND DetectorTypeId=2");
while ($r_Detector = mysqli_fetch_array($rs_Detector)) {
    $str = str_replace("{Kind}", $r_Detector['Kind'], $str_Detector);
    $str = str_replace("{Code}", $r_Detector['Number'], $str);
    $str = str_replace("{Ratification}", $r_Detector['Ratification'], $str);
    $str = str_replace("{AdditionalTextIta}", $r_Detector['AdditionalTextIta'], $str);
    
    $a_Detector[$r_Detector['Id']] = $str;
    $a_DetectorPosition[$r_Detector['Id']] = ($r_Detector['Position']=="") ? " " : " ".$r_Detector['Position']." ";
    $a_DetectorKind[$r_Detector['Id']] = $r_Detector['Kind'];
}

$str_Speed = "";
$str_TrafficLight = "";
$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$ProtocolNumber = 0;



$StatusTypeId = ($RegularPostalFine) ? 9 : 20;

$NotificationTypeId = 6;



$NotificationDate = date("Y-m-d");



$a_Lan = unserialize(LANGUAGE);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
$a_AdditionalMass = unserialize(ADDITIONAL_MASS);

$P = "frm_send_fine.php";

$numLit = new CLS_LITERAL_NUMBER();
if(isset($_POST['checkbox'])) {
    
    $rs->Start_Transaction();
 
    
    $str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
    $rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
    $a_ProtocolLetterLocality = array();
    while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
    }




    if($ultimate){

        $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
        $flow = mysqli_fetch_array($flows);

        $int_FlowNumber = $flow['Number']+1;
        //$int_FlowNumber=0;

        $str_FlowType = ($RegularPostalFine) ? "_PostaNorm_Ita_" : "_Verb_Ita_";

        $FileNameDoc = "Flusso_".$int_FlowNumber.$str_FlowType.$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);
        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";

        $aInsert = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
            array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
            array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$PrinterId,'settype'=>'int'),
            array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>$PrintTypeId,'settype'=>'int'),
            array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeId,'settype'=>'int'),
            array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
            array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
            array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
            array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
        );

        $FlowId = $rs->Insert('Flow',$aInsert);


    }
    else{
        $FileNameDoc = "Flusso_Verb_Ita_".$_SESSION['cityid']."_PROVVISORIO";
        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";
    }

    $path = NATIONAL_FLOW."/".$_SESSION['cityid']."/";
    $myfile = fopen($path.$Documentation, "w") or die("Unable to open file!");








    $checkFlowHeader = 0;
    foreach($_POST['checkbox'] as $FineId) {
        $chk_180                    = false;
        $chk_126Bis                 = false;
        $chk_ReducedPayment         = false;

        $n_LicensePoint             = 0;
        $n_TotPartialFee            = 0;
        $n_TotFee                   = 0;
        $n_TotMaxFee                = 0;

        $NotificationFee            = 0;
        $ChargeTotalFee             = 0;
        $ResearchFee                = 0;

        //Se il forfettario è impostato, reimposta la variabile
        if ($r_Customer['NationalTotalFee'] > 0) $ChargeTotalFee = $r_Customer['NationalTotalFee'];
        //Se le spese di notifica sono impostate, reimposta le variabili
        else {
            $NotificationFee = $r_Customer['NationalNotificationFee'];
            $ResearchFee = $r_Customer['NationalResearchFee'];
        }


        $rs_Fine = $rs->Select('V_FineArticle', "Id=" . $FineId. " 
        AND (TrespasserTypeId=1 OR TrespasserTypeId=11
            OR (TrespasserTypeId=2 AND FineSendDate IS NULL)
            OR (TrespasserTypeId=3 AND FineSendDate IS NULL)
            OR (TrespasserTypeId=15 AND FineSendDate IS NULL)
            OR (TrespasserTypeId=16 AND FineSendDate IS NULL))
        "
        );

        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
            $n_PageNumber =0;

            $ViolationTypeId = $r_Fine['ViolationTypeId'];
            $ProtocolYear = $r_Fine['ProtocolYear'];
            
            if(trim($r_Fine['ArticleLetterAssigned'])!=''){
                $NationalProtocolLetterType1 = $r_Fine['ArticleLetterAssigned'];
                $NationalProtocolLetterType2 = $r_Fine['ArticleLetterAssigned'];
            } else if(trim($r_Fine['ViolationLetterAssigned'])!=''){
                $NationalProtocolLetterType1 = $r_Fine['ViolationLetterAssigned'];
                $NationalProtocolLetterType2 = $r_Fine['ViolationLetterAssigned'];
            } else {
                $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType1'];
                $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType2'];
            }

            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);


            $RuleTypeId = $r_RuleType['Id'];

            $ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            $FormTypeId = $r_RuleType['NationalFormId'];

            $a_PrintObject = explode("*", $r_RuleType['PrintObject' . $a_Lan[$n_LanguageId]]);

            //testo invito in AG - - Garlasco, Albuzzano e Borgo San Siro
            if((($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2") || ($r_Fine['Article']==80 && $r_Fine['Paragraph']=="14")) && $r_Fine['KindSendDate']!=''){
                $rs_Form= $rs->Select('Form', "FormTypeId=101 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            } else if($r_Fine['Article']==142){
                $rs_Form= $rs->Select('Form', "FormTypeId=102 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];
            }

            $articleComplete = $r_Fine['Article'];
            if ($r_Fine['Paragraph'] > 0)
                $articleComplete .= "/" . $r_Fine['Paragraph'];
            if ($r_Fine['Letter'] != "" && $r_Fine['Letter'] != null)
                $articleComplete .= "/" . $r_Fine['Letter'];


            if (!($r_Fine['StatusTypeId'] == 15 || $r_Fine['StatusTypeId'] == 8 )) {

                $_SESSION['Message'] = "Problemi con la creazione del flusso con verbale ID ." . $FineId . ". Controllare e riprovare.";
                header("location: " . $P);
                DIE;
            }

            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

            $str_AdditionalNight = "";
            if ($r_ArticleTariff['AdditionalNight'] == 1) {

                $a_Time = explode(":", $r_Fine['FineTime']);

                if ($a_Time[0] < FINE_HOUR_START_DAY || ($a_Time[0] > FINE_HOUR_END_DAY) || ($a_Time[0] == FINE_HOUR_END_DAY && $a_Time[1] != "00")) {
                    $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId];
                }
            }
            $str_AdditionalMass = "";
            if ($r_ArticleTariff['AdditionalMass'] == 1) {
                if ($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
            }

            if ($r_ArticleTariff['126Bis'] == 1) {
                $chk_126Bis = true;
                $n_LicensePoint += $r_ArticleTariff['LicensePoint'];
            }


            if ($r_ArticleTariff['PresentationDocument'] == 1) {
                $chk_180 = true;
            }


            $n_TotFee += $r_Fine['Fee'];
            $n_TotMaxFee += $r_Fine['MaxFee'];
            if ($r_ArticleTariff['ReducedPayment'] == 1) {
                $chk_ReducedPayment = true;
                $n_TotPartialFee += $r_Fine['Fee'] * FINE_PARTIAL;
            } else {
                $n_TotPartialFee += $r_Fine['Fee'];
            }

            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);


            if (($r_Fine['Article'] == 126 AND $r_Fine['Paragraph'] == '0' AND $r_Fine['Letter'] == 'bis') || ($r_Fine['Article'] == 180 AND $r_Fine['Paragraph'] == '8' AND $r_Fine['Letter'] == '')) {
                $PreviousId = $r_Fine['PreviousId'];

                $rs_PreviousFine = $rs->Select('Fine', "Id=" . $PreviousId);
                $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);

                $PreviousProtocolId = $r_PreviousFine['ProtocolId'];
                $PreviousProtocolYear = $r_PreviousFine['ProtocolYear'];
                $PreviousFineDate = DateOutDB($r_PreviousFine['FineDate']);


            } else {

                $PreviousProtocolId = "";
                $PreviousProtocolYear = "";
                $PreviousFineDate = "";


            }

            $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);            

            
            //Prima di arrivare a questo blocco, le spese forfettario, notifica e ricerca sono già state caricate nelle variabili
            //Se è spedito per posta ordinaria, le spese di ricerca vengono azzerate
            if($RegularPostalFine){
                $ResearchFee = 0.00;
                $NotificationFee = $postalcharge['ReminderZone' . $ZoneId];
            } else {
                //Se il forfettario è impostato, le spese di ricerca sono uguali alla differenza tra il forfettario e le spese di notifica
                if ($ChargeTotalFee > 0) {
                    $NotificationFee = $r_Customer['NationalNotificationFee'];
                    $ResearchFee = $ChargeTotalFee - $NotificationFee;
                }
            }

            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;

            $AdditionalFee = $NotificationFee + $ResearchFee;

            $CANFee = $postalcharge['CanFee'];
            $CADFee = $postalcharge['CadFee'];

            $AdditionalFeeCAN = $AdditionalFee + $CANFee;
            $AdditionalFeeCAD = $AdditionalFee + $CADFee;

            $str_Speed = "";
            $str_TrafficLight  = "";
            $SpeedLengthAverage = 0;
            $SpeedExcess = 0;
            $r_FineTime = $r_Fine['FineTime'];
            if ($r_Fine['Speed'] > 0 AND $r_Fine['Speed']!='0.00') {

                $SpeedExcess = intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit']);

                $str_Speed = " " . $a_Detector[$r_Fine['DetectorId']];
                $str_Speed = str_replace("{Speed}", intval($r_Fine['Speed']), $str_Speed);
                $str_Speed = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_Speed);
                $str_Speed = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_Speed);
                $str_Speed = str_replace("{SpeedExcess}", intval($SpeedExcess), $str_Speed);


                $str_Speed = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_Speed);
                $SpeedLengthAverage = $a_SpeedLengthAverage[$r_Fine['DetectorId']];

            }else if($r_Fine['TimeTLightFirst'] > 0) {
                $str_TrafficLight = $a_Detector[$r_Fine['DetectorId']];
            }



            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
            );
            if($r_Fine['FineTypeId']==4){

                $str_ContractFine .= $FineId.";Atto Giudiziario;VERBALI;40;". $_SESSION['cityid'] . ";"  . $r_Customer['ManagerName']. ";" . $ManagerSubject. ";" . $r_Customer['ManagerAddress']. ";" . $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")". ";". $r_Customer['ManagerPhone']. ";" . $str_SmaPayment. ";" . $str_SmaName. ";" . $str_SmaAuthorization. ";" . $str_Mod23LSubject. ";" . $str_Mod23LCustomerName. ";" . $str_Mod23LCustomerSubject. ";" . $str_Mod23LCustomerAddress. ";" . $str_Mod23LCustomerCity. ";" . $a_GenreLetter[$trespasser['Genre']] . " " . $trespasser['CompanyName'] . " " . $trespasser['Surname'] . " " . $trespasser['Name']. ";". $str_TrespasserAddress. ";" . $trespasser['ZIP']. ";" . $trespasser['City']. ";" . $trespasser['Province']. ";" . $DocumentationZip . PHP_EOL;

            } else {
                $str_ProtocolLetter = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
                $Content = getStaticContent($FormTypeId,$_SESSION['cityid'],1, $n_LanguageId);
                if ($r_ArticleTariff['AdditionalSanctionId'] > 0) {
                    $rs_AdditionalSanction = $rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']);
                    $r_AdditionalSanction = mysqli_fetch_array($rs_AdditionalSanction);
                    $Content = str_replace("{AdditionalSanctionId}", $str_TrafficLight . " SANZIONE ACCESSORIA: " . $r_AdditionalSanction['Title' . $a_Lan[$n_LanguageId]], $Content);

                } else {
                    $Content = str_replace("{AdditionalSanctionId}", $str_TrafficLight . "", $Content);
                }

                $Content = str_replace("<h3>", "", $Content);
                $Content = str_replace("</h3>", "", $Content);
                $Content = str_replace("<h4>", "", $Content);
                $Content = str_replace("</h4>", "", $Content);
                $Content = str_replace("<h5>", "", $Content);
                $Content = str_replace("</h5>", "", $Content);
                $Content = str_replace("<b>", "", $Content);
                $Content = str_replace("</b>", "", $Content);
//        $Content = str_replace("<br />","",$Content);


                $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
                $Content = str_replace("{FineTime}", TimeOutDB($r_FineTime), $Content);
                $Content = str_replace("{VehicleTypeId}", $r_Fine['VehicleTitle' . $a_Lan[$n_LanguageId]], $Content);
                $Content = str_replace("{VehiclePlate}", $r_Fine['VehiclePlate'], $Content);

                $Content = str_replace("{VehicleBrand}", $r_Fine['VehicleBrand'], $Content);
                $Content = str_replace("{VehicleModel}", $r_Fine['VehicleModel'], $Content);
                $Content = str_replace("{VehicleColor}", $r_Fine['VehicleColor'], $Content);


                $str_ListArticle = '';

                $Paragraph = ($r_Fine['Paragraph'] == "0") ? "" : $r_Fine['Paragraph'] . " ";
                $Letter = ($r_Fine['Letter'] == "0") ? "" : $r_Fine['Letter'];

                $str_ArticleId = $r_Fine['Article'] . "/" . $Paragraph . $Letter;
                /////////////////////////////////////////////
                //Article Owner
                /////////////////////////////////////////////
                $rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
                $r_FineOwner = mysqli_fetch_array($rs_FineOwner);

                $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
                
				$str_ArticleDescription = str_replace("{Speed}", intval($r_Fine['Speed']), $str_ArticleDescription);
				$str_ArticleDescription = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_ArticleDescription);
				$str_ArticleDescription = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_ArticleDescription);
				$str_ArticleDescription = str_replace("{SpeedExcess}", intval($SpeedExcess), $str_ArticleDescription);
				$str_ArticleDescription = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_ArticleDescription);
		   
				$str_ArticleDescription .= $str_Speed . $str_AdditionalNight . $str_AdditionalMass;

                $str_ExpirationDate = (isset($r_Fine['ExpirationDate']) || $r_Fine['ExpirationDate']!="") ? DateOutDB($r_Fine['ExpirationDate']) : "";
                $str_ArticleDescription = str_replace("{ExpirationDate}", $str_ExpirationDate, $str_ArticleDescription);


                /////////////////////////////////////////////
                //Additional Article
                /////////////////////////////////////////////

                if ($r_Fine['ArticleNumber'] > 1) {
                    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $FineId, "ArticleOrder");
                    while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {

                        if ($r_ArticleTariff['126Bis'] == 1) {
                            $chk_126Bis = true;
                            $n_LicensePoint += $r_AdditionalArticle['LicensePoint'];
                        }

                        $n_TotFee += $r_AdditionalArticle['Fee'];
                        $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
                        if ($r_AdditionalArticle['ReducedPayment'] == 1) {
                            $chk_ReducedPayment = true;
                            $n_TotPartialFee += $r_AdditionalArticle['Fee'] * FINE_PARTIAL;
                        } else {
                            $n_TotPartialFee += $r_AdditionalArticle['Fee'];
                        }

                        $str_AdditionalArticleDescription = (strlen($r_AdditionalArticle['AdditionalArticleDescription' . LAN]) > 0) ? $r_AdditionalArticle['AdditionalArticleDescription' . LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];

                        $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate']!="") ? DateOutDB($r_AdditionalArticle['ExpirationDate']) : "";
                        $str_AdditionalArticleDescription = str_replace("{ExpirationDate}", $str_ExpirationDate, $str_AdditionalArticleDescription);


                        $Paragraph = ($r_AdditionalArticle['Paragraph'] == "0") ? "" : $r_AdditionalArticle['Paragraph'] . " ";
                        $Letter = ($r_AdditionalArticle['Letter'] == "0") ? "" : $r_AdditionalArticle['Letter'];


                        $str_ListArticle .= " e Art. " . $r_AdditionalArticle['Article'] . "/" . $Paragraph . $Letter;
                        $str_ArticleDescription .= $str_AdditionalArticleDescription;

                    }

                }

                if ($chk_ReducedPayment) {
                    $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                    $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";

                } else {
                    $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                    $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
                }

                $Content = str_replace("{PagoPA1}", $r_Fine['PagoPA1']. " per ".$str_PaymentDay1, $Content);
                $Content = str_replace("{PagoPA2}", $r_Fine['PagoPA2']. " per ".$str_PaymentDay2, $Content);


                $Content = str_replace("{ArticleId}", $str_ArticleId .$str_ListArticle, $Content);

                $str_ArticleDescription = str_replace(array("\n", "\r"), "", $str_ArticleDescription);
                $Content = str_replace("{ArticleDescription}", $str_ArticleDescription, $Content);

                $str_ControllerName = trim($r_Fine['ControllerQualification']." ".$r_Fine['ControllerName']);
                $str_ControllerCode = trim($r_Fine['ControllerCode']);
                /////////////////////////////////////////////
                //Additional controller
                /////////////////////////////////////////////

                $rs_FineAdditionalController = $rs->Select('V_AdditionalController', "FineId=" . $FineId);

                while ($r_FineAdditionalController = mysqli_fetch_array($rs_FineAdditionalController)){
                    $str_ControllerCode="";
                    $str_ControllerName .= ", ".trim($r_FineAdditionalController['ControllerQualification']." ".$r_FineAdditionalController['ControllerName']);
                }




                $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ReasonDescriptionIta'];
                
                if (isset($CreationType) && $CreationType==5){
                    $Content = str_replace("La violazione non e' stata immediatamente contestata" ,"Verbale redatto in data odierna a seguito di invito n ".$r_Fine['Code'].".",$Content);
                    $Content = str_replace("{ReasonId}", "", $Content);
                } else if(trim($str_ReasonDescription)==""){
                    $Content = str_replace("La violazione non e' stata immediatamente contestata:" ,"",$Content);
                    $Content = str_replace("La violazione non e' stata immediatamente contestata" ,"",$Content);
                    $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);

                }else{
                    $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);
                }

                if($r_Fine['DetectorId']>0){
                    $Content = str_replace("{DetectorPosition}", $a_DetectorPosition[$r_Fine['DetectorId']], $Content);
                } else {
                    $Content = str_replace("{DetectorPosition}", " ", $Content);
                }

                if ($SpeedLengthAverage > 0) {
                    $SpeedTimeAverage = $SpeedLengthAverage * 3.6 / $r_Fine['SpeedControl'];

                    $Content = str_replace("{SpeedTimeAverage}", NumberDisplay($SpeedTimeAverage), $Content);
                    $Content = str_replace("{SpeedLengthAverage}", $SpeedLengthAverage, $Content);
                }

                if ($chk_126Bis && $FormTypeId!=101) {

                    $query = "Select A.AdditionalTextIta, ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId ";
                    $query .= "WHERE A.CityId='" . $_SESSION['cityid'] . "' AND A.Article=126 AND A.Letter='bis' AND ART.Year = " . $_SESSION['year'];

                    $articles126bis = $rs->SelectQuery($query);
                    $article126bis = mysqli_fetch_array($articles126bis);


                    $Content = ($r_Fine['ArticleNumber'] > 1) ? str_replace("{ArticleAdditionalText}", $article126bis['AdditionalTextIta'], $Content) : str_replace("{ArticleAdditionalText}", $r_Fine['ArticleAdditionalText' . $a_Lan[$n_LanguageId]], $Content);




                    $Content = str_replace("{DecurtationPoints}", $n_LicensePoint, $Content);
                    $Content = str_replace("{Fee126bis}", NumberDisplay($article126bis['Fee']), $Content);
                    $Content = str_replace("{MaxFee126bis}", NumberDisplay($article126bis['MaxFee']), $Content);


                } else {
                    if ($r_Fine['Article']==126 ){
                        $Content = str_replace("{ArticleAdditionalText}", "", $Content);
                    } else{
                        $Content = str_replace("{ArticleAdditionalText}", $r_Fine['ArticleAdditionalText' . $a_Lan[$n_LanguageId]], $Content);
                    }

                }

                /////////////////////////////////////////////
                //126 BIS
                /////////////////////////////////////////////
                $Content = str_replace("{PreviousProtocolId}", $PreviousProtocolId, $Content);
                $Content = str_replace("{PreviousProtocolYear}", $PreviousProtocolYear, $Content);
                $Content = str_replace("{PreviousFineDate}", $PreviousFineDate, $Content);


                $Content = str_replace("{Locality}", $r_Fine['CityTitle'], $Content);
                $Content = str_replace("{Address}", $r_Fine['Address'], $Content);

                $str_Gps = (trim($r_Fine['GpsLat'])=="") ? "" : "( ".$r_Fine['GpsLat'].", ".$r_Fine['GpsLong']. " )";
                $Content = str_replace("{Gps}", $str_Gps, $Content);

                $Content = str_replace("{BankOwner}", $r_Customer['NationalBankOwner'], $Content);
                $Content = str_replace("{BankName}", $r_Customer['NationalBankName'], $Content);
                $Content = str_replace("{BankAccount}", $r_Customer['NationalBankAccount'], $Content);
                $Content = str_replace("{BankSwift}", $r_Customer['NationalBankSwift'], $Content);
                $Content = str_replace("{BankIban}", $r_Customer['NationalBankIban'], $Content);


                $rs_Row = $rs->Select('V_FineHistory', "(NotificationTypeId=2 || NotificationTypeId=3) AND Id=" . $FineId);
                $r_Row = mysqli_fetch_array($rs_Row);

                $NotificationDocumentDate = DateOutDB($r_Row['NotificationDate']);


                $controllers = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Id =" . $r_Row['FineControllerId']);
                $controller = mysqli_fetch_array($controllers);

                $ChiefControllerName = trim($controller['Qualification']." ".$controller['Name']);



                $Content = str_replace("{CurrentDate}", $NotificationDocumentDate, $Content);

                $Content = str_replace("{IuvCode}", $r_Fine['IuvCode'], $Content);
                
                if (is_null($r_Customer['ManagerSignName'])) {
                    if ($r_Customer['CityUnion'] > 1) {
                        $Content = str_replace("{Date}", $r_Fine['CityTitle'] . ", " . $NotificationDocumentDate, $Content);
                    } else {
                        $Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . $NotificationDocumentDate, $Content);
                    }
                } else {
                    $Content = str_replace("{Date}", $r_Customer['ManagerSignName'] . ", " . $NotificationDocumentDate, $Content);
                }

                $Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . date("d/m/Y"), $Content);

                //Località firma verbale
                $Content = str_replace("{ManagerSignName}", $r_Customer['ManagerSignName'], $Content);
                
                $Content = str_replace("{ManagerDataEntryName}", $r_Customer['ManagerDataEntryName'], $Content);
                $Content = str_replace("{ManagerProcessName}", $r_Customer['ManagerProcessName'], $Content);

                $Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
                $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);

                $PartialFee = $n_TotPartialFee;
                $MaxFee = $n_TotMaxFee * FINE_MAX;

                $TotalPartialFee = $PartialFee + $AdditionalFee;
                $TotalPartialFeeCAN = $PartialFee + $AdditionalFeeCAN;
                $TotalPartialFeeCAD = $PartialFee + $AdditionalFeeCAD;

                $TotalFee = $n_TotFee + $AdditionalFee;
                $TotalFeeCAN = $n_TotFee + $AdditionalFeeCAN;
                $TotalFeeCAD = $n_TotFee + $AdditionalFeeCAD;

                $TotalMaxFee = $MaxFee + $AdditionalFee;
                $TotalMaxFeeCAN = $MaxFee + $AdditionalFeeCAN;
                $TotalMaxFeeCAD = $MaxFee + $AdditionalFeeCAD;

                $Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
                $Content = str_replace("{TotalPartialFee}", NumberDisplay($TotalPartialFee), $Content);
                $Content = str_replace("{TotalPartialFeeCAN}", NumberDisplay($TotalPartialFeeCAN), $Content);
                $Content = str_replace("{TotalPartialFeeCAD}", NumberDisplay($TotalPartialFeeCAD), $Content);

                $Content = str_replace("{Fee}", NumberDisplay($n_TotFee), $Content);
                $Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);
                $Content = str_replace("{TotalFeeCAN}", NumberDisplay($TotalFeeCAN), $Content);
                $Content = str_replace("{TotalFeeCAD}", NumberDisplay($TotalFeeCAD), $Content);


                $Content = str_replace("{ResearchFee}", NumberDisplay($ResearchFee), $Content);
                $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);
                $Content = str_replace("{ChargeTotalFee}", NumberDisplay($ChargeTotalFee), $Content);
                $Content = str_replace("{CANFee}", NumberDisplay($CANFee), $Content);
                $Content = str_replace("{CADFee}", NumberDisplay($CADFee), $Content);


                $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
                $Content = str_replace("{TotalMaxFee}", NumberDisplay($TotalMaxFee), $Content);
                $Content = str_replace("{TotalMaxFeeCAN}", NumberDisplay($TotalMaxFeeCAN), $Content);
                $Content = str_replace("{TotalMaxFeeCAD}", NumberDisplay($TotalMaxFeeCAD), $Content);

                $Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content);
                $Content = str_replace("{AdditionalFeeCAN}", NumberDisplay($AdditionalFeeCAN), $Content);
                $Content = str_replace("{AdditionalFeeCAD}", NumberDisplay($AdditionalFeeCAD), $Content);

                $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
                if($str_ControllerCode=="") {
                    $Content = str_replace("matr. {ControllerCode}", $str_ControllerCode, $Content);
                }
                $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);

                $str_ControllerDate = (strlen($r_Fine['ControllerDate'])==10) ? DateOutDB($r_Fine['ControllerDate']) : "";
                $str_ControllerTime = (trim($r_Fine['ControllerTime'])!="") ? TimeOutDB($r_Fine['ControllerTime']) : "";


                $str_ControllerConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$str_ControllerName. " Matr. ". $str_ControllerCode;
                //$str_ControllerConvalidation = "Convalidato  previa visione dei fotogrammi il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime;
                $str_ChiefConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$ChiefControllerName;
                //$str_ChiefConvalidation = "Convalidato previa visione dei fotogrammi il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime;


                $Content = str_replace("{ControllerConvalidation}", $str_ControllerConvalidation, $Content);

                if ($_SESSION['cityid'] == 'D925') {

                    //se il testo è per la velocità e Celeritas
                    if($ViolationTypeId == 2 && substr($a_DetectorKind[$r_Fine['DetectorId']], 0, strlen('Celeritas Evo 1506')) === 'Celeritas Evo 1506')
                        $str_ChiefConvalidation = "La presente violazione e' stata accertata e convalidata, previa visione dei fotogrammi il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime .
                            " dall'Uff. di polizia locale Vice Comandante R. Recco, presso il Comando di Polizia Locale in intestazione.";
                    else
                        $str_ChiefConvalidation = "Convalidato previa visione dei fotogrammi il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime;
                    
                    if(strlen($r_Fine['ControllerDate'])==10 && trim($r_Fine['ControllerTime'])!=""){
                        $Content = str_replace("<b>Il verbalizzante</b>: {ChiefControllerName}", "", $Content);
                    } else {
                        $Content = str_replace("{ChiefConvalidation}", "", $Content);
                    }

                } else {

                    $Content = str_replace("{ControllerDate}", $str_ControllerDate, $Content);
                    $Content = str_replace("{ControllerTime}", $str_ControllerTime, $Content);
                }


                $Content = str_replace("{ChiefConvalidation}", $str_ChiefConvalidation, $Content);
                $Content = str_replace("{ChiefControllerName}", $ChiefControllerName, $Content);

                $str_ReceiveDate = ($r_Fine['TrespasserTypeId'] == 1 && $r_Fine['ReceiveDate'] != "") ? ' - data id trasgr.' . DateOutDB($r_Fine['ReceiveDate']) : '';



                if($chk_180 && $r_Fine['TrespasserId1_180']>0){

                    $rs_trespasser = $rs->Select('Trespasser', "Id=" . $r_Fine['TrespasserId1_180']);
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);
                    $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
                    $Content = str_replace("{TrespasserCity}", $r_trespasser['City'], $Content);
                    $Content = str_replace("{TrespasserProvince}", $r_trespasser['Province'], $Content);

                } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {
                    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=2");
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);
                    $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
                    $Content = str_replace("{TrespasserCity}", $r_trespasser['City'], $Content);

                    if(strlen($r_trespasser['BornDate']) == 10 && strlen(trim($r_trespasser['BornPlace'])) > 0){
                        $Content = str_replace("{TrespasserBornDate}", DateOutDB($r_trespasser['BornDate']), $Content);
                        $Content = str_replace("{TrespasserBornCity}", $r_trespasser['BornPlace'], $Content);
                    } else {
                        $Content = str_replace(" il {TrespasserBornDate}", "", $Content);
                        $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
                    }

                    $str_Province = (isset($r_trespasser['Province']) && $r_trespasser['Province']!='') ?  "(".$r_trespasser['Province'].")" : "";

                    $Content = str_replace("<col>Res: in {TrespasserAddress} {TrespasserCity} ({TrespasserProvince})<col>", "<col>".$r_trespasser['Address']. " ".$r_trespasser['City']. " ".$str_Province."<col>", $Content);

                } else {
                    $Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
                    $Content = str_replace("{TrespasserCity}", $trespasser['City'], $Content);
                    if(isset($trespasser['Province']) && trim($trespasser['Province'])!=''){
                        $Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);

                    } else {
                        $Content = str_replace("({TrespasserProvince})", '', $Content);

                    }
                }





                if(strlen($trespasser['BornDate']) == 10 && strlen(trim($trespasser['BornPlace'])) > 0){
                    $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']) . $str_ReceiveDate, $Content);

                    $str_BornPlace = preg_replace('#\040{2,}#', ' ', $trespasser['BornPlace']);

                    $Content = str_replace("{TrespasserBornCity}", $str_BornPlace, $Content);
                } else {
                    $Content = str_replace(" il {TrespasserBornDate}", $str_ReceiveDate, $Content);
                    $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
                }


                $Content = str_replace("{TrespasserAddress}", $str_TrespasserAddress . " " . $trespasser['ZIP'], $Content);
                $Content = str_replace("{TrespasserCountry}", $trespasser['CountryTitle'], $Content);
                $Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);


                if ($trespasser['Genre'] == "D") {
                    $Content = str_replace("{TrespasserC}", $trespasser['CompanyName'], $Content);
                    $Content = str_replace("{TrespasserCC}", $trespasser['City'] . "(" . $trespasser['Province'] . ")", $Content);
                    $Content = str_replace("{TrespasserCA}", $trespasser['Address'], $Content);

                    $Content = str_replace("{TrespasserN}", "___________________", $Content);
                    $Content = str_replace("{TrespasserS}", "___________________", $Content);
                    $Content = str_replace("{TrespasserBC}", "_____________________", $Content);
                    $Content = str_replace("{TrespasserBD}", "_________________", $Content);
                    $Content = str_replace("{TrespasserR}", "______________________ Via ______________________________", $Content);
                    $Content = str_replace("{TrespasserP}", "____", $Content);

                } else {
                    $Content = str_replace("{TrespasserN}", $trespasser['Name'], $Content);
                    $Content = str_replace("{TrespasserS}", $trespasser['Surname'], $Content);

                    if (strlen(trim($trespasser['BornPlace'])) > 0) {
                        $Content = str_replace("{TrespasserBC}", $trespasser['BornPlace'], $Content);
                    } else {
                        $Content = str_replace("{TrespasserBC}", "____________________", $Content);
                    }

                    if (strlen(trim($str_BornPlace)) > 0) {
                        $Content = str_replace("{TrespasserBD}", DateOutDB($trespasser['BornDate']), $Content);
                    } else {
                        $Content = str_replace("{TrespasserBD}", "________________", $Content);
                    }

                    $Content = str_replace("{TrespasserR}", $trespasser['City'] . " " . $trespasser['Address'], $Content);
                    $Content = str_replace("{TrespasserP}", $trespasser['Province'], $Content);

                    $Content = str_replace("{TrespasserC}", "____________________________________________", $Content);
                    $Content = str_replace("{TrespasserCC}", "___________________________________", $Content);
                    $Content = str_replace("{TrespasserCA}", "Via _______________________________________ N. __", $Content);
                }

                $offices = $rs->Select('V_JudicialOffice', "CityId='" . $_SESSION['cityid'] . "'");
                while ($office = mysqli_fetch_array($offices)) {
                    if ($office['OfficeId'] == 1) {
                        $Content = str_replace("{Judge}", $office['OfficeTitle' . $a_Lan[$n_LanguageId]], $Content);
                        $Content = str_replace("{JudgeCity}", $office['City'], $Content);
                        $Content = str_replace("{JudgeProvince}", $office['Province'], $Content);
                    }
                    if ($office['OfficeId'] == 2) {
                        $Content = str_replace("{Court}", $office['OfficeTitle' . $a_Lan[$n_LanguageId]], $Content);
                        $Content = str_replace("{CourtCity}", $office['City'], $Content);
                        $Content = str_replace("{CourtProvince}", $office['Province'], $Content);

                    }

                }


                $ProtocolId = ($RegularPostalFine) ? $r_Fine['ProtocolIdAssigned'] : $r_Fine['ProtocolId'];

                $Content = str_replace("{Code}", $r_Fine['Code'], $Content);
                $Content = str_replace("{ProtocolId}", $ProtocolId, $Content);

                if ($r_Fine['TrespasserTypeId'] == 11) {
                    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=10");
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);

                    $Content = str_replace("{TrespasserRentName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
                    $Content = str_replace("{DateRent}", DateOutDB($r_Fine['ReceiveDate']), $Content);

                }


                $Content = str_replace(array("\n", "\r"), "", $Content);
            }



            if($r_Fine['FineTypeId']!=4) {




                if($_SESSION['cityid']=="H452"){
                    $str_CustomerAddress = "Art.57 CPP e Art.11 c.1 L.a) e b) CDS";
                    $str_CustomerCity = $r_Customer['ManagerAddress']. " " .$r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
                } else {
                    $str_CustomerAddress = $r_Customer['ManagerAddress'];
                    $str_CustomerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
                }



                $a_Flow = array(
                    "FineId" => $FineId,
                    "Page1_Row1"=> $r_Fine['ProtocolId'],
                    "Page1_Row2" => $r_Fine['Code'],
                    "TIPOLOGIA_STAMPA" => "Lettera normale",
                    "TIPOLOGIA_ATTO" => "VERBALI_ORDINARI_ITA",
                    "TIPOLOGIA_FLUSSO" => $FormTypeId,
                    "CodiceComune" => $_SESSION['cityid'],

                    "HeaderRow1" => $r_Customer['ManagerName'],
                    "HeaderRow1_RichiestaDati" => $r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName'],
                    "HeaderRow2" => $ManagerSubject,
                    "HeaderRow3" => $str_CustomerAddress,
                    "HeaderRow4" => $str_CustomerCity,
                    "HeaderRow5" => $r_Customer['ManagerPhone'],

                    "HeaderRow1_Col2" => $a_PrintObject[0],
                    "HeaderRow2_Col2" => $a_PrintObject[1],
                    "HeaderRow3_Col2" => $a_PrintObject[2],
                );


                $a_Flow["Richiesta_Dati"] = ($chk_126Bis) ? "SI" : "NO";


                $a_Flow["Spese_Anticipate"] = $str_SmaPayment;

                $a_Flow["Intestatario_SMA"] = $str_SmaName;
                $a_Flow["Numero_SMA"] = $str_SmaAuthorization;


                $a_Flow["Mod23_Soggetto_Mittente"]  = $str_Mod23LSubject;
                $a_Flow["Mod23_Ente_Gestito"]       = $str_Mod23LCustomerName;
                $a_Flow["Mod23_Recapito_Soggetto"]  = $str_Mod23LCustomerSubject;
                $a_Flow["Mod23_Indirizzo_Soggetto"] = $str_Mod23LCustomerAddress;
                $a_Flow["Mod23_Citta_Soggetto"]     = $str_Mod23LCustomerCity;



                $n_PageNumber++;
                $aMainPart = explode("<main_part>", $Content);
                $aRow = explode("<row>", $aMainPart[1]);

                //Arial 9 minuscolo
                $a_Flow["Page1_Row1"] = $aRow[1];


                $a_Flow["Page1_Row2"] = ($r_Customer['PDFRefPrint'] == 1) ? 'Ref. Nr:' . $r_Fine['Code'] : '';


                $a_Flow["Recipient_Row1"] = $a_GenreLetter[$trespasser['Genre']] . " " . $trespasser['CompanyName'] . " " . $trespasser['Surname'] . " " . $trespasser['Name'];
                $a_Flow["Recipient_Row2"] = $str_TrespasserAddress;//L
                $a_Flow["Recipient_Row3"] = $trespasser['ZIP'] . ' ' . $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ")";


                $str_FullNameSurname = $trespasser['CompanyName'] . " " . $trespasser['Surname'] . " " . $trespasser['Name'];
                $str_TaxCode= $trespasser["TaxCode"];

                //Arial 8 minuscolo
                $aCol = explode("<br />", $aRow[3]);



                $a_Flow["Page1_Row3_Col1"] = $aCol[0];//J
                $a_Flow["Page1_Row3_Col2"] = (isset($aCol[1])) ? $aCol[1] : "";//J

                $a_Flow["Page1_Row4"] = $aRow[4];//C
                $exp_aRow = explode("<br />", $aRow[5]);
                for ($i = 0; $i < 4; $i++) {
                    if (isset($exp_aRow[$i]))
                        $a_Flow["Page1_Row5_" . ($i + 1)] = $exp_aRow[$i];
                    else
                        $a_Flow["Page1_Row5_" . ($i + 1)] = "";
                }

                //$a_Flow["Page1_Row5"] = $aRow[5];//J

                $a_Payment = explode("<PAYMENT>", $aRow[6]);
                $a_PaymentType = explode("<PAYMENTTYPE>", $aRow[7]);

                $a_FifthField = array("Table" => 1, "Id" => $r_Fine['Id']);
                if ($chk_ReducedPayment) {
                    $a_FifthField['PaymentType'] = 0;
                    $FifthField1 = SetFifthField($a_FifthField);
                    $a_FifthField['PaymentType'] = 1;
                    $FifthField2 = SetFifthField($a_FifthField);



                    if ($r_Customer['LumpSum'] == 1) {
                        $Bollettino2Fee = ($r_ArticleTariff['ReducedPayment']) ? $TotalFeeCAD : $TotalMaxFeeCAD;
                        $Bollettino1Fee = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFeeCAD : $TotalMaxFeeCAD;
                    }else {
                        $Bollettino2Fee = ($r_ArticleTariff['ReducedPayment']) ? $TotalFee : $TotalMaxFee;
                        $Bollettino1Fee = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFee : $TotalFee;
                    }



                    $scadenzaBoll1 = "Pagamento entro 5gg dalla notif.";
                    $scadenzaBoll2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";

                    $a_Flow["Page1_RowPay_Reduced1"] = $a_Payment[1];//C Bold

                    if ($r_Customer['LumpSum'] == 1) {
                        $a_Flow["Page1_RowPay_Reduced2"] = $a_PaymentType[1];//J
                        $a_Flow["Page1_RowPay_Reduced3"] = $a_PaymentType[2];//J
                        $a_Flow["Page1_RowPay_Reduced4"] = $a_PaymentType[3];//J
                    } else {
                        $a_Flow["Page1_RowPay_Reduced2"] = $a_PaymentType[10];//J
                        $a_Flow["Page1_RowPay_Reduced3"] = "";
                        $a_Flow["Page1_RowPay_Reduced4"] = "";
                    }
                } else {
                    $a_FifthField['PaymentType'] = 1;
                    $FifthField1 = SetFifthField($a_FifthField);
                    $a_FifthField['PaymentType'] = 2;
                    $FifthField2 = SetFifthField($a_FifthField);

                    $Bollettino1Fee = $TotalFee;
                    $Bollettino2Fee = $TotalMaxFee;


                    $scadenzaBoll1 = "Pagamento entro 60gg dalla notif.";


                    $scadenzaBoll2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";

                    $a_Flow["Page1_RowPay_Reduced1"] = "";
                    $a_Flow["Page1_RowPay_Reduced2"] = "";
                    $a_Flow["Page1_RowPay_Reduced3"] = "";
                    $a_Flow["Page1_RowPay_Reduced4"] = "";
                }

                $FifthFieldFee1 = SetFifthFieldFee($Bollettino1Fee);
                $FifthFieldFee2 = SetFifthFieldFee($Bollettino2Fee);


                if (!$chk_ReducedPayment) $a_Payment[2] = str_replace("DAL 6", "DAL 1", $a_Payment[2]);


                $a_Flow["Page1_RowPay_Normal1"] = $a_Payment[2];//C Bold
                if ($r_Customer['LumpSum'] == 1) {
                    $a_Flow["Page1_RowPay_Normal2"] = $a_PaymentType[4];//J
                    $a_Flow["Page1_RowPay_Normal3"] = $a_PaymentType[5];//J
                    $a_Flow["Page1_RowPay_Normal4"] = $a_PaymentType[6];//J
                } else {
                    $a_Flow["Page1_RowPay_Normal2"] = $a_PaymentType[11];//J
                    $a_Flow["Page1_RowPay_Normal3"] = "";
                    $a_Flow["Page1_RowPay_Normal4"] = "";
                }

                if (!$chk_ReducedPayment) {
                    $a_Flow["Page1_RowPay_Max1"] = $a_Payment[3];//C Bold

                    if ($r_Customer['LumpSum'] == 1) {
                        $a_Flow["Page1_RowPay_Max2"] = $a_PaymentType[7];//J
                        $a_Flow["Page1_RowPay_Max3"] = $a_PaymentType[8];//J
                        $a_Flow["Page1_RowPay_Max4"] = $a_PaymentType[9];//J
                    } else {
                        $a_Flow["Page1_RowPay_Max2"] = $a_PaymentType[12];//J
                        $a_Flow["Page1_RowPay_Max3"] = "";
                        $a_Flow["Page1_RowPay_Max4"] = "";
                    }
                } else {
                    $a_Flow["Page1_RowPay_Max1"] = "";
                    $a_Flow["Page1_RowPay_Max2"] = "";
                    $a_Flow["Page1_RowPay_Max3"] = "";
                    $a_Flow["Page1_RowPay_Max4"] = "";
                }

                if ($r_Customer['LumpSum'] == 1) {
                    $a_Flow["Page1_Row8"] = $aRow[8];//J
                    $a_Flow["Page1_Row9"] = $aRow[9];//J
                } else {
                    $a_Flow["Page1_Row8"] = "";//J
                    $a_Flow["Page1_Row9"] = "";//J
                }

                $aCol = explode("<col>", $aRow[10]);
                if ($r_Fine['TrespasserTypeId'] == 11) {
                    $a_Flow["Page1_Row10_Col1"] = $aCol[2];//J
                    $a_Flow["Page1_Row10_Col2"] = $aCol[3];//J
                } else {
                    $a_Flow["Page1_Row10_Col1"] = $aCol[0];//J
                    $a_Flow["Page1_Row10_Col2"] = $aCol[1];//J
                }


                $aCol = explode("<col>", $aRow[11]);
                if ($r_Fine['TrespasserTypeId'] == 11) {
                    $a_Flow["Page1_Row11_Col1"] = $aCol[2];//J
                    $a_Flow["Page1_Row11_Col2"] = $aCol[3];//J
                } else {
                    $a_Flow["Page1_Row11_Col1"] = $aCol[0];//J
                    $a_Flow["Page1_Row11_Col2"] = $aCol[1];//J
                }


                $aCol = explode("<col>", $aRow[12]);
                if ($r_Fine['TrespasserTypeId'] == 11) {

                    $a_Flow["Page1_Row12_Col1"] = $aCol[2];//J
                    $a_Flow["Page1_Row12_Col2"] = $aCol[3];//J

                } else if($chk_180 && $r_Fine['TrespasserId1_180']>0) {

                    $a_Flow["Page1_Row12_Col1"] = "Trasgressore:";
                    $a_Flow["Page1_Row12_Col2"] = $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'] . ' - ' . $trespasser['Address'] . ' ' . $trespasser['ZIP'] . ' ' . $trespasser['City'];

                } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {

                    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=3");
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);

                    $a_Flow["Page1_Row12_Col1"] = $aCol[0];//J
                    $a_Flow["Page1_Row12_Col2"] = $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'] . ' - ' . $r_trespasser['Address'] . ' ' . $r_trespasser['ZIP'] . ' ' . $r_trespasser['City'];

                } else {

                    $a_Flow["Page1_Row12_Col1"] = $aCol[0];//J
                    $a_Flow["Page1_Row12_Col2"] = $aCol[1];//J

                }


                $a_Flow["Page1_Row13"] = $aRow[13];//J

                $aCol = explode("<col>", $aRow[14]);

                $a_Flow["Page1_Row14_Col1"] = $aCol[0];//L
                if (isset($aCol[1])) {
                    $a_Flow["Page1_Row14_Col2"] = $aCol[1];//L

                } else {
                    $a_Flow["Page1_Row14_Col2"] = "";//L
                }


                if (isset($aCol[2])) {
                    //$a_Flow["Page1_Row14_Col3"] = $aCol[2];//L
                    $str_Aggiuntiva = " " . $aCol[2];

                } else {

                    $str_Aggiuntiva = "";

                    //$a_Flow["Page1_Row14_Col3"] = "";

                }


                $a_Flow["Page1_Row15"] = $aRow[15] . $str_Aggiuntiva;//J
                $a_Flow["Page1_Row16"] = $aRow[16];//J
                $a_Flow["Page1_Row17"] = $aRow[17];//J


                $str_Protocollo = "";

                if ($r_Customer['ExternalRegistration'] == 1) {
                    $str_Protocollo = "Il presente atto, protocollo numero " . $r_Fine['ExternalProtocol'] . " del " . DateOutDB($r_Fine['ExternalDate']);
                    $str_Protocollo .= " firmato digitalmente dall'accertatore verbalizzante, e' elaborazione meccanografica conforme all'originale";
                    $str_Protocollo .= " e  depositato presso l'archivio di questo ente.";
                } else if ($_SESSION['cityid'] == 'U480') {
                    $str_Protocollo = "Il presente atto firmato dall'accertatore verbalizzante, e' elaborazione meccanografica conforme all'originale e depositato presso l'archivio di questo ente.";
                    $str_Protocollo .= " L'accertamento e' avvenuto in forza della Convenzione tra la Provincia di Imperia e la Provincia di Savona del 06/11/2017 Prot. n. 52257 per il servizio di funzioni di polizia stradale";
                    $str_Protocollo .= " finalizzate al contrasto dei fenomeni dell'eccesso di velocita' su alcune strade del territorio della Provincia di Savona.";
                }


                $a_Flow["Page1_Row18"] = $str_Protocollo;//J
                $a_Flow["Page1_Row19"] = $aRow[19];//C

                //$a_Flow["Page1_Row20"] = $aRow[20];//J
                $exp_aRow = explode("<br />", $aRow[20]);
                for ($i = 0; $i < 3; $i++) {
                    if (isset($exp_aRow[$i]))
                        $a_Flow["Page1_Row20_" . ($i + 1)] = $exp_aRow[$i];
                    else
                        $a_Flow["Page1_Row20_" . ($i + 1)] = "";
                }
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                ////
                ////
                ////    Fine page 2
                ////
                ////
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                $n_PageNumber++;
                $aRow = explode("<row>", $aMainPart[2]);

                //Arial 7 minuscolo
                $a_Flow["Page2_Row1"] = $aRow[1];//L
                $a_Flow["Page2_Row2"] = $aRow[2];//C
                $a_Flow["Page2_Row3"] = $aRow[3];//J
                $a_Flow["Page2_Row4"] = $aRow[4];//C
                $a_Flow["Page2_Row5"] = $aRow[5];//J
                $a_Flow["Page2_Row6"] = $aRow[6];//C
                $a_Flow["Page2_Row7"] = $aRow[7];//J
                $a_Flow["Page2_Row8"] = $aRow[8];//J
                $a_Flow["Page2_Row9"] = $aRow[9];//C
                $a_Flow["Page2_Row10"] = $aRow[10];//J
                //        $a_Flow["Page2_Row11"] = $aRow[11];//C
                $exp_aRow = explode("<br />", $aRow[11]);
                for ($i = 0; $i < 5; $i++) {
                    if (isset($exp_aRow[$i]))
                        $a_Flow["Page2_Row11_" . ($i + 1)] = $exp_aRow[$i];
                    else
                        $a_Flow["Page2_Row11_" . ($i + 1)] = "";
                }

                $a_Flow["Page2_Row12"] = $aRow[12];//J
                $a_Flow["Page2_Row13"] = $aRow[13];//C
                $a_Flow["Page2_Row14"] = $aRow[14];//J

                if ($chk_126Bis && $r_Fine['ReasonId']!=100) {
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ////
                    ////
                    ////    LicensePoint page 1
                    ////
                    ////
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    $n_PageNumber++;
                    $aRow = explode("<row>", $aMainPart[3]);

                    //Arial 10 minuscolo
                    $a_Flow["Page3_Row1"] = $aRow[1];//C
                    $a_Flow["Page3_Row2"] = $aRow[2];//C

                    //Arial 9 minuscolo
                    $aCol = explode("<col>", $aRow[3]);
                    $a_Flow["Page3_Row3_Col1"] = $aCol[0];//L
                    $a_Flow["Page3_Row3_Col2"] = $aCol[1];//J

                    $a_Flow["Page3_Row4"] = $aRow[4];//C
                    $a_Flow["Page3_Row5"] = $aRow[5];//L
                    $a_Flow["Page3_Row6"] = $aRow[6];//L
                    $a_Flow["Page3_Row7"] = $aRow[7];//L
                    $a_Flow["Page3_Row8"] = $aRow[8];//L
                    $a_Flow["Page3_Row9"] = $aRow[9];//L
                    $a_Flow["Page3_Row10"] = $aRow[10];//L
                    $a_Flow["Page3_Row11"] = $aRow[11];//L
                    $a_Flow["Page3_Row12"] = $aRow[12];//L
                    $a_Flow["Page3_Row13"] = $aRow[13];//L
                    $a_Flow["Page3_Row14"] = $aRow[14];//L
                    //            $a_Flow["Page3_Row15"] = $aRow[15];//J
                    $a_Flow["Page3_Row15"] = $aRow[15];//J
                    $exp_aRow = explode("<br />", $aRow[16]);
                    for ($i = 0; $i < 2; $i++) {
                        if (isset($exp_aRow[$i]))
                            $a_Flow["Page3_Row16_" . ($i + 1)] = $exp_aRow[$i];
                        else
                            $a_Flow["Page3_Row16_" . ($i + 1)] = "";
                    }
                    $a_Flow["Page3_Row17"] = $aRow[17];//J
                    $a_Flow["Page3_Row18"] = $aRow[18];//J

                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ////
                    ////
                    ////    LicensePoint page 2
                    ////
                    ////
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    $n_PageNumber++;
                    $aRow = explode("<row>", $aMainPart[4]);

                    //Arial 9 minuscolo
                    $a_Flow["Page4_Row1"] = $aRow[1];//J
                    $a_Flow["Page4_Row2"] = $aRow[2];//J
                    $a_Flow["Page4_Row3"] = $aRow[3];//J
                    $a_Flow["Page4_Row4"] = $aRow[4];//J
                    $a_Flow["Page4_Row5"] = $aRow[5];//J
                    $a_Flow["Page4_Row6"] = $aRow[6];//J
                    $a_Flow["Page4_Row7"] = $aRow[7];//J
                    $a_Flow["Page4_Row8"] = $aRow[8];//J
                } else {
                    $a_Flow["Page3_Row1"] = "";//C
                    $a_Flow["Page3_Row2"] = "";//C
                    $a_Flow["Page3_Row3_Col1"] = "";//L
                    $a_Flow["Page3_Row3_Col2"] = "";//J

                    $a_Flow["Page3_Row4"] = "";//C
                    $a_Flow["Page3_Row5"] = "";//L
                    $a_Flow["Page3_Row6"] = "";//L
                    $a_Flow["Page3_Row7"] = "";//L
                    $a_Flow["Page3_Row8"] = "";//L
                    $a_Flow["Page3_Row9"] = "";//L
                    $a_Flow["Page3_Row10"] = "";//L
                    $a_Flow["Page3_Row11"] = "";//L
                    $a_Flow["Page3_Row12"] = "";//L
                    $a_Flow["Page3_Row13"] = "";//L
                    $a_Flow["Page3_Row14"] = "";//L
                    $a_Flow["Page3_Row15"] = "";//L
                    for ($i = 0; $i < 2; $i++) {
                        $a_Flow["Page3_Row16_" . ($i + 1)] = "";//J
                    }

                    $a_Flow["Page3_Row17"] = "";//J
                    $a_Flow["Page3_Row18"] = "";//J

                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ////
                    ////
                    ////    LicensePoint page 2
                    ////
                    ////
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    $a_Flow["Page4_Row1"] = "";//J
                    $a_Flow["Page4_Row2"] = "";//J
                    $a_Flow["Page4_Row3"] = "";//J
                    $a_Flow["Page4_Row4"] = "";//J
                    $a_Flow["Page4_Row5"] = "";//J
                    $a_Flow["Page4_Row6"] = "";//J
                    $a_Flow["Page4_Row7"] = "";//J
                    $a_Flow["Page4_Row8"] = "";//J
                }


                if ($str_PostalType != "" && $FormTypeId!=101) {
                    $n_PageNumber++;
                    $a_Flow["Bollettino"] = "SI";

                    $a_Flow["Tipo_Bollettino"] = $str_PostalType;
                    $a_Flow["Autorizzazione"] = $str_PostalAuthorization;

                    $a_Flow["ContoCorrente"] = $r_Customer['NationalBankAccount'];
                    $a_Flow["IntestatarioConto"] = $r_Customer['NationalBankOwner'];
                    $a_Flow["Causale"] = "PAGAMENTO ACCERTAMENTO VIOLAZIONE C.D.S. Art." . $articleComplete;
                    $a_Flow["Protocollo"] = $r_Fine['Code'] . " DEL " . DateOutDB($r_Fine['FineDate']);
                    $a_Flow["ComuneAnno"] = $_SESSION['citytitle'] . " " . $ProtocolYear;
                    $a_Flow["NumeroCronologico"] = "CRON. " . $r_Fine['ProtocolId'] . " DEL " . $ProtocolYear;
                    $a_Flow["CognomeLimitato"] = substr(strtoupper($trespasser['CompanyName'] . $trespasser['Surname']), 0, 28);
                    $a_Flow["NomeLimitato"] = substr(strtoupper($trespasser['Name']), 0, 28);
                    $a_Flow["IndirizzoLimitato"] = substr(strtoupper($trespasser['Address']), 0, 28);
                    $a_Flow["CapLimitato"] = substr(strtoupper($trespasser['ZIP']), 0, 28);
                    $a_Flow["ComuneLimitato"] = substr(strtoupper($trespasser['City']), 0, 28);
                    $a_Flow["CodiceFiscale"] = strtoupper($trespasser['TaxCode']);
                    $a_Flow["ImportoNumeroBoll1"] = NumberDisplay($Bollettino1Fee);
                    $a_Flow["ImportoNumeroBoll2"] = NumberDisplay($Bollettino2Fee);
                    $a_Flow["IntestatarioLimitatoConto"] = substr(strtoupper($r_Customer['NationalBankOwner']), 0, 52);
                    $a_Flow["ImportoLettereBoll1"] = $numLit->converti_numero_bollettino($Bollettino1Fee);
                    $a_Flow["QuintoCampo_CodiceBoll1"] = $FifthField1;
                    $a_Flow["QuintoCampo_ImportoBoll1"] = $FifthFieldFee1;
                    $a_Flow["ImportoLettereBoll2"] = $numLit->converti_numero_bollettino($Bollettino2Fee);
                    $a_Flow["QuintoCampo_CodiceBoll2"] = $FifthField2;
                    $a_Flow["QuintoCampo_ImportoBoll2"] = $FifthFieldFee2;

                } else {
                    $a_Flow["Bollettino"] = "NO";

                    $a_Flow["Tipo_Bollettino"]              = "";
                    $a_Flow["Autorizzazione"]               = "";

                    $a_Flow["ContoCorrente"]                = "";
                    $a_Flow["IntestatarioConto"]            = "";
                    $a_Flow["Causale"]                      = "";
                    $a_Flow["Protocollo"]                   = "";
                    $a_Flow["ComuneAnno"]                   = "";
                    $a_Flow["NumeroCronologico"]            = "";
                    $a_Flow["CognomeLimitato"]              = "";
                    $a_Flow["NomeLimitato"]                 = "";
                    $a_Flow["IndirizzoLimitato"]            = "";
                    $a_Flow["CapLimitato"]                  = "";
                    $a_Flow["ComuneLimitato"]               = "";
                    $a_Flow["CodiceFiscale"]                = "";
                    $a_Flow["ImportoNumeroBoll1"]           = "";
                    $a_Flow["ImportoNumeroBoll2"]           = "";
                    $a_Flow["IntestatarioLimitatoConto"]    = "";
                    $a_Flow["ImportoLettereBoll1"]          = "";
                    $a_Flow["QuintoCampo_CodiceBoll1"]      = "";
                    $a_Flow["QuintoCampo_ImportoBoll1"]     = "";
                    $a_Flow["ImportoLettereBoll2"]          = "";
                    $a_Flow["QuintoCampo_CodiceBoll2"]      = "";
                    $a_Flow["QuintoCampo_ImportoBoll2"]     = "";

                }

                $a_Flow["ScadenzaBoll1"] = $scadenzaBoll1;
                $a_Flow["ScadenzaBoll2"] = $scadenzaBoll2;

                $a_Flow["NOME_FLUSSO"] = $DocumentationZip;
                $a_Flow["NUMERO_PAGINE"] = $n_PageNumber;



                if($r_Customer['PagoPAPayment']==1){

                    $str_PagoPa1Fee = AddNZeroToNumber(str_replace(".","",number_format((float)$Bollettino1Fee, 2, '.', '')),6);
                    $str_PagoPa2Fee = AddNZeroToNumber(str_replace(".","",number_format((float)$Bollettino2Fee, 2, '.', '')),6);

                    //Da cambiare prendendo la url dalla funzione
                    $url_PagoPAPage = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";

                    $a_Flow["BOL_PA_P1_intestatario_CCP"] = $r_Customer['PagoPACPPOwner'];
                    $a_Flow["BOL_PA_P1_oggetto_del_pagamento"] = $r_Customer['PagoPAPaymentSubject'];
                    $a_Flow["BOL_PA_P1_ente_creditore"] = $r_Customer['ManagerName'];
                    $a_Flow["BOL_PA_P1_CF_ente_creditore"] = $r_Customer['ManagerTaxCode'];
                    $a_Flow["BOL_PA_P1_CCP_ente_creditore"] = $r_Customer['NationalBankAccount'];
                    $a_Flow["BOL_PA_P1_settore_ente_creditore"] = "Anno ". $r_Fine['ProtocolYear'] ." targa ".$r_Fine['VehiclePlate'];
                    $a_Flow["BOL_PA_P1_info_ente_creditore"] = $r_Customer['PagoPAPaymentInfo'];
                    $a_Flow["BOL_PA_P1_cbill_Ente_Creditore"] = $r_Customer['PagoPACBILL'];
                    $a_Flow["BOL_PA_P1_nome_cognome_destinatario"] = str_replace("&","E",$str_FullNameSurname);
                    $a_Flow["BOL_PA_P1_CF_PIVA"] = $str_TaxCode;
                    $a_Flow["BOL_PA_P1_indirizzo_destinatario_completo"] = "";
                    $a_Flow["NUMAVVIUV1"] = "001".$r_Fine['PagoPA1'];
                    $a_Flow["NUMAVVIUV2"] = "001".$r_Fine['PagoPA2'];
                    $a_Flow["QRCODE1_LINK"] = $url_PagoPAPage.$r_Fine['PagoPA1'];
                    $a_Flow["QRCODE2_LINK"] = $url_PagoPAPage.$r_Fine['PagoPA2'];
                    $a_Flow["QRCODE1"] = "PAGOPA|002|001".$r_Fine['PagoPA1']."|00311260095|".$str_PagoPa1Fee;
                    $a_Flow["QRCODE2"] = "PAGOPA|002|001".$r_Fine['PagoPA2']."|00311260095|".$str_PagoPa2Fee;
                    $a_Flow["STRINGA_QRCODE1"] = $str_PaymentDay1;
                    $a_Flow["STRINGA_QRCODE2"] = $str_PaymentDay2;

                    $a_Flow["IMPORTO1"] = NumberDisplay($Bollettino1Fee);
                    $a_Flow["IMPORTO2"] = NumberDisplay($Bollettino2Fee);

                    $a_Flow["BOL_PA_P1_autorizzazione"] = $str_PostalAuthorization;

                }



                if ($checkFlowHeader == 0) {
                    foreach ($a_Flow as $key => $value) {
                        fwrite($myfile, $key . Chr(9));  //  TAB
                    }
                    fwrite($myfile, Chr(13) . Chr(10));  //  fine riga
                    $checkFlowHeader = 1;
                }

                foreach ($a_Flow as $value) {
                    fwrite($myfile, trim($value) . Chr(9));  //  TAB
                }
                fwrite($myfile, Chr(13) . Chr(10));  //  fine riga
                $a_Flow = null;

            } else {
                $rs_FineDocumentation = $rs->Select('FineDocumentation', "FineId=" . $FineId." AND DocumentationTypeId=2", "Id DESC");


                $pdf_union = new FPDI();

                while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
                    $FileName = $r_FineDocumentation['Documentation'];
                    $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $FileName);
                    for ($p = 1; $p <= $n_PageCount; $p++) {

                        $tmp_Page = $pdf_union->ImportPage($p);
                        $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);

                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';



                        $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_union->useTemplate($tmp_Page);
                    }

                }

                $a_CompressFine[] = $FineId . ".pdf";
                $pdf_union->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' . $FineId . ".pdf", "F");


                //todo post list mercury

            }

            if ($ultimate) {

                $rs_FineHistory = $rs->Select('FineHistory', "FineId=" . $r_Fine['Id']." AND NotificationTypeId=".$NotificationTypeId);

                if(mysqli_num_rows($rs_FineHistory)==0){
                    $aInsert = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['Id'], 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                        array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $controller['Id'], 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $DocumentationZip),
                        array('field' => 'FlowId', 'selector' => 'value', 'type' => 'int', 'value' => $FlowId, 'settype' => 'int'),
                        );
                    $rs->Insert('FineHistory', $aInsert);

                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int')
                    );
                    $rs->Update('Fine', $aUpdate, 'Id=' . $r_Fine['Id']);
                }
            }
        }
    }
    fclose($myfile);


    $aBlazon = explode(".",$_SESSION['blazon']);

    $zip = new ZipArchive();
    if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFile($path.$Documentation,$Documentation);
        $zip->addFile($_SESSION['blazon'],'blazon.'.$aBlazon[1]);

        if($str_ContractFine!=''){
            $file_Contract = fopen(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/Distinta_Verbali.txt', "w");

            $str_ContractFineHeader = 'FineId;TIPOLOGIA_STAMPA;TIPOLOGIA_ATTO;TIPOLOGIA_FLUSSO;CodiceComune;HeaderRow1;HeaderRow2;HeaderRow3;HeaderRow4;HeaderRow5;Spese_Anticipate;Intestatario_SMA;Numero_SMA;Mod23_Soggetto_Mittente;Mod23_Ente_Gestito;Mod23_Recapito_Soggetto;Mod23_Indirizzo_Soggetto;Mod23_Citta_Soggetto;Recipient_Row1;Recipient_Row2;Recipient_Row3_1;Recipient_Row3_2;Recipient_Row3_3;NOME_FLUSSO'.PHP_EOL;

            fwrite($file_Contract, $str_ContractFineHeader);
            fwrite($file_Contract, $str_ContractFine);

            fclose($file_Contract);


            $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/Distinta_Verbali.txt','Distinta_Verbali.txt');

            for($i=0; $i<count($a_CompressFine); $i++){
                $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' .$a_CompressFine[$i],$a_CompressFine[$i]);
            }
        }

        $zip->close();
        $_SESSION['Documentation'] = NATIONAL_FLOW_HTML.'/'.$_SESSION['cityid'].'/'.$DocumentationZip;

        if($str_ContractFine!=''){
            unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/Distinta_Verbali.txt');

            for($i=0; $i<count($a_CompressFine); $i++){
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/TMP/' .$a_CompressFine[$i]);
            }
        }
    } else {
        $str_Warning .= "Errore nella creazione dell\'archivio del flusso.<br>";
    }

    //Se il verbale è da inviare ad uno stampatore, chiama una funzione specifica definita nel parametri di configurazione
    //passandogli i riferimenti al file del flusso come parametri
    if($a_PrinterConf && $ultimate && PRODUCTION){
        if(!$phpFTP->connect()){
            $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
            header("location: ".$P);
            DIE;
        } else {
            //Riferimenti zip flusso
            $a_Flow = array(
                'LocalFile' => $path.$DocumentationZip,
                'RemoteFile' => isset($a_PrinterConf['Path']['VERBALI'])
                ? $a_PrinterConf['Path']['VERBALI'].'/'.$DocumentationZip
                : $DocumentationZip
            );
            
            if(call_user_func_array($a_PrinterConf['Function'], array($phpFTP, $a_Flow))){
                $a_UpdateFlow = array(
                    array('field'=>'UploadDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'))
                );
                
                $rs->Update('Flow', $a_UpdateFlow, "Id=$FlowId");
                
                $str_Success .= "Flusso caricato con successo.<br>";
            } else {
                $str_Warning .= 'Errore nell\'invio del flusso allo stampatore:<br>'.implode('<br>', $phpFTP->errors());
            }
            
            $phpFTP->disconnect();
        }
    }
    
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = $str_Warning;
    } else if ($ultimate) {
        $str_Success .= 'Azione eseguita con successo.';
        $_SESSION['Message']['Success'] = $str_Success;
    }
    
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $rs->End_Transaction();
}
 
