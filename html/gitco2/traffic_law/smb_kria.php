<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
//require_once(CLS.'/cls_smbclient.php');

require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

function checkFolderNameDate($name){
    $dt = DateTime::createFromFormat("Y_m_d", $name);
    return $dt !== false ? $dt->format('d/m/Y') : false;
}

$a_Elaborated = array();
$a_ToElaborate = array();
$str_ImportFolder = IMPORT_FOLDER. '/' . $_SESSION['cityid'];

$rs_Detector = $rs->SelectQuery("SELECT Code FROM Detector WHERE CityId='{$_SESSION['cityid']}'");
$a_Detectors =  array_column(mysqli_fetch_all($rs_Detector,MYSQLI_ASSOC), 'Code');

//TODO primo sviluppo con uso di cls_smbclient, rimuovere eventualmente
// $smbc = new cls_smbclient($cartella, $utente, $pass);

// if($smbc->dir($rootFolder) !== false){
//     foreach($a_Detectors as $code){
//         $a_Entry = array();
//         $a_Entry['Code'] = $code;
//         //Guardo se la cartella del rilevatore esiste
//         if(($smbc->dir($rootFolder, $code)) !== false){
//             $importFolders = $smbc->dir("$rootFolder/$code");
//             foreach($importFolders as $importFolder){
//                 if($formattedDate = checkFolderNameDate($importFolder['filename'])){
//                     $a_Entry['Date'] = $formattedDate;
//                     $dateFolder = $importFolder['filename'];
//                     $importFolder = $smbc->dir("$rootFolder/$code/$dateFolder");
//                     foreach($importFolder as $file){
//                         $fileName = $file['filename'];
//                         $a_Entry['File'] = $fileName;
//                         $a_Entry['Size'] = $file['size'] > 0 ? formatBytes($file['size'], 2) : 0;
//                         if(pathinfo($fileName)['extension'] == 'zip')
//                             $a_ToElaborate[] = $a_Entry;
//                         else if(pathinfo($fileName)['extension'] == 'elab')
//                             $a_Elaborated[] = $a_Entry;
//                     }
//                 }
//             }
//         }
//     }
// }

if(file_exists($str_ImportFolder)){
    foreach($a_Detectors as $code){
        if($dir = @scandir("$str_ImportFolder/$code")){
            foreach(array_diff($dir, array('.','..')) as $dateDirName){
                if($formattedDate = checkFolderNameDate($dateDirName)){
                    if(!empty($file = glob("$str_ImportFolder/$code/$dateDirName/*.{elab,zip}", GLOB_BRACE))){
                        $fileName = basename($file[0]);
                        $fileSize = formatBytes(filesize($file[0]));
                        $a_Entry = array('Code' => $code, 'Date' => $formattedDate, 'File' => $fileName, 'Size' => $fileSize, 'Path' => "$code/$dateDirName/$fileName");
                        if(pathinfo($fileName)['extension'] == 'zip')
                            $a_ToElaborate[] = $a_Entry;
                        else if(pathinfo($fileName)['extension'] == 'elab')
                            $a_Elaborated[] = $a_Entry;
                    }
                }
            }
        }
    }
} else {
    $_SESSION['Message']['Error'] = "La cartella $str_ImportFolder non è raggiungibile. Contattare l'amministratore per verficare l'esistenza o i permessi di accesso.";
}
echo $str_out;

?>

