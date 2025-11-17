<?php

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".impostaParametriUrl($AdditionalFilters, $P.$Parameters));
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

$a_PrinterConfigs = unserialize(PRINTER_FTP_CONFIG);
$a_PrinterConf = $a_PrinterConfigs[$n_PrinterId] ?? null;

//Se viene selezionato uno stampatore per cui è previsto l'invio del flusso tramite FTP, tenta la connessione
if($a_PrinterConf && $ultimate && PRODUCTION){
    $phpFTP = PhpFTPFactory::create(
        $a_PrinterConf['Type'],
        $a_PrinterConf['Host'],
        $a_PrinterConf['Username'],
        $a_PrinterConf['Password'],
        $a_PrinterConf['Port']);
    if(!$phpFTP->connect()){
        $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
        header("location: ".$P);
        DIE;
    } else {
        $phpFTP->disconnect();
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$FormTypeId = 30;

$a_DocumentationFineZip = array();
$str_Warning = '';
$str_Success = '';

$Flowdate = date("Y-m-d");

$P = "frm_senddynamic_reminder.php";

//Parametri stampatore////////////////////////////
$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=$n_PrinterId AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_SmaName                = $r_PrintParameter['NationalReminderSmaName'] ?? '';
$str_SmaAuthorization       = $r_PrintParameter['NationalReminderSmaAuthorization'] ?? '';
$str_SmaPayment             = $r_PrintParameter['NationalReminderSmaPayment'] ?? '';
////////////////////////////////////////////////

if(isset($_POST['checkbox'])) {
    
    $rs->Start_Transaction();

        if($ultimate){
            $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
            $flow = mysqli_fetch_array($flows);

            $int_FlowNumber = $flow['Number']+1;
            
            $FileNameDoc = "Flusso_".$int_FlowNumber."_Sollec_Ita_".$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);
        }
        else{
            $FileNameDoc = "Flusso_Sollec_Ita_".$_SESSION['cityid']."_PROVVISORIO";
        }

        $DocumentationZip = $FileNameDoc.".zip";
        $path = NATIONAL_FLOW."/".$_SESSION['cityid']."/";
        
        //CREA LA CARTELLA DELL'ENTE SE NON ESISTE
        if (!is_dir(NATIONAL_FLOW."/".$_SESSION['cityid'])) {
            mkdir(NATIONAL_FLOW."/".$_SESSION['cityid'], 0777);
        }
        
        //MERCURIO/POSTA/PUBBLIMAIL/KOINE
        if($n_PrinterId == 4 || $n_PrinterId == 2 || $n_PrinterId == 1 || $n_PrinterId == 5){
            $Documentation = $FileNameDoc.".csv";
            $tempCSV = tempnam($path, $Documentation);
            $myfile = fopen($tempCSV, "w") or die("Unable to open file!");
            
            if($n_PrinterId == 4)
                //COLONNE PUBBLIMAIL
                $a_FlowColumns = array(
                    "NOME_FILE",
                    "DATA_DI_CREAZIONE",
                    "FINEID",
                    "TIPOLOGIA_STAMPA",
                    "TIPOLOGIA_ATTO",
                    "TIPOLOGIA_FLUSSO",
                    "CODICE_COMUNE",
                    "SPESE_ANTICIPATE",
                    "INTESTATARIO_SMA",
                    "NUMERO_SMA",
                    "RIF._ATTO",
                    "NOME_RECAPITO",
                    "CAP_RECAPITO",
                    "LOCALITA_RECAPITO",
                    "CITTA_RECAPITO",
                    "PROVINCIA_RECAPITO",
                );
            else if($n_PrinterId == 2 || $n_PrinterId == 5)
                //COLONNE MERCURIO/KOINE
                $a_FlowColumns = array(
                    "NOME_FILE",
                    "DATA_DI_CREAZIONE",
                    "FINEID",
                    "TIPOLOGIA_STAMPA",
                    "TIPOLOGIA_ATTO",
                    "TIPOLOGIA_FLUSSO",
                    "CODICE_COMUNE",
                    "SPESE_ANTICIPATE",
                    "INTESTATARIO_SMA",
                    "NUMERO_SMA",
                    "RIF._ATTO",
                    "NOME_RECAPITO",
                    "CAP_RECAPITO",
                    "LOCALITA_RECAPITO",
                    "CITTA_RECAPITO",
                    "PROVINCIA_RECAPITO",
                );
            else
                //COLONNE POSTA
                $a_FlowColumns = array(
                    "NOME_FILE",
                    "DATA_DI_CREAZIONE",
                    "FINEID",
                    "TIPOLOGIA_STAMPA",
                    "TIPOLOGIA_ATTO",
                    "TIPOLOGIA_FLUSSO",
                    "RIF._ATTO",
                    "NOME_RECAPITO",
                    "CAP_RECAPITO",
                    "LOCALITA_RECAPITO",
                    "CITTA_RECAPITO",
                    "PROVINCIA_RECAPITO",
                );
            
            fputcsv($myfile, $a_FlowColumns, ';');
        }

        foreach($_POST['checkbox'] as $FineId) {
            
            $rs_Reminder = $rs->SelectQuery(
                "SELECT
                F.Id,
                F.Code,
                F.ProtocolId,
                F.ProtocolYear,
                F.Locality,
                F.FineDate,
                F.FineTime,
                F.CityId,
                F.ReminderDate,
                
                FH.NotificationTypeId,
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

                FR.Documentation,
                
                T.Id TrespasserId,
                T.Genre,
                T.CompanyName,
                T.Surname,
                T.Name,
                T.Address,
                T.StreetNumber,
                T.Ladder,
                T.Indoor,
                T.Plan,
                T.ZIP,
                T.City,
                T.Province,
                T.TaxCode,
                T.ZoneId,
                T.LanguageId,
                
                C.Title CityTitle,
                CO.Title CountryTitle
                
                FROM Fine F
                JOIN FineHistory FH ON F.Id=FH.FineId AND FH.NotificationTypeId=6
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN FineReminder FR ON F.Id = FR.FineId AND FR.PrintDate = F.ReminderDate
                JOIN Trespasser T ON FH.TrespasserId = T.Id
                JOIN sarida.City C on C.Id = F.Locality
                JOIN sarida.Country CO ON CO.Id=T.CountryId
                WHERE F.Id=" . $FineId
                );
            
            $r_Reminder = mysqli_fetch_array($rs_Reminder);
            
            /****COSTRUZIONE RIFERIMENTO ATTO****/
            //PARAMETRI DELL'ENTE
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
            
            $ViolationTypeId = $r_Reminder['ViolationTypeId'];
            $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType1'];
            $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Reminder['Locality']]['NationalProtocolLetterType2'];
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            
            $RuleTypeId = $r_RuleType['Id'];
            $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            
            $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$FineId." AND FlowDate IS NOT NULL");
            $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);
            
            $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];
            
            
            $str_ReminderCode = "R". $r_Reminder['ProtocolId']."/".$r_Reminder['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;
            /**********/
            
            //$rs_Documentation = $rs->Select("FineDocumentation","FineId=$FineId AND DocumentationTypeId=30");
            $DocumentName = $r_Reminder['Documentation'];
            $a_DocumentationFineZip[$FineId] = $DocumentName;
            $str_TrespasserAddress =  trim(
                $r_Reminder['Address'] ." ".
                $r_Reminder['StreetNumber'] ." ".
                $r_Reminder['Ladder'] ." ".
                $r_Reminder['Indoor'] ." ".
                $r_Reminder['Plan']
                );
            
            if($n_PrinterId == 4)
                //RIGHE PUBBLIMAIL
                $a_FlowRows = array(
                    $DocumentName,
                    DateOutDB($r_Reminder['ReminderDate']),
                    $FineId,
                    "Posta Normale",
                    "SOLLECITI",
                    $FormTypeId,
                    $_SESSION['cityid'],
                    $str_SmaPayment,
                    $str_SmaName,
                    $str_SmaAuthorization,
                    $str_ReminderCode,
                    ($r_Reminder['Genre'] != 'D' ? $r_Reminder['Name']." ".$r_Reminder['Surname'] : $r_Reminder['CompanyName']),
                    $r_Reminder['ZIP'],
                    $str_TrespasserAddress,
                    $r_Reminder['City'],
                    $r_Reminder['Province'],
                );
            else if($n_PrinterId == 2 || $n_PrinterId == 5)
                //RIGHE MERCURIO/KOINE
                $a_FlowRows = array(
                    $DocumentName,
                    DateOutDB($r_Reminder['ReminderDate']),
                    $FineId,
                    "Posta Normale",
                    "SOLLECITI",
                    $FormTypeId,
                    $_SESSION['cityid'],
                    $str_SmaPayment,
                    $str_SmaName,
                    $str_SmaAuthorization,
                    $str_ReminderCode,
                    ($r_Reminder['Genre'] != 'D' ? $r_Reminder['Name']." ".$r_Reminder['Surname'] : $r_Reminder['CompanyName']),
                    $r_Reminder['ZIP'],
                    $str_TrespasserAddress,
                    $r_Reminder['City'],
                    $r_Reminder['Province'],
                );
            else
                //RIGHE POSTA
                $a_FlowRows = array(
                    $DocumentName,
                    DateOutDB($r_Reminder['ReminderDate']),
                    $FineId,
                    "Posta Normale",
                    "SOLLECITI",
                    $FormTypeId,
                    $str_ReminderCode,
                    ($r_Reminder['Genre'] != 'D' ? $r_Reminder['Name']." ".$r_Reminder['Surname'] : $r_Reminder['CompanyName']),
                    $r_Reminder['ZIP'],
                    $str_TrespasserAddress,
                    $r_Reminder['City'],
                    $r_Reminder['Province'],
                );
            
            fputcsv($myfile, $a_FlowRows, ';');
            
            $a_FlowRows = null;
            
        }
        
        //MERCURIO/POSTA/PUBBLIMAIL/KOINE
        if ($n_PrinterId == 4 || $n_PrinterId == 2 || $n_PrinterId == 1 || $n_PrinterId == 5){
            fclose($myfile);
        }

        $zip = new ZipArchive();
        if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($a_DocumentationFineZip as $FineId => $DocumentName){
                $zip->addFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName, $DocumentName);
            }
            
            //MERCURIO/POSTA/PUBBLIMAIL/KOINE
            if ($n_PrinterId == 4 || $n_PrinterId == 2 || $n_PrinterId == 1 || $n_PrinterId == 5){
                $zip->addFile($tempCSV, $Documentation);
            }
            $zip->close();
            
            //MERCURIO/POSTA/PUBBLIMAIL/KOINE
            if ($n_PrinterId == 4 || $n_PrinterId == 2 || $n_PrinterId == 1 || $n_PrinterId == 5){
                unlink($tempCSV);
            }
            
            if ($n_PrinterId == 4 || $n_PrinterId == 2 || $n_PrinterId == 5)
                $_SESSION['Documentation'] = $MainPath.'/doc/national/flow/'.$_SESSION['cityid'].'/'.$DocumentationZip;
        }

        //crea il pdf per gli utenti
        // initiate FPDI
        $pdf = new FPDI();
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 8));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // iterate through the files
        foreach ($a_DocumentationFineZip as $FineId => $DocumentName) {
            // get the page count
            //echo NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName;
            $pageCount = $pdf->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $DocumentName);
            // iterate through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                // get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);
                
                // create a page (landscape or portrait depending on the imported page size)
                if ($size['w'] > $size['h']) {
                    $pdf->AddPage('L', array($size['w'], $size['h']));
                } else {
                    $pdf->AddPage('P', array($size['w'], $size['h']));
                }
                
                // use the imported page
                $pdf->useTemplate($templateId);
                
            }
        }     
        // Output the new PDF
        $FileNamePdf = $FileNameDoc.".pdf";
        $pdf->Output(NATIONAL_FLOW."/".$_SESSION['cityid'].'/'.$FileNamePdf, "F");
        
        if ($n_PrinterId == 1)
            $_SESSION['Documentation'] = $MainPath.'/doc/national/flow/'.$_SESSION['cityid'].'/'.$FileNamePdf;
        

        if($ultimate){
            
            foreach($_POST['checkbox'] as $FineId) {
                $aUpdate = array(
                    array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>$Flowdate,'settype'=>'date'),
                    array('field' =>'FlowNumber', 'selector' => 'value', 'type' => 'int', 'value' => $int_FlowNumber, 'settype' => 'int'),
                );
                $rs->Update('FineReminder',$aUpdate, 'FlowDate IS NULL AND FineId='.$FineId);
            }
            
            $Zone0Number=count($_POST['checkbox']);
        
            $aInsert = array(
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
                array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
                array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>4,'settype'=>'int'),
                array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>9,'settype'=>'int'),
                array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
                array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
                array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
                array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$n_PrinterId,'settype'=>'int'),
                array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
                array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
            );
            
            $FlowId = $rs->Insert('Flow',$aInsert);
            
            //Se il verbale è da inviare ad uno stampatore, chiama una funzione specifica definita nel parametri di configurazione
            //passandogli i riferimenti al file del flusso come parametri
            if($a_PrinterConf && PRODUCTION){
                if(!$phpFTP->connect()){
                    $_SESSION['Message']['Error'] = "Tentativo di connessione al server dello stampatore fallito:<br>".implode('<br>', $phpFTP->errors());
                    header("location: ".$P);
                    DIE;
                } else {
                    //Riferimenti zip flusso
                    $a_Flow = array(
                        'LocalFile' => $path.$DocumentationZip,
                        'RemoteFile' => isset($a_PrinterConf['Path']['SOLLECITI'])
                        ? $a_PrinterConf['Path']['SOLLECITI'].'/'.$DocumentationZip
                        : $DocumentationZip
                    );
                    
                    if(call_user_func_array($a_PrinterConf['Function'], array($phpFTP, $a_Flow))){
                        $a_UpdateFlow = array(
                            array('field'=>'UploadDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'))
                        );
                        
                        $rs->Update('Flow', $a_UpdateFlow, "Id=$FlowId");
                        
                        $str_Success .= "Flusso caricato con successo.<br>";
                    } else {
                        $str_Warning .= 'Errore nell\'invio del flusso allo stampatore:<br>'.implode('<br>', $phpFTP->errors());
                    }
                    
                    $phpFTP->disconnect();
                }
            }
            
            if ($str_Warning != ''){
                $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
            } else {
                $str_Success .= 'Azione eseguita con successo.';
                $_SESSION['Message']['Success'] = $str_Success;
            }
        }
        
        //$rs->UnlockTables();
        $aUpdate = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
        );
        $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
        
        $rs->End_Transaction();
}

