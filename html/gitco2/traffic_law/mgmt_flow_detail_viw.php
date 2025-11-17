<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

function resultColourClass($r_FN){
    if($r_FN['ResultId'] > 0){
        if(($r_FN['ResultId'] > 9 && $r_FN['ResultId'] < 21) || ($r_FN['ResultId'] == 21 && $r_FN['ValidatedAddress'] != 1) || $r_FN['ResultId'] == 23){
            return 'table_caption_error';
        } else return 'table_caption_success';
    } else return '';
}

$FlowId = CheckValue('FlowId','n');

$rs_Flow = $rs->Select('Flow', "Id=$FlowId");
$r_Flow = mysqli_fetch_assoc($rs_Flow);

$cls_view = new CLS_VIEW(MGMT_FLOW_DETAIL);
$rs_FineNotification = $rs->SelectQuery($cls_view->generateSelect("FH.FlowId=$FlowId", null, 'F.ProtocolId ASC'));
$RowNumber = mysqli_num_rows($rs_FineNotification);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_flow_detail" action="prn_flow_detail_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="FlowId" value="<?= $FlowId; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
        <div class="col-sm-2 BoxRowLabel">
            Flusso N.
        </div>
        <div class="col-sm-2 BoxRowCaption">      
        	<?= '('.$r_Flow['CityId'].') '.$r_Flow['Number'].'/'.$r_Flow['Year']; ?>
        </div>   
        <div class="col-sm-2 BoxRowLabel">
            Del
        </div>
        <div class="col-sm-2 BoxRowCaption">      
            <?= DateOutDB($r_Flow['CreationDate']); ?>
        </div>          	        
        <div class="col-sm-2 BoxRowLabel">
            Spedito il 
        </div>
        <div class="col-sm-2 BoxRowCaption">      
            <?= DateOutDB($r_Flow['SendDate']); ?>
        </div> 
                	    
        <div class="clean_row HSpace4"></div>
                	    
        <div class="table_label_H col-sm-1"></div>
        <div class="table_label_H col-sm-2">Cronologico</div>
        <div class="table_label_H col-sm-2">Data Notifica</div>
        <div class="table_label_H col-sm-2">Raccomandata</div>				
        <div class="table_label_H col-sm-2">Ricevuta ritorno</div>
        <div class="table_label_H col-sm-3">Esito</div>
        
        <div class="clean_row HSpace4"></div>
        
    	<?php if ($RowNumber > 0): ?>
    		<?php $n_Count = 1; ?>
    		<?php while ($r_FineNotification = $rs->getArrayLine($rs_FineNotification)): ?>
			<div class="tableRow">
    			<div class="table_caption_H col-sm-1">
    				<?= $n_Count++; ?>
				</div>
                <div class="table_caption_H col-sm-2">
                    <?= $r_FineNotification['ProtocolId'] .'/'. $r_FineNotification['ProtocolYear']; ?>
                </div>
                <div class="table_caption_H col-sm-2">
                    <?= DateOutDB($r_FineNotification['NotificationDate']); ?>
                </div>
                <div class="table_caption_H col-sm-2">
                    <?= $r_FineNotification['LetterNumber']; ?>
                </div>
                <div class="table_caption_H col-sm-2">
                    <?= $r_FineNotification['ReceiptNumber']; ?>
                </div>
                <div class="table_caption_H col-sm-3 <?= resultColourClass($r_FineNotification); ?>">
                    <?= $r_FineNotification['ResultTitle']; ?>
                </div>  
			</div>
			
			<div class="clean_row HSpace4"></div>
			
    		<?php endwhile; ?>
    		
            <div class="table_label_H HSpace4 text-center" style="height:8rem;">
                <button type="button" class="btn btn-default" id="back" style="margin-top:2rem;">
                	Indietro
            	</button>
                <button type="submit" class="btn btn-warning" name="CSV" value="0" style="margin-top:2rem;">    
                    <i class="fa fa-file-pdf-o"></i> Stampa prospetto           
                </button>
                <button type="submit" class="btn btn-success" name="CSV" value="1" style="margin-top:2rem;">
                    <i class="fa fa-file-excel-o"></i> Stampa prospetto  
                </button>
            </div>
    	<?php else: ?>
	        <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente.
            </div>
    	<?php endif; ?>
	</form>
</div>

<script type="text/javascript">
    $('document').ready(function () {
        $('#back').click(function () {
            window.location="<?= impostaParametriUrl(array('Filter' => 1), 'mgmt_flow.php'.$str_GET_Parameter); ?>";
        });
    });
</script>
<?php
require_once(INC . "/footer.php");
