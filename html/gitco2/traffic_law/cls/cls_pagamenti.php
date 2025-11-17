<?php
require_once(CLS."/cls_dispute.php");
require_once(CLS."/cls_view.php");

class cls_pagamenti{
    const NAZIONALE = "National";
    const ESTERO = "Foreign";
    
    private $FineId = 0;
    private $CityId;
    private $ProcessingDate = '';
    private $NotificationDate = '';
    private $LastPaymentDate = ''; //Data ultimo pagamento
    private $cls_db = null;
    private $r_PaymentProcedure = null;
    private $r_FineDispute = null;
    
    private $genericMessage;    //Messaggio generico
    private $errorMessage;      //Eventuali messaggi di errore
    private $disputeMessage;    //Messaggi relativi a ricorsi
    private $hasDispute;        //Se ha un ricorso
    private $hasDisputeClosed;   //Se ha un ricorso chiuso
    private $disputeAmount;   //importo fissato dal ricorso
    private $baseFee;           //Importo di base della sanzione (Es.: Per 62€ di cui 42€ importo base + 20€ spese --> 42€)
    private $fineReducedFee;        //Importo ridotto
    private $fineFee;         //Importo entro 60gg
    private $fineMaxFee;           //Importo oltre 60gg
    private $additionalFee;     //Spese (presenti nel FineHistory) relative al verbale
    private $semester;          //Semestri trascorsi dalla notifica
    private $surcharge;         //Maggiorazione semestrale totale
    private $previousReminderNotificationFeesSum;   //Ammontare delle spese di notifica dei solleciti pregressi (compreso ultimo)
    private $lastReminderNotificationFee;   //Spese di notifica del sollecito più recente
    private $lastReminderTotalNotificationFee; //Spese di notifica solleciti precedenti, presenti sull'ultimo sollecito
    private $lastReminderTotalAmount;          //Somma del dovuto del sollecito più recente
    private $lastReminderSurcharge;          //Maggiorazione del sollecito più recente
    private $isReduced;         //Articolo ridotto (booleano)
    private $hasReminder;      //Ha sollecito (booleano)
    private $fee;               //Importo dovuto calcolato finale
    private $payed;             //Somma del pagato
    private $totalFee;          //Importo forfettario attuale
    private $fineNotificationFee;       //Importo spese di notifica del verbale
    private $fineResearchFee;           //Importo spese di ricerca del verbale
    private $customerFee;       //Spese di notifica prese dal FineHistory
    private $canFee;            //Spese Cad prese dal FineHistory
    private $cadFee;            //Spese Can prese dal FineHistory
    private $notifierFee;       //Spese NotifierFee prese dal FineHistory
    private $otherFee;          //Spese OtherFee prese dal FineHistory
    private $currentNotificationFee;    //Spese di notifica attuali (vengono considerate anche le spese di sollecito)
    private $finePrefectureFee; //Spese fissate dal prefetto (per gli articoli che lo prevedono) in sede di verbale (da non confondere con quelle del ricorso)
    private $prescriptionDate;  //Data di prescrizione
    private $statusDescription; //Descrizione dello status restituito
    private $status;            //Codice di risposta (per maggiori info vedere il commento sul metodo)
    private $latePaymentStatus; //Scaglione nel quale viene rilevato il pagamento tardivo
    private $responseTime;      //Tempi di risposta    
    
    //Parametri correnti dell'ente (alla data di calcolo passata determinata dalla classe)
    private $currentCustomerNotificationFee;    //Importo spese di notifica attuale presente nelle impostazioni di ente
    private $currentCustomerReminderNotificationFee;    //Importo spese di notifica attuale del sollecito presente nelle impostazioni di ente
    private $currentCustomerResearchFee;        //Importo spese di ricerca attuale presente nelle impostazioni di ente
    
    //NOTA!!! Questi vettori al momento vengono riempiti solo se c'è almeno un pagamento e non si supera il dovuto totale
    private $cronScaglioni;
    private $cronPagamenti;

    //Costruttore
    function __construct($FineId, $CityId, $ProcessingDate = null, $cls_db = null){
        /** @var CLS_DB $db */
        if($FineId > 0 && !empty($CityId)){
            if(isset($cls_db)) $rs = $cls_db;
            else global $rs;
            
            $this->cls_db = $rs;
            $this->CityId = $CityId;
            $this->FineId = $FineId;
            
            $rs_Injunction = $rs->Select("FineInjunction","FineId = $FineId");
            $r_Injunction = $rs->getArrayLine($rs_Injunction);
            
            if(mysqli_num_rows($rs_Injunction) > 0){
                $this->ProcessingDate = DateInDB($r_Injunction['RegDate']);
            }
            else{
                $this->ProcessingDate = DateInDB($ProcessingDate) ?: date('Y-m-d');
            }
            
            $disputeView = new CLS_VIEW(MGMT_DISPUTE); //TODO 28/02/24 vedi modifica da fare alla vista per i ricorsi appena sarà possibile aprire bachi interni
            $paymentProcedureView = new CLS_VIEW(V_PAYMENTPROCEDURE_WITHOUT_NOTIFICATION_CONSTRAINT);
            
            $str_WherePaymentProcedure = "";
            
            $FineNotification = $this->cls_db->getArrayLine($this->cls_db->Select("FineNotification", "FineId=$FineId"));
            if(empty($FineNotification)){
                //Bug3370 Se è un non notificato vado ad impostare la data di oggi
                $this->NotificationDate = date('Y-m-d');
                $str_WherePaymentProcedure = " 
                    (F.StatusTypeId = 20)
                    ";
            } else {
                $this->NotificationDate = $FineNotification['NotificationDate'];
                //Bug3437 - Lo StatusTypeId = 40 serve per recuperare i verbali iscritti a ruolo
                $str_WherePaymentProcedure = " 
                    (
                        (
                            (F.StatusTypeId >= 25)
                        and (F.StatusTypeId <= 30)
                          )
                        or (F.StatusTypeId = 40)
                    )
                    and (
                      FN.NotificationDate is not null
                    ) 
                    and (
                      (FN.ResultId <= 3) 
                      or (
                        (FN.ResultId >= 4) 
                        and (FN.ResultId <= 9)
                      ) 
                      or (
                        (FN.ResultId = 21) 
                        and (FN.ValidatedAddress = 1)
                      ) 
                      or (FN.ResultId = 22)
                    )";
            }
            
            $str_WherePaymentProcedure .= " AND CityId='$CityId' AND Id=$FineId";
            $this->r_PaymentProcedure = $this->cls_db->getArrayLine($this->cls_db->selectQuery($paymentProcedureView->generateSelect($str_WherePaymentProcedure)));
            $this->r_FineDispute = $this->cls_db->getArrayLine($this->cls_db->selectQuery($disputeView->generateSelect("F.Id=".$FineId,null, "GradeTypeId DESC",1)));
            
            $this->verificaPagamentiPerScaglione($this->FineId, $this->NotificationDate, $this->ProcessingDate);
        } else {
            $this->genericMessage = "Non è possibile procedere con l'elaborazione se non è specificato l'Id del verbale e il codice ente";
            $this->statusDescription = "Errore";
            $this->status = -1;
        }
    }
    
    public function getProcessingDate()
    {
        return $this->ProcessingDate;
    }
    
    public function getGenericMessage()
    {
        return $this->genericMessage;
    }
    
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getDisputeMessage()
    {
        return $this->disputeMessage;
    }
    
    public function getDisputeAmount()
    {
        return $this->disputeAmount;
    }
    
