<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
echo $str_out;
?>

<div class="col-md-12">
        <?php
        if(isset($_GET['answer'])){
            $answer = $_GET['answer'];
            $class = $answer =='L\'intervallo di numeri definito si sovrappone a quello di un bollettario già esistente per questo blocco/lettera'? 'alert-warning':'alert-success';
            $answer = $_GET['answer'];
            ?><div class="alert <?php echo $class;?>"><?php echo $answer;?></div><?php
        }
        $cityid = $_SESSION['cityid'];
        $boletario_id = $_GET['boletario'];
        $boletario = $rs->SelectQuery("select Receipt.*,Controller.Id as ContId,Controller.Name FROM Receipt left JOIN Controller on Receipt.ControllerId = Controller.Id WHERE Receipt.CityId = '" . $cityid . "' and Receipt.Id = ".$boletario_id."");
        while ($row_boletario = mysqli_fetch_array($boletario)) {
        ?>
            <form class="form-inline" method="post" id="f_violation" action="mgmt_receipt_add_exe.php">
                <input type="hidden" name="boletario_id" value="<?php echo $boletario_id;?>">
                <div class="col-md-12">
                    <div class="col-md-2 form-group BoxRowLabel">
                        <label>Tipo Atto (Scegliere tra):</label>
                    </div>
                    <div class="col-md-1 form-group BoxRowCaption">
                        <input type="radio" name="select" value="2" style="position: initial;vertical-align: top;" id="select_tipo" <?php echo $selected = $row_boletario['TipoAtto'] == 2? 'checked':'';?>> <label style="line-height:1.8;vertical-align: top;"> Preavvisi</label>
                    </div>
                    <div class="col-md-1 form-group BoxRowCaption">
                        <input type="radio" name="select" value="3" style="position: initial;vertical-align: top;" id="select_tipo" <?php echo $selected = $row_boletario['TipoAtto'] == 3? 'checked':'';?>> <label style="line-height:1.8;vertical-align: top;"> Verbali</label>
                    </div>
                    <div class="col-md-1 form-group BoxRowCaption">
                        <input type="radio" name="select" value="1" style="position: initial;vertical-align: top;" id="select_tipo" <?php echo $selected = $row_boletario['TipoAtto'] == 1? 'checked':'';?>> <label style="line-height:1.8;vertical-align: top;"> Verbali Generici</label>
                    </div>
                    <div class="col-md-7 form-group BoxRowCaption"></div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-md-12">
                    <div class="col-md-2 form-group BoxRowLabel"  style="height: 30px;">
                        <label>Uso Accertatore</label>
                    </div>
                    <div class="col-md-3 form-group BoxRowCaption"  style="height: 30px;">
                        <select class="form-control bv-form select_controller" id="select_controller" name="controller" required>
                            <option value="">Seleziona Accertatori</option>
                            <?php
                            $cityid = $_SESSION['cityid'];
                            $accertratore = $rs->SelectQuery("SELECT * FROM Controller WHERE CityId = '" . $cityid . "'");
                            while ($row_acce = mysqli_fetch_array($accertratore)) {
                                $Qualification = ($row_acce['Qualification'] != "") ? $row_acce['Qualification']." " : "";
                                if ($row_boletario['ControllerId'] == $row_acce['Id']){
                                    ?>
                                    <option value="<?php echo $row_boletario['ControllerId'];?>" selected><?php echo $Qualification.$row_boletario['Name'] ?></option>
                                    <?php
                                }else{
                                    ?>
                                    <option value="<?php echo $row_acce['Id'];?>"><?php echo $Qualification.$row_acce['Name'];?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-7 BoxRowCaption"  style="height: 30px;">

                            <input type="checkbox" class="isselected" name="multi_accertatore" <?php echo $selected = $row_boletario['ControllerId'] == 0? 'checked':'';?> value="1">
                            <label style="line-height:1.8;vertical-align: top;"> Promiscuo</label>

                    </div>

                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-md-12">
                    <div class="col-md-6 form-group">
                        <div class="BoxRowLabel col-sm-4">
                            <label>Dalla lettera / numero</label>
                        </div>
                        <div class="BoxRowCaption col-sm-8">
                            <input type="text" class="prefix_1 form-control frm_field_string  bv-form" name="prefix_1" pattern="[A-Za-z]{1,2}"
                                   title="Sono permesse solo lettere!"  style="width:8rem;" value="<?php echo $row_boletario['Preffix'];?>" maxlength="2">
                            <input type="text" class="first_number form-control frm_field_numeric  bv-form" name="first_number_edit"
                                   title="Sono permessi solo numeri!" value="<?php echo $row_boletario['StartNumber'];?>" style="width:15rem;" required>
                            <span id="num_1"></span>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <div class="show_error alert-warning"></div>
                        <div class="BoxRowLabel col-sm-4">
                            <label>Alla lettera / numero</label>
                        </div>
                        <div class="BoxRowCaption col-sm-8">
                            <input type="text" class="prefix_2 form-control frm_field_string" name="prefix_2" pattern="[A-Za-z]{1,2}"
                                   title="Sono permesse solo lettere!"  style="width:8rem;" value="<?php echo $row_boletario['Preffix'];?>" maxlength="2">
                            <input type="text" class="second_number form-control frm_field_numeric" name="second_number"
                                   title="Sono permessi solo numeri!" value="<?php echo $row_boletario['EndNumber'];?>" style="width:15rem;" required>
                            <span id="num_2" class="pull-right"></span>
                            <span id="num_3" class="pull-right"></span>

                        </div>
                    </div>
                    <div class="col-md-2 open_input">
                            <input type="hidden" value="<?php echo $row_boletario['NumPage'];?>" name="nr_page">
                        <?php
                            if ($row_boletario['Scaricato']==1){
                                echo "Scaricato alla pagina numero: ".$row_boletario['NumPage']."";
                            }else{
                                ?>
                                <div class="BoxRowLabel col-sm-6">Scarica</div>
                                <div class="BoxRowCaption col-sm-6"><input type="checkbox" name="scarica" value="1" class="scarica"></div>

                                <?php
                            }
                        ?>

                    </div>
                </div>

            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input class="btn btn-default" type="submit" id="edit_boletario" name="edit_boletario" value="Salva" />
                        <button class="btn btn-default" id="back">Indietro</button>
                    </div>
                </div>
            </div>
        </form>
    <?php
    }
    ?>
</div>

<script>
   $(document).ready(function () {
       $('.select_controller').select2();
       $(document).on('change','.scarica',function() {
           if($(this).is(':checked')){
               $('.open_input').append('<div id="nr_page"><label>Inserire ultimo numero!</label>' +
                   '<input required type="number" name="nr_page"  title="Sono permessi solo numeri!" class="form-control BoxRowCaption" id="numpage"></div>')

           }else{
               $('#nr_page').remove();
           }
       });

       $(document).on('input','.form-inline input',function (e) {
           var second_number = parseInt($('.second_number').val());
           var first_number = parseInt($('.first_number').val());

           var prefix2 = $('.prefix_2').val();
           var prefix1 = $('.prefix_1').val();

           if (prefix1 != prefix2){
               $('#edit_boletario').hide()
           }else{
               $('#edit_boletario').show()
           }


           if($.isNumeric(first_number)==true){
               $("#edit_boletario").prop("disabled", false);
               $("#num_1").removeClass("help-block");
               $("#num_1").html('');
           }
           if($.isNumeric(second_number)==true){
               $("#edit_boletario").prop("disabled", false);
               $("#num_2").removeClass("help-block");
               $("#num_2").html('');
           }

           if (first_number < second_number){

               $("#edit_boletario").prop("disabled", false);
               $("#num_3").removeClass("help-block");
               $("#num_3").html('');

           }

       });

       $("#f_violation").submit(function(e){
           e.preventDefault();
           var validation = true;
           var second_number = parseInt($('.second_number').val());
           var first_number = parseInt($('.first_number').val());

           if (first_number >= second_number){

               $("#edit_boletario").prop("disabled", true);
               $("#num_3").addClass("help-block");
               $("#num_3").html('Il secondo numero dovrebbe essere più grande del primo!');
               validation = false;

           }

           if($.isNumeric(first_number)==false){
               $("#edit_boletario").prop("disabled", true);
               $("#num_1").addClass("help-block");
               $("#num_1").html('In questo campo è permesso l’inserimento di soli valori numerici!');
               validation = false;
           }else{
               $("#edit_boletario").prop("disabled", false);
               $("#num_1").removeClass("help-block");
               $("#num_1").html('');

           }
           if($.isNumeric(second_number)==false){
               $("#edit_boletario").prop("disabled", true);
               $("#num_2").addClass("help-block");
               $("#num_2").html('In questo campo è permesso l’inserimento di soli valori numerici!');
               validation = false;
           }else{
               $("#edit_boletario").prop("disabled", false);
               $("#num_2").removeClass("help-block");
               $("#num_2").html('');

           }


           if (validation) {
               $("#f_violation")[0].submit();
           }
       });
       $(document).on('keyup','#numpage',function (e) {
           var number = parseInt($('#numpage').val());
           var second_number1 = parseInt($('.second_number').val());
           var first_number1 = parseInt($('.first_number').val());



           if ( number >=first_number1 && number < second_number1){
               $('#edit_boletario').removeAttr('disabled');
           }else{
               $('#edit_boletario').attr('disabled', 'false');
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
   });

</script>