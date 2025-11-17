<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_exp_injunction.php");
require_once(PGFN."/fn_prn_anag_anomalies.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

echo $str_out;

$Filter = CheckValue('Filter','n');
$PageTitle = CheckValue('PageTitle','s');

$str_Where = "FineId IN(";
$str_WhereSubQuery = "SELECT F.Id FROM Fine F JOIN FineTrespasser FT ON F.Id = FT.FineId JOIN Trespasser T ON FT.TrespasserId = T.Id WHERE 1=1";
$str_Order = "ORDER BY RegDate DESC, RegTime DESC";

$a_ControllerArray = array();
$a_Controllers = $rs->getResults($rs->Select("Controller", "CityId='{$_SESSION['cityid']}'"));
foreach($a_Controllers as $controller){
    $a_ControllerArray[$controller['Id']] = $controller['Name'];
}

//Filtro anno
if(!empty($Search_FromProtocolYear) && is_numeric($Search_FromProtocolYear))
  {
  $str_WhereSubQuery .= " AND F.ProtocolYear >= $Search_FromProtocolYear";
  }
if(!empty($Search_ToProtocolYear) && is_numeric($Search_ToProtocolYear))
  {
  $str_WhereSubQuery .= " AND F.ProtocolYear <= $Search_ToProtocolYear";
  }

//Filtro nazione
switch($s_TypePlate){
    case 'F' : $str_WhereSubQuery .= " AND T.CountryId != 'Z000'"; break;
    case 'N' :
    default : $str_WhereSubQuery .= " AND T.CountryId = 'Z000'";
}
$str_WhereSubQuery .= " AND F.CityId = '".$_SESSION['cityid']."'";
$str_WhereSubQuery .= ")";

$str_Where .= $str_WhereSubQuery;

$str_GroupBy = " GROUP BY FileName, FileNamePDF, FileNameXLS, ConcessionaireSendDate, RegDate, RegTime, OfficerControllerId, ProcessControllerId";

$dirPath = ($s_TypePlate=="N") ? NATIONAL_INJUNCTION : FOREIGN_INJUNCTION;
$dirPath.= "/" .$_SESSION['cityid'];

$webPath = ($s_TypePlate=="N") ? WEB_NATIONAL_INJUNCTION : WEB_FOREIGN_INJUNCTION;
$webPath.= "/" .$_SESSION['cityid'];

$rs_FineInjunction = $rs->SelectQuery("SELECT FileName, FileNamePDF, FileNameXLS, ConcessionaireSendDate, RegDate, RegTime, OfficerControllerId, ProcessControllerId FROM FineInjunction WHERE $str_Where $str_GroupBy $str_Order");
?>
<div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
    <div class="col-sm-11" style="font-size: 1.2rem;">
        <ul>
            <li>Nota bene:
            <ul style="list-style-position: inside;">
                <li>Per sbloccare la possibilità di scaricare le stampe è necessario inserire prima la data di invio al concessionario e salvare.</li>
                <li>Per aggiornare i file PDF è necessario salvare le modifiche prima di aprirli dall'apposito pulsante.</li>
            </ul>
        </ul>
    </div>
</div>
<div class="row-fluid">
    <form id="f_exp_injunction_data" name="f_exp_injunction_data" action="exp_injunction_data.php" method="post">
    	<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <div class="col-sm-11">
        	<input type="hidden" name="Filter" value="1">
        	<div class="col-sm-1 BoxRowLabel font_small">
                Nazionalità trasgressore
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(unserialize(EXP_INJUNCTION_NATIONALITY), true, 'TypePlate', 'TypePlate', $s_TypePlate, false, null, null, ''); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
        		Da anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', !$Filter && $Search_FromProtocolYear <= 0 ? $_SESSION['year'] : $Search_FromProtocolYear, false); ?>
        	</div>
        	<div class="col-sm-1 BoxRowLabel">
        		Ad anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', !$Filter && $Search_ToProtocolYear <= 0 ? $_SESSION['year'] : $Search_ToProtocolYear, false); ?>
        	</div>
        	<div class="col-sm-6 BoxRowLabel">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRowLabel">
            </div>
    	</div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
        
    	<div class="clean_row HSpace4"></div>
    	
		<div class="table_label_H col-sm-4">Nome tracciato</div>
		<div class="table_label_H col-sm-2">Data e ora creazione</div>
		<div class="table_label_H col-sm-1">N° verbali contenuti</div>
		<div class="table_label_H col-sm-1 font_small">Data invio al concessionario</div>
		<div class="table_label_H col-sm-1">Resp. procedimento</div>
		<div class="table_label_H col-sm-1">Funz. responsabile</div>
		<div class="table_label_H col-sm-2">Stampe</div>
        <div class="clean_row HSpace4"></div>
        <?php if(empty($s_TypePlate)): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere la nazionalità.
			</div>
        <?php else: ?>
    		<?php
    		$counter = 0;
    		while($r_FineInjunction = $rs->getArrayLine($rs_FineInjunction)){
        		$rs_CountFineInjunction = $rs->SelectQuery("SELECT COUNT(FineId) AS FineCount FROM FineInjunction WHERE FileName = '".$r_FineInjunction['FileName']."'");
        		$r_CountFineInjunction = $rs->getArrayLine($rs_CountFineInjunction);
        		
        		$ConcessionaireSendDate = DateOutDb($r_FineInjunction['ConcessionaireSendDate']);
	            
        		$showPDF = false;
	            $showXLS = false;
	            $pdfPath = "";
	            $xlsPath = "";
	            $txtPath = $webPath."/".$r_FineInjunction['FileName'].".txt";
	            
	            if(!empty($r_FineInjunction['FileNamePDF'])){
		          $showPDF = true;
		          $pdfPath = $webPath."/".$r_FineInjunction['FileNamePDF'].".pdf";;
	            }
                if(!empty($r_FineInjunction['FileNameXLS'])){
                  $showXLS = true;
                  $xlsPath = $webPath."/".$r_FineInjunction['FileNameXLS'].".xls";
                  }
        		$FineCount = $r_CountFineInjunction['FineCount'];
        		?>
            	<div class="tableRow">
                    <div class="table_caption_H col-sm-4 rowCell" id="FileName_<?= $counter?>" style="text-align:center;"><?= $r_FineInjunction['FileName']?></div>
                    <div class="table_caption_H col-sm-1 rowCell" id="RegDate_<?= $counter?>" style="text-align:center;"><?= DateOutDB($r_FineInjunction['RegDate'])?></div>
                    <div class="table_caption_H col-sm-1 rowCell" id="RegTime_<?= $counter?>" style="text-align:center;"><?= $r_FineInjunction['RegTime']?></div>
                    <div class="table_caption_H col-sm-1 rowCell" style="text-align:center;"><?= $FineCount?></div>
                    <div class="col-sm-1 BoxRowCaption rowCell" id="SendDate_<?= $counter?>">
                    	<input class="form-control frm_field_date" type="text" value="<?= $ConcessionaireSendDate?>" name="SendDateConcessionaire">
                    </div>
                    <div class="col-sm-1 BoxRowCaption rowCell">
                    	<?= CreateArraySelect($a_ControllerArray, true, "ProcessControllerId[]", "ProcessControllerId_$counter", $r_FineInjunction['ProcessControllerId']) ?>
                    </div>
                    <div class="col-sm-1 BoxRowCaption rowCell">
                    	<?= CreateArraySelect($a_ControllerArray, true, "OfficerControllerId[]", "OfficerControllerId_$counter", $r_FineInjunction['OfficerControllerId']) ?>
                    </div>
                    <?php if($r_FineInjunction['ConcessionaireSendDate'] != NULL){?>
                    <div class="table_caption_H col-sm-2 rowCell" id="buttonCell">
                    	<a class="btn btn-info btn-sm txtButton" href="<?= $txtPath ?>" target="_blank" download><b>Tracciato</b></a>
        			<?php if($showPDF): //Mostra il pulsante pdf solo se c'è effettivamente il file?>
        				<a class="btn btn-danger btn-sm pdfButton" href="<?= $pdfPath?>" target="_blank" download><b>PDF</b> <i class="fa fa-file-pdf"></i></a>
        			<?php endif;?>
        			<?php if($showXLS): //Mostra il pulsante xls solo se c'è effettivamente il file?>
        				<a class="btn btn-success btn-sm pdfButton" href="<?= $xlsPath?>" target="_blank" download><b>XLS</b> <i class="fa fa-file-excel"></i></a><br><br>
        			<?php endif;
        			}
        			else{?>
        			<div class="table_caption_H col-sm-2 rowCell" id="buttonCell">
        			<?php }?>
                    </div>
                    <div class="clean_row HSpace4"></div>
    			</div>
			<?php $counter++; }?>
			<input type="hidden" id="DirPath" value="<?= $dirPath; ?>">
	    	<div class="table_label_H HSpace4" style="height:8rem;">
                <button type="button" class="btn btn-success" id="act_button" style="margin-top:1rem;">Salva</button>
            </div>
		<?php endif; ?>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
    	$('.rowCell').css("height","3rem");
    	$('.txtButton').css("font-size","12px");
    	$('.txtButton').css("padding","1px");
    	$('.txtButton').css("margin-top","0.25rem");
    	$('.txtButton').css("color","white");
    	$('.pdfButton').css("font-size","12px");
    	$('.pdfButton').css("padding","1px");
    	$('.pdfButton').css("margin-top","0.25rem");
    	$('.pdfButton').css("color","white");
    });
    $('#search').on("click",(e)=>{
         var fromDate = $('#Search_FromProtocolYear').val();
         var toDate = $('#Search_ToProtocolYear').val();
         if(fromDate > toDate)
         	alert("ATTENZIONE: La data finale è antecedente alla data iniziale");
     	else
     		$('#f_exp_injunction_data').submit();
		e.preventDefault();
    });
    
    //Salvataggio dei dati
    $('#act_button').on('click', (e)=>{
    		if(confirm("Si stanno per salvare/modificare le date di inoltro associate ai tracciati dei ruoli. Continuare?")){
					$('#act_button').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
					$('#act_button').prop('disabled', true);
    		
        			 let contatore = '<?=$counter?>';
        			 let jsonBody = compilaJSON(contatore);
        			 
        			 $.ajax({
        			 	url:"ajax/ajx_injunction_data.php",
        			 	type:"POST",
        			 	dataType:"json",
        			 	cache:false,
        			 	data:{"Body":jsonBody},
        			 	success: function(data){
        			 		window.location="<?= impostaParametriUrl(array('Filter' => 1), 'exp_injunction_data.php'.$str_GET_Parameter); ?>";
        			 	},
        			 	error: function(data){
	 						$('#act_button').html('Salva');
							$('#act_button').prop('disabled', false);
        			 		console.log(data);
        			 		alert("Errore nell'esecuzione dell'operazione");
    			 		}
        			 });
    			 }
    		else {
            	e.preventDefault();
            	return false;
    			}
    	});
    
    function compilaJSON(counter){
    	let jsonBuilt = []; 
    	
    	for(var i = 0; i < counter; i++)
    		{	
    			let FileName = $('#FileName_'+i).text();
    			let DirPath = $('#DirPath').val();
    			let RegDate = $('#RegDate_'+i).text();
    			let RegTime = $('#RegTime_'+i).text();
    			let SendDate = $('#SendDate_'+i).children('input').val();
    			let ProcessControllerId = $('#ProcessControllerId_'+i).val();
    			let OfficerControllerId = $('#OfficerControllerId_'+i).val();
    			jsonBuilt.push({"dirpath":DirPath,"filename":FileName,"regdate":RegDate,"regtime":RegTime,"senddate":SendDate,"processcontrollerid":ProcessControllerId,"officercontrollerid":OfficerControllerId});
    		}
		return JSON.stringify(jsonBuilt);
	}
</script>
<?php
require_once(INC."/footer.php");
