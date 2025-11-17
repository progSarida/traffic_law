<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

global $rs;

const STAT_FINE_ARCHIVED_STATUS = array(33, 35, 36);
const STAT_FINE_CANCELED_STATUS = array(90);
const STAT_FINE_STATUS_TITLES = array(
    33 => 'Archiviati per rinotifica a stesso trasgressore, per noleggio a breve termine e via messo',
    35 => 'Archiviati per rinotifica ad altro trasgressore e solo archivio',
    36 => 'Archiviati per rinotifica per noleggio a lungo termine',
    90 => 'Preinserimenti annullati'
);

const STAT_FINE_ACTIVE = array(
    'aliases' => array(
        'ViolationTitle' => 'VT.Title',
        'ArticleId' => 'FA.ArticleId',
        'Article' => 'A.Article',
        'Paragraph' => 'A.Paragraph',
        'Letter' => 'A.Letter',
        'CountryId' => 'F.CountryId'
    ),
    'from' => 'Fine F
        join FineArticle FA on F.Id = FA.FineId
        join ViolationType VT on FA.ViolationTypeId = VT.Id
        join Article A on A.Id = FA.ArticleId',
    'where' => 'F.StatusTypeId > 30',
);

const STAT_FINE_SENT = array(
    'distinct' => true,
    'aliases' => array(
        'Code' => 'F.Code',
        'FineDate' => 'F.FineDate',
        'ViolationTitle' => 'VT.Title',
        'ArticleId' => 'FA.ArticleId',
        'Article' => 'A.Article',
        'Paragraph' => 'A.Paragraph',
        'Letter' => 'A.Letter',
    ),
    'from' => 'Fine F
        join FineArticle FA on F.Id = FA.FineId
        join ViolationType VT on FA.ViolationTypeId = VT.Id
        join Article A on A.Id = FA.ArticleId',
    'where' => 'F.ProtocolId > 0 AND F.StatusTypeId >= 20 AND F.StatusTypeId <= 40 and COALESCE(F.ControllerDate, F.FineDate) BETWEEN "@FirstDate" AND "@LastDate"'
);

const STAT_FINE_SENT_WITH_NOTIFICATIONS = array(
    'aliases' => array(
        'Code' => 'F.Code',
        'FineDate' => 'F.FineDate',
        'ViolationTitle' => 'VT.Title',
        'ArticleId' => 'FA.ArticleId',
        'Article' => 'A.Article',
        'Paragraph' => 'A.Paragraph',
        'Letter' => 'A.Letter',
    ),
    'from' => 'Fine F
        join FineArticle FA on F.Id = FA.FineId
        join ViolationType VT on FA.ViolationTypeId = VT.Id
        join Article A on A.Id = FA.ArticleId',
    'where' => 'F.ProtocolId > 0 AND F.StatusTypeId >= 20 AND F.StatusTypeId <= 40 and COALESCE(F.ControllerDate, F.FineDate) BETWEEN "@FirstDate" AND "@LastDate"'
);

$a_ToReplace = array(
    "@FirstDate" => date('Y-m-d', strtotime("first day of january {$_SESSION['year']}")),
    "@LastDate" => date('Y-m-d', strtotime("last day of december {$_SESSION['year']}"))
);

//TARGHE INSERITE
$a_FineInsert = array();
$cls_view = new CLS_VIEW(STAT_FINE_ACTIVE);
$query = $cls_view->generateSelect("F.CityId='" . $_SESSION['cityid'] . "' AND F.ProtocolYear=" . $_SESSION['year']);
$rs_FineInsert = $rs->SelectQuery($query);

while($r_FineInsert = $rs->getArrayLine($rs_FineInsert)){
    $a_FineInsert[$r_FineInsert['CountryId'] != 'Z000' ? 'Foreign' : 'National']
                 [$r_FineInsert['ViolationTitle']]
                 [$r_FineInsert['ArticleId']]
                 [] = $r_FineInsert;
}

//SPEDITI

//Senza contare rinotifiche
$a_SendFine = array();
$cls_view = new CLS_VIEW(STAT_FINE_SENT);
$query = strtr($cls_view->generateSelect("F.CityId='" . $_SESSION['cityid']."'"), $a_ToReplace);
$rs_SendFine = $rs->SelectQuery($query);

while ($r_SendFine = $rs->getArrayLine($rs_SendFine)) {
    $a_SendFine[$r_SendFine['ViolationTitle']]
    [$r_SendFine['ArticleId']]
    [] = $r_SendFine;
}

//Contando rinotifiche
$a_SendFineNotification = array();
$cls_view = new CLS_VIEW(STAT_FINE_SENT_WITH_NOTIFICATIONS);
$query = strtr($cls_view->generateSelect("F.CityId='" . $_SESSION['cityid']."'"), $a_ToReplace);
$rs_SendFineNotification = $rs->SelectQuery($query);

