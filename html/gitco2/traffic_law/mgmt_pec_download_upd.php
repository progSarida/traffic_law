<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

//Per distinguere se si stà entrando in modifica o cancellazione, se non impostato il default è update
$b_Delete = CheckValue('Op', 's') == 'del';
$Id = CheckValue('Id', 'n');

$r_DownloadEmail = $rs->getArrayLine($rs->Select('DownloadEmail', "Id=$Id"));

if($r_DownloadEmail){
    if($r_DownloadEmail['Status'] == 'toread'){
        $a_UpdateM = array(
            array('field'=>'Status','selector'=>'value','type'=>'str','value'=>'read'),
            array('field'=>'VersionDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
            array('field'=>'VersionTime','selector'=>'value','type'=>'str','value'=>date('H:i:s')),
        );
        
        $rs->Update('DownloadEmail', $a_UpdateM, "Id=$Id");
    }
    
    $BodyFilePath = file_exists(MAILDOWNLOAD_FOLDER."/{$r_DownloadEmail['CityId']}/$Id/$Id.html")
    ? MAILDOWNLOAD_FOLDER_HTML."/{$r_DownloadEmail['CityId']}/$Id/$Id.html"
    : null;
    
    $a_DownloadEmailAttachments = $rs->getResults($rs->Select('DownloadEmailAttachments', "DownloadEmailId=$Id"));
    $a_DownloadEmailCategories = $rs->getResults($rs->SelectQuery("SELECT DECA.DownloadEmailId, DECA.CategoryId, EM.Title FROM DownloadEmailCategories DECA join EmailCategories EM ON DECA.CategoryId = EM.Id WHERE DECA.DownloadEmailId=$Id"));
    $a_DownloadEmailCategories = array_column($a_DownloadEmailCategories, 'Title', 'CategoryId');
}

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_update" action="<?= $b_Delete ? 'mgmt_pec_download_del_exe.php' : 'mgmt_pec_download_upd_exe.php' ?>" method="post" autocomplete="off">
		<input type="hidden" name="Id" value="<?= $r_DownloadEmail['Id']; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="table_caption_I col-sm-12 text-center">DETTAGLI DEL MESSAGGIO</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel col-sm-2">
			Data e ora ricezione
		</div>
		<div class="BoxRowCaption col-sm-10">
			<?= DateOutDB($r_DownloadEmail['ReceiveDate']).' alle '.TimeOutDB($r_DownloadEmail['ReceiveTime']); ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel col-sm-2">
			Oggetto
		</div>
		<div class="BoxRowCaption col-sm-10">
			<?= htmlentities($r_DownloadEmail['MailSubject']); ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel col-sm-2">
			Mittente
		</div>
		<div class="BoxRowCaption col-sm-10">
			<?= htmlentities($r_DownloadEmail['MailFrom']); ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel col-sm-2">
			Destinatari
		</div>
		<div class="BoxRowCaption col-sm-10">
			<?= htmlentities($r_DownloadEmail['MailTo']); ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel col-sm-2">
			Cc
		</div>
		<div class="BoxRowCaption col-sm-10">
			<?= htmlentities($r_DownloadEmail['MailCC']); ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="BoxRowLabel col-sm-2">
			Rispondi a
		</div>
		<div class="BoxRowCaption col-sm-10">
			<?= htmlentities($r_DownloadEmail['MailReplyTo']); ?>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="table_caption_I col-sm-6 text-center">ALLEGATI</div>
		<div class="table_caption_I col-sm-6 text-center">CORPO DEL MESSAGGIO</div>
		
		<div class="clean_row HSpace4"></div>
		
		<div class="col-sm-6">
			<div class="BoxRow col-sm-12" style="height:9rem;overflow-y:scroll;">
				<?php foreach($a_DownloadEmailAttachments as $attachment): ?>
    				<div class="BoxRowLabel col-sm-2 font_small">
    					Nome Documento
        			</div>
        			<div class="BoxRowCaption col-sm-8" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        				<?= $attachment['Documentation']; ?>
        			</div>
    				<div class="BoxRowLabel col-sm-2 text-center">
    					<i data-id="<?= $attachment['DownloadEmailId'] ?>" data-name="<?= htmlspecialchars($attachment['Documentation']) ?>" data-container="body" data-toggle="tooltip" data-placement="top" title="Scarica allegato" class="fa fa-download attachment tooltip-r" style="font-size:2rem;cursor:pointer"></i>
        			</div>
        			
        			<div class="clean_row HSpace4"></div>
    			<?php endforeach; ?>
			</div>
			
			<div class="table_caption_I col-sm-12 text-center">MODIFICA</div>
			
			<div class="BoxRow col-sm-12" style="height:71rem;">
				<div class="BoxRowLabel col-sm-3">
					Segna come lavorato
    			</div>
				<div class="BoxRowCaption col-sm-9">
					<?php if($b_Delete): ?>
						<?= CheckbuttonOutDB($r_DownloadEmail['Status'] == 'processed' ? 1 : 0); ?>
					<?php else: ?>
						<input type="checkbox" name="Processed" value="1" <?= ChkCheckButton($r_DownloadEmail['Status'] == 'processed' ? 1 : 0); ?>>
					<?php endif; ?>
    			</div>
    			
    			<div class="clean_row HSpace4"></div>
    			
				<div class="BoxRowLabel col-sm-3">
					Categorie
    			</div>
				<div class="BoxRowCaption col-sm-9" style="min-height:2rem;height:auto;">
					<?php if($b_Delete): ?>
						<?= implode(', ', $a_DownloadEmailCategories); ?>
					<?php else: ?>
						<?= CreateSelectExtended('EmailCategories', '1=1', 'Id', 'Categories[]', 'Categories', 'Id', 'Title', null, true, false, null, null, null, 'multiple="multiple"') ?>
					<?php endif; ?>
    			</div>
			</div>
		</div>
		<div class="BoxRow col-sm-6" style="height:80rem">
			<iframe src="<?= $BodyFilePath; ?>" style="width:100%;height:100%;background:white;"></iframe>
		</div>
		
		<div class="clean_row HSpace4"></div>
		
        <div class="table_label_H HSpace4" style="height:8rem;">
        	<?php if($b_Delete): ?>
        		<button id="delete" type="submit" class="btn btn-danger" style="margin-top:2rem;width:inherit;"><i class="fas fa-times fa-fw"></i> Elimina</button>
        	<?php else: ?>
        		<button id="update" type="submit" class="btn btn-success" style="margin-top:2rem;width:inherit;"><i class="fas fa-save fa-fw"></i> Salva</button>
        	<?php endif; ?>
        	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
        </div>
	</form>
</div>
<script>
	$(document).ready(function () {
		<?php if(!$b_Delete): ?>
			$('#Categories').val([<?= !empty($a_DownloadEmailCategories) ? implode(',', array_keys($a_DownloadEmailCategories)) : '' ?>]).change();
			$("#Categories").select2();
		<?php else: ?>
			$('#f_update').on('submit', function(){
				if(!confirm('ATTENZIONE: Si stà per eliminare il messaggio in maniera definitiva. Continuare?')){
					return false;
				}
			});
		<?php endif; ?>
		
		$('#back').on('click', function(){
			window.location=`<?= 'mgmt_pec_download.php'.$str_GET_Parameter; ?>`;
			return false;
		});
		
		$('.attachment').on('click', function(){
			var Id = $(this).data('id');
			var FileName = $(this).data('name');
			
			if(Id != '' && FileName != ''){
				window.location=`mgmt_pec_download_attachment_exe.php?Id=`+Id+`&FileName=`+FileName;
			}
		});
    });
</script>
<?php
require_once (INC . "/footer.php");