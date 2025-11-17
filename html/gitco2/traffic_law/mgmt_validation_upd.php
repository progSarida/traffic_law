<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$Id = CheckValue('Id', 'n');

$page = CheckValue('page', 'n');
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

$str_Where = "StatusTypeId=0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];


$strOrder = "Id DESC";



$str_Next = "";
$str_Previous = "";
$str_Table = "";

$rs_Toponym = $rs->SelectQuery("SELECT * from sarida.ZIPCity WHERE CityId='".$_SESSION['cityid']."'");
$n_Toponym = mysqli_num_rows($rs_Toponym);

if($n_Toponym>0){
    $str_Toponym='
        <div class="col-sm-1 BoxRowLabel">
            Topon.
        </div>
        <div class="col-sm-3 BoxRowCaption">
            <select name="Toponym" id="Toponym" class="form-control" style="width:20rem;">
                <option></option>';
                    while($r_Toponym = mysqli_fetch_array($rs_Toponym)){
                        $str_Toponym .= '<option value="'.$r_Toponym['StreetName'].'">'.$r_Toponym['StreetName'].'</option>';
                    }
            $str_Toponym .='</select>
        </div>
    ';
}else{
    $str_Toponym='
        <div class="col-sm-4 BoxRowLabel"></div>
    ';
}

$str_Table = 'V_Validation';
$table_rows = $rs->Select('V_Validation',$str_Where. " AND Id=".$Id);
$table_row = mysqli_fetch_array($table_rows);

$rs_Id = $rs->SelectQuery("SELECT MIN(Id) NextId FROM ".$str_Table." WHERE Id > $Id AND $str_Where");
$r_Id = mysqli_fetch_array($rs_Id);
$nextid = '<input type="hidden" name="nextId" id="nextId" value="'.$r_Id['NextId'].'">';
if (!is_null($r_Id['NextId'])) {
    $str_Next = '<a href="' . $str_GET_FilterPage . '&Id=' . $r_Id['NextId'] . '"><i class="glyphicon glyphicon-arrow-right" style="font-size:2rem;color:#fff"></i></a>';
}

$rs_Id = $rs->SelectQuery("SELECT MAX(Id) PreviousId FROM ".$str_Table." WHERE Id < $Id AND $str_Where");

$r_Id = mysqli_fetch_array($rs_Id);
$nextid .= '<input type="hidden" name="PreviousId" id="PreviousId" value="'.$r_Id['PreviousId'].'">';
if (!is_null($r_Id['PreviousId'])) {
    $str_Previous = '<a href="' . $str_GET_FilterPage . '&Id=' . $r_Id['PreviousId'] . '"><i class="glyphicon glyphicon-arrow-left" style="font-size:2rem;color:#fff"></i></a>';
}

echo "<div class='check'></div>";
include './inc/page/VerbaleForm/Accertatore_upd.php';


if ($table_row['DetectorId'] == 0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector', "Id=" . $table_row['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title' . LAN];

}


