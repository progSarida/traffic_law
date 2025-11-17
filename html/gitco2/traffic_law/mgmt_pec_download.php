<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(CLS."/cls_mail.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

global $rs;

$PageTitle = CheckValue('PageTitle', 's');

$a_Status = array(
    'toread' => array('Class' => 'fa fa-eye-slash text-danger', 'ColorClass' => 'text-danger', 'Tooltip' => 'Da leggere'),
    'read' => array('Class' => 'fa fa-eye text-info', 'ColorClass' => 'text-info', 'Tooltip' => 'Letto il %s (%s)'),
    'toprocess' => array('Class' => 'fa fa-clock text-warning', 'ColorClass' => 'text-warning', 'Tooltip' => 'In lavorazione dal %s (%s)'),
    'processed' => array('Class' => 'fa fa-check-circle text-success', 'ColorClass' => 'text-success', 'Tooltip' => 'Lavorato il %s (%s)')
);

//Gestore mail: inizializzazione///////////////////////////////////////////////////////////
$rs_CustomerMail = $rs->Select('CustomerMailAuthentication', "ConfigType=2 AND CityId='".$_SESSION['cityid']."'");
$r_CustomerMail = $rs->getArrayLine($rs_CustomerMail) ?? array();

try{
    $mail = new MAIL_HANDLER($r_CustomerMail);
    
    //Tenta di aprire il server in entrata
    $testmail = $mail->mailboxOpening();
    
    if($testmail !== true){
        $mailBoxError = $testmail;
    } else {
        //Cambia la cartella con quella salvata nelle configurazioni
        $selectFolder = $mail->mailboxSelectFolder($r_CustomerMail['IncomingMailbox']);
        
        if($selectFolder !== true){
            $mailBoxError = $selectFolder;
        } else {
            $numMessages = $mail->mailboxGetNumMails();
            $mail->mailboxClosing();
        }
    }
} catch (Exception $e) {
    $mailBoxError = $e->getMessage();
}
////////////////////////////////////////////////////////////////////////

if(isset($mailBoxError)){
    $_SESSION['Message']['Warning'] = 'Errore server di posta in entrata: '.$mailBoxError.'<br>Non sarà possibile scaricare nuovi messaggi.';
} 

$str_Where = "1=1 AND DE.CityId='{$_SESSION['cityid']}'";

if($Search_Categories != ''){
    $Search_Categories = is_array($Search_Categories) ? $Search_Categories : unserialize($Search_Categories);
    $str_Where .= ' AND DECA.CategoryId IN('.implode(',', $Search_Categories).')';
}
if($Search_Outcome != ''){
    $str_Where .= " AND DE.Status = '$Search_Outcome'";
}
if($Search_Name != ''){
    $str_Where .= " AND DE.MailSubject LIKE '%$Search_Name%'";
}
if($Search_UserName != ''){
    $str_Where .= " AND DE.MailFrom LIKE '%$Search_UserName%'";
}
if($Search_FromSendDate != ''){
    $str_Where .= " AND DE.ReceiveDate >= '".DateInDB($Search_FromSendDate)."'";
}
if($Search_ToSendDate != ''){
    $str_Where .= " AND DE.ReceiveDate <= '".DateInDB($Search_ToSendDate)."'";
}

$a_EmailCategories = array_column($rs->getResults($rs->Select('EmailCategories')), 'Title', 'Id');

$cls_view = new CLS_VIEW(MGMT_PEC_DOWNLOAD);
$rs_DownloadEmail = $rs->SelectQuery($cls_view->generateSelect($str_Where));
$RowNumber = mysqli_num_rows($rs_DownloadEmail);
mysqli_data_seek($rs_DownloadEmail, $pagelimit);

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_search" action="mgmt_pec_download.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		
		<div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Oggetto
            </div>
            <div class="col-sm-4 BoxRowCaption">
            	<input name="Search_Name" value="<?= $Search_Name; ?>" type="text" class="frm_field_string form-control">
            </div>
        	<div class="col-sm-1 BoxRowLabel">
            	Stato
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect(array('toread' => 'Da leggere', 'read' => 'Letto', 'toprocess' => 'In lavorazione', 'processed' => 'Lavorato'), true, 'Search_Outcome', 'Search_Outcome', $Search_Outcome, false)?>
            </div>
        	<div class="col-sm-1 BoxRowLabel font_small">
            	Da data ricezione
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<input name="Search_FromSendDate" value="<?= $Search_FromSendDate; ?>" type="text" class="frm_field_date form-control">
            </div>
        	<div class="col-sm-1 BoxRowLabel font_small">
            	A data ricezione
            </div>
        	<div class="col-sm-1 BoxRowCaption">
            	<input name="Search_ToSendDate" value="<?= $Search_ToSendDate; ?>" type="text" class="frm_field_date form-control">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Mittente
            </div>
            <div class="col-sm-4 BoxRowCaption">
            	<input name="Search_UserName" value="<?= $Search_UserName; ?>" type="text" class="frm_field_string form-control">
            </div>
        	<div class="col-sm-7 BoxRowLabel">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
        	<div class="col-sm-1 BoxRowLabel">
        		Categorie
            </div>
            <div class="col-sm-11 BoxRowCaption">
            	<?= CreateSelectExtended('EmailCategories', '1=1', 'Id', 'Search_Categories[]', 'Search_Categories', 'Id', 'Title', null, true, false, null, null, null, 'multiple="multiple"') ?>
            </div>
		</div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:6.8rem">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" style="margin-top:0;width:100%;height:100%"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
	</form>
	
    <div class="clean_row HSpace4"></div>
    
    <div class="col-sm-12 table_label_H text-center" style="height:8rem;">
    	<?php if(in_array('imp', $aUserButton)): ?>
	    	<?php if(isset($mailBoxError)): ?>
        		<button disabled type="button" class="btn btn-warning" style="margin-top:1rem;height:5rem;"><strong><i class="fa fa-fw fa-warning"></i>  Non è stato possibile connettersi alla casella di posta.</strong></button>
        	<?php elseif(file_exists(TMP.'/'.'pec_download_'.$_SESSION['cityid'].'.lock')): ?>
        		<button disabled type="button" class="btn btn-info" style="margin-top:1rem;height:5rem;"><strong><i class="fa fa-fw fa-clock"></i>  Un'operazione è già in corso, attendere...</strong></button>
        	<?php elseif($numMessages > 0): ?>
        		<button id="pec_download" type="button" progress-tick="500" class="btn btn-info" style="margin-top:1rem;height:5rem;"><strong><i class="fa fa-fw fa-envelope"></i>  Ci sono: <?= $numMessages; ?> messaggi da scaricare</strong></button>
        	<?php else: ?>
        		<button disabled type="button" class="btn btn-default" style="margin-top:1rem;height:5rem;width:auto;"><strong><i class="fa fa-fw fa-envelope"></i>  Non ci sono nuovi messaggi da scaricare.</strong></button>
        	<?php endif; ?>
    	<?php endif; ?>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
	<div class="col-sm-12" id="DIV_Progress" style="display:none;">
		<div class="table_label_H col-sm-12" style="background-color:#294A9C;color:white;">AVANZAMENTO DELL' OPERAZIONE</div>
		
		<div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12 table_caption_H"  style="height:auto;text-align:center;padding:0;">
            <div class="progress" style="margin-bottom:0;">
    			<div id="progressbar" class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
    		</div>
            <div id="DIV_Rows" class="col-sm-12">Messaggi scaricati: <span></span></div>
            <div id="DIV_Messages" class="col-sm-12"></div>
            <div id="DIV_Results" class="col-sm-12"></div>
        </div>
        
        <div class="clean_row HSpace4"></div>
	</div>
    
    <div class="table_label_H col-sm-4">Oggetto</div>
    <div class="table_label_H col-sm-3">Mittente</div>
    <div class="table_label_H col-sm-2">Categorie</div>
    <div class="table_label_H col-sm-1">Data ricezione</div>
    <div class="table_label_H col-sm-1">Stato</div>	
    <div class="table_label_H col-sm-1"></div>
    
    <div class="clean_row HSpace4"></div>
    
	<?php if ($RowNumber > 0):?>
		<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
			<?php $r_DownloadEmail = $rs->getArrayLine($rs_DownloadEmail);?>
			<?php if (! empty($r_DownloadEmail)): ?>
				<div class="tableRow <?= $a_Status[$r_DownloadEmail['Status']]['ColorClass'] ?>">
    				<div class="table_caption_H col-sm-4" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;<?= $r_DownloadEmail['Status'] != 'toread' ? 'font-weight:normal;' : '' ?>">
    					<?= $r_DownloadEmail['MailSubject']; ?>
        			</div>
        			<div class="table_caption_H col-sm-3" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;<?= $r_DownloadEmail['Status'] != 'toread' ? 'font-weight:normal;' : '' ?>">
        				<?= htmlentities($r_DownloadEmail['MailFrom']); ?>
        			</div>
    				<div class="table_caption_H col-sm-2">
    					<?php $a_Categories = array_column($rs->getResults($rs->Select('DownloadEmailCategories', "DownloadEmailId={$r_DownloadEmail['Id']}")), 'CategoryId'); ?>
    					<?php foreach($a_Categories as $category): ?>
    						<span class="fa-stack fa tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= $a_EmailCategories[$category]; ?>" style="font-size: 1.1rem;">
                            	<i class="fa-stack-2x fa fa-square text-info" style="line-height: 2.2rem;"></i>
                                <i class="fa-stack-1x fa-inverse"><strong><?= $category; ?></strong></i>
                      		</span>
    					<?php endforeach; ?>
        			</div>
    				<div class="table_caption_H col-sm-1" style="<?= $r_DownloadEmail['Status'] != 'toread' ? 'font-weight:normal;' : '' ?>">
    					<?= DateOutDB($r_DownloadEmail['ReceiveDate']).' '.TimeOutDB($r_DownloadEmail['ReceiveTime']); ?>
        			</div>
    				<div class="table_caption_H col-sm-1 text-center">
    					<?php foreach($a_Status as $value => $data): ?>
    						<?php if($r_DownloadEmail['Status'] == $value ): ?>
    	                    	<i class="tooltip-r <?= $data['Class']; ?>" style="margin-top: 0.2rem;font-size: 1.8rem;" data-container="body" data-toggle="tooltip" data-placement="top" title="<?= sprintf($data['Tooltip'], DateOutDB($r_DownloadEmail['VersionDate']).' '.TimeOutDB($r_DownloadEmail['VersionTime']), $r_DownloadEmail['UserId']) ?>"></i>
    						<?php else: ?>
    							<i class="tooltip-r <?= $data['Class']; ?> opaque" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
    						<?php endif;?>
                    	<?php endforeach; ?>
        			</div>
    				<div class="table_caption_button col-sm-1">
    					<?= ChkButton($aUserButton, 'upd','<a href="mgmt_pec_download_upd.php'.$str_GET_Parameter.'&Id='.$r_DownloadEmail['Id'].'"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="margin-top:0.3rem;"></i></a>'); ?>
    					<?= ChkButton($aUserButton, 'del','<a href="mgmt_pec_download_upd.php'.$str_GET_Parameter.'&Op=del&Id='.$r_DownloadEmail['Id'].'"><i data-toggle="tooltip" data-container="body" data-placement="top" title="Elimina" class="tooltip-r fa fa-times" style="margin-top:0.3rem;"></i></a>'); ?>
        			</div>
    			</div>
    			
    			<div class="clean_row HSpace4"></div>
			<?php endif; ?>
		<?php endfor; ?>
    	<?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage.$str_GET_Parameter, '');?>
	<?php else: ?>
        <div class="table_caption_H col-sm-12 text-center">
        	Nessun record presente
        </div>
	<?php endif; ?>
