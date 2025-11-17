<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$Id = CheckValue('Id','n');
$ReminderPage = CheckValue('ReminderPage','s');

if ($ReminderPage != ""){
    $str_back =
    '$("#back").click(function(){
       window.history.back();
    });';
} else {
    $str_back =
    '$("#back").click(function(){
       window.location="'.$str_BackPage.$str_GET_Parameter.'&Filter=1";
    });';
}

$a_Procedure =array("No","Si");

//<video id="sampleMovie" src="HTML5Sample.mov" controls></video>

//Lo Status è > 7 così rientrano anche gli avvisi bonari
$str_Where .= " AND StatusTypeId>7 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];


if($_SESSION['userlevel']<3){
    $str_WherePage = $str_Where." AND Id=".$Id;
} else {
    $str_WherePage = "Id=".$Id;
}

$strOrder = "Id DESC";

$rs_Fine = $rs->Select("V_Fine",$str_WherePage);

$FindNumber = mysqli_num_rows($rs_Fine);
if($FindNumber==0){
    $rs_Fine = $rs->Select('V_FineRent',$str_WherePage);
    $r_Fine = mysqli_fetch_array($rs_Fine);
    
} else {
    $r_Fine = mysqli_fetch_array($rs_Fine);
}

$str_FolderViolation = (($r_Fine['CountryId']=='Z000') ? 'doc/national/violation/' : 'doc/foreign/violation/').$_SESSION['cityid'].'/'.$Id.'/';
$str_FolderFine = (($r_Fine['CountryId']=='Z000') ? 'doc/national/fine/' : 'doc/foreign/fine/').$_SESSION['cityid'].'/'.$Id.'/';

$ProtocolId = $r_Fine['ProtocolId'];

// $n_NextProtocolId = $ProtocolId+1;
// $n_PreviousProtocolId = $ProtocolId-1;

$str_Next = "";
$str_Previous = "";
$str_Folder = "";

$rs_Id = $rs->SelectQuery("SELECT FineId AS NextId
           FROM V_mgmt_Fine
          WHERE ProtocolId > $ProtocolId AND StatusTypeId>10 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " ORDER BY ProtocolId LIMIT 1");
$r_Id = mysqli_fetch_array($rs_Id);

if (!is_null($r_Id['NextId'])) {
    $str_Next = $ReminderPage != "" ? "" : '<a href="'.$str_CurrentPage.'&Id='.$r_Id['NextId'].'&P='.$str_BackPage.'"><i class="glyphicon glyphicon-arrow-right" style="font-size:3.6rem;color:#fff"></i></a>';
}

$rs_Id = $rs->SelectQuery("SELECT FineId AS PreviousId
           FROM V_mgmt_Fine
          WHERE ProtocolId < $ProtocolId AND StatusTypeId>10 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " ORDER BY ProtocolId DESC LIMIT 1");
$r_Id = mysqli_fetch_array($rs_Id);

if(!is_null($r_Id['PreviousId'])) {
    $str_Previous = $ReminderPage != "" ? "" : '<a href="' . $str_CurrentPage. '&Id=' . $r_Id['PreviousId'] .'&P='.$str_BackPage. '"><i class="glyphicon glyphicon-arrow-left" style="font-size:3.6rem;color:#fff"></i></a>';
}

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         Injunction
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
//$str_CSSInjunction = ' style="color:#C43A3A; cursor:not-allowed;" ';

?>
<input type="hidden" name="FineDate" id="FineDate" value="<?=$r_Fine['FineDate']?>">
<input type="hidden" name="LicenseDate" id="LicenseDate" value="<?=$license?>">
    <input type="hidden" name="Punti" id="Punti" value="<?=$get_punti?>">
<?php

?>
<script>
    $(document).ready(function () {
        var Fine_Date_Get = $('#FineDate').val();
        var new_fine = Fine_Date_Get.split("/");
        var newfinedt = new_fine[2]+"/"+new_fine[1]+"/"+new_fine[0];

        var LicenseDate = $('#LicenseDate').val();
        var new_license = LicenseDate.split("/");
        var license = new_license[2]+"/"+new_license[1]+"/"+new_license[0];
        var years = new Date(new Date(newfinedt)- new Date(license)).getFullYear() - 1970;
//        var oldpiket = '<\?=$oldpunti?>';
//         $("#piket").html(oldpiket);
    })
