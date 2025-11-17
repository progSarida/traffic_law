<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_pagopa_spontaneous_type2.php");
require_once(INC."/initialization.php");

ini_set('max_execution_time', 5000);

$rs = new CLS_DB(new cls_db_gestoreErroriRaccogli());
$rs->SetCharset('utf8');

$ImportFile = CheckValue('ImportFile','s');
$Filters = CheckValue('Filters', 's');
$ForceDuplicate = CheckValue('ForceDuplicate', 'n');

$str_Error = '';
$n_LineCount = 0;
$n_CompletedCount = 0;
$a_ErrorLines = array();

$PaymentTypeId = 19;
$TableId=1;
//Mettiamo sempre nazionale, se un giorno andrà distinto saneremo i dati in base alla nazionalità della targa o trasgressore
$BankMgmt = $r_Customer['NationalBankMgmt'];
$FinePaymentSpecificationType = $r_Customer['FinePaymentSpecificationType'];
$PaymentDocumentId = 0;
$ImportationId = 1;

$CityId = $_SESSION['cityid'];
$path = PAYMENT_FOLDER."/".$_SESSION['cityid']."/";

$a_CSVColumns = array (
    'ID',
    'AnnoImposta',
    'NumeroFattura',
    'AnnoEmissione',
    'ImportoDovuto',
    'QuintoCampo',
    'Importo',
    'DataScadenza',
    'DataVersamento',
    'DataAccredito',
    'DataRiversamento',
    'DataAssociazione',
    'DataInserimento',
    'Nominativo',
    'CodiceFiscale',
    'ModalitaPagamento',
    'TipoPagamento',
    'Cc',
    'Sottoservizio',
    'RiferimentoPraticaEsterna',
    'RiferimentoPraticaScollegata',
    'Flusso',
    'Quietanza',
    'Annullato',
    'Note',
    'Connettore',
    'SoftwareEsportazione',
    'ProvvisorioIncasso',
    'Prelevamento',
    'Causale',
    'TipoImpattoContabilita',
    'Consolidato',
    'DataApprovazione',
    'EnteBeneficiario',
    'Tassonomia'
);

if($ImportFile != ''){
    $fileStream = @fopen($path.$ImportFile,  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, IMP_PAGOPA_SPONTANEOUS_TYPE2_SEPARATOR);
        $a_CSVColumnsDiffs = compareColumns($a_CSVColumns, $a_CSVFirstLine); //Confronta le colonne previste con la prima riga del file
        
        if(empty($a_CSVColumnsDiffs)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                
                $a_IndexedLines = buildLinesArray($a_CSVColumns, IMP_PAGOPA_SPONTANEOUS_TYPE2_SEPARATOR, $fileStream);

                foreach($a_IndexedLines as $a_CSVLine){
                    $ReasonNote = '';
                    $FineId = 0;
                    $Amount = 0;
                    $FifthField = "";
                    $PaymentDate = "";
                    $CreditDate = "";
                    $ProtocolId = null;
                    $ProtocolYear = null;
                    $Name = "";
                    
                    $n_LineCount++;
                    
                    //NOMINATIVO
                    if($a_CSVLine['Nominativo'] != ''){
                        $Name = $a_CSVLine['Nominativo'];
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Nominativo assente.';
                        continue;
                    }
                    
                    //IMPORTO
                    if($a_CSVLine['Importo'] != ''){
                        $Amount = str_replace(',','.',$a_CSVLine['Importo']);
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Importo assente.';
                        continue;
                    }
                    
                    //QUINTO CAMPO
                    if($a_CSVLine['QuintoCampo'] != ''){
                        $FifthField = $a_CSVLine['QuintoCampo'];
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Quinto campo assente.';
                        continue;
                    }
                    
                    //DATA VERSAMENTO
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['DataVersamento'])){
                        $PaymentDate = $FormattedDate->format('Y-m-d');
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Data versamento assente o non valida.';
                        continue;
                    }
                    
                    //DATA ACCREDITO
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['DataAccredito'])){
                        $CreditDate = $FormattedDate->format('Y-m-d');
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Data accredito assente o non valida.';
                        continue;
                    }
                    
                    //CAUSALE
                    if($a_CSVLine['Causale'] != ''){
                        $ReasonNote = $a_CSVLine['Causale'];
                        //RECUPERO I PROTOCOLLI DALLA CAUSALE
                        $protocolArray = getFineDataFromNote($ReasonNote);
                        if($protocolArray != null)
                        {
                            $ProtocolId = $protocolArray[0];
                            $ProtocolYear = $protocolArray[1];
                        }
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Causale non presente.';
                        continue;
                    }
                    
                    //CONTROLLO VERBALI ASSOCIABILI
                    if(isset($ProtocolId, $ProtocolYear)){
                        $rs_Fine = $rs->Select('Fine', "CityId='$CityId' AND ProtocolId=$ProtocolId AND ProtocolYear=$ProtocolYear AND StatusTypeId NOT IN('".implode(',', STATUSTYPEID_VERBALI_STATI_FINALI)."')", 'RegDate DESC');
                        if($rs->gestoreErrori->UltimaEsecuzioneInErrore()){
                            $a_ErrorLines[$n_LineCount] = 'Errore nell\'identificazione di atti associabili.';
                            continue;
                        }
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $r_Fine = $rs->getArrayLine($rs_Fine);
                            $FineId = $r_Fine['Id'];
                        }
                    }
                    
                    //CONTROLLO PAGAMENTO GIà ESISTENTE
                    if($PaymentDate != null && $Amount > 0 && $Name != "" && $ReasonNote != '' && $FifthField != ''){
                        $b_HaQuintoCampo = $b_HaQuintoCampoUguale = false;
                        $NameSQL = mysqli_real_escape_string($rs->conn,$Name);
                        $ReasonNoteSQL = mysqli_real_escape_string($rs->conn,$ReasonNote);
                        
                        $rs_FinePayment = $rs->Select('FinePayment', "PaymentDate='$PaymentDate' AND Amount=$Amount AND Name='$NameSQL' AND Note='$ReasonNoteSQL'");
                        if($rs->gestoreErrori->UltimaEsecuzioneInErrore()){
                            $a_ErrorLines[$n_LineCount] = 'Errore nell\'identificazione di pagamenti già esistenti';
                            continue;
                        }
                        
                        if(mysqli_num_rows($rs_FinePayment) > 0){
                            //Se trova pagamenti esamina il quinto campo
                            while($r_FinePayment = $rs->getArrayLine($rs_FinePayment)){
                                if($r_FinePayment['ImportReceiptNumber'] != ''){
                                    $b_HaQuintoCampo = true;
                                    if($r_FinePayment['ImportReceiptNumber'] == $FifthField){
                                        $b_HaQuintoCampoUguale = true;
                                    }
                                }
                            }
                            
                            //Se trova un Pagamento duplicato con lo stesso quinto campo allora:
                            //Se c'è il flag di forzatura, lo inserisce in quelli da bonificare
                            //Altrimenti salta l'inserimento
                            if($b_HaQuintoCampoUguale){
                                if($ForceDuplicate){
                                    $FineId = 0;
                                } else continue;
                            }
                            //Se trova un Pagamento duplicato ma con quinto campo diverso lo salva in da bonificare
                            elseif($b_HaQuintoCampo){
                                $FineId = 0;
                            }
                        }
                    }
                    
