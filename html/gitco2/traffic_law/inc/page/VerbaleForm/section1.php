  <?php
  $inserted_vehicle = $nr>0?$vehicle:null;
  $inserted_vehicle_id = $nr>0?$vehicle_id:null;
  $inserted_adress = $nr>0?$adress:null;
  $inserted_fine_date = $nr>0?$fine_date:null;
  if ($_form_type_id ==1){
      $class = null;
  }else{
      $class = 'frm_field_required';
  }
    $code = "";
  if (isset($fineId) && $fineId > 0){
      $getCode = $rs->SelectQuery("SELECT Code FROM Fine WHERE Id = ".$fineId."");
      $DefaultCode = mysqli_fetch_array($getCode)['Code'];
      if ($DefaultCode != '/'.$_SESSION['year']) {
          $explodeCode = explode("/", $DefaultCode);
          if (count($explodeCode) == 2) {
              $code = $explodeCode[0];
              if (is_numeric($code)) {
                  $code = (int)$code + 1;
              } else {
                  $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $code);
                  $prefix = $parts[0];
                  $num = (int)$parts[1] + 1;
                  $code = $prefix . $num;
              }
          } elseif (count($explodeCode) == 3) {
              if (is_numeric($explodeCode[0])) {
                  $code = (int)$explodeCode[0] . "/" . (int)$explodeCode[1] + 1;
              } else {
                  $prefix = $explodeCode[0];
                  $num = (int)$explodeCode[1] + 1;
                  $code = $prefix . "/" . $num;
              }
          }
      }
  }

  if (isset($_GET['P']) && $_GET['P']=='mgmt_warning.php'){
      $notifica = '<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Genere
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '. CreateSelect("RuleType","CityId='".$_SESSION['cityid']."'","Id","RuleTypeId","Id","Title","",true,10, "frm_field_required") .'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Riferimento
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input name="Code" id="Code" type="text" class="form-control frm_field_string '.$class.'" style="width:15rem" placeholder="'.$code.'">
                            <span id="span_code"></span>
                        </div>
                        <div class="col-sm-3 BoxRowLabel" style="border-right:2px solid white;">
                          '."/".$_SESSION['year'].'
                        </div>
                    </div>';
  }elseif (isset($_GET['P']) && $_GET['P']=='mgmt_violation.php' || $_GET['P']=='mgmt_violationrent.php'){
      $notifica = ' <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Genere
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '. CreateSelect("RuleType","CityId='".$_SESSION['cityid']."'","Id","RuleTypeId","Id","Title","",true,10, "frm_field_required") .'
                        </div>
                        
                        <div class="col-sm-2 BoxRowLabel">
                            Riferimento
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input name="Code" id="Code" type="text" class="form-control frm_field_string '.$class.'" style="width:15rem"  placeholder="'.$code.'">
                            <span id="span_code"></span>
                        </div>
                        <div class="col-sm-1 BoxRowLabel" style="border-right:2px solid white;">
                          '."/".$_SESSION['year'].'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                           Eludi Controlli
                        </div>
                        <div class="col-sm-1 BoxRowCaption" >
                            <input type="checkbox" id="Conrolli" name="Controlli">
                        </div>
                    </div>';
  }else{
      $notifica = '<div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Genere
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelect("RuleType","CityId='".$_SESSION['cityid']."'","Id","RuleTypeId","Id","Title","",true,10, "frm_field_required") .'
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Notifica
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select class="form-control frm_field_required" name="NotificationType" id="NotificationType" style="width:10rem;">
                            <option></option>
                            <option value="2">Su strada</option>
                            <option value="1">Differita</option>
                        </select>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Riferimento
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="Code" id="Code" type="text" class="form-control frm_field_string '.$class.'" style="width:15rem;"  placeholder="'.$code.'">
                        <span id="span_code"></span>
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="border-right:2px solid white;">
                      '."/".$_SESSION['year'].'
                    </div>
                </div>';
  }
  $str_out .=  '
        	    <div class="clean_row HSpace4"></div>
        	            	
        	    '.$notifica.'
                <div class="clean_row HSpace4"></div>

        	    <div class="col-sm-12">

                    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date fine_date" value="'. DateOutDB($inserted_fine_date).'"  name="FineDate" id="FineDate" style="width:12rem; border: solid 3px #c49916;">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time frm_field_required" name="FineTime" id="FineTime" style="width:8rem">
                         <span id="span_time"></span>
                    </div>
                    '.$str_Locality.'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
					'. $str_Toponym .'
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-9 BoxRowCaption">
        				<input type="text" class="find_list form-control frm_field_string frm_field_required" value="'.$inserted_adress.'" name="Address" id="Address" style="width:40rem;">	
        				<ul id="Address_List" class="ul_SearchList"></ul>
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-3 BoxRowCaption">
					<span id="span_tipo"></span>
					<select class="form-control frm_field_string" id="VehicleTypeId" name="VehicleTypeId">';

                            $results = $rs->SelectQuery("SELECT * FROM VehicleType");
                            foreach ($results as $row_selected ){
                                $id = $row_selected['Id'];
                                $value = $row_selected['Title'.LAN];
                                if ($id == $inserted_vehicle_id){
                                    $str_out .= "<option  value=\"$inserted_vehicle_id\" selected>$inserted_vehicle</option>";
                                }else{
                                    $str_out .= "<option  value=\"$id\">$value</option>";
                                }


                            }

    $str_out .= '</select>  
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-3 BoxRowCaption">
					<input type="hidden" id="VehicleCountry" name="VehicleCountry" value="Italia"/>
                        '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title","Z000",false,15,"frm_field_required") .'
					</div>
					<div id="department" hidden>
                        <div class="col-sm-1 BoxRowLabel">
                            Dip.
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <select name="DepartmentId" id="DepartmentId" class="form-control"></select>
                        </div>
                    </div>
                    <div id="toHide" class="col-sm-2 BoxRowCaption">
                    </div>
  				</div>   
  				<div class="clean_row HSpace4"></div>
  	        	<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Targa
        				<i class="fa fa-share" style="position:absolute; right:0.1rem;font-size:1.7rem;top:0.2rem;"></i>
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				<input type="text" class="form-control frm_field_string frm_field_required" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase">
					</div>
					<div id="massa">
                        <div class="col-sm-3 BoxRowLabel">
                            Massa
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input type="text" class="form-control frm_field_numeric" name="VehicleMass" id="VehicleMass" style="width:8rem">	
                        </div>
                    </div>
                    <div id="toHide2" hidden class="col-sm-6 BoxRowCaption">
                    </div>

  				</div>

  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Colore
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string" name="VehicleColor" id="VehicleColor" class="find_list" style="width:8rem">	
        				<ul id="VehicleColor_List" class="ul_SearchList"></ul>
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    <input type="text" class="form-control frm_field_string" name="VehicleBrand" id="VehicleBrand" class="find_list" style="width:8rem">	
        				<ul id="VehicleBrand_List" class="ul_SearchList"></ul>
					</div>

        			<div class="col-sm-2 BoxRowLabel">
        				Modello
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    <input type="text" class="form-control frm_field_string" name="VehicleModel" id="VehicleModel" class="find_list" style="width:8rem">	
        				<ul id="VehicleModel_List" class="ul_SearchList"></ul>
					</div>

  				</div>
  				<div class="col-sm-12" id="div_chkPlate">
  					<div id="trespasser_content" class="col-sm-12" style="display: none;"></div>
				    <div id="fine_content" class="col-sm-12" style="display: none;"></div>
                </div>

  				<div class="clean_row HSpace4"></div>				
 				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Rilevatore
					</div>
					<div class="col-sm-5 BoxRowCaption">
        				'. CreateSelect("Detector","CityId='".$_SESSION['cityid']."'","Title".LAN,"DetectorId","Id","Title".LAN,"",false,20) .'
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Ora
                        <span class="tooltip-r" data-toggle="tooltip" data-placement="top" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo. L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora. "><i class="glyphicon glyphicon-info-sign" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;" ></i></span>
       				</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. CreateSelect("TimeType","Disabled=0","Id","TimeTypeId","Id","Title","",true) .'
					</div>					
  				</div>
  				<div class="clean_row HSpace4"></div>
