<?php
require_once(CLS."/cls_view.php");

//per tentare di risolvere l'errore del massimo tempo di 60 secondi superato
ini_set('max_execution_time', 3000);
//per tentare di risolvere l'errore del massimo spazio di 512 MB superato
//per enti grandi come Savona siamo passati a 2GB
ini_set('memory_limit', '2048M');

//***Variabili e costanti Maschera***
define("TIPO_VELOCITA", 2);

$a_RadioChk = array("","","");

$a_ChkTypePayment = array("","","","","");
$a_ChkTypeViolation = array("","","","");
$a_ChkTypeRule = array("","","");
$a_ChkTypeNotification = array("","","","","","","");
$a_ChkTypeArchive = array("","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","");
$a_ChkHasInjunction = array("","","");

$str_Radio = "";
$str_CheckGenre1 = "";
$str_CheckGenre2 = "";
$Search_Genre = CheckValue('Search_Genre','n');

$Search_ForeignFineNotPayed     = CheckValue('Search_ForeignFineNotPayed','n'); //Verbali esteri non pagati

$btn_search                     = CheckValue('btn_search','n');

$d_PrintDate                    = (CheckValue('PrintDate','s')=="") ? date("d/m/Y") : CheckValue('PrintDate','s');

$n_TypeViolation                = CheckValue('Search_TypeViolation','n');
$n_TypePayment                  = CheckValue('Search_TypePayment','n');
$n_TypeRule                     = CheckValue('Search_TypeRule','n');
$n_TypeNotification             = CheckValue('Search_TypeNotification','n');
$n_TypeArchive                  = CheckValue('Search_TypeArchive','n');
$n_HasInjunction                = CheckValue('Search_HasInjunction','n'); 

$FineArchive                    = CheckValue('FineArchive','n');
$FineDispute                    = CheckValue('FineDispute', 'n');

$str_ChkFineArchive0 = $str_ChkFineArchive1 = $str_ChkFineArchive2 = "";
$str_ChkFineDispute0 = $str_ChkFineDispute1 = $str_ChkFineDispute2 = "";

$a_ChkTypePayment[$n_TypePayment] = "SELECTED";
$a_ChkTypeViolation[$n_TypeViolation] = "SELECTED";
$a_ChkTypeRule[$n_TypeRule] = "SELECTED";
$a_ChkTypeNotification[$n_TypeNotification] = "SELECTED";
$a_ChkTypeArchive[$n_TypeArchive] = "SELECTED";
$a_ChkHasInjunction[$n_HasInjunction] = "SELECTED";

//Ricorsi
if ($FineDispute == 0) {
    $str_ChkFineDispute0 = " CHECKED ";
} else if ($FineDispute == 2) {
    $str_ChkFineDispute2 = " CHECKED ";
} else {
    $str_ChkFineDispute1 = " CHECKED ";
}

//Inclusione archiviati
if($FineArchive==0){        //Escludi
    $str_ChkFineArchive0 =" CHECKED ";
}else if($FineArchive==2){  //Solo loro
    $str_ChkFineArchive2 =" CHECKED ";
}else{                      //Includi
    $str_ChkFineArchive1 =" CHECKED ";
}

//Persona
if($Search_Genre==1){
    $str_CheckGenre1 = " SELECTED ";
}else if($Search_Genre==2){
    $str_CheckGenre2 = " SELECTED ";
}

//***Fine Variabili Maschera***

//***Variabili Stampa***

define('INDEX_TRESPASSERS', 'PP_AdditionalTrespassers');

define('INDEX_AMOUNTPAYED', 'PP_AmountPayed');
define('INDEX_INTERESTFEE', 'PP_InterestFee');
define('INDEX_AMOUNTOWNED', 'PP_AmountOwned');
define('INDEX_AMOUNTFEE', 'PP_AmountFee');
define('INDEX_REMINDERNOTIFICATIONFEE', 'PP_ReminderNotificationFee');
define('INDEX_PREVIOUSPROTOCOL', 'PP_PreviousProtocol');
define('INDEX_REMINDERCOUNT', 'PP_ReminderCount');
define('INDEX_REMINDERSTATUS', 'PP_ReminderStatus');
define('INDEX_REDUCEDDATE', 'PP_ReducedDate');

