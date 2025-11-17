<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
include(CLS."/cls_progressbar.php");
include(CLS."/cls_elaboration.php");
require_once(CLS."/cls_dispute.php");
require_once(CLS."/cls_view.php");
include_once TCPDF . "/tcpdf.php";

$str_UserId = $_SESSION['username'];
$CityId = $_SESSION['cityid'];

$FinalElaboration               = (int)CheckValue('FinalElaboration','n');
$n_fileType                     = (int)CheckValue('fileType','n');

$CurrentDate                    = DateInDB(CheckValue('Search_Date','s'));
$CurrentTime                    = CheckValue('Search_Time','s');
$CurrentYear                    = substr($CurrentDate,0,4);
$ElaborationType                = CheckValue('ElaborationType','n');
$Search_FromNotificationDate    = CheckValue('Search_FromNotificationDate','s');
$Search_ToNotificationDate      = CheckValue('Search_ToNotificationDate','s');
$n_ControllerId                 = CheckValue('ControllerId','n');
$Search_FromFineDate            = CheckValue('Search_FromFineDate','s');
$Search_ToFineDate              = CheckValue('Search_ToFineDate','s');
$Search_FromProtocolId          = CheckValue('Search_FromProtocolId','n');
$Search_ToProtocolId            = CheckValue('Search_ToProtocolId','n');
$s_TypePlate                    = CheckValue('TypePlate','s');
$Search_Dispute                 = CheckValue('Search_Dispute','s');
$Search_Outcome                 = CheckValue('Search_Outcome','s');

$strFilters['Year'] = "ANNO - ";
if($Search_Year!=""){
    $str_Where .= " AND ProtocolYear=".$Search_Year;
    $strFilters['Year'].= $Search_Year;
}
else
    unset($strFilters['Year']);
$strFilters['ProtocolId'] = "CRONOLOGICO - ";
if(!$Search_FromProtocolId>0 && !$Search_ToProtocolId>0)
    unset($strFilters['ProtocolId']);
if($Search_FromProtocolId>0){
    $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
    $strFilters['ProtocolId'].= "Dal ".$Search_FromProtocolId." ";
}
if($Search_ToProtocolId>0){
    $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
    $strFilters['ProtocolId'].= "al ".$Search_ToProtocolId;
}

$strFilters['FineDate'] = "DATA VERBALE - ";
if($Search_FromFineDate=="" && $Search_ToFineDate=="")
    unset($strFilters['FineDate']);
if($Search_FromFineDate != ""){
    $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
    $strFilters['FineDate'].= "Dal ".$Search_FromFineDate." ";
}
if($Search_ToFineDate != ""){
    $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
    $strFilters['FineDate'].= "al ".$Search_ToFineDate;
}
$strFilters['NotificationDate'] = "DATA NOTIFICA - ";
if($Search_FromNotificationDate=="" && $Search_ToNotificationDate=="")
    unset($strFilters['NotificationDate']);


if($Search_FromNotificationDate != ""){
    $strFilters['NotificationDate'].= "Dal ".$Search_FromNotificationDate." ";
}
if($Search_FromNotificationDate=="" || DateInDB($Search_FromNotificationDate) <= DateInDB("30/03/2021")){
    $Search_FromNotificationDate = "30/03/2021";
}
$str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";

if($Search_ToNotificationDate != ""){
    $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";
    $strFilters['NotificationDate'].= "al ".$Search_ToNotificationDate;
}

if($Search_Dispute != ""){
    $strFilters['Dispute']= "RICORSI - ";
    if($Search_Dispute=="without")
        $strFilters['Dispute'].= "Posizioni senza ricorsi";
    else if($Search_Dispute=="with")
        $strFilters['Dispute'].= "Posizioni con ricorsi";
}
if($Search_Outcome != ""){
    $strFilters['Outcome']= "ESITO - ";
    if($Search_Outcome=="positive")
        $strFilters['Outcome'].= "Posizioni con esito positivo";
    else if($Search_Outcome=="negative")
        $strFilters['Outcome'].= "Posizioni con esito negativo";
}

