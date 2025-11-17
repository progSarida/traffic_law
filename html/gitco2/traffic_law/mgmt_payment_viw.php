<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PaymentId = CheckValue('PaymentId', 'n');
$rs = new CLS_DB();
$rs->SetCharset('utf8');

$n_PaymentProcedure = 1;

$Key = $PreviousId = $NextId = null;
$str_Folder = $Documentation = $Document = '';

$rs_PaymentType = $rs->Select('sarida.PaymentType', "1=1");
$a_PaymentTypeId = array();
while ($r_PaymentType = mysqli_fetch_array($rs_PaymentType)) {
    $a_PaymentTypeId[$r_PaymentType['Id']] = $r_PaymentType['Title'];
}


$rs_Payment = $rs->Select('V_FinePayment', "PaymentId=" . $PaymentId);
if (mysqli_num_rows($rs_Payment) > 0) {
    $r_Payment = mysqli_fetch_array($rs_Payment);
    $FineId = $r_Payment['FineId'];
    
    $rs_Payments = $rs->Select('V_FinePayment', "FineId=".$FineId);
    while ($r_Payments = mysqli_fetch_assoc($rs_Payments)){
        $a_Payments[] = $r_Payments['PaymentId'];
    }
    
    $Key = array_search($PaymentId, $a_Payments);
    $PreviousId = array_key_exists(($Key-1),$a_Payments) ? $a_Payments[$Key-1] : null;
    $NextId = array_key_exists(($Key+1),$a_Payments) ? $a_Payments[$Key+1] : null;

    $rs_Row = $rs->SelectQuery("
        SELECT  
        FA.Fee,
        FA.MaxFee,
        
        ArT.ReducedPayment,
        
        FH.NotificationTypeId,
        FH.CustomerFee,
        FH.NotificationFee,
        FH.ResearchFee,
        FH.CadFee,
        FH.CanFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=" . $r_Payment['FineId'] . " AND NotificationTypeId=6");


    $r_Row = mysqli_fetch_array($rs_Row);

    $AdditionalFee = $r_Row['NotificationFee'] + $r_Row['ResearchFee'];
    $Fee = NumberDisplay($r_Row['Fee'] + $AdditionalFee);
    $MaxFee = NumberDisplay($r_Row['MaxFee'] + $AdditionalFee);

    $ReducedFee = "";
    if ($r_Row['ReducedPayment']) {
        $ReducedFee = ($r_Row['Fee'] * FINE_PARTIAL) + $AdditionalFee;
        $ReducedFee = NumberDisplay($ReducedFee);
    }

    $SendDate = DateOutDB($r_Row['SendDate']);
    $DeliveryDate = DateOutDB($r_Row['DeliveryDate']);

    $TrespasserName = $r_Payment['CompanyName'] . " " . $r_Payment['Surname'] . " " . $r_Payment['Name'];
    $TrespasserAddress = $r_Payment['Address'] . " - " . $r_Payment['ZIP'] . " " . $r_Payment['City'] . " (" . $r_Payment['Province'] . ") " . $r_Payment['CountryTitle'];

    $n_PaymentProcedure = ChkPaymentProcedure($r_Payment['FineId'], $rs);
    
    $str_Folder = ($r_Payment['FineCountryId'] == 'Z000') ? NATIONAL_FINE_HTML : FOREIGN_FINE_HTML;
    
    //TODO Tenere monitorato
    if($r_Payment['Documentation']!=null)	//In caso il nome dell'immagine fosse presente all'interno della riga, alla colonna "Documentation"
    {
        $Document = $r_Payment['Documentation'];
        $Documentation = $str_Folder.'/'.$r_Payment['CityId'].'/'.$FineId.'/'.$Document;
    } else {
        $rs_Document = $rs->Select("FineDocumentation", "DocumentationTypeId=15 AND Note='".$PaymentId."'");
        $r_Document = mysqli_fetch_array($rs_Document);
        
        if($r_Document['Documentation'] != ""){
            $Document = $r_Document['Documentation'];
            $Documentation = $str_Folder.'/'.$r_Payment['CityId'].'/'.$FineId.'/'.$Document;
        }
    }

} else {
    $rs_Payment = $rs->Select('V_FinePaymentAll', "PaymentId=" . $PaymentId);
    $r_Payment = mysqli_fetch_array($rs_Payment);

    $AdditionalFee = "";
    $Fee = "";
    $MaxFee = "";
    $ReducedFee = "";

    $SendDate = "";
    $DeliveryDate = "";

    $TrespasserName = "";
    $TrespasserAddress = "";
    
    $str_Folder = PAYMENT_RECLAIM_HTML;
    
    if($r_Payment['Documentation'] != ""){
        $Document = $r_Payment['Documentation'];
        $Documentation = $str_Folder.'/'.$_SESSION['cityid'].'/'.$Document;
    }

}
if($Documentation != ''){
    $MimeType = @mime_content_type($Documentation) ?: '';
    
    if($MimeType == "image/jpeg" || $MimeType == "image/png"){
        $str_Document = '<img class="iZoom" src="'.$Documentation.'" width="100%" id="DocumentPreview">';
    } else {
        $str_Document = '<iframe style="width:100%; height:100%;background:white;" src="'.$Documentation.'"></iframe>';
    }
} else {
    $str_Document = '<i class="fas fa-file-excel" style="position: absolute;left: 35%;top: 50%;font-size: 25rem;line-height: inherit;opacity: 0.2;"></i>';
}


$str_out .= 
    '<div class="row-fluid">
        <div class="col-sm-7">
            <div class="col-sm-1 text-center BoxRowLabel" style="background-color: #294A9C;">
                '.(!empty($PreviousId)
                    ? '<a href='.impostaParametriUrl(array('PaymentId' => $PreviousId,)).'><i data-container="body" data-toggle="tooltip" data-placement="top" title="Indietro" class="tooltip-r glyphicon glyphicon-arrow-left" style="color:white;line-height:2rem;"></i></a>' 
                    : '').'
            </div>
            <div class="col-sm-10 text-center BoxRowLabel" style="background-color: #294A9C;">
                PAGAMENTO
            </div>
            <div class="col-sm-1 text-center BoxRowLabel" style="background-color: #294A9C;">
                '.(!empty($NextId)
                    ? '<a href='.impostaParametriUrl(array('PaymentId' => $NextId,)).'><i data-container="body" data-toggle="tooltip" data-placement="top" title="Avanti" class="tooltip-r glyphicon glyphicon-arrow-right" style="color:white;line-height:2rem;"></i></a>' 
                    : '').'
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-2 BoxRowLabel">
                Protocollo
            </div>
            <div class="col-sm-2 BoxRowCaption">
            '.($r_Payment['ProtocolId'] != '' ? $r_Payment['ProtocolId'].'/'.$r_Payment['ProtocolYear'] : '').'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . $r_Payment['Code'] . '
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Elaborazione controllo pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
            ' . $a_PaymentProcedure[$n_PaymentProcedure] . '
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . $r_Payment['VehiclePlate'] . '
            </div>            
            <div class="col-sm-2 BoxRowLabel">
            Data Infrazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . DateOutDB($r_Payment['FineDate']) . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
            Ora Infrazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . $r_Payment['FineTime'] . '
            </div>
            
            <div class="clean_row HSpace4"></div> 
            
            <div class="col-sm-2 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-4 BoxRowCaption">
            ' . $TrespasserName . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-4 BoxRowCaption">
            ' . $TrespasserAddress . '
            </div>
            
            <div class="clean_row HSpace4"></div>
                                        
            <div class="col-sm-2 BoxRowLabel">
                Ridotto
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . $ReducedFee . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Minima
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . $Fee . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Massima
            </div>
            <div class="col-sm-2 BoxRowCaption">
            ' . $MaxFee . '
            </div>
            
            <div class="clean_row HSpace4"></div> 
            
            <div class="col-sm-3 BoxRowLabel">
            Verbale spedito
            </div>
            <div class="col-sm-3 BoxRowCaption">
            ' . $SendDate . '
            </div>
            <div class="col-sm-3 BoxRowLabel">
            Verbale ricevuto
            </div>
            <div class="col-sm-3 BoxRowCaption">
            ' . $DeliveryDate . '
            </div>
            
            <div class="clean_row HSpace4"></div> 	  
            
            <div class="col-sm-2 BoxRowLabel">
                Metodo pagamento
            </div>
            <div class="col-sm-3 BoxRowCaption">
            
                ' . $a_PaymentTypeId[$r_Payment['PaymentTypeId']] . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo pagamento
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . $a_PaymentDocumentId[$r_Payment['PaymentDocumentId']] . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                C/TERZI
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' . $a_BankMgmt[$r_Payment['BankMgmt']] . '
            </div>
            
            <div class="clean_row HSpace4"></div>
                     
            <div class="col-sm-2 BoxRowLabel">
            	Importo pagato
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	' . NumberDisplay($r_Payment['Amount']) . '	
            </div>
            <div class="col-sm-2 BoxRowLabel">
            	Data Pagamento
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	' . DateOutDB($r_Payment['PaymentDate']) . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
            	Data accredito
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	' . DateOutDB($r_Payment['CreditDate']) . '
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Sanzione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['Fee']) . '
            </div>       
            <div class="col-sm-2 BoxRowLabel">
                Ricerca
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['ResearchFee'] - $r_Payment['CustomerFee']) . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Notifica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['NotificationFee']) . '	
            </div>     
            <div class="col-sm-2 BoxRowLabel">
                Magg. semestrale
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['Percentual']) . '
            </div>                
            <div class="col-sm-2 BoxRowLabel">
                CAN
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['CanFee']) . '
            </div> 
            <div class="col-sm-2 BoxRowLabel">
                CAD
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['CadFee']) . '
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Costi addizionali ente
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['CustomerFee']) . '
            </div> 
            <div class="col-sm-2 BoxRowLabel">
                Notifica tribunale
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . NumberDisplay($r_Payment['OfficeNotificationFee']) . '	
            </div>
            <div class="col-sm-4 BoxRowHTitle">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
            	Nominativo 
            </div>
            <div class="col-sm-10 BoxRowCaption">
            	' . $r_Payment['PaymentName'] . '
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
            	Tipo documento 
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	' . $r_Payment['DocumentType'] . '
            </div>
            <div class="col-sm-3 BoxRowLabel">
            	Quinto campo 
            </div>
            <div class="col-sm-4 BoxRowCaption">
            	' . $r_Payment['FifthField'] . '
            </div>
            
            <div class="clean_row HSpace4"></div>
            		 															
            <div class="col-sm-2 BoxRowLabel" style="height:20rem" >
            	Note
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height:20rem">
               ' . $r_Payment['Note'] . ' 			
            </div>
        </div>

        <div class="col-sm-5">
            <div class="col-sm-4 BoxRowLabel">
                Documentazione
            </div>
                                                          
            <div class="col-sm-8 BoxRowCaption">
                '.$Document.'
            </div>

            <div class="clean_row HSpace4"></div> 

            <div class="BoxRow" style="height:47.2rem;">
                '.$str_Document.'
            </div>
        </div>

        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <form name="f_payment" id="f_payment" method="post" action="mgmt_payment_prn_exe.php">
                        <input type="hidden" name="Id" value="'.$PaymentId.'">  
                        <input type="hidden" name="FineId" value="'. $r_Payment['FineId'] .'">
                        <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
                        <button class="btn btn-default" id="print">Stampa</button>
                        <button type="submit" class="btn btn-default" id="back">Indietro</button>
                    </form>
                </div>    
            </div>
        </div>  
  	</div>';


echo $str_out;
?>
    <script type="text/javascript">
        $('document').ready(function () {

            $('#back').click(function () {
            	window.location= "<?= $str_BackPage ?>";
                return false;
            });

            $('#DocumentPreview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        });
    </script>
<?php


include(INC . "/footer.php");
