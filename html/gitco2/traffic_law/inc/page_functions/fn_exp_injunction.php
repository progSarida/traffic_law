<?php
use function Complex\ln;

require_once(PGFN."/fn_prn_anag_anomalies.php");

define('EXP_INJUNCTION_FILEID_SEPARATOR', '--');

define("INDEX_PAYMENT_ANY", 1);
define("INDEX_PAYMENT_OMITTED", 2);
define("INDEX_PAYMENT_DELAYED", 3);
define("INDEX_PAYMENT_PARTIAL", 4);

define('EXP_INJUNCTION_NATIONALITY', serialize(array('N' => 'Italiana', 'F' => 'Estera')));
define('EXP_INJUNCTION_PAYMENT_OPTIONS', serialize(array(
    INDEX_PAYMENT_OMITTED => 'Omessi',
    INDEX_PAYMENT_DELAYED => 'Tardivi',
    INDEX_PAYMENT_PARTIAL => 'Parziali',
    INDEX_PAYMENT_ANY => 'Tutte le tipologie'
)));
define('EXP_INJUNCTION_VALIDATEDADDRESS_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_ORDER_OPTIONS', serialize(array(
    1 => array('Name' => 'Cronologico', 'Order' => 'ProtocolId'),
    2 => array('Name' => 'Data verbale', 'Order' => 'FineDate'),
    3 => array('Name' => 'Data notifica', 'Order' => 'NotificationDate'),
    4 => array('Name' => 'Nome Trasgressore', 'Order' => 'Surname, CompanyName, Name'),
)));
define('EXP_INJUNCTION_DISPUTE_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_DISPUTEPREFECT_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_FOREIGN_NOTIFICATION_OPTIONS', serialize(array(
    0 => 'Solo not.',
    1 => 'Solo non not.',
    2 => 'Entrambi',
)));
define('EXP_INJUNCTION_EXPIRED_INSTALMENTS_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_ANOMALIES_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_PAYEDREMINDER_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_HASCADDOCUMENT_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_HASNOTDOCUMENT_OPTIONS', serialize(array(
    0 => 'Escludi',
    1 => 'Includi',
    2 => 'Solo loro',
)));
define('EXP_INJUNCTION_PRINT_TRESPASSER_TYPES', serialize(array(
    1 => "TRASG.",
    2 => "OBBLIG.",
    3 => "TRASG.",
    10 => "OBBLIG.",
    11 => "TRASG.",
    12 => "TRASG.",
    15 => "ESER.",
    16 => "ESER.",
)));

function expInjunctionWhere() {
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $s_TypePlate;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_ValidatedAddress;
    global $Search_HasDispute;
    global $Search_NotificationStatus;
    global $Search_HasCAD;
    global $Search_HasNotification;
    
    $str_Where = "1=1 AND CityId='{$_SESSION['cityid']}'";
    
    switch($s_TypePlate){
        case 'F' : $str_Where .= " AND TrespasserCountryId != 'Z000'"; break;
        case 'N' :
        default : $str_Where .= " AND TrespasserCountryId = 'Z000'";
    }
    if ($Search_FromProtocolYear != ''){
        $str_Where .= " AND ProtocolYear >= $Search_FromProtocolYear";
    }
    if ($Search_ToProtocolYear != ''){
        $str_Where .= " AND ProtocolYear <= $Search_ToProtocolYear";
    }
    if ($Search_FromFineDate != "") {
        $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
    }
    if ($Search_FromNotificationDate != "") {
        $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
    }
    if ($Search_ToNotificationDate != "") {
        $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";
    }
    if ($Search_FromProtocolId != "") {
        $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != "") {
        $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
    }
    switch($Search_HasNotification){
        case 0 : $str_Where .= " AND !((FN.ResultId <= 9 OR FN.ResultId IN(21,22)) AND DocumentationIdNot IS NULL)"; break;
        case 1 : break;
        case 2 : $str_Where .= " AND ((FN.ResultId <= 9 OR FN.ResultId IN(21,22)) AND DocumentationIdNot IS NULL)"; break;
    }
    if($s_TypePlate == 'N'){
        switch($Search_HasCAD){
            case 0 : $str_Where .= " AND !(FN.ResultId IN(21,2) AND DocumentationIdCAD IS NULL)"; break;
            case 1 : break;
            case 2 : $str_Where .= " AND (FN.ResultId IN(21,2) AND DocumentationIdCAD IS NULL)"; break;
        }
    }
    if($s_TypePlate == 'N'){ //Il controllo sui CAD lo si fa solo per i verbali nazionali
        switch($Search_ValidatedAddress){
            case 0 : $str_Where .= " 
                AND ( 
                    (FN.ResultId > 2 AND FN.ResultId <= 9) 
                    OR (FN.ResultId IN (21,2) AND (FN.ValidatedAddress = 1 OR (FN.ValidatedAddress = 0 AND FP.Id IS NOT NULL))) 
                    OR FN.ResultId IN (1,22) 
                )"; break;
            case 1 : $str_Where .= " AND ( FN.ResultId <= 9 OR FN.ResultId IN(21,22) )"; break;
            case 2 : $str_Where .= " AND ( FN.ResultId IN(21,2) AND FN.ValidatedAddress = 0 AND FP.Id IS NULL)"; break;
        }
    }
    if ($s_TypePlate != 'N') {
        switch($Search_NotificationStatus){
            case 0 : $str_Where .= " AND FN.NotificationDate IS NOT NULL"; break; //Solo notificati
            case 1 : $str_Where .= " AND FN.NotificationDate IS NULL"; break; //Solo non notificati
            case 2 : $str_Where .= ""; break; //Entrambi
        }
    }
//     switch($Search_HasDispute){
//         case 0: $str_Where .= " AND DisputeId IS NULL"; break; //Escludi
//         case 2: $str_Where .= " AND DisputeId IS NOT NULL AND DisputeId IN(SELECT Id FROM Dispute WHERE GradeTypeId = 3)"; break; //Solo loro
//     }
    switch($Search_HasDispute){
        case 0: $str_Where .= " AND (DisputeId IS NULL OR NOT(GradeTypeId=3 AND DisputeStatusId=2))"; break; //Escludi
        case 1 : $str_Where .= ""; break; //Includi
        case 2: $str_Where .= " AND (DisputeId IS NOT NULL AND (GradeTypeId=3 AND DisputeStatusId=2)"; break; //Solo loro
    }
    
    return $str_Where;
}

function expInjunctionOrderBy() {
    global $Order_Type;
    
    $a_OrderOptions = unserialize(EXP_INJUNCTION_ORDER_OPTIONS);
    
    return $a_OrderOptions[$Order_Type]['Order'] ?? null;
}

//************************INIZIO PARTE PDF***************************
require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");
require_once(TCPDF . "/fpdi.php");

