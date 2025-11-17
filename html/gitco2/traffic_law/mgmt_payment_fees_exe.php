<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require_once(INC."/initialization.php");

$ReturnPage = CheckValue('ReturnPage','s');

$FinePaymentSpecificationType = CheckValue("FinePaymentSpecificationType", 'n');

$str_Where = "P.CityId='".$_SESSION['cityid']."'";

//Passo i PaymentId tramite le checkbox ed imposto il vincolo alla query
//In caso dovesse, per qualche motivo, mancare il PaymentId, il processo verrebbe bloccato
if(isset($_POST['checkbox'])) {
    $str_Where .= "AND PaymentId IN(";
    $counter = 0;
    foreach($_POST['checkbox'] as $PId){
        if($counter == 0)
            $str_Where .= $PId;
        else
            $str_Where .= ",".$PId;
        $counter++;
    }
    $str_Where .= ")";


    $str_fineArticle = '';
    
    $strOrder = "PaymentDate";
    
    $str_query = "SELECT * FROM V_FinePayment P";
    
    $rs_Payment = $rs->SelectQuery("$str_query $str_fineArticle WHERE $str_Where ORDER BY $strOrder");
    
    //Per ogni PaymentId vado a ricalcolare gli scorpori
    while($r_Payment = mysqli_fetch_array($rs_Payment))
        {
        $FineId = $r_Payment['FineId'];
        $PaymentId = $r_Payment['PaymentId'];
        $PaymentDocumentId = $r_Payment['PaymentDocumentId'];
        $PaymentDate = $r_Payment['PaymentDate'];
        $Amount = $r_Payment['Amount'];
        $ProtocolYear = $r_Payment['ProtocolYear'];
        $ReminderDate = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"))['ReminderDate'] ?? null;
        //Calcolo scorporo atteso
        $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $Amount, $FineId, $_SESSION['cityid'], $ProtocolYear, $PaymentDate, $ReminderDate);
        
        $a_Payment = array(
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
            array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$a_Fee['Fee'],'settype'=>'flt'),
            array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['ResearchFee'],'settype'=>'flt'),
            array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['NotificationFee'],'settype'=>'flt'),
            array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt'),
            array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CustomerFee'],'settype'=>'flt'),
            array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CanFee'],'settype'=>'flt'),
            array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CadFee'],'settype'=>'flt'),
        );
    //     trigger_error("****************");
    //     trigger_error("Modifico il pagamento ID: ".$PaymentId." del verbale con ID: ".$FineId." e CRON: ".$r_Payment['ProtocolId']."/".$r_Payment['ProtocolYear']);
    //     trigger_error("Importi salvati --> Sanzione: ".$r_Payment['Fee']." Notifica: ".$r_Payment['NotificationFee']." Ricerca: ".$r_Payment['ResearchFee']." Comune: ".$r_Payment['CustomerFee']);
    //     trigger_error("Importi ricalcolati --> Sanzione: ".$a_Fee['Fee']." Notifica: ".$a_Fee['NotificationFee']." Ricerca: ".$a_Fee['ResearchFee']." Comune: ".$a_Fee['CustomerFee']);
        $rs->Update('FinePayment',$a_Payment, "Id=".$PaymentId);
        }
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}
else //Viene lanciato errore in caso non venga passato il PaymentId
    $_SESSION['Message']['Error'] = "Non è stato possibile completare la richiesta. ID pagamento mancante.";
header("location: ".$ReturnPage);