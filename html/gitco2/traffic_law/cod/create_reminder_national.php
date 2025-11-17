<?php
require(CLS.'/cls_literal_number.php');

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='create_reminder_national'");
$r_Locked = mysqli_fetch_assoc($rs_Locked);

if ($r_Locked['Locked'] == 1) {
    $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
    header("location: ".$P);
    DIE;
} else {
    $UpdateLockedPage = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
    );
    $rs->Update('LockedPage', $UpdateLockedPage, "Title='create_reminder_national'");
}
$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$n_LanguageId = 1;
$FormTypeId = 30;


$FinePDFList = $r_Customer['FinePDFList'];


$str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}



$ultimate = CheckValue('ultimate','n');

$a_DocumentationFineZip = array();
$a_ReminderId = array();


$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$CurrentDate = date("Y-m-d");
$n_ReminderCount = 0;

$a_Lan = unserialize(LANGUAGE);



if(isset($_POST['checkbox'])) {


    $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);

    if($FinePDFList)  $pdf_union = new FPDI();

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




    foreach($_POST['checkbox'] as $FineId) {
        $rs_Reminder = $rs->SelectQuery(
            "SELECT 
                F.Id,
                F.ProtocolId,
                F.ProtocolYear,
                F.Locality,
                F.FineDate,
                F.FineTime,
                F.CityId,
                
                FH.NotificationTypeId,
                FH.FineId,
                FH.TrespasserId,
                FH.TrespasserTypeId,
                FH.NotificationFee,
                FH.ResearchFee,
                FH.ControllerId,
                FH.SendDate,
                FH.DeliveryDate,

                FA.Fee,
                FA.MaxFee,
                FA.ViolationTypeId,
                
                T.Id TrespasserId,
                T.Genre,
                T.CompanyName,
                T.Surname,
                T.Name,
                T.Address,
                T.ZIP,
                T.City,
                T.Province,
                T.TaxCode,
                T.ZoneId,
                T.LanguageId,
              
                C.Title CityTitle
              
                FROM Fine F 
                JOIN FineHistory FH ON F.Id=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN sarida.City C on C.Id = F.Locality
                WHERE F.Id=".$FineId
        );


        $r_Reminder = mysqli_fetch_array($rs_Reminder);













        $ViolationTypeId = $r_Reminder['ViolationTypeId'];


        $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType1'];
        $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType2'];

        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
        $r_RuleType = mysqli_fetch_array($rs_RuleType);


        $RuleTypeId = $r_RuleType['Id'];

        $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;

        $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId);
        $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);

        $TotalNotification = $r_ReminderHistory['NotificationFee'];
        $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];


        $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;


        $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];


        $TotalAmount = 0;
        $PercentualAmount = 0;
        $NotificationFee = 2;


        $TaxCode = trim($r_Reminder['TaxCode']);

        $Fee = $r_Reminder['Fee'];

        $MaxFee = ($r_Customer['ReminderAdditionalFee']==1) ? ($r_Reminder['MaxFee']*FINE_MAX)-$Fee : 0.00;
        $TotalNotification += $r_Reminder['NotificationFee'] + $r_Reminder['ResearchFee'];

        $TotalAmount = $Fee + $MaxFee + $TotalNotification;

        $rs_Payment = $rs->SelectQuery("SELECT MAX(PaymentDate) PaymentDate, SUM(Amount) Amount FROM FinePayment WHERE FineId=".$FineId);
        $r_Payment = mysqli_fetch_array($rs_Payment);
        $Amount = $r_Payment['Amount'];

        if ($Amount>0) {

            $rs_FineNotification = $rs->SelectQuery("SELECT NotificationDate FROM FineNotification WHERE FineId=".$FineId);
            $r_FineNotification = mysqli_fetch_array($rs_FineNotification);

            $n_Day = DateDiff("D", $r_FineNotification['NotificationDate'], $r_Payment['PaymentDate'])+1;

            if($n_Day<=5){

                $flt_DiffFee = $Amount - ($Fee * FINE_PARTIAL) + $TotalNotification;


                $str_ReminderType = '
                    di euro '. NumberDisplay($Amount).' eseguito in data '. DateOutDB($r_Payment['PaymentDate']).' risulta effettuato entro '. $n_Day .' giorni dalla notifica ma per un importo inferiore al dovuto di euro '. NumberDisplay($flt_DiffFee);
            } else {
                $flt_DiffFee = $Fee + $TotalNotification;
                $str_ReminderType = '
                    di euro '. NumberDisplay($Amount).' eseguito in data '. DateOutDB($r_Payment['PaymentDate']).' risulta effettuato oltre 5 giorni dalla notifica del verbale e pertanto (la SV/Codesta Ditta) avrebbe dovuto pagare euro '. NumberDisplay($flt_DiffFee);
            }

        } else {
            $str_ReminderType = 'risulta omesso';
        }



        $Percentual =  $r_Customer['NationalPercentualReminder'];

        if($Percentual>0){

            $d_DateLimit = date('Y-m-d', strtotime($r_Reminder['DeliveryDate']. ' + '.FINE_DAY_LIMIT.' days'));

            $n_Month = floor(DateDiff("M", $d_DateLimit, $CurrentDate)/6);

            for($i=1; $i<=$n_Month; $i++){
                $PercentualAmount += $TotalAmount*$Percentual/100;
            }

            $TotalAmount += $PercentualAmount;
        }

        $TotalAmount += $NotificationFee;
        $TotalAmount -= $Amount;





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

        
        
        $pdf->Temporary();
        $pdf->RightHeader = false;


        $page_format = array('Rotate'=>45);
        $pdf->SetMargins(10,10,10);
        $pdf->AddPage('P', $page_format);


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

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


        



        

        $forms = $rs->Select('Form',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
        $form = mysqli_fetch_array($forms);

        $Content = $form['Content'];


        $Content = str_replace("{TaxCode}", $TaxCode,$Content);

        $Content = str_replace("{FineDate}", DateOutDB($r_Reminder['FineDate']),$Content);
        $Content = str_replace("{FineTime}", TimeOutDB($r_Reminder['FineTime']),$Content);
        $Content = str_replace("{CurrentDate}", $CreationDate, $Content);
        $Content = str_replace("{ReminderType}", $str_ReminderType, $Content);

        $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
        $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
        $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);

        $Content = str_replace("{Fee}", NumberDisplay($Fee), $Content);
        $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
        $Content = str_replace("{TotalNotification}", NumberDisplay($TotalNotification), $Content);
        $Content = str_replace("{Amount}", NumberDisplay($Amount), $Content);
        $Content = str_replace("{TotalAmount}", NumberDisplay($TotalAmount), $Content);
        $Content = str_replace("{Percentual}", NumberDisplay($Percentual), $Content);
        $Content = str_replace("{PercentualAmount}", NumberDisplay($PercentualAmount), $Content);
        $Content = str_replace("{NotificationFee}", NumberDisplay($NotificationFee), $Content);

        $Content = str_replace("{Locality}", $r_Reminder['CityTitle'],$Content);
        $Content = str_replace("{CityTitle}", $r_Reminder['CityId'],$Content);

        $Content = str_replace("{TrespasserId}", $r_Reminder['TrespasserId'],$Content);
        $Content = str_replace("{SendDate}", DateOutDB($r_Reminder['SendDate']),$Content);
        $Content = str_replace("{DeliveryDate}", DateOutDB($r_Reminder['DeliveryDate']),$Content);

        $Content = str_replace("{BankOwner}", $r_Customer['NationalBankOwner'],$Content);
        $Content = str_replace("{BankName}", $r_Customer['NationalBankName'],$Content);
        $Content = str_replace("{BankAccount}", $r_Customer['NationalBankAccount'],$Content);
        $Content = str_replace("{BankSwift}", $r_Customer['NationalBankSwift'],$Content);
        $Content = str_replace("{BankIban}", $r_Customer['NationalBankIban'],$Content);

        $Content = str_replace("{Date}","Sestri Levante, ".$CreationDate,$Content);





        

        $Content = str_replace("{ReminderCode}",$str_ReminderCode, $Content);
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
            $a_ReminderId[] = $r_Reminder['Id'];

        }




        $aMainPart = explode("<page>",$Content);
        $aRow = explode("<row>",$aMainPart[1]);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'L', true);
        $pdf->LN(10);


        
        $pdf->writeHTMLCell(100, 0, 110, '', '<h4>'.$a_GenreLetter[$r_Reminder['Genre']]." ".substr($r_Reminder['CompanyName'].' '.$r_Reminder['Surname'].' '.$r_Reminder['Name'],0,35).'</h4>', 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $r_Reminder['Address'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $r_Reminder['ZIP'].' '.$r_Reminder['City']. ' '."(".$r_Reminder['Province'].")", 0, 0, 1, true, 'L', true);
        $pdf->LN(20);


        $pdf->SetFont('helvetica', '', 8);



        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'L', true);
        $pdf->LN();
        if($TaxCode!=""){
            $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[3]), 0, 0, 1, true, 'L', true);
        }
        $pdf->LN();
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[4]), 0, 0, 1, true, 'L', true);
        $pdf->LN(20);



        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[5]), 0, 0, 1, true, 'J', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[6]), 0, 0, 1, true, 'J', true);
        $pdf->LN(10);
        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'C', true);
        $pdf->LN();



        for($i=8; $i<=14; $i++){
            $aCol = explode("<col>",$aRow[$i]);
            $y = $pdf->getY();

            if(($Percentual>0 AND $i==12) || $i!=12){
                $pdf->writeHTMLCell(120, 5, 10, $y, utf8_encode($aCol[0]), 0, 0, 1, true, 'L', true);
                $pdf->writeHTMLCell(80, 5, 105, $y, utf8_encode($aCol[1]), 0, 0, 1, true, 'R', true);
            }

            $pdf->LN(5);
            $y = $pdf->getY();
            if($i==13) $pdf->Line(7, $y , 200, $y);

        }

        $pdf->LN(5);


        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[15]), 0, 0, 1, true, 'J', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[16]), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[17]), 0, 0, 1, true, 'L', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[18]), 0, 0, 1, true, 'L', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(100, 0, 80, '', utf8_encode($aRow[19]), 0, 0, 1, true, 'C', true);
        $pdf->LN();
        $pdf->writeHTMLCell(100, 0, 80, '', utf8_encode($aRow[20]), 0, 0, 1, true, 'C', true);
        $pdf->LN();







        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        ////
        ////
        ////    Fine page 2
        ////
        ////
        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $aRow = explode("<row>",$aMainPart[2]);

        $pdf->SetMargins(10,10,10);
        $pdf->AddPage('P', $page_format);


        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[1]), 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[2]), 0, 0, 1, true, 'J', true);
        $pdf->LN(30);


        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[3]), 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[4]), 0, 0, 1, true, 'J', true);
        $pdf->LN(30);


        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[5]), 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[6]), 0, 0, 1, true, 'J', true);
        $pdf->LN(30);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[7]), 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $pdf->writeHTMLCell(190, 0, 10, '', utf8_encode($aRow[8]), 0, 0, 1, true, 'J', true);
        $pdf->LN(5);

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
        //$pdf->logo_bollettino($_SESSION['blazon'];

        $a_Address = array();
        $a_Address['Riga1'] = $r_Reminder['Address'];
        $a_Address['Riga2'] = '';
        $a_Address['Riga3'] = $r_Reminder['ZIP'];
        $a_Address['Riga4'] = $r_Reminder['City']. ' '."(".$r_Reminder['Province'].')';



        $NW = new CLS_LITERAL_NUMBER();
        $numeroLetterale = $NW->converti_numero_bollettino($TotalAmount);

        $pdf->scelta_td_bollettino(674,$r_Reminder['TrespasserId'],NumberDisplay($TotalAmount),'si',$r_Customer['NationalBankAccount']);
        $pdf->iban_bollettino($r_Customer['NationalBankIban']);
        $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'], 0, 50));
        $pdf->causale_bollettino('pagamento sollecito '. $str_ReminderCode,'verbale cron '.$r_Reminder['ProtocolId'].'/'.$r_Reminder['ProtocolYear'].'/'.$str_ProtocolLetter);
        $pdf->zona_cliente_bollettino(substr($r_Reminder['CompanyName'].' '.$r_Reminder['Surname'].' '.$r_Reminder['Name'],0,35),$a_Address);
        $pdf->importo_in_lettere_bollettino($numeroLetterale);




        $n_ReminderCount++;

        if ($ultimate){

            $a_Insert = array(
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Reminder['TrespasserId'],'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_Reminder['TrespasserTypeId'],'settype'=>'int'),
                array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$NotificationFee,'settype'=>'flt'),
                array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$CreationDate,'settype'=>'date'),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
            );
            $rs->Insert('FineReminder',$a_Insert);
            


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


            if($FinePDFList){
                $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE."/".$_SESSION['cityid']."/".$FileName);
                for($p=1;$p<=$n_PageCount;$p++){

                    $tmp_Page = $pdf_union->ImportPage($p);
                    $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);

                    $str_Format = ($tmp_Size['w']>$tmp_Size['h']) ? 'L' : 'P';

                    $pdf_union->AddPage($str_Format, array($tmp_Size['w'],$tmp_Size['h']),false);
                    $pdf_union->useTemplate($tmp_Page);
                }
            }




        }else $FileName = 'export.pdf';


    }

    if ($ultimate){

        $str_Definitive = "Stampa definitiva avvenuta con successo!";
        for($i=0; $i<count($a_DocumentationFineZip); $i++){
            copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_DocumentationFineZip[$i], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $a_ReminderId[$i] . "/" . $a_DocumentationFineZip[$i]);
            unlink(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$a_DocumentationFineZip[$i]);
        }


        $FileName = $_SESSION['cityid']."_".date("Y-m-d_H-i-s").".pdf";
        if($FinePDFList){
            $pdf_union->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
        }

        $_SESSION['Message'] = $str_Definitive;
        
    }else{
        if (!is_dir(NATIONAL_FINE."/".$_SESSION['cityid'])) {
            mkdir(NATIONAL_FINE."/".$_SESSION['cityid'], 0777);
        }
        $pdf->Output(NATIONAL_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
        $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/'.$FileName;
    }
}

//$rs->UnlockTables();
$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);
$rs->Update('LockedPage',$aUpdate, "Title='create_reminder_national'");
$rs->End_Transaction();