while ($r_SendFineNotification = $rs->getArrayLine($rs_SendFineNotification)) {
    $a_SendFineNotification[$r_SendFineNotification['ViolationTitle']]
    [$r_SendFineNotification['ArticleId']]
    [] = $r_SendFineNotification;
}

//TODO PROTOCOLLATI da rivedere
// $ExternalDate = date("Y-m-d");
// $rs_Row = $rs->SelectQuery("SELECT COUNT(ExternalDate) TotProtocol FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND CountryId='Z000' AND ExternalDate='".$ExternalDate."'");

// $r_Row = mysqli_fetch_array($rs_Row);

// $str_out3 .= '
//             <div class="table_label_H col-sm-12">PROTOCOLLATI E FIRMATI</div>
//                 <div class="table_label_H col-sm-12">OGGI '.$r_Row['TotProtocol'].'</div>';

// $rs_Row = $rs->SelectQuery("SELECT COUNT(ExternalProtocol) TotProtocol , MAX(ExternalProtocol) ExternalProtocol FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND CountryId='Z000' AND ExternalProtocol>0");

// $r_Row = mysqli_fetch_array($rs_Row);

// $str_out3 .= '
//                 <div class="table_label_H col-sm-12">TOTALE '.$r_Row['TotProtocol'].' - ultimo prot assegnato: '.$r_Row['ExternalProtocol'].'/'.$_SESSION['year'].'</div>';


// $rs_Row = $rs->SelectQuery("SELECT COUNT(SendDate) TotSent FROM V_FineHistory WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . "  AND NotificationTypeId=6");
// $r_Row = mysqli_fetch_array($rs_Row);
// $TotSent = $r_Row['TotSent'];


// $rs_Row = $rs->SelectQuery("SELECT MAX(SendDate) LastSendDate  FROM V_FineHistory WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND NotificationTypeId=6");
// $r_Row = mysqli_fetch_array($rs_Row);
// $LastSendDate = DateOutDB($r_Row['LastSendDate']);

// $str_out3 .= '
//                 <div class="table_label_H col-sm-12">POSTALIZZATI '.$TotSent.' - ultimo lotto spedito: '.$LastSendDate.'</div>';

