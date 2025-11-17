<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/function_postalCharge.php");

require(INC."/initialization.php");
ini_set('max_execution_time', 3000);

//TODO Pulire il codice incapsulando i comandi in dei metodi. Così com'è adesso è troppo frammentario

$rs_PaymentType = $rs->Select('sarida.PaymentType', "1=1");
$a_PaymentType = array();
while ($r_PaymentType = mysqli_fetch_array($rs_PaymentType)){
    $a_PaymentType[$r_PaymentType['Id']] = $r_PaymentType['Title'];
}

$n_CSV = CheckValue('CSV','n');
$str_CurrentPage = "";

$n_Cont = 0;

$PageTitle = CheckValue('PageTitle','s');
$str_CurrentPage .= "&PageTitle=".$PageTitle;

$DateType = CheckValue('DateType', 'n'); //Pagamento o Accredito
$FromPaymentDate = CheckValue('FromPaymentDate','s'); //Anche per accredito
$ToPaymentDate = CheckValue('ToPaymentDate','s'); //Anche per accredito

$FromRegDate= CheckValue('FromRegDate','s');
$ToRegDate= CheckValue('ToRegDate','s');

$FromFineDate= CheckValue('FromFineDate','s');
$ToFineDate= CheckValue('ToFineDate','s');


$FromProtocolId= CheckValue('FromProtocolId','n');
$ToProtocolId= CheckValue('ToProtocolId','n');

$FinePaymentSpecificationType = CheckValue('FinePaymentSpecificationType','n');

$FromAmount= str_replace(",",".",CheckValue('FromAmount','s'));
$ToAmount= str_replace(",",".",CheckValue('ToAmount','s'));


$BankMgmt =  CheckValue('BankMgmt','n');

$PaymentTypeId= CheckValue('PaymentTypeId','n');

$a_PaymentField  = array("PaymentDate","RegDate","FineDate");

$CurrentYear = CheckValue('CurrentYear','n');
$ReclaimPayment = CheckValue('ReclaimPayment','n');

$RefundPayment = CheckValue('RefundPayment','n');

$Locality= CheckValue('Locality','s');

$CustomerField = CheckValue('CustomerField','n');


$str_Where = "P.CityId='".$_SESSION['cityid']."'";
$str_DateColumn = $DateType > 0 ? 'COALESCE(P.CreditDate,P.PaymentDate)' : 'P.PaymentDate';
$strOrder = "PaymentDate";


$d_PrintDate = CheckValue('PrintDate','s');



if($FromPaymentDate!=""){
    $str_Where .= " AND $str_DateColumn >= '".DateInDB($FromPaymentDate)  ."'";
    $str_CurrentPage .="&FromPaymentDate=".$FromPaymentDate;
}
if($ToPaymentDate!=""){
    $str_Where .= " AND $str_DateColumn <= '".DateInDB($ToPaymentDate)  ."'";
    $str_CurrentPage .="&ToPaymentDate=".$ToPaymentDate;
}

if($FromRegDate!=""){
    $str_Where .= " AND P.RegDate >= '".DateInDB($FromRegDate)  ."'";
    $str_CurrentPage .="&FromRegDate=".$FromRegDate;
}

if($ToRegDate!=""){
    $str_Where .= " AND P.RegDate <= '".DateInDB($ToRegDate)  ."'";
    $str_CurrentPage .="&ToRegDate=".$ToRegDate;
}

if($FromFineDate!=""){
    $str_Where .= " AND P.FineDate >= '".DateInDB($FromFineDate)  ."'";
    $str_CurrentPage .="&FromFineDate=".$FromFineDate;
}
if($ToFineDate!=""){
    $str_Where .= " AND P.FineDate <= '".DateInDB($ToFineDate)  ."'";
    $str_CurrentPage .="&ToFineDate=".$ToFineDate;
}

if($FromProtocolId>0){
    $str_Where .= " AND P.ProtocolId >= $FromProtocolId";
    $str_CurrentPage .="&FromProtocolId=".$FromProtocolId;
}

if($ToProtocolId>0){
    $str_Where .= " AND P.ProtocolId <= $ToProtocolId";
    $str_CurrentPage .="&ToProtocolId=".$ToProtocolId;
}


if($FromAmount>0){
    $str_Where .= " AND P.Amount >= $FromAmount";
    $str_CurrentPage .="&FromAmount=".$FromAmount;
}

if($ToAmount>0){
    $str_Where .= " AND P.Amount <= $ToAmount";
    $str_CurrentPage .="&ToAmount=".$ToAmount;
}

if($PaymentTypeId>0){
    $str_Where .= ($PaymentTypeId==10) ? " AND (P.PaymentTypeId = 1 OR P.PaymentTypeId = 2 OR P.PaymentTypeId = 18)" : " AND P.PaymentTypeId = " .$PaymentTypeId;

    $str_CurrentPage .="&PaymentTypeId=".$PaymentTypeId;
}

if($CurrentYear){
    $str_Where .= " AND P.ProtocolYear =".$_SESSION['year'];
    $str_CurrentPage .="&CurrentYear=".$CurrentYear;
}

if($Locality!=""){
    $str_Where .= " AND P.Locality = '".$Locality."'";
    $str_CurrentPage .="&Locality=".$Locality;
}


if($ReclaimPayment){
    $str_CurrentPage .="&ReclaimPayment=".$ReclaimPayment;
}

if($RefundPayment){
    $str_CurrentPage .="&RefundPayment=".$RefundPayment;
}

$str_CurrentPage .="&BankMgmt=".$BankMgmt;
if($BankMgmt==1){
    $str_Where .= " AND P.BankMgmt = 1";
}else if($BankMgmt==2){
    $str_Where .= " AND P.BankMgmt = 0";
}

if($CustomerField) {
    $str_CurrentPage .="&CustomerField=".$CustomerField;
}

$str_fineArticle = '';
if ($Search_Violation>0) {
    $str_fineArticle = "INNER JOIN FineArticle FA on FA.FineId = P.FineId "; //aggiungere la cityId?
    
    $str_Where .= " AND FA.ViolationTypeId=".$Search_Violation;
    $str_CurrentPage .="&Search_Violation=".$Search_Violation;
    
    if($Search_Detector>0)
    {
        $str_Where .= " AND FA.DetectorId=".$Search_Detector;
        $str_CurrentPage .="&Search_Detector=".$Search_Detector;
    }
}

