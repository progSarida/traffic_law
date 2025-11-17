<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$a_StatusTypeId = array();
$a_StatusTypeId[35] = "#A94442";
$a_StatusTypeId[36] = "#23448E";
$a_StatusTypeId[37] = "#A94442";

$a_Euro = array();
$a_Euro[28] = "DDD728";
$a_Euro[30] = "3C763D";


$str_Union = CreateSelectCustomerUnion($Search_Locality);


$chh_FindFilter = trim($str_Where);
$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
$strOrder = "ProtocolId";

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}

$rs_Result = $rs->Select('Result', "1=1");
while ($r_Result = mysqli_fetch_array($rs_Result)){
    $a_Result[$r_Result['Id']] = $r_Result['Title'];
}
$a_GradeType = array("","I","II","III");

$a_DisputeStatusId = array("","#DDD728","#3C763D","#A94442");

if($Search_ValidatedAddress==1) $ValidatedAddress_Checked = 'checked';
else $ValidatedAddress_Checked = '';

$str_out .= '

    <div class="col-sm-12">   
        <div id="DIV_SrcPayment" style="display:none; position:absolute;top:30%;left:20%; z-index: 900">    
            <input type="hidden" name="FineId" id="FineId">
            <div class="col-sm-12">
                <div class="col-sm-12 table_label_H" style="text-align:center">			
                    Ricerca pagamento
                </div>
                <span class="fa fa-times-circle close_window" style="color:#fff;position:absolute; right:10px;top:2px;font-size:20px; "></span>
            </div>
            <div class="clean_row HSpace4"></div> 
            
            <div class="col-sm-12">             
                <div class="col-sm-1 BoxRowLabel">
                    Cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input name="Payment_Protocol" id="Payment_Protocol" type="text" style="width:10rem">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Ref
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Payment_Code" id="Payment_Code" type="text" style="width:10rem">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nome
                </div>                            
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Payment_Name" id="Payment_Name" type="text" style="width:15rem">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Targa
                </div>                              
                <div class="col-sm-2 BoxRowCaption">
                    <input name="Payment_Plate" id="Payment_Plate" type="text" style="width:10rem">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="text-align: center">
                    <i class="fa fa-search-plus" style="margin-top:0.3rem;font-size:1.6rem;"></i>
                </div>                                                                                     
        
            </div>
            <div class="clean_row HSpace4"></div> 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:20rem;">
                    <div id="payment_content" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>
                </div>
            </div> 
        </div>
    </div>    

<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:6.8rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Id/Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_ProtocolId" type="text" style="width:10rem" value="'.$Search_ProtocolId.'">
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" style="width:8rem" value="'.$Search_Plate.'">
            </div>          
            <div class="col-sm-1 BoxRowLabel">
                Prot/Ref
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:9rem" value="'.$Search_Ref.'">
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. $str_Union .'
            </div>              
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelect("RuleType","CityId='".$_SESSION['cityid']."'","Id","Search_RuleTypeId","Id","Title",$Search_RuleTypeId,true,10, "frm_field_required") .'
            </div>              
            <div class="clean_row HSpace4"></div>
            
            
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>								
                </select>
            </div>              
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false,9) .'
            </div>                                                
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="Search_Status" id="Search_Status" style="width:9rem;">
                    <option value="0"></option>
                    <option value="20" ' .$a_Search_Status[20] .'>In attesa di notifica</option>
                    <option value="23" ' .$a_Search_Status[23] .'>Non notificato</option>
                    <option value="25" ' .$a_Search_Status[25] .'>Notificato</option>
                    <option value="30" ' .$a_Search_Status[30] .'>Pagato</option>
                    <option value="35" ' .$a_Search_Status[35] .'>Archiviato</option>
                    <option value="36" ' .$a_Search_Status[36] .'>Rinotificato</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" style="width:9rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" style="width:9rem">
            </div>             
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" style="width:9rem" value="'.$Search_Trespasser.'">
            </div>          
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowLabel">
                Notifiche da validare
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" name="Search_ValidatedAddress" id="ValidatedAddress" value="1" '.$ValidatedAddress_Checked.'>
            </div>  
            <div class="col-sm-10 BoxRowCaption">
                
            </div>
        </div>
        <div class="col-sm-1 BoxRow" style="height:7rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:1.5rem;font-size:3rem;"></i>	
            </div>
        </div>
    </form>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';


$str_out .='        
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Ref</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-3">Trasgressore</div>
				<div class="table_label_H col-sm-2">Stato pratica</div>
        		<div class="table_add_button col-sm-1 right">
        			  				
					
				</div>
				<div class="clean_row HSpace4"></div>';





