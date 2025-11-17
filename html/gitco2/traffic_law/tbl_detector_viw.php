<?php

include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_detector.php");
include(INC."/function.php");
require(INC."/initialization.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$Id = CheckValue('DetectorId','n');

$Validation=CheckValue("Validation","n");

$CurrentYear = $_SESSION['year'];

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
$rs_Detector = $rs->ExecuteQuery("SELECT DT.Title AS DetectorType, DT.ViolationTypeId, D.* 
    FROM Detector as D JOIN DetectorType as DT ON D.DetectorTypeId=DT.Id 
    WHERE D.Id=".$Id);
$r_Detector = mysqli_fetch_array($rs_Detector);
$str_Fixed = ($r_Detector['Fixed'] == 1) ? "SI" : "NO";

//GET ARTICLES INFO FILTERED BY VIOLATIONTYPE
$rs_Article = $rs->ExecuteQuery("SELECT Id, CityId, DescriptionIta, Article, Paragraph, Letter, Id1, Id2, Id3, AdditionalTextIta
    FROM Article WHERE ViolationTypeId=".$r_Detector['ViolationTypeId']." AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' 
    ORDER BY Article ASC, Paragraph ASC, Letter ASC, Id1 ASC, Id2 ASC, Id3 ASC");
$a_articles = mysqli_fetch_all($rs_Article,MYSQLI_ASSOC);

$tableName = "DetectorArticle";
//GET DETECTOR ARTICLES
$rs_DetectorArticle = $rs->ExecuteQuery("SELECT ArticleId FROM ".$tableName." WHERE DetectorId=".$Id);
$a_detectorArticles = mysqli_fetch_all($rs_DetectorArticle,MYSQLI_ASSOC);

//GET REASONS FILTERED BY CITYID
$rs_Reason = $rs->ExecuteQuery("SELECT Progressive,Id,TitleIta FROM Reason WHERE Id='".$r_Detector['ReasonId']."'");
$a_reason = mysqli_fetch_array($rs_Reason);

$a_DetectorSpeedLimits = array();
$rs_VehicleType = $rs->Select('VehicleType', "Id!=1");
$rs_DetectorSpeedLimits = $rs->Select('DetectorSpeedLimits', "DetectorId=$Id");
while($r_DetectorSpeedLimits = $rs->getArrayLine($rs_DetectorSpeedLimits)){
    $a_DetectorSpeedLimits[$r_DetectorSpeedLimits['VehicleTypeId']] = $r_DetectorSpeedLimits['SpeedLimit'];
}

//SET CLASS DETECTOR
$cls_detector = new cls_detector($a_articles, $a_detectorArticles);
$str_articles = $cls_detector->getStringViewArticle();
$str_reasons = $cls_detector->getStringViewReason($a_reason);
$str_app_min = $cls_detector->getStringViewAppMin($r_Detector);

$str_LanguageDetector ='';
for($i=1;$i<count($a_LanguageDetector);$i++){
    $str_LanguageDetector .= '
            <div class="col-sm-2 BoxRowLabel">
                Testo <img src="'. IMG .'/' .$aLan[$i] .'" style="width:16px" />
            </div>
            <div class="col-sm-10 BoxRowCaption">
                '. StringOutDB($r_Detector[$a_LanguageDetector[$i]] ).'
            </div>
            <div class="clean_row HSpace4"></div>
        ';
}

$str_DetectorRatification ='';

$rs_DetectorRatification = $rs->Select("DetectorRatification", "DetectorId=$Id AND (($CurrentYear >= YEAR(FromDate) AND $CurrentYear <= YEAR(ToDate)) OR ($CurrentYear >= YEAR(FromDate) AND ToDate IS NULL) or (FromDate IS NULL AND ToDate IS NULL))", "ToDate IS NULL DESC, Todate DESC");
if(mysqli_num_rows($rs_DetectorRatification)>0){
    while($r_DetectorRatification = mysqli_fetch_array($rs_DetectorRatification)){
        $str_DetectorRatification .= '
            <div class="col-sm-9">
                <div class="col-sm-3 BoxRowLabel" style="height:6.4rem">
                    Descrizione
                </div>
                <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                    '. StringOutDB($r_DetectorRatification['Ratification']) .'
                </div>
            </div>
            <div class="col-sm-3">
                <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                    Data inizio
                </div>
                <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                    '. DateOutDB($r_DetectorRatification['FromDate']) .'
                </div>

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-6 BoxRowLabel" style="height:3.2rem">
                    Data fine
                </div>
                <div class="col-sm-6 BoxRowCaption" style="height:3.2rem">
                    '. DateOutDB($r_DetectorRatification['ToDate']) .'
                </div>
            </div>

            <div class="clean_row HSpace4"></div>
        ';
    }
} else {
    $str_DetectorRatification .= '
        <div class="col-sm-12 table_caption_H">
            Nessuna taratura trovata per l\'anno corrente.
        </div>';
}


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
                '.$r_Detector['Brand'].'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                Matricola
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['Number'].'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                Tipo
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['Kind'].'
            </div>
            ';
$str_DetectorDetails = '
            <div class="col-sm-4 BoxRowLabel">
                Tipologia
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.StringOutDB($r_Detector['DetectorType']).'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">Postazione fissa</div>
            <div class="col-sm-8 BoxRowCaption">
                '.$str_Fixed.'
            </div>
            <div class="clean_row HSpace4"></div>';
if($r_Detector['DetectorTypeId'] == 1){
    $str_DetectorDetails .= '
            <div class="col-sm-4 BoxRowLabel">
                Tolleranza del
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.number_format($r_Detector['Tolerance'],2,',','').'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                Distanza tra i 2 tutor velocità
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.number_format($r_Detector['SpeedLengthAverage'],2,',','').'
            </div>
            <div class="clean_row HSpace4"></div>';
}
$str_DetectorDetails .= '
            <div class="col-sm-4 BoxRowLabel">
                Posizione segnaletica
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['Position'].'
            </div>
';
$str_DetectorImport = '                 
            <div class="col-sm-4 BoxRowLabel">
                Codice import
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['Code'].'
            </div>
            <div class="clean_row HSpace4"></div>';
if($r_Detector['DetectorTypeId'] == 2){
    $str_DetectorImport .= '
            <div class="col-sm-4 BoxRowLabel">
                N. mmagini elaborate
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['UploadImageNumber'].'
            </div>
            <div class="clean_row HSpace4"></div>';
}
$str_DetectorImport .= '
            <div class="col-sm-4 BoxRowLabel font_small">
                Destinazione "Validazione dati"
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.($r_Detector['Validation']==1?'Si':'No').'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                # Megasp
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['IdMegasp'].'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                # Maggioli
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['MaggioliCode'].'
            </div>
    ';

$str_DetectorSpeedLimits = '';
while($r_VehicleType = $rs->getArrayLine($rs_VehicleType)){
    $str_DetectorSpeedLimits .= '
        <div class="BoxRowLabel col-sm-8">
        	'.$r_VehicleType['TitleIta'].'
        </div>
        <div class="BoxRowCaption col-sm-4">
        	'.number_format($a_DetectorSpeedLimits[$r_VehicleType['Id']] ?? '',2,',','').'
        </div>
        <div class="clean_row HSpace4"></div>';
}



$str_out .= '
    <div class="row-fluid">
        <div class="col-sm-12">
        	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
        		'.(!empty($PreviousId) ? '<a href="'.impostaParametriUrl(array('DetectorId' => $PreviousId,)).'"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Precedente" class="tooltip-r glyphicon glyphicon-arrow-left" style="font-size:3rem;color:#fff"></i></a>' : '').'
            </div>
            <div class="BoxRowTitle col-sm-10" style="width:83.33%;">
               Dati Rilevatore
            </div>
        	<div class="col-sm-1 BoxRowCaption text-center" style="height:3.5rem">
                '.(!empty($NextId) ? '<a href="'.impostaParametriUrl(array('DetectorId' => $NextId,)).'"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Prossimo" class="tooltip-r glyphicon glyphicon-arrow-right" style="font-size:3rem;color:#fff"></i></a>' : '').'
            </div>
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-3 row-fluid" style="border: white 1px solid;">
                <div class="col-sm-12 BoxRowLabel table_caption_I">
                    <b>DATI</b>
                </div>
                <div class="clean_row HSpace16"></div>
                '.$str_DetectorData.'
            </div>
            <div class="col-sm-5 row-fluid" style="border: white 1px solid;">
                <div class="col-sm-12 BoxRowLabel table_caption_I">
                    <b>CARATTERISTICHE</b>
                </div>
                <div class="clean_row HSpace16"></div>
                '.$str_DetectorDetails.'
            </div>
            <div class="col-sm-4 row-fluid" style="border: white 1px solid;">
                <div class="col-sm-12 BoxRowLabel table_caption_I">
                    <b>IMPORTAZIONE</b>
                </div>
                <div class="clean_row HSpace16"></div>
                '.$str_DetectorImport.'
            </div>

            <div class="clean_row HSpace16"></div>
            <div class="col-sm-3 row-fluid" style="border: white 1px solid;">
                <div class="col-sm-12 BoxRowLabel table_caption_I">
                    <b>TESTI</b>
                </div>
                <div class="clean_row HSpace16"></div>
                '. $str_LanguageDetector .' 
            </div>
            <div class="col-sm-9 row-fluid" style="border: white 1px solid;">
                <div class="'.($r_Detector['DetectorTypeId'] != 1 ? 'col-sm-12' : 'col-sm-9').'">
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>ARTICOLI</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    '. $str_articles . '
                    <div class="clean_row HSpace16"></div>
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>MANCATA CONTESTAZIONE</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    '.$str_reasons.'
                    <div class="col-sm-12 BoxRowLabel table_caption_I">
                        <b>APP. MIN. N.</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    '.$str_app_min.'
                </div>
            	<div class="col-sm-3"'.($r_Detector['DetectorTypeId'] != 1 ? ' style="display:none;"' : '').'>
                    <div class="col-sm-12 BoxRowLabel table_caption_I" style="font-size:1rem;">
                        <b>LIMITI VELOCITÀ PER TIPO DI VEICOLO</b>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    '.$str_DetectorSpeedLimits.'
            	</div>
            </div>

            <div class="clean_row HSpace16"></div>

            <div class="col-sm-3 BoxRowLabel" style="min-height:6.4rem">
                Testo aggiuntivo
            </div>
            <div class="col-sm-9 BoxRowCaption" style="min-height:6.4rem;height:auto;">
                '. StringOutDB($r_Detector['Ratification']) .'
            </div>

            <div class="clean_row HSpace16"></div>

            <div class="col-sm-12 BoxRowLabel table_caption_I">
                <b>TARATURA</b>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-12 BoxRowLabel">
                <b>VALIDITÀ TARATURA PERIODICHE REGISTRATE</b>
            </div>

            <div class="clean_row HSpace4"></div>

            <div style="height:13.2rem;overflow:auto;">

            '. $str_DetectorRatification .'

            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow text-center" style="height:6rem">
                    <input type="button" class="btn btn-default" id="back" value="Indietro" style="margin-top:1rem">
                </div>
            </div>
        </div>
    </div>    
    ';

echo $str_out;
?>
<script>
    $(document).ready(function () {

        $('#back').click(function () {
            window.location = "tbl_detector.php<?php echo $str_GET_Parameter;?>";
            return false;
        });
    });

</script>

<?php
include(INC."/footer.php");


