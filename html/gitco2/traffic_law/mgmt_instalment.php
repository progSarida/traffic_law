<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_dynamic_form.php");
include(INC."/function.php");
require(INC."/initialization.php");


$Id = CheckValue('Id', 'n');
$PaymentRateId =  CheckValue('payment_rate_id', 'n');
$rs_payment_rate = $rs->SelectQuery("SELECT * FROM PaymentRate WHERE Id=" . $PaymentRateId);
$payment_rate = $rs->getArrayLine($rs_payment_rate);
$instalmentPath = NATIONAL_RATE."/".$_SESSION['cityid']."/".$payment_rate['FineId']."/".$payment_rate['Id'];
$InstallmentPage =  CheckValue('InstallmentPage', 's') ?: 'mgmt_fine_upd.php';    //Imposta la pagina per il reindirizzamento
$FilterParameter = CheckValue('InstallmentPage', 's') != '' ? '&Filter=1' : ''; //Imposta il parametro Filter solo se la chiamata viene dalla pagina della lista rateizzazioni
$ConditionalId = CheckValue('InstallmentPage', 's') != '' ? ('&Id='.$payment_rate['Id'].'&FineId='.$payment_rate['FineId']) : ('&Id='.$payment_rate['FineId']);
$InstallmentPrintPageParameter = empty(CheckValue('InstallmentPage', 's')) ? '' : 'mgmt_installments_upd.php';
$InstallmentType = $_REQUEST['instalmentType'] ?? NULL;

$a_FilesToRemove = array();

if (!is_dir($instalmentPath)){
    mkdir($instalmentPath, 0750, true);
    chmod($instalmentPath, 0750);
    }

if($InstallmentType==3) {
    if(mysqli_num_rows($rs_payment_rate)>0){
        $rs->ExecuteQuery("DELETE FROM PaymentRate WHERE Id=" . $PaymentRateId);
        $rs->ExecuteQuery("DELETE FROM PaymentRateNumber WHERE PaymentRateId=" . $PaymentRateId);
        $rs->Delete("FineDocumentation","FineId=".$payment_rate['FineId']." AND DocumentationTypeId = 36");
        //Cancello la cartella della rateizzazioni con i suoi file interni
        if(is_dir($instalmentPath)){
            array_map('unlink', glob("$instalmentPath/*.*"));
            rmdir($instalmentPath);
            }
    }
    header('Location:' . $InstallmentPage . $str_GET_Parameter . '&Id=' . $_GET['Id'].'&Tab=rate' . $FilterParameter);
}else if(isset($_REQUEST['instalmentBackState'])){
    if($_REQUEST['instalmentBackState']>=1 && $_REQUEST['instalmentBackState']<=3){
        switch($_REQUEST['instalmentBackState']){
            case '1':
                $a_FilesToRemove[] = "Richiesta_Rateizzazione.pdf";
                
                if($payment_rate['SignedRequestDocumentId'] > 0){
                    $r_SignedRequestDoc = $rs->getArrayLine($rs->Select("FineDocumentation", "Id={$payment_rate['SignedRequestDocumentId']}"));
                    if($r_SignedRequestDoc){
                        $a_FilesToRemove[] = $r_SignedRequestDoc['Documentation'];
                        $rs->Delete("FineDocumentation", "Id={$payment_rate['SignedRequestDocumentId']}");
                    }
                }
                
                $a_PaymentRate = array(
                    array('field'=>'RequestStatusId','selector'=>'value','type'=>'int','value'=>0),
                    array('field'=>'RequestOutcome','selector'=>'value','type'=>'int','value'=>null, 'nullable' => true),
                    array('field'=>'StartDate','selector'=>'value','type'=>'date','value'=>null, 'nullable' => true),
                    array('field'=>'ResponseReason','selector'=>'value','type'=>'str','value'=>null, 'nullable' => true),
                    array('field'=>'SignedRequestDocumentId','selector'=>'value','type'=>'int','value'=>null, 'nullable' => true),
                );
                break;
            case '1_5':
                $r_SignedRequestDoc = $rs->getArrayLine($rs->Select("FineDocumentation", "Id={$payment_rate['SignedRequestDocumentId']}"));
                $a_FilesToRemove[] = $r_SignedRequestDoc['Documentation'];
                $a_PaymentRate = array(
                    array('field'=>'SignedRequestDocumentId','selector'=>'value','type'=>'int','value'=>null, 'nullable' => true),
                );
                $rs->Delete("FineDocumentation", "Id={$payment_rate['SignedRequestDocumentId']}");
                break;
            case '2':
                $a_FilesToRemove[] = "Esito_Rateizzazione.pdf";
                $a_PaymentRate = array(
                    array('field'=>'ResponseStatusId','selector'=>'value','type'=>'int','value'=>0)
                );
                break;
            case '3':
                $a_FilesToRemove[] = "Bollettini_Rateizzazione.pdf";
                $a_PaymentRate = array(
                    array('field'=>'BillStatusId','selector'=>'value','type'=>'int','value'=>0)
                );
                break;
        }
        $rs->Update('PaymentRate', $a_PaymentRate, 'Id='.$PaymentRateId);
        
        foreach ($a_FilesToRemove as $fileToRemove){
            unlink($instalmentPath."/".$fileToRemove);
        }
    }
    header('Location:' . $InstallmentPage . $str_GET_Parameter . $ConditionalId .'&Tab=rate' . $FilterParameter);
}
else if(isset($_REQUEST['checkForm'])){
    if($_REQUEST['checkForm']==1){
        $res = $_POST['form_body'];
        require __DIR__ . '/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf();
        $form = mb_convert_encoding($res, 'UTF-8', 'UTF-8');

        switch($InstallmentType) {
            case 1:
                $fileName = "Richiesta_Rateizzazione.pdf";
                break;
            case 2:
                $fileName = "Esito_Rateizzazione.pdf";
                break;
        }

        $mpdf->WriteHTML($form);
        $mpdf->Output($instalmentPath."/".$fileName, \Mpdf\Output\Destination::FILE);


        if(is_file($instalmentPath."/".$fileName) && $_REQUEST['instalmentPrintType']==1 && $InstallmentType>0 && $InstallmentType<3){
            if($InstallmentType==1){
                $a_PaymentRate = array(
                    array('field'=>'RequestStatusId','selector'=>'value','type'=>'int','value'=>1)
                );
            }
            else if($InstallmentType==2){
                $a_PaymentRate = array(
                    array('field'=>'ResponseStatusId','selector'=>'value','type'=>'int','value'=>1),
                    array('field'=>'ResponseReason','selector'=>'value','type'=>'str','value'=>$_REQUEST['instalmentResponseReason'])
                );
            }

            $rs->Update('PaymentRate', $a_PaymentRate, 'Id='.$PaymentRateId);
            
            header('Location:' . $InstallmentPage . $str_GET_Parameter . $ConditionalId . '&Tab=rate' . $FilterParameter);
        }
        else{
            $mpdf->Output($fileName, \Mpdf\Output\Destination::DOWNLOAD);
        }
    }
}

