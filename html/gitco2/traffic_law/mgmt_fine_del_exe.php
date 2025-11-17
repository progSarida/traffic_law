<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id= CheckValue('Id','n');
$CountryId = CheckValue('CountryId','s');
$str_Folder = ($CountryId=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;

$rs_Fine = $rs->Select("V_Fine", "Id=$Id");
$r_Fine = mysqli_fetch_array($rs_Fine);

$ProtocolId = $r_Fine['ProtocolId'];
$ViolationTypeId = $r_Fine['ViolationTypeId'];

$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."'");

if (mysqli_num_rows($rs_RuleType) > 0){
    $r_RuleType = mysqli_fetch_array($rs_RuleType);
    $RuleTypeId = $r_RuleType['Id'];
    $str_WhereRule = " AND RuleTypeId=$RuleTypeId";
} else $str_WhereRule = "";


$rs_MaxFine = $rs->SelectQuery("SELECT MAX(ProtocolId) AS MaxCronId From V_FineAll WHERE ProtocolId>0 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'].$str_WhereRule);
$r_MaxFineId = mysqli_fetch_array($rs_MaxFine);
//print_r($r_MaxFineId);


if ($ProtocolId <> $r_MaxFineId[0]) {
    header("location: mgmt_fine.php".$str_GET_Parameter."&error=Impossibile cancellare il verbale che non è l'ultimo creato!");
}



//controllo Ulteriori dati
//Ulteriori dati (procedure) se $chk_NotificationDAte è falso lavora su TMP_126BisProcedure / TMP_PresentationDocumentProcedure / TMP_LicensePointProcedure / TMP_PaymentProcedure /
$n_PaymentProcedure = ExistsPaymentProcedure($Id, $rs);
if ($n_PaymentProcedure) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. Il verbale è stato elaborato per l'emissione di un sollecito di pagamento/ingiunzione!");
}


$n_126BisProcedure = Exists126BisProcedure($Id, $rs);
if ($n_126BisProcedure) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. Il verbale è stato elaborato per la richiesta dei dati del trasgressore per l'art. 126 Bis!");
}


$n_PresentationDocumentProcedure = ExistsPresentationDocumentProcedure($Id, $rs);
if ($n_PresentationDocumentProcedure) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. Il verbale è stato elaborato per la richiesta della documentazione richiesta per l'art. 180!");
}

$n_LicensePointProcedure = ExistsLicensePointProcedure($Id, $rs);
if ($n_LicensePointProcedure) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. Il verbale è stato elaborato per la procedura di decurtazione punti della patente!");
}

// ricorsi
$n_Dispute = ExistsDispute($Id, $rs);
if ($n_Dispute) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. E' presente un ricorso!");
}

// dati trasgressore - documenti art 126
$n_Communication = ExistsCommunication($Id, $rs);
if ($n_Communication) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. E' presente della documentazione per art. 126!");
}


// dati trasgressore - documenti art 180
$n_Presentation = ExistsPresentation($Id, $rs);
if ($n_Presentation) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. E' presente della documentazione per art. 180!");
}

$n_Documentation = ExistsDocumentationNotGeneric($Id, $rs);
if ($n_Documentation) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. E' presente della documentazione specifica!");
}

// pagamenti
$n_Payment = ExistsPayment($Id, $rs);
if ($n_Payment) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. E' presente almeno un pagamento!");
}

// flussi
$n_Flow = ExistsFlow($Id, $rs);
if ($n_Flow) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. E' presente un flusso!");
}

// notifiche NON SU STRADA
$n_NotificationNotOnRoad = ExistsNotificationNotOnRoad($Id, $rs);
if ($n_NotificationNotOnRoad) {
    header("location: mgmt_fine.php".$str_GET_Parameter.
        "&error=Impossibile cancellare il verbale. Il verbale è stato notificato!");
}

// se passa tutto questo sposto i documenti
// cancello il residuo 


$rs->Start_Transaction();

//if ($r_FineRelated['StatusTypeId'] < 15){
    $rs_FineDocumentation = $rs->Select('FineDocumentation',"FineId=$Id"); // AND DocumentationTypeId=1");
    if (mysqli_num_rows($rs_FineDocumentation)>0) {
        if (is_dir($str_Folder."/".$_SESSION['cityid']."/".$Id)) {
            
            while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
                $Documentation = $r_FineDocumentation['Documentation'];
                $DocumentationId = $r_FineDocumentation['Id'];
                rename($str_Folder."/".$_SESSION['cityid']."/".$Id."/".$Documentation, REPORT_FOLDER."/".$_SESSION['cityid'] ."/". $Documentation);
                echo $str_Folder."/".$_SESSION['cityid']."/".$Id."/".$Documentation;
                echo '<br>';
                echo REPORT_FOLDER."/".$_SESSION['cityid']."/". $Documentation;
                echo '<br>';
                $rs->Delete('FineDocumentation',"Id = $DocumentationId");
            }
            
            if (count(scandir($str_Folder."/".$_SESSION['cityid']."/".$Id)) == 2) {
                echo "cancello ".$str_Folder."/".$_SESSION['cityid']."/".$Id;
                rmdir($str_Folder."/".$_SESSION['cityid']."/".$Id);
            }
        }
    }
   
    $rs->Delete('FineArticle','FineId='.$Id);
    $rs->Delete('FineAdditionalArticle','FineId='.$Id);
    $rs->Delete('FineOwner','FineId='.$Id);
    $rs->Delete('FineTrespasser','FineId='.$Id); //non filtro per tipo di trasgressore perché devo eliminarli tutti
    $rs->Delete('FineAdditionalController','FineId='.$Id);
    
    $rs->Delete('FineNotification','FineId='.$Id);
    $rs->Delete('FineHistory','FineId='.$Id);
    $rs->Delete('FinePayment','FineId='.$Id);
    $rs->Delete('FinePresentation','FineId='.$Id);
    $rs->Delete('FineRefund','FineId='.$Id);
    $rs->Delete('FineReminder','FineId='.$Id);
    $rs->Delete('FineCommunication','FineId='.$Id);
    
    $rs->Delete('FineInjunction','FineId='.$Id);
    $rs->Delete('FineAnomaly','FineId='.$Id);
    $rs->Delete('FineArchive','FineId='.$Id); //non dovrebbe esserci
  
    $rs->Delete('Fine','Id='.$Id);
