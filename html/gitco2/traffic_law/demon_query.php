<?php
require_once('cost-sarida-gitco.php');

date_default_timezone_set("Europe/Rome");


define("ROOT", __DIR__);


define("INC", ROOT."/inc");
define("CLS", ROOT."/cls");


define("FOREIGN_VIOLATION", ROOT."/doc/foreign/violation");
define("FOREIGN_FINE", ROOT."/doc/foreign/fine");
define("FOREIGN_REQUEST", ROOT."/doc/foreign/request");
define("FOREIGN_FLOW", ROOT."/doc/foreign/flow");

define("NATIONAL_VIOLATION", ROOT."/doc/national/violation");
define("NATIONAL_FINE", ROOT."/doc/national/fine");
define("NATIONAL_REQUEST", ROOT."/doc/national/request");
define("NATIONAL_FLOW", ROOT."/doc/national/flow");


require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");


$v_c = $_REQUEST['v_c'];
$v_n = $_REQUEST['v_n'];
$v_y = $_REQUEST['v_y'];
$v_d = $_REQUEST['v_d'];
$v_t = $_REQUEST['v_t'];
$v_p = $_REQUEST['v_p'];






$rs= new CLS_DB();

$RequestTime = date("H:i");

$a_FineRequest = array(
	array('field'=>'ProtocolId','selector'=>'value','type'=>'str','value'=>$v_n),
	array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$v_p),
	array('field'=>'RequestDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d"),'settype'=>'date'),
	array('field'=>'RequestTime','selector'=>'value','type'=>'time','value'=>$RequestTime,'settype'=>'time'),
);

$rs->Insert('FineRequest',$a_FineRequest);



$table_rows = $rs->Select('V_Customer',"CreationType=1 AND CityId='".$v_c."'");
$table_row = mysqli_fetch_array($table_rows);
$ManagerName = $table_row['ManagerName'];
$CityUnion = $table_row['CityUnion'];


$rs_rows = $rs->SelectQuery("SELECT * FROM V_FineArticle WHERE (TrespasserTypeId=1 OR TrespasserTypeId=11) AND CityId='".$v_c."' AND ProtocolId=".$v_n." AND ProtocolYear=".$v_y." AND FineDate='".$v_d."' AND DATE_FORMAT(FineTime,'%H:%i')='".$v_t."' AND (REPLACE(VehiclePlate,'  ','') = '".$v_p."' OR REPLACE(VehiclePlate,' ','') = '".$v_p."')");


