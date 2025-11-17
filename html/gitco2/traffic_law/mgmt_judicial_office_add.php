<?php


include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
$rs = new CLS_DB();
if (isset($_POST['insert'])){




    if(isset($_POST['Disattivato'])){
        $disable = 1;
    }else{
        $disable = 0;
    }
    if ($_SESSION['usertype'] >= 50){
        $cityId = $_POST['CityId'];
    }else{
        $cityId = $_SESSION['cityid'];
    }
    $check = $rs->SelectQuery("select * from JudicialOffice where CityId = '$cityId' and OfficeId = '".$_POST['Ufficio']."'");
    $nr = mysqli_num_rows($check);
    if ($nr == 0) {
        $aJudicalOffice = array(
            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $cityId),
            array('field' => 'OfficeId', 'selector' => 'value', 'type' => 'int', 'value' => $_POST['Ufficio'], 'settype' => 'int'),
            array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Citta']),
            array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Provincia']),
            array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Indirizzo']),
            array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Zip']),
            array('field' => 'Phone', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Telefono']),
            array('field' => 'Fax', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Fax']),
            array('field' => 'Mail', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Mail']),
            array('field' => 'PEC', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Pec']),
            array('field' => 'Web', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Web']),
            array('field' => 'Disabled', 'selector' => 'value', 'type' => 'int', 'value' => $disable, 'settype' => 'int'),

        );
        $rs->Insert('JudicialOffice', $aJudicalOffice);
        header("location:mgmt_judicial_office.php?answer=Inserito con successo!");
    }else{
        header("location:mgmt_judicial_office_add.php?answer=Ufficio giudiziario essiste!");
    }

} else {
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

echo $str_out;
$uffico = $rs->SelectQuery("select * from Office");
$commune = $rs->SelectQuery("select * from sarida.City");

?>

<div class="col-sm-12">
    <?php if(isset($_GET['answer'])){
        $answer = $_GET['answer'];
        ?><div class="alert alert-warning"><?php echo $answer;?></div><?php
    }?>
    <form id="FormRilevatori" name="FormRilevatori" style="margin-top:10px" method="post" action="">

        <div class="row-fluid">
            <div class="col-sm-12">
                <div class="BoxRowTitle" id="BoxRowTitle">
                    Inserimento  Dati
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                    Commune
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <?php
                    if ($_SESSION['usertype'] >= 50) {
                       ?>
                        <select class="form-control" name="CityId" style="width: 300px;">
                            <?php
                            echo "<option></option>";
                            while($row_commune = mysqli_fetch_array($commune)){
                                ?>
                                <option value="<?=$row_commune['Id']?>"><?=$row_commune['Title']?></option>
                                <?php
                            }
                            ?>
                        </select>
                       <?php
                    }else{
                        echo $_SESSION['citytitle'];
                    }
                    ?>

                </div>
                <div class="col-sm-1 BoxRowLabel">
                 Ufficio
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <select class="form-control" name="Ufficio" style="width: 300px;">
                        <?php
                            echo "<option></option>";
                            while($row = mysqli_fetch_array($uffico)){
                                ?>
                                    <option value="<?=$row['Id']?>"><?=$row['TitleIta']?></option>
                                <?php
                            }
                        ?>
                    </select>
                </div>

                <div class="col-sm-1 BoxRowLabel">
                    Citta
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Citta" id="Kind" class="form-control frm_field_string" required style="width: 300px;">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Provincia
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Provincia" id="Kind" class="form-control frm_field_string" required style="width: 300px;" maxlength="2">
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                   Indirizzo
                </div>

                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Indirizzo" id="Kind" class="form-control frm_field_string" required style="width: 300px;">
                </div>

                <div class="col-sm-1 BoxRowLabel">
                    Zip
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Zip" id="Kind" class="form-control frm_field_numeric" required style="width: 300px;" maxlength="6">
                </div>

                <div class="col-sm-1 BoxRowLabel">
                  Nr. Telefono
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Telefono" id="Kind" class="form-control frm_field_string" required style="width: 300px;">
                </div>

                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                   Fax
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Fax" id="Kind" class="form-control frm_field_string" style="width: 300px;" required>
                </div>


                <div class="col-sm-1 BoxRowLabel">
                   Mail
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Mail" id="Model" class="form-control frm_field_string" style="width: 300px;" required>
                </div>


                <div class="col-sm-1 BoxRowLabel">
                   Pec
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Pec" id="TitleIta" class="form-control frm_field_string" style="width: 300px;" required >
                </div>

                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                  Web
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" name="Web" id="Omologazione" class="form-control frm_field_string" style="width: 300px;"   >
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Disattivato
                </div>
                <div class="col-sm-7 BoxRowCaption">
                    <input type="checkbox" name="Disattivato" id="Kind" class="" style="width: 50px;" >
                </div>

                <!--div class="col-sm-1 BoxRowLabel">Vel. obbligatoria</div>
                <div class="col-sm-5 BoxRowCaption">
                    <input type="checkbox" name="Obbligatoria">
                </div-->

            </div>
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input type="submit" class="btn btn-default" name="insert" id="insert" value="Salva" style="display: inline-block;">
                        <input type="button" class="btn btn-default" id="back" value="Indietro">
                    </div>
                </div>
            </div>
    </form>
</div>
<script>
    $(document).ready(function () {
        // $('#FormRilevatori').bootstrapValidator({
        //     live: 'disabled',
        //     fields: {
        //         frm_field_required: {
        //             selector: '.frm_field_required',
        //             validators: {
        //                 notEmpty: {
        //                     message: 'Richiesto'
        //                 }
        //             }
        //         },
        //
        //         frm_field_numeric: {
        //             selector: '.frm_field_numeric',
        //             validators: {
        //                 numeric: {
        //                     message: 'Numero'
        //                 }
        //             }
        //         },
        //     }
        // })

        $('#back').click(function () {
            window.location = "mgmt_judicial_office.php<?php echo $str_GET_Parameter;?>";
            return false;
        });

    });

</script>
<?php }