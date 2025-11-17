<?php

include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
$rs = new CLS_DB();
if (isset($_POST['insert'])){
    if (isset($_POST['Obbligatoria'])){
        $fixed = 1;
    }else{
        $fixed=0;
    }
    $aDetector= array(
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
        array('field' => 'Kind', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Kind']),
        array('field' => 'Brand', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Brand']),
        array('field' => 'TitleIta', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['TitleIta']),
        array('field' => 'Model', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Model']),
        array('field' => 'Ratification', 'selector' => 'value', 'type' => 'str', 'value' => $_POST['Omologazione']),
        array('field' => 'Fixed', 'selector' => 'value', 'type' => 'int', 'value' => $fixed,'settype' => 'int'),
        array('field' => 'Tolerance', 'selector' => 'value', 'type' => 'flt', 'value' => $_POST['Tolerance'], 'settype' => 'flt'),

        //array('field' => 'Code', 'selector' => 'value', 'type' => 'int', 'value' => $endnumber, 'settype' => 'int'),

    );
    $insert = $rs->Insert('Detector', $aDetector);
    if ($insert){
        header("location: tbl_detector.php?PageTitle=Gestione/Rilevatori&answer=Inserito conn successo!");
    }
}
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
echo $str_out;

?>

<div class="col-sm-12">
    <?php if(isset($_GET['answer'])){
        $answer = $_GET['answer'];
        ?><div class="alert alert-success"><?php echo $answer;?></div><?php
    }?>
    <form id="FormRilevatori" name="FormRilevatori" style="margin-top:10px" method="post" action="tbl_detector_add_new.php">

        <div class="row-fluid">
            <div class="col-sm-12">
                <div class="BoxRowTitle" id="BoxRowTitle">
                    Inserimento nuovo Rilevatore
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                    Tipo
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Kind" id="Kind" class="form-control frm_field_string frm_field_required" required>
                </div>

                <div class="col-sm-1 BoxRowLabel">
                    Marca
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Brand" id="Brand" class="form-control frm_field_string frm_field_required" required>
                </div>

                <div class="col-sm-1 BoxRowLabel">
                    Modello
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Model" id="Model" class="form-control frm_field_string frm_field_required" required>
                </div>


                <div class="col-sm-1 BoxRowLabel">
                    Tolleranza del
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Tolerance" id="Tolerance" class="form-control frm_field_numeric" pattern="^[0-9]{1}(\.[0-9]{1,2})?$" title="Si prega di consentire solo nel formato scritto" style="width: 15rem;" >
                </div>

                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                    Testo su verbale
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="TitleIta" id="TitleIta" class="form-control frm_field_string frm_field_required"required >
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Omologazione
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="Omologazione" id="Omologazione" class="form-control frm_field_string" >
                </div>
                <div class="col-sm-1 BoxRowLabel">Vel. obbligatoria</div>
                <div class="col-sm-5 BoxRowCaption">
                    <input type="checkbox" name="Obbligatoria">
                </div>

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
            window.location = "tbl_detector.php<?php echo $str_GET_Parameter;?>";
            return false;
        });

    });

</script>



