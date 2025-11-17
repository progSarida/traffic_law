<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$rs = new CLS_DB();
echo $str_out;
$cityid = CheckValue('city','s');
$id = CheckValue('id', 'n');
$row = $rs->SelectQuery("select traffic_law.JudicialOffice.*,traffic_law.Office.* 
        from traffic_law.JudicialOffice 
        join traffic_law.Office on traffic_law.JudicialOffice.OfficeId = traffic_law.Office.Id WHERE traffic_law.JudicialOffice.CityId='$cityid' AND traffic_law.Office.Id=$id");
$uffico = $rs->SelectQuery("select * from Office");
$row = mysqli_fetch_array($row);
?>
    <div class="col-sm-12">
        <form id="FormRilevatori" name="FormRilevatori" style="margin-top:10px" method="post" action="mgmt_judicial_office_upd_exe.php">
            <div class="row-fluid">
                <div class="col-sm-12">
                    <div class="BoxRowTitle" id="BoxRowTitle">
                        Inserimento  Dati
                    </div>
                    <input type="hidden" name="cityid" value="<?=$row['CityId']?>">
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Ufficio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <select class="form-control" name="Ufficio" style="width: 300px;">
                            <?php
                            echo "<option></option>";
                            while($row2 = mysqli_fetch_array($uffico)){
                                ?>
                                <option value="<?=$row2['Id']?>" <?php
                                if($row2['Id']==$id) echo "selected"
                                ?>><?=$row2['TitleIta']?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Citta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Citta"  class="form-control frm_field_string"  style="width: 300px;" value="<?=$row['City']?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Provincia"  class="form-control frm_field_string"  style="width: 300px;" max="2" value="<?=$row['Province']?>" maxlength="2">
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Indirizzo
                    </div>

                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Indirizzo" class="form-control frm_field_string"  style="width: 300px;" value="<?=$row['Address']?>">
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Zip
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Zip" class="form-control frm_field_numeric"  style="width: 300px;" value="<?=$row['ZIP']?>" maxlength="6">
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Nr. Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Telefono"class="form-control frm_field_string"  style="width: 300px;" value="<?=$row['Phone']?>">
                    </div>

                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Fax" class="form-control frm_field_string" style="width: 300px;"  value="<?=$row['Fax']?>">
                    </div>


                    <div class="col-sm-1 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Mail" id="Model" class="form-control frm_field_string" style="width: 300px;" value="<?=$row['Mail']?>">
                    </div>


                    <div class="col-sm-1 BoxRowLabel">
                        Pec
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Pec" id="TitleIta" class="form-control frm_field_string" style="width: 300px;"  value="<?=$row['PEC']?>">
                    </div>

                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="Web" id="Omologazione" class="form-control frm_field_string" style="width: 300px;"   value="<?=$row['Web']?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Disattivato
                    </div>
                    <div class="col-sm-7 BoxRowCaption">
                        <input type="checkbox" name="Disattivato" id="Kind" class="" style="width: 50px;" <?php if($row['Disabled']==1) echo "checked"?>>
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