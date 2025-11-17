<?php
define('INDEX_HABITUALFINES', 'PP_HabitualFines');
define('INDEX_FINE_DEFINITIVE', 1);
define('INDEX_FINE_NOTDEFINITIVE', 2);
define('INDEX_COMMUNICATION_NOTCREATED', 1);
define('INDEX_COMMUNICATION_CREATED', 2);
define('INDEX_COMMUNICATION_SENT', 3);
define('INDEX_COMMUNICATION_NOTIFIED', 4);
define('INDEX_TRESPASSER_NOTNOVICE', 1);
define('INDEX_TRESPASSER_NOVICE', 2);
define('INDEX_HABITUAL_YES', 1);
define('INDEX_HABITUAL_NO', 2);

define('MGMT_PREFECTCOMMUNICATION_ACTION_CREATEDOC', "CreateDoc");
define('MGMT_PREFECTCOMMUNICATION_ACTION_DELETEDOC', "DeleteDoc");
define('MGMT_PREFECTCOMMUNICATION_ACTION_UPDATE', "Update");
define('MGMT_PREFECTCOMMUNICATION_ACTION_PRINTPDF', "PDF");
define('MGMT_PREFECTCOMMUNICATION_ACTION_PRINTXLS', "XLS");

define('MGMT_PREFECTCOMMUNICATION_SENDTYPE_OPT', serialize(array(
    1 => "Raccomandata",
    2 => "PEC",
)));
define('MGMT_PREFECTCOMMUNICATION_FINE_OPT', serialize(array(
    INDEX_FINE_DEFINITIVE => "Solo verbali definitivi",
    INDEX_FINE_NOTDEFINITIVE => "Solo verbali non definitivi",
)));
define('MGMT_PREFECTCOMMUNICATION_STATUS_OPT', serialize(array(
    INDEX_COMMUNICATION_NOTCREATED => "Non creata",
    INDEX_COMMUNICATION_CREATED => "Creata",
    INDEX_COMMUNICATION_SENT => "Spedita",
    INDEX_COMMUNICATION_NOTIFIED => "Notificata"
)));
define('MGMT_PREFECTCOMMUNICATION_TRESPASSER_OPT', serialize(array(
    INDEX_TRESPASSER_NOTNOVICE => "Solo i titolari di patente di guida da oltre due anni",
    INDEX_TRESPASSER_NOVICE => "Solo i neo patentati titolari di patente di guida da meno di due anni",
)));
define('MGMT_PREFECTCOMMUNICATION_HABITUAL_OPT', serialize(array(
    INDEX_HABITUAL_YES => "Solo i recidivi nel biennio",
    INDEX_HABITUAL_NO => "Solo i NON recidivi nel biennio",
)));

function mgmtPrefectCommunicationWhere() {
    global $Search_Plate;
    global $Search_Code;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_Type;
    
    $str_Where = "1=1";
    
    if ($Search_Plate != "") {
        $str_Where .= " AND F.VehiclePlate='$Search_Plate'";
    }
    if ($Search_Code != "") {
        $str_Where .= " AND F.Code LIKE '%$Search_Code%'";
    }
    if ($Search_FromFineDate != "") {
        $str_Where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_Where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
    }
    if ($Search_FromProtocolId != "") {
        $str_Where .= " AND F.ProtocolId>=$Search_FromProtocolId";
    }
    if ($Search_ToProtocolId != "") {
        $str_Where .= " AND F.ProtocolId<=$Search_ToProtocolId";
    }
    if ($Search_FromProtocolYear != "") {
        $str_Where .= " AND F.ProtocolYear>=$Search_FromProtocolYear";
    }
    if ($Search_ToProtocolYear != "") {
        $str_Where .= " AND F.ProtocolYear<=$Search_ToProtocolYear";
    }
    switch($Search_Type){
        case INDEX_COMMUNICATION_NOTCREATED : $str_Where .= " AND FD.Id IS NULL"; break;
        case INDEX_COMMUNICATION_CREATED : $str_Where .= " AND FD.Id > 0"; break;
        case INDEX_COMMUNICATION_SENT : $str_Where .= " AND FPC.SendDate IS NOT NULL"; break;
        case INDEX_COMMUNICATION_NOTIFIED : $str_Where .= " AND FPC.NotificationDate IS NOT NULL"; break;
    }
        
    return $str_Where;
}

