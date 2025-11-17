<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue("PageTitle", "s");
$CityId= $_SESSION['cityid'];

$rs_PostalCharge = $rs->Select('PostalCharge',"ToDate IS NULL and CityId='$CityId'");
$r_PostalCharge = mysqli_fetch_array($rs_PostalCharge);

$str_out .='
    <form name="f_postal_charge" id="f_postal_charge" class="form-horizontal" action="tbl_postal_charge_upd_exe.php?PageTitle='.$PageTitle.'" method="post">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Spese postali estero
        </div>
         <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Spese CAN.
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="CanFee" id="CanFee" type="text" value="'.$r_PostalCharge['CanFee'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Spese CAD.
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="CadFee" id="CadFee" type="text" value="'.$r_PostalCharge['CadFee'].'">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                VERBALI
            </div>
            <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                SOLLECITI
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel">
                Zona 0
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone0" id="Zone0" type="text" value="'.$r_PostalCharge['Zone0'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Zona 0
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone0" id="ReminderZone0" type="text" value="'.$r_PostalCharge['ReminderZone0'].'">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel">
                Zona 1
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone1" id="Zone1" type="text" value="'.$r_PostalCharge['Zone1'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Zona 1
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone1" id="ReminderZone1" type="text" value="'.$r_PostalCharge['ReminderZone1'].'">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel">
                Zona 2
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone2" id="Zone2" type="text" value="'.$r_PostalCharge['Zone2'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Zona 2
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone2" id="ReminderZone2" type="text" value="'.$r_PostalCharge['ReminderZone2'].'">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel">
                Zona 2
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="Zone3" id="Zone3" type="text" value="'.$r_PostalCharge['Zone3'].'">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Zona 2
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" style="width:28rem" name="ReminderZone3" id="ReminderZone3" type="text" value="'.$r_PostalCharge['ReminderZone3'].'">
            </div>

            <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="update" value="Aggiorna" style="margin-top: 1.2rem;" />
                 </div>    
            </div>
        </div>
    </form>                                   
    ';

echo $str_out;

?>


<script>
    $('document').ready(function () {

    });

</script>

<?php
include(INC . "/footer.php");