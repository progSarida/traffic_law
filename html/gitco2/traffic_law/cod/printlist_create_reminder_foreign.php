<?php
require(CLS.'/cls_literal_number.php');
include(INC."/function_postalCharge.php");

$a_GradeType = array("","I","II","III");

$GrandTotalAmount = 0;
$GrandTotalAmountPayed = 0;
$GrandDifference = 0;
$GrandReminderFees = 0;
$GrandNotificationFees = 0;
$GrandSemester = 0;
$GrandPercentualAmount = 0;
$GrandOriginalAmount = 0;
$GrandPayedAmountFee = 0;
$GrandPayedFees = 0;
$GrandPayedPercentual = 0;


if(isset($CreationType)){
    if($CreationType==5 AND $ProtocolIdAssigned==0){
        $rs_Customer = $rs->Select("V_Customer", "CreationType=5 AND CityId='".$_SESSION['cityid']."'");
        $r_Customer  = mysqli_fetch_array($rs_Customer);
    }
}

$str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
}

$rs_ProcessingDataPaymentNational = $rs->SelectQuery("SELECT RangeDayMin FROM ProcessingDataPaymentNational WHERE CityId='".$_SESSION['cityid']."'");
$RangeDayMin = mysqli_fetch_array($rs_ProcessingDataPaymentNational)['RangeDayMin'];

$html = '<h3 style="text-align: center;"><strong>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'<br />
ELENCO SOLLECITI DI PAGAMENTO</strong></h3>

<p style="text-align: center;">Stampato il '.$CreationDate.'</p>
<br />
Il pagamento del verbale originario doveva essere effettuato entro '.$RangeDayMin.' giorni dalla data della notifica.
<h3><strong>SPECIFICHE DELLA STAMPA</strong></h3>
<u><strong>Data Definizione</strong></u> : E&#39; la data in cui il verbale &egrave; diventato definitivo. Questa data viene utilizzata per il passaggio della sanzione dal 1/4 del massimo alla met&agrave; del massimo e per la decorrenza del calcolo della maggiorazione del 10% semestrale.<br />
<u><strong>Data Calc.</strong></u> : E&#39; la data di calcolo/elaborazione del sollecito che serve per calcolare il numero di semestri trascosi dalla data di definizione del verbale.<br />
<strong><u>Sem. Magg.</u></strong> : Indica il numero di semestri applicati per la maggiorazione del sollecito.<br />
<strong><u>Tipo </u></strong>: Indica particolari situazioni collegate al verbale per le quali si consiglia il controllo dei conteggi prima di stampare i solleciti.<br />
<u><strong>Ric.</strong></u> : Verbale per il quale risulta presente un ricorso.<br />
<strong><u>Rat.</u></strong> : Verbale per il quale &egrave; attiva l&#39;opzione di pagamento rateale.<br />
<u><strong>Stamp</strong></u> : Prov.(Sollecito stampato in modo Provvisorio), Def.(Sollecito stampato in modo Definitivo), Inv.(Il flusso &egrave; stato creato ed inviato)<br />
<strong><u>N.R.</u></strong> : Vuol dire che in ulteriori dati al singolo verbale &egrave; stato selezionato il flag per non radoppiare la sanzione.<br />
<u><strong>N.M.</strong></u> : Vuol dire che in ulteriori dati al singolo verbale &egrave; stato selezionato il flag per non applicare la maggiorazione.<br />
<u><strong>Elaboraz.(A) (M)</strong></u> : vuol dire se il sollecito &egrave; stato elaborato automaticamente (A) dalla procedura generale, oppure &egrave; stato modificato manualmente (M) della posizione singola. Se appare la scritta (2do) vuol dire che il sollecito &egrave; stato stampato con il testo del secondo sollecito.<br />
<u><strong>Dettaglio Pag.</strong></u> : Indica il numero di pagamenti trovati al verbale nel momento della elaborazione del sollecito.<br />
<strong><u>Integr.</u></strong> : Se viene valorizzato SI, indica che nel momento della elaborazione generale dei solleciti era stato selezionato il flag (Gestione integr. pagamento su spese notifica) nei parametri.<br />
<u><strong>G.Agg.DatDef.</strong></u> : Se viene valorizzato vuol dire che alle posizione che presentano pagamento/i &egrave; stato aggiunta alla data de definizione i giorni aggiuntivi in pi&ugrave; trovati nei parametri (Giorni aggiuntivi ai 60 previsti per il pagamento) al momento della elaborazione generale del sollecito.<br />
<u><strong>Dettag. Ricorso</strong></u> :Evidenzia l&#39;autorita, il grado e l&#39;esito della sospensiva e del merito del ricorso che &egrave; stato trovato nel momento della elaborazione del sollecito.

