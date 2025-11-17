<?php
require_once(CLS . '/cls_literal_number.php');
require_once(INC."/function_postalCharge.php");

function buildHeader($r_Customer, $PrintDestinationFold){
    $str_Header = '<span style="line-height:1.1">';
    if($r_Customer['ManagerSector'] != ''){
        $str_Header .= $r_Customer['ManagerSector'] != '' ? $r_Customer['ManagerSector'] : '';
        $str_Header .= '<br>';
    }
    if ($PrintDestinationFold != ''){
        $str_Header .= '<span style="font-size:7rem">RESTITUZIONE PIEGO IN CASO DI MANCATO RECAPITO:<br>';
        $str_Header .= strtoupper($PrintDestinationFold).'</span><br>';
    }
    if($_SESSION['cityid']=="H452"){
        $str_Header .= 'Art.57 CPP e Art.11 c.1 L.a) e b) CDS<br>';
    } else if($r_Customer['ManagerAddress'] != ''){
        $str_Header .= $r_Customer['ManagerAddress'].'<br>';
    }
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ManagerPhone'] != ''){
        $str_Header .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Header .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Header .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Header .= $r_Customer['ManagerPhone'] ? 'TEL: '.$r_Customer['ManagerPhone'] : '';
    }
    return $str_Header.'</span>';
}

//Usato nel caso questo script sia incluso in un altro script che già inizializza una transazione
//In mysql, le azioni LOCK TABLES e UNLOCK TABLES, prima di essere eseguite, implicitano il commit di qualsiasi transazione attiva
//mgmt_fine_exp_ag_exe.php
if(!isset($b_DisableTransaction) && !$b_DisableTransaction){
    //BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
    //NOTA BENE: il blocco della tabella serve ad impedire di creare atti con cronologici duplicati
    //es: si creano verbali dinamici nello stesso momento in cui si creano verbali PEC
    $a_LockTables = array("LockedPage WRITE");
    $rs->LockTables($a_LockTables);
    
    $rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
        if ($r_Locked['Locked'] == 1) {
            $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
            header("location: ".$P);
            DIE;
        } else {
            $UpdateLockedPage = array(
                array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
            );
            $rs->Update('LockedPage', $UpdateLockedPage, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
        }
    } else {
        $InsertLockedPage = array(
            array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}"),
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Insert('LockedPage', $InsertLockedPage);
    }

    $rs->UnlockTables();
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}



$n_LanguageId = 1;
$ZoneId = 0;

$str_DocumentationPath = ($RegularPostalFine==1) ? NATIONAL_VIOLATION : NATIONAL_FINE;
$str_DocumentationHtml = ($RegularPostalFine==1) ? "violation" : "fine";



$StatusTypeId = ($RegularPostalFine==1) ? 8 : 15;
$DocumentationTypeId = ($RegularPostalFine==1) ? 1 : 2;
$NotificationTypeId = ($RegularPostalFine==1) ? 3 : 2;

if($CreationType==5 AND $ProtocolIdAssigned==0){
    $rs_Customer = $rs->Select("V_Customer", "CreationType=5 AND CityId='".$_SESSION['cityid']."'");
    $r_Customer  = mysqli_fetch_array($rs_Customer);
}

$FinePDFList = $r_Customer['FinePDFList'];


$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}


$rs_row = $rs->Select('DocumentationProtocol', "UserId=" . $_SESSION['userid'] . " AND CityId='" . $_SESSION['cityid'] . "'");
$n_row = mysqli_num_rows($rs_row);
if ($n_row > 0) {

    $_SESSION['Message'] = "Ci sono verbali da protocollare. Impossibile crearne altri.";
    header("location: " . $P);
    DIE;
}

$ultimate = CheckValue('ultimate', 'n');

$int_ContFine = 0;
if ($ultimate) {
    if ($r_Customer['DigitalSignature'] == 1 && PRODUCTION) {
        $ftp_connection = false;
        $chk_inp_file = false;
        $server = '89.96.225.74';
        $username = 'velox';
        $password = 'Cd28+PeB';

        echo "Controllo file presenti nella cartella del verbalizzante...<br />";

        echo "Login FTP...<br />";
        $checkUpload = 0;
        $conn = @ftp_connect($server);
        if ($conn) {
            $login = @ftp_login($conn, $username, $password);
            if ($login) {

                $ftp_connection = true;

                echo 'Connessione riuscita<br />';
                $path = "/" . $_SESSION['username'];

                $origin = ftp_pwd($conn);

                if (@ftp_chdir($conn, $_SESSION['username'])) {
                    $filelist = ftp_rawlist($conn, "/" . $_SESSION['username'] . "/");

                    for ($i = 0; $i < count($filelist); $i++) {
                        $b_Find = strpos($filelist[$i], ".p7m");
                        if ($b_Find) {
                            $_SESSION['Message'] = "Ci sono file firmati presenti nella cartella " . $_SESSION['username'] . ". Eliminarli e riprovare.";
                            echo "<script>window.location='" . $P . "?TypePlate=" . $s_TypePlate . "'</script>";
                            DIE;
                        }
                        $b_Find = strpos($filelist[$i], ".pdf");
                        if ($b_Find) {
                            $_SESSION['Message'] = "Ci sono file pdf presenti nella cartella " . $_SESSION['username'] . ". Eliminarli e riprovare.";
                            echo "<script>window.location='" . $P . "?TypePlate=" . $s_TypePlate . "'</script>";
                            DIE;
                        }
                    }


                } else {
                    $_SESSION['Message'] = "Utente non abilitato alla firma o cartella inesistente.";
                    echo "<script>window.location='" . $P . "?TypePlate=" . $s_TypePlate . "'</script>";
                    DIE;
                }
            } else {
                $_SESSION['Message'] = "Login fallita.";
                echo "<script>window.location='" . $P . "?TypePlate=" . $s_TypePlate . "'</script>";
                DIE;
            }

        } else {
            $_SESSION['Message'] = "Connessione fallita.";
            echo "<script>window.location='" . $P . "?TypePlate=" . $s_TypePlate . "'</script>";
            DIE;
        }
    }
}

$str_Speed = "";


$a_DocumentationFineZip = array();
$a_FineId = array();

$a_Detector = array();
$a_SpeedLengthAverage = array();
$a_DetectorPosition = array();
$a_DetectorKind = array();

$a_GenreLetter = array("D" => "Spett.le", "M" => "Sig.", "F" => "Sig.ra");
/*
    La segnalazione della postazione di rilevamento della velocita' dei veicoli e' stata effettuata mediante
    esposizione di appositi cartelli segnaletici informativi, posizionati prima della postazione di rilevamento,
    ai sensi del D. Interm. Del 15 agosto 2007, e in modo da garantire l'avvistamento della postazione di rilevamento
    della velocita' dei veicoli e la salvaguardia della sicurezza della circolazione stradale.
*/


