<?php
require(CLS .'/cls_literal_number.php');
include(INC."/function_postalCharge.php");

define("ADDITIONAL_SANCTION_NOT_EXPECTED", "non_prevista");

if (!is_dir(FOREIGN_PEC . "/" . $_SESSION['cityid'])) {
    mkdir(FOREIGN_PEC . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(FOREIGN_FINE . "/" . $_SESSION['cityid'])) {
    mkdir(FOREIGN_FINE . "/" . $_SESSION['cityid'], 0777);
}

$n_LanguageId = 1;
$ZoneId = 0;
$ultimate = CheckValue('ultimate', 'n');

$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
}


$rs_row = $rs->Select('DocumentationProtocol', "UserId=" . $_SESSION['userid'] . " AND CityId='" . $_SESSION['cityid'] . "'");
$n_row = mysqli_num_rows($rs_row);
if ($n_row > 0) {  
    $_SESSION['Message'] = "Ci sono verbali da protocollare. Impossibile crearne altri.";
    header("location: frm_createdynamic_pec.php".$Filters);
    DIE;
}

$StatusTypeId = 12;
$DocumentationTypeId = 2;
$NotificationTypeId = 15;

//TODO valutare se serve
//$FinePDFList = $r_Customer['FinePDFList'];


$int_ContFine = 0;


//DA SCOMMENTARE DOPO TEST
$a_LockTables =
    array("LockedPage WRITE",
    );
$rs->LockTables($a_LockTables);
$rs_Locked = $rs->Select('LockedPage', "Title='createdynamic_pec_foreign'");
$r_Locked = mysqli_fetch_array($rs_Locked);

if ($r_Locked['Locked'] == 1) {
    $_SESSION['Message'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
    header("location: frm_createdynamic_pec.php".$Filters);
    DIE;
} else {
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='createdynamic_pec_foreign'");
}
$rs->UnlockTables();
//


$a_Documentation = array();

$a_GenreLetter = array("D" => "Spett.le", "M" => "Sig.", "F" => "Sig.ra");
/*
    La segnalazione della postazione di rilevamento della velocita' dei veicoli e' stata effettuata mediante
    esposizione di appositi cartelli segnaletici informativi, posizionati prima della postazione di rilevamento,
    ai sensi del D. Interm. Del 15 agosto 2007, e in modo da garantire l'avvistamento della postazione di rilevamento
    della velocita' dei veicoli e la salvaguardia della sicurezza della circolazione stradale.
*/

/////////////////////////////////////////////
//Detector
/////////////////////////////////////////////

$a_DetectorPosition = array();
$a_DetectorKind = array();
$a_DetectorCode = array();
$a_DetectorTolerance = array();
$a_DetectorRatification = array();
$a_DetectorAdditionalTextIta = array();
$a_DetectorTypeId = array();
$a_SpeedLengthAverage = array();

$rs_Detector = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0");
while ($r_Detector = mysqli_fetch_array($rs_Detector)) {
    $a_DetectorPosition[$r_Detector['Id']] = ($r_Detector['Position']=="") ? "" : StringOutDB($r_Detector['Position']);
    $a_DetectorKind[$r_Detector['Id']] = ($r_Detector['Kind']=="") ? "" : StringOutDB($r_Detector['Kind']);
    $a_DetectorCode[$r_Detector['Id']] = ($r_Detector['Code']=="") ? "" : $r_Detector['Code'];
    $a_DetectorTolerance[$r_Detector['Id']] = ($r_Detector['Tolerance']=="") ? "" : $r_Detector['Tolerance'];
    $a_DetectorRatification[$r_Detector['Id']] = ($r_Detector['Ratification']=="") ? "" : StringOutDB($r_Detector['Ratification']);
    $a_DetectorAdditionalTextIta[$r_Detector['Id']] = ($r_Detector['AdditionalTextIta']=="") ? "" : StringOutDB($r_Detector['AdditionalTextIta']);
    $a_DetectorTypeId[$r_Detector['Id']] = ($r_Detector['DetectorTypeId']=="") ? "" : $r_Detector['DetectorTypeId'];
    
    if ($r_Detector['DetectorTypeId'] == 1)
        $a_SpeedLengthAverage[$r_Detector['Id']] = $r_Detector['SpeedLengthAverage'];
}
/////////////////////////////////////////////

/////////////////////////////////////////////
//Offices
/////////////////////////////////////////////
$offices = $rs->Select('V_JudicialOffice', "CityId='" . $_SESSION['cityid'] . "'");

$a_Office = array();

while ($office = mysqli_fetch_array($offices)) {
    $a_Office[$office['OfficeId']]['OfficeTitleIta'] = isset($office['OfficeTitleIta']) ? StringOutDB($office['OfficeTitleIta']) : "";
    $a_Office[$office['OfficeId']]['City'] = isset($office['City']) ? StringOutDB($office['City']) : "";
    $a_Office[$office['OfficeId']]['Province'] = isset($office['Province']) ? StringOutDB($office['Province']) : "";
    $a_Office[$office['OfficeId']]['Address'] = isset($office['Address']) ? StringOutDB($office['Address']) : "";
    $a_Office[$office['OfficeId']]['ZIP'] = isset($office['ZIP']) ? $office['ZIP'] : "";
    $a_Office[$office['OfficeId']]['Phone'] = isset($office['Phone']) ? $office['Phone'] : "";
    $a_Office[$office['OfficeId']]['Fax'] = isset($office['Fax']) ? StringOutDB($office['Fax']) : "";
    $a_Office[$office['OfficeId']]['Mail'] = isset($office['Mail']) ? StringOutDB($office['Mail']) : "";
    $a_Office[$office['OfficeId']]['PEC'] = isset($office['PEC']) ? StringOutDB($office['PEC']) : "";
    $a_Office[$office['OfficeId']]['Web'] = isset($office['Web']) ? StringOutDB($office['Web']) : "";
}
/////////////////////////////////////////////


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$ProtocolNumber = 0;

//personalizzazione per gli utenti di tipo 2 che sono tutti ad oggi accertatori della provincia di Savona
if ($_SESSION['usertype'] == 2) {
    $rs_Time = $rs->SelectQuery("SELECT MAX( ControllerTime ) ControllerTime FROM Fine WHERE ControllerDate='" . date("Y-m-d") . "'");
    $r_Time = mysqli_num_rows($rs_Time);
    $Time = ($r_Time['ControllerTime'] == "") ? date("H:i:s") : $r_Time['ControllerTime'];
}

$NotificationDate = (strlen(trim($CreationDate)) == 0) ? date("Y-m-d") : DateInDB($CreationDate);


$a_Lan = unserialize(LANGUAGE);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT_CITYID)[$_SESSION['cityid']];
$a_AdditionalMass = unserialize(ADDITIONAL_MASS_CITYID)[$_SESSION['cityid']];
if($a_AdditionalNight == null)
    $a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
    
    if($a_AdditionalMass == null)
        $a_AdditionalMass = unserialize(ADDITIONAL_MASS);


