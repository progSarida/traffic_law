<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_prefectcommunication.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");

global $rs;

function buildHeader($r_Customer){
    $str_Header = '<span style="line-height:1.1">';
    if($r_Customer['ManagerSector'] != ''){
        $str_Header .= $r_Customer['ManagerSector'] != '' ? $r_Customer['ManagerSector'] : '';
        $str_Header .= '<br>';
    }
    if($_SESSION['cityid']=="H452"){
        $str_Header .= 'Art.57 CPP e Art.11 c.1 L.a) e b) CDS<br>';
    } else if($r_Customer['ManagerAddress'] != ''){
        $str_Header .= $r_Customer['ManagerAddress'].'<br>';
    }
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ManagerPhone'] != ''){
        $str_Header .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Header .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Header .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Header .= $r_Customer['ManagerPhone'] ? 'TEL: '.$r_Customer['ManagerPhone'] : '';
    }
    return $str_Header.'</span>';
}

$Filters = CheckValue('Filters','s');
$FineId  = CheckValue('FineId','n');
$Action = CheckValue("Action", 's');
$ultimate = CheckValue("ultimate", 's');

$str_Error = '';

if($Action == MGMT_PREFECTCOMMUNICATION_ACTION_CREATEDOC){
    $cls_view = new CLS_VIEW(MGMT_PREFECTCOMMUNICATION);
    $query = $cls_view->generateSelect("F.Id=$FineId", null);
    $a_Results = $rs->getResults($rs->SelectQuery($query)) ?: array();
    
    $a_Results = mgmtPrefectCommunicationPostProcess($a_Results);
    $r_Result = reset($a_Results);
    
    $TrespasserId = $r_Result['TrespasserId'];
    
    $r_Fine  =                  $rs->getArrayLine($rs->Select('Fine', "Id=$FineId"));
    
    $Documentation = $ultimate
    ? "Comunicazione_Prefetto_{$r_Fine['ProtocolId']}_{$r_Fine['ProtocolYear']}_".date("H-i_Y-m-d").'.'.PDFA
    : 'export_prefectcommunication_'.$_SESSION['cityid'].'.'.PDFA;
    
    if($r_Fine['CountryId']=='Z000'){
        $str_DocumentFolder = NATIONAL_FINE.'/'.$_SESSION['cityid'].($ultimate ? "/$FineId" : '/create');
        $str_DocumentFolderHTML = NATIONAL_FINE_HTML.'/'.$_SESSION['cityid'].($ultimate ? '/'.$FineId : '/create');
    } else {
        $str_DocumentFolder = FOREIGN_FINE.'/'.$_SESSION['cityid'].($ultimate ? "/$FineId" : '/create');
        $str_DocumentFolderHTML = FOREIGN_FINE_HTML.'/'.$_SESSION['cityid'].($ultimate ? '/'.$FineId : '/create');
    }
    
    if (!is_dir($str_DocumentFolder)) {
        mkdir($str_DocumentFolder, 0770, true);
        chmod($str_DocumentFolder, 0770);
    }
    
    //TCPDF/////////////////////////////////////////////////////////
    //Coordinata inizio stampa testo dinamico
    $TextStartCoord = array('X'=>10, 'Y'=>92);
    $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);
    $pdf->NationalFine = 1;
    $pdf->CustomerFooter = 0;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Comunicazione prefetto');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 8));
    $pdf->SetPrintHeader(true);
    $pdf->RightHeader = true;
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetCellPadding(0);
    
    $a_ProtocolLetterLocality = array();
    $Locality = "";
    
    $a_ProtocolLetters =        $rs->getResults($rs->Select(MAIN_DB . '.City', ($r_Customer['CityUnion'] > 1) ? "UnionId='{$_SESSION['cityid']}'" : "Id='{$_SESSION['cityid']}'"));
    $r_FineArticle  =           $rs->getArrayLine($rs->Select('FineArticle', "FineId=$FineId"));
    $r_Article =                $rs->getArrayLine($rs->Select('Article', "Id={$r_FineArticle['ArticleId']}"));
    $r_ArticleTariff =          $rs->getArrayLine($rs->Select('ArticleTariff', "ArticleId={$r_FineArticle['ArticleId']} AND Year={$r_Fine['ProtocolYear']}"));
    $r_ViolationTypeLetter =    $rs->getArrayLine($rs->Select('ViolationTypeLetter', "ViolationTypeId={$r_FineArticle['ViolationTypeId']} AND CityId='{$_SESSION['cityid']}'"));
    $r_VehicleType =            $rs->getArrayLine($rs->Select('VehicleType', "Id={$r_Fine['VehicleTypeId']}"));
    $r_RuleType =               $rs->getArrayLine($rs->Select('V_RuleType', "ViolationTypeId={$r_FineArticle['ViolationTypeId']} AND CityId='{$_SESSION['cityid']}' AND Id={$_SESSION['ruletypeid']}"));
    $r_Trespasser =             $rs->getArrayLine($rs->Select('Trespasser', "Id=$TrespasserId"));
    $r_FineNotification =       $rs->getArrayLine($rs->Select('FineNotification', "FineId=$FineId"));
    $r_FineCommunication =      $rs->getArrayLine($rs->Select('FineCommunication', "FineId=$FineId AND TrespasserId=$TrespasserId"));
    $r_FormDynamic =            $rs->getArrayLine($rs->Select("FormDynamic", "CityId='{$_SESSION['cityid']}' AND FormTypeId=21 AND NationalityId=1"));
    $r_Reason =                 $rs->getArrayLine($rs->Select("Reason", "Id={$r_FineArticle['ReasonId']}"));
    $r_FineOwner =              $rs->getArrayLine($rs->Select("FineOwner", "FineId=$FineId"));
    $r_Signer =                 $rs->getArrayLine($rs->Select("Controller", "Id={$r_Customer['PrefectCommunicationSigner']}"));
    $r_Prefect =                $rs->getArrayLine($rs->Select('V_JudicialOffice', "CityId='{$_SESSION['cityid']}' AND OfficeId=2"));
    $r_Payment =                $rs->getArrayLine($rs->Select("FinePayment", "FineId=$FineId", "Id DESC", "0, 1"));
    
    if($r_FineArticle['DetectorId'] > 0){
        $r_Detector = $rs->getArrayLine($rs->Select('Detector', "Id='{$r_FineArticle['DetectorId']}'"));
    } else $r_Detector = null;
    
    $Content = $r_FormDynamic['Content'];
    
    foreach($a_ProtocolLetters as $a_ProtocolLetter) {
        if($a_ProtocolLetter['Id'] == $r_Fine['Locality']) $Locality = $a_ProtocolLetter['Title'];
        $a_ProtocolLetterLocality[$a_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $a_ProtocolLetter['NationalProtocolLetterType1'];
        $a_ProtocolLetterLocality[$a_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $a_ProtocolLetter['NationalProtocolLetterType2'];
    }
    
    if(trim($r_Article['ArticleLetterAssigned'])!=''){
        $NationalProtocolLetterType1 = $r_Article['ArticleLetterAssigned'];
        $NationalProtocolLetterType2 = $r_Article['ArticleLetterAssigned'];
    } else if(trim($r_ViolationTypeLetter['ViolationLetterAssigned'])!=''){
        $NationalProtocolLetterType1 = $r_ViolationTypeLetter['ViolationLetterAssigned'];
        $NationalProtocolLetterType2 = $r_ViolationTypeLetter['ViolationLetterAssigned'];
    } else {
        $NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType1'];
        $NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType2'];
    }
    
    $str_TrespasserAddress =  trim(
        $r_Trespasser['Address'] ." ".
        $r_Trespasser['StreetNumber'] ." ".
        $r_Trespasser['Ladder'] ." ".
        $r_Trespasser['Indoor'] ." ".
        $r_Trespasser['Plan']
    );
    
    $str_ArticleDescription = "";
    $str_AdditionalSanction = "";
    $Paragraph = ($r_Article['Paragraph'] == "0") ? "" : $r_Article['Paragraph'] . " ";
    $Letter = ($r_Article['Letter'] == "0") ? "" : $r_Article['Letter'];
    $str_ArticleId = $r_Article['Article'] . "/" . $Paragraph . $Letter;
    
    if (isset($r_FineOwner['AdditionalDescriptionIta'])
        && strlen(trim($r_FineOwner['AdditionalDescriptionIta'] ?? '')) > 0){
            $str_AdditionalSanction = StringOutDB($r_FineOwner['AdditionalDescriptionIta']);
    } else {
        $r_AdditionalSanction = $rs->getArrayLine($rs->Select('AdditionalSanction', "Id=" . $r_ArticleTariff['AdditionalSanctionId']));
        $str_AdditionalSanction = StringOutDB($r_AdditionalSanction['TitleIta']);
    }
    
    $str_ProtocolLetter = ($r_RuleType['Id'] == RULETYPE_CDS) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
    $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescriptionIta'] ?? '')) > 0) ? $r_FineOwner['ReasonDescriptionIta'] : ($r_Reason['TitleIta'] ?? '');
    
    $b_isNovice = mgmtPrefectCommunicationIsNovice($r_Trespasser['LicenseDate']);
    /////////////////////////////////////////////
    //SOTTOTESTI
    /////////////////////////////////////////////
    $EmptyPregMatch = false;
    //Continua a cercare per variabili di sottotesti da sostituire finchè non trova nulla
    while(!$EmptyPregMatch){
        $a_Variables = array();
        $a_Matches = array();
        
        if(preg_match_all("/\{\{.*?\}\}/", $Content, $a_Variables) > 0){
            $a_Matches = $a_Variables[0];
            
            foreach ($a_Matches as $var){
                $a_Variables = $rs->getResults($rs->Select("FormVariable", "Id='$var' AND FormTypeId=21 AND NationalityId=1 AND CityId='{$_SESSION['cityid']}'"));
                $a_Variables = array_column($a_Variables, "Content", "Type");
                
                //Rilevatore
                if ($var == "{{DetectorText}}"){
                    if(!empty($r_Detector)){
                        //Velocità
                        if ($r_Detector['DetectorTypeId'] == 1){
                            $Content = str_replace("{{DetectorText}}", $a_Variables[1], $Content);
                        }
                        //Non gestito
                        else {
                            $Content = str_replace("{{DetectorText}}", '', $Content);
                        }
                    } else $Content = str_replace("{{DetectorText}}", '', $Content);
                } 
                //Dettagli com. prefetto
                else if($var == "{{CommunicationSubject}}"){
                    if($r_Result[INDEX_HABITUALFINES] > 0){
                        if($r_Result['Habitual'] > 0){
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[1], $Content);
                        } else if($r_Result['LossHabitual'] > 0) {
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[2], $Content);
                        } else if($r_Result['RevocationHabitual'] > 0) {
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[3], $Content);
                        } else if($r_Result['RevisionHabitual'] > 0) {
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[4], $Content);
                        } else $Content = str_replace("{{CommunicationSubject}}", "", $Content);
                    } else {
                        if($r_Result['SuspensionLicense'] > 0){
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[1], $Content);
                        } else if($r_Result['LossLicense'] > 0) {
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[2], $Content);
                        } else if($r_Result['RevocationLicense'] > 0) {
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[3], $Content);
                        } else if($r_Result['RevisionLicense'] > 0) {
                            $Content = str_replace("{{CommunicationSubject}}", $a_Variables[4], $Content);
                        } else $Content = str_replace("{{CommunicationSubject}}", "", $Content);
                    }
                }
                //Articolo sanzione accessoria/provvedimento amministrativo
                else if($var == "{{AdditionalSanctionArticle}}"){
                    if($r_Result[INDEX_HABITUALFINES] > 0){
                        if($r_Result['Habitual'] > 0){
                            $Content = str_replace("{{AdditionalSanctionArticle}}", $a_Variables[1], $Content);
                        } else $Content = str_replace("{{AdditionalSanctionArticle}}", "", $Content);
                    } else {
                        if($r_Result['SuspensionLicense'] > 0){
                            $Content = str_replace("{{AdditionalSanctionArticle}}", $a_Variables[1], $Content);
                        } else $Content = str_replace("{{AdditionalSanctionArticle}}", "", $Content);
                    }
                }
                //Neopatentato
                else if($var == "{{Novice}}"){
                    if($b_isNovice){
                        $Content = str_replace("{{Novice}}", $a_Variables[1], $Content);
                    } else $Content = str_replace("{{Novice}}", $a_Variables[2], $Content);
                }
                //Recidiva
                else if($var == "{{Habitual}}"){
                    if($r_Result[INDEX_HABITUALFINES] > 0){
                        $Content = str_replace("{{Habitual}}", $a_Variables[1], $Content);
                    } else $Content = str_replace("{{Habitual}}", $a_Variables[2], $Content);
                }
                //Pagamento
                else if($var == "{{Payment}}"){
                    if(!empty($r_Payment)){
                        $Content = str_replace("{{Payment}}", $a_Variables[1], $Content);
                    } else $Content = str_replace("{{Payment}}", "", $Content);
                }
                //Mancata contestazione
                else if($var == "{{ReasonText}}"){
                    if(!empty($str_ReasonDescription)){
                        $Content = str_replace("{{ReasonText}}", $a_Variables[1], $Content);
                    } else $Content = str_replace("{{ReasonText}}", "", $Content);
                }
                //Se non trovo il segnaposto del testo nei risultati presi dalla banca dati, sostituisco con stringa vuota (per non andare in loop)
                else $Content = str_replace($var, "", $Content);
            }
        } else $EmptyPregMatch = true;
    }
    
    //Variabili/////////////////////////////////////////////
    $Content = str_replace("{Date}", date('d/m/Y'), $Content);
    $Content = str_replace("{ProtocolYear}", $r_Fine['ProtocolYear'], $Content);
    $Content = str_replace("{ProtocolLetter}", $str_ProtocolLetter, $Content);
    $Content = str_replace("{ProtocolId}", $r_Fine['ProtocolId'], $Content);
    $Content = str_replace("{Code}", $r_Fine['Code'], $Content);
    $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
    $Content = str_replace("{FineTime}", TimeOutDB($r_Fine['FineTime']), $Content);
    $Content = str_replace("{NotificationDate}", DateOutDB($r_FineNotification['NotificationDate']), $Content);
    $Content = str_replace("{PaymentDate}", DateOutDB($r_Payment['PaymentDate']), $Content);
    $Content = str_replace("{Locality}", $Locality, $Content);
    $Content = str_replace("{Address}", $r_Fine['Address'], $Content);
    $Content = str_replace("{TrespasserName}", StringOutDB((isset($r_Trespasser['CompanyName']) ? $r_Trespasser['CompanyName'].' ' : '') . $r_Trespasser['Surname'] . ' ' . $r_Trespasser['Name']), $Content);
    $Content = str_replace("{TrespasserCity}", StringOutDB($r_Trespasser['City']), $Content);
    $Content = str_replace("{TrespasserProvince}", StringOutDB($r_Trespasser['Province']), $Content);
    $Content = str_replace("{TrespasserAddress}", StringOutDB($str_TrespasserAddress), $Content);
    $Content = str_replace("{TrespasserZip}", StringOutDB($r_Trespasser['ZIP']), $Content);
    $Content = str_replace("{TrespasserBornDate}", DateOutDB($r_Trespasser['BornDate']), $Content);
    $Content = str_replace("{TrespasserBornCity}", StringOutDB($r_Trespasser['BornPlace']), $Content);
    $Content = str_replace("{TrespasserLicenseCategory}", !empty($r_Trespasser['LicenseCategory']) ? StringOutDB("cat. ".$r_Trespasser['LicenseCategory']) : "", $Content);
    $Content = str_replace("{TrespasserLicenseNumber}", !empty($r_Trespasser['LicenseNumber']) ? StringOutDB("n. ".$r_Trespasser['LicenseNumber']) : "", $Content);
    $Content = str_replace("{TrespasserLicenseOffice}", !empty($r_Trespasser['LicenseOffice']) ? StringOutDB("da ".$r_Trespasser['LicenseOffice']) : "", $Content);
    $Content = str_replace("{TrespasserLicenseDate}", !empty($r_Trespasser['LicenseDate']) ? "il ".DateOutDB($r_Trespasser['LicenseDate']) : "", $Content);
    $Content = str_replace("{TrespasserCommunicationDate}", DateOutDB($r_FineCommunication['CommunicationDate']), $Content);
    $Content = str_replace("{VehicleTypeId}", StringOutDB($r_VehicleType['TitleIta']), $Content);
    $Content = str_replace("{VehiclePlate}", StringOutDB($r_Fine['VehiclePlate']), $Content);
    $Content = str_replace("{VehicleBrand}", StringOutDB($r_Fine['VehicleBrand']), $Content);
    $Content = str_replace("{VehicleModel}", StringOutDB($r_Fine['VehicleModel']), $Content);
    $Content = str_replace("{VehicleColor}", StringOutDB($r_Fine['VehicleColor']), $Content);
    $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);
    $Content = str_replace("{ArticleId}", $str_ArticleId , $Content);
    $Content = str_replace("{ArticleDescription}", StringOutDB($str_ArticleDescription), $Content);
    $Content = str_replace("{AdditionalSanctionId}", $str_AdditionalSanction, $Content);
    $Content = str_replace("{SignerName}", $r_Signer['Name'], $Content);
    $Content = str_replace("{SignerCode}", $r_Signer['Code'], $Content);
    $Content = str_replace("{SignerQualification}", $r_Signer['Qualification'], $Content);
    $Content = str_replace("{Prefect}", $r_Prefect['OfficeTitleIta'], $Content);
    $Content = str_replace("{PrefectCity}", $r_Prefect['City'], $Content);
    $Content = str_replace("{PrefectProvince}", $r_Prefect['Province'], $Content);
    $Content = str_replace("{PrefectAddress}", $r_Prefect['Address'], $Content);
    $Content = str_replace("{PrefectZIP}", $r_Prefect['ZIP'], $Content);
    $Content = str_replace("{PrefectPhone}", $r_Prefect['Phone'], $Content);
    $Content = str_replace("{PrefectFax}", $r_Prefect['Fax'], $Content);
    $Content = str_replace("{PrefectMail}", $r_Prefect['Mail'], $Content);
    $Content = str_replace("{PrefectPEC}", $r_Prefect['PEC'], $Content);
    $Content = str_replace("{PrefectWeb}", $r_Prefect['Web'], $Content);
    $Content = str_replace("{ManagerName}", StringOutDB($r_Customer['ManagerName']), $Content);
    $Content = str_replace("{ManagerAdditionalName}", StringOutDB($r_Customer['ManagerAdditionalName']), $Content);
    $Content = str_replace("{ManagerSector}", StringOutDB($r_Customer['ManagerSector']), $Content);
    $Content = str_replace("{ManagerAddress}", StringOutDB($r_Customer['ManagerAddress']), $Content);
    $Content = str_replace("{ManagerZIP}", $r_Customer['ManagerZIP'], $Content);
    $Content = str_replace("{ManagerCity}", StringOutDB($r_Customer['ManagerCity']), $Content);
    $Content = str_replace("{ManagerProvince}", StringOutDB($r_Customer['ManagerProvince']), $Content);
    $Content = str_replace("{ManagerPhone}", $r_Customer['ManagerPhone'], $Content);
    $Content = str_replace("{ManagerFax}", $r_Customer['ManagerFax'], $Content);
    $Content = str_replace("{ManagerMail}", $r_Customer['ManagerMail'], $Content);
    $Content = str_replace("{ManagerPEC}", $r_Customer['ManagerPEC'], $Content);
    $Content = str_replace("{ManagerWeb}", $r_Customer['ManagerWeb'], $Content);
    $Content = str_replace("{ManagerTaxCode}", $r_Customer['ManagerTaxCode'], $Content);
    $Content = str_replace("{ManagerOfficeInfo}", StringOutDB($r_Customer['ManagerOfficeInfo']), $Content);
    $Content = str_replace("{- copia modulo dati del conducente}", $r_Result['FineNotificationType'] != 2 && !(empty($r_FineCommunication))
        ? "- copia modulo dati del conducente"
        : "", $Content);
    $Content = str_replace("{mediante ricevimento di comunicazione scritta}", $r_Result['FineNotificationType'] != 2
        ? "mediante ricevimento di comunicazione scritta"
        : "", $Content);
    $Content = str_replace("{in quanto trattasi di violazione recidiva nel biennio}", $r_Result[INDEX_HABITUALFINES] > 0
        ? "in quanto trattasi di violazione recidiva nel biennio" 
        : "", $Content);
    
    $art218 = $b_isNovice ? "218-bis" : "218";
    $Content = str_replace("{218-218bis}", $art218, $Content);
    
    if($r_Trespasser['Genre'] == 'F'){
        $Content = str_replace("{del-della}", "della", $Content);
        $Content = str_replace("{sig.-sig.ra}", "sig.ra", $Content);
        $Content = str_replace("{nato-nata}", "nata", $Content);
    } else {
        $Content = str_replace("{del-della}", "del", $Content);
        $Content = str_replace("{sig.-sig.ra}", "sig.", $Content);
        $Content = str_replace("{nato-nata}", "nato", $Content);
    }
    
    if(!empty($r_Detector)){
        if ($r_Detector['DetectorTypeId'] == 1){
            $SpeedLengthAverage = $r_Detector['SpeedLengthAverage'];
            $SpeedTimeAverage = $r_FineArticle['SpeedTimeAverage'] > 0 ? $r_FineArticle['SpeedTimeAverage'] : ($SpeedLengthAverage * 3.6) / $r_FineArticle['SpeedControl'];
            $SpeedExcess = intval($r_FineArticle['Speed']) - intval($r_FineArticle['SpeedLimit']);
        } else {
            $SpeedLengthAverage = $SpeedTimeAverage = $SpeedExcess = 0;
        }
        
        $Content = str_replace("{Speed}", intval($r_FineArticle['Speed']), $Content);
        $Content = str_replace("{SpeedControl}", intval($r_FineArticle['SpeedControl']), $Content);
        $Content = str_replace("{SpeedLimit}", intval($r_FineArticle['SpeedLimit']), $Content);
        $Content = str_replace("{SpeedExcess}", intval($SpeedExcess), $Content);
        $Content = str_replace("{DetectorName}", $r_Detector['TitleIta'], $Content);
        $Content = str_replace("{DetectorPosition}", $r_Detector['Position'], $Content);
        $Content = str_replace("{DetectorKind}", $r_Detector['Kind'], $Content);
        $Content = str_replace("{DetectorCode}", $r_Detector['Code'], $Content);
        $Content = str_replace("{DetectorNumber}", $r_Detector['Number'], $Content);
        $Content = str_replace("{DetectorTolerance}", intval($r_Detector['Tolerance']), $Content);
        $Content = str_replace("{DetectorRatification}", $r_Detector['Ratification'], $Content);
        $Content = str_replace("{DetectorAdditionalText}", $r_Detector['AdditionalTextIta'], $Content);
        $Content = str_replace("{DetectorSpeedLengthAverage}", $SpeedLengthAverage, $Content);
        $Content = str_replace("{DetectorSpeedTimeAverage}", number_format($SpeedTimeAverage, 3, ',', '.'), $Content);
        $Content = str_replace("{DetectorSpeedTimeHourAverage}", number_format($SpeedTimeAverage/3600, 3, ',', '.'), $Content);
        
        $r_DetectorRatification = $rs->getArrayLine($rs->Select('DetectorRatification', "DetectorId={$r_FineArticle['DetectorId']} AND ((FromDate <= '{$r_Fine['FineDate']}' AND (ToDate >= '{$r_Fine['FineDate']}' OR ToDate IS NULL)) OR (FromDate IS NULL AND ToDate IS NULL))"));
        if(!empty($r_DetectorRatification)){
            $Content = str_replace("{CalibrationText}", StringOutDB($r_DetectorRatification['Ratification']), $Content);
            $Content = str_replace("{CalibrationFromDate}", DateOutDB($r_DetectorRatification['FromDate']), $Content);
            $Content = str_replace("{CalibrationToDate}", DateOutDB($r_DetectorRatification['ToDate']), $Content);
        }
    }
    ///////////////////////////////////////////////////////
    
    if($r_Result[INDEX_HABITUALFINES] > 0){
        if($r_Result['Habitual'] > 0){
            $pdf->PrintObject1 = "Segnalazione ex art. $art218 del codice della strada - Applicazione della sanzione accessoria della sospensione della patente di guida";
        } else if($r_Result['LossHabitual'] > 0) {
            $pdf->PrintObject1 = "Applicazione della sanzione accessoria del ritiro della patente di guida";
        } else if($r_Result['RevocationHabitual'] > 0) {
            $pdf->PrintObject1 = "Applicazione della sanzione amministrativa della revoca della patente di guida";
        } else if($r_Result['RevisionHabitual'] > 0) {
            $pdf->PrintObject1 = "Applicazione della sanzione amministrativa della revisione della patente di guida";
        } else $pdf->PrintObject1 = "";
        $pdf->PrintObject2 = $r_Result[INDEX_HABITUALFINES] > 0 ? "in quanto trattasi di violazione recidiva nel biennio." : ""; //Riga 2
    } else {
        if($r_Result['SuspensionLicense'] > 0){
            $pdf->PrintObject1 = "Segnalazione ex art. $art218 del codice della strada - Applicazione della sanzione accessoria della sospensione della patente di guida.";
        } else if($r_Result['LossLicense'] > 0) {
            $pdf->PrintObject1 = "Applicazione della sanzione accessoria del ritiro della patente di guida.";
        } else if($r_Result['RevocationLicense'] > 0) {
            $pdf->PrintObject1 = "Applicazione della sanzione amministrativa della revoca della patente di guida.";
        } else if($r_Result['RevisionLicense'] > 0) {
            $pdf->PrintObject1 = "Applicazione della sanzione amministrativa della revisione della patente di guida.";
        } else $pdf->PrintObject1 = "";
    }
    
    $pdf->AddPage('P');
    
    //Se provvisorio evidenzia il testo in giallo
    $pdf->SetFillColor(255, !$ultimate ? 250 : 255, !$ultimate ? 150 : 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Image($_SESSION['blazon'], 7, 7, 12, 17);
    
    $pdf->SetFont('helvetica', '', 8, '', true);
    
    $pdf->writeHTMLCell(73, 0, 22, 5.5, '<strong>' . $r_Customer['ManagerName'] . '</strong>', 0, 0, 1, true, 'L', true);
    $pdf->LN(3);
    $pdf->writeHTMLCell(73, 0, 22, '', buildHeader($r_Customer), 0, 0, 1, true, 'L', true);
    if($r_Customer['ManagerPEC'] != ''){
        $pdf->MultiCell(85, 0, 'PEC: '.$r_Customer['ManagerPEC'], 0, 'L', 1, 1, 10, 30, true);
    }
    
    //Stampa le finestre delle buste
    $window = true;
    if (!$ultimate && $window){
        $pdf->RoundedRect(3.5, 5.3, 89.9, 21.3, 3, '1111', '', array('color' => array(145)), '');
        $pdf->RoundedRect(94.2, 37.6, 110, 44.1, 3, '1111', '', array('color' => array(145)), '');
        $pdf->RoundedRect(10.4, 50.9, 70, 29.9, 3, '1111', '', array('color' => array(145)), '');
        $pdf->RoundedRect(0.1, 0.1, 209.6, 89.7, 0.5, '1111', '', array('color' => array(0,0,255)), '');
        $pdf->SetLineStyle(array('color' => array(0, 0, 0)));
    }
    
    //INTESTAZIONE PREFETTO////////////////////////////////////////////////////////////////////////////////////
    $pdf->SetFont('', '', 10, '', true);
    $pdf->SetCellPadding(0);
    $pdf->MultiCell(100, 0, ucfirst(strtolower($r_Customer['ManagerCity']))." li, ".date('d/m/Y'), 0, 'L', 1, 1, 100, 55.5, true);
    $pdf->MultiCell(100, 0, "", 0, 'L', 1, 1, 100, '', true);
    $pdf->MultiCell(100, 0, strtoupper("All'Ufficio Territoriale del Governo di {$r_Prefect['City']}"), 0, 'L', 1, 1, 100, '', true);
    $pdf->MultiCell(100, 0, mb_strtoupper("{$r_Prefect['Address']}", "UTF-8"), 0, 'L', 1, 1, 100, '', true);
    $pdf->MultiCell(100, 0, strtoupper("{$r_Prefect['ZIP']} {$r_Prefect['City']} - {$r_Prefect['Province']}"), 0, 'L', 1, 1, 100, '', true);
    $pdf->SetFont('', '', 8, '', true);
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    $pdf->SetFont('helvetica', '', 10, '', true);
    
    //IMPOSTA COORDINATA INIZIO TESTO DINAMICO
    $pdf->SetXY($TextStartCoord['X'], $TextStartCoord['Y']);
    
    $pdf->writeHTML($Content, true, $ultimate ? false : true, true, false, '');
    
    //testo STAMPA PROVVISORIA
    if(!$ultimate){
        $TotalPages = $pdf->PageNo();
        for ($i=1; $i<=$TotalPages; $i++){
            $pdf->setPage($i, true);
            $pdf->SetXY($pdf->pixelsToUnits(80), $pdf->pixelsToUnits(675));
            $pdf->StartTransform();
            $pdf->Rotate(50);
            $pdf->SetFont('helvetica', '', 22);
            $pdf->SetTextColor(190);
            $pdf->Cell($pdf->pixelsToUnits(650),0,'S   T   A   M   P   A         P   R   O   V   V   I   S   O   R   I   A',0,1,'C',0,'');
            $pdf->StopTransform();
        }
    }
    
    try{
        $pdf->Output($str_DocumentFolder . '/' . $Documentation, "F");
        
        if($ultimate){
            $a_FineDocumentation = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $Documentation),
                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 18),
                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
            );
            
            $rs->Start_Transaction();
            $rs->InsertOrUpdateIfExist('FineDocumentation',$a_FineDocumentation, "FineId=$FineId AND DocumentationTypeId=18");
            $rs->End_Transaction();
        }
        
        $_SESSION['Documentation'] = $str_DocumentFolderHTML .'/'. $Documentation;
    } catch(Exception $e){
        trigger_error('ID '.$FineId.': Errore nella stampa della comunicazione al prefetto: '.$e, E_USER_WARNING);
        $str_Error .= 'Errore nella stampa della comunicazione al prefetto.<br>';
    }
    
} else if($Action == MGMT_PREFECTCOMMUNICATION_ACTION_DELETEDOC){
    
    $r_PrefectCommunication = $rs->getArrayLine($rs->Select("FinePrefectCommunication", "FineId=$FineId"));
    
    if(!$r_PrefectCommunication){
        $r_Fine = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"));
        $r_FineDocumentation = $rs->getArrayLine($rs->Select("FineDocumentation", "FineId=$FineId AND DocumentationTypeId=18"));
        
        if($r_FineDocumentation){
            $str_DocumentFolder = ($r_Fine['CountryId']=='Z000' ? NATIONAL_FINE : FOREIGN_FINE).'/'.$_SESSION['cityid']."/$FineId";
            
            if(unlink($str_DocumentFolder.'/'.$r_FineDocumentation['Documentation'])){
                $rs->Start_Transaction();
                $rs->Delete("FineDocumentation", "FineId=$FineId AND DocumentationTypeId=18");
                $rs->End_Transaction();
            } else $str_Error .= 'Errore nell\'eliminazione della comunicazione al prefetto.<br>';
        } else $str_Error .= 'Documento della comunicazione non registrato sulla banca dati.<br>';
    } else $str_Error .= 'Non è possibile eliminare la comunicazione al prefetto se è già stata trasmessa.<br>';
    
} else if($Action == MGMT_PREFECTCOMMUNICATION_ACTION_UPDATE){
    $SendType = CheckValue('SendType','n');
    $LetterNumber = $SendType == 1 ? CheckValue('LetterNumber','s') : '';
    $ReceiptNumber = $SendType == 1 ? CheckValue('ReceiptNumber','s') : '';
    
    $a_PrefectCommunication = array(
        array('field'=>'FineId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'SendDate','selector'=>'field','type'=>'date'),
        array('field'=>'NotificationDate','selector'=>'field','type'=>'date'),
        array('field'=>'ReceiptNumber','selector'=>'value','type'=>'str','value'=>$ReceiptNumber),
        array('field'=>'LetterNumber','selector'=>'value','type'=>'str','value'=>$LetterNumber),
        array('field'=>'SendType','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'ResultId','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    );
    
    $rs->Start_Transaction();
    $rs->InsertOrUpdateIfExist('FinePrefectCommunication',$a_PrefectCommunication, "FineId=$FineId");
    $rs->End_Transaction();
    
    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
}

if ($str_Error != ''){
    $_SESSION['Message']['Error'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Error.'</div>';
} else {
    if($ultimate || $Action != MGMT_PREFECTCOMMUNICATION_ACTION_CREATEDOC){
        $_SESSION['Message']['Success'] = "Azione eseguita con successo";
    }
}

header("location: ".impostaParametriUrl(array("FineId" => $FineId, "P" => "mgmt_prefectcommunication.php"), "mgmt_prefectcommunication_upd.php".$Filters));
