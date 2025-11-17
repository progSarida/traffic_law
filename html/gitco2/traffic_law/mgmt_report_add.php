<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$_form_type_id = null;

$isPageUpdate = false;
$isLatestFine = false;

//$a_FineTypeId = array("","","","","","");
//$rs_Fine= $rs->Select("Fine","CityId='".$_SESSION['cityid']."'", "RegDate DESC");
//if(mysqli_num_rows($rs_Fine)>0){
//    $r_Fine = mysqli_fetch_array($rs_Fine);
//    $a_FineTypeId[$r_Fine['FineTypeId']] = " SELECTED ";
//}

$RegDate = Date('Y-m-d');

$str_Where = "RegDate='".$RegDate."' and UserId ='".$_SESSION['username']."' AND CityId ='".$_SESSION['cityid']."' AND ProtocolYear = ".$_SESSION['year']." AND RuleTypeId = ".$_SESSION['ruletypeid'];

//cambio vista da V_Fine a V_FineAllo perché la prima in caso di noleggio come ultimo inserito non rende i dati
$rs_PreviousFine = $rs->Select("V_FineAll",   $str_Where, "Id DESC LIMIT 1");
$r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);
$a_PreviousControllers = array();
$n_TotalControllers = 0;

if (mysqli_num_rows($rs_PreviousFine) > 0){
    $isLatestFine = true;
    
    $rs_PreviousController = $rs->SelectQuery("SELECT ControllerId FROM Fine WHERE Id=".$r_PreviousFine['Id']." UNION SELECT ControllerId FROM FineAdditionalController WHERE FineId=".$r_PreviousFine['Id']);
    $n_TotalControllers = mysqli_num_rows($rs_PreviousController);
    
    while($r_PreviousController = mysqli_fetch_array($rs_PreviousController)){
        $a_PreviousControllers[] = $r_PreviousController;
    }
    
    $tipoAttoWhere = $r_PreviousFine['FineTypeId']!= null ? " AND TipoAtto=".$r_PreviousFine['FineTypeId'] : "";
    $controllerIdWhere = $r_PreviousFine['ControllerId']!= null ?
    " AND (ControllerId=".$r_PreviousFine['ControllerId']. " OR ControllerId=0)"
        : " AND ControllerId=0" ;
        $rs_Receipt = $rs->Select("Receipt", "CityId='".$_SESSION['cityid']."' AND Session_Year= ".$_SESSION['year'].$tipoAttoWhere.$controllerIdWhere);
        $r_Receipt = mysqli_fetch_array($rs_Receipt);
        $n_Receipt = mysqli_num_rows($rs_Receipt);
}


$controller_id = $r_PreviousFine['ControllerId'];
$cont_name = $r_PreviousFine['ControllerName'];
$fineId = $r_PreviousFine['Id'];

$cont_code = $r_PreviousFine['ControllerCode'];
$controller_name = $cont_code .' '.$cont_name;


$PreviousFineDate = ($r_PreviousFine['FineDate']!="") ? DateOutDB($r_PreviousFine['FineDate']) : "";
$PreviousAddress = utf8_encode($r_PreviousFine['Address']);

/*
 $PreviousArticle    = $r_PreviousFine['Article'];
 $PreviousLetter     = $r_PreviousFine['Letter'];
 $PreviousParagraph  = $r_PreviousFine['Paragraph'];
 $PreviousArticleDescription = $r_PreviousFine['ArticleDescriptionIta'];
 */
$PreviousArticle    = "";
$PreviousLetter     = "";
$PreviousParagraph  = "";
$PreviousArticleDescription = "";






/*

FineDate
ControllerId
ControllerDate
ControllerTime
Locality
StreetTypeId
Address
VehicleTypeId


FineTypeId

ArticleId
ViolationTypeId
ReasonId
Fee
MaxFee
Article
Paragraph
Letter

ControllerName
ControllerCode
TimeTypeTitle
TrespasserId
TrespasserTypeId
*/







$PreviouVehicleTypeId = $r_PreviousFine['VehicleTypeId'];
if($PreviouVehicleTypeId=="") $PreviouVehicleTypeId=1;


echo "<div class='check'></div>";

require(INC . '/report_section/verbalization_data.php') ;
//NUOVO BOLLETTARIO
require(INC . '/report_section/locality_data.php');

if (isset($_GET['back']) && $_GET['back']=='true'){
    if (isset($_REQUEST['insertionType'])){
        if ($_REQUEST['insertionType'] == 1)
            $str_BackPage = 'mgmt_report.php'.$str_GET_Parameter;
            else if ($_REQUEST['insertionType'] == 2)
                $str_BackPage = 'mgmt_warning.php'.$str_GET_Parameter;
                else if ($_REQUEST['insertionType'] == 3)
                    $str_BackPage = 'mgmt_violation.php'.$str_GET_Parameter;
                    else if ($_REQUEST['insertionType'] == 4)
                        $str_BackPage = 'mgmt_violationrent.php'.$str_GET_Parameter;
    } else $str_BackPage = 'mgmt_report.php'.$str_GET_Parameter;
}

$str_out .= '
        <form name="f_violation" id="f_violation" method="post" action="mgmt_report_add_exe.php' . $str_GET_Parameter . '" xmlns="http://www.w3.org/1999/html">
        <input type="hidden" name="ArticleNumber" id="ArticleNumber" value="1">
        <input type="hidden" name="TotalPoints" id="TotalPoints" value="0">
        <input type="hidden" name="ControllerNumber" id="ControllerNumber" value="1">
        <input type="hidden" name="UploadNumber" id="UploadNumber" value="">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="1">
        <input type="hidden" name="TrespasserTypeId" id="TrespasserTypeId">
        <input type="hidden" id="TrespasserId10" name="TrespasserId10" value="">
        <input type="hidden" id="TrespasserId11" name="TrespasserId11" value="">
        <input type="hidden" id="TrespasserId12" name="TrespasserId12" value="">
        <input type="hidden" id="TrespasserId15" name="TrespasserId15" value="">
        <input type="hidden" id="TrespasserId16" name="TrespasserId16" value="">
        <input type="hidden" id="TrespasserId17" name="TrespasserId17" value="">
        <input type="hidden" id="InsertTrasgressor" name="InsertTrasgressor" value="false">
        <input type="hidden" id="has_patria_potesta1" value="0">
        <input type="hidden" id="has_patria_potesta2" value="0">
        <input type="hidden" id="has_patria_potesta3" value="0">
        <input type="hidden" name="P" value="' . $str_CurrentPage . '">
        <input type="hidden" id="b_Rent" name="b_Rent" value="1">
        <input type="hidden" name="LicenseDatePropretario" id="LicenseDatePropretario">
        <input type="hidden" name="LicenseDateTrasgressore" id="LicenseDateTrasgressore">
        <input type="hidden" name="Fine_Date_Get" id="Fine_Date_Get">
            
            
            
    	<div class="row-fluid">
        	<div class="col-sm-6">
                <div class="col-sm-12">
                    <div class="col-sm-5 BoxRowLabel" style="text-align:center; background-color: #294A9C;">
                        INSERIMENTO
                    </div>
                    <div class="col-sm-4 BoxRowLabel">
                         Eludi Controlli
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="checkbox" id="Conrolli" name="Controlli"'.($r_PreviousFine['ChkControl'] ? 'checked' : 'off').'>
                    </div>
                    <div class="clean_row HSpace16"></div>
                </div>
        	    <div class="col-sm-12">
                    <div id="Div_InsertionType" style="display:none;">
                        <div class="col-sm-4 BoxRowLabel" style="height: 4.5rem; font-size: large; line-height: 3rem; background-color: #294A9C; text">
                            <strong>Tipo di Atto</strong>
                        </div>
                        <div class="col-sm-8 BoxRowCaption" style="height: 4.5rem; font-size: large; text">
                            <select class="form-control frm_field_required" name="InsertionType" id="InsertionType" style="width:23rem; height: 3.9rem; font-size: large; text">
                                <option value="1" selected>Verbale</option>
                                <option value="2">Preavisso</option>
                                <option value="3">Preinserimento</option>
                            </select>
                        </div>
                    </div>
        	    </div>
                            
                            
        	    <div id="ReportType" class="col-sm-12" style="display:none;">
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel" style="height: 4.5rem; font-size: large; line-height: 3rem; text">
                        <strong>Tipo verbale</strong>
                    </div>
                    <div class="col-sm-9 BoxRowCaption" style="height: 4.5rem; font-size: large; text">
                        <select class="form-control frm_field_required" name="FineTypeId" id="FineTypeId" style="width:23rem; height: 2.9rem; font-size: large; text">
                        ';
