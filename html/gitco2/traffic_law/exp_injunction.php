<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_exp_injunction.php");
require_once(PGFN."/fn_prn_anag_anomalies.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

ini_set('max_execution_time', 3000);

$str_Error = '';

if(empty($r_Customer['RoleCityCode'])){
    $str_Error .= "E' necessario definire il codice ente in Ente > Gestione Ente > Ruoli per poter utilizzare la procedura.<br />";
}
if($r_Customer['RoleMinExpiredInstallments'] <= 0){
    $str_Error .= "E' necessario definire numero minimo di rate scadute in Ente > Gestione Ente > Ruoli per poter utilizzare la procedura.";
}

if($str_Error != ''){
    $_SESSION['Message']['Error'] = $str_Error;
    
    echo $str_out;
    require_once(INC."/footer.php");
    DIE;
}

/** @var CLS_DB $rs */
//TODO viene fatto questo perchè anche se initialization.php lo fa già, menu_top.php reinizializza un'altra istanza $rs
//che quindi non avrà il charset impostato. Da rimuovere una volta che viene rimosso $rs da menut_top oppure se verrà già impostato nel costruttore di cls_db
$rs->SetCharset('utf8');

$Filter = CheckValue('Filter', 'n');
$PageTitle = CheckValue('PageTitle','s');
$MinExpiredRates = $r_Customer['RoleMinExpiredInstallments'];

//Per riempire testo e valori delle relative checkbox
$a_PaymentOptions = unserialize(EXP_INJUNCTION_PAYMENT_OPTIONS);
$a_OrderOptions = unserialize(EXP_INJUNCTION_ORDER_OPTIONS);
$a_ValidatedAddressOptions = unserialize(EXP_INJUNCTION_VALIDATEDADDRESS_OPTIONS);
$a_ForeignNotificationOptions = unserialize(EXP_INJUNCTION_FOREIGN_NOTIFICATION_OPTIONS);
$a_DisputeOptions = unserialize(EXP_INJUNCTION_DISPUTE_OPTIONS);
$a_DisputePrefectOptions = unserialize(EXP_INJUNCTION_DISPUTEPREFECT_OPTIONS);
$a_ExpiredInstalmentsOptions = unserialize(EXP_INJUNCTION_EXPIRED_INSTALMENTS_OPTIONS);
$a_AnomaliesOptions = unserialize(EXP_INJUNCTION_ANOMALIES_OPTIONS);
$a_PayedReminderOptions = unserialize(EXP_INJUNCTION_PAYEDREMINDER_OPTIONS);
$a_CADDocumentOptions = unserialize(EXP_INJUNCTION_HASCADDOCUMENT_OPTIONS);
$a_NotDocumentOptions = unserialize(EXP_INJUNCTION_HASNOTDOCUMENT_OPTIONS);

$a_OrderOptionsSelect = array_diff(array_combine(array_keys($a_OrderOptions), array_column($a_OrderOptions, 'Name')), [null]);

$RowCount = $RowNumber = $RowCountAnomalies = 0;

$Order_Type = $Order_Type != "" ? $Order_Type : key($a_OrderOptions);
$Search_PaymentType = $Search_PaymentType != "" ? $Search_PaymentType : INDEX_PAYMENT_ANY;
$Search_ValidatedAddress = ($Search_ValidatedAddress == '') ? 0 : $Search_ValidatedAddress;
$Search_NotificationStatus = ($Search_NotificationStatus == '') ? 0 : $Search_NotificationStatus;
$Search_Expired = ($Search_Expired == '') ? 1 : $Search_Expired;
$Search_Anomalies = ($Search_Anomalies == '') ? 1 : $Search_Anomalies;
$Search_Payed = ($Search_Payed == '') ? 0 : $Search_Payed;


if(!empty($s_TypePlate) && $Filter == 1) {
    $str_Where = expInjunctionWhere();
    $str_Order = expInjunctionOrderBy();
    
    if($s_TypePlate == 'N'){
        $cls_view = new CLS_VIEW(FINE_NOT_PAYED);
        $str_ProcessingTable = 'National';
        }
    else{
        $cls_view = new CLS_VIEW(FINE_NOT_PAYED_WITHOUT_NOTIFICATION_CONSTRAINTS);
        $str_ProcessingTable = 'Foreign';
        }
    
    $query = $cls_view->generateSelect($str_Where, null, $str_Order);
    
    $rs_FineProcedure = $rs->SelectQuery($query);
    $RowNumber = mysqli_num_rows($rs_FineProcedure);
    
    $r_ProcessingData = $rs->getArrayLine($rs->Select("ProcessingDataPayment$str_ProcessingTable", "CityId='".$_SESSION['cityid']."'"));
}

$ValidatedAddressFilter = '';
$ForeignNotificationStatus = '';

foreach($a_ValidatedAddressOptions as $value => $name):
$ValidatedAddressFilter .= '<div class="col-sm-3"><input type="radio" name="Search_ValidatedAddress" value="'.$value.'" style="top:0;"'.(($value == $Search_ValidatedAddress) ? ' checked' : "").'>&nbsp;<span style="position:relative;top:-0.3rem">'.$name.'</span></div>';
endforeach;

foreach($a_ForeignNotificationOptions as $value => $name):
$ForeignNotificationStatus .= '<div class="col-sm-3"><input type="radio" name="Search_NotificationStatus" value="'.$value.'" id="Search_NotificationStatus_'.$value.'" style="top:0;"'.(($value == $Search_NotificationStatus) ? ' checked' : "").'>&nbsp;<span style="position:relative;top:-0.3rem">'.$name.'</span></div>';
endforeach;

echo $str_out;
?>
<script>
SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
</script>
<div class="row-fluid">
    <div class="col-sm-12 alert alert-warning" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-warning col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
        	<ul>
        		<li><strong>Il seguente tracciato non prevede l'inclusione di coobbligati (record N3)</strong></li>
            	<li><strong>Prima della generazione del ruolo si rende necessario effettuare la validazione degli indirizzi delle posizioni notificate tramite CAD.</strong></li>
            	<li><strong>L'elaborazione definitiva chiuderà eventuali posizioni con rateizzazioni in corso per le quali non sono state rispettate le previste scadenze e non sarà più possibile creare rateizzazioni per queste posizioni.</strong></li>
        	</ul>
        </div>
    </div>
    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul>
                <li>Nota bene:
                <ul style="list-style-position: inside;">
                    <li>Le righe rosse rappresentano i verbali che non soddisfano le condizioni per poter procedere con l'elaborazione a ruolo e quindi non verranno inclusi nella stampa definitiva.</li>
                    <li>Le righe verdi rappresentano i verbali che soddisfano le condizioni per poter procedere con l'elaborazione a ruolo. Verranno inclusi nella stampa definitiva.</li>
                	<li>Se non compare un cronologico verificare nella scheda "ulteriori dati" nel dettaglio del verbale che il flag di iscrizione a ruolo sia impostato a "SI".</li>
                </ul>
            	</li>
            	<li>Le condizioni per l'elaborazione a ruolo sono:
                <ul style="list-style-position: inside;">
                    <li>Se un verbale ha ricorso, non deve essere sospeso e deve avere esito in favore del comune.</li>
                    <li>Non vi devono essere anomalie nelle anagrafiche dei trasgressori (far riferimento alla voce di menu Ruoli > Elenco anomalie anagrafica).</li>
                    <li>Se vi è una rateizzazione aperta, devono essere presenti almeno <strong><?= $MinExpiredRates; ?></strong> rate scadute.</li>
                </ul>
            	</li>
            	<li>Il filtro "Posizioni notificate tramite CAD senza pagamento e prive di validazione dell’indirizzo" serve a specificare se delle posizioni senza pagamento con esito notifica "Notificato CAD" e "Compiuta giacenza", vanno incluse anche quelle prive di validazione indirizzo.</li>
            </ul>
        </div>
    </div>
    <form id="f_exp_injunction" name="f_exp_injunction" action="exp_injunction.php" method="get">
        <input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
    	<input type="hidden" name="Filter" value="1">
        <div class="col-sm-11">
        	<div class="col-sm-1 BoxRowLabel">
        		Da anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', !$Filter && $Search_FromProtocolYear <= 0 ? $_SESSION['year'] : $Search_FromProtocolYear, false); ?>
        	</div>
        	<div class="col-sm-1 BoxRowLabel">
        		Ad anno
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', !$Filter && $Search_ToProtocolYear <= 0 ? $_SESSION['year'] : $Search_ToProtocolYear, false); ?>
        	</div>
            <div class="col-sm-1 BoxRowLabel">
                Da cron.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="<?= $Search_FromProtocolId; ?>" id="Search_FromProtocolId" name="Search_FromProtocolId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A cron.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="<?= $Search_ToProtocolId; ?>" id="Search_ToProtocolId" name="Search_ToProtocolId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data accert.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data accert.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel font_small">
                Nazionalità trasgressore
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(unserialize(EXP_INJUNCTION_NATIONALITY), true, 'TypePlate', 'TypePlate', $s_TypePlate, false, null, null, ''); ?>
            </div>  
            <div class="col-sm-1 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromNotificationDate; ?>" name="Search_FromNotificationDate" id="Search_FromNotificationDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToNotificationDate; ?>" name="Search_ToNotificationDate" id="Search_ToNotificationDate">
            </div>
            <div class="col-sm-3 BoxRowLabel font_small">
                Posizioni con almeno n. <?= $MinExpiredRates ?> rate scadute
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?php foreach($a_ExpiredInstalmentsOptions as $value => $name): ?>
                	<div class="col-sm-3">
                    	<input type="radio" name="Search_Expired" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_Expired ? ' checked' : '' ?>>
                    	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                	</div>
                <?php endforeach; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Pagamenti
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?php foreach($a_PaymentOptions as $value => $name): ?>
                    <div class="col-sm-3">
                    	<input type="radio" name="Search_PaymentType" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_PaymentType ? ' checked' : '' ?>>
                    	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                	</div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-3 BoxRowLabel font_small">
                Posizioni con ricorso al prefetto chiuso vinto dal comune e non appellato
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?php foreach($a_DisputePrefectOptions as $value => $name): ?>
            		<?php //TODO rimuovere cablatura disabled quando verrà completato il filtro ?>
            		<div class="col-sm-3">
                		<input <?= $value != 0 ? 'disabled' : ''; ?> type="radio" name="Search_HasDisputePrefect" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_HasDisputePrefect ? ' checked' : '' ?>>
                		<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                	</div>
                <?php endforeach; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
            	Posizioni con anomalie anagrafica
            </div>
            <div class="col-sm-4 BoxRowCaption">
            	<?php foreach($a_AnomaliesOptions as $value => $name): ?>
                	<div class="col-sm-3">
                    	<input type="radio" name="Search_Anomalies" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_Anomalies ? ' checked' : '' ?>>
                    	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
    				</div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-3 BoxRowLabel font_small">
                Pos. con ricorso al giudice di pace ed eventuali gradi successivi chiuso vinto dal comune
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?php foreach($a_DisputeOptions as $value => $name): ?>
            		<?php //TODO implementare ?>
            		<div class="col-sm-3">
                    	<input type="radio" name="Search_HasDispute" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_HasDispute ? ' checked' : '' ?>>
                    	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
					</div>
                <?php endforeach; ?>
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel font_small">
            	Posizioni che hanno pagato l'importo del sollecito
            </div>
            <div class="col-sm-4 BoxRowCaption">
            	<?php foreach($a_PayedReminderOptions as $value => $name): ?>
            		<div class="col-sm-3">
                    	<input type="radio" name="Search_Payed" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_Payed ? ' checked' : '' ?>>
                    	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
					</div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-3 BoxRowLabel font_small" id="ValidatedAddressTitle">
            </div>
            <div class="col-sm-3 BoxRowCaption" id="ValidatedAddressBody">
                <!-- VALIDAZIONE INDIRIZZO O NOTIFICA ESTERA -->
            </div>
            
            <div class="clean_row HSpace4"></div>
            <div id="Filter_HasCAD_Empty" class="col-sm-6 BoxRowLabel" style="display:none"></div>
            <div id="Filter_HasCAD">
                <div class="col-sm-2 BoxRowLabel font_small">
                	Posizioni notificate con CAD e immagine mancante
                </div>
                <div class="col-sm-4 BoxRowCaption">
                	<?php foreach($a_CADDocumentOptions as $value => $name): ?>
                		<div class="col-sm-3">
                    		<input type="radio" name="Search_HasCAD" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_HasCAD ? ' checked' : '' ?>>
                    		<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
						</div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="Filter_HasNotification_Empty" class="col-sm-6 BoxRowLabel" style="display:none"></div>
            <div id="Filter_HasNotification">
                <div class="col-sm-3 BoxRowLabel font_small">
                	Posizioni notificate tramite raccomandata senza immagine della notifica
                </div>
                <div class="col-sm-3 BoxRowCaption">
                	<?php foreach($a_NotDocumentOptions as $value => $name): ?>
                		<div class="col-sm-3">
                        	<input type="radio" name="Search_HasNotification" value="<?= $value; ?>" style="top:0;"<?= $value == $Search_HasNotification ? ' checked' : '' ?>>
                        	<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
                    	</div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Ordina per
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?php foreach($a_OrderOptionsSelect as $value => $name): ?>
                	<div class="col-sm-3">
                		<input type="radio" name="Order_Type" value="<?= $value; ?>" style="top:0;"<?= $value == $Order_Type ? ' checked' : '' ?>>
                		<span style="position:relative;top:-0.3rem"> <?= $name; ?></span>
        			</div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-6 BoxRowLabel">
            </div>
    	</div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:16rem">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
        
    	<div class="clean_row HSpace4"></div>
    	
        <div class="table_label_H col-sm-1">Riga</div>
		<div class="table_label_H col-sm-1">Cronologico</div>
		<div class="table_label_H col-sm-1">Rif.to</div>
        <div class="table_label_H col-sm-1">Data notifica</div>
		<div class="table_label_H col-sm-1">Data/Ora</div>
		<div class="table_label_H col-sm-1">Targa</div>
		<div class="table_label_H col-sm-2">Trasgressore</div>
		<div class="table_label_H col-sm-1">Ricorso</div>
		<div class="table_label_H col-sm-1">Anom. Anag.</div>
		<div class="table_label_H col-sm-1">Rate scadute</div>
		<div class="table_label_H col-sm-1"></div>
        
        <?php if(empty($s_TypePlate) || $Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere la nazionalità del trasgressore.
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php while ($r_FineProcedure = $rs->getArrayLine($rs_FineProcedure)): ?>
        			<?php
        			    $n_ExpiredRates = 0;
        			    $b_Dispute = false;
        			    $b_ExpiredRates = false;
        			    $b_TrespasserAnomaly = false;
        			    
        			    $a_TrespasserAnomalies = manageAnomalies($r_FineProcedure, $r_FineProcedure['TrespasserCountryId'] == 'Z000' ? 'N' : 'F');
        			    $r_PaymentRate = $rs->getArrayLine($rs->Select('PaymentRate', "FineId={$r_FineProcedure['FineId']} AND StatusRateId=0"));
        			    if($r_PaymentRate){
        			        $rs_PaymentRateNumber = $rs->Select('PaymentRateNumber', "PaymentRateId={$r_PaymentRate['Id']}");
        			        while($r_PaymentRateNumber = $rs->getArrayLine($rs_PaymentRateNumber)){
        			            $r_Payment = $rs->getArrayLine($rs->Select('FinePayment', "FineId={$r_FineProcedure['FineId']} AND PaymentFee={$r_PaymentRateNumber['RateNumber']}"));
        			            //Se la data di scadenza è minore della data di pagamento (data attuale se non c'è un pagamento per quella rata)
        			            if($r_PaymentRateNumber['PaymentDate'] < ($r_Payment['PaymentDate'] ?? date('Y-m-d'))){
        			                $n_ExpiredRates++;
        			            }
        			        }
        			    }
        			    
        			    //TODO GESTIONE RICORSI CON VERBALI MULTIPLI DA AGGREGARE NELL'ESTRAZIONE
        			    if($r_FineProcedure['DisputeId'] > 0){
        			        //Parte stato ricorsi
        			        $disputeView = new CLS_VIEW(MGMT_DISPUTE);
        			        $rs_FineDispute= $rs->selectQuery($disputeView->generateSelect("F.Id=".$r_FineProcedure['FineId'],null, "GradeTypeId DESC",1));
        			        $r_FineDispute = $rs->getArrayLine($rs_FineDispute);
        			        
        			        $cls_dispute = new cls_dispute();
        			        $cls_dispute->setDispute($r_FineDispute,1);
        			        $disputeStatus = $cls_dispute->a_info['responseCode'];
        			        
        			        if(($disputeStatus >= 1 && $disputeStatus <= 4) || ($disputeStatus == 6)) //Ricorso in attesa, rinviato, sospeso o accolto
        			        {
        			            $b_Dispute = true;    //Segno in rosso i ricorsi che non hanno esito finale respinto
        			        }
        			    }
        			    
        			    $b_ExpiredRates = $r_PaymentRate && $n_ExpiredRates < $MinExpiredRates;
        			    $b_TrespasserAnomaly = checkAnomalyExistence($a_TrespasserAnomalies, true);
        			    
        			    if(($Search_Expired == 0 && $n_ExpiredRates >= $MinExpiredRates) || ($Search_Expired == 2 && $n_ExpiredRates < $MinExpiredRates)){
        			        continue;
        			    }
        			    if(($Search_Anomalies == 0 && $b_TrespasserAnomaly) || ($Search_Anomalies == 2 && !$b_TrespasserAnomaly)){
        			        continue;
        			    }
        			    
        			    //SE PRESENTI PAGAMENTI CHE COPRONO TUTTO SALTO IL RICORSO
        			    //Controlla lo stato dei pagamenti associati al verbale
        			    $cls_pagamenti = new cls_pagamenti($r_FineProcedure['FineId'], $r_FineProcedure['CityId']);
        			    
        			    //In base al filtro "Pagamenti" selezionato e allo stato del pagamento, salta la riga
        			    $b_skipPayment = false;
        			    switch($Search_PaymentType){
        			        case INDEX_PAYMENT_OMITTED : if($cls_pagamenti->getStatus() != 0) $b_skipPayment = true; break;
        			        case INDEX_PAYMENT_PARTIAL : if($cls_pagamenti->getStatus() != 1) $b_skipPayment = true; break;
        			        case INDEX_PAYMENT_DELAYED : if($cls_pagamenti->getStatus() != 2) $b_skipPayment = true; break;
        			    }
        			    if($b_skipPayment) continue;
        			    
        			    //Se il rimanente da pagare è inferiore a "Importo minimo per iscrizione a ruolo" salta l'elaborazione
        			    if(($cls_pagamenti->getFee() - $cls_pagamenti->getPayed()) < $r_ProcessingData['AmountLimit']){
        			        continue;
        			    }
        			    //Se lo status è tra 3 (pagato pari) e 4 (pagato in eccesso) salta l'elaborazione
        			    if(($cls_pagamenti->getStatus()) == 3 || ($cls_pagamenti->getStatus()) == 4){
        			        continue;
    			        }
    			        
    			        //Determina in presenza di un sollecito se l'importo pagato è uguale o supera sanzione+spese notifica
    			        $b_PayedReminder = $cls_pagamenti->hasReminder() && ($cls_pagamenti->getPayed() >= ($cls_pagamenti->getLastReminderTotalAmount() - $cls_pagamenti->getLastReminderSurcharge()));
    			        
    			        //"Posizioni che hanno pagato l'importo del sollecito"
    			        if(($Search_Payed == 0 && $b_PayedReminder) || ($Search_Payed == 2 && !$b_PayedReminder)){
    			            continue;
    			        }
    			        
        			    //Determina il colore della riga
    			        if($b_Dispute || $b_ExpiredRates || $b_TrespasserAnomaly){
    			            $TextColor = 'text-danger';
    			            $RowCountAnomalies++;
    			            
    			        } else {
    			            $TextColor = 'text-success';
    			        }
        			    $TextColor = $b_Dispute || $b_ExpiredRates || $b_TrespasserAnomaly ? 'text-danger' : 'text-success';
                    ?>
        			<?php $RowCount++; ?>
	                <div class="tableRow <?= $TextColor ?>">
                        <div class="table_caption_H col-sm-1"><?= $RowCount; ?></div>
                        <div class="table_caption_H col-sm-1"><?= $r_FineProcedure['ProtocolId']; ?></div>
                        <div class="table_caption_H col-sm-1"><?= $r_FineProcedure['Code']; ?></div>
                        <div class="table_caption_H col-sm-1"><?= !empty($r_FineProcedure['NotificationDate']) ? DateOutDB($r_FineProcedure['NotificationDate']) : "-"; ?></div>
                        <div class="table_caption_H col-sm-1"><?= DateOutDB($r_FineProcedure['FineDate']); ?>  <?= TimeOutDB($r_FineProcedure['FineTime']); ?></div>
                        <div class="table_caption_H col-sm-1"><?= $r_FineProcedure['VehiclePlate']; ?></div>
                        <div class="table_caption_H col-sm-2">(Cod. <?= $r_FineProcedure['TrespasserCode']; ?>) <?= implode(' ', array($r_FineProcedure['CompanyName'], $r_FineProcedure['Surname'], $r_FineProcedure['Name'])); ?></div>
                        <div class="table_caption_H col-sm-1"><?= YesNoOutDB($r_FineProcedure['DisputeId'] > 0); ?></div>
                        <div class="table_caption_H col-sm-1" style="line-height: 2.4rem;">
                        	<?php if(!empty($a_anomaliesMessages = getAnomaliesMessages($a_TrespasserAnomalies))): ?>
                        		<?php foreach($a_anomaliesMessages as $anomalyMessage): ?>
                        			<i class="tooltip-r fas fa-exclamation-circle fa-fw" style="font-size:1.3rem;" data-html="true" data-container="body" data-toggle="tooltip" data-placement="left" title="<?= $anomalyMessage ?>"></i>
                        		<?php endforeach; ?>
                        	<?php endif?>
                        </div>
                        <div class="table_caption_H col-sm-1"><?= $r_PaymentRate ? $n_ExpiredRates : ''; ?></div>
                        <div class="table_caption_button col-sm-1" style="line-height:2.4rem">
                        	<?php if($b_TrespasserAnomaly): ?>
            					<a href="mgmt_trespasser_upd.php<?=$str_GET_Parameter?>&Id=<?=$r_FineProcedure['TrespasserId']?>"><span class="tooltip-r fas fa-user-edit" title="Modifica anagrafica" data-placement="top"></span></a>&nbsp;
        					<?php endif; ?>
        				</div>
        			</div>
        			
        			<div class="clean_row HSpace4"></div>
        		<?php endwhile; ?>
        		
	        	<?php if($RowCount > 0): ?>
	        	
        			<div class="clean_row HSpace16"></div>
	        	
                    <div class="col-sm-2 BoxRowLabel">
                    	Risultati totali
                    </div>
                    <div class="col-sm-1 BoxRowCaption text-center">
                    	<strong><?= $RowCount; ?></strong>
                    </div>
                    <div class="col-sm-2 BoxRowLabel table_caption_success">
                    	Posizioni pronte per l'elaborazione
                    </div>
                    <div class="col-sm-1 BoxRowCaption text-center">
                    	<strong><?= $RowCount-$RowCountAnomalies; ?></strong>
                    </div>
                    <div class="col-sm-2 BoxRowLabel table_caption_error">
                    	Posizioni con anomalie (escluse dall'elaborazione)
                    </div>
                    <div class="col-sm-1 BoxRowCaption text-center">
                    	<strong><?= $RowCountAnomalies; ?></strong>
                    </div>
    	            <div class="col-sm-3 BoxRowHTitle">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
    	            <div class="col-sm-2 BoxRowLabel">
                        Codice Ente creditore
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <?= $r_Customer['RoleCityCode']; ?>
                    </div>
    	            <div class="col-sm-9 BoxRowHTitle">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
            	    <div class="table_label_H HSpace4" style="height:8rem;">
    	                <button type="submit" class="btn btn-success" id="act_button" style="margin-top:1rem;">Generazione file in modalità provvisoria (ripetibile)</button>
                        <div id="ultimateDiv">
                            <input type="checkbox" value="1" name="ultimate" id="ultimate"> Definitiva
                        </div>
                    </div>
        		<?php else: ?>
        	        <div class="table_caption_H col-sm-12 text-center">
                    	Nessun record presente.
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
        		<?php endif; ?>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
                
                <div class="clean_row HSpace4"></div>
    		<?php endif; ?>
		<?php endif; ?>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
    	SARIDA.nascondiCaricamento();
    	
        $('#ultimate').click(function(){
        	if($('#ultimate').is(":checked")) {
        		$('#act_button').text('Estrazione dati definitiva (non ripetibile)');
        		$('#act_button').removeClass( "btn-success" ).addClass( "btn-warning" );
        	}else{
        		$('#act_button').text('Generazione file in modalità provvisoria (ripetibile)');
        		$('#act_button').removeClass( "btn-warning" ).addClass( "btn-success" );
        	}
        });

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
    });
    
    $('#TypePlate').on('change',function(){
    	var countryLetter = $('#TypePlate').val();
    	//console.log("***VAL: "+countryLetter+"***");
    	if(countryLetter == 'N')
    		{
			$('#Filter_HasNotification').show();
    		$('#Filter_HasNotification_Empty').hide();
    		$('#ValidatedAddressTitle').text('Posizioni notificate tramite CAD senza pagamento e prive di validazione dell’indirizzo');
    		$('#ValidatedAddressBody').html('<?=$ValidatedAddressFilter?>');
    		$('#Filter_HasCAD').show();
    		$('#Filter_HasCAD_Empty').hide();
    		}
		else if(countryLetter == 'F')
    		{
    		$('#ValidatedAddressTitle').text('Notifiche estere');
    		$('#ValidatedAddressBody').html('<?=$ForeignNotificationStatus?>');
    		$('#Filter_HasCAD').hide();
    		$('#Filter_HasCAD_Empty').show();
    		$("input[name='Search_HasCAD'][value='1']").prop('checked', true);
    		}
		else{
    		$('#Filter_HasNotification').show();
    		$('#Filter_HasNotification_Empty').hide();
    		$('#Filter_HasCAD').show();
    		$('#Filter_HasCAD_Empty').hide();
			$('#ValidatedAddressTitle').text('');
    		$('#ValidatedAddressBody').html('');
			}
    	}).change();
    	
	$(document).on('change', "input[name='Search_NotificationStatus']", function(){
		if($(this).val() == 1){
			$("input[name='Search_HasNotification'][value='1']").prop('checked', true);
    		$('#Filter_HasNotification').hide();
    		$('#Filter_HasNotification_Empty').show();
		} else {
    		$('#Filter_HasNotification').show();
    		$('#Filter_HasNotification_Empty').hide();
		}
	})
	$("input[name='Search_NotificationStatus']:checked").change();
    	
   	$('#ValidatedAddressBody').on('change',function(){
   		var countryLetter = $('#TypePlate').val();
   		if(countryLetter != 'N'){
    		if($('#Search_NotificationStatus_1').is(":checked")){
       			$('#Search_FromNotificationDate').attr('disabled', true);
       			$('#Search_ToNotificationDate').attr('disabled', true);
       			$('#Search_FromNotificationDate').val('');
       			$('#Search_ToNotificationDate').val('');
       			}
    		else{
    			$('#Search_FromNotificationDate').attr('disabled', false);
       			$('#Search_ToNotificationDate').attr('disabled', false);
    			}
			}
   		}).change();
   		
        $('#act_button').click(function () {
    		$('#f_exp_injunction').attr('action', 'exp_injunction_exe.php');
        });
   		
        $('#f_exp_injunction').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_date:{
                	selector: '.frm_field_date',
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    }
                },
            }
        }).on('success.form.bv', function(e){
        	var returnSubmit = false;
        	if($(this).attr('action') == 'exp_injunction_exe.php'){
	            if($('#ultimate').is(":checked")) {
                    if(confirm("Si stanno per elaborare i dati in maniera definitiva. L'elaborazione chiuderà eventuali rateizzazioni in corso sui verbali elaborati e non sarà più possibile creare rateizzazioni su tali verbali. Continuare?")){
                    	if(confirm("Si è proprio sicuri di voler continuare?")){
                    		returnSubmit = true;
                    	}
                    }
                } else {
                    if(confirm("L'estrazione provvisoria verrà mostrata a video. Continuare?")){
                        returnSubmit = true;
                    }
            	}
            	if(!returnSubmit) $(this).attr('action', 'exp_injunction.php');
        	} else returnSubmit = true;
        	
        	if(returnSubmit){
        		SARIDA.mostraCaricamento("Caricamento in corso...<br>L'operazione potrebbe richiedere alcuni minuti.");
        	}
        		
        	return returnSubmit;
        });
</script>
<?php
require_once(INC."/footer.php");
