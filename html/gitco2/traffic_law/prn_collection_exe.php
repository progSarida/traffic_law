<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(INC."/function.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");
require(TCPDF . "/fpdi.php");

require(CLS.'/cls_literal_number.php');

$Action = CheckValue('Action', 's');

$CityId = CheckValue("Search_CityId", "s");
$Year = CheckValue("Search_Year", "s");
$AllFee = CheckValue('Search_Type','n');

$rs_CityTitle = $rs->SelectQuery("SELECT Title FROM sarida.City WHERE Id='$CityId'");
$CityTitle = mysqli_fetch_assoc($rs_CityTitle)['Title'];

$a_AllFee_text = array('Solo la sanzione amministrativa riscossa ','Tutte le componenti riscosse: spese di ricerca, postali/notifica');


$A_text = 'Proventi complessivi delle sanzioni derivanti dall\'accertamento di tutte le violazioni al codice della strada (ad eccezione delle sole violazioni di cui all\'art. 142, comma 12-bis)';
$B_text = 'Proventi complessivi delle sanzioni derivanti dall\'accertamento delle violazioni dei limiti massimi di velocità di cui all\'art. 142, comma 12-bis, comminate dai propri organi di polizia stradale sulle strade di competenza e in concessione';
$C_text = '50% del totale dei proventi delle sanzioni derivanti dall\'accertamento delle violazioni dei limiti massimi di velocita di cui all\'art. 142, comma 12-bis, comminate dai propri organi di polizia stradale sulle strade non di proprietà dell\'ente locale';
$D_text = '50% del totale dei proventi delle sanzioni derivanti dall\'accertamento delle violazioni dei limiti massimi di velocita di cui all\'art. 142, comma 12-bis, comminate su strade di proprietà dell\'ente locale da parte di organi di polizia stradale dipendenti da altri enti.';

$FromDate = date("d/m/Y",mktime(0, 0, 0, 1, 1, $Year));
$ToDate = date("d/m/Y",mktime(23, 59, 59, 12, 31, $Year));
echo $FromDate .", ".$ToDate;

