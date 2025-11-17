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

//frm_createdynamic_pec_exe
$CreationDate = CheckValue('CreationDate','s') != '' ? CheckValue('CreationDate','s') : date('d/m/Y');
$ChiefControllerId = CheckValue('ChiefControllerId','n');
$SignaturePwd = CheckValue('SignaturePwd', 's');
$Search_EscludiRinotifichePEC = CheckValue('Search_EscludiRinotifichePEC', 'n') != '' ? CheckValue('Search_EscludiRinotifichePEC', 'n') : ($r_Customer['ManagePEC'] == 1 ? 1 : 0);

$RecordLimit = CheckValue('RecordLimit','n') == 0 ? 5 : CheckValue('RecordLimit','n');
$PageTitle = CheckValue('PageTitle','s');

$PreviousId = 0;

$str_Where = '1=1';

$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year']." AND RuleTypeId = ".$_SESSION['ruletypeid']." AND ControllerId IS NOT NULL";

/*Modificato a seguito Bug2147 - Decidiamo questo perché si è visto che, contrariamente a quanto ipotizzato in fase di progettazione della funzione di firma, gli enti anche grandi sono soliti indiviudare un solo agente firmatario per le PEC invece di fare che ognuno firma i suoi*/
//$str_Where .= " AND ((FineChiefControllerId=$ChiefControllerId AND StatusTypeId=14) OR (StatusTypeId=10))";
$str_Where .= " AND ((StatusTypeId=14) OR (StatusTypeId=10))";

//modo per dire che abbia almeno un trasgressore con PEC???
//TODO Questa parte di query genera un problema in sviluppo che fa caricare all'infinito (o quasi) la ricerca.
//Considerazioni: Sarebbe corretto aggiungere il cityid come filtro?
$str_Where .= ($s_TypePlate=='N') ? " AND Id IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)" : "";
//Si potrebbe risolvere sostituendo la str_Where appena sopra con quella di seguito
//$str_Where .= ($s_TypePlate=='N') ? " AND LENGTH(COALESCE(PEC,''))>0" : "";

$str_WHhereControllers = "CityId='".$_SESSION['cityid']."' AND ('".DateInDB($CreationDate)."' >= FromDate OR FromDate IS NULL) AND ('".DateInDB($CreationDate)."' <= ToDate OR ToDate IS NULL) AND Disabled=0 AND ChiefController=1";
if ($r_Customer['EnableINIPECDigitalSignature'] == 1) 
    $str_WHhereControllers .= " AND FineDigitalSign=1";

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
if ($Search_HasKindSendDate > 0) {
    $str_Where .= " AND KindSendDate IS NOT NULL";
}
if ($Search_Article != ""){
    $str_Where .= " AND ArticleId=".$Search_Article;
}
if ($Search_Detector > 0){
    $str_Where .= " AND DetectorId=$Search_Detector";
}

if ($Search_Plate != ""){
    $str_Where .= " AND VehiclePlate='$Search_Plate'";
}

if ($Search_EscludiRinotifichePEC != ''){
    //deve escludere i record che sono di rinotifica
    // il cui invio precedente via PEC era falito
    // scrivo condizione di selezione e poi la nego
    // BUG 1617 - cercare la notifica PEC fallita più recente
    $str_Where .= $Search_EscludiRinotifichePEC == 1 ? 
    " AND NOT(PreviousId > 0 AND length(COALESCE(PEC,''))>0 AND PreviousPECTrespasserId is not null AND (PreviousAnomaly = 'S' OR COALESCE(PreviousSendError,'') != '') )"
        : "";
        //DA INTEGRARE
        /*
        AND NOT( F.PreviousId>0 AND F.ProtocolId IN(
            SELECT FMulti.ProtocolId FROM (
                SELECT F2.ProtocolId,COUNT(F2.Id)
                FROM Fine F2
                JOIN FineHistory FH6 ON F2.Id = FH6.FineId AND FH6.NotificationTypeId = 6
                WHERE F2.CityId='D711' AND F2.Code=Code AND F2.ProtocolId=ProtocolId AND ((FH6.ResultId > 9 and FH6.ResultId < 21) or FH6.ResultId=23)
                GROUP BY F2.ProtocolId HAVING COUNT(F2.Id) >= 1)
            AS FMulti))";
            */
}

