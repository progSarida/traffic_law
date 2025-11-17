<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

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

$RecordLimit = CheckValue('RecordLimit','n') == 0 ? 5 : CheckValue('RecordLimit','n');
$PageTitle = CheckValue('PageTitle','s');
$Search_ControllerId = CheckValue('Search_ControllerId','s');
$Search_Genre = CheckValue('Search_Genre','s');
$Search_FromFineDate = CheckValue('Search_FromFineDate','s');
$Search_ToFineDate = CheckValue('Search_ToFineDate','s');
$Search_Violation = CheckValue('Search_Violation','n');

//Parametri di ritorno da createdynamic_pecnotification
$FixedRecipient = CheckValue('FixedRecipient', 's');
$SignaturePwd = CheckValue('SignaturePwd', 's');

$PreviousId = 0;

$str_Where = '1=1';

$str_Where .= " AND VV.CityId='".$_SESSION['cityid']."' AND VV.ProtocolYear=".$_SESSION['year']." AND VV.RuleTypeId = ".$_SESSION['ruletypeid']." AND FH.NotificationTypeId=15";
$str_Where .= " AND ((VV.FineChiefControllerId=$UserControllerId AND VV.StatusTypeId=12 AND VV.FineTypeId IN(3,4)) OR (VV.StatusTypeId=12))";
//modo per dire che abbia almeno un trasgressore con PEC???
//TODO Questa parte di query genera un problema in sviluppo che fa caricare all'infinito (o quasi) la ricerca.
//Considerazioni: Sarebbe corretto aggiungere il cityid come filtro?
$str_Where .= ($s_TypePlate=='N') ? " AND VV.Id IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)" : "";
//Si potrebbe risolvere sostituendo la str_Where appena sopra con quella di seguito
//$str_Where .= ($s_TypePlate=='N') ? " AND LENGTH(COALESCE(PEC,''))>0" : "";

if ($s_TypePlate == "N") {
    $str_Where .= " AND VV.CountryId='Z000'";
} else {
    $str_Where .= " AND VV.CountryId!='Z000'";
}
if($Search_Genre != ""){
    if ($Search_Genre == "D"){
        $str_Where .= " AND VV.Genre='D' ";
    } else if ($Search_Genre == "P") {
        $str_Where .= " AND VV.Genre!='D' ";
    }
}
if ($Search_ControllerId != ''){
    $str_Where .= " AND VV.ControllerId=".$Search_ControllerId;
}
if ($Search_FromFineDate != "") {
    $str_Where .= " AND VV.FineDate>='".DateInDB($Search_FromFineDate)."'";
}
if ($Search_ToFineDate != "") {
    $str_Where .= " AND VV.FineDate<='".DateInDB($Search_ToFineDate)."'";
}
if ($Search_Violation != "" && $Search_Violation != 0) {
    $str_Where .= " AND VV.ViolationTypeId=".$Search_Violation;
}

$strOrder = "VV.FineDate ASC, VV.FineTime ASC, VV.Id ASC";
if($RecordLimit>0){
    $strOrder .= " LIMIT $RecordLimit";
}

