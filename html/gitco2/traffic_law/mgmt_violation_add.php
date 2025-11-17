<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

/*
$aDocViolation = glob(ROOT.'/public/*.jpg');

$a_doc = explode("traffic_law/",$aDocViolation[0]);

$First_Doc = $a_doc[1];
*/
if (isset($_GET['back']) && $_GET['back']=='true'){
    $str_BackPage = 'mgmt_violation.php?PageTitle=Preinserimenti/Gestione%20dati';
}

$charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);

$_form_type_id = 1;
$TotalChargeForeign = ($charge_row['ForeignTotalFee']>0) ? $charge_row['ForeignTotalFee'] : $charge_row['ForeignNotificationFee'] + $charge_row['ForeignResearchFee'];

$TotalChargeNational = ($charge_row['NationalTotalFee']>0) ? $charge_row['NationalTotalFee'] : $charge_row['NationalNotificationFee'] + $charge_row['NationalResearchFee'];
$title_lang = 'Title'.LAN;
$today_date = Date('Y-m-d');

$sessionYear = $_SESSION['year'];
$select = $rs->SelectQuery("SELECT Fine.Id as FineId, Fine.VehicleTypeId,Fine.FineDate,Fine.ControllerId,Fine.Address,VehicleType.$title_lang,Controller.Name,Controller.Id,Controller.Code as cont_code from Fine 
left join VehicleType ON Fine.VehicleTypeId = VehicleType.Id JOIN Controller ON Fine.ControllerId = Controller.Id 
WHERE Fine.RegDate='$today_date' and UserId ='".$_SESSION['username']."' and Fine.CityId ='".$_SESSION['cityid']."' and Fine.ProtocolYear = '$sessionYear' order by Fine.Id DESC limit 1");

$article = "";
$comma = "";
$letter ="";
$nr = mysqli_num_rows($select);
if ($nr==1){
    while($row = mysqli_fetch_array($select)){
        $fine_date = $row['FineDate'];
        $fineId = $row['FineId'];
        $controller_id = $row['ControllerId'];
        $cont_name = $row['Name'];
        $adress = $row['Address'];
        $vehicle = $row['TitleIta'];
        $cont_code = $row['cont_code'];
        $vehicle_id = $row['VehicleTypeId'];
        $controller_name = $cont_code .' '.$cont_name;


    }
}

if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    $class = $answer =='Inserito con successo'?"alert-success":"alert-warning";
    echo "<div class='alert $class message'>$answer</div>";

    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 6000);
    </script>
    <?php
}
echo "<div class='check' id='test'></div>";
include './inc/page/VerbaleForm/Accertatore.php';

//

include './inc/page/VerbaleForm/Articolo.php';


$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

if($n_Code>0){
    $str_Locality='
                   
                        <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select name="Locality" class="form-control frm_field_required" style="width:10rem;">
                            <option></option>
               
    ';
    
    while($r_Row = mysqli_fetch_array($rs_Row)){
        $str_Locality.='<option value="'.$r_Row['Id'].'">'.$r_Row['Title'].'</option>';

    }

    $str_Locality.='        </select>
                        </div>
                    ';
    
}else{
    $str_Locality='    <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                        ' . $_SESSION['citytitle'] . '
                        </div>

    ';
}






$str_Toponym ='
        <div class="col-sm-2 BoxRowLabel">
            Toponimo
        </div>
        <div class="col-sm-3 BoxRowCaption">';

$str_Toponym .= CreateSelect('sarida.Toponym', '1=1', 'Id', 'Toponym', 'Title', 'Title', 0, false);
$str_Toponym .='</div>';
$str_Toponym .='
        <div class="col-sm-3 BoxRowLabel">
            Seleziona Strada
        </div>
        <div class="col-sm-4 BoxRowCaption">';

$str_Toponym .= CreateSelect('StreetType', '1=1', 'Id', 'StreetType', 'Title', 'Title', 0, false);
$str_Toponym .= '</div>';


