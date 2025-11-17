<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC."/menu_" . $_SESSION['UserMenuType'] . ".php");
require_once(CLS . "/cls_view.php");
include(CLS . "/cls_message.php");
include(PGFN."/fn_prn_anag_anomalies.php");

$cls_view = new CLS_VIEW(FINETRESPASSERLIST);

$str_Where = '1=1';
$str_Where .= " AND F.CityId='{$_SESSION['cityid']}' ";

//$n_RecordLimit = CheckValue('RecordLimit', 'n');
//$Search_Type = CheckValue('AnomalyType', 'n');
//$s_TypePlate = CheckValue('NationalityType', 's');
$Filter = CheckValue('Filter','n');

if ($Search_FromProtocolYear != '') {
    $str_Where .= " AND ProtocolYear >= " . $Search_FromProtocolYear;
    }
if ($Search_ToProtocolYear != '') {
    $str_Where .= " AND ProtocolYear <= " . $Search_ToProtocolYear;
    }
if ($Search_FromProtocolId != '') {
    $str_Where .= " AND ProtocolId >= " . $Search_FromProtocolId;
    }
if ($Search_ToProtocolId != '') {
    $str_Where .= " AND ProtocolId <= " . $Search_ToProtocolId;
    }
if ($Search_FromFineDate != '') {
    $FormattedDate = checkDateString('d/m/Y', $Search_FromFineDate);
    $str_Where .= " AND FineDate >= '" . $FormattedDate->format('Y-m-d')."'";
    }
if ($Search_ToFineDate != '') {
    $FormattedDate = checkDateString('d/m/Y', $Search_ToFineDate);
    $str_Where .= " AND FineDate <= '" . $FormattedDate->format('Y-m-d')."'";
    }
if ($Search_Ref != '') {
    $str_Where .= " AND Code LIKE '%" . $Search_Ref . "%'";
    }
if ($s_TypePlate != 'Tutti') {
    if($s_TypePlate=='N')
        $str_Where .= " AND CountryId = 'Z000'";
    else
        $str_Where .= " AND CountryId != 'Z000'";
    }
if ($Search_Country != '') {
        $str_Where .= " AND CountryId = ".$Search_Country;
    }
if ($Search_FromNotificationDate != '') {
    $FormattedDate = checkDateString('d/m/Y', $Search_FromNotificationDate);
    $str_Where .= " AND NotificationDate >= '".$FormattedDate->format('Y-m-d')."'";
    }
if ($Search_ToNotificationDate != '') {
    $FormattedDate = checkDateString('d/m/Y', $Search_ToNotificationDate);
    $str_Where .= " AND NotificationDate <= '".$FormattedDate->format('Y-m-d')."'";
    }
if ($Search_Trespasser != '') {
    $fullName = str_replace(" ", "", $Search_Trespasser);
    $str_Where .= " AND CONCAT(Name,Surname) LIKE '%".$fullName."%'";
    }


$str_Where .= setSearchAnomaly($Search_Genre, $Search_Type);

$query = $cls_view->generateSelect($str_Where,null,null,null);
$rs_fineTrespasserList = $rs->SelectQuery($query);
$RowNumber = mysqli_num_rows($rs_fineTrespasserList);

$RowCounter = 0;

echo $str_out;
?>

