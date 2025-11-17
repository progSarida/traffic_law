rti<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$_form_type_id = null;

//$a_FineTypeId = array("","","","","","");
//$rs_Fine= $rs->Select("Fine","CityId='".$_SESSION['cityid']."'", "RegDate DESC");
//if(mysqli_num_rows($rs_Fine)>0){
//    $r_Fine = mysqli_fetch_array($rs_Fine);
//    $a_FineTypeId[$r_Fine['FineTypeId']] = " SELECTED ";
//}

if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    $class = strlen($answer)>50?"alert-warning":"alert-success";
    echo "<div class='alert $class message'>$answer</div>";
    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 4000);
    </script>
    <?php
}



$RegDate = Date('Y-m-d');

$str_Where = "RegDate='".$RegDate."' and UserId ='".$_SESSION['username']."' AND CityId ='".$_SESSION['cityid']."' AND ProtocolYear = ".$_SESSION['year'];


$rs_PreviousFine = $rs->Select("V_Fine",   $str_Where, "Id DESC LIMIT 1");
$r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);
$controller_id = $r_PreviousFine['ControllerId'];
$cont_name = $r_PreviousFine['ControllerName'];
$fineId = $r_PreviousFine['Id'];

$cont_code = $r_PreviousFine['ControllerCode'];
$controller_name = $cont_code .' '.$cont_name;


$PreviousFineDate = ($r_PreviousFine['FineDate']!="") ? DateOutDB($r_PreviousFine['FineDate']) : "";
$PreviousAddress = utf8_encode($r_PreviousFine['Address']);

/*
$PreviousArticle    = $r_PreviousFine['Article'];
$PreviousLetter     = $r_PreviousFine['Letter'];
$PreviousParagraph  = $r_PreviousFine['Paragraph'];
$PreviousArticleDescription = $r_PreviousFine['ArticleDescriptionIta'];
*/
$PreviousArticle    = "";
$PreviousLetter     = "";
$PreviousParagraph  = "";
$PreviousArticleDescription = "";




$PreviouVehicleTypeId = $r_PreviousFine['VehicleTypeId'];
if($PreviouVehicleTypeId=="") $PreviouVehicleTypeId=1;


echo "<div class='check'></div>";



$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

