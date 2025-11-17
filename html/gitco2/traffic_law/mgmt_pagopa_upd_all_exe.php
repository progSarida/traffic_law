<?php
include("_path.php");
include(INC."/parameter.php");
include(INC."/pagopa.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
include(INC."/function_postalCharge.php");
require(TCPDF . "/tcpdf.php");
ini_set('max_execution_time', 3000);

$rs= new CLS_DB();

$str_Warning = '';

$FineId = CheckValue('FineId','n');
$NotificationDate = date("Y-m-d");
$ZoneId = 0;
$MangerName = $r_Customer['ManagerName'];
$ManagerAddress = $r_Customer['ManagerAddress'];
$ManagerCity = $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")";
$ManagerPhone = $r_Customer['ManagerPhone'];

$a_Failed = array();

$pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
$pagopaService=mysqli_fetch_array($pagopaServicequery);

$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);
$pdf->TemporaryPrint= 0;
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('PagoPA');
$pdf->SetSubject('');
$pdf->SetKeywords('');
$pdf->SetMargins(10,10,10);
$pdf->setCellHeightRatio(1.5);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->setHeaderFont(array('helvetica', '', 8));
$pdf->setFooterFont(array('helvetica', '', 8));
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);
$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
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

$simpleFileName='pagoPA_upd_'.date("Y-m-d_H-i").'.pdf';
$fileName=getDocumentationPath('Z000',70,'',$_SESSION['cityid'],$simpleFileName);

