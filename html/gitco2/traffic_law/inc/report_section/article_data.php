<?php

$TimeTypeId = "";
$SpeedLimit = "";
$SpeedControl = "";
$Speed = "";
$TimeTLightFirst = "";
$TimeTLightSecond = "";
$AddittionalDescriptionIta = "";
$PrefectureFee = "";
$PrefectureDate = "";

if ($isPageUpdate){
    $TimeTypeId = $r_Fine['TimeTypeId'];
    $SpeedLimit = $r_FirstArticle['SpeedLimit'];
    $SpeedControl = $r_FirstArticle['SpeedControl'];
    $Speed = $r_FirstArticle['Speed'];
    $TimeTLightFirst = $r_FirstArticle['TimeTLightFirst'];
    $TimeTLightSecond = $r_FirstArticle['TimeTLightSecond'];
    if(isset($r_FineOwner))
        $AddittionalDescriptionIta = $r_FineOwner['AdditionalDescriptionIta'];
}

$str_ArticleBox = '';
$str_Caret = '
    <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none;" id="upart"></i>
    <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem; display:none;" id="downart"></i>
';

for($i=1; $i<=5; $i++){
    $Article    = ($i==1) ? $PreviousArticle   : "";
    $Letter     = ($i==1) ? $PreviousLetter : "";
    $Paragraph  = ($i==1) ? $PreviousParagraph : "";
    $ArticleDescription = ($i==1) ?  $PreviousArticleDescription : "";
    
    if ($isPageUpdate){
        $PrefectureFee = isset($a_ArticlesData[$i-1]) ? $a_ArticlesData[$i-1]['PrefectureFee'] : "";
        $PrefectureDate = isset($a_ArticlesData[$i-1]) ? DateOutDB($a_ArticlesData[$i-1]['PrefectureDate']) : "";
    }
    
    $str_ArticleBox .= '
        
        
           <div class="col-sm-12 alert alert-info" id="BoxArticle_'.$i.'" style="'.($i > 1 ? 'margin:0;padding:1rem;display:none' : 'margin:0;padding:1rem;').'">
            <div class="col-sm-12 text-center BoxRowLabel" style="background-color: #294A9C;">
                ARTICOLO '.$i.'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12">
                <div class="col-sm-1 BoxRowLabel">
                    Codice
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string article_change_comune" id="artcomune_'.$i.'" type="text" data-number="'.$i.'" style="width:6rem">
                </div>
              <div class="col-sm-1 BoxRowLabel">
                Articolo
              </div>
              <div class="col-sm-1 BoxRowCaption">
                    <button id="ArticleSearch_'.$i.'" type="button" data-container="body" data-toggle="tooltip" data-placement="top" title="Cerca articolo" class="btn btn-primary tooltip-r" style="margin:0;width:100%;height:100%;padding:0;">
                        <i class="glyphicon glyphicon-search" style="font-size:1.6rem;"></i>
                    </button>
                    <input type="hidden"  name="ArticleId_'.$i.'" id="ArticleId_'.$i.'">
              </div>
              <div class="col-sm-1 BoxRowCaption">
                  <input class="form-control frm_field_numeric article_change" value="'.$Article .'" type="text" name="id1_'.$i.'" id="id1_'.$i.'" data-number="'.$i.'" style="width:8rem">
              </div>
              <div class="col-sm-1   BoxRowLabel">
                    Comma
              </div>
              <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string article_change" value="'.$Letter.'" type="text" name="id2_'.$i.'" id="id2_'.$i.'" style="width:6rem">
              </div>
                        
                  <div class="col-sm-1 BoxRowLabel">
                    Lettera
              </div>
              <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string article_change" value="'.$Paragraph.'" type="text" name="id3_'.$i.'" id="id3_'.$i.'" style="width:5rem">
              </div>
              <div class="col-sm-2 BoxRowLabel">
                  Punti
              </div>
              <div class="col-sm-1 BoxRowCaption" id="LicensePoint_'.$i.'">
              </div>
              <div class="col-sm-1 BoxRowCaption" id="YoungLicensePoint_'.$i.'" style="display:none;">
              </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div id="DayNumber_'.$i.'" style="display: none;">
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                  Day Number
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    <input type="text" name="DayNumber_180_'.$i.'" class="form-control frm_field_string" id="DayNumber_180_'.$i.'" style="width: 10rem;">
                </div>
                <div class="clean_row HSpace4"></div>
            </div>
                        
            <div class="col-sm-12" style="min-height:8rem; position: relative;">
                <div class="col-sm-12 BoxRowLabel" style="min-height:8rem;height:auto;background-color: rgb(40, 114, 150);">
                    <i class="fa fa-pencil-square-o" id="EditArticle_'.$i.'" style="position:absolute; top:1px;right:15px;font-size: 2rem; display:none"></i>
                    <div id="ArticleText_'.$i.'" class="col-sm-11" style="position:absolute; display: none; top:5px;left:width:60rem; height:7rem">
                        <textarea class="form-control frm_field_string" id="textarea_'.$i.'" name="ArticleText_'.$i.'" style="background-color:#C7EBE0;  color:#294A9C; top:1px;left:1px; padding-left: 2rem;height:7rem">' .$ArticleDescription .'</textarea>
                    </div>
                    <span class="col-sm-11" id="span_Article_'.$i.'" style="position:relative;font-size:1rem;"></span>
                    '. $str_Caret .'
                </div>
                        
            </div>';
    
    if($i==1) $str_ArticleBox.= '
    <div class="clean_row HSpace4"></div>
    <div id="AdditionalSanction_'.$i.'" class="col-sm-12" style="display:none;">
		<div id="AdditionalSanctionWarning_'.$i.'" class="table_caption_H col-sm-12 alert-warning" style="height:auto; display:none;">
            <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;NOTA: la sanzione accessoria è di tipo variabile, è necessario variare il testo dall\'originale.
        </div>
        <div class="col-sm-3 BoxRowLabel" style="height:6.4rem">
            Sanzione accessoria
        </div>
        <div class="BoxRowCaption col-sm-9" style="min-height:6.4rem;height:auto">
            <span id="AdditionalSanctionSelect_'.$i.'"></span>
        	<textarea data-articlenumber='.$i.' class="form-control frm_field_string txt-warning additionalsanctionfield" name="AdditionalSanctionInputText_'.$i.'" id="AdditionalSanctionInputText_'.$i.'" style="height: 5.8rem;margin-left: 0;resize: none;"></textarea>
        </div>
    </div>';
    
    
    $str_ArticleBox.='
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="col-sm-3 BoxRowLabel">
        Min edittale
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input type="hidden" name="Fee_'.$i.'" id="Fee_'.$i.'">
            <span id="span_Fee_'.$i.'"></span>
        </div>
        <div class="col-sm-3 BoxRowLabel">
            Max edittale
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input type="hidden" name="MaxFee_'.$i.'" id="MaxFee_'.$i.'">
            <span id="span_MaxFee_'.$i.'"></span>
        </div>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12" id="DIV_Prefecture_'.$i.'" style="display:none;">
        <div class="col-sm-3 BoxRowLabel">
            Importo prefettura
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input value="'.$PrefectureFee.'" class="form-control frm_field_currency'.($isPageUpdate ? ' frm_field_required' : '').'" type="text" name="PrefectureFee_'.$i.'" id="PrefectureFee_'.$i.'">
        </div>
        <div class="col-sm-3 BoxRowLabel">
            Data prefettura
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <input value="'.$PrefectureDate.'" type="text" class="form-control frm_field_date'.($isPageUpdate ? ' frm_field_required' : '').'" name="PrefectureDate_'.$i.'" id="PrefectureDate_'.$i.'">
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';
    $str_CssDisplay = 'style="display: none;"';
    $str_Caret = '';
}





