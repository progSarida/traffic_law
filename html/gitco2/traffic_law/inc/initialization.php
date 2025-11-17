<?php
$str_out = "";
$str_Where = "1=1";

$aVehicleTypeId = array(
    "fas fa-question-circle",
    "fa fa-car",
    "fa fa-motorcycle",
    "fas fa-shuttle-van",
    "fa fa-truck",
    "fas fa-caravan",
    "fa fa-rocket",
    "fas fa-trailer",
    "fa fa-bus",
    "fa fa-bicycle", 
    "fas fa-tractor", 
    "fas fa-trailer",
    "fas fa-truck-moving"
);

$a_PaymentDocumentId = array("Ridotto","Normale","Maggiorato","");
$a_TimeTypeId = array("Ordinaria", "Solare", "Legale");
$a_BankMgmt = array("Si","No");
$a_PaymentProcedure = array("No","Si");



$a_FineTypeId = array(
    '1'=>'<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-file tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Preinserimento"></i>',
    '2'=>'<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-book tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Preavviso"></i>',
    '3'=>'<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-file-alt tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Verbale normale"></i>',
    '4'=>'<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-file-archive tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Verbale contratto"></i>',
    '5'=>'<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-file-alt tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Verbale d\'ufficio"></i>',
);




$str_FineTypeLabel ='
 		<div style="position:absolute; top:5px;font-size:1.2rem;color:#fff;width:280px;text-align: left">
 		    <div style="width:280px; position:relative; top:-5px;left:10px;">Tipo atto:</div>
 		    <div style="width:140px;float:left; position:relative; top:-5px;">
                <div>
                    <i class="fa fa-file" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Preinserimento
                </div>
                <div>
                    <i class="fa fa fa-book" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Preavviso
                </div>
            </div>
            <div style="width:140px;float:left; position:relative; top:-5px;">
                <div>
                    <i class="fa fa-file-alt" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale
                </div>
                <div>
                    <i class="fa fa-file-archive" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Verbale contratto
                </div>
            </div>
		</div>
		';













$str_CurrentPage = curPageName();


$a_Search_Status = array();
$a_Search_Status[23]= "";
$a_Search_Status[20]= "";
$a_Search_Status[25]= "";
$a_Search_Status[27]= "";
$a_Search_Status[28]= "";
$a_Search_Status[30]= "";
$a_Search_Status[35]= "";
$a_Search_Status[36]= "";

$a_Search_StatusExtended = array();
$a_Search_StatusExtended[23]= "";
$a_Search_StatusExtended[20]= "";
$a_Search_StatusExtended[25]= "";
$a_Search_StatusExtended[27]= "";
$a_Search_StatusExtended[28]= "";
$a_Search_StatusExtended[30]= "";
$a_Search_StatusExtended[35]= "";
$a_Search_StatusExtended[36]= "";
$a_Search_StatusExtended[2300]= "";
$a_Search_StatusExtended[2323]= "";
$a_Search_StatusExtended[2302]= "";

if(isset($_REQUEST['cityid'])) {
    $_SESSION['cityid'] = $_REQUEST['cityid'];
    $_SESSION['citytitle'] = $_REQUEST['citytitle'];
    
    $file_blazon = glob(ROOT.'/img/blazon/'.$_SESSION['cityid'].'/*.{png,jpg,jpeg}', GLOB_BRACE);
    
    $ablazon = explode("traffic_law/",$file_blazon[0]);
    $_SESSION['blazon'] = $ablazon[1];
}
if(isset($_REQUEST['year'])) {
    $_SESSION['year'] = $_REQUEST['year'];
}
if(isset($_REQUEST['ruletypeid'])) {
    $_SESSION['ruletypeid'] = $_REQUEST['ruletypeid'];
    $_SESSION['ruletypetitle'] = $_REQUEST['ruletypetitle'];
}

$returnChk = chkCityYear($_SESSION['cityid'], $_SESSION['year'], $_SESSION['ruletypeid']);

if($returnChk['city']===false){
    $_SESSION['Message']['Error'] = "Comune di {$_SESSION['citytitle']} non accessibile per l'utente {$_SESSION['username']}.";
    echo '<script>window.location.href="select_customer.php";</script>';
    DIE;
}
if($returnChk['year']===false){
    $_SESSION['Message']['Error'] = "Anno {$_SESSION['year']} del comune di {$_SESSION['citytitle']} non accessibile per l'utente {$_SESSION['username']}.";
    echo '<script>window.location.href="select_customer.php";</script>';
    DIE;
}
if($returnChk['ruletype']===false){
    $_SESSION['Message']['Error'] = "Regolamento {$_SESSION['ruletypetitle']} non gestito per il comune di {$_SESSION['citytitle']}.";
    echo '<script>window.location.href="select_customer.php";</script>';
    DIE;
}


$rs= new CLS_DB();
$rs->SetCharset('utf8');


$rs_Customer = $rs->Select("V_Customer", "CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL");
$r_Customer  = mysqli_fetch_array($rs_Customer);
if($r_Customer['CityUnion']==2){
    
    
    if($_SESSION['usertype']==0){
        $rs_Controller = $rs->Select("Controller", "Id=".$_SESSION['controllerid']);
        $r_Controller  = mysqli_fetch_array($rs_Controller);
        
        
        $str_Where .= " AND Locality = '".$r_Controller['Locality']."' ";
    }
}


require("page/filter.php");