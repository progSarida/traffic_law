<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_exp_injunction.php");
require_once(PGFN."/fn_prn_anag_anomalies.php");
require_once(INC."/header.php");
require_once(CLS."/cls_view.php");
require_once(CLS."/cls_290.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

ini_set('max_execution_time', 30000);

/** @var CLS_DB $rs */
//TODO viene fatto questo perchè anche se initialization.php lo fa già, menu_top.php reinizializza un'altra istanza $rs
//che quindi non avrà il charset impostato. Da rimuovere una volta che viene rimosso $rs da menut_top oppure se verrà già impostato nel costruttore di cls_db
$rs->SetCharset('utf8');

$P = "exp_injunction.php";
$CurrentYear = $_SESSION['year'];
$ultimate = CheckValue('ultimate','n');
$CurrentDate = date('Y-m-d');
$CurrentTime = date('H:i');
$b_Error = false;
$a_Injunction = array();

if($s_TypePlate=="N"){
    $str_ProcessingTable = "National";
}
else{
    $str_ProcessingTable = "Foreign";
}

//SELEZIONE PARAMETRI PROCEDURA DI PAGAMENTO
$a_ProcessingData = $rs->getArrayLine($rs->SelectQuery("SELECT * FROM ProcessingDataPayment".$str_ProcessingTable." WHERE Disabled=0 AND CityId = '".$_SESSION['cityid']."'"));
if($a_ProcessingData==null){
    $_SESSION['Message']['Error'] = "Non è possibile procedere con l'elaborazione se non sono stati impostati i parametri dell'ente competente.<br >Compilare i parametri dell'ente dal menù Ente\Procedure Ente.";
    $b_Error = true;
}

//LETTURA PARAMETRI DELL'ENTE
$a_Customer = $rs->getArrayLine($rs->SelectQuery("SELECT * FROM Customer WHERE CityId='" . $_SESSION['cityid'] . "'"));
if($a_Customer==null || empty($a_Customer['PatronalFeast']) ){
    $_SESSION['Message']['Error'] = "Non è possibile procedere con l'elaborazione se non è stata indicata la data della festa patronale tra le configurazioni dell'ente competente.<br >Compilare il campo Festa patronale nella scheda Indirizzo del menù Ente\Gestione Ente.";
    $b_Error = true;
}

if(!$b_Error){
    $str_Where = expInjunctionWhere();
    $str_Order = expInjunctionOrderBy();
    
    if($s_TypePlate == 'N'){
        $cls_view = new CLS_VIEW(FINE_NOT_PAYED);
        $str_ProcessingTable = 'National';
    } else {
        $cls_view = new CLS_VIEW(FINE_NOT_PAYED_WITHOUT_NOTIFICATION_CONSTRAINTS);
        $str_ProcessingTable = 'Foreign';
    }
    
    $query = $cls_view->generateSelect($str_Where, null, $str_Order);
    $a_Injunction = $rs->getResults($rs->SelectQuery($query));
    
    $r_ProcessingData = $rs->getArrayLine($rs->Select("ProcessingDataPayment$str_ProcessingTable", "CityId='".$_SESSION['cityid']."'"));
    $amountLimit = $r_ProcessingData['AmountLimit'];
}

if (count($a_Injunction) > 0) {

    $n_RecordCountN2 = 0;
    $n_RecordCountN4 = 0;
    $n_RecordCountRow = 2;
    $f_TotaleAmount  = 0.00;
    
    //Davide: NOTA BENE: se mai si dovessero fare modifiche ai nomi dei file, fare attenzione a questo pezzo di stringa e come viene usato, perchè su di essa si basa della logica di salvataggi e letture per le cose del ruolo.
    $randomString = uniqid();
    
    if($ultimate)
    //Davide: NOTA BENE: se mai si dovessero fare modifiche ai nomi dei file, fare attenzione a questo pezzo di stringa e come viene usato, perchè su di essa si basa della logica di salvataggi e letture per le cose del ruolo.
        $FileName = "Tracciato290_".$_SESSION['cityid']."_".$_SESSION['year']."_" . date("Y-m-d_H-i").EXP_INJUNCTION_FILEID_SEPARATOR.$randomString;
    else
        $FileName = "PROVVISORIO_Tracciato290_".$_SESSION['cityid']."_".$_SESSION['year']."_" . date("Y-m-d_H-i");

    $dirPath = ($s_TypePlate=="N") ? NATIONAL_INJUNCTION : FOREIGN_INJUNCTION;
    $dirPath.= "/" .$_SESSION['cityid'];
    if(!is_dir($dirPath))
        mkdir($dirPath);

    $webPath = ($s_TypePlate=="N") ? WEB_NATIONAL_INJUNCTION : WEB_FOREIGN_INJUNCTION;
    $webPath.= "/" .$_SESSION['cityid'];

    $filePath = $dirPath . "/" . $FileName.".txt";
    $webFilePath = $webPath . "/" . $FileName.".txt";
    
    $a_cls_pagamenti = array();
    foreach ($a_Injunction as $r_Injunction){
        $a_cls_pagamenti[$r_Injunction['FineId']] = new cls_pagamenti($r_Injunction['FineId'], $r_Injunction['CityId']);
    }

    //Stampa PDF
    $pdfFile = injunctionPDFPrint($rs, $randomString, $a_Injunction, $a_cls_pagamenti, $dirPath, $webPath, $str_ProcessingTable, $s_TypePlate, $r_Customer['RoleMinExpiredInstallments'], $amountLimit);
    $pdfPath = '';
    $pdfFileName = '';
    
    //Il metodo costruisce già il percorso web e lo restituisce insieme al nome del file
    foreach ($pdfFile as $type => $name){
        if($type == 'path')
            $pdfPath = $name;
        elseif($type == 'fileName')
            $pdfFileName = $name;
    }
        
    //Stampa XLS
    $xlsFile = injunctionXLSPrint($rs, $randomString, $a_Injunction, $a_cls_pagamenti, $dirPath, $webPath, $str_ProcessingTable, $s_TypePlate, $r_Customer['RoleMinExpiredInstallments'], $amountLimit);
    $xlsPath = '';
    $xlsFileName = '';
    
    //Il metodo costruisce già il percorso web e lo restituisce insieme al nome del file
    foreach ($xlsFile as $type => $name){
        if($type == 'path')
            $xlsPath = $name;
        elseif($type == 'fileName')
            $xlsFileName = $name;
    }
        
    $cls_290 = new cls_290($r_Customer['RoleCityCode']);
    $cls_290->N0();
    $cls_290->N1();

    $rs->Start_Transaction();
    
    foreach ($a_Injunction as $r_Injunction) {
        /** @var cls_pagamenti $cls_pagamenti */
        $cls_pagamenti = $a_cls_pagamenti[$r_Injunction['FineId']];
        $n_ExpiredRates = 0;
        
        //In base al filtro "Pagamenti" selezionato e allo stato del pagamento, salta la riga
        $b_skipPayment = false;
        switch($Search_PaymentType){
            case INDEX_PAYMENT_OMITTED : if($cls_pagamenti->getStatus() != 0) $b_skipPayment = true; break;
            case INDEX_PAYMENT_PARTIAL : if($cls_pagamenti->getStatus() != 1) $b_skipPayment = true; break;
            case INDEX_PAYMENT_DELAYED : if($cls_pagamenti->getStatus() != 2) $b_skipPayment = true; break;
        }
        if($b_skipPayment) continue;
        
        //Se il rimanente da pagare è inferiore a "Importo minimo per iscrizione a ruolo" salta l'elaborazione
        if(($cls_pagamenti->getFee() - $cls_pagamenti->getPayed()) < $amountLimit){
            continue;
        }
        //Determina in presenza di un sollecito se l'importo pagato è uguale o supera sanzione+spese notifica
        $b_PayedReminder = $cls_pagamenti->hasReminder() && ($cls_pagamenti->getPayed() >= ($cls_pagamenti->getLastReminderTotalAmount() - $cls_pagamenti->getLastReminderSurcharge()));
        
        //"Posizioni che hanno pagato l'importo del sollecito"
        if(($Search_Payed == 0 && $b_PayedReminder) || ($Search_Payed == 2 && !$b_PayedReminder)){
            continue;
        }

        //SE PRESENTE RICORSO SALTO LA POSIZIONE
        //TODO GESTIONE RICORSI CON VERBALI MULTIPLI DA AGGREGARE NELL'ESTRAZIONE
        if($r_Injunction['DisputeId']>0){
            //Parte stato ricorsi
            $disputeView = new CLS_VIEW(MGMT_DISPUTE);
            $rs_FineDispute= $rs->selectQuery($disputeView->generateSelect("F.Id=".$r_Injunction['FineId'],null, "GradeTypeId DESC",1));
            $r_FineDispute = $rs->getArrayLine($rs_FineDispute);
            
                $cls_dispute = new cls_dispute();
                $cls_dispute->setDispute($r_FineDispute,1);
                $disputeStatus = $cls_dispute->a_info['responseCode'];
                
                if(($disputeStatus >= 1 && $disputeStatus <= 4) || ($disputeStatus == 6)){ //Ricorso in attesa, rinviato, sospeso o accolto
                    continue;   //Salta l'elaborazione
                }
        }
        
        //VERIFICA RATEIZZAZIONI
        // fase 1 saltiamo quelli che hanno rateizzazioni aperte
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
            
            if($n_ExpiredRates < $r_Customer['RoleMinExpiredInstallments']) continue;
        }
        
        //VERIFICA ANAGRAFICA
        // qui si applicano le funzioni di Dario ai dati del trasgressore se è tirato su dalla vista con TrespasserId
        $a_TrespasserAnomalies = manageAnomalies($r_Injunction, $r_Injunction['TrespasserCountryId'] == 'Z000' ? 'N' : 'F');
        if(checkAnomalyExistence($a_TrespasserAnomalies, true)){
            continue;
        }
        
        //VERIFICA RATEIZAZIONI
        // fase 1 saltiamo quelli che hanno rateizzazioni aperte
        // fase 2 se esiste rateizzazione per ogni rata si cerca se c'è un pagamento per quel fineId con quel numero di rata o con il quinto campo del numero di rata se ce l'ha
        // e se ci sono almeno due rate scadute o 
        
        //VERIFICA ANAGRAFICA
        // qui si applicano le funzioni di Dario ai dati del trasgressore se è tirato su dalla vista con TrespasserId
        
        $r_Injunction['InfoCartella'] = "V. Cron. ".$r_Injunction['ProtocolId']."/".$r_Injunction['ProtocolYear']." del ";
        $r_Injunction['InfoCartella'].= DateOutDB($r_Injunction['FineDate']). " Targa ".$r_Injunction['VehiclePlate']." Not. il ";
        
        //VALUTAZIONE PRESCRIZIONE
        //L'estero può essere selezionato anche senza notifica
        if($r_Injunction['NotificationDate'] != NULL){
            
            $r_Injunction['InfoCartella'].= DateOutDB($r_Injunction['NotificationDate']);

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
                continue;
            }
        }
        
        //SE PRESENTI PAGAMENTI CHE COPRONO TUTTO SALTO IL RICORSO
        //Controlla lo stato dei pagamenti associati al verbale
        //Se lo status è tra 3 (pagato pari) e 4 (pagato in eccesso) salta l'elaborazione
        if(($cls_pagamenti->getStatus()) == 3 || ($cls_pagamenti->getStatus()) == 4){
            continue;
            }

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
        
        //L'estero può essere selezionato anche senza notifica
        if($r_Injunction['NotificationDate'] != NULL){
            $DT_InterestDate = new DateTime($r_Injunction['NotificationDate']);
            $DT_InterestDate->modify('+'.FINE_DAY_LIMIT.' days');
            $r_Injunction['InterestDate'] = $DT_InterestDate->format('Y-m-d');
            }
        
        $cls_290->N2($r_Injunction);

        //TODO vecchio codice prima dell'uso di cls_pagamenti, rimuovere se non ci sono retrogressioni
//         $paymentQuery  =    "SELECT FineId, SUM(Amount) Amount, SUM(Fee) Fee, SUM(ResearchFee) ResearchFee, SUM(NotificationFee) NotificationFee,
//                             SUM(CanFee) CanFee, SUM(CadFee) CadFee, SUM(CustomerFee) CustomerFee, SUM(OfficeNotificationFee) OfficeNotificationFee
//                             FROM FinePayment WHERE FineId=".$r_Injunction['FineId']." GROUP BY FineId";
//         $r_Payment = $rs->getArrayLine($rs->SelectQuery($paymentQuery));

//         $reminderQuery =    "SELECT FineId, COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee 
//                             FROM FineReminder WHERE FineId=".$r_Injunction['FineId']." AND FlowDate IS NOT NULL GROUP BY FineId";
//         $r_FineReminder = $rs->getArrayLine($rs->SelectQuery($reminderQuery));
        
//         $Fee = $r_Injunction['Fee'];
//         $HalfMaxFee = $r_Injunction['MaxFee'] * FINE_MAX;
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

//         $a_splitPayment = array(
//             "Fee" => $r_Payment['Fee'],
//             "ResearchFee" => $r_Payment['ResearchFee'],
//             "NotificationFee" => $r_Payment['NotificationFee'] + $r_Payment['CanFee'] + $r_Payment['CadFee'] + $r_Payment['CustomerFee'] + $r_Payment['OfficeNotificationFee'],
//         );
        
        $TotalPayed = $a_amounts['Payment'];
        $cod5242 = 0;
        $cod5354 = 0;
        $cod5243 = 0;

        $internal = false;
        if($internal){
            $a_N4 = array(
                "5242"=>$a_amounts['Fee'],
                "S_01"=>$a_amounts['ResearchFee'],
                "S_02"=>$a_amounts['Payment'],
                "S_05"=>$a_amounts['Notification']
            );
        }
        else{
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

            $a_N4 = array(
                "5242" => $cod5242,
                "5354" => $cod5354,
                "5243" => $cod5243
            );
        }

        foreach ($a_N4 as $code => $amount) {
            if($amount>0){
                if($code=="S_02"){
                    //$cls_290->N4($code,$amount, $a_splitPayment);
                } else {
                    $cls_290->N4($code,$amount);
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        

        if($ultimate){
			//MK 20241118 da rev 10144
                $PagoPACode1 = $r_Injunction['PagoPA1'];
                $PagoPACode2 = $r_Injunction['PagoPA2'];
                $b_PagoPAFail1 = $b_PagoPAFail2 = false;
                
                if(!empty($PagoPACode1)){
                    if(!deletePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $PagoPACode1, $r_Injunction['FineId'], $TrespasserType, $trespasser, $TrespasserCode, $Causale, $a_PagoPAServiceParams[$GenreParemeter], $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                        $b_PagoPAFail1 = true;
                    }
                    $a_I_PagoPAToCancel[] = array(
                        array('field'=>'ServiceId','selector'=>'value','type'=>'int','value'=>$pagopaService['Id'],'settype'=>'int'),
                        array('field'=>'PagoPA','selector'=>'value','type'=>'str','value'=>$PagoPACode1),
                        array('field'=>'Cancelled','selector'=>'value','type'=>'str','value'=>$b_PagoPAFail1 ? 'N' : 'S'),
                    );
                }
                
                if(!empty($PagoPACode2)){
                    if(!deletePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $PagoPACode2, $r_Injunction['FineId'], $TrespasserType, $trespasser, $TrespasserCode, $Causale, $a_PagoPAServiceParams[$GenreParemeter], $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                        $b_PagoPAFail2 = true;
                    }
                    $a_I_PagoPAToCancel[] = array(
                        array('field'=>'ServiceId','selector'=>'value','type'=>'int','value'=>$pagopaService['Id'],'settype'=>'int'),
                        array('field'=>'PagoPA','selector'=>'value','type'=>'str','value'=>$PagoPACode2),
                        array('field'=>'Cancelled','selector'=>'value','type'=>'str','value'=>$b_PagoPAFail2 ? 'N' : 'S'),
                    );
                }
                if(!empty($a_PaymentRateNumber)){
                    foreach($a_PaymentRateNumber as $r_PaymentRateNumber){
                        if(!empty($r_PaymentRateNumber['PagoPAIUV'])){
                            $b_deletedIUV = deletePagoPA(PAGOPA_PREFIX_INSTALLMENT, $pagopaService, $r_PaymentRateNumber['PagoPAIUV'], $r_PaymentRateNumber['PaymentRateId'], $TrespasserType, $trespasser, $TrespasserCode, $Causale, $a_PagoPAServiceParams[$GenreParemeter], $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'], $r_PaymentRateNumber['RateNumber']);
                            
                            $a_I_PagoPAToCancel[] = array(
                                array('field'=>'ServiceId','selector'=>'value','type'=>'int','value'=>$pagopaService['Id'],'settype'=>'int'),
                                array('field'=>'PagoPA','selector'=>'value','type'=>'str','value'=>$r_PaymentRateNumber['PagoPAIUV']),
                                array('field'=>'Cancelled','selector'=>'value','type'=>'str','value'=>$b_deletedIUV ? 'S' : 'N'),
                            );
                        }
                    }
                }
			//MK 20241118 fine
            //Il FileName viene salvato in colonne diverse perchè un domani potrebbe rendersi necessario diversificarlo
            $a_FineInjunction = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_Injunction['FineId'],'settype'=>'int'),
                array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$FileName),
                array('field'=>'FileNamePDF','selector'=>'value','type'=>'str','value'=>$pdfFileName),
                array('field'=>'FileNameXLS','selector'=>'value','type'=>'str','value'=>$xlsFileName),
                array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>$CurrentDate),
                array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>$CurrentTime),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
            );
            $rs->Insert('FineInjunction',$a_FineInjunction);
            
            $a_Fine = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>40),
            );
            $rs->Update('Fine', $a_Fine, 'Id='.$r_Injunction['FineId']);
            //L'estero potrebbe non avere notifica
            if($r_Injunction['InjunctionProcedure'] != NULL){
                $a_FineNotification = array(
                    array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                );
                $rs->Update('FineNotification',$a_FineNotification, "FineId={$r_Injunction['FineId']}");
                }
            
            $a_PaymentRate = array(
                array('field'=>'StatusRateId','selector'=>'value','type'=>'int','value'=>RATEIZZAZIONE_CHIUSA,'settype'=>'int'),
                array('field'=>'ClosingDate','selector'=>'value','type'=>'date','value'=>$CurrentDate)
            );
            $rs->Update('PaymentRate',$a_PaymentRate ,"FineId={$r_Injunction['FineId']} AND StatusRateId=".RATEIZZAZIONE_APERTA);
        
            $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
        }
    }

    $cls_290->N5();
    $cls_290->N9();
    $cls_290->saveFile($filePath);
    
    $rs->End_Transaction();
    
    echo $str_out;
}
?>
<script>
SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
</script>
<?php if(!$b_Error): ?>
<div class="row-fluid">
	<div class="col-sm-12 BoxRowLabel alert" style="height:auto;padding:2rem;">
		<div class="col-sm-12">
			<?php if($ultimate): ?>
    			<div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                    <div class="col-sm-11" style="font-size: 1.2rem;">
                        <ul>
                            <li>Nota bene:
                            <ul style="list-style-position: inside;">
                                <li>Per procedere alla stampa del tracciato dei ruoli è necessario inserire prima la data di invio al concessionario. Cliccare sul tasto "Inserisci date" per essere reindirizzati all'apposita pagina</li>
                            </ul>
                        </ul>
                    </div>
                </div>
        		<div class="col-sm-12 text-center">
            		<b>ESTRAZIONE DEFINITIVA</b><br><br>
        			<button class="btn btn-default" id="back">Indietro</button>
        			<form action="exp_injunction_data.php" style="display:inline">
        				<input type="hidden" name="TypePlate" value="<?=$s_TypePlate?>">
        				<input type="hidden" name="Search_FromProtocolYear" value="<?=$Search_FromProtocolYear?>">
        				<input type="hidden" name="Search_ToProtocolYear" value="<?=$Search_ToProtocolYear?>">
        				<button class="btn btn-info" type="submit">Inserisci date</button><br><br>
        			</form>
        		</div>
			<?php else: ?>
        		<div class="col-sm-12 text-center">
            		<b>ESTRAZIONE PROVVISORIA</b><br><br>
            		File di prova, <b>NON IMPORTARE!</b> Eseguire l'estrazione dei dati in modalità definitiva per aggiornare lo stato delle posizioni presenti in archivio<br><br>
        			<button class="btn btn-default" id="back">Indietro</button>
        			<a class="btn btn-info dwBtn" href="<?= $webFilePath ?>" download>Scarica file</a>
        			<a class="btn btn-danger dwBtn" href="<?= $pdfPath?>" target="_blank" download>PDF <i class="fa fa-file-pdf"></i></a>
        			<a class="btn btn-success dwBtn" href="<?= $xlsPath?>" target="_blank" download>XLS <i class="fa fa-file-excel"></i></a><br><br>
        		</div>
			<?php endif; ?>
		</div>
		<div class="col-sm-12">
			<?php $cls_290->showFile(); ?>
		</div>
	</div>
</div>
<?php endif; ?>
<script type="text/javascript">
$(document).ready(function () {
	SARIDA.nascondiCaricamento();
	
	$('#back').on('click', function(){
		window.location="<?= 'exp_injunction.php'.$str_GET_Parameter; ?>";
	});
});
//Fa diventare bianco il testo dei bottoni dedicati al download
$('.dwBtn').css("color","white");
</script>
<?php
require_once(INC."/footer.php");