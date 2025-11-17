<?php
require(CLS .'/cls_literal_number.php');

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
//NOTA BENE: il blocco della tabella serve ad impedire di creare atti con cronologici duplicati
//es: si creano verbali dinamici nello stesso momento in cui si creano verbali PEC
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".impostaParametriUrl($ReturnParams, 'frm_recreatedynamic_pecnotification.php'.$Filters));
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
    );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$privateKeyFound = false;
//Controlla l'esistenza della chiave privata per l'utente
if(file_exists(SIGNATURES . '/' . $_SESSION['userid'].CERT_EXTENSION)){
    $privateKeyFound = true;
}

if (!is_dir(NATIONAL_PEC . "/" . $_SESSION['cityid'])) {
    mkdir(NATIONAL_PEC . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(NATIONAL_FINE . "/" . $_SESSION['cityid'])) {
    mkdir(NATIONAL_FINE . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(NATIONAL_FLOW . "/" . $_SESSION['cityid'])) {
    mkdir(NATIONAL_FLOW . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(SIGNED_FOLDER)) {
    mkdir(SIGNED_FOLDER, 0777);
}

if (!is_dir(TOSIGN_FOLDER)) {
    mkdir(TOSIGN_FOLDER, 0777);
}

if (!is_dir(SIGNED_FOLDER . "/" . $_SESSION['cityid'])) {
    mkdir(SIGNED_FOLDER . "/" . $_SESSION['cityid'], 0777);
}

if (!is_dir(TOSIGN_FOLDER . "/" . $_SESSION['cityid'])) {
    mkdir(TOSIGN_FOLDER . "/" . $_SESSION['cityid'], 0777);
}

$ultimate = CheckValue('ultimate', 'n');

$str_Warning = '';

$FormTypeId = 41;
$DocumentationTypeId = 13;
//Array documenti notifiche
$a_Documentation_N = array();
//Array contenenti le insert e update di ogni verbale
$a_I_FineDocumentation = array();
$a_U_Fine = array();

//Dati finehistory

//Dati fine
$StatusTypeId = 20;

$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}


if (isset($_POST['checkbox'])) {
    
    $rs->Start_Transaction();
    
    //Inizializza pdf-union
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array('helvetica', '', 8));
    $pdf_union->setFooterFont(array('helvetica', '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);
    
    foreach ($_POST['checkbox'] as $Ids) {
        $a_Ids = explode(',', $Ids);
        $FineId = $a_Ids[0];
        $TrespasserId = $a_Ids[1];
        $DocumentationId = $a_Ids[2];
        $Operation = $a_Ids[3];
        //$Operation = 1: Invio fallito
        //$Operation = 2: Mancata consegna
        //$Operation = 3: Notifica scaduta
        
        $a_Documentation_N[$FineId] = array();
        
        //OPERAZIONI PRELIMINARI//////////////////////////////////////////////////////////////////////
        if($ultimate){
            $rs_Documentation = $rs->Select("FineDocumentation", "Id=$DocumentationId");
            $r_Documentation = $rs->getArrayLine($rs_Documentation);
            //NOTIFICA SCADUTA
            if($Operation == 3){
                //Cancella la notifica non firmata dalla cartella dei documenti da firmare
                if(file_exists(TOSIGN_FOLDER . "/" . $_SESSION['cityid'] . "/" . $r_Documentation['Documentation'])){
                    unlink(TOSIGN_FOLDER . "/" . $_SESSION['cityid'] . "/" . $r_Documentation['Documentation']);
                }
                //Cancella la notifica non firmata dalla cartella del verbale
                if(file_exists(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $r_Documentation['Documentation'])){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $r_Documentation['Documentation']);
                }
                $rs->Delete('FineDocumentation', "Id=$DocumentationId AND FineId=$FineId");
            }
            //INVIO FALLITO
            //TODO da riprendere e testare
            if($Operation == 1){
                $a_FineHDocuments = array();
                
                $rs_FineHistory = $rs->Select('FineHistory',"FineId=$FineId AND TrespasserId=$TrespasserId");
                
                while ($r_FineHistory = mysqli_fetch_assoc($rs_FineHistory)){
                    $a_FineHDocuments[$r_FineHistory['NotificationTypeId']] = $r_FineHistory['Documentation'];
                    if($r_FineHistory['NotificationTypeId'] == 6)
                        $FlowId = $r_FineHistory['FlowId'];
                }
                
                //Cancella la notifica non firmata dalla cartella del verbale
                if(file_exists(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $r_Documentation['Documentation'])){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $r_Documentation['Documentation']);
                }
                $rs->Delete('FineDocumentation', "Id=$DocumentationId AND FineId=$FineId");
                
                //Cancella la notifica firmata dalla cartella del verbale
                if(file_exists(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $a_FineHDocuments[16])){
                    unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $a_FineHDocuments[16]);
                }
                $rs->Delete('FineDocumentation', "Documentation={$a_FineHDocuments[16]} AND FineId=$FineId");
                
                $zip = new ZipArchive;
                if ($zip->open(NATIONAL_FLOW.'/'.$_SESSION['cityid'].'/'.$a_FineHDocuments[6]) === true) {
                    $nfiles = $zip->numFiles;
                    //Cancella il file della notifica dallo zip
                    if($zip->deleteName($a_FineHDocuments[16])){
                        $nfiles--;
                    }
                    //Cancella il file del verbale dallo zip
                    if($zip->deleteName($a_FineHDocuments[15])){
                        $nfiles--;
                    }
                    $zip->close();
                    
                    if($nfiles <= 0){
                        //Se l'archivio del flusso non contiene più nulla e quindi cancellato automaticamente, cancella il record del flusso
                        $rs->Delete('Flow', "Id=$FlowId");
                    }
                }
                
                //Cancella il record dell'invio
                $rs->Delete('FlowPecMails', "Id=$FlowId");
                //Cancella i record su FineHistory per le notifiche e flusso
                $rs->Delete('FineHistory', "FineId=$FlowId AND TrespasserId=$TrespasserId AND NotificationTypeId IN(16,6)");
            }
        }
        //////////////////////////////////////////////////////////////////////////////////////////////
        
        $rs_Fine = $rs->Select('V_ViolationAll', "Id=" . $FineId . " AND TrespasserId=". $TrespasserId);
        
        while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
            
            //TCPDF/////////////////////////////////////////////////////////
            $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);
            $pdf->TemporaryPrint = $ultimate;
            $pdf->NationalFine = 1;
            $pdf->CustomerFooter = 0;
            //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_SESSION['citytitle']);
            $pdf->SetTitle('Notification PEC');
            $pdf->SetSubject('');
            $pdf->SetKeywords('');
            $pdf->setHeaderFont(array('helvetica', '', 8));
            $pdf->setFooterFont(array('helvetica', '', 8));
            $pdf->SetMargins(10, 8, 10);
            /////////////////////////////////////////////////////////
            
            $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
            $trespasser = mysqli_fetch_array($trespassers);
            
            $ViolationTypeId = $r_Fine['ViolationTypeId'];
            
            $rs_FineHistoryPEC = $rs->Select("FineHistory", "FineId=$FineId AND NotificationTypeId=15 AND TrespasserId=".$r_Fine['TrespasserId']);
            $r_FineHistoryPEC = mysqli_fetch_array($rs_FineHistoryPEC);
            
            $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "'");
            $r_RuleType = mysqli_fetch_array($rs_RuleType);
            
            $rs_FineChiefController = $rs->Select('Controller', "Id=".$r_Fine['FineChiefControllerId']);
            $r_FineChiefController = mysqli_fetch_array($rs_FineChiefController);
            $FineChiefController = (isset($r_FineChiefController['Qualification']) ? $r_FineChiefController['Qualification'].' ' : '').$r_FineChiefController['Name'].' (Matr: '.$r_FineChiefController['Code'].')';
            
            $rs_FineController = $rs->Select('Controller', "Id=".$r_Fine['ControllerId']);
            $r_FineController = mysqli_fetch_array($rs_FineController);
            $FineController = (isset($r_FineController['Qualification']) ? $r_FineController['Qualification'].' ' : '').$r_FineController['Name'].' (Matr: '.$r_FineController['Code'].')';
            
            $rs_Signer = $rs->Select('Controller', "Id=".$_SESSION['controllerid']);
            $r_Signer = mysqli_fetch_array($rs_Signer);
            $Signer = (isset($r_Signer['Qualification']) ? $r_Signer['Qualification'].' ' : '').$r_Signer['Name'].' (Matr: '.$r_Signer['Code'].')';
            
            $RuleTypeId = $r_RuleType['Id'];
            
            if(trim($r_Fine['ArticleLetterAssigned'])!=''){
                $NationalProtocolLetterType1 = $r_Fine['ArticleLetterAssigned'];
                $NationalProtocolLetterType2 = $r_Fine['ArticleLetterAssigned'];
            } else if(trim($r_Fine['ViolationLetterAssigned'])!=''){
                $NationalProtocolLetterType1 = $r_Fine['ViolationLetterAssigned'];
                $NationalProtocolLetterType2 = $r_Fine['ViolationLetterAssigned'];
            } else {
                $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType1'];
                $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType2'];
            }
            
            $str_ProtocolLetter = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
            
            //$ManagerSubject = $r_RuleType['PrintHeader' . $a_Lan[$n_LanguageId]];
            
            $pdf->SetPrintHeader(true);
            $pdf->RightHeader = false;
            
            $page_format = array('Rotate' => 45);
            
            $pdf->AddPage('P', $page_format);
            
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            
            $pdf->Image($_SESSION['blazon'], 10, 8, 15, 23);
            
            $ManagerName = $r_Customer['ManagerName'];
            $pdf->customer = $ManagerName;
            
            $pdf->SetFont('helvetica', '', 10, '', true);
            
            
            if (strlen($r_Customer['ManagerName']) > 22) {
                $pdf->writeHTMLCell(60, 0, 30, '', '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
                $pdf->LN(9);
                
            } else {
                
                $pdf->writeHTMLCell(60, 0, 30, '', '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
                $pdf->LN(4);
            }
            
            
            if($_SESSION['cityid']=="H452"){
                $str_CustomerAddress = "Art.57 CPP e Art.11 c.1 L.a) e b) CDS";
                $str_CustomerCity = $r_Customer['ManagerAddress']. " " .$r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
            } else {
                $str_CustomerAddress = $r_Customer['ManagerAddress'];
                $str_CustomerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
            }
            
            $pdf->writeHTMLCell(60, 0, 30, '', $r_Customer['ManagerSector'], 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(70, 0, 30, '', $str_CustomerAddress, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(70, 0, 30, '', $str_CustomerCity.($r_Customer['ManagerPhone'] ? ' TEL: '.$r_Customer['ManagerPhone'] : ''), 0, 0, 1, true, 'L', true);
            
            if ($r_Customer['FineNonDeliveryAddress'] != ""){
                $pdf->SetFont('helvetica', '', 8, '', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(120, 0, 30, '', "Restituzione piego in caso di mancato recapito :", 0, 0, 1, true, 'L', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(120, 0, 30, '', StringOutDB($r_Customer['FineNonDeliveryAddress']), 0, 0, 1, true, 'L', true);
            }
            
            $pdf->LN(20);
            
            $pdf->SetFont('helvetica', '', 10, '', true);
            $Content=getDynamicContent($FormTypeId,$_SESSION['cityid']);
            //VARIABILI//////////////////////////////////////////////////////////////////////////////////////////////////
            $Content = str_replace("{Signer}", StringOutDB($Signer), $Content);
            $Content = str_replace("{FineController}", StringOutDB($FineController), $Content);
            $Content = str_replace("{FineChiefController}", StringOutDB($FineChiefController), $Content);
            $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
            $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
            $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
            $Content = str_replace("{ManagerSector}", StringOutDB($r_Customer['ManagerSector']), $Content);
            $Content = str_replace("{ManagerZIP}", $r_Customer['ManagerZIP'], $Content);
            $Content = str_replace("{ManagerCity}", StringOutDB($r_Customer['ManagerCity']), $Content);
            $Content = str_replace("{ManagerProvince}", $r_Customer['ManagerProvince'], $Content);
            $Content = str_replace("{ManagerAddress}", StringOutDB($r_Customer['ManagerAddress']), $Content);
            $Content = str_replace("{ManagerPEC}", StringOutDB($r_Customer['ManagerPEC']), $Content);
            $Content = str_replace("{ManagerPhone}", $r_Customer['ManagerPhone'] ? 'TEL: '.$r_Customer['ManagerPhone'] : '', $Content);
            $Content = str_replace("{ManagerFax}", $r_Customer['ManagerFax'] ? 'FAX: '.$r_Customer['ManagerFax'] : '', $Content);
            $Content = str_replace("{ManagerMail}", $r_Customer['ManagerMail'] ? 'MAIL: '.$r_Customer['ManagerMail'] : '', $Content);
            $Content = str_replace("{ProtocolYear}", $r_Fine['ProtocolYear'], $Content);
            $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);
            $Content = str_replace("{ProtocolId}", $r_Fine['ProtocolId'], $Content);
            $Content = str_replace("{TrespasserName}", StringOutDB((isset($trespasser['CompanyName']) ? $trespasser['CompanyName'].' ' : '').$trespasser['Name'] . ' ' . $trespasser['Surname']), $Content);
            $Content = str_replace("{TrespasserCity}", StringOutDB($trespasser['City']), $Content);
            $Content = str_replace("{TrespasserProvince}", StringOutDB($trespasser['Province']), $Content);
            $Content = str_replace("{TrespasserAddress}", StringOutDB($trespasser['Address']), $Content);
            $Content = str_replace("{TaxCode}", StringOutDB($trespasser['TaxCode']), $Content);
            $Content = str_replace("{TrespasserPEC}", StringOutDB($trespasser['PEC']), $Content);
            $Content = str_replace("{DataSourceDate}", DateOutDB($trespasser['DataSourceDate']), $Content);
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Nome del file//////////////////////////////

            //Sostituisce la data nel nome del file con quella attuale
            $Documentation_N = preg_replace('([0-9]{4}-[0-9]{2}-[0-9]{2})', date('Y-m-d'), $r_Documentation['Documentation']);
            $Documentation_N_Signed = str_replace('_N.'.PDFA, '_N_signed.'.PDFA, $Documentation_N);
            
            $a_Documentation_N[$FineId][] = array('Doc' => $Documentation_N, 'Sign' => false);
            if($privateKeyFound)
                $a_Documentation_N[$FineId][] = array('Doc' => $Documentation_N_Signed, 'Sign' => true);
            /////////////////////////////////////////////
            
            //SOSTITUISCE I PAGE BREAK DEL CKEDITOR CON QUELLO DI TCPDF
            $CKEditor_pagebreak = '~<div[^>]*style="[^"]*page-break[^:]*:[ ]*always.*</div>~';
            $TCPDF_pagebreak = '<br pagebreak="true" />';
            $Content = preg_replace($CKEditor_pagebreak, $TCPDF_pagebreak, $Content);
            //
            
            $pdf->SetAutoPageBreak(true, 0);
            $pdf->SetPrintHeader(false);
            
            $pdf->writeHTML($Content, true, false, true, false, '');
            
            
            $PageNo= $pdf->PageNo();
            if($PageNo%2 == 1){
                $pdf->AddPage('P', $page_format);
            }
            
            //SCORRE TUTTE LE PAGINE E SCRIVE L'ETICHETTA "STAMPA PROVVISORIA" SU OGNUNA DI ESSE////////////////////////////////////////////
            if (!$ultimate){
                $TotalPages = $pdf->PageNo();
                for ($i=1; $i<=$TotalPages; $i++){
                    $pdf->setPage($i, true);
                    $pdf->SetXY(10, 250);
                    $pdf->StartTransform();
                    $pdf->Rotate(50);
                    $pdf->SetFont('helvetica', '', 22);
                    $pdf->SetTextColor(190);
                    $pdf->Cell(280,0,'S   T   A   M   P   A         P   R   O   V   V   I   S   O   R   I   A',0,1,'C',0,'');
                    $pdf->StopTransform();
                }
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Scrive il file pdf della notifica sul file system////////////////////////////////////////////////////////////////////////////////////
            $pdf->Output(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation_N, "F");
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Se "Abilita la creazione e la firma digitale della relata di notifica dei verbali con contestuale invio degli atti tramite PEC" è attivo//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($privateKeyFound){
                //Scrive il file pdf della notifica firmata sul file system se il flag è abilitato//////////////////////////////////////////////////////
                digitalSign(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation_N, NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Documentation_N_Signed, $SignaturePwd, 'Notifica Verbale PEC', "Questo documento è firmato digitalmente da <strong>".$Signer."</strong>", 10, 45);
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //PDF UNION////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $n_PageCount = $pdf_union->setSourceFile(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . ($privateKeyFound ? $Documentation_N_Signed : $Documentation_N));
            for ($p = 1; $p <= $n_PageCount; $p++) {
                
                $tmp_Page = $pdf_union->ImportPage($p);
                $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
                
                $str_Format = ($tmp_Size['w'] > $tmp_Size['h']) ? 'L' : 'P';
                
                $pdf_union->AddPage($str_Format, array($tmp_Size['w'], $tmp_Size['h']), false);
                $pdf_union->useTemplate($tmp_Page);
            }
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            //Prepara le insert e update
            if ($ultimate){
                
                //FineDocumentation notifica
                $a_InsertFD = array(
                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                    array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation_N),
                    array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId),
                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                );
                $a_I_FineDocumentation[] = array('Data' => $a_InsertFD, 'FineId' => $FineId);
                
            }
        }
    }
    
    //INSERIMENTO E AGGIORNAMENTO DATI///////////////////////////////////////////////////////////////////////////////////////////////
    if ($ultimate){
        //Inserisce i record in FineDocumentation
        foreach ($a_I_FineDocumentation as $Insert){
            $rs->Insert('FineDocumentation', $Insert['Data']);
        }
    } 
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //GESTIONE DOCUMENTI/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($ultimate){
        //Crea il pdf delle notifiche unite
        $UnionDocumentation = $_SESSION['cityid'] . "_" . date("Y-m-d_H-i-s") . "_N.".PDFA;
        if (!is_dir(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/create')) {
            mkdir(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/create', 0777);
        }
        
        $pdf_union->Output(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/create/' . $UnionDocumentation, "F");
        
        //Smista i file nelle apposite cartelle
        foreach ($a_Documentation_N as $DocFineId => $a_Doc){
            foreach ($a_Doc as $Doc){
                if($Doc['Sign']){
                    //Se il documento è firmato lo sposta in public/FIRMATI/<ente>/
                    if(!copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'], SIGNED_FOLDER . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'])){
                        $str_Warning .= "Copia file da percorso temporaneo a " . SIGNED_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                        trigger_error("Copia file da percorso temporaneo a " . SIGNED_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                    }
                } else {
                    //Se il documento NON è firmato lo sposta in public/DA_FIRMARE/<ente>/ e doc/national/fine/<ente>/<idverbale>/
                    if(!copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'], TOSIGN_FOLDER . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'])){
                        $str_Warning .= "Copia file da percorso temporaneo a " . TOSIGN_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                        trigger_error("Copia file da percorso temporaneo a " . TOSIGN_FOLDER. "/" . $_SESSION['cityid'] . " fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                    }
                    
                    if(!copy(NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $Doc['Doc'], NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . "/" . $Doc['Doc'])){
                        $str_Warning .= "Copia file da percorso temporaneo a " . NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . " fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                        trigger_error("Copia file da percorso temporaneo a " . NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $DocFineId . " fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                    }
                }
                
                //Elimina il file dal percorso temporaneo
                if(!unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc['Doc'])){
                    $str_Warning .= "Eliminazione file da percorso temporaneo fallita: {$Doc['Doc']} [FineId: $DocFineId]<br>";
                    trigger_error("Eliminazione file da percorso temporaneo fallita: {$Doc['Doc']} [FineId: $DocFineId]", E_USER_WARNING);
                }
            }
        }
        
        $_SESSION['Documentation'] = $MainPath . '/doc/national/pec/' . $_SESSION['cityid'] . '/create/' . $UnionDocumentation;
    } else {
        $UnionDocumentation = 'export_createdynamic_pecnotification_national.'.PDFA;
        
        $pdf_union->Output(NATIONAL_PEC . "/" . $_SESSION['cityid'] . '/' . $UnionDocumentation, "F");
        
        foreach ($a_Documentation_N as $DocFineId => $a_Doc){
            foreach ($a_Doc as $Doc){
                unlink(NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/' . $Doc['Doc']);
            }
        }
        
        $_SESSION['Documentation'] = $MainPath . '/doc/national/pec/' . $_SESSION['cityid'] . '/' . $UnionDocumentation;
    }
        
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = $str_Warning;
    } else if ($ultimate){
        $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
    }
            
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
        
    $rs->End_Transaction();
}
