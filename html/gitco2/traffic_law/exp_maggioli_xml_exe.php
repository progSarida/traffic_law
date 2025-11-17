<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_exp_maggioli_xml.php");
require_once(INC."/pagopa.php");
require_once(INC."/initialization.php");

if (!file_exists(EXPORT_FOLDER)){
    mkdir(EXPORT_FOLDER, 0750, true);
}

$a_codiceCliente = unserialize(MAGGIOLIEXP_CODICI_CLIENTE);
$a_prefissiUID = unserialize(MAGGIOLIEXP_UID_PREFIXES);

$Filters = CheckValue('Filters', 's');
$ExportType = CheckValue('Search_Type', 'n');
$Year = CheckValue('Search_Year', 's');
$FromRegDate = CheckValue('Search_FromFineDate', 's');
$ToRegDate = CheckValue('Search_ToFineDate', 's');
$TypePlate = CheckValue('TypePlate', 's');

if(!array_key_exists($_SESSION['cityid'], $a_codiceCliente)){
    $_SESSION['Message']['Error'] = "L'ente in uso non dispone di un codice cliente Maggioli per poter usare questa procedura.";
    header("location: exp_maggioli_xml.php".$Filters);
    DIE;
}

$str_Error = $str_Warning = '';
$str_Where = "1=1 AND F.CityId='{$_SESSION['cityid']}'";

$codiceCliente = $a_codiceCliente[$_SESSION['cityid']] ?? '';
$fileName = 'DaSTaMG_'.$codiceCliente.'_'.date('Ymd_His').'_';
$xmlElementName = "Posizione";
$addDocuments = false;
$addDocumentsFolder = 'DOCUMENTALE';
$docsPath = ($TypePlate == 'N' ? NATIONAL_FINE : FOREIGN_FINE)."/{$_SESSION['cityid']}";
$exportFolder = EXPORT_FOLDER."/maggioli/{$_SESSION['cityid']}";

if (!file_exists($exportFolder)){
    mkdir($exportFolder, 0750, true);
}

if ($Year != ''){
    $str_Where .= " AND F.ProtocolYear=".$Year;
}
if ($FromRegDate != "") {
    $str_Where .= " AND DATE(@RegDate)>='".DateInDB($FromRegDate)."'";
}
if ($ToRegDate != "") {
    $str_Where .= " AND DATE(@RegDate)<='".DateInDB($ToRegDate)."'";
}
if ($TypePlate == "N") {
    $str_Where .= " AND F.CountryId='Z000'";
} else {
    $str_Where .= " AND F.CountryId!='Z000'";
}
    
switch($ExportType){
    case 1:
        $folder = "ANAGRAFICHE";
        $xmlRootName = "Anagrafiche";
        $fileName .= 'anagrafiche';
        $view = EXP_MAGGIOLI_ANAGRAFICHE_XML;
        $regDateColumn = 'COALESCE(FT.ReceiveDate, FT.RegDate)';
        $type = 1;
        break;
    case 2:
        $folder = "NOTIFICHE";
        $xmlRootName = "Notifiche";
        $fileName .= 'notifiche';
        $view = EXP_MAGGIOLI_NOTIFICHE_XML;
        $regDateColumn = 'FN.RegDate';
        $type = 2;
        break;
    case 3:
        $folder = "PAGAMENTI";
        $xmlRootName = "Pagamenti";
        $fileName .= 'pagamenti';
        $view = EXP_MAGGIOLI_PAGAMENTI_XML;
        $regDateColumn = 'FP.RegDate';
        $type = 3;
        break;
    case 4:
        $folder = "SPESENOTIFICA";
        $xmlRootName = "SpeseNotifica";
        $fileName .= 'spese_notifiche';
        $view = EXP_MAGGIOLI_SPESENOTIFICA_XML;
        $regDateColumn = 'FH.SendDate';
        $type = 4;
        break;
    case 5:
        $folder = "DOCUMENTALE";
        $xmlRootName = "Documentale";
        $fileName .= 'documentale';
        $addDocuments = true;
        $view = EXP_MAGGIOLI_DOCUMENTALE_XML;
        $regDateColumn = 'FD.VersionDate';
        //Dato che questa vista ha una union a cui andrei ad agganciare la str_Where che filtra
        //per una colonna inerente solo alla prima select, la sostituisco prima della sostituzione generale
        $unionWhere = array('FinePayment' => str_replace('@RegDate', 'FP.RegDate', $str_Where));
        $type = 5;
        break;
    default:
       $_SESSION['Message']['Error'] = 'Tipo esportazione non riconosciuto.';
       header("location: exp_maggioli_xml.php".$Filters);
       DIE;
}