$str_Article_Data ='
        <div class="clean_row HSpace4"></div>
 			<div class="col-sm-12">
        		<div class="col-sm-2 BoxRowLabel">
                    Rilevatore
				</div>
			<div class="col-sm-6 BoxRowCaption">
                '. CreateSelectQuery("SELECT Id,CONCAT(Title".LAN.", ' (Matr. ', Number, ')') AS Title FROM Detector WHERE CityId='".$_SESSION['cityid']."' ORDER BY Title ASC", 'DetectorId', 'Id', 'Title', null, false) .'
                <span id="span_Detector"></span>
            </div>
        	<div class="col-sm-2 BoxRowLabel">
                Ora
                <i data-toggle="tooltip" data-placement="top" data-container="body" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo. L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
       		</div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("TimeType","Disabled=0","Id","TimeTypeId","Id","Title",$TimeTypeId,true) .'
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12" id="DIV_Title_Speed" style="display:none;">
            <div class="col-sm-12 BoxRowLabel" style="text-align:center;background-color: #294A9C;">
                VELOCITÀ
            </div>
            <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-12" id="DIV_Speed" style="display:none;">
            <div class="col-sm-2 BoxRowLabel">
                Limite
            </div>
            <div class="col-sm-2 BoxRowCaption" id="">
                <input value="'.$SpeedLimit.'" class="form-control frm_field_numeric" type="text" name="SpeedLimit" id="SpeedLimit" style="width:6rem" readonly>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Rilevata
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input value="'.$SpeedControl.'" class="form-control frm_field_numeric" type="text" name="SpeedControl" id="SpeedControl" style="width:6rem" readonly>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Effettiva
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="hidden" name="Speed" id="Speed" value="'.$Speed.'" style="width:6rem">
                <span id="span_Speed">'.$Speed.'</span>
            </div>
        </div>
                    
        <div class="col-sm-12" id="DIV_Title_TLight" style="display:none;">
            <div class="col-sm-12 BoxRowLabel" style="text-align:center;background-color: #294A9C;">
                SEMAFORO
            </div>
            <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-12" id="DIV_TLight" style="display:none;">
            <div class="col-sm-4 BoxRowLabel">
                Primo fotogramma
            </div>
            <div class="col-sm-2 BoxRowCaption" id="">
                <input value="'.$TimeTLightFirst.'" class="form-control frm_field_numeric" type="text" name="TimeTLightFirst" id="TimeTLightFirst" style="width:6rem">
            </div>
            <div class="col-sm-4 BoxRowLabel">
                Secondo fotogramma
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input value="'.$TimeTLightSecond.'" class="form-control frm_field_numeric" type="text" name="TimeTLightSecond" id="TimeTLightSecond" style="width:6rem">
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
                '. $str_ArticleBox .'
        <div class="col-sm-12" style="margin-bottom:10px;">
    		<div class="col-sm-10 BoxRow" style="padding-left: 0.4rem;">
    			Punti totali
    		</div>
    		<div class="col-sm-2 BoxRow" style="background-color: #294A9C;border-left: 1px solid white;text-align:center;">
    			<span id="span_TotalPoints"></span>
    		</div>
    	</div>
				';