if($chh_FindFilter=="1=1"){
    $str_out.= '
        <div class="table_caption_H col-sm-12" style="font-size:2rem;color:orange;text-align: center">
        Inserire criteri ricerca
        </div>
        ';
}else{
    $rs_FineList = $rs->Select('V_mgmt_Fine_List',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);

    $RowNumber = mysqli_num_rows($rs_FineList);

    if ($RowNumber == 0) {
        $str_out.= '
        <div class="table_caption_H col-sm-12" style="text-align: center">
        Nessun record presente
        </div>
        ';
    } else {
        while ($r_FineList = mysqli_fetch_array($rs_FineList)) {


            $ExternalProtocol = ($r_FineList['ExternalProtocol']>0)? $r_FineList['ExternalProtocol'].'/'.$r_FineList['ExternalYear'] : "";


            $str_PreviousId     = "";
            $str_Archive        = "";
            $str_ProtocolId     = "";

            if($r_FineList['PreviousId']>0){
                $rs_Previous = $rs->Select('Fine',"Id=".$r_FineList['PreviousId']);
                $r_Previous = mysqli_fetch_array($rs_Previous);

                $str_PreviousId = '
            <a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Previous['Id'].'">
                <span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale collegato Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'].'">
                    <i class="fa fa-file-text" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
                </span>
            </a>
            ';
            }


            $str_126Bis = '';
            /*
            $rs_126Bis = $rs->Select('V_FineArticle', "PreviousId=".$r_FineList['FineId']. " AND Id1=126");

            if(mysqli_num_rows($rs_126Bis)>0){
                $r_126Bis = mysqli_fetch_array($rs_126Bis);
                $str_126Bis = '
            <a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_126Bis['Id'].'">
                <span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="126 BIS creato in data '. DateOutDB($r_126Bis['FineDate']).' Cron '.$r_126Bis['ProtocolId'].'/'.$r_126Bis['ProtocolYear'].'">
                    <i class="fa fa-paperclip" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
                </span>
            </a>
            ';

            }
*/




            $str_Style      = (isset($a_StatusTypeId[$r_FineList['StatusTypeId']])) ? ' style="color:'.$a_StatusTypeId[$r_FineList['StatusTypeId']].';"' : '';
            $str_CssEuro    = (isset($a_Euro[$r_FineList['StatusTypeId']])) ? $a_Euro[$r_FineList['StatusTypeId']] : '000';


            if($r_FineList['StatusTypeId']==35 || $r_FineList['StatusTypeId']==37){
                $rs_Archive = $rs->SelectQuery("
                SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
                FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
                WHERE FA.FineId=".$r_FineList['FineId']);
                $r_Archive = mysqli_fetch_array($rs_Archive);

                $str_Archive = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale archiviato in data '. DateOutDB($r_Archive['ArchiveDate']).' '.$r_Archive['ReasonTitle'].' '.$r_Archive['Note'].'"><i class="fa fa-info-circle" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';


            }else if($r_FineList['StatusTypeId']==36){
                $rs_Previous = $rs->Select('Fine',"PreviousId=".$r_FineList['FineId']);
                $r_Previous = mysqli_fetch_array($rs_Previous);

                $str_PreviousId = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale noleggio ristampato con Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'].'"><i class="fa fa-file-text" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';

            }else if($r_FineList['StatusTypeId']==33){
                $str_ProtocolId = '<span class="tooltip-r" data-toggle="tooltip" data-placement="right" title="Verbale rinotificato"><i class="fa fa-exchange" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
            }

            $str_Trespasser = $r_FineList['CompanyName'] .' '.$r_FineList['Surname'] .' '.$r_FineList['Name'];
            $str_Trespasser = (strlen($str_Trespasser)>42) ? substr($str_Trespasser,0,40).'...' : $str_Trespasser;

            $str_out.= '
			<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $r_FineList['FineId'] .' '.$str_PreviousId.$str_Archive.$str_126Bis.$str_ProtocolId.'</div>
			<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $r_FineList['ProtocolId'].' / '.$r_FineList['ProtocolYear'].'</div>
		    <div class="table_caption_H col-sm-1"'.$str_Style.'>' . $r_FineList['Code'].'</div>
        	<div class="table_caption_H col-sm-1"'.$str_Style.'>' . DateOutDB($r_FineList['FineDate']) .'</div>
        	<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $r_FineList['FineTime'] .'</div>
        	<div class="table_caption_H col-sm-1"'.$str_Style.'>' . StringOutDB($r_FineList['VehiclePlate']) .'</div>
			<div class="table_caption_H col-sm-3"'.$str_Style.'>' . StringOutDB($str_Trespasser) .'</div>
			';
            //<div class="table_caption_H col-sm-1"'.$str_Style.'>' . $ExternalProtocol .'</div>';

            $Status = '';
            if($r_Customer['ExternalRegistration']==1)
                $Status .= ($r_FineList['ExternalProtocol']>0) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale protocollato in data '. DateOutDB($r_FineList['ExternalDate']).'"><i class="fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<i class="fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';



            if($r_FineList['StatusTypeId']>14) {
                $Status .= (!is_null($r_FineList['FlowDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso creato in data ' . DateOutDB($r_FineList['FlowDate']) . '"><i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso non e stato creato"><i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';
                $Status .= (!is_null($r_FineList['PrintDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso stampato in data ' . DateOutDB($r_FineList['PrintDate']) . '"><i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso non e stato stampato"><i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';

            } else if($r_FineList['FineTypeId']==2) {
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Preavviso"> <i class="fa fa-file-text" style="margin-top:0.2rem;margin-left:3.9rem;font-size:1.7rem;"></i></span>' ;
            } else if($r_FineList['StatusTypeId']==3) {
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale"> <i class="fa fa-file-text" style="margin-top:0.2rem;margin-left:3.9rem;font-size:1.7rem;"></i></span>' ;
            } else {
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale"><i class="fa fa fa-file" style="margin-top:0.2rem;margin-left:3.5rem;font-size:1.8rem;"></i></span>' ;
            }

            $Status .= (! is_null($r_FineList['SendDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale inviato in data '. DateOutDB($r_FineList['SendDate']).'"><i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale non e stato inviato"><i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';


            if (! is_null($r_FineList['ResultId'])) {

                if (! is_null($r_FineList['DeliveryDate'])) {
                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale notificato in data '. DateOutDB($r_FineList['DeliveryDate']).'"><i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:green;"></i></span>';
                    $str_DeliveryStatus = '<a href="mgmt_notification_viw.php'.$str_GET_Parameter.'&Id='.$r_FineList['FineId'].'"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
                }else{
                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="'.$a_Result[$r_FineList['ResultId']].'"><i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:red;"></i></span>';
                    $str_DeliveryStatus = '<a href="mgmt_notification_viw.php'.$str_GET_Parameter.'&Id='.$r_FineList['FineId'].'"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
                }


            } else {
                if($_SESSION['usertype']>50) {
                    $Status .= '
                <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Importa notifica">
                    <a href="mgmt_notification_add.php'.$str_GET_Parameter.'&FineId='.$r_FineList['FineId'].'">
                        <i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.8rem;opacity:.2"></i></a></span>
                ';
                }else{
                    $Status .= '<i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
                }
                $str_DeliveryStatus = '&nbsp;';
            }

            if (! is_null($r_FineList['PaymentDate'])) {
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale pagato in data '. DateOutDB($r_FineList['PaymentDate']).'"><i id="'.$r_FineList['FinePaymentId'].'" class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#'.$str_CssEuro.'" name="'.$str_CssEuro.'"></i></span>';
            }else if($r_FineList['StatusTypeId']==27 && $_SESSION['userlevel']>=7) $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
            else{

                if($_SESSION['usertype']>50) {
                    $Status .= '
                    <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Cerca pagamento">
                        <i class="fa fa-eur src_payment" fineid="'.$r_FineList['FineId'].'" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.8rem;opacity:.2"></i>
                    </span>
                ';
                }else{
                    $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1;color:#'.$str_CssEuro.'" name="'.$str_CssEuro.'"></i>';
                }
            }





            if (! is_null($r_FineList['DisputeId'])) {
                $rs_Row = $rs->Select('V_FineDispute',"FineId=".$r_FineList['FineId']." ORDER BY GradeTypeId DESC");
                if(mysqli_num_rows($rs_Row)>0){
                    $r_Row = mysqli_fetch_array($rs_Row);
                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="'.$a_GradeType[$r_Row['GradeTypeId']].' Grado - '.$r_Row['OfficeTitle'].' '. $r_Row['OfficeCity'].' Depositato in data '. DateOutDB($r_Row['DateFile']) .'"><i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:'.$a_DisputeStatusId[$r_Row['DisputeStatusId']].'"></i></span>';

                }                
            }else
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Senza grado"><i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';




            if (! is_null($r_FineList['CommunicationDate'])) {
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Comunicazione presentata in data '.DateOutDB($r_FineList['CommunicationDate']).'"><i id="'.$r_FineList['FineId'].'" class="fa fa-address-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
            }else $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Comunicazione non e stata presentata"><i class="fa fa-address-card" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';


            if($_SESSION['userlevel']>=3) {
                if (! is_null($r_FineList['ValidatedAddress'])) {
                    if ($r_FineList['ValidatedAddress'] == 1) $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notifica e stata validata"><i class="fa fa-archive" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
                    else $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notifica non e stata validata"><i id="' . $r_FineList['FineId'] . '" class="fa fa-archive" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';
                    
                }
            }






            if (! is_null($r_FineList['Documentation'])) {
                $str_DocumentFolder = ($r_FineList['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$r_FineList['FineId'] : FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$r_FineList['FineId'];
                $str_PrintButton = '<a href="'.$str_DocumentFolder."/".$r_FineList['Documentation'].'" target="_BLANK"><span class="fa fa-print" style="position:absolute;left:25px;top:5px;"></span></a>';
                
            }else{
                $str_PrintButton = '';
            }
            

            

            $upd_button = ChkButton($aUserButton, 'upd','<a href="mgmt_fine_upd.php'.$str_GET_Parameter.'&Id='.$r_FineList['FineId'].'"><span class="glyphicon glyphicon-pencil" style="position:absolute;left:45px;top:5px;"></span></a>');

            $str_out.=
                '<div class="table_caption_H col-sm-2">' . $Status .'</div>
			<div class="table_caption_button col-sm-1">
				'. ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_FineList['FineId'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>') .'
				&nbsp;
				'. ChkButton($aUserButton, 'prn', $str_PrintButton) .'
				'.$upd_button.'
				'. ChkButton($aUserButton, 'prn','<a href="mgmt_fine_prn.php'.$str_GET_Parameter.'&Id='.$r_FineList['FineId'].'"><span class="fa fa-paper-plane" style="position:absolute;left:65px;top:5px;"></span></a>');

            if($r_FineList['StatusTypeId']<30){
                $str_out.=
                    ChkButton($aUserButton, 'del','<a href="mgmt_fine_exp.php'.$str_GET_Parameter.'&Id='.$r_FineList['FineId'].'"><span class="glyphicon glyphicon-remove-sign" style="position:absolute;left:85px;top:5px;"></span></a>');

            }
            $str_out.='
			</div>
			<div class="clean_row HSpace4"></div>';
        }
    }
    $table_users_number = $rs->Select('V_mgmt_Fine',$str_Where);

    $UserNumberTotal = mysqli_num_rows($table_users_number);

    $strLabel =' 
 		<div style="position:absolute; top:5px;font-size:1.2rem;color:#fff;width:430px;text-align: left">
			<div style="width:140px;float:left;">
				<i class="fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale protocollato
			</div>	
			<div style="width:140px;float:left;">
				<i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Flusso creato
			</div>					
			<div style="width:140px;float:left;">
	 			<i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale stampato
			</div>
			<div style="width:140px;float:left;">
	 			<i class="fa fa-file" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale digitale
			</div>						
			<div style="width:140px;float:left;">
				<i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale spedito
			</div>
			<div style="width:140px;float:left;">
	 			<i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale Notificato
			</div>
			<div style="width:140px;float:left;">
				<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale pagato
			</div>
			<div style="width:140px;float:left;">
				<i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale contestato
			</div>		
		</div>
		
		
		
		';


    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,$strLabel);

}


$str_out.= '<div>
	</div>';


echo $str_out;
?>
    <div class="overlay" id="overlay" style="display:none;"></div>

    <div id="overlay_PaymentView">
        <div id="FormPaymentTrespasser">
        </div>
    </div>

    <div id="overlay_CommunicationView">
        <div id="FormCommunicationTrespasser">
        </div>
    </div>



    <script type="text/javascript">


        $(document).ready(function () {
            <?= require ('inc/jquery/base_search.php')?>

            <?= require ('inc/jquery/overlay_search_payment.php')?>
            <?= require ('inc/jquery/overlay_search_communication.php')?>

            $('.src_payment').on('click', function() {
                $('#DIV_SrcPayment').show();
                $('#FineId').val($(this).attr("fineid"));

            });
            $(".close_window").click(function () {
                $('#DIV_SrcPayment').hide();
            });
            $( function() {
                $( "#DIV_SrcPayment" ).draggable();
            } );


            $('.fa-search-plus').click(function () {
                $('.fa-search-plus').hide();


                var FineId = $('#FineId').val();
                var Search_Protocol = $('#Payment_Protocol').val();
                var Search_Name = $('#Payment_Name').val();
                var Search_Plate = $('#Payment_Plate').val();
                var Search_Code = $('#Payment_Code').val();

                $.ajax({
                    url: 'ajax/ajx_src_finepayment.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {FineId:FineId, Search_Code: Search_Code, Search_Protocol: Search_Protocol, Search_Name: Search_Name, Search_Plate:Search_Plate},
                    success: function (data) {
                        $('#payment_content').html(data.Payment);
                        $('.fa-search-plus').show();
                    }
                });


            });
            $('.fa-archive').click(function () {
                var FineId = $(this).attr('id');
                if(FineId!=null){
                    if(confirm("Si sta per validare la notifica. Continuare?")){
                        $.ajax({
                            url: 'ajax/ajx_validate_address.php?FineId='+FineId,
                            type: 'POST',
                            dataType: 'json',
                            cache: false,
                            success: function (data) {
                                location.reload(true);
                            }
                        });
                    }
                }

            });

        });








    </script>
<?php
include(INC."/footer.php");