$payment_rates = $rs->SelectQuery("SELECT * FROM PaymentRateNumber WHERE PaymentRateId=" . $payment_rate['Id']);
$payment_trespasser = $rs->Select('Trespasser', "Id=" . $payment_rate['TrespasserId']);
$Trespasser = mysqli_fetch_array($payment_trespasser);
$rate_number = mysqli_num_rows($payment_rates);

$Registry = $rs->SelectQuery("SELECT * FROM sarida.City WHERE City.Id='" . $_SESSION['cityid'] . "'");
$Registry = mysqli_fetch_array($Registry);
$customer = $rs->SelectQuery("SELECT * FROM Customer WHERE CityId='" . $_SESSION['cityid'] . "'");
$customer = mysqli_fetch_array($customer);

$formParams = array('NationalityId'=>1,'LanguageId'=>1,'CityId'=>$_SESSION['cityid']);
$str_responseReason = '';
$textTitle = '';
switch($InstallmentType) {
    case 1:
        $formParams['FormTypeId'] = 90;
        $str_responseReason = '<div class="col-sm-8 BoxRowCaption"></div>';
        $fileName = "Richiesta_Rateizzazione.pdf";
        $textTitle = "Richiesta rateizzazione";
        breaK;
    case 2:
        $formParams['FormTypeId'] = 91;
        $str_responseReason = '
            <div class="col-sm-2 BoxRowLabel">Motivazione</div>
            <div class="col-sm-6 BoxRowCaption">
                <input class="frm_field frm_field_string" style="width:99%;" type="text" name="instalmentResponseReason" value="'.$payment_rate['ResponseReason'].'">
            </div>
            ';

        $fileName = "Esito_Rateizzazione.pdf";
        $textTitle = "Esito rateizzazione";
    breaK;
}
if($InstallmentType > 0 && $InstallmentType < 3){
    $cls_form = new cls_dynamic_form($formParams);
    $a_form = $rs->getArrayLine($rs->ExecuteQuery($cls_form->getFormQuery()));
    if ($a_form==null) {
        header("Location:" . $InstallmentPage . $str_GET_Parameter . '&error=Modello rateizzazione mancante per questo comune' . $ConditionalId . '&Tab=rate' . $FilterParameter);
        die;
    }
    $a_variables = $rs->getResults($rs->ExecuteQuery($cls_form->getVariablesQuery()));
    $cls_form->setForm($a_form, $a_variables);
    
    $cls_form->replaceVariables();
}
$fine = $rs->SelectQuery("SELECT * FROM Fine WHERE Id=".$payment_rate['FineId']);
$fine = mysqli_fetch_array($fine);

