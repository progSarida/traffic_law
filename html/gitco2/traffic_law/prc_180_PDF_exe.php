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


$str_ProcessingTable = ($s_TypePlate=="N") ? "National" : "Foreign";

$rs_ProcessingData = $rs->Select('ProcessingData180'.$str_ProcessingTable, "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0");
$r_ProcessingData = mysqli_fetch_array($rs_ProcessingData);
$str_WhereCountry = ($s_TypePlate=="N") ? " AND CountryId='Z000'" : " AND CountryId!='Z000'";
//condizione aggiunta per escludere i verbali creati da avviso bonario che hanno un record 30 in FineHistory
$str_Where .= " AND Id not in (select FH30.FineId from FineHistory FH30 where FH30.NotificationTypeId = 30)";

$str_UserId = $_SESSION['username'];


if($Search_FromFineDate != "")      $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate != "")        $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
if($Search_FromProtocolId>0)        $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId>0)          $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
if($Search_Year!="")                $str_Where .= " AND ProtocolYear=".$Search_Year;




$CityId = $r_ProcessingData['CityId'];
$n_ControllerId = $r_ProcessingData['ControllerId'];
$ProcessingDate = date("Y-m-d");
$ProcessingStartTime = date("H:i:s");
if($Search_FromNotificationDate!="")
    $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."' ";
if($Search_ToNotificationDate!="")
    $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."' ";

$rs_Article = $rs->Select('V_Article', "Article=180 AND Paragraph='8' AND CityId='".$CityId."' AND Year=".$CurrentYear);

$str_get = "btn_search=1&TypePlate=".$s_TypePlate."&Search_FromFineDate=".$Search_FromFineDate
    ."&Search_ToFineDate=".$Search_ToFineDate."&Search_FromNotificationDate=".$Search_FromNotificationDate
    ."&Search_ToNotificationDate=".$Search_ToNotificationDate."&Search_FromProtocolId=".$Search_FromProtocolId
    ."&Search_ToProtocolId=".$Search_ToProtocolId."&Search_Year=".$Search_Year."&ElaborationDate=".DateOutDB($CurrentDate)
    ."&ElaborationTime=".$CurrentTime."&ElaborationType=".$ElaborationType;

