<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/function_import.php");
require_once(PGFN."/fn_imp_pagopa_spontaneous_type2.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

/** @var CLS_DB $rs */
//TODO viene fatto questo perchè anche se initialization.php lo fa già, menu_top.php reinizializza un'altra istanza $rs
//che quindi non avrà il charset impostato. Da rimuovere una volta che viene rimosso $rs da menut_top oppure se verrà già impostato nel costruttore di cls_db
$rs->SetCharset('utf8');

$path = PAYMENT_FOLDER."/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');

$CityId = $_SESSION['cityid'];

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

//Colonne documento di tipo 2
$a_CSVColumns = array (
    'ID',
    'AnnoImposta',
    'NumeroFattura',
    'AnnoEmissione',
    'ImportoDovuto',
    'QuintoCampo',
    'Importo',
    'DataScadenza',
    'DataVersamento',
    'DataAccredito',
    'DataRiversamento',
    'DataAssociazione',
    'DataInserimento',
    'Nominativo',
    'CodiceFiscale',
    'ModalitaPagamento',
    'TipoPagamento',
    'Cc',
    'Sottoservizio',
    'RiferimentoPraticaEsterna',
    'RiferimentoPraticaScollegata',
    'Flusso',
    'Quietanza',
    'Annullato',
    'Note',
    'Connettore',
    'SoftwareEsportazione',
    'ProvvisorioIncasso',
    'Prelevamento',
    'Causale',
    'TipoImpattoContabilita',
    'Consolidato',
    'DataApprovazione',
    'EnteBeneficiario',
    'Tassonomia'
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
        $a_CSVFirstLine = fgetcsv($fileStream, 0, IMP_PAGOPA_SPONTANEOUS_TYPE2_SEPARATOR);
        $a_CSVColumnsDiffs = compareColumns($a_CSVColumns, $a_CSVFirstLine); //Confronta le colonne previste con la prima riga del file
        
        if(empty($a_CSVColumnsDiffs)){
            if(count($a_CSVColumns) == count($a_CSVFirstLine)){
                $a_IndexedLines = buildLinesArray($a_CSVColumns, IMP_PAGOPA_SPONTANEOUS_TYPE2_SEPARATOR, $fileStream);
                
                foreach($a_IndexedLines as $a_CSVLine){
                    $n_LineCount++;
                    $ProtocolId = null;
                    $ProtocolYear = null;
                    $FifthField = "";
                    $Amount = 0;
                    $PaymentDate = "";
                    $Name = "";
                    $TaxCode = "";
                    $PaymentMode = "";
                    $PaymentType = "";
                    $FineId = null;
                    $ReasonNote = '';
                    
                    $FineId = 0;
                    
                    //NOMINATIVO
                    if($a_CSVLine['Nominativo'] != ''){
                        $Name = $a_CSVLine['Nominativo'];
                        $a_DataLines[$n_LineCount]['Nominativo']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Nominativo']['value'] = $Name;
                    } else {
                        $a_DataLines[$n_LineCount]['Nominativo']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Nominativo assente.';
                    }
                    
                    //QUINTO CAMPO
                    if($a_CSVLine['QuintoCampo'] != ''){
                        $FifthField = $a_CSVLine['QuintoCampo'];
                        $a_DataLines[$n_LineCount]['QuintoCampo']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['QuintoCampo']['value'] = $FifthField;
                    } else {
                        $a_DataLines[$n_LineCount]['QuintoCampo']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Quinto campo assente.';
                    }
                    
                    //IMPORTO
                    if($a_CSVLine['Importo'] != ''){
                        //String replace invece di number format perchè c'è bisogno dei decimali precisi
                        $Amount = str_replace(',','.',$a_CSVLine['Importo']);
                        $a_DataLines[$n_LineCount]['Importo']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Importo']['value'] = $Amount;
                    } else {
                        $a_DataLines[$n_LineCount]['Importo']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Importo assente.';
                    }
                    
                    //DATA VERSAMENTO
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['DataVersamento'])){
                        $PaymentDate = $FormattedDate->format('Y-m-d');
                        $a_DataLines[$n_LineCount]['DataVersamento']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['DataVersamento']['value'] = $a_CSVLine['DataVersamento'];
                    } else {
                        $a_DataLines[$n_LineCount]['DataVersamento']['error'] = 'D';
                        $a_DataLines[$n_LineCount]['DataVersamento']['value'] = $a_CSVLine['DataVersamento'];
                        $a_Errors[$n_LineCount][] = 'Data versamento assente o non valida.';
                    }
                    
                    //DATA ACCREDITO
                    if($FormattedDate = checkDateString('d/m/Y', $a_CSVLine['DataAccredito'])){
                        $PaymentDate = $FormattedDate->format('Y-m-d');
                        $a_DataLines[$n_LineCount]['DataAccredito']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['DataAccredito']['value'] = $a_CSVLine['DataAccredito'];
                    } else {
                        $a_DataLines[$n_LineCount]['DataAccredito']['error'] = 'D';
                        $a_DataLines[$n_LineCount]['DataAccredito']['value'] = $a_CSVLine['DataAccredito'];
                        $a_Errors[$n_LineCount][] = 'Data accredito assente o non valida.';
                    }
                    
                    //CODICE FISCALE
                    if($a_CSVLine['CodiceFiscale'] != ''){
                        $TaxCode = $a_CSVLine['CodiceFiscale'];
                        $a_DataLines[$n_LineCount]['CodiceFiscale']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['CodiceFiscale']['value'] = $TaxCode;
                    } else {
                        $a_DataLines[$n_LineCount]['CodiceFiscale']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Codice fiscale assente.';
                    }
                    
                    //MODALITA PAGAMENTO
                    if($a_CSVLine['ModalitaPagamento'] != ''){
                        $PaymentMode = $a_CSVLine['ModalitaPagamento'];
                        $a_DataLines[$n_LineCount]['ModalitaPagamento']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['ModalitaPagamento']['value'] = $PaymentMode;
                    } else {
                        $a_DataLines[$n_LineCount]['ModalitaPagamento']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Modalita pagamento assente.';
                    }
                    
                    //TIPO PAGAMENTO
                    if($a_CSVLine['TipoPagamento'] != ''){
                        $PaymentType = $a_CSVLine['TipoPagamento'];
                        $a_DataLines[$n_LineCount]['TipoPagamento']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['TipoPagamento']['value'] = $PaymentType;
                    } else {
                        $a_DataLines[$n_LineCount]['TipoPagamento']['error'] = 'W';
                        $a_Warnings[$n_LineCount][] = 'Tipo pagamento assente.';
                    }
                    
                    //CAUSALE
                    if($a_CSVLine['Causale'] != ''){
                        $ReasonNote = $a_CSVLine['Causale'];
                        $a_DataLines[$n_LineCount]['Causale']['error'] = 'S';
                        $a_DataLines[$n_LineCount]['Causale']['value'] = $ReasonNote;
                        //RECUPERO I PROTOCOLLI DALLA CAUSALE
                        $protocolArray = getFineDataFromNote($ReasonNote);
                        if($protocolArray != null)
                        {
                            $ProtocolId = $protocolArray[0];
                            $ProtocolYear = $protocolArray[1];
                        }
                    } else {
                        $a_DataLines[$n_LineCount]['Causale']['error'] = 'D';
                        $a_Errors[$n_LineCount][] = 'Causale assente.';
                    }
                    
                    //CONTROLLO VERBALI ASSOCIABILI
                    if(isset($ProtocolId, $ProtocolYear)){
                        $rs_Fine = $rs->Select('Fine', "CityId='$CityId' AND ProtocolId=$ProtocolId AND ProtocolYear=$ProtocolYear AND StatusTypeId NOT IN('".implode(',', STATUSTYPEID_VERBALI_STATI_FINALI)."')", 'RegDate DESC');
                        if(mysqli_num_rows($rs_Fine) > 0){
                            $r_Fine = $rs->getArrayLine($rs_Fine);
                            $FineId = $r_Fine['Id'];
                            $a_DataLines[$n_LineCount]['Associato']['value'] =
                            '<i class="fas fa-fw fa-file tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Verbale associato (ID:'.$FineId.')" style="font-size:1.6rem;margin-top:0.3rem"></i>';
                        }
                    }
                    
                    //CONTROLLO PAGAMENTO GIà ESISTENTE
                    if($PaymentDate != "" && $Amount > 0 && $Name != "" && $ReasonNote != "" && $FifthField != ""){
                        $b_HaQuintoCampo = $b_HaQuintoCampoUguale = false;
                        $NameSQL = mysqli_real_escape_string($rs->conn,$Name);
                        $ReasonNoteSQL = mysqli_real_escape_string($rs->conn,$ReasonNote);
                        $rs_FinePayment = $rs->Select('FinePayment', "PaymentDate='$PaymentDate' AND Amount=$Amount AND Name='$NameSQL' AND Note='$ReasonNoteSQL'");
                        
                        //Se trova pagamenti esamina il quinto campo
                        while($r_FinePayment = $rs->getArrayLine($rs_FinePayment)){
                            if($r_FinePayment['ImportReceiptNumber'] != ''){
                                $b_HaQuintoCampo = true;
                                if($r_FinePayment['ImportReceiptNumber'] == $FifthField){
                                    $b_HaQuintoCampoUguale = true;
                                }
                            }
                        }
                        
                        //Se trova un Pagamento duplicato con lo stesso quinto campo allora colora la riga di rosso
                        if($b_HaQuintoCampoUguale){
                            $a_FoundPayment[$n_LineCount] = 'table_caption_error';
                            $a_DataLines[$n_LineCount]['Associato']['value'] .=
                            '<i class="fas fa-fw fa-info-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Trovato pagamento con stesso quinto campo, verrà salvato come da bonificare solo se il flag di forzatura è attivo." style="font-size:1.6rem;margin-top:0.3rem"></i>';
                        } 
                        //Se trova un Pagamento duplicato ma con quinto campo diverso la colora di giallo
                        elseif($b_HaQuintoCampo){
                            $a_FoundPayment[$n_LineCount] = 'table_caption_warning';
                            $a_DataLines[$n_LineCount]['Associato']['value'] .=
                            '<i class="fas fa-fw fa-info-circle tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Trovato pagamento ma con quinto campo diverso, verrà salvato come da bonificare." style="font-size:1.6rem;margin-top:0.3rem"></i>';
                        }
                    }
                    
                    
                    $a_DataLines[$n_LineCount]['Riga']['value'] = $n_LineCount;
                    $a_DataLines[$n_LineCount]['Riga']['error'] = empty($a_Errors[$n_LineCount]) ? (empty($a_Warnings[$n_LineCount]) ? 'S' : 'W') : 'D';
                }
            }
        } else $str_Error = 'File CSV non valido per questa importazione. La struttura presenta le seguenti differenze:'.
            (!empty($a_CSVColumnsDiffs['extra']) ? '<br><br>Colonne aggiuntive: '.implode(', ', $a_CSVColumnsDiffs['extra']) : '').
            (!empty($a_CSVColumnsDiffs['missing']) ? '<br><br>Colonne mancanti: '.implode(', ', $a_CSVColumnsDiffs['missing']) : '');
    } else $str_Error = "Errore nell'apertura del file: $ImportFile.";
}

