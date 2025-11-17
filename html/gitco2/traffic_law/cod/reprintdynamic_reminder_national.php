<?php
require(CLS.'/cls_literal_number.php');

$n_LanguageId = 1;
$FormTypeId = 30;
//$a_Cities = unserialize(REMINDER_NATIONAL_CITIES);


//Flow
$flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND Year=".date('Y'));
$flow = mysqli_fetch_array($flows);

$int_FlowNumber = $flow['Number']+1;

$FileNameDoc = "Flusso_".$int_FlowNumber."_Sollec_Ita_".$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);
$DocumentationZip = $FileNameDoc.".zip";
$path = NATIONAL_FLOW."/".$_SESSION['cityid']."/";

//

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
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}

$ultimate = 0;
$NoElegibleFine = true;



//DA SCOMMENTARE DOPO TEST
// $a_LockTables =
// array( "LockedPage WRITE",
// );
// $rs->LockTables($a_LockTables);
// $rs_Locked = $rs->Select('LockedPage', "Title='createdynamic_reminder_national'");
// $r_Locked = mysqli_fetch_array($rs_Locked);

// if($r_Locked['Locked']==1){
//     $_SESSION['Message'] = "Pagina bloccata dall'utente ".$r_Locked['UserName'].".<br /> Attendere qualche minuto prima di creare i solleciti.";
    
//     header("location: ".$P);
//     DIE;
// } else {
//     $aUpdate = array(
//         array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
//         array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
//     );
//     $rs->Update('LockedPage',$aUpdate, "Title='createdynamic_reminder_national'");
    
    
// }
// $rs->UnlockTables();
//



$a_DocumentationFineZip = array();
$a_ReminderId = array();
$a_InvalidReminders = array();


$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$CurrentDate = date("Y-m-d");
$n_ReminderCount = 0;

$a_Lan = unserialize(LANGUAGE);



