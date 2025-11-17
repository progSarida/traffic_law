<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
require (CLS."/cls_message.php");
include (INC . "/function.php");
include (INC . "/header.php");
require (INC . "/initialization.php");
require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$aUserButton[] = 'viw';
$aUserButton[] = 'prn';

//Se "L'ente gestisce l'invio degli atti tramite PEC" non è abilitato blocca l'esecuzione
if ($r_Customer['ManagePEC'] == 0){
    echo $str_out;
    echo '<div class="alert alert-danger">La gestione dell\'invio degli atti tramite PEC non è abilitata per l\'ente in uso.</div>';
    DIE;
}

//Se "Abilita la creazione e la firma digitale della relata di notifica dei verbali con contestuale invio degli atti tramite PEC" non è abilitato blocca l'esecuzione
if ($r_Customer['EnableINIPECNotification'] == 0){
    echo $str_out;
    echo '<div class="alert alert-danger">La creazione e firma digitale delle relate di notifica PEC non è abilitata per l\'ente in uso.</div>';
    DIE;
}

//Se l'utente loggato non ha un firmatario associato blocca l'esecuzione
if ($_SESSION['controllerid'] == 0 && $_SESSION['usertype'] != 2){
    echo $str_out;
    echo '<div class="alert alert-danger">L\'utente in uso non dispone di un firmatario associato o dei privilegi necessari, pertanto non è possibile usufruire di questa procedura.</div>';
    DIE;
} else {
    $UserControllerId = $_SESSION['controllerid'];
    
    $rs_Controller = $rs->Select("Controller", "Id=".$UserControllerId);
    $r_Controller = mysqli_fetch_array($rs_Controller);
    
    if (mysqli_num_rows($rs_Controller) > 0){
        if (($r_Controller['NotificationDigitalSign'] == 0 && $r_Customer['EnableINIPECNotification'] == 1) || $r_Controller['Disabled'] == 1){
            echo $str_out;
            echo '<div class="alert alert-danger">Il firmatario associato all\'utente in uso non dispone di abilitazione alla firma di notifica digitale o risulta disabilitato, pertanto non è possibile usufruire di questa procedura affinchè la creazione e firma digitale delle relate di notifica dei verbali da notficare tramite PEC è abilitata.</div>';
            DIE;
        }
        $SignController = (isset($r_Controller['Qualification']) ? $r_Controller['Qualification'].' ' : '').$r_Controller['Name'];
    } else {
        echo $str_out;
        echo '<div class="alert alert-danger">Il firmatario associato all\'utente in uso non è stato trovato negli accertatori registrati, assicurarsi che il codice sia corretto.</div>';
        DIE;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

$Filter = CheckValue('Filter', 'n');
//Parametri di ritorno da createdynamic_pecnotification
$SignaturePwd = CheckValue('SignaturePwd', 's');

$str_Where = "
        F.CityId = '{$_SESSION['cityid']}'
        AND F.ProtocolYear = {$_SESSION['year']}
        AND FD.DocumentationTypeId = 13
        AND ((F.FineChiefControllerId={$_SESSION['controllerid']} AND F.StatusTypeId=20 AND F.FineTypeId IN(3,4)) or F.StatusTypeId=20)";

$str_WhereInit = $str_Where;

if ($Search_Type == 1){
    $str_Where .= " AND (FD2.Id IS NULL AND DATE(NOW()) > DATE(FD.VersionDate))";
} else if ($Search_Type == 2){
    $str_Where .= " AND TRIM(COALESCE(FP.SendError, '')) != ''";
} else if ($Search_Type == 3){
    $str_Where .= " AND FP.Anomaly='S'";
} else {
    //Ordine controlli:
    //1: Notifiche con invii falliti
    //2: Notifiche con ricevute di mancata consegna
    //3: Notifiche scadute
    $str_Where .= " AND (TRIM(COALESCE(FP.SendError, '')) != '' OR FP.Anomaly='S' OR (FD2.Id IS NULL AND DATE(NOW()) > DATE(FD.VersionDate)))";
}

$Search = $str_Where != $str_WhereInit;

if ($Filter == 1 && $Search){
    $rs_Documentation = $rs->SelectQuery("
        SELECT F.*,FD.Documentation,FD.FineId,FD.VersionDate,FD.Id AS DocumentationId,T.CompanyName,T.Name,T.Surname,FP.Anomaly,FP.SendError,FH.TrespasserId 
        FROM FineDocumentation FD
        LEFT JOIN FineDocumentation FD2 on FD.FineId=FD2.FineId AND FD.Documentation=REPLACE(FD2.Documentation, '_signed', '') AND FD2.DocumentationTypeId =14
        JOIN FineHistory FH ON substring(FD.Documentation, 32,10)=substring(FH.Documentation, 32,10) AND FH.NotificationTypeId =15
        JOIN Trespasser T ON FH.TrespasserId = T.Id
        LEFT JOIN FlowPecMails FP ON FH.TrespasserId=FP.TrespasserId AND FH.FineId=FP.FineId
        JOIN Fine F ON F.Id=FD.FineId
        WHERE $str_Where");
    
    $RowNumber = mysqli_num_rows($rs_Documentation);
}

$hasPrivateKey=hasPrivateKey($_SESSION['userid']);
        
echo $str_out;
?>

<div class="row-fluid">
	<form id="f_search" action="frm_recreatedynamic_pecnotification.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?=$PageTitle;?>">
		<input type="hidden" name="Filter" value="1">

        <div class="col-sm-11" style="height:4.5rem; border-right:1px solid #E7E7E7;">
        	<div class="col-sm-1 BoxRowLabel">
        		Filtra per:
        	</div>
        	<div class="col-sm-2 BoxRowCaption">
        		<?= CreateArraySelect(array('Tutte', 'Scadute', 'Invii falliti', 'Mancate consegne'), true, 'Search_Type', 'Search_Type', $Search_Type, true) ?>
        	</div>
        	<div class="col-sm-9 BoxRowLabel"></div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="col-sm-12 BoxRowLabel"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="font-size:3rem;padding:0;margin:0;width:100%;height:100%">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div>
    </form>

    <div class="clean_row HSpace4"></div>
    
    <form id="f_recreatedynamic_pecnotification" action="frm_recreatedynamic_pecnotification_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
    
    	<div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked/></div>
    	<div class="table_label_H col-sm-1">Cron.</div>
    	<div class="table_label_H col-sm-2">Dati atto</div>
    	<div class="table_label_H col-sm-3">Documentazione</div>
    	<div class="table_label_H col-sm-2">Trasgressore</div>
    	<div class="table_label_H col-sm-1">Data creazione</div>
    	<div class="table_label_H col-sm-2">Stato</div>
        <?php if ($Filter != 1 || !$Search):?>
            <div class="table_caption_H col-sm-12 text-center">
            	Inserire criteri di ricerca
            </div>
        <?php else: ?>
    		<?php if ($RowNumber > 0):?>
    			<?php $i=1; ?>
    			<div class="accordion-group">
    			<?php while($r_Documentation = mysqli_fetch_assoc($rs_Documentation)):?>
    				<?php $Operation = $r_Documentation['SendError'] != '' ? 1 : ($r_Documentation['Anomaly'] == 'S' ? 2 : 3); ?>
    				<div class="tableRow" id="accordion_<?= $i; ?>">
            			<div class="col-sm-1" style="text-align:center;padding:0">
	            			<div class="table_caption_button col-sm-6" style="text-align:center;">
	            				<?php if($Operation == 3): //TODO RIMUOVERE, controllo inserito per obbligare a processare solo le notifiche scadute dato che lo sviluppo degli altri casi è incompleto?>
                					<input type="checkbox" name="checkbox[]" value="<?= $r_Documentation['FineId'].','.$r_Documentation['TrespasserId'].','.$r_Documentation['DocumentationId'].','.$Operation; ?>" checked />
            					<?php endif;?>
            				</div>
	            			<div class="table_caption_H col-sm-6" style="text-align:center;">
                				<?= $i++; ?>
            				</div>
        				</div>
            			<div class="table_caption_H col-sm-1">
            				<?= $r_Documentation['ProtocolId'] . ' / ' . $r_Documentation['ProtocolYear'];?>
        				</div>
                    	<div class="table_caption_H col-sm-2" style="padding-right:0.5rem">
                        	<div class="col-sm-8">
                        		<?= $a_FineTypeId[$r_Documentation['FineTypeId']] . ' ' . DateOutDB($r_Documentation['FineDate']) . ' - ' . TimeOutDB($r_Documentation['FineTime']); ?>
                    		</div>
                        	<div class="col-sm-4 text-right">
                        		<?=StringOutDB($r_Documentation['VehiclePlate']);?>
                        		<i class="<?=$aVehicleTypeId[$r_Documentation['VehicleTypeId']];?>" style="color:#337AB7;"></i>
                    		</div>
                		</div>
                    	<div class="table_caption_H col-sm-3" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
    						<?=StringOutDB($r_Documentation['Documentation']);?>
                		</div>
                    	<div class="table_caption_H col-sm-2" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                    		<?=StringOutDB(trim($r_Documentation['CompanyName'] . ' ' . $r_Documentation['Surname'] . ' ' . $r_Documentation['Name']));?>
                		</div>
                    	<div class="table_caption_H col-sm-1">
                    		<?= isset($r_Documentation['VersionDate']) ? DateTimeOutDB($r_Documentation['VersionDate']) : '' ?>
                		</div>
                    	<div class="table_caption_H col-sm-1 text-center">
                    		<?php if($Operation == 1): ?>
                    		<a href="javascript:void(0);" id="heading_<?= $i; ?>" data-toggle="collapse" data-target="#collapse_<?= $i; ?>" aria-expanded="false" aria-controls="collapse_<?= $i; ?>">
    	                		<span class="fa-stack text-danger fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="Invio fallito. fare click per info" style="margin-top: 0.2rem;vertical-align:top;">
                                	<i class="fa-2x fa-slash fa-stack-1x fas"></i><i class="fa-paper-plane fa-stack-2x fas" style="/* color:Tomato */"></i>
                                </span>
                			</a>
                			<?php elseif($Operation == 2): ?>
    	                		<i class="fas fa-exclamation-circle fa-fw tooltip-r text-danger" data-container="body" data-toggle="tooltip" data-placement="left" title="Mancata consegna" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
                			<?php else: ?>
                				<i class="fas fa-hourglass-end fa-fw tooltip-r text-danger" data-container="body" data-toggle="tooltip" data-placement="left" title="Notifica scaduta" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
                			<?php endif; ?>
                		</div>
                        <div class="table_caption_button  col-sm-1">
                    		<?=ChkButton($aUserButton, 'viw', '<a href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_Documentation['FineId'] . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza" class="glyphicon glyphicon-eye-open fa-fw tooltip-r" style="margin-top: 0.4rem;"></span></a>');?>
                        </div>
                        <div class="col-sm-12 collapse table_caption_H table_caption_error" id="collapse_<?= $i; ?>" aria-labelledby="heading_<?= $i ?>" data-parent="#accordion" aria-expanded="false" style="height:0;color:white;">
                        	<?= $r_Documentation['SendError'];  ?>
                        </div>
            		</div>
            		<div class="clean_row HSpace4"></div>
    			<?php endwhile; ?>
    			</div>
    			
                <?php if($hasPrivateKey && $r_Customer['EnableINIPECNotification'] == 1): ?>
                    <div class="BoxRowLabel col-sm-3">
                    	Password per la firma
                    	<i data-targetfield="SignaturePwd" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="BoxRowCaption col-sm-9">
                    	<input value="<?= $SignaturePwd ?>" maxlength="100" class="form-control frm_field_string" type="password" name="SignaturePwd" id="SignaturePwd">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                <?php endif; ?>
                
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	<div style="padding-top:2rem;">
            	    	<?= ChkButton($aUserButton, 'prn','<button type="submit" id="print" class="btn btn-success" style="width:18rem;">Anteprima di stampa</button>'); ?>
    					<?= ChkButton($aUserButton, 'prn','<span id="SPAN_ultimate"><input type="checkbox" value="1" name="ultimate" id="ultimate" style="margin-left:5rem;"> Definitivo</span>'); ?>
        	    	</div>
                	<div style="position:absolute; bottom:5px;font-size:1.2rem;color:#fff;width:405px;text-align: left;line-height:2.5rem">
                 		<div style="width:200px;">
							<i class="fas fa-hourglass-end" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Notifica scaduta
                		</div>
                		<div style="width:200px;">
                			<i class="fas fa-paper-plane" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Invio fallito
                		</div>
                		<div style="width:200px;">
                			<i class="fas fa-exclamation-circle" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Mancata consegna
                		</div>
                	</div>
                </div>
        	<?php else: ?>
                <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente
                </div>
        	<?php endif; ?>
        <?php endif; ?>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        
    	var $myGroup = $('.accordion-group');
    	$myGroup.on('show.bs.collapse','.collapse', function() {
    	    $myGroup.find('.collapse.in').collapse('hide');
    	});
    	
        $('#f_search').submit(function () {
            $("#search").prop('disabled', true);
            $('#search i').toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
        });

        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_recreatedynamic_pecnotification").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_recreatedynamic_pecnotification").trigger( "check" );
        });

        $('.showpassword').click(function(){
            var field = '#' + $(this).data('targetfield');
            if('password' == $(field).attr('type')){
                 $(field).prop('type', 'text');
            }else{
                 $(field).prop('type', 'password');
            }
            $(this).toggleClass("glyphicon-eye-open glyphicon-eye-close");
        });

        $(".showpassword").hover(function(){
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("cursor","");
        });

        $("#f_recreatedynamic_pecnotification").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#print').prop('disabled', false);
        	else
        		$('#print').prop('disabled', true);
        });

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error').css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).not('.table_caption_error').css("background-color", "");
      	});

        $('#ultimate').click(function(){
            if($('#ultimate').is(":checked")) {
                $('#print').html('Stampa definitiva');
                $('#print').removeClass( "btn-success" ).addClass( "btn-warning" );
            }else{
                $('#print').html('Anteprima di stampa');
                $('#print').removeClass( "btn-warning" ).addClass( "btn-success" );
            }
        });

        $('#f_recreatedynamic_pecnotification').submit(function(){
			if($('#ultimate').is(":checked")) {
				if(confirm("Si stanno per ricreare le notifiche PEC in maniera definitiva. Continuare?")){
					$('#SPAN_ultimate').hide();
					$('#print').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
					$('#print').prop('disabled', true);
				} else {
                	e.preventDefault();
                	return false;
				}
			}
        });
        
    });
</script>

<?php
include (INC . "/footer.php");