define('INDEX_PAYMENTSTATUS', 'PP_PaymentStatus');

define('INDEX_ISLASTREMINDERAVAILABLE', 'PP_IsLastReminderAvailable');
define('INDEX_ISFINESHOWABLE', 'PP_IsFineShowable');
define('INDEX_ISREMINDERSHOWABLE', 'PP_IsReminderShowable');

$Action = CheckValue("Action", "s");

$str_Violation = "";
$str_Detector = "";

//***Fine Variabili Stampa***



//***Funzioni***

function impostaQueryJoinFineHistory()
    {
    global $str_QueryFineHistory;
    global $n_TypeNotification;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_HasPEC;
    global $Search_FromSendDate;
    global $Search_ToSendDate;
    
    $str_QueryFineHistory = "";
    //filtro verbali pec
    if($Search_HasPEC > 0)
        {
        $str_QueryFineHistory .= " JOIN FineHistory FH ON F.Id = FH.FineId AND FH.NotificationTypeId=15 ";
        }
    elseif($Search_FromSendDate != "" || $Search_ToSendDate != "")
        {
        $str_QueryFineHistory = " JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6 ";
        }
    else
        {
        $str_QueryFineHistory = " LEFT JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6";
        }
    
    return $str_QueryFineHistory;
    }

function impostaQueryJoinArticle()
    {
        $str_QueryArticle = " JOIN Article A ON A.Id = FA.ArticleId";
        //Al momento ritorna sempre vuoto. Cambiare in caso si voglia discriminare per qualche articolo
        return $str_QueryArticle;
    }

function impostaOrderBy()
    {
    $strOrder = "F.Id, F.ProtocolYear, F.ProtocolId";
    return $strOrder;
    }
    
