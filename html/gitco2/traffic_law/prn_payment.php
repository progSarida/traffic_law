<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');
$str_CurrentPage .= "&PageTitle=".$PageTitle;

$DateType = CheckValue('DateType', 'n');    //Pagamento o Accredito
$FromPaymentDate = CheckValue('FromPaymentDate','s'); //Anche per accredito
$ToPaymentDate = CheckValue('ToPaymentDate','s'); //Anche per accredito

$FromRegDate= CheckValue('FromRegDate','s');
$ToRegDate= CheckValue('ToRegDate','s');

$FromFineDate= CheckValue('FromFineDate','s');
$ToFineDate= CheckValue('ToFineDate','s');


$d_PrintDate = (CheckValue('PrintDate','s')=="") ? date("d/m/Y") : CheckValue('PrintDate','s');


$FromProtocolId= CheckValue('FromProtocolId','n');
$ToProtocolId= CheckValue('ToProtocolId','n');

$FromAmount= str_replace(",",".",CheckValue('FromAmount','s'));
$ToAmount= str_replace(",",".",CheckValue('ToAmount','s'));

$BankMgmt =  CheckValue('BankMgmt','n');

$PaymentTypeId= CheckValue('PaymentTypeId','n');

$CurrentYear = CheckValue('CurrentYear','n');
$ReclaimPayment = CheckValue('ReclaimPayment','n');
$RefundPayment = CheckValue('RefundPayment','n');

$CustomerField = CheckValue('CustomerField','n');

$FromAmount= str_replace(",",".",CheckValue('FromAmount','s'));
$ToAmount= str_replace(",",".",CheckValue('ToAmount','s'));

$str_CheckPaymentDate = "";
$str_CheckInsertDate = "";
$str_CheckFineDate = "";

$str_CheckCurrentYear = "";

$str_CheckReclaimPayment0 = "";
$str_CheckReclaimPayment1 = "";
$str_CheckReclaimPayment2 = "";

$str_CheckRefundPayment0 = "";
$str_CheckRefundPayment1 = "";
$str_CheckRefundPayment2 = "";



$str_CheckRefundPayment = "";

$str_CheckBankMgmt0 = "";
$str_CheckBankMgmt1 = "";

$str_CheckCustomerField = "";
$n_CountDataType = 0;


$str_DetectorOptions = '<option></option>';
$rs_Detector = $rs->SelectQuery("SELECT D.TitleIta, D.Id FROM Detector D JOIN DetectorType DT on D.DetectorTypeId = DT.Id WHERE DT.ViolationTypeId =".$Search_Violation." AND D.CityId='".$_SESSION['cityid']."'");

while ($r_Detector = mysqli_fetch_array($rs_Detector)){
    $str_DetectorOptions .= '<option'.($r_Detector['Id'] == $Search_Detector ? ' selected' : '').' value='.$r_Detector['Id'].'>'.StringOutDB($r_Detector['TitleIta']).'</option>';
}

$str_DateColumn = $DateType > 0 ? 'COALESCE(P.CreditDate,P.PaymentDate)' : 'P.PaymentDate';
$str_Where = "P.CityId='".$_SESSION['cityid']."'";


if($FromPaymentDate!=""){
	$str_CurrentPage .="&FromPaymentDate=".$FromPaymentDate;
	$str_Where .= " AND $str_DateColumn >= '".DateInDB($FromPaymentDate)  ."'";
}
if($ToPaymentDate!=""){
	$str_CurrentPage .="&ToPaymentDate=".$ToPaymentDate;
	$str_Where .= " AND $str_DateColumn <= '".DateInDB($ToPaymentDate)  ."'";
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
}else{
    $FromProtocolId="";
}

if($ToProtocolId>0){
    $str_Where .= " AND P.ProtocolId <= $ToProtocolId";
    $str_CurrentPage .="&ToProtocolId=".$ToProtocolId;
}else{
    $ToProtocolId="";
}




