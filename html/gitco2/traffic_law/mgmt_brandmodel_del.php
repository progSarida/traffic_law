<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Id = CheckValue('AnomalyBrandModelId', 's');

$rs_AnomalyBrandModel = $rs->SelectQuery("
    SELECT ABM.*,VT.TitleIta AS VehicleTypeTitle 
    FROM AnomalyBrandModel ABM 
    LEFT JOIN VehicleType VT ON VT.Id = ABM.VehicleTypeId
    WHERE ABM.Id=$Id");
$r_AnomalyBrandModel = $rs->getArrayLine($rs_AnomalyBrandModel);

$rs_Anomaly = $rs->SelectQuery("
    SELECT * 
    FROM FineAnomaly FAN
    JOIN Fine F ON FAN.FineId = F.Id
    WHERE FAN.AnomalyBrandModelId=$Id;
");
$RowNumber = mysqli_num_rows($rs_Anomaly);

echo $str_out;
?>
<div class="row-fluid">
    <form name="f_brandmodel_del" id="f_brandmodel_del" action="mgmt_brandmodel_del_exe.php" method="post">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="Id" value="<?= $Id; ?>">
        
    	<div class="table_label_H col-sm-12">ELIMINA ANOMALIA MARCA MODELLO</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="col-sm-2 BoxRowLabel">
            Marca
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <?= StringOutDB($r_AnomalyBrandModel['Brand']); ?>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Modello
        </div>
        <div class="col-sm-2 BoxRowCaption">
        	<?= StringOutDB($r_AnomalyBrandModel['Model']); ?>
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Tipo veicolo
        </div>
        <div class="col-sm-2 BoxRowCaption">
        	<?= StringOutDB($r_AnomalyBrandModel['VehicleTypeTitle']); ?>
        </div>
    	
    	<div class="clean_row HSpace4"></div>
    
    	<div class="table_label_H col-sm-12">VERBALI ASSOCIATI</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="table_label_H col-sm-1">Rifer.to</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Targa</div>
        <div class="table_label_H col-sm-1">Data acqu.</div>
        <div class="table_label_H col-sm-3">Marca</div>
        <div class="table_label_H col-sm-3">Modello</div>
        <div class="table_label_H col-sm-1"></div>
        
        <div class="clean_row HSpace4"></div>
        
        <?php if ($RowNumber > 0): ?>
            <?php while ($r_Anomaly = mysqli_fetch_array($rs_Anomaly)): ?>
            <div class="tableRow">
            	<input value="<?= $r_Anomaly['FineId'] ?>" type="hidden" name="FineId[]">
            	<div class="table_caption_H col-sm-1 text-center"><?= $r_Anomaly['Code']; ?></div>
            	<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($r_Anomaly['FineDate']); ?></div>
            	<div class="table_caption_H col-sm-1 text-center"><?= TimeOutDB($r_Anomaly['FineTime']); ?></div>
            	<div class="table_caption_H col-sm-1">
            		<span style="position:absolute; right:0.5rem;"><?= $r_Anomaly['VehiclePlate']; ?>
        				<i class="<?= $aVehicleTypeId[$r_Anomaly['VehicleTypeId']] ?>" style="color:#337AB7;"></i>
        			</span>
        		</div>
            	<div class="table_caption_H col-sm-1 text-center"><?= DateOutDB($r_Anomaly['RegDate']); ?></div>
            	<div class="table_caption_H col-sm-3 text-center"><?= StringOutDB($r_Anomaly['VehicleBrand']); ?></div>
            	<div class="table_caption_H col-sm-3 text-center"><?= StringOutDB($r_Anomaly['VehicleModel']); ?></div>
            	<div class="table_caption_button col-sm-1 col-sm-3">
            		<a href="<?= 'mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$r_Anomaly['Id'].'&AnomalyBrandModelId='.$Id; ?>"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>
            		<a href="<?= 'mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$r_Anomaly['Id'].'&AnomalyBrandModelId='.$Id; ?>"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>
            	</div>
            </div>
            <div class="clean_row HSpace4"></div>
            <?php endwhile; ?>
        <?php else: ?>
        	<div class="table_caption_H col-sm-12">
        		Nessun record presente.
        	</div>
        	<div class="clean_row HSpace4"></div>
        <?php endif; ?>
        
        <div class="table_label_H HSpace4" style="height:8rem;">
        	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
        	<button id="delete" type="submit" class="btn btn-danger" style="margin-top:2rem;width:inherit;"><i class="fas fa-trash"></i> Elimina</button>
        </div>
    </form>
</div>

<script type="text/javascript">
$(document).ready(function () {
	$('#back').on('click', function(){
		window.location="<?= 'mgmt_brandmodel.php'.$str_GET_Parameter; ?>";
	});
	
  	$(".tableRow").mouseover(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
  	});
  	$(".tableRow").mouseout(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
  	});

    $("#f_brandmodel_del").on('submit', function(e){
		if (confirm('Sei sicuro di voler cancellare questa accoppiata Marca-Modello dalle relative anomalie? Eventuali verbali associati a questa anomalia andranno gestiti singolarmente in Gestione\Anomalie')) {
			if (confirm('Sei proprio sicuro di voler procedere?')) {
				return true;
			}
		}
	    return false;
	});
});
</script>

<?php
require_once(INC."/footer.php");