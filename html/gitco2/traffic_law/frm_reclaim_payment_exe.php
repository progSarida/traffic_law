<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters','s');

$FineId = CheckValue('Search_FineId','n');
$PaymentTypeId = CheckValue('PaymentTypeId','n'); //tipo di pagamento es. Bonifico, Bollettino, PagoPa

$InstallmentId = CheckValue('InstallmentList','n') != 0 ? CheckValue('InstallmentList','n') : null;

//echo CheckValue('InstallmentList','n'); DIE;

$ImportationId          = 2;
$DocumentationTypeId    = 15;

$str_City = $_SESSION['cityid'];
if($_SESSION['cityid']=='A950'||$_SESSION['cityid']=='C730'){
    $str_WhereCity = " (CityId='A950' OR CityId='C730') ";
} else if($_SESSION['cityid']=='H763'||$_SESSION['cityid']=='H416'){
    $str_WhereCity = " (CityId='H763' OR CityId='H416') ";
}else{
    $str_WhereCity = " CityId='". $_SESSION['cityid'] ."' ";
}

$rs_customer = $rs->Select('Customer', "CityId='" . $str_City . "'");
$r_customer = mysqli_fetch_array($rs_customer);


$NationalBankMgmt = $r_customer['NationalBankMgmt'];
$ForeignBankMgmt = $r_customer['ForeignBankMgmt'];
$FinePaymentSpecificationType = $r_customer['FinePaymentSpecificationType'];
$PaymentDocumentId = CheckValue('PaymentDocumentId','n'); //pagamento per tariffa di ammontare Ridotto, Normale, Maggiorato
$PaymentId = CheckValue('PaymentId','n');
$PaymentDate = DateInDB(CheckValue('PaymentDate','s'));

$rs_Fine = $rs->Select('Fine', "Id=" . $FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);

//echo  "PaymentId ".$PaymentId . " FineId ".$FineId;

$rs_FinePayment = $rs->Select('FinePayment', "Id=" . $PaymentId);
$r_FinePayment = mysqli_fetch_array($rs_FinePayment);




$rs_FineHistory = $rs->Select('FineHistory', "NotificationTypeId IN(2,15) AND FineId=" . $FineId);

