<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
require_once(CLS . "/cls_view.php");
include_once TCPDF . "/tcpdf.php";
ini_set('max_execution_time', 3000);
const PRINT_DECURTAZIONE="/doc/print/license_point";
if (! is_dir(ROOT . "/doc/print"))
    mkdir(ROOT . "/doc/print", 0777);
if (! is_dir(ROOT . PRINT_DECURTAZIONE))
    mkdir(ROOT . PRINT_DECURTAZIONE, 0777);
$a_LicensePointMex = getLicensePointCodeMex($rs);
$columnWidths = array(10, 25, 60, 80, 50, 25,15, 15);
$headers = array("", "Cron", "Codice - Trasgressore<br>Codice Fiscale", "Luogo e Data Nascita<br>Indirizzo Residenza", "Dati Patente", "Data violazione<br>Data notifica","Articolo", "Punti");
$height = 8;

$str_GETLink = "";

$str_CurrentPage = "?btn_search=1";

$n_RecordLimit = CheckValue('RecordLimit', 'n');
if ($n_RecordLimit == 0)
    $n_RecordLimit = 5;

$btn_search = CheckValue('btn_search', 'n');
if($Search_DiscrepancyType==null)
    $Search_DiscrepancyType='Tutte';
$str_GET_Parameter .= "&btn_search=$btn_search";

$rs_Manager = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_LicensePoint = mysqli_fetch_array($rs_Manager);
$MangerName = $r_LicensePoint['ManagerName'];
$ManagerAddress = $r_LicensePoint['ManagerAddress'];
$ManagerCity = $r_LicensePoint['ManagerZIP'] . " " . $r_LicensePoint['ManagerCity'] . " (" . $r_LicensePoint['ManagerProvince'] . ")";
$ManagerPhone = $r_LicensePoint['ManagerPhone'];
if ($r_Customer['LicensePointPaymentCompletion'] == 0)
    $licensepoint = new CLS_VIEW(V_LICENSEPOINT0);
else
    $licensepoint = new CLS_VIEW(V_LICENSEPOINT1);

    $str_Where .= " AND CityId='{$_SESSION['cityid']}' " . createLicensePointWhere();
global $Search_FromProtocolYear;

$strOrder = "FineDate ASC, ProtocolId ASC";
$rs_LicensePoint = $rs->selectQuery($licensepoint->generateSelect($str_Where, null, $strOrder, $n_RecordLimit));

