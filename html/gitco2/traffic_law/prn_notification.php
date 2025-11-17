<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_GETLink = "?1";



$FromDate= CheckValue('FromDate','s');
$ToDate= CheckValue('ToDate','s');

$FromProtocolId= CheckValue('FromProtocolId','n');
$ToProtocolId= CheckValue('ToProtocolId','n');



$DateType = CheckValue('DateType','n');

$CurrentYear = CheckValue('CurrentYear','n');


$str_CheckSendDate = "";
$str_CheckNotificationDate = "";
$str_CheckRegDate = "";

$str_CheckCurrentYear = "";


$a_PaymentField  = array("SendDate","NotificationDate","RegDate");

if($DateType==0){
    $str_CheckSendDate = " CHECKED ";
}
if($DateType==1){
    $str_CheckNotificationDate = " CHECKED ";
}
if($DateType==2){
    $str_CheckRegDate = " CHECKED ";
}




$str_Where = "CityId='".$_SESSION['cityid']."'";

if($FromDate == "" && $ToDate=="") {
    $str_FromToDate = '
        <div class="col-sm-3 BoxRowCaption" style="font-size:1rem">
            Da <input name="FromDate" type="text" style="width:12rem" value="'.date("01/01/".$_SESSION['year']).'">
        </div>
        <div class="col-sm-3 BoxRowCaption" style="font-size:1rem">
            A <input name="ToDate" type="text" style="width:12rem" value="'.date("31/12/".$_SESSION['year']).'">
        </div>
    ';
} else {
    $str_FromToDate = '
        <div class="col-sm-3 BoxRowCaption" style="font-size:1rem">
            Da <input name="FromDate" type="text" style="width:12rem" value="'.$FromDate.'">
        </div>
        <div class="col-sm-3 BoxRowCaption" style="font-size:1rem">
            A <input name="ToDate" type="text" style="width:12rem" value="'.$ToDate.'">
        </div>
    ';
}

if($FromDate!=""){
	$str_Where .= " AND ".$a_PaymentField[$DateType].">= '".DateInDB($FromDate)  ."'";
	$str_CurrentPage .="&FromDate=".$FromDate;
	$str_GETLink.="&FromDate=".$FromDate;
}
if($ToDate!=""){
    $str_Where .= " AND ".$a_PaymentField[$DateType]."<= '".DateInDB($ToDate)  ."'";
	$str_CurrentPage .="&ToDate=".$ToDate;
	$str_GETLink.="&ToDate=".$ToDate;
}

if($FromProtocolId>0){
    $str_Where .= " AND ProtocolId >= $FromProtocolId";
    $str_CurrentPage .="&FromProtocolId=".$FromProtocolId;
    $str_GETLink.="&FromProtocolId=".$FromProtocolId;
}else{
    $FromProtocolId="";
}

if($ToProtocolId>0){
    $str_Where .= " AND ProtocolId <= $ToProtocolId";
    $str_CurrentPage .="&ToProtocolId=".$ToProtocolId;
    $str_GETLink.="&ToProtocolId=".$ToProtocolId;
}else{
    $ToProtocolId="";
}






if($CurrentYear){
    $str_Where .= " AND ProtocolYear =".$_SESSION['year'];
    $str_CurrentPage .="&CuttentYear=".$CurrentYear;
    $str_GETLink.="&CuttentYear=".$CurrentYear;
    $str_CheckCurrentYear =" CHECKED ";
}





if($r_Customer['CityUnion']>1){
    $Locality= CheckValue('Locality','s');

    if($Locality!=""){
        $str_Where .= " AND Locality = '".$Locality."'";
        $str_CurrentPage .="&Locality=".$Locality;
        $str_GETLink.="&Locality=".$Locality;
    }


    $str_Union ='
        <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
            Località:
        </div>
        <div class="col-sm-4 BoxRowCaption" style="font-size:1rem">
            '. CreateSelect(MAIN_DB.".City","UnionId='".$_SESSION['cityid']."'","Title","Locality","Id","Title",$Locality,false) .'
        </div>

    ';



}else{
    $str_Union= '';

}








$strOrder = "ProtocolYear, ProtocolId";


