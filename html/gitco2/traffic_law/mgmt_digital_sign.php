<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_message.php");

include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$messageObject=new CLS_MESSAGE();
$rs=new CLS_DB();
//Se l'utente loggato non ha un firmatario associato blocca l'esecuzione
$hasPrivateKey=hasPrivateKey($_SESSION['userid']);
$error=false;
if(!$r_Customer['EnableSignAll'])
  $messageObject->addError('L\' ente non ha abilitato la firma digitale di tutti i verbali, modificare il parametro "Abilita la firma digitale di tutti i verbali" in gestione ente');
else if ($error=($_SESSION['controllerid'] == 0 && $_SESSION['usertype'] != 2))
    $messageObject->addError('L\'utente in uso non dispone di un firmatario associato o dei privilegi necessari, pertanto non è possibile usufruire di questa procedura.');
 else {
    $UserControllerId = $_SESSION['controllerid'];
    $rs_Controller = $rs->Select("Controller", "Id=".$UserControllerId);
    $r_Controller = mysqli_fetch_array($rs_Controller);
    
    if (mysqli_num_rows($rs_Controller) > 0){
        if ($r_Controller['FineDigitalSign'] == 0 || $r_Controller['Disabled'] == 1){
            $error=true;
            $messageObject->addError('Il firmatario associato all\'utente in uso non dispone di abilitazione alla firma digitale o risulta disabilitato, pertanto non è possibile usufruire di questa procedura.');
        } else if(!$hasPrivateKey)
            $messageObject->addWarning("Attenzione, chiave privata non trovata per l'utente in sessione, le notifiche verranno salvate come non firmate: {$_SESSION['userid']}.".CERT_EXTENSION);
        $SignController = (isset($r_Controller['Qualification']) ? $r_Controller['Qualification'].' ' : '').$r_Controller['Name'];
    } else 
        $messageObject->addError('Il firmatario associato all\'utente in uso non è stato trovato negli accertatori registrati, assicurarsi che il codice sia corretto.');
}
$message=$messageObject->getMessagesString();
echo $str_out;
echo $messageObject->getMessagesString();

if($error==true)
    die;

$CreationDate = CheckValue('CreationDate','s') != '' ? CheckValue('CreationDate','s') : date('d/m/Y');
$ChiefControllerId = CheckValue('ChiefControllerId','n');
$RecordLimit = CheckValue('RecordLimit','n') == 0 ? 5 : CheckValue('RecordLimit','n');
$PageTitle = CheckValue('PageTitle','s');
$Search_ControllerId = CheckValue('Search_ControllerId','s');
$Search_Genre = CheckValue('Search_Genre','s');
$Search_FromFineDate = CheckValue('Search_FromFineDate','s');
$Search_ToFineDate = CheckValue('Search_ToFineDate','s');
$Search_Violation = CheckValue('Search_Violation','n');
$str_Where = '1=1';
$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND ControllerId IS NOT NULL";
$str_Where .= " AND FineChiefControllerId=$UserControllerId AND StatusTypeId in(15,8) ";
$str_Where .= ($s_TypePlate=='N') ? " AND Id not IN (SELECT FineId FROM FineDocumentation fd WHERE fd.FineId=v.Id and fd.DocumentationTypeId=3) AND Id IN (SELECT FineId FROM FineDocumentation fd WHERE fd.FineId=v.Id and fd.DocumentationTypeId=2)" : "";
$str_Where .=" and Id not in (select FineId from FineHistory where NotificationTypeId=13 and CityId='{$_SESSION['cityid']}')";

if ($s_TypePlate == "N") {
  $str_Where .= " AND CountryId='Z000'";
} else {
  $str_Where .= " AND CountryId!='Z000'";
}
if($Search_Genre != ""){
    if ($Search_Genre == "D"){
        $str_Where .= " AND Genre='D' ";
    } else if($Search_Genre == "P") {
        $str_Where .= " AND Genre!='D' ";
    }
}
if ($Search_ControllerId != ''){
  $str_Where .= " AND ControllerId=".$Search_ControllerId;
}
if ($Search_FromFineDate != "") {
  $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
}
if ($Search_ToFineDate != "") {
  $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
}
if ($Search_Violation != "" && $Search_Violation != 0) {
  $str_Where .= " AND ViolationTypeId=".$Search_Violation;
}

$strOrder = "Id ASC, FineDate ASC, FineTime ASC";
if($RecordLimit>0){
  $strOrder .= " LIMIT $RecordLimit";
}
if ($s_TypePlate != ""){
  $table_rows = $rs->Select('V_ViolationAll v',$str_Where, $strOrder);
  $RowNumber = mysqli_num_rows($table_rows);
}
echo $str_out;
$PreviousId=0;
?>

