<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");
require_once TCPDF . "/tcpdf.php";

$strOrder = "CompanyName, Surname, Name";
$str_Where = "1=1 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

if($Search_Status>0){
    if($Search_Status==15){
        $str_Where .= " AND StatusTypeId>=13 AND ProtocolId>0";
        $strOrder = "ProtocolId";
    }else{
        $str_Where .= " AND StatusTypeId<13";
    }
}else $Search_Status=1;

if ($Search_FromFineDate != "") {
    $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
}
if ($Search_ToFineDate != "") {
    $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
}
if ($Search_FromProtocolId != "") {
    $str_Where .= " AND ProtocolId>=$Search_FromProtocolId";
}
if ($Search_ToProtocolId != "") {
    $str_Where .= " AND ProtocolId<=$Search_ToProtocolId";
}
if ($Search_Locality != "") {
    $str_Where .= " AND Locality='$Search_Locality'";
}
switch($s_TypePlate){
    case 'F' : $str_Where .= " AND CountryId != 'Z000'"; break;
    case 'N' : $str_Where .= " AND CountryId = 'Z000'"; break;
}
if($Search_Trespasser != ""){
    $str_Where .= " AND CONCAT_WS(' ',CompanyName,Surname,Name) LIKE '%{$Search_Trespasser}%'";
}
if($Search_HasTaxCode > 0){
    $str_Where .= " AND COALESCE(TaxCode, '') = '' AND COALESCE(VatCode, '') = ''";
}


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

$pdf->AddPage();

$pdf->SetMargins(10,10,10);
$pdf->SetFont('arial', '', 10, '', true);

$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);




$table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$table_row = mysqli_fetch_array($table_rows);

$MangerName = $table_row['ManagerName'];
$ManagerAddress = $table_row['ManagerAddress'];
$ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
$ManagerPhone = $table_row['ManagerPhone'];

$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


$pdf->LN(25);


$table_rows = $rs->Select('V_FineTrespasserList',$str_Where, $strOrder);


$pdf->SetFont('arial', '', 8, '', true);
$Cont = 0;
while ($table_row = mysqli_fetch_array($table_rows)) {

	$Cont++;

	if($Cont==19){
		$Cont=0;
		$pdf->AddPage();

		$pdf->SetMargins(10,10,10);
		$pdf->SetFont('arial', '', 10, '', true);

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


		$pdf->LN(25);


	}

	$FineDate = $table_row['FineDate'];
	$aTime = explode(":",$table_row['FineTime']);
	$FineTime = $aTime[0].":".$aTime[1];


	$VehiclePlate =	$table_row['VehiclePlate'];
	$Address = $table_row['Address'];
	$ZIP = $table_row['ZIP'];
	$City = $table_row['City'];
	$Province = $table_row['Province'];
	$TaxCode = $table_row['TaxCode'];

	$Speed = $table_row['Speed'];

	$Name = $table_row['CompanyName'] .' '.$table_row['Surname'].' '.$table_row['Name'];

	$CompleteAddress = $Address." ".$ZIP." ".$City." (".$Province.")";




	$pdf->SetFont('arial', '', 8, '', true);




	$y = $pdf->getY();
	$pdf->writeHTMLCell(80, 4, 10, $y, $Name, 1, 0, 1, true, 'L', true);
	$pdf->writeHTMLCell(100, 4, 90, $y, $CompleteAddress, 1, 0, 1, true, 'C', true);

	$pdf->LN(4);

	$y = $pdf->getY();
	$pdf->writeHTMLCell(50, 4, 10, $y, $TaxCode, 1, 0, 1, true, 'L', true);
    $pdf->writeHTMLCell(20, 4, 60, $y, $table_row['Article'] .' '.$table_row['Paragraph'].' '.$table_row['Letter'], 1, 0, 1, true, 'L', true);

	$pdf->writeHTMLCell(30, 4, 80, $y, $FineDate, 1, 0, 1, true, 'C', true);
	$pdf->writeHTMLCell(30, 4, 110, $y, $FineTime, 1, 0, 1, true, 'C', true);
	$pdf->writeHTMLCell(30, 4, 140, $y, $VehiclePlate, 1, 0, 1, true, 'C', true);
	$pdf->writeHTMLCell(20, 4, 170, $y, intval($Speed)." Km/h", 1, 0, 1, true, 'C', true);



	$pdf->LN(8);


}



$FileName = 'export.pdf';



$pdf->Output(ROOT.'/doc/print/'.$FileName, "F");
$_SESSION['Documentation'] = $MainPath.'/doc/print/'.$FileName;


header("location: ".impostaParametriUrl(array('Filter' => 1), "prn_trespasser.php".$str_GET_Parameter));