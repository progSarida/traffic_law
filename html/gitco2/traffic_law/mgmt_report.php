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
$a_StatusTypeId[8] = "#928703";
$a_StatusTypeId[9] = "#3C763D";

$str_Union = CreateSelectCustomerUnion($Search_Locality);

$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND StatusTypeId != 32";
$strOrder = "ProtocolId, TrespasserTypeId";

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}

$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Cron
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
                '. CreateSelect("ViolationType","1=1 AND RuleTypeId = $RuleTypeId","Id","Search_Violation","Id","Title",$Search_Violation,false,9) .'
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
                '.$RuleTypeTitle.'
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
                <input class="form-control frm_field_string" name="Search_TrespasserFullName" type="text" style="width:9rem" value="'.$Search_TrespasserFullName.'">
            </div>            
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>	
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
				<div class="table_label_H col-sm-1">Cron </div>
				<div class="table_label_H col-sm-1">Ref</div>
				<div class="table_label_H col-sm-2">Dati atto</div>
				<div class="table_label_H col-sm-3">Proprietario / Obbligato / Noleggio</div>
				<div class="table_label_H col-sm-3">Trasgressore / Noleggiante</div>
				<div class="table_label_H col-sm-1">Articoli</div>
        		<div class="table_add_button col-sm-1 right">
        			
        				'.ChkButton($aUserButton, 'add','
                            <a href="mgmt_report_add.php'.$str_GET_Parameter.'&insertionType=1">
                            <span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
                            </a>
                            ').'      				
					
				</div>
				<div class="clean_row HSpace4"></div>';



$rs_Report = $rs->Select('V_mgmt_ReportTrespasser',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);

$n_RowNumber = mysqli_num_rows($rs_Report);

