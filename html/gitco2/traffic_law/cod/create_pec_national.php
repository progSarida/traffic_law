<?php
require(CLS.'/cls_literal_number.php');
include(INC."/function_postalCharge.php");

$n_LanguageId = 1;



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
    if ($r_Customer['DigitalSignature'] == 1) {
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


$a_LockTables =
    array("LockedPage WRITE",
    );
$rs->LockTables($a_LockTables);
$rs_Locked = $rs->Select('LockedPage', "Title='create_pec_national'");
$r_Locked = mysqli_fetch_array($rs_Locked);

if ($r_Locked['Locked'] == 1) {
    $_SESSION['Message'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";

    header("location: " . $P);
    DIE;
} else {
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='create_pec_national'");


}
$rs->UnlockTables();


$str_Speed = "";


$a_DocumentationFineZip = array();
$a_FineId = array();

$a_Detector = array();
$a_SpeedLengthAverage = array();
$a_SpeedLengthAverage = array();
$a_DetectorPosition = array();

$a_GenreLetter = array("D" => "Spett.le", "M" => "Sig.", "F" => "Sig.ra");


if ($_SESSION['cityid'] == 'U480') {
    $str_Detector = "
    Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. 
    La velocita' rilevata dall'apparecchiatura elettronica e' stata di {SpeedControl} Km/h e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), 
    ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h quindi di {SpeedExcess} Km/h oltre il limite imposto. 
    L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e 
    precisamente {Kind} matricola {Code} - {Ratification}";
} else {
    $str_Detector = "
    Dettaglio della violazione: il limite e' fissato per quel tratto di strada in {SpeedLimit} Km/h. 
    La velocita' rilevata dall'apparecchiatura elettronica e' stata di {SpeedControl} Km/h e detratta la 
    tolleranza dell'apparecchiatura del 5% e comunque di almeno {Tolerance} Km/h (art. 197 del DPR 610/96), 
    ne consegue che la velocita' da considerare al fine della violazione risulta essere di {Speed} Km/h. 
    L'infrazione e' stata rilevata mediante dispositivo di controllo di tipo omologato{TimeTypeId} e 
    precisamente {Kind} matricola {Code} - {Ratification}, sul quale sono state effettuate 
    le previste verifiche preventive indicate dal manuale d'uso e dal decreto di omologazione.";
}


$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0");
while ($r_Detector = mysqli_fetch_array($rs_Detector)) {
    $str = str_replace("{Kind}", $r_Detector['Kind'], $str_Detector);
    $str = str_replace("{Code}", $r_Detector['Code'], $str);
    $str = str_replace("{Tolerance}", intval($r_Detector['Tolerance']), $str);
    $str = str_replace("{Ratification}", $r_Detector['Ratification'], $str);

    $a_Detector[$r_Detector['Id']] = $str;
    $a_SpeedLengthAverage[$r_Detector['Id']] = $r_Detector['SpeedLengthAverage'];
    $a_DetectorPosition[$r_Detector['Id']] = ($r_Detector['Position']=="") ? " " : " ".$r_Detector['Position']." ";

}


$str_Speed = "";
$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$ProtocolNumber = 0;


$StatusTypeId = 12;
$NotificationTypeId = 15;

//personalizzazione per gli utenti di tipo 2 che sono tutti ad oggi accertatori della provincia di Savona
if ($_SESSION['usertype'] == 2) {
    $rs_Time = $rs->SelectQuery("SELECT MAX( ControllerTime ) ControllerTime FROM Fine WHERE ControllerDate='" . date("Y-m-d") . "'");
    $r_Time = mysqli_num_rows($rs_Time);
    $Time = ($r_Time['ControllerTime'] == "") ? "08:00:11" : $r_Time['ControllerTime'];
}

$NotificationDate = (strlen(trim($CreationDate)) == 0) ? date("Y-m-d") : DateInDB($CreationDate);


$a_Lan = unserialize(LANGUAGE);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
$a_AdditionalMass = unserialize(ADDITIONAL_MASS);


if (isset($_POST['checkbox'])) {
    $chk_warning = false;


    foreach ($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_ViolationArticle', "(TrespasserTypeId=1 OR TrespasserTypeId=11 OR TrespasserTypeId=2) AND Id=" . $FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);


        if (!($r_Fine['StatusTypeId'] == 10 || $r_Fine['StatusTypeId'] == 14)) {
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
        } else if ($r_Fine['ControllerId'] == "" && $_SESSION['usertype'] == 2 && $ultimate) {
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
            $rs->Update('LockedPage', $aUpdate, "Title='create_pec_national'");

            header("location: " . $P . $str_BackPage);
            DIE;
        }
    }


    if ($ChiefControllerId > 0) {
        $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Id =" . $ChiefControllerId);
        $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

        $ChiefControllerName = $r_ChiefController['Name'] . " Matricola " . $r_ChiefController['Code'];

    } else if ($_SESSION['usertype'] == 3) {

        $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Id =" . $_SESSION['controllerid'] . " AND Disabled=0");
        $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

        $ChiefControllerName = $r_ChiefController['Name'] . " Matricola " . $r_ChiefController['Code'];
    }


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

    $pdf->SetMargins(10, 10, 10);

    $strRegistryCsvFile = 'Id;Cognome;FiscIVA;DataNascita;ISTATNascita;Nazionalita;ISTATResidenza;Indirizzo;Localita;Data_invio;Mezzo_invio;Importo_Spediz;Id_IRIDE;PEC'.PHP_EOL;
    $strDocumentCsvFile = 'Id;Oggetto1;Oggetto2;Origine,Classifica;Tipo;Data;Numero;Mittente;Carico;Anno_pratica;Numero_pratica;Id_IRIDE'.PHP_EOL;
    $strAttachedCsvFile = 'Id;Allegato;NomeFile'.PHP_EOL;;






    $file_Registry = fopen(NATIONAL_PEC . "/" . $_SESSION['cityid'] . "/Registry.txt", "w");
    $file_Document = fopen(NATIONAL_PEC . "/" . $_SESSION['cityid'] . "/Document.txt", "w");

    fwrite($file_Registry, $strRegistryCsvFile);
    fwrite($file_Document, $strDocumentCsvFile);
    if ($ultimate) {
        $file_Attached = fopen(NATIONAL_PEC . "/" . $_SESSION['cityid'] . "/Attached.txt", "w");
        fwrite($file_Attached, $strAttachedCsvFile);
    }




    foreach ($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_ViolationArticle', "(TrespasserTypeId=1 OR TrespasserTypeId=11 OR TrespasserTypeId=2) AND Id=" . $FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);


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



        $ChargeTotalFee = 0;

        $NotificationFee = $r_Customer['NationalPECNotificationFee'];
        $ResearchFee = $r_Customer['NationalPECResearchFee'];


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

            $pdf->SetMargins(10, 10, 10);

        }

        $pdf->Temporary();
        $pdf->RightHeader = true;
        $pdf->PrintObject1 = $a_PrintObject[0];
        $pdf->PrintObject2 = $a_PrintObject[1];
        $pdf->PrintObject3 = $a_PrintObject[2];


        $pdf->AddPage();


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

        $ManagerName = $r_Customer['ManagerName'];
        $pdf->customer = $ManagerName;


        $pdf->SetFont('helvetica', '', 10, '', true);
        // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

//        $pdf->Line(7, 9, 200, 9);


        if (strlen($r_Customer['ManagerName']) > 22) {
            $pdf->writeHTMLCell(60, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(10);

        } else {

            $pdf->writeHTMLCell(60, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(5);
        }


        $pdf->writeHTMLCell(60, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
//        $pdf->Line(7, 34, 200, 34);
        $pdf->LN(10);


        /*
                $a_LockTables =
                    array( "V_ViolationArticle WRITE",
                        "ArticleTariff WRITE",
                        "V_Trespasser WRITE",
                        "PostalCharge WRITE",
                        "Form WRITE",
                        "V_JudicialOffice WRITE",
                        "AdditionalSanction WRITE",
                        "LockedPage WRITE",
                        "Fine WRITE",
                        "FineHistory WRITE",
                        "FineDocumentation WRITE",
                    );
                $rs->LockTables($a_LockTables);
        */


        $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
        $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
        /*
                LicensePoint
                YoungLicensePoint
                AdditionalSanctionId
                PresentationDocument
                LossLicense
                AdditionalBus
                AdditionalCamion
                AdditionalNight
                126Bis
                Habitual
                ReducedPayment
                ReducedDayPayment
                SuspensionLicense
        */


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


        $Fee = $r_Fine['Fee'];
        $MaxFee = $r_Fine['MaxFee'];


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


        $ZoneId = 0;

        $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);
        


        $CustomerFee = $r_Fine['CustomerAdditionalFee'];
        $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;

        $AdditionalFee = $NotificationFee + $ResearchFee;


        if ($r_Customer['CityUnion'] > 1) {

            $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Sign !='' AND Disabled=0 AND Locality='" . $r_Fine['Locality'] . "'");
            $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

            $ChiefControllerName = $r_ChiefController['Name'];
        }


        $str_Speed = "";
        $SpeedLengthAverage = 0;
        $FineTime = $r_Fine['FineTime'];
        if ($r_Fine['Speed'] > 0) {

            $SpeedExcess = intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit']);

            $str_Speed = " " . $a_Detector[$r_Fine['DetectorId']];
            $str_Speed = str_replace("{Speed}", intval($r_Fine['Speed']), $str_Speed);
            $str_Speed = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_Speed);
            $str_Speed = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_Speed);
            $str_Speed = str_replace("{SpeedExcess}", intval($SpeedExcess), $str_Speed);


            $str_Speed = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_Speed);
            $SpeedLengthAverage = $a_SpeedLengthAverage[$r_Fine['DetectorId']];

        }


        $forms = $rs->Select('Form', "FormTypeId=" . $FormTypeId . " AND CityId='" . $_SESSION['cityid'] . "' AND LanguageId=" . $n_LanguageId);
        $form = mysqli_fetch_array($forms);

        $str_ProtocolLetter = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
        $Content = $form['Content'];


        if ($r_ArticleTariff['AdditionalSanctionId'] > 0) {
            $rs_AdditionalSanction = $rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']);
            $r_AdditionalSanction = mysqli_fetch_array($rs_AdditionalSanction);

            $Content = str_replace("{AdditionalSanctionId}", "SANZIONE ACCESSORIA: " . $r_AdditionalSanction['Title' . $a_Lan[$n_LanguageId]], $Content);

        } else {
            $Content = str_replace("{AdditionalSanctionId}", "", $Content);
        }


        $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
        $Content = str_replace("{FineTime}", TimeOutDB($FineTime), $Content);
        $Content = str_replace("{VehicleTypeId}", $r_Fine['VehicleTitle' . $a_Lan[$n_LanguageId]], $Content);
        $Content = str_replace("{VehiclePlate}", $r_Fine['VehiclePlate'], $Content);

        $Content = str_replace("{VehicleBrand}", $r_Fine['VehicleBrand'], $Content);
        $Content = str_replace("{VehicleModel}", $r_Fine['VehicleModel'], $Content);
        $Content = str_replace("{VehicleColor}", $r_Fine['VehicleColor'], $Content);

        $Paragraph = ($r_Fine['Paragraph'] == "0") ? "" : $r_Fine['Paragraph'] . " ";
        $Letter = ($r_Fine['Letter'] == "0") ? "" : $r_Fine['Letter'];

        $Content = str_replace("{ArticleId}", $r_Fine['Article'] . "/" . $Paragraph . $Letter, $Content);


        /////////////////////////////////////////////
        //Article Owner
        /////////////////////////////////////////////
        $rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);

        $r_FineOwner = mysqli_fetch_array($rs_FineOwner);

        $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
        $str_ArticleDescription .= $str_Speed . $str_AdditionalNight . $str_AdditionalMass;
        $Content = str_replace("{ArticleDescription}", $str_ArticleDescription, $Content);

        $str_ExpirationDate = (isset($r_Fine['ExpirationDate']) && $r_Fine['ExpirationDate']!="") ? DateOutDB($r_Fine['ExpirationDate']) : "";
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

                $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription' . LAN])) > 0) ? $r_AdditionalArticle['AdditionalArticleDescription' . LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];

                $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate']!="") ? DateOutDB($r_AdditionalArticle['ExpirationDate']) : "";
                $str_AdditionalArticleDescription = str_replace("{ExpirationDate}", $str_ExpirationDate, $str_AdditionalArticleDescription);

                $Paragraph = ($r_AdditionalArticle['Paragraph'] == "0") ? "" : $r_AdditionalArticle['Paragraph'] . " ";
                $Letter = ($r_AdditionalArticle['Letter'] == "0") ? "" : $r_AdditionalArticle['Letter'];


                $str_ArticleDescription .= " e Art. " . $r_AdditionalArticle['Article'] . "/" . $Paragraph . $Letter;
                $str_ArticleDescription .= " " . $str_AdditionalArticleDescription;


            }

        }

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
        $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);

        $Content = str_replace("{ArticleAdditionalText}", $r_Fine['ArticleAdditionalText' . $a_Lan[$n_LanguageId]], $Content);

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
        /////////////////////////////////////////////
        //126 BIS
        /////////////////////////////////////////////
        $Content = str_replace("{PreviousProtocolId}", $PreviousProtocolId, $Content);
        $Content = str_replace("{PreviousProtocolYear}", $PreviousProtocolYear, $Content);
        $Content = str_replace("{PreviousFineDate}", $PreviousFineDate, $Content);


        $Content = str_replace("{Locality}", $r_Fine['CityTitle'], $Content);
        $Content = str_replace("{Address}", $r_Fine['Address'], $Content);

        $Content = str_replace("{BankOwner}", $r_Customer['NationalBankOwner'], $Content);
        $Content = str_replace("{BankName}", $r_Customer['NationalBankName'], $Content);
        $Content = str_replace("{BankAccount}", $r_Customer['NationalBankAccount'], $Content);
        $Content = str_replace("{BankSwift}", $r_Customer['NationalBankSwift'], $Content);
        $Content = str_replace("{BankIban}", $r_Customer['NationalBankIban'], $Content);


        $Content = str_replace("{CurrentDate}", $CreationDate, $Content);


        if ($r_Customer['ManagerSignName'] == "") {
            if ($r_Customer['CityUnion'] > 1) {
                $Content = str_replace("{Date}", $r_Fine['CityTitle'] . ", " . $CreationDate, $Content);
            } else {
                $Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . $CreationDate, $Content);
            }
        } else {
            $Content = str_replace("{Date}", $r_Customer['ManagerSignName'] . ", " . $CreationDate, $Content);
        }

        $Content = str_replace("{ManagerDataEntryName}", $r_Customer['ManagerDataEntryName'], $Content);
        $Content = str_replace("{ManagerProcessName}", $r_Customer['ManagerProcessName'], $Content);


        $Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
        $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);

        $PartialFee = $Fee * FINE_PARTIAL;
        $MaxFee = $MaxFee * FINE_MAX;

        $TotalPartialFee = $PartialFee + $AdditionalFee;

        $TotalFee = $Fee + $AdditionalFee;

        $TotalMaxFee = $MaxFee + $AdditionalFee;


        $Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
        $Content = str_replace("{TotalPartialFee}", NumberDisplay($TotalPartialFee), $Content);

        $Content = str_replace("{Fee}", NumberDisplay($Fee), $Content);
        $Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);

        $Content = str_replace("{ResearchFee}",NumberDisplay($ResearchFee),$Content);
        $Content = str_replace("{NotificationFee}",NumberDisplay($NotificationFee),$Content);
        $Content = str_replace("{ChargeTotalFee}",NumberDisplay($ChargeTotalFee),$Content);


        $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
        $Content = str_replace("{TotalMaxFee}", NumberDisplay($TotalMaxFee), $Content);

        $Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content);

        $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
        $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);


        $Content = str_replace("{ChiefControllerName}", $ChiefControllerName, $Content);

        $Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
        $Content = str_replace("{TrespasserCity}", $trespasser['City'], $Content);
        $Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);

        $Content = str_replace("{TrespasserPEC}", $trespasser['PEC'], $Content);
        $Content = str_replace("{DataSourceDate}", DateOutDB($trespasser['DataSourceDate']), $Content);

        if (strlen($trespasser['PEC']) == 16) {
            $Content = str_replace("{TaxCode}", "C.F.: " . $trespasser['TaxCode'], $Content);

        } else if (strlen($trespasser['PEC']) == 11) {
            $Content = str_replace("{TaxCode}", "P.IVA: " . $trespasser['TaxCode'], $Content);
        } else {
            $Content = str_replace("{TaxCode}", "", $Content);
        }

        if (!((strlen($trespasser['BornDate']) != 10) || (is_null($trespasser['BornDate'])))) {
            $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
        } else {
            $Content = str_replace("il {TrespasserBornDate}", "", $Content);
        }

        if (strlen(trim($trespasser['BornPlace'])) > 0) {
            $Content = str_replace("{TrespasserBornCity}", $trespasser['BornPlace'], $Content);

        } else {
            $Content = str_replace("Nato/a a {TrespasserBornCity}", "", $Content);
        }

        $Content = str_replace("{TrespasserAddress}", $trespasser['Address'] . " " . $trespasser['ZIP'], $Content);
        $Content = str_replace("{TrespasserCountry}", $trespasser['CountryTitle'], $Content);

        if ($trespasser['Genre'] == "D") {
            $Content = str_replace("{TrespasserC}", $trespasser['CompanyName'], $Content);
            $Content = str_replace("{TrespasserCC}", $trespasser['City'] . "(" . $trespasser['Province'] . ")", $Content);
            $Content = str_replace("{TrespasserCA}", $trespasser['Address'], $Content);

            $Content = str_replace("{TrespasserN}", "___________________", $Content);
            $Content = str_replace("{TrespasserS}", "___________________", $Content);
            $Content = str_replace("{TrespasserBC}", "_____________________", $Content);
            $Content = str_replace("{TrespasserBD}", "_________________", $Content);
            $Content = str_replace("{TrespasserR}", "____________________________________________", $Content);
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

            if ($ProtocolNumber == 0) $rs->Start_Transaction();

            if($r_Fine['ProtocolIdAssigned']==0){
                $str_WhereRule = ($RuleTypeId == 1) ? " AND RuleTypeId=1" : " AND RuleTypeId!=1";

                $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . $str_WhereRule);

                $r_Protocol = mysqli_fetch_array($rs_Protocol);

                $ProtocolNumber = $r_Protocol['ProtocolId'] + 1;

            } else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];

            $Content = str_replace("{ProtocolId}", $ProtocolNumber, $Content);

            $strProtocolNumber = "";
            for ($k = strlen((string)$ProtocolNumber); $k < 9; $k++) {
                $strProtocolNumber .= "0";
            }
            $strProtocolNumber .= $ProtocolNumber;

            $Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_" . $RndCode . ".pdf";
            $Documentation_N = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_" . $RndCode . "_N.pdf";
            $a_DocumentationFineZip[] = $Documentation;
            $a_FineId[] = $r_Fine['Id'];

        } else {
            if ($ProtocolNumber == 0) {
                $str_WhereRule = ($RuleTypeId == 1) ? " AND RuleTypeId=" . $RuleTypeId : " AND RuleTypeId!=1";

                $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . $str_WhereRule);

                $r_Protocol = mysqli_fetch_array($rs_Protocol);

                $ProtocolNumber = $r_Protocol['ProtocolId'];
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


        $aMainPart = explode("<main_part>", $Content);
        $aRow = explode("<row>", $aMainPart[1]);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'L', true);
        $pdf->LN(10);

        if ($r_Customer['PDFRefPrint']) {
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'L', true);
            $pdf->LN(9);
        } else {
            $pdf->writeHTMLCell(190, 0, 10, '', '', 0, 0, 1, true, 'L', true);
            $pdf->LN(9);
        }


        $pdf->writeHTMLCell(100, 0, 110, '', '<h4>' . $a_GenreLetter[$trespasser['Genre']] . " " . substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 35) . '</h4>', 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $trespasser['Address'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $trespasser['ZIP'] . ' ' . $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ")", 0, 0, 1, true, 'L', true);
        $pdf->LN(20);


        $pdf->SetFont('helvetica', '', 8);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[3]), 0, 0, 1, true, 'J', true);
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[4]), 0, 0, 1, true, 'C', true);
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
        $pdf->LN();

        $a_Payment = explode("<PAYMENT>", $aRow[6]);
        $a_PaymentType = explode("<PAYMENTTYPE>", $aRow[7]);

        if ($r_ArticleTariff['ReducedPayment']) {
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_Payment[1]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[10]), 0, 0, 1, true, 'J', true);
            $pdf->LN();
        }


        if (!$r_ArticleTariff['ReducedPayment']) $a_Payment[2] = str_replace("DAL 6", "DAL 1", $a_Payment[2]);
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_Payment[2]), 0, 0, 1, true, 'L', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[11]), 0, 0, 1, true, 'J', true);
        $pdf->LN();


        if (!$r_ArticleTariff['ReducedPayment']) {
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_Payment[3]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($a_PaymentType[12]), 0, 0, 1, true, 'J', true);
            $pdf->LN();

        }

        $pdf->LN(4);


        $aCol = explode("<col>", $aRow[10]);
        $y = $pdf->getY();
        if ($r_Fine['TrespasserTypeId'] == 11) {
            $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'J', true);
            $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[3]), 0, 0, 1, true, 'J', true);
        } else {
            $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'J', true);
            $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
        }
        $pdf->LN(5);


        $aCol = explode("<col>", $aRow[11]);
        $y = $pdf->getY();
        if ($r_Fine['TrespasserTypeId'] == 11) {
            $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'J', true);
            $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[3]), 0, 0, 1, true, 'J', true);
        } else {
            $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'J', true);
            $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
        }
        $pdf->LN(5);


        $aCol = explode("<col>", $aRow[12]);
        $y = $pdf->getY();
        if ($r_Fine['TrespasserTypeId'] == 11) {
            $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'J', true);
            $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[3]), 0, 0, 1, true, 'J', true);
        } else {
            $pdf->writeHTMLCell(50, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'J', true);
            $pdf->writeHTMLCell(120, 5, 55, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
        }
        $pdf->LN(10);


        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[13]), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);

        $aCol = explode("<col>", $aRow[14]);
        $y = $pdf->getY();

        if (isset($aCol[1])) {
            $pdf->writeHTMLCell(90, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(90, 5, 120, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'L', true);
            $pdf->LN(2);

        } else {
            $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
            $pdf->LN(2);
        }


        $y = $pdf->getY();


        if (isset($aCol[2])) {
            //$pdf->writeHTMLCell(90, 5, 10, $y, utf8_encode($aRow[15]), 0, 0, 1, true, 'L', true);
            //$pdf->writeHTMLCell(90, 5, 100, $y, utf8_encode($aCol[2]), 0, 0, 1, true, 'L', true);
            //$pdf->LN(2);

            $str_Aggiuntiva = " " . $aCol[2];

        } else {


            //$pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[15]), 0, 0, 1, true, 'J', true);
            //$pdf->LN(5);

            $str_Aggiuntiva = "";
        }
        $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[15] . $str_Aggiuntiva), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);


        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[16]), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);


        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[17]), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[18]), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);


        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[19]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[20]), 0, 0, 1, true, 'J', true);


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
        $pdf->writeHTMLCell(190, 20, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
        $pdf->LN(15);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[3]), 0, 0, 1, true, 'J', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[4]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[6]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'J', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[9]), 0, 0, 1, true, 'J', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[10]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[11]), 0, 0, 1, true, 'J', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[12]), 0, 0, 1, true, 'C', true);
        $pdf->LN();

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[13]), 0, 0, 1, true, 'J', true);
        $pdf->LN(15);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[14]), 0, 0, 1, true, 'J', true);
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
            $pdf->Text(60, 270, $str_PaymentDay1);


            $pdf->write2DBarcode($url_PagoPAPage.$r_Fine['PagoPA2'], 'QRCODE,H', 120, 240, 30, 30, $style, 'N');
            $pdf->Text(120, 270, $str_PaymentDay2);

        }
        if ($r_ArticleTariff['LicensePoint'] > 0) {
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
            $pdf->SetFont('helvetica', '', 10, '', true);


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
            $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(60, 0, 10, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
//            $pdf->Line(7, 34, 200, 34);
            $pdf->LN(10);


            $aRow = explode("<row>", $aMainPart[3]);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
            $pdf->LN();
            $pdf->SetFont('helvetica', '', 10);
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'C', true);
            $pdf->LN();


            $pdf->SetFont('helvetica', '', 9);


            $aCol = explode("<col>", $aRow[3]);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(30, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(150, 5, 30, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'J', true);
            $pdf->LN(8);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(150, 5, 30, $y, utf8_encode($aRow[4]), 0, 0, 1, true, 'L', true);
            $pdf->LN(10);


            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, utf8_encode($aRow[5]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[6]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[9]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[10]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[11]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[12]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[13]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[14]), 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[15]), 0, 0, 1, true, 'C', true);
            $pdf->LN();
            $pdf->LN(2);
            $pdf->writeHTMLCell(190, 5, 10, '', utf8_encode($aRow[16]), 0, 0, 1, true, 'J', true);
            $pdf->LN();

            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[17]), 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[18]), 0, 0, 1, true, 'C', true);
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
            $pdf->SetFont('helvetica', '', 10, '', true);

            $aRow = explode("<row>", $aMainPart[4]);

            $pdf->SetFont('helvetica', '', 9);

            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
            $pdf->LN(10);


            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[2]), 0, 0, 1, true, 'J', true);
            $pdf->LN(32);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[3]), 0, 0, 1, true, 'J', true);
            $pdf->LN(24);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[4]), 0, 0, 1, true, 'J', true);
            $pdf->LN(17);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
            $pdf->LN(20);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[6]), 0, 0, 1, true, 'J', true);
            $pdf->LN(12);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[7]), 0, 0, 1, true, 'J', true);
            $pdf->LN(12);

            $pdf->SetFont('helvetica', 'B', 9, '', true);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[8]), 0, 0, 1, true, 'J', true);


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



        if($r_Customer['PostalType']!=""){
            $page_format = array('Rotate'=>-90);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            $pdf->AddPage('L', $page_format);
            $pdf->crea_bollettino();

            //$pdf->logo_bollettino($_SESSION['blazon']);

            $a_Address = array();
            $a_Address['Riga1'] = $trespasser['Address'];
            $a_Address['Riga2'] = '';
            $a_Address['Riga3'] = $trespasser['ZIP'];
            $a_Address['Riga4'] = $trespasser['City']. ' '."(".$trespasser['Province'].')';

            $a_FifthField = array("Table"=>1, "Id"=>$FineId);

            $NW = new CLS_LITERAL_NUMBER();
            $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalPartialFee : $TotalFee;
            $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 0 : 1 ;
            $str_FifthField = SetFifthField($a_FifthField);
            $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);

            $str_Object = substr('Cron '.$ProtocolNumber.'/'.$ProtocolYear.'/'.$str_ProtocolLetter. ' targa '.$r_Fine['VehiclePlate'].' '.$r_Fine['Code'].' DEL '.DateOutDB($r_Fine['FineDate']),0,66);


            if($r_ArticleTariff['ReducedPayment']){
                $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
            }else{
                $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
            }

            $Content = str_replace("{PagoPA1}", $r_Fine['PagoPA1']. " per ".$str_PaymentDay1, $Content);
            $Content = str_replace("{PagoPA2}", $r_Fine['PagoPA2']. " per ".$str_PaymentDay2, $Content);



            $numeroLetterale =  $NW->converti_numero_bollettino($flt_Amount);

            $pdf->scelta_td_bollettino($r_Customer['PostalType'],$str_FifthField,NumberDisplay($flt_Amount),'si',$r_Customer['NationalBankAccount']);
            $pdf->iban_bollettino($r_Customer['NationalBankIban']);
            $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'],0,50));
            $pdf->causale_bollettino($str_Object, $str_PaymentDay1);
            $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name'],0,50),$a_Address);
            $pdf->importo_in_lettere_bollettino($numeroLetterale);
            if($r_Customer['PostalAuthorization']!=""){
                $pdf->autorizzazione_bollettino($r_Customer['PostalAuthorization']);
            }
            $pdf->set_quinto_campo($r_Customer['PostalType'],$str_FifthField);





            $pdf->crea_bollettino_inverso();

            //$pdf->logo_bollettino($_SESSION['blazon']);

            $a_Address = array();
            $a_Address['Riga1'] = $trespasser['Address'];
            $a_Address['Riga2'] = '';
            $a_Address['Riga3'] = $trespasser['ZIP'];
            $a_Address['Riga4'] = $trespasser['City']. ' '."(".$trespasser['Province'].')';

            $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFee : $TotalMaxFee;
            $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 1 : 2;
            $str_FifthField = SetFifthField($a_FifthField);
            $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);



            $numeroLetterale =  $NW->converti_numero_bollettino($flt_Amount);


            $pdf->scelta_td_bollettino($r_Customer['PostalType'],$str_FifthField,NumberDisplay($flt_Amount),'si',$r_Customer['NationalBankAccount'],'due');
            $pdf->iban_bollettino($r_Customer['NationalBankIban'],'due');
            $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'],0,50),'due');
            $pdf->causale_bollettino($str_Object, $str_PaymentDay2,'due');
            $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name'],0,50),$a_Address,'due');
            $pdf->importo_in_lettere_bollettino($numeroLetterale,'due');
            if($r_Customer['PostalAuthorization']!=""){
                $pdf->autorizzazione_bollettino($r_Customer['PostalAuthorization'],'due');

            }

            // BOLLETTINI 674   451     896 ->

        }


        if ($ultimate) {

            $aInsert = array(
                array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $CustomerFee, 'settype' => 'flt'),
                array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NotificationFee, 'settype' => 'flt'),
                array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $ResearchFee, 'settype' => 'flt'),
                array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_ChiefController['Id'], 'settype' => 'int'),
                array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
            );
            $rs->Insert('FineHistory', $aInsert);

            $aUpdate = array(
                array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                array('field' => 'ProtocolId', 'selector' => 'value', 'type' => 'int', 'value' => $ProtocolNumber, 'settype' => 'int'),
            );
            $rs->Update('Fine', $aUpdate, 'Id=' . $FineId);

            $aInsert = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 2),
            );
            $rs->Insert('FineDocumentation', $aInsert);


            if (!is_dir(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                mkdir(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
            }


            $FileName = $Documentation;

            $pdf->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $FileName, "F");


        }


        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        ////
        ////
        ////    Notification page
        ////
        ////
        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        if ($ultimate) {
            $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);

            $pdf->TemporaryPrint = $ultimate;
            $pdf->NationalFine = 1;
            $pdf->CustomerFooter = 0;
            //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);


            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_SESSION['citytitle']);
            $pdf->SetTitle('Notification');
            $pdf->SetSubject('');
            $pdf->SetKeywords('');


            $pdf->SetMargins(10, 10, 10);

        }

        $pdf->Temporary();
        $pdf->RightHeader = true;
        $pdf->PrintObject1 = $a_PrintObject[0];
        $pdf->PrintObject2 = $a_PrintObject[1];
        $pdf->PrintObject3 = $a_PrintObject[2];


        $page_format = array('Rotate'=>45);
        $pdf->SetMargins(10,10,10);
        $pdf->AddPage('P', $page_format);


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

        $ManagerName = $r_Customer['ManagerName'];
        $pdf->customer = $ManagerName;


        $pdf->SetFont('helvetica', '', 10, '', true);


        if (strlen($r_Customer['ManagerName']) > 22) {
            $pdf->writeHTMLCell(60, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(10);

        } else {

            $pdf->writeHTMLCell(60, 0, 30, '', '<h3>' . $r_Customer['ManagerName'] . '</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(5);
        }


        $pdf->writeHTMLCell(60, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")", 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
        $pdf->LN(10);


        $aRow = explode("<row>", $aMainPart[5]);

        $pdf->SetFont('helvetica', '', 9);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
        $pdf->LN(10);


        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[2]), 0, 0, 1, true, 'J', true);
        $pdf->LN(25);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[3]), 0, 0, 1, true, 'C', true);
        $pdf->LN(5);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[4]), 0, 0, 1, true, 'J', true);
        $pdf->LN(10);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[5]), 0, 0, 1, true, 'C', true);
        $pdf->LN(5);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 10, $y, utf8_encode($aRow[6]), 0, 0, 1, true, 'J', true);
        $pdf->LN(40);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 35, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'C', true);
        $pdf->LN(30);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'J', true);




        if ($ultimate) {

            $pdf->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation_N, "F");
            $a_DocumentationFineZip[] = $Documentation_N;
            $a_FineId[] = $r_Fine['Id'];

            $strAttachedCsvFile = $ProtocolNumber.';1;'.$Documentation.PHP_EOL;
            fwrite($file_Attached, $strAttachedCsvFile);
            $strAttachedCsvFile = $ProtocolNumber.';2;'.$Documentation_N.PHP_EOL;
            fwrite($file_Attached, $strAttachedCsvFile);

        }

        $str_Trespasser = trim($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name']);
        $str_TrespasserAddress = trim($trespasser['Address']);
        //$str_TrespasserLocality = trim($trespasser['ZIP'].' '.$trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ")");
        $str_TrespasserLocality = '';

        $str_Object = 'VERBALE DI ACCERTAMENTO N. ' . $ProtocolNumber . '/' . $_SESSION['year'].' A CARICO DI ' . $str_Trespasser . ' TARGA ' . $r_Fine['VehiclePlate'];
        $month = '0000' .date('m');


        $rs_IstatCode = $rs->SelectQuery("
            SELECT C.IstatCode
            FROM ".MAIN_DB.".City C JOIN ".MAIN_DB.".ZIPCity ZC ON C.Id = ZC.CityId
            WHERE ZC.ZIP='".$trespasser['ZIP']."'");
        if(mysqli_num_rows($rs_IstatCode)==0){
            $rs_IstatCode = $rs->Select(MAIN_DB.".City", "ZIP='".$trespasser['ZIP']."'");
        }

        $r_IstatCode = mysqli_fetch_array($rs_IstatCode);
        $IstatCode = $r_IstatCode['IstatCode'];

        $strRegistryCsvFile = $ProtocolNumber.';'.$str_Trespasser.';\''.trim($trespasser['TaxCode']).';;;100;'.$IstatCode.';'.$str_TrespasserAddress.';'.$str_TrespasserLocality.';;PEC;0;'.$trespasser['IrideCode'].';'.$trespasser['PEC'].PHP_EOL;
        $strDocumentCsvFile = $ProtocolNumber.';'.$str_Object.';;P;002.012.005;VBCDS;;;AVX;AVX;'.$_SESSION['year'].';002.012.005/'.$month.';0'.PHP_EOL;

        fwrite($file_Registry, $strRegistryCsvFile);
        fwrite($file_Document, $strDocumentCsvFile);

    }



    fclose($file_Document);
    fclose($file_Registry);
    if ($ultimate) {
        fclose($file_Attached);
    }

    /*
Id	Cognome	FiscIVA	DataNascita	ISTATNascita	Nazionalita	ISTATResidenza	Indirizzo	Localita	Data_invio	Mezzo_invio	Importo_Spediz	Id_IRIDE
1					                            100	        99999				                                PEC	            0	            0
2					                            100	        99999				                                PEC	            0	            0



Id	Oggetto1	Oggetto2	Origine	Classifica	Tipo	Data	Numero	Mittente	Carico	Anno_pratica	Numero_pratica	    Id_IRIDE
1			                P	    002.012.005	VBCDS			            AVX	    AVX	    2018	        002.012.005/000001	42207




    */
}



