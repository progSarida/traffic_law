<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

if($_POST) {
    $FineId = CheckValue('FineId','n');
    $Amount = CheckValue('Amount','n');
    $ProtocolYear = CheckValue('ProtocolYear','n');
    $PaymentDocumentId = CheckValue('PaymentDocumentId','n');
    $PaymentDate = DateInDB(CheckValue('PaymentDate','s'));


    $str_City = $_SESSION['cityid'];
    
    $ReminderDate = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"))['ReminderDate'] ?? null;

    $table_rows = $rs->Select('Customer', "CityId='".$str_City."'");
    $table_row = mysqli_fetch_array($table_rows);
    $FinePaymentSpecificationType = $table_row['FinePaymentSpecificationType'];

    //Se viene impostato il pagamento ridotto controllo se è previsto dall'articolo
    if($PaymentDocumentId == 0){
        //Controllo se l'articolo del verbale prevede pagamento ridotto
        $rs_Reduced = $rs->Select('V_FineTariff', "FineId=$FineId AND ReducedPayment > 0");
        //Se non è previsto imposto automaticamente il pagamento come normale
        if(mysqli_num_rows($rs_Reduced) == 0)
            $PaymentDocumentId = 1;
    }
    
    $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $Amount, $FineId, $str_City, $ProtocolYear, $PaymentDate, $ReminderDate);

    echo json_encode(
        array(
            "Fee" => number_format($a_Fee['Fee'], 2, '.', ''),
            "ResearchFee" => number_format($a_Fee['ResearchFee'], 2, '.', ''),
            "NotificationFee" => number_format($a_Fee['NotificationFee'], 2, '.', ''),
            "PercentualFee" => number_format($a_Fee['PercentualFee'], 2, '.', ''),
            "CustomerFee" => number_format($a_Fee['CustomerFee'], 2, '.', ''),
            "CanFee" => number_format($a_Fee['CanFee'], 2, '.', ''),
            "CadFee" => number_format($a_Fee['CadFee'], 2, '.', ''),
        )
    );

}






