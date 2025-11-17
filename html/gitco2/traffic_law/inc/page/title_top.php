<?php


$a_PageTitle = explode("/",$str_PageTitle);

$str_TitleOut =  (strlen(trim($a_PageTitle[0]))>0) ? '<span class="title_page" style="position:relative;float:right;top:0.5rem"><i class="fa fa-bars"></i> '.$a_PageTitle[0].' <i class="fa fa-caret-right"></i> '.$a_PageTitle[1].'</span>' : '';



$a_Page = unserialize(PAGE_GLOBAL);
$description = "THis page not has pagehelp";
$questionmark = null;
$actual_link = $_SERVER['PHP_SELF'];
$newlink = explode("/",$actual_link);
$str_Blazon = (!in_array(curPageName(),$a_Page)) ? '<img src="'.$_SESSION['blazon'].'" style="height:5rem;"><span class="title_city"> ('.$_SESSION['cityid'] .') '.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>' : '<img src="'.$_SESSION['blazon'].'" style="height:5rem;"><span class="title_city"> ('.$_SESSION['cityid'] .') '.$_SESSION['citytitle'];
$helpFileName=str_replace(".php",".pdf",$newlink[count($newlink)-1]);

if(file_exists(ROOT."/doc/help/".$helpFileName))
  $str_help='<a onClick="window.open('."'".HELP."/".$helpFileName."','help'".')" tooltip="Help" style=style="background-color: #fff;height:3rem;"><i class="fa fa-book"></i></a>';
else
  $str_help='';
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

$str_Having = "";

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


if($Search_Trespasser!="")      $str_Where .= " AND (CompanyName LIKE '%".addslashes($Search_Trespasser)."%' OR Surname LIKE '%".addslashes($Search_Trespasser)."%')";
if($Search_TrespasserName!="")  $str_Where .= " AND Name LIKE '%".addslashes($Search_TrespasserName)."%'";
if($Search_TrespasserFullName!="")  $str_Where .= " AND (TrespasserFullName LIKE '%".addslashes($Search_TrespasserFullName)."%')";

//Solo per elenco verbali
if($Search_TrespasserFullNameSearch!="")  $str_Having .= " AND (TrespasserFullName LIKE '%".addslashes($Search_TrespasserFullNameSearch)."%')";

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
if($Search_PaymentRate > 0 )    $str_Where .= " AND PaymentRateId>0";


if($Search_Province!="")  $str_Where .= " AND Province LIKE '%".addslashes($Search_Province)."%'";
if($Search_City!="")  $str_Where .= " AND CityId LIKE '%".addslashes($Search_City)."%'";
if($Search_ValidatedAddress!=0)  $str_Where .= " AND EXISTS(
  select * from FineNotification where FineNotification.FineId = FineId and ValidatedAddress=1
  )";




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



