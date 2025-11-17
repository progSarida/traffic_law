<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");


include_once TCPDF . "/tcpdf.php";


$Search_Genre = CheckValue('Search_Genre', 'n');

$Search_FromNotificationDate = CheckValue('Search_FromNotificationDate', 's');
$Search_ToNotificationDate = CheckValue('Search_ToNotificationDate', 's');

$Search_Address = CheckValue('Search_Address', 's');
$Search_Trespasser = CheckValue('Search_Trespasser', 's');
$Search_Locality = CheckValue('Search_Locality', 's');
$Search_ArticleId = CheckValue('Search_ArticleId', 'n');

$btn_search = CheckValue('btn_search', 'n');

$d_PrintDate = (CheckValue('PrintDate', 's') == "") ? date("d/m/Y") : CheckValue('PrintDate', 's');

$n_TypeViolation = CheckValue('Search_TypeViolation', 'n');
$n_TypePayment = CheckValue('Search_TypePayment', 'n');
$n_TypeRule = CheckValue('Search_TypeRule', 'n');
$n_TypeNotification = CheckValue('Search_TypeNotification', 'n');

$FineArchive = CheckValue('FineArchive', 'n');
$FineDispute = CheckValue('FineDispute', 'n');
///////////////////////////////////////
// CREATION QUERY
///////////////////////////////////////
$str_Where = " CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];


if ($Search_Address != "") $str_Where .= " AND Address='" . $Search_Address . "'";
if ($Search_FromProtocolId > 0) $str_Where .= " AND ProtocolId>=" . $Search_FromProtocolId;
if ($Search_ToProtocolId > 0) $str_Where .= " AND ProtocolId<=" . $Search_ToProtocolId;
if ($Search_Trespasser != "") $str_Where .= " AND (CompanyName LIKE '%" . addslashes($Search_Trespasser) . "%' OR Surname LIKE '%" . addslashes($Search_Trespasser) . "%')";
if ($Search_Locality != "") $str_Where .= " AND Locality='" . $Search_Locality . "' ";


if ($n_TypeNotification > 0 || $Search_FromNotificationDate != "" || $Search_ToNotificationDate != "") {

    $str_QueryFineHistory = " ";

    if ($Search_FromNotificationDate != "") $str_Where .= " AND DeliveryDate>='" . DateInDB($Search_FromNotificationDate) . "'";
    if ($Search_ToNotificationDate != "") $str_Where .= " AND DeliveryDate<='" . DateInDB($Search_ToNotificationDate) . "'";


    if ($n_TypeNotification == 1) {
        $str_Where .= " AND ((ResultId>=1 AND ResultId<=9) OR ResultId = 22)";
    } else if ($n_TypeNotification == 3) {
        $str_Where .= " AND ResultId>9 AND ResultId<22 ";
    } else if ($n_TypeNotification == 2) {
        $str_Where .= " AND ResultId IS NULL ";
    }

}
if ($Search_ArticleId > 0) {
    $str_Where .= " AND ArticleId=" . $Search_ArticleId;

}

if ($Search_FromFineDate != "") $str_Where .= " AND FineDate>='" . DateInDB($Search_FromFineDate) . "'";
if ($Search_ToFineDate != "") $str_Where .= " AND FineDate<='" . DateInDB($Search_ToFineDate) . "'";

if ($n_TypePayment > 0) {
    if ($n_TypePayment == 2) {
        $str_Where .= " AND FineId IN(SELECT FineId FROM FinePayment WHERE CityId='" . $_SESSION['cityid'] . "') ";
    } else if ($n_TypePayment == 3) {
        $str_Where .= " AND FineId NOT IN(SELECT FineId FROM FinePayment WHERE CityId='" . $_SESSION['cityid'] . "') ";
    }

}

if ($FineArchive == 1) {
    $str_Where .= " AND StatusTypeId>=12 AND StatusTypeId<=37 ";
} else if ($FineArchive == 2) {
    $str_Where .= " AND StatusTypeId>=35 AND StatusTypeId<=37 ";
} else {
    $str_Where .= " AND StatusTypeId>=12 AND StatusTypeId<35 ";
}
if ($FineDispute == 0) {
    $str_Where .= " AND FineId NOT IN (SELECT FineId Id FROM FineDispute WHERE DisputeStatusId=1)";
} else if ($FineDispute == 2) {
    $str_Where .= " AND FineId IN (SELECT FineId Id FROM FineDispute WHERE DisputeStatusId=1)";
}

if ($Search_Genre == 1) {
    $str_Where .= " AND Genre != 'D'";
} else if ($Search_Genre == 2) {
    $str_Where .= " AND Genre = 'D'";
}


$strOrder = "ProtocolYear, ProtocolId";

