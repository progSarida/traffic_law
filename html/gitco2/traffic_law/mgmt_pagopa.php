<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
include(INC."/function_postalCharge.php");

$n_RecordLimit = CheckValue('RecordLimit','n');
$s_No_Limit= ($n_RecordLimit==-1) ? " SELECTED " : "";
$s_Limit5 = ($n_RecordLimit==5) ? " SELECTED " : "";
$s_Limit25 = ($n_RecordLimit==25) ? " SELECTED " : "";
$s_Limit50 = ($n_RecordLimit==50) ? " SELECTED " : "";
$s_Limit100 = ($n_RecordLimit==100) ? " SELECTED " : "";
$s_Limit200 = ($n_RecordLimit==200) ? " SELECTED " : "";
$Search_FromId=CheckValue('Search_FromId','s');
$Search_ToId=CheckValue('Search_ToId','s');
$Search_FromProtocolId=CheckValue('Search_FromProtocolId','s');
$Search_ToProtocolId=CheckValue('Search_ToProtocolId','s');
$Search_Code=CheckValue('Search_Code','s');
$Search_VehiclePlate=CheckValue('Search_VehiclePlate','s');
$Search_FromFineDate=CheckValue('Search_FromFineDate', 's');
$Search_ToFineDate=CheckValue('Search_ToFineDate', 's');
$Search_FormTypeId = CheckValue('Search_FormTypeId','s');

$Search_Partial=CheckValue('Search_Partial','s');
$Search_Normal=CheckValue('Search_Normal','s');
$Search_Max=CheckValue('Search_Max','s');

$CurrentDate = date("Y-m-d"); //Data usata nel caso estero per trovare le spese di spedizione
$ZoneId = 0;
$ButtonEnabled = false;
$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'&PageTitle=Dati/Gestione PagoPA" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
             <div class="col-sm-1 BoxRowLabel">
                Da Id
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_FromId" type="text" style="width:10rem" value="'.$Search_FromId.'">
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                A Id
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_ToId" type="text" style="width:10rem" value="'.$Search_ToId.'">
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                Da Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_FromProtocolId" type="text" style="width:10rem" value="'.$Search_FromProtocolId.'">
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                A Cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" name="Search_ToProtocolId" type="text" style="width:10rem" value="'.$Search_ToProtocolId.'">
            </div>      
             <div class="col-sm-1 BoxRowLabel">
                Code
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control " name="Search_Code" type="text" style="width:10rem" value="'.$Search_Code.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_VehiclePlate" type="text" style="width:9rem" value="'.$Search_VehiclePlate.'">
            </div>

            <div class="clean_row HSpace4"></div>
       
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
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="RecordLimit" id="RecordLimit" />
                    
                    <option value="5"'.$s_Limit5.'>5</option>
                    <option value="25"'.$s_Limit25.'>25</option>
                    <option value="50"'.$s_Limit50.'>50</option>
                    <option value="100"'.$s_Limit100.'>100</option>
                    <option value="200"'.$s_Limit200.'>200</option>
                    <option value="-1"'.$s_No_Limit.'>TUTTI</option>
                </select>
            </div>  
            <div class="col-sm-1 BoxRowLabel">
                Ridotto
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control" name="Search_Partial" type="text" style="width:10rem" value="'.$Search_Partial.'">
            </div>      
             <div class="col-sm-1 BoxRowLabel">
                Normale
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control" name="Search_Normal" type="text" style="width:10rem" value="'.$Search_Normal.'">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Norm. (>60gg)
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control" name="Search_Max" type="text" style="width:10rem" value="'.$Search_Max.'">
            </div>          
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>	
            </div>
        </div>
    </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';

if($Search_FromId!="")
    $str_Where.=" and Id>=".$Search_FromId;
if($Search_ToId!="")
    $str_Where.=" and Id<=".$Search_ToId;
if($Search_FromProtocolId!="" && $Search_FromProtocolId>0)
    $str_Where.=" and ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId!="" && $Search_FromProtocolId>0)
    $str_Where.=" and ProtocolId<=".$Search_ToProtocolId;