$r_ProcessingNational = $rs->getArrayLine($rs->Select('ProcessingData126BisNational', "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));
$r_ProcessingForeign = $rs->getArrayLine($rs->Select('ProcessingData126BisForeign', "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));

switch($s_TypePlate){
    case "N":
        $r_ProcessingData = $r_ProcessingNational;
        $str_WhereCountry = " AND CountryId='Z000'";
        break;
    case "F":
        $r_ProcessingData = $r_ProcessingForeign;
        $str_WhereCountry = " AND CountryId!='Z000'";
        break;
    default:
        $r_ProcessingData = null;
        $str_WhereCountry = "";
        break;
}

$rs_Article = $rs->Select('V_Article', "Article=126 AND Paragraph='0' AND Letter='bis' AND CityId='".$CityId."' AND Year=".$CurrentYear);
if(!mysqli_num_rows($rs_Article)>0){
    $_SESSION['Message']['Error'] = "Articolo 126Bis non trovato per l'anno ".$CurrentYear;
    header("location: ".impostaParametriUrl(array('btn_search' => 1, 'ElaborationType' => $ElaborationType, 'ControllerId' => $n_ControllerId), 'prc_126Bis.php'.$str_GET_Parameter));
    DIE;
}


$r_Article = mysqli_fetch_array($rs_Article);
$rs_Reason = $rs->Select('Reason', "ViolationTypeId=".$r_Article['ViolationTypeId']." AND CityId='".$CityId."'");
$r_Reason = mysqli_fetch_array($rs_Reason);
$ReasonId = $r_Reason['Id'];

$rs_FineProcedure = $rs->Select('V_126BisProcedure',$str_Where.$str_WhereCountry." AND CityId='".$CityId."'");
$totalRows = mysqli_num_rows($rs_FineProcedure);

$cls_dispute = new cls_dispute();
$cls_126Bis = new cls_elaboration("126Bis");


$countNegative = 0;
$countPositive = 0;

if($FinalElaboration==1){
    $rs->Start_Transaction();
    
    $cont=1;
    $str_Message = "";
    $ProgressFileName = CheckValue("ProgressFile", "s");
    $ProgressFile = TMP . "/".$ProgressFileName;
    $progress = new CLS_PROGRESSBAR($totalRows);
}
else{
    $rs_Customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
    $r_Customer = mysqli_fetch_array($rs_Customer);

    $ManagerName = $r_Customer['ManagerName'];
    $ManagerAddress = $r_Customer['ManagerAddress'];
    $ManagerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
    $ManagerPhone = $r_Customer['ManagerPhone'];

    $n_ContRow = 1;
    if($n_fileType==1){
        $filename="export.xls";
        header ("Content-Type: application/vnd.ms-excel");
        header ("Content-Disposition: inline; filename=$filename");
        $str_Csv = '
            <table border="1">
	    ';

        foreach ($strFilters as $strFilter){
            $str_Csv.= '
                <tr>
                    <td>'.$strFilter.'</td>
                </tr>';
        }
        $str_Csv.= '<tr>
                        <td></td>
                    </tr>	        
                    <tr>
                        <td>VERB ORIG</td>
                        <td>REF</td>
                        <td>DATA NOTIFICA</td>
                        <td>TARGA</td>
                        <td>PROPRIETARIO</td>
                        <td>ESITO</td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>';

    }
    else {

//        echo "PDF";

        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

        $pdf->setPrintHeader(false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Request');
        $pdf->SetSubject('Request');
        $pdf->SetKeywords('');


        $pdf->SetMargins(10, 15, 10);
        $pdf->AddPage();
        $pdf->SetFont('arial', '', 10, '', true);

        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);

        $yStart = $pdf->GetY() - 5;
        $pdf->SetY($yStart);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerName, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

        $pdf->LN(15);
        $yEnd = $pdf->GetY();

        $pdf->SetY($yStart);
        foreach ($strFilters as $strFilter){
            $pdf->writeHTMLCell(150, 0, 110, '', $strFilter, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
        }
        $pdf->SetY($yEnd);
        $pdf->SetFont('arial', '', 6, '', true);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(20, 4, 10, $y, "VERB ORIG", 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 30, $y, "REF", 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 60, $y, "DATA NOTIFICA", 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(15, 4, 85, $y, "TARGA", 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(40, 4, 100, $y, "PROPRIETARIO", 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(60, 4, 140, $y, "ESITO", 1, 0, 1, true, 'L', true);
        $pdf->LN(6);
    }
}


while($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)){
    if($s_TypePlate=="" || $r_ProcessingData==null){
        if($r_FineProcedure['CountryId']!="Z000")
            $r_ProcessingData = $r_ProcessingForeign;
        else
            $r_ProcessingData = $r_ProcessingNational;
    }

    if($FinalElaboration==1){
        //progress update
        $progress->writeJSON($cont, $ProgressFile);
        $cont++;
    }
    else {

        $a_trespasser = array(1=>null,2=>null,11=>null,15=>null);
        $rs_FineTrespasser = $rs->SelectQuery('SELECT TrespasserTypeId, TrespasserId, CompanyName, Surname, Name FROM V_FineTrespasser WHERE FineId='.$r_FineProcedure['Id']);
        while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)) {
            $a_trespasser[$r_FineTrespasser['TrespasserTypeId']] = $r_FineTrespasser['CompanyName'].$r_FineTrespasser['Surname']." ".$r_FineTrespasser['Name'];
        }

        $trespasser = "";
        if($a_trespasser[1]!="")
            $trespasser = $a_trespasser[1];
        else if($a_trespasser[2]!="")
            $trespasser = $a_trespasser[2];
        else if($a_trespasser[11]!="")
            $trespasser = $a_trespasser[11];
        else if($a_trespasser[15]!="")
            $trespasser = $a_trespasser[15];

        $rs_Fine = $rs->Select('Fine', "Id=" . $r_FineProcedure['Id']);
        $r_Fine = mysqli_fetch_array($rs_Fine);
        $a_Fine126Bis = $rs->getArrayLine($rs->ExecuteQuery(
            'SELECT Id, ProtocolYear, ProtocolId, StatusTypeId FROM `V_FineArticle` 
            WHERE PreviousId='.$r_FineProcedure['Id'].' AND Article=126 AND Letter="bis" ORDER BY Id DESC')
        );
        $str_126bisExist = "";
        if($a_Fine126Bis!=null){
            $str_filed = "";
            if($a_Fine126Bis['StatusTypeId']==35)
                $str_filed = "archiviato ";
            $str_126bisExist = "126Bis ".$str_filed.$a_Fine126Bis['ProtocolId']."/".$a_Fine126Bis['ProtocolYear'];
        }

        $codeStr = $r_Fine['Code'];
        if(strlen($r_Fine['Code'])>18)
            $codeStr = substr($r_Fine['Code'],0,18)."...";
        $strTrespasser = $trespasser;
        if(strlen($a_trespasser[1])>28)
            $strTrespasser = substr($trespasser,0,28)."...";

        if ($n_ContRow == 26 && $n_fileType==0) {
            $pdf->AddPage();
            $pdf->SetFont('arial', '', 10, '', true);

            $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);

            $yStart = $pdf->GetY() - 5;
            $pdf->SetY($yStart);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerName, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

            $pdf->LN(15);
            $yEnd = $pdf->GetY();

            $pdf->SetY($yStart);
            foreach ($strFilters as $strFilter){
                $pdf->writeHTMLCell(150, 0, 110, '', $strFilter, 0, 0, 1, true, 'L', true);
                $pdf->LN(4);
            }
            $pdf->SetY($yEnd);

            $n_ContRow = 1;

            $pdf->SetFont('arial', '', 6, '', true);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(20, 4, 10, $y, "VERB ORIG", 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(30, 4, 30, $y, "REF", 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(25, 4, 60, $y, "DATA NOTIFICA", 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(15, 4, 85, $y, "TARGA", 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(40, 4, 100, $y, "PROPRIETARIO", 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(60, 4, 140, $y, "ESITO", 1, 0, 1, true, 'L', true);
            $pdf->LN(6);
        }

    }

    $str_Output = "";
    $params = array_merge(
        array(
            "NotificationDate" => $r_FineProcedure['NotificationDate'],
            "CommunicationDate" => $r_FineProcedure['CommunicationDate'],
            "IncompletedCommunicationFlag" => $r_FineProcedure['IncompletedCommunication'],
            "CurrentDate" => $CurrentDate,
            "DisputeDays" => 0,
            "DisputeCheck" => true,
            "DisputeMsg" => null
        ),
        $r_ProcessingData
    );

    $rs_Fine = $rs->Select('Fine', "Id=".$r_FineProcedure['Id']);
    $r_Fine = mysqli_fetch_array($rs_Fine);

    $disputeView = new CLS_VIEW(MGMT_DISPUTE);
    $rs_FineDispute= $rs->selectQuery($disputeView->generateSelect("F.Id=".$r_FineProcedure['Id'],null, "GradeTypeId DESC",1));
    if(mysqli_num_rows($rs_FineDispute)>0){
        $RG = "";
        $dateHearing = "";
        if($Search_Dispute=="without")
            continue;
        else
            $r_FineDispute = mysqli_fetch_array($rs_FineDispute);

        $cls_dispute->setDispute($r_FineDispute, $params['DisputeCheckType']);

        $params['DisputeDays'] = $cls_dispute->a_info['days'];
        $params['DisputeCheck'] = $cls_dispute->a_info['check'];
        $params['DisputeMsg'] = $cls_dispute->a_info['msg'];

        if($FinalElaboration==1 && $r_FineDispute['DisputeStatusId']==3){
            $a_FineNotification = array(
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
            );
            $rs->Update('FineNotification',$a_FineNotification,"FineId=".$cls_dispute->a_dispute['FineId']);
        }

    }
    else{
        if($Search_Dispute=="with")
            continue;
        $cls_dispute->resetInfo();
    }

    if($params['DisputeCheck']===false){
        if($Search_Outcome=="positive")
            continue;

        $countNegative++;
    }

    $cls_126Bis->checkMissingData($params);
    if($cls_126Bis->a_missingData['check']){
        $cls_126Bis->checkDaysLimitation();
        if($cls_126Bis->a_daysLimitation['check']){
            if($Search_Outcome=="negative")
                continue;

            $countPositive++;

            if($FinalElaboration==1){
                $ControllerId = ($n_ControllerId>0) ? $n_ControllerId : $r_Fine['ControllerId'];

                if($ElaborationType){
                    $StatusTypeId = 14;
                    $FineTypeId = 3;

                    $rs_Protocol = $rs->SelectQuery("SELECT IFNULL(MAX(ProtocolId)+1, 1) ProtocolId, IFNULL(MAX(ProtocolIdAssigned)+1, 1) ProtocolIdAssigned FROM Fine WHERE CityId='" . $r_Fine['CityId'] . "' AND ProtocolYear=" . $CurrentYear);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $ProtocolId = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];
                }
                else
                {
                    $StatusTypeId = 10;
                    $FineTypeId = 1;
                    $ProtocolId = 0;
                }

                $a_Fine = array(
                    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$r_FineProcedure['Id']."BIS/".$r_Fine['ProtocolYear']),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Fine['CityId']),
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
                    array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$CurrentYear,'settype'=>'int'),
                    array('field'=>'FineTypeId','selector'=>'value','type'=>'int','value'=>$FineTypeId,'settype'=>'int'),
                    array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolId,'settype'=>'int'),
                    array('field'=>'FineDate','selector'=>'value','type'=>'str','value'=>$CurrentDate),
                    array('field'=>'FineTime','selector'=>'value','type'=>'str','value'=>$CurrentTime),
                    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
                    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$r_Fine['Locality']),
                    array('field'=>'StreetTypeId','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$r_ProcessingData['Address']),
                    array('field'=>'VehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['VehicleTypeId'],'settype'=>'int'),
                    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$r_Fine['VehiclePlate']),
                    array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleCountry']),
                    array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$r_Fine['CountryId']),
                    array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$r_Fine['DepartmentId'],'settype'=>'int'),
                    array('field'=>'VehicleBrand','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleBrand']),
                    array('field'=>'VehicleModel','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleModel']),
                    array('field'=>'VehicleColor','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleColor']),
                    array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$r_Fine['VehicleMass'],'settype'=>'flt'),
                    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$str_UserId),
                    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Creazione automatica 126 BIS'),
                    array('field'=>'PreviousId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['Id'],'settype'=>'int'),
                );
                $FineId = $rs->Insert('Fine',$a_Fine);

                $str_Output.= " ID ".$FineId." CRONO ".$ProtocolId."/".$CurrentYear;
                $str_Output.= " - REF. ".$r_FineProcedure['Id']."BIS/".$r_Fine['ProtocolYear']." - ";

                $a_FineArticle = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$r_Article['Id'],'settype'=>'int'),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Fine['CityId']),
                    array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$r_Article['ViolationTypeId'],'settype'=>'int'),
                    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_Article['Fee'],'settype'=>'flt'),
                    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_Article['MaxFee'],'settype'=>'flt'),

                );
                $rs->Insert('FineArticle',$a_FineArticle);

                $rs_Trespasser = $rs->Select('FineTrespasser', "FineId=".$r_FineProcedure['Id']." AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
                $r_Trespasser = mysqli_fetch_array($rs_Trespasser);

                $TrespasserTypeId = 1;

                $a_FineTrespasser = array(
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Trespasser['TrespasserId'],'settype'=>'int'),
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId,'settype'=>'int'),
                    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Contravventore già esistente 126 BIS'),
                );

                $rs->Insert('FineTrespasser',$a_FineTrespasser);

                $a_FineNotification = array(
                    array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                );

                $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);
            }
        }
        else{
            if($Search_Outcome=="positive")
                continue;

            $countNegative++;

            if($cls_126Bis->a_daysLimitation['terms']>0 && $FinalElaboration==1){
                $a_FineNotification = array(
                    array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                );
                $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);
            }
        }

    }
    else{
        //comunicazione dati corretta
        if($Search_Outcome=="positive")
            continue;

        $countNegative++;
        if($FinalElaboration==1){
            $a_FineNotification = array(
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
            );
            $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);
        }
    }

    $cls_126Bis->get126BisMsg();

    if($FinalElaboration==1){
        $str_Output.= "VERBALE ORIGINALE ".$r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']." - ".$cls_126Bis->a_elaborate['msg'];
        $str_Message .= '
        <div class="col-sm-12">
            ' . $str_Output . '
            <div class="clean_row HSpace4"></div>
        </div>
    ';
    }
    else{
        if($str_126bisExist!="")
            $str_Result = $str_126bisExist."\n".$cls_126Bis->a_elaborate['msg'];
        else
            $str_Result = $cls_126Bis->a_elaborate['msg'];
        if($n_fileType==0){
            $pdf->MultiCell(20, 9, $r_Fine['ProtocolId'] . ' / ' . $r_Fine['ProtocolYear'], 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(30, 9, 'Ref: ' . $codeStr, 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(25, 9, DateOutDB($r_FineProcedure['NotificationDate']), 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(15, 9, $r_Fine['VehiclePlate'], 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(40, 9, $strTrespasser, 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(60, 9, $str_Result, 1, 'L', false, 1, '', '', true, 0, false, true, 9, 'M');
        }
        else{

            $str_Csv.= '
                    <tr>
                        <td>'.$r_Fine['ProtocolId'] . ' / ' . $r_Fine['ProtocolYear'].'</td>
                        <td>'.'Ref: ' . $codeStr.'</td>
                        <td>'.DateOutDB($r_FineProcedure['NotificationDate']).'</td>
                        <td>'.$r_Fine['VehiclePlate'].'</td>
                        <td>'.$strTrespasser.'</td>
                        <td>'.$str_Result.'</td>
                    </tr>
                ';


        }

        $n_ContRow++;
    }
}

if($FinalElaboration==1){

    if($countPositive>0){
        $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
        while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

            $str_Content = $r_UserMail['CityTitle'].": sono stati elaborati n. ".$n_ContFine." verbali";

            $a_Mail = array(
                array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Elaborazione 126 Bis"),
                array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
                array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
                array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
            );
            $rs->Insert('Mail',$a_Mail);
        }
    }
    
    $rs->End_Transaction();

    echo json_encode(
        array(
            "Esito" => 1,
            "Messaggio" => trim($str_Message),
        )
    );

}
else{
    if($n_fileType==0) {
        $FileName = 'export.pdf';
        $pdf->Output(ROOT . '/doc/print/' . $FileName, "F");
        $_SESSION['Documentation'] = $MainPath . '/doc/print/' . $FileName;
        header("location: ".impostaParametriUrl(array('btn_search' => 1, 'ElaborationType' => $ElaborationType, 'ControllerId' => $n_ControllerId), 'prc_126Bis.php'.$str_GET_Parameter));
    }
    else{
        $str_Csv .= '</table>';
        echo $str_Csv;
    }

}