$str_out.='
                            <option value="3" selected>Normale</option>
                            <option value="4">Contratto</option>
                            <option value="5">D’ufficio</option>
    
                        </select>
                    </div>
        	    </div>';

$differita = 'Differita';

$str_out .= $str_verbalization_Data;

$str_out .= $str_Locality_Data;

require(INC . '/report_section/vehicle_data.php') ;
require(INC . '/report_section/article_data.php') ;

$str_out .= $str_Vehicle_Data;
$str_out .= $str_Article_Data;

require(INC . '/report_section/notification_fees.php');

$str_out .= '<div id="NotificationFees">' . $str_Notification_Fees . '</div>';

require(INC . '/report_section/controller_data.php');
require(INC . '/report_section/reason_data.php');
//NUOVO
require(INC . '/report_section/trespasser_data.php');



$str_out .= '
	        	<div class="col-sm-12" id="DIV_DayNumber_180" style="display:none;">
        			<div class="col-sm-5 BoxRowLabel">
        				Giorni presentazione documenti
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" value="0" name="DayNumber_180" id="DayNumber_180" style="width:6rem">
 					</div>
  				</div>
    
                <div class="clean_row HSpace4"></div>
	        	<div id="AdditionalFees" class="col-sm-12"">
	        	    <div class="col-sm-9 BoxRowLabel">
        				Spese addizionali ente
					</div>
					<div class="col-sm-3 BoxRowCaption">
				    	<input class="form-control frm_field_numeric" type="text" name="CustomerAdditionalFee" id="CustomerAdditionalFee" value="0.00">
					</div>
  				</div>
                <div class="col-sm-12" style="margin-bottom:10px;">
        			<div class="col-sm-9 BoxRow" style="padding-left: 0.4rem;">
        				Importo totale
					</div>
					<div class="col-sm-3 BoxRow" style="background-color: #294A9C;border-left: 1px solid white;text-align:center;">
                        <i class="fas fa-euro-sign"></i>
        				<span id="span_TotalFee"></span>
					</div>
  				</div>';




$str_out .= '<div id="TrespasserData">' . $str_Trespasser_Data . '</div>';

$str_out .= '</div>';

$str_out .='
  				<div class="col-sm-6" >
                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel" style="text-align:center; background-color: #294A9C;">
                            DOCUMENTAZIONE
                        </div>
                        <div class="col-sm-4 BoxRowLabel">
                             Carica tutta la cartella
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select name="AllFolder" class="form-control" style="width:6rem;">
                                <option value="N">NO
                                <option value="Y">SI
                            </select>
                        </div>
                    </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-6 BoxRow" style="height:10rem;">
                    <div class="example">
                        <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
                    </div>
                </div>
                <div class="col-sm-6 BoxRow" style="height:10rem;">
                    <span id="span_documentInfo"></span>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                    <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"/>
                    </div>
                    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                    Documento
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    <input type="hidden" name="Documentation" id="Documentation" value="">
                    <span id="span_Documentation" style="height:6rem;width:40rem;font-size:1.1rem;"></span>
                </div>
                <div class="col-sm-12 BoxRow">
    
                </div>
            </div>
    
    
	       <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input class="btn btn-default" type="submit" id="save" value="Salva" />
                        <button class="btn btn-default" id="back">Indietro</button>
                     </div>
                </div>
            </div>
        </div>
    </form>';


$str_out .= '
<div id="BoxArticleSearch" style="top:40rem;left:50rem;">
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">
            Articoli trovati
        </div>
        <div class="row-fluid">
            <div class="col-sm-12">
    
                 <div class="BoxRowCaption col-sm-3">
                    <input type="text" name="searchs" id="searchs"  class="form-control bv-form">
                    <input type="hidden" id="art_num" value="">
                 </div>
                 <div class="BoxRowCaption col-sm-3">
                    <button type="button" id="search_btn" class="btn btn-default btn-xs" style="margin-top: -8px">Cerca</button>
                    <i class="glyphicon glyphicon-remove" id="remove_btn" style="margin-top:0.1rem;font-size:1.7rem;"></i>
                 </div>
                 <div class="BoxRowCaption col-sm-6"></div>
    
            </div>
        </div>
        <span class="fa fa-times-circle close_window_article" style="color:#fff;position:absolute; right:10px;top:2px;font-size:20px; "></span>
    </div>
    <div class="clean_row HSpace4"></div>
    <div id="Div_Article_Content" style="overflow:auto;height:23rem"></div>
</div>
    
    
<div class="overlay" id="overlay" style="display:none;"></div>
';
require(INC . "/module/mod_trespasser.php");
require(INC . "/module/mod_zip.php");
require(INC . "/module/mod_foreignzip.php");



echo $str_out;
?>

<script type="text/javascript">
	var getInsertionType = <?= isset($_REQUEST['insertionType']) ? $_REQUEST['insertionType'] : 0 ?>;
	var ActType = 3;
    var chkDate = true;
    var controller = true;
    var chkCode = true;
    var giaPresente = false;
    var chkTime = true;
    var tresRequired = 0;
    var young = false;
    var namespan;

    var addMass = <?=MASS?>;
    $('#VehicleMass').change(function() {
        checkFee();
    });
    
