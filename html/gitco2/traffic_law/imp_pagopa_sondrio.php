<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

function buildLinesArray($columnsArray, $csvSeparator, $fileStream){
    $n_CSVReadLine = 0;
    $a_IndexedLines = array();
    
    while (($a_CSVLine = fgetcsv($fileStream, 0, $csvSeparator)) !== false){
        foreach($a_CSVLine as $lineIndex => $lineValue){
            $encoding = mb_detect_encoding($lineValue,'UTF-8, ASCII, ISO-8859-1');
            $a_IndexedLines[$n_CSVReadLine][$columnsArray[$lineIndex]] = mb_convert_encoding($lineValue, 'UTF-8', $encoding);
        }
        $n_CSVReadLine++;
    }
    return $a_IndexedLines;
}

function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-question-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}

global $rs;

$ImportFile = CheckValue('ImportFile','s');

$CityId = $_SESSION['cityid'];
$path = PAYMENT_FOLDER."/$CityId/";
$str_Separator = ';';

$str_Error = '';
$n_FileCount=0;
$n_LineCount=0;
$a_files = array();
$a_DataLines = array();
$a_Errors = array();
$a_Warnings = array();
$a_FoundFines = array();
$a_FoundPayment = array();
$a_QueryData = array();


$a_CSVColumns = array (
    'CODICE_SERVIZIO',
    'CODICE_SOTTOSERVIZIO',
    'NUMERO_LISTA',
    'TIPO_IDENTIFICATIVO_UNIVOCO',
    'CODICE_FISCALE_DEBITORE',
    'CODICE_DEBITORE',
    'ANAGRAFICA_DEBITORE',
    'INDIRIZZO_DEBITORE',
    'CIVICO_DEBITORE',
    'CAP_DEBITORE',
    'LOCALITA_DEBITORE',
    'PROVINCIA_DEBITORE',
    'NAZIONE_DEBITORE',
    'EMAIL_DEBITORE',
    'PEC_DEBITORE',
    'ALTRO_RECAPITO',
    'CODICE_IDENTIFICATIVO_BOLLETTINO',
    'IDENTIFICATIVO_DEBITO',
    'IDENTIFICATIVO_DISPOSIZIONE',
    'SCADENZA',
    'DATA_INIZIO_VALIDITA',
    'DATA_FINE_VALIDITA',
    'TIPO_CAUSALE',
    'CAUSALE_VERSAMENTO',
    'IMPORTO',
    'DETTAGLIO_VOCI',
    'ANNO_RIFERIMENTO',
    'CAUSALE_BOLLETTINO',
    'DATI_SPECIFICI_RISCOSSIONE',
    'NOME_PDF_ALLEGATO',
    'NOME_PDF_OUTPUT',
    'OPERAZIONE',
    'DATA_EFFETTIVA_INCASSO',
    'DATA_ACCREDITO',
    'ABI_ORDINANTE',
    'NUMERO_PROVVISORIO',
    'TRN_RIVERSAMENTO'
);

if ($directory_handle = opendir($path)) {
    while (($file = readdir($directory_handle)) !== false) {
        if(isset(pathinfo($file)['extension']) && pathinfo($file)['extension'] == 'csv'){
            $a_files[$file] = '<a href="'.$str_CurrentPage.'&ImportFile='.$file.'"><span class="fa fa-upload"></span></a>';
        }
    }
    closedir($directory_handle);
} else $str_Error .= "Impossibile aprire la cartella delle importazioni.<br>";

