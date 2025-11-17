<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/initialization.php");
require_once(INC."/menu_" . $_SESSION['UserMenuType'] . ".php");
require_once(CLS."/cls_view.php");

$Filter = CheckValue('Filter', 'n');
$PageTitle = CheckValue('PageTitle', 's');

$str_Where = '1=1';

/**Ente**/
if($_SESSION['userlevel'] >= 3){
    if($Search_CityId != ''){
        $str_Where .= " AND ci.Id='$Search_CityId'";
    }
}
else
    $str_Where .= " AND ci.Id='{$_SESSION['cityid']}'";
    
    /**Numero flusso**/
    if($Search_Flow != ''){
        $str_Where.= " AND fl.Number=$Search_Flow";
    }
    
    /**Anno**/
    if ($Search_Year != ''){
        $str_Where .= " AND fi.ProtocolYear=$Search_Year";
    }
    
    /**Tipo flusso**/
    if ($Search_PrintNumber > 0){
        $str_Where .= " AND fl.PrintTypeId=$Search_PrintNumber";
    }
    
    /**Stampatore**/
    if($Search_Type > 0){
        $str_Where.= " AND fl.PrinterId=$Search_Type";
    }
    
    /**Data di upload inizio**/
    if($Search_FromSendDate!=''){
        $str_Where.= " AND fl.UploadDate>='".DateInDB($Search_FromSendDate)."'";
    }
    
    /**Data di upload fine**/
    if($Search_ToSendDate!=''){
        $str_Where.= " AND fl.UploadDate<='".DateInDB($Search_ToSendDate)."'";
    }
    
    $query = new CLS_VIEW(PRN_NOTIFICATION_MISSING);
    
    define('NUMERO_PER_PAGINA',100);
    $limitePagina = $page * NUMERO_PER_PAGINA - NUMERO_PER_PAGINA;
    
    /**TRASFORMARE NELLA QUERY PER LE NOTIFICHE MANCANTI**/
    if ($Filter == 1){
        //$rs_Flow = $rs->Select('V_Flow', $str_Where, 'Year DESC, Number DESC, CreationDate DESC');
        $order = "fl.Number asc";
        $rs_NotificationMissing = $rs->SelectQuery($query->generateSelect($str_Where,null,$order,null));
        
        $RowNumber = mysqli_num_rows($rs_NotificationMissing);
        
        mysqli_data_seek($rs_NotificationMissing, $limitePagina);
    }
    
    //******Stampa******
    echo $str_out;
    ?>

