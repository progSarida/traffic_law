<?php

if(isset($_POST['checkbox'])) {
    $rs->End_Transaction();
    foreach($_POST['checkbox'] as $ReminderId) {
        $rs_FineReminder = $rs->Select("FineReminder", "Id=$ReminderId");
        $r_FineReminder = mysqli_fetch_array($rs_FineReminder);
        
        $a_InsertHistory = array(
            array('field'=>'FineReminderId','selector'=>'value','type'=>'int','value'=>$r_FineReminder['Id'],'settype'=>'int'),
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$r_FineReminder['FineId'],'settype'=>'int'),
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_FineReminder['TrespasserId'],'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_FineReminder['TrespasserTypeId'],'settype'=>'int'),
            array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['NotificationFee'],'settype'=>'flt'),
            array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$r_FineReminder['PrintDate'],'settype'=>'date'),
            array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$r_FineReminder['Documentation']),
            
            array('field'=>'PaymentDays','selector'=>'value','type'=>'int','value'=>$r_FineReminder['PaymentDays'],'settype'=>'int'),
            array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$r_FineReminder['PaymentDate'],'settype'=>'date'),
            array('field'=>'DaysFromNotificationDate','selector'=>'value','type'=>'int','value'=>$r_FineReminder['DaysFromNotificationDate'],'settype'=>'int'),
            array('field'=>'DelayDays','selector'=>'value','type'=>'int','value'=>$r_FineReminder['DelayDays'],'settype'=>'int'),
            array('field'=>'Semester','selector'=>'value','type'=>'int','value'=>$r_FineReminder['Semester'],'settype'=>'int'),
            array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['Fee'],'settype'=>'flt'),
            array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['MaxFee'],'settype'=>'flt'),
            array('field'=>'HalfMaxFee','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['HalfMaxFee'],'settype'=>'flt'),
            array('field'=>'TotalNotification','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['TotalNotification'],'settype'=>'flt'),
            array('field'=>'Amount','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['Amount'],'settype'=>'flt'),
            array('field'=>'TotalAmount','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['TotalAmount'],'settype'=>'flt'),
            array('field'=>'Percentual','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['Percentual'],'settype'=>'flt'),
            array('field'=>'PercentualAmount','selector'=>'value','type'=>'flt','value'=>$r_FineReminder['PercentualAmount'],'settype'=>'flt'),
            array('field'=>'Deleted','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
        );
        
        $rs->Insert('FineReminderHistory',$a_InsertHistory);
        
        $rs_ReminderDate = $rs->SelectQuery("SELECT ReminderDate FROM Fine WHERE Id=".$r_FineReminder['FineId']);
        $ReminderDate = mysqli_fetch_array($rs_ReminderDate)['ReminderDate'];
        
        if ($ReminderDate == $r_FineReminder['PrintDate']){
            $a_InsertFine = array(
                array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>NULL,'settype'=>'date'),
            );
            $rs->Update("Fine", $a_InsertFine, "Id=".$r_FineReminder['FineId']);
        }
        
        $rs->Delete("FineReminder", "Id=$ReminderId");
    }
    $_SESSION['Message'] = "Solleciti eliminati con successo.";
    $rs->End_Transaction();
}
$_SESSION['Message'] = "Solleciti eliminati con successo.";