<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_pagopa_spontaneous.php");
require_once(INC."/initialization.php");

ini_set('max_execution_time', 5000);

/** @var CLS_DB $rs */

$ImportFile = CheckValue('ImportFile','s');
$Filters = CheckValue('Filters', 's');

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
    'Data pag.',
    'Importo pag.',
    'Verbale',
    'Data accert.',
    'Targa',
    'Pagante',
    'Contatto pagante',
);

if($ImportFile != ''){
    $fileStream = @fopen($path.$ImportFile,  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, IMP_PAGOPA_SPONTANEOUS_SEPARATOR);
        $a_CSVColumnsDiffs = compareColumns($a_CSVColumns, $a_CSVFirstLine); //Confronta le colonne previste con la prima riga del file
        
        if(empty($a_CSVColumnsDiffs)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                
                $a_IndexedLines = buildLinesArray($a_CSVColumns, IMP_PAGOPA_SPONTANEOUS_SEPARATOR, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $Note = '';
                    $FineId = 0;
                    
                    $n_LineCount++;
                    
                    //PROTOCOLLO
                    if(checkFineProtocol($a_CSVLine['Verbale'])){
                        list($ProtocolId, $ProtocolYear) = explode('/', $a_CSVLine['Verbale']);
                        $Note .= "Cron. : {$a_CSVLine['Verbale']}".PHP_EOL;
                    } else {
                        $Note .= "Cron. da verificare: {$a_CSVLine['Verbale']}".PHP_EOL;
                    }
                    
                    //TARGA
                    if($a_CSVLine['Targa'] != ''){
                        $VehiclePlate = $a_CSVLine['Targa'];
                        $Note .= "Targa: {$a_CSVLine['Targa']}".PHP_EOL;
                    }
                    
                    //DATA ACCERT.
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['Data accert.'])){
                        $FineDate = $FormattedDate->format('Y-m-d');
                        $Note .= "Data accert. : {$a_CSVLine['Data accert.']}".PHP_EOL;
                    } else if($a_CSVLine['Data accert.'] != '') {
                        $Note .= "Data accert. da verificare: {$a_CSVLine['Data accert.']}".PHP_EOL;
                    }
                    
                    //DATA PAG.
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['Data pag.'])){
                        $PaymentDate = $FormattedDate->format('Y-m-d');
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Data pagamento assente o non valida.';
                        continue;
                    }
                    
                    //IMPORTO PAG.
                    if($a_CSVLine['Importo pag.'] != ''){
                        $Amount = number_format((float)$a_CSVLine['Importo pag.'],2,'.','');
                    } else {
                        $a_ErrorLines[$n_LineCount] = 'Importo assente.';
                        continue;
                    }
                    
                    //PAGANTE
                    if($a_CSVLine['Pagante'] != ''){
                        $Name = mysqli_real_escape_string($rs->conn, $a_CSVLine['Pagante']);
                    }
                    
                    //CONTATTO PAGANTE
                    if($a_CSVLine['Contatto pagante'] != ''){
                        $Note .= "Contatto pagante: {$a_CSVLine['Contatto pagante']}";
                    }
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($VehiclePlate,$ProtocolId,$ProtocolYear)){
                        $rs_Fine = $rs->Select('Fine', "CityId='$CityId' AND ProtocolId=$ProtocolId AND ProtocolYear=$ProtocolYear AND VehiclePlate='$VehiclePlate'", 'RegDate DESC');
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $r_Fine = $rs->getArrayLine($rs_Fine);
                            $FineId = $r_Fine['Id'];
                        }
                    }
                    
                    //CONTROLLO PAGAMENTO GIà ESISTENTE SE HO TROVATO UN VERBALE COLLEGATO
                    if(isset($PaymentDate,$Amount,$Name)){
                        $rs_FinePayment = $rs->Select('FinePayment', "PaymentDate='$PaymentDate' AND Amount='$Amount' AND Name='$Name'", 'RegDate DESC');
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
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Name),
                        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
                        array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
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
                    
                    $n_CompletedCount++;
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
        } else $str_Error = 'File CSV non valido per questa importazione. La struttura presenta le seguenti differenze:'.
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
    $_SESSION['Message']['Warning'] = $str_Warning;
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: imp_pagopa_spontaneous.php".$Filters);