if($ImportFile != ''){
    $fileStream = @fopen("$path/$ImportFile",  "r");
    if(is_resource($fileStream)){
        $a_CSVFirstLine = fgetcsv($fileStream, 0, $str_Separator);
        $a_CSVMissingColumns = array_diff($a_CSVColumns, $a_CSVFirstLine);
        
        if(empty($a_CSVMissingColumns)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                $a_IndexedLines = buildLinesArray($a_CSVColumns, $str_Separator, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $n_LineCount++;
                    $Nome = $IUV = $Importo = $DataIncasso = $DataAccredito = $FineId = $Causale = null;
                    
                    //IUV
                    if($a_CSVLine['CODICE_IDENTIFICATIVO_BOLLETTINO'] != ''){
                        $IUV = $a_CSVLine['CODICE_IDENTIFICATIVO_BOLLETTINO'];
                        $a_DataLines[$n_LineCount]['Iuv']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Iuv']['value'] = $IUV;
                        $a_QueryData[$n_LineCount]['Iuv'] = $IUV;
                    } else {
                        $a_DataLines[$n_LineCount]['Iuv']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Iuv assente.';
                    }
                    
                    //Nome
                    if($a_CSVLine['ANAGRAFICA_DEBITORE'] != ''){
                        $Nome = $a_CSVLine['ANAGRAFICA_DEBITORE'];
                        $a_DataLines[$n_LineCount]['Nome']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Nome']['value'] = $Nome;
                    } else {
                        $a_DataLines[$n_LineCount]['Nome']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Debitore assente.';
                    }
                    
                    //Causale
                    if($a_CSVLine['CAUSALE_BOLLETTINO'] != ''){
                        $Causale = $a_CSVLine['CAUSALE_BOLLETTINO'];
                        $a_DataLines[$n_LineCount]['Causale']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Causale']['value'] = $Causale;
                    }
                    
                    //Importo
                    if($a_CSVLine['IMPORTO'] != ''){
                        $Importo = $a_CSVLine['IMPORTO'];
                        $a_DataLines[$n_LineCount]['Importo']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Importo']['value'] = $Importo;
                    } else {
                        $a_DataLines[$n_LineCount]['Importo']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Importo assente.';
                    }
                    
                    //Data pagamento
                    if($a_CSVLine['DATA_EFFETTIVA_INCASSO'] != ''){
                        $DataIncasso = $a_CSVLine['DATA_EFFETTIVA_INCASSO'];
                        $a_DataLines[$n_LineCount]['DataIncasso']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['DataIncasso']['value'] = $DataIncasso;
                    } else {
                        $a_DataLines[$n_LineCount]['DataIncasso']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Data pagamento assente.';
                    }
                    
                    //Data accredito
                    if($a_CSVLine['DATA_ACCREDITO'] != ''){
                        $DataAccredito = $a_CSVLine['DATA_ACCREDITO'];
                        $a_DataLines[$n_LineCount]['DataAccredito']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['DataAccredito']['value'] = $DataAccredito;
                    } else {
                        $a_DataLines[$n_LineCount]['DataAccredito']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Data accredito assente.';
                    }
                    
                    //CONTROLLO VERBALI GIà ESISTENTI
                    if(isset($IUV)){
                        $rs_Fine = $rs->SelectQuery("SELECT Id FROM Fine WHERE CityId='$CityId' AND PagoPA1 = '$IUV' OR PagoPA2 = '$IUV' ORDER BY RegDate DESC");
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $a_FoundFines[] = $n_LineCount;
                            $r_Fine = $rs->getArrayLine($rs_Fine);
                            $FineId = $r_Fine['Id'];
                            $a_DataLines[$n_LineCount]['Associato']['value'] =
                            '<i class="fas fa-file tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Verbale associato (ID:'.$FineId.')" style="font-size:1.6rem;margin-top:0.3rem"></i>';
                        } else {
                            $a_Warnings[$n_LineCount][] = 'Nessun verbale trovato per questo IUV. Verrà salvato un pagamento non associato';
                        }
                    }
                    
                    //CONTROLLO PAGAMENTO GIà ESISTENTE SE HO TROVATO UN VERBALE COLLEGATO
                    if(isset($FineId,$DataIncasso)){
                        $rs_FinePayment = $rs->Select('FinePayment', "CityId='$CityId' AND FineId=$FineId AND PaymentDate='".DateInDB($DataIncasso)."'");
                        if(mysqli_num_rows($rs_FinePayment) > 0){
                            $a_FoundPayment[] = $n_LineCount;
                        }
                    }
                    
                    $a_DataLines[$n_LineCount]['Riga']['value'] = $n_LineCount;
                    $a_DataLines[$n_LineCount]['Riga']['error'] = empty($a_Errors[$n_LineCount]) ? (empty($a_Warnings[$n_LineCount]) ? 'S' : 'W') : 'D';
                }
            } else $str_Error = 'File CSV non valido per questa importazione.<br>Colonne previste: '.count($a_CSVColumns).', Colonne identificate: '.count($a_CSVFirstLine);
        } else $str_Error = 'File CSV non valido per questa importazione.<br>La struttura non presenta i seguenti campi: '.implode(', ', $a_CSVMissingColumns);
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;
?>
<div class="row-fluid">
	<div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota: questa importazione è stata creata per importare manualmente le rendicontazioni dei pagamenti per gli enti
                	che utilizzano il gestore PagoPA Sondrio.<br>
                	IMPORTANTE: i pagamenti di cui non si riesce a trovare un verbale associato a sistema vengono salvati come da bonificare. Ciò comporta all'introduzione di possibili
                	pagamenti da bonificare duplicati se si elabora lo stesso file più volte.</li>
            </ul>
        </div>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
	<form id="f_pagopa_sondrio" action="imp_pagopa_sondrio_exe.php" method="post" autocomplete="off">
		<input type="hidden" name="ImportFile" value="<?= $ImportFile; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="table_label_H col-sm-12">ELENCO FILE</div>
		
		<div class="clean_row HSpace4"></div>
		
		<?php if(!empty($a_files)): ?>
			<?php foreach($a_files as $file => $fileLink): ?>
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
		            <div class="table_label_H col-sm-3">Nome</div>
		            <div class="table_label_H col-sm-3">Causale</div>
		            <div class="table_label_H col-sm-1">Importo pag.</div>	
                    <div class="table_label_H col-sm-1">Data pag.</div>
                    <div class="table_label_H col-sm-1">Data accr.</div>
                    <div class="table_label_H col-sm-2">IUV</div>

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
        						<div class="table_caption_error table_caption_H col-sm-3">
        							<?= $line['Nome']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-3">
        							<?= $line['Causale']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['Importo']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['DataIncasso']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-1">
        							<?= $line['DataAccredito']['value'] ?? ''; ?>
        						</div>
        						<div class="table_caption_error table_caption_H col-sm-2">
        							<?= $line['Iuv']['value'] ?? ''; ?>
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
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-3">
        							<?= printIcon($line['Nome']['error'] ?? null); ?> <?= $line['Nome']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-3">
        							<?= printIcon($line['Causale']['error'] ?? null); ?> <?= $line['Causale']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['Importo']['error'] ?? null); ?> <?= $line['Importo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['DataIncasso']['error'] ?? null); ?> <?= $line['DataIncasso']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['DataAccredito']['error'] ?? null); ?> <?= $line['DataAccredito']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-2">
        							<?= printIcon($line['Iuv']['error'] ?? null); ?> <?= $line['Iuv']['value'] ?? ''; ?>
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
                	    		<button id="import" type="submit" class="btn btn-success" style="width:10rem; margin-top:2rem;">Importa</button>
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
<?php
include (INC . "/footer.php");