<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";

$aLan = unserialize(LANGUAGE);

$aVehicleTypeId = array("","Auto","Moto","Altro","Altro","Altro","Altro","Altro","Altro","Altro", "Altro", "Altro","Altro");
$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";


$ProtocolYear = $_SESSION['year'];
$CityId = "'".$_SESSION['cityid']."'";
$Locality = "'".$_SESSION['cityid']."'";
$StatusTypeId = 5;
$NotificationTypeId = 1;


$NotificationDate = date("Y-m-d");
$FlowDate = date("Y-m-d");
$SendDate = date("Y-m-d");
$PrintDate = date("Y-m-d");
$P = "frm_send_request.php";

$Search_Country= CheckValue('Search_Country','s');
$Search_Zone= CheckValue('Search_Zone','s');

$b_TicinoRequest = 0;
$a_TicinoFile = array();
$b_FirstPage = 1;
if($Search_Country =='Z133' && $Search_Zone=='TI'){
    $b_TicinoRequest = 1;
}


$P = "frm_send_request.php?Search_Zone=".$Search_Zone."&Search_Country=".$Search_Country;


if(isset($_POST['checkbox'])) {

    $ultimate = CheckValue('ultimate','s');

    $table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
    $table_row = mysqli_fetch_array($table_rows);


    $RowForPage = 28;

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Request');
    $pdf->SetSubject('Request');
    $pdf->SetKeywords('');


    $pdf->SetMargins(10,10,10);

    $pdf->AddPage();
    $pdf->SetFont('arial', '', 10, '', true);

    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);

    $ManagerName = $table_row['ManagerName'];
    $Reference = $table_row['Reference'];

    // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
    $pdf->writeHTMLCell(150, 0, 30, '', $table_row['ManagerName'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $table_row['ManagerAddress'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")", 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(150, 0, 30, '', $table_row['ManagerPhone'], 0, 0, 1, true, 'L', true);

    $pdf->LN(20);

    $str_Where = "CountryId='".$Search_Country."'";

    if($Search_Zone!="") $str_Where.= " AND Region='".$Search_Zone."'";


    $table_rows = $rs->Select('Entity', $str_Where);
    $table_row = mysqli_fetch_array($table_rows);

    $pdf->writeHTMLCell(100, 0, 110, '', $table_row['Tar_Indirizzo_Prima_Riga'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $table_row['Tar_Indirizzo_Seconda_Riga'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $table_row['Tar_Indirizzo_Terza_Riga'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $table_row['Tar_Indirizzo_Quarta_Riga'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $table_row['Tar_Indirizzo_Quinta_Riga'], 0, 0, 1, true, 'L', true);
    $pdf->LN(4);
    $pdf->writeHTMLCell(100, 0, 110, '', $table_row['Tar_Email'], 0, 0, 1, true, 'L', true);

    if(strlen(trim($table_row['Fax']))>0){
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', "Fax :".$table_row['Fax'], 0, 0, 1, true, 'L', true);
    }
    $pdf->LN(10);

    $LanguageId = $table_row['Tar_Linguaggio'];
    $EntityId = $table_row['Id'];

    $table_rows = $rs->Select('Form',"FormTypeId=1 AND LanguageId=".$LanguageId);
    $table_row = mysqli_fetch_array($table_rows);

    $Content = str_replace("{ManagerName}",$ManagerName,$table_row['Content']);
    if($ultimate || $b_TicinoRequest){
        $RndCode = "";
        for($i=0;$i<5;$i++){
            $n = rand(1, 24);
            $RndCode .= substr($strCode,$n,1);
            $n = rand(0, 9);
            $RndCode .= $n;
        }


        $FlowNumber = $RndCode;
        $zone = (!isset($Search_Zone)) ? $Search_Zone : "00";


        $Documentation = date("Y-m-d")."_".$_SESSION['cityid']."_".$Search_Country."_".$zone."_".$FlowNumber.".pdf";
        $Content = str_replace("{ProtocolNumber}",$FlowNumber,$Content);
        $Content = str_replace("{Reference}",$Reference,$Content);
        $FileName = $Documentation;



    }else{
        $Content = str_replace("{ProtocolNumber}","PROVVISORIO",$Content);
        $Content = str_replace("{Reference}","PROVVISORIO",$Content);
    }
    $aMainPart = explode("<main_part>",$Content);
    $aRow = explode("<row>",$aMainPart[1]);

    $pdf->writeHTMLCell(180, 0, 10, '', $aRow[1], 0, 0, 1, true, 'L', true);
    $pdf->LN(10);

    $pdf->writeHTMLCell(180, 0, 10, '', $aRow[2], 0, 0, 1, true, 'L', true);
    $pdf->LN(10);

    $pdf->writeHTMLCell(180, 0, 10, '', $aRow[3], 0, 0, 1, true, 'L', true);
    $pdf->LN(20);

    $pdf->writeHTMLCell(180, 0, 10, '', $aRow[4], 0, 0, 1, true, 'J', true);
    $pdf->LN(20);





    if($b_TicinoRequest){
        $str_Content = '<table border="1">';





        $table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
        $table_row = mysqli_fetch_array($table_rows);

        $ManagerName = $table_row['ManagerName'];
        $Reference = $table_row['Reference'];


        $str_Content .= '
        <tr>
            <td colspan="4">
                '. $table_row['ManagerName'] .'
            </td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4">
                '. $table_row['ManagerAddress'] .'
            </td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4">
                '. $table_row['ManagerZIP'].' '.$table_row['ManagerCity'].' ('.$table_row['ManagerProvince'].')' .'
            </td>
            <td colspan="4"></td>
        </tr>                
        <tr>
            <td colspan="4">
                '. $table_row['ManagerPhone'] .'
            </td>
            <td colspan="4"></td>
        </tr>       
        ';
        $str_Where = "CountryId='".$Search_Country."'";

        if($Search_Zone!="") $str_Where.= " AND Region='".$Search_Zone."'";

        $table_rows = $rs->Select('Entity', $str_Where);
        $table_row = mysqli_fetch_array($table_rows);

        $str_Content .= '
        <tr>
            <td colspan="4"></td>
            <td colspan="4">
                '. $table_row['Tar_Indirizzo_Prima_Riga'] .'
            </td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="4">
                '. $table_row['Tar_Indirizzo_Seconda_Riga'] .'
            </td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="4">
                '. $table_row['Tar_Indirizzo_Terza_Riga'] .'
            </td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="4">
                '. $table_row['Tar_Indirizzo_Quarta_Riga'] .'
            </td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="4">
                '. $table_row['Tar_Indirizzo_Quinta_Riga'] .'
            </td>
        </tr> 
        <tr>
            <td colspan="4"></td>
            <td colspan="4">
                '. $table_row['Tar_Email'] .'
            </td>
        </tr>                                         
        ';


        $LanguageId = $table_row['Tar_Linguaggio'];
        $EntityId = $table_row['Id'];

        $table_rows = $rs->Select('Form',"FormTypeId=1 AND LanguageId=".$LanguageId);
        $table_row = mysqli_fetch_array($table_rows);

        $Content = str_replace("{ManagerName}",$ManagerName,$table_row['Content']);

        $RndCode = "";
        for($i=0;$i<5;$i++){
            $n = rand(1, 24);
            $RndCode .= substr($strCode,$n,1);
            $n = rand(0, 9);
            $RndCode .= $n;
        }


        $FlowNumber = $RndCode;
        $zone = (!isset($Search_Zone)) ? $Search_Zone : "00";


        $Documentation = date("Y-m-d")."_".$_SESSION['cityid']."_".$Search_Country."_".$zone."_".$FlowNumber.".xls";
        $Content = str_replace("{ProtocolNumber}",$FlowNumber,$Content);
        $Content = str_replace("{Reference}",$Reference,$Content);
        $FileName = $Documentation;


        $aMainPart = explode("<main_part>",$Content);
        $aRow = explode("<row>",$aMainPart[1]);

        $str_Content .= '
	        <tr>
	            <td colspan="8">
	            '. $aRow[1] .'
                </td>
	        </tr>
	        <tr>
	            <td colspan="8">
	            '. $aRow[2] .'
                </td>
	        </tr>	        
	        <tr>
	            <td colspan="8">
	            '. $aRow[3] .'
                </td>
	        </tr>	        
	        <tr>
	            <td colspan="8">
	            '. $aRow[4] .'
                </td>
	        </tr>	                	        
	    ';



        $aField= explode("<col>",$aRow[5]);

        $str_Content .= '
	        <tr>
	            <td>
	            '. $aField[1] .'
                </td>
	             <td>
	            '. $aField[2] .'
                </td>
	            <td>
	            '. $aField[5] .'
                </td>
	             <td>
	            '. $aField[6] .'
                </td>
	            <td>
	            '. $aField[7] .'
                </td>
	             <td>
	            '. $aField[8] .'
                </td>
	        </tr>	                	        
	    ';



        $pagerow = 0;

        foreach($_POST['checkbox'] as $value) {

            $pagerow++;


            $table_rows = $rs->Select('V_Violation',"VehiclePlate='".$value."' AND StatusTypeId=1", 'Id');
            $table_row = mysqli_fetch_array($table_rows);

            $VehicleType = str_replace("Autoveicolo","Auto",$table_row['VehicleTitle'.$aLan[$LanguageId]]);
            $VehicleType = str_replace("Motoveicolo","Moto",$VehicleType);





            $str_Content .= '
	        <tr>
	            <td>
	            '. $VehicleType .'
                </td>
	             <td>
	            '. strtoupper($table_row['VehiclePlate']) .'
                </td>
	            <td>
	            '. DateOutDB($table_row['FineDate']) .'
                </td>
	             <td>
	            '. TimeOutDB($table_row['FineTime']) .'
                </td>
	            <td>
	            '. $table_row['Address'] .'
                </td>
	            <td>
	            '. $table_row['Article'].' '.$table_row['Paragraph'].' '.$table_row['Letter']. '('.$table_row['ViolationTitle'].')
                </td>
	        </tr>	                	        
	    ';
            if ($ultimate){

                $rs->Start_Transaction();
                $table_rows = $rs->Select('V_Violation',"VehiclePlate='".$value."' AND StatusTypeId<10", 'Id');
                while($table_row = mysqli_fetch_array($table_rows)){
                    $a_FineHistory = array(
                        array('field'=>'NotificationTypeId','selector'=>'value','type'=>'int','value'=>$NotificationTypeId),
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$table_row['Id'],'settype'=>'int'),
                        array('field'=>'EntityId','selector'=>'value','type'=>'int','value'=>$EntityId,'settype'=>'int'),
                        array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                        array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>$FlowDate),
                        array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$SendDate),
                        array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$PrintDate),
                        array('field'=>'FlowNumber','selector'=>'value','type'=>'str','value'=>$FlowNumber),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),

                    );
                    $rs->Insert('FineHistory',$a_FineHistory);

                    $a_Fine = array(
                        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
                    );
                    $rs->Update('Fine',$a_Fine, 'Id='.$table_row['Id']);

                }
                $rs->End_Transaction();
            }

        }

        $aRow = explode("<row>",$aMainPart[2]);

        $str_Content .= '
	        <td>
	            <td colspan="8">
	            '. $aRow[1] .'
                </td>
	        </tr>
	        <tr>
	            <td colspan="4">
	            '. $aRow[2] .'
                </td>
                </td colspan="4"></td>
	        </tr>	        
	        <tr>
	            <td colspan="4">
	            '. $aRow[3] .'
                </td>
                </td colspan="4"></td>
	        </tr>	        
	        <tr>
	            <td colspan="4">
	            '. $aRow[4] .'
                </td>
                </td colspan="4"></td>
	        </tr>
	        <tr>
	            <td colspan="4">
	            '. $aRow[5] .'
                </td>
                </td colspan="4"></td>
	        </tr>
	        <tr>
	            <td colspan="4">
	            '. $aRow[6] .'
                </td>
                </td colspan="4"></td>
	        </tr>		
	        <tr>
	            <td colspan="4">
	            '. $aRow[7] .'
                </td>
                </td colspan="4"></td>
	        </tr>
	        <tr>
                <td colspan="4"></td>
	            <td colspan="4">
	            '. $aRow[8] .'
                </td>
	        </tr>	        		                		        	                	        
	    ';





        header ("Content-Type: application/vnd.ms-excel");
        header ("Content-Disposition: inline; filename=$FileName");

        $str_Content .= '</table>';
        echo $str_Content;






    } else{

        $aField= explode("<col>",$aRow[5]);

        $y = $pdf->getY();
        $pdf->SetFont('arial', '', 10, '', true);
        $pdf->writeHTMLCell(15, 4, 5, $y, $aField[1], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(20, 4, 20, $y, $aField[2], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(20, 4, 40, $y, $aField[3], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(15, 4, 60, $y, $aField[4], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(20, 4, 75, $y, $aField[5], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(10, 4, 95, $y, $aField[6], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(65, 4, 105, $y, $aField[7], 1, 0, 1, true, 'C', true);
        $pdf->writeHTMLCell(30, 4, 170, $y, $aField[8], 1, 0, 1, true, 'C', true);

        $pdf->LN(4);
        $pdf->SetFont('arial', '', 8, '', true);

        $pagerow = 0;

        foreach($_POST['checkbox'] as $value) {

            $pagerow++;
            if($pagerow>$RowForPage){

                $RowForPage = 50;
                $pdf->AddPage();
                $pdf->SetFont('arial', '', 10, '', true);

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetTextColor(0, 0, 0);
                $y = $pdf->getY();

                $pdf->writeHTMLCell(15, 4, 5, $y, $aField[1], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, 20, $y, $aField[2], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, 40, $y, $aField[3], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(15, 4, 60, $y, $aField[4], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(20, 4, 75, $y, $aField[5], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(10, 4, 95, $y, $aField[6], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(65, 4, 105, $y, $aField[7], 1, 0, 1, true, 'C', true);
                $pdf->writeHTMLCell(30, 4, 170, $y, $aField[8], 1, 0, 1, true, 'C', true);


                $pdf->LN(4);

                $pdf->SetFont('arial', '', 8, '', true);
                $pagerow = 1;
            }

            $y = $pdf->getY();

            $table_rows = $rs->Select('V_Violation',"VehiclePlate='".$value."' AND StatusTypeId=1", 'Id');
            $table_row = mysqli_fetch_array($table_rows);

            $VehicleType = str_replace("Autoveicolo","Auto",$table_row['VehicleTitle'.$aLan[$LanguageId]]);
            $VehicleType = str_replace("Motoveicolo","Moto",$VehicleType);



            $str_Address = (strlen($table_row['Address'])>30) ? substr($table_row['Address'],0,30).'...' : $table_row['Address'];

            $pdf->writeHTMLCell(15, 4, 5, $y, $VehicleType, 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(20, 4, 20, $y, strtoupper($table_row['VehiclePlate']), 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(20, 4, 40, $y, $table_row['VehicleBrand'], 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(15, 4, 60, $y, $table_row['VehicleColor'], 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(20, 4, 75, $y, DateOutDB($table_row['FineDate']), 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(10, 4, 95, $y, TimeOutDB($table_row['FineTime']), 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(65, 4, 105, $y, $str_Address, 1, 0, 1, true, 'L', true);
            $pdf->writeHTMLCell(30, 4, 170, $y, $table_row['Article']." ".$table_row['Paragraph']." ".$table_row['Letter']. "(".$table_row['ViolationTitle'].")", 1, 0, 1, true, 'L', true);

            $pdf->LN(4);

        }


        $aRow = explode("<row>",$aMainPart[2]);

        $pdf->LN(17);

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[1], 0, 0, 1, true, 'L', true);
        $pdf->LN(16);

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[2], 0, 0, 1, true, 'L', true);
        $pdf->LN(3);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[3], 0, 0, 1, true, 'L', true);
        $pdf->LN(3);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[4], 0, 0, 1, true, 'L', true);
        $pdf->LN(3);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[5], 0, 0, 1, true, 'L', true);
        $pdf->LN(3);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[6], 0, 0, 1, true, 'L', true);
        $pdf->LN(3);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[7], 0, 0, 1, true, 'L', true);
        $pdf->LN(6);

        $pdf->SetFont('arial', '', 8, '', true);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[8], 0, 0, 1, true, 'R', true);



        if (!is_dir(FOREIGN_REQUEST."/".$_SESSION['cityid'])) {
            mkdir(FOREIGN_REQUEST."/".$_SESSION['cityid'], 0777);
        }



        if ($ultimate){
            $P = "frm_send_request.php";
            $rs->Start_Transaction();

            foreach($_POST['checkbox'] as $VehiclePlate) {

                $table_rows = $rs->Select('V_Violation',"VehiclePlate='".$VehiclePlate."' AND StatusTypeId<10", 'Id');
                while($table_row = mysqli_fetch_array($table_rows)){
                    $a_FineHistory = array(
                        array('field'=>'NotificationTypeId','selector'=>'value','type'=>'int','value'=>$NotificationTypeId),
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$table_row['Id'],'settype'=>'int'),
                        array('field'=>'EntityId','selector'=>'value','type'=>'int','value'=>$EntityId,'settype'=>'int'),
                        array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                        array('field'=>'FlowDate','selector'=>'value','type'=>'date','value'=>$FlowDate),
                        array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>$SendDate),
                        array('field'=>'PrintDate','selector'=>'value','type'=>'date','value'=>$PrintDate),
                        array('field'=>'FlowNumber','selector'=>'value','type'=>'str','value'=>$FlowNumber),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),

                    );
                    $rs->Insert('FineHistory',$a_FineHistory);

                    $a_Fine = array(
                        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId),
                    );
                    $rs->Update('Fine',$a_Fine, 'Id='.$table_row['Id']);

                }
            }

            $rs->End_Transaction();
            $FileName = $Documentation;

        }else{
            $FileName = 'export.pdf';
        }

        $pdf->Output(FOREIGN_REQUEST."/".$_SESSION['cityid'].'/'.$FileName, "F");
        $_SESSION['Documentation'] = $MainPath.'/doc/foreign/request/'.$_SESSION['cityid'].'/'.$FileName;


        header("location: ".$P);
    }

}