//PAGAMENTI
$a_Payments = array();
$rs_Payments = $rs->Select("V_FinePayment","CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year']);
while ($r_Payment = $rs->getArrayLine($rs_Payments)) {
    $a_Payments[$r_Payment['PaymentTypeTitle']]
    [$r_Payment['FineId']]
    [] = $r_Payment;
}

//Annullati e archiviati

$rs_Archived = $rs->SelectQuery("SELECT StatusTypeId, COUNT(StatusTypeId) TOT FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] ." AND StatusTypeId IN(".implode(',', STAT_FINE_ARCHIVED_STATUS).") GROUP BY StatusTypeId");
$rs_Canceled = $rs->SelectQuery("SELECT StatusTypeId, COUNT(StatusTypeId) TOT FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] ." AND StatusTypeId IN(".implode(',', STAT_FINE_CANCELED_STATUS).") GROUP BY StatusTypeId");

$a_Archived = $rs->getResults($rs_Archived);
$a_Canceled = $rs->getResults($rs_Canceled);

echo $str_out;
?>
<div class="row-fluid">
	<div class="BoxRowTitle col-sm-12">Statistiche verbali di <?= $_SESSION['citytitle'] ?> - Anno: <?= $_SESSION['year'] ?></div>
    <ul class="nav nav-tabs" id="fine">
        <li class="active"><a href="#InsertFine" data-toggle="tab">POSIZIONI ATTIVE</a></li>
        <li><a href="#SendFine" data-toggle="tab">VERBALI SPEDITI</a></li>
        <!-- <li><a href="#ProtocolFine" data-toggle="tab">PROTOCOLLATI</a></li> -->
        <li><a href="#PaymentFine" data-toggle="tab">PAGATI</a></li>
        <li><a href="#DeleteFine" data-toggle="tab">PREINSERIMENTI ANNULLATI</a></li>
        <li><a href="#ArchiveFine" data-toggle="tab">VERBALI ARCHIVIATI</a></li>
    </ul>
    <div class="tab-content">
        <div id="InsertFine" class="tab-pane active">
        	<ul class="nav nav-tabs" id="InsertFineNationality">
    	        <li class="active"><a href="#InsertFineNational" data-toggle="tab">NAZIONALI</a></li>
            	<li><a href="#InsertFineForeign" data-toggle="tab">ESTERE</a></li>
        	</ul>
        	<div class="tab-content">
        		<div id="InsertFineNational" class="tab-pane active">
        			<div class="bg-primary col-sm-12 alert" style="height:auto;color:inherit">
	    			    <div class="col-sm-4" style="height:700px;overflow:auto">
                            <table id="chartData" style="width:100%;margin:0;">        
                                <tr>
                                	<th>ARTICOLO</th>
                                	<th>QUANTITA'</th >
                                </tr>
                                <?php $articleTotal = 0; ?>
                                <?php foreach($a_FineInsert['National'] as $violationTitle => $articles): ?>
                                	<tr>
                                		<th><?= $violationTitle; ?></th>
                                		<th></th>
                                	</tr>
                                	<?php foreach($articles as $article): ?>
                                		<?php $singleArticle = reset($article); $articleTotal += count($article)  ?>
                                		<tr style="color:<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>">
                                			<td><?= $singleArticle['Article'].' '.$singleArticle['Paragraph'].' '.$singleArticle['Letter']; ?></td>
                                			<td><?= count($article); ?></td>
                                		</tr>
                                	<?php endforeach; ?>
                                <?php endforeach; ?>
                                <tr>
                                	<th>TOTALE</th>
                                	<th><?= $articleTotal; ?></th>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-8">
                        	<canvas id="chart" width="800" height="700" style="float:none;margin: 0 auto;"></canvas>
                        </div>
        			</div>
        		</div>
        		<div id="InsertFineForeign" class="tab-pane">
        			<div class="bg-primary col-sm-12 alert" style="height:auto;color:inherit;">
	    			    <div class="col-sm-4" style="height:700px;overflow:auto">
                            <table id="chartData_f" style="width:100%;margin:0;">        
                                <tr>
                                	<th>ARTICOLO</th>
                                	<th>QUANTITA'</th >
                                </tr>
                                <?php $articleTotal = 0; ?>
                                <?php foreach($a_FineInsert['Foreign'] as $violationTitle => $articles): ?>
                                	<tr>
                                		<th><?= $violationTitle; ?></th>
                                		<th></th>
                                	</tr>
                                	<?php foreach($articles as $article): ?>
                                		<?php $singleArticle = reset($article); $articleTotal += count($article)  ?>
                                		<tr style="color:<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>">
                                			<td><?= $singleArticle['Article'].' '.$singleArticle['Paragraph'].' '.$singleArticle['Letter']; ?></td>
                                			<td><?= count($article); ?></td>
                                		</tr>
                                	<?php endforeach; ?>
                                <?php endforeach; ?>
                                <tr>
                                	<th>TOTALE</th>
                                	<th><?= $articleTotal; ?></th>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-8">
                        	<canvas id="chart_f" width="800" height="700" style="float:none;margin: 0 auto;"></canvas>
                        </div>
        			</div>
        		</div>
        	</div>
        </div>
        
        <div id="SendFine" class="tab-pane">
        	<ul class="nav nav-tabs" id="SendFineGroups">
    	        <li class="active"><a href="#SendFineWithNotification" data-toggle="tab">RINOTIFICHE COMPRESE</a></li>
            	<li><a href="#SendFineWithoutNotification" data-toggle="tab">RINOTIFICHE NON COMPRESE</a></li>
        	</ul>
        	<div class="tab-content">
        		<div id="SendFineWithNotification" class="tab-pane active">
		        	<div class="table_label_H col-sm-6">Articolo</div>
                	<div class="table_label_H col-sm-6">Quantità</div>
                	
                	<div class="clean_row HSpace4"></div>
                	<?php $sentNotificationTotal = 0; ?>
                    <?php foreach($a_SendFineNotification as $violationTitle => $articles): ?>
                    	<div class="table_caption_I col-sm-12 text-center"><?= $violationTitle ?></div>
                    	
                    	<div class="clean_row HSpace4"></div>
                    	<?php foreach($articles as $article): ?>
                    		<?php $singleArticle = reset($article); $sentNotificationTotal += count($article)  ?>
                    			<div class="col-sm-6 BoxRowLabel"><?= $singleArticle['Article'].' '.$singleArticle['Paragraph'].' '.$singleArticle['Letter']; ?></div>
                    			<div class="col-sm-6 BoxRowCaption"><?= count($article); ?></div>
                    			
                    			<div class="clean_row HSpace4"></div>
                    	<?php endforeach; ?>
                    <?php endforeach; ?>
                	<div class="table_label_H col-sm-6">Totale</div>
                	<div class="table_label_H col-sm-6"><?= $sentNotificationTotal; ?></div>
        		</div>
        		<div id="SendFineWithoutNotification" class="tab-pane">
		        	<div class="table_label_H col-sm-6">Articolo</div>
                	<div class="table_label_H col-sm-6">Quantità</div>
                	
                	<div class="clean_row HSpace4"></div>
                	<?php $sentTotal = 0; ?>
                    <?php foreach($a_SendFine as $violationTitle => $articles): ?>
                    	<div class="table_caption_I col-sm-12 text-center"><?= $violationTitle ?></div>
                    	
                    	<div class="clean_row HSpace4"></div>
                    	<?php foreach($articles as $article): ?>
                    		<?php $singleArticle = reset($article); $sentTotal += count($article)  ?>
                    			<div class="col-sm-6 BoxRowLabel"><?= $singleArticle['Article'].' '.$singleArticle['Paragraph'].' '.$singleArticle['Letter']; ?></div>
                    			<div class="col-sm-6 BoxRowCaption"><?= count($article); ?></div>
                    			
                    			<div class="clean_row HSpace4"></div>
                    	<?php endforeach; ?>
                    <?php endforeach; ?>
                	<div class="table_label_H col-sm-6">Totale</div>
                	<div class="table_label_H col-sm-6"><?= $sentTotal; ?></div>
        		</div>
    		</div>
        </div>
        <!-- <div id="ProtocolFine" class="tab-pane">
        </div> -->
        <div id="PaymentFine" class="tab-pane">
        	<div class="table_label_H col-sm-12">Prospetto pagamenti associati</div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="table_label_H col-sm-6">Tipologia</div>
        	<div class="table_label_H col-sm-6">Quantità</div>
        	
        	<div class="clean_row HSpace4"></div>
        	<?php $paymentTotal = 0; $a_FinesWithPayment = array(); ?>
            <?php foreach($a_Payments as $paymentType => $finePayments): ?>
            	<?php $paymentTypeTotal = 0; ?>
            	<div class="BoxRowLabel col-sm-6"><?= $paymentType ?></div>
            	<?php foreach($finePayments as $fineId => $payment): ?>
            		<?php $a_FinesWithPayment[] = $fineId; ?>
            		<?php $paymentTypeTotal += count($payment); ?>
            	<?php endforeach; ?>
            	<?php $paymentTotal += $paymentTypeTotal; ?>
            	<div class="BoxRowCaption col-sm-6"><?= $paymentTypeTotal ?></div>
            	
            	<div class="clean_row HSpace4"></div>
            <?php endforeach; ?>
        	<div class="table_label_H col-sm-6">Totale pagamenti associati</div>
        	<div class="table_label_H col-sm-6"><?= $paymentTotal; ?></div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="table_label_H col-sm-6">Totale posizioni con pagamenti</div>
        	<div class="table_label_H col-sm-6"><?= count(array_unique($a_FinesWithPayment)); ?></div>
        </div>
        
        <div id="DeleteFine" class="tab-pane">
        	<div class="table_label_H col-sm-6">Tipologia</div>
        	<div class="table_label_H col-sm-6">Quantità</div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<?php $canceledTotal = 0; ?>
        	<?php foreach($a_Canceled as $result): ?>
        		<?php $canceledTotal += $result['TOT']; ?>
        		<div class="BoxRowLabel col-sm-6"><?= STAT_FINE_STATUS_TITLES[$result['StatusTypeId']] ?></div>
        		<div class="BoxRowCaption col-sm-6"><?= $result['TOT'] ?></div>
        		
        		<div class="clean_row HSpace4"></div>
        	<?php endforeach;; ?>
    		<div class="table_label_H col-sm-6">Totale Annullati</div>
    		<div class="table_label_H col-sm-6"><?= $canceledTotal; ?></div>
        </div>
        
        <div id="ArchiveFine" class="tab-pane">
        	<div class="table_label_H col-sm-6">Tipologia</div>
        	<div class="table_label_H col-sm-6">Quantità</div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<?php $archivedTotal = 0; ?>
        	<?php foreach($a_Archived as $result): ?>
        		<?php $archivedTotal += $result['TOT']; ?>
        		<div class="BoxRowLabel col-sm-6"><?= STAT_FINE_STATUS_TITLES[$result['StatusTypeId']] ?></div>
        		<div class="BoxRowCaption col-sm-6"><?= $result['TOT'] ?></div>
        		
        		<div class="clean_row HSpace4"></div>
        	<?php endforeach;; ?>
    		<div class="table_label_H col-sm-6">Totale Archiviati</div>
    		<div class="table_label_H col-sm-6"><?= $archivedTotal; ?></div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= LIB ?>/piechart/js/piechart.js"></script>
<script type="text/javascript" src="<?= LIB ?>/piechart/js/piechart_f.js"></script>
<link rel="stylesheet" href="<?= LIB ?>/piechart/css/piechart.css" />
<script>
    $('document').ready(function () {

        $( pieChart );
        $( pieChart_f );

    });
    // Run the code when the DOM is ready
</script>
<?php
require_once(INC . "/footer.php");