    public function getHasDispute()
    {
        return $this->hasDispute;
    }
    
    public function getHasDisputeClosed()
    {
        return $this->hasDisputeClosed;
    }

    public function getNotificationDate()
    {
        return $this->NotificationDate;
    }
    
    public function getLastPaymentDate()
    {
        return $this->LastPaymentDate;
    }
    
    public function getBaseFee()
    {
        return $this->baseFee;
    }

    public function getFineReducedFee()
    {
        return $this->fineReducedFee;
    }
    
    public function getFineFee()
    {
        return $this->fineFee;
    }
    
    public function getFineMaxFee()
    {
        return $this->fineMaxFee;
    }
    
    public function getAdditionalFee()
    {
        return $this->additionalFee;
    }

    public function getSemester()
    {
        return $this->semester;
    }

    public function getSurcharge()
    {
        return $this->surcharge;
    }

    public function getPreviousReminderNotificationFeesSum()
    {
        return $this->previousReminderNotificationFeesSum;
    }

    public function getLastReminderNotificationFee()
    {
        return $this->lastReminderNotificationFee;
    }
    
    public function getLastReminderTotalNotificationFee()
    {
        return $this->lastReminderTotalNotificationFee;
    }
    
    public function getLastReminderTotalAmount()
    {
        return $this->lastReminderTotalAmount;
    }
    
    public function getLastReminderSurcharge()
    {
        return $this->lastReminderSurcharge;
    }
    
    public function isReduced()
    {
        return $this->isReduced;
    }
    
    public function hasReminder()
    {
        return $this->hasReminder;
    }
    
    public function getFee()
    {
        return $this->fee;
    }

    public function getPayed()
    {
        return $this->payed;
    }

    public function getTotalFee()
    {
        return $this->totalFee;
    }

    public function getCurrentCustomerNotificationFee()
    {
        return $this->currentCustomerNotificationFee;
    }

    public function getCurrentCustomerResearchFee()
    {
        return $this->currentCustomerResearchFee;
    }
    
    public function getCurrentCustomerReminderNotificationFee()
    {
        return $this->currentCustomerReminderNotificationFee;
    }

    public function getFineNotificationFee()
    {
        return $this->fineNotificationFee;
    }
    
    public function getFineResearchFee()
    {
        return $this->fineResearchFee;
    }
    
    public function getCustomerFee()
    {
        return $this->customerFee;
    }

    public function getCanFee()
    {
        return $this->canFee;
    }

    public function getCadFee()
    {
        return $this->cadFee;
    }

    public function getNotifierFee()
    {
        return $this->notifierFee;
    }

    public function getOtherFee()
    {
        return $this->otherFee;
    }

    public function getCurrentNotificationFee()
    {
        return $this->currentNotificationFee;
    }
    
    public function getCronScaglioni(){
        return $this->cronScaglioni;
    }
    
    public function getCronPagamenti(){
        return $this->cronPagamenti;
    }
    
    public function getPrescriptionDate()
    {
        return $this->prescriptionDate;
    }
    