if($n_Code>0){
    $str_Locality='
                   
                        <div class="col-sm-2 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select name="Locality" class="form-control" style="width:10rem;">
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
        <div class="col-sm-3 BoxRowCaption">';

$str_Toponym .= CreateSelect(MAIN_DB.'.Toponym', '1=1', 'Id', 'Toponym', 'Title', 'Title', '', false);
$str_Toponym .='</div>';
$str_Toponym .='
        <div class="col-sm-4 BoxRowLabel">
            Seleziona Strada
        </div>
        <div class="col-sm-3 BoxRowCaption">';

$str_Toponym .= CreateSelect('StreetType', '1=1', 'Id', 'StreetType', 'Title', 'Title', '', false);
$str_Toponym .= '</div>';

if (isset($_GET['back']) && $_GET['back']=='true'){
    $str_BackPage = 'mgmt_report.php?PageTitle=Verbali/Gestione%20verbali';
}

$str_out .= '
        <form name="f_violation" id="f_violation" method="post" action="mgmt_report_add_exe.php' . $str_GET_Parameter . '" xmlns="http://www.w3.org/1999/html">
        <input type="hidden" name="ArticleNumber" id="ArticleNumber" value="1">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="1">
        <input type="hidden" id="TrespasserId10" name="TrespasserId10" value="">
        <input type="hidden" id="TrespasserId11" name="TrespasserId11" value="">
        <input type="hidden" id="TrespasserId15" name="TrespasserId15" value="">
        <input type="hidden" id="TrespasserId16" name="TrespasserId16" value="">
        <input type="hidden" id="InsertTrasgressor" name="InsertTrasgressor" value="false">
        <input type="hidden" id="has_patria_potesta1" value="0">
        <input type="hidden" id="has_patria_potesta2" value="0">
        <input type="hidden" id="has_patria_potesta3" value="0">
        <input type="hidden" id="art_1" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_2" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_3" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_4" fee="" maxFee="" addMass="">
        <input type="hidden" id="art_5" fee="" maxFee="" addMass="">
        <input type="hidden" name="P" value="' . $str_CurrentPage . '">
        <input type="hidden" name="b_Rent" value="1">
        <input type="hidden" name="LicenseDatePropretario" id="LicenseDatePropretario">
        <input type="hidden" name="LicenseDateTrasgressore" id="LicenseDateTrasgressore">
        
        
        
    	<div class="row-fluid">
        	<div class="col-sm-6">
        	    <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel" style="height: 3.5rem; font-size: large; text">
                        <strong>Tipo verbale</strong>
                    </div>
                    <div class="col-sm-5 BoxRowCaption" style="height: 3.5rem; font-size: large; text">
                        <select class="form-control frm_field_required" name="FineTypeId" id="FineTypeId" style="width:23rem; height: 2.9rem; font-size: large; text">
                        ';
                        $str_out.='
                            <option value="3" selected>Normale</option>
                            <option value="4">Contratto</option>
                            <option value="5">D’ufficio</option>
                            
                        </select>
                    </div>
                    <div class="col-sm-3 BoxRowLabel" style="height: 3.5rem; font-size: large; text">
                         Eludi Controlli
                    </div>
                    <div class="col-sm-1 BoxRowCaption" style="height: 3.5rem; font-size: large; text">
                        <input type="checkbox" id="Conrolli" name="Controlli">
                    </div>
        	    </div>';


                $differita = 'Differita';

require(INC . '/page/fine/vehicle.php') ;
require(INC . '/page/fine/article.php') ;
require(INC . '/page/fine/controller.php');
require(INC . '/page/fine/reason.php');

 				$str_out .='

	        	<div class="col-sm-12" id="DIV_DayNumber_180" style="display:none;">
        			<div class="col-sm-5 BoxRowLabel">
        				Giorni presentazione documenti
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" value="0" name="DayNumber_180" id="DayNumber_180" style="width:6rem">
 					</div>
  				</div>


	        	<div class="col-sm-12">
	        	    <div class="col-sm-3 BoxRowLabel">
        				Spese addizionali ente
					</div>
					<div class="col-sm-3 BoxRowCaption">
				    	<input class="form-control frm_field_numeric" type="text" name="CustomerAdditionalFee" id="CustomerAdditionalFee" value="0.00">
					</div>
        			<div class="col-sm-3 BoxRowLabel">
        				Importo totale
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				<span id="span_TotalFee"></span>
					</div>
  				</div>';



$str_out .='
  				<div class="clean_row HSpace4"></div>
   				<div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Trasgressore
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        <select class="form-control frm_field_required" name="TrespasserType" id="TrespasserType" style="width:40rem;">
                            <option value="1">Proprietario (Proprietario/Trasgressore)</option>
                            <option value="2">Obbligato / Trasgressore (Proprietario + Trasgressore/conducente)</option>
                            <option value="3">Noleggio(Leasing) / Noleggiante(Obbligato/Trasgressore)</option>
                        </select>

                    </div>
   				</div>
                <div class="clean_row HSpace4"></div>
                <div id="DIV_TrespasserType" style="display:none">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                        <span id="trasgressor1">TRASGRESSORE:</span> <span id="span_name_11"></span><span id="error_name_11"></span>                   
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Data notifica
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date" name="NotificationDate_11" id="NotificationDate_11" style="width:10rem">
                    </div>  
                    <div class="col-sm-2 BoxRowCaption">
                        <select name="NotificationType_11" id="NotificationType_11">
                            <option></option>
                            <option value="1">Su strada</option>
                            <option value="2">Messo</option>
                            <option value="3">Ufficio</option>
                        </select>
                        <span id="notification_11"></span>
                    </div>
                </div>


                <div class="clean_row HSpace4"></div>       
                <div class="col-sm-12" style="height:20rem;">
                    <ul class="nav nav-tabs" id="mioTab">
                        <li class="active" id="tab_Trespasser11"><a href="#Trespasser11" data-toggle="tab">PERSONA FISICA</a></li>
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
                </div>                                  
                </div>              
                    
                <div class="clean_row HSpace4"></div>
                <div id="DIV_Tutor_11" style="display:none">
                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel">
                            ESERCENTE PATRIA POTESTA: <span id="span_name_16"></span>   
                            <span id="error_name_16"></span>                    
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input type="text" class="form-control frm_field_date" name="NotificationDate_16" id="NotificationDate_16" style="width:10rem">
                        </div>    
                        <div class="col-sm-2 BoxRowCaption">
                            <select name="NotificationType_16" id="NotificationType_16">
                                <option value="1">Su strada</option>
                                <option value="2">Messo</option>
                                <option value="3">Ufficio</option>
                            </select>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span class="glyphicon glyphicon-plus-sign add_button_16" style="color:#294A9C;position:absolute; right:10px;top:3px;font-size:17px; "></span> 
                        </div>
                    </div>
    
    
                    <div class="clean_row HSpace4"></div>       
                    <div class="col-sm-12" style="height:20rem;">
    
                        <div class="row-fluid">
            
                            <div class="col-sm-12">
                                <div class="col-sm-3 BoxRowLabel">
                                    Cognome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Surname16" id="Surname16" style="width:12rem">
                                </div>
                            
                                <div class="col-sm-3 BoxRowLabel">
                                    Nome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Name16" id="Name16" style="width:12rem">
                                </div>
                               
                                
                            </div>
                                 
                        </div>
                                           
                        <div id="trespasser_content_16" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>                                  
                </div>
  				<div class="clean_row HSpace4"></div> 		
  				<div id="proprietario">		
                    <div>
                        <div class="col-sm-12">
                            <div class="col-sm-6 BoxRowLabel">
                                <span id="trasgressor2">PROPRIETARIO:</span> <span id="span_name_10"></span> <span id="error_name_10"></span>   
                                <input type="hidden" id="propretario">    				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data notifica
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="NotificationDate_10" id="NotificationDate_10" style="width:10rem">
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <select name="NotificationType_10" id="NotificationType_10">
                                    <option></option>
                                    <option value="1">Su strada</option>
                                    <option value="2">Messo</option>
                                    <option value="3">Ufficio</option>
                                </select>
                                <span id="notification_10"></span>
                            </div>
                        </div>
                    </div>

                    <div class="clean_row HSpace4"></div>   	
                    <div class="col-sm-12" style="height:20rem;">
                        <ul class="nav nav-tabs" id="mioTab">
                            <li class="active" id="tab_Trespasser10"><a href="#Trespasser10" data-toggle="tab">PERSONA FISICA</a></li>
                            <li id="tab_company10"><a href="#company10" data-toggle="tab">DITTA</a></li>
                            
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
                </div>
  				  				
  				<div class="clean_row HSpace4"></div>
   				<div id="DIV_Tutor_10" style="display:none">
                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel">
                            ESERCENTE PATRIA POTESTA: <span id="span_name_15"></span>
                            <span id="error_name_15"></span>      				
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input type="text" class="form-control frm_field_date" name="NotificationDate_15" id="NotificationDate_15" style="width:10rem">
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <select name="NotificationType_15" id="NotificationType_15">
                                <option value="1">Su strada</option>
                                <option value="2">Messo</option>
                                <option value="3">Ufficio</option>
                            </select>
                        </div>     
                        <div class="col-sm-1 BoxRowCaption">
                            <span class="glyphicon glyphicon-plus-sign add_button_15" style="color:#294A9C;position:absolute; right:10px;top:3px;font-size:17px; "></span> 
                        </div>                   
                    </div>
    
    
                    <div class="clean_row HSpace4"></div>   	
                    <div class="col-sm-12" style="height:20rem;">
    
                        <div class="row-fluid">
            
                            <div class="col-sm-12">
                                <div class="col-sm-3 BoxRowLabel">
                                    Cognome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Surname15" id="Surname15" style="width:12rem">
                                </div>
                            
                                <div class="col-sm-3 BoxRowLabel">
                                    Nome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Name15" id="Name15" style="width:12rem">
                                </div>
                                
                                
                            </div>
                                 
                        </div>
                                           
                        <div id="trespasser_content_15" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>  				  				
  				</div>
  				</div>
  				
  				<div class="col-sm-6" >
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                     DOCUMENTAZIONE 
                    </div> 
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:10rem;">
                    <div class="example">
                        <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                    <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"/>
                    </div>
                    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>                
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Documento
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <input type="hidden" name="Documentation" id="Documentation" value="">
                        <span id="span_Documentation" style="height:6rem;width:40rem;font-size:1.1rem;"></span>
                    </div>
                </div>
                <div class="col-sm-12 BoxRow">    

                </div>					
            </div>
  				  				  				
  				  				  				
  				  				  				
  				  				  				
  				  				  				
  				  				
                <div class="col-sm-12 BoxRowTitle" >
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                        Elaborazioni
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Elaborare il sollecito di pagamento/ingiunzione in caso di infedele/tardivo/omesso pagamento
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <select name="Payment">
                            <option value="1">SI
                            <option value="0">NO
                        </select>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Elaborare il verbale art. 126 Bis in caso di omessa comunicazione dei dati del trasgressore
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                         <select name="126Bis">
                            <option value="1">SI
                            <option value="0">NO
                        </select>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <select name="PresentationDocument">
                            <option value="1">SI
                            <option value="0">NO
                        </select>                    
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Procedura con la decurtazione punti della patente di guida del trasgressore comunicato
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <select name="LicensePoint">
                            <option value="1">SI
                            <option value="0">NO
                        </select> 
                    </div>
                </div>  	
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Chiudi il verbale
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <select name="CloseFine">
                            <option value="0">NO
                            <option value="1">SI
                        </select> 
                    </div>
                </div> 
                 <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel" style="height:2.8rem;">
        				Note motivazione
					</div>
                    <div class="col-sm-8 BoxRowCaption" style="height:2.8rem;">
                        <textarea name="NoteProcedure" class="form-control frm_field_string" style="width:100rem;height:2.4rem"></textarea>	
					</div>
                </div>			  							
  			</div> 
  				
	
            ';

require(INC.'/page/VerbaleForm/salva.php');

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
';
require(INC . "/module/mod_trespasser.php");



echo $str_out;
?>

<script type="text/javascript">
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
                var FineTypeId = $('#FineTypeId').val();

                if ($('#art_' + i).attr('addMass') == 1) {
                    if(mass<addMass){
                        sum+=Fee;
                        if(FineTypeId==4){
                            $('#Fee_'+i).prop('type', 'text').val(Fee.toFixed(2));
                            $('#MaxFee_'+i).prop('type', 'text').val(MaxFee.toFixed(2));
                        }else{
                            $('#Fee_'+i).val(Fee.toFixed(2));
                            $('#span_Fee_'+i).html(Fee.toFixed(2));
                            $('#MaxFee_'+i).val(MaxFee.toFixed(2));
                            $('#span_MaxFee_'+i).html(MaxFee.toFixed(2));
                        }
                    } else {
                        sum+=Fee*2;
                        if(FineTypeId==4){
                            $('#Fee_'+i).prop('type', 'text').val((Fee*2).toFixed(2));
                            $('#MaxFee_'+i).prop('type', 'text').val((MaxFee*2).toFixed(2));
                        } else {
                            $('#Fee_'+i).val((Fee*2).toFixed(2));
                            $('#span_Fee_'+i).html((Fee*2).toFixed(2));
                            $('#MaxFee_'+i).val((MaxFee*2).toFixed(2));
                            $('#span_MaxFee_'+i).html((MaxFee*2).toFixed(2));
                        }
                    }
                }
                if ($('#art_' + i).attr('addMass') == '0') {
                    sum+=Fee;
                }
            }
            sum += parseFloat($('#CustomerAdditionalFee').val());
            $('#span_TotalFee').html(sum.toFixed(2));
        }