?>

<script>

function populateAdditionalSanction(n, usetype = null, progressive = null, text = null, savedText = null){
    switch (usetype) {
    	case 'non_prevista':
    		$('#AdditionalSanction_'+n+', #AdditionalSanctionWarning_'+n).hide();
        	$('#AdditionalSanctionSelect_'+n).html('');
        	$('#AdditionalSanctionInputText_'+n).val('');
          	break;
        case 'fissa':
        	$('#AdditionalSanction_'+n).show();
        	$('#AdditionalSanctionWarning_'+n).hide();
        	$('#AdditionalSanctionSelect_'+n).html(progressive+' - '+text).show();
        	$('#AdditionalSanctionInputText_'+n).val('').hide();
            break;
        case 'variabile':
        	$('#AdditionalSanction_'+n+', #AdditionalSanctionWarning_'+n).show();
        	$('#AdditionalSanctionSelect_'+n).html(text).hide();
        	$('#AdditionalSanctionInputText_'+n).val(text).show();
          	break;
        default:
        	$('#AdditionalSanction_'+n+', #AdditionalSanctionWarning_'+n).hide();
        	$('#AdditionalSanctionSelect_'+n).html('');
        	$('#AdditionalSanctionInputText_'+n).val('');
  	}

  	if(savedText){
  		$('#AdditionalSanctionSelect_'+n+', #AdditionalSanctionWarning_'+n).hide();
    	$('#AdditionalSanctionInputText_'+n).val(savedText).show();
  	}
}