if($Search_Code!="" && $Search_Code!=0)
    $str_Where.=" and Code='".$Search_Code."'";
if($Search_VehiclePlate!="")
    $str_Where.=" and VehiclePlate='".$Search_VehiclePlate."'";
if($Search_FromFineDate!="")
    $str_Where.=" and FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate!="")
    $str_Where.=" and FineDate<='".DateInDB($Search_ToFineDate)."'";

if($Search_Partial!="")
    $str_Where.=" and PagoPAReducedPartial='".$Search_Partial."'";
if($Search_Normal!="")
    $str_Where.=" and PagoPAReducedTotal='".$Search_Normal."'";
if($Search_Max!="")
    $str_Where.=" and PagoPATotal='".$Search_Max."'";

        
$str_Limit="FineDate,FineTime";
if($n_RecordLimit>=0)
    $str_Limit.=" LIMIT ".$n_RecordLimit;
    
$str_out .='    <form id="f_azzera" action="mgmt_pagopa_upd_all_exe.php?PageTitle=Dati/Gestione PagoPA" method="post">
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">Seleziona <input type="checkbox" id="checkAll" checked /></div>
				<div class="table_label_H col-sm-1">Id/Cron</div>
				<div class="table_label_H col-sm-1">Riferimento</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">IUV ridotto</div>
				<div class="table_label_H col-sm-1">IUV normale</div>
				<div class="table_label_H col-sm-1">Ridotto</div>
				<div class="table_label_H col-sm-1">Rid. (>5gg)</div>
				<div class="table_label_H col-sm-1">Normale</div>
				<div class="table_label_H col-sm-1">Norm. (>60gg)</div>
				<div class="table_label_H col-sm-1"></div>
                <div class="clean_row HSpace4"></div>';
    
//if($Search_PrintNumber!="")
//    $str_Where.=" and Id in (select Id from FineHistory where NotificationTypeId=6 and FlowId='".$Search_PrintNumber."')";
$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F")
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
    $chh_FindFilter = trim($str_Where);
    
    $str_Where .= " AND StatusTypeId<=25 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." and ((PagoPA1 is not null and PAgoPA1!='') or  (PagoPA2 is not null and PAgoPA2!=''))";
    