function injunctionPDFPrint($rs, $idstring, $a_Injunction, $a_cls_pagamenti, $dirPath, $webPath, $str_ProcessingTable, $s_TypePlate, $minExpiredRates, $amountLimit){
    //Davide: NOTA BENE: se mai si dovessero fare modifiche ai nomi dei file, fare attenzione a questo pezzo di stringa e come viene usato, perchè su di essa si basa della logica di salvataggi e letture per le cose del ruolo.
    $FileName = $_SESSION['cityid']."_".$_SESSION['year']."_". date("Y-m-d_H-i").EXP_INJUNCTION_FILEID_SEPARATOR.$idstring;
    
    $filePath = $dirPath . "/" . $FileName.".pdf";
    $webFilePath = $webPath . "/" . $FileName.".pdf";
    
    $a_PrintTrespasserTypes = unserialize(EXP_INJUNCTION_PRINT_TRESPASSER_TYPES);
    
    //PARAMETRI PDF/////////////////////////////////////
    $pdf = new PDF_HANDLE('L','mm','A4', true,'UTF-8',false,true);
    $pdf->TemporaryPrint= 0;
    $pdf->NationalFine= 1;
    $pdf->CustomerFooter = 0;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Elaborazione ruoli tracciato 290');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');
    $pdf->SetMargins(10,10,10);
    $pdf->setCellHeightRatio(1.5);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    
    paginaPresentazione($pdf, $minExpiredRates);
    
    //Aggiungo una pagina vuota che accoglierà il testo del frontespizio alla fine quando si avranno tutti i dati necessari
    $pdf->AddPage('L');
    
    intestazioneNuovaPagina($pdf); //Intestazione
    
    $n_TotalRows = 0;
    $row_Counter = 1;
    $record_Counter = 1;
    $lineHeight = 25;
    
    //Totali di pagina
    $PageTotalFee = 0;
    $PageTotalNotification = 0;
    $PageTotalPercentualFee = 0;
    
    //Totali generali
    $GrandTotalFee = 0;
    $GrandTotalNotification = 0;
    $GrandTotalPercentualFee = 0;
    
    //LETTURA PARAMETRI DELL'ENTE
    $a_Customer = $rs->getArrayLine($rs->SelectQuery("SELECT * FROM Customer WHERE CityId='" . $_SESSION['cityid'] . "'"));
    if($a_Customer==null || empty($a_Customer['PatronalFeast']) ){
        $_SESSION['Message']['Error'] = "Non è possibile procedere con l'elaborazione se non è stata indicata la data della festa patronale tra le configurazioni dell'ente competente.<br >Compilare il campo Festa patronale nella scheda Indirizzo del menù Ente\Gestione Ente.";
        header("location: ".impostaParametriUrl(array('Filter' => 1), 'exp_injunction.php'.$str_GET_Parameter));
        die;
    }
    
    foreach ($a_Injunction as $r_Injunction) {
        $b_TotalPrinted = false;
        /** @var cls_pagamenti $cls_pagamenti */
        $cls_pagamenti = $a_cls_pagamenti[$r_Injunction['FineId']];
        //Il contatore dei record. E' qui perchè così conta anche quelli che vengono saltati
        $record_Counter++;
        
        //Imposta il vero genere del trasgressore
        $actualGenre = checkActualGenre($r_Injunction['Genre'], $r_Injunction['TaxCode'], $r_Injunction['VatCode']);
        
        //Controlla se ci sono i presupposti per elaborare il 290 del verbale in questione, altrimenti salta
        $skipElaboration = verificaSaltaElaborazione($rs, $r_Injunction, $minExpiredRates, $amountLimit, $str_ProcessingTable, $cls_pagamenti);
        if($skipElaboration) continue;
            
        //In caso non sia presente il codice catastale
        if(strlen(trim($r_Injunction['TrespasserCityId']))!=4){
            //In caso sia presente almeno il nome della città
            //NB Se il nome della città non fosse presente verrebbe dato errore di anagrafica e saltata la sua elaborazione
            if(isset($r_Injunction['City'])){
                $rs_City = $rs->Select(MAIN_DB.".City","Title = '".addslashes($r_Injunction['City'])."'");
                if(mysqli_num_rows($rs_City) > 0)
                    $r_Injunction['TrespasserCityId'] = mysqli_fetch_array($rs_City)['Id'];
                    //In caso il nome della città fosse sbagliato si farebbe una ricerca per CAP prendendo il primo risultato
                    //NB Un CAP può essere presente in più città differenti e quindi dare molteplici risultati. Sarebbe meglio evitare di ricercare per solo CAP
                    else{
                        $rs_City = $rs->Select(MAIN_DB.".City","ZIP='".trim($r_Injunction['ZIP'])."'");
                        if(mysqli_num_rows($rs_City)==0){
                            $rs_City = $rs->Select(MAIN_DB.".ZIPCity","ZIP='".trim($r_Injunction['ZIP'])."'");
                            if(mysqli_num_rows($rs_City)==0) $r_Injunction['TrespasserCityId'] = "";
                            else $r_Injunction['TrespasserCityId'] = mysqli_fetch_array($rs_City)['CityId'];
                        }else{
                            $r_Injunction['TrespasserCityId'] = mysqli_fetch_array($rs_City)['Id'];
                        }
                    }
            }
        }
        
        //TODO vecchio codice prima dell'uso di cls_pagamenti, rimuovere se non ci sono retrogressioni
        //***********IMPORTI**********
//         $paymentQuery  =    "SELECT FineId, SUM(Amount) Amount, SUM(Fee) Fee, SUM(ResearchFee) ResearchFee, SUM(NotificationFee) NotificationFee,
//                             SUM(CanFee) CanFee, SUM(CadFee) CadFee, SUM(CustomerFee) CustomerFee, SUM(OfficeNotificationFee) OfficeNotificationFee
//                             FROM FinePayment WHERE FineId=".$r_Injunction['FineId']." GROUP BY FineId";
//         $r_Payment = $rs->getArrayLine($rs->SelectQuery($paymentQuery));
        
//         //Recupera la conta dei solleciti
//         $reminderQuery =    "SELECT FineId, COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee
//                             FROM FineReminder WHERE FineId=".$r_Injunction['FineId']." AND FlowDate IS NOT NULL GROUP BY FineId";
//         $r_FineReminder = $rs->getArrayLine($rs->SelectQuery($reminderQuery));
//         //Recupera solo l'ultimo sollecito
//         $lastReminderQuery = "SELECT * FROM FineReminder WHERE FineId = ".$r_Injunction['FineId']." ORDER BY Id DESC LIMIT 1";
//         $r_LastFineReminder = $rs->getArrayLine($rs->SelectQuery($lastReminderQuery));
        
//         $Fee = $r_Injunction['Fee'] ?? 0;
//         $HalfMaxFee = ($r_Injunction['MaxFee'] ?? 0) * FINE_MAX;
//         $HalfMaxFee = number_format($HalfMaxFee,2);
//         $HalfMaxFee = str_replace(",","", $HalfMaxFee);
//         $MaxFee = $HalfMaxFee - $Fee;
        
        $a_amounts = array(
            'Fee'=>             $cls_pagamenti->getFineMaxFee(),
            'ResearchFee'=>     $cls_pagamenti->getFineResearchFee() ?? 0,
            'Payment'=>         $cls_pagamenti->getPayed() ?? 0,
            'Notification'=>    ($cls_pagamenti->getFineNotificationFee() ?? 0) + ($cls_pagamenti->getPreviousReminderNotificationFeesSum() ?? 0),
            'PercentualAmount'=> $cls_pagamenti->getSurcharge() ?? 0
        );
        
        $cod5242 = 0;
        $cod5354 = 0;
        $cod5243 = 0;
        
        //Totale del pagato da cui sottrarre le varie voci man mano che le si analizza nel prossimo blocco di if
        $TotalPayed = $a_amounts['Payment'];
        
        //Scorporo pagamenti sulle varie voci
        if($a_amounts['Payment'] > 0){
            if(($a_amounts['Fee']-$TotalPayed)<=0){  //Pagamento > Fee
                $cod5242 = 0;
                $TotalPayed -= $a_amounts['Fee'];
                
                if((($a_amounts['ResearchFee']+$a_amounts['Notification'])-$TotalPayed)<0){ //Pagamento > Fee + Spese
                    $cod5354 = 0;
                    $TotalPayed -= ($a_amounts['ResearchFee']+$a_amounts['Notification']);
                    
                        if(($a_amounts['PercentualAmount'] - $TotalPayed) < 0){ //Pagamento > Fee + Spese + Maggiorazione
                            $cod5243 = 0;
                            $TotalPayed -= $a_amounts['PercentualAmount'];
                        } else {
                            $cod5243 = ($a_amounts['PercentualAmount'] - $TotalPayed);
                            $TotalPayed = 0;
                            //continue; //Salta l'elaborazione se risulta pagato più del dovuto???
                        }
                } else {
                    $cod5354 = (($a_amounts['ResearchFee']+$a_amounts['Notification'])-$TotalPayed);
                    $cod5243 = $a_amounts['PercentualAmount'];
                    $TotalPayed = 0;
                }
            } else {
                $cod5242 = ($a_amounts['Fee']-$TotalPayed);
                $cod5354 = $a_amounts['ResearchFee']+$a_amounts['Notification'];
                $cod5243 = $a_amounts['PercentualAmount'];
                $TotalPayed = 0;
            }
        }
        else{
            $cod5242 = $a_amounts['Fee'];
            $cod5354 = $a_amounts['ResearchFee']+$a_amounts['Notification'];
            $cod5243 = $a_amounts['PercentualAmount'];
        }
        //Incremento i totali di pagina
        $PageTotalFee += $cod5242;
        $PageTotalNotification += $cod5354;
        $PageTotalPercentualFee += $cod5243;
        //Incremento i totali generali
        $GrandTotalFee += $cod5242;
        $GrandTotalNotification += $cod5354;
        $GrandTotalPercentualFee += $cod5243;
        
        //********************************
        
        $pdf->SetFont('helvetica','',9);
        
        $fineDate = DateOutDB($r_Injunction['FineDate']);
        $notificationDate = !empty($r_Injunction['NotificationDate']) ? DateOutDB($r_Injunction['NotificationDate']) : "";
        $bornDate = DateOutDB($r_Injunction['BornDate']);
        $trespasserName = "({$r_Injunction['TrespasserCode']}) ".trim($actualGenre != 'D' ? ($r_Injunction['Name'].' '.$r_Injunction['Surname']) : $r_Injunction['CompanyName']);
        $legalform = $actualGenre == 'D' && !empty($r_Injunction['LegalFormSign']) ? ' '.strtoupper($r_Injunction['LegalFormSign']) : '';
        $resSede = trim($r_Injunction['CompanyName'] ?? "" != "") ? "Sede:" : "Res.:";
        $TaxCode = trim($r_Injunction['TaxCode']);
        $VatCode = trim($r_Injunction['VatCode']);
        $TaxVatCode = ($actualGenre == 'D' ? 'P.IVA: ' : 'C.F.: ').(!empty($TaxCode) ? $TaxCode : (!empty($VatCode) ? $VatCode : ''));
        
        $pdf->SetFont('helvetica','',8);
        $pdf->writeHTMLCell(115, 5, 5, $lineHeight,  "<div>".$trespasserName." ".$legalform."</div>", 0,0,true);
        $pdf->SetFont('helvetica','',9);
        $pdf->writeHTMLCell(15, 5, 120, $lineHeight, "<div>".$r_Injunction['ProtocolYear']."</div>");
        $pdf->writeHTMLCell(15, 5, 135, $lineHeight, "<div>5242</div>");
        $pdf->writeHTMLCell(120, 5,150, $lineHeight, "<div>Sanz. amministrativa per violazione al C.D.S. Cron. ".$r_Injunction['ProtocolId']."/".$r_Injunction['ProtocolYear']." del ".$fineDate."</div>");
        $pdf->writeHTMLCell(20, 5, 270, $lineHeight, "<div>".number_format($cod5242,2,',','.')."</div>",0,0,false,true,'R');
        $lineHeight+=5;
        $pdf->writeHTMLCell(115, 5, 5, $lineHeight, "<div>".$TaxVatCode."</div>");
        $pdf->writeHTMLCell(15, 5, 120, $lineHeight, "<div></div>");
        $pdf->writeHTMLCell(15, 5, 135, $lineHeight, "<div></div>");
        $pdf->writeHTMLCell(120, 5,150, $lineHeight, "<div>Notificato in data ".$notificationDate." Rif. ".$r_Injunction['Code']."</div>");
        $pdf->writeHTMLCell(20, 5, 270, $lineHeight, "<div></div>");
        $lineHeight+=5;
        $pdf->writeHTMLCell(115, 5, 5, $lineHeight, "<div>".$resSede." ".$r_Injunction['Address']."</div>");
        $pdf->writeHTMLCell(15, 5, 120, $lineHeight, "<div>".$r_Injunction['ProtocolYear']."</div>");
        $pdf->writeHTMLCell(15, 5, 135, $lineHeight, "<div>5354</div>");
        $pdf->writeHTMLCell(120, 5,150, $lineHeight, "<div>Spese postali/notifica/ricerca (già sostenute)</div>");
        $pdf->writeHTMLCell(20, 5, 270, $lineHeight, "<div>".number_format($cod5354,2,',','.')."</div>",0,0,false,true,'R');
        $lineHeight+=5;
        $pdf->writeHTMLCell(115, 5, 5, $lineHeight, "<div>".$r_Injunction['ZIP']." ".$r_Injunction['City']." (".$r_Injunction['Province'].")</div>");
        $pdf->writeHTMLCell(15, 5, 120, $lineHeight, "<div>".$r_Injunction['ProtocolYear']."</div>");
        $pdf->writeHTMLCell(15, 5, 135, $lineHeight, "<div>5243</div>");
        $pdf->writeHTMLCell(120, 5,150, $lineHeight, "<div>Maggiorazione del 10% semestrale (ART. 27 L.689/81)</div>");
        $pdf->writeHTMLCell(20, 5, 270, $lineHeight, "<div>".number_format($cod5243,2,',','.')."</div>",0,0,false,true,'R');
        $lineHeight+=5;
        if(!empty($r_Injunction['BornPlace'])){
            $pdf->writeHTMLCell(115, 5, 5, $lineHeight, "<div>Nato/a a ".$r_Injunction['BornPlace']." il ".$bornDate."</div>");
        }
        $pdf->writeHTMLCell(15, 5, 120, $lineHeight, "<div></div>");
        $pdf->writeHTMLCell(15, 5, 135, $lineHeight, "<div></div>");
        $pdf->writeHTMLCell(120, 5,150, $lineHeight, "<div></div>");
        $pdf->writeHTMLCell(20, 5, 270, $lineHeight, "<div></div>");
        $lineHeight+=5;
        $pdf->writeHTMLCell(115, 5, 5, $lineHeight, "<div>{$a_PrintTrespasserTypes[$r_Injunction['TrespasserTypeId']]}</div>");
        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(150, 150, 150)));
        $pdf->Line(268, $lineHeight, 288, $lineHeight, array("dash"));
        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
        $pdf->writeHTMLCell(120, 5, 150, $lineHeight, "<div><b>TOTALE</b></div>");
        $pdf->writeHTMLCell(20, 5, 270, $lineHeight, "<div><b>".number_format(($cod5242+$cod5243+$cod5354),2,',','.')."</b></div>",0,0,false,true,'R');
        $lineHeight+=5;
        $pdf->Line(5, $lineHeight, 288, $lineHeight);
        $row_Counter++;
        
        if($row_Counter % 6 == 0){
            totaliPagina($pdf,$PageTotalFee,$PageTotalNotification,$PageTotalPercentualFee, ($row_Counter-1), ($n_TotalRows+1));
            $row_Counter = 1;
            $lineHeight = 25;
            $b_TotalPrinted = true;
            if(count($a_Injunction)+1 > $record_Counter) intestazioneNuovaPagina($pdf);
        }
        
        $n_TotalRows++;
    }
    
    if(!$b_TotalPrinted){
        totaliPagina($pdf,$PageTotalFee,$PageTotalNotification,$PageTotalPercentualFee, ($row_Counter-1), $n_TotalRows);
    }
    pagineTotaliFinali($pdf,$GrandTotalFee,$GrandTotalNotification,$GrandTotalPercentualFee, $a_Customer, $n_TotalRows);
    $n_TotalPages = $pdf->PageNo();
    
    frontespizioPDF($pdf, $n_TotalRows, $n_TotalPages, $GrandTotalFee, $GrandTotalNotification, $GrandTotalPercentualFee, $a_Customer);
    
    $pdf->Output($filePath, "F");
    
    return array("path" => $webFilePath, "fileName" => $FileName);
}

