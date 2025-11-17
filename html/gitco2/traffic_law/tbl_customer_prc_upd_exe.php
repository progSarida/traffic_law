<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

global $rs;

$n_Tab = CheckValue('tab_value','n');
$CityId = $_SESSION['cityid'];
$n_CustomerChargeId = CheckValue('CustomerChargeId','n');

$Automatic = CheckValue('Data126BisNationalAutomatic','n');
$ControllerId = CheckValue('Data126BisNationalControllerId','n');
$WaitDay = CheckValue('Data126BisNationalWaitDay','n');
$DayAccepted = CheckValue('Data126BisNationalDayAccepted','n');
$RangeDayMin = CheckValue('Data126BisNationalRangeDayMin','n');
$RangeDayMax = CheckValue('Data126BisNationalRangeDayMax','n');
$Address = CheckValue('Data126BisNationalAddress','s');
$DisputeCheckType = CheckValue('Data126BisNationalDisputeCheckType','n');
$communicationDays = CheckValue('Data126BisNationalCommunicationDays','n');
$communicationDelay = CheckValue('Data126BisNationalCommunicationDelay','n');
$incompletedCommunication = CheckValue('Data126BisNationalIncompletedCommunication','n');


$rs->Start_Transaction();

$a_PrcInsertUpdate = array(
    array('field'=>'Automatic','selector'=>'value','type'=>'int','value'=>$Automatic,'settype'=>'int'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
    array('field'=>'WaitDay','selector'=>'value','type'=>'int','value'=>$WaitDay,'settype'=>'int'),
    array('field'=>'DayAccepted','selector'=>'value','type'=>'int','value'=>$DayAccepted,'settype'=>'int'), //non è sulla pagina
    array('field'=>'RangeDayMin','selector'=>'value','type'=>'int','value'=>$RangeDayMin,'settype'=>'int'),
    array('field'=>'RangeDayMax','selector'=>'value','type'=>'int','value'=>$RangeDayMax,'settype'=>'int'),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'DisputeCheckType','selector'=>'value','type'=>'int','value'=>$DisputeCheckType,'settype'=>'int'),
    array('field'=>'CommunicationDays','selector'=>'value','type'=>'int','value'=>$communicationDays,'settype'=>'int'),
    array('field'=>'CommunicationDelay','selector'=>'value','type'=>'int','value'=>$communicationDelay,'settype'=>'int'),
    array('field'=>'IncompletedCommunication','selector'=>'value','type'=>'int','value'=>$incompletedCommunication,'settype'=>'int'),
);

