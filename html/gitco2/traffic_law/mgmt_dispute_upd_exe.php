<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$DisputeId = CheckValue('DisputeId','n');
$FineId = CheckValue('FineId','n');
$GradeTypeId = CheckValue('GradeTypeId','n');
$OwnerPresentation = CheckValue('OwnerPresentation','n');

$rgNumber = CheckValue('RGNumber','s');

//Dispute Date (Multidim array)
$a_PostDisputeDate = array();
foreach($_REQUEST['DisputeHearingId'] as $key=>$id){
    if($_REQUEST['TimeHearing'][$key]=="")
        $_REQUEST['TimeHearing'][$key] = null;
    $a_PostDisputeDate[] = array(
        'row'=>
            array(
                'Id'=>(int)$_REQUEST['DisputeHearingId'][$key],
                'DisputeResultId'=>(int)$_REQUEST['DisputeResultId'][$key],
                'DateAction' => DateInDB($_REQUEST['DateAction'][$key]),
                'DateHearing' => DateInDB($_REQUEST['DateHearing'][$key]),
                'LosingParty' => (int)$_REQUEST['LosingParty'][$key],
                'Parcel' => (int)$_REQUEST['Parcel'][$key]
            ),
        array('field'=>'DateHearing', 'selector'=>'value', 'value' => DateInDB($_REQUEST['DateHearing'][$key]), 'type'=>'str'),
        array('field'=>'TimeHearing', 'selector'=>'value', 'value' => $_REQUEST['TimeHearing'][$key], 'type'=>'str'),
        array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$DisputeId),
        array('field'=>'GradeTypeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$GradeTypeId),
        array('field'=>'TypeHearing', 'selector'=>'value', 'value' => $_REQUEST['TypeHearing'][$key], 'type'=>'str'),
        array('field'=>'DisputeActId', 'selector'=>'value', 'value' => (int)$_REQUEST['DisputeActId'][$key], 'type'=>'int'),
        array('field'=>'DisputeResultId', 'selector'=>'value', 'value' => (int)$_REQUEST['DisputeResultId'][$key], 'type'=>'int'),
        array('field'=>'Note', 'selector'=>'value', 'value' => $_REQUEST['Note'][$key], 'type'=>'str'),
        array('field'=>'Number', 'selector'=>'value', 'value' => $_REQUEST['Number'][$key], 'type'=>'str'),
        array('field'=>'DateMeasure', 'selector'=>'value', 'value' => DateInDB($_REQUEST['HearingDateMeasure'][$key]), 'type'=>'str'),
        array('field'=>'DateAction', 'selector'=>'value', 'value' => DateInDB($_REQUEST['DateAction'][$key]), 'type'=>'str'),
        array('field'=>'DateNotification', 'selector'=>'value', 'value' => DateInDB($_REQUEST['DateNotification'][$key]), 'type'=>'str'),
        array('field'=>'LosingParty', 'selector'=>'value', 'value' => (int)$_REQUEST['LosingParty'][$key], 'type'=>'int'),
        array('field'=>'Parcel', 'selector'=>'value', 'value' => (int)$_REQUEST['Parcel'][$key], 'type'=>'int')
    );

    if($_REQUEST['LosingParty'][$key]==1){
        $a_PostLosingParty[] = array(
            'row'=>
                array(
                    'Id'=>(int)$_REQUEST['LosingPartyId'][$key]
                ),
            array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$DisputeId),
            array('field'=>'DisputeDateId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$_REQUEST['DisputeHearingId'][$key]),
            array('field'=>'Type', 'selector'=>'value', 'value' => 1, 'type'=>'int'),
            array('field'=>'Side', 'selector'=>'value', 'value' => (int)$_REQUEST['LosingSide'][$key], 'type'=>'int'),
            array('field'=>'PaymentDate', 'selector'=>'value', 'value' => DateInDB($_REQUEST['LosingPaymentDate'][$key]), 'type'=>'str'),
            array('field'=>'Payer', 'selector'=>'value', 'value' => $_REQUEST['LosingPayer'][$key], 'type'=>'str')
        );
    }

    if($_REQUEST['Parcel'][$key]==1){
        $a_PostParcel[] = array(
            'row'=>
                array(
                    'Id'=>(int)$_REQUEST['ParcelId'][$key],
                ),
            array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$DisputeId),
            array('field'=>'DisputeDateId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$_REQUEST['DisputeHearingId'][$key]),
            array('field'=>'Type', 'selector'=>'value', 'value' => 2, 'type'=>'int'),
            array('field'=>'Side', 'selector'=>'value', 'value' => (int)$_REQUEST['ParcelSide'][$key], 'type'=>'int'),
            array('field'=>'PaymentDate', 'selector'=>'value', 'value' => DateInDB($_REQUEST['ParcelPaymentDate'][$key]), 'type'=>'str'),
            array('field'=>'Payer', 'selector'=>'value', 'value' => $_REQUEST['ParcelPayer'][$key], 'type'=>'str'),
            array('field'=>'Number', 'selector'=>'value', 'value' => $_REQUEST['ParcelNumber'][$key], 'type'=>'str'),
            array('field'=>'Date', 'selector'=>'value', 'value' => DateInDB($_REQUEST['ParcelDate'][$key]), 'type'=>'str'),
            array('field'=>'Lawyer', 'selector'=>'value', 'value' => $_REQUEST['ParcelLawyer'][$key], 'type'=>'str'),
            array('field'=>'Notes', 'selector'=>'value', 'value' => $_REQUEST['ParcelNotes'][$key], 'type'=>'str'),
            array('field'=>'Fee', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelFee'][$key], 'type'=>'flt'),
            array('field'=>'OtherExpenses', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelOtherExpenses'][$key], 'type'=>'flt'),
            array('field'=>'Overheads', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelOverheads'][$key], 'type'=>'flt'),
            array('field'=>'LawyerFund', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelLawyerFund'][$key], 'type'=>'flt'),
            array('field'=>'VAT', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelVAT'][$key], 'type'=>'flt'),
            array('field'=>'VATExemption', 'selector'=>'value', 'value' => (int)$_REQUEST['ParcelVATExemption'][$key], 'type'=>'int'),
            array('field'=>'CU', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelCU'][$key], 'type'=>'flt'),
            array('field'=>'RevenueStamp', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelRevenueStamp'][$key], 'type'=>'flt'),
            array('field'=>'OtherLivingExpenses', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelOtherCosts'][$key], 'type'=>'flt'),
            array('field'=>'RA', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelRA'][$key], 'type'=>'flt'),
            array('field'=>'RAExemption', 'selector'=>'value', 'value' => (int)$_REQUEST['ParcelRAExemption'][$key], 'type'=>'int'),
            array('field'=>'Total', 'selector'=>'value', 'value' => (float)$_REQUEST['ParcelTotal'][$key], 'type'=>'flt'),
        );
    }
}

//Dispute Amounts (Multidim array)
$a_PostDisputeAmount = array();
foreach($_REQUEST['DisputeAmountId'] as $key=>$id){
    $a_PostDisputeAmount[] = array(
        'row'=>
            array(
                'Id'=>(int)$_REQUEST['DisputeAmountId'][$key]
            ),
        array('field'=>'Amount', 'selector'=>'value', 'value' => $_REQUEST['DisputeAmount'][$key], 'type'=>'flt', 'settype'=>'flt'),
        array('field'=>'Note', 'selector'=>'value', 'value' => $_REQUEST['DisputeAmountNotes'][$key], 'type'=>'str'),
        array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$DisputeId),
        array('field'=>'DisputeDateId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$_REQUEST['LastDisputeDateId']),
        array('field'=>'GradeTypeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$GradeTypeId),
    );
}

//Fine Dispute Amounts (Multidim array)
$a_PostFineDisputeAmount = array();
foreach($_REQUEST['FDADisputeAmountId'] as $key=>$id){
    if(count($_REQUEST['FDADisputeAmountId'])==1 && count($a_PostDisputeAmount)==1){
        if((float)$_REQUEST['DisputeAmount'][1]>0 && (int)$_REQUEST['FDADisputeAmountId'][$key]==0){
            $_REQUEST['FDADisputeAmountId'][$key] = (int)$_REQUEST['DisputeAmountId'][1];
        }
    }

    $a_PostFineDisputeAmount[] = array(
        'row'=>
            array(
                'FineId'=>(int)$_REQUEST['FDAFineId'][$key],
                'DisputeDateId'=>(int)$_REQUEST['LastDisputeDateId']
            ),
        array('field'=>'FineId', 'selector'=>'value', 'value' => (int)$_REQUEST['FDAFineId'][$key], 'type'=>'int'),
        array('field'=>'DisputeAmountId', 'selector'=>'value', 'value' => (int)$_REQUEST['FDADisputeAmountId'][$key], 'type'=>'int'),
        array('field'=>'DisputeId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$DisputeId),
        array('field'=>'DisputeDateId', 'selector'=>'value', 'type'=>'int', 'value'=>(int)$_REQUEST['LastDisputeDateId']),
    );
}

$AddGrade  = CheckValue('AddGrade','n');

$rs->Start_Transaction();

if($AddGrade==1){
    $a_Dispute = array(
        array('field'=>'Id', 'selector'=>'value', 'type'=>'int', 'value'=>$DisputeId,'settype'=>'int'),
        array('field'=>'OwnerPresentation', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
        array('field'=>'GradeTypeId', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
        array('field'=>'OfficeId','selector'=>'field', 'type'=>'int', 'settype'=>'int'),
        array('field'=>'OfficeCity','selector'=>'field', 'type'=>'str'),
        array('field'=>'RegDate', 'selector'=>'value','type'=>'date', 'value'=>date("Y-m-d")),
        array('field'=>'RegTime', 'selector'=>'value','type'=>'str', 'value'=>date("H:i")),
        array('field'=>'UserId', 'selector'=>'value', 'type'=>'str', 'value'=>$_SESSION['username'])
    );

    $rs->Insert('Dispute',$a_Dispute);

    $a_FineDispute = array(
        array('field'=>'DisputeStatusId', 'selector'=>'value', 'type'=>'int', 'value'=>1, 'settype'=>'int'),
    );
    $rs->Update('FineDispute',$a_FineDispute,"DisputeId=".$DisputeId);

} else {

    $a_Dispute = array(
        array('field'=>'OwnerPresentation', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
        array('field'=>'ProtocolNumber', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'DateProtocol', 'selector'=>'field', 'type'=>'date'),
        array('field'=>'DateReceive', 'selector'=>'field', 'type'=>'date'),
        array('field'=>'DateSend', 'selector'=>'field', 'type'=>'date'),
        array('field'=>'DateFile', 'selector'=>'field', 'type'=>'date'),
        array('field'=>'OfficeId','selector'=>'field', 'type'=>'int', 'settype'=>'int'),
        array('field'=>'OfficeCity','selector'=>'field', 'type'=>'str'),
        array('field'=>'OfficeAdditionalData','selector'=>'field', 'type'=>'str'),
        array('field'=>'DateMeasure','selector'=>'field', 'type'=>'date'),
        array('field'=>'MeasureNumber','selector'=>'field', 'type'=>'str'),
        array('field'=>'FineSuspension', 'selector'=>'field', 'type'=>'int', 'settype'=>'int'),
        array('field'=>'SuspensiveDate', 'selector'=>'field', 'type'=>'date'),
        array('field'=>'SuspensiveNumber', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'DateProtocolEntity','selector'=>'field', 'type'=>'date'),
        array('field'=>'EntityProtocolNumber','selector'=>'field', 'type'=>'str'),
        array('field'=>'Number', 'selector'=>'value', 'value'=>$rgNumber, 'type'=>'str'),
        array('field'=>'Division', 'selector'=>'field', 'type'=>'str'),
    );

    $rs->Update('Dispute',$a_Dispute,"Id=".$DisputeId." AND GradeTypeId=".$GradeTypeId);

    $checkJudgment = false;
    foreach($a_PostDisputeDate as $a_DisputeDate){
        $a_row = $a_DisputeDate['row'];
        unset($a_DisputeDate['row']);
        if($a_row['Id']>0){
            $rs->Update('DisputeDate',$a_DisputeDate,"Id=".$a_row['Id']);
        }
        else if(!empty($a_row['DisputeResultId']) || !empty($a_row['DateHearing'])){
            $rs->Insert('DisputeDate',$a_DisputeDate);
        }

        if($a_row['LosingParty']!=1){
            $rs->Delete('DisputeInvoice', "DisputeDateId=".$a_row['Id']." AND Type=1");
        }
        if($a_row['Parcel']!=1){
            $rs->Delete('DisputeInvoice', "DisputeDateId=".$a_row['Id']." AND Type=2");
        }

        if($a_row['DisputeResultId']>1){
            $checkJudgment = true;
            if($OwnerPresentation){
                if($a_row['DisputeResultId']==4)
                    $DisputeStatusId = 2;
                else
                    $DisputeStatusId = 3;
            } else {
                if($a_row['DisputeResultId']==4)
                    $DisputeStatusId = 3;
                else
                    $DisputeStatusId = 2;
            }

            $a_FineDispute = array(
                array('field'=>'DisputeStatusId', 'selector'=>'value', 'type'=>'int', 'value'=>$DisputeStatusId, 'settype'=>'int'),
            );
            $rs->Update('FineDispute',$a_FineDispute,"DisputeId=".$DisputeId);

            $a_Dispute = array(
                array('field'=>'DateMerit', 'selector'=>'value', 'value'=>$a_row['DateAction'], 'type'=>'date'),
            );
            $rs->Update('Dispute',$a_Dispute,"Id=".$DisputeId." AND GradeTypeId=".$GradeTypeId);


        }

        if($checkJudgment===false){
            $a_Dispute = array(
                array('field'=>'DateMerit', 'selector'=>'value', 'value'=>null, 'type'=>'date'),
            );
            $rs->Update('Dispute',$a_Dispute,"Id=".$DisputeId." AND GradeTypeId=".$GradeTypeId);
        }


    }

    foreach($a_PostDisputeAmount as $a_DisputeAmount) {
        $a_row = $a_DisputeAmount['row'];
        unset($a_DisputeAmount['row']);
        if ($a_row['Id'] > 0) {
            $rs->Update('DisputeAmount', $a_DisputeAmount, "Id=" . $a_row['Id']);
        }
    }

    foreach($a_PostFineDisputeAmount as $a_FDA) {
        $a_row = $a_FDA['row'];
        unset($a_FDA['row']);
        $a_checkFDA = $rs->getArrayLine($rs->ExecuteQuery("SELECT FineId FROM FineDisputeAmount WHERE FineId=".$a_row['FineId']." AND DisputeDateId=".$a_row['DisputeDateId']." AND DisputeId=".$DisputeId));
        if (isset($a_checkFDA['FineId'])) {
            $rs->Update('FineDisputeAmount', $a_FDA, "FineId=".$a_row['FineId']." AND DisputeDateId=".$a_row['DisputeDateId']." AND DisputeId=".$DisputeId);
        }
        else
        {
            $rs->Insert('FineDisputeAmount', $a_FDA);
        }
    }

    foreach($a_PostLosingParty as $a_LosingParty) {

        $a_row = $a_LosingParty['row'];
        unset($a_LosingParty['row']);
        if($a_row['Id']>0){

            $rs->Update('DisputeInvoice',$a_LosingParty,"Id=".$a_row['Id']);
        }
        else{
            $rs->Insert('DisputeInvoice',$a_LosingParty);
        }
    }

    foreach($a_PostParcel as $a_Parcel) {
        var_dump($a_Parcel);

        $a_row = $a_Parcel['row'];
        unset($a_Parcel['row']);
        if($a_row['Id']>0){
            $rs->Update('DisputeInvoice',$a_Parcel,"Id=".$a_row['Id']);
        }
        else{
            $rs->Insert('DisputeInvoice',$a_Parcel);
        }

    }
}

$rs->End_Transaction();


header("location: mgmt_dispute_upd.php?&Id=".$DisputeId."&FineId=".$FineId."&answer=Salvataggio avvenuto con successo");