if ($CityId != "" && $Year != ""){
    $FileName = $CityId.'_'.$Year.'_art142_'.date("Y-m-d_H-i");
    
    $rs_Payments = $rs->SelectQuery("
    SELECT SUM(FP.Amount) AS SumNot142, SUM(FP.Fee) AS SumNot142Fee
	FROM V_FinePayment FP
	INNER JOIN FineArticle FA on FA.FineId=FP.FineId 
	INNER JOIN Article A on A.Id=FA.ArticleId 
	WHERE FP.CityId='".$CityId."' AND PaymentDate >= '". DateInDB($FromDate) . "' AND PaymentDate <= '".DateInDB($ToDate)."' ".
        "AND (A.ViolationTypeId <> 2 OR (A.ViolationTypeId = 2 AND A.Article <> 142));");
    
    $r_Payments = mysqli_fetch_array($rs_Payments);
    $SumNot142 = ($AllFee ? $r_Payments['SumNot142'] : $r_Payments['SumNot142Fee']);
    $SumNot142 = number_format((float)$SumNot142, 2, ',', ' ');
    
    $rs_Payments = $rs->SelectQuery("
    SELECT SUM(FP.Amount) AS Sum142, SUM(FP.Fee) AS Sum142Fee
	FROM V_FinePayment FP
	INNER JOIN FineArticle FA on FA.FineId=FP.FineId
    INNER JOIN Article A on A.Id=FA.ArticleId
	WHERE FP.CityId='".$CityId."' AND PaymentDate >= '". DateInDB($FromDate) . "' AND PaymentDate <= '".DateInDB($ToDate)."' ".
        " AND A.ViolationTypeId = 2 AND A.Article = 142;");
    
    $r_Payments = mysqli_fetch_array($rs_Payments);
    $Sum142 = ($AllFee ? $r_Payments['Sum142'] : $r_Payments['Sum142Fee']);
    $Sum142 = number_format((float)$Sum142, 2, ',', ' ');
    
    
    switch ($Action){
        case 'Pdf':{
            $FileName .= '.pdf';
            
            $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);
            $page_format = array('Rotate'=>45);
            
            $pdf->TemporaryPrint= 0;
            $pdf->CustomerFooter = 0;
            
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_SESSION['citytitle']);
            $pdf->SetTitle('ART 142');
            $pdf->SetSubject('');
            $pdf->SetKeywords('');
            
            $pdf->SetMargins(20,10,20);
            
            $pdf->AddPage('P', $page_format);
            $pdf->SetFont('helvetica', 'B', 12);
            
            $pdf->Write(0, 'Ente: '.$CityTitle.' ('.$CityId.')', '', 0, 'C', true, 0, false, false, 0);
            $pdf->LN(5);
            $pdf->Write(0, 'Anno: '.$Year, '', 0, 'C', true, 0, false, false, 0);
            $pdf->LN(5);
            $pdf->Write(0, 'Estrazione basata su: '.$a_AllFee_text[$AllFee], '', 0, 'C', true, 0, false, false, 0);
            $pdf->LN(10);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Write(0, 'Allegato A all\'art. 2, comma 2, del decreto n 608 del 30/12/2019. Dati da inserire nel quadro 1', '', 0, 'C', true, 0, false, false, 0);
            $pdf->LN(5);
            
            $pdf->setCellPaddings(2, 2, 2, 2);
            $pdf->SetFont('helvetica', '', 10);
            
            $w = array(10, 120, 130);
            $h = array(12, 30);
            
            $pdf->MultiCell($w[0], $h[0], '', 1, '', 0, 0, '', '', true);
            $pdf->MultiCell($w[1], $h[0], 'DESCRIZIONE', 1, 'C', 0, 0, '', '', true, 0, false, true, $h[0], 'M');
            $pdf->MultiCell(0, $h[0], 'IMPORTO', 1, 'C', 0, 1, '', '', true, 0, false, true, $h[0], 'M');
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->MultiCell($w[0], $h[1], 'A', 1, 'C', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell($w[1], $h[1], $A_text, 1, '', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->MultiCell(0, $h[1], $SumNot142.' €', 1, 'C', 0, 1, '', '', true, 0, false, true, $h[1], 'M');
            
            $pdf->MultiCell($w[0], $h[1], 'B', 1, 'C', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell($w[1], $h[1], $B_text, 1, '', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->MultiCell(0, $h[1], $Sum142.' €', 1, 'C', 0, 1, '', '', true, 0, false, true, $h[1], 'M');
            
            $pdf->MultiCell($w[0], $h[1], 'C', 1, 'C', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->MultiCell($w[1], $h[1], $C_text, 1, '', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->MultiCell(0, $h[1], ($Sum142 <0.01 ? '0,00 €' : 'Inserire Importo'), 1, 'C', 0, 1, '', '', true, 0, false, true, $h[1], 'M');
            
            $pdf->MultiCell($w[0], $h[1], 'D', 1, 'C', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->MultiCell($w[1], $h[1], $D_text, 1, '', 0, 0, '', '', true, 0, false, true, $h[1], 'M');
            $pdf->MultiCell(0, $h[1], 'Inserire Importo', 1, 'C', 0, 1, '', '', true, 0, false, true, $h[1], 'M');
            
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($w[2], $h[0], 'TOTALE PROVENTI VIOLAZIONI LIMITI MASSIMI Dl VELOCITA EX ART. 142, COMMA 12-BIS ', 1, 'R', 0, 0, '', '', true, 0, false, true, $h[0], 'M');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->MultiCell(0, $h[0], 'A + C + D', 1, 'C', 0, 1, '', '', true, 0, false, true, $h[0], 'M');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($w[2], $h[0], 'TOTALE PROVENTI VIOLAZIONI AL CODICE DELLA STRADA', 1, 'R', 0, 0, '', '', true, 0, false, true, $h[0], 'M');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->MultiCell(0, $h[0], 'A + B + C + D', 1, 'C', 0, 1, '', '', true, 0, false, true, $h[0], 'M');
            
            $pdf->LN(5);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Write(0, 'I proventi di cui ai punti C, D, se non inseriti a programma non vengono riportati in questa stampa.', '', 0, 'C', true, 0, false, false, 0);
            $pdf->LN(1);
            $pdf->Write(0, 'In tal caso vanno estratti dal programma della contabilità dell\'Ente.', '', 0, 'C', true, 0, false, false, 0);
            
            if (!is_dir(ROOT.'/doc/print/art142/')) {
                mkdir(ROOT.'/doc/print/art142/', 0777);
            }
            
            $pdf->Output(ROOT.'/doc/print/art142/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/print/art142/'.$FileName;
            break;
        }
        case 'Excel':{
            $FileName .= '.xls';
            ob_start(); ?>
        	<table>
	    		<tr></tr>
    			<tr></tr>
    			<tr><td>Ente: <?= $CityTitle.' ('.$CityId.')'; ?></td></tr>
    			<tr></tr>
    			<tr><td>Anno: <?= $Year; ?></td></tr>
    			<tr></tr>
    			<tr><td>Estrazione basata su: <?= $a_AllFee_text[$AllFee]; ?></td></td>
    			<tr></tr>
        		<tr><td>Allegato A all'art. 2, comma 2, del decreto n 608 del 30/12/2019. Dati da inserire nel quadro 1</td></tr>
        		<tr></tr>
    		</table>
    		<table border="1">
        		<tr bgcolor="lightblue">
        			<th rowspan="2" colspan="1"></th>
        			<th rowspan="2" colspan="18">DESCRIZIONE</th>
        			<th rowspan="2" colspan="2">IMPORTO</th>
    			</tr>
    			<tr></tr>
    			<tr>
    				<th bgcolor="lightblue" rowspan="4" colspan="1">A</th>
        			<td bgcolor="lightcyan" rowspan="4" colspan="18"><?= $A_text ?></th>
        			<th bgcolor="lavender" rowspan="4" colspan="2"><?= $SumNot142 ?> €</th>
    			</tr>
    			<tr></tr>
    			<tr></tr>
    			<tr></tr>
    			<tr>
    				<th bgcolor="lightblue" rowspan="4" colspan="1">B</th>
        			<td bgcolor="lightcyan" rowspan="4" colspan="18"><?= $B_text ?></th>
        			<th bgcolor="lavender" rowspan="4" colspan="2"><?= $Sum142 ?> €</th>
    			</tr>
    			<tr></tr>
    			<tr></tr>
    			<tr></tr>
    			<tr>
    				<th bgcolor="lightblue" rowspan="4" colspan="1">C</th>
        			<th bgcolor="lightcyan" rowspan="4" colspan="18"><?= $C_text ?></th>
        			<th bgcolor="lavender" rowspan="4" colspan="2"><?= $Sum142 < 0.01 ? '0,00 €' : 'Inserire Importo' ?> €</th>
    			</tr>
    			<tr></tr>
    			<tr></tr>
    			<tr></tr>
    			<tr>
    				<th bgcolor="lightblue" rowspan="4" colspan="1">D</th>
        			<th bgcolor="lightcyan" rowspan="4" colspan="18"><?= $D_text ?></th>
        			<th bgcolor="lavender" rowspan="4" colspan="2">Inserire Importo</th>
    			</tr>
    			<tr></tr>
    			<tr></tr>
    			<tr></tr>
    			<tr>
    				<th bgcolor="lightblue" rowspan="4" colspan="1"></th>
    				<th bgcolor="lightcyan" rowspan="4" colspan="18">TOTALE PROVENTI VIOLAZIONI LIMITI MASSIMI Dl VELOCITA EX ART. 142, COMMA 12-BIS</th>
        			<th bgcolor="lavender" rowspan="4" colspan="2">A + C + D</th>
    			</tr>
    			<tr></tr>
    			<tr></tr>
    			<tr></tr>
    			<tr>
    				<th bgcolor="lightblue" rowspan="4" colspan="1"></th>
    				<th bgcolor="lightcyan" rowspan="4" colspan="18">TOTALE PROVENTI VIOLAZIONI AL CODICE DELLA STRADA</th>
        			<th bgcolor="lavender" rowspan="4" colspan="2">A + B + C + D</th>
    			</tr>
    		</table>
    		<table>
    			<tr><td>I proventi di cui ai punti C, D, se non inseriti a programma non vengono riportati in questa stampa. In tal caso vanno estratti dal programma della contabilità dell'Ente</td></tr>
    		</table>
            <?php 
            $table = ob_get_clean();
            file_put_contents(ROOT.'/doc/print/art142/'.$FileName, "\xEF\xBB\xBF".$table);
            $_SESSION['Documentation'] = $MainPath.'/doc/print/art142/'.$FileName;
            break;
        }
    }
}

header("location: prn_collection.php".$str_GET_Parameter);