<div class="row-fluid">
	<form id="f_search" action="mgmt_digital_sign.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <div class="col-sm-11">
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
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false); ?>
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
            <div class="col-sm-2 BoxRowLabel">
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
            	Firmatario
            </div>
            <div class="col-sm-3 BoxRowCaption table_caption_I">
            	<?= $SignController ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
            	Firma abilitata
            </div>
            <div class="col-sm-1 BoxRowCaption text-center">
            	<i class="<?= $r_Customer['EnableINIPECDigitalSignature'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger"; ?>" style="font-size: 1.8rem;"></i>
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_createdynamicpec" action="mgmt_digital_sign_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="TypePlate" value="<?= $s_TypePlate; ?>">
        <input type="hidden" name="ControllerId" value="<?= $Search_ControllerId; ?>">
        <input type="hidden" name="FineId">
        <div class="table_label_H col-sm-1">Info</div>
        <div class="table_label_H col-sm-1">ID</div>
        <div class="table_label_H col-sm-2">Codice</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-2">Targa</div>
        <div class="table_label_H col-sm-2">Nazione</div>
        <div class="table_label_H col-sm-2"></div>
        
        <?php if($s_TypePlate==""): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere nazionalità targa
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>

        		<?php while ($table_row = mysqli_fetch_assoc($table_rows)): ?>
        			<?php if($table_row['Id']!=$PreviousId): ?>
        			<?php 
        			  $PreviousId = $table_row['Id'];
            			$pecs = $trespassers = '';
            			$rs_Trespasser = $rs->Select('V_FineTrespasser', 'FineId='.$table_row['Id']);
            			$count=0;
            			while($r_Trespasser = mysqli_fetch_assoc($rs_Trespasser)){
            			  
            			  
            			  if($count==0){
            			    $trespassers= trim(StringOutDB($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name']));
            			    $pecs= StringOutDB($table_row['PEC']);
            			  } else{
            			    $trespassers .= ', ' .PHP_EOL.trim(StringOutDB($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name']));
            			    $pecs .= ' ' .PHP_EOL.StringOutDB($table_row['PEC']);
            			  }
            			  $count=$count+1;
            			}
            			?>
        		
        			<div class="clean_row HSpace4"></div>
        			
                    <div class="tableRow">
                        <div class="table_caption_H col-sm-1 text-center">
                			<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= $trespassers ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;
                			<?php if(!empty($pecs)): ?>
                			<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= $pecs ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>
                			<?php endif; ?>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                        	<?= $table_row['Id']; ?>
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
                        <div class="table_caption_button  col-sm-2">
                             <a onClick="submit(<?=$table_row['Id']?>)"><span class="glyphicon glyphicon-pencil"></span></a>
                             <a href="mgmt_violation_viw.php<?=$str_GET_Parameter?>&Id=<?=$table_row['Id'] ?>"><span class="glyphicon glyphicon-eye-open"></span></a>
                        
                    	</div>
                	</div>
            		<?php endif; ?>
            	<?php endwhile; ?>
        		<div class="clean_row HSpace4"></div>        		
                <?php if($r_Customer['CityUnion']>1 || $_SESSION['usertype']==3 || $_SESSION['usertype']==2): ?>
                	<div class="BoxRowCaption col-sm-12"></div>
                <?php else: ?>
                	<?php if($r_Customer['ChiefControllerList']): ?>
	                    <div class="col-sm-3 BoxRowLabel">
                            Verbalizzante
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <?= CreateSelectConcat("
                                SELECT Id, CONCAT(Code,' ',Name) AS Name 
                                FROM Controller WHERE CityId='".$_SESSION['cityid']."' 
                                ORDER BY Name","ChiefControllerId","Id","Name",$ChiefControllerId,false,'',"frm_field_required"); ?>
                        </div>
	                    <div class="col-sm-6 BoxRowCaption">
                        </div>	
                	<?php else: ?>
                		<?php 
                		$rs_ChiefController = $rs->Select('Controller',"CityId='".$_SESSION['cityid']."' AND Sign !='' AND Disabled=0");
                		$r_ChiefController = mysqli_fetch_array($rs_ChiefController);
                		?>
	                    <div class="col-sm-3 BoxRowLabel">
                            Verbalizzante
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select class="frm_field_required form-control" name="ChiefControllerId">
                                <option value="<?= $r_ChiefController['Id']; ?>"><?= $r_ChiefController['Name'].' Matricola '.$r_ChiefController['Code']; ?></option>
                            </select>
                        </div>
	                    <div class="col-sm-2 BoxRowCaption">
                        </div>	
                	<?php endif; ?>
                <?php endif; ?>
                
                <div class="clean_row HSpace4"></div>
                
                <?php if($hasPrivateKey && $r_Customer['EnableINIPECDigitalSignature'] == 1): ?>
                    <div class="BoxRowLabel col-sm-3">
                    	Password per la firma
                    	<i data-targetfield="SignaturePwd" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="BoxRowCaption col-sm-9">
                    	<input maxlength="100" class="form-control frm_field_string" type="password" name="SignaturePwd" id="SignaturePwd">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                <?php endif; ?>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente
                </div>
        	<?php endif; ?>
        <?php endif; ?>

	</form>
</div>

<script type="text/javascript">
function submit(fineId){
	document.forms[1].FineId.value=fineId;
	document.forms[1].submit();
}
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
                $('#print').html('Stampa definitiva');
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
        });
	});
</script>
<?php
include(INC."/footer.php");