function mgmtPrefectCommunicationPostProcess($a_Results){
    require_once(CLS."/cls_view.php");
    global $rs;
    global $Search_Definitive;
    global $Search_Trespasser;
    global $Search_HasHabitual;
    
    foreach($a_Results as $index => $record){
        $a_Results[$index][INDEX_HABITUALFINES] = 0;
        $FineId = $record['FineId'];
        
        if(($Search_Trespasser == INDEX_TRESPASSER_NOTNOVICE && mgmtPrefectCommunicationIsNovice($record['LicenseDate'])) ||
            ($Search_Trespasser == INDEX_TRESPASSER_NOVICE && !mgmtPrefectCommunicationIsNovice($record['LicenseDate']))){
            unset($a_Results[$index]);
            continue;
        }
        
        //Solo se si seleziona "Solo verbali definitivi" oppure "Solo verbali non definitivi"
        if(($Search_Definitive == INDEX_FINE_DEFINITIVE) || ($Search_Definitive == INDEX_FINE_NOTDEFINITIVE)){
            //Prima valutare ricorso, poi la recidiva
            if($record['DisputeId'] > 0){
                $disputeView = new CLS_VIEW(MGMT_DISPUTE);
                $r_FineDispute = $rs->getArrayLine($rs->selectQuery($disputeView->generateSelect("F.Id=$FineId",null, "GradeTypeId DESC",1)));
                
                $cls_dispute = new cls_dispute();
                $cls_dispute->setDispute($r_FineDispute,1);
                $disputeStatus = $cls_dispute->a_info['responseCode'];
                
                //Controlla se il ricorso è in attesa, rinviato, sospeso o accolto per determinare se è un non definitivo
                $fineIndexType = (($disputeStatus >= 1 && $disputeStatus <= 4) || ($disputeStatus == 6)) ? INDEX_FINE_NOTDEFINITIVE : INDEX_FINE_DEFINITIVE;
                
                //Salta la riga se il tipo selezionato dal filtro è diverso dal tipo di verbale del ciclo
                if($Search_Definitive != $fineIndexType){ 
                    unset($a_Results[$index]);
                    continue;
                }
            }
            //Nel caso non vi sia ricorso, se il verbale non è notificato su strada, verifica se sono passati i termini per farlo, in caso negativo non restituisco il record
            else if($record['FineNotificationType'] != 2){
                $CurrentDate = date('Y-m-d');
                $DisputeLimitDate = date('Y-m-d', strtotime($record['NotificationDate']. " + ".(DISPUTE_DAY_LIMIT)." days"));
                
                //Controlla se i giorni passati sono inferiori o uguali a 60 per determinare se è un non definitivo
                $fineIndexType = ($CurrentDate <= $DisputeLimitDate) ? INDEX_FINE_NOTDEFINITIVE : INDEX_FINE_DEFINITIVE;
                
                //Salta la riga se il tipo selezionato dal filtro è diverso dal tipo di verbale del ciclo
                if($Search_Definitive != $fineIndexType){
                    unset($a_Results[$index]);
                    continue;
                }
            }
        }
        
        //Se per l'articolo violato è prevista una delle recidive, verifico se esistono verbali con la stessa violazione entro un biennio dalla data di violazione
        if($record['Habitual'] > 0 || $record['LossHabitual'] > 0 || $record['RevisionHabitual'] > 0 || $record['RevocationHabitual']){
            $query = "
                SELECT f4.FineId
                FROM FineTrespasser f4
                JOIN Fine f on f.Id = f4.FineId
                JOIN FineArticle f2 on f.Id = f2.FineId
                LEFT JOIN FineAdditionalArticle f3 on f3.FineId = f.Id
                WHERE f4.TrespasserId={$record['TrespasserId']} 
                AND FineDate BETWEEN DATE_SUB('{$record['FineDate']}',INTERVAL 2 YEAR) AND '{$record['FineDate']}' 
                AND COALESCE(f2.ArticleId,f3.ArticleId)={$record['ArticleId']} 
                AND f4.FineId < {$record['FineId']}";
            $habitual = mysqli_num_rows($rs->SelectQuery($query));
            $a_Results[$index][INDEX_HABITUALFINES] = $habitual;
            
            //Se non vi è recidiva, controllo se almeno l'articolo prevede di base ritiro, sospensione, revoca o revisione della patente, in caso negativo non restituisco il record
            if($habitual <= 0 && $record['SuspensionLicense'] <= 0 && $record['LossLicense'] <= 0 && $record['RevisionLicense'] <= 0 && $record['RevocationLicense'] <= 0){
                unset($a_Results[$index]);
                continue;
            }
        }
        
        if(($Search_HasHabitual == INDEX_HABITUAL_NO && $habitual > 0) ||
            ($Search_HasHabitual == INDEX_HABITUAL_YES && $habitual <=0 )){
                unset($a_Results[$index]);
                continue;
        }
    }
    return $a_Results;
}