function impostaWhere(){
    global $Search_Address;
    global $Search_FromProtocolId;
    global $Search_ToProtocolId;
    global $Search_Trespasser;
    global $Search_Locality;
    global $n_TypeNotification;
    global $Search_FromNotificationDate;
    global $Search_ToNotificationDate;
    global $Search_ArticleId;
    global $Search_FromFineDate;
    global $Search_ToFineDate;
    global $n_TypePayment;
    global $FineDispute;
    global $str_ChkFineDispute0;
    global $str_ChkFineDispute1;
    global $str_ChkFineDispute2;
    global $FineArchive;
    global $str_ChkFineArchive0;
    global $str_ChkFineArchive1;
    global $str_ChkFineArchive2;
    global $Search_Genre;
    global $str_CheckGenre1;
    global $str_CheckGenre2;
    global $Search_NotificationStatus;
    global $Search_CurrentYear;
    global $Search_ForeignFineNotPayed;
    global $Search_HasKindFine;
    global $Search_NationalityId;
    global $Search_FromSendDate;
    global $Search_ToSendDate;
    global $n_TypeViolation;
    global $Search_FromArchiveDate;
    global $Search_ToArchiveDate;
    global $Search_HasKindSendDate;
    global $n_TypeArchive;
    global $Search_Violation;
    global $Search_Detector;
    global $Search_More;
    global $Search_DayTimeSlot;
    global $n_HasInjunction;
    
    $str_Where = " F.CityId='".$_SESSION['cityid']."'";
    
    if($Search_NotificationStatus > 0)    $str_Where .= " AND F.PreviousId > 0 AND FA.ViolationTypeId != 5";
    if($Search_Address != "")    $str_Where .= " AND F.Address='".$Search_Address."'";
    if($Search_FromProtocolId>0)    $str_Where .= " AND F.ProtocolId>=".$Search_FromProtocolId;
    if($Search_ToProtocolId>0)      $str_Where .= " AND F.ProtocolId<=".$Search_ToProtocolId;
    if($Search_Trespasser!="")     $str_Where .= " AND  concat(ifnull(T.CompanyName,''),' ',ifnull(T.Surname,''),' ',ifnull(T.Name,'')) LIKE '%".addslashes($Search_Trespasser)."%'";
    if($Search_Locality!="")        $str_Where .= " AND F.Locality='".$Search_Locality."' ";
    if($Search_CurrentYear>0)        $str_Where .= " AND F.ProtocolYear=".$_SESSION['year'];
    if($Search_ForeignFineNotPayed>0) $str_Where .= " AND T.CountryId != 'Z000' AND F.Id NOT IN(SELECT FineId FROM FinePayment WHERE CityId='".$_SESSION['cityid']."')";
    
    if($Search_NationalityId == 1){
        $str_Where .= " AND T.CountryId = 'Z000'";
    }
    elseif($Search_NationalityId == 2){
        $str_Where .= " AND T.CountryId != 'Z000'";
    }
    
    //Ruolo
    if($n_HasInjunction == 1){ //Iscritti
        $str_Where .= " AND F.Id IN(SELECT FI1.FineId FROM FineInjunction FI1)";
    }
    elseif($n_HasInjunction == 2){  //Non iscritti
        $str_Where .= " AND F.Id NOT IN(SELECT FI1.FineId FROM FineInjunction FI1)";
    }
    
    
    //I vincoli sullo StatusTypeId vengono definiti in queste due variabili (Start-End)
    //Dovesse rendersi necessario modificare la forbice di filtro sullo StatusTypeId in qualche punto del codice, basterà cambiare il valore delle variabili
    $str_StatusTypeIdStart = "F.StatusTypeId > 10 ";
    $str_StatusTypeIdEnd = "";
    
    if($Search_HasKindFine){
        $str_StatusTypeIdStart = " (F.StatusTypeId >= 8 AND F.StatusTypeId <> 10)";
    }
    
    if($n_TypeViolation==1){ //preavvisi
        $str_Where .= " AND FineTypeId = 2 and ProtocolId > 0 AND F.Id NOT IN (SELECT FineId FROM FineHistory WHERE NotificationTypeId = 6)";
    } else if ($n_TypeViolation == 3){//solleciti emessi
        $str_Where .= " AND ProtocolId > 0 AND FR.Id IS NOT NULL";
    } else if($n_TypeViolation== 0 || $n_TypeViolation==2) {//tutti / verbali
        
        if($n_TypeViolation == 0){//tutti
            $str_Where .= " AND ProtocolId > 0 AND $str_StatusTypeIdStart";
        } elseif($n_TypeViolation==2) { //verbali
            $str_Where .= " AND ((FineTypeId != 2 and ProtocolId > 0 and $str_StatusTypeIdStart) or (FineTypeId = 2 and ProtocolId > 0 AND F.Id IN (SELECT FineId FROM FineHistory WHERE NotificationTypeId = 6))) ";
        }
        
        if($n_TypeNotification>0){
            
            if($n_TypeNotification == 5 || $n_TypeNotification == 6){
                //$str_QueryFineHistory .= " JOIN FineHistory FH ON F.Id = FH.FineId AND FH.NotificationTypeId=6 LEFT JOIN Flow FL ON FL.Id = FH.FlowId ";
                
                if($n_TypeNotification == 5){
                    $str_Where .= " AND (FLW.DocumentTypeId NOT IN(17,18) AND FLW.DocumentTypeId IS NOT NULL AND FineNotificationType <> 2) ";
                } else {
                    $str_Where .= " AND ((FLW.DocumentTypeId IN(17,18) AND FLW.DocumentTypeId IS NOT NULL) OR FineNotificationType = 2) ";
                }
            } else {
                //$str_QueryFineHistory .= " JOIN FineHistory FH ON F.Id = FH.FineId AND FH.NotificationTypeId=6 ";
                
                if($n_TypeNotification==1 || $n_TypeNotification==4){
                    //Il filtro Notifica: Da a, ha solo effetto se stò cercando atti notificati
                    if($Search_FromNotificationDate != "")  $str_Where .= " AND FH.DeliveryDate>='".DateInDB($Search_FromNotificationDate)."'";
                    if($Search_ToNotificationDate != "")    $str_Where .= " AND FH.DeliveryDate<='".DateInDB($Search_ToNotificationDate)."'";
                    
                    if($n_TypeNotification==1){
                        $str_Where .= " AND ((FH.ResultId >= 1 AND FH.ResultId <= 5) OR (FH.ResultId >= 7 AND FH.ResultId <= 9)) ";
                    } else {
                        $str_Where .= " AND (FH.ResultId IN(6,22,24)) ";
                    }
                } else if($n_TypeNotification==3){
                    $str_Where .= " AND ((FH.ResultId>9 AND FH.Resultid<22) OR FH.Resultid=23) ";
                } else if($n_TypeNotification==2){
                    $str_Where .= " AND FH.ResultId IS NULL ";
                }
            }
        }
    }
    
    //Data postalizzazione
    if($Search_FromSendDate != "" || $Search_ToSendDate != ""){
        if($Search_FromSendDate != "")  $str_Where .= " AND FH.SendDate>='".DateInDB($Search_FromSendDate)."'";
        if($Search_ToSendDate != "")    $str_Where .= " AND FH.SendDate<='".DateInDB($Search_ToSendDate)."'";
    }
    //Articolo
    if($Search_ArticleId>0){
        $str_Where .= " AND ArticleId=".$Search_ArticleId;
    }
    
    //Data accertamento
    if($Search_FromFineDate != "")  $str_Where .= " AND F.FineDate>='".DateInDB($Search_FromFineDate)."'";
    if($Search_ToFineDate != "")    $str_Where .= " AND F.FineDate<='".DateInDB($Search_ToFineDate)."'";
    
    //Data archivio
    if ($Search_FromArchiveDate != "") $str_Where .= " AND FAR.ArchiveDate IS NOT NULL AND FAR.ArchiveDate >='" . DateInDB($Search_FromArchiveDate) . "'";
    if ($Search_ToArchiveDate != "") $str_Where .= " AND FAR.ArchiveDate IS NOT NULL AND FAR.ArchiveDate <='" . DateInDB($Search_ToArchiveDate) . "'";
    
    //Ricorsi
    if ($FineDispute == 0) {
        $str_Where .= " AND F.Id NOT IN (SELECT FDI1.FineId FROM FineDispute FDI1 WHERE FDI1.DisputeStatusId=1)";
    } else if ($FineDispute == 2) {
        $str_Where .= " AND F.Id IN (SELECT FDI1.FineId FROM FineDispute FDI1 WHERE FDI1.DisputeStatusId=1)";
    }
    
    //Inclusione archiviati
    if($FineArchive==0){        //Escludi
        //Prende anche gli inviti in AG archiviati se si seleziona "Includi inviti in AG"
        if($Search_HasKindSendDate)
            $str_StatusTypeIdEnd = "F.StatusTypeId<=34";
            else
                $str_StatusTypeIdEnd = "F.StatusTypeId<34";
                $str_Where .= " AND $str_StatusTypeIdStart AND $str_StatusTypeIdEnd ";
                
    }else if($FineArchive==2){  //Solo loro
        $str_StatusTypeIdStart = "F.StatusTypeId>=35";
        $str_StatusTypeIdEnd = "F.StatusTypeId<=37";
        $str_Where .= " AND $str_StatusTypeIdStart AND $str_StatusTypeIdEnd ";
        
    }else{                      //Includi
        $str_StatusTypeIdEnd = "F.StatusTypeId<=37";
        $str_Where .= " AND $str_StatusTypeIdStart AND $str_StatusTypeIdEnd ";
    }
    
    //Selezione tipologia archiviati
    $str_Where .= $n_TypeArchive!=0 ? (" AND F.StatusTypeId = ".$n_TypeArchive) : "";
    
    //Persona
    if($Search_Genre==1){
        $str_Where .= " AND Genre != 'D'";
    }else if($Search_Genre==2){
        $str_Where .= " AND Genre = 'D'";
    }
    
    //Violazione
    if ($Search_Violation>0) {
        $str_Where .= " AND FA.ViolationTypeId=".$Search_Violation;
//         $rs_Violation = $rs->SelectQuery("SELECT Title FROM ViolationType WHERE Id=".$Search_Violation);
//         $str_Violation = mysqli_fetch_assoc($rs_Violation)['Title'];
        if($Search_Detector>0)
            {
            $str_Where .= " AND FA.DetectorId=".$Search_Detector;
//             $rs_Detector = $rs->SelectQuery("SELECT TitleIta FROM Detector WHERE Id=".$Search_Detector);
//             $str_Detector = mysqli_fetch_assoc($rs_Detector)['TitleIta'];
            }
        }
    
    //Detrermina se il verbale è in fascia diurna o notturna
    if($Search_DayTimeSlot == FASCIA_DIURNA) {
        $str_Where .= " AND (F.FineTime >= '".FINE_DAILY_SLOT_START."' AND F.FineTime <= '".FINE_DAILY_SLOT_END."')";
    }
    else if($Search_DayTimeSlot == FASCIA_NOTTURNA) {
        $str_Where .= " AND (F.FineTime > '".FINE_DAILY_SLOT_END."' OR F.FineTime < '".FINE_DAILY_SLOT_START."')";
    }
    
    if(!$Search_HasKindSendDate){
        //Per escludere gli inviti in AG
        //KindSendDate è nulla per gli avvisi bonari e valorizzata per gli inviti in AG
        //In questo caso si vuole escludere gli inviti in AG ma non gli avvisi bonari. Non essendo sicuri che gli avvisi bonari non abbiano mai KindSendDate, li andiamo ad includere in caso avessero KindSendDate e FineHistory.NotificationTypeId 30 (Avvisi bonari)
        $str_Where .= " AND (F.KindSendDate IS NULL OR (F.KindSendDate IS NOT NULL AND F.Id IN(SELECT FHI1.FineId FROM FineHistory FHI1 WHERE FHI1.NotificationTypeId = 30))) ";
    }
    
    //Filtro "Ulteriori dati"
    if($Search_More > 0)
    {
        $str_Where .= " AND CASE WHEN ";
        switch($Search_More){
            case 1:{
                $str_Where .= "FN.PaymentProcedure IS NOT NULL THEN FN.PaymentProcedure = 1 ELSE TPP.PaymentProcedure IS NULL";
                break;
            }
            case 2:{
                $str_Where .= "FN.ReminderAdditionalFeeProcedure IS NOT NULL THEN FN.ReminderAdditionalFeeProcedure = 1 ELSE TRA.ReminderAdditionalFeeProcedure IS NOT NULL";
                break;
            }
            case 3:{
                $str_Where .= "FN.126BisProcedure IS NOT NULL THEN FN.126BisProcedure = 1 ELSE T126.126BisProcedure IS NULL";
                break;
            }
            case 4:{
                $str_Where .= "FN.PresentationDocumentProcedure IS NOT NULL THEN FN.PresentationDocumentProcedure = 1 ELSE TPD.PresentationDocumentProcedure IS NULL";
                break;
            }
            case 5:{
                $str_Where .= "FN.LicensePointProcedure IS NOT NULL THEN FN.LicensePointProcedure > 0 ELSE TLP.LicensePointProcedure IS NULL";
                break;
            }
            case 6:{
                $str_Where .= "FN.InjunctionProcedure IS NOT NULL THEN FN.InjunctionProcedure = 1 ELSE TIP.InjunctionProcedure IS NULL";
                break;
            }
        }
        $str_Where .= " END";
    }
    
    return $str_Where;
}

function creaQuery()
    {
    global $btn_search;
    
    $cls_view = new CLS_VIEW(PRN_REGISTRY);
    $a_ToReplace = array(
        "@JoinFineHistory" => impostaQueryJoinFineHistory(),
        "@JoinArticle" => impostaQueryJoinArticle()
        );
    
    $fineHistorySelect = " ,FH.DeliveryDate,
            FH.SendDate,
            FH.CustomerFee,
            FH.ResearchFee,
            FH.NotificationFee AS NotificationFee,
            FH.CustomerFee AS CustomerFee,
            FH.NotificationFee AS NotificationFee,
            FH.ResearchFee AS ResearchFee
            ";
    
    $str_Query = strtr($cls_view->generateSelect(impostaWhere(), null, impostaOrderBy()), $a_ToReplace);
    $str_Query = substr_replace($str_Query,$fineHistorySelect,strpos($str_Query,"FROM"),0);
    
    return $str_Query;
    }
    
    