<div class="row-fluid">
    <div class="progress" style="display:none;margin:0;">
       <div id="progressbar" class="progress-bar progress-bar-striped progress-bar-success" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
    </div>

    <div class="row-fluid">
      <div id="message"></div>
    </div>

    <div class="clean_row HSpace4"></div>

    <form id="f_Search" action="prn_anag_anomalies.php" method="post">
        <input type="hidden" name="btn_search" value="1">
        <input type="hidden" name="PageTitle" value="Stampa/Anomalie anagrafica">
        <input type="hidden" name="Filter" value="1">
        <div class="col-sm-12" >
            <div class="col-sm-11">
                <div class="col-sm-1 BoxRowLabel">
                    Da anno
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear, false)?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Ad anno
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear, false)?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_numeric" type="text" value="<?=$Search_FromProtocolId?>" id="Search_FromProtocolId" name="Search_FromProtocolId">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    A cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_numeric" type="text" value="<?=$Search_ToProtocolId?>" id="Search_ToProtocolId" name="Search_ToProtocolId">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da data accert.
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="<?=$Search_FromFineDate?>" name="Search_FromFineDate">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    A data accert.
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="<?=$Search_ToFineDate?>" name="Search_ToFineDate">
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                    Riferimento
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Ref" type="text" value="<?=$Search_Ref?>">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nazionalità trasgressore
                </div>
                <div class="col-sm-1 BoxRowCaption NT">
                    <select class="form-control" name="TypePlate" id="NationalityType" value="<?= $s_TypePlate?>">
                        <option>Tutti</option>
                        <option value="N">Nazionali</option>
                        <option value="F">Estere</option>
                    </select>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?= CreateSelectConcat("SELECT '' as CountryId, 'Tutti' AS Title UNION SELECT distinct T.CountryId, C.Title FROM Trespasser T JOIN Country C ON T.CountryId=C.Id WHERE T.CountryId!='Z000' AND T.CityId = '{$_SESSION['cityid']}' ORDER BY Title='Tutti' DESC, Title", "Search_Country", "CountryId", "Title", $Search_Country, true)?>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da data notifica
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="<?=$Search_FromNotificationDate?>" name="Search_FromNotificationDate">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    A data notifica
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="<?=$Search_ToNotificationDate?>" name="Search_ToNotificationDate">
                </div>
    
                <div class="clean_row HSpace4"></div>
    
                <div class="col-sm-1 BoxRowLabel">
                    Tipo persona:
                </div>
                <div class="col-sm-2 BoxRowCaption persona">
                    <input type="radio" class="radio_genre" id="person_1" style="top:0;" name="Search_Genre" value="1"><span  style="position:relative;top:-0.3rem;"> Fisica</span>
                    <input type="radio" class="radio_genre" id="person_0" style="top:0;" name="Search_Genre" value="0"><span  style="position:relative;top:-0.3rem"> Entrambe</span>
                    <input type="radio" class="radio_genre" id="person_2" style="top:0;" name="Search_Genre" value="2"><span  style="position:relative;top:-0.3rem"> Ditta</span>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Trasgressore
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="<?= $Search_Trespasser ?>">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Elenco anomalie:
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    <?= CreateArraySelect(array(''), false, 'Search_Type', 'Search_Type', $Search_Type, false,null,'anomalies') ?>
                </div>
            </div>
            <div class="col-sm-1 BoxRowFilterButton" style="height:6.8rem">
                <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="height:100%;font-size:3rem;padding:0;margin:0;width:100%">
                    <i class="glyphicon glyphicon-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>


<?php if($Filter == 0):?>
	<div class="row-fluid">
		<div class="table_caption_H col-sm-12">Selezionare i filtri</div>
	</div>
