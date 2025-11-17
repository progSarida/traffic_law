<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$str_Union = CreateSelectCustomerUnion($Search_Locality);


$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
$strOrder = "FineDate ASC, FineTime ASC";




$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F"){
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";
}

$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-2 BoxRowLabel">
                
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
                <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:10rem" value="'.$Search_Ref.'">
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
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false,12) .'
            </div>                                                
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("StatusType","Id<=10","Id","Search_Status","Id","Title",$Search_Status,false,16) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" style="width:12rem">
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
				<div class="table_label_H col-sm-1">Rif.to</div>
				<div class="table_label_H col-sm-1">Stato</div>
				<div class="table_label_H col-sm-2">Dati atto</div>
				<div class="table_label_H col-sm-3">Proprietario / Obbligato / Noleggio</div>
				<div class="table_label_H col-sm-3">Trasgressore / Noleggiante</div>
				<div class="table_label_H col-sm-1">Articoli</div>
        		<div class="table_add_button col-sm-1 right">
        			
        				'.ChkButton($aUserButton, 'add','
                            <a href="mgmt_report_add.php'.$str_GET_Parameter.'&insertionType=3">
                            <span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
                            </a>
                            ').'      				
					
				</div>
				<div class="clean_row HSpace4"></div>';




$rs_Violation = $rs->Select('V_mgmt_RegularViolationTrespasser',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($rs_Violation);

if ($RowNumber == 0) {
	$str_out.=
		'<div class="table_caption_H col-sm-14">
			Nessun record presente
		</div>';
} else {
	while ($r_Violation = mysqli_fetch_array($rs_Violation)) {
        $a_TrespasserTypeId     = array();
        $a_TrespasserId         = array();
        $a_TrespasserFullName   = array();
        $a_FineNotificationDate = array();




        $str_RegularPostalFine = ($r_Violation['StatusTypeId']==7 || $r_Violation['StatusTypeId']==9) ? '<i style="color:#fff;" class="fa fa-reply" id="'.$r_Violation['FineId'].'"></i> ' : '';


        if (strpos($r_Violation['TrespasserId'], "|") === false) {

            $a_TrespasserId[$r_Violation['TrespasserTypeId']]          = $r_Violation['TrespasserId'];
            $a_TrespasserFullName[$r_Violation['TrespasserTypeId']]    = $r_Violation['TrespasserFullName'];
            $a_FineNotificationDate[$r_Violation['TrespasserTypeId']]  = $r_Violation['FineNotificationDate'];

        } else {

            $a_Tmp_TrespasserTypeId     = explode("|", $r_Violation['TrespasserTypeId']);
            $a_Tmp_TrespasserId         = explode("|", $r_Violation['TrespasserId']);
            $a_Tmp_TrespasserFullName   = explode("|", $r_Violation['TrespasserFullName']);
            $a_Tmp_FineNotificationDate = explode("|", $r_Violation['FineNotificationDate']);;

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

        $str_VehicleType = '<i class="'.$aVehicleTypeId[$r_Violation['VehicleTypeId']].'" style="color:#337AB7;"></i>';

        $str_FineData = $a_FineTypeId[$r_Violation['FineTypeId']].' '. DateOutDB($r_Violation['FineDate']) .' - ' . TimeOutDB($r_Violation['FineTime']).' <span style="position:absolute; right:0.5rem;">'.StringOutDB($r_Violation['VehiclePlate']).' '.$str_VehicleType.'</span>';
        $str_ArticleNumber = ($r_Violation['ArticleNumber']>1) ? '<i class="fa fa-list-ol" style="position:absolute;right:2rem;top:0.3rem; color:#337AB7; font-size:1.6rem;"></i>' : '';

        $Status = '';

        $str_ButtonController = "";
        $str_ButtonController = ($_SESSION['usertype']==2 && $r_Violation['ControllerId'] == "") ? '<i id="'.$r_Violation['FineId'].'" class="fa fa-cogs" style="position:absolute;color:#fff; left:0.1rem;font-size:1.7rem;top:0.2rem;"></i>' : '';

        if($r_Violation['StatusTypeId']==8) $Status = $str_ButtonController. '<i class="fa fa-envelope " style="color:#E0DB75; margin-top:0.1rem;"></i>';
        else if($r_Violation['StatusTypeId']==9) $Status = $str_ButtonController. '<i class="fa fa-envelope" style="color:#3C763D; margin-top:0.1rem;"></i>';
        else if($r_Violation['TrespasserId'] != ""){
            $Status = ($r_Violation['ControllerId'] == "") ? $str_ButtonController.'<i id="icon_'.$r_Violation['FineId'].'" class="fa fa-battery-three-quarters" style="color:rgba(107,155,29,0.76); margin-top:0.2rem;"></i>' : '<i class="fa fa-battery-full" title="Trasgressore associato" style="color:#3C763D; margin-top:0.2rem;"></i>';
        }
        else if($r_Violation['StatusTypeId']==5) $Status = $str_ButtonController.'<i class="fa fa-battery-half" style="color:#E0DB75; margin-top:0.2rem;"></i>';
        else $Status = $str_ButtonController.'<i class="fa fa-battery-quarter" title="Non e associato un trasgressore" style="color:#C43A3A; margin-top:0.2rem;"></i>';

        $str_out.= '
        	<div class="table_caption_H col-sm-1">' . $str_RegularPostalFine . $r_Violation['Code'].'</div>
        	<div class="table_caption_H col-sm-1" style="text-align:center;font-size:2rem;">' . $Status .'</div>
        	<div class="table_caption_H col-sm-2">' . $str_FineData .'</div>
			<div class="table_caption_H col-sm-3">' . StringOutDB($str_Trespasser1) .'</div>
			<div class="table_caption_H col-sm-3">' . StringOutDB($str_Trespasser2) .'</div>
			<div class="table_caption_H col-sm-1">' . $r_Violation['Article'] .'/'.$r_Violation['Paragraph'].' '. $r_Violation['Letter'] . ' '. $str_ArticleNumber .'</div>
			';



		$str_out.= '<div class="table_caption_button col-sm-1">';
		$str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$r_Violation['FineId'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');

		$str_out.= '</div>
			            <div class="clean_row HSpace4"></div>';
		}


}
$table_users_number = $rs->Select('V_mgmt_ViolationTrespasser',$str_Where);
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage, $str_FineTypeLabel);
$str_out.= '<div>
	</div>';

echo $str_out;
?>

<script type="text/javascript">

	$(document).ready(function () {

        $('.fa-reply').click(function () {
            var Id = $(this).attr('id');
            if(confirm("Vuoi trasformare questo preinserimento in spedizione ordinaria?")){

                $.ajax({
                    url: 'ajax/ajx_violation_act_exe.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Id: Id}
                });
                window.location.reload(true);
            }

        });



	    <?= require ('inc/jquery/base_search.php')?>


        $(".fa-cogs, .fa-reply").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });


        /*
        $(".fa-cogs").click(function () {

            var id=$(this).attr('id');

            $(this).hide();

            $.ajax({
                url: 'ajax/ajx_upd_controller_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id: id},
                success: function () {

                    $('#icon_'+id).removeClass('fa-battery-three-quarters').addClass('fa-battery-full').css('color','#3C763D');

                }
            });
        });
        */


	});
</script>
<?php
include(INC."/footer.php");
