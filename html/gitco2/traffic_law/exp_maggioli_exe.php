<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require(INC."/function.php");
include(INC."/pagopa.php");
require(INC."/initialization.php");

function writeLog(String $tipo, String $messaggio){
    switch($tipo){
        case 'N': trigger_error("<EXP_MAGGIOLI> DEBUG -> $messaggio", E_USER_NOTICE); break;
        case 'W': trigger_error("<EXP_MAGGIOLI> ATTENZIONE -> $messaggio", E_USER_WARNING); break;
        case 'E': trigger_error("<EXP_MAGGIOLI> ERRORE -> $messaggio", E_USER_WARNING); break;
        default : trigger_error("<EXP_MAGGIOLI> DEBUG -> $messaggio", E_USER_NOTICE); break;
    }
}

$Filters = CheckValue('Filters', 's');
$ExportType = CheckValue('Search_Type', 'n');
$Year = CheckValue('Search_Year', 's');
$FromRegDate = CheckValue('Search_FromFineDate', 's');
$ToRegDate = CheckValue('Search_ToFineDate', 's');
$TypePlate = CheckValue('TypePlate', 's');

$str_Error = '';
$str_Where = "1=1 AND F.CityId='{$_SESSION['cityid']}'";
$delimiter = ';';

if($ExportType > 0){
    
    switch($ExportType){
        case 1:
            $folder = "INVII";
            $fileName = "{$_SESSION['cityid']}_INVII_".date('Y-m-d');
            $view = EXP_MAGGIOLI_INVII;
            $documentColumnName = 'Immagine';
            break;
        case 2:
            $folder = "CARTOLINE";
            $fileName = "{$_SESSION['cityid']}_CARTOLINE_".date('Y-m-d');
            $view = EXP_MAGGIOLI_CARTOLINE;
            $documentColumnName = 'Immagine';
            break;
        case 3:
            $folder = "PAGAMENTI";
            $fileName = "{$_SESSION['cityid']}_PAGAMENTI_".date('Y-m-d');
            $view = EXP_MAGGIOLI_PAGAMENTI;
            $documentColumnName = null;
            break;
        default:
           $_SESSION['Message']['Error'] = 'Tipo esportazione non riconosciuto.';
           header("location: exp_maggioli.php".$Filters);
           DIE;
    }
    
    if (!file_exists(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder")){
        mkdir(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder", 0777, true);
    }
    
    if ($Year != ''){
        $str_Where .= " AND F.ProtocolYear=".$Year;
    }
    if ($FromRegDate != "") {
        $str_Where .= " AND F.RegDate>='".DateInDB($FromRegDate)."'";
    }
    if ($ToRegDate != "") {
        $str_Where .= " AND F.RegDate<='".DateInDB($ToRegDate)."'";
    }
    if ($TypePlate == "N") {
        $str_Where .= " AND F.CountryId='Z000'";
    } else {
        $str_Where .= " AND F.CountryId!='Z000'";
    }
    
    $cls_view = new CLS_VIEW($view);
    $rs_Table = $rs->SelectQuery($cls_view->generateSelect($str_Where, null, 'F.ProtocolId ASC'));
    $RowNumber = mysqli_num_rows($rs_Table);
    
    if($RowNumber > 0){
        if($exportFile = fopen(VIOLATION_FOLDER."/{$_SESSION['cityid']}/$folder/$fileName.csv", 'w')){
            //Scrive la prima riga con nomi colonne, rimuove Id perchè serve solo come riferimento e non deve finire sul csv
            $header = array_column(mysqli_fetch_fields($rs_Table), 'name');
            $header = array_diff($header, array('Id'));
            fputcsv($exportFile, $header, $delimiter);
            
            //Valori
            while($row = $rs->getArrayLine($rs_Table)){
                if(isset($documentColumnName) && $row[$documentColumnName] != ''){
                    if(!@copy(
                        ($TypePlate == 'N' ? NATIONAL_FINE : FOREIGN_FINE)."/{$_SESSION['cityid']}/{$row['Id']}/{$row[$documentColumnName]}", 
                        (VIOLATION_FOLDER)."/{$_SESSION['cityid']}/$folder/{$row[$documentColumnName]}",
                        )){
                            writeLog('W', "Errore nella copia del documento: ".error_get_last()['message']);
                        }
                }
                
                //Scrive una riga con i valori, rimuove Id perchè serve solo come riferimento e non deve finire sul csv
                unset($row['Id']);
                fputcsv($exportFile, $row, $delimiter);
            }
            
            fclose($exportFile);
            
            $_SESSION['Documentation'] = "$MainPath/public/_VIOLATION_/{$_SESSION['cityid']}/$folder/$fileName.csv";
        } else {
            $str_Error = 'Impossibile creare il file .csv: '.error_get_last()['message'];
        }
    }
} else {
    $str_Error = 'Tipo esportazione non riconosciuto.';
}

if ($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
} else {
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

header("location: exp_maggioli.php".$Filters);

