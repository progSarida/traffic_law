<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_stat_fine_collection.php");
require_once(INC."/initialization.php");

/** @var $rs CLS_DB */

$a_Months = unserialize(STAT_FINE_COLLECTION_MONTHS);
$a_ViolationTypeFileSuffix = unserialize(STAT_FINE_COLLECTION_VIOLATIONTYPE_FILENAME_SUFFIX);

$NextYear = $Search_Year+1;
$a_MonthsToSearch = array_slice($a_Months, 0, array_search($Search_Month,array_keys($a_Months)) + 1, true);
$FromDate = date('Y-m-d', strtotime("first day of january $Search_Year"));
$ToDate = date('Y-m-t', strtotime("$Search_Year-$Search_Month-01"));

$str_Where = statFineCollectionWhere();

//DATI PRIMA TABELLA/////////////////
//Incluse: determina se i valori della riga verranno sommati o no all riga delle somme totali (Sanzioni ACCERTATE: aggiornate)
//Negative: determina se i valori della riga devono essere negativi
$a_ViewsFirstTable = array(
    'Sanzioni ACCERTATE TOTALI' =>
    array('View' => STAT_FINE_COLLECTION_ACCERTATE, 'Where' => $str_Where, 'Incluse' => false, 'Negative' => false),
    'Sanzioni ACCERTATE INIVIATE' =>
    array('View' => STAT_FINE_COLLECTION_INVIATE, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => false),
    'RIDUZIONE 30%: VERBALI pag. nei 5gg dalla notifica o senza' =>
    array('View' => STAT_FINE_COLLECTION_RIDUZIONE30, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    'Verbali ARCHIVIATI' =>
    array('View' => STAT_FINE_COLLECTION_ARCHIVIATI, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    'Verbali RINOTIFICATI' =>
    array('View' => STAT_FINE_COLLECTION_RINOTIFICATI, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    'Verbali NON NOTIFICATI SOLO STRANIERI' =>
    array('View' => STAT_FINE_COLLECTION_NONOT_STRANIERI, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    'Verbali NON NOTIFICATI/STAM/RIST.' =>
    array('View' => STAT_FINE_COLLECTION_NONOT_STAM_RIST, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    'VERBALI INVIATI IN ATTESA DI NOTIFICA' =>
    array('View' => STAT_FINE_COLLECTION_WAITING, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    'VERBALI INESITATI DA OLTRE 90 GG DALLA CREAZIONE DEL FLUSSO' =>
    array('View' => STAT_FINE_COLLECTION_EXPIRED, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => true),
    "Verbali accertati nel $Search_Year e notificati nel $NextYear" =>
    array('View' => STAT_FINE_COLLECTION_ACC2022_NOT2023, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => false),
    'DIFFERENZA sanzione in misura ridotta e importo a ruolo' =>
    array('View' => STAT_FINE_COLLECTION_DIFFSANZ_RID_RUOLO, 'Where' => $str_Where, 'Incluse' => true, 'Negative' => false)
);

$a_RowsFirstTable = array();

foreach($a_ViewsFirstTable as $rowTitle => $view){
    $a_Row = array();
    $RowSum = 0;
    
    $a_Row['Title'] = $rowTitle;
    $a_Row['Incluse'] = $view['Incluse'];
    $cls_view = new CLS_VIEW($view['View']);
    
    $a_ToReplace = array(
        "@Ente" => $_SESSION['cityid'],
        "@FromDate" => $FromDate,
        "@ToDate" => $ToDate,
        "@ProtocolYear" => $Search_Year,
        "@ProtocolNextYear" => $NextYear
    );
    
    $query = strtr($cls_view->generateSelect($view['Where']), $a_ToReplace);
    $a_Results = $rs->getResults($rs->SelectQuery($query));
    $a_Results = array_column($a_Results, null, 'Month');
    
    foreach(array_keys($a_Months) as $monthNumber){
        if(key_exists($monthNumber, $a_MonthsToSearch)){
            $monthValue = $a_Results[$monthNumber]['Sum'] ?? 0;
            
            if($monthValue > 0 && $view['Negative']){
                $monthValue *= -1;
            }
            
            $a_Row[$monthNumber] = $monthValue;
            $RowSum += $monthValue;
        }
    }
    $a_Row['RowSum'] = $RowSum;
    $a_RowsFirstTable[] = $a_Row;
}

//RIGA DELLA SOMMA DELLE COLONNE
$a_Row = array('Title' => 'Sanzioni ACCERTATE: aggiornate');
foreach($a_RowsFirstTable as $row){
    $b_include = $row['Incluse'];
    //Esclude i primi due indici, quello del titolo e dell'inclusione nella somma
    foreach (array_slice($row, 1, count($row), true) as $column => $value){
        $a_Row[$column] = isset($a_Row[$column]) ? ($a_Row[$column] + ($b_include ? $value : 0)) : ($b_include ? $value : 0);
    }
}

$a_RowsFirstTable[] = $a_Row;
//////////////////////////////////////

//DATI SECONDA TABELLA/////////////////
$a_ViewsSecondTable = array(
    "Sanzioni $Search_Year incassate nel $Search_Year" =>
    array('View' => STAT_FINE_COLLECTION_SANZ_INC_ANNO_COMP, 'Where' => $str_Where, 'Year' => $Search_Year, 'NextYear' => $Search_Year, 'Negative' => false),
    "Sanzioni $Search_Year incassate nel $NextYear" =>
    array('View' => STAT_FINE_COLLECTION_SANZ_INC_ANNO_COMP, 'Where' => $str_Where, 'Year' => $Search_Year, 'NextYear' => $NextYear, 'Negative' => false),
);

$a_SecondTable = array(
    'Per data di pagamento' => array(
        'Views' => $a_ViewsSecondTable,
        'ToReplace' => array(
            "@PaymentDateColumn" => 'FP.PaymentDate'
        )
    ),
    'Per data di accredito' => array(
        'Views' => $a_ViewsSecondTable,
        'ToReplace' => array(
            "@PaymentDateColumn" => 'FP.CreditDate'
        )
    ),
);

$a_RowsSecondTable = array();

foreach($a_SecondTable as $sectionTitle => $data){
    
    $a_RowsSecondTable[$sectionTitle] = array();
    
    foreach ($data['Views'] as $rowTitle => $view){
        $a_Row = array();
        $RowSum = 0;
        
        $a_Row['Title'] = $rowTitle;
        $cls_view = new CLS_VIEW($view['View']);
        
        $a_ToReplace = array(
            "@Ente" => $_SESSION['cityid'],
            "@FromDate" => $FromDate,
            "@ToDate" => $ToDate,
            "@ProtocolYear" => $view['Year'],
            "@ProtocolNextYear" => $view['NextYear'],
        );
        
        $a_ToReplace = array_merge($a_ToReplace, $data['ToReplace']);
        
        $query = strtr($cls_view->generateSelect($view['Where']), $a_ToReplace);
        $a_Results = $rs->getResults($rs->SelectQuery($query));
        $a_Results = array_column($a_Results, null, 'Month');
        
        foreach(array_keys($a_Months) as $monthNumber){
            if(key_exists($monthNumber, $a_MonthsToSearch)){
                $monthValue = $a_Results[$monthNumber]['Sum'] ?? 0;
                
                if($monthValue > 0 && $view['Negative']){
                    $monthValue *= -1;
                }
                
                $a_Row[$monthNumber] = $monthValue;
                $RowSum += $monthValue;
            }
        }
        $a_Row['RowSum'] = $RowSum;
        $a_RowsSecondTable[$sectionTitle][] = $a_Row;
    }
}
//////////////////////////////////////

//DATI TERZA TABELLA/////////////////
$a_ViewsThirdTable = array(
    "Sanzioni incassate nel $Search_Year" =>
    array('View' => STAT_FINE_COLLECTION_SANZ_INC, 'Where' => $str_Where, 'Year' => $Search_Year, 'NextYear' => $Search_Year, 'Negative' => false),
    "Sanzioni incassate nel $Search_Year non associate" =>
    array('View' => STAT_FINE_COLLECTION_SANZ_INC_NON_ASSOC, 'Where' => null, 'Year' => $Search_Year, 'NextYear' => $Search_Year, 'Negative' => false),
);

$a_ThirdTable = array(
    'Per data di pagamento' => array(
        'Views' => $a_ViewsThirdTable,
        'ToReplace' => array(
            "@PaymentDateColumn" => 'FP.PaymentDate'
        )
    ),
    'Per data di accredito' => array(
        'Views' => $a_ViewsThirdTable,
        'ToReplace' => array(
            "@PaymentDateColumn" => 'FP.CreditDate'
        )
    ),
);

$a_RowsThirdTable = array();

foreach($a_ThirdTable as $sectionTitle => $data){
    
    $a_RowsThirdTable[$sectionTitle] = array();
    
    foreach ($data['Views'] as $rowTitle => $view){
        $a_Row = array();
        $RowSum = 0;
        
        $a_Row['Title'] = $rowTitle;
        $cls_view = new CLS_VIEW($view['View']);
        
        $a_ToReplace = array(
            "@Ente" => $_SESSION['cityid'],
            "@FromDate" => $FromDate,
            "@ToDate" => $ToDate,
            "@ProtocolYear" => $view['Year'],
            "@ProtocolNextYear" => $view['NextYear'],
        );
        
        $a_ToReplace = array_merge($a_ToReplace, $data['ToReplace']);
        
        $query = strtr($cls_view->generateSelect($view['Where']), $a_ToReplace);
        $a_Results = $rs->getResults($rs->SelectQuery($query));
        $a_Results = array_column($a_Results, null, 'Month');
        
        foreach(array_keys($a_Months) as $monthNumber){
            if(key_exists($monthNumber, $a_MonthsToSearch)){
                $monthValue = $a_Results[$monthNumber]['Sum'] ?? 0;
                
                if($monthValue > 0 && $view['Negative']){
                    $monthValue *= -1;
                }
                
                $a_Row[$monthNumber] = $monthValue;
                $RowSum += $monthValue;
            }
        }
        $a_Row['RowSum'] = $RowSum;
        $a_RowsThirdTable[$sectionTitle][] = $a_Row;
    }
}
//////////////////////////////////////

$fileName = $_SESSION['cityid'].'_resoconto_riscossioni_'.$a_ViolationTypeFileSuffix[$Search_Type].'_'.$Search_Month.'_'.$Search_Year.'_'.date('d-m-Y').'.xls';

ob_start();
?>
<table border=1>
	<tr><th colspan="8" bgcolor="lightblue">STATISTICA CDS</th></tr>
	<tr></tr>
	<tr>
		<th colspan="8" bgcolor="#b1c3ff">Avvisi/Verbali emessi nel periodo:</th>
        <th bgcolor="darkturquoise">Gennaio</th>
        <th bgcolor="darkturquoise">Febbraio</th>
        <th bgcolor="darkturquoise">Marzo</th>
        <th bgcolor="darkturquoise">Aprile</th>
        <th bgcolor="orange">Maggio</th>				
        <th bgcolor="orange">Giugno</th>
        <th bgcolor="orange">Luglio</th>
        <th bgcolor="orange">Agosto</th>
        <th bgcolor="lightgreen">Settembre</th>
        <th bgcolor="lightgreen">Ottobre</th>
        <th bgcolor="lightgreen">Novembre</th>
        <th bgcolor="lightgreen">Dicembre</th>
        <th bgcolor="#b1c3ff">Tot.</th>
	</tr>
	<?php foreach($a_RowsFirstTable as $index => $row): ?>
		<tr>
    		<th colspan="8" bgcolor="lightblue"><?= $row['Title']; ?></th>
        	<?php foreach (array_keys($a_Months) as $monthNumber): ?>
        		<?php if(key_exists($monthNumber, $a_MonthsToSearch)): ?>
        			<td bgcolor="<?= ($index+1) == count($a_RowsFirstTable) ? '#f7ecb5' : '#dcf0fa' ?>" align="right"><?= formatCellValue($row[$monthNumber]); ?></td>
    			<?php else: ?>
    				<td bgcolor="#b1b1b1"></td>
        		<?php endif; ?>
        	<?php endforeach; ?>
        	<td bgcolor="<?= ($index+1) == count($a_RowsFirstTable) ? '#f7ecb5' : '#b1c3ff' ?>" align="right"><?= formatCellValue($row['RowSum']); ?></td>
    	</tr>
	<?php endforeach; ?>
	<tr></tr>
	<tr></tr>
	<tr><th colspan="8" bgcolor="lightblue">SANZIONI CDS RISCOSSE</th></tr>

	<?php foreach($a_RowsThirdTable as $sectionTitle => $sectionRows): ?>
		<tr><th colspan="8" bgcolor="#b1c3ff"><?= $sectionTitle; ?></th></tr>
    	<?php foreach($sectionRows as $row): ?>
    		<tr>
        		<th colspan="8" bgcolor="lightblue"><?= $row['Title']; ?></th>
            	<?php foreach (array_keys($a_Months) as $monthNumber): ?>
            		<?php if(key_exists($monthNumber, $a_MonthsToSearch)): ?>
            			<td bgcolor="#dcf0fa" align="right"><?= formatCellValue($row[$monthNumber]); ?></td>
        			<?php else: ?>
        				<td bgcolor="#b1b1b1"></td>
            		<?php endif; ?>
            	<?php endforeach; ?>
            	<td bgcolor="#b1c3ff" align="right"><?= formatCellValue($row['RowSum']); ?></td>
        	</tr>
    	<?php endforeach; ?>
	<?php endforeach; ?>
	
	<tr></tr>
	<tr></tr>
	<tr><th colspan="8" bgcolor="lightblue">SANZIONI CDS RISCOSSE PER ANNO DI COMPETENZA</th></tr>
	
	<?php foreach($a_RowsSecondTable as $sectionTitle => $sectionRows): ?>
		<tr><th colspan="8" bgcolor="#b1c3ff"><?= $sectionTitle; ?></th></tr>
    	<?php foreach($sectionRows as $row): ?>
    		<tr>
        		<th colspan="8" bgcolor="lightblue"><?= $row['Title']; ?></th>
            	<?php foreach (array_keys($a_Months) as $monthNumber): ?>
            		<?php if(key_exists($monthNumber, $a_MonthsToSearch)): ?>
            			<td bgcolor="#dcf0fa" align="right"><?= formatCellValue($row[$monthNumber]); ?></td>
        			<?php else: ?>
        				<td bgcolor="#b1b1b1"></td>
            		<?php endif; ?>
            	<?php endforeach; ?>
            	<td bgcolor="#b1c3ff" align="right"><?= formatCellValue($row['RowSum']); ?></td>
        	</tr>
    	<?php endforeach; ?>
	<?php endforeach; ?>
</table>
<?php
$table = ob_get_clean();

if (! is_dir(ROOT . "/doc/print")) mkdir(ROOT . "/doc/print", 0777);
if (! is_dir(ROOT . "/doc/print/resoconto_riscossioni")) mkdir(ROOT . "/doc/print/resoconto_riscossioni", 0777);

//Scrive il report in formato xls sul file system
file_put_contents(ROOT . "/doc/print/resoconto_riscossioni/". $fileName, "\xEF\xBB\xBF".$table);

$_SESSION['Documentation'] = $MainPath . "/doc/print/resoconto_riscossioni/". $fileName;

header("location: ".impostaParametriUrl(array('Filter' => 1, 'Search_Year' => $Search_Year, 'Search_Month' => $Search_Month, 'Search_Type' => $Search_Type), 'stat_fine_collection.php'));