function intestazioneNuovaPagina(&$pdf){
    /** @var TCPDF $pdf */
    global $page_format;
    $pdf->AddPage('L',$page_format);
    $pdf->SetMargins(5,5,5);
    $pdf->setCellHeightRatio(1.5);
    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    $pdf->SetFont('helvetica', '', 14);
    $pdf->writeHTMLCell(0, 10, 5, 5, "<div><b>COMUNE DI ".strtoupper($_SESSION['citytitle'])."</b></div>"); //Titolo
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTMLCell(0, 5, 280, 5, "<div>".$pdf->PageNo()."</div>"); //Numero pagina
    $pdf->writeHTMLCell(0, 10, 5, 12, "<div>Estrazione ruolo del ".date('d/m/Y')."</div>"); //Sotto titolo
    $pdf->Line(5, 20, 288, 20);
    $pdf->writeHTMLCell(120, 10, 5, 20, "<div><b>Intestatario tabella</b></div>");
    $pdf->writeHTMLCell(15, 10, 120, 20, "<div><b>Anno</b></div>");
    $pdf->writeHTMLCell(15, 10, 135, 20, "<div><b>Cod.</b></div>");
    $pdf->writeHTMLCell(120, 10, 150, 20, "<div><b>Informazioni cartella</b></div>");
    $pdf->writeHTMLCell(30, 10, 270, 20, "<div><b>Dettaglio</b></div>");
    $pdf->Line(5, 25, 288, 25);
}