if($chh_FindFilter=="1=1"){
    $str_out.= '
    <div class="table_caption_H col-sm-12" style="font-size:2rem;color:orange;text-align: center">
    Inserire criteri ricerca 
    </div>';
}else {
    //$PagoPAAlias = $r_Customer['PagoPAAlias'];
    //$PagoPaIban = $r_Customer['PagoPaIban'];
    //echo $str_Where; die;
    $rs_ProcessingPagoPA = $rs->Select('V_ViolationPagoPA', $str_Where, $str_Limit);
    //$n_FineId = 0;

    if (mysqli_num_rows($rs_ProcessingPagoPA) <= 0){
        $str_out .=
        '<div class="table_caption_H col-sm-12">
    		Nessun record presente.
    	</div>
    	<div class="clean_row HSpace4"></div>';
    } else {
        while ($r_ProcessingPagoPA = mysqli_fetch_array($rs_ProcessingPagoPA)) {
            //serviva per l'azzeramento che andava fatto solo sui preinserimenti
            //if($n_FineId!=$r_ProcessingPagoPA['Id'] && $r_ProcessingPagoPA['StatusTypeId']== 10){
            $str_Check='<input type="checkbox" name="checkbox[]" value="' . $r_ProcessingPagoPA['Id'] . '" checked />';
                $n_FineId = $r_ProcessingPagoPA['Id'];
                $ButtonEnabled = true;
            //} else $str_Check = '';
            
            $ZoneId = $r_ProcessingPagoPA['ZoneId'];
            
            //$chk_ReducedPayment = false;
            $rs_AdditionalArticle=null;
            if ($r_ProcessingPagoPA['ArticleNumber'] > 1) {
                $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $n_FineId, "ArticleOrder");
            }
            
            $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=" . $r_ProcessingPagoPA['ArticleId'] . " AND Year=" . $r_ProcessingPagoPA['ProtocolYear']);
            $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
            
            //TODO NON USATO
//             if ($r_ArticleTariff['ReducedPayment'] == 1){
//                 $chk_ReducedPayment = true;
//             }
                
            $trespassers = $rs->Select('V_FineTrespasser', "FineId=" . $r_ProcessingPagoPA['Id'] . " AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
            $trespasser = mysqli_fetch_array($trespassers);
            
            $a_Importi = calcolaImporti($r_ProcessingPagoPA, $rs_AdditionalArticle, $r_ArticleTariff, $r_Customer, $CurrentDate, $ZoneId, $trespasser['PEC']);
            $a_Pago = $a_Importi['Sum'];
    
            $str_out.='<div class="table_caption_button col-sm-1" style="text-align:center">'. $str_Check .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['Id'] .' - '. $r_ProcessingPagoPA['ProtocolId'] . '/'.$r_ProcessingPagoPA['ProtocolYear'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['Code'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['FineDate'] .' '.$r_ProcessingPagoPA['FineTime'].'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['VehiclePlate'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['PagoPA1'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['PagoPA2'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['PagoPAReducedPartial'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['PagoPAReducedTotal'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['PagoPAPartial'] .'</div>';
            $str_out.=' <div class="table_caption_H col-sm-1">' . $r_ProcessingPagoPA['PagoPATotal'] .'</div>';
            $str_out.= '<div class="table_caption_button col-sm-1">';
            $str_out.= '<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$r_ProcessingPagoPA['Id'].'"><span class="glyphicon glyphicon-eye-open" style="top: 0.3rem;"></span></a> ';
            $str_out.= '<a href="mgmt_pagopa_upd_exe.php'.$str_GET_Parameter.'&PageTitle=Dati/Gestione PagoPA&FineId='.$r_ProcessingPagoPA['Id'] .'"><span data-reducedpartial="'.$a_Pago['ReducedPartial'].'" data-reducedtotal="'.$a_Pago['ReducedTotal'].'" data-partial="'.$a_Pago['Partial'].'" data-Total="'.$a_Pago['Total'].'" class="glyphicon glyphicon-pencil upd-pagopa" style="top: 0.3rem;"></span></a>';
            $str_out.= '</div>';
            $str_out.= '<div class="clean_row HSpace4"></div>';
        }
        
        if($chh_FindFilter != "1=1"){
            $str_out.=
            '<div class="table_label_H HSpace4" style="height:8rem;">
            <input'.(!$ButtonEnabled ? ' disabled' : '').' type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:2rem;" value="Aggiorna" />
        </div>';
        }
    }
}

$str_out.= '</form>';

echo $str_out;
?>
<script type="text/javascript">


    $(document).ready(function () {
        <?= require ('inc/jquery/base_search.php')?>
        
        $('#sub_Button').click(function(e) {
        	e.preventDefault();
			if (confirm('Si stanno per aggiornare i codici PagoPa selezionati, continuare?')) {
				if (confirm('Sei proprio sicuro di voler procedere?')) {
					$('#f_azzera').submit();
					$(document).reload();
				} else return false;
			} else return false;
		});
		
        $('#checkAll').change(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_azzera").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_azzera").trigger( "check" );
        });

        $("#f_azzera").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#sub_Button').prop('disabled', false);
        	else
        		$('#sub_Button').prop('disabled', true);
        });
        
        $('.upd-pagopa').on("click", function(){
        	var reducedPartial=$(this).data('reducedpartial');
        	var reducedTotal=$(this).data('reducedtotal');
        	var partial=$(this).data('partial');
        	var total=$(this).data('total');
        	if (confirm("Si stanno per aggiornare I codici PagoPA con:\nRidotto parziale:"+reducedPartial+"\nRidotto totale:"+reducedTotal+"\nParziale:"+partial+"\nTotale:"+total+"\n\nContinuare?")) {
        			return true;
        		}  
    		return false;
    });
    });
</script>
<?php
include(INC."/footer.php");