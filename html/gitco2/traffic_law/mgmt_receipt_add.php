<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
echo $str_out;
?>

<div class="col-sm-12">
<!--    <h2 class="text-center">Carico Bolettario</h2>-->
<!--    <hr style=" border: 0;height: 1px;background: #333;background-image: linear-gradient(to right, #ccc, #333, #ccc);">-->
        <?php if(isset($_GET['answer'])){
            $class = $_GET['answer'] =='Aggiornato con successo'? 'alert-success':'alert-warning';
            $answer = $_GET['answer'];
            ?><div class="alert <?php echo $class;?>"><?php echo $answer;?></div><?php
        }?>
        <form  class="form-inline" id="f_violation" method="post" action="mgmt_receipt_add_exe.php">

            <div class="col-md-12">
                <div class="col-md-2 form-group BoxRowLabel">
                    <label>Tipo Atto:</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="2" style="position: initial;vertical-align: top;" checked> <label style="line-height:2;vertical-align: top;"> Preavvisi</label>
                </div>
                <div class="col-md-2 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="3" style="position: initial;vertical-align: top;"> <label style="line-height:2;vertical-align: top;"> Verbali</label>
                </div>
                <div class="col-md-6 form-group BoxRowCaption">
                    <input type="radio"  name="select" value="1" style="position: initial;vertical-align: top;"><label style="line-height:2;vertical-align: top;"> Verbali Generici</label>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-md-12">
                <div class="col-md-2 form-group BoxRowLabel" style="height: 31px;">
                    <label>Uso Singolo Accertatore</label>
                </div>
                <div class="col-md-3 form-group BoxRowCaption"  style="height: 31px;">
                    <select class="form-control select_controller" id="select_controller" name="controller" required>
                        <option value="">Seleziona Accertatori</option>
                        <?php
                            $cityid = $_SESSION['cityid'];
                            $accertratore = $rs->SelectQuery("SELECT * FROM Controller WHERE CityId = '".$cityid."'");
                            while($row_acce = mysqli_fetch_array($accertratore)){
                                $Qualification = ($row_acce['Qualification'] != "") ? $row_acce['Qualification']." " : "";
                                ?>
                                    <option value="<?php echo $row_acce['Id'];?>"><?php echo $Qualification.$row_acce['Name']?></option>
                                <?php


                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 form-group BoxRowCaption"  style="height: 31px;">
                    <input type="checkbox" class="isselected" name="multi_accertatore" value="1"> <label style="line-height:2;vertical-align: top;"> Promiscuo (Piu Accertatori)</label>
                </div>
                <div class="col-md-1 form-group BoxRowCaption"  style="height: 31px;">
                    <label style="line-height:2;vertical-align: top;"> Numero blocco: </label>
                </div>
                <div class="col-md-3 form-group BoxRowCaption"  style="height: 31px;">
                    <span id="num_4"></span>
                    <input type="text" class="form-control frm_field_numeric" title="Sono permessi solo numeri!" name="numero_bloco" id="numero_blocco" value="<?=isset($_GET['numero_bloco']) ? $_GET['numero_bloco']: ''?>" style="line-height:2;vertical-align: top;margin-top: 3px;">
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-md-12">

                <div class="col-md-6 form-group">
                    <div class="BoxRowLabel col-sm-4">
                        <label>Dalla lettera / numero</label>
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <span id="pref_1"></span>
                        <input type="text" value="<?=isset($_GET['prefix_1']) ? $_GET['prefix_1']: ''?>" class="prefix_1 form-control frm_field_string" pattern="[A-Za-z]{1,2}" title="Sono permesse solo lettere!" name="prefix_1"  style="width:10rem;" maxlength="2">
                        <input type="text" value="<?=isset($_GET['first_number']) ? $_GET['first_number']: ''?>" class="first_number form-control frm_field_numeric" title="Sono permessi solo numeri!" name="first_number" style="width:22rem;" required>
                        <span id="num_1" class="pull-right"></span>
                    </div>
                </div>
                <div class="col-md-6 form-group ">
                    <div class="BoxRowLabel col-sm-4">
                        <label>Alla lettera / numero</label>
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <span id="pref_2"></span>
                        <input type="text" value="<?=isset($_GET['prefix_2']) ? $_GET['prefix_2']: ''?>" class="prefix_2 form-control frm_field_string  bv-form" pattern="[A-Za-z]{1,2}"
                               title="Sono permesse solo lettere!" name="prefix_2"  style="width:10rem;"  maxlength="2">
                        <input type="text" value="<?=isset($_GET['second_number']) ? $_GET['second_number']: ''?>" class="second_number form-control frm_field_numeric" title="Sono permessi solo numeri!" name="second_number" style="width:22rem;" required>
                        <span id="num_2" class="pull-right"></span>
                        <span id="num_3" class="pull-right"></span>
                    </div>
                </div>

            </div>
<!--            <div class="clean_row HSpace4"></div>-->
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input class="btn btn-default" type="submit" id="add_boletario" name="add_boletario" value="Salva" />
                        <button class="btn btn-default" id="back">Indietro</button>
                    </div>
                </div>
            </div>
        </form>
</div>
<script>
    $(document).ready(function () {

        $(".select_controller").select2();

        $(document).on('input','.form-inline input',function (e) {
            var second_number = parseInt($('.second_number').val());
            var first_number = parseInt($('.first_number').val());
            var numero_blocco = parseInt($('#numero_blocco').val());

            var prefix2 = $('.prefix_2').val();
            var prefix1 = $('.prefix_1').val();
            if (prefix1 !=prefix2){

                $("#add_boletario").hide();

            }else{
                $("#add_boletario").show();
            }

            //
            if($.isNumeric(first_number)==true){
                $("#add_boletario").prop("disabled", false);
                $("#num_1").removeClass("help-block");
                $("#num_1").html('');
            }
            if($.isNumeric(second_number)==true){
                $("#add_boletario").prop("disabled", false);
                $("#num_2").removeClass("help-block");
                $("#num_2").html('');
            }
            if($.isNumeric(numero_blocco)==true){
                $("#add_boletario").prop("disabled", false);
                $("#num_4").removeClass("help-block");
                $("#num_4").html('');
            }
            if (first_number < second_number){

                $("#add_boletario").prop("disabled", false);
                $("#num_3").removeClass("help-block");
                $("#num_3").html('');

            }

            ///////////////////////////////

            var hasNumber = /\d/;
            if (hasNumber.test(prefix1) == true){
                $("#add_boletario").prop("disabled", true);
                $("#pref_1").addClass("help-block");
                $("#pref_1").html('In questo campo è permesso l’inserimento di sole lettere!');

            }else{
                $("#add_boletario").prop("disabled", false);
                $("#pref_1").removeClass("help-block");
                $("#pref_1").html('');

            }

            if (hasNumber.test(prefix2) == true){
                $("#add_boletario").prop("disabled", true);
                $("#pref_2").addClass("help-block");
                $("#pref_2").html('In questo campo è permesso l’inserimento di sole lettere!');

            }else{
                $("#add_boletario").prop("disabled", false);
                $("#pref_2").removeClass("help-block");
                $("#pref_2").html('');

            }


        });

        $(document).on('click','.isselected',function() {

            if($(this).is(':checked')) {
                $('#select_controller').attr('disabled', true);
                $('#select_controller').prop("selectedIndex", 0).change();
            }else{
                $('#select_controller').attr('disabled', false);
            }
        });
        $('#back').click(function () {
            window.location = "mgmt_receipt.php<?php echo $str_GET_Parameter;?>";
            return false;
        });

        $("#f_violation").submit(function(e){
            e.preventDefault();
            var validation = true;
            var second_number = parseInt($('.second_number').val());
            var first_number = parseInt($('.first_number').val());
            var numero_blocco = $('#numero_blocco').val();
            if (first_number >= second_number){

                $("#add_boletario").prop("disabled", true);
                $("#num_3").addClass("help-block");
                $("#num_3").html('Il secondo numero dovrebbe essere più grande del primo!');
                validation = false;

            }
            if (numero_blocco !=""){
                if($.isNumeric(numero_blocco)==false){
                    $("#add_boletario").prop("disabled", true);
                    $("#num_4").addClass("help-block");
                    $("#num_4").html('In questo campo è permesso l’inserimento di soli valori numerici!');
                    validation = false;
                }
            }

            if($.isNumeric(first_number)==false){
                $("#add_boletario").prop("disabled", true);
                $("#num_1").addClass("help-block");
                $("#num_1").html('In questo campo è permesso l’inserimento di soli valori numerici!');
                validation = false;
            }else{
                $("#add_boletario").prop("disabled", false);
                $("#num_1").removeClass("help-block");
                $("#num_1").html('');
               // var validation = true;
            }
            if($.isNumeric(second_number)==false){
                $("#add_boletario").prop("disabled", true);
                $("#num_2").addClass("help-block");
                $("#num_2").html('In questo campo è permesso l’inserimento di soli valori numerici!');
                validation = false;
            }else{
                $("#add_boletario").prop("disabled", false);
                $("#num_2").removeClass("help-block");
                $("#num_2").html('');
                //var validation = true;
            }


            if (validation) {
                $("#f_violation")[0].submit();
            }
        });


    });

</script>