//     //NUOVO ANAGRAFE
//     var validPIVA = true;
//     var validCF = true;
//     //

    $('.find_list').keyup(function () {
        var min_length = 2; // min caracters to display the autocomplete
        var keyword = $(this).val();

        namespan = $(this).attr('name');
        if (keyword.length >= min_length) {

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: keyword, field: namespan},
                success: function (data) {

                    $('#' + namespan + '_List').show();
                    $('#' + namespan + '_List').html(data);
                }
            });
        } else {
            $('#' + namespan + '_List').hide();
        }
    });

    $('#Address').focusout(function(){
        setTimeout(function () {
            $('#Address_List').hide();
        }, 150);
    });

    $('#Address').focusin(function(){
        $('#Address_List').show();
    });

    function set_item(item, namespan) {
        // change input value
        $('#' + namespan).val(item);
        //$('#CustomerID').val(id);
        $('#' + namespan + '_List').hide();
    }

    function checkFee() {
        var mass = $('#VehicleMass').val();
        var sum = 0;
        for (var i = 1; i <= $('#ArticleNumber').val(); i++) {

            var Fee = parseFloat($('#ArticleId_' + i).attr('fee'));
            var MaxFee = parseFloat($('#ArticleId_' + i).attr('maxFee'));
            var PrefectureFee = parseFloat($('#PrefectureFee_' + i).val());
            var FineTypeId = $('#FineTypeId').val();

            if ($('#ArticleId_' + i).attr('addMass') == 1) {
                if(mass<addMass){
                    sum+=Fee;
                    if(FineTypeId==4){
                        $('#Fee_'+i).prop('type', 'text').val(Fee.toFixed(2));
                        $('#MaxFee_'+i).prop('type', 'text').val(MaxFee.toFixed(2));
                    }else{
                        $('#Fee_'+i).val(Fee.toFixed(2));
                        $('#span_Fee_'+i).html(Fee.toFixed(2));
                        $('#MaxFee_'+i).val(MaxFee.toFixed(2));
                        $('#span_MaxFee_'+i).html(MaxFee.toFixed(2));
                    }
                } else {
                    sum+=Fee*2;
                    if(FineTypeId==4){
                        $('#Fee_'+i).prop('type', 'text').val((Fee*2).toFixed(2));
                        $('#MaxFee_'+i).prop('type', 'text').val((MaxFee*2).toFixed(2));
                    } else {
                        $('#Fee_'+i).val((Fee*2).toFixed(2));
                        $('#span_Fee_'+i).html((Fee*2).toFixed(2));
                        $('#MaxFee_'+i).val((MaxFee*2).toFixed(2));
                        $('#span_MaxFee_'+i).html((MaxFee*2).toFixed(2));
                    }
                }
            }
            if ($('#ArticleId_' + i).attr('addMass') == '0') {
                sum+=Fee;
            }
        }
        if ($('#InsertionType').val() != 1) {
        	sum+= parseFloat($('#TotalCharge').val());
        } else
        	sum += parseFloat($('#CustomerAdditionalFee').val());
        
        $('#span_TotalFee').html(sum.toFixed(2));
    }

  	//NUOVO
    function checkOnRoad(){
        var TrespasserType = $("#TrespasserType").val();
    	var Date = $("#FineDate").val();
        if ($("#NotificationType").val() == '2'){
            if (TrespasserType == 1){
            	$("#NotificationType_10").val(1);
            	$("#NotificationType_10").change();
            } else if (TrespasserType == 2) {
            	$("#NotificationType_11").val(1);
            	$("#NotificationType_11").change();
            } else if (TrespasserType == 3) {
            	$("#NotificationType_11").val(1);
            	$("#NotificationType_11").change();
            } else if (TrespasserType == 4) {
            	$("#NotificationType_12").val(1);
            	$("#NotificationType_12").change();
            }
        }
    }

    function sumPoints (){
    	var points = 0;
    	var articleNumber = $('#ArticleNumber').val();
    	var type = (young) ? 'YoungLicensePoint_' : 'LicensePoint_';
    	$("[id^="+type+"]").each(function (index) {
			index++;
			if (index <= articleNumber && $('#' + type + index).html().trim() != ""){
				points += parseInt($('#' + type + index).html());
			}
    	});
    	if (points >= 15) {
        	points = 15;
        	$('#span_TotalPoints').html(points + " (MAX)");
    	} else $('#span_TotalPoints').html(points);

    	$('#TotalPoints').val(points);
    }

    $('document').ready(function(){
    	checkFee();
    	sumPoints ();

    	$('#InsertionType, #CountryId').change(function(){
    		checkFee();
    	});

//         $('#Conrolli').on('click',function(){
//             $('#Code').trigger("change");
//             $('#ControllerId').trigger("change");
//             controller = true;
//             var Eludi_Controlli  = $('#Conrolli').is(':checked');
//             if (Eludi_Controlli == false){
//                 $("#save").prop("disabled", false);
//                 $("#span_acce_1").removeClass("help-block");
//                 $("#span_acce_1").html('');
//             }
//         });

        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        <?= CreateSltChangeJQ("Controller", "CityId='".$_SESSION['cityid']."' AND Disabled=0", "Name", "ControllerCode", "Code", "Id", "ControllerId"); ?>
        
        $(document).on('click', '.add_article', function(){
            chkArticle();
        });


//         $('#FineTime').change(function() {

//             var str = $('#FineTime').val();
//             if(str.length==4){
//                 $('#FineTime').val( str.substr(0,2) + ":" + str.substr(2,2) );
//                 str = $('#FineTime').val();
//             }

//             var part1 = parseInt(str.substr(0,2));
//             var part2 = parseInt(str.substr(3,2));

//             var hours = 24;
//             var minutes = 59;

//             if (part1 >hours || part2 >minutes){

//                 chkTime = false;
//                 $("#save").prop("disabled", true);
//                 $("#span_time").addClass("help-block");
//                 $("#span_time").html('Ora errato!');
//             } else{

//                 chkTime=true;
//                 if(chkTime==true) {
//                     $("#save").prop("disabled", false);
//                     $("#span_time").removeClass("help-block");
//                     $("#span_time").html('');
//                 }
//             }
//         });


        $('#UploadNumber').on('change', function() {
            if ($("#UploadNumber").val() == '2')
            	$('#span_documentInfo').html('Selezionare almeno 2 fotogrammi');
            else 
            	$('#span_documentInfo').html('');
        });
        

        $('#VehicleTypeId').change(function () {
            var type = $('#VehicleTypeId').val();
            if(type==2 || type==9) {
                $('#massa').hide();
                $('#toHide2').show();
            }
            else {
                $('#massa').show();
                $('#toHide2').hide();
            }
        });


        $('#Toponym').change(function() {
            var type = $(this).val();
            $('#Address').val(type + ' ' + $('#StreetType').val());

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').html(data);
                }
            });
        });
        $('#StreetType').change(function() {
            var type = $(this).val();
            $('#Address').val($('#Toponym').val() + ' ' + type);

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').html(data);
                }
            });
        });


        $('#VehicleMass').focusout(function() {
            if($(this).val()=='') $(this).val(0);
        });


        $('#VehiclePlate').focusout(function() {
            var VehiclePlate = $(this).val();
            var FineDate = $('#FineDate').val();
            var FineTime = $('#FineTime').val();
            var id = $( "#CountryId" ).val();
            var checkPlate;
            var find;
            var message = "Attenzione: la targa potrebbe non essere italiana ";
            var VehicleTypeId = $('#VehicleTypeId').val();

            if(id=='Z000'){
                find = 0;
                // checkPlate = '^[a-zA-Z]{2}[a-zA-Z0-9][0-9]{5}$';
                // if(VehiclePlate.match(checkPlate)!=null) find = 1;
                if(VehicleTypeId == 2){
                    checkPlate = '^[a-zA-Z]{2}[0-9]{5}$';
                    if(VehiclePlate.match(checkPlate)!=null)  find = 1;
                } else if(VehicleTypeId == 9) {
                	checkPlate = '^[a-zA-Z0-9]{6}$';
                	if(/*VehiclePlate.match(/[01aAeEiIoOqQuU]/)==null &&*/ VehiclePlate.match(checkPlate)!=null) find = 1;
                } else {
                    checkPlate = '^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)!=null) find = 1;
                }
                
                if(VehicleTypeId != 9 && VehiclePlate.match(/[qQuUoOiI]/) != null) {
                    find = 0;
                    message += 'oppure si tratta di una vecchia targa';
                }

                //Autoveicolo
                if(VehicleTypeId == 1 && VehiclePlate.match('^[a-zA-Z]{2}[0-9]{5}$') != null) {
                    find = 0;
                    message = 'Targa errata: forse si tratta di motoveicolo';
                } else if (VehicleTypeId == 1 && VehiclePlate.match('^[a-zA-Z0-9]{6}$') != null) {
                    find = 0;
                    message = 'Targa errata: forse si tratta di ciclomotore';
                }

                //Motoveicolo
                if(VehicleTypeId == 2 && VehiclePlate.match('^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$') != null) {
                    find = 0;
                    message = 'Targa errata: forse si tratta di autoveicolo';
                } else if (VehicleTypeId == 2 && VehiclePlate.match('^[a-zA-Z0-9]{6}$') != null) {
                    find = 0;
                    message = 'Targa errata: forse si tratta di ciclomotore';
                }

                //Ciclomotore
                if(VehicleTypeId == 9 && VehiclePlate.match('^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$') != null) {
                    find = 0;
                    message = 'Targa errata: forse si tratta di autoveicolo';
                } else if (VehicleTypeId == 9 && VehiclePlate.match('^[a-zA-Z]{2}[0-9]{5}$') != null) {
                    find = 0;
                    message = 'Targa errata: forse si tratta di motoveicolo';
                }
                
                if(find==0 && $('#VehiclePlate').val() != "") alert(message);
            }
            else {
                if(id=='Z112'){
                    checkPlate ='^[a-zA-Z]{1,3}[ t][ t][a-zA-Z]{1,3}[0-9]{1,4}$';
                    if(VehiclePlate.match(checkPlate)==null) alert("Attenzione: la targa potrebbe non essere tedesca");
                }
    
                if(id=='Z110'){
                    find = 0;
                    checkPlate ='^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;
    
                    checkPlate ='^[0-9]{3,4}[a-zA-Z]{2}[0-9]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;
    
                    if(find==0) alert("Attenzione: la targa potrebbe non essere francese");
                }
    
    
                if(id=='Z131'){
                    find = 0;
                    checkPlate ='^[a-zA-Z]{2}[0-9]{4}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;
    
                    checkPlate ='^[0-9]{4}[a-zA-Z]{3}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;
    
                    if(find==0) alert("Attenzione: la targa potrebbe non essere spagnola");
                }
    
                if(id=='Z129'){
                    checkPlate ='^[a-zA-Z]{1,2}[0-9]{2}[a-zA-Z]{3}$';
                    if((VehiclePlate.match(checkPlate))==null) alert("Attenzione: la targa potrebbe non essere rumena");
                }
                if(id=='Z133'){
                    checkPlate ='^[a-zA-Z]{2}[0-9]{2,6}$';
                    if((VehiclePlate.match(checkPlate))==null) alert("Attenzione: la targa potrebbe non essere svizzera");
                }
                if(id=='Z103'){
                    find = 0;
                    checkPlate ='^[0-9][a-zA-Z]{2,3}[0-9]{2,3}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;
    
                    checkPlate ='^[a-zA-Z]{3}[0-9]{3}$';
                    if((VehiclePlate.match(checkPlate))==null) find = 1;
    
                    if(find==0) alert("Attenzione: la targa potrebbe non essere belga");
                }
    
                $.ajax({
                    url: 'ajax/search_plate.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {VehiclePlate:VehiclePlate, FineDate:FineDate, FineTime:FineTime},
                    success:function(data){
    
    
                        if(data.H>0){
                            $('#div_chkPlate').addClass("div_Height_"+data.H);
                        }else{
                            $('#div_chkPlate').removeClass("div_Height_150");
                            $('#div_chkPlate').removeClass("div_Height_300");
                        }
    
                        if(data.F!=""){
                            $('#fine_content').addClass("div_HiddenContent");
                            $('#fine_content').html(data.F);
                            $('#fine_content').show();
                        }else{
                            $('#fine_content').removeClass("div_HiddenContent");
                            $('#fine_content').hide();
                        }
    
                        if(data.T!=""){
                            $('#trespasser_content').addClass("div_HiddenContent");
                            $('#trespasser_content').html(data.T);
                            $('#trespasser_content').show();
                        }else{
                            $('#trespasser_content').removeClass("div_HiddenContent");
                            $('#trespasser_content').hide();
                        }
    
    
                    }
                });            
            }
        });

        $('#back').click(function () {
            window.location = "<?= $str_BackPage ?>";
            return false;
        });

        $('#f_violation').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        //submit
		
        $('#save').click(function(){
        	var type = $('#TrespasserType').val();
        	var error = '';
        	var isPlateTrespasser = false;

        	//Controlla se è selezionato un trasgressore dalla voce "Altri trasgressori con questa targa", se sì non controlla la selezione trasgressori
            $('input[name="checkbox[]"]').each(function () {
            	isPlateTrespasser = $(this).is(':checked');
                if (isPlateTrespasser) {
                	return false;
                }
            });
        	
        	if($('#InsertionType').val() == '1' && !isPlateTrespasser){
				if ($('#FineTypeId').val() != '5' ){
					if ($('#NotificationType').val() != '1'){
		            	if (type == '1'){
		                	if (!$('#TrespasserId10').val()) error = "Assegnare un trasgressore";
		            	} else {
		                	if (!$('#TrespasserId10').val() || !$('#TrespasserId11').val()) error = "Assegnare i trasgressori";
		            	}
					}
				}
            }

        	if (!$('#ArticleId_1').val()) error = "Inserire almeno un articolo";

        	if (!chkCode) error = "Controllare il campo Riferimento";

        	if($('#InsertionType').val() == '1' && $('#FineTypeId').val() == '4' ){
            	var path = $("#Documentation").val();
            	if (path != ""){
            		var extension = path.substr( (path.lastIndexOf('.') +1) );
            		if (strtolower(extension) != "pdf")
            			error = "Per i verbali di tipo contratto sono previsti esclusivamente documenti in formato .pdf";
            	}
        	}
            
            if (error){
                alert(error);
                return false;
            }
        });

        $('#f_violation').bootstrapValidator({
            live: 'disabled',
            fields: {
            	AdditionalSanctionField: {
            		selector: '.additionalsanctionfield',
                    validators: {
                        callback:{
                            callback: function(value, password, $field){
                                var ArticleNumber = $field.data('articlenumber');
                                var additionalSanctionInput = $field.val().trim();
                                var additionalSanctionTxt = $('#AdditionalSanctionSelect_'+ArticleNumber).text().trim();

                                if(additionalSanctionInput.length == 0){
                                    return {
                                        valid: false,
                                        message: 'Richiesto'
                                    };
                                 }
                                
                                if(additionalSanctionInput === additionalSanctionTxt)
                                {
                                    return {
                                        valid: false,
                                        message: 'Il testo deve essere variato dall\'originale'
                                    };
                                }
                                return true;
                            }           
                        }    
                    }
                },

                FineTime: {
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        },
                        regexp: {
                            //regexp: '^(1?[0-9]|2[0-9]):[0-5][0-9]$',
                            regexp: '^(0?[0-9]|1?[0-9]|2[0-4]):[0-5][0-9]$',
                            message: 'Ora non valida'
                        }
                    }

                },
                
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },

                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },


                fine_date:{
                    selector: '.fine_date',
                    validators: {
                        callback: {
                            message: 'Anno Errato',
                            callback: function(value) {
                                var error = {
                                    valid: false,
                                    message: 'Data Errato'
                                };
                                if(value=='') {
                                    return {
                                        valid: false,
                                        message: 'Richiesto'
                                    };
                                }
                                var str = value;
                                if(str.length==10){
                                    if(parseInt(str.substr(6,4)) != parseInt('<?= $_SESSION['year']?>')) return error;
                                    if(parseInt(str.substr(0,2)) > 31 || parseInt(str.substr(0,2)) < 1) return error;
                                    if(parseInt(str.substr(3,2)) > 12 || parseInt(str.substr(3,2)) < 1) return error;
                                } else return error;
                                return true
                            }
                        },
                    }
                },

            }
        });


        $('#tab_Company_src').click(function () {
            var NumberTab = 10;
            $('#Genre').val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');
        });

        $('#tab_Trespasser_src').click(function () {
            var NumberTab = 10;
            $('#Genre').val('M');
            $("#sex_code").html("");
            $('#CompanyName'+NumberTab).val('');
        });

        $('#tab_company10').click(function () {
            var NumberTab = 10;
            $('#Genre'+NumberTab).val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');
        });        

        $('#tab_Trespasser10').click(function () {
            var NumberTab = 10;
            $('#Genre'+NumberTab).val('M');
            $('#CompanyName'+NumberTab).val('');
        });

        $('#tab_company11').click(function () {
            var NumberTab = 11;

            $('#Genre'+NumberTab).val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');

        });

        $('#tab_Trespasser11').click(function () {
            var NumberTab = 11;
            $('#Genre'+NumberTab).val('M');
            $('#CompanyName'+NumberTab).val('');
        });

        //NUOVO
        $('#tab_company12').click(function () {
            var NumberTab = 12;

            $('#Genre'+NumberTab).val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');

        });

      	//NUOVO
        $('#tab_Trespasser12').click(function () {
            var NumberTab = 12;
            $('#Genre'+NumberTab).val('M');
            $('#CompanyName'+NumberTab).val('');
        });

        $('#CompanyName10').keyup({ NumberTab:10 },chkTrespasser);
        $('#Name10').keyup({ NumberTab:10 },chkTrespasser);
        $('#Surname10').keyup({ NumberTab:10 },chkTrespasser);

        $('#CompanyName11').keyup({ NumberTab:11 },chkTrespasser);
        $('#Name11').keyup({ NumberTab:11 },chkTrespasser);
        $('#Surname11').keyup({ NumberTab:11 },chkTrespasser);

        //NUOVO
        $('#CompanyName12').keyup({ NumberTab:12 },chkTrespasser);
        $('#Name12').keyup({ NumberTab:12 },chkTrespasser);
        $('#Surname12').keyup({ NumberTab:12 },chkTrespasser);


        $('#Name15').keyup({ NumberTab:15 },chkTrespasser);
        $('#Surname15').keyup({ NumberTab:15 },chkTrespasser);

        $('#Name16').keyup({ NumberTab:16 },chkTrespasser);
        $('#Surname16').keyup({ NumberTab:16 },chkTrespasser);

      	//NUOVO
        $('#Name17').keyup({ NumberTab:17 },chkTrespasser);
        $('#Surname17').keyup({ NumberTab:17 },chkTrespasser);

        //function trespasser
        function chkTrespasser(e){
            var NumberTab = e.data.NumberTab;

            var min_length = 3;

            if(NumberTab<14){
                var CompanyName = $('#CompanyName' + NumberTab).val();
                //var Genre = $('#Genre').val();
            }else{
                var CompanyName = '';
                var Genre = '';
            }

            var TaxCode = '';
            if(Genre == "D"){
                TaxCode = $('#TaxCode2_' + NumberTab).val();
            } else TaxCode = $('#TaxCode' + NumberTab).val();

            var Name = $('#Name' + NumberTab).val();
            var Surname = $('#Surname' + NumberTab).val();
            var FineDate = $('#FineDate').val();
            var CountryId = $('#CountryId').val();
            if (Name !="" || Surname!=""){
                var Genre = $('#Genre').val();
            } else {
                var Genre = $('#Genre' + NumberTab).val();
            }

            //NUOVO search_trespasser_rent_NEW.php
            if (CompanyName.length >= min_length || Surname.length >= min_length || Name.length >= min_length) {
                console.log(TaxCode);
                $.ajax({
                    url: 'ajax/search_trespasser_rent.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {FineDate:FineDate, CompanyName: CompanyName, Surname: Surname, Name: Name, Genre: Genre, NumberTab: NumberTab, TaxCode:TaxCode, CountryId:CountryId},
                    success: function (data) {

                        $('#trespasser_content_' + NumberTab ).show();
                        $('#trespasser_content_' + NumberTab).html(data.Trespasser);

                    }
                });
            } else {
                $('#trespasser_content_' + NumberTab).hide();
            }
        }
        
