<?php
global $cls_pagamenti;
global $instalmentId;

$InstallmentPageParameter = empty($InstallmentPage) ? '' : ("&InstallmentPage=".$InstallmentPage);
$InstallmentPrintPageParameter = empty($InstallmentPageParameter) ? '' : '&InstallmentPage=mgmt_installments_upd.php';
define("RICHIESTA_ACCOLTA","Richiesta accolta");
define("RICHIESTA_RESPINTA","Richiesta respinta");

$str_CSSRate = 'data-toggle="tab"';
$str_Rate = '
        <div class="col-sm-12 BoxRowTitle" >
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                Rateizzazione
            </div>
        </div>
        <div class="clean_row HSpace16"></div>';
$payment_existing = '';
$instalmentScript = "";

if(!empty($instalmentId)){
    $rs_PaymentRate = $rs->Select("PaymentRate", "Id=$instalmentId");
} else {
    $instalmentId = 0;
    $rs_PaymentRate = $rs->Select("PaymentRate", "FineId=$Id AND StatusRateId != ".RATEIZZAZIONE_CHIUSA, "Id DESC");
}

$n_Rate = 0;
if(mysqli_num_rows($rs_PaymentRate)==0){
    if($_SESSION['userlevel']>=3 AND ($r_Fine['StatusTypeId']>13 && $r_Fine['StatusTypeId']<30)) {
        
        $rateReferenceId = null;
        $rateFee = $cls_pagamenti->getFee();
        
        //$r_FineReminder caricato in reminder_data
        if($r_FineReminder){
            //Tipo sollecito
            $rateReferenceId = $r_FineReminder['Id'];
            $rateDocumentTypeId = 9;
        } else {
            //Tipo verbale
            $rateDocumentTypeId = 1;
        }
        
        $str_Rate .= '
            <div class="col-sm-12 table_caption_I text-center">
                DATI RATEIZZAZIONE
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-6 BoxRowLabel">Importo totale</div>
            <div class="col-sm-6 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" type="text" id="total_import" value="'.$rateFee.'">
                <input type="hidden" id="rate_method" value="'.$r_Customer['InstallmentMethod'].'">
            </div>
            <div class="clean_row HSpace4"></div>';
        if($r_Customer['InstallmentMethod'] == cls_installment::TIPOLOGIA_IMP_LEGISLATIVO){
            $str_Rate .= '
            <div class="col-sm-6 BoxRowLabel">Reddito annuale</div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" type="text" id="rate_income">
            </div>
            <div class="col-sm-3 BoxRowLabel">N. Familiari conviventi</div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" id="rate_family_members">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-6 BoxRowLabel">Numero Rate</div>
            <div id="payment_num_field" class="col-sm-6 BoxRowCaption"></div>
            <input class="form-control frm_field_numeric frm_field_required" min="1" type="hidden" id="payment_num">
            <div class="clean_row HSpace4"></div>';
        } else {
            $str_Rate .= '
            <div class="col-sm-6 BoxRowLabel">Numero Rate</div>
            <div class="col-sm-6 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" min="1" type="text" id="payment_num">
            </div>
            <div class="clean_row HSpace4"></div>';
        }
        if($r_Customer['ApplyInstallmentRates'] > 0){
            $str_Rate .= '
            <div class="col-sm-6 BoxRowLabel">Tasso interesse</div>
            <div id="rate_installment_interests_field" class="col-sm-6 BoxRowCaption"></div>
            <input type="hidden" id="rate_installment_interests" value="0">
            <div class="clean_row HSpace4"></div>';
        }
        
        //Controller
        $rs_Controller = $rs->Select("Controller","CityId ='".$_SESSION['cityid']."' AND Disabled = 0","Id ASC");
        
        $str_ControllerOptions = "";
        
        while($r_Controller = $rs->getArrayLine($rs_Controller)){
            $str_ControllerOptions .= '<option value="'.$r_Controller['Id'].'">'.$r_Controller['Qualification']." ".$r_Controller['Name'].'</option>';   
        }
        
        $str_Rate .= '
            <div class="col-sm-6 BoxRowLabel">Data richiesta</div>
            <div class="col-sm-6 BoxRowCaption">
                <input class="form-control frm_field_date frm_field_required" type="text" id="rate_request_date">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-6 BoxRowLabel">Nominativo</div>
            <div class="col-sm-6 BoxRowCaption">
                <select class="form-control" name="payment_nominativo" id="payment_nominativo">
                '.
                $str_ControllerOptions
                .'
                </select>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-6 BoxRowLabel">Posizione</div>
            <div class="col-sm-6 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" type="text" id="payment_position">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-6 BoxRowLabel">Trasgressore</div>
            <div class="col-sm-6 BoxRowCaption">
                '.CreateSelectConcat("SELECT TrespasserId, CONCAT(IFNULL(CompanyName,''),' ',IFNULL(Surname,''),' ',IFNULL(Name,'')) Name FROM V_FineTrespasser WHERE FineId=". $Id ." AND (TrespasserTypeId=1 OR TrespasserTypeId=11) ORDER BY CompanyName, Surname, Name", "trespasser_select", "TrespasserId", "Name", "",false).'
                    
            </div>
            <div class="col-sm-12 table_label_H HSpace4" style="height: 6rem;">
                <button type="button" disabled class="btn btn-success" id="save_rates" style="margin-top:1rem;">Crea</button>
                <input type="hidden" id="rate_documenttypeid" value="'.$rateDocumentTypeId.'">
                <input type="hidden" id="rate_referenceid" value="'.$rateReferenceId.'">
            </div>
            <div class="col-sm-12 table_caption_I text-center">
                PROSPETTO RATE
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-2 table_label_H">N. Rata</div>
            <div class="col-sm-3 table_label_H">Importo</div>
            <div class="col-sm-3 table_label_H">Quota capitale</div>
            <div class="col-sm-2 table_label_H">Interessi</div>
            <div class="col-sm-2 table_label_H">Data scadenza</div>
            <div class="clean_row HSpace4"></div>
            <div id="installment_data_empty" class="col-sm-12 table_caption_H">
                Compilare i dati per poter generare il prospetto
            </div>
            <div id="installment_data_error" class="hidden col-sm-12 table_caption_error">
            </div>
            <div id="installment_data">
            </div>
';
        
    }
}else {
    $payment_rate = mysqli_fetch_array($rs_PaymentRate);
    $payment_trespasser = $rs->getArrayLine($rs->SelectQuery("SELECT * FROM V_Trespasser WHERE Id=".$payment_rate['TrespasserId']));
    $payment_rates = $rs->SelectQuery("SELECT * FROM PaymentRateNumber WHERE PaymentRateId=".$payment_rate['Id']);
    $instalmentId = $payment_rate['Id'];
    //Numero rate
    $n_Rate = $payment_rate['InstalmentNumber'];
    $str_Rate .= '
        <div class="col-sm-12 table_caption_I text-center">
            DATI RATEIZZAZIONE
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">Importo totale</div>
        <div class="col-sm-2 BoxRowCaption">
            € '.number_format($payment_rate['InstalmentAmount'],2,",",".").'
            <input type="hidden" id="total_import" value="'.$payment_rate['InstalmentAmount'].'">
            <input type="hidden" id="rate_method" value="'.$payment_rate['InstallmentMethod'].'">
        </div>
        <div class="col-sm-2 BoxRowLabel">Numero di rate</div>
        <div class="col-sm-2 BoxRowCaption">
            '.$payment_rate['InstalmentNumber'].'
            <input type="hidden" id="payment_num" value="'.$payment_rate['InstalmentNumber'].'">
        </div>
        <div class="col-sm-2 BoxRowLabel">Data richiesta</div>
        <div class="col-sm-2 BoxRowCaption">
            '.DateOutDB($payment_rate['RequestDate']).'
            <input type="hidden" id="rate_request_date" value="'.$payment_rate['RequestDate'].'">
        </div>
        <div class="clean_row HSpace4"></div>';
    
    if($payment_rate['InterestsPercentual'] > 0){
        $str_Rate .= '
        <div class="col-sm-2 BoxRowLabel">Tasso interesse</div>
        <div class="col-sm-2 BoxRowCaption">
            '.number_format($payment_rate['InterestsPercentual'],2,",",".").' %
            <input type="hidden" id="rate_installment_interests" value="'.$payment_rate['InterestsPercentual'].'">
        </div>';
    } else {
        $str_Rate .= '<div class="col-sm-4 BoxRowLabel"></div>';
    }
    if($payment_rate['InstallmentMethod'] == cls_installment::TIPOLOGIA_IMP_LEGISLATIVO){
        $str_Rate .= '
        <div class="col-sm-2 BoxRowLabel">Reddito</div>
        <div class="col-sm-2 BoxRowCaption">
            € '.number_format($payment_rate['Income'],2,",",".").'
            <input type="hidden" id="rate_income" value="'.$payment_rate['Income'].'">
        </div>
        <div class="col-sm-3 BoxRowLabel">N. Familiari conviventi</div>
        <div class="col-sm-1 BoxRowCaption">
            '.$payment_rate['FamilyMembers'].'
            <input type="hidden" id="rate_family_members" value="'.$payment_rate['FamilyMembers'].'">
        </div>
        ';
    } else {
        $str_Rate .= '<div class="col-sm-8 BoxRowLabel"></div>';
    }
    
    $str_Rate .= '
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">Trasgressore</div>
        <div class="col-sm-10 BoxRowCaption">
            ' . utf8_encode($payment_trespasser['CompanyName']) . ' ' . utf8_encode($payment_trespasser['Surname']) . ' '. utf8_encode($payment_trespasser['Name']) . '
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">Nominativo</div>
        <div class="col-sm-4 BoxRowCaption">
            '.$payment_rate['RateName'].'
            <input type="hidden" id="payment_nominativo" value="'.$payment_rate['RateName'].'">
        </div>
        <div class="col-sm-2 BoxRowLabel">Posizione</div>
        <div class="col-sm-4 BoxRowCaption">
            '.$payment_rate['Position'].'
            <input type="hidden" id="payment_position" value="'.$payment_rate['Position'].'">
        </div>
    ';
    
    
    if($payment_rate['RequestStatusId']==0) {
        $str_instalmentRequest = '
            <a style="width:100%;height:100%;color:white" class="btn btn-info" id="instalmentRequest" href="#">
                <i class="fa fa-print"></i> Stampa
            </a>';
        $deleteRequest = '';
    }
    else {
        $instalmentPath = NATIONAL_RATE . "/" . $_SESSION['cityid'] . "/" . $payment_rate['FineId'] . "/" . $payment_rate['Id'] . "/";
        $instalmentWebPath = NATIONAL_RATE_HTML . "/" . $_SESSION['cityid'] . "/" . $payment_rate['FineId'] . "/" . $payment_rate['Id'] . "/";
        $requestFile = $instalmentPath . "Richiesta_Rateizzazione.pdf";
        $webRequestFile = $instalmentWebPath . "Richiesta_Rateizzazione.pdf";
        if (is_file($requestFile)) {
            $str_instalmentRequest = '
                <a id="OpenFileRequest" style="width:100%;height:100%;color:white" class="tooltip-r btn btn-warning OpenFile" data-toggle="tooltip" data-placement="right" title="Richiesta rateizzazione" link="' . $webRequestFile . '">
                    <i class="fa fa-eye"></i> Visualizza
                </a>
            ';
            
            if($payment_rate['ResponseStatusId']<=0 && $payment_rate['BillStatusId']<=0){
                $deleteRequest = '
                <a style="width:100%;height:100%;color:white" class="tooltip-r btn btn-danger" data-toggle="tooltip" data-placement="right" title="Cancella file" id="instalmentBack1" href="#">
                    <i class="fa fa-trash"></i> Elimina
                </a>
             ';
            } else {
                $deleteRequest = '';
            }
            
        } else {
            $deleteRequest = '';
            $str_instalmentRequest = 'File pdf non trovato!';
        }
    }
    
    $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div style="height: 3.5rem; padding: 0px;" class="col-sm-12">
                <div style="height: 3.5rem; padding-top:6px; " class="col-sm-2 BoxRowLabel">RICHIESTA</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$str_instalmentRequest.'</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$deleteRequest.'</div>
                <div style="height: 3.5rem;" class="col-sm-6 BoxRowCaption"></div>
            </div>';
    
    if($payment_rate['RequestStatusId']==1) {
        if($payment_rate['SignedRequestDocumentId'] > 0){
            $r_SignedRequestDoc = $rs->getArrayLine($rs->Select("FineDocumentation", "Id={$payment_rate['SignedRequestDocumentId']}"));
            
            $str_instalmentSignedRequest = '
                <a id="OpenFileRequestSigned" style="width:100%;height:100%;color:white" class="tooltip-r btn btn-warning OpenFile" data-toggle="tooltip" data-placement="right" title="Richiesta rateizzazione" link="' . $instalmentWebPath.$r_SignedRequestDoc['Documentation'] . '">
                    <i class="fa fa-eye"></i> Visualizza
                </a>
            ';
            
            $deleteSignedRequest = '
            <a style="width:100%;height:100%;color:white" class="tooltip-r btn btn-danger" data-toggle="tooltip" data-placement="right" title="Cancella file" id="instalmentBack1_5" href="#">
                <i class="fa fa-trash"></i> Elimina
            </a>';
            
            $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div style="height: 3.5rem; padding: 0px;" class="col-sm-12">
                <div style="height: 3.5rem; padding-top:6px; " class="col-sm-2 BoxRowLabel">RICHIESTA FIRMATA</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$str_instalmentSignedRequest.'</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$deleteSignedRequest.'</div>
                <div style="height: 3.5rem;" class="col-sm-6 BoxRowCaption"></div>
            </div>';
        } else {
            $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div style="height: 3.5rem; padding: 0px;" class="col-sm-12">
                <div style="height: 3.5rem; padding-top:6px; " class="col-sm-2 BoxRowLabel">RICHIESTA FIRMATA</div>
                <div style="height: 3.5rem;" class="col-sm-4 BoxRowFilterButton">
                    <form id="f_signedInstallmentRequest" enctype="multipart/form-data" action="#" method="post">
                    <input type="file" name="signedInstallmentRequest" id="signedInstallmentRequest" style="width: 100%;height: 100%;padding-top:6px;">
                    </form>
                </div>
                <div style="height: 3.5rem;" class="col-sm-6 BoxRowCaption"></div>
            </div>';
        }
    }
    //Per rendere modificabili alcune parti solo per i vigili
    //Responso
    $outcome = (($r_Customer['InstallmentControllerApproval'] == 0) || ($r_Customer['InstallmentControllerApproval'] == 1 && $_SESSION['controllerid'] > 0)) ? 
            CreateArraySelect(array("" => "", 1 => RICHIESTA_ACCOLTA, 0 => RICHIESTA_RESPINTA), true, "request_outcome", "request_outcome", $payment_rate['RequestOutcome'], true) 
            : 
            (translateOutcome($payment_rate['RequestOutcome']).'<input name="request_outcome" id="request_outcome" type="hidden" value="'.$payment_rate['RequestOutcome'].'">');
    //Motivazioni
    $reason = (($r_Customer['InstallmentControllerApproval'] == 0) || ($r_Customer['InstallmentControllerApproval'] == 1 && $_SESSION['controllerid'] > 0)) ?
            '<div class="col-sm-10 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" type="text" id="response_reason" value="'.$payment_rate['ResponseReason'].'" maxlength="100">
            </div>'
            :
            '<div class="col-sm-10 BoxRowCaption">'.
                $payment_rate['ResponseReason']
                .'<input class="form-control frm_field_string frm_field_required" type="hidden" id="response_reason" value="'.$payment_rate['ResponseReason'].'">
            </div>';
    
    if($payment_rate['RequestStatusId']==1 && $payment_rate['ResponseStatusId']==0) {
        $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-2 BoxRowLabel">Esito richiesta</div>
            <div class="col-sm-4 BoxRowCaption">
                '.
                $outcome
                .'
            </div>
            <div class="col-sm-6 BoxRowCaption"></div>
            <div class="clean_row HSpace4"></div>
            <div id="request_outcome_div" class="'.(is_null($payment_rate['RequestOutcome']) ? 'hidden' : '').'">
                <div id="request_outcome_negative_div" class="'.($payment_rate['RequestOutcome'] != 0 ? 'hidden' : '').'">
                    <div class="col-sm-2 BoxRowLabel">Motivazione</div>
                    '.$reason.'
                </div>
                <div id="request_outcome_positive_div" class="'.($payment_rate['RequestOutcome'] != 1 ? 'hidden' : '').'">
                    <div class="col-sm-2 BoxRowLabel">Data decorrenza prima rata</div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" id="rate_start_date" value="'.DateOutDB($payment_rate['StartDate']).'">
                    </div>
                    <div class="col-sm-8 BoxRowCaption"></div>
                </div>
            </div>
            <div class="col-sm-12 table_label_H HSpace4" style="height: 6rem;">
                <button type="button" disabled class="btn btn-success" id="save_rates" style="margin-top:1rem;">Salva</button>
            </div>';
    }
    else if($payment_rate['ResponseStatusId']==1){
        if($payment_rate['RequestOutcome']==1){
            $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-2 BoxRowLabel">Esito richiesta</div>
            <div class="col-sm-4 BoxRowCaption">
                Richiesta accolta
            </div>
            <div class="col-sm-6 BoxRowCaption"></div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-2 BoxRowLabel">Data decorrenza prima rata</div>
            <div class="col-sm-2 BoxRowCaption">
                '.DateOutDB($payment_rate['StartDate']).'
            </div>
            <div class="col-sm-8 BoxRowCaption"></div>
            <div class="clean_row HSpace4"></div>';
        }
        else if($payment_rate['RequestOutcome']==0){
            $str_Rate .= '
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-2 BoxRowLabel">Esito richiesta</div>
                <div class="col-sm-4 BoxRowCaption">
                    Richiesta respinta
                </div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">Motivazione</div>
                <div class="col-sm-10 BoxRowCaption">
                    '.$payment_rate['ResponseReason'].'
                </div>';
            
        }
        
    }
    
    
    if($payment_rate['ResponseStatusId']==0){
        if(mysqli_num_rows($payment_rates) > 0 && $payment_rate['RequestOutcome']==1){
            $str_instalmentResponse = '
            <a style="width:100%;height:100%;color:white" class="btn btn-info" id="instalmentResponse" href="#">
                <i class="fa fa-print"></i> Stampa
            </a>';
        } else {
            $str_instalmentResponse = "";
        }
        
        $deleteResponse = "";
    }
    else {
        $responseFile = $instalmentPath . "Esito_Rateizzazione.pdf";
        $webResponseFile = $instalmentWebPath . "Esito_Rateizzazione.pdf";
        if (is_file($responseFile)) {
            $str_instalmentResponse = '
                <a id="OpenFileResponse" style="width:100%;height:100%;color:white" class="tooltip-r btn btn-warning OpenFile" data-toggle="tooltip" data-placement="right" title="Esito rateizzazione" link="' . $webResponseFile . '">
                    <i class="fa fa-eye"></i> Visualizza
                </a>
            ';
            
            if($payment_rate['BillStatusId']<=0){
                $deleteResponse = '
                <a style="width:100%;height:100%;color:white" class="tooltip-r btn btn-danger" data-toggle="tooltip" data-placement="right" title="Cancella file" id="instalmentBack2" href="#">
                    <i class="fa fa-trash"></i> Elimina
                </a>
             ';
            } else {
                $deleteResponse = '';
            }
        } else {
            $str_instalmentResponse = 'File pdf non trovato!';
            $deleteResponse = '';
        }
    }
    
    $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div style="height: 3.5rem; padding: 0px;" class="col-sm-12">
                <div style="height: 3.5rem; padding-top:6px; " class="col-sm-2 BoxRowLabel">ESITO</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$str_instalmentResponse.'</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$deleteResponse.'</div>
                <div style="height: 3.5rem;" class="col-sm-6 BoxRowCaption"></div>
            </div>';
    
    
    
    if($payment_rate['BillStatusId']==0){
        $deleteBill = '';
        $str_instalmentBill = '
            <a style="width:100%;height:100%;color:white" class="btn btn-info" id="instalmentBill" href="#">
                <i class="fa fa-print"></i> Stampa
            </a>';
        
        if($payment_rate['ResponseStatusId']==0)
            $str_instalmentBill = "";
    }
    else{
        //AVVISI-PAGOPA - INSERIRE DOCUMENTO CON NOTIFICHE PAGAMENTO
        $billFile = $instalmentPath."Bollettini_Rateizzazione.pdf";
        $webBillFile = $instalmentWebPath."Bollettini_Rateizzazione.pdf";
        if(is_file($billFile)) {
            $str_instalmentBill = '
                <a id="OpenFileBill" style="width:100%;height:100%;color:white" class="tooltip-r btn btn-warning OpenFile" data-toggle="tooltip" data-placement="right" title="Bollettini rateizzazione" link="' . $webBillFile . '">
                    <i class="fa fa-eye"></i> Visualizza
                </a>
            ';
            $deleteBill = '
                <a style="width:100%;height:100%;color:white" class="tooltip-r btn btn-danger" data-toggle="tooltip" data-placement="right" title="Cancella file" id="instalmentBack3" href="#">
                    <i class="fa fa-trash"></i> Elimina
                </a>
             ';
        }else{
            $str_instalmentBill = 'File pdf non trovato!';
            $deleteBill = '';
        }
    }
    //AVVISI-PAGOPA - CAMBIARE DESCRIZIONI
    $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div style="height: 3.5rem; padding: 0px;" class="col-sm-12">
                <div style="height: 3.5rem; padding-top:6px; " class="col-sm-2 BoxRowLabel">BOLLETTINI</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$str_instalmentBill.'</div>
                <div style="height: 3.5rem;" class="col-sm-2 BoxRowFilterButton">'.$deleteBill.'</div>
                <div style="height: 3.5rem;" class="col-sm-6 BoxRowCaption"></div>
            </div>';
    
    
    if($payment_rate['ResponseStatusId']==1 && $payment_rate['RequestOutcome']==1){
        $str_Rate .= '            
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-12 table_caption_I text-center">
                PROSPETTO RATE
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-2 table_label_H">N. Rata</div>
            <div class="col-sm-3 table_label_H">Importo</div>
            <div class="col-sm-3 table_label_H">Quota capitale</div>
            <div class="col-sm-2 table_label_H">Interessi</div>
            <div class="col-sm-2 table_label_H">Data scadenza</div>
            <div class="clean_row HSpace4"></div>
            <div id="installment_data">
            ';
            
        while ($payment_rate_number = mysqli_fetch_array($payment_rates)){
            $str_Rate .= '
                <div class="col-sm-2 table_caption_H">'.$payment_rate_number['RateNumber'].'</div>
                <div class="col-sm-3 table_caption_H">€ '.number_format($payment_rate_number['Amount'], 2, ',', '.').'</div>
                <div class="col-sm-3 table_caption_H">€ '.number_format($payment_rate_number['ShareAmount'], 2, ',', '.').'</div>
                <div class="col-sm-2 table_caption_H">€ '.number_format($payment_rate_number['InterestsAmount'], 2, ',', '.').'</div>
                <div class="col-sm-2 table_caption_H">'.DateOutDB($payment_rate_number['PaymentDate']).'</div>
                <div class="clean_row HSpace4"></div>';
        }
        $str_Rate .= '</div>';
    } else {
        $str_Rate .= '
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-12 table_caption_I text-center">
                PROSPETTO RATE
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-2 table_label_H">N. Rata</div>
            <div class="col-sm-3 table_label_H">Importo</div>
            <div class="col-sm-3 table_label_H">Quota capitale</div>
            <div class="col-sm-2 table_label_H">Interessi</div>
            <div class="col-sm-2 table_label_H">Data scadenza</div>
            <div class="clean_row HSpace4"></div>
            <div id="installment_data_empty" class="col-sm-12 table_caption_H">
                Compilare i dati per poter generare il prospetto
            </div>
            <div id="installment_data_error" class="hidden col-sm-12 table_caption_error">
            </div>
            <div id="installment_data">
            </div>';
    }
    
    $str_Rate .= '
    <div class="clean_row HSpace16"></div> 
    <div class="col-sm-12 table_label_H HSpace4" style="height:6rem;">
        <a style="margin-top:1.5rem;color:white;" class="btn btn-danger" id="deleteInstalment" href="#">
            Cancella Rateizzazione
        </a>
    </div>';
}