$table_rows = $rs->Select("V_prn_Assessment", $str_Where, $strOrder);


if (mysqli_num_rows($table_rows) > 0) {


    $rs_Customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
    $r_Customer = mysqli_fetch_array($rs_Customer);

    $MangerName = $r_Customer['ManagerName'];
    $ManagerAddress = $r_Customer['ManagerAddress'];
    $ManagerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
    $ManagerPhone = $r_Customer['ManagerPhone'];
    $LumpSum = $r_Customer['LumpSum'];


    $str_Table =  "National";

    $rs_ProcessingData = $rs->Select('ProcessingDataPayment'.$str_Table, "Disabled=0 AND CityId='" . $_SESSION['cityid'] . "'");

    $r_ProcessingData = mysqli_fetch_array($rs_ProcessingData);
    $f_AmountLimit          = $r_ProcessingData['AmountLimit'];
    $n_PaymentDayAccepted   = $r_ProcessingData['PaymentDayAccepted'];

    $str_WhereCountry = ($str_Table=="National") ? "CountryId='Z000'" :  "CountryId!='Z000'";





    $rs_Result = $rs->Select('Result', "1=1");
    while ($r_Result = mysqli_fetch_array($rs_Result)) {
        $a_Result[$r_Result['Id']] = $r_Result['Title'];
    }
    $a_GradeType = array("", "I", "II", "III");

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
    $n_ContRow = 0;


    $n_CountNotification = 0;
    $n_CountPayment = $n_CountNotPayment = 0;

    $f_TotalPayment = 0.00;

    $n_CountReducedPayment = $n_CountNormalPayment = $n_CountMaxPayment = 0;
    $f_TotalReducedPayment = $f_TotalNormalPayment = $f_TotalMaxPayment = 0.00;
    $f_TotalReducedFee =  $f_TotalNormalFee = $f_TotalMaxFee = 0.00;










    while ($table_row = mysqli_fetch_array($table_rows)) {
        $n_ContRow++;


        if ($n_ContRow == 14) {
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
        }


        $str_PreviousId = "";
        if ($table_row['PreviousId'] > 0) {
            $rs_Previous = $rs->Select('Fine', "Id=" . $table_row['PreviousId']);
            $r_Previous = mysqli_fetch_array($rs_Previous);
            $str_PreviousId = 'Verbale collegato Cron ' . $r_Previous['ProtocolId'] . '/' . $r_Previous['ProtocolYear'];
        }

        $str_Archive = "";
        if ($table_row['StatusTypeId'] == 35) {
            $rs_Archive = $rs->SelectQuery("
                SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
                FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
                WHERE FA.FineId=" . $table_row['FineId']);
            $r_Archive = mysqli_fetch_array($rs_Archive);

            $str_Archive = 'Archiviato: ' . DateOutDB($r_Archive['ArchiveDate']);


        } else if ($table_row['StatusTypeId'] == 36) {
            $rs_Previous = $rs->Select('Fine', "PreviousId=" . $table_row['FineId']);
            $r_Previous = mysqli_fetch_array($rs_Previous);

            $str_PreviousId = 'Noleggio ristampato con Cron ' . $r_Previous['ProtocolId'] . '/' . $r_Previous['ProtocolYear'];

        }


        $str_Article = "Art. ";
        $rs_article = $rs->Select('V_Article', "Id=" . $table_row['ArticleId'] . " AND Year=" . $table_row['ProtocolYear']);
        $r_article = mysqli_fetch_array($rs_article);

        $str_Article .= $r_article['Article'] . ' ' . str_replace("0", "", $r_article['Paragraph']) . ' ' . $r_article['Letter'];


        $rs_Row = $rs->SelectQuery("
        SELECT  
        FA.Fee,
        FA.MaxFee,
        
        ArT.ReducedPayment,
        
        FH.NotificationTypeId,
        FH.CustomerFee,
        FH.NotificationFee,
        FH.ResearchFee,
        FH.CanFee, 	
        FH.CadFee,
        FH.NotifierFee,
        FH.OtherFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=" . $table_row['FineId'] . " AND (NotificationTypeId=6 OR NotificationTypeId=15) AND ArT.Year=" . $table_row['ProtocolYear']
        );

        $r_Row = mysqli_fetch_array($rs_Row);

        $NotificationDate = $r_Row['DeliveryDate'];
        $CustomerFee = $r_Row['CustomerFee'];
        $NotificationFee = $r_Row['NotificationFee'] + $r_Row['NotifierFee'] + $r_Row['OtherFee'] - $CustomerFee;
        $ResearchFee = $r_Row['ResearchFee'];
        $CanFee = $r_Row['CanFee'];
        $CadFee = $r_Row['CadFee'];


        $ReducedFee = ($r_article['ReducedPayment']) ? number_format(($r_Row['Fee'] * FINE_PARTIAL), 2) : $r_Row['Fee'];
        $NormalFee = $r_Row['Fee'];
        $MaxFee = $r_Row['MaxFee'] * FINE_MAX;
        $AdditionalFee = $NotificationFee + $ResearchFee + $CustomerFee;
        if($LumpSum) $AdditionalFee = $AdditionalFee + $CanFee + $CadFee;

        $f_ReducedFee = (float)$ReducedFee + (float)$AdditionalFee;
        $f_NormalFee = (float)$NormalFee + (float)$AdditionalFee;
        $f_MaxFee = (float)$MaxFee + (float)$AdditionalFee;



/*
        $f_TotalReducedFee += $f_ReducedFee;
        $f_TotalNormalFee += $f_NormalFee;
        $f_TotalMaxFee += $f_MaxFee;
*/

        $str_ReducedFee = "Rid: ".NumberDisplay($f_ReducedFee)."(".NumberDisplay($ReducedFee)."+".NumberDisplay($AdditionalFee).")";
        $str_NormalFee = "Ord: ".NumberDisplay($f_NormalFee)."(".NumberDisplay($NormalFee)."+".NumberDisplay($AdditionalFee).")";



        $rs_Locality = $rs->Select(MAIN_DB . ".City", "Id='" . $table_row['Locality'] . "'");
        $r_Locality = mysqli_fetch_array($rs_Locality);

        $str_Locality = $r_Locality['Title'] . ' - ' . $table_row['Address'];


        $str_Flow = (isset($table_row['FlowDate'])) ? 'Flusso: ' . DateOutDB($table_row['FlowDate']) : '';

        $str_Send = (isset($table_row['SendDate'])) ? 'Invio: ' . DateOutDB($table_row['SendDate']) : '';

        $str_Result = "";
        if ($table_row['ResultId']!="") {
            $str_Result = (isset($table_row['DeliveryDate'])) ? 'Notificato: ' . DateOutDB($table_row['DeliveryDate']) : $a_Result[$table_row['ResultId']];

            $n_CountNotification++;
        }


        $str_Payment = '';
        $rs_Row = $rs->Select('FinePayment', "FineId=" . $table_row['FineId']);
        if (mysqli_num_rows($rs_Row) > 0) {
            $r_Row = mysqli_fetch_array($rs_Row);
            $str_Payment = 'Pagato: ' . DateOutDB($r_Row['PaymentDate']) . " - € " . NumberDisplay($r_Row['Amount']);


                if($NotificationDate != ""){
                    $n_Day = DateDiff("D", $NotificationDate, $r_Row['PaymentDate'])-$n_PaymentDayAccepted;

                } else {
                    $n_Day = 10;
                }




                if($n_Day<=5){
                    if(($r_Row['Amount']+$f_AmountLimit)<$f_ReducedFee){
                        $n_CountNormalPayment++;
                        $f_TotalNormalPayment += $f_NormalFee;
                    }else{
                        $n_CountReducedPayment++;
                        $f_TotalReducedPayment += $f_ReducedFee;
                    }

                } else if($n_Day<=60){
                    if(($r_Row['Amount']+$f_AmountLimit)<$f_NormalFee){
                        $n_CountMaxPayment++;
                        $f_TotalMaxPayment += $f_MaxFee;
                    } else{
                        $n_CountNormalPayment++;
                        $f_TotalNormalPayment += $f_NormalFee;
                    }
                } else{
                    $n_CountMaxPayment++;
                    $f_TotalMaxPayment += $f_MaxFee;
                }

            $n_CountPayment++;;
            $f_TotalPayment += $r_Row['Amount'];

        } else {
            $n_CountNotPayment++;

            $f_TotalReducedFee += $f_ReducedFee;
            $f_TotalNormalFee += $f_NormalFee;
            $f_TotalMaxFee += $f_MaxFee;
        }


        $str_Dispute = '';
        $rs_Row = $rs->Select('V_FineDispute', "FineId=" . $table_row['FineId'] . " ORDER BY GradeTypeId DESC");
        if (mysqli_num_rows($rs_Row) > 0) {
            $r_Row = mysqli_fetch_array($rs_Row);
            $str_Dispute = $a_GradeType[$r_Row['GradeTypeId']] . ' Grado - ' . $r_Row['OfficeTitle'] . ' ' . $r_Row['OfficeCity'] . ' Depositato in data ' . DateOutDB($r_Row['DateFile']);
        }

        $rs_Row = $rs->Select('FineCommunication', "FineId=" . $table_row['FineId']);
        $r_Row = mysqli_fetch_array($rs_Row);
        $str_Communication = (!is_null($r_Row['CommunicationDate'])) ? 'Comunicazione dati: ' . DateOutDB($r_Row['CommunicationDate']) : '';


        $y = $pdf->getY();
        $pdf->writeHTMLCell(15, 4, 10, $y, $table_row['ProtocolId'] . ' / ' . $table_row['ProtocolYear'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 25, $y, DateOutDB($table_row['FineDate']) . ' ' . TimeOutDB($table_row['FineTime']), 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(15, 4, 50, $y, $table_row['VehiclePlate'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(135, 4, 65, $y, '('.$table_row['TrespasserId']. ') '.$table_row['CompanyName'] . ' ' . $table_row['Surname'] . ' ' . $table_row['Name'], 1, 0, 1, true, 'L', true);
        $pdf->LN(4);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(70, 4, 10, $y, substr($str_Locality, 0, 50), 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 80, $y, $str_Article, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(35, 4, 130, $y, $str_ReducedFee, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(35, 4, 165, $y, $str_NormalFee, 1, 0, 1, true, 'L', true);


        $pdf->LN(4);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(30, 4, 10, $y, 'Ref: ' . $table_row['Code'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 40, $y, $str_Flow, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 65, $y, $str_Send, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 90, $y, $str_Result, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(35, 4, 120, $y, $str_Payment, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(45, 4, 155, $y, $str_Archive, 1, 0, 1, true, 'L', true);
        $pdf->LN(4);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(50, 4, 10, $y, $str_PreviousId, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 60, $y, $str_Communication, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(90, 4, 110, $y, $str_Dispute, 1, 0, 1, true, 'L', true);
        $pdf->LN(6);

    }

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


    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 4, 10, $y, "Riepilogo generale", 1, 0, 1, true, 'C', true);
    $pdf->LN(4);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot Verbali notificati", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, $n_CountNotification, 1, 0, 1, true, 'L', true);
    $pdf->LN(4);


/*
    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot imp dovuto entro 5gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, NumberDisplay($f_TotalReducedFee), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot imp dovuto entro 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, NumberDisplay($f_TotalNormalFee), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot imp dovuto oltre 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, NumberDisplay($f_TotalNormalFee), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

*/



    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Verbali pagati totali", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 60, $y, $n_CountPayment, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, "Per un totale di", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 150, $y, NumberDisplay($f_TotalPayment), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);



/*
    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Verbali ridotti pagati", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 60, $y, $n_CountReducedPayment, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, "Per un totale di", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 150, $y, NumberDisplay($f_TotalReducedPayment), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Verbali ordinari pagati", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 60, $y, $n_CountNormalPayment, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, "Per un totale di", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 150, $y, NumberDisplay($f_TotalNormalPayment), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);
*/



    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "N. Verb. entro 5gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 60, $y, $n_CountReducedPayment, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, "Tot imp dovuto entro 5gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 150, $y, NumberDisplay($f_TotalReducedPayment), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "N. Verb. entro 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 60, $y, $n_CountNormalPayment, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, "Tot imp dovuto entro 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 150, $y, NumberDisplay($f_TotalNormalPayment), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "N. Verb. oltre 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(40, 4, 60, $y, $n_CountMaxPayment, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, "Tot imp dovuto oltre 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 150, $y, NumberDisplay($f_TotalMaxPayment), 1, 0, 1, true, 'L', true);
    $pdf->LN(15);




    $y = $pdf->getY();
    $pdf->writeHTMLCell(190, 4, 10, $y, "Riepilogo generale non pagati", 1, 0, 1, true, 'C', true);
    $pdf->LN(4);
    
    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Verbali non pagati totali", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, $n_CountNotPayment, 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot imp dovuto entro 5gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, NumberDisplay($f_TotalReducedFee), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot imp dovuto entro 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, NumberDisplay($f_TotalNormalFee), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Tot imp dovuto oltre 60gg", 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(140, 4, 60, $y, NumberDisplay($f_TotalMaxFee), 1, 0, 1, true, 'L', true);
    $pdf->LN(4);

    $FileName = 'export.pdf';


    $pdf->Output(ROOT . '/doc/print/' . $FileName, "F");
    $_SESSION['Documentation'] = $MainPath . '/doc/print/' . $FileName;


}


header("location: prn_assessment.php" . $str_GET_Back_Page . "&btn_search=1&Search_FromNotificationDate=" . $Search_FromNotificationDate . "&Search_ToNotificationDate=" . $Search_ToNotificationDate . "&Search_TypePayment=" . $n_TypePayment . "&Search_ArticleId=" . $Search_ArticleId . "&FineArchive=" . $FineArchive . "&Search_Genre=" . $Search_Genre . "&Search_TypeNotification=" . $n_TypeNotification. "&FineDispute=" . $FineDispute);