</script>
<?php
require(INC."/fine_section/trespasser_data.php");
require(INC."/fine_section/fine_data.php");
require(INC."/fine_section/documentation_data.php");
require(INC."/fine_section/document_data.php");
require(INC."/fine_section/notification_data.php");
require(INC."/fine_section/payment_data.php");
require(INC."/fine_section/reminder_data.php");
require(INC."/fine_section/dispute_data.php");
require(INC."/fine_section/refund_data.php");
require(INC."/fine_section/126bis_data.php");
require(INC."/fine_section/procedure_data_view.php");
require(INC."/fine_section/rate_data_view.php");
require(INC."/fine_section/injunction_data.php");

$str_out .= '
<div class="row-fluid">
    <div class="col-sm-12">
        <div class="col-sm-12" >
            <div class="col-sm-1 BoxRowCaption" style="height:3.8rem">
                ' . $str_Previous . '
            </div>
            <div class="col-sm-10">
                <ul class="nav nav-tabs fine-tabs" id="mioTab" style="background-color: #dfe7e7;font-size:1rem;height:4rem;">
                    <li class="active" id="FineSection"><a href="#Fine" data-toggle="tab">VERBALE</a></li>
                    <li id="TrespasserSection"><a href="#Trespasser" data-toggle="tab">TRASGR/OBBLIGATO</a></li>
                    <li id="NotificationSection"><a href="#Notification"'. $str_CSSNotification .'>NOTIFICA</a></li>
                    <li id="paymentSection"><a href="#Payment"'. $str_CSSPayment .'>PAGAMENTO</a></li>
                    <li id="DocumentSection"><a href="#Document"'. $str_CSSDocument .'>DOCUMENTAZIONE</a></li>
                    <li id="ReminderSection"><a href="#Reminder"'. $str_CSSReminder .'>SOLLECITO</a></li>                 
                    <li id="DisputeSection"><a href="#Dispute"'. $str_CSSDispute.'>RICORSO</a></li>                   
                    <li id="RefundSection"><a href="#Refund"'. $str_CSSRefund .'>RIMBORSO</a></li>
                    <li id="RateSection"><a href="#Rate"'. $str_CSSRate .'>RATEIZZAZIONE</a></li>
                    <li id="InjunctionSection"><a href="#Injunction"'. $str_CSSInjunction .'>COATTIVA</a></li>
                    '.($b_126bis ? '<li><a href="#126Bis"'. $str_CSS126Bis .'>COM. 126 BIS</a></li>' : '').'   
                    <li id="ProcedureSection"><a href="#Procedure"'. $str_CSSProcedure .'>ULTERIORI DATI</a></li>
                </ul>
            </div>				
            <div class="col-sm-1 BoxRowCaption" style="height:3.8rem; text-align:right;">
                ' . $str_Next . '
            </div>					
        </div> 

    </div> 
    <div class="col-sm-6">
        <div class="col-sm-12" >   	
            <div class="tab-content">
                '. $str_Fine_Data .'
                '. $str_Trespasser_Data .'
                '. $str_Notification_Data .'
                '. $str_Payment_Data .'
                '. $str_Document_Data .'         
                '. $str_Reminder_data .'
                '. $str_Dispute_data .'
                '. $str_Refund_data .'
                '. $str_Rate_data .'
                '.($b_126bis ? $str_126Bis_data : '').'
                '. $str_Procedure_data .'
                '. $str_Injunction_data .'
            </div>
         </div>
    </div>     
    <div class="col-sm-6">
        '. $str_Documentation_data .'
    </div>        
    
        
    
    
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                <button class="btn btn-default" id="back" style="margin-top:1rem;">Indietro</button>
            </div>    
        </div>
    </div>  
</div>';



