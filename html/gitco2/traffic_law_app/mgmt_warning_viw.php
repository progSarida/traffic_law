<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
$rs= new CLS_DB();

$FineId          = CheckValue('FineId','n');

$rs_Fine = $rs->Select('V_Fine', "Id=".$FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);


$rs_FineOwner = $rs->Select('FineOwner', "FineId=".$r_Fine['Id']);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);



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



$rs_Country = $rs->Select('Country', "Id='".$r_Fine['CountryId']."'");
$str_Country = mysqli_fetch_array($rs_Country)['Title'];

$rs_VehicleType = $rs->Select('VehicleType', "Id=".$r_Fine['VehicleTypeId']);
$str_VehicleType = mysqli_fetch_array($rs_VehicleType)['TitleIta'];


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
            <div class="col-xs-4 BoxRowLabel" style="height:4.6rem;"></div>
            
            <div class="clean_row HSpace12"></div>
        </div>

        <div class="col-xs-12" style="margin-top:2rem;">
        <form name="f_violation" id="f_violation" method="post" action="mgmt_warning_upd_exe.php">
       
            '. $str_Img .'    
                   
            <div class="col-xs-4 BoxRowLabel">
                Data
            </div>
            <div class="col-xs-8 BoxRowCaption">
               '. DateOutDB($r_Fine['FineDate']).'
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Ora
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. TimeOutDB($r_Fine['FineTime']).'
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. $r_Fine['Address'] .'	
            </div>
        
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. $str_VehicleType .'
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Nazione
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. $str_Country .'
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Targa
            </div>
            <div class="col-xs-8 BoxRowCaption">
               '. $r_Fine['VehiclePlate'] .'
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-xs-4 BoxRowLabel">
                Marca
            </div>
            <div class="col-xs-8 BoxRowCaption">
                '. $r_Fine['VehicleBrand'] .'
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Modello
            </div>
            <div class="col-xs-8 BoxRowCaption">
               '. $r_Fine['VehicleModel'] .'	
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel">
                Colore
            </div>
            <div class="col-xs-8 BoxRowCaption">
               '. $r_Fine['VehicleColor'] .'
            </div>
            
            <div class="clean_row HSpace4"></div>

            <div class="col-xs-4 BoxRowLabel" style="height:6rem;">
                Testo
            </div>
            <div class="col-xs-8 BoxRowCaption" style="height:6rem;">
                <div id="Description" style="width:25rem; height:3rem; margin-bottom:10rem">'. $r_FineOwner['ArticleDescriptionIta'] .'</div>
            </div>
        </div>
        </form>
    </div>    
';



echo $str_out;

?>