if ($_SESSION['cityid'] == 'U480') {
    $str_Detector = "
    Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. 
    La velocita' rilevata dall'apparecchiatura elettronica e' stata di {SpeedControl} Km/h e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), 
    ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. 
    L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e 
    precisamente {Kind} matricola {Code} - {Ratification}{AdditionalTextIta}";
} else if ($_SESSION['cityid'] == 'A446') {
    $str_Detector = "
    Il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. 
    La velocita' rilevata dall'apparecchiatura elettronica funzionante in presenza di quest'organo di Polizia e' stata di {SpeedControl} km/h, e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} km/h, ne consegue la violazione ivi indicata. 
    L'infrazione e' stata accertata mediante dispositivo di controllo di tipo omologato {TimeTypeId} e 
    precisamente {Kind} matricola {Code} {Ratification}{AdditionalTextIta}"; 
} else if ($_SESSION['cityid'] == 'D925') {
	$str_Detector = "
    Dettaglio della violazione: la velocita' rilevata dall'organo di polizia e' stata di {SpeedControl} Km/h e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), 
    ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. 
    L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e 
    precisamente {Kind} matricola {Code} - {Ratification}{AdditionalTextIta}";
} else {
    $str_Detector = "
	Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. 
    La velocita' rilevata dall'organo di polizia e' stata di {SpeedControl} Km/h e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), 
    ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. 
    L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e 
    precisamente {Kind} matricola {Code} - {Ratification}{AdditionalTextIta}";
}



//Detector di velocità diversi da SCOUT SPEED
$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0 AND DetectorTypeId=1 AND Kind!='SCOUT SPEED'");
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


$str_Detector = "
La velocita' riscontrata dallo strumento in modalita' dinamica e' di {SpeedControl} Km/h, in cui vige il limite di velocita' di {SpeedLimit} Km/h, superando quindi il limite imposto di {SpeedExcess} Km/h. 
La velocita' rilevata dallo strumento e' stata ridotta del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96). Rilevatore utilizzato: {Kind} matricola {Code} {Ratification}{AdditionalTextIta}
     ";

