<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";

$n_fileType = CheckValue('fileType','n');

$CurrentDate = DateInDB(CheckValue('ElaborationDate','s'));
$CurrentTime = CheckValue('ElaborationTime','s');
$ElaborationType = CheckValue('ElaborationType','n');
$CurrentYear = substr($CurrentDate,0,4);


$Search_FromNotificationDate = CheckValue('Search_FromNotificationDate','s');
$Search_ToNotificationDate = CheckValue('Search_ToNotificationDate','s');

$Search_FromFineDate            = CheckValue('Search_FromFineDate','s');
$Search_ToFineDate              = CheckValue('Search_ToFineDate','s');
$Search_FromProtocolId          = CheckValue('Search_FromProtocolId','n');
$Search_ToProtocolId            = CheckValue('Search_ToProtocolId','n');
$s_TypePlate                    = CheckValue('TypePlate','s');
$Search_Dispute = CheckValue('Search_Dispute','s');
$Search_Outcome = CheckValue('Search_Outcome','s');

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

$str_UserId = $_SESSION['username'];

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
    $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
    $strFilters['NotificationDate'].= "Dal ".$Search_FromNotificationDate." ";
}
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
    $strFilters['Outcome']= "ESITO - Posizioni con esito positivo";
}

$CityId = $_SESSION['cityid'];

$ProcessingDate = date("Y-m-d");
$ProcessingStartTime = date("H:i:s");

$rs_Article = $rs->Select('V_Article', "Article=126 AND Paragraph='0' AND Letter='bis' AND CityId='".$CityId."' AND Year=".$CurrentYear);


$str_get = "btn_search=1&TypePlate=".$s_TypePlate."&Search_FromFineDate=".$Search_FromFineDate
    ."&Search_ToFineDate=".$Search_ToFineDate."&Search_FromNotificationDate=".$Search_FromNotificationDate
    ."&Search_ToNotificationDate=".$Search_ToNotificationDate."&Search_FromProtocolId=".$Search_FromProtocolId
    ."&Search_ToProtocolId=".$Search_ToProtocolId."&Search_Year=".$Search_Year."&ElaborationDate=".DateOutDB($CurrentDate)
    ."&ElaborationTime=".$CurrentTime."&ElaborationType=".$ElaborationType."&Search_Dispute=".$Search_Dispute."&Search_Outcome=".$Search_Outcome;
