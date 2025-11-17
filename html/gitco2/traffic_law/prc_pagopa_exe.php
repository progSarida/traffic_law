<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(INC . "/function_postalCharge.php");
require_once(INC . "/initialization.php");
require_once(INC . "/pagopa.php");
require_once(CLS . "/cls_progressbar.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS . "/cls_pdf.php");
require_once(TCPDF . "/fpdi.php");

$a_Results = array();
$a_Messages = array();
$pagopaLimit = 50;
$NotificationDate = date("Y-m-d");
$ZoneId = 0;
$pagopaServicequery = $rs->Select("PagoPAService", "id={$r_Customer['PagoPAService']}");
$pagopaService = mysqli_fetch_array($pagopaServicequery);
$pagopaLimit = $pagopaService['RowLimit'];
$rs = new CLS_DB();

$str_Where = "
    CityId='{$_SESSION['cityid']}' AND CountryId='Z000'
    AND ProtocolYear={$_SESSION['year']} AND StatusTypeId=10
    AND (PagoPA1 IS NULL OR PagoPA2 IS NULL) AND (TrespasserTypeId=1 OR TrespasserTypeId=11)";

//Se "Disabilita elaborazione PagoPA per i preinserimenti di inviti in AG (per art. 193/2 e 80/14)" è attivato in Procedure Ente,
//i preinserimenti con art 193/2 80/14 non verranno restituiti, a meno che non siano creati da inviti in AG oppure rinotifiche
if($r_Customer['DisableKindPagoPAProcessing']){
    $str_Where .= " AND !(((Article = 80 AND Paragraph = '14' AND COALESCE(Letter,'') = '') OR (Article = 193 AND Paragraph = '2' AND COALESCE(Letter,'') = '')) AND KindCreateDate IS NOT NULL AND PreviousId <= 0)";
}

$rs_ProcessingPagoPA = $rs->Select('V_ViolationPagoPA', $str_Where, "FineDate,FineTime LIMIT " . $pagopaLimit);

$Count = $rs->getArrayLine($rs->SelectQuery("
    SELECT COUNT(*) AS Count
    FROM V_ViolationPagoPA
    WHERE $str_Where"));

$total = min($pagopaLimit, $Count['Count']);

$cont = 0;
$ProgressFileName = CheckValue("ProgressFile", "s");
$ProgressFile = TMP . "/".$ProgressFileName;
$ProgressBar = new CLS_PROGRESSBAR($total);
$ProgressBar->writeJSON($cont, $ProgressFile);

$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
$pdf->TemporaryPrint = 0;
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('PagoPA');
$pdf->SetSubject('');
$pdf->SetKeywords('');
$pdf->SetMargins(10, 10, 10);
$pdf->setCellHeightRatio(1.5);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->AddPage();
$pdf->setHeaderFont(array('helvetica','',8));
$pdf->setFooterFont(array('helvetica','',8));
$pdf->SetFont('helvetica', '', 8);
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA,'',PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);

$MangerName = $r_Customer['ManagerName'];
$ManagerAddress = $r_Customer['ManagerAddress'];
$ManagerCity = $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")";
$ManagerPhone = $r_Customer['ManagerPhone'];

$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

$pdf->LN(20);
$pdf->setCellPaddings(0.5, 0.5, 0.5, 0.5);
$pdf->MultiCell(15, 0, 'ID Verbale', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(15, 0, 'Targa', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(30, 0, 'Data', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(30, 0, 'Rif', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(40, 0, 'TaxCode', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(45, 0, 'PagoPA1', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(45, 0, 'PagoPA2', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(12, 0, 'Parziale Ridotto', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(12, 0, 'Totale Ridotto', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(12, 0, 'Parziale', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(15, 0, 'Totale', 1, 'C', 0, 1, '', '', true);
$pdf->LN(6);

$simpleFileName = "pagoPA_{$_SESSION['cityid']}_" . date('Y-m-d_H-i') . ".pdf";
$fileName = getDocumentationPath('Z000', 70, '', $_SESSION['cityid'], $simpleFileName);
$minSuccess = 0;

while ($r_ProcessingPagoPA = $rs->getArrayLine($rs_ProcessingPagoPA)){
    $o_Result = new stdClass();
    $o_Message = new stdClass();
    
    $FineId = $r_ProcessingPagoPA['Id'];
    $fineDate = $r_ProcessingPagoPA['FineDate'];
    $rs_AdditionalArticle = null;
    
    if ($r_ProcessingPagoPA['ArticleNumber'] > 1){
        $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $FineId, "ArticleOrder");
//         while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)){
//             if ($r_AdditionalArticle['ReducedPayment'] == 1){
//                 //TODO è inutile impostarla a true se poco dopo viene rimessa a false, capire perchè c'era questo comportamento
//                 //$chk_ReducedPayment = true;
//             }
//         }
    }
    
    $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_ProcessingPagoPA['ArticleId'] . " AND Year=" . $r_ProcessingPagoPA['ProtocolYear']);
    $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

    $ProgressBar->writeJSON($cont++, $ProgressFile);
    $chk_ReducedPayment = false;

    if ($r_ArticleTariff['ReducedPayment'] == 1){
        $chk_ReducedPayment = true;
    }
    
    $trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
    
    if (! $trespasser = mysqli_fetch_array($trespassers)){
        $o_Message->Value = "Trasgressore non trovato per il verbale $FineId";
        $o_Message->Class = "alert-danger col-sm-12";
        array_push($a_Messages, $o_Message);
        continue;
    }

    $str_TrespasserName = substr(trim($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name']), 0, 139);
    $str_TrespasserName = str_replace(array("&",",","  "), array("E"," ",""), $str_TrespasserName);

    if (! preg_match('/[a-zA-Z0-9_@. -]+/m', $str_TrespasserName)){
        $o_Message->Value = "Nominativo trasgessore non valido per il verbale $FineId";
        $o_Message->Class = "alert-danger col-sm-12";
        array_push($a_Messages, $o_Message);
        continue;
    }
    
    // calcolo importi dopo aver recuperato i dati del trasgressore perché devo sapere se l'atto esce via pec o meno
    $a_Importi = calcolaImporti($r_ProcessingPagoPA, $rs_AdditionalArticle, $r_ArticleTariff, $r_Customer, $NotificationDate, $ZoneId, $trespasser['PEC']);
    $a_Pago = $a_Importi['Sum'];
    
    $str_TrespasserType = ($trespasser['Genre'] == "D") ? "G" : "F";
    $str_TrespasserTaxCode = trim(PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode'])) ?: "ANONIMO";
    $str_Fine = 'Anno ' . $r_ProcessingPagoPA['ProtocolYear'] . ' targa ' . $r_ProcessingPagoPA['VehiclePlate'];
    $a_PagoPA = array('','');

    //TODO CAPIRE COME VENIVANO USATI
//     $tipoContabilità = '1';
//     $codiceContabilità = '2';
//     $tipoDovuto = 'ALTRO';

    // $GenreParemeter D è per ditta/impresa e P per privato
    $GenreParemeter = ($trespasser['Genre'] == "D") ? "D" : "P";
    // Kind='S' è tipologia sanzione
    $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='" . $_SESSION['cityid'] . "' AND ServiceId=" . $pagopaService['Id'] . " AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
    $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
    
//     if (mysqli_num_rows($rs_PagoPAServiceParameter) > 0){
//         $r_PagoPAServiceParameter = mysqli_fetch_array($rs_PagoPAServiceParameter);
//         $tipoContabilità = $r_PagoPAServiceParameter['Type'];
//         $codiceContabilità = $r_PagoPAServiceParameter['Code'];
//         $tipoDovuto = $r_PagoPAServiceParameter['TypeDue'];
//     }
    
    // echo "<br>$tipoContabilità - $codiceContabilità";
    $validFine = $r_ProcessingPagoPA['VehiclePlate'] != null && trim($r_ProcessingPagoPA['VehiclePlate']) != '';
    
    $PagoPACode1 = $r_ProcessingPagoPA['PagoPA1'];
    $PagoPACode2 = $r_ProcessingPagoPA['PagoPA2'];
    
    if($chk_ReducedPayment){
        $fullFee = 'ReducedTotal'; //sanizione minima
        $partialFee = 'ReducedPartial'; //sanzione ridotta
    }else{
        $fullFee = 'Total'; //metà del massimo
        $partialFee = 'Partial'; //sanzione minima
    }
    
    $b_PagoPAFail1 = $b_PagoPAFail2 = false;
    $aPagoPAUpdate = array();
    
    if($validFine){
        if(empty($PagoPACode1)){
            $PagoPACode1 = callPagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $partialFee, $r_Customer, $FineId, $fineDate, $str_TrespasserType, $trespasser, $str_TrespasserTaxCode, $str_Fine, $a_PagoPAServiceParams);
            if(!empty($PagoPACode1)){
                $aPagoPAUpdate[] = array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1);
                $aPagoPAUpdate[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedPartial'] ,'settype'=>'flt');
                $aPagoPAUpdate[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedTotal'] ,'settype'=>'flt');
            } else $b_PagoPAFail1 = true;
        }
        
        if(empty($PagoPACode2)){
            $PagoPACode2 = callPagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $fullFee, $r_Customer, $FineId, $fineDate, $str_TrespasserType, $trespasser, $str_TrespasserTaxCode, $str_Fine, $a_PagoPAServiceParams);
            if(!empty($PagoPACode2)){
                $aPagoPAUpdate[] = array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2);
                $aPagoPAUpdate[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Partial'] ,'settype'=>'flt');
                $aPagoPAUpdate[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
            } else $b_PagoPAFail2 = true;
        }
        
        if(!empty($aPagoPAUpdate)){
            $rs->Update('Fine', $aPagoPAUpdate, "Id=" . $FineId);
        }
    }
    
    $aInsert = array(
        array('field' => 'FineId','selector' => 'value','type' => 'int','value' => $FineId,'settype' => 'int'),
        array('field' => 'Documentation','selector' => 'value','type' => 'str','value' => $simpleFileName),
        array('field' => 'DocumentationTypeId','selector' => 'value','type' => 'int','value' => 70,'settype' => 'int'),
        array('field' => 'Attachment','selector' => 'value','type' => 'int','value' => 0,'settype' => 'int'),
        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
    );
    
    $rs->insert('FineDocumentation', $aInsert);

    $htmlClass = "table_caption_warning";
    $fillColor = array(247,237,181);

    if (! $validFine){
        $htmlClass = "table_caption_warning";
        $fillColor = array(247,237,181);
    } else if ($b_PagoPAFail1 || $b_PagoPAFail2){
        $htmlClass = "table_caption_error";
        $fillColor = array(196,28,28);
    } else {
        $htmlClass = "table_caption_success";
        $fillColor = array(59,118,60);
        $minSuccess = 1;
    }
    
    $o_Result->FineId = array('Value' => $FineId, 'Class' => $htmlClass.' col-sm-1');
    $o_Result->VehiclePlate = array('Value' => $trespasser['VehiclePlate'], 'Class' => 'col-sm-1');
    $o_Result->FineDate = array('Value' => DateOutDB($trespasser['FineDate']).' '.TimeOutDB($trespasser['FineTime']), 'Class' => 'col-sm-2');
    $o_Result->Code = array('Value' => $trespasser['Code'], 'Class' => 'col-sm-1');
    $o_Result->TrespasserTaxCode = array('Value' => $str_TrespasserTaxCode, 'Class' => 'col-sm-1');
    $o_Result->PagoPA1 = array('Value' => $PagoPACode1 ?: '', 'Class' => 'col-sm-1');
    $o_Result->PagoPA2 = array('Value' => $PagoPACode2 ?: '', 'Class' => 'col-sm-1');
    $o_Result->ReducedPartial = array('Value' => $a_Pago['ReducedPartial'] ?: '', 'Class' => 'col-sm-1');
    $o_Result->ReducedTotal = array('Value' => $a_Pago['ReducedTotal'] ?: '', 'Class' => 'col-sm-1');
    $o_Result->Partial = array('Value' => $a_Pago['Partial'] ?: '', 'Class' => 'col-sm-1');
    $o_Result->Total = array('Value' => $a_Pago['Total'] ?: '', 'Class' => 'col-sm-1');
        
    array_push($a_Results, $o_Result);
    
    $pdf->setCellPaddings(0.5, 0.5, 0.5, 0.5);
    $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
    $pdf->MultiCell(15, 0, $FineId, 1, 'C', 1, 0, '', '', true);
    $pdf->SetFillColor(255, 255, 255);

    $pdf->MultiCell(15, 0, $trespasser['VehiclePlate'], 1, 'C', 0, 0, '', '', true);
    $pdf->MultiCell(30, 0, $trespasser['FineDate'] . ' ' . $trespasser['FineTime'], 1, 'C', 1, 0, '', '', true);
    $pdf->MultiCell(30, 0, $trespasser['Code'], 1, 'C', 3, 0, '', '', true);
    $pdf->MultiCell(40, 0, $str_TrespasserTaxCode, 1, 'C', 4, 0, '', '', true);
    $pdf->MultiCell(45, 0, $PagoPACode1, 1, 'C', 5, 0, '', '', true);
    $pdf->MultiCell(45, 0, $PagoPACode2, 1, 'C', 6, 0, '', '', true);
    $pdf->MultiCell(12, 0, $a_Pago['ReducedPartial'], 1, 'C', 7, 0, '', '', true);
    $pdf->MultiCell(12, 0, $a_Pago['ReducedTotal'], 1, 'C', 8, 0, '', '', true);
    $pdf->MultiCell(12, 0, $a_Pago['Partial'], 1, 'C', 9, 0, '', '', true);
    $pdf->MultiCell(15, 0, $a_Pago['Total'], 1, 'C', 10, 1, '', '', true);
    $pdf->LN(1);
}

$ProgressBar->writeJSON($cont, $ProgressFile);

$pdf->SetFillColor(255, 255, 255);

$pdf->Output(ROOT . '/' . $fileName, "F");

echo json_encode(
    array(
        "Esito" => $minSuccess,
        "Dati" => $a_Results,
        "Messaggi" => $a_Messages,
        "File" => $MainPath . '/' . $fileName
    )
);