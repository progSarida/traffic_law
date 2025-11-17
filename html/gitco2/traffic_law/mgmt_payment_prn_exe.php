<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";

$Filters = CheckValue('Filters','s');

$FineId= CheckValue('FineId','n');
$PaymentId= CheckValue('Id','n');

//mgmt_payment_add_exe
$AddPage = CheckValue('AddPage','n');

$table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$table_row = mysqli_fetch_array($table_rows);

$MangerName = $table_row['ManagerName'];
$ManagerAddress = $table_row['ManagerAddress'];
$ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
$ManagerPhone = $table_row['ManagerPhone'];
$MangerSignName = $table_row['ManagerSignName'];





$pdf = new TCPDF('', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('Request');
$pdf->SetSubject('Request');
$pdf->SetKeywords('');


$pdf->SetMargins(10,10,10);

trigger_error("numero pagamento: ".$PaymentId, E_USER_NOTICE);
trigger_error("numero verbale: ".$FineId, E_USER_NOTICE);


$rs_Payment = $rs->SelectQuery("
        SELECT 
        F.ProtocolId,
        F.ProtocolYear,
        F.Code,
        F.VehiclePlate,
        
        FP.Amount,
        FP.PaymentDate,
        FP.Name,
        FP.ReceiptNumber,
         
        FA.Fee,
        FA.MaxFee,
        
        
        FH.NotificationTypeId,
        FH.CustomerFee,
        FH.NotificationFee,
        FH.ResearchFee,
        FH.CadFee,
        FH.CanFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM Fine F JOIN FinePayment FP ON F.Id=FP.FineId 
          JOIN FineArticle FA ON FP.FineId=FA.FineId AND FP.Id= ".$PaymentId ."
         LEFT JOIN FineHistory FH ON FA.FineId = FH.FineId
       
       
        WHERE FA.FineId=" . $FineId . " AND (NotificationTypeId in (6,30) OR NotificationTypeId IS NULL)");


$r_Payment = mysqli_fetch_array($rs_Payment);

trigger_error("pagamento trovato: ".$r_Payment['Name']. " - ".$r_Payment['Code'], E_USER_NOTICE);

if($r_Payment['ReceiptNumber']==0){
    $rs_FinePayment = $rs->SelectQuery("
        SELECT 
        MAX(ReceiptNumber)+1 ReceiptNumber
        FROM FinePayment 
        
        WHERE CityId='".$_SESSION['cityid']."' AND 
        YEAR(PaymentDate)=".date('Y',strtotime($r_Payment['PaymentDate']))
    );


    $r_FinePayment = mysqli_fetch_array($rs_FinePayment);
   
    $ReceiptNumber = $r_FinePayment['ReceiptNumber'];
    
    $a_FinePayment = array(
        array('field'=>'ReceiptNumber','selector'=>'value','type'=>'int','value'=>$ReceiptNumber,'settype'=>'int'),
    );
    $rs->Update('FinePayment',$a_FinePayment, 'Id='.$PaymentId);

    trigger_error("numero ricevuta calcolato: ".$ReceiptNumber, E_USER_NOTICE);
    
}else $ReceiptNumber = $r_Payment['ReceiptNumber'];

trigger_error("numero ricevuta trovato: ".$ReceiptNumber, E_USER_NOTICE);


$NotificationFee = $r_Payment['NotificationFee'] + $r_Payment['CadFee'] + $r_Payment['CanFee'];
$AdditionalFee = $r_Payment['ResearchFee'] + $r_Payment['CustomerFee'];
$Fee = $r_Payment['Fee'];


$TotalFee = $NotificationFee + $AdditionalFee + $Fee;

$Amount = $r_Payment['Amount'];
$PaymentDate = $r_Payment['PaymentDate'];
$Name = $r_Payment['Name'];

$ProtocolId = $r_Payment['ProtocolId'];
$ProtocolYear = $r_Payment['ProtocolYear'];
$Code = $r_Payment['Code'];
$VehiclePlate = $r_Payment['VehiclePlate'];

trigger_error("spese: ".$TotalFee, E_USER_NOTICE);
trigger_error("importo pagato: ".$Amount, E_USER_NOTICE);

$pdf->AddPage();
$pdf->SetFont('arial', '', 9, '', true);

$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

for($i=1; $i<=3; $i++){

    $y = ($i==1) ? 10 : $pdf->getY();
    $pdf->Image($_SESSION['blazon'], 10, $y, 10, 18);

    $pdf->writeHTMLCell(150, 0, 30, '' , $MangerName, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


    $pdf->LN(10);

    $y = $pdf->getY();
    $pdf->SetFont('arial', '', 9, '', true);
    $pdf->writeHTMLCell(30, 4, 10, $y, "Ricevuta n.". $ReceiptNumber , 0, 0, 1, true, 'L', true);

    $pdf->writeHTMLCell(70, 4, 110, $y, $MangerSignName.", ".DateOutDB($PaymentDate), 0, 0, 1, true, 'R', true);

    $pdf->LN(10);
    $y = $pdf->getY();


    $pdf->writeHTMLCell(50, 4, 10, $y, "Importo infrazione", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 60, $y, NumberDisplay($Fee)." €", 0, 0, 1, true, 'R', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Spese notifica", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 60, $y, NumberDisplay($NotificationFee)." €", 0, 0, 1, true, 'R', true);
    $pdf->LN(4);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Spese aggiuntive", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 60, $y, NumberDisplay($AdditionalFee)." €", 0, 0, 1, true, 'R', true);
    $pdf->LN(2);

    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 60, $y, "______________", 0, 0, 1, true, 'R', true);
    $pdf->LN(4);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(50, 4, 10, $y, "Importo totale", 0, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(30, 4, 60, $y, NumberDisplay($TotalFee)." €", 0, 0, 1, true, 'R', true);
    $pdf->LN(10);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(80, 4, 10, $y, "Per oblazione effettuata da ". $Name, 0, 0, 1, true, 'L', true);
    $pdf->LN(6);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(150, 4, 10, $y, "RELATIVA VERBALE CRON. ". $ProtocolId."/".$ProtocolYear . " RIF: ".$Code." VEICOLO TARGA ".$VehiclePlate, 0, 0, 1, true, 'L', true);
    $pdf->LN(6);


    $y = $pdf->getY();
    $pdf->writeHTMLCell(60, 4, 120, $y, "<b>TOTALE QUIETANZA ". NumberDisplay($Amount)." €"."</b>", 0, 0, 1, true, 'R', true);
    $pdf->LN(6);



    $y = $pdf->getY();
    $pdf->writeHTMLCell(120, 4, 10, $y, "L'Incaricato ", 0, 0, 1, true, 'L', true);
    $pdf->LN(20);

}


$FileName = 'export.pdf';

$pdf->Output(ROOT."/doc/print/payment/".$FileName, "F");
$_SESSION['Documentation'] = $MainPath.'/doc/print/payment/'.$FileName;

if ($AddPage != 1){
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_payment.php'), 'mgmt_payment.php'.$Filters));
} else {
    $_SESSION['Message'] = "Azione eseguita con successo.";
    header("location: ".impostaParametriUrl(array('P' => 'mgmt_payment.php'), 'mgmt_payment_viw.php'.$str_GET_Parameter)."&PaymentId=".$PaymentId);
}