if(isset($_POST['checkbox'])) {
    
    $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
       
    $pdf->TemporaryPrint= $ultimate;
    $pdf->NationalFine= 1;
    $pdf->CustomerFooter = 0;
    
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Reminder');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 8));
    
    $pdf->SetMargins(10,10,10);
    
    
    
    
    foreach($_POST['checkbox'] as $ReminderId) {
        $rs_Reminder = $rs->SelectQuery(
            "SELECT
                FR.FineId ReminderFineId,
                FR.PaymentDays,
                FR.PaymentDate,
                FR.DaysFromNotificationDate,
                FR.DelayDays,
                FR.Semester,
                FR.Fee,
                FR.MaxFee,
                FR.HalfMaxFee,
                FR.TotalNotification,
                FR.Amount,
                FR.TotalAmount,
                FR.Percentual,
                FR.PercentualAmount,

                
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
                FH.FineId,
                FH.TrespasserId,
                FH.TrespasserTypeId,
                FH.ControllerId,
                FH.SendDate,
                FH.DeliveryDate,
            
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
            
                C.Title CityTitle,
            
                VT.TitleIta VehicleType
            
                FROM FineReminder FR
                JOIN Fine F ON FR.FineId = F.Id AND FR.PrintDate = F.ReminderDate
                JOIN FineHistory FH ON FR.FineId=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON FR.FineId=FA.FineId
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN sarida.City C on C.Id = F.Locality
                JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
                WHERE FR.Id=".$ReminderId
            );
        
        $r_Reminder = mysqli_fetch_assoc($rs_Reminder);
        
//         echo "<pre>";print_r($r_Reminder);echo "</pre>";
//         DIE;
        
        $FineId = $r_Reminder['ReminderFineId'];
        
            $ViolationTypeId = $r_Reminder['ViolationTypeId'];
            
            
            $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType1'];
            $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType2'];
            
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            
            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_Reminder['ArticleId'] . " AND Year=" . $r_Reminder['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
            
            
            $RuleTypeId = $r_RuleType['Id'];
            
            $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            
            $rs_ReminderLetter = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter FROM FineReminder WHERE FineId=".$FineId);
            $r_ReminderLetter = mysqli_fetch_array($rs_ReminderLetter);
            
            //$TotalNotification = $r_ReminderHistory['NotificationFee'];
            $n_ReminderLetter = $r_ReminderLetter['ReminderLetter'];
            
            
               $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;
            
            
               $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
            
            
//             $TotalAmount = 0;
//             $PercentualAmount = 0;
               $NotificationFee = $r_Customer['NationalReminderNotificationFee'];
               
               if ($r_Customer['IncreaseNationalNotificationFee'] != 0){
                   $rs_RemindersNumber = $rs->Select("FineReminder", "Id=$FineId");
                   $n_Reminders = mysqli_num_rows($rs_RemindersNumber);
                   $NotificationFee += $r_Customer['NationalReminderNotificationFee']*$n_Reminders;
               }
            
            
               $TaxCode = trim($r_Reminder['TaxCode']);
            
//             $Fee = $r_Reminder['Fee'];
            
//             $MaxFee = ($r_Customer['ReminderAdditionalFee']==1) ? ($r_Reminder['MaxFee']*FINE_MAX)-$Fee : 0.00;
//             $HalfMaxFee = $r_Reminder['MaxFee']*FINE_MAX;
//             $TotalNotification += $r_Reminder['NotificationFee'] + $r_Reminder['ResearchFee'];
            
            
//             $TotalAmount = $Fee + $MaxFee + $TotalNotification;
            
//             $rs_Payment = $rs->SelectQuery("SELECT MAX(PaymentDate) PaymentDate, SUM(Amount) Amount FROM FinePayment WHERE FineId=".$FineId);
//             $r_Payment = mysqli_fetch_array($rs_Payment);
//             $Amount = $r_Payment['Amount'];
//             //$PaymentDate = DateOutDB($r_Payment['PaymentDate']);
            
//             if ($Amount>0) {
                
//                 $rs_FineNotification = $rs->SelectQuery("SELECT NotificationDate FROM FineNotification WHERE FineId=".$FineId);
//                 $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
                
//                 $n_Day = DateDiff("D", $r_FineNotification['NotificationDate'], $r_Payment['PaymentDate'])+1;
                
//                 if($n_Day<=FINE_DAY_LIMIT_REDUCTION){
                    
//                     $flt_DiffFee = $Amount - ($Fee * FINE_PARTIAL) + $TotalNotification;
                    
                    
//                     //                 $str_ReminderType = '
//                     //                     di euro '. NumberDisplay($Amount).' eseguito in data '. DateOutDB($r_Payment['PaymentDate']).' risulta effettuato entro '. $n_Day .' giorni dalla notifica ma per un importo inferiore al dovuto di euro '. NumberDisplay($flt_DiffFee);
//                 } else {
//                     $flt_DiffFee = $Fee + $TotalNotification;
//                     //                 $str_ReminderType = '
//                     //                     di euro '. NumberDisplay($Amount).' eseguito in data '. DateOutDB($r_Payment['PaymentDate']).' risulta effettuato oltre 5 giorni dalla notifica del verbale e pertanto (la SV/Codesta Ditta) avrebbe dovuto pagare euro '. NumberDisplay($flt_DiffFee);
//                 }
                
//             } else {
//                 //$str_ReminderType = 'risulta omesso';
//             }
            
            
//             $Percentual =  $r_Customer['NationalPercentualReminder'];
            
//             if($Percentual>0){
                
//                 $d_DateLimit = date('Y-m-d', strtotime($r_Reminder['DeliveryDate']. ' + '.FINE_DAY_LIMIT.' days'));
                
//                 $n_Month = floor(DateDiff("M", $d_DateLimit, $CreationDate)/6);
                
//                 for($i=1; $i<=$n_Month; $i++){
//                     $PercentualAmount += $TotalAmount*$Percentual/100;
//                 }
                
//                 $TotalAmount += $PercentualAmount;
//             }
            
//             $TotalAmount += $NotificationFee;
//             $TotalAmount -= $Amount;
            
            //Se l'ammontare totale è negativo il il verbale non è elegibile alla creazione del sollecito, altrimenti procedi
            if ($r_Reminder['TotalAmount'] < 0){
                $a_InvalidReminders[] = $FineId." - Targa: ".$r_Reminder['VehiclePlate'];
            } else {
                $NoElegibleFine = false;
                
                $page_format = "";
                if($ultimate && $n_ReminderCount>0){
                    $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
                    
                    $pdf->TemporaryPrint= $ultimate;
                    $pdf->NationalFine= 1;
                    $pdf->CustomerFooter = 0;
                    
                    
                    $pdf->SetCreator(PDF_CREATOR);
                    $pdf->SetAuthor($_SESSION['citytitle']);
                    $pdf->SetTitle('Reminder');
                    $pdf->SetSubject('');
                    $pdf->SetKeywords('');
                    $pdf->setHeaderFont(array('helvetica', '', 8));
                    $pdf->setFooterFont(array('helvetica', '', 8));
                    
                    
                    
                }
                
                    
                    //$pdf->Temporary();
                    $pdf->RightHeader = false;
                    
                    
                    $page_format = array('Rotate'=>45);
                    $pdf->SetMargins(10,10,10);
                    $pdf->AddPage('P', $page_format);
                    
                    
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetTextColor(0, 0, 0);
                    
                    //INTESTAZIONE
                    $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);
                    
                    $ManagerName = $r_Customer['ManagerName'];
                    $pdf->customer = $ManagerName;
                    
                    
                    $pdf->SetFont('helvetica', '', 10, '', true);
                    if(strlen($r_Customer['ManagerName'])>22){
                        $pdf->writeHTMLCell(60, 0, 30, '', '<h3>'.$r_Customer['ManagerName'].'</h3>', 0, 0, 1, true, 'L', true);
                        $pdf->LN(10);
                        
                    }else{
                        
                        $pdf->writeHTMLCell(60, 0, 30, '', '<h3>'.$r_Customer['ManagerName'].'</h3>', 0, 0, 1, true, 'L', true);
                        $pdf->LN(5);
                    }
                    
                    $pdf->writeHTMLCell(130, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                    $pdf->writeHTMLCell(130, 0, 30, '', "GESTIONE: SARIDA S.R.L. - P.IVA 01338160995", 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                    $pdf->writeHTMLCell(130, 0, 30, '', "Sede in: via M. Vattuone 9 - 16039 Sestri Levante (GE)", 0, 0, 1, true, 'L', true);
                    $pdf->LN(4);
                    $pdf->writeHTMLCell(130, 0, 30, '', "Tel: 0039 0185 1830468/9 - Mail: informazioni@poliziamunicipale-online.it", 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);
                    $pdf->writeHTMLCell(130, 0, 10, '', "Sollecito nr: ".$str_ReminderCode, 0, 0, 1, true, 'L', true);
                    $pdf->LN(10);
                    
                    //FINE INTESTAZIONE
                    
                    //Prende il contenuto del testo
                    $forms = $rs->Select('FormDynamic',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
                    $form = mysqli_fetch_array($forms);
                    
                    $Content = StringOutDB($form['Content']);
                    
                    //SOTTOTESTI
                    $EmptyPregMatch = false;
                    //Continua a cercare per variabili di sottotesti da sostituire finchè non trova nulla
                    while(!$EmptyPregMatch){
                        $a_Variables = array();
                        $a_Matches = array();
                        
                        if(preg_match_all("/\{\{.*?\}\}/", $Content, $a_Variables) > 0){
                            $a_Matches = $a_Variables[0];
                            
                            foreach ($a_Matches as $var){
                                
                                $a_Types = array();
                                $rs_variable = $rs->Select('FormVariable',"Id='$var' AND FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId." And NationalityId=1");
                                
                                while ($r_variable = mysqli_fetch_array($rs_variable)){
                                    $a_Types[$r_variable['Type']] = $r_variable['Content'];
                                }
                                
                                
                                //Sottotesto maggiorazione
                                if ($var == "{{Surcharge}}"){
                                    $str_Surcharge = "";
                                    if ($r_Reminder['Percentual'] <= 0){
                                        $str_Surcharge .= $a_Types[1];
                                    }
                                    $Content = str_replace("{{Surcharge}}", $str_Surcharge, $Content);
                                }
                                //Sottotesto oggetto
                                if ($var == "{{ReminderObject}}"){
                                    $str_ReminderObject = "";
                                    if ($r_Reminder['Amount']>=0.01) {
                                        if($r_Reminder['DaysFromNotificationDate']<=FINE_DAY_LIMIT_REDUCTION){
                                            $str_ReminderObject = $a_Types[2];
                                        } else $str_ReminderObject = $a_Types[3];
                                    } else $str_ReminderObject = $a_Types[1];
                                    $Content = str_replace("{{ReminderObject}}", $str_ReminderObject, $Content);
                                }
                                //Sottotesto contenuto
                                if ($var == "{{ReminderContent}}"){
                                    $str_ReminderContent = "";
                                    if ($r_Reminder['Amount']>=0.01) {
                                        if($r_Reminder['DaysFromNotificationDate']<=FINE_DAY_LIMIT_REDUCTION){
                                            $str_ReminderContent = $a_Types[2];
                                        } else $str_ReminderContent = $a_Types[3];
                                    } else $str_ReminderContent = $a_Types[1];
                                    $Content = str_replace("{{ReminderContent}}", $str_ReminderContent, $Content);
                                }
                                //Sottotesto termini
                                if ($var == "{{ReminderPaymentTerms}}"){
                                    $str_ReminderPaymentTerms = "";
                                    if ($r_Reminder['Amount']>=0.01) {
                                        if($r_Reminder['DaysFromNotificationDate']<=FINE_DAY_LIMIT_REDUCTION){
                                            $str_ReminderPaymentTerms = $a_Types[2];
                                        } else $str_ReminderPaymentTerms = $a_Types[3];
                                    } else $str_ReminderPaymentTerms = $a_Types[1];
                                    $Content = str_replace("{{ReminderPaymentTerms}}", $str_ReminderPaymentTerms, $Content);
                                }
                                else $Content = str_replace($var, $a_Types[1], $Content);
                                
                            }
                        } else $EmptyPregMatch = true;
                    }
                    //
                    
                    //Sostituisce le variabili
//                     $rs_PaymentDays = $rs->SelectQuery("SELECT DataPaymentNationalPaymentDayReminder FROM V_CustomerParameter WHERE CityId='".$r_Reminder['CityId']."'");
//                     $PaymentDays = mysqli_fetch_array($rs_PaymentDays)['DataPaymentNationalPaymentDayReminder'];
                    //$Content = str_replace("{Image}", '<img src="'.$_SESSION['blazon'].'" width="45" height="69">', $Content); //NOTA: I TAG IMG CON ATTRIBUTO src='' NON FUNZIONANO, USARE src="" (doppie graffe)
                    $Content = str_replace("{PaymentDays}", $r_Reminder['PaymentDays'], $Content);
                    $Content = str_replace("{PaymentDate}", $r_Reminder['PaymentDate'], $Content);
                    $Content = str_replace("{DaysFromNotificationDate}", $r_Reminder['DaysFromNotificationDate'], $Content);
                    $Content = str_replace("{DelayDay}", $r_Reminder['DelayDays'], $Content);
                    
                    $Content = str_replace("{TrespasserName}", StringOutDB($r_Reminder['CompanyName']) . ' ' . StringOutDB($r_Reminder['Surname']) . ' ' . StringOutDB($r_Reminder['Name']), $Content);
                    $Content = str_replace("{TrespasserGenre}", $a_GenreLetter[$r_Reminder['Genre']], $Content);
                    $Content = str_replace("{TrespasserCity}", $r_Reminder['City'], $Content);
                    $Content = str_replace("{TrespasserProvince}", $r_Reminder['Province'], $Content);
                    $Content = str_replace("{TrespasserAddress}", $r_Reminder['TrespasserAddress'], $Content);
                    $Content = str_replace("{TrespasserZip}", $r_Reminder['ZIP'], $Content);
                    
                    $Content = str_replace("{TaxCode}", $TaxCode,$Content);
                    
                    $Content = str_replace("{FineDate}", DateOutDB($r_Reminder['FineDate']),$Content);
                    $Content = str_replace("{FineTime}", TimeOutDB($r_Reminder['FineTime']),$Content);
                    $Content = str_replace("{CurrentDate}", $CreationDate, $Content);
                    //$Content = str_replace("{ReminderType}", $str_ReminderType, $Content);
                    $Content = str_replace("{Address}", StringOutDB($r_Reminder['Address']), $Content);
                    $Content = str_replace("{VehiclePlate}", StringOutDB($r_Reminder['VehiclePlate']), $Content);
                    $Content = str_replace("{VehicleType}", StringOutDB($r_Reminder['VehicleType']), $Content);
                    
                    $Content = str_replace("{Code}", $r_Reminder['Code'],$Content);
                    $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
                    $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
                    $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);
                    
                    $Content = str_replace("{Fee}", NumberDisplay($r_Reminder['Fee']), $Content);
                    $Content = str_replace("{MaxFee}", NumberDisplay($r_Reminder['MaxFee']), $Content);
                    $Content = str_replace("{HalfMaxFee}", NumberDisplay($r_Reminder['HalfMaxFee']), $Content);
                    $Content = str_replace("{TotalNotification}", NumberDisplay($r_Reminder['TotalNotification']), $Content);
                    $Content = str_replace("{Amount}", NumberDisplay($r_Reminder['Amount']), $Content);
                    $Content = str_replace("{TotalAmount}", NumberDisplay($r_Reminder['TotalAmount']), $Content);
                    $Content = str_replace("{Percentual}", NumberDisplay($r_Reminder['Percentual']), $Content);
                    $Content = str_replace("{PercentualAmount}", NumberDisplay($r_Reminder['PercentualAmount']), $Content);
                    $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);
                    
                    $Content = str_replace("{Locality}", $r_Reminder['CityTitle'],$Content);
                    $Content = str_replace("{CityTitle}", $r_Reminder['CityId'],$Content);
                    
                    $Content = str_replace("{TrespasserId}", $r_Reminder['TrespasserId'],$Content);
                    $Content = str_replace("{SendDate}", DateOutDB($r_Reminder['SendDate']),$Content);
                    $Content = str_replace("{DeliveryDate}", DateOutDB($r_Reminder['DeliveryDate']),$Content);
                    
                    $Content = str_replace("{BankOwner}", $r_Customer['NationalReminderBankOwner'],$Content);
                    $Content = str_replace("{BankName}", $r_Customer['NationalReminderBankName'],$Content);
                    $Content = str_replace("{BankAccount}", $r_Customer['NationalReminderBankAccount'],$Content);
                    $Content = str_replace("{BankSwift}", $r_Customer['NationalReminderBankSwift'],$Content);
                    $Content = str_replace("{BankIban}", $r_Customer['NationalReminderBankIban'],$Content);
                    $Content = str_replace("{ManagerDataEntryName}",$r_Customer['ManagerDataEntryName'], $Content);
                    $Content = str_replace("{ManagerProcessName}",$r_Customer['ManagerProcessName'], $Content);
                    
                    $Content = str_replace("{Date}","Sestri Levante, ".$CreationDate,$Content);
                    
                    $Content = str_replace("{ReminderCode}",$str_ReminderCode, $Content);
                    //
                    
                    
                    $pdf->SetFont('helvetica', '', 8);
                    
                    //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
                    $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
                    $TCPDF_pagebreak = '<br pagebreak="true" />';
                    preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
                    //
                    
                    $pdf->SetAutoPageBreak(true, 0);
                    $pdf->SetPrintHeader(false);
                    
                    $pdf->writeHTML($Content, true, false, true, false, '');
                    
                    //PAGOPA
                    if($r_Customer['PagoPAPayment']==1){
                        $style = array(
                            'border' => 1,
                            'vpadding' => 'auto',
                            'hpadding' => 'auto',
                            'fgcolor' => array(0,0,0),
                            'bgcolor' => false, //array(255,255,255)
                            'module_width' => 1, // width of a single module in points
                            'module_height' => 1 // height of a single module in points
                        );
                        
                        if ($r_ArticleTariff['ReducedPayment']) {
                            $str_PaymentDay1 = "Pagamento entro 5gg dalla notif.";
                            $str_PaymentDay2 = "Pagamento dopo 5gg ed entro 60gg dalla notif.";
                            
                        } else {
                            $str_PaymentDay1 = "Pagamento entro 60gg dalla notif.";
                            $str_PaymentDay2 = "Pagamento dopo 60gg ed entro 6 mesi dalla notif.";
                        }
                        
                        //$url_PagoPAPage = "https://nodopagamenti-test.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";
                        $url_PagoPAPage = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/pagamento-diretto-immediato?iuv=";
                        
                        if($r_Reminder['PagoPA1']!='' && $r_Reminder['PagoPA2']!=''){
                            $pdf->write2DBarcode($url_PagoPAPage.$r_Reminder['PagoPA1'], 'QRCODE,H', 60, 240, 30, 30, $style, 'N');
                            $pdf->writeHTMLCell(30, 0, 60, 271, $str_PaymentDay1, 0, 0, 1,true, true, 'C', true);
                            
                            
                            $pdf->write2DBarcode($url_PagoPAPage.$r_Reminder['PagoPA2'], 'QRCODE,H', 120, 240, 30, 30, $style, 'N');
                            $pdf->writeHTMLCell(30, 0, 120, 271, $str_PaymentDay2, 0, 0, 1,true, true, 'C', true);
                        }
                    }
                    //FINE PAGOPA
                    
                
                if($ultimate){
                    
                    $RndCode = "";
                    for($i=0;$i<5;$i++){
                        $n = rand(1, 24);
                        $RndCode .= substr($strCode,$n,1);
                        $n = rand(0, 9);
                        $RndCode .= $n;
                    }
                    
                    if($n_ReminderCount==0) $rs->Start_Transaction();
                    
                    $Documentation = str_replace("/","-", $str_ReminderCode)."_".date("Y-m-d")."_".$_SESSION['cityid']."_".$RndCode.".pdf";
                    $a_DocumentationFineZip[] = $Documentation;
                    $a_ReminderId[] = $FineId;
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
                

                    $page_format = array('Rotate'=>-90);
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                    $pdf->SetMargins(0, 0, 0);
                    $pdf->SetAutoPageBreak(false);
                    
                    $pdf->AddPage('L', $page_format);
                    $pdf->crea_bollettino();
                    
                    //Calcoli quinto campo
                    $a_FifthField = array("Table" => 1, "Id" => $FineId);
                    $a_FifthField['PaymentType'] = ($r_ArticleTariff['ReducedPayment']) ? 0 : 1;
                    $a_FifthField['DocumentType'] = 3; //DocumentType a 3 indica il sollecito
                    $str_FifthField = SetFifthField($a_FifthField);
                    //
                    
                    $a_Address = array();
                    $a_Address['Riga1'] = $r_Reminder['TrespasserAddress'];
                    $a_Address['Riga2'] = '';
                    $a_Address['Riga3'] = $r_Reminder['ZIP'];
                    $a_Address['Riga4'] = $r_Reminder['City']. ' '."(".$r_Reminder['Province'].')';
                    
                    
                    
                    $NW = new CLS_LITERAL_NUMBER();
                    $numeroLetterale = $NW->converti_numero_bollettino($r_Reminder['TotalAmount']);
                    $ConvertedTotalAmount = number_format((float)$r_Reminder['TotalAmount'], 2, ',', '');
                    $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'],$str_FifthField,$ConvertedTotalAmount,'si',$r_Customer['NationalReminderBankAccount']);
                    $pdf->iban_bollettino($r_Customer['NationalReminderBankIban']);
                    $pdf->intestatario_bollettino(substr($r_Customer['NationalReminderBankOwner'], 0, 50));
                    $pdf->causale_bollettino('pagamento sollecito '. $str_ReminderCode,'verbale cron '.$r_Reminder['ProtocolId'].'/'.$r_Reminder['ProtocolYear'].'/'.$str_ProtocolLetter);
                    $pdf->zona_cliente_bollettino(substr($r_Reminder['CompanyName'].' '.$r_Reminder['Surname'].' '.$r_Reminder['Name'],0,35),$a_Address);
                    $pdf->importo_in_lettere_bollettino($numeroLetterale);
                    $pdf->set_quinto_campo($r_Customer['NationalPostalType'], $str_FifthField);
                    
                
                
                $n_ReminderCount++;
                
                if ($ultimate){
                    
//                     $rs_ReminderHistory = $rs->Select('FineReminder', "Id=$ReminderId");
//                     $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);
                    
//                     $a_Insert = array(
//                         array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
//                         array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
//                         array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_ReminderHistory['TrespasserId'],'settype'=>'int'),
//                         array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_ReminderHistory['TrespasserTypeId'],'settype'=>'int'),
//                         array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['NotificationFee'],'settype'=>'flt'),
//                         array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$r_ReminderHistory['PrintDate'],'settype'=>'date'),
//                         array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$r_ReminderHistory['Documentation']),
//                         array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>$r_ReminderHistory['FlowDate'],'settype'=>'date'),
//                         array('field'=>'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $r_ReminderHistory['FlowNumber'], 'settype' => 'int'),
                        
//                         array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>$r_ReminderHistory['PaymentDays'],'settype'=>'int'),
//                         array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$r_ReminderHistory['PaymentDate'],'settype'=>'date'),
//                         array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>$r_ReminderHistory['DaysFromNotificationDate'],'settype'=>'int'),
//                         array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>$r_ReminderHistory['DelayDays'],'settype'=>'int'),
//                         array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>$r_ReminderHistory['Semester'],'settype'=>'int'),
//                         array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['Fee'],'settype'=>'flt'),
//                         array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['MaxFee'],'settype'=>'flt'),
//                         array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['HalfMaxFee'],'settype'=>'flt'),
//                         array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['TotalNotification'],'settype'=>'flt'),
//                         array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['Amount'],'settype'=>'flt'),
//                         array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['TotalAmount'],'settype'=>'flt'),
//                         array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['Percentual'],'settype'=>'flt'),
//                         array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>$r_ReminderHistory['PercentualAmount'],'settype'=>'flt'),
//                     );
                    
//                     $rs->Insert('FineReminderHistory',$a_Insert);
                    
                    $a_Update = array(
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Reminder['TrespasserId'],'settype'=>'int'),
                        array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_Reminder['TrespasserTypeId'],'settype'=>'int'),
                        array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$NotificationFee,'settype'=>'flt'),
                        array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$CreationDate,'settype'=>'date'),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                        array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"),'settype'=>'date'),
                        array('field'=>'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                        
                        array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>$r_Reminder['PaymentDays'],'settype'=>'int'),
                        array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$r_Reminder['PaymentDate'],'settype'=>'date'),
                        array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>$r_Reminder['DaysFromNotificationDate'],'settype'=>'int'),
                        array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>$r_Reminder['DelayDays'],'settype'=>'int'),
                        array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>$r_Reminder['Semester'],'settype'=>'int'),
                        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_Reminder['Fee'],'settype'=>'flt'),
                        array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_Reminder['MaxFee'],'settype'=>'flt'),
                        array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>$r_Reminder['HalfMaxFee'],'settype'=>'flt'),
                        array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>$r_Reminder['TotalNotification'],'settype'=>'flt'),
                        array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$r_Reminder['Amount'],'settype'=>'flt'),
                        array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>$r_Reminder['TotalAmount'],'settype'=>'flt'),
                        array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>$r_Reminder['Percentual'],'settype'=>'flt'),
                        array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>$r_Reminder['PercentualAmount'],'settype'=>'flt'),
                    );
                    $rs->Update('FineReminder',$a_Update,"Id=$ReminderId");
                    
                    
                    
                    $a_Update = array(
                        array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$CreationDate,'settype'=>'date'),
                    );
                    $rs->Update('Fine',$a_Update, 'Id='.$FineId);
                    
                    
                    $a_Insert = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                        array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>30),
                    );
                    $rs->Insert('FineDocumentation',$a_Insert);
                    
                    
                    
                    $FileName = $Documentation;
                    
                    $pdf->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
    
                    
                } else $FileName = 'export_dynamic_reminder_n.pdf';
            }
            
    }
    
    //Se si ha selezionato almeno un verbale elegibile alla creazione del sollecito procedi
    if (!$NoElegibleFine){
        //SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE
        $TotalPages = $pdf->PageNo();
        
        for ($i=1; $i<=$TotalPages; $i++){
            $pdf->setPage($i, true);
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Temporary();
            $pdf->SetXY($x,$y);
            $pdf->SetFont('', '', 8, '', true);
            $pdf->SetTextColor(0, 0, 0);
        }
        //
        
        if ($ultimate){
            
            //Flow
            $Zone0Number=count($_POST['checkbox']);
            
            $aInsert = array(
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
                array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
                array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>3,'settype'=>'int'),
                array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int'),
                array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
                array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
                array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
                array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$n_PrinterId,'settype'=>'int'),
                array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
            );
            
            $rs->Insert('Flow',$aInsert);
            
            $zip = new ZipArchive();
            if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($a_DocumentationFineZip as $FineId => $DocumentName){
                    $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName, $DocumentName);
                }
                
                //MERCURIO/POSTA
                if ($n_PrinterId == 1 || $n_PrinterId == 0){
                    $zip->addFile($tempCSV, $Documentation);
                }
                $zip->close();
                
                //MERCURIO/POSTA
                if ($n_PrinterId == 1 || $n_PrinterId == 0){
                    unlink($tempCSV);
                }
                
                $_SESSION['Documentation'] = $MainPath.'/doc/national/flow/'.$_SESSION['cityid'].'/'.$DocumentationZip;
            }
            //
            
            $str_Definitive = "Stampa definitiva avvenuta con successo!";
            for($i=0; $i<count($a_DocumentationFineZip); $i++){
                if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid']."/".$a_ReminderId[$i])) {
                    mkdir(NATIONAL_FINE."/".$_SESSION['cityid']."/". $a_ReminderId[$i], 0777);
                }
                copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_ReminderId[$i] . "/" . $a_DocumentationFineZip[$i]);
                unlink(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);
            }
            
            
            $FileName = $_SESSION['cityid']."_".date("Y-m-d_H-i-s").".pdf";
           
            $_SESSION['Message'] = $str_Definitive;
            
        }else{
            if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid'])) {
                mkdir(NATIONAL_FINE."/".$_SESSION['cityid'], 0777);
            }
            $pdf->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/'.$FileName;
        }
    }
    
}

$rs->UnlockTables();
$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);
$rs->Update('LockedPage',$aUpdate, "Title='createdynamic_reminder_national'");
$rs->End_Transaction();