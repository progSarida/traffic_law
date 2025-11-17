<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(INC."/initialization.php");

ini_set('max_execution_time', 5000);

function buildLinesArray($columnsArray, $csvSeparator, $fileStream){
    $n_CSVReadLine = 0;
    $a_IndexedLines = array();
    
    while (($a_CSVLine = fgetcsv($fileStream, 0, $csvSeparator)) !== false){
        foreach($a_CSVLine as $lineIndex => $lineValue){
            $encoding = mb_detect_encoding($lineValue,'UTF-8, ASCII, ISO-8859-1');
            $a_IndexedLines[$n_CSVReadLine][$columnsArray[$lineIndex]] = mb_convert_encoding($lineValue, 'UTF-8', $encoding);
        }
        $n_CSVReadLine++;
    }
    return $a_IndexedLines;
}

global $rs;

$ImportFile = CheckValue('ImportFile','s');
$Filters = CheckValue('Filters', 's');

$str_Error = '';
$str_Separator = ';';
$n_LineCount = 0;
$n_CompletedCount = 0;
$a_Errors = array();

$PaymentTypeId = 9;
$TableId=0;
//Mettiamo sempre nazionale, se un giorno andrà distinto saneremo i dati in base alla nazionalità della targa o trasgressore
$BankMgmt = $r_Customer['NationalBankMgmt'];
$FinePaymentSpecificationType = $r_Customer['FinePaymentSpecificationType'];
$PaymentDocumentId = 0;
$ImportationId = 0;

$CityId = $_SESSION['cityid'];
$path = PAYMENT_FOLDER."/$CityId/";

$a_CSVColumns = array (
    'CODICE_SERVIZIO',
    'CODICE_SOTTOSERVIZIO',
    'NUMERO_LISTA',
    'TIPO_IDENTIFICATIVO_UNIVOCO',
    'CODICE_FISCALE_DEBITORE',
    'CODICE_DEBITORE',
    'ANAGRAFICA_DEBITORE',
    'INDIRIZZO_DEBITORE',
    'CIVICO_DEBITORE',
    'CAP_DEBITORE',
    'LOCALITA_DEBITORE',
    'PROVINCIA_DEBITORE',
    'NAZIONE_DEBITORE',
    'EMAIL_DEBITORE',
    'PEC_DEBITORE',
    'ALTRO_RECAPITO',
    'CODICE_IDENTIFICATIVO_BOLLETTINO',
    'IDENTIFICATIVO_DEBITO',
    'IDENTIFICATIVO_DISPOSIZIONE',
    'SCADENZA',
    'DATA_INIZIO_VALIDITA',
    'DATA_FINE_VALIDITA',
    'TIPO_CAUSALE',
    'CAUSALE_VERSAMENTO',
    'IMPORTO',
    'DETTAGLIO_VOCI',
    'ANNO_RIFERIMENTO',
    'CAUSALE_BOLLETTINO',
    'DATI_SPECIFICI_RISCOSSIONE',
    'NOME_PDF_ALLEGATO',
    'NOME_PDF_OUTPUT',
    'OPERAZIONE',
    'DATA_EFFETTIVA_INCASSO',
    'DATA_ACCREDITO',
    'ABI_ORDINANTE',
    'NUMERO_PROVVISORIO',
    'TRN_RIVERSAMENTO'
);


