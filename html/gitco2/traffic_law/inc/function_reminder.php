<?php 
///////////////////////////////////////////////////////////////////////////////
// Funzioni per elaborazione pagamenti per solleciti
///////////////////////////////////////////////////////////////////////////////

//TODO spostare in views.php
const PRC_PAYMENT = array(
    'aliases' => array(
        'Id' => 'F.Id',
        'Code' => 'F.Code',
        'FineCountry' => 'F.CountryId',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'VehicleMass' => 'F.VehicleMass',
        'ReminderDate' => 'F.ReminderDate',
        'FineAddress' => 'F.Address',
        'KindSendDate' => 'F.KindSendDate',
        'PrintDate' => 'FR.PrintDate',
        'FineReminderId' => 'FR.Id',
        'NotificationDate' => 'FN.NotificationDate',
        'ReminderAdditionalFeeProcedure' => 'FN.ReminderAdditionalFeeProcedure',
        'Fee' => 'FA.Fee',
        'MaxFee' => 'FA.MaxFee',
        'PrefectureFee' => 'FA.PrefectureFee',
        'ReducedPayment' => 'ART.ReducedPayment',
        'PrefectureFixed' => 'ART.PrefectureFixed',
        'AdditionalMass' => 'ART.AdditionalMass',
        'AdditionalNight' => 'ART.AdditionalNight',
        'AdditionalFee0' => 'FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.NotifierFee + FH.OtherFee',
        'AdditionalFee1' => 'FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.CanFee + FH.CadFee + FH.NotifierFee + FH.OtherFee',
        'SendDate' => 'FH.SendDate',
        'DeliveryDate' => 'FH.DeliveryDate',
        'ResultId' => 'FH.ResultId',
        'TrespasserId' => 'FH.TrespasserId',
        'TrespasserTypeId' => 'FH.TrespasserTypeId',
        'NotificationTypeId' => 'FH.NotificationTypeId',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'ZoneId' => 'T.ZoneId',
        'TrespasserCountry' => 'T.CountryId',
        'TrespasserAddress' => 'T.Address',
        'TrespasserCity' => 'T.City',
        'ZIP' => 'T.ZIP',
        'Province' => 'T.Province',
        'BornCountry' => 'T.BornCountryId',
        'BornPlace' => 'T.BornPlace',
        'BornDate' => 'T.BornDate',
        'StatusRateId' => 'PR.StatusRateId'
    ),
    "from" => "Fine F
        JOIN FineNotification FN ON F.Id = FN.FineId
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN ArticleTariff ART ON FA.ArticleId = ART.ArticleId AND ART.Year = F.ProtocolYear
        JOIN FineHistory FH ON F.Id = FH.FineId
        JOIN Trespasser T ON FH.TrespasserId = T.Id
        LEFT JOIN FineReminder FR ON F.Id = FR.FineId AND F.ReminderDate = FR.PrintDate
        LEFT JOIN PaymentRate PR ON F.Id = PR.FineId AND PR.StatusRateId <> ".RATEIZZAZIONE_CHIUSA,
    "where" => "(FR.Id IS NULL OR COALESCE(FR.Documentation, '') = '')
        AND FH.NotificationTypeId = 6
        AND FN.PaymentProcedure = 1
        AND F.StatusTypeId >= 25
        AND F.StatusTypeId <= 30
        AND FN.NotificationDate IS NOT NULL
        AND (FN.ResultId <= 3
            OR FN.ResultId >= 4
            AND FN.ResultId <= 9
            OR FN.ResultId = 21
            AND FN.ValidatedAddress = 1
            OR FN.ResultId = 22)"
);

//Costanti per pilotare il testo dei solleciti
define ("MOTIVO", "Modificato d'ufficio da elaborazione solleciti per pagamenti completi");
define ("MOTIVO_PRESCRIZIOME", "Modificato d'ufficio da elaborazione solleciti per prescrizione");

define('STR_PAYMENT_TOT', "PAG. TOT.");
define('STR_PAYMENT_PARZ', "PAG. PARZ.");
define('STR_PAYMENT_PARZ_RIT', "PAG. PARZ. RIT.");
define('STR_PAYMENT_OME', "NON PAGATO");

define('STR_PAYMENT_OK_0', "PRIMA DELLA DATA DI NOTIFICA");
define('STR_PAYMENT_OK_1', "RID. ENTRO 5GG+TOLL.");
define('STR_PAYMENT_OK_2', "NORM. ENTRO 60GG+TOLL.");
define('STR_PAYMENT_OK_3', "1/2 MAX EDITT. ENTRO 60GG+6MESI DA NOTIF.");
define('STR_PAYMENT_OK_4', "1/2 MAX EDITT. + %s MAGG. SEMESTRALE");
define('STR_PAYMENT_OK_5', "IMPORTO RICORSO");
define('STR_PAYMENT_OK_6', "IMPORTO RICORSO + %s MAGG. SEMESTRALE");

