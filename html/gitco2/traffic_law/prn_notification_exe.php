<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";


$FromDate= CheckValue('FromDate','s');
$ToDate= CheckValue('ToDate','s');

$FromProtocolId= CheckValue('FromProtocolId','n');
$ToProtocolId= CheckValue('ToProtocolId','n');

$DateType = CheckValue('DateType','n');

$a_PaymentField  = array("SendDate","NotificationDate","RegDate");

$CurrentYear = CheckValue('CurrentYear','n');


$Locality= CheckValue('Locality','s');





$P = "prn_notification.php?DateType=".$DateType;






$str_Where = "CityId='".$_SESSION['cityid']."'";

$strOrder = "ProtocolYear, ProtocolId";

if($FromDate!=""){
    $str_Where .= " AND ".$a_PaymentField[$DateType].">= '".DateInDB($FromDate)  ."'";
    $str_CurrentPage .="&FromDate=".$FromDate;
}
if($ToDate!=""){
    $str_Where .= " AND ".$a_PaymentField[$DateType]."<= '".DateInDB($ToDate)  ."'";
    $str_CurrentPage .="&ToDate=".$ToDate;
}

if($FromProtocolId>0){
    $str_Where .= " AND ProtocolId >= $FromProtocolId";
    $str_CurrentPage .="&FromProtocolId=".$FromProtocolId;
}

if($ToProtocolId>0){
    $str_Where .= " AND ProtocolId <= $ToProtocolId";
    $str_CurrentPage .="&ToProtocolId=".$ToProtocolId;
}


if($CurrentYear){
    $str_Where .= " AND ProtocolYear =".$_SESSION['year'];
    $str_CurrentPage .="&CurrentYear=".$CurrentYear;
}

if($Locality!=""){
    $str_Where .= " AND Locality = '".$Locality."'";
    $str_CurrentPage .="&Locality=".$Locality;
}

$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('Request');
$pdf->SetSubject('Request');
$pdf->SetKeywords('');


$pdf->SetMargins(10,10,10);

$table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$table_row = mysqli_fetch_array($table_rows);

$MangerName = $table_row['ManagerName'];
$ManagerAddress = $table_row['ManagerAddress'];
$ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
$ManagerPhone = $table_row['ManagerPhone'];




$pdf->AddPage();
$pdf->SetFont('arial', '', 9, '', true);

$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


$pdf->LN(10);
$pdf->SetFont('arial', '', 10, '', true);
$pdf->writeHTMLCell(270, 0, 10, '', "ELENCO NOTIFICHE DAL $FromDate AL $ToDate", 0, 0, 1, true, 'C', true);
$pdf->LN(10);


