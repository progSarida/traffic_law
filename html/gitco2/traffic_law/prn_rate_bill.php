<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require_once(CLS."/cls_iuv.php");
require(INC."/function.php");
require_once(INC."/pagopa.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");

require(CLS . "/cls_pdf.php");
require(CLS . '/cls_literal_number.php');
require_once(CLS."/avvisiPagoPA/ModelloRate.php");

function buildManagerInfo($r_Customer){
    $str_Info = '';
    
    if($r_Customer['ManagerZIP'] != '' || $r_Customer['ManagerCity'] != '' || $r_Customer['ManagerProvince'] != '' || $r_Customer['ManagerPhone'] != ''){
        $str_Info .= $r_Customer['ManagerZIP'] != '' ? $r_Customer['ManagerZIP'].' ' : '';
        $str_Info .= $r_Customer['ManagerCity'] != '' ? $r_Customer['ManagerCity'].' ' : '';
        $str_Info .= $r_Customer['ManagerProvince'] != '' ? "({$r_Customer['ManagerProvince']}) " : '';
        $str_Info .= $r_Customer['ManagerPhone'] ? ' - TEL: '.$r_Customer['ManagerPhone'] : '';
        $str_Info .= $r_Customer['ManagerPEC'] ? ' - PEC: '.$r_Customer['ManagerPEC'] : '';
    }
    
    return $str_Info;
}


$Id = CheckValue('payment_rate_id', 'n');

$payment_rate = $rs->SelectQuery("SELECT * FROM PaymentRate WHERE Id=" . $Id);
$payment_rate = mysqli_fetch_array($payment_rate);

$InstallmentPage =  CheckValue('InstallmentPage', 's') ?: 'mgmt_fine_upd.php';    //Imposta la pagina per il reindirizzamento
$FilterParameter = CheckValue('InstallmentPage', 's') != '' ? '&Filter=1' : ''; //Imposta il parametro Filter solo se la chiamata viene dalla pagina della lista rateizzazioni
$ConditionalId = CheckValue('InstallmentPage', 's') != '' ? ('&Id='.$payment_rate['Id'].'&FineId='.$payment_rate['FineId']) : ('&Id='.$payment_rate['FineId']);

$P =$InstallmentPage;

$trespasser = $rs->SelectQuery("SELECT * FROM V_Trespasser WHERE Id=" . $payment_rate['TrespasserId']);
$trespasser = mysqli_fetch_array($trespasser);

$str_Error = '';
$str_Warning = '';

$instalmentPath = NATIONAL_RATE."/".$_SESSION['cityid']."/".$payment_rate['FineId']."/".$payment_rate['Id'];

if (!is_dir($instalmentPath)){
    mkdir($instalmentPath, 0750, true);
    chmod($instalmentPath, 0750);
}

$FileName = "Bollettini_Rateizzazione.pdf";

//AVVISI-PAGOPA - AGGIUNGERE CHIAMATA A GENERAZIONE AVVISI DI PAGAMENTO CONDIZIONATI DAI FLAG RELATIVI

$rs_Fine = $rs->SelectQuery("SELECT * FROM V_Fine WHERE Id=" . $payment_rate['FineId']);

$FindNumber = mysqli_num_rows($rs_Fine);
if($FindNumber==0){
    $rs_Fine = $rs->SelectQuery("SELECT * FROM V_FineRent WHERE Id=" . $payment_rate['FineId']);
    $r_Fine = mysqli_fetch_array($rs_Fine);
    
} else {
    $r_Fine = mysqli_fetch_array($rs_Fine);
}

$payment_rates = $rs->SelectQuery("SELECT * FROM PaymentRateNumber WHERE PaymentRateId=" . $payment_rate['Id']);


$str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}
$NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType1'];
$NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['NationalProtocolLetterType2'];
$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $r_Fine['ViolationTypeId'] . " AND CityId='" . $_SESSION['cityid'] . "'");
$r_RuleType = mysqli_fetch_array($rs_RuleType);
$RuleTypeId = $r_RuleType['Id'];
$str_ProtocolLetter = ($RuleTypeId == 1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;


    $pdf = new PDF_HANDLE('P', 'mm', 'A4', true, 'UTF-8', false, true);

    $pdf->SetMargins(10, 10, 10);

    $page_format = array('Rotate' => -90);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);