$rs_FineArticle = $rs->Select("FineArticle", "FineId=".$payment_rate['FineId']);
$r_FineArticle = $rs->getArrayLine($rs_FineArticle);

$province = $rs->SelectQuery("SELECT * FROM sarida.Province WHERE Id=" . $Registry['ProvinceId']);
$province = mysqli_fetch_array($province);

$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$r_FineArticle['ViolationTypeId']." AND CityId='".$_SESSION['cityid']."'");
$r_RuleType = mysqli_fetch_array($rs_RuleType);
$RuleTypeId = $r_RuleType['Id'];

$str_WhereCity = ($r_Customer['CityUnion']>1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
$rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
$a_ProtocolLetterLocality = array();
while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType1'] = $r_ProtocolLetter['NationalProtocolLetterType1'];
    $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['NationalProtocolLetterType2'] = $r_ProtocolLetter['NationalProtocolLetterType2'];
}
$NationalProtocolLetterType1 = $a_ProtocolLetterLocality[$fine['Locality']]['NationalProtocolLetterType1'];
$NationalProtocolLetterType2 = $a_ProtocolLetterLocality[$fine['Locality']]['NationalProtocolLetterType2'];


$payment_body = '<table>';
$totalAmount = 0;
while ($payment = mysqli_fetch_array($payment_rates)) {
    $totalAmount += $payment['Amount'];
    $payment_body .= '<tr><td style="width: 5rem; font-size: 12px;"><strong>Rata ' . $payment['RateNumber'] . '</strong></td>';
    $payment_body .= '<td style="width: 10rem; font-size: 12px;"><strong>' . number_format($payment['Amount'],2,",",".") . ' Euro</strong></td>';
    $payment_body .= '<td style="width: 5rem; font-size: 12px;"><strong>Scadenza</strong></td>';
    $payment_body .= '<td style="width: 7rem; font-size: 12px;"><strong>' . DateOutDB($payment['PaymentDate']) . '</strong></td></tr>';
    '&nbsp;&nbsp;&nbsp;' . $payment['Amount'] . ' Euro       Scadenza  ' . DateOutDB($payment['PaymentDate']);
}
$payment_body .= '</table>';

$a_keywords = array(
    "Blazon" => '<img src="'.$_SESSION['blazon'].'" style="width: 70px">',
    "ManagerCity" => $customer['ManagerCity'],
    "ManagerProvince" => $province['Title'],
    "ManagerAddress" => $customer['ManagerAddress'],
    "ManagerZip" => $customer['ManagerZIP'],
    "ManagerProvinceInitial" => $customer['ManagerProvince'],
    "ManagerVAT" => $customer['ManagerVAT'],
    "ManagerTaxCode" => $customer['ManagerTaxCode'],
    "ManagerPhone" => $customer['ManagerPhone'],
    "ManagerFax" => $customer['ManagerFax'],
    'Payment'=> $payment_body,
    'RegDate'=> DateOutDB($payment_rate['RegDate']),
    'RateNumber'=> $payment_rate['InstalmentNumber'],
    'TotalAmount' => number_format($payment_rate['InstalmentAmount'],2,",","."),
    'Nominativo' =>  $payment_rate['RateName'],
    'Position' => $payment_rate['Position'],
    'TrespasserName' => $Trespasser['CompanyName'].$Trespasser['Surname']." ".$Trespasser['Name'],
    'TrespasserBornCity' => $Trespasser['BornPlace'],
    'TrespasserBornDate' => DateOutDB($Trespasser['BornDate']),
    'TrespasserAddress' => $Trespasser['Address'],
    'TrespasserZip' => $Trespasser['ZIP'],
    'TrespasserCity' => $Trespasser['City'],
    "TrespasserProvinceInitial" => $Trespasser['Province'],
    'TrespasserCode' => $Trespasser['Code'],
    'CityId' => $_SESSION['cityid'],
    'Code' => $fine['Code'],
    'ProtocolYear' => $fine['ProtocolYear']
);