if($PaymentTypeId>0){
    $str_Where .= ($PaymentTypeId==10) ? " AND (P.PaymentTypeId = 1 OR P.PaymentTypeId = 2 OR P.PaymentTypeId = 18)" : " AND P.PaymentTypeId = " .$PaymentTypeId;

    $str_CurrentPage .="&PaymentTypeId=".$PaymentTypeId;
}


if($CurrentYear){
    $str_Where .= " AND P.ProtocolYear =".$_SESSION['year'];
    $str_CurrentPage .="&CurrentYear=".$CurrentYear;
    $str_CheckCurrentYear =" CHECKED ";
}

if($ReclaimPayment==1){
    $str_CurrentPage .="&ReclaimPayment=".$ReclaimPayment;
    $str_CheckReclaimPayment1 =" CHECKED ";
}else if($ReclaimPayment==2){
    $str_CurrentPage .="&ReclaimPayment=".$ReclaimPayment;
    $str_CheckReclaimPayment2 =" CHECKED ";
}else{
    $str_CheckReclaimPayment0 =" CHECKED ";
}

if($RefundPayment==1){
    $str_CurrentPage .="&RefundPayment=".$RefundPayment;
    $str_CheckRefundPayment1 =" CHECKED ";
}else if($RefundPayment==2){
    $str_CurrentPage .="&RefundPayment=".$RefundPayment;
    $str_CheckRefundPayment2 =" CHECKED ";
}else{
    $str_CheckRefundPayment0 =" CHECKED ";
}


if($CustomerField) {
    $str_CurrentPage .="&CustomerField=".$CustomerField;
    $str_CheckCustomerField =" CHECKED ";
}

if($FromAmount>0){
    $str_Where .= " AND P.Amount >= $FromAmount";
    $str_CurrentPage .="&FromAmount=".$FromAmount;
}

if($ToAmount>0){
    $str_Where .= " AND P.Amount <= $ToAmount";
    $str_CurrentPage .="&ToAmount=".$ToAmount;
}

$str_CurrentPage .="&BankMgmt=".$BankMgmt;
$str_CurrentPage .="&DateType=".$DateType;

