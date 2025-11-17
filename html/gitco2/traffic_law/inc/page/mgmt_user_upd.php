<?php

$rs_User = $rs->Select(MAIN_DB.'.User',"Id=".$_SESSION['userid']);
$r_User = mysqli_fetch_array($rs_User);



$str_out .='
<div class="row-fluid">
<form action="mgmt_user_upd_exe.php" id="f_user" method="post">
    <div class="col-sm-12">
        <div class="col-sm-3 BoxRowLabel">
            Utente
        </div>
        <div class="col-sm-9 BoxRowCaption">
            ' . $r_User['UserName'] . '
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-3 BoxRowLabel">
            Ultimo accesso
        </div>
        <div class="col-sm-9 BoxRowCaption">
            ' . DateOutDB($r_User['LoginDate']) . ' ' . TimeOutDB($r_User['LoginTime']) . '
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12 BoxRowLabel">
            Aggiornamento password
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-3 BoxRowLabel">
            Vecchia password
        </div>
        <div class="col-sm-9 BoxRowCaption">
            <input type="password" class="form-control frm_field_string frm_field_required" name="old_P" id="old_P" style="width:15rem;" />
            <span id="span_O"></span>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-3 BoxRowLabel">
            Nuova password
        </div>
        <div class="col-sm-9 BoxRowCaption">
            <input type="password" data-minlength="6" class="form-control frm_field_string" name="new_P" id="new_P" style="width:15rem;" />
            <span id="span_P"></span>
        </div>    
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-3 BoxRowLabel">
            Ripeti password
        </div>
        <div class="col-sm-9 BoxRowCaption">
            <input type="password" data-minlength="6" class="form-control frm_field_string" name="chk_P" id="chk_P" style="width:15rem;" />
        </div> 
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="save" value="Salva" />
                 </div>    
            </div>
        </div>
    </div>
</form>
</div>                                             
';





echo $str_out;
?>
<script type="text/javascript">
    $('document').ready(function(){
        $('#old_P').change(function() {
            if($('#old_P').val()===$('#new_P').val()){
                $("#save").prop("disabled", true);
                $("#span_P").addClass("help-block").html('Password vecchia e nuova devono essere diverse!');

            }else{
                $("#span_P").removeClass("help-block").html('');
                $("#save").prop("disabled", false);
            }
            var old_P = $(this).val();

            $.ajax({
                url: 'ajax/ajx_chk_password.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {old_P: old_P},

                success: function (data) {
                    if(data.Password===0){
                        $("#save").prop("disabled", true);
                        $("#span_O").addClass("help-block").html('Password errata!');
                    }else{
                        $("#span_O").removeClass("help-block").html('');
                        $("#save").prop("disabled", false);
                    }
                }
            });
        });
        $('#new_P').change(function() {
            if($('#old_P').val()===$('#new_P').val()){
                $("#save").prop("disabled", true);
                $("#span_P").addClass("help-block").html('Password vecchia e nuova devono essere diverse!');
            }else{
                $("#save").prop("disabled", false);
            }
        });
        $('#f_user').bootstrapValidator({
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
                new_P: {
                    validators: {
                        stringLength: {
                            min: 8,
                            message: 'Minimo 8 caratteri'
                        },
                        notEmpty: {
                            message: 'Richiesto'
                        },
                        notEqual:{
                            field: 'old_P',
                            message: 'Password uguale a quella vecchia'
                        }
                    }
                },
                chk_P: {
                    validators: {
                        identical: {
                            field: 'new_P',
                            message: 'Password non uguali'
                        },
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                }
            }
        });

    });

</script>