$rs_Row = $rs->Select(MAIN_DB . '.City', "UnionId='" . $_SESSION['cityid'] . "'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

if ($n_Code > 0) {
    $str_Locality='
                <div class="col-sm-1 BoxRowLabel">
                    Comune
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <select name="Locality" class="form-control" style="width:10rem;">
                        <option></option>';
                            while ($r_Row = mysqli_fetch_array($rs_Row)) {
                                $str_Locality .= '<option value="' . $r_Row['Id'] . '"';
                                if ($table_row['Locality'] == $r_Row['Id']) $str_Locality .= ' SELECTED ';

                                $str_Locality .= '>' . $r_Row['Title'] . '</option>';

                            }

    $str_Locality .= '</select></div>';

} else {
$str_Locality='<div class="col-sm-1 BoxRowLabel">
                        Comune
                </div>
                <div class="col-sm-3 BoxRowCaption">
                ' . $_SESSION['citytitle'] . '
                </div>
    ';
}

$str_Folder = ($table_row['CountryId'] == 'Z000') ? 'doc/national/violation' : 'doc/foreign/violation';

$str_tree = "";
$str_Img = "";
$doc_rows = $rs->Select('FineDocumentation', "FineId=" . $Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);

if ($doc_n > 0) {
    $doc_row = mysqli_fetch_array($doc_rows);

    $File = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $table_row['Id'] . '/' . $doc_row['Documentation'];
    if (strtolower(substr($doc_row['Documentation'], -3)) == "jpg") {
        $str_Img = ' 
            $("#preview").attr("src","' . $File . '");
            $("#preview_img").show();
        ';
    } else {
        //$str_Img = '$("#preview_img").html("<iframe style=\"width:100%; height:100%\" src=\"' . $File . '\"></iframe>");';
        $str_Img = '
            $("#preview_doc").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");
            $("#preview_doc").show();
            ';


    }

    $str_tree = '
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $table_row['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
            
            var FileType = file.substr(file.length - 3);

            if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\'){
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");
                $("#preview_doc").show();
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }            
        });
    ';

}