// } 
// else {
//     $_SESSION['Archive']['Error'] = "Non è possibile annullare il verbale per: ". $Id;
//     header("location: mgmt_fine.php".$str_GET_Parameter);
//     exit;
// }

$rs->End_Transaction();

header("location: mgmt_fine.php".$str_GET_Parameter.
    "&answer=Cancellazione dell'ultimo verbale inserito effettuata con successo!");


function ExistsPaymentProcedure($FineId, $rs){
    $rs_TMP_PaymentProcedure = $rs->Select('TMP_PaymentProcedure',"FineId=".$FineId);
    $n_TMP_PaymentProcedure = mysqli_num_rows($rs_TMP_PaymentProcedure)>0;
    return $n_TMP_PaymentProcedure;
}

function Exists126BisProcedure($FineId, $rs){
    $rs_TMP_126BisProcedure = $rs->Select('TMP_126BisProcedure',"FineId=".$FineId);
    $n_TMP_126BisProcedure = mysqli_num_rows($rs_TMP_126BisProcedure)>0;
    return $n_TMP_126BisProcedure;
}

function ExistsPresentationDocumentProcedure($FineId, $rs){
    $rs_TMP_PresentationDocumentProcedure = $rs->Select('TMP_PresentationDocumentProcedure',"FineId=".$FineId);
    $n_TMP_PresentationDocumentProcedure = mysqli_num_rows($rs_TMP_PresentationDocumentProcedure)>0;
    return $n_TMP_PresentationDocumentProcedure;
}

function ExistsLicensePointProcedure($FineId, $rs){
    $rs_TMP_LicensePointProcedure = $rs->Select('TMP_LicensePointProcedure',"FineId=".$FineId);
    $n_TMP_LicensePointProcedure = mysqli_num_rows($rs_TMP_LicensePointProcedure)>0;
    return $n_TMP_LicensePointProcedure;
}

function ExistsDispute($FineId, $rs){
    $rs_Dispute = $rs->Select('FineDispute',"FineId=".$FineId);
    $n_Dispute = mysqli_num_rows($rs_Dispute)>0;
    return $n_Dispute;
}

// cercare in fineHistory di tipo 6 per il fineId in left join con Flow on Documentation = FileName
function ExistsFlow($FineId, $rs){
    
    $s_Select = "SELECT FineId, FileName ";
    $s_From = "FROM FineHistory INNER JOIN Flow ON Documentation = FileName ";
    $s_Where = "WHERE FineId = $FineId AND Documentation is not null";
    //echo $s_Select.$s_From.$s_Where;
    $rs_Flow = $rs->SelectQuery($s_Select.$s_From.$s_Where); 
    $n_Flow = mysqli_num_rows($rs_Flow)>0;
    return $n_Flow;
}

function ExistsDocumentationNotGeneric($FineId, $rs){
    $rs_Documentation = $rs->Select('FineDocumentation',"FineId=".$FineId." AND DocumentationTypeId >= 20 AND DocumentationTypeId < 50"); 
    $n_Documentation = mysqli_num_rows($rs_Documentation)>0;
    return $n_Documentation;
}

//Dati per art. 126
function ExistsCommunication($FineId, $rs){
    $rs_Communication = $rs->Select('FineDocumentation',"FineId=".$FineId." AND DocumentationTypeId= 20");
    $n_Communication = mysqli_num_rows($rs_Communication)>0;
    return $n_Communication;
}

//Dati per art. 180
function ExistsPresentation($FineId, $rs){
    $rs_Presentation = $rs->Select('FinePresentation',"FineId=".$FineId);
    $n_Presentation = mysqli_num_rows($rs_Presentation)>0;
    return $n_Presentation;
}

function ExistsPayment($FineId, $rs){
    $rs_Payment = $rs->Select('FinePayment',"FineId=".$FineId);
    $n_Payment = mysqli_num_rows($rs_Payment)>0;
    return $n_Payment;
}

function ExistsNotificationNotOnRoad($FineId, $rs){
    $rs_Notification = $rs->Select('FineNotification',"FineId=".$FineId." AND ResultId <> 8");
    $n_Notification = mysqli_num_rows($rs_Notification)>0;
    return $n_Notification;
}