// parte creazione file Excel
if($n_CSV){
    $filename="export.xls";
    header ("Content-Type: application/vnd.ms-excel");
    header ("Content-Disposition: inline; filename=$filename");


    $str_Content = '
        <table border="1">
	        <tr>
	            <th bgcolor="#f7f676"></th>
	            <th bgcolor="#f7f676">Data pag.</th>
                <th bgcolor="#f7f676">Data accr.</th>
	    ';

    if($_SESSION['userlevel']>0 && $CustomerField==0){
        $str_Content .= '

                <th bgcolor="#f7f676">Metodo</th>
                <th bgcolor="#f7f676" colspan="2">Nominativo</th>
                <th bgcolor="#f7f676" colspan="2">Causale</th>
                <th bgcolor="#f7f676">Cron</th>
                <th bgcolor="#f7f676" align="right">Sanzione</th>
                <th bgcolor="#f7f676" align="right">Ricerca</th>
                <th bgcolor="#f7f676" align="right">Notifica</th>
                <th bgcolor="#f7f676" align="right">Spese Comune</th>
                <th bgcolor="#f7f676" align="right">Maggiorazione</th>
                <th bgcolor="#f7f676" align="right">Riscosso</th>
                <th bgcolor="#f7f676" align="right">Data notifica</th>
                
                ';
    } else {
        $str_Content .= '
                <th bgcolor="#f7f676" colspan="2">Nominativo</th>
                <th bgcolor="#f7f676" colspan="2">Causale</th>
                <th bgcolor="#f7f676">Cron</th>
                <th bgcolor="#f7f676">Ref</th>
                <th bgcolor="#f7f676">Targa</th>
                <th bgcolor="#f7f676" align="right">Sanzione</th>
                <th bgcolor="#f7f676" align="right">Notifica/Ricerca</th>
                <th bgcolor="#f7f676" align="right">Spese Comune</th>
                <th bgcolor="#f7f676" align="right">Maggiorazione</th>
                <th bgcolor="#f7f676" align="right">Riscosso</th> 
                <th bgcolor="#f7f676" align="right">Data notifica</th>
                ';
    }

    $str_Content .= '
        </tr>
	';

    $f_TotAmount=0;
    $f_TotFee = 0;
    $f_TotNotification = 0;
    $f_TotResearch = 0;
    $f_TotCustomer = 0;
    $f_TotNotificationResearch = 0;

    //selezione pagamenti "Non assiociati" è Escludi = 0 o Includi = 1 (e non "solo loro" che è 2)
    if($ReclaimPayment<2){
        
        $str_query = "SELECT P.* FROM V_FinePayment P";
        $rs_Payment = $rs->SelectQuery(
            "$str_query $str_fineArticle WHERE $str_Where ORDER BY $strOrder");


        if (mysqli_num_rows($rs_Payment) == 0) {
            $str_Content .= '
            <tr>
	            <td colspan="11">Nessun pagamento presente</td>
	        </tr>    
	    ';

        } else {
            while ($r_Payment = mysqli_fetch_array($rs_Payment)) {
                $n_Cont++;
                
                $Amount = $r_Payment['Amount'];
                $f_TotAmount +=$Amount;


                $CustomerFee = $r_Payment['CustomerFee'];
                $NotificationFee =  $r_Payment['NotificationFee'] + $r_Payment['CanFee'] + $r_Payment['CadFee'];
                $PercentualFee = $r_Payment['PercentualFee'];
                $ResearchFee = $r_Payment['ResearchFee'];
                $Fee = $r_Payment['Fee'];
                
                $Causale = $r_Payment['Note'];

                $f_CustomerNotificationResearch = $NotificationFee + $ResearchFee;
                $f_TotFee += $Fee;
                $f_TotNotification += $NotificationFee;
                $f_TotPercentual += $PercentualFee;
                $f_TotResearch += $ResearchFee;
                $f_TotCustomer += $CustomerFee;

                $f_TotNotificationResearch += $f_CustomerNotificationResearch;

                $str_Content .= '<tr>';

                $str_Content .= '<td>'.$n_Cont.'</td>';
                $str_Content .= '<td>'.DateOutDB($r_Payment['PaymentDate']).'</td>';
                $str_Content .= '<td>'.DateOutDB($r_Payment['CreditDate']).'</td>';

                $str_FineLetter = ($r_Payment['FineCountryId']=="Z000") ? "U" : "ES";
                $str_PaymentName = (strlen(trim($r_Payment['PaymentName']))==0) ? $r_Payment['CompanyName']." ".$r_Payment['Surname']." ".$r_Payment['Name'] : $r_Payment['PaymentName'];

                if($_SESSION['userlevel']>0 && $CustomerField==0){
                    $str_Content .= '<td>'.$r_Payment['PaymentTypeTitle'].'</td>';
                    $str_Content .= '<td colspan="2">'.$str_PaymentName.'</td>';
                    $str_Content .= '<td colspan="2">' . $Causale . '</td>';
                    $str_Content .= '<td>'.$r_Payment['ProtocolId'] .'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter.'</td>';

                    $str_Content .= '<td>'.NumberDisplay($Fee).'</td>';
                    $str_Content .= '<td>'.NumberDisplay($ResearchFee).'</td>';
                    $str_Content .= '<td>'.NumberDisplay($NotificationFee).'</td>';
                } else {
                    $str_Content .= '<td>'.$str_PaymentName.'</td>';
                    $str_Content .= '<td>'.$r_Payment['ProtocolId'] .'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter.'</td>';
                    $str_Content .= '<td>'.$r_Payment['Code'].'</td>';
                    $str_Content .= '<td>'.$r_Payment['VehiclePlate'].'</td>';

                    $str_Content .= '<td>'.NumberDisplay($Fee).'</td>';
                    $str_Content .= '<td>'.NumberDisplay($f_CustomerNotificationResearch).'</td>';
                }

                $str_Content .= '<td>'.NumberDisplay($CustomerFee).'</td>';
                $str_Content .= '<td>'.NumberDisplay($PercentualFee).'</td>';
                $str_Content .= '<td>'.NumberDisplay($r_Payment['Amount']).'</td>';
                $str_Content .= '<td>' . DateOutDB($r_Payment['NotificationDate']) . '</td>';
                $str_Content .= '</tr>';
            }
        }
    }


    //selezione pagamenti "Non assiciati"  Includi = 1 o "sol loro" che è 2 (e non  Escludi = 0) 
    if($ReclaimPayment>0){
        $NotificationFee = 0;

        $charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
        $charge_row = mysqli_fetch_array($charge_rows);


        $TotalChargeForeign = ($charge_row['ForeignTotalFee']>0) ? $charge_row['ForeignTotalFee'] : $charge_row['ForeignNotificationFee'] + $charge_row['ForeignResearchFee'];
        $TotalChargeNational = ($charge_row['NationalTotalFee']>0) ? $charge_row['NationalTotalFee'] : $charge_row['NationalNotificationFee'] + $charge_row['NationalResearchFee'];

        if($TotalChargeForeign==0){
            if($charge_row['NationalTotalFee']>0){
                $ResearchFee = 0;
                $NotificationFee = $charge_row['NationalTotalFee'];

            }else{
                $ResearchFee = $charge_row['NationalResearchFee'];
                $NotificationFee = $charge_row['NationalNotificationFee'];
            }

        } else {
            if($charge_row['ForeignTotalFee']>0){
                $ResearchFee = 0;
                $NotificationFee = $charge_row['ForeignTotalFee'];

            }else{
                $ResearchFee = $charge_row['ForeignResearchFee'];
                $NotificationFee = $charge_row['ForeignNotificationFee'];
            }
        }

        $strReclaimWhere = "";
        $str_DateColumn = $DateType > 0 ? 'COALESCE(CreditDate,PaymentDate)' : 'PaymentDate';
        
        if($PaymentTypeId>0){
            $strReclaimWhere .= ($PaymentTypeId==10) ? " AND (PaymentTypeId = 1 OR PaymentTypeId = 2 OR PaymentTypeId = 18)" : " AND PaymentTypeId = " .$PaymentTypeId;
        }
        if($FromAmount>0){
            $strReclaimWhere .= " AND Amount >= $FromAmount";
        }

        if($ToAmount>0){
            $strReclaimWhere .= " AND Amount <= $ToAmount";
        }
        
        if($BankMgmt==1){
            $strReclaimWhere .= " AND BankMgmt = 1";
        }else if($BankMgmt==2){
            $strReclaimWhere .= " AND BankMgmt = 0";
        }

        if($FromPaymentDate!=""){
            $strReclaimWhere .= " AND $str_DateColumn >= '".DateInDB($FromPaymentDate)  ."'";
            $str_CurrentPage .="&FromPaymentDate=".$FromPaymentDate;
        }
        if($ToPaymentDate!=""){
            $strReclaimWhere .= " AND $str_DateColumn <= '".DateInDB($ToPaymentDate)  ."'";
            $str_CurrentPage .="&ToPaymentDate=".$ToPaymentDate;
        }

        if($FromRegDate!=""){
            $strReclaimWhere .= " AND RegDate >= '".DateInDB($FromRegDate)  ."'";
            $str_CurrentPage .="&FromRegDate=".$FromRegDate;
        }

        if($ToRegDate!=""){
            $strReclaimWhere .= " AND RegDate <= '".DateInDB($ToRegDate)  ."'";
            $str_CurrentPage .="&ToRegDate=".$ToRegDate;
        }
        
        $str_fineArticleReclaim = '';
        if ($Search_Violation>0) {
            $str_fineArticleReclaim = "INNER JOIN FineArticle FA on FA.FineId = FP.FineId "; //aggiungere la cityId?
            
            $strReclaimWhere .= " AND FA.ViolationTypeId=".$Search_Violation;
                       
            if($Search_Detector>0)
            {
                $strReclaimWhere .= " AND FA.DetectorId=".$Search_Detector;
            }
        }

        $str_queryReclaim = "SELECT FP.*, PT.Title PaymentTypeTitle
              FROM FinePayment FP
              JOIN sarida.PaymentType PT ON FP.PaymentTypeId = PT.Id";

        $rs_ReclaimPayment = $rs->SelectQuery(
            "$str_queryReclaim $str_fineArticleReclaim WHERE FP.CityId='" . $_SESSION['cityid'] . "' AND FP.FineId=0". $strReclaimWhere ." ORDER BY PaymentDate");

//         VECCHIA VERSIONE
//         $rs_ReclaimPayment = $rs->SelectQuery("SELECT FP.*, PT.Title PaymentTypeTitle
//               FROM FinePayment FP
//               JOIN sarida.PaymentType PT ON FP.PaymentTypeId = PT.Id
//               WHERE FP.CityId='" . $_SESSION['cityid'] . "' AND FP.FineId=0".$strReclaimWhere);
        
        while ($r_ReclaimPayment = mysqli_fetch_array($rs_ReclaimPayment)) {
            $n_Cont++;

            if ($NotificationFee == 0) {
                $r_PostalCharge=getPostalCharge($_SESSION['cityid'],$r_ReclaimPayment['PaymentDate']);
                $NotificationFee = $r_PostalCharge['Zone1'];
            }


            $Amount = $r_ReclaimPayment['Amount'];
            $f_TotAmount += $Amount;

            $CustomerFee = $r_ReclaimPayment['CustomerFee'];
            $NotificationFee =  $r_ReclaimPayment['NotificationFee'] + $r_ReclaimPayment['CanFee'] + $r_ReclaimPayment['CadFee'];
            $PercentualFee = $r_ReclaimPayment['PercentualFee'];
            $ResearchFee = $r_ReclaimPayment['ResearchFee'];
            $Fee = $r_ReclaimPayment['Fee'];

            $f_CustomerNotificationResearch = $NotificationFee + $ResearchFee;

            $f_TotFee += $Fee;
            $f_TotNotification += $NotificationFee;
            $f_TotPercentual += $PercentualFee;
            $f_TotResearch += $ResearchFee;
            $f_TotCustomer += $CustomerFee;

            $f_TotNotificationResearch += $f_CustomerNotificationResearch;




            $str_Content .= '<tr>';

            $str_Content .= '<td>' . $n_Cont . '</td>';
            $str_Content .= '<td>' . DateOutDB($r_ReclaimPayment['PaymentDate']) . '</td>';
            $str_Content .= '<td>'.DateOutDB($r_ReclaimPayment['CreditDate']).'</td>';


            $str_Name = (trim($r_ReclaimPayment['Name'])=="") ? "DA ASSOCIARE" : trim($r_ReclaimPayment['Name']);


            $str_Content .= '<td>' . $r_ReclaimPayment['PaymentTypeTitle'] . '</td>';
            $str_Content .= '<td colspan="2">' . $str_Name . '</td>';
            $str_Content .= '<td colspan="2">' . $r_ReclaimPayment['Note'] . '</td>';
            $str_Content .= '<td></td>';
            $str_Content .= '<td>' . NumberDisplay($Fee) . '</td>';
            $str_Content .= '<td>' . NumberDisplay($ResearchFee) . '</td>';
            $str_Content .= '<td>' . NumberDisplay($NotificationFee) . '</td>';


            $str_Content .= '<td>'.NumberDisplay($CustomerFee).'</td>';
            $str_Content .= '<td>'.NumberDisplay($PercentualFee).'</td>';
            $str_Content .= '<td>' . NumberDisplay($r_ReclaimPayment['Amount']) . '</td>';
            $str_Content .= '<td></td>';

            $str_Content .= '</tr>';
        }
    }

    if($RefundPayment){
        
        $str_queryRefund = "SELECT SUM(FR.Amount) Tot FROM FineRefund FR ";
        $str_RefundWhere = "FR.CityId='".$_SESSION['cityid']."'";
        
        if($FromPaymentDate!=""){
            $str_RefundWhere .= " AND FR.RefundDate >= '".DateInDB($FromPaymentDate)  ."'";
        }
        if($ToPaymentDate!=""){
            $str_RefundWhere .= " AND FR.RefundDate <= '".DateInDB($ToPaymentDate)  ."'";
        }
        
        $str_fineArticleRefund = '';
        if ($Search_Violation>0) {
            $str_fineArticleRefund = "INNER JOIN FineArticle FA on FA.FineId = FR.FineId "; //aggiungere la cityId?
            
            $str_RefundWhere .= " AND FA.ViolationTypeId=".$Search_Violation;
            
            if($Search_Detector>0)
            {
                $str_RefundWhere .= " AND FA.DetectorId=".$Search_Detector;
            }
        }
        
        $rs_RefundPayment = $rs->SelectQuery(
            "$str_queryRefund $str_fineArticleRefund
            WHERE $str_RefundWhere");
      
        //vecchia versione che filtrava solo per data pagament
//         $rs_RefundPayment = $rs->SelectQuery("SELECT SUM(Amount) Tot 
//               FROM FineRefund 
//               WHERE CityId='".$_SESSION['cityid']."' AND RefundDate >= '".DateInDB($FromPaymentDate)  ."' 
//               AND RefundDate <= '".DateInDB($ToPaymentDate)  ."'");
        
        
        $r_RefundPayment = mysqli_fetch_array($rs_RefundPayment);

        $str_Content.= '<td>TOTALI VERBALI RIMBORSATI</td>';
        $str_Content.= '<td>&nbsp;</td>';
        $str_Content.= '<td>' . NumberDisplay($r_RefundPayment['Tot']) .'</td>';

        $f_TotFee = $f_TotFee - $r_RefundPayment['Tot'];
        $f_TotAmount = $f_TotAmount - $r_RefundPayment['Tot'];
    }

    $str_Content .= '<tr></tr>';
    $str_Content .= '<th bgcolor="#f7f676" colspan="9">TOTALE</th>';
    $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotFee).'</th>';

    if($_SESSION['userlevel']>0 && $CustomerField==0){
        $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotResearch).'</th>';
        $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotNotification).'</th>';
    } else {
        $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotNotificationResearch).'</th>';

    }

    $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotCustomer).'</th>';
    $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotPercentual).'</th>';
    $str_Content .= '<th bgcolor="#f7f676">'.NumberDisplay($f_TotAmount).'</th>';
    $str_Content .= '<th bgcolor="#f7f676"></th>';

    $str_Content .= '</table>';
    echo $str_Content;
}
else // parte creazione file pdf
{
    include_once TCPDF . "/tcpdf.php";

    $P = "prn_payment.php?DateType=".$DateType;

    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

    //*******NUOVA PAGINA - INTESTAZIONE******
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Request');
    $pdf->SetSubject('Request');
    $pdf->SetKeywords('');


    $pdf->SetMargins(10,10,10);

    $r_Payments = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
    $r_Payment = mysqli_fetch_array($r_Payments);

    $MangerName = $r_Payment['ManagerName'];
    $ManagerAddress = $r_Payment['ManagerAddress'];
    $ManagerCity = $r_Payment['ManagerZIP']." ".$r_Payment['ManagerCity']." (".$r_Payment['ManagerProvince'].")";
    $ManagerPhone = $r_Payment['ManagerPhone'];


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



    $pdf->writeHTMLCell(270, 0, 10, '', "DATA STAMPA $d_PrintDate", 0, 0, 1, true, 'C', true);
    $pdf->LN(10);

    $pdf->writeHTMLCell(270, 0, 10, '', "ELENCO PAGAMENTI DAL $FromPaymentDate AL $ToPaymentDate (Per ".($DateType > 0 ? 'data accredito' : 'data pagamento').")", 0, 0, 1, true, 'C', true);
    $pdf->LN(10);
    //****************************************

    $str_GET_Parameter = "";

    if($FromProtocolId>0){
        $str_GET_Parameter .= "Dal CRON ".$FromProtocolId." ";
    }

    if($ToProtocolId>0){
        $str_GET_Parameter .= "Al CRON ".$ToProtocolId;
    }

    if($str_GET_Parameter != ""){
        $pdf->writeHTMLCell(270, 0, 10, '', $str_GET_Parameter, 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $str_GET_Parameter = "";
    }


    if($FromAmount>0){
        $str_GET_Parameter .= "Da EURO ".$FromAmount." ";
    }

    if($ToAmount>0){
        $str_GET_Parameter .= "A EURO ".$ToAmount;
    }

    if($str_GET_Parameter != ""){
        $pdf->writeHTMLCell(270, 0, 10, '', $str_GET_Parameter, 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $str_GET_Parameter = "";
    }


    if($PaymentTypeId>0){
        $str_GET_Parameter .= "Pagamento ".$a_PaymentType[$PaymentTypeId];

        $pdf->writeHTMLCell(270, 0, 10, '', $str_GET_Parameter, 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $str_GET_Parameter = "";
    }

    if($CurrentYear){
        $str_GET_Parameter .= "Anno corrente ";

        $pdf->writeHTMLCell(270, 0, 10, '', $str_GET_Parameter, 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $str_GET_Parameter = "";
    }


    if($ReclaimPayment==1){
        $str_GET_Parameter .= "Pagamenti non associati compresi";
    }

    if($ReclaimPayment==2){
        $str_GET_Parameter .= "Solo pagamenti non associati";
    }

    if($str_GET_Parameter != ""){
        $pdf->writeHTMLCell(270, 0, 10, '', $str_GET_Parameter, 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $str_GET_Parameter = "";
    }




    if($RefundPayment==1){
        $str_GET_Parameter .= "Rimborsi compresi";
    }

    if($RefundPayment==2){
        $str_GET_Parameter .= "Solo Rimborsi";
    }

    if($str_GET_Parameter != ""){
        $pdf->writeHTMLCell(270, 0, 10, '', $str_GET_Parameter, 0, 0, 1, true, 'C', true);
        $pdf->LN(10);

        $str_GET_Parameter = "";
    }



    //*******NUOVA PAGINA - INTESTAZIONE******
    $pdf->AddPage();
    $pdf->SetFont('arial', '', 7, '', true);

    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

//     $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




//     $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
//     $pdf->LN(4);
//     $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
//     $pdf->LN(4);
//     $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
//     $pdf->LN(4);
//     $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


    $pdf->LN(10);
    $pdf->SetFont('arial', '', 8, '', true);
    $pdf->writeHTMLCell(270, 0, 10, '', "ELENCO PAGAMENTI DAL $FromPaymentDate AL $ToPaymentDate (Per ".($DateType > 0 ? 'data accredito' : 'data pagamento').")", 0, 0, 1, true, 'C', true);
    $pdf->LN(10);
    //*************************

    $f_TotAmount=0;
    $f_TotFee = 0;
    $f_TotNotification = 0;
    $f_TotResearch = 0;
    $f_TotCustomer = 0;
    $f_TotNotificationResearch = 0;

    $n_Cont = 0;
    $n_Row = 0;

    $n_ChangePage = 30;

    //Non associati --> Escludi ed Includi
    if($ReclaimPayment<2){
        
        //********TABELLA - INTESTAZIONE*******
        $y = $pdf->getY();
        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(20, 4, '', '', "Data pag.", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(20, 4, '', '', "Data accr.", 1, 0, 1, true, 'C', true);
        
        if($_SESSION['userlevel']>0 && $CustomerField==0){
            $pdf->writeHTMLCell(30, 4, '', '', "Metodo", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(50, 4, '', '', "Nominativo", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(16, 4, '', '', "Ricerca", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(16, 4, '', '', "Notifica", 1, 0, 1, true, 'C', true);
        } else {
            $pdf->writeHTMLCell(50, 4, '', '', "Ref comune", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(50, 4, '', '', "Targa/Nominativo", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(22, 4, '', '', "Notifica/Ricerca", 1, 0, 1, true, 'C', true);
        }
        
        
        $pdf->writeHTMLCell(16, 4, '', '', "Spese", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(16, 4, '', '', "Magg", 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(22, 4, '', '', "Riscosso", 1, 0, 1, true, 'C', true);
        
        
        $pdf->LN(4);
        
        //*****************************
        
        $str_query = "SELECT P.* FROM V_FinePayment P";
        $r_Payments = $rs->SelectQuery(
            "$str_query $str_fineArticle WHERE $str_Where ORDER BY $strOrder");
        
        //$r_Payments = $rs->Select('V_FinePayment',$str_Where, $strOrder);
        $RowNumber = mysqli_num_rows($r_Payments);


        $pdf->SetFont('arial', '', 8, '', true);

        if ($RowNumber == 0) {
            $y = $pdf->getY();
            $pdf->writeHTMLCell(276, 4, 10, $y, "Nessun pagamento presente", 1, 0, 1, true, 'C', true);

        } else {
            while ($r_Payment = mysqli_fetch_array($r_Payments)) {



                //Totalizzatori
                if($n_Row==$n_ChangePage ){
                    $pdf->LN(10);

                    $y = $pdf->getY();
                    if($_SESSION['userlevel']>0 && $CustomerField==0){
                        $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 192, $y, NumberDisplay($f_TotResearch), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 208, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
                        
                        $pdf->writeHTMLCell(16, 4, 224, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 240, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 256, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
                    } else {
                        $pdf->writeHTMLCell(22, 4, 190, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 212, $y, NumberDisplay($f_TotNotificationResearch), 1, 0, 1, true, 'R', true);
                        
                        $pdf->writeHTMLCell(16, 4, 234, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 250, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 266, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
                    }
                    

                    $pdf->AddPage();
                    $pdf->SetFont('arial', '', 8, '', true);

                    $pdf->setFooterData(array(0,64,0), array(0,64,128));
                    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetTextColor(0, 0, 0);

                    $pdf->LN(10);

                    $pdf->writeHTMLCell(270, 0, 10, '', "ELENCO PAGAMENTI DAL $FromPaymentDate AL $ToPaymentDate (Per ".($DateType > 0 ? 'data accredito' : 'data pagamento').")", 0, 0, 1, true, 'C', true);
                    $pdf->LN(10);




                    $y = $pdf->getY();
                    $pdf->SetFont('arial', '', 8, '', true);
                    $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
                    $pdf->writeHTMLCell(20, 4, '', '', "Data pag", 1, 0, 1, true, 'C', true);
                    $pdf->writeHTMLCell(20, 4, '', '', "Data accr.", 1, 0, 1, true, 'C', true);
                    if($_SESSION['userlevel']>0 && $CustomerField==0){
                        $pdf->writeHTMLCell(30, 4, '', '', "Metodo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(50, 4, '', '', "Nominativo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(16, 4, '', '', "Ricerca", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(16, 4, '', '', "Notifica", 1, 0, 1, true, 'C', true);
                    } else {
                        $pdf->writeHTMLCell(50, 4, '', '', "Ref comune", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(50, 4, '', '', "Targa/Nominativo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Notifica/Ricerca", 1, 0, 1, true, 'C', true);
                    }
                    $pdf->writeHTMLCell(16, 4, '', '', "Spese", 1, 0, 1, true, 'C', true);
                    $pdf->writeHTMLCell(16, 4, '', '', "Magg", 1, 0, 1, true, 'C', true);
                    $pdf->writeHTMLCell(22, 4, '', '', "Riscosso", 1, 0, 1, true, 'C', true);
                    $pdf->LN(4);


                    $pdf->SetFont('arial', '', 8, '', true);
                    $n_Row =0;
                    $n_ChangePage = 30;

                }

                $n_Row++;
                $n_Cont++;


                $Amount = $r_Payment['Amount'];
                $f_TotAmount +=$Amount;


                $y = $pdf->getY();




                $CustomerFee = $r_Payment['CustomerFee'];
                $NotificationFee =  $r_Payment['NotificationFee'] + $r_Payment['CanFee'] + $r_Payment['CadFee'];
                $PercentualFee = $r_Payment['PercentualFee'];
                $ResearchFee = $r_Payment['ResearchFee'];
                $Fee = $r_Payment['Fee'];


                $f_CustomerNotificationResearch = $NotificationFee + $ResearchFee;
                $f_TotFee += $Fee;
                $f_TotNotification += $NotificationFee;
                $f_TotPercentual += $PercentualFee;
                $f_TotResearch += $ResearchFee;
                $f_TotCustomer += $CustomerFee;

                $f_TotNotificationResearch += $f_CustomerNotificationResearch;

                $pdf->writeHTMLCell(10, 4, 10, $y, $n_Cont, 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, '', '', DateOutDB($r_Payment['PaymentDate']), 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, '', '', DateOutDB($r_Payment['CreditDate']), 1, 0, 1, true, 'C', true);
                
                $str_FineLetter = ($r_Payment['FineCountryId']=="Z000") ? "U" : "ES";

                $maxNameLength = 45;
                $str_PaymentName = (strlen(trim($r_Payment['PaymentName']))==0) ? $r_Payment['CompanyName']." ".$r_Payment['Surname']." ".$r_Payment['Name'] : $r_Payment['PaymentName'];

                if(strlen($str_PaymentName) > $maxNameLength) {
                    $str_PaymentName = preg_replace("/^(.{1,$maxNameLength})(\s.*|$)/s", '\\1...', $str_PaymentName);
                }



                if($_SESSION['userlevel']>0 && $CustomerField==0){
                    
                    $pdf->writeHTMLCell(30, 4, '', '', $r_Payment['PaymentTypeTitle'], 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(50, 4, '', '', mb_strlen($str_PaymentName) > 25 ? mb_substr($str_PaymentName, 0, 25)."..." : $str_PaymentName, 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(30, 4, '', '', $r_Payment['ProtocolId'] .'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter, 1, 0, 1, true, 'C', true);

                    $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($Fee), 1, 0, 1, true, 'R', true);
                    $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($ResearchFee), 1, 0, 1, true, 'R', true);
                    $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($NotificationFee), 1, 0, 1, true, 'R', true);

                } else {
                    
                    $pdf->writeHTMLCell(50, 4, '', '', $r_Payment['Code'], 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(50, 4, '', '', mb_strlen($str_PaymentName) > 25 ? mb_substr($str_PaymentName, 0, 25)."..." : $str_PaymentName, 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(30, 4, '', '', $r_Payment['ProtocolId'] .'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter, 1, 0, 1, true, 'C', true);
                    $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($Fee), 1, 0, 1, true, 'R', true);
                    $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($f_CustomerNotificationResearch), 1, 0, 1, true, 'R', true);
                }


                $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($CustomerFee), 1, 0, 1, true, 'R', true);
                $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($PercentualFee), 1, 0, 1, true, 'R', true);
                $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($r_Payment['Amount']), 1, 0, 1, true, 'R', true);


                $pdf->LN(4);
            }

        }
    }

    
    if($ReclaimPayment>0){
        //Stampa l'intestazione dei non associati solo se è selezionato "Solo loro"
        if($ReclaimPayment==2){
            //********TABELLA - INTESTAZIONE*******
            $y = $pdf->getY();
            $pdf->SetFont('arial', '', 8, '', true);
            $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(20, 4, '', '', "Data pag.", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(20, 4, '', '', "Data accr.", 1, 0, 1, true, 'C', true);
            
            if($_SESSION['userlevel']>0 && $CustomerField==0){
                $pdf->writeHTMLCell(30, 4, '', '', "Metodo", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(80, 4, '', '', "Nominativo", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(16, 4, '', '', "Ricerca", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(16, 4, '', '', "Notifica", 1, 0, 1, true, 'C', true);
            } else {
                $pdf->writeHTMLCell(110, 4, '', '', "Targa/Nominativo", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(22, 4, '', '', "Notifica/Ricerca", 1, 0, 1, true, 'C', true);
            }
            
            
            $pdf->writeHTMLCell(16, 4, '', '', "Spese", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(16, 4, '', '', "Magg", 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(22, 4, '', '', "Riscosso", 1, 0, 1, true, 'C', true);
            
            
            $pdf->LN(4);
            
            //*****************************
        }
        
        $NotificationFee = 0;

        $charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
        $charge_row = mysqli_fetch_array($charge_rows);


        $TotalChargeForeign = ($charge_row['ForeignTotalFee']>0) ? $charge_row['ForeignTotalFee'] : $charge_row['ForeignNotificationFee'] + $charge_row['ForeignResearchFee'];
        $TotalChargeNational = ($charge_row['NationalTotalFee']>0) ? $charge_row['NationalTotalFee'] : $charge_row['NationalNotificationFee'] + $charge_row['NationalResearchFee'];

        if($TotalChargeForeign==0){
            if($charge_row['NationalTotalFee']>0){
                $ResearchFee = 0;
                $NotificationFee = $charge_row['NationalTotalFee'];

            }else{
                $ResearchFee = $charge_row['NationalResearchFee'];
                $NotificationFee = $charge_row['NationalNotificationFee'];
            }

        } else {
            if($charge_row['ForeignTotalFee']>0){
                $ResearchFee = 0;
                $NotificationFee = $charge_row['ForeignTotalFee'];

            }else{
                $ResearchFee = $charge_row['ForeignResearchFee'];
                $NotificationFee = $charge_row['ForeignNotificationFee'];
            }
        }

        $strReclaimWhere = "";
        $str_DateColumn = $DateType > 0 ? 'COALESCE(CreditDate,PaymentDate)' : 'PaymentDate';
        
        if($PaymentTypeId>0){
            $strReclaimWhere .= ($PaymentTypeId==10) ? " AND (PaymentTypeId = 1 OR PaymentTypeId = 2 OR PaymentTypeId = 18)" : " AND PaymentTypeId = " .$PaymentTypeId;
        }
        if($FromAmount>0){
            $strReclaimWhere .= " AND Amount >= $FromAmount";
        }

        if($ToAmount>0){
            $strReclaimWhere .= " AND Amount <= $ToAmount";
        }
        if($BankMgmt==1){
            $strReclaimWhere .= " AND BankMgmt = 1";

        }else if($BankMgmt==2){
            $strReclaimWhere .= " AND BankMgmt = 0";

        }

        if($FromPaymentDate!=""){
            $strReclaimWhere .= " AND $str_DateColumn >= '".DateInDB($FromPaymentDate)  ."'";
            $str_CurrentPage .="&FromPaymentDate=".$FromPaymentDate;
        }
        if($ToPaymentDate!=""){
            $strReclaimWhere .= " AND $str_DateColumn <= '".DateInDB($ToPaymentDate)  ."'";
            $str_CurrentPage .="&ToPaymentDate=".$ToPaymentDate;
        }

        if($FromRegDate!=""){
            $strReclaimWhere .= " AND RegDate >= '".DateInDB($FromRegDate)  ."'";
            $str_CurrentPage .="&FromRegDate=".$FromRegDate;
        }

        if($ToRegDate!=""){
            $strReclaimWhere .= " AND RegDate <= '".DateInDB($ToRegDate)  ."'";
            $str_CurrentPage .="&ToRegDate=".$ToRegDate;
        }

        if($FromFineDate!=""){
            $str_CurrentPage .="&FromFineDate=".$FromFineDate;
        }
        if($ToFineDate!=""){
            $str_CurrentPage .="&ToFineDate=".$ToFineDate;
        }
        
        $str_fineArticleReclaim = '';
        if ($Search_Violation>0) {
            $str_fineArticleReclaim = "INNER JOIN FineArticle FA on FA.FineId = FP.FineId "; //aggiungere la cityId?
            
            $strReclaimWhere .= " AND FA.ViolationTypeId=".$Search_Violation;
            
            if($Search_Detector>0)
            {
                $strReclaimWhere .= " AND FA.DetectorId=".$Search_Detector;
            }
        }
        
        $str_queryReclaim = "SELECT FP.*, PT.Title PaymentTypeTitle
              FROM FinePayment FP
              JOIN sarida.PaymentType PT ON FP.PaymentTypeId = PT.Id";
        $rs_ReclaimPayment = $rs->SelectQuery(
            "$str_queryReclaim $str_fineArticleReclaim WHERE FP.CityId='" . $_SESSION['cityid'] . "' AND FP.FineId=0". $strReclaimWhere." ORDER BY PaymentDate");
        
//         $rs_ReclaimPayment = $rs->SelectQuery("SELECT FP.*, PT.Title PaymentTypeTitle
//               FROM FinePayment FP
//               JOIN sarida.PaymentType PT ON FP.PaymentTypeId = PT.Id
              
              
//               WHERE FP.CityId='" . $_SESSION['cityid'] . "' AND FP.FineId=0".$strReclaimWhere
//             ." ORDER BY PaymentDate"

//         );

        while ($r_ReclaimPayment = mysqli_fetch_array($rs_ReclaimPayment)) {

            
            if($n_Row==$n_ChangePage ){
                $pdf->LN(10);

                $y = $pdf->getY();
                if($ReclaimPayment != 2):
                    if($_SESSION['userlevel']>0 && $CustomerField==0){
                        $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 192, $y, NumberDisplay($f_TotResearch), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 208, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
                        
                        $pdf->writeHTMLCell(16, 4, 224, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 240, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 256, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
                    } else {
                        $pdf->writeHTMLCell(22, 4, 190, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 212, $y, NumberDisplay($f_TotNotificationResearch), 1, 0, 1, true, 'R', true);
                        
                        $pdf->writeHTMLCell(16, 4, 234, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 250, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 266, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
                    }
                else:
                    if($_SESSION['userlevel']>0 && $CustomerField==0){
                        $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 192, $y, NumberDisplay($f_TotResearch), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 208, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
                        
                        $pdf->writeHTMLCell(16, 4, 224, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 240, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 256, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
                    } else {
                        $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 192, $y, NumberDisplay($f_TotNotificationResearch), 1, 0, 1, true, 'R', true);
                        
                        $pdf->writeHTMLCell(16, 4, 214, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(16, 4, 230, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
                        $pdf->writeHTMLCell(22, 4, 246, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
                    }
                endif;

                $pdf->AddPage();
                $pdf->SetFont('arial', '', 8, '', true);

                $pdf->setFooterData(array(0,64,0), array(0,64,128));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->LN(10);

                $pdf->writeHTMLCell(270, 0, 10, '', "ELENCO PAGAMENTI DAL $FromPaymentDate AL $ToPaymentDate (Per ".($DateType > 0 ? 'data accredito' : 'data pagamento').")", 0, 0, 1, true, 'C', true);
                $pdf->LN(10);




                $y = $pdf->getY();
                $pdf->SetFont('arial', '', 8, '', true);
                $pdf->writeHTMLCell(10, 4, 10, $y, "", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, '', '', "Data Pag.", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, '', '', "Data Accr.", 1, 0, 1, true, 'C', true);
                if($ReclaimPayment == 2):
                    if($_SESSION['userlevel']>0 && $CustomerField==0){
                        $pdf->writeHTMLCell(30, 4, '', '', "Metodo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(50, 4, '', '', "Nominativo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(16, 4, '', '', "Ricerca", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(16, 4, '', '', "Notifica", 1, 0, 1, true, 'C', true);
                    } else {
                        $pdf->writeHTMLCell(110, 4, '', '', "Nominativo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Notifica/Ricerca", 1, 0, 1, true, 'C', true);
                    }
                else:
                    if($_SESSION['userlevel']>0 && $CustomerField==0){
                        $pdf->writeHTMLCell(30, 4, '', '', "Metodo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(50, 4, '', '', "Nominativo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(16, 4, '', '', "Ricerca", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(16, 4, '', '', "Notifica", 1, 0, 1, true, 'C', true);
                    } else {
                        $pdf->writeHTMLCell(50, 4, '', '', "Ref comune", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(50, 4, '', '', "Targa/Nominativo", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(30, 4, '', '', "Cron", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Sanzione", 1, 0, 1, true, 'C', true);
                        $pdf->writeHTMLCell(22, 4, '', '', "Notifica/Ricerca", 1, 0, 1, true, 'C', true);
                    }
                endif;
                $pdf->writeHTMLCell(16, 4, '', '', "Spese", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(16, 4, '', '', "Magg", 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(22, 4, '', '', "Riscosso", 1, 0, 1, true, 'C', true);
                $pdf->LN(4);


                $pdf->SetFont('arial', '', 8, '', true);
                $n_Row =0;
                $n_ChangePage = 30;

            }

            $n_Row++;
            $n_Cont++;


            $Amount = $r_ReclaimPayment['Amount'];
            $f_TotAmount += $Amount;

            $CustomerFee = $r_ReclaimPayment['CustomerFee'];
            $NotificationFee =  $r_ReclaimPayment['NotificationFee'] + $r_ReclaimPayment['CanFee'] + $r_ReclaimPayment['CadFee'];
            $PercentualFee = $r_ReclaimPayment['PercentualFee'];
            $ResearchFee = $r_ReclaimPayment['ResearchFee'];
            $Fee = $r_ReclaimPayment['Fee'];

            $f_CustomerNotificationResearch = $NotificationFee + $ResearchFee;

            $f_TotFee += $Fee;
            $f_TotNotification += $NotificationFee;
            $f_TotPercentual += $PercentualFee;
            $f_TotResearch += $ResearchFee;
            $f_TotCustomer += $CustomerFee;

            $f_TotNotificationResearch += $f_CustomerNotificationResearch;

            $y = $pdf->getY();




            $pdf->writeHTMLCell(10, 4, 10, $y, $n_Cont, 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(20, 4, '', '', DateOutDB($r_ReclaimPayment['PaymentDate']), 1, 0, 1, true, 'C', true);
            $pdf->writeHTMLCell(20, 4, '', '', DateOutDB($r_ReclaimPayment['CreditDate']), 1, 0, 1, true, 'C', true);

            $maxNameLength = 45;
            $str_Name = (trim($r_ReclaimPayment['Name'])=="") ? "DA ASSOCIARE" : trim($r_ReclaimPayment['Name']);
            if(strlen($str_Name) > $maxNameLength) {
                $str_Name = preg_replace("/^(.{1,$maxNameLength})(\s.*|$)/s", '\\1...', $str_Name);
            }
            
            if($_SESSION['userlevel']>0 && $CustomerField==0){
                
                $pdf->writeHTMLCell(30, 4, '', '', $r_ReclaimPayment['PaymentTypeTitle'], 1, 0, 1, true, 'L', true);
                //Se non è selezionato "Non associati: Solo loro" (Es.: Includi) cambia la creazione della cella omologarsi al formato della tabella
                if($ReclaimPayment==2):
                    $pdf->writeHTMLCell(80, 4, '', '', $str_Name, 1, 0, 1, true, 'L', true);
                else:
                    $pdf->writeHTMLCell(50, 4, '', '', mb_strlen($str_Name) > 25 ? mb_substr($str_Name,0,25)."..." : $str_Name, 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(30, 4, '', '', "", 1, 0, 1, true, 'L', true);
                endif;
                $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($Fee), 1, 0, 1, true, 'R', true);
                $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($ResearchFee), 1, 0, 1, true, 'R', true);
                $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($NotificationFee), 1, 0, 1, true, 'R', true);
            } else {
                //Se non è selezionato "Non associati: Solo loro" (Es.: Includi) cambia la creazione della cella omologarsi al formato della tabella
                if($ReclaimPayment==2):
                    $pdf->writeHTMLCell(110, 4, '', '', $str_Name, 1, 0, 1, true, 'L', true);
                else:
                    $pdf->writeHTMLCell(50, 4, '', '', "", 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(50, 4, '', '', mb_strlen($str_Name) > 25 ? mb_substr($str_Name,0,25)."..." : $str_Name, 1, 0, 1, true, 'L', true);
                    $pdf->writeHTMLCell(30, 4, '', '', "", 1, 0, 1, true, 'L', true);
                endif;
                $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($Fee), 1, 0, 1, true, 'R', true);
                $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($f_CustomerNotificationResearch), 1, 0, 1, true, 'R', true);
            }


            $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($CustomerFee), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, '', '', NumberDisplay($PercentualFee), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, '', '', NumberDisplay($r_ReclaimPayment['Amount']), 1, 0, 1, true, 'R', true);
            $pdf->LN(4);
        }
    }






    if($RefundPayment){

        $str_queryRefound = "SELECT SUM(FR.Amount) Tot FROM FineRefund FR ";
        $strRefundWhere = '';
        $str_fineArticleRefund = '';
        if ($Search_Violation>0) {
            $str_fineArticleRefund = "INNER JOIN FineArticle FA on FA.FineId = FR.FineId "; //aggiungere la cityId?
            
            $strRefundWhere .= " AND FA.ViolationTypeId=".$Search_Violation;
            
            if($Search_Detector>0)
            {
                $strRefundWhere .= " AND FA.DetectorId=".$Search_Detector;
            }
        }

        $rs_RefundPayment = $rs->SelectQuery(
            "$str_queryRefound $str_fineArticleRefund 
            WHERE FR.CityId='".$_SESSION['cityid']."' AND FR.RefundDate >= '".DateInDB($FromPaymentDate)  ."'
            AND FR.RefundDate <= '".DateInDB($ToPaymentDate)  ."'"
            . $strRefundWhere);

// vecchia versione che non filtrava per violazione        
//         $rs_RefundPayment = $rs->SelectQuery("SELECT SUM(Amount) Tot 
//               FROM FineRefund 
//               WHERE CityId='".$_SESSION['cityid']."' AND RefundDate >= '".DateInDB($FromPaymentDate)  ."' 
//               AND RefundDate <= '".DateInDB($ToPaymentDate)  ."'");
        
        $r_RefundPayment = mysqli_fetch_array($rs_RefundPayment);

        $f_TotFee = $f_TotFee - $r_RefundPayment['Tot'];
        $f_TotAmount = $f_TotAmount - $r_RefundPayment['Tot'];

        $pdf->LN(10);
        $y = $pdf->getY();
        $pdf->writeHTMLCell(80, 4, 180, $y, "TOTALI VERBALI RIMBORSATI", 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(26, 4, 260, $y, NumberDisplay($r_RefundPayment['Tot']), 1, 0, 1, true, 'R', true);


    }

    $pdf->LN(10);
    $y = $pdf->getY();
    if($ReclaimPayment != 2):
        if($_SESSION['userlevel']>0 && $CustomerField==0){
            $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 192, $y, NumberDisplay($f_TotResearch), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 208, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
            
            $pdf->writeHTMLCell(16, 4, 224, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 240, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, 256, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
        } else {
    
            $pdf->writeHTMLCell(22, 4, 190, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, 212, $y, NumberDisplay($f_TotNotificationResearch), 1, 0, 1, true, 'R', true);
            
            $pdf->writeHTMLCell(16, 4, 234, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 250, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, 266, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
        }
    else:
        if($_SESSION['userlevel']>0 && $CustomerField==0){
            $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 192, $y, NumberDisplay($f_TotResearch), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 208, $y, NumberDisplay($f_TotNotification), 1, 0, 1, true, 'R', true);
            
            $pdf->writeHTMLCell(16, 4, 224, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 240, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, 256, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
        } else {
            
            $pdf->writeHTMLCell(22, 4, 170, $y, NumberDisplay($f_TotFee), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, 192, $y, NumberDisplay($f_TotNotificationResearch), 1, 0, 1, true, 'R', true);
            
            $pdf->writeHTMLCell(16, 4, 214, $y, NumberDisplay($f_TotCustomer), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(16, 4, 230, $y, NumberDisplay($f_TotPercentual), 1, 0, 1, true, 'R', true);
            $pdf->writeHTMLCell(22, 4, 246, $y, NumberDisplay($f_TotAmount), 1, 0, 1, true, 'R', true);
        }
    endif;


    

    $FileName = $_SESSION['cityid'].'_stampa_pagamenti_'.date("Y-m-d_H-i").'.pdf';

    $pdf->Output(ROOT."/doc/print/payment/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/payment/'.$FileName;

    header("location: ".$P.$str_CurrentPage);
}

