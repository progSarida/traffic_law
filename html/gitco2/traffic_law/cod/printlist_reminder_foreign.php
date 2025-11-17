<?php
require(CLS.'/cls_literal_number.php');

$Operation          = CheckValue('Operation','s');
$PrintType          = CheckValue('PrintType','s');

$a_GradeType = array("","I","II","III");
$GrandTotalAmount = 0.00;
$GrandTotalAmountPayed = 0.00;
$GrandDifference = 0.00;
$GrandReminderFees = 0.00;
$GrandNotificationFees = 0.00;
$GrandSemester = 0;
$GrandPercentualAmount = 0.00;
//$GrandPercentual = 0.00;
$GrandOriginalAmount = 0.00;
$GrandPayedAmountFee = 0.00;
$GrandPayedFees = 0.00;
$GrandPayedPercentual = 0.00;
$PrintDate = date('d/m/Y');

if(isset($_POST['checkbox'])) {
    
    $rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'");
    $a_ProtocolLetterLocality = array();
    while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
    }
    
    $rs_ProcessingDataPaymentForeign = $rs->SelectQuery("SELECT RangeDayMin, WaitDay, ReducedPaymentDayAccepted, PaymentDayAccepted FROM ProcessingDataPaymentForeign WHERE CityId='".$_SESSION['cityid']."'");
    $r_ProcessingDataPaymentForeign = mysqli_fetch_array($rs_ProcessingDataPaymentForeign);
    $RangeDayMin = $r_ProcessingDataPaymentForeign['RangeDayMin'];
    $WaitDay = $r_ProcessingDataPaymentForeign['WaitDay'];
    //Tolleranza < 60gg
    $PaymentDayAccepted = $r_ProcessingDataPaymentForeign['PaymentDayAccepted'];
    
    switch ($Operation){
        case INDEX_OPERATION_LIST_CREATED: {
            $str_Folder = PRINT_FOLDER.'/elenco_solleciti_creati'."/".$_SESSION['cityid'];
            $str_Folder_html = PRINT_FOLDER_HTML.'/elenco_solleciti_creati'."/".$_SESSION['cityid'];
            $Print_type = 'Provv.';
            $FileName = "{$_SESSION['cityid']}_printlist_reminder_f_".date('Y-m-d');
            break;
        }
        case INDEX_OPERATION_LIST_EMITTED: {
            $str_Folder = PRINT_FOLDER.'/elenco_solleciti_emessi'."/".$_SESSION['cityid'];
            $str_Folder_html = PRINT_FOLDER_HTML.'/elenco_solleciti_emessi'."/".$_SESSION['cityid'];
            $Print_type = 'Inv.';
            $FileName = "{$_SESSION['cityid']}_printlist_reminder_emitted_f_".date('Y-m-d');
            break;
        }
    }
    
    if (!is_dir($str_Folder)) {
        mkdir($str_Folder, 0770, true);
        chmod($str_Folder, 0770);
    }
    
    $str_Order = frmReminderListOrderBy();
    $str_Where = frmReminderListWhere($Operation);
    $a_UsedFilters = frmReminderListUsedFilters($Operation);
    $str_Where .= " AND F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year'];
    $str_Where .= " AND FR.Id IN(".implode(',', $_POST['checkbox']).")";
    
    $cls_view = new CLS_VIEW(FRM_REMINDER_LIST);
    
    $query = $cls_view->generateSelect($str_Where, null, $str_Order);
    $rs_Results = $rs->SelectQuery($query);
    
    //Aggiunge il progressivo crescente ai solleciti
    $r_Results = orderEmittedRemindersProgressive($rs_Results);
    
    $a_Dati = array();
    
    foreach ($r_Results as $r_Reminder){
        $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType1'];
        $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType2'];
        
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$r_Reminder['ViolationTypeId']." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        $RuleTypeId = $r_RuleType['Id'];
        $str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
        
        switch ($Operation){
            case INDEX_OPERATION_LIST_CREATED: {
                $str_WhereRegDate = " AND RegDate <= '".$r_Reminder['PrintDate']."'";
                $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId) ReminderCount FROM FineReminder WHERE FineId=".$r_Reminder['FineId']." AND PrintDate IS NOT NULL AND FlowDate IS NULL AND SendDate IS NULL");
                break;
            }
            case INDEX_OPERATION_LIST_EMITTED: {
                $str_WhereRegDate = "";
                $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId) ReminderCount FROM FineReminder WHERE FineId=".$r_Reminder['FineId']." AND FlowDate IS NOT NULL AND SendDate IS NOT NULL");
                break;
            }
        }
        
        $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);
        $n_Reminder = $r_ReminderHistory['ReminderCount'];
        
        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$r_Reminder["Prog"];
        
        $rs_PaymentsNumber = $rs->Select("FinePayment", "FineId=".$r_Reminder['FineId'].$str_WhereRegDate);
        $PaymentsNumber = mysqli_num_rows($rs_PaymentsNumber);
        
        $rs_Dispute = $rs->SelectQuery("SELECT FD.GradeTypeId, FD.OfficeTitle, DR.Title DisputeResult FROM V_FineDispute FD JOIN DisputeResult DR ON FD.DisputeStatusId = DR.Id WHERE FineId=".$r_Reminder['FineId']);
        $r_Dispute = mysqli_fetch_array($rs_Dispute);
        
        $PayedAmountFee = 0;
        $PayedFees = 0;
        $PayedPercentual = 0;
        
        while($r_Payments = mysqli_fetch_array($rs_PaymentsNumber)){
            $PayedAmountFee += $r_Payments['Fee'];
            //Spese
            $PayedFees += $r_Payments['ResearchFee'] + $r_Payments['NotificationFee'] + $r_Payments['CanFee'] + $r_Payments['CadFee'] + $r_Payments['CustomerFee'] + $r_Payments['OfficeNotificationFee'];
            $PayedPercentual += $r_Payments['PercentualFee'];
        }
        
        $Fee = $r_Reminder['Fee'];
        $HalfMaxFee = $r_Reminder['HalfMaxFee'];
        //$MaxFee = $r_Reminder['MaxFee'];
        
        $TotalAmount = $r_Reminder['TotalAmount'];
        $AmountPayed = $r_Reminder['Amount'];
        
        $TrespasserName = implode(' ', array(trim($r_Reminder['CompanyName']), trim($r_Reminder['Surname']), trim($r_Reminder['Name'])));
        if(empty(trim($r_Reminder['CompanyName']))){
            $TrespasserAddress = "Residente in: ";
            $TrespasserBornData = "Nato/a : ".trim($r_Reminder['BornPlace'])." ".trim($r_Reminder['BornCountry'])." il : ".DateOutDB($r_Reminder['BornDate']);
        } else {
            $TrespasserAddress = "Sede in: ";
            $TrespasserBornData= '';
        }
        $TrespasserAddress .= implode(' ', array(trim($r_Reminder['TrespasserAddress']), trim($r_Reminder['City']), trim($r_Reminder['ZIP']), trim($r_Reminder['Province'])));
        $TrespasserName .= ", $TrespasserAddress,  $TrespasserBornData";
        
        //il valore della sanzione dipende dallo scaglione in cui è stato calcolato il ricorso
        // che è stato salvato con valore negativo
        // differenza tra la data di creazione del sollecito e la data di notifica maggiorata di 60 gg
        $ReminderDays = DateDiff("D", $r_Reminder['NotificationDate'], $r_Reminder['PrintDate']);
        //echo "da elaborazione a notifica gg: $ReminderDays";
        //se supera i 180 gg si applica la metà del massimo edittale
        $Amount = 0;
        
        //trigger_error("Data notifica: ".$r_Reminder['NotificationDate']." Data stampa: ".$r_Reminder['PrintDate']." Differenza (gg): ".$ReminderDays);

        //60gg+toll
        if ($ReminderDays<=(FINE_DAY_LIMIT+$PaymentDayAccepted)){
            $Amount = $Fee;
        }
        //Oltre i 60gg
        else if ($ReminderDays > FINE_DAY_LIMIT){
            $Amount = $HalfMaxFee;
        }
        
        /*
         * Scorporo del Pagato al netto delle spese in caso fosse maggiore dell'importo senza maggiorazione.
         * In tal caso, la cifra eccedente viene messa sulla maggiorazione.
         * */
        if($PayedAmountFee > $Amount)
        {
            $Exceedence = $PayedAmountFee-$Amount;
            $PayedPercentual += $Exceedence;
            $PayedAmountFee = $PayedAmountFee-$Exceedence;
        }
        
        $OriginalAmount = $r_Reminder['TotalAmount'];
        $Difference = ($TotalAmount-$AmountPayed);
        $ReminderFees = $r_Reminder['NotificationFee'];
        $NotificationFees = $r_Reminder['TotalNotification'];
        $PercentualAmount = $r_Reminder['PercentualAmount'];
        $Dispute =
        $r_Dispute['OfficeTitle'].
        (isset($r_Dispute['GradeTypeId']) ? '/'.$a_GradeType[$r_Dispute['GradeTypeId']] : '').
        (isset($r_Dispute['DisputeResult']) ? '/'.$r_Dispute['DisputeResult'] : '');
        
        $length = abs(180);
        if(strlen($TrespasserName) > $length) {
            $TrespasserName = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $TrespasserName);
        }
        
        $ReminderPrintDate = DateOutDB($r_Reminder['PrintDate']);
        $ReminderSendDate = "";
        $ReminderFlowDate = "";
        $ReminderFlowSendDate ="";
        
        if ($Operation == INDEX_OPERATION_LIST_EMITTED){
            //La query viene eseguita in questo blocco perchè solo nell'elenco solleciti definitivo è presente il flow number
            $rs_Flow = $rs->Select("Flow","RuleTypeId={$_SESSION['ruletypeid']} AND Number = ".$r_Reminder['FlowNumber']." AND CityId = '".$r_Reminder['CityId']."' AND CreationDate = '".$r_Reminder['FlowDate']."'",null,null);
            $r_Flow = mysqli_fetch_array($rs_Flow);
            $ReminderSendDate = DateOutDB($r_Reminder['SendDate']);
            $ReminderFlowDate = DateOutDB($r_Reminder['FlowDate']);
            $ReminderFlowSendDate = DateOutDB($r_Flow['SendDate']);
        }
        
        $a_Dati[] = array(
            "FineId" => $r_Reminder['FineId'],
            "FineReminderId" => $r_Reminder['FineReminderId'],
            "FlowNumber" => $r_Reminder['FlowNumber'],
            "Trasgressore" => $TrespasserName,
            "Data definizione" => "",
            "Anno" => $r_Reminder['ProtocolYear'],
            "San.Am.Dov" => $Amount,
            "Spese Dovute" => $NotificationFees,
            "Mag.Dov." => $PercentualAmount,
            "Spese Solle." => $ReminderFees,
            "Tot. Dovuto" => $OriginalAmount,
            "Ulteriori Dati" => $r_Reminder['TrespasserTypeDesc'],
            "N.Cronol+Targa" => $str_ReminderCode.' '.StringOutDB($r_Reminder['VehiclePlate']),
            "Data versamento" => DateOutDB($r_Reminder['PaymentDate']),
            "Stamp." => $Print_type,
            "San.Am.Pag" => $PayedAmountFee,
            "Spese Pagate" => $PayedFees,
            "Mag.Pag." => $PayedPercentual,
            "Sem.Magg." => $r_Reminder['Semester'],
            "Tot. Pagato" => $AmountPayed,
            "Data Verb. - Data Not." => DateOutDB($r_Reminder['FineDate']). ' - ' .DateOutDB($r_Reminder['NotificationDate']),
            "Dettaglio Pag." => $PaymentsNumber > 0 ? $PaymentsNumber.'/Pag.' : '',
            "Data Calc." => $ReminderPrintDate,
            "Dettag. Ricorso" => $Dispute,
            "G.Agg.DataDef." => $WaitDay,
            "Differenza" => $Difference,
            "Data elaborazione sollecito" => $ReminderPrintDate,
            "Data stampa sollecito" => $ReminderSendDate,
            "Data creazione flusso di stampa" => $ReminderFlowDate,
            "Data invio flusso" => $ReminderFlowSendDate,
        );
    }
    
    $RowNumber = 0;
    
    switch ($PrintType){
        case FRM_REMINDERLIST_ACTION_PRINTPDF: {
            $FileName .= '.pdf';
            $countFilter = 0;
            
            $html = '<h3 style="text-align: center;"><strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'<br />
                ELENCO: SOLLECITI DI PAGAMENTO/POSIZIONI DA ISCRIVERE A RUOLO/DA EMETTERE INGIUNZIONI DI PAGAMENTO</strong></h3>
                
                <p style="text-align: center;">Stampato il '.$PrintDate.'</p>
                <br />
                Il pagamento del verbale originario doveva essere effettuato entro '.$RangeDayMin.' giorni dalla data della notifica.
                <h3><strong>SPECIFICHE DELLA STAMPA</strong></h3>
                <u><strong>Data Definizione</strong></u> : E&#39; la data in cui il verbale &egrave; diventato definitivo. Questa data viene utilizzata per il passaggio della sanzione dal 1/4 del massimo alla met&agrave; del massimo e per la decorrenza del calcolo della maggiorazione del 10% semestrale.<br />
                <u><strong>Data Calc.</strong></u> : E&#39; la data di calcolo/elaborazione che viene utilizzata per calcolare il numero di semestri trascorsi dalla data di definizione del verbale.<br />
                <strong><u>Sem. Magg.</u></strong> : Indica il numero di semestri applicati per il calcolo della maggiorazione del 10% semestrale.<br />
                <strong><u>Tipo </u></strong>: Indica particolari situazioni collegate al verbale per le quali si consiglia il controllo dei conteggi prima di stampare gli atti.<br />
                <u><strong>Ric.</strong></u> : Verbale per il quale risulta presente un ricorso.<br />
                <strong><u>Rat.</u></strong> : Verbale per il quale &egrave; attiva l&#39;opzione di pagamento rateale.<br />
                <u><strong>Stamp</strong></u> : Provv.(Atto stampato in modo Provvisorio), Def.(Atto stampato in modo Definitivo), Inv.(Il flusso &egrave; stato creato ed inviato)<br />
                <u><strong>Dettaglio Pag.</strong></u> : Indica il numero di pagamenti trovati al verbale nel momento dell\'elaborazione.<br />
                <u><strong>G.Agg.DatDef.</strong></u> : Se viene valorizzato significa che alle posizioni che presentano pagamento/i &egrave; stata aggiunta alla data di definizione i giorni aggiuntivi in pi&ugrave; trovati nei parametri (Giorni aggiuntivi ai 60 previsti per il pagamento) al momento della elaborazione generale.<br />
                <u><strong>Dettag. Ricorso</strong></u> : Evidenzia l&#39;autorit&aacute;, il grado e l&#39;esito della sospensiva e del merito del ricorso che &egrave; stato trovato nel momento della elaborazione.<br />
                    
                <h3><strong>OPZIONI SELEZIONATE NEL MOMENTO DELLA STAMPA&nbsp;</strong></h3>';
            
            $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
            $pdf->TemporaryPrint= 0;
            $pdf->NationalFine= 2;
            $pdf->CustomerFooter = 0;
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_SESSION['citytitle']);
            $pdf->SetTitle('Reminder list');
            $pdf->SetSubject('');
            $pdf->SetKeywords('');
            $pdf->setHeaderFont(array('helvetica', '', 8));
            $pdf->setFooterFont(array('helvetica', '', 8));
            $pdf->SetMargins(10,10,10);
            $pdf->setCellHeightRatio(1.5);
            
            //Prima pagina//////////////////////////////////////////////////////////////////////////////
            $pdf->AddPage('L', array('Rotate'=>45));
            $pdf->SetFont('helvetica', '', 10);
            $pdf->writeHTML($html, true, false, true, false, '');
            
            $pdf->LN(5);
            
            foreach ($a_UsedFilters as $filterName => $filterValue){
                $countFilter++;
                $pdf->writeHTMLCell(pdfColumnSize($pdf, 4), 0, '', '' , "$filterName : $filterValue", 1, (next($a_UsedFilters) === false || $countFilter % 3 == 0) ? 1 : 0, 0, true, 'L', true);
            }
            
            $pdf->SetFont('helvetica', '', 10);
            
            frmReminderListNewPDFPage($pdf, $Operation, $_SESSION['year'], $_SESSION['citytitle']);
            
            foreach ($a_Dati as $riga){
                $RowNumber++;
                
                //Celle righe//////////////////////////////////////////////////////////////////////////////
                $pdf->setCellHeightRatio(1);
                $pdf->SetFont('helvetica', '', 9);
                
                //First line
                $pdf->MultiCell(275, 0, $riga["Trasgressore"], 0, '', 0, 0, '', '', true);
                $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
                
                //Second line
                $pdf->MultiCell(80, 0, $riga["Data definizione"], 0, '', 0, 0, '', '', true);
                $pdf->MultiCell(30, 0, $riga["Anno"], 0, '', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, number_format($riga["San.Am.Dov"],2,',','.'), 0, 'R', 0, 0, '', '', true); //sanzione amministrativa dovuta
                $pdf->MultiCell(25, 0, number_format($riga["Spese Dovute"], 2, ',', '.'), 0, 'R', 0, 0, '', '', true); //spese aggiuntive verbale
                $pdf->MultiCell(25, 0, number_format($riga["Mag.Dov."], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, number_format($riga["Spese Solle."], 2, ',', '.'), 0, 'R', 0, 0, '', '', true); //spese invio sollecito
                $pdf->MultiCell(25, 0, number_format($riga["Tot. Dovuto"], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->SetTextColor(255, 0, 0);
                $pdf->MultiCell(40, strlen($riga["Trasgressore"]) > 15 ? 7 : 0, $riga["Ulteriori Dati"], 0, 'R', 0, 1, '', '', true);
                $pdf->SetTextColor(0, 0, 0);
                
                //Third line
                $pdf->SetFont('helvetica', '', 7);
                $pdf->MultiCell(60, 0, $riga["N.Cronol+Targa"], 0, '', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->MultiCell(35, 0, $riga["Data versamento"], 0, '', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetTextColor(255, 125, 0);
                $pdf->MultiCell(15, 0, $riga["Stamp."], 0, '', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(25, 0, number_format($riga["San.Am.Pag"], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, number_format($riga["Spese Pagate"], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, number_format($riga["Mag.Pag."], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, $riga["Sem.Magg."], 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, number_format($riga["Tot. Pagato"], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', '', 6);
                $pdf->MultiCell(40, 0, '', 0, 'R', 0, 1, '', '', true);
                $pdf->SetTextColor(0, 0, 0);
                
                //Fourth line
                $pdf->SetFont('helvetica', '', 8);
                $pdf->MultiCell(60, 0, $riga["Data Verb. - Data Not."], 0, '', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->MultiCell(35, 0, $riga["Dettaglio Pag."], 0, '', 0, 0, '', '', true);
                $pdf->MultiCell(15, 0, '', 0, '', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->MultiCell(25, 0, $riga["Data Calc."], 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, $riga["Dettag. Ricorso"], 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, '', 0, 'R', 0, 0, '', '', true);
                $pdf->MultiCell(25, 0, $riga["G.Agg.DataDef."], 0, 'R', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->MultiCell(25, 0, number_format($riga["Differenza"], 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(255, 0, 0);
                $pdf->MultiCell(40, 0, '-', 0, 'R', 0, 1, '', '', true);
                $pdf->SetTextColor(0, 0, 0);
                
                //Fifth line
                $pdf->SetFont('helvetica', '', 8);
                switch ($Operation){
                    case INDEX_OPERATION_LIST_CREATED: {
                        $pdf->MultiCell(60, 0, $riga["Data elaborazione sollecito"], 0, 'L', 0, 0, '', '', true);
                        break;
                    }
                    case INDEX_OPERATION_LIST_EMITTED: {
                        $pdf->MultiCell(60, 0, $riga["Data elaborazione sollecito"], 0, 'L', 0, 0, '', '', true);
                        $pdf->MultiCell(60, 0, $riga["Data stampa sollecito"], 0, 'L', 0, 0, '', '', true);
                        $pdf->MultiCell(60, 0, $riga["Data creazione flusso di stampa"], 0, 'L', 0, 0, '', '', true);
                        $pdf->MultiCell(55, 0, $riga["Data invio flusso"], 0, 'L', 0, 0, '', '', true);
                        break;
                    }
                }
                
                //Black line
                $pdf->LN(4);
                $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+$pdf->getPageWidth()-20, $pdf->GetY());
                $pdf->LN(1);
                
                $GrandTotalAmount += $riga["San.Am.Dov"];
                $GrandTotalAmountPayed += $riga["Tot. Pagato"];
                $GrandDifference += $riga["Differenza"];
                $GrandReminderFees += $riga["Spese Solle."];
                $GrandNotificationFees += $riga["Spese Dovute"];
                $GrandSemester += $riga['Sem.Magg.'];
                $GrandPercentualAmount += $riga["Mag.Dov."];
                $GrandOriginalAmount += $riga["Tot. Dovuto"];
                $GrandPayedAmountFee += $riga["San.Am.Pag"];
                $GrandPayedFees += $riga["Spese Pagate"];
                $GrandPayedPercentual += $riga["Mag.Pag."];
                
                ////////////////////////////////////////////////////////////////////////////////
                
                //Ogni 5 righe crea una nuova pagina e scrive le celle header e footer
                if($RowNumber%5 == 0 && (count($_POST['checkbox'])-$RowNumber) > 0){
                    frmReminderListPDFSummary($pdf, $RowNumber, $GrandTotalAmount, $GrandNotificationFees, $GrandPercentualAmount,
                        $GrandReminderFees, $GrandOriginalAmount, $PrintDate, $GrandPayedAmountFee, $GrandPayedFees, $GrandPayedPercentual,
                        $GrandTotalAmountPayed, $GrandDifference, $GrandSemester);
                    
                    frmReminderListNewPDFPage($pdf, $Operation, $_SESSION['year'], $_SESSION['citytitle']);
                }
            }
            
            frmReminderListPDFSummary($pdf, $RowNumber, $GrandTotalAmount, $GrandNotificationFees, $GrandPercentualAmount, $GrandReminderFees,
                $GrandOriginalAmount, $PrintDate, $GrandPayedAmountFee, $GrandPayedFees, $GrandPayedPercentual, $GrandTotalAmountPayed,
                $GrandDifference, $GrandSemester);
            
            //Nuova pagina//////////////////////////////////////////////////////////////////////////////
            $page_format = "";
            $pdf->Header = false;
            $pdf->SetMargins(10,10,10);
            $pdf->AddPage('L', $page_format);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            
            $pdf->MultiCell(85, 0, 'RIASSUNTO ULTERIORI DATI', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(50, 0, 'TOTALE TROVATE', 1, '', 0, 1, '', '', true);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->MultiCell(85, 0, 'Controllare gli importi nella posizione singola.', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(50, 0, '-', 1, '', 0, 1, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            ////////////////////////////////////////////////////////////////////////////////
            
            $pdf->Output($str_Folder.'/'.$FileName, "F");
            $_SESSION['Documentation'] = $str_Folder_html.'/'.$FileName;
            
            break;
        }
        
        case FRM_REMINDERLIST_ACTION_PRINTXLS: {
            $FileName .= '.xls';
            ob_start();
            $n_Count = 1; ?>
    	<table>
    		<tr></tr>
			<tr></tr>
    		<tr><td>ELENCO: SOLLECITI DI PAGAMENTO/POSIZIONI DA ISCRIVERE A RUOLO/DA EMETTERE INGIUNZIONI DI PAGAMENTO</td></tr>
    		<tr><td><?= 'COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year']; ?></td></tr>
			<tr></tr>
            <tr><td>Stampato il <?= $PrintDate; ?><td></tr>
            <tr></tr>
            <tr><td>Il pagamento del verbale originario doveva essere effettuato entro <?= $RangeDayMin; ?> giorni dalla data della notifica.</td></tr>
            <tr></tr>
            <tr><td>SPECIFICHE DELLA STAMPA</td></tr>
            <tr>
            	<th colspan="2">Data Definizione</th>
            	<td colspan="17">La data in cui il verbale è diventato definitivo. Questa data viene utilizzata per il passaggio della sanzione dal 1/4 del massimo alla metà; del massimo e per la decorrenza del calcolo della maggiorazione del 10% semestrale.</td>
        	</tr>
            <tr>
            	<th colspan="2">Data Calc.</th>
        		<td colspan="17">La data di calcolo/elaborazione che viene utilizzata per calcolare il numero di semestri trascorsi dalla data di definizione del verbale.</td>
    		</tr>
            <tr>
            	<th colspan="2">Sem. Magg.</th>
        		<td colspan="17">Indica il numero di semestri applicati per il calcolo della maggiorazione del 10% semestrale.</td>
    		</tr>
            <tr>
            	<th colspan="2">Tipo </th>
            	<td colspan="17">Indica particolari situazioni collegate al verbale per le quali si consiglia il controllo dei conteggi prima di stampare gli atti.</td>
        	</tr>
            <tr>
            	<th colspan="2">Ric.</th>
            	<td colspan="17">Verbale per il quale risulta presente un ricorso.</td>
        	</tr>
            <tr>
            	<th colspan="2">Rat.</th>
            	<td colspan="17">Verbale per il quale è attiva l'opzione di pagamento rateale.</td>
        	</tr>
            <tr>
            	<th colspan="2">Stamp</th>
            	<td colspan="17">Provv.(Atto stampato in modo Provvisorio), Def.(Atto stampato in modo Definitivo), Inv.(Il flusso è stato creato ed inviato)</td>
        	</tr>
            <tr>
            	<th colspan="2">Dettaglio Pag.</th>
            	<td colspan="17">Indica il numero di pagamenti trovati al verbale nel momento dell\'elaborazione.</td>
        	</tr>
            <tr>
            	<th colspan="2">G.Agg.DatDef.</th>
            	<td colspan="17">Se viene valorizzato significa che alle posizioni che presentano pagamento/i è stata aggiunta alla data di definizione i giorni aggiuntivi in più; trovati nei parametri (Giorni aggiuntivi ai 60 previsti per il pagamento) al momento della elaborazione generale.</td>
        	</tr>
            <tr>
            	<th colspan="2">Dettag. Ricorso</th>
            	<td colspan="17">Evidenzia l'autorità, il grado e l'esito della sospensiva e del merito del ricorso che è stato trovato nel momento della elaborazione.</td>
        	</tr>
        	<tr></tr>
			<tr><td>OPZIONI SELEZIONATE NEL MOMENTO DELLA STAMPA:</td></tr>
			<?php foreach($a_UsedFilters as $filterName => $filterValue): ?>
				<tr>
					<th colspan="2"><?= $filterName; ?>:</th>
					<td><?= $filterValue; ?></td>
				</tr>
			<?php endforeach; ?>
			<tr></tr>
    		<tr></tr>
    		<tr><td>Risultati: <?= mysqli_num_rows($rs_Results) ?></td></tr>
    		<tr></tr>
		</table>
		<table border="1">
    		<tr bgcolor="lightblue">
    			<th colspan="1">N.</th>
    			<th colspan="6">Trasgressore</th>
    			<th colspan="2">Data definizione</th>
    			<th colspan="2">Anno</th>
    			<th colspan="2">San.Am.Dov</th>
    			<th colspan="2">Spese Dovute</th>
    			<th colspan="2">Mag.Dov.</th>
    			<th colspan="2">Spese Solle.</th>
    			<th colspan="2">Tot. Dovuto</th>
    			<th colspan="2">Ulteriori Dati</th>
    			<th colspan="2">N.Cronol+Targa</th>
    			<th colspan="2">Data versamento</th>
    			<th colspan="2">Stamp.</th>
    			<th colspan="2">San.Am.Pag</th>
    			<th colspan="2">Spese Pagate</th>
    			<th colspan="2">Mag.Pag.</th>
    			<th colspan="2">Sem.Magg.</th>
    			<th colspan="2">Tot. Pagato</th>
    			<th colspan="2">Data Verb. - Data Not.</th>
    			<th colspan="2">Dettaglio Pag.</th>
    			<th colspan="2">Data Calc.</th>
    			<th colspan="2">Dettag. Ricorso</th>
    			<th colspan="2">G.Agg.DataDef.</th>
    			<th colspan="2">Differenza</th>
    			<th colspan="2">Data elaborazione sollecito</th>
    			<?php if($Operation == INDEX_OPERATION_LIST_EMITTED): ?>
	    			<th colspan="2">Data stampa sollecito</th>
        			<th colspan="2">Data creazione flusso di stampa</th>
        			<th colspan="2">Data invio flusso</th>
    			<?php endif; ?>
        	</tr>
        	<?php foreach($a_Dati as $riga): ?>
        		<tr>
        			<td colspan="1"><?= $n_Count++; ?></td>
	    			<td colspan="6"><?= $riga["Trasgressore"]; ?></td>
        			<td colspan="2"><?= $riga["Data definizione"]; ?></td>
        			<td colspan="2"><?= $riga["Anno"]; ?></td>
        			<td colspan="2"><?= $riga["San.Am.Dov"]; ?></td>
        			<td colspan="2"><?= $riga["Spese Dovute"]; ?></td>
        			<td colspan="2"><?= $riga["Mag.Dov."]; ?></td>
        			<td colspan="2"><?= $riga["Spese Solle."]; ?></td>
        			<td colspan="2"><?= $riga["Tot. Dovuto"]; ?></td>
        			<td colspan="2"><?= $riga["Ulteriori Dati"]; ?></td>
        			<td colspan="2"><?= $riga["N.Cronol+Targa"]; ?></td>
        			<td colspan="2"><?= $riga["Data versamento"]; ?></td>
        			<td colspan="2"><?= $riga["Stamp."]; ?></td>
        			<td colspan="2"><?= $riga["San.Am.Pag"]; ?></td>
        			<td colspan="2"><?= $riga["Spese Pagate"]; ?></td>
        			<td colspan="2"><?= $riga["Mag.Pag."]; ?></td>
        			<td colspan="2"><?= $riga["Sem.Magg."]; ?></td>
        			<td colspan="2"><?= $riga["Tot. Pagato"]; ?></td>
        			<td colspan="2"><?= $riga["Data Verb. - Data Not."]; ?></td>
        			<td colspan="2"><?= $riga["Dettaglio Pag."]; ?></td>
        			<td colspan="2"><?= $riga["Data Calc."]; ?></td>
        			<td colspan="2"><?= $riga["Dettag. Ricorso"]; ?></td>
        			<td colspan="2"><?= $riga["G.Agg.DataDef."]; ?></td>
        			<td colspan="2"><?= $riga["Differenza"]; ?></td>
        			<td colspan="2"><?= $riga["Data elaborazione sollecito"]; ?></td>
	    			<?php if($Operation == INDEX_OPERATION_LIST_EMITTED): ?>
    	    			<td colspan="2"><?= $riga["Data stampa sollecito"]; ?></td>
            			<td colspan="2"><?= $riga["Data creazione flusso di stampa"]; ?></td>
            			<td colspan="2"><?= $riga["Data invio flusso"]; ?></td>
        			<?php endif; ?>
            	</tr>
        	<?php endforeach; ?>
		</table>
    	<?php
    	$table = ob_get_clean();
    	
    	//Scrive il report in formato xls sul file system
    	file_put_contents($str_Folder.'/'.$FileName, "\xEF\xBB\xBF".$table);
    	//Carica il percorso del file in sessione
    	$_SESSION['Documentation'] = $str_Folder_html.'/'.$FileName;
    	break;
        }
    }
}