if($BankMgmt==1){
    $str_Where .= " AND P.BankMgmt = 1";

    $str_CheckBankMgmt1 = " SELECTED ";
}else if($BankMgmt==2){
    $str_Where .= " AND P.BankMgmt = 0";

    $str_CheckBankMgmt0 = " SELECTED ";
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


if($r_Customer['CityUnion']>1){
    $Locality= CheckValue('Locality','s');

    if($Locality!=""){
        $str_Where .= " AND Locality = '".$Locality."'";
        $str_CurrentPage .="&Locality=".$Locality;
    }

    $str_Union ='
        <div class="col-sm-2 BoxRowLabel">
            Località:
        </div>
        <div class="col-sm-3 BoxRowCaption">
            '. CreateSelect(MAIN_DB.".City","UnionId='".$_SESSION['cityid']."'","Title","Locality","Id","Title",$Locality,false) .'
        </div>

    ';



}else{

    $str_Union ='
    <div class="col-sm-5 BoxRowLabel">
    </div>
    ';


}

if($_SESSION['userlevel']>0){
    $str_UserLevelFilter = '                            
        <div class="col-sm-1 BoxRowCaption" style="font-size:1rem;text-align:right">
            <input type="checkbox" name="CustomerField" value="1" '.$str_CheckCustomerField.'>
        </div>
        <div class="col-sm-1 BoxRowCaption">
            Uso comune
        </div>           
        
        <div class="col-sm-1 BoxRowLabel">
            Metodo:
        </div>
        <div class="col-sm-2 BoxRowCaption">
            '.CreateSelect("sarida.PaymentType","Disabled=0","Title","PaymentTypeId","Id","Title",$PaymentTypeId,false) .'
        </div>        
        
        
        
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Data stampa:
        </div>
        <div class="col-sm-1 BoxRowCaption">
              <input class="form-control frm_field_date" name="PrintDate" type="text" style="width:9rem" value="'.$d_PrintDate.'">
        </div>
       ';

} else {
    $str_UserLevelFilter = '
        <div class="col-sm-5 BoxRowLabel">
            &nbsp;
        </div>
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Data stampa:
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date" name="PrintDate" type="text" style="width:9rem" value="'.$d_PrintDate.'" readonly>
        </div>                        
';
}




$strOrder = "PaymentDate";


$str_out .='
<form id="f_Search" action="'.$str_CurrentPage.'" method="post">
<input type="hidden" name="FinePaymentSpecificationType" value="'.$r_Customer['FinePaymentSpecificationType'].'">
<input type="hidden" name="PrintDate" value="'.$d_PrintDate.'">
<input type="hidden" name="PageTitle" value="'.$PageTitle.'">
<div class="row-fluid">        
    <div class="col-sm-12" >
        <div class="col-sm-11" style="height:11.2rem; border-right:1px solid #E7E7E7;">

            <div class="col-sm-1 BoxRowLabel BoxRowCaption">
                '.CreateArraySelect(array('Pagamento', 'Accredito'), true, 'DateType', 'DateType', $DateType, true).'
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromPaymentDate" type="text" style="width:9rem" value="'.$FromPaymentDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToPaymentDate" type="text" style="width:9rem" value="'.$ToPaymentDate.'">
            </div>
            '.$str_Union.'            
            
            <div class="col-sm-1 BoxRowCaption" style="font-size:1rem;text-align:right">
                <input type="checkbox" name="CurrentYear" value="1" '.$str_CheckCurrentYear.'>
            </div>
            <div class="col-sm-1 BoxRowCaption">
                Anno corrente
            </div>   
                     
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Registrazione:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromRegDate" type="text" style="width:9rem" value="'.$FromRegDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToRegDate" type="text" style="width:9rem" value="'.$ToRegDate.'">
            </div>
            '.$str_UserLevelFilter.'    
                
            <div class="clean_row HSpace4"></div>  
            <div class="col-sm-1 BoxRowLabel">
                Accertamento:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromFineDate" type="text" style="width:9rem" value="'.$FromFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToFineDate" type="text" style="width:9rem" value="'.$ToFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Importo:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">
                 <input class="form-control frm_field_currency" name="FromAmount" type="text" style="width:8rem" value="'.$FromAmount.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>         
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_currency" name="ToAmount" type="text" style="width:8rem" value="'.$ToAmount.'">
            </div>            
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Riscossore:
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select name="BankMgmt">
                    <option value="0"></option>
                    <option value="1" '.$str_CheckBankMgmt1.'>Sarida</option>
                    <option value="2" '.$str_CheckBankMgmt0.'>C/Terzi</option>
                </select>
            </div>

            <div class="clean_row HSpace4"></div>  

            <div class="col-sm-1 BoxRowLabel">
                Cron:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>        
            <div class="col-sm-1 BoxRowCaption">                
                <input class="form-control frm_field_numeric" name="FromProtocolId" type="text" style="width:8rem" value="'.$FromProtocolId.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-1 BoxRowCaption"> 
                <input class="form-control frm_field_numeric" name="ToProtocolId" type="text" style="width:8rem" value="'.$ToProtocolId.'">
            </div>

            <div class="col-sm-1 BoxRowLabel">
                Non associati:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="ReclaimPayment" value="0" '.$str_CheckReclaimPayment0.' style="top:0"><span  style="position:relative;top:-0.3rem;margin-left:0.3rem">Escludi</span> 
                <input type="radio" name="ReclaimPayment" value="1" '.$str_CheckReclaimPayment1.' style="top:0"><span  style="position:relative;top:-0.3rem;margin-left:0.3rem">Includi</span>
                <input type="radio" name="ReclaimPayment" value="2" '.$str_CheckReclaimPayment2.' style="top:0"><span  style="position:relative;top:-0.3rem;margin-left:0.3rem">Solo loro</span>
            </div>
            <div class="col-sm-1 BoxRowLabel"> &nbsp;</div>
            <div class="col-sm-1 BoxRowLabel">
                Rimborsi:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="RefundPayment" value="0" '.$str_CheckRefundPayment0.' style="top:0"><span  style="position:relative;top:-0.3rem;margin-left:0.3rem">Escludi</span>  
                <input type="radio" name="RefundPayment" value="1" '.$str_CheckRefundPayment1.' style="top:0"><span  style="position:relative;top:-0.3rem;margin-left:0.3rem">Includi</span> 
                <input type="radio" name="RefundPayment" value="2" '.$str_CheckRefundPayment2.' style="top:0"><span  style="position:relative;top:-0.3rem;margin-left:0.3rem">Solo loro</span> 
            </div>                                                                
                                                           
            <div class="clean_row HSpace4"></div> 

            <div class="col-sm-2 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,20) .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Rilevatore
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <select class="form-control" name="Search_Detector" id="Search_Detector">
                    '.$str_DetectorOptions.'
                </select>
            </div>
            
        </div>
        <div class="col-sm-1 table_caption_H" style="height:11.2rem;">
            <img src="'.IMG.'/progress.gif" style="width:70px;display: none;position:absolute;left:20px;top:10px" id="Progress"/>
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">

       
                <button class="btn btn-primary" id="btn_src" style="width:10rem">
                    <i class="glyphicon glyphicon-search" style="margin-top:0.2rem;font-size:3.5rem;"></i>
                </button>
       
            </div>
            <button class="btn btn-primary" id="btn_pdf" style="position:absolute;bottom:0px;left:10px;width:4rem">    
                <i class="fa fa-file-pdf-o" style="font-size:1.5rem;"></i>            
            </button>
            <button class="btn btn-primary" id="btn_xls" style="position:absolute;bottom:0px;left:60px;width:4rem">
                <i class="fa fa-file-excel-o" style="font-size:1.5rem;"></i>
            </button>
        </div>        
    </div>
</div>                    
</form>
<div class="clean_row HSpace4"></div>
';


$str_out.= '
    	<div class="row-fluid">
        	<div class="col-sm-12">
            	<div class="table_label_H col-sm-1">&nbsp;</div>
				<div class="table_label_H col-sm-1">Data Pag.</div>

				';


if($_SESSION['userlevel']>0 && $CustomerField==0){
    $str_out.= '
    			<div class="table_label_H col-sm-2">Metodo</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Sanzione</div>
				<div class="table_label_H col-sm-1">Ricerca</div>
				<div class="table_label_H col-sm-1">Notifica</div>
    ';
} else {
    $str_out.= '
				<div class="table_label_H col-sm-1">Ref comune</div>
				<div class="table_label_H col-sm-2">Targa/Nominativo</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Sanzione</div>
				<div class="table_label_H col-sm-1">Notifica/Ricerca</div>
    ';
}


    $str_out.= '
				<div class="table_label_H col-sm-1">Spese Comune</div>
				<div class="table_label_H col-sm-1">Maggiorazione</div>
                <div class="table_label_H col-sm-2">Riscosso</div>
				<div class="clean_row HSpace4"></div>';



echo $str_out;
echo "
<div class=\"table_label_H col-sm-12\">
</div>
";
$str_out = '';

if($FromPaymentDate == "" && $ToPaymentDate=="") {
    $str_out.=
        '<div class="table_caption_H col-sm-12">
			Scegliere un periodo di pagamento/accredito
		</div>';
    echo $str_out;
}else{

    $f_TotAmount=0;
    $f_TotFee = 0;
    $f_TotNotification = 0;
    $f_TotPercentual = 0;
    $f_TotResearch = 0;
    $f_TotCustomer = 0;
    $f_TotNotificationResearch = 0;

    $n_Cont = 0;

    if($ReclaimPayment<2){
        $str_query = "SELECT P.* FROM V_FinePayment P";
        $rs_Payment = $rs->SelectQuery(
            "$str_query $str_fineArticle WHERE $str_Where ORDER BY $strOrder");
        
        if (mysqli_num_rows($rs_Payment) == 0) {
            $str_out.= '
                <div class="table_caption_H col-sm-12">
			        Nessun pagamento presente
		        </div>
		        ';
        } else {
            while ($r_Payment = mysqli_fetch_array($rs_Payment)) {

                $chkFee = "table_caption_success";
                $chkNotification = "table_caption_success";
                $chkResearch = "table_caption_success";


                $n_Cont++;

                $Amount = $r_Payment['Amount'];
                $f_TotAmount +=$Amount;

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


                $str_out.= '<div class="table_caption_H col-sm-1">' . $n_Cont .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($r_Payment['PaymentDate']) .'</div>';


                $str_FineLetter = ($r_Payment['FineCountryId']=="Z000") ? "U" : "ES";
                $str_PaymentName = (strlen(trim($r_Payment['PaymentName']))==0) ? $r_Payment['CompanyName']." ".$r_Payment['Surname']." ".$r_Payment['Name'] : $r_Payment['PaymentName'];


                $chkAmount = ($Amount!=$Fee+$NotificationFee+$ResearchFee+$CustomerFee) ? "<span class='glyphicon glyphicon-exclamation-sign' style='color: red;'></span>" : "";

                if($_SESSION['userlevel']>0 && $CustomerField==0){
                    $str_out.= '
                    <div class="table_caption_H col-sm-2">' . $r_Payment['PaymentTypeTitle'] .'</div>
                    <div class="table_caption_H col-sm-1 '.$chkAmount.'">' . $r_Payment['ProtocolId'] .'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter.'</div>
                    <div class="table_caption_H col-sm-1 '.$chkFee.'">' . NumberDisplay($Fee) .'</div>
                    <div class="table_caption_H col-sm-1 '.$chkResearch.'">' . NumberDisplay($ResearchFee) .'</div>
                    <div class="table_caption_H col-sm-1 '.$chkNotification.'">' . NumberDisplay($NotificationFee) .'</div>
                ';
                } else {
                    $str_out.= '
                    <div class="table_caption_H col-sm-1">' . $r_Payment['Code'] . '</div>
                    <div class="table_caption_H col-sm-2" style="font-size:0.9rem;">(' . $r_Payment['VehiclePlate'] .') '.substr($str_PaymentName,0,20) .'</div>
                    <div class="table_caption_H col-sm-1">' . $r_Payment['ProtocolId'].'/'.$r_Payment['ProtocolYear'].'/'.$str_FineLetter.'</div>
                    <div class="table_caption_H col-sm-1 '.$chkFee.'">' . NumberDisplay($Fee) .'</div>
				    <div class="table_caption_H col-sm-1">'. NumberDisplay($f_CustomerNotificationResearch) .'</div>
                ';
                }



                $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($CustomerFee) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($PercentualFee) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2" style="text-align:right">' . NumberDisplay($r_Payment['Amount']) .'</div>';


                $str_out.= '
			            <div class="clean_row HSpace4"></div>';
            }
        }
    }



    if($ReclaimPayment>0){

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



        $rs_ReclaimPayment = $rs->SelectQuery("SELECT FP.*, PT.Title PaymentTypeTitle
              FROM FinePayment FP
              JOIN sarida.PaymentType PT ON FP.PaymentTypeId = PT.Id
              
              
              WHERE FP.CityId='".$_SESSION['cityid']."' AND FP.FineId=0".$strReclaimWhere);
        while($r_ReclaimPayment = mysqli_fetch_array($rs_ReclaimPayment)){




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


            $str_out.= '<div class="table_caption_H col-sm-1">' . $n_Cont .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($r_ReclaimPayment['PaymentDate']) .'</div>';

            $str_Name = (trim($r_ReclaimPayment['Name'])=="") ? "DA ASSOCIARE" : trim($r_ReclaimPayment['Name']);


            $str_out.= '
                <div class="table_caption_H col-sm-2">' . $r_ReclaimPayment['PaymentTypeTitle'] .'</div>
                <div class="table_caption_H col-sm-1">&nbsp;</div>
                <div class="table_caption_H col-sm-1">' . NumberDisplay($Fee) .'</div>
                <div class="table_caption_H col-sm-1">' . NumberDisplay($ResearchFee) .'</div>
                <div class="table_caption_H col-sm-1">' . NumberDisplay($NotificationFee) .'</div>
            ';


            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($CustomerFee) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($PercentualFee). '</div>';
            $str_out.= '<div class="table_caption_H col-sm-2" style="text-align:right">' . NumberDisplay($r_ReclaimPayment['Amount']) .'</div>';

        }

    }

    if($RefundPayment){
        $str_RefundWhere = "CityId='".$_SESSION['cityid']."'";
        
        if($FromPaymentDate!=""){
            $str_RefundWhere .= " AND RefundDate >= '".DateInDB($FromPaymentDate)  ."'";
        }
        if($ToPaymentDate!=""){
            $str_RefundWhere .= " AND RefundDate <= '".DateInDB($ToPaymentDate)  ."'";
        }

        $rs_RefundPayment = $rs->SelectQuery("SELECT SUM(Amount) Tot 
              FROM FineRefund 
              WHERE $str_RefundWhere");
        $r_RefundPayment = mysqli_fetch_array($rs_RefundPayment);


        $str_out.= '<div class="table_caption_H col-sm-5">TOTALI VERBALI RIMBORSATI</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($r_RefundPayment['Tot']) .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-6">&nbsp;</div>';


        $f_TotFee = $f_TotFee - $r_RefundPayment['Tot'];
        $f_TotAmount = $f_TotAmount - $r_RefundPayment['Tot'];
    }



    $str_out.= '<div>
	</div>';



    if($_SESSION['userlevel']>0 && $CustomerField==0){
        $str_out.= '
                    <div class="table_caption_H col-sm-5">TOTALI</div>
                    <div class="table_caption_H col-sm-1">' . NumberDisplay($f_TotFee) .'</div>
                    <div class="table_caption_H col-sm-1">' . NumberDisplay($f_TotResearch) .'</div>
                    <div class="table_caption_H col-sm-1">' . NumberDisplay($f_TotNotification) .'</div>
                ';
    } else {
        $str_out.= '
                    <div class="table_caption_H col-sm-6">TOTALI</div>
                    <div class="table_caption_H col-sm-1">' . NumberDisplay($f_TotFee) .'</div>
				    <div class="table_caption_H col-sm-1">'. NumberDisplay($f_TotNotificationResearch) .'</div>
                ';
    }

    $str_out.= '<div class="table_caption_H col-sm-1">' .NumberDisplay($f_TotCustomer). '</div>';
    $str_out.= '<div class="table_caption_H col-sm-1">' .NumberDisplay($f_TotPercentual). '</div>';
    $str_out.= '<div class="table_caption_H col-sm-2" style="text-align:right">' . NumberDisplay($f_TotAmount) .'</div>';

    echo $str_out;
}






?>

<script type="text/javascript">

	$(document).ready(function () {

        $("#btn_src").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_payment.php');
            $('#btn_src').hide();
            $('#btn_xls').hide();
            $('#btn_pdf').hide();
            $('#Progress').show();

            $('#f_Search').submit();

        });

        $("#btn_pdf").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_payment_exe.php');
            $('#btn_src').hide();
            $('#btn_xls').hide();
            $('#btn_pdf').hide();
            $('#Progress').show();

            $('#f_Search').submit();
        });

        $("#btn_xls").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_payment_exe.php?CSV=1');


            $('#f_Search').submit();
        });

        $('#Search_Violation').on('change', function(){
            var ViolationTypeId = $(this).val();

            $.ajax({
                url: 'ajax/ajx_get_detectorByViolationTypeId.php',
                type: 'POST',
                dataType: 'json',
                data: {ViolationTypeId:ViolationTypeId},
                success: function (data) {
                	$('#Search_Detector').html(data.Options);
                },
                error: function (result) {
                    console.log(result);
                    alert("error: " + result.responseText);
                }
            });
        });

	});
</script>
<?php
include(INC."/footer.php");
