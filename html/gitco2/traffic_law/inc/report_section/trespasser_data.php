<?php
$str_Trespasser_Data = "";
$str_Trespasser_Data .='
   				<div id="DIV_TrespasserTypeSelect" class="col-sm-12">
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Trasgressore
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        <select class="form-control frm_field_required" name="TrespasserType" id="TrespasserType">';
                            if ($isPageUpdate) $str_Trespasser_Data .= '<option value="0">Invariato</option>';
                            $str_Trespasser_Data .='
                            <option value="1">Proprietario (Proprietario/Trasgressore)</option>
                            <option value="2">Obbligato / Trasgressore (Proprietario + Trasgressore/conducente)</option>
                            <option value="3">Noleggio / Noleggiante(Obbligato/Trasgressore)</option>
                            <option value="4">Noleggio / Noleggiante(Obbligato) / Conducente (Trasgressore)</option>
                        </select>

                    </div>
   				</div>
                <div class="clean_row HSpace4"></div>

                <div id="DIV_TrespasserChoose">
      				<div id="driver" style="display:none">		
                        <div>
                            <div class="col-sm-12">
                                <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                                    <span id="trasgressor3">CONDUCENTE:</span> <span id="span_name_12"></span> <span id="error_name_12"></span>   
                                    <input type="hidden" id="conducente">    				
                                </div>
                                <div class="col-sm-2 BoxRowLabel">
                                    Data notifica
                                </div>
                                <div class="col-sm-2 BoxRowCaption">
                                    <input type="text" class="form-control frm_field_date" name="NotificationDate_12" id="NotificationDate_12">
                                </div>
                                <div class="col-sm-2 BoxRowCaption">
                                    <select name="NotificationType_12" class="form-control" id="NotificationType_12">
                                        <option></option>
                                        <option value="1">Su strada</option>
                                        <option value="2">Messo</option>
                                        <option value="3">Ufficio</option>
                                    </select>
                                    <span id="notification_12"></span>
                                </div>
                            </div>
                        </div>
    
                        <div class="clean_row HSpace4"></div> 
                        <div id="DIV_Message_12" class="alert alert-warning" style="display:none;margin:0;align-items:center;padding: 1rem;">
                            <i class="fa fa-warning col-sm-1" style="text-align:center;"></i>
                            <ul class="col-sm-11" style="list-style-position:inside;"></ul>
                        </div>	
                        <div class="col-sm-12" style="height:20rem;">
                            <ul class="nav nav-tabs" id="mioTab">
                                <li class="active" id="tab_Trespasser12"><a href="#Trespasser12" data-toggle="tab">PERSONA FISICA</a></li>
                                <li id="tab_company12"><a href="#company12" data-toggle="tab">DITTA</a></li>
                                
                            </ul>
                            <span class="glyphicon glyphicon-plus-sign add_button_12" style="color:#294A9C;position:absolute; right:10px;top:10px;font-size:25px; "></span> 
                            <div class="tab-content">
                                <div class="tab-pane" id="company12">
                                    <div class="row-fluid">
                                        <div class="col-sm-12">
                                            <div class="col-sm-2 BoxRowLabel">
                                                <input type="hidden" name="Genre12" id="Genre12" value="D">
                                                Ragione sociale
                                            </div>
                                            <div class="col-sm-10 BoxRowCaption">
                                                <input name="CompanyName12" id="CompanyName12" type="text">
                                            </div>
                                           
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane active" id="Trespasser12">
                                    <div class="row-fluid">
                                        <div class="col-sm-12">
                                            <div class="col-sm-3 BoxRowLabel">
                                                Cognome
                                            </div>
                                            <div class="col-sm-3 BoxRowCaption">
                                                <input class="form-control frm_field_string" type="text" name="Surname12" id="Surname12">
                                            </div>
                                            <div class="col-sm-3 BoxRowLabel">
                                                Nome
                                            </div>
                                            <div class="col-sm-3 BoxRowCaption">
                                                <input class="form-control frm_field_string" type="text" name="Name12" id="Name12">
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div> 
                                <div id="trespasser_content_12" class="col-sm-12" style="height:150px;overflow:auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>   
                    <div id="DIV_Tutor_12" style="display:none">
                        <div class="col-sm-12">
                            <div class="col-sm-5 BoxRowLabel" style="background-color:#294A9C;">
                                ESERCENTE PATRIA POTESTA: <span id="span_name_17"></span>   
                                <span id="error_name_17"></span>                    
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data notifica
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="NotificationDate_17" id="NotificationDate_17">
                            </div>    
                            <div class="col-sm-2 BoxRowCaption">
                                <select name="NotificationType_17" class="form-control" id="NotificationType_17">
                                    <option></option>
                                    <option value="1">Su strada</option>
                                    <option value="2">Messo</option>
                                    <option value="3">Ufficio</option>
                                </select>
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <span class="glyphicon glyphicon-plus-sign add_button_17" style="color:#294A9C;position:absolute; right:10px;top:3px;font-size:17px; "></span> 
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
                                        <input class="form-control frm_field_string" type="text" name="Surname17" id="Surname17">
                                    </div>
                                
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input class="form-control frm_field_string" type="text" name="Name17" id="Name17">
                                    </div>
                                   
                                    
                                </div>
                                     
                            </div>
                                               
                            <div id="trespasser_content_17" class="col-sm-12" style="height:150px;overflow:auto"></div>
                        </div>                                  
                    </div>

                    <div id="DIV_TrespasserType" style="display:none">
                    <div class="col-sm-12">
                        <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                            <span id="trasgressor1">TRASGRESSORE:</span> <span id="span_name_11"></span><span id="error_name_11"></span>                   
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input type="text" class="form-control frm_field_date" name="NotificationDate_11" id="NotificationDate_11">
                        </div>  
                        <div class="col-sm-2 BoxRowCaption">
                            <select name="NotificationType_11" class="form-control" id="NotificationType_11">
                                <option></option>
                                <option value="1">Su strada</option>
                                <option value="2">Messo</option>
                                <option value="3">Ufficio</option>
                            </select>
                            <span id="notification_11"></span>
                        </div>
                        <div id="TrespReceiveDate" style="display:none;">
                            <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">  				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data comunica
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date frm_field_required" name="ReceiveDate" id="ReceiveDate">
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                            </div>
                        </div>
                        <div id="TrespOwnerAdditionalFee">
                            <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">  				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Costi aggiuntivi
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_numeric" name="OwnerAdditionalFee" id="OwnerAdditionalFee">
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                            </div>
                        </div>
                    </div>
    
    
                    <div class="clean_row HSpace4"></div>
                    <div id="DIV_Message_11" class="alert alert-warning" style="display:none;margin:0;align-items:center;padding: 1rem;">
                        <i class="fa fa-warning col-sm-1" style="text-align:center;"></i>
                        <ul class="col-sm-11" style="list-style-position:inside;"></ul>
                    </div> 
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
                                            <input name="CompanyName11" id="CompanyName11" type="text">
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
                                            <input class="form-control frm_field_string" type="text" name="Surname11" id="Surname11">
                                        </div>
                                        <div class="col-sm-3 BoxRowLabel">
                                            Nome
                                        </div>
                                        <div class="col-sm-3 BoxRowCaption">
                                            <input class="form-control frm_field_string" type="text" name="Name11" id="Name11">
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
                            <div class="col-sm-5 BoxRowLabel" style="background-color:#294A9C;">
                                ESERCENTE PATRIA POTESTA: <span id="span_name_16"></span>   
                                <span id="error_name_16"></span>                    
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data notifica
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="NotificationDate_16" id="NotificationDate_16">
                            </div>    
                            <div class="col-sm-2 BoxRowCaption">
                                <select name="NotificationType_16" class="form-control" id="NotificationType_16">
                                    <option></option>
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
                                        <input class="form-control frm_field_string" type="text" name="Surname16" id="Surname16">
                                    </div>
                                
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input class="form-control frm_field_string" type="text" name="Name16" id="Name16">
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
                                <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                                    <span id="trasgressor2">PROPRIETARIO:</span> <span id="span_name_10"></span> <span id="error_name_10"></span>   
                                    <input type="hidden" id="propretario">    				
                                </div>
                                <div class="col-sm-2 BoxRowLabel">
                                    Data notifica
                                </div>
                                <div class="col-sm-2 BoxRowCaption">
                                    <input type="text" class="form-control frm_field_date" name="NotificationDate_10" id="NotificationDate_10">
                                </div>
                                <div class="col-sm-2 BoxRowCaption">
                                    <select name="NotificationType_10" class="form-control" id="NotificationType_10">
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
                        <div id="DIV_Message_10" class="alert alert-warning" style="display:none;margin:0;align-items:center;padding: 1rem;">
                            <i class="fa fa-warning col-sm-1" style="text-align:center;"></i>
                            <ul class="col-sm-11" style="list-style-position:inside;"></ul>
                        </div>
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
                                                <input name="CompanyName10" id="CompanyName10" type="text">
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
                                                <input class="form-control frm_field_string" type="text" name="Surname10" id="Surname10">
                                            </div>
                                            <div class="col-sm-3 BoxRowLabel">
                                                Nome
                                            </div>
                                            <div class="col-sm-3 BoxRowCaption">
                                                <input class="form-control frm_field_string" type="text" name="Name10" id="Name10">
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
                            <div class="col-sm-5 BoxRowLabel" style="background-color:#294A9C;">
                                ESERCENTE PATRIA POTESTA: <span id="span_name_15"></span>
                                <span id="error_name_15"></span>      				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data notifica
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="NotificationDate_15" id="NotificationDate_15">
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <select name="NotificationType_15" class="form-control" id="NotificationType_15">
                                    <option></option>
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
                                        <input class="form-control frm_field_string" type="text" name="Surname15" id="Surname15">
                                    </div>
                                
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input class="form-control frm_field_string" type="text" name="Name15" id="Name15">
                                    </div>
                                    
                                    
                                </div>
                                     
                            </div>
                                               
                            <div id="trespasser_content_15" class="col-sm-12" style="height:150px;overflow:auto"></div>
                        </div>  				  				
      				</div>
                </div>
';

?>

<script>
    $('document').ready(function () {
        $('#NotificationType').change(function () {
            if($(this).val()==2){
                $('#ReasonId').removeClass('frm_field_required');
                $('#ReasonOwner').removeClass('frm_field_required');
                $('#ReasonId').append("<option></option>");
            } else {
                $('#ReasonId').addClass('frm_field_required');
                $('#ReasonOwner').addClass('frm_field_required');
            }
        });
        $('#ReasonCode').keyup(function (e) {
            const code = $(this).val();
            var isFound = false;
            if(code==''){
                $("#ReasonId").val($("#ReasonId option:first").val());
                return;
            }

            $("#ReasonId > option").each(function() {
                if ($(this).html().indexOf(code)>=0) {
                    $('#ReasonId').val($(this).val());
                    isFound = true;
                }
            });
            if(!isFound){
                $("#ReasonId").val($("#ReasonId option:first").val());
            }
        });
    });
</script>
