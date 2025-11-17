<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_exp_maggioli_xml.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_mapEsitiNotifica = unserialize(MAGGIOLIEXP_MAP_ESITINOTIFICA);
$a_mapTipologiaAnagrafica = unserialize(MAGGIOLIEXP_MAP_TIPOLOGIAANAGRAFICA);
$a_codiceCliente = unserialize(MAGGIOLIEXP_CODICI_CLIENTE);
$a_prefissiUID = unserialize(MAGGIOLIEXP_UID_PREFIXES);
$a_tipiEsportazioneMaggioli = unserialize(MAGGIOLIEXP_EXPORTTYPES);

$PageTitle = CheckValue('PageTitle','s');

$RowCount = $RowNumber = 0;
$a_Errors = array();
$b_blockSearch = false;

if(!array_key_exists($_SESSION['cityid'], $a_codiceCliente)){
    $_SESSION['Message']['Error'] = "L'ente in uso non dispone di un codice cliente Maggioli per poter usare questa procedura.";
    $b_blockSearch = true;
}

if ($Search_Type > 0 && !empty($s_TypePlate && !$b_blockSearch)){
    
    $str_Where = "1=1 AND F.CityId='{$_SESSION['cityid']}'";
    
    if ($Search_Year != ''){
        $str_Where .= " AND F.ProtocolYear=".$Search_Year;
    }
    if ($Search_FromFineDate != "") {
        $str_Where .= " AND DATE(@RegDate)>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_Where .= " AND DATE(@RegDate)<='".DateInDB($Search_ToFineDate)."'";
    }
    if ($s_TypePlate == "N") {
        $str_Where .= " AND F.CountryId='Z000'";
        $docsPath = NATIONAL_FINE."/{$_SESSION['cityid']}";
    } else {
        $str_Where .= " AND F.CountryId!='Z000'";
        $docsPath = FOREIGN_FINE."/{$_SESSION['cityid']}";
    }
    
    switch($Search_Type){
        case 1:
            $view = EXP_MAGGIOLI_ANAGRAFICHE_XML;
            $regDateColumn = 'COALESCE(FT.ReceiveDate, FT.RegDate)';
            break;
        case 2:
            $view = EXP_MAGGIOLI_NOTIFICHE_XML;
            $regDateColumn = 'FN.RegDate';
            break;
        case 3:
            $view = EXP_MAGGIOLI_PAGAMENTI_XML;
            $regDateColumn = 'FP.RegDate';
            break;
        case 4:
            $view = EXP_MAGGIOLI_SPESENOTIFICA_XML;
            $regDateColumn = 'FH.SendDate';
            break;
        case 5:
            $view = EXP_MAGGIOLI_DOCUMENTALE_XML;
            $regDateColumn = 'FD.VersionDate';
            //Dato che questa vista ha una union a cui andrei ad agganciare la str_Where che filtra
            //per una colonna inerente solo alla prima select, la sostituisco prima della sostituzione generale
            $unionWhere = array('FinePayment' => str_replace('@RegDate', 'FP.RegDate', $str_Where));
            break;
        default:
            $_SESSION['Message']['Error'] = 'Tipo esportazione non riconosciuto.';
    }
    
    if(isset($view)){
        $cls_view = new CLS_VIEW($view);
        $cls_view->unionWheres = $unionWhere ?? null;
        
        $a_ToReplace = array(
            "@MaggioliCode" => $a_codiceCliente[$_SESSION['cityid']],
            "@Regexp" => MAGGIOLIEXP_UID_REGEX,
            "@UidPrefix" => $a_prefissiUID[$_SESSION['cityid']] ?? '???',
            "@RegDate" => $regDateColumn,
            "@ExclusionDate" => '2023-05-05',   //Data esportazioni già effettuate. Bug2746
        );
        
        $query = strtr($cls_view->generateSelect($str_Where, null, 'QUERY_Id DESC, STR_TO_DATE(QUERY_RegDate, "%d/%m/%Y") DESC'), $a_ToReplace);
        
        $rs_Table = $rs->SelectQuery($query);
        $RowNumber = mysqli_num_rows($rs_Table);
    }
}

