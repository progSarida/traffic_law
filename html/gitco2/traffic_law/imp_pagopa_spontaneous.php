<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_pagopa_spontaneous.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

/** @var CLS_DB $rs */
//TODO viene fatto questo perchè anche se initialization.php lo fa già, menu_top.php reinizializza un'altra istanza $rs
//che quindi non avrà il charset impostato. Da rimuovere una volta che viene rimosso $rs da menut_top oppure se verrà già impostato nel costruttore di cls_db
$rs->SetCharset('utf8');

$path = PAYMENT_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');

$str_Error = '';
$n_FileCount = 0;
$n_LineCount = 0;
$a_FileLinks = array();
$a_DataLines = array();
$a_Errors = array();
$a_Warnings = array();
$a_IndexedLines = array();
$a_FoundFines = array();
$a_FoundPayment = array();

$a_CSVColumns = array (
    'Data pag.',
    'Importo pag.',
    'Verbale',
    'Data accert.',
    'Targa',
    'Pagante',
    'Contatto pagante',
);

if ($directory_handle = opendir($path)) {
    while (($file = readdir($directory_handle)) !== false) {
        if(isset(pathinfo($file)['extension']) && pathinfo($file)['extension'] == 'csv'){
            $a_FileLinks[$file] = ChkButton($aUserButton, 'imp','<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>');
        }
    }
    closedir($directory_handle);
}

