<?php
$str_ControllerBox = '';
$str_ControllerSelect = '<option></option>';
$str_CssSelect = 'class="form-control frm_field_required"';
$str_CssDisplay = '';
$str_Controller_Btn = '
    <i class="fa fa-caret-up" style="position:absolute; top:-1px;right:3px;font-size: 1.5rem; display:none" id="controller_up"></i>
    <i class="fa fa-caret-down" style="position:absolute; bottom:-1px;right:3px;font-size: 1.5rem;" id="controller_down"></i>
';



$rs_Controller = $rs->SelectQuery("SELECT Id, Code, CONCAT(Code,' ',Qualification,' ',Name) AS Name FROM Controller WHERE CityId ='".$_SESSION['cityid']."' AND disabled = 0 ORDER BY Name");
while($r_Controller = mysqli_fetch_array($rs_Controller)){
    $str_ControllerSelect .= '
        <option value="'. $r_Controller['Id'] .'">'. $r_Controller['Name'] .'</option>
    
    ';
}




for($i=1; $i<=5; $i++){

    $str_ControllerBox .= '
                <div class="col-sm-7 pull-right"  id="Div_Controller_'.$i.'" '.$str_CssDisplay.'>
                    <div class="col-sm-3 BoxRowLabel table_caption_I">
                        Accertatore '.$i.'
                    </div>          
                    <div class="col-sm-8 BoxRowCaption">
                        <select '. $str_CssSelect .' class="form-control" name="ControllerId[]" id="ControllerCode_'.$i.'">
                        '. $str_ControllerSelect .'
                        </select>
                        <span id="span_controller"></span>
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        '. $str_Controller_Btn .'
                    </div>

                </div>                         
            <div class="clean_row HSpace4"></div>
';

    $str_CssDisplay = 'style="display: none;"';
    $str_Controller_Btn = '';
    $str_CssSelect = '';
}


?>

<script>
    $('document').ready(function () {
        var ControllerNumber = $('#ControllerNumber').val();

        $('#controller_down').click(function () {

            ControllerNumber++;
            $("#Div_Controller_"+ControllerNumber).show();


            //$(this).hide();
            if(ControllerNumber==5){
                $("#controller_down").hide();
            } else {
                $("#controller_up").show();
                $("#controller_down").show();

            }
            $('#ControllerNumber').val(ControllerNumber);

        });


        $('#controller_up').click(function () {


            $("#ControllerCode_"+ControllerNumber).val('');

            $("#Div_Controller_"+ControllerNumber).hide();

            ControllerNumber--;

            if(ControllerNumber==1){
                $("#controller_up").hide();
            } else {
                $("#controller_up").show();
                $("#controller_down").show();

            }

        });


/*
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
*/
    });

</script>