//         $(document).on('focusout','#FineDate',function() {
//             var Notifica = $("#NotificationType").val();
//             if(Notifica == 2){
//                 $("#NotificationDate_10").val($(this).val());
//             	//setOnRoadNotifications();
//             }
//             for(i=1;i<=5;i++) {
//                 $('.select_controller_'+i).trigger("change");
//             }
//         });

//         $("#NotificationDate_10").on("change",function () {
//             //$("#NotificationType_10").attr("required",true);
//             $("#notification_10").addClass("help-block");
//             $("#notification_10").html('Richiesto!');
//             $('#NotificationType_10').addClass('frm_field_required');
//             if ($("#NotificationDate_10").val() ==""){
//                 $("#notification_10").removeClass("help-block");
//                 $("#notification_10").html('');
//                 $('#NotificationType_10').removeClass('frm_field_required');
//                 if ($("#NotificationType").val()==1) {
//                     $("#span_name_10").removeClass("help-block");
//                     $("#span_name_10").html('');
//                 }
//             }

//         });
//         $("#NotificationDate_11").on("change",function () {
//             $("#notification_11").addClass("help-block");
//             $("#notification_11").html('Richiesto!');
//             $('#NotificationType_11').addClass('frm_field_required');
//             if ($("#NotificationDate_11").val() ==""){
//                 $("#notification_11").removeClass("help-block");
//                 $("#notification_11").html('');
//                 $('#NotificationType_11').removeClass('frm_field_required');
//                 if ($("#NotificationType").val()==1) {
//                     $("#span_name_11").removeClass("help-block");
//                     $("#span_name_11").html('');
//                 }
//             }
//         });