<?php elseif($Filter > 0):?>
    <div class="row-fluid">
    <form name="f_upload" id="f_upload" action="" method="post">
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Riga</div>
            <div class="table_label_H col-sm-3">Nominativo</div>
            <div class="table_label_H col-sm-1">Data di nascita</div>
            <div class="table_label_H col-sm-1">Luogo di nascita</div>
            <div class="table_label_H col-sm-1">C.F./P.IVA</div>
            <div class="table_label_H col-sm-2">Indirizzo residenza/domicilio/sede</div>
            <div class="table_label_H col-sm-2">Anomalie</div>
            <div class="table_label_H col-sm-1"></div>
            
            <div class="clean_row HSpace4"></div>

            <input type="hidden" name="TypePlate" value="<?= $s_TypePlate ?>">

    <?php 
        while($r_fineTrespasserList = mysqli_fetch_array($rs_fineTrespasserList)):
            //Lista anomalie (escluso controllo di coerenza)
            $anomaliesList = manageAnomalies($r_fineTrespasserList, $r_fineTrespasserList['CountryId'] == 'Z000' ? 'N' : 'F');
            $anomaliesPresence = CheckAnomalyExistence($anomaliesList, true);
            $coherenceResult = CheckCodeInconsistence($r_fineTrespasserList, $Search_Type);
            $actualGenre = checkActualGenre($r_fineTrespasserList['Genre'], $r_fineTrespasserList['TaxCode'], $r_fineTrespasserList['VatCode']);
            //Il controllo sul tipo di persona è stato spostato a codice perchè il genere viene definito controllando il CF/PIVA
            if(($Search_Genre == 1 && $actualGenre != 'F') OR ($Search_Genre == 2 && $actualGenre != 'D')){
                continue;
            }
            
            //echo "<br>".$r_fineTrespasserList['Name']." - ".$actualGenre." - ".$anomaliesPresence;
            //Controlla se la riga è da saltare o meno in base al controllo di coerenza nome/cognome - CF
            //Questo perchè l'incoerenza non è definibile a partire dalla query ma dev'essere controllata lato codice
            //Il controllo si attiva solo in caso di persone fisiche con selezionati tutti i tipi
            if(($Search_Type == TYPE_ANOMALY_IH_NAMESURNAME || $Search_Type == TYPE_ANOMALY_ALL) && $actualGenre!='D'):
                //Salta la riga se il risultato del controllo di coerenza nome/cognome-CF dice di saltare la riga
                if($Search_Type == TYPE_ANOMALY_IH_NAMESURNAME && $coherenceResult == SKIP_LINE)
                    continue;
                //Salta la riga se si vuole vedere tutte le anomalie, non c'è incoerenza e non ci sono altre anomalie
                elseif(($Search_Type == TYPE_ANOMALY_ALL) && ($coherenceResult == SKIP_LINE) && !$anomaliesPresence)
                    continue;
            endif;

            if(!$anomaliesPresence) continue;
            
            $RowCounter++;?>
            <div class="table_caption_H col-sm-1"><?= $RowCounter?></div>
            <div class="table_caption_H col-sm-3"><?php 
              $name = "";
              $name .= $r_fineTrespasserList['CompanyName'] !=null ? $r_fineTrespasserList['CompanyName']." " : "";
              $name .= $r_fineTrespasserList['Surname'] != null ? $r_fineTrespasserList['Surname']." " : "";
              $name .= $r_fineTrespasserList['Name'] != null ? $r_fineTrespasserList['Name'] : "";
              echo $name;
              ?></div>
            <div class="table_caption_H col-sm-1"><?= $actualGenre!='D' ? DateOutDB($r_fineTrespasserList['BornDate']) : '/'?></div>
            <div class="table_caption_H col-sm-1"><?= $actualGenre!='D' ? $r_fineTrespasserList['BornPlace'] : '/'?></div>
            <div class="table_caption_H col-sm-1"><?= trim($r_fineTrespasserList['TaxCode']) != '' ? $r_fineTrespasserList['TaxCode'] : $r_fineTrespasserList['VatCode']?></div>
            <?php 
                //Parametri per la costruzione dell'indirizzo
                $residenceAddress = $r_fineTrespasserList['Address'] != "" ? $r_fineTrespasserList['Address'].", " : "";
                $residenceZip = $r_fineTrespasserList['ZIP'] != "" ? $r_fineTrespasserList['ZIP'].", " : "";
                //La provincia viene mostrata solo se è presente una città
                $residenceCityProvince = $r_fineTrespasserList['City'] != "" ? $r_fineTrespasserList['City'] : "";
                $residenceCityProvince .= $r_fineTrespasserList['Province'] != "" ? " (".$r_fineTrespasserList['Province'].")" : "";
              ?>
            <div class="table_caption_H col-sm-2"><?=  $residenceAddress.$residenceZip.$residenceCityProvince?></div>
            <div class="table_caption_H col-sm-2">
            	<?php if(!empty($a_anomaliesMessages = getAnomaliesMessages($anomaliesList))): ?>
            		<?php foreach($a_anomaliesMessages as $anomalyMessage): ?>
            			<i class="tooltip-r fas fa-exclamation-circle fa-fw" style="font-size:1.3rem;line-height:inherit" data-html="true" data-container="body" data-toggle="tooltip" data-placement="left" title="<?= $anomalyMessage ?>"></i>
            		<?php endforeach; ?>
            	<?php endif?>
            </div>
            <div class="table_caption_H col-sm-1">
            	<a href="mgmt_trespasser_viw.php<?=$str_GET_Parameter?>&Id=<?=$r_fineTrespasserList['TrespasserId']?>&Filter=<?=$Filter?>&AnomalyType=<?=$Search_Type?>"><span class="tooltip-r glyphicon glyphicon-eye-open" title="Visualizza" data-placement="top" style="position:absolute;left:5px;top:5px"></span></a>
            	<a href="mgmt_trespasser_upd.php<?=$str_GET_Parameter?>&Id=<?=$r_fineTrespasserList['TrespasserId']?>&Filter=<?=$Filter?>&AnomalyType=<?=$Search_Type?>"><span class="tooltip-r glyphicon glyphicon-pencil" title="Modifica" data-placement="top" style="position:absolute;left:25px;top:5px"></span></a>
            </div>
            <div class="clean_row HSpace4"></div>
        </div>
    <?php    
        endwhile;?>
    
    <?php 
    if($RowCounter == 0){ //Non può basarsi sulla query ma sul RowCounter perchè alcune esclusioni vengono fatte a codice
    ?>
    	<div class="table_caption_H col-sm-12">Nessun record presente</div>
	<?php 
        }
    ?>
            <div class="table_label_H col-sm-12" style="height:8rem; position:relative">
                <button class="btn btn-success btn-primary" type="button" id="btn_excel" style="margin-top:2rem;width:16rem">
                    <i class="fa fa-file-excel-o fa-fw"></i> Stampa prospetto
                </button>
            </div>
        </form>
    </div>