if(mysqli_num_rows($rs_rows)>0) {

	$v = mysqli_fetch_array($rs_rows);


    
	
	$str_Location = "";
    if($CityUnion>1){
        $rs_localities = $rs->Select(MAIN_DB.".City","Id='".$v['Locality']."'");
        $rs_locality = mysqli_fetch_array($rs_localities);
        $str_Location = $rs_locality['Title']." - ";
    }



	$txt = "<result>1<result>";
	$txt .= "<id>".$v['ProtocolId']."/".$v['ProtocolYear']."<id>";

	$adate = explode("-",$v['FineDate']);
	$txt .= "<date>".$adate[2]."/".$adate[1]."/".$adate[0]." (dd/mm/aaaa)<date>";

	$atime = explode(":",$v['FineTime']);
	$txt .= "<time>".$atime[0].":".$atime[1]." (hh/mm)<time>";

	$txt .= "<plate>".$v['VehiclePlate']."<plate>";

	$txt .= "<city>".$ManagerName."<city>";
	$txt .= "<location>".utf8_encode($str_Location.$v['Address'])."<location>";





	if($v['TimeTLightFirst']>0){
        $txt .= "<tlight1>".$v['TimeTLightFirst']."<tlight1>";
        $txt .= "<tlight2>".$v['TimeTLightSecond']."<tlight2>";

    } else {
        $txt .= "<speedlimit>".intval($v['SpeedLimit'])."<speedlimit>";
        $txt .= "<speed>".intval($v['Speed'])."<speed>";

    }




    $rs_ArticleTariff = $rs->Select("ArticleTariff","ArticleId=".$v['ArticleId']." AND Year=".$v['ProtocolYear']);
    $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
    $ReducedPayment = $r_ArticleTariff['ReducedPayment'];
    $Fee = $v['Fee'];
    $MaxFee = $v['MaxFee'];
	$Communication = $v['126Bis'];


	$txt .= "<communication>". $Communication ."<communication>";

    $txt .= "<country>".$v['CountryId']."<country>";

    $rs_docs = $rs->Select("FineDocumentation","FineId=".$v['Id']." AND DocumentationTypeId=1");
    if(mysqli_num_rows($rs_docs)>0){
        $str_Delimiter = "";
        $str_Document = "";
        $n_cont=0;
        while($rs_doc = mysqli_fetch_array($rs_docs)){
            $n_cont++;
            $fileExt = pathinfo($rs_doc['Documentation'], PATHINFO_EXTENSION);
            if($fileExt=="jpg" || $fileExt=="pdf" || $fileExt=="mp4"){
                if($n_cont>1) $str_Document .= "**";
                if($v['CountryId']=='Z000'){
                    $str_Document .= "national/violation/".$v['CityId']."/".$v['Id']."/".$rs_doc['Documentation'];
                }else{
                    $str_Document .= "foreign/violation/".$v['CityId']."/".$v['Id']."/".$rs_doc['Documentation'];
                }
            }

        }
        $txt .= "<document>".$str_Document."<document>";

    }







	$rs_dates = $rs->Select("FineHistory","FineId=".$v['Id']." AND NotificationTypeId=6");
	$rs_date = mysqli_fetch_array($rs_dates);

    $txt .= (strlen($rs_date['SendDate'])==8) ? "<senddate>".DateOutDB($rs_date['SendDate'])."<senddate>" : "<senddate><senddate>";


	$NotificationFee = $rs_date['NotificationFee']+$rs_date['ResearchFee']+$rs_date['OtherFee'];






    $MaxFee = $v['MaxFee'];


    if($ReducedPayment){
        $amount1 = $Fee*FINE_PARTIAL+$NotificationFee;
        $amount1 = $amount1*1.034+0.35;
    }else{
        $amount1 = "------";
    }


	$amount2 = $Fee+$NotificationFee;
	$amount2 = $amount2*1.034+0.35;


    $amount3 = $MaxFee*FINE_MAX+$NotificationFee;
    $amount3 = $amount3*1.034+0.35;




    $FineId = $v['Id'];
    $rs_FineReminder = $rs->Select("FineReminder", "CityId='".$v_c."' AND FineId=".$FineId);


    if(mysqli_num_rows($rs_FineReminder)>0) {

        $r_FineReminder = mysqli_fetch_array($rs_FineReminder);

        $rs_Payment = $rs->SelectQuery("SELECT SUM(Amount) Amount FROM FinePayment WHERE FineId=" . $FineId);
        $r_Payment = mysqli_fetch_array($rs_Payment);
        $Amount = $r_Payment['Amount'];


        $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId) ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=" . $FineId);
        $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);

        $TotalNotification = $r_ReminderHistory['NotificationFee'];


        $ZoneId = $trespasser['ZoneId'];

        $TotalAmount = 0;
        $PercentualAmount = 0;




        $NotificationFee += $r_FineReminder['NotficationFee'];



        $MaxFee = ($MaxFee * FINE_MAX) - $Fee;
        $TotalNotification += $rs_date['NotificationFee'] + $rs_date['ResearchFee'] + $rs_date['OtherFee'];


        $TotalAmount = $Fee + $MaxFee + $TotalNotification;


        $Percentual = 0;

        if ($Percentual > 0) {
            //$r_Reminder['DeliveryDate'] = "2018-01-01";
            $d_DateLimit = date('Y-m-d', strtotime($rs_date['DeliveryDate'] . ' + ' . FINE_DAY_LIMIT . ' days'));

            $n_Month = floor(DateDiff("M", $d_DateLimit, $CurrentDate) / 6);

            for ($i = 1; $i <= $n_Month; $i++) {
                $PercentualAmount += $TotalAmount * $Percentual / 100;
            }

            $TotalAmount += $PercentualAmount;
        }

        $TotalAmount += $NotificationFee;

        $amount1 = $TotalAmount*1.034+0.35;

        $amount2 = "0";

        $amount3 = "0";




    }











	$txt .= "<amount1>".number_format($amount1,2)."<amount1>";
	$txt .= "<amount2>".number_format($amount2,2)."<amount2>";
	$txt .= "<amount3>".number_format($amount3,2)."<amount3>";

	$txt .= "<article>".$v['Article']."<article>";
	$txt .= "<paragraph>".$v['Paragraph']."<paragraph>";

}else{
	$txt = "<result>0<result>";


}

?>

<!DOCTYPE html>
<html>
<head>
</head>
<body>

	<?= $txt ?>

</body>
</html>
