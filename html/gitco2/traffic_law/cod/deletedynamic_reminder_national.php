<?php
require_once(CLS."/cls_pagamenti.php");

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='deletedynamic_reminder_national'");
$r_Locked = mysqli_fetch_assoc($rs_Locked);

if ($r_Locked['Locked'] == 1) {
    $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
    header("location: ".$P);
    DIE;
} else {
    $UpdateLockedPage = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
    );
    $rs->Update('LockedPage', $UpdateLockedPage, "Title='deletedynamic_reminder_national'");
}
$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$str_Warning = "";
$pagopaServicequery=$rs->Select("PagoPAService","id={$r_Customer['PagoPAService']}");
$pagopaService=$rs->getArrayLine($pagopaServicequery);

$a_Failed = array();

if(isset($_POST['checkbox'])) {
    $rs->Start_Transaction();
    
    foreach($_POST['checkbox'] as $ReminderId) {
        $rs->Begin_Transaction();
        
        $rs_FineReminder = $rs->Select("FineReminder", "Id=$ReminderId");
        $r_FineReminder = $rs->getArrayLine($rs_FineReminder);
        
        $r_Fine = $rs->getArrayLine($rs->SelectQuery(
            "SELECT
                F.Id,
                F.FineDate,
                F.ReminderDate,
                F.PagoPA1,
                F.PagoPA2,
                F.PagoPAReducedPartial,
                F.PagoPAReducedTotal,
                F.PagoPAPartial,
                F.PagoPATotal,
                F.ProtocolYear,
                F.VehiclePlate,
                FA.ViolationTypeId,
                TAR.ReducedPayment
            
                FROM Fine F
                JOIN FineArticle FA ON F.Id=FA.FineId
                JOIN ArticleTariff TAR ON TAR.ArticleId=FA.ArticleId and TAR.Year = F.ProtocolYear
                WHERE F.Id=".$r_FineReminder['FineId']
            ));
        
        $r_Trespasser = $rs->getArrayLine($rs->Select("Trespasser", "Id={$r_FineReminder['TrespasserId']}"));
        
        $FineId = $r_Fine['Id'];
        $ReminderDate = $r_Fine['ReminderDate'];
        $reminderDocName = $r_FineReminder['Documentation']; 
        
        $TrespasserType = ($r_Trespasser['Genre'] == "D") ? "G" : "F";
        $TrespasserTaxCode = PickVatORTaxCode($r_Trespasser['Genre'], $r_Trespasser['VatCode'], $r_Trespasser['TaxCode']);
        $FineText = 'Anno ' . $r_Fine['ProtocolYear'] . ' targa ' . $r_Fine['VehiclePlate'];
        $GenreParemeter = ($r_Trespasser['Genre'] == "D")? "D" : "P";
        $rs_PagoPAServiceParameter = $rs->Select('PagoPAServiceParameter', "CityId='".$_SESSION['cityid']."' AND ServiceId=".$pagopaService['Id']." AND Genre='$GenreParemeter' AND Kind='S' AND ValidityEndDate IS NULL");
        $a_PagoPAServiceParams= $rs->getResults($rs_PagoPAServiceParameter);
        
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
        
        //cancello i riferimenti sul verbale solo se quello che sto cancellando è l'ultimo creato in quella data per quel verbale
        if ($ReminderDate == $r_FineReminder['PrintDate']){
            
            //Recupero il reminder id massimo
            $rs_ReminderMax = $rs->SelectQuery("SELECT Max(Id) as LastId FROM FineReminder where PrintDate = '".$r_FineReminder['PrintDate']."' AND FineId = ".$r_FineReminder['FineId']);
            //Controllo l'esistenza di FineReminder pregressi e recupero i dati dell'ultima occorrenza più recente
            $rs_ReminderOld = $rs->SelectQuery("SELECT * FROM FineReminder where FineId = ".$r_FineReminder['FineId']." AND Id != ".$ReminderId." ORDER BY Id DESC LIMIT 1");
            if(mysqli_num_rows($rs_ReminderMax)>0)
                $r_ReminderMax = mysqli_fetch_array($rs_ReminderMax)['LastId'];
            
            $r_ReminderOld = mysqli_fetch_array($rs_ReminderOld);
                
            //Se non ci sono occorrenze precedenti, imposto a NULL le date nel verbale legate ai solleciti
            if (mysqli_num_rows($rs_ReminderOld) == 0 && $r_ReminderMax == $ReminderId) {
                //dovresti recuperare il dato del verbale per pagoPA
                $PagoPACode1 = $r_Fine['PagoPA1'];
                $PagoPACode2 = $r_Fine['PagoPA2'];
                $b_PagoPAFail1 = $b_PagoPAFail2 = false;
                
                $cls_pagamenti = new cls_pagamenti($FineId, $_SESSION['cityid']);
                
                $ReducedFee = $cls_pagamenti->getFineReducedFee() + $cls_pagamenti->getAdditionalFee();
                $Fee = $cls_pagamenti->getFineFee() + $cls_pagamenti->getAdditionalFee();
                $MaxFee = $cls_pagamenti->getFineMaxFee() + $cls_pagamenti->getAdditionalFee();
                
                if(!empty($PagoPACode1) || !empty($PagoPACode2)){
                    $a_Importi = array(
                        'Amounts' => array(
                            array(
                                'ReducedPartial'=>number_format($ReducedFee, 2, '.', ''),
                                'ReducedTotal'=>number_format($Fee, 2, '.', ''),
                                'Partial'=>number_format($Fee, 2, '.', ''),
                                'Total'=>number_format($MaxFee, 2, '.', ''),
                                'ViolationTypeId' => $r_Fine['ViolationTypeId']
                            )
                        ),
                        'Sum' => array(
                            'ReducedPartial'=>number_format($ReducedFee, 2, '.', ''),
                            'ReducedTotal'=>number_format($Fee, 2, '.', ''),
                            'Partial'=>number_format($Fee, 2, '.', ''),
                            'Total'=>number_format($MaxFee, 2, '.', ''),
                        )
                    );
                    
                    if($r_Fine['ReducedPayment']){
                        $fullFeeUpd = 'ReducedTotal'; //sanizione minima
                        $partialFeeUpd = 'ReducedPartial'; //sanzione ridotta
                    }else{
                        $fullFeeUpd = 'Total'; //metà del massimo
                        $partialFeeUpd = 'Partial'; //sanzione minima
                    }
                    
                    if(!empty($PagoPACode1)){
                        if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $partialFeeUpd, $PagoPACode1, $FineId, $r_Fine['FineDate'], $TrespasserType, $r_Trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                            $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedPartial'] ,'settype'=>'flt');
                            $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Partial'] ,'settype'=>'flt');
                            if(empty($PagoPACode2)){
                                $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' => null,'settype'=>'flt', 'nullable' => true);
                            }
                        } else $b_PagoPAFail1 = true;
                    }
                    
                    if(!empty($PagoPACode2)){
                        if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $fullFeeUpd, $PagoPACode2, $FineId, $r_Fine['FineDate'], $TrespasserType, $r_Trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                            $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['ReducedTotal'] ,'settype'=>'flt');
                            $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum']['Total'] ,'settype'=>'flt');
                            if(empty($PagoPACode1)){
                                $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null,'settype'=>'flt', 'nullable' => true);
                            }
                        } else $b_PagoPAFail2 = true;
                    }
                    
                    if($b_PagoPAFail1 || $b_PagoPAFail2){
                        $a_Failed[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito, il sollecito non è stato eliminato.';
                    } else {
                        $a_FineUpd[] =array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>NULL,'settype'=>'date');
                        $a_FineUpd[] =array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>NULL,'settype'=>'date');
                    }
                }
            }
            //Se invece trova solleciti pregressi, imposto le date sul verbale con quelle dell'ultimo sollecito utile
            elseif(mysqli_num_rows($rs_ReminderOld) > 0){
                //dovresti usare il TotaleDovuto del ReminderOld
                $PagoPACode1 = $r_Fine['PagoPA1'];
                $PagoPACode2 = $r_Fine['PagoPA2'];
                $b_PagoPAFail1 = $b_PagoPAFail2 = false;
                $a_FineUpd = array();
                
                if(!empty($PagoPACode1) || !empty($PagoPACode2)){
                    $feeIndex = "Total";
                    $a_Importi = array(
                        'Amounts' => array(
                            array(
                                $feeIndex=>number_format(((float)($r_ReminderOld['TotalAmount'] - $r_ReminderOld['Amount'])), 2, '.', ''),
                                'ViolationTypeId' => $r_Fine['ViolationTypeId']
                            )
                        ),
                        'Sum' => array(
                            $feeIndex=>number_format(((float)($r_ReminderOld['TotalAmount'] - $r_ReminderOld['Amount'])), 2, '.', ''),
                        )
                    );
                    
                    if(!empty($PagoPACode1)){
                        if(updatePagoPA(PAGOPA_PREFIX_FINE_PARTIAL, $pagopaService, $a_Importi, $feeIndex, $PagoPACode1, $FineId, $r_Fine['FineDate'], $TrespasserType, $r_Trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                            $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum'][$feeIndex] ,'settype'=>'flt');
                            $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum'][$feeIndex] ,'settype'=>'flt');
                            if(empty($PagoPACode2)){
                                $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' => null,'settype'=>'flt', 'nullable' => true);
                            }
                        } else $b_PagoPAFail1 = true;
                    }
                    
                    if(!empty($PagoPACode2)){
                        if(updatePagoPA(PAGOPA_PREFIX_FINE_TOTAL, $pagopaService, $a_Importi, $feeIndex, $PagoPACode2, $FineId, $r_Fine['FineDate'], $TrespasserType, $r_Trespasser, $TrespasserTaxCode, $FineText, $a_PagoPAServiceParams, $r_Customer['PagoPAPassword'], $r_Customer['SondrioServizio'], $r_Customer['SondrioSottoservizio'])){
                            $a_FineUpd[] = array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum'][$feeIndex] ,'settype'=>'flt');
                            $a_FineUpd[] = array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$a_Importi['Sum'][$feeIndex] ,'settype'=>'flt');
                            if(empty($PagoPACode1)){
                                $a_FineUpd[] = array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null ,'settype'=>'flt', 'nullable' => true);
                                $a_FineUpd[] = array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' => null,'settype'=>'flt', 'nullable' => true);
                            }
                        } else $b_PagoPAFail2 = true;
                    }
                    
                    if($b_PagoPAFail1 || $b_PagoPAFail2){
                        $a_Failed[$FineId] = 'ID '.$FineId.': Aggiornamento PagoPA fallito, il sollecito non è stato eliminato.';
                    } else {
                        $a_FineUpd[] = array('field'=>'ReminderDate','selector'=>'value','type'=>'date','value'=>$r_ReminderOld['PrintDate'],'settype'=>'date');
                        $a_FineUpd[] = array('field'=>'ProcessingPaymentDateTime','selector'=>'value','type'=>'date','value'=>$r_ReminderOld['PrintDate'],'settype'=>'date');
                    }
                }
            }
            if(!empty($a_FineUpd)) {
                $a_FinePagoPAHistory = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field' => 'PagoPA1', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode1),
                    array('field' => 'PagoPA2', 'selector' => 'value', 'type' => 'str', 'value' => $PagoPACode2),
                    array('field'=>'ReminderId','selector'=>'value','type'=>'int','value'=>$ReminderId,'settype'=>'int'),
                    array('field' => 'PagoPAReducedPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Fine['PagoPAReducedPartial'] ,'settype'=>'flt'),
                    array('field' => 'PagoPAPartial', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Fine['PagoPAPartial'] ,'settype'=>'flt'),
                    array('field' => 'PagoPAReducedTotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Fine['PagoPAReducedTotal'] ,'settype'=>'flt'),
                    array('field' => 'PagoPATotal', 'selector' => 'value', 'type' => 'flt', 'value' =>$r_Fine['PagoPATotal'] ,'settype'=>'flt'),
                );
                
                $rs->Insert("FinePagoPAHistory", $a_FinePagoPAHistory);
                $rs->Update("Fine", $a_FineUpd, "Id=".$r_FineReminder['FineId']);
            }
        }
        if(!isset($a_Failed[$FineId])) {
            $rs->Insert('FineReminderHistory',$a_InsertHistory);
            //Cancello l'immagine salvata in FineDocumentation
            $rs->Delete("FineDocumentation","Documentation='$reminderDocName' AND DocumentationTypeId IN(29,30)");
            //verificare il valore di $ReminderId perché il record non viene cancellato
            $rs->Delete("FineReminder", "Id=$ReminderId");
        }
        $rs->End_Transaction();
    }
    foreach($a_Failed as $failMessage){
        trigger_error($failMessage, E_USER_WARNING);
        $str_Warning .= $failMessage.'<br>';
    }
    
    if ($str_Warning != ''){
        $_SESSION['Message']['Warning'] = '<div style="height:12rem;overflow-y:auto;">'.$str_Warning.'</div>';
    } else {
        $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
    }
    
    $aUpdate = array(
        array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
        array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
    );
    
    $rs->Begin_Transaction();
    $rs->Update('LockedPage',$aUpdate, "Title='deletedynamic_reminder_national'");
    $rs->End_Transaction();
}