$str_out .= '
        <form name="f_violation" id="f_violation" method="post" action="mgmt_violation_add_exe.php">
    	<input type="hidden" name="P" value="' . $str_CurrentPage . '">
        <input type="hidden" name="ArticleNumber" id="ArticleNumber" value="1">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="1">
        <input type="hidden" name="UploadNumber" id="UploadNumber" value="">
        <input type="hidden" id="art_1" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_2" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_3" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_4" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_5" fee="" maxFee="" addMass="">
    	<div class="row-fluid">
        	<div class="col-sm-6">';

                include './inc/page/VerbaleForm/section1.php';
                include './inc/page/VerbaleForm/SpeseNotifica.php';
                include './inc/page/VerbaleForm/Infrazione.php';

                $str_out .='
  				<div class="col-sm-12 BoxRowLabel">
                    Dati Trasgressore
                </div>
                <div class="clean_row HSpace4"></div>
                <div style="height:12rem;">
                    <input type="hidden" name="Genre" id="Genre">    
                    <div id="Div_Trespasser_Company" style="display:none">
                        <div class="col-sm-3 BoxRowLabel">
                            Ragione sociale
                        </div>
                        <div class="col-sm-9 BoxRowCaption">
                            <input name="CompanyName" id="CompanyName" type="text" style="width:40rem">
                        </div>
                    </div>
                    <div id="Div_Trespasser_Person" style="display:none">
                        <div class="col-sm-2 BoxRowLabel">
                            Cognome
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_str" type="text" name="Surname" id="Surname" style="width:20rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nome
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_str" type="text" name="Name" id="Name" style="width:20rem">
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data Nascita
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:20rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Luogo Nascita
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            <input class="form-control frm_field_str" type="text" name="BornPlace" id="BornPlace" style="width:35rem">
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12 BoxRow">
                            <div class="col-sm-3 BoxRowLabel">
                                Codice fiscale
                            </div>
                            <div class="col-sm-9 BoxRowCaption">
                                <input type="text" name="TaxCode" id="TaxCode" style="width:30rem">
                            </div>
                        </div>                                            
                    </div>
                    <div id="Div_Trespasser_Address" style="display:none">
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-3 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-9 BoxRowCaption">
                            <input class="form-control" type="text" name="TrespasserAddress" id="TrespasserAddress" style="width:41rem">
                        </div>
                    
                        <div class="clean_row HSpace4"></div>
        
                        <div class="col-sm-1 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input type="text" name="ZIP" style="width:8rem">
                        </div>
        
                        <div class="col-sm-2 BoxRowLabel">
                            Città
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control" type="text" name="City" id="City" style="width:30rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Provincia
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input class="form-control" type="text" name="Province" id="Province" style="width:12rem">
                        </div>
                    </div>
                </div>        

  			</div>';
            include './inc/page/VerbaleForm/DOCUMENTAZIONE.php';
            include './inc/page/VerbaleForm/salva.php';

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
';


echo $str_out;
?>

    <script type="text/javascript">

        $('#Toponym').change(function() {
            var type = $(this).val();
            var address = $('#Address').val();
            if(address=='') $('#Address').val(type);
            else $('#Address').val(address + ' ' + type);

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').show();
                    $('#Address_List').html(data);
                }
            });
        });
        $('#StreetType').change(function() {
            var type = $(this).val();
            var address = $('#Address').val();
            if(address=='') $('#Address').val(type);
            else $('#Address').val(address + ' ' + type);

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').show();
                    $('#Address_List').html(data);
                }
            });
        });

        $('.EditAdditionalSanction').click(function () {
            var get_ArticleNumber_text = $(this).attr('id');
            Article = get_ArticleNumber_text.split("_");
            ArticleNumber_Text = Article[1];

            if($('#AdditionalSanction_'+ArticleNumber_Text).is(":visible")) {
                $('#AdditionalSanction_'+ArticleNumber_Text).hide();
                $('#AdditionalSanctionInput_'+ArticleNumber_Text).show();
            } else {
                $('#AdditionalSanction_'+ArticleNumber_Text).show();
                $('#AdditionalSanctionInput_'+ArticleNumber_Text).hide();
                $('#AdditionalSanctionInputText_'+ArticleNumber_Text).val('');
            }
        });
        var addMass = <?=MASS?>;
        $('#VehicleMass').change(function() {
            checkFee();
        });

        function checkFee() {
            var mass = $('#VehicleMass').val();
            var sum = 0;
            for (var i = 1; i <= 5; i++) {

                var Fee = parseFloat($('#art_' + i).attr('fee'));
                var MaxFee = parseFloat($('#art_' + i).attr('maxFee'));

                if ($('#art_' + i).attr('addMass') == 1) {
                    if(mass<addMass){
                        $('#Fee_'+i).val(Fee.toFixed(2));
                        $('#MaxFee_'+i).val(MaxFee.toFixed(2));
                        sum+=Fee;
                        $('#span_Fee_'+i).html(Fee.toFixed(2));
                        $('#span_MaxFee_'+i).html(MaxFee.toFixed(2));
                    } else {
                        $('#Fee_'+i).val((Fee*2).toFixed(2));
                        $('#MaxFee_'+i).val((MaxFee*2).toFixed(2));
                        sum+=Fee*2;
                        $('#span_Fee_'+i).html((Fee*2).toFixed(2));
                        $('#span_MaxFee_'+i).html((MaxFee*2).toFixed(2));
                    }
                }
                if ($('#art_' + i).attr('addMass') == '0') {
                    sum+=Fee;
                }
            }
            sum += parseFloat($('#span_TotalCharge').html());
            $('#span_TotalFee').html(sum.toFixed(2));
        }

    </script>