if ($s_TypePlate != ""){
    $table_rows = $rs->SelectQuery("
        SELECT * 
        FROM V_ViolationAll VV  
        JOIN FineHistory FH ON VV.Id=FH.FineId AND VV.TrespasserId=FH.TrespasserId 
        WHERE $str_Where ORDER BY $strOrder");
    $RowNumber = mysqli_num_rows($table_rows);
}

//BUG 2530
//$hasPrivateKey=hasPrivateKey($_SESSION['userid']);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_search" action="frm_createdynamic_pecnotification.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		
		<?php if($r_Customer['EnableINIPECNotification'] != 1): ?>
			<div class="table_caption_H col-sm-12 alert-warning">
                <i class="fas fa-fw fa-warning col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;Abilita la creazione e la firma digitale della relata di notifica dei verbali con contestuale invio degli atti tramite PEC" non è abilitato, gli invii verranno effettuati senza la creazione di notifiche e verranno allegati solo i verbali.
            </div>
            
            <div class="clean_row HSpace4"></div>
		<?php endif; ?>
		<?php /*//BUG2530 if(!$hasPrivateKey): ?>
			<div class="table_caption_H col-sm-12 alert-warning">
                <i class="fas fa-fw fa-warning col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;Attenzione, chiave privata non trovata per l'utente in sessione, le notifiche verranno salvate come non firmate: <?= $_SESSION['userid'].CERT_EXTENSION; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
		<?php endif;*/ ?>
		
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= $_SESSION['ruletypetitle']; ?>
            </div>
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="RecordLimit" id="RecordLimit">
                    <option value="5"<?= $RecordLimit == 5 ? ' selected' : ''; ?>>5</option>
                    <option value="25"<?= $RecordLimit == 25 ? ' selected' : ''; ?>>25</option>
                    <option value="50"<?= $RecordLimit == 50 ? ' selected' : ''; ?>>50</option>
                    <option value="100"<?= $RecordLimit == 100 ? ' selected' : ''; ?>>100</option>
                    <option value="200"<?= $RecordLimit == 200 ? ' selected' : ''; ?>>200</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Accertatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectQuery("SELECT Id, CONCAT(Code,' - ',Name) AS ControllerName FROM Controller WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 ORDER BY Name","Search_ControllerId","Id","ControllerName",$Search_ControllerId,false); ?> 
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate" id="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate" id="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
            </div>
                    
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N" <?= $s_SelPlateN ?>>Nazionali</option>
                    <option value="F" <?= $s_SelPlateF ?>>Estere</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Tipo contravventore
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="Search_Genre" id="Search_Genre">
                    <option value="">Entrambi</option>
                    <option value="D"<?= $Search_Genre == 'D' ? ' selected' : ''; ?>>Ditta</option>
                    <option value="P"<?= $Search_Genre == 'P' ? ' selected' : ''; ?>>Persona fisica</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1 AND RuleTypeId={$_SESSION['ruletypeid']}","Id","Search_Violation","Id","Title",$Search_Violation,false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
            	Firmatario
            </div>
            <div class="col-sm-2 BoxRowCaption table_caption_I">
            	<?= $SignController ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
            	Notifica e firma abilitata
            </div>
            <div class="col-sm-1 BoxRowCaption text-center">
            	<i class="<?= $r_Customer['EnableINIPECNotification'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger"; ?>" style="font-size: 1.8rem;"></i>
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_createdynamicpec" action="frm_createdynamic_pecnotification_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="TypePlate" value="<?= $s_TypePlate; ?>">
    
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked/></div>
        <div class="table_label_H col-sm-1">Info</div>
        <div class="table_label_H col-sm-1">ID</div>
        <div class="table_label_H col-sm-2">Codice</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-2">Targa</div>
        <div class="table_label_H col-sm-2">Nazione</div>
        <div class="table_label_H col-sm-1"></div>
        
        <?php if($s_TypePlate==""): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere nazionalità targa
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php $i=1; ?>
        		<?php while ($table_row = mysqli_fetch_assoc($table_rows)): ?>
        			<?php 
        			$rs_Trespasser = $rs->Select('Trespasser', 'Id='.$table_row['TrespasserId']);
        			$r_Trespasser = mysqli_fetch_assoc($rs_Trespasser);
        			?>
        		
        			<div class="clean_row HSpace4"></div>
        			
                    <div class="tableRow">
	          			<div class="col-sm-1" style="text-align:center;padding:0">
	            			<div class="table_caption_button col-sm-6" style="text-align:center;">
	            			<?php if ($PreviousId != $table_row['FineId']): ?>
            					<input type="checkbox" name="checkbox[]" value="<?= $table_row['FineId']; ?>" checked />
	            			<?php endif; ?>
            				</div>
	            			<div class="table_caption_H col-sm-6" style="text-align:center;">
                				<?= $i++; ?>
            				</div>
        				</div>
                		<?php $PreviousId = $table_row['FineId']; ?>
                        <div class="table_caption_H col-sm-1 text-center">
                			<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= trim(StringOutDB($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name'])) ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;
                			<?php if(!empty($table_row['PEC'])): ?>
                			<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= StringOutDB($table_row['PEC']); ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>
                			<?php endif; ?>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                        	<?= $table_row['FineId']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $table_row['Code']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                    		<?= DateOutDB($table_row['FineDate']); ?>
                		</div>
                        <div class="table_caption_H col-sm-1">
                        	<?= TimeOutDB($table_row['FineTime']); ?>
                    	</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $table_row['VehiclePlate']; ?>
                        	<i class="<?= $aVehicleTypeId[$table_row['VehicleTypeId']]; ?>" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i>
                    	</div>
                        <div class="table_caption_H col-sm-2">
                    		<?= $table_row['VehicleCountry']; ?>
                		</div>
                        <div class="table_caption_button  col-sm-1">
                        	<?= ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['FineId'].'"><span class="glyphicon glyphicon-eye-open"></span></a>'); ?>
                    	</div>
                	</div>
            	<?php endwhile; ?>

                <div class="clean_row HSpace4"></div>
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

        $('#TypePlate').change(function(){
            $('#f_search').submit();
        });

        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_createdynamicpec").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_createdynamicpec").trigger( "check" );
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

        $("#f_createdynamicpec").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#print').prop('disabled', false);
        	else
        		$('#print').prop('disabled', true);
        });
	    
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $('#ultimate').click(function(){
            if($('#ultimate').is(":checked")) {
                $('#print').html('Stampa e invio definitivo');
                $('#print').removeClass( "btn-success" ).addClass( "btn-warning" );
            }else{
                $('#print').html('Anteprima di stampa');
                $('#print').removeClass( "btn-warning" ).addClass( "btn-success" );
            }
        });

        $('#f_createdynamicpec').bootstrapValidator({
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
                CreationDate:{
                    validators: {
                        notEmpty: {message: 'Richiesto'},

                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    }
                },
            }
        }).on('success.form.bv', function(e){
			if($('#ultimate').is(":checked")) {
				if(confirm("Si stanno per creare le notifiche PEC in maniera definitiva. Continuare?")){
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
include(INC."/footer.php");
