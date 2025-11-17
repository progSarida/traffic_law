<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_table.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_CurrentPage = curPageName()."?1=1";


$Id= CheckValue('Id','s');
$CityId= CheckValue('CityId','s');

$rs= new CLS_DB();

$articles = $rs->Select('Article',"Id='$Id'", "Id");
$article = mysqli_fetch_array($articles);

$comunes = $rs->Select('Customer',"CityId='$CityId'");
$comune = mysqli_fetch_array($comunes);



$tariffes = $rs->Select('ArticleTariff',"ArticleId=".$article['Id']." AND Year=".$_SESSION['year']);
$tariff = mysqli_fetch_array($tariffes);




$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
				</div>
         	</div>
        </div>
        
        <div class="row-fluid">
        	<div class="col-sm-12">
        	  	<div class="col-sm-12 BoxRow" >
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				Modifica articolo
					</div>
  				</div>
            </div> 
        </div> 
    	<div class="row-fluid" style="margin-top:2rem;">
    	    <form name="f_article" method="post" action="tbl_article_del_exe.php">
    	     <input name="Id" type="hidden" value="'.$Id.'" />
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Articolo cds
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['Article'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Comma cds
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['Paragraph'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Lettera cds
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['Letter'].'
                    </div>
                </div> 
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        City
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$comune['ManagerName'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Violazione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['ViolationTypeId'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Anno
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$tariff['Year'].'
                    </div>
                </div> 
            </div>      
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Articolo ente
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['Id1'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Comma ente
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['Id2'].'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Lettera ente
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '.$article['Id3'].'
                    </div>
                </div> 
            </div>    
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem">
                    <div class="col-sm-2 BoxRowLabel">
                        Descrizione <img src="' .IMG.'/'.$aLan[1] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <textarea class="frm_field_required frm_field_string"  name="DescriptionIta" style="height:6rem; width:80rem;">'.StringOutDB($article['DescriptionIta']).'</textarea>
                    </div>
                </div> 
            </div>  
            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem">
                    <div class="col-sm-2 BoxRowLabel">
                        Descrizione <img src="' .IMG.'/'.$aLan[2] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <textarea class="frm_field_required frm_field_string"  name="DescriptionEng" style="height:6rem; width:80rem;">'.StringOutDB($article['DescriptionEng']).'</textarea>
                    </div>
                </div> 
            </div>            
         
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem">
                    <div class="col-sm-2 BoxRowLabel">
                        Descrizione <img src="' .IMG.'/'.$aLan[3] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <textarea class="frm_field_required frm_field_string" name="DescriptionGer" style="height:6rem; width:80rem;">'.StringOutDB($article['DescriptionGer']).'</textarea>
                    </div>
                </div> 
            </div> 
            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem">
                    <div class="col-sm-2 BoxRowLabel">
                        Descrizione <img src="' .IMG.'/'.$aLan[4] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <textarea class="frm_field_required frm_field_string"  name="DescriptionSpa" style="height:6rem; width:80rem;">'.StringOutDB($article['DescriptionSpa']).'</textarea>
                    </div>
                </div> 
            </div>           
  
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem">
                    <div class="col-sm-2 BoxRowLabel">
                        Descrizione <img src="' .IMG.'/'.$aLan[5] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <textarea class="frm_field_required frm_field_string"  name="DescriptionFre" style="height:6rem; width:80rem;">'.StringOutDB($article['DescriptionFre']).'</textarea>
                    </div>
                </div> 
            </div>            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem">
                    <div class="col-sm-2 BoxRowLabel">
                        Note
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <textarea class="frm_field_str" name="Note" style="height:6rem; width:80rem;">'.StringOutDB($article['Note']).'</textarea>
                    </div>
                </div> 
            </div>   
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Sanzione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="frm_field_numeric" name="Fee" type="text" value="'.$tariff['Fee'].'" style="width:10rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Importo massimo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input  class="frm_field_numeric" name="MaxFee" type="text" value="'.$tariff['MaxFee'].'" style="width:10rem">
                    </div>
                </div> 
            </div>                                 
 
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-3 BoxRowLabel">
                        Decurtazione punti
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="frm_field_numeric" name="LicensePoint" type="text" value="'.$tariff['LicensePoint'].'" style="width:10rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Decurtazione neopatentati
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="frm_field_numeric" name="YoungLicensePoint" type="text" value="'.$tariff['YoungLicensePoint'].'" style="width:10rem">
                    </div>
                </div> 
            </div>  
            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Documento
                        <input name="PresentationDocument" type="checkbox" '.ChkCheckButton($tariff['PresentationDocument']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ritiro patente al fine della sospensione
                        <input name="SuspensionLicense" type="checkbox" '.ChkCheckButton($tariff['SuspensionLicense']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ritiro
                        <input name="LossLicense" type="checkbox" '.ChkCheckButton($tariff['LossLicense']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Sospensione della patente nel caso di recidiva nel biennio
                        <input name="Habitual" type="checkbox" '.ChkCheckButton($tariff['Habitual']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        126 Bis
                        <input name="126Bis" type="checkbox" '.ChkCheckButton($tariff['126Bis']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        &nbsp;
                    </div>
                </div> 
            </div>         
                              
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Add.le Massa
                        <input name="AdditionalMass" type="checkbox" '.ChkCheckButton($tariff['AdditionalMass']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Add.le notte
                        <input name="AdditionalNight" type="checkbox" '.ChkCheckButton($tariff['AdditionalNight']).' />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Riduzione giorni
                        <input name="ReducedPayment" type="checkbox" '.ChkCheckButton($tariff['ReducedPayment']).' />
                    </div>
                    <div class="col-sm-6 BoxRowLabel">
                        &nbsp;                  
                    </div>

                </div> 
            </div>            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Sanzione addizionale
                    </div>   
                    <div class="col-sm-10 BoxRowLabel">
                    '. CreateSelectShort("AdditionalSanction","1=1","Id","AdditionalSanctionId","Id","TitleIta",$tariff['AdditionalSanctionId'],false,80) .'
                    </div>

                </div> 
            </div>            
                        
            
            
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
               
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                       <button type="submit" class="btn btn-default" style="margin-top:1rem;">Delete</button>
                        <input type="button" onclick="window.location=\'tbl_article.php\'" value="Indietro" class="btn btn-default" style="margin-top:1rem;">
                    </div>
                </div>
            </div>
            </form>	
        </div>                                                                    
';


echo $str_out;

include(INC."/footer.php");