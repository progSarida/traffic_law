<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");
require_once(CLS."/cls_view.php");

$str_Where = '1=1';

/**Ente**/
if($_SESSION['userlevel'] >= 3){
    if($Search_CityId != ''){
        $str_Where .= " AND ci.Id='$Search_CityId'";
    }
}
else
    $str_Where .= " AND ci.Id='{$_SESSION['cityid']}'";
    
    /**Numero flusso**/
    if($Search_Flow != ''){
        $str_Where.= " AND fl.Number=$Search_Flow";
    }
    
    /**Anno**/
    if ($Search_Year != ''){
        $str_Where .= " AND fi.ProtocolYear=$Search_Year";
    }
    
    /**Tipo flusso**/
    if ($Search_PrintNumber > 0){
        $str_Where .= " AND fl.PrintTypeId=$Search_PrintNumber";
    }
    
    /**Stampatore**/
    if($Search_Type > 0){
        $str_Where.= " AND fl.PrinterId=$Search_Type";
    }
    
    /**Data di upload inizio**/
    if($Search_FromSendDate!=''){
        $str_Where.= " AND fl.UploadDate>='".DateInDB($Search_FromSendDate)."'";
    }
    
    /**Data di upload fine**/
    if($Search_ToSendDate!=''){
        $str_Where.= " AND fl.UploadDate<='".DateInDB($Search_ToSendDate)."'";
    }
    
    $query = new CLS_VIEW(PRN_NOTIFICATION_MISSING);
    
    /**TRASFORMARE NELLA QUERY PER LE NOTIFICHE MANCANTI**/
    $order = "fl.Number asc";
    $rs_NotificationMissing = $rs->SelectQuery($query->generateSelect($str_Where,null,$order,null));
    
    $RowNumber = mysqli_num_rows($rs_NotificationMissing);
    
    mysqli_data_seek($rs_NotificationMissing, $limitePagina);
    
    //******Stampa******
    
    $fileName = $_SESSION['cityid'].'_notifiche_mancanti_'.$Search_Month.'_'.$Search_Year.'_'.date('d-m-Y').'.xls';
    
    ob_start();
    ?>

<table border="1">
	<tr bgcolor="lightblue">
    		<th class="table_label_H col-sm-1">N°Prog.</th>
    		<th class="table_label_H col-sm-4">Codice ente</th>
    		<th class="table_label_H col-sm-4">Descrizione ente</th>
    		<th class="table_label_H col-sm-3">N°Flusso</th>
    		<th class="table_label_H col-sm-4">Cron</th>
    	</tr>
	<?php $n_Count = 1; ?>
	<?php while($r_NotificationMissing = $rs->getArrayLine($rs_NotificationMissing)):?>
			<tr>
				<th>
					<?=$n_Count?>
				</th>
				<th>
					<?= $r_NotificationMissing["CityId"]?>
				</th>
				<th>
					<?= $r_NotificationMissing["CityName"]?>
				</th>
				<th>
					<?= $r_NotificationMissing["FlowNumber"]?>
				</th>
				<th>
					<?= $r_NotificationMissing["ProtocolId"]."/".$r_NotificationMissing["ProtocolYear"]?>
				</th>
			</tr>
			<?php $n_Count++?>
	<?php endwhile; ?>
</table>

<?php 
$table = ob_get_clean();

//Stampa
if (! is_dir(ROOT . "/doc/print")) mkdir(ROOT . "/doc/print", 0777);
if (! is_dir(ROOT . "/doc/print/notifiche_mancanti")) mkdir(ROOT . "/doc/print/notifiche_mancanti", 0777);

//Scrive il report in formato xls sul file system
file_put_contents(ROOT . "/doc/print/notifiche_mancanti/". $fileName, "\xEF\xBB\xBF".$table);

$_SESSION['Documentation'] = $MainPath . "/doc/print/notifiche_mancanti/". $fileName;

$listaParametri = array('Filter' => 1, 'PageTitle' => "Stampe/Notifiche mancanti", 'Search_CityId' => $Search_CityId, 'Search_Flow' => $Search_Flow, 'Search_Year' => $Search_Year, 'Search_PrintNumber' => $Search_PrintNumber, 'Search_Type' => $Search_Type, 'Search_FromSendDate' => $Search_FromSendDate, 'Search_ToSendDate' => $Search_ToSendDate);

header("location: ".impostaParametriUrl($listaParametri, 'prn_notification_missing.php'));