if (isset($_POST['checkbox'])) {
    
    $rs->Start_Transaction();
    
    $chk_warning = false;
    
    foreach ($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);
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
            $rs->Update('LockedPage', $aUpdate, "Title='createdynamic_pec_foreign'");

            header("location: frm_createdynamic_pec.php".$Filters);
            DIE;
        }
    }

    /*TODO da tenere controllato: è preso da tendina verbalizzante che appare solo se Customer.ChiefControllerList è impostata.
    Se presente prende i dati dell'accertatore per sostituirli nella variabile del testo e fare altre personalizzazioni,
    Se non è presente, se l'utente è di un certo tipo ne prende il ControllerId dentro a User.ControllerId e usa quest ultimo per tirare giù i dati*/
    $ChiefControllerName = '';
    if ($ChiefControllerId > 0) {
        $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Id =" . $ChiefControllerId);
        $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

        $ChiefControllerName = trim($r_ChiefController['Qualification']." ".$r_ChiefController['Name']);

    } 
    else if ($_SESSION['usertype']==3 || $_SESSION['usertype']==2) {

        $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Id =" . $_SESSION['controllerid'] . " AND Disabled=0");
        $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

        $ChiefControllerName = trim($r_ChiefController['Qualification']." ".$r_ChiefController['Name']);
    }

    //Inizializza pdf-union
    $pdf_union = new FPDI();

    foreach ($_POST['checkbox'] as $FineId) {
        $a_Documentation[$FineId] = array();

        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=" . $FineId);
        
        //TIPI TRASGRESSORE
        $a_TrespasserTypes = array();
        $rs_Trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $FineId);
        while ($r_Trespassers = mysqli_fetch_assoc($rs_Trespassers)){
            $a_TrespasserTypes['T'.$r_Trespassers['TrespasserTypeId']] = $r_Trespassers;
        }
        /////////////////////////////////////////////
        
        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
        	
        	//TCPDF/////////////////////////////////////////////////////////
        	$pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);
        	$pdf->TemporaryPrint = $ultimate;
        	$pdf->ForeignFine = 1;
        	$pdf->CustomerFooter = 0;
        	//$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);
        	$pdf->SetCreator(PDF_CREATOR);
        	$pdf->SetAuthor($_SESSION['citytitle']);
        	$pdf->SetTitle('Fine PEC');
        	$pdf->SetSubject('');
        	$pdf->SetKeywords('');
        	$pdf->setHeaderFont(array('helvetica', '', 8));
        	$pdf->setFooterFont(array('helvetica', '', 8));
        	$pdf->SetMargins(10, 10, 10);
        	/////////////////////////////////////////////////////////
        	
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
                $ForeignProtocolLetterType1 = $r_Fine['ArticleLetterAssigned'];
                $ForeignProtocolLetterType2 = $r_Fine['ArticleLetterAssigned'];
            } else if(trim($r_Fine['ViolationLetterAssigned'])!=''){
                $ForeignProtocolLetterType1 = $r_Fine['ViolationLetterAssigned'];
                $ForeignProtocolLetterType2 = $r_Fine['ViolationLetterAssigned'];
            } else {
                $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType1'];
                $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType2'];
            }

            //In questo caso "Id" corrisponde al RuleTypeId
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "' AND Id=".$_SESSION['ruletypeid']);
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            
            $rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $FineId);
            $r_FineOwner = mysqli_fetch_array($rs_FineOwner);
            
            $RuleTypeId = $r_RuleType['Id'];

            $ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            $FormTypeId = $r_RuleType['ForeignFormId'];

            $a_PrintObject = explode("*", $r_RuleType['PrintObject' . $a_Lan[$n_LanguageId]]);

            //testo invito in AG - - Garlasco, Albuzzano e Borgo San Siro
            if((($r_Fine['Article']==193 && $r_Fine['Paragraph']=="2") || ($r_Fine['Article']==80 && $r_Fine['Paragraph']=="14")) && $r_Fine['KindSendDate']!=''){
                $rs_Form= $rs->Select('Form', "FormTypeId=101 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            } 
            //Tolta cablatura per Arcola per i testi della velocità
            /*else if($r_Fine['Article']==142){
                $rs_Form= $rs->Select('Form', "FormTypeId=102 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];
            }*/

            //Testo particolare per casistica che non conosciamo - Garlasco, Albuzzano e Borgo San Siro
            if($CreationType==5){
                $rs_Form= $rs->Select('Form', "FormTypeId=100 AND CityId='" . $_SESSION['cityid'] . "'");
                if(mysqli_num_rows($rs_Form)==1) $FormTypeId = mysqli_fetch_array($rs_Form)['FormTypeId'];

            }

            //Se il verbale è di tipo contratto viene generato il pdf con testo tipo 40 che prevede solo relata e bollettini
            if($r_Fine['FineTypeId']==4) $FormTypeId=40;

            $ChargeTotalFee = 0;
            $NotificationFee = $r_Customer['ForeignPECNotificationFee'];
            $ResearchFee = $r_Customer['ForeignPECResearchFee'];

//Gestione forfettario, vale solo per verbali normali (principalmente estero)
//             
//             if ($r_Customer['ForeignTotalFee'] > 0) 
//                 $ChargeTotalFee = $r_Customer['ForeignTotalFee'];
//             else {
//                 if ($r_Customer['ForeignNotificationFee'] > 0) {
//                     $NotificationFee = $r_Customer['ForeignNotificationFee'];
//                 }
//                 $ResearchFee = $r_Customer['ForeignResearchFee'];
//             }

            $int_ContFine++;
            if ($ultimate && $ProtocolNumber > 0) {
                $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);

                $pdf->TemporaryPrint = $ultimate;
                $pdf->ForeignFine = 0;
                $pdf->CustomerFooter = 0;
                //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);


                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($_SESSION['citytitle']);
                $pdf->SetTitle('Fine PEC');
                $pdf->SetSubject('');
                $pdf->SetKeywords('');
                $pdf->setHeaderFont(array('helvetica', '', 8));
                $pdf->setFooterFont(array('helvetica', '', 8));
            }

            //$pdf->Temporary();
            $pdf->SetPrintHeader(true);
            $pdf->RightHeader = true;
            $pdf->PrintObject1 = $a_PrintObject[0];
            $pdf->PrintObject2 = $a_PrintObject[1];
            $pdf->PrintObject3 = $a_PrintObject[2];
            $pdf->SetMargins(10, 8, 10);

            $page_format = ($int_ContFine > 1) ? array('Rotate' => 45) : array();


            $pdf->AddPage('P', $page_format);
            
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Image($_SESSION['blazon'], 10, 8, 15, 23);

            $ManagerName = $r_Customer['ManagerName'];
            $pdf->customer = $ManagerName;


            $pdf->SetFont('helvetica', '', 10, '', true);
            

            if (strlen($r_Customer['ManagerName']) > 22) {
                $pdf->writeHTMLCell(60, 0, 30, '', '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
                $pdf->LN(9);

            } else {

                $pdf->writeHTMLCell(60, 0, 30, '', '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
                $pdf->LN(4);
            }


            if($_SESSION['cityid']=="H452"){
                $str_CustomerAddress = "Art.57 CPP e Art.11 c.1 L.a) e b) CDS";
                $str_CustomerCity = $r_Customer['ManagerAddress']. " " .$r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
            } else {
                $str_CustomerAddress = $r_Customer['ManagerAddress'];
                $str_CustomerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
            }

            $pdf->writeHTMLCell(60, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(70, 0, 30, '', $str_CustomerAddress, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(70, 0, 30, '', $str_CustomerCity, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerPhone'], 0, 0, 1, true, 'L', true);
            
            if ($r_Customer['FineNonDeliveryAddress'] != ""){
                $pdf->SetFont('helvetica', '', 8, '', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(120, 0, 30, '', "Restituzione piego in caso di mancato recapito :", 0, 0, 1, true, 'L', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(120, 0, 30, '', StringOutDB($r_Customer['FineNonDeliveryAddress']), 0, 0, 1, true, 'L', true);
            }
            
            $pdf->LN(10);
            
            //Stampa le finestre delle buste
            $window = true;
            if (!$ultimate && $window){
                $pdf->RoundedRect(2, 8, 90, 21, 3.50, '1111', '', array('color' => array(145)), '');
                $pdf->RoundedRect(93, 38, 115, 45, 3.50, '1111', '', array('color' => array(145)), '');
            }
            
            $pdf->SetFont('helvetica', '', 8, '', true);

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
                            array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_Fine['Fee'], 'settype' => 'flt'),
                            array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_Fine['MaxFee'], 'settype' => 'flt'),
                        );

                    }
                }
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


            //Gestione verbalizzazione da tabella Fine
            if($r_Fine['ReportNotificationDate']!="") $NotificationDate = $r_Fine['ReportNotificationDate'];

//             if($r_Fine['ReportChiefControllerId']>0) {
//                 $ChiefControllerId = $r_Fine['ReportChiefControllerId'];

//                 $rs_ChiefController = $rs->Select('Controller', "Id =" . $ChiefControllerId);
//                 $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

//                 $ChiefControllerName = trim($r_ChiefController['Qualification']." ".$r_ChiefController['Name']);
//             }


//             if($CreationType==5){
//                 $ResearchFee = $r_Customer['ForeignPECResearchFee'];
//                 $NotificationFee = $r_Customer['ForeignTotalFee']; //TODO forse sbagliato

//                 $AdditionalFee = $NotificationFee + $ResearchFee;

//             } else {
//                 $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);

//                 //ChargeTotalFee
//                 if ($ChargeTotalFee > 0) {
//                     $ResearchFee = $ChargeTotalFee - $postalcharge['Zone' . $ZoneId];
//                     $NotificationFee = $postalcharge['Zone' . $ZoneId];
//                 } else {
//                     if ($NotificationFee == 0) {
//                         $NotificationFee = $postalcharge['Zone' . $ZoneId];
//                     }
//                 }
//             }


            $CustomerFee = $r_Fine['CustomerAdditionalFee'];
            $NotificationFee += $r_Fine['OwnerAdditionalFee'] + $CustomerFee;

            $AdditionalFee = $NotificationFee + $ResearchFee;
            
            

//             $CANFee = $postalcharge['CanFee'];
//             $CADFee = $postalcharge['CadFee'];

//             $AdditionalFeeCAN = $AdditionalFee + $CANFee;
//             $AdditionalFeeCAD = $AdditionalFee + $CADFee;

            if ($ChiefControllerId == 0) {
                if ($r_Customer['CityUnion'] > 1) {

                    $rs_ChiefController = $rs->Select('Controller', "CityId='" . $_SESSION['cityid'] . "' AND Sign !='' AND Disabled=0 AND Locality='" . $r_Fine['Locality'] . "'");
                    $r_ChiefController = mysqli_fetch_array($rs_ChiefController);

                    $ChiefControllerName = trim($r_ChiefController['Qualification'] . " " . $r_ChiefController['Name']);

                }
            }
            
            /////////////////////////////////////////////
            //Additional Article
            /////////////////////////////////////////////
            $str_ListArticle = "";
            $str_ArticleDescription = "";
            
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
                                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_AdditionalArticle['Fee'], 'settype' => 'flt'),
                                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$r_AdditionalArticle['MaxFee'], 'settype' => 'flt'),
                                );
                                
                                
                            }
                        }
                    }
                    
                    $n_TotFee += $r_AdditionalArticle['Fee'];
                    $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
                    if ($r_AdditionalArticle['ReducedPayment'] == 1) {
                        $chk_ReducedPayment = $chk_ReducedPayment || true;
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
                    
                    if ($r_AdditionalArticle['PrefectureFixed'] == 1){
                        $str_ArticleDescription .= "con importo fissato da prefettura di euro ".$r_AdditionalArticle['PrefectureFee']." in data ".DateOutDB($r_AdditionalArticle['PrefectureDate']);
                    }
                    
                }
            }
            
            //Preleva il testo dinamico
            $Content=getDynamicContent($FormTypeId,$_SESSION['cityid'],2,$n_LanguageId);
    
            /////////////////////////////////////////////
            //SOTTOTESTI
            /////////////////////////////////////////////
            $EmptyPregMatch = false;
            //Continua a cercare per variabili di sottotesti da sostituire finchè non trova nulla
            while(!$EmptyPregMatch){
                $a_Variables = array();
                $a_Matches = array();
                
                if(preg_match_all("/\{\{.*?\}\}/", $Content, $a_Variables) > 0){
                    $a_Matches = $a_Variables[0];
                    
                    foreach ($a_Matches as $var){
                        $a_Types = getFormVariables($var,$_SESSION['cityid'],$FormTypeId, 2, $n_LanguageId);
                        while ($r_variable = mysqli_fetch_array($rs_variable)){
                            $a_Types[$r_variable['Type']] = StringOutDB($r_variable['Content']);
                        }
                        
                        //TODO Togliere la gestione della descrizione del trasgressore una volta che il sottotesto è definito su db
                        //Sottotesto trasgressore
                        if ($var == "{{TrespasserDescription}}"){
                            $Content = str_replace("{{TrespasserDescription}}", "", $Content);
                        }
                        
                        //Sottotesto pagamenti
                        if ($var == "{{PaymentDescription}}"){
                            $str_Payment = "";
                            if ($r_ArticleTariff['ReducedPayment']){
                                if ($r_Customer['LumpSum'] == 1)
                                    //Ridotto Una tantum
                                    $str_Payment .= $a_Types[4];
                                else
                                    //Ridotto
                                    $str_Payment .= $a_Types[1];
                            }
                            
                            if ($r_Customer['LumpSum'] == 1)
                                //Minimo una tantum
                                $str_Payment .= $a_Types[5];
                            else
                                //Minimo
                                $str_Payment .= $a_Types[2];
                                    
                            if (!$r_ArticleTariff['ReducedPayment']){
                                if ($r_Customer['LumpSum'] == 1)
                                    //Metà una tantum
                                    $str_Payment .= $a_Types[6];
                                else
                                    //Metà
                                    $str_Payment .= $a_Types[3];
                            }
                                    
                            if ($r_Customer['LumpSum'] == 1){
                                //CAN
                                $str_Payment .= $a_Types[7];
                                //CAD
                                $str_Payment .= $a_Types[8];
                            }
                                    
                            $Content = str_replace("{{PaymentDescription}}", $str_Payment, $Content);
                        }
                        
                        //Sottotesto dati per tipo trasgressore
                        else if ($var == "{{TrespasserType}}"){
                            $str_TrespasserType = "";
                            if ($r_Fine['TrespasserTypeId'] == 11) {
                                //Nolleggio
                                $str_TrespasserType = $a_Types[2];
                                
                                $a_FirstTrespasser = $a_TrespasserTypes['T10'];
                                $a_SecondTrespasser = $a_TrespasserTypes['T11'];
                                
                                //PRIMA FIGURA
                                $str_TrespasserType = str_replace("{TrespasserRentName}", $a_FirstTrespasser['CompanyName'] . ' ' . $a_FirstTrespasser['Surname'] . ' ' . $a_FirstTrespasser['Name'], $str_TrespasserType);
                                $str_TrespasserType = str_replace("{DateRent}", DateOutDB($r_Fine['ReceiveDate']), $str_TrespasserType);
                                
                                //SECONDA FIGURA
                                $str_TrespasserType = preg_replace('/' . '{TrespasserName}' . '/', StringOutDB($a_SecondTrespasser['CompanyName'] . ' ' . $a_SecondTrespasser['Surname'] . ' ' . $a_SecondTrespasser['Name']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserBornCity}' . '/', StringOutDB($a_SecondTrespasser['BornPlace']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserBornDate}' . '/', DateOutDB($a_SecondTrespasser['BornDate']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserAddress}' . '/', StringOutDB($a_SecondTrespasser['Address'] . " " . $a_SecondTrespasser['ZIP']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserCity}' . '/', StringOutDB($a_SecondTrespasser['City']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserProvince}' . '/', StringOutDB($a_SecondTrespasser['Province']), $str_TrespasserType, 1);
                            } else if($chk_180 && $r_Fine['TrespasserId1_180']>0) {
                                //ART 180
                                $str_TrespasserType = $a_Types[3];
                            } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16){
                                //Obbligato
                                $str_TrespasserType = $a_Types[4];

                                //di base il primo è il T2 proprietario e il secondo è T3
                                //nel caso della patria potestà
                                //T15 Patria potestà Proprietario/Obbligato è prima figura
                                //T16 Patria potestà Trasgressore è seconda figura
                                $a_FirstTrespasser = in_array('T15', $a_TrespasserTypes) ? $a_TrespasserTypes['T15'] : $a_TrespasserTypes['T2'];
                                $a_SecondTrespasser = in_array('T16', $a_TrespasserTypes) ? $a_TrespasserTypes['T16'] : $a_TrespasserTypes['T3'];
                                  
                                //PRIMA FIGURA
                                $str_TrespasserType = preg_replace('/' . '{TrespasserName}' . '/', StringOutDB($a_FirstTrespasser['CompanyName'] . ' ' . $a_FirstTrespasser['Surname'] . ' ' . $a_FirstTrespasser['Name']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserBornCity}' . '/', StringOutDB($a_FirstTrespasser['BornPlace']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserBornDate}' . '/', DateOutDB($a_FirstTrespasser['BornDate']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserAddress}' . '/', StringOutDB($a_FirstTrespasser['Address'] . " " . $a_FirstTrespasser['ZIP']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserCity}' . '/', StringOutDB($a_FirstTrespasser['City']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserProvince}' . '/', StringOutDB($a_FirstTrespasser['Province']), $str_TrespasserType, 1);
                                
                                //SECONDA FIGURA
                                $str_TrespasserType = preg_replace('/' . '{TrespasserName}' . '/', StringOutDB($a_SecondTrespasser['CompanyName'] . ' ' . $a_SecondTrespasser['Surname'] . ' ' . $a_SecondTrespasser['Name']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserAddress}' . '/', StringOutDB($a_SecondTrespasser['Address'] . " " . $a_SecondTrespasser['ZIP']), $str_TrespasserType, 1);
                                $str_TrespasserType = preg_replace('/' . '{TrespasserCity}' . '/', StringOutDB($a_SecondTrespasser['City']), $str_TrespasserType, 1);
                            } else {
                                //Proprietario
                                $str_TrespasserType = $a_Types[1];
                            }
                            
                            $Content = str_replace("{{TrespasserType}}", $str_TrespasserType, $Content);
                            
                        }
                        
                        //Pagina notifiche
                        else if ($var == "{{NotificationPage}}"){
                            if($CreationType==5 && $_SESSION['cityid']!='A175' && $_SESSION['cityid']!='D925')
                                $Content = str_replace("{{NotificationPage}}", $a_Types[1], $Content);
                                else $Content = str_replace("{{NotificationPage}}", '', $Content);
                        }
                        
                        //Pagina decurtazione punti - testo
                        else if ($var == "{{LicensePointPage}}"){
                            //var_dump($FormTypeId, $chk_126Bis, $r_Fine['ReasonId'], $r_ArticleTariff['126Bis'], $r_AdditionalArticle['126Bis']);
                            if ($chk_126Bis && $FormTypeId!=101 && $r_Fine['ReasonId']!=100){
                                $Content = str_replace("{{LicensePointPage}}", $a_Types[1], $Content);
                            }
                            else $Content = str_replace("{{LicensePointPage}}", '', $Content);
                        }
                        //Pagina decurtazione punti - trasgressore
                        else if ($var == "{{LicensePointPageHead}}"){
                            if ($chk_126Bis && $FormTypeId!=101 && $r_Fine['ReasonId']!=100){
                                $str_LicensePointPageTresp = "";
                                if ($trespasser['Genre'] != 'D'){
                                    $str_LicensePointPageTresp = $a_Types[1];
                                    $str_LicensePointPageTresp = str_replace("{TrespasserName}", StringOutDB($trespasser['Name'] . ' ' . $trespasser['Surname']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserAddress}", StringOutDB($trespasser['Address']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $str_LicensePointPageTresp);
                                }
                                else {
                                    $str_LicensePointPageTresp = $a_Types[2];
                                    $str_LicensePointPageTresp = str_replace("{TrespasserName}", StringOutDB($trespasser['CompanyName']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $str_LicensePointPageTresp);
                                    $str_LicensePointPageTresp = str_replace("{TrespasserAddress}", StringOutDB($trespasser['Address']), $str_LicensePointPageTresp);
                                }
                                $Content = str_replace("{{LicensePointPageHead}}", $str_LicensePointPageTresp, $Content);
                            } else $Content = str_replace("{{LicensePointPageHead}}", "", $Content);
                        }
                        
                        //Rilevatore
                        else if ($var == "{{DetectorText}}"){
                            if (isset($a_DetectorTypeId[$r_Fine['DetectorId']])){
                                //Velocità
                                if ($a_DetectorTypeId[$r_Fine['DetectorId']] == 1)
                                    $Content = str_replace("{{DetectorText}}", "<strong>".$a_Types[1]."</strong>", $Content);
                                //Semaforo
                                else if ($a_DetectorTypeId[$r_Fine['DetectorId']] == 2)
                                    $Content = str_replace("{{DetectorText}}", "<strong>".$a_Types[2]."</strong>", $Content);
                                //Non gestito
                                else $Content = str_replace("{{DetectorText}}", '', $Content);
                            } else $Content = str_replace("{{DetectorText}}", '', $Content);
                        }
                        
                        //Mancata contestazione
                        else if ($var == "{{ReasonText}}"){
                            $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescriptionIta'])) > 0) ? $r_FineOwner['ReasonDescriptionIta'] : $r_Fine['ReasonDescriptionIta'];
                            if ($CreationType==5)
                                $Content = str_replace("{{ReasonText}}", $a_Types[2], $Content);
                            else if ($str_ReasonDescription != "")
                                $Content = str_replace("{{ReasonText}}", $a_Types[1], $Content);
                            else $Content = str_replace("{{ReasonText}}", '', $Content);
                        }
                        
                        else $Content = str_replace($var, $a_Types[1], $Content);
                        
                    }
                } else $EmptyPregMatch = true;
            }
            //
            
            //FIRMATARIO/////
            $rs_SignController = $rs->Select("Controller", "Id=".$_SESSION['controllerid']);
            $r_SignController = mysqli_fetch_array($rs_SignController);
            $SignController = (isset($r_SignController['Qualification']) ? $r_SignController['Qualification'].' ' : '').$r_SignController['Name'];
            $Content = "<br /><br /><br /><br />Questo documento è firmato digitalmente da <strong>$SignController</strong>" . $Content;
            ////////////////
            
            //TODO togliere cablatura e creare sottotesto
            $Content = "{{HeaderSarida}}</br>" . $Content;
            
            $str_Header = '<br />
<br /><br />
<table border="0" cellpadding="1" cellspacing="1" style="width:908px">
	<tbody>
		<tr>
			<td style="width:283px">&nbsp;</td>
			<td style="width:550px"><span style="font-size:10px;line-height:1.1px"><strong>{TrespasserName}</strong><br />
			{TrespasserAddress}<br />
			{TrespasserZip}&nbsp;{TrespasserCity}<br />
			{TrespasserCountry}</span></td>
		</tr>
	</tbody>
</table><br /><br />
';
            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
                );
            
            $str_Header = str_replace("{TrespasserName}", strtoupper(StringOutDB((isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '') . $trespasser['Surname'] . ' ' . $trespasser['Name'])), $str_Header);
            $str_Header = str_replace("{TrespasserAddress}", strtoupper(StringOutDB($str_TrespasserAddress != "" ? $str_TrespasserAddress : "")), $str_Header);
            $str_Header = str_replace("{TrespasserCountry}", strtoupper(StringOutDB($trespasser['CountryTitle'])), $str_Header);
            $str_Header = str_replace("{TrespasserCity}", strtoupper(StringOutDB($trespasser['City'])), $str_Header);
            $str_Header = str_replace("{TrespasserProvince}", strtoupper(StringOutDB($trespasser['Province'])), $str_Header);
            $str_Header = str_replace("{TrespasserZip}", StringOutDB($trespasser['ZIP']), $str_Header);
            $str_Header = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $str_Header);
            $str_Header = str_replace("{TrespasserBornCity}", strtoupper(StringOutDB($trespasser['BornPlace'])), $str_Header);
            
            $Content = str_replace("{{HeaderSarida}}", $str_Header, $Content);
            //
            
            /////////////////////////////////////////////
            //VARIABILI
            /////////////////////////////////////////////
            
            //Trespasser/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $rs_trespasser = $rs->Select('Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $r_trespasser = mysqli_fetch_array($rs_trespasser);
            $str_TrespasserAddress =  trim(
                $trespasser['Address'] ." ".
                $trespasser['StreetNumber'] ." ".
                $trespasser['Ladder'] ." ".
                $trespasser['Indoor'] ." ".
                $trespasser['Plan']
            );
            
            $Content = str_replace("{TrespasserName}", StringOutDB((isset($r_trespasser['CompanyName']) ? $r_trespasser['CompanyName'].' ' : '') . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name']), $Content);
            $Content = str_replace("{TrespasserGenre}", $a_GenreLetter[$trespasser['Genre']], $Content);
            $Content = str_replace("{TrespasserCity}", StringOutDB($r_trespasser['City']), $Content);
            $Content = str_replace("{TrespasserProvince}", StringOutDB($r_trespasser['Province']), $Content);
            $Content = str_replace("{TrespasserAddress}", StringOutDB($str_TrespasserAddress), $Content);
            $Content = str_replace("{TrespasserZip}", StringOutDB($r_trespasser['ZIP']), $Content);
            $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);
            $Content = str_replace("{TrespasserBornCity}", StringOutDB($trespasser['BornPlace']), $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Fine/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($r_ArticleTariff['ReducedPayment']) {
                $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
                
            } else {
                $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
            }
            
            $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
            $Content = str_replace("{FineTime}", ($r_Fine['FineTime'] != "" || $r_Fine['FineTime'] || null) ? TimeOutDB($r_Fine['FineTime']) : "", $Content);
            $Content = str_replace("{VehicleTypeId}", StringOutDB($r_Fine['VehicleTitleIta']), $Content);
            $Content = str_replace("{VehiclePlate}", StringOutDB($r_Fine['VehiclePlate']), $Content);
            $Content = str_replace("{VehicleBrand}", StringOutDB($r_Fine['VehicleBrand']), $Content);
            $Content = str_replace("{VehicleModel}", StringOutDB($r_Fine['VehicleModel']), $Content);
            $Content = str_replace("{VehicleColor}", StringOutDB($r_Fine['VehicleColor']), $Content);
            $Content = str_replace("{IuvCode}", StringOutDB($r_Fine['IuvCode']), $Content);
            $Content = str_replace("{Code}", $r_Fine['Code'], $Content);
            $Content = str_replace("{PagoPA1}", $r_Fine['PagoPA1']. " per ".$str_PaymentDay1, $Content);
            $Content = str_replace("{PagoPA2}", $r_Fine['PagoPA2']. " per ".$str_PaymentDay2, $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //Article/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $Paragraph = ($r_Fine['Paragraph'] == "0") ? "" : $r_Fine['Paragraph'] . " ";
            $Letter = ($r_Fine['Letter'] == "0") ? "" : $r_Fine['Letter'];
            $str_ArticleId = $r_Fine['Article'] . "/" . $Paragraph . $Letter;
            
            if (($r_ArticleTariff['UseAdditionalSanction'] != ADDITIONAL_SANCTION_NOT_EXPECTED) && ($r_ArticleTariff['AdditionalSanctionId'] > 0)) {
                $rs_AdditionalSanction = $rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']);
                $r_AdditionalSanction = mysqli_fetch_array($rs_AdditionalSanction);
                $str_AdditionalSanction = StringOutDB("SANZIONE ACCESSORIA: " . $r_AdditionalSanction['TitleIta']);
            } else {
                $str_AdditionalSanction = "SANZIONI ACCESSORIE: non previste";
            }
            
            $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]])) > 0) ? $r_FineOwner['ArticleDescription' . $a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription' . $a_Lan[$n_LanguageId]];
           
            $str_ArticleDescription = str_replace("{Speed}", intval($r_Fine['Speed']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{SpeedExcess}", intval(intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit'])), $str_ArticleDescription);
            $str_ArticleDescription = str_replace("{TimeTypeId}", $r_Fine['TimeDescriptionIta'], $str_ArticleDescription);
            
            $str_ExpirationDate = (isset($r_Fine['ExpirationDate']) || $r_Fine['ExpirationDate']!="") ? DateOutDB($r_Fine['ExpirationDate']) : "";
            $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescriptionIta'])) > 0) ? $r_FineOwner['ReasonDescriptionIta'] : $r_Fine['ReasonDescriptionIta'];
            $str_ArticleDescription = $str_ArticleDescription;
            
            $Content = str_replace("{ReasonId}", StringOutDB($str_ReasonDescription), $Content);
            $Content = str_replace("{ArticleId}", $str_ArticleId .$str_ListArticle, $Content);
            $Content = str_replace("{ArticleDescription}", StringOutDB($str_ArticleDescription), $Content);
            $Content = str_replace("{ArticleAdditionalNight}", $str_AdditionalNight, $Content);
            $Content = str_replace("{ArticleAdditionalMass}", $str_AdditionalMass, $Content);
            $Content = str_replace("{AdditionalSanctionId}", $str_AdditionalSanction, $Content);
            $Content = str_replace("{ExpirationDate}", $str_ExpirationDate, $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Detector/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $SpeedExcess = 0;
            if ($r_Fine['DetectorId'] > 0) {
                
                if (isset($a_SpeedLengthAverage[$r_Fine['DetectorId']])){
                    $SpeedLengthAverage = $a_SpeedLengthAverage[$r_Fine['DetectorId']];
                    $SpeedTimeAverage = $r_Fine['SpeedTimeAverage'] > 0 ? $r_Fine['SpeedTimeAverage'] : ($SpeedLengthAverage * 3.6) / $r_Fine['SpeedControl'];
                    $SpeedExcess = intval($r_Fine['Speed']) - intval($r_Fine['SpeedLimit']);
                } else {
                    $SpeedLengthAverage = $SpeedTimeAverage = $SpeedExcess = 0;
                }
                
                
                $Content = str_replace("{Speed}", intval($r_Fine['Speed']), $Content);
                $Content = str_replace("{SpeedControl}", intval($r_Fine['SpeedControl']), $Content);
                $Content = str_replace("{SpeedLimit}", intval($r_Fine['SpeedLimit']), $Content);
                $Content = str_replace("{SpeedExcess}", intval($SpeedExcess), $Content);
                $Content = str_replace("{TimeTypeId}", StringOutDB($r_Fine['TimeDescriptionIta']), $Content);
                
                $Content = str_replace("{DetectorPosition}", $a_DetectorPosition[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorKind}", $a_DetectorKind[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorCode}", $a_DetectorCode[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorTolerance}", intval($a_DetectorTolerance[$r_Fine['DetectorId']]), $Content);
                $Content = str_replace("{DetectorRatification}", $a_DetectorRatification[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorAdditionalText}", $a_DetectorAdditionalTextIta[$r_Fine['DetectorId']], $Content);
                $Content = str_replace("{DetectorSpeedLengthAverage}", $SpeedLengthAverage, $Content);
                $Content = str_replace("{DetectorSpeedTimeAverage}", number_format($SpeedTimeAverage, 3, ',', '.'), $Content);
                $Content = str_replace("{DetectorSpeedTimeHourAverage}", number_format($SpeedTimeAverage/3600, 3, ',', '.'), $Content);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //Controller////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $str_ControllerName = trim(StringOutDB($r_Fine['ControllerQualification']." ".$r_Fine['ControllerName']));
            $str_ControllerCode = trim($r_Fine['ControllerCode']);
            
            $rs_FineAdditionalController = $rs->Select('V_AdditionalController', "FineId=" . $FineId);
            while ($r_FineAdditionalController = mysqli_fetch_array($rs_FineAdditionalController)){
                $str_ControllerCode="";
                $str_ControllerName .= ", ".trim(StringOutDB($r_FineAdditionalController['ControllerQualification']." ".$r_FineAdditionalController['ControllerName']));
            }
            
            $str_ControllerDate = DateOutDB($r_Fine['ControllerDate']);
            $str_ControllerTime = ($r_Fine['ControllerTime'] != "" || $r_Fine['ControllerTime'] || null) ? TimeOutDB($r_Fine['ControllerTime']) : "";
            $str_ControllerConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$str_ControllerName. " Matr. ". $str_ControllerCode;
            $str_ChiefConvalidation = "Convalidato il giorno ". $str_ControllerDate ." alle ore ". $str_ControllerTime ." - ".$ChiefControllerName;
            
            $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
            $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);
            $Content = str_replace("{ControllerDate}", $str_ControllerDate, $Content);
            $Content = str_replace("{ControllerTime}", $str_ControllerTime, $Content);
            $Content = str_replace("{ChiefConvalidation}", $str_ChiefConvalidation, $Content);
            $Content = str_replace("{ControllerConvalidation}", $str_ControllerConvalidation, $Content);
            $Content = str_replace("{ChiefControllerName}", $ChiefControllerName, $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            /////////////////////////////////////////////
            //126 BIS
            /////////////////////////////////////////////
            $str_ArticleAdditionalText = "";
            $article126bis = null;
            if ($chk_126Bis) {
                $articles126bis = $rs->SelectQuery("Select A.AdditionalTextIta, ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId WHERE A.CityId='" . $_SESSION['cityid'] . "' AND A.Article=126 AND A.Letter='bis' AND ART.Year = " . $_SESSION['year']);
                $article126bis = mysqli_fetch_array($articles126bis);
                
                $str_ArticleAdditionalText = ($r_Fine['ArticleNumber'] > 1) ? $article126bis['AdditionalTextIta'] : $r_Fine['ArticleAdditionalTextIta'];
            } else {
                if ($r_Fine['Article']==126 ){
                    $str_ArticleAdditionalText = "";
                } else{
                    $str_ArticleAdditionalText = $r_Fine['ArticleAdditionalTextIta'];
                }
            }
            
            $str_Date = "";
            if ($r_Customer['ManagerSignName'] == "") {
                if ($r_Customer['CityUnion'] > 1) {
                    $str_Date = $r_Fine['CityTitle'] . ", " . DateOutDB($NotificationDate);
                } else {
                    $str_Date = $r_Customer['ManagerName'] . ", " . DateOutDB($NotificationDate);
                }
            } else {
                $str_Date =  $r_Customer['ManagerSignName'] . ", " . DateOutDB($NotificationDate);
            }
            
            $PartialFee = $n_TotPartialFee;
            $MaxFee = $n_TotMaxFee * FINE_MAX;
            
            $TotalPartialFee = $PartialFee + $AdditionalFee;
//             $TotalPartialFeeCAN = $PartialFee + $AdditionalFeeCAN;
//             $TotalPartialFeeCAD = $PartialFee + $AdditionalFeeCAD;
            
            $TotalFee = $n_TotFee + $AdditionalFee;
//             $TotalFeeCAN = $n_TotFee + $AdditionalFeeCAN;
//             $TotalFeeCAD = $n_TotFee + $AdditionalFeeCAD;
            
            $TotalMaxFee = $MaxFee + $AdditionalFee;
//             $TotalMaxFeeCAN = $MaxFee + $AdditionalFeeCAN;
//             $TotalMaxFeeCAD = $MaxFee + $AdditionalFeeCAD;
            
            $str_ProtocolLetter = ($RuleTypeId == 1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
            
            $Content = str_replace("{ArticleAdditionalText}", StringOutDB($str_ArticleAdditionalText), $Content);
            
            $Content = str_replace("{DecurtationPoints}", $n_LicensePoint, $Content);
            $Content = str_replace("{Fee126bis}", (!empty($article126bis) ? NumberDisplay($article126bis['Fee']) :  NumberDisplay(0)), $Content);
            $Content = str_replace("{MaxFee126bis}", (!empty($article126bis) ? NumberDisplay($article126bis['MaxFee']) :  NumberDisplay(0)), $Content);
            
            $Content = str_replace("{PreviousProtocolId}", $PreviousProtocolId, $Content);
            $Content = str_replace("{PreviousProtocolYear}", $PreviousProtocolYear, $Content);
            $Content = str_replace("{PreviousFineDate}", $PreviousFineDate, $Content);

            $Content = str_replace("{Locality}", StringOutDB($r_Fine['CityTitle']), $Content);
            $Content = str_replace("{Address}", StringOutDB($r_Fine['Address']), $Content);

            $Content = str_replace("{Gps}", (trim($r_Fine['GpsLat'])=="") ? "" : "( ".$r_Fine['GpsLat'].", ".$r_Fine['GpsLong']. " )", $Content);

            $Content = str_replace("{BankOwner}", StringOutDB($r_Customer['ForeignBankOwner']), $Content);
            $Content = str_replace("{BankName}", StringOutDB($r_Customer['ForeignBankName']), $Content);
            $Content = str_replace("{BankAccount}", $r_Customer['ForeignBankAccount'], $Content);
            $Content = str_replace("{BankSwift}", $r_Customer['ForeignBankSwift'], $Content);
            $Content = str_replace("{BankIban}", $r_Customer['ForeignBankIban'], $Content);

            $Content = str_replace("{CurrentDate}", DateOutDB($NotificationDate), $Content);
            $Content = str_replace("{CurrentTime}", date("H:i"), $Content);
            $Content = str_replace("{Date}", $str_Date, $Content);

            $Content = str_replace("{ManagerDataEntryName}", StringOutDB($r_Customer['ManagerDataEntryName']), $Content);
            $Content = str_replace("{ManagerProcessName}", StringOutDB($r_Customer['ManagerProcessName']), $Content);
            
            $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
            $Content = str_replace("{ManagerAdditionalName}", StringOutDB($r_Customer['ManagerAdditionalName']), $Content);
            $Content = str_replace("{ManagerSector}", StringOutDB($r_Customer['ManagerSector']), $Content);
            $Content = str_replace("{ManagerAddress}", StringOutDB($r_Customer['ManagerAddress']), $Content);
            $Content = str_replace("{ManagerZIP}", $r_Customer['ManagerZIP'], $Content);
            $Content = str_replace("{ManagerCity}", StringOutDB($r_Customer['ManagerCity']), $Content);
            $Content = str_replace("{ManagerProvince}", StringOutDB($r_Customer['ManagerProvince']), $Content);
            $Content = str_replace("{ManagerPhone}", $r_Customer['ManagerPhone'], $Content);
            //Località firma verbale
            $Content = str_replace("{ManagerSignName}", $r_Customer['ManagerSignName'], $Content);

            $Content = str_replace("{ProtocolYear}", $ProtocolYear, $Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);

            $Content = str_replace("{PartialFee}", NumberDisplay($PartialFee), $Content);
            $Content = str_replace("{TotalPartialFee}", NumberDisplay($TotalPartialFee), $Content);
//             $Content = str_replace("{TotalPartialFeeCAN}", NumberDisplay($TotalPartialFeeCAN), $Content);
//             $Content = str_replace("{TotalPartialFeeCAD}", NumberDisplay($TotalPartialFeeCAD), $Content);

            $Content = str_replace("{Fee}", NumberDisplay($n_TotFee), $Content);
            $Content = str_replace("{TotalFee}", NumberDisplay($TotalFee), $Content);
//             $Content = str_replace("{TotalFeeCAN}", NumberDisplay($TotalFeeCAN), $Content);
//             $Content = str_replace("{TotalFeeCAD}", NumberDisplay($TotalFeeCAD), $Content);

            $Content = str_replace("{ResearchFee}", NumberDisplay($ResearchFee), $Content);
            $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);
            $Content = str_replace("{ChargeTotalFee}", NumberDisplay($ChargeTotalFee), $Content);
//             $Content = str_replace("{CANFee}", NumberDisplay($CANFee), $Content);
//             $Content = str_replace("{CADFee}", NumberDisplay($CADFee), $Content);

            $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
            $Content = str_replace("{TotalMaxFee}", NumberDisplay($TotalMaxFee), $Content);
//             $Content = str_replace("{TotalMaxFeeCAN}", NumberDisplay($TotalMaxFeeCAN), $Content);
//             $Content = str_replace("{TotalMaxFeeCAD}", NumberDisplay($TotalMaxFeeCAD), $Content);

            $Content = str_replace("{AdditionalFee}", NumberDisplay($AdditionalFee), $Content);
//             $Content = str_replace("{AdditionalFeeCAN}", NumberDisplay($AdditionalFeeCAN), $Content);
//             $Content = str_replace("{AdditionalFeeCAD}", NumberDisplay($AdditionalFeeCAD), $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Judicial Office////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $Content = str_replace("{Judge}", isset($a_Office[1]) ? $a_Office[1]['OfficeTitleIta'] : "", $Content);
            $Content = str_replace("{JudgeCity}", isset($a_Office[1]) ? $a_Office[1]['City'] : "" , $Content);
            $Content = str_replace("{JudgeProvince}", isset($a_Office[1]) ? $a_Office[1]['Province'] : "" , $Content);
            $Content = str_replace("{JudgeAddress}", isset($a_Office[1]) ? $a_Office[1]['Address'] : "" , $Content);
            $Content = str_replace("{JudgeZIP}", isset($a_Office[1]) ? $a_Office[1]['ZIP'] : "" , $Content);
            $Content = str_replace("{JudgePhone}", isset($a_Office[1]) ? $a_Office[1]['Phone'] : "" , $Content);
            $Content = str_replace("{JudgeFax}", isset($a_Office[1]) ? $a_Office[1]['Fax'] : "" , $Content);
            $Content = str_replace("{JudgeMail}", isset($a_Office[1]) ? $a_Office[1]['Mail'] : "" , $Content);
            $Content = str_replace("{JudgePEC}", isset($a_Office[1]) ? $a_Office[1]['PEC'] : "" , $Content);
            $Content = str_replace("{JudgeWeb}", isset($a_Office[1]) ? $a_Office[1]['Web'] : "" , $Content);
            
            $Content = str_replace("{Prefect}", isset($a_Office[2]) ? $a_Office[2]['OfficeTitleIta'] : "" , $Content);
            $Content = str_replace("{PrefectCity}", isset($a_Office[2]) ? $a_Office[2]['City'] : "" , $Content);
            $Content = str_replace("{PrefectProvince}", isset($a_Office[2]) ? $a_Office[2]['Province'] : "" , $Content);
            $Content = str_replace("{PrefectAddress}", isset($a_Office[2]) ? $a_Office[2]['Address'] : "" , $Content);
            $Content = str_replace("{PrefectZIP}", isset($a_Office[2]) ? $a_Office[2]['ZIP'] : "" , $Content);
            $Content = str_replace("{PrefectPhone}", isset($a_Office[2]) ? $a_Office[2]['Phone'] : "" , $Content);
            $Content = str_replace("{PrefectFax}", isset($a_Office[2]) ? $a_Office[2]['Fax'] : "" , $Content);
            $Content = str_replace("{PrefectMail}", isset($a_Office[2]) ? $a_Office[2]['Mail'] : "" , $Content);
            $Content = str_replace("{PrefectPEC}", isset($a_Office[2]) ? $a_Office[2]['PEC'] : "" , $Content);
            $Content = str_replace("{PrefectWeb}", isset($a_Office[2]) ? $a_Office[2]['Web'] : "" , $Content);
            
            $Content = str_replace("{Court}", isset($a_Office[3]) ? $a_Office[3]['OfficeTitleIta'] : "" , $Content);
            $Content = str_replace("{CourtCity}", isset($a_Office[3]) ? $a_Office[3]['City'] : "" , $Content);
            $Content = str_replace("{CourtProvince}", isset($a_Office[3]) ? $a_Office[3]['Province'] : "" , $Content);
            $Content = str_replace("{CourtAddress}", isset($a_Office[3]) ? $a_Office[3]['Address'] : "" , $Content);
            $Content = str_replace("{CourtZIP}", isset($a_Office[3]) ? $a_Office[3]['ZIP'] : "" , $Content);
            $Content = str_replace("{CourtPhone}", isset($a_Office[3]) ? $a_Office[3]['Phone'] : "" , $Content);
            $Content = str_replace("{CourtFax}", isset($a_Office[3]) ? $a_Office[3]['Fax'] : "" , $Content);
            $Content = str_replace("{CourtMail}", isset($a_Office[3]) ? $a_Office[3]['Mail'] : "" , $Content);
            $Content = str_replace("{CourtPEC}", isset($a_Office[3]) ? $a_Office[3]['PEC'] : "" , $Content);
            $Content = str_replace("{CourtWeb}", isset($a_Office[3]) ? $a_Office[3]['Web'] : "" , $Content);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//             $str_ReceiveDate = ($r_Fine['TrespasserTypeId'] == 1 && $r_Fine['ReceiveDate'] != "") ? ' - data id trasgr.' . DateOutDB($r_Fine['ReceiveDate']) : '';


//             if($chk_180 && $r_Fine['TrespasserId1_180']>0){

//                 $rs_trespasser = $rs->Select('Trespasser', "Id=" . $r_Fine['TrespasserId1_180']);
//                 $r_trespasser = mysqli_fetch_array($rs_trespasser);
//                 $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
//                 $Content = str_replace("{TrespasserCity}", $r_trespasser['City'], $Content);
//                 $Content = str_replace("{TrespasserProvince}", $r_trespasser['Province'], $Content);

//             } else if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {
//                 $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=2");
//                 $r_trespasser = mysqli_fetch_array($rs_trespasser);
//                 $Content = str_replace("{TrespasserName}", $r_trespasser['CompanyName'] . ' ' . $r_trespasser['Surname'] . ' ' . $r_trespasser['Name'], $Content);
//                 $Content = str_replace("{TrespasserCity}", $r_trespasser['City'], $Content);

//                 if(strlen($r_trespasser['BornDate']) == 10 && strlen(trim($r_trespasser['BornPlace'])) > 0){
//                     $Content = str_replace("{TrespasserBornDate}", DateOutDB($r_trespasser['BornDate']), $Content);
//                     $Content = str_replace("{TrespasserBornCity}", $r_trespasser['BornPlace'], $Content);
//                 } else {
//                     $Content = str_replace(" il {TrespasserBornDate}", "", $Content);
//                     $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
//                 }

//                 $str_Province = (isset($r_trespasser['Province']) && $r_trespasser['Province']!='') ?  " (".$r_trespasser['Province'].")" : "";

//                 $Content = str_replace("{TrespasserAddress} {TrespasserCity} ({TrespasserProvince})<col>", $r_trespasser['Address']. " ".$r_trespasser['City'].$str_Province."<col>", $Content);

//             } else {
//                 $Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
//                 $Content = str_replace("{TrespasserCity}", $trespasser['City'], $Content);
//                 if(isset($trespasser['Province']) && trim($trespasser['Province'])!=''){
//                     $Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);

//                 } else {
//                     $Content = str_replace("({TrespasserProvince})", '', $Content);

//                 }

//             }

//             if(strlen($trespasser['BornDate']) == 10 && strlen(trim($trespasser['BornPlace'])) > 0){
//                 $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']) . $str_ReceiveDate, $Content);
//                 $Content = str_replace("{TrespasserBornCity}", $trespasser['BornPlace'], $Content);
//             } else {
//                 $Content = str_replace(" il {TrespasserBornDate}", $str_ReceiveDate, $Content);
//                 $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
//             }
            
            /////////////////////////////////////////////
            //Protocol
            /////////////////////////////////////////////
            if ($ultimate) {
                if($r_Fine['ProtocolId'] > 0) $ProtocolNumber = $r_Fine['ProtocolId'];
                else if ($r_Fine['ProtocolIdAssigned'] == 0) {
                    $str_WhereRule = ($RuleTypeId == 1) ? " AND RuleTypeId=1" : " AND RuleTypeId!=1";
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . $str_WhereRule);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                    $ProtocolNumber = $n_Protocol + 1;
                } else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];

                $Content = str_replace("{ProtocolId}", $ProtocolNumber, $Content);

            } else {
                if ($ProtocolNumber == 0) {
                    $str_WhereRule = ($RuleTypeId == 1) ? " AND RuleTypeId=" . $RuleTypeId : " AND RuleTypeId!=1";
                    $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . $str_WhereRule);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                    $ProtocolNumber = $n_Protocol;
                }
                $ProtocolNumber++;
                $Content = str_replace("{ProtocolId}", $ProtocolNumber . " - PROVV", $Content);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //Nome del file//////////////////////////////
            $RndCode = "";
            for ($i = 0; $i < 5; $i++) {
                $n = rand(1, 24);
                $RndCode .= substr($strCode, $n, 1);
                $n = rand(0, 9);
                $RndCode .= $n;
            }
            
            $strProtocolNumber = "";
            for ($k = strlen((string)$ProtocolNumber); $k < 9; $k++) {
                $strProtocolNumber .= "0";
            }
            $strProtocolNumber .= $ProtocolNumber;

            $Documentation = $ProtocolYear . "_" . $strProtocolNumber . "_" . date("Y-m-d") . "_" . $_SESSION['cityid'] . "_$RndCode." . PDFA;
            $a_Documentation[$FineId][] = $Documentation;
            /////////////////////////////////////////////
            
            //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
            $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
            $TCPDF_pagebreak = '<br pagebreak="true" />';
            preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
            //
            
            $pdf->SetAutoPageBreak(true, 0);
            $pdf->SetPrintHeader(false);

            $pdf->writeHTML($Content, true, false, true, false, '');
            
            //PAGOPA
            if($r_Fine['PagoPA1']!='' && $r_Fine['PagoPA2']!=''){
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
            
                $pdf->write2DBarcode($url_PagoPAPage.$r_Fine['PagoPA1'], 'QRCODE,H', 40, 240, 30, 30, $style, 'N');
                $pdf->writeHTMLCell(70, 0, 20, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                
                $pdf->write2DBarcode($url_PagoPAPage.$r_Fine['PagoPA2'], 'QRCODE,H', 140, 240, 30, 30, $style, 'N');
                $pdf->writeHTMLCell(70, 0, 120, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
            }
            //
            
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            ////
            ////
            ////    BILL
            ////
            ////
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////


            if ($r_Customer['ForeignPostalType'] != "" && $FormTypeId!=101) {

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


                $pdf->scelta_td_bollettino($r_Customer['ForeignPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['ForeignBankAccount']);
                $pdf->iban_bollettino($r_Customer['ForeignBankIban']);
                $pdf->intestatario_bollettino(substr($r_Customer['ForeignBankOwner'], 0, 50));
                $pdf->causale_bollettino($str_Object, $str_PaymentDay1);
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address);
                $pdf->importo_in_lettere_bollettino($numeroLetterale);
                if ($r_Customer['ForeignPostalAuthorization'] != "") {
                    $pdf->autorizzazione_bollettino($r_Customer['ForeignPostalAuthorization']);
                }
                $pdf->set_quinto_campo($r_Customer['ForeignPostalType'], $str_FifthField);




                if ($r_Customer['LumpSum'] == 1) {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFeeCAD : $TotalMaxFeeCAD;
                }else {
                    $flt_Amount = ($r_ArticleTariff['ReducedPayment']) ? $TotalFee : $TotalMaxFee;
                }


                $pdf->crea_bollettino_inverso();

                $a_Address = array();
                $a_Address['Riga1'] = $trespasser['Address'];
                $a_Address['Riga2'] = '';
                $a_Address['Riga3'] = $trespasser['ZIP'];
                $a_Address['Riga4'] = $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ')';


                $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 1 : 2;
                $str_FifthField = SetFifthField($a_FifthField);
                $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);


                $numeroLetterale = $NW->converti_numero_bollettino($flt_Amount);


                $pdf->scelta_td_bollettino($r_Customer['ForeignPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['ForeignBankAccount'], 'due');
                $pdf->iban_bollettino($r_Customer['ForeignBankIban'], 'due');
                $pdf->intestatario_bollettino(substr($r_Customer['ForeignBankOwner'], 0, 50), 'due');
                $pdf->causale_bollettino($str_Object, $str_PaymentDay2, 'due');
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address, 'due');
                $pdf->importo_in_lettere_bollettino($numeroLetterale, 'due');
                if ($r_Customer['ForeignPostalAuthorization'] != "") {
                    $pdf->autorizzazione_bollettino($r_Customer['ForeignPostalAuthorization'], 'due');

                }

                $page_format = array('Rotate'=>45);
                $pdf->AddPage('P', $page_format);
                // BOLLETTINI 674   451     896 ->

            }

            //Conta le pagine attuali, se sono dispari aggiunge una pagina bianca in fondo, porta il puntatore alla pagina prima del bollettino e la muove in quella posizione
            $PageNo= $pdf->PageNo();
            if($PageNo%2 == 1){
                $pdf->AddPage('P', $page_format);
                $pdf->movePage($PageNo, $PageNo-1);
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
                    array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                    array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                    array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                    array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_ChiefController['Id'], 'settype' => 'int'),
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
                $rs->Update('Fine', $aUpdate, 'Id=' . $FineId);


                $aInsert = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId),
                );
                $rs->Insert('FineDocumentation', $aInsert);


                if($CreationType==5 || $n_Duplicate==4){

                    $a_FineHistory = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 6, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserId'], 'settype' => 'int'),
                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $r_Fine['TrespasserTypeId'], 'settype' => 'int'),
                        array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$CustomerFee, 'settype' => 'flt'),
                        array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$NotificationFee, 'settype' => 'flt'),
                        array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => (float)$ResearchFee, 'settype' => 'flt'),
                        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $r_ChiefController['Id'], 'settype' => 'int'),
                        array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                        array('field' => 'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),

                    );

                    if($CreationType==5){
                        $a_FineHistory[] = array('field'=>'ResultId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int');
                        $a_FineHistory[] = array('field'=>'DeliveryDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                    } else {
                        $a_FineHistory[] = array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$NotificationDate);
                    }


                    $rs->Insert('FineHistory', $a_FineHistory);

                    if($CreationType==5){
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

                if (!is_dir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId)) {
                    mkdir(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId, 0777);
                }
            }
            
            //SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE////////////////////////////////////////////
            if (!$ultimate){
                $TotalPages = $pdf->PageNo();
                for ($i=1; $i<=$TotalPages; $i++){
                    $pdf->setPage($i, true);
                    $pdf->SetXY(10, 250);
                    $pdf->StartTransform();
                    $pdf->Rotate(50);
                    $pdf->SetFont('helvetica', '', 22);
                    $pdf->SetTextColor(190);
                    $pdf->Cell(280,0,'S   T   A   M   P   A         P   R   O   V   V   I   S   O   R   I   A',0,1,'C',0,'');
                    $pdf->StopTransform();
                }
            }
            $pdf->Output(FOREIGN_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation, "F");
            $n_PageCount = $pdf_union->setSourceFile(FOREIGN_FINE . "/" . $_SESSION['cityid'] . "/" . $Documentation);
            for ($p = 1; $p <= $n_PageCount; $p++) {
                $tmp_Page = $pdf_union->ImportPage($p);
                $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                $pdf_union->useTemplate($tmp_Page);
            }
        }
    }
    
    if ($ultimate){
        $UnionDocumentation = $_SESSION['cityid'] . "_" . date("Y-m-d_H-i-s") .'.'. PDFA;
        if (!is_dir(FOREIGN_PEC . "/" . $_SESSION['cityid'] . '/create')) {
            mkdir(FOREIGN_PEC . "/" . $_SESSION['cityid'] . '/create', 0777);
        }
        if ($r_Customer['EnableINIPECDigitalSignature'] == 1)
            pdfDigitalSign($pdf_union,$_POST['SignaturePwd'],'Verbale PEC'); 
        $pdf_union->Output(FOREIGN_PEC . "/" . $_SESSION['cityid'] . '/create/' . $UnionDocumentation, "F");
            $_SESSION['Message'] = "Stampa definitiva avvenuta con successo!";
    } else {
        $UnionDocumentation = 'export_createdynamic_pec_foreign.'.PDFA;
        $pdf_union->Output(FOREIGN_PEC . "/" . $_SESSION['cityid'] . '/' . $UnionDocumentation, "F");
        $_SESSION['Documentation'] = $MainPath . '/doc/foreign/pec/' . $_SESSION['cityid'] . '/' . $UnionDocumentation;
    }
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='createdynamic_pec_foreign'");    
    $rs->End_Transaction();
}