$str_out .= '
        <form name="f_violation" id="f_violation" class="thisform" method="post" action="mgmt_validation_exe.php'.$str_GET_Parameter.'">
        <input type="hidden" name="Id" value="' . $Id . '">
        <input type="hidden" name="P" value="' . $str_BackPage . '">
        <input type="hidden" name="page" value="' . $page . '">
        <input type="hidden" name="Search_Plate" value="' . $Search_Plate . '">        
        <input type="hidden" name="Search_Country" value="' . $Search_Country . '">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="'.$nr.'">
       '.$nextid.'
    	<div class="row-fluid">
        	<div class="col-sm-6">
        	    
  	            <div class="col-sm-12" >
  	              	<div class="col-sm-1 BoxRowCaption">
                        ' . $str_Previous . '
                    </div>	
                    <div class="col-sm-10 BoxRowCaption"></div>
                    <div class="col-sm-1 BoxRowCaption">
                        ' . $str_Next . '
                    </div>				
									
  				</div> 
  				<div class="clean_row HSpace4"></div>' .
    '
  				<div class="col-sm-12">
  				    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date frm_field_required" name="FineDate" id="FineDate" value="' . DateOutDB($table_row['FineDate']) . '" style="width:9rem">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time frm_field_required" name="FineTime" id="FineTime" value="' .TimeOutDB($table_row['FineTime']) . '" style="width:8rem">
                          <span id="span_time"></span>
                    </div>	
                    <div class="col-sm-6 BoxRowCaption"></div>
                </div>
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    '.$str_Locality.'
                    '. $str_Toponym .'
        			<div class="col-sm-1 BoxRowLabel">
        				Località
					</div>
					
					<div class="col-sm-3 BoxRowCaption">
        				<input type="text" class="find_list form-control frm_field_string frm_field_required" name="FineAddress" id="Address" value="' . StringOutDB($table_row['Address']) . '" style="width:22rem">	
        				<ul id="Address_List" class="ul_SearchList"></ul>
					</div>
				
  				</div>                

  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. CreateSelect("VehicleType","Disabled=0","Title".LAN,"VehicleTypeId","Id","Title".LAN,$table_row['VehicleTypeId'],true,14) .'
					</div>
        			<div class="col-sm-3 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-3 BoxRowCaption">
					<input type="hidden" id="VehicleCountry" name="VehicleCountry" value="'.$table_row['VehicleCountry'].'"/>
                        '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title", $table_row['CountryId'],false,15,"frm_field_required") .'
					</div>
        		</div>
					
  				<div class="clean_row HSpace4"></div>
  	        	
  				
  				<div class="col-sm-12" id="div_chkPlate">
  					<div id="trespasser_content" class="col-sm-12" style="display: none;"></div>
				    <div id="fine_content" class="col-sm-12" style="display: none;"></div>
                </div> 				
  				
 				<div class="col-sm-12">
 				    <div class="col-sm-3 BoxRowLabel">
        				Targa
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				<input type="text" class="form-control frm_field_string frm_field_required" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase" value="' . StringOutDB($table_row['VehiclePlate']) . '">
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Rilevatore
					</div>
					<div class="col-sm-4 BoxRowCaption">
        
        					'. CreateSelect("Detector","CityId='".$_SESSION['cityid']."'","Title".LAN,"DetectorId","Id","Title".LAN,$table_row['DetectorId'],false,20) .'
					</div>
        							
  				</div>
                    
                <div class="clean_row HSpace4"></div>	

                <div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Mancata contestazione
					</div>
                    <div class="col-sm-9 BoxRowCaption">
                        '. CreateSelectQuery("SELECT R.Id,CONCAT(R.Progressive,' - ',R.TitleIta) AS Title FROM Reason R JOIN ViolationType V on R.ViolationTypeId = V.Id WHERE R.CityId='{$_SESSION['cityid']}' AND V.RuleTypeId={$_SESSION['ruletypeid']} AND R.Disabled=0 ORDER BY R.Id","ReasonId","Id","Title",$table_row['ReasonId'],false,'', "frm_field_required") .'
                    </div>
                </div>

  				<div class="clean_row HSpace4"></div>				
                 ';

        if ($table_row['Speed'] > 0) {
            $str_out .= '
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                        VELOCITA
                    </div>
                </div> 
                <div class="col-sm-12" id="DIV_Speed">
                    <div class="col-sm-2 BoxRowLabel">
                        Limite
                    </div>
                    <div class="col-sm-2 BoxRowCaption" id="">
                          
                        <input class="form-control frm_field_numeric" type="text" name="SpeedLimit" id="SpeedLimit" value="' . round($table_row['SpeedLimit']) . '" style="width:6rem" >    				
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Rilevata
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        
                        <input class="form-control frm_field_numeric" type="text" name="SpeedControl" id="SpeedControl" value="' . round($table_row['SpeedControl']) . '" style="width:6rem" >
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Effettiva
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        ' . round($table_row['Speed']) . '
                    </div>
                </div> 
                <div class="clean_row HSpace4"></div>    
            ';
        }else{
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
					    <input class="form-control frm_field_numeric" type="text" name="SpeedLimit" id="SpeedLimit" style="width:6rem" >    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Rilevata
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" name="SpeedControl" id="SpeedControl" style="width:6rem" >
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Effettiva
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input type="hidden" name="Speed" id="Speed" style="width:6rem">
        				<span id="span_Speed" style="font-size:1.1rem;">&nbsp;</span>
 					</div>
  				</div> ';
        }

        if ($table_row['TimeTLightFirst'] > 0) {
            $first = $table_row['TimeTLightFirst'] > 0 ? $table_row['TimeTLightFirst']:"";
            $second = $table_row['TimeTLightSecond']> 0? $table_row['TimeTLightSecond']:"";
            $str_out .= '
                 <div class="col-sm-12" id="DIV_TLight">
                       <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                           SEMAFORO
                       </div>
                       <div class="col-sm-12">
                            <div class="col-sm-4 BoxRowLabel">
                                Primo fotogramma
                            </div>
                            <div class="col-sm-2 BoxRowCaption" id="">
                                <input class="form-control frm_field_numeric" type="text" name="TimeTLightFirst" id="TimeTLightFirst" value="' .$first . '" style="width:6rem">    				
                            </div>
                            <div class="col-sm-4 BoxRowLabel">
                                Secondo fotogramma
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="form-control frm_field_numeric" type="text" name="TimeTLightSecond" id="TimeTLightSecond" value="' .$second . '" style="width:6rem">
                            </div>
                       </div>
                 </div>
                 <div class="clean_row HSpace4"></div>
               ';
        }else{
            $str_out.= '
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
				';
        }