//         $("#NotificationType").on('change',function () {
//             var id = $(this).val();
//             var finedate = $("#FineDate").val();

//             if (id ==2){
//                 $("#NotificationDate_10").val(finedate);
//                 var search = "Su strada";
//                 $('#NotificationType_10 option:contains('+search+')').prop('selected',true);

//                 $("#NotificationDate_10").attr("readonly",true);
//                 $("#NotificationDate_10").attr("required",true);
//                 $("#NotificationDate_10").addClass("frm_field_required")

//             }else{
//                 $("#NotificationDate_10").val('');
//             }
//         });
//         $(document).on("change","#NotificationType_10",function () {

//             var id = $(this).val();
//             var finedate = $("#FineDate").val();
//             if (id !=1){
//                 if ( $("#NotificationDate_10").val() =="") {
//                     $("#NotificationDate_10").val(finedate);
//                 }
//                 $("#NotificationDate_10").attr("readonly",false);
//             }else{
//                 $("#NotificationDate_10").val(finedate);
//                 $("#NotificationDate_10").attr("readonly",true);
//             }
//             $("#notification_10").removeClass("help-block");
//             $("#notification_10").html('');
//         });

//         $(document).on("change","#NotificationType_11",function () {
//             var finedate = $("#FineDate").val();
//             $("#NotificationDate_11").val(finedate);
//             $("#notification_11").removeClass("help-block");
//             $("#notification_11").html('');
//         });

		//NUOVO
		$('#NotificationType_10, #NotificationType_11, #NotificationType_12, #NotificationType_15, #NotificationType_16, #NotificationType_17').on('change', function() {
			var ElementId = $(this).attr("id").split('_').pop();
			var FineDate = $('#FineDate').val();
			if ($(this).val() == 1) {
				$('#NotificationDate_' + ElementId).val(FineDate);
			}
		});

		//NUOVO
		$('#FineDate').on('focusout', function() {
			checkOnRoad();
			$('#NotificationType_10, #NotificationType_11, #NotificationType_12, #NotificationType_15, #NotificationType_16, #NotificationType_17').change();
		});

		$('#NotificationType').on('change', function() {
			checkOnRoad();
		});

		//NUOVO
        //trespasser_type
        $('#TrespasserType').on('change', function() {
           	//RESET
           	young = false;
        	$("#NotificationDate_10, #NotificationDate_11, #NotificationDate_12").val("");
        	$("#NotificationType_10, #NotificationType_11, #NotificationType_12").val("");
        	$("[id^=TrespasserId]").val("");
        	$("[id^=span_name_]").parent().css("background-color", "#294A9C");
        	$("[id^=span_name_]").html("");
        	$("[id^=DIV_Tutor_]").hide();
        	$("[id^=DIV_Message_]").hide();
            $("[id^=LicensePoint_]").show();
            $("[id^=YoungLicensePoint_]").hide();
            sumPoints();
        	checkOnRoad();
//             type = this.value;
//             var finedate = $("#FineDate").val();
//             var notifica = $("#NotificationType").val();
//             if(notifica == 2 && type==2){
//                 $("#NotificationDate_11").val(finedate);
//                 var search = "Su strada";
//                 $('#NotificationType_11 option:contains('+search+')').prop('selected',true);
//                 $("#NotificationDate_11").attr("readonly",true);
//                 $('#NotificationDate_11').addClass('frm_field_required');
//                 $("#NotificationDate_10").val("");
//                 $("#NotificationType_10").val("");
//                 $("#NotificationDate_10").attr("readonly",false);
//             }
            if(this.value==1){
                tresRequired = 1;
                $('#trasgressor2').html('PROPRIETARIO:');
                $('#DIV_TrespasserType').hide();
                $('#proprietario').show();
                $('#driver').hide();
                //$('#NotificationDate_10').removeClass('frm_field_required');
                $('#TrespReceiveDate').hide();
                $('#TrespOwnerAdditionalFee').hide();

            } else if(this.value==2){
                tresRequired = 2;
                $('#trasgressor2').html('PROPRIETARIO:');
                $('#trasgressor1').html('TRASGRESSORE:');
                $('#DIV_TrespasserType').show();
                $('#proprietario').show();
                $('#driver').hide();
                //$('#NotificationDate_10').removeClass('frm_field_required');
                $('#TrespReceiveDate').show();
                $('#TrespOwnerAdditionalFee').hide();
            } else if(this.value==3){
                tresRequired = 2;
                //$('#NotificationDate_10').addClass('frm_field_required');
                $('#trasgressor2').html('NOLEGGIO:');
                $('#trasgressor1').html('NOLLEGGIANTE:');
                $('#proprietario').show();
                $('#driver').hide();
                $('#DIV_TrespasserType').show();
                $('#TrespReceiveDate').show();
                $('#TrespOwnerAdditionalFee').show();
            } else {
                tresRequired = 2;
                $('#trasgressor2').html('NOLEGGIO:');
                $('#trasgressor1').html('NOLLEGGIANTE:');
                $('#proprietario').show();
                $('#driver').show();
                $('#DIV_TrespasserType').show();
                $('#TrespReceiveDate').show();
                $('#TrespOwnerAdditionalFee').show();
            }
        });

        //add_button

        $(".add_button_10").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggio');
            } else {
                $('#TitleTrespasser').html('Inserimento Obbligato');
            }
            $('#TrespasserTypeId').val(10);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();
            $('#Fine_Date_Get').val(finedate_);

            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_11").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggiante');
            } else {
                $('#TitleTrespasser').html('Inserimento Trasgressore');
            }
            $('#TrespasserTypeId').val(11);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();

            $('#Fine_Date_Get').val(finedate_);

            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        //NUOVO
        $(".add_button_12").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggiante');
            } else {
                $('#TitleTrespasser').html('Inserimento Trasgressore');
            }
            $('#TrespasserTypeId').val(12);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();

            $('#Fine_Date_Get').val(finedate_);

            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_15").click(function () {
            $('#TitleTrespasser').html('Inserimento Trasgressore');
            $('#TrespasserTypeId').val(15);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_16").click(function () {
            $('#TitleTrespasser').html('Inserimento Obbligato');
            $('#TrespasserTypeId').val(16);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        //NUOVO
        $(".add_button_17").click(function () {
            $('#TitleTrespasser').html('Inserimento Obbligato');
            $('#TrespasserTypeId').val(17);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });

        //NUOVO
        //anagrafica
        $('#btn_saveanagrafica').on('click',function () {
            var TrespasserType = $('#TrespasserType').val();

            var Fine_Date_Get = $('#Fine_Date_Get').val();
            var new_fine = Fine_Date_Get.split("/");
            var newfinedt = new_fine[2]+"/"+new_fine[1]+"/"+new_fine[0];

            var BornDate = $('#BornDate').val();
            var new_born = BornDate.split("/");
            var new_borndt = new_born[2]+"/"+new_born[1]+"/"+new_born[0];

            var TrespasserTypeId = $('#TrespasserTypeId').val();

            $("#error_name_"+$('#TrespasserTypeId').val()).removeClass("help-block");
            $("#error_name_"+$('#TrespasserTypeId').val()).html('');

            $("#error_name_10").removeClass("help-block");
            $("#error_name_10").html('');

            $("#error_name_11").removeClass("help-block");
            $("#error_name_11").html('');
            var years = new Date(new Date(newfinedt)- new Date(new_borndt)).getFullYear() - 1970;
            if (TrespasserType !=3 && TrespasserType !=4){
                if (TrespasserTypeId==10){

                    if (years < 18) {
                        $("#DIV_Tutor_10").show();
                        $("#has_patria_potesta1").val(1);
                        $("#has_patria_potesta3").val(1);

                    }
                }
                if (TrespasserTypeId==11) {

                    if (years < 18) {

                        $("#has_patria_potesta2").val(1);
                        $("#DIV_Tutor_11").show();
                    }
                }
            }
          	//NUOVO
            if (TrespasserType == 4){
                if (TrespasserTypeId==12) {

                    if (years < 18) {

                        $("#has_patria_potesta3").val(1);
                        $("#DIV_Tutor_12").show();
                    }
                }
            }



        });


        $(".close_window_article").click(function () {
            $('#BoxArticleSearch').hide();
        });

        $(".fa-pencil-square-o, .glyphicon-search, .fa-share, .fa-caret-down, .fa-caret-up, .fa-edit").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });


        $("#Edit_Contestazione").click(function () {
            if($("#ReasonOwner").is(":hidden")){
                $('#ReasonId').css("display","none");
                $('#ReasonOwner').removeAttr( 'style' );
            } else {
                $('#ReasonOwner').css("display","none");
                $('#ReasonId').removeAttr( 'style' );
            }

        });

        $("#search_btn").click(function () {
            var src = $('#searchs').val();
            var id1=null;
            var RuleTypeId = $('#RuleTypeId').val();
            var artid = $("#art_num").val();
            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, search: src, ArticleCounter: artid, RuleTypeId: RuleTypeId},
                success: function (data) {
                    $('#Div_Article_Content').html(data.Article);
                }
            });
        });

        $("#remove_btn").click(function () {
            var src = $('#searchs').val('');
        });
        
        ///mancata hide
        $("#NotificationType").on('change',function () {
            var id = $(this).val();
            if(id==2){
                $("#div_Reason").hide();
                $("#ReasonOwner").val('');

            }else{
                $("#div_Reason").show();
            }
        });

        $( function() {
            $( "#BoxArticleSearch" ).draggable();
        } );

        
        $( "#f_ins_trespasser" ).on( "submitted", function( event, TrespasserId, TrespasserName ) {
        	  //console.log(TrespasserId, TrespasserName);
              setTimeout(function(){
            	  $("#span_name_"+$('#TrespasserTypeId').val()).parent().css( "background-color", "#299c35" );
              }, 100);
              $("#span_name_"+$('#TrespasserTypeId').val()).html(TrespasserName);
              $("#TrespasserId"+$('#TrespasserTypeId').val()).val(TrespasserId);
              $(".add_button_"+$('#TrespasserTypeId').val()).hide();
    	});

      	//Controlla gli articoli duplicati e avvisa in caso affermativo
        $("#BoxArticleSearch").on("click", ".fa-plus-square", function(){
            setTimeout(function(){
                var ArticleNumber = $('#ArticleNumber').val();
                var $elems = $("[name^=ArticleId_]").slice(0,ArticleNumber);
                var values = [];

                var isDuplicated = false;

                $elems.each(function () {
                  if(!this.value) return true;
                  if(values.indexOf(this.value) !== -1) {
                     isDuplicated = true;
                     return false;
                   }
                  values.push(this.value);
                });


                if (isDuplicated){
                    alert('Attenzione, sono stati inseriti uno o più articoli duplicati');
                }
            }, 200);
        });

    });