</script>



<script type="text/javascript">
    var chkDate = true;
    var controller = true;
    var chkCode = true;
    var giaPresente = false;
    var chkTime = true;
    var tresRequired = 0;

    var namespan;

    $('.find_list').keyup(function () {
        var min_length = 2; // min caracters to display the autocomplete
        var keyword = $(this).val();

        namespan = $(this).attr('name');
        if (keyword.length >= min_length) {

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: keyword, field: namespan},
                success: function (data) {

                    $('#' + namespan + '_List').show();
                    $('#' + namespan + '_List').html(data);
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

    function set_item(item, id) {
        // change input value
        $('#' + namespan).val(item);
        //$('#CustomerID').val(id);
        $('#' + namespan + '_List').hide();
    }

    $('document').ready(function(){
        var ArticleNumber = $('#ArticleNumber').val();

        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        <?= CreateSltChangeJQ("Controller", "CityId='".$_SESSION['cityid']."' AND Disabled=0", "Name", "ControllerCode", "Code", "Id", "ControllerId") ?>


        $(document).on('click', '.add_article', function(){
            chkArticle();
        });

        $('#SpeedLimit').focusout(chkSpeed);
        $('#SpeedControl').focusout(chkSpeed);



        $('#FineTime').change(function() {

            var str = $('#FineTime').val();
            if(str.length==4){
                $('#FineTime').val( str.substr(0,2) + ":" + str.substr(2,2) );
                str = $('#FineTime').val();
            }

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
        });


        $('#fileTreeDemo_1').fileTree({ root: 'public/_REPORT_/<?= $_SESSION['cityid']?>/', script: 'jqueryFileTree.php' }, function(file) {

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



        $('#Toponym').change(function() {
            var type = $(this).val();
            $('#Address').val(type + ' ' + $('#StreetType').val());

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').html(data);
                }
            });
        });
        $('#StreetType').change(function() {
            var type = $(this).val();
            $('#Address').val($('#Toponym').val() + ' ' + type);

            $.ajax({
                url: 'ajax/search.php?searchtype=t',
                type: 'POST',
                data: {keyword: $('#Address').val(), field: 'Address'},
                success: function (data) {
                    $('#Address_List').html(data);
                }
            });
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
                        if (data.strSpeed == 1) {
                            $('#DIV_Title_Speed').show();
                            $('#DIV_Speed').show();

                            $("#SpeedLimit").prop("readonly", false);
                            $("#SpeedControl").prop("readonly", false);

                            $("#id1_"+ArticleNumber).val('');
                            $("#id2_"+ArticleNumber).val('');
                            $("#id3_"+ArticleNumber).val('');

                            $("#id1_"+ArticleNumber).prop("disabled", true);
                            $("#id2_"+ArticleNumber).prop("disabled", true);
                            $("#id3_"+ArticleNumber).prop("disabled", true);

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


                            $("#id1_"+ArticleNumber).prop("disabled", true);
                            $("#id2_"+ArticleNumber).prop("disabled", false);
                            $("#id3_"+ArticleNumber).prop("disabled", false);

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


        $('#VehiclePlate').change(function() {
            var VehiclePlate = $(this).val();
            var FineDate = $('#FineDate').val();
            var FineTime = $('#FineTime').val();
            var id = $( "#CountryId" ).val();
            var checkPlate;
            var find;
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

        //submit
        var type = $('#TrespasserType').val();




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

                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
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

                FineTime: {
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        },
                        regexp: {
                            //regexp: '^(1?[0-9]|2[0-9]):[0-5][0-9]$',
                            regexp: '^(0?[0-9]|1?[0-9]|2[0-4]):[0-5][0-9]$',
                            message: 'Ora non valida'
                        }
                    }

                },

            }
        });

        function chkArticle() {

            var id1=$('#id1_'+ArticleNumber).val();
            var id2=$('#id2_'+ArticleNumber).val();
            var id3=$('#id3_'+ArticleNumber).val();

            var FineTypeId = $('#FineTypeId').val();
            var FineTime = $('#FineTime').val();



            var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();



            if(id1!='' && (id2!='' || id3!='')){

                var RuleTypeId = $('#RuleTypeId').val();

                var request = $.ajax({
                    url: "ajax/search_article.php",
                    dataType: 'json',
                    type: "post",
                    cache: false,
                    data: "RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3+"&FineTime=" + FineTime
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
                    $('#ReasonId').val(response.reasonId);


                    if(response.Result==1){
                        $('#ArticleId_'+ArticleNumber).val(response.ArticleId);

                        if(ArticleNumber==1){
                            $('#ViolationTypeId').val(response.ViolationTypeId);
                            $('#span_ViolationTitle').html(response.ViolationTitle);
                        }


                        if(response.PresentationDocument==0){
                            $('#DIV_DayNumber_180').hide();
                            $("#DayNumber_180").val('0');
                        } else {
                            $('#DIV_DayNumber_180').show();
                        }
                        $('#artcomune_'+ArticleNumber).val(response.ArtComunali);
                        $('#art_'+ArticleNumber).attr('fee', response.Fee);
                        $('#art_'+ArticleNumber).attr('addMass', response.AddMass);
                        $('#art_'+ArticleNumber).attr('maxFee', response.MaxFee);
                        $('#art_'+ArticleNumber).attr('desc', response.Description);
                        $('#AdditionalSanctionSelect_'+ArticleNumber).html(response.AdditionalSanctionSelect);
                        $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                        //$('#LicensePoint_'+ArticleNumber).html(response.LicensePoint);




                        if(FineTypeId==4){
                            $('#Fee_'+ArticleNumber).prop('type', 'text').val(response.Fee);
                            $('#MaxFee_'+ArticleNumber).prop('type', 'text').val(response.MaxFee);
                            $('#EditArticle_'+ArticleNumber).show();
                        }else{
                            $('#EditArticle_'+ArticleNumber).show();
                            $('#Fee_'+ArticleNumber).val(response.Fee);
                            $('#span_Fee_'+ArticleNumber).html(response.Fee);
                            $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                            $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);
                        }

                        checkFee();


                        var totFee = 0;
                        for(var i=1;i<=ArticleNumber;i++){
                            totFee = totFee + Number($('#Fee_'+i).val());
                        }
                        totFee = totFee + Number(CustomerAdditionalFee);
                        $('#span_TotalFee').html(totFee.toFixed(2));


                    }


                });
                request.fail(function (jqXHR, textStatus){
                    $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
                });

                //event.preventDefault();
            }

        }


        $('#CustomerAdditionalFee').change(function(){
            var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();

            var totFee = 0;
            for(var i=1;i<=ArticleNumber;i++){
                totFee = totFee + Number($('#Fee_'+i).val());
            }
            totFee = totFee + Number(CustomerAdditionalFee);

            $('#span_TotalFee').html(totFee.toFixed(2));



        });

        function chkSpeed() {
            var DetectorId = $('#DetectorId').val();
            var SpeedLimit=$('#SpeedLimit').val();
            var SpeedControl=$('#SpeedControl').val();
            var FineTime = $('#FineTime').val();
            var RuleTypeId = $('#RuleTypeId').val();
            var NotificationType = $('#NotificationType').val();

            var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();

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
        $('#tab_Trespasser_src').click(function () {
            var NumberTab = 10;
            $('#Genre').val('M');
            $("#sex_code").html("");
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


        $('#Name15').keyup({ NumberTab:15 },chkTrespasser);
        $('#Surname15').keyup({ NumberTab:15 },chkTrespasser);

        $('#Name16').keyup({ NumberTab:16 },chkTrespasser);
        $('#Surname16').keyup({ NumberTab:16 },chkTrespasser);

        //function trespasser
        function chkTrespasser(e){
            var NumberTab = e.data.NumberTab;

            var min_length = 3;

            if(NumberTab<14){
                var CompanyName = $('#CompanyName' + NumberTab).val();
                //var Genre = $('#Genre').val();
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
        $(document).on('change','#FineDate',function() {
            var Notifica = $("#NotificationType").val();
            if(Notifica == 2){
                $("#NotificationDate_10").val($(this).val());

            }
            for(i=1;i<=5;i++) {
                $('.select_controller_'+i).trigger("change");
            }
        });

        $("#NotificationDate_10").on("change",function () {
            //$("#NotificationType_10").attr("required",true);
            $("#notification_10").addClass("help-block");
            $("#notification_10").html('Richiesto!');
            $('#NotificationType_10').addClass('frm_field_required');
            if ($("#NotificationDate_10").val() ==""){
                $("#notification_10").removeClass("help-block");
                $("#notification_10").html('');
                $('#NotificationType_10').removeClass('frm_field_required');
                if ($("#NotificationType").val()==1) {
                    $("#span_name_10").removeClass("help-block");
                    $("#span_name_10").html('');
                }
            }

        });
        $("#NotificationDate_11").on("change",function () {
            $("#notification_11").addClass("help-block");
            $("#notification_11").html('Richiesto!');
            $('#NotificationType_11').addClass('frm_field_required');
            if ($("#NotificationDate_11").val() ==""){
                $("#notification_11").removeClass("help-block");
                $("#notification_11").html('');
                $('#NotificationType_11').removeClass('frm_field_required');
                if ($("#NotificationType").val()==1) {
                    $("#span_name_11").removeClass("help-block");
                    $("#span_name_11").html('');
                }
            }
        });

        $("#NotificationType").on('change',function () {
            var id = $(this).val();
            var finedate = $("#FineDate").val();

            if (id ==2){
                $("#NotificationDate_10").val(finedate);
                var search = "Su strada";
                $('#NotificationType_10 option:contains('+search+')').prop('selected',true);

                $("#NotificationDate_10").attr("readonly",true);
                $("#NotificationDate_10").attr("required",true);
                $("#NotificationDate_10").addClass("frm_field_required")

            }else{
                $("#NotificationDate_10").val('');
            }
        });
        $(document).on("change","#NotificationType_10",function () {

            var id = $(this).val();
            var finedate = $("#FineDate").val();
            if (id !=1){
                if ( $("#NotificationDate_10").val() =="") {
                    $("#NotificationDate_10").val(finedate);
                }
                $("#NotificationDate_10").attr("readonly",false);
            }else{
                $("#NotificationDate_10").val(finedate);
                $("#NotificationDate_10").attr("readonly",true);
            }
            $("#notification_10").removeClass("help-block");
            $("#notification_10").html('');
        });

        $(document).on("change","#NotificationType_11",function () {
            var finedate = $("#FineDate").val();
            $("#NotificationDate_11").val(finedate);
            $("#notification_11").removeClass("help-block");
            $("#notification_11").html('');
        });

        //trespasser_type
        $('#TrespasserType').on('change', function() {
            type = this.value;
            var finedate = $("#FineDat9e").val();
            var notifica = $("#NotificationType").val();
            if(notifica == 2 && type==2){
                $("#NotificationDate_11").val(finedate);
                var search = "Su strada";
                $('#NotificationType_11 option:contains('+search+')').prop('selected',true);
                $("#NotificationDate_11").attr("readonly",true);
                $('#NotificationDate_11').addClass('frm_field_required');
                $("#NotificationDate_10").val("");
                $("#NotificationType_10").val("");
                $("#NotificationDate_10").attr("readonly",false);
            }
            if(this.value==1){
                tresRequired = 1;
                $('#trasgressor2').html('PROPRIETARIO:');
                $('#DIV_TrespasserType').hide();
                $('#proprietario').show();
                $('#NotificationDate_10').removeClass('frm_field_required');

            } else if(this.value==2){
                tresRequired = 2;
                $('#trasgressor2').html('PROPRIETARIO:');
                $('#trasgressor1').html('TRASGRESSORE:');
                $('#DIV_TrespasserType').show();
                $('#proprietario').show();
                $('#NotificationDate_10').removeClass('frm_field_required');
            } else {
                tresRequired = 2;
                $('#NotificationDate_10').addClass('frm_field_required');
                $('#trasgressor2').html('NOLEGGIANTE:');
                $('#trasgressor1').html('NOLEGGIO:');
                $('#proprietario').show();
                $('#DIV_TrespasserType').show();
            }
        });

        //add_button

        $(".add_button_10").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggio');
            } else {
                $('#TitleTrespasser').html('Inserimento Obbligato');
            }
            $('#TrespasserTypeId').val(10);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();
            $('#Fine_Date_Get').val(finedate_);

            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_11").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggiante');
            } else {
                $('#TitleTrespasser').html('Inserimento Trasgressore');
            }
            $('#TrespasserTypeId').val(11);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();

            $('#Fine_Date_Get').val(finedate_);

            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_15").click(function () {
            $('#TitleTrespasser').html('Inserimento Trasgressore');
            $('#TrespasserTypeId').val(15);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_16").click(function () {
            $('#TitleTrespasser').html('Inserimento Obbligato');
            $('#TrespasserTypeId').val(16);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });

        //anagrafica
        $('#btn_saveanagrafica').on('click',function () {
            var TrespasserType = $('#TrespasserType').val();

            var Fine_Date_Get = $('#Fine_Date_Get').val();
            var new_fine = Fine_Date_Get.split("/");
            var newfinedt = new_fine[2]+"/"+new_fine[1]+"/"+new_fine[0];

            var BornDate = $('#BornDate').val();
            var new_born = BornDate.split("/");
            var new_borndt = new_born[2]+"/"+new_born[1]+"/"+new_born[0];

            var TrespasserTypeId = $('#TrespasserTypeId').val();

            $("#error_name_"+$('#TrespasserTypeId').val()).removeClass("help-block");
            $("#error_name_"+$('#TrespasserTypeId').val()).html('');

            $("#error_name_10").removeClass("help-block");
            $("#error_name_10").html('');

            $("#error_name_11").removeClass("help-block");
            $("#error_name_11").html('');
            var years = new Date(new Date(newfinedt)- new Date(new_borndt)).getFullYear() - 1970;
            if (TrespasserType !=3){
                if (TrespasserTypeId==10){

                    if (years < 18) {
                        $("#DIV_Tutor_10").show();
                        $("#has_patria_potesta1").val(1);
                        $("#has_patria_potesta3").val(1);

                    }
                }
                if (TrespasserTypeId==11) {

                    if (years < 18) {

                        $("#has_patria_potesta2").val(1);
                        $("#DIV_Tutor_11").show();
                    }
                }
            }



        });


        $(".close_window_article").click(function () {

            $('#BoxArticleSearch').hide();
        });



        $(".fa-pencil-square-o, .glyphicon-search, .fa-share, .fa-caret-down, .fa-caret-up, .fa-edit").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });


        $("#Edit_Contestazione").click(function () {
            if($("#ReasonOwner").is(":hidden")){
                $('#ReasonId').css("display","none");
                $('#ReasonOwner').removeAttr( 'style' );
            } else {
                $('#ReasonOwner').css("display","none");
                $('#ReasonId').removeAttr( 'style' );
            }

        });

        $(".glyphicon-search").click(function () {

            var id1=$('#id1_'+ArticleNumber).val();
            var RuleTypeId = $('#RuleTypeId').val();
alert(id1+RuleTypeId );
            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, RuleTypeId: RuleTypeId, ArticleCounter: ArticleNumber},
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
        $("#remove_btn").click(function () {
            var src = $('#searchs').val('');
        });
        ///mancata hide
        $("#NotificationType").on('change',function () {
            var id = $(this).val();
            if(id==2){
                $("#div_Reason").hide();
                $("#ReasonOwner").val('');

            }else{
                $("#div_Reason").show();
            }
        });
        /////article comune
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

                    if(index==1){
                        $('#ReasonId').val(response.ReasonId);
                    }

                    $('#EditArticle_'+index).show();
                    $('#Edit_Contestazione').show();
                    if(response.Result==0){

                        $('#span_Fee_'+index).html('');
                        $('#span_MaxFee_'+index).html('');

                    }
                    if(response.Result==1){

                        $('#id1_'+index).val(response.Id1);
                        $('#id2_'+index).val(response.Id2);
                        $('#id3_'+index).val(response.Id3);
                        $('#ArticleId_'+index).val(response.ArticleId);

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

                        checkFee();
                    }
                });
                request.fail(function (jqXHR, textStatus){
                    $('#span_Article_'+index).html("ERR: Prova con il diferente input");
                });
            }
        });

        $('.article_change').focusout(chkArticle);



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

            var CustomerAdditionalFee = $('#CustomerAdditionalFee').val();

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
            totFee = totFee + Number(CustomerAdditionalFee);
            $('#span_TotalFee').html(totFee.toFixed(2));


        });

        $("#ArticleSearch").click(function () {

            var id1=$('#id1_'+ArticleNumber).val();
            var RuleTypeId = $('#RuleTypeId').val();

            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, RuleTypeId: RuleTypeId},
                success: function (data) {

                    $('#Div_Article_Content').html(data.Article);
                    $('#BoxArticleSearch').fadeIn('slow');

                }
            });

        });



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
                                    console.log(result);
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


        $('#tab_Company').click(function () {
            $('#Genre').val('D');
            $("#sexM").prop("checked", false);
            $("#sexF").prop("checked", false);

            $('#TaxCode').val('');



        });

        $('#tab_Trespasser').click(function () {
            $('#Genre').val('M');

            $("#sexM").prop("checked", true);
            $("#sexF").prop("checked", false);
            $('#TaxCode').val('');
            $('#CompanyName').val('');


        });

        $('#sexM').click(function () {
            $('#Genre').val('M');
            $("#sexF").prop("checked", false);
        });
        $('#sexF').click(function () {
            $('#Genre').val('F');
            $("#sexM").prop("checked", false);
        });


        $("#BornCountry, #CountryId").change(function () {

            var str_FieldCityS = ($(this).attr("id")=="BornCountry") ? "BornCitySelect" : "CitySelect";
            var str_FieldCityI = ($(this).attr("id")=="BornCountry") ? "BornCityInput" : "CityInput";

            var Country = $(this).val();


            $("#ZIP").val('');
            $("#ZIP").removeClass('txt-success txt-warning txt-danger');

            if (Country == "Z000") {
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
                $("#"+str_FieldCityS).hide().children('option:not(:first)').remove();
                $("#"+str_FieldCityI).show();
            }
        });



        $("#CityInput, #BornCitySelect, #CitySelect, #Address").change(function () {

            var str_FieldNaneId = $(this).attr("id");

            if(str_FieldNaneId=="BornCitySelect") $('#BornCity').val($("#"+str_FieldNaneId+" option:selected" ).text());
            else {
                var CityId="";
                if(str_FieldNaneId=="CitySelect" || str_FieldNaneId=="Address"){
                    $('#City').val($("#"+str_FieldNaneId+" option:selected" ).text());
                    CityId =  $('#CitySelect').val();
                } else if (str_FieldNaneId=="CityInput" || str_FieldNaneId=="Address") {
                    CityId =  $('#CityInput').val();
                }

                var Address = $('#Address').val();
                var CountryId = $('#CountryId').val();

                if(CityId!=""){

                    $.ajax({
                        url: 'ajax/ajx_src_zip.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {CountryId:CountryId, CityId: CityId, Address:Address},
                        success: function (data) {
                            $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#ZIP").val(data.ZIP);
                            $("#Province").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#Province").val(data.province);
                        }
                    });

                } else $("#ZIP").val('');
            }




        });

        function checkPiva(taxcode){
            if(taxcode==''){
                $('#TaxCode').removeClass('txt-success txt-danger txt-warning').addClass('txt-warning');
                $('#btn_saveanagrafica').prop('disabled',false);
                $("#erroriva").text(' ');
            } else {
                $.ajax({
                    url: 'ajax/checkpiva.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {TaxCode:taxcode},
                    success: function (data) {
                        if (data == 'Exists') {
                            $("#erroriva").text('Il C.F./P.IVA esiste gia nella database');
                            $('#TaxCode').removeClass('txt-success txt-danger txt-warning').addClass('txt-danger');
                            $('#btn_saveanagrafica').prop('disabled',true);
                        } else {
                            $("#erroriva").text(' ');
                            $('#TaxCode').removeClass('txt-success txt-danger txt-warning').addClass('txt-success');
                            $('#btn_saveanagrafica').prop('disabled',false);
                        }
                    }
                });
            }
        }

        $("#TaxCode").change(function(){
            var taxcode = $("#TaxCode").val();
            checkPiva(taxcode);
        });

        //licensedate
        $("#LicenseDate").on("change",function(){
            var tresId = $("#TrespasserTypeId").val();
            if(tresId == 10){
                $("#LicenseDatePropretario").val(this.value)
            }else if(tresId == 11){
                $("#LicenseDateTrasgressore").val(this.value)
            }
        });

        $("#BornCountry, #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #CountryId").change(function(){

            if ($('#CountryId').val() == 'Z000') {
                var Surname = $('#Surname').val();
                var Name = $('#Name').val();
                var Sex = $('#sexM').prop('checked') ? 'M' : 'F';

                var BornDate = $('#BornDate').val();
                var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();

                if (Surname && Name && Sex && BornDate && BornCitySelect) {
                    var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);

                    if (TaxCode.length == 16){
                        $('#TaxCode').val(TaxCode);
                        checkPiva(TaxCode);
                    } else {
                        $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                    }

                } else {
                    $('#TaxCode').val("")
                }

            } else {
                $('#TaxCode').val("")
            }
        });



        $('#f_ins_trespasser').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

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
            // alert(AccertatoreNumber);

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
                if (FirstCode.indexOf('/') > 0)
                {
                    FirstCode = FirstCode.replace("/","");
                }
                var validCode = true;
                var FineTypeId = $('#FineTypeId').val();

                if (validCode) {
                    $.ajax({
                        url: 'ajax/search_code.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {Code: SendCode,FineTypeId:FineTypeId},
                        success: function (data) {
                            if (data.Result == "NO") {
                                $("#save").prop("disabled", true);
                                $("#span_code").addClass("help-block");
                                $("#span_code").html('Già presente!');
                                chkCode = false;
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
<?php
include(INC."/footer.php");
/*






*/