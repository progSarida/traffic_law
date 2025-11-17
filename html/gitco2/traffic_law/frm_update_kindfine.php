<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

//la procedura di assegnazione della data di invio flusso bonario vale per lettere generate in modo tradizionale (statico) oppure dinamico.
$str_FormAction = 'frm_update_kindfine_exe.php';


$PageTitle = CheckValue('PageTitle','s');
$Search_HasPEC = CheckValue('Search_HasPEC', 'n') != '' ? CheckValue('Search_HasPEC', 'n') : ($r_Customer['ManagePEC'] == 1 ? 1 : 0);

$KindSendDate = CheckValue('KindSendDate','s') != '' ? CheckValue('KindSendDate','s') : date('d/m/Y');

$PreviousId = 0;

$str_Where = '1=1';
$str_Where .= " AND StatusTypeId=10 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year']." AND KindCreateDate IS NOT NULL AND KindSendDate IS NULL AND PreviousId <= 0"; 

if ($s_TypePlate == "N") {
    $str_Where .= " AND CountryId='Z000'";
} else {
    $str_Where .= " AND CountryId!='Z000'";
}
if ($Search_HasPEC != ''){
    $str_Where .= $Search_HasPEC == 1 ? " AND Id NOT IN (SELECT Id FROM V_Fine WHERE (PEC != '' AND PEC IS NOT NULL) GROUP BY Id)" : "";
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

if ($Search_FromKindCreateDate != "") {
    $str_Where .= " AND KindCreateDate>='".DateInDB($Search_FromKindCreateDate)."'";
}
if ($Search_ToKindCreateDate != "") {
    $str_Where .= " AND KindCreateDate<='".DateInDB($Search_ToKindCreateDate)."'";
}

if ($Search_Violation != "" && $Search_Violation != 0) {
    $str_Where .= " AND ViolationTypeId=".$Search_Violation;
}
if ($Search_Article != "") {
    $a_Article = explode(' ', $Search_Article);
    $str_Where .= (isset($a_Article[0]) ? " AND Article={$a_Article[0]}" : "").(isset($a_Article[1]) ? " AND Paragraph='{$a_Article[1]}'" : "").(isset($a_Article[2]) ? " And Letter='{$a_Article[2]}'" : "");
} else {
    $str_Where .= " AND ((Article=193 AND Paragraph='2') OR (Article=80 AND Paragraph='14'))";
}

if ($s_TypePlate != ""){
    $rs_Fine = $rs->Select('V_Fine', $str_Where, 'FineDate ASC, FineTime ASC, Id ASC', ($RecordLimit != '' ? $RecordLimit : null));
    //echo $str_Where;
    $RowNumber = mysqli_num_rows($rs_Fine);
}

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_search" action="frm_update_kindfine.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel" >
                Numero record
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array(5,25,50,100,200), false, 'RecordLimit', 'RecordLimit', $RecordLimit, true) ?>
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
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate ?>" name="Search_ToFineDate" id="Search_ToFineDate">
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
            	<?= CreateArraySelect(array('D' => 'Ditta', 'P' => 'Persona fisica'), true, 'Search_Genre', 'Search_Genre', $Search_Genre, false, null, null, 'Entrambi'); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
            	Articolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateArraySelect(array('80 14','193 2'), false, 'Search_Article', 'Search_Article', $Search_Article, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data creazione 
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromKindCreateDate; ?>" name="Search_FromKindCreateDate" id="Search_FromKindCreateDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data creazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToKindCreateDate ?>" name="Search_ToKindCreateDate" id="Search_ToKindCreateDate">
            </div>
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Escludi PEC
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<!-- Input per checkbox vuota -->
    			<input value="0" type="hidden" name="Search_HasPEC"> 
            	<input name="Search_HasPEC" id="Search_HasPEC" type="checkbox" value="1" <?= ChkCheckButton($Search_HasPEC); ?>>
            </div>
            <div class="col-sm-10 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_updatekindfine" action="<?= $str_FormAction; ?>" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="TypePlate" value="<?= $s_TypePlate; ?>">
    
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked/></div>
        <div class="table_label_H col-sm-1">Info</div>
        <div class="table_label_H col-sm-1">ID</div>
        <div class="table_label_H col-sm-1">Targa</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-1">Ora</div>
        <div class="table_label_H col-sm-1">Articolo</div>
        <div class="table_label_H col-sm-3">Descrizione</div>
        <div class="table_label_H col-sm-1">Data creazione</div>
        <div class="table_label_H col-sm-1"></div>
        
        <?php if($s_TypePlate==""): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere nazionalità targa
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php $i=1; ?>
        		<?php while ($r_Fine = mysqli_fetch_assoc($rs_Fine)): ?>
        			
        			<div class="clean_row HSpace4"></div>
        			
                    <div class="tableRow">
	          			<div class="col-sm-1" style="text-align:center;padding:0">
	            			<div class="table_caption_button col-sm-6" style="text-align:center;">
	            			<?php if ($PreviousId != $r_Fine['Id']): ?>
            					<input type="checkbox" name="checkbox[]" value="<?= $r_Fine['Id']; ?>" checked />
	            			<?php endif; ?>
            				</div>
	            			<div class="table_caption_H col-sm-6" style="text-align:center;">
                				<?= $i++; ?>
            				</div>
        				</div>
                		<?php $PreviousId = $r_Fine['Id']; ?>
                		<div class="table_caption_H col-sm-1 text-center">
                			<i class="fas fa-user tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= trim(StringOutDB($r_Fine['TrespasserFullName'])); ?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>&nbsp;
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
                        <div class="table_caption_H col-sm-3" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        	<?= $r_Fine['ArticleDescriptionIta']; ?>
                    	</div>
                    	<div class="table_caption_H col-sm-1">
                    		<?= DateOutDB($r_Fine['KindCreateDate']); ?>
                		</div>
                        <div class="table_caption_button  col-sm-1">
                        	<?= ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Fine['Id'].'"><span class="tooltip-r glyphicon glyphicon-eye-open" title="Visualizza" data-placement="top" style="line-height:2rem;"></span></a>'); ?>
                    	</div>
                	</div>
            	<?php endwhile; ?>
            	
        		<div class="clean_row HSpace4"></div>
        	
        	    <div class="col-sm-3 BoxRowLabel">
                    Data invio lettera invio bonario
                </div>
        		<div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_date txt-warning" type="text" id="KindSendDate" name="KindSendDate" value="<?= $KindSendDate; ?>">
                </div>
                <div class="col-sm-7 BoxRowLabel">
            	</div>
                
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	
        	    	<div style="padding-top:3rem;">
            	    	
            	    	<button type="submit" id="update" class="btn btn-success" style="width:16rem;">Inserisci date</button>        	    	</div>
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
            $("#f_updatekindfine").trigger( "check" );
        });

        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_updatekindfine").trigger( "check" );
        });

        $("#f_updatekindfine").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#update').prop('disabled', false);
        	else
        		$('#update').prop('disabled', true);
        });
	    
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $('#f_updatekindfine').on('submit', function(e){
        	
        	if (confirm("Si sta per aggiornare le date in modo definitivo. Continuare?")){
    			$('#update').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
    			$('#update').prop('disabled', true);
        	} else {
            	e.preventDefault();
            	return false;
        	}

        });
	});
</script>
<?php
include(INC."/footer.php");