';

 				$str_out .='
  	            <div class="col-sm-12" id="DIV_Title_Speed" style="display:none;">
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				VELOCITA
					</div>
  				</div> 
	        	<div class="col-sm-12" id="DIV_Speed" style="display:none;">
        			<div class="col-sm-2 BoxRowLabel">
        				Limite
					</div>
					<div class="col-sm-2 BoxRowCaption" id="">
					    <input class="form-control frm_field_numeric" type="text" name="SpeedLimit" id="SpeedLimit" style="width:6rem" readonly>    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Rilevata
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" name="SpeedControl" id="SpeedControl" style="width:6rem" readonly>
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Effettiva
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input type="hidden" name="Speed" id="Speed" style="width:6rem">
        				<span id="span_Speed" style="font-size:1.1rem;">&nbsp;</span>
 					</div>
  				</div>  

  	            <div class="col-sm-12" id="DIV_Title_TLight" style="display:none;">
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				SEMAFORO
					</div>
  				</div>  
	        	<div class="col-sm-12" id="DIV_TLight" style="display:none;">
        			<div class="col-sm-4 BoxRowLabel">
        				Primo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption" id="">
					    <input class="form-control frm_field_numeric" type="text" name="TimeTLightFirst" id="TimeTLightFirst" style="width:6rem">    				
 					</div>
 					<div class="col-sm-4 BoxRowLabel">
        				Secondo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" name="TimeTLightSecond" id="TimeTLightSecond" style="width:6rem">
 					</div>
  				</div> 
                <div class="clean_row HSpace4"></div>
                '. $str_ArticleBox .'
				';