$str_out.='<div class="col-sm-12">
        			
					<div class="clean_row HSpace4"></div>
					'.$strAcertatore.'
  				</div> 
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
                        <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                          <button type="submit" class="btn btn-success" name="conferma" id="conferma"><i class="fa fa-check"></i> Conferma</button>
                          <button type="button" class="btn btn-danger cancella" id="'.$Id.'"><i class="fa fa-trash"></i> Elimina</button>
                          <button type="button" class="btn btn-default" id="back">Esci</button>
                        </div>
                    </div>
                </div>
	        	
        	</div>
        	
        	<div class="col-sm-6">
        	    <div class="col-sm-12" >
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				DOCUMENTAZIONE
					</div>
  				</div> 
                <div class="col-sm-12 BoxRow" style="width:100%;height:10rem;">
                    <div class="example">
                        <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                    <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"  />
                    </div>
                    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>                
                </div>		
  			</div>
  		</div>	
        
    </form>
    </div>';


echo $str_out;

?>
<script type="text/javascript">
        var controller = true;
        $('#back').click(function(){
            window.location="<?= 'mgmt_validation.php'.$str_GET_Parameter ?>"
        });
        $('#Address').focusout(function(){
            setTimeout(function () {
                $('#Address_List').hide();
            }, 150);
        });
        $('#Address').focusin(function(){
            $('#Address_List').show();
        });
        $(document).ready(function () {
        
            $('#CountryId').on('change', function(){
            	var id = $("#CountryId").val();
            	$('#VehicleCountry').val($( "#CountryId option:selected" ).text());
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
                                $('#DIV_Title_TLight').hide();
                                $('#DIV_TLight').hide();
                                $("#TimeTLightFirst").val('');
                                $("#TimeTLightSecond").val('');
                            }else if(data.strSpeed == 2){
                                $('#DIV_Title_TLight').show();
                                $('#DIV_TLight').show();
                                $('#DIV_Title_Speed').hide();
                                $('#DIV_Speed').hide();
                                $("#SpeedLimit").prop("readonly", true);
                                $("#SpeedControl").prop("readonly", true);
                                $("#SpeedLimit").val('');
                                $("#SpeedControl").val('');
                            } else {
                                $('#DIV_Title_Speed').hide();
                                $('#DIV_Speed').hide();
                                $('#DIV_Title_TLight').hide();
                                $('#DIV_TLight').hide();
                                $("#TimeTLightFirst").val('');
                                $("#TimeTLightSecond").val('');
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

                    frm_field_numeric: {
                        selector: '.frm_field_numeric',
                        validators: {
                            numeric: {
                                message: 'Numero'
                            }
                        }
                    },

                    frm_field_date: {
                        selector: '.frm_field_date',
                        validators: {
                            date: {
                                format: 'DD/MM/YYYY',
                                message: 'Data non valida'
                            }
                        }
                    },


                    FineDate:{
                        validators: {
                            notEmpty: {message: 'Richiesto'},

                            date: {
                                format: 'DD/MM/YYYY',
                                message: 'Data non valida'
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
                var targa = $('#VehiclePlate').val();
                if (today_date == "" || ora == ""  || targa == "") {
                    e.preventDefault();
                }else {
                    document.f_violation.submit();
                }
            });

            $(".cancella").on('click',function () {
                if (!confirm("Sei sicuro di voler procedere con la cancellazione?")){
                    return false;
                }
                var deleted_id = $(this).attr('id');
                var nextId = $("#nextId").val();
                var previousId = $("#PreviousId").val();
                var myData = {"deleted_id":deleted_id};
                $.ajax({
                    url: 'mgmt_validation_exe.php',
                    type: 'POST',
                    data: myData,
                    success: function(data){
                        if (nextId!=""){
                            window.location ='mgmt_validation_upd.php<?=$str_GET_Parameter?>&Id='+nextId
                        } else if(previousId!=""){
                            window.location ='mgmt_validation_upd.php<?=$str_GET_Parameter?>&Id='+previousId
                        }else{
                            window.location ='mgmt_validation.php';
                        }
                    },

                });

            });
        });


    </script>
    <script>
        $('document').ready(function () {
            $('#preview').iZoom({borderColor: '#294A9C', borderStyle: 'double', borderWidth: '3px'});
            <?= $str_tree ?>

            <?= $str_Img ?>
            $(document).on('keyup', '.input_accertatore_number', function (e) {
                const code = $(this).val();
                const index = $(this).data('number');
                let isFound = false;
                if(!code){
                    $('.select_controller_' +index).val('');
                    return;
                }

                $('.select_controller_' +index+' option').each(function(){
                    if ($(this).html().startsWith(code)) {
                        $('.select_controller_' +index).val($(this).val());
                        isFound = true;
                    }

                });
                if(!isFound){
                    $('.select_controller_' +index).val('');
                    return;
                }
            });

            var AccertatoreNumber = $('#AccertatoreNumber').val();
            for(i=1;i<=AccertatoreNumber;i++){
                $(document).on('change','.select_controller_'+i,function (e) {
                    var cont_id = $(this).attr('order');
                    var id = $(this).val();
                    var article = $('#id1_1').val();
                    var FineDate = $('#FineDate').val();
                    check_accertatore(id,article,FineDate,cont_id)
                });
            }
            var nr = $('#nr').val();
            if(nr>1){
                $("#upace").show();
            }

            var ControllerTemplate = index =>`
                <div class id="BoxAccertatore_${index}">

                    <div class="col-sm-4 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
                        Accertatore
                    </div>

                    <div class="col-sm-2 BoxRowCaption" style="height:4rem;background-color: rgb(40, 114, 150);">
                        <input class="form-control frm_field_numeric input_accertatore_number" data-number="${index}" type="text" name="ControllerCode" id="ControllerCode" style="width:5rem">
                    </div>

                    <div class="col-sm-6 BoxRowCaption datas_${index}" style="height:4rem;background-color: rgb(40, 114, 150);">
                        <span id="span_acce_${index}"></span>
                        <select class="form-control frm_field_required select_controller_${index}" name="ControllerId[]" id="ControllerId[]" order="${index}">
                        <option></option>
                        <?php
                            $ControllersOptions = "";
                            foreach ($all_controllers as $row_selected ){
                                $id_all = $row_selected['Id'];
                                $value_all = $row_selected['Name'];
                                $code_all = $row_selected['Code'];
                                $ControllersOptions .= "<option  value=\"$id_all\">$value_all</option>";
                            }
                            echo $ControllersOptions;
                            ?>
                        </select>
                    </div>

                </div>
            `;

            if (AccertatoreNumber > 1){
                $("#upace_upd").show();
            }

            $(document).on('change','#FineDate',function() {
                for(i=1;i<=5;i++) {
                    $('.select_controller_'+i).trigger("change");
                }
            });

            $('#downace_upd').click(function () {

                AccertatoreNumber++;

                $(document).on('change','.select_controller_'+AccertatoreNumber,function (e) {
                    var cont_id = $(this).attr('order');
                    var id = $(this).val();
                    var article = $('#id1_1').val();
                    var FineDate = $('#FineDate').val();
                    check_accertatore(id,article,FineDate,cont_id)
                });
                $("#Controllers").append(ControllerTemplate (AccertatoreNumber))
                $("#upace_upd").show();

                if(AccertatoreNumber==5){
                    $("#downace_upd").hide();
                }else{
                    $("#downace_upd").show();
                }

                $('#AccertatoreNumber').val(AccertatoreNumber);

                $(document).on('change','.select_controller_'+AccertatoreNumber,function () {
                    $('#update').val("Salva");
                    $('#update').show();
                    $('#back').val("Anulla")
                });

            });


            $('#upace_upd').click(function () {
                $("#BoxAccertatore_"+AccertatoreNumber).remove();
                AccertatoreNumber--;
                if(AccertatoreNumber==1){
                    $("#upace_upd").hide();
                }else{
                    $("#downace_upd").show();
                }
                $('#AccertatoreNumber').val(AccertatoreNumber);
            });

        });
    </script>
<?php
include(INC . "/footer.php");
