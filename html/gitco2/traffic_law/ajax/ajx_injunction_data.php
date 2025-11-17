<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_exp_injunction.php");
require_once(INC."/initialization.php");

$jsonBody = json_decode($_POST['Body'], true); //Composto da array di righe
$response = array();
$str_Warning = '';

if($jsonBody){
    $rs->Start_Transaction();
    
    foreach ($jsonBody as $obj){    //Ciclo l'array
        $a_ControllerIds = $a_Controllers = array();
        $dirPath = $obj["dirpath"];
        $fileName = $obj["filename"];   //Nome file tracciato (txt senza estensione)
        $sendDate = $obj["senddate"];   //Data di inoltro al concessionario (unico dato compilabile)
        $processcontrollerid = $obj["processcontrollerid"] ?: 0;   //Data di inoltro al concessionario (unico dato compilabile)
        $officercontrollerid = $obj["officercontrollerid"] ?: 0;   //Data di inoltro al concessionario (unico dato compilabile)
        
        if($processcontrollerid > 0) $a_ControllerIds[] = $processcontrollerid;
        if($officercontrollerid > 0) $a_ControllerIds[] = $officercontrollerid;
        
        if(!empty($a_ControllerIds)){
            $a_Controllers = $rs->getResults($rs->SelectQuery("SELECT Id, Name FROM Controller WHERE CityId='{$_SESSION['cityid']}' AND Id > 0 AND Id IN(".implode(',', $a_ControllerIds).")"));
            $a_Controllers = array_column($a_Controllers, "Name", "Id");
        }
        
        $r_FineInjunction = $rs->getArrayLine($rs->Select("FineInjunction", "FileName='$fileName'"));
        
        if($r_FineInjunction){
            if(!empty($r_FineInjunction['FileNamePDF'])){
                $OldFileName = $r_FineInjunction['FileNamePDF'].".pdf";
                
                $a_fileId = explode('--', $fileName);
                $fileId = $a_fileId[1] ?? '';
                
                if(!empty($fileId)){
                    $NewFileName = $_SESSION['cityid']."_".$_SESSION['year']."_" . date("Y-m-d_H-i").EXP_INJUNCTION_FILEID_SEPARATOR.$fileId;
                    $NewFullFileName = $NewFileName.".pdf";
                    
                    if(!empty($sendDate) && ($r_FineInjunction['ConcessionaireSendDate'] != DateInDB($sendDate) || $r_FineInjunction['ProcessControllerId'] != $processcontrollerid || $r_FineInjunction['OfficerControllerId'] != $officercontrollerid)){
                        
                        $a_FineInjunction = array(
                            array('field'=>'ConcessionaireSendDate','selector'=>'value','type'=>'date','value'=>DateInDB($sendDate),'settype'=>'date'),
                            array('field'=>'FileNamePDF','selector'=>'value','type'=>'str','value'=>$NewFileName),
                            array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                            array('field'=>'ProcessControllerId','selector'=>'value','type'=>'int','value'=>$processcontrollerid,'settype'=>'int'),
                            array('field'=>'OfficerControllerId','selector'=>'value','type'=>'int','value'=>$officercontrollerid,'settype'=>'int')
                        );
                        $rs->Update("FineInjunction", $a_FineInjunction, "FileName = '".$fileName."'");
                        
                        if(!printConcessionaireDatePDF($sendDate, $a_Controllers[$processcontrollerid] ?? '', $a_Controllers[$officercontrollerid] ?? '', $dirPath, $OldFileName, $NewFullFileName)){
                            $str_Warning .= "Errore nel salvataggio del file $NewFullFileName, i dati sono stati salvati.<br>";
                        }
                    }
                    //Nel caso manchi il suffisso che serve a generare i pdf, va modificato sulla banca dati per ogni riga di FineInjunction con quel nome e a sistema, il nome del file aggiungendo il suffisso EXP_INJUNCTION_FILEID_SEPARATOR seguito da 13 caratteri casuali (assicurasi che il suffisso censito non esista già sulla banca dati)
                } else $str_Warning .= "Il nome del file del tracciato $fileName è in un formato obsoleto. Contattare l'assistenza.<br>";
            }
        }
    }
    
    $rs->End_Transaction();
}

if(!empty($str_Warning)){
    $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
} else {
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

echo json_encode($response);