<?php endif;?>
<script type="text/javascript">
    $(document).ready(function () {
    	//$('.anomalies').attr("disabled",true);
    	$('#person_0').prop("checked",true);
    	$('#NationalityType').find('option[value=<?php echo json_encode($s_TypePlate)?>]').attr('selected', 'selected');
    	$('#Search_Country').find('option[value=<?php echo json_encode($Search_Country)?>]').attr('selected', 'selected');
    	$('#person_'+<?php echo json_encode($Search_Genre)?>).prop("checked","true");
    	anomaliesCombo();
    	$('#Search_Type').find('option[value=<?php echo json_encode($Search_Type)?>]').attr('selected', 'selected');
    });
    
	$('.radio_genre').on('change',() => {
		anomaliesCombo();
	});
	
	function anomaliesCombo()
		{
		var items;
		
		var all = <?= json_encode(unserialize(TYPE_ANOMALY_LIST)); ?>;
		
		var commonArray = <?= json_encode(unserialize(TYPE_ANOMALY_LIST_COMMON)); ?>;
		var physicalArray = <?= json_encode(unserialize(TYPE_ANOMALY_LIST_PERSON)); ?>;
		var juridicalArray = <?= json_encode(unserialize(TYPE_ANOMALY_LIST_COMPANY)); ?>;
		
		var common = Object.keys(all)
          .filter(key => commonArray.includes(key))
          .reduce((obj, key) => {
            return {
              ...obj,
              [key]: all[key]
            };
        }, {});
        
		var physical = Object.keys(all)
          .filter(key => physicalArray.includes(key))
          .reduce((obj, key) => {
            return {
              ...obj,
              [key]: all[key]
            };
        }, {});
        
		var juridical = Object.keys(all)
          .filter(key => juridicalArray.includes(key))
          .reduce((obj, key) => {
            return {
              ...obj,
              [key]: all[key]
            };
        }, {});
				
		if($('#person_1').is(':checked'))
        	items = $.extend(common,physical);
        if($('#person_0').is(':checked'))
        	items = $.extend(common,physical,juridical);
        if($('#person_2').is(':checked'))
    		items = $.extend(common,juridical);
    	
    	var str = "";
    	for(var key in items)	
    		str+="<option value='"+key+"'>"+items[key]+"</option>";
    	$('.anomalies').html(str);
    	
    	return items;
		}
	$('#btn_excel').on('click',()=>{
		$('#btn_excel').html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
    	$('#btn_excel').prop('disabled',true);
		$('#f_Search').attr('action','prn_anag_anomalies_exe.php');
		$('#f_Search').submit();
		});
</script>
<?php
include(INC . "/footer.php");
?>