if($ImportFile != ''){
    $fileStream = @fopen($path.$ImportFile,  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, $str_Separator);
        $a_CSVMissingColumns = array_diff($a_CSVColumns, $a_CSVFirstLine);
        
        if(empty($a_CSVMissingColumns)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                
                $a_IndexedLines = buildLinesArray($a_CSVColumns, $str_Separator, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $Note = 'Importato da PagoPA: ';
                    $FineId = 0;
                    
                    $n_LineCount++;
                    
                    $Nome = $IUV = $Importo = $DataIncasso = $DataAccredito = $FineId = $ProtocolYear = null;
                    
                    //Nome
                    if($a_CSVLine['ANAGRAFICA_DEBITORE'] != ''){
                        $Nome = $a_CSVLine['ANAGRAFICA_DEBITORE'];
                    } 
                    
                    //Causale
                    if($a_CSVLine['CAUSALE_BOLLETTINO'] != ''){
                        $Note .= $a_CSVLine['CAUSALE_BOLLETTINO'];
                    }
                    
                    //IUV
                    if($a_CSVLine['CODICE_IDENTIFICATIVO_BOLLETTINO'] != ''){
                        $IUV = $a_CSVLine['CODICE_IDENTIFICATIVO_BOLLETTINO'];
                    } else {
                        $a_Errors[$n_LineCount][] = 'Iuv assente.';
                        continue;
                    }
                    
                    //Importo
                    if($a_CSVLine['IMPORTO'] != ''){
                        $Importo = str_replace(",", ".", $a_CSVLine['IMPORTO']);
                    } else {
                        $a_Errors[$n_LineCount][] = 'Importo assente.';
                        continue;
                    }
                    
                    //Data pagamento
                    if($a_CSVLine['DATA_EFFETTIVA_INCASSO'] != ''){
                        $DataIncasso = DateInDB($a_CSVLine['DATA_EFFETTIVA_INCASSO']);
                    } else {
                        $a_Errors[$n_LineCount][] = 'Data pagamento assente.';
                        continue;
                    }
                    
                    //Data accredito
                    if($a_CSVLine['DATA_ACCREDITO'] != ''){
                        $DataAccredito = DateInDB($a_CSVLine['DATA_ACCREDITO']);
                    } else {
                        $a_Errors[$n_LineCount][] = 'Data accredito assente.';
                        continue;
                    }
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($IUV)){
                        $rs_Fine = $rs->Select('Fine', "CityId='$CityId' AND PagoPA1 = '$IUV' OR PagoPA2 = '$IUV'", 'RegDate DESC');
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $r_Fine = $rs->getArrayLine($rs_Fine);
                            $FineId = $r_Fine['Id'];
                            $ProtocolYear = $r_Fine['ProtocolYear'];
                        }
                    }
                    
                    //CONTROLLO PAGAMENTO GIà ESISTENTE SE HO TROVATO UN VERBALE COLLEGATO
                    if(isset($FineId,$DataIncasso)){
                        $rs_FinePayment = $rs->Select('FinePayment', "CityId='$CityId' AND FineId=$FineId AND PaymentDate='$DataIncasso'");
                        if(mysqli_num_rows($rs_FinePayment) > 0){
                            //Se trova il pagamento, non prosegue e passa alla prossima riga
                            continue;
                        }
                    }
                    
/////////////////////INSERIMENTO DATI
                    $a_FinePayment = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                        array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                        array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                        array('field' => 'PaymentDocumentId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentDocumentId, 'settype' => 'int'),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Nome),
                        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataIncasso),
                        array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataAccredito),
                        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
                        array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Importo, 'settype' => 'flt'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $Note),
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
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
                        $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $Importo, $FineId, $CityId, $ProtocolYear, $DataIncasso, $ReminderDate);
                        
                        $a_FinePayment[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['Fee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['ResearchFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['NotificationFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'PercentualFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['PercentualFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CustomerFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'CanFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CanFee'], 'settype' => 'flt');
                        $a_FinePayment[] = array('field' => 'CadFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CadFee'], 'settype' => 'flt');
                    }
                    
                    $rs->Insert('FinePayment', $a_FinePayment);
                    
                    $n_CompletedCount++;
                }
                
                if($n_CompletedCount>0){
                    $a_Import = array(
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>9),
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
                            array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Nuova importazione PagoPA"),
                            array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
                            array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
                            array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
                        );
                        $rs->Insert('Mail',$a_Mail);
                    }
                }
                
                unlink($path.$ImportFile);
            } else $str_Error = 'File CSV non valido per questa importazione.<br>Colonne previste: '.count($a_CSVColumns).', Colonne identificate: '.count($a_CSVFirstLine);
        } else $str_Error = 'File CSV non valido per questa importazione.<br>La struttura non presenta i seguenti campi: '.implode(', ', $a_CSVMissingColumns);
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
} else $str_Error = "Specificare il file da importare.";

if($str_Error != '') {
    $_SESSION['Message']['Error'] = $str_Error;
} else if(!empty($a_Errors)){
    $str_Warning = 'Non è stato possibile inserire alcuni dati:<br>';
    foreach($a_Errors as $line => $message){
        $str_Warning .= "Riga $line: $message";
    }
    $_SESSION['Message']['Warning'] = $str_Warning;
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: imp_pagopa_sondrio.php".$Filters);