$a_ExportedFiles = $rs->getResults($rs->SelectQuery("
    SELECT EF.*, ET.Name AS TypeName
    FROM ExportedFiles EF 
    JOIN ExportType ET ON EF.Type = ET.Id
    WHERE CityId = '{$_SESSION['cityid']}'
    AND Type IN(".implode(',', $a_tipiEsportazioneMaggioli).")
    ORDER BY EF.Date DESC, EF.Id DESC, EF.Type DESC"));

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_exp_maggioli" action="exp_maggioli_xml.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="col-sm-12 table_label_H" style="background-color: #294A9C;">
    		<a href="javascript:void(0);" id="heading" data-toggle="collapse" data-target="#collapse" aria-expanded="false" aria-controls="collapse" style="color:white;">
        		ESPORTAZIONI EFFETTUATE
        		<i class="fa fa-caret-down caret-toggle"></i>
			</a>
    	</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div id="accordion">
        	<div class="table_label_H col-sm-1">Riga</div>
            <div class="table_label_H col-sm-3">Archivio esportato</div>
            <div class="table_label_H col-sm-1">Tipo</div>
            <div class="table_label_H col-sm-1">Data</div>
            <div class="table_label_H col-sm-1">Utente</div>
            <div class="table_label_H col-sm-1">Quantità</div>
            <div class="table_label_H col-sm-2">Note</div>
            <div class="table_label_H col-sm-1">Da data reg.</div>
            <div class="table_label_H col-sm-1">A data reg.</div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-12 collapse" id="collapse" aria-labelledby="heading" data-parent="#accordion" aria-expanded="false" style="height:0;max-height:14rem;overflow:auto;">
      			<?php if(!empty($a_ExportedFiles)): ?>
	      			<?php $i=1; ?>
          			<?php foreach($a_ExportedFiles as $row): ?>
          				<div class="tableRow">
        	      			<div class="table_caption_H col-sm-1">
                				<?= $i++; ?>
            				</div>
                            <div class="table_caption_H col-sm-3">
                            	<?= $row['Name']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            	<?= $row['TypeName']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= DateOutDB($row['Date']); ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= $row['UserId']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= $row['RowsCount']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-2">
                            	<?= $row['Note']; ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= DateOutDB($row['FromDate']); ?>
                        	</div>
                        	<div class="table_caption_H col-sm-1">
                            	<?= DateOutDB($row['ToDate']); ?>
                        	</div>
                    	</div>
        			
        				<div class="clean_row HSpace4"></div>
          			<?php endforeach; ?>
      			<?php else: ?>
		            <div class="table_caption_H col-sm-12 text-center">
                    	Nessuna esportazione effettuata
                    </div>
      			<?php endif; ?>
            </div>
        </div>
        
        <div class="col-sm-12 table_label_H" style="background-color:#294A9C;color:white;">
    		CREA ESPORTAZIONE
    	</div>
    	
    	<div class="clean_row HSpace4"></div>
		
		<div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array('N' => 'Nazionali', 'F' => 'Estere'), true, 'TypePlate', 'TypePlate', $s_TypePlate) ?>
            </div>
        	<div class="col-sm-2 BoxRowLabel">
            	Tipo di flusso
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect(array(1 => 'Anagrafiche', 2 => 'Notifiche', 3 => 'Pagamenti', 4 => 'Spese notifica', 5 => 'Documenti'), true, 'Search_Type', 'Search_Type', $Search_Type, false); ?>
            </div>
        	<div class="col-sm-1 BoxRowLabel">
            	Anno
            </div>
            <div class="col-sm-1 BoxRowCaption font_small">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_Year', 'Search_Year', $Search_Year != '' ? $Search_Year : $_SESSION['year'], true); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data reg.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= (empty($Search_FromFineDate) && $Search_Type <= 0 && empty($s_TypePlate)) ? date("d/m/Y", strtotime("-1 months")) : $Search_FromFineDate; ?>" name="Search_FromFineDate" id="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data reg.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= (empty($Search_ToFineDate) && $Search_Type <= 0 && empty($s_TypePlate)) ? date("d/m/Y") : $Search_ToFineDate; ?>" name="Search_ToFineDate" id="Search_ToFineDate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
        	<div class="col-sm-12 BoxRowLabel">
            </div>
		</div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:4.5rem">
            	<button type="button" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" style="margin-top:0;width:100%;height:100%"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>

    	<div class="clean_row HSpace4"></div>
    	
        <div class="table_label_H col-sm-1">Riga</div>
        <div class="table_label_H col-sm-2">Riferimento</div>
        <div class="table_label_H col-sm-1">Data viol.</div>
        <div class="table_label_H col-sm-1">Targa</div>
    	<?php switch($Search_Type): 
            case 1: ?>
                <div class="table_label_H col-sm-2">Nominativo</div>
                <div class="table_label_H col-sm-2">Dati di nascita</div>
            	<div class="table_label_H col-sm-1">Tipologia</div>
            <?php break; ?>
            <?php case 2: ?>
            	<div class="table_label_H col-sm-1">Data notifica</div>
            	<div class="table_label_H col-sm-2">Esito</div>
                <div class="table_label_H col-sm-2">Rif. Raccomandata</div>
            <?php break; ?>
			<?php case 3: ?>
            	<div class="table_label_H col-sm-2">Data reg. pagamento</div>
            	<div class="table_label_H col-sm-2">Data pagamento</div>
                <div class="table_label_H col-sm-1">Importo pagato</div>
            <?php break; ?>
			<?php case 4: ?>
            	<div class="table_label_H col-sm-3">Rif. Raccomandata</div>
                <div class="table_label_H col-sm-2">Spese notifica</div>
            <?php break; ?>
			<?php case 5: ?>
            	<div class="table_label_H col-sm-3">Documento</div>
                <div class="table_label_H col-sm-2">Descrizione</div>
            <?php break; ?>
            <?php default: ?>
            	<div class="table_label_H col-sm-5"></div>
        <?php endswitch; ?>
        <div class="table_label_H col-sm-1">Data reg.</div>
        <div class="table_label_H col-sm-1"></div>
        
        <?php if($Search_Type <= 0 || empty($s_TypePlate) || $b_blockSearch): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere la nazionalità della targa e il tipo di estrazione da effettuare.
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php while ($r_Table = $rs->getArrayLine($rs_Table)): ?>
        			<?php $RowCount++; $b_FixTrepsasser = false; ?>
        			<div class="clean_row HSpace4"></div>
        			
                    <div class="tableRow">
            			<div class="table_caption_H col-sm-1">
            				<?= $RowCount; ?>
        				</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $r_Table['CodiceUID']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                        	<?= $r_Table['QUERY_FineDate']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-1">
                        	<?= $r_Table['QUERY_VehiclePlate']; ?>
                    	</div>
                    	<?php switch($Search_Type): 
                            case 1: ?>
                                <div class="table_caption_H col-sm-2">
                                	<?= $r_Table['Nominativo']; ?>
                            	</div>
                                <div class="table_caption_H col-sm-2">
                                	<?php if(!empty($r_Table['DataNascita']) && !empty($r_Table['LocalitaNascita'])): ?>
                                		<?= printIcon('S'); ?> 
                                	<?php else: ?>
                                		<?= printIcon('D'); ?> 
                                		<?php $a_Errors[$RowCount] = "Data di nascita o luogo di nascita mancanti."; $b_FixTrepsasser = true; ?> 
                                	<?php endif; ?>
                                	<?= $r_Table['DataNascita'].' '.$r_Table['LocalitaNascita']; ?>
                            	</div>
                                <div class="table_caption_H col-sm-1">
                                	<?= $a_mapTipologiaAnagrafica[$r_Table['TipologiaAnagrafica']] ?? ''; ?>
                            	</div>
                            <?php break; ?>
                            <?php case 2: ?>
        		            	<div class="table_caption_H col-sm-1">
        		            		<?= $r_Table['DataNotifica']; ?>
								</div>
                            	<div class="table_caption_H col-sm-2">
                            		<?= $a_mapEsitiNotifica[$r_Table['EsitoNotifica']] ?? ''; ?>
								</div>
                                <div class="table_caption_H col-sm-2">
                                	<?= $r_Table['RiferimentoRaccomandata']; ?>
								</div>
                            <?php break; ?>
    						<?php case 3: ?>
        		            	<div class="table_caption_H col-sm-2">
        		            		<?= $r_Table['DataRegistrazione']; ?>
								</div>
                            	<div class="table_caption_H col-sm-2">
                            		<?= $r_Table['DataPagamento']; ?>
								</div>
                                <div class="table_caption_H col-sm-1">
                                	€ <?= $r_Table['ImportoPagato']; ?>
								</div>
                            <?php break; ?>
    						<?php case 4: ?>
                            	<div class="table_caption_H col-sm-3">
                            	<!-- Con l'icona mettere ancora mettere ancora che punta alla nuova pagina del flusso? --> 
                            		<?php if(!empty($r_Table['RiferimentoRaccomandata'])): ?>
    	                        		<?= printIcon('S'); ?> 
	                        		<?php else: ?>
	                        			<?= printIcon('D'); ?>
                            		<?php endif; ?>
                            		<?= $r_Table['RiferimentoRaccomandata']; ?>
								</div>
                                <div class="table_caption_H col-sm-2">
                                	€ <?= $r_Table['SpeseNotificaPN']; ?>
								</div>
                            <?php break; ?>
    						<?php case 5: ?>
                            	<div class="table_caption_H col-sm-3" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
	                        		<?php if(file_exists("$docsPath/{$r_Table['QUERY_Id']}/".basename(str_replace('\\', '/', $r_Table['Documento'])))): ?>
    	                        		<?= printIcon('S'); ?> 
	                        		<?php else: ?>
	                        			<?= printIcon('D'); ?>
	                        			<?php $a_Errors[$RowCount] = "Documento non trovato: ".basename(str_replace('\\', '/', $r_Table['Documento'])); ?> 
                            		<?php endif; ?>
                            		<?= basename(str_replace('\\', '/', $r_Table['Documento'])); ?>
								</div>
                                <div class="table_caption_H col-sm-2">
                                	<?= $r_Table['Descrizione']; ?>
								</div>
                            <?php break; ?>
                            <?php default: ?>
                        		<div class="table_caption_H col-sm-5"></div>
                        <?php endswitch; ?>
                        <div class="table_caption_H col-sm-1">
                        	<?= $r_Table['QUERY_RegDate']; ?>
                    	</div>
                        <div class="table_caption_button col-sm-1">
                        	<?= ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Table['QUERY_Id'].'"><span class="glyphicon glyphicon-eye-open tooltip-r" title="Visualizza" data-placement="top"></span></a>'); ?>
                    		<?php if($b_FixTrepsasser): ?>
                    			<?= ChkButton($aUserButton, 'viw','<a href="mgmt_trespasser_upd.php'.$str_GET_Parameter.'&Id='.$r_Table['QUERY_TrespasserId'].'"><span class="fa fa-wrench tooltip-r" title="Correggi anagrafica" data-placement="top"></span></a>'); ?>
                    		<?php endif; ?>
                    	</div>
                    </div>
        		<?php endwhile; ?>
        		
				<?php if(!empty($a_Errors)): ?>
					<div class="clean_row HSpace48"></div>	
					
        			<div class="table_label_H col-sm-12 ">PROBLEMI RISCONTRATI</div>
        			
        			<div class="clean_row HSpace4"></div>
        			
		            <div class="table_label_H col-sm-1">Riga</div>
	            	<div class="table_label_H col-sm-11">Avviso</div>	
        			
        			<div class="clean_row HSpace4"></div>
        			<?php foreach($a_Errors as $errorLine => $errorMessage): ?>
		                    <div class="table_caption_H col-sm-1 alert-danger"><?= $errorLine; ?></div>
                    		<div class="table_caption_H col-sm-11 alert-danger"><?= $errorMessage; ?></div>
                    		
                    		<div class="clean_row HSpace4"></div>
        			<?php endforeach; ?>
				<?php endif; ?>
        		
                <div class="clean_row HSpace4"></div>
        	
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	<div style="padding-top:2rem;">
            	    	<?= ChkButton($aUserButton, 'exp','<button type="button" id="export" class="btn btn-success" style="width:19rem;"><i class="fas fa-file-zip-o fa-fw fa-2x" style="vertical-align:middle;"></i> Crea esportazione </button>'); ?>
        	    	</div>
                </div>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
        	<?php endif; ?>
        <?php endif; ?>
    </form>
</div>

<script type="text/javascript">
	$(document).ready(function () {
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
      	
        $('#search').click(function () {
            $("#search, #export").prop('disabled', true);
            $('#search i').toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
            $('#f_exp_maggioli').submit();
        });
        
        $("#export").on('click',function(e){
            <?php if(!empty($a_Errors)): ?>
            	if(confirm("Sono presenti errori nei dati da esportare, questo potrebbe comportare problemi sull'applicativo Maggioli. Continuare?")){
            		if(confirm("Si è proprio sicuri di voler continuare?")){
			            $('#f_exp_maggioli').attr('action', 'exp_maggioli_xml_exe.php');
                        $(this).html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>');
                        $('#f_exp_maggioli').submit();
                        $("#search, #export").prop('disabled', true);
            		}
            	}
            <?php else: ?>
	            $('#f_exp_maggioli').attr('action', 'exp_maggioli_xml_exe.php');
                $(this).html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>');
                $('#f_exp_maggioli').submit();
                $("#search, #export").prop('disabled', true);
            <?php endif;?>
        });
	});
</script>

<?php
require_once(INC."/footer.php");
