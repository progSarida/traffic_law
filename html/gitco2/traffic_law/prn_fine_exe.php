<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";
require(TCPDF . "/fpdi.php");
//per tentare di risolvere l'errore del massimo tempo di 60 secondi superato
ini_set('max_execution_time', 3000);
//per tentare di risolvere l'errore del massimo spazio di 512 MB superato
ini_set('memory_limit', '2048M');
//sopprime getimagesize(): corrupt JPEG data: lanciato da tcpdf //TODO monitorare
ini_set ('gd.jpeg_ignore_warning', 1);

//TODO Uncaught Exception: Unable to find "startxref" keyword.
//Succede quando tira su un documento che non è pdf, i documenti con documentationtype 2 nel caso di verbali contratto FineTypeId = 4
//potrebbero essere anche immagini caricate da mgmt_report.
function addToUnion($pdf_union, $file){
    $n_PageCount = $pdf_union->setSourceFile($file);
    
    for($p=1;$p<=$n_PageCount;$p++){
        
        $tmp_Page = $pdf_union->ImportPage($p);
        $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);
        
        $str_Format = ($tmp_Size['w']>$tmp_Size['h']) ? 'L' : 'P';
        
        $pdf_union->AddPage($str_Format, array($tmp_Size['w'],$tmp_Size['h']),false);
        $pdf_union->useTemplate($tmp_Page);
    }
    
    if($n_PageCount%2 == 1){
        $pdf_union->AddPage('P');
    }
}




$b_PrintFine = CheckValue('b_PrintFine','n');
$Search_FromRegDate = CheckValue('Search_FromRegDate','s');
$Search_ToRegDate = CheckValue('Search_ToRegDate','s');

$Search_Article = CheckValue('Search_Article','n');
$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

$strOrder = "FineDate, FineTime";


$Search_StatusType= CheckValue('Search_StatusType','n');

if($Search_StatusType==15){
    $str_Where .= " AND ProtocolId>0 AND StatusTypeId>14";
    $strOrder = "ProtocolId";
}else{
    $str_Where .= " AND StatusTypeId<15";
}

if($Search_Article>0){
    $str_Where .= " AND ArticleId=".$Search_Article;
}



if($Search_FromProtocolId>0)    $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId>0)      $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
if($Search_FromFineDate != "")
    $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate != "")
    $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
if($Search_Trespasser!="")
    $str_Where .= " AND (CompanyName LIKE '%".addslashes($Search_Trespasser)."%' OR TrespasserSurname LIKE '%".addslashes($Search_Trespasser)."%')";
        

if($Search_Locality != "")      $str_Where .= " AND Locality='".$Search_Locality."'";