$y = $pdf->getY();
$pdf->SetFont('arial', '', 9, '', true);
$pdf->writeHTMLCell(10, 4, 20, $y, "", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 30, $y, "Data Invio", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 60, $y, "Data notif", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(40, 4, 90, $y, "Cron", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 120, $y, "Notifica", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 150, $y, "CAN", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 180, $y, "CAD", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 210, $y, "Messo", 1, 0, 1, true, 'C', true);
$pdf->writeHTMLCell(30, 4, 240, $y, "Altro", 1, 0, 1, true, 'C', true);



$pdf->LN(4);


$table_rows = $rs->Select('V_FineNotification',$str_Where, $strOrder);
$RowNumber = mysqli_num_rows($table_rows);

$f_TotCan = 0;
$f_TotCad = 0;
$f_TotNotification = 0;
$f_TotNotifier = 0;
$f_TotOther = 0;


$pdf->SetFont('arial', '', 8, '', true);
$n_Cont = 0;
$n_Row = 0;
$n_ChangePage = 30;

if ($RowNumber == 0) {
    $y = $pdf->getY();
    $pdf->writeHTMLCell(276, 4, 10, $y, "Nessuna notifica presente", 1, 0, 1, true, 'C', true);

} else {
    while ($table_row = mysqli_fetch_array($table_rows)) {


        if($n_Row==$n_ChangePage ){
            $pdf->LN(10);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(30, 4, 120, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(30, 4, 150, $y, NumberDisplay($f_TotCan), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(30, 4, 180, $y, NumberDisplay($f_TotCad), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(30, 4, 210, $y, NumberDisplay($f_TotNotifier), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(30, 4, 240, $y, NumberDisplay($f_TotOther), 1, 0, 1, true, 'R', true);

            $pdf->AddPage();
            $pdf->SetFont('arial', '', 10, '', true);

            $pdf->setFooterData(array(0,64,0), array(0,64,128));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->LN(10);

            $pdf->writeHTMLCell(200, 0, 30, '', "ELENCO NOTIFICHE DAL $FromDate AL $ToDate", 0, 0, 1, true, 'C', true);
            $pdf->LN(10);


            $y = $pdf->getY();
            $pdf->SetFont('arial', '', 9, '', true);
            $pdf->writeHTMLCell(10, 4, 20, $y, "", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 30, $y, "Data Invio", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 60, $y, "Data notif", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(40, 4, 90, $y, "Cron", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 120, $y, "Notifica", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 150, $y, "CAN", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 180, $y, "CAD", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 210, $y, "Messo", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, 240, $y, "Altro", 1, 0, 1, true, 'C', true);
            $pdf->LN(4);


            $pdf->SetFont('arial', '', 8, '', true);
            $n_Row =0;
            $n_ChangePage = 30;

        }

        $n_Row++;
        $n_Cont++;



        $y = $pdf->getY();
        $pdf->writeHTMLCell(10, 4, 20, $y, $n_Cont, 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 30, $y, DateOutDB($table_row['SendDate']), 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 60, $y, DateOutDB($table_row['NotificationDate']) , 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(40, 4, 90, $y, $table_row['ProtocolId'] .'/'.$table_row['ProtocolYear'], 1, 0, 1, true, 'L', true);


        $rs_Row = $rs->SelectQuery("
			SELECT  
			NotificationTypeId,
			NotificationFee,
			ResearchFee,
			CanFee,
			CadFee,
			NotifierFee,
			OtherFee,
			SendDate,
			DeliveryDate,
			ResultId
			FROM FineHistory
			
			WHERE FineId=".$table_row['FineId']." AND NotificationTypeId=6");


        $r_Row = mysqli_fetch_array($rs_Row);

        $NotificatonFee = $r_Row['NotificationFee'] + $r_Row['ResearchFee'];




        $f_TotNotification += $NotificatonFee;
        $f_TotCan += $r_Row['CanFee'];
        $f_TotCad += $r_Row['CadFee'];
        $f_TotNotifier += $r_Row['NotifierFee'];
        $f_TotOther += $r_Row['OtherFee'];


        $pdf->writeHTMLCell(30, 4, 120, $y, NumberDisplay($NotificatonFee), 1, 0, 1, true, 'R', true);
        $pdf->writeHTMLCell(30, 4, 150, $y, NumberDisplay($r_Row['CanFee']), 1, 0, 1, true, 'R', true);
        $pdf->writeHTMLCell(30, 4, 180, $y, NumberDisplay($r_Row['CadFee']), 1, 0, 1, true, 'R', true);
        $pdf->writeHTMLCell(30, 4, 210, $y, NumberDisplay($r_Row['NotifierFee']), 1, 0, 1, true, 'R', true);
        $pdf->writeHTMLCell(30, 4, 240, $y, NumberDisplay($r_Row['OtherFee']), 1, 0, 1, true, 'R', true);


        $pdf->LN(4);
    }

}

$pdf->LN(10);
$y = $pdf->getY();
$pdf->writeHTMLCell(30, 4, 120, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 150, $y, NumberDisplay($f_TotCan), 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 180, $y, NumberDisplay($f_TotCad), 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 210, $y, NumberDisplay($f_TotNotifier), 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 240, $y, NumberDisplay($f_TotOther), 1, 0, 1, true, 'R', true);

$FileName = 'export.pdf';

$pdf->Output(ROOT."/doc/print/payment/".$FileName, "F");
$_SESSION['Documentation'] = $MainPath.'/doc/print/payment/'.$FileName;


header("location: ".$P.$str_CurrentPage);