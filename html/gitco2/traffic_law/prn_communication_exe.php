<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
include (INC . "/function.php");
require (INC . "/initialization.php");
ini_set('max_execution_time', 3000);

if (! is_dir(ROOT . "/doc/print"))
  mkdir(ROOT . "/doc/print", 0777);
if (! is_dir(ROOT . "/doc/print/art126"))
  mkdir(ROOT . "/doc/print/art126", 0777);

$str_Where = "1=1";
$strOrder = "ProtocolYear DESC, ProtocolId DESC";

$s_TypePlate        = CheckValue('TypePlate','s');
//$minYear = mysqli_fetch_array($rs->ExecuteQuery("select min(Year) as minYear from ArticleTariff art join Article a on (a.Id=art.ArticleId) where a.CityId='" . $_SESSION['cityid'] . "'"));

$str_Where .= " AND StatusTypeId>=10 AND StatusTypeId<=32 AND CityId='" . $_SESSION['cityid']."'";

$rs_parameter=$rs->Select("V_CustomerParameter","CityId='{$_SESSION['cityid']}'");
$customerParameter=mysqli_fetch_array($rs_parameter);

$str_Where.=createCommunicationFilter(
    $s_TypePlate,
    $Search_Country,
    $Search_FromProtocolId,
    $Search_ToProtocolId,
    $Search_Plate,
    $Search_Trespasser,
    $Search_Violation,
    $Search_LicenseYoung,
    $Search_FromFineDate,
    $Search_ToFineDate,
    $Search_Ref,
    //$Search_FromNotificationDate partirà sempre dal 30/03/2021 se i ricorsi sono esclusi
    $Search_FromNotificationDate,
    $Search_ToNotificationDate,
    $Search_PaymentDate,
    $Search_FromProtocolYear,
    $Search_ToProtocolYear,
    $Search_CommunicationStatus,
    $Search_Com126Bis,
    $customerParameter['Data126BisNationalWaitDay'],
    $Flag126bis,
    $FlagDispute,
    $Search_NotificationStatus);

trigger_error("***NotificationStatus stampa: ".$Search_NotificationStatus);

$rs_Manager = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_Manager = mysqli_fetch_array($rs_Manager);

$MangerName = $r_Manager['ManagerName'];
$ManagerAddress = $r_Manager['ManagerAddress'];
$ManagerCity = $r_Manager['ManagerZIP'] . " " . $r_Manager['ManagerCity'] . " (" . $r_Manager['ManagerProvince'] . ")";
$ManagerPhone = $r_Manager['ManagerPhone'];

$CountryTitle = $ViolationTitle = $CommunicationType = '';

if (! empty($Search_Country))
  {
  $rs_CountryTitle = $rs->SelectQuery("SELECT Title FROM Country WHERE Id='$Search_Country'");
  $CountryTitle = mysqli_fetch_assoc($rs_CountryTitle)['Title'];
  }
if (! empty($Search_Violation))
  {
  $rs_ViolationTitle = $rs->SelectQuery("SELECT Title FROM ViolationType WHERE Id='$Search_Violation'");
  $ViolationTitle = mysqli_fetch_assoc($rs_ViolationTitle)['Title'];
  }
$rs_Communication = $rs->Select('V_FineCommunication', $str_Where, $strOrder);
$FileName = $_SESSION['cityid'] . '_art126_' . date("Y-m-d_H-i");