<div class="row-fluid">
    <form id="f_kria_download" action="">
        <div class="col-sm-12 table_label_H" style="background-color:#294A9C;color:white;">
    		IMPORTAZIONI DA SCARICARE
    	</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll" checked/></div>
        <div class="table_label_H col-sm-1">Codice import</div>
        <div class="table_label_H col-sm-1">Data</div>
        <div class="table_label_H col-sm-8">File importazione</div>
        <div class="table_label_H col-sm-1">Dimensioni</div>
        
		<div class="clean_row HSpace4"></div>
		
		<?php if(!empty($a_ToElaborate)): ?>
			<?php $i=1; ?>
    		<?php foreach($a_ToElaborate as $row): ?>
    			<div class="tableRow">
          			<div class="col-sm-1" style="text-align:center;padding:0">
            			<div class="table_caption_button col-sm-6" style="text-align:center;">
            				<input type="checkbox" name="checkbox[]" value="<?= $row['Path']; ?>" checked />
        				</div>
            			<div class="table_caption_H col-sm-6" style="text-align:center;">
            				<?= $i++; ?>
        				</div>
    				</div>
                    <div class="table_caption_H col-sm-1">
                    	<?= $row['Code']; ?>
                	</div>
                	<div class="table_caption_H col-sm-1">
                    	<?= $row['Date']; ?>
                	</div>
                	<div class="table_caption_H col-sm-8">
                    	<?= $row['File']; ?>
                	</div>
                	<div class="table_caption_H col-sm-1">
                    	<?= $row['Size']; ?>
                	</div>
    			</div>
    			
    			<div class="clean_row HSpace4"></div>
    		<?php endforeach; ?>
		    <div class="table_label_H HSpace4" style="height:8rem;">
    	    	<div style="padding-top:2rem;">
        	    	<?= ChkButton($aUserButton, 'act','<button id="kria_download" progress-tick="500" type="button" class="btn btn-success" style="width:16rem">Scarica importazioni</i></button>'); ?>
    	    	</div>
            </div>
		<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessuna importazione da scaricare
            </div>
            
            <div class="clean_row HSpace4"></div>
		<?php endif; ?>
		
    	<div class="col-sm-12" id="DIV_Progress" style="display:none;">
			<div class="table_label_H col-sm-12" style="background-color:#294A9C;color:white;">AVANZAMENTO DELL' OPERAZIONE</div>
			
			<div class="clean_row HSpace4"></div>
            
            <div class="col-sm-12 table_caption_H"  style="height:auto;text-align:center;padding:0;">
                <div class="progress" style="margin-bottom:0;">
        			<div id="progressbar" class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        		</div>
                <div id="DIV_Rows" class="col-sm-12">Importazioni scaricate: <span></span></div>
                <div id="DIV_Messages" class="col-sm-12"></div>
                <div id="DIV_Results" class="col-sm-12"></div>
            </div>
            
            <div class="clean_row HSpace4"></div>
		</div>

        <div class="col-sm-12 table_label_H" style="background-color: #294A9C;">
    		<a href="javascript:void(0);" id="heading" data-toggle="collapse" data-target="#collapse" aria-expanded="false" aria-controls="collapse" style="color:white;">
        		IMPORTAZIONI SCARICATE
        		<i class="fa fa-caret-down caret-toggle"></i>
			</a>
    	</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div id="accordion">
            <div class="table_label_H col-sm-1">Riga</div>
            <div class="table_label_H col-sm-1">Codice import</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-8">File importazione</div>
            <div class="table_label_H col-sm-1">Dimensioni</div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-12 collapse" id="collapse" aria-labelledby="heading" data-parent="#accordion" aria-expanded="false" style="height:0;">
      			<?php if(!empty($a_Elaborated)): ?>
	      			<?php $i=1; ?>
          			<?php foreach($a_Elaborated as $row): ?>
          				<div class="tableRow">
        	      			<div class="table_caption_H col-sm-1">
                				<?= $i++; ?>
            				</div>
                            <div class="table_caption_H col-sm-1">
                            	<?= $row['Code']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= $row['Date']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-8">
                            	<?= $row['File']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= $row['Size']; ?>
                        	</div>
                    	</div>
        			
        				<div class="clean_row HSpace4"></div>
          			<?php endforeach; ?>
      			<?php else: ?>
		            <div class="table_caption_H col-sm-12 text-center">
                    	Nessuna importazione scaricata
                    </div>
      			<?php endif; ?>
            </div>
        </div>
	</form>
</div>
<script src="<?= JS ?>/progressbar.js" type="text/javascript"></script>

<script type="text/javascript">

	$(document).ready(function () {

	    $('.collapse').on("show.bs.collapse", function(){
	    	$(".caret-toggle").toggleClass('fa-caret-up fa-caret-down');
	    });

	    $('.collapse').on("hide.bs.collapse", function(){
	    	$(".caret-toggle").toggleClass('fa-caret-up fa-caret-down');
	    });
	    
        $('#checkAll').click(function() {
            $('input[name=checkbox\\[\\]]').prop('checked', this.checked);
            $("#f_kria_download").trigger( "check" );
        });
        
        $('input[name=checkbox\\[\\]]').change(function() {
            $("#f_kria_download").trigger( "check" );
        });
	    
    	$("#f_kria_download").on('check', function(){
        	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
        		$('#kria_download').prop('disabled', false);
        	else
        		$('#kria_download').prop('disabled', true);
        });
	    
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
	    
	    $('#kria_download').on('click', function(){
	    console.log($('#f_kria_download').serialize());
	    	$('#DIV_Progress').show();
	    	progressBar_start('smb_kria_exe.php', this, $('#f_kria_download').serialize());
	    });
	    
        $('#kria_download').on('progressDone', function(e, data){
            $('#progressbar').removeClass('progress-bar-info progress-bar-striped active');
            console.log(data.Result);
            if(data.Result.Fail.length > 0){
            	$('#progressbar').addClass('progress-bar-warning');
            } else {
            	$('#progressbar').addClass('progress-bar-success');
            }
        	$.each(data.Result.Fail, function(index, obj) {
        		$('#DIV_Results').append('<div class="col-sm-3 table_caption_H alert-danger">'+obj.File+'</div><div class="col-sm-9 table_caption_H alert-danger text-left">'+obj.Message+'</div>')
            });
        	$.each(data.Result.Success, function(index, obj) {
        		$('#DIV_Results').append('<div class="col-sm-3 table_caption_H alert-success">'+obj.File+'</div><div class="col-sm-9 table_caption_H alert-success text-left">'+obj.Message+'</div>')
            });
        });
        $('#kria_download').on('progressFail', function(e, data){
            $('#progressbar').removeClass('progress-bar-info progress-bar-striped active');
            $('#progressbar').addClass('progress-bar-danger');
            $('#DIV_Messages').html(data.responseText);
        });
        $('#kria_download').on('progressGet', function(e, data){
            $('#DIV_Rows span').html(data.Contati + ' / ' + data.Totali);
            $('#DIV_Messages').html(data.Passo);
        });

	});

</script>

<?php
include(INC."/footer.php");

