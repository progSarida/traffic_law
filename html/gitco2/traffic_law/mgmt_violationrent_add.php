<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

/*
$aDocViolation = glob(ROOT.'/public/*.jpg');

$a_doc = explode("traffic_law/",$aDocViolation[0]);

$First_Doc = $a_doc[1];
*/
if (isset($_GET['back']) && $_GET['back']=='true'){
    $str_BackPage = 'mgmt_violation.php?PageTitle=Preinserimenti/Gestione%20dati';
}

$charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);

$_form_type_id = 1;
$TotalChargeForeign = ($charge_row['ForeignTotalFee']>0) ? $charge_row['ForeignTotalFee'] : $charge_row['ForeignNotificationFee'] + $charge_row['ForeignResearchFee'];

$TotalChargeNational = ($charge_row['NationalTotalFee']>0) ? $charge_row['NationalTotalFee'] : $charge_row['NationalNotificationFee'] + $charge_row['NationalResearchFee'];
$title_lang = 'Title'.LAN;
$today_date = Date('Y-m-d');
$rs_city = $rs->SelectQuery("SELECT Id, Title FROM sarida.City ORDER BY Title ASC");
$r_city = $rs->SelectQuery("SELECT Id, Title FROM sarida.City ORDER BY Title ASC");
$sessionYear = $_SESSION['year'];
$select = $rs->SelectQuery("SELECT Fine.Id as FineId, Fine.VehicleTypeId,Fine.FineDate,Fine.ControllerId,Fine.Address,VehicleType.$title_lang,Controller.Name,Controller.Id,Controller.Code as cont_code from Fine 
left join VehicleType ON Fine.VehicleTypeId = VehicleType.Id JOIN Controller ON Fine.ControllerId = Controller.Id 
WHERE Fine.RegDate='$today_date' and UserId ='".$_SESSION['username']."' and Fine.CityId ='".$_SESSION['cityid']."' and Fine.ProtocolYear = '$sessionYear' order by Fine.Id DESC limit 1");

$article = "";
$comma = "";
$letter ="";
$nr = mysqli_num_rows($select);
if ($nr==1){
    while($row = mysqli_fetch_array($select)){
        $fine_date = $row['FineDate'];
        $fineId = $row['FineId'];
        $controller_id = $row['ControllerId'];
        $cont_name = $row['Name'];
        $adress = $row['Address'];
        $vehicle = $row['TitleIta'];
        $cont_code = $row['cont_code'];
        $vehicle_id = $row['VehicleTypeId'];
        $controller_name = $cont_code .' '.$cont_name;


    }
}

if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    $class = $answer =='Inserito con successo'?"alert-success":"alert-warning";
    echo "<div class='alert $class message'>$answer</div>";

    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 6000);
    </script>
    <?php
}
echo "<div class='check' id='test'></div>";
include './inc/page/VerbaleForm/Accertatore.php';

//

include './inc/page/VerbaleForm/Articolo.php';


$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

if($n_Code>0){
    $str_Locality='
                   
                        <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select name="Locality" class="form-control frm_field_required" style="width:10rem;">
                            <option></option>
               
    ';

    while($r_Row = mysqli_fetch_array($rs_Row)){
        $str_Locality.='<option value="'.$r_Row['Id'].'">'.$r_Row['Title'].'</option>';

    }

    $str_Locality.='        </select>
                        </div>
                    ';

}else{
    $str_Locality='    <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                        ' . $_SESSION['citytitle'] . '
                        </div>

    ';
}






$str_Toponym ='
        <div class="col-sm-2 BoxRowLabel">
            Toponimo
        </div>
        <div class="col-sm-2 BoxRowCaption">';

$str_Toponym .= CreateSelect('sarida.Toponym', '1=1', 'Id', 'Toponym', 'Title', 'Title', 0, false);
$str_Toponym .='</div>';
$str_Toponym .='
        <div class="col-sm-2 BoxRowLabel">
            Seleziona Strada
        </div>
        <div class="col-sm-1 BoxRowCaption">';

$str_Toponym .= CreateSelect('StreetType', '1=1', 'Id', 'StreetType', 'Title', 'Title', 0, false);
$str_Toponym .= '</div>';


$str_out .= '
        <form name="f_violation" id="f_violation" method="post" action="mgmt_violation_add_exe.php">
    	<input type="hidden" name="P" value="' . $str_CurrentPage . '">
        <input type="hidden" id="TrespasserId10" name="TrespasserId10" value="">
        <input type="hidden" id="TrespasserId11" name="TrespasserId11" value="">
        <input type="hidden" name="b_Rent" value="1">
        <input type="hidden" name="ArticleNumber" id="ArticleNumber" value="1">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="1">
        <input type="hidden" name="UploadNumber" id="UploadNumber" value="">
        <input type="hidden" id="art_1" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_2" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_3" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_4" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_5" fee="" maxFee="" addMass="">
    	<div class="row-fluid">
        	<div class="col-sm-6">';

include './inc/page/VerbaleForm/section1.php';
include './inc/page/VerbaleForm/SpeseNotifica.php';
include './inc/page/VerbaleForm/Infrazione.php';

$str_out .='
  				<div class="clean_row HSpace4"></div>
  	            <div class="col-sm-12">
        			<div class="col-sm-12 BoxRowLabel">
        				DITTA NOLEGGIO: <span id="span_name_10"></span>        				
					</div>
				</div>

				<div class="clean_row HSpace4"></div>   	
	        	<div class="col-sm-12" style="height:20rem;">
                    <ul class="nav nav-tabs" id="mioTab">
                        <li class="active" id="tab_Trespasser10"><a href="#Trespasser10" data-toggle="tab">PERSONA</a></li>
                        <li  id="tab_company10"><a href="#company10" data-toggle="tab">DITTA</a></li>
                        
                    </ul>
                    <span class="glyphicon glyphicon-plus-sign add_button_10" style="color:#294A9C;position:absolute; right:10px;top:10px;font-size:25px; "></span> 
                    <div class="tab-content">
                        <div class="tab-pane" id="company10">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-2 BoxRowLabel">
                                        <input type="hidden" name="Genre10" id="Genre10" value="D">
                                        Ragione sociale
                                    </div>
                                    <div class="col-sm-10 BoxRowCaption">
                                        <input name="CompanyName10" id="CompanyName10" type="text" style="width:15rem">
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane active" id="Trespasser10">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-3 BoxRowLabel">
                                        Cognome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input type="text" name="Surname10" id="Surname10" style="width:12rem">
                                    </div>
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input type="text" name="Name10" id="Name10" style="width:12rem">
                                    </div>
                                    
                                </div>
                            </div>
                        </div> 
                        <div id="trespasser_content_10" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>
                </div>
  				  				
  				  				
  				  				
   				<div class="clean_row HSpace4"></div>
  	            <div class="col-sm-12">
        			<div class="col-sm-12 BoxRowLabel">
        				NOLEGGIANTE: <span id="span_name_11"></span>        				
					</div>
				</div>

                <div class="clean_row HSpace4"></div>      
                <div class="col-sm-12"">
                    <div class="col-sm-3 BoxRowLabel">
                        Costi aggiuntivi
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" class="form-control frm_field_numeric" name="OwnerAdditionalFee" value="0" style="width:10rem;">	
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Data comunicazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_date frm_field_required" type="text" name="ReceiveDate" style="width:15rem;">	
                    </div>
                </div> 
				<div class="clean_row HSpace4"></div>   				  				
  				  				
				<div class="clean_row HSpace4"></div>   	
	        	<div class="col-sm-12" style="height:20rem;">
                    <ul class="nav nav-tabs" id="mioTab">
                        <li class="active" id="tab_Trespasser11"><a href="#Trespasser11" data-toggle="tab">PERSONA</a></li>
                        <li id="tab_company11"><a href="#company11" data-toggle="tab">DITTA</a></li>
                        
                    </ul>
                    <span class="glyphicon glyphicon-plus-sign add_button_11" style="color:#294A9C;position:absolute; right:10px;top:10px;font-size:25px; "></span>     
                    <div class="tab-content">
                        <div class="tab-pane" id="company11">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-2 BoxRowLabel">
                                        <input type="hidden" name="Genre11" id="Genre11" value="D">
                                        Ragione sociale
                                    </div>
                                    <div class="col-sm-10 BoxRowCaption">
                                        <input name="CompanyName11" id="CompanyName11" type="text" style="width:15rem">
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane active" id="Trespasser11">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-3 BoxRowLabel">
                                        Cognome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input type="text" name="Surname11" id="Surname11" style="width:12rem">
                                    </div>
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input type="text" name="Name11" id="Name11" style="width:12rem">
                                    </div>
                                   
                                </div>
                            </div>
                        </div> 
                        <div id="trespasser_content_11" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>
                </div></div> ';