$PrintType = $_POST['PrintType'];
if ($PrintType == 'Pdf')
  {
  include_once TCPDF . "/tcpdf.php";
  $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
  $pdf->SetCreator(PDF_CREATOR);
  $pdf->SetAuthor($_SESSION['citytitle']);
  $pdf->SetTitle('Comunicazioni Art. 126');
  $pdf->SetSubject('Comunicazioni Art. 126');
  $pdf->SetKeywords('');
  $pdf->SetMargins(10, 10, 10);
  $pdf->AddPage();
  $pdf->SetFont('helvetica', '', 9, '', true);
  $pdf->setFooterData(array(0,64,0), array(0,64,128));
  $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
  $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Image($_SESSION['blazon'], 10, 10, 12, 17);
  $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
  $pdf->LN(4);
  $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
  $pdf->LN(4);
  $pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
  $pdf->LN(4);
  $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

  $pdf->LN(10);

  $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>ELENCO COMUNICAZIONI ART. 126</strong></h3>', true, false, true, false, '');
  $pdf->writeHTML('<h4 style="text-align: center;margin:0;">Data di stampa: ' . date('d/m/Y') . '</h4>', true, false, true, false, '');
  $pdf->LN(20);
  $pdf->writeHTML('<h3 style="margin:0;"><strong>OPZIONI SELEZIONATE AL MOMENTO DELLA STAMPA</strong></h3>', true, false, true, false, '');
  $pdf->LN(5);

  $pdf->SetFont('helvetica', 'B', 8);
  $pdf->MultiCell(40, 0, 'Da cron', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'A cron', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'Da anno', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'Ad anno', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'Da data accertamento', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'A data accertamento', 1, '', 0, 1, '', '', true);
  $pdf->SetFont('helvetica', '', 8);

  $pdf->MultiCell(40, 0, $Search_FromProtocolId, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_ToProtocolId, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_FromProtocolYear, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_ToProtocolYear, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_FromFineDate, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_ToFineDate, 1, '', 0, 1, '', '', true);
  $pdf->LN(5);

  $pdf->SetFont('helvetica', 'B', 8);
  $pdf->MultiCell(40, 0, 'Riferimento', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'Nazionalità', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, 'Nazione', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'Da data notifica', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'A data notifica', 1, '', 0, 1, '', '', true);

  $pdf->SetFont('helvetica', '', 8);
  $pdf->MultiCell(40, 0, $Search_Ref, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $s_TypePlate == 'F' ? 'Estere' : 'Nazionali', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, $CountryTitle, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_FromNotificationDate, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, $Search_ToNotificationDate, 1, '', 0, 1, '', '', true);
  $pdf->LN(5);

  $pdf->SetFont('helvetica', 'B', 8);
  $pdf->MultiCell(40, 0, 'Neopatentati', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, 'Verbale art. 126 bis creato', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, 'Stato comunicazione', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, 'Stato notifica verbale originario', 1, '', 0, 1, '', '', true);
  
  $pdf->SetFont('helvetica', '', 8);
  $pdf->MultiCell(40, 0, $Search_LicenseYoung==1 ? "Escludi" : ($Search_LicenseYoung==0 ? "Includi" : "Solo loro"), 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(40, 0, CheckbuttonOutDB($Search_Com126Bis), 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, COMMUNICATIONSTATUSOPTIONS[$Search_CommunicationStatus], 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, ORIGINALFINENOTIFICATIONSTATUS[$Search_NotificationStatus], 1, '', 0, 1, '', '', true);
  $pdf->LN(5);
  
  $pdf->SetFont('helvetica', 'B', 8);
  $pdf->MultiCell(80, 0, 'Trasgressore', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, 'Violazione', 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, 'Elabora verb. art. 126 Bis a NO', 1, '', 0, 1, '', '', true);
  
  $pdf->SetFont('helvetica', '', 8);
  $pdf->MultiCell(80, 0, $Search_Trespasser, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, $ViolationTitle, 1, '', 0, 0, '', '', true);
  $pdf->MultiCell(80, 0, $Flag126bis==1 ? "Escludi" : ($Flag126bis==0 ? "Includi" : "Solo loro"), 1, '', 0, 1, '', '', true);
  $pdf->LN(5);
  
  $pdf->SetFont('helvetica', 'B', 8);
  $pdf->MultiCell(80, 0, 'Ricorsi', 1, '', 0, 0, '', '', true);
 
  $pdf->SetFont('helvetica', '', 8);
  $pdf->MultiCell(80, 0, $FlagDispute==1 ? "Escludi" : ($FlagDispute==0 ? "Includi" : "Solo loro"), 1, '', 0, 1, '', '', true);
  

  $pdf->AddPage();
  $pdf->SetFont('helvetica', '', 7, '', true);

  $pdf->setFooterData(array(0,64,0), array(0,64,128));
  $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
  $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0, 0, 0);

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
  $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>ELENCO COMUNICAZIONI ART. 126</strong></h3>', true, false, true, false, '');
  $pdf->LN(10);

  $y = $pdf->getY();
  $pdf->SetFont('helvetica', '', 8, '', true);
  $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
  $pdf->writeHTMLCell(30, 4, 20, $y, "Cron", 1, 0, 1, true, 'C', true);
  $pdf->writeHTMLCell(50, 4, 50, $y, "Riferimento", 1, 0, 1, true, 'C', true);
  $pdf->writeHTMLCell(50, 4, 100, $y, "Dati atto", 1, 0, 1, true, 'C', true);
  $pdf->writeHTMLCell(90, 4, 150, $y, "Trasgressore", 1, 0, 1, true, 'C', true);
  $pdf->writeHTMLCell(25, 4, 240, $y, "Data not.", 1, 0, 1, true, 'C', true);
  $pdf->writeHTMLCell(20, 4, 265, $y, "Articolo", 1, 0, 1, true, 'C', true);
  $pdf->LN(4);

  $n_Cont = 0;
  $n_Row = 0;

  $n_ChangePage = 30;

  $pdf->SetFont('helvetica', '', 8, '', true);
  while ($r_Communication = mysqli_fetch_array($rs_Communication))
    {
    trigger_error("Stampo riga per verbale " . $r_Communication['ProtocolId'] . '/' . $r_Communication['ProtocolYear'], E_USER_NOTICE);
    if ($n_Row == $n_ChangePage)
      {
      $pdf->AddPage();
      $pdf->SetFont('helvetica', '', 8, '', true);
      $pdf->setFooterData(array(0,64,0), array(0,64,128));
      $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
      $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
      $pdf->SetFillColor(255, 255, 255);
      $pdf->SetTextColor(0, 0, 0);
      $pdf->LN(10);
      $pdf->writeHTML('<h3 style="text-align: center;margin:0;"><strong>ELENCO COMUNICAZIONI ART. 126</strong></h3>', true, false, true, false, '');
      $pdf->LN(10);
      $y = $pdf->getY();
      $pdf->SetFont('helvetica', '', 8, '', true);
      $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
      $pdf->writeHTMLCell(30, 4, 20, $y, "Cron", 1, 0, 1, true, 'C', true);
      $pdf->writeHTMLCell(50, 4, 50, $y, "Riferimento", 1, 0, 1, true, 'C', true);
      $pdf->writeHTMLCell(50, 4, 100, $y, "Dati atto", 1, 0, 1, true, 'C', true);
      $pdf->writeHTMLCell(90, 4, 150, $y, "Trasgressore", 1, 0, 1, true, 'C', true);
      $pdf->writeHTMLCell(25, 4, 240, $y, "Data not.", 1, 0, 1, true, 'C', true);
      $pdf->writeHTMLCell(20, 4, 265, $y, "Articolo", 1, 0, 1, true, 'C', true);
      $pdf->LN(4);
      $pdf->SetFont('helvetica', '', 8, '', true);
      $n_Row = 0;
      $n_ChangePage = 30;
      }
    $n_Row ++;
    $n_Cont ++;

    $str_Trespasser = StringOutDB((! empty($r_Communication['CompanyName']) ? $r_Communication['CompanyName'] . ' ' : '') . $r_Communication['Surname'] . ' ' . $r_Communication['Name']);
    $str_Trespasser = (strlen($str_Trespasser) > 42) ? substr($str_Trespasser, 0, 40) . '...' : $str_Trespasser;
    $y = $pdf->getY();
    $pdf->SetFont('helvetica', '', 8, '', true);
    $pdf->writeHTMLCell(10, 4, 10, $y, $n_Cont, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 20, $y, $r_Communication['ProtocolId'] . '/' . $r_Communication['ProtocolYear'], 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(50, 4, 50, $y, $r_Communication['Code'], 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(50, 4, 100, $y, DateOutDB($r_Communication['FineDate']) . ' - ' . TimeOutDB($r_Communication['FineTime']). ' - ' . $r_Communication['VehiclePlate'], 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(90, 4, 150, $y, $str_Trespasser, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(25, 4, 240, $y, ! empty($r_Communication['NotificationDate']) ? DateOutDB($r_Communication['NotificationDate']) : '-', 1, 0, 1, true, 'C', true);
    $pdf->writeHTMLCell(20, 4, 265, $y, $r_Communication['Article'] . ' ' . $r_Communication['Paragraph'] . ' ' . $r_Communication['Letter'], 1, 0, 1, true, 'L', true);
    $pdf->LN(4);
    }
  $FileName .= '.pdf';
  $pdf->Output(ROOT . "/doc/print/art126/" . $FileName, "F");
  $_SESSION['Documentation'] = $MainPath . '/doc/print/art126/' . $FileName;
  header("location: " . impostaParametriUrl(array('Filter' => 1), "mgmt_communication.php" . $str_GET_Parameter));
  }
else
  {
  $FileName .= '.xls';
  $str_Csv = '<table border="1">
        <tbody>
	        <tr>
	            <td>Cron</td>
                <td>Ref</td>
                <td>Dati atto</td>
                <td>Trasgressore</td>
                <td>Data not.</td>
                <td>Articolo</td>
            </tr>';
  while ($r_Communication = mysqli_fetch_array($rs_Communication))
    {
    $str_Trespasser = StringOutDB((! empty($r_Communication['CompanyName']) ? $r_Communication['CompanyName'] . ' ' : '') . $r_Communication['Surname'] . ' ' . $r_Communication['Name']);
    $str_Csv .= "<tr>
                    <td>{$r_Communication['ProtocolId']}/{$r_Communication['ProtocolYear']}</td>
                    <td>{$r_Communication['Code']}</td>
                    <td>".DateOutDB($r_Communication['FineDate']) . ' - ' . TimeOutDB($r_Communication['FineTime']). ' - ' . $r_Communication['VehiclePlate']."</td>
                    <td>{$str_Trespasser}</td>
                    <td>" . (! empty($r_Communication['NotificationDate']) ? DateOutDB($r_Communication['NotificationDate']) : '-') . "</td>
                    <td>" . $r_Communication['Article'] . ' ' . $r_Communication['Paragraph'] . ' ' . $r_Communication['Letter'] . "</td></tr>";
    }
  $str_Csv .= "</tbody></table>";
  file_put_contents(ROOT . "/doc/print/art126/". $FileName, $str_Csv);
  $_SESSION['Documentation'] = $MainPath . '/doc/print/art126/' . $FileName;
  header("location: " . impostaParametriUrl(array('Filter' => 1), "mgmt_communication.php" . $str_GET_Parameter));
  }