if($b_PrintFine==5) {

    $str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
    $rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
    $a_ProtocolLetterLocality = array();
    while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    }
    
    $filename = $_SESSION['cityid'] . '_fine_contability_' . date("Y-m-d_H-i") . '.xls';

    $strOrder = "ProtocolId";

    $str_Where .= " AND ProtocolId>0";


    if ($Search_FromRegDate != "") {
        $str_Where .= " AND RegDate>='" . DateInDB($Search_FromRegDate) . "'";
    }
    if ($Search_ToRegDate != "") {
        $str_Where .= " AND RegDate<='" . DateInDB($Search_ToRegDate) . "'";
    }


    $table_rows = $rs->Select('V_FineAll', $str_Where, $strOrder);

    $str_Content = '
        <table border="1">
            <tbody>
    	        <tr>
    	            <td>#</td>
    	            <td>TARGA</td>
    	            <td>CRON</td>
                    <td>DATA VIOLAZIONE</td>
                    <td>ARTICOLO/I</td>
                    <td>LETTERA VERBALE</td>
                </tr>    
    	';
    while ($table_row = mysqli_fetch_array($table_rows)) {

        $str_Paragraph = ($table_row['Paragraph']=="0") ? "" : $table_row['Paragraph'];
        $str_Letter = ($table_row['Letter']=="0") ? "" : $table_row['Letter'];


        $str_Article = $table_row['Article']." ".$str_Paragraph." ".$str_Letter;


        $str_ProtocolLetter = ($table_row['CountryId']=="Z000") ? $a_ProtocolLetterLocality[$table_row['Locality']]['NationalProtocolLetterType1'] : $a_ProtocolLetterLocality[$table_row['Locality']]['ForeignProtocolLetterType1'];

        if($table_row['ArticleNumber']>1){
            $rs_AdditionalArticle = $rs->SelectQuery("
                SELECT A.Article, A.Paragraph, A.Letter FROM Article A JOIN FineAdditionalArticle FAA
                ON A.Id = FAA.ArticleId 
                WHERE FAA.FineId=".$table_row['Id']
            );

            while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
                $str_Paragraph = ($r_AdditionalArticle['Paragraph']=="0") ? "" : $r_AdditionalArticle['Paragraph'];
                $str_Letter = ($r_AdditionalArticle['Letter']=="0") ? "" : $r_AdditionalArticle['Letter'];

                $str_Article .= " - ".$r_AdditionalArticle['Article']." ".$str_Paragraph." ".$str_Letter;

            }

        }

        $str_Content .= '
                <tr>
                    <td>'. $table_row['Id'] .'</td>
                    <td>'. $table_row['VehiclePlate'] .'</td>
                    <td>'. $table_row['ProtocolId'] .'</td>
                    <td>'. DateOutDB($table_row['FineDate']) .'</td>
                    <td>'. $str_Article .'</td>
                    <td>'.$str_ProtocolLetter.'</td>
                </tr>    
            ';

    }
    $str_Content .= '</tbody></table>';
    
    if (!is_dir(ROOT . "doc/print/fine_contability")) {
        mkdir(ROOT . "doc/print/fine_contability", 0777);
    }
    
    file_put_contents(ROOT . "/doc/print/art126/". $filename, $str_Content);
    $_SESSION['Documentation'] = $MainPath . '/doc/print/art126/' . $filename;

} else if($b_PrintFine==2 || $b_PrintFine==3 || $b_PrintFine==4){
    
    //VERBALI
    
    $pdf_union = new FPDI();
    $pdf_union->setHeaderFont(array('helvetica', '', 8));
    $pdf_union->setFooterFont(array('helvetica', '', 8));
    $pdf_union->setPrintHeader(false);
    $pdf_union->setPrintFooter(false);
    
    $pdf_union->SetFillColor(255, 255, 255);
    $pdf_union->SetTextColor(0, 0, 0);
    
    $a_docs = array();
    $s_documentTypes = 2;
    
    switch($b_PrintFine){
        case 2: $s_documentTypes = '2'; break;
        case 3: $s_documentTypes = '10,11,12'; break;
        case 4: $s_documentTypes = '2,10,11,12'; break;
    }
    
    $str_WhereCity = ($r_Customer['CityUnion'] > 1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
    $rs_ProtocolLetter = $rs->Select(MAIN_DB . '.City', $str_WhereCity);
    $a_ProtocolLetterLocality = array();
    while ($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)) {
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
    }
    
    
    $table_rows = $rs->SelectQuery("
        SELECT F.*, FD.Documentation, FD.DocumentationTypeId
        FROM V_FineAll F 
        JOIN FineDocumentation FD ON F.Id=FD.FineId 
        WHERE DocumentationTypeId IN($s_documentTypes) AND ".$str_Where." ORDER BY ProtocolId");
    
    while ($table_row = mysqli_fetch_array($table_rows)) {
        $str_ProtocolLetter = ($table_row['CountryId']=="Z000") ? $a_ProtocolLetterLocality[$table_row['Locality']]['NationalProtocolLetterType1'] : $a_ProtocolLetterLocality[$table_row['Locality']]['ForeignProtocolLetterType1'];
        
        $a_docs[$table_row['Id']]['CountryId'] = $table_row['CountryId'];
        $a_docs[$table_row['Id']]['ProtocolId'] = $table_row['ProtocolId'];
        $a_docs[$table_row['Id']]['ProtocolYear'] = $table_row['ProtocolYear'];
        $a_docs[$table_row['Id']]['ProtocolLetter'] = $str_ProtocolLetter;
        $a_docs[$table_row['Id']][$table_row['DocumentationTypeId']] = $table_row['Documentation'];
    }
    
    foreach ($a_docs as $FineId => $a_documents) {
        
        $b_frontNotification = false;
        $str_Folder = (($a_documents['CountryId']=='Z000') ? NATIONAL_FINE : FOREIGN_FINE)."/".$_SESSION['cityid']."/".$FineId."/";

        if(isset($a_documents[2]) && file_exists($str_Folder.$a_documents[2])){
            addToUnion($pdf_union, $str_Folder.$a_documents[2]);
        }
        
        if(isset($a_documents[10]) && file_exists($str_Folder.$a_documents[10])){
            if (@mime_content_type($str_Folder.$a_documents[10]) == 'image/jpeg'){
                $b_frontNotification = true;
                $pdf_union->AddPage();
                $pdf_union->MultiCell(0, 0, 'Cron. Nr: '.$a_documents['ProtocolId'].'/'.$a_documents['ProtocolYear'].'/'.$a_documents['ProtocolLetter'], 0, 'C', 1, 1, 0, 5, true);
                $pdf_union->Image($str_Folder.$a_documents[10],'','',0,133,'','','',true,300,'',false,false,1,false,false,true);
            } else addToUnion($pdf_union, $str_Folder.$a_documents[10]);
        }
        
        if(isset($a_documents[11]) && file_exists($str_Folder.$a_documents[11])){
            if (@mime_content_type($str_Folder.$a_documents[11]) == 'image/jpeg'){
                if($b_frontNotification){
                    $pdf_union->Image($str_Folder.$a_documents[11],'',133,0,133,'','','',true,300,'',false,false,1,false,false,true);
                } else {
                    $pdf_union->AddPage();
                    $pdf_union->MultiCell(0, 0, 'Cron. Nr: '.$a_documents['ProtocolId'].'/'.$a_documents['ProtocolYear'].'/'.$a_documents['ProtocolLetter'], 0, 'C', 1, 1, 0, 5, true);
                    $pdf_union->Image($str_Folder.$a_documents[11],'','',0,133,'','','',true,300,'',false,false,1,false,false,true);
                }
            } else addToUnion($pdf_union, $str_Folder.$a_documents[11]);
        }
        
        if(isset($a_documents[12]) && file_exists($str_Folder.$a_documents[12])){
            if (@mime_content_type($str_Folder.$a_documents[12]) == 'image/jpeg'){
                $pdf_union->AddPage();
                $pdf_union->Image($str_Folder.$a_documents[12],'','',0,'','','','',true,300,'',false,false,0,false,false,true);
            } else addToUnion($pdf_union, $str_Folder.$a_documents[12]);
        }


    }
    $pdf_union->Output(ROOT.'/doc/print/export_fine.pdf', "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/export_fine.pdf';

} else if($b_PrintFine==0){

    //IMMAGINI RILIEVI

    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


    $pdf->SetMargins(10,10,10);



    $table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
    $table_row = mysqli_fetch_array($table_rows);

    $MangerName = $table_row['ManagerName'];
    $ManagerAddress = $table_row['ManagerAddress'];
    $ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
    $ManagerPhone = $table_row['ManagerPhone'];


    $table_rows = $rs->Select('V_FineArticle',$str_Where, $strOrder);


    while ($table_row = mysqli_fetch_array($table_rows)) {



        $FineDate = $table_row['FineDate'];
        $aTime = explode(":",$table_row['FineTime']);
        $Hour = $aTime[0];


        if($table_row['TimeTypeId']==1){
            $SunnyHour = $aTime[0]-1;
            $Hour = $SunnyHour;
        }
        if($table_row['TimeTypeId']==2){
            $LegalHour = $aTime[0]+1;
            $Hour = $LegalHour;
        }
        $FineTime = $Hour.":".$aTime[1];



        $VehiclePlate =	$table_row['VehiclePlate'];


        $rs_localities = $rs->Select(MAIN_DB.".City","Id='".$table_row['Locality']."'");
        $rs_locality = mysqli_fetch_array($rs_localities);

        $locality = $rs_locality['Title'] .' - '.$table_row['Address'];






        $rs_rows = $rs->Select('FineArticle',"FineId=".$table_row['Id']);
        $r_row = mysqli_fetch_array($rs_rows);

        $ArticleId = $r_row['ArticleId'];
        $Speed = $r_row['Speed'];
        $SpeedControl = $r_row['SpeedControl'];

        $TimeTLightFirst = $r_row['TimeTLightFirst'];
        $TimeTLightSecond = $r_row['TimeTLightSecond'];

        $SpeedLimit = $r_row['SpeedLimit'];

        if($Speed>0){
            $str_SpeedTraffic_Label = "Velocita";
            $str_SpeedTraffic_Field = intval($SpeedControl);
        }else{
            $str_SpeedTraffic_Label = "Intervallo fotogrammi";
            $str_SpeedTraffic_Field = $TimeTLightFirst."/".$TimeTLightSecond;

        }


        $rs_rows = $rs->Select('Article',"Id=".$ArticleId);
        $r_row = mysqli_fetch_array($rs_rows);

        $Article = $r_row['Article'];
        $Paragraph = $r_row['Paragraph'];



        $pdf->AddPage();
        $pdf->SetFont('arial', '', 10, '', true);

        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




        $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


        $pdf->LN(5);


        $rs_docs = $rs->Select("FineDocumentation","FineId=".$table_row['Id']." AND DocumentationTypeId = 1");
        $n_Count=0;
        if (mysqli_num_rows($rs_docs) > 0){
            while ($rs_doc = mysqli_fetch_array($rs_docs)){
                $n_Count++;
                if($table_row['CountryId']=='Z000'){
                    $DocumentationV = NATIONAL_VIOLATION_HTML."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    $DocumentationVRoot = NATIONAL_VIOLATION."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    $DocumentationN = NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    $DocumentationNRoot = NATIONAL_FINE."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    
                }else{
                    $DocumentationV = FOREIGN_VIOLATION_HTML."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    $DocumentationVRoot = FOREIGN_VIOLATION."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    $DocumentationN = FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                    $DocumentationNRoot = FOREIGN_FINE."/".$_SESSION['cityid']."/".$table_row['Id']."/".$rs_doc['Documentation'];
                }
                
                //TODO non più utilizzato (riga 326-337)
                //list($width, $height, $type, $attr) = file_exists($Documentation) ? getimagesize($Documentation) : null;
                
                $pdf->AddPage();
                $pdf->SetFont('arial', '', 10, '', true);
                
                $pdf->setFooterData(array(0,64,0), array(0,64,128));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetTextColor(0, 0, 0);
                
                $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);
                    
                if (file_exists($DocumentationVRoot))
                    $pdf->Image($DocumentationV, 60, 30, 180, 120);
                else if (file_exists($DocumentationNRoot))
                    $pdf->Image($DocumentationN, 60, 30, 180, 120);
                    
            }
        }

        /*
            if($width>800 && $height>700){
                $img = new Imagick($DocumentationRoot);
                $width = intval($img->getimagewidth() / 3);
                $height = intval($img->getimageheight() / 3);
                $img->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
                $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality(40);
                $img->stripImage();
                $img->writeImage($DocumentationRoot);


            }
        */

        $pdf->setY(160);

        if($Search_Status==15){
            $y = $pdf->getY();
            $pdf->writeHTMLCell(240, 4, 10, $y, "Cron: ".$table_row['ProtocolId']."/".$table_row['ProtocolYear'], 1, 0, 1, true, 'L', true);
            $pdf->LN(5);

        }

        $y = $pdf->getY();
        $pdf->writeHTMLCell(30, 4, 10, $y, 'Data', 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 40, $y, $FineDate, 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 90, $y, 'Ora', 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(100, 4, 120, $y, $FineTime.$table_row['TimeDescriptionIta'], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 220, $y, '', 1, 0, 1, true, 'C', true);

        $pdf->LN(5);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(30, 4, 10, $y, 'Targa', 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 40, $y, $VehiclePlate, 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 70, $y, 'Localita', 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(150, 4, 100, $y, $locality, 1, 0, 1, true, 'C', true);
        $pdf->LN(5);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(30, 4, 10, $y, 'Articolo', 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 40, $y, $Article."/".$Paragraph, 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 70, $y, 'Limite', 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(30, 4, 100, $y, intval($SpeedLimit), 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(60, 4, 130, $y, $str_SpeedTraffic_Label, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(60, 4, 190, $y, $str_SpeedTraffic_Field, 1, 0, 1, true, 'C', true);


    }


    $FileName = 'export.pdf';

    $pdf->Output(ROOT.'/doc/print/'.$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/'.$FileName;
} else {
    
    //ELENCO
    
    $rs_Result = $rs->Select('Result', "1=1");
    while ($r_Result = mysqli_fetch_array($rs_Result)){
        $a_Result[$r_Result['Id']] = $r_Result['Title'];
    }
    $a_GradeType = array("","I","II","III");


    $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


    $pdf->SetMargins(10,10,10);



    $table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
    $table_row = mysqli_fetch_array($table_rows);

    $MangerName = $table_row['ManagerName'];
    $ManagerAddress = $table_row['ManagerAddress'];
    $ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
    $ManagerPhone = $table_row['ManagerPhone'];
    $b_ExtReg = $table_row['ExternalRegistration'];

    $pdf->AddPage();
    $pdf->SetFont('arial', '', 10, '', true);

    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




    $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


    $pdf->LN(15);





    $table_rows = $rs->Select('V_FineComplete',$str_Where, $strOrder);
    $pdf->SetFont('arial', '', 7, '', true);
    $n_ContRow = 0;
    while ($table_row = mysqli_fetch_array($table_rows)) {
        $n_ContRow++;

        if($n_ContRow==14){
            $pdf->AddPage();
            $pdf->SetFont('arial', '', 10, '', true);

            $pdf->setFooterData(array(0,64,0), array(0,64,128));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);




            $pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);


            $pdf->LN(15);

            $n_ContRow = 1;

            $pdf->SetFont('arial', '', 7, '', true);
        }




        $rs_Row = $rs->Select('V_FineHistory',"Id=".$table_row['FineId']." AND NotificationTypeId=6");
        $r_Row = mysqli_fetch_array($rs_Row);

        $str_PreviousId = "";
        if($table_row['PreviousId']>0){
            $rs_Previous = $rs->Select('Fine',"Id=".$table_row['PreviousId']);
            $r_Previous = mysqli_fetch_array($rs_Previous);
            $str_PreviousId = 'Verbale collegato Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'];
        }

        $str_Archive = "";
        if($table_row['StatusTypeId']==35){
            $rs_Archive = $rs->SelectQuery("
                SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
                FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
                WHERE FA.FineId=".$table_row['FineId']);
            $r_Archive = mysqli_fetch_array($rs_Archive);

            $str_Archive = 'Archiviato: '. DateOutDB($r_Archive['ArchiveDate']);



        }else if($table_row['StatusTypeId']==36){
            $rs_Previous = $rs->Select('Fine',"PreviousId=".$table_row['FineId']);
            $r_Previous = mysqli_fetch_array($rs_Previous);

            $str_PreviousId = 'Noleggio ristampato con Cron '. $r_Previous['ProtocolId'].'/'.$r_Previous['ProtocolYear'];

        }


        $str_Article = "Art. ";
        $rs_article = $rs->Select('Article',"Id=".$table_row['ArticleId']);
        $r_article = mysqli_fetch_array($rs_article);

        $str_Article .= $r_article['Article'].' '.str_replace("0","",$r_article['Paragraph']).' '.$r_article['Letter'];


        $rs_Locality = $rs->Select(MAIN_DB.".City","Id='".$table_row['Locality']."'");
        $r_Locality = mysqli_fetch_array($rs_Locality);

        $str_Locality = $r_Locality['Title'] .' - '.$table_row['Address'];

        $str_Protocol = '';
        if($b_ExtReg) {
            $ExternalProtocol = ($table_row['ExternalProtocol']>0)? 'N. '.$table_row['ExternalProtocol'].'/'.$table_row['ExternalYear'] : '';
            $str_Protocol = (!is_null($r_Row['ExternalDate'])) ? 'Protocollo: ' . DateOutDB($r_Row['ExternalDate']).' '.$ExternalProtocol : '';
        }

        $str_Flow = (!is_null($r_Row['FlowDate'])) ? 'Flusso: '. DateOutDB($r_Row['FlowDate']) : '';

        $str_Send = (! is_null($r_Row['SendDate'])) ? 'Invio: '. DateOutDB($r_Row['SendDate']) : '';

        $str_Result = "";
        if (! is_null($r_Row['ResultId'])) {
            $str_Result = (! is_null($r_Row['DeliveryDate'])) ? 'Notificato: '. DateOutDB($r_Row['DeliveryDate']) : $a_Result[$r_Row['ResultId']];
        }


        $str_Payment = '';
        $rs_Row = $rs->Select('FinePayment',"FineId=".$table_row['FineId']);
        if(mysqli_num_rows($rs_Row)>0){
            $r_Row = mysqli_fetch_array($rs_Row);
            $str_Payment = 'Pagato: '. DateOutDB($r_Row['PaymentDate']);
        }



        $str_Dispute = '';
        $rs_Row = $rs->Select('V_FineDispute',"FineId=".$table_row['FineId']." ORDER BY GradeTypeId DESC");
        if(mysqli_num_rows($rs_Row)>0){
            $r_Row = mysqli_fetch_array($rs_Row);
            $str_Dispute = $a_GradeType[$r_Row['GradeTypeId']].' Grado - '.$r_Row['OfficeTitle'].' '. $r_Row['OfficeCity'].' Depositato in data '. DateOutDB($r_Row['DateFile']);
        }

        $rs_Row = $rs->Select('FineCommunication',"FineId=".$table_row['FineId']);
        $r_Row = mysqli_fetch_array($rs_Row);
        $str_Communication = (! is_null($r_Row['CommunicationDate'])) ? 'Comunicazione dati: '.DateOutDB($r_Row['CommunicationDate']) : '';













        $y = $pdf->getY();
        $pdf->writeHTMLCell(15, 4, 10, $y, $table_row['ProtocolId'].' / '.$table_row['ProtocolYear'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 25, $y, DateOutDB($table_row['FineDate']) .' '. TimeOutDB($table_row['FineTime']), 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(15, 4, 50, $y, $table_row['VehiclePlate'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(85, 4, 65, $y, $table_row['CompanyName'] .' '.$table_row['Surname'] .' '.$table_row['Name'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 150, $y, $str_Protocol, 1, 0, 1, true, 'L', true);
        $pdf->LN(4);


        $y = $pdf->getY();
        $pdf->writeHTMLCell(140, 4, 10, $y, $str_Locality, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 150, $y, $str_Article, 1, 0, 1, true, 'L', true);
        $pdf->LN(4);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(30, 4, 10, $y, 'Ref: '.$table_row['Code'], 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 40, $y, $str_Flow, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 65, $y, $str_Send, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(35, 4, 90, $y, $str_Result, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(25, 4, 125, $y, $str_Payment, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 150, $y, $str_Archive, 1, 0, 1, true, 'L', true);
        $pdf->LN(4);

        $y = $pdf->getY();
        $pdf->writeHTMLCell(50, 4, 10, $y, $str_PreviousId, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(50, 4, 60, $y, $str_Communication, 1, 0, 1, true, 'L', true);
        $pdf->writeHTMLCell(90, 4, 110, $y, $str_Dispute, 1, 0, 1, true, 'L', true);
        $pdf->LN(6);
    }

    $FileName = 'export.pdf';

    $pdf->Output(ROOT.'/doc/print/'.$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/print/'.$FileName;
}

header("location: prn_fine.php" . $str_GET_Back_Page . "&btn_search=1&Search_StatusType=" . $Search_StatusType . "&b_PrintFine=" . $b_PrintFine . "&Search_Article=" . $Search_Article);