function chkArticle(changeReason = true, fineid = 0) {

	var ArticleNumber = $('#ArticleNumber').val();
    var id1=$('#id1_'+ArticleNumber).val();
    var id2=$('#id2_'+ArticleNumber).val();
    var id3=$('#id3_'+ArticleNumber).val();

    var FineTypeId = $('#FineTypeId').val();
    var FineTime = $('#FineTime').val();


    var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();


    if(id1!='' && (id2!='' || id3!='')){

        var RuleTypeId = $('#RuleTypeId').val();

        var request = $.ajax({
            url: "ajax/search_article.php",
            dataType: 'json',
            type: "post",
            cache: false,
            data: "RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3+"&FineTime=" + FineTime+"&FineId="+fineid
        });

        request.done(function (response){
            $("#downart").show();
            if (response.NonExist ==1){
                //alert(response.Description);
                $('#span_Article_'+ArticleNumber).html(response.Description).css("background-color","red");
                $("#save").prop("disabled", true);
            }else{
                $('#Edit_Contestazione').show();
                $('#span_Article_'+ArticleNumber).html(response.Description);
                $('#span_Article_'+ArticleNumber).css("background-color","");
                $("#save").prop("disabled", false);
            }
            if(changeReason){
            	$('#ReasonId').val(response.reasonId);
            }


            if(response.Result==1){
                $('#ArticleId_'+ArticleNumber).val(response.ArticleId);

                populateAdditionalSanction(
                	ArticleNumber,
                	response.UseAdditionalSanction,
                	response.AdditionalSanctionProgressive, 
                	response.AdditionalSanctionSelect,
                	response.SavedAdditionalSanction);

                if(ArticleNumber==1){
                    $('#ViolationTypeId').val(response.ViolationTypeId);
                    $('#span_ViolationTitle').html(response.ViolationTitle);
                }

                if(response.PresentationDocument==0){
                    $('#DIV_DayNumber_180').hide();
                    $("#DayNumber_180").val('0');
                } else {
                    $('#DIV_DayNumber_180').show();
                }
                //Prefettura
                if(response.PrefectureFixed==1){
                    $('#DIV_Prefecture_'+ArticleNumber).show();
                } else {
                	$('#DIV_Prefecture_'+ArticleNumber).hide();
                	$('#PrefectureFee_'+ArticleNumber).val('');
                	$('#PrefectureDate_'+ArticleNumber).val('');
                }
                //
                $('#artcomune_'+ArticleNumber).val(response.ArtComunali);
                $('#ArticleId_'+ArticleNumber).attr('fee', response.Fee);
                $('#ArticleId_'+ArticleNumber).attr('addMass', response.AddMass);
                $('#ArticleId_'+ArticleNumber).attr('maxFee', response.MaxFee);
                $('#ArticleId_'+ArticleNumber).attr('desc', response.Description);
                $('#YoungLicensePoint_'+ArticleNumber).html(response.YoungLicensePoint);
                $('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);




                if(FineTypeId==4){
                    $('#Fee_'+ArticleNumber).prop('type', 'text').val(response.Fee);
                    $('#MaxFee_'+ArticleNumber).prop('type', 'text').val(response.MaxFee);
                    $('#EditArticle_'+ArticleNumber).show();
                }else{
                    $('#EditArticle_'+ArticleNumber).show();
                    $('#Fee_'+ArticleNumber).val(response.Fee);
                    $('#span_Fee_'+ArticleNumber).html(response.Fee);
                    $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                    $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);
                }

                checkFee();
                sumPoints();

            }


        });
        request.fail(function (jqXHR, textStatus){
            $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
        });

        //event.preventDefault();
    }

}