//Detector di velocità uguali a SCOUT SPEED
$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0 AND DetectorTypeId=1 AND Kind='SCOUT SPEED'");
while ($r_Detector = mysqli_fetch_array($rs_Detector)) {

    $DetectorKind = $r_Detector['Kind'];
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

//Detector di tipo semaforo
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


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$ProtocolNumber = 0;





if ($_SESSION['usertype'] == 2) {
    $rs_Time = $rs->SelectQuery("SELECT MAX( ControllerTime ) ControllerTime FROM Fine WHERE ControllerDate='" . date("Y-m-d") . "'");
    $r_Time = mysqli_num_rows($rs_Time);
    $Time = ($r_Time['ControllerTime'] == "") ? date("H:i:s") : $r_Time['ControllerTime'];
}

$NotificationDate = (strlen(trim($CreationDate)) == 0) ? date("Y-m-d") : DateInDB($CreationDate);


$a_Lan = unserialize(LANGUAGE);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
$a_AdditionalMass = unserialize(ADDITIONAL_MASS);


//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$PrintDestinationFold AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_FoldReturn = $r_PrintParameter['NationalFineFoldReturn'] ?? '';
$str_PostalAuthorization = trim($r_PrintParameter['NationalPostalAuthorization']) ?? '';
////////////////////////////////////////////////

$b_PrintBill = !empty($r_Customer['NationalPostalType']);

if (isset($_POST['checkbox'])) {
    $chk_warning = false;
    foreach ($_POST['checkbox'] as $FineId) {
        
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);


        if ($r_Fine['StatusTypeId'] != 7 && $r_Fine['StatusTypeId'] != 10 && $r_Fine['StatusTypeId'] != 14) {
            $chk_warning = true;
            $_SESSION['Message'] = "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Controllare e riprovare.";
        }

        $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);

        if ($trespasser['CountryId'] == 'ZZZZ') {
            $chk_warning = true;
            $_SESSION['Message'] = "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Stato del trasgressore non presente.";

        }

        if ($r_Fine['ControllerId'] == "" && $_SESSION['usertype'] != 2) {
            $chk_warning = true;
            $_SESSION['Message'] = "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Manca il verbalizzante.";
        } else if (($r_Fine['ControllerId'] == "" && $_SESSION['usertype'] == 2 && $ultimate) || ($_SESSION['controllerid']!=0 && $ultimate && $CreationType==5)) {
            $ControllerDate = date("Y-m-d");
            $ControllerTime = date("H:i:s", strtotime('+41 seconds', strtotime($Time)));

            $Time = $ControllerTime;

            $a_Fine = array(
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['controllerid'], 'settype' => 'int'),
                array('field' => 'ControllerDate', 'selector' => 'value', 'type' => 'date', 'value' => $ControllerDate),
                array('field' => 'ControllerTime', 'selector' => 'value', 'type' => 'str', 'value' => $ControllerTime),
            );

            $rs->Update('Fine', $a_Fine, "Id=" . $FineId);
        }


        if ($chk_warning) {
            $aUpdate = array(
                array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
            );
            $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");

            header("location: " . $P . $str_BackPage);
            DIE;
        }
    }

    $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);

    if ($FinePDFList) $pdf_union = new FPDI();

    $pdf->TemporaryPrint = $ultimate;
    $pdf->NationalFine = 1;
    $pdf->CustomerFooter = 0;

    //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);


    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Violation');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 8));

    $pdf->SetMargins(10, 10, 10);


    foreach ($_POST['checkbox'] as $FineId) {
        
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);

        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {

            $chk_180            = false;
            $chk_126Bis         = false;
            $chk_ReducedPayment = false;
            $n_LicensePoint     = 0;
            $n_TotPartialFee    = 0;
            $n_TotFee           = 0;
            $n_TotMaxFee        = 0;


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




            //In questo caso "Id" corrisponde al RuleTypeId
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "' AND Id=".$_SESSION['ruletypeid']);
            $r_RuleType = mysqli_fetch_array($rs_RuleType);


            $RuleTypeId = $r_RuleType['Id'];

            $ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            $FormTypeId = $r_RuleType['NationalFormId'];

            $a_PrintObject = explode("*", $r_RuleType['PrintObject' . $a_Lan[$n_LanguageId]]);

            //testo invito in AG - - Garlasco, Albuzzano e Borgo San Siro
            if((($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2") || ($r_Fine['Article']==80 && $r_Fine['Paragraph']=="14")) && $r_Fine['KindSendDate']!=''){
                $rs_Form= $rs->Select('Form', "FormTypeId=101 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            } else if($r_Fine['Article']==142){ //personalizzazione testo velocità per Arcola
                $rs_Form= $rs->Select('Form', "FormTypeId=102 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];
            }

            //Testo statico del verbale con spese ridotte stampato per Garlasco, Albuzzano e Borgo San Siro
            // in conseguenza della lettera di invio bonario.
            if($CreationType==5){
                $rs_Form= $rs->Select('Form', "FormTypeId=100 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            }


            $ChargeTotalFee = 0;
            $NotificationFee = 0;
            $ResearchFee = 0;

            //Se il forfettario è impostato, reimposta la variabile
            if ($r_Customer['NationalTotalFee'] > 0) $ChargeTotalFee = $r_Customer['NationalTotalFee'];
            //Se le spese di notifica sono impostate, reimposta le variabili
            else {
                $NotificationFee = $r_Customer['NationalNotificationFee'];
                $ResearchFee = $r_Customer['NationalResearchFee'];
            }
            
            


            $int_ContFine++;
            if ($ultimate && $ProtocolNumber > 0) {
                $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);

                $pdf->TemporaryPrint = $ultimate;
                $pdf->NationalFine = 1;
                $pdf->CustomerFooter = 0;
                //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);


                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($_SESSION['citytitle']);
                $pdf->SetTitle('Violation');
                $pdf->SetSubject('');
                $pdf->SetKeywords('');
                $pdf->setHeaderFont(array('helvetica', '', 8));
                $pdf->setFooterFont(array('helvetica', '', 8));

            }

            $pdf->Temporary();
            $pdf->RightHeader = true;
            $pdf->setPrintHeader(true);
            if($FormTypeId != 101){
                //TESTO STAMPA HEADER IN ALTO A DX
                $pdf->PrintObject1 = $a_PrintObject[0]; //Riga 1
                $pdf->PrintObject2 = $a_PrintObject[1]; //Riga 2
                $pdf->PrintObject3 = $a_PrintObject[2]; //Riga 3
            }
            $pdf->SetMargins(10, 10, 10);

            $page_format = ($int_ContFine > 1) ? array('Rotate' => 45) : array();


            $pdf->AddPage('P', $page_format);


            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Image($_SESSION['blazon'], 7, 7, 12, 17);

            $ManagerName = $r_Customer['ManagerName'];
            $pdf->customer = $ManagerName;


            $pdf->SetFont('helvetica', '', 8, '', true);
            
            $pdf->writeHTMLCell(73, 0, 22, 5.5, '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
            $pdf->LN(3);
            
            $pdf->writeHTMLCell(73, 0, 22, '', buildHeader($r_Customer, $str_FoldReturn), 0, 0, 1, true, 'L', true);
            
            if($r_Customer['ManagerPEC'] != ''){
                $pdf->MultiCell(85, 0, 'PEC: '.$r_Customer['ManagerPEC'], 0, 'L', 1, 1, 10, 30, true);
            }
            
            $pdf->LN(3);


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


            if($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2"){
                if($InsuranceDate!=""){
                    $n_Day = DateDiff("D", $r_Fine['ExpirationDate'], DateInDB($InsuranceDate));

                    if($n_Day<=30){
                        $r_Fine['Fee'] = $r_Fine['Fee'] * FINE_INSURANCE_REDUCED;
                        $r_Fine['MaxFee'] = $r_Fine['MaxFee'] * FINE_INSURANCE_REDUCED;


                        $a_FineArticle = array(
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_Fine['Fee'], 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_Fine['MaxFee'], 'settype' => 'flt'),
                        );

                    }
                }
            }


            //Nel caso in cui l'articolo deve cambiare ed è diverso da quello del verbale, vengono ricalcolati e aggiornati gli importi
            //ArticleSelect viene passato da mgmt_violation_act.php
            $NewArticle = CheckValue('ArticleSelect','n');
            if($NewArticle > 0 && $NewArticle != $r_Fine['ArticleId']){
                $rs_NewArticle = $rs->SelectQuery("SELECT * FROM Article A JOIN ArticleTariff AT ON A.Id=AT.ArticleId AND AT.Year={$r_Fine['ProtocolYear']} WHERE A.Id=$NewArticle");
                $r_NewArticle = mysqli_fetch_assoc($rs_NewArticle);
                
                //Se prevista riapplico la maggiorazione
                if($r_NewArticle['AdditionalNight']){
                    $aTime = explode(":",$r_Fine['FineTime']);
                    if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
                        $r_NewArticle['Fee'] = $r_NewArticle['Fee'] + round($r_NewArticle['Fee']/FINE_NIGHT,2);
                        $r_NewArticle['MaxFee'] = $r_NewArticle['MaxFee'] + round($r_NewArticle['MaxFee']/FINE_NIGHT,2);
                    }
                }
                
                $n_TotFee += $r_NewArticle['Fee'];
                $n_TotMaxFee += $r_NewArticle['MaxFee'];
                
                if ($r_NewArticle['ReducedPayment'] == 1) {
                    $chk_ReducedPayment = true;
                    $n_TotPartialFee += $r_NewArticle['Fee'] * FINE_PARTIAL;
                } else {
                    $n_TotPartialFee += $r_NewArticle['Fee'];
                }
                
                if($ultimate){
                    $a_UpdateFineArticle = array(
                        array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$NewArticle,'settype'=>'int'),
                        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_NewArticle['Fee'],'settype'=>'flt'),
                        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_NewArticle['MaxFee'],'settype'=>'flt'),
                    );
                    
                    $rs->Update('FineArticle', $a_UpdateFineArticle, "FineId=$FineId");
                }
            } else {
                $n_TotFee += $r_Fine['Fee'];
                $n_TotMaxFee += $r_Fine['MaxFee'];
                if ($r_ArticleTariff['ReducedPayment'] == 1) {
                    $chk_ReducedPayment = true;
                    $n_TotPartialFee += $r_Fine['Fee'] * FINE_PARTIAL;
                } else {
                    $n_TotPartialFee += $r_Fine['Fee'];
                }
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

            //Il creation type non esiste nella frm_create_fine.php, quindi virtualmente è sempre nullo (0)
            $postalcharge = getPostalCharge($_SESSION['cityid'],$NotificationDate);
            
            if($CreationType==5){
                $ResearchFee = $r_Customer['NationalResearchFee'];
                $NotificationFee = $r_Customer['NationalTotalFee'];

                $AdditionalFee = $NotificationFee + $ResearchFee;
            } else {
                $ChargeTotalFee = $r_Customer['NationalTotalFee'];
                $NotificationFee = $r_Customer['NationalNotificationFee'];
                $ResearchFee = $r_Customer['NationalResearchFee'];
                
                //Prima di arrivare a questo blocco, le spese forfettario, notifica e ricerca sono già state caricate nelle variabili
                //Se è spedito per posta ordinaria, le spese di ricerca vengono azzerate
                if($RegularPostalFine==1){
                    $ResearchFee = 0.00;
                    $NotificationFee = $postalcharge['ReminderZone' . $ZoneId];
                } else {
                    //Se il forfettario è impostato, le spese di ricerca sono uguali alla differenza tra il forfettario e le spese di notifica
                    if ($ChargeTotalFee > 0) {
                        $ResearchFee = $ChargeTotalFee - $NotificationFee;
                    }
                }
            }

            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;

            $AdditionalFee = $NotificationFee + $ResearchFee;

            $CANFee = $postalcharge['CanFee'];
            $CADFee = $postalcharge['CadFee'];

            $AdditionalFeeCAN = $AdditionalFee + $CANFee;
            $AdditionalFeeCAD = $AdditionalFee + $CADFee;
            
            //SCELTA VERBALIZZANTE///////////////////////////////////////////////////////////
            $ChiefControllerName = '';
            $ChiefControllerId = null;
            if($r_Fine['ReportChiefControllerId'] > 0){
                //Se è presente il verbalizzante nel verbale usa quello
                $ChiefControllerId = $r_Fine['ReportChiefControllerId'];
            } else if($r_Fine['UIFineChiefControllerId'] > 0){
                //Altrimenti se è presente un "verbalizzante stampa" usa quello
                $ChiefControllerId = $r_Fine['UIFineChiefControllerId'];
            } else if($SelectChiefControllerId > 0){
                //Altrimenti se è scelto da tendina usa quello
                $ChiefControllerId = $SelectChiefControllerId;
            }
            
            if($ChiefControllerId > 0){
                $rs_ChiefController = $rs->Select('Controller', "Id=$ChiefControllerId");
                $r_ChiefController = mysqli_fetch_array($rs_ChiefController);
                $ChiefControllerName = trim($r_ChiefController['Qualification'] . " " . $r_ChiefController['Name']);
            } else if($r_Customer['CityUnion'] > 1) {
                //TODO caso riciclato, possibilmente da rivedere
                //Se non c'è il verbalizzante e CityUnion > 1, tento di recuperare un verbalizzante non disabilitato, con firma abilitata e per l'ente in sessione
                $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Sign !='' AND Disabled=0 AND Locality='" . $r_Fine['Locality'] . "'");
                $r_ChiefController = mysqli_fetch_array($rs_ChiefController);
                $ChiefControllerName = trim($r_ChiefController['Qualification'] . " " . $r_ChiefController['Name']);
            }
            ///////////////////////////////////////////////////////////////////////////////
            
//             $b_ValidChiefController = true;
//             //(caso rinotifica) Controlla se era già stato salvato il verbalizzante scelto da interfaccia in crea verbali e se è ancora valido, se sì usa quest'ultimo nella variabile {ChiefControllerName}
//             if ($r_Fine['UIFineChiefControllerId'] > 0) {
//                 $str_DateWhere = " AND ('$NotificationDate' >= FromDate OR FromDate IS NULL) AND ('$NotificationDate' <= ToDate OR ToDate IS NULL)";
//                 $rs_ChiefController = $rs->Select('Controller', "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Id =" . $r_Fine['UIFineChiefControllerId'].$str_DateWhere);
                
//                 if(mysqli_num_rows($rs_ChiefController) > 0){
//                     $r_ChiefController = mysqli_fetch_array($rs_ChiefController);
//                     $ChiefControllerName = trim($r_ChiefController['Qualification']." ".$r_ChiefController['Name']);
//                 } else $b_ValidChiefController = false;
//             } 

            $str_Speed = "";
            $str_TrafficLight = "";
            $SpeedLengthAverage = 0;
            $FineTime = $r_Fine['FineTime'];
            $SpeedExcess = 0;
            if ($r_Fine['Speed'] > 0 AND $r_Fine['Speed']!='0.00') {

                $SpeedExcess = intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit']);

                $str_Speed = " " . $a_Detector[$r_Fine['DetectorId']];
                $str_Speed = str_replace("{Speed}", intval($r_Fine['Speed']), $str_Speed);
                $str_Speed = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_Speed);
                $str_Speed = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_Speed);
                $str_Speed = str_replace("{SpeedExcess}", intval($SpeedExcess), $str_Speed);


                $str_Speed = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_Speed);
                $SpeedLengthAverage = $a_SpeedLengthAverage[$r_Fine['DetectorId']];

            } else if($r_Fine['TimeTLightFirst'] > 0) {
                $str_TrafficLight = $a_Detector[$r_Fine['DetectorId']];
            }

            if($r_Fine['FineTypeId']==4) $FormTypeId=40;

            $str_ProtocolLetter = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            $Content = getStaticContent($FormTypeId,$_SESSION['cityid'],1, $n_LanguageId);

            if ($r_ArticleTariff['AdditionalSanctionId'] > 0) {
                $rs_AdditionalSanction = $rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']);
                $r_AdditionalSanction = mysqli_fetch_array($rs_AdditionalSanction);

                $Content = str_replace("{AdditionalSanctionId}", $str_TrafficLight . " SANZIONE ACCESSORIA: " . $r_AdditionalSanction['Title' . $a_Lan[$n_LanguageId]], $Content);

            } else {
                $Content = str_replace("{AdditionalSanctionId}", $str_TrafficLight . "", $Content);
            }


            $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
            $Content = str_replace("{FineTime}", TimeOutDB($FineTime), $Content);
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

                    if ($r_AdditionalArticle['126Bis'] == 1) {
                        $chk_126Bis = true;
                        $n_LicensePoint += $r_AdditionalArticle['LicensePoint'];
                    }


                    if($r_AdditionalArticle['Article']==193 && $r_AdditionalArticle['Paragraph']=="2"){
                        if($InsuranceDate!=""){
                            $n_Day = DateDiff("D", $r_Fine['ExpirationDate'], DateInDB($InsuranceDate));
                            if($n_Day<=30){
                                $r_AdditionalArticle['Fee'] = $r_AdditionalArticle['Fee'] * FINE_INSURANCE_REDUCED;
                                $r_AdditionalArticle['MaxFee'] = $r_AdditionalArticle['MaxFee'] * FINE_INSURANCE_REDUCED;

                                $a_FineAdditionalArticle = array(
                                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_AdditionalArticle['Fee'], 'settype' => 'flt'),
                                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $r_AdditionalArticle['MaxFee'], 'settype' => 'flt'),
                                );


                            }
                        }
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
                    $str_ArticleDescription .= " " . $str_AdditionalArticleDescription;


                }

            }

            $Content = str_replace("{ArticleId}", $str_ArticleId .$str_ListArticle, $Content);


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

            $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ReasonDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ReasonDescription' . $a_Lan[$n_LanguageId]];

            if ($CreationType==5){
                $Content = str_replace("La violazione non e' stata immediatamente contestata" ,"Verbale redatto in data odierna a seguito di invito n ".$r_Fine['Code'].".",$Content);
                $Content = str_replace("{ReasonId}", "", $Content);
            } else if (trim($str_ReasonDescription)==""){
                $Content = str_replace("La violazione non e' stata immediatamente contestata:" ,"",$Content);
                $Content = str_replace("La violazione non e' stata immediatamente contestata" ,"",$Content);

                $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);
            } else {
                $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);
            }

            if($r_Fine['DetectorId']>0){
                $Content = str_replace("{DetectorPosition}", $a_DetectorPosition[$r_Fine['DetectorId']], $Content);
            } else {
                $Content = str_replace("{DetectorPosition}", " ", $Content);
            }


            if ($chk_126Bis) {

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


            if ($SpeedLengthAverage > 0) {
                $SpeedTimeAverage = $SpeedLengthAverage * 3.6 / $r_Fine['SpeedControl'];

                $Content = str_replace("{SpeedTimeAverage}", NumberDisplay($SpeedTimeAverage), $Content);
                $Content = str_replace("{SpeedLengthAverage}", $SpeedLengthAverage, $Content);
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


            $Content = str_replace("{CurrentDate}", DateOutDB($NotificationDate), $Content);
            $Content = str_replace("{CurrentTime}", date("H:i"), $Content);


            $Content = str_replace("{IuvCode}", $r_Fine['IuvCode'], $Content);
            
            
            if ($r_Customer['ManagerSignName'] == "") {
                if ($r_Customer['CityUnion'] > 1) {
                    $Content = str_replace("{Date}", $r_Fine['CityTitle'] . ", " . DateOutDB($NotificationDate), $Content);
                } else {
                    $Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . DateOutDB($NotificationDate), $Content);
                }
            } else {
                $Content = str_replace("{Date}", $r_Customer['ManagerSignName'] . ", " . DateOutDB($NotificationDate), $Content);
            }
            
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
            $Content = str_replace("{TotalMaxFeeCAD}", NumberDisplay($TotalMaxFeeCAD), $Content);
            $Content = str_replace("{TotalMaxFeeCAN}", NumberDisplay($TotalMaxFeeCAN), $Content);

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

			$str_ControllerConvalidation = "Convalidato  il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$str_ControllerName. " Matr. ". $str_ControllerCode;
            //$str_ControllerConvalidation = "Convalidato  previa visione dei fotogrammi il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime;
            $str_ChiefConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$ChiefControllerName;
			//$str_ChiefConvalidation = "Convalidato previa visione dei fotogrammi il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime;


            $Content = str_replace("{ControllerConvalidation}", $str_ControllerConvalidation, $Content);

            if ($_SESSION['cityid'] == 'D925') {
                //se il testo è per la velocità               
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

            } else if ($_SESSION['cityid'] == 'U882') {
                $str_ChiefConvalidation = "";
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

                $str_Province = (isset($r_trespasser['Province']) && $r_trespasser['Province']!='') ?  " (".$r_trespasser['Province'].")" : "";

                $Content = str_replace("{TrespasserAddress} {TrespasserCity} ({TrespasserProvince})<col>", $r_trespasser['Address']. " ".$r_trespasser['City'].$str_Province."<col>", $Content);
                
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
                $Content = str_replace("{TrespasserBornCity}", $trespasser['BornPlace'], $Content);
            } else {
                $Content = str_replace(" il {TrespasserBornDate}", $str_ReceiveDate, $Content);
                $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
            }

            //StreetNumber Ladder Indoor Plan
            $Content = str_replace("{TrespasserAddress}", $trespasser['Address'] . " " . $trespasser['ZIP'], $Content);
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

                if (strlen(trim($trespasser['BornPlace'])) > 0 && !((strlen($trespasser['BornDate']) != 10) || (is_null($trespasser['BornDate'])))) {
                    $Content = str_replace("{TrespasserBD}", DateOutDB($trespasser['BornDate']), $Content);
                } else {
                    $Content = str_replace("{TrespasserBD}", "________________", $Content);
                }

                $Content = str_replace("{TrespasserR}", $trespasser['City'] . " " . $trespasser['Address'], $Content);
                $Content = str_replace("{TrespasserP}", $trespasser['Province'], $Content);

                $Content = str_replace("{TrespasserC}", "____________________________________________", $Content);
                $Content = str_replace("{TrespasserCC}", "___________________________________", $Content);
                $Content = str_replace("{TrespasserCA}", "Via _______________________________________ n&#176; __", $Content);
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

            $Content = str_replace("{Code}", $r_Fine['Code'], $Content);
            if ($ultimate) {

                $RndCode = "";
                for ($i = 0; $i < 5; $i++) {
                    $n = rand(1, 24);
                    $RndCode .= substr($strCode, $n, 1);
                    $n = rand(0, 9);
                    $RndCode .= $n;
                }


                if ($ProtocolNumber == 0){
                    //Usato nel caso questo script sia incluso in un altro script che già inizializza una transazione
                    //mgmt_fine_exp_ag_exe.php
                    if(!isset($b_DisableTransaction) && !$b_DisableTransaction)
                        $rs->Start_Transaction();
                }

                if($r_Fine['ProtocolId'] > 0) $ProtocolNumber = $r_Fine['ProtocolId'];
                else if ($r_Fine['ProtocolIdAssigned'] == 0) {
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);

                    $r_Protocol = mysqli_fetch_array($rs_Protocol);

                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

                    $ProtocolNumber = $n_Protocol + 1;

                } else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];

                $Content = str_replace("{ProtocolId}", $ProtocolNumber, $Content);

                $strProtocolNumber = "";
                for ($k = strlen((string)$ProtocolNumber); $k < 9; $k++) {
                    $strProtocolNumber .= "0";
                }
                $strProtocolNumber .= $ProtocolNumber;

                $Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_" . $RndCode . ".pdf";
                $a_DocumentationFineZip[] = $Documentation;
                $a_FineId[] = $r_Fine['Id'];

            } else {
                if ($ProtocolNumber == 0) {
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);

                    $r_Protocol = mysqli_fetch_array($rs_Protocol);

                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

                    $ProtocolNumber = $n_Protocol;

                }



                $ProtocolNumber++;
                $Content = str_replace("{ProtocolId}", $ProtocolNumber . " - PROVV", $Content);
            }


            if ($r_Fine['TrespasserTypeId'] == 11) {
                $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=10");
                $r_trespasser = mysqli_fetch_array($rs_trespasser);

                $Content = str_replace("{TrespasserRentName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
                $Content = str_replace("{DateRent}", DateOutDB($r_Fine['ReceiveDate']), $Content);

            }


            if ($r_ArticleTariff['ReducedPayment']) {
                $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";

            } else {
                $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
            }

            $Content = str_replace("{PagoPA1}", $r_Fine['PagoPA1']. " per ".$str_PaymentDay1, $Content);
            $Content = str_replace("{PagoPA2}", $r_Fine['PagoPA2']. " per ".$str_PaymentDay2, $Content);








            $aMainPart = explode("<main_part>", $Content);
            $aRow = explode("<row>", $aMainPart[1]);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[1]), 0, 0, 1, true, 'L', true);
            $pdf->LN(6);

            if ($r_Customer['PDFRefPrint']) {
                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[2]), 0, 0, 1, true, 'L', true);
                $pdf->LN(9);
            } else {
                $pdf->writeHTMLCell(190, 0, 10, '', '', 0, 0, 1, true, 'L', true);
                $pdf->LN(9);
            }




            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
            );
            $pdf->writeHTMLCell(100, 0, 110, '', $a_GenreLetter[$trespasser['Genre']] . " " . substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 35), 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(100, 0, 110, '', $str_TrespasserAddress, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(100, 0, 110, '', $trespasser['ZIP'] . ' ' . $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ")", 0, 0, 1, true, 'L', true);
            $pdf->LN(16);


            if($r_Fine['FineTypeId']==4){
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                ////
                ////
                ////    Contract fine
                ////
                ////
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                $pdf->SetFont('helvetica', '', 9);

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[3]), 0, 0, 1, true, 'C', true);
                $pdf->LN(10);


                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[4]), 0, 0, 1, true, 'J', true);
                $pdf->LN(25);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[5]), 0, 0, 1, true, 'C', true);
                $pdf->LN(5);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[6]), 0, 0, 1, true, 'J', true);
                $pdf->LN(10);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[7]), 0, 0, 1, true, 'C', true);
                $pdf->LN(5);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[8]), 0, 0, 1, true, 'J', true);
                $pdf->LN(40);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 35, '', StringOutDB($aRow[9]), 0, 0, 1, true, 'C', true);
                $pdf->LN(30);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[10]), 0, 0, 1, true, 'J', true);

            }else{
                $pdf->SetFont('helvetica', '', 8);

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[3]), 0, 0, 1, true, 'J', true);
                $pdf->LN();
                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[4]), 0, 0, 1, true, 'C', true);
                $pdf->LN();
                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[5]), 0, 0, 1, true, 'J', true);
                $pdf->LN();

                $a_Payment = explode("<PAYMENT>", $aRow[6]);
                $a_PaymentType = explode("<PAYMENTTYPE>", $aRow[7]);

                if ($r_ArticleTariff['ReducedPayment']) {
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_Payment[1]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    if ($r_Customer['LumpSum'] == 1) {
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[1]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[2]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[3]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                    } else {
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[10]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                    }

                }


                if (!$r_ArticleTariff['ReducedPayment']) $a_Payment[2] = str_replace("DAL 6", "DAL 1", $a_Payment[2]);
                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_Payment[2]), 0, 0, 1, true, 'L', true);
                $pdf->LN();
                if ($r_Customer['LumpSum'] == 1) {
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[4]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[5]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[6]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();
                } else {
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[11]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();
                }


                if (!$r_ArticleTariff['ReducedPayment']) {
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_Payment[3]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();
                    if ($r_Customer['LumpSum'] == 1) {
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[7]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[8]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[9]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                    } else {
                        $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($a_PaymentType[12]), 0, 0, 1, true, 'J', true);
                        $pdf->LN();
                    }
                }
                if ($r_Customer['LumpSum'] == 1) {
                    $pdf->SetFont('helvetica', '', 6);
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[8]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[9]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(6);
                }

                $pdf->LN(4);


                $aCol = explode("<col>", $aRow[10]);
                $y = $pdf->getY();
                if ($r_Fine['TrespasserTypeId'] == 11) {
                    $pdf->writeHTMLCell(30, 5, 10, $y, StringOutDB($aCol[2]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(150, 5, 55, $y, StringOutDB($aCol[3]), 0, 0, 1, true, 'J', true);
                } else {
                    $pdf->writeHTMLCell(50, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(130, 5, 55, $y, StringOutDB($aCol[1]), 0, 0, 1, true, 'J', true);
                }
                $pdf->LN(5);


                $aCol = explode("<col>", $aRow[11]);
                $y = $pdf->getY();
                if ($r_Fine['TrespasserTypeId'] == 11) {
                    $pdf->writeHTMLCell(50, 5, 10, $y, StringOutDB($aCol[2]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(130, 5, 55, $y, StringOutDB($aCol[3]), 0, 0, 1, true, 'J', true);
                } else {
                    $pdf->writeHTMLCell(50, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(130, 5, 55, $y, StringOutDB($aCol[1]), 0, 0, 1, true, 'J', true);
                }
                $pdf->LN(5);


                $aCol = explode("<col>", $aRow[12]);
                $y = $pdf->getY();
                if ($r_Fine['TrespasserTypeId'] == 11) {
                    $pdf->writeHTMLCell(50, 5, 10, $y, StringOutDB($aCol[2]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(120, 5, 55, $y+3, StringOutDB($aCol[3]), 0, 0, 1, true, 'J', true);

                } else if($chk_180 && $r_Fine['TrespasserId1_180']>0) {

                    $pdf->writeHTMLCell(50, 5, 10, $y, "Trasgressore:", 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(120, 5, 55, $y, StringOutDB($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'] . ' - ' . $trespasser['Address'] . ' ' . $trespasser['ZIP'] . ' ' . $trespasser['City']), 0, 0, 1, true, 'J', true);

                } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16){

                    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=3");
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);

                    $pdf->writeHTMLCell(50, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(120, 5, 55, $y, StringOutDB($r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'] . ' - ' . $r_trespasser['Address'] . ' ' . $r_trespasser['ZIP'] . ' ' . $r_trespasser['City']), 0, 0, 1, true, 'J', true);

                } else {

                    $pdf->writeHTMLCell(50, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'J', true);
                    $pdf->writeHTMLCell(120, 5, 55, $y, StringOutDB($aCol[1]), 0, 0, 1, true, 'J', true);
                }
                $pdf->LN(8);


                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[13]), 0, 0, 1, true, 'J', true);
                $pdf->LN(4);

                $aCol = explode("<col>", $aRow[14]);
                $y = $pdf->getY();

                if (isset($aCol[1])) {
                    $pdf->writeHTMLCell(90, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(90, 5, 120, $y, StringOutDB($aCol[1]), 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);

                } else {
                    $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                }


                $y = $pdf->getY();


                if (isset($aCol[2])) {
                    //$pdf->writeHTMLCell(90, 5, 10, $y, StringOutDB($aRow[15]), 0, 0, 1, true, 'L', true);
                    //$pdf->writeHTMLCell(90, 5, 100, $y, StringOutDB($aCol[2]), 0, 0, 1, true, 'L', true);
                    //$pdf->LN(2);

                    $str_Aggiuntiva = " " . $aCol[2];

                } else {


                    //$pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[15]), 0, 0, 1, true, 'J', true);
                    //$pdf->LN(5);

                    $str_Aggiuntiva = "";
                }
                $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[15] . $str_Aggiuntiva), 0, 0, 1, true, 'J', true);
                $pdf->LN(5);


                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[16]), 0, 0, 1, true, 'J', true);
                $pdf->LN(5);


                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[17]), 0, 0, 1, true, 'J', true);
                $pdf->LN(5);

                $y = $pdf->getY();
                $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[18]), 0, 0, 1, true, 'J', true);
                $pdf->LN(5);


                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[19]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[20]), 0, 0, 1, true, 'J', true);







                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                ////
                ////
                ////    Fine page 2
                ////
                ////
                //////////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////
                $pdf->Temporary();
                $pdf->RightHeader = false;
                $pdf->AddPage();
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', '', 10, '', true);


                $pdf->LN(1);

                $aRow = explode("<row>", $aMainPart[2]);

                $pdf->SetFont('helvetica', '', 8);
                $pdf->writeHTMLCell(190, 20, 10, '', StringOutDB($aRow[1]), 0, 0, 1, true, 'C', true);
                $pdf->LN(15);

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[2]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[3]), 0, 0, 1, true, 'J', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[4]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[5]), 0, 0, 1, true, 'J', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[6]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[7]), 0, 0, 1, true, 'J', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[8]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[9]), 0, 0, 1, true, 'J', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[10]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[11]), 0, 0, 1, true, 'J', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[12]), 0, 0, 1, true, 'C', true);
                $pdf->LN();

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[13]), 0, 0, 1, true, 'J', true);
                $pdf->LN(15);

                $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[14]), 0, 0, 1, true, 'J', true);
                $pdf->LN();



                $style = array(
                    'border' => 1,
                    'vpadding' => 'auto',
                    'hpadding' => 'auto',
                    'fgcolor' => array(0,0,0),
                    'bgcolor' => false, //array(255,255,255)
                    'module_width' => 1, // width of a single module in points
                    'module_height' => 1 // height of a single module in points
                );


                //$url_PagoPAPage = "https://nodopagamenti-test.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";
                $url_PagoPAPage = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";

                if($r_Fine['PagoPA1']!='' && $r_Fine['PagoPA2']!=''){
                    $pdf->write2DBarcode($url_PagoPAPage.$r_Fine['PagoPA1'], 'QRCODE,H', 60, 240, 30, 30, $style, 'N');
                    $pdf->Text(50, 270, $str_PaymentDay1);


                    $pdf->write2DBarcode($url_PagoPAPage.$r_Fine['PagoPA2'], 'QRCODE,H', 120, 240, 30, 30, $style, 'N');
                    $pdf->Text(100, 270, $str_PaymentDay2);

                }


                if ($chk_126Bis && $FormTypeId!=101 && $r_Fine['ReasonId']!=100) {
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ////
                    ////
                    ////    LicensePoint page 1
                    ////
                    ////
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    $pdf->Temporary();
                    $pdf->RightHeader = false;
                    $pdf->AddPage();
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8, '', true);


                    if (strlen($r_Customer['ManagerName']) > 22) {
                        $pdf->writeHTMLCell(60, 0, 10, '', '<h3>' . trim($r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName']) . '</h3>', 0, 0, 1, true, 'L', true);
                        $pdf->LN(10);

                    } else {

                        $pdf->writeHTMLCell(60, 0, 10, '', '<h3>' . trim($r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName']) . '</h3>', 0, 0, 1, true, 'L', true);
                        $pdf->LN(5);
                    }

                    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerSector'], 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                    $pdf->writeHTMLCell(70, 0, 10, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                    $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
//            $pdf->Line(7, 34, 200, 34);
                    $pdf->LN(10);


                    $aRow = explode("<row>", $aMainPart[3]);

                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[1]), 0, 0, 1, true, 'C', true);
                    $pdf->LN();
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[2]), 0, 0, 1, true, 'C', true);
                    $pdf->LN();


                    $pdf->SetFont('helvetica', '', 9);


                    $aCol = explode("<col>", $aRow[3]);
                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(30, 5, 10, $y, StringOutDB($aCol[0]), 0, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(150, 5, 30, $y, StringOutDB($aCol[1]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(8);

                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(150, 5, 30, $y, StringOutDB($aRow[4]), 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);


                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 5, 10, $y, StringOutDB($aRow[5]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[6]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[7]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[8]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[9]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[10]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[11]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[12]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[13]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[14]), 0, 0, 1, true, 'L', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[15]), 0, 0, 1, true, 'C', true);
                    $pdf->LN();
                    $pdf->LN(2);
                    $pdf->writeHTMLCell(190, 5, 10, '', StringOutDB($aRow[16]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();

                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[17]), 0, 0, 1, true, 'J', true);
                    $pdf->LN();
                    $pdf->LN(2);
                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[18]), 0, 0, 1, true, 'C', true);
                    $pdf->LN();


                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ////
                    ////
                    ////    LicensePoint page 2
                    ////
                    ////
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    $pdf->Temporary();
                    $pdf->RightHeader = false;
                    $pdf->AddPage();
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 8, '', true);

                    $aRow = explode("<row>", $aMainPart[4]);

                    $pdf->SetFont('helvetica', '', 9);

                    $pdf->writeHTMLCell(190, 0, 10, '', StringOutDB($aRow[1]), 0, 0, 1, true, 'C', true);
                    $pdf->LN(10);


                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[2]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(32);

                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[3]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(24);

                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[4]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(17);

                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[5]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(20);

                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[6]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(12);

                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[7]), 0, 0, 1, true, 'J', true);
                    $pdf->LN(12);

                    $pdf->SetFont('helvetica', 'B', 9, '', true);
                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(190, 0, 10, $y, StringOutDB($aRow[8]), 0, 0, 1, true, 'J', true);


                }




                if($CreationType==5 && $_SESSION['cityid']!='A175' && $_SESSION['cityid']!='D925'){
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ////
                    ////
                    ////    Notification page
                    ////
                    ////
                    //////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////////////////////////
                    ///
                    $page_format =array('Rotate' => 45);

                    $pdf->Temporary();
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->RightHeader = false;
                    $pdf->AddPage('P', $page_format);

                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('helvetica', '', 10, '', true);


                    $pdf->LN(150);

                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->writeHTMLCell(120, 10, 80, '', "Ufficio ".$ManagerSubject, 0, 0, 1, true, 'C', true);
                    $pdf->LN(10);

                    $pdf->writeHTMLCell(120, 0, 110, '', "Relata di notifica N ...... ", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);

                    $pdf->writeHTMLCell(120, 0, 110, '', "Il sottoscritto ............................................. ufficiale di P.L.", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);

                    $pdf->writeHTMLCell(120, 0, 110, '', "Il giorno ........... del mese ............ dell'anno ............", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);

                    $pdf->writeHTMLCell(120, 0, 110, '', "Ha notificato a ..........................................................", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);

                    $pdf->writeHTMLCell(120, 0, 110, '', "Residente a ..............................................................", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);

                    $pdf->writeHTMLCell(120, 0, 110, '', "Copia del presente atto cconsegnandolo a mani", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);



                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(60, 0, 115, '', "Il Ricevente", 0, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(60, 0, 155, '', "L'ufficiale di P.L.", 0, 0, 1, true, 'L', true);
                    $pdf->LN(15);
                    $y = $pdf->getY();
                    $pdf->writeHTMLCell(60, 0, 110, '', ".................................", 0, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(60, 0, 150, '', ".................................", 0, 0, 1, true, 'L', true);

                    $pdf->AddPage('P', $page_format);
                }

            }





            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            ////
            ////
            ////    BILL
            ////
            ////
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////


            if ($b_PrintBill && $FormTypeId!=101) {

                $page_format = array('Rotate' => -90);
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false);

                $pdf->AddPage('L', $page_format);



                if ($r_Customer['LumpSum'] == 1) {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFeeCAD : $TotalFeeCAD;
                }else {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFee : $TotalFee;
                }


                $pdf->crea_bollettino();

                //$pdf->logo_bollettino($_SESSION['blazon']);

                $a_Address = array();
                $a_Address['Riga1'] = $trespasser['Address'];
                $a_Address['Riga2'] = '';
                $a_Address['Riga3'] = $trespasser['ZIP'];
                $a_Address['Riga4'] = $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ')';

                $a_FifthField = array("Table" => 1, "Id" => $FineId);

                $NW = new CLS_LITERAL_NUMBER();

                $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 0 : 1;
                $str_FifthField = SetFifthField($a_FifthField);
                $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);

                $str_Object = substr('Cron ' . $ProtocolNumber . '/' . $ProtocolYear . '/' . $str_ProtocolLetter . ' targa ' . $r_Fine['VehiclePlate'] . ' ' . $r_Fine['Code'] . ' DEL ' . DateOutDB($r_Fine['FineDate']), 0, 66);




                $numeroLetterale = $NW->converti_numero_bollettino($flt_Amount);


                $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['NationalBankAccount']);
                $pdf->iban_bollettino($r_Customer['NationalBankIban']);
                $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'], 0, 50));
                $pdf->causale_bollettino($str_Object, $str_PaymentDay1);
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address);
                $pdf->importo_in_lettere_bollettino($numeroLetterale);
                $pdf->set_quinto_campo($r_Customer['NationalPostalType'], $str_FifthField);
                $pdf->autorizzazione_bollettino($str_PostalAuthorization);



                if ($r_Customer['LumpSum'] == 1) {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFeeCAD : $TotalMaxFeeCAD;
                }else {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFee : $TotalMaxFee;
                }


                $pdf->crea_bollettino_inverso();

                //$pdf->logo_bollettino($_SESSION['blazon']);

                $a_Address = array();
                $a_Address['Riga1'] = $trespasser['Address'];
                $a_Address['Riga2'] = '';
                $a_Address['Riga3'] = $trespasser['ZIP'];
                $a_Address['Riga4'] = $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ')';


                $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 1 : 2;
                $str_FifthField = SetFifthField($a_FifthField);
                $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);


                $numeroLetterale = $NW->converti_numero_bollettino($flt_Amount);


                $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['NationalBankAccount'], 'due');
                $pdf->iban_bollettino($r_Customer['NationalBankIban'], 'due');
                $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'], 0, 50), 'due');
                $pdf->causale_bollettino($str_Object, $str_PaymentDay2, 'due');
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address, 'due');
                $pdf->importo_in_lettere_bollettino($numeroLetterale, 'due');
                $pdf->autorizzazione_bollettino($str_PostalAuthorization, 'due');


                // BOLLETTINI 674   451     896 ->
                $page_format = array('Rotate'=>45);
                $pdf->AddPage('P', $page_format);
            }







            if ($ultimate) {

                if(isset($a_FineArticle)){
                    $rs->Update('FineArticle', $a_FineArticle, 'FineId=' . $FineId);
                }
                if(isset($a_FineAdditionalArticle)){
                    $rs->Update('FineArticle', $a_FineAdditionalArticle, 'FineId=' . $FineId);
                }

                /////////////////////////////////////////////////
                //
                // REGULAR / AG
                //
                /////////////////////////////////////////////////



                $aInsert = array(
                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                    array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                    array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerFee, 'settype' => 'flt'),
                    array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                    array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefControllerId, 'settype' => 'int'),
                    array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                );
                $rs->Insert('FineHistory', $aInsert);

                if($RegularPostalFine==1) {
                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                        array('field' => 'ProtocolIdAssigned', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolNumber, 'settype' => 'int'),
                    );
                } else {
                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                        array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolNumber, 'settype' => 'int'),
                    );

                }
                
                //Salva il verbalizzante stampa se non presente
                if($r_Fine['UIFineChiefControllerId'] <= 0){
                    $aUpdate[] = array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','value'=>$SelectChiefControllerId,'settype' => 'int');
                }
                
                $rs->Update('Fine', $aUpdate, 'Id=' . $FineId);


                $aInsert = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId),
                );
                $rs->Insert('FineDocumentation', $aInsert);




                //$AGCreation in mgmt_fine_exp_ag_exe
                if($CreationType==5 || $n_Duplicate==4 || isset($AGCreation)){

                    $a_FineHistory = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                        array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $ChiefControllerId, 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                        //array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'), //Bug3151 - Bug3152
                    );

                    if($CreationType==5 || isset($AGCreation)){
                        $a_FineHistory[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int');
                        $a_FineHistory[] = array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                    } else {
                        $a_FineHistory[] = array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                    }


                    $rs->Insert('FineHistory', $a_FineHistory);

                    if($CreationType==5 || isset($AGCreation)){
                        $rs_Tariff = $rs->Select('V_FineTariff', "FineId=" . $FineId);
                        $r_Tariff = mysqli_fetch_array($rs_Tariff);


                        $LicensePointProcedure = $r_Tariff['LicensePoint'];
                        $PresentationDocumentProcedure = 0;
                        $BisProcedure = $r_Tariff['126Bis'];
                        $HabitualProcedure = $r_Tariff['Habitual'];
                        $SuspensionLicenseProcedure = $r_Tariff['SuspensionLicense'];
                        $LossLicenseProcedure = $r_Tariff['LossLicense'];


                        $a_FineNotification = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'LogDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int'),
                            array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                            array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>$PresentationDocumentProcedure,'settype'=>'int'),
                            array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>$LicensePointProcedure,'settype'=>'int'),
                            array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>$HabitualProcedure,'settype'=>'int'),
                            array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>$SuspensionLicenseProcedure,'settype'=>'int'),
                            array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>$LossLicenseProcedure,'settype'=>'int'),
                            array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                            array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                        );
                        $rs->Insert('FineNotification', $a_FineNotification);

                        $a_Fine = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 25, 'settype' => 'int')
                        );

                    } else {
                        $a_Fine = array(
                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 20, 'settype' => 'int')
                        );
                    }

                    $rs->Update('Fine', $a_Fine, 'Id=' . $FineId);

                }

                if ($_SESSION['usertype'] == 3) {
                    $aInsert = array(
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['userid'], 'settype' => 'int'),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                    );
                    $rs->Insert('DocumentationProtocol', $aInsert);
                }


                if (!is_dir($str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                    mkdir($str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                }


                $FileName = $Documentation;

                $pdf->Output($str_DocumentationPath . "/" . $_SESSION['cityid'] . '/' . $FileName, "F");


                if ($FinePDFList) {
                    $n_PageCount = $pdf_union->setSourceFile($str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $FileName);
                    for ($p = 1; $p <= $n_PageCount; $p++) {

                        $tmp_Page = $pdf_union->ImportPage($p);
                        $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);

                        $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';

                        $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                        $pdf_union->useTemplate($tmp_Page);
                    }
                }


            } else {
                $FileName = 'export.pdf';

            }
        }

    }

    if ($ultimate) {
        if ($r_Customer['DigitalSignature'] == 1 && PRODUCTION) {
            for ($i = 0; $i < count($a_DocumentationFineZip); $i++) {
                copy($str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], $str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i]);
            }

            $ftp_connection = false;
            $chk_inp_file = false;
            $server = '89.96.225.74';
            $username = 'velox';
            $password = 'Cd28+PeB';
            echo "Login FTP...<br />";
            $checkUpload = 0;
            $conn = ftp_connect($server);
            if ($conn) {
                $login = ftp_login($conn, $username, $password);
                if ($login) {

                    $ftp_connection = true;

                    echo 'Connessione riuscita<br />';
                    $path = "/" . $_SESSION['username'];

                    $origin = ftp_pwd($conn);
                    // Controllo se esiste la cartella username
                    if (ftp_chdir($conn, $_SESSION['username'])) {
                        // Se esiste torno alla cartella originale
                        ftp_chdir($conn, $origin);
                        $checkUpload = 1;
                        for ($i = 0; $i < count($a_DocumentationFineZip); $i++) {
                            $upload = ftp_put($conn, $path . "/" . $a_DocumentationFineZip[$i], $str_DocumentationPath . "/" . $_SESSION['cityid'] . '/' . $a_DocumentationFineZip[$i], FTP_BINARY);
                            if (!$upload) {
                                $checkUpload = 0;
                                echo "<br />Upload files non completato!<br />Controllare file mancanti.";
                                DIE;

                            } else {
                                unlink($str_DocumentationPath . "/" . $_SESSION['cityid'] . '/' . $a_DocumentationFineZip[$i]);

                            }
                        }
                    } else {
                        // La cartella non esiste
                        echo "<br />Utente non abilitato alla firma o cartella inesistente!";
                        DIE;
                    }
                } else {
                    echo '<br />Login fallita';
                    DIE;
                }
            } else {
                echo '<br />Connessione fallita';
                DIE;
            }
            if ($checkUpload == 1) {
                echo "<br />Upload dei seguenti file eseguito correttamente:";
                for ($i = 0; $i < count($a_DocumentationFineZip); $i++) {
                    echo "<br />" . ($i + 1) . ") " . $a_DocumentationFineZip[$i];
                    copy($str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], $str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i]);
                    unlink($str_DocumentationPath . "/" . $_SESSION['cityid'] . '/' . $a_DocumentationFineZip[$i]);
                }
                //Usato nel caso questo script sia incluso in un altro script che già inizializza una transazione
                //mgmt_fine_exp_ag_exe.php
                if(!isset($b_DisableTransaction) && !$b_DisableTransaction)
                    $rs->End_Transaction();
            }

            $_SESSION['Message']['Success'] = "Azione eseguita con successo.<br /> Sono stati creati e caricati nella cartella per la firma " . $int_ContFine . " verbali.";
        } else {
            $str_Definitive = "Azione eseguita con successo.";
            for ($i = 0; $i < count($a_DocumentationFineZip); $i++) {
                copy($str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], $str_DocumentationPath . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i]);
                unlink($str_DocumentationPath . "/" . $_SESSION['cityid'] . '/' . $a_DocumentationFineZip[$i]);
            }


            $FileName = $_SESSION['cityid'] . "_" . date("Y-m-d_H-i-s") . ".pdf";
            if ($FinePDFList) {
                $pdf_union->Output($str_DocumentationPath . "/" . $_SESSION['cityid'] . '/create/' . $FileName, "F");
                $_SESSION['Documentation'] = $MainPath . '/doc/national/'. $str_DocumentationHtml. '/' . $_SESSION['cityid'] . '/create/' . $FileName;
            }

            $_SESSION['Message']['Success'] = $str_Definitive;
        }
    } else {
        if (!is_dir($str_DocumentationPath . "/" . $_SESSION['cityid'])) {
            mkdir($str_DocumentationPath . "/" . $_SESSION['cityid'], 0777);
        }
        $pdf->Output($str_DocumentationPath . "/" . $_SESSION['cityid'] . '/' . $FileName, "F");
        $_SESSION['Documentation'] = $MainPath . '/doc/national/'. $str_DocumentationHtml. '/' . $_SESSION['cityid'] . '/' . $FileName;
    }
}

//$rs->UnlockTables();
$aUpdate = array(
    array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
    array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
);
$rs->Update('LockedPage', $aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");

//Usato nel caso questo script sia incluso in un altro script che già inizializza una transazione
//mgmt_fine_exp_ag_exe.php
if(!isset($b_DisableTransaction) && !$b_DisableTransaction)
$rs->End_Transaction();