$strOrder = "FineDate ASC, FineTime ASC, Id ASC";
//echo $str_Where;

if($RecordLimit>0){
    $strOrder .= " LIMIT $RecordLimit";
}
if ($s_TypePlate != ""){
    $table_rows = $rs->Select('V_ViolationAll',$str_Where, $strOrder);
   // echo $str_Where;
    $RowNumber = mysqli_num_rows($table_rows);
}

//BUG2530
//$hasPrivateKey=hasPrivateKey($_SESSION['userid']);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_search" action="frm_createdynamic_pec.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		
		<?php /*//BUG2530 if(!$hasPrivateKey): ?>
		<?php if(!$hasPrivateKey): ?>
			<div class="table_caption_H col-sm-12 alert-warning">
                <i class="fas fa-fw fa-warning col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;Attenzione, chiave privata non trovata per l'utente in sessione, i verbali verranno salvati come non firmati: <?= $_SESSION['userid'].CERT_EXTENSION; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
		<?php endif; ?>
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
            	<?= CreateArraySelect(array(5,25,50,100,200), false, 'RecordLimit', 'RecordLimit', $RecordLimit, true) ?>
            </div>

            <div class="col-sm-1 BoxRowLabel">
                Accertatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectQuery("SELECT Id, CONCAT(Code,' - ',Name) AS ControllerName FROM Controller WHERE CityId='".$_SESSION['cityid']."' AND Disabled=0 ORDER BY Name","Search_ControllerId","Id","ControllerName",$Search_ControllerId,false); ?> 
            </div>
            <div class="col-sm-1 BoxRowCaption">
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
            	<?= CreateArraySelect(array('N' => 'Nazionali', 'F' => 'Estere'), true, 'TypePlate', 'TypePlate', $s_TypePlate) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Tipo contravventore
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array('D' => 'Ditta', 'P' => 'Persona fisica'), true, 'Search_Genre', 'Search_Genre', $Search_Genre, false, null, null, 'Entrambi') ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1 AND RuleTypeId={$_SESSION['ruletypeid']}","Id","Search_Violation","Id","Title",$Search_Violation,false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Rilevatore
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelectQuery("SELECT Id,CONCAT(progressive, ' - ', COALESCE(NULLIF(Ratification, ''), TitleIta)) AS TitleIta FROM Detector WHERE CityId='{$_SESSION['cityid']}' ORDER BY progressive", 'Search_Detector', 'Id', 'TitleIta', $Search_Detector, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Articolo
            </div>    
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelectQuery("SELECT Id,CONCAT_WS(' ',Article,Paragraph,Letter)Article FROM Article WHERE CityId='".$_SESSION['cityid']."' ORDER BY Article ASC", 'Search_Article', 'Id', 'Article', $Search_Article, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control " type="text" value="<?= $Search_Plate; ?>" name="Search_Plate" id="Search_Plate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo inviti in AG
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" value="1" id="Search_HasKindSendDate" name="Search_HasKindSendDate" <?= ChkCheckButton($Search_HasKindSendDate > 0 ? 1 : 0); ?>>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Escludi rinofiche per PEC non not.
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<!-- Input per checkbox vuota -->
    			<input value="0" type="hidden" name=Search_EscludiRinotifichePEC> 
            	<input name="Search_EscludiRinotifichePEC" id="Search_EscludiRinotifichePEC" type="checkbox" value="1" <?= ChkCheckButton($Search_EscludiRinotifichePEC); ?>/>
            </div>
    		<div class="col-sm-1 BoxRowLabel font_small">
                Data Verbalizzazione
            </div>
    		<div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date txt-warning" type="text" id="CreationDate" name="CreationDate" value="<?= $CreationDate; ?>">
            </div>
            
            <div class="col-sm-1 BoxRowLabel">
            	Firmatario
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE $str_WHhereControllers ORDER BY CAST(Code AS UNSIGNED)","ChiefControllerId","Id","Name",$ChiefControllerId,false,"","txt-warning"); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
            	Firma abilitata
            </div>
            <div class="col-sm-1 BoxRowCaption text-center">
            	<i class="<?= $r_Customer['EnableINIPECDigitalSignature'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger"; ?>" style="font-size: 1.8rem;"></i>
            </div>

        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:6.8rem">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:100%"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_createdynamicpec" action="frm_createdynamic_pec_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="TypePlate" value="<?= $s_TypePlate; ?>">
        <input type="hidden" name="ChiefControllerId" value="<?= $ChiefControllerId; ?>">
        <input type="hidden" name="CreationDate" value="<?= $CreationDate; ?>">
        <input type="hidden" name="Search_EscludiRinotifichePEC" value="<?= $Search_EscludiRinotifichePEC; ?>">
    
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked/></div>
        <div class="table_label_H col-sm-1">Info</div>
        <div class="table_label_H col-sm-1">ID</div>
        <div class="table_label_H col-sm-2">Codice</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Articolo</div>
        <div class="table_label_H col-sm-2">Targa</div>
        <div class="table_label_H col-sm-1">Nazione</div>
        <div class="table_label_H col-sm-1"></div>
        
        <?php if($s_TypePlate=="" || $ChiefControllerId==""): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere nazionalità targa e firmatario
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php $i=1; ?>
        		<?php while ($table_row = mysqli_fetch_assoc($table_rows)): ?>
        			<?php 
        			if($table_row['PreviousId'] > 0){
        			    $rs_PreviousFine=$rs->Select('Fine', "Id={$table_row['PreviousId']}");
        			    $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);
        			} else $r_PreviousFine = null;
        			$rs_Trespasser = $rs->Select('Trespasser', 'Id='.$table_row['TrespasserId']);
        			$r_Trespasser = mysqli_fetch_array($rs_Trespasser);
        			
        			if($table_row['KindSendDate'] != ''){
        			    $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Invito in AG"></i>';
        			} else $FineIcon = $a_FineTypeId[$table_row['FineTypeId']];
        			?>
        		
        			<div class="clean_row HSpace4"></div>
        			
                    <div class="tableRow">
	          			<div class="col-sm-1" style="text-align:center;padding:0">
	            			<div class="table_caption_button col-sm-6" style="text-align:center;">
	            			<?php if ($PreviousId != $table_row['Id']): ?>
	            				<input type="checkbox" name="checkbox[]" value="<?= $table_row['Id']; ?>" checked />
	            			<?php endif; ?>
            				</div>
	            			<div class="table_caption_H col-sm-6" style="text-align:center;">
                				<?= $i++; ?>
            				</div>
        				</div>
                		<?php $PreviousId = $table_row['Id']; ?>
                        <div class="table_caption_H col-sm-1 text-center">
                        	<?php if($r_PreviousFine && $r_PreviousFine['StatusTypeId'] == 34 && $r_PreviousFine['KindSendDate'] != ''): ?>
                			<i class="fas fa-mail-bulk tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Creazione verbale da invito in AG" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;
                			<?php endif; ?>
                        	<?php if($r_Trespasser): ?>
                			<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= trim(StringOutDB($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name'])) ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;
                			<?php endif; ?>
                			<?php if(!empty($table_row['PEC'])): ?>
                			<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= StringOutDB($table_row['PEC']); ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>
                			<?php endif; ?>
                			<?php if(empty($table_row['ControllerId']) || $table_row['ControllerId'] == 0):?>
                			<i class="fas fa-info-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Accertatore mancante" style="margin-top:0.2rem;font-size:1.8rem; color:red"></i>
                			<?php endif; ?>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                        	<?= $FineIcon . $table_row['Id']; ?>
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
                    	<div class="table_caption_H col-sm-1">
                    		<?= $table_row['Article']; ?> <?= $table_row['Paragraph']; ?> <?= $table_row['Letter']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $table_row['VehiclePlate']; ?>
                        	<i class="<?= $aVehicleTypeId[$table_row['VehicleTypeId']]; ?>" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                    		<?= $table_row['VehicleCountry']; ?>
                		</div>
                        <div class="table_caption_button  col-sm-1">
                        	<?= ChkButton($aUserButton, 'viw','<a href="mgmt_violation_viw.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open"></span></a>'); ?>
                        	<?= ChkButton($aUserButton, 'upd','<a href="mgmt_violation_upd.php'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-pencil"></span></a>'); ?>
                    	</div>
                	</div>
            	<?php endwhile; ?>
        	
        		<div class="clean_row HSpace4"></div>
        		
                <?php /*//BUG2530 if($hasPrivateKey && $r_Customer['EnableINIPECDigitalSignature'] == 1): ?>
                    <div class="BoxRowLabel col-sm-3">
                    	Password per la firma
                    	<i data-targetfield="SignaturePwd" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="BoxRowCaption col-sm-9">
                    	<input value="<?= $SignaturePwd; ?>" maxlength="100" class="form-control frm_field_string" type="password" name="SignaturePwd" id="SignaturePwd">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                <?php endif; */ ?>
        		
        		<div class="col-sm-3 BoxRowLabel">
                    Data Verbalizzazione
                </div>
        		<div class="col-sm-2 BoxRowCaption">
        			<?= $CreationDate; ?>
                </div>
                
        		<?php 
        		$rs_ChiefController = $rs->SelectQuery("SELECT CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE Id=$ChiefControllerId");
        		$r_ChiefController = mysqli_fetch_array($rs_ChiefController);
        		?>
                <div class="col-sm-3 BoxRowLabel">
                    Verbalizzante
                </div>
                <div class="col-sm-4 BoxRowCaption">
                	<?= $r_ChiefController['Name'] ?>
                </div>
                
                <div class="col-sm-12 BoxRowCaption" style="height:auto">
        			<div class="table_caption_H col-sm-7 alert-warning pull-right" style="height:auto">
                        <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>
                        <ul style="margin:0;padding-left:3.5rem;">
                            <li>In caso di prima stampa: se il dato del verbalizzante è già registrato nel verbale, quest\'ultimo avrà priorità sulla tendina sovrastante.</li>
                            <li>In caso di rinotifica: il verbalizzante mostrato nella stampa sarà quello specificato nella tendina sovrastante.
                        </ul>
                    </div>
                </div>
                
                <div class="clean_row HSpace4"></div>
        	
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	<div style="padding-top:2rem;">
            	    	<?= ChkButton($aUserButton, 'prn','<button type="submit" id="print" class="btn btn-success" style="width:16rem;">Anteprima di stampa</button>'); ?>
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
		$('#CreationDate').on('change', function(){
			var date = $(this).val();
			var EnableINIPECDigitalSignature = <?= $r_Customer['EnableINIPECDigitalSignature'] == 1 ? 'true' : 'false'; ?>;
			
	        try { $.datepicker.parseDate('dd/mm/yy', date); } catch (error) {date = null;}

			if(date){
				$.ajax({
			        url: 'ajax/ajx_getControllersByValidityDate.php',
			        type: 'POST',
			        dataType: 'json',
			        data: {Date: date, FineDigitalSign: EnableINIPECDigitalSignature},
			        ContentType: "application/json; charset=UTF-8",
			        success: function (data) {
			        	$('#ChiefControllerId').html('');
			        	$('#ChiefControllerId').append('<option></option>');
			        	$.each(data.Result, function(i, value) {
				        	var name = [value.code, value.qualification, value.name];
			        		$('#ChiefControllerId').append($('<option>', {
			        		    value: value.id,
			        		    text: name.join(' ')
			        		}));
			        	});
			        },
			        error: function (data) {
			            console.log(data);
			            alert("error: " + data.responseText);
			        }
			    });
			}
		});
		
		$('#Search_EscludiRinotifichePEC').on('change', function() {
            $('#Search_EscludiRinotifichePEC').not(this).prop('checked', false);  
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

        $('#f_search').bootstrapValidator({
            live: 'disabled',
            fields: {
                CreationDate:{
                    validators: {
                        notEmpty: {message: 'Richiesto'},
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    }
                },
                ChiefControllerId: {
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },
            }
        });

        $('#f_createdynamicpec').on('submit', function(e){
			if($('#ultimate').is(":checked")) {
				if(confirm("Si stanno per creare i verbali PEC in maniera definitiva. Continuare?")){
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