$rs= new CLS_DB();
$rs->SetCharset('utf8');
$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-6" style="background-color: #fff;height:11rem">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
				</div>
				
				<div class="col-sm-6 BoxRow" style="height:11rem;">
                    <form id="f_Search" action="'.$FormPage.'" method="post">
                    <div class="col-sm-12 BoxRow">
                       <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                            Ricerca cron:
                        </div>
                        <div class="col-sm-2 BoxRowCaption" style="font-size:1rem">
                            Da <input name="FromProtocolId" type="text" style="width:8rem" value="'.$FromProtocolId.'">
                        </div>
                        <div class="col-sm-2 BoxRowCaption" style="font-size:1rem">
                            A <input name="ToProtocolId" type="text" style="width:8rem" value="'.$ToProtocolId.'">
                        </div>
                        '.$str_Union.'
                    </div>
                    <div class="col-sm-12 BoxRow">
                        <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                            Ricerca data:
                        </div>
                         <div class="col-sm-4 BoxRowCaption" style="font-size:1rem">
                            <input type="radio" name="DateType" value="0" '.$str_CheckSendDate.'>Invio
                            <input type="radio" name="DateType" value="1" '.$str_CheckNotificationDate.'>Notifica

                            <input type="radio" name="DateType" value="2" '.$str_CheckRegDate.'>Inser
                        </div>                       
                        
                        '.$str_FromToDate.'


                    </div>
                    <div class="col-sm-12 BoxRow">
                        <div class="col-sm-9 BoxRowLabel" style="font-size:1.2rem">
                            &nbsp;
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <i class="glyphicon glyphicon-filter" style="position:absolute;top:4px;right:10px;font-size:2rem"></i>
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <i class="fa fa-file-pdf-o" style="position:absolute;top:4px;right:2px;font-size:2rem"></i>
                        </div>
                    </div>
 
 
 
 
                    </form>
                    
                    ';


$str_out.= '    </div>

				
         	</div>
        </div>
    	<div class="row-fluid">
        	<div class="col-sm-12">
            	<div class="table_label_H col-sm-1">&nbsp;</div>
				<div class="table_label_H col-sm-2">Data Invio.</div>
				<div class="table_label_H col-sm-2">Data notif</div>				
				<div class="table_label_H col-sm-2">Cron</div>
				<div class="table_label_H col-sm-1">Notifica</div>
				<div class="table_label_H col-sm-1">CAN</div>
				<div class="table_label_H col-sm-1">CAD</div>
				<div class="table_label_H col-sm-1">Messo</div>
				<div class="table_label_H col-sm-1">Altro</div>
				<div class="clean_row HSpace4"></div>';






if($FromDate == "" && $ToDate=="") {
    $str_out.=
        '<div class="table_caption_H col-sm-12">
			Scegliere un periodo
		</div>';
    echo $str_out;
}else{
    $table_rows = $rs->Select('V_FineNotification',$str_Where, $strOrder);
    $RowNumber = mysqli_num_rows($table_rows);


    $f_TotCan = 0;
    $f_TotCad = 0;
    $f_TotNotification = 0;
    $f_TotNotifier = 0;
    $f_TotOther = 0;

    $n_Cont = 0;
    if ($RowNumber == 0) {
        $str_out.=
            '<div class="table_caption_H col-sm-12">
			Nessun pagamento presente
		</div>';
    } else {
        while ($table_row = mysqli_fetch_array($table_rows)) {

            $n_Cont++;

            $str_out.= '<div class="table_caption_H col-sm-1">' . $n_Cont .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-2">' . DateOutDB($table_row['SendDate']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-2">' . DateOutDB($table_row['NotificationDate']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-2">' . $table_row['ProtocolId'] .'/'.$table_row['ProtocolYear'].'</div>';


            $rs_Row = $rs->SelectQuery("
			SELECT  
			NotificationTypeId,
			NotificationFee,
			ResearchFee,
			CanFee,
			CadFee,
			NotifierFee,
			OtherFee,
			SendDate,
			DeliveryDate,
			ResultId
			FROM FineHistory
			
			WHERE FineId=".$table_row['FineId']." AND NotificationTypeId=6");


            $r_Row = mysqli_fetch_array($rs_Row);

            $NotificationFee = $r_Row['NotificationFee'];
            $ResearchFee =$r_Row['ResearchFee'];





            $f_TotNotification += $NotificationFee;
            $f_TotCan += $r_Row['CanFee'];
            $f_TotCad += $r_Row['CadFee'];
            $f_TotNotifier += $r_Row['NotifierFee'];
            $f_TotOther += $r_Row['OtherFee'];


            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($NotificationFee) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($r_Row['CanFee']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($r_Row['CadFee']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($r_Row['NotifierFee']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($r_Row['OtherFee']) .'</div>';




            $str_out.= '
			            <div class="clean_row HSpace4"></div>';
        }


    }



    $str_out.= '<div>
	</div>';
    $str_out.= '<div class="table_caption_H col-sm-7">TOTALI</div>';

    $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($f_TotNotification) .'</div>';
    $str_out.= '<div class="table_caption_H col-sm-1">' . NumberDisplay($f_TotCan) .'</div>';
    $str_out.= '<div class="table_caption_H col-sm-1">' .NumberDisplay($f_TotCad). '</div>';
    $str_out.= '<div class="table_caption_H col-sm-1">' .NumberDisplay($f_TotNotifier). '</div>';
    $str_out.= '<div class="table_caption_H col-sm-1">' .NumberDisplay($f_TotOther). '</div>';
    echo $str_out;
}






?>

<script type="text/javascript">

	$(document).ready(function () {

		$(".glyphicon-filter").click(function(){
            $('#f_Search').attr('action', 'prn_notification.php');
			$("#f_Search").submit();
		});

        $(".fa-file-pdf-o").click(function(){
            $('#f_Search').attr('action', 'prn_notification_exe.php');
            $("#f_Search").submit();
        });








	});
</script>
<?php
include(INC."/footer.php");