if ($ultimate) {

    $str_Definitive = "Stampa definitiva avvenuta con successo!";

    $FileNameZip = "PEC_" . $_SESSION['cityid'] . "_" . date("Y-m-d") . "_" . date("H-i-s") . "_" . count($_POST['checkbox']) . ".zip";

    $zip = new ZipArchive();
    if($zip->open(NATIONAL_PEC . "/" . $_SESSION['cityid'] . "/" . $FileNameZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)===true){
        $_SESSION['Documentation'] = $MainPath . '/doc/national/pec/' . $_SESSION['cityid'] . '/' . $FileNameZip;

        for ($i = 0; $i < count($a_DocumentationFineZip); $i++) {
            $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], $a_DocumentationFineZip[$i]);
            sleep(1);
        }
        $zip->close();

        for ($i = 0; $i < count($a_DocumentationFineZip); $i++) {
            if(copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_FineId[$i] . "/" . $a_DocumentationFineZip[$i])){
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $a_DocumentationFineZip[$i]);
            }
        }


        $_SESSION['Message'] = $str_Definitive;
    }

} else {
    $FileName = 'export.pdf';

    if (!is_dir(NATIONAL_FINE . "/" . $_SESSION['cityid'])) {
        mkdir(NATIONAL_FINE . "/" . $_SESSION['cityid'], 0777);
    }
    $pdf->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $FileName, "F");
    $_SESSION['Documentation'] = $MainPath . '/doc/national/fine/' . $_SESSION['cityid'] . '/' . $FileName;
}
//$rs->UnlockTables();
$aUpdate = array(
    array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
    array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
);
$rs->Update('LockedPage', $aUpdate, "Title='create_pec_national'");
$rs->End_Transaction();