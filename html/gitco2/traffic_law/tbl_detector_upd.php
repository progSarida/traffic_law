<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_detector.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Id = CheckValue('DetectorId','n');
$Validation=CheckValue("Validation","n");
$tableName = "DetectorArticle";

$CurrentYear = $_SESSION['year'];
//GET DETECTOR ARTICLES
$rs_DetectorArticle = $rs->ExecuteQuery("
    SELECT ArticleId FROM ".$tableName." WHERE DetectorId=".$Id."
");
$a_detectorArticles = mysqli_fetch_all($rs_DetectorArticle,MYSQLI_ASSOC);
if (isset($_POST['update'])){
    
    $rs->Start_Transaction();
    
    $a_DetectorSpeedLimits = array();
    $rs_DetectorSpeedLimits = $rs->Select('DetectorSpeedLimits', "DetectorId=$Id");
    while($r_DetectorSpeedLimits = $rs->getArrayLine($rs_DetectorSpeedLimits)){
        $a_DetectorSpeedLimits[$r_DetectorSpeedLimits['VehicleTypeId']] = $r_DetectorSpeedLimits['SpeedLimit'];
    }
    
    $DetectorTypeId = CheckValue('DetectorTypeId', 'n');
    $Filters = CheckValue('Filters', 's');
    
    $aDetector= array(
        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
        array('field' => 'DetectorTypeId', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'Kind', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Brand', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Number', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Ratification', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Fixed', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'Tolerance', 'selector' => 'field', 'type' => 'flt', 'settype' => 'flt'),
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
        array('field' => 'Position', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Validation', 'selector' => 'value', 'type' => 'int','settype'=>'int','value'=>$Validation),
        array('field' => 'SpeedLengthAverage', 'selector' => 'field', 'type' => 'flt', 'settype' => 'flt'),
        array('field' => 'IdMegasp', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'MaggioliCode', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'UploadImageNumber', 'selector' => 'field', 'type' => 'int','settype' => 'int'),
        array('field' => 'ReasonId', 'selector' => 'field', 'type' => 'int','settype' => 'int'),
        array('field' => 'AppMinN', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Del', 'selector' => 'field', 'type' => 'date','settype' => 'date'),
    );
    $rs->Update('Detector',$aDetector, 'Id='.$Id);
    
    $DetectorArticles = $_POST['DetectorArticle'];
    
    $a_DbArticle = array();
    for($i=0;$i<count($a_detectorArticles);$i++){
        if(array_search($a_detectorArticles[$i]['ArticleId'], $DetectorArticles)===false){
            $rs->Delete($tableName,'DetectorId='.$Id.' AND ArticleId='.$a_detectorArticles[$i]['ArticleId']);
        }
        else{
            $a_DbArticle[] = $a_detectorArticles[$i]['ArticleId'];
        }
    }
    
    for($i=0;$i<count($DetectorArticles);$i++){
        $aDetectorArticle= array(
            array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $Id, 'settype' => 'int'),
            array('field' => 'ArticleId', 'selector' => 'value', 'type' => 'int', 'value' => $DetectorArticles[$i], 'settype' => 'int')
        );
        
        if(array_search($DetectorArticles[$i], $a_DbArticle)===false && $DetectorArticles[$i]>0){
            $rs->Insert($tableName,$aDetectorArticle);
        }
    }
    
    if($DetectorTypeId == 1){
        foreach($_POST['DetectorVehicleType'] as $vehicleTypeId => $speedLimit){
            $a_Ins_DetectorSpeedLimits = array (
                array('field' => 'VehicleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $vehicleTypeId, 'settype' => 'int'),
                array('field' => 'SpeedLimit', 'selector' => 'value', 'type' => 'flt', 'value' => $speedLimit, 'settype' => 'flt'),
            );
            if(isset($a_DetectorSpeedLimits[$vehicleTypeId])){
                if(!empty($speedLimit)){
                    $rs->Update('DetectorSpeedLimits', $a_Ins_DetectorSpeedLimits, "DetectorId=$Id AND VehicleTypeId=$vehicleTypeId");
                } else {
                    $rs->Delete('DetectorSpeedLimits', "DetectorId=$Id AND VehicleTypeId=$vehicleTypeId");
                }
            } else {
                if(!empty($speedLimit)){
                    $a_Ins_DetectorSpeedLimits[] = array('field' => 'DetectorId', 'selector' => 'value', 'type' => 'int', 'value' => $Id, 'settype' => 'int');
                    $rs->Insert('DetectorSpeedLimits', $a_Ins_DetectorSpeedLimits);
                }
            }
        }
    } else {
        $rs->Delete('DetectorSpeedLimits', "DetectorId=$Id");
    }
    
    $str_Warning = '';
    
    if (($DetectorTypeId == 1 || $DetectorTypeId == 2)){
        
        $rs_ChkDetectorRatification = $rs->Select('DetectorRatification', "DetectorId=$Id AND ((FromDate <= '".date('Y-m-d')."' AND (ToDate >= '".date('Y-m-d')."' OR ToDate IS NULL)) OR (FromDate IS NULL AND ToDate IS NULL))");
        if(mysqli_num_rows($rs_ChkDetectorRatification) < 1){
            $str_Warning .= 'Alla data odierna non è presente alcuna taratura periodica in corso di validità.<br>';
        }
        
        $rs_ChkDetectorRatification = $rs->Select('DetectorRatification', "DetectorId=$Id AND ((YEAR(FromDate) <= '".date('Y')."' AND (YEAR(ToDate) >= '".date('Y')."' OR ToDate IS NULL)) OR (FromDate IS NULL AND ToDate IS NULL))");
        if(mysqli_num_rows($rs_ChkDetectorRatification) < 1){
            $str_Warning .= 'Per l\'anno di interesse non è presente alcuna taratura periodica.';
        }
    }
    
    if($str_Warning != ''){
        $_SESSION['Message']['Warning'] = 'ATTENZIONE!<br>'.$str_Warning;
    } else $_SESSION['Message']['Success'] = 'Azione eseguita con successo.';
    
    $rs->End_Transaction();
    
    header("location: tbl_detector.php".$Filters);
    
    DIE;
}
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
echo $str_out;

?>


<?php

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

$rs_Detectors = $rs->SelectQuery("SELECT Id FROM Detector where CityId = '{$_SESSION['cityid']}' order by Id desc");
while ($r_Detectors = mysqli_fetch_assoc($rs_Detectors)){
    $a_Detectors[] = $r_Detectors['Id'];
}
$Key = array_search($Id, $a_Detectors);
$PreviousId = array_key_exists(($Key+1),$a_Detectors) ? $a_Detectors[$Key+1] : null;
$NextId = array_key_exists(($Key-1),$a_Detectors) ? $a_Detectors[$Key-1] : null;

//GET DETECTOR FROM DB
$rs_Detector = $rs->ExecuteQuery("SELECT DT.Title AS DetectorType, DT.ViolationTypeId, replace(D.Position,'\"','\'') as PositionQuoted, replace(D.AppMinN,'\"','\'') as AppMinNQuoted,D.*
FROM Detector as D JOIN DetectorType as DT ON D.DetectorTypeId=DT.Id WHERE D.Id=".$Id);
$r_Detector = mysqli_fetch_array($rs_Detector);

$str_Fixed = ($r_Detector['Fixed'] == 1) ? "SI" : "NO";
$a_Fixed = array("" ,"");
$a_Fixed[$r_Detector['Fixed']] = " SELECTED";

//GET ARTICLES INFO FILTERED BY VIOLATIONTYPE
$rs_Article = $rs->ExecuteQuery("SELECT Id, CityId, DescriptionIta, Article, Paragraph, Letter, Id1, Id2, Id3, AdditionalTextIta
FROM Article WHERE ViolationTypeId=".$r_Detector['ViolationTypeId']." AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' 
ORDER BY Article ASC, Paragraph ASC, Letter ASC, Id1 ASC, Id2 ASC, Id3 ASC");
$a_articles = mysqli_fetch_all($rs_Article,MYSQLI_ASSOC);

//GET REASONS FILTERED BY CITYID
$rs_Reason = $rs->ExecuteQuery("SELECT * FROM Reason WHERE (CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Fixed IS NOT NULL) OR Id={$r_Detector['ReasonId']} ORDER by Id DESC");
$a_reasons = mysqli_fetch_all($rs_Reason,MYSQLI_ASSOC);

$a_DetectorSpeedLimits = array();
$rs_VehicleType = $rs->Select('VehicleType', "Id!=1");
$rs_DetectorSpeedLimits = $rs->Select('DetectorSpeedLimits', "DetectorId=$Id");
while($r_DetectorSpeedLimits = $rs->getArrayLine($rs_DetectorSpeedLimits)){
    $a_DetectorSpeedLimits[$r_DetectorSpeedLimits['VehicleTypeId']] = $r_DetectorSpeedLimits['SpeedLimit'];
}

//SET CLASS DETECTOR
$cls_detector = new cls_detector($a_articles, $a_detectorArticles);
$str_articles = $cls_detector->getStringUpdateArticle();
$str_reasons = $cls_detector->getStringUpdateReason($a_reasons, $r_Detector['Fixed'], $r_Detector['ReasonId']);
$str_app_min = $cls_detector->getStringUpdateAppMinN($r_Detector['AppMinNQuoted'], $r_Detector['Del']);

$str_LanguageDetector ='';
for($i=1;$i<count($a_LanguageDetector);$i++){
    $str_LanguageDetector .= '
            <div class="col-sm-4 BoxRowLabel">
                Testo <img src="'. IMG .'/' .$aLan[$i] .'" style="width:16px" />
            </div>
            <div class="col-sm-8 BoxRowCaption">
                <input type="text" name="'. $a_LanguageDetector[$i] .'" id="'. $a_LanguageDetector[$i] .'" class="form-control frm_field_string frm_field_required" value="'. StringOutDB($r_Detector[$a_LanguageDetector[$i]] ).'">
            </div>
            <div class="clean_row HSpace4"></div>
        ';
}

$str_CalibrationTemplate = '
    <div id="DetectorCalibrationTemplate" class="hidden">
        <div class="col-sm-9">
            <div class="BoxRowHTitle col-sm-1" style="height:6.4rem;display:flex;flex-direction: column;justify-content: center;align-items: center;">
                <button type="button" data-toggle="tooltip" data-placement="right" title="Elimina" calid="" class="btn btn-danger deletecalvalidity tooltip-r" style="padding: 0.5rem;margin-bottom: 0.5rem;width: 3rem;"><i class="fa fa-times"></i></button>
                <button type="button" data-toggle="tooltip" data-placement="right" title="Modifica" calid="" class="btn btn-info editcalvalidity tooltip-r" style="padding: 0.5rem;width: 3rem;"><i class="fa fa-pencil"></i></button>
                <button type="button" data-toggle="tooltip" data-placement="right" title="Annulla" calid="" class="btn btn-warning cancelcalvalidity tooltip-r hidden" style="padding: 0.5rem;width: 3rem;margin-bottom: 0.5rem;"><i class="fa fa-ban"></i></button>
                <button type="button" data-toggle="tooltip" data-placement="right" title="Applica" calid="" class="btn btn-warning applycalvalidity tooltip-r hidden" style="padding: 0.5rem;width: 3rem;"><i class="fa fa-check"></i></button>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
                Descrizione
            </div>
            <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                <textarea disabled origvalue="" class="caltext form-control frm_field_string" style="height:5.8rem;margin-left:0;resize: none;"></textarea>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                Data inizio
            </div>
            <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                <input disabled origvalue="" type="text" class="calfromdate form-control frm_field_date" value="">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                Data fine
            </div>
            <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                <input disabled origvalue="" type="text" class="caltodate form-control frm_field_date" value="">
            </div>
        </div>

        <div class="clean_row HSpace4"></div>
    </div>';

$str_DetectorRatification ='
    <div class="col-sm-12 BoxRowLabel table_caption_I">
        <b>TARATURA</b>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
    <div class="BoxRowLabel col-sm-12">
        <b>NUOVA VALIDITÀ TARATURA PERIODICA</b>
    </div>
    <div class="clean_row HSpace4"></div>

    <div id="InsertDetectorCalibration">
        <div class="col-sm-9">
            <div class="BoxRowHTitle col-sm-1" style="height:6.4rem;display:flex;flex-direction: column;justify-content: center;align-items: center;">
                <button type="button" id="AddCalValidity" data-toggle="tooltip" data-placement="right" title="Inserisci" class="btn btn-success tooltip-r" style="padding: 0.5rem;width: 3rem;"><i class="fa fa-plus"></i></button>
            </div>
            <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
                Descrizione
            </div>
            <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                <textarea class="caltext form-control frm_field_string" style="height:5.8rem;margin-left:0;resize: none;"></textarea>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                Data inizio
            </div>
            <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                <input type="text" class="calfromdate form-control frm_field_date">
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                Data fine
            </div>
            <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                <input type="text" class="caltodate form-control frm_field_date">
            </div>
        </div>

        <div class="clean_row HSpace4"></div>
    </div>

    <div class="col-sm-12 BoxRowLabel">
        <b>VALIDITÀ TARATURA PERIODICHE REGISTRATE</b>
    </div>

    <div class="clean_row HSpace4"></div>

    '.$str_CalibrationTemplate.'

    <div id="CalibrationsContainer" style="height:13.2rem;overflow:auto;">';


$rs_DetectorRatification = $rs->Select("DetectorRatification", "DetectorId=$Id AND (($CurrentYear >= YEAR(FromDate) AND $CurrentYear <= YEAR(ToDate)) OR ($CurrentYear >= YEAR(FromDate) AND ToDate IS NULL) or (FromDate IS NULL AND ToDate IS NULL))", "ToDate IS NULL DESC, Todate DESC");
if(mysqli_num_rows($rs_DetectorRatification)>0){
    while($r_DetectorRatification = mysqli_fetch_array($rs_DetectorRatification)){

        $str_DetectorRatification .= '
        <div id="DetectorCalibration_'. $r_DetectorRatification['Id'] .'">
            <div class="col-sm-9">
                <div class="BoxRowHTitle col-sm-1" style="height:6.4rem;display:flex;flex-direction: column;justify-content: center;align-items: center;">
                    <input type="hidden" name="RatificationId[]" value="'. $r_DetectorRatification['Id'] .'">
                    <button type="button" data-toggle="tooltip" data-placement="right" title="Elimina" calid="'. $r_DetectorRatification['Id'] .'" class="btn btn-danger deletecalvalidity tooltip-r" style="padding: 0.5rem;margin-bottom: 0.5rem;width: 3rem;"><i class="fa fa-times"></i></button>
                    <button type="button" data-toggle="tooltip" data-placement="right" title="Modifica" calid="'. $r_DetectorRatification['Id'] .'" class="btn btn-info editcalvalidity tooltip-r" style="padding: 0.5rem;width: 3rem;"><i class="fa fa-pencil"></i></button>
                    <button type="button" data-toggle="tooltip" data-placement="right" title="Annulla" calid="'. $r_DetectorRatification['Id'] .'" class="btn btn-warning cancelcalvalidity tooltip-r hidden" style="padding: 0.5rem;width: 3rem;margin-bottom: 0.5rem;"><i class="fa fa-ban"></i></button>
                    <button type="button" data-toggle="tooltip" data-placement="right" title="Applica" calid="'. $r_DetectorRatification['Id'] .'" class="btn btn-warning applycalvalidity tooltip-r hidden" style="padding: 0.5rem;width: 3rem;"><i class="fa fa-check"></i></button>
                </div>
                <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
                    Descrizione
                </div>
                <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                    <textarea disabled origvalue="'.StringOutDB($r_DetectorRatification['Ratification']).'" class="caltext form-control frm_field_string" style="height:5.8rem;margin-left:0;resize: none;">'.StringOutDB($r_DetectorRatification['Ratification']).'</textarea>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                    Data inizio
                </div>
                <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                    <input disabled origvalue="'.DateOutDB($r_DetectorRatification['FromDate']).'" type="text" class="calfromdate form-control frm_field_date" value="'. DateOutDB($r_DetectorRatification['FromDate']) .'">
                </div>

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                    Data fine
                </div>
                <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                    <input disabled origvalue="'.DateOutDB($r_DetectorRatification['ToDate']).'" type="text" class="caltodate form-control frm_field_date" value="'.DateOutDB($r_DetectorRatification['ToDate']).'">
                </div>
            </div>

            <div class="clean_row HSpace4"></div>
        </div>

        ';
    }
} else {
    $str_DetectorRatification .= '
        <div class="col-sm-12 table_caption_H">
            Nessuna validità taratura trovata per l\'anno corrente.
        </div>';
}
$str_DetectorRatification .= '</div>';

$str_DetectorData = '
        <div class="col-sm-4 BoxRowLabel">
            Id
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '.$r_Detector['progressive'].'
        </div>
        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Marca
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="Brand" id="Brand" class="form-control frm_field_string" value="'.$r_Detector['Brand'].'" >
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Matricola
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="Number" id="Number" class="form-control frm_field_string frm_field_required" value="'.$r_Detector['Number'].'" >
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Tipo
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="Kind" id="Kind" class="form-control frm_field_string frm_field_required" value="'.$r_Detector['Kind'].'" >
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            App. Min. N.
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="AppMinN" id="AppMinN" class="form-control" value="'.$r_Detector['AppMinN'].'" >
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            Del
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="Del" id="Del" class="calfromdate form-control frm_field_date" value="'.$r_Detector['Del'].'">
        </div>        
        ';
$str_DetectorDetails = '
        <div class="col-sm-4 BoxRowLabel">
            Tipologia
        </div>
        <div class="col-sm-8 BoxRowCaption">
            '. CreateSelect("DetectorType","1=1","Id","DetectorTypeId","Id","Title",$r_Detector['DetectorTypeId'],true,15, "frm_field_required").'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">Postazione fissa</div>
        <div class="col-sm-8 BoxRowCaption">
            <select name="Fixed" id="Fixed" class="form-control" style="width: 15rem;">
                <option value="0" '.$a_Fixed[0].'>NO</option>
                <option value="1" '.$a_Fixed[1].'>SI</option>
            </select>
        </div>
        <div class="clean_row HSpace4"></div>

        <div id="DIV_Speed"'.($r_Detector['DetectorTypeId'] != 1 ? ' style="display:none;"' : '').'>
            <div class="col-sm-4 BoxRowLabel">
                Tolleranza del
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" name="Tolerance" id="Tolerance" class="form-control txt-warning" value="'.number_format($r_Detector['Tolerance'],2,',','').'">
            </div>
            <div class="col-sm-6 BoxRowCaption">
                %
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                Distanza tra i 2 tutor velocità
            </div>
            <div class="col-sm-8 BoxRowCaption">
                <input type="text" name="SpeedLengthAverage" id="SpeedLengthAverage" class="form-control" value="'.number_format($r_Detector['SpeedLengthAverage'],2,',','').'">
            </div>
            <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-4 BoxRowLabel">
              Posizione segnaletica
          </div>
          <div class="col-sm-8 BoxRowCaption">
              <input type="text" name="Position" id="Position" class="form-control" value="'.$r_Detector['PositionQuoted'].'">
          </div>';
$str_DetectorImport = '
        <div class="col-sm-4 BoxRowLabel">
            Codice import
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="Code" id="Code" class="form-control frm_field_string " value="'.$r_Detector['Code'].'">
        </div>
        <div class="clean_row HSpace4"></div>
        <div id="DIV_Semaphore"'.($r_Detector['DetectorTypeId'] != 2 ? ' style="display:none;"' : '').'>
            <div class="col-sm-4 BoxRowLabel">
                N. immagini elaborate
            </div>
            <div class="col-sm-8 BoxRowCaption">
                <input type="text" name="UploadImageNumber" id="UploadImageNumber" class="form-control frm_field_numeric " value="'.$r_Detector['UploadImageNumber'].'">
            </div>
            <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-4 BoxRowLabel font_small">
            Destinazione "Validazione dati"
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input name="Validation" type="checkbox" ' . ChkCheckButton($r_Detector['Validation']) . ' value="1"/>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            # Megasp
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="IdMegasp" id="IdMegasp" class="form-control frm_field_numeric " value="'.$r_Detector['IdMegasp'].'">
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-4 BoxRowLabel">
            # Maggioli
        </div>
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="MaggioliCode" id="MaggioliCode" class="form-control frm_field_numeric " value="'.$r_Detector['MaggioliCode'].'">
        </div>
';


        ?>

<form id="f_detector" method="post" action="tbl_detector_upd.php">
    <input type="hidden" name="DetectorId" id="DetectorId" value="<?= $Id; ?>">
    <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
    <div class="row-fluid">
        <div class="col-sm-12">
        	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
        		<?php if(!empty($PreviousId)): ?>
                	<a href="<?= impostaParametriUrl(array('DetectorId' => $PreviousId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Precedente" class="tooltip-r glyphicon glyphicon-arrow-left" style="font-size:3rem;color:#fff"></i></a>
            	<?php endif; ?>
            </div>
            <div class="BoxRowTitle col-sm-10" style="width:83.33%;">
               Modifica Rilevatore
            </div>
        	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
        		<?php if(!empty($NextId)): ?>
                	<a href="<?= impostaParametriUrl(array('DetectorId' => $NextId,)); ?>"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Prossimo" class="tooltip-r glyphicon glyphicon-arrow-right" style="font-size:3rem;color:#fff"></i></a>
            	<?php endif; ?>
            </div>
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-3 row-fluid" style="border: white 1px solid;">
                <div class="col-sm-12 BoxRowLabel table_caption_I">
                    <b>DATI RILEVATORE</b>
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
            	<div id="DIV_ArticlesReason" class="<?= $r_Detector['DetectorTypeId'] != 1 ? 'col-sm-12' : 'col-sm-9'; ?>">
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>ARTICOLI</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    	<?=$str_articles?>
                    <div class="clean_row HSpace16"></div>
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>MANCATA CONTESTAZIONE</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                        <?=$str_reasons;?>
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                    <b>APP. MIN. N.</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                        <?=$str_app_min;?>
            	</div>
            	<div id="DIV_SpeedLimits" class="col-sm-3"<?= $r_Detector['DetectorTypeId'] != 1 ? ' style="display:none;"' : ''; ?>>
                    <div class="col-sm-12 BoxRowLabel table_caption_I" style="font-size:1rem;">
                        <b>LIMITI VELOCITÀ PER TIPO DI VEICOLO</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    <?php while($r_VehicleType = $rs->getArrayLine($rs_VehicleType)): ?>
	                    <div class="BoxRowLabel col-sm-8">
                        	<?= $r_VehicleType['TitleIta']; ?>
                        </div>
                        <div class="BoxRowCaption col-sm-4">
                        	<input type="text" name="DetectorVehicleType[<?= $r_VehicleType['Id']; ?>]" class="form-control vehiletype_speed" value="<?= number_format($a_DetectorSpeedLimits[$r_VehicleType['Id']] ?? '',2,',','');  ?>" disabled>
                        </div>
                        <div class="clean_row HSpace4"></div>
                    <?php endwhile; ?>
            	</div>
            </div>

            <div class="clean_row HSpace16"></div>
            
            <div class="col-sm-3 BoxRowLabel" style="height:6.4rem">
                Testo aggiuntivo
            </div>
            <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                <textarea rows="4" name="Ratification" id="Ratification" class="form-control frm_field_string" style="height:5.8rem;margin-left:0;resize: none;"><?= StringOutDB($r_Detector['Ratification']); ?></textarea>
            </div>
            
            <div class="clean_row HSpace16"></div>
            

            <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
                <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                <div class="col-sm-11" style="font-size: 1.2rem;">
                    <ul style="list-style-position: inside;">
                        <li>In caso di inserimento o modifica di una validità taratura periodica, è necessario inserire il relativo testo nel campo "Descrizione" e indicare almeno la <strong>"data di inizio"</strong> validità della stessa per poter salvare i dati inseriti.</li>
                        <li>In assenza di altre validità taratura perdiodiche già registrate, è possibile inserire una nuova validità con solo campo "Descrizione" compilato e periodo validità indefinito.
                    </ul>
                </div>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <?=$str_DetectorRatification;?>

            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input type="submit" class="btn btn-default" id="update" name="update" value="Salva" style="display: inline-block;">
                    <input type="button" class="btn btn-default" id="back" value="Indietro">
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function manageDetectorRatifications(action, detectorId, calId = null, calText = null, calFromDate = null, calToDate = null){
        return $.ajax({
            url: 'ajax/ajx_manageDetectorRatifications.php',
            type: 'POST',
            dataType: 'json',
            data: {Action: action, DetectorId: detectorId, CalibrationText: calText, FromDate: calFromDate, ToDate: calToDate, CalibrationId: calId},
            ContentType: "application/json; charset=UTF-8"
        });
    }

	function validateCalibration(text, fromDate, toDate){
        try {
            var currentYear = <?= $CurrentYear; ?>;
            var fromYear = toYear = parsedFromDate = parsedToDate = null;

            if(calFromDate.length > 0){
            	parsedFromDate = $.datepicker.parseDate('dd/mm/yy', calFromDate);
    			fromYear = new Date(parsedFromDate).getFullYear();
            }
            if(calToDate.length > 0){
    			parsedToDate = $.datepicker.parseDate('dd/mm/yy', calToDate);
            	toYear = new Date(parsedToDate).getFullYear();
            }

            if(text.length <= 0){
                alert('Specificare una descrizione.');
                return false;
            }
            if((parsedFromDate && parsedToDate) && (parsedFromDate > parsedToDate)){
                alert('La data di inizio validità non deve superare quella di fine validità.');
                return false;
            }
            if(fromYear && toYear && (currentYear < fromYear || currentYear > toYear)){
                if(!confirm('l\'anno di esercizio in utilizzo non è compreso nel periodo di validità specificato e pertanto non verrà mostrato fra le validità registrate su questa pagina. Continuare?'))
                	return false;
            } else if(fromYear && fromYear > currentYear){
                if(!confirm('l\'anno di esercizio in utilizzo non è compreso nel periodo di validità specificato e pertanto non verrà mostrato fra le validità registrate su questa pagina. Continuare?'))
                	return false;
            }
        } catch (error) {
            alert('Una o più date di inserimento specificate non sono valide');
            return false;
        }

        return true;
	}

	function refreshCalibrations(resultData){
		if(resultData.Result.Quantity == 0){
			$('#CalibrationsContainer').empty().append('<div class="col-sm-12 table_caption_H">Nessuna validità taratura trovata per l\'anno corrente.</div>');
		}else if(Object.keys(resultData.Result.NewDates).length > 0){
			$('#InsertDetectorCalibration').find('.caltext, .calfromdate, .caltodate').val('');
			$('#CalibrationsContainer').empty();
        	$.each(resultData.Result.NewDates, function(i, value) {
        		var clone = $("#DetectorCalibrationTemplate").clone();
        		clone.removeClass('hidden');
        		clone.attr('id', 'DetectorCalibration_'+value.Id);
        		clone.find('.deletecalvalidity, .editcalvalidity, .cancelcalvalidity, .applycalvalidity').each(function(){
        			$(this).attr('calid', value.Id).prop('disabled', false);
                });
        		clone.find('.calfromdate').attr('origvalue', value.FromDate).val(value.FromDate);
        		clone.find('.caltodate').attr('origvalue', value.ToDate).val(value.ToDate);
        		clone.find('.caltext').attr('origvalue', value.Ratification).val(value.Ratification);
        		$('#CalibrationsContainer').append(clone);
        	});
		}
	}

    function enableCalibrationButtons(enable){
        $('[id^=DetectorCalibration_]').find('.editcalvalidity, .deletecalvalidity').prop('disabled', !enable);
        $('#AddCalValidity').prop('disabled', !enable);
    }

    function editCalValidity(){
        calId = '#DetectorCalibration_'+$(this).attr('calid');
        $(calId).find('.editcalvalidity,.deletecalvalidity').addClass('hidden');
        $(calId).find('.applycalvalidity, .cancelcalvalidity').removeClass('hidden');
        $(calId).find('.calfromdate,.caltodate,.caltext').prop('disabled', false);
        enableCalibrationButtons(false);
    }

    function applyCalValidity(){
    	button = $(this);
    	calId = '#DetectorCalibration_'+$(this).attr('calid');
        calText = $(calId).find('.caltext').val();
        calFromDate = $(calId).find('.calfromdate').val();
        calToDate = $(calId).find('.caltodate').val();
        detectorId = $('#DetectorId').val();
        calibrationId = $(this).attr('calid');

        if(validateCalibration(calText, calFromDate, calToDate)){
            button.find('i').toggleClass('fa-check fa-circle-notch fa-spin');
            enableCalibrationButtons(false);
            $(calId).find('.applycalvalidity, .cancelcalvalidity').prop('disabled', true);

			$.when(manageDetectorRatifications('edit', detectorId, calibrationId, calText, calFromDate, calToDate)).done(function (data) {
				refreshCalibrations(data);
				alert(data.Result.Message);
				enableCalibrationButtons(true);
				$(calId).find('.applycalvalidity, .cancelcalvalidity').prop('disabled', false);
			}).fail(function(data){
				console.log(data);
                alert("error: " + data.responseText);
                button.find('i').toggleClass('fa-check fa-circle-notch fa-spin');
                enableCalibrationButtons(true);
                $(calId).find('.applycalvalidity, .cancelcalvalidity').prop('disabled', false);
			});
        }
    }

    function cancelCalValidity(){
    	calId = '#DetectorCalibration_'+$(this).attr('calid');
        $(calId).find('.calfromdate,.caltodate,.caltext').each(function(){
        	$(this).val($(this).attr('origvalue')).prop('disabled', true).removeClass('txt-warning');
        });
        enableCalibrationButtons(true);
        $(calId).find('.editcalvalidity,.deletecalvalidity').removeClass('hidden');
        $(calId).find('.applycalvalidity, .cancelcalvalidity').addClass('hidden');
    }

    function deleteCalValidity(){
        calibrationId = $(this).attr('calid');
        detectorId = $('#DetectorId').val();
        button = $(this);

        if(confirm('Si stà per cancellare il periodo di validità taratura in modo definitivo. Continuare?')){
            button.find('i').toggleClass('fa-times fa-circle-notch fa-spin');
            enableCalibrationButtons(false);
            
			$.when(manageDetectorRatifications('del', detectorId, calibrationId)).done(function (data) {
				refreshCalibrations(data);
				alert(data.Result.Message);
				enableCalibrationButtons(true);
			}).fail(function(data){
				console.log(data);
                alert("error: " + data.responseText);
                button.find('i').toggleClass('fa-times fa-circle-notch fa-spin');
                enableCalibrationButtons(true);
			});
        }
    }

    function addCalValidity(){
        button = $(this);
        calText = $('#InsertDetectorCalibration').find('.caltext').val();
        calFromDate = $('#InsertDetectorCalibration').find('.calfromdate').val();
        calToDate = $('#InsertDetectorCalibration').find('.caltodate').val();
        detectorId = $('#DetectorId').val();

        if(validateCalibration(calText, calFromDate, calToDate)){
            button.find('i').toggleClass('fa-plus fa-circle-notch fa-spin');
            enableCalibrationButtons(false);

			$.when(manageDetectorRatifications('add', detectorId, null, calText, calFromDate, calToDate)).done(function (data) {
				refreshCalibrations(data);
				alert(data.Result.Message);
				button.find('i').toggleClass('fa-plus fa-circle-notch fa-spin');
				enableCalibrationButtons(true);
			}).fail(function(data){
                console.log(data);
                alert("error: " + data.responseText);
                button.find('i').toggleClass('fa-plus fa-circle-notch fa-spin');
                enableCalibrationButtons(true);
			});
        }
    }

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
            alert("Cambiando tipo di rilevatore, per cambiare articoli e mancata contestazione è necessario salvare e riaprire la pagina.")
        });

        $('#Fixed').change(function () {
            $('#ReasonId').val('');
        	$('#ReasonId').find('option[fixed!="'+$(this).val()+'"]').prop('disabled', true);
        	$('#ReasonId').find('option[fixed="'+$(this).val()+'"], option:not([fixed])').prop('disabled', false);
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
                            regexp: '^[0-9]{1,3}(,[0-9]{1,2})$',
                            message : 'Decimale'
                        }
                    }
                },
            }
        }).on('success.form.bv', function(e){
            var submit = true;
        	$('#InsertDetectorCalibration').find('.caltext,.calfromdate,.caltodate').each(function() {
            	if($(this).val() != ''){
                	if(!confirm('Sono presenti dati non salvati in inserimento validità taratura periodica. Procedere comunque?'))
                    	submit = false;
            	}
        	});
        	$('#CalibrationsContainer').find('.caltext').each(function() {
            	if(!$(this).prop('disabled')){
                	if(!confirm('Sono presenti dati non salvati in modifica validità taratura periodiche. Procedere comunque?'))
                    	submit = false;
            	}
        	});

        	return submit;
        });

        $('#back').click(function () {
            window.location = "tbl_detector.php<?php echo $str_GET_Parameter;?>";
            return false;
        });

        $('#CalibrationsContainer').on('click', '.editcalvalidity', editCalValidity);
        $('#CalibrationsContainer').on('click', '.applycalvalidity', applyCalValidity);
        $('#CalibrationsContainer').on('click', '.cancelcalvalidity', cancelCalValidity);
        $('#CalibrationsContainer').on('click', '.deletecalvalidity', deleteCalValidity);
        $('#AddCalValidity').on('click', addCalValidity);
        
        setTimeout(function(){
    			$('#f_detector input,textarea, select').siblings('.help-block').css({"top": "0.3rem", "left": "-4.5rem"});
            }, 100);
        
    });

</script>

<?php
include(INC."/footer.php");