<script type="text/javascript">
    var controller = true;
    var chkCode = true;
    var giaPresente = false;
    var chkCode = true;
    var namespan;

    var totCharge = <?= $TotalChargeNational ?>;


    $('.find_list').keyup(function(){
        var min_length = 2; // min caracters to display the autocomplete
        var keyword = $(this).val();

        namespan = $(this).attr('name');

        if (keyword.length >= min_length) {

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword:keyword, field:namespan},
                success:function(data){

                    $('#' + namespan + '_List').show().html(data);
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

    function set_item(item,id) {
        // change input value
        $('#' + namespan).val(item);
        //$('#CustomerID').val(id);
        $('#' + namespan + '_List').hide();
    }


    
    $('document').ready(function(){

        var ArticleNumber = $('#ArticleNumber').val();



        //$('#preview').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});


        <?= CreateSltChangeJQ("Controller", "CityId='".$_SESSION['cityid']."' AND Disabled=0", "Name", "ControllerCode", "Code", "Id", "ControllerId") ?>


        //$('.article_change').focusout(chkArticle);
        $(document).on('click', '.add_article', function(){
            chkArticle();
        });



        $('#SpeedLimit').focusout(chkSpeed);
        $('#SpeedControl').focusout(chkSpeed);





        //$('#FineDate').focusout(function() {
        //    var str = $('#FineDate').val();
        //    var chkYear;
        //    if(str.length==8){
        //        $('#FineDate').val( str.substr(0,2) + "/" + str.substr(2,2) + "/" + str.substr(4) );
        //        chkYear = str.substr(4);
        //
        //    }else{
        //        var aDate = str.split("/");
        //        chkYear = aDate[2];
        //    }
        //
        //    if(chkYear!='<?//= $_SESSION['year']?>//'){
        //        $("#save").prop("disabled", true);
        //        $("#span_date").addClass("help-block");
        //        $("#span_date").html('Anno errato!');
        //
        //        chkDate = false;
        //
        //    }else{
        //        chkDate = true;
        //        if(chkDate==true){
        //            $("#save").prop("disabled", false);
        //            $("#span_date").removeClass("help-block");
        //            $("#span_date").html('');
        //
        //        }
        //    }
        //
        //});

        $('#FineTime').change(function() {

            var str = $('#FineTime').val();
            var part1 = parseInt(str.substr(0,2));
            var part2 = parseInt(str.substr(3,2));

            var hours = 24;
            var minutes = 59;

            if (part1 >hours || part2 >minutes){

                chkTime = false;
                $("#save").prop("disabled", true);
                $("#span_time").addClass("help-block");
                $("#span_time").html('Ora errato!');
            } else{

                chkTime=true;
                if(chkTime==true) {
                    $("#save").prop("disabled", false);
                    $("#span_time").removeClass("help-block");
                    $("#span_time").html('');
                }
            }
            if(str.length==4){

                $('#FineTime').val( str.substr(0,2) + ":" + str.substr(2,2) );
            }
        });


        $('#fileTreeDemo_1').fileTree({ root: 'public/_VIOLATION_/<?= $_SESSION['cityid']?>/', script: 'jqueryFileTree.php?insert=true' }, function(file) {

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


        $('#CountryId').change(function(){
            var id = $( "#CountryId" ).val();
            $('#VehicleCountry').val($( "#CountryId option:selected" ).text());

            if(id=='Z110'){
                var ent = $.ajax({
                    url: "ajax/department.php",
                    type: "POST",
                    data: {id:id},
                    dataType: "text"
                });
                ent.done(function(data){
                    $('#DepartmentId').html(data);
                    $('#department').show();
                    $('#toHide').hide();
                });
                ent.fail(function(jqXHR, textStatus){
                    alert( "Request failed: " + textStatus );
                });
            }else{
                $('#DepartmentId').html('');
                $('#department').hide();
                $('#toHide').show();
            }


            if(id=='Z000'){
                $('#TotalCharge').val('<?= $TotalChargeNational ?>');
                $('#span_TotalCharge').html('<?= $TotalChargeNational ?>');
                totCharge = <?= $TotalChargeNational ?>;

            }else{
                $('#TotalCharge').val('<?= $TotalChargeForeign ?>');
                $('#span_TotalCharge').html('<?= $TotalChargeForeign ?>');
                totCharge = <?= $TotalChargeForeign ?>;
            }

        });

        $('#Toponym').change(function() {
            $('#Address').val($(this).val());
        });

        $('#DetectorId').change(function() {
            var DetectorId = $(this).val();

            if(DetectorId!='') {
                $.ajax({
                    url: 'ajax/search_detector.php',
                    type: 'POST',
                    cache: false,
                    data: {DetectorId: DetectorId},
                    dataType:'json',
                    success: function (data) {

                        if (data.UploadNumber ==2){
                            $("#UploadNumber").val(data.UploadNumber);

                        }
                        if (data.strSpeed == 1) {
                            $('#DIV_Title_Speed').show();
                            $('#DIV_Speed').show();

                            $('#SpeedLimit').prop("readonly", false);
                            $('#SpeedControl').prop("readonly", false);

                            $('#id1_' + ArticleNumber).val('');
                            $('#id2_' + ArticleNumber).val('');
                            $('#id3_' + ArticleNumber).val('');

                            $('#id1_' + ArticleNumber).prop("disabled", true);
                            $('#id2_' + ArticleNumber).prop("disabled", true);
                            $('#id3_' + ArticleNumber).prop("disabled", true);

                            $('#DIV_Title_TLight').hide();
                            $('#DIV_TLight').hide();

                            $("#TimeTLightFirst").val('');
                            $("#TimeTLightSecond").val('');

                            $('#span_Article_'+ArticleNumber).html('');

                            }else if(data.strSpeed == 2){
                            $('#DIV_Title_TLight').show();
                            $('#DIV_TLight').show();

                            $("#id1_"+ArticleNumber).val('146');
                            $("#id2_"+ArticleNumber).val('3');
                            $("#id3_"+ArticleNumber).val('');

                            chkArticle();


                            $('#id1_' + ArticleNumber).prop("disabled", true);
                            $('#id2_' + ArticleNumber).prop("disabled", false);
                            $('#id3_' + ArticleNumber).prop("disabled", false);

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

                    }

                });
            }else{
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
        });



        $('#VehicleMass').focusout(function() {
            if($(this).val()=='') $(this).val(0);
        });
        $('#VehicleCapacity').focusout(function() {
            if($(this).val()=='') $(this).val(0);
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


        $('#VehiclePlate').change(function() {
            var VehiclePlate = $(this).val();
            var FineDate = $('#FineDate').val();
            var FineTime = $('#FineTime').val();
            var id = $( "#CountryId" ).val();
            var checkPlate;
            var message = "Attenzione: la targa potrebbe non essere italiana ";
            var VehicleTypeId = $('#VehicleTypeId').val();
            if(id=='Z000'){
                find = 0;
                // checkPlate = '^[a-zA-Z]{2}[a-zA-Z0-9][0-9]{5}$';
                // if(VehiclePlate.match(checkPlate)!=null) find = 1;
                if(VehicleTypeId == 9){
                    checkPlate = '^[a-zA-Z]{2}[0-9]{5}$';
                    if(VehiclePlate.match(checkPlate)!=null)  find = 1;
                } else {
                    checkPlate = '^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)!=null) find = 1;
                }
                if(VehiclePlate.includes('q') || VehiclePlate.includes('Q') || VehiclePlate.includes('u') || VehiclePlate.includes('U') ||
                    VehiclePlate.includes('o') || VehiclePlate.includes('O') || VehiclePlate.includes('i') || VehiclePlate.includes('I')) {
                    find = 0;
                    message += 'oppure si tratta di una vecchia targa';
                }
                if(find==0) alert(message);
            }

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


        $('#f_violation').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
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

                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },

                FineTime: {
                    validators: {
                        notEmpty: {message: 'Richiesto'},
                        regexp: {
                            //regexp: '^(1?[0-9]|2[0-9]):[0-5][0-9]$',
                            regexp: '^(0?[0-9]|1?[0-9]|2[0-4]):[0-5][0-9]$',
                            message: 'Ora non valida'
                        }
                    }

                },

            }
        }).on('success.form.bv', function(e){
            e.preventDefault();
            var today_date = $('#FineDate').val();
            var ora = $('#FineTime').val();
            var Eludi_Controlli  = $('#Conrolli').is(':checked');
            var targa = $('#VehiclePlate').val();
            var reason = $('#ReasonId').val();
            var vehicleType = $('#VehicleTypeId').val();
            var Code = $('#Code').val();
            var Controller = $('.select_controller_1').val();
            var article1 = $('#ArticleId_1').val();
            var article2 = $('#ArticleId_2').val();
            var article3 = $('#ArticleId_3').val();
            var article4 = $('#ArticleId_4').val();
            var article5 = $('#ArticleId_5').val();
            var articles = {today_date:today_date,
                ora:ora,
                targa:targa,
                article1:article1,
                article2:article2,
                article3:article3,
                article4:article4,
                article5:article5,
            };
            /*
            if (Code != "") {
                $.ajax({
                    url: 'ajax/mgmt_validate_fine_ajax.php',
                    type: 'POST',
                    data: {Code: Code, Controller: Controller,Eludi_Controlli:Eludi_Controlli,FineTypeId:1},
                    success: function (data) {
                        if (data == "NOB") {
                            check("#save","#span_code","Codice non essiste!!");
                            chkCode = false;
                        }
                        if (data == "NE") {
                            check("#save","#span_acce_1","Controller Errato!");
                            controller = false;
                        }

                        if (data == "GE") {
                            check("#save","#span_code","Codice gia esiste!!");
                            chkCode = false;
                        }
                        if (data == "OK") {

                            submitForm(today_date,ora,targa,vehicleType,reason,articles);
                        }

                    }
                });
            }else{*/
                submitForm(today_date,ora,targa,vehicleType,reason,articles);
            //}
            return false;

        });

        $('#Conrolli').on('click',function(){
            $('#Code').trigger("change");
            $('#ControllerId').trigger("change");
            controller = true;
            var Eludi_Controlli  = $('#Conrolli').is(':checked');
            if (Eludi_Controlli == true){
                $("#save").prop("disabled", false);
                $("#span_acce_1").removeClass("help-block");
                $("#span_acce_1").html('');
            }
        });

        function check(button,span,text){
            $(button).prop("disabled", true);
            $(span).addClass("help-block");
            $(span).html(text);
        }

        function submitForm(today_date,ora,targa,vehicleType,reason,articles){
            if (today_date == "" || ora == "" || targa == "" || vehicleType == "") {
                e.preventDefault();
            } else {
                $("#save").prop("disabled", false);
                $("#span_acce").removeClass("help-block");
                $("#span_acce").html('');
                    if (chkCode) {
                        $.ajax({
                            url: 'ajax/mgmt_check_verbali.php',
                            type: 'POST',
                            data: articles,
                            success: function (data) {
                                if (data == "NOK") {
                                    $('.check').append('<div class="alert alert-warning">Questa multa esiste nel database. Per favore seleziona un altro articolo!</div>');
                                    $("html, body").animate({scrollTop: 0}, "slow");
                                    $("#save").prop("disabled", true);
                                }
                                if (data == "OK") {
                                    $('.check').hide();
                                    if (giaPresente == false && controller) document.f_violation.submit();
                                }
                            }
                        });
                    }

            }
        }

        function chkArticle() {

            var id1=$('#id1_'+ArticleNumber).val();
            var id2=$('#id2_'+ArticleNumber).val();
            var id3=$('#id3_'+ArticleNumber).val();
            var FineTime = $('#FineTime').val();

            if(id1!='' && (id2!='' || id3!='')){

                var RuleTypeId = $('#RuleTypeId').val();
                var request = $.ajax({
                    url: "ajax/search_article.php",
                    dataType: 'json',
                    type: "post",
                    cache: false,
                    data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3+"&ArticleCounter="+ArticleNumber
                });

                request.done(function (response){
                    $("#downart").show();
                    if (response.NonExist ==1){

                        alert(response.Description);
                        $('#span_Article_'+ArticleNumber).html(response.Description).css("background-color","red");
                        $("#save").prop("disabled", true);
                    }else{
                        $("#save").prop("disabled", false);
                        $('#Edit_Contestazione').show();
                        $('#span_Article_'+ArticleNumber).html(response.Description);
                        $('#span_Article_'+ArticleNumber).css("background-color","");
                    }
                    //$('#span_Article_'+ArticleNumber).html(response.Description);
                    $('#ReasonId').html(response.ReasonDescription);
                    $('#ReasonId_Second').val(response.reasonId);
                    $('#ReasonId').trigger("change");
                    $('#EditArticle_'+ArticleNumber).show();

                    if(response.Result==1){

                        $('#ArticleId_'+ArticleNumber).val(response.ArticleId);

                        if(ArticleNumber==1){
                            $('#ViolationTypeId').val(response.ViolationTypeId);
                            $('#span_ViolationTitle').html(response.ViolationTitle);
                        }
                        $('#artcomune_'+ArticleNumber).val(response.ArtComunali);
                        $('#Fee_'+ArticleNumber).val(response.Fee);
                        $('#span_Fee_'+ArticleNumber).html(response.Fee);
                        $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                        $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);

                        $('#art_'+ArticleNumber).attr('fee', response.Fee);
                        $('#art_'+ArticleNumber).attr('addMass', response.AddMass);
                        $('#art_'+ArticleNumber).attr('maxFee', response.MaxFee);
                        $('#art_'+ArticleNumber).attr('desc', response.Description);
                        $('#AdditionalSanctionSelect_'+ArticleNumber).html(response.AdditionalSanctionSelect);
                        $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                        //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                        var totFee = 0;
                        for(var i=1;i<=ArticleNumber;i++){
                            totFee = totFee + Number($('#Fee_'+i).val());
                        }
                        totFee = totFee + Number(totCharge);
                        $('#span_TotalFee').html(totFee.toFixed(2));

                        checkFee();
                    }



                });
                request.fail(function (jqXHR, textStatus){
                    $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
                });

                //event.preventDefault();
            }

        }




        function chkSpeed() {
            var DetectorId = $('#DetectorId').val();
            var SpeedLimit=$('#SpeedLimit').val();
            var SpeedControl=$('#SpeedControl').val();
            var FineTime = $('#FineTime').val();
            var RuleTypeId = $('#RuleTypeId').val();

            if(SpeedLimit!='' && SpeedControl!=''){

                var request = $.ajax({
                    url: "ajax/search_article.php",
                    dataType: 'json',
                    type: "post",
                    cache: false,
                    data: "RuleTypeId="+RuleTypeId+"&DetectorId="+DetectorId+"&SpeedLimit=" + SpeedLimit + "&SpeedControl=" + SpeedControl +"&FineTime=" + FineTime
                });

                request.done(function (response){
                    console.log(response)

                    $("#downart").show();
                    $('#span_Article_'+ArticleNumber).html(response.Description);
                    $('#ReasonId').html(response.ReasonDescription);
                    $('#ReasonId_Second').val(response.reasonId);
                    $('#ReasonId').trigger("change");
                    $('#EditArticle_'+ArticleNumber).show();
                    $('#Edit_Contestazione').show();
                    if(response.Result==1){
                        $('#ArticleId_'+ArticleNumber).val(response.ArticleId);
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
                        $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                        //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                        var totFee = 0;
                        for(var i=1;i<=ArticleNumber;i++){
                            totFee = totFee + Number($('#Fee_'+i).val());
                        }
                        totFee = totFee + Number(totCharge);

                        $('#span_TotalFee').html(totFee.toFixed(2));
                    }else{
                        if (response.NonExist ==1){

                            alert(response.Description);
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

        $( function() {
            $( "#BoxArticleSearch" ).draggable();
        } );


        $(".close_window_article").click(function () {
            $('#BoxArticleSearch').hide();
        });


        $(".glyphicon-search").click(function () {


            var id1=$('#id1_'+ArticleNumber).val();

            var RuleTypeId = $('#RuleTypeId').val();
            var get_ArticleNumber = $(this).attr('id');
            Article = get_ArticleNumber.split("_");
            ArticleNumber_Edit = Article[1];
            $("#art_num").val(ArticleNumber_Edit);
            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, RuleTypeId: RuleTypeId, ArticleCounter: ArticleNumber_Edit},
                success: function (data) {

                    $('#Div_Article_Content').html(data.Article);
                    $('#BoxArticleSearch').fadeIn('slow');


                }
            });

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
                    $('#ReasonId').html(response.ReasonDescription);
                    $('#ReasonId_Second').val(response.reasonId);
                    $('#ReasonId').trigger("change");
                    $('#EditArticle_'+index).show();
                    $('#Edit_Contestazione').show();
                    if(response.Result==1){

                        $('#id1_'+index).val(response.Id1);
                        $('#id2_'+index).val(response.Id2);
                        $('#id3_'+index).val(response.Id3);
                        $('#ArticleId_'+index).val(response.ArticleId);
                        $('#artcomune_'+index).val(response.ArtComunali);
                        if(index==1){
                            $('#ViolationTypeId').val(response.ViolationTypeId);
                            $('#span_ViolationTitle').html(response.ViolationTitle);
                        }

                        $('#Fee_'+index).val(response.Fee);
                        $('#span_Fee_'+index).html(response.Fee);
                        $('#MaxFee_'+index).val(response.MaxFee);
                        $('#span_MaxFee_'+index).html(response.MaxFee);

                        $('#art_'+index).attr('fee', response.Fee);
                        $('#art_'+index).attr('addMass', response.AddMass);
                        $('#art_'+index).attr('maxFee', response.MaxFee);
                        $('#art_'+index).attr('desc', response.Description);
                        $('#AdditionalSanctionSelect_'+index).html(response.AdditionalSanctionSelect);
                        $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                        //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                        var totFee = 0;
                        for(var i=1;i<=index;i++){
                            totFee = totFee + Number($('#Fee_'+i).val());
                        }
                        totFee = totFee + Number(totCharge);
                        $('#span_TotalFee').html(totFee.toFixed(2));
                        checkFee();
                    }

                });
                request.fail(function (jqXHR, textStatus){
                    $('#span_Article_'+index).html("ERR: "+ textStatus);
                });
            }
        });

        $("#remove_btn").click(function () {
            var src = $('#searchs').val('');
        });

        $('.article_change ').on('change',function () {
            chkArticle();
        });

        $('.article_change').keyup(function (e) {
            const code = $(this).val();
            var id1=null;
            var get_ArticleNumber_text = $(this).attr('id');
            Article = get_ArticleNumber_text.split("_");
            ArticleNumber_Text = Article[1];
            var code_comma = $("#id2_"+ArticleNumber_Text).val();
            var code_lettera = $("#id23_"+ArticleNumber_Text).val();
            var RuleTypeId = $('#RuleTypeId').val();
            var isFound = false;
            const index = $(this).data('number');
            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, search_code: code, ArticleCounter: index, RuleTypeId: RuleTypeId},
                success: function (data) {
                    if(code_comma != "" && code_lettera !="") {
                        $('#span_Article_' + index).html(data.Article);
                        $('#id1_' + index).val(data.Id1);
                        $('#id2_' + index).val(data.Id2);
                        $('#id3_' + index).val(data.Id3);
                        if (data.Article != 'Nessun articolo trovato') {
                            $("#downart").show();
                        }
                        chkArticle();
                    }
                }
            });

        });

        $(".fa-pencil-square-o, .glyphicon-search, .fa-share, .fa-caret-down, .fa-caret-up, .fa-edit").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });

        $(document).on('click','.fa-pencil-square-o',function () {

            var get_ArticleNumber_text = $(this).attr('id');
            Article = get_ArticleNumber_text.split("_");
            ArticleNumber_Text = Article[1];
            var text = $('#span_Article_'+ArticleNumber_Text).text();

            if($('#ArticleText_'+ArticleNumber_Text).is(":visible")) {

                $('#textarea_'+ArticleNumber_Text).val('');
                $('#ArticleText_'+ArticleNumber_Text).hide();
                $('#span_Article_'+ArticleNumber_Text).html($('#art_'+ArticleNumber_Text).attr('desc'));
            }
            else {
                $('#ArticleText_'+ArticleNumber_Text).show();
                $('#span_Article_'+ArticleNumber_Text).html('');
                $('#textarea_'+ArticleNumber_Text).val(text);
            }
        });

        $(document).on('click','#Edit_Contestazione',function (e) {
            var whatis = $("#itsreason").val();
            if (whatis=='reason'){
                $('#ReasonId').css("display","none");
                $('#Reason_Text').removeAttr( 'style' );
                $("#itsreason").val('text')
            } else {
                $('#ReasonId').removeAttr( 'style' );
                $('#Reason_Text').css("display","none");
                $("#itsreason").val('reason')
            }
        });

        $('#downart').click(function () {

            ArticleNumber++;

            $("#BoxArticle_"+ArticleNumber).show();
            $("#upart").show();
            $(this).hide();
            if(ArticleNumber==5){
                $("#downart").hide();
            }
            $('#ArticleNumber').val(ArticleNumber);

        });


        $('#upart').click(function () {

            $("#BoxArticle_"+ArticleNumber).hide();
            ArticleNumber--;

            if(ArticleNumber==1){
                $("#upart").hide();
                $("#downart").show();
            }else{
                $("#downart").show();
            }
            $('#ArticleNumber').val(ArticleNumber);

            var totFee = 0;
            for(var i=1;i<=ArticleNumber;i++){
                totFee = totFee + Number($('#Fee_'+i).val());
            }
            totFee = totFee + Number(totCharge);
            $('#span_TotalFee').html(totFee.toFixed(2));


        });

        $('.fa-share').click(function () {

            var VehicleTypeId = $("#VehicleTypeId").val();
            var VehiclePlate = $("#VehiclePlate").val();


            $.ajax({
                url: 'ajax/ajx_src_trespasser_with_plate_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {VehicleTypeId: VehicleTypeId, VehiclePlate: VehiclePlate},
                success: function (data) {
                    var Genre           = data.Genre;
                    var a_Trespasser    = data.Trespasser;
                    var a_Vehicle       = data.Vehicle;
                    var a_Error         = data.Error;
                    if(a_Error['ErrorCode']==""){
                        $('#Genre').val(Genre);
                        if(Genre=="D"){
                            $('#CompanyName').val(a_Trespasser['CompanyName']);

                            $('#Div_Trespasser_Person').hide();
                            $('#Div_Trespasser_Company').show();

                        }else{
                            $('#Surname').val(a_Trespasser['Surname']);
                            $('#Name').val(a_Trespasser['Name']);
                            $('#BornDate').val(a_Trespasser['BornDate']);
                            $('#TaxCode').val(a_Trespasser['TaxCode']);
                            $('#BornPlace').val(a_Trespasser['BornPlace']);

                            $('#Div_Trespasser_Company').hide();
                            $('#Div_Trespasser_Person').show();
                        }


                        $('#Province').val(a_Trespasser['Province']);
                        $('#City').val(a_Trespasser['City']);
                        $('#TrespasserAddress').val(a_Trespasser['Address']);
                        $('#ZIP').val(a_Trespasser['ZIP']);
                        $('#Div_Trespasser_Address').show();


                        $('#VehiclePlate').val(a_Vehicle['VehiclePlate']);
                        $('#VehicleMass').val(a_Vehicle['VehicleMass']);



                        $('#VehicleBrand').val(a_Vehicle['VehicleBrand']);
                        $('#VehicleModel').val(a_Vehicle['VehicleModel']);
                        //$('#VehicleLastRevisionDate').val(a_Vehicle['VehicleLastRevisionDate']);
                        //$('#VehicleLastRevisionResult').val(a_Vehicle['VehicleLastRevisionResult']);
                        $('#Div_Vehicle').show();

                        /*
                         a_Vehicle['VehicleType']
                         a_Vehicle['dataInizioProprieta']
                         a_Vehicle['dataPrimaImmatricolazione']
                         a_Vehicle['numeroTelaio']
                         a_Vehicle['codiceOmologazione']
                         a_Vehicle['VehicleColor']
                         a_Vehicle['origine']


                         a_Vehicle['categoria']
                         a_Vehicle['usoVeicolo']

                         a_Vehicle['codiceAntifalsificazioneTagliandoUltimaRevisione']
                         a_Vehicle['numeroCartaCircolazione']
                         a_Vehicle['siglaUMC']
                         a_Vehicle['lunghezzaVeicoloInMetri']
                         a_Vehicle['larghezzaVeicoloInMetri']
                         a_Vehicle['numeroPostiTotali']

                         a_Vehicle['massaComplessivaRimorchioInKG']
                         a_Vehicle['taraInKG']

                         */

                    }else{
                        alert(a_Error['ErrorCode']+ ' ' + a_Error['ErrorDescription']);
                    }

                }



            });
        });

    });
</script>
<script>
    $(document).ready(function(){
        var AccertatoreNumber = $('#AccertatoreNumber').val();
        if (AccertatoreNumber > 1){
            $("#upace").show();
        }
        $('#downace').click(function () {

            AccertatoreNumber++;
            $("#BoxAccertatore_"+AccertatoreNumber).show();
            $("#upace").show();
            $(this).hide();
            if(AccertatoreNumber==5){
                $("#downace").hide();
            }
            $('#AccertatoreNumber').val(AccertatoreNumber);

        });

        $('#upace').click(function () {

            $("#BoxAccertatore_"+AccertatoreNumber).hide();
            $(".select_controller_"+AccertatoreNumber).val('');
            AccertatoreNumber--;

            if(AccertatoreNumber==1){
                $("#upace").hide();
            }else{
                $("#downace").show();
            }
            $('#AccertatoreNumber').val(AccertatoreNumber);


        });
        for(i=1;i<=5;i++){
            $('.select_controller_'+i).change(function () {
                $("#downace").show();
                var cont_id = $(this).attr('order')
                var id = $(this).val();
                var article = $('#id1_1').val();
                var FineDate = $('#FineDate').val();
                check_accertatore(id,article,FineDate,cont_id)
            });
        }
       function check_accertatore(id,article,FineDate,acce_number){
           $.ajax({
               url: 'ajax/ajx_selected_controller.php',
               type: 'POST',
               data: {id: id, article: article, FineDate: FineDate,acce_number:acce_number},
               success: function (data) {
                   if (data == "NOTOK") {
                       giaPresente = true;
                       $("#save").prop("disabled", true);
                       $("#span_acce_"+acce_number).addClass("help-block");
                       $("#span_acce_"+acce_number).html('IL controller e Ausilario!');
                       controller = false;

                   }
                   if (data == "OK") {
                       giaPresente = false;
                       $("#save").prop("disabled", false);
                       $("#span_acce_"+acce_number).removeClass("help-block");
                       $("#span_acce_"+acce_number).html('');
                       controller = true;
                   }
                   if (data == "NO") {
                       giaPresente = true;
                       $("#save").prop("disabled", true);
                       $("#span_acce_"+acce_number).addClass("help-block");
                       $("#span_acce_"+acce_number).html('Il controller non è nell\'incarico!');
                       controller = false;
                   }
               }
           });
       }
    });

</script>
    <script>
        $(document).ready(function () {
            $('#Code').change(function() {
                var FirstCode = $(this).val();
                var SendCode = $(this).val();
                var validCode = true;
                if (FirstCode.indexOf('/') > 0)
                {
                    FirstCode = FirstCode.replace("/","");
                }

                if (validCode) {
                    $.ajax({
                        url: 'ajax/search_code.php',
                        type: 'POST',
                        data: {Code: SendCode,FineTypeId:1},
                        dataType:'json',
                        success: function (data) {
                            if (data.Result == "NO") {
                                $("#save").prop("disabled", true);
                                $("#span_code").addClass("help-block");
                                $("#span_code").html('Già presente!');
                                chkCode=false;
                            }
                            if (data.Result == "OK") {
                                $("#save").prop("disabled", false);
                                $("#span_code").removeClass("help-block");
                                $("#span_code").html('');
                                chkCode = true;
                            }
                        }
                    });
                }

            });

        })
    </script>



<?php
include(INC."/footer.php");
