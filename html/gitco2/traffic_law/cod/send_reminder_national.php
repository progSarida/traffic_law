<?php

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".$P);
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$n_LanguageId = 1;
$FormTypeId = 30;

$CurrentDate = date("Y-m-d");
$a_DocumentationFineZip = array();

$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");


$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$ProtocolYear = $_SESSION['year'];


$Flowdate = date("Y-m-d");



$a_Lan = unserialize(LANGUAGE);


$P = "frm_send_reminder.php";

$numLit = new CLS_LITERAL_NUMBER();
if(isset($_POST['checkbox'])) {
    if($n_PrinterId==0){
        $int_FlowNumber = 0;
        $DocumentationZip = "";
    } else {

        $str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
        $rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
        $a_ProtocolLetterLocality = array();
        while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
            $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
            $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
        }




        if($ultimate){
            $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
            $flow = mysqli_fetch_array($flows);

            $int_FlowNumber = $flow['Number']+1;

            $rs->Begin_Transaction();
            $FileNameDoc = "Flusso_".$int_FlowNumber."_Sollec_Ita_".$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);
        }
        else{
            $FileNameDoc = "Flusso_Sollec_Ita_".$_SESSION['cityid']."_PROVVISORIO";
        }

        $Documentation = $FileNameDoc.".txt";
        $DocumentationZip = $FileNameDoc.".zip";
        $path = NATIONAL_FLOW."/".$_SESSION['cityid']."/";
        $myfile = fopen($path.$Documentation, "w") or die("Unable to open file!");








        $ZoneId=0;
        $Percentual =  $r_Customer['NationalPercentualReminder'];
        $checkFlowHeader = 0;
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
              
                C.Title CityTitle
              
                FROM Fine F 
                JOIN FineHistory FH ON F.Id=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN sarida.City C on C.Id = F.Locality
                WHERE F.Id=".$FineId
            );


            $r_Reminder = mysqli_fetch_array($rs_Reminder);





            $rs_Payment = $rs->SelectQuery("SELECT SUM(Amount) Amount FROM FinePayment WHERE FineId=".$FineId);
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

            $ViolationTypeId = $r_Reminder['ViolationTypeId'];

            $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType1'];
            $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType2'];

            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);

            $RuleTypeId = $r_RuleType['Id'];

            $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;

            $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId);
            $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);

            $TotalNotification = $r_ReminderHistory['NotificationFee'];
            $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];


            $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;


            $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];


            $a_PrintObject = explode("*",$r_RuleType['PrintObject'.$a_Lan[$n_LanguageId]]);

            $TotalAmount = 0;
            $PercentualAmount = 0;
            $NotificationFee = 2;


            $TaxCode = trim($r_Reminder['TaxCode']);

            $Fee = $r_Reminder['Fee'];
            $HalfMaxFee = $r_Reminder['MaxFee']*FINE_MAX;
            $MaxFee = ($r_Customer['ReminderAdditionalFee']==1) ? ($r_Reminder['MaxFee']*FINE_MAX)-$Fee : 0.00;
            $TotalNotification += $r_Reminder['NotificationFee'] + $r_Reminder['ResearchFee'];


            $TotalAmount = $Fee + $MaxFee + $TotalNotification;




            if($Percentual>0){
                //$r_Reminder['DeliveryDate'] = "2018-01-01";
                $d_DateLimit = date('Y-m-d', strtotime($r_Reminder['DeliveryDate']. ' + '.FINE_DAY_LIMIT.' days'));

                $n_Month = floor(DateDiff("M", $d_DateLimit, $CurrentDate)/6);

                for($i=1; $i<=$n_Month; $i++){
                    $PercentualAmount += $TotalAmount*$Percentual/100;
                }

                $TotalAmount += $PercentualAmount;
            }

            $TotalAmount += $NotificationFee;
            $TotalAmount -= $Amount;

            $forms = $rs->Select('Form',"FormTypeId=".$FormTypeId." AND CityId='".$_SESSION['cityid']."' AND LanguageId=".$n_LanguageId);
            $form = mysqli_fetch_array($forms);


            $Content = $form['Content'];


            $Content = str_replace("<h3>","",$Content);
            $Content = str_replace("</h3>","",$Content);
            $Content = str_replace("<h4>","",$Content);
            $Content = str_replace("</h4>","",$Content);
            $Content = str_replace("<h5>","",$Content);
            $Content = str_replace("</h5>","",$Content);
            //$Content = str_replace("<b>","",$Content);
            //$Content = str_replace("</b>","",$Content);


            $Content = str_replace("{TaxCode}", $TaxCode,$Content);

            $Content = str_replace("{FineDate}", DateOutDB($r_Reminder['FineDate']),$Content);
            $Content = str_replace("{FineTime}", TimeOutDB($r_Reminder['FineTime']),$Content);
            $Content = str_replace("{ReminderType}", $str_ReminderType, $Content);

            $Content = str_replace("{ProtocolId}", $r_Reminder['ProtocolId'],$Content);
            $Content = str_replace("{ProtocolYear}", $r_Reminder['ProtocolYear'],$Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter,$Content);

            $Content = str_replace("{Fee}", NumberDisplay($Fee), $Content);
            $Content = str_replace("{MaxFee}", NumberDisplay($MaxFee), $Content);
            $Content = str_replace("{HalfMaxFee}", NumberDisplay($HalfMaxFee), $Content);
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


            $Content = str_replace("{ReminderCode}",$str_ReminderCode, $Content);



            $rs_Row = $rs->Select('FineReminder', "	FlowDate IS NULL AND FineId=".$FineId);
            $r_Row = mysqli_fetch_array($rs_Row);

            $CreationDate = DateOutDB($r_Row['PrintDate']);
            $Content = str_replace("{CurrentDate}", $CreationDate,$Content);

            if($r_Customer['CityUnion']>1){
                $Content = str_replace("{Date}",$r_Reminder['CityTitle'].", ".$CreationDate,$Content);
            }else{
                $Content = str_replace("{Date}",$r_Customer['ManagerName'].", ".$CreationDate,$Content);
            }

            $str_SmaName                = $r_Customer['ForeignSmaName'];
            $str_SmaAuthorization       = $r_Customer['ForeignSmaAuthorization'];
            $str_SmaPayment             = $r_Customer['ForeignSmaPayment'];


            $Content = str_replace(array("\n","\r"),"",$Content);

            $a_Flow = array(
                "FineId" => $FineId,
                "TIPOLOGIA_STAMPA"=>"Posta Normale",
                "TIPOLOGIA_ATTO"=>"SOLLECITI",
                "TIPOLOGIA_FLUSSO"=>$FormTypeId,
                "CodiceComune"=>$_SESSION['cityid'],

                "HeaderRow1"=>$r_Customer['ManagerName'],
                "HeaderRow2"=>$ManagerSubject,
/*
                "HeaderRow3"=>$r_Customer['ManagerAddress'],
                "HeaderRow4"=>$r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")",
                "HeaderRow5"=>$r_Customer['ManagerPhone'],
*/

                "HeaderRow3"=>"GESTIONE: SARIDA S.R.L. - P.IVA 01338160995",
                "HeaderRow4"=>"Sede in: via M. Vattuone 9 - 16039 Sestri Levante (GE)",
                "HeaderRow5"=>"Tel: 0039 0185 1830468/9 - Mail: informazioni@poliziamunicipale-online.it",

                "HeaderRow6"=>"",
                "HeaderRow7"=>"",

                "Spese_Anticipate"          => $str_SmaPayment,
                "Intestatario_SMA"          => $str_SmaName,
                "Numero_SMA"                => $str_SmaAuthorization,

            );


            $str_Mod23LSubject          = $r_Customer['NationalMod23LSubject'];
            $str_Mod23LCustomerName     = $r_Customer['NationalMod23LCustomerName'];
            $str_Mod23LCustomerSubject  = $r_Customer['NationalMod23LCustomerSubject'];
            $str_Mod23LCustomerAddress  = $r_Customer['NationalMod23LCustomerAddress'];
            $str_Mod23LCustomerCity     = $r_Customer['NationalMod23LCustomerCity'];

            $str_PostalType             = $r_Customer['NationalPostalType'];
            $str_PostalAuthorization    = $r_Customer['NationalPostalAuthorization'];
            $str_SmaName                = $r_Customer['NationalSmaName'];
            $str_SmaAuthorization       = $r_Customer['NationalSmaAuthorization'];
            $str_SmaPayment             = $r_Customer['NationalSmaPayment'];



            $aMainPart = explode("<page>",$Content);
            $aRow = explode("<row>",$aMainPart[1]);

            $a_Flow["TrespasserName"] = $a_GenreLetter[$r_Reminder['Genre']]." ".substr($r_Reminder['CompanyName'].' '.$r_Reminder['Surname'].' '.$r_Reminder['Name'],0,35);
            $a_Flow["TrespasserAddress"] = $r_Reminder['Address'];
            $a_Flow["TrespasserCity"] = $r_Reminder['ZIP'].' '.$r_Reminder['City']. ' '."(".$r_Reminder['Province'].")";


            $a_Flow["ReminderCode"]         = $aRow[1];
            $a_Flow["ProtocolId"]           = $aRow[2];
            $a_Flow["TrespasserTaxCode"]    = $aRow[3];
            $a_Flow["TrespasserId"]         = $aRow[4];

            $a_Flow["Object"]               = $aRow[5];

            $a_Flow["TextRow1_1"]           = $aRow[6];
            $a_Flow["TextRow1_2"]           = $aRow[7];

            $n_Field = 0;
            for($i=8; $i<=14; $i++){
                $n_Field++;
                $aCol = explode("<col>",$aRow[$i]);

                if(($Percentual>0 AND $i==12) || $i!=12){
                    $a_Flow["Field" . $n_Field . "_1"] = $aCol[0];
                    if(isset($aCol[1])){
                        $a_Flow["Field" . $n_Field . "_2"] = $aCol[1];
                    }
                } else{
                    $a_Flow["Field".$n_Field."_1"] = "";
                    $a_Flow["Field".$n_Field."_2"] = "";
                }
            }



            $a_Flow["TextRow1_3"] = $aRow[15];
            $a_Flow["TextRow1_4"] = $aRow[16];
            $a_Flow["TextRow1_5"] = $aRow[17];
            $a_Flow["TextRow1_6"] = $aRow[18];
            $a_Flow["TextRow1_7"] = $aRow[19];
            $a_Flow["TextRow1_8"] = $aRow[20];





            $a_FifthField = array("Table"=>1, "Id"=>$r_Reminder['Id']);




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

            $a_Flow["TitleRow2_1"]      = $aRow[1];
            $a_Flow["TextRow2_1"]       = $aRow[2];
            $a_Flow["TitleRow2_2"]      = $aRow[3];
            $a_Flow["TextRow2_2"]       = $aRow[4];
            $a_Flow["TitleRow2_3"]      = $aRow[5];
            $a_Flow["TextRow2_3"]       = $aRow[6];
            $a_Flow["TitleRow2_4"]      = $aRow[7];
            $a_Flow["TextRow2_4"]       = $aRow[8];

            $a_Flow["TitleRow2_5"]      = "";
            $a_Flow["TextRow2_5"]       = "";
            $a_Flow["TitleRow2_6"]      = "";
            $a_Flow["TextRow2_6"]       = "";
            $a_Flow["TitleRow2_7"]      = "";
            $a_Flow["TextRow2_7"]       = "";
            $a_Flow["TitleRow2_8"]      = "";
            $a_Flow["TextRow2_8"]       = "";
            $a_Flow["TitleRow2_9"]      = "";
            $a_Flow["TextRow2_9"]       = "";

            $Bollettino1Fee = $TotalAmount;

            $a_FifthField = array("Table" => 1, "Id" => $r_Reminder['Id']);

            $a_FifthField['PaymentType'] = 1;
            $FifthField1 = SetFifthField($a_FifthField);
            $FifthFieldFee1 = SetFifthFieldFee($Bollettino1Fee);


            $a_Flow["Bollettino"] = "SI";

            $a_Flow["Tipo_Bollettino"] = $str_PostalType;
            $a_Flow["Autorizzazione"] = $str_PostalAuthorization;



            $a_Flow["ContoCorrente"] = $r_Customer['NationalBankAccount'];
            $a_Flow["IntestatarioConto"] = $r_Customer['NationalBankOwner'];
            $a_Flow["Causale"] = "PAGAMENTO SOLLECITO C.D.S. " . $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter;
            $a_Flow["Protocollo"] = $str_ReminderCode . " DEL " . DateOutDB($r_Reminder['FineDate']);
            $a_Flow["ComuneAnno"] = $_SESSION['citytitle'] . " " . $ProtocolYear;
            $a_Flow["NumeroCronologico"] = "CRON. " . $r_Reminder['ProtocolId'] . " DEL " . $ProtocolYear;
            $a_Flow["CognomeLimitato"] = substr(strtoupper($r_Reminder['CompanyName'] . $r_Reminder['Surname']), 0, 28);
            $a_Flow["NomeLimitato"] = substr(strtoupper($r_Reminder['Name']), 0, 28);
            $a_Flow["IndirizzoLimitato"] = substr(strtoupper($r_Reminder['Address']), 0, 28);
            $a_Flow["CapLimitato"] = substr(strtoupper($r_Reminder['ZIP']), 0, 28);
            $a_Flow["ComuneLimitato"] = substr(strtoupper($r_Reminder['City']), 0, 28);
            $a_Flow["CodiceFiscale"] = strtoupper($r_Reminder['TaxCode']);
            $a_Flow["ImportoNumeroBoll1"] = NumberDisplay($Bollettino1Fee);
            $a_Flow["IntestatarioLimitatoConto"] = substr(strtoupper($r_Customer['NationalBankOwner']), 0, 52);
            $a_Flow["ImportoLettereBoll1"] = $numLit->converti_numero_bollettino($Bollettino1Fee);
            $a_Flow["QuintoCampo_CodiceBoll1"] = $FifthField1;
            $a_Flow["QuintoCampo_ImportoBoll1"] = $FifthFieldFee1;





            if($checkFlowHeader==0){
                foreach($a_Flow as $key=>$value){
                    fwrite ($myfile, $key . Chr(9));  //  TAB
                }
                fwrite ($myfile, Chr(13) . Chr(10));  //  fine riga
                $checkFlowHeader = 1;
            }

            foreach($a_Flow as $value){
                fwrite ($myfile, trim($value) . Chr(9));  //  TAB
            }
            fwrite ($myfile, Chr(13) . Chr(10));  //  fine riga
            $a_Flow = null;

            if ($ultimate){

                $aUpdate = array(
                    array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>$Flowdate,'settype'=>'date')
                );
                $rs->Update('FineReminder',$aUpdate, 'FlowDate IS NULL AND FineId='.$r_Reminder['Id']);
            }
        }

        fclose($myfile);

        $aBlazon = explode(".",$_SESSION['blazon']);

        $zip = new ZipArchive();
        if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($path.$Documentation,$Documentation);
            $zip->addFile($_SESSION['blazon'],'blazon.'.$aBlazon[1]);
            $zip->close();
            $_SESSION['Documentation'] = $MainPath.'/doc/national/flow/'.$_SESSION['cityid'].'/'.$DocumentationZip;
        }
    }


    if($ultimate){
        $Zone0Number=count($_POST['checkbox']);
        
        $aInsert = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
            array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
            array('field'=>'PrintType','selector'=>'value','type'=>'str','value'=>'Lettera'),
            array('field'=>'DocumentType','selector'=>'value','type'=>'str','value'=>'SOLLECITI'),
            array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
            array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
            array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
            array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$n_PrinterId,'settype'=>'int'),
            array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
            array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
        );

        $rs->Insert('Flow',$aInsert);
        
        $aUpdate = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
        );
        $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
        
        $rs->End_Transaction();
    }
}

