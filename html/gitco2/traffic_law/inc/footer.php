<?php
$Documentation_Out = "";
if($_SESSION['Documentation']!=""){
	$Documentation_Out ='
		window.open(\''.$_SESSION['Documentation'].'\');
	';
	$_SESSION['Documentation']="";
}

///////////////////////////////
///
///  MODALE MESSAGGIO
///
///////////////////////////////
if($_SESSION['Message'] === '') $_SESSION['Message'] = null;
$Message_Out = "";
$str_div = "";
$b_gotMessage = false;
if(!is_null($_SESSION['Message'])){
	$Message_Out ='$("#MsgModal").modal("show");';
	
    if(isset($_SESSION['Message']['Success'])){
        $str_MessageColour = 'bg-success-dark';
        $str_MessageTitle = '<i class="fa fa-check-circle fa-fw" style="font-size: 2.5rem;"></i><strong style="vertical-align: text-bottom;">Successo</strong>';
        $str_Message = $_SESSION['Message']['Success'];
        $b_gotMessage = true;
    } else if(isset($_SESSION['Message']['Warning'])){
        $str_MessageColour = 'bg-warning-dark';
        $str_MessageTitle = '<i class="fa fa-warning fa-fw" style="font-size: 2.5rem;"></i><strong style="vertical-align: text-bottom;">Avviso</strong>';
        $str_Message = $_SESSION['Message']['Warning'];
        $b_gotMessage = true;
    } else if(isset($_SESSION['Message']['Error'])){
        $str_MessageColour = 'bg-danger-dark';
        $str_MessageTitle = '<i class="fa fa-times-circle fa-fw" style="font-size: 2.5rem;"></i><strong style="vertical-align: text-bottom;">Errore</strong>';
        $str_Message = $_SESSION['Message']['Error'];
        $b_gotMessage = true;
    } else {
        $str_MessageColour = 'bg-info-dark';
        $str_MessageTitle = '<i class="fa fa-info-circle fa-fw" style="font-size: 2.5rem;"></i><strong style="vertical-align: text-bottom;">Informazione</strong>';
        $str_Message = $_SESSION['Message'];
        $b_gotMessage = true;
    }
    
    $str_Message = stripslashes($str_Message);
    
    $str_div = '
<div id="MsgModal" class="modal modal-center fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:hidden;">
            <div class="modal-header text-center '.$str_MessageColour.'">
                '.$str_MessageTitle.'
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                '.$str_Message.'
            </div>
            <div class="modal-footer">
                <button id="MsgModal_clipboard" type="button" class="btn btn-info"><i class="fa fa-copy"></i> Copia negli appunti</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
        
    </div>
</div>';
    
    $_SESSION['Message']=null;
}



///////////////////////////////
///
///  DIV MEX
///
///////////////////////////////
$str_div_message = '';
if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    $class = strlen($answer)>50?"alert-warning":"alert-success";
    $str_div_message ='
        $("#div_message_page").addClass("alert alert-success");
        $("#div_message_page").html("'.$answer.'");
        $("#div_message_page").show();
        setTimeout(function(){ $("#div_message_page").hide()}, 4000);
    ';
}

if (isset($_GET['error'])){
    $error = $_GET['error'];
    $class = strlen($error)>50?"alert-warning":"alert-danger";
    $str_div_message ='
        $("#div_message_page").addClass("alert alert-danger");
        $("#div_message_page").html("'.$error.'");
        $("#div_message_page").show();
        setTimeout(function(){ $("#div_message_page").hide()}, 4000);
    ';
}






$strFlip = '';
$str_div_flip = '<div id="flip">';

if($b_gotMessage){
    $str_div_flip .= '
        <button id="flip_message" data-toggle="tooltip" data-container="body" data-placement="right" title="Mostra ultimo messaggio" class="tooltip-r btn btn-default" style="width: 25px;padding: 0.5rem;height: 25px;">
            <i class="fa fa-comment"></i>
        </button>';
}