if(mysqli_num_rows($rs_Article)>0) {

    $rs_FineProcedure = $rs->Select('V_126BisProcedure', $str_Where . $str_WhereCountry . " AND CityId='" . $CityId . "'", "NotificationDate");

    $n_ContFine = 0;
    $n_ContFineOutDate = 0;
    $n_ContFineDispute = 0;
    $str_FineOutDate = "";
    $str_Content = "";


    $rs_Customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
    $r_Customer = mysqli_fetch_array($rs_Customer);

    $MangerName = $r_Customer['ManagerName'];
    $ManagerAddress = $r_Customer['ManagerAddress'];
    $ManagerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
    $ManagerPhone = $r_Customer['ManagerPhone'];


    $rs_Result = $rs->Select('Result', "1=1");
    while ($r_Result = mysqli_fetch_array($rs_Result)) {
        $a_Result[$r_Result['Id']] = $r_Result['Title'];
    }
    $a_GradeType = array("", "I", "II", "III");

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

    }else{
        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($_SESSION['citytitle']);
        $pdf->SetTitle('Request');
        $pdf->SetSubject('Request');
        $pdf->SetKeywords('');


        $pdf->SetMargins(10, 10, 10);
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
        $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
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







    while ($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)) {
        if($s_TypePlate=="" || $r_ProcessingData==null){
            if($r_FineProcedure['CountryId']!="Z000")
                $r_ProcessingData = $r_ProcessingForeign;
            else
                $r_ProcessingData = $r_ProcessingNational;
        }

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


        $str_Result = '';

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
            $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
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

        $b_FineDispute = false;
        $n_DisputeDay = 0;
        $str_Dispute = "";

        $NotificationDate = $r_FineProcedure['NotificationDate'];
        $CommunicationDate = $r_FineProcedure['CommunicationDate'];

        $codeStr = $r_Fine['Code'];
        if(strlen($r_Fine['Code'])>18)
            $codeStr = substr($r_Fine['Code'],0,18)."...";
        $strTrespasser = $trespasser;
        if(strlen($a_trespasser[1])>28)
            $strTrespasser = substr($trespasser,0,28)."...";

        $rs_FineDispute = $rs->Select('V_FineDispute', "FineId=" . $r_FineProcedure['Id']);
        if (mysqli_num_rows($rs_FineDispute) > 0 ) {
            if($Search_Dispute=="without")
                continue;

            $r_FineDispute = mysqli_fetch_array($rs_FineDispute);

            $RG = "";
            $dateHearing = "";
            if($r_FineDispute['Number']!=null)
                $RG = "RG ".$r_FineDispute['Number'];
            if($r_FineDispute['TypeHearing']!=null)
                $dateHearing = $r_FineDispute['TypeHearing'];
            if($r_FineDispute['DateHearing']!=null){
                if($dateHearing=="")
                    $dateHearing = "UDIENZA";
                $dateHearing.= " ".DateOutDB($r_FineDispute['DateHearing']);
            }
            $addToDispute = "";
            if($RG!="" && $dateHearing!="")
                $addToDispute = "\n".$RG." ".$dateHearing;
            else if($RG!="" || $dateHearing!="")
                $addToDispute = "\n".$RG.$dateHearing;

            if($r_ProcessingData['DisputeCheckType']==0){
                switch ($r_FineDispute['DisputeStatusId']){
                    case 1:
                        $str_Result = "Negativo: Ricorso in attesa".$addToDispute;
                        $b_FineDispute = true;
                        break;
                    case 2:
                        $str_Dispute = " - Ricorso respinto o inammissibile".$addToDispute;
                        if($r_FineDispute['DateFile']!=null && $r_FineDispute['DateMerit']!=null)
                            $n_DisputeDay = DateDiff("D", $r_FineDispute['DateFile'], $r_FineDispute['DateMerit'])+1;
                        break;
                    case 3:
                        $str_Result = "Negativo: Ricorso accolto".$addToDispute;
                        $b_FineDispute = true;

                        break;
                }
            }
            else if($r_ProcessingData['DisputeCheckType']==1){
                //TODO Data provvedimento sospensiva da aggiungere nei ricorsi per controllo
            }

        }
        else{
            if($Search_Dispute=="with")
                continue;
        }

        if ($b_FineDispute) {
            if($Search_Outcome=="positive")
                continue;
            $n_ContFineDispute++;
        }
        else if ($CommunicationDate == "") {

            $n_Day = DateDiff("D", $NotificationDate, $CurrentDate) + 1;
            $covidDays = 0;
            $covidDateStart = "2020-02-23";
            $covidDateStop = "2020-04-15";
            $limitDate = date('Y-m-d', strtotime($NotificationDate. ' + '.($r_ProcessingData['RangeDayMax'] + $n_DisputeDay).' days'));
            if($NotificationDate<=$covidDateStop && $limitDate>=$covidDateStart){
                if($NotificationDate>=$covidDateStart)
                    $startDate = $NotificationDate;
                else
                    $startDate = $covidDateStart;
                if($limitDate<=$covidDateStop)
                    $endDate = $limitDate;
                else
                    $endDate = $covidDateStop;
                $covidDays = (int)DateDiff("D", $startDate, $endDate) + 1;
            }

            $n_CalcDay = $n_Day - $r_ProcessingData['RangeDayMin'];
            $RangeDayMax = $r_ProcessingData['RangeDayMax'] + $n_DisputeDay + $covidDays;

            if ($n_CalcDay > $r_ProcessingData['WaitDay'] && $n_CalcDay <= $RangeDayMax) {
                if($Search_Outcome=="negative")
                    continue;

                $str_ok = "Limite ".($RangeDayMax+$r_ProcessingData['RangeDayMin']);
                if($n_DisputeDay>0 || $covidDays>0)
                    $str_ok.= " ( ".($r_ProcessingData['RangeDayMax']+$r_ProcessingData['RangeDayMin']);
                if($n_DisputeDay>0)
                    $str_ok.= " + ricorso ".$n_DisputeDay;
                if($covidDays>0)
                    $str_ok.= " + covid ".$covidDays;
                if($n_DisputeDay>0 || $covidDays>0)
                    $str_ok.= " )";

                if($_SESSION['cityid']=="U480")
                    $str_extra = "";
                else
                    $str_extra = "\nTrascorsi ".$n_Day." - ".$str_ok;
                $str_Result = "Positivo ".$str_Dispute.$str_extra;
            } else {
                if($Search_Outcome=="positive")
                    continue;
                if($n_CalcDay > $RangeDayMax){
                    $str_max = "Limite ".($RangeDayMax+$r_ProcessingData['RangeDayMin']);
                    if($n_DisputeDay>0 || $covidDays>0)
                        $str_max.= " ( ".($r_ProcessingData['RangeDayMax']+$r_ProcessingData['RangeDayMin']);
                    if($n_DisputeDay>0)
                        $str_max.= " + ricorso ".$n_DisputeDay;
                    if($covidDays>0)
                        $str_max.= " + covid ".$covidDays;
                    if($n_DisputeDay>0 || $covidDays>0)
                        $str_max.= " )";
                    if($_SESSION['cityid']=="U480")
                        $str_extra = "";
                    else
                        $str_extra = "\nTrascorsi ".$n_Day." - ".$str_max;
                    $str_Result = "Negativo: Scadenza termini in giorni".$str_extra;
                }

                else{
                    $str_min = "Limite Min ".($r_ProcessingData['RangeDayMin']+$r_ProcessingData['WaitDay']);
                    if($r_ProcessingData['WaitDay']>0)
                        $str_min.= " ( Minimo ".$r_ProcessingData['RangeDayMin']. " + Attesa ".$r_ProcessingData['WaitDay'].")";
                    if($n_Day<0)
                        $n_Day = 0;
                    if($_SESSION['cityid']=="U480")
                        $str_extra = "";
                    else
                        $str_extra = "\nTrascorsi ".$n_Day." - ".$str_min;
                    $str_Result = "Negativo: Anticipo termini in giorni".$str_extra;
                }
            }
        }
        else {
            if($Search_Outcome=="positive")
                continue;
            $str_Result = "Negativo: Comunicazione presente";
        }
        if($str_126bisExist!="")
            $str_Result = $str_126bisExist."\n".$str_Result;
        if($n_fileType==0){
            $pdf->MultiCell(20, 9, $r_Fine['ProtocolId'] . ' / ' . $r_Fine['ProtocolYear'], 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(30, 9, 'Ref: ' . $codeStr, 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(25, 9, DateOutDB($NotificationDate), 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(15, 9, $r_Fine['VehiclePlate'], 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(40, 9, $strTrespasser, 1, 'L', false, 0, '', '', true, 0, false, true, 9, 'M');
            $pdf->MultiCell(60, 9, $str_Result, 1, 'L', false, 1, '', '', true, 0, false, true, 9, 'M');
        }
        else{
            $str_Csv.= '
                    <tr>
                        <td>'.$r_Fine['ProtocolId'] . ' / ' . $r_Fine['ProtocolYear'].'</td>
                        <td>'.'Ref: ' . $codeStr.'</td>
                        <td>'.DateOutDB($NotificationDate).'</td>
                        <td>'.$r_Fine['VehiclePlate'].'</td>
                        <td>'.$strTrespasser.'</td>
                        <td>'.$str_Result.'</td>
                    </tr>
                ';

        }

        $n_ContRow++;

    }

    if($n_fileType==0) {
        $FileName = 'export.pdf';
        $pdf->Output(ROOT . '/doc/print/' . $FileName, "F");
        $_SESSION['Documentation'] = $MainPath . '/doc/print/' . $FileName;

        header("location: prc_126Bis.php?".$str_get);
    }
    else{
        $str_Csv .= '</table>';
        echo $str_Csv;
    }
}
else{
    header("location: prc_126Bis.php?".$str_get."&error=Articolo 126Bis non trovato per l'anno ".$CurrentYear);
    die;
}