if(mysqli_num_rows($rs_Article)>0) {

    $rs_FineProcedure = $rs->Select('V_180Procedure',$str_Where.$str_WhereCountry." AND CityId='".$CityId."'", "NotificationDate");
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
    $n_ContRow = 0;
    if($n_fileType==1){
        $filename="export.xls";
        header ("Content-Type: application/vnd.ms-excel");
        header ("Content-Disposition: inline; filename=$filename");
        $str_Csv = '
        <table border="1">
	        <tr>
	            <td>VERB ORIG</td>
                <td>REF</td>
                <td>DATA NOTIFICA</td>
                <td>TARGA</td>
                <td>PROPRIETARIO</td>
                <td>ESITO</td>
            </tr>
	    ';
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


        $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


        $pdf->LN(15);
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

        $str_Result = "";

        $trespasser = array(1=>null,2=>null,3=>null,10=>null,11=>null);
        $a_trespasser = array(1=>null,2=>null);
        $rs_FineTrespasser = $rs->SelectQuery('SELECT TrespasserTypeId, TrespasserId, CompanyName, Surname, Name FROM V_FineTrespasser WHERE FineId='.$r_FineProcedure['Id']);
        while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)) {
            $trespasser[$r_FineTrespasser['TrespasserTypeId']] = $r_FineTrespasser['CompanyName'].$r_FineTrespasser['Surname']." ".$r_FineTrespasser['Name'];
        }

        if($trespasser[1]!=""){
            $a_trespasser[1] = $trespasser[1];
        }
        else if($trespasser[2]!=""){
            $a_trespasser[1] = $trespasser[2];
        }

        $n_ContRow++;
        $str_Result = '';

        if ($n_ContRow == 58 && $n_fileType==0) {
            $pdf->AddPage();
            $pdf->SetFont('arial', '', 10, '', true);

            $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);


            $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


            $pdf->LN(15);

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

        $b_FineDispute = false;
        $n_DisputeDay = 0;
        $str_Dispute = "";

        $NotificationDate = $r_FineProcedure['NotificationDate'];
        $PresentationDate = $r_FineProcedure['PresentationDate'];

        if(strlen($trespasser[1])>26)
            $trespasser[1] = substr($trespasser[1],0,24)."...";
        $codeStr = $r_Fine['Code'];
        if(strlen($r_Fine['Code'])>18)
            $codeStr = substr($r_Fine['Code'],0,18)."...";
        $strTrespasser = $a_trespasser[1];
        if(strlen($a_trespasser[1])>28)
            $strTrespasser = substr($a_trespasser[1],0,28)."...";


        $rs_FineDispute = $rs->Select('V_FineDispute', "FineId=" . $r_FineProcedure['Id']);
        if (mysqli_num_rows($rs_FineDispute) > 0) {
            $r_FineDispute = mysqli_fetch_array($rs_FineDispute);

            switch ($r_FineDispute['DisputeStatusId']){
                case 1:
                    $str_Result = "Negativo: Ricorso in attesa o rinviato";
                    $b_FineDispute = true;
                    break;
                case 3:
                    $str_Result = "Negativo: Ricorso accolto";
                    $b_FineDispute = true;

                    break;
                case 2:
                    $str_Dispute = " - Ricorso respinto o inammissibile";
                    $n_DisputeDay = DateDiff("D", $r_FineDispute['DateFile'], $r_FineDispute['DateMerit'])+1;
                    break;
            }
        }

        if ($b_FineDispute) {
            $n_ContFineDispute++;
        }
        else if ($PresentationDate == "") {

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
                $str_ok = "Limite ".($RangeDayMax+$r_ProcessingData['RangeDayMin']);
                if($n_DisputeDay>0 || $covidDays>0)
                    $str_ok.= " ( ".($r_ProcessingData['RangeDayMax']+$r_ProcessingData['RangeDayMin']);
                if($n_DisputeDay>0)
                    $str_ok.= " + ricorso ".$n_DisputeDay;
                if($covidDays>0)
                    $str_ok.= " + covid ".$covidDays;
                if($n_DisputeDay>0 || $covidDays>0)
                    $str_ok.= " )";
                $str_Result = "Positivo ".$str_Dispute."\nTrascorsi ".$n_Day." - ".$str_ok;
            } else {
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
                    $str_Result = "Negativo: Scadenza termini in giorni\nTrascorsi ".$n_Day." - ".$str_max;
                }

                else{
                    $str_min = "Limite Min ".($r_ProcessingData['RangeDayMin']+$r_ProcessingData['WaitDay']);
                    if($r_ProcessingData['WaitDay']>0)
                        $str_min.= " ( Minimo ".$r_ProcessingData['RangeDayMin']. " + Attesa ".$r_ProcessingData['WaitDay'].")";
                    if($n_Day<0)
                        $n_Day = 0;
                    $str_Result = "Negativo: Anticipo termini in giorni\nTrascorsi ".$n_Day." - ".$str_min;
                }
            }
        }
        else {
            $str_Result = "Negativo: Presentazione presente";
        }

        if($n_fileType==0){
            $pdf->MultiCell(20, 6, $r_Fine['ProtocolId'] . ' / ' . $r_Fine['ProtocolYear'], 1, 'L', false, 0, '', '', true, 0, false, true, 6, 'M');
            $pdf->MultiCell(30, 6, 'Ref: ' . $codeStr, 1, 'L', false, 0, '', '', true, 0, false, true, 6, 'M');
            $pdf->MultiCell(25, 6, DateOutDB($NotificationDate), 1, 'L', false, 0, '', '', true, 0, false, true, 6, 'M');
            $pdf->MultiCell(15, 6, $r_Fine['VehiclePlate'], 1, 'L', false, 0, '', '', true, 0, false, true, 6, 'M');
            $pdf->MultiCell(40, 6, $strTrespasser, 1, 'L', false, 0, '', '', true, 0, false, true, 6, 'M');
            $pdf->MultiCell(60, 6, $str_Result, 1, 'L', false, 1, '', '', true, 0, false, true, 6, 'M');
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
    }

    if($n_fileType==0) {
        $FileName = 'export.pdf';
        $pdf->Output(ROOT . '/doc/print/' . $FileName, "F");
        $_SESSION['Documentation'] = $MainPath . '/doc/print/' . $FileName;

        header("location: prc_180.php?".$str_get);
    }
    else{
        $str_Csv .= '</table>';
        echo $str_Csv;
    }
}