</script>

<script type="text/javascript">

var str_GET_Parameter = <?php echo '"'. $str_GET_Parameter . '"'; ?>;

function callFileTree(filePath) {
    $("#Documentation").val('');
    $("#span_Documentation").html('');
    $("#preview_img").hide();
    $("#preview_doc").hide();

    var scriptPath = 'jqueryFileTree.php?insert=true';
	
    $('#fileTreeDemo_1').fileTree({ root: filePath, script: scriptPath }, function(file) {

        var FileType = file.substr(file.length - 3);

        if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc'){
            $("#preview_img").hide();
            $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src='"+file+"'></iframe>");
            $("#preview_doc").show();
        }else{
            $("#preview_doc").hide();
            $("#preview").attr("src",file);
            $("#preview_img").show();
        }
        

        $("#Documentation").val(file);
        $("#span_Documentation").html(file);
    });

//     setTimeout(function(){
//         $(".jqueryFileTree div:first a").trigger("click")
//         $(".jqueryFileTree li:first a").trigger("click")
//         $(".jqueryFileTree li:first ul a").trigger("click")
//     }, 1000);
    
//     setTimeout(function(){
//         $(".jqueryFileTree li:first ul a").trigger("click")
//     }, 1500);
}

$('document').ready(function(){

	if (getInsertionType == '0'){
		$('#Div_InsertionType').show();
		getInsertionType = '1';
	}

	$('#InsertionType').val(getInsertionType == 4 ? 3 : getInsertionType);

	$('#NotificationFees').hide();

	//DA CHIEDERE
	$("#b_Rent").val('');

	//Reset bootstrapValidator per Microsoft EDGE
	$('#f_violation select').change(function(){
    	$('#f_violation').data('bootstrapValidator').resetForm();
	});
	
    $('#InsertionType').change(function(){

	//Reset validatori
		$('#f_violation').data('bootstrapValidator').resetForm();

    //Verbale
        if($('#InsertionType').val() == '1'){
        	ActType = 3;
        //Tipo Verbale
        	$("#FineTypeId").val('3')
        	$("#FineTypeId option[value=2]").prop('disabled', true);
        	$('#ReportType').show();
        //Verbalizzante
        	$('#VerbalizationData').show();
       	//Riferimento
   			if (!$('#Code').hasClass('frm_field_required')){
   				$('#Code').addClass('frm_field_required');
   				$('#f_violation').bootstrapValidator('addField', $('#Code'));
   	   		}
       	//Notifica
       		$('#Notification').show();
       		if (!$('#NotificationType').hasClass('frm_field_required'))
       			$('#NotificationType').addClass('frm_field_required');
    	//Spese Notifica
        	$('#NotificationFees').hide();
    	//Spese addizionali ente
    		$('#AdditionalFees').show();
    	//Mancata contestazione
	   		if ($('#NotificationType').val() == '2'){
       			$('#ReasonId').removeClass('frm_field_required');
       			$('#div_Reason').hide();
    		}
    	//Dati Trasgressore
        	$('#TrespasserData').show();
        	$("[id^=NotificationDate_]").prop("disabled", false);
        	$("[id^=NotificationType_]").prop("disabled", false);
        	$("#OwnerAdditionalFee").prop("disabled", true);
        //Documentazione
        	callFileTree('public/_REPORT_/<?= $_SESSION['cityid']?>/');
    	//Action
        	$('#f_violation').attr('action', "mgmt_report_add_exe.php" + str_GET_Parameter);
    	}
  	//Preavviso
        if($('#InsertionType').val() == '2'){
        	ActType = 2;
    	//Tipo Verbale
        	$('#ReportType').hide();
        //Verbalizzante
        	$('#VerbalizationData').hide();
       	//Riferimento
   			if (!$('#Code').hasClass('frm_field_required')){
   				$('#Code').addClass('frm_field_required');
   				$('#f_violation').bootstrapValidator('addField', $('#Code'));
   	   		}
       	//Notifica
       		$('#Notification').hide();
       		$('#NotificationType').removeClass('frm_field_required');
    	//Spese Notifica
        	$('#NotificationFees').show();
    	//Spese addizionali ente
			$('#AdditionalFees').hide();
    	//Mancata contestazione
	   		if (!$('#ReasonId').hasClass('frm_field_required'))
				$('#ReasonId').addClass('frm_field_required');
    		$('#div_Reason').show();
    	//Dati Trasgressore
        	$('#TrespasserData').show();
        	$("[id^=NotificationDate_]").prop("disabled", true);
        	$("[id^=NotificationType_]").prop("disabled", true);
        	$("#OwnerAdditionalFee").prop("disabled", false);
        //Documentazione
        	callFileTree('public/_WARNING_/<?= $_SESSION['cityid']?>/');
    	//Action
        	$('#f_violation').attr('action', "mgmt_warning_add_exe.php" + str_GET_Parameter);
    	}
  	//Preinserimento
        if($('#InsertionType').val() == '3'){
        	ActType = 1;
    	//Tipo Verbale
        	$('#ReportType').hide();
        //Verbalizzante
        	$('#VerbalizationData').hide();
       	//Riferimento
			$('#Code').removeClass('frm_field_required');
       	//Notifica
       		$('#Notification').hide();
       		$('#NotificationType').removeClass('frm_field_required');
       		$('#f_violation').bootstrapValidator('removeField', $('#Code'));
    	//Spese Notifica
        	$('#NotificationFees').show();
    	//Spese addizionali ente
			$('#AdditionalFees').hide();
    	//Mancata contestazione
	   		if (!$('#ReasonId').hasClass('frm_field_required'))
				$('#ReasonId').addClass('frm_field_required');
    		$('#div_Reason').show();
    	//Dati Trasgressore
        	$('#TrespasserData').show();
        	$("[id^=NotificationDate_]").prop("disabled", true);
        	$("[id^=NotificationType_]").prop("disabled", true);
        	$("#OwnerAdditionalFee").prop("disabled", false);
        //Documentazione
        	callFileTree('public/_VIOLATION_/<?= $_SESSION['cityid']?>/');
    	//Action
        	$('#f_violation').attr('action', "mgmt_violation_add_exe.php" + str_GET_Parameter);
    	}
    });

    $('#InsertionType').trigger("change");

	//Assicura che il rilevatore sia modificabile solo sul primo articolo
	$('#ArticleNumber').change(function() {
		var ArticleNumber = parseInt($('#ArticleNumber').val());
		if (ArticleNumber > 1){
			$('#span_Detector').html($('#DetectorId option:selected').text());
			$('#span_Detector').show();
			$('#DetectorId').hide()
		} else {
			$('#span_Detector').hide();
			$('#DetectorId').show()
		}
	});

    //Aggiorna gli edditali quando viene cambiata l'ora
    $('#FineTime').focusout(function(){
  	    var articlesNumber = $('#ArticleNumber').val();
  	    for (i=1; i<=articlesNumber; i++){
  	    	$('#ArticleNumber').val(i);
  	    	chkArticle();
  	    }
  	});

    //Visualizza il documento relativo al checkbox quando si clicca
    setTimeout(function(){
        $('input[name="checked[]"]').change(function() {
        	if ($(this).is(":checked")){
            	$(this).siblings('a').click();
        	}
        });
    }, 500);

	//Carica gli accertatori del verbale precedente
	var a_Controllers = <?= json_encode($a_PreviousControllers) ?>;
	
	for (i=1; i<=<?= $n_TotalControllers?>; i++){
  		$('#AccertatoreNumber').val(i);
  		$('#ControllerCode_' + i).val(a_Controllers[i-1].ControllerId);
  		if (i < <?= $n_TotalControllers?>) $('#controller_down').click();
	}

	//Aziona il controllo sul riferimento al caricamento della pagina
    setTimeout(function(){
        $('#Code').change();
    }, 500);

    //Deduce il TipoAtto (Per bollettario)
//     $('#FineTypeId').change(function(){
//     	var Type = parseInt($('#FineTypeId').val());
//     	ActType = (Type > 3) ? 1 : 3;
//     });

    //Sezione "Altri trasgressori con questa targa", mostra o nasconde la ricerca trasgressori se almeno un trasgressore e selezionato
    $('#trespasser_content').on("change", 'input[name="checkbox[]"]', function(){
        $('input[name="checkbox[]"]').not(this).prop('checked', false);
        var ischecked = false
        $('input[name="checkbox[]"]').each(function () {
            ischecked = $(this).is(':checked');
            if (ischecked) {
            	return false;
            }
        });
        
        if(ischecked){
        	$('#TrespasserData').hide();
        } else {
        	$('#TrespasserData').show();
        }
    });

  	//(quando si va a caricare la pagina da mgmt_violationrent) Nasconde la selezione del tipo di trasgressore e imposta nolleggio
    if(getInsertionType == 4){
    	$('#DIV_TrespasserTypeSelect').hide();
    	$('#TrespasserType').val(3);
    	$('#TrespasserType').change();
    }
        
    
});

