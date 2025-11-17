<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";


$FineId = CheckValue('FineId','n');
$LanguageId = CheckValue('LanguageId','n');
$FormTypeId = 20;


$prefect= CheckValue('prefect','s');



$aLan = unserialize(LANGUAGE);






$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);



foreach ($_POST['checkbox'] as $key=>$TrespasserId) {

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


    $pdf->Line(10, 30, 205, 30);




    $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


    $pdf->LN(35);


    $rs_Trespasser = $rs->Select("V_Trespasser","Id=".$TrespasserId);
    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);


    $pdf->writeHTMLCell(100, 0, 110, '', '<h4>'.substr($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name'],0,35).'</h4>', 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $r_Trespasser['Address'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $r_Trespasser['ZIP'].' '.$r_Trespasser['City']. ' '."(".$r_Trespasser['Province'].")", 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $r_Trespasser['CountryTitle'], 0, 0, 1, true, 'L', true);

    $pdf->LN(30);



    if($prefect=='Y'){
        $rs_office = $rs->Select('JudicialOffice',"OfficeId=2 AND CityId='".$_SESSION['cityid']."'");
        $r_office = mysqli_fetch_array($rs_office);

        $pdf->writeHTMLCell(100, 0, 110, '', 'e p.c. Prefettura di '.$r_office['City'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $r_office['Address'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $r_office['ZIP'].' '.$r_office['City']. ' '."(".$r_office['Province'].")", 0, 0, 1, true, 'L', true);
        $pdf->LN(30);
    }


    $rs_form = $rs->Select('Form',"CityId='".$_SESSION['cityid']."' AND FormTypeId=".$FormTypeId." AND LanguageId=".$LanguageId);
    if(mysqli_num_rows($rs_form)==0){
        $rs_form = $rs->Select('Form',"FormTypeId=".$FormTypeId." AND LanguageId=".$LanguageId);
    }
    $r_form = mysqli_fetch_array($rs_form);



    $Content = $r_form['Content'];

    $rs_Archive = $rs->Select('FineArchive',"FineId=".$FineId);
    $r_Archive = mysqli_fetch_array($rs_Archive);


    $rs_Reason = $rs->Select('Reason',"Id=".$r_Archive['ReasonId']);
    $r_Reason = mysqli_fetch_array($rs_Reason);


    $rs_Fine = $rs->Select('V_FineHistory',"Id=".$FineId." AND NotificationTypeId in (2,15)");
    $r_Fine = mysqli_fetch_array($rs_Fine);

    $Content = str_replace("{ArchiveDate}", DateOutDB($r_Archive['ArchiveDate']) ,$Content);
    $Content = str_replace("{ReasonId}", $r_Reason['Title'.$aLan[$LanguageId]] ,$Content);
    $Content = str_replace("{NotificationDate}", DateOutDB($r_Fine['FineDate']) ,$Content);

    $Content = str_replace("{ProtocolId}", $r_Fine['ProtocolId'] ,$Content);
    $Content = str_replace("{ProtocolYear}", $r_Fine['ProtocolYear'] ,$Content);

    //DA FARE: se nella prima parte della var c'è la "," non aggiungerla
    //o se ce n'è più di una togliere la seconda
    if(is_null($table_row['ManagerSignName'])){
        if($table_row['CityUnion']>1){
            $Content = str_replace("{Date}",$table_row['ManagerCity'].
                (strpos($table_row['ManagerCity'],',')!== false ? " ":", ").DateOutDB($r_Archive['ArchiveDate']),$Content);
        }else{
            $Content = str_replace("{Date}",$table_row['ManagerName'].
                (strpos($table_row['ManagerName'],',')!== false ? " ":", ").DateOutDB($r_Archive['ArchiveDate']),$Content);
        }
    }else{
        $Content = str_replace("{Date}",$table_row['ManagerSignName'].
            (strpos($table_row['ManagerSignName'],',')!== false ? " ":", ").DateOutDB($r_Archive['ArchiveDate']),$Content);
    }

    $aMainPart = explode("<main_part>",$Content);
    $aRow = explode("<row>",$aMainPart[1]);

    $pdf->SetFont('helvetica', '', 10);

    $pdf->writeHTMLCell(190, 0, 20, '', $aRow[3], 0, 0, 1, true, 'J', true);
    $pdf->LN(20);
    $pdf->writeHTMLCell(190, 0, 20, '', $aRow[4], 0, 0, 1, true, 'J', true);
    $pdf->LN(10);
    $pdf->writeHTMLCell(190, 0, 20, '', $aRow[5], 0, 0, 1, true, 'C', true);
    $pdf->LN(35);

    $pdf->writeHTMLCell(190, 0, 20, '', $aRow[6], 0, 0, 1, true, 'L', true);
    $pdf->LN();
    $pdf->writeHTMLCell(160, 0, 20, '', $aRow[7], 0, 0, 1, true, 'R', true);
    $pdf->LN();

}



$FileName = 'export.pdf';



$pdf->Output(ROOT.'/doc/print/'.$FileName, "F");
$_SESSION['Documentation'] = $MainPath.'/doc/print/'.$FileName;


header("location: mgmt_archive.php");