define('STR_PAYMENT_KO_0', "PRIMA DELLA DATA DI NOTIFICA - IMP. PAG. %s < DOV. %s");
define('STR_PAYMENT_KO_1', "RID. ENTRO 5GG+TOLL. - IMP. PAG. %s < DOV. %s");
define('STR_PAYMENT_KO_2', "NORM. ENTRO 60GG+TOLL. - IMP. PAG. %s < DOV. %s");
define('STR_PAYMENT_KO_3', "1/2 MAX EDITT. ENTRO 60GG+6MESI DA NOTIF. - IMP. PAG. %s < DOV. %s");
define('STR_PAYMENT_KO_4', "1/2 MAX EDITT. + %s MAGG. SEM. - IMP. PAG. %s < DOV. %s");
define('STR_PAYMENT_KO_5', "IMPORTO RICORSO - IMP. PAG. %s < DOV. %s");
define('STR_PAYMENT_KO_6', "IMPORTO RICORSO + %s MAGG. SEM. - IMP. PAG. %s < DOV. %s");

define('STR_FROM_NOTIFICATION_DATE_OK'," DA DATA NOT. (OK)");
define('STR_FROM_NOTIFICATION_DATE_KO'," DA DATA NOT. (KO): PAG.");
define('STR_AMOUNT_NO_NOTIFICATION', " < DOV. (NO SPESE NOT.) ");
define('STR_AMOUNT_NOTIFICATION', " < DOV. ");

define('STR_OK', " (OK) ");
define('STR_KO', " (KO) ");