$PrintType = $_GET['PrintType'];
if ($PrintType == 'pdf'){
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Point');
    $pdf->SetSubject('Point');
    $pdf->SetKeywords('');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetFont('arial', '', 9, '', true);
    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(150, 150, 150)));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->AddPage();


    $pdf->Image($_SESSION['blazon'], 10, 10, 12, 17);

    $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

    $pdf->LN(10);

    $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>ELENCO DECURTAZIONE PUNTI VERBALI</strong></h3>', true, false, true, false, '');
    $pdf->writeHTML('<h4 style="text-align: center;margin:0;">Data di stampa: ' . date('d/m/Y') . '</h4>', true, false, true, false, '');
    $pdf->LN(20);
    $pdf->writeHTML('<h3 style="margin:0;"><strong>OPZIONI SELEZIONATE AL MOMENTO DELLA STAMPA</strong></h3>', true, false, true, false, '');
    $pdf->LN(5);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(40, 0, 'Da cron', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'A cron', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Da anno', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Ad anno', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Da data', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'A data', 1, '', 0, 1, '', '', true);
    $pdf->SetFont('helvetica', '', 8);

    $pdf->MultiCell(40, 0, $Search_FromProtocolId,   1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_ToProtocolId,     1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_FromProtocolYear, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_ToProtocolYear,   1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_FromFineDate,     1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_ToFineDate,       1, '', 0, 1, '', '', true);
    $pdf->LN(5);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(40, 0, 'Riferimento', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Nazionalità patente', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Nazione', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Da data notifica', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'A data notifica', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Neopatentati', 1, '', 0, 1, '', '', true);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(40, 0, $Search_Ref, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_LicenseType == 'F' ? 'Estere' : 'Nazionali', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $CountryTitle, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_FromNotificationDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_ToNotificationDate, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_LicenseYoung == 1 ? "Escludi" : ($Search_LicenseYoung == 0 ? "Includi" : "Solo loro"), 1, '', 0, 1, '', '', true);
    $pdf->LN(5);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(40, 0, 'Esclusi', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Anomalie', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Incongruenze', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(80, 0, 'Trasgressore', 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, 'Riattribuzione', 1, '', 0, 1, '', '', true);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(40, 0, $Search_LicenseHiddem == 0 ? "Escludi" : ($Search_LicenseHidden == 1 ? "Includi" : "Solo loro"), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_Anomalies == 0 ? "Escludi" : ($Search_Anomalies == 1 ? "Includi" : "Solo loro"), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_Discrepancy == 0 ? "Escludi" : ($Search_Discrepancy == 1 ? "Includi" : "Solo loro"), 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(80, 0, $Search_Trespasser, 1, '', 0, 0, '', '', true);
    $pdf->MultiCell(40, 0, $Search_Reattribution, 1, '', 0, 1, '', '', true);
    $pdf->LN(5);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(40, 0, 'Ricorso', 1, '', 0, 1, '', '', true);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(40, 0, $Search_HasDispute == 1 ? "Escludi" : ($Search_HasDispute == 0 ? "Includi" : "Solo loro"), 1, '', 0, 1, '', '', true);
    $pdf->LN(5);


    $pdf->AddPage();
    $pdf->Image($_SESSION['blazon'], 10, 10, 12, 17);

    $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

    $pdf->LN(10);
    $pdf->SetFont('helvetica', '', 9, '', true);
    $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>ELENCO DECURTAZIONE PUNTI VERBALI</strong></h3>', true, false, true, false, '');
    $pdf->LN(6);

    createPDFColumns($pdf, $columnWidths, $headers, $height,true);
    $n_Cont = 0;
    $n_Row = 0;

    $n_ChangePage = 15;


    $pdf->SetFont('arial', '', 8, '', true);
    while ($r_LicensePoint = mysqli_fetch_array($rs_LicensePoint)) {
        $b_RemovePointsCap = false;
        $str_anomalyText = '';
        $b_anomalyRow = false;
        $n_anomalyType = 1;
        $str_Trespasser = trim(trim($r_LicensePoint['Surname']) . ' ' . trim($r_LicensePoint['Name']));
        $n_LicenseYear = DateDiff("Y", $r_LicensePoint['LicenseDate'], $r_LicensePoint['FineDate']);
        $str_Trespasser = trim(trim($r_LicensePoint['Surname']) . ' ' . trim($r_LicensePoint['Name']));
        $str_anomalyText=checkTrespasser($r_LicensePoint,$str_Trespasser);
        if($str_anomalyText!=null)
            $b_anomalyRow=true;
        else if ($r_Customer['LicensePointPaymentCompletion'] == 0)//la data di notifica va controllata solo nel caso 0 in cui la data di notifica è obbligatoria. Nel caso 1 può bastare che ci sia un pagamento.
        {
            if (trim($r_LicensePoint['NotificationDate']) == ""){
                $str_anomalyText = "Data notifica mancante, questo ente effettua la decurtazione dei punti solo per i verbali completati";
                $b_anomalyRow = true;
            }
        }
        if ($r_LicensePoint['CommunicationStatus'] == 3)
        {
            $str_anomalyText = "Elaborazione già avvenuta in stato anomalo: ({$r_LicensePoint['LicensePointId']}) " . $a_LicensePointMex[$r_LicensePoint['LicensePointId']];
            $b_anomalyRow = true;
            $n_anomalyType = 2;
        }
    else if (trim($r_LicensePoint['LicensePointCode1']) == "" || (trim($r_LicensePoint['LicensePointCode2']) == "" && $r_LicensePoint["Habitual"]==1)) {
            $str_anomalyText = "Codice decurtazione punti mancante sull' articolo del verbale";
            $b_anomalyRow = true;
        }
        if ($r_LicensePoint['DocumentTypeId'] != 1) {
            $b_anomalyRow = true;
            $str_anomalyText = "Il trasgressore possiede un tipo di documento esente dalla decurtazione: {$r_LicensePoint['DocumentTypeTitle']}";
        }
        $n_Point = ($n_LicenseYear >= 3) ? $r_LicensePoint['LicensePoint'] : $r_LicensePoint['YoungLicensePoint'];
        if ($r_LicensePoint['ArticleNumber'] > 1) {
            $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $r_LicensePoint['Id'] . " AND LicensePoint>0");
            while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                $n_PointLicense = ($n_LicenseYear >= 3) ? $r_AdditionalArticle['LicensePoint'] : $r_AdditionalArticle['YoungLicensePoint'];
                $n_Point += $n_PointLicense;

                //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
                if ($r_AdditionalArticle['SuspensionLicense'] == 1 || $r_AdditionalArticle['LossLicense'] == 1 || in_array($r_AdditionalArticle['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
                    $b_RemovePointsCap = true;
            }
        }

        //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
        if ($r_LicensePoint['SuspensionLicense'] == 1 || $r_LicensePoint['LossLicense'] == 1 || in_array($r_LicensePoint['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
            $b_RemovePointsCap = true;
        if ($n_Point > 15 && !$b_RemovePointsCap)
            $n_Point = 15;

        if ($n_Row >= $n_ChangePage) {
            $pdf->AddPage();
            $pdf->LN(10);
            $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>ELENCO DECURTAZIONE PUNTI VERBALI</strong></h3>', true, false, true, false, '');
            $pdf->LN(10);
            createPDFColumns($pdf, $columnWidths, $headers, $height,true);
            $n_Row = 0;
        }
        $n_Row++;
        $n_Cont++;
        if ($r_LicensePoint['CommunicationStatus'] != 9)
            $point_sign = '<p style="color:red">-'.$n_Point.'</p>';
        else
            $point_sign = '<p style="color:green">+'.$n_Point.'</p>';
        $values = array(
            $n_Cont,
            $r_LicensePoint['ProtocolId'] . '/' . $r_LicensePoint['ProtocolYear'],
            "{$r_LicensePoint['TrespasserCode']} - {$r_LicensePoint['Surname']} {$r_LicensePoint['Name']}<br>{$r_LicensePoint['TaxCode']}",
            DateOutDB($r_LicensePoint['BornDate'])." ".$r_LicensePoint['BornPlace']."<BR>".$r_LicensePoint['Address']." ".$r_LicensePoint['City'],
            $r_LicensePoint['LicenseNumber'] . ' (' . $r_LicensePoint['LicenseCategory'] . ')' . ' - ' . $r_LicensePoint['LicenseOffice'] . ' ' . DateOutDB($r_LicensePoint['LicenseDate']),
            DateOutDB($r_LicensePoint['FineDate'])."<br>".DateOutDB($r_LicensePoint['NotificationDate']),
            getArticleString($r_LicensePoint['Article'] , $r_LicensePoint['Paragraph'] , $r_LicensePoint['Letter']),
            $point_sign);
        createPDFColumns($pdf, $columnWidths, $values, $height,false,array('L','L','L','L','L','C','R','R'));

        if ($b_anomalyRow){
            $pdf->SetFillColor(255, $n_anomalyType == 1 ? 255 : 234, 220);
            $pdf->setCellPaddings('', 0.5, '', '');
            createPDFColumns($pdf, array(10,270), array("",($n_anomalyType == 1 ? "INCONGRUENZA: " : "ANOMALIA: ").$str_anomalyText), $height, false, array('L','L'));
            $pdf->setCellPaddings('', 0, '', '');
            $pdf->SetFillColor(255, 255, 255);
            $n_Row ++;
        }
    }

    $FileName = $_SESSION['cityid'] . '_decurtazione_punti_' . date("Y-m-d_H-i") . '.pdf';
    $pdf->Output(ROOT . PRINT_DECURTAZIONE ."/". $FileName, "F");
    $_SESSION['Documentation'] = $MainPath . PRINT_DECURTAZIONE ."/". $FileName;
    header("location: " . impostaParametriUrl(array('Filter' => 1), "frm_upload_licensepoint.php" . $str_GET_Parameter));

}else{
    $FileName = $_SESSION['cityid'] . '_decurtazione_punti_' . date("Y-m-d_H-i") . '.xls';
    $str_Csv ="<table border='1'>
<tbody>
<tr><td colspan='6'><strong>ELENCO DECURTAZIONE PUNTI VERBALI</strong></td></tr>
<tr><td>Data di stampa</td><td colspan='5'>".date('d/m/Y')."</td></tr>
</tbody></table>
<br>
<table border='1'><tbody>
<tr><td colspan='6'><strong>OPZIONI SELEZIONATE AL MOMENTO DELLA STAMPA</strong></td></tr>
<tr>
    <td>Da cron</td>
    <td>A cron</td>
    <td>Da anno</td>
    <td>Ad anno</td>
    <td>Da data</td>
    <td>A data</td>    
</tr>
<tr>
    <td>$Search_FromProtocolId</td>
    <td>$Search_ToProtocolId</td>
    <td>$Search_FromProtocolYear</td>
    <td>$Search_ToProtocolYear</td>
    <td>$Search_FromFineDate</td>
    <td>$Search_ToFineDate</td>    
</tr>
<tr>
    <td>Riferimento</td>
    <td>Nazionalità</td>
    <td>Nazione</td>
    <td>Da data notifica</td>
    <td>A data notifica</td>
    <td>Neopatentati</td>    
</tr>
<tr>
    <td>$Search_Ref</td>
    <td>".($s_TypePlate == 'F' ? 'Estere' : 'Nazionali')."</td>
    <td>$CountryTitle</td>
    <td>$Search_FromNotificationDate</td>
    <td>$Search_ToNotificationDate</td>
    <td>$Search_LicenseYoung</td>    
</tr>
<tr>
    <td>Esclusi</td>
    <td>Anomalie</td>
    <td>Incongruenze</td>
    <td>Trasgressore</td>
    <td>Riattribuzione</td>
    <td>Ricorso</td>

</tr>
<tr>
    <td>".($Search_LicenseHidden == 0 ? "Escludi" : ($Search_LicenseHidden == 1 ? "Includi" : "Solo loro"))."</td>
    <td>".($Search_Anomalies == 0 ? "Escludi" : ($Search_Anomalies == 1 ? "Includi" : "Solo loro"))."</td>
    <td>".($Search_Discrepancy == 0 ? "Escludi" : ($Search_Discrepancy == 1 ? "Includi" : "Solo loro"))."</td>
    <td>$Search_Trespasser</td>
    <td>$Search_Reattribution</td>
    <td>".($Search_HasDispute == 1 ? "Escludi" : ($Search_HasDispute == 0 ? "Includi" : "Solo loro"))."</td>
</tr>
</tbody></table><br>";


    $str_Csv.="<table border='1'><tbody> <tr>
	            <td></td>
	            <td>Cron</td>
                <td>Codice trasgressore</td>
                <td>Nominativo trasgressore</td>
                <td>Codice fiscale</td>
                <td>Luogo nascita</td>
                <td>Data nascita</td>
                <td>Residenza</td>
                <td>Dati patente</td>
                <td>Data violazione</td>
                <td>Data notifica</td>
                <td>Articolo</td>
                <td>Punti</td>
                <td>Anomalie/incongruenza</td>
            </tr>";


    while ($r_LicensePoint = mysqli_fetch_array($rs_LicensePoint)) {
        $b_RemovePointsCap = false;
        $str_anomalyText = '';
        $b_anomalyRow = false;
        $n_anomalyType = 1;

        $str_Trespasser = trim(trim($r_LicensePoint['Surname']) . ' ' . trim($r_LicensePoint['Name']));
        $str_anomalyText=checkTrespasser($r_LicensePoint,$str_Trespasser);
        if($str_anomalyText!=null){
            $b_anomalyRow=true;
        }
        else if ($r_Customer['LicensePointPaymentCompletion'] == 0)//la data di notifica va controllata solo nel caso 0 in cui la data di notifica è obbligatoria. Nel caso 1 può bastare che ci sia un pagamento.
        {
            if (trim($r_LicensePoint['NotificationDate']) == ""){
                $str_anomalyText = "Data notifica mancante, questo ente effettua la decurtazione dei punti solo per i verbali completati";
                $b_anomalyRow = true;
            }
        }
        if ($r_LicensePoint['CommunicationStatus'] == 3)
        {
            $str_anomalyText = "Elaborazione già avvenuta in stato anomalo: ({$r_LicensePoint['LicensePointId']}) " . $a_LicensePointMex[$r_LicensePoint['LicensePointId']];
            $b_anomalyRow = true;
            $n_anomalyType = 2;
        }
     else if (trim($r_LicensePoint['LicensePointCode1']) == "" || (trim($r_LicensePoint['LicensePointCode2']) == "" && $r_LicensePoint["Habitual"]==1)) {
            $str_anomalyText = "Codice decurtazione punti mancante sull' articolo del verbale";
            $b_anomalyRow = true;
        }
        if ($r_LicensePoint['DocumentTypeId'] != 1) {
            $b_anomalyRow = true;
            $str_anomalyText = "Il trasgressore possiede un tipo di documento esente dalla decurtazione: {$r_LicensePoint['DocumentTypeTitle']}";
        }

        $n_LicenseYear = DateDiff("Y", $r_LicensePoint['LicenseDate'], $r_LicensePoint['FineDate']);
        $n_Point = ($n_LicenseYear >= 3) ? $r_LicensePoint['LicensePoint'] : $r_LicensePoint['YoungLicensePoint'];
        if ($r_LicensePoint['ArticleNumber'] > 1) {
            $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $r_LicensePoint['Id'] . " AND LicensePoint>0");
            while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                $n_PointLicense = ($n_LicenseYear >= 3) ? $r_AdditionalArticle['LicensePoint'] : $r_AdditionalArticle['YoungLicensePoint'];
                $n_Point += $n_PointLicense;
                //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
                if ($r_AdditionalArticle['SuspensionLicense'] == 1 || $r_AdditionalArticle['LossLicense'] == 1 || in_array($r_AdditionalArticle['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
                    $b_RemovePointsCap = true;
            }
        }

        //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
        if ($r_LicensePoint['SuspensionLicense'] == 1 || $r_LicensePoint['LossLicense'] == 1 || in_array($r_LicensePoint['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
            $b_RemovePointsCap = true;
        if ($n_Point > 15 && !$b_RemovePointsCap)
            $n_Point = 15;

        $n_Cont++;
        if ($r_LicensePoint['CommunicationStatus'] != 9)
            $point_sign = '<p style="color:red">-'.$n_Point.'</p>';
        else
            $point_sign = '<p style="color:green">+'.$n_Point.'</p>';

        $str_Csv .="<tr>
            <td>$n_Cont</td>
            <td>{$r_LicensePoint['ProtocolId']} / {$r_LicensePoint['ProtocolYear']}</td>
            <td>{$r_LicensePoint['TrespasserCode']}</td>
            <td>{$r_LicensePoint['Surname']} {$r_LicensePoint['Name']}</td>
            <td>{$r_LicensePoint['TaxCode']}</td>
            <td>{$r_LicensePoint['BornPlace']}</td>
            <td>".DateOutDB($r_LicensePoint['BornDate'])."</td>
            <td>{$r_LicensePoint['Address']} {$r_LicensePoint['City']}</td>
            <td>{$r_LicensePoint['LicenseNumber']} ( {$r_LicensePoint['LicenseCategory'] }) -  {$r_LicensePoint['LicenseOffice']} ".DateOutDB($r_LicensePoint['LicenseDate'])."</td>
            <td>".DateOutDB($r_LicensePoint['FineDate'])."</td>
            <td>".DateOutDB($r_LicensePoint['NotificationDate'])."</td>
            <td>".getArticleString($r_LicensePoint['Article'] , $r_LicensePoint['Paragraph'] , $r_LicensePoint['Letter'])."</td>
            <td>$point_sign</td>
            <td colspan='5'>$str_anomalyText</td></tr>";

    }
    $str_Csv .= "</tbody></table>";
    file_put_contents(ROOT . PRINT_DECURTAZIONE."/". $FileName, $str_Csv);
    $_SESSION['Documentation'] = $MainPath . PRINT_DECURTAZIONE ."/". $FileName;
    header("location: " . impostaParametriUrl(array('Filter' => 1), "frm_upload_licensepoint.php" . $str_GET_Parameter));
}