<div class="row-fluid">
	<form id="f_search" action="" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Ente
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?php if($_SESSION['userlevel'] >= 3): ?>
            		<?= CreateSelectQuery("SELECT SC.Title, C.CityId FROM Customer C JOIN sarida.City SC ON SC.Id=C.CityId","Search_CityId","CityId","Title",$Filter != 1 ? $_SESSION['cityid'] : $Search_CityId,false); ?>
            	<?php else: ?>
            		<?= $_SESSION['citytitle']; ?>
            	<?php endif; ?>
            </div>   
            <div class="col-sm-1 BoxRowLabel">
                Flusso N°
            </div>    
            <div class="col-sm-1 BoxRowCaption">      
                <input class="form-control frm_field_numeric" name="Search_Flow" type="text" value="<?= $Search_Flow ?>">
            </div>    
            <div class="col-sm-1 BoxRowLabel">
                Anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect(range(date('Y'), 2012), false, 'Search_Year', 'Search_Year', $Search_Year != '' ? $Search_Year : $_SESSION['year'], true); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo flusso
            </div>
            <div class="col-sm-2 BoxRowCaption"> 
                <?= CreateSelect("Print_Type", "Id!=6", "Id", "Search_PrintNumber", "Id", "Name", $Search_PrintNumber, false); ?>
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                Stampatore
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?= CreateSelect('Printer', '1=1', 'Id', 'Search_Type', 'Id', 'Name', $Search_Type, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data di upload
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_date" name="Search_FromSendDate" id="UploadDateStart" type="text" value="<?= $Search_FromSendDate; ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                a data di upload
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input class="form-control frm_field_date" name="Search_ToSendDate" id="UploadDateEnd" type="text" value="<?= $Search_ToSendDate; ?>">
            </div>
            <div class="col-sm-4 BoxRowCaption"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    <form id="mainForm" action="" method="post">
	    <div class="table_label_H col-sm-1">N°Prog.</div>
	    <div class="table_label_H col-sm-4">Ente</div>
	    <div class="table_label_H col-sm-3">N°Flusso</div>
	    <div class="table_label_H col-sm-3">Cron</div>
	    <div class="table_add_button col-sm-1 right"></div>
	    
	    <div class="clean_row HSpace4"></div>
	    
		<?php if ($Filter != 1):?>
		<div class="table_caption_H col-sm-12 text-center">
			Inserire criteri di ricerca
		</div>
	    	<?php else: ?>
			<?php if ($RowNumber > 0):?>
				<?php $n_Count = $limitePagina+1; ?>
				<?php for($i=0; $i<NUMERO_PER_PAGINA; $i++):?> 
	    				<?php $r_NotificationMissing = $rs->getArrayLine($rs_NotificationMissing); ?>
	    				<?php if(!empty($r_NotificationMissing)):?>
						<div class="tableRow">
							<div class="table_caption_H col-sm-1">
								<?=$n_Count?>
							</div>
							<div class="table_caption_H col-sm-4">
								<?= $r_NotificationMissing["CityId"]." / ".$r_NotificationMissing["CityName"]?>
							</div>
							<div class="table_caption_H col-sm-3">
								<?= $r_NotificationMissing["FlowNumber"]?>
							</div>
							<div class="table_caption_H col-sm-3">
								<?= $r_NotificationMissing["ProtocolId"]."/".$r_NotificationMissing["ProtocolYear"]?>
							</div>
							<div class="table_caption_button col-sm-1">
								<a href="<?='mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_NotificationMissing["FineId"]?>"><span class="tooltip-r glyphicon glyphicon-eye-open" title="Visualizza" data-placement="top" style="position:absolute;left:5px;top:5px;"></span></a>
							</div>
						</div>
						<div class="clean_row HSpace4"></div>
					
						<?php $n_Count++?>
					<?php endif;?>
				<?php endfor; ?>
				<div class="table_label_H HSpace4 text-center" style="height:8rem;">
		        <button type="button" class="btn btn-success" id="printExcel" name="XLS" value="1" style="margin-top:2rem;">
		            <i class="fa fa-file-excel-o"></i> Stampa prospetto  
		        </button>
		    </div>
			<?= CreatePagination(NUMERO_PER_PAGINA, $RowNumber, $page, impostaParametriUrl(array('Filter' => 1), $str_CurrentPage.$str_GET_Parameter), $strLabel);?>
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
		//Funzione di modifica della riga al passaggio del mouse
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	
      	//Funzione di modifica della riga all'uscita del mouse
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
      	
      	//Controlla lo stato della tendina della voce di ricerca "Status attivo" per determinare l'abilitazione della "Data status"
      	$("#Search_Step").on('change', function(){
      		//Se lo status non è determinato allora la data resta disabilitata
      		if($(this).val() == ''){
      			$('#Search_StepDate').prop('disabled', true).val('');
      		//In caso lo status venga determinato allora fa lo stesso con la data
      		} else {
      			$('#Search_StepDate').prop('disabled', false);
      		}
      	});
      	
      	//Al click sul pulsante di stampa
      	$('#printExcel').click(function () {
      		//Mostro l'icona di caricamento
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
    		//Chiamo la pagina "_exe" e disabilito il pulsante di stampa
        	if ($(this).attr("id") == 'printExcel'){
        		window.location="prn_notification_missing_exe.php<?=$str_GET_Parameter?>&Filter=1";
        		$('#printExcel').prop('disabled',true);
        	}
        });
        
        //Blocco la selezione dei pulsanti al click del pulsante di ricerca
        $('#f_search').on('submit', function () {
        	$('#search, #printExcel').prop('disabled', true);
        });
	});
</script>

<?php
require_once(INC."/footer.php");