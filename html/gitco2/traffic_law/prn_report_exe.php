<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
ini_set('max_execution_time', 3000);



$FromFineDate   = CheckValue('FromFineDate','s');
$ToFineDate     = CheckValue('ToFineDate','s');

$NotPayed = CheckValue('NotPayed','n');
$LumpSum = CheckValue('LumpSum','n');

$str_Where_FromDate = $str_Where_ToDate = $str_Where_NotPayed = '';




if($FromFineDate!=""){
    $str_Where_FromDate = " AND FH.SendDate>='".DateInDB($FromFineDate)."'";
}
if($ToFineDate!=""){
    $str_Where_ToDate = " AND FH.SendDate<'".DateInDB($ToFineDate)."'";
}
$str_filter = "";
if($NotPayed){
    $str_filter.= "NON PAGATI";
    $str_Where_NotPayed = " AND F.Id NOT IN (SELECT FineId FROM FinePayment)";
}
if($LumpSum){
    $str_filter.= " - CAN e CAD ACCORPATI ";
}







$str_Query = "
    SELECT 
    
        CONCAT (A.Article,' ' ,A.Paragraph, ' ', A.Letter , ' ') ArticleTitle, 
        COUNT(*) Tot,
        SUM(FA.Fee) Fee,
        SUM(FH.CustomerFee) CustomerFee,
        SUM(FH.NotificationFee) NotificationFee,
        SUM(FH.ResearchFee) ResearchFee,
        SUM(FH.CanFee) CanFee,
        SUM(FH.CadFee) CadFee
        
        
        FROM FineArticle FA 
        JOIN Fine F ON FA.FineId=F.Id
        JOIN Article A ON A.Id=FA.ArticleId
        JOIN FineHistory FH ON FH.FineId = F.Id
    
    WHERE
    
        F.CityId='" . $_SESSION['cityid'] . "' AND F.ProtocolYear=" . $_SESSION['year']." AND 
        FH.NotificationTypeId=6 AND FH.SendDate IS NOT NULL ". $str_Where_FromDate . $str_Where_ToDate ."   
        AND ((F.StatusTypeId>=20 AND F.StatusTypeId<=30) OR F.StatusTypeId=12) 
        ". $str_Where_NotPayed ."
        
    GROUP BY    
        A.Article,A.Paragraph, A.Letter
    ";
$rs_Fine = $rs->SelectQuery($str_Query);
$a_fine = $rs->getResults($rs_Fine);
$a_lumpSum['label'] = array();
if($LumpSum){
    $a_lumpSum['label'][] = "CAN+CAD";
}
else{
    $a_lumpSum['label'][] = "CAN";
    $a_lumpSum['label'][] = "CAD";
}

$str_Out = '
            <table>
            <tr>
                <td>VERBALI '.$str_filter.'</td>
            </tr>
            <tr>
                <td> </td>
            </tr>
            <tr>
                <td>REPORT NORMALE</td>
            </tr>
            <tr>

				<td>Articolo</td>
				<td>Quantita\'</td>
				<td>Sanzione</td>
				<td>Notifica</td>
				<td>Ricerca</td>';
foreach($a_lumpSum['label'] as $label)
    $str_Out.= '<td>'.$label.'</td>';
$str_Out.= '
				<td>Totale</td>
		    </tr>
        ';

foreach($a_fine as $r_Fine){




    $flt_Notification = $r_Fine['CustomerFee']+$r_Fine['NotificationFee']+$r_Fine['CanFee']+$r_Fine['CadFee'];
    $a_lumpSum['amount'] = array();
    if($LumpSum){
        $a_lumpSum['amount'][] = $r_Fine['CanFee']+$r_Fine['CadFee'];
    }
    else{
        $a_lumpSum['amount'][] = $r_Fine['CanFee'];
        $a_lumpSum['amount'][] = $r_Fine['CadFee'];
    }


    $flt_Total = $r_Fine['Fee']+$r_Fine['ResearchFee']+$flt_Notification;



    $str_Out .= '

            <tr>

				<td>'.$r_Fine['ArticleTitle'].'</td>
				<td>'.$r_Fine['Tot'].'</td>
				<td>'.NumberDisplay($r_Fine['Fee']).'</td>
				
				<td>'.NumberDisplay($r_Fine['NotificationFee']).'</td>
				<td>'.NumberDisplay($r_Fine['ResearchFee']).'</td>';

    foreach($a_lumpSum['amount'] as $amount)
        $str_Out.= '<td>'.NumberDisplay($amount).'</td>';
    $str_Out .= '
				<td>'.NumberDisplay($flt_Total).'</td>				
            </tr>

        
        ';




}

$str_Out.= '
            <tr>
                <td> </td>
            </tr>
            <tr>
                <td>REPORT RIDOTTO</td>
            </tr>
            <tr>

				<td>Articolo</td>
				<td>Quantita\'</td>
				<td>Sanzione</td>
				<td>Notifica</td>
				<td>Ricerca</td>';
foreach($a_lumpSum['label'] as $label)
    $str_Out.= '<td>'.$label.'</td>';
$str_Out.= '
				<td>Totale</td>
		    </tr>
        ';
foreach($a_fine as $r_Fine){

    $flt_Notification = $r_Fine['CustomerFee']+$r_Fine['NotificationFee']+$r_Fine['CanFee']+$r_Fine['CadFee'];
    $a_lumpSum['amount'] = array();
    if($LumpSum){
        $a_lumpSum['amount'][] = $r_Fine['CanFee']+$r_Fine['CadFee'];
    }
    else{
        $a_lumpSum['amount'][] = $r_Fine['CanFee'];
        $a_lumpSum['amount'][] = $r_Fine['CadFee'];
    }

    $fee = number_format($r_Fine['Fee']*FINE_PARTIAL,2,".","");
    $flt_Total = $fee+$r_Fine['ResearchFee']+$flt_Notification;



    $str_Out .= '

            <tr>

				<td>'.$r_Fine['ArticleTitle'].'</td>
				<td>'.$r_Fine['Tot'].'</td>
				<td>'.NumberDisplay($r_Fine['Fee']).'</td>
				
				<td>'.NumberDisplay($r_Fine['NotificationFee']).'</td>
				<td>'.NumberDisplay($r_Fine['ResearchFee']).'</td>';

    foreach($a_lumpSum['amount'] as $amount)
        $str_Out.= '<td>'.NumberDisplay($amount).'</td>';
    $str_Out .= '
				<td>'.NumberDisplay($flt_Total).'</td>				
            </tr>

        
        ';




}










$filename="export.xls";
    header ("Content-Type: application/vnd.ms-excel");
    header ("Content-Disposition: inline; filename=$filename");


    $str_Out .= '</table>';
    echo $str_Out;