include './inc/page/VerbaleForm/DOCUMENTAZIONE.php';
include './inc/page/VerbaleForm/salva.php';

$str_out .= '
<div id="BoxArticleSearch" style="top:40rem;left:50rem;">
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">           
            Articoli trovati
        </div>
        <div class="row-fluid">
            <div class="col-sm-12">
               
                 <div class="BoxRowCaption col-sm-3">
                    <input type="text" name="searchs" id="searchs"  class="form-control bv-form">
                    <input type="hidden" id="art_num" value="">
                 </div>
                 <div class="BoxRowCaption col-sm-3">
                    <button type="button" id="search_btn" class="btn btn-default btn-xs" style="margin-top: -8px">Cerca</button>
                    <i class="glyphicon glyphicon-remove" id="remove_btn" style="margin-top:0.1rem;font-size:1.7rem;"></i>
                 </div>
                 <div class="BoxRowCaption col-sm-6"></div>
                
            </div>
        </div>  
        <span class="fa fa-times-circle close_window_article" style="color:#fff;position:absolute; right:10px;top:2px;font-size:20px; "></span>  
    </div>
    <div class="clean_row HSpace4"></div> 
    <div id="Div_Article_Content" style="overflow:auto;height:23rem"></div>
</div>  
    <div class="overlay" id="overlay" style="display:none;"></div>

<div id="Div_Windows_Insert_Trespasser">
    <form name="f_ins_trespasser" id="f_ins_trespasser" class="submit_form">
        <input type="hidden" name="Genre" id="Genre" value="M">
        <input type="hidden" name="TrespasserTypeId" id="TrespasserTypeId">
        <input type="hidden" name="BornCity" id="BornCity">
        <input type="hidden" name="Fine_Date_Get" id="Fine_Date_Get">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            <span id="TitleTrespasser"></span>
            <span class="fa fa-times-circle close_window_trespasser" style="color:#fff;position:absolute; right:2px;top:2px;font-size:20px; "></span>
        </div>

        <div class="clean_row HSpace4"></div>
                            
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li class="active" id="tab_Trespasser_src"><a href="#Trespasser_src" data-toggle="tab">Persona fisica</a></li>
                <li id="tab_Company_src"><a href="#Company_src" data-toggle="tab">Ditta</a></li>
            </ul>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="tab-content"><!-- open div tab-content -->
            <div class="tab-pane" id="Company_src"><!-- open div tab-pane Company -->
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Ragione sociale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string trespasser_frm_field_required" name="CompanyName" id="CompanyName" type="text" style="width:20rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Forma Giuridica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <select class="form-control" name="LegalFormId" id="LegalFormId">
                                <option></option>';
$rs = new CLS_DB();
                                $getLegalform = $rs->SelectQuery("SELECT * FROM LegalForm");
                                $type = null;

                                while($row = mysqli_fetch_array($getLegalform)){
                                    if ($row['Type'] != $type) {
                                        if ($type !== null) {
                                            echo '</optgroup>';
                                        }
                                        $type = $row['Type'];
                                        $str_out.='<optgroup label="' . htmlspecialchars($type) . '">';
                                    }

                                    $str_out.='<option value="'.$row['Id'].'">'.$row['Description'].'</option>';
                                }

    $str_out.='</select>
                    </div>
                </div>
            </div>
             <!-- open div tab-pane Company -->

            <div class="tab-pane active" id="Trespasser_src"><!-- open div tab-pane Trespasser -->
                <div class="col-sm-12">
                    <div class="col-sm-1 BoxRowLabel">
                        Sesso
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="radio" value="M" name="Sex" id="sexM">M &nbsp;
                        <input type="radio" value="F" name="Sex" id="sexF">F
                        <span id="sex_code"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Cognome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string trespasser_frm_field_required" type="text" id="Surname" name="Surname">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string trespasser_frm_field_required" type="text" id="Name" name="Name">
                        
                    </div>
                </div>
                
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI NASCITA
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "BornCountry", "Id", "Title", "Z000", false, 15) . '
                    </div>
                </div>   

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input id="BornCityInput" class="form-control frm_field_string" type="text" name="BornCityInput" style="display:none; width:12rem">
                        <select id="BornCitySelect" class="form-control" name="BornCitySelect">
                             <option></option>';
                                while($row_city = mysqli_fetch_array($r_city)) {
                                    $str_out .= '<option value='.$row_city['Id'].'>'.$row_city['Title'].'</option>';
                                }
                                $str_out .='
                        </select>
                    </div> 
                    <div class="col-sm-2 BoxRowLabel">
                        Data Nascita
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:12rem">
                    </div>     
                </div>   

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI PATENTE
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Categoria
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseCategory"  style="width:4rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Numero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseNumber" style="width:12rem">
                    </div>                   
                </div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>                   
                    <div class="col-sm-2 BoxRowLabel">
                        Data rilascio
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="LicenseDate"  style="width:12rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ente rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseOffice" style="width:12rem">
                    </div>
                </div>
            </div><!-- close div tab-pane Trespasser -->
        </div><!-- close div tab-content -->
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle">
                DATI RESIDENZA
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "CountryId", "Id", "Title", "Z000", true, 15) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input id="CityInput" class="form-control frm_field_string" type="text" name="CityInput" style="display:none; width:20rem">
                <select id="CitySelect" class="form-control" name="CitySelect">
                     <option></option>';
                        while($row = mysqli_fetch_array($rs_city)) {
                            $str_out .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                        }
                        $str_out .='
                </select>
            </div>            
        </div>  
        <div class="clean_row HSpace4"></div>
                   
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>                      
            <div class="col-sm-1 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Address2" name="Address2" style="width:30rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Cap
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="ZIP" id="ZIP" style="width:8rem">
            </div>
            <div id="province_div">
                <div class="col-sm-1 BoxRowLabel">
                    Provincia
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" type="text" name="Province" id="Province" style="width:8rem">
                </div>
            </div>
            
            <div class="clean_row HSpace4"></div>
        </div>
            
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>                      
            <div class="col-sm-2 BoxRowLabel">
                Civico
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="StreetNumber" name="StreetNumber" style="width:18rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Scala
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Ladder" id="Ladder" style="width:18rem">
            </div>
            
            <div class="clean_row HSpace4"></div>
        </div>  
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>                      
           
            <div class="col-sm-2 BoxRowLabel">
                Interno
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Indoor" id="Indoor" style="width:18rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Piano
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Plan" id="Plan" style="width:18rem">
            </div>
            
            <div class="clean_row HSpace4"></div>
        </div>         
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                C.F./P.IVA
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="width:18rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="PEC" id="PEC" style="width:18rem">
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Mail">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Phone">
            </div>
        </div>
      
        <div class="clean_row HSpace4"></div>      

        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowButton">
            <button type="submit" class="btn btn-primary" id="btn_saveanagrafica">
                <i class="fa fa-floppy-o" style="font-size:3.5rem;"></i>
            </button>
            </div>
            <div class="alert alert-warning">
                <center>
                    <p id="erroriva"></p>
                </center>
            </div>
        </div> 
    </form>

</div>
';


