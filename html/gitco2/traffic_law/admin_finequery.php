<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


if($_SESSION['userlevel']<3){
    header("location: ".$EmpySession);
}


$Search_FineId              = CheckValue('Search_FineId','n');


$Search_TrespasserAddress   = CheckValue("Search_TrespasserAddress","s");
$Search_ProtocolYear        = CheckValue("Search_ProtocolYear","n");




$rs_Result = $rs->Select('Result', "1=1");
while ($r_Result = mysqli_fetch_array($rs_Result)){
    $a_Result[$r_Result['Id']] = $r_Result['Title'];
}
$a_GradeType = array("","I","II","III");

$a_DisputeStatusId = array("","#DDD728","#3C763D","#A94442");

$b_submit = CheckValue('b_submit','n');




if($Search_FineId >0)    $str_Where .= " AND Id=".$Search_FineId;
if($Search_TrespasserAddress !="")    $str_Where .= " AND Address LIKE '%".$Search_TrespasserAddress."%'";
if($Search_ProtocolYear >0)    $str_Where .= " AND ProtocolYear=".$Search_ProtocolYear;


$strOrder = "FineDate";


$str_GET_Parameter .="&Search_FineId=" . $Search_FineId."&Search_TrespasserAddress=" . $Search_TrespasserAddress."&Search_ProtocolYear=" . $Search_ProtocolYear;


$str_out .='
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <input type="hidden" value="1" name="b_submit">
    <div class="col-sm-12" >
        <div class="col-sm-11">
            <div class="col-sm-12 BoxRow" style="height:2.3rem;">
                <div class="col-sm-1 BoxRowLabel">
                    Targa
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Plate" type="text" style="width:10rem" value="'.$Search_Plate.'">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_numeric" name="Search_ProtocolId" type="text" style="width:10rem" value="'.$Search_ProtocolId.'">
                </div>                            
                <div class="col-sm-1 BoxRowLabel">
                    Prot/Ref
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:10rem" value="'.$Search_Ref.'">
                </div>
                <div class="col-sm-1 BoxRowLabel"> 
                    Anno verbale
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_ProtocolYear" type="text" style="width:10rem" value="'.$Search_ProtocolYear.'">
                </div>
                <div class="col-sm-1 BoxRowLabel"> 
                    Ente
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. CreateSelect("Customer","1=1","ManagerCity","Search_Customer","CityId","ManagerCity",$Search_Customer,false) .'
                </div>
            </div>
            <div class="col-sm-12 BoxRow" style="height:2.3rem;">
                <div class="col-sm-2 BoxRowLabel"> 
                    Cognome/Rag Soc Tragressore
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Trespasser" type="text" style="width:10rem" value="'.$Search_Trespasser.'">
                </div>
                <div class="col-sm-2 BoxRowLabel"> 
                    Nome Tragressore
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_TrespasserName" type="text" style="width:10rem" value="'.$Search_TrespasserName.'">
                </div>
                <div class="col-sm-2 BoxRowLabel"> 
                    Indirizzo Tragressore
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_TrespasserAddress" type="text" style="width:10rem" value="'.$Search_TrespasserAddress.'">
                </div>
                <div class="col-sm-1 BoxRowLabel"> 
                    ID Verbale
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_numeric" name="Search_FineId" type="text" style="width:10rem" value="'.$Search_FineId.'">
                </div>                
            </div>        
        </div>    
        <div class="col-sm-1 BoxRow"  style="height:4.6rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.3rem;font-size:1.6rem;"></i>
            </div>    	
        </div>
    </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
    
<div class="row-fluid">
    <div class="col-sm-12">
        <div class="table_label_H col-sm-1">Ente</div>
        <div class="table_label_H col-sm-1">Cron</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Targa</div>
        <div class="table_label_H col-sm-3">Trasgressore</div>
        <div class="table_label_H col-sm-1">Stato</div>
        <div class="table_label_H col-sm-2">Avanzamento verbale</div>
        <div class="table_label_H col-sm-1">&nbsp;</div>
        <div class="clean_row HSpace4"></div>';





