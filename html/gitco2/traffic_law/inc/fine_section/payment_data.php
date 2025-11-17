<?php



//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         PAYMENT
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

$str_CSSPayment = 'data-toggle="tab"';
$str_Payment = "";
$str_PaymentImg = "";

$str_order = "PaymentDate asc";

$rs_Payment = $rs->Select('FinePayment', "FineId=" . $Id, $str_order);

$Payment_list = array();

if(mysqli_num_rows($rs_Payment)>0){
    while($r_Payment = mysqli_fetch_array($rs_Payment))
    {
        $Payment_list[] = $r_Payment;
    }
    
    $str_DocumentationFolder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE_HTML."/".$_SESSION['cityid']."/".$Id : FOREIGN_FINE_HTML."/".$_SESSION['cityid']."/".$Id;
    if($Payment_list['Documentation']!=""){
        $str_PaymentImg = '
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12" >
            <div class="col-sm-12 BoxRowLabel" style="height:41rem">
                <div class="imgWrapper" style="height:40.2rem;overflow:auto;margin-top:2rem;margin-left:5rem;">
                   <img id="img_front" class="iZoom"  src="'. $str_DocumentationFolder .'/'. $r_Payment[0]['Documentation'] .'" width="500px" />
                </div>
            </div>
        </div>
    ';
    }
    
    $rs_PaymentType = $rs->Select(MAIN_DB.'.PaymentType', "1=1");
    $a_PaymentTypeId = array();
    while ($r_PaymentType = mysqli_fetch_array($rs_PaymentType)) {
        $a_PaymentTypeId[$r_PaymentType['Id']] = $r_PaymentType['Title'];
    }
    
    //Intestazione
    $str_Payment = '
                <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                    PAGAMENTO
                </div>
            </div>
            <div class="clean_row HSpace4"></div>';
    
    //Righe collassabili
    foreach($Payment_list as $Payment_row)
    {
        $str_PaymentTypeId = (isset($a_PaymentTypeId[$Payment_row['PaymentTypeId']])) ? $a_PaymentTypeId[$Payment_row['PaymentTypeId']] : "";
        $str_PaymentDocumentId = (isset($a_PaymentDocumentId[$Payment_row['PaymentDocumentId']])) ? $a_PaymentDocumentId[$Payment_row['PaymentDocumentId']] : "";
        $str_BankMgmt = (isset($a_BankMgmt[$Payment_row['BankMgmt']])) ? $a_BankMgmt[$Payment_row['BankMgmt']] : "";
        //Gestione immagini pagamenti
        //***************************
        if($Payment_row['Documentation']!=null)	//In caso il nome dell'immagine fosse presente all'interno della riga, alla colonna "Documentation"
        {
            $path = getDocumentationPath($r_Fine['CountryId'],-1,$Payment_row['FineId'],$_SESSION['cityid'],$Payment_row['Documentation']);
        }
        if($Payment_row['Documentation']==null)	//In caso il nome dell'immagine NON fosse presente all'interno della colonna "Documentation", la si ricerca nella tabella FineDocumentation
        {
            $rs_documentation = $rs->Select('FineDocumentation','FineId='.$Id." AND DocumentationTypeId = 15");
            if(mysqli_num_rows($rs_documentation)>0) //In caso si trovi il nome dell'immagine in corrispondenza della colonna "Documentation" di FineDocumentation
            {
                while($r_documentation = mysqli_fetch_array($rs_documentation))
                    $path = getDocumentationPath($r_Fine['CountryId'],-1,$Payment_row['FineId'],$_SESSION['cityid'],$r_documentation['Documentation']);
            }
            else	//In caso non si trovi corrispondenza nemmeno nella colonna "Documentation" di FineDocumentation, viene restituita una stringa vuota
                $path = getDocumentationPath($r_Fine['CountryId'],-1,$Payment_row['FineId'],$_SESSION['cityid'],'');
        }
        $MimeType = @mime_content_type($path) ? mime_content_type($path) : '';
        //**************************
        $str_Payment .= '
            <div class="col-sm-12" id="FileName_'.$Payment_row['Id'].'" onclick="changeRowColor('.$Payment_row['Id'].')">
                <div class="col-sm-2 BoxRowLabel">
                    Metodo pagamento
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. $str_PaymentTypeId .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Data Pagamento
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . DateOutDB($Payment_row['PaymentDate']) . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Importo
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    ' . $Payment_row['Amount'] . '
                </div>
                <div class="col-sm-1 BoxRowHTitle" style="padding:0;text-align:center;background-color: #294A9C;color:white;padding-top:4px;">
                            <i class="fas fa-angle-down caret-toggle" id="heading" data-toggle="collapse" data-target="#collapse'.$Payment_row['Id'].'" aria-expanded="false" aria-controls="collapse"></i>
                </div>
                <div class="col-sm-1 BoxRowHTitle" style="padding:0;text-align:center;background-color: #294A9C;color:white;padding-top:4px;">
                            <span onclick="changeRowColor('.$Payment_row['Id'].')" data-toggle="tooltip" data-placement="top" title="Visualizza" data-mimetype="'.$MimeType.'" path="'.$path.'" docid="-1" doctype="-1" class="tooltip-r glyphicon glyphicon-eye-open col-sm-10" style="text-align:center;"></span>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="collapse" id="collapse'.$Payment_row['Id'].'">
		    <div class="col-sm-12">
		       <div class="col-sm-2 BoxRowLabel">
		            Tipo pagamento
		        </div>
		        <div class="col-sm-2 BoxRowCaption">
		            '. $str_PaymentDocumentId .'
		        </div>
		        <div class="col-sm-2 BoxRowLabel">
		            Tipo documento
		        </div>
		        <div class="col-sm-2 BoxRowCaption">
		            ' . $Payment_row['DocumentType'] . '
		        </div>
		        <div class="col-sm-2 BoxRowLabel">
		            Quinto campo
		        </div>
		        <div class="col-sm-2 BoxRowCaption">
		            ' . $Payment_row['FifthField'] . '
		        </div>
		    </div>
		    <div class="clean_row HSpace4"></div>
		    <div class="col-sm-12">
		    	<div class="col-sm-2 BoxRowLabel">
		            Nominativo
		        </div>
		        <div class="col-sm-10 BoxRowCaption">
		            ' . $Payment_row['Name'] . '
		        </div>
		    </div>
		    <div class="clean_row HSpace4"></div>
		    <div class="col-sm-12">
		    	<div class="col-sm-2 BoxRowLabel">
		            C/TERZI
		        </div>
		        <div class="col-sm-4 BoxRowCaption">
		            '. $str_BankMgmt .'
		        </div>
		        <div class="col-sm-2 BoxRowLabel">
		            Data registrazione
		        </div>
		        <div class="col-sm-4 BoxRowCaption">
		            '.$Payment_row['RegDate'].'
		        </div>
		    </div>
		    <div class="clean_row HSpace4"></div>
		    <div class="col-sm-12">
		        <div class="col-sm-2 BoxRowLabel" style="height:15rem" >
		            Note
		        </div>
		        <div class="col-sm-10 BoxRowCaption" style="height:15rem">
		           ' . $Payment_row['Note'] . '
		               
		        </div>
		    </div>
            </div>
            ';
    }
    //Immagine
    $str_Payment.= $str_PaymentImg;
    
} else $str_CSSPayment = ' style="color:#C43A3A; cursor:not-allowed;" ';




$str_Payment_Data = '
<div class="tab-pane" id="Payment">
    <div class="col-sm-12">
        '.$str_Payment.'
    </div>
</div>
';
?>