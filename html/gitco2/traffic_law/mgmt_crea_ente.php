<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/menu_".$_SESSION['UserMenuType'].".php");

echo $str_out;
?>
<div class="row-fluid">
    <form name="f_upd_customer" id="f_upd_customer" class="form-horizontal" action="mgmt_crea_ente_exe.php" method="post">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Creazione Ente
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Codice Catastale
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?= CreateSelectQuery("select Id, concat(Id,' ',Title) as label from ". MAIN_DB.".City where Id not in (select distinct CityId from traffic_law.Customer)", "ManagerCityId", "Id", "label",null,true); ?>
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Nome
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" name="ManagerName" id="ManagerName" type="text">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" name="ManagerAddress" id="ManagerAddress" type="text" >
            </div>
            <div class="col-sm-1 BoxRowLabel">
                CAP
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" name="ManagerZIP" id="ManagerZIP" type="text" >
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" name="ManagerCity" id="ManagerCity" type="text" valu>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Provincia
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" name="ManagerProvince" id="ManagerProvince" type="text" ">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Stato
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string frm_field_required" name="ManagerCountry" id="ManagerCountry" type="text">
            </div>
    
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-1 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="ManagerPhone" id="ManagerPhone" type="text" >
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Fax
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="ManagerFax" id="ManagerFax" type="text" >
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Email
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="ManagerMail" id="ManagerMail" type="text" >
            </div>
            <div class="col-sm-1 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="ManagerPEC" id="ManagerPEC" type="text" >
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Codice IPA
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="IpaCode" id="IpaCode" type="text" >
            </div>
    
    		<div class="clean_row HSpace4"></div>
    
    		<div class="col-sm-12 BoxRow" style="text-align:center;height:5rem">
            	<input type="submit" class="btn btn-default" id="save" style="margin-top:1rem;"  value="Salva" />
         	</div>
    	</div>
    </form>
</div>
<script type='text/javascript'>
    $('document').ready(function(){
         $('#ManagerCityId').change(function () {
            var CityId = $(this).val();
            $.ajax({
                url: 'ajax/ajx_get_city.php',
                type: 'GET',
                dataType: 'json',
                cache: false,
                data: {CityId: CityId,Title:null,Province:null,Zip:null,Country:null},
                success:function(data) {
                    $('#ManagerName').val(data.Title);
                    $('#ManagerProvince').val(data.Province);
                    $('#ManagerZIP').val(data.Zip);
                      $('#ManagerCountry').val(data.Country);

                }
            });
        });
		 $('#f_upd_customer').bootstrapValidator({
			live: 'disabled',
			fields: {
				frm_field_required: {
					selector: '.frm_field_required',
					validators: {
						notEmpty: {
							message: 'Richiesto'
						}
					}
				}
			}
		});

    });
</script>
<?php
include(INC . "/footer.php");
