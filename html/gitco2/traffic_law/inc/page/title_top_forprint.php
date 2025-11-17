<?php


$a_PageTitle = explode("/",$str_PageTitle);

$str_TitleOut =  (strlen(trim($a_PageTitle[0]))>0) ? '<span class="title_page"><i class="fa fa-bars"></i> '.$a_PageTitle[0].' <i class="fa fa-caret-right"></i> '.$a_PageTitle[1].'</span>' : '';



$a_Page = unserialize(PAGE_GLOBAL);
$description = "THis page not has pagehelp";
$questionmark = null;
$actual_link = $_SERVER['PHP_SELF'];
$newlink = explode("/",$actual_link);
$results = $rs->SelectQuery("SELECT * FROM sarida.HelpMenuPage WHERE LinkPage = '$newlink[2]'");
if (mysqli_num_rows($results) > 0){
    $questionmark = '<span class="glyphicon glyphicon-question-sign help"></span>';
    $row = mysqli_fetch_array($results);
    $linkpage = $row['LinkPage'];
    $description = $row['Description'];
}


/*
$str_Blazon = (!in_array(curPageName(),$a_Page)) ? '<img src="'.$_SESSION['blazon'].'" style="height:5rem;"><span class="title_city"> ('.$_SESSION['cityid'] .') '.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>' : '<img src="'.$_SESSION['blazon'].'" style="height:5rem;"><span class="title_city"> ('.$_SESSION['cityid'] .') '.$_SESSION['citytitle'];

$str_out .= '
    	<div class="row-fluid" style="height:6rem;background-color: #fff;">
        	<div class="col-sm-12" >
        		<div class="col-sm-6">
                    '. $str_Blazon .'
                    '.$questionmark.'
                </div>
				
        		<div class="col-sm-6">
					'. $str_TitleOut .'
				</div>
			</div>	
            <div class="col-sm-12" id="div_message_page">
            </div>
        </div>

        ';
*/

$str_out .= '
    	<div class="row-fluid">
                <div class="col-sm-1" style="background-color: #fff;height:3rem;">'.$str_help.'</div>
        	<div class="col-sm-11" style="background-color: #fff;height:3rem;">
                '.$str_TitleOut.'
            </div>
            <div class="col-sm-12" id="div_message_page">
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
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
if($Search_Ref != "")           $str_Where .= " AND Code LIKE '".addslashes($Search_Ref)."%'";
if($Search_Plate != "")         $str_Where .= " AND VehiclePlate LIKE '%".addslashes($Search_Plate)."%' ";
if($Search_Country!="")         $str_Where .= " AND CountryId = '".$Search_Country."' ";
if($Search_Zone != "")          $str_Where .= " AND (VehiclePlate LIKE '".$Search_Zone."%')";

if($Search_Plate!="")           $str_Where .= " AND (VehiclePlate LIKE '%".addslashes($Search_Plate)."%')";
if($Search_Trespasser!="")      $str_Where .= " AND (CompanyName LIKE '%".addslashes($Search_Trespasser)."%' OR Surname LIKE '%".addslashes($Search_Trespasser)."%')";
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

if($Search_TrespasserName!="")  $str_Where .= " AND Name LIKE '%".addslashes($Search_TrespasserName)."%'";
if($Search_Province!="")  $str_Where .= " AND Province LIKE '%".addslashes($Search_Province)."%'";
if($Search_City!="")  $str_Where .= " AND CityId LIKE '%".addslashes($Search_City)."%'";
if($Search_ValidatedAddress!=0)  $str_Where .= " AND EXISTS(
  select * from FineNotification where FineNotification.FineId = V_mgmt_Fine.FineId and ValidatedAddress=1
  )";







$rs_Customer = $rs->Select("Customer", "CityId='".$_SESSION['cityid']."'");
$r_Customer  = mysqli_fetch_array($rs_Customer);
if($r_Customer['CityUnion']==2){


    if($_SESSION['usertype']==0){
        $rs_Controller = $rs->Select("Controller", "Id=".$_SESSION['controllerid']);
        $r_Controller  = mysqli_fetch_array($rs_Controller);


        $str_Where .= " AND Locality = '".$r_Controller['Locality']."' ";
    }
}
?>
<div id="HelpModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Help Page</h4>
            </div>
            <div class="modal-body">

                <textarea id="help_page"><?=$description?></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    var help = CKEDITOR.replace('help_page',{
        toolbar: [
            { name: 'document', items: [ 'Print'] }
        ],
        height: 500,

    });
    help.config.readOnly = true;
    help.config.allowedContent = true;

    // CKEDITOR.config.allowedContent = true;


</script>



