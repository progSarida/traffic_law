<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
$rs= new CLS_DB();


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



$rs_Fine = $rs->Select('V_Fine', "StatusTypeId=13 AND ProtocolYear=2022 AND ProtocolId=0 AND ControllerId=".$_SESSION['controllerid']);
$r_Fine = mysqli_fetch_array($rs_Fine);


$rs_FineOwner = $rs->Select('FineOwner', "FineId=".$r_Fine['Id']);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);




$rs_FineDocumentation = $rs->Select('FineDocumentation', "(Documentation LIKE '%.png' OR Documentation LIKE '%.jpg') AND DocumentationTypeId=1 AND FineId=".$r_Fine['Id'], " Documentation");

$str_Img = '';
if(mysqli_num_rows($rs_FineDocumentation)==1){
    $r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation);
    $str_DocumentationPath =  ($r_Fine['VehicleCountry']=='Italia') ? NATIONAL_VIOLATION_HTML : FOREIGN_VIOLATION_HTML;

    $str_Img = '
        <div class="col-xs-12 BoxRowCaption" style="height:10rem;">
            <img src="'. $str_DocumentationPath .'/'. $_SESSION['usercity'] .'/'. $r_FineDocumentation['FineId'] .'/'.$r_FineDocumentation['Documentation'] .'" width="50%" height="100%"/>
        </div>        
        
        <div class="clean_row HSpace4"></div>
    ';
}


$str_out = '
    <div class="row-fluid">
        <div class="col-xs-12">
            <div class="col-xs-2 BoxRowLabel" style="height:4.6rem;">
                <a href="panel.php"><button class="btn btn-lg btn-primary"><--</button></a>
            </div>
            <div class="col-xs-6 BoxRowCaption" style="height:4.6rem;"></div>
            <div class="col-xs-4 BoxRowLabel" style="height:4.6rem;">
                <a href="mgmt_warning_del_exe.php?FineId='. $r_Fine['Id'] .'"><button class="btn btn-lg btn-danger">CANCELLA</button></a>
            </div>
            
            <div class="clean_row HSpace12"></div>
        </div>

        <div class="col-xs-12" style="margin-top:2rem;">
        <form name="f_violation" id="f_violation" method="post" action="mgmt_warning_upd_exe.php">
            <input type="hidden" name="FineId" value="'. $r_Fine['Id'] .'">

            '. $str_Img .'    
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
                <input type="text" class="form-control frm_field_date" value="'. DateOutDB($r_Fine['FineDate']).'"  name="FineDate" id="FineDate" style="width:12rem;" readonly>
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Ora
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_time frm_field_required" name="FineTime" id="FineTime" value="'. TimeOutDB($r_Fine['FineTime']).'" style="width:8rem" readonly>  
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="Address" id="Address"  value="'. $r_Fine['Address'] .'" style="width:20rem">	
            </div>
        
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. CreateSelect("VehicleType","1=1","TitleIta","VehicleTypeId","Id","TitleIta",$r_Fine['VehicleTypeId'],true,15,"frm_field_required") .'
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Nazione
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title",$r_Fine['CountryId'],true,15,"frm_field_required") .'
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Targa
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string frm_field_required" value="'. $r_Fine['VehiclePlate'] .'" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Marca
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string frm_field_required" value="'. $r_Fine['VehicleBrand'] .'"  name="VehicleBrand" id="VehicleBrand" style="width:20rem; text-transform:uppercase">	
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Modello
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" value="'. $r_Fine['VehicleModel'] .'"  name="VehicleModel" id="VehicleModel" style="width:20rem">	
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Colore
            </div>
            <div class="col-xs-8 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" value="'. $r_Fine['VehicleColor'] .'"  name="VehicleColor" id="VehicleColor" style="width:15rem">	
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Infrazione
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. CreateSelectConcat($str_Query_Article,"ArticleId","Id","ArticleDescription","",false,20) .'
            </div>

            <div class="col-xs-4 BoxRowLabel" style="height:6rem;">
                Testo
            </div>
            <div class="col-xs-8 BoxRowCaption" style="height:6rem;">
                <div id="Description" style="width:25rem; height:3rem; margin-bottom:10rem">'. $r_FineOwner['ArticleDescriptionIta'] .'</div>
            </div>
 
            <button class="btn btn-lg btn-primary btn-block" type="submit" >AGGIORNA</button>
        </div>
        </form>
        
        
        <div class="col-xs-12" style="margin-top:2rem;">

            <a href="mgmt_warning_prn_exe.php?FineId='. $r_Fine['Id'] .'"><button class="btn btn-lg btn-warning btn-block">STAMPA</button></a>

        </div>
    </div>    
';



echo $str_out;

?>

<script>

  $('document').ready(function () {

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
