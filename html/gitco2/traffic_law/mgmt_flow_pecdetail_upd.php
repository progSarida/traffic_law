<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_mail.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$FlowId = CheckValue('FlowId','s');

$RowNumber = 0;

if (!empty($FlowId)){
    $rs_Flow = $rs->Select('Flow', "Id=$FlowId");
    $r_Flow = mysqli_fetch_assoc($rs_Flow);
    $rs_CountNotifications = $rs->SelectQuery("SELECT SUM(Delivered ='S') AS Delivered, SUM(Accepted ='S') AS Accepted, SUM(Anomaly ='S') AS Anomaly From FlowPecMails WHERE FlowId=".$FlowId);
    $r_Count = mysqli_fetch_assoc($rs_CountNotifications);
    
    $rs_FlowPecMails = $rs->SelectQuery('
        SELECT FPM.Id,FPM.MailSubject,FPM.SendError,FPM.Delivered,FPM.Accepted,FPM.Anomaly,FPM.TrespasserId,FPM.FineId,F.Code,F.FineDate,F.ProtocolId,F.ProtocolYear,F.VehiclePlate,T.Surname,T.Name,T.CompanyName
        FROM FlowPecMails FPM
        JOIN Trespasser T ON T.Id=FPM.TrespasserId
        JOIN Fine F ON F.Id=FPM.FineId
        WHERE FPM.FlowId='.$FlowId.' ORDER BY F.ProtocolId DESC
    ');
    $RowNumber = mysqli_num_rows($rs_FlowPecMails);
}

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_update" action="mgmt_flow_pecdetail_upd_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		<input type="hidden" name="FlowId" value="<?= $FlowId; ?>">
		
        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4.4rem; font-size:2rem;">
            <strong>Notifiche di accettazione/consegna</strong>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <?php if($RowNumber > 0): ?>
        	<div class="BoxRowLabel col-sm-2">Numero flusso</div>
    		<div class="BoxRowCaption col-sm-1"><?= '('.$r_Flow['CityId'].') '.$r_Flow['Number'].'/'.$r_Flow['Year'] ?></div>
        	<div class="BoxRowLabel col-sm-2">Ricevute di accettazione trovate</div>
        	<div class="BoxRowCaption col-sm-1"><?= $r_Count['Accepted'] ?> su <?= $RowNumber ?><?= $RowNumber == $r_Count['Accepted'] ? ' <i class="glyphicon glyphicon-ok text-success"></i>' : '' ?></div>
        	<div class="BoxRowLabel col-sm-2">Ricevute di consegna trovate</div>
        	<div class="BoxRowCaption col-sm-1"><?= $r_Count['Delivered'] ?> su <?= $RowNumber ?><?= $RowNumber == $r_Count['Delivered'] ? ' <i class="glyphicon glyphicon-ok text-success"></i>' : '' ?></div>
        	<div class="BoxRowLabel col-sm-2">Ricevute di mancata consegna trovate</div>
        	<div class="BoxRowCaption col-sm-1"><?= $r_Count['Anomaly'] ?> su <?= $RowNumber ?><?= $r_Count['Anomaly'] > 0 ? ' <i class="fas fa-exclamation-circle text-danger"></i>' : '' ?></div>
        	
        	<div class="clean_row HSpace4"></div>
        <?php endif; ?>
        
        <div class="table_label_H col-sm-1">Cron.</div>
        <div class="table_label_H col-sm-2">Riferimento</div>
        <div class="table_label_H col-sm-6">Oggetto mail</div>
        <div class="table_label_H col-sm-3">Stato</div>
        
        <div class="clean_row HSpace4"></div>
        
        <?php if($RowNumber > 0): ?>
        	<?php while($r_FlowPecMails = mysqli_fetch_assoc($rs_FlowPecMails)): ?>
    		    <div class="tableRow">
    		    	<input type="hidden" name="FlowPecMailsId[]" value="<?= $r_FlowPecMails['Id'] ?>">
                    <div class="table_caption_H col-sm-1"><?= $r_FlowPecMails['ProtocolId'].' / '.$r_FlowPecMails['ProtocolYear']; ?></div>
                    <div class="table_caption_H col-sm-2"><?= $r_FlowPecMails['Code']; ?></div>
                    <div class="table_caption_H col-sm-6"><?= $r_FlowPecMails['MailSubject']; ?></div>
                    <div class="table_caption_H col-sm-3 text-center table_caption_warning">
                        <i class="tooltip-r fa fa-envelope fa-fw <?= isset($r_FlowPecMails['SendError']) ? 'text-danger' : 'text-success'; ?>" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= isset($r_FlowPecMails['SendError']) ? $r_FlowPecMails['SendError'] : 'Invio avvenuto' ?>"></i>
                        <i class="tooltip-r fa fa-paper-plane fa-fw<?= $r_FlowPecMails['Delivered'] === 'S' ? ' text-success' : ' opaque'; ?>" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= $r_FlowPecMails['Delivered'] === 'S' ? 'Consegna PRESENTE' : 'Consegna ASSENTE'; ?>"></i>
                        <i class="tooltip-r fas fa-clipboard-check fa-fw<?= $r_FlowPecMails['Accepted'] === 'S' ? ' text-success' : ' opaque'; ?>" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= $r_FlowPecMails['Accepted'] === 'S' ? 'Accettazione PRESENTE' : 'Accettazione ASSENTE'; ?>"></i>
                        <?php if($r_FlowPecMails['Anomaly'] === 'T'): ?>
                        	<i class="tooltip-r fas fa-hourglass-2 fa-fw text-danger" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="Preavviso di mancata consegna"></i>
                        <?php elseif($r_FlowPecMails['Anomaly'] === 'S'): ?>
                        	<i class="tooltip-r fas fa-exclamation-circle fa-fw text-danger" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="Mancata consegna"></i>
                        <?php else: ?>
                        	<i class="tooltip-r fas fa-exclamation-circle fa-fw opaque" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="Nessuna anomalia"></i>
                        <?php endif; ?>
                    </div>
                </div>
                
    			<div class="clean_row HSpace4"></div>
        	<?php endwhile; ?>
			        
            <div class="table_label_H HSpace4" style="height:8rem;">
            	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
            	<?php if ($r_Count['Delivered'] < $RowNumber || $r_Count['Accepted'] < $RowNumber): ?>
	            	<button id="update" type="submit" class="btn btn-info" style="margin-top:2rem;width:inherit;"><i id="UpdateIcon" class="fas fa-sync-alt fa-fw"></i> Aggiorna</button>
            	<?php endif; ?>
            </div>
            
        <?php else: ?>
	        <div class="table_caption_H col-sm-12 text-center">
				Nessun record presente
            </div>
        <?php endif; ?>
    </form>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#back').on('click', function(){
			window.location="<?= impostaParametriUrl(array('Filter' => 1), 'mgmt_flow.php'.$str_GET_Parameter); ?>";
			return false;
		});
		
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

      	$("#f_update").on('submit', function(){
          	$('#update').prop('disabled', true);
          	$('#UpdateIcon').toggleClass('fa-spin');
      	});
	});
</script>

<?php
include(INC."/footer.php");