$rs_CustomerParameter = $rs->Select('ProcessingData126BisNational',"CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerParameter)==0){
    $a_PrcInsertUpdate[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('ProcessingData126BisNational',$a_PrcInsertUpdate);
} else{
    $rs->Update('ProcessingData126BisNational',$a_PrcInsertUpdate,"CityId='".$CityId."'");
}




$Automatic = CheckValue('Data126BisForeignAutomatic','n');
$ControllerId = CheckValue('Data126BisForeignControllerId','n');
$WaitDay = CheckValue('Data126BisForeignWaitDay','n');
$RangeDayMin = CheckValue('Data126BisForeignRangeDayMin','n');
$RangeDayMax = CheckValue('Data126BisForeignRangeDayMax','n');
$Address = CheckValue('Data126BisForeignAddress','s');
$DisputeCheckType = CheckValue('Data126BisForeignDisputeCheckType','n');
$communicationDays = CheckValue('Data126BisForeignCommunicationDays','n');
$communicationDelay = CheckValue('Data126BisForeignCommunicationDelay','n');
$incompletedCommunication = CheckValue('Data126BisForeignIncompletedCommunication','n');

$a_PrcInsertUpdate = array(
    array('field'=>'Automatic','selector'=>'value','type'=>'int','value'=>$Automatic,'settype'=>'int'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
    array('field'=>'WaitDay','selector'=>'value','type'=>'int','value'=>$WaitDay,'settype'=>'int'),
    array('field'=>'DayAccepted','selector'=>'value','type'=>'int','value'=>$DayAccepted,'settype'=>'int'), //non è sulla pagina
    array('field'=>'RangeDayMin','selector'=>'value','type'=>'int','value'=>$RangeDayMin,'settype'=>'int'),
    array('field'=>'RangeDayMax','selector'=>'value','type'=>'int','value'=>$RangeDayMax,'settype'=>'int'),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'DisputeCheckType','selector'=>'value','type'=>'int','value'=>$DisputeCheckType,'settype'=>'int'),
    array('field'=>'CommunicationDays','selector'=>'value','type'=>'int','value'=>$communicationDays,'settype'=>'int'),
    array('field'=>'CommunicationDelay','selector'=>'value','type'=>'int','value'=>$communicationDelay,'settype'=>'int'),
    array('field'=>'IncompletedCommunication','selector'=>'value','type'=>'int','value'=>$incompletedCommunication,'settype'=>'int'),
);

$rs_CustomerParameter = $rs->Select('ProcessingData126BisForeign',"CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerParameter)==0){
    $a_PrcInsertUpdate[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('ProcessingData126BisForeign',$a_PrcInsertUpdate);
} else{
    $rs->Update('ProcessingData126BisForeign',$a_PrcInsertUpdate,"CityId='".$CityId."'");
}


$Automatic = CheckValue('Data180NationalAutomatic','n');
$ControllerId = CheckValue('Data180NationalControllerId','n');
$WaitDay = CheckValue('Data180NationalWaitDay','n');
$RangeDayMin = CheckValue('Data180NationalRangeDayMin','n');
$RangeDayMax = CheckValue('Data180NationalRangeDayMax','n');
$DayAccepted = CheckValue('Data180NationalDayAccepted','n');
$Address = CheckValue('Data180NationalAddress','s');
$presentationDelayType = CheckValue('Data180NationalPresentationDelayType','n');
$presentationDays = CheckValue('Data180NationalPresentationDays','n');
$presentationDelay = CheckValue('Data180NationalPresentationDelay','n');
$incompletedPresentation = CheckValue('Data180NationalIncompletedPresentation','n');

$a_PrcInsertUpdate = array(
    array('field'=>'Automatic','selector'=>'value','type'=>'int','value'=>$Automatic,'settype'=>'int'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
    array('field'=>'WaitDay','selector'=>'value','type'=>'int','value'=>$WaitDay,'settype'=>'int'),         //non c'è sulla pagina
    array('field'=>'DayAccepted','selector'=>'value','type'=>'int','value'=>$DayAccepted,'settype'=>'int'), 
    array('field'=>'RangeDayMin','selector'=>'value','type'=>'int','value'=>$RangeDayMin,'settype'=>'int'),
    array('field'=>'RangeDayMax','selector'=>'value','type'=>'int','value'=>$RangeDayMax,'settype'=>'int'),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'PresentationDelayType','selector'=>'value','type'=>'int','value'=>$presentationDelayType,'settype'=>'int'),
    array('field'=>'PresentationDays','selector'=>'value','type'=>'int','value'=>$presentationDays,'settype'=>'int'),
    array('field'=>'PresentationDelay','selector'=>'value','type'=>'int','value'=>$presentationDelay,'settype'=>'int'),
    array('field'=>'IncompletedPresentation','selector'=>'value','type'=>'int','value'=>$incompletedPresentation,'settype'=>'int'),
);

$rs_CustomerParameter = $rs->Select('ProcessingData180National',"CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerParameter)==0){
    $a_PrcInsertUpdate[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('ProcessingData180National',$a_PrcInsertUpdate);
} else{
    $rs->Update('ProcessingData180National',$a_PrcInsertUpdate,"CityId='".$CityId."'");
}

$Automatic = CheckValue('Data180ForeignAutomatic','n');
$ControllerId = CheckValue('Data180ForeignControllerId','n');
$WaitDay = CheckValue('Data180ForeignWaitDay','n');
$RangeDayMin = CheckValue('Data180ForeignRangeDayMin','n');
$RangeDayMax = CheckValue('Data180ForeignRangeDayMax','n');
$DayAccepted = CheckValue('Data180ForeignDayAccepted','n');
$Address = CheckValue('Data180ForeignAddress','s');
$presentationDelayType = CheckValue('Data180ForeignPresentationDelayType','n');
$presentationDays = CheckValue('Data180ForeignPresentationDays','n');
$presentationDelay = CheckValue('Data180ForeignPresentationDelay','n');
$incompletedPresentation = CheckValue('Data180ForeignIncompletedPresentation','n');

$a_PrcInsertUpdate = array(
    array('field'=>'Automatic','selector'=>'value','type'=>'int','value'=>$Automatic,'settype'=>'int'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
    array('field'=>'WaitDay','selector'=>'value','type'=>'int','value'=>$WaitDay,'settype'=>'int'),
    array('field'=>'DayAccepted','selector'=>'value','type'=>'int','value'=>$DayAccepted,'settype'=>'int'), //non è sulla pagina
    array('field'=>'RangeDayMin','selector'=>'value','type'=>'int','value'=>$RangeDayMin,'settype'=>'int'),
    array('field'=>'RangeDayMax','selector'=>'value','type'=>'int','value'=>$RangeDayMax,'settype'=>'int'),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'PresentationDelayType','selector'=>'value','type'=>'int','value'=>$presentationDelayType,'settype'=>'int'),
    array('field'=>'PresentationDays','selector'=>'value','type'=>'int','value'=>$presentationDays,'settype'=>'int'),
    array('field'=>'PresentationDelay','selector'=>'value','type'=>'int','value'=>$presentationDelay,'settype'=>'int'),
    array('field'=>'IncompletedPresentation','selector'=>'value','type'=>'int','value'=>$incompletedPresentation,'settype'=>'int'),
);

$rs_CustomerParameter = $rs->Select('ProcessingData180Foreign',"CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerParameter)==0){
    $a_PrcInsertUpdate[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('ProcessingData180Foreign',$a_PrcInsertUpdate);
} else{
    $rs->Update('ProcessingData180Foreign',$a_PrcInsertUpdate,"CityId='".$CityId."'");
}

$Automatic = CheckValue('DataPaymentNationalAutomatic','n');
$WaitDay = CheckValue('DataPaymentNationalWaitDay','n');
$RangeDayMin = CheckValue('DataPaymentNationalRangeDayMin','n');
$RangeDayMax = CheckValue('DataPaymentNationalRangeDayMax','n');
$ReducedPaymentDayAccepted = CheckValue('DataPaymentNationalReducedPaymentDayAccepted','n');
$PaymentDayAccepted = CheckValue('DataPaymentNationalPaymentDayAccepted','n');
$PaymentDayReminder = CheckValue('DataPaymentNationalPaymentDayReminder','n');
$AmountLimitNational = CheckValue('AmountLimitNational','f');
$IncludeNotificationResearchNational = CheckValue('DataPaymentNationalIncludeNotificationResearch','n');
$ApplyPercentualOnPrefectureFeeNational = CheckValue('DataPaymentNationalApplyPercentualOnPrefectureFee','n');

$a_PrcInsertUpdate = array(
    array('field'=>'Automatic','selector'=>'value','type'=>'int','value'=>$Automatic,'settype'=>'int'),
    array('field'=>'WaitDay','selector'=>'value','type'=>'int','value'=>$WaitDay,'settype'=>'int'),
    array('field'=>'RangeDayMin','selector'=>'value','type'=>'int','value'=>$RangeDayMin,'settype'=>'int'),
    array('field'=>'RangeDayMax','selector'=>'value','type'=>'int','value'=>$RangeDayMax,'settype'=>'int'),
    array('field'=>'ReducedPaymentDayAccepted','selector'=>'value','type'=>'int','value'=>$ReducedPaymentDayAccepted,'settype'=>'int'),
    array('field'=>'PaymentDayAccepted','selector'=>'value','type'=>'int','value'=>$PaymentDayAccepted,'settype'=>'int'),
    array('field'=>'PaymentDayReminder','selector'=>'value','type'=>'int','value'=>$PaymentDayReminder,'settype'=>'int'),
    array('field'=>'AmountLimit','selector'=>'value','type'=>'flt','value'=>$AmountLimitNational,'settype'=>'flt'),
    array('field'=>'IncludeNotificationResearch','selector'=>'value','type'=>'int', 'value'=>$IncludeNotificationResearchNational, 'settype'=>'int'),
    array('field'=>'ApplyPercentualOnPrefectureFee','selector'=>'value','type'=>'int', 'value'=>$ApplyPercentualOnPrefectureFeeNational, 'settype'=>'int'),
);

$rs_CustomerParameter = $rs->Select('ProcessingDataPaymentNational',"CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerParameter)==0){
    $a_PrcInsertUpdate[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('ProcessingDataPaymentNational',$a_PrcInsertUpdate);
} else{
    $rs->Update('ProcessingDataPaymentNational',$a_PrcInsertUpdate,"CityId='".$CityId."'");
}

$Automatic = CheckValue('DataPaymentForeignAutomatic','n');
$WaitDay = CheckValue('DataPaymentForeignWaitDay','n');
$RangeDayMin = CheckValue('DataPaymentForeignRangeDayMin','n');
$RangeDayMax = CheckValue('DataPaymentForeignRangeDayMax','n');
$ReducedPaymentDayAccepted = CheckValue('DataPaymentForeignReducedPaymentDayAccepted','n');
$PaymentDayAccepted = CheckValue('DataPaymentForeignPaymentDayAccepted','n');
$PaymentDayReminder = CheckValue('DataPaymentForeignPaymentDayReminder','n');
$AmountLimitForeign = CheckValue('AmountLimitForeign','f');
$IncludeNotificationResearchForeign = CheckValue('DataPaymentForeignIncludeNotificationResearch','n');
$ApplyPercentualOnPrefectureFeeForeign = CheckValue('DataPaymentForeignApplyPercentualOnPrefectureFee','n');

$a_PrcInsertUpdate = array(
    array('field'=>'Automatic','selector'=>'value','type'=>'int','value'=>$Automatic,'settype'=>'int'),
    array('field'=>'WaitDay','selector'=>'value','type'=>'int','value'=>$WaitDay,'settype'=>'int'),
    array('field'=>'RangeDayMin','selector'=>'value','type'=>'int','value'=>$RangeDayMin,'settype'=>'int'),
    array('field'=>'RangeDayMax','selector'=>'value','type'=>'int','value'=>$RangeDayMax,'settype'=>'int'),
    array('field'=>'ReducedPaymentDayAccepted','selector'=>'value','type'=>'int','value'=>$ReducedPaymentDayAccepted,'settype'=>'int'),
    array('field'=>'PaymentDayAccepted','selector'=>'value','type'=>'int','value'=>$PaymentDayAccepted,'settype'=>'int'),
    array('field'=>'PaymentDayReminder','selector'=>'value','type'=>'int','value'=>$PaymentDayReminder,'settype'=>'int'),
    array('field'=>'AmountLimit','selector'=>'value','type'=>'flt','value'=>$AmountLimitForeign,'settype'=>'flt'),
    array('field'=>'IncludeNotificationResearch','selector'=>'value','type'=>'int', 'value'=>$IncludeNotificationResearchForeign, 'settype'=>'int'),
    array('field'=>'ApplyPercentualOnPrefectureFee','selector'=>'value','type'=>'int', 'value'=>$ApplyPercentualOnPrefectureFeeForeign, 'settype'=>'int'),
);

$rs_CustomerParameter = $rs->Select('ProcessingDataPaymentForeign',"CityId='".$CityId."'");
if(mysqli_num_rows($rs_CustomerParameter)==0){
    $a_PrcInsertUpdate[] = array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId);
    $rs->Insert('ProcessingDataPaymentForeign',$a_PrcInsertUpdate);
} else{
    $rs->Update('ProcessingDataPaymentForeign',$a_PrcInsertUpdate,"CityId='".$CityId."'");
}
$a_PrcInsertUpdate = array(
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
    array('field'=>'CreationType','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
    array('field'=>'FromDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d'),'settype'=>'date'),
    
    array('field'=>'NationalTotalFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NationalNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NationalResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NationalPECNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NationalPECResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    
    array('field'=>'ForeignTotalFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ForeignNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ForeignResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ForeignPECNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ForeignPECResearchFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    
    array('field'=>'NationalPostalType','selector'=>'field','type'=>'str'),
    array('field'=>'NationalPostalTypePagoPA','selector'=>'field','type'=>'str'),

    array('field'=>'NationalPercentualReminder','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'NationalReminderNotificationFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'IncreaseNationalNotificationFee','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    
    array('field'=>'ForeignPostalType','selector'=>'field','type'=>'str'),
    array('field'=>'ForeignPostalTypePagoPA','selector'=>'field','type'=>'str'),

    array('field'=>'ForeignPercentualReminder','selector'=>'field','type'=>'flt','settype'=>'flt'),

    array('field'=>'IncreaseForeignNotificationFee','selector'=>'chkbox','type'=>'int','settype'=>'int'),
);

//Valori colonne CustomerCharge
$NationalTotalFee = CheckValue('NationalTotalFee','n');
$NationalNotificationFee = CheckValue('NationalNotificationFee','n');
$NationalResearchFee = CheckValue('NationalResearchFee','n');
$NationalPECNotificationFee = CheckValue('NationalPECNotificationFee','n');
$NationalPECResearchFee = CheckValue('NationalPECResearchFee','n');
$ForeignTotalFee = CheckValue('ForeignTotalFee','n');
$ForeignNotificationFee = CheckValue('ForeignNotificationFee','n');
$ForeignResearchFee = CheckValue('ForeignResearchFee','n');
$ForeignPECNotificationFee = CheckValue('ForeignPECNotificationFee','n');
$ForeignPECResearchFee = CheckValue('ForeignPECResearchFee','n');

$NationalPostalType = CheckValue('NationalPostalType','s');
$NationalPostalTypePagoPA = CheckValue('NationalPostalTypePagoPA','s');
$ForeignPostalType = CheckValue('ForeignPostalType','s');
$ForeignPostalTypePagoPA = CheckValue('ForeignPostalTypePagoPA','s');

$NationalPercentualReminder = CheckValue('NationalPercentualReminder','n');
$ForeignPercentualReminder = CheckValue('ForeignPercentualReminder','n');
$NationalReminderNotificationFee = CheckValue('NationalReminderNotificationFee','n');
$IncreaseForeignNotificationFee = CheckValue('IncreaseForeignNotificationFee','n');
$IncreaseNationalNotificationFee = CheckValue('IncreaseNationalNotificationFee','n');

//Se esiste un risultato già salvato, controlla le differenze, nel caso
if(!empty($n_CustomerChargeId) && $n_CustomerChargeId>0){
    
    //Seleziono il record in vigore da CustomerCharge per capire se cambia qualcosa, ed in tal caso fare la Insert
    $rs_CustomerCharge = $rs->Select('CustomerCharge',"Id = $n_CustomerChargeId AND CityId = '$CityId' AND ToDate IS NULL AND CreationType = 1");
    $r_CustomerCharge = $rs->getArrayLine($rs_CustomerCharge);

    //Bug2665 se ci sono differenze aggiungo una riga, altrimenti lascio tutto com'è
    //Dal controllo delle differenze escludo i PostalType che sono trattati a parte e per tutti i CreationType
    //Controlla se c'è almeno una differenza tra i valori passati e quelli dell'ultimo record disponibile
    $diff_col = false;
    
    $diff_col = ($diff_col || ($NationalTotalFee != $r_CustomerCharge['NationalTotalFee']));
    $diff_col = ($diff_col || ($NationalNotificationFee != $r_CustomerCharge['NationalNotificationFee']));
    $diff_col = ($diff_col || ($NationalResearchFee != $r_CustomerCharge['NationalResearchFee']));
    $diff_col = ($diff_col || ($NationalPECNotificationFee != $r_CustomerCharge['NationalPECNotificationFee']));
    $diff_col = ($diff_col || ($NationalPECResearchFee != $r_CustomerCharge['NationalPECResearchFee']));
    $diff_col = ($diff_col || ($ForeignTotalFee != $r_CustomerCharge['ForeignTotalFee']));
    $diff_col = ($diff_col || ($ForeignNotificationFee != $r_CustomerCharge['ForeignNotificationFee']));
    $diff_col = ($diff_col || ($ForeignResearchFee != $r_CustomerCharge['ForeignResearchFee']));
    $diff_col = ($diff_col || ($ForeignPECNotificationFee != $r_CustomerCharge['ForeignPECNotificationFee']));
    $diff_col = ($diff_col || ($ForeignPECResearchFee != $r_CustomerCharge['ForeignPECResearchFee']));
    $diff_col = ($diff_col || ($NationalPercentualReminder != $r_CustomerCharge['NationalPercentualReminder']));
    $diff_col = ($diff_col || ($ForeignPercentualReminder != $r_CustomerCharge['ForeignPercentualReminder']));
    $diff_col = ($diff_col || ($NationalReminderNotificationFee != $r_CustomerCharge['NationalReminderNotificationFee']));
    $diff_col = ($diff_col || ($IncreaseForeignNotificationFee != $r_CustomerCharge['IncreaseForeignNotificationFee']));
    $diff_col = ($diff_col || ($IncreaseNationalNotificationFee != $r_CustomerCharge['IncreaseNationalNotificationFee']));
    
    $diff_col = ($diff_col || ($NationalPostalType != $r_CustomerCharge['NationalPostalType']));
    $diff_col = ($diff_col || ($NationalPostalTypePagoPA != $r_CustomerCharge['NationalPostalTypePagoPA']));
    $diff_col = ($diff_col || ($ForeignPostalType != $r_CustomerCharge['ForeignPostalType']));
    $diff_col = ($diff_col || ($ForeignPostalTypePagoPA != $r_CustomerCharge['ForeignPostalTypePagoPA']));
    
    if($diff_col)
        {
        //La seguente condizione serve per evitare che, in caso di cambio nello stesso giorno, si vada ad impostare una ToDate antecedente la FromDate
        $ToDate = $r_CustomerCharge['FromDate']!=date('Y-m-d') ? date('Y-m-d',strtotime('-1 days')) : $r_CustomerCharge['FromDate'];
        $a_PrcUpdateToDate = array(
            array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>$ToDate,'settype'=>'date')
        );
        $rs->Update('CustomerCharge',$a_PrcUpdateToDate,"Id='".$n_CustomerChargeId."'"); //Aggiorno la ToDate del precedente record
        $rs->Insert("CustomerCharge", $a_PrcInsertUpdate);  //Creo un nuovo record aggiornato
        }
    
    //Bug 2818 salviamo i tipi di bollettini per tutti i CreationTypeId invece che solo per CreationTypeId 1
    //Controlla se ci sono differenze tra i postaltype passati e quelli dell'ultimo record disponibile
    $diff_postaltype = false;
    
    $diff_postaltype = ($diff_postaltype || ($NationalPostalType != $r_CustomerCharge['NationalPostalType']));
    $diff_postaltype = ($diff_postaltype || ($NationalPostalTypePagoPA != $r_CustomerCharge['NationalPostalTypePagoPA']));
    $diff_postaltype = ($diff_postaltype || ($ForeignPostalType != $r_CustomerCharge['ForeignPostalType']));
    $diff_postaltype = ($diff_postaltype || ($ForeignPostalTypePagoPA != $r_CustomerCharge['ForeignPostalTypePagoPA']));
    //In caso cambi uno o più dei PostalType sul CreationType 1  effettuo il cambio anche sugli altri CreationType
    if($diff_postaltype){
        $a_PrcUpdate = array(
            array('field'=>'NationalPostalType','selector'=>'field','type'=>'str'),
            array('field'=>'NationalPostalTypePagoPA','selector'=>'field','type'=>'str'),
            array('field'=>'ForeignPostalType','selector'=>'field','type'=>'str'),
            array('field'=>'ForeignPostalTypePagoPA','selector'=>'field','type'=>'str'),
        );
        
        $rs->Update('CustomerCharge',$a_PrcUpdate,"CityId='".$CityId."' AND ToDate IS NULL AND CreationType <> 1");
    }
} else{ //Se non esiste un risultato per l'ente corrente, crea un nuovo risultato
    $rs->Insert('CustomerCharge',$a_PrcInsertUpdate);
}

$a_Customer = array(
    array('field'=>'ReminderAdditionalFee','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'NationalReminderHeaderSarida','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'ForeignReminderHeaderSarida','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'ElaborationDaysNational','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'ElaborationDaysForeign','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'PaymentDaysNational','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'PaymentDaysForeign','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'EnableForeignKindSending','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'EnableKindOldProcedure','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'DisableKindPagoPAProcessing','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'LicensePointPaymentCompletion','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'LicensePointDecurtationDays','selector' => 'field','type' => 'int','settype'=>'int'),
    array('field'=>'NationalKindFineSendFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderPhone','selector'=>'field','type'=>'str','settype'=>'str'),
    array('field'=>'ReminderEmail','selector'=>'field','type'=>'str','settype'=>'str'),
    array('field' => 'ReminderOfficeInfo','selector' => 'field','type' => 'str'),
    array('field' => 'RoleConcessionaire','selector' => 'field','type' => 'str'),
    array('field' => 'RoleCityCode','selector' => 'field','type' => 'str'),
    array('field' => 'RoleMinExpiredInstallments','selector' => 'field','type' => 'int','settype' => 'int'),
);

$rs->Update('Customer',$a_Customer,"CityId='".$CityId."'");

$a_Update = array (
    array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"),'settype'=>'date'),
);
$rs->Update("PostalCharge", $a_Update, "ToDate IS NULL and CityId='$CityId'");
$a_Insert = array (
    array('field'=>'Zone0','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Zone1','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Zone2','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'Zone3','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CanFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'CadFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone0','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone1','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone2','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'ReminderZone3','selector'=>'field','type'=>'flt','settype'=>'flt'),
    array('field'=>'FromDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"),'settype'=>'date'),
    array('field'=>'ToDate','selector'=>'value','type'=>'date','value'=>NULL,'settype'=>'date'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId)
);
$rs->Insert("PostalCharge", $a_Insert);

$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Aggiornamento effettuato.";

header("location: tbl_customer_prc_upd.php?PageTitle=Ente/Procedure ente&tab=".$n_Tab);
