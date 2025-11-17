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
$a_Euro[27] = "A94442";//ROSSO
$a_Euro[28] = "c9c427";//GIALLO
$a_Euro[30] = "3C763D";//VERDE

$str_Union = CreateSelectCustomerUnion($Search_Locality);


$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
$strOrder = "ProtocolId";

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}

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
    </div>    ';

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
				<div class="table_label_H col-sm-2">Trasgressore / Noleggiante</div>
                <div class="table_label_H col-sm-1">Stato</div>
				<div class="table_label_H col-sm-1">Articoli</div>
        		<div class="table_add_button col-sm-1 right">
        			
        				'.ChkButton($aUserButton, 'add','
                            <a href="mgmt_report_add.php'.$str_GET_Parameter.'&insertionType=2">
                            <span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
                            </a>
                            ').'      				
					
				</div>
				<div class="clean_row HSpace4"></div>';



$rs_Warning = $rs->Select('V_mgmt_WarningTrespasser',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);


$RowNumber = mysqli_num_rows($rs_Warning);

if ($RowNumber == 0) {
	$str_out.= 'Nessun record presente';
} else {
	while ($r_Warning = mysqli_fetch_array($rs_Warning)) {

        $a_TrespasserTypeId     = array();
        $a_TrespasserId         = array();
        $a_TrespasserFullName   = array();
        $a_FineNotificationDate = array();
        
        $rs_FineHistoryTrespasser = $rs->Select('mgmt_FineHistory_Trespasser',"FineId=".$r_Warning['FineId']);
        $r_FineHistoryTrespasser = mysqli_fetch_array($rs_FineHistoryTrespasser);
        
        $str_Style      = (isset($a_StatusTypeId[$r_Warning['StatusTypeId']])) ? ' style="color:'.$a_StatusTypeId[$r_Warning['StatusTypeId']].';"' : '';
        $str_CssEuro    = (isset($a_Euro[$r_Warning['StatusTypeId']])) ? '#' . $a_Euro[$r_Warning['StatusTypeId']] : '#000';
        $str_Euro       = (isset($a_Euro[$r_Warning['StatusTypeId']])) ? $a_Euro[$r_Warning['StatusTypeId']] : '000';

        if (strpos($r_Warning['TrespasserId'], "|") === false) {

            $a_TrespasserId[$r_Warning['TrespasserTypeId']]          = $r_Warning['TrespasserId'];
            $a_TrespasserFullName[$r_Warning['TrespasserTypeId']]    = $r_Warning['TrespasserFullName'];
            $a_FineNotificationDate[$r_Warning['TrespasserTypeId']]  = $r_Warning['FineNotificationDate'];

        } else {

            $a_Tmp_TrespasserTypeId     = explode("|", $r_Warning['TrespasserTypeId']);
            $a_Tmp_TrespasserId         = explode("|", $r_Warning['TrespasserId']);
            $a_Tmp_TrespasserFullName   = explode("|", $r_Warning['TrespasserFullName']);
            $a_Tmp_FineNotificationDate = explode("|", $r_Warning['FineNotificationDate']);;

            for($i=0; $i<count($a_Tmp_TrespasserId); $i++){
                $a_TrespasserId[$a_Tmp_TrespasserTypeId[$i]]          = $a_Tmp_TrespasserId[$i];
                $a_TrespasserFullName[$a_Tmp_TrespasserTypeId[$i]]    = $a_Tmp_TrespasserFullName[$i];
                $a_FineNotificationDate[$a_Tmp_TrespasserTypeId[$i]]  = $a_Tmp_FineNotificationDate[$i];
            }

        }

        $str_Trespasser1 = $str_Trespasser2 = '';
        $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i></span>';
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


            if($a_FineNotificationDate[$n_AssingedIndex]!="") $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data '. DateOutDB($a_FineNotificationDate[$n_AssingedIndex]).'"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';


            $str_Trespasser1 = (strlen($str_Trespasser1)>33) ? substr($str_Trespasser1,0,30).'...' : $str_Trespasser1;

            $str_Trespasser1 = $str_NotificationDate . $str_Trespasser1;

        }
        if(isset($a_TrespasserId[3]) || isset($a_TrespasserId[11])){
            if(isset($a_TrespasserId[3])){
                $str_Trespasser2 = $a_TrespasserFullName[3];
                $n_AssingedIndex = 3;
            } else {
                $str_Trespasser2 = $a_TrespasserFullName[11];
                $n_AssingedIndex = 11;
            }
            if($a_FineNotificationDate[$n_AssingedIndex]!="") $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data '. DateOutDB($a_FineNotificationDate[$n_AssingedIndex]).'"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
            $str_Trespasser2 = (strlen($str_Trespasser2)>33) ? substr($str_Trespasser2,0,30).'...' : $str_Trespasser2;


            $str_Trespasser2 = $str_NotificationDate . $str_Trespasser2;

        }
        $str_VehicleType = '<i class="'.$aVehicleTypeId[$r_Warning['VehicleTypeId']].'" style="color:#337AB7;"></i>';

        $str_FineData = $a_FineTypeId[$r_Warning['FineTypeId']].' '. DateOutDB($r_Warning['FineDate']) .' - ' . TimeOutDB($r_Warning['FineTime']).' <span style="position:absolute; right:0.5rem;">'.StringOutDB($r_Warning['VehiclePlate']).' '.$str_VehicleType.'</span>';
        $str_ArticleNumber = ($r_Warning['ArticleNumber']>1) ? '<i class="fa fa-list-ol" style="position:absolute;right:2rem;top:0.3rem; color:#337AB7; font-size:1.6rem;"></i>' : '';

        //stato pagamento
        $Status = "";
        if($r_FineHistoryTrespasser['PaymentDate']!=""){
            $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale pagato in data '. DateOutDB($r_FineHistoryTrespasser['PaymentDate']).'"><i id="'.$r_FineHistoryTrespasser['PaymentId'].'" class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:'.$str_CssEuro.'" name="'.$str_Euro.'"></i></span>';
        }else if($r_Warning['StatusTypeId']==27 && $_SESSION['userlevel']>=7) $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
        else{
            
            if($_SESSION['usertype']>50) {
                $Status .= '
                    <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Cerca pagamento">
                        <i class="fa fa-eur src_payment" fineid="'.$r_Warning['FineId'].'" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.8rem;opacity:.2"></i>
                    </span>
                ';
            }else{
                $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1;color:'.$str_CssEuro.'" name="'.$str_Euro.'"></i>';
            }
        }

        $str_out.= '
			<div class="table_caption_H col-sm-1"><i class="fa fa-reply" id="'.$r_Warning['FineId'].'"></i> ' . $r_Warning['ProtocolId'].' / '.$r_Warning['ProtocolYear'].'</div>
            <div class="table_caption_H col-sm-1">' . $r_Warning['Code'].'</div>
        	<div class="table_caption_H col-sm-2">' . $str_FineData .'</div>
			<div class="table_caption_H col-sm-3">' . StringOutDB($str_Trespasser1) .'</div>
			<div class="table_caption_H col-sm-2">' . StringOutDB($str_Trespasser2) .'</div>
            <div class="table_caption_H col-sm-1">' . $Status .'</div>
            <div class="table_caption_H col-sm-1">' . $r_Warning['Article'] .'/'.$r_Warning['Paragraph'].' '. $r_Warning['Letter'] . ' '. $str_ArticleNumber .'</div>			
			';




		$str_out.=
			'
			<div class="table_caption_button col-sm-1">
				'. ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Warning['FineId'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>') .'
				'. ChkButton($aUserButton, 'upd','<a href="mgmt_report_upd.php'.$str_GET_Parameter.'&Id='.$r_Warning['FineId'].'"><span class="glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');



        if(is_null($r_Warning['TrespasserId'])){
            $str_out.= ChkButton($aUserButton, 'act','<a href="mgmt_violation_trespasser.php'.$str_GET_Parameter.'&Id='.$r_Warning['FineId'].'"><span class="fa fa-user-plus" style="font-size:1.6rem;position:absolute;left:45px;top:2px;"></span></a>');
        } else {
            $str_out.= ChkButton($aUserButton, 'act','<a href="mgmt_violation_trespasser.php'.$str_GET_Parameter.'&Id='.$r_Warning['FineId'].'"><span class="fa fa-user-times" style="font-size:1.6rem;position:absolute;left:45px;top:2px;"></span></a>');
        }




		$str_out.='
			</div>
			<div class="clean_row HSpace4"></div>';
	}
}
$table_users_number = $rs->Select('V_mgmt_WarningTrespasser',$str_Where);

$UserNumberTotal = mysqli_num_rows($table_users_number);



$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,$str_FineTypeLabel);
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

        $('.fa-reply').click(function() {
            var Id = $(this).attr('id');

            var c = confirm("Vuoi trasformare questo preavviso in verbale?");
            if(c){
                window.location="mgmt_warning_act_exe.php?Id="+Id;
            }

        });
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

	});








</script>
<?php
include(INC."/footer.php");