if($payment_rate['DocumentTypeId'] == 9){
    $rs_ReminderHistory = $rs->SelectQuery("SELECT COUNT(FineId)+1 ReminderLetter, SUM(NotificationFee) NotificationFee FROM FineReminder WHERE FineId=".$payment_rate['FineId']);
    $r_ReminderHistory = mysqli_fetch_array($rs_ReminderHistory);
    $n_ReminderLetter = $r_ReminderHistory['ReminderLetter'];
    
    $str_ProtocolLetter = ($RuleTypeId==1) ? $NationalProtocolLetterType1 : $NationalProtocolLetterType2;
    
    $str_ReminderCode = "R". $fine['ProtocolId']."/".$fine['ProtocolYear']."/".$str_ProtocolLetter."-".$n_ReminderLetter;   
    
    $a_keywords['ProtocolId'] = $fine['ProtocolId']." (Sollecito numero $str_ReminderCode)";
} else {
    $a_keywords['ProtocolId'] = $fine['ProtocolId'];
}

if($InstallmentType > 0 && $InstallmentType < 3){
    $cls_form->replaceKeywords($a_keywords);
}
require __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8']);
$mpdf->setAutoTopMargin = 'stretch';

include(INC . "/header.php");
require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
?>

<form method="post" id="formPrint" action="" accept-charset="UTF-8" enctype='multipart/form-data'>
    <input type="hidden" name="checkForm" value="1">
    <input type="hidden" name="instalmentType" value="<?=$InstallmentType;?>">
    <input type="hidden" name="payment_rate_id" value="<?=$PaymentRateId;?>">
    <input type="hidden" name="InstallmentPage" value="<?=$InstallmentPrintPageParameter?>">
    <div class="col-sm-12">
        <div class="col-sm-2"></div>
        <div class="col-sm-8 ">
            <div class="col-sm-8 BoxRowTitle">
                <?php echo $textTitle; ?>
                <div class="clean_row HSpace16"></div>
            </div>

        </div>
        <div class="col-sm-2"></div>
        <div class="clean_row"></div>
        <div class="col-sm-2"></div>
        <div class="col-sm-8">
            <div class="col-sm-2 BoxRowLabel">Stampa</div>
            <div class="col-sm-2 BoxRowCaption">
                <select name="instalmentPrintType" style="width:99%">
                    <option value="0">Provvisoria</option>
                    <option value="1">Definitiva</option>
                </select>
            </div>
            <?=$str_responseReason;?>
            <div class="clean_row HSpace16"></div>
        </div>
        <div class="col-sm-2"></div>
        <div class="clean_row"></div>
        <div class="col-sm-2"></div>
        <div class="col-sm-8">

                <textarea id="form_body" name="form_body" rows="50"
                          class="form-control"><?php if(isset($cls_form)) echo $cls_form->Content; ?></textarea>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input class="btn btn-default" type="button" id="sendmail" name="sendmail"
                               value="Stampa"/>
                        <input class="btn btn-default" id="updateBack" name="updateBack"
                               value="Indietro"/>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-sm-2" style="display: flex; justify-content: space-between; border: 2px black solid "></div>
    </div>
</form>
<script type="text/javascript">
    var edit = CKEDITOR.replace('form_body', {
        customConfig: '',
        filebrowserBrowseUrl: './ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
        disallowedContent: 'img{width,height,float}',
        extraAllowedContent: 'img[width,height,align];span{background}',
        extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,uploadfile,pastefromword,liststyle',
        height: 600,
        contentsCss: [
            'http://cdn.ckeditor.com/4.11.3/full-all/contents.css',
            'assets/css/pastefromword.css'
        ],
    });

    edit.config.allowedContent = true;
    edit.config.removePlugins = 'Source';
    edit.execCommand( 'shiftEnter' );

    $('#updateBack').click(function () {
        window.location = '<?= $InstallmentPage?><?=$str_GET_Parameter;?><?=$ConditionalId?><?= $FilterParameter?>';
    });
    
    $('#sendmail').click(function(){
    	$('#formPrint').attr("action","mgmt_instalment.php<?=$str_GET_Parameter;?><?=$ConditionalId?><?= $FilterParameter?>&Tab=rate");
		$('#formPrint').submit();
	}); 
    
</script>

<?php