<h3><strong>OPZIONE SELEZIONATE NEL MOMENTO DELLA STAMPA&nbsp;</strong></h3>
';


if(isset($_POST['checkbox'])) {
    
    $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
    $page_format = array('Rotate'=>45);

    $pdf->TemporaryPrint= 0;
    $pdf->NationalFine= 1;
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
    $pdf->AddPage('L', $page_format);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->writeHTML($html, true, false, true, false, '');
////////////////////////////////////////////////////////////////////////////////

//Nuova pagina//////////////////////////////////////////////////////////////////////////////
    $page_format = "";
        
    $pdf->Header = false;
    
    $pdf->SetMargins(10,10,10);
    $pdf->AddPage('L', $page_format);
    
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    
    
    $pdf->writeHTML('<h2>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'</h2>', true, false, true, false, '');
    $pdf->writeHTML('<h3>ELENCO DI TUTTI I SOLLECITI</h3>', true, false, true, false, '');
    
    $pdf->LN(5);
////////////////////////////////////////////////////////////////////////////////
    
//Celle header//////////////////////////////////////////////////////////////////////////////
    $pdf->SetFont('helvetica', 'B', 8);

    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(35, 0, 'Trasgressore', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Data definizione', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Anno', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'San.Am.Dov', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Dovute', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Mag.Dov.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Solle.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Tot. Dovuto', 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
    $pdf->MultiCell(25, 0, 'Tot. Dovuto Soll', 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
    
    $pdf->setCellPadding(5.5);
    $pdf->MultiCell(40, 0, 'Ulteriori Dati', 1, 'C', 0, 0, '', '', true);
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(35, 0, 'N.Cronol+Targa', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Data versamento', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Integr.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'San.Am.Pag', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Spese Pagate', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Mag.Pag.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Sem.Magg.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Tot. Pagato', 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
    $pdf->MultiCell(25, 0, 'Tot. Pagato Soll', 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->MultiCell(35, 0, 'Data Verb. - Data Not.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(35, 0, 'Dettaglio Pag.', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(15, 0, 'Stamp', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Data Calc.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Dettag. Ricorso', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Elaboraz.(A) (M).', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'G.Agg.DataDef.', 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, 'Differenza', 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
    $pdf->MultiCell(25, 0, 'Differenza Soll', 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
    
    $pdf->setCellPadding(0);
    $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
    
    $pdf->setCellPaddings(1, 0.5, 1, 0.5);
    $pdf->LN(7);
////////////////////////////////////////////////////////////////////////////////
    
    $RownNumber = 0;
    
    foreach($_POST['checkbox'] as $FineId) {
        $RownNumber++ ;
        
        $rs_Reminder = $rs->SelectQuery(
            "SELECT
                F.Id,
                F.Code,
                F.ProtocolId,
                F.ProtocolYear,
                F.Locality,
                F.Address,
                F.VehiclePlate,
                F.FineDate,
                F.FineTime,
                F.CityId,
                F.PagoPA1,
                F.PagoPA2,
                F.StatusTypeId,
            
                FH.NotificationTypeId,
                FH.NotificationDate,
                FH.FineId,
                FH.TrespasserId,
                FH.TrespasserTypeId,
                FH.NotificationFee,
                FH.ResearchFee,
                FH.OtherFee,
                FH.ControllerId,
                FH.SendDate,
                FH.DeliveryDate,
            
                FA.Fee,
                FA.MaxFee,
                FA.ViolationTypeId,
                FA.ArticleId,
            
                T.Id TrespasserId,
                T.Genre,
                T.CompanyName,
                T.Surname,
                T.Name,
                T.Address TrespasserAddress,
                T.ZIP,
                T.City,
                T.Province,
                T.TaxCode,
                T.ZoneId,
                T.LanguageId,

                TT.Description TrespasserTypeDesc,
            
                C.Title CityTitle,
            
                VT.TitleIta VehicleType
            
                FROM Fine F
                JOIN FineHistory FH ON F.Id=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN TrespasserType TT ON FH.TrespasserTypeId = TT.Id
                JOIN sarida.City C on C.Id = F.Locality
                JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
                WHERE F.Id=".$FineId
            );
        
        $r_Reminder = mysqli_fetch_array($rs_Reminder);
        
        $ViolationTypeId = $r_Reminder['ViolationTypeId'];
        
        
        $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType1'];
        $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['ForeignProtocolLetterType2'];
        
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);
        
        
        $RuleTypeId = $r_RuleType['Id'];
        
        $str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
        
        $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId);
        $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);
        
        $TotalNotification = $r_ReminderHistory['NotificationFee'];
        $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];
        
        
        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;
        
        $ZoneId= $trespasser['ZoneId'];
        $rs_PostalCharge=getPostalCharge($_SESSION['cityid'],DateInDB($ProcessingDate));
        
        $TotalAmount = 0;
        $NotificationFee = $r_PostalCharge['ReminderZone'.$ZoneId];
        $n_Day = 0;
        $n_Month = 0;
        
        if ($r_Customer['IncreaseForeignNotificationFee'] != 0){
            $rs_RemindersNumber = $rs->Select("FineReminder", "Id=$FineId");
            $n_Reminders = mysqli_num_rows($rs_RemindersNumber);
            $NotificationFee += $r_PostalCharge['ReminderZone'.$ZoneId]*$n_Reminders;
        }
        
        $TaxCode = trim($r_Reminder['TaxCode']);
        
        $Fee = $r_Reminder['Fee'];
        
        $MaxFee = ($r_Customer['ReminderAdditionalFee']==1) ? ($r_Reminder['MaxFee']*FINE_MAX)-$Fee : 0.00;
        $HalfMaxFee = $r_Reminder['MaxFee']*FINE_MAX;
        
        $TotalNotification += $r_Reminder['NotificationFee'] + $r_Reminder['ResearchFee'] + $r_Reminder['OtherFee'];
        
        $TotalAmount = $Fee + $MaxFee + $TotalNotification;
        
        $rs_PaymentsNumber = $rs->SelectQuery("SELECT Id, Fee, ResearchFee, NotificationFee FROM FinePayment WHERE FineId=".$FineId);
        $PaymentsNumber = mysqli_num_rows($rs_PaymentsNumber);
        
        $PayedAmountFee = 0;
        $PayedFees = 0;
        
        while($r_Payments = mysqli_fetch_array($rs_PaymentsNumber)){
            $PayedAmountFee += $r_Payments['Fee'];
            $PayedFees += $r_Payments['ResearchFee'] + $r_Payments['NotificationFee'];
        }
        
        $rs_Payment = $rs->SelectQuery("SELECT MAX(PaymentDate) PaymentDate, SUM(Amount) Amount FROM FinePayment WHERE FineId=".$FineId);
        $r_Payment = mysqli_fetch_array($rs_Payment);
        
        $Amount = $r_Payment['Amount'];
        $PaymentDate = DateOutDB($r_Payment['PaymentDate']);
        
        $PayedPercentual = $Amount - ($PayedAmountFee + $PayedFees);
        
        if ($Amount>0) {
            
            $rs_FineNotification = $rs->SelectQuery("SELECT NotificationDate FROM FineNotification WHERE FineId=".$FineId);
            $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
            
            $n_Day = DateDiff("D", $r_FineNotification['NotificationDate'], $r_Payment['PaymentDate'])+1;
            
            if($n_Day<=FINE_DAY_LIMIT_REDUCTION){
                
                $flt_DiffFee = $Amount - ($Fee * FINE_PARTIAL) + $TotalNotification;
                
                
                //                 $str_ReminderType = '
                //                     di euro '. NumberDisplay($Amount).' eseguito in data '. DateOutDB($r_Payment['PaymentDate']).' risulta effettuato entro '. $n_Day .' giorni dalla notifica ma per un importo inferiore al dovuto di euro '. NumberDisplay($flt_DiffFee);
            } else {
                $flt_DiffFee = $Fee + $TotalNotification;
                //                 $str_ReminderType = '
                //                     di euro '. NumberDisplay($Amount).' eseguito in data '. DateOutDB($r_Payment['PaymentDate']).' risulta effettuato oltre 5 giorni dalla notifica del verbale e pertanto (la SV/Codesta Ditta) avrebbe dovuto pagare euro '. NumberDisplay($flt_DiffFee);
            }
            
        } else {
            //$str_ReminderType = 'risulta omesso';
        }
        
        
        $Percentual =  $r_Customer['ForeignPercentualReminder'];
        
        if($Percentual>0){
            $PercentualAmount = 0;
            
            $d_DateLimit = date('Y-m-d', strtotime($r_Reminder['DeliveryDate']. ' + '.FINE_DAY_LIMIT.' days'));
            
            $n_Month = floor(DateDiff("M", $d_DateLimit, DateInDB($ProcessingDate))/6);
            $Base = $TotalAmount - $Amount;
            for($i=1; $i<=$n_Month; $i++){
                $PercentualAmount += $Base*$Percentual/100;
            }
            
            $TotalAmount += $PercentualAmount;
        }
        
        $TotalAmount += $NotificationFee;
        $TotalAmount -= $Amount;
        
        $rs_Dispute = $rs->SelectQuery("SELECT FD.GradeTypeId, FD.OfficeTitle, DR.Title DisputeResult FROM V_FineDispute FD JOIN DisputeResult DR ON FD.DisputeStatusId = DR.Id WHERE FineId=".$r_Reminder['FineId']);
        $r_Dispute = mysqli_fetch_array($rs_Dispute);
        
    //Celle righe//////////////////////////////////////////////////////////////////////////////
        $pdf->setCellHeightRatio(1);
        
        $pdf->SetFont('helvetica', '', 9);
        
        //First line
        $TrespasserName = (isset($r_Reminder['CompanyName']) ? StringOutDB($r_Reminder['CompanyName']).' ' : '') . StringOutDB($r_Reminder['Surname']) . ' ' . StringOutDB($r_Reminder['Name']);
        $AmountSum = number_format($MaxFee + $Fee, 2, ',', '.');
        $OriginalAmount = number_format($TotalAmount + $Amount, 2, ',', '.');
        $AmountPayed = number_format($Amount, 2, ',', '.');
        $Difference = number_format($TotalAmount, 2, ',', '.');
        $ReminderFees = number_format($r_PostalCharge['ReminderZone'.$r_Reminder['ZoneId']], 2, ',', '.');
        $NotificationFees = number_format($TotalNotification, 2, ',', '.');
        $LinePercentualAmount = number_format($PercentualAmount, 2, ',', '.');
        $Dispute =
            $r_Dispute['OfficeTitle'].
            (isset($r_Dispute['GradeTypeId']) ? '/'.$a_GradeType[$r_Dispute['GradeTypeId']] : '').
            (isset($r_Dispute['DisputeResult']) ? '/'.$r_Dispute['DisputeResult'] : '');
        
        $length = abs(30);
        if(strlen($TrespasserName) > $length) {
            $TrespasserName = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $TrespasserName);
        }
        
        $pdf->MultiCell(35, 0, $TrespasserName, 0, '', 0, 0, '', '', true);
        $pdf->MultiCell(35, 0, $ProcessingDate, 0, '', 0, 0, '', '', true);
        $pdf->MultiCell(15, 0, $r_Reminder['ProtocolYear'], 0, '', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $AmountSum, 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($TotalNotification, 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $LinePercentualAmount, 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $ReminderFees, 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $OriginalAmount, 0, 'R', 0, 0, '', '', true);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->MultiCell(25, 0, '-', 0, 'R', 0, 0, '', '', true);
        $pdf->SetTextColor(255, 0, 0);
        if (strlen($TrespasserName) > 15)
            $pdf->MultiCell(40, 7, $r_Reminder['TrespasserTypeDesc'], 0, 'R', 0, 1, '', '', true);
        else
            $pdf->MultiCell(40, 0, $r_Reminder['TrespasserTypeDesc'], 0, 'R', 0, 1, '', '', true);
        $pdf->SetTextColor(0, 0, 0);
        
        //Second line
        $pdf->SetFont('helvetica', '', 7);
        $pdf->MultiCell(35, 0, $str_ReminderCode.' '.StringOutDB($r_Reminder['VehiclePlate']), 0, '', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(35, 0, $PaymentDate, 0, '', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(15, 0, '-', 0, '', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(25, 0, number_format($PayedAmountFee, 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, number_format($PayedFees, 2, ',', '.'), 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, '-', 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $n_Month, 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, $AmountPayed, 0, 'R', 0, 0, '', '', true);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->MultiCell(25, 0, '-', 0, 'R', 0, 0, '', '', true);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->MultiCell(40, 0, '-', 0, 'R', 0, 1, '', '', true);
        $pdf->SetTextColor(0, 0, 0);
        
        //Third line
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(35, 0, DateOutDB($r_Reminder['FineDate']). ' - ' .DateOutDB($r_Reminder['NotificationDate']), 0, '', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(35, 0, $PaymentsNumber > 0 ? $PaymentsNumber.'/Pag.' : '', 0, '', 0, 0, '', '', true);
        $pdf->SetTextColor(255, 125, 0);
        $pdf->MultiCell(15, 0, 'Provv.', 0, '', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(25, 0, DateOutDB($r_Reminder['DeliveryDate']), 0, 'R', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->MultiCell(25, 0, $Dispute, 0, 'R', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(25, 0, '-', 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(25, 0, '-', 0, 'R', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell(25, 0, $Difference, 0, 'R', 0, 0, '', '', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->MultiCell(25, 0, '-', 0, 'R', 0, 0, '', '', true);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->MultiCell(40, 0, '-', 0, 'R', 0, 1, '', '', true);
        $pdf->SetTextColor(0, 0, 0);
        
        //Black line
        $pdf->LN(3);
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+$pdf->getPageWidth()-20, $pdf->GetY());
        $pdf->LN(1);
        
        $GrandTotalAmount += $AmountSum;
        $GrandTotalAmountPayed += $AmountPayed;
        $GrandDifference += $Difference;
        $GrandReminderFees += $ReminderFees;
        $GrandNotificationFees += $NotificationFees;
        $GrandSemester += $n_Month;
        $GrandPercentualAmount += $LinePercentualAmount;
        $GrandOriginalAmount += $OriginalAmount;
        $GrandPayedAmountFee += $PayedAmountFee;
        $GrandPayedFees += $PayedFees;
        $GrandPayedPercentual += $PayedPercentual;
        
    ////////////////////////////////////////////////////////////////////////////////
        
        //Ogni 6 righe crea una nuova pagina e scrive le celle header e footer
        if($RownNumber%6 == 0 && (count($_POST['checkbox'])-$RownNumber) > 0){
            
        //Celle footer//////////////////////////////////////////////////////////////////////////////
            $pdf->setCellHeightRatio(1.5);
            
            $pdf->SetFont('helvetica', 'B', 8);
            
            $pdf->LN(4);
            
            $pdf->MultiCell(85, 0, 'NUMERO TOTALE SOLLECITI: '.$RownNumber, 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandTotalAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandNotificationFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandPercentualAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandReminderFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandOriginalAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
            $pdf->MultiCell(25, 0, '-', 1, 'R', 0, 1, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            
            $pdf->MultiCell(85, 0, 'Stampa del '.$CreationDate, 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandPayedAmountFee, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandPayedFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandPayedPercentual, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, $GrandSemester, 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandTotalAmountPayed, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
            $pdf->MultiCell(25, 0, '-', 1, 'R', 0, 1, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            
            $pdf->MultiCell(185, 0, '', 0, '', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, number_format($GrandDifference, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
            $pdf->MultiCell(25, 0, '-', 1, 'R', 0, 1, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
        ////////////////////////////////////////////////////////////////////////////////
            
        //Nuova pagina//////////////////////////////////////////////////////////////////////////////
            $page_format = "";
            
            $pdf->Header = false;
            
            $pdf->SetMargins(10,10,10);
            $pdf->AddPage('L', $page_format);
            
            
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            
            
            $pdf->writeHTML('<h2>COMUNE DI '.strtoupper($_SESSION['citytitle']).' - GESTIONE ANNO '.$_SESSION['year'].'</h2>', true, false, true, false, '');
            $pdf->writeHTML('<h3>ELENCO DI TUTTI I SOLLECITI</h3>', true, false, true, false, '');
            
            $pdf->LN(5);
        //////////////////////////////////////////////////////////////////////////////
            
        //Celle header//////////////////////////////////////////////////////////////////////////////
            $pdf->SetFont('helvetica', 'B', 8);
            
            $pdf->setCellPaddings(1, 0.5, 1, 0.5);
            $pdf->MultiCell(35, 0, 'Trasgressore', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(35, 0, 'Data definizione', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(15, 0, 'Anno', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'San.Am.Dov', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Spese Dovute', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Mag.Dov.', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Spese Solle.', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Tot. Dovuto', 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
            $pdf->MultiCell(25, 0, 'Tot. Dovuto Soll', 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            
            $pdf->setCellPadding(5.5);
            $pdf->MultiCell(40, 0, 'Ulteriori Dati', 1, 'C', 0, 0, '', '', true);
            
            $pdf->setCellPadding(0);
            $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
            
            $pdf->setCellPaddings(1, 0.5, 1, 0.5);
            $pdf->MultiCell(35, 0, 'N.Cronol+Targa', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(35, 0, 'Data versamento', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(15, 0, 'Integr.', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'San.Am.Pag', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Spese Pagate', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Mag.Pag.', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Sem.Magg.', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Tot. Pagato', 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
            $pdf->MultiCell(25, 0, 'Tot. Pagato Soll', 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            
            $pdf->setCellPadding(0);
            $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
            
            $pdf->setCellPaddings(1, 0.5, 1, 0.5);
            $pdf->MultiCell(35, 0, 'Data Verb. - Data Not.', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(35, 0, 'Dettaglio Pag.', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(15, 0, 'Stamp', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Data Calc.', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Dettag. Ricorso', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Elaboraz.(A) (M).', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'G.Agg.DataDef.', 1, 'R', 0, 0, '', '', true);
            $pdf->MultiCell(25, 0, 'Differenza', 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
            $pdf->MultiCell(25, 0, 'Differenza Soll', 1, 'R', 0, 0, '', '', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
            
            $pdf->setCellPadding(0);
            $pdf->MultiCell(0, 5, '', 0, '', 0, 1, '', '', true);
            
            $pdf->setCellPaddings(1, 0.5, 1, 0.5);
            $pdf->LN(7);
        //////////////////////////////////////////////////////////////////////////////
        }
        
    }
    
//Celle Footer//////////////////////////////////////////////////////////////////////////////
    $pdf->setCellHeightRatio(1.5);
    
    $pdf->SetFont('helvetica', 'B', 8);
    
    $pdf->LN(4);
    
    $pdf->MultiCell(85, 0, 'NUMERO TOTALE SOLLECITI: '.$RownNumber, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandTotalAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandNotificationFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPercentualAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandReminderFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandOriginalAmount, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
    $pdf->MultiCell(25, 0, '-', 1, 'R', 0, 1, '', '', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
    
    $pdf->MultiCell(85, 0, 'Stampa del '.$CreationDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPayedAmountFee, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPayedFees, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandPayedPercentual, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, $GrandSemester, 1, 'R', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandTotalAmountPayed, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
    $pdf->MultiCell(25, 0, '-', 1, 'R', 0, 1, '', '', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
    
    $pdf->MultiCell(185, 0, '', 0, '', 0, 0, '', '', true);
    $pdf->MultiCell(25, 0, number_format($GrandDifference, 2, ',', '.'), 1, 'R', 0, 0, '', '', true);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetLineStyle(array('color' => array(0, 0, 255)));
    $pdf->MultiCell(25, 0, '-', 1, 'R', 0, 1, '', '', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
//////////////////////////////////////////////////////////////////////////////

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
    
    $pdf->LN(5);
////////////////////////////////////////////////////////////////////////////////
    
    $pdf->SetPrintHeader(false);
    
   
    //SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE
    $TotalPages = $pdf->PageNo();
    
    for ($i=1; $i<=$TotalPages; $i++){
        $pdf->setPage($i, true);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x,$y);
        $pdf->SetFont('', '', 8, '', true);
        $pdf->SetTextColor(0, 0, 0);
    }
    //
    
    $FileName = 'printlist_create_reminder_f.pdf';
    
    if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid'])) {
        mkdir(FOREIGN_FINE."/".$_SESSION['cityid'], 0777);
    }
    $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/'.$FileName;
    
}