    public function getStatusDescription()
    {
        return $this->statusDescription;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function getLatePaymentStatus()
    {
        $this->latePaymentStatus = $this->latePaymentStatus ?: -1; 
        return $this->latePaymentStatus;
    }
    
    public function getResponseTime()
    {
        return $this->responseTime;
    }
    //ResponseTime è l'unica proprietà dotata di setter perchè dev'essere impostata per i test sulle chiamate multiple
    public function setResponseTime($time)
    {
        $this->responseTime = $time."s";
    }
    
    public function getFinePrefectureFee()
    {
        return $this->finePrefectureFee;
    }
    
    public function setFinePrefectureFee($finePrefectureFee)
    {
        $this->finePrefectureFee = $finePrefectureFee;
    }
    
    function verificaPagamentiPerScaglione($FineId, $NotificationDate, $ProcessingDate){
        $scaglione = 1;
        $dateSignificative = array(
            //5gg
            1 => date('Y-m-d', strtotime($NotificationDate. " + ".(FINE_DAY_LIMIT_REDUCTION)." days")),
            //60gg
            2 => date('Y-m-d', strtotime($NotificationDate. " + ".(FINE_DAY_LIMIT)." days")),
        );
        
        $dataCalcolo = null;
        
        do{
            if(!empty($dateSignificative[$scaglione])){
                $dataCalcolo = $dateSignificative[$scaglione];
            } else {
                $dataCalcolo = date('Y-m-d', strtotime($NotificationDate. " + ".(FINE_DAY_LIMIT + 1)." days"));
                $dataCalcolo = date('Y-m-d', strtotime($dataCalcolo. " + ".(FINE_MONTH_LIMIT_SEMESTRAL * ($scaglione - count($dateSignificative)))." months"));
            }
            
            if($dataCalcolo > $ProcessingDate) $dataCalcolo = $ProcessingDate;
            
            $this->recuperaStatoPagamentoVerbale($FineId, $dataCalcolo);
            $scaglione++;
        } while ($dataCalcolo < $ProcessingDate && !in_array($this->status, array(-1,3,4,7)));
    }

    /**
     * @return int Codici di risposta
     * -1 (errore)
     * 0 (non pagato),
     * 1 (parziale pagato),
     * 2 (pagato parziale per ritardo)
     * 3 (pagato pari)
     * 4 (pagato in eccesso)
     * 5 (ricorso in attesa)
     * 6 (ricorso accolto)
     * 7 (prescrizione)
     * **/
    function recuperaStatoPagamentoVerbale($FineId, $ProcessingDate){
        //ora inizio elaborazione serve solo per indicare la durata dell'elaborazione
        $ProcessingStartTime = microtime(true);
        $CityId = $this->CityId;
        
        $rs_CustomerCharge = $this->cls_db->Select("CustomerCharge","CityId = '$CityId' AND CreationType = 1 AND ((FromDate <= '$ProcessingDate' AND ToDate >= '$ProcessingDate') OR (COALESCE(FromDate, '0001-01-01') <= '$ProcessingDate' AND ToDate IS NULL))", "Id DESC", 1);
        $r_CustomerCharge = $this->cls_db->getArrayLine($rs_CustomerCharge);
        
        //Recupero le informazioni di Fine, FineTrespasser, Trespasser
        $rs_FineTrespasser = $this->cls_db->SelectQuery("SELECT T.CountryId AS TrespasserCountry FROM Fine F JOIN FineTrespasser FT ON F.Id = FT.FineId JOIN Trespasser T ON FT.TrespasserId
= T.Id WHERE F.Id = ".$FineId." AND F.CityId = '".$CityId."'");
        $r_FineTrespasser = $this->cls_db->getArrayLine($rs_FineTrespasser);
        
        //Recupero i dati dei pagamenti alla data richiesta
        $rs_FinePayment = $this->cls_db->SelectQuery("SELECT SUM(Amount) AS TotalPayed, MAX(PaymentDate) AS MaxPaymentDate FROM FinePayment WHERE FineId = $FineId AND PaymentDate <= '$ProcessingDate'");
        $r_FinePayment = $this->cls_db->getArrayLine($rs_FinePayment);
        
        //LETTURA PARAMETRI DELL'ENTE
        $rs_Customer = $this->cls_db->Select('V_Customer', "CityId='$CityId' AND CreationType = 1");
        $r_Customer = mysqli_fetch_array($rs_Customer);
        
        //recupera tutti i record dei verbali notificati in un certo stato per cui esaminare i pagamenti
        $r_PaymentProcedure = $this->r_PaymentProcedure;
        
        //Nazionalità del trasgressore
        $Nationality = $r_FineTrespasser['TrespasserCountry'] == "Z000" ? self::NAZIONALE : self::ESTERO;
        //Carattere nazionalità
        $c_Nationality = $Nationality == self::NAZIONALE ? 'N' : 'F';
        
        //SELEZIONE PARAMETRI PROCEDURA DI PAGAMENTO
        $rs_ProcessingData = $this->cls_db->Select('ProcessingDataPayment'.$Nationality, "CityId='".$CityId."'");
        $r_ProcessingData = $this->cls_db->getArrayLine($rs_ProcessingData);
        
        //SELEZIONE RICORSI
        $r_FineDispute = $this->r_FineDispute;
        $cls_dispute = new cls_dispute();
        
        //Selezione FineHistory con NotificationTypeId 6 per il recupero delle spese del verbale
        $rs_FineHistory = $this->cls_db->Select("FineHistory","FineId = $FineId AND NotificationTypeId = 6");
        $r_FineHistory = $this->cls_db->getArrayLine($rs_FineHistory);
        
        //Errore in caso non dovessero esserci dati di ProcessingData per l'ente
        if(mysqli_num_rows($rs_ProcessingData) <= 0){
            trigger_error("Non è possibile procedere con l'elaborazione se non sono stati impostati i parametri dell'ente competente. Compilare i parametri dell'ente dal menù Ente\Procedure Ente.",E_USER_WARNING);
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->genericMessage = "Non è possibile procedere con l'elaborazione se non sono stati impostati i parametri dell'ente competente. Compilare i parametri dell'ente dal menù Ente\Procedure Ente.";
            $this->statusDescription = "Errore";
            $this->status = -1;
            return;
        }
        
        if(!isset($r_Customer['PatronalFeast'] ) || empty($r_Customer['PatronalFeast']) ){
            trigger_error("Non è possibile procedere con l'elaborazione se non è stata indicata la data della festa patronale tra le configurazioni dell'ente competente.<br >Compilare il campo Festa patronale nella scheda Indirizzo del menù Ente\Gestione Ente.",E_USER_WARNING);
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->genericMessage = "Non è possibile procedere con l'elaborazione se non è stata indicata la data della festa patronale tra le configurazioni dell'ente competente.<br >Compilare il campo Festa patronale nella scheda Indirizzo del menù Ente\Gestione Ente.";
            $this->statusDescription = "Errore";
            $this->status = -1;
            return;
        }
        
        if(empty($r_PaymentProcedure)){
            trigger_error("Non sono disponibili risultati per il FineId in questione. Controllare i dati (StatusTypeId ecc...)", E_USER_WARNING);
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->genericMessage = "Non sono disponibili risultati per il FineId in questione.. Controllare i dati (StatusTypeId ecc...)";
            $this->statusDescription = "Errore";
            $this->status = -1;
            return;
        }
        
        //LETTURA PARAMETRI PROCEDURA DI PAGAMENTO
        $n_ReducedPaymentDayAccepted    = $r_ProcessingData['ReducedPaymentDayAccepted']; //gg pagamento ridotti sono i gg di tolleranza per valutare il ritardo di pagamento nel caso dei pagati ridotti
        $n_PaymentDayAccepted           = $r_ProcessingData['PaymentDayAccepted']; //gg pagamento normale  sono i gg di tolleranza per valutare il ritardo di pagamento nel caso dei pagati normali
        
        $IncludeNotificationResearch = $r_ProcessingData['IncludeNotificationResearch']; //dice se includere le spese quando la sanzione è fissata dal prefetto nel ricorso
        $ApplyPercentualOnPrefectureFee = $r_ProcessingData['ApplyPercentualOnPrefectureFee']; //dice se applicare le maggiorazioni all'importo fissato dal prefetto nel ricorso
        
        //FLAG INCLUSIONE SPESE NELLA MAGGIORAZIONE
        $IncludeNotificationResearchInSurcharge =  $r_ProcessingData['IncludeNotificationResearchInSurcharge'];
        
        $LumpSum = $r_Customer['LumpSum'];
        
        $ZoneId = $r_PaymentProcedure['ZoneId'];
        
        //VARIABILI PER SANZIONI
        $AdditionalFee = 0; //spese addizionali prese da FineHistory
        
        //Spese attualmente registrate
        $NotificationFee = 0;   //Spese di notifica alla data di calcolo
        $ResearchFee = 0;       //Spese di ricerca alla data di calcolo
        $FineNotificationFee = $r_FineHistory['NotificationFee'];   //Spese di notifica del verbale
        $FineResearchFee = $r_FineHistory['ResearchFee'];           //Spese di ricerca del verbale
        $CustomerFee = $r_FineHistory['CustomerFee'];
        $CanFee = $r_FineHistory['CanFee'];
        $CadFee = $r_FineHistory['CadFee'];
        $NotifierFee = $r_FineHistory['NotifierFee'];
        $OtherFee = $r_FineHistory['OtherFee'];
        $Forfettario = $c_Nationality == 'N' ? $r_CustomerCharge['NationalTotalFee'] : $r_CustomerCharge['ForeignTotalFee'];
        
        
        //Notifica
        if($c_Nationality=="N"):
            $NotificationFee =  $r_CustomerCharge['NationalNotificationFee'];
            $CurrentReminderNotificationFee = $r_Customer['NationalReminderNotificationFee'];
        else:
            $rs_PostalCharge = $this->cls_db->Select("PostalCharge","CityId = '$CityId' AND ((FromDate <= '$ProcessingDate' AND ToDate >= '$ProcessingDate') OR (FromDate <= '$ProcessingDate' AND ToDate IS NULL))", "Id DESC", 1);
            $r_PostalCharge = $this->cls_db->getArrayLine($rs_PostalCharge);
            $str_PostalZone = "Zone".$ZoneId;
            $NotificationFee = $r_PostalCharge[$str_PostalZone];
            $CurrentReminderNotificationFee = $r_PostalCharge['Reminder'.$str_PostalZone];
        endif;
        
        //Ricerca. Se presente forfettario gli dà la precedenza
        if($Forfettario != 0):
            $ResearchFee = ($Forfettario - $NotificationFee);
        else:
            $ResearchFee = ($c_Nationality == 'N' ? $r_CustomerCharge['NationalResearchFee'] : $r_CustomerCharge['ForeignResearchFee']);
        endif;
        
        $IsReduced = false; //indica se la sanzione prevede tariffa ridotta sull'articolo principale (non teniamo conto degli altri articoli)
        $this->isReduced = false;
        
        $DisputeFee = 0;    //sanzione amministrativa fissata da autorità giudiziaria
        $PrefectureFee = 0; //sanzioni fissate da prefettura per determinati articoli
        
        $Fee = 0;           //sanzione amministrativa complessiva per tutti gli articoli
        $MaxFee = 0;        //totale massimo edittale
        $ReducedFee = 0;    //totale spese ridotte del 30% se pagate entro 5 gg quando previsto
        
        //FIXME vedere se servono, non usate
        $HalfMaxFee = 0;    //Metà del totale massimo edittale
        
        $TotalFee = 0;           //sanzione amministrativa complessiva per tutti gli articoli + spese addizionali
        $TotalMaxFee = 0;        //totale massimo edittale + spese addizionali
        $TotalReducedFee = 0;    //totale spese ridotte
        
        //Percentuale maggiorazione semestrale
        $n_PercentualeMaggiorazione = $c_Nationality == 'N' ? $r_Customer['NationalPercentualReminder'] : $r_Customer['ForeignPercentualReminder'];
        
        //Semestri passati dai 60gg
        $n_Semester = 0;
        
        // data di notifica
        $NotificationDate = $r_PaymentProcedure['NotificationDate'];
        
        //Se è presente un ricorso
        $b_dispute = false; // indica che c'era un ricorso
        
        //durata sospensiva ricorso
        //TODO integrare giorni di attesa nel calcolo degli scaglioni
        $n_DisputeDay = 0;
        
        //Parte stato ricorsi
        if(!empty($r_FineDispute)){
            $this->hasDispute = 1;
            $cls_dispute->setDispute($r_FineDispute,1);
            $disputeStatus = $cls_dispute->a_info['responseCode'];
            
            if($disputeStatus >= 1 && $disputeStatus <= 4):
                $b_dispute = true;
                $this->genericMessage = "Calcolo sospeso a causa di ricorso pendente";
                $this->disputeMessage = "Ricorso in attesa, rinviato o sospeso";
                $this->status = 5;
                $this->calcolaTempiElaborazione($ProcessingStartTime);
                return;
            elseif($disputeStatus == 5):
                $this->disputeMessage = "Ricorso respinto o inammissibile";
                $b_dispute = true;
            elseif($disputeStatus == 6):
                $n_DayFromMerit = 0;
                if(!empty($r_FineDispute['DateMerit']))
                    $n_DayFromMerit = DateDiff("D", $r_FineDispute['DateMerit'], $ProcessingDate)+1;
                $b_dispute = true;
                if ($n_DayFromMerit>DATE_FROM_MERIT):
                    $this->genericMessage = "Calcolo bloccato a causa di ricorso accolto";
                    $this->disputeMessage = "Ricorso accolto";
                    $this->status = 6;
                    $this->hasDisputeClosed = 1;
                    $this->calcolaTempiElaborazione($ProcessingStartTime);
                    return;
                else:
                    $this->genericMessage = "Calcolo sospeso a causa di ricorso accolto ma 215gg non ancora trascorsi";
                    $this->disputeMessage = "Ricorso in attesa";
                    $this->status = 5;
                    $this->calcolaTempiElaborazione($ProcessingStartTime);
                    return;
                endif;
            endif;
        }
        
        //VALUTAZIONE PRESCRIZIONE
        //Se la data di notifica avanti di 5 anni (5 anni + 270 gg per estero) + i giorni di ricorso
        // + shift per festività è < $ProcessingDate siamo in prescrizione
        $PrescriptionDateOriginale = date('Y-m-d', strtotime($NotificationDate. ' + '. PRESCRIPTION_YEARS));
        if($Nationality==self::ESTERO){
            // per l'estero la prescrizione va valutata su 5 anni + 270 gg
            $PrescriptionDateOriginale = date('Y-m-d', strtotime($PrescriptionDateOriginale. ' + '. PRESCRIPTION_FOREIGN_DAYS));
        }
            
        //valutazione prescrizione covid
        $PrescriptionDateTemp = AggiornaPrescizionePerSospensioneCovid($PrescriptionDateOriginale, $NotificationDate, DeterminaDataNotificaMinima(D_COVID_I, $Nationality), D_COVID_F);
        
        $PrescriptionDateTemp = date('Y-m-d', strtotime($PrescriptionDateTemp. ' + '. "$n_DisputeDay days"));
        $PrescriptionDate = SkipFestiveDays($PrescriptionDateTemp, $this->CityId, $this->cls_db); //passata alla funzione che sposta al giorno successivo se c'è festività
        trigger_error("DataPrescrizione --> $PrescriptionDate",E_USER_NOTICE);
        trigger_error("DataElaborazine --> $ProcessingDate",E_USER_NOTICE);
        
        if($ProcessingDate > $PrescriptionDate)
          trigger_error("PRESCRITTO",E_USER_NOTICE);
        else
          trigger_error("CONTINUO CALCOLO",E_USER_NOTICE);
            
        if($ProcessingDate > $PrescriptionDate) {
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->genericMessage = "Sono trascorsi i termini di prescizione alla data ".DateOutDB($PrescriptionDate);
            $this->statusDescription = "Prescritto";
            $this->prescriptionDate = $PrescriptionDate;
            $this->status = 7;
            return; //Prescrizione
        }
        
        //DETERMINAZIONE SANZIONE DOVUTA
        $AdditionalFee = $r_PaymentProcedure['AdditionalFee'.$LumpSum];
        
        //CASO SPESE FISSATE NEL RICORSO
        if ($b_dispute && isset($r_FineDispute['Amount'])) {
            if (isset($r_FineDispute['Amount']) && $r_FineDispute['Amount']>0) {
                $DisputeFee = $r_FineDispute['Amount'];
                $this->disputeAmount = $DisputeFee;
                if ($IncludeNotificationResearch){
                    $DisputeFee += $AdditionalFee;
                } else {
                    $AdditionalFee = 0;
                }
            }
            else{
                $this->disputeAmount = 0;
            }
        } else {
            //SPESE CALCOLATE DA ARTICOLO PRINCIPALE
            if ($r_PaymentProcedure['PrefectureFixed'] && ($r_PaymentProcedure['PrefectureFee'] > 0)) { //Spese fissate dalla prefettura
                $PrefectureFee = $r_PaymentProcedure['PrefectureFee'];
            }else {
                //prese da FineArticle
                $Fee = $r_PaymentProcedure['Fee'];
                $MaxFee = $r_PaymentProcedure['MaxFee'];
                
                //$r_Customer['ReminderAdditionalFee'] è il flag "Mantieni la sanzione al minimo edittale" fissato dall'ente che vale per tutti gli atti
                //$r_PaymentProcedure['ReminderAdditionalFeeProcedure'] è il flag "Mantieni la sanzione al minimo edittale" fissato per uno specifico verbale in Ulteriori dati e prevale su quello dell'ente
                //per cui se è diverso dal primo ne sovrascrive il comportamento
                $MaxFee = ($r_Customer['ReminderAdditionalFee']==0) ? ($r_PaymentProcedure['MaxFee']*FINE_MAX): $r_PaymentProcedure['Fee'];
                if ($r_Customer['ReminderAdditionalFee'] != $r_PaymentProcedure['ReminderAdditionalFeeProcedure']) {
                    $MaxFee = ($r_PaymentProcedure['ReminderAdditionalFeeProcedure']==0) ? ($r_PaymentProcedure['MaxFee']*FINE_MAX): $r_PaymentProcedure['Fee'];
                }
                
                $ReducedFee = $r_PaymentProcedure['Fee']; //se non è abilitato al pagamento ridotto paga tutta la sanzione minima
                if ($r_PaymentProcedure['ReducedPayment']) {
                    $IsReduced = true;
                    $this->isReduced = true;
                    $ReducedFee = ($r_PaymentProcedure['Fee'] * FINE_PARTIAL);
                }
                else {
                    $IsReduced = false;
                    $this->isReduced = false;
                }
                
                if (ELABORA_MAGGIORAZIONE_NOTTURNA) {
                    //GESTIONE SANZIONI NOTTURNO ED ECCEDENZA MASSA
                    //secondo il prontuario prima si applica il terzo della nutturna che rientra nei casi generali e poi si applica
                    // il raddoppio per i veicoli particolari (Pag. 573-4 edizione agosto 2018)
                    if($r_PaymentProcedure['AdditionalNight'])
                    {
                        $FineTime = TimeOutDB($r_PaymentProcedure['FineTime']);
                        $aTime = explode(":",$FineTime);
                        if($aTime[0]<FINE_HOUR_START_DAY || ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
                            $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                            $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);
                            $ReducedFee = $ReducedFee + round($ReducedFee/FINE_NIGHT,2);
                        }
                    }
                }
                
                if (ELABORA_MAGGIORAZIONE_MASSA_ECCESSO) {
                    if($r_PaymentProcedure['AdditionalMass'])
                    {
                        if ($r_PaymentProcedure['VehicleMass'] > MASS) {
                            $Fee = $Fee * FINE_MASS;
                            $MaxFee = $MaxFee * FINE_MASS;
                            $ReducedFee = $ReducedFee * FINE_MASS;
                        }
                    }
                }
            }
            
            //SPESE CALCOLATE DA ARTICOLI AGGIUNTIVI
            $rs_AdditionalArticle = $this->cls_db->Select('V_AdditionalArticle', "FineId=" . $r_PaymentProcedure['Id'], "ArticleOrder");
            while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)):
            
            if ($r_AdditionalArticle['PrefectureFixed']) {
                $PrefectureFee += $r_AdditionalArticle['PrefectureFee'];
            } else {
                
                $Fee += $r_AdditionalArticle['Fee'];
                $MaxFeeAdditionalArticle = 0;
                //$r_Customer['ReminderAdditionalFee'] è il flag "Mantieni la sanzione al minimo edittale" fissato dall'ente che vale per tutti gli atti
                //$r_PaymentProcedure['ReminderAdditionalFeeProcedure'] è il flag "Mantieni la sanzione al minimo edittale" fissato per uno specifico verbale in Ulteriori dati e prevale su quello dell'ente
                //per cui se è diverso dal primo ne sovrascrive il comportamento
                //TODO IL "+=" VIENE USATO SOLO PER L'ARTICOLO ADDIZIONALE. E' UN BACO??????????
                $MaxFeeAdditionalArticle = ($r_Customer['ReminderAdditionalFee']==0) ? ($r_AdditionalArticle['MaxFee']*FINE_MAX): $r_AdditionalArticle['Fee'];
                if ($r_Customer['ReminderAdditionalFee'] != $r_PaymentProcedure['ReminderAdditionalFeeProcedure']) {
                    $MaxFeeAdditionalArticle = ($r_PaymentProcedure['ReminderAdditionalFeeProcedure']==0) ? ($r_AdditionalArticle['MaxFee']*FINE_MAX) : $r_AdditionalArticle['Fee'];
                }
                $MaxFee += $MaxFeeAdditionalArticle;
                
                
                if ($r_AdditionalArticle['ReducedPayment'] == 1) {
                    $ReducedFee += $r_AdditionalArticle['Fee'] * FINE_PARTIAL;
                } else {
                    $ReducedFee += $r_AdditionalArticle['Fee'];//se non è abilitato al pagamento ridotto paga tutta la sanzione minima
                }
                
                if (ELABORA_MAGGIORAZIONE_NOTTURNA) {
                    //GESTIONE SANZIONI NOTTURNO ED ECCEDENZA MASSA
                    //secondo il prontuario prima si applica il terzo della nutturna che rientra nei casi generali e poi si applica
                    // il raddoppio per i veicoli particolari (Pag. 573-4 edizione agosto 2018)
                    if($r_AdditionalArticle['AdditionalNight'])
                    {
                        $FineTime = TimeOutDB($r_PaymentProcedure['FineTime']);
                        $aTime = explode(":",$FineTime);
                        if($aTime[0]<FINE_HOUR_START_DAY || ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
                            $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                            $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);
                            $ReducedFee = $ReducedFee + round($ReducedFee/FINE_NIGHT,2);
                        }
                    }
                }
                if (ELABORA_MAGGIORAZIONE_MASSA_ECCESSO) {
                    if($r_AdditionalArticle['AdditionalMass'])
                    {
                        if ($r_PaymentProcedure['VehicleMass'] > MASS) {
                            $Fee = $Fee * FINE_MASS;
                            $MaxFee = $MaxFee * FINE_MASS;
                            $ReducedFee = $ReducedFee * FINE_MASS;
                        }
                    }
                }
            }
            endwhile;
            
