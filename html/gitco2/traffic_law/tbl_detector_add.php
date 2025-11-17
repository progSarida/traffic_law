<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_table.php");
include(INC."/function.php");

$Validation=checKValue("Validation","n");
if (isset($_POST['insert'])){
    
    $Filters = CheckValue('Filters', 's');
    $DetectorTypeId = CheckValue('DetectorTypeId', 'n');

    require(INC."/initialization.php");
    $detector_rs=$rs->SelectQuery("SELECT max(progressive)+1 as progressive from Detector where CityId='{$_SESSION['cityid']}'");
    $detector=mysqli_fetch_array($detector_rs);
    $aDetector= array(
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
        array('field' => 'DetectorTypeId', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'Kind', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Brand', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Number', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Ratification', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Position', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Fixed', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'Tolerance', 'selector' => 'field', 'type' => 'flt', 'settype' => 'flt'),
        array('field' => 'Validation', 'selector' => 'value', 'type' => 'int','settype'=>'int','value'=>$Validation),
        array('field' => 'TitleIta', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleEng', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleGer', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleSpa', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleFre', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleRom', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitlePor', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitlePol', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleHol', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleAlb', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'TitleDen', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Code', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'SpeedLengthAverage', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'IdMegasp', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'MaggioliCode', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'UploadImageNumber', 'selector' => 'field', 'type' => 'int','settype' => 'int'),
        array('field' => 'progressive', 'selector' => 'value', 'type' => 'int','settype' => 'int', 'value'=>$detector['progressive']),
        array('field' => 'AppMinN', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Del', 'selector' => 'field', 'type' => 'date','settype' => 'date'),
    );

    $DetectorId = $rs->Insert('Detector',$aDetector);
    
    if($DetectorTypeId == 1){
        foreach($_POST['DetectorVehicleType'] as $vehicleTypeId => $speedLimit){
            $a_Ins_DetectorSpeedLimits = array (
                array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $vehicleTypeId, 'settype' => 'int'),
                array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $speedLimit, 'settype' => 'flt'),
                array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorId, 'settype' => 'int')
            );
            $rs->Insert('DetectorSpeedLimits', $a_Ins_DetectorSpeedLimits);
        }
    }

    $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
   
    header("location: ".impostaParametriUrl(array("DetectorId"=>$DetectorId), "tbl_detector_upd.php".$Filters));
}

include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$a_LanguageDetector = array(
    "",
    "TitleIta",
    "TitleEng",
    "TitleGer",
    "TitleSpa",
    "TitleFre",
    "TitleRom",
    "TitlePor",
    "TitlePol",
    "TitleHol",
    "TitleAlb",
    "TitleDen",
);


$rs_VehicleType = $rs->Select('VehicleType', "Id!=1");


$str_LanguageDetector ='';
for($i=1;$i<count($a_LanguageDetector);$i++){
    $str_LanguageDetector .= '
            <div class="col-sm-4 BoxRowLabel">
                Testo <img src="'. IMG .'/' .$aLan[$i] .'" style="width:16px" />
            </div>
            <div class="col-sm-8 BoxRowCaption">
                <input type="text" name="'. $a_LanguageDetector[$i] .'" id="'. $a_LanguageDetector[$i] .'" class="form-control frm_field_string frm_field_required">
            </div>
            <div class="clean_row HSpace4"></div>
        ';
}
$str_DetectorData = '
    <div class="col-sm-4 BoxRowLabel">
        Marca
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input type="text" name="Brand" id="Brand" class="form-control frm_field_string" value="" >
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-4 BoxRowLabel">
        Matricola
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input type="text" name="Number" id="Number" class="form-control frm_field_string frm_field_required" value="" >
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-4 BoxRowLabel">
        Tipo
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input type="text" name="Kind" id="Kind" class="form-control frm_field_string frm_field_required" value="" >
    </div>
    ';
