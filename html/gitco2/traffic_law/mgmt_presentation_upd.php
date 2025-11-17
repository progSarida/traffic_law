<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');




$Id= CheckValue('Id','n');


$rs_Trespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id);
$r_Trespasser = mysqli_fetch_array($rs_Trespasser);


$LicenseDate = "";
if($r_Trespasser['LicenseDate']!="" OR !is_null($r_Trespasser['LicenseDate']))  $LicenseDate = DateOutDB($r_Trespasser['LicenseDate']);

$inserted = $rs->SelectQuery("SELECT * FROM FinePresentation WHERE FineId = $Id ");
$inserted_value = mysqli_fetch_array($inserted)['DocumentationTypeId'];

$str_out .='
        <form name="f_comm_upd" id="f_comm_upd" action="mgmt_presentation_upd_exe.php" method="post" enctype="multipart/form-data">
        <input type="hidden" id="TrespasserId" name="TrespasserId">
        <input type="hidden" name="MainTrespasserId" value="'.$r_Trespasser['TrespasserId'].'">
        <input type="hidden" name="FineId" value="'.$Id.'">
        <input type="hidden" name="CountryId" value="'.$r_Trespasser['VehicleCountryId'].'">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        	    <div class="col-sm-12">

                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel">
                            Data presentazione documento
                        </div>
                        <div class="col-sm-7 BoxRowCaption">
                            <input class="form-control frm_field_date frm_field_required" type="text" id="PresentationDate" name="PresentationDate"  style="width:12rem" />
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel">
                            Nominativo
                        </div>
                        <div class="col-sm-7 BoxRowCaption">
                            ' . $r_Trespasser['CompanyName'] . ' ' . $r_Trespasser['Surname'] . ' '. $r_Trespasser['Name'] . '
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Documento
                        </div>
                        <div class="col-sm-10 BoxRowCaption">
                             
                            <span id="span_DocumentTypeId" style="width:40rem">
                                    <select name="DocumentationTypeId" style="width:20rem">';

                                        $document = $rs->SelectQuery("SELECT * FROM DocumentationType WHERE Id >= 25 AND Id <= 28");
                                        if (mysqli_num_rows($inserted) > 0){
                                            while($row = mysqli_fetch_array($document)){
                                                if ($inserted_value!=$row['Id']) $str_out.='<option value="'.$row['Id'].'"';
                                                if($row['Id']==26) $str_out.=' SELECTED ';
                                                $str_out.='>'.$row['Title'].'</option>';
                                            }
                                        }else{
                                            while($row = mysqli_fetch_array($document)){
                                                $str_out.='<option value="'.$row['Id'].'"';
                                                if($row['Id']==26) $str_out.=' SELECTED ';
                                                $str_out.='>'.$row['Title'].'</option>';

                                            }
                                        }

                                    $str_out .='</select>   
                            </span>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-12">
    
                        <div class="col-sm-2 BoxRowLabel">
                            Caricare Documento
                        </div>
                        <div class="col-sm-10 BoxRowCaption">
                             
                            <input type="file" name="image" id="image">
                        </div>
                        
                    </div>
                    <div class="clean_row HSpace4"></div>
                   <div class="col-sm-12" style="height:6.4rem;">
                        <div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
                            Note
                        </div>
                        <div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                            <textarea name="Note" class="form-control frm_field_string" style="width:40rem;height:5.5rem"></textarea>	
                        </div>
  				    </div>
                   

                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
                        <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                            <input class="btn btn-default" type="submit" id="save" value="Salva" />
                            <button class="btn btn-default" id="back">Indietro</button>
                        </div>
                    </div>
    
                </div>
            </div>    
        </div>
        </form>
	</div>';



echo $str_out;
?>


<script type="text/javascript">

    $('document').ready(function(){

        var min_length = 3;
        var VehiclePlate = '';
        var Id = 0;
        var Genre = "M";


        $('#f_comm_upd').bootstrapValidator({
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


        $('#Surname_S').keyup(function(){
            var Name = $('#Name_S').val();
            var Surname = $(this).val();

            if (Surname.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){


                        $('#trespasser_content').html(data.Trespasser).show();
                    }
                });
            } else {
                $('#trespasser_content').hide();
            }
        });


        $('#Name_S').keyup(function(){
            var Name = $(this).val();
            var Surname = $('#Surname_S').val();


            if (Surname.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){


                        $('#trespasser_content').html(data.Trespasser).show();

                    }
                });
            } else {
                $('#trespasser_content').hide();
            }
        });


        $('#back').click(function () {
            window.location = "<?= $str_BackPage ?>";
            return false;
        });





        $("input:radio[name='TrespasserTypeId']").click(function() {
            if($("input:radio[name='TrespasserTypeId']:checked").val()==1){
                $('#othertrespasser').hide();
                $('#span_DocumentTypeId').hide();
                $('#span_DocumentTitle').show();

            }else{
                $('#othertrespasser').show();
                $('#span_DocumentTypeId').show();
                $('#span_DocumentTitle').hide();
            }
        });

    });
</script>
<?php
include(INC."/footer.php");