//Metodo che dice se saltare o meno l'elaborazione del ruolo per il verbale in questione ed incrementa il contatore delle rate scadute
function verificaSaltaElaborazione($rs, $r_Injunction, $minExpiredRates, $amountLimit, $str_ProcessingTable, $cls_pagamenti){
    /** @var cls_pagamenti $cls_pagamenti */
    global $Search_Payed;
    global $Search_PaymentType;
    
    //In base al filtro "Pagamenti" selezionato e allo stato del pagamento, salta la riga
    $b_skipPayment = false;
    switch($Search_PaymentType){
        case INDEX_PAYMENT_OMITTED : if($cls_pagamenti->getStatus() != 0) $b_skipPayment = true; break;
        case INDEX_PAYMENT_PARTIAL : if($cls_pagamenti->getStatus() != 1) $b_skipPayment = true; break;
        case INDEX_PAYMENT_DELAYED : if($cls_pagamenti->getStatus() != 2) $b_skipPayment = true; break;
    }
    if($b_skipPayment) return true;
    
    //Se il rimanente da pagare è inferiore a "Importo minimo per iscrizione a ruolo" salta l'elaborazione
    if(($cls_pagamenti->getFee() - $cls_pagamenti->getPayed()) < $amountLimit){
        return true;
    }
    
    //Determina in presenza di un sollecito se l'importo pagato è uguale o supera sanzione+spese notifica
    $b_PayedReminder = $cls_pagamenti->hasReminder() && ($cls_pagamenti->getPayed() >= ($cls_pagamenti->getLastReminderTotalAmount() - $cls_pagamenti->getLastReminderSurcharge()));
    
    //"Posizioni che hanno pagato l'importo del sollecito"
    if(($Search_Payed == 0 && $b_PayedReminder) || ($Search_Payed == 2 && !$b_PayedReminder)){
        return true;
    }
    
    //SE PRESENTE RICORSO SALTO LA POSIZIONE
    //TODO GESTIONE RICORSI CON VERBALI MULTIPLI DA AGGREGARE NELL'ESTRAZIONE
    if($r_Injunction['DisputeId']>0){
        //Parte stato ricorsi
        $disputeView = new CLS_VIEW(MGMT_DISPUTE);
        $rs_FineDispute= $rs->selectQuery($disputeView->generateSelect("F.Id=".$r_Injunction['FineId'],null, "GradeTypeId DESC",1));
        $r_FineDispute = $rs->getArrayLine($rs_FineDispute);
        
        if($r_FineDispute['GradeTypeId'] == 3) //L'elaborazione condizionata al ricorso si applica solo dal 3° grado di giudizio
        {
            $cls_dispute = new cls_dispute();
            $cls_dispute->setDispute($r_FineDispute,1);
            $disputeStatus = $cls_dispute->a_info['responseCode'];
            
            if(($disputeStatus >= 1 && $disputeStatus <= 4) || ($disputeStatus == 6)) //Ricorso in attesa, rinviato, sospeso o accolto
            {
                return true;   //Salta l'elaborazione
            }
        }
    }
    
    //VERIFICA RATEIZZAZIONI
    // fase 1 saltiamo quelli che hanno rateizzazioni aperte
    $n_ExpiredRates = 0;
    $r_PaymentRate = $rs->getArrayLine($rs->Select('PaymentRate', "FineId={$r_Injunction['FineId']} AND StatusRateId=0"));
    if($r_PaymentRate){
        // fase 2 se esiste rateizzazione per ogni rata si cerca se c'è un pagamento per quel fineId con quel numero di rata o con il quinto campo del numero di rata se ce l'ha
        // e se ci sono meno di due rate scadute si salta
        $rs_PaymentRateNumber = $rs->Select('PaymentRateNumber', "PaymentRateId={$r_PaymentRate['Id']}");
        while($r_PaymentRateNumber = $rs->getArrayLine($rs_PaymentRateNumber)){
            $r_Payment = $rs->getArrayLine($rs->Select('FinePayment', "FineId={$r_Injunction['FineId']} AND PaymentFee={$r_PaymentRateNumber['RateNumber']}"));
            //Se la data di scadenza è minore della data di pagamento (data attuale se non c'è un pagamento per quella rata)
            if($r_PaymentRateNumber['PaymentDate'] < ($r_Payment['PaymentDate'] ?? date('Y-m-d'))){
                $n_ExpiredRates++;
            }
        }
        
        if($n_ExpiredRates < $minExpiredRates)
            return true;
    }
    
    //VERIFICA FLAG
    //L'estero è in LEFT JOIN con FineNotification e quindi potrebbe essere NULL
    if($r_Injunction['InjunctionProcedure'] != NULL && $r_Injunction['InjunctionProcedure'] < 1){
        return true;
    }
    
    //VERIFICA ANAGRAFICA
    // qui si applicano le funzioni di Dario ai dati del trasgressore se è tirato su dalla vista con TrespasserId
    $a_TrespasserAnomalies = manageAnomalies($r_Injunction, $r_Injunction['TrespasserCountryId'] == 'Z000' ? 'N' : 'F');
    if(checkAnomalyExistence($a_TrespasserAnomalies, true)){
        return true;
    }
    
    //VALUTAZIONE PRESCRIZIONE
    //L'estero può essere selezionato anche senza notifica
    if($r_Injunction['NotificationDate'] != NULL){
    //Se la data di notifica avanti di 5 anni (5 anni + 270 gg per esterno) + i giorni di ricorso
    // + shitp per festività è < $ProcessingDate siamo in prescrizione
    $PrescriptionDateOriginale = date('Y-m-d', strtotime($r_Injunction['NotificationDate']. ' + '. PRESCRIPTION_YEARS));
    if(!$str_ProcessingTable=="National")  // per l'estero la prescrizione va valutata su 5 anni + 270 gg
        $PrescriptionDateOriginale = date('Y-m-d', strtotime($PrescriptionDateOriginale. ' + '. PRESCRIPTION_FOREIGN_DAYS));
    
        //valutazione prescrizione covid
        $PrescriptionDateTemp = AggiornaPrescizionePerSospensioneCovid($PrescriptionDateOriginale, $r_Injunction['NotificationDate'], DeterminaDataNotificaMinima(D_COVID_I, $str_ProcessingTable), D_COVID_F);
        
        $PrescriptionDateTemp = date('Y-m-d', strtotime($PrescriptionDateTemp));
        $PrescriptionDate = SkipFestiveDays($PrescriptionDateTemp); //passata alla funzione che sposta al giorno successivo se c'è festività
        //SE PRESCRITTO NON ESPORTO
        if (date('Y-m-d') > $PrescriptionDate){
            return true;
        }
    }
    
    //Controlla lo stato dei pagamenti associati al verbale
    //Se lo status è tra 3 (pagato pari) e 4 (pagato in eccesso) salta l'elaborazione
    if(($cls_pagamenti->getStatus()) == 3 || ($cls_pagamenti->getStatus()) == 4){
        return true;
        }
        
    return false;
}