function mgmtPrefectCommunicationUsedFilters(){
    $a_Filters = array();
    
    global $Search_Plate;
    global $Search_Code;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_FromProtocolYear;
    global $Search_ToProtocolYear;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $Search_Definitive;
    global $Search_Type;
    global $Search_Trespasser;
    global $Search_HasHabitual;
    
    $a_FineTypes = unserialize(MGMT_PREFECTCOMMUNICATION_FINE_OPT);
    $a_StatusTypes = unserialize(MGMT_PREFECTCOMMUNICATION_STATUS_OPT);
    $a_TrespasserTypes = unserialize(MGMT_PREFECTCOMMUNICATION_TRESPASSER_OPT);
    $a_HabitualOpt = unserialize(MGMT_PREFECTCOMMUNICATION_HABITUAL_OPT);
    
    if ($Search_Plate != "") {
        $a_Filters['Targa'] = $Search_Plate;
    }
    if ($Search_Code != "") {
        $a_Filters['Riferimento'] = $Search_Code;
    }
    if ($Search_FromFineDate != "") {
        $a_Filters['Da data violazione'] = $Search_FromFineDate;
    }
    if ($Search_ToFineDate != "") {
        $a_Filters['A data violazione'] = $Search_ToFineDate;
    }
    if ($Search_FromProtocolId != "") {
        $a_Filters['Da cron.'] = $Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != "") {
        $a_Filters['A cron.'] = $Search_ToProtocolId;
    }
    if ($Search_FromProtocolYear != "") {
        $a_Filters['Da anno'] = $Search_FromProtocolYear;
    }
    if ($Search_ToProtocolYear != "") {
        $a_Filters['Ad anno'] = $Search_ToProtocolYear;
    }
    $a_Filters['Tipologia verbale'] = $a_FineTypes[$Search_Definitive] ?? "Tutti";
    $a_Filters['Stato comunicazione'] = $a_StatusTypes[$Search_Type] ?? "Tutti";
    $a_Filters['Tipo trasgressore'] = $a_TrespasserTypes[$Search_Trespasser] ?? "Tutti";
    $a_Filters['Rapporto trasgressore/violazione'] = $a_HabitualOpt[$Search_HasHabitual] ?? "Tutti i trasgressori";
    
    return $a_Filters;
}

function mgmtPrefectCommunicationIsNovice($licenseDate){
    if($licenseDate){
        $d = DateTime::createFromFormat('Y-m-d', $licenseDate);
        $valid = $d && $d->format('Y-m-d') == $licenseDate;
        
        if($valid){
            $CurrentDate = date('Y-m-d');
            $NoviceLimitDate = date('Y-m-d', strtotime($licenseDate. " + ".NOVICE_DRIVER_YEARS." years"));
            return $NoviceLimitDate >= $CurrentDate;
        }
    }
    return false;
}

function mgmtPrefectCommunicationCommStatus($notificationDate, $sendDate, $documentId){
    $status = INDEX_COMMUNICATION_NOTCREATED;
    
    if(!empty($notificationDate)){
        $status = INDEX_COMMUNICATION_NOTIFIED;
    } else if(!empty($sendDate)){
        $status = INDEX_COMMUNICATION_SENT;
    } else if(!empty($documentId)){
        $status = INDEX_COMMUNICATION_CREATED;
    }
    
    return $status;
}

function pdfColumnSize($pdf, int $col){
    $max = 12;
    $min = 1;
    
    $dimensions = [
        //unità di default (PDF_UNIT) in millimetri
        "margins"   => $pdf->GetMargins(),
        "width"     => $pdf->getPageWidth(),
    ];
    $singleCol = round(($dimensions["width"] - ($dimensions["margins"]["left"] + $dimensions["margins"]["right"])) / $max);
    return $singleCol * ($col > $max ? $max : ($col < $min ? $min : $col));
}