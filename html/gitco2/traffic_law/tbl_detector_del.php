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

$CurrentYearStart = $_SESSION['year'].'-01-01';
$CurrentYearEnd = $_SESSION['year'].'-12-31';

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
$rs_Reason = $rs->ExecuteQuery("SELECT Reason.Id, Reason.TitleIta FROM Reason WHERE Id='".$r_Detector['ReasonId']."'");
$a_reason = mysqli_fetch_array($rs_Reason);

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

$rs_DetectorRatification = $rs->Select("DetectorRatification", "DetectorId =$Id AND FromDate <= '$CurrentYearEnd' AND (ToDate >= '$CurrentYearStart' OR ToDate IS NULL)", "FromDate DESC");
$n_CountRatification = 0;
if(mysqli_num_rows($rs_DetectorRatification)>0){
    while($r_DetectorRatification = mysqli_fetch_array($rs_DetectorRatification)){
        $n_CountRatification++;
        $str_ToDate = ($r_DetectorRatification['ToDate']!="") ? DateOutDB($r_DetectorRatification['ToDate']) : "";

        if($n_CountRatification == 1){
            $str_DetectorRatification .= '
            <div class="col-sm-9">
                <div class="col-sm-3 BoxRowLabel" style="height:6.4rem">
                    Omologazione
                </div>
                <div class="col-sm-9 BoxRowCaption" style="height:6.4rem">
                    '. StringOutDB($r_DetectorRatification['Ratification']) .'
                </div>
            </div>';
        } else {
            $str_DetectorRatification .= '
            <div class="col-sm-9">
                <div class="col-sm-12 BoxRowLabel" style="height:6.4rem">
                </div>
            </div>';
        }
        
        $str_DetectorRatification .= '
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
                    '. $str_ToDate .'
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
                '.$r_Detector['Tolerance'].'
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-4 BoxRowLabel">
                Distanza tra i 2 tutor velocità
            </div>
            <div class="col-sm-8 BoxRowCaption">
                '.$r_Detector['SpeedLengthAverage'].'
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


$str_out .= '
    <div class="row-fluid">
        <form id="f_detector" method="post" action="tbl_detector_del_exe.php">
            <input type="hidden" name="Id" value="'.$r_Detector['Id'].'">
            <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
            <div class="col-sm-12">
                <div class="BoxRowTitle" id="BoxRowTitle">
                   Dati Rilevatore
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
    
                <div class="clean_row HSpace16"></div>
    
                <div class="col-sm-3 BoxRowLabel" style="min-height:6.4rem">
                    Testo aggiuntivo
                </div>
                <div class="col-sm-9 BoxRowCaption" style="min-height:6.4rem;height:auto;">
                    '. StringOutDB($r_Detector['Ratification']) .'
                </div>
    
                <div class="clean_row HSpace16"></div>
    
                <div class="col-sm-9 BoxRowLabel table_caption_I">
                    <b>TARATURA</b>
                </div>
                <div class="col-sm-3 BoxRowLabel table_caption_I font_">
                    <b>VALIDITÀ TARATURA PERIODICHE INSERITE</b>
                </div>
    
                <div class="clean_row HSpace4"></div>
    
                <div id="RatificationsContainer" style="height:13.2rem;overflow:auto;">
    
                '. $str_DetectorRatification .'
    
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow text-center" style="height:6rem">
                        <div class="col-sm-12 text-center" style="line-height:6rem;">
                            <button type="submit" class="btn btn-danger">Elimina</button>
                            <button type="button" class="btn btn-default" id="back">Indietro</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>    
    ';

echo $str_out;
?>
<script>
    $(document).ready(function () {

    	$('#f_detector').on('submit', function(e){
    		if(confirm('Si stà per eliminare il rilevatore in modo definitivo. Continuare?')){
    			if(confirm('Si è proprio sicuri di voler continuare?'))
    				return true;
    			else return false;
    		} else return false;
    	});

        $('#back').click(function () {
            window.location = "tbl_detector.php<?php echo $str_GET_Parameter;?>";
            return false;
        });
    });

</script>

<?php
include(INC."/footer.php");