echo $str_out;
?>

    <script type="text/javascript">
        $("#BornCountry, #CountryId").change(function () {

            //var str_FieldProvince = ($(this).attr("id")=="BornCountry") ? "BornProvinceTitle" : "ProvinceTitle";
            var str_FieldCityS = ($(this).attr("id")=="BornCountry") ? "BornCitySelect" : "CitySelect";
            var str_FieldCityI = ($(this).attr("id")=="BornCountry") ? "BornCityInput" : "CityInput";

            var Country = $(this).val();


            $("#ZIP").val('');
            $("#ZIP").removeClass('txt-success txt-warning txt-danger');

            if (Country == "Z000") {
                //$("#"+str_FieldProvince).show().children('option:not(:first)').remove();
                $("#"+str_FieldCityS).show().children('option:not(:first)').remove();
                $("#"+str_FieldCityI).hide();
                if(str_FieldCityS=="CitySelect") $("#Province").show();

                $.ajax({
                    url: 'ajax/ajx_src_prov_city.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Country: Country},
                    success: function (data) {
                        //$("#"+str_FieldProvince).children('option:not(:first)').remove();
                        $.each(data.selectValues, function (key, value) {
                            $("#"+str_FieldCityS)
                                .append($("<option></option>")
                                    .attr({
                                        'value': key,
                                    })
                                    .text(value['Title']));
                        });

                    }
                });
            } else {
                $("#TaxCode").removeClass('txt-success txt-warning txt-danger');
                if(str_FieldCityS=="CitySelect") $("#Province").hide();
                //$("#"+str_FieldProvince).hide().children('option:not(:first)').remove();
                $("#"+str_FieldCityS).hide().children('option:not(:first)').remove();
                $("#"+str_FieldCityI).show();
            }
        });

        $('#Toponym').change(function() {
            var type = $(this).val();
            var address = $('#Address').val();
            if(address=='') $('#Address').val(type);
            else $('#Address').val(address + ' ' + type);

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').show();
                    $('#Address_List').html(data);
                }
            });
        });
        $('#StreetType').change(function() {
            var type = $(this).val();
            var address = $('#Address').val();
            if(address=='') $('#Address').val(type);
            else $('#Address').val(address + ' ' + type);

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').show();
                    $('#Address_List').html(data);
                }
            });
        });

        $('.EditAdditionalSanction').click(function () {
            var get_ArticleNumber_text = $(this).attr('id');
            Article = get_ArticleNumber_text.split("_");
            ArticleNumber_Text = Article[1];

            if($('#AdditionalSanction_'+ArticleNumber_Text).is(":visible")) {
                $('#AdditionalSanction_'+ArticleNumber_Text).hide();
                $('#AdditionalSanctionInput_'+ArticleNumber_Text).show();
            } else {
                $('#AdditionalSanction_'+ArticleNumber_Text).show();
                $('#AdditionalSanctionInput_'+ArticleNumber_Text).hide();
                $('#AdditionalSanctionInputText_'+ArticleNumber_Text).val('');
            }
        });
        var addMass = <?=MASS?>;
        $('#VehicleMass').change(function() {
            checkFee();
        });

        function checkFee() {
            var mass = $('#VehicleMass').val();
            var sum = 0;
            for (var i = 1; i <= 5; i++) {

                var Fee = parseFloat($('#art_' + i).attr('fee'));
                var MaxFee = parseFloat($('#art_' + i).attr('maxFee'));

                if ($('#art_' + i).attr('addMass') == 1) {
                    if(mass<addMass){
                        $('#Fee_'+i).val(Fee.toFixed(2));
                        $('#MaxFee_'+i).val(MaxFee.toFixed(2));
                        sum+=Fee;
                        $('#span_Fee_'+i).html(Fee.toFixed(2));
                        $('#span_MaxFee_'+i).html(MaxFee.toFixed(2));
                    } else {
                        $('#Fee_'+i).val((Fee*2).toFixed(2));
                        $('#MaxFee_'+i).val((MaxFee*2).toFixed(2));
                        sum+=Fee*2;
                        $('#span_Fee_'+i).html((Fee*2).toFixed(2));
                        $('#span_MaxFee_'+i).html((MaxFee*2).toFixed(2));
                    }
                }
                if ($('#art_' + i).attr('addMass') == '0') {
                    sum+=Fee;
                }
            }
            sum += parseFloat($('#span_TotalCharge').html());
            $('#span_TotalFee').html(sum.toFixed(2));
        }

    </script>




    <script type="text/javascript">
        var controller = true;
        var chkCode = true;
        var giaPresente = false;
        var chkCode = true;
        var namespan;

        var totCharge = <?= $TotalChargeNational ?>;


        $('.find_list').keyup(function(){
            var min_length = 2; // min caracters to display the autocomplete
            var keyword = $(this).val();

            namespan = $(this).attr('name');

            if (keyword.length >= min_length) {

                $.ajax({
                    url: 'ajax/search.php?searchtype=t',
                    type: 'POST',
                    data: {keyword:keyword, field:namespan},
                    success:function(data){

                        $('#' + namespan + '_List').show().html(data);
                    }
                });
            } else {
                $('#' + namespan + '_List').hide();
            }


        });

        $('#Address').focusout(function(){
            setTimeout(function () {
                $('#Address_List').hide();
            }, 150);
        });
        $('#Address').focusin(function(){
            $('#Address_List').show();
        });

        function set_item(item,id) {
            // change input value
            $('#' + namespan).val(item);
            //$('#CustomerID').val(id);
            $('#' + namespan + '_List').hide();
        }



        $('document').ready(function(){

            var ArticleNumber = $('#ArticleNumber').val();



            //$('#preview').iZoom({diameter:200});
            $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});


            <?= CreateSltChangeJQ("Controller", "CityId='".$_SESSION['cityid']."' AND Disabled=0", "Name", "ControllerCode", "Code", "Id", "ControllerId") ?>


            //$('.article_change').focusout(chkArticle);
            $(document).on('click', '.add_article', function(){
                chkArticle();
            });



            $('#SpeedLimit').focusout(chkSpeed);
            $('#SpeedControl').focusout(chkSpeed);





            //$('#FineDate').focusout(function() {
            //    var str = $('#FineDate').val();
            //    var chkYear;
            //    if(str.length==8){
            //        $('#FineDate').val( str.substr(0,2) + "/" + str.substr(2,2) + "/" + str.substr(4) );
            //        chkYear = str.substr(4);
            //
            //    }else{
            //        var aDate = str.split("/");
            //        chkYear = aDate[2];
            //    }
            //
            //    if(chkYear!='<?//= $_SESSION['year']?>//'){
            //        $("#save").prop("disabled", true);
            //        $("#span_date").addClass("help-block");
            //        $("#span_date").html('Anno errato!');
            //
            //        chkDate = false;
            //
            //    }else{
            //        chkDate = true;
            //        if(chkDate==true){
            //            $("#save").prop("disabled", false);
            //            $("#span_date").removeClass("help-block");
            //            $("#span_date").html('');
            //
            //        }
            //    }
            //
            //});

            $('#FineTime').change(function() {

                var str = $('#FineTime').val();
                var part1 = parseInt(str.substr(0,2));
                var part2 = parseInt(str.substr(3,2));

                var hours = 24;
                var minutes = 59;

                if (part1 >hours || part2 >minutes){

                    chkTime = false;
                    $("#save").prop("disabled", true);
                    $("#span_time").addClass("help-block");
                    $("#span_time").html('Ora errato!');
                } else{

                    chkTime=true;
                    if(chkTime==true) {
                        $("#save").prop("disabled", false);
                        $("#span_time").removeClass("help-block");
                        $("#span_time").html('');
                    }
                }
                if(str.length==4){

                    $('#FineTime').val( str.substr(0,2) + ":" + str.substr(2,2) );
                }
            });


            $('#fileTreeDemo_1').fileTree({ root: 'public/_VIOLATION_/<?= $_SESSION['cityid']?>/', script: 'jqueryFileTree.php?insert=true' }, function(file) {

                var FileType = file.substr(file.length - 3);

                if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc'){
                    $("#preview_img").hide();
                    $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src='"+file+"'></iframe>");
                    $("#preview_doc").show();
                }else{
                    $("#preview_doc").hide();
                    $("#preview").attr("src",file);
                    $("#preview_img").show();
                }


                $("#Documentation").val(file);
                $("#span_Documentation").html(file);
            });


            $('#CountryId').change(function(){
                var id = $( "#CountryId" ).val();
                $('#VehicleCountry').val($( "#CountryId option:selected" ).text());

                if(id=='Z110'){
                    var ent = $.ajax({
                        url: "ajax/department.php",
                        type: "POST",
                        data: {id:id},
                        dataType: "text"
                    });
                    ent.done(function(data){
                        $('#DepartmentId').html(data);
                        $('#department').show();
                        $('#toHide').hide();
                    });
                    ent.fail(function(jqXHR, textStatus){
                        alert( "Request failed: " + textStatus );
                    });
                }else{
                    $('#DepartmentId').html('');
                    $('#department').hide();
                    $('#toHide').show();
                }


                if(id=='Z000'){
                    $('#TotalCharge').val('<?= $TotalChargeNational ?>');
                    $('#span_TotalCharge').html('<?= $TotalChargeNational ?>');
                    totCharge = <?= $TotalChargeNational ?>;

                }else{
                    $('#TotalCharge').val('<?= $TotalChargeForeign ?>');
                    $('#span_TotalCharge').html('<?= $TotalChargeForeign ?>');
                    totCharge = <?= $TotalChargeForeign ?>;
                }

            });

            $('#Toponym').change(function() {
                $('#Address').val($(this).val());
            });

            $('#DetectorId').change(function() {
                var DetectorId = $(this).val();

                if(DetectorId!='') {
                    $.ajax({
                        url: 'ajax/search_detector.php',
                        type: 'POST',
                        cache: false,
                        data: {DetectorId: DetectorId},
                        dataType:'json',
                        success: function (data) {

                            if (data.UploadNumber ==2){
                                $("#UploadNumber").val(data.UploadNumber);

                            }
                            if (data.strSpeed == 1) {
                                $('#DIV_Title_Speed').show();
                                $('#DIV_Speed').show();

                                $('#SpeedLimit').prop("readonly", false);
                                $('#SpeedControl').prop("readonly", false);

                                $('#id1_' + ArticleNumber).val('');
                                $('#id2_' + ArticleNumber).val('');
                                $('#id3_' + ArticleNumber).val('');

                                $('#id1_' + ArticleNumber).prop("disabled", true);
                                $('#id2_' + ArticleNumber).prop("disabled", true);
                                $('#id3_' + ArticleNumber).prop("disabled", true);

                                $('#DIV_Title_TLight').hide();
                                $('#DIV_TLight').hide();

                                $("#TimeTLightFirst").val('');
                                $("#TimeTLightSecond").val('');

                                $('#span_Article_'+ArticleNumber).html('');

                            }else if(data.strSpeed == 2){
                                $('#DIV_Title_TLight').show();
                                $('#DIV_TLight').show();

                                $("#id1_"+ArticleNumber).val('146');
                                $("#id2_"+ArticleNumber).val('3');
                                $("#id3_"+ArticleNumber).val('');

                                chkArticle();


                                $('#id1_' + ArticleNumber).prop("disabled", true);
                                $('#id2_' + ArticleNumber).prop("disabled", false);
                                $('#id3_' + ArticleNumber).prop("disabled", false);

                                $('#DIV_Title_Speed').hide();
                                $('#DIV_Speed').hide();

                                $("#SpeedLimit").prop("readonly", true);
                                $("#SpeedControl").prop("readonly", true);
                                $("#SpeedLimit").val('');
                                $("#SpeedControl").val('');
                            }

                            else {
                                $('#DIV_Title_Speed').hide();
                                $('#DIV_Speed').hide();

                                $("#SpeedLimit").prop("readonly", true);
                                $("#SpeedControl").prop("readonly", true);
                                $("#SpeedLimit").val('');
                                $("#SpeedControl").val('');

                                $("#id1_"+ArticleNumber).prop("disabled", false);
                                $("#id2_"+ArticleNumber).prop("disabled", false);
                                $("#id3_"+ArticleNumber).prop("disabled", false);

                                $('#DIV_Title_TLight').hide();
                                $('#DIV_TLight').hide();
                                $("#TimeTLightFirst").val('');
                                $("#TimeTLightSecond").val('');

                                $('#span_Article_'+ArticleNumber).html('');
                            }

                        }

                    });
                }else{
                    $("#SpeedLimit").prop("readonly", true);
                    $("#SpeedControl").prop("readonly", true);
                    $("#SpeedLimit").val('');
                    $("#SpeedControl").val('');

                    $("#id1_"+ArticleNumber).prop("disabled", false);
                    $("#id2_"+ArticleNumber).prop("disabled", false);
                    $("#id3_"+ArticleNumber).prop("disabled", false);

                    $('#DIV_Title_TLight').hide();
                    $('#DIV_TLight').hide();
                    $("#TimeTLightFirst").val('');
                    $("#TimeTLightSecond").val('');

                    $('#span_Article_'+ArticleNumber).html('');
                }
            });



            $('#VehicleMass').focusout(function() {
                if($(this).val()=='') $(this).val(0);
            });
            $('#VehicleCapacity').focusout(function() {
                if($(this).val()=='') $(this).val(0);
            });

            $('#VehicleTypeId').change(function () {
                var type = $('#VehicleTypeId').val();
                if(type==2 || type==9) {
                    $('#massa').hide();
                    $('#toHide2').show();
                }
                else {
                    $('#massa').show();
                    $('#toHide2').hide();
                }
            });


            $('#VehiclePlate').change(function() {
                var VehiclePlate = $(this).val();
                var FineDate = $('#FineDate').val();
                var FineTime = $('#FineTime').val();
                var id = $( "#CountryId" ).val();
                var checkPlate;
                var message = "Attenzione: la targa potrebbe non essere italiana ";
                var VehicleTypeId = $('#VehicleTypeId').val();
                if(id=='Z000'){
                    find = 0;
                    // checkPlate = '^[a-zA-Z]{2}[a-zA-Z0-9][0-9]{5}$';
                    // if(VehiclePlate.match(checkPlate)!=null) find = 1;
                    if(VehicleTypeId == 9){
                        checkPlate = '^[a-zA-Z]{2}[0-9]{5}$';
                        if(VehiclePlate.match(checkPlate)!=null)  find = 1;
                    } else {
                        checkPlate = '^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                        if(VehiclePlate.match(checkPlate)!=null) find = 1;
                    }
                    if(VehiclePlate.includes('q') || VehiclePlate.includes('Q') || VehiclePlate.includes('u') || VehiclePlate.includes('U') ||
                        VehiclePlate.includes('o') || VehiclePlate.includes('O') || VehiclePlate.includes('i') || VehiclePlate.includes('I')) {
                        find = 0;
                        message += 'oppure si tratta di una vecchia targa';
                    }
                    if(find==0) alert(message);
                }

                if(id=='Z112'){
                    checkPlate ='^[a-zA-Z]{1,3}[ t][ t][a-zA-Z]{1,3}[0-9]{1,4}$';
                    if(VehiclePlate.match(checkPlate)==null) alert("Attenzione: la targa potrebbe non essere tedesca");
                }

                if(id=='Z110'){
                    find = 0;
                    checkPlate ='^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    checkPlate ='^[0-9]{3,4}[a-zA-Z]{2}[0-9]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    if(find==0) alert("Attenzione: la targa potrebbe non essere francese");
                }


                if(id=='Z131'){
                    find = 0;
                    checkPlate ='^[a-zA-Z]{2}[0-9]{4}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    checkPlate ='^[0-9]{4}[a-zA-Z]{3}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    if(find==0) alert("Attenzione: la targa potrebbe non essere spagnola");
                }

                if(id=='Z129'){
                    checkPlate ='^[a-zA-Z]{1,2}[0-9]{2}[a-zA-Z]{3}$';
                    if((VehiclePlate.match(checkPlate))==null) alert("Attenzione: la targa potrebbe non essere rumena");
                }
                if(id=='Z133'){
                    checkPlate ='^[a-zA-Z]{2}[0-9]{2,6}$';
                    if((VehiclePlate.match(checkPlate))==null) alert("Attenzione: la targa potrebbe non essere svizzera");
                }
                if(id=='Z103'){
                    find = 0;
                    checkPlate ='^[0-9][a-zA-Z]{2,3}[0-9]{2,3}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    checkPlate ='^[a-zA-Z]{3}[0-9]{3}$';
                    if((VehiclePlate.match(checkPlate))==null) find = 1;

                    if(find==0) alert("Attenzione: la targa potrebbe non essere belga");
                }


                $.ajax({
                    url: 'ajax/search_plate.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {VehiclePlate:VehiclePlate, FineDate:FineDate, FineTime:FineTime},
                    success:function(data){


                        if(data.H>0){
                            $('#div_chkPlate').addClass("div_Height_"+data.H);
                        }else{
                            $('#div_chkPlate').removeClass("div_Height_150");
                            $('#div_chkPlate').removeClass("div_Height_300");
                        }

                        if(data.F!=""){
                            $('#fine_content').addClass("div_HiddenContent");
                            $('#fine_content').html(data.F);
                            $('#fine_content').show();
                        }else{
                            $('#fine_content').removeClass("div_HiddenContent");
                            $('#fine_content').hide();
                        }

                        if(data.T!=""){
                            $('#trespasser_content').addClass("div_HiddenContent");
                            $('#trespasser_content').html(data.T);
                            $('#trespasser_content').show();
                        }else{
                            $('#trespasser_content').removeClass("div_HiddenContent");
                            $('#trespasser_content').hide();
                        }

                    }
                });
            });

            $('#back').click(function () {
                window.location = "<?= $str_BackPage ?>";
                return false;
            });

            $('#f_violation').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
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

                    fine_date:{
                        selector: '.fine_date',
                        validators: {
                            callback: {
                                message: 'Anno Errato',
                                callback: function(value) {
                                    var error = {
                                        valid: false,
                                        message: 'Data Errato'
                                    };
                                    if(value=='') {
                                        return {
                                            valid: false,
                                            message: 'Richiesto'
                                        };
                                    }
                                    var str = value;
                                    if(str.length==10){
                                        if(parseInt(str.substr(6,4)) != parseInt('<?= $_SESSION['year']?>')) return error;
                                        if(parseInt(str.substr(0,2)) > 31 || parseInt(str.substr(0,2)) < 1) return error;
                                        if(parseInt(str.substr(3,2)) > 12 || parseInt(str.substr(3,2)) < 1) return error;
                                    } else return error;
                                    return true
                                }
                            },
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

                    FineTime: {
                        validators: {
                            notEmpty: {message: 'Richiesto'},
                            regexp: {
                                //regexp: '^(1?[0-9]|2[0-9]):[0-5][0-9]$',
                                regexp: '^(0?[0-9]|1?[0-9]|2[0-4]):[0-5][0-9]$',
                                message: 'Ora non valida'
                            }
                        }

                    },

                }
            }).on('success.form.bv', function(e){
                e.preventDefault();
                var today_date = $('#FineDate').val();
                var ora = $('#FineTime').val();
                var Eludi_Controlli  = $('#Conrolli').is(':checked');
                var targa = $('#VehiclePlate').val();
                var reason = $('#ReasonId').val();
                var vehicleType = $('#VehicleTypeId').val();
                var Code = $('#Code').val();
                var Controller = $('.select_controller_1').val();
                var article1 = $('#ArticleId_1').val();
                var article2 = $('#ArticleId_2').val();
                var article3 = $('#ArticleId_3').val();
                var article4 = $('#ArticleId_4').val();
                var article5 = $('#ArticleId_5').val();
                var articles = {today_date:today_date,
                    ora:ora,
                    targa:targa,
                    article1:article1,
                    article2:article2,
                    article3:article3,
                    article4:article4,
                    article5:article5,
                };
                if (Code != "") {
                    $.ajax({
                        url: 'ajax/mgmt_validate_fine_ajax.php',
                        type: 'POST',
                        data: {Code: Code, Controller: Controller,Eludi_Controlli:Eludi_Controlli,FineTypeId:1},
                        success: function (data) {
                            if (data == "NOB") {
                                check("#save","#span_code","Codice non essiste!!");
                                chkCode = false;
                            }
                            if (data == "NE") {
                                check("#save","#span_acce_1","Controller Errato!");
                                controller = false;
                            }

                            if (data == "GE") {
                                check("#save","#span_code","Codice gia esiste!!");
                                chkCode = false;
                            }
                            if (data == "OK") {

                                submitForm(today_date,ora,targa,vehicleType,reason,articles);
                            }

                        }
                    });
                }else{
                    submitForm(today_date,ora,targa,vehicleType,reason,articles);
                }
                return false;

            });

            $('#Conrolli').on('click',function(){
                $('#Code').trigger("change");
                $('#ControllerId').trigger("change");
                controller = true;
                var Eludi_Controlli  = $('#Conrolli').is(':checked');
                if (Eludi_Controlli == true){
                    $("#save").prop("disabled", false);
                    $("#span_acce_1").removeClass("help-block");
                    $("#span_acce_1").html('');
                }
            });

            function check(button,span,text){
                $(button).prop("disabled", true);
                $(span).addClass("help-block");
                $(span).html(text);
            }

            function submitForm(today_date,ora,targa,vehicleType,reason,articles){
                if (today_date == "" || ora == "" || targa == "" || vehicleType == "") {
                    e.preventDefault();
                } else {
                    $("#save").prop("disabled", false);
                    $("#span_acce").removeClass("help-block");
                    $("#span_acce").html('');
                    if (chkCode) {
                        $.ajax({
                            url: 'ajax/mgmt_check_verbali.php',
                            type: 'POST',
                            data: articles,
                            success: function (data) {
                                if (data == "NOK") {
                                    $('.check').append('<div class="alert alert-warning">Questa multa esiste nel database. Per favore seleziona un altro articolo!</div>');
                                    $("html, body").animate({scrollTop: 0}, "slow");
                                    $("#save").prop("disabled", true);
                                }
                                if (data == "OK") {
                                    $('.check').hide();
                                    if (giaPresente == false && controller) document.f_violation.submit();
                                }
                            }
                        });
                    }

                }
            }

            function chkArticle() {

                var id1=$('#id1_'+ArticleNumber).val();
                var id2=$('#id2_'+ArticleNumber).val();
                var id3=$('#id3_'+ArticleNumber).val();
                var FineTime = $('#FineTime').val();

                if(id1!='' && (id2!='' || id3!='')){

                    var RuleTypeId = $('#RuleTypeId').val();
                    var request = $.ajax({
                        url: "ajax/search_article.php",
                        dataType: 'json',
                        type: "post",
                        cache: false,
                        data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3+"&ArticleCounter="+ArticleNumber
                    });

                    request.done(function (response){
                        $("#downart").show();
                        if (response.NonExist ==1){

                            alert(response.Description);
                            $('#span_Article_'+ArticleNumber).html(response.Description).css("background-color","red");
                            $("#save").prop("disabled", true);
                        }else{
                            $("#save").prop("disabled", false);
                            $('#Edit_Contestazione').show();
                            $('#span_Article_'+ArticleNumber).html(response.Description);
                            $('#span_Article_'+ArticleNumber).css("background-color","");
                        }
                        //$('#span_Article_'+ArticleNumber).html(response.Description);
                        $('#ReasonId').html(response.ReasonDescription);
                        $('#ReasonId_Second').val(response.reasonId);
                        $('#ReasonId').trigger("change");
                        $('#EditArticle_'+ArticleNumber).show();

                        if(response.Result==1){

                            $('#ArticleId_'+ArticleNumber).val(response.ArticleId);

                            if(ArticleNumber==1){
                                $('#ViolationTypeId').val(response.ViolationTypeId);
                                $('#span_ViolationTitle').html(response.ViolationTitle);
                            }
                            $('#artcomune_'+ArticleNumber).val(response.ArtComunali);
                            $('#Fee_'+ArticleNumber).val(response.Fee);
                            $('#span_Fee_'+ArticleNumber).html(response.Fee);
                            $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                            $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);

                            $('#art_'+ArticleNumber).attr('fee', response.Fee);
                            $('#art_'+ArticleNumber).attr('addMass', response.AddMass);
                            $('#art_'+ArticleNumber).attr('maxFee', response.MaxFee);
                            $('#art_'+ArticleNumber).attr('desc', response.Description);
                            $('#AdditionalSanctionSelect_'+ArticleNumber).html(response.AdditionalSanctionSelect);
                            $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                            //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                            var totFee = 0;
                            for(var i=1;i<=ArticleNumber;i++){
                                totFee = totFee + Number($('#Fee_'+i).val());
                            }
                            totFee = totFee + Number(totCharge);
                            $('#span_TotalFee').html(totFee.toFixed(2));

                            checkFee();
                        }



                    });
                    request.fail(function (jqXHR, textStatus){
                        $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
                    });

                    //event.preventDefault();
                }

            }




            function chkSpeed() {
                var DetectorId = $('#DetectorId').val();
                var SpeedLimit=$('#SpeedLimit').val();
                var SpeedControl=$('#SpeedControl').val();
                var FineTime = $('#FineTime').val();
                var RuleTypeId = $('#RuleTypeId').val();

                if(SpeedLimit!='' && SpeedControl!=''){

                    var request = $.ajax({
                        url: "ajax/search_article.php",
                        dataType: 'json',
                        type: "post",
                        cache: false,
                        data: "RuleTypeId="+RuleTypeId+"&DetectorId="+DetectorId+"&SpeedLimit=" + SpeedLimit + "&SpeedControl=" + SpeedControl +"&FineTime=" + FineTime
                    });

                    request.done(function (response){
                        console.log(response)

                        $("#downart").show();
                        $('#span_Article_'+ArticleNumber).html(response.Description);
                        $('#ReasonId').html(response.ReasonDescription);
                        $('#ReasonId_Second').val(response.reasonId);
                        $('#ReasonId').trigger("change");
                        $('#EditArticle_'+ArticleNumber).show();
                        $('#Edit_Contestazione').show();
                        if(response.Result==1){
                            $('#ArticleId_'+ArticleNumber).val(response.ArticleId);
                            $('#Speed').val(response.Speed);
                            $('#span_Speed').html(response.Speed);

                            $('#id1_'+ArticleNumber).val(response.Id1);
                            $('#id2_'+ArticleNumber).val(response.Id2);
                            $('#id3_'+ArticleNumber).val(response.Id3);
                            $('#artcomune_'+ArticleNumber).val(response.ArtComunali);

                            if(ArticleNumber==1){
                                $('#ViolationTypeId').val(response.ViolationTypeId);
                                $('#span_ViolationTitle').html(response.ViolationTitle);
                            }

                            $('#Fee_'+ArticleNumber).val(response.Fee);
                            $('#span_Fee_'+ArticleNumber).html(response.Fee);
                            $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                            $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);
                            $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                            //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                            var totFee = 0;
                            for(var i=1;i<=ArticleNumber;i++){
                                totFee = totFee + Number($('#Fee_'+i).val());
                            }
                            totFee = totFee + Number(totCharge);

                            $('#span_TotalFee').html(totFee.toFixed(2));
                        }else{
                            if (response.NonExist ==1){

                                alert(response.Description);
                                $('#span_Article_'+ArticleNumber).html(response.Description).css("background-color","red");
                                $("#save").prop("disabled", true);
                            }
                        }


                    });
                    request.fail(function (jqXHR, textStatus){
                        $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
                    });
                    //event.preventDefault();
                }
            }

            $( function() {
                $( "#BoxArticleSearch" ).draggable();
            } );


            $(".close_window_article").click(function () {
                $('#BoxArticleSearch').hide();
            });


            $(".glyphicon-search").click(function () {


                var id1=$('#id1_'+ArticleNumber).val();

                var RuleTypeId = $('#RuleTypeId').val();
                var get_ArticleNumber = $(this).attr('id');
                Article = get_ArticleNumber.split("_");
                ArticleNumber_Edit = Article[1];
                $("#art_num").val(ArticleNumber_Edit);
                $.ajax({
                    url: 'ajax/ajx_src_article_lst.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Id1: id1, RuleTypeId: RuleTypeId, ArticleCounter: ArticleNumber_Edit},
                    success: function (data) {

                        $('#Div_Article_Content').html(data.Article);
                        $('#BoxArticleSearch').fadeIn('slow');


                    }
                });

            });

            $("#search_btn").click(function () {
                var src = $('#searchs').val();
                var id1=null;
                var RuleTypeId = $('#RuleTypeId').val();
                var artid = $("#art_num").val();
                $.ajax({
                    url: 'ajax/ajx_src_article_lst.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Id1: id1, search: src, ArticleCounter: artid, RuleTypeId: RuleTypeId},
                    success: function (data) {
                        $('#Div_Article_Content').html(data.Article);
                    }
                });
            });

            $(document).on('change','.article_change_comune',function () {
                var search = $("#artcomune_"+ArticleNumber).val();
                var index = $(this).data('number');
                var FineTime = $('#FineTime').val();
                if(search!=''){
                    var RuleTypeId = $('#RuleTypeId').val();
                    var request = $.ajax({
                        url: "ajax/search_article.php",
                        dataType: 'json',
                        type: "POST",
                        cache: false,
                        data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&ArticleCounter="+index+"&search="+search
                    });

                    request.done(function (response){

                        $("#downart").show();
                        $('#span_Article_'+index).html(response.Description);
                        $('#ReasonId').html(response.ReasonDescription);
                        $('#ReasonId_Second').val(response.reasonId);
                        $('#ReasonId').trigger("change");
                        $('#EditArticle_'+index).show();
                        $('#Edit_Contestazione').show();
                        if(response.Result==1){

                            $('#id1_'+index).val(response.Id1);
                            $('#id2_'+index).val(response.Id2);
                            $('#id3_'+index).val(response.Id3);
                            $('#ArticleId_'+index).val(response.ArticleId);
                            $('#artcomune_'+index).val(response.ArtComunali);
                            if(index==1){
                                $('#ViolationTypeId').val(response.ViolationTypeId);
                                $('#span_ViolationTitle').html(response.ViolationTitle);
                            }

                            $('#Fee_'+index).val(response.Fee);
                            $('#span_Fee_'+index).html(response.Fee);
                            $('#MaxFee_'+index).val(response.MaxFee);
                            $('#span_MaxFee_'+index).html(response.MaxFee);

                            $('#art_'+index).attr('fee', response.Fee);
                            $('#art_'+index).attr('addMass', response.AddMass);
                            $('#art_'+index).attr('maxFee', response.MaxFee);
                            $('#art_'+index).attr('desc', response.Description);
                            $('#AdditionalSanctionSelect_'+index).html(response.AdditionalSanctionSelect);
                            $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                            //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);

                            var totFee = 0;
                            for(var i=1;i<=index;i++){
                                totFee = totFee + Number($('#Fee_'+i).val());
                            }
                            totFee = totFee + Number(totCharge);
                            $('#span_TotalFee').html(totFee.toFixed(2));
                            checkFee();
                        }

                    });
                    request.fail(function (jqXHR, textStatus){
                        $('#span_Article_'+index).html("ERR: "+ textStatus);
                    });
                }
            });

            $("#remove_btn").click(function () {
                var src = $('#searchs').val('');
            });

            $('.article_change ').on('change',function () {
                chkArticle();
            });

            $('.article_change').keyup(function (e) {
                const code = $(this).val();
                var id1=null;
                var get_ArticleNumber_text = $(this).attr('id');
                Article = get_ArticleNumber_text.split("_");
                ArticleNumber_Text = Article[1];
                var code_comma = $("#id2_"+ArticleNumber_Text).val();
                var code_lettera = $("#id23_"+ArticleNumber_Text).val();
                var RuleTypeId = $('#RuleTypeId').val();
                var isFound = false;
                const index = $(this).data('number');
                $.ajax({
                    url: 'ajax/ajx_src_article_lst.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Id1: id1, search_code: code, ArticleCounter: index, RuleTypeId: RuleTypeId},
                    success: function (data) {
                        if(code_comma != "" && code_lettera !="") {
                            $('#span_Article_' + index).html(data.Article);
                            $('#id1_' + index).val(data.Id1);
                            $('#id2_' + index).val(data.Id2);
                            $('#id3_' + index).val(data.Id3);
                            if (data.Article != 'Nessun articolo trovato') {
                                $("#downart").show();
                            }
                            chkArticle();
                        }
                    }
                });

            });

            $(".fa-pencil-square-o, .glyphicon-search, .fa-share, .fa-caret-down, .fa-caret-up, .fa-edit").hover(function(){
                $(this).css("color","#2684b1");
                $(this).css("cursor","pointer");
            },function(){
                $(this).css("color","#fff");
                $(this).css("cursor","");
            });

            $(document).on('click','.fa-pencil-square-o',function () {

                var get_ArticleNumber_text = $(this).attr('id');
                Article = get_ArticleNumber_text.split("_");
                ArticleNumber_Text = Article[1];
                var text = $('#span_Article_'+ArticleNumber_Text).text();

                if($('#ArticleText_'+ArticleNumber_Text).is(":visible")) {

                    $('#textarea_'+ArticleNumber_Text).val('');
                    $('#ArticleText_'+ArticleNumber_Text).hide();
                    $('#span_Article_'+ArticleNumber_Text).html($('#art_'+ArticleNumber_Text).attr('desc'));
                }
                else {
                    $('#ArticleText_'+ArticleNumber_Text).show();
                    $('#span_Article_'+ArticleNumber_Text).html('');
                    $('#textarea_'+ArticleNumber_Text).val(text);
                }
            });

            $(document).on('click','#Edit_Contestazione',function (e) {
                $('#ReasonId').css("display","none");
                $('#Reason_Text').removeAttr( 'style' );

            });

            $('#downart').click(function () {

                ArticleNumber++;

                $("#BoxArticle_"+ArticleNumber).show();
                $("#upart").show();
                $(this).hide();
                if(ArticleNumber==5){
                    $("#downart").hide();
                }
                $('#ArticleNumber').val(ArticleNumber);

            });


            $('#upart').click(function () {

                $("#BoxArticle_"+ArticleNumber).hide();
                ArticleNumber--;

                if(ArticleNumber==1){
                    $("#upart").hide();
                    $("#downart").show();
                }else{
                    $("#downart").show();
                }
                $('#ArticleNumber').val(ArticleNumber);

                var totFee = 0;
                for(var i=1;i<=ArticleNumber;i++){
                    totFee = totFee + Number($('#Fee_'+i).val());
                }
                totFee = totFee + Number(totCharge);
                $('#span_TotalFee').html(totFee.toFixed(2));


            });

            $('.fa-share').click(function () {

                var VehicleTypeId = $("#VehicleTypeId").val();
                var VehiclePlate = $("#VehiclePlate").val();


                $.ajax({
                    url: 'ajax/ajx_src_trespasser_with_plate_exe.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {VehicleTypeId: VehicleTypeId, VehiclePlate: VehiclePlate},
                    success: function (data) {
                        var Genre           = data.Genre;
                        var a_Trespasser    = data.Trespasser;
                        var a_Vehicle       = data.Vehicle;
                        var a_Error         = data.Error;
                        if(a_Error['ErrorCode']==""){
                            $('#Genre').val(Genre);
                            if(Genre=="D"){
                                $('#CompanyName').val(a_Trespasser['CompanyName']);

                                $('#Div_Trespasser_Person').hide();
                                $('#Div_Trespasser_Company').show();

                            }else{
                                $('#Surname').val(a_Trespasser['Surname']);
                                $('#Name').val(a_Trespasser['Name']);
                                $('#BornDate').val(a_Trespasser['BornDate']);
                                $('#TaxCode').val(a_Trespasser['TaxCode']);
                                $('#BornPlace').val(a_Trespasser['BornPlace']);

                                $('#Div_Trespasser_Company').hide();
                                $('#Div_Trespasser_Person').show();
                            }


                            $('#Province').val(a_Trespasser['Province']);
                            $('#City').val(a_Trespasser['City']);
                            $('#TrespasserAddress').val(a_Trespasser['Address']);
                            $('#ZIP').val(a_Trespasser['ZIP']);
                            $('#Div_Trespasser_Address').show();


                            $('#VehiclePlate').val(a_Vehicle['VehiclePlate']);
                            $('#VehicleMass').val(a_Vehicle['VehicleMass']);



                            $('#VehicleBrand').val(a_Vehicle['VehicleBrand']);
                            $('#VehicleModel').val(a_Vehicle['VehicleModel']);
                            //$('#VehicleLastRevisionDate').val(a_Vehicle['VehicleLastRevisionDate']);
                            //$('#VehicleLastRevisionResult').val(a_Vehicle['VehicleLastRevisionResult']);
                            $('#Div_Vehicle').show();

                            /*
                             a_Vehicle['VehicleType']
                             a_Vehicle['dataInizioProprieta']
                             a_Vehicle['dataPrimaImmatricolazione']
                             a_Vehicle['numeroTelaio']
                             a_Vehicle['codiceOmologazione']
                             a_Vehicle['VehicleColor']
                             a_Vehicle['origine']


                             a_Vehicle['categoria']
                             a_Vehicle['usoVeicolo']

                             a_Vehicle['codiceAntifalsificazioneTagliandoUltimaRevisione']
                             a_Vehicle['numeroCartaCircolazione']
                             a_Vehicle['siglaUMC']
                             a_Vehicle['lunghezzaVeicoloInMetri']
                             a_Vehicle['larghezzaVeicoloInMetri']
                             a_Vehicle['numeroPostiTotali']

                             a_Vehicle['massaComplessivaRimorchioInKG']
                             a_Vehicle['taraInKG']

                             */

                        }else{
                            alert(a_Error['ErrorCode']+ ' ' + a_Error['ErrorDescription']);
                        }

                    }



                });
            });

        });
    </script>
    <script>
        $(document).ready(function(){
            var AccertatoreNumber = $('#AccertatoreNumber').val();
            if (AccertatoreNumber > 1){
                $("#upace").show();
            }
            $('#downace').click(function () {

                AccertatoreNumber++;
                $("#BoxAccertatore_"+AccertatoreNumber).show();
                $("#upace").show();
                $(this).hide();
                if(AccertatoreNumber==5){
                    $("#downace").hide();
                }
                $('#AccertatoreNumber').val(AccertatoreNumber);

            });

            $('#upace').click(function () {

                $("#BoxAccertatore_"+AccertatoreNumber).hide();
                $(".select_controller_"+AccertatoreNumber).val('');
                AccertatoreNumber--;

                if(AccertatoreNumber==1){
                    $("#upace").hide();
                }else{
                    $("#downace").show();
                }
                $('#AccertatoreNumber').val(AccertatoreNumber);


            });
            for(i=1;i<=5;i++){
                $('.select_controller_'+i).change(function () {
                    $("#downace").show();
                    var cont_id = $(this).attr('order')
                    var id = $(this).val();
                    var article = $('#id1_1').val();
                    var FineDate = $('#FineDate').val();
                    check_accertatore(id,article,FineDate,cont_id)
                });
            }
            function check_accertatore(id,article,FineDate,acce_number){
                $.ajax({
                    url: 'ajax/ajx_selected_controller.php',
                    type: 'POST',
                    data: {id: id, article: article, FineDate: FineDate,acce_number:acce_number},
                    success: function (data) {
                        if (data == "NOTOK") {
                            giaPresente = true;
                            $("#save").prop("disabled", true);
                            $("#span_acce_"+acce_number).addClass("help-block");
                            $("#span_acce_"+acce_number).html('IL controller e Ausilario!');
                            controller = false;

                        }
                        if (data == "OK") {
                            giaPresente = false;
                            $("#save").prop("disabled", false);
                            $("#span_acce_"+acce_number).removeClass("help-block");
                            $("#span_acce_"+acce_number).html('');
                            controller = true;
                        }
                        if (data == "NO") {
                            giaPresente = true;
                            $("#save").prop("disabled", true);
                            $("#span_acce_"+acce_number).addClass("help-block");
                            $("#span_acce_"+acce_number).html('Il controller non è nell\'incarico!');
                            controller = false;
                        }
                    }
                });
            }
        });

    </script>
    <script>
        $(document).ready(function () {
            $('#Code').change(function() {
                var FirstCode = $(this).val();
                var SendCode = $(this).val();
                var validCode = true;
                if (FirstCode.indexOf('/') > 0)
                {
                    FirstCode = FirstCode.replace("/","");
                }
                if($.isNumeric(FirstCode)==false){
                    if (FirstCode.length >2){
                        var str1 = FirstCode.substr(0,2);
                        var str2 = FirstCode.substr(2,FirstCode.length);
                        if ($.isNumeric(str2)==false){

                            $("#save").prop("disabled", true);
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Errato inserimento code!');
                            validCode = false;
                        }
                        if ($.isNumeric(str1[0])){
                            $("#save").prop("disabled", true);
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Errato inserimento code!');
                            validCode = false;
                        }
                    }else if (FirstCode.length==2){
                        var str1 = FirstCode.substr(0,1);
                        var str2 = FirstCode.substr(1,FirstCode.length);
                        if ($.isNumeric(str1)==true || $.isNumeric(str1)==false && $.isNumeric(str2)==false) {
                            $("#save").prop("disabled", true);
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Errato inserimento code!');
                            validCode = false;
                        }
                    }
                }
                if (validCode) {
                    $.ajax({
                        url: 'ajax/search_code.php',
                        type: 'POST',
                        data: {Code: SendCode,FineTypeId:1},
                        dataType:'json',
                        success: function (data) {
                            if (data.Result == "NO") {
                                $("#save").prop("disabled", true);
                                $("#span_code").addClass("help-block");
                                $("#span_code").html('Già presente!');
                                chkCode=false;
                            }
                            if (data.Result == "OK") {
                                $("#save").prop("disabled", false);
                                $("#span_code").removeClass("help-block");
                                $("#span_code").html('');
                                chkCode = true;
                            }
                        }
                    });
                }

            });

        })
    </script>
    <script>
        $(document).ready(function () {

            $(".add_button_10").click(function () {

                $('#TitleTrespasser').html('Inserimento ditta noleggio');
                $('#TrespasserTypeId').val(10);
                $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
            });
            $(".add_button_11").click(function () {

                $('#TitleTrespasser').html('Inserimento noleggiante');
                $('#TrespasserTypeId').val(11);
                $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
            });
            $(".close_window_trespasser").click(function () {

                $('#Div_Windows_Insert_Trespasser').hide();
            });
            $(".close_window_article").click(function () {

                $('#BoxArticleSearch').hide();
            });

            $('#tab_company10').click(function () {
                var NumberTab = 10;

                $('#Genre'+NumberTab).val('D');
                $('#Surname'+NumberTab).val('');
                $('#Name'+NumberTab).val('');

            });
            $('#tab_Company_src').click(function () {
                var NumberTab = 10;
                $('#Genre').val('D');
                $('#Surname'+NumberTab).val('');
                $('#Name'+NumberTab).val('');
            });
            $('#tab_Trespasser10').click(function () {
                var NumberTab = 10;
                $('#Genre'+NumberTab).val('M');
                $('#CompanyName'+NumberTab).val('');
            });

            $('#tab_company11').click(function () {
                var NumberTab = 11;

                $('#Genre'+NumberTab).val('D');
                $('#Surname'+NumberTab).val('');
                $('#Name'+NumberTab).val('');

            });

            $('#tab_Trespasser11').click(function () {
                var NumberTab = 11;
                $('#Genre'+NumberTab).val('M');
                $('#CompanyName'+NumberTab).val('');
            });

            $('#CompanyName10').keyup({ NumberTab:10 },chkTrespasser);
            $('#Name10').keyup({ NumberTab:10 },chkTrespasser);
            $('#Surname10').keyup({ NumberTab:10 },chkTrespasser);

            $('#CompanyName11').keyup({ NumberTab:11 },chkTrespasser);
            $('#Name11').keyup({ NumberTab:11 },chkTrespasser);
            $('#Surname11').keyup({ NumberTab:11 },chkTrespasser);

            function chkTrespasser(e){
                var NumberTab = e.data.NumberTab;
                var min_length = 3;


                if(NumberTab<14){
                    var CompanyName = $('#CompanyName' + NumberTab).val();
                }else{
                    var CompanyName = '';
                    var Genre = '';
                }

                var TaxCode = '';
                if(Genre == "D"){
                    TaxCode = $('#TaxCode2_' + NumberTab).val();
                } else TaxCode = $('#TaxCode' + NumberTab).val();

                var Name = $('#Name' + NumberTab).val();
                var Surname = $('#Surname' + NumberTab).val();
                var FineDate = $('#FineDate').val();
                if (Name !="" || Surname!=""){
                    var Genre = $('#Genre').val();
                } else {
                    var Genre = $('#Genre' + NumberTab).val();
                }

                if (CompanyName.length >= min_length || Surname.length >= min_length || Name.length >= min_length) {
                    $.ajax({
                        url: 'ajax/search_trespasser_rent.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {FineDate:FineDate, CompanyName: CompanyName, Surname: Surname, Name: Name, Genre: Genre, NumberTab: NumberTab, TaxCode:TaxCode},
                        success: function (data) {

                            $('#trespasser_content_' + NumberTab ).show();
                            $('#trespasser_content_' + NumberTab).html(data.Trespasser);

                        }
                    });
                } else {
                    $('#trespasser_content_' + NumberTab).hide();
                }
            }

            $( function() {
                $( "#Div_Windows_Insert_Trespasser" ).draggable();
                $( "#BoxArticleSearch" ).draggable();
            } );

            $('#f_ins_trespasser').bootstrapValidator({
                live: 'disabled',
                fields: {
                    frm_field_required: {
                        selector: '.trespasser_frm_field_required',
                        validators: {
                            notEmpty: {
                                message: 'Richiesto'
                            }
                        }
                    },

                }
            }).on('success.form.bv', function(event){

                event.preventDefault();
                var form = $("#f_ins_trespasser").serialize();
                var TaxCode = $('#TaxCode').val();
                var surname = $('#Surname').val();
                var name = $('#Name').val();
                var companyname = $('#CompanyName').val();
                var genre = $('#Genre').val();
                var validateform = true;
                if (genre != 'D') {
                    if(surname == '' || name == ''){
                        validateform = false;
                    }
                } else {
                    if (companyname == '') {
                        validateform = false;
                    }
                }
                if (genre!='D' && !$("input[name='Sex']").is(':checked')) {
                    $("#btn_saveanagrafica").prop("disabled", true);
                    $("#sex_code").addClass("help-block");
                    $("#sex_code").html('Richiesto!');
                    validateform = false;
                } else {
                    validateform = true;
                }
                if (validateform == true) {
                    $.ajax({
                        url: 'ajax/checkpiva.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {TaxCode: TaxCode},
                        success: function (data) {
                            if (data == 'Exists' && TaxCode != "") {
                                $('#erroriva').html('Il trassgressore esiste nella database.');
                            } else {
                                $('#erroriva').html(' ');
                                $.ajax({
                                    url: 'ajax/ajx_add_trespasser_exe.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    cache: false,
                                    data: form,
                                    success: function (data) {

                                        $("#span_name_"+$('#TrespasserTypeId').val()).html(data.TrespasserName);
                                        $("#TrespasserId"+$('#TrespasserTypeId').val()).val(data.TrespasserId);
                                        $(".add_button_"+$('#TrespasserTypeId').val()).hide();
                                        $("#f_ins_trespasser").trigger("reset");

                                        $("#Div_Windows_Insert_Trespasser").hide();
                                        $('#overlay').fadeOut('fast');

                                    },
                                    error: function (result) {
                                        alert("error");
                                    }
                                });
                            }
                        }
                    });
                }
            });

            $(".close_window_trespasser").click(function () {
                $('#overlay').fadeOut('fast');
                $('#Div_Windows_Insert_Trespasser').hide();
            });

            $("#overlay").click(function () {
                $(this).fadeOut('fast');
                $('#Div_Windows_Insert_Trespasser').hide();

            });

            $('#sexM').click(function () {
                $('#Genre').val('M');
                $("#sexF").prop("checked", false);
                $("#btn_saveanagrafica").prop("disabled", false);
                $("#sex_code").removeClass("help-block");
                $("#sex_code").html('');
            });
            $('#sexF').click(function () {
                $('#Genre').val('F');
                $("#sexM").prop("checked", false);
                $("#btn_saveanagrafica").prop("disabled", false);
                $("#sex_code").removeClass("help-block");
                $("#sex_code").html('');
            });

            // $('#tab_Company').click(function () {
            //     $('#Genre').val('D');
            //     $("#sexM").prop("checked", true);
            //     $("#sexF").prop("checked", false);
            //
            //     $('#TaxCode').val('');
            //
            // });
            //
            // $('#tab_Trespasser').click(function () {
            //     $('#Genre').val('M');
            //
            //     $('#TaxCode').val('');
            //     $('#CompanyName').val('');
            //
            // });
        });
    </script>


<?php
include(INC."/footer.php");