$str_DetectorDetails = '
    <div class="col-sm-4 BoxRowLabel">
        Tipologia
    </div>
    <div class="col-sm-8 BoxRowCaption">
        '. CreateSelect("DetectorType","1=1","Id","DetectorTypeId","Id","Title",null,true,15, "frm_field_required").'
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-4 BoxRowLabel">Postazione fissa</div>
    <div class="col-sm-8 BoxRowCaption">
        <select name="Fixed" class="form-control" style="width: 15rem;">
            <option value="0" >NO</option>
            <option value="1" >SI</option>
        </select>
    </div>
    <div class="clean_row HSpace4"></div>
    <div id="DIV_Speed">
        <div class="col-sm-4 BoxRowLabel">
            Tolleranza del
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input type="text" name="Tolerance" id="Tolerance" class="form-control txt-warning" value="">
        </div>
        <div class="col-sm-6 BoxRowCaption">
            %
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Distanza tra i 2 tutor velocità
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="SpeedLengthAverage" id="SpeedLengthAverage" class="form-control" value="">
        </div>
        <div class="clean_row HSpace4"></div>
    </div>
    <div class="col-sm-4 BoxRowLabel">
        Posizione segnaletica
    </div>
    <div class="col-sm-8 BoxRowCaption">
      <input type="text" name="Position" id="Position" class="form-control" >
    </div>';
$str_DetectorImport = '
    <div class="col-sm-4 BoxRowLabel">
        Codice import
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input type="text" name="Code" id="Code" class="form-control frm_field_string " value="">
    </div>
    <div class="clean_row HSpace4"></div>

    <div id="DIV_Semaphore" style="display:none;">
        <div class="col-sm-4 BoxRowLabel">
            N. immagini elaborate
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="UploadImageNumber" id="UploadImageNumber" class="form-control frm_field_numeric " value="">
        </div>
        <div class="clean_row HSpace4"></div>
    </div>
    <div class="col-sm-4 BoxRowLabel font_small">
        Destinazione "Validazione dati"
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input name="Validation" type="checkbox" value="1" />
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-4 BoxRowLabel">
        # Megasp
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input type="text" name="IdMegasp" id="IdMegasp" class="form-control frm_field_numeric " value="">
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-4 BoxRowLabel">
        # Maggioli
    </div>
    <div class="col-sm-8 BoxRowCaption">
        <input type="text" name="MaggioliCode" id="MaggioliCode" class="form-control frm_field_numeric " value="">
    </div>
    ';