</div>
<script src="<?= JS ?>/progressbar.js" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#Search_Categories').val([<?= !empty($Search_Categories) ? implode(',', $Search_Categories) : '' ?>]).change();
		$("#Search_Categories").select2();
		
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
		
	    $('#pec_download').on('click', function(){
	    	if(confirm('ATTENZIONE: tutti i messaggi scaricati saranno eliminati dalla casella web, continuare?')){
		    	$('#DIV_Progress').show();
	    		progressBar_start('mgmt_pec_download_imp_exe.php', this, {CityId: '<?= $_SESSION['cityid']; ?>'});
	    	}
	    });
	    
        $('#pec_download').on('progressDone', function(e, data){
            $('#progressbar').removeClass('progress-bar-info progress-bar-striped active');
			$('#progressbar').addClass('progress-bar-success');

			setTimeout(location.reload(), 1000);			
        });
        $('#pec_download').on('progressFail', function(e, data){
            $('#progressbar').removeClass('progress-bar-info progress-bar-striped active');
            $('#progressbar').addClass('progress-bar-danger');
            $('#DIV_Messages').html(data.responseText);
        });
        $('#pec_download').on('progressGet', function(e, data){
            $('#DIV_Rows span').html(data.Contati + ' / ' + data.Totali);
            $('#DIV_Messages').html(data.Passo);
        });
    });
</script>
<?php
require_once (INC . "/footer.php");