            $ReducedFee = number_format($ReducedFee, 2,'.','');
            $Fee = number_format($Fee, 2,'.','');
            $MaxFee = number_format($MaxFee, 2,'.','');
            
            $this->fineReducedFee = $ReducedFee;
            $this->fineFee = $Fee;
            $this->fineMaxFee = $MaxFee;
            
            //se $IncludeNotificationResearchInSurcharge 
            //è accesso allora aggiungo additionalFee all'imponibile della maggiorazione semestrale TotalXXXFee
            
            //FIXME vedere se servono, non usate
            $HalfMaxFee = ((number_format($r_PaymentProcedure['MaxFee'],2,'.','')*number_format(FINE_MAX,2,'.','')) + number_format($PrefectureFee,2,'.',''));
            
            $TotalFee = $Fee + $PrefectureFee + ($IncludeNotificationResearchInSurcharge ? $AdditionalFee : 0);
            $TotalMaxFee = $MaxFee + $PrefectureFee + ($IncludeNotificationResearchInSurcharge ? $AdditionalFee : 0);
            $TotalReducedFee = $ReducedFee + $PrefectureFee + ($IncludeNotificationResearchInSurcharge ? $AdditionalFee : 0);
        }
        
        //Calcolo sanzione corrente (senza maggiorazioni)
        //Giorni passati dalla notifica
        $giorniTrascorsi = date_diff(date_create($ProcessingDate),date_create($NotificationDate))->format('%a');
        //Es.: Ridotto 5+10gg il normale deve essere considerato a partire dal 16° giorno e così via
        //Qui non è così perchè vengono valorizzate le variabili con i giorni secchi ma bisogna tenerne conto nelle if
        $reducedDiff = (FINE_DAY_LIMIT_REDUCTION+$n_ReducedPaymentDayAccepted);
        $normalDiff = (FINE_DAY_LIMIT+$n_PaymentDayAccepted);
        
        $CurrentFee = 0;
        
        //Impostazione messaggio ed importo base
        //Se sono presenti spese fissate nel ricorso
        if($DisputeFee > 0){
            $CurrentFee = $DisputeFee;
            $this->genericMessage = "La sanzione è senza maggiorazione e la base è stabilita manualmente nel ricorso";
            $this->disputeMessage = "Spese fissate nel ricorso";
            
            //Importo di base della sanzione senza maggiorazioni
            if(!$IncludeNotificationResearch){
                $this->baseFee = number_format(($CurrentFee),2,'.','');
            } else {
                $this->baseFee = number_format(($CurrentFee-$AdditionalFee),2,'.','');
            }
        } else {
            //Se il ridotto è abilitato e siamo entro i 5gg + tolleranza
            if($IsReduced && $giorniTrascorsi <= $reducedDiff){
                $CurrentFee = $TotalReducedFee;
                $this->genericMessage = "La sanzione è relativa ad un importo ridotto del 30%";
            //Se il ridotto è disabilitato o comunque siamo oltre il ridotto ed entro 60gg + tolleranza
            } elseif($giorniTrascorsi <= $normalDiff) {
                $CurrentFee = $TotalFee;
                $this->genericMessage = "La sanzione è relativa ad un importo entro 60gg";
            //Oltre 60gg + tolleranza
            } else {
                $CurrentFee = $TotalMaxFee;
                $this->genericMessage = "La sanzione è relativa ad un importo oltre i 60gg";
            }
            
            //Importo di base della sanzione senza maggiorazioni
            $this->baseFee = number_format(($CurrentFee),2,'.','');
        }          
            

        
        
        //Calcolo maggiorazioni semestrali
        
        //RECUPERO SOLLECITI PREGRESSI
        
        //se ci sono già dei solleciti li recupero per conteggiare le spese di invio
        $str_where = "1=1 ";
        $str_where .= " AND FR.FlowDate IS NOT NULL AND F.ReminderDate IS NOT NULL AND F.ProtocolId>0 AND F.CityId='{$this->CityId}' AND FR.SendDate <= '$ProcessingDate'";
        $str_where .= " AND F.Id=".$FineId;
        //$str_where .= " AND FineReminderId = (SELECT MAX(Id) FROM FineReminder WHERE FineId = $FineId)";
        
        //selezione criterio ordinamento -
        $strOrder = "FineReminderId ASC";
        $rs_FineReminder = $this->cls_db->SelectQuery(
            "SELECT
                FR.Id AS FineReminderId,
                FR.PrintDate,
                FR.TotalAmount as TotalAmount,
                FR.NotificationFee,
                FR.TotalNotification,
                FR.PercentualAmount,
                FR.FlowDate
            
                FROM Fine F
                JOIN FineReminder FR ON FR.FineId = F.Id
                WHERE $str_where ORDER BY $strOrder"
            );
        
        $this->hasReminder = mysqli_num_rows($rs_FineReminder) > 0;
        
        $ReminderNotificationAmount = 0;
        while($r_FineReminder = $this->cls_db->getArrayLine($rs_FineReminder)){
            $ReminderNotificationAmount += $r_FineReminder['NotificationFee'];
            $this->lastReminderNotificationFee = $r_FineReminder['NotificationFee'];
            $this->lastReminderTotalNotificationFee = $r_FineReminder['TotalNotification'];
            $this->lastReminderTotalAmount = $r_FineReminder['TotalAmount'];
            $this->lastReminderSurcharge = $r_FineReminder['PercentualAmount'];
            }
        $FinalTotalSurcharge = 0;
        $n_Semester = 0;
        //Se la distanza tra la notifica + 60gg e la Processing date è > 6 mesi allora si inizia il calcolo delle maggiorazioni
        //In questo blocco viene calcolata la maggiorazione alla ProcessingDate
        $dataNot60gg = date('Y-m-d', strtotime($NotificationDate. " + ".(FINE_DAY_LIMIT + 1)." days"));
        $dataInizioMaggiorazioni = date('Y-m-d', strtotime($dataNot60gg. " + ".FINE_MONTH_LIMIT_SEMESTRAL." months"));
        if($ProcessingDate > $dataInizioMaggiorazioni){
            //Se estero prende le spese di notifica del campo "ReminderZoneX"
            if($c_Nationality != 'N'){
                $str_PostalZone = "ReminderZone".$ZoneId;
                $NotificationFee = $r_PostalCharge[$str_PostalZone];
            }
            //Numero di semestri alla data considerata (a partire dal 60° giorno dalla data di notifica)
            $ProcessingDatePerCalcoloSemestri = AnticipaDataElaborazionePerSospensioneCovid($ProcessingDate, $NotificationDate, DeterminaDataNotificaMinima(D_COVID_I, $Nationality), D_COVID_F );
            $n_Semester = intdiv($this->calcolaMesi($ProcessingDatePerCalcoloSemestri, $dataNot60gg), FINE_MONTH_LIMIT_SEMESTRAL);
            //Per ogni semestre si calcola la maggiorazione a partire dall'importo base + spese
            //Si somma poi la maggiorazione all'importo base + spese e gli si aggiungono i costi di notifica dei solleciti emessi fino alla ProcessingDate
            //Caso spese fissate nel ricorso
            if($DisputeFee > 0){
                
                    //Ciclo i semestri per calcolare la maggiorazione
                    for($i = 0; $i < $n_Semester; $i++){
                        $dataLimite = date('Y-m-d', strtotime($dataNot60gg. " + ".(FINE_MONTH_LIMIT_SEMESTRAL * ($i+1))." months"));
                        
                        $a_CronologiaPagamenti = $this->recuperaCronologiaPagamenti($FineId, SkipFestiveDays($dataLimite, $this->CityId, $this->cls_db));
                        $totPagamenti = array_sum($a_CronologiaPagamenti);
                    
                        //Serve ad aggiungere la maggiorazione SOLO se c'è del residuo da pagare
                        if($CurrentFee > $totPagamenti){
                            $FinalTotalSurcharge += (($DisputeFee-$totPagamenti) * ($n_PercentualeMaggiorazione))/100;
                        }
                        
                        //se $FinalTotalSurcharge < MetàMinEdittaleX3/5 continuo a sommare, altrimenti imposto il valore uguale a  MetàMinEdittaleX3/5
                        // ma continuo il cliclo per aver i semestri
                        if ($FinalTotalSurcharge > $DisputeFee*LIMITE_MAGGIORAZIONE)
                            $FinalTotalSurcharge = $DisputeFee*LIMITE_MAGGIORAZIONE;
                    }
                    //Le maggiorazioni con spese fissate sul ricorso vengono applicate solo se specificato nelle impostazioni dell'ente
                    if($ApplyPercentualOnPrefectureFee){
                        //Aggiungo alla Fee la maggiorazione finale
                        $CurrentFee = ($DisputeFee + $FinalTotalSurcharge);
                        }
                    $this->genericMessage = "La sanzione è maggiorata e la base è stabilita manualmente nel ricorso";
                    $this->disputeMessage = "Spese fissate nel ricorso";
                    if(!$IncludeNotificationResearch){
                        $this->baseFee = number_format(($DisputeFee),2,'.','');
                    } else {
                        $this->baseFee = number_format(($DisputeFee-$AdditionalFee),2,'.','');
                    }
                    $this->finePrefectureFee = 0;
            //Caso spese ricavate dagli articoli
            } else {
                //Ciclo i semestri per calcolare la maggiorazione
                for($i = 0; $i < $n_Semester; $i++){
                    $dataLimite = date('Y-m-d', strtotime($dataNot60gg. " + ".(FINE_MONTH_LIMIT_SEMESTRAL * ($i+1))." months"));
                    
                    $a_CronologiaPagamenti = $this->recuperaCronologiaPagamenti($FineId, SkipFestiveDays($dataLimite, $this->CityId, $this->cls_db));
                    $totPagamenti = array_sum($a_CronologiaPagamenti);
                    
                    //Serve ad aggiungere la maggiorazione SOLO se c'è del residuo da pagare
                    if($CurrentFee > $totPagamenti){
                        //Bug3321 questa condizione serve per evitare maggiorazioni negative
                        $TaxableFee = ($CurrentFee - $totPagamenti) >= 0 ? ($CurrentFee - $totPagamenti) : 0;
                        $FinalTotalSurcharge += ($TaxableFee * ($n_PercentualeMaggiorazione))/100;
                    }
                    
                    //nota: con una legge è stato fissato che la somma delle maggiorazioni semestrali non può eccedere i 3/5 della sanzione base
                    // che è base imponibile della maggiorazione stessa
                    //se $FinalTotalSurcharge >MetàMinEdittaleX3/5 imposto il valore uguale a  MetàMinEdittaleX3/5
                    // ma continuo il cliclo per aver i semestri decorsi
                    if ($FinalTotalSurcharge > $MaxFee*LIMITE_MAGGIORAZIONE)
                        $FinalTotalSurcharge = $MaxFee*LIMITE_MAGGIORAZIONE;
                }
                //Aggiungo alla Fee la maggiorazione finale
                $CurrentFee += $FinalTotalSurcharge;
                
                //se le spese non erano incluse nell'imponibile della sanzione semestrale ora le devo aggiungere al dovuto calcolato
                if (!$IncludeNotificationResearchInSurcharge)
                    $CurrentFee += $AdditionalFee;
                
                if($PrefectureFee == 0)
                    {
                    $this->genericMessage = "La sanzione è maggiorata e la base è stabilita dagli articoli";
                    $this->finePrefectureFee = 0;
                    $this->baseFee = number_format($MaxFee,2,'.','');
                    }
                else{
                    $this->genericMessage = "La sanzione è maggiorata e la base è stabilita dal prefetto";
                    $this->baseFee = number_format($PrefectureFee,2,'.','');
                    $this->finePrefectureFee = number_format($PrefectureFee,2,'.','');
                    }
            }
            //Spese di notifica solleciti precedenti
            $CurrentFee += $ReminderNotificationAmount;
        }
        
        //Pagato totale
        $TotalPayed = $r_FinePayment['TotalPayed'] ?? 0;
        
        $this->LastPaymentDate = $r_FinePayment['MaxPaymentDate'];
        $this->additionalFee = number_format($AdditionalFee,2,'.','');
        $this->semester = $n_Semester;
        $this->surcharge = number_format($FinalTotalSurcharge,2,'.','');
        $this->previousReminderNotificationFeesSum = number_format($ReminderNotificationAmount,2,'.','');
        $this->fee = number_format($CurrentFee,2,'.','');
        $this->payed = number_format($TotalPayed,2,'.','');
        $this->totalFee = number_format($Forfettario,2,'.','');
        $this->currentCustomerNotificationFee = number_format($NotificationFee,2,'.','');
        $this->currentCustomerResearchFee = number_format($ResearchFee,2,'.','');
        $this->currentCustomerReminderNotificationFee = number_format($CurrentReminderNotificationFee,2,'.','');
        $this->fineNotificationFee = number_format($FineNotificationFee,2,'.','');
        $this->fineResearchFee = number_format($FineResearchFee,2,'.','');
        $this->customerFee = number_format($CustomerFee,2,'.','');
        $this->canFee = number_format($CanFee,2,'.','');
        $this->cadFee = number_format($CadFee,2,'.','');
        $this->notifierFee = number_format($NotifierFee,2,'.','');
        $this->otherFee = number_format($OtherFee,2,'.','');
        
        if($this->hasReminder){
            $NF = ($this->lastReminderTotalNotificationFee + $this->lastReminderNotificationFee + $OtherFee - $FineResearchFee - $CustomerFee);
            $this->currentNotificationFee = number_format($NF,2,'.','');
            }
        else{
            $this->currentNotificationFee = $this->fineNotificationFee;
            }
        
        if($this->payed == $this->fee){
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->statusDescription = "Pagato pari al dovuto";
            $this->status = 3;
        }
        //Saldato totale in eccesso
        elseif($this->payed > $this->fee){
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->statusDescription = "Pagato in eccesso";
            $this->status = 4;
        }
        //Non pagato
        elseif($this->payed == 0){
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->statusDescription = "Non pagato";
            $this->status = 0;
        }
        //Controlla se ci sono pagamenti in ritardo
        elseif($this->payed < $this->fee){
            $b_pagamentoInRitardo = $this->controllaPagamentoInRitardo($FineId, $IsReduced, $ReducedFee, $Fee, $MaxFee, $AdditionalFee, $NotificationFee, $n_Semester, $NotificationDate, $ProcessingDate, $n_PercentualeMaggiorazione);
            //Pagato parziale non in ritardo
            if(!$b_pagamentoInRitardo){                   
                $this->calcolaTempiElaborazione($ProcessingStartTime);
                $this->statusDescription = "Pagato parziale";
                $this->status = 1;
            }
            //Pagato parziale in ritardo
            else{
                $this->calcolaTempiElaborazione($ProcessingStartTime);
                $this->statusDescription = "Pagato parziale in ritardo";
                $this->status = 2;
            }
        }
        //Se si arriva fin qui significa che qualcosa è andato storto
        else {
            $this->calcolaTempiElaborazione($ProcessingStartTime);
            $this->genericMessage = "Errore generico: Arrivato alla fine della funzione senza elaborazioni significative.";
            $this->statusDescription = "Errore";
            $this->status = -1;
        }
    }
    
    //Restituisce un array con Scaglione => Importo a seconda dello scaglione in cui ci si trova nella ProcessingDate
    function recuperaCronologiaScaglioni($FineId, $IsReduced, $ReducedFee, $Fee, $MaxFee, $AdditionalFee, $CurrentCustomerNotificationFee, $n_Semester, $NotificationDate, $ProcessingDate, $n_PercentualeMaggiorazione){
        //Array che contiene i dettagli degli importi negli scaglioni in formato scaglione -> importo
        $a_CronologiaScaglioni = array();
        //Se prevede ridotto imposta il ridotto come scaglione 0 altrimenti il normale
        if($IsReduced)
            $a_CronologiaScaglioni[1] = $ReducedFee + $AdditionalFee;   //5gg
        else
            $a_CronologiaScaglioni[1] = $Fee + $AdditionalFee;
                
        $a_CronologiaScaglioni[2] = $Fee + $AdditionalFee;              //60gg
        $a_CronologiaScaglioni[3] = $MaxFee + $AdditionalFee;           //60gg+6mesi
        
        //Recupera i solleciti presenti alla data considerata e crea un array data => importo in modo da impostare le spese per i vari scaglioni
        $rs_FineReminder = $this->cls_db->SelectQuery("SELECT PrintDate, NotificationFee FROM FineReminder WHERE FineId = $FineId AND PrintDate <= '$ProcessingDate'");
        
        $n_NumeroSolleciti = 0;
        $a_Solleciti = array();
        while($r_FineReminder = $this->cls_db->getArrayLine($rs_FineReminder)):
            $n_NumeroSolleciti++;
            $a_Solleciti[$r_FineReminder['PrintDate']] = $r_FineReminder['NotificationFee'];
        endwhile;
        
        //Maggiorazioni semestrali
        if($n_Semester > 0){
            //Se si è a in questo blocco significa che si è almeno allo scaglione 3
            $scaglione = 4; //60gg+(6mesi*2) --> 1° maggiorazione
            $CurrentSurcharge = 0;
            for($i = 0; $i < $n_Semester; $i++)
            {
                //Variabile che serve per moltiplicare percentuali e giorni
                $moltiplicatore = $i+1;
                //Data da cui si parte con le maggiorazioni semestrali (Notifica+60gg+6mesi)
                $dataNot60gg = date('Y-m-d', strtotime($NotificationDate. " + ".(FINE_DAY_LIMIT + 1)." days"));
                $dataInizioMaggiorazioni = date('Y-m-d', strtotime($dataNot60gg. " + ".FINE_MONTH_LIMIT_SEMESTRAL." months"));
                //Data di inizio scaglione corrente
                $dataLimiteMinScaglione = date('Y-m-d',strtotime($dataInizioMaggiorazioni.'+'.(FINE_MONTH_LIMIT_SEMESTRAL*($moltiplicatore-1)).' months'));
                //Data di fine scaglione corrente
                $dataLimiteMaxScaglione = date('Y-m-d',strtotime($dataInizioMaggiorazioni.'+'.(FINE_MONTH_LIMIT_SEMESTRAL*$moltiplicatore).' months'));
//                 echo $dataInizioMaggiorazioni."<br>";
//                 echo $dataLimiteMinScaglione."<br>";
//                 echo $dataLimiteMaxScaglione."<br>";
                //Recupero i pagamenti presenti all'inizio dello scaglione corrente
                $listaPagamenti = $this->recuperaCronologiaPagamenti($FineId, $dataLimiteMinScaglione);
//                 debugArray($listaPagamenti);
                $pagato = 0;
                foreach($listaPagamenti as $data => $importo){
                    $pagato += $importo;
                    }
                //Calcola le spese di notifica dei solleciti emessi entro la data limite dello scaglione
                $SpeseNotificaSollecitiScaglioneCorrente = 0;
                foreach($a_Solleciti as $data => $importo){
                    if($data <= $dataLimiteMinScaglione){
                        $SpeseNotificaSollecitiScaglioneCorrente += $importo;
                        }
                    }
                
                //Bug3321 questa condizione serve per evitare maggiorazioni negative
                $TaxableFee = ($MaxFee + $AdditionalFee);
                
                //Calcolo maggiorazione
                $FinalTotalSurcharge = ($TaxableFee-$pagato) >= 0 ? ((($TaxableFee - $pagato) * ($n_PercentualeMaggiorazione))/100) : 0;
                
                //nota: con una legge è stato fissato che la somma delle maggiorazioni semestrali non può eccedere i 3/5 della sanzione base
                // che è base imponibile della maggiorazione stessa
                //se $FinalTotalSurcharge >MetàMinEdittaleX3/5 imposto il valore uguale a  MetàMinEdittaleX3/5
                // ma continuo il cliclo per aver i semestri decorsi
                if ($FinalTotalSurcharge > $MaxFee*LIMITE_MAGGIORAZIONE)
                    $FinalTotalSurcharge = $MaxFee*LIMITE_MAGGIORAZIONE;
                
                $CurrentSurcharge += $FinalTotalSurcharge;
                //Raccoglie l'importo dovuto dello scaglione corrente
                $a_CronologiaScaglioni[$scaglione] = ($TaxableFee + $CurrentSurcharge + $SpeseNotificaSollecitiScaglioneCorrente);
                //echo "Scaglione: ".$scaglione." data inizio ".$dataLimiteMinScaglione." data fine ".$dataLimiteMaxScaglione." MaxFee ".$MaxFee." AdditionalFee ".$AdditionalFee." Pagato ".$pagato." Current surcharge ".$CurrentSurcharge." dovuto ".($TaxableFee + $CurrentSurcharge + $SpeseNotificaSollecitiScaglioneCorrente)." singole voci ".$TaxableFee." --> ".$CurrentSurcharge." --> ".$SpeseNotificaSollecitiScaglioneCorrente." pagato ".$pagato."<br>";
                $scaglione++;
            }
        }
        
        $this->cronScaglioni = $a_CronologiaScaglioni;
        return $a_CronologiaScaglioni;
    }
    
    //Restituisce un Array con dataPagamento => importo
    function recuperaCronologiaPagamenti($FineId, $ProcessingDate){
        $rs_FinePayment = $this->cls_db->SelectQuery("SELECT PaymentDate, Amount FROM FinePayment WHERE FineId = $FineId AND PaymentDate <= '$ProcessingDate'");
        
        //Array che contiene tutti i pagamenti effettuati entro la data considerata in formato data -> importo
        $a_CronologiaPagamenti = array();
        
        while($r_FinePayment = $this->cls_db->getArrayLine($rs_FinePayment)){
            if(!isset($a_CronologiaPagamenti[$r_FinePayment['PaymentDate']])) $a_CronologiaPagamenti[$r_FinePayment['PaymentDate']] = 0;
            $a_CronologiaPagamenti[$r_FinePayment['PaymentDate']] += $r_FinePayment['Amount'];
        }
        
        $this->cronPagamenti = $a_CronologiaPagamenti;
        return $a_CronologiaPagamenti;
    }
    
    //Restituisce se il verbale risulta pagato in ritardo
    function controllaPagamentoInRitardo($FineId, $IsReduced, $ReducedFee, $Fee, $MaxFee, $AdditionalFee, $CurrentCustomerNotificationFee, $n_Semester, $NotificationDate, $ProcessingDate, $n_PercentualeMaggiorazione){
        //Recupero tutti gli importi dei vari scaglioni fino alla data considerata
        $a_CronologiaScaglioni = $this->recuperaCronologiaScaglioni($FineId, $IsReduced, $ReducedFee, $Fee, $MaxFee, $AdditionalFee, $CurrentCustomerNotificationFee, $n_Semester, $NotificationDate, $ProcessingDate, $n_PercentualeMaggiorazione);
        //Recupero tutti i pagamenti effettuati fino alla data considerata
        $a_CronologiaPagamenti = $this->recuperaCronologiaPagamenti($FineId, $ProcessingDate);
        //echo "Processing date: ".$ProcessingDate."<br>";
        $b_PagamentoInRitardo = false;
        $n_Pagato = 0;
        //Per ogni pagamento controllo se corrisponde ad uno degli importi degli scaglioni
        foreach($a_CronologiaPagamenti as $importoPagato){
            $n_Pagato += $importoPagato;
            foreach($a_CronologiaScaglioni as $scaglione => $importoDovuto){
                //I pagamenti in ritardo possono esistere solo negli scaglioni 1 (5gg) e 2 (60gg)
                if($scaglione > 2){
                    break;
                    }
                $importoDovuto = number_format($importoDovuto,2,'.','');
                $n_Pagato = number_format($n_Pagato,2,'.','');
                //echo "controllaPagamentoInRitardo --> scaglione: ".$scaglione." dovuto: ".$importoDovuto." pagato ".$n_Pagato."<br>";
                //Controlla l'uguaglianza perchè vogliamo sapere se è stata pagata quella cifra specifica
                if($n_Pagato == $importoDovuto){
                    $b_PagamentoInRitardo = true;
                    $this->latePaymentStatus = $scaglione;
                    break;
                }
            }
        }
        
        //Ora viene restituito se è c'è almeno un pagamento in ritardo o meno
        //Si potrebbe valutare di migliorare la funzione facendo restituire anche quale pagamento è in ritardo e per quale scaglione
        return $b_PagamentoInRitardo;
    }
    
    function calcolaTempiElaborazione($ProcessingStartTime){
        //ora fine elaborazione
        $ProcessingEndTime = microtime(true);
        
        //Tempo impiegato
        $ProcessingTime = number_format($ProcessingEndTime-$ProcessingStartTime,4,'.','');
        
        //trigger_error("Tempo di elaborazione --> $ProcessingTime secondi",E_USER_NOTICE);
        $this->responseTime = $ProcessingTime."s";
        
        return $ProcessingTime;
    }
    
    //NOTA: nel caso il giorno di data1 sia uguale al giorno di data2, sottraiamo comunque un mese perchè quando poi calcoliamo i semestri dobbiamo tenere
    //conto che l'ultimo giorno del semestre è ancora nello scaglione
    static function calcolaMesi($data1, $data2){
        $data1 = DateTime::createFromFormat("Y-m-d", $data1);
        $data2 = DateTime::createFromFormat("Y-m-d", $data2);
        
        $mesi1 = ($data1->format("Y") * 12) + $data1->format("m");
        $mesi2 = ($data2->format("Y") * 12) + $data2->format("m");
        
        $mesiCalcolati = $mesi1-$mesi2;
        //qui usiamo <= invece che < perchè non consideriamo il mese come trascorso se i giorni sono uguali
        if($data1->format("d") <= $data2->format("d")) $mesiCalcolati--;
        return $mesiCalcolati;
    }
}