$a_Address = array();
$a_Address['Riga1'] = $trespasser['Address'];
$a_Address['Riga2'] = '';
$a_Address['Riga3'] = $trespasser['ZIP'];
$a_Address['Riga4'] = $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ')';
$NW = new CLS_LITERAL_NUMBER();
$a_FifthField = array("Table" => 1, "Id" => $r_Fine['Id']);

    //Parametri verbale
    $FineId = $payment_rate['FineId'];
    //Parametri rateizzazione
    $PaymentRateId = $payment_rate['Id'];
    //Parametri PagoPa
    $b_PagoPAEnabled = $r_Customer['PagoPAPayment'] > 0 && $_SESSION['ruletypeid'] == RULETYPE_CDS;
    $PagoPaNoticeNational = $r_Customer['PagoPAPaymentNoticeNationalInstallment']; //Abilitazione avvisi pagamento nazionali PagoPa
    $PagoPaNoticeForeign = $r_Customer['PagoPAPaymentNoticeForeignInstallment'];   //Abilitazione avvisi pagamento esteri PagoPa
    $PagoPaNoticeFlag = ($trespasser['CountryId'] == 'Z000') ? $PagoPaNoticeNational : $PagoPaNoticeForeign; //Controlla il flag adeguato degli avvisi pagamento PagoPa in base alla nazionalità del trasgressore
    $str_ManagerInfo = buildManagerInfo($r_Customer);
    //Parametri stampatore
    $rs_PrinterParameter = $rs->Select("PrinterParameter", "PrinterId = 1 AND CityId = '{$_SESSION['cityid']}'");
    $r_PrinterParameter = $rs->getArrayLine($rs_PrinterParameter);
    $str_PostalAuthorization = trim($r_PrinterParameter['NationalPostalAuthorization']) ?? '';
    $str_PostalAuthorizationPagoPA = trim($r_PrinterParameter['NationalPostalAuthorizationPagoPA'] ?? ''); //FIXME Da condizionare con Foreign???
    //Parametri trasgressore li ho nella variabile $trespasser
    $str_ProtocolCode = $r_Fine['ProtocolId'].' / '. $r_Fine['ProtocolYear'].' / '.$str_ProtocolLetter;
    //Parametri stampa PagoPa
    $o_Avviso = new Avviso('Verbale C.D.S. Prot. '.$str_ProtocolCode, $_SESSION['blazon']);
    $o_Ente = new Ente($r_Customer['ManagerName'], $r_Customer['ManagerSector'], $str_ManagerInfo, trim($r_Customer['ManagerTaxCode']), $r_Customer['PagoPACBILL']);
    $o_Destinatario = new Destinatario($trespasser['TaxCode'], ($trespasser['Name'].' '.$trespasser['Surname']), ($trespasser['Address'].", ".$trespasser['StreetNumber'].", ".$trespasser['ZIP'].", ".$trespasser['City']));
    $a_Importi = array();
    $a_Bollettini = array();
    $ViolationTypeId = $r_Fine['ViolationTypeId'];
    //Parametri bollettini e PagoPa
    
    //FIXME IMPORTANTE!!!!!!! TOGLIERE LA CONDIZIONE SULL'ENTE FORMIGINE QUANDO LA FUNZIONALITA' SARA' ESTESA ANCHE AGLI ALTRI GESTORI
    //**************************************INIZIO BLOCCO DA MODIFICARE IN FUTURO********************************************
    if($r_Customer['CityId'] == 'D711'){
        $b_PrintBill = ($b_PagoPAEnabled ? $PagoPaNoticeFlag <= 0 : true) && !empty($r_Customer['NationalPostalType']);
        $b_PrintBillPagoPA = $b_PagoPAEnabled && !empty($r_Customer['NationalPostalTypePagoPA']) && $PagoPaNoticeFlag > 0;
    }
    else {
        $b_PrintBill = !empty($r_Customer['NationalPostalType']);
        $b_PrintBillPagoPA = false;
    }
    //**************************************FINE BLOCCO DA MODIFICARE IN FUTURO********************************************
    
    //Controlli parametri
    if($b_PagoPAEnabled){
        //Se l'ente non ha CF/PIVA impostati
        if(empty($r_Customer['ManagerTaxCode'])){
            $str_Error .= 'È necessario specificare il codice fiscale dell\'ente per il funzionamento della gestione PagoPA (Ente > Gestione Ente > Indirizzo).<br>';
        }
    }
    if($b_PrintBill){
        if(empty($r_Customer['NationalBankAccount'])){
            $str_Error .= 'Per la stampa del bollettino è necessario impostare il codice del conto corrente nei parametri dell\'ente.<br>';
        }
        if(empty($str_PostalAuthorization)){
            $str_Error .= 'Per la stampa del bollettino è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente in base alla Destinazione di stampa selezionata (Ente > Gestione Ente > Posta).<br>';
        }
    }
    if($b_PrintBillPagoPA){
        //Se la stampa all'avviso di pagamento PagoPA e la stampa del bollettino postale PagoPA sono attive ma non è impostata l'autorizzazione alla stampa
        if(empty($str_PostalAuthorizationPagoPA)){
            $str_Error .= 'Per la stampa del bollettino postale PagoPA è necessario impostare l\'autorizzazione alla stampa nei parametri dell\'ente in base alla Destinazione di stampa selezionata (Ente > Gestione Ente > Posta).<br>';
        }
    }
    //Se i parametri necessari dell'ente sono mancanti allora salta la generazione dei documenti
    if(empty($str_Error)){
        $printCheck = 0;
        $checkError = false;
        //Ciclo rateizzazioni
        while ($rate = mysqli_fetch_array($payment_rates)){
            $str_Causal = 'Rata n. '.$rate['RateNumber'].' Cron. '.$str_ProtocolCode." targa ".$r_Fine['VehiclePlate']." del ".DateOutDB($r_Fine['FineDate']);
            $flt_Amount = $rate['Amount'];
            
            
            //***Parte avviso di pagamento PagoPa***
            //Se è abilitato sia il flag sul pagamento PagoPaNazionale che quello per gli avvisi PagoPa nazionale o estero
            if($b_PagoPAEnabled && $b_PrintBillPagoPA){
                $PagoPACode = null;
                $PagoPAPaymentNotice = null;
                //Trasgressore
                $TrespasserType = ($trespasser['Genre'] == "D") ? "G" : "F";
                $TrespasserTaxCode = PickVatORTaxCode($trespasser['Genre'], $trespasser['VatCode'], $trespasser['TaxCode']);
                $GenreParemeter = ($trespasser['Genre'] == "D")? "D" : "P";
                //Servizio PagoPa
                $pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
                $pagopaService=mysqli_fetch_array($pagopaServicequery);
                $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
                $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
                //Array importi
                $a_Fee = array(
                    'Amounts' => array(
                        array(
                            'Total'=>number_format(((float)$flt_Amount), 2, '.', ''),
                            'ViolationTypeId' => $ViolationTypeId
                        )
                    ),
                    'Sum' => array(
                        'Total'=>number_format(((float)$flt_Amount), 2, '.', ''),
                    )
                );
                //Causale
                $FineText = 'Anno ' . $r_Fine['ProtocolYear'] . ' targa ' . $r_Fine['VehiclePlate'];
                //Trespasser dev'essere un record di trespasser e non della vista
                $r_Trespasser = $rs->getArrayLine($rs->Select("Trespasser","Id = ".$trespasser['Id']));
                $cls_iuv = new cls_iuv();
                if(empty($rate['PagoPAIUV'])){
                    //MK 20241118 da rev 10144
                    $PagoPACode = callPagoPA(PAGOPA_PREFIX_INSTALLMENT, $pagopaService, $a_Fee, 'Total', $r_Customer, $PaymentRateId, $rate['PaymentDate'], $TrespasserType, $r_Trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams,$rate['RateNumber']);
                }else{
                    $PagoPACode = $rate['PagoPAIUV'];
                    }
                if(!empty($PagoPACode)){
                    try{
                        if($r_Customer['IsIuvCodiceAvviso'] != 1){
                            
                            $PagoPAPaymentNotice = $cls_iuv->generateNoticeCode($PagoPACode, $r_Customer['PagoPAAuxCode'], $r_Customer['PagoPAApplicationCode']);
                        }else{
                            $PagoPAPaymentNotice = $PagoPACode;
                        }
                    }
                    catch(Exception $e){
                        trigger_error('PaymentRateId '.$PaymentRateId.' Rata n° '.$rate['RateNumber'].': Generazione codice avviso di pagamento PagoPA fallito.', E_USER_WARNING);
                        $str_Warning .= 'Rata n°: '.$rate['RateNumber'].' Generazione codice avviso di pagamento PagoPA fallita: '.$e->getMessage().'<br>';
                        $checkError = true;
                    }
                    //Salvataggio IUV su PaymentRateNumber
                    $PaymentRateNumber = array(
                        array('field'=>'PagoPAIUV','selector'=>'value','type'=>'str','value'=>$PagoPACode),
                    );
                    $rs->Update('PaymentRateNumber', $PaymentRateNumber,"PaymentRateId = ".$PaymentRateId." AND RateNumber = ".$rate['RateNumber']);
                
                    //Riempimento oggetti importo e bollettino
                    $a_Importi[] = new Importo($flt_Amount,$PagoPAPaymentNotice,null,null,DateOutDB($rate['PaymentDate']));
                    $a_Bollettini[] = new Bollettino(
                        $str_PostalAuthorizationPagoPA,
                        $r_Customer['NationalBankAccount'],
                        $r_Customer['NationalBankOwner'],
                        $r_Customer['NationalPostalTypePagoPA'],
                        $str_Causal
                        );
                }
                else{
                    trigger_error('PaymentRateId '.$PaymentRateId.' Rata n° '.$rate['RateNumber'].': Generazione IUV fallita.', E_USER_WARNING);
                    $str_Warning .= 'Rata n°: '.$rate['RateNumber'].' Generazione IUV fallita.<br>';
                    $checkError = true;
                }
            }
            //***Parte Bollettino classico***
            elseif($b_PrintBill){
                $a_FifthField['PaymentType'] = 1;
                //Se la rateizzazione è di un sollecito, mette 3 (sollecito) altrimenti 0 (atto giudizioario notificato (verbale))
                $a_FifthField['DocumentType'] = $payment_rate['DocumentTypeId'] == 9 ? 3 : 1;
                $str_FifthField = SetFifthField($a_FifthField, $rate['RateNumber']);
                $str_FifthFieldFee = SetFifthFieldFee($flt_Amount);
                $numeroLetterale = $NW->converti_numero_bollettino($flt_Amount);
                
                if($printCheck==0){
                    $pdf->AddPage('L', $page_format);
                    $pdf->crea_bollettino();
                    $billNumber = "uno";
                    $printCheck = 1;
                }
                else{
                    $pdf->crea_bollettino_inverso();
                    $billNumber = "due";
                    $printCheck = 0;
                }
                
                $pdf->scelta_td_bollettino($r_Customer['NationalPostalType'], $str_FifthField, str_replace(".", "", NumberDisplay($flt_Amount)), 'si', $r_Customer['NationalBankAccount'], $billNumber);
                $pdf->iban_bollettino($r_Customer['NationalBankIban'], $billNumber);
                $pdf->intestatario_bollettino(substr($r_Customer['NationalBankOwner'], 0, 50), $billNumber);
                $pdf->causale_bollettino($str_Causal, "Da pagare entro il ". ' ' . DateOutDB($rate['PaymentDate']), $billNumber);
                $pdf->zona_cliente_bollettino(substr($trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], 0, 50), $a_Address, $billNumber);
                $pdf->importo_in_lettere_bollettino($numeroLetterale, $billNumber);
                $pdf->set_importo_bollettino(NumberDisplay($flt_Amount),'si', $billNumber);
                $pdf->autorizzazione_bollettino($str_PostalAuthorization, $billNumber);
                
                $pdf->set_quinto_campo($r_Customer['NationalPostalType'], $str_FifthField);
            }
        }
    
    if($b_PagoPAEnabled && $b_PrintBillPagoPA){
        //Se non ci sono errori allora genera l'avviso
        if(!$checkError){
            $pdfAvviso = new ModelloRate($o_Avviso, $o_Ente, $o_Destinatario, new Importi($a_Importi));
            $pdfAvviso->setBollettini(new Bollettini($a_Bollettini));
            $pdfAvviso->costruisci(false);
            $pdfAvviso->Output($instalmentPath."/".$FileName,"F");
        }
        else{
            $str_Warning .= 'La generazione dell\'avviso è fallita. Riprovare';
        }
    }
    elseif($b_PrintBill){
        $pdf->Output($instalmentPath."/".$FileName, "F");
    }
    
    if(is_file($instalmentPath."/".$FileName)){
        $a_PaymentRate = array(
            array('field'=>'BillStatusId','selector'=>'value','type'=>'int','value'=>1)
        );
        
        $rs->Update('PaymentRate', $a_PaymentRate, 'Id='.$Id);
    }
}

if ($str_Error != ''){
    $_SESSION['Message']['Error'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Error.'</div>';
}
elseif ($str_Warning != ''){
    $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
} else {
    if($ultimate){
        $_SESSION['Message']['Success'] = $str_Success;
    }
}

header("location: ".$P.$str_GET_Parameter.$ConditionalId.$FilterParameter."&Tab=rate");