if ($n_RowNumber == 0) {
    $str_out.= 'Nessun record presente';
} else {
    while ($r_Report = mysqli_fetch_array($rs_Report)) {
        $a_TrespasserTypeId     = array();
        $a_TrespasserId         = array();
        $a_TrespasserFullName   = array();
        $a_FineNotificationDate = array();
        
        $str_Style      = (isset($a_StatusTypeId[$r_Report['StatusTypeId']])) ? ' style="color:'.$a_StatusTypeId[$r_Report['StatusTypeId']].';"' : '';


        if (strpos($r_Report['TrespasserId'], "|") === false) {

            $a_TrespasserId[$r_Report['TrespasserTypeId']]          = $r_Report['TrespasserId'];
            $a_TrespasserFullName[$r_Report['TrespasserTypeId']]    = $r_Report['TrespasserFullName'];
            $a_FineNotificationDate[$r_Report['TrespasserTypeId']]  = $r_Report['FineNotificationDate'];

        } else {

            $a_Tmp_TrespasserTypeId     = explode("|", $r_Report['TrespasserTypeId']);
            $a_Tmp_TrespasserId         = explode("|", $r_Report['TrespasserId']);
            $a_Tmp_TrespasserFullName   = explode("|", $r_Report['TrespasserFullName']);
            $a_Tmp_FineNotificationDate = explode("|", $r_Report['FineNotificationDate']);;

            for($i=0; $i<count($a_Tmp_TrespasserId); $i++){
                $a_TrespasserId[$a_Tmp_TrespasserTypeId[$i]]          = $a_Tmp_TrespasserId[$i];
                $a_TrespasserFullName[$a_Tmp_TrespasserTypeId[$i]]    = $a_Tmp_TrespasserFullName[$i];
                $a_FineNotificationDate[$a_Tmp_TrespasserTypeId[$i]]  = $a_Tmp_FineNotificationDate[$i];
            }

        }

        $str_Trespasser1 = $str_Trespasser2 = '';
        $str_NotificationDate1 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';
        if(isset($a_TrespasserId[1]) || isset($a_TrespasserId[2]) || isset($a_TrespasserId[10])){
            if(isset($a_TrespasserId[1])){
                $str_Trespasser1 = $a_TrespasserFullName[1];
                $n_AssingedIndex = 1;
            } else if(isset($a_TrespasserId[2])){
                $str_Trespasser1 = $a_TrespasserFullName[2];
                $n_AssingedIndex = 2;
            } else{
                $str_Trespasser1 = $a_TrespasserFullName[10];
                $n_AssingedIndex = 10;
            }


            if($a_FineNotificationDate[$n_AssingedIndex]!="") $str_NotificationDate1 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data '. DateOutDB($a_FineNotificationDate[$n_AssingedIndex]).'"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';


            $str_Trespasser1 = (strlen($str_Trespasser1)>33) ? substr($str_Trespasser1,0,30).'...' : $str_Trespasser1;

            $str_Trespasser1 = $str_NotificationDate1 . $str_Trespasser1;

        }
        $str_NotificationDate2 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';
        if(isset($a_TrespasserId[3]) || isset($a_TrespasserId[11])){
            if(isset($a_TrespasserId[3])){
                $str_Trespasser2 = $a_TrespasserFullName[3];
                $n_AssingedIndex = 3;
            } else {
                $str_Trespasser2 = $a_TrespasserFullName[11];
                $n_AssingedIndex = 11;
            }
            if($a_FineNotificationDate[$n_AssingedIndex]!="") $str_NotificationDate2 = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data '. DateOutDB($a_FineNotificationDate[$n_AssingedIndex]).'"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
            $str_Trespasser2 = (strlen($str_Trespasser2)>33) ? substr($str_Trespasser2,0,30).'...' : $str_Trespasser2;


            $str_Trespasser2 = $str_NotificationDate2 . $str_Trespasser2;

        }

        $str_VehicleType = '<i class="'.$aVehicleTypeId[$r_Report['VehicleTypeId']].'" style="color:#337AB7;"></i>';

        $str_FineData = $a_FineTypeId[$r_Report['FineTypeId']].' '. DateOutDB($r_Report['FineDate']) .' - ' . TimeOutDB($r_Report['FineTime']).' <span style="position:absolute; right:0.5rem;">'.StringOutDB($r_Report['VehiclePlate']).' '.$str_VehicleType.'</span>';
        $str_ArticleNumber = ($r_Report['ArticleNumber']>1) ? '<i class="fa fa-list-ol" style="position:absolute;right:2rem;top:0.3rem; color:#337AB7; font-size:1.6rem;"></i>' : '';

        $Status = '';
        $KindFine = false;
        //bonario
        if ($r_Report['StatusTypeId']==8 || $r_Report['StatusTypeId']==9){
            $KindFine = true;
            $rs_FineHistory = $rs->Select('FineHistory', "FineId=".$r_Report['FineId']." AND NotificationTypeId=30");
            $r_FineHistory = mysqli_fetch_assoc($rs_FineHistory);
        }
        if($r_Report['StatusTypeId']==8){
            $Status .= '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario'.(!is_null($r_FineHistory['FlowDate']) ? ' - creato in data '.DateOutDB($r_FineHistory['FlowDate']) : '').'"><i class="fas fa-wallet" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
        }else if($r_Report['StatusTypeId']==9){
            $Status .= '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario'.(!is_null($r_FineHistory['SendDate']) ? ' - inviato in data '.DateOutDB($r_FineHistory['SendDate']) : '').'"><i class="fas fa-wallet" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
        }
        
		$str_out.= '
			<div class="table_caption_H col-sm-1" '.$str_Style.'>' . $r_Report['ProtocolId'].' / '.$r_Report['ProtocolYear'].$Status.'</div>
        	<div class="table_caption_H col-sm-1" '.$str_Style.'>' . $r_Report['Code'].'</div>
        	<div class="table_caption_H col-sm-2" '.$str_Style.'>' . $str_FineData .'</div>
			<div class="table_caption_H col-sm-3">' . StringOutDB($str_Trespasser1) .'</div>
			<div class="table_caption_H col-sm-3">' . StringOutDB($str_Trespasser2) .'</div>
			<div class="table_caption_H col-sm-1">' . $r_Report['Article'] .'/'.$r_Report['Paragraph'].' '. $r_Report['Letter'] . ' '. $str_ArticleNumber .'</div>			
			';


		$str_out.=
			'

			<div class="table_caption_button col-sm-1">
				'. ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Report['FineId'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>') .'
				'. (!$KindFine ? ChkButton($aUserButton, 'upd','<a href="mgmt_report_upd.php'.$str_GET_Parameter.'&Id='.$r_Report['FineId'].'"><span class="glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>') : '');



        if(is_null($r_Report['TrespasserId'])){
            $str_out.= !$KindFine ? ChkButton($aUserButton, 'act','<a href="mgmt_violation_trespasser.php'.$str_GET_Parameter.'&Id='.$r_Report['FineId'].'"><span class="fa fa-user-plus" style="font-size:1.6rem;position:absolute;left:45px;top:2px;"></span></a>') : '';
        } else {
            $str_out.= !$KindFine ? ChkButton($aUserButton, 'act','<a href="mgmt_violation_trespasser.php'.$str_GET_Parameter.'&Id='.$r_Report['FineId'].'"><span class="fa fa-user-times" style="font-size:1.6rem;position:absolute;left:45px;top:2px;"></span></a>') : '';
        }



		$str_out.='
			</div>
			<div class="clean_row HSpace4"></div>';
	}
}
$table_users_number = $rs->Select('V_mgmt_ReportTrespasser',$str_Where);

$UserNumberTotal = mysqli_num_rows($table_users_number);



$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage, $str_FineTypeLabel);
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


	});








</script>
<?php
include(INC."/footer.php");
