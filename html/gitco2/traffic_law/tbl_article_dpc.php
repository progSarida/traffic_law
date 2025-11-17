<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$a_Lan = unserialize(LANGUAGE);


$Id = CheckValue('Id', 'n');


$rs_Article = $rs->Select('V_Article', "Id=" . $Id, "Year ASC LIMIT 1");
$r_Article = mysqli_fetch_array($rs_Article);





$str_ArticleDescription = '';

for ($i = 1; $i < count($a_Lan); $i++) {
    $str_ArticleDescription .= '
        <div class="col-sm-12" style="height:6rem">
            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                '. $a_Lan[$i] .' <img src="' . IMG . '/' . $aLan[$i] . '" style="width:16px" alt="'. $a_Lan[$i] .'" /> 
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height:6rem">
                <textarea class="frm_field_required frm_field_string" name="Description'. $a_Lan[$i] .'" style="height:5.8rem; width:80rem;">' . StringOutDB($r_Article['ArticleDescription' . $a_Lan[$i]]) . '</textarea>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
    ';
}



$rs_Customer = $rs->Select('Customer', "CityId='" . $r_Article['CityId'] . "'");
$r_Customer = mysqli_fetch_array($rs_Customer);



$str_out .='    
    <div class="col-sm-12">
        <div class="col-sm-12" >
            <form name="f_article" id="f_article" action="tbl_article_add_exe.php">
            <input type="hidden" name="Year" value="'. $r_Article['Year'] .'">
            <input type="hidden" name="Id" value="'. $Id .'">
            <div class="col-sm-12 table_label_H" style="text-align:center">
                Duplica articolo
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Articolo CDS:                    
            </div>
            <div class="col-sm-4 BoxRowLabel">
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Art.
                    </div>    
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" type="text" name="Article" value="'.$r_Article['Article'].'">             
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Comma
                    </div>                        
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Paragraph" value="'.$r_Article['Paragraph'].'">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Lettera
                    </div>     
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Letter" value="'.$r_Article['Letter'].'">
                    </div>
                </div>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Articolo Ente:                   
            </div>
            <div class="col-sm-4 BoxRowLabel"> 
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Art.
                    </div>  
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_numeric frm_field_required" type="text" name="Id1" value="'.$r_Article['Id1'] . '">                  
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Comma
                    </div>                             
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_numeric" type="text" name="Id2" value="'.$r_Article['Id2'] . '">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Lettera
                    </div>         
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_numeric" type="text" name="Id3" value="'.$r_Article['Id3'] . '">
                    </div>  
                </div>
            </div>                       
            <div class="col-sm-1 BoxRowLabel">
                Codice ente
            </div>                    
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="ArtComune" type="text" value="'.$r_Article['ArtComune'].'" style="width:8rem">               
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Ente
            </div>
            <div class="col-sm-2 BoxRowCaption">
               '. CreateSelect("Customer","1=1","ManagerName","CityId","CityId","ManagerName",$_SESSION['cityid'],true) .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Particella verbale
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="ArticleLetterAssigned" id="ArticleLetterAssigned" type="text" value="'.$r_Article['ArticleLetterAssigned'].'" style="width:5rem">
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
               '. CreateSelect("ViolationType","1=1","Id","ViolationTypeId","Id","Title",$r_Article['ViolationTypeId'],true,15) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Anno
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Article['Year'] . '
            </div>

            <div class="clean_row HSpace4"></div>
                                        
            '. $str_ArticleDescription .'

            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Testo addizionale
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height:6rem">
                <textarea class="form-control frm_field_string" name="AdditionalTextIta" style="height:6rem; width:80rem;">'.StringOutDB($r_Article['AdditionalTextIta']).'</textarea>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel" style="height:6rem">
                Note
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height:6rem">
                <textarea class="form-control frm_field_string" name="Note" style="height:6rem; width:80rem;">'.StringOutDB($r_Article['Note']).'</textarea>
            </div>

           <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel">
                Sanzione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_currency frm_field_required" name="Fee" id="Fee" type="text" value="'.$r_Article['Fee'].'" style="width:10rem">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Importo massimo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input  class="form-control frm_field_currency frm_field_required" name="MaxFee" id="MaxFee" type="text" value="'.$r_Article['MaxFee'].'" style="width:10rem">
            </div>                       

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-3 BoxRowLabel">
                Decurtazione punti
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" name="LicensePoint" type="text" value="'.$r_Article['LicensePoint'].'" style="width:10rem">
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Decurtazione neopatentati
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" name="YoungLicensePoint" type="text" value="'.$r_Article['YoungLicensePoint'].'" style="width:10rem">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Presentazione documenti
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="PresentationDocument" type="checkbox" '.ChkCheckButton($r_Article['PresentationDocument']).' />
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Sospensione patente
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="SuspensionLicense" type="checkbox" '.ChkCheckButton($r_Article['SuspensionLicense']).' />
            </div>                
            <div class="col-sm-1 BoxRowLabel">
                Ritiro patente
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="LossLicense" type="checkbox" '.ChkCheckButton($r_Article['LossLicense']).' />
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                Recidivo
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="Habitual" type="checkbox" '.ChkCheckButton($r_Article['Habitual']).' />
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                126 Bis
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="126Bis" type="checkbox" '.ChkCheckButton($r_Article['126Bis']).' />
            </div> 
            <div class="col-sm-2 BoxRowCaption"></div>

            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-1 BoxRowLabel">
                Add.le Massa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="AdditionalMass" type="checkbox" '.ChkCheckButton($r_Article['AdditionalMass']).' />
            </div>
                      
            <div class="col-sm-1 BoxRowLabel">
                Add.le notte
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="AdditionalNight" type="checkbox" '.ChkCheckButton($r_Article['AdditionalNight']).' />
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Riduzione giorni
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="AdditionalNight" type="checkbox" '.ChkCheckButton($r_Article['ReducedPayment']).' />
            </div>
            <div class="col-sm-6 BoxRowCaption"></div> 
                                                                      
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Sanzione addizionale
            </div>   
            <div class="col-sm-10 BoxRowCaption">
                '. CreateSelectShort("AdditionalSanction","1=1","Id","AdditionalSanctionId","Id","TitleIta",$r_Article['AdditionalSanctionId'],false,80) .'
            </div>
            
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12 " style="text-align:center;line-height:6rem;">
                    <input type="submit" class="btn btn-default button" id="update" style="margin-top:1rem;" value="Duplica">
                    <input type="button" onclick="window.location=\'tbl_article.php\'" id="back" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                </div>
            </div>
            </form>	
        </div>   
    </div>
                                                                             
';




echo $str_out;
?>

<script type="text/javascript">
    $('#back').click(function () {
        window.location = "tbl_article.php";
        return false;
    });

    $('#f_article').bootstrapValidator({
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
            }
        }
    }).on('success', function(){
        $('#f_article').submit();
    });


</script>


<?php
include(INC."/footer.php");