if(mysqli_num_rows($rs_FineHistory)==0){
    $a_Fee = array();
    $rs_FineArticle = $rs->SelectQuery("
                    SELECT 
                    F.Id,
                    F.CityId,
                    F.Code,
                    F.FineDate,
                    F.ProtocolId,
                    F.ProtocolYear,
                    F.VehiclePlate,
                    F.CountryId,

                    FA.Fee,
                    FA.MaxFee,
                    
                    ArT.ReducedPayment

                    FROM Fine F 
                    JOIN 
                    FineArticle FA ON F.Id= FA.FineId
                    JOIN
                    ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
                    
                    
                    WHERE F.Id=".$FineId
    );
    $r_FineArticle = mysqli_fetch_array($rs_FineArticle);
    $Fee = $r_FineArticle['Fee'];
    $MaxFee = $r_FineArticle['MaxFee']/2;   //In FineArticle viene registrato il Max edittale
    $ReducedFee = ($r_FineArticle['Fee'] * FINE_PARTIAL);
    $ReducedFee = number_format($ReducedFee, 2, '.', '');
    
    //Se PaymentDocId = 0 --> ok, Se = 1 --> Fee, Se = 2 --> MaxFee
    //Se viene impostato il tipo di pagamento a ridotto ma l'articolo non prevede il ridotto, viene impostato il pagamento normale come minimo
    if($PaymentDocumentId == 0 && !$r_FineArticle['ReducedPayment'])
        $PaymentDocumentId = 1;
    
    switch($PaymentDocumentId){
        case 0:  $a_Fee['Fee'] = $ReducedFee; break;
        case 1:  $a_Fee['Fee'] = $Fee; break;
        case 2:  $a_Fee['Fee'] = $MaxFee; break;
    }
    $a_Fee['ResearchFee']=0.00;
    $a_Fee['NotificationFee']=0.00;
    $a_Fee['PercentualFee']=0.00;
    $a_Fee['CustomerFee']=0.00;
    $a_Fee['CanFee']=0.00;
    $a_Fee['CadFee']=0.00;

}else{
    //Se viene impostato il pagamento ridotto controllo se è previsto dall'articolo
    if($PaymentDocumentId == 0){
        //Controllo se l'articolo del verbale prevede pagamento ridotto
        $rs_Reduced = $rs->Select('V_FineTariff', "FineId=$FineId AND ReducedPayment > 0");
        //Se non è previsto imposto automaticamente il pagamento come normale
        if(mysqli_num_rows($rs_Reduced) == 0)
          $PaymentDocumentId = 1;  
    }
    
    $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $r_FinePayment['Amount'], $FineId, $str_City, $r_Fine['ProtocolYear'], $PaymentDate, $r_Fine['ReminderDate']);

}
/* 24/05/2022 - controllo che aggionrnava lo stato di pagamento se l'ammontare del pagamento supera l'ammontare del verbale
//Importo pagato
$Amount = CheckValue('Amount','n');
//totale sul verbale
$TotalFineAmount = $a_Fee['Fee']+$a_Fee['ResearchFee']+$a_Fee['NotificationFee']+$a_Fee['CustomerFee']+$a_Fee['CanFee']+$a_Fee['CadFee'];
if($Amount>=$TotalFineAmount){
    
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>30,'settype'=>'int')
    );
    $rs->Update('Fine',$a_Fine,"Id=".$FineId. "AND StatusTypeId not in (".implode(',', STATUSTYPEID_VERBALI_STATI_FINALI).")");
}
*/
if($PaymentTypeId==2 || $PaymentTypeId==18 || $PaymentTypeId==9 || $PaymentTypeId==19 || $PaymentTypeId==11){
    trigger_error("Importi--> Fee: ".$a_Fee['Fee']."NotificationFee: ".$a_Fee['NotificationFee']."ResearchFee:".$a_Fee['ResearchFee']);
    $a_Payment = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
        array('field'=>'BankMgmt','selector'=>'field','type'=>'int','settype'=>'int'),
        array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$a_Fee['Fee'],'settype'=>'flt'),
        array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['ResearchFee'],'settype'=>'flt'),
        array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['NotificationFee'],'settype'=>'flt'),
        array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt'),
        array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CustomerFee'],'settype'=>'flt'),
        array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CanFee'],'settype'=>'flt'),
        array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CadFee'],'settype'=>'flt'),
        array('field'=>'PaymentTypeId','selector'=>'value','type'=>'int','value'=>$PaymentTypeId,'settype'=>'int'),

    );


    $rs->Update('FinePayment',$a_Payment, "Id=".$PaymentId);



}else{





    $Documentation = CheckValue('Documentation','s');
    $Name = strtoupper(CheckValue('Name','s'));

    $CreditDate = DateInDB(CheckValue('CreditDate','s'));
    $FifthField = CheckValue('FifthField','s');


    if($FifthField>16)$FifthField=substr($FifthField,0,16);



    $TableId = CheckValue('TableId','n');
    $PaymentFee = CheckValue('PaymentFee','n');

    if(strlen($Documentation)>0) {
        $aDoc = explode("/",$Documentation);

        $Documentation = $aDoc[count($aDoc)-1];


        $rs->SetCharset('utf8');


        $fines = $rs->Select('Fine', $str_WhereCity. " AND Id=".$FineId);
        $FindNumber = mysqli_num_rows($fines);

        if($FindNumber>0) {


            $rs->Start_Transaction();
            $fine = mysqli_fetch_array($fines);
 			$str_Folder = ($fine['CountryId'] == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;

            $CityId = $fine['CityId'];
            $payments = $rs->Select('FinePayment', $str_WhereCity. " AND Documentation='".$Documentation."'");
            $FindNumber = mysqli_num_rows($payments);

            if($FindNumber==0) {

                $BankMgmt = ($fine['CountryId'] == 'Z000') ? $NationalBankMgmt : $ForeignBankMgmt;

                trigger_error("Importi--> Fee: ".$a_Fee['Fee']."NotificationFee: ".$a_Fee['NotificationFee']."ResearchFee:".$a_Fee['ResearchFee']);
                
                
                $a_Payment = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$Name),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                    array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                    array('field'=>'PaymentTypeId','selector'=>'value','type'=>'int','value'=>$PaymentTypeId,'settype'=>'int'),
                    array('field'=>'PaymentDocumentId','selector'=>'field','type'=>'int','settype'=>'int'),
                    array('field'=>'ImportationId','selector'=>'value','type'=>'int','value'=>$ImportationId,'settype'=>'int'),
                    array('field'=>'PaymentDate','selector'=>'value','type'=>'date','value'=>$PaymentDate),
                    array('field'=>'CreditDate','selector'=>'value','type'=>'date','value'=>$CreditDate),
                    array('field'=>'TableId','selector'=>'value','type'=>'int','value'=>$TableId, 'settype'=>'int'),
                    array('field'=>'PaymentFee','selector'=>'value','type'=>'int','value'=>$PaymentFee, 'settype'=>'int'),
                    array('field'=>'Amount','selector'=>'field','type'=>'flt','settype'=>'flt'),
                    array('field'=>'DocumentType','selector'=>'field','type'=>'int','settype'=>'int'),
                    array('field'=>'FifthField','selector'=>'value','type'=>'str','value'=>$FifthField),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>str_replace('.tif', '.jpg', $Documentation)),
                    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$a_Fee['Fee'],'settype'=>'flt'),
                    array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['ResearchFee'],'settype'=>'flt'),
                    array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['NotificationFee'],'settype'=>'flt'),
                    array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt'),
                    array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CustomerFee'],'settype'=>'flt'),
                    array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CanFee'],'settype'=>'flt'),
                    array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CadFee'],'settype'=>'flt'),
                    array('field'=>'InstallmentId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$InstallmentId,'nullable'=>true),
                );
 

                $rs->Insert('FinePayment',$a_Payment);



                $path = PAYMENT_RECLAIM."/".$_SESSION['cityid'] ."/";
                if (file_exists($path.$Documentation)) {

                    if (!is_dir($str_Folder."/".$CityId."/".$FineId)) {
                        mkdir($str_Folder."/".$CityId."/".$FineId, 0777);
                    }
                    $img = new Imagick($path . $Documentation);
                    $width = intval($img->getimagewidth());
                    $height = intval($img->getimageheight());
                    $img->stripImage();
                    $img->SetImageFormat('JPG');
                    $img->writeImage($str_Folder . "/" . $CityId . "/" . $FineId . "/" . str_replace('.tif', '.jpg', $Documentation));
                    $img->destroy();

                    if (file_exists($str_Folder."/".$CityId."/".$FineId."/".str_replace('.tif', '.jpg', $Documentation))) {
                        if (file_exists($path.$Documentation)) {
                            unlink($path.$Documentation);

                        }
                    }
                    else{
                        $_SESSION['Message']['Error'] = "Poblemi con la creazione del documento: ".$Documentation;
                        header("location: ".impostaParametriUrl(array('PaymentTypeId' => $PaymentTypeId), 'frm_reclaim_payment.php'.$Filters));
                        DIE;
                    }

                }


            }else{


                $a_Payment = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
                    array('field'=>'Name','selector'=>'field','type'=>'str'),
                    array('field'=>'PaymentDate','selector'=>'field','type'=>'date'),
                    array('field'=>'CreditDate','selector'=>'field','type'=>'date'),
                    array('field'=>'PaymentFee','selector'=>'field','type'=>'int', 'settype'=>'int'),
                    array('field'=>'Amount','selector'=>'field','type'=>'flt','settype'=>'flt'),
                    array('field'=>'DocumentType','selector'=>'field','type'=>'int','settype'=>'int'),
                    array('field'=>'FifthField','selector'=>'value','type'=>'str','value'=>$FifthField),
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$a_Fee['Fee'],'settype'=>'flt'),
                    array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['ResearchFee'],'settype'=>'flt'),
                    array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['NotificationFee'],'settype'=>'flt'),
                    array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt'),
                    array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CustomerFee'],'settype'=>'flt'),
                    array('field'=>'CanFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CanFee'],'settype'=>'flt'),
                    array('field'=>'CadFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['CadFee'],'settype'=>'flt'),
                    array('field'=>'Code','selector'=>'field','type'=>'str'),
                    array('field'=>'ProtocolId','selector'=>'field','type'=>'int','settype'=>'int'),
                    array('field'=>'VehiclePlate','selector'=>'field','type'=>'str'),
                    array('field'=>'FineDate','selector'=>'field','type'=>'date'),
                    array('field'=>'InstallmentId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$InstallmentId,'nullable'=>true),

                );

                $rs->Update('FinePayment',$a_Payment, "Id=".$PaymentId);




                $path = PAYMENT_RECLAIM."/".$_SESSION['cityid'] ."/";
                if (file_exists($path.$Documentation)) {
                    $a_FineDocumentation = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                        array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>$DocumentationTypeId,'settype'=>'int'),
                        array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'str', 'value' => date("Y-m-d H:i:s"))
                    );
                    $rs->Insert('FineDocumentation',$a_FineDocumentation);

                    if (!is_dir($str_Folder."/".$CityId."/".$FineId)) {
                        mkdir($str_Folder."/".$CityId."/".$FineId, 0777);
                    }

                    copy($path.$Documentation, $str_Folder."/".$CityId."/".$FineId."/".$Documentation);
                    if (file_exists($str_Folder."/".$CityId."/".$FineId."/".$Documentation)) {
                        unlink($path.$Documentation);
                    }
                    else{
                        $_SESSION['Message']['Error'] = "Poblemi con la creazione del documento: ".$Documentation;
                        header("location: ".impostaParametriUrl(array('PaymentTypeId' => $PaymentTypeId), 'frm_reclaim_payment.php'.$Filters));
                        DIE;
                    }

                }
            }
            $rs->End_Transaction();
        }
    }
}

$_SESSION['Message']['Success'] = "Azione eseguita con successo.";
header("location: ".impostaParametriUrl(array('PaymentTypeId' => $PaymentTypeId), 'frm_reclaim_payment.php'.$Filters));
