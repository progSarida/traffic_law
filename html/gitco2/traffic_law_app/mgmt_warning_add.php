<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");



$str_Query_Article =
"
SELECT  
AA.Id,
AA.ArticleId,	
AA.Description,
CONCAT(AA.Article,' ',AA.Paragraph,' ',	AA.Letter, ' - ', AT.Fee,' / ',AT.MaxFee) ArticleDescription,

AT.Year,	
AT.Fee,	
AT.MaxFee

FROM ArticleApp AA JOIN ArticleTariff AT ON AA.ArticleId = AT.ArticleId
WHERE AT.Year=". Date("Y") ." AND AA.CityId='". $_SESSION['usercity'] ."'
";


$str_out = '
    <div class="row-fluid">
        <div class="col-xs-12">
            <div class="col-xs-2 BoxRowLabel" style="height:4.6rem;">
                <a href="panel.php"><button class="btn btn-lg btn-primary"><--</button></a>
            </div>
            <div class="col-xs-10 BoxRowCaption" style="height:4.6rem;"></div>
            
            <div class="clean_row HSpace12"></div>
        </div>
        
        <div class="col-xs-12" style="margin-top:2rem;">
        <form name="f_violation" id="f_violation" method="post" action="mgmt_warning_add_exe.php" enctype="multipart/form-data">
        
        
            <div class="col-xs-4 BoxRowLabel">
                Accertatore
            </div>
            <div class="col-xs-8 BoxRowCaption">
                 '. CreateSelect("Controller","Id=" . $_SESSION['controllerid'],"Name","ControllerId","Id","Name",1,true,20,"frm_field_required") .'
            </div>
            
            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Data
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_date" value="'. date("d/m/Y").'"  name="FineDate" id="FineDate" style="width:12rem;" readonly>
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Ora
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_time frm_field_required" name="FineTime" id="FineTime" value="'. date("H:i").'" style="width:8rem" readonly>  
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string frm_field_required" name="Address" id="Address"  style="width:20rem; text-transform:uppercase">	
            </div>
        
            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Carica Documento
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="file" accept="image/*" capture="camera" />
            </div>

            <div class="clean_row HSpace4"></div>                
            <div class="col-xs-4 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. CreateSelect("VehicleType","1=1","TitleIta","VehicleTypeId","Id","TitleIta",1,true,15,"frm_field_required") .'
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Nazione
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title","Z000",true,15,"frm_field_required") .'
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Targa
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string frm_field_required" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase">
            </div>
            
            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Marca
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string frm_field_required" name="VehicleBrand" id="VehicleBrand" style="width:20rem; text-transform:uppercase">	
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Modello
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="VehicleModel" id="VehicleModel" style="width:20rem">	
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Colore
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="VehicleColor" id="VehicleColor" style="width:15rem">	
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Infrazione
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. CreateSelectConcat($str_Query_Article,"ArticleId","Id","ArticleDescription","",false,20,"frm_field_required") .'
            </div>

            <div class="col-xs-4 BoxRowLabel" style="height:6rem;">
                Testo
            </div>
            <div class="col-xs-8 BoxRowCaption" style="height:6rem;">
                <div id="Description" style="width:25rem; height:3rem; margin-bottom:10rem"></div>
            </div>

            <button class="btn btn-lg btn-primary btn-block" type="submit">SALVA</button>
        </div>
        </form>
    </div>    
';



echo $str_out;

?>

<script>

    $('document').ready(function () {
        if(navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position)
            {
              let lat = position.coords.latitude;
              let lng = position.coords.longitude;
                $.ajax({
                    url: 'ajax/search_address.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {LAT:lat, LNG:lng},
                    success: function (data) {

                        $('#Address').val(data.Address);
                    }
                });
        
            });
        }
        
        
        $('#ArticleId').change(function () {

            let ArticleId = $(this).val();
            if(ArticleId!=""){
                $.ajax({
                    url: 'ajax/search_article.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {ArticleId: ArticleId},
                    success: function (data) {
                        $("#Description").text(data.Description);
                    }
                });
            } else $("#Description").text("");
        });

      $('#f_violation').bootstrapValidator({
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
        }
      });


	});
</script>
