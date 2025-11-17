<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_file_upload.php");
require_once(CLS."/cls_installment.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

$success = true;
$a_Messages = array();

$Operation = CheckValue("Operation", 's');

if($Operation == 'update'){
    $fine_id = $_REQUEST['fine_id'];
    $tres_id = $_REQUEST['tres_id'];
    $rate_num = $_REQUEST['rate_num'];
    $instalment_amount = $_REQUEST['instalment_amount'];
    $documenttypeid = $_REQUEST['rate_documenttypeid'];
    $ratereferenceid = $_REQUEST['rate_referenceid'];
    
    //Gestire il recupero del paymentrateid che, se passato recupera per id sennò per FineId come sotto
    $installmentid = $_REQUEST['installment_id'] ?? null;
    
    if(empty($installmentid)){
        $a_instalment = $rs->getArrayLine($rs->Select("PaymentRate", "FineId=$fine_id AND StatusRateId != ".RATEIZZAZIONE_CHIUSA));
        }
    else{
        $a_instalment = $rs->getArrayLine($rs->Select("PaymentRate", "Id=$installmentid AND StatusRateId != ".RATEIZZAZIONE_CHIUSA));
        }
    
    if($a_instalment==null) {
        $income = $_REQUEST['income'];
        $family_members = $_REQUEST['family_members'];
        $installment_method = $_REQUEST['installment_method'];
        $interests_percentual = $_REQUEST['interests_percentual'];
        $request_date = $_REQUEST['request_date'];
        
        $rs_Controller = $rs->Select("Controller","CityId = '".$_SESSION['cityid']."' AND Disabled = 0 AND Id = ".$_REQUEST['payment_nominativo']);
        $r_Controller = $rs->getArrayLine($rs_Controller);
        
        $RateName = $r_Controller['Qualification']." ".$r_Controller['Name'];
        
        $PaymentRate = array(
            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fine_id, 'settype' => 'int'),
            array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $tres_id, 'settype' => 'int'),
            array('field' => 'StatusRateId', 'selector' => 'value', 'type' => 'int', 'value' => RATEIZZAZIONE_APERTA, 'settype' => 'int'),
            array('field' => 'DocumentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $documenttypeid, 'settype' => 'int'),
            array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d')),
            array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date('H:i')),
            array('field' => 'UserId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['userid'], 'settype' => 'int'),
            array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => ''),
            array('field' => 'RateName', 'selector' => 'value', 'type' => 'str', 'value' => $RateName),
            array('field' => 'Position', 'selector' => 'value', 'type' => 'str', 'value' => $_REQUEST['payment_position']),
            array('field' => 'InstalmentNumber', 'selector' => 'value', 'type' => 'int', 'value' => $rate_num, 'settype' => 'int'),
            array('field' => 'ReferenceId', 'selector' => 'value', 'type' => 'int', 'value' => $ratereferenceid, 'settype' => 'int'),
            array('field' => 'InstalmentAmount', 'selector' => 'value', 'type' => 'flt', 'value' => $instalment_amount, 'settype' => 'flt'),
            array('field' => 'Income', 'selector' => 'value', 'type' => 'flt', 'value' => $income, 'settype' => 'flt'),
            array('field' => 'FamilyMembers', 'selector' => 'value', 'type' => 'int', 'value' => $family_members, 'settype' => 'int'),
            array('field' => 'InterestsPercentual', 'selector' => 'value', 'type' => 'flt', 'value' => $interests_percentual, 'settype' => 'flt'),
            array('field' => 'InstallmentMethod', 'selector' => 'value', 'type' => 'int', 'value' => $installment_method, 'settype' => 'int'),
            array('field' => 'RequestDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($request_date)),
            array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'str', 'value' => $_REQUEST['payment_nominativo']),
        );
        
        $rs->Insert('PaymentRate', $PaymentRate);
    }
    else{
        $request_outcome = $_REQUEST['request_outcome'];
        $response_reason = $_REQUEST['response_reason'];
        if($response_reason=="undefined"){
            $response_reason = "";
        }
        if($request_outcome===""){
            $request_outcome = null;
        }
        elseif($request_outcome==1){
            if(!empty($_REQUEST['start_date'])){
                $start_date = DateInDB($_REQUEST['start_date']);
                $installment_method = $_REQUEST['installment_method'];
                $interests_percentual = $_REQUEST['interests_percentual'];
                $CityId = CheckValue("CityId", 's');
                
                $cls_installment = new cls_installment($CityId, $installment_method, $start_date, $interests_percentual);
                
                $a_RateMaxImporti = array(
                    $r_Customer['InstallmentRateLimit1'] => $r_Customer['InstallmentFeeLimit1'],
                    $r_Customer['InstallmentRateLimit2'] => $r_Customer['InstallmentFeeLimit2'],
                    $r_Customer['InstallmentRateLimit3'] => $r_Customer['InstallmentFeeLimit3'],
                );
                
                $cls_installment->calcolaRate($instalment_amount, $rate_num, $r_Customer['InstallmentRatesMinimumAmount'], $a_RateMaxImporti);
                $a_Installments = $cls_installment->rate;
                
                $rs_PaymentRateNumber = $rs->Select("PaymentRateNumber", "PaymentRateId={$a_instalment['Id']}");
                
                if(mysqli_num_rows($rs_PaymentRateNumber) > 0){
                    $rs->Delete("PaymentRateNumber", "PaymentRateId={$a_instalment['Id']}");
                }
                
                foreach($a_Installments as $rateNumber => $installment){
                    $PaymentRateNumber = array(
                        array('field'=>'PaymentRateId','selector'=>'value','type'=>'int','value'=>$a_instalment['Id'],'settype'=>'int'),
                        array('field'=>'RateNumber','selector'=>'value','type'=>'int','value'=>$rateNumber,'settype'=>'int'),
                        array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$installment[cls_installment::INDICE_IMP_RATA],'settype'=>'flt'),
                        array('field'=>'ShareAmount','selector'=>'value','type'=>'flt','value'=>$installment[cls_installment::INDICE_QUOTA_CAP],'settype'=>'flt'),
                        array('field'=>'InterestsAmount','selector'=>'value','type'=>'flt','value'=>$installment[cls_installment::INDICE_ITERESSI],'settype'=>'flt'),
                        array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$installment[cls_installment::INDICE_DATA_SCAD_RATA]),
                        array('field'=>'Note','selector'=>'value','type'=>'str','value'=>''),
                        array('field'=>'FifthField','selector'=>'value','type'=>'str','value'=>''),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>''),
                    );
                    $rs->Insert('PaymentRateNumber', $PaymentRateNumber);
                }
            } else {
                $rs->Delete("PaymentRateNumber","PaymentRateId=".$a_instalment['Id']);
            }
        }
        else if($request_outcome==0){
            $rs->Delete("PaymentRateNumber","PaymentRateId=".$a_instalment['Id']);
        }
        
        $PaymentRate = array(
            array('field' => 'StartDate', 'selector' => 'value', 'type' => 'date', 'value' => $start_date),
            array('field' => 'RequestOutcome', 'selector' => 'value', 'type' => 'int', 'value' => $request_outcome, 'settype' => 'int', 'nullable' => true),
            array('field' => 'ResponseReason', 'selector' => 'value', 'type' => 'str', 'value' => $response_reason, 'settype' => 'str'),
        );
        
        $rs->Update('PaymentRate', $PaymentRate,'Id='.$a_instalment['Id']);
    }
} else if($Operation == 'uploadDoc'){
    $fine_id = CheckValue('fine_id', 'n');
    $installment_id = CheckValue('installment_id', 'n');
    $CityId = CheckValue("CityId", 's');
    
    $imgUploader = new cls_file_upload();
    $a_AllowedExtensions = array('.pdf' => 'application/pdf');
    $n_MaxFileSize = ART126_DOCUMENT_MAX_FILE_SIZE;
    
    if($CityId != '' && $fine_id > 0 && $installment_id > 0){
        foreach($_FILES as $file){
            if($imgUploader->checkUploadErrors($file['error'])){
                $DocumentName = basename($file['name']);
                $DocumentationTypeId = 36;
                
                $str_FineFolder = NATIONAL_RATE . "/" . $CityId . "/" . $fine_id;
                $str_InstallmentFolder = $str_FineFolder . "/" . $installment_id;
                
                $imgUploader->setPrintError(false);
                $imgUploader->setMaxSize($n_MaxFileSize);
                $imgUploader->setDestination($str_InstallmentFolder."/");
                $imgUploader->setAllowedExtensions($a_AllowedExtensions);
                $imgUploader->setFileName($DocumentName);
                $imgUploader->setMaxSize($n_MaxFileSize);
                
                $imgUploader->validate($file);
                
                $validated_name = $imgUploader->getFileName($DocumentName);
                
                if (!is_dir($str_InstallmentFolder)) {
                    mkdir($str_InstallmentFolder, 0770, true);
                    chmod($str_FineFolder, 0770);
                    chmod($str_InstallmentFolder, 0770);
                }
                
                if(file_exists($str_InstallmentFolder."/".$validated_name)) {
                    $a_Messages[] = 'Il documento caricato è già presente.';
                    $success = false;
                } else {
                    $imgUploader->upload($file);
                    
                    if(!$imgUploader->error){
                        
                        $a_FineDocumentation = array(
                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$fine_id,'settype'=>'int'),
                            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$validated_name),
                            array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                            array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s")),
                            array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                        );
                        $DocumentationId = $rs->Insert('FineDocumentation',$a_FineDocumentation);
                        
                        $a_PaymentRate = array(
                            array('field' => 'SignedRequestDocumentId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationId, 'settype' => 'int'),
                        );
                        
                        $rs->Update('PaymentRate', $a_PaymentRate,"Id=$installment_id");
                    } else {
                        $a_Messages[] = "Errore nel caricamento del documento: ".$imgUploader->error;
                        $success = false;
                    }
                }
            } else {
                $a_Messages[] = "Errore nel caricamento del documento: ".$imgUploader->error;
                $success = false;
            }
        }
    } else {
        $a_Messages[] = "Errore nel caricamento del documento.";
        $success = false;
    }
}

echo json_encode(
    array(
        "Messages" => $a_Messages,
        "Success" => $success,
    )
);