?>



    <form id="f_detector" method="post" action="tbl_detector_add.php">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div class="row-fluid">
            <div class="col-sm-12">
                <div class="BoxRowTitle" id="BoxRowTitle">
                    Inserimento Rilevatore
                </div>
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-3 row-fluid" style="border: white 1px solid;">
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>DATI</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    <?= $str_DetectorData; ?>
                </div>
                <div class="col-sm-5 row-fluid" style="border: white 1px solid;">
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>CARATTERISTICHE</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    <?= $str_DetectorDetails; ?>
                </div>
                <div class="col-sm-4 row-fluid" style="border: white 1px solid;">
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>IMPORTAZIONE</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    <?= $str_DetectorImport; ?>
                </div>

                <div class="clean_row HSpace16"></div>
                <div class="col-sm-3 row-fluid" style="border: white 1px solid;">
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>TESTI</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    <?=$str_LanguageDetector?>
                </div>
                <div class="col-sm-9 row-fluid" style="border: white 1px solid;">
                	<div id="DIV_ArticlesReason" class="col-sm-9">
	                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                            <b>ARTICOLI</b>
                        </div>
                        <div class="clean_row HSpace16"></div>
                        <div class="col-sm-12 BoxRowCaption">
                            <b>GLI ARTICOLI DEVONO ESSERE INSERITI DOPO LA CREAZIONE DEL RILEVATORE</b>
                        </div>
                       <div class="col-sm-12 BoxRowLabel table_caption_I">
                            <b>APP. MIN. N.</b>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            App. Min. N.
                        </div>
                        <div class="col-sm-10 BoxRowCaption">
                             <input type="text" name="AppMinN" id="AppMinN" class="form-control" value="">
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Del
                        </div>
                        <div class="col-sm-10 BoxRowCaption">
                            <input type="text" name="Del" id="Del" class="calfromdate form-control frm_field_date" value="">
                        </div>
                	</div>
                   	<div id="DIV_SpeedLimits" class="col-sm-3">
                        <div class="col-sm-12 BoxRowLabel table_caption_I" style="font-size:1rem;">
                            <b>LIMITI VELOCITÀ PER TIPO DI VEICOLO</b>
                        </div>
                        <div class="clean_row HSpace16"></div>
                        <?php while($r_VehicleType = $rs->getArrayLine($rs_VehicleType)): ?>
    	                    <div class="BoxRowLabel col-sm-8">
                            	<?= $r_VehicleType['TitleIta']; ?>
                            </div>
                            <div class="BoxRowCaption col-sm-4">
                            	<input type="text" name="DetectorVehicleType[<?= $r_VehicleType['Id']; ?>]" class="form-control vehiletype_speed" value="">
                            </div>
                            <div class="clean_row HSpace4"></div>
                        <?php endwhile; ?>
                	</div>
                </div>

                <div class="clean_row HSpace16"></div>

                <div class="col-sm-12 BoxRowCaption">
                    <b>LE TARATURE DEVONO ESSERE INSERITE DOPO LA CREAZIONE DEL RILEVATORE</b>
                </div>

                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input type="submit" class="btn btn-default" id="insert" name="insert" value="Salva" style="display: inline-block;">
                        <input type="button" class="btn btn-default" id="back" value="Indietro">
                    </div>
                </div>
            </div>
        </div>
    </form>

<script>
    $(document).ready(function () {
        $('#DetectorTypeId').change(function () {
            if ($(this).val() == 1){
            	$('#DIV_Speed').show();
            	$('#DIV_Semaphore').hide(); 
            	$('#DIV_SpeedLimits').show();
            	$('#DIV_ArticlesReason').addClass('col-sm-9').removeClass('col-sm-12');
            } else if ($(this).val() == 2){
            	$('#DIV_Speed').hide();
            	$('#DIV_Semaphore').show(); 
            	$('#DIV_SpeedLimits').hide(); 
            	$('#DIV_ArticlesReason').addClass('col-sm-12').removeClass('col-sm-9');
            } else {
            	$('#DIV_Speed').hide();
            	$('#DIV_Semaphore').hide(); 
            	$('#DIV_SpeedLimits').hide(); 
            	$('#DIV_ArticlesReason').addClass('col-sm-12').removeClass('col-sm-9');
            }
        });

        $('#f_detector').bootstrapValidator({
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
                Tolerance: {
                    validators: {
                        regexp : {
                            regexp: '^[0-9]{1,3}(,[0-9]{1,2})$',
                            message : 'Decimale'
                        },
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },
                SpeedLengthAverage: {
                    validators: {
                        regexp : {
                            regexp: '^[0-9]{1,4}(,[0-9]{1,2})$',
                            message : 'Decimale'
                        }
                    }
                },
                vehiletype_speed: {
                	selector: '.vehiletype_speed',
                    validators: {
                        regexp : {
                            regexp: '^[0-9]{1,4}(,[0-9]{1,2})$',
                            message : 'Decimale'
                        }
                    }
                },
            }
        })

        $('#back').click(function () {
            window.location = "tbl_detector.php<?php echo $str_GET_Parameter;?>";
            return false;
        });
        
        setTimeout(function(){
    			$('#f_detector input,textarea,select').siblings('.help-block').css({"top": "0.3rem", "left": "-4.5rem"});
            }, 100);
    });

</script>

<?php
include(INC."/footer.php");