</script>

    <script>
    
        $(document).ready(function () {

        	var ForeignPlate = false;
        	var CountryV = $('#CountryId').val();
        	
        	if ($('#Code').val() != ""){
            	if ( CountryV != 'Z000' ) {
                	if (!ForeignPlate){
                        var ForeignCode = parseInt($('#Code').val());
                        $('#Code').val( ForeignCode - 1);
                    	ForeignPlate = true;
                    	$('#Receipt').hide();
                	}
                } else {
                	ForeignPlate = false;
                	var NationalCode = parseInt($('#Code').val());
                     $('#Code').val( NationalCode + 1 );
                     $('#Receipt').show();
                }
        	}
        	
			$('#CountryId').change(function() {
				CountryV = $('#CountryId option:selected').val();
				
				if ($('#Code').val() != ""){
	                if ( CountryV != 'Z000' ) {
	                	if (!ForeignPlate){
	                        var ForeignCode = parseInt($('#Code').val());
	                        $('#Code').val( ForeignCode - 1);
	                    	ForeignPlate = true;
	                    	$('#Receipt').hide();
	                	}
	                } else {
	                	ForeignPlate = false;
	               	    var NationalCode = parseInt($('#Code').val());
	                     $('#Code').val( NationalCode + 1 );
	                     $('#Receipt').show();
	                }
				}
			});
            
            $('#Code, #ControllerCode_1, #InsertionType, #FineTypeId, #Conrolli, #CountryId').change(function() {
                var FirstCode = $('#Code').val();
                var SendCode = $('#Code').val();
                var InputPrefix = $('#InputPrefix').val();
                var InputBlockNumber = $('#InputBlockNumber').val();
                var ControllerId = $('#ControllerCode_1').val();
                var EludiControlli = ($('#Conrolli').is(':checked')) ? true : false;
                                 
                if (FirstCode.indexOf('/') > 0)
                {
                    FirstCode = FirstCode.replace("/","");
                }
                var validCode = true;
                var FineTypeId = $('#FineTypeId').val();

                if ($('#Code').val() != ""){
                    if (validCode) {
                        $.ajax({
                            //NUOVO BOLLETTARIO
                            url: 'ajax/search_code.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {Code: SendCode,FineTypeId:FineTypeId, ActType:ActType, ControllerId:ControllerId, EludiControlli:EludiControlli, ForeignPlate: ForeignPlate, InputPrefix: InputPrefix, InputBlockNumber: InputBlockNumber},
                            success: function (data) {
                                console.log(data);
                                if (data.Result == "NO") {
                                    $("#save").prop("disabled", true);
                                    $("#span_code").show();
                                    $("#span_code small").html(data.Message);
                                    chkCode = false;
                                }
                                if (data.Result == "OK") {
                                    $("#save").prop("disabled", false);
                                    $("#span_code").hide();
                                    $("#span_code small").html('');
                                    chkCode = true;
                                }
                                
                                if (data.ShowReceipt){
                            		$('#Receipt').show();
                                 	$('#ReceiptNumber').html(data.ReceiptNumber);
                                 	$('#ReceiptPrefix').html(data.Prefix);
                                 	$('#ReceiptStart').html(data.StartNumber);
                                 	$('#ReceiptEnd').html(data.EndNumber);

									//var oldPrefix =  $('#InputPrefix').val();
                                 	if (data.Prefix != InputPrefix) {
                                    	$('#InputPrefix').val(data.Prefix);                                		
                                 	} 
                                 	
                                 	//var oldReceiptNumber = $('#InputBlockNumber').val();
                                 	if (data.ReceiptNumber != null && data.ReceiptNumber != "" 
                                     	&& data.ReceiptNumber != InputBlockNumber) {
                                 		$('#InputBlockNumber').val(data.ReceiptNumber);
                                 	} 
                                   
                                } else {
                            		$('#Receipt').hide();
                                 	$('#ReceiptNumber').html("");
                                 	$('#ReceiptPrefix').html("");
                                 	$('#ReceiptStart').html("");
                                 	$('#ReceiptEnd').html("");
                                 	/*
                                 	var prefix;
                                 	if (data.Prefix != null && data.Prefix != "") {
                                     	//prefix = "/"+data.Prefix;
                                     	//$('#InputPrefix').val(data.Prefix);
                                 		//$('#Prefix').html(data.Prefix).show();
                                 	} 
                                 	else {
                                 		//$('#InputPrefix').val("");
                                 		//$('#Prefix').html("").hide();
                                    }

                                 	var receiptNumber;
                                 	if (data.ReceiptNumber != null && data.ReceiptNumber != "") {
                                 		receiptNumber = "/"+data.ReceiptNumber;
                                     	//$('#InputBlockNumber').val(data.ReceiptNumber);
                                 		//$('#BlockNumber').html(data.ReceiptNumber).show();
                                 		
                                 	} 
                                 	else {
                                 		//$('#InputBlockNumber').val("");
                                 		//$('#BlockNumber').html("").hide();
                                    }
                                    */
                                }
                                
                                if (data.AlternateReceipt) {
                                    $("#span_controller").addClass("help-block");
                                    $("#span_controller").html("Bolletta associata ad accertatore diverso");
                                } else {
                                	$("#span_controller").removeClass("help-block");
                                    $("#span_controller").html('');
                                }
                            },
                            error: function (data) {
                                console.log(data);
                                alert("error");
                            }
                        });
                    }
                } else {
                    $("#save").prop("disabled", false);
                    $("#span_code").hide();
                    $("#span_code small").html('');
                    chkCode = true;
                }
            });
        });
       
        
    </script>
<?php
include(INC."/footer.php");
/*






*/