if($str_Error != '') $_SESSION['Message']['Error'] = $str_Error;

echo $str_out;
?>

<div class="row-fluid">
    <div class="col-sm-12 alert alert-warning" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota bene:
                <ul style="list-style-position: inside;">
                    <li>Le righe colorate di rosso simboleggiano pagamenti già presenti a sistema con gli stessi dati e stesso quinto campo. Non verrano importati a meno che il flag <strong>Forza il salvataggio di pagamenti duplicati come "da bonificare"</strong> sia acceso, in quel caso verrano importati come "da bonificare"</li>
                    <li>Le righe colorate di giallo simboleggiano pagamenti già presenti a sistema con gli stessi dati ma con quinto campo diverso. Verranno importati come pagamenti "da bonificare"</li>
                </ul>
            </li></ul>
        </div>
    </div>
	<form id="f_pagopa_spontaneous" action="imp_pagopa_spontaneous_type2_exe.php" method="post" autocomplete="off">
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
		            <div class="table_label_H col-sm-1">Data accr.</div>
                    <div class="table_label_H col-sm-1">Importo pag.</div>
                    <div class="table_label_H col-sm-1">Modalità pag.</div>
                    <div class="table_label_H col-sm-1">Tipo pag.</div>
                    <div class="table_label_H col-sm-1">Nominativo</div>
                    <div class="table_label_H col-sm-1">CF</div>
                    <div class="table_label_H col-sm-1">Quinto campo</div>
                    <div class="table_label_H col-sm-3">Causale</div>
					<?php foreach ($a_DataLines as $lineIndex => $line): ?>
						<div class="tableRow">
							<?php if(isset($a_FoundPayment[$lineIndex])): ?>
        						<div class="col-sm-1" style="padding: 0">
        							<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-6">
        								<?= printIcon($line['Riga']['error'] ?? null); ?> <?= $line['Riga']['value'] ?? ''; ?>
        							</div>
        							<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H text-center col-sm-6">
        								<?= $line['Associato']['value'] ?? ''; ?>
        							</div>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1">
        							<?= printIcon($line['DataVersamento']['error'] ?? null); ?> <?= $line['DataVersamento']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1">
        							<?= printIcon($line['DataAccredito']['error'] ?? null); ?> <?= $line['DataAccredito']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1">
        							<?= printIcon($line['Importo']['error'] ?? null); ?> <?= $line['Importo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1">
        							<?= printIcon($line['ModalitaPagamento']['error'] ?? null); ?> <?= $line['ModalitaPagamento']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1">
        							<?= printIcon($line['TipoPagamento']['error'] ?? null); ?> <?= $line['TipoPagamento']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Nominativo']['error'] ?? null); ?> <?= $line['Nominativo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['CodiceFiscale']['error'] ?? null); ?> <?= $line['CodiceFiscale']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['QuintoCampo']['error'] ?? null); ?> <?= $line['QuintoCampo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= $a_FoundPayment[$lineIndex] ?> table_caption_H col-sm-3" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Causale']['error'] ?? null); ?> <?= htmlspecialchars($line['Causale']['value'] ?? ''); ?>
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
        							<?= printIcon($line['DataVersamento']['error'] ?? null); ?> <?= $line['DataVersamento']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['DataAccredito']['error'] ?? null); ?> <?= $line['DataAccredito']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['Importo']['error'] ?? null); ?> <?= $line['Importo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['ModalitaPagamento']['error'] ?? null); ?> <?= $line['ModalitaPagamento']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1">
        							<?= printIcon($line['TipoPagamento']['error'] ?? null); ?> <?= $line['TipoPagamento']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Nominativo']['error'] ?? null); ?> <?= $line['Nominativo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['CodiceFiscale']['error'] ?? null); ?> <?= $line['CodiceFiscale']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['QuintoCampo']['error'] ?? null); ?> <?= $line['QuintoCampo']['value'] ?? ''; ?>
        						</div>
        						<div class="<?= in_array($lineIndex, $a_FoundFines) ? 'alert-success ' : ''; ?>table_caption_H col-sm-3" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        							<?= printIcon($line['Causale']['error'] ?? null); ?> <?= htmlspecialchars($line['Causale']['value'] ?? ''); ?>
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
					
					<div class="col-sm-4 BoxRowLabel">
						Forza il salvataggio di pagamenti duplicati come "da bonificare"
					</div>
					<div class="col-sm-1 BoxRowCaption">
						<input type="checkbox" value="1" name="ForceDuplicate">
					</div>
					<div class="col-sm-7 BoxRowHTitle"></div>
					
					<div class="clean_row HSpace4"></div>
					
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
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css({"background-color" : "#cfeaf7c7", "color" : "black"});
  	});
  	$(".tableRow").mouseout(function(){
  		$( this ).find( '.table_caption_H, .table_caption_button' ).css({"background-color" : "", "color" : ""});
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
require_once(INC . "/footer.php");