function paginaPresentazione(&$pdf, $minExpiredRates){
    global $page_format;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_ToFineDate;
    global $s_TypePlate;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_HasDispute;
    global $Search_HasDisputePrefect;
    global $Search_Expired;
    global $Search_Anomalies;
    global $Search_Payed;
    global $Search_PaymentType;
    global $Search_ValidatedAddress;
    global $Search_HasCAD;
    global $Search_HasNotification;
    global $Order_Type;
    
    $a_ExpiredInstalmentsOptions = unserialize(EXP_INJUNCTION_EXPIRED_INSTALMENTS_OPTIONS);
    $a_DisputePrefectOptions = unserialize(EXP_INJUNCTION_DISPUTEPREFECT_OPTIONS);
    $a_AnomaliesOptions = unserialize(EXP_INJUNCTION_ANOMALIES_OPTIONS);
    $a_PayedReminderOptions = unserialize(EXP_INJUNCTION_PAYEDREMINDER_OPTIONS);
    $a_PaymentOptions = unserialize(EXP_INJUNCTION_PAYMENT_OPTIONS);
    $a_ValidatedAddressOptions = unserialize(EXP_INJUNCTION_VALIDATEDADDRESS_OPTIONS);
    $a_OrderOptions = unserialize(EXP_INJUNCTION_ORDER_OPTIONS);
    $a_DisputeOptions = unserialize(EXP_INJUNCTION_DISPUTE_OPTIONS);
    $a_CADDocumentOptions = unserialize(EXP_INJUNCTION_HASCADDOCUMENT_OPTIONS);
    $a_NotDocumentOptions = unserialize(EXP_INJUNCTION_HASNOTDOCUMENT_OPTIONS);
    
    //Inizializazione pdf
    $html = '<h3 style="text-align: center;"><strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'<br />
        ELABORAZIONE RUOLI</strong></h3>
        
        <p style="text-align: center;">Stampato il '.date('d/m/Y').'</p>
        <br />
        <h3><strong>OPZIONI SELEZIONATE NEL MOMENTO DELLA STAMPA&nbsp;</strong></h3>
        <br />';
    
    //Prima pagina//////////////////////////////////////////////////////////////////////////////
    $pdf->AddPage('L', $page_format);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTML($html, true, false, true, false, '');
    
    //Prima riga
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(35, 0, 'ANNO', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'CRON', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(50, 0, 'DATA ACCERTAMENTO', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, ' DATA NOTIFICA', 1, '', 0, 1, '', '', true);
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(35/2, 0, 'DA', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, 'A', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, 'DA', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, 'A', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(50/2, 0, 'DA', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(50/2, 0, 'A', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, 'DA', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, 'A', 1, '', 0, 1, '', '', true);
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(35/2, 0, $Search_FromProtocolYear, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, $Search_ToProtocolYear, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, $Search_FromProtocolId, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, $Search_ToProtocolId, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(50/2, 0, $Search_FromFineDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(50/2, 0, $Search_ToFineDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, $Search_FromNotificationDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35/2, 0, $Search_ToNotificationDate, 1, '', 0, 1, '', '', true);
    
    $pdf->LN(5);
    //Seconda riga
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(50, 0, 'NAZIONALITA\' TRASGRESSORE', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(150, 0, 'POS. CON RICORSO AL GIUDICE DI PACE ED EVENTUALI GRADI SUCC. CHIUSO VINTO DAL COMUNE', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(80, 0, "POSIZIONI CON ALMENO N. $minExpiredRates RATE SCADUTE", 1, '', 0, 1, '', '', true);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(50, 0, ($s_TypePlate == 'N' ? 'NAZIONALE' : 'ESTERO'), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(150, 0, strtoupper($a_DisputeOptions[$Search_HasDispute]) ?? '', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(80, 0, strtoupper($a_ExpiredInstalmentsOptions[$Search_Expired] ?? ''), 1, '', 0, 1, '', '', true);
    $pdf->LN(5);
    //Terza riga
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(70, 0, 'PAGAMENTI', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(70, 0, 'ORDINA PER', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(140, 0, 'POSIZIONI NOTIFICATE VIA CAD SENZA PAGAMENTO E PRIVE DI VALIDAZ. DELL\'INDIRIZZO', 1, '', 0, 1, '', '', true);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(70, 0, strtoupper($a_PaymentOptions[$Search_PaymentType] ?? ''), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(70, 0, strtoupper($a_OrderOptions[$Order_Type]['Name'] ?? ''), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(140, 0, strtoupper($a_ValidatedAddressOptions[$Search_ValidatedAddress] ?? ''), 1, '', 0, 1, '', '', true);
    $pdf->LN(5);
    //Quarta riga
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(130, 0, 'POS. CON RICORSO AL PREFETTO CHIUSO VINTO DAL COMUNE E NON APPELLATO', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(70, 0, 'POS. CON ANOMALIE ANAGRAFICA', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(80, 0, 'POS. CHE HANNO PAGATO L\'IMPORTO DEL SOLLECITO', 1, '', 0, 1, '', '', true);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(130, 0, strtoupper($a_DisputePrefectOptions[$Search_HasDisputePrefect] ?? ''), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(70, 0, strtoupper($a_AnomaliesOptions[$Search_Anomalies] ?? ''), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(80, 0, strtoupper($a_PayedReminderOptions[$Search_Payed] ?? ''), 1, '', 0, 1, '', '', true);
    $pdf->LN(5);
    //Quinta
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(130, 0, 'POS. NOTIFICATE CON CAD E IMMAGINE MANCANTE', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(150, 0, 'POS. NOTIFICATE TRAMITE RACCOMANDATA SENZA IMMAGINE DELLA NOTIFICA', 1, '', 0, 1, '', '', true);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(130, 0, strtoupper($a_CADDocumentOptions[$Search_HasCAD] ?? ''), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(150, 0, strtoupper($a_NotDocumentOptions[$Search_HasNotification] ?? ''), 1, '', 0, 1, '', '', true);
    
    $pdf->SetFont('helvetica', '', 10);
    ////////////////////////////////////////////////////////////////////////////////
}

function totaliPagina($pdf, $totalfee, $totalnotification, $totalpercentualfee, $pageRows, $totalRows){
    $pdf->writeHTMLCell(270, 3, 5, 175, "<div><b>Totale di pagina per Sanz. amministrativa per violazione al C.D.S.</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 175, "<div><b>".number_format($totalfee,2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 179, "<div><b>Totale di pagina per Spese postali/notifica/ricerca (gia' sostenute)</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 179, "<div><b>".number_format($totalnotification,2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 183, "<div><b>Totale di pagina per Maggiorazione del 10% semestrale (ART. 27 L.689/81)</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 183, "<div><b>".number_format($totalpercentualfee,2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(150, 150, 150)));
    $pdf->Line(5, 189, 288, 189, array("dash"));
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    $pdf->writeHTMLCell(270, 3, 5, 189, "<div><b>TOTALE DI PAGINA</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 189, "<div><b>".number_format(($totalfee+$totalnotification+$totalpercentualfee),2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 193, "<div><b>N° POSIZIONI PAGINA</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 193, "<div><b>".$pageRows."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 197, "<div><b>N° POSIZIONI TOTALI</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 197, "<div><b>".$totalRows."</b></div>",0,0,false,true,'R');
}

function pagineTotaliFinali(&$pdf, $totalfee, $totalnotification, $totalpercentualfee, $a_Customer, $totalRows){
    /** @var TCPDF $pdf */
    global $page_format;
    //Totali numerici
    $pdf->AddPage('L',$page_format);
    $pdf->SetMargins(5,5,5);
    $pdf->setCellHeightRatio(1.5);
    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    $pdf->SetFont('helvetica', '', 14);
    $pdf->writeHTMLCell(0, 10, 5, 5, "<div><b>COMUNE DI ".strtoupper($_SESSION['citytitle'])."</b></div>"); //Titolo
    $pdf->Line(5, 15, 288, 15);
    $pdf->writeHTMLCell(0, 5, '', 30, "<div><b>TOTALE</b></div>", 0, 0, false, true, 'C');
    $pdf->Line(5, 40, 288, 40);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTMLCell(270, 3, 5, 45, "<div><b>Totale Sanz. amministrativa per violazione al C.D.S.</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 45, "<div><b>".number_format($totalfee,2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 55, "<div><b>Totale Spese postali/notifica/ricerca (gia' sostenute)</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 55, "<div><b>".number_format($totalnotification,2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 65, "<div><b>Totale Maggiorazione del 10% semestrale (ART. 27 L.689/81)</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 65, "<div><b>".number_format($totalpercentualfee,2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(150, 150, 150)));
    $pdf->Line(5, 75, 288, 75, array("dash"));
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    $pdf->writeHTMLCell(270, 3, 5, 80, "<div><b>TOTALE</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 80, "<div><b>".number_format(($totalfee+$totalnotification+$totalpercentualfee),2,',','.')."</b></div>",0,0,false,true,'R');
    $pdf->writeHTMLCell(270, 3, 5, 90, "<div><b>N° POSIZIONI TOTALI</b></div>");
    $pdf->writeHTMLCell(20, 3, 270, 90, "<div><b>".$totalRows."</b></div>",0,0,false,true,'R');
}

//Il frontespizio viene collocato in una pagina vuota precedentemente aggiunta perchè per la sua compilazione servono i dati degli importi che si hanno solo alla fine
function frontespizioPDF(&$pdf, $n_TotalRows, $n_TotalPages, $totalfee, $totalnotification, $totalpercentualfee, $a_Customer){
    /** @var TCPDF $pdf */
    //Totali testuali
    $pdf->SetPage(2);
    $pdf->SetMargins(5,5,5);
    $pdf->setCellHeightRatio(1.5);
    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    $pdf->SetFont('helvetica', '', 14);
    $pdf->writeHTMLCell(0, 10, 5, 5, "<div><b>FRONTESPIZIO DELLA MINUTA DI RUOLO</b></div>"); //Titolo
    $pdf->Line(5, 15, 288, 15);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTMLCell(0, 5, 5, 20, "<div>CODICE/DESCRIZIONE ENTE: ".strtoupper($_SESSION['citytitle'])." (".$a_Customer['ManagerProvince'].")</div>");
    $pdf->writeHTMLCell(0, 5, 5, 25, "<div>ELENCO DEI CODICI UTILIZZATI PER L'ISCRIZIONE A RUOLO</div>");
    $pdf->writeHTMLCell(0, 5, 5, 30, "<div>[5242] Sanzione amministrativa pecuniaria (comprensiva di eventuali aumenti)</div>");
    $pdf->writeHTMLCell(0, 5, 5, 35, "<div>[5354] Recupero spese postali/notifica/ricerca (gia' sostenute)</div>");
    $pdf->writeHTMLCell(0, 5, 5, 40, "<div>[5243] Maggiorazione del 10% semestrale (ART. 27 L.689/81)</div>");
    $pdf->writeHTMLCell(0, 5, 5, 45, "<div>ENTE IMPOSITORE: Comune di ".strtoupper($_SESSION['citytitle'])."</div>");
    $pdf->writeHTMLCell(0, 5, 5, 50, "<div>BENEFICIARIO: Comune di ".strtoupper($_SESSION['citytitle']).", ".$a_Customer['ManagerAddress'].", ".$a_Customer['ManagerZIP'].", ".$a_Customer['ManagerCity']." (".$a_Customer['ManagerProvince'].")</div>");
    $pdf->Line(5, 60, 288, 60);
    $pdf->writeHTMLCell(0, 5, 5, 65, "<div>INFORMAZIONI COMPLEMENTARI DA INSERIRE SU TUTTE LE CARTELLE</div>");
    $pdf->writeHTMLCell(0, 5, 5, 75, "<div>RESPONSABILE DEL PROCEDIMENTO .........................................................</div>");
    $pdf->Line(5, 85, 288, 85);
    $pdf->writeHTMLCell(0, 5, 5, 90, "<div>TIPO RISCOSSIONE: RUOLO TIPO RUOLO: COATTIVO</div>");
    $pdf->Line(5, 100, 288, 100);
    $pdf->writeHTMLCell(0, 5, 5, 105, "<div>DATA STAMPA: ".date("d/m/Y")." | NUMERO DI RATE: 1 = Unica | SCADENZA PRIMA RATA: a 30 giorni dalla ricezione </div>");
    $pdf->Line(5, 115, 288, 115);
    //$n_TotalPages -> Riccardo: nella pratica le pagine di cui si compone l'estrazione non sono <numeroPagine> meno la prima che riporta le selezioni adottate per effettuare l'estrazione
    $pdf->writeHTMLCell(0, 5, 5, 120, "<div>NUMERO TOTALE ARTICOLI: $n_TotalRows NUMERO TOTALE PAGINE: ".($n_TotalPages-1)." IMPORTO TOTALE DA RISCUOTERE: € ".number_format(($totalfee+$totalnotification+$totalpercentualfee),2,',','.')."</div>");
    $pdf->Line(5, 130, 288, 130);
    $pdf->writeHTMLCell(0, 5, 5, 135, "<div>ACRONIMI UTILIZZATI: OBBLIG. = Obbligato in solido TRASG. = Trasgressore ESER. = Esercente patria potestà</div>", 0, 1);
    $pdf->writeHTMLCell(100, 5, 5, 155, "<div>Data di inoltro al Concessionario ......................................</div>");
    $pdf->writeHTMLCell(100, 5, 105, 155, "<div>Timbro e firma .....................................................................</div>");
}

//**************************************PDF*****************************************

//**********************************Stampa Excel************************************
function injunctionXLSPrint($rs, $idstring, $a_Injunction, $a_cls_pagamenti, $dirPath, $webPath, $str_ProcessingTable, $s_TypePlate, $minExpiredRates, $amountLimit){
    $FileName = $_SESSION['cityid']."_".$_SESSION['year']."_". date("Y-m-d_H-i").EXP_INJUNCTION_FILEID_SEPARATOR.$idstring;
    $filePath = $dirPath . "/" . $FileName.".xls";
    $webFilePath = $webPath . "/" . $FileName.".xls";
    
    $a_PrintTrespasserTypes = unserialize(EXP_INJUNCTION_PRINT_TRESPASSER_TYPES);
    
    $xls = '';
    filtriXLS($xls, $minExpiredRates);
    
    //Intestazione
    $xls .= '<tr>
                <th>TRASGRESSORE/OBBLIGATO IN SOLIDO</th>
                <th>SOGGETTO</th>
                <th>C.F. o P.IVA</th>
                <th>RESIDENZA/SEDE</th>
                <th>COMUNE DI NASCITA</th>
                <th>TIPOLOGIA TRASGRESSORE</th>
                <th>CRON.</th>
                <th>REF.</th>
                <th>DATA VERBALE</th>
                <th>NOTIFICA</th>
                <th>SANZIONE</th>
                <th>SPESE</th>
                <th>MAGGIORAZIONE</th>
             </tr>';
    
    //Totali generali
    $GrandTotalFee = 0;
    $GrandTotalNotification = 0;
    $GrandTotalPercentualFee = 0;
    
    foreach ($a_Injunction as $r_Injunction) {
        /** @var cls_pagamenti $cls_pagamenti */
        $cls_pagamenti = $a_cls_pagamenti[$r_Injunction['FineId']];
        //Controlla se ci sono i presupposti per elaborare il 290 del verbale in questione, altrimenti salta
        $skipElaboration = verificaSaltaElaborazione($rs, $r_Injunction, $minExpiredRates, $amountLimit, $str_ProcessingTable, $cls_pagamenti);
        if($skipElaboration)
            continue;
            
        //In caso non sia presente il codice catastale
        if(strlen(trim($r_Injunction['TrespasserCityId']))!=4){
            //In caso sia presente almeno il nome della città
            //NB Se il nome della città non fosse presente verrebbe dato errore di anagrafica e saltata la sua elaborazione
            if(isset($r_Injunction['City'])){
                $rs_City = $rs->Select(MAIN_DB.".City","Title = '".addslashes($r_Injunction['City'])."'");
                if(mysqli_num_rows($rs_City) > 0)
                    $r_Injunction['TrespasserCityId'] = mysqli_fetch_array($rs_City)['Id'];
                    //In caso il nome della città fosse sbagliato si farebbe una ricerca per CAP prendendo il primo risultato
                    //NB Un CAP può essere presente in più città differenti e quindi dare molteplici risultati. Sarebbe meglio evitare di ricercare per solo CAP
                    else{
                        $rs_City = $rs->Select(MAIN_DB.".City","ZIP='".trim($r_Injunction['ZIP'])."'");
                        if(mysqli_num_rows($rs_City)==0){
                            $rs_City = $rs->Select(MAIN_DB.".ZIPCity","ZIP='".trim($r_Injunction['ZIP'])."'");
                            if(mysqli_num_rows($rs_City)==0) $r_Injunction['TrespasserCityId'] = "";
                            else $r_Injunction['TrespasserCityId'] = mysqli_fetch_array($rs_City)['CityId'];
                        }else{
                            $r_Injunction['TrespasserCityId'] = mysqli_fetch_array($rs_City)['Id'];
                        }
                    }
            }
        }
        
        $actualGenre = checkActualGenre($r_Injunction['Genre'], $r_Injunction['TaxCode'], $r_Injunction['VatCode']);
        $Name = "({$r_Injunction['TrespasserCode']}) ".trim($actualGenre != 'D' ? ($r_Injunction['Name'].' '.$r_Injunction['Surname']) : $r_Injunction['CompanyName']);
        $legalform = $actualGenre == 'D' && !empty($r_Injunction['LegalFormSign']) ? ' '.strtoupper($r_Injunction['LegalFormSign']) : '';
        $Person = $actualGenre != 'D' ? "Persona fisica" : "Ditta";
        $TaxCode = trim($r_Injunction['TaxCode']);
        $VatCode = trim($r_Injunction['VatCode']);
        $TaxVatCode = (!empty($TaxCode) ? $TaxCode : (!empty($VatCode) ? $VatCode : ''));
        $Residence = $r_Injunction['Address'].", ".$r_Injunction['ZIP'].", ".$r_Injunction['City']." (".$r_Injunction['Province'].")";
        $TrespasserType = $a_PrintTrespasserTypes[$r_Injunction['TrespasserTypeId']];
        $Born = $actualGenre != 'D' ? $r_Injunction['BornPlace']." il ".DateOutDB($r_Injunction['BornDate']) : "-";
        $Cron = $r_Injunction['ProtocolId']."/".$r_Injunction['ProtocolYear'];
        $Ref = $r_Injunction['Code'];
        $FineDate = DateOutDB($r_Injunction['FineDate']);
        $NotificationDate = !empty($r_Injunction['NotificationDate']) ? DateOutDB($r_Injunction['NotificationDate']) : "-";
        
        //TODO vecchio codice prima dell'uso di cls_pagamenti, rimuovere se non ci sono retrogressioni
//         //***********IMPORTI**********
//         $paymentQuery  =    "SELECT FineId, SUM(Amount) Amount, SUM(Fee) Fee, SUM(ResearchFee) ResearchFee, SUM(NotificationFee) NotificationFee,
//                             SUM(CanFee) CanFee, SUM(CadFee) CadFee, SUM(CustomerFee) CustomerFee, SUM(OfficeNotificationFee) OfficeNotificationFee
//                             FROM FinePayment WHERE FineId=".$r_Injunction['FineId']." GROUP BY FineId";
//         $r_Payment = $rs->getArrayLine($rs->SelectQuery($paymentQuery));
        
//         //Recupera la conta dei solleciti
//         $reminderQuery =    "SELECT FineId, COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee
//                             FROM FineReminder WHERE FineId=".$r_Injunction['FineId']." AND FlowDate IS NOT NULL GROUP BY FineId";
//         $r_FineReminder = $rs->getArrayLine($rs->SelectQuery($reminderQuery));
//         //Recupera solo l'ultimo sollecito
//         $lastReminderQuery = "SELECT * FROM FineReminder WHERE FineId = ".$r_Injunction['FineId']." ORDER BY Id DESC LIMIT 1";
//         $r_LastFineReminder = $rs->getArrayLine($rs->SelectQuery($lastReminderQuery));
        
//         $Fee = $r_Injunction['Fee'] ?? 0;
//         $HalfMaxFee = ($r_Injunction['MaxFee'] ?? 0) * FINE_MAX;
//         $HalfMaxFee = number_format($HalfMaxFee,2);
//         $HalfMaxFee = str_replace(",","", $HalfMaxFee);
//         $MaxFee = $HalfMaxFee - $Fee;
        
        $a_amounts = array(
            'Fee'=>             $cls_pagamenti->getFineMaxFee(),
            'ResearchFee'=>     $cls_pagamenti->getFineResearchFee() ?? 0,
            'Payment'=>         $cls_pagamenti->getPayed() ?? 0,
            'Notification'=>    ($cls_pagamenti->getFineNotificationFee() ?? 0) + ($cls_pagamenti->getPreviousReminderNotificationFeesSum() ?? 0),
            'PercentualAmount'=> $cls_pagamenti->getSurcharge() ?? 0
        );
        
        $Fee = 0;
        $TotalNotification = 0;
        $PercentualAmount = 0;
        
        //Totale del pagato da cui sottrarre le varie voci man mano che le si analizza nel prossimo blocco di if
        $TotalPayed = $a_amounts['Payment'];
        
        //Scorporo pagamenti sulle varie voci
        if($a_amounts['Payment'] > 0){
            if(($a_amounts['Fee']-$TotalPayed)<=0){  //Pagamento > Fee
                $Fee = 0;
                $TotalPayed -= $a_amounts['Fee'];
                if((($a_amounts['ResearchFee']+$a_amounts['Notification'])-$TotalPayed)<=0){ //Pagamento > Fee + Spese
                    $TotalNotification = 0;
                    $TotalPayed -= ($a_amounts['ResearchFee']+$a_amounts['Notification']);
                    if(($a_amounts['PercentualAmount'] - $TotalPayed) <= 0){ //Pagamento > Fee + Spese + Maggiorazione
                        $PercentualAmount = 0;
                        $TotalPayed -= $a_amounts['PercentualAmount'];
                    }
                    else{ //Pagamento < Fee + Spese + Maggiorazione --> sottraggo da tutti ma parzialmente da Percentual
                        $PercentualAmount = ($a_amounts['PercentualAmount'] - $TotalPayed);
                        $TotalPayed = 0;
                        //continue; //Salta l'elaborazione se risulta pagato più del dovuto???
                    }
                }
                else{ //Pagamento < Fee + Spese --> sottraggo solo da Fee e Spese
                    $TotalNotification = (($a_amounts['ResearchFee']+$a_amounts['Notification'])-$TotalPayed);
                    $PercentualAmount = $a_amounts['PercentualAmount'];
                    $TotalPayed = 0;
                }
            }
            else{ //Pagamento < Fee --> sottraggo solo da Fee
                $Fee = ($a_amounts['Fee']-$TotalPayed);
                $TotalNotification = ($a_amounts['ResearchFee']+$a_amounts['Notification']);
                $PercentualAmount = $a_amounts['PercentualAmount'];
                $TotalPayed = 0;
            }
        }
        else{
            $Fee = $a_amounts['Fee'];
            $TotalNotification = $a_amounts['ResearchFee']+$a_amounts['Notification'];
            $PercentualAmount = $a_amounts['PercentualAmount'];
        }
        
        //Incremento i totali generali
        $GrandTotalFee += $Fee;
        $GrandTotalNotification += $TotalNotification;
        $GrandTotalPercentualFee += $PercentualAmount;
        
        //********************************
        
        //Le celle con ="$valore" serve ad indicare ad excel the il va
        $xls .= '
            <tr>
                <th align="left">
                    '.$Name.' '.$legalform.'
                </th>
                <th align="left">
                    '.$Person.'
                </th>
                <th align="left">
                    ="'.$TaxVatCode.'"
                </th>
                <th align="left">
                    '.$Residence.'
                </th>
                <th align="left">
                    '.$Born.'
                </th>
                <th alight="right">
                    '.$TrespasserType.'
                </th>
                <th align="right">
                    '.$Cron.'
                </th>
                <th align="right">
                    '.$Ref.'
                </th>
                <th align="center">
                    '.$FineDate.'
                </th>
                <th align="center">
                    '.$NotificationDate.'
                </th>
                <th align="right">
                    '.number_format($Fee,2,',','.').'€
                </th>
                <th align="right">
                    '.number_format($TotalNotification,2,',','.').'€
                </th>
                <th align="right">
                    '.number_format($PercentualAmount,2,',','.').'€
                </th>
            </tr>
        ';    
    }
    //Totali
    $xls .= '<tr>
                <th colspan="10"></th>
                <th align="right">'.number_format($GrandTotalFee,2,',','.').'€</th>
                <th align="right">'.number_format($GrandTotalNotification,2,',','.').'€</th>
                <th align="right">'.number_format($GrandTotalPercentualFee,2,',','.').'€</th>
             </tr>';
    $xls .= '</table>';
    
    //Scrive il report in formato xls sul file system
    file_put_contents($filePath, "\xEF\xBB\xBF".$xls);
    
    return array("path" => $webFilePath, "fileName" => $FileName);
}

function filtriXLS(&$xls, $minExpiredRates){
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_ToFineDate;
    global $s_TypePlate;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_HasDispute;
    global $Search_HasDisputePrefect;
    global $Search_Expired;
    global $Search_Anomalies;
    global $Search_Payed;
    global $Search_PaymentType;
    global $Search_ValidatedAddress;
    global $Search_HasCAD;
    global $Search_HasNotification;
    global $Order_Type;
    
    $a_ExpiredInstalmentsOptions = unserialize(EXP_INJUNCTION_EXPIRED_INSTALMENTS_OPTIONS);
    $a_DisputePrefectOptions = unserialize(EXP_INJUNCTION_DISPUTEPREFECT_OPTIONS);
    $a_AnomaliesOptions = unserialize(EXP_INJUNCTION_ANOMALIES_OPTIONS);
    $a_PayedReminderOptions = unserialize(EXP_INJUNCTION_PAYEDREMINDER_OPTIONS);
    $a_PaymentOptions = unserialize(EXP_INJUNCTION_PAYMENT_OPTIONS);
    $a_ValidatedAddressOptions = unserialize(EXP_INJUNCTION_VALIDATEDADDRESS_OPTIONS);
    $a_OrderOptions = unserialize(EXP_INJUNCTION_ORDER_OPTIONS);
    $a_DisputeOptions = unserialize(EXP_INJUNCTION_DISPUTE_OPTIONS);
    $a_CADDocumentOptions = unserialize(EXP_INJUNCTION_HASCADDOCUMENT_OPTIONS);
    $a_NotDocumentOptions = unserialize(EXP_INJUNCTION_HASNOTDOCUMENT_OPTIONS);
    
    //Inizializazione xls
    $xls .= '
    <table border="1">
        	<tr>
        		<th>
        		FILTRI:
        		</th>
        	</tr>
            <tr>
                <th bgcolor="white">
        		ANNO
        		</th>
        		<th bgcolor="white">
        		DA
        		</th>
                <th bgcolor="white">
        		'.$Search_FromProtocolYear.'
        		</th>
                <th bgcolor="white">
        		A
        		</th>
                <th bgcolor="white">
        		'.$Search_ToProtocolYear.'
        		</th>
        	</tr>
            <tr>
                <th bgcolor="white">
        		CRON
        		</th>
                <th bgcolor="white">
        		DA
        		</th>
                <th bgcolor="white">
        		'.$Search_FromProtocolId.'
        		</th>
                <th bgcolor="white">
        		A
        		</th>
                <th bgcolor="white">
        		'.$Search_ToProtocolId.'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		DATA ACCERTAMENTO
        		</th>
                <th bgcolor="white">
        		DA
        		</th>
                <th bgcolor="white">
        		'.$Search_FromFineDate.'
        		</th>
                <th bgcolor="white">
        		A
        		</th>
                <th bgcolor="white">
        		'.$Search_ToFineDate.'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		NAZIONALITA\' TRASGRESSORE
        		</th>
                <th bgcolor="white">
        		'.($s_TypePlate == "N" ? "NAZIONALE" : "ESTERO").'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		DATA NOTIFICA
        		</th>
                <th bgcolor="white">
        		DA
        		</th>
                <th bgcolor="white">
        		'.$Search_FromNotificationDate.'
        		</th>
                <th bgcolor="white">
        		A
        		</th>
                <th bgcolor="white">
        		'.$Search_ToNotificationDate.'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI CON RICORSO AL GIUDICE DI PACE ED EVENTUALI GRADI SUCCESSIVI CHIUSO VINTO DAL COMUNE
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_DisputeOptions[$Search_HasDispute] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI CON RICORSO AL PREFETTO CHIUSO VINTO DAL COMUNE E NON APPELLATO
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_DisputePrefectOptions[$Search_HasDisputePrefect] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI CON ALMENO N. '.$minExpiredRates.' RATE SCADUTE
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_ExpiredInstalmentsOptions[$Search_Expired] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI CON ANOMALIE ANAGRAFICA
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_AnomaliesOptions[$Search_Anomalies] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI CHE HANNO PAGATO L\'IMPORTO DEL SOLLECITO
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_PayedReminderOptions[$Search_Payed] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		PAGAMENTI
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_PaymentOptions[$Search_PaymentType] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI NOTIFICATE VIA CAD SENZA PAGAMENTO E PRIVE DI VALIDAZ. DELL\'INDIRIZZO
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_ValidatedAddressOptions[$Search_ValidatedAddress] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI NOTIFICATE CON CAD E IMMAGINE MANCANTE
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_CADDocumentOptions[$Search_HasCAD] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		POSIZIONI NOTIFICATE TRAMITE RACCOMANDATA SENZA IMMAGINE DELLA NOTIFICA
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_NotDocumentOptions[$Search_HasNotification] ?? '').'
        		</th>
            </tr>
            <tr>
                <th bgcolor="white">
        		ORDINA PER
        		</th>
                <th bgcolor="white">
        		'.strtoupper($a_OrderOptions[$Order_Type]['Name'] ?? '').'
        		</th>
            </tr>
    ';
    
}

//Modifica il pdf aggiungendoci la data di inoltro al concessionario, il responsabile del procedimento e funzionario responsabile
function printConcessionaireDatePDF($date, $processcontrollerid, $officercontrollerid, $dirPath, $originfileName, $newfilename){
    /** @var FPDI $pdf_union */
    /** @var TCPDF $pdf_union */
    
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array('helvetica', '', 8));
    $pdf_union->setFooterFont(array('helvetica', '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);
    try {
        ob_start();
        $n_PageCount = $pdf_union->setSourceFile($dirPath."/".$originfileName);
        for ($p = 1; $p <= $n_PageCount; $p++) {
            $tmp_Page = $pdf_union->ImportPage($p);
            $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
            $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
            
            $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
            $pdf_union->useTemplate($tmp_Page);
            if($p == 2){
                $pdf_union->SetFont('helvetica','',10);
                $pdf_union->Rect(73, 75, 60, 4, 'DF', array('all' => 0), array(255,255,255));
                $pdf_union->MultiCell(60, 0, $processcontrollerid, 0, 'C', true, 1, 73, 75);
                $pdf_union->Rect(60, 155, 30, 4, 'DF', array('all' => 0), array(255,255,255));
                $pdf_union->MultiCell(30, 0, $date, 0, 'C', true, 1, 60, 155);
                $pdf_union->Rect(130, 155, 60, 4, 'DF', array('all' => 0), array(255,255,255));
                $pdf_union->MultiCell(60, 0, $officercontrollerid, 0, 'C', true, 1, 130, 155);
                }
        }
        
        $pdf_union->Output($dirPath . "/" . $newfilename, 'F');
        ob_end_clean();
        return true;
    } catch (Exception $e) {
        trigger_error("<PAGINA DATA INOLTRO CONCESSIONARIO> ATTENZIONE -> Errore nella modifica della pagina del documento di ruolo: $e",E_USER_WARNING);
        ob_end_clean();
        return false;
    }
}
//******************************************XLS***************************************************