function chkSpeed() {
	var ArticleNumber = $('#ArticleNumber').val();
    var DetectorId = $('#DetectorId').val();
    var SpeedLimit=$('#SpeedLimit').val();
    var SpeedControl=$('#SpeedControl').val();
    var FineTime = $('#FineTime').val();
    var RuleTypeId = $('#RuleTypeId').val();
    var NotificationType = $('#NotificationType').val();

    var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();

    if(SpeedLimit!='' && SpeedControl!=''){

        var request = $.ajax({
            url: "ajax/search_article.php",
            dataType: 'json',
            type: "post",
            cache: false,
            data: "RuleTypeId="+RuleTypeId+"&DetectorId="+DetectorId+"&SpeedLimit=" + SpeedLimit + "&SpeedControl=" + SpeedControl +"&FineTime=" + FineTime
        });

        request.done(function (response){

            $("#downart").show();
            $('#span_Article_'+ArticleNumber).html(response.Description);
            $('#ReasonId').html(response.ReasonDescription);
            $('#ReasonId_Second').val(response.reasonId);
            $('#ReasonId').trigger("change");
            $('#EditArticle_'+ArticleNumber).show();
            $('#Edit_Contestazione').show();
            if(response.Result==1){
                $('#ArticleId_'+ArticleNumber).val(response.ArticleId);

                populateAdditionalSanction(
                	ArticleNumber,
                	response.UseAdditionalSanction,
                	response.AdditionalSanctionProgressive, 
                	response.AdditionalSanctionSelect);
                
                $('#Speed').val(response.Speed);
                $('#span_Speed').html(response.Speed);

                $('#id1_'+ArticleNumber).val(response.Id1);
                $('#id2_'+ArticleNumber).val(response.Id2);
                $('#id3_'+ArticleNumber).val(response.Id3);
                $('#artcomune_'+ArticleNumber).val(response.ArtComunali);

                if(ArticleNumber==1){
                    $('#ViolationTypeId').val(response.ViolationTypeId);
                    $('#span_ViolationTitle').html(response.ViolationTitle);
                }

                $('#Fee_'+ArticleNumber).val(response.Fee);
                $('#span_Fee_'+ArticleNumber).html(response.Fee);
                $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);
                $('#YoungLicensePoint_'+ArticleNumber).html(response.YoungLicensePoint);
                $('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                var totFee = 0;
                for(var i=1;i<=ArticleNumber;i++){
                	var PrefectureFee = parseFloat($('#PrefectureFee_' + i).val());

                	//Se l'importo prefettura viene sommato al totale invece che l'edittale
                	if (PrefectureFee > 0 && PrefectureFee != "")
                		totFee += PrefectureFee;
                	else
                    	totFee += Number($('#Fee_'+i).val());
                }
                totFee += Number(totCharge);

                $('#span_TotalFee').html(totFee.toFixed(2));

                sumPoints();
            }else{
                if (response.NonExist ==1){

                    //alert(response.Description);
                    $('#span_Article_'+ArticleNumber).html(response.Description).css("background-color","red");
                    $("#save").prop("disabled", true);
                }
            }


        });
        request.fail(function (jqXHR, textStatus){
            $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
        });
        //event.preventDefault();
    }
}

    $('document').ready(function () {
        var ArticleNumber = $('#ArticleNumber').val();

        $("[id^=ArticleSearch_]").click(function () {

            var id1=$('#id1_'+ArticleNumber).val();
            var RuleTypeId = $('#RuleTypeId').val();

            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, RuleTypeId: RuleTypeId, ArticleCounter: ArticleNumber},
                success: function (data) {

                    $('#Div_Article_Content').html(data.Article);
                    $('#BoxArticleSearch').fadeIn('slow');

                },
                error: function (data) {
                	console.log(data);
                	alert(data.responseText);
                }
            });

        });

        $('#SpeedLimit').focusout(chkSpeed);
        $('#SpeedControl').focusout(chkSpeed);




        $('#DetectorId').change(function() {
            var DetectorId = $(this).val();
            var CountryId = $("#CountryId").val();

            if(DetectorId!='') {
                $.ajax({
                    url: 'ajax/search_detector.php',
                    type: 'POST',
                    cache: false,
                    data: {DetectorId: DetectorId},
                    dataType:'json',
                    success: function (data) {
                        //console.log(data)

                        if (data.UploadNumber ==2){
                        	$("#UploadNumber").val(data.UploadNumber);
                        	$("#UploadNumber").trigger("change");
                        } else {
                        	$("#UploadNumber").val('');
                        	$("#UploadNumber").trigger("change");
                        }

                        if (CountryId == "Z000"){
                            if ((data.Ratification).trim() == "") alert("Omologazione assente");
                        }

                        if (data.DetectorTypeId == 1) {
                            $('#DIV_Title_Speed').show();
                            $('#DIV_Speed').show();

                            $("#SpeedLimit").prop("readonly", false);
                            $("#SpeedControl").prop("readonly", false);

                            $("#id1_"+ArticleNumber).val('').prop("disabled", true);
                            $("#id2_"+ArticleNumber).val('').prop("disabled", true);
                            $("#id3_"+ArticleNumber).val('').prop("disabled", true);

                            $('#DIV_Title_TLight').hide();
                            $('#DIV_TLight').hide();

                            $("#TimeTLightFirst").val('');
                            $("#TimeTLightSecond").val('');

                            $('#span_Article_'+ArticleNumber).html('');

                        }else if(data.DetectorTypeId == 2){
                            $('#DIV_Title_TLight').show();
                            $('#DIV_TLight').show();

                            $("#id1_"+ArticleNumber).val('146').prop("disabled", true);
                            $("#id2_"+ArticleNumber).val('3').prop("disabled", false);
                            $("#id3_"+ArticleNumber).val('').prop("disabled", false);

                            chkArticle();



                            $('#DIV_Title_Speed').hide();
                            $('#DIV_Speed').hide();

                            $("#SpeedLimit").prop("readonly", true);
                            $("#SpeedControl").prop("readonly", true);
                            $("#SpeedLimit").val('');
                            $("#SpeedControl").val('');
                        }

                        else {
                            $('#DIV_Title_Speed').hide();
                            $('#DIV_Speed').hide();

                            $("#SpeedLimit").prop("readonly", true);
                            $("#SpeedControl").prop("readonly", true);
                            $("#SpeedLimit").val('');
                            $("#SpeedControl").val('');

                            $("#id1_"+ArticleNumber).prop("disabled", false);
                            $("#id2_"+ArticleNumber).prop("disabled", false);
                            $("#id3_"+ArticleNumber).prop("disabled", false);

                            $('#DIV_Title_TLight').hide();
                            $('#DIV_TLight').hide();
                            $("#TimeTLightFirst").val('');
                            $("#TimeTLightSecond").val('');

                            $('#span_Article_'+ArticleNumber).html('');
                        }

                    },
                    error: function (data) {
    	                console.log(data);
    	                alert("error: " + data.responseText);
                    }

                });
            }else{
                $("#SpeedLimit").prop("readonly", true);
                $("#SpeedControl").prop("readonly", true);
                $("#SpeedLimit").val('');
                $("#SpeedControl").val('');

                $("#id1_"+ArticleNumber).val('').prop("disabled", false);
                $("#id2_"+ArticleNumber).val('').prop("disabled", false);
                $("#id3_"+ArticleNumber).val('').prop("disabled", false);


                $('#DIV_Title_TLight').hide();
                $('#DIV_Title_Speed').hide();
                $('#DIV_TLight').hide();
                $("#TimeTLightFirst").val('');
                $("#TimeTLightSecond").val('');

                $('#span_Article_'+ArticleNumber).html('');

                $('#Fee_'+ArticleNumber).val('');
                $('#span_Fee_'+ArticleNumber).html('');
                $('#MaxFee_'+ArticleNumber).val('');
                $('#span_MaxFee_'+ArticleNumber).html('');
                $('#YoungLicensePoint_'+ArticleNumber).html('');
                $('#LicensePoint_'+ArticleNumber).html('');

                populateAdditionalSanction(ArticleNumber);

                sumPoints();
            }
        });

        $('#CustomerAdditionalFee').change(function(){
            var CustomerAdditionalFee = parseFloat($('#CustomerAdditionalFee').val());

            var totFee = 0;
            for(var i=1;i<=ArticleNumber;i++){
            	var PrefectureFee = parseFloat($('#PrefectureFee_' + i).val());

            	//Se l'importo prefettura viene sommato al totale invece che l'edittale
            	if (PrefectureFee > 0 && PrefectureFee != "")
            		totFee += PrefectureFee;
            	else
                	totFee += Number($('#Fee_'+i).val());
            }
            totFee += Number(CustomerAdditionalFee);

            $('#span_TotalFee').html(totFee.toFixed(2));
        });

        //Attiva il calcolo quando si inserisce un importo prefettura e rende i campi obbligatori
        $("[id^=PrefectureFee_], [id^=PrefectureDate_]").change(function(){
//         	var id = $(this).attr('id');
//     		var nid = id.slice(id.length - 1);
//     		var PrefectureFee = parseFloat($('#PrefectureFee_' + nid).val()) || 0;
//     		var PrefectureDate = $('#PrefectureDate_' + nid).val();

//     		console.log(nid,PrefectureFee,PrefectureDate);

//     		$('#f_violation').data('bootstrapValidator').resetForm();

//     		if (PrefectureFee != "" || PrefectureDate != ""){
// 				$("#PrefectureFee_"+nid).addClass("frm_field_required");
// 				$("#PrefectureDate_"+nid).addClass("frm_field_required");
// 				$("#f_violation").bootstrapValidator('enableFieldValidators', $('#PrefectureFee_'+nid), 'notEmpty', true);
// 				$("#f_violation").bootstrapValidator('enableFieldValidators', $('#PrefectureDate_'+nid), 'notEmpty', true);
//     		} else {
// 				$("#PrefectureFee_"+nid).removeClass("frm_field_required");
// 				$("#PrefectureDate_"+nid).removeClass("frm_field_required");
// 				$("#f_violation").bootstrapValidator('enableFieldValidators', $('#PrefectureFee_'+nid), 'notEmpty', false);
// 				$("#f_violation").bootstrapValidator('enableFieldValidators', $('#PrefectureDate_'+nid), 'notEmpty', false);
//     		}
            checkFee();
        });

        /////article comune
        $(document).on('change','.article_change_comune',function () {
            var search = $("#artcomune_"+ArticleNumber).val();
            var index = $(this).data('number');
            var FineTime = $('#FineTime').val();
            if(search!=''){
                var RuleTypeId = $('#RuleTypeId').val();
                var request = $.ajax({
                    url: "ajax/search_article.php",
                    dataType: 'json',
                    type: "POST",
                    cache: false,
                    data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&ArticleCounter="+index+"&search="+search
                });

                request.done(function (response){

                    $("#downart").show();
                    $('#span_Article_'+index).html(response.Description);

                    if(index==1){
                        $('#ReasonId').val(response.ReasonId);
                    }

                    $('#EditArticle_'+index).show();
                    $('#Edit_Contestazione').show();
                    if(response.Result==0){

                        $('#span_Fee_'+index).html('');
                        $('#span_MaxFee_'+index).html('');

                    }
                    if(response.Result==1){

                        $('#id1_'+index).val(response.Id1);
                        $('#id2_'+index).val(response.Id2);
                        $('#id3_'+index).val(response.Id3);
                        $('#ArticleId_'+index).val(response.ArticleId);

                        populateAdditionalSanction(
                        	ArticleNumber,
                        	response.UseAdditionalSanction,
                        	response.AdditionalSanctionProgressive, 
                        	response.AdditionalSanctionSelect);

                        if(index==1){
                            $('#ViolationTypeId').val(response.ViolationTypeId);
                            $('#span_ViolationTitle').html(response.ViolationTitle);
                        }

                        $('#Fee_'+index).val(response.Fee);
                        $('#span_Fee_'+index).html(response.Fee);
                        $('#MaxFee_'+index).val(response.MaxFee);
                        $('#span_MaxFee_'+index).html(response.MaxFee);

                        $('#ArticleId_'+index).attr('fee', response.Fee);
                        $('#ArticleId_'+index).attr('addMass', response.AddMass);
                        $('#ArticleId_'+index).attr('maxFee', response.MaxFee);
                        $('#ArticleId_'+index).attr('desc', response.Description);
                        $('#YoungLicensePoint_'+ArticleNumber).html(response.YoungLicensePoint);
                        $('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                        checkFee();
                        sumPoints();
                    }
                });
                request.fail(function (jqXHR, textStatus){
                    $('#span_Article_'+index).html("ERR: Prova con il diferente input");
                });
            }
        });

        $('.article_change').focusout(chkArticle);



        $(document).on('click','.fa-pencil-square-o',function () {
            var get_ArticleNumber_text = $(this).attr('id');
            Article = get_ArticleNumber_text.split("_");
            ArticleNumber_Text = Article[1];
            var text = $('#span_Article_'+ArticleNumber_Text).text();

            if($('#ArticleText_'+ArticleNumber_Text).is(":visible")) {
                $('#textarea_'+ArticleNumber_Text).val('');
                $('#ArticleText_'+ArticleNumber_Text).hide();
                $('#span_Article_'+ArticleNumber_Text).html($('#ArticleId_'+ArticleNumber_Text).attr('desc'));
            }
            else {
                $('#ArticleText_'+ArticleNumber_Text).show();
                $('#span_Article_'+ArticleNumber_Text).html('');
                $('#textarea_'+ArticleNumber_Text).val(text);
            }
        });


        $('#downart').click(function () {

            ArticleNumber++;
            $('#ArticleNumber').change();
            $("#BoxArticle_"+ArticleNumber).show();


            $("#upart").show();
            $(this).hide();
            if(ArticleNumber==5){
                $("#downart").hide();
            }
            $('#ArticleNumber').val(ArticleNumber);
            $('#ArticleNumber').change();
            checkFee();
            sumPoints();

        });


        $('#upart').click(function () {

            var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();

            $("#BoxArticle_"+ArticleNumber).hide();
            $("#PrefectureFee_"+ArticleNumber).val('');
            ArticleNumber--;

            if(ArticleNumber==1){
                $("#upart").hide();
                $("#downart").show();
            }else{
                $("#downart").show();
            }
            $('#ArticleNumber').val(ArticleNumber);
            $('#ArticleNumber').change();

            var totFee = 0;
            for(var i=1;i<=ArticleNumber;i++){
            	var PrefectureFee = parseFloat($('#PrefectureFee_' + i).val());

            	//Se l'importo prefettura viene sommato al totale invece che l'edittale
            	if (PrefectureFee > 0 && PrefectureFee != "")
            		totFee += PrefectureFee;
            	else
                	totFee += Number($('#Fee_'+i).val());
            }
            totFee += Number(CustomerAdditionalFee);
            $('#span_TotalFee').html(totFee.toFixed(2));

            sumPoints();

        });

        $("#ArticleSearch").click(function () {

            var id1=$('#id1_'+ArticleNumber).val();
            var RuleTypeId = $('#RuleTypeId').val();

            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, RuleTypeId: RuleTypeId},
                success: function (data) {

                    $('#Div_Article_Content').html(data.Article);
                    $('#BoxArticleSearch').fadeIn('slow');

                }
            });

        });

    });

</script>