if($ImportFile != ''){
    $fileStream = @fopen($path.$ImportFile,  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, IMP_PAGOPA_SPONTANEOUS_SEPARATOR);
        $a_CSVColumnsDiffs = compareColumns($a_CSVColumns, $a_CSVFirstLine); //Confronta le colonne previste con la prima riga del file
        
        if(empty($a_CSVColumnsDiffs)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                $a_IndexedLines = buildLinesArray($a_CSVColumns, IMP_PAGOPA_SPONTANEOUS_SEPARATOR, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $n_LineCount++;
                    $ProtocolId = $ProtcolYear = $VehiclePlate = $FineDate = $PaymentDate = $Amount = $Name = $FineId = null;
                    
                    //PROTOCOLLO
                    if(checkFineProtocol($a_CSVLine['Verbale'])){
                        $a_DataLines[$n_LineCount]['Verbale']['value'] = $a_CSVLine['Verbale'];
                        $a_DataLines[$n_LineCount]['Verbale']['error'] = 'S';
                        list($ProtocolId, $ProtcolYear) = explode('/', $a_CSVLine['Verbale']);
                    } else {
                        $a_DataLines[$n_LineCount]['Verbale']['value'] = $a_CSVLine['Verbale'];
                        $a_DataLines[$n_LineCount]['Verbale']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Identificativo verbale non valido.';
                    }
                    
                    //TARGA
                    if($a_CSVLine['Targa'] != ''){
                        $VehiclePlate = $a_CSVLine['Targa'];
                        $a_DataLines[$n_LineCount]['Targa']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Targa']['value'] = $VehiclePlate;
                    } else {
                        $a_DataLines[$n_LineCount]['Targa']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Targa assente.';
                    }
                    
                    //DATA ACCERT.
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['Data accert.'])){
                        $a_DataLines[$n_LineCount]['Data accert.']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Data accert.']['value'] = $a_CSVLine['Data accert.'];
                    } else {
                        $a_DataLines[$n_LineCount]['Data accert.']['error'] = 'W';
                        $a_DataLines[$n_LineCount]['Data accert.']['value'] = $a_CSVLine['Data accert.'];
                        $a_Warnings[$n_LineCount][] = 'Data violazione assente o non valida.';
                    }
                    
                    //DATA PAG.
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['Data pag.'])){
                        $PaymentDate = $FormattedDate->format('Y-m-d');
                        $a_DataLines[$n_LineCount]['Data pag.']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Data pag.']['value'] = $a_CSVLine['Data pag.'];
                    } else {
                        $a_DataLines[$n_LineCount]['Data pag.']['error'] = 'D';
                        $a_DataLines[$n_LineCount]['Data pag.']['value'] = $a_CSVLine['Data pag.'];
                        $a_Errors[$n_LineCount][] = 'Data pagamento assente o non valida.';
                    }
                    
                    //IMPORTO PAG.
                    if($a_CSVLine['Importo pag.'] != ''){
                        $Amount = number_format((float)$a_CSVLine['Importo pag.'],2,'.','');
                        $a_DataLines[$n_LineCount]['Importo pag.']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Importo pag.']['value'] = $a_CSVLine['Importo pag.'];
                    } else {
                        $a_DataLines[$n_LineCount]['Importo pag.']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Importo assente.';
                    }
                    
                    //PAGANTE
                    if($a_CSVLine['Pagante'] != ''){
                        $Name = mysqli_real_escape_string($rs->conn, $a_CSVLine['Pagante']);
                        $a_DataLines[$n_LineCount]['Pagante']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Pagante']['value'] = $a_CSVLine['Pagante'];
                    } else {
                        $a_DataLines[$n_LineCount]['Pagante']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Pagante assente.';
                    }
                    
                    //CONTATTO PAGANTE
                    if($a_CSVLine['Contatto pagante'] != ''){
                        $a_DataLines[$n_LineCount]['Contatto pagante']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Contatto pagante']['value'] = $a_CSVLine['Contatto pagante'];
                    } else {
                        $a_DataLines[$n_LineCount]['Contatto pagante']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Contatto pagante assente.';
                    }
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($VehiclePlate,$ProtocolId,$ProtcolYear)){
                        $rs_Fine = $rs->Select('Fine', "CityId='{$_SESSION['cityid']}' AND ProtocolId=$ProtocolId AND ProtocolYear=$ProtcolYear AND VehiclePlate='$VehiclePlate'", 'RegDate DESC');
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $a_FoundFines[] = $n_LineCount;
                            $r_Fine = $rs->getArrayLine($rs_Fine);
                            $FineId = $r_Fine['Id'];
                            $a_DataLines[$n_LineCount]['Associato']['value'] =
                            '<i class="fas fa-file tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Verbale associato (ID:'.$FineId.')" style="font-size:1.6rem;margin-top:0.3rem"></i>';
                        }
                    }
                    
                    //CONTROLLO PAGAMENTO GIà ESISTENTE SE HO TROVATO UN VERBALE COLLEGATO
                    if(isset($FineId,$PaymentDate,$Amount,$Name)){
                        $rs_FinePayment = $rs->Select('FinePayment', "FineId=$FineId AND PaymentDate='$PaymentDate' AND Amount='$Amount' AND Name='$Name'", 'RegDate DESC');
                        if(mysqli_num_rows($rs_FinePayment) > 0){
                            $a_FoundPayment[] = $n_LineCount;
                        }
                    }

                    $a_DataLines[$n_LineCount]['Riga']['value'] = $n_LineCount;
                    $a_DataLines[$n_LineCount]['Riga']['error'] = empty($a_Errors[$n_LineCount]) ? (empty($a_Warnings[$n_LineCount]) ? 'S' : 'W') : 'D';
                }
                
            } else $str_Error = 'File CSV non valido per questa importazione.<br>Colonne previste: '.count($a_CSVColumns).', Colonne identificate: '.count($a_CSVFirstLine);
        } else $str_Error = 'File CSV non valido per questa importazione. La struttura presenta le seguenti differenze:'.
            (!empty($a_CSVColumnsDiffs['extra']) ? '<br><br>Colonne aggiuntive: '.implode(', ', $a_CSVColumnsDiffs['extra']) : '').
            (!empty($a_CSVColumnsDiffs['missing']) ? '<br><br>Colonne mancanti: '.implode(', ', $a_CSVColumnsDiffs['missing']) : '');
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_pagopa_spontaneous" action="imp_pagopa_spontaneous_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="ImportFile" value="<?= $ImportFile; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="table_label_H col-sm-12">ELENCO FILE</div>
		
		<div class="clean_row HSpace4"></div>
		<?php if(!empty($a_FileLinks)): ?>
			<?php foreach($a_FileLinks as $file => $fileLink): ?>
				<?php $n_FileCount++; ?>
				<div class="tableRow">
		            <div class="table_caption_H col-sm-1"><?= $n_FileCount; ?></div>
                	<div class="table_caption_H col-sm-10"><?= $file; ?></div>
                	<div class="table_caption_button col-sm-1"><?= $fileLink; ?></div>
				</div>
				
            	<div class="clean_row HSpace4"></div>
			<?php endforeach; ?>
			<?php if($ImportFile != ''): ?>
				<?php if($str_Error == ''): ?>
					<div class="table_label_H col-sm-1">Riga</div>
		            <div class="table_label_H col-sm-1">Data pag.</div>
		            <div class="table_label_H col-sm-1">Importo pag.</div>	
                    <div class="table_label_H col-sm-2">Verbale</div>
                    <div class="table_label_H col-sm-1">Data accert.</div>
                    <div class="table_label_H col-sm-1">Targa</div>
                    <div class="table_label_H col-sm-3">Pagante</div>
                    <div class="table_label_H col-sm-2">Contatto pagante</div>
					<?php foreach ($a_DataLines as $lineIndex => $line): ?>
						<div class="tableRow">
							<?php if(in_array($lineIndex, $a_FoundPayment)): ?>
        						<div class="col-sm-1" style="padding: 0">
        							<div class="table_caption_error table_caption_H col-sm-6">
        								<?= $line['Riga']['value'] ?? ''; ?>
        							</div>
        							<div class="table_caption_error table_caption_H text-center col-sm-6">
        								<?= $line['Associato']['value'] ?? ''; ?>
        							</div>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Data pag.']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Importo pag.']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2">
        							<?= $line['Verbale']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Data accert.']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Targa']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-3">
        							<?= $line['Pagante']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2">
        							<?= $line['Contatto pagante']['value'] ?? ''; ?>
        						</div>
        					<?php else: ?>
        						<div class="col-sm-1" style="padding: 0">
        							<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-6">
        								<?= printIcon($line['Riga']['error'] ?? null); ?> <?= $line['Riga']['value'] ?? ''; ?>
        							</div>
        							<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H text-center col-sm-6">
        								<?= $line['Associato']['value'] ?? ''; ?>
        							</div>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['Data pag.']['error'] ?? null); ?> <?= $line['Data pag.']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['Importo pag.']['error'] ?? null); ?> <?= $line['Importo pag.']['value'] ?? ''; ?> 
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-2">
        							<?= printIcon($line['Verbale']['error'] ?? null); ?> <?= $line['Verbale']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['Data accert.']['error'] ?? null); ?> <?= $line['Data accert.']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['Targa']['error'] ?? null); ?> <?= $line['Targa']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-3" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Pagante']['error'] ?? null); ?> <?= $line['Pagante']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Contatto pagante']['error'] ?? null); ?> <?= $line['Contatto pagante']['value'] ?? ''; ?>
        						</div>
        					<?php endif; ?>
						</div>
						
						<div class="clean_row HSpace4"></div>
					<?php endforeach; ?>
					
					<?php if(!empty($a_Errors) || !empty($a_Warnings)): ?>
						<div class="clean_row HSpace48"></div>	
						
            			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
            			
            			<div class="clean_row HSpace4"></div>
            			
			            <div class="table_label_H col-sm-1">Riga</div>
		            	<div class="table_label_H col-sm-11">Avviso</div>	
            			
            			<div class="clean_row HSpace4"></div>
            			<?php foreach($a_Errors as $errorLine => $errors): ?>
            				<?php foreach($errors as $errorMessage): ?>
			                    <div class="table_caption_H col-sm-1 alert-danger"><?= $errorLine; ?></div>
                        		<div class="table_caption_H col-sm-11 alert-danger"><?= $errorMessage; ?></div>
                        		
                        		<div class="clean_row HSpace4"></div>
            				<?php endforeach; ?>
            			<?php endforeach; ?>
            			<?php foreach($a_Warnings as $warningLine => $warnings): ?>
            				<?php foreach($warnings as $warningMessage): ?>
			                    <div class="table_caption_H col-sm-1 alert-warning"><?= $warningLine; ?></div>
                        		<div class="table_caption_H col-sm-11 alert-warning"><?= $warningMessage; ?></div>
                        		
                        		<div class="clean_row HSpace4"></div>
            				<?php endforeach; ?>
            			<?php endforeach; ?>
					<?php endif; ?>
					
	        	    <div class="table_label_H HSpace4" style="height:8rem;">
	        	    	<div class="col-sm-3"></div>
	        	    	<div class="col-sm-6">
		        	    	<?php if(empty($a_Errors)): ?>
                	    		<?= ChkButton($aUserButton, 'imp','<button id="import" type="submit" class="btn btn-success" style="width:10rem; margin-top:2rem;">Importa</button>'); ?>
                	    	<?php endif;?>
	        	    	</div>
	        	    	<div class="col-sm-3"></div>
                    </div>
				<?php else: ?>
	                <div class="text-center bg-danger-dark col-sm-12" style="height:auto;">ERRORE: <?= $str_Error; ?></div>
                
                	<div class="clean_row HSpace4"></div>	
				<?php endif; ?>
			<?php else: ?>
                <div class="table_label_H col-sm-12">SCEGLIERE UN FILE PER L'IMPORTAZIONE</div>
                
                <div class="clean_row HSpace4"></div>	
			<?php endif; ?>
		<?php else: ?>
            <div class="table_caption_H col-sm-11">Nessun file presente</div>
            <div class="table_caption_button col-sm-1"></div>
		<?php endif; ?>

	</form>
</div>
<script>
$(document).ready(function () {
  	$(".tableRow").mouseover(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
  	});
  	$(".tableRow").mouseout(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
  	});
	
    $('#f_pagopa_spontaneous').on('submit', function(e){
    	if (confirm("Si stanno per importare i dati mostrati. Continuare?")){
			$('#import').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
			$('#import').prop('disabled', true);
    	} else {
        	e.preventDefault();
        	return false;
    	}
    });
});
</script>
<?php
include (INC . "/footer.php");
