<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$PaymentId= CheckValue('PaymentId','n');
$n_PaymentProcedure = 1;

$str_Fine ='
<div class="container-fluid">
    <div class="row-fluid">
        <div class="col-sm-12">
            <div class="col-sm-12" style="background-color: #fff">
                <img src="'.$_SESSION['blazon'].'" style="width:50px;">
                <span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
            </div>
        </div>    
    </div>
    <form name="f_payment" id="f_payment" method="post" action="mgmt_payment_del_exe.php">
        <input type="hidden" name="Id" value="'.$PaymentId.'">
        <input type="hidden" name="mgmtPayment" value="1">
        <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
';


$rs_Payment = $rs->Select('V_FinePayment', "PaymentId=" . $PaymentId);
if(mysqli_num_rows($rs_Payment)>0){
    $r_Payment = mysqli_fetch_array($rs_Payment);

    $rs_Row = $rs->SelectQuery("
        SELECT  
        FA.Fee,
        FA.MaxFee,
        
        ArT.ReducedPayment,
        
        FH.NotificationTypeId,
        FH.NotificationFee,
        FH.ResearchFee,
        FH.SendDate,
        FH.DeliveryDate,
        FH.ResultId
        FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=".$r_Payment['FineId']." AND NotificationTypeId=6");


    $r_Row = mysqli_fetch_array($rs_Row);

    $AdditionalFee = $r_Row['NotificationFee'] +	$r_Row['ResearchFee'];
    $Fee = NumberDisplay($r_Row['Fee'] + $AdditionalFee);
    $MaxFee = NumberDisplay($r_Row['MaxFee'] + $AdditionalFee);

    $ReducedFee = "";
    if($r_Row['ReducedPayment']){
        $ReducedFee = ($r_Row['Fee']*FINE_PARTIAL)+$AdditionalFee;
        $ReducedFee = NumberDisplay($ReducedFee);
    }

    $SendDate = DateOutDB($r_Row['SendDate']);
    $DeliveryDate = DateOutDB($r_Row['DeliveryDate']);


    $TrespasserName = $r_Payment['CompanyName']." ".$r_Payment['Surname']." ".$r_Payment['Name'];
    $TrespasserAddress = $r_Payment['Address']." - ".$r_Payment['ZIP']." ".$r_Payment['City'].' ('.$r_Payment['Province'].") ".$r_Payment['CountryTitle'];
    $n_PaymentProcedure = 0;
    //$n_PaymentProcedure = ChkPaymentProcedure($r_Payment['FineId'], $rs);

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

}


    $str_Fine .= '
                  
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Protocollo
                </div>
                <div class="col-sm-2 BoxRowCaption">
                ' . $r_Payment['ProtocolId'] . '/' . $r_Payment['ProtocolYear'] . '
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
            </div>
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Targa
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                '.$r_Payment['VehiclePlate'].'
                </div>            
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                Data Infrazione
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                '.DateOutDB($r_Payment['FineDate']).'
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                Ora Infrazione
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                '.$r_Payment['FineTime'].'
                </div>                
            </div>

            <div class="col-sm-12 BoxRow">
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Nominativo
                </div>
                <div class="col-sm-4 BoxRowLabel" style="font-size:1.2rem">
                '. $TrespasserName .'
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Indirizzo
                </div>
                <div class="col-sm-4 BoxRowLabel" style="font-size:1.2rem">
                '. $TrespasserAddress .'
                </div> 	 	
            </div>                        
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Ridotto
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                '.$ReducedFee.'
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Minima
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                '. $Fee .'
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                    Massima
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">
                '. $MaxFee .'
                </div>
            </div>      
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-3 BoxRowLabel" style="font-size:1.2rem">
                Verbale spedito
                </div>
                <div class="col-sm-3 BoxRowLabel" style="font-size:1.2rem">
                '. $SendDate .'
                </div>
                <div class="col-sm-3 BoxRowLabel" style="font-size:1.2rem">
                Verbale ricevuto
                </div>
                <div class="col-sm-3 BoxRowLabel" style="font-size:1.2rem">
                '. $DeliveryDate .'
                </div>
            </div>  
            <div class="col-sm-12 BoxRow">
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
                <div class="col-sm-2 BoxRowLabel">
                    Importo
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    ' . $r_Payment['Amount'] . '	
                </div>
            </div>
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-3 BoxRowLabel">
                    Nominativo 
                </div>
                <div class="col-sm-9 BoxRowCaption">
                    ' . $r_Payment['PaymentName'] . '
                </div>
            </div>
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-3 BoxRowLabel">
                    Tipo documento 
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    ' . $r_Payment['DocumentType'] . '
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Quinto campo 
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    ' . $r_Payment['FifthField'] . '
                </div>					
            </div> 															
            <div class="col-sm-12 BoxRow" style="height:20rem">
                <div class="col-sm-3 BoxRowLabel" >
                    Note
                </div>
                <div class="col-sm-9 BoxRowCaption" style="height:20rem">
                   ' . $r_Payment['Note'] . ' 			
                    
                </div>
            </div>  	
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                    <input type="submit" class="btn btn-default" style="margin-top:1rem;" value="cancella">
                    <button class="btn btn-default" id="back" style="margin-top:1rem;">Indietro</button>
                </div>    
             </div>

        </div>
  	</form>
    
</div>';


echo $str_Fine;
?>
    <script type="text/javascript">
        $('document').ready(function(){


            $('#f_payment').submit(function(){
                return del = confirm("Si sta per cancellare il pagamento in maniera definitiva. Continuare?");
            });


            $('#back').click(function(){
            	window.location= "<?= $str_BackPage ?>";
                return false;
            });
        });

    </script>
<?php



include(INC."/footer.php");