if(! $b_submit){

    $str_out.= '<div class="table_caption_H col-sm-12">Selezionare un filtro</div>';
    echo $str_out;
} else{

    $table_rows = $rs->Select('V_FineQuery',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);


    $RowNumber = mysqli_num_rows($table_rows);

    if ($RowNumber == 0) {
        $str_out.= '<div class="table_caption_H col-sm-12">Nessun record presente</div>';
    } else {
        while ($table_row = mysqli_fetch_array($table_rows)) {

            $rs_Row = $rs->Select('V_FineHistory',"Id=".$table_row['Id']." AND NotificationTypeId=6");
            $r_Row = mysqli_fetch_array($rs_Row);

            $str_out.=
                '<div class="table_caption_H col-sm-1">' . $table_row['ManagerCity'] .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'].' / '.$table_row['ProtocolYear'].'</div>
        	<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
        	<div class="table_caption_H col-sm-1">' . $table_row['FineTime'] .'</div>
        	<div class="table_caption_H col-sm-1">' . StringOutDB($table_row['VehiclePlate']) .'</div>
			<div class="table_caption_H col-sm-3">' . StringOutDB($table_row['CompanyName'] .' '.$table_row['Surname'] .' '.$table_row['Name']) .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['StatusTypeTitle']  .'</div>';
            $Status = '';
            $Status .= (! is_null($r_Row['FlowDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso creato in data '. DateOutDB($r_Row['FlowDate']).'"><i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<i class="fa fa-sort-amount-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
            $Status .= (! is_null($r_Row['PrintDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Flusso stampato in data '. DateOutDB($r_Row['PrintDate']).'"><i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<i class="fa fa-print" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
            $Status .= (! is_null($r_Row['SendDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale inviato in data '. DateOutDB($r_Row['SendDate']).'"><i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<i class="fa fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';


            if (! is_null($r_Row['ResultId'])) {
                if (! is_null($r_Row['DeliveryDate'])) {
                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale notificato in data '. DateOutDB($r_Row['DeliveryDate']).'"><i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:green;"></i></span>';
                    $str_DeliveryStatus = '<a href="mgmt_notification_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
                }else{
                    $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="'.$a_Result[$r_Row['ResultId']].'"><i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:red;"></i></span>';
                    $str_DeliveryStatus = '<a href="mgmt_notification_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><i class="fa fa-list-alt" style="position:absolute;left:45px;top:5px;"></i></a>';
                }

            } else {
                $Status .= '<i class="fa fa-envelope-square" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';

                $str_DeliveryStatus = '&nbsp;';
            }
/*
            $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['Id']);
            if(mysqli_num_rows($rs_Row)>0){
                $r_Row = mysqli_fetch_array($rs_Row);
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale pagato in data '. DateOutDB($r_Row['PaymentDate']).'"><i id="'.$r_Row['Id'].'" class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>';
            }else $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';
*/



            $rs_Row = $rs->Select('V_FineDispute',"FineId=".$table_row['Id']." ORDER BY GradeTypeId DESC");
            if(mysqli_num_rows($rs_Row)>0){
                $r_Row = mysqli_fetch_array($rs_Row);
                $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="'.$a_GradeType[$r_Row['GradeTypeId']].' Grado - '.$r_Row['OfficeTitle'].' '. $r_Row['OfficeCity'].' Depositato in data '. DateOutDB($r_Row['DateFile']) .'"><i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:'.$a_DisputeStatusId[$r_Row['DisputeStatusId']].'"></i></span>';

            } else $Status .= '<i class="fa fa-gavel" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';


            $rs_Row = $rs->Select('FineCommunication',"FineId=".$table_row['Id']);
            $r_Row = mysqli_fetch_array($rs_Row);
            $Status .= (! is_null($r_Row['CommunicationDate'])) ? '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Comunicazione presentata in data '.DateOutDB($r_Row['CommunicationDate']).'"><i class="fa fa-address-card-o" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i></span>' : '<i class="fa fa-address-card-o" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1"></i>';


            $str_LocationPage = ($table_row['ProtocolId']>0) ? "mgmt_fine_viw.php" : "mgmt_violation_viw.php";

            $str_DocumentFolder = ($table_row['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$table_row['Id'] : FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$table_row['Id'];

            $str_out.=
                '<div class="table_caption_H col-sm-2">' . $Status .'</div>
			<div class="table_caption_button col-sm-1">
				<a href="'.$str_LocationPage.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>
				&nbsp;
			</div>
			<div class="clean_row HSpace4"></div>';
        }
    }
    $table_users_number = $rs->Select('V_FineQuery',$str_Where);
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


    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");
    $str_out.= '<div>
	</div>';


    echo $str_out;
}


?>
<div class="overlay" id="overlay" style="display:none;"></div>

<div id="overlay_PaymentView">
    <div id="FormTrespasser">
    </div>
</div>

<script type="text/javascript">


	$(document).ready(function () {

        $('.glyphicon-search').click(function(){
            $('#f_Search').submit();
        });

        $('#f_Search').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                $("#f_Search").submit();
            }
        });

        <?= require ('inc/jquery/overlay_search_payment.php')?>



	});

</script>
<?php
include(INC."/footer.php");


