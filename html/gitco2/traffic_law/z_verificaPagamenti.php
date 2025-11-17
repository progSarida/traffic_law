<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(CLS."/cls_pagamenti.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$FineId = CheckValue("FineId", "n");
$ProcessingDate = CheckValue("ProcessingDate", "s");
$SearchType = CheckValue("SearchType","s");
$TestCycleAmount = CheckValue("TestCycleAmount","s") != "" ? CheckValue("TestCycleAmount","s") : "1";
$MassiveTestText = "";
if($SearchType == "singleSearch"){
    $classePagamenti = new cls_pagamenti($FineId,$_SESSION['cityid'],$ProcessingDate);
}
elseif($SearchType == "multipleSearch"){
    $ProcessingStartTime = microtime(true);
    $responseTimeArray = array();
    $classePagamenti;
    for($i = 0; $i < $TestCycleAmount; $i++){
        $classePagamenti = new cls_pagamenti($FineId,$_SESSION['cityid'],$ProcessingDate);
        array_push($responseTimeArray,$classePagamenti->getResponseTime());
    }
    $classePagamenti->setResponseTime(array_sum($responseTimeArray)/count($responseTimeArray));
    $ProcessingEndTime = microtime(true);
    $ProcessingTime = number_format($ProcessingEndTime-$ProcessingStartTime,4);
    $MassiveTestText = "Tempo di risposta totale test di $TestCycleAmount chiamate: ".$ProcessingTime."s ";
}

?>
<div class="col-sm-12 BoxRowLabel">
<b>Funzione di verifica dello stato dei pagamenti di un verbale alla data impostata</b>
</div>
<div class="clean_row HSpace4"></div>
<form action="z_verificaPagamenti.php">
    <div class="col-sm-12 BoxRowLabel">
        FineId
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 BoxRowCaption searchField">
        <input type="text" name="FineId" id="FineId" value="<?=$FineId?>">
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 BoxRowLabel">
        Data elaborazione
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 BoxRowCaption searchField">
    	<input class="form-control frm_field_date" id="ProcessingDate" type="text" value="<?=$ProcessingDate?>" name="ProcessingDate" style="width:15rem;">
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-1" style="margin-right:1rem;">
    		<label for="singleSearch">Ricerca singola</label>
    		<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Ricerca singola" class="tooltip-r btn btn-primary pull-left" id="singleSearch" name="SearchType" style="margin-top:0;width:100%;height:100%;" value="singleSearch"><i class="glyphicon glyphicon-search" style="font-size:2.5rem;"></i></button>
	</div>
	<div class="col-sm-1">
			<label for="multipleSearch">Ricerca multipla</label>
    		<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Test ricerca multipla" class="tooltip-r btn btn-primary pull-left" id="multipleSearch" name="SearchType" style="margin-top:0;width:100%;height:100%;" value="multipleSearch"><i class="glyphicon glyphicon-zoom-in" style="font-size:2.5rem;"></i></button>
	</div>
	<div class="col-sm-1" style="height:100%">
		<label for="TestCycleAmount">N° di cicli di test</label>
		<input class="form-control frm_field_number" id="TestCycleAmount" type="text" value="<?=$TestCycleAmount?>" name="TestCycleAmount" style="margin-top:0;width:100%;height:100%;">
	</div>
	<div><?=$MassiveTestText?></div>
	<div class="clean_row HSpace4"></div>
    <div>
    	<div class="col-sm-12 BoxRowLabel">
    	Risposta
    	</div>
    	<div class="clean_row HSpace4"></div>
    	<?php 
    	
    	
    	 $genericMessage = $classePagamenti->getGenericMessage();
    	 $disputeMessage = $classePagamenti->getDisputeMessage();
    	 $notifcationDate = DateOutDB($classePagamenti->getNotificationDate());
    	 $lastPaymentDate = DateOutDB($classePagamenti->getLastPaymentDate());
    	 $baseFee = $classePagamenti->getBaseFee();
    	 $fineReducedFee = $classePagamenti->getFineReducedFee();
    	 $fineFee = $classePagamenti->getFineFee();
    	 $fineMaxFee = $classePagamenti->getFineMaxFee();
    	 $additionalFee = $classePagamenti->getAdditionalFee();
    	 $finePrefectureFee = $classePagamenti->getFinePrefectureFee();
    	 $disputePrefectureFee = $classePagamenti->getDisputeAmount();
    	 $semester = $classePagamenti->getSemester();
    	 $surcharge = $classePagamenti->getSurcharge();
    	 $previousReminderNotificationFeesSum = $classePagamenti->getPreviousReminderNotificationFeesSum();
    	 $lastReminderNotificationFee = $classePagamenti->getLastReminderNotificationFee();
    	 $lastReminderTotalNotificationFee = $classePagamenti->getLastReminderTotalNotificationFee();
    	 $lastReminderTotalAmount = $classePagamenti->getLastReminderTotalAmount();
    	 $isReduced = $classePagamenti->isReduced();
    	 $hasReminder = $classePagamenti->hasReminder();
    	 $hasDispute = $classePagamenti->getHasDispute();
    	 $fee = $classePagamenti->getFee();
    	 $payed = $classePagamenti->getPayed();
    	 $totalFee = $classePagamenti->getTotalFee();
    	 $currentCustomerNotificationFee = $classePagamenti->getCurrentCustomerNotificationFee();
    	 $currentCustomerResearchFee = $classePagamenti->getCurrentCustomerResearchFee();
    	 $currentCustomerReminderNotificationFee = $classePagamenti->getCurrentCustomerReminderNotificationFee();
    	 $fineNotificationFee = $classePagamenti->getFineNotificationFee();
    	 $fineResearchFee = $classePagamenti->getFineResearchFee();
    	 $currentNotificationFee = $classePagamenti->getCurrentNotificationFee();
    	 $customerFee = $classePagamenti->getCustomerFee();
    	 $canFee = $classePagamenti->getCanFee();
    	 $cadFee = $classePagamenti->getCadFee();
    	 $notifierFee = $classePagamenti->getNotifierFee();
    	 $otherFee = $classePagamenti->getOtherFee();
    	 $statusDescription = $classePagamenti->getStatusDescription();
    	 $status = $classePagamenti->getStatus();
    	 $latePaymentStatus = $classePagamenti->getLatePaymentStatus();
    	 $responseTime = $classePagamenti->getResponseTime();
    	?>
    	<div class="col-sm-2 BoxRowLabel">
    	Generic message
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$genericMessage?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Dispute message
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$disputeMessage?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Notification date
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$notifcationDate?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Last payment date
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$lastPaymentDate?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Base fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$baseFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Reduced
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$fineReducedFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Normal
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$fineFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Max
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$fineMaxFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Additional fee (Tutte le spese)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$additionalFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Importo prefettura (verbale)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$finePrefectureFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Importo prefettura (ricorso)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$disputePrefectureFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Semester
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$semester?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Surcharge
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$surcharge?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Previous reminder notification fees sum
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$previousReminderNotificationFeesSum?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Last reminder notification fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$lastReminderNotificationFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Last reminder total notification
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$lastReminderTotalNotificationFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Last reminder total amount
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$lastReminderTotalAmount?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Ridotto
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=($isReduced ? "vero" : "falso")?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Ha sollecito
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=($hasReminder ? "vero" : "falso")?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Ha ricorso
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=($hasDispute ? "vero" : "falso")?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Fee	(Dovuto)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$fee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Payed (Pagato)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$payed?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	TotalFee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$totalFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Current customer notification fee (impostate in ente)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$currentCustomerNotificationFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Current customer research fee (impostate in ente)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$currentCustomerResearchFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Current customer reminder notification fee (impostate in ente)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$currentCustomerReminderNotificationFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Notification Fee (da FineHistory)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$fineNotificationFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Research Fee (da FineHistory)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$fineResearchFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Current notification Fee (aggiornate in base ai solleciti ecc...)
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$currentNotificationFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Customer fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$customerFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Can fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$canFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Cad fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$cadFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Notifier fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$notifierFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Other fee
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$otherFee?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Status description
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$statusDescription?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Status
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$status?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Scaglione pagamento in ritardo
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$latePaymentStatus?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel">
    	Tempi di risposta
    	</div>
    	<div class="col-sm-10 BoxRowCaption">
        <?=$responseTime?>
        </div>
        <div class="clean_row HSpace4"></div>
    </div>
</form>

<div class="col-sm-12 BoxRowLabel">
        Legenda degli status
</div>
<div class="col-sm-12 BoxRowCaption legenda"><b>-1</b> (errore)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>0</b> (non pagato)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>1</b> (parziale pagato)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>2</b> (pagato parziale per ritardo)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>3</b> (pagato pari)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>4</b> (pagato in eccesso)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>5</b> (ricorso in attesa)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>6</b> (ricorso accolto)</div>
<div class="clean_row HSpace4"></div>
<div class="col-sm-12 BoxRowCaption legenda"><b>7</b> (prescrizione)</div>
<div class="clean_row HSpace4"></div>

<script type="text/javascript">
	$('.legenda').css("padding-left", "1%");
	$('.searchField').css("height", "3rem");
	$('input').css("margin-top", ".25rem");
	$('input').css("margin-bottom", ".25rem");
	$('input').css("margin-left", ".5rem");
</script>
<?php
include(INC."/footer.php");