//PAGE SCRIPTS
$instalmentScript = "
    var rateWindowHeight = 890;	
    var rateWindowMaxHeight = 890;

    function installmentsAjax(data, operation){
        var paymentInfoStatus = ".($InstallmentPageParameter == '' ? $cls_pagamenti->getStatus() : 0).";
        
        if(paymentInfoStatus != -1){
            data.Operation = operation;
        	data.CityId = '".$_SESSION['cityid']."'
        	    
            return $.ajax({
                url: 'ajax/ajx_manageInstallments.php',
                type: 'POST',
                dataType: 'json',
                data: data,
                ContentType: 'application/json; charset=UTF-8'
            });
        }
        else{
            alert('error: non sono disponibili i dati degli importi e dei pagamenti');
            $('#save_rates').prop('disabled', false);
            }
    }

    function callInstallmentsAjax(){
        var metodo = $('#rate_method').val();
        var dovuto = $('#total_import').val();
        var dataInizio = $('#rate_start_date').val();
        var dataRichiesta = $('#rate_request_date').val();
        var numeroRate = $('#payment_num').val();
        var reddito = $('#rate_income').val();
        var numeroFamiliari = $('#rate_family_members').val();
        var payment_nominativo = $('#payment_nominativo').val();
        var payment_position = $('#payment_position').val();
        var tres_id = $('#trespasser_select').val();
        
        //fine_id è dichiarata in mgmt_fine_upd
        var dati = {FineId: fine_id, Metodo: metodo, Dovuto: dovuto, DataInizio: dataInizio, DataRichiesta:dataRichiesta, Rate: numeroRate, Reddito: reddito, Familiari: numeroFamiliari};
            
        //console.log(metodo, dovuto, dataInizio, dataRichiesta, numeroRate, reddito, numeroFamiliari, payment_nominativo, payment_position, tres_id);

        if(metodo != '' && dovuto > 0 && dataRichiesta != '' && payment_nominativo != '' && payment_position != '' && tres_id != '' && ((reddito > 0 && numeroFamiliari != '') || numeroRate > 0)){
    		$.when(installmentsAjax(dati, 'nuovaRata')).done(function (data) {
                showResults(data);
    		}).fail(function(data){
    			alert('Errore: '+data.responseText);
    			console.log(data);
    		});
        } else {
            $('#save_rates').prop('disabled', true);
            $('#installment_data_error').addClass('hidden').text('');
        }
    }
    	    
	function showResults(data){
        //console.log('Show results: '+JSON.stringify(data));
		$('#installment_data_empty').addClass('hidden');
        if(data.InstallmentsNumber > 0){
            $('#payment_num').val(data.InstallmentsNumber);
            $('#payment_num_field').text(data.InstallmentsNumber);
            if(data.InstallmentsNumber > 17)
                $('#installment_data').css('overflow-y','scroll');
            else
                $('#installment_data').css('overflow-y','hidden');
        }
        $('#rate_installment_interests').val(data.InterestTax);
        $('#rate_installment_interests_field').text(data.InterestTax > 0 ? formatValue(data.InterestTax, 'decimal')+' %' : '');
		$('#installment_data').html('');
    	    
		if(!data.Success){
            data.Messages[0] = data.Messages[0].charAt(0).toUpperCase()+data.Messages[0].slice(1);  //Imposta la prima lettera del primo errore in maiuscolo
			$('#installment_data_error').removeClass('hidden').text(data.Messages);
            $('#save_rates').prop('disabled', true);
            $('#instalmentResponse').addClass('hidden');
		} else {
            $('#installment_data_error').addClass('hidden').text('');
            $.each(data.Data, function( index, result ) {
                var tableRow = $('<div>', {class: 'tableRow'});
                tableRow.append(buildElement(2, '', index));
                tableRow.append(buildElement(3, 'rate_'+index, '€ '+formatValue(result.ImportoRata, 'decimal')).append($('<input>',{type: 'hidden', name : 'rate['+index+']'})));
                tableRow.append(buildElement(3, '', '€ '+formatValue(result.QuotaCapitale, 'decimal')));
                tableRow.append(buildElement(2, '', '€ '+formatValue(result.Interessi, 'decimal')));
                tableRow.append(buildElement(2, '', formatValue(result.DataScadenza, 'date')));
                tableRow.append($('<div>', {class: 'clean_row HSpace4'}));
                tableRow.appendTo('#installment_data');
            });
            $('#installment_data').scrollTop(0);
            $('#save_rates').prop('disabled', false);
            $('#instalmentResponse').removeClass('hidden');
        }
	}
    	    
	function buildElement(size, name, value){
		return $('<div>', {
            class: 'table_caption_H col-sm-'+size,
            text: value,
            ...(name != '') && {name: name}
        });
	}
    	    
	function formatValue(value, type){
        if(value == null){
            return '';
        } else {
            switch(type){
                case 'decimal': return value.toLocaleString('it-IT', {maximumFractionDigits: 2, minimumFractionDigits: 2});
                case 'date': {
                    value = new Date(value);
                    return value.toLocaleDateString('it-IT', {year: 'numeric', month: '2-digit', day: '2-digit'});
                }
            }
        }
	}
    	    
    $(document).ready(() => {
        var responseStatusId = ".($payment_rate['ResponseStatusId'] ?? -1).";
        var requestOutcome = ".($payment_rate['RequestOutcome'] ?? -1).";
        
        if(responseStatusId!=1){
            callInstallmentsAjax();
            }

        $('#preview_img').css('height',rateWindowHeight);
        $('#preview_section').css('height',rateWindowHeight);
    });
    var rate_href = 'mgmt_instalment.php".$str_GET_Parameter."&payment_rate_id=".$instalmentId.$InstallmentPageParameter."';
        
    $('#instalmentRequest').click(function () {
        window.location = rate_href + '&Id='+ fine_id +'&instalmentType=1';
    });
        
    $('#instalmentResponse').click(function () {
        if($('#rate_start_date').val() != ''){
            window.location = rate_href + '&Id='+ fine_id +'&instalmentType=2';
        } else {
            alert('È necessario specificare la Data decorrenza prima rata per poter stampare l\'esito.');
            return false;
        }
    });

    //AVVISI-PAGOPA - CHIAMATA A prn_rate_bill
    $('#instalmentBill').click(function () {
        window.location = 'prn_rate_bill.php".$str_GET_Parameter."&payment_rate_id=".$instalmentId.$InstallmentPrintPageParameter."';
    });
            
    $('#instalmentBack1').click(function () {
        window.location = rate_href + '&Id='+ fine_id +'&instalmentBackState=1".$InstallmentPrintPageParameter."';
    });

    $('#instalmentBack1_5').click(function () {
        window.location = rate_href + '&Id='+ fine_id +'&instalmentBackState=1_5".$InstallmentPrintPageParameter."';
    });
            
    $('#instalmentBack2').click(function () {
        window.location = rate_href + '&Id='+ fine_id +'&instalmentBackState=2".$InstallmentPrintPageParameter."';
    });
            
    $('#instalmentBack3').click(function () {
        window.location = rate_href + '&Id='+ fine_id +'&instalmentBackState=3".$InstallmentPrintPageParameter."';
    });
            
    $('#deleteInstalment').click(function () {
        window.location = rate_href + '&Id='+ fine_id +'&instalmentType=3';
    });
            
    $(document).on('change', '#total_import, #rate_income, #rate_request_date, #rate_family_members, #rate_start_date, #payment_num, #trespasser_select, #payment_nominativo, #payment_position', function () {
        callInstallmentsAjax();
    });
            
    $(document).on('change', '#request_outcome', function () {
        var request_outcome = parseInt($('#request_outcome').val());
            
        if(request_outcome===0){
            $('#request_outcome_div').removeClass('hidden');
            $('#request_outcome_negative_div').removeClass('hidden');
            $('#request_outcome_positive_div').addClass('hidden');
            $('#rate_start_date').val('').change();
        }
        else if(request_outcome===1){
            $('#request_outcome_div').removeClass('hidden');
            $('#request_outcome_negative_div').addClass('hidden');
            $('#request_outcome_positive_div').removeClass('hidden');
            $('#response_reason').val('');
        } else {
            $('#request_outcome_div').addClass('hidden');
            $('#request_outcome_negative_div').addClass('hidden');
            $('#request_outcome_positive_div').addClass('hidden');
            $('#rate_start_date').val('').change();
            $('#response_reason').val('');
        }
    });

    $(document).on('change', '#signedInstallmentRequest', function () {
        var formdata = new FormData($('#f_signedInstallmentRequest')[0]);
        //fine_id è dichiarata in mgmt_fine_upd
        formdata.append('installment_id', '".$instalmentId."');
        formdata.append('CityId', '".$_SESSION['cityid']."');
        formdata.append('fine_id', fine_id);
        formdata.append('Operation', 'uploadDoc');

        $.ajax({
            url: 'ajax/ajx_create_rate.php',
            dataType: 'JSON',
            cache: false,
            contentType: false,
            processData: false,
            data: formdata,                         
            type: 'POST',
            success: function(data){
        		if(!data.Success){
        			alert(data.Messages);
                    $('#signedInstallmentRequest').val('');
        		} else {
                    alert('Salvataggio effettuato con successo');
                    location.reload();
                }
            },
            error: function (data) {
                console.log(data);
                alert('error: ' + data.responseText);
            }
        });
    });

    $(document).on('click', '#save_rates', function () {
        var paymentInfoStatus = ".($InstallmentPageParameter == '' ? $cls_pagamenti->getStatus() : 0).";
        
        if(paymentInfoStatus != -1){
            $('#save_rates').prop('disabled', true);
            
            var tres_id = $('#trespasser_select').val();
            var rate_num = $('#payment_num').val();
            var total_payment = $('#total_import').val();
            var payment_nominativo = $('#payment_nominativo').val();
            var payment_position = $('#payment_position').val();
            var request_outcome = $('#request_outcome').val();
            var response_reason = $('#response_reason').val();
            var documenttypeid = $('#rate_documenttypeid').val();
            var ratereferenceid = $('#rate_referenceid').val();
            var reddito = $('#rate_income').val();
            var numeroFamiliari = $('#rate_family_members').val();
            var installmentMethod = $('#rate_method').val();
            var interestsPercentual = $('#rate_installment_interests').val() != null ? $('#rate_installment_interests').val() : 0;
            var dataRichiesta = $('#rate_request_date').val();
            var start_date = $('#rate_start_date').val();
            var installment_id_txt = typeof installment_id != 'undefined' ? ('&installment_id='+installment_id) : '';
    
            var data = {CityId: '".$_SESSION['cityid']."', Operation: 'update'};
    
             $.ajax({
                url: 'ajax/ajx_create_rate.php?fine_id='+fine_id+'&rate_num='+rate_num+'&start_date='+start_date+'&total_payment='+total_payment+'&tres_id='+
                tres_id+'&payment_nominativo='+payment_nominativo+'&payment_position='+payment_position+'&instalment_amount='+total_payment+'&request_outcome='+request_outcome+'&rate_documenttypeid='+documenttypeid+'&rate_referenceid='+ratereferenceid+
                '&income='+reddito+'&family_members='+numeroFamiliari
                +'&response_reason='+response_reason+'&installment_method='+installmentMethod+'&interests_percentual='+interestsPercentual+'&request_date='+dataRichiesta+installment_id_txt,
                type: 'POST',
                dataType: 'json',
                ContentType: 'application/json; charset=UTF-8',
                cache: false,
                data: data,
                success: function (data) {
            		if(!data.Success){
            			alert(data.Messages);
                        $('#save_rates').prop('disabled', false);
            		} else {
                        alert('Salvataggio effettuato con successo');
                        $('#save_rates').prop('disabled', true);
                        location.reload();
                    }
                },
                error: function (data) {
                    console.log(data);
                    alert('error: ' + data.responseText);
                    $('#save_rates').prop('disabled', false);
                }
            });
        }
        else{
            alert('error: non sono disponibili i dati degli importi e dei pagamenti');
            $('#save_rates').prop('disabled', false);
            }
    });
   
   $('#installment_data').height(400);
    
   var searchParams = new URLSearchParams(window.location.search)

   var requestFile = '".($requestFile ?? '')."';
   var webRequestFile = '".($webRequestFile ?? '')."';
   var webSignedRequestFile = '';
   var instalmentPath = '".($instalmentPath ?? '')."';
   var instalmentWebPath = '".($instalmentWebPath ?? '')."';
   var signedRequestDoc = '".($r_SignedRequestDoc['Documentation'] ?? '')."'
   
   if(signedRequestDoc != ''){
     webSignedRequestFile = instalmentWebPath+signedRequestDoc;
    }
    
    var file = '';
    if(webSignedRequestFile!='')
        file = webSignedRequestFile;
    else if(requestFile != '')
        file = webRequestFile;

   //Per caricare l'anteprima al clic sul tab rateizzazioni
   $('#RateSection').on('click',()=>{
            showInstallmentRequestImage(file);
   		});
   //Per caricare l'anteprima al caricamento/ricaricamento della pagina
   if(searchParams.get('Tab') == 'rate'){
            showInstallmentRequestImage(file);
    }
   
   //Funzione per mostrare il pdf della richiesta di rateizzazione
   function showInstallmentRequestImage(file){
        if(file != ''){
            var FileName = file.split('/').pop();
            $('#preview_doc').hide();
            $('#preview').hide();
            $('#preview_iframe_img').attr('src',file);
            $('#preview_iframe_img').css('width','100%');
            $('#preview_iframe_img').show();
            $('#preview_img').css('height',rateWindowHeight);
            $('#preview_section').css('height',rateWindowHeight);
            $('#preview_img').show();
            $('#FileTreeBox').hide();
            }
        else{
        $('#preview_section').css('height',663);
        }
    }

    $('#OpenFileRequest').on('click',()=>{
        var link = $('#OpenFileRequest').attr('link');
        $('#preview_iframe_img').attr('src',link);
    });
    $('#OpenFileRequestSigned').on('click',()=>{
        var link = $('#OpenFileRequestSigned').attr('link');
        $('#preview_iframe_img').attr('src',link);
    });
    $('#OpenFileResponse').on('click',()=>{
        var link = $('#OpenFileResponse').attr('link');
        $('#preview_iframe_img').attr('src',link);
    });
    $('#OpenFileBill').on('click',()=>{
        var link = $('#OpenFileBill').attr('link');
        $('#preview_iframe_img').attr('src',link);
    });

    function openDocumentPreview(file){
        $('#preview_iframe_img').attr('src',file);
    }
    $('#preview_section').css('max-height',rateWindowMaxHeight);
    $('#preview_iframe_img').css('max-height',rateWindowMaxHeight);
    $('#backPanel').css('max-height',rateWindowMaxHeight);
    $('#preview_iframe_img').css('height',rateWindowHeight);
    $('#backPanel').css('height',rateWindowHeight);
    //Abilita lo scroll solo per un numero di rate superiore a 17
    if('".$n_Rate."' > 17)
        $('#installment_data').css('overflow-y','scroll');
    else
        $('#installment_data').css('overflow-y','hidden');
";

//Imposta la visualizzazione per il dettaglio verbale
$str_Rate_data = '
    <div class="tab-pane" id="Rate">
        <div id="rateWindow" class="col-sm-12">
            '.$str_Rate.'
        </div>
    </div>
                
';

//Imposta la visualizzazione per il punto menu dedicato
$str_Rate_data_menu = '
    <div class="tab-pane" id="Rate">
        <div id="rateWindow" class="col-sm-6">
            '.$str_Rate.'
        </div>
    </div>
                
';


function translateOutcome($outcomeCode)
{
    $outcome = '';
    switch($outcomeCode){
        case '': $outcome = ''; break;
        case 0: $outcome = RICHIESTA_RESPINTA; break;
        case 1: $outcome = RICHIESTA_ACCOLTA; break;
    }
    
    return $outcome;
}