if (isset($_POST['checkbox'])) {
    foreach ($_POST['checkbox'] as $FineId){
        $r_ProcessingPagoPA = $rs->getArrayLine($rs->Select("V_ViolationPagoPA"," Id= ".$FineId));
        
        if($r_ProcessingPagoPA){
            $chk_ReducedPayment = false;
            $rs_AdditionalArticle=null;
            
            if ($r_ProcessingPagoPA['ArticleNumber'] > 1) {
                $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $FineId, "ArticleOrder");
                //TODO NON USATO
//                 while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle))
//                     if ($r_AdditionalArticle['ReducedPayment'] == 1)
//                         $chk_ReducedPayment = true;
            }
            
            $trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
            $trespasser = mysqli_fetch_array($trespassers);
            $str_TrespasserType = ($trespasser['Genre'] == "D") ? "G" : "F";
            $str_TrespasserTaxCode = trim(PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode'])) ?: "ANONIMO";
            $str_Fine = 'Anno ' . $r_ProcessingPagoPA['ProtocolYear'] . ' targa ' . $r_ProcessingPagoPA['VehiclePlate'];
            $fineDate = $r_ProcessingPagoPA['FineDate'];
            
            //TODO CAPIRE COME VENIVANO USATI
//             $tipoContabilità='1';
//             $codiceContabilità='2';
//             $tipoDovuto = 'ALTRO';
            
            //$GenreParemeter D è per ditta/impresa e P per privato
            $GenreParemeter = ($trespasser['Genre'] == "D")? "D" : "P";
            //Kind='S' è tipologia sanzione
            $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
            $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
            
//             if(mysqli_num_rows($rs_PagoPAServiceParameter)>0) {
//                 $r_PagoPAServiceParameter = mysqli_fetch_array($rs_PagoPAServiceParameter);
//                 $tipoContabilità = $r_PagoPAServiceParameter['Type'];
//                 $codiceContabilità = $r_PagoPAServiceParameter['Code'];
//                 $tipoDovuto = $r_PagoPAServiceParameter['TypeDue'];
//             }
            
            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_ProcessingPagoPA['ArticleId'] . " AND Year=" . $r_ProcessingPagoPA['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
			
            if ($r_ArticleTariff['ReducedPayment'] == 1){
                $chk_ReducedPayment = true;
            }
            
            $a_Importi = calcolaImporti($r_ProcessingPagoPA, $rs_AdditionalArticle, $r_ArticleTariff, $r_Customer, $NotificationDate, $ZoneId, $trespasser['PEC']);
            
            $PagoPACode1 = $r_ProcessingPagoPA['PagoPA1'];
            $PagoPACode2 = $r_ProcessingPagoPA['PagoPA2'];
            $b_PagoPAFail1 = $b_PagoPAFail2 = false;
            $a_FineUpd = array();
            
            if($chk_ReducedPayment){
                $fullFeeUpd = 'ReducedTotal'; //sanizione minima
                $partialFeeUpd = 'ReducedPartial'; //sanzione ridotta
            }else{
                $fullFeeUpd = 'Total'; //metà del massimo
                $partialFeeUpd = 'Partial'; //sanzione minima
            }
            
            if(!empty($PagoPACode1)){
                if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $partialFeeUpd, $PagoPACode1, $FineId, $fineDate, $str_TrespasserType, $trespasser, $str_TrespasserTaxCode, $str_Fine, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                    $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedPartial'] ,'settype'=>'flt');
                    $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Partial'] ,'settype'=>'flt');
                } else $b_PagoPAFail1 = true;
            }
            
            if(!empty($PagoPACode2)){
                if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $fullFeeUpd, $PagoPACode2, $FineId, $fineDate, $str_TrespasserType, $trespasser, $str_TrespasserTaxCode, $str_Fine, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                    $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedTotal'] ,'settype'=>'flt');
                    $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                } else $b_PagoPAFail2 = true;
            }
            
            if($b_PagoPAFail1 || $b_PagoPAFail2){
                $a_Failed[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito per uno o più IUV.';
            }
            
            if(!empty($a_FineUpd)) {
                $a_FinePagoPAHistory = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'ReminderId','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                    array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1),
                    array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2),
                    array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPAReducedPartial'] ,'settype'=>'flt'),
                    array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPAPartial'] ,'settype'=>'flt'),
                    array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPAReducedTotal'] ,'settype'=>'flt'),
                    array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_ProcessingPagoPA['PagoPATotal'] ,'settype'=>'flt'),
                );
                
                $rs->Insert("FinePagoPAHistory", $a_FinePagoPAHistory);
                $rs->Update("Fine", $a_FineUpd, "Id=".$FineId);
            }
        }
        
        if(!$b_PagoPAFail1 || !$b_PagoPAFail2){
            $aInsert= array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId,'settype'=>'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $simpleFileName),
                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 70,'settype'=>'int'),
                array('field' => 'Attachment', 'selector' => 'value', 'type' => 'int', 'value' => 0,'settype'=>'int'),
                array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
            );
            $rs->insert('FineDocumentation', $aInsert);
            
            $pdf->setCellPaddings(0.5, 0.5, 0.5, 0.5);
            $pdf->MultiCell(15, 0,  $FineId, 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(15, 0, $trespasser['VehiclePlate'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(30, 0, $trespasser['FineDate'].' '.$trespasser['FineTime'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(30, 0, $trespasser['Code'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(40, 0, $str_TrespasserTaxCode, 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(45, 0, $r_ProcessingPagoPA['PagoPA1'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(45, 0, $r_ProcessingPagoPA['PagoPA2'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(12, 0, $a_Importi['Sum']['ReducedPartial'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(12, 0, $a_Importi['Sum']['ReducedTotal'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(12, 0, $a_Importi['Sum']['Partial'], 1, 'C', 0, 0, '', '', true);
            $pdf->MultiCell(15, 0, $a_Importi['Sum']['Total'], 1, 'C', 0, 1, '', '', true);
            $pdf->LN(1);
        }
    }
    $pdf->Output(ROOT.'/'.$fileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/'.$fileName;
}

foreach($a_Failed as $failMessage){
    trigger_error($failMessage, E_USER_WARNING);
    $str_Warning .= $failMessage.'<br>';
}

if($str_Warning != ''){
    $_SESSION['Message']['Warning'] = $str_Warning;
} else {
    $_SESSION['Message']['Success'] = 'Aggiornamento dei codici PagoPA riuscito.';
}

header("location: mgmt_pagopa.php".$str_GET_Parameter);
?>