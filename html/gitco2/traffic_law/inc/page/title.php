<?php

$str_SubMenu ='
    <div class="FN_SubMenu">
        <ul class="nav navbar-nav navbar-right" id="SubMenu_r">

';


if($_SESSION['userlevel']>=3){
    $rs_Mail = $rs->Select('Mail', "ReadStatus=0 AND UserId=".$_SESSION['userid']);
    $str_Mail = (mysqli_num_rows($rs_Mail)>0) ? '<div class="span_envelope">'.mysqli_num_rows($rs_Mail).'</div>' : "";

    $str_SubMenu .= '
            <li style="width:5rem;"><a href="admin_finequery.php?PageTitle=Ricerca/Avanzata"><span class="fa fa-search"></span>&nbsp;</a></li>
            <li style="width:5rem;"><a href="mgmt_mail.php?PageTitle=Gestione/Posta"><span class="fa fa-envelope-o"></span>&nbsp;
            '.$str_Mail.'   
               </a></li>
            ';
}
$str_SubMenu .= '
<li class="dropdown">

    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
        <span class="fa fa-university"></span>&nbsp;'.substr($_SESSION['citytitle'],0,8) .'
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu FN_SubMenu" >
';

$cities = $rs->SelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle;");
while($city = mysqli_fetch_array($cities)){
    if($city['CityId']!=$_SESSION['cityid']) $str_SubMenu .= '<li id="SubMenu-menu_r"><a href="index.php?cityid='.$city['CityId'].'&citytitle='.$city['CityTitle'].'">'.substr($city['CityId']." ".$city['CityTitle'],0,15).'</a></li>';
}

$str_SubMenu .= '
    </ul>
</li>
<li class="dropdown">

    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
        <span class="glyphicon glyphicon-calendar"></span>&nbsp;'. $_SESSION['year'] .'<span class="caret"></span>
    </a>
    <ul class="dropdown-menu FN_SubMenu" >
    ';


$years = $rs->SelectQuery("SELECT CityYear FROM ".MAIN_DB.".V_UserCity WHERE CityId='".$_SESSION['cityid']."' AND UserId=".$_SESSION['userid']." ORDER BY CityYear DESC;");


while($year = mysqli_fetch_array($years)){
    if($year['CityYear']!=$_SESSION['year']) $str_SubMenu .= '<li id="SubMenu-menu_r"><a href="index.php?year='.$year['CityYear'].'">'.$year['CityYear'].'</a></li>';
}


$str_SubMenu .= '
    </ul>
</li>
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
        <span class="glyphicon glyphicon-user tooltip-r" data-toggle="tooltip" data-placement="left" '. $str_UserClass .'></span> &nbsp;Account<span class="caret"></span>
    </a>
    <ul class="dropdown-menu FN_SubMenu" >
        <li id="SubMenu-menu_r"><a href="#"><span class="fa fa-cogs"></span> Account</a></li>
        &nbsp;
        <li id="SubMenu-menu_r"><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
    </ul>
</li>






</ul>
</div>
';

$a_PageTitle = explode("/",$str_PageTitle);

$str_TitleOut =  (strlen(trim($a_PageTitle[0]))>0) ? '<span class="title_page"><i class="fa fa-bars"></i> '.$a_PageTitle[0].' <i class="fa fa-caret-right"></i> '.$a_PageTitle[1].'</span>' : '';



$a_Page = unserialize(PAGE_GLOBAL);



$str_Customer = (!in_array(curPageName(),$a_Page)) ? '<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>' : '<span class="title_city">'.$_SESSION['citytitle'];

$str_out .= '
    	<div class="row-fluid" style="height:6rem;background-color: #fff;">
        	<div class="col-sm-12" >
        		<div class="col-sm-7">
                    '. $str_Customer .'
				</div>
        		<div class="col-sm-5" style="height:6rem;background-color: #fff;">
					'. $str_TitleOut .' '.$str_SubMenu .' 
					
				</div>
			</div>	
        </div>

        ';

if($_SESSION['PasswordDay']<=0){
    $str_out .= '
    	<div class="row-fluid" style="height:6rem;background-color: #fff;">
        	<div class="col-sm-12 alert-danger" style="text-align:center" >
        		
					PASSWORD SCADUTA. AGGIORNARLA.
				
			</div>	
        </div>
        
        
        ';
    include(INC.'/page/mgmt_user_upd.php');
    DIE;
}else if(LOCK_SITE){
    $str_out .= '
    	<div class="row-fluid" style="height:6rem;background-color: #fff;">
        	<div class="col-sm-12 alert-warning" style="text-align:center" >
        		
					SITO IN AGGIORNAMENTO. RIPROVARE PIU\' TARDI
				
			</div>	
        </div>
        
        ';


    if ( $_SESSION['userlevel']<5 ){
        echo $str_out;
        DIE;
    }
}



$s_SelPlateN = "";
$s_SelPlateF = "";

if($s_TypePlate!="") {
    if ($s_TypePlate == "N") {
        $str_Where .= " AND CountryId='Z000'";
        $s_SelPlateN = " SELECTED ";
    }
    if ($s_TypePlate == "F"){
        $str_Where .= " AND CountryId!='Z000'";
        $s_SelPlateF = " SELECTED ";
    }
}

if($Search_Status>0){
    $a_Search_Status[$Search_Status] = " SELECTED ";
    $str_Where .= " AND StatusTypeId=".$Search_Status;

}





if($Search_Violation>0)         $str_Where .= " AND ViolationTypeId=".$Search_Violation;
if($Search_Customer!="")        $str_Where .= " AND CityId='".addslashes($Search_Customer)."'";
if($Search_Ref != "")           $str_Where .= " AND (ProtocolId LIKE '".addslashes($Search_Ref)."%' OR Code LIKE '".addslashes($Search_Ref)."%' OR ExternalProtocol LIKE '".addslashes($Search_Ref)."%')";
if($Search_Plate != "")         $str_Where .= " AND VehiclePlate LIKE '%".addslashes($Search_Plate)."%' ";
if($Search_Country!="")         $str_Where .= " AND CountryId = '".$Search_Country."' ";
if($Search_Zone != "") $str_Where .= " AND (VehiclePlate LIKE '".$Search_Zone."%')";

if($Search_Plate!="")           $str_Where .= " AND (VehiclePlate LIKE '%".addslashes($Search_Plate)."%')";
if($Search_Trespasser!="")      $str_Where .= " AND (CompanyName LIKE '%".addslashes($Search_Trespasser)."%' OR Surname LIKE '%".addslashes($Search_Trespasser)."%')";
if(@$Search_TrespasserName!="")      $str_Where .= " AND (Name LIKE '%".addslashes($Search_TrespasserName)."%')";

if($Search_TaxCode!="")         $str_Where .= " AND TaxCode LIKE '%".addslashes($Search_TaxCode)."%'";
if($Search_Flow!="")            $str_Where .= " AND Number=".$Search_Flow;
if($Search_PaymentName!="")     $str_Where .= " AND (PaymentName LIKE '%".addslashes($Search_PaymentName)."%')";
if($Search_Locality != "")      $str_Where .= " AND Locality='".$Search_Locality."'";
if($Search_ProtocolId > 0)      $str_Where .= " AND ProtocolId  = '".$Search_ProtocolId."'";
if($Search_FromFineDate != "")  $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate != "")    $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
if($Search_FromProtocolId>0)    $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId>0)      $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
if($Search_RuleTypeId>0)        $str_Where .= " AND RuleTypeId=".$Search_RuleTypeId;
if($Search_FromPaymentDate != "")  $str_Where .= " AND PaymentDate>='".DateInDB($Search_FromPaymentDate)."'";
if($Search_ToPaymentDate != "")    $str_Where .= " AND PaymentDate<='".DateInDB($Search_ToPaymentDate)."'";

if(@$Search_FromNotificationDate != "")  $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."'";
if(@$Search_ToNotificationDate != "")    $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."'";

if(@$Search_Address != "")    $str_Where .= " AND Address='".$Search_Address."'";








$rs_Customer = $rs->Select("Customer", "CityId='".$_SESSION['cityid']."'");
$r_Customer  = mysqli_fetch_array($rs_Customer);
if($r_Customer['CityUnion']==2){


    if($_SESSION['usertype']==0){
        $rs_Controller = $rs->Select("Controller", "Id=".$_SESSION['controllerid']);
        $r_Controller  = mysqli_fetch_array($rs_Controller);


        $str_Where .= " AND Locality = '".$r_Controller['Locality']."' ";
    }
}