function stampaRigaPDF($pdf, $riga, $cronologico, $code, $trespasser, $fineDate, $fineTime, $vehiclePlate, $pagato, $dovuto, $messaggio, $colore = array(255,255,255), $coloreTesto = array(0,0,0))
{
    $length = abs(38);
    if(strlen($trespasser) > $length) {
        $trespasser = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $trespasser);
    }
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->SetFont('helvetica', '', 7);
    call_user_func_array([$pdf, 'SetFillColor'], $colore);
    call_user_func_array([$pdf, 'SetTextColor'], $coloreTesto);
    $pdf->MultiCell(10, 0, $riga, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(20, 0, $cronologico, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(40, 0, $code, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(65, 0, $trespasser, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, $fineDate, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(10, 0, $fineTime, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(18, 0, $vehiclePlate, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, number_format($pagato, 2, ',', ''), 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, number_format($dovuto, 2, ',', ''), 1, 'R', 1, 0, '', '', true);
    $pdf->SetFont('helvetica', '', 7);
    //$pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(70, 0, $messaggio, 1, 'R', 1, 1, '', '', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0,0,0);
    // $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
}

function coloreTestoChiusi($pagato, $dovuto, $margine){
    if($pagato-$dovuto >= 0.0001){
        //Pagato > dovuto (giallo)
        return array(107,77,0);
    } else if(($dovuto-($pagato+$margine)) >= 0.0001){
        //Pagato < dovuto + margine (rosso)
        return array(142,0,0);
    } else {
        return array(0,0,0);
    }
}

//Nel caso con ricorso non aggiungiamo le tolleranze delle giornate aggiuntive su pagamento ridotto/normale 
// perché nel ricorso i giorni già si allungano
function dimScaglione($scaglione, $n_ReducedPaymentDayAccepted = 0, $n_PaymentDayAccepted = 0) {
    $dateString = "";
    switch($scaglione) {
        case 0: //entro la notifica
            $dateString = '0 days';
            break;
        case 1: //5gg
            $dateString = (FINE_DAY_LIMIT_REDUCTION).' days';
            break;
        case 2: //60gg
            $dateString = (FINE_DAY_LIMIT - (FINE_DAY_LIMIT_REDUCTION)).' days'; //dovrebbe sommare 55
            break;
        default:
            $dateString = FINE_MONTH_LIMIT_SEMESTRAL.' months';
    }
    return $dateString;
}

//Legenda per giorni nelle configurazioni
//$n_ReducedPaymentDayAccepted    = $r_ProcessingData['ReducedPaymentDayAccepted']; //gg pagamento ridotti
//$n_PaymentDayAccepted           = $r_ProcessingData['PaymentDayAccepted']; //gg pagamento normale
//NOTA 15/11/2022 per retrocompatibilità le inizializzo a 0 perché vanno in realtà tolte e usate solo nel caso di pagamento presente
function dimScaglioneSenzaRicorso($scaglione, $n_ReducedPaymentDayAccepted = 0, $n_PaymentDayAccepted = 0) {
    $dateString = "";
    switch($scaglione) {
        case 0: //entro la notifica
            $dateString = '0 days';
            break;
        case 1: //5gg
            $dateString = (FINE_DAY_LIMIT_REDUCTION) .' days';
            break;
        case 2: //60gg
            //tolgo da 60 gg + gli aggiuntivi quelli già sommati al passo precedente
            $dateString = (FINE_DAY_LIMIT - (FINE_DAY_LIMIT_REDUCTION)).' days'; //dovrebbe sommare 55 oltre ai 5
            break;
        //Dal 61° giorno viene effettuata una maggiorazione ogni 6 mesi
        default:
            $dateString = FINE_MONTH_LIMIT_SEMESTRAL.' months';
    }
    return $dateString;
}

function messaggioScaglione($scaglione, $TotaleSanzione, $TotalePagato, $b_pagato, $b_parziale, $continuaElaborazionePerPagamentoScaduto, $tollRidotto, $tollNormale) {
    $messaggio = "";
    $elaborazione = $continuaElaborazionePerPagamentoScaduto==true ? " PAG. RIT. " : "";
    switch($scaglione) {
        case 0: //pre notifica
            if($b_pagato) {
                $messaggio .= STR_PAYMENT_OK_0;
            }
            if($b_parziale) {
                $messaggio .= STR_PAYMENT_KO_0. number_format($TotalePagato, 2, ',', '').STR_AMOUNT_NO_NOTIFICATION. number_format($TotaleSanzione, 2, ',', '');
                $messaggio .= $elaborazione;
            }
            break;
        case 1: //Entro 5 gg
            if($b_pagato) {
                $messaggio .= STR_PAYMENT_OK_1." (".$tollRidotto."gg) ".STR_FROM_NOTIFICATION_DATE_OK;
            }
            if($b_parziale) {
                $messaggio .= STR_PAYMENT_KO_1." (".$tollRidotto."gg) ".STR_FROM_NOTIFICATION_DATE_KO. number_format($TotalePagato, 2, ',', '').STR_AMOUNT_NOTIFICATION. number_format($TotaleSanzione, 2, ',', '');
                $messaggio .= $elaborazione;
            }
            break;
        case 2: //Entro 60gg
            if($b_pagato) {
                $messaggio .= STR_PAYMENT_OK_2." (".$tollNormale."gg) ".STR_FROM_NOTIFICATION_DATE_OK;
            }
            if($b_parziale) {
                $messaggio .= STR_PAYMENT_KO_2." (".$tollNormale."gg) ".STR_FROM_NOTIFICATION_DATE_KO.number_format($TotalePagato, 2, ',', '').STR_AMOUNT_NOTIFICATION. number_format($TotaleSanzione, 2, ',', '');
                $messaggio .= $elaborazione;
            }
            break;
        case 3: //Entro 60 gg + 6 mesi
            if($b_pagato) {
                $messaggio .= STR_PAYMENT_OK_3;
            }
            if($b_parziale) {
                $messaggio .= STR_PAYMENT_KO_3 .number_format($TotalePagato, 2, ',', '').STR_AMOUNT_NOTIFICATION.number_format($TotaleSanzione, 2, ',', '');
                $messaggio .= $elaborazione;
            }
            break;
        default:
            if($b_pagato) {
                $messaggio .= "PAG. TOT. 1/2 MAX EDITT. + ". ($scaglione - 3)." MAGG. SEM. (OK)";
            }
            if($b_parziale) {
                $messaggio .= "PAG. PARZ. 1/2 MAX EDITT. + ". ($scaglione - 3)." MAGG. SEM. (OK): PAGATO ". number_format($TotalePagato, 2, ',', '') .STR_AMOUNT_NOTIFICATION. number_format($TotaleSanzione, 2, ',', '');
                $messaggio.= $elaborazione;
            }
    }
    return $messaggio;
}

function leggiPagamenti($scaglione, $rs, $dataInizio, $dataFine, $FineId) {
    $Amount = 0;
    if ($scaglione == 0)
        $rs_FinePayment = $rs->SelectQuery("SELECT count(*) CountT, SUM(Amount) Amount FROM FinePayment WHERE FineId=". $FineId ." AND PaymentDate < '".$dataFine."'"); //non considero la data di notifica iniziale
    elseif ($scaglione == 1)
        $rs_FinePayment = $rs->SelectQuery("SELECT count(*) CountT, SUM(Amount) Amount FROM FinePayment WHERE FineId=". $FineId ." AND PaymentDate >= '".$dataInizio. "' AND PaymentDate <= '".$dataFine."'");
    else
        $rs_FinePayment = $rs->SelectQuery("SELECT count(*) CountT, SUM(Amount) Amount FROM FinePayment WHERE FineId=". $FineId ." AND PaymentDate > '".$dataInizio. "' AND PaymentDate <= '".$dataFine."'");
    $r_FinePayment = mysqli_fetch_array($rs_FinePayment);
    $Count = (isset($r_FinePayment['CountT']) && !empty($r_FinePayment['CountT'])) ? $r_FinePayment['CountT'] : 0;
    if($Count>0)
        $Amount += isset($r_FinePayment['Amount']) && !empty($r_FinePayment['Amount']) ? $r_FinePayment['Amount'] : 0;
    return $Amount;
}

//calcola la sanzione in caso con ricorso. Se non è fissata sanzione in giudizio vale quella standard
function  calcolaSanzione($scaglione, $DisputeFee, $TotalReducedFee, $TotalMaxFee,$TotalFee, $NationalPercentualReminder,
    $dovutoResiduo,  $b_dispute,  $IncludeNotificationResearch, $AdditionalFee, &$maggiorazioneAccumulata, $NumeroMaggiorazioniSemestrali) {
        
        $sanzione = 0;
        $residuo = 0;
        switch($scaglione) {
            case 0: //pre notifica
                $sanzione = $TotalReducedFee - $AdditionalFee; //tolgo le spese di notifica e ricerca
                if ($b_dispute and $DisputeFee>0) {
                    $sanzione = $DisputeFee;
                if($IncludeNotificationResearch)
                    $sanzione = $sanzione + $AdditionalFee;
                }
                break;
            case 1: //Entro 5 gg
                $sanzione = $TotalReducedFee;
                if ($b_dispute and $DisputeFee>0) {
                    $sanzione = $DisputeFee;
                }
                break;
            case 2: //Entro 60gg
                $sanzione = $TotalFee;
                if ($b_dispute and $DisputeFee>0) {
                    $sanzione = $DisputeFee;
                }
                break;
            case 3: //Entro 60 gg + 6 mesi
                $sanzione = $TotalMaxFee;
                if ($b_dispute and $DisputeFee>0) {
                    $sanzione = $DisputeFee;
                }
                break;
            default:
                    $sanzione = $TotalMaxFee;
                    if ($b_dispute and $DisputeFee>0)
                        $sanzione = $DisputeFee;
                    //La maggiorazione accumulata viene passata per riferimento
                    //Il min è stato introdotto perchè, in caso di verbale parzialmente pagato, bisogna maggiorare solo la differenza
                    if(($dovutoResiduo-$maggiorazioneAccumulata) >= 0)
                        $maggiorazioneAccumulata += (min(($dovutoResiduo-$maggiorazioneAccumulata),$sanzione)*$NationalPercentualReminder)/100;
                    
                    //nota: con una legge è stato fissato che la somma delle maggiorazioni semestrali non può eccedere i 3/5 della sanzione base
                    // che è base imponibile della maggiorazione stessa
                    if($maggiorazioneAccumulata > $TotalMaxFee*LIMITE_MAGGIORAZIONE)
                        $maggiorazioneAccumulata = $TotalMaxFee*LIMITE_MAGGIORAZIONE;
                        
                    $sanzione = ($sanzione+$maggiorazioneAccumulata);
        }
        return $sanzione;
}

function  calcolaSanzioneSenzaRicorso(
    $scaglione, $DisputeFee, $TotalReducedFee, 
    $TotalMaxFee,$TotalFee, $NationalPercentualReminder,
    $dovutoResiduo, $IncludeNotificationResearch, 
    $dataApplicazione, $ProcessingDate, $ApplyPercentualOnPrefectureFee, 
    $DateLimitNext, $sanzioneTotalePrecedente, $TotalePagato,  
    $AdditionalFee, &$maggiorazioneAccumulata, $NumeroMaggiorazioniSemestrali) {
        
    $sanzione = 0;
    $residuo = 0;
    switch($scaglione) {
        case 0: //pre notifica
            $sanzione = $TotalReducedFee - $AdditionalFee; //tolgo le spese di notifica e ricerca
            break;
        case 1: //1 - 5 gg
            $sanzione = $TotalReducedFee;
            break;
        case 2: //6 - 60gg
            $sanzione = $TotalFee;

            break;
        case 3: //61 - 6mesi
            $sanzione = $TotalMaxFee;
            break;
        default:
            //La maggiorazione accumulata viene passata per riferimento
            //Il min è stato introdotto perchè, in caso di verbale parzialmente pagato, bisogna maggiorare solo la differenza
            //trigger_error("***SCAGLIONE: ".$scaglione." DOVUTO RESIDUO: ".$dovutoResiduo." dovuto-magg: ".($dovutoResiduo-$maggiorazioneAccumulata));
            if(($dovutoResiduo-$maggiorazioneAccumulata) >= 0)
                $maggiorazioneAccumulata += ((min(($dovutoResiduo-$maggiorazioneAccumulata),$TotalMaxFee)*$NationalPercentualReminder)/100);
                
            //nota con una legge è stato fissato che la somma delle maggiorazioni semestrali non può eccedere i 3/5 della sanzione base 
            // che è base imponibile della maggiorazione stessa
            if($maggiorazioneAccumulata > $TotalMaxFee*LIMITE_MAGGIORAZIONE)
                $maggiorazioneAccumulata = $TotalMaxFee*LIMITE_MAGGIORAZIONE;
            
            $sanzione = ($TotalMaxFee + $maggiorazioneAccumulata);
    }
    //echo $PercentualAmount . " 10% <br/>";
    //echo ($SanzioneSemestraleAccumulata + $PercentualAmount) ." somma accumulata 10%<br/>";
    return $sanzione;
}

/*
 * CREAZIONE STAMPA BASE ELABORA SOLLECITI
 * */

//*****INTESTAZIONE*****
/**
 * @desc intestazione elabora solleciti
 * **/
function newBasePageType1($pdf, $r_Customer, $n_CountFine){
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 8);
    
    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);
    
    $ManagerName = $r_Customer['ManagerName'];
    $ManagerAddress = $r_Customer['ManagerAddress'];
    $ManagerCity = $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")";
    $ManagerPhone = $r_Customer['ManagerPhone'];
    
    //Intestazione superiore sinistra
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerName, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);
    
    $pdf->LN(20);
    
    if ($n_CountFine == 0){
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(0, 0, 'ELABORAZIONE PAGAMENTI PER GENERAZIONE SOLLECITI', 0, 'C', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->LN(5);
        $pdf->MultiCell(0, 0, 'DATA STAMPA: '.$PrintDate, 0, 'C', 0, 0, '', '', true);
        $pdf->LN(10);
    }
    
    //Intestazione tabella (w: 278, h: 0)
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(10, 0, 'Riga', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(20, 0, 'Cronologico', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(23, 0, 'Rif.to', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Trasgressore', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Data', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(10, 0, 'Ora', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Targa', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Pagato', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Dovuto', 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(120, 0, 'Risultato', 1, 'C', 0, 1, '', '', true);
    ///////////////////////////////////////////////////////////////////////////////////////////
}

//*****CORPO*****
/**
 * @desc corpo elabora solleciti
 * **/
function printPDFRowType1($pdf, $riga, $cronologico, $code, $trespasser, $fineDate, $fineTime, $vehiclePlate, $pagato, $dovuto, $messaggio, $colore = array(255,255,255), $coloreTesto = array(0,0,0))
{
    $length = abs(38);
    if(strlen($trespasser) > $length) {
        $trespasser = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $trespasser);
    }
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->SetFont('helvetica', '', 7);
    call_user_func_array([$pdf, 'SetFillColor'], $colore);
    call_user_func_array([$pdf, 'SetTextColor'], $coloreTesto);
    $pdf->MultiCell(10, 0, $riga, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(20, 0, $cronologico, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(23, 0, substr($code,0,12), 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(35, 0, substr($trespasser, 0, 20), 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, $fineDate, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(10, 0, $fineTime, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, $vehiclePlate, 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, number_format($pagato, 2, ',', '.'), 1, 'R', 1, 0, '', '', true);
    $pdf->MultiCell(15, 0, number_format($dovuto, 2, ',', '.'), 1, 'R', 1, 0, '', '', true);
    $pdf->SetFont('helvetica', '', 7);
    //$pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(120, 0, $messaggio, 1, 'R', 1, 1, '', '', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0,0,0);
    // $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
}

//*****SOMMARIO*****
/**
 * @desc sommario elabora solleciti
 * **/
function printSummaryTableType1($pdf, $n_total_row, $ProcessingDate){
    $pdf->LN(5);
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(70, 0, "Righe totali:", 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, $n_total_row, 1, 'C', 0, 1, '', '', true);
    $pdf->MultiCell(70, 0, "Data di elaborazione:", 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, DateOutDB($ProcessingDate), 1, 'C', 0, 1, '', '', true);
}

//*****STAMPA PROVVISORIA*****
/**
 * @desc stampa provvisoria elabora solleciti
 * **/
function printPrevisionalLabel($pdf){
    $TotalPages = $pdf->PageNo();
    
    for ($i=1; $i<=$TotalPages; $i++){
        $pdf->setPage($i, true);
        
        $pdf->StartTransform();
        // Scale by 150% centered by (50,80) which is the lower left corner of the rectangle
        $pdf->SetFont('Helvetica', '', 30);
        $pdf->SetTextColor(190);
        $pdf->Rotate(25, 130, 120);
        $pdf->Text(20, 100, 'S   T   A   M   P   A         P   R   O   V   V   I   S   O   R   I   A');
        // Stop Transformation
        $pdf->StopTransform();
    }
}

//Intestazione elenco solleciti
/**
 * @deprecated
 * @desc intestazione elenco solleciti
 * **/
function newBasePageType2($pdf, $operazione){
    //Nuova pagina//////////////////////////////////////////////////////////////////////////////
    $page_format = "";
    $pdf->Header = false;
    $pdf->SetMargins(10,10,10);
    $pdf->AddPage('L', $page_format);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->writeHTML('<h2>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'</h2>', true, false, true, false, '');
    //$pdf->writeHTML('<h3>ELENCO DI TUTTI GLI ATTI</h3>', true, false, true, false, '');
    
    $pdf->LN(2);
    ////////////////////////////////////////////////////////////////////////////////
    //Celle header//////////////////////////////////////////////////////////////////////////////
    $pdf->SetFont('helvetica', 'B', 8);
    //Prima riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(275, 0, 'Trasgressore', 1, '', 0, 0, '', '', true);
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Seconda riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(80, 0, 'Data definizione', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(30, 0, 'Anno', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'San.Am.Dov', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Dovute', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Mag.Dov.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Solle.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Tot. Dovuto', 1, 'R', 0, 0, '', '', true);
    //Cella destra di 4 righe
    //Altezza della cella grande di destra
    $pdf->setCellPadding(8.15);
    $pdf->MultiCell(40, 0, 'Ulteriori Dati', 1, 'C', 0, 0, '', '', true);
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Terza riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(60, 0, 'N.Cronol+Targa', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Data versamento', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Stamp.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'San.Am.Pag', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Pagate', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Mag.Pag.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Sem.Magg.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Tot. Pagato', 1, 'R', 0, 0, '', '', true);
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Quarta riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(60, 0, 'Data Verb. - Data Not.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Dettaglio Pag.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, '', 0, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Data Calc.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Dettag. Ricorso', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, '', 0, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'G.Agg.DataDef.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Differenza', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    //Quinta riga
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    if($operazione == "LIST"):
        $pdf->MultiCell(235, 0, 'Data elaborazione sollecito', 1, '', 0, 0, '', '', true);
    elseif($operazione == "LIST_EMITTED"):
        $pdf->MultiCell(60, 0, 'Data elaborazione sollecito', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(60, 0, 'Data stampa sollecito', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(60, 0, 'Data creazione flusso di stampa', 1, '', 0, 0, '', '', true);
        $pdf->MultiCell(55, 0, 'Data invio flusso', 1, '', 0, 0, '', '', true);
    endif;
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->LN(3);
    //////////////////////////////////////////////////////////////////////////////
}

//Sommario elenco solleciti
/**
 * @deprecated
 * @desc sommario elenco solleciti
 * **/
function printSummaryTableType2($pdf,$RownNumber,$GrandTotalAmount,$GrandNotificationFees,$GrandPercentualAmount,$GrandReminderFees,
    $GrandOriginalAmount,$PrintDate,$GrandPayedAmountFee,$GrandPayedFees,$GrandPayedPercentual,$GrandTotalAmountPayed,
    $GrandDifference,$GrandSemester){
    //Celle footer//////////////////////////////////////////////////////////////////////////////
    $pdf->setCellHeightRatio(1.5);
    
    $pdf->SetFont('helvetica', 'B', 8);
    
    $pdf->LN(4);
    
    $pdf->MultiCell(110, 0, 'NUMERO TOTALE ATTI: '.$RownNumber, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandTotalAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandNotificationFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPercentualAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandReminderFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandOriginalAmount, 2, ',', '.'), 1, 'R', 0, 1, '', '', true);
    
    $pdf->MultiCell(110, 0, 'Stampa del '.$PrintDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPayedAmountFee, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPayedFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPayedPercentual, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, $GrandSemester, 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandTotalAmountPayed, 2, ',', '.'), 1, 'R', 0, 1, '', '', true);
    
    $pdf->MultiCell(210, 0, '', 0, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandDifference, 2, ',', '.'), 1, 'R', 0, 1, '', '', true);
}

/** @deprecated */
//In caso si ricerchi il 29/02 in un anno bisestile, imposta automaticamente il 28/02
function aggiustaBisestile(&$data)
{
    if($data!="")
    {
    $arrayData = explode("/",$data);
    $giorno = $arrayData[0];
    $mese = $arrayData[1];
    $anno = $arrayData[2];
    $bisestile = ($anno%100 == 0) ? ($anno%400 == 0) : ($anno%4 == 0);
    if($giorno == 29 && $mese == 02 && !$bisestile)
        $giorno = 28;
        
        $data = $giorno."/".$mese."/".$anno;
    }
}


//Intestazione stampa sollecito
function buildHeaderReminder($r_Customer, $PrintDestinationFold){
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
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ReminderPhone'] != ''){
        $str_Header .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Header .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Header .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Header .= $r_Customer['ReminderPhone'] ? 'TEL: '.$r_Customer['ReminderPhone'] : '';
    }
    return $str_Header.'</span>';
}




//NUOVE FUNZIONI///////////////////////////////////////////////////////////////////////////////////////

function messaggioRiga(bool $pagTot, cls_pagamenti $cls_pagamenti, String $cityId, int $tollRidotto, int $tollMinimo){
    //TODO è sbagliato gaurdare lastpaymentday, andrebbe guardata processingdate, perchè????
    global $rs;
    $notificationDate = $cls_pagamenti->getNotificationDate();
    $lastPaymentDate = $cls_pagamenti->getLastPaymentDate();
    $dataNot5gg = SkipFestiveDays(date('Y-m-d', strtotime($notificationDate. " + ".FINE_DAY_LIMIT_REDUCTION." days")), $cityId, $rs);
    $dataNot5gg = date('Y-m-d', strtotime($dataNot5gg. " + ". $tollRidotto." days"));
    $dataNot60gg = SkipFestiveDays(date('Y-m-d', strtotime($notificationDate. " + ".FINE_DAY_LIMIT." days")), $cityId, $rs);
    $dataNot60gg = date('Y-m-d', strtotime($dataNot60gg. " + ".$tollMinimo." days"));
    $TotalPayed = $cls_pagamenti->getPayed();
    $TotalAmount = $cls_pagamenti->getFee();
    $semester = $cls_pagamenti->getSemester();
    $message = '';
    
    $matchingDate = $pagTot ? $lastPaymentDate : $cls_pagamenti->getProcessingDate();
    
    if($cls_pagamenti->getStatus() == 0){
        //NON PAGATO
        $message = STR_PAYMENT_OME;
    } 
    else if($cls_pagamenti->getDisputeAmount() <= 0) {
        if($matchingDate < $notificationDate){
            //DATA PAGAMENTO minore DATA NOTIFICA
            if($pagTot){
                $message =STR_PAYMENT_TOT.' '.STR_PAYMENT_OK_0.STR_OK;
            } elseif($cls_pagamenti->getStatus() == 1){
                $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_0, $TotalPayed, $TotalAmount).STR_KO;
            } elseif($cls_pagamenti->getStatus() == 2){
                $message = STR_PAYMENT_PARZ_RIT.' '.sprintf(STR_PAYMENT_KO_0, $TotalPayed, $TotalAmount).STR_KO;
            }
        }
        elseif($matchingDate <= $dataNot5gg){
            //ENTRO 5 GIORNI + toll
            if($pagTot){
                $message =STR_PAYMENT_TOT.' '.STR_PAYMENT_OK_1.STR_OK;
            } elseif($cls_pagamenti->getStatus() == 1){
                $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_1, $TotalPayed, $TotalAmount).STR_KO;
            } elseif($cls_pagamenti->getStatus() == 2){
                $message = STR_PAYMENT_PARZ_RIT.' '.sprintf(STR_PAYMENT_KO_1, $TotalPayed, $TotalAmount).STR_KO;
            }
        }
        elseif($matchingDate <= $dataNot60gg){
            //ENTRO 60 GIORNI + toll
            if($pagTot){
                $message =STR_PAYMENT_TOT.' '.STR_PAYMENT_OK_2.STR_OK;
            } elseif($cls_pagamenti->getStatus() == 1){
                $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_2, $TotalPayed, $TotalAmount).STR_KO;
            } elseif($cls_pagamenti->getStatus() == 2){
                $message = STR_PAYMENT_PARZ_RIT.' '.sprintf(STR_PAYMENT_KO_2, $TotalPayed, $TotalAmount).STR_KO;
            }
        } else {
            //MAGGIORAZIONI
            if($semester == 0){
                if($pagTot){
                    $message =STR_PAYMENT_TOT.' '.STR_PAYMENT_OK_3.STR_OK;
                } elseif($cls_pagamenti->getStatus() == 1){
                    $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_3, $TotalPayed, $TotalAmount).STR_KO;
                } elseif($cls_pagamenti->getStatus() == 2){
                    $message = STR_PAYMENT_PARZ_RIT.' '.sprintf(STR_PAYMENT_KO_3, $TotalPayed, $TotalAmount).STR_KO;
                }
            } else {
                if($pagTot){
                    $message =STR_PAYMENT_TOT.' '.sprintf(STR_PAYMENT_OK_4.STR_OK, $semester);
                } elseif($cls_pagamenti->getStatus() == 1){
                    $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_4, $semester, $TotalPayed, $TotalAmount).STR_KO;
                } elseif($cls_pagamenti->getStatus() == 2){
                    $message = STR_PAYMENT_PARZ_RIT.' '.sprintf(STR_PAYMENT_KO_4, $semester, $TotalPayed, $TotalAmount).STR_KO;
                }
            }
        }
    }
    else {
        if($pagTot){
            if($semester > 0){
                $message =STR_PAYMENT_TOT.' '.sprintf(STR_PAYMENT_OK_6.STR_OK, $semester);
            } else {
                $message =STR_PAYMENT_TOT.' '.STR_PAYMENT_OK_5.STR_OK;
            }
        } elseif($cls_pagamenti->getStatus() == 1){
            if($semester > 0){
                $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_6, $semester, $TotalPayed, $TotalAmount).STR_KO;
            } else {
                $message = STR_PAYMENT_PARZ.' '.sprintf(STR_PAYMENT_KO_5, $TotalPayed, $TotalAmount).STR_KO;
            }
        }
    }
    return $message;
}

//Questa funzione dice se il verbale è stato estinto durante i periodi di tolleranza
function controllaTolleranze(string $cityId, string $notificationDate, ?array $cronScaglioni, ?array $cronPagamenti, bool $ridotto, int $tollRidotto, int $tollMinimo, float $importoEsclusione, &$importoDovuto, &$importoPagato){
    global $rs;
    //Il vettore cronScaglioni viene riempito solo se c'è almeno un pagamento, altrimenti è null
    if(isset($cronScaglioni)){
        foreach($cronScaglioni as $scaglione => $importoDovutoScaglione){
            switch($scaglione){
                case 1:{
                    //il controllo di tolleranza sul primo scaglione si fa solo se è previsto importo ridotto
                    if($ridotto){
                        $TotPagato = 0;
                        $dataNot5gg = SkipFestiveDays(date('Y-m-d', strtotime($notificationDate. " + ".FINE_DAY_LIMIT_REDUCTION." days")), $cityId, $rs);
                        $dataNot5gg = date('Y-m-d', strtotime($dataNot5gg. " + ". $tollRidotto." days"));
                        foreach ($cronPagamenti as $dataPaga => $importoPag){
                            if($dataPaga <= $dataNot5gg) $TotPagato += $importoPag;
                        }
                        if($TotPagato > 0 && (round($importoDovutoScaglione - ($TotPagato + $importoEsclusione), 2) < 0.01)) {
                            $importoDovuto = $importoDovutoScaglione;
                            $importoPagato = $TotPagato;
                            return $scaglione;
                        }
                    }
                    break;
                }
                case 2:{
                    $TotPagato = 0;
                    $dataNot60gg = SkipFestiveDays(date('Y-m-d', strtotime($notificationDate. " + ".FINE_DAY_LIMIT." days")), $cityId, $rs);
                    $dataNot60gg = date('Y-m-d', strtotime($dataNot60gg. " + ".$tollMinimo." days"));
                    foreach ($cronPagamenti as $dataPaga => $importoPag){
                        if($dataPaga <= $dataNot60gg) $TotPagato += $importoPag;
                    }
                    if($TotPagato > 0 && (round($importoDovutoScaglione - ($TotPagato + $importoEsclusione), 2) < 0.01)){
                        $importoDovuto = $importoDovutoScaglione;
                        $importoPagato = $TotPagato;
                        return $scaglione;
                    }
                }
            }
        }
    }
    return false;
}