echo $str_out;
?>
<script type="text/javascript">

	function loadFileTree(path){
        $("#fileTreeDemo_1").fileTree({ root:path, script: 'jqueryFileTree.php' }, function(file) {
            var FileType = file.split('.').pop();
                
            if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc' || FileType.toLowerCase()=='html'){
                $("#preview_img").hide();
                $("#preview_video").hide();
                
                $("#preview_doc").html('<iframe style="width:100%; height:100%; background:white;" src="'+file+'"></iframe>');
                $("#preview_doc").show();
                
            }else if(FileType.toLowerCase()=='mp4'){
                $("#preview_img").hide();
                $("#preview_doc").hide();
                
                $("#preview_video").attr("src",file);
                $("#preview_video").show();
                
            }else{
                $("#preview_doc").hide();
                $("#preview_video").hide();
                
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }
        });
	}

    $('document').ready(function(){
        //$('#preview').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        <?= $str_tree ?>

        <?= $str_Img ?>



        <?= $str_PDF ?>
        <?= $str_tree_reminder ?>



        <?= $str_back ?>

        var ViolationFolder = '<?= $str_FolderViolation; ?>';
        var FineFolder = '<?= $str_FolderFine; ?>';

		$('#mioTab a').click(function () {
			switch($(this).attr('href')) {
                case "#Document":
					$("#FileTreeBox").hide();
    				$("#preview_doc").hide();
					$("#accordion .glyphicon-eye-open").first().click();
                	break;
                case "#Rate":
					$("#FileTreeBox").hide();
    				$("#preview_doc").hide();
					$(".rate-group [data-file]:not(.disabled)").first().click();
                	break;
            	case "#126Bis":
            		loadFileTree(FineFolder);
					$("#FileTreeBox").show();
    				$("#preview_doc").hide();
    				$(".jqueryFileTree a").first().click();
    				break;
                default:
                	loadFileTree(ViolationFolder);
					$("#FileTreeBox").show();
					$("#preview_doc").hide();
					$(".jqueryFileTree a").first().click();
            } 
		});
    //Per visualizzare o meno la cifra e la data della prefettura in caso di articoli che prevedano cifre assegnate manualmente dalla Prefettura
    if(<?=json_encode($PrefectureFixed)?> == true)
    	$('.prefecture').css('display','');
    else
    	$('.prefecture').css('display','none');
    });
    //**************
    $('#paymentSection').click(()=>{$('#FileTreeBox').css("display","none")});	//Per nascondere la barra intermedia della schermata della documentazione
    $('#paymentSection').click(()=>{$('#preview').css("display","none")});
    //Gestisce il cambio di colore delle righe dei pagamenti alla selezione
    var prevRowId;

    function changeRowColor(id)
     {
     //Il cambiamento di colore avviene solo se la riga precedentemente evidenziata è diversa da quella selezionata ora
     if(prevRowId!=id)
       {
       //Rosso/Selezione
       $('#FileName_' + id + ' .BoxRowCaption').css("background-color", "#eaacc1");
       $('#FileName_' + id + ' .BoxRowLabel').css("background-color", "#96283c");
       $('#FileName_' + id + ' .BoxRowHTitle').css("background-color", "#6d1830");
       //Blu/Deselezione
       $('#FileName_' + prevRowId + ' .BoxRowCaption').css("background-color", "");
       $('#FileName_' + prevRowId + ' .BoxRowLabel').css("background-color", "");
       $('#FileName_' + prevRowId + ' .BoxRowHTitle').css("background-color", "#294A9C");
       prevRowId = id;
       }
      }
      
     //Impostazioni per il clic di tutti i tab tranne Documentazione e Rateizzazione
     $('#FineSection, #TrespasserSection, #NotificationSection, #paymentSection, #ReminderSection, #DisputeSection, #RefundSection, #InjunctionSection, #ProcedureSection').on('click',()=>{
     	$('#preview_iframe_img').hide();
       	$('#preview').hide();
       	});
     //Impostazioni per il clic di tutti i tab tranne Rateizzazione
     $('#FineSection, #TrespasserSection, #NotificationSection, #PaymentSection, #DocumentSection, #ReminderSection, #DisputeSection, #RefundSection, #InjunctionSection, #ProcedureSection').on('click',()=>{
       	$('#preview_iframe_img').hide();
       	$('#preview_img').css('height','60rem');
       	$('#preview_section').css('height','55.2rem');
       	});

</script>
<?php
include(INC."/footer.php");