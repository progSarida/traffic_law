<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(CLS."/cls_progressbar.php");

ini_set('max_execution_time', 3000);

function newMessageObject($file, $message){
    $o_Result = new stdClass();
    $o_Result->File = $file;
    $o_Result->Message = $message;
    return $o_Result;
}

$a_Files = CheckValue('checkbox', 'a');
$ProgressFileName = CheckValue("ProgressFile", "s");

$ProgressFile = TMP . "/".$ProgressFileName;
$ProgressBar = new CLS_PROGRESSBAR(count($a_Files));
$ProgressBar->writeJSON($count = 0, $ProgressFile, array('Passo' => 'Inizializzazione'));

$CityId = $_SESSION['cityid'];
$a_Results = array('Success' => array(), 'Fail' => array());
$b_Fail = false;

foreach ($a_Files as $fullPath){
    $pathParts = pathinfo($fullPath);
    $pathPieces = explode(DIRECTORY_SEPARATOR, $pathParts['dirname']);
    if(isset($pathPieces[0])){
        $newZipName = "kria_{$pathPieces[0]}_{$pathParts['filename']}";
        $ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Download archivio in corso...'));
        if(copy(IMPORT_FOLDER.'/'.$CityId.'/'.$fullPath, VIOLATION_FOLDER.'/'.$CityId.'/'.$newZipName.'.zip')){
            $zip = new ZipArchive();
            if($zip->open(VIOLATION_FOLDER.'/'.$CityId.'/'.$newZipName.'.zip') === true ){
                if (($fileIndex = $zip->locateName('kria.csv', ZipArchive::FL_NOCASE)) !== false){
                    //Rinomina il csv e chiude lo zip perchè altrimenti non salverebbe
                    $zip->renameIndex($fileIndex, $newZipName.'.csv');
                    $zip->close();
                    //Riapre lo zip e lo estrae nella cartella letta 
                    if($zip->open(VIOLATION_FOLDER.'/'.$CityId.'/'.$newZipName.'.zip')){
                        $ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Estrazione archivio in corso...'));
                        $zip->extractTo(VIOLATION_FOLDER.'/'.$CityId.'/');
                        $zip->close();
                        
                        if(!rename(IMPORT_FOLDER.'/'.$CityId.'/'.$fullPath, IMPORT_FOLDER.'/'.$CityId.'/'.$fullPath.'.elab')){
                            $b_Fail = true;
                            array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Impossibile rinominare l'archivio in .elab."));
                        }
                        if(!unlink(VIOLATION_FOLDER.'/'.$CityId.'/'.$newZipName.'.zip')){
                            $b_Fail = true;
                            array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Impossibile eliminare l'archivio scaricato: $newZipName.zip."));
                        }
                    } else {
                        $b_Fail = true;
                        array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Errore nella riapertura dell'archivio: $newZipName.zip."));
                    }
                } else {
                    $b_Fail = true;
                    array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "File kria.cvs non trovato all'interno dell'archivio."));
                    if(!unlink(VIOLATION_FOLDER.'/'.$CityId.'/'.$newZipName.'.zip')){
                        array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Impossibile eliminare l'archivio scaricato: $newZipName.zip."));
                    }
                }
            } else {
                $b_Fail = true;
                array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Errore nell'apertura dell'archivio: $newZipName.zip."));
            }
        } else {
            $b_Fail = true;
            array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Errore nella copia dell'archivio: $newZipName.zip."));
        }
    } else {
        $b_Fail = true;
        array_push($a_Results['Fail'], newMessageObject($pathParts['basename'], "Percorso non valido: $fullPath"));
    }
    
    $ProgressBar->writeJSON($count++, $ProgressFile, array('Passo' => ''));
    
    if(!$b_Fail) array_push($a_Results['Success'], newMessageObject($pathParts['basename'], "Completato con successo."));
}

$ProgressBar->writeJSON($count, $ProgressFile, array('Passo' => 'Completato'));

echo json_encode(
    array(
        "Result" => $a_Results
    )
);