if (!file_exists(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder")){
    mkdir(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder", 0770, true);
}

$cls_view = new CLS_VIEW($view);
$cls_view->unionWheres = $unionWhere ?? null;

$a_ToReplace = array(
    "@MaggioliCode" => $a_codiceCliente[$_SESSION['cityid']],
    "@Regexp" => MAGGIOLIEXP_UID_REGEX,
    "@UidPrefix" => $a_prefissiUID[$_SESSION['cityid']] ?? '???',
    "@RegDate" => $regDateColumn,
    "@ExclusionDate" => '2023-05-05',   //Data esportazioni già effettuate. Bug2746
);

$query = strtr($cls_view->generateSelect($str_Where, null, 'QUERY_Id DESC, STR_TO_DATE(QUERY_RegDate, "%d/%m/%Y") DESC'), $a_ToReplace);

$rs_Table = $rs->SelectQuery($query);
$RowNumber = mysqli_num_rows($rs_Table);

if($RowNumber > 0){
    $data = $rs->getResults($rs_Table);
    
    //Se flusso documentale toglie i record di cui non si trova il file a sistema
    if($addDocuments){
        foreach($data as $index => $record){
            if(!file_exists("$docsPath/{$record['QUERY_Id']}/".basename(str_replace('\\', '/', $record['Documento'])))){
                unset($data[$index]);
            }
        }
    }
    
    $zip = new ZipArchive();
    if ($zip->open(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder/$fileName.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFromString("$fileName.xml", buildMaggioliXml($xmlRootName, $xmlElementName, $data));
        
        //Se flusso documentale aggiunge i documenti all'interno della cartella DOCUMENTALE
        if($addDocuments){
            $zip->addEmptyDir($addDocumentsFolder);
            foreach($data as $record){
                $zip->addFile(
                    "$docsPath/{$record['QUERY_Id']}/".basename(str_replace('\\', '/', $record['Documento'])),
                    "$addDocumentsFolder/".basename(str_replace('\\', '/', $record['Documento']))
                );
            }
        }
        
        $zip->close();
        
        if(!copy(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder/$fileName.zip", "$exportFolder/$fileName.zip")){
            $str_Warning .= 'Non è stato possibile copiare l\'archivio dei dati esportati nella cartella di backup.';
        }
        
        $_SESSION['Documentation'] = VIOLATION_FOLDER_HTML."/{$_SESSION['cityid']}/$folder/$fileName.zip";
    } else $str_Error .= 'Errore nella creazione dell\'archivio dei dati esportati.';
}

$rs->Start_Transaction();

$a_Insert = array(
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    array('field'=>'Type','selector'=>'value','type'=>'int','value'=>$type),
    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>"$fileName.zip"),
    array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>count($data)),
    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>"Anno di esercizio selezionato: $Year"),
);

if ($FromRegDate != "") {
    $a_Insert[] = array('field'=>'FromDate','selector'=>'value','type'=>'date','value'=>DateInDB($FromRegDate));
}
if ($ToRegDate != "") {
    $a_Insert[] = array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>DateInDB($ToRegDate));
}
$rs->Insert('ExportedFiles', $a_Insert);

$rs->End_Transaction();

if ($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
} else if($str_Warning != '') {
    $_SESSION['Message']['Warning'] = $str_Warning;
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: exp_maggioli_xml.php".$Filters);