if($_SESSION['userlevel']>=3){

    $CurrentDate = date("Y-m-d");

    $str_CityId = (isset($_SESSION['cityid']) && $_SESSION['cityid']!="") ? $_SESSION['cityid'] : "";

    $rs_Dispute = $rs->SelectQuery("
      SELECT
        F.ProtocolId,
        F.ProtocolYear,

        T.CompanyName,
        T.Surname,
        T.Name,

        Cus.ManagerCity,

        D.GradeTypeId,
        D.OfficeCity,

        DD.DateHearing,
        DD.TimeHearing,

        O.TitleIta OfficeTitle


        FROM Fine F
            JOIN FineTrespasser FT ON FT.FineId = F.Id AND (FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=2)
            JOIN Trespasser T ON FT.TrespasserId= T.Id
            JOIN FineDispute FD ON F.Id = FD.FineId
            JOIN Dispute D ON FD.DisputeId=D.Id
            JOIN DisputeDate DD ON DD.DisputeId=D.Id
            JOIN Office O ON O.Id = D.OfficeId
            JOIN Customer Cus ON Cus.CityId=F.CityId



        WHERE D.DateMerit IS NULL AND DD.DisputeResultId=0 AND DD.DateHearing IS NOT NULL
        AND F.CityId='". $str_CityId ."' AND DD.DateHearing>='". $CurrentDate. "'
        ORDER BY DD.DateHearing DESC

    ");



    $n_RowNumber = mysqli_num_rows($rs_Dispute);
    if($n_RowNumber>0){
        $a_GradeType = array("","I","II","III");

        $a_DisputeStatusId = array("","#DDD728","#3C763D","#A94442");
        $str_Date = "#3C763D";


        $str_Panel = '<div id="panel" style="z-index:-1;">';
        $str_Panel .='
	        <div class="container-fluid">
    	        <div class="row-fluid">

        ';
        while ($r_Dispute = mysqli_fetch_array($rs_Dispute)) {

            $str_TrespasserName= $r_Dispute['CompanyName']." ".$r_Dispute['Surname']." ".$r_Dispute['Name'];

            $str_Panel .= '
                <div class="table_caption_H col-sm-1">'.$r_Dispute['ProtocolId'].'/'.$r_Dispute['ProtocolYear'].'</div>
                <div class="table_caption_H col-sm-1">'.$a_GradeType[$r_Dispute['GradeTypeId']].' Grado</div>
                <div class="table_caption_H col-sm-3">'.$r_Dispute['OfficeTitle'].' - '.$r_Dispute['OfficeCity'].'</div>
                <div class="table_caption_H col-sm-3" style="text-align: left;">'.$str_TrespasserName.'</div>
                ';
            $str_Color = "#3C763D";
            if($r_Dispute['DateHearing']==""){
                $str_Panel .= '<div class="table_caption_H col-sm-4">In attesa di esito</div>';

            }else{

                $n_Day = DateDiff("D",date("Y-m-d"),$r_Dispute['DateHearing']);



                if($n_Day<30){
                    $str_Color = "#BA8520";
                    if($str_Date != "#A94442") $str_Date = "#DDD728";
                }
                if($n_Day<15){
                    $str_Color = "#A94442";
                    $str_Date = "#A94442";
                }


                $str_Panel .= '<div class="table_caption_H col-sm-2" style="color:'.$str_Color.'">'.DateOutDB($r_Dispute['DateHearing']).'</div>';
                $str_Panel .= '<div class="table_caption_H col-sm-2" style="color:'.$str_Color.'">'.$r_Dispute['TimeHearing'].'</div>';
                $str_Panel .= '<div class="clean_row HSpace4"></div>';
            }


        }

        $str_Panel .= '
                </div>
             </div>
        ';

        $str_Panel .= '</div>';

        $strFlip .= '
            <button id="flip_gavel" data-toggle="tooltip" data-container="body" data-placement="top" title="Ricorsi" class="tooltip-r btn btn-default" style="width: 25px;padding: 0.5rem;height: 25px;">
                <i class="fa fa-gavel" style="color:'.$str_Color.'"></i>
            </button>';


        $str_div_flip .= $str_Panel.$strFlip;
    }

}

$str_div_flip .= '</div>'












?>
<div class="overlay" id="overlay" style="display:none;"></div>
<?= $str_div ?>



<?= $str_div_flip ?>
 
<script>

    $('document').ready(function () {

        var shownMessage = false;

        <?= $str_div_message ?>

        $("input, textarea, select").focus(function(){
            $(this).addClass("active_field");
        });

        $("input, textarea, select").blur(function(){
            $(this).removeClass("active_field");
        });

        $(document).on('keyup', 'input[type=text].frm_field_date', function (e) {

            this.value = this.value.replace(/[^0-9\/]/g,'');

            var textSoFar = $(this).val();

            if (textSoFar.length == 2 || textSoFar.length == 5) {
                $(this).val(textSoFar + "/");
            }
            else if (textSoFar.length > 10) {
                $(this).val(textSoFar.substr(0,10));
            }
        });

        $(document).on('keyup', 'input[type=text].frm_field_time', function (e) {

            var textSoFar = $(this).val();

            if (textSoFar.length == 2) {
                $(this).val(textSoFar + ":");
            }
            else if (textSoFar.length > 5) {
                $(this).val(textSoFar.substr(0,5));
            }
        });

        $(document).on('keyup', 'input[type=text].frm_field_numeric', function (e) {
            this.value = this.value.replace(/[^0-9\.\,]/g,'').replace(',','.');
        });

        $(document).on('keyup', 'input[type=text].frm_field_currency', function (e) {
            this.value = this.value.replace(/[^0-9\.\,]/g,'').replace(',','.');
        });

        $("#div_menu_left").click(function(){
            $(this).animate({"left":"0px"}, 1000);
            $(this).css("cursor","pointer");
        });
        $("#div_menu_left").mouseleave(function(){
            $(this).stop();
            $(this).animate({"left":"-250px"}, 1000);
            $(this).css("cursor","");
        });
        
        $("#events_menu button[data-eventid]").click(function(event){
        	var button = $(this);
			event.stopPropagation();
	        $.ajax({
	        	url: "ajax/ajx_mark_events.php",
                type: "POST",
                dataType: "json",
                ContentType: "application/json; charset=UTF-8",
                data: {Id: $(this).data("eventid")},
                success:function(data){
                	button.remove();
                	$.each(data, function( index, value ) {
                		if(value <= 0) $(`#events_menu [data-badgecounter='${index}']`).removeClass('ripple-danger');
                    	$(`#events_menu [data-badgecounter='${index}'] span`).text(value);
                    });
                },
                error: function (data) {
                    console.log(data);
                    alert('error: ' + data.responseText);
                }
            });
        });


        $('body').tooltip({
            selector: '.tooltip-r'
        });



        <?= $Documentation_Out ?>
        <?= $Message_Out ?>




        $("#overlay").click(function(){
            $(this).fadeOut('fast');
            $('#DivMessageOverlay').hide();

        });
        $("#flip_gavel").click(function(){
            $("#panel").slideToggle("slow");
        });
        $("#flip_message").click(function(){
        	$("#MsgModal").modal("show");
        });
        $(".tooltip-r").click(function(){
        	$(this).tooltip("hide");
        });

        $('#MsgModal').on('hidden.bs.modal',function(){
            if(!shownMessage){
            	$("#flip_message").tooltip("show");
                setTimeout(function () {
                	$("#flip_message").tooltip("hide");
                	shownMessage = true;
                }, 3000);
            }
        });
        $('#MsgModal_clipboard').on('click', function(){
        	var element = $('#MsgModal').find('.modal-body').get(0);
        	var message = element.innerText || element.textContent || "";
            navigator.clipboard.writeText(message.trim());
        });

    });


    //Dropdown Select and request  regola* and violation*
    $('select[name="CityId"]').click(function() {
       var cityId = $(this).val();
       $('#regolaId').html('');
       $.ajax({
            type: 'GET',
            url: "req_new.php?cityId="+cityId,
            success:function(data){
                if (data) {
                    data = ( JSON.parse(data) ).rules;
                    var el;
                    for(var i=0; i<data.length; i++){
                    el = '<option value="'+ data[i][0].id +'">'+ data[i][0].title +'</option>';
                    $('#regolaId').append(el);
                    }
                }
            }
        });
       $('#regolaId').html('<option value="0">Select Regola</option>');
    });

     $('#CityId').click();

    $('select[name="regolaId"]').change(function() {
       var ruleTypeId = $(this).val();
       var defItem = $('#violation option').eq(0);
       $('#violation').html('');
       $.ajax({
            type: 'GET',
            url: "req_new.php?ruleTypeId="+ruleTypeId,
            success:function(data){
              data = ( JSON.parse(data) ).violations;
              var el;
              for(var i=0; i<data.length; i++){
                el = '<option value="'+ data[i][0].id +'">'+ data[i][0].title +'</option>';
                $('#violation').append(el);
              }
              
            }
        });
       $('#violation').html(defItem);
    });

</script>
<script>
    $(function () {

        $('.frm_field_currency').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_currency: {
                    selector: '.frm_field_currency',
                    validators: {
                        numeric: {
                            message: 'Valuta'
                        }
                    }
                },

            }
        });
        $('.frm_field_required').bootstrapValidator({
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
            }
        });
        $('.frm_field_numeric').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('.help').on('click',function(){
            $('#HelpModal').modal('show');
        });



    });



</script>
<?php
if(isset($rs))  unset($rs);

?>
</body>
</html>