/////////////////////INSERIMENTO DATI
                    $a_FinePayment = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                        array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                        array('field' => 'PaymentDocumentId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentDocumentId, 'settype' => 'int'),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Name),
                        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                        array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => $CreditDate),
                        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
                        array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $ReasonNote), //Causale
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                        array('field' => 'ImportReceiptNumber', 'selector' => 'value', 'type' => 'str', 'value' => $FifthField), //Facciamo la stessa cosa fatta su imp_pagopa_exe.php, perchè il quinto campo che ci passano non è di 16 caratteri
                    );
                    
                    //SE TROVA UN VERBALE A CUI ASSOCIARE IL PAGAMENTO LO SCORPORA
                    if($FineId > 0){
                        
                        //Se viene impostato il pagamento ridotto controllo se è previsto dall'articolo
                        if($PaymentDocumentId == 0){
                            //Controllo se l'articolo del verbale prevede pagamento ridotto
                            $rs_Reduced = $rs->Select('V_FineTariff', "FineId=$FineId AND ReducedPayment > 0");
                            //Se non è previsto imposto automaticamente il pagamento come normale
                            if(mysqli_num_rows($rs_Reduced) == 0)
                                $PaymentDocumentId = 1;
                        }
                        
                        $ReminderDate = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"))['ReminderDate'] ?? null;
                        
                        //Se si fa lo scorporo per l'importo ridotto (0) altrimenti per il normale (1)
                        $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $Amount, $FineId, $CityId, $ProtocolYear, $PaymentDate, $ReminderDate);
                        
                        $a_FinePayment[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['Fee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['ResearchFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['NotificationFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'PercentualFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['PercentualFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CustomerFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'CanFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CanFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'CadFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CadFee'], 'settype' => 'flt');
                    }
                    
                    $rs->Insert('FinePayment', $a_FinePayment);
                    
                    if($rs->gestoreErrori->UltimaEsecuzioneInErrore()){
                        $a_ErrorLines[$n_LineCount] = 'Errore nel salvataggio del pagamento';
                    } else $n_CompletedCount++;
                }
                
                if($n_CompletedCount>0){
                    $a_Import = array(
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>6),
                        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$ImportFile),
                        array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_CompletedCount),
                    );
                    
                    $rs->Insert('ImportedFiles',$a_Import);
                    
                    $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
                    while($r_UserMail = mysqli_fetch_array($rs_UserMail)){
                        $str_Content = $r_UserMail['CityTitle'].": sono state elaborate n. ".$n_CompletedCount." violazioni.";
                        $a_Mail = array(
                            array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                            array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione"),
                            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
                            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
                            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
                        );
                        $rs->Insert('Mail',$a_Mail);
                    }
                }
                
                unlink($path.$ImportFile);
            } else $str_Error = 'File CSV non valido per questa importazione.<br>Colonne previste: '.count($a_CSVColumns).', Colonne identificate: '.count($a_CSVFirstLine);
        } else  $str_Error = 'File CSV non valido per questa importazione. La struttura presenta le seguenti differenze:'.
            (!empty($a_CSVColumnsDiffs['extra']) ? '<br><br>Colonne aggiuntive: '.implode(', ', $a_CSVColumnsDiffs['extra']) : '').
            (!empty($a_CSVColumnsDiffs['missing']) ? '<br><br>Colonne mancanti: '.implode(', ', $a_CSVColumnsDiffs['missing']) : '');
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
} else $str_Error = "Specificare il file da importare.";

if($str_Error != '') {
    $_SESSION['Message']['Error'] = $str_Error;
} else if(!empty($a_ErrorLines)){
    $str_Warning = 'Non è stato possibile inserire alcuni dati:<br>';
    foreach($a_ErrorLines as $line => $message){
        $str_Warning .= "Riga $line: $message";
    }
    $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: imp_pagopa_spontaneous_type2.php".$Filters);
