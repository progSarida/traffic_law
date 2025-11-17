<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PageTitle = CheckValue('PageTitle','s');
$Search_ControllerId = CheckValue('Search_ControllerId', 's');
$Search_HasPEC = CheckValue('Search_HasPEC', 'n') != '' ? CheckValue('Search_HasPEC', 'n') : ($r_Customer['ManagePEC'] == 1 ? 1 : 0);
$RecordLimit = CheckValue('RecordLimit', 'n');
$Search_FromFineDate = CheckValue('Search_FromFineDate','s');
$Search_ToFineDate = CheckValue('Search_ToFineDate','s');
$Search_Violation = CheckValue('Search_Violation','n');
$Search_Genre = CheckValue('Search_Genre','s');

ini_set('max_execution_time', 0);

$RepeatedId = 0;

$str_Where = '1=1';

$str_Where .= " AND (StatusTypeId=10 OR StatusTypeId=14) AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year']." AND RuleTypeId=".$_SESSION['ruletypeid']." AND KindCreateDate IS NULL ". 
"AND PreviousId=0 AND FineTypeId <> 4";

if ($s_TypePlate == "N") {
    $str_Where .= " AND Amicable=1 AND CountryId='Z000'";
} else {
    $str_Where .= " AND CountryId!='Z000'";
}
if ($Search_HasPEC != ''){
    $str_Where .= $Search_HasPEC == 1 ? " AND Id NOT IN (SELECT Id FROM V_ViolationAll WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)" : "";
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

//Controlla se è abilitato il flag "Attiva l’invio dei verbali esteri tramite avviso bonario da spedire tramite posta ordinaria"
$ForeignKindFlag = $r_Customer['EnableForeignKindSending'] == 0 ? false : true;

//Aggancia il controllo sui giorni di elaborazione nella where e controlla che i parametri necessari per la procedura siano definiti
if ($s_TypePlate != ""){
    if ($s_TypePlate == 'N' || ($s_TypePlate != 'N' && $ForeignKindFlag)){
        $str_ErrorMsg = '';
        $ElaborationDays = 0;
        $PaymentDays = 0;
        
        $ElaborationDaysColumn = $s_TypePlate == 'N' ? 'ElaborationDaysNational' : 'ElaborationDaysForeign';
        $PaymentDaysColumn = $s_TypePlate == 'N' ? 'PaymentDaysNational' : 'PaymentDaysForeign';
        $rs_CustomerKindParams = $rs->SelectQuery("SELECT $ElaborationDaysColumn,$PaymentDaysColumn FROM Customer WHERE CityId='".$_SESSION['cityid']."'");
        
        if (mysqli_num_rows($rs_CustomerKindParams) > 0){
            $r_CustomerKindParams =  mysqli_fetch_assoc($rs_CustomerKindParams);
            $ElaborationDays = $r_CustomerKindParams[$ElaborationDaysColumn];
            $PaymentDays = $r_CustomerKindParams[$PaymentDaysColumn];
            
            if (empty($ElaborationDays) || $ElaborationDays <= 0){
                $str_ErrorMsg = '"Giorni elaborazione"';
            }
            if (empty($PaymentDays) || $PaymentDays <= 0){
                $str_ErrorMsg = '"Giorni pagamento"';
            }
        } else $str_ErrorMsg = '"Giorni elaborazione" e "Giorni pagamento"';
        
        if (empty($str_ErrorMsg)){
            $str_Where .= " AND DATE(NOW()) <= DATE_ADD(FineDate, INTERVAL $ElaborationDays DAY)";

            //echo $str_Where;
            $rs_Fine = $rs->Select('V_ViolationAll', $str_Where, 'FineDate ASC, FineTime ASC, Id ASC', ($RecordLimit != '' ? $RecordLimit : null));
            $RowNumber = mysqli_num_rows($rs_Fine);
        } else {
            echo $str_out;
            echo '<div class="alert alert-danger">E\' necessario definire '.$str_ErrorMsg.' su Ente/Procedure Ente per poter utilizzare questa procedura per la nazionalità specificata.</div>';
            DIE;
        }
    }
}

echo $str_out;
?>

<div class="row-fluid">
    <div class="col-sm-12 alert alert-danger" style="padding:5px; display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota bene:
                    <ul style="list-style-position: inside;">
                        <li>
                            Se i verbali prevedono pagamento tramite PagoPA con stampa di avviso di pagamento e l'ente gestisce il PagoPA su conto corrente postale, è necessario abilitare il bollettino nazionale PagoPA definendo il TD in Ente > Procedure Ente > Postalizzazione, 
                            altrimenti l'avviso di pagamento PagoPA non avrà la sezione dedicata al pagamento in poste.
                        </li>
                    </ul>
            	</li>
            </ul>
        </div>
    </div>

    <div class="clean_row HSpace4"></div>
	<form id="f_search" action="frm_senddynamic_kindfine.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <input type="hidden" name="Filter" value="1">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="RecordLimit" id="RecordLimit">
                    <option value="5"<?= $RecordLimit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="25"<?= $RecordLimit == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50"<?= $RecordLimit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100"<?= $RecordLimit == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200"<?= $RecordLimit == 200 ? 'selected' : ''; ?>>200</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1 AND RuleTypeId={$_SESSION['ruletypeid']}","Id","Search_Violation","Id","Title",$Search_Violation,false); ?>
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
                Escludi PEC
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<!-- Input per checkbox vuota -->
    			<input value="0" type="hidden" name="Search_HasPEC"> 
            	<input name="Search_HasPEC" type="checkbox" value="1" <?= ChkCheckButton($Search_HasPEC); ?>/>
            </div>
            <div class="col-sm-5 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_senddynamickindfine" action="frm_senddynamic_kindfine_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="TypePlate" value="<?= $s_TypePlate; ?>">
    
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked/></div>
        <div class="table_label_H col-sm-1">Info</div>
        <div class="table_label_H col-sm-1">ID</div>
        <div class="table_label_H col-sm-1">Targa</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Articolo</div>
        <div class="table_label_H col-sm-4">Descrizione</div>
        <div class="table_label_H col-sm-1"></div>
        
        <?php if($s_TypePlate==""): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere nazionalità targa
			</div>
		<?php elseif($s_TypePlate != "N" && !$ForeignKindFlag): ?>
        	<div class="table_caption_H col-sm-12 text-center text-danger">
				 L'ente non è abilitato all’invio dei verbali esteri tramite avviso bonario da spedire tramite posta ordinaria
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php $i=1; ?>
        		<?php while ($r_Fine = mysqli_fetch_assoc($rs_Fine)): ?>
        		
        			<?php
        			//Controlla che il record abbia tutti gli articoli con Amicable (gestito come invio bonario) uguale a 1 
        			//e che non vi siano notifiche collegate
        			$Skip = false;
        			$rs_AdditionalArticle = $rs->SelectQuery(
        			    "SELECT FineId 
                        FROM FineAdditionalArticle FA 
                        JOIN Article A ON FA.ArticleId=A.Id
                        WHERE A.Amicable=0 AND FineId=".$r_Fine['Id']);
        			$rs_FineNotification = $rs->SelectQuery(
        			    "SELECT FineId FROM FineNotification WHERE FineId=".$r_Fine['Id']);
        			if ($r_Fine['Amicable'] == 0 || mysqli_num_rows($rs_AdditionalArticle) > 0 || mysqli_num_rows($rs_FineNotification) > 0){
        			    $Skip = true;
        			    $RowNumber--;
        			} else {
        			    $rs_Trespasser = $rs->Select('Trespasser', 'Id='.$r_Fine['TrespasserId']);
        			    $r_Trespasser = mysqli_fetch_assoc($rs_Trespasser);
        			}
        			?>
        			
        			<?php if (!$Skip): ?>
            			<div class="clean_row HSpace4"></div>
            			
                        <div class="tableRow">
		          			<div class="col-sm-1" style="text-align:center;padding:0">
    	            			<div class="table_caption_button col-sm-6" style="text-align:center;">
    	            			<?php if ($RepeatedId != $r_Fine['Id']): ?>
	            					<input type="checkbox" name="check[]" value="<?= $r_Fine['Id']; ?>" checked />
    	            			<?php endif; ?>
                				</div>
    	            			<div class="table_caption_H col-sm-6" style="text-align:center;">
                    				<?= $i++; ?>
                				</div>
            				</div>
                    		<?php $RepeatedId = $r_Fine['Id']; ?>
                    		<div class="table_caption_H col-sm-1 text-center">
                    			<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= trim(StringOutDB($r_Trespasser['CompanyName'].' '.$r_Trespasser['Surname'].' '.$r_Trespasser['Name'])) ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;
                    			<?php if(!empty($r_Fine['PEC'])): ?>
                    			<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= StringOutDB($r_Fine['PEC']); ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>
                    			<?php endif; ?>
                    		</div>
                            <div class="table_caption_H col-sm-1">
                            	<?= $a_FineTypeId[$r_Fine['FineTypeId']] . $r_Fine['Id']; ?>
                        	</div>
                            <div class="table_caption_H col-sm-1">
                            	<?= $r_Fine['VehiclePlate']; ?>
                        	</div>
                            <div class="table_caption_H col-sm-1">
                        		<?= DateOutDB($r_Fine['FineDate']); ?>
                    		</div>
                            <div class="table_caption_H col-sm-1">
                            	<?= TimeOutDB($r_Fine['FineTime']); ?>
                        	</div>
                            <div class="table_caption_H col-sm-1">
                        		<?= $r_Fine['Article'].' '.$r_Fine['Paragraph'].' '.$r_Fine['Letter']; ?>
                    		</div>
                            <div class="table_caption_H col-sm-4">
                            	<?= $r_Fine['ArticleDescriptionIta']; ?>
                        	</div>
                            <div class="table_caption_button  col-sm-1">
                            	<?= ChkButton($aUserButton, 'prn','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Fine['Id'].'"><span class="tooltip-r glyphicon glyphicon-eye-open" title="Visualizza" data-placement="top" style="line-height:2rem;"></span></a>'); ?>
                        	</div>
                    	</div>
                	<?php endif; ?>
            	<?php endwhile; ?>
        	
        		<?php if ($RowNumber > 0): ?>
            		<div class="clean_row HSpace4"></div>
            		
            		<div class="col-sm-3 BoxRowLabel">
                        Data creazione
                    </div>
            		<div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_date frm_field_required" type="text" name="CreationDate" value="<?= date('d/m/Y'); ?>">
                    </div>
            		<div class="col-sm-8 BoxRowCaption">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
            	
            	    <div class="table_label_H HSpace4" style="height:8rem;">
            	    	<div style="padding-top:2rem;">
                	    	<?= ChkButton($aUserButton, 'prn','<button type="submit" id="print" class="btn btn-success">Anteprima flusso</button>'); ?>
        					<?= ChkButton($aUserButton, 'prn','<input type="checkbox" name="ultimate" id="ultimate" style="margin-left:5rem;"> Definitivo'); ?>
            	    	</div>
                    </div>
				<?php else: ?>
	    	        <div class="table_caption_H col-sm-12 text-center">
                    	Nessun record presente
                    </div>
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
	$(document).ready(function () {

        $('#TypePlate').change(function(){
            $('#f_search').submit();
        });

        $('#checkAll').click(function() {
            $('input[name=check\\[\\]]').prop('checked', this.checked);
            $("#f_senddynamickindfine").trigger( "check" );
        });

        $('input[name=check\\[\\]]').change(function() {
            $("#f_senddynamickindfine").trigger( "check" );
        });

        $("#f_senddynamickindfine").on('check', function(){
        	if ($('input[name=check\\[\\]]:checked').length > 0)
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
                $('#print').html('Flusso definitivo');
                $('#print').removeClass( "btn-success" ).addClass( "btn-warning" );
            }else{
                $('#print').html('Anteprima flusso');
                $('#print').removeClass( "btn-warning" ).addClass( "btn-success" );
            }
        });

        $('#f_senddynamickindfine').on('submit', function(e){
        	if($('#ultimate').is(":checked")) {
            	if (!confirm("Si stanno per creare verbali e flusso in maniera definitiva. Continuare?")){
                	e.preventDefault();
                	return false;
            	}
        	}
        });
	});